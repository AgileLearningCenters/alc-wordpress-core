<?php

defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Toolkit;

////////////////////////////////
//                            //
//  FRONTEND AJAX CALLBACKS   //
//                            //
////////////////////////////////

/**
 * Ajax callback: change month on frontend calendar
 */
function tbajax_action_change_month_callback()
{
    $calendar = new TeamBooking\Calendar();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    $parameters->setMonth(date('m', strtotime($_POST['month'] . '/01')));
    $parameters->setYear($_POST['year']);
    $parameters->setIsAjaxCall(TRUE);
    ob_start();

    // WordPress custom hook
    Functions\calendar_change_month($parameters);

    if (!isset($parameters->stop) || $parameters->stop === FALSE) {
        $calendar->getCalendar($parameters);
    }
    echo Toolkit\wrapAjaxResponse(ob_get_clean());
    exit;
}

/**
 * Ajax callback: show day schedule on frontend calendar
 */
function tbajax_action_show_day_schedule_callback()
{
    $calendar = new TeamBooking\Calendar();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    ob_start();
    if ($parameters instanceof TeamBooking\RenderParameters) {
        $parameters->setDay($_POST['day']);
        $parameters->setSlots($parameters->decode($_POST['slots']));
        $parameters->setIsAjaxCall(TRUE);

        // WordPress custom hook
        Functions\calendar_click_on_day($parameters);

        if (!isset($parameters->override_schedule) || $parameters->override_schedule === FALSE) {
            $calendar->getSchedule($parameters);
        }
    } else {
        $error = new TeamBooking_Error(9);
        echo $error->getMessage();
    }
    echo Toolkit\wrapAjaxResponse(ob_get_clean());
    exit;
}

/**
 * Ajax callback: get reservation modal content
 */
function tbajax_action_get_reservation_modal_callback()
{
    /** @var $slot TeamBooking\Slot */
    $slot = unserialize(gzinflate(base64_decode($_POST['slot'])));
    // Calling the template
    $form = TeamBooking\Frontend\Form::fromSlot($slot);
    // Render
    echo $form->getContent();
    exit;
}

/**
 * Ajax callback: get register/login modal content
 */
function tbajax_action_get_register_modal_callback()
{
    $event_id = $_POST['event'];
    $coworker_id = $_POST['coworker'];
    $service_id = $_POST['service'];
    $post_id = $_POST['post_id'];
    echo TeamBooking\Frontend\Form::getContentRegisterAdvice(FALSE, $event_id, $service_id, $coworker_id, $post_id);
    exit;
}

function tbajax_action_upcoming_more_callback()
{
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    $limit = filter_var($_POST['limit'], FILTER_SANITIZE_NUMBER_INT);
    $increment = filter_var($_POST['increment'], FILTER_SANITIZE_NUMBER_INT);
    if ($_POST['increment'] !== 'false') {
        if ($limit > 0 && $parameters->getSlotsShown() + $increment > $limit) {
            $parameters->setSlotsShown($limit);
        } else {
            $parameters->setSlotsShown($parameters->getSlotsShown() + $increment);
        }
    }
    $parameters->setIsAjaxCall(TRUE);
    echo Toolkit\wrapAjaxResponse(\TeamBooking\Shortcodes\Upcoming::getView($parameters));
    exit;
}

function tbajax_action_filter_upcoming_callback()
{
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    if ($_POST['timezone'] !== 'false') {
        $parameters->setTimezone(Toolkit\getTimezone(filter_var($_POST['timezone'], FILTER_SANITIZE_STRING)));
    }
    $parameters->setIsAjaxCall(TRUE);
    echo json_encode(array('upcoming' => \TeamBooking\Shortcodes\Upcoming::getView($parameters)));
    exit;
}

function tbajax_action_filter_calendar_callback()
{
    $calendar = new TeamBooking\Calendar();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    $unscheduled = FALSE;
    if ($_POST['services'] !== 'false') {
        $parameters->setRequestedServiceIds(unserialize(base64_decode($_POST['services'])));
    } else {
        if ($_POST['service'] !== 'false') {
            $parameters->setRequestedServiceIds(array($_POST['service']));
            if (Database\Services::get($_POST['service'])->getClass() === 'unscheduled') $unscheduled = TRUE;
        } else {
            $parameters->setRequestedServiceIds($parameters->getServiceIds());
        }
    }
    if ($_POST['coworkers'] !== 'false') {
        $parameters->setRequestedCoworkerIds(unserialize(base64_decode($_POST['coworkers'])));
    } else {
        if ($_POST['coworker'] !== 'false') {
            $parameters->setRequestedCoworkerIds(array($_POST['coworker']));
        } else {
            $parameters->setRequestedCoworkerIds($parameters->getCoworkerIds());
        }
    }
    if ($_POST['timezone'] !== 'false') {
        $parameters->setTimezone(Toolkit\getTimezone(filter_var($_POST['timezone'], FILTER_SANITIZE_STRING)));
    }
    $parameters->setIsAjaxCall(TRUE);
    // generate the responses
    ob_start();
    $calendar->getCalendar($parameters, TRUE);
    $response_one = ob_get_clean();
    // responses output
    echo json_encode(array('calendar' => $response_one, 'unscheduled' => $unscheduled));
    exit;
}

