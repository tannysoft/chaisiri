<?php

global $wpdb, $db_record, $MdlDb, $armainhelper, $arfieldhelper, $arsettingcontroller, $arfsettings, $arffield, $arfrecordmeta, $arfform, $style_settings, $maincontroller;

@ini_set('max_execution_time', 0);

if( !function_exists('is_plugin_active') ){
	require_once(ABSPATH.'/wp-admin/includes/plugin.php');
}

/* REMOVE OLD EXISTING BACKUP FOLDER AND TABLES */
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
$wpdb->query("INSERT `".$wpdb->prefix."arf_forms_backup` SELECT * FROM `".$MdlDb->forms."`");

$wpdb->query("CREATE TABLE `".$wpdb->prefix."arf_fields_backup` LIKE `".$MdlDb->fields."`");
$wpdb->query("INSERT `".$wpdb->prefix."arf_fields_backup` SELECT * FROM `".$MdlDb->fields."`");

$wp_upload_dir = wp_upload_dir();
$source_dir = $wp_upload_dir['basedir'].'/arforms/maincss';
$destination_dir = $wp_upload_dir['basedir'].'/arforms/maincss_backup';

if( is_dir( $destination_dir ) ){
	$destination_dir = $wp_upload_dir['basedir'].'/arforms/maincss_backup_4';
}

if( !is_dir( $destination_dir ) ){
	wp_mkdir_p($destination_dir);
}

$directory = opendir($source_dir);
while(($file = readdir($directory)) != false ){
	if( $file != '' && file_exists($source_dir.'/'.$file) ){
		@copy($source_dir.'/'.$file, $destination_dir.'/'.$file);
	}
}
update_option( 'arf_4_0_update_date', date( 'Y-m-d H:i:s' ) );
$timestamp = strtotime('+1 month');
wp_schedule_single_event( $timestamp, 'arf_remove_backup_data' );
/* CREATING BACKUP OF TABLES */

/* MODIFY ENTRIES TABLE */
$wpdb->query( "ALTER TABLE `".$MdlDb->entries."` ADD `is_incomplete_entry` TINYINT(1) NULL DEFAULT '0' AFTER `user_id`" );
/* MODIFY ENTRIES TABLE */

/* DELETE OLD TEMPLATES AND INSTALL NEW TEMPLATES */
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

$style_settings->set_default_options();
$style_settings->store();

include(MODELS_PATH."/artemplate.php");
/* DELETE OLD TEMPLATES AND INSTALL NEW TEMPALTES */

/* MIGRATION FOR DIVIDER FIELD INTO SECTION */
$forms = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT(f.id),f.options FROM `" . $MdlDb->forms ."` f LEFT JOIN `" . $MdlDb->fields ."` ff ON f.id = ff.form_id WHERE ff.type = %s AND is_template = %d", 'divider', 0 ) );

