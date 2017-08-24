<?php

namespace TeamBooking;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Customer
 *
 * @author VonStroheim
 */
class Customer
{
    private $email;
    private $name;
    private $wp_user_id;
    private $reservations;
    private $reservations_obj;

    /**
     * Customer constructor.
     *
     * @param \WP_User $user
     * @param array    $reservations
     */
    public function __construct(\WP_User $user, array $reservations)
    {
        /** @var $reservations \TeamBooking_ReservationData[] */
        $this->email = $user->user_email;
        $this->name = $user->display_name;
        if (isset($user->ID)) {
            $this->wp_user_id = $user->ID;
        }
        $this->reservations = array();
        foreach ($reservations as $reservation) {
            if (isset($this->reservations[ $reservation->getServiceId() ])) {
                $this->reservations[ $reservation->getServiceId() ]++;
            } else {
                $this->reservations[ $reservation->getServiceId() ] = 1;
            }
            $this->reservations_obj[ $reservation->getServiceId() ][] = $reservation;
        }
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->wp_user_id;
    }

    /**
     * @param null $service_id
     *
     * @return array|int|mixed
     */
    public function getReservations($service_id = NULL)
    {
        if (NULL !== $service_id) {
            if (isset($this->reservations[ $service_id ])) {
                return $this->reservations[ $service_id ];
            } else {
                return 0;
            }
        } else {
            return $this->reservations;
        }
    }

    /**
     * @param $service_id
     *
     * @return int
     */
    public function getEnumerableReservations($service_id)
    {
        $num = 0;
        if (isset($this->reservations_obj[ $service_id ])) {
            foreach ($this->reservations_obj[ $service_id ] as $reservation) {
                /** @var \TeamBooking_ReservationData $reservation */
                if ($reservation->isEnumerableForCustomerLimits()) $num++;
            }
        }

        return $num;
    }

    /**
     * @return mixed
     */
    public function getTotalReservations()
    {
        return array_sum($this->reservations);
    }
}