<?php
/**
 * @package Make Plus
 */

ttfmake_load_section_header();

$section_name = 'ttfmake-section[{{ get("id") }}]';
?>
	<div class="ttfmake-woocommerce-product-grid-options-container">
		<div class="ttfmake-woocommerce-product-grid-options-column">
			<div class="ttfmake-type-select-wrapper">
				<h4><?php esc_html_e( 'Show', 'make-plus' ); ?></h4>
				<select id="<?php echo $section_name; ?>[type]" name="<?php echo $section_name; ?>[type]" data-model-attr="type">
					<?php foreach ( ttfmake_get_section_choices( 'type', 'woocommerce-product-grid' ) as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"{{ (get('type') == '<?php echo $value; ?>') ? ' selected' : '' }}>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="ttfmake-taxonomy-select-wrapper">
				<h4><?php esc_html_e( 'From', 'make-plus' ); ?></h4>
				<select id="<?php echo $section_name; ?>[taxonomy]" name="<?php echo $section_name; ?>[taxonomy]" data-model-attr="taxonomy">
					<?php foreach ( ttfmake_get_section_choices( 'taxonomy', 'woocommerce-product-grid' ) as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"{{ (get('taxonomy') == '<?php echo $value; ?>') ? ' selected' : '' }}{{ (get('taxonomy').indexOf('ttfmp-disabled') !== -1) ? ' disabled="disabled"' : '' }}>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="ttfmake-sortby-select-wrapper">
				<h4><?php esc_html_e( 'Sort', 'make-plus' ); ?></h4>
				<select id="<?php echo $section_name; ?>[sortby]" name="<?php echo $section_name; ?>[sortby]" data-model-attr="sortby">
					<?php foreach ( ttfmake_get_section_choices( 'sortby', 'woocommerce-product-grid' ) as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"{{ (get('sortby') == '<?php echo $value; ?>') ? ' selected' : '' }}>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="ttfmake-woocommerce-product-grid-options-column">
			<div class="ttfmake-columns-select-wrapper">
				<h4><?php esc_html_e( 'Columns', 'make-plus' ); ?></h4>
				<select id="<?php echo $section_name; ?>[columns]" name="<?php echo $section_name; ?>[columns]" data-model-attr="columns">
					<?php foreach ( ttfmake_get_section_choices( 'columns', 'woocommerce-product-grid' ) as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"{{ (get('columns') == '<?php echo $value; ?>') ? ' selected' : '' }}>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<h4 class="ttfmake-woocommerce-product-grid-options-title">
				<?php esc_html_e( 'Number to show', 'make-plus' ); ?>
			</h4>
			<input id="<?php echo $section_name; ?>[count]" class="code" type="number" name="<?php echo $section_name; ?>[count]" value="{{ get('count') }}" data-model-attr="count" />
			<p><?php echo wp_kses( __( 'To show all, set to <code>-1</code>.', 'make-plus' ), wp_kses_allowed_html() ); ?></p>
		</div>

		<div class="ttfmake-woocommerce-product-grid-options-column">
			<p>
				<h4><?php esc_html_e( 'Display', 'make-plus' ); ?></h4>
				<input id="<?php echo $section_name; ?>[thumb]" type="checkbox" name="<?php echo $section_name; ?>[thumb]" value="1" data-model-attr="thumb"{{ (get('thumb') == '1') ? ' checked': '' }} />
				<label for="<?php echo $section_name; ?>[thumb]">
					<?php esc_html_e( 'Show product image', 'make-plus' ); ?>
				</label>
			</p>

			<?php if ( get_option( 'woocommerce_enable_review_rating' ) !== 'no' ) : ?>
			<p>
				<input id="<?php echo $section_name; ?>[rating]" type="checkbox" name="<?php echo $section_name; ?>[rating]" value="1" data-model-attr="rating"{{ (get('rating') == '1') ? ' checked': '' }} />
				<label for="<?php echo $section_name; ?>[rating]">
					<?php esc_html_e( 'Show rating', 'make-plus' ); ?>
				</label>
			</p>
			<?php endif; ?>

			<p>
				<input id="<?php echo $section_name; ?>[price]" type="checkbox" name="<?php echo $section_name; ?>[price]" value="1" data-model-attr="price"{{ (get('price') == '1') ? ' checked': '' }} />
				<label for="<?php echo $section_name; ?>[price]">
					<?php esc_html_e( 'Show price', 'make-plus' ); ?>
				</label>
			</p>

			<p>
				<input id="<?php echo $section_name; ?>[addcart]" type="checkbox" name="<?php echo $section_name; ?>[addcart]" value="1" data-model-attr="addcart"{{ (get('addcart') == '1') ? ' checked': '' }} />
				<label for="<?php echo $section_name; ?>[addcart]">
					<?php esc_html_e( 'Show Add to Cart button', 'make-plus' ); ?>
				</label>
			</p>
		</div>
	</div>

	<div class="clear"></div>

	<input type="hidden" class="ttfmake-section-state" name="<?php echo $section_name; ?>[state]" value="{{ get('state') }}" />

<?php ttfmake_load_section_footer(); ?>
