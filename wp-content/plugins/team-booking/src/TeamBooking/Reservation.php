<?php

defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Toolkit;

/**
 * Class TeamBooking_Reservation
 *
 * @author VonStroheim
 */
class TeamBooking_Reservation
{
    private $has_coworker_token;

    /** @var TeamBooking_ReservationData */
    private $data;

    /** @var TeamBooking\Abstracts\Service */
    private $service;
    private $reservations;

    /** @var TeamBookingCoworker */
    private $coworker;

    /** @var TeamBookingCustomBTSettings */
    private $coworker_service_data;

    /** @var  Google_Service_Calendar */
    private static $google_service;

    /** @var TeamBookingSettings */
    private static $settings;

    /** @var Google_Service_Calendar_Event */
    private $event_to_patch;

    public function __construct(TeamBooking_ReservationData $data)
    {
        $this->has_coworker_token = FALSE;
        static::$settings = Functions\getSettings();
        $this->data = $data;
        if (!$data->getCoworker()) {
            $this->data->setCoworker(static::chooseCoworker($data->getServiceId()));
        }
        $this->coworker = static::$settings->getCoworkerData($this->data->getCoworker());
        $this->coworker_service_data = $this->coworker->getCustomEventSettings($data->getServiceId());
        $this->service = Database\Services::get($data->getServiceId());
        $this->reservations = Database\Reservations::getByService($this->service->getId());
        // Create a new Google Client object
        $client = new Google_Client();
        $client->addScope('https://www.googleapis.com/auth/calendar');
        $client->setApplicationName(static::$settings->getApplicationProjectName());
        $client->setClientId(static::$settings->getApplicationClientId());
        $client->setClientSecret(static::$settings->getApplicationClientSecret());
        $client->setAccessType('offline');
        static::$google_service = new Google_Service_Calendar($client);
        $access_token = $this->coworker->getAccessToken();
        if (!empty($access_token)) {
            $client->setAccessToken($this->coworker->getAccessToken());
            $this->has_coworker_token = TRUE;
        }
    }

