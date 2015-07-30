<?php
/**
 * @package Make Plus
 */
?>
<div id="ttfmp-notice-<?php echo esc_attr( $id ); ?>" class="notice notice-<?php echo esc_attr( $type ); ?> <?php echo esc_attr( $legacy_class ); ?>">
	<?php if ( true === $dismiss ) : ?>
		<a class="ttfmp-dismiss" href="#" data-nonce="<?php echo esc_attr( $nonce ); ?>"><?php _e( 'Hide', 'make-plus' ); ?></a>
	<?php endif; ?>
	<?php echo wpautop( $message ); ?>
</div>