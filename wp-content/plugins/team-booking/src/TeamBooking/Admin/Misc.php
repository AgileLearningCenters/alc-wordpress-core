<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Misc
 *
 * @author VonStroheim
 */
class Misc
{
    /**
     * @var Misc
     */
    private static $_instance;

    /**
     * @return Misc
     */
    public static function render()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Generate the tab wrapper for admin page
     *
     * @param string $active_tab
     */
    public function getTabWrapper($active_tab)
    {
        $header = new Framework\Header();
        $header->setPluginData(TEAMBOOKING_FILE_PATH);
        $header->setMainText('Hi, ' . wp_get_current_user()->user_firstname);
        $header->addTab('overview', __('Overview', 'team-booking'), 'dashicons-chart-area', $active_tab === 'overview');
        $header->addTab('slots', __('Slots', 'team-booking'), 'dashicons-calendar-alt', $active_tab === 'slots');
        $header->addTab('events', __('Services', 'team-booking'), 'dashicons-clipboard', $active_tab === 'events');
        $header->addTab('coworkers', __('Coworkers', 'team-booking'), 'dashicons-groups', $active_tab === 'coworkers');
        $header->addTab('customers', __('Customers', 'team-booking'), 'dashicons-id', $active_tab === 'customers');
        $header->addTab('personal', __('Personal', 'team-booking'), 'dashicons-businessman', $active_tab === 'personal');
        $header->addTab('aspect', __('Frontend style', 'team-booking'), 'dashicons-art', $active_tab === 'aspect');
        $header->addTab('general', __('Core settings', 'team-booking'), 'dashicons-admin-generic', $active_tab === 'general');
        $header->addTab('payments', __('Payment gateways', 'team-booking'), 'dashicons-cart', $active_tab === 'payments');
        $header->addTab('pricing', __('Promotions', 'team-booking'), 'dashicons-money', $active_tab === 'pricing');
        $header->render();
    }

    /**
     * Generate the tab wrapper for admin page (coworker)
     *
     * @param string $active_tab
     */
    public function getTabWrapperCoworker($active_tab)
    {
        $header = new Framework\Header();
        $header->setPluginData(TEAMBOOKING_FILE_PATH);
        $header->setMainText('Hi, ' . wp_get_current_user()->user_firstname);
        $header->addTab('overview', __('Overview', 'team-booking'), 'dashicons-chart-area', $active_tab === 'overview');
        $header->addTab('slots', __('Slots', 'team-booking'), 'dashicons-calendar-alt', $active_tab === 'slots');
        $header->addTab('events', __('Services', 'team-booking'), 'dashicons-clipboard', $active_tab === 'events');
        $header->addTab('personal', __('Personal', 'team-booking'), 'dashicons-businessman', $active_tab === 'personal');
        $header->render();
    }

