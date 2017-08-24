<?php

namespace TeamBooking\Mappers;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Abstracts\FormElement;
use TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Toolkit,
    TeamBooking\Admin\Framework;

/**
 * Parses the reservation form data to a reservation object
 *
 * @param array $form_data
 *
 * @return \TeamBooking_ReservationData
 */
function reservationFormMapper($form_data)
{
    // Parse data
    $parsed_data = array();
    parse_str($form_data, $parsed_data);
    // verify nonce
    $nonce = $parsed_data['nonce'];
    if (!wp_verify_nonce($nonce, 'teambooking_submit_reservation')) {
        return FALSE;
    }
    try {
        $service = Database\Services::get($parsed_data['service']);
    } catch (\Exception $e) {
        exit(Toolkit\wrapAjaxResponse($e->getMessage()));
    }
    $expected_fields = Database\Forms::getActiveHooks($service->getForm());
    $expected_fields = Functions\manipulate_expected_form_field_hooks($expected_fields, $service, $parsed_data);
    $returned_fields = array();
    foreach ($expected_fields as $name) {
        // $name is the hook
        $form_field = new \TeamBooking_ReservationFormField();
        $form_field->setName($name);
        $form_field->setLabel(Database\Forms::getTitleFromHook($service->getForm(), $name));
        if (isset($parsed_data['form_fields'][ $name ])) {
            $form_field->setValue(Toolkit\filterInput($parsed_data['form_fields'][ $name ]));
            $form_field->setPriceIncrement(Database\Forms::getPriceIncrementFromOptionValue($service->getForm(), $name, $parsed_data['form_fields'][ $name ]));
        } else {
            // We are facing unchecked checkboxes here...
            $form_field->setValue(__('Not selected', 'team-booking'));
        }
        $returned_fields[] = $form_field;
    }
    $return = new \TeamBooking_ReservationData();
    if (isset($parsed_data['slot_start'])) {
        $return->setSlotStart($parsed_data['slot_start']);
        $return->setStart(strtotime($parsed_data['slot_start']));
    }
    if (isset($parsed_data['slot_end'])) {
        $return->setSlotEnd($parsed_data['slot_end']);
        $return->setEnd(strtotime($parsed_data['slot_end']));
    }
    $return->setServiceLocation($parsed_data['service_location']);
    $return->setTickets($parsed_data['tickets']);
    $return->setPostId($parsed_data['post_id']);
    $return->setPostTitle(get_the_title($parsed_data['post_id']));
    if (isset($parsed_data['owner'])) {
        $return->setCoworker($parsed_data['owner']);
    }
    $return->setPrice($service->getPrice());
    // Collecting discount campaigns
    $discounts_used = array();
    if ($service->getClass() === 'unscheduled') {
        $discounted_price = Functions\getDiscountedPrice($service);
    } else {
        $discounted_price = Functions\getDiscountedPrice($service, $return->getStart(), $return->getEnd());
    }
    if ($service->getPrice() != $discounted_price) {
        $campaigns = Database\Promotions::getByClassAndService('campaign', $service->getId(), TRUE, TRUE);
        foreach ($campaigns as $pricing_id => $pricing) {
            $discounts_used[ $pricing_id ] = array(
                'name'  => $pricing->getName(),
                'value' => $pricing->getDiscount(),
                'type'  => $pricing->getDiscountType(),
                'id'    => $pricing_id
            );
        }
        if (!empty($discounts_used)) {
            $return->setDiscount($discounts_used);
        }

        if ($discounted_price < 0) {
            $discounted_price = 0;
        }
        $return->setPriceDiscounted($discounted_price);
    }
    $return->setServiceClass($service->getClass());
    $return->setCurrencyCode(Functions\getSettings()->getCurrencyCode());
    if (isset($parsed_data['event_id'])) {
        $return->setGoogleCalendarEvent($parsed_data['event_id']);
    }
    if (isset($parsed_data['calendar_id'])) {
        $return->setGoogleCalendarId(Toolkit\base64UrlDecode($parsed_data['calendar_id']));
    }
    $return->setServiceName($service->getName());
    $return->setServiceId($parsed_data['service']);
    $return->setFormFields($returned_fields);
    if (isset($parsed_data['event_id_parent'])) {
        if ($parsed_data['event_id_parent']) {
            $return->setGoogleCalendarEventParent($parsed_data['event_id_parent']);
        }
    }
    if (isset($parsed_data['customer_wp_id'])) {
        $return->setCustomerUserId($parsed_data['customer_wp_id']);
    }
    $now = Toolkit\getNowInSecondsUTC();
    $return->setCreationInstant($now);
    $return->setToken(Toolkit\generateToken());

    return $return;
}

