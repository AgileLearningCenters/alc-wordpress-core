<?php

namespace TeamBooking\Frontend\Components;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class FormMap
 *
 * @author VonStroheim
 */
class FormMap
{
    /**
     * @param string $location
     * @param bool   $show_map
     *
     * @return string
     */
    public static function get($location, $show_map = TRUE)
    {
        ob_start();
        FormMap::render($location, $show_map);

        return ob_get_clean();
    }

    /**
     * @param string $location
     * @param bool   $show_map
     */
    public static function render($location, $show_map = TRUE)
    {
        ?>
        <div class="ui horizontal tbk-divider tbk-map" style="display:none">
            <i class="marker tb-icon"></i>
        </div>
        <div class="tbk-map tbk-address" style="display:none">
            <?= $location ?>
            <a class="tbk-get-directions" target="_blank"
               href="//maps.google.com?daddr=<?= $location ?>">
                (<?= esc_html__('get directions', 'team-booking') ?>)
            </a>
        </div>
        <?php if ($show_map) { ?>
        <div class="tbk-segment tbk-map tbk-map-canvas"
             data-zoom="<?= \TeamBooking\Functions\getSettings()->getGmapsZoomLevel() ?>"
             style="display:none">
        </div>
        <?php
    }
    }
}