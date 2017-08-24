<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Database;

/**
 * Class Calendar
 *
 * @author VonStroheim
 */
class Calendar
{
    /** @var  \Google_Client */
    private static $client;

    /** @var  \Google_Service_Calendar */
    private static $service;

    public function __construct()
    {
        // Create a new Google Client object
        static::$client = new \Google_Client();
        static::$client->addScope('https://www.googleapis.com/auth/calendar');
        static::$client->setApplicationName(Functions\getSettings()->getApplicationProjectName());
        static::$client->setClientId(Functions\getSettings()->getApplicationClientId());
        static::$client->setClientSecret(Functions\getSettings()->getApplicationClientSecret());
        static::$client->setRedirectUri(admin_url() . 'admin-ajax.php?action=teambooking_oauth_callback');
        static::$client->setAccessType('offline');
        // Has to be set before the auth call
        static::$service = new \Google_Service_Calendar(static::$client);
    }

    /**
     * Authenticate method
     *
     * @param string $code The code
     *
     * @return string Access Token
     */
    public function authenticate($code)
    {
        try {
            static::$client->authenticate($code);
        } catch (\Exception $ex) {
            // TODO: better error message handling?
            die($ex->getMessage());
        }

        return static::$client->getAccessToken();
    }

    /**
     * Get the email address of the Coworker's Google Account
     *
     * To make clear what Google Account the Coworker has configured
     * with TeamBooking, it gets the email address for convenience.
     * In order to do that, an id_token (OpenID) is used.
     * If the stored one is expired, then the Coworker tokens
     * will be refreshed and the new values stored.
     *
     * @param string $access_token
     * @param string $coworker_id
     *
     * @return string|\Exception
     */
    public function getTokenEmailAccount($access_token, $coworker_id)
    {
        // prepare the client service with the access token
        $this->setAccessToken($access_token);
        if (isset(json_decode($access_token)->id_token)) {
            try {
                // get token info
                $token_info = static::$client->verifyIdToken();
            } catch (\Exception $e) {
                // the id_token is expired, perhaps? Let's try to refresh it
                try {
                    static::$client->refreshToken(static::$client->getRefreshToken());
                } catch (\Exception $ex) {
                    // can't be refreshed, let's check if the tokes has been revoked
                    $original_error = Toolkit\lookingForJSON($ex->getMessage());
                    if (NULL !== $original_error && NULL !== json_decode($original_error[0])) {
                        $array_message = json_decode($original_error[0]);
                        if ($array_message->error_description === 'Token has been revoked.') {
                            // The token we have, has been revoked, let's advice
                            return '<span class="tbk-revoked-upstream-advice">' . esc_html__('Uh-oh, your authorization was revoked upstream, please press the revoke button below, and authorize again!', 'team-booking') . '</span>';
                        }
                    }

                    // unknown error...
                    return $ex;
                }
                $coworker_data = Functions\getSettings()->getCoworkerData($coworker_id);
                $coworker_data->setAccessToken(static::$client->getAccessToken());
                // save the new value
                Functions\getSettings()->updateCoworkerData($coworker_data);
                Functions\getSettings()->save();
                // get token info (try again)
                try {
                    $token_info = static::$client->verifyIdToken();
                } catch (\Exception $ex) {
                    // no hope...
                    return $ex;
                }
            }
            // get account email address
            $attributes = $token_info->getAttributes();

            return $attributes['payload']['email'];
        } else {
            return '';
        }
    }