    /**
     * Confirms the reservation (always already set in database)
     *
     * @param null|string $who
     *
     * @return TeamBooking_Error|TeamBooking_ReservationData
     */
    public function doReservation($who = NULL)
    {
        if (NULL === $who) $who = get_current_user_id();
        $this->data->setConfirmationWho($who);
        $optional_params = array();

        if ($this->service->getClass() === 'unscheduled') {

            return $this->data;
        } else {
            if (!$this->has_coworker_token) {
                $error = new TeamBooking_Error(1);

                return $error;
            }
            // Get the Google Calendar ID (backward-compatible)
            if (!$this->data->getGoogleCalendarId()) {
                $calendar_data = reset($this->coworker->getCalendars());
                $google_calendar_id = $calendar_data['calendar_id'];
            } else {
                $google_calendar_id = $this->data->getGoogleCalendarId();
            }

            // Retrieve the event to update
            if (NULL === $this->data->getGoogleCalendarEvent()) {
                /* @var $event_retrieved Google_Service_Calendar_Event */
                // the following is essentially a check for multiple-service container collisions
                try {
                    $event_retrieved = static::$google_service->events->get($google_calendar_id, $this->data->getGoogleCalendarEventParent());
                } catch (Google_Service_Exception $e) {
                    $error = new TeamBooking_Error(5);
                    $error->setMessage($e->getMessage());

                    return $error;
                }

                $event_list_params = array(
                    'timeMin'      => $event_retrieved->getStart()->getDateTime(),
                    'timeMax'      => $event_retrieved->getEnd()->getDateTime(),
                    'singleEvents' => TRUE,
                    'orderBy'      => 'startTime',
                );
                $events = static::$google_service->events->listEvents($google_calendar_id, $event_list_params);
                // array of "linked event titles"
                $services_array = array_map('TeamBooking\\Functions\\tb_mb_strtolower', array_map('trim', explode('+', substr(\TeamBooking\Parser::parseSummary($event_retrieved->getSummary()), 0, -10))));

                foreach ($events->getItems() as $item) {
                    /** #@var $item Google_Service_Calendar_Event */
                    if ($item->getSummary() == $event_retrieved->getSummary()) {
                        continue; // the container is itself...
                    }

                    if (in_array(\TeamBooking\Parser::parseSummary($item->getSummary()), $services_array)
                        && strtotime($item->getStart()->getDateTime()) == $this->data->getStart()
                        && strtotime($item->getEnd()->getDateTime()) == $this->data->getEnd()
                    ) {
                        if ($this->service->getClass() === 'event'
                            && \TeamBooking\Parser::parseSummary($item->getSummary()) == Functions\tb_mb_strtolower($this->coworker_service_data->getLinkedEventTitle())
                        ) {
                            // There is an exact collision, but could still be possible to make the reservation if the concurrent event is of the same service...
                            $this->data->setGoogleCalendarEvent($item->getId());
                            try {
                                $event_retrieved = static::$google_service->events->get($google_calendar_id, $this->data->getGoogleCalendarEvent());
                            } catch (Google_Service_Exception $e) {
                                $error = new TeamBooking_Error(5);
                                $error->setMessage($e->getMessage());

                                return $error;
                            }

                        }
                    } elseif (in_array(\TeamBooking\Parser::parseSummary($item->getSummary()), $services_array)
                        && (
                            (strtotime($item->getStart()->getDateTime()) < $this->data->getEnd()
                                && strtotime($item->getStart()->getDateTime()) >= $this->data->getStart())
                            ||
                            (strtotime($item->getEnd()->getDateTime()) > $this->data->getStart()
                                && strtotime($item->getEnd()->getDateTime()) <= $this->data->getEnd())
                        )
                        // TODO: add buffers
                    ) {
                        $error = new TeamBooking_Error(2);

                        return $error;
                    } elseif ($this->service->getClass() === 'appointment'
                        // to avoid overbooking for concurrent reservation attempt
                        && \TeamBooking\Parser::parseSummary($item->getSummary()) == Functions\tb_mb_strtolower($this->coworker_service_data->getAfterBookedTitle())
                        && strtotime($item->getStart()->getDateTime()) == $this->data->getStart()
                        && strtotime($item->getEnd()->getDateTime()) == $this->data->getEnd()
                    ) {
                        $error = new TeamBooking_Error(2);

                        return $error;
                    }
                }
            } else {
                /* @var $event_retrieved Google_Service_Calendar_Event */
                try {
                    $event_retrieved = static::$google_service->events->get($google_calendar_id, $this->data->getGoogleCalendarEvent());
                } catch (Google_Service_Exception $e) {
                    $error = new TeamBooking_Error(5);
                    $error->setMessage($e->getMessage());

                    return $error;
                }
            }

            if (!$this->isEventStillAvailable($event_retrieved)) {
                $error = new TeamBooking_Error(2);

                return $error;
            }
            $this->event_to_patch = new Google_Service_Calendar_Event();
            // Privacy precautions
            $this->event_to_patch->setGuestsCanSeeOtherGuests(FALSE);
            $this->event_to_patch->setGuestsCanInviteOthers(FALSE);
            $this->event_to_patch->setGuestsCanModify(FALSE);
            // Other settings
            $this->event_to_patch->setHangoutLink($event_retrieved->getHangoutLink());
            $this->setNewTitle();
            $this->setSlotStartEndTimes($event_retrieved);
            $this->setUnhookedDescription($event_retrieved, $this->data->getDatabaseId());
            $this->setSource();
            $this->setReminders();
            $this->setEventColor();
            $this->setLocation();
            $this->data->setEventHtmlLink($event_retrieved->getHtmlLink());
            if ($this->service->getClass() === 'event') {
                $add_attendee = $this->addAttendee($event_retrieved);

                // Error checkpoint
                if ($add_attendee instanceof TeamBooking_Error) {
                    return $add_attendee;
                }
            } elseif ($this->service->getClass() === 'appointment' && $this->coworker_service_data->addCustomerAsGuest()) {
                $now = date_i18n(get_option('date_format'));
                $attendees = $event_retrieved->getAttendees();
                $new_attendee = new Google_Service_Calendar_EventAttendee();
                // set a random email, if not present
                if (!$this->data->getCustomerEmail()) {
                    $domain = explode('@', $this->service->getEmailToAdmin('to'));
                    $domain = count($domain) === 2 ? $domain[1] : 'example.com';
                    $new_attendee->setEmail(uniqid('tbk', TRUE) . '@' . $domain);
                } else {
                    $new_attendee->setEmail($this->data->getCustomerEmail());
                }
                $new_attendee->setDisplayName($this->data->getCustomerDisplayName());
                $new_attendee->setComment($this->data->getCustomerDisplayName() . ' - ' . sprintf(__('Date of reservation: %s', 'team-booking'), $now));
                $new_attendee->setResponseStatus('accepted');
                $attendees[] = $new_attendee;
                $this->event_to_patch->setAttendees($attendees);
            }

            /**
             * TODO: File attachment support (requires Google Drive)
             */
            #$file_references = $this->data->getFilesReferences();
            #if (!empty($file_references)) {
            #    $attachments = $event_retrieved->getAttachments();
            #    foreach ($file_references as $hook => $file_info_array) {
            #        if (filter_var($file_info_array['url'], FILTER_VALIDATE_URL)) {
            #            $attachments[] = array(
            #                'fileUrl' => $file_info_array['url'],
            #                'mimeType' => $file_info_array['type'],
            #                'title' => $hook
            #            );
            #        }
            #    }
            #    $this->event_to_patch->setAttachments($attachments);
            #    $optional_params['supportsAttachments'] = TRUE;
            #}

            // Update event
            try {
                // we should create a new one?
                if (NULL === $this->data->getGoogleCalendarEvent()) {
                    $added_event = static::$google_service->events->insert(
                        $google_calendar_id,
                        $this->event_to_patch,
                        $optional_params
                    );
                    $added_event_id = $added_event->getId();

                    if ($this->data->isWaitingApproval()
                        && $this->service->getClass() === 'event'
                    ) {
                        foreach ($this->reservations as $db_id => $reservation) {
                            /* @var $reservation TeamBooking_ReservationData */
                            if ($reservation->verifyChecksum($this->data)) {
                                continue;
                            }
                            if ($reservation->isWaitingApproval()
                                && NULL === $reservation->getGoogleCalendarEvent()
                                && $reservation->getGoogleCalendarEventParent() == $this->data->getGoogleCalendarEventParent()
                                && $reservation->getStart() == $this->data->getStart()
                                && $reservation->getEnd() == $this->data->getEnd()
                            ) {
                                $reservation->setGoogleCalendarEvent($added_event_id);
                                Database\Reservations::update($reservation);
                            }
                        }
                    }
                    $this->data->setGoogleCalendarEvent($added_event_id);
                    $this->data->setHangoutLink($added_event->getHangoutLink());
                    $this->data->setEventHtmlLink($added_event->getHtmlLink());
                } else {
                    static::$google_service->events->patch(
                        $google_calendar_id,
                        $this->data->getGoogleCalendarEvent(),
                        $this->event_to_patch,
                        $optional_params
                    );
                    $this->data->setHangoutLink($this->event_to_patch->getHangoutLink());
                }
            } catch (Exception $ex) {
                // Error checkpoint
                if (strpos($ex->getMessage(), 'Invalid attendee email') !== FALSE) {
                    $error = new TeamBooking_Error(4);

                    return $error;
                } else {
                    $error = new TeamBooking_Error(5);
                    $error->setMessage('Google API error: ' . $ex->getMessage());

                    return $error;
                }
            }

            return $this->data;
        }
    }