/**
 * It takes a form field and returns the HTML for admin builder
 *
 * @param FormElement $field
 * @param             $service_id
 *
 * @return string
 */
function adminFormFieldsMapper(FormElement $field, $service_id)
{
    $built_in_hooks = Database\Forms::getBuiltInHooks();
    $fieldname = 'event[form_fields][custom]';
    ob_start();
    ?>
    <div style="margin: 5px 0 0 0">
        <label title="<?= esc_attr__('Show', 'team-booking') ?>" style="vertical-align: inherit">
            <input data-ays-ignore="true" name="<?= $fieldname ?>[<?= $field->getHook() ?>_show]"
                   type="checkbox"
                   value="<?= $field->getHook() ?>" <?php checked(TRUE, $field->isVisible()) ?>/>
        </label>
        <?php if ($field->getType() !== 'radio' && $field->getType() !== 'paragraph') { ?>
            <label title="<?= esc_attr__('Required', 'team-booking') ?>" style="vertical-align: inherit">
                <input data-ays-ignore="true" name="<?= $fieldname ?>[<?= $field->getHook() ?>_required]"
                       type="checkbox" class="tb_required _EF6060"
                       value="<?= $field->getHook() ?>" <?php checked(TRUE, $field->isRequired()) ?>/>
            </label>
        <?php } ?>
    </div>
    <?php
    $fieldset = ob_get_clean();
    ob_start();

    if ($field->getType() === 'text_field') {
        $validation_settings = $field->getData('validation');
        ?>
        <a title="<?= esc_attr__('Field validation rule', 'team-booking') ?>"
           id="tb-field-regex-open-<?= $field->getHook() ?>"
           class="ui mini circular label <?= $validation_settings['validate'] ? 'active' : 'inverted' ?>">
            <span class="dashicons dashicons-editor-spellcheck" style="vertical-align: middle;"></span>
        </a>
        <?= Functions\getValidationModal($field, $fieldname) ?>
    <?php }
    if (!in_array($field->getHook(), $built_in_hooks)) { ?>
        <a title="<?= esc_attr__('Delete', 'team-booking') ?>" class="ui mini circular label tb-remove-custom-field"
           data-hook="<?= $field->getHook() ?>"
           data-serviceid="<?= $service_id ?>"
           href="#"><span class="dashicons dashicons-trash" style="vertical-align: middle;"></span></a>
        <a title="<?= esc_attr__('Settings', 'team-booking') ?>"
           class="ui mini circular label tb-expand-handle"
           href="#"><span class="dashicons dashicons-admin-settings" style="vertical-align: middle;"></span></a>
    <?php } ?>
    <a title="<?= esc_attr__('Save', 'team-booking') ?>"
       class="ui mini circular label <?= in_array($field->getHook(), $built_in_hooks) ? 'tb-save-builtin-field' : 'tb-save-custom-field' ?>"
       data-hook="<?= $field->getHook() ?>" data-serviceid="<?= $service_id ?>"
       href="#"><span class="dashicons dashicons-yes" style="vertical-align: middle;"></span></a>
    <?php
    $buttons = ob_get_clean();
    ob_start();
    ?>
    <span class="dashicons dashicons-menu tb-drag-handle"></span>
    <?php
    $move_buttons = ob_get_clean();
    if (in_array($field->getHook(), $built_in_hooks)) {
        ob_start();
        ?>
        <table class="widefat tbk-form-field-draggable tbk-built-in">
            <tbody>
            <tr>
                <td rowspan="1000" class="tb-no-select" style="width: 20px;">
                    <span class="dashicons dashicons-menu tb-drag-handle"></span>
                </td>
                <td rowspan="1000" style="width:150px;">
                    <span style="font-size: initial;color:#ffd79d;">
                        <strong><?= esc_html__('Built-in', 'team-booking') ?></strong>
                    </span>
                    <?= $fieldset ?>
                </td>
                <td>
                    <h4 style="display: inline-block"><?= $field->getTitle() ?>
                        <span
                            class="tbk-builtin-field-hook"> [<?= $field->getHook() ?>]</span>
                    </h4>
                    <div style="float:right;">
                        <?= $buttons ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
        $return = ob_get_clean();
    } elseif ($field->getType() === 'text_field') {
        ob_start();
        ?>
        <table class="widefat tbk-form-field-draggable">
            <tbody>
            <tr>
                <td rowspan="1000" class="tb-no-select" style="width: 20px;">
                    <?= $move_buttons ?>
                </td>
                <td rowspan="1000" style="width:150px;">
                    <span style="font-size: initial;">
                        <strong><?= esc_html__('Text field', 'team-booking') ?></strong>
                    </span>
                    <?= $fieldset ?>
                </td>
                <td>
                    <div class="tbk-field-preview" style="display: inline-block">
                        <h4 class="tbk-field-label-rendered" style="margin: 0 0 1px 1px"><?= $field->getTitle() ?></h4>
                        <input type="text" readonly value="<?= $field->getData('value') ?>" style="background: white">
                        <p class="tbk-field-description-rendered"><?= $field->getDescription() ?></p>
                    </div>
                    <div class="tbk-field-label" style="display: none;">
                        <span class="description tbk-up"><?= esc_html__('Label', 'team-booking') ?></span>
                        <input data-ays-ignore="true" type="text" class="all-options"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_label]"
                               value="<?= Toolkit\filterInput($field->getTitle()) ?>">
                    </div>
                    <div style="float:right;">
                        <?= $buttons ?>
                    </div>
                </td>
            </tr>
            <!-- hidden content -->
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Default value', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options tbk-text-value-edit"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_default]"
                           value="<?= $field->getData('value') ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Hook', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_hook]" value="<?= $field->getHook() ?>"
                           placeholder="<?= esc_attr__('without square brackets', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Description (optional)', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options tbk-description-edit"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_description]"
                           value="<?= $field->getDescription() ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span
                        class="description tbk-up"><?= esc_html__('Pre-fill meta_key (optional)', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_prefill]"
                           value="<?= $field->getData('prefill') ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                </td>
            </tr>
            <?php Functions\backend_form_field_add_content($field, $fieldname) ?>
            </tbody>
        </table>
        <?php
        $return = ob_get_clean();
    } elseif ($field->getType() === 'text_area') {
        ob_start();
        ?>
        <table class="widefat tbk-form-field-draggable">
            <tbody>
            <tr>
                <td rowspan="1000" class="tb-no-select" style="width: 20px;">
                    <?= $move_buttons ?>
                </td>
                <td rowspan="1000" style="width:150px;">
                    <span style="font-size: initial;">
                        <strong><?= esc_html__('Textarea', 'team-booking') ?></strong>
                    </span>
                    <?= $fieldset ?>
                </td>
                <td>
                    <div class="tbk-field-preview" style="display: inline-block">
                        <h4 class="tbk-field-label-rendered"><?= $field->getTitle() ?></h4>
                        <textarea readonly style="background: white;resize: horizontal;max-width: 200px;"></textarea>
                        <p class="tbk-field-description-rendered"><?= $field->getDescription() ?></p>
                    </div>
                    <div class="tbk-field-label" style="display: none;">
                        <span class="description tbk-up"><?= esc_html__('Label', 'team-booking') ?></span>
                        <input data-ays-ignore="true" type="text" class="all-options"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_label]"
                               value="<?= Toolkit\filterInput($field->getTitle()) ?>">
                    </div>
                    <div style="float:right;">
                        <?= $buttons ?>
                    </div>
                </td>
            </tr>
            <!-- hidden content -->
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Default text', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options tbk-text-value-edit"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_default]"
                           value="<?= $field->getData('value') ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Hook', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_hook]" value="<?= $field->getHook() ?>"
                           placeholder="<?= esc_attr__('without square brackets', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Description (optional)', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options tbk-description-edit"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_description]"
                           value="<?= $field->getDescription() ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span
                        class="description tbk-up"><?= esc_html__('Pre-fill meta_key (optional)', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_prefill]"
                           value="<?= $field->getData('prefill') ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                </td>
            </tr>
            <?php Functions\backend_form_field_add_content($field, $fieldname) ?>
            </tbody>
        </table>
        <?php
        $return = ob_get_clean();
    } elseif ($field->getType() === 'paragraph') {
        ob_start();
        ?>
        <table class="widefat tbk-form-field-draggable">
            <tbody>
            <tr>
                <td rowspan="1000" class="tb-no-select" style="width: 20px;">
                    <?= $move_buttons ?>
                </td>
                <td rowspan="1000" style="width:150px;">
                    <span style="font-size: initial;">
                        <strong><?= esc_html__('Paragraph', 'team-booking') ?></strong>
                    </span>
                    <?= $fieldset ?>
                </td>
                <td>
                    <div class="tbk-field-preview" style="display: inline-block">
                        <h4 class="tbk-field-label-rendered"><?= $field->getTitle() ?></h4>
                        <p class="tbk-field-description-rendered"><?= $field->getDescription() ?></p>
                    </div>
                    <div class="tbk-field-label" style="display: none;">
                        <span class="description tbk-up"><?= esc_html__('Label', 'team-booking') ?></span>
                        <input data-ays-ignore="true" type="text" class="all-options"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_label]"
                               value="<?= Toolkit\filterInput($field->getTitle()) ?>">
                    </div>
                    <div style="float:right;">
                        <?= $buttons ?>
                    </div>
                </td>
            </tr>
            <!-- hidden content -->
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Content', 'team-booking') ?></span>
                    <span class="description tbk-up" style="color: darkgray;">
                        <?= esc_html__('If you use html code, please ensure it is not broken!', 'team-booking') ?>
                    </span>
                    <textarea data-ays-ignore="true" type="text" class="all-options tbk-description-edit"
                              name="<?= $fieldname ?>[<?= $field->getHook() ?>_description]"><?= $field->getDescription() ?></textarea>
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Hook', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_hook]" value="<?= $field->getHook() ?>"
                           placeholder="<?= esc_attr__('without square brackets', 'team-booking') ?>">
                </td>
            </tr>
            <?php Functions\backend_form_field_add_content($field, $fieldname) ?>
            </tbody>
        </table>
        <?php
        $return = ob_get_clean();
    } elseif ($field->getType() === 'checkbox') {
        ob_start();
        ?>
        <table class="widefat tbk-form-field-draggable">
            <tbody>
            <tr>
                <td rowspan="1000" class="tb-no-select" style="width: 20px;">
                    <?= $move_buttons ?>
                </td>
                <td rowspan="1000" style="width:150px;">
                    <span style="font-size: initial;">
                        <strong><?= esc_html__('Checkbox', 'team-booking') ?></strong>
                    </span>
                    <?= $fieldset ?>
                </td>
                <td>
                    <div class="tbk-field-preview" style="display: inline-block">
                        <h4 class="tbk-field-label-rendered"><?= $field->getTitle() ?></h4>
                        <input type="checkbox"
                               onclick="return false;" <?php checked(TRUE, $field->getData('checked')) ?>>
                        <p class="tbk-field-description-rendered"><?= $field->getDescription() ?></p>
                    </div>
                    <div class="tbk-field-label" style="display: none;">
                        <span class="description tbk-up"><?= esc_html__('Label', 'team-booking') ?></span>
                        <input data-ays-ignore="true" type="text" class="all-options"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_label]"
                               value="<?= Toolkit\filterInput($field->getTitle()) ?>">
                    </div>
                    <div style="float:right;">
                        <?= $buttons ?>
                    </div>
                </td>
            </tr>
            <!-- hidden content -->
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Hook', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_hook]" value="<?= $field->getHook() ?>"
                           placeholder="<?= esc_attr__('without square brackets', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Description (optional)', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options tbk-description-edit"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_description]"
                           value="<?= $field->getDescription() ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <input data-ays-ignore="true" type="checkbox" value="1" class="tbk-checkbox-default-state"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_selected]" <?php checked(TRUE, $field->getData('checked')) ?>>
                    <span class="description" style="vertical-align: middle;">
                        <?= esc_html__('Selected by default', 'team-booking') ?>
                    </span>
                </td>
            </tr>
            <?php Functions\backend_form_field_add_content($field, $fieldname) ?>
            </tbody>
        </table>
        <?php
        $return = ob_get_clean();
    } elseif ($field->getType() === 'select') {
        $options = $field->getData('options');
        $first_option = reset($options);
        $other_options = array_slice($options, 1);
        $currency_array = Toolkit\getCurrencies(Functions\getSettings()->getCurrencyCode());
        ob_start();
        ?>
        <table class="widefat tbk-form-field-draggable">
            <tbody>
            <tr>
                <td rowspan="1000" class="tb-no-select" style="width: 20px;">
                    <?= $move_buttons ?>
                </td>
                <td rowspan="1000" style="width:150px;">
                    <span style="font-size: initial;">
                        <strong><?= esc_html__('Select', 'team-booking') ?></strong>
                    </span>
                    <?= $fieldset ?>
                </td>
                <td>
                    <div class="tbk-field-preview" style="display: inline-block">
                        <h4 class="tbk-field-label-rendered"><?= $field->getTitle() ?></h4>
                        <select>
                            <?php foreach ($options as $option) {
                                echo '<option>' . $option['text'] . '</option>';
                            }
                            ?>
                        </select>
                        <p class="tbk-field-description-rendered"><?= $field->getDescription() ?></p>
                    </div>
                    <div class="tbk-field-label" style="display: none;">
                        <span class="description tbk-up"><?= esc_html__('Label', 'team-booking') ?></span>
                        <input data-ays-ignore="true" type="text" class="all-options"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_label]"
                               value="<?= Toolkit\filterInput($field->getTitle()) ?>">
                    </div>
                    <div style="float:right;">
                        <?= $buttons ?>
                    </div>
                </td>
            </tr>
            <!-- hidden content -->
            <tr class="tb-hide">
                <td>
                    <span
                        class="dashicons dashicons-admin-generic tb-advanced-option-handle <?= $first_option['price_increment'] > 0 ? 'active' : '' ?>"
                        data-modal="tbk-advanced-option-modal-<?= $field->getHook() ?>"></span>
                    <input data-ays-ignore="true" type="text" class="all-options tbk-select-option-edit"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_default][label]"
                           value="<?= $first_option['text'] ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                    <input data-ays-ignore="true" type="hidden"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_default][price_increment]"
                           value="<?= $first_option['price_increment'] ?>">
                    <span class="description"><?= esc_html__('Option', 'team-booking') ?></span>
                </td>
            </tr>
            <?php
            foreach ($other_options as $key => $option) {
                ?>
                <tr class="tb-hide">
                    <td>
                        <span class="dashicons dashicons-dismiss tb-delete-option-handle"></span>
                        <span
                            class="dashicons dashicons-admin-generic tb-advanced-option-handle <?= $option['price_increment'] > 0 ? 'active' : '' ?>"
                            data-modal="tbk-advanced-option-modal-<?= $field->getHook() ?>"></span>

                        <input data-ays-ignore="true" type="text"
                               class="all-options single-option tbk-select-option-edit"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_options][<?= $key ?>][label]"
                               value="<?= $option['text'] ?>">
                        <input data-ays-ignore="true" type="hidden"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_options][<?= $key ?>][price_increment]"
                               value="<?= $option['price_increment'] ?>">
                        <span class="description"><?= esc_html__('Option', 'team-booking') ?></span>
                    </td>
                </tr>
            <?php } ?>
            <tr class="tb-hide">
                <td>
                    <span class="dashicons dashicons-plus-alt tb-add-option-handle"></span>
                    <span class="description" style="vertical-align: top;">
                        <?= esc_html__('Add option', 'team-booking') ?>
                    </span>
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Description (optional)', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options tbk-description-edit"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_description]"
                           value="<?= $field->getDescription() ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Hook', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_hook]" value="<?= $field->getHook() ?>"
                           placeholder="<?= esc_attr__('without square brackets', 'team-booking') ?>">
                </td>
            </tr>
            <?php Functions\backend_form_field_add_content($field, $fieldname) ?>
            </tbody>
        </table>

        <!-- Option settings modal -->
        <?php ob_start() ?>
        <h4>
            <?= esc_html__('Price increment', 'team-booking') ?>
            <span class="description">
                (<?= esc_html__('base price', 'team-booking') ?>
                <?= Functions\currencyCodeToSymbol(Functions\getSettings()->getCurrencyCode(), Database\Services::get($service_id)->getPrice()) . ')' ?>
            </span>
        </h4>
        <input type="number" step="<?= $currency_array['decimal'] === TRUE ? '0.01' : '1' ?>" min="0" value="0"
               name="price_increment">
        <?= Functions\currencyCodeToSymbol(Functions\getSettings()->getCurrencyCode(), NULL) ?>

        <?php
        $modal_content = ob_get_clean();
        $modal = new Framework\Modal('tbk-advanced-option-modal-' . $field->getHook());
        $modal->setButtonText(array('approve' => __('Set', 'team-booking') . ' (' . __('remember to save', 'team-booking') . ')', 'deny' => __('Close', 'team-booking')));
        $modal->setHeaderText(array('main' => $field->getTitle(), 'sub' => __('advanced option settings', 'team-booking')));
        $modal->addContent($modal_content);
        $modal->render();
        $return = ob_get_clean();

    } elseif ($field->getType() === 'radio') {
        $options = $field->getData('options');
        $first_option = reset($options);
        $other_options = array_slice($options, 1);
        $currency_array = Toolkit\getCurrencies(Functions\getSettings()->getCurrencyCode());
        ob_start();
        ?>
        <table class="widefat tbk-form-field-draggable">
            <tbody>
            <tr>
                <td rowspan="1000" class="tb-no-select" style="width: 20px;">
                    <?= $move_buttons ?>
                </td>
                <td rowspan="1000" style="width:150px;">
                    <span style="font-size: initial;">
                        <strong><?= esc_html__('Radio group', 'team-booking') ?></strong>
                    </span>
                    <?= $fieldset ?>
                </td>
                <td>
                    <div class="tbk-field-preview" style="display: inline-block">
                        <h4 class="tbk-field-label-rendered"><?= $field->getTitle() ?></h4>
                        <fieldset>
                            <?php foreach ($options as $option) {
                                echo '<input type="radio" name="' . $field->getHook() . '">' . $option['text'] . '</input><br>';
                            }
                            ?>
                        </fieldset>
                        <p class="tbk-field-description-rendered"><?= $field->getDescription() ?></p>
                    </div>
                    <div class="tbk-field-label" style="display: none;">
                        <span class="description tbk-up"><?= esc_html__('Label', 'team-booking') ?></span>
                        <input data-ays-ignore="true" type="text" class="all-options"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_label]"
                               value="<?= Toolkit\filterInput($field->getTitle()) ?>">
                    </div>
                    <div style="float:right;">
                        <?= $buttons ?>
                    </div>
                </td>
            </tr>
            <!-- hidden content -->
            <tr class="tb-hide">
                <td>
                    <span
                        class="dashicons dashicons-admin-generic tb-advanced-option-handle <?= $first_option['price_increment'] > 0 ? 'active' : '' ?>"
                        data-modal="tbk-advanced-option-modal-<?= $field->getHook() ?>"></span>
                    <input data-ays-ignore="true" type="text" class="all-options tbk-radio-option-edit"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_default][label]"
                           value="<?= $first_option['text'] ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                    <input data-ays-ignore="true" type="hidden"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_default][price_increment]"
                           value="<?= $first_option['price_increment'] ?>">
                    <span class="description"><?= esc_html__('Option', 'team-booking') ?></span>
                </td>
            </tr>
            <?php
            foreach ($other_options as $key => $option) {
                ?>
                <tr class="tb-hide">
                    <td>

                        <span class="dashicons dashicons-dismiss tb-delete-option-handle"></span>
                        <span
                            class="dashicons dashicons-admin-generic tb-advanced-option-handle <?= $option['price_increment'] > 0 ? 'active' : '' ?>"
                            data-modal="tbk-advanced-option-modal-<?= $field->getHook() ?>"></span>

                        <input data-ays-ignore="true" type="text"
                               class="all-options single-option tbk-radio-option-edit"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_options][<?= $key ?>][label]"
                               value="<?= $option['text'] ?>">
                        <input data-ays-ignore="true" type="hidden"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_options][<?= $key ?>][price_increment]"
                               value="<?= $option['price_increment'] ?>">
                        <span class="description"><?= esc_html__('Option', 'team-booking') ?></span>
                    </td>
                </tr>
            <?php } ?>
            <tr class="tb-hide">
                <td>
                    <span class="dashicons dashicons-plus-alt tb-add-option-handle"></span>
                    <span class="description" style="vertical-align: top;">
                        <?= esc_html__('Add option', 'team-booking') ?>
                    </span>
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Description (optional)', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options tbk-description-edit"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_description]"
                           value="<?= $field->getDescription() ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Hook', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_hook]" value="<?= $field->getHook() ?>"
                           placeholder="<?= esc_attr__('without square brackets', 'team-booking') ?>">
                </td>
            </tr>
            <?php Functions\backend_form_field_add_content($field, $fieldname) ?>
            </tbody>
        </table>

        <!-- Option settings modal -->
        <?php ob_start() ?>
        <h4>
            <?= esc_html__('Price increment', 'team-booking') ?>
            <span class="description">
                (<?= esc_html__('base price', 'team-booking') ?>
                <?= Functions\currencyCodeToSymbol(Functions\getSettings()->getCurrencyCode(), Database\Services::get($service_id)->getPrice()) . ')' ?>
            </span>
        </h4>
        <input type="number" step="<?= $currency_array['decimal'] === TRUE ? '0.01' : '1' ?>" min="0" value="0"
               name="price_increment">
        <?= Functions\currencyCodeToSymbol(Functions\getSettings()->getCurrencyCode(), NULL) ?>

        <?php
        $modal_content = ob_get_clean();
        $modal = new Framework\Modal('tbk-advanced-option-modal-' . $field->getHook());
        $modal->setButtonText(array('approve' => __('Set', 'team-booking') . ' (' . __('remember to save', 'team-booking') . ')', 'deny' => __('Close', 'team-booking')));
        $modal->setHeaderText(array('main' => $field->getTitle(), 'sub' => __('advanced option settings', 'team-booking')));
        $modal->addContent($modal_content);
        $modal->render();
        $return = ob_get_clean();

    } elseif ($field->getType() === 'file_upload') {
        ob_start();
        ?>
        <table class="widefat tbk-form-field-draggable">
            <tbody>
            <tr>
                <td rowspan="1000" class="tb-no-select" style="width: 20px;">
                    <?= $move_buttons ?>
                </td>
                <td rowspan="1000" style="width:150px;">
                    <span style="font-size: initial;">
                        <strong><?= esc_html__('File Field', 'team-booking') ?></strong>
                    </span>
                    <?= $fieldset ?>
                </td>
                <td>
                    <div class="tbk-field-preview" style="display: inline-block">
                        <h4 class="tbk-field-label-rendered"><?= $field->getTitle() ?></h4>
                        <input type="file" disabled>
                        <p class="tbk-field-description-rendered"><?= $field->getDescription() ?></p>
                    </div>
                    <div class="tbk-field-label" style="display: none;">
                        <span class="description tbk-up"><?= esc_html__('Label', 'team-booking') ?></span>
                        <input data-ays-ignore="true" type="text" class="all-options"
                               name="<?= $fieldname ?>[<?= $field->getHook() ?>_label]"
                               value="<?= Toolkit\filterInput($field->getTitle()) ?>">
                    </div>
                    <div style="float:right;">
                        <?= $buttons ?>
                    </div>
                </td>
            </tr>
            <!-- hidden content -->
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Description (optional)', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options tbk-description-edit"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_description]"
                           value="<?= $field->getDescription() ?>"
                           placeholder="<?= esc_attr__('none', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up"><?= esc_html__('Hook', 'team-booking') ?></span>
                    <input data-ays-ignore="true" type="text" class="all-options"
                           name="<?= $fieldname ?>[<?= $field->getHook() ?>_hook]" value="<?= $field->getHook() ?>"
                           placeholder="<?= esc_attr__('without square brackets', 'team-booking') ?>">
                </td>
            </tr>
            <tr class="tb-hide">
                <td>
                    <span class="description tbk-up">
                        <?= esc_html__('Allowed extensions (no dots, comma separated)', 'team-booking') ?>
                    </span>
                    <input data-ays-ignore="true" type="text" class="all-options"
                           name="event[form_fields][custom][<?= $field->getHook() ?>_extensions]"
                           value="<?= $field->getData('file_extensions') ?>">
                </td>
            </tr>
            <?php Functions\backend_form_field_add_content($field, $fieldname) ?>
            </tbody>
        </table>
        <?php
        $return = ob_get_clean();
    }

    return $return;
}