    /**
     * Returns a list of Google Calendars for a specific
     * access token (owned calendars only)
     *
     * @param string $access_token
     *
     * @return array
     */
    public function getGoogleCalendarList($access_token)
    {
        // prepare the client service with the access token
        $this->setAccessToken($access_token);

        try {
            $calendar_list = static::$service->calendarList->listCalendarList()->getItems();
        } catch (\Exception $exc) {
            if ($exc instanceof \Google_Service_Exception) {
                $errors = $exc->getErrors();

                #return $errors[0]['message'];
            }

            // something went wrong, let's return an empty array
            return array();
        }
        // preparing the list
        $return_array = array();
        foreach ($calendar_list as $calendar_entry) {
            /* @var $calendar_entry \Google_Service_Calendar_CalendarListEntry */
            if ($calendar_entry->getAccessRole() === 'owner') {
                // we're filtering the owned calendars only
                $return_array[] = $calendar_entry;
            }
        }

        return $return_array;
    }

    /**
     * Set the access token
     *
     * @param string $access_token
     *
     * @return string Returns the Refresh Token, if available, or NULL
     */
    public function setAccessToken($access_token)
    {
        static::$client->setAccessToken($access_token);

        return static::$client->getRefreshToken();
    }

    /**
     * Revokes a token
     *
     * @param string $token
     *
     * @return boolean
     */
    public function revokeToken($token)
    {
        $client = new \Google_Client();
        try {
            $attempt = $client->revokeToken($token);
        } catch (\Exception $e) {
            $error_log = new \TeamBooking_ErrorLog();
            $error_log->setCoworkerId(get_current_user_id());
            $error_log->setErrorCode($e->getCode());
            $error_log->setMessage($e->getMessage());
            Functions\errorLog($error_log);

            return $e->getMessage();
        }

        return $attempt;
    }

    /**
     * Get the authorization link
     *
     * @return string Authorization Url
     */
    public function createAuthUrl()
    {
        // unique state id to avoid exploits
        $state = md5(mt_rand());
        if (!session_id()) {
            session_start();
        }
        $_SESSION['tbk-auth-state'] = $state;
        static::$client->addScope('openid');
        static::$client->addScope('email');
        static::$client->setState($state);

        return static::$client->createAuthUrl();
    }

    /**
     * It tests the ownership of a Google Calendar ID
     *
     * @param int    $coworker_id
     * @param string $calendar_id
     *
     * @return boolean
     */
    public function testCalendarID($coworker_id, $calendar_id)
    {
        $coworker_data = Functions\getSettings()->getCoworkerData($coworker_id);
        $access_token = $coworker_data->getAccessToken();
        if (!empty($access_token)) {
            $this->setAccessToken($access_token);
            try {
                $request = static::$service->events->listEvents($calendar_id);
            } catch (\Exception $e) {
                if ($e->getCode() == 404) {
                    return FALSE;
                } else {
                    return $e->getMessage();
                }
            }

            return TRUE;
        } else {
            return NULL;
        }
    }

