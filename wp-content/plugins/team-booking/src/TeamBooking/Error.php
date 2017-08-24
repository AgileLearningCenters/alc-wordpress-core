<?php

defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class defines an error object used to return errors
 * to customer/frontend when a reservation fails.
 *
 * @author VonStroheim
 *
 * TODO: join TeamBooking_Error with TeamBooking_ErrorLog
 */
class TeamBooking_Error
{
    private $code;
    private $message;

    public function __construct($code)
    {
        $this->code = $code;
        switch ($code) {
            case 1:
                $this->message = 'The Coworker auth token is not present anymore';
                break;
            case 2:
                $this->message = 'Slot not available anymore or cancelled';
                break;
            case 3:
                $this->message = 'The customer has already booked the slot';
                break;
            case 4:
                $this->message = 'The customer has entered an invalid email address';
                break;
            case 5:
                $this->message = 'Generic Google API error'; // More specific with error message returned by Google
                break;
            case 6:
                $this->message = 'The event is full';
                break;
            case 7:
                $this->message = 'The event is already cancelled or not marked as booked anymore';
                break;
            case 8:
                $this->message = 'The customer is trying to book a number of tickets that, together with his previous tickets, overcomes the maximum allowed';
                break;
            case 9:
                $this->message = "Can't decode the rendering parameters object";
                break;
            case 10:
                $this->message = 'Operation not allowed for this service class';
                break;
            case 50:
                $this->message = 'Reservation data cannot be read';
                break;
            case 51:
                $this->message = 'Service nonexistent';
                break;
            case 52:
                $this->message = 'Reservation already confirmed';
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

}
