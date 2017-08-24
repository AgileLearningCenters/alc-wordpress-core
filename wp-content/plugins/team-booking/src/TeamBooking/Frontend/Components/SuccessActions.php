<?php

namespace TeamBooking\Frontend\Components;
defined('ABSPATH') or die("No script kiddies please!");

/**
 * Class SuccessActions
 *
 * Shows the action buttons after a successful reservation
 *
 * @package TeamBooking\Frontend\Components
 */
class SuccessActions
{
    /**
     * @param \TeamBooking_ReservationData $data
     *
     * @throws \Exception
     */
    public static function render(\TeamBooking_ReservationData $data)
    {
        ?>
        <div class="tbk-after-reservation-actions">
            <?php
            if ($data->getServiceClass() !== 'unscheduled'
                && \TeamBooking\Functions\getSettings()->getShowIcal()
            ) {
                ?>
                <form id="tb-get-ical-form" method="POST" action="<?= admin_url() . 'admin-post.php' ?>">
                    <input type="hidden" name="action" value="tb_get_ical">
                    <?php wp_nonce_field('team_booking_options_verify') ?>
                    <input type="hidden" name="start" value="<?= $data->getStart() ?>">
                    <input type="hidden" name="end" value="<?= $data->getEnd() ?>">
                    <input type="hidden" name="description"
                           value="<?= esc_attr(\TeamBooking\Database\Services::get($data->getServiceId())->getDescription()) ?>">
                    <input type="hidden" name="summary" value="<?= esc_attr($data->getServiceName()) ?>">
                    <input type="hidden" name="uri" value="">
                    <input type="hidden" name="address" value="<?= esc_attr($data->getServiceLocation()) ?>">
                    <button type="submit" class="tbk-button tb-get-ical" tabindex="0">
                        <i class="tb-icon calendar outline"></i>
                        <?= esc_html__('Save on my calendar', 'team-booking') ?>
                    </button>
                </form>
            <?php } ?>
            <button class="tbk-button tbk-green tbk-refresh" tabindex="0">
                <?= esc_html__('Ok', 'team-booking') ?>
            </button>
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
        SuccessActions::render($data);

        return ob_get_clean();
    }
}