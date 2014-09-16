<?php
function buat_cookie_page_id(){
   $current_page = get_the_ID();
   ?>
    <script language="javascript" >
        var j = jQuery;
        j(document).ready(function(){
            j.cookie('buat_current_page_id',<?php echo $current_page ?>, { path: '/'});
        });
    </script>
<?php
}
add_action('wp_head','buat_cookie_page_id',100);

///////////////////////////////////////////////////////////////////////////////////////
//                          Hooks to filter users
///////////////////////////////////////////////////////////////////////////////////////

function buat_select_template(){
    if(is_admin() || !get_the_ID())
        return;
    $do_redirect = false;
    if( get_the_ID() == buat_current_page_type()) {
        include_once( apply_filters('buat_template',BUAT_TEMPLATE.'index.php') );
       $do_redirect = true;
    }
    if(!$do_redirect)
        return;
    die();
}
add_action('template_redirect','buat_select_template');

///////////////////////////////////////////////////////////////////////////////////////

function buat_display_numbers_of_user_filter($query , $object){
    if($object != 'members')
        return $query;
    $settings = get_option('buat_basic_setting',true);
    return $query.'&per_page='.$settings['buat_users_per_page'];
}
add_filter('bp_dtheme_ajax_querystring','buat_display_numbers_of_user_filter',2,2);
add_filter('bp_ajax_querystring','buat_display_numbers_of_user_filter',2,2);

///////////////////////////////////////////////////////////////////////////////////////

function buat_filter_users_by_type($query, $object){
    if($object != 'members')
        return $query;
    $excludes = buat_get_filtered_members('exclude');
    $query = 'exclude='.implode(',',(array)$excludes).'&'.$query;
    return apply_filters('buat_ajax_query_string',$query);
}
add_filter('bp_dtheme_ajax_querystring','buat_filter_users_by_type',1001,2);
add_filter('bp_ajax_querystring','buat_filter_users_by_type',1001,2);


///////////////////////////////////////////////////////////////////////////////////////
//                          Hooks to trigger roles
///////////////////////////////////////////////////////////////////////////////////////

function buat_triger_existing_users(){
    $settings = get_option('buat_basic_setting',true);
    if((int) $settings['buat_is_triggerd'])
        return;
    $users = get_users();
    $excludes = explode(',',$settings['buat_exclude_id_for_roles'] ? trim($settings['buat_exclude_id_for_roles']) : 1);
    if($settings['buat_default_type_selection']){
        $field_name = $settings['buat_type_field_selection'];
        $field_id = buat_get_field_id_by_name($field_name);
        $type_names = buat_get_all_types($field_id);
        foreach((array)$type_names as $val){
            $excludes_from_type = array_merge((array)$excludes_from_type,buat_get_filtered_members('include' , $val['name']),$excludes);
        }
        if($settings['buat_manage_existing_users'] == 'default_type_to_all' || $settings['buat_manage_existing_users'] == 'role_to_type'):
            foreach((array)$users as $user){
                if(in_array($user->ID, $excludes_from_type))
                    continue;
                
                if($settings['buat_manage_existing_users'] == 'default_type_to_all'){
                    
                    if(!buat_get_field_data($field_name,$user->ID))
                    buat_set_field_data($field_name,$user->ID,$settings['buat_default_type_selection']);
                    $default = $settings['buat_default_type_selection'];
                    buat_update_user_role($user->ID,$settings['buat_role_selection_for_'.$default]);
                }
                if($settings['buat_manage_existing_users'] == 'role_to_type'){
                        $role = buat_get_user_role($user->ID);
                        $type = $settings['buat_role_to_type_for_'.strtolower($role)];
                        if($type == 'false')
                            continue;
                        buat_set_field_data($field_name,$user->ID,$type);
                }
            }
        endif;
        if($settings['buat_change_all_existing_users_role'] == 'true'):
            foreach((array)$type_names as $val){
                if(!$settings['buat_role_selection_for_'.$val['name']])
                    break;
                $users = buat_get_filtered_members('include' , $val['name']);
                foreach((array)$users as $id){
                    if(in_array($id, $excludes) || is_super_admin($id))
                        continue;
                    buat_update_user_role($id,$settings['buat_role_selection_for_'.$val['name']]);
                }
            }
        endif;
    }
    
    $settings['buat_is_triggerd'] = 1;
    update_option('buat_basic_setting',$settings);  
}
add_action('buat_trigger','buat_triger_existing_users',1);

///////////////////////////////////////////////////////////////////////////////////////

function buat_trigger_role_at_registration($user_id, $user_login, $user_password, $user_email, $usermeta){
    if(!$user_id)
        return;
    $settings = get_option('buat_basic_setting',true);
    $field_name = $settings['buat_type_field_selection'];
    $field_id = buat_get_field_id_by_name($field_name);
    $type_name = $usermeta["field_$field_id"];
    if(!$settings['buat_role_selection_for_'. $type_name])
        return;
    buat_update_user_role($user_id,$settings['buat_role_selection_for_'. $type_name]);
}
add_action('bp_core_signup_user','buat_trigger_role_at_registration',10,5);

///////////////////////////////////////////////////////////////////////////////////////

function buat_trigger_role_at_profile_update(){
    global $bp;
    $settings = get_option('buat_basic_setting',true);
    if($settings['buat_can_user_change_type'] == 'false')
        return;
    $user_id = $bp->displayed_user->id;
    $excludes = explode(',',$settings['buat_exclude_id_for_roles'] ? trim($settings['buat_exclude_id_for_roles']) : 1);
    if(in_array($user_id,$excludes) || is_super_admin($user_id))
        return;
    $field_name = $settings['buat_type_field_selection'];
    $type_name = buat_get_field_data($settings['buat_type_field_selection'], $user_id);
    buat_update_user_role($user_id,$settings['buat_role_selection_for_'. $type_name]);
}
add_action('xprofile_screen_edit_profile','buat_trigger_role_at_profile_update');

///////////////////////////////////////////////////////////////////////////////////////

function buat_protect_chaging_type_by_force($field_id){
    global $bp;
    $settings = get_option('buatp_basic_setting',true);
    if( $bp->current_action != 'edit' && $bp->current_component != 'profile' )
        return $field_id;
    if(current_user_can('create_users'))
        return $field_id;
    $type_field =  $settings['buatp_type_field_selection'];
    $type_field_id = buat_get_field_id_by_name($type_field);
   
     if( $settings['buatp_can_user_change_type'] == 'false' && $type_field_id == $field_id)
         return false;
     return $field_id;
}
add_filter('xprofile_data_field_id_before_save','buat_protect_chaging_type_by_force',1,1);


///////////////////////////////////////////////////////////////////////////////////////
//                          Hooks to filter profile fields
///////////////////////////////////////////////////////////////////////////////////////

                            // for PRO version only

///////////////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////////////

function buat_member_count($count){
    if(!buat_get_dir_name())
        return $count;
    return count(buat_get_all_users_by_type(buat_get_dir_name()));
}
add_filter('bp_get_total_member_count','buat_member_count',100);

///////////////////////////////////////////////////////////////////////////////////////
//                          Hooks to Styling
///////////////////////////////////////////////////////////////////////////////////////

                            // for PRO version only

?>