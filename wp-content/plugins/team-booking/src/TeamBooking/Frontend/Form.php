<?php

namespace TeamBooking\Frontend;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Abstracts\FormElement,
    TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Toolkit,
    TeamBooking\Frontend\Components;

/**
 * Class Form
 *
 * @author VonStroheim
 */
class Form
{
    public $instance;
    public $start_end_times;
    public $coworker_string;
    public $service_location;
    /** @var \TeamBooking\Services\Appointment | \TeamBooking\Services\Event | \TeamBooking\Services\Unscheduled */
    public $service;
    private $coworker_id;
    private $calendar_id;
    private $event_id;
    private $service_id;
    private $tickets_left;
    private $event_id_parent;
    private $slot_start;
    private $slot_end;
    /** @var \TeamBookingSettings */
    private $settings;
    private $timezone;
    /** @var \TeamBooking\Slot|NULL */
    private $slot;

    /**
     * @param \TeamBooking\Slot $slot
     */
    private function scheduled(\TeamBooking\Slot $slot)
    {
        $this->settings = Functions\getSettings();
        try {
            if (Database\Services::get($slot->getServiceId())->getClass() !== 'unscheduled') {
                $this->event_id = $slot->getEventId();
                $this->service_id = $slot->getServiceId();
                $this->coworker_id = $slot->getCoworkerId();
                $this->calendar_id = $slot->getCalendarId();
                $this->event_id_parent = $slot->getEventIdParent();
                $this->slot_start = $slot->getStartTime();
                $this->slot_end = $slot->getEndTime();
                $this->timezone = $slot->getTimezone();
                $this->service = Database\Services::get($slot->getServiceId());
                if ($this->service->getClass() === 'event') {
                    $this->tickets_left = $this->service->getSlotMaxTickets() - $slot->getAttendeesNumber();
                }
                $this->service_location = $slot->getLocation();
                $this->start_end_times = $slot->getTimesString();
                $this->coworker_string = $slot->getCoworkerDisplayString();
                $this->slot = $slot;
            }
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * @param $service_id
     */
    private function unscheduled($service_id)
    {
        $this->settings = Functions\getSettings();
        $this->service_id = $service_id;
        try {
            $this->service = Database\Services::get($service_id);
            $this->service_location = $this->service->getLocation();
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * @param $service_id
     *
     * @return Form
     */
    public static function fromService($service_id)
    {
        $form = new Form();
        $form->unscheduled($service_id);

        return $form;
    }

    /**
     * @param \TeamBooking\Slot $slot
     *
     * @return Form
     */
    public static function fromSlot(\TeamBooking\Slot $slot)
    {
        $form = new Form();
        $form->scheduled($slot);

        return $form;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        $string_total_tickets = __('Tickets', 'team-booking');
        $string_book_now = __('Book now', 'team-booking');
        $string_book_and_pay = __('Book and pay', 'team-booking');

        if (did_action('tbk_reservation_form_header') < 1) {
            add_action('tbk_reservation_form_header', array(
                $this,
                'form_header',
            ));
        }
        if (did_action('tbk_reservation_form_map') < 1) {
            add_action('tbk_reservation_form_map', array(
                $this,
                'form_map',
            ));
        }
        if (did_action('tbk_reservation_form_description') < 1) {
            add_action('tbk_reservation_form_description', array(
                $this,
                'form_description',
            ));
        }

        if (is_user_logged_in()) {
            // TODO: use only wp methods
            $user = $this->settings->getCoworkerData(get_current_user_id());
            // TODO: better logic
            if ($this->service->getClass() === 'unscheduled' && $this->service->getMaxReservationsUser()) {
                $customer = new \TeamBooking\Customer(get_user_by('id', get_current_user_id()), Database\Reservations::getAll());
                $customer_reservations_left = $this->service->getMaxReservationsUser() - $customer->getEnumerableReservations($this->service_id);
                if ($customer_reservations_left <= 0) {
                    return $this->getContentReservationsFinished();

                }
            }
        }
        $form_fields = Database\Forms::get($this->service->getForm(), TRUE);
        $form_fields = Functions\manipulate_frontend_form_fields($form_fields, $this->service, $this->slot);
        $there_are_files = FALSE;
        $form_fields_markup = array();
        $form_fields_hidden_markup = array();

        // fields pre-processing
        foreach ($form_fields as $field) {
            if (!($field instanceof FormElement)) continue;
            // Check for file upload fields
            if ($field->getType() === 'file_upload') {
                $there_are_files = TRUE;
            }
            switch ($field->getHook()) {
                case 'first_name':
                    $default_value = (isset($user) && $this->settings->getAutofillReservationForm() ? $user->getFirstName() : '');
                    if (!empty($default_value) && $this->settings->getAutofillReservationForm() === 'hide') {
                        $field->setHidden(TRUE);
                    }
                    break;
                case 'second_name':
                    $default_value = (isset($user) && $this->settings->getAutofillReservationForm() ? $user->getLastName() : '');
                    if (!empty($default_value) && $this->settings->getAutofillReservationForm() === 'hide') {
                        $field->setHidden(TRUE);
                    }
                    break;
                case 'email':
                    $default_value = (isset($user) && $this->settings->getAutofillReservationForm() ? $user->getEmail() : '');
                    if (!empty($default_value) && $this->settings->getAutofillReservationForm() === 'hide') {
                        $field->setHidden(TRUE);
                    }
                    break;
                case 'url':
                    $default_value = (isset($user) && $this->settings->getAutofillReservationForm() ? $user->getUrl() : '');
                    if (!empty($default_value) && $this->settings->getAutofillReservationForm() === 'hide') {
                        $field->setHidden(TRUE);
                    }
                    break;
                default:
                    $default_value = (
                    isset($user)
                    && $this->settings->getAutofillReservationForm()
                    && $field->getData('prefill')
                        ? get_user_meta($user->getId(), $field->getData('prefill'), TRUE)
                        : $field->getData('value')
                    );
                    if (!empty($default_value) && $this->settings->getAutofillReservationForm() === 'hide') {
                        $field->setHidden(TRUE);
                    }
                    break;
            }

            if ($field->getType() === 'checkbox') {
                $field->setData('value', __('Selected', 'team-booking'));
            } else {
                $field->setData('value', $default_value);
            }

            if ($field->isHidden()) {
                $form_fields_hidden_markup[] = $field;
            } else {
                $form_fields_markup[] = $field;
            }
        }

        ob_start();
        ?>
        <?php
        if (isset($this->event_id)) {
            $start_date_time_object = new \DateTime($this->slot_start, new \DateTimeZone($this->timezone));
            $start_date_time_object->setTimezone(new \DateTimeZone($this->timezone));
            echo Components\NavigationHeader::InReservationForm($start_date_time_object);
        }
        ?>
        <div class="tbk-reservation-form-container">
            <?php Functions\reservation_form_header($this) ?>
            <div class="tbk-content">
                <?php Functions\reservation_form_description($this) ?>
                <?php Functions\reservation_form_map($this); ?>
                <!-- form section -->
                <div class="ui horizontal tbk-divider">
                    <i class="info letter tb-icon"></i>
                </div>
                <?php
                /**
                 * Now we're setting up the form
                 */
                $form_enctype = $there_are_files ? 'enctype="multipart/form-data"' : '';
                ?>
                <form class="tbk-reservation-form" method="POST" action="" <?= $form_enctype ?>>
                    <?php wp_nonce_field('teambooking_submit_reservation', 'nonce') ?>
                    <input type="hidden" name="tickets" value="1">
                    <input type="hidden" name="service" value="<?= esc_attr($this->service_id) ?>">
                    <input type="hidden" name="post_id" value="">
                    <input type="hidden" name="service_location" value="<?= $this->service_location ?>">
                    <input type="hidden" name="customer_wp_id"
                           value="<?= isset($user) ? esc_attr($user->getId()) : '' ?>">
                    <?php if ($this->service->getClass() !== 'service') { ?>
                        <input type="hidden" name="owner" value="<?= esc_attr($this->coworker_id) ?>">
                        <input type="hidden" name="calendar_id"
                               value="<?= Toolkit\base64UrlEncode($this->calendar_id) ?>">
                        <input type="hidden" name="event_id" value="<?= $this->event_id ?>">
                        <input type="hidden" name="event_id_parent" value="<?= $this->event_id_parent ?>">
                        <input type="hidden" name="slot_start" value="<?= $this->slot_start ?>">
                        <input type="hidden" name="slot_end" value="<?= $this->slot_end ?>">
                    <?php }

                    //Let's render the hidden fields (pre-filled user data) if any
                    foreach ($form_fields_hidden_markup as $field) {
                        /** @var $field FormElement */
                        echo $field->getMarkup(TRUE);
                    }

                    // Start grouping (max two, for now)
                    $group_limit = NULL;
                    $number_of_fields = count($form_fields_markup);
                    if ($number_of_fields > 3 && $number_of_fields < 700) {
                        $group_limit = 2;
                        $group_limit_textual = 'tbk-two';
                    } else {
                        $group_limit = 3;
                        $group_limit_textual = 'three';
                    }
                    $i = 1;
                    $j = 0;
                    foreach ($form_fields_markup as $field) {
                        $j++;

                        if ($field->getType() === 'paragraph') {
                            if ($i === 1) {
                                echo "<div class='tbk-one tbk-fields'>";
                            } else {
                                echo "</div><div class='tbk-one tbk-fields'>";
                            }
                        } else {
                            if ($i === 1) echo "<div class='$group_limit_textual tbk-fields'>";
                        }

                        /** @var $field FormElement */
                        echo $field->getMarkup();
                        // Close grouping
                        if ($i === $group_limit || $j === $number_of_fields || $field->getType() === 'paragraph') {
                            $i = 1; // reset
                            echo '</div>';
                        } else {
                            $i++;
                        }
                    }
                    ?>
                </form>
            </div>
            <!-- modal footer -->
            <div class="tbk-reservation-form-footer">
                <!-- tickets and price section -->
                <?php if ($this->service->getClass() === 'event' || $this->service->getPotentialPrice() > 0) {
                    if ($this->service === 'unscheduled') {
                        $discounted_price = Functions\getDiscountedPrice($this->service);
                    } else {
                        $discounted_price = Functions\getDiscountedPrice($this->service, Functions\strtotime_tb($this->slot_start), Functions\strtotime_tb($this->slot_end));
                    }
                    if ($this->service->getPrice() != $discounted_price) {
                        $price_string = Functions\currencyCodeToSymbol($this->settings->getCurrencyCode(), NULL)
                            . '<del>' . Functions\priceFormat($this->service->getPrice()) . '</del><span class="tbk-discounted-price"> '
                            . Functions\priceFormat($discounted_price) . '</span>';
                    } else {
                        $price_string = Functions\currencyCodeToSymbol($this->settings->getCurrencyCode(), $this->service->getPrice());
                    }
                    $currency_array = Toolkit\getCurrencies($this->settings->getCurrencyCode());
                    ?>
                    <div class="tbk-tickets-price-section"
                         data-currency-format="<?= Functions\currencyCodeToSymbol($this->settings->getCurrencyCode(), 0, 0, TRUE) ?>"
                         data-currency-symbol="<?= htmlentities(Functions\currencyCodeToSymbol($this->settings->getCurrencyCode(), NULL)) ?>">
                        <table style="width: auto;margin: 0;">
                            <tr>
                                <?php
                                // Tickets number
                                if ($this->service->getClass() === 'event') { ?>
                                    <td class="tbk-tickets-span">
                                        <?= esc_html($string_total_tickets) ?>
                                        <?php if ($this->service->getPotentialPrice() > 0) { ?>
                                            <span class="tbk-total-price-line"
                                                  style="display: <?= $this->service->getPrice() > 0 ? 'inline-block' : 'none' ?>;">
                                                    <?php if ($this->service->getClass() === 'event') { ?>
                                                        @
                                                        <span class="tbk-total-price-line-price-unit"
                                                              style="font-style: italic;"
                                                              data-base="<?= htmlentities($price_string) ?>"
                                                              data-decimals="<?= $currency_array['decimal'] === TRUE ? 2 : 0 ?>">
                                                        <?= $price_string ?>
                                                        </span>
                                                        <span class="tbk-tickets-span-each">
                                                            /<?= esc_html__('each', 'team-booking') ?>
                                                        </span>
                                                    <?php } ?>
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td class="tbk-ticket-value-cell">
                                        <?php
                                        if ($this->service->getSlotMaxUserTickets() <= 1 && !current_user_can('manage_options')) {
                                            echo '1';
                                            ?>
                                            <input class="tbk-ticket-value" type="hidden"
                                                   value="1"
                                                   data-price-num="<?= $this->service->getPrice() ?>"
                                                   data-price-disc-num="<?= $discounted_price ?>"/>
                                            <?php
                                        } else {
                                            if ($this->tickets_left < $this->service->getSlotMaxUserTickets() || current_user_can('manage_options')) {
                                                $i_max = $this->tickets_left;
                                            } else {
                                                $i_max = $this->service->getSlotMaxUserTickets();
                                            }
                                            ?>
                                            <input class="tbk-ticket-value" required type="number" min="1" value="1"
                                                   max="<?= $i_max ?>"
                                                   pattern="[0-9]"
                                                   step="1" data-price-num="<?= $this->service->getPrice() ?>"
                                                   data-price-disc-num="<?= $discounted_price ?>"
                                                   style="padding: 6px;font-weight: 400;"/>
                                        <?php } ?>
                                    </td>
                                <?php } else {
                                    ?>
                                    <td>
                                        <input class="tbk-ticket-value" type="hidden" value="1"
                                               data-price-num="<?= $this->service->getPrice() ?>"
                                               data-price-disc-num="<?= $discounted_price ?>"/>
                                    </td>
                                    <?php
                                } ?>
                                <?php if ($this->service->getPotentialPrice() > 0) { ?>
                                    <td <?= ($this->service->getClass() === 'event') ? 'style="text-align: right;"' : '' ?>>
                                        <div style="
                                     font-weight: 300;
                                     font-style: italic;
                                     display: inline-block;
                                     ">
                                            <div class="tbk-total-price-line"
                                                 style="display: <?= $this->service->getPrice() > 0 ? 'inline-block' : 'none' ?>;">
                                            </div>
                                        </div>
                                    </td>
                                <?php } ?>
                            </tr>

                            <?php Functions\frontend_form_add_ticket_row($this->service, $this->slot); ?>

                        </table>
                    </div>
                <?php } ?>
                <div class="tbk-book-now">
                    <!-- confirm button -->
                    <button class="tbk-book-now-button" type="submit" data-files="<?= $there_are_files ? 1 : 0 ?>">
                        <?= count($this->settings->getPaymentGatewaysActive()) == 1
                        && $this->service->getSettingsFor('payment') === 'immediately'
                        && $this->service->getPrice() > 0
                            ? esc_html($string_book_and_pay)
                            : esc_html($string_book_now) ?>

                        <?php if ($this->service->getPotentialPrice() > 0) { ?>
                            <span class="tbk-total-price-line-price"
                                  data-base="<?= htmlentities($price_string) ?>"
                                  data-decimals="<?= $currency_array['decimal'] === TRUE ? 2 : 0 ?>">
                                <?= $price_string ?>
                            </span>
                        <?php } ?>

                        <?= isset($customer_reservations_left) ? '<span class="tbk-services-left">(' . $customer_reservations_left . ' ' . esc_html__('left', 'team-booking') . ')</span>' : '' ?>
                    </button>
                </div>
            </div>
            <?php if ($this->service->getClass() === 'event'
                && current_user_can('manage_options')
                && $this->service->getSlotMaxUserTickets() < $this->service->getSlotMaxTickets()
            ) { ?>
                <p class="ui negative message tbk-logged-admin-advice">
                    <?= esc_html__("You are logged in as Administrator, any limit on tickets number won't be applied", 'team-booking') ?>
                </p>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param bool   $direct
     * @param string $event_id
     * @param string $service_id
     * @param string $coworker_id
     * @param mixed  $post_id
     *
     * @return mixed
     */
    public static function getContentRegisterAdvice($direct = FALSE, $event_id = '', $service_id = '', $coworker_id = '', $post_id = NULL)
    {
        if (Functions\getSettings()->getRedirectBackAfterLogin()) {
            $redirect_url = get_permalink($post_id);
            if (!empty($event_id)) {
                $events = Database\Events::getByEventId($event_id);
                if (empty($events)) {
                    $broken_id = explode('_', $event_id, 2);
                    if (count($broken_id) === 2) {
                        $events = Database\Events::getByEventId($broken_id[0], strtotime($broken_id[1]));
                    }
                }
                if (!empty($events)) {
                    if (isset($events[ $coworker_id ])) {
                        $events = reset($events[ $coworker_id ]);
                    }
                }
                $event = NULL;
                if (is_array($events) && isset($events[ $event_id ])) {
                    $event = $events[ $event_id ];
                }
                if ($event instanceof Database\eventObject) {
                    $event->id = $event_id;
                    $slot = \TeamBooking\Slot::getFromEvent($event, $service_id, $coworker_id);
                    $redirect_url = add_query_arg('tbk_date', $slot->getDateString(TRUE), $redirect_url);
                }
            }
            $registration_url = add_query_arg('redirect_to', urlencode($redirect_url), Functions\getSettings()->getRegistrationUrl());
            $login_url = add_query_arg('redirect_to', urlencode($redirect_url), Functions\getSettings()->getLoginUrl());
        } else {
            $registration_url = Functions\getSettings()->getRegistrationUrl();
            $login_url = Functions\getSettings()->getLoginUrl();
        }
        ob_start();
        ?>
        <?php if ($direct) echo '<div style="text-align:center;">' ?>
        <div style="margin:10px 0 20px 0;padding: 0 20px;text-align: center;">
            <?= esc_html__('You must be logged-in to book this!', 'team-booking') ?>
        </div>
        <div>
            <a href="<?= esc_url($registration_url) ?>"
               class="tbk-button tbk-red"><?= esc_html__("I'm not registered...", 'team-booking') ?></a>
        </div>
        <div>
            <a href="<?= esc_url($login_url) ?>"
               class="tbk-button tbk-green"><?= esc_html__('Login', 'team-booking') ?></a>
        </div>
        <?php
        if (!$direct) { ?>
            <div>
                <div class="tbk-button tbk-dimmer-off" tabindex="0">
                    <?= esc_html__('Go back', 'team-booking') ?>
                </div>
            </div>
        <?php } ?>
        <?php if ($direct) echo '</div>' ?>
        <?php

        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function getContentReservationsFinished()
    {
        ob_start();
        ?>
        <div style="margin:10px 0 20px 0;text-align: center;">
            <div class="tbk-reservation-limit-service-name"><?= $this->service->getName() ?></div>
            <?= esc_html__("You've reached the limit for that service!", 'team-booking') ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @param \TeamBooking\Frontend\Form $form
     */
    public function form_header($form)
    {
        echo Components\FormHeader::get($form->service->getName(), $form->start_end_times, $form->coworker_string);
    }

    /**
     * @param \TeamBooking\Frontend\Form $form
     */
    public function form_map($form)
    {
        Components\FormMap::render($form->service_location, $this->service->getSettingsFor('show_map'));
    }

    /**
     * @param \TeamBooking\Frontend\Form $form
     */
    public function form_description($form)
    {
        if ($form->service->getDescription()) Components\FormServiceDescription::render($form->service->getDescription());
    }

}