if( count( $forms ) > 0 ){

    foreach( $forms as $form ){

        $form_id = $form->id;

        $form_opts = arf_json_decode( $form->options, true );

        $field_order = json_decode( $form_opts['arf_field_order'] , true );
        $inner_field_order = isset( $form_opts['arf_inner_field_order'] ) ? json_decode( $form_opts['arf_inner_field_order'] , true ) : array();
        $field_resize_width = json_decode( $form_opts['arf_field_resize_width'] , true );
        $inner_field_resize_width = isset( $form_opts['arf_inner_field_resize_width'] ) ? json_decode( $form_opts['arf_field_order'] , true ) : array();

        $allfields = $arffield->getAll( array('fi.form_id' => $form_id ), 'id');

        asort( $field_order );
        asort( $inner_field_order );
        
        $allfieldsarr = array();
        $allfieldstype = array();
        $section_fields = array();
        $divider_fields = array();
        $i = 0;

        foreach( $allfields as $k => $field_obj ){
            if( 'divider' == $field_obj->type ){
                $section_fields[] = $i;
                $divider_fields[] = $field_obj->id;
            }
            $i++;
        }

        $final_field_order_types = array();
        $final_section_fields = array();
        foreach( $field_order as $field_id => $ford ){
            foreach( $allfields as $k => $field_obj ){
                if( $field_id == $field_obj->id ){
                    $final_field_order_types[$ford] = $field_id.'*|*'.$field_obj->type;
                    if( 'divider' == $field_obj->type ){
                        $final_section_fields[] = $ford - 1;
                    }
                    break;
                } else {
                    $final_field_order_types[$ford] = $field_id.'*|*'.$ford;
                }
            }
        }
        
        foreach( $final_field_order_types as $k => $v ){
            $vdata = explode('*|*', $v);
            $allfieldsarr[] = $vdata[0];
            $allfieldstype[] = $vdata[1];
        }

        $new_inner_field_order = $new_inner_field_resize_width = array();

        $section_fields = $final_section_fields;

        foreach( $section_fields as $k => $section_field_id ){
            $first = $section_fields[$k];
            $last = isset( $section_fields[$k + 1] ) ? $section_fields[$k + 1] : count( $allfieldsarr ) - 1;

            for( $pd = $first; $pd < $last; $pd++ ){
                if( isset( $allfieldstype[$pd + 1] ) && ( $allfieldstype[$pd + 1] == 'break' || $allfieldstype[$pd + 1] == 'divider') ){
                    $last = $pd + 1;
                }
            }

            $n = 1;
            
            for( $x2 = $first; $x2 <= $last; $x2++ ){
                if( is_array( $divider_fields ) && in_array( $allfieldsarr[$x2], $divider_fields) ){

                    if( !isset( $new_inner_field_order[$allfieldsarr[$x2]] ) ){
                        $xi = $x2;
                        $new_inner_field_order[$allfieldsarr[$x2]] = array();
                        $new_inner_field_resize_width[$allfieldsarr[$x2]] = array();

                        $field_opts = $wpdb->get_row( $wpdb->prepare( "SELECT field_options FROM `" . $MdlDb->fields . "` WHERE id = %d", $allfieldsarr[$x2] ) );

                        if( isset( $field_opts ) ){
                            $new_field_opts = array();

                            $field_opt = arf_json_decode( $field_opts->field_options, true );

                            $new_field_opts['arf_section_font'] = $field_opt['arf_divider_font'];
                            $new_field_opts['arf_section_font_size'] = $field_opt['arf_divider_font_size'];
                            $new_field_opts['arf_section_font_style'] = $field_opt['arf_divider_font_style'];
                            $new_field_opts['arf_section_bg_color'] = $field_opt['arf_divider_bg_color'];
                            $new_field_opts['arf_section_inherit_bg'] = $field_opt['arf_divider_inherit_bg'];
                            $new_field_opts['name'] = $field_opt['name'];
                            $new_field_opts['default_value'] = $field_opt['default_value'];
                            $new_field_opts['description'] = $field_opt['description'];
                            $new_field_opts['css_outer_wrapper'] = $field_opt['css_outer_wrapper'];
                            $new_field_opts['css_label'] = $field_opt['css_label'];
                            $new_field_opts['css_description'] = $field_opt['css_description'];
                            $new_field_opts['type'] = 'section';                                
                            $new_field_opts['classes'] = $field_opt['classes'];
                            $new_field_opts['inner_class'] = $field_opt['inner_class'];
                            $new_field_opts['key'] = $field_opt['key'];
                            $new_field_opts['ishidetitle'] = $field_opt['ishidetitle'];

                            $wpdb->update(
                                $MdlDb->fields,
                                array(
                                    'field_options' => json_encode( $new_field_opts ),
                                    'type' => 'section'
                                ),
                                array(
                                    'id' => $allfieldsarr[$x2]
                                )
                            );
                        }
                    }

                } else {
                    if( !isset($n) ){
                        $n = 1;
                    }

                    if( 'break' == $allfieldstype[$x2] && $x2 == $last ){
                        break;
                    }

                    $resize_width_key = ($x2 + 1);

                    $new_inner_field_order[$allfieldsarr[$xi]][] = $allfieldsarr[$x2].'|'.$n;

                    $field_opts = $wpdb->get_row( $wpdb->prepare( "SELECT field_options FROM `" . $MdlDb->fields . "` WHERE id = %d", $allfieldsarr[$x2] ) );

                    if( isset( $field_opts ) ){
                        $field_opt = arf_json_decode( $field_opts->field_options, true );
                        $field_opt['has_parent'] = true;
                        $field_opt['parent_field_type'] = 'section';
                        $field_opt['parent_field'] = $allfieldsarr[$xi];
                        $wpdb->update(
                            $MdlDb->fields,
                            array(
                                'field_options' => json_encode( $field_opt )
                            ),
                            array(
                                'id' => $allfieldsarr[$x2]
                            )
                        );
                    }

                    if( !preg_match('/^(\d)+$/',$allfieldsarr[$x2]) ){
                        $dt = explode( '|', $allfieldsarr[$x2] );
                        $resize_width = isset( $field_resize_width[$resize_width_key] ) ? $field_resize_width[$resize_width_key] : '100.000';
                        $new_inner_field_resize_width[$allfieldsarr[$xi]][] = $dt[0].'|'.$resize_width.'|'.$n;
                    } else {
                        $resize_width = isset( $field_resize_width[$resize_width_key] ) ? $field_resize_width[$resize_width_key] : '100.000';
                        $new_inner_field_resize_width[$allfieldsarr[$xi]][] = $allfieldsarr[$x2].'|'.$resize_width.'|'.$n;
                    }

                    unset( $field_order[$allfieldsarr[$x2]] );
                    unset( $field_resize_width[$resize_width_key] );
                    $n++;
                }
            }
        }
        
        $new_final_field_order = array();

        $x = 1;
        foreach( $field_order as $fid => $fval ){
            $new_final_field_order[$fid] = $x;
            $new_field_resize_width[$x] = $field_resize_width[$fval];
            $x++;
        }

        $updated_field_order = json_encode( $new_final_field_order );
        $updated_field_width = json_encode( $new_field_resize_width );
        $updated_inner_field_order = json_encode( $new_inner_field_order );
        $updated_inner_field_width = json_encode( $new_inner_field_resize_width );

        $form_opts['arf_field_order'] = $updated_field_order;
        $form_opts['arf_inner_field_order'] = $updated_inner_field_order;
        $form_opts['arf_field_resize_width'] = $updated_field_width;
        $form_opts['arf_inner_field_resize_width'] = $updated_inner_field_width;

        $wpdb->update(
            $MdlDb->forms,
            array(
                'options' => maybe_serialize( $form_opts )
            ),
            array(
                'id' => $form_id
            )
        );

    }

}
/* MIGRATION FOR DIVIDER FIELD INTO SECTION */

