<?php

defined('ABSPATH') or die('No script kiddies please!');

class TeamBookingSettings
{
    private $coworkers_data;
    private $coworkers_url_array;
    private $bookings; // deprecated @since 2.2.0
    private $logs; // deprecated @since 1.3.0
    private $error_logs;
    private $drop_tables_on_unistall;
    private $show_ical;
    private $gmaps_api_key;
    private $skip_gmaps_library;
    private $gmaps_zoom_level;
    private $application_cliend_id;
    private $application_client_secret;
    private $application_project_name;
    private $color_background;
    private $color_weekline;
    private $pattern;
    private $color_free_slot;
    private $color_soldout_slot;
    private $group_slots_by;
    private $border;
    private $price_tag_color;
    private $slot_style;
    private $numbered_dots_lower_bound;
    private $numbered_dots_logic;
    private $map_style;
    private $map_style_use_default;
    private $fix_62dot5;
    private $silent_debug;
    private $version;
    private $first_month_with_free_slot_is_shown;
    private $autofill_reservation_form;
    private $database_reservation_timeout;
    private $max_pending_time;
    private $registration_url;
    private $login_url;
    private $redirect_back_after_login;
    private $payment_gateways;
    private $currency_code;
    private $tokens;
    private $continents_allowed;

    public function __construct()
    {
        $this->color_background = '#FFFFFF';
        $this->color_weekline = '#FFBDBD';
        $this->color_free_slot = '#A1CF64';
        $this->color_soldout_slot = '#d95c5c';
        $this->price_tag_color = 'yellow';
        $this->border = array(
            'size'   => 5,          //px
            'color'  => '#CCCCCC',  //HEX
            'radius' => 0           //px
        );
        $this->pattern = array(
            'weekline' => 0,    // no pattern
            'calendar' => 0,    // no pattern
        );
        $this->fix_62dot5 = FALSE;
        $this->numbered_dots_logic = 'slots'; //possible values: slots, tickets, hide, service, slots_service, tickets_service
        $this->numbered_dots_lower_bound = 0;
        $this->map_style = 0;
        $this->map_style_use_default = FALSE;
        $this->setGroupSlotsByTime();
        $this->version = TEAMBOOKING_VERSION;
        $this->autofill_reservation_form = TRUE;
        $this->database_reservation_timeout = 0;
        $this->first_month_with_free_slot_is_shown = FALSE;
        $this->max_pending_time = 3600;
        $this->silent_debug = TRUE;
        $this->payment_gateways = array();
        $this->addPaymentGatewaySettingObject(new TeamBooking_PaymentGateways_Stripe_Settings);
        $this->addPaymentGatewaySettingObject(new TeamBooking_PaymentGateways_PayPal_Settings);
        $this->coworkers_url_array = array();
        $this->drop_tables_on_unistall = FALSE;
        $this->show_ical = TRUE;
        $this->gmaps_api_key = NULL;
        $this->skip_gmaps_library = FALSE;
        $this->gmaps_zoom_level = 14;
        $this->tokens = array();
        $this->continents_allowed = array(
            'Africa'     => TRUE,
            'America'    => TRUE,
            'Antarctica' => TRUE,
            'Arctic'     => TRUE,
            'Asia'       => TRUE,
            'Atlantic'   => TRUE,
            'Australia'  => TRUE,
            'Europe'     => TRUE,
            'Indian'     => TRUE,
            'Pacific'    => TRUE
        );
    }

    /**
     * Returns the Coworkers data array
     *
     * @return TeamBookingCoworker[]
     */
    public function getCoworkersData()
    {
        if (!empty($this->coworkers_data)) {
            return $this->coworkers_data;
        } else {
            return array();
        }
    }

    /**
     * Set / update the data for a specific coworker
     *
     * @param TeamBookingCoworker $data
     */
    public function updateCoworkerData(TeamBookingCoworker $data)
    {
        $this->coworkers_data[ $data->getId() ] = $data;
    }

