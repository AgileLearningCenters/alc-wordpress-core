<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Database\eventObject,
    TeamBooking\Functions;

/**
 * Class Slot
 *
 * @author VonStroheim
 */
class Slot
{
    ///////////////////
    // service data  //
    ///////////////////
    private $service_id;
    private $service_name;
    private $service_info;
    ///////////////////////
    // Google event data //
    ///////////////////////
    private $start;
    private $end;
    private $event_id;
    private $calendar_id;
    private $container;
    private $multiple_services;
    private $attendees_number = 0;
    private $allday;
    private $event_id_parent;
    private $read_only;
    ////////////////
    // slot data  //
    ////////////////
    private $coworker_id;
    private $soldout;
    private $location;
    private $from_reservation_id;
    private $timezone;
    private $customers;

    /**
     * Returns the Google Calendar ID in which
     * the event is.
     *
     * @return string
     */
    public function getCalendarId()
    {
        return $this->calendar_id;
    }

    /**
     * Sets the Google Calendar ID in which
     * the event is.
     *
     * @param string $id
     */
    public function setCalendarId($id)
    {
        $this->calendar_id = $id;
    }

    /**
     * Returns the services array in case of a multiple
     * service container event.
     *
     * @return array
     */
    public function getMultipleServices()
    {
        if (is_array($this->multiple_services)) {
            return $this->multiple_services;
        } else {
            return array();
        }
    }

    /**
     * Sets the services array in case of a multiple
     * service container event.
     *
     * @param array $services
     */
    public function setMultipleServices(array $services)
    {
        $this->multiple_services = $services;
    }

    /**
     * Returns the service name of the slot
     *
     * @return array
     */
    public function getServiceName()
    {
        return $this->service_name;
    }

    /**
     * Sets the service name of the slot
     *
     * @param string $name
     */
    public function setServiceName($name)
    {
        $this->service_name = $name;
    }

    /**
     * Returns the service description
     *
     * @return string
     */
    public function getServiceInfo()
    {
        return $this->service_info;
    }

    /**
     * Sets the service description
     *
     * @param string $info
     */
    public function setServiceInfo($info)
    {
        $this->service_info = $info;
    }

    /**
     * Returns the service id of the slot
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Sets the service id of the slot
     *
     * @param string $id
     */
    public function setServiceId($id)
    {
        $this->service_id = $id;
    }

    /**
     * Returns the location of the slot
     *
     * @return string
     */
    public function getLocation()
    {
        return trim($this->location);
    }

    /**
     * Sets the location of the slot
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Sets the reservation database id, if the slot is created from it
     *
     * @param $id
     */
    public function setFromReservation($id)
    {
        $this->from_reservation_id = $id;
    }

    /**
     * Returns the reservation database id, if the slot was created from it
     *
     * @return int|NULL
     */
    public function getFromReservation()
    {
        return $this->from_reservation_id;
    }

    /**
     * Sets the start time of the slot
     *
     * @param string $time is RFC3339 or y-mm-dd
     */
    public function setStartTime($time)
    {
        $this->start = $time;
    }

    /**
     * Returns the start time of the slot
     *
     * @return string is RFC3339 or y-mm-dd
     */
    public function getStartTime()
    {
        return $this->start;
    }

    /**
     * Sets the end time of the slot
     *
     * @param string $time is RFC3339 or y-mm-dd
     */
    public function setEndTime($time)
    {
        $this->end = $time;
    }

    /**
     * Returns the end time of the slot
     *
     * @return string is RFC3339 or y-mm-dd
     */
    public function getEndTime()
    {
        return $this->end;
    }

    /**
     * Returns the Google event id
     *
     * @return string
     */
    public function getEventId()
    {
        return $this->event_id;
    }

    /**
     * Sets the Google event id
     *
     * @param string $id
     */
    public function setEventId($id)
    {
        $this->event_id = $id;
    }

    /**
     * Returns the Google parent event id, if any
     *
     * @return string
     */
    public function getEventIdParent()
    {
        return isset($this->event_id_parent) ? $this->event_id_parent : FALSE;
    }

    /**
     * Sets the Google parent event id, if any
     *
     * @param string $id
     */
    public function setEventIdParent($id)
    {
        $this->event_id_parent = $id;
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return (bool)$this->read_only;
    }