/* REWRITE ALL TABLE CSS */
$all_forms = $wpdb->get_results( $wpdb->prepare( "SELECT id,form_css FROM `" . $MdlDb->forms . "` WHERE id > %d AND is_template != %d", 11, 1 ) );
foreach( $all_forms as $form_data ){
    $form_id = $form_data->id;
    $new_form_css = maybe_unserialize( $form_data->form_css );
    if( !isset( $new_form_css['arfsubmitboxxoffsetsetting'] ) ){
        $new_form_css['arfsubmitboxxoffsetsetting'] = '1';
    }

    if( !isset( $new_form_css['arfsubmitboxyoffsetsetting'] ) ){
        $new_form_css['arfsubmitboxyoffsetsetting'] = '2';
    }

    if( !isset( $new_form_css['arfsubmitboxblursetting'] ) ){
        $new_form_css['arfsubmitboxblursetting'] = '3';
    }

    if( !isset( $new_form_css['arfsubmitboxshadowsetting']) ){
        $new_form_css['arfsubmitboxshadowsetting'] = '0';
    }

    if( count($new_form_css) > 0 ){
        $new_values = array();
        foreach ($new_form_css as $k => $v) {
            $new_values[$k] = $v;
            if( preg_match("/auto/",$new_values[$k]) ){
                $new_values[$k] = str_replace("px","",$new_values[$k]);
            }
        }

        update_option('arf_form_css_'.$form_id,json_encode( $new_values) );

        $saving = true;
        $use_saved = true;
        $arfssl = (is_ssl()) ? 1 : 0;

        $filename = FORMPATH . '/core/css_create_main.php';

        $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
        $temp_css_file .= "\n";
        ob_start();
        include $filename;
        $temp_css_file .= ob_get_contents();
        ob_end_clean();
        $temp_css_file .= "\n " . $warn;
        $wp_upload_dir = wp_upload_dir();
        $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
        $css_file_new = $dest_dir . 'maincss_' . $form_id. '.css';

        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);

        $filename1 = FORMPATH . '/core/css_create_materialize.php';
        $temp_css_file1 = $warn1 = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
        $temp_css_file1 .= "\n";
        ob_start();
        include $filename1;
        $temp_css_file1 .= ob_get_contents();
        ob_end_clean();
        $temp_css_file1 .= "\n " . $warn1;
        $wp_upload_dir = wp_upload_dir();
        $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
        $css_file_new1 = $dest_dir . 'maincss_materialize_' . $form_id. '.css';

        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->put_contents($css_file_new1, $temp_css_file1, 0777);
    }

    $wpdb->update(
        $MdlDb->forms,
        array(
            'form_css' => maybe_serialize( $new_form_css )
        ),
        array(
            'id' => $form_id
        )
    );
}
/* REWRITE ALL TABLE CSS */

