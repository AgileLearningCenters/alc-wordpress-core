<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Admin;
use TeamBooking\Database\Forms,
    TeamBooking\Database\Reservations,
    TeamBooking\Database\Services,
    TeamBooking\Functions,
    TeamBooking\Toolkit;

/**
 * Class Overview
 *
 * @author VonStroheim
 */
class Overview
{
    public $new_reservation_ids;
    private $settings;
    /** @var \TeamBooking_ReservationData[] */
    private $reservations;
    private $filter;

    /**
     * Overview constructor.
     *
     * @param bool $pending
     */
    public function __construct($pending = FALSE)
    {
        $this->filter = isset($_GET['filter']) ? $_GET['filter'] : NULL;
        if (current_user_can('manage_options')) {
            $this->reservations = Reservations::getAll($this->filter);
        } else {
            $this->reservations = Reservations::getByCoworker(get_current_user_id(), $this->filter);
        }
        $this->settings = Functions\getSettings();
    }

    /**
     * @return string
     */
    public function getPostBody()
    {
        ob_start();
        ?>
        <div class="tbk-wrapper" xmlns="http://www.w3.org/1999/html">

            <div class="tbk-row">
                <?= $this->getDataExportForms() ?>
                <div class="tbk-column tbk-span-12">
                    <?php $this->getReservationList()->render() ?>
                </div>
            </div>
            <div class="ui small modal tbk-reservation-details-modal" style="display: none"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @return Framework\PanelForList
     */
    public function getReservationList()
    {
        $panel = new Framework\PanelForList(__('Reservations', 'team-booking'));

        ob_start();
        echo '<form method="post" class="ays-ignore">';
        $table = new ReservationsTable($this->new_reservation_ids);
        $table->views();
        $table->prepare_items();
        $table->search_box(esc_html__('Search', 'team-booking'), 'tbk-search');
        $table->display();
        echo '</form>';

        if (current_user_can('manage_options')) {
            // Delete Modal Markup
            $modal = new Framework\Modal('tb-reservation-delete-modal');
            $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
            $modal->setButtonText(array('approve' => __('Yes', 'team-booking'), 'deny' => __('No', 'team-booking')));
            $modal->addContent(esc_html__('The reservation will be removed from the database.', 'team-booking'));
            $modal->addContent(Framework\Html::paragraph(__('Please note: non-expired-nor-cancelled Appointments Classes will stay booked, non-expired-nor-cancelled Events Classes will regain the tickets, but customers and coworkers will not be notified of that.', 'team-booking')));
            $modal->render();
        }
        // Cancellation Modal Markup
        $modal = new Framework\Modal('tb-reservation-cancel-modal');
        $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
        $modal->setButtonText(array('approve' => __('Yes', 'team-booking'), 'deny' => __('No', 'team-booking')));
        $modal->addContent(Framework\Html::span(array('class' => 'previously-confirmed', 'text' => __('The reservation will be revoked, and the slot will be freed.', 'team-booking'))));
        $modal->addContent('<div>');
        $modal->addContent(Framework\Html::paragraph(__('Add a reason (optional, will be added to the cancellation email):', 'team-booking')));
        $modal->addContent('<textarea style="width:100%;" id="reason"></textarea>');
        $modal->addContent('</div>');
        $modal->addContent(Framework\Html::paragraph(__('Please note: non-expired-nor-cancelled Appointments Classes will stay booked, non-expired-nor-cancelled Events Classes will regain the tickets, but customers and coworkers will not be notified of that.', 'team-booking')));
        $modal->render();

        // Confirmation Modal
        $modal = new Framework\Modal('tb-delete-all-reservations-modal');
        $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
        $modal->setButtonText(array('approve' => __('Yes', 'team-booking'), 'deny' => __('No', 'team-booking')));
        $modal->addContent(Framework\Html::paragraph(__('All the reservations for all the services, regardless of current filtering options, will be removed from the database. This action is permanent.', 'team-booking')));
        $modal->addContent(Framework\Html::paragraph(__('Please note: non-expired-nor-cancelled Appointments Classes will stay booked, non-expired-nor-cancelled Events Classes will regain the tickets, but customers and coworkers will not be notified of that.', 'team-booking')));
        $modal->render();

        $panel->addElement(Framework\ElementFrom::content(ob_get_clean()));

        return $panel;
    }

    /**
     * TODO: remove
     *
     * @return mixed
     */
    public function getLastErrors()
    {
        ob_start();
        ?>

        <div class="tbk-content">
            <table class="widefat">
                <tbody>
                <tr class="alternate">
                    <th style="font-weight:bold"><?= esc_html__('Message', 'team-booking') ?></th>
                    <th style="font-weight:bold"><?= esc_html__('When', 'team-booking') ?></th>

                    <?php if (current_user_can('manage_options')) { ?>
                        <th style="font-weight:bold"><?= esc_html__('Coworker', 'team-booking') ?></th>
                    <?php } ?>
                </tr>

                <?php
                $error_logs = $this->settings->getErrorLogs();

                if (!empty($error_logs)) {

                    $error_logs = array_reverse($this->settings->getErrorLogs());

                    foreach ($error_logs as $log) {
                        /* @var $log \TeamBooking_ErrorLog */
                        if (!current_user_can('manage_options') && get_current_user_id() != $log->getCoworkerId()) {
                            continue;
                        }
                        ?>
                        <tr>
                            <td>
                                <a href="#" title="<?= $log->getDescription() ?>"><?= $log->getMessage() ?></a>
                            </td>

                            <td>
                                <?= Functions\date_i18n_tb(get_option('date_format') . ' ' . get_option('time_format'), $log->getTimestamp()) ?>
                            </td>

                            <?php if (current_user_can('manage_options')) { ?>
                                <td>
                                    <?= $log->getCoworker() ?>
                                </td>
                            <?php } ?>

                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getStats()
    {
        // retrieve some reservations count
        $non_expired_reservations_num = 0;
        $cancelled_reservations_num = 0;
        $waiting_for_approval_num = 0;
        $at_least_one_service_with_approval = FALSE;
        $total_amount = 0;
        $paid_amount = 0;
        foreach ($this->reservations as $id => $reservation) {
            if (!Functions\checkServiceIdExistance($reservation->getServiceId())) {
                continue;
            }
            if (!current_user_can('manage_options')
                && $reservation->getCoworker() != get_current_user_id()
            ) continue;
            if (!Functions\isReservationPastInTime($reservation)
                ||
                ($reservation->getServiceClass() === 'unscheduled' && $reservation->isConfirmed())
            ) {
                $non_expired_reservations_num++;
            }
            if ($reservation->isCancelled()) {
                $cancelled_reservations_num++;
            }
            if (Services::get($reservation->getServiceId())->getSettingsFor('approval_rule') !== 'none') {
                $at_least_one_service_with_approval = TRUE;
            }
            if ($reservation->isWaitingApproval()) {
                $waiting_for_approval_num++;
            }
            if (!$reservation->isCancelled()
                || (
                    $reservation->isCancelled()
                    && $reservation->isPaid()
                )
            ) {
                if (count($reservation->getDiscount()) > 1) {
                    $total_amount += $reservation->getTickets() * $reservation->getPriceDiscounted();
                } else {
                    $total_amount += $reservation->getTickets() * $reservation->getPrice();
                }
            }
            if ($reservation->isPaid()) {
                if (count($reservation->getDiscount()) > 1) {
                    $paid_amount += $reservation->getTickets() * $reservation->getPriceDiscounted();
                } else {
                    $paid_amount += $reservation->getTickets() * $reservation->getPrice();
                }
            }
        }
        $currency_symbol = '$';
        foreach (Toolkit\getCurrencies() as $code => $currency) {
            if ($code == $this->settings->getCurrencyCode()) {
                $currency_symbol = $currency['symbol'];
            }
        }
        $string_1 = __('Upcoming', 'team-booking');
        $string_2 = __('Cancelled', 'team-booking');
        $string_3 = __('Total amount', 'team-booking');
        $string_4 = __('Paid amount', 'team-booking');
        $string_5 = __('Waiting for approval', 'team-booking');

        ob_start();
        // Non-expired reservations
        Framework\Stats::get($string_1, $non_expired_reservations_num)->render();
        // Waiting for approval
        if ($at_least_one_service_with_approval || $waiting_for_approval_num > 0) {
            Framework\Stats::get($string_5, $waiting_for_approval_num)->render();
        }
        // Cancelled reservations
        Framework\Stats::get($string_2, $cancelled_reservations_num)->render();
        // Total & paid amount
        if ($total_amount > 0) {
            Framework\Stats::get($string_3, $total_amount . Framework\Html::span(array('text' => $currency_symbol, 'class' => 'currency')))->render();
            Framework\Stats::get($string_4, $paid_amount . Framework\Html::span(array('text' => $currency_symbol, 'class' => 'currency')))->render();
        }

        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function getDataExportForms()
    {
        ob_start();
        ?>
        <form id="tb-get-csv-form" method="POST"
              action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_csv_all">
            <?php wp_nonce_field('team_booking_options_verify') ?>
        </form>

        <form id="tb-get-xlsx-form" method="POST"
              action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_xlsx_all">
            <?php wp_nonce_field('team_booking_options_verify') ?>
        </form>

        <form id="tb-print-reservation-data-form" method="POST"
              action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_print_res_pdf">
            <?php wp_nonce_field('team_booking_options_verify') ?>
        </form>

        <?php
        return ob_get_clean();
    }

    /**
     * @param $reservation_id
     *
     * @return string
     */
    public static function getReservationDetailsModal($reservation_id)
    {
        $reservation = Reservations::getById($reservation_id);
        if (NULL !== $reservation->getStart()) {
            $timezone = Toolkit\getTimezone();
            $date_time_object = \DateTime::createFromFormat('U', $reservation->getStart());
            $date_time_object_end = \DateTime::createFromFormat('U', $reservation->getEnd());
            $date_time_object->setTimezone($timezone);
            $date_time_object_end->setTimezone($timezone);
            if ($reservation->isAllDay()) {
                $when_value = Functions\date_i18n_tb(get_option('date_format'), $date_time_object->getTimestamp())
                    . ' ' . __('All day', 'team-booking');
            } else {
                $when_value = Functions\date_i18n_tb(get_option('date_format'), $date_time_object->getTimestamp() + $date_time_object->getOffset())
                    . ' '
                    . Functions\date_i18n_tb(get_option('time_format'), $date_time_object->getTimestamp() + $date_time_object->getOffset())
                    . ' - '
                    . Functions\date_i18n_tb(get_option('time_format'), $date_time_object_end->getTimestamp() + $date_time_object_end->getOffset());
            }
        } else {
            $when_value = esc_html__('Unscheduled', 'team-booking');
        }
        $files = $reservation->getFilesReferences();
        ob_start();
        ?>
        <div class="tbk-tabbed-modal ui small modal tbk-reservation-details-modal">
            <i class="close tb-icon"></i>

            <div class="header">
                <?= esc_html__('Reservation', 'team-booking') ?> #<?= $reservation->getDatabaseId() ?>
                <div class="ui mini circular label tbk-print-reservation-data" tabindex="0"
                     style="float: right;background: none;color: white;"
                     title="<?= esc_html__('Print PDF', 'team-booking') ?>"
                     data-id="<?= $reservation->getDatabaseId() ?>">
                    <span class="dashicons dashicons-download"></span>
                </div>
            </div>

            <div class="tbk-tabs">
                <a class="tbk-active tbk-tab-selector" data-show="tb-customer-details">
                    <?= esc_html__('Customer', 'team-booking') ?>
                </a>
                <?php if ($reservation->isPaid() && count($reservation->getPaymentDetails()) > 0) { ?>
                    <a class="tbk-tab-selector" tabindex="0" data-show="tb-payment-details">
                        <?= esc_html__('Payment', 'team-booking') ?>
                    </a>
                <?php } ?>
                <a class="tbk-tab-selector" tabindex="0" data-show="tb-booking-details">
                    <?= esc_html__('Booking', 'team-booking') ?>
                </a>
                <?php if (!empty($files)) { ?>
                    <a class="tbk-tab-selector" tabindex="0" data-show="tb-files-details">
                        <?= esc_html__('Files', 'team-booking') ?>
                    </a>
                <?php } ?>
                <?php if ($reservation->getCancellationReason() || ($reservation->getPendingReason() && $reservation->isPending())) { ?>
                    <a class="tbk-tab-selector" tabindex="0" data-show="tb-other-details">
                        <?= esc_html__('Other', 'team-booking') ?>
                    </a>
                <?php } ?>
            </div>

            <div class="content" style="width: calc(100% - 3rem);">
                <!-- Other details part -->
                <div class="tb-data tb-other-details" style="display:none;">
                    <?php if ($reservation->getCancellationReason()) { ?>
                        <h4><?= esc_html__('Cancellation reason', 'team-booking') ?></h4>
                        <div style="font-style: italic;font-weight: 300;">
                            <?= $reservation->getCancellationReason() ?>
                        </div>
                    <?php } ?>
                    <?php if ($reservation->getPendingReason() && $reservation->isPending()) { ?>
                        <h4><?= esc_html__('Pending reason', 'team-booking') ?></h4>
                        <div style="font-style: italic;font-weight: 300;">
                            <?= $reservation->getPendingReason() ?>
                        </div>
                    <?php } ?>
                </div>

                <!-- Customer details part -->
                <div class="tb-data tb-customer-details">
                    <?php if (current_user_can('manage_options')) { ?>
                        <div class="ui mini circular label tbk-edit-reservation-data" tabindex="0" style="float: right;"
                             title="<?= esc_html__('Edit', 'team-booking') ?>">
                            <span class="dashicons dashicons-edit"></span>
                        </div>
                    <?php } ?>
                    <h4><?= esc_html__('Customer details', 'team-booking') ?></h4>
                    <?php
                    foreach ($reservation->getFieldsArray() as $key => $field) {
                        if ($field) {
                            ?>
                            <div style="font-style: italic;font-weight: 300;">
                                <?php
                                try {
                                    $label_from_hook = Forms::getTitleFromHook(Services::get($reservation->getServiceId())->getForm(), $key);
                                } catch (\Exception $ex) {
                                    $label_from_hook = FALSE;
                                }
                                if ($label_from_hook) {
                                    echo $label_from_hook;
                                } else {
                                    echo $key;
                                }
                                try {
                                    if ($key === 'email'
                                        && $reservation->isConfirmed()
                                        && $reservation->getServiceClass() !== 'unscheduled'
                                        && !Functions\isReservationPastInTime($reservation)
                                        && Services::get($reservation->getServiceId())->getEmailReminder('send')
                                    ) {
                                        echo ' (' . esc_html__('reminder', 'team-booking');
                                        echo ' <span class="tbk-email-reminder-sent"' . ($reservation->isEmailReminderSent() ? '' : ' style="display:none;"') . '>' . esc_html__('sent', 'team-booking') . '</span>';
                                        echo '<span class="tbk-email-reminder-notsent"' . ($reservation->isEmailReminderSent() ? ' style="display:none;"' : '') . '>' . esc_html__('not sent yet', 'team-booking') . ' - </span>';
                                        if (!$reservation->isEmailReminderSent()) {
                                            echo '<a href="#" data-id="' . $reservation->getDatabaseId() . '" class="tbk-email-reminder-send-manually">' . esc_html__('send now', 'team-booking') . '</a>';
                                        }
                                        echo ')';
                                    }
                                } catch (\Exception $e) {
                                    // nothing
                                }
                                ?>
                            </div>
                            <input type="text" class="large-text" style='margin-bottom:10px;' value="<?= $field ?>"
                                   data-key="<?= $key ?>"
                                   readonly="">
                            <?php
                        }
                    }
                    ?>
                    <div style="font-style: italic;font-weight: 300;">
                        <?= esc_html__("Customer's timezone", 'team-booking') ?>
                    </div>
                    <input type="text" class="large-text noedit" style='margin-bottom:10px;'
                           value="<?= $reservation->getCustomerTimezone() ?>" readonly="">
                    <?php
                    if ($reservation->getCustomerUserId()) {
                        $user_data = get_userdata($reservation->getCustomerUserId());
                        ?>
                        <div style="font-style: italic;font-weight: 300;">
                            <?= esc_html__('WordPress User', 'team-booking') ?>
                        </div>
                        <input type="text" class="large-text noedit" style='margin-bottom:10px;'
                               value="<?= $user_data ? $user_data->user_nicename : esc_html__('User removed', 'team-booking') ?>"
                               readonly="">

                    <?php } ?>

                </div>

                <?php
                if ($reservation->isPaid() && count($reservation->getPaymentDetails()) > 0) {
                    ?>
                    <!-- Payment details part -->
                    <div class="tb-data tb-payment-details" style="display:none;">
                        <h4><?= esc_html__('Payment details', 'team-booking') ?></h4>

                        <div style="font-style: italic;font-weight: 300;">
                            <?= esc_html__('Payment gateway', 'team-booking') ?>
                        </div>
                        <input type="text" readonly="" class="large-text" style='margin-bottom:10px;'
                               value="<?= $reservation->getPaymentGateway() ?>">
                        <?php foreach ($reservation->getPaymentDetails() as $key => $detail) { ?>
                            <div style="font-style: italic;font-weight: 300;">
                                <?= ucwords($key) ?>
                            </div>
                            <input type="text" readonly="" class="large-text" style='margin-bottom:10px;'
                                   value="<?= $detail ?>">
                        <?php } ?>
                    </div>
                <?php } ?>

                <!-- Booking details part -->
                <div class="tb-data tb-booking-details" style="display:none;">
                    <h4><?= esc_html__('Booking details', 'team-booking') ?></h4>

                    <div style="font-style: italic;font-weight: 300;">
                        <?= esc_html__('Service', 'team-booking') ?>
                    </div>
                    <input type="text" readonly="" class="large-text" style='margin-bottom:10px;'
                           value="<?= $reservation->getServiceName() ?>">
                    <?php if ($reservation->getServiceLocation() != '') { ?>
                        <div style="font-style: italic;font-weight: 300;">
                            <?= esc_html__('Location', 'team-booking') ?>
                        </div>
                        <input type="text" readonly="" class="large-text" style='margin-bottom:10px;'
                               value="<?= $reservation->getServiceLocation() ?>">
                    <?php } ?>
                    <?php if (current_user_can('manage_options')) { ?>
                        <div style="font-style: italic;font-weight: 300;">
                            <?= esc_html__('Coworker', 'team-booking') ?>
                        </div>
                        <input type="text" readonly="" class="large-text" style='margin-bottom:10px;'
                               value="<?= Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getDisplayName() ?>">
                    <?php } ?>
                    <?php if ($reservation->getServiceClass() !== 'unscheduled') { ?>
                        <div style="font-style: italic;font-weight: 300;">
                            <?= esc_html__('Date of service', 'team-booking') ?>
                        </div>
                        <input type="text" readonly="" class="large-text" style='margin-bottom:10px;'
                               value="<?= esc_attr($when_value) ?>">
                        <div style="font-style: italic;font-weight: 300;">
                            <?= esc_html__('Tickets', 'team-booking') ?>
                        </div>
                        <input type="text" readonly="" class="large-text" style='margin-bottom:10px;'
                               value="<?= $reservation->getTickets() ?>">
                        <?php
                    }
                    ?>
                    <div style="font-style: italic;font-weight: 300;">
                        <?= esc_html__('Total price', 'team-booking') ?>
                    </div>
                    <input type="text" readonly="" class="large-text" style='margin-bottom:10px;'
                           value="<?= Functions\currencyCodeToSymbol($reservation->getCurrencyCode(), $reservation->getTickets() * $reservation->getPriceIncremented()) ?>">

                    <?php if (count($reservation->getDiscount()) > 0) {
                        $discount_array = $reservation->getDiscount();
                        ?>
                        <div style="font-style: italic;font-weight: 300;">
                            <?= esc_html__('Promotions used', 'team-booking') ?>
                        </div>
                        <?php foreach ($discount_array as $discount) {
                            echo '<span class="ui mini label" style="margin-bottom:10px;">'
                                . $discount['name'] . (isset($discount['coupon']) ? (' (' . $discount['coupon'] . ')') : '')
                                . '</span>';
                        }
                        ?>
                    <?php } ?>
                </div>

                <?php
                if (!empty($files)) {
                    ?>
                    <!-- Files details part -->
                    <div class="tb-data tb-files-details" style="display:none;">
                        <h4><?= esc_html__('Attached files', 'team-booking') ?></h4>
                        <?php foreach ($files as $hook => $file_info_array) { ?>
                            <div style="font-style: italic;font-weight: 300;">
                                <?= ucwords(str_replace('_', ' ', $hook)) ?>
                            </div>
                            <a href="<?= $file_info_array['url'] ?>"><?= esc_html__('Open it', 'team-booking') ?></a>
                        <?php } ?>
                    </div>
                    <?php
                }
                ?>
            </div>

            <div class="actions">
                <div class="ui button green tbk-edit-reservation-data-save"
                     data-id="<?= $reservation->getDatabaseId() ?>"
                     style="height: auto;display: none;">
                    <?= esc_html__('Save', 'team-booking') ?>
                </div>
                <div class="ui button deny black" style="height: auto;">
                    <?= esc_html__('Close', 'team-booking') ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