function tbajax_action_fast_month_selector_callback()
{
    $calendar = new TeamBooking\Calendar();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    $parameters->setMonth($_POST['month']);
    $parameters->setIsAjaxCall(TRUE);
    // generate the response
    ob_start();
    $calendar->getCalendar($parameters);
    $response = ob_get_clean();
    // responses output
    echo $response;
    exit;
}

function tbajax_action_fast_year_selector_callback()
{
    $calendar = new TeamBooking\Calendar();
    $parameters = new TeamBooking\RenderParameters();
    $parameters = $parameters->decode($_POST['params']);
    $parameters->setYear($_POST['year']);
    $parameters->setIsAjaxCall(TRUE);
    // generate the response
    ob_start();
    $calendar->getCalendar($parameters);
    $response = ob_get_clean();
    // responses output
    echo $response;
    exit;
}

function tbajax_action_cancel_reservation_callback()
{
    $hash = $_POST['reservation_hash'];
    $id = $_POST['reservation_id'];
    /**
     * Retrieving the reservation record in database
     */
    $reservation_db_record = Database\Reservations::getById($id);
    /**
     * Hash check
     */
    if ($reservation_db_record->getToken() !== $hash) {
        exit;
    }
    /**
     * Instantiating the reservation service class
     */
    $reservation = new TeamBooking_Reservation($reservation_db_record);
    /**
     * Calling the cancel method
     */
    $updated_record = $reservation->cancelReservation($id);
    if ($updated_record instanceof TeamBooking_ReservationData) {
        /**
         * Everything went fine, let's update the database record
         */
        Database\Reservations::update($updated_record);
        echo Toolkit\wrapAjaxResponse('ok');
    } elseif ($updated_record instanceof TeamBooking_Error) {
        /**
         * Something goes wrong
         */
        if ($updated_record->getCode() == 7) {
            /*
             * The reservation is already cancelled, let's update the database record
             */
            $reservation_db_record->setStatusCancelled();
            Database\Reservations::update($reservation_db_record);
        }
        echo Toolkit\wrapAjaxResponse($updated_record->getMessage());
    }
    exit;
}

function tbajax_action_validate_coupon_callback()
{
    $response = array();
    $reservation = Toolkit\objDecode(filter_input(INPUT_POST, 'reservation'));
    /** @var $reservation TeamBooking_ReservationData */
    try {
        $service = Database\Services::get($reservation->getServiceId());
        if ($service->getClass() === 'unscheduled') {
            $discount = Functions\getPriceWithCoupon($service, Toolkit\filterInput($_POST['code']));
        } else {
            $discount = Functions\getPriceWithCoupon($service, Toolkit\filterInput($_POST['code']), $reservation->getStart(), $reservation->getEnd());
        }
        if (!$discount) {
            $response['value'] = 'ko';
        } else {
            // Adding increments
            $reservation->setPriceDiscounted($discount['discounted']);
            $reservation->addDiscount($discount['promotion']);
            if (count($reservation->getDiscount()) >= 1) {
                $discounted_unit_price = $discount['discounted'] + ($reservation->getPriceIncremented() - $reservation->getPriceDiscounted());
            } else {
                $discounted_unit_price = $discount['discounted'] + ($reservation->getPriceIncremented() - $reservation->getPrice());
            }
            $response['reservation'] = Toolkit\objEncode($reservation);
            $response['value'] = Functions\currencyCodeToSymbol(Functions\getSettings()->getCurrencyCode(), $reservation->getTickets() * $discounted_unit_price);
        }
    } catch (Exception $e) {
        $response['value'] = 'ko';
    }
    echo Toolkit\wrapAjaxResponse(json_encode($response));
    exit;
}

///////////////////////////////
//                           //
//  BACKEND AJAX CALLBACKS   //
//                           //
///////////////////////////////

/**
 * oAuth2 callback, for authentication
 */
