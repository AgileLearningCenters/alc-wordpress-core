<?php

namespace TeamBooking\Functions;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Abstracts\FormElement,
    TeamBooking\Abstracts\Service,
    TeamBooking\Cache,
    TeamBooking\Database,
    TeamBooking\Toolkit,
    TeamBooking\EmailHandler,
    TeamBooking\Slot;

include_once dirname(TEAMBOOKING_FILE_PATH) . '/includes/tb_ajax_calls.php';
include_once dirname(TEAMBOOKING_FILE_PATH) . '/includes/tb_file_generators.php';
include_once dirname(TEAMBOOKING_FILE_PATH) . '/includes/tb_toolkit.php';

/**
 * Returns the plugin settings object
 *
 * @return \TeamBookingSettings Plugin settings object
 */
function getSettings()
{
    if (NULL === Cache::get('settings')) {
        Cache::add(get_option('team_booking'), 'settings');
    }

    return Cache::get('settings');
}

/**
 * Get the Coworker ID list
 *
 * @return array Coworkers ID list
 */
function getCoworkersIdList()
{
    $all_users = get_users();
    $coworkers = array();
    foreach ($all_users as $user) {
        if ($user->has_cap('tb_can_sync_calendar')) {
            $coworkers[] = $user->ID;
        }
    }
    if (NULL !== $coworkers) {
        return array_values($coworkers);
    } else {
        return array();
    }
}

/**
 * Get the authorized Coworker ID list
 *
 * @return array Coworkers ID list
 */
function getAuthCoworkersIdList()
{
    $coworkers_data = getSettings()->getCoworkersData();
    $list = array();
    if (NULL !== $coworkers_data) {
        foreach ($coworkers_data as $coworker_id => $data) {
            $token = $data->getAccessToken();
            if (!empty($token)) {
                $list[] = $coworker_id;
            }
        }
    }

    return $list;
}

/**
 * Get the authorized Coworkers list
 *
 * @return array
 */
function getAuthCoworkersList()
{
    $results = array();
    $coworkers_data = getSettings()->getCoworkersData();
    foreach ($coworkers_data as $id => $data) {
        /* @var $data \TeamBookingCoworker */
        if ($data->getAccessToken()) {
            $results[ $id ]['name'] = $data->getDisplayName();
            $results[ $id ]['email'] = $data->getEmail();
            $results[ $id ]['roles'] = $data->getRoles();
            $results[ $id ]['tokens'] = $data->getAccessToken();
            $results[ $id ]['calendars'] = $data->getCalendars();
            $results[ $id ]['auth_account'] = $data->getAuthAccount();
        }
    }

    return $results;
}

/**
 * Get the Coworkers list
 *
 * @return array
 */

function getAllCoworkersList()
{
    $results = array();
    // Get the ids of users with TB capability right now
    $ids = getCoworkersIdList();
    // Get the eventual list of coworkers already present
    $present_data = getSettings()->getCoworkersData();
    foreach ($ids as $id) {
        if (isset($present_data[ $id ])) {
            $coworker = $present_data[ $id ];
            unset($present_data[ $id ]);
        } else {
            $coworker = new \TeamBookingCoworker($id);
        }
        $results[ $id ]['name'] = $coworker->getDisplayName();
        $results[ $id ]['email'] = $coworker->getEmail();
        $results[ $id ]['calendars'] = $coworker->getCalendars();
        $results[ $id ]['roles'] = $coworker->getRoles();
        $results[ $id ]['services_allowed'] = $coworker->getAllowedServices();
        if (isset(json_decode($coworker->getAccessToken())->refresh_token)) {
            $results[ $id ]['token'] = 'refresh';
        } elseif (isset(json_decode($coworker->getAccessToken())->access_token)) {
            $results[ $id ]['token'] = 'access';
        } else {
            $results[ $id ]['token'] = '';
        }
    }
    // There are coworkers without TB capability left?
    if (!empty($present_data)) {
        foreach ($present_data as $id => $data) {
            if (get_userdata($id) == FALSE) {
                // User not exists anymore
                $settings = getSettings();
                $settings->dropCoworkerData($id);
                $settings->save();
                continue;
            }
            $results[ $id ]['name'] = $data->getDisplayName();
            $results[ $id ]['email'] = $data->getEmail();
            $results[ $id ]['calendar'] = $data->getCalendars();
            $results[ $id ]['roles'] = $data->getRoles();
            $results[ $id ]['services_allowed'] = $data->getAllowedServices();
            if (isset(json_decode($data->getAccessToken())->refresh_token)) {
                $results[ $id ]['token'] = 'refresh';
            } elseif (isset(json_decode($data->getAccessToken())->access_token)) {
                $results[ $id ]['token'] = 'access';
            } else {
                $results[ $id ]['token'] = '';
            }
            $results[ $id ]['allowed_no_more'] = TRUE;
        }
    }

    return $results;
}

/**
 * Renders the field validation modal in service settings tab
 *
 * @param FormElement $field
 * @param string      $fieldname
 *
 * @return string
 */
