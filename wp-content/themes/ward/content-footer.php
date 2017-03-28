<?php
/**
 * The template for displaying article footers
 *
 * @since 1.0.6
 */
if ( is_singular() ) {
?>
	<footer class="clearfix">
	    <?php
	    wp_link_pages( array( 'before' => '<p id="pages">' . __( 'Pages:', 'ward' ) ) );
	    edit_post_link( __( '(edit)', 'ward' ), '<p class="edit-link">', '</p>' );
		the_tags( '<p class="tags"><i class="fa fa-tags"></i> <span>' . __( 'Tags:', 'ward' ) . '</span>', ' ', '</p>' );
	    ?>
	</footer><!-- .entry -->
<?php
}