    /**
     * Cancel the reservation
     *
     * If everything went fine, it returns the updated
     * reservation data, which must be saved in the database
     * by the function caller (log_id is not available here).
     *
     * @param                 $reservation_database_id
     * @param int|string|null $who
     *
     * @return TeamBooking_Error|TeamBooking_ReservationData
     */
    public function cancelReservation($reservation_database_id, $who = NULL)
    {
        if (NULL === $who) $who = get_current_user_id();
        if ($this->data->isWaitingApproval()) {
            /**
             * Updating the reservation data
             */
            $this->data->setStatusCancelled();
            $this->data->setCancellationWho($who);
            $this->data->setEnumerableForCustomerLimits(FALSE);
            if ($_POST['reason']) {
                $this->data->setCancellationReason(Toolkit\filterInput($_POST['reason']));
            }

            /*
             * Send email to customer
             */
            if ($this->service->getEmailCancellationToCustomer('send') && $this->data->getCustomerEmail()) {
                $this->sendCancellationEmail();
            }

            /*
             * Send email to admin/coworker
             */
            if ($this->service->getEmailCancellationToAdmin('send')) {
                $this->sendCancellationEmailBackend($who);
            }

            return $this->data;
        }
        if ($this->service->getClass() === 'appointment') {
            $booked_title = static::$settings->getCoworkerData($this->data->getCoworker())->getCustomEventSettings($this->service->getId())->getAfterBookedTitle();
            $free_title = static::$settings->getCoworkerData($this->data->getCoworker())->getCustomEventSettings($this->service->getId())->getLinkedEventTitle();
            $gcal_event_id = $this->data->getGoogleCalendarEvent();
            // Check if the coworker is still active
            if (!$this->has_coworker_token) {
                $error = new TeamBooking_Error(1);

                return $error;
            }
            // Get the Google Calendar ID (backward-compatible)
            if (!$this->data->getGoogleCalendarId()) {
                $calendar_data = reset($this->coworker->getCalendars());
                $google_calendar_id = $calendar_data['calendar_id'];
            } else {
                $google_calendar_id = $this->data->getGoogleCalendarId();
            }
            /* @var $event_retrieved Google_Service_Calendar_Event */
            $event_retrieved = static::$google_service->events->get($google_calendar_id, $gcal_event_id);
            // Check if the event is still available and marked as booked
            if ($event_retrieved->getStatus() === 'cancelled' || \TeamBooking\Parser::parseSummary($event_retrieved->getSummary()) !== Functions\tb_mb_strtolower($booked_title)) {
                $error = new TeamBooking_Error(7);

                return $error;
            }
            try {
                if ($this->data->getGoogleCalendarEventParent()) {
                    /**
                     * If the Appointment was container-derived,
                     * just delete it
                     */
                    static::$google_service->events->delete($google_calendar_id, $gcal_event_id);
                } else {
                    /**
                     * If the Appointment was slot-derived, then
                     * we should reset the slot in it's available state
                     */
                    $event_retrieved->setSummary($free_title);
                    $event_retrieved->setColorId(NULL);
                    $reminders = new Google_Service_Calendar_EventReminders();
                    $reminders->setUseDefault(TRUE);
                    $event_retrieved->setReminders($reminders);
                    $event_retrieved->setDescription('');
                    if ($this->coworker_service_data->addCustomerAsGuest()) {
                        $attendees = $event_retrieved->getAttendees();
                        foreach ($attendees as $id => $attendee) {
                            /* @var $attendee Google_Service_Calendar_EventAttendee */
                            if ($attendee->getEmail() == $this->data->getCustomerEmail()) {
                                // Found!
                                unset($attendees[ $id ]);
                                break;
                            }
                        }
                        $event_retrieved->setAttendees($attendees);
                    }
                    static::$google_service->events->update($google_calendar_id, $gcal_event_id, $event_retrieved);
                }
            } catch (Exception $ex) {
                $error = new TeamBooking_Error(5);
                $error->setMessage('Google API error: ' . $ex->getMessage());

                return $error;
            }
            /**
             * Updating the reservation data
             */
            $this->data->setStatusCancelled();
            $this->data->setCancellationWho($who);
            $this->data->setEnumerableForCustomerLimits(FALSE);
            if ($_POST['reason']) {
                $this->data->setCancellationReason(Toolkit\filterInput($_POST['reason']));
            }
            /*
             * Send email to customer
             */
            if ($this->service->getEmailCancellationToCustomer('send') && $this->data->getCustomerEmail()) {
                $this->sendCancellationEmail();
            }

            /*
             * Send email to admin/coworker
             */
            if ($this->service->getEmailCancellationToAdmin('send')) {
                $this->sendCancellationEmailBackend($who);
            }

            return $this->data;
        } elseif ($this->service->getClass() === 'event') {
            $free_title = static::$settings->getCoworkerData($this->data->getCoworker())->getCustomEventSettings($this->service->getId())->getLinkedEventTitle();
            $gcal_event_id = $this->data->getGoogleCalendarEvent();
            // Check if the coworker is still active
            if (!$this->has_coworker_token) {
                $error = new TeamBooking_Error(1);

                return $error;
            }
            // Get the Google Calendar ID (backward-compatible)
            if (!$this->data->getGoogleCalendarId()) {
                $calendar_data = reset($this->coworker->getCalendars());
                $google_calendar_id = $calendar_data['calendar_id'];
            } else {
                $google_calendar_id = $this->data->getGoogleCalendarId();
            }
            /* @var $event_retrieved Google_Service_Calendar_Event */
            $event_retrieved = static::$google_service->events->get($google_calendar_id, $gcal_event_id);
            // Check if the event is still available and referenced to the service
            if ($event_retrieved->getStatus() === 'cancelled'
                || \TeamBooking\Parser::parseSummary($event_retrieved->getSummary()) != Functions\tb_mb_strtolower($free_title)
            ) {
                $error = new TeamBooking_Error(7);

                return $error;
            }
            try {
                $attendees = $event_retrieved->getAttendees();
                $attendee_to_modify = FALSE;
                $attendee_to_modify_id = FALSE;
                /**
                 * Let's find our customer
                 */
                if ($this->coworker_service_data->addCustomerAsGuest()) {
                    foreach ($attendees as $id => $attendee) {
                        /* @var $attendee Google_Service_Calendar_EventAttendee */
                        if ($attendee->getEmail() == $this->data->getCustomerEmail()) {
                            // Found!
                            $attendee_to_modify = $attendee;
                            $attendee_to_modify_id = $id;
                            break;
                        }
                    }
                }
                /**
                 * Now, let's extract the total tickets bounded to this customer
                 */
                $old_ticket_number = 0;
                foreach ($this->reservations as $reservation) {
                    /* @var $reservation TeamBooking_ReservationData */
                    if ($reservation->getGoogleCalendarEvent() == $gcal_event_id
                        && $reservation->isBooked()
                    ) {
                        if (($this->data->getCustomerUserId() > 0 && $this->data->getCustomerUserId() === $reservation->getCustomerUserId())
                            || ($this->data->getCustomerUserId() === 0 && $reservation->getCustomerEmail() === $this->data->getCustomerEmail())
                        )
                            $old_ticket_number += (int)$reservation->getTickets();
                    }
                }
                // Decreasing
                $new_ticket_number = $old_ticket_number - (int)$this->data->getTickets();
                /**
                 * If the new ticket number is zero, then just drop the attendee,
                 * otherwise update it
                 */
                if ($attendee_to_modify) {
                    if ($new_ticket_number <= 0) {
                        unset($attendees[ $attendee_to_modify_id ]);
                    } else {
                        $now = date_i18n(get_option('date_format'));
                        $attendee_to_modify->setComment($this->data->getCustomerDisplayName() . ' - ' . sprintf(__('Date of reservation: %s', 'team-booking'), $now));
                        $attendee_to_modify->setAdditionalGuests((int)$new_ticket_number - 1);
                        $attendees[ $attendee_to_modify_id ] = $attendee_to_modify;
                    }
                }

                $event_retrieved = $this->setUnhookedDescription($event_retrieved, $reservation_database_id, TRUE);

                $there_are_guests = FALSE;
                foreach ($this->reservations as $id => $reservation) {
                    /* @var $reservation TeamBooking_ReservationData */
                    if ($reservation_database_id != $id
                        && $reservation->isConfirmed()
                        && $reservation->getGoogleCalendarEvent() == $event_retrieved->getId()
                    ) {
                        $there_are_guests = TRUE;
                    }
                }

                if (!$there_are_guests
                    && $this->data->getGoogleCalendarEventParent()
                ) {

                    /**
                     * If the Event was container-derived and it has no attendees left,
                     * just delete it
                     */
                    static::$google_service->events->delete($google_calendar_id, $gcal_event_id);
                    foreach ($this->reservations as $db_id => $reservation) {
                        /* @var $reservation TeamBooking_ReservationData */
                        if ($reservation->isWaitingApproval()
                            && $reservation->getGoogleCalendarEventParent() == $this->data->getGoogleCalendarEventParent()
                            && $reservation->getStart() == $this->data->getStart()
                            && $reservation->getEnd() == $this->data->getEnd()
                        ) {
                            $reservation->setGoogleCalendarEvent(NULL);
                            Database\Reservations::update($reservation);
                        }
                    }
                } else {

                    if ($this->data->getGoogleCalendarEventParent()) {
                        // We should pass this to another "stack"
                        foreach ($this->reservations as $db_id => $reservation) {
                            /* @var $reservation TeamBooking_ReservationData */
                            if (!$reservation->isWaitingApproval()
                                && $reservation->getGoogleCalendarEventParent() == NULL
                                && $reservation->getGoogleCalendarEvent() == $this->data->getGoogleCalendarEvent()
                                && $reservation->getStart() == $this->data->getStart()
                                && $reservation->getEnd() == $this->data->getEnd()
                            ) {
                                $reservation->setGoogleCalendarEventParent($this->data->getGoogleCalendarEventParent());
                                Database\Reservations::update($reservation);
                                break;
                            }
                        }
                    }

                    /**
                     * If the Event was slot-derived or it has attendees left, then update it
                     */
                    if ($this->coworker_service_data->addCustomerAsGuest()) {
                        $event_retrieved->setAttendees($attendees);
                    }
                    if (!$there_are_guests) {
                        $event_retrieved->setColorId(NULL);
                        $reminders = new Google_Service_Calendar_EventReminders();
                        $reminders->setUseDefault(TRUE);
                        $event_retrieved->setReminders($reminders);
                    }
                    static::$google_service->events->update($google_calendar_id, $gcal_event_id, $event_retrieved);
                }
            } catch (Exception $ex) {
                $error = new TeamBooking_Error(5);
                $error->setMessage('Google API error: ' . $ex->getMessage());

                return $error;
            }

            /*
             * Updating the reservation data
             */
            $this->data->setStatusCancelled();
            $this->data->setCancellationWho($who);
            $this->data->setEnumerableForCustomerLimits(FALSE);
            if (isset($_POST['reason'])) {
                $this->data->setCancellationReason(Toolkit\filterInput($_POST['reason']));
            }

            /*
             * Send email to customer
             */
            if ($this->service->getEmailCancellationToCustomer('send') && $this->data->getCustomerEmail()) {
                $this->sendCancellationEmail();
            }

            /*
             * Send email to admin/coworker
             */
            if ($this->service->getEmailCancellationToAdmin('send')) {
                $this->sendCancellationEmailBackend($who);
            }

            return $this->data;
        } else {
            $error = new TeamBooking_Error(10);

            return $error;
        }
    }

