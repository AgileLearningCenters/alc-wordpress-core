<?php
/*
Copyright 2013  I.T.RO.® (email : support.itro@live.com)
This file is part of ITRO Popup Plugin.
*/
global $wpdb;
define ('OPTION_TABLE_NAME', $wpdb->prefix . 'itro_plugin_option');
define ('FIELD_TABLE_NAME', $wpdb->prefix . 'itro_plugin_field');

/* -------Create plugin tables */
function itro_db_init()
{
	global $wpdb;
	/* ------------------Option table */
	$option_table_name = OPTION_TABLE_NAME;
	$sql = "CREATE TABLE IF NOT EXISTS $option_table_name 
	(
	option_id int NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(option_id),
	option_name varchar(255),
	option_val varchar(255)
	) CHARACTER SET=utf8 COLLATE utf8_general_ci";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	/* --------------Custom field table */
	$field_table_name = FIELD_TABLE_NAME;
	$sql = "CREATE TABLE IF NOT EXISTS $field_table_name
	(
	field_id int NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(field_id),
	field_name varchar(255),
	field_value TEXT
	) CHARACTER SET=utf8 COLLATE utf8_general_ci";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );	
}

/* update old fixed 'wp_' prefix to the current one' */
function itro_update_db()
{
	global $wpdb;
	if ( get_option('itro_prev_ver') <= 3.68 )
	{
		itro_update_option('popup_border_width',3);
		itro_update_option('popup_border_radius',8);
	}
	
	if( get_option('itro_prev_ver') <= 4.6 && $wpdb->prefix != 'wp_' )
	{
		$wpdb->query("RENAME TABLE wp_itro_plugin_option TO ". $wpdb->prefix ."itro_plugin_option");
		$wpdb->query("RENAME TABLE wp_itro_plugin_field TO ". $wpdb->prefix ."itro_plugin_field");
	}
}

/* ------------------ PLUGIN OPTION DB MANAGEMENT --------------  */
function itro_update_option($opt_name,$opt_val)
{
	global $wpdb;
	$option_table_name = OPTION_TABLE_NAME;
	$data_to_send = array('option_val'=> $opt_val);
	$where_line = array('option_name' => $opt_name);
	$wp_query = $wpdb->get_results("SELECT * FROM $option_table_name WHERE option_name='$opt_name'");
	if ( $wp_query == NULL )
	{
		$wpdb->insert( $option_table_name , $where_line);
		$wpdb->update( $option_table_name , $data_to_send, $where_line );
	}
	else
	{
		$wpdb->update( $option_table_name , $data_to_send, $where_line );
	}
}

function itro_get_option($opt_name)
{
	global $wpdb;
	$option_table_name = OPTION_TABLE_NAME;
	$result = $wpdb->get_results("SELECT * FROM $option_table_name WHERE option_name='$opt_name'");
	foreach($result as $pippo)
	{
		$opt_val = $pippo->option_val;
	}
	if(isset($opt_val)) {return ($opt_val);}
	else {return (NULL);}
}

/* ------------------ CUSTOM FIELD CONTENT DB MANAGEMENT --------------  */
function itro_update_field($field_name,$field_value)
{
	global $wpdb;
	$field_table_name = FIELD_TABLE_NAME;
	$data_to_send = array('field_value'=> $field_value);
	$where_line = array('field_name' => $field_name);
	$wp_query = $wpdb->get_results("SELECT * FROM $field_table_name WHERE field_name='$field_name'");
	if ( $wp_query == NULL )
	{
		$wpdb->insert( $field_table_name , $where_line);
		$wpdb->update( $field_table_name , $data_to_send, $where_line );
	}
	else
	{
		$wpdb->update( $field_table_name , $data_to_send, $where_line );
	}
}

function itro_get_field($field_name)
{
	global $wpdb;
	$field_table_name = FIELD_TABLE_NAME;
	$result = $wpdb->get_results("SELECT * FROM $field_table_name WHERE field_name='$field_name'");
	foreach($result as $pippo)
	{
		$field_value = $pippo->field_value;
	}
	if(isset($field_value)) {return ($field_value);}
	else {return (NULL);}
}
?>