<?php

namespace TeamBooking\Frontend;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Toolkit,
    TeamBooking\Frontend\Components;

/**
 * Class Review
 *
 * @author VonStroheim
 */
class Review
{
    /**
     * @param \TeamBooking_ReservationData $data
     *
     * @return mixed
     * @throws \Exception
     */
    public static function get(\TeamBooking_ReservationData $data)
    {
        add_action('tbk_reservation_review_details', array(
            'TeamBooking\\Frontend\\Review',
            'review_details',
        ));
        add_action('tbk_reservation_review_footer', array(
            'TeamBooking\\Frontend\\Review',
            'review_footer',
        ));
        $service = Database\Services::get($data->getServiceId());
        ob_start();
        ?>
        <div class="tbk-reservation-review-container" data-reservation="<?= Toolkit\objEncode($data) ?>">
            <div class="tbk-reservation-review-header">
                <span class="tbk-thin-italic"><?= esc_html__('Review your reservation', 'team-booking') ?></span>
            </div>
            <div class="tbk-reservation-review-content">
                <?php Functions\reservation_review_details($data); ?>
            </div>
            <div class="tbk-reservation-review-footer">
                <?php Functions\reservation_review_footer($service); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param $text
     */
    public static function review_header($text)
    {
        echo Components\NavigationHeader::InReservationReview($text);
    }

    /**
     * @param \TeamBooking_ReservationData $data
     */
    public static function review_details($data)
    {
        $service = Database\Services::get($data->getServiceId());
        ?>
        <table>
            <?php if ($data->getServiceClass() !== 'unscheduled' && $service->getSettingsFor('show_times') !== 'no') {
                $timezone = new \DateTimeZone($data->getCustomerTimezone());
                $start = \DateTime::createFromFormat('U', $data->getStart());
                $end = \DateTime::createFromFormat('U', $data->getEnd());
                $start->setTimezone($timezone);
                $end->setTimezone($timezone);
                ?>
                <!-- times row -->
                <tr>
                    <th scope="row">
                        <?= $service->getSettingsFor('show_times') === 'start_time_only' ? esc_html__('When', 'team-booking') : esc_html__('Start', 'team-booking') ?>
                    </th>
                    <td>
                        <?= Functions\date_i18n_tb(get_option('date_format'), $start->getTimestamp() + $start->getOffset()) ?>
                        <?= Functions\date_i18n_tb(get_option('time_format'), $start->getTimestamp() + $start->getOffset()) ?>
                    </td>
                </tr>
                <?php
                if ($service->getSettingsFor('show_times') !== 'start_time_only') {
                    ?>
                    <tr>
                        <th scope="row">
                            <?= esc_html__('End', 'team-booking') ?>
                        </th>
                        <td>
                            <?= Functions\date_i18n_tb(get_option('date_format'), $end->getTimestamp() + $end->getOffset()) ?>
                            <?= Functions\date_i18n_tb(get_option('time_format'), $end->getTimestamp() + $end->getOffset()) ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
            <?php if ($service->getSettingsFor('show_coworker') && NULL !== $data->getCoworker()) { ?>
                <!-- coworker row -->
                <tr>
                    <th scope="row">
                        <?= esc_html__('With', 'team-booking') ?>
                    </th>
                    <td>
                        <?= ucwords(Functions\getSettings()->getCoworkerData($data->getCoworker())->getDisplayName()) ?>
                    </td>
                </tr>
            <?php } ?>
            <!-- form rows -->
            <?php
            foreach ($data->getFormFields() as $field) { ?>
                <tr>
                    <th><?= htmlspecialchars_decode($field->getLabel(), ENT_QUOTES) ?></th>
                    <td>
                        <?= !$field->getValue()
                            ? ('<span class="tbk-not-provided">' . esc_html__('Not provided', 'team-booking') . '</span>')
                            : $field->getValue() ?>
                    </td>
                </tr>
            <?php } ?>
            <?php if ($service->getClass() === 'event') { ?>
                <!-- tickets row -->
                <tr>
                    <th scope="row">
                        <?= esc_html__('Tickets', 'team-booking') ?>
                    </th>
                    <td>
                        <?= $data->getTickets() ?>
                    </td>
                </tr>
            <?php } ?>
            <?php if ($data->getPriceIncremented() > 0) { ?>
                <!-- amount row -->
                <tr>
                    <th scope="row">
                        <?= esc_html__('Total amount', 'team-booking') ?>
                    </th>
                    <td>
                                <span class="total-amount">
                                    <?= Functions\currencyCodeToSymbol(Functions\getSettings()->getCurrencyCode(), $data->getTickets() * $data->getPriceIncremented()) ?>
                                </span>
                        <span class="discounted-amount"></span>
                    </td>
                </tr>
                <?php if (Functions\isThereOneCouponAtLeast($data->getServiceId())) { ?>
                    <!-- coupon row -->
                    <tr>
                        <th scope="row">
                            <?= esc_html__('Coupon?', 'team-booking') ?>
                        </th>
                        <td>
                            <div class="tbk-file-input tbk-coupon-section">
                                <input style="padding:4px;" type="text" class="tbk-coupon-value"
                                       id="_tbk_coupon_<?= $data->getCreationInstant() ?>">
                                <label for="tbk_coupon_<?= $data->getCreationInstant() ?>"
                                       class="tbk-button tbk-file-button tbk-coupon-claim" tabindex="0"
                                       aria-label="<?= esc_html__('redeem discount coupon', 'team-booking') ?>"
                                       id="label_tbk_coupon_<?= $data->getCreationInstant() ?>">
                                                <span
                                                    class="claim-text"><?= esc_html__('redeem', 'team-booking') ?></span>
                                            <span class="cancel-text">
                                                <i class="dashicons dashicons-no-alt" style="display: none;"></i>
                                            </span>
                                </label>
                            </div>
                            <p class="tbk-wrong-coupon" style="display: none;">
                                <?= esc_html__('Wrong or expired coupon', 'team-booking') ?>
                            </p>
                            <p class="tbk-right-coupon" style="display: none;"></p>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </table>
        <?php if (current_user_can('manage_options') && $service->getPrice() > 0) { ?>
        <div class="ui negative message">
            <?= esc_html__('You are logged-in as Administrator. You will skip any eventual payment step!', 'team-booking') ?>
        </div>
    <?php }
    }

    /**
     * @param \TeamBooking\Abstracts\Service $service
     */
    public static function review_footer($service)
    {
        ?>
        <button class="tbk-book-confirmation-button">
            <?= count(Functions\getSettings()->getPaymentGatewaysActive()) === 1
            && $service->getSettingsFor('payment') === 'immediately'
            && $service->getPrice() > 0
                ? esc_html__('Confirm and pay', 'team-booking')
                : esc_html__('Confirm', 'team-booking') ?>
        </button>
        <?php
    }
}