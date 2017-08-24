<?php

namespace TeamBooking\FormElements;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Abstracts,
    TeamBooking\Functions;

/**
 * Form element: Radio Group
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
class Radio extends Abstracts\FormElement
{
    public function __construct()
    {
        parent::__construct();
        $this->setRequired(FALSE);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'radio';
    }

    /**
     * @param bool $hidden
     *
     * @return string
     */
    public function getMarkup($hidden = FALSE)
    {
        $random_append = substr(md5(mt_rand()), 0, 8);
        $checked = 'checked="checked"';
        ob_start();
        ?>
        <div class="tbk-field <?= $this->isRequired() ? 'tbk-required' : '' ?>">
            <label style="display: block;">
                <?= $this->getTitle() ?>
            </label>
            <?php
            foreach ($this->getData('options') as $option) {
                ?>
                <div class="tbk-radio">
                    <input id="tb-radio-<?= $this->getHook() . '-' . $random_append ?>" type="radio"
                           value="<?= esc_attr($option['text']) ?>" name="form_fields[<?= $this->getHook() ?>]"
                           data-price-inc="<?= $option['price_increment'] ?>"
                        <?= $checked ?>
                    >
                    <label for="tb-radio-<?= $this->getHook() . '-' . $random_append ?>">
                        <?= $this->wrapStringForTranslations(esc_attr($option['text'])) ?>
                        <?php if ($option['price_increment'] > 0) { ?>
                            <span class="tbk-price-increment-form">
                                    + <?= Functions\currencyCodeToSymbol(Functions\getSettings()->getCurrencyCode(), $option['price_increment']) ?>
                                </span>
                        <?php } ?>
                    </label>
                </div>

                <script>
                    jQuery('#tb-radio-<?= $this->getHook() . '-' . $random_append ?>').change(function () {
                        $selectedItem = jQuery(this).closest('.tbk-reservation-form-container').find('.tbk-ticket-value');
                        tbUpdateTotalPrice($selectedItem);
                    });
                </script>
                <?php
                $random_append = substr(md5(mt_rand()), 0, 8);
                $checked = NULL;
            }
            ?>
            <p class="tbk-field-description"><?= $this->getDescription() ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}