function getValidationModal(FormElement $field, $fieldname)
{
    $validation_settings = $field->getData('validation');
    ob_start();
    ?>
    <div class="ui small modal" id="tb-field-regex-modal-<?= $field->getHook() ?>">
        <i class="close tb-icon"></i>

        <div class="content" style="display:block;width: initial;">
            <div style="font-style: italic;font-weight: 300;">
                <?= __('Validation rule', 'team-booking') ?>
            </div>
            <select style="margin-bottom:10px;width:99%;" name="<?= $fieldname ?>[<?= $field->getHook() ?>_validation]">
                <option
                    value="none" <?php selected('none', $validation_settings['validate'], TRUE); ?>><?= __('No validation', 'team-booking') ?></option>
                <option
                    value="email" <?php selected('email', $validation_settings['validate'], TRUE); ?>><?= __('Email', 'team-booking') ?></option>
                <option
                    value="alphanumeric" <?php selected('alphanumeric', $validation_settings['validate'], TRUE); ?>><?= __('Alphanumeric', 'team-booking') ?></option>
                <option
                    value="phone" <?php selected('phone', $validation_settings['validate'], TRUE); ?>><?= __('Phone number (US only)', 'team-booking') ?></option>
                <option
                    value="custom" <?php selected('custom', $validation_settings['validate'], TRUE); ?>><?= __('Custom', 'team-booking') ?></option>
            </select>

            <div style="font-style: italic;font-weight: 300;">
                <?= __('Custom validation regex (works if "custom" is selected)', 'team-booking') ?>
            </div>
            <input type="text" class="large-text" style="margin-bottom:10px;"
                   name="<?= $fieldname ?>[<?= $field->getHook() ?>_regex]"
                   value="<?= $validation_settings['validation_regex']['custom'] ?>">

            <div class="ui button tb-close" style="height:auto">
                <?= __('Close', 'team-booking') ?>
            </div>
        </div>
    </div>
    <script>
        jQuery('#tb-field-regex-open-<?= $field->getHook() ?>').on('click', function (e) {
            e.preventDefault();
            jQuery('#tb-field-regex-modal-<?= $field->getHook() ?>')
                .uiModal({detachable: false})
                .uiModal('attach events', '.tb-close', 'hide')
                .uiModal('show')
            ;
        });
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Checks if there is a booking with that ID already
 *
 * @param string $id
 *
 * @return boolean TRUE if exists
 */
function checkServiceIdExistance($id)
{
    try {
        Database\Services::get($id);

        return TRUE;
    } catch (\Exception $e) {
        return FALSE;
    }
}

/**
 * Checks if there is a booking with that name already
 * or if the service's name contains invalid strings
 *
 * @param string $name
 *
 * @return boolean
 */
function checkServiceNameExistance($name)
{
    $services = Database\Services::get();
    $response = FALSE;
    foreach ($services as $service) {
        if (strtolower($service->getName()) == strtolower($name)
            || strpos('||', $name) !== FALSE
        ) {
            $response = TRUE;
            break;
        }
    }

    return $response;
}

/**
 *
 * Error log
 *
 * @param \TeamBooking_ErrorLog $log
 */
function errorLog(\TeamBooking_ErrorLog $log)
{
    $settings = getSettings();
    $error_logs = $settings->getErrorLogs();
    $now = current_time('timestamp');
    $log->setTimestamp($now);
    if (count($error_logs) >= 10) {
        array_shift($error_logs);
    }
    $error_logs[] = $log;
    $settings->setErrorLogs($error_logs);
    $settings->save();
}

/**
 * Returns appropriate text color
 *
 * @param string     $color (hexadecimal color value)
 * @param bool|FALSE $prefer_white
 *
 * @return string
 */
function getRightTextColor($color, $prefer_white = FALSE)
{
    $brightness_limit = $prefer_white ? 185 : 145;
    $rgb = Toolkit\hex2RGB($color);
    if (!$rgb) {
        return 'inherit';
    }
    $brightness = sqrt(
        $rgb['red'] * $rgb['red'] * .299 +
        $rgb['green'] * $rgb['green'] * .587 +
        $rgb['blue'] * $rgb['blue'] * .114);
    if ($brightness < $brightness_limit) {
        return '#FFFFFF';
    } else {
        return '#414141';
    }
}

/**
 * Returns appropriate hover color
 *
 * @param string $color (hexadecimal color value)
 *
 * @return string
 */
function getRightHoverColor($color)
{
    $rgb = Toolkit\hex2RGB($color);
    $brightness = sqrt(
        $rgb['red'] * $rgb['red'] * .299 +
        $rgb['green'] * $rgb['green'] * .587 +
        $rgb['blue'] * $rgb['blue'] * .114);
    if ($brightness < 145) {
        return 'rgba(255, 255, 255, 0.15)';
    } else {
        return 'rgba(0, 0, 0, 0.15)';
    }
}

/**
 * Returns appropriate background label color
 *
 * @param string $color (hexadecimal color value)
 *
 * @return string
 */
function getRightBackgroundColor($color)
{
    $rgb = Toolkit\hex2RGB($color);
    $brightness = sqrt(
        $rgb['red'] * $rgb['red'] * .299 +
        $rgb['green'] * $rgb['green'] * .587 +
        $rgb['blue'] * $rgb['blue'] * .114);
    if ($brightness < 145) {
        return '#2B2B2B';
    } else {
        return '#F4F4F4';
    }
}

function currencyCodeToSymbol($cc, $amount = 0, $keep_code = FALSE, $only_position = FALSE)
{
    $currencies = Toolkit\getCurrencies();
    $decimals = 2;
    if (!isset($currencies[ $cc ])) {
        $symbol = "$";
        $position = 'before';
    } else {
        if ($keep_code) {
            $symbol = $cc;
        } else {
            $symbol = $currencies[ $cc ]['symbol'];
        }
        $position = $currencies[ $cc ]['format'];
        $decimals = $currencies[ $cc ]['decimal'] ? 2 : 0;
    }

    if ($only_position) {
        return $position;
    }

    if (NULL === $amount) {
        return $symbol;
    } else {
        if ($position === 'after') {
            return priceFormat($amount, $decimals) . $symbol;
        } else {
            return $symbol . priceFormat($amount, $decimals);
        }
    }
}

function priceFormat($value, $decimals = NULL)
{
    if (NULL === $decimals) {
        $currencies = Toolkit\getCurrencies();
        $decimals = $currencies[ getSettings()->getCurrencyCode() ]['decimal'] === TRUE ? 2 : 0;
    }

    return number_format((float)$value, $decimals);
}

function cleanReservations($all = FALSE, $just_check = FALSE, $timeout_override = FALSE)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'teambooking_reservations';
    if ($timeout_override) {
        $timeout = $timeout_override;
    } else {
        $timeout = 30 * DAY_IN_SECONDS;
        #$timeout = tbGetSettings()->getDatabaseReservationTimeout();
    }
    if ($all) {
        $wpdb->query("TRUNCATE TABLE $table_name");
    } else {
        $now = Toolkit\getNowInSecondsUTC(); // UTC, seconds
        $reservations = Database\Reservations::getAll();
        $count = 0;
        foreach ($reservations as $id => $reservation) {
            /* @var $reservation \TeamBooking_ReservationData */
            if ($reservation instanceof \TeamBooking_ReservationData) {
                $age = $now - $reservation->getStart(); // UTC, seconds
                if ($age > $timeout && $timeout) {
                    if (!$just_check) {
                        Database\Reservations::delete($id);
                    } else {
                        $count++;
                    }
                }
            }
        }
        if ($count) {
            return $count;
        } else {
            return FALSE;
        }
    }
}

