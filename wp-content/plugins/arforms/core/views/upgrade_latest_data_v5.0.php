<?php

global $wpdb, $db_record, $MdlDb, $armainhelper, $arfieldhelper, $arsettingcontroller, $arfsettings, $arffield, $arfrecordmeta, $arfform, $style_settings, $maincontroller;

@ini_set('max_execution_time', 0);

if( !function_exists('is_plugin_active') ){
	require_once(ABSPATH.'/wp-admin/includes/plugin.php');
}

$wpdb->query( "DROP TABLE IF EXISTS `".$wpdb->prefix."arf_forms_backup`" );
$wpdb->query( "DROP TABLE IF EXISTS `".$wpdb->prefix."arf_fields_backup`" );

$wp_upload_dir = wp_upload_dir();
$backup_dir = $wp_upload_dir['basedir'].'/arforms/maincss_backup';
if( is_dir($backup_dir) ){
	arf_rmdir( $backup_dir );
}
/* REMOVE OLD EXISTING BACKUP FOLDER AND TABLES */

/* CREATING BACKUP OF TABLES */
$wpdb->query("CREATE TABLE `".$wpdb->prefix."arf_forms_backup` LIKE `".$MdlDb->forms."`");
$wpdb->query("INSERT `".$wpdb->prefix."arf_forms_backup` SELECT * FROM `".$MdlDb->forms."` WHERE is_template = 1");

$wpdb->query("CREATE TABLE `".$wpdb->prefix."arf_fields_backup` LIKE `".$MdlDb->fields."`");
$wpdb->query("INSERT `".$wpdb->prefix."arf_fields_backup` SELECT * FROM `".$MdlDb->fields."` WHERE form_id BETWEEN 1 AND 11");


$wp_upload_dir = wp_upload_dir();
$source_dir = $wp_upload_dir['basedir'].'/arforms/maincss';
$destination_dir = $wp_upload_dir['basedir'].'/arforms/maincss_backup';

if( is_dir( $destination_dir ) ){
	$destination_dir = $wp_upload_dir['basedir'].'/arforms/maincss_backup_45';
}

if( !is_dir( $destination_dir ) ){
	wp_mkdir_p($destination_dir);
}

for( $i = 1; $i <= 11; $i++ ){

    $source_path = $source_dir . '/maincss_' . $i . '.css';
    $destination_path = $destination_dir . '/maincss_' . $i . '.css';

    $source_path1 = $source_dir . '/maincss_materialize_' . $i . '.css';
    $destination_path1 = $destination_dir . '/maincss_materialize_' . $i . '.css';

    if( file_exists( $source_path ) ){
        @copy( $source_path, $destination_path );
    }

    if( file_exists( $source_path1 ) ){
        @copy( $source_path1, $destination_path1 );
    }

}
update_option( 'arf_4_5_update_date', date( 'Y-m-d H:i:s' ) );
$timestamp = strtotime('+1 month');
wp_schedule_single_event( $timestamp, 'arf_remove_backup_data' );


$arf_update_templates = true;
$wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->forms."` WHERE id < %d",12) );

$wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->fields."` WHERE form_id < %d", 12));

$arfsettings = get_transient('arf_options');

if (!is_object($arfsettings)) {
    if ($arfsettings) {
        $arfsettings = $arfsettings;
    } else {
        $arfsettings = get_option('arf_options');

        if (!is_object($arfsettings)) {
            if ($arfsettings){
                $arfsettings = $arfsettings;
            } else {
                $arfsettings = new arsettingmodel();
            }

            update_option('arf_options', $arfsettings);
            set_transient('arf_options', $arfsettings);
        }
    }
}

$arfsettings->set_default_options();

$style_settings = get_transient('arfa_options');
if (!is_object($style_settings)) {
    if ($style_settings) {
        $style_settings = $style_settings;
    } else {
        $style_settings = get_option('arfa_options');
        if (!is_object($style_settings)) {
            if ($style_settings){
                $style_settings = $style_settings;
            } else {
                $style_settings = new arstylemodel();
            }
            
            update_option('arfa_options', $style_settings);
            set_transient('arfa_options', $style_settings);
        }
    }
}

include(MODELS_PATH."/artemplate.php");
/* DELETE OLD TEMPLATES AND INSTALL NEW TEMPALTES */

/* DELETE ALL PREVIEW DATA FROM OPTIONS TABLE */
$wpdb->query("DELETE FROM `" . $wpdb->options . "` WHERE  `option_name` LIKE  '%arf_previewtabledata%'");
/* DELETE ALL PREVIEW DATA FROM OPTIONS TABLE */

delete_transient( 'arf_sample_listing_page' );