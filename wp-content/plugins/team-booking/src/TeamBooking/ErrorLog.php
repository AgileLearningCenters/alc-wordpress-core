<?php

defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class defines an error object used to log API errors
 *
 * @author VonStroheim
 */
class TeamBooking_ErrorLog
{
    private $error_code;
    private $message;
    private $timestamp;
    private $coworker_id;
    private $calendar_id;

    /**
     * @return string
     */
    public function getMessage()
    {
        if ($this->error_code == 502) {
            return 'Error 502 (Server Error)!!'; // AVOID strange scramblin'css
        } else {
            return $this->message;
        }
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @param $code
     */
    public function setErrorCode($code)
    {
        $this->error_code = $code;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        switch ($this->error_code) {
            case 404:
                // TODO: remove (it should be useless as of v1.4.0)
                $return = 'The Calendar ID provided by Coworker is neither existant nor belongs to his authorized Google Account';
                break;
            case 500:
                $return = "A Google internal temporary server error, don't worry too much about it";
                break;
            case 502:
                $return = 'The server encountered a temporary error and could not complete the request. Just a temporary error.';
                break;
            default :
                $return = __('No description available', 'team-booking');
                break;
        }

        return $return;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getCoworkerId()
    {
        return $this->coworker_id;
    }

    public function setCoworkerId($id)
    {
        $this->coworker_id = $id;
    }

    /**
     * @return string
     */
    public function getCoworker()
    {
        $coworker = new TeamBookingCoworker($this->coworker_id);

        return $coworker->getFirstName() . ' ' . $coworker->getLastName();
    }

    /**
     * @return null
     */
    public function getCalendarId()
    {
        if (isset($this->calendar_id)) {
            return $this->calendar_id;
        } else {
            return NULL;
        }
    }

    /**
     * @param $id
     */
    public function setCalendarId($id)
    {
        $this->calendar_id = $id;
    }

}