    /**
     * @param $bool
     */
    public function setReadOnly($bool)
    {
        $this->read_only = (bool)$bool;
    }

    /**
     * Returns the coworker id
     *
     * @return int
     */
    public function getCoworkerId()
    {
        return $this->coworker_id;
    }

    /**
     * Sets the coworker id
     *
     * @param int $id
     */
    public function setCoworkerId($id)
    {
        $this->coworker_id = $id;
    }

    /**
     * Returns the number of attendees for that slot
     *
     * @return int
     */
    public function getAttendeesNumber()
    {
        return $this->attendees_number;
    }

    /**
     * Sets the number of attendees for that slot
     *
     * @param int $number
     */
    public function setAttendeesNumber($number)
    {
        $this->attendees_number = $number;
    }

    /**
     * Sets the slot as all-day or not
     *
     * @param $bool
     */
    public function setAllDay($bool)
    {
        $this->allday = (bool)$bool;
    }

    /**
     * Checks if the slot is of All Day type
     *
     * @return boolean
     */
    public function isAllDay()
    {
        return (bool)$this->allday;
    }

    /**
     * Sets the slot as Container-derived or not
     *
     * @param $boolean
     */
    public function setContainer($boolean)
    {
        $this->container = (bool)$boolean;
    }

    /**
     * Checks if the slot is derived from a Container
     *
     * @return boolean
     */
    public function isContainer()
    {
        return (bool)$this->container;
    }

    /**
     * Sets the slot as soldout/booked
     */
    public function setSoldout()
    {
        $this->soldout = TRUE;
    }

    /**
     * Checks if the slot is soldout/booked
     *
     * @return boolean
     */
    public function isSoldout()
    {
        return (bool)$this->soldout;
    }

    /**
     * Sets the timezone of the slot
     *
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Returns the timezone of the slot
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param array $customers
     */
    public function setCustomers(array $customers)
    {
        $this->customers = $customers;
    }

    /**
     * @param array $customer
     */
    public function addCustomer(array $customer)
    {
        $this->customers[] = $customer;
    }

    /**
     * @return array
     */
    public function getCustomers()
    {
        return (array)$this->customers;
    }

    /**
     * @return string
     */
    public function getCoworkerDisplayString()
    {
        $coworker = Functions\getSettings()->getCoworkerData($this->getCoworkerId());
        try {
            $service = Database\Services::get($this->getServiceId());
            if (!$service->getSettingsFor('show_coworker')) {
                return '';
            }
            if ($service->getSettingsFor('show_coworker_url')) {
                return '<a href="' . Functions\getSettings()->getCoworkerUrl($this->getCoworkerId()) . '" target="_blank">' . $coworker->getFullName() . '</a>';
            }
        } catch (\Exception $e) {
            return '';
        }

        return $coworker->getFullName();
    }

    /**
     * @param string $format
     * @param string $start_or_end
     *
     * @return string
     */
    public function getDateFormatted($format, $start_or_end = 'start')
    {
        $start_date_time_object = new \DateTime($this->getStartTime(), new \DateTimeZone($this->getTimezone()));
        if (NULL !== $this->getTimezone()) $start_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        $end_date_time_object = new \DateTime($this->getEndTime(), new \DateTimeZone($this->getTimezone()));
        if (NULL !== $this->getTimezone()) $end_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        if ($start_or_end === 'start') {
            return $start_date_time_object->format($format);
        } else {
            return $end_date_time_object->format($format);
        }
    }

    /**
     * @param bool $start_only
     *
     * @return string
     */
    public function getDateString($start_only = TRUE)
    {
        $date_format = get_option('date_format');
        $start_date_time_object = new \DateTime($this->getStartTime());
        if (NULL !== $this->getTimezone()) $start_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        $end_date_time_object = new \DateTime($this->getEndTime());
        if (NULL !== $this->getTimezone()) $end_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        if ($this->isAllDay()) {
            return esc_html__('All day', 'team-booking');
        } else {
            if ($start_only) {
                return $start_date_time_object->format($date_format);
            } else {
                return $start_date_time_object->format($date_format) . ' - ' . $end_date_time_object->format($date_format);
            }
        }
    }

