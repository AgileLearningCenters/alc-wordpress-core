<?php
/**
 * @package Make Plus
 */

ttfmake_load_section_header();

global $ttfmake_section_data;
?>

<div class="ttfmp-panels">
    <div class="ttfmp-panels-stage"></div>
    <a href="#" class="ttfmp-add-panels-item ttfmp-panels-add-item-link" title="<?php esc_attr_e( 'Add new panel', 'make-plus' ); ?>">
        <div class="ttfmp-panels-add-item">
            <span>
                <?php esc_html_e( 'Add panel', 'make-plus' ); ?>
            </span>
        </div>
    </a>
</div>

<?php ttfmake_load_section_footer(); ?>