function teambooking_oauth_callback()
{
    // Check if there is auth code and user capability
    if (isset($_GET['code']) && current_user_can('tb_can_sync_calendar')) {
        // casting the REQUEST array to an object and saving it
        $request = (object)$_REQUEST;
        // initialize session
        if (!session_id()) {
            session_start();
        }
        // nonce check (defined in TeamBooking_Calendar class)
        if (isset($_SESSION['tbk-auth-state'])) {
            if ($_SESSION['tbk-auth-state'] != $request->state) {
                $location = admin_url('admin.php?page=team-booking&tab=personal&nag_auth_failed=1');
                die(wp_redirect($location));
            }
        }
        // get settings
        $settings = Functions\getSettings();
        // get the coworker data
        $coworker = $settings->getCoworkerData(get_current_user_id());
        // instantiate a calendar class
        $calendar = new TeamBooking\Calendar();
        // retrieve the coworker's access token, if present
        $access_token = $coworker->getAccessToken();
        if (!empty($access_token)) {
            // an access token is already present... revoke it?
        } else {
            /**
             * Access token not present, let's exchange the auth code
             * for access token, refresh token, id token set.
             *
             * Before saving them, we'll check if there is a refresh token.
             * If the refresh token is not present, the Google Account
             * thinks that this application is already trusted, and a previous
             * refresh token was already granted without being revoked yet.
             *
             * We'll check also if the Google Account email is actually used
             * by another coworker.
             *
             */
            $tokens = $calendar->authenticate($request->code);
            // is there a refresh token?
            if (NULL === $calendar->setAccessToken($tokens)) {
                // There is no refresh token
                $location = admin_url('admin.php?page=team-booking&tab=personal&nag_auth_no_refresh=1');
                die(wp_redirect($location));
            } else {
                /**
                 * There is a refresh token, is the Google Account already used?
                 *
                 * NOTE:
                 * this configuration (refresh token provided && Google Account already authorized)
                 * should NOT be possible, according to oAuth flow.
                 *
                 * TODO: test if this whole block is useless
                 */
                $already_used = FALSE;
                $this_email = $calendar->getTokenEmailAccount($tokens, get_current_user_id());
                if ($this_email instanceof Google_Auth_Exception) {
                    $this_email = $this_email->getMessage();
                }
                $coworker_id_list = Functions\getAuthCoworkersList();
                foreach ($coworker_id_list as $coworker_id => $coworker_data) {
                    $that_email = $calendar->getTokenEmailAccount($coworker_data['tokens'], $coworker_id);
                    if ($that_email instanceof Google_Auth_Exception) {
                        $that_email = $that_email->getMessage();
                    }
                    if ($this_email == $that_email) {
                        $already_used = TRUE;
                    }
                }
                if ($already_used) {
                    $location = admin_url('admin.php?page=team-booking&tab=personal&nag_auth_already_used=1');
                    die(wp_redirect($location));
                }

                /**
                 * All is correct, let's save the tokens
                 */
                $coworker->setAccessToken($tokens);
                $coworker->setAuthAccount($this_email);
                $settings->updateCoworkerData($coworker);
                $settings->save();
                // set redirect
                $location = admin_url('admin.php?page=team-booking&tab=personal&nag_auth_success=1');
                die(wp_redirect($location));
            }
        }
        exit;
    } elseif (isset($_GET['error'])) {
        // Handle the access_denied error
        var_dump($_GET['error']);
        exit;
    }
    exit;
}

/**
 * Instantiates the listeners for IPN payments of any gateway
 */
function teambooking_ipn_listener()
{
    foreach (Functions\getSettings()->getPaymentGatewaySettingObjects() as $gateway_id => $gateway) {
        /* @var $gateway TeamBooking_PaymentGateways_Settings */
        if (isset($_REQUEST[ $gateway_id ])) {
            // use raw POST data
            $raw_post_data = file_get_contents('php://input');
            $raw_post_array = explode('&', $raw_post_data);
            $post_data = array();
            foreach ($raw_post_array as $keyval) {
                $keyval = explode('=', $keyval);
                if (count($keyval) === 2) {
                    $post_data[ $keyval[0] ] = urldecode($keyval[1]);
                }
            }
            $gateway->listenerIPN($post_data);
            exit;
        }
    }
    exit;
}

function teambooking_rest_api()
{
    $method = $_SERVER['REQUEST_METHOD'];
    $response = \TeamBooking\API\REST::call($method, $_REQUEST);
    if ($response['code'] == 302) {
        header('Location: ' . $response['response']);
    } else {
        if (isset($response['output'])) {

        } else {
            header('Content-Type: application/json', TRUE, $response['code']);
            echo json_encode($response);
        }
    }
    exit;
}