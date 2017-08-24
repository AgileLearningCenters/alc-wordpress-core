<?php

defined('ABSPATH') or die('No script kiddies please!');

#TeamBookingFormSelect extends TeamBookingFormTextField
#TeamBooking_Components_Form_Select

use TeamBooking\Functions,
    TeamBooking\Toolkit;

/**
 * @deprecated 2.2.0 No longer used by internal code
 * @see        \TeamBooking\FormElements\Select
 *
 * Class TeamBookingFormSelect
 */
class TeamBookingFormSelect extends TeamBookingFormTextField
{
    protected $options = array();

    //------------------------------------------------------------

    public function addOption(TeamBooking_Components_Form_Option $option)
    {
        $this->options[] = $option;
    }

    //------------------------------------------------------------

    public function resetOptions()
    {
        $this->options = array();
    }

    //------------------------------------------------------------

    /**
     * @return TeamBooking_Components_Form_Option[]
     */
    public function getOptions()
    {
        // legacy
        foreach ($this->options as $key => $option) {
            if (!($option instanceof TeamBooking_Components_Form_Option)) {
                $this->options[ $key ] = new TeamBooking_Components_Form_Option($option);
            }
        }

        return $this->options;
    }

    //------------------------------------------------------------

    public function getMarkup($input_size = '')
    {
        $random = Toolkit\randomNumber(6);
        ?>
        <div class="tbk-field <?= $this->getRequiredFieldClass() ?>">
            <label><?= $this->wrapStringForTranslations(Toolkit\unfilterInput($this->label)) ?></label>

            <div class="tbk-dropdown" id="tb-<?= $this->hook . $random ?>" tabindex="0">
                <input type="hidden"
                       name="form_fields[<?= $this->hook ?>]" <?= $this->required ? "required='required'" : '' ?>>

                <div class="default tbk-text"><?= esc_html__('Select...', 'team-booking') ?></div>
                <i class="dropdown tb-icon"></i>

                <div class="tbk-menu">
                    <?php
                    //legacy
                    if (!($this->value instanceof TeamBooking_Components_Form_Option)) {
                        $this->value = new TeamBooking_Components_Form_Option($this->value);
                    }
                    ?>
                    <div class="tbk-item" data-value="<?= $this->value->getText() ?>">
                        <?= $this->value->getText() ?>
                        <?php if ($this->value->getPriceIncrement() > 0) { ?>
                            <span class="tbk-price-increment-form">
                                    + <?= Functions\currencyCodeToSymbol(Functions\getSettings()->getCurrencyCode(), $this->value->getPriceIncrement()) ?>
                            </span>
                        <?php } ?>
                    </div>
                    <?php foreach ($this->getOptions() as $option) {
                        //legacy
                        if (!($option instanceof TeamBooking_Components_Form_Option)) {
                            $option = new TeamBooking_Components_Form_Option($option);
                        }
                        ?>
                        <div class="tbk-item" data-value="<?= $option->getText() ?>"
                             data-price-inc="<?= $option->getPriceIncrement() ?>">
                            <?= $this->wrapStringForTranslations($option->getText()) ?>
                            <?php if ($option->getPriceIncrement() > 0) { ?>
                                <span class="tbk-price-increment-form">
                                    + <?= Functions\currencyCodeToSymbol(Functions\getSettings()->getCurrencyCode(), $option->getPriceIncrement()) ?>
                                </span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php $this->getValidationMessageLabel() ?>
            <script>
                jQuery(document).ready(function ($) {
                    $('#tb-<?= $this->hook . $random ?>').on('click keydown', function (event) {
                            var $dropdown = $(this);
                            $dropdown.closest('.tb-frontend-calendar').css('overflow', 'visible');
                            var keycode = (event.which ? event.which : 13);
                            if (keycode != 13 && keycode != 32 && keycode != 1) {
                                // keyboard navigation
                                var item = $dropdown.find('.tbk-item');
                                var itemSelected = $dropdown.find('.tbk-item.selected');
                                var next;
                                if (keycode === 40) {
                                    if (itemSelected) {
                                        itemSelected.removeClass('active selected');
                                        next = itemSelected.next();
                                        if (next.length > 0) {
                                            next.addClass('active selected');
                                        } else {
                                            item.eq(0).addClass('active selected');
                                        }
                                    } else {
                                        item.eq(0).addClass('active selected');
                                    }
                                    event.stopPropagation();
                                    return false;
                                } else if (keycode === 38) {
                                    if (itemSelected) {
                                        itemSelected.removeClass('active selected');
                                        next = itemSelected.prev();
                                        if (next.length > 0) {
                                            next.addClass('active selected');
                                        } else {
                                            item.last().addClass('active selected');
                                        }
                                    } else {
                                        item.last().addClass('active selected');
                                    }
                                    event.stopPropagation();
                                    return false;
                                }
                                return true;
                            }
                            event.stopPropagation();
                            $dropdown.find('.tbk-menu').toggle();
                            $dropdown.find('.tbk-text').removeClass('default').html($dropdown.find('.tbk-item.selected').html());
                            $dropdown.find('input').val($dropdown.find('.tbk-item.selected').data('value'));
                            $dropdown.closest('.tbk-field').removeClass('tbk-error');
                            var $selectedItem = $dropdown.closest('.tbk-reservation-form-container').find('.tbk-ticket-value');
                            tbUpdateTotalPrice($selectedItem);
                            return false;
                        })
                        .on('mousedown', '.tbk-item', function (event) {
                            event.stopPropagation();
                            var $dropdown = $(this).closest('.tbk-dropdown');
                            $dropdown.find('.tbk-text').removeClass('default').html($(this).html());
                            $(this).closest('.tbk-menu').find('.tbk-item').removeClass('active selected');
                            $(this).addClass('active selected');
                            $(this).closest('.tbk-menu').hide();
                            $(this).closest('.tb-frontend-calendar').css('overflow', 'hidden');
                            $dropdown.find('input').val($(this).data('value'));
                            $dropdown.closest('.tbk-field').removeClass('tbk-error');
                            var $selectedItem = $(this).closest('.tbk-reservation-form-container').find('.tbk-ticket-value');
                            tbUpdateTotalPrice($selectedItem);
                        })
                        .focusout(function () {
                            $(this).closest('.tb-frontend-calendar').css('overflow', 'hidden');
                            $(this).find('.tbk-menu').hide();
                        })
                        .keyup(function (event) {
                            var $dropdown = $(this);
                            if (event.which == 9 && $dropdown.is(':focus')) {
                                $dropdown.find('.tbk-menu').show();
                                $dropdown.closest('.tb-frontend-calendar').css('overflow', 'visible');
                            }
                        })
                    ;
                })
            </script>
        </div>
        <?php
    }

    public function getProperties()
    {
        $properties = array(
            'hook'        => $this->getHook(),
            'description' => '',
            'required'    => $this->getIsRequired(),
            'visible'     => $this->getIsActive(),
            'title'       => htmlspecialchars_decode($this->getLabel(), ENT_QUOTES)
        );
        if (!($this->value instanceof TeamBooking_Components_Form_Option)) {
            $this->value = new TeamBooking_Components_Form_Option($this->value);
        }
        $properties['data']['options'][] = array(
            'text'            => htmlspecialchars_decode($this->value->getText(), ENT_QUOTES),
            'price_increment' => $this->value->getPriceIncrement()
        );
        foreach ($this->getOptions() as $option) {
            if (!($option instanceof TeamBooking_Components_Form_Option)) {
                $option = new TeamBooking_Components_Form_Option($option);
            }
            $properties['data']['options'][] = array(
                'text'            => htmlspecialchars_decode($option->getText(), ENT_QUOTES),
                'price_increment' => $option->getPriceIncrement()
            );
        }

        return $properties;
    }

}