    /**
     * Drop the data of a specific coworker
     *
     * @param integer $coworker_id
     */
    public function dropCoworkerData($coworker_id)
    {
        unset($this->coworkers_data[ $coworker_id ]);
    }

    /**
     * Get the data for a specific coworker.
     *
     * If the coworker has no data saved yet,
     * then a new instance of data class will be returned.
     *
     * @param integer $id
     *
     * @return \TeamBookingCoworker
     */
    public function getCoworkerData($id)
    {
        $coworkers_data = $this->getCoworkersData();
        if (isset($coworkers_data[ $id ])) {
            return $coworkers_data[ $id ];
        } else {
            return new TeamBookingCoworker($id);
        }
    }

    /**
     * Save/update a coworker URL
     *
     * @param integer $coworker_id
     * @param string  $url
     */
    public function updateCoworkerUrl($coworker_id, $url)
    {
        $this->coworkers_url_array[ $coworker_id ] = $url;
    }

    /**
     * Drop a coworker URL
     *
     * @param integer $coworker_id
     */
    public function dropCoworkerUrl($coworker_id)
    {
        unset($this->coworkers_url_array[ $coworker_id ]);
    }

    /**
     * Get a coworker URL
     *
     * @param integer $coworker_id
     *
     * @return string the coworker URL
     */
    public function getCoworkerUrl($coworker_id)
    {
        if (!isset($this->coworkers_url_array[ $coworker_id ])) {
            // No customized coworker URL, returning default
            return get_site_url() . '/?author=' . $coworker_id;
        } else {
            if (empty($this->coworkers_url_array[ $coworker_id ])) {
                // Empty customized coworker URL, returning default
                return get_site_url() . '/?author=' . $coworker_id;
            } else {
                return $this->coworkers_url_array[ $coworker_id ];
            }
        }
    }

    /**
     * @deprecated 2.2.0 No longer used by internal code, only by update routine where needed
     * @see        \TeamBooking\Database\Services:get()
     *
     * Get the array of service objects.
     *
     * Structure:
     * $array[service_id] = {TeamBookingType object}
     *
     * @return TeamBookingType[]
     */
    public function getServices()
    {
        if (!empty($this->bookings)) {
            return $this->bookings;
        } else {
            return array();
        }
    }

    /**
     * @deprecated 2.2.0 No longer used by internal code, only by update routine where needed
     */
    public function removeServices()
    {
        $this->bookings = array();
    }

    /**
     * @deprecated 2.2.0 No longer used by internal code, only by update routine where needed
     * @see        \TeamBooking\Database\Services:add()
     *
     * Update/save a service
     *
     * @param TeamBookingType $service
     * @param integer         $id
     */
    public function updateService(TeamBookingType $service, $id)
    {
        $this->bookings[ $id ] = $service;
    }

    /**
     * @deprecated 2.2.0 No longer used by internal code, only by update routine where needed
     * @see        \TeamBooking\Database\Services:get()
     *
     * Get service object
     *
     * @param string $id
     *
     * @return TeamBookingType
     * @throws Exception
     */
    public function getService($id)
    {
        $services = $this->getServices();
        if (isset($services[ $id ])) {
            return $services[ $id ];
        } else {
            throw new Exception('Service id not found');
        }
    }

    /**
     * Get the list of all services IDs
     *
     * @param bool $drop_unscheduled
     *
     * @return array IDs list
     * @throws Exception
     */
    public function getServiceIdList($drop_unscheduled = FALSE)
    {
        // TODO: using array_keys instead?
        $services = \TeamBooking\Database\Services::get();
        $ids = array();
        foreach ($services as $service) {
            if ($drop_unscheduled && $service->getClass() === 'unscheduled') continue;
            $ids[] = $service->getId();
        }

        return $ids;
    }

    /**
     * @deprecated 1.3.0
     *
     * Drop a reservation log
     *
     * @param string $log_key
     */
    public function dropLog($log_key)
    {
        unset($this->logs[ $log_key ]);
    }

    /**
     * Drop an error log
     *
     * @param string $log_key
     */
    public function dropErrorLog($log_key)
    {
        unset($this->error_logs[ $log_key ]);
    }

