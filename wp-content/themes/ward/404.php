<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @since 1.0.0
 */
get_header(); ?>
	<div id="primary" <?php bavotasan_primary_attr(); ?>>

		<article id="post-0" class="post error404 not-found">
			<img src="<?php echo BAVOTASAN_THEME_URL; ?>/library/images/404.png" class="aligncenter" alt="" />

    	   	<h1 class="entry-title"><?php _e( '404 Error', 'ward' ); ?></h1>

	        <div class="entry-content">
	            <p><?php _e( "Sorry. We can't seem to find the page you're looking for.", 'ward' ); ?></p>
	        </div>
	    </article>

	</div>
<?php get_footer(); ?>