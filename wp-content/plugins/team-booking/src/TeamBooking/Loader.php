<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Loader
 *
 * @author VonStroheim
 */
class Loader
{
    public function load()
    {
        // load text domain for translations
        add_action('init', array(
            $this,
            'load_textdomain',
        ));

        // register custom post types
        add_action('init', array(
            'TeamBooking\\Database\\Services',
            'post_type',
        ));
        add_action('init', array(
            'TeamBooking\\Database\\Forms',
            'post_type',
        ));

        add_action(
            'admin_post_tb_get_ical',
            array(
                'TeamBooking\\ProcessReservation',
                'getIcalFile',
            )
        );
        add_action(
            'admin_post_nopriv_tb_get_ical',
            array(
                'TeamBooking\\ProcessReservation',
                'getIcalFile',
            )
        );

        // add oAuth handler
        add_action('wp_ajax_teambooking_oauth_callback', 'teambooking_oauth_callback');

        // add IPN listener
        add_action('wp_ajax_teambooking_ipn_listener', 'teambooking_ipn_listener');
        add_action('wp_ajax_nopriv_teambooking_ipn_listener', 'teambooking_ipn_listener');

        // add e-mail reminders handler
        add_action('tb_email_reminder_handler', 'TeamBooking\\Functions\\tbSendEmailReminder');

        add_action('widgets_init', function () {
            register_widget('TeamBooking\\Widgets\\Calendar');
            register_widget('TeamBooking\\Widgets\\Upcoming');
        });

        add_action('init', array(
            $this,
            'register_shortcodes',
        ));
        add_filter('set-screen-option', array(
            $this,
            'setScreenOptions',
        ), 10, 3);

        // Visual Composer elements
        if (defined('WPB_VC_VERSION') && function_exists('vc_map')) {
            require_once dirname(TEAMBOOKING_FILE_PATH) . '/includes/tb_vc_elements.php';
        }

        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            if (is_admin()) {
                // activation hook
                register_activation_hook(TEAMBOOKING_FILE_PATH, array(
                    $this,
                    'install',
                ));
                // deactivation hook
                register_deactivation_hook(TEAMBOOKING_FILE_PATH, array(
                    $this,
                    'deactivate',
                ));
                // uninstall hook ($this can't work, TeamBooking_Loader class must be called directly)
                register_uninstall_hook(TEAMBOOKING_FILE_PATH, array(
                    'TeamBooking\\Loader',
                    'uninstall',
                ));

                /*
                 * Check settings integrity
                 */
                if (!Functions\getSettings() instanceof \TeamBookingSettings) {
                    add_action('init', function () {
                        if (!function_exists('deactivate_plugins')) {
                            require_once ABSPATH . 'wp-admin/includes/plugin.php';
                        }
                        deactivate_plugins(plugin_basename(TEAMBOOKING_FILE_PATH), TRUE, FALSE);
                        wp_redirect(admin_url('plugins.php'));
                    });
                } else {
                    /*
                     * TGM Plugin Activation loader
                     */
                    include_once dirname(TEAMBOOKING_FILE_PATH) . '/libs/tgm/class-tgm-plugin-activation.php';
                    add_action('tgmpa_register', array(
                        $this,
                        'requiredPlugins',
                    ));

                    /*
                     * Update method
                     */
                    if (version_compare(Functions\getSettings()->getVersion(), TEAMBOOKING_VERSION, '<')) {
                        add_action('init', array(
                            'TeamBooking\\Update',
                            'update',
                        ));
                    }
                }

                new Admin();
            } else {
                add_action('wp_enqueue_scripts', array(
                    $this,
                    'tb_frontend_resources_enqueue',
                ));
            }
        } else {
            // add REST API handler
            add_action('wp_ajax_teambooking_rest_api', 'teambooking_rest_api');
            add_action('wp_ajax_nopriv_teambooking_rest_api', 'teambooking_rest_api');

            add_action(
                'wp_ajax_tb_submit_payment',
                array(
                    'TeamBooking\\ProcessReservation',
                    'submitPayment',
                ), 10, 0
            );
            add_action(
                'wp_ajax_nopriv_tb_submit_payment',
                array(
                    'TeamBooking\\ProcessReservation',
                    'submitPayment',
                ), 10, 0
            );
            add_action(
                'wp_ajax_tbajax_action_submit_form',
                array(
                    'TeamBooking\\ProcessReservation',
                    'submitReservation',
                ), 10, 0
            );
            add_action(
                'wp_ajax_nopriv_tbajax_action_submit_form',
                array(
                    'TeamBooking\\ProcessReservation',
                    'submitReservation',
                ), 10, 0
            );
            add_action(
                'wp_ajax_tbajax_action_prepare_form',
                array(
                    'TeamBooking\\ProcessReservation',
                    'prepareReservation',
                )
            );
            add_action(
                'wp_ajax_nopriv_tbajax_action_prepare_form',
                array(
                    'TeamBooking\\ProcessReservation',
                    'prepareReservation',
                )
            );
            add_action(
                'wp_ajax_tb_process_onsite_payment',
                array(
                    'TeamBooking\\ProcessReservation',
                    'processOnsite',
                )
            );
            add_action(
                'wp_ajax_nopriv_tb_process_onsite_payment',
                array(
                    'TeamBooking\\ProcessReservation',
                    'processOnsite',
                )
            );

            // register Ajax Callbacks
            add_action('wp_ajax_tbajax_action_change_month', 'tbajax_action_change_month_callback');
            add_action('wp_ajax_tbajax_action_show_day_schedule', 'tbajax_action_show_day_schedule_callback');
            add_action('wp_ajax_tbajax_action_filter_calendar', 'tbajax_action_filter_calendar_callback');
            add_action('wp_ajax_tbajax_action_filter_upcoming', 'tbajax_action_filter_upcoming_callback');
            add_action('wp_ajax_tbajax_action_upcoming_more', 'tbajax_action_upcoming_more_callback');
            add_action('wp_ajax_tbajax_action_get_reservation_modal', 'tbajax_action_get_reservation_modal_callback');
            add_action('wp_ajax_tbajax_action_get_register_modal', 'tbajax_action_get_register_modal_callback');
            add_action('wp_ajax_tbajax_action_fast_month_selector', 'tbajax_action_fast_month_selector_callback');
            add_action('wp_ajax_tbajax_action_fast_year_selector', 'tbajax_action_fast_year_selector_callback');
            add_action('wp_ajax_tbajax_action_cancel_reservation', 'tbajax_action_cancel_reservation_callback');
            add_action('wp_ajax_tbajax_action_validate_coupon', 'tbajax_action_validate_coupon_callback');

            add_action('wp_ajax_nopriv_tbajax_action_change_month', 'tbajax_action_change_month_callback');
            add_action('wp_ajax_nopriv_tbajax_action_show_day_schedule', 'tbajax_action_show_day_schedule_callback');
            add_action('wp_ajax_nopriv_tbajax_action_filter_calendar', 'tbajax_action_filter_calendar_callback');
            add_action('wp_ajax_nopriv_tbajax_action_filter_upcoming', 'tbajax_action_filter_upcoming_callback');
            add_action('wp_ajax_nopriv_tbajax_action_upcoming_more', 'tbajax_action_upcoming_more_callback');
            add_action('wp_ajax_nopriv_tbajax_action_get_reservation_modal', 'tbajax_action_get_reservation_modal_callback');
            add_action('wp_ajax_nopriv_tbajax_action_get_register_modal', 'tbajax_action_get_register_modal_callback');
            add_action('wp_ajax_nopriv_tbajax_action_fast_month_selector', 'tbajax_action_fast_month_selector_callback');
            add_action('wp_ajax_nopriv_tbajax_action_fast_year_selector', 'tbajax_action_fast_year_selector_callback');
            add_action('wp_ajax_nopriv_tbajax_action_cancel_reservation', 'tbajax_action_cancel_reservation_callback');
            add_action('wp_ajax_nopriv_tbajax_action_validate_coupon', 'tbajax_action_validate_coupon_callback');
        }
    }

    /**
     * Load frontend resources
     */
    public static function tb_frontend_resources_enqueue()
    {
        Functions\registerFrontendResources();

        self::find_shortcodes();
        add_action('the_post', array('TeamBooking\\Loader', 'find_shortcodes'));
    }

    public static function find_shortcodes()
    {
        if (defined('TBK_CALENDAR_SHORTCODE_FOUND') && defined('TBK_RESERV_SHORTCODE_FOUND')) {
            return;
        }

        global $post;
        $post_meta = '';
        $post_content = '';
        if (is_object($post)) {
            $post_meta = get_post_meta($post->ID);
            $post_content = $post->post_content;
        }

        if (is_array($post_meta)) {
            $post_meta = json_encode($post_meta);
            $post_content .= $post_meta;
        }
        $calendar_check = (defined('TBK_WIDGET_SHORTCODE_FOUND')
            || defined('TBK_CALENDAR_SHORTCODE_FOUND')
            || has_shortcode($post_content, 'tb-calendar')
            || has_shortcode($post_content, 'tb-upcoming')
        );
        $reservations_check = (defined('TBK_RESERV_SHORTCODE_FOUND') || has_shortcode($post_content, 'tb-reservations'));
        if ($calendar_check || $reservations_check) {
            if ($calendar_check) {
                if (!defined('TBK_CALENDAR_SHORTCODE_FOUND')) {
                    define('TBK_CALENDAR_SHORTCODE_FOUND', TRUE);
                }
            }
            if ($reservations_check) {
                if (!defined('TBK_RESERV_SHORTCODE_FOUND')) {
                    define('TBK_RESERV_SHORTCODE_FOUND', TRUE);
                }
            }
            \TeamBooking\Functions\enqueueFrontendResources();
        }
    }

    /**
     * The plugin Install method
     * Creates an instance of the install-class and fires the installation method
     * It is used only during the activation of the plugin
     *
     * @param $networkwide
     *
     * @return bool
     */
    public function install($networkwide)
    {
        if ($networkwide && function_exists('is_multisite') && is_multisite()) {
            global $wpdb;
            $blogs = $wpdb->get_results("
                    SELECT blog_id
                    FROM {$wpdb->blogs}
                    WHERE site_id = '{$wpdb->siteid}'
                    AND spam = '0'
                    AND deleted = '0'
                    AND archived = '0'
                ");
            foreach ($blogs as $blog_id) {
                switch_to_blog($blog_id);
                $install = new \TeamBooking_Install();
                $install->install();
                restore_current_blog();
            }
            $return = TRUE;
        } else {
            $install = new \TeamBooking_Install();
            $return = $install->install();
        }

        return $return;
    }

    /**
     * The plugin Deactivate method
     * Creates an instance of the install-class and fires the deactivation method
     * It is used only during the deactivation of the plugin
     *
     * @param $networkwide
     */
    public function deactivate($networkwide)
    {
        $install = new \TeamBooking_Install();
        if ($networkwide && function_exists('is_multisite') && is_multisite()) {
            global $wpdb;
            $blogs = $wpdb->get_results("
                    SELECT blog_id
                    FROM {$wpdb->blogs}
                    WHERE site_id = '{$wpdb->siteid}'
                    AND spam = '0'
                    AND deleted = '0'
                    AND archived = '0'
                ");
            foreach ($blogs as $blog_id) {
                switch_to_blog($blog_id);
                $install->deactivate();
                restore_current_blog();
            }
        } else {
            $install->deactivate();
        }
    }

    /**
     * The plugin Uninstall method
     * Creates an instance of the install-class and fires the unistallation method
     * It is used only during the uninstallation of the plugin
     *
     * @param $networkwide
     */
    public static function uninstall($networkwide)
    {
        $install = new \TeamBooking_Install();
        if ($networkwide && function_exists('is_multisite') && is_multisite()) {
            global $wpdb;
            $blogs = $wpdb->get_results("
                    SELECT blog_id
                    FROM {$wpdb->blogs}
                    WHERE site_id = '{$wpdb->siteid}'
                    AND spam = '0'
                    AND deleted = '0'
                    AND archived = '0'
                ");
            foreach ($blogs as $blog_id) {
                switch_to_blog($blog_id);
                $install->uninstall();
                restore_current_blog();
            }
        } else {
            $install->uninstall();
        }
    }

    /**
     * Load the text domain on plugin load.
     *
     * Hooked to the plugins_loaded via the load method
     */
    public function load_textdomain()
    {
        $domain = 'team-booking';
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        load_textdomain($domain, WP_LANG_DIR . '/team-booking/' . $locale . '.mo');
        load_plugin_textdomain($domain, FALSE, plugin_basename(TEAMBOOKING_PATH) . '/languages/');
    }

    public function requiredPlugins()
    {
        /*
         * Array of plugin arrays. Required keys are name and slug.
         * If the source is NOT from the .org repo, then source is also required.
         */
        $plugins = array(
            array(
                'name'         => 'Envato Market',
                // The plugin name.
                'slug'         => 'envato-market',
                // The plugin slug (typically the folder name).
                'source'       => 'http://envato.github.io/wp-envato-market/dist/envato-market.zip',
                // The plugin source.
                'required'     => FALSE,
                // If false, the plugin is only 'recommended' instead of required.
                'external_url' => '',
            ),
        );

        $config = array(
            'id'           => 'tgmpa',
            // Unique ID for hashing notices for multiple instances of TGMPA.
            'default_path' => '',
            // Default absolute path to bundled plugins.
            'menu'         => 'tgmpa-install-plugins',
            // Menu slug.
            'parent_slug'  => 'plugins.php',
            // Parent menu slug.
            'capability'   => 'manage_options',
            // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
            'has_notices'  => TRUE,
            // Show admin notices or not.
            'dismissable'  => TRUE,
            // If false, a user cannot dismiss the nag message.
            'dismiss_msg'  => '',
            // If 'dismissable' is false, this message will be output at top of nag.
            'is_automatic' => FALSE,
            // Automatically activate plugins after installation or not.
            'message'      => '',
            // Message to output right before the plugins table.
            'strings'      => array(
                'page_title'                     => __('Install Recommended Plugins', 'team-booking'),
                'menu_title'                     => __('Install Plugins', 'team-booking'),
                'installing'                     => __('Installing Plugin: %s', 'team-booking'),
                // %s = plugin name.
                'notice_can_install_recommended' => _n_noop(
                    'TeamBooking recommends the following plugin: %1$s.',
                    'TeamBooking recommends the following plugins: %1$s.',
                    'team-booking'
                ),
                // %1$s = plugin name(s).
                'notice_ask_to_update'           => _n_noop(
                    'The following plugin needs to be updated to its latest version to ensure maximum compatibility with TeamBooking: %1$s.',
                    'The following plugins need to be updated to their latest version to ensure maximum compatibility with TeamBooking: %1$s.',
                    'team-booking'
                ),
                // %1$s = plugin name(s).
                'notice_ask_to_update_maybe'     => _n_noop(
                    'There is an update available for: %1$s.',
                    'There are updates available for the following plugins: %1$s.',
                    'team-booking'
                ),
                // %1$s = plugin name(s).
            ),
        );

        tgmpa($plugins, $config);
    }

    /**
     * Register Shortcodes handler
     */
    public function register_shortcodes()
    {
        // Main shortcode
        add_shortcode('tb-calendar', array(
            'TeamBooking\\Shortcodes\\Calendar',
            'render',
        ));

        // User reservation list
        add_shortcode('tb-reservations', array(
            'TeamBooking\\Shortcodes\\Reservations',
            'render',
        ));

        // Upcoming events list
        add_shortcode('tb-upcoming', array(
            'TeamBooking\\Shortcodes\\Upcoming',
            'render',
        ));
    }

    /**
     * @param $status
     * @param $option
     * @param $value
     *
     * @return mixed
     */
    public function setScreenOptions($status, $option, $value)
    {
        if ('tbk_reservations_per_page' === $option) return $value;

        return $status;
    }
}