    /**
     * Prepare and set the new google calendar event title
     */
    private function setNewTitle()
    {
        if ($this->service->getClass() !== 'event') {
            $summary = $this->coworker_service_data->getAfterBookedTitle();
            $additional_data = $this->coworker_service_data->getAdditionalEventTitleData();
            $append = '';
            if ($additional_data['customer']['full_name']) {
                $append .= ' ' . Toolkit\unfilterInput($this->data->getCustomerDisplayName());
            }
            if ($additional_data['customer']['email']) {
                $append .= ' ' . Toolkit\unfilterInput($this->data->getCustomerEmail());
            }
            if ($additional_data['customer']['phone']) {
                $append .= ' ' . Toolkit\unfilterInput($this->data->getCustomerPhone());
            }
            if (!empty($append)) $summary .= ' || ' . trim($append);
            $this->event_to_patch->setSummary($summary);
        } else {
            if ($this->data->isFromContainer()) {
                $summary = $this->coworker_service_data->getLinkedEventTitle();
                $this->event_to_patch->setSummary($summary);
            }
        }
    }

    /**
     * Sets the event description, with all the hooks replaced
     *
     * @param Google_Service_Calendar_Event $event_retrieved
     * @param null                          $reservation_db_id_to_exclude
     * @param bool                          $return_the_event
     *
     * @return Google_Service_Calendar_Event
     */
    private function setUnhookedDescription(Google_Service_Calendar_Event $event_retrieved, $reservation_db_id_to_exclude = NULL, $return_the_event = FALSE)
    {
        if ($this->service->getClass() === 'event') {
            $description = '';
            if ($this->coworker_service_data->getEventDescriptionContent() === 1) {
                foreach ($this->reservations as $id => $reservation) {
                    /* @var $reservation TeamBooking_ReservationData */
                    if ($reservation_db_id_to_exclude != $id
                        && $reservation->isConfirmed()
                        && $reservation->getGoogleCalendarEvent() == $event_retrieved->getId()
                    ) {
                        $date = date_i18n(get_option('date_format'), $reservation->getCreationInstant());
                        $description .= $reservation->getCustomerDisplayName()
                            . ' ' . $reservation->getCustomerPhone()
                            . ' ' . $reservation->getCustomerEmail()
                            . ' +' . $reservation->getTickets()
                            . ' (#' . $reservation->getDatabaseId() . ' - '
                            . sprintf(__('Date of reservation: %s', 'team-booking'), $date) . ')';
                        $description .= "\n";
                    }
                }
                if (!$return_the_event) {
                    $now = date_i18n(get_option('date_format'));
                    $description .= $this->data->getCustomerDisplayName()
                        . ' ' . $this->data->getCustomerPhone()
                        . ' ' . $this->data->getCustomerEmail()
                        . ' +' . $this->data->getTickets()
                        . ' (#' . $this->data->getDatabaseId() . ' - '
                        . sprintf(__('Date of reservation: %s', 'team-booking'), $now) . ')';
                }
            }
        } else {
            $description = $this->coworker_service_data->getNotificationEmailBody();
            // Replace Hooks
            $description = Toolkit\findAndReplaceHooks($description, $this->data->getHooksArray());
            $breaks = array(
                '<br />',
                '<br>',
                '<br/>',
            );
            $description = str_ireplace($breaks, "\n", $description);
        }
        if ($return_the_event) {
            $event_retrieved->setDescription(Toolkit\unfilterInput(strip_tags($description)));

            return $event_retrieved;
        } else {
            $this->event_to_patch->setDescription(Toolkit\unfilterInput(strip_tags($description)));
        }

        return TRUE;
    }

