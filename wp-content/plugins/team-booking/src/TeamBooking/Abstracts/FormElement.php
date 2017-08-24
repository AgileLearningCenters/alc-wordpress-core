<?php

namespace TeamBooking\Abstracts;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Toolkit;

/**
 * Abstract FormElement Class
 *
 * Implemented by form elements
 *
 * @since    2.2.0
 * @author   VonStroheim
 */
abstract class FormElement
{
    /**
     * Type of this element
     *
     * @var string
     */
    protected $type = '';

    /**
     * Hook of this element
     *
     * @var string
     */
    protected $hook = '';

    /**
     * Title of this element
     *
     * @var string
     */
    protected $title = '';

    /**
     * Description of this element
     *
     * @var string
     */
    protected $description = '';

    /**
     * Other data of this element
     *
     * @var array
     */
    protected $data = array();

    /**
     * Visibility of this element
     *
     * @var boolean
     */
    protected $visible = TRUE;

    /**
     * If the element must be hidden in the form
     *
     * @var boolean
     */
    protected $hidden = FALSE;

    /**
     * Requirement of this element
     *
     * @var boolean
     */
    protected $required = FALSE;

    public function __construct()
    {
        $this->type = $this->getType();
    }

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @param bool $hidden
     *
     * @return string
     */
    abstract public function getMarkup($hidden = FALSE);

    /**
     * @return array
     */
    public function getProperties()
    {
        return get_object_vars($this);
    }

    /**
     * Change the visibility of this element
     *
     * @param boolean $bool
     */
    public function setVisible($bool)
    {
        $this->visible = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Set the element as hidden or not
     *
     * @param boolean $bool
     */
    public function setHidden($bool)
    {
        $this->hidden = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * Change the requirement for this element
     *
     * @param boolean $bool
     */
    public function setRequired($bool)
    {
        $this->required = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Set the hook of this element
     *
     * @param string $text
     */
    public function setHook($text)
    {
        $this->hook = Toolkit\filterInput($text, TRUE);
    }

    /**
     * @return string
     */
    public function getHook()
    {
        return $this->hook;
    }

    /**
     * Set the title of this element
     *
     * @param string $text
     */
    public function setTitle($text)
    {
        $this->title = Toolkit\filterInput($text);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->wrapStringForTranslations(Toolkit\unfilterInput($this->title));
    }

    /**
     * Set the description of this element
     *
     * @param string $text
     */
    public function setDescription($text)
    {
        $this->description = Toolkit\filterInput($text);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->wrapStringForTranslations(Toolkit\unfilterInput($this->description), 'description');
    }

    /**
     * Set additional data of this element
     *
     * @param string $property
     * @param mixed  $value
     */
    public function setData($property, $value)
    {
        $this->data[ $property ] = $value;
    }

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function getData($property)
    {
        return isset($this->data[ $property ]) ? $this->data[ $property ] : NULL;
    }

    /**
     * Handles the translation of the text
     *
     * @param string $text
     * @param string $what
     *
     * @return string
     */
    protected function wrapStringForTranslations($text, $what = 'title')
    {
        $builtin_titles = array(
            'first_name'  => __('First name', 'team-booking'),
            'second_name' => __('Last name', 'team-booking'),
            'email'       => __('Email', 'team-booking'),
            'address'     => __('Address', 'team-booking'),
            'phone'       => __('Phone number', 'team-booking'),
            'url'         => __('Website', 'team-booking'),
        );

        if ($what === 'title' && isset($builtin_titles[ $this->hook ])) {
            return $builtin_titles[ $this->hook ];
        } else {
            // TODO
            return $text;
        }
    }

}