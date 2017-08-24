<?php

namespace TeamBooking\Admin\Framework;

class PanelSaveButton implements Element
{
    protected $name;
    protected $value;

    public function __construct($name, $value)
    {
        $this->name = esc_html($name);
        $this->value = $value;
    }

    public function render()
    {
        submit_button($this->name, 'primary', $this->value);
    }

    public function appendTo(Panel &$panel)
    {
        $panel->addElement($this);
    }
}