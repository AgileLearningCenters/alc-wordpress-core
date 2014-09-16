<?php

function buat_get_all_field_groups($condition = ''){
    global $wpdb;
    $query = "SELECT * FROM ".$wpdb->base_prefix."bp_xprofile_groups $condition";
    $groups = $wpdb->get_results($query, ARRAY_A);
    foreach((array) $groups as $group){
        $ids[$i++] = $group['id'];
    }
    return (array) $ids;
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_get_all_fields($condition = "WHERE parent_id = 0" , $return = 'name'){
   global $wpdb;
   $query="SELECT * FROM ".$wpdb->base_prefix."bp_xprofile_fields $condition";
   $fields=$wpdb->get_results($query,ARRAY_A);
   if(!count($fields))
       return array();
   foreach($fields as $field) {
       $name = $field[$return];
       $arr[$name] = $name;
   }
   return $arr;
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_get_all_select_box_fields() {
   global $wpdb;
   $query="SELECT * FROM ".$wpdb->base_prefix."bp_xprofile_fields WHERE type='selectbox'";
   return buat_get_all_fields("WHERE type='selectbox'");
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_check_type_field_exist(){
   $fields = buat_get_all_select_box_fields();
   if(is_array($fields) && count($fields))
       return true;
   else
       return false;
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_get_all_types($field_id , $selection = '*', $output = 'multidimantion'){
    global $wpdb;
    $query="SELECT $selection FROM ".$wpdb->base_prefix."bp_xprofile_fields WHERE type='option' AND parent_id='".$field_id."'";
    $types=$wpdb->get_results($query,ARRAY_A);
    if(count($types)){
        if($output == 'multidimantion')
        return $types;
        else {
            foreach ($types as $val) {
                $arr[$val[$selection]] = $val[$selection];
            }
            return (array) $arr;
        }
    }
    else
        return false;
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_get_field_id_by_name($name) {
    return xprofile_get_field_id_from_name($name);
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_get_all_roles() {
	$editable_roles = get_editable_roles();
        foreach ( $editable_roles as $role => $details ) {
		$name = translate_user_role($details['name'] );
                $arr[esc_attr($role)] = $name;
	}
	return array_reverse((array)$arr);
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_get_dir_name(){
    $settings = get_option('buat_basic_setting',true);
    $page_id = get_the_ID();
    $field_name = $settings['buat_type_field_selection'];
    $field_id = buat_get_field_id_by_name($field_name);
    $type_names = buat_get_all_types($field_id);
    foreach ((array)$type_names as $val){
        if($settings['buat_page_selection_for_'.$val['name']] == $page_id ){
            return $val['name'];
        }     
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_the_dir_name(){
    echo buat_get_dir_name();
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_get_all_users_by_type($type_name){
    global $wpdb;
    $buat_general_settings = get_option('buat_basic_setting',true);
    $field_id = buat_get_field_id_by_name($buat_general_settings['buat_type_field_selection']);
    $query = "SELECT user_id FROM ".$wpdb->base_prefix."bp_xprofile_data WHERE field_id = $field_id AND value = '$type_name'";
    $users = $wpdb->get_results($query,ARRAY_A);
    if(!count($users))
        return 0;
    foreach($users as $user) {
        $ids[$i++] = $user['user_id'];
    }
    return apply_filters('buat_get_all_users_by_type',$ids);
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_current_page_type($return = 'page_id') {
    $buat_general_settings = get_option('buat_basic_setting',true);
    $current_page = (int)get_the_ID();
    if($_POST['action'] == 'members_filter')
    $current_page = $_COOKIE['buat_current_page_id'];
    foreach((array)$buat_general_settings as $index => $val){
       if(strpos($index, 'page_selection_for_'))	
           if($current_page == $val){
               if($return == 'page_id')
                   return $val;
               if($return == 'type_name')
                   return str_replace('buat_page_selection_for_', '', $index);
           }          
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_get_filtered_members($return = 'exclude' , $type_name = '' , $query = '') {
    if(!$type_name)
        $type_name = buat_current_page_type('type_name');
    if(!$type_name && $return != 'all')
        return;
    if(!$query)
        $query = 'type=alphabetical&per_page=false';
    $users = (array) buat_get_all_users_by_type($type_name);
    if ( bp_has_members( $query ) ): 
        while ( bp_members() ) : bp_the_member(); $i++;
           if(!in_array(bp_get_member_user_id(), $users)) {
                   $excludes[$i] = (int)bp_get_member_user_id(); 
           }
           else { 
                    $includes[$i] = (int)bp_get_member_user_id();
           }
        endwhile;
    endif;
    if($return == 'exclude')
        return (array) $excludes;
    else if($return == 'include')
        return (array) $includes;
    else 
        return array_merge((array) $excludes , (array) $includes); 
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_get_field_data($field_name,$user_id){
    if(!$field_name || !$user_id)
        return;
    return bp_get_profile_field_data(array(
		'field' => $field_name, 
		'user_id' => $user_id
	));
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_set_field_data($field_name,$user_id,$value){
    if(!$field_name || !$user_id)
        return;
    return xprofile_set_field_data($field_name,$user_id,$value,false);
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_update_user_role($user_id,$role) {
    if(!$user_id || !$role)
        return;
    $user = new WP_User( $user_id );
    $user->set_role($role);
}

//////////////////////////////////////////////////////////////////////////////////////////////

function buat_get_user_role($user_id){
    if(!$user_id)
        return;
    $user = new WP_User( $user_id );
    //print_r($user);
    return $user->roles[1];
}

//////////////////////////////////////////////////////////////////////////////////////////////////

function buat_reset_settings(){
    update_option('buat_basic_setting','');
    update_option('buat_profile_data_setting','');
    update_option( 'buat_style_setting',''); 
}

//////////////////////////////////////////////////////////////////////////////////////////////

?>