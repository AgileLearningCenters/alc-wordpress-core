<?php

namespace TeamBooking\Admin;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Admin,
    TeamBooking\Functions;

/**
 * Class Core
 *
 * @author VonStroheim
 */
class Core
{
    public $roles_all;
    public $roles_allowed;
    private $redirect_uri;
    private $js_origins;
    private $settings;

    public function __construct()
    {
        $this->settings = Functions\getSettings();
        $this->redirect_uri = admin_url() . 'admin-ajax.php?action=teambooking_oauth_callback';
        $this->js_origins = strtolower(substr(site_url(), 0, strpos(site_url(), '/'))) . '//' . $_SERVER['HTTP_HOST'];
    }

    /**
     * The core settings page
     *
     * @return string
     */
    public function getPostBody()
    {
        ob_start();
        ?>
        <div class="tbk-wrapper">
            <?php
            $row = new Framework\Row();
            $column = Framework\Column::ofWidth(5);
            $column->addElement($this->getGoogleProjectSettings());
            $column->addElement($this->getQuickGuide());
            $column->addElement($this->getTokenTable());
            $column->appendTo($row);
            $column = Framework\Column::ofWidth(5);
            $column->addElement($this->getSettings());
            $column->appendTo($row);
            $column = Framework\Column::ofWidth(2);
            $column->addElement($this->getAdvanced());
            $column->appendTo($row);
            $row->render();
            ?>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * The internal settings part
     *
     * @return Framework\PanelWithForm
     */
    private function getSettings()
    {
        $panel = new Framework\PanelWithForm(ucfirst(__('Settings', 'team-booking')));
        $panel->setAction('tbk_save_core');
        $panel->setNonce('team_booking_options_verify');

        // Coworker's roles
        $element = new Framework\PanelSettingCheckboxes(__('Roles allowed to be Coworkers', 'team-booking'));
        $element->addDescription(__('Users with selected roles will be your Coworkers. Those who link a Google Calendar can participate to all service classes. Coworkers without a linked Google Calendar can participate to "Unscheduled services" only.', 'team-booking'));
        $element->addToDescription('<p>' . esc_html__('Administrators are always allowed.', 'team-booking') . '</p>', FALSE);
        foreach ($this->roles_all as $name => $role) {
            if ($name === 'administrator') continue;
            $element->addCheckbox(array(
                'fieldname' => 'roles_allowed' . '[' . $name . ']',
                'label'     => $role['name'],
                'value'     => $role['name'],
                'checked'   => in_array($role['name'], $this->roles_allowed)
            ));
        }
        $element->appendTo($panel);

        // Autofill fields
        $element = new Framework\PanelSettingRadios(__('Autofill reservation form fields for registered users', 'team-booking'));
        $element->addFieldname('autofill');
        $element->addDescription(__('If yes, a logged-in customer will found some reservation form fields pre-filled, based on his WordPress profile data. If yes and hide, the pre-filled fields will not be shown, but their data still pass.', 'team-booking'));
        $element->addOption(array(
            'label'   => __('Yes', 'team-booking'),
            'value'   => 'yes',
            'checked' => $this->settings->getAutofillReservationForm() === TRUE
        ));
        $element->addOption(array(
            'label'   => __('Yes, and hide fields', 'team-booking'),
            'value'   => 'hide',
            'checked' => $this->settings->getAutofillReservationForm() === 'hide'
        ));
        $element->addOption(array(
            'label'   => __('No', 'team-booking'),
            'value'   => 'no',
            'checked' => $this->settings->getAutofillReservationForm() === FALSE
        ));
        $element->appendTo($panel);

        // Load at first month
        $element = new Framework\PanelSettingYesOrNo(__('Load the frontend calendar at the closest month with available slots', 'team-booking'));
        $element->addDescription(__('If yes, the frontend calendar will be automatically loaded at the closest month with at least one free slot. Please note: if yes, the first page loading can be slower.', 'team-booking'));
        $element->addFieldname('first_month_automatic');
        $element->setState($this->settings->isFirstMonthWithFreeSlotShown());
        $element->appendTo($panel);

        // ICAL file
        $element = new Framework\PanelSettingYesOrNo(__('Allow customers to download ICAL file after a reservation', 'team-booking'));
        $element->addFieldname('show_ical');
        $element->setState($this->settings->getShowIcal());
        $element->appendTo($panel);

        // Login URL
        $element = new Framework\PanelSettingText(__('Login URL', 'team-booking'));
        $element->addDescription(__('Logged-only services will invite users to login here', 'team-booking'));
        $element->addFieldname('login_url');
        $element->addDefaultValue($this->settings->getLoginUrl());
        $element->appendTo($panel);

        // Registration URL
        $element = new Framework\PanelSettingText(__('Registration URL', 'team-booking'));
        $element->addDescription(__('Logged-only services will invite users to register here', 'team-booking'));
        $element->addFieldname('registration_url');
        $element->addDefaultValue($this->settings->getRegistrationUrl());
        $element->appendTo($panel);

        // Redirect after login/registration
        $element = new Framework\PanelSettingYesOrNo(__('Redirect the customers back to the calendar page after login/registration', 'team-booking'));
        $element->addFieldname('redirect_back_after_login');
        $element->setState($this->settings->getRedirectBackAfterLogin());
        $element->addAlert(__('An eventual login redirect plugin may interfere with that.', 'team-booking'));
        $element->appendTo($panel);

        // Database retention time
        $element = new Framework\PanelSettingSelector(__('Keep reservations in database for', 'team-booking'));
        $element->addFieldname('database_reservation_timeout');
        $element->addDescription(__("Counting starts from reservation's date", 'team-booking'));
        $element->setSelected($this->settings->getDatabaseReservationTimeout());
        $element->addOption(15 * DAY_IN_SECONDS, __('15 days', 'team-booking'));
        if (Functions\cleanReservations(FALSE, TRUE, 15 * DAY_IN_SECONDS)) $element->addWarning(15 * DAY_IN_SECONDS, __('Warning: some reservations will be deleted on save', 'team-booking'));
        $element->addOption(30 * DAY_IN_SECONDS, __('30 days', 'team-booking'));
        if (Functions\cleanReservations(FALSE, TRUE, 30 * DAY_IN_SECONDS)) $element->addWarning(30 * DAY_IN_SECONDS, __('Warning: some reservations will be deleted on save', 'team-booking'));
        $element->addOption(60 * DAY_IN_SECONDS, __('60 days', 'team-booking'));
        if (Functions\cleanReservations(FALSE, TRUE, 60 * DAY_IN_SECONDS)) $element->addWarning(60 * DAY_IN_SECONDS, __('Warning: some reservations will be deleted on save', 'team-booking'));
        $element->addOption(120 * DAY_IN_SECONDS, __('120 days', 'team-booking'));
        if (Functions\cleanReservations(FALSE, TRUE, 120 * DAY_IN_SECONDS)) $element->addWarning(120 * DAY_IN_SECONDS, __('Warning: some reservations will be deleted on save', 'team-booking'));
        $element->addOption(240 * DAY_IN_SECONDS, __('240 days', 'team-booking'));
        if (Functions\cleanReservations(FALSE, TRUE, 240 * DAY_IN_SECONDS)) $element->addWarning(240 * DAY_IN_SECONDS, __('Warning: some reservations will be deleted on save', 'team-booking'));
        $element->addOption(0, __('Forever', 'team-booking'));
        $element->appendTo($panel);

        // Max pending time
        $element = new Framework\PanelSettingSelector(__('Max pending time', 'team-booking'));
        $element->addFieldname('max_pending_time');
        $element->addDescription(__('If payment is not made within this time, the reservation will be released. It affects only services where payment must be done immediately.', 'team-booking'));
        $element->setSelected($this->settings->getMaxPendingTime());
        $element->addOption(0, __('Never', 'team-booking'));
        $element->addOption(900, __('15min', 'team-booking'));
        $element->addOption(1800, __('30min', 'team-booking'));
        $element->addOption(3600, __('1h', 'team-booking'));
        $element->addOption(7200, __('2h', 'team-booking'));
        $element->addOption(10800, __('3h', 'team-booking'));
        $element->addOption(21600, __('6h', 'team-booking'));
        $element->addOption(43200, __('12h', 'team-booking'));
        $element->addOption(86400, __('1day', 'team-booking'));
        $element->addOption(172800, __('2days', 'team-booking'));
        $element->addOption(259200, __('3days', 'team-booking'));
        $element->addOption(345600, __('4days', 'team-booking'));
        $element->addAlertDropcap(__('Note', 'team-booking'));
        $element->addAlert(__('Values too low could, in extreme cases, lead to payments after reservation is released. Also consider that, in order to process IPN confirmation, your server must be up and running. PayPal example: if your server is down, IPN will be resent by PayPal for up to four days, with a maximum of 15 retries. The interval will increase after each fail attempt.', 'team-booking'));
        $element->appendTo($panel);

        // Delete reservation database on uninstall
        $element = new Framework\PanelSettingYesOrNo(__("Delete plugin's database tables when the plugin is uninstalled", 'team-booking'));
        $element->addFieldname('drop_tables');
        $element->setState($this->settings->getDropTablesOnUninstall());
        $element->appendTo($panel);

        // Debug
        $element = new Framework\PanelSettingRadios(__('Debug', 'team-booking'));
        $element->addFieldname('debug');
        $element->addOption(array(
            'label'   => __('Silent', 'team-booking'),
            'value'   => 'silent',
            'checked' => $this->settings->getSilentDebug() === TRUE
        ));
        $element->addOption(array(
            'label'   => __('Verbose (Google API error messages on frontend calendar)', 'team-booking'),
            'value'   => 'verbose',
            'checked' => $this->settings->getSilentDebug() === FALSE
        ));
        $element->appendTo($panel);

        // Google Maps API key
        $element = new Framework\PanelSettingText(__('Google Maps API key', 'team-booking'));
        $element->addDescription(__("This is mandatory in order to use Google Maps for installations made after 22nd of June, 2016. If you don't have a Google Maps API key already, please check the documentation in order to know how to obtain it.", 'team-booking'));
        $element->addFieldname('gmaps_api_key');
        $element->addDefaultValue($this->settings->getGmapsApiKey());
        $element->appendTo($panel);

        // Skip Google Maps library
        $element = new Framework\PanelSettingYesOrNo(__('Skip Google Maps library loading', 'team-booking'));
        $element->addFieldname('skip_gmaps');
        $element->setState($this->settings->getSkipGmapLibs());
        $element->appendTo($panel);

        // Restrict Timezones
        $element = new Framework\PanelSettingCheckboxes(__('Restrict continents in frontend timezone selectors', 'team-booking'));
        $element->addDescription(__('Unchecked continents will be hidden', 'team-booking'));
        foreach (Functions\continents_list() as $value => $name) {
            $element->addCheckbox(array(
                'fieldname' => 'continents_allowed' . '[' . $value . ']',
                'label'     => $name,
                'value'     => $value,
                'checked'   => $this->settings->getContinentAllowed($value)
            ));
        }
        $element->appendTo($panel);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_core');
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * The advanced operations part
     *
     * @return Framework\Panel
     */
    private function getAdvanced()
    {
        $panel = new Framework\Panel(ucfirst(__('Advanced', 'team-booking')));
        $element = new Framework\PanelSettingWildcard(NULL);
        ob_start();
        echo Framework\Html::paragraph(array(
            'text'   => Framework\Html::anchor(array(
                'text'  => __('Export current settings', 'team-booking'),
                'id'    => 'team-booking-export-settings',
                'class' => 'button tbk-button-long-text'
            )),
            'escape' => FALSE
        ));
        ?>
        <form id="team-booking-export-settings_form" method="POST"
              action="<?= Admin::add_params_to_admin_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="tbk_settings_backup">
            <?php wp_nonce_field('team_booking_options_verify') ?>
        </form>
        <?php
        echo Framework\Html::paragraph(array(
            'text'   => Framework\Html::anchor(array(
                'text'  => __('Import settings from file', 'team-booking'),
                'id'    => 'team-booking-import-settings',
                'class' => 'button tbk-button-long-text'
            )),
            'escape' => FALSE
        ));

        echo Framework\Html::paragraph(array(
            'text'   => Framework\Html::anchor(array(
                'text'  => __('Repair database', 'team-booking'),
                'id'    => 'team-booking-repair-database',
                'class' => 'button tbk-button-long-text'
            )),
            'escape' => FALSE
        ));

        $element->addContent(ob_get_clean());

        // Import settings modal
        $modal = new Framework\Modal('team-booking-import-settings_modal');
        $modal->setHeaderText(array('main' => __('Import settings from file', 'team-booking')));
        $modal->setButtonText(array(
            'approve' => __('OK', 'team-booking'),
            'deny'    => __('Cancel', 'team-booking')
        ));
        $modal->addContent('<form id="team-booking-import-settings_form" method="POST" action="' . Admin::add_params_to_admin_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">');
        $modal->addContent('<input type="hidden" name="action" value="tbk_import_settings">');
        $modal->addContent(wp_nonce_field('team_booking_options_verify', '_wpnonce', TRUE, FALSE));
        $modal->addContent('<input type="file" name="settings_backup_file">');
        $modal->addContent('</form>');
        $element->addContent($modal);

        // Repair database modal
        $modal = new Framework\Modal('team-booking-repair-database_modal');
        $modal->setHeaderText(array('main' => __('Are you sure?', 'team-booking')));
        $modal->addContent(Framework\Html::paragraph(__('This may take a while, please be patient.', 'team-booking')));
        $modal->setButtonText(array(
            'approve' => __('OK', 'team-booking'),
            'deny'    => __('Cancel', 'team-booking')
        ));
        $element->addContent($modal);

        // Import JSON modal
        $modal = new Framework\Modal('team-booking-import-core-json_modal');
        $modal->setHeaderText(array('main' => __('Import from JSON file', 'team-booking')));
        $modal->setButtonText(array(
            'approve' => __('OK', 'team-booking'),
            'deny'    => __('Cancel', 'team-booking')
        ));
        $modal->addContent('<form id="team-booking-import-core-json_form" method="POST" action="">');
        $modal->addContent(wp_nonce_field('team_booking_options_verify', '_wpnonce', TRUE, FALSE));
        $modal->addContent('<input type="file" name="settings_json_file" data-ays-ignore="true">');
        $modal->addContent(Framework\Html::paragraph(array('text' => __('The Authorized redirect URI and/or Authorized Javascript Origins seems to be incorrect, have you correctly pasted the values provided here in your Google Project console? Please double check, and retry with the new JSON file!', 'team-booking'), 'class' => 'tb-json-errors uri_mismatch')));
        $modal->addContent(Framework\Html::paragraph(array('text' => __('Sorry, this is not a JSON Google Project file, or it is not complete.', 'team-booking'), 'class' => 'tb-json-errors invalid_file')));
        $modal->addContent(Framework\Html::paragraph(array('text' => __('Please select a file!', 'team-booking'), 'class' => 'tb-json-errors no_file')));
        $modal->addContent('</form>');
        $element->addContent($modal);

        $element->appendTo($panel);

        return $panel;
    }

    /**
     * The quick guide part
     *
     * @return Framework\Panel
     */
    private function getQuickGuide()
    {
        $panel = new Framework\Panel(ucfirst(__('Are you lost?', 'team-booking')));
        $plugin_data = get_plugin_data(TEAMBOOKING_FILE_PATH);
        $url = 'http://console.developers.google.com/';
        $url_doc = $plugin_data['PluginURI'] . '/docs';
        $string1 = esc_html__('To made Team Booking working, you need to start a new Project on your Google Developer Console', 'team-booking') . ' (<a href="' . $url . '" alt="Google Developer Console Link" target="_blank">link</a>)';
        $string2 = esc_html__('Then read the "Core Configuration" paragraph of the Team Booking Documentation', 'team-booking') . ' (<a href="' . $url_doc . '" alt="TeamBooking documentation" target="_blank">link</a>)';
        $element = new Framework\PanelSettingWildcard(NULL);
        $element->addDescription(Framework\Html::paragraph(array('text' => $string1, 'escape' => FALSE)), FALSE);
        $element->addToDescription(Framework\Html::paragraph(array('text' => $string2, 'escape' => FALSE)), FALSE);
        $element->appendTo($panel);

        return $panel;
    }

    /**
     * The API tokens part
     *
     * @return Framework\Panel
     */
    private function getTokenTable()
    {
        $panel = new Framework\Panel(ucfirst(__('API tokens', 'team-booking')));

        $button = new Framework\PanelTitleAddNewButton(__('New API token (read-only)', 'team-booking'));
        $button->addClass('team-booking-new-token');
        $button->addData(array('write' => '0'));
        $panel->addTitleContent($button);
        $button = new Framework\PanelTitleAddNewButton(__('New API token', 'team-booking'));
        $button->addClass('team-booking-new-token');
        $button->addData(array('write' => '1'));
        $panel->addTitleContent($button);

        $table = new Framework\Table();
        $table->setId('tbk-core-tokens');
        // Preparing the table columns
        $table->addColumns(array(
            esc_html__('Token', 'team-booking'),
            esc_html__('Scope', 'team-booking'),
            esc_html__('Usages', 'team-booking'),
            esc_html__('Actions', 'team-booking')
        ));
        // Preparing the table rows
        foreach ($this->settings->getTokens() as $token => $specs) {
            $button = new Framework\ActionButton('dashicons-trash');
            $button->addClass('tbk-token-action-delete');
            $button->setTitle(__('Delete', 'team-booking'));
            $button->addData('token', $token);
            $table->addRow(array(
                0 => $token,
                1 => $specs['write'] ? esc_html__('read/write', 'team-booking') : esc_html__('read', 'team-booking'),
                2 => $this->settings->getTotalTokenUsages($token),
                3 => $button
            ));
        }

        $panel->addElement($table);

        return $panel;
    }

    /**
     * The Google Project settings part
     *
     * @return Framework\PanelWithForm
     */
    private function getGoogleProjectSettings()
    {
        $panel = new Framework\PanelWithForm(ucfirst(__('Google Project Data', 'team-booking')));
        $json_button = new Framework\PanelTitleAddNewButton(__('Import from JSON file', 'team-booking'));
        $json_button->setId('team-booking-import-core-json');
        $panel->addTitleContent($json_button);
        $panel->setAction('tbk_save_core');
        $panel->setNonce('team_booking_options_verify');

        // Client ID
        $element = new Framework\PanelSettingTextWithLock(__('Client ID', 'team-booking'));
        $element->addDefaultValue($this->settings->getApplicationClientId());
        $element->addFieldname('client_id');
        $element->setReadOnly($this->settings->getApplicationClientId());
        $element->appendTo($panel);

        // Client Secret
        $element = new Framework\PanelSettingTextWithLock(__('Client Secret', 'team-booking'));
        $element->addDefaultValue($this->settings->getApplicationClientSecret());
        $element->addFieldname('client_secret');
        $element->setReadOnly($this->settings->getApplicationClientSecret());
        $element->appendTo($panel);

        // Product name
        $element = new Framework\PanelSettingTextWithLock(__('Product name', 'team-booking'));
        $element->addDefaultValue($this->settings->getApplicationProjectName());
        $element->addFieldname('project_name');
        $element->setReadOnly($this->settings->getApplicationProjectName());
        $element->appendTo($panel);

        // Redirect URI
        $element = new Framework\PanelSettingWildcard(__('Authorized redirect URI', 'team-booking'));
        $element->addDescription($this->redirect_uri);
        $element->appendTo($panel);

        // JS origin
        $element = new Framework\PanelSettingWildcard(__('Authorized Javascript Origins', 'team-booking'));
        $element->addDescription($this->js_origins);
        $element->appendTo($panel);

        // Save changes
        $element = new Framework\PanelSaveButton(__('Save changes', 'team-booking'), 'tbk_save_core');
        $panel->addElement($element);

        return $panel;
    }

}
