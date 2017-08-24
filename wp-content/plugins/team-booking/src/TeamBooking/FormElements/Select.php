<?php

namespace TeamBooking\FormElements;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Abstracts,
    TeamBooking\Functions;

/**
 * Form element: Select
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Select extends Abstracts\FormElement
{
    /**
     * @return string
     */
    public function getType()
    {
        return 'select';
    }

    public function getMarkup($hidden = FALSE)
    {
        $random = \TeamBooking\Toolkit\randomNumber(6);
        ob_start();
        ?>
        <div class="tbk-field <?= $this->isRequired() ? 'tbk-required' : '' ?>">
            <label><?= $this->getTitle() ?></label>

            <div class="tbk-dropdown" id="tb-<?= $this->getHook() . $random ?>" tabindex="0">
                <input type="hidden"
                       name="form_fields[<?= $this->getHook() ?>]" <?= $this->isRequired() ? "required='required'" : '' ?>>

                <div class="default tbk-text"><?= esc_html__('Select...', 'team-booking') ?></div>
                <i class="dropdown tb-icon"></i>

                <div class="tbk-menu">
                    <?php foreach ($this->getData('options') as $option) { ?>
                        <div class="tbk-item" data-value="<?= $option['text'] ?>"
                             data-price-inc="<?= $option['price_increment'] ?>">
                            <?= $this->wrapStringForTranslations($option['text']) ?>
                            <?php if ($option['price_increment'] > 0) { ?>
                                <span class="tbk-price-increment-form">
                                    + <?= Functions\currencyCodeToSymbol(Functions\getSettings()->getCurrencyCode(), $option['price_increment']) ?>
                                </span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <p class="tbk-field-description"><?= $this->getDescription() ?></p>
            <script>
                jQuery(document).ready(function ($) {
                    $('#tb-<?= $this->getHook() . $random ?>').on('click keydown', function (event) {
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
        return ob_get_clean();
    }
}