    /**
     * Fetch the events across all the (given) coworkers
     * Returns a well-organized slots array
     *
     * @param array $services     The requested services
     * @param array $coworker_ids The requested coworkers
     * @param null  $min_get_time Minimum time for returned slots
     * @param null  $max_get_time Maximum time for returned slots
     * @param bool  $just_parse
     * @param       $timezone
     * @param int   $limit
     *
     * @return SlotsResults
     */
    public function getSlots(array $services, array $coworker_ids, $min_get_time = NULL, $max_get_time = NULL, $just_parse = FALSE, $timezone = NULL, $limit = 0)
    {
        if (NULL === $timezone) {
            $timezone = \TeamBooking\Toolkit\getTimezone();
        }
        if (NULL === $min_get_time) {
            $now = new \DateTime();
            $now->setTimezone($timezone);
            $min_get_time = $now->format(\DateTime::RFC3339);
        }
        /**
         * Get the reservations, for a later comparison.
         *
         * If a reservation is in pending state, the relative slot
         * in Google Calendar remains untouched, but the frontend calendar
         * must hide it.
         *
         */
        $reservation_in_database = Database\Reservations::getByTime($min_get_time, $max_get_time);

        if (!$just_parse) {
            $process = new Fetch\fromGoogle();
            $process->setRequestedCoworkers($coworker_ids);
            $process->sync();
        }

        $process = new Parser($reservation_in_database);
        $process->setRequestedCoworkers($coworker_ids);
        $process->setRequestedServices($services);
        $process->setRequestedMinTime($min_get_time);
        $process->setRequestedMaxTime($max_get_time);
        $return = $process->getSlots($limit);

        /**
         * Let's clean the soldout/booked slots, if requested, on a service basis
         */
        foreach ($return as $key => $slot) {
            // We must take reservations into account, with a multisite compatibility
            if (function_exists('is_multisite') && is_multisite()) {
                // multisite environment
                require_once ABSPATH . '/wp-admin/includes/plugin.php';
                global $wpdb;
                foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
                    switch_to_blog($blog_id);
                    if (is_plugin_active(plugin_basename(TEAMBOOKING_FILE_PATH))) {
                        $reservation_in_database = Database\Reservations::getByTime($min_get_time, $max_get_time);
                        $slot = $this->computeReservations($slot, $reservation_in_database);
                        $slot = $this->computeWaitingForApprovalReservations($slot, $reservation_in_database);
                    }
                    restore_current_blog();
                }
            } else {
                // no multisite environment
                $slot = $this->computeReservations($slot, $reservation_in_database);
                $slot = $this->computeWaitingForApprovalReservations($slot, $reservation_in_database);
            }
            try {
                if ($slot->isSoldout() && !Database\Services::get($slot->getServiceId())->getSettingsFor('show_soldout')) {
                    unset($return[ $key ]);
                }
            } catch (\Exception $e) {
                unset($return[ $key ]);
            }
            // WordPress custom hook
            Functions\schedule_slot_parse($slot);
            if (isset($slot->show) && $slot->show === FALSE) unset($return[ $key ]);
        }

        /**
         * Let's fill the object.
         */
        $return_obj = new SlotsResults($timezone);
        $return_obj->addSlotsFromArray((array)$return);

        /**
         * Return the results array.
         */
        $return_obj->trimSlots($min_get_time, $max_get_time);