    /**
     * @return null|string
     */
    public function getTimesString()
    {
        $time_format = get_option('time_format');
        $start_date_time_object = new \DateTime($this->getStartTime());
        if (NULL !== $this->getTimezone()) $start_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        $end_date_time_object = new \DateTime($this->getEndTime());
        if (NULL !== $this->getTimezone()) $end_date_time_object->setTimezone(new \DateTimeZone($this->getTimezone()));
        try {
            $service = Database\Services::get($this->getServiceId());
            switch ($service->getSettingsFor('show_times')) {
                case 'yes':
                    if ($this->isAllDay()) {
                        return esc_html__('All day', 'team-booking');
                    } else {
                        return $start_date_time_object->format($time_format) . ' - ' . $end_date_time_object->format($time_format);
                    }
                case 'no' :
                    return NULL;
                case 'start_time_only':
                    if ($this->isAllDay()) {
                        return esc_html__('All day', 'team-booking');
                    } else {
                        return $start_date_time_object->format($time_format);
                    }
                default:
                    return NULL;
            }
        } catch (\Exception $e) {
            return NULL;
        }
    }

    /**
     * @param eventObject $event
     * @param             $service_id
     * @param             $coworker_id
     * @param array       $services_array
     *
     * @return Slot
     */
    public static function getContainerFromEvent(eventObject $event, $service_id, $coworker_id, array $services_array = array())
    {
        $slot = Slot::getFromEvent($event, $service_id, $coworker_id);
        $slot->setContainer(TRUE);
        if (count($services_array) > 1) {
            $slot->setMultipleServices(array_map('strtolower', $services_array));
        }

        return $slot;
    }

    /**
     * @param eventObject $event
     * @param             $service_id
     * @param             $coworker_id
     *
     * @return Slot
     */
    public static function getSoldoutFromEvent(eventObject $event, $service_id, $coworker_id)
    {
        $slot = Slot::getFromEvent($event, $service_id, $coworker_id);
        $slot->setSoldout();

        return $slot;
    }

    /**
     * @param eventObject $event
     * @param             $service_id
     * @param             $coworker_id
     *
     * @return Slot
     * @throws \Exception
     */
    public static function getFromEvent(eventObject $event, $service_id, $coworker_id)
    {
        $slot = new Slot();

        // Check for All Day event and set start/end time
        #$slot->setTimezone($event->getStart()->getTimezone());
        $slot->setAllDay($event->allday);
        $slot->setEndTime($event->end);
        $slot->setStartTime($event->start);
        // Sets the slot's location
        $location_setting = Database\Services::get($service_id)->getSettingsFor('location');
        if ($location_setting === 'inherited') {
            $slot->setLocation($event->location);
        } elseif ($location_setting === 'fixed') {
            $slot->setLocation(Database\Services::get($service_id)->getLocation());
        } else {
            $slot->setLocation(''); //empty
        }
        $slot->setCoworkerId($coworker_id);
        $slot->setServiceId($service_id);
        $slot->setEventId($event->id);
        $slot->setCalendarId($event->organizer);
        $slot->setServiceName(Database\Services::get($service_id)->getName());
        $slot->setServiceInfo(Database\Services::get($service_id)->getDescription());
        $slot->setReadOnly(Functions\tb_mb_strtolower($event->creator) !== Functions\tb_mb_strtolower(Functions\getSettings()->getCoworkerData($coworker_id)->getAuthAccount()));

        return $slot;
    }

    /**
     * @return array
     */
    public function getApiResource()
    {
        $return = array(
            'type'               => 'slot',
            'serviceID'          => $this->getServiceId(),
            'serviceName'        => $this->getServiceName(),
            'serviceDescription' => $this->getServiceInfo(),
            'location'           => $this->getLocation(),
            'coworkerID'         => $this->getCoworkerId(),
            'isSoldout'          => $this->isSoldout(),
            'isAllday'           => $this->isAllDay(),
            'tickets'            => $this->getAttendeesNumber(),
            'start'              => $this->getStartTime(),
            'end'                => $this->getEndTime(),
            'gcalEvent'          => $this->getEventId(),
            'gcalParentEvent'    => $this->getEventIdParent(),
            'gcalID'             => $this->getCalendarId(),
        );

        return $return;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->event_id . $this->calendar_id . $this->service_id . $this->event_id_parent . $this->start . $this->end;
    }

}
