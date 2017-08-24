<?php

namespace TeamBooking\Frontend\Components;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class PaymentChoices
 *
 * Shows the payment choices to the customer
 *
 * @author VonStroheim
 */
class PaymentChoices
{
    /**
     * @param \TeamBooking_ReservationData $data
     */
    public static function render(\TeamBooking_ReservationData $data, $navigation = TRUE)
    {
        if ($navigation) echo NavigationHeader::InPaymentChoice($data->getServiceName());
        ?>
        <div class="tbk-pre-payment"
             data-checksum="<?= $data->getToken() ?>"
             data-id="<?= $data->getDatabaseId() ?>">
            <div class="ui stackable equal width center aligned tbk-grid tbk-payment-choices">
                <?php
                $active_payment_gateways = \TeamBooking\Functions\getSettings()->getPaymentGatewaysActive();
                $i = 1;
                foreach ($active_payment_gateways as $gateway) {
                    /* @var $gateway \TeamBooking_PaymentGateways_Settings */
                    ?>
                    <?php if ($i != 1) { ?>
                        <div class="ui vertical tbk-divider">
                            <?= esc_html__('or', 'team-booking') ?>
                        </div>
                    <?php } ?>
                    <div class="tbk-column">
                        <?= $gateway->getPayButton() ?>
                    </div>
                    <?php
                    $i++;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * @param \TeamBooking_ReservationData $data
     *
     * @return string
     */
    public static function get(\TeamBooking_ReservationData $data)
    {
        ob_start();
        PaymentChoices::render($data);

        return ob_get_clean();
    }
}