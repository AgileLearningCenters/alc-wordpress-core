<?php
class buat_admin_options {

    private $settings_api;

    function __construct() {
        $this->settings_api = WpBpShop_Settings_API::getInstance();

        add_action( 'admin_init', array($this, 'admin_init') );
        if(!is_multisite())
        add_action('admin_menu', array($this, 'admin_menu'));
        if(is_multisite())
        add_action('network_admin_menu', array($this, 'admin_menu'));
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_menu_page('Buddypress user account type', 'BUAT lite settings', 10, 'buddypress_user_account_type_lite_settings', array($this, 'plugin_page') );
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'buat_basic_setting',
                'title' => __( 'Basic Settings', 'buat' )
            ),
            array(
                'id' => 'buat_profile_data_setting',
                'title' => __( 'Profile Data Settings', 'buat' )
            ),
            array(
                'id' => 'buat_style_setting',
                'title' => __( 'Style Settings', 'buat' )
            ),
            array(
                'id' => 'buat_access_setting',
                'title' => __( 'Access Management and Shortcodes', 'buat' )
            )
        );
        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        do_action('buat_before_save_basic_data');
        $current_basic_setting = get_option('buat_basic_setting',true);
        do_action('buat_after_save_basic_data',$current_basic_setting);
        //////////////////////////////////////////////////////////////////////////////////////
        //                              Basic settings page
        //////////////////////////////////////////////////////////////////////////////////////
        $buat_basic_setting = array(array(
                    'name' => 'buat_type_field_selection',
                    'class' => 'buat_type_field_selection',
                    'label' => __( 'Select type defining field', 'buat' ),
                    'desc' => __( 'User will select his/her own type during registration, which field will define user types', 'buat' ),
                    'type' => 'select',
                    'options' => array_merge(array('null' => '------'),buat_get_all_select_box_fields()) 
                    ));
        if(isset($_POST['buat_default_type_selection'])){
            $current_basic_setting['buat_default_type_selection'] = $_POST['buat_default_type_selection'];
            $current_basic_setting['buat_is_triggerd'] = 0;
        }
        if(isset($_POST['buat_selected_field']) || $current_basic_setting['buat_default_type_selection'])
            $not_configured = true;
        else
            $not_configured = false;
        if($not_configured) {
            $field_name = $current_basic_setting['buat_type_field_selection'];
            if(isset($_POST['buat_selected_field'])) {
                $field_name = $_POST['buat_selected_field'];
            }
            $current_basic_setting['buat_type_field_selection'] = $field_name;
            update_option('buat_basic_setting',$current_basic_setting);
            $field_id = buat_get_field_id_by_name($field_name);
            $type_names = buat_get_all_types($field_id);
            $pages = get_pages(array ('sort_order' => 'ASC','post_type' => 'page','post_status' => 'publish','sort_column' => 'post_title'));
            $page_list_arr['0'] = '-------------'; 
            foreach($pages as $page) {
                $page_list_arr[$page->ID] = $page->post_title;
            }
            
            foreach((array)$type_names as $val) {
                $i++;
                $arr[$val['name']] = $val['name'];
                $page_list[$i] = array(
                    'name' => 'buat_page_selection_for_'.$val['name'],
                    'class' => 'buat_page_selection_for_'.$val['name'],
                    'label' => __( sprintf('Select page to show <strong>%1$s</strong> type users', $val['name']), 'buat' ),
                    'desc' => __( sprintf('<strong>%1$s</strong> type users list will be shown in this page', $val['name']), 'buat' ),
                    'type' => 'select',
                    'options' =>  $page_list_arr  
                );
                $roles_list[$i] = array(
                    'name' => 'buat_role_selection_for_'.$val['name'],
                    'class' => 'buat_role_selection_for_'.$val['name'],
                    'label' => __( sprintf('Select role for <strong>%1$s</strong> type users', $val['name']), 'buat' ),
                    'desc' => __( sprintf('<strong>%1$s</strong> type users role, by default,subscriber ', $val['name']), 'buat' ),
                    'type' => 'select',
                    'options' =>  buat_get_all_roles()  
                );
            }
            
            
            $exisiting_roles = array(
                    array(
                    'name' => 'buat_manage_existing_users',
                    'class' => 'buat_manage_existing_users',
                    'label' => __( 'Whats about existing users?', 'buat' ),
                    'desc' => __( 'What you want to do with exisitng users, you may assign then all to default type,<br>
                                   or can change type according to their role','buat' ),
                    'type' => 'radio',
                    'options' => array(
                            'false' => 'Do Nothing',
                            'role_to_type' => 'Assign type according to role',
                            'default_type_to_all' => 'Assign default type and role to all',
                            )
                     ),
                );
            
            foreach( buat_get_all_roles() as $role ) {
                $j++;
                $options = array_merge(array('false' => '----------'),buat_get_all_types($field_id,'name','single'));
                $exisiting_roles_to_type[$j] = array(
                    'name' => 'buat_role_to_type_for_'.strtolower($role),
                    'class' => 'buat_role_to_type_for_'.$role.' buat_role_to_type',
                    'label' => __( sprintf('Select type for <strong>%1$s</strong> users', $role), 'buat' ),
                    'desc' => __( sprintf('<strong>%1$s</strong> users will be converted to seleted type', $role), 'buat' ),
                    'type' => 'select',
                    'options' => $options,
                    'default' => 'false'
                );
            }
            $exisiting_roles = array_merge($exisiting_roles,$exisiting_roles_to_type);
            /*
                $exisiting_roles = array(
                    array(
                        'name' => 'buat_change_all_existing_users_role',
                        'class' => 'buat_change_all_existing_users_role',
                        'label' => __( sprintf('Change current users role according to type', $role), 'buat' ),
                        'desc' => __( sprintf('You can update current user roles depanding on user type, that you selected before', $role), 'buat' ),
                        'type' => 'select',
                        'options' => array('true'=>'Yes','false' => 'No'),
                        'default' => 'false'
                ),
                    array(
                        'name' => 'buat_manage_existing_users',
                        'class' => 'buat_manage_existing_users',
                        'type' => 'hidden',
                        'value' => 'false'
                        ),
                );
            */
            
            $conditionals = array(
                'name' => 'buat_default_type_selection',
                'class' => 'buat_default_type_selection',
                'label' => __( 'Select default type', 'buat' ),
                'desc' => __( 'User will obtain this type by default', 'buat' ),
                'type' => 'select',
                'options' => $arr
            );
            
            
            $more_basic_fields = array(
                
                array(
                'name' => 'buat_exclude_id_for_roles',
                'class' => 'buat_exclude_id_for_roles',
                'label' => __( 'Exclude IDs from change role<br>( comma sapareted )', 'buat' ),
                'desc' => __( '<br>You may exclude some ids to prevent them from changing roles,admin\'s id (1) will be excluded bu default','buat' ),
                'type' => 'text',
                'default' => '1'
                ),
                array(
                'name' => 'buat_users_per_page',
                'class' => 'buat_users_per_page',
                'label' => __( 'Users to display per page', 'buat' ),
                'desc' => __( '<br>By default, BuddyPress shows 10 users per page, how many users you want to show per page?','buat' ),
                'type' => 'text',
                'default' => '10'
                ),
                array(
                'name' => 'buat_can_user_change_type',
                'class' => 'buat_can_user_change_type',
                'label' => __( 'Can user change type after registration?', 'buat' ),
                'desc' => __( 'User may change own type after registration from his/her buddypress profile, Do you want to allow this? By default, No
                            <br> If you select \'No\', Then, only <strong>site admin</strong> can view or change user type','buat' ),
                'type' => 'radio',
                'options' => array(
                        'true' => 'Yes',
                        'false' => 'No',
                        ),
                 'default' => array('false' => 'No')
                 ),
                array(
                'name' => 'buat_is_triggerd',
                'class' => 'buat_is_triggerd',
                'type' => 'hidden',
                'value' => 0
                ),
                
            );
            $buat_basic_setting = array_merge($buat_basic_setting,array($conditionals));
            $buat_basic_setting = array_merge($buat_basic_setting,$page_list,$roles_list,(array)$exisiting_roles, $more_basic_fields);
                  
        }
        
        
        $html = array(
            'type' => 'html',
            'value' => '<div id="buatp_pro">
                <h4>This feature is for BuddyPress User Account Type PRO version</h4>
                <a href="http://wpbpshop.com/buddypress-user-account-type-pro" target="_blank" id="get_the_pro">Get the pro</a>
                <div>'
        );
        
        $buat_profile_data_setting = array($html);
        $buat_style_setting = array($html);
        $buat_access_setting = array($html);
        
        
        
        $settings_fields = array(
            'buat_basic_setting' => $buat_basic_setting,
            'buat_profile_data_setting' => $buat_profile_data_setting,
            'buat_style_setting' => $buat_style_setting,
            'buat_access_setting' => $buat_access_setting
        );

        return $settings_fields;
    }

    function plugin_page() {
        echo '<div id="buat_setting_page_wraper">';
        echo '<div class="wrap">';
        settings_errors();
        if(!buat_check_type_field_exist()) {
            echo '<div class="postbox"><div style="margin-top: 20px; margin-left: 20px;">';
            $url = admin_url('admin.php?page=bp-profile-setup');
            if(is_multisite())
            $url = network_admin_url('admin.php?page=bp-profile-setup');
            echo __('<p>Theres no "Drop Down Select Box" field found at Buddypress profile fields</p>','buat');
            echo __('<p>Create a "Drop Down Select Box" type profile filed from <a href="'.$url.'">Buddypress->profile fields</a>, 
                    create a new field, Name it as you want, e.g. User Type, select "Field Type" as "Drop Down Select Box"
                    and create some types, e.g type 1, type 2</p>','buat');
            echo __('<p>It is essential to create "Drop Down Select Box" field to detemine user type. Read the manual to know more</p>','buat');
            echo '</div></div></div>';
            return;
        }
        echo '<div id="buat_hidden_fields"></div>';
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        do_action('buat_trigger');
        echo '</div> </div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

}

$settings = new buat_admin_options();
?>
