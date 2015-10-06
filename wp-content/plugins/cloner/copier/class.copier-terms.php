<?php

if ( ! class_exists( 'Site_Copier_Terms' ) ) {
	class Site_Copier_Terms extends Site_Copier {

		public function get_default_args() {
			return array(
				'update_relationships' => false, // all or array with post, page or any post_type
				'posts_mapping' => array()
			);
		}


		public function copy() {
			global $wpdb;

			if ( ! function_exists( 'wp_delete_link' ) )
				include_once( ABSPATH . 'wp-admin/includes/bookmark.php' );

			// Remove current terms
			$taxonomies = get_taxonomies();
			if ( isset( $taxonomies['nav_menu'] ) )
				unset( $taxonomies['nav_menu'] );

			$all_terms = get_terms( $taxonomies, array( 'hide_empty' => false ) );
			foreach ( $all_terms as $term )
				$result = wp_delete_term( $term->term_id, $term->taxonomy );

			unset( $all_terms );

			// Remove current links
			$all_links = get_bookmarks();
			foreach ( $all_links as $link )
				wp_delete_link( $link->link_id );


			switch_to_blog( $this->source_blog_id );

			// Need to check what post types are in the source blog
			$exclude_post_types = '("' . implode( '","', array( 'nav_menu_item' ) ) . '")';
			$post_types = $wpdb->get_col( "SELECT DISTINCT post_type FROM $wpdb->posts WHERE post_type NOT IN $exclude_post_types" );

			$source_posts_ids = get_posts( array(
				'ignore_sticky_posts' => true,
				'posts_per_page' => -1,
				'post_type' => $post_types,
				'fields' => 'ids',
				'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private', 'inherit' ),
			) );

			$_taxonomies = $wpdb->get_col( "SELECT DISTINCT taxonomy FROM $wpdb->term_taxonomy" );
	        $taxonomies = array();
	        foreach ( $_taxonomies as $taxonomy )
	            $taxonomies[ $taxonomy ] = $taxonomy;

	        if ( isset( $taxonomies['nav_menu'] ) )
				unset( $taxonomies['nav_menu'] );

			$source_terms = $this->get_terms( $taxonomies, array( 'orderby' => 'id', 'get' => 'all' ) );

			$_source_links = get_bookmarks();
			$source_links = array();
			foreach ( $_source_links as $source_link ) {
				$item = $source_link;
				$object_terms = $this->get_object_terms( $source_link->link_id, array( 'link_category' ) );
				if ( ! empty( $object_terms ) && ! is_wp_error( $object_terms ) )
					$item->terms = $object_terms;

				$source_links[] = $item;

			}

			restore_current_blog();

			// Now insert the links
			$mapped_links = array();
			foreach ( $source_links as $link ) {
				$new_link = (array)$link;
				unset( $new_link['link_id'] );

				$new_link_id = wp_insert_link( $new_link );
				if ( ! is_wp_error( $new_link_id ) )
					$mapped_links[ $link->link_id ] = $new_link_id;

			}

			// Deprecated
			do_action( 'blog_templates-copy-links', $this->template, get_current_blog_id(), $this->user_id );

			/**
             * Fires after the links have been copied.
             *
             * @param Integer $user_id Blog Administrator ID.
             * @param Integer $source_blog_id Source Blog ID from where we are copying the links.
             * @param Array $template Only applies when using New Blog Templates. Includes the template attributes.
             */
			do_action( 'wpmudev_copier-copy-links', $this->user_id, $this->source_blog_id, $this->template );


			// Insert the terms
			$mapped_terms = array();
			foreach ( $source_terms as $term ) {

				$term_args = array(
					'description' => $term->description,
					'slug' => $term->slug,
					'import_id' => $term->term_id
				);

				$new_term = $this->wp_insert_term( $term->name, $term->taxonomy, $term_args );
				do_action( 'wpmudev_copier_insert_term', $new_term, $term );

				if ( is_wp_error( $new_term ) ) {
					// Usually Uncategorized cannot be deleted, we need to check if it's already in the destination blog and map it
					$error_code = $new_term->get_error_code();
					if ( 'term_exists' === $error_code ) {
						$new_term = get_term_by( 'slug', $term->slug, $term->taxonomy );
						if ( ! is_object( $new_term ) || is_wp_error( $new_term ) )
							continue;

						$new_term = array(
							'term_id' => $new_term->term_id
						);
					}

				}

				if ( ! is_wp_error( $new_term ) ) {
					// Check if the term has a parent
					$mapped_terms[ $term->term_id ] = absint( $new_term['term_id'] );
				}

			}

			// Now update terms parents
			foreach ( $source_terms as $term )
				if ( ! empty( $term->parent ) && isset( $mapped_terms[ $term->parent ] ) && isset( $mapped_terms[ $term->term_id ] ) )
					wp_update_term( $mapped_terms[ $term->term_id ], $term->taxonomy, array( 'parent' => $mapped_terms[ $term->parent ] ) );

			// Deprecated
			do_action( 'blog_templates-copy-terms', $this->template, get_current_blog_id(), $this->user_id, $mapped_terms );

			// Update posts term relationships
			if ( $this->args['update_relationships'] ) {

				// Assign link categories
				if ( isset( $taxonomies['link_category'] ) ) {
					// There are one or more link categories
					// Let's assigned them
					if ( ! empty( $source_links ) )
						$this->assign_terms_to_links( $source_links, $mapped_terms, $mapped_links );
				}


				if ( is_array( $this->args['update_relationships'] ) )
					$args['post_type'] = $this->args['update_relationships'];

				if ( ! empty( $source_posts_ids ) ) {

					// Remove the link categories for posts
					$posts_taxonomies = $taxonomies;
					if ( isset( $posts_taxonomies['link_category'] ) )
						unset( $posts_taxonomies['link_category'] );

					$this->assign_terms_to_objects( $source_posts_ids, $posts_taxonomies, $mapped_terms );

				}


				// Deprecated
				do_action( 'blog_templates-copy-term_relationships', $this->template, get_current_blog_id(), $this->user_id );

			}

			/**
             * Fires after the terms have been copied.
             *
             * @param Integer $user_id Blog Administrator ID.
             * @param Integer $source_blog_id Source Blog ID from where we are copying the terms.
             * @param Array $template Only applies when using New Blog Templates. Includes the template attributes.
			 * @param Array $mapped_terms Relationship between source term IDs and new term IDs
             */
			do_action( 'wpmudev_copier-copy-terms', $this->user_id, $this->source_blog_id, $this->template, $mapped_terms );

			// If there's a links widget in the sidebar we may need to set the new category ID
	        $widget_links_settings = get_blog_option( $this->source_blog_id, 'widget_links' );

	        if ( is_array( $widget_links_settings ) ) {

		        $new_widget_links_settings = $widget_links_settings;

		        foreach ( $widget_links_settings as $widget_key => $widget_settings ) {
		            if ( ! empty( $widget_settings['category'] ) && isset( $mapped_terms[ $widget_settings['category'] ] ) ) {

		                $new_widget_links_settings[ $widget_key ]['category'] = $mapped_terms[ $widget_settings['category'] ];
		            }
		        }

	        	$updated = update_option( 'widget_links', $new_widget_links_settings );
	        }

	        return true;
		}

		public function assign_terms_to_objects( $source_objects_ids, $taxonomies, $mapped_terms ) {

			$objects_ids = array();
			foreach ( $source_objects_ids as $source_object_id ) {
				if ( isset( $this->args['posts_mapping'][$source_object_id] ) )
					$objects_ids[] = $this->args['posts_mapping'][$source_object_id];
			}

			if ( empty( $objects_ids ) )
				return;

			$source_objects_terms = array();
			switch_to_blog( $this->source_blog_id );
			foreach ( $source_objects_ids as $object_id ) {
				$object_terms = $this->get_object_terms( $object_id, $taxonomies );
				if ( ! empty( $object_terms ) && ! is_wp_error( $object_terms ) )
					$source_objects_terms[ $object_id ] = $object_terms;
			}
			restore_current_blog();
			if ( ! empty( $source_objects_terms ) ) {
				// We just need to set the object terms with the remapped terms IDs
				foreach ( $source_objects_terms as $object_id => $source_object_terms ) {
					if ( ! isset( $this->args['posts_mapping'][ $object_id ] ) )
						continue;

					$new_object_id = $this->args['posts_mapping'][ $object_id ];

					$taxonomies = array_unique( wp_list_pluck( $source_object_terms, 'taxonomy' ) );

					foreach ( $taxonomies as $taxonomy ) {
						$source_terms_ids = wp_list_pluck( wp_list_filter( $source_object_terms, array( 'taxonomy' => $taxonomy ) ), 'term_id' );

						$new_terms_ids = array();
						foreach ( $source_terms_ids as $source_term_id ) {
							if ( isset( $mapped_terms[ $source_term_id ] ) )
								$new_terms_ids[] = $mapped_terms[ $source_term_id ];
						}

						// Set post terms
						$this->set_object_terms( $new_object_id, $new_terms_ids, $taxonomy );
					}

				}
			}
		}

		public function assign_terms_to_links( $source_links, $mapped_terms, $mapped_links ) {

			foreach ( $source_links as $source_link ) {
				$source_link_id = $source_link->link_id;
				$source_terms_ids = wp_list_pluck( $source_link->terms, 'term_id' );

				if ( ! isset( $mapped_links[ $source_link_id ] ) )
					continue;

				$new_link_id = $mapped_links[ $source_link_id ];

				$new_terms_ids = array();
				foreach ( $source_terms_ids as $source_term_id ) {
					if ( isset( $mapped_terms[ $source_term_id ] ) )
						$new_terms_ids[] = $mapped_terms[ $source_term_id ];
				}

				$this->set_object_terms( $new_link_id, $new_terms_ids, 'link_category' );
			}

		}

		/**
		 * We need this function because WP checks if the taxonomy exist
		 * When switching between blogs, this will not work
		 *
		 * @param type $object_id
		 * @param type $terms_ids
		 * @return array|WP_Error Affected Term IDs
		 */
		private function get_object_terms( $object_ids, $taxonomies ) {
			global $wpdb;

			if ( empty( $object_ids ) || empty( $taxonomies ) )
				return array();

			if ( !is_array($taxonomies) )
				$taxonomies = array($taxonomies);

			if ( !is_array($object_ids) )
				$object_ids = array($object_ids);
			$object_ids = array_map('intval', $object_ids);

			$defaults = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all');
			$args = wp_parse_args( array(), $defaults );

			$terms = array();
			if ( count($taxonomies) > 1 ) {
				foreach ( $taxonomies as $index => $taxonomy ) {
					$t = get_taxonomy($taxonomy);
					if ( isset($t->args) && is_array($t->args) && $args != array_merge($args, $t->args) ) {
						unset($taxonomies[$index]);
						$terms = array_merge($terms, wp_get_object_terms($object_ids, $taxonomy, array_merge($args, $t->args)));
					}
				}
			} else {
				$t = get_taxonomy( $taxonomies[ key( $taxonomies ) ] );
				if ( isset($t->args) && is_array($t->args) )
					$args = array_merge($args, $t->args);
			}

			extract($args, EXTR_SKIP);

			if ( 'count' == $orderby )
				$orderby = 'tt.count';
			else if ( 'name' == $orderby )
				$orderby = 't.name';
			else if ( 'slug' == $orderby )
				$orderby = 't.slug';
			else if ( 'term_group' == $orderby )
				$orderby = 't.term_group';
			else if ( 'term_order' == $orderby )
				$orderby = 'tr.term_order';
			else if ( 'none' == $orderby ) {
				$orderby = '';
				$order = '';
			} else {
				$orderby = 't.term_id';
			}

			// tt_ids queries can only be none or tr.term_taxonomy_id
			if ( ('tt_ids' == $fields) && !empty($orderby) )
				$orderby = 'tr.term_taxonomy_id';

			if ( !empty($orderby) )
				$orderby = "ORDER BY $orderby";

			$order = strtoupper( $order );
			if ( '' !== $order && ! in_array( $order, array( 'ASC', 'DESC' ) ) )
				$order = 'ASC';

			$taxonomies = "'" . implode("', '", $taxonomies) . "'";
			$object_ids = implode(', ', $object_ids);

			$select_this = '';
			if ( 'all' == $fields )
				$select_this = 't.*, tt.*';
			else if ( 'ids' == $fields )
				$select_this = 't.term_id';
			else if ( 'names' == $fields )
				$select_this = 't.name';
			else if ( 'slugs' == $fields )
				$select_this = 't.slug';
			else if ( 'all_with_object_id' == $fields )
				$select_this = 't.*, tt.*, tr.object_id';

			$query = "SELECT $select_this FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy IN ($taxonomies) AND tr.object_id IN ($object_ids) $orderby $order";

			if ( ! isset( $taxonomy ) )
				$taxonomy = $taxonomies[0];

			if ( 'all' == $fields || 'all_with_object_id' == $fields ) {
				$_terms = $wpdb->get_results( $query );
				foreach ( $_terms as $key => $term ) {
					$_terms[$key] = sanitize_term( $term, $taxonomy, 'raw' );
				}
				$terms = array_merge( $terms, $_terms );
				update_term_cache( $terms );
			} else if ( 'ids' == $fields || 'names' == $fields || 'slugs' == $fields ) {
				$_terms = $wpdb->get_col( $query );
				$_field = ( 'ids' == $fields ) ? 'term_id' : 'name';
				foreach ( $_terms as $key => $term ) {
					$_terms[$key] = sanitize_term_field( $_field, $term, $term, $taxonomy, 'raw' );
				}
				$terms = array_merge( $terms, $_terms );
			} else if ( 'tt_ids' == $fields ) {
				$terms = $wpdb->get_col("SELECT tr.term_taxonomy_id FROM $wpdb->term_relationships AS tr INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tr.object_id IN ($object_ids) AND tt.taxonomy IN ($taxonomies) $orderby $order");
				foreach ( $terms as $key => $tt_id ) {
					$terms[$key] = sanitize_term_field( 'term_taxonomy_id', $tt_id, 0, $taxonomy, 'raw' ); // 0 should be the term id, however is not needed when using raw context.
				}
			}

			if ( ! $terms )
				$terms = array();

			return apply_filters( 'wp_get_object_terms', $terms, $object_ids, $taxonomies, $args );
		}

		/**
		 * We need this function because WP checks if the taxonomy exist
		 * We are going to force the insertion
		 *
		 * @param type $object_id
		 * @param type $terms_ids
		 * @return array|WP_Error Affected Term IDs
		 */
		private function set_object_terms( $object_id, $terms, $taxonomy, $append = false ) {
			global $wpdb;

			$object_id = (int) $object_id;

			if ( !is_array($terms) )
				$terms = array($terms);

			$old_tt_ids =  wp_list_pluck( $this->get_object_terms( $object_id, $taxonomy, array( 'fields' => 'tt_ids', 'orderby' => 'none' ) ), 'term_id' );

			$tt_ids = array();
			$term_ids = array();
			$new_tt_ids = array();

			foreach ( (array) $terms as $term) {
				if ( !strlen(trim($term)) )
					continue;

				if ( !$term_info = term_exists($term, $taxonomy) ) {
					// Skip if a non-existent term ID is passed.
					if ( is_int($term) )
						continue;

					$args = array(
						'import_id' => $term->term_id
					);
					$term_info = $this->wp_insert_term($term, $taxonomy, $args );
				}
				if ( is_wp_error($term_info) )
					return $term_info;
				$term_ids[] = $term_info['term_id'];
				$tt_id = $term_info['term_taxonomy_id'];
				$tt_ids[] = $tt_id;

				if ( $wpdb->get_var( $wpdb->prepare( "SELECT term_taxonomy_id FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id = %d", $object_id, $tt_id ) ) )
					continue;

				/**
				 * Fires immediately before an object-term relationship is added.
				 *
				 * @since 2.9.0
				 *
				 * @param int $object_id Object ID.
				 * @param int $tt_id     Term taxonomy ID.
				 */
				do_action( 'add_term_relationship', $object_id, $tt_id );
				$wpdb->insert( $wpdb->term_relationships, array( 'object_id' => $object_id, 'term_taxonomy_id' => $tt_id ) );

				/**
				 * Fires immediately after an object-term relationship is added.
				 *
				 * @since 2.9.0
				 *
				 * @param int $object_id Object ID.
				 * @param int $tt_id     Term taxonomy ID.
				 */
				do_action( 'added_term_relationship', $object_id, $tt_id );
				$new_tt_ids[] = $tt_id;
			}

			if ( $new_tt_ids )
				wp_update_term_count( $new_tt_ids, $taxonomy );

			if ( ! $append ) {
				$delete_tt_ids = array_diff( $old_tt_ids, $tt_ids );

				if ( $delete_tt_ids ) {
					$in_delete_tt_ids = "'" . implode( "', '", $delete_tt_ids ) . "'";
					$delete_term_ids = $wpdb->get_col( $wpdb->prepare( "SELECT tt.term_id FROM $wpdb->term_taxonomy AS tt WHERE tt.taxonomy = %s AND tt.term_taxonomy_id IN ($in_delete_tt_ids)", $taxonomy ) );
					$delete_term_ids = array_map( 'intval', $delete_term_ids );

					$remove = $this->_remove_object_terms( $object_id, $delete_term_ids, $taxonomy );
					if ( is_wp_error( $remove ) ) {
						return $remove;
					}
				}
			}


			wp_cache_delete( $object_id, $taxonomy . '_relationships' );

			/**
			 * Fires after an object's terms have been set.
			 *
			 * @since 2.8.0
			 *
			 * @param int    $object_id  Object ID.
			 * @param array  $terms      An array of object terms.
			 * @param array  $tt_ids     An array of term taxonomy IDs.
			 * @param string $taxonomy   Taxonomy slug.
			 * @param bool   $append     Whether to append new terms to the old terms.
			 * @param array  $old_tt_ids Old array of term taxonomy IDs.
			 */
			do_action( 'set_object_terms', $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids );
			return $tt_ids;
		}


		public function get_terms( $taxonomies = array(), $args = '' ) {
			global $wpdb;
			$empty_array = array();

			$single_taxonomy = ! is_array( $taxonomies ) || 1 === count( $taxonomies );
			if ( ! is_array( $taxonomies ) ) {
				$taxonomies = array( $taxonomies );
			}

			$defaults = array('orderby' => 'name', 'order' => 'ASC',
				'hide_empty' => true, 'exclude' => array(), 'exclude_tree' => array(), 'include' => array(),
				'number' => '', 'fields' => 'all', 'slug' => '', 'parent' => '',
				'hierarchical' => true, 'child_of' => 0, 'get' => '', 'name__like' => '', 'description__like' => '',
				'pad_counts' => false, 'offset' => '', 'search' => '', 'cache_domain' => 'core' );
			$args = wp_parse_args( $args, $defaults );
			$args['number'] = absint( $args['number'] );
			$args['offset'] = absint( $args['offset'] );
			if ( !$single_taxonomy || ! is_taxonomy_hierarchical( reset( $taxonomies ) ) ||
				( '' !== $args['parent'] && 0 !== $args['parent'] ) ) {
				$args['child_of'] = 0;
				$args['hierarchical'] = false;
				$args['pad_counts'] = false;
			}

			if ( 'all' == $args['get'] ) {
				$args['child_of'] = 0;
				$args['hide_empty'] = 0;
				$args['hierarchical'] = false;
				$args['pad_counts'] = false;
			}

			/**
			 * Filter the terms query arguments.
			 *
			 * @since 3.1.0
			 *
			 * @param array        $args       An array of arguments.
			 * @param string|array $taxonomies A taxonomy or array of taxonomies.
			 */
			$args = apply_filters( 'get_terms_args', $args, $taxonomies );

			$child_of = $args['child_of'];
			if ( $child_of ) {
				$hierarchy = _get_term_hierarchy( reset( $taxonomies ) );
				if ( ! isset( $hierarchy[ $child_of ] ) ) {
					return $empty_array;
				}
			}

			$parent = $args['parent'];
			if ( $parent ) {
				$hierarchy = _get_term_hierarchy( reset( $taxonomies ) );
				if ( ! isset( $hierarchy[ $parent ] ) ) {
					return $empty_array;
				}
			}

			// $args can be whatever, only use the args defined in defaults to compute the key
			$filter_key = ( has_filter('list_terms_exclusions') ) ? serialize($GLOBALS['wp_filter']['list_terms_exclusions']) : '';
			$key = md5( serialize( wp_array_slice_assoc( $args, array_keys( $defaults ) ) ) . serialize( $taxonomies ) . $filter_key );
			$last_changed = wp_cache_get( 'last_changed', 'terms' );
			if ( ! $last_changed ) {
				$last_changed = microtime();
				wp_cache_set( 'last_changed', $last_changed, 'terms' );
			}
			$cache_key = "get_terms:$key:$last_changed";
			$cache = wp_cache_get( $cache_key, 'terms' );
			if ( false !== $cache ) {

				/**
				 * Filter the given taxonomy's terms cache.
				 *
				 * @since 2.3.0
				 *
				 * @param array        $cache      Cached array of terms for the given taxonomy.
				 * @param string|array $taxonomies A taxonomy or array of taxonomies.
				 * @param array        $args       An array of arguments to get terms.
				 */
				$cache = apply_filters( 'get_terms', $cache, $taxonomies, $args );
				return $cache;
			}

			$_orderby = strtolower( $args['orderby'] );
			if ( 'count' == $_orderby ) {
				$orderby = 'tt.count';
			} else if ( 'name' == $_orderby ) {
				$orderby = 't.name';
			} else if ( 'slug' == $_orderby ) {
				$orderby = 't.slug';
			} else if ( 'term_group' == $_orderby ) {
				$orderby = 't.term_group';
			} else if ( 'none' == $_orderby ) {
				$orderby = '';
			} elseif ( empty($_orderby) || 'id' == $_orderby ) {
				$orderby = 't.term_id';
			} else {
				$orderby = 't.name';
			}
			/**
			 * Filter the ORDERBY clause of the terms query.
			 *
			 * @since 2.8.0
			 *
			 * @param string       $orderby    ORDERBY clause of the terms query.
			 * @param array        $args       An array of terms query arguments.
			 * @param string|array $taxonomies A taxonomy or array of taxonomies.
			 */
			$orderby = apply_filters( 'get_terms_orderby', $orderby, $args, $taxonomies );

			$order = strtoupper( $args['order'] );
			if ( ! empty( $orderby ) ) {
				$orderby = "ORDER BY $orderby";
			} else {
				$order = '';
			}

			if ( '' !== $order && ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
				$order = 'ASC';
			}

			$where = "tt.taxonomy IN ('" . implode("', '", $taxonomies) . "')";

			$exclude = $args['exclude'];
			$exclude_tree = $args['exclude_tree'];
			$include = $args['include'];

			$inclusions = '';
			if ( ! empty( $include ) ) {
				$exclude = '';
				$exclude_tree = '';
				$inclusions = implode( ',', wp_parse_id_list( $include ) );
			}

			if ( ! empty( $inclusions ) ) {
				$inclusions = ' AND t.term_id IN ( ' . $inclusions . ' )';
				$where .= $inclusions;
			}

			if ( ! empty( $exclude_tree ) ) {
				$exclude_tree = wp_parse_id_list( $exclude_tree );
				$excluded_children = $exclude_tree;
				foreach ( $exclude_tree as $extrunk ) {
					$excluded_children = array_merge(
						$excluded_children,
						(array) get_terms( $taxonomies[0], array( 'child_of' => intval( $extrunk ), 'fields' => 'ids', 'hide_empty' => 0 ) )
					);
				}
				$exclusions = implode( ',', array_map( 'intval', $excluded_children ) );
			} else {
				$exclusions = '';
			}

			if ( ! empty( $exclude ) ) {
				$exterms = wp_parse_id_list( $exclude );
				if ( empty( $exclusions ) ) {
					$exclusions = implode( ',', $exterms );
				} else {
					$exclusions .= ', ' . implode( ',', $exterms );
				}
			}

			if ( ! empty( $exclusions ) ) {
				$exclusions = ' AND t.term_id NOT IN (' . $exclusions . ')';
			}

			/**
			 * Filter the terms to exclude from the terms query.
			 *
			 * @since 2.3.0
			 *
			 * @param string       $exclusions NOT IN clause of the terms query.
			 * @param array        $args       An array of terms query arguments.
			 * @param string|array $taxonomies A taxonomy or array of taxonomies.
			 */
			$exclusions = apply_filters( 'list_terms_exclusions', $exclusions, $args, $taxonomies );

			if ( ! empty( $exclusions ) ) {
				$where .= $exclusions;
			}

			if ( ! empty( $args['slug'] ) ) {
				$slug = sanitize_title( $args['slug'] );
				$where .= " AND t.slug = '$slug'";
			}

			if ( ! empty( $args['name__like'] ) ) {
				$where .= $wpdb->prepare( " AND t.name LIKE %s", '%' . $wpdb->esc_like( $args['name__like'] ) . '%' );
			}

			if ( ! empty( $args['description__like'] ) ) {
				$where .= $wpdb->prepare( " AND tt.description LIKE %s", '%' . $wpdb->esc_like( $args['description__like'] ) . '%' );
			}

			if ( '' !== $parent ) {
				$parent = (int) $parent;
				$where .= " AND tt.parent = '$parent'";
			}

			$hierarchical = $args['hierarchical'];
			if ( 'count' == $args['fields'] ) {
				$hierarchical = false;
			}
			if ( $args['hide_empty'] && !$hierarchical ) {
				$where .= ' AND tt.count > 0';
			}

			$number = $args['number'];
			$offset = $args['offset'];

			// don't limit the query results when we have to descend the family tree
			if ( $number && ! $hierarchical && ! $child_of && '' === $parent ) {
				if ( $offset ) {
					$limits = 'LIMIT ' . $offset . ',' . $number;
				} else {
					$limits = 'LIMIT ' . $number;
				}
			} else {
				$limits = '';
			}

			if ( ! empty( $args['search'] ) ) {
				$like = '%' . $wpdb->esc_like( $args['search'] ) . '%';
				$where .= $wpdb->prepare( ' AND ((t.name LIKE %s) OR (t.slug LIKE %s))', $like, $like );
			}

			$selects = array();
			switch ( $args['fields'] ) {
				case 'all':
					$selects = array( 't.*', 'tt.*' );
					break;
				case 'ids':
				case 'id=>parent':
					$selects = array( 't.term_id', 'tt.parent', 'tt.count' );
					break;
				case 'names':
					$selects = array( 't.term_id', 'tt.parent', 'tt.count', 't.name' );
					break;
				case 'count':
					$orderby = '';
					$order = '';
					$selects = array( 'COUNT(*)' );
					break;
				case 'id=>name':
					$selects = array( 't.term_id', 't.name' );
					break;
				case 'id=>slug':
					$selects = array( 't.term_id', 't.slug' );
					break;
			}

			$_fields = $args['fields'];

			/**
			 * Filter the fields to select in the terms query.
			 *
			 * @since 2.8.0
			 *
			 * @param array        $selects    An array of fields to select for the terms query.
			 * @param array        $args       An array of term query arguments.
			 * @param string|array $taxonomies A taxonomy or array of taxonomies.
			 */
			$fields = implode( ', ', apply_filters( 'get_terms_fields', $selects, $args, $taxonomies ) );

			$join = "INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id";

			$pieces = array( 'fields', 'join', 'where', 'orderby', 'order', 'limits' );

			/**
			 * Filter the terms query SQL clauses.
			 *
			 * @since 3.1.0
			 *
			 * @param array        $pieces     Terms query SQL clauses.
			 * @param string|array $taxonomies A taxonomy or array of taxonomies.
			 * @param array        $args       An array of terms query arguments.
			 */
			$clauses = apply_filters( 'terms_clauses', compact( $pieces ), $taxonomies, $args );
			$fields = isset( $clauses[ 'fields' ] ) ? $clauses[ 'fields' ] : '';
			$join = isset( $clauses[ 'join' ] ) ? $clauses[ 'join' ] : '';
			$where = isset( $clauses[ 'where' ] ) ? $clauses[ 'where' ] : '';
			$orderby = isset( $clauses[ 'orderby' ] ) ? $clauses[ 'orderby' ] : '';
			$order = isset( $clauses[ 'order' ] ) ? $clauses[ 'order' ] : '';
			$limits = isset( $clauses[ 'limits' ] ) ? $clauses[ 'limits' ] : '';

			$query = "SELECT $fields FROM $wpdb->terms AS t $join WHERE $where $orderby $order $limits";

			if ( 'count' == $_fields ) {
				$term_count = $wpdb->get_var($query);
				return $term_count;
			}

			$terms = $wpdb->get_results($query);
			if ( 'all' == $_fields ) {
				update_term_cache($terms);
			}

			if ( empty($terms) ) {
				wp_cache_add( $cache_key, array(), 'terms', DAY_IN_SECONDS );

				/** This filter is documented in wp-includes/taxonomy.php */
				$terms = apply_filters( 'get_terms', array(), $taxonomies, $args );
				return $terms;
			}

			if ( $child_of ) {
				$children = _get_term_hierarchy( reset( $taxonomies ) );
				if ( ! empty( $children ) ) {
					$terms = _get_term_children( $child_of, $terms, reset( $taxonomies ) );
				}
			}

			// Update term counts to include children.
			if ( $args['pad_counts'] && 'all' == $_fields ) {
				_pad_term_counts( $terms, reset( $taxonomies ) );
			}
			// Make sure we show empty categories that have children.
			if ( $hierarchical && $args['hide_empty'] && is_array( $terms ) ) {
				foreach ( $terms as $k => $term ) {
					if ( ! $term->count ) {
						$children = get_term_children( $term->term_id, reset( $taxonomies ) );
						if ( is_array( $children ) ) {
							foreach ( $children as $child_id ) {
								$child = get_term( $child_id, reset( $taxonomies ) );
								if ( $child->count ) {
									continue 2;
								}
							}
						}

						// It really is empty
						unset($terms[$k]);
					}
				}
			}
			reset( $terms );

			$_terms = array();
			if ( 'id=>parent' == $_fields ) {
				while ( $term = array_shift( $terms ) ) {
					$_terms[$term->term_id] = $term->parent;
				}
			} elseif ( 'ids' == $_fields ) {
				while ( $term = array_shift( $terms ) ) {
					$_terms[] = $term->term_id;
				}
			} elseif ( 'names' == $_fields ) {
				while ( $term = array_shift( $terms ) ) {
					$_terms[] = $term->name;
				}
			} elseif ( 'id=>name' == $_fields ) {
				while ( $term = array_shift( $terms ) ) {
					$_terms[$term->term_id] = $term->name;
				}
			} elseif ( 'id=>slug' == $_fields ) {
				while ( $term = array_shift( $terms ) ) {
					$_terms[$term->term_id] = $term->slug;
				}
			}

			if ( ! empty( $_terms ) ) {
				$terms = $_terms;
			}

			if ( $number && is_array( $terms ) && count( $terms ) > $number ) {
				$terms = array_slice( $terms, $offset, $number );
			}

			wp_cache_add( $cache_key, $terms, 'terms', DAY_IN_SECONDS );

			/** This filter is documented in wp-includes/taxonomy */
			$terms = apply_filters( 'get_terms', $terms, $taxonomies, $args );
			return $terms;
		}


		function remove_object_terms( $object_id, $terms, $taxonomy ) {
			global $wpdb;

			$object_id = (int) $object_id;

			if ( ! is_array( $terms ) ) {
				$terms = array( $terms );
			}

			$tt_ids = array();

			foreach ( (array) $terms as $term ) {
				if ( ! strlen( trim( $term ) ) ) {
					continue;
				}

				if ( ! $term_info = term_exists( $term, $taxonomy ) ) {
					// Skip if a non-existent term ID is passed.
					if ( is_int( $term ) ) {
						continue;
					}
				}

				if ( is_wp_error( $term_info ) ) {
					return $term_info;
				}

				$tt_ids[] = $term_info['term_taxonomy_id'];
			}

			if ( $tt_ids ) {
				$in_tt_ids = "'" . implode( "', '", $tt_ids ) . "'";

				/**
				 * Fires immediately before an object-term relationship is deleted.
				 *
				 * @since 2.9.0
				 *
				 * @param int   $object_id Object ID.
				 * @param array $tt_ids    An array of term taxonomy IDs.
				 */
				do_action( 'delete_term_relationships', $object_id, $tt_ids );
				$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id IN ($in_tt_ids)", $object_id ) );

				/**
				 * Fires immediately after an object-term relationship is deleted.
				 *
				 * @since 2.9.0
				 *
				 * @param int   $object_id Object ID.
				 * @param array $tt_ids    An array of term taxonomy IDs.
				 */
				do_action( 'deleted_term_relationships', $object_id, $tt_ids );
				wp_update_term_count( $tt_ids, $taxonomy );

				return (bool) $deleted;
			}

			return false;
		}

		function wp_insert_term( $term, $taxonomy, $args = array() ) {
			global $wpdb;

			if ( ! taxonomy_exists($taxonomy) ) {
				return new WP_Error('invalid_taxonomy', __('Invalid taxonomy'));
			}
			/**
			 * Filter a term before it is sanitized and inserted into the database.
			 *
			 * @since 3.0.0
			 *
			 * @param string $term     The term to add or update.
			 * @param string $taxonomy Taxonomy slug.
			 */
			$term = apply_filters( 'pre_insert_term', $term, $taxonomy );
			if ( is_wp_error( $term ) ) {
				return $term;
			}
			if ( is_int($term) && 0 == $term ) {
				return new WP_Error('invalid_term_id', __('Invalid term ID'));
			}
			if ( '' == trim($term) ) {
				return new WP_Error('empty_term_name', __('A name is required for this term'));
			}
			$defaults = array( 'alias_of' => '', 'description' => '', 'parent' => 0, 'slug' => '');
			$args = wp_parse_args( $args, $defaults );

			if ( $args['parent'] > 0 && ! term_exists( (int) $args['parent'] ) ) {
				return new WP_Error( 'missing_parent', __( 'Parent term does not exist.' ) );
			}
			$args['name'] = $term;
			$args['taxonomy'] = $taxonomy;
			$args = sanitize_term($args, $taxonomy, 'db');

			// expected_slashed ($name)
			$name = wp_unslash( $args['name'] );
			$description = wp_unslash( $args['description'] );
			$parent = (int) $args['parent'];

			$slug_provided = ! empty( $args['slug'] );
			if ( ! $slug_provided ) {
				$_name = trim( $name );
				$existing_term = get_term_by( 'name', $_name, $taxonomy );
				if ( $existing_term ) {
					$slug = $existing_term->slug;
				} else {
					$slug = sanitize_title( $name );
				}
			} else {
				$slug = $args['slug'];
			}

			$term_group = 0;
			if ( $args['alias_of'] ) {
				$alias = $wpdb->get_row( $wpdb->prepare( "SELECT term_id, term_group FROM $wpdb->terms WHERE slug = %s", $args['alias_of'] ) );
				if ( $alias->term_group ) {
					// The alias we want is already in a group, so let's use that one.
					$term_group = $alias->term_group;
				} else {
					// The alias isn't in a group, so let's create a new one and firstly add the alias term to it.
					$term_group = $wpdb->get_var("SELECT MAX(term_group) FROM $wpdb->terms") + 1;

					/**
					 * Fires immediately before the given terms are edited.
					 *
					 * @since 2.9.0
					 *
					 * @param int    $term_id  Term ID.
					 * @param string $taxonomy Taxonomy slug.
					 */
					do_action( 'edit_terms', $alias->term_id, $taxonomy );
					$wpdb->update($wpdb->terms, compact('term_group'), array('term_id' => $alias->term_id) );

					/**
					 * Fires immediately after the given terms are edited.
					 *
					 * @since 2.9.0
					 *
					 * @param int    $term_id  Term ID
					 * @param string $taxonomy Taxonomy slug.
					 */
					do_action( 'edited_terms', $alias->term_id, $taxonomy );
				}
			}

			if ( $term_id = term_exists($slug) ) {
				$existing_term = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $wpdb->terms WHERE term_id = %d", $term_id), ARRAY_A );
				// We've got an existing term in the same taxonomy, which matches the name of the new term:
				if ( is_taxonomy_hierarchical($taxonomy) && $existing_term['name'] == $name && $exists = term_exists( (int) $term_id, $taxonomy ) ) {
					// Hierarchical, and it matches an existing term, Do not allow same "name" in the same level.
					$siblings = get_terms($taxonomy, array('fields' => 'names', 'get' => 'all', 'parent' => $parent ) );
					if ( in_array($name, $siblings) ) {
						if ( $slug_provided ) {
							return new WP_Error( 'term_exists', __( 'A term with the name and slug provided already exists with this parent.' ), $exists['term_id'] );
						} else {
							return new WP_Error( 'term_exists', __( 'A term with the name provided already exists with this parent.' ), $exists['term_id'] );
						}
					} else {
						$slug = wp_unique_term_slug($slug, (object) $args);
						if ( false === $wpdb->insert( $wpdb->terms, compact( 'name', 'slug', 'term_group' ) ) ) {
							return new WP_Error('db_insert_error', __('Could not insert term into the database'), $wpdb->last_error);
						}
						$term_id = (int) $wpdb->insert_id;
					}
				} elseif ( $existing_term['name'] != $name ) {
					// We've got an existing term, with a different name, Create the new term.
					$slug = wp_unique_term_slug($slug, (object) $args);
					if ( false === $wpdb->insert( $wpdb->terms, compact( 'name', 'slug', 'term_group' ) ) ) {
						return new WP_Error('db_insert_error', __('Could not insert term into the database'), $wpdb->last_error);
					}
					$term_id = (int) $wpdb->insert_id;
				} elseif ( $exists = term_exists( (int) $term_id, $taxonomy ) )  {
					// Same name, same slug.
					return new WP_Error( 'term_exists', __( 'A term with the name and slug provided already exists.' ), $exists['term_id'] );
				}
			} else {
				// This term does not exist at all in the database, Create it.
				$import_id = false;
				if ( isset( $args['import_id'] ) ) {
					// Try to insert the term with this ID
					$_result = $wpdb->get_var( $wpdb->prepare( "SELECT term_id FROM $wpdb->terms WHERE term_id = %d", $args['import_id'] ) );

					if ( ! $_result )
						$import_id = absint( $args['import_id'] );

				}

				$slug = wp_unique_term_slug($slug, (object) $args);

				if ( $import_id )
					$result = $wpdb->insert( $wpdb->terms, compact( 'term_id', 'name', 'slug', 'term_group' ) );
				else
					$result = $wpdb->insert( $wpdb->terms, compact( 'name', 'slug', 'term_group' ) );

				if ( false === $result ) {
					return new WP_Error('db_insert_error', __('Could not insert term into the database'), $wpdb->last_error);
				}
				$term_id = (int) $wpdb->insert_id;
			}

			// Seems unreachable, However, Is used in the case that a term name is provided, which sanitizes to an empty string.
			if ( empty($slug) ) {
				$slug = sanitize_title($slug, $term_id);

				/** This action is documented in wp-includes/taxonomy.php */
				do_action( 'edit_terms', $term_id, $taxonomy );
				$wpdb->update( $wpdb->terms, compact( 'slug' ), compact( 'term_id' ) );

				/** This action is documented in wp-includes/taxonomy.php */
				do_action( 'edited_terms', $term_id, $taxonomy );
			}

			$tt_id = $wpdb->get_var( $wpdb->prepare( "SELECT tt.term_taxonomy_id FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.term_id = %d", $taxonomy, $term_id ) );

			if ( !empty($tt_id) ) {
				return array('term_id' => $term_id, 'term_taxonomy_id' => $tt_id);
			}
			$wpdb->insert( $wpdb->term_taxonomy, compact( 'term_id', 'taxonomy', 'description', 'parent') + array( 'count' => 0 ) );
			$tt_id = (int) $wpdb->insert_id;

			/**
			 * Fires immediately after a new term is created, before the term cache is cleaned.
			 *
			 * @since 2.3.0
			 *
			 * @param int    $term_id  Term ID.
			 * @param int    $tt_id    Term taxonomy ID.
			 * @param string $taxonomy Taxonomy slug.
			 */
			do_action( "create_term", $term_id, $tt_id, $taxonomy );

			/**
			 * Fires after a new term is created for a specific taxonomy.
			 *
			 * The dynamic portion of the hook name, $taxonomy, refers
			 * to the slug of the taxonomy the term was created for.
			 *
			 * @since 2.3.0
			 *
			 * @param int $term_id Term ID.
			 * @param int $tt_id   Term taxonomy ID.
			 */
			do_action( "create_$taxonomy", $term_id, $tt_id );

			/**
			 * Filter the term ID after a new term is created.
			 *
			 * @since 2.3.0
			 *
			 * @param int $term_id Term ID.
			 * @param int $tt_id   Taxonomy term ID.
			 */
			$term_id = apply_filters( 'term_id_filter', $term_id, $tt_id );

			clean_term_cache($term_id, $taxonomy);

			/**
			 * Fires after a new term is created, and after the term cache has been cleaned.
			 *
			 * @since 2.3.0
			 */
			do_action( "created_term", $term_id, $tt_id, $taxonomy );

			/**
			 * Fires after a new term in a specific taxonomy is created, and after the term
			 * cache has been cleaned.
			 *
			 * @since 2.3.0
			 *
			 * @param int $term_id Term ID.
			 * @param int $tt_id   Term taxonomy ID.
			 */
			do_action( "created_$taxonomy", $term_id, $tt_id );

			return array('term_id' => $term_id, 'term_taxonomy_id' => $tt_id);
		}

	}

}