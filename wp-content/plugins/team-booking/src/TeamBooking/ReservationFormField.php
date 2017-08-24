<?php

defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class is more handy than the setting's form classes
 * to just transport values for reservation purposes
 *
 * @author VonStroheim
 */
class TeamBooking_ReservationFormField
{
    private $value;
    private $label;
    private $name;
    private $price_increment;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getPriceIncrement()
    {
        if (!$this->price_increment) {
            return 0;
        } else {
            return $this->price_increment;
        }
    }

    /**
     * @param $float
     */
    public function setPriceIncrement($float)
    {
        $this->price_increment = (float)$float;
    }

}
