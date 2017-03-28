<?php
/**
 * The template for displaying a page.
 *
 * @since 1.0.6
 */
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php
		if ( is_search() ) :
		the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
		elseif ( is_front_page() ) :
			the_title( '<h2 class="entry-title">', '</h2>' );
		else :
			the_title( '<h1 class="entry-title">', '</h1>' );
		endif;
		?>

	    <div class="entry-content">
		    <?php the_content( __( 'Read more &rarr;', 'ward' ) ); ?>
	    </div><!-- .entry-content -->

	    <?php get_template_part( 'content', 'footer' ); ?>
	</article><!-- #post-<?php the_ID(); ?> -->