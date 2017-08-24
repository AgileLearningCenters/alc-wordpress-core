<?php

namespace TeamBooking\Frontend\Components;
defined('ABSPATH') or die('No script kiddies please!');

class FormServiceDescription
{
    public static function get($description)
    {
        ob_start();
        FormServiceDescription::render($description);

        return ob_get_clean();
    }

    public static function render($description)
    {
        ?>
        <div class="tbk-service-description">
            <p><?= $description ?></p>
        </div>
        <?php
    }
}