    /**
     * Sets the site as google calendar event source
     */
    private function setSource()
    {
        $source = new Google_Service_Calendar_EventSource;
        $source->setTitle(static::$settings->getApplicationProjectName());
        $source->setUrl(home_url());
        $this->event_to_patch->setSource($source);
    }

    /**
     * Sets the reminders
     */
    private function setReminders()
    {
        $reminder_setting = $this->coworker_service_data->getReminder();
        $reminders = new Google_Service_Calendar_EventReminders;
        if ($reminder_setting !== 'none' && $reminder_setting !== 0) {
            $reminders->setUseDefault(FALSE);
            $reminder = array(
                'method'  => 'email',
                'minutes' => $reminder_setting,
            );
            $reminders->setOverrides(array($reminder));
        }
        $this->event_to_patch->setReminders($reminders);
    }

    /**
     * Sets the google calendar event color
     */
    private function setEventColor()
    {
        $color_setting = $this->coworker_service_data->getBookedEventColor();
        if ($color_setting) {
            $this->event_to_patch->setColorId($color_setting);
        } else {
            $this->event_to_patch->setColorId(NULL);
        }
    }

    /**
     * Sets the google calendar event location
     */
    private function setLocation()
    {
        if ($this->data->getCustomerAddress()
            && $this->service->getSettingsFor('location') === 'none'
            && $this->service->getClass() !== 'event'
        ) {
            $this->event_to_patch->setLocation($this->data->getCustomerAddress());
        }
    }

