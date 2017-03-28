<?php
/**
 * @package Make Plus
 */

ttfmake_load_section_header();

$section_name = 'ttfmake-section[{{ get("id") }}]';
?>
	<div class="ttfmake-edd-downloads-options-container">
		<div class="ttfmake-edd-downloads-options-column">
			<div class="ttfmake-taxonomy-select-wrapper">
				<h4><?php esc_html_e( 'From', 'make-plus' ); ?></h4>
				<select id="<?php echo $section_name; ?>[taxonomy]" name="<?php echo $section_name; ?>[taxonomy]"
                        data-model-attr="taxonomy">
					<?php foreach ( ttfmake_get_section_choices( 'taxonomy', 'edd-downloads' ) as $value => $label ) : ?>
						<option value="{{ get('taxonomy') }}"{{ (get('taxonomy') == '<?php echo $value; ?>') ? ' selected' : '' }}{{ (get('taxonomy') && get('taxonomy').indexOf('ttfmp-disabled') !== -1) ? ' disabled="disabled"' : '' }}>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="ttfmake-sortby-select-wrapper">
				<h4><?php esc_html_e( 'Sort', 'make-plus' ); ?></h4>
				<select id="<?php echo $section_name; ?>[sortby]" name="<?php echo $section_name; ?>[sortby]" data-model-attr="sortby">
					<?php foreach ( ttfmake_get_section_choices( 'sortby', 'edd-downloads' ) as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"{{ (get('sortby') == '<?php echo $value; ?>') ? ' selected' : '' }}>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="ttfmake-edd-downloads-options-column">
			<div class="ttfmake-columns-select-wrapper">
				<h4><?php esc_html_e( 'Columns', 'make-plus' ); ?></h4>
				<select id="<?php echo $section_name; ?>[columns]" name="<?php echo $section_name; ?>[columns]" data-model-attr="columns">
					<?php foreach ( ttfmake_get_section_choices( 'columns', 'edd-downloads' ) as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"{{ (get('columns') == '<?php echo $value; ?>') ? ' selected' : '' }}>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<h4 class="ttfmake-edd-downloads-options-title">
				<?php esc_html_e( 'Number to show', 'make-plus' ); ?>
			</h4>
			<input id="<?php echo $section_name; ?>[count]" class="code" type="number" name="<?php echo $section_name; ?>[count]" value="{{ get('count') }}" data-model-attr="count" />
			<p><?php echo wp_kses( __( 'To show all, set to <code>-1</code>.', 'make-plus' ), wp_kses_allowed_html() ); ?></p>
		</div>

		<div class="ttfmake-edd-downloads-options-column">
			<h4><?php esc_html_e( 'Display', 'make-plus' ); ?></h4>
			<p>
				<input id="<?php echo $section_name; ?>[thumb]" type="checkbox" name="<?php echo $section_name; ?>[thumb]" value="1" data-model-attr="thumb"{{ (parseInt(get('thumb'), 10) == 1) ? ' checked': '' }} />
				<label for="<?php echo $section_name; ?>[thumb]">
					<?php esc_html_e( 'Show thumbnail image', 'make-plus' ); ?>
				</label>
			</p>
			<p>
				<input id="<?php echo $section_name; ?>[price]" type="checkbox" name="<?php echo $section_name; ?>[price]" value="1" data-model-attr="price"{{ (parseInt(get('thumb'), 10) == 1) ? ' checked' : '' }} />
				<label for="<?php echo $section_name; ?>[price]">
					<?php esc_html_e( 'Show price', 'make-plus' ); ?>
				</label>
			</p>
			<p>
				<input id="<?php echo $section_name; ?>[addcart]" type="checkbox" name="<?php echo $section_name; ?>[addcart]" value="1" data-model-attr="addcart"{{ (parseInt(get('addcart'), 10) == 1) ? ' checked': '' }} />
				<label for="<?php echo $section_name; ?>[addcart]">
					<?php esc_html_e( 'Show purchase button', 'make-plus' ); ?>
				</label>
			</p>

			<div class="ttfmake-details-select-wrapper">
				<h4><?php esc_html_e( 'Details', 'make-plus' ); ?></h4>
				<select id="<?php echo $section_name; ?>[details]" name="<?php echo $section_name; ?>[details]" data-model-attr="details">
					<?php foreach ( ttfmake_get_section_choices( 'details', 'edd-downloads' ) as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"{{ (get('details') == '<?php echo $value; ?>') ? ' selected' : '' }}>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>

	<div class="clear"></div>

	<input type="hidden" class="ttfmake-section-state" name="<?php echo $section_name; ?>[state]" value="{{ get('state') }}" />

<?php ttfmake_load_section_footer(); ?>