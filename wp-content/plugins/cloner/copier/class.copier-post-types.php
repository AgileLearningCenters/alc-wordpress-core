<?php

if ( ! class_exists( 'Site_Copier_Post_Types' ) ) {
    class Site_Copier_Post_Types extends Site_Copier {

        protected $type;
        protected $args = array();
        protected $posts_copied = array();

        public function __construct( $source_blog_id, $template, $args = array(), $user_id = 0 ) {
            $this->args = wp_parse_args( $args, $this->get_default_args() );

            $this->source_blog_id = $source_blog_id;
            $this->user_id = $user_id;
            $this->template = $template;
        }

        public function get_default_args() {}

        public function copy() {
            global $wpdb;

            if ( $this->type == false )
                return new WP_Error( 'wrong_post_type', __( 'No Custom Post Types to copy', WPMUDEV_COPIER_LANG_DOMAIN ) );


            /**
             * Allows to remove some hooks when inserting the posts
             *
             * Many plugins uses posts actions to publish in FB or to send emails.
             * This filter is useful if we want to try to avoid it.
             * The hooks are removed by default.
             *
             * The filter removes the following hooks:
             * - save_post
             * - wp_insert_post
             * - save_post_{$post->post_type}
             * - transition_post_status
             * - {$old_status}_to_{$new_status}
             * - {$new_status}_{$post->post_type}
             * - It also disables wp_mail just in case
             *
             * @param Boolean $remove_hooks Remove hooks if set to true (true by default)
             */
            $remove_hooks = apply_filters( 'wpmudev_copier_remove_insert_post_filters', true );

            if ( $remove_hooks ) {
                add_filter( 'wp_mail', array( $this, 'disable_wp_mail' ), 1 );
                remove_all_actions( 'save_post' );
                remove_all_actions( 'wp_insert_post' );
                remove_all_actions( 'transition_post_status' );
            }


            $this->clear_posts();

            switch_to_blog( $this->source_blog_id );

            /**
             * Filter the posts query variables.
             *
             * Allows to modify the posts query on the source blog ID.
             *
             * @param Array. WP_Query attributes to perform the search
             */
            $args = apply_filters( 'wpmudev_copier_get_source_posts_args', array(
                'posts_per_page' => -1,
                'ignore_sticky_posts' => true,
                'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private', 'inherit' ),
                'post_type' => $this->type
            ) );
            $all_posts = get_posts(
                $args
            );

            // Adding the metadata
            foreach ( $all_posts as $key => $post ) {

                $meta_keys = array_keys( get_post_meta( $post->ID ) );
                $all_posts[ $key ]->meta = array();
                foreach ( $meta_keys as $meta_key ) {
                    $all_posts[ $key ]->meta[ $meta_key ] = get_post_meta( $post->ID, $meta_key, true );
                }

                $all_posts[ $key ]->is_sticky = is_sticky( $post->ID );
            }

            restore_current_blog();

            if ( empty( $all_posts ) )
                return new WP_Error( 'empty_posts', __( 'No posts to copy', WPMUDEV_COPIER_LANG_DOMAIN ) );

            // Array that relations the source posts with the destination posts
            $posts_mapping = array();
            foreach ( $all_posts as $post ) {
                $new_post = (array)$post;
                $new_post['import_id'] = $new_post['ID'];
                unset( $new_post['ID'] );


                if ( isset( $this->args['update_date'] ) && $this->args['update_date'] && $new_post['post_status'] != 'future' ) {
                    // Do we have to update post dates?
                    $current_time = current_time( 'mysql', false );
                    $current_gmt_time = current_time( 'mysql', false );

                    $new_post['post_date'] = $current_time;
                    $new_post['post_modified'] = $current_time;
                    $new_post['post_date_gmt'] = $current_gmt_time;
                    $new_post['post_modified_gmt'] = $current_gmt_time;

                    // Deprecated
                    if ( $this->type == 'page' )
                        do_action('blog_templates-update-pages-dates', $this->template, get_current_blog_id(), $this->user_id, $new_post, $post->ID );

                    // Deprecated
                    if ( $this->type == 'post' )
                        do_action('blog_templates-update-posts-dates', $this->template, get_current_blog_id(), $this->user_id, $new_post, $post->ID );

                }

                /**
                 * Fires before a post is copied.
                 *
                 * Useful for modifying a post attributes
                 *
                 * @param Integer $source_blog_id Blog ID from where we are copying the post.
                 * @param Integer $post_id Source Post ID.
                 * @param Array $new_post New post attributes.
                 * @param Integer $user_id Post Author.
                 * @param Array $template Only applies when using New Blog Templates. Includes the template attributes
                 */
                do_action( 'wpmudev_copier-copy-post', $this->source_blog_id, $post->ID, $new_post, $this->user_id, $this->template, $post );

                if ( $remove_hooks ) {
                    $action = "new_to_" . $new_post['post_status'];
                    remove_all_actions( $action );

                    $action = $new_post['post_status'] . "_" . $new_post['post_type'];
                    remove_all_actions( $action );
                }


                // If the user is set in arguments, let's use that one
                if ( $this->user_id )
                    $new_post['post_author'] = $this->user_id;

                /**
                 * Allows to change the cloned post before inserting it
                 *
                 * @param Array $new_post New post properties
                 * @param Integer $source_blog_id Blog ID from where we are copying the post.
                 * @param Integer $post_id Source Post ID.
                 * @param Integer $user_id Post Author.
                 * @param Array $template Only applies when using New Blog Templates. Includes the template attributes
                 */
                $new_post = apply_filters( 'wpmudev_copier_insert_new_post', $new_post, $this->source_blog_id, $post->ID, $this->user_id, $this->template );

                $new_post_id = @wp_insert_post( $new_post );


                if ( ! is_wp_error( $new_post_id ) ) {
                    // Map the post
                    $posts_mapping[ $post->ID ] = $new_post_id;

                    // And insert metadata
                    foreach ( $post->meta as $meta_key => $meta_value )
                        update_post_meta( $new_post_id, $meta_key, $meta_value );

                    if ( $post->is_sticky )
                        stick_post( $new_post_id );

                }

            }

            // Now remap parents
            foreach ( $posts_mapping as $post_id ) {
                $current_parent_id = wp_get_post_parent_id( $post_id );
                if ( ! empty( $current_parent_id ) && isset( $posts_mapping[ $current_parent_id ] ) ) {
                    $postarr = array(
                        'ID' => $post_id,
                        'post_parent' => $posts_mapping[ $current_parent_id ]
                    );
                    wp_update_post( $postarr );
                }

            }

            /**
             * Fires after all posts have been copied.
             *
             * @param Integer $source_blog_id Blog ID from where we are copying the post.
             * @param Integer $posts_mapping Map of posts [source_post_id] => $new_post_id.
             * @param Integer $user_id Post Author.
             * @param Array $template Only applies when using New Blog Templates. Includes the template attributes
             */
            do_action( 'wpmudev_copier-copied-posts', $this->source_blog_id, $posts_mapping, $this->user_id, $this->template, $this->type );

            if ( $remove_hooks )
                remove_filter( 'wp_mail', array( $this, 'disable_wp_mail' ), 1 );

            return $posts_mapping;

        }

        /**
         * Disable the mails. Some subscriptions plugins could send emails when a new post is created
         *
         * @param type $phpmailer
         * @return type
         */
        public function disable_wp_mail( $args ) {
            $args['to'] = '';
            return $args;
        }

        private function clear_posts() {
            /**
             * Filter the deletion posts query variables.
             *
             * Before inserting any post we need to delete the current posts in the
             * destination blog. This filter allows to modify the deletion posts query on the destination blog.
             *
             * @param Array. WP_Query attributes to perform the search.
             */
            $args = apply_filters( 'wpmudev_copier_get_delete_posts_args', array(
                'posts_per_page' => -1,
                'ignore_sticky_posts' => true,
                'post_status' => 'any',
                'fields' => 'ids',
                'post_type' => $this->type
            ) );

            $all_posts = get_posts( $args );

            remove_action( 'before_delete_post', '_reset_front_page_settings_for_post' );
            remove_action( 'wp_trash_post',      '_reset_front_page_settings_for_post' );

            foreach ( $all_posts as $post_id )
                @wp_delete_post( $post_id, true );

        }

    }


}