/**
 * Used for abstractions
 *
 * @param \TeamBooking_ReservationData $data
 *
 * @return \TeamBooking\Slot
 * @throws \Exception
 */
function reservationDataToSlot(\TeamBooking_ReservationData $data)
{
    $slot = new \TeamBooking\Slot();
    $slot->setServiceId($data->getServiceId());
    $slot->setServiceName($data->getServiceName());
    $slot->setServiceInfo(Database\Services::get($data->getServiceId())->getDescription());
    $slot->setStartTime($data->getSlotStart());
    $slot->setEndTime($data->getSlotEnd());
    $slot->setEventId($data->getGoogleCalendarEvent());
    $slot->setCalendarId($data->getGoogleCalendarId());
    $slot->setEventIdParent($data->getGoogleCalendarEventParent());
    $slot->setAttendeesNumber($data->getTickets());
    $slot->setLocation($data->getServiceLocation());
    $slot->setAllDay(FALSE);
    $slot->setCoworkerId($data->getCoworker());
    if ($data->getServiceClass() === 'appointment'
        || Database\Services::get($data->getServiceId())->getSlotMaxTickets() <= $slot->getAttendeesNumber()
    ) {
        $slot->setSoldout();
    }
    $slot->addCustomer(array(
        'email'          => $data->getCustomerEmail(),
        'name'           => $data->getCustomerDisplayName(),
        'id'             => $data->getCustomerUserId(),
        'address'        => $data->getCustomerAddress(),
        'timezone'       => $data->getCustomerTimezone(),
        'tickets'        => $data->getTickets(),
        'status'         => $data->getStatus(),
        'reservation_id' => $data->getDatabaseId()
    ));
    $slot->setFromReservation($data->getDatabaseId());

    return $slot;
}