    public function getWhatsnewPage()
    {
        ?>
        <div class="wrap about-wrap">
        <h1><?= sprintf(esc_html__('Welcome to TeamBooking %s', 'team-booking'), '2.4') ?></h1>
        <p class="about-text">
            <?= esc_html__('Thank you for updating to the latest version.', 'team-booking') ?>
        </p>
        <div class="wp-badge"
             style="    background: url(<?= TEAMBOOKING_URL . '/images/logo-white.png' ?>) center 45px no-repeat #0073aa;">
            Ver. <?= TEAMBOOKING_VERSION ?>
        </div>

        <h2><?= esc_html__('Features', 'team-booking') ?></h2>
        <div class="feature-section two-col">
            <div class="col">
                <img src="<?= TEAMBOOKING_URL . '/images/feature-1.png' ?>" alt="Slots list"
                     style="border: 5px solid lightgrey;box-sizing: border-box;">
                <h3><?= esc_html__('Backend slots list!', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__('A new section in the backend allows you to have a quick overview of the availability slots and who reserved what.', 'team-booking') ?>
                </p>
            </div>
            <div class="col">
                <div>
                    <img src="<?= TEAMBOOKING_URL . '/images/feature-2.png' ?>" alt="Single use coupons"
                         style="border: 5px solid lightgrey;box-sizing: border-box;">
                </div>
                <h3><?= esc_html__('Single use Coupon list', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__('The Coupon promotions are now more flexible. You can write down a list of single use coupon codes to give to your customers.', 'team-booking') ?>
                </p>
            </div>
        </div>

        <h2><?= esc_html__('Improvements', 'team-booking') ?></h2>
        <div class="feature-section three-col">
            <div class="col">
                <div>
                    <img src="<?= TEAMBOOKING_URL . '/images/improvement-1.png' ?>" alt="Timezone continents"
                         style="border: 5px solid lightgrey;box-sizing: border-box;">
                </div>
                <h3><?= esc_html__('Selectable timezone continents', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__("Your business is limited to some continents and you want to limit the timezones to those continents only? Now you can do it!", 'team-booking') ?>
                </p>
            </div>
            <div class="col">
                <div>
                    <img src="<?= TEAMBOOKING_URL . '/images/improvement-2.png' ?>" alt="Login URL"
                         style="border: 5px solid lightgrey;box-sizing: border-box;">
                </div>
                <h3><?= esc_html__('Customizable login URL', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__('The login URL is now customizable. Oh, and you can choose if the customers must be redirected back to the calendar page after login/registration.', 'team-booking') ?>
                </p>
            </div>
            <div class="col">
                <img src="<?= TEAMBOOKING_URL . '/images/improvement-3.png' ?>" alt="Map toggle"
                     style="border: 5px solid lightgrey;box-sizing: border-box;">
                <h3><?= esc_html__('Show or hide the map', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__("When a service address is specified, TeamBooking used to display the map automatically. Now you can choose if the map should be displayed or not. We know: better late than never, right?", 'team-booking') ?>
                </p>
            </div>
        </div>

        <h2><?= esc_html__('In case you missed it', 'team-booking') ?></h2>
        <div class="feature-section three-col">
            <div class="col">
                <div>
                    <img src="<?= TEAMBOOKING_URL . '/images/feature-backend-1.png' ?>" alt="Visual Composer"
                         style="border: 5px solid lightgrey;box-sizing: border-box;">
                </div>
                <h3><?= esc_html__('Visual Composer elements.', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__('The popular Page Builder is fully supported by TeamBooking. You can add all the shortcodes even via frontend live editor.', 'team-booking') ?>
                </p>
            </div>
            <div class="col">
                <div>
                    <img src="<?= TEAMBOOKING_URL . '/images/feature-backend-2.png' ?>" alt="Overview"
                         style="border: 5px solid lightgrey;box-sizing: border-box;">
                </div>
                <h3><?= esc_html__('New promotions features.', 'team-booking') ?></h3>
                <p>
                    <strong>
                        <?= esc_html__('Usage limit:', 'team-booking') ?>
                    </strong>
                    <?= esc_html__('you can set a limit of usages (one usage = one slot) for your campaigns or coupons. Once the limit is reached, the promotion will be stopped. If you want, you can raise the limit then.', 'team-booking') ?>
                </p>
                <p>
                    <strong>
                        <?= esc_html__('Time bounds:', 'team-booking') ?>
                    </strong>
                    <?= esc_html__('you can specify a minimum time (and/or a maximum time) which the promotion targets. If a timeslot does not comply with those bounds, the promotion will not be applied to it.', 'team-booking') ?>
                </p>
            </div>
            <div class="col">
                <img src="<?= TEAMBOOKING_URL . '/images/feature-backend-3.png' ?>" alt="Allowed Services"
                     style="border: 5px solid lightgrey;box-sizing: border-box;">
                <h3><?= esc_html__('Dynamic booked slot titles.', 'team-booking') ?></h3>
                <p>
                    <?= esc_html__("For your Appointment services, now you can set a dynamic booked event title. That means you can add customer's data (e-mail and name) directly to the booked slot title in Google Calendar, to check who reserved them without even opening the slots.", 'team-booking') ?>
                </p>
            </div>
        </div>

        <div class="changelog">
            <h2><?= esc_html__('Under the hood', 'team-booking') ?></h2>

            <div class="under-the-hood three-col">
                <div class="col">
                    <h3><?= esc_html__('Query string parameters', 'team-booking') ?></h3>
                    <p>
                        <?= esc_html__('A useful way to a further customization of your frontend calendar pages. Check the documentation for all the details.', 'team-booking') ?>
                    </p>
                </div>
                <div class="col">
                    <h3><?= esc_html__('New e-mail hooks', 'team-booking') ?></h3>
                    <p>
                        <?= esc_html__('The reservation_id and service_name hooks are now available.', 'team-booking') ?>
                    </p>
                </div>
                <div class="col">
                    <h3><?= esc_html__('New do_reservation API request', 'team-booking') ?></h3>
                    <p>
                        <?= esc_html__('Now you are able to make a reservation through the API!', 'team-booking') ?>
                    </p>
                </div>
            </div>
        </div>

        <?php
    }

}
