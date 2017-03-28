<?php
/**
 * The template for displaying posts in the Status post format
 *
 * @since 1.0.6
 */
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php if ( ! is_single() ) { ?>
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
		<?php } ?>
				<div class="post-format"><?php _e( 'Status', 'ward' ); ?></div>
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 60 ); ?>
				<div class="author"><span class="vcard author"><span class="fn"><?php the_author(); ?></span></span></div>

				<div class="entry-content">
					<time class="published" datetime="<?php echo get_the_date( 'Y-m-d' ) . 'T' . get_the_time( 'H:i' ) . 'Z'; ?>">
						<?php printf( __( 'Posted on %1$s at %2$s', 'ward' ), get_the_date(), get_the_time() );	?>
					</time>

					<?php the_content( __( 'Read more &rarr;', 'ward' ) ); ?>
			    </div><!-- .entry-content -->

			    <?php get_template_part( 'content', 'footer' ); ?>
		<?php if ( ! is_single() ) { ?>
				</div>
			</div>
		</div>
		<?php } ?>
    </article>