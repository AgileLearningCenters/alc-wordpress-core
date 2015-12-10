<?php
/*
  Plugin Name: Gravity Forms Agile CRM Add-On
  Plugin URI: https://www.agilecrm.com/gravity-forms
  Description: Agile CRM integration plugin for gravity forms. Sync form entries to Agile easily.
  Version: 1.0
  Requires at least: 4.0
  Tested up to: 4.1
  Author: Agile CRM
  Author URI: https://www.agilecrm.com
 */

defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

if (class_exists("GFForms") && !class_exists('AgileGFAddon')) {

    class AgileGFAddon
    {

        protected $tag = 'agile-gf-addon';
        private $account_settings_tab = 'account';
        private $form_settings_tab = 'form';
        private $plugin_settings_tabs = array();
        protected $name = 'Gravity Forms Agile CRM Add-On';
        protected $version = '1.0';

        function __construct()
        {
            //register actions or hooks
            add_action('init', array(&$this, 'start_session'));
            add_action('wp_footer', array(&$this, 'set_email'), 98765);

            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'add_menu'));

            add_action('gform_after_submission', array(&$this, 'sync_entries_to_agile'), 10, 2);

            add_action('wp_ajax_agilecrm_gf_load_fields', array(&$this, 'load_form_fields'));
            add_action('wp_ajax_agilecrm_gf_map_fields', array(&$this, 'map_form_fields'));
        }

        /**
         * Start PHP session if not started earlier
         */
        public function start_session()
        {
            if (!session_id()) {
                session_start();
            }
        }

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
            // Set up the settings for this plugin
            $this->init_settings();
            $this->plugin_settings_tabs[$this->account_settings_tab] = 'Account Details';
            $this->plugin_settings_tabs[$this->form_settings_tab] = 'Form Settings';
        }

        /**
         * Initialize some custom settings
         */
        public function init_settings()
        {
            // register the settings for this plugin
            register_setting($this->tag . '-settings-group', 'agilecrm_gf_domain');
            register_setting($this->tag . '-settings-group', 'agilecrm_gf_admin_email');
            register_setting($this->tag . '-settings-group', 'agilecrm_gf_api_key');

            register_setting($this->tag . '-settings-group1', 'agilecrm_gf_form_map');
            register_setting($this->tag . '-settings-group2', 'agilecrm_gf_contact_fields');
            register_setting($this->tag . '-settings-group3', 'agilecrm_gf_mapped_forms');

            add_settings_section($this->tag . '-section-one', '', '', $this->tag);
        }

        /**
         * add a menu
         */
        public function add_menu()
        {
            add_options_page('Settings-' . $this->name, 'Agile Gravity Forms', 'manage_options', $this->tag, array(&$this, 'plugin_settings_page'));
        }

        /**
         * Generate plugin setting tabs
         */
        public function plugin_settings_tabs()
        {
            $current_tab = (isset($_GET['tab']) && isset($this->plugin_settings_tabs[$_GET['tab']])) ? $_GET['tab'] : $this->account_settings_tab;

            echo '<h2 class="nav-tab-wrapper">';
            foreach ($this->plugin_settings_tabs as $tab_key => $tab_caption) {
                $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
                echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->tag . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
            }
            echo '</h2>';
        }

        /**
         * Menu Callback
         */
        public function plugin_settings_page()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            // Render the settings template based on the tab selected
            $current_tab = (isset($_GET['tab']) && isset($this->plugin_settings_tabs[$_GET['tab']])) ? $_GET['tab'] : $this->account_settings_tab;
            include(sprintf("%s/templates/" . $current_tab . "-tab.php", dirname(__FILE__)));
        }

        /**
         * Load form fields related to form id through Ajax
         */
        public function load_form_fields()
        {
            global $wpdb;
            $formId = $_POST['formid'];
            $form = RGFormsModel::get_form_meta($formId);
            $formFieldsOptions = '<option value="" selected="selected"></option>';

            if (is_array($form["fields"])) {
                foreach ($form["fields"] as $field) {
                    if (isset($field["inputs"]) && is_array($field["inputs"])) {
                        foreach ($field["inputs"] as $input) {
                            $formFieldsOptions .= '<option value="' . $input["id"] . '">' . GFCommon::get_label($field, $input["id"]) . '</option>';
                        }
                    } else if (!rgar($field, 'displayOnly')) {
                        $formFieldsOptions .= '<option value="' . $field["id"] . '">' . GFCommon::get_label($field) . '</option>';
                    }
                }
            }

            $agileFields = array(
                'first_name' => array('name' => 'First name', 'is_required' => true, 'type' => 'SYSTEM', 'is_address' => false),
                'last_name' => array('name' => 'Last name', 'is_required' => true, 'type' => 'SYSTEM', 'is_address' => false),
                'company' => array('name' => 'Company', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => false),
                'title' => array('name' => 'Job description', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => false),
                'tags' => array('name' => 'Tag', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => false),
                'email' => array('name' => 'Email', 'is_required' => true, 'type' => 'SYSTEM', 'is_address' => false),
                'phone' => array('name' => 'Phone', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => false),
                'website' => array('name' => 'Website', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => false),
                'address_address' => array('name' => 'Address', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => true),
                'address_city' => array('name' => 'City', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => true),
                'address_state' => array('name' => 'State', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => true),
                'address_zip' => array('name' => 'Zip', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => true),
                'address_country' => array('name' => 'Country', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => true),
                'notes' => array('name' => 'Note', 'is_required' => false, 'type' => 'SYSTEM', 'is_address' => false)
            );

            $customFields = $this->agile_http("custom-fields/scope?scope=CONTACT", null, "GET");

            if ($customFields) {
                $customFields = json_decode($customFields, true);
                foreach ($customFields as $customField) {
                    $agileFields[AgileGFAddon::clean($customField['field_label'])] = array(
                        'name' => $customField['field_label'],
                        'is_required' => (boolean) $customField['is_required'],
                        'type' => 'CUSTOM,' . $customField['field_type'],
                        'is_address' => false
                    );
                }
            }

            update_option("agilecrm_gf_contact_fields", $agileFields);


            $mapFieldsMarkup = '';
            foreach ($agileFields as $fieldKey => $fieldVal) {
                $mapFieldsMarkup .= '<tr valign="top"><th scope="row">' . $fieldVal['name'];
                $required = '';
                if ($fieldVal['is_required']) {
                    $mapFieldsMarkup .= '<span style="color:#FF0000"> *</span>';
                    $required = 'class="required" required';
                }
                $mapFieldsMarkup .= '</th>';
                $mapFieldsMarkup .= '<td><select id="agilecrm_form_field_' . $fieldKey . '" name="agilecrm_gf_form_map[' . $fieldKey . ']"' . $required . '>' . $formFieldsOptions . '</select></td></tr>';
            }

            $agilecrm_gf_form_map = get_option('agilecrm_gf_form_map');

            $responseJson = array(
                'markup' => '',
                'selectedFields' => ($agilecrm_gf_form_map && isset($agilecrm_gf_form_map['form_' . $formId])) ? $agilecrm_gf_form_map['form_' . $formId] : array()
            );

            $responseJson['markup'] .= '<h3 class="title">Map Gravity form fields to Agile CRM contact properties</h3>';

            $responseJson['markup'] .= '<table class="form-table" style="width:33%"><tbody>';
            $responseJson['markup'] .= '<tr valign="top"><th scope="row">Agile property</th><td><strong>Form field</strong></td></tr>';
            $responseJson['markup'] .= $mapFieldsMarkup;
            $responseJson['markup'] .= '</tbody></table>';

            $responseJson['markup'] .= '<h3>Add a tag to all contacts created from this form</h3>';
            $responseJson['markup'] .= '<table class="form-table"><tbody><tr valign="top">'
                . '<th scope="row" style="width: 136px;">Tag</th>'
                . '<td><input type="text" name="agilecrm_gf_form_map[hard_tag]" id="agilecrm_form_field_hard_tag"><br>'
                . '<small>Tag name should start with an alphabet and can not contain special characters other than space and underscore.</small></td>'
                . '</tr></tbody></table>';

            echo json_encode($responseJson);
            die();
        }

        /**
         * Save form mapped fields to database via Ajax
         */
        public function map_form_fields()
        {
            global $wpdb;
            $agilecrm_gf_form_map = get_option('agilecrm_gf_form_map');
            $agilecrm_form_sync_id = $_POST['agilecrm_gf_sync_form'];

            //save checked forms ids  
            $agilecrm_gf_mapped_forms = get_option('agilecrm_gf_mapped_forms');
            if (isset($_POST['agilecrm_gf_mapped_forms'])) {
                $syncedForms = $_POST['agilecrm_gf_mapped_forms'];
                if (in_array($agilecrm_form_sync_id, $agilecrm_gf_mapped_forms)) {
                    $syncedForms = array();
                }
                if ($agilecrm_gf_mapped_forms != false) {
                    $syncedForms = array_merge($agilecrm_gf_mapped_forms, $syncedForms);
                }
            } else {
                $syncedForms = $agilecrm_gf_mapped_forms;
                if (($key = array_search($agilecrm_form_sync_id, $syncedForms)) !== false) {
                    unset($syncedForms[$key]);
                }
            }
            $update = update_option('agilecrm_gf_mapped_forms', $syncedForms);

            if (isset($_POST['agilecrm_gf_form_map'])) {
                $formFields['form_' . $agilecrm_form_sync_id] = $_POST['agilecrm_gf_form_map'];
                if ($agilecrm_gf_form_map != false) {
                    $formFields = array_merge($agilecrm_gf_form_map, $formFields);
                }

                if (isset($formFields['form_' . $agilecrm_form_sync_id]['hard_tag']) && $formFields['form_' . $agilecrm_form_sync_id]['hard_tag'] != '') {
                    $formFields['form_' . $agilecrm_form_sync_id]['hard_tag'] = trim($formFields['form_' . $agilecrm_form_sync_id]['hard_tag']);
                }

                $update = update_option('agilecrm_gf_form_map', $formFields);
            }

            echo ($update) ? '1' : '0';

            die();
        }

        /**
         * Syncs form entries to Agile CRM whenever a mapped form is submited.
         */
        public function sync_entries_to_agile($entry, $form)
        {
            $agilecrm_gf_form_map = get_option('agilecrm_gf_form_map');
            $agilecrm_gf_mapped_forms = get_option('agilecrm_gf_mapped_forms');

            $formId = $entry['form_id'];
            if ($formId) {
                if ($agilecrm_gf_mapped_forms && in_array($formId, $agilecrm_gf_mapped_forms)) {
                    if ($agilecrm_gf_form_map && isset($agilecrm_gf_form_map['form_' . $formId])) {

                        $agileFields = get_option('agilecrm_gf_contact_fields');
                        $mappedFields = $agilecrm_gf_form_map['form_' . $formId];
                        $contactProperties = array();
                        $addressProp = array();

                        foreach ($agileFields as $fieldKey => $fieldVal) {
                            if ($mappedFields[$fieldKey] != '') {

                                $fieldTypeArray = explode(",", $fieldVal['type']);
                                if (in_array('CUSTOM', $fieldTypeArray)) {
                                    $valueEntered = $entry[$mappedFields[$fieldKey]];
                                    if (isset($fieldTypeArray[1]) && $fieldTypeArray[1] == "DATE") {
                                        /*
                                          These formats are supported m/d/y, d-m-y, d.m.y, y/m/d, y-m-d
                                         */
                                        if ($valueEntered != "") {
                                            $valueEntered = strtotime($valueEntered . " 12:00:00");
                                        }
                                    }
                                    $contactProperties[] = array(
                                        "name" => $fieldVal['name'],
                                        "value" => $valueEntered,
                                        "type" => $fieldTypeArray[0]
                                    );
                                } elseif (in_array('SYSTEM', $fieldTypeArray)) {
                                    if ($fieldVal['is_address']) {
                                        $addressField = explode("_", $fieldKey);
                                        $addressProp[$addressField[1]] = $entry[$mappedFields[$fieldKey]];
                                    } else {
                                        if ($fieldKey != 'tags' && $fieldKey != 'notes') {
                                            $contactProperties[] = array(
                                                "name" => $fieldKey,
                                                "value" => $entry[$mappedFields[$fieldKey]],
                                                "type" => $fieldTypeArray[0]
                                            );
                                        }
                                    }
                                }
                            }
                        }

                        if ($addressProp) {
                            $contactProperties[] = array(
                                "name" => "address",
                                "value" => json_encode($addressProp),
                                "type" => "SYSTEM"
                            );
                        }

                        $finalData = array("properties" => $contactProperties);

                        //tags
                        $finalData['tags'] = array();

                        if ($mappedFields["tags"] != '') {
                            if (AgileGFAddon::startsWithNumber($mappedFields["tags"])) {
                                $entry[$mappedFields['tags']] = preg_replace('/[0-9]+/', '', mb_ereg_replace('[^ \w]+', '', $entry[$mappedFields['tags']]));
                            }
                            $finalData['tags'][] = preg_replace('!\s+!', ' ', mb_ereg_replace('[^ \w]+', '', trim($entry[$mappedFields['tags']])));
                        }
                        if ($mappedFields["hard_tag"] != '') {
                            $hardTags = explode(",", trim($mappedFields['hard_tag']));
                            foreach ($hardTags as $hTag) {
                                if (AgileGFAddon::startsWithNumber($hTag)) {
                                    $finalData['tags'][] = preg_replace('/[0-9]+/', '', mb_ereg_replace('[^ \w]+', '', trim($hTag)));
                                } else {
                                    $finalData['tags'][] = preg_replace('!\s+!', ' ', mb_ereg_replace('[^ \w]+', '', trim($hTag)));
                                }
                            }
                        }
                        $finalData['tags'] = array_filter($finalData['tags']);

                        if (isset($entry[$mappedFields['email']]) && $entry[$mappedFields['email']] != '') {
                            $resultedContact = array();
                            $isExistsResult = $this->agile_http("contacts/search/email/" . $entry[$mappedFields['email']], null, "GET");
                            if ($isExistsResult && $isExistsResult != '') {
                                //replaces long to string, then decode
                                $resultedContact = json_decode(preg_replace('/("\w+"):(\d+(\.\d+)?)/', '\\1:"\\2"', $isExistsResult), true);
                                if (isset($resultedContact['id'])) {

                                    foreach ($finalData['tags'] as $ntag) {
                                        $resultedContact['tags'][] = $ntag;
                                    }
                                    $resultedContact['tags'] = array_unique($resultedContact['tags']);
                                    if ($resultedContact['tags']) {
                                        $resultedContact['tags'] = array_values($resultedContact['tags']);
                                    }

                                    foreach ($finalData['properties'] as $prop) {
                                        foreach ($resultedContact['properties'] as $oldkey => $oldprop) {
                                            if ($oldprop["name"] == $prop["name"]) {
                                                if (trim($prop["value"]) != "") {
                                                    $resultedContact['properties'][$oldkey]["value"] = trim($prop["value"]);
                                                }
                                            }
                                        }
                                    }

                                    $finalData['id'] = sprintf('%.0f', $resultedContact['id']);
                                    $this->agile_http("contacts", json_encode($resultedContact), "PUT");
                                }
                            } else {
                                $createdResult = $this->agile_http("contacts", json_encode($finalData), "POST");
                                if ($createdResult) {
                                    $resultedContact = json_decode($createdResult, true);
                                    if (isset($resultedContact['id'])) {
                                        $finalData['id'] = sprintf('%.0f', $resultedContact['id']);
                                    }
                                }
                            }
                            //for web tracking
                            $_SESSION['agileCRMTrackEmail'] = $entry[$mappedFields['email']];


                            if (isset($finalData['id'])) {
                                //creating notes if it is mapped
                                if ($mappedFields["notes"] != '' && $entry[$mappedFields['notes']] != '') {
                                    $noteJson = json_encode(array(
                                        "subject" => "Note from Gravity forms",
                                        "description" => $entry[$mappedFields['notes']],
                                        "contact_ids" => array($finalData['id'])
                                    ));
                                    $this->agile_http("notes", $noteJson, "POST");
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * Set user entered email to track web activities
         */
        public function set_email()
        {
            if (isset($_SESSION['agileCRMTrackEmail'])) {
                echo '<script> ';
                echo 'if(typeof _agile != "undefined") { ';
                echo '_agile.set_email("' . $_SESSION['agileCRMTrackEmail'] . '");';
                echo ' }';
                echo ' </script>';
                unset($_SESSION['agileCRMTrackEmail']);
            }
        }

        /**
         * AgileCRM Request Wrapper function
         */
        public function agile_http($endPoint, $data, $requestMethod)
        {
            $agile_domain = get_option('agilecrm_gf_domain');
            $agile_email = get_option('agilecrm_gf_admin_email');
            $agile_api_key = get_option('agilecrm_gf_api_key');

            if ($agile_domain && $agile_email && $agile_api_key) {
                $agile_url = "https://" . $agile_domain . ".agilecrm.com/dev/api/";

                $ch = curl_init();
                //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                //curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                //curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);

                switch ($requestMethod) {
                    case "POST":
                        curl_setopt($ch, CURLOPT_URL, $agile_url . $endPoint);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        break;
                    case "GET":
                        curl_setopt($ch, CURLOPT_URL, $agile_url . $endPoint);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                        break;
                    case "PUT":
                        curl_setopt($ch, CURLOPT_URL, $agile_url . $endPoint);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        break;
                    case "DELETE":
                        curl_setopt($ch, CURLOPT_URL, $agile_url . $endPoint);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                        break;
                    default:
                        break;
                }

                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type : application/json; charset : UTF-8;', 'Accept: application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERPWD, $agile_email . ':' . $agile_api_key);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);

                $output = curl_exec($ch);
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($statusCode == 200) {
                    return $output;
                } elseif ($statusCode == 401) {
                    return false;
                }
            }

            return false;
        }

        /**
         * Sanitize custom field names, return value is used as a key.
         */
        public static function clean($string)
        {
            $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
            $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
            return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
        }

        /**
         * Checks whether a string starts with a number or not
         * 
         * @param string
         * @return boolean TRUE if string starts with a number, FALSE otherwise.
         */
        public static function startsWithNumber($str)
        {
            if (strlen($str) > 0) {
                if ($str[0] != null) {
                    return is_numeric($str[0]);
                } else {
                    return is_numeric($str);
                }
            }
            return false;
        }
    }

    //class end

    new AgileGFAddon();
}