<?php
$CI = get_instance();
$CI->load->database();
$CI->load->dbforge();


// update VERSION NUMBER INSIDE SETTINGS TABLE
$settings_data = array( 'value' => '5.14');
$CI->db->where('key', 'version');
$CI->db->update('settings', $settings_data);