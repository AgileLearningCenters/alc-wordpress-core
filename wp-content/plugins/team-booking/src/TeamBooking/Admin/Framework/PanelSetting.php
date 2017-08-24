<?php

namespace TeamBooking\Admin\Framework;

class PanelSetting
{
    protected $title;
    protected $description = '';
    protected $fieldname = '';
    protected $alert = '';
    protected $alert_dropcap = 'Note';
    protected $notice = '';

    public function __construct($title = '')
    {
        $this->title = esc_html($title);
    }

    public function addDescription($string, $escape = TRUE)
    {
        if ($escape) {
            $this->description = esc_html($string);
        } else {
            $this->description = $string;
        }
    }

    public function addAlert($text)
    {
        $this->alert = $text;
    }

    public function addAlertDropcap($text)
    {
        $this->alert_dropcap = $text;
    }

    public function addNotice($text)
    {
        $this->notice = $text;
    }

    public function addToDescription($string, $escape = TRUE)
    {
        if ($escape) {
            $this->description .= esc_html($string);
        } else {
            $this->description .= $string;
        }
    }

    public function addFieldname($fieldname)
    {
        $this->fieldname = $fieldname;
    }

    public function appendTo(Panel &$panel)
    {
        $panel->addElement($this);
    }
}