        return $return_obj;
    }

    /**
     * Renders the frontend calendar.
     *
     * @param RenderParameters $parameters
     *
     * @return array|SlotsResults
     * @throws \Exception
     */
    public function getCalendar(RenderParameters $parameters, $just_parse = FALSE)
    {
        // Set the timezone and date now
        $timezone = $parameters->getTimezone();
        $date_now = \DateTime::createFromFormat('U', current_time('timestamp', TRUE));
        $date_now->setTimezone($timezone);

        // If already syncing, let's just parse (useful when there are multiple calendar widgets in one page)
        global $team_booking_is_fetching;
        if (isset($team_booking_is_fetching)) {
            $just_parse = TRUE;
        } else {
            $team_booking_is_fetching = TRUE;
        }
        // Retrieving the month
        $the_month = $parameters->getMonth();
        // If the month is not set, take the current
        if (empty($the_month)) {
            $parameters->setMonth($date_now->format('m'));
        }
        // Retrieving the year
        $the_year = $parameters->getYear();
        // If the year is not set, take the current
        if (empty($the_year)) {
            $parameters->setYear($date_now->format('Y'));
        }
        /**
         * Let's do a requested services count.
         *
         * If it's only one, and that one is a "Service" class,
         * then we need to render the form directly, without
         * the frontend calendar.
         */
        if (count($parameters->getRequestedServiceIds()) === 1) {
            // Let's put the value in a variable
            $arr = $parameters->getRequestedServiceIds();
            $service_id = reset($arr);
        }
        if (isset($service_id) && Database\Services::get($service_id)->getClass() === 'unscheduled') {
            // Calling the template for the unscheduled form
            $template = Frontend\Form::fromService($service_id);
            if ($parameters->getIsAjaxCall()) {
                if (!is_user_logged_in() && Database\Services::get($service_id)->getSettingsFor('logged_only')) {
                    echo Frontend\Form::getContentRegisterAdvice(TRUE);
                } else {
                    echo $template->getContent();
                }
            } else {
                $calendar = new Frontend\Calendar($parameters);
                if (!is_user_logged_in() && Database\Services::get($service_id)->getSettingsFor('logged_only')) {
                    $calendar->getFormDirectly(Frontend\Form::getContentRegisterAdvice(TRUE));
                } else {
                    $calendar->getFormDirectly($template->getContent());
                }
            }
        } else {
            /**
             * Set the max_time
             *
             * Check for automatic month selection option.
             * If not active or if the call is an Ajax call,
             * then set the max_time parameter
             */
            if ($parameters->getIsAjaxCall() || !Functions\getSettings()->isFirstMonthWithFreeSlotShown()) {
                /**
                 * We're using the "Y-m-t" method to get the last day
                 * of the month, and set it nearly to midnight.
                 */
                $the_time = date_i18n('Y-m-t H:i:s', mktime(23, 59, 59, $parameters->getMonth(), 1, $parameters->getYear()));

                /**
                 * Dealing with the timezone.
                 *
                 * Let's create a DateTime object, with the
                 * reference to the end of the month.
                 * Set the timezone to that object, so we can
                 * extract the offset in that point in time,
                 * and add the offset interval to the object itself.
                 *
                 * That's because Google will not read the timezone
                 * for the max_time / min_time params.
                 */
                $time_object = new \DateTime($the_time);
                $time_object->setTimezone($timezone);
                if ($timezone->getOffset($time_object) < 0) {
                    $time_object->add(new \DateInterval('PT' . abs($timezone->getOffset($time_object)) . 'S'));
                } else {
                    $time_object->sub(new \DateInterval('PT' . $timezone->getOffset($time_object) . 'S'));
                }

                // Let's set the max_time
                $max_time = $time_object->format(\DateTime::RFC3339);
            } else {
                // Let's keep the max_time null
                $max_time = NULL;
            }

            /**
             *  Set the min_time
             */
            $the_time = date_i18n('Y-m-d H:i:s', mktime(0, 0, 0, $parameters->getMonth(), 1, $parameters->getYear()));
            // Dealing with the timezone
            $time_object = new \DateTime($the_time);
            $time_object->setTimezone($timezone);
            if ($timezone->getOffset($time_object) < 0) {
                $time_object->add(new \DateInterval('PT' . abs($timezone->getOffset($time_object)) . 'S'));
            } else {
                $time_object->sub(new \DateInterval('PT' . $timezone->getOffset($time_object) . 'S'));
            }
            // Let's set the min_time
            $min_time = $time_object->format(\DateTime::RFC3339);

            /**
             * Get slots
             */
            $slots = $this->getSlots($parameters->getRequestedServiceIds(), $parameters->getRequestedCoworkerIds(), $min_time, $max_time, $just_parse, $timezone);

            /**
             * Let's check those three conditions:
             *
             * 1. Load the calendar at first month with slots option active
             * 2. Is not an Ajax call
             * 3. The slots result object is not empty
             *
             * If all of them are met, then we have a slots object that
             * must be trimmed to the first month with slots only.
             *
             */
            if (!$parameters->getIsAjaxCall()
                && $slots->thereAreSlots()
                && Functions\getSettings()->isFirstMonthWithFreeSlotShown()
            ) {
                // Conditions met, let's pick the closest slot in time
                $closest_slot = $slots->getClosestSlot();
                $closest_start_time = \DateTime::createFromFormat('U', strtotime($closest_slot->getStartTime()));
                $closest_start_time->setTimezone($parameters->getTimezone());
                // Set the month by picking it from the first slot
                $parameters->setMonth($closest_start_time->format('m'));
                // Set the year by picking it from the first slot
                $parameters->setYear($closest_start_time->format('Y'));
                // Set the max_time
                $the_time = date_i18n('Y-m-t H:i:s', mktime(23, 59, 59, $parameters->getMonth(), 1, $parameters->getYear()));
                $time_object = new \DateTime($the_time);
                $time_object->setTimezone($timezone);
                if ($timezone->getOffset($time_object) < 0) {
                    $time_object->add(new \DateInterval('PT' . abs($timezone->getOffset($time_object)) . 'S'));
                } else {
                    $time_object->sub(new \DateInterval('PT' . $timezone->getOffset($time_object) . 'S'));
                }
                $max_time = $time_object->format('c');
                // Set the min_time
                $the_time = date_i18n('Y-m-d H:i:s', mktime(0, 0, 0, $parameters->getMonth(), 1, $parameters->getYear()));
                $time_object = new \DateTime($the_time);
                $time_object->setTimezone($timezone);
                if ($timezone->getOffset($time_object) < 0) {
                    $time_object->add(new \DateInterval('PT' . abs($timezone->getOffset($time_object)) . 'S'));
                } else {
                    $time_object->sub(new \DateInterval('PT' . $timezone->getOffset($time_object) . 'S'));
                }
                $min_time = $time_object->format('c');
                // Finally, trim the slots
                $slots->trimSlots($min_time, $max_time);
            }

            /**
             * Preparing the slots for the template class.
             */
            $calendar = new Frontend\Calendar($parameters);
            $calendar->slots_obj = $slots;
            echo $calendar->getContent();

            return $slots;
        }

        return TRUE;
    }

    /**
     * @param RenderParameters $parameters
     *
     * @throws \Exception
     */
    public function getSchedule(RenderParameters $parameters)
    {
        // Set the timezone and date now
        $timezone = $parameters->getTimezone();
        $date_now = \DateTime::createFromFormat('U', current_time('timestamp', TRUE));
        $date_now->setTimezone($timezone);

        $the_day = $parameters->getDay();
        if (empty($the_day)) {
            $parameters->setDay($date_now->format('d'));
        }
        $the_month = $parameters->getMonth();
        if (empty($the_month)) {
            $parameters->setMonth($date_now->format('m'));
        }
        $the_year = $parameters->getYear();
        if (empty($the_year)) {
            $parameters->setYear($date_now->format('Y'));
        }

        if (count($parameters->getRequestedServiceIds()) === 1) {
            $services = $parameters->getRequestedServiceIds();
            $service = reset($services);
        }
        if (isset($service) && Database\Services::get($service)->getClass() === 'unscheduled') {
            echo "<div class='tb-day-schedule' data-day='" . $parameters->getDay() . "'></div>";
        } else {
            // Calling the template
            $schedule = new Frontend\Schedule($parameters);
            // Setting variables
            $schedule->setSlots($parameters->getSlots());
            // Render
            echo $schedule->getContent();
        }
    }

    /**
     * @param Slot  $slot
     * @param array $reservations
     *
     * @return Slot
     */
    private function computeReservations(Slot $slot, array $reservations)
    {
        try {
            $service = Database\Services::get($slot->getServiceId());
        } catch (\Exception $e) {
            return $slot;
        }

        if ($service->getClass() !== 'event') {
            return $slot;
        }

        if ($slot->isSoldout()) return $slot;

        /**
         * Are the attendees for this slot (counting the pending ones too, and the hide for approval too)
         * equal to the maximum allowed number?
         */
        $attendees_number = $slot->getAttendeesNumber();

        /**
         * in order to count the tickets already reserved we should extract
         * the tickets number from the reservation record
         */

        // Looping through the reservations
        foreach ($reservations as $reservation) {
            /* @var $reservation \TeamBooking_ReservationData */
            if ($reservation->getGoogleCalendarEvent() != $slot->getEventId()) continue;

            if ($slot->getFromReservation() === $reservation->getDatabaseId()) continue;

            if ($reservation->isConfirmed()
                && $reservation->getServiceId() == $slot->getServiceId()
            ) {
                $attendees_number += (int)$reservation->getTickets();
                $slot->addCustomer(array(
                    'email'          => $reservation->getCustomerEmail(),
                    'name'           => $reservation->getCustomerDisplayName(),
                    'id'             => $reservation->getCustomerUserId(),
                    'address'        => $reservation->getCustomerAddress(),
                    'timezone'       => $reservation->getCustomerTimezone(),
                    'tickets'        => $reservation->getTickets(),
                    'status'         => $reservation->getStatus(),
                    'reservation_id' => $reservation->getDatabaseId()
                ));
            }

            if ($reservation->isPending()
                && !Functions\isReservationTimedOut($reservation)
                && $reservation->getStart() == strtotime($slot->getStartTime())
                && $reservation->getEnd() == strtotime($slot->getEndTime())
            ) {
                /**
                 * There is a pending reservation with this event ID
                 * and the record is not timed out, so let's add its
                 * tickets to the global counter
                 */
                $attendees_number += (int)$reservation->getTickets();
                $slot->addCustomer(array(
                    'email'          => $reservation->getCustomerEmail(),
                    'name'           => $reservation->getCustomerDisplayName(),
                    'id'             => $reservation->getCustomerUserId(),
                    'address'        => $reservation->getCustomerAddress(),
                    'timezone'       => $reservation->getCustomerTimezone(),
                    'tickets'        => $reservation->getTickets(),
                    'status'         => $reservation->getStatus(),
                    'reservation_id' => $reservation->getDatabaseId()
                ));
            }
        }

        /**
         * If the total attendees number is greater that or equal to the maximum
         * tickets allowed, let's mark the slot as "Sold Out", that is the same
         * as "Booked", more or less.
         */
        if ($attendees_number >= $service->getSlotMaxTickets()) {
            // Marking it as soldout
            $slot->setSoldout();
            $slot->setAttendeesNumber($service->getSlotMaxTickets());
        } else {
            $slot->setAttendeesNumber($attendees_number);
        }

        return $slot;
    }

    /**
     * @param Slot  $slot
     * @param array $reservations
     *
     * @return Slot
     */
    private function computeWaitingForApprovalReservations(Slot $slot, array $reservations)
    {
        try {
            $service = Database\Services::get($slot->getServiceId());
        } catch (\Exception $e) {
            return $slot;
        }

        if ($service->getClass() !== 'event'
            || $service->getSettingsFor('free_until_approval')
        ) {
            return $slot;
        }

        $temp_tickets = 0;

        foreach ($reservations as $reservation) {
            /* @var $reservation \TeamBooking_ReservationData */
            if ($reservation->isWaitingApproval()
                && $reservation->getDatabaseId() !== $slot->getFromReservation()
                && $reservation->getGoogleCalendarEvent() === $slot->getEventId()
                && $reservation->getServiceId() === $slot->getServiceId()
                && $reservation->getStart() == strtotime($slot->getStartTime())
                && $reservation->getEnd() == strtotime($slot->getEndTime())
            ) {
                // Event, let's sum the tickets to hide
                $temp_tickets += $reservation->getTickets();
                $slot->addCustomer(array(
                    'email'          => $reservation->getCustomerEmail(),
                    'name'           => $reservation->getCustomerDisplayName(),
                    'id'             => $reservation->getCustomerUserId(),
                    'address'        => $reservation->getCustomerAddress(),
                    'timezone'       => $reservation->getCustomerTimezone(),
                    'tickets'        => $reservation->getTickets(),
                    'status'         => $reservation->getStatus(),
                    'reservation_id' => $reservation->getDatabaseId()
                ));
            }
        }
        if ($temp_tickets) {
            $temp_tickets += $slot->getAttendeesNumber();
            if ($temp_tickets >= $service->getSlotMaxTickets()) {
                // Marking it as soldout
                $slot->setSoldout();
                $slot->setAttendeesNumber($service->getSlotMaxTickets());
            } else {
                $slot->setAttendeesNumber($temp_tickets);
            }
        }

        return $slot;
    }

}