    /**
     * Adds an attendee to the google calendar event
     *
     * @param Google_Service_Calendar_Event $event_retrieved
     *
     * @return boolean|\TeamBooking_Error
     */
    private function addAttendee(Google_Service_Calendar_Event $event_retrieved)
    {
        $now = date_i18n(get_option('date_format'));

        // Check again the attendees limit
        $attendees = $event_retrieved->getAttendees();
        $attendees_number = 0;
        $customer_total_tickets = 0;
        foreach ($this->reservations as $reservation) {
            /* @var $reservation TeamBooking_ReservationData */
            if ($reservation->isBooked()
                && $reservation->getGoogleCalendarEvent() == $event_retrieved->getId()
                && $reservation->getDatabaseId() !== $this->data->getDatabaseId()
            ) {
                $attendees_number += (int)$reservation->getTickets();
                if (($this->data->getCustomerUserId() > 0 && $reservation->getCustomerUserId() === $this->data->getCustomerUserId())
                    || ($this->data->getCustomerUserId() === 0 && $reservation->getCustomerEmail() === $this->data->getCustomerEmail())
                ) {
                    $customer_total_tickets += (int)$reservation->getTickets();
                }
            }
        }

        if ($attendees_number == $this->service->getSlotMaxTickets()) {
            // the event is full
            $error = new TeamBooking_Error(6);

            return $error;
        }

        $new_attendee = new Google_Service_Calendar_EventAttendee();
        // set a random email, if not present
        if (!$this->data->getCustomerEmail()) {
            $domain = explode('@', $this->service->getEmailToAdmin('to'));
            if (count($domain) == 2) {
                $domain = $domain[1];
            } else {
                $domain = 'example.com';
            }
            $new_attendee->setEmail(uniqid('tbk', TRUE) . '@' . $domain);
            $new_ticket_number = (int)$this->data->getTickets();
        } else {
            // Check the email for already present reservations
            if ($this->hasCustomerAlreadyBooked($event_retrieved)) {
                // Check if more than one ticket per user are allowed,
                // so we need to update the number or simply return an error
                if ($this->service->getSlotMaxUserTickets() > 1 || current_user_can('manage_options')) {
                    // Increasing
                    $new_ticket_number = $customer_total_tickets + (int)$this->data->getTickets();
                    // Check if the new total tickets are more than what allowed
                    // per single user
                    if (!current_user_can('manage_options') && $new_ticket_number > $this->service->getSlotMaxUserTickets()) {
                        // tickets are more than allowed
                        $error = new TeamBooking_Error(8);

                        return $error;
                    }

                    // If there is an attendee with that email, let's update it
                    if ($this->coworker_service_data->addCustomerAsGuest()) {
                        foreach ($attendees as $attendee) {
                            /* @var $attendee Google_Service_Calendar_EventAttendee */
                            if ($attendee->getEmail() === $this->data->getCustomerEmail()) {
                                $attendee->setComment($this->data->getCustomerDisplayName() . ' - ' . sprintf(__('Date of reservation: %s', 'team-booking'), $now));
                                $attendee->setAdditionalGuests((int)$new_ticket_number - 1);
                                $attendee->setResponseStatus('accepted');
                                $this->event_to_patch->setAttendees($attendees);

                                return TRUE;
                            }
                        }
                    }
                } else {
                    // the user has already booked
                    $error = new TeamBooking_Error(3);

                    return $error;
                }
            } else {
                $new_ticket_number = (int)$this->data->getTickets();
            }
            $new_attendee->setEmail($this->data->getCustomerEmail());
        }

        if ($this->coworker_service_data->addCustomerAsGuest()) {
            $new_attendee->setDisplayName($this->data->getCustomerDisplayName());
            $new_attendee->setComment($this->data->getCustomerDisplayName() . ' - ' . sprintf(__('Date of reservation: %s', 'team-booking'), $now));
            $new_attendee->setAdditionalGuests((int)$new_ticket_number - 1);
            $new_attendee->setResponseStatus('accepted');
            $attendees[] = $new_attendee;
            $this->event_to_patch->setAttendees($attendees);
        }

        return TRUE;
    }

