<?php
/**
 * @package Make Plus
 */

global $ttfmake_section_data;

$section_name = "ttfmake-section[{{ get('parentID') }}][panels-items][{{ id }}]";
$combined_id = "{{ get('parentID') }}-{{ id }}";
$overlay_id  = "ttfmake-overlay-" . $combined_id;
?>

<div id="ttfmp-panels-item-{{ id }}" class="ttfmp-panels-item" data-id="{{ id }}" data-section-type="panelsItem">
    <div title="<?php esc_attr_e( 'Drag-and-drop this panel into place', 'make-plus' ); ?>" class="ttfmake-sortable-handle">
        <div class="sortable-background"></div>
    </div>

    <div class="wrapper-panels-item-links">
        <a href="#" class="configure-panels-item-link ttfmake-section-configure ttfmake-overlay-open" title="<?php esc_attr_e( 'Configure panel', 'make-plus' ); ?>" data-overlay="#<?php echo $overlay_id; ?>">
            <span>
                <?php esc_html_e( 'Configure panel', 'make-plus' ); ?>
            </span>
        </a>
        <a href="#" class="edit-content-link edit-panels-item-link{{ get('item-content') && get('item-content').length ? ' item-has-content': '' }}" title="<?php esc_attr_e( 'Edit content', 'make-plus' ); ?>" data-textarea="ttfmake-content-<?php echo $combined_id; ?>" data-iframe="ttfmake-iframe-<?php echo $combined_id; ?>">
            <span>
                <?php esc_html_e( 'Edit content', 'make-plus' ); ?>
            </span>
        </a>
        <a href="#" class="remove-panels-item-link ttfmp-panels-item-remove" title="<?php esc_attr_e( 'Delete panel', 'make-plus' ); ?>">
            <span>
                <?php esc_html_e( 'Delete panel', 'make-plus' ); ?>
            </span>
        </a>
    </div>

    <?php
    // Add the content editor
    ttfmake_get_builder_base()->add_frame( $combined_id, 'item-content', '', '', true );

    global $ttfmake_overlay_class, $ttfmake_overlay_id, $ttfmake_overlay_title;
    $ttfmake_overlay_class = 'ttfmake-configuration-overlay';
    $ttfmake_overlay_id    = $overlay_id;

    get_template_part( '/inc/builder/core/templates/overlay', 'header' );

    $inputs = $ttfmake_section_data['section']['item'];

    // Print the inputs
    $output = '';

    foreach ( $inputs as $input ) {
        if ( isset( $input['type'] ) && isset( $input['name'] ) ) {
            $output .= ttfmake_create_input( $section_name, $input, array() );
        }
    }

    echo $output;

    get_template_part( '/inc/builder/core/templates/overlay', 'footer' ); ?>
</div>