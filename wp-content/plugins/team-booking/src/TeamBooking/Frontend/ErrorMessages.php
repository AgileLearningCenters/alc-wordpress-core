<?php

namespace TeamBooking\Frontend;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class ErrorMessages
 *
 * @author VonStroheim
 */
class ErrorMessages
{
    private function basicTemplate($string)
    {
        ob_start();
        ?>
        <?= Components\NavigationHeader::InError() ?>
        <div class="tbk-slide-body">
            <div class="ui error message">
                <div class="tbk-header">
                    <?= __('Cannot make the reservation!', 'team-booking') ?>
                </div>
                <p><?= $string ?></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function invalidAttendeeEmail()
    {
        $string = __('Your email is invalid, please check it and retry!', 'team-booking');

        return $this->basicTemplate($string);
    }

    /**
     * @return string
     */
    public function alreadyBooked()
    {
        $string = __('You have already booked this...', 'team-booking');

        return $this->basicTemplate($string);
    }

    /**
     * @return string
     */
    public function eventFull()
    {
        $string = __('Sorry, this service is full! Maybe someone booked the last slot right now, just a moment before you :(', 'team-booking');

        return $this->basicTemplate($string);
    }

    /**
     * @return string
     */
    public function eventNotAvailableAnymore()
    {
        $string = __('Sorry, this slot is not available anymore. Probably it was booked by someone else or cancelled a while ago, just before you :(', 'team-booking');

        return $this->basicTemplate($string);
    }

    /**
     * @return string
     */
    public function coworkersRevokedAuth()
    {
        $string = __('Sorry, you should contact the administrator', 'team-booking');

        return $this->basicTemplate($string);
    }

    /**
     * @param string $error_message
     *
     * @return string
     */
    public function genericGoogleApiError($error_message)
    {
        $string = __('Sorry, you should contact the administrator providing these informations:', 'team-booking') . ' ' . $error_message;

        return $this->basicTemplate($string);
    }

    /**
     * @param string $error_message
     *
     * @return string
     */
    public function customerMaxCumulativeTicketsOvercome($error_message = '')
    {
        $string = __('Sorry, you chose a number of tickets that makes you exceed the maximum allowed per-customer. Please retry with a lower number!', 'team-booking') . ' ' . $error_message;

        return $this->basicTemplate($string);
    }

}