    /**
     * Sets the start/end boundings
     *
     * @param Google_Service_Calendar_Event $event_retrieved
     */
    private function setSlotStartEndTimes(Google_Service_Calendar_Event $event_retrieved)
    {
        if ($this->data->isFromContainer()) {
            $event_start_time = new Google_Service_Calendar_EventDateTime();
            $event_start_time->setDateTime($this->data->getSlotStart());
            $event_retrieved->setStart($event_start_time);
            $this->event_to_patch->setStart($event_start_time);
            $event_end_time = new Google_Service_Calendar_EventDateTime();
            $event_end_time->setDateTime($this->data->getSlotEnd());
            $event_retrieved->setEnd($event_end_time);
            $this->event_to_patch->setEnd($event_end_time);
        }
        if ($event_retrieved->getStart()->getDateTime()) {
            $this->data->setStart(strtotime($event_retrieved->getStart()->getDateTime()));
            $this->data->setEnd(strtotime($event_retrieved->getEnd()->getDateTime()));
        } else {
            // Is all-day
            $this->data->setStart(strtotime($event_retrieved->getStart()->getDate()));
            $this->data->setEnd(strtotime($event_retrieved->getEnd()->getDate()));
        }
    }

    /**
     * Checks if the google calendar event is still available
     *
     * @param Google_Service_Calendar_Event $event_retrieved
     *
     * @return boolean
     */
    private function isEventStillAvailable(Google_Service_Calendar_Event $event_retrieved)
    {
        if ($this->service->getClass() === 'appointment' && $this->isSlotBooked($event_retrieved)) {
            return FALSE;
        }
        if ($event_retrieved->getStatus() === 'cancelled') {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Uses a rule to check if an Appointment slot is booked or not
     *
     * @param Google_Service_Calendar_Event $slot
     *
     * @return bool
     */
    private function isSlotBooked($slot)
    {
        // The new rule
        if (\TeamBooking\Parser::parseSummary($slot->getSummary()) == strtolower(static::$settings->getCoworkerData($this->coworker->getId())->getCustomEventSettings($this->service->getId())->getAfterBookedTitle())) {
            return TRUE;
        } else {
            return FALSE;
        }
        // The old rule
        #if (isGoogleEventFromThisSource($slot)) {
        #return TRUE;
        #} else {
        #return FALSE;
        #}
    }

    /**
     * Checks if the customer has already booked
     *
     * @param Google_Service_Calendar_Event $event_retrieved
     *
     * @return boolean
     */
    private function hasCustomerAlreadyBooked(Google_Service_Calendar_Event $event_retrieved)
    {
        foreach ($this->reservations as $reservation) {
            /* @var $reservation TeamBooking_ReservationData */
            if ($reservation->isConfirmed()
                && $reservation->getGoogleCalendarEvent() == $event_retrieved->getId()
                && $reservation->getDatabaseId() !== $this->data->getDatabaseId()
            ) {
                if ($this->data->getCustomerUserId() > 0 && $reservation->getCustomerUserId() === $this->data->getCustomerUserId()) {
                    // logged user match
                    return TRUE;
                }
                if ($this->data->getCustomerUserId() === 0 && $reservation->getCustomerEmail() === $this->data->getCustomerEmail()) {
                    // guest e-mail match
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    /**
     * Sends the notification email to the coworker
     *
     * @return boolean
     */
    public function sendNotificationEmailToCoworker()
    {
        $body = Toolkit\findAndReplaceHooks($this->coworker_service_data->getNotificationEmailBody(), $this->data->getHooksArray());
        if ($this->data->getEventHtmlLink()) {
            $body .= '<br>' . esc_html__('Event link', 'team-booking') . ': ' . $this->data->getEventHtmlLink();
        }
        $email = new TeamBooking\EmailHandler();
        $email->setSubject(Toolkit\findAndReplaceHooks($this->coworker_service_data->getNotificationEmailSubject(), $this->data->getHooksArray()));
        $email->setBody($body); // unhooked
        $sender = $this->prepareSender($this->data->getFieldsArray());
        $email->setFrom($this->service->getEmailToAdmin('to'), $sender['name']);
        $email->setReplyTo($sender['from']);
        $email->setTo($this->coworker->getEmail());
        $files_references = $this->data->getFilesReferences();
        if (!empty($files_references) && $this->coworker_service_data->getIncludeFilesAsAttachment()) {
            foreach ($files_references as $file_reference) {
                $email->setAttachment($file_reference['file']);
            }
        }

        // Hook for custom actions
        Functions\reservation_email_to('coworker', $email, $this->data);

        // Check if we should send the e-mail
        if (!isset($email->stop) || !$email->stop) {
            $email->send();
        }

        return TRUE;
    }

    /**
     * Sends the confirmation email to customer
     *
     * @return boolean
     */
    public function sendConfirmationEmail()
    {
        $email = new TeamBooking\EmailHandler();
        $subject = Toolkit\findAndReplaceHooks($this->service->getEmailToCustomer('subject'), $this->data->getHooksArray(TRUE));
        $body = Toolkit\findAndReplaceHooks($this->service->getEmailToCustomer('body'), $this->data->getHooksArray(TRUE));
        // Set data
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setFrom($this->service->getEmailToAdmin('to'), get_bloginfo('name'));
        $email->setTo($this->data->getCustomerEmail());

        // Hook for custom actions
        Functions\reservation_email_to('customer', $email, $this->data);

        // Check if we should send the e-mail
        if (!isset($email->stop) || !$email->stop) {
            $email->send();
        }

        return TRUE;
    }

    /**
     * Sends the notification email to admin
     *
     * @return boolean
     */
    public function sendNotificationEmail()
    {
        $email = new TeamBooking\EmailHandler();
        $subject = Toolkit\findAndReplaceHooks($this->service->getEmailToAdmin('subject'), $this->data->getHooksArray());
        $body = Toolkit\findAndReplaceHooks($this->service->getEmailToAdmin('body'), $this->data->getHooksArray());
        // Set data
        $email->setSubject($subject);
        $email->setBody($body);
        $sender = $this->prepareSender($this->data->getFieldsArray());
        $email->setFrom($this->service->getEmailToAdmin('to'), $sender['name']);
        $email->setReplyTo($sender['from']);
        $email->setTo($this->service->getEmailToAdmin('to'));
        $files_references = $this->data->getFilesReferences();
        if (!empty($files_references) && $this->service->getEmailToAdmin('attachments')) {
            foreach ($files_references as $file_reference) {
                $email->setAttachment($file_reference['file']);
            }
        }

        // Hook for custom actions
        Functions\reservation_email_to('admin', $email, $this->data);

        // Check if we should send the e-mail
        if (!isset($email->stop) || !$email->stop) {
            $email->send();
        }

        return TRUE;
    }

    /**
     * Send the cancellation email to customer
     *
     * @return boolean
     */
    public function sendCancellationEmail()
    {
        $email = new TeamBooking\EmailHandler();
        $email->setFrom($this->service->getEmailToAdmin('to'), get_option('blogname'));
        $subject = Toolkit\findAndReplaceHooks($this->service->getEmailCancellationToCustomer('subject'), $this->data->getHooksArray(TRUE));
        $body = Toolkit\findAndReplaceHooks($this->service->getEmailCancellationToCustomer('body'), $this->data->getHooksArray(TRUE));
        $email->setTo($this->data->getCustomerEmail());
        $email->setSubject($subject);
        $email->setBody($body);
        $email->send();

        return TRUE;
    }

    /**
     * Send the cancellation email to admin/coworker
     *
     * @return boolean
     */
    public function sendCancellationEmailBackend($who)
    {
        $email = new TeamBooking\EmailHandler();
        if (is_numeric($who)
            && $this->data->getCustomerEmail()
            && $this->data->getCoworker() !== $who
            && !user_can($who, 'manage_options')
        ) {
            $email->setReplyTo($this->data->getCustomerEmail(), $this->data->getCustomerDisplayName());
        }
        $email->setFrom(get_bloginfo('admin_email'), get_option('blogname'));
        $subject = Toolkit\findAndReplaceHooks($this->service->getEmailCancellationToAdmin('subject'), $this->data->getHooksArray(TRUE));
        $body = Toolkit\findAndReplaceHooks($this->service->getEmailCancellationToAdmin('body'), $this->data->getHooksArray(TRUE));
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setTo($this->coworker->getEmail());
        $email->setBcc($this->service->getEmailToAdmin('to'), get_option('blogname'));
        $email->send();

        return TRUE;
    }

    /**
     * Prepares the confirmation email sender
     *
     * @param mixed $fields
     *
     * @return string
     */
    private function prepareSender($fields)
    {
        $results = array(
            'from' => NULL,
            'name' => NULL,
        );
        if (isset($fields['email'])) {
            $results['from'] = $fields['email'];
        } else {
            $results['from'] = get_bloginfo('admin_email');
        }
        if (isset($fields['first_name'])) {
            $results['name'] .= $fields['first_name'];
        }
        if (isset($fields['second_name'])) {
            $results['name'] .= ' ' . $fields['second_name'];
        }
        if (NULL === $results['name']) {
            $results['name'] = get_bloginfo('name');
        }

        return $results;
    }

    /**
     * @return Teambooking\Services\Appointment|Teambooking\Services\Event|Teambooking\Services\Unscheduled
     */
    public function getServiceObj()
    {
        return $this->service;
    }

    /**
     * Coworker assignation rules
     *
     * TODO: at the moment, the coworkers specified in the shortcode will be ignored
     * for unscheduled events: allow this
     *
     * @param string $service_id
     *
     * @return int
     */
    public static function chooseCoworker($service_id)
    {
        $rule = Database\Services::get($service_id)->getSettingsFor('assignment_rule');
        if (empty($rule)) {
            $rule = 'equal';
        }
        $coworkers = Functions\getCoworkersIdList();
        foreach ($coworkers as $key => $coworker_id) {
            if (!Functions\getSettings()->getCoworkerData($coworker_id)->isServiceAllowed($service_id)) {
                unset($coworkers[ $key ]);
            }
        }
        if ($rule === 'equal') {
            // TODO: add timespan RULE!!!!!!!
            $reservations = Database\Reservations::getByService($service_id);
            foreach ($coworkers as $coworker_id) {
                $coworkers_equalizer[ $coworker_id ] = 0;
            }
            foreach ($reservations as $reservation) {
                /* @var $reservation TeamBooking_ReservationData */
                if ($reservation->isCancelled()) continue;
                if (isset($coworkers_equalizer[ $reservation->getCoworker() ])) {
                    $coworkers_equalizer[ $reservation->getCoworker() ]++;
                }
            }
            asort($coworkers_equalizer);
            // Pick the first
            reset($coworkers_equalizer);
            $coworker_choosen = key($coworkers_equalizer);
        } elseif ($rule === 'random') {
            $random_key = array_rand($coworkers);
            $coworker_choosen = $coworkers[ $random_key ];
        } elseif ($rule === 'direct') {
            $coworker_choosen = Database\Services::get($service_id)->getDirectCoworkerId();
        }
        if (empty($coworker_choosen)) {
            $random_key = array_rand($coworkers);
            $coworker_choosen = $coworkers[ $random_key ];
        }

        return $coworker_choosen;
    }

}