function tbCleanReservationsRoutine()
{
    cleanReservations();

    return TRUE;
}

function isReservationTimedOut(\TeamBooking_ReservationData $reservation)
{
    $timeout = getSettings()->getMaxPendingTime(); // seconds
    if ($timeout <= 0) return FALSE;
    $now = Toolkit\getNowInSecondsUTC(); // UTC, seconds
    $reservation_time = $reservation->getCreationInstant(); // UTC, seconds
    if (($now - $reservation_time) > $timeout) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function isReservationPastInTime(\TeamBooking_ReservationData $reservation)
{
    $now = Toolkit\getNowInSecondsUTC(); // UTC, seconds
    $reservation_time = $reservation->getStart(); // UTC, seconds
    if (!$reservation_time) return FALSE; // it is unscheduled
    if ($now > $reservation_time) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * Handler for uploaded files
 *
 * Returns associative array:
 * $movefile[file] The local path to the uploaded file.
 * $movefile[url] The public URL for the uploaded file.
 * $movefile[type] The MIME type.
 *
 * @param $file
 *
 * @return array|bool
 */
function handleFileUpload($file)
{
    if (!function_exists('wp_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $upload_overrides = array('test_form' => FALSE);
    $movefile = wp_handle_upload($file, $upload_overrides);
    if ($movefile) {
        return $movefile;
    } else {
        return FALSE;
    }
}

function date_i18n_tb($format, $value)
{
    $prev = date_default_timezone_get();
    date_default_timezone_set('UTC');
    $return = date_i18n($format, $value);
    date_default_timezone_set($prev);

    return $return;
}

function strtotime_tb($value)
{
    $prev = date_default_timezone_get();
    date_default_timezone_set('UTC');
    $return = strtotime($value);
    date_default_timezone_set($prev);

    return $return;
}

function isThereOneCouponAtLeast($service_id)
{
    $promotions = Database\Promotions::getByClassAndService('coupon', $service_id, TRUE, TRUE);
    if (empty($promotions)) {
        return FALSE;
    } else {
        foreach ($promotions as $db_id => $promotion) {
            if ($promotion->getLimit() > 0) {
                $used_discounts = count_used_discounts(Database\Reservations::getAll());
                if (isset($used_discounts[ $db_id ]) && $used_discounts[ $db_id ] >= $promotion->getLimit()) {
                    unset($promotions[ $db_id ]);
                }
            }
        }

        return !empty($promotions);
    }
}

/**
 * @param Service $service
 * @param         $code
 * @param         $start
 * @param         $end
 *
 *
 * @return array|boolean
 */
function getPriceWithCoupon($service, $code, $start = NULL, $end = NULL)
{
    $promotions = Database\Promotions::getByClassAndService('coupon', $service->getId(), TRUE, TRUE);
    foreach ($promotions as $id => $promotion) {
        /** @var $promotion \TeamBooking_Promotions_Coupon */
        if ($promotion->validateCode($code)) {
            if (count($promotion->getList()) > 0) {
                if (check_used_listed_coupon(Database\Reservations::getByServices($promotion->getServices()), $id, $code)) {
                    return FALSE;
                }
            } elseif ($promotion->getLimit() > 0) {
                $used_discounts = count_used_discounts(Database\Reservations::getByServices($promotion->getServices()));
                if (isset($used_discounts[ $id ]) && $used_discounts[ $id ] >= $promotion->getLimit()) {
                    return FALSE;
                }
            }
            $service_price = getDiscountedPrice($service, $start, $end);
            $discount_used = array(
                'name'   => $promotion->getName(),
                'value'  => $promotion->getDiscount(),
                'type'   => $promotion->getDiscountType(),
                'id'     => $id,
                'coupon' => $code
            );
            if ($promotion->getDiscountType() === 'percentage') {
                $value = $service_price - ($service->getPrice() - Toolkit\applyPercentage($service->getPrice(), $promotion->getDiscount()));
            } else {
                $value = $service_price - $promotion->getDiscount();
            }
            if ($value < 0) {
                $value = 0;
            }

            return array(
                'discounted' => $value,
                'promotion'  => $discount_used
            );
        }
    }

    return FALSE; // nothing to return? The coupon is invalid or expired
}

/**
 * Only for campaigns!
 *
 * @param \TeamBooking\Abstracts\Service $service
 * @param                                $slot_start
 * @param                                $slot_end
 *
 * @return int
 */
function getDiscountedPrice($service, $slot_start = NULL, $slot_end = NULL)
{
    $promotions = Database\Promotions::getByService($service->getId(), TRUE, TRUE);
    $base_price = $service->getPrice();
    $discounted_price = $base_price;
    foreach ($promotions as $db_id => $promotion) {
        if (!($promotion instanceof \TeamBooking_Promotions_Campaign)) {
            continue;
        }
        if ($promotion->getLimit() > 0) {
            $used_discounts = count_used_discounts(Database\Reservations::getAll());
            if (isset($used_discounts[ $db_id ]) && $used_discounts[ $db_id ] >= $promotion->getLimit()) {
                continue;
            }
        }
        if ($service->getClass() !== 'unscheduled') {
            if (NULL !== $promotion->getStartBound()) {
                if (NULL === $slot_start || NULL === $slot_end) continue;
                $time_obj = \DateTime::createFromFormat('U', $promotion->getStartBound());
                $time_obj->setTimezone(Toolkit\getTimezone());
                if ($slot_start < $time_obj->getTimestamp() - $time_obj->getOffset()) {
                    continue;
                }
            }
            if (NULL !== $promotion->getEndBound()) {
                if (NULL === $slot_start || NULL === $slot_end) continue;
                $time_obj = \DateTime::createFromFormat('U', $promotion->getEndBound());
                $time_obj->setTimezone(Toolkit\getTimezone());
                if ($slot_end > $time_obj->getTimestamp() - $time_obj->getOffset()) {
                    continue;
                }
            }
        }

        if ($promotion->getDiscountType() === 'percentage') {
            $discount = $discounted_price - Toolkit\applyPercentage($base_price, $promotion->getDiscount());
        } else {
            $discount = $promotion->getDiscount();
        }
        $discounted_price -= $discount;
    }
    if ($discounted_price < 0) {
        $discounted_price = 0;
    }

    return $discounted_price;
}

/**
 * Check if the (current|given) user is
 * either an Admin or a Coworker
 *
 * @param null $user_id
 *
 * @return bool
 */
function isAdminOrCoworker($user_id = NULL)
{
    if (NULL === $user_id) {
        if (current_user_can('manage_options')
            || current_user_can('tb_can_sync_calendar')
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return FALSE;
        }
        if ($user->has_cap('manage_options')
            || $user->has_cap('tb_can_sync_calendar')
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

/**
 * Sends the reminder e-mail messages to customers
 *
 * @return bool
 */
function tbSendEmailReminder()
{
    $reservations = Database\Reservations::getAll();
    $local_time = Toolkit\getNowInSecondsUTC();
    foreach ($reservations as $db_id => $data) {
        if (!$data->isConfirmed()) continue;
        if ($data->getServiceClass() === 'unscheduled') continue;
        try {
            $service = Database\Services::get($data->getServiceId());
        } catch (\Exception $e) {
            continue;
        }
        if ($service->getEmailReminder('send')) {
            $reference_time = $data->getStart(); // Unix
            $creation_time = $data->getCreationInstant(); // Unix
            $timeframe = $service->getEmailReminder('days_before') * DAY_IN_SECONDS; // Seconds
            if ($reference_time - $local_time < $timeframe
                && $reference_time - $local_time > 0
                && !$data->isEmailReminderSent()
            ) {
                if ($reference_time - $creation_time < DAY_IN_SECONDS
                    && $service->getEmailToCustomer('send')
                ) {
                    continue; // too early
                }
                $email = new \TeamBooking\EmailHandler();
                $subject = Toolkit\findAndReplaceHooks($service->getEmailReminder('subject'), $data->getHooksArray(TRUE));
                $body = Toolkit\findAndReplaceHooks($service->getEmailReminder('body'), $data->getHooksArray(TRUE));
                $email->setSubject($subject);
                $email->setBody($body);
                $email->setFrom($service->getEmailToAdmin('to'), get_bloginfo('name'));
                $email->setTo($data->getCustomerEmail());
                $email->send();
                $data->setEmailReminderSent(TRUE);
                Database\Reservations::update($data);
            }
        }
    }

    return TRUE;
}

/**
 * Sends the reminder e-mail message to customer (manual)
 *
 * @param $reservation_id
 *
 * @return bool
 */
function sendEmailReminderManually($reservation_id)
{
    $reservation = Database\Reservations::getById($reservation_id);
    if (!$reservation->isEmailReminderSent()
        && $reservation->isConfirmed()
    ) {
        try {
            $service = Database\Services::get($reservation->getServiceId());
        } catch (\Exception $e) {
            return FALSE;
        }
        if ($service->getEmailReminder('send')) {
            if (!isReservationPastInTime($reservation)) {
                $email = new \TeamBooking\EmailHandler();
                $subject = Toolkit\findAndReplaceHooks($service->getEmailReminder('subject'), $reservation->getHooksArray(TRUE));
                $body = Toolkit\findAndReplaceHooks($service->getEmailReminder('body'), $reservation->getHooksArray(TRUE));
                $email->setSubject($subject);
                $email->setBody($body);
                $email->setFrom($service->getEmailToAdmin('to'), get_bloginfo('name'));
                $email->setTo($reservation->getCustomerEmail());
                $email->send();
                $reservation->setEmailReminderSent(TRUE);
                Database\Reservations::update($reservation);

                return TRUE;
            }

            return FALSE;
        }

        return FALSE;
    }

    return FALSE;
}

/**
 * Retrieve a Coworker by his API token
 *
 * @param $ctoken
 *
 * @return bool|\TeamBookingCoworker
 */
function getCoworkerFromApiToken($ctoken)
{
    foreach (getSettings()->getCoworkersData() as $coworker) {
        if ($coworker->getApiToken() == $ctoken) return $coworker;
    }

    return FALSE;
}

function registerFrontendResources()
{
    if (getSettings()->getFix62dot5()) {
        wp_register_style('semantic-style', TEAMBOOKING_URL . 'libs/semantic/semantic-fix-min.css', array(), filemtime(TEAMBOOKING_PATH . 'libs/semantic/semantic-fix-min.css'));
    } else {
        wp_register_style('semantic-style', TEAMBOOKING_URL . 'libs/semantic/semantic-min.css', array(), filemtime(TEAMBOOKING_PATH . 'libs/semantic/semantic-min.css'));
    }
    wp_register_style('teambooking-style-modal', TEAMBOOKING_URL . 'libs/remodal/remodal.css', array(), filemtime(TEAMBOOKING_PATH . 'libs/remodal/remodal.css'));
    wp_register_style('teambooking-style-modal-theme', TEAMBOOKING_URL . 'libs/remodal/remodal-default-theme.css', array(), filemtime(TEAMBOOKING_PATH . 'libs/remodal/remodal-default-theme.css'));
    wp_register_style('teambooking-style-frontend', TEAMBOOKING_URL . 'css/frontend.css', array(), filemtime(TEAMBOOKING_PATH . 'css/frontend.css'));
    wp_register_style('teambooking_fonts', '//fonts.googleapis.com/css?family=Oswald|Open+Sans:300italic,400,300,700|Josefin+Sans:400,700', array(), '1.0.0');
    wp_enqueue_style('dashicons');
    wp_register_script('tb-base64-decoder', TEAMBOOKING_URL . 'libs/base64/base64decode.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'libs/base64/base64decode.js'), TRUE);
    wp_register_script('tb-modal-script', TEAMBOOKING_URL . 'libs/remodal/remodal.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'libs/remodal/remodal.js'), TRUE);
    wp_register_script('tb-frontend-script', TEAMBOOKING_URL . 'js/frontend.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'js/frontend.js'), TRUE);
    // in javascript, object properties are accessed as ajax_object.some_value
    wp_localize_script('tb-frontend-script', 'TB_Ajax', array('ajax_url' => admin_url('admin-ajax.php')));

    if (!getSettings()->getGmapsApiKey()) {
        wp_register_script('google-places-script', 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places', array('jquery'));
    } else {
        wp_register_script('google-places-script', 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=' . getSettings()->getGmapsApiKey(), array('jquery'));
    }

    wp_register_script('tb-geocomplete-script', TEAMBOOKING_URL . 'js/assets/jquery.geocomplete.min.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'js/assets/jquery.geocomplete.min.js'), TRUE);
    wp_register_script('tb-gmap3-script', TEAMBOOKING_URL . 'libs/gmap3/gmap3.min.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'libs/gmap3/gmap3.min.js'), TRUE);

    if (getSettings()->getPaymentGatewaySettingObject('stripe') instanceof \TeamBooking_PaymentGateways_Stripe_Settings) {
        if (getSettings()->getPaymentGatewaySettingObject('stripe')->isActive()) {
            wp_register_script('tb-jquery-payment', TEAMBOOKING_URL . 'libs/jquery-payment/jquery.payment.min.js', array('jquery'), filemtime(TEAMBOOKING_PATH . 'libs/jquery-payment/jquery.payment.min.js'), TRUE);
            if (getSettings()->getPaymentGatewaySettingObject('stripe')->isLoadLibrary()) {
                wp_register_script('stripejs', 'https://js.stripe.com/v2/');
            }
        }
    }
}

function enqueueFrontendResources()
{
    if (!defined('TBK_COMMON_RSC_ENQUEUED')) {
        if (!getSettings()->getSkipGmapLibs()) {
            wp_enqueue_script('google-places-script');
        }

        if (getSettings()->getPaymentGatewaySettingObject('stripe')->isActive()) {
            wp_enqueue_script('tb-jquery-payment');
            if (getSettings()->getPaymentGatewaySettingObject('stripe')->isLoadLibrary()) {
                wp_enqueue_script('stripejs');
            }
        }

        wp_enqueue_script('tb-base64-decoder');
        wp_enqueue_script('tb-geocomplete-script');
        wp_enqueue_script('tb-gmap3-script');
        wp_enqueue_script('tb-frontend-script');
        wp_enqueue_style('teambooking_fonts');
        wp_enqueue_style('semantic-style');
        wp_enqueue_style('teambooking-style-frontend');
        define('TBK_COMMON_RSC_ENQUEUED', TRUE);
    }

    if (!defined('TBK_RESERVATIONS_RSC_ENQUEUED')
        && defined('TBK_RESERV_SHORTCODE_FOUND')
    ) {
        wp_enqueue_script('tb-modal-script');
        wp_enqueue_style('teambooking-style-modal');
        wp_enqueue_style('teambooking-style-modal-theme');
        define('TBK_RESERVATIONS_RSC_ENQUEUED', TRUE);
    }

    if (!defined('TBK_CALENDAR_RSC_ENQUEUED')
        && (defined('TBK_CALENDAR_SHORTCODE_FOUND') || defined('TBK_WIDGET_SHORTCODE_FOUND'))
    ) {
        wp_enqueue_script('semantic-script');
        define('TBK_CALENDAR_RSC_ENQUEUED', TRUE);
    }
}

/**
 * @param null $locale
 *
 * @return array
 */
function continents_list($locale = NULL)
{
    static $mo_loaded = FALSE, $locale_loaded = NULL;
    $continents = array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');
    if (!$mo_loaded || $locale !== $locale_loaded) {
        $locale_loaded = $locale ?: get_locale();
        $mofile = WP_LANG_DIR . '/continents-cities-' . $locale_loaded . '.mo';
        unload_textdomain('continents-cities');
        load_textdomain('continents-cities', $mofile);
        $mo_loaded = TRUE;
    }
    $return = array();
    foreach ($continents as $continent) {
        $return[ $continent ] = translate($continent, 'continents-cities');
    }

    return $return;
}

/**
 * @param string $selected_zone
 * @param bool   $widget
 * @param null   $locale
 *
 * @return mixed
 */
function timezone_list($selected_zone, $widget = FALSE, $locale = NULL)
{
    static $mo_loaded = FALSE, $locale_loaded = NULL;
    $continents = array();
    foreach (getSettings()->getContinentsAllowed() as $continent => $allowed) {
        if ($allowed) $continents[] = $continent;
    }

    if (!$mo_loaded || $locale !== $locale_loaded) {
        $locale_loaded = $locale ?: get_locale();
        $mofile = WP_LANG_DIR . '/continents-cities-' . $locale_loaded . '.mo';
        unload_textdomain('continents-cities');
        load_textdomain('continents-cities', $mofile);
        $mo_loaded = TRUE;
    }

    $zonen = array();
    foreach (timezone_identifiers_list() as $zone) {
        $zone = explode('/', $zone);
        if (!in_array($zone[0], $continents)) {
            continue;
        }
        $exists = array(
            0 => isset($zone[0]) && $zone[0],
            1 => isset($zone[1]) && $zone[1],
            2 => isset($zone[2]) && $zone[2],
        );
        $exists[3] = ($exists[0] && 'Etc' !== $zone[0]);
        $exists[4] = ($exists[1] && $exists[3]);
        $exists[5] = ($exists[2] && $exists[3]);

        $zonen[] = array(
            'continent'   => $exists[0] ? $zone[0] : '',
            'city'        => $exists[1] ? $zone[1] : '',
            'subcity'     => $exists[2] ? $zone[2] : '',
            't_continent' => $exists[3] ? translate(str_replace('_', ' ', $zone[0]), 'continents-cities') : '',
            't_city'      => $exists[4] ? translate(str_replace('_', ' ', $zone[1]), 'continents-cities') : '',
            't_subcity'   => $exists[5] ? translate(str_replace('_', ' ', $zone[2]), 'continents-cities') : ''
        );
    }

    $structure = array();
    $structure[1] = '<div class="' . ($widget ? 'mini' : 'tiny') . ' tbk-menu">';
    $i = 2;
    foreach ($zonen as $key => $zone) {
        $value = array($zone['continent']);

        if (empty($zone['city'])) {
            $display = $zone['t_continent'];
        } else {
            $value[] = $zone['city'];
            $display = '<span class="tbk-timezone-list-item-continent">' . $zone['t_continent'] . '</span>' . ' ' . $zone['t_city'];
            if (!empty($zone['subcity'])) {
                $value[] = $zone['subcity'];
                $display .= ' - ' . $zone['t_subcity'];
            }
        }
        $value = implode('/', $value);
        $selected = '';
        if ($value === $selected_zone) {
            $selected = ' tbk-selected';
            $structure[0] = '<span class="tbk-text">' . $display . '</span>';
        }
        $structure[ $i ] = '<div class="tbk-menu-item' . $selected . '" data-timezone="' . esc_attr($value) . '">' . $display . '</div>';
        $i++;
    }
    $selected = '';
    if ('UTC' === $selected_zone) {
        $selected = ' tbk-selected';
        $structure[0] = '<span class="tbk-text">UTC</span>';
    }
    $structure[] = '<div class="tbk-menu-item' . $selected . '" data-timezone="UTC">UTC</div>';
    $structure[] = '</div>';
    ksort($structure);

    return implode("\n", $structure);
}

/**
 * @param \TeamBooking\RenderParameters $parameters
 */
function parse_query_params(\TeamBooking\RenderParameters $parameters)
{
    if (isset($_GET['tbk_timezone'])) {
        try {
            $timezone = new \DateTimeZone(urldecode($_GET['tbk_timezone']));
            $parameters->setTimezone($timezone);
        } catch (\Exception $ex) {
        }
    }
    if (isset($_GET['tbk_month'])) {
        $month = FALSE;
        if ($month === FALSE) $month = Toolkit\validateDateFormat(tb_mb_ucwords(tb_mb_strtolower($_GET['tbk_month'])), 'F', 'm');
        if ($month !== FALSE) $parameters->setMonth($month);
        if ($month === FALSE) $month = Toolkit\validateDateFormat(tb_mb_ucwords(tb_mb_strtolower($_GET['tbk_month'])), 'M', 'm');
        if ($month !== FALSE) $parameters->setMonth($month);
        if ($month === FALSE) $month = Toolkit\validateDateFormat($_GET['tbk_month'], 'm', 'm');
        if ($month !== FALSE) $parameters->setMonth($month);
        if ($month === FALSE) $month = Toolkit\validateDateFormat($_GET['tbk_month'], 'n', 'm');
        if ($month !== FALSE) $parameters->setMonth($month);
    }
    if (isset($_GET['tbk_year'])) {
        $year = FALSE;
        if ($year === FALSE) $year = Toolkit\validateDateFormat($_GET['tbk_year'], 'Y', 'Y');
        if ($year !== FALSE) $parameters->setYear($year);
        if ($year === FALSE) $year = Toolkit\validateDateFormat($_GET['tbk_year'], 'y', 'Y');
        if ($year !== FALSE) $parameters->setYear($year);
    }
}

/**
 * @param $string
 *
 * @return mixed
 */
function tb_mb_strtoupper($string)
{
    if (function_exists('mb_strtoupper')) {
        return mb_strtoupper($string, 'UTF-8');
    } else {
        return strtoupper($string);
    }
}

/**
 * @param $string
 *
 * @return mixed
 */
function tb_mb_strtolower($string)
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($string, 'UTF-8');
    } else {
        return strtolower($string);
    }
}

/**
 * @param $string
 *
 * @return mixed
 */
function tb_mb_ucwords($string)
{
    if (function_exists('mb_strtolower')) {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    } else {
        return ucwords($string);
    }
}

/**
 * @param \TeamBooking_ReservationData[] $reservations
 *
 * @return array
 */
function count_used_discounts($reservations)
{
    $discount_counts = array();
    foreach ($reservations as $reservation) {
        $discount_array = $reservation->getDiscount();
        foreach ($discount_array as $discount) {
            if (!isset($discount['id'])) {
                $promotion = Database\Promotions::getByName($discount['name']);
                reset($promotion);
                $discount['id'] = key($promotion);
            }
            if (isset($discount_counts[ $discount['id'] ])) {
                $discount_counts[ $discount['id'] ]++;
            } else {
                $discount_counts[ $discount['id'] ] = 1;
            }
        }
    }

    return $discount_counts;
}

/**
 * @param \TeamBooking_ReservationData[] $reservations
 * @param string                         $promotion_id
 * @param string                         $code
 *
 * @return bool
 */
function check_used_listed_coupon($reservations, $promotion_id, $code)
{
    foreach ($reservations as $reservation) {
        $discount_array = $reservation->getDiscount();
        foreach ($discount_array as $discount) {
            if (!isset($discount['id'])) {
                $promotion = Database\Promotions::getByName($discount['name']);
                reset($promotion);
                $discount['id'] = key($promotion);
            }
            if (isset($discount['coupon']) && $discount['id'] === $promotion_id && tb_mb_strtolower($code) === tb_mb_strtolower($discount['coupon'])) {
                return TRUE;
            }
        }
    }

    return FALSE;
}

/////////////////////
//  CUSTOM HOOKS   //
/////////////////////

/**
 * @param $params \TeamBooking\RenderParameters
 */
function calendar_click_on_day($params)
{
    do_action('tbk_calendar_click_on_day', $params);
}

/**
 * @param $params \TeamBooking\RenderParameters
 */
function calendar_change_month($params)
{
    do_action('tbk_calendar_change_month', $params);
}

/**
 * @param $slot \TeamBooking\Slot
 */
function schedule_slot_parse($slot)
{
    do_action('tbk_schedule_slot_parse', $slot);
}

/**
 * @param $slot \TeamBooking\Slot
 */
function schedule_slot_render($slot)
{
    do_action('tbk_schedule_slot_render', $slot);
}

/**
 * @param $data \TeamBooking_ReservationData
 */
function reservation_before_processing($data)
{
    do_action('tbk_reservation_before_processing', $data);
}

/**
 * @param string                       $who
 * @param EmailHandler                 $email
 * @param \TeamBooking_ReservationData $data
 */
function reservation_email_to($who = 'admin', $email, $data)
{
    if ($who === 'admin') {
        do_action('tbk_reservation_email_to_admin', $email, $data);
    } elseif ($who === 'coworker') {
        do_action('tbk_reservation_email_to_coworker', $email, $data);
    } elseif ($who === 'customer') {
        do_action('tbk_reservation_email_to_customer', $email, $data);
    }
}

/**
 * @param \TeamBooking\Frontend\Form $form
 */
function reservation_form_header($form)
{
    do_action('tbk_reservation_form_header', $form);
}

/**
 * @param \TeamBooking\Frontend\Form $form
 */
function reservation_form_description($form)
{
    do_action('tbk_reservation_form_description', $form);
}

/**
 * @param \TeamBooking\Frontend\Form $form
 */
function reservation_form_map($form)
{
    do_action('tbk_reservation_form_map', $form);
}

/**
 * @param string $text
 */
function reservation_review_header($text)
{
    do_action('tbk_reservation_review_header', $text);
}

/**
 * @param \TeamBooking_ReservationData $data
 */
function reservation_review_details($data)
{
    do_action('tbk_reservation_review_details', $data);
}

/**
 * @param Service $service
 */
function reservation_review_footer($service)
{
    do_action('tbk_reservation_review_footer', $service);
}

/**
 * @param $content
 */
function api_page_main_content($content)
{
    echo apply_filters('tbk_api_page_main_content', $content);
}

/**
 * @param $hook
 * @param $all_values
 *
 * @return mixed
 */
function email_hook_replace($hook, $all_values)
{
    return apply_filters('tbk_email_hook_replace', isset($all_values[ $hook ]) ? $all_values[ $hook ] : $hook, $hook, $all_values);
}

/**
 * @param FormElement[] $fields
 * @param Service       $service
 * @param Slot          $slot
 *
 * @return FormElement[]
 */
function manipulate_frontend_form_fields($fields, $service, $slot)
{
    return apply_filters('tbk_frontend_form_manipulate_fields', $fields, $service, $slot);
}

/**
 * @param array   $hooks
 * @param Service $service
 * @param array   $form_data
 *
 * @return array
 */
function manipulate_expected_form_field_hooks($hooks, $service, $form_data)
{
    return apply_filters('tbk_frontend_form_manipulate_expected_hooks', $hooks, $service, $form_data);
}

/**
 * @param string                       $content
 * @param \TeamBooking_ReservationData $data
 *
 * @return string
 */
function modify_thankyou_content($content, $data)
{
    return apply_filters('tbk_frontend_thankyou_content', $content, $data);
}

/**
 * @param FormElement $field
 * @param string      $fieldname_root
 */
function backend_form_field_add_content($field, $fieldname_root)
{
    do_action('tbk_backend_form_field_add_content', $field, $fieldname_root);
}

/**
 * @param array   $parsed_data
 * @param Service $service
 * @param string  $old_hook
 */
function backend_form_field_save($parsed_data, $service, $old_hook)
{
    do_action('tbk_backend_form_field_save', $parsed_data, $service, $old_hook);
}

/**
 * @param Service $service
 * @param Slot    $slot
 */
function frontend_form_add_ticket_row($service, $slot)
{
    do_action('tbk_frontend_form_add_ticket_row', $service, $slot);
}