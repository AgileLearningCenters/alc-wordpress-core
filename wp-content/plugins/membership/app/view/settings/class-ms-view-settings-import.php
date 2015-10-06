<?php
/**
 * Displays the Import preview.
 *
 * @since  1.0.0
 * @package Membership2
 * @subpackage Model
 */
class MS_View_Settings_Import extends MS_View {

	/**
	 * Displays the import preview form.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function to_html() {
		$data = apply_filters(
			'ms_import_preview_data_before',
			$this->data['model']->source
		);
		$compact = ! empty( $this->data['compact'] );
		if ( ! is_object( $data ) ) {
			$data = (object) array(
				'memberships' => array(),
				'members' => array(),
				'notes' => array(),
				'settings' => array(),
				'source' => '',
				'export_time' => '',
			);
		}

		// Converts object to array
		$data->memberships = (array) $data->memberships;
		$data->members = (array) $data->members;

		$fields = $this->prepare_fields( $data );

		if ( $compact ) {
			$overview_box = array(
				$fields['batchsize'],
				$fields['sep'],
				$fields['clear_all'],
				$fields['skip'],
				$fields['import'],
			);
		} else {
			$overview_box = array(
				$fields['details'],
				$fields['sep'],
				$fields['batchsize'],
				$fields['sep'],
				$fields['clear_all'],
				$fields['back'],
				$fields['import'],
				$fields['download'],
			);
		}

		ob_start();
		MS_Helper_Html::settings_box(
			$overview_box,
			__( 'Import Overview', MS_TEXT_DOMAIN )
		);

		if ( ! $compact ) {
			MS_Helper_Html::settings_box(
				array( $fields['memberships'] ),
				__( 'List of all Memberships', MS_TEXT_DOMAIN ),
				'',
				'open'
			);

			MS_Helper_Html::settings_box(
				array( $fields['members'] ),
				__( 'List of all Members', MS_TEXT_DOMAIN ),
				'',
				'open'
			);

			MS_Helper_Html::settings_box(
				array( $fields['settings'] ),
				__( 'Imported Settings', MS_TEXT_DOMAIN ),
				'',
				'open'
			);
		}

		echo '<script>window._ms_import_obj = ' . json_encode( $data ) . '</script>';

		$html = ob_get_clean();

		return apply_filters(
			'ms_import_preview_object',
			$html,
			$data
		);
	}

	/**
	 * Prepare the HTML fields that can be displayed
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	protected function prepare_fields( $data ) {
		// List of known Membership types; used to display the nice-name
		$ms_types = MS_Model_Membership::get_types();
		$ms_paytypes = MS_Model_Membership::get_payment_types();

		// Prepare the "Memberships" table
		$memberships = array(
			array(
				__( 'Membership name', MS_TEXT_DOMAIN ),
				__( 'Membership Type', MS_TEXT_DOMAIN ),
				__( 'Payment Type', MS_TEXT_DOMAIN ),
				__( 'Description', MS_TEXT_DOMAIN ),
			),
		);

		foreach ( $data->memberships as $item ) {
			if ( ! isset( $ms_types[$item->type] ) ) {
				$item->type = MS_Model_Membership::TYPE_STANDARD;
			}

			switch ( $item->pay_type ) {
				case 'recurring':
					$payment_type = MS_Model_Membership::PAYMENT_TYPE_RECURRING;
					break;

				case 'finite':
					$payment_type = MS_Model_Membership::PAYMENT_TYPE_FINITE;
					break;

				case 'date':
					$payment_type = MS_Model_Membership::PAYMENT_TYPE_DATE_RANGE;
					break;

				default:
					$payment_type = MS_Model_Membership::PAYMENT_TYPE_PERMANENT;
					break;
			}

			$memberships[] = array(
				$item->name,
				$ms_types[$item->type],
				$ms_paytypes[$payment_type],
				$item->description,
			);
		}

		// Prepare the "Members" table
		$members = array(
			array(
				__( 'Username', MS_TEXT_DOMAIN ),
				__( 'Email', MS_TEXT_DOMAIN ),
				__( 'Subscriptions', MS_TEXT_DOMAIN ),
				__( 'Invoices', MS_TEXT_DOMAIN ),
			),
		);

		foreach ( $data->members as $item ) {
			$inv_count = 0;
			if ( isset( $item->subscriptions )
				&& is_array( $item->subscriptions )
			) {
				foreach ( $item->subscriptions as $registration ) {
					$inv_count += count( $registration->invoices );
				}
			}

			$members[] = array(
				$item->username,
				$item->email,
				count( $item->subscriptions ),
				$inv_count,
			);
		}

		$settings = array();
		foreach ( $data->settings as $setting => $value ) {
			switch ( $setting ) {
				case 'addons':
					$model = MS_Factory::load( 'MS_Model_Addon' );
					$list = $model->get_addon_list();
					$code = '';
					foreach ( $value as $addon => $state ) {
						if ( $state ) {
							$code .= __( 'Activate: ', MS_TEXT_DOMAIN );
						} else {
							$code .= __( 'Dectivate: ', MS_TEXT_DOMAIN );
						}
						$code .= $list[$addon]->name . '<br/>';
					}
					$settings[] = array(
						__( 'Add-Ons', MS_TEXT_DOMAIN ),
						$code,
					);
					break;
			}
		}
		if ( empty( $settings ) ) {
			$settings[] = array(
				'',
				__( '(No settings are changed)', MS_TEXT_DOMAIN ),
			);
		}

		// Prepare the return value.
		$fields = array();

		// Export-Notes
		$notes = '';
		if ( isset( $data->notes ) ) {
			if ( is_scalar( $data->notes ) ) {
				$notes = array( $data->notes );
			}

			$in_sub = false;
			$notes = '<ul class="ms-import-notes">';
			foreach ( $data->notes as $line => $text ) {
				$is_sub = ( strpos( $text, '- ' ) === 0 );
				if ( $in_sub != $is_sub ) {
					$in_sub = $is_sub;
					if ( $is_sub ) {
						$notes .= '<ul>';
					} else {
						$notes .= '</ul>';
					}
				}
				if ( $in_sub ) {
					$text = substr( $text, 2 );
				}
				$notes .= '<li>' . $text;
			}
			$notes .= '</ul>';
		}

		$fields['details'] = array(
			'type' => MS_Helper_Html::TYPE_HTML_TABLE,
			'class' => 'ms-import-preview',
			'value' => array(
				array(
					__( 'Data source', MS_TEXT_DOMAIN ),
					$data->source .
					' &emsp; <small>' .
					sprintf(
						__( 'exported on %1$s', MS_TEXT_DOMAIN ),
						$data->export_time
					) .
					'</small>',
				),
				array(
					__( 'Content', MS_TEXT_DOMAIN ),
					sprintf(
						_n(
							'%1$s Membership',
							'%1$s Memberships',
							count( $data->memberships ),
							MS_TEXT_DOMAIN
						),
						'<b>' . count( $data->memberships ) . '</b>'
					) . ' / ' . sprintf(
						_n(
							'%1$s Member',
							'%1$s Members',
							count( $data->members ),
							MS_TEXT_DOMAIN
						),
						'<b>' . count( $data->members ) . '</b>'
					),
				),
			),
			'field_options' => array(
				'head_col' => true,
				'head_row' => false,
				'col_class' => array( 'preview-label', 'preview-data' ),
			)
		);

		if ( ! empty( $notes ) ) {
			$fields['details']['value'][] = array(
				__( 'Please note', MS_TEXT_DOMAIN ),
				$notes,
			);
		}

		$batchsizes = array(
			10 => __( 'Small (10 items)' ),
			30 => __( 'Normal (30 items)' ),
			100 => __( 'Big (100 items)' ),
		);

		$fields['batchsize'] = array(
			'id' => 'batchsize',
			'type' => MS_Helper_Html::INPUT_TYPE_SELECT,
			'title' => __( 'Batch size for import', MS_TEXT_DOMAIN ),
			'desc' => __( 'Big batches will be processed faster but may result in PHP Memory errors.', MS_TEXT_DOMAIN ),
			'value' => 30,
			'field_options' => $batchsizes,
			'class' => 'sel-batchsize',
		);

		$fields['clear_all'] = array(
			'id' => 'clear_all',
			'type' => MS_Helper_Html::INPUT_TYPE_CHECKBOX,
			'title' => __( 'Replace current content with import data (removes existing Memberships/Members before importing data)', MS_TEXT_DOMAIN ),
			'class' => 'widefat',
		);

		$fields['memberships'] = array(
			'type' => MS_Helper_Html::TYPE_HTML_TABLE,
			'class' => 'ms-import-preview',
			'value' => $memberships,
			'field_options' => array(
				'head_col' => false,
				'head_row' => true,
				'col_class' => array( 'preview-name', 'preview-type', 'preview-pay-type', 'preview-desc' ),
			)
		);

		$fields['members'] = array(
			'type' => MS_Helper_Html::TYPE_HTML_TABLE,
			'class' => 'ms-import-preview',
			'value' => $members,
			'field_options' => array(
				'head_col' => false,
				'head_row' => true,
				'col_class' => array( 'preview-name', 'preview-email', 'preview-count', 'preview-count' ),
			)
		);

		$fields['settings'] = array(
			'type' => MS_Helper_Html::TYPE_HTML_TABLE,
			'class' => 'ms-import-preview',
			'value' => $settings,
			'field_options' => array(
				'head_col' => true,
				'head_row' => false,
			)
		);

		$fields['sep'] = array(
			'type' => MS_Helper_Html::TYPE_HTML_SEPARATOR,
		);

		$fields['back'] = array(
			'type' => MS_Helper_Html::TYPE_HTML_LINK,
			'class' => 'wpmui-field-button button',
			'value' => __( 'Cancel', MS_TEXT_DOMAIN ),
			'url' => $_SERVER['REQUEST_URI'],
		);

		$fields['skip'] = array(
			'type' => MS_Helper_Html::TYPE_HTML_LINK,
			'class' => 'wpmui-field-button button',
			'value' => __( 'Skip', MS_TEXT_DOMAIN ),
			'url' => MS_Controller_Plugin::get_admin_url(
				false,
				array( 'skip_import' => 1 )
			),
		);

		$fields['import'] = array(
			'id' => 'btn-import',
			'type' => MS_Helper_Html::INPUT_TYPE_BUTTON,
			'value' => __( 'Import', MS_TEXT_DOMAIN ),
			'button_value' => MS_Controller_Import::AJAX_ACTION_IMPORT,
			'button_type' => 'submit',
		);

		$fields['download'] = array(
			'id' => 'btn-download',
			'type' => MS_Helper_Html::INPUT_TYPE_BUTTON,
			'value' => __( 'Download as Export File', MS_TEXT_DOMAIN ),
			'class' => 'button-link',
		);

		return $fields;
	}

}