/* INSTALL NEW EMAIL MARKETERS */

$charset_collate = '';
if ($wpdb->has_cap('collation')) {
    if (!empty($wpdb->charset))
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
    if (!empty($wpdb->collate))
        $charset_collate .= " COLLATE $wpdb->collate";
}

$wpdb->query("ALTER TABLE `".$MdlDb->ar."` ADD `sendinblue` TEXT NOT NULL AFTER `mailerlite`, ADD `hubspot` TEXT NOT NULL AFTER `sendinblue`, ADD `convertkit` TEXT NOT NULL AFTER `hubspot`");

$wpdb->query("INSERT INTO `".$MdlDb->autoresponder."` (`responder_id`) VALUES (15), (16), (17)");

$ar_types = maybe_unserialize(get_option('arf_ar_type'));
$ar_types['hubspot_type'] = 1;
$ar_types['sendinblue_type'] = 1;
$ar_types['convertkit_type'] = 1;
$ar_types = $ar_types;
update_option('arf_ar_type', $ar_types);

/* INSTALL NEW EMAIL MARKETERS */

/* INSTALLING NEW TABLES FOR INCOMPLETE ENTRIES */
$sql = "CREATE TABLE IF NOT EXISTS ".$MdlDb->incomplete_entries."(
    id int(11) NOT NULL auto_increment,
    form_id int(11) NOT NULL,
    token varchar(50) NOT NULL,
    description text NOT NULL,
    ip_address varchar(100) NOT NULL,
    browser_info text NOT NULL,
    country varchar(30) NOT NULL,
    user_id int(11) NOT NULL,
    created_date datetime NOT NULL,
    PRIMARY KEY (id)
){$charset_collate}";

$wpdb->query($sql);
if( $wpdb->last_error != '' ){ update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QOUTES)); }

$sql = "CREATE TABLE IF NOT EXISTS ".$MdlDb->incomplete_entry_metas."(
    id int(11) NOT NULL auto_increment,
    entry_id int(11) NOT NULL,
    field_id int(11) NOT NULL,
    entry_value longtext NOT NULL,
    created_date datetime NOT NULL,
    last_created_date datetime NOT NULL,
    PRIMARY KEY (id)
){$charset_collate}";

$wpdb->query($sql);
if( $wpdb->last_error != '' ){ update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QOUTES)); }

/* INSTALLING NEW TABLES FOR INCOMPLETE ENTRIES */

/* REMOVING SITE-WIDE POPUP IF FORM IS NOT EXISTS */
$popup_forms = $wpdb->get_results( "SELECT popup_id,form_id FROM `". $MdlDb->form_popup . "` ORDER BY form_id DESC" );

foreach( $popup_forms as $popup){
	
	$form_id = $popup->form_id;
	$popup_id = $popup->popup_id;

	$getVar = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM `". $MdlDb->forms . "` WHERE id = %d", $form_id ) );

	if( $getVar < 1 || $getVar == '' ){
		$wpdb->delete(
			$MdlDb->form_popup,
			array(
				'popup_id' => $popup_id
			)
		);
	}
}

/* REMOVING SITE-WIDE POPUP IF FORM IS NOT EXISTS */

/* ADD AN OPTION FOR AWEBER NOTICE - START */
$aweber_config = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `". $MdlDb->autoresponder."` WHERE id = %d and is_verify = %d", 3, 1 ) );
if( $aweber_config > 0 ){
    update_option('arf_reauth_aweber_app','1');
    update_option('arf_clear_cookie_for_aweber','1');
}
/* ADD AN OPTION FOR AWEBER NOTICE - END*/

/* ASSIGNING NEW CAPABILITIES TO ADMIN USERS */
$args = array(
    'role' => 'administrator',
    'fields' => 'id'
);
$users = get_users($args);
if( count($users) > 0 ){
    foreach($users as $key => $user_id ){
		global $current_user;
 		$arfroles = $armainhelper->frm_capabilities();

        $userObj = new WP_User($user_id);
        foreach ($arfroles as $arfrole => $arfroledescription){
            $userObj->add_cap($arfrole);
        }
        unset($arfrole);
        unset($arfroles);
        unset($arfroledescription);
    }
}
/* ASSIGNING NEW CAPABILITIES TO ADMIN USERS */