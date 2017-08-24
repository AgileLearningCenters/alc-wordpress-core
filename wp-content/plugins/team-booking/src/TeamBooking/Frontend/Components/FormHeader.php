<?php

namespace TeamBooking\Frontend\Components;
defined('ABSPATH') or die('No script kiddies please!');

class FormHeader
{
    /**
     * @param string $name
     * @param string $times
     * @param string $coworker
     *
     * @return mixed
     */
    public static function get($name, $times, $coworker)
    {
        ob_start();
        ?>
        <div class="tbk-reservation-form-header">
            <div class="tbk-title">
                <span class="tbk-thin-italic"><?= esc_html__('Reservation for', 'team-booking') ?></span>
                <?= esc_html($name) ?>
            </div>
            <div class="tbk-meta" style="white-space: normal;">
                <!-- times string -->
                <?php if (!empty($times)) { ?>
                    <i class="wait tb-icon"></i>
                    <div class="tbk-reservation-form-header-times">
                        <?= $times ?>
                    </div>
                <?php } ?>
                <!-- coworker string -->
                <?php if (!empty($coworker)) { ?>
                    <div class="tbk-reservation-form-header-coworker">
                        <i class="user tb-icon"></i>
                        <?= $coworker ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}