    /**
     * Set the error logs array
     *
     * @param array $error_logs
     */
    public function setErrorLogs(array $error_logs)
    {
        $this->error_logs = $error_logs;
    }

    /**
     * Get the error logs array
     *
     * @return TeamBooking_ErrorLog[]
     */
    public function getErrorLogs()
    {
        if (!$this->error_logs) {
            return array();
        } else {
            return $this->error_logs;
        }
    }

    /**
     * @deprecated 1.3.0
     *
     * @return array
     */
    public function getLogs()
    {
        if (!$this->logs) {
            return array();
        } else {
            return $this->logs;
        }
    }

    /**
     * @param $bool
     */
    public function setDropTablesOnUninstall($bool)
    {
        $this->drop_tables_on_unistall = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getDropTablesOnUninstall()
    {
        return isset($this->drop_tables_on_unistall) ? (bool)$this->drop_tables_on_unistall : FALSE;
    }

    /**
     * @param $bool
     */
    public function setShowIcal($bool)
    {
        $this->show_ical = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getShowIcal()
    {
        return isset($this->show_ical) ? (bool)$this->show_ical : FALSE;
    }

    /**
     * @param $bool
     */
    public function setSkipGmapLibs($bool)
    {
        $this->skip_gmaps_library = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getSkipGmapLibs()
    {
        return isset($this->skip_gmaps_library) ? (bool)$this->skip_gmaps_library : FALSE;
    }

    /**
     * @param string $key
     */
    public function setGmapsApiKey($key)
    {
        $this->gmaps_api_key = $key;
    }

    /**
     * @return null|string
     */
    public function getGmapsApiKey()
    {
        return isset($this->gmaps_api_key) ? $this->gmaps_api_key : NULL;
    }

    /**
     * @param $int
     */
    public function setGmapsZoomLevel($int)
    {
        if ((int)$int < 0) $int = 0;
        if ((int)$int > 19) $int = 19;
        $this->gmaps_zoom_level = (int)$int;
    }

    /**
     * @return int
     */
    public function getGmapsZoomLevel()
    {
        return $this->gmaps_zoom_level === NULL ? 14 : $this->gmaps_zoom_level;
    }

    /**
     * @param string $id
     */
    public function setApplicationClientId($id)
    {
        $this->application_cliend_id = $id;
    }

    /**
     * @return string
     */
    public function getApplicationClientId()
    {
        return $this->application_cliend_id;
    }

    /**
     * @param string $secret
     */
    public function setApplicationClientSecret($secret)
    {
        $this->application_client_secret = $secret;
    }

    /**
     * @return string
     */
    public function getApplicationClientSecret()
    {
        return $this->application_client_secret;
    }

    /**
     * @param string $name
     */
    public function setApplicationProjectName($name)
    {
        $this->application_project_name = $name;
    }

    /**
     * @return string
     */
    public function getApplicationProjectName()
    {
        return $this->application_project_name;
    }

    /**
     * @param string $color
     */
    public function setColorBackground($color)
    {
        $this->color_background = $color;
    }

    /**
     * @return string
     */
    public function getColorBackground()
    {
        return $this->color_background;
    }

    /**
     * @param string $color
     */
    public function setColorWeekLine($color)
    {
        $this->color_weekline = $color;
    }

    /**
     * @return string
     */
    public function getColorWeekLine()
    {
        return $this->color_weekline;
    }

    /**
     * @param $int
     */
    public function setPatternCalendar($int)
    {
        $this->pattern['calendar'] = $int;
    }

    /**
     * @param $int
     */
    public function setPatternWeekline($int)
    {
        $this->pattern['weekline'] = $int;
    }

    /**
     * @return array
     */
    public function getPattern()
    {
        if (!is_array($this->pattern)) {
            return array(
                'calendar' => 0,
                'weekline' => 0,
            );
        } else {
            return $this->pattern;
        }
    }

    /**
     * @param string $color
     */
    public function setColorFreeSlot($color)
    {
        $this->color_free_slot = $color;
    }

    /**
     * @return string
     */
    public function getColorFreeSlot()
    {
        return $this->color_free_slot;
    }

    /**
     * @param string $color
     */
    public function setColorSoldoutSlot($color)
    {
        $this->color_soldout_slot = $color;
    }

    /**
     * @return string
     */
    public function getColorSoldoutSlot()
    {
        if (!$this->color_soldout_slot) {
            return '#d95c5c';
        } else {
            return $this->color_soldout_slot;
        }
    }

    /**
     * @param string $logic
     */
    public function setNumberedDotsLogic($logic)
    {
        $this->numbered_dots_logic = $logic;
    }

    /**
     * @return string
     */
    public function getNumberedDotsLogic()
    {
        if (!$this->numbered_dots_logic) {
            return 'slots';
        } else {
            return $this->numbered_dots_logic;
        }
    }

    /**
     * @param $number
     */
    public function setNumberedDotsLowerBound($number)
    {
        $this->numbered_dots_lower_bound = (int)abs($number);
    }

    /**
     * @return int
     */
    public function getNumberedDotsLowerBound()
    {
        if (!$this->numbered_dots_lower_bound) {
            return 0;
        } else {
            return $this->numbered_dots_lower_bound;
        }
    }

    /**
     * @param $style
     */
    public function setMapStyle($style)
    {
        $this->map_style = (int)$style;
    }

    /**
     * @param bool $id_only
     *
     * @return int
     */
    public function getMapStyle($id_only = FALSE)
    {
        if (!$this->map_style) {
            $style_id = 0;
        } else {
            $style_id = $this->map_style;
        }
        if (!$id_only) {
            include TEAMBOOKING_PATH . 'includes/tb_mapstyles.php';

            return $tb_mapstyles[ $style_id ];
        } else {
            return $style_id;
        }
    }

    /**
     * @param $bool
     */
    public function setMapStyleUseDefault($bool)
    {
        $this->map_style_use_default = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getMapStyleUseDefault()
    {
        return isset($this->map_style_use_default) ? (bool)$this->map_style_use_default : FALSE;
    }

    /**
     * @param $bool
     */
    public function setFix62dot5($bool)
    {
        $this->fix_62dot5 = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getFix62dot5()
    {
        return isset($this->fix_62dot5) ? (bool)$this->fix_62dot5 : FALSE;
    }

    /**
     * @param $int
     */
    public function setBorderSize($int)
    {
        if (!is_array($this->border)) {
            $this->border = array();
        }
        $this->border['size'] = (int)$int;
    }

    /**
     * @param $hex
     */
    public function setBorderColor($hex)
    {
        if (!is_array($this->border)) {
            $this->border = array();
        }
        $this->border['color'] = $hex;
    }

    /**
     * @param $int
     */
    public function setBorderRadius($int)
    {
        if (!is_array($this->border)) {
            $this->border = array();
        }
        $this->border['radius'] = (int)$int;
    }

    /**
     * @return array
     */
    public function getBorder()
    {
        if (!is_array($this->border)) {
            return array(
                'size'   => 5,
                'color'  => '#CCCCCC',
                'radius' => 0,
            );
        } else {
            return $this->border;
        }
    }

    public function setGroupSlotsByTime()
    {
        $this->group_slots_by = 'time';
    }

    /**
     * @return bool
     */
    public function isGroupSlotsByTime()
    {
        if ($this->group_slots_by === 'time') {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function setGroupSlotsByCoworker()
    {
        $this->group_slots_by = 'coworker';
    }

    /**
     * @return bool
     */
    public function isGroupSlotsByCoworker()
    {
        if ($this->group_slots_by === 'coworker') {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function setGroupSlotsByService()
    {
        $this->group_slots_by = 'service';
    }

    /**
     * @return bool
     */
    public function isGroupSlotsByService()
    {
        if ($this->group_slots_by === 'service') {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param string $color
     */
    public function setPriceTagColor($color)
    {
        $this->price_tag_color = $color;
    }

    /**
     * @return string
     */
    public function getPriceTagColor()
    {
        if (!$this->price_tag_color) {
            return 'yellow';
        } else {
            return $this->price_tag_color;
        }
    }

    /**
     * @param int $style
     */
    public function setSlotStyle($style)
    {
        $this->slot_style = (int)$style;
    }

    /**
     * @return int
     */
    public function getSlotStyle()
    {
        return NULL !== $this->slot_style ? $this->slot_style : 0;
    }

    /**
     * @param $bool
     */
    public function setSilentDebug($bool)
    {
        $this->silent_debug = (bool)$bool;
    }

    /**
     * @return mixed
     */
    public function getSilentDebug()
    {
        return (bool)$this->silent_debug;
    }

    /**
     * @param $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param $value
     */
    public function setAutofillReservationForm($value)
    {
        $this->autofill_reservation_form = TRUE;
        if ($value === 'no') {
            $this->autofill_reservation_form = FALSE;
        } elseif ($value === 'hide') {
            $this->autofill_reservation_form = 'hide';
        }
    }

    /**
     * @return bool
     */
    public function getAutofillReservationForm()
    {
        return $this->autofill_reservation_form;
    }

    /**
     * @param $bool
     */
    public function setShowFirstMonthWithFreeSlot($bool)
    {
        $this->first_month_with_free_slot_is_shown = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function isFirstMonthWithFreeSlotShown()
    {
        return isset($this->first_month_with_free_slot_is_shown) ? (bool)$this->first_month_with_free_slot_is_shown : FALSE;
    }

    /**
     * @param $seconds
     */
    public function setDatabaseReservationTimeout($seconds)
    {
        $this->database_reservation_timeout = $seconds;
    }

    /**
     * @return int
     */
    public function getDatabaseReservationTimeout()
    {
        return isset($this->database_reservation_timeout) ? $this->database_reservation_timeout : 0;
    }

    /**
     * @param $seconds
     */
    public function setMaxPendingTime($seconds)
    {
        if ($seconds < 900 && $seconds > 0) $seconds = 900; //15 min
        $this->max_pending_time = $seconds;
    }

    /**
     * @return int
     */
    public function getMaxPendingTime()
    {
        return isset($this->max_pending_time) ? $this->max_pending_time : 3600;
    }

    /**
     * @param string $url
     */
    public function setRegistrationUrl($url)
    {
        $this->registration_url = $url;
    }

    /**
     * @return string
     */
    public function getRegistrationUrl()
    {
        if (empty($this->registration_url)) {
            return wp_registration_url();
        } else {
            return $this->registration_url;
        }
    }

    /**
     * @param string $url
     */
    public function setLoginUrl($url)
    {
        $this->login_url = $url;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        if (empty($this->login_url)) {
            return wp_login_url();
        } else {
            return $this->login_url;
        }
    }

    /**
     * @param $bool
     */
    public function setRedirectBackAfterLogin($bool)
    {
        $this->redirect_back_after_login = (bool)$bool;
    }

    /**
     * @return bool
     */
    public function getRedirectBackAfterLogin()
    {
        if (NULL === $this->redirect_back_after_login) {
            return TRUE;
        }

        return $this->redirect_back_after_login;
    }

    /**
     * @param TeamBooking_PaymentGateways_Settings $object
     */
    public function addPaymentGatewaySettingObject(TeamBooking_PaymentGateways_Settings $object)
    {
        $this->payment_gateways[ $object->getGatewayId() ] = $object;
    }

    /**
     * @return TeamBooking_PaymentGateways_Settings[]
     */
    public function getPaymentGatewaySettingObjects()
    {
        return isset($this->payment_gateways) ? $this->payment_gateways : array();
    }

    /**
     * @param $id
     */
    public function dropPaymentGatewaySettingObject($id)
    {
        unset($this->payment_gateways[ $id ]);
    }

    /**
     *
     * @param string $gateway_id
     *
     * @return boolean|TeamBooking_PaymentGateways_Settings|TeamBooking_PaymentGateways_PayPal_Settings|TeamBooking_PaymentGateways_Stripe_Settings
     */
    public function getPaymentGatewaySettingObject($gateway_id)
    {
        return isset($this->payment_gateways[ $gateway_id ]) ? $this->payment_gateways[ $gateway_id ] : FALSE;
    }

    /**
     * @return TeamBooking_PaymentGateways_Settings[]
     */
    public function getPaymentGatewaysActive()
    {
        $results_array = array();
        foreach ($this->getPaymentGatewaySettingObjects() as $gateway) {
            /* @var $gateway TeamBooking_PaymentGateways_Settings */
            if ($gateway->isActive()) {
                $results_array[] = $gateway;
            }
        }

        return $results_array;
    }

    /**
     * @return bool
     */
    public function thereIsAtLeastOneActivePaymentGateway()
    {
        $active_gateways = $this->getPaymentGatewaysActive();

        return !empty($active_gateways);
    }

    /**
     * @param $code
     */
    public function setCurrencyCode($code)
    {
        $this->currency_code = $code;
    }

    /**
     * @return mixed
     */
    public function getCurrencyCode()
    {
        return isset($this->currency_code) ? $this->currency_code : 'USD';
    }

    /**
     * @return array
     */
    public function getContinentsAllowed()
    {
        if (NULL !== $this->continents_allowed) {
            return $this->continents_allowed;
        } else {
            return array(
                'Africa'     => TRUE,
                'America'    => TRUE,
                'Antarctica' => TRUE,
                'Arctic'     => TRUE,
                'Asia'       => TRUE,
                'Atlantic'   => TRUE,
                'Australia'  => TRUE,
                'Europe'     => TRUE,
                'Indian'     => TRUE,
                'Pacific'    => TRUE
            );
        }
    }

    /**
     * @param $continent
     * @param $bool
     */
    public function setContinentAllowed($continent, $bool)
    {
        $continents = $this->getContinentsAllowed();
        if (isset($continents[ $continent ])) {
            $continents[ $continent ] = (bool)$bool;
            $this->continents_allowed = $continents;
        }
    }

    /**
     * @param $continent
     *
     * @return bool|mixed
     */
    public function getContinentAllowed($continent)
    {
        $continents = $this->getContinentsAllowed();
        if (isset($continents[ $continent ])) {
            return $continents[ $continent ];
        } else {
            return FALSE;
        }
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return isset($this->tokens) ? $this->tokens : array();
    }

    /**
     * @param bool $write
     */
    public function addToken($write = FALSE)
    {
        $token = TeamBooking\Toolkit\generateToken();
        $this->tokens[ $token ] = array(
            'write'  => $write,
            'usages' => array() // operation => number_of_calls
        );
    }

    /**
     * @param $operation
     * @param $token
     *
     * @return bool
     */
    public function incrementTokenUsage($operation, $token)
    {
        if (!isset($this->tokens[ $token ])) return FALSE;
        if (!isset($this->tokens[ $token ]['usages'][ $operation ])) {
            $this->tokens[ $token ]['usages'][ $operation ] = 1;
        } else {
            $this->tokens[ $token ]['usages'][ $operation ]++;
        }
        $this->save();

        return TRUE;
    }

    /**
     * @param $token
     *
     * @return bool|int
     */
    public function getTotalTokenUsages($token)
    {
        if (!isset($this->tokens[ $token ])) return FALSE;
        if (!isset($this->tokens[ $token ]['usages'])) return 0;
        $num = 0;
        foreach ($this->tokens[ $token ]['usages'] as $operation => $number) {
            $num += $number;
        }

        return $num;
    }

    /**
     * @param $token
     */
    public function revokeToken($token)
    {
        if (isset($this->tokens[ $token ])) unset($this->tokens[ $token ]);
    }

    /**
     * @return mixed
     */
    public function save()
    {
        return update_option('team_booking', $this);
    }

    public function clean()
    {
        unset($this->paypal_email,
            $this->paypal_sandbox_test,
            $this->paypal_save_ipn_logs,
            $this->after_payment_url,
            $this->max_pending_time,
            $this->revoke_tokens_on_uninstall);
    }

}
