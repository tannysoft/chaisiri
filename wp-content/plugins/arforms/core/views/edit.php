<?php
global $wpdb, $arf_memory_limit, $memory_limit, $arfversion, $aresponder, $responder_fname, $responder_lname, $responder_email, $mailchimpkey, $mailchimpid, $infusionsoftkey, $aweberkey, $aweberid, $getresponsekey, $getresponseid, $gvokey, $gvoid, $ebizackey, $ebizacid, $style_settings, $arfsettings, $arformhelper, $arrecordcontroller, $armainhelper, $arformcontroller, $arfieldhelper, $maincontroller, $arfadvanceerrcolor, $MdlDb, $arffield, $arfform, $arfajaxurl, $conditional_logic_array_if, $arf_date_check_arr;

if (isset($arf_memory_limit) && isset($memory_limit) && ($arf_memory_limit * 1024 * 1024) > $memory_limit) {
    @ini_set("memory_limit", $arf_memory_limit . 'M');
} 

/* arf_dev_flag Temp CSS for Query Monitor */
echo "<style type='text/css'>.qm-js#qm{position:relative;z-index:999;}.notice.arf-notice-update-warning{display:none !important;}</style>";

$id = (isset($_REQUEST['id']) && $_REQUEST['id'] != '' ) ? $_REQUEST['id'] : 0;

if ($action == 'duplicate' || $action == 'edit') {
    $cached_record = wp_cache_get( 'arf_editor_form_obj' );
    if( ! $cached_record ){
        $record = $arfform->arf_select_db_data( true, '', $MdlDb->forms, '*', 'WHERE id = %d', array( $id ), '', '', '', false, true );
        wp_cache_set( 'arf_editor_form_obj', $record );
    } else {
        $record = $cached_record;
    }
}

if($action == 'edit'){
    if(empty($record)){
        echo '<script type="text/javascript">window.location.href = "' . admin_url('admin.php?page=ARForms') . '";</script>';
    }
}

if (isset($record) && $record->is_template && $_REQUEST['arfaction'] != 'duplicate') {
    wp_die(addslashes(esc_html__('That template cannot be edited', 'ARForms')));
}
if( !isset($record) ){
    $record = new stdClass();
}

$values = array();
$values['fields'] = array();
$arf_all_fields = array();
$record_arr = (array)$record;

if (!empty($record_arr)) {
    $values['id'] = $form_id = $record->id;
    $values['form_key'] = $record->form_key;
    $values['description'] = $record->description;
    $values['name'] = $record->name;
    $values['form_name'] = $record->name;
    $all_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $MdlDb->fields . "` WHERE form_id = %d ORDER BY ID ASC", $form_id));
}

$field_list = array();
$include_fields = array();
$exclude = array('divider', 'captcha', 'section', 'break');
$all_hidden_fields = array();
$responder_list_option = "";

if (!empty($all_fields)) {
    foreach ($all_fields as $key => $field_) {
        if( !in_array($field_->id,$exclude) && $field_->type == 'hidden') {
            $all_hidden_fields[] = $field_;
            $include_fields[] = $field_->id;
            continue;
        }
        foreach ($field_ as $k => $field_val) {
            if ($k == 'type' && !in_array($field_val, $exclude)) {
                $include_fields[] = $field_->id;
            }
            if ($k == 'options') {
                $arf_all_fields[$key][$k] = json_decode($field_val, true);
                if (json_last_error() != JSON_ERROR_NONE) {
                    $arf_all_fields[$key][$k] = maybe_unserialize($field_val);
                }
            } else if ($k == 'field_options') {
                $field_opts = json_decode($field_val, true);
                if (json_last_error() != JSON_ERROR_NONE) {
                    $field_opts = maybe_unserialize($field_val);
                }
                if( isset($field_opts) && is_array($field_opts) ){ /* arf_dev_flag-3.0 - please check this condition for import/export */
                    foreach ($field_opts as $ki => $val_) {
                        $arf_all_fields[$key][$ki] = $val_;
                    }
                }
            } else {
                $arf_all_fields[$key][$k] = $field_val;
            }
        }
    }
    foreach ($all_fields as $key => $field_) {
        foreach ($field_ as $k => $field_val) {
            if (in_array($field_->id, $include_fields)) {
                if (!isset($field_list[$key])) {
                    $field_list[$key] = new stdClass();
                }
                if ($k == 'options') {
                    $fOpt = json_decode($field_val, true);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        $fOpt = maybe_unserialize($field_val);
                    }
                    $field_list[$key]->$k = $fOpt;
                } else if ($k == 'field_options') {
                    $field_opts = json_decode($field_val, true);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        $field_opts = maybe_unserialize($field_val);
                    }
                    $field_list[$key]->$k = $field_opts;

                    $disable_crop_img = false;
                    if ( 'file' == $field_->type && 1 == $field_opts['arf_is_multiple_file'] ) {
                        $disable_crop_img = true;
                    }
                    
                } else {
                    $field_list[$key]->$k = $field_val;
                }
            }
        }
    }
    $values['fields'] = $arf_all_fields;
}

$field_data = file_get_contents(VIEWS_PATH . '/arf_editor_data.json');

$field_data_obj = json_decode($field_data);
$form_opts = isset($record->options) ? maybe_unserialize($record->options) : array();
$form_opts = $arformcontroller->arf_html_entity_decode($form_opts);

if (is_array($form_opts) && !empty($form_opts) ) {

    foreach ($form_opts as $opt => $value) {

        if (in_array($opt, array('email_to', 'reply_to', 'reply_to_name','admin_cc_email','admin_bcc_email'))) {

            $values['notification'][0][$opt] = $armainhelper->get_param('notification[0][' . $opt . ']', $value);
            
        }

        $values[$opt] = $armainhelper->get_param($opt, $value);
    }
}


$form_defaults = $arformhelper->get_default_opts();

foreach ($form_defaults as $opt => $default) {


    if (!isset($values[$opt]) or $values[$opt] == '') {
        if ($opt == 'notification') {
            $values[$opt] = ($_POST and isset($_POST[$opt])) ? $_POST[$opt] : $default;
            foreach ($default as $o => $d) {
                if ($o == 'email_to') {
                    $d = '';
                }
                $values[$opt][0][$o] = ($_POST and isset($_POST[$opt][0][$o])) ? $_POST[$opt][0][$o] : $d;
                unset($o);
                unset($d);
            }
        } else {
            $values[$opt] = ($_POST and isset($_POST['options'][$opt])) ? $_POST['options'][$opt] : $default;
        }
    }
    unset($opt);
    unset($defaut);
}
$responder_fname = isset($record->autoresponder_fname) ? $record->autoresponder_fname : '';
$responder_lname = isset($record->autoresponder_lname) ? $record->autoresponder_lname : '';
$responder_email = isset($record->autoresponder_email) ? $record->autoresponder_email : '';

$arffield_selection = $arfieldhelper->field_selection();

$display = apply_filters('arfdisplayfieldoptions', array('label_position' => true));



wp_enqueue_script('sack');
$key = isset($record->form_key) ? $record->form_key : '';

$form_temp_key = '';
if (!isset($record->form_key)) {
    global $armainhelper;
    $possible_letters = '23456789bcdfghjkmnpqrstvwxyz';
    $random_dots = 0;
    $random_lines = 20;

    $form_temp_key = '';
    $i = 0;
    while ($i < 8) {
        $form_temp_key .= substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1);
        $i++;
    }
}

$pre_link = (isset($record->form_key)) ? $arformhelper->get_direct_link($record->form_key) : $arformhelper->get_direct_link($form_temp_key);

$wp_format_date = get_option('date_format');


$data = "";

$data = isset($record) ? $record : '';

$data = $arformcontroller->arfObjtoArray($data);
$aweber_arr = "";
$aweber_arr = isset($data['form_css']) ? $data['form_css'] : '';

$values_nw = isset($data['options']) ? maybe_unserialize($data['options']) : array();

$arr = maybe_unserialize($aweber_arr);

$newarr = array();
if (isset($arr) && !empty($arr) && is_array($arr)) {
    foreach ($arr as $k => $v) {
        $newarr[$k] = $v;
    }
}
$arfinputstyle_template = (isset($_GET['templete_style']) && $_GET['templete_style'] !='') ? $_GET['templete_style'] : ((isset($newarr['arfinputstyle']) && $newarr['arfinputstyle'] !='') ? $newarr['arfinputstyle'] : 'material');

if( !empty( $arfinputstyle_template ) && !in_array( $arfinputstyle_template, $maincontroller->arf_default_themes() ) ) {
    $arfinputstyle_template = 'standard';
}

$skinJsonFile = file_get_contents(VIEWS_PATH . '/arf_editor_data.json');

$skinJson = json_decode(stripslashes($skinJsonFile));

$skinJson = apply_filters('arf_form_fields_outside', $skinJson,$arfinputstyle_template);

if (empty($newarr)) {
    $default_data_varible = 'default_data_'.$arfinputstyle_template;
    
    $custom_css_data = $arformcontroller->arfObjtoArray($skinJson->$default_data_varible);
    foreach ($custom_css_data as $k => $v) {
        $newarr[$k] = $v;
    }
}
$newarr['arfinputstyle']  = (isset($_GET['templete_style']) && $_GET['templete_style'] !='') ? $_GET['templete_style'] : ((isset($newarr['arfinputstyle']) && $newarr['arfinputstyle'] !='') ? $newarr['arfinputstyle'] : 'material');

if( !empty( $newarr['arfinputstyle'] ) && !in_array( $newarr['arfinputstyle'], $maincontroller->arf_default_themes() ) ){
    $newarr['arfinputstyle'] = 'standard';
}


if(isset($_REQUEST['arf_rtl_switch_mode']) && $_REQUEST['arf_rtl_switch_mode']=="yes" ) {
    $newarr['arfformtitlealign'] = "right";
    $newarr['form_align'] = "right";
    $newarr['arfdescalighsetting'] = 'right';
    $newarr['align'] = "right";
    $newarr['text_direction'] = '0';
    $newarr['arfsubmitalignsetting'] = "right";
}


$values_nw['display_title_form'] = isset($values_nw['display_title_form']) ? $values_nw['display_title_form'] : (isset($newarr['display_title_form']) ? $newarr['display_title_form']  : 1);


$active_skin = (isset($newarr['arfmainform_color_skin']) && $newarr['arfmainform_color_skin'] != '') ? $newarr['arfmainform_color_skin'] : 'cyan';

foreach ($newarr as $k => $v) {
    if (strpos($v, '#') === FALSE) {
        if (( preg_match('/color/', $k) or in_array($k, array('arferrorbgsetting', 'arferrorbordersetting', 'arferrortextsetting')) ) && !in_array($k, array('arfcheckradiocolor'))) {
            $newarr[$k] = '#' . $v;
        } else {
            $newarr[$k] = $v;
        }
    }
}



    /* Form Section */
    
    $skinJson->skins->custom->form->title = (isset($newarr['arfmainformtitlecolorsetting']) && $newarr['arfmainformtitlecolorsetting'] != '') ? esc_attr($newarr['arfmainformtitlecolorsetting']) : $skinJson->skins->cyan->form->title;

    $skinJson->skins->custom->form->description = (isset($newarr['arfmainformtitlecolorsetting']) && $newarr['arfmainformtitlecolorsetting'] != '') ? esc_attr($newarr['arfmainformtitlecolorsetting']) : $skinJson->skins->cyan->form->description;

    $skinJson->skins->custom->form->border = (isset($newarr['arfmainfieldsetcolor']) && $newarr['arfmainfieldsetcolor'] != "") ? esc_attr($newarr['arfmainfieldsetcolor']) : $skinJson->skins->cyan->form->border;

    $skinJson->skins->custom->form->background = (isset($newarr['arfmainformbgcolorsetting']) && $newarr['arfmainformbgcolorsetting'] != '' ) ? esc_attr($newarr['arfmainformbgcolorsetting']) : $skinJson->skins->cyan->form->background;

    $skinJson->skins->custom->form->shadow = (isset($newarr['arfmainformbordershadowcolorsetting']) && $newarr['arfmainformbordershadowcolorsetting'] != '') ? esc_attr($newarr['arfmainformbordershadowcolorsetting']) : $skinJson->skins->cyan->form->shadow;

    $skinJson->skins->custom->form->section_background = (isset($newarr['arfformsectionbackgroundcolor']) && $newarr['arfformsectionbackgroundcolor'] != '') ? esc_attr($newarr['arfformsectionbackgroundcolor']) : $skinJson->skins->cyan->form->section_background;


    /* Tooltip Section */

    $skinJson->skins->custom->tooltip->background = ( isset($newarr['arf_tooltip_bg_color']) && $newarr['arf_tooltip_bg_color'] != "" ) ? esc_attr($newarr['arf_tooltip_bg_color']) : $skinJson->skins->cyan->tooltip->background;

    $skinJson->skins->custom->tooltip->text = ( isset($newarr['arf_tooltip_font_color']) && $newarr['arf_tooltip_font_color'] != "" ) ? esc_attr($newarr['arf_tooltip_font_color']) : $skinJson->skins->cyan->tooltip->text;

    /* Matrix Field Color */
    $skinJson->skins->custom->matrix->odd_row = ( isset($newarr['arf_matrix_odd_bgcolor']) && $newarr['arf_matrix_odd_bgcolor'] != "" ) ? esc_attr($newarr['arf_matrix_odd_bgcolor']) : $skinJson->skins->cyan->matrix->odd_row;

    $skinJson->skins->custom->matrix->even_row = ( isset($newarr['arf_matrix_even_bgcolor']) && $newarr['arf_matrix_even_bgcolor'] != "" ) ? esc_attr($newarr['arf_matrix_even_bgcolor']) : $skinJson->skins->cyan->matrix->even_row;

    /* Page Break Section */

    $skinJson->skins->custom->pagebreak->active_tab = (isset($newarr['bg_color_pg_break']) && $newarr['bg_color_pg_break']) ? esc_attr($newarr['bg_color_pg_break']) : $skinJson->skins->cyan->pagebreak->active_tab;

    $skinJson->skins->custom->pagebreak->inactive_tab = (isset($newarr['bg_inavtive_color_pg_break']) && $newarr['bg_inavtive_color_pg_break'] != '' ) ? esc_attr($newarr['bg_inavtive_color_pg_break']) : $skinJson->skins->cyan->pagebreak->inactive_tab;
    
    $skinJson->skins->custom->pagebreak->text = ( isset($newarr['text_color_pg_break']) && $newarr['text_color_pg_break'] != '' ) ? esc_attr($newarr['text_color_pg_break']) : $skinJson->skins->cyan->pagebreak->text;

    $skinJson->skins->custom->pagebreak->style3_text = ( isset($newarr['text_color_pg_break_style3']) && $newarr['text_color_pg_break_style3'] != '' ) ? esc_attr($newarr['text_color_pg_break_style3']) : $skinJson->skins->cyan->pagebreak->style3_text;

    /* Survey Section */

    $skinJson->skins->custom->survey->bar_color = ( isset($newarr['bar_color_survey']) && $newarr['bar_color_survey'] != '' ) ? esc_attr($newarr['bar_color_survey']) : $skinJson->skins->cyan->survey->bar_color;
    
    $skinJson->skins->custom->survey->background = ( isset($newarr['bg_color_survey']) && $newarr['bg_color_survey'] != '' ) ? esc_attr($newarr['bg_color_survey']) : $skinJson->skins->cyan->survey->background;
    
    $skinJson->skins->custom->survey->text = ( isset($newarr['text_color_survey']) && $newarr['text_color_survey'] != '' ) ? esc_attr($newarr['text_color_survey']) : $skinJson->skins->cyan->survey->text;

    /*timer section*/
    $skinJson->skins->custom->pagebreaktimer->circle_bg_color = ( isset($newarr['timer_bg_color']) && $newarr['timer_bg_color'] != '' ) ? esc_attr($newarr['bar_color_survey']) : $skinJson->skins->cyan->pagebreaktimer->circle_bg_color;

    $skinJson->skins->custom->pagebreaktimer->circle_bg_color = ( isset($newarr['timer_forground_color']) && $newarr['timer_forground_color'] != '' ) ? esc_attr($newarr['timer_forground_color']) : $skinJson->skins->cyan->pagebreaktimer->circle_bg_color;

    /* Label Section */

    $skinJson->skins->custom->label->text = (isset($newarr['label_color']) && $newarr['label_color'] != '' ) ? esc_attr($newarr['label_color']) : $skinJson->skins->cyan->label->text;

    $skinJson->skins->custom->label->description = (isset($newarr['label_color']) && $newarr['label_color'] != '' ) ? esc_attr($newarr['label_color']) : $skinJson->skins->cyan->label->text;

    /* Input Section */

    $skinJson->skins->custom->input->main = (isset($newarr['arfmainbasecolor']) && $newarr['arfmainbasecolor'] != "" ) ? esc_attr($newarr['arfmainbasecolor']) : $skinJson->skins->cyan->input->main;   

    
    $skinJson->skins->custom->input->text = ( isset($newarr['text_color']) && $newarr['text_color'] != '' ) ? esc_attr($newarr['text_color']) : $skinJson->skins->cyan->input->text;

    $skinJson->skins->custom->input->background = (isset($newarr['bg_color']) && $newarr['bg_color'] != '') ? esc_attr($newarr['bg_color']) : $skinJson->skins->cyan->input->background;

    $skinJson->skins->custom->input->background_active = ( isset($newarr['arfbgactivecolorsetting']) && $newarr['arfbgactivecolorsetting'] != '' ) ? esc_attr($newarr['arfbgactivecolorsetting']) : $skinJson->skins->cyan->input->background_active;

    $skinJson->skins->custom->input->background_error = ( isset($newarr['arferrorbgcolorsetting']) && $newarr['arferrorbgcolorsetting'] != '' ) ? esc_attr($newarr['arferrorbgcolorsetting']) : $skinJson->skins->cyan->input->background_error;
    
    $skinJson->skins->custom->input->border = ( isset($newarr['border_color']) && $newarr['border_color'] != '' ) ? esc_attr($newarr['border_color']) : $skinJson->skins->cyan->input->border;
    
    $skinJson->skins->custom->input->border_active = (isset($newarr['arfborderactivecolorsetting']) && $newarr['arfborderactivecolorsetting'] != '' ) ? esc_attr($newarr['arfborderactivecolorsetting']) : $skinJson->skins->cyan->input->border_active;
    
    $skinJson->skins->custom->input->border_error = (isset($newarr['arferrorbordercolorsetting']) && $newarr['arferrorbordercolorsetting'] != '' ) ? esc_attr($newarr['arferrorbordercolorsetting']) : $skinJson->skins->cyan->input->border_error;


    $skinJson->skins->custom->input->prefix_suffix_background = ( isset($newarr['prefix_suffix_bg_color']) && $newarr['prefix_suffix_bg_color'] != '' ) ? esc_attr($newarr['prefix_suffix_bg_color']) : $skinJson->skins->cyan->input->prefix_suffix_background;

    $skinJson->skins->custom->input->prefix_suffix_icon_color = (isset($newarr['prefix_suffix_icon_color']) && $newarr['prefix_suffix_icon_color'] != '' ) ? esc_attr($newarr['prefix_suffix_icon_color']) : $skinJson->skins->cyan->input->prefix_suffix_icon_color;   

    $skinJson->skins->custom->input->checkbox_icon_color = ( isset($newarr['checked_checkbox_icon_color']) && $newarr['checked_checkbox_icon_color'] != '' ) ? esc_attr($newarr['checked_checkbox_icon_color']) : $skinJson->skins->cyan->input->checkbox_icon_color;
    
    $skinJson->skins->custom->input->radio_icon_color = ( isset($newarr['checked_radio_icon_color']) && $newarr['checked_radio_icon_color'] != '' ) ? esc_attr($newarr['checked_radio_icon_color']) : $skinJson->skins->cyan->input->radio_icon_color;

    $skinJson->skins->custom->input->like_button = ( isset($newarr['arflikebtncolor']) && $newarr['arflikebtncolor'] != "" ) ? esc_attr($newarr['arflikebtncolor']) : $skinJson->skins->cyan->input->like_button;

    $skinJson->skins->custom->input->dislike_button = ( isset($newarr['arfdislikebtncolor']) && $newarr['arfdislikebtncolor'] != "" ) ? esc_attr($newarr['arfdislikebtncolor']) : $skinJson->skins->cyan->input->dislike_button;

    $skinJson->skins->custom->input->rating_color = ( isset($newarr['arfstarratingcolor']) && $newarr['arfstarratingcolor'] != "" ) ? esc_attr($newarr['arfstarratingcolor']) : $skinJson->skins->cyan->input->rating_color;

    $skinJson->skins->custom->input->slider_selection_color = ( isset($newarr['arfsliderselectioncolor']) && $newarr['arfsliderselectioncolor'] != "" ) ? esc_attr($newarr['arfsliderselectioncolor']) : $skinJson->skins->cyan->input->slider_selection_color;

    $skinJson->skins->custom->input->slider_track_color = ( isset($newarr['arfslidertrackcolor']) && $newarr['arfslidertrackcolor'] != "" ) ? esc_attr($newarr['arfslidertrackcolor']) : $skinJson->skins->cyan->input->slider_track_color;

    /* Submit Section */
    
    $skinJson->skins->custom->submit->text = (isset($newarr['arfsubmittextcolorsetting']) && $newarr['arfsubmittextcolorsetting'] != '' ) ? esc_attr($newarr['arfsubmittextcolorsetting']) : $skinJson->skins->cyan->submit->text;
    
    $skinJson->skins->custom->submit->background = (isset($newarr['submit_bg_color']) && $newarr['submit_bg_color'] != '' ) ? esc_attr($newarr['submit_bg_color']) : $skinJson->skins->cyan->submit->background;
    
    $skinJson->skins->custom->submit->background_hover = (isset($newarr['arfsubmitbuttonbgcolorhoversetting']) && $newarr['arfsubmitbuttonbgcolorhoversetting'] != '' ) ? esc_attr($newarr['arfsubmitbuttonbgcolorhoversetting']) : $skinJson->skins->cyan->submit->background_hover;
    
    $skinJson->skins->custom->submit->border = isset($newarr['arfsubmitbordercolorsetting']) ? esc_attr($newarr['arfsubmitbordercolorsetting']) : $skinJson->skins->cyan->submit->border;
    
    $skinJson->skins->custom->submit->shadow = ( isset($newarr['arfsubmitshadowcolorsetting']) && $newarr['arfsubmitshadowcolorsetting'] != '' ) ? esc_attr($newarr['arfsubmitshadowcolorsetting']) : $skinJson->skins->cyan->submit->shadow;
    
    /* Success Message Section */

    $skinJson->skins->custom->success_msg->background = ( isset($newarr['arfsucessbgcolorsetting']) && $newarr['arfsucessbgcolorsetting'] != '' ) ? esc_attr($newarr['arfsucessbgcolorsetting']) : $skinJson->skins->cyan->success_msg->background;
    
    $skinJson->skins->custom->success_msg->border = (isset($newarr['arfsucessbordercolorsetting']) && $newarr['arfsucessbordercolorsetting'] != '') ? esc_attr($newarr['arfsucessbordercolorsetting']) : $skinJson->skins->cyan->success_msg->border;
    
    $skinJson->skins->custom->success_msg->text = ( isset($newarr['arfsucesstextcolorsetting']) && $newarr['arfsucesstextcolorsetting'] != '' ) ? esc_attr($newarr['arfsucesstextcolorsetting']) : $skinJson->skins->cyan->success_msg->text;

    /* Success Message Material Section */

    $skinJson->skins->custom->success_msg_material->background = ( isset($newarr['arfsucessbgcolorsetting']) && $newarr['arfsucessbgcolorsetting'] != '' ) ? esc_attr($newarr['arfsucessbgcolorsetting']) : $skinJson->skins->cyan->success_msg_material->background;
    
    $skinJson->skins->custom->success_msg_material->border = (isset($newarr['arfsucessbordercolorsetting']) && $newarr['arfsucessbordercolorsetting'] != '') ? esc_attr($newarr['arfsucessbordercolorsetting']) : $skinJson->skins->cyan->success_msg_material->border;
    
    $skinJson->skins->custom->success_msg_material->text = ( isset($newarr['arfsucesstextcolorsetting']) && $newarr['arfsucesstextcolorsetting'] != '' ) ? esc_attr($newarr['arfsucesstextcolorsetting']) : $skinJson->skins->cyan->success_msg_material->text;

    /* Error Message Section */

    $skinJson->skins->custom->error_msg->background = (isset($newarr['arfformerrorbgcolorsettings']) && $newarr['arfformerrorbgcolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbgcolorsettings']) : $skinJson->skins->custom->error_msg->background;

    $skinJson->skins->custom->error_msg->text = (isset($newarr['arfformerrortextcolorsettings']) && $newarr['arfformerrortextcolorsettings'] != '' ) ? esc_attr($newarr['arfformerrortextcolorsettings']) : $skinJson->skins->custom->error_msg->text;

    $skinJson->skins->custom->error_msg->border = (isset($newarr['arfformerrorbordercolorsettings']) && $newarr['arfformerrorbordercolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbordercolorsettings']) : $skinJson->skins->custom->error_msg->border;

    /* Error Message Material Section */

    $skinJson->skins->custom->error_msg_material->background = (isset($newarr['arfformerrorbgcolorsettings']) && $newarr['arfformerrorbgcolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbgcolorsettings']) : $skinJson->skins->custom->error_msg_material->background;

    $skinJson->skins->custom->error_msg_material->text = (isset($newarr['arfformerrortextcolorsettings']) && $newarr['arfformerrortextcolorsettings'] != '' ) ? esc_attr($newarr['arfformerrortextcolorsettings']) : $skinJson->skins->custom->error_msg_material->text;

    $skinJson->skins->custom->error_msg_material->border = (isset($newarr['arfformerrorbordercolorsettings']) && $newarr['arfformerrorbordercolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbordercolorsettings']) : $skinJson->skins->custom->error_msg_material->border;

    
    /* Validation Message */
    
    $skinJson->skins->custom->validation_msg->background = ( isset($newarr['arfvalidationbgcolorsetting']) && $newarr['arfvalidationbgcolorsetting'] != '' ) ? esc_attr($newarr['arfvalidationbgcolorsetting']) : (($active_skin != 'custom') ? $skinJson->skins->cyan->validation_msg->background : '');
    
    $skinJson->skins->custom->validation_msg->text = ( isset($newarr['arfvalidationtextcolorsetting']) && $newarr['arfvalidationtextcolorsetting'] != '' ) ? esc_attr($newarr['arfvalidationtextcolorsetting']) : (($active_skin != 'custom') ? $skinJson->skins->cyan->validation_msg->text : '');

    /* DateTime Picker Section */
    
    $skinJson->skins->custom->datepicker->background = ( isset($newarr['arfdatepickerbgcolorsetting']) && $newarr['arfdatepickerbgcolorsetting'] != '' ) ? esc_attr($newarr['arfdatepickerbgcolorsetting']) : $skinJson->skins->cyan->datepicker->background;
    
    $skinJson->skins->custom->datepicker->text = ( isset($newarr['arfdatepickertextcolorsetting']) && $newarr['arfdatepickertextcolorsetting'] != '' ) ? esc_attr($newarr['arfdatepickertextcolorsetting']) : $skinJson->skins->cyan->datepicker->text;

    /* Upload Button Section */
   
    $skinJson->skins->custom->uploadbutton->text = ( isset($newarr['arfuploadbtntxtcolorsetting']) && $newarr['arfuploadbtntxtcolorsetting'] != '' ) ? esc_attr($newarr['arfuploadbtntxtcolorsetting']) : $skinJson->skins->cyan->uploadbutton->text;

    $skinJson->skins->custom->uploadbutton->background = ( isset($newarr['arfuploadbtnbgcolorsetting']) && $newarr['arfuploadbtnbgcolorsetting'] != '' ) ? esc_attr($newarr['arfuploadbtnbgcolorsetting']) : $skinJson->skins->cyan->uploadbutton->background;

$browser_info = $arrecordcontroller->getBrowser($_SERVER['HTTP_USER_AGENT']);
$translated_text_filedrag = "
    var __ARF_UPLOAD_CSV_MSG  = '". addslashes(esc_html__("Please upload csv files only","ARForms"))."';
    var __ARF_UPLOAD_IMG_MSG  = '". addslashes(esc_html__("Please upload image files only","ARForms"))."';
";
wp_register_script('filedrag-js', ARFURL . '/js/filedrag/filedrag.js', array(), $arfversion);
wp_register_script('filedrag-lower-js', ARFURL . '/js/filedrag/filedrag_lower.js', array(), $arfversion);
wp_add_inline_script('filedrag-js', $translated_text_filedrag);
wp_add_inline_script('filedrag-lower-js', $translated_text_filedrag);
$armainhelper->load_scripts(array('filedrag-js'));
global $arformcontroller,$get_googlefonts_data;
$get_googlefonts_data = $arformcontroller->get_arf_google_fonts();
$google_font_array = array_chunk($get_googlefonts_data, 150);

foreach ($google_font_array as $key => $font_values) {
    $google_fonts_string = implode('|', $font_values);
    $google_font_url_one = '';
    if (is_ssl()) {
        $google_font_url_one = "https://fonts.googleapis.com/css?family=" . $google_fonts_string;
    } else {
        $google_font_url_one = "http://fonts.googleapis.com/css?family=" . $google_fonts_string;
    }

    echo '<link rel = "stylesheet" type = "text/css" href = "' . $google_font_url_one . '" />';
}
function arf_google_font_listing() {
    global $get_googlefonts_data;

    if (count($get_googlefonts_data) > 0) {
        foreach ($get_googlefonts_data as $goglefontsfamily) {
            $arf_google_fonts[$goglefontsfamily] = $goglefontsfamily;
        }
    }
    return $arf_google_fonts;
}

$display = apply_filters('arfdisplayfieldoptions', array('label_position' => true));
$arfaction = $_REQUEST['arfaction'];

if ($arfaction == 'duplicate') {
    if ($id < 100) {
        $template_id = 1;
    } else {
        $template_id = 0;
    }
}

$arf_template_id = isset($template_id) ? $template_id : 0;

$res = get_option('arf_ar_type');
if (empty($autoresponder_all_data_query)) {
    $autoresponder_all_data_query = $wpdb->get_results("SELECT * FROM " . $MdlDb->autoresponder, 'ARRAY_A');
}
$res1 = $autoresponder_all_data_query[2];
$res2 = $autoresponder_all_data_query[0];
$res3 = $autoresponder_all_data_query[3];
$res4 = $autoresponder_all_data_query[4];
$res5 = $autoresponder_all_data_query[5];
$res6 = $autoresponder_all_data_query[7];
$res7 = $autoresponder_all_data_query[8];
$res14 = $autoresponder_all_data_query[9];

$ar_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->ar . " WHERE frm_id = %d ORDER BY id DESC", $id), 'ARRAY_A');


$aweber_arr = isset( $ar_data[0]['aweber'] ) ? maybe_unserialize( $ar_data[0]['aweber'] ) : array();

$mailchimp_arr = isset( $ar_data[0]['mailchimp'] ) ? maybe_unserialize( $ar_data[0]['mailchimp'] ) : array();

$madmimi_arr = isset($ar_data[0]['madmimi']) ? maybe_unserialize($ar_data[0]['madmimi']) : array();

$getresponse_arr = isset( $ar_data[0]['getresponse'] ) ? maybe_unserialize( $ar_data[0]['getresponse'] ) : array();

$gvo_arr = isset($ar_data[0]['gvo']) ? maybe_unserialize($ar_data[0]['gvo']) : array();

$ebizac_arr = isset($ar_data[0]['ebizac']) ? maybe_unserialize($ar_data[0]['ebizac']) : array();

$icontact_arr = isset($ar_data[0]['icontact']) ? maybe_unserialize($ar_data[0]['icontact']) : array();

$constant_contact_arr = isset($ar_data[0]['constant_contact']) ? maybe_unserialize($ar_data[0]['constant_contact']) : array();

$ar_data[0]['enable_ar'] = isset($ar_data[0]['enable_ar']) ? $ar_data[0]['enable_ar'] : '';
$global_enable_ar = maybe_unserialize(isset($ar_data[0]['enable_ar']) ? $ar_data[0]['enable_ar'] : '' );

$current_active_ar = '';

if (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) {
    $current_active_ar = 'mailchimp';
} else if (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) {
    $current_active_ar = 'aweber';
} else if (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) {
    $current_active_ar = 'icontact';
} else if (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) {
    $current_active_ar = 'constant_contact';
} else if (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) {
    $current_active_ar = 'getresponse';
} else if (isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1) {
    $current_active_ar = 'gvo';
} else if (isset($ebizac_arr['enable']) && $ebizac_arr['enable'] == 1) {
    $current_active_ar = 'ebizac';
} else if (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) {
    $current_active_ar = 'madmimi';
} else {
    $current_active_ar = 'mailchimp';
}

$current_active_ar = apply_filters('arf_current_autoresponse_set_outside', $current_active_ar, $ar_data);

$setvaltolic = 0;
global $arformsplugin;
$setvaltolic = $arformcontroller->$arformsplugin();

$remove_placeholders = false;

$wp_upload_dir = wp_upload_dir();
$upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';

    $arf_form_style = "<style type='text/css' class='added_new_style_css'>";
    if ($arf_template_id == 1) {
        $define_template = $id;
    } else {
        $define_template = $id;
    }

    if ( $arfaction != 'edit') {
        $id = rand();
    }
    

    $form_id = $id;
    $saving = true;
    $use_saved = true;
    $new_values = array();
    
    foreach ($newarr as $key => $value) {
        $new_values[$key] = $value;
    }

    $arfssl = false;
    if( is_ssl() ){
        $arfssl = true;
    }

    $is_form_save = true;

    $common_css_filename = FORMPATH . '/core/css_create_common.php';

    $css_rtl_filename = FORMPATH . '/core/css_create_rtl.php';

    if ($new_values['arfinputstyle'] == 'standard' || $new_values['arfinputstyle'] == 'rounded') {
        
        if ($arfaction == 'new' || $arfaction == 'edit') {

            $filename = FORMPATH . '/core/css_create_main.php';
            ob_start();
            include $filename;
            include $common_css_filename;
            if( is_rtl() ){
                include $css_rtl_filename;
            }
            $css = ob_get_contents();
            $css = str_replace('##', '#', $css);
            $arf_form_style .= $css;
            ob_end_clean();
        } else if ($arfaction == 'duplicate') {

            if( $record->is_template ){
                $form_css = maybe_unserialize($record->form_css);
                $input_style = isset($form_css['arfinputstyle']) ? $form_css['arfinputstyle'] : 'material';
                if( $input_style == 'material' ) {
                    if($new_values['arfinputstyle'] == 'rounded'){
                        $new_values['border_radius'] = 50;
                    } else {
                        $new_values['border_radius'] = 4;
                    }
                    $new_values['arffieldinnermarginssetting_1'] = 7;
                    $new_values['arffieldinnermarginssetting_2'] = 10;
                    $new_values['arfcheckradiostyle'] = 'default';
                    $new_values['arfsubmitborderwidthsetting'] = '0';
                    $new_values['arfsubmitbuttonstyle'] = 'flat';
                    $new_values['arfmainfield_opacity'] = 0;
                    $new_values['arffieldinnermarginssetting'] = '7px 10px 7px 10px';
                }
            }
            $filename = FORMPATH . '/core/css_create_main.php';

            ob_start();
            include $filename;
            include $common_css_filename;
            if( is_rtl() ){
                include $css_rtl_filename;
            }
            $css = ob_get_contents();
            $css = str_replace('##', '#', $css);
            $arf_form_style .= $css;
            ob_end_clean();
        }
    } else if ($new_values['arfinputstyle'] == 'material') {

        if( $arfaction == 'duplicate' && isset($record) && isset($record->is_template) && $record->is_template ){
            $remove_placeholders = true;
            $form_css = maybe_unserialize($record->form_css);
            $input_style = isset($form_css['arfinputstyle']) ? $form_css['arfinputstyle'] : 'material';
            if( $input_style != 'material') {
                $new_values['arffieldinnermarginssetting_1'] = 0;
                $new_values['arffieldinnermarginssetting_2'] = 0;
                $new_values['border_radius'] = 0;
                $new_values['arfcheckradiostyle'] = 'material';
                $new_values['arfsubmitborderwidthsetting'] = '2';
                $new_values['arfsubmitbuttonstyle'] = 'border';
                $new_values['arfmainfield_opacity'] = 1;
                $new_values['arfsubmitbuttonxoffsetsetting'] = '1';
                $new_values['arfsubmitbuttonyoffsetsetting'] = '2';
                $new_values['arfsubmitbuttonblursetting'] = '3';
                $new_values['arfsubmitbuttonshadowsetting'] = '0';
                $new_values['arffieldinnermarginssetting'] = '0px 0px 0px 0px';
                
                $new_values['hide_labels'] = 0;
                
                $newarr['hide_labels'] = 0;
            }
        }

        $filename = FORMPATH . '/core/css_create_materialize.php';

        ob_start();

        include $filename;
        include $common_css_filename;
        if( is_rtl() ){
            include $css_rtl_filename;
        }
        $css = ob_get_contents();

        $css = str_replace('##', '#', $css);

        $arf_form_style .= $css;

        ob_end_clean();

    } else if( $new_values['arfinputstyle'] == 'material_outlined' ){
        if( $arfaction == 'duplicate' && isset($record) && isset($record->is_template) && $record->is_template ){
            $remove_placeholders = true;
            $form_css = maybe_unserialize($record->form_css);
            $input_style = isset($form_css['arfinputstyle']) ? $form_css['arfinputstyle'] : 'material';
                $new_values['arffieldinnermarginssetting_1'] = 16;
                $new_values['arffieldinnermarginssetting_2'] = 16;
                $new_values['arffieldinnermarginssetting_3'] = 16;
                $new_values['arffieldinnermarginssetting_4'] = 16;
                
                $new_values['border_radius'] = 0;
                $new_values['arfcheckradiostyle'] = 'material';
                $new_values['arfsubmitborderwidthsetting'] = '2';
                $new_values['arfsubmitbuttonstyle'] = 'border';
                $new_values['arfmainfield_opacity'] = 1;
                $new_values['arfsubmitbuttonxoffsetsetting'] = '1';
                $new_values['arfsubmitbuttonyoffsetsetting'] = '2';
                $new_values['arfsubmitbuttonblursetting'] = '3';
                $new_values['arfsubmitbuttonshadowsetting'] = '0';
                $new_values['arffieldinnermarginssetting'] = '16px 16px 16px 16px';
                $new_values['arfsubmitbuttonstyle'] = 'border';


                $newarr['arffieldinnermarginssetting_1'] = 16;
                $newarr['arffieldinnermarginssetting_2'] = 16;
                $newarr['arffieldinnermarginssetting_3'] = 16;
                $newarr['arffieldinnermarginssetting_4'] = 16;
                $newarr['arffieldinnermarginssetting'] = '16px 16px 16px 16px';
                $newarr['arfsubmitborderwidthsetting'] = '2';
                $newarr['arfsubmitbuttonstyle'] = 'border';
        }

        $filename = FORMPATH . '/core/css_create_materialize_outline.php';

        ob_start();

        include $filename;
        include $common_css_filename;
        if( is_rtl() ){
            include $css_rtl_filename;
        }
        $css = ob_get_contents();

        $css = str_replace('##', '#', $css);

        $arf_form_style .= $css;

        ob_end_clean();
    }
    $arf_form_style .= "</style>";
    echo $arf_form_style;

$prepg_temp = addslashes(esc_html__("Previous", "ARForms"));
$next_temp = addslashes(esc_html__("Next", "ARForms"));
$default_selected_tmp = addslashes(esc_html__("wizard", "ARForms"));

if (isset($values['fields'])) {
    foreach ($values['fields'] as $field) {

        if ($field["type"] == "break") {
            $prepg_temp = esc_attr($field["pre_page_title"]);
            $next_temp = esc_attr($field["next_page_title"]);
            $default_selected_tmp = esc_attr($field['page_break_type']);
            break;
        }
    }
}
$form_options = isset($record->options) ? maybe_unserialize($record->options) : array();

$arf_field_order = (isset($form_options['arf_field_order']) && $form_options['arf_field_order'] != '') ? $form_options['arf_field_order'] : '';

$arf_inner_field_order = ( isset( $form_options['arf_inner_field_order'] ) && '' != $form_options['arf_inner_field_order'] ) ? $form_options['arf_inner_field_order'] : '';

$arf_field_resize_width = (isset($form_options['arf_field_resize_width']) && $form_options['arf_field_resize_width'] != '') ? $form_options['arf_field_resize_width'] : '';

$arf_inner_field_resize_width = ( isset( $form_options['arf_inner_field_resize_width'] ) && $form_options['arf_inner_field_resize_width'] != '' ) ? $form_options['arf_inner_field_resize_width'] : '';

if( $arf_field_order != '' ){
    $arf_field_order = json_decode( $arf_field_order, true );
    $arf_field_order = json_encode(array_filter($arf_field_order));
}

if( '' != $arf_inner_field_order ){
    $arf_inner_field_order = json_decode( $arf_inner_field_order, true );
    $arf_inner_field_order = json_encode( array_filter( $arf_inner_field_order ) );
}

if( $arf_field_resize_width != '' ){
    $arf_field_resize_width = json_decode( $arf_field_resize_width, true );
    $arf_field_resize_width = json_encode(array_filter($arf_field_resize_width));
}

if( '' != $arf_inner_field_resize_width ){
    $arf_inner_field_resize_width = json_decode( $arf_inner_field_resize_width, true );
    $arf_inner_field_resize_width = json_encode( array_filter( $arf_inner_field_resize_width ) );
}

$wp_upload_dir = wp_upload_dir();
if (is_ssl()) {
    $upload_css_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/');
} else {
    $upload_css_url = $wp_upload_dir['baseurl'] . '/arforms/';
}
$form_opts['arf_form_other_css'] = (isset($form_opts['arf_form_other_css']) && $form_opts['arf_form_other_css']!='') ? $arformcontroller->br2nl($form_opts['arf_form_other_css']) : '';

?>
<script type="text/javascript">
    function arfSkinJson() {
        var skinJson;
        skinJson = <?php echo json_encode($skinJson); ?>;
        return skinJson;
    }
</script>
<style type="text/css" id="arf_form_other_css_<?php echo $id;?>">
    <?php 
    if( isset($form_opts['arf_form_other_css']) ){
        if($arfaction == 'new' || $arfaction == 'duplicate' ){
            echo $temp_arf_form_other_css = preg_replace('/(-|_)('.$define_template.')/', '${1}'.$id, $form_opts['arf_form_other_css'], -1, $count);            
            $form_opts['arf_form_other_css'] = $temp_arf_form_other_css;
        } else {
            echo $form_opts['arf_form_other_css'];
        }
    }
?>
</style>
<?php do_action('arf_display_additional_css_in_editor'); ?>
<input type="hidden" id="arf_db_json_object" value='<?php echo json_encode($skinJson->skins->custom); ?>' />
<input type="hidden" id="arf_browser_info" value='<?php echo json_encode( $browser_info ); ?>' />
<style type="text/css" id='arf_form_<?php echo $id; ?>'>
<?php
$custom_css_array_form = array(
    'arf_form_outer_wrapper' => '.arf_form_outer_wrapper|.arfmodal',
    'arf_form_inner_wrapper' => '.arf_fieldset|.arfmodal',
    'arf_form_title' => '.formtitle_style',
    'arf_form_description' => 'div.formdescription_style',
    'arf_form_element_wrapper' => '.arfformfield',
    'arf_form_element_label' => 'label.arf_main_label',
    'arf_form_elements' => '.controls',
    'arf_submit_outer_wrapper' => 'div.arfsubmitbutton',
    'arf_form_submit_button' => '.arfsubmitbutton button.arf_submit_btn',
    'arf_form_next_button' => 'div.arfsubmitbutton .next_btn',
    'arf_form_previous_button' => 'div.arfsubmitbutton .previous_btn',
    'arf_form_success_message' => '#arf_message_success',
    'arf_form_error_message' => '.control-group.arf_error .help-block|.control-group.arf_warning .help-block|.control-group.arf_warning .help-inline|.control-group.arf_warning .control-label|.control-group.arf_error .popover|.control-group.arf_warning .popover',
    'arf_form_page_break' => '.page_break_nav',
);

foreach ($custom_css_array_form as $custom_css_block_form => $custom_css_classes_form) {

    if (isset($form->options[$custom_css_block_form]) and $form->options[$custom_css_block_form] != '') {

        $form->options[$custom_css_block_form] = $arformcontroller->br2nl($form->options[$custom_css_block_form]);

        if ($custom_css_block_form == 'arf_form_outer_wrapper') {
            $arf_form_outer_wrapper_array = explode('|', $custom_css_classes_form);

            foreach ($arf_form_outer_wrapper_array as $arf_form_outer_wrapper1) {
                if ($arf_form_outer_wrapper1 == '.arf_form_outer_wrapper')
                    echo '.ar_main_div_' . $form->id . '.arf_form_outer_wrapper { ' . $form->options[$custom_css_block_form] . ' } ';
                if ($arf_form_outer_wrapper1 == '.arfmodal')
                    echo '#popup-form-' . $form->id . '.arfmodal{ ' . $form->options[$custom_css_block_form] . ' } ';
            }
        }
        else if ($custom_css_block_form == 'arf_form_inner_wrapper') {
            $arf_form_inner_wrapper_array = explode('|', $custom_css_classes_form);
            foreach ($arf_form_inner_wrapper_array as $arf_form_inner_wrapper1) {
                if ($arf_form_inner_wrapper1 == '.arf_fieldset')
                    echo '.ar_main_div_' . $form->id . ' ' . $arf_form_inner_wrapper1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                if ($arf_form_inner_wrapper1 == '.arfmodal')
                    echo '.arfmodal .arfmodal-body .ar_main_div_' . $form->id . ' .arf_fieldset { ' . $form->options[$custom_css_block_form] . ' } ';
            }
        }
        else if ($custom_css_block_form == 'arf_form_error_message') {
            $arf_form_error_message_array = explode('|', $custom_css_classes_form);

            foreach ($arf_form_error_message_array as $arf_form_error_message1) {
                echo '.ar_main_div_' . $form->id . ' ' . $arf_form_error_message1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
            }
        } else {
            echo '.ar_main_div_' . $form->id . ' ' . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
        }
    }
}

$arfdefine_date_formate_array = $arformcontroller->arfreturndateformate();
?>
</style>

<div class="arf_editor_wrapper">
    <div id="arfsaveformloader"><?php echo ARF_LOADER_ICON; ?></div>
    <input type="hidden" id="arf_control_labels" value="" data-field-id="" />
    <input type="hidden" id="arf_reset_styling" value="false" />
    <input type="hidden" id="arf_copying_fields" value="false" />
    <input type="hidden" id="arf_single_column_field_ids" value="" />
    <div id="arf_hidden_fields_html" style="display:none !important;height:0px !important;width:0px !important;visibility: hidden !important;"></div>
    <input type="hidden" name="arfwpversion" id="arfwpversion" value="<?php echo $GLOBALS['wp_version']; ?>" />
    <input type="hidden" name="arfchange_field" id="arfchange_field" />
    <input type="hidden" name="arfchange_inner_field" id="arfchange_inner_field" />
    <input type="hidden" name="arfdateformate" id="arfdateformate" data-wp-formate = "<?php echo $arfdefine_date_formate_array['arfwp_dateformate'];?>"  data-js-formate = "<?php echo $arfdefine_date_formate_array['arfjs_dateformate'];?>" />
    <input type="hidden" name="arfgettemplate_style" id="arfgettemplate_style" value="<?php echo (isset($_GET['templete_style']) && $_GET['templete_style'] !='') ? $_GET['templete_style'] : '';?>" />

    <form action="" method="POST" id="arf_current_form_export" name="arf_current_form_export">
        <input type="hidden" name="s_action" value="opt_export_form" />
        <input type="hidden" name="opt_export" value="" />
        <input type="hidden" name="export_button" value="export_button" />
        <input type="hidden" name="is_single_form" value="1" />
        <input type="hidden" name="frm_add_form_id_name" id="frm_add_form_id_name" value="<?php echo $form_id; ?>" />
    </form>

    <form name="arf_form" id="frm_main_form" method="post" onSubmit='return arfmainformedit(0);'>
        <input type="hidden" name="arfmainformurl" data-id="arfmainformurl" value="<?php echo ARFURL; ?>" />   
        <input type="hidden" name="arfmainformversion" id="arfmainformversion" value="<?php echo $arfversion; ?>" />
        <input type="hidden" name="arfuploadurl" id="arfuploadurl" value="<?php echo $upload_css_url; ?>"/>
        <input type="hidden" name="arfaction" id="arfaction" value="<?php echo $_GET['arfaction']; ?>" />
        <input type="hidden" name="arfajaxurl" id="arfajaxurl" class="arf_ajax_url" value="<?php echo $arfajaxurl; ?>" />
        <input type="hidden" name="arffiledragurl" data-id="arffiledragurl" value="<?php echo ARF_FILEDRAG_SCRIPT_URL; ?>" />
        <input type="hidden" name="arf_editor_nonce" data-id="arf_editor_nonce" value="<?php echo wp_create_nonce('arf_edit_form_nonce'); ?>" />
        <input type="hidden" name="prev_arfaction" value="<?php $_GET["arfaction"]; ?>" />

        <input type="hidden" name="frm_autoresponder_no" id="frm_autoresponder_no" value="" />

        <input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
        <input type="hidden" name="define_template" id="define_template" value="<?php echo isset($define_template) ? $define_template : 0; ?>" />
        <input type="hidden" id="arf_isformchange" name="arf_isformchange" data-value="1" value="1" />

        <input type="hidden" id="page_break_first_pre_btn_txt" value="<?php echo esc_attr($prepg_temp); ?>" />

        <input type="hidden" id="page_break_first_next_btn_txt" value="<?php echo esc_attr($next_temp); ?>" />

        <input type="hidden" id="page_break_first_select" value="<?php echo esc_attr($default_selected_tmp); ?>" />
        <input type ="hidden" id="changed_style_attr" value="" />

        <input type ="hidden" id="default_style_attr" value='<?php echo json_encode($newarr);?>' />

        <input type="hidden" id="arf_field_order" name="arf_field_order" value='<?php echo $arf_field_order; ?>' data-db-field-order='<?php echo ($_GET['arfaction']== 'edit') ? $arf_field_order : ''; ?>' />
        <input type="hidden" id="arf_inner_field_order" name="arf_inner_field_order" value='<?php echo $arf_inner_field_order; ?>' data-db-field-order='<?php echo ($_GET['arfaction']== 'edit') ? $arf_inner_field_order : ''; ?>' />
        <input type="hidden" id="arf_field_resize_width" name="arf_field_resize_width" value='<?php echo $arf_field_resize_width; ?>' data-db-field-resize='<?php echo ($_GET['arfaction']== 'edit') ? $arf_field_resize_width : ''; ?>' />
        <input type="hidden" id="arf_inner_field_resize_width" name="arf_inner_field_resize_width" value='<?php echo $arf_inner_field_resize_width ?>' data-db-field-resize='<?php echo ($_GET['arfaction'] == 'edit') ? $arf_inner_field_resize_width : ''; ?>' />

        <input type="hidden" id="arf_input_radius" name="arf_input_radius" value='<?php echo $newarr['border_radius']; ?>' />
        <?php $browser_info = $arrecordcontroller->getBrowser($_SERVER['HTTP_USER_AGENT']); ?>
        <input type="hidden" data-id="arf_browser_name" id="arf_browser_name" value="<?php echo $browser_info['name']; ?>" />
        <input type="hidden" data-id="arf_browser_version" id="arf_browser_version" value="<?php echo $browser_info['version']; ?>" />
        <input type="hidden" name="arf_pre_link" data-id="arf_pre_link" id="arf_pre_link" value="<?php echo $pre_link; ?>">
        <div class="arf_editor_header_belt">
            <div class="arf_editor_header_inner_belt">
                <div class="arf_editor_top_menu_wrapper">
                    <ul class="arf_editor_top_menu">
                        <li class="arf_editor_top_menu_item" id="mail_notification">
                            <span class="arf_editor_top_menu_item_icon">
                                <svg viewBox="0 -4 32 32">
                                <g id="email"><path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M27.321,22.868H3.661c-1.199,0-2.172-0.973-2.172-2.172V3.053c0-1.2,0.973-2.203,2.172-2.203h23.66c1.199,0,2.171,1.003,2.171,2.203v17.643C29.492,21.895,28.52,22.868,27.321,22.868zM27.501,20.894V3.69l-12.28,9.268v0.008l-0.005-0.004l-0.005,0.004v-0.008L3.484,3.676v17.218H27.501z M24.994,2.844H5.95l9.267,7.377L24.994,2.844z"/></g>
                                </svg>
                            </span>
                            <label class="arf_editor_top_menu_label">
                                <?php echo addslashes(esc_html__('Email Notifications', 'ARForms')); ?>
                            </label>
                        </li>
                        <li class="arf_editor_top_menu_item" id="conditional_law">
                            <span class="arf_editor_top_menu_item_icon">
                                <svg viewBox="0 -5 32 32">
                                <g id="conditional_law"><path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M1.489,22.819V20.85H23.5v1.969H1.489z M10.213,13.263l2.246,2.246l5.246-5.246l1.392,1.392l-5.246,5.246l0.013,0.013l-1.392,1.392l-0.013-0.013l-0.013,0.013l-1.392-1.392l0.013-0.013l-2.246-2.246L10.213,13.263z M1.489,5.85H23.5v1.969H1.489V5.85z M1.489,0.85H23.5v1.969H1.489V0.85z"/></g>
                                </svg>
                            </span>
                            <label class="arf_editor_top_menu_label">
                                <?php echo addslashes(esc_html__('Conditional Rule', 'ARForms')); ?>
                            </label>
                        </li>
                        <li class="arf_editor_top_menu_item" id="submit_action">
                            <span class="arf_editor_top_menu_item_icon">
                                <svg viewBox="0 -5 32 32">
                                <g id="submit_action"><path fill="none" stroke="#ffffff" fill-rule="evenodd" clip-rule="evenodd" stroke-width="1.7" d="M23.362,0.85v10.293c0,3.138-2.544,5.683-5.683,5.683h-7.33v3.283l-8.86-6.007l8.86-6.319v4.05h6.686c0.738,0,1.336-0.598,1.336-1.336V0.85H23.362z"/></g>
                                </svg>
                            </span>
                            <label class="arf_editor_top_menu_label">
                                <?php echo addslashes(esc_html__('Submit Action', 'ARForms')); ?>
                            </label>
                        </li>
                        <li class="arf_editor_top_menu_item" id="email_marketers">
                            <span class="arf_editor_top_menu_item_icon">
                                <svg viewBox="0 -3 32 32">
                                <g id="email_marketers"><path  stroke="#ffffff" fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" stroke-width="0.5" d="M23.287,23.217c-0.409,0.46-0.84,0.866-0.932,0.934c-0.092,0.068-0.568,0.417-1.088,0.745c-0.387,0.244-0.789,0.468-1.204,0.669c-5.41,2.64-11.02,1.559-12.981-4.493c-0.291-0.896-0.125-1.162-0.658-1.273c-0.998-0.209-2.2-0.696-2.647-1.711c-0.528-1.2-0.571-2.338-0.003-3.193c0.341-0.513,0.323-0.929-0.217-1.223c-3.604-1.958-1.974-5.485,0.918-8.376c2.536-2.537,6.438-5.428,9.759-3.627c0.54,0.293,1.352,0.39,1.911,0.135c0.513-0.235,1.032-0.436,1.555-0.597c1.414-0.435,4.297-0.813,4.985,1.057c0.509,1.382,0.654,3.366-0.127,4.745c-0.305,0.536-0.203,1.047,0.103,1.582c0.589,1.031,0.529,2.774,0.514,3.681c-0.019,1.043,0.299,1.927,0.67,2.809c0.239,0.568,0.521,1.013,0.623,1.038c0.069,0.017,0.119,0.054,0.134,0.119c0.048,0.209,0.081,0.413,0.101,0.613c0.035,0.341,0.105,0.926,0.164,1.311c0.034,0.226,0.056,0.459,0.061,0.704C24.961,20.623,24.314,22.061,23.287,23.217z M20.125,23.994c0.614,0.016,1.48-0.411,1.869-0.889c0.415-0.511,0.764-1.068,1.024-1.661c0.249-0.564-0.004-0.708-0.534-0.397c-2.286,1.34-5.727,1.179-7.432-0.95c-0.385-0.481-0.52-0.737-0.421-0.483c0.099,0.254,0.036,0.629-0.172,0.854c-0.209,0.224-0.23,0.61-0.025,0.843s0.537,0.25,0.72,0.055c0.184-0.194,0.351-0.326,0.374-0.297c0.022,0.029-0.106,0.204-0.29,0.39c-0.185,0.187-0.205,0.459-0.038,0.6c0.167,0.141,0.444,0.108,0.614-0.062c0.168-0.172,0.486-0.141,0.723,0.049c0.238,0.191,0.322,0.453,0.176,0.605c-0.147,0.152,0.136,0.512,0.666,0.732c0.529,0.22,1.025,0.291,1.082,0.233s0.167-0.068,0.246-0.024c0.081,0.044,0.116,0.11,0.077,0.149c-0.038,0.04,0.417,0.193,1.03,0.237C19.917,23.986,20.022,23.991,20.125,23.994zM22.358,20.167c-0.141,0.143-0.28,0.285-0.421,0.426l-0.128,0.126c-0.071,0.07,0.188-0.045,0.493-0.354C22.61,20.056,22.59,19.931,22.358,20.167z M4.795,16.74c0.122,0.274,0.447,0.299,0.684,0.079c0.236-0.221,0.504-0.19,0.634,0.05c0.131,0.24,0.098,0.572-0.105,0.76c-0.204,0.188-0.032,0.718,0.482,1.056c0.459,0.302,0.945,0.495,1.389,0.515c0.079,0.003,0.241,0.035,0.264,0.136c0.045,0.203,0.097,0.41,0.153,0.621c0.093,0.34,0.354,0.451,0.569,0.251c0.216-0.199,0.446-0.339,0.516-0.313c0.068,0.026,0.149,0.136,0.185,0.247c0.034,0.111-0.144,0.408-0.397,0.664c-0.253,0.255-0.292,0.935-0.03,1.493c0.027,0.059,0.056,0.117,0.084,0.174c0.271,0.553,0.725,0.794,0.944,0.574c0.221-0.22,0.544-0.116,0.752,0.215c0.209,0.332,0.251,0.745,0.064,0.946c-0.188,0.201-0.233,0.475-0.096,0.604c0.083,0.079,0.168,0.154,0.257,0.224c0.052,0.041,0.105,0.081,0.159,0.118c0.09,0.062,0.296-0.027,0.459-0.199s0.299-0.306,0.306-0.299c0.007,0.006-0.122,0.147-0.288,0.315c-0.165,0.168-0.152,0.408,0.038,0.524c0.189,0.117,0.468,0.078,0.614-0.07c0.146-0.147,0.485-0.114,0.777,0.044c0.291,0.157,0.45,0.352,0.34,0.467c-0.111,0.116,0.28,0.348,0.892,0.41c1.708,0.172,3.512-0.274,5.061-1.156c0.534-0.305,0.435-0.575-0.179-0.621c-4.634-0.335-10.049-4.076-6.684-8.961c0.198-0.287-1.173-1.688-1.188-2.397c-0.038-1.685,0.779-2.368,2.145-3.229c0.763-0.481,1.711-0.692,2.656-0.677c0.613,0.011,1.134,0.093,1.171,0.056c0.038-0.036,0.095-0.077,0.126-0.092c0.023-0.01,0.021,0.003,0.005,0.029c-0.016,0.023,0.005,0.007,0.052-0.031c0.037-0.028,0.071-0.051,0.092-0.061c0.037-0.015,0.1-0.025,0.14-0.024c0.04,0.002,0.002,0.072-0.085,0.154c-0.086,0.083-0.107,0.162-0.047,0.175c0.061,0.014,0.214-0.074,0.351-0.192c0.137-0.12-0.172-0.489-0.76-0.67c-0.111-0.035-0.225-0.064-0.338-0.09c-0.6-0.133-1.115-0.09-1.13-0.078c-0.014,0.013-0.509,0.147-1.072,0.394c-0.395,0.173-0.784,0.379-1.166,0.612c-0.524,0.321-0.615,0.336-0.234-0.018c0.38-0.354,0.217-0.474-0.328-0.189c-2.063,1.079-3.949,3.012-5.192,4.528c-0.098,0.12-0.251,0.198-0.421,0.239c-0.263,0.064-0.495,0.026-0.505,0.036c-0.011,0.01-0.342,0.127-0.646,0.396c-0.305,0.27-0.69,0.857-1.028,1.174C4.896,15.969,4.673,16.466,4.795,16.74z M13.062,2.367c-0.99-0.478-2.052-0.443-3.087-0.101C9.392,2.458,8.606,3.06,8.177,3.502C7.292,4.417,6.353,5.387,5.34,6.434C4.709,7.081,4.212,7.589,3.828,7.983c-0.43,0.44-0.777,0.788-0.772,0.779c0.004-0.009,0.352-0.376,0.779-0.82c1.123-1.165,2.877-2.98,4.211-4.366c0.427-0.444,0.737-0.784,0.691-0.761C8.693,2.838,8.302,3.211,7.869,3.648C6.887,4.636,5.564,5.986,4.004,7.587c-0.429,0.441-0.64,0.513-0.437,0.18c0.204-0.333,0.054-0.217-0.28,0.301C2.964,8.567,2.731,9.077,2.669,9.577c-0.172,1.4,0.531,2.441,1.545,3.169c0.499,0.359,1.162,0.104,1.445-0.444c1.648-3.197,4.321-6.447,7.404-8.688C13.562,3.254,13.617,2.634,13.062,2.367z M18.808,1.454c-0.61,0.061-1.088,0.308-1.111,0.332c-0.022,0.023-0.054,0.037-0.069,0.032c-0.015-0.006-0.082,0.015-0.15,0.047c-0.039,0.019-0.079,0.039-0.12,0.061c-0.28,0.148-0.556,0.303-0.829,0.464c-0.451,0.266-0.877,0.668-1.068,0.775c-0.192,0.106-0.638,0.338-0.969,0.573c-0.2,0.142-0.398,0.287-0.59,0.44c-0.455,0.361-0.897,0.735-1.33,1.116c-1.043,1.074-2.101,2.163-3.173,3.271C8.11,10.15,7.034,11.902,6.26,13.861c-0.003,0.01-0.01,0.018-0.017,0.026C6.234,13.9,6.183,14,6.086,14.062c-0.048,0.031-0.108,0.063-0.185,0.094c-0.021,0.009-0.041,0.017-0.063,0.026c-0.012,0.005-0.02,0.008-0.031,0.013c-0.526,0.196-0.864,0.478-1.054,0.809c-0.304,0.536,0.189,0.728,0.624,0.291c0.177-0.178,0.349-0.351,0.516-0.52c0.435-0.438,0.596-0.87,0.594-1.065c-0.002-0.09,0.04-0.196,0.14-0.316c1.955-2.384,5.12-5.258,8.391-5.892c0.262-0.051,0.546-0.09,0.808-0.122c0.448-0.055,0.915-0.111,1.044-0.113c0.149-0.002,0.23,0.022,0.194,0.055c-0.052,0.048,0.407,0.131,0.994,0.315c0.15,0.048,0.301,0.102,0.449,0.162c0.57,0.232,1.245,0.367,1.585,0.232c0.341-0.134,1.063-0.489,1.348-1.037C22.479,4.995,21.533,1.183,18.808,1.454z M22.605,15.494c-0.452-0.864-0.868-1.535-0.877-2.836c-0.006-1.052,0.049-2.333-0.383-3.319c-0.349-0.798-0.817-0.735-1.315-0.426c-0.522,0.325-0.952,0.779-1.067,0.877c-0.114,0.099-0.315,0.316-0.519,0.43c-0.171,0.096-0.359,0.171-0.383,0.179c-0.087,0.027-0.176,0.045-0.267,0.056c-0.08,0.009-0.205,0.028-0.322,0.021c-0.178-0.01-0.719-0.381-1.319-0.51c-1.802-0.385-2.773,0.865-2.898,2.311c-0.053,0.615,0.316,0.868,0.621,0.568c0.307-0.3,0.551-0.494,0.548-0.433c-0.003,0.062-0.241,0.338-0.535,0.618c-0.293,0.28-0.447,0.892-0.221,1.313c0.137,0.254,0.306,0.49,0.509,0.695c0.079,0.08,0.044,0.151-0.017,0.23c-0.031,0.039-0.06,0.079-0.086,0.118c-0.046,0.066,0.154-0.096,0.449-0.365c0.295-0.268,0.56-0.451,0.595-0.411c0.035,0.041-0.285,0.43-0.714,0.873c-0.057,0.057-0.113,0.114-0.17,0.173c-0.43,0.441-0.993,1.259-1.083,1.87c-0.057,0.385-0.056,0.765-0.005,1.137c0.084,0.611,0.494,0.871,0.741,0.621c0.247-0.251,0.442-0.471,0.433-0.492c-0.006-0.012-0.012-0.025-0.017-0.038c-0.101-0.292,0.885-0.485,1.035-0.49c1.515-0.053,3.036-0.205,4.515-0.551c0.968-0.329,1.938-0.657,2.883-1.05c0.021-0.009,0.087-0.039,0.17-0.078C22.999,16.541,22.89,16.04,22.605,15.494z M22.397,17.352c-0.464,0.17-1.026,0.484-1.252,0.716c-0.225,0.231-0.757,0.452-1.188,0.48c-0.432,0.029-0.712-0.03-0.625-0.118c0.086-0.088-0.146-0.093-0.522-0.022c-0.376,0.071-0.921,0.36-1.216,0.659c-0.296,0.3-0.548,0.497-0.564,0.44c-0.017-0.056,0.146-0.288,0.362-0.516c0.215-0.229-0.021-0.353-0.531-0.297c-0.509,0.058-0.714,0.55-0.311,1.016c0.013,0.013,0.024,0.026,0.036,0.041c0.41,0.46,0.825,0.719,0.813,0.698c-0.013-0.021,0.179-0.24,0.424-0.489c0.246-0.25,0.545-0.223,0.704,0.037c0.158,0.26,0.2,0.57,0.057,0.718c-0.144,0.149,0.178,0.46,0.752,0.543c0.344,0.05,0.696,0.056,1.046,0.013c0.189-0.023,0.369-0.059,0.539-0.107c0.292-0.081,0.458-0.225,0.389-0.271c-0.068-0.046,0.225-0.442,0.653-0.884c0.142-0.146,0.282-0.292,0.425-0.438c0.428-0.442,0.875-1.183,0.893-1.667C23.297,17.419,22.862,17.184,22.397,17.352z M20.224,13.986c-0.675,0.086-0.916-0.718-0.896-1.272c0.018-0.495,0.16-1.292,0.775-1.37c0.698-0.09,0.877,0.721,0.896,1.272C20.982,13.111,20.84,13.907,20.224,13.986z M20.25,11.584c-0.436-0.287-0.567,0.841-0.56,1.032c0.012,0.35,0.059,0.913,0.388,1.13c0.443,0.293,0.554-0.848,0.56-1.032C20.626,12.364,20.58,11.802,20.25,11.584z M16.527,15.25c-0.631,0.081-0.824-0.869-0.808-1.313c0.02-0.579,0.198-1.278,0.86-1.364c0.639-0.081,0.794,0.85,0.81,1.314C17.369,14.465,17.19,15.165,16.527,15.25z M16.64,12.832c-0.478-0.316-0.571,1.04-0.555,1.2c0.033,0.307,0.098,0.771,0.382,0.959c0.434,0.287,0.549-0.828,0.56-1.038C17.014,13.603,16.966,13.047,16.64,12.832z M19.212,7.655c-0.071,0.071-0.145,0.131-0.162,0.134c-0.018,0.003,0.031-0.057,0.109-0.134c0.077-0.077,0.15-0.137,0.161-0.133C19.333,7.524,19.284,7.584,19.212,7.655z M16.305,3.161c-0.017,0.008-0.294,0.19-0.611,0.416s-0.256,0.101,0.13-0.292c0.385-0.393,0.762-0.69,0.84-0.659c0.077,0.03,0.035,0.163-0.094,0.291C16.442,3.044,16.324,3.153,16.305,3.161z M8.963,13.61c-0.011,0.014-0.023,0.021-0.03,0.017c-0.009-0.005-0.005-0.019,0.015-0.032C8.967,13.582,8.977,13.594,8.963,13.61z"/></g></svg>
                            </span>
                            <label class="arf_editor_top_menu_label">
                                <?php echo addslashes(esc_html__('Opt-ins', 'ARForms')); ?>
                            </label>
                        </li>
                        <li class="arf_editor_top_menu_item arf_editor_top_menu_dropdown">
                            <span class="arf_editor_top_menu_item_icon">
                                <svg viewBox="0 -3 32 32">
                                <g id="general_options"><path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M12.501,20.85v2.002H7.474V20.85H1.489v-2h5.985v-2.002h5.027v2.002h16.953v2H12.501z M18.473,14.853v-2.002H1.489v-2h16.984V8.849H23.5v2.002h5.954v2H23.5v2.002H18.473z M12.501,6.854H7.474V4.852H1.489v-2h5.985V0.85h5.027v2.002h16.953v2H12.501V6.854z"/></g></svg>
                            </span>
                            <label class="arf_editor_top_menu_label">
                                <?php echo addslashes(esc_html__('Other Options', 'ARForms')); ?>
                                <span class="arf_editor_top_menu_item_icon_drop_icon">
                                    <svg viewBox="1 1 12 10" width="12px" height="10px">
                                        <g id="arf_top_menu_arrow">
                                            <path fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M13.041,3.751L7.733,9.03c-0.169,0.167-0.39,0.251-0.611,0.251
                                                c-0.221,0-0.442-0.084-0.611-0.251L1.203,3.751C0.897,3.447,0.882,2.979,1.13,2.644C0.882,2.307,0.897,1.839,1.203,1.536
                                                c0.338-0.336,0.885-0.336,1.223,0l4.696,4.67l4.697-4.67c0.337-0.335,0.885-0.335,1.222,0c0.307,0.304,0.32,0.771,0.072,1.108
                                                C13.361,2.98,13.347,3.447,13.041,3.751z"/>
                                        </g>
                                    </svg>
                                </span>
                            </label>
                            <div class="arf_editor_top_dropdown_submenu_container">
                                <ul class="arf_editor_top_dropdown">
                                    <li class="arf_editor_top_dropdown_option" id="general_options"><?php echo addslashes(esc_html__('General Options', 'ARForms')); ?></li>
                                    <li class="arf_editor_top_dropdown_option" id="arf_hidden_fields_options"><?php echo esc_html__('Hidden Input Fields', 'ARForms'); ?></li>
                                    <li class="arf_editor_top_dropdown_option" id="arf_tracking_code"><?php echo addslashes(esc_html__('Submit Tracking Script', 'ARForms')); ?></li>
                                    <li class="arf_editor_top_dropdown_option <?php echo ($_GET['arfaction']=='new' || $_GET['arfaction']=='duplicate') ? 'arf_export_form_editor_note':''; ?>" id="arf_export_current_form_link"><?php echo addslashes(esc_html__('Export Form', 'ARForms')); ?></li>
                                    <?php do_action('arf_editor_general_options_menu'); ?>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="arf_editor_top_menu_button_wrapper">
                    <div class="arf_editor_shortcode_wrapper">
                        <div class="arf_editor_shortcode_icon_wrapper arfbelttooltip" id="arf_shortcodes_info" data-title="<?php echo addslashes(esc_html__('Shortcodes', 'ARForms')); ?>"></div>
                        <div class="arf_editor_form_shortcode_list_popup">
                            <div class="arf_editor_form_shortcode_list_content">
                                <?php
                                    $arf_saved_form_shortcode = "display:none;";
                                    $arf_unsaved_form_shortcode = "";
                                    if(isset($_GET['arfaction']) && $_GET['arfaction']== 'edit'){
                                        $arf_saved_form_shortcode = "";
                                        $arf_unsaved_form_shortcode = "display:none;";
                                    }
                                    $shortcode_form_id = (isset($_GET['arfaction']) && $_GET['arfaction'] == 'edit') ? $form_id : '{arf_form_id}';
                                ?>
                                <ul id="arf_editor_saved_form_shortcodes" class="arf_editor_form_shortcode_list" style="<?php echo $arf_saved_form_shortcode; ?>">
                                    <li class="arf_editor_form_shortcode_header"><span><?php echo addslashes(esc_html__("Shortcodes", "ARForms"));?></span></li>
                                    <li class="arf_editor_form_shortcode">
                                        <span class="arf_shortcode_label"><?php echo addslashes(esc_html__("Embed Inline Form", "ARForms"));?></span>
                                        <span class="arf_shortcode_content">[ARForms id=<?php echo $shortcode_form_id; ?>]</span>
                                    </li>
                                    <li class="arf_editor_form_shortcode">
                                        <span class="arf_shortcode_label"><?php echo addslashes(esc_html__("Embed Popup Form", "ARForms"));?></span>
                                        <span class="arf_shortcode_content">[ARForms_popup id=<?php echo $shortcode_form_id; ?> desc='Click here to open Form' type='link' width='800' modaleffect='fade_in' is_fullscreen='no' overlay='0.6' is_close_link='yes' modal_bgcolor='#000000']</span>
                                    </li>
                                    <li class="arf_editor_form_shortcode">
                                        <span class="arf_shortcode_label"><?php echo addslashes(esc_html__("PHP Function", "ARForms"));?></span>
                                        <span class="arf_shortcode_content">&lt;?php global $maincontroller; echo $maincontroller->get_form_shortcode(array('id'=>'<?php echo $shortcode_form_id; ?>')); ?&gt;</span>
                                        <span class="arf_shortcode_reference_link_container"><a href="<?php echo ARFURL; ?>/documentation/index.html#shortcodes" target="_blank" class="arf_shortcode_reference_link"><?php echo esc_html__("More Info.", "ARForms"); ?></a></span>
                                    </li>
                                </ul>

                                <ul id="arf_editor_unsaved_form_shortcodes" class="arf_editor_form_shortcode_list" style="<?php echo $arf_unsaved_form_shortcode; ?>">
                                    <li class="arf_editor_form_shortcode_header"><span><?php echo addslashes(esc_html__("Shortcodes", "ARForms"));?></span></li>
                                    <li class="arf_editor_form_shortcode">
                                        <span class="arf_shortcode_content"><?php echo addslashes(esc_html__("Please save form to generate shortcode.", "ARForms")); ?></span>
                                    </li>
                                </ul>

                            </div>
                        </div>
                    </div>
                    <button type="submit" name="arf_save" class="arf_top_menu_save_button rounded_button btn_green">
                        <?php echo addslashes(esc_html__('Save', 'ARForms')); ?>                        
                    </button>
                    <button type="button" name="arf_preview" class="arf_top_menu_preview_button arfbelttooltip" data-url="<?php echo ($action == 'new') ? $pre_link . '&form_id=' . $id : $pre_link; ?>" data-default-url="<?php echo ($action == 'new') ? $pre_link : '';?>" onclick="arfgetformpreview();" data-title="<?php echo addslashes(esc_html__('Preview', 'ARForms')); ?>" >
                        <span class="arf_top_menu_preview_button_icon">
                            <svg viewBox="0 0 30 30" width="40px" height="35px">
                            <g id="preview"><path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M12.993,15.23c-7.191,0-11.504-7.234-11.504-7.234S5.801,0.85,12.993,0.85c7.189,0,11.504,7.19,11.504,7.19S20.182,15.23,12.993,15.23z M12.993,2.827c-5.703,0-8.799,5.214-8.799,5.214s3.096,5.213,8.799,5.213c5.701,0,8.797-5.213,8.797-5.213S18.694,2.827,12.993,2.827zM12.993,11.572c-1.951,0-3.531-1.581-3.531-3.531s1.58-3.531,3.531-3.531c1.949,0,3.531,1.581,3.531,3.531S14.942,11.572,12.993,11.572z"/></g>
                            </svg>
                        </span>
                    </button>
                    <button type="button" name="arf_reset" class="arf_top_menu_reset_button arfbelttooltip" data-title="<?php echo addslashes(esc_html__('Reset Style', 'ARForms')); ?>" onclick="reset_style_functionality();" >
                        <span class="arf_top_menu_reset_button_icon">
                            <svg viewBox="-4 -1 30 30" width="40px" height="35px">
                            <g id="preview"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M16.07,0.293c-0.26-0.107-0.482-0.063-0.666,0.134l-2.037,1.827c-0.679-0.641-1.455-1.138-2.328-1.49  c-0.872-0.352-1.775-0.528-2.708-0.528c-0.99,0-1.937,0.194-2.838,0.581C4.591,1.204,3.814,1.724,3.16,2.378  C2.506,3.032,1.986,3.81,1.598,4.711c-0.387,0.901-0.58,1.847-0.58,2.837s0.193,1.937,0.58,2.838  c0.388,0.901,0.908,1.679,1.562,2.332c0.654,0.654,1.432,1.175,2.333,1.562c0.901,0.388,1.848,0.581,2.838,0.581  c1.092,0,2.13-0.23,3.113-0.69s1.821-1.109,2.514-1.947c0.051-0.063,0.075-0.135,0.071-0.214c-0.003-0.079-0.033-0.145-0.091-0.195  L12.634,10.5c-0.07-0.058-0.149-0.086-0.238-0.086c-0.102,0.013-0.175,0.051-0.219,0.114c-0.464,0.604-1.031,1.069-1.705,1.4  c-0.672,0.33-1.387,0.494-2.142,0.494c-0.66,0-1.29-0.128-1.89-0.386c-0.601-0.257-1.119-0.604-1.558-1.042  c-0.438-0.438-0.785-0.957-1.042-1.557s-0.386-1.23-0.386-1.891c0-0.659,0.129-1.29,0.386-1.89s0.604-1.119,1.042-1.557  C5.322,3.664,5.84,3.316,6.441,3.059c0.6-0.257,1.229-0.386,1.89-0.386c1.275,0,2.384,0.436,3.323,1.305L9.882,6.062  c-0.196,0.19-0.24,0.41-0.133,0.657C9.858,6.973,10.044,7.1,10.311,7.1h5.521c0.165,0,0.308-0.061,0.429-0.181  c0.12-0.121,0.181-0.264,0.181-0.429V0.855C16.442,0.589,16.318,0.401,16.07,0.293z"></path></g>
                            </svg>
                        </span>
                    </button>
                    <button type="button" name="arf_cancel" class="arf_top_menu_cancel_button arfbelttooltip" onClick="window.location = '<?php echo admin_url('admin.php?page=ARForms'); ?>'" data-title="<?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?>">
                        <span class="arf_top_menu_cancel_button_icon">
                            <svg viewBox="-5 -1 30 30" width="45px" height="45px">
                            <g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
        <div class="arf_editor_header_shortcode_belt">
            <div class="arf_editor_header_form_title"></div>
            <div class="arf_editor_header_form_width">
                <div class="arf_editor_form_width_wrapper">
                    <span class="arf_editor_form_width_label"><?php echo addslashes(esc_html__('Width', 'ARForms')); ?></span>
                    <span class="arfform_width_header_span" >
                        <?php
                            $form_width_unit_opts = array(
                                'px' => 'px',
                                '%' => '%'
                            );

                            echo $maincontroller->arf_selectpicker_dom( 'arf_editor_form_width_unit', 'arf_editor_form_width_unit', 'arf_editor_form_width_unit_dd', 'width:40px;', $newarr['form_width_unit'], array(), $form_width_unit_opts );
                        ?>
                    </span>
                    <span class="arf_editor_form_width_input_wrapper">
                        <input type="text" name="arf_editor_form_width" id="arf_editor_form_width" class="arf_editor_form_width_input" value="<?php echo esc_attr($newarr['arfmainformwidth']) ?>" />
                    </span>
                    <div class="arf_display_form_id_editor <?php echo ($arfaction == 'edit') ? '' : 'arf_save_form_id_note' ?>">(Form ID: <?php echo ($arfaction == 'edit') ? $form_id : '{arf_form_id}'; ?>)</div>
                </div>
            </div>           
        </div>

        <div class="arf_form_editor_wrapper">
            <div class="arf_form_element_wrapper">

                <ul class="arf_form_style_tabs">
                    <li class="arf_form_element_type_tab_item active" data-id="arf_form_input_fields_container"><?php echo addslashes(esc_html__('Input Fields', 'ARForms')); ?></li>
                    <li class="arf_form_element_type_tab_item" data-id="arf_form_other_fields_container"><?php echo addslashes(esc_html__('Other Fields', 'ARForms')); ?></li>
                </ul>
                <ul class="arf_form_elements_container active" id="arf_form_input_fields_container">
                    <?php
                    $advancedFields = $arfieldhelper->pro_field_selection();

                    $allFields = array_merge($arffield_selection, $advancedFields);
                    $input_fields = $arfieldhelper->arf_input_field_keys();

                    $other_fields = $arfieldhelper->arf_other_fields_keys(); 
                    $sortedFields = $arfieldhelper->arf_field_element_orders();
                    $full_width_elm_array = $arfieldhelper->arf_full_width_field_element();

                    foreach( $sortedFields as $key ){
                        if( in_array($key,$input_fields) ){
                            $icon = $allFields[$key]['icon'];
                            ?>
                            <li class="arf_form_element_item frmbutton frm_t<?php echo $key ?>" id="<?php echo $key; ?>" data-field-id="<?php echo $id; ?>" data-type="<?php echo $key; ?>">
                                <div class="arf_form_element_item_inner_container">
                                    <span class="arf_form_element_item_icon">
                                        <?php echo $icon; ?>
                                    </span>
                                    <label class="arf_form_element_item_text"><?php echo $allFields[$key]['label']; ?></label>
                                </div>
                            </li>
                            <?php
                        }
                    }
                ?>
                </ul>

                <ul class="arf_form_elements_container" id="arf_form_other_fields_container">
                    <?php
                        foreach( $sortedFields as $key ){
                            if( in_array( $key, $other_fields ) ){
                                $icon = $allFields[$key]['icon'];
                                $full_width_cls = '';
                                if( in_array( $key, $full_width_elm_array ) ){
                                    $full_width_cls = ' arf_full_width_field_element ';
                                }
                                ?>
                                <li class="arf_form_element_item <?php echo $full_width_cls; ?> frmbutton frm_t<?php echo $key ?>" id="<?php echo $key; ?>" data-field-id="<?php echo $id; ?>" data-type="<?php echo $key; ?>">
                                    <div class="arf_form_element_item_inner_container">
                                        <span class="arf_form_element_item_icon">
                                            <?php echo $icon; ?>
                                        </span>
                                        <label class="arf_form_element_item_text"><?php echo $allFields[$key]['label']; ?></label>
                                    </div>
                                </li>
                                <?php
                            }
                        }
                    ?>
                </ul>
        <div class="arf_form_element_resize"></div>
        <?php
            $svg_style = "";
            $viewBox = "0 -6 30 30";
            if( is_rtl() ){
                $svg_style = "position:relative;left:15px;transform:rotateY(180deg);-webkit-transform:rotateY(180deg);-o-transform:rotateY(180deg);-moz-transform:rotateY(180deg);-ms-transform:rotateY(180deg);";
                $viewBox = "-13 -6 30 30";
            }
        ?>
        <button type="button" class="arf_hide_form_element_wrapper"><svg viewBox="<?php echo $viewBox; ?>" width="25px" height="45px" style="<?php echo $svg_style ; ?>"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4E5462" d="M3.845,6.872l4.816,4.908l-1.634,1.604L0.615,6.849L0.625,6.84  L0.617,6.832L7.152,0.42l1.603,1.634L3.845,6.872z"/></svg></button> 
            </div>
            <?php echo str_replace('id="{arf_id}"','id="arfeditor_loader" style="display:block;" ',ARF_LOADER_ICON)?>
            <div class="arf_form_editor_content" style="display:none;">
                <div class="arf_form_editor_inner_container" id="maineditcontentview">
                    <?php require(VIEWS_PATH . '/edit_form.php'); ?>
                </div>
            </div>
            <div class="arf_form_styling_tools">
                <ul class="arf_form_style_tabs">
                    <li class="arf_form_style_tab_item active" data-id="arf_form_styling_tools"><?php echo addslashes(esc_html__('Style Options', 'ARForms')); ?></li>
                    <li class="arf_form_style_tab_item" data-id="arf_form_custom_css"><?php echo addslashes(esc_html__('Custom CSS', 'ARForms')); ?></li>
                </ul>
                <input type="hidden" name="arf_styling_height" id="arf_styling_height"/>
                <input type="hidden" name="arf_styling_content_height" id="arf_styling_content_height"/>
                <div class="arf_form_style_tab_container active" id="arf_form_styling_tools">
                    <input type="hidden" name="arfmf" value="<?php echo $id; ?>" id="arfmainformid" />
                    <div class="arf_form_style_tab_accordion">
                        <div class="arf_form_accordion_tabs">
                            <dl class="arf_accordion_tab_color_options active">
                                <dd>
                                    <a href="javascript:void(0)" data-target="arf_accordion_tab_color_options"><?php echo addslashes(esc_html__('Basic Styling Options', 'ARForms')); ?></a>
                                    <div class="arf_accordion_container active">
                                        <div class="arf_input_style_container">
                                            <div class="arf_accordion_container_row arf_padding">
                                                <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Select Theme', 'ARForms')); ?></div>
                                            </div>
                                            <div class="arf_accordion_container_row">
                                                <div class='arf_accordion_inner_title arf_width_50'><?php echo esc_html__('Input Style', 'ARForms'); ?></div>
                                                <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                    <?php

                                                        $inputStyle = array();

                                                        $newarr['arfinputstyle'] = (isset($newarr['arfinputstyle']) && $newarr['arfinputstyle'] != '' ) ? $newarr['arfinputstyle'] : 'material';
                                                        $inputStyle = array(
                                                            'standard' => addslashes(esc_html__('Standard Style', 'ARForms')),
                                                            'rounded' => addslashes(esc_html__('Rounded Style', 'ARForms')),
                                                            'material' => addslashes(esc_html__('Material Style', 'ARForms')),
                                                            'material_outlined' => addslashes( esc_html__( 'Material Outlined', 'ARForms' ) ),
                                                        );

                                                        echo $maincontroller->arf_selectpicker_dom( 'arfinpst', 'arfmainforminputstyle', '', '', $newarr['arfinputstyle'], array(), $inputStyle );

                                                    ?>
                                                </div>
                                            </div>
                                            <div class="arf_input_style_loader_div">
                                                <div class="arf_imageloader arf_form_style_input_style_loader" id="arf_input_style_loader"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="arf_accordion_container_row_separator"></div>

                                        <div class="arf_color_scheme_container">
                                            <div class="arf_accordion_container_row arf_padding">
                                                <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Color Scheme', 'ARForms')); ?></div>
                                            </div>
                                            <div class="arf_accordion_container_row" style="height: auto;">
                                                <div class='arf_accordion_inner_title arf_custom_color_title'><?php echo addslashes(esc_html__('Choose Color', 'ARForms')) ?></div>
                                                <div class="arf_accordion_content_container arf_custom_color_div arf_right" style="margin-right: -4px;">
                                                    <input type="hidden" name="arfmcs" data-db-skin="<?php echo $active_skin; ?>" id="arf_color_skin" value="<?php echo $active_skin; ?>" data-default-skin="<?php echo $active_skin; ?>" />
                                                    <?php
                                                    if (isset($skinJson->skins) && !empty($skinJson->skins)) {
                                                        foreach ($skinJson->skins as $skin => $val) {
                                                            if( $skin == 'custom' ){
                                                                continue;
                                                            }
                                                            ?>
                                                            <div class="arf_skin_container <?php echo ($active_skin == $skin) ? 'active_skin' : ''; ?>" data-skin="<?php echo $skin; ?>" style="background:<?php echo $val->main; ?>;" id="arf_skin_<?php echo $skin; ?>">
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div class="arf_customize_color_div arf_right" style="width:100%;">
                                                    <div class="arf_customize_color_inner_label_div">
                                                        <div class='arf_accordion_inner_title arf_label_custom_color' style="width: 90%;"><?php echo addslashes(esc_html__('Custom Color', 'ARForms')); ?></div>
                                                    </div>
                                                    <div class="arf_customize_color_inner_control_div">
                                                        <?php $custom_bg_color = (isset($newarr['arfmainbasecolor']) && $newarr['arfmainbasecolor'] != "" ) ? esc_attr($newarr['arfmainbasecolor']) : $skinJson->skins->$active_skin->main ?>
                                                        <div class="arf_skin_container <?php echo ($active_skin == 'custom') ? 'active_skin' : ''; ?>" data-skin="custom" style="background:<?php echo $custom_bg_color; ?>;margin-top: 11px;margin-right: 11px;margin-left: -5px;"></div>
                                                        <div class="arf_custom_color">
                                                            <div class="arf_custom_color_icon">
                                                                <svg viewBox="-6 -10 35 35">
                                                                <g id="paint_brush"><path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M15.948,7.303L15.875,7.23l0.049-0.049l-2.459-2.459l3.944-3.872l2.313,0.024v2.654L15.948,7.303z M12.631,6.545c0.058,0.039,0.111,0.081,0.167,0.122c0.036,0.005,0.066,0.011,0.066,0.011c0.022,0.008,0.034,0.023,0.056,0.032l1.643,1.643c0.58,5.877-7.619,6.453-7.619,6.453c-5.389,0.366-5.455-1.907-5.455-1.907c3.559,1.164,6.985-5.223,6.985-5.223C11.001,4.915,12.631,6.545,12.631,6.545z"/></g>
                                                                </svg>
                                                            </div>
                                                            <div class="arf_custom_color_label" style="width: 70px;"><?php echo addslashes(esc_html__('Custom', 'ARForms')); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="arf_color_scheme_loader_div">
                                                <div class="arf_imageloader arf_form_style_color_scheme_loader" id="arf_color_scheme_loader"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Font Options', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row">
                                            <div class='arf_accordion_inner_title arf_width_50'><?php echo addslashes(esc_html__('Font Family', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <?php
                                                    $fontsarr = array(
                                                        '' => array(
                                                            'inherit' => addslashes( esc_html__('Inherit from theme', 'ARForms') ),
                                                        ),
                                                        'default||' . addslashes(esc_html__('Default Fonts', 'ARForms')) => $arformcontroller->get_arf_default_fonts(),
                                                        'google||' . addslashes(esc_html__('Google Fonts', 'ARForms')) => arf_google_font_listing(),
                                                    );

                                                    $newarr['arfcommonfont'] = ( isset( $newarr['arfcommonfont'] ) && $newarr['arfcommonfont'] != '' ) ? $newarr['arfcommonfont'] : 'Helvetica';

                                                    echo $maincontroller->arf_selectpicker_dom( 'arfcommonfont', 'arfcommonfontfamily', '','', $newarr['arfcommonfont'], array(), $fontsarr, true, array(), false, array(), false, array(), true );

                                                ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_accordion_container_row_input_size" >
                                            <div class='arf_accordion_inner_title arfwidth40'><?php echo esc_html__('Input Field size', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arfwidth60" style="margin-left: -5px;">
                                                <div class="arf_slider_wrapper">
                                                    <div id="arf_mainfieldcommonsize" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div>
                                                  
                                                     <input id="arfmainfieldcommonsize_exs" class="arf_slider_input" data-slider-id='arfmainfieldcommonsize_exsSlider' type="text" data-slider-value="<?php echo isset($newarr['arfmainfieldcommonsize']) ? esc_attr($newarr['arfmainfieldcommonsize']) : '3' ?>" />
                                                    <div class="arf_slider_unit_data">
                                                        <div style="float:left;margin-left: -7px;"><?php echo addslashes(esc_html__('1', 'ARForms')); ?></div>
                                                        <div style="float:right;margin-right:-15px;"><?php echo addslashes(esc_html__('10', 'ARForms')); ?></div>
                                                    </div>

                                                    <input type="hidden" name="arfmainfieldcommonsize" style="width:100px;" class="txtxbox_widget"  id="arfmainfieldcommonsize" value="<?php echo isset($newarr['arfmainfieldcommonsize']) ? esc_attr($newarr['arfmainfieldcommonsize']) : '3' ?>" size="4" />
                                                </div>
                                            </div>
                                            <div class="arf_right arfmarginright">
                                                <div class="arf_custom_font" style="margin-top: 20px;">
                                                    <div class="arf_custom_font_icon">
                                                        <svg viewBox="-10 -10 35 35">
                                                        <g id="paint_brush">
                                                        <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M7.423,14.117c1.076,0,2.093,0.022,3.052,0.068v-0.82c-0.942-0.078-1.457-0.146-1.542-0.205  c-0.124-0.092-0.203-0.354-0.235-0.787s-0.049-1.601-0.049-3.504l0.059-6.568c0-0.299,0.013-0.472,0.039-0.518  C8.772,1.744,8.85,1.725,8.981,1.725c1.549,0,2.584,0.043,3.105,0.128c0.162,0.026,0.267,0.076,0.313,0.148  c0.059,0.092,0.117,0.687,0.176,1.784h0.811c0.052-1.201,0.14-2.249,0.264-3.145l-0.107-0.156c-2.396,0.098-4.561,0.146-6.494,0.146  c-1.94,0-3.936-0.049-5.986-0.146L0.954,0.563c0.078,0.901,0.11,1.976,0.098,3.223h0.84c0.085-1.062,0.141-1.633,0.166-1.714  C2.083,1.99,2.121,1.933,2.17,1.9c0.049-0.032,0.262-0.065,0.641-0.098c0.652-0.052,1.433-0.078,2.34-0.078  c0.443,0,0.674,0.024,0.69,0.073c0.016,0.049,0.024,1.364,0.024,3.947c0,1.313-0.01,2.602-0.029,3.863  c-0.033,1.776-0.072,2.804-0.117,3.084c-0.039,0.201-0.098,0.34-0.176,0.414c-0.078,0.075-0.212,0.129-0.4,0.161  c-0.404,0.065-0.791,0.098-1.162,0.098v0.82C4.861,14.14,6.008,14.117,7.423,14.117L7.423,14.117z"/>
                                                        </svg>
                                                    </div>

                                                    <div class="arf_custom_font_label"><?php echo addslashes(esc_html__('Advanced font options', 'ARForms')); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Form Width Settings', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Form Width', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container" style="<?php echo is_rtl() ? "width:50%;" : "width:58%;"; ?>" >
                                                <div class="arf_dropdown_wrapper">
                                                    <?php
                                                        $form_width_unit_opts = array(
                                                            'px' => 'px',
                                                            '%' => '%'
                                                        );

                                                        $form_width_unit_attr = array(
                                                            'data-arfstyle' => 'true',
                                                            'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id}.arf_form_outer_wrapper~|~arf_form_width_unit","material":".ar_main_div_{arf_form_id}.arf_form_outer_wrapper~|~arf_form_width_unit","material_outlined":".ar_main_div_{arf_form_id}.arf_form_outer_wrapper~|~arf_form_width_unit"}',
                                                            'data-arfstyleappend' => 'true',
                                                            'data-arfstyleappendid' => 'arf_{arf_form_id}_form_outer_wrapper'
                                                        );

                                                        echo $maincontroller->arf_selectpicker_dom( 'arffu', 'arffu', '', 'width:50px;', $newarr['form_width_unit'], $form_width_unit_attr, $form_width_unit_opts );
                                                    ?>
                                                </div>
                                                <input type="text" name="arffw" class="arf_small_width_txtbox arfcolor" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id}.arf_form_outer_wrapper~|~max-width{arf_form_width_unit}","material":".ar_main_div_{arf_form_id}.arf_form_outer_wrapper~|~max-width{arf_form_width_unit}","material_outlined":".ar_main_div_{arf_form_id}.arf_form_outer_wrapper~|~max-width{arf_form_width_unit}"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_outer_wrapper" value="<?php echo esc_attr($newarr['arfmainformwidth']) ?>" id="arf_form_width"/>
                                            </div>

                                        </div>

                                        <!-- Success message position --> 
                                        <div class="arf_accordion_container_row_separator"></div>
                                            <div class="arf_accordion_container_row arf_padding">
                                                <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Success/Error Message Position', 'ARForms')); ?></div>
                                            </div>
                                            <div class="arf_accordion_container_row arf_half_width">
                                                <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Position', 'ARForms')); ?></div>
                                                <div class="arf_accordion_content_container arf_align_right arf_right">                                                    
                                                    <div class="arf_toggle_button_group arf_two_button_group">

                                                        <?php $newarr['arfsuccessmsgposition'] = isset($newarr['arfsuccessmsgposition']) ? $newarr['arfsuccessmsgposition'] : 'top'; ?>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arfsuccessmsgposition'] == 'bottom') ? 'arf_success' : ''; ?>">
                                                            <input type="radio" name="arfsuccessmsgposition" class="visuallyhidden" id="success_msg_position_bottom" value="bottom" <?Php checked($newarr['arfsuccessmsgposition'], 'bottom'); ?> /><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?>
                                                        </label>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arfsuccessmsgposition'] == 'top') ? 'arf_success' : ''; ?>">
                                                            <input type="radio" name="arfsuccessmsgposition" class="visuallyhidden" id="success_msg_position_top" value="top" <?Php checked($newarr['arfsuccessmsgposition'], 'top'); ?> /><?php echo addslashes(esc_html__('Top', 'ARForms')); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>


                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Validation Message Style', 'ARForms')); ?></div>
                                            <div class="arf_accordion_container_row arf_half_width">
                                                <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Type', 'ARForms')); ?></div>
                                                <div class="arf_accordion_content_container arf_align_right arf_right">                                                    
                                                    <div class="arf_toggle_button_group arf_two_button_group">
                                                        <?php $newarr['arferrorstyle'] = isset($newarr['arferrorstyle']) ? $newarr['arferrorstyle'] : 'normal'; ?>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyle'] == 'normal') ? 'arf_success' : ''; ?>"><input type="radio" name="arfest" class="visuallyhidden" id="arfest1" value="normal" <?Php checked($newarr['arferrorstyle'], 'normal'); ?> /><?php echo addslashes(esc_html__('Standard', 'ARForms')); ?></label>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyle'] == 'advance') ? 'arf_success' : ''; ?>"><input type="radio" name="arfest" class="visuallyhidden" id="arfest2" value="advance" <?Php checked($newarr['arferrorstyle'], 'advance'); ?> /><?php echo addslashes(esc_html__('Modern', 'ARForms')); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="arf_accordion_container_row arf_half_width" id="arf_validation_message_style_position" style="<?php echo ($newarr['arferrorstyle'] == 'normal') ? 'display: none;' : '';?>">
                                                <div class="arf_accordion_inner_title" ><?php echo addslashes(esc_html__('Position', 'ARForms')); ?></div>
                                                <div class="arf_accordion_content_container">                                                    
                                                    <div class="arf_toggle_button_group arf_four_button_group">
                                                        <?php $newarr['arferrorstyleposition'] = isset($newarr['arferrorstyleposition']) ? $newarr['arferrorstyleposition'] : 'right'; ?>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyleposition'] == 'right') ? 'arf_success' : ''; ?>"><input type="radio" name="arfestbc" class="visuallyhidden" data-id="arfestbc2" value="right" <?Php checked($newarr['arferrorstyleposition'], 'right'); ?> /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyleposition'] == 'left') ? 'arf_success' : ''; ?>"><input type="radio" name="arfestbc" class="visuallyhidden" data-id="arfestbc2" value="left" <?Php checked($newarr['arferrorstyleposition'], 'left'); ?> /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyleposition'] == 'bottom') ? 'arf_success' : ''; ?>"><input type="radio" name="arfestbc" class="visuallyhidden" data-id="arfestbc2" value="bottom" <?Php checked($newarr['arferrorstyleposition'], 'bottom'); ?> /><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></label>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyleposition'] == 'top' ) ? 'arf_success' : ''; ?>"><input type='radio' name='arfestbc' class='visuallyhidden' id='arfestbc1' value='top' <?php checked($newarr['arferrorstyleposition'], 'top'); ?> /><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="arf_accordion_container_row arf_half_width" id="arf_standard_validation_message_style_position" style="<?php echo ($newarr['arferrorstyle'] == 'advance') ? 'display: none;' : '';?>">
                                                <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Position', 'ARForms')); ?></div>
                                                <div class="arf_accordion_content_container">
                                                    <div class="arf_toggle_button_group arf_four_button_group">

                                                        <?php $newarr['arfstandarderrposition'] = isset($newarr['arfstandarderrposition']) ? $newarr['arfstandarderrposition'] : 'relative'; ?>

                                                        <label class="arf_toggle_btn <?php echo ($newarr['arfstandarderrposition'] == 'absolute') ? 'arf_success' : ''; ?>">
                                                            <input type="radio" name="arfstndrerr" class="visuallyhidden" data-id="arfstndrerr2" value="absolute" <?Php checked($newarr['arfstandarderrposition'], 'absolute'); ?> /><?php echo addslashes(esc_html__('Absolute', 'ARForms')); ?>
                                                        </label>

                                                        <label class="arf_toggle_btn <?php echo ($newarr['arfstandarderrposition'] == 'relative') ? 'arf_success' : ''; ?>">
                                                            <input type="radio" name="arfstndrerr" class="visuallyhidden" data-id="arfstndrerr2" value="relative" <?Php checked($newarr['arfstandarderrposition'], 'relative'); ?> /><?php echo addslashes(esc_html__('Relative', 'ARForms')); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </dd>
                            </dl>

                            <dl class="arf_accordion_tab_form_settings">
                                <dd>
                                    <a href="javascript:void(0)" data-target="arf_accordion_tab_form_settings"><?php echo addslashes(esc_html__('Advanced Form Options', 'ARForms')); ?></a>
                                    <div class="arf_accordion_container">
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Form Title options', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo addslashes(esc_html__('Display Title and Description', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50">
                                                <div class="arf_float_right arfmarginright4">
                                                    <label class="arf_js_switch_label">
                                                        <span><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                                                    </label>
                                                    <span class="arf_js_switch_wrapper">
                                                        <input type="checkbox" class="js-switch" name="options[display_title_form]" id="display_title_form" <?php echo (isset($values_nw['display_title_form']) && $values_nw['display_title_form'] == '1') ? 'checked="checked"' : ''; ?> onchange="change_form_title();" value="<?php echo isset($values_nw['display_title_form']) ? $values_nw['display_title_form'] : ''; ?>" />
                                                        <span class="arf_js_switch"></span>
                                                    </span>
                                                    <label class="arf_js_switch_label">
                                                        <span>&nbsp;<?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <input type="hidden" id="temp_display_title_form" value="1" />
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Title Alignment', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_right">                                               
                                                <div class="arf_toggle_button_group arf_three_button_group">
                                                    <?php

                                                    $newarr['arfformtitlealign'] = isset($newarr['arfformtitlealign']) ? $newarr['arfformtitlealign'] : 'center';
                                                    ?>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfformtitlealign'] == 'right') ? 'arf_success' : ''; ?>"><input  class="visuallyhidden" type="radio" name="arffta" value="right" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align","material":".ar_main_div_{arf_form_id}  .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align","material_outlined":".ar_main_div_{arf_form_id}  .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_text_align" <?php checked($newarr['arfformtitlealign'], 'right') ?> /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfformtitlealign'] == 'center') ? 'arf_success' : ''; ?>"><input  class="visuallyhidden" type="radio" name="arffta" value="center" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_text_align" <?php checked($newarr['arfformtitlealign'], 'center') ?> /><?php echo addslashes(esc_html__('Center', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfformtitlealign'] == 'left') ? 'arf_success' : ''; ?>"><input  class="visuallyhidden" type="radio" name="arffta" value="left" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_text_align" <?php checked($newarr['arfformtitlealign'], 'left') ?> /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_form_padding"><?php echo addslashes(esc_html__('Margin', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center arf_form_container arf_right arfformmarginvals">
                                                <span class="arfpxspan arfformarginvalpx">px</span>
                                                <div class="arf_form_margin_box_wrapper"><input type="text" name="arfformtitlepaddingsetting_1" id="arfformtitlepaddingsetting_1" value="<?php echo esc_attr($newarr['arfmainformtitlepaddingsetting_1']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-top","material":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-top","material_outlined":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-top"}' class="arf_form_margin_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_margin" /><br /><span class="arf_px arf_font_size arfformmarginleft"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span></div>
                                                <div class="arf_form_margin_box_wrapper"><input type="text" name="arfformtitlepaddingsetting_2" id="arfformtitlepaddingsetting_2" value="<?php echo esc_attr($newarr['arfmainformtitlepaddingsetting_2']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-right","material":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-right","material_outlined":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-right"}' class="arf_form_margin_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_margin" /><br /><span class="arf_px arf_font_size arfformmarginleft"><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></span></div>
                                                <div class="arf_form_margin_box_wrapper"><input type="text" name="arfformtitlepaddingsetting_3" id="arfformtitlepaddingsetting_3" value="<?php echo esc_attr($newarr['arfmainformtitlepaddingsetting_3']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-bottom","material":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-bottom","material_outlined":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-bottom"}' class="arf_form_margin_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_margin" /><br /><span class="arf_px arf_font_size arfformmarginleft"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></span></div>
                                                <div class="arf_form_margin_box_wrapper"><input type="text" name="arfformtitlepaddingsetting_4" id="arfformtitlepaddingsetting_4" value="<?php echo esc_attr($newarr['arfmainformtitlepaddingsetting_4']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-left","material":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-left","material_outlined":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-left"}' class="arf_form_margin_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_margin" /><br /><span class="arf_px arf_font_size arfformmarginleft"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></span></div>
                                            </div>
                                            <?php
                                            $arfformtitlepaddingsetting_value = '';

                                            if (esc_attr($newarr['arfmainformtitlepaddingsetting_1']) != '') {
                                                $arfformtitlepaddingsetting_value .= $newarr['arfmainformtitlepaddingsetting_1'] . 'px ';
                                            } else {
                                                $arfformtitlepaddingsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfmainformtitlepaddingsetting_2']) != '') {
                                                $arfformtitlepaddingsetting_value .= $newarr['arfmainformtitlepaddingsetting_2'] . 'px ';
                                            } else {
                                                $arfformtitlepaddingsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfmainformtitlepaddingsetting_3']) != '') {
                                                $arfformtitlepaddingsetting_value .= $newarr['arfmainformtitlepaddingsetting_3'] . 'px ';
                                            } else {
                                                $arfformtitlepaddingsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfmainformtitlepaddingsetting_4']) != '') {
                                                $arfformtitlepaddingsetting_value .= $newarr['arfmainformtitlepaddingsetting_4'] . 'px';
                                            } else {
                                                $arfformtitlepaddingsetting_value .= '0px';
                                            }
                                            ?>
                                            <input type="hidden" name="arfftps" style="width:100px;" id="arfformtitlepaddingsetting" class="txtxbox_widget" value="<?php echo $arfformtitlepaddingsetting_value; ?>" />
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Form Settings', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Form Alignment', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arfhieght35">
                                                <div class="arf_toggle_button_group arf_three_button_group" style="margin-right:5px;">
                                                    <?php $newarr['form_align'] = isset($newarr['form_align']) ? $newarr['form_align'] : 'center'; 
                                                        
                                                    ?>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['form_align'] == 'right') ? 'arf_success' : ''; ?>"><input type="radio" name="arffa" class="visuallyhidden" data-id="arfestbc2" value="right" <?Php checked($newarr['form_align'], 'right'); ?> data-arfstyle="true" data-arfstyledata='{"standard":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align","material":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align","material_outlined":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_align"
                                                    /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['form_align'] == 'center') ? 'arf_success' : ''; ?>"><input type="radio" name="arffa" class="visuallyhidden" data-id="arfestbc2" value="center" <?Php checked($newarr['form_align'], 'center'); ?> data-arfstyle="true" data-arfstyledata='{"standard":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align","material":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align","material_outlined":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_align"/><?php echo addslashes(esc_html__('Center', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['form_align'] == 'left') ? 'arf_success' : ''; ?>"><input type="radio" name="arffa" class="visuallyhidden" data-id="arfestbc2" value="left" <?Php checked($newarr['form_align'], 'left'); ?> data-arfstyle="true" data-arfstyledata='{"standard":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align","material":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align","material_outlined":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_align"/><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="height: auto;">
                                            <div class="arf_accordion_inner_title arf_two_row_text "><?php echo addslashes(esc_html__('Background Image', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right">
                                                <div class="arf_imageloader arf_form_style_file_upload_loader" id="ajax_form_loader"></div>
                                                <div id="form_bg_img_div" <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') { ?> class="iframe_original_btn" data-id="arfmfbi" style="margin-right:5px; position: relative; overflow: hidden; cursor:pointer; max-width:140px; height:27px; background: #1BBAE1; font-weight:bold; <?php if ($newarr['arfmainform_bg_img'] == '') { ?> background:#1BBAE1;padding: 7px 10px 0 10px;font-size:13px;border-radius:3px;-webkit-border-radius:3px;-o-border-radius:3px;-moz-border-radius:3px;color:#FFFFFF;border:1px solid #CCCCCC;display: inline-block; <?php } ?>" <?php } else { ?> style="margin-left:0px;" <?php } ?>  >
                                                    <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' && $newarr['arfmainform_bg_img'] == '') { ?><span class="arf_form_style_file_upload_icon">
                                                        <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg></span><?php } ?>
                                                    <input type="hidden" name="arfmfbi" onclick="clear_file_submit();" value="<?php echo esc_attr($newarr['arfmainform_bg_img']) ?>" data-id="arfmainform_bg_img" />
                                                    <?php
                                                    if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') {
                                                        if ($newarr['arfmainform_bg_img'] != '') {
                                                            ?>
                                                            <img src="<?php echo $newarr['arfmainform_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('form_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                            </span>
                                                            
                                                        <?php } else { ?>

                                                            <input type="text" class="original" name="form_bg_img" id="field_arfmfbi" data-form-id="" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; width: 160px; height: 59px; left: -999px; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />

                                                            <input type="hidden" id="type_arfmfbi" name="type_arfmfbi" value="1" >
                                                            <input type="hidden" value="jpg, jpeg, jpe, gif, png, bmp, tif, tiff, ico" id="file_types_arfmfbi" name="field_types_arfmfbi" />
                                                            <input type="hidden" name="imagename_form" id="imagename_form" value="" />
                                                            <input type="hidden" name="arfmfbi" onclick="clear_file_submit();" value="" data-id="arfmainform_bg_img" />

                                                            <?php
                                                        }
                                                        echo '<div id="arfmfbi_iframe_div"><iframe style="display:none;" id="arfmfbi_iframe" src="' . ARFURL . '/core/views/iframe.php" ></iframe></div>';
                                                    } else {
                                                        ?>
                                                        <?php if ($newarr['arfmainform_bg_img'] != '') { ?>
                                                            <img src="<?php echo $newarr['arfmainform_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('form_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                        <?php } else { ?>

                                                            <div class="arfajaxfileupload" style="position: relative; overflow: hidden; cursor: pointer;">
                                                                <div class="arf_form_style_file_upload_icon">
                                                                    <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg>
                                                                </div>
                                                                <input type="file" name="form_bg_img" id="form_bg_img" data-val="form_bg" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                            </div>


                                                            <input type="hidden" name="imagename_form" id="imagename_form" value="" />
                                                            <input type="hidden" name="arfmfbi" onclick="clear_file_submit();" value="" data-id="arfmainform_bg_img" />

                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                            $arf_bg_position_style_x="";
                                            $arf_bg_position_height_style_x="";
                                            $arf_bg_position_style_y="";
                                            $arf_bg_position_height_style_y="";

                                            if((isset($newarr['arf_bg_position_x']) && $newarr['arf_bg_position_x']=='px') && (isset($newarr['arf_bg_position_input_x']) && $newarr['arf_bg_position_input_x']!='')){
                                                $arf_bg_position_style_x = "display: block;";
                                                $arf_bg_position_height_style_x = "arf_bg_position_active_height";
                                            } else{
                                                $arf_bg_position_style_x = "display: none;";
                                                $arf_bg_position_height_style_x = "arf_bg_position_inactive_height";
                                            }

                                            if((isset($newarr['arf_bg_position_y']) && $newarr['arf_bg_position_y']=='px') && (isset($newarr['arf_bg_position_input_y']) && $newarr['arf_bg_position_input_y']!='')){
                                                $arf_bg_position_style_y = "display: block;";
                                                $arf_bg_position_height_style_y = "arf_bg_position_active_height";
                                            } else{
                                                $arf_bg_position_style_y = "display: none;";
                                                $arf_bg_position_height_style_y = "arf_bg_position_inactive_height";
                                            }
                                        ?>

                                        <div class="arf_accordion_container_row arf_half_width <?php echo $arf_bg_position_height_style_x; ?>" style="margin-bottom: 30px;">
                                            <div style="width: 31% !important;" class="arf_accordion_inner_title arf_form_padding arf_two_row_text"><?php echo addslashes(esc_html__('Background Image Position-X', 'ARForms')); ?></div>
                                            <div class="arf_form_bg_position_container">
                                                <?php
                                                    $bg_position_selected_x=""; 
                                                    if(isset($newarr['arf_bg_position_x']) && $newarr['arf_bg_position_x']!=''){
                                                            $bg_position_selected_x = $newarr['arf_bg_position_x'];
                                                    } else{ 
                                                        $bg_position_selected_x = "left";
                                                    }
                                                ?>
                                                <div class="arf_dropdown_wrapper" style="width: 100%;">
                                                    <?php

                                                        $bg_position_opt = array(
                                                            'center' => 'center',
                                                            'left' => 'left',
                                                            'right' => 'right',
                                                            'px' => 'px'
                                                        );

                                                        $bg_position_attr = array(
                                                            'onchange' => 'update_form_bg_position(this,"x", "arf_form_bg_position_input_div_x", "arf_fieldset_' . $id . '")'
                                                        );

                                                        echo $maincontroller->arf_selectpicker_dom( 'arf_bg_position_x', 'arf_bg_position_x', '', 'width:70%;margin-left: 15px;', $bg_position_selected_x, $bg_position_attr, $bg_position_opt );
                                                    ?>
                                                    <span class="arf_px arf_font_size arfpxspan" style="margin-right:0;position: relative;"><?php echo addslashes(esc_html__('X-axis', 'ARForms')); ?></span>
                                                </div>  
                                                  
                                            </div>

                                            <div class="arf_form_bg_position_input_container">
                                                <div class="arf_form_bg_position_input_div" id="arf_form_bg_position_input_div_x" style="margin-top:10px;margin-left:18px; margin-right: -6px;<?php echo $arf_bg_position_style_x; ?>">
                                                    <span class="arf_px arf_font_size arfpxspan" style="margin-right:0;"><?php echo addslashes(esc_html__('X-axis', 'ARForms')); ?></span>
                                                    <input type="text" name="arf_bg_position_input_x" id="arf_form_bg_position_input_x" value="<?php echo (isset($newarr['arf_bg_position_input_x']) && $newarr['arf_bg_position_input_x']!='') ? esc_attr($newarr['arf_bg_position_input_x']) : '' ; ?>" class="arf_form_bg_position_input" onfocusout="set_form_bg_position(this, 'x', 'arf_fieldset_<?php echo $id; ?>')">
                                                </div>
                                            </div>
                                        </div>


                                        <div class="arf_accordion_container_row arf_half_width <?php echo $arf_bg_position_height_style_y; ?>" style="margin-bottom: 30px;">
                                            <div style="width: 31% !important;" class="arf_accordion_inner_title arf_form_padding arf_two_row_text"><?php echo addslashes(esc_html__('Background Image Position-Y', 'ARForms')); ?></div>

                                            <div class="arf_form_bg_position_container">
                                                <div class="arf_dropdown_wrapper" style="width: 100%;">
                                                    <?php
                                                        $bg_position_selected_y=""; 
                                                        if(isset($newarr['arf_bg_position_y']) && $newarr['arf_bg_position_y']!=''){
                                                                $bg_position_selected_y = $newarr['arf_bg_position_y'];
                                                        } else{ 
                                                            $bg_position_selected_y = "top";
                                                        }

                                                        $bg_position_opt = array(
                                                            'center' => 'center',
                                                            'top' => 'top',
                                                            'bottom' => 'bottom',
                                                            'px' => 'px'
                                                        );

                                                        $bg_position_attr = array(
                                                            'onchange' => 'update_form_bg_position(this,"y", "arf_form_bg_position_input_div_y", "arf_fieldset_' . $id . '");'
                                                        );

                                                        echo $maincontroller->arf_selectpicker_dom( 'arf_bg_position_y', 'arf_bg_position_y', '', 'width:70%;margin-left:15px;', $bg_position_selected_y, $bg_position_attr, $bg_position_opt );
                                                    ?>
                                                    <span class="arf_px arf_font_size arfpxspan" style="margin-right:0;position: relative;"><?php echo addslashes(esc_html__('Y-axis', 'ARForms')); ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="arf_form_bg_position_input_container">
                                                <div class="arf_form_bg_position_input_div" id="arf_form_bg_position_input_div_y" style="margin-top:10px;margin-left: 18px;margin-right: -6px;<?php echo $arf_bg_position_style_y; ?>">
                                                    <span class="arf_px arf_font_size arfpxspan" style="margin-right:0;"><?php echo addslashes(esc_html__('Y-axis', 'ARForms')); ?></span>
                                                    <input type="text" name="arf_bg_position_input_y" id="arf_form_bg_position_input_y" value="<?php echo (isset($newarr['arf_bg_position_input_y'])&&$newarr['arf_bg_position_input_y']!='') ? esc_attr($newarr['arf_bg_position_input_y']) : '' ; ?>" class="arf_form_bg_position_input" onfocusout="set_form_bg_position(this, 'y', 'arf_fieldset_<?php echo $id; ?>')">
                                                </div>
                                            </div>
                                        </div>


                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_form_padding arf_two_row_text"><?php echo addslashes(esc_html__('Form Padding', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center arf_form_container">
                                                <div class="arf_form_padding_box_wrapper"><input type="text" name="arfmainfieldsetpadding_1" id="arfmainfieldsetpadding_1" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-top","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-top","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-top"}' value="<?php echo esc_attr($newarr['arfmainfieldsetpadding_1']); ?>" class="arf_form_padding_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_padding" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span></div>
                                                <div class="arf_form_padding_box_wrapper"><input type="text" name="arfmainfieldsetpadding_2" id="arfmainfieldsetpadding_2" value="<?php echo esc_attr($newarr['arfmainfieldsetpadding_2']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-right","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-right","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-right"}' class="arf_form_padding_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_padding" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></span></div>
                                                <div class="arf_form_padding_box_wrapper"><input type="text" name="arfmainfieldsetpadding_3" id="arfmainfieldsetpadding_3" value="<?php echo esc_attr($newarr['arfmainfieldsetpadding_3']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-bottom","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-bottom","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-bottom"}' class="arf_form_padding_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_padding" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></span></div>
                                                <div class="arf_form_padding_box_wrapper"><input type="text" name="arfmainfieldsetpadding_4" id="arfmainfieldsetpadding_4" value="<?php echo esc_attr($newarr['arfmainfieldsetpadding_4']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-left||.ar_main_div_{arf_form_id} .arf_inner_wrapper_sortable.arfmainformfield.ui-sortable-helper~|~left","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-left||.ar_main_div_{arf_form_id} .arf_inner_wrapper_sortable.arfmainformfield.ui-sortable-helper~|~left","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-left||.ar_main_div_{arf_form_id} .arf_inner_wrapper_sortable.arfmainformfield.ui-sortable-helper~|~left"}' class="arf_form_padding_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_padding"data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_padding" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></span></div>
                                                <?php
                                                $arfmainfieldsetpadding_value = '';

                                                if (esc_attr($newarr['arfmainfieldsetpadding_1']) != '') {
                                                    $arfmainfieldsetpadding_value .= $newarr['arfmainfieldsetpadding_1'] . 'px ';
                                                } else {
                                                    $arfmainfieldsetpadding_value .= '0px ';
                                                }
                                                if (esc_attr($newarr['arfmainfieldsetpadding_2']) != '') {
                                                    $arfmainfieldsetpadding_value .= $newarr['arfmainfieldsetpadding_2'] . 'px ';
                                                } else {
                                                    $arfmainfieldsetpadding_value .= '0px ';
                                                }
                                                if (esc_attr($newarr['arfmainfieldsetpadding_3']) != '') {
                                                    $arfmainfieldsetpadding_value .= $newarr['arfmainfieldsetpadding_3'] . 'px ';
                                                } else {
                                                    $arfmainfieldsetpadding_value .= '0px ';
                                                }
                                                if (esc_attr($newarr['arfmainfieldsetpadding_4']) != '') {
                                                    $arfmainfieldsetpadding_value .= $newarr['arfmainfieldsetpadding_4'] . 'px';
                                                } else {
                                                    $arfmainfieldsetpadding_value .= '0px';
                                                }
                                                ?>
                                                <input type="hidden" name="arfmfsp" style="width:160px;" id="arfmainfieldsetpadding" class="txtxbox_widget arf_float_right" value="<?php echo $arfmainfieldsetpadding_value; ?>" size="4" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_form_padding arf_two_row_text"><?php echo addslashes(esc_html__('Section Padding', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center arf_form_container">
                                                <div class="arf_section_padding_box_wrapper"><input type="text" name="arfsectionpaddingsetting_1" id="arfsectionpaddingsetting_1" onchange="arf_change_field_padding('arfsectionpaddingsetting');" value="<?php echo esc_attr($newarr['arfsectionpaddingsetting_1']); ?>" class="arf_section_padding_box" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span></div>
                                                <div class="arf_section_padding_box_wrapper"><input type="text" name="arfsectionpaddingsetting_2" id="arfsectionpaddingsetting_2" value="<?php echo esc_attr($newarr['arfsectionpaddingsetting_2']); ?>" onchange="arf_change_field_padding('arfsectionpaddingsetting');" class="arf_section_padding_box"/><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></span></div>
                                                <div class="arf_section_padding_box_wrapper"><input type="text" name="arfsectionpaddingsetting_3" id="arfsectionpaddingsetting_3" value="<?php echo esc_attr($newarr['arfsectionpaddingsetting_3']); ?>" onchange="arf_change_field_padding('arfsectionpaddingsetting');" class="arf_section_padding_box"/><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></span></div>
                                                <div class="arf_section_padding_box_wrapper"><input type="text" name="arfsectionpaddingsetting_4" id="arfsectionpaddingsetting_4" value="<?php echo esc_attr($newarr['arfsectionpaddingsetting_4']); ?>" onchange="arf_change_field_padding('arfsectionpaddingsetting');" class="arf_section_padding_box" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></span></div>
                                                <?php
                                                $arfsectionpaddingsetting_value = '';

                                                if (esc_attr($newarr['arfsectionpaddingsetting_1']) != '')
                                                    $arfsectionpaddingsetting_value .= $newarr['arfsectionpaddingsetting_1'] . 'px ';
                                                else
                                                    $arfsectionpaddingsetting_value .= '20px ';

                                                if (esc_attr($newarr['arfsectionpaddingsetting_2']) != '')
                                                    $arfsectionpaddingsetting_value .= $newarr['arfsectionpaddingsetting_2'] . 'px ';
                                                else
                                                    $arfsectionpaddingsetting_value .= '0px ';

                                                if (esc_attr($newarr['arfsectionpaddingsetting_3']) != '')
                                                    $arfsectionpaddingsetting_value .= $newarr['arfsectionpaddingsetting_3'] . 'px ';
                                                else
                                                    $arfsectionpaddingsetting_value .= '20px ';

                                                if (esc_attr($newarr['arfsectionpaddingsetting_4']) != '')
                                                    $arfsectionpaddingsetting_value .= $newarr['arfsectionpaddingsetting_4'] . 'px';
                                                else
                                                    $arfsectionpaddingsetting_value .= '20px';
                                                ?>
                                                <input type="hidden" name="arfscps" style="width:100px;" id="arfsectionpaddingsetting" class="txtxbox_widget" value="<?php echo $arfsectionpaddingsetting_value; ?>" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Form Border', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Border Type', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_right">
                                                <div class="arf_toggle_button_group arf_two_button_group" style="margin-right:5px;">
                                                    <?php $newarr['form_border_shadow'] = isset($newarr['form_border_shadow']) ? $newarr['form_border_shadow'] : 'shadow'; ?>
                                                    <label class="arf_flat_border_btn arf_toggle_btn <?php echo ($newarr['form_border_shadow'] == 'flat') ? 'arf_success' : ''; ?>" style="padding:7px 20px;"><input type="radio" name="arffbs" class="visuallyhidden" value="flat" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow-none","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow-none","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow-none"}'  id="arfmainformbordershadow2" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_type" <?php checked($newarr['form_border_shadow'], 'flat'); ?> /><?php echo addslashes(esc_html__('Flat', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['form_border_shadow'] == 'shadow') ? 'arf_success' : ''; ?>"><input type="radio" name="arffbs" class="visuallyhidden" id="arfmainformbordershadow1" value="shadow" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow"}' <?php checked($newarr['form_border_shadow'], 'shadow'); ?> data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_type" /><?php echo addslashes(esc_html__('Shadow', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>                                        
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Border Size', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" name="arfmfis" style="width:142px;" class="txtxbox_widget"  id="arfmainfieldset" value="<?php echo esc_attr($newarr['fieldset']) ?>" size="4" />&nbsp;<span class="arf_px">px</span>
                                                    </div>
                                                <?php } else { ?>
                                                        <div class="arf_slider_wrapper">
                                                           <div id="arf_arfmainfieldset" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div> 
                                                            <input id="arfmainfieldset_exs" class="arf_slider_input" type="text" data-slider-id='arfmainfieldset_exsSlider' type="text" data-slider-value="<?php echo esc_attr($newarr['fieldset']) ?>" />
                                                                <div class="arf_slider_unit_data">
                                                                    <div style="float:left;">0 px</div>
                                                                    <div style="float:right;">50 px</div>
                                                                </div>
                                                                <input type="hidden" name="arfmfis" style="width:100px;" class="txtxbox_widget"  id="arfmainfieldset" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-width","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-width","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-width"}' value="<?php echo esc_attr($newarr['fieldset']) ?>" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_width" size="4" />
                                                        </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Border Radius', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" name="arfmfsr" style="width:142px;" class="txtxbox_widget"  id="arfmainfieldsetradius" value="<?php echo esc_attr($newarr['arfmainfieldsetradius']) ?>" size="4" />&nbsp;<span class="arf_px">px</span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                          <div id="arf_arfmainfieldsetradius" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div> 
                                                        <input id="arfmainfieldsetradius_exs" class="arf_slider_input" data-slider-id='arfmainfieldsetradius_exsSlider' type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="<?php echo esc_attr($newarr['arfmainfieldsetradius']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div style="float:left;">0 px</div>
                                                            <div style="float:right;">100 px</div>
                                                        </div>

                                                        <input type="hidden" name="arfmfsr" style="width:100px;" class="txtxbox_widget"  id="arfmainfieldsetradius" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-radius","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-radius","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-radius"}' value="<?php echo esc_attr($newarr['arfmainfieldsetradius']) ?>" size="4" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_radius" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        
                                        

                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Window Opacity', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Window Opacity', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right" style="margin-right:5px;">
                                                        <input type="text" name="arfmainform_opacity" id="arfmainform_opacity" class="txtxbox_widget" value="<?php echo esc_attr($newarr['arfmainform_opacity']) ?>" style="width:142px;" />
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <div id="arf_mainform_opacity_exs" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div> 
                                                        <input id="arfmainform_opacity_exs" class="arf_slider_input" data-slider-id='arfmainform_opacity_exsSlider' type="text" data-slider-min="0" data-slider-max="1" data-slider-step="0.1" data-slider-value="<?php echo ( esc_attr($newarr['arfmainform_opacity']) * 10 ) ?>"  />
                                                        <div class="arf_slider_unit_data">
                                                            <div style="float:left;"><?php echo addslashes(esc_html__('0', 'ARForms')); ?></div>
                                                            <div style="float:right;"><?php echo addslashes(esc_html__('1', 'ARForms')); ?></div>
                                                        </div>
                                                        <input type="hidden" name="arfmainform_opacity" id="arfmainform_opacity" class="txtxbox_widget" value="<?php echo esc_attr($newarr['arfmainform_opacity']) ?>" style="width:100px;" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>

                                    </div>
                                </dd>
                            </dl>
                            <dl class="arf_accordion_tab_input_settings">
                                <dd>
                                    <a href="javascript:void(0)" data-target="arf_accordion_tab_input_settings"><?php echo esc_html__('Input field Options', 'ARForms'); ?></a>
                                    <div class="arf_accordion_container">
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo esc_html__('Label Options', 'ARForms'); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Label Position', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arfhieght35 arf_right">
                                                <?php
                                                    $newarr['position'] = isset($newarr['position']) ? $newarr['position'] : 'top';
                                                    $disable_label_position = '';
                                                    $checked_right = checked($newarr['position'],'right',false);
                                                    $checked_left = checked($newarr['position'],'left',false);
                                                    $checked_top = checked($newarr['position'],'top',false);
                                                    $disabled_right = $disabled_left = "";
                                                    if( $newarr['arfinputstyle'] == 'material' || 'material_outlined' == $newarr['arfinputstyle'] ){
                                                        $disable_label_position = 'disabled="disabled"';
                                                        $disabled_right = $disabled_left = "arf_disabled_toggle_button";
                                                    } else {
                                                        $disable_label_position = '';
                                                        $disabled_right = $disabled_left = "";
                                                    }
                                                ?>
                                                <div class="arf_toggle_button_group arf_three_button_group" style="margin-right:5px;">
                                                    <label class="arf_toggle_btn arf_label_position arf_right_position <?php echo ($checked_right != '') ? 'arf_success' : ''; echo $disabled_right; ?>" style="padding: 7px 10px;"><input type="radio" name="arfmps" class="visuallyhidden" onchange="frmSetPosClass('right');" <?php echo $disable_label_position; ?> value="right" <?php echo $checked_right; ?> /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn arf_label_position arf_left_position <?php echo ($checked_left != '') ? 'arf_success' : ''; echo $disabled_left; ?>" style="padding: 7px 10px;"><input type="radio" name="arfmps" class="visuallyhidden" onchange="frmSetPosClass('left');" <?php echo $disable_label_position; ?> value="left" <?php echo $checked_left; ?> /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn arf_label_position  arf_top_position <?php echo ($checked_top != '') ? 'arf_success' : ''; ?>" style="padding: 7px 10px;"><input type="radio" name="arfmps" class="visuallyhidden" onchange="frmSetPosClass('top');" value="top" <?php echo $checked_top; ?> /><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Label Align', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arf_right">
                                                <div class="arf_toggle_button_group arf_two_button_group" style="margin-right:5px;">
                                                    <?php
                                                        $newarr['align'] = isset($newarr['align']) ? $newarr['align'] : 'right'; 
                                                        $disable_label_alignment = '';
                                                        $disable_label_alignment_cls = '';
                                                        if( 'material_outlined' == $newarr['arfinputstyle'] ){
                                                            $disable_label_alignment = ' disabled="disbaled" ';
                                                            $disable_label_alignment_cls = 'arf_disabled_toggle_button';
                                                        }
                                                    ?>
                                                    <label class="arf_toggle_btn arf_label_align arf_right_alignment <?php echo $disable_label_alignment_cls; ?> <?php echo ($newarr['align'] == 'right') ? 'arf_success' : ''; ?>" style="padding: 7px 12px;"><input type="radio" name="arffrma" id="frm_align" class="visuallyhidden" value="right" <?php echo $disable_label_alignment; ?> <?php checked($newarr['align'], 'right'); ?> data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} label.arf_main_label~|~text-align||.ar_main_div_{arf_form_id} .sortable_inner_wrapper .arfformfield .fieldname~|~text-align","material":".ar_main_div_{arf_form_id} .arf_materialize_form  label.arf_main_label~|~text-align||.ar_main_div_{arf_form_id} .sortable_inner_wrapper .arfformfield .fieldname~|~text-align||.arf_materialize_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_right_position||.arf_materialize_form .input-field .arf_material_theme_container_with_icons label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_right_position||.arf_materialize_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_right_position_inherit||.arf_materialize_form .input-field .arf_material_theme_container_with_icons label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_right_position_inherit","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form  label.arf_main_label~|~text-align||.ar_main_div_{arf_form_id} .sortable_inner_wrapper .arfformfield .fieldname~|~text-align||.arf_material_outline_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_right_position||.arf_material_outline_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_right_position_inherit"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_text_align"  /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn arf_label_align arf_left_alignment <?php echo ($newarr['align'] == 'left') ? 'arf_success' : ''; ?>" style="padding: 7px 16px;"><input type="radio" name="arffrma" id="frm_align_2" class="visuallyhidden" value="left" <?php checked($newarr['align'], 'left'); ?> data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} label.arf_main_label~|~text-align||.ar_main_div_{arf_form_id} .sortable_inner_wrapper .arfformfield .fieldname~|~text-align","material":".ar_main_div_{arf_form_id} .arf_materialize_form label.arf_main_label~|~text-align||.ar_main_div_{arf_form_id} .sortable_inner_wrapper .arfformfield .fieldname~|~text-align||.arf_materialize_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_left_position||.arf_materialize_form .input-field .arf_material_theme_container_with_icons label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_left_position||.arf_materialize_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_left_position_inherit||.arf_materialize_form .input-field ..arf_material_theme_container_with_icons label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_left_position_inherit","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form label.arf_main_label~|~text-align||.ar_main_div_{arf_form_id} .sortable_inner_wrapper .arfformfield .fieldname~|~text-align||.arf_material_outline_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_left_position||.arf_material_outline_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_left_position_inherit"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_text_align" /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo esc_html__('Label Width', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arf_right">
                                                <span class="arfpxspan arffieldwidthpx">px</span>
                                                <input type="text" name="arfmws" class="arf_small_width_txtbox arfcolor arffieldwidthinput" id="arfmainformwidthsetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~width","material":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~width","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~width"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_width" value="<?php echo esc_attr($newarr["width"]) ?>" size="5" />
                                                <input type="hidden" name="arfmwu" id="arfmainwidthunit" value="px"  <?php echo($newarr['position'] == 'top')?'disabled="disabled"':'';?>/>
                                            </div>
                                        </div>
                                        <?php
                                            $disable_hide_label_options = '';
                                            if( 'material_outlined' == $newarr['arfinputstyle'] ){
                                                $disable_hide_label_options = 'arf_disable_switch';
                                            }
                                        ?>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo esc_html__('Hide Label', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right">
                                                <div class="arf_float_right" style="margin-right:5px;">
                                                    <label class="arf_js_switch_label">
                                                        <span class=""><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                                                    </label>
                                                    <span class="arf_js_switch_wrapper <?php echo $disable_hide_label_options; ?>">
                                                        <input type="checkbox" class="js-switch" name="arfhl" id="arfhidelabels" value="<?php echo $newarr['hide_labels'] != "" ? $newarr['hide_labels'] : 0; ?>" onchange="frmSetPosClassHide()"  <?php echo ($newarr['hide_labels'] == '1') ? 'checked="checked"' : ""; ?> />
                                                        <span class="arf_js_switch"></span>
                                                    </span>
                                                    <label class="arf_js_switch_label">
                                                        <span class="">&nbsp;<?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Input Field Description Options', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Font Size', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;width: 60px;">
                                                    <?php
                                                        $font_size_opts = array();
                                                        for( $i = 8; $i <= 20; $i++ ){
                                                            $font_size_opts[$i] = $i;
                                                        }
                                                        for( $i = 22; $i <= 28; $i = $i + 2 ){
                                                            $font_size_opts[$i] = $i;
                                                        }
                                                        for( $i = 32; $i <= 40; $i = $i + 4 ){
                                                            $font_size_opts[$i] = $i;
                                                        }

                                                        $font_size_attr = array(
                                                            'data-arfstyle' => 'true',
                                                            'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~font-size||.ar_main_div_{arf_form_id} .arftitlediv .arfeditorformdescription input~|~font-size","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~font-size||.ar_main_div_{arf_form_id} .arftitlediv .arfeditorformdescription input~|~font-size","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~font-size||.ar_main_div_{arf_form_id} .arftitlediv .arfeditorformdescription input~|~font-size"}',
                                                            'data-arfstyleappend' => 'true',
                                                            'data-arfstyleappendid' => 'arf_{arf_form_id}_field_description_font_size'
                                                        );

                                                        echo $maincontroller->arf_selectpicker_dom( 'arfdfss', 'arfdescfontsizesetting', '', '', $newarr['arfdescfontsizesetting'], $font_size_attr, $font_size_opts );
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Text Alignment', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <div class="toggle-btn-grp joint-toggle arffieldtextalignment">
                                                    
                                                    <label onclick="" class="toggle-btn arf_three_button right <?php
                                                    if ($newarr['arfdescalighsetting'] == "right") {
                                                        echo "success";
                                                    }
                                                    ?>" style="float:right;margin: 5px 0px  !important;"><input type="radio" name="arfdas" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_description_align" class="visuallyhidden" value="right" <?php checked($newarr['arfdescalighsetting'], 'right'); ?> /><svg width="24px" height="29px" viewBox="3 0 23 27"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#BCC9E0" d="M12.089,24.783v-3h14.125v3H12.089z M12.089,7.783h14.063v3H12.089  V7.783z M1.089,0.784h24.938v2.999H1.089V0.784z M26.027,17.783H1.089v-2.999h24.938V17.783z"/></svg></label>
                                                    <label onclick="" class="toggle-btn arf_three_button center <?php
                                                    if ($newarr['arfdescalighsetting'] == "center") {
                                                        echo "success";
                                                    }
                                                    ?>" style="float:right;"><input type="radio" name="arfdas"  class="visuallyhidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_description_align" value="center" <?php checked($newarr['arfdescalighsetting'], 'center'); ?> /><svg width="24px" height="29px" viewBox="3 0 23 27"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#BCC9E0" d="M1.089,17.783v-2.999h24.938v2.999H1.089z M6.089,10.783v-3h14.063  v3H6.089z M1.089,0.784h24.938v2.999H1.089V0.784z M20.214,24.783H6.089v-3h14.125V24.783z"/></svg></label>
                                                    <label onclick="" class="toggle-btn arf_three_button left <?php
                                                    if ($newarr['arfdescalighsetting'] == "left") {
                                                        echo "success";
                                                    }
                                                    ?>" style="float:right;"><input type="radio" name="arfdas" class="visuallyhidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_description_align" value="left" <?php checked($newarr['arfdescalighsetting'], 'left'); ?> /><svg width="24px" height="29px" viewBox="3 0 23 27"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#BCC9E0" d="M1.089,17.783v-2.999h24.938v2.999H1.089z M1.089,0.784h24.938  v2.999H1.089V0.784z M15.152,10.783H1.089v-3h14.063V10.783z M15.214,24.783H1.089v-3h14.125V24.783z"/></svg></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Input Field Options', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Field Width', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container" style="margin-left: -6px;">
                                                <div class="arf_dropdown_wrapper">
                                                    <?php
                                                        $field_width_unit_opts = array(
                                                            'px' => 'px',
                                                            '%' => '%'
                                                        );

                                                        $field_width_unit_attr = array(
                                                            'data-arfstyle' => 'true',
                                                            'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .controls~|~arf_field_width_unit","material":".ar_main_div_{arf_form_id} .controls~|~arf_field_width_unit","material_outlined":".ar_main_div_{arf_form_id} .controls~|~arf_field_width_unit"}',
                                                            'data-arfstyleappend' => 'true',
                                                            'data-arfstyleappendid' => 'arf_{arf_form_id}_field_width',
                                                        );

                                                        echo $maincontroller->arf_selectpicker_dom( 'arffiu', 'arffieldunit', '', 'width:50px', $newarr['field_width_unit'], $field_width_unit_attr, $field_width_unit_opts );
                                                    ?>
                                                </div>
                                                <input type="text" name="arfmfiws" id="arfmainfieldwidthsetting" class="arf_small_width_txtbox arfcolor" data-arfstyle="true" data-arfstyledata='{"standard": ".ar_main_div_{arf_form_id} .controls~|~width{arf_field_width_unit}","material": ".ar_main_div_{arf_form_id} .controls,.ar_main_div_{arf_form_id} .edit_field_type_checkbox .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_radio .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_arf_switch .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_scale .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_like .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_arf_smiley .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_colorpicker .fieldname-row~|~width{arf_field_width_unit}","material_outlined": ".ar_main_div_{arf_form_id} .controls,.ar_main_div_{arf_form_id} .edit_field_type_checkbox .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_radio .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_arf_switch .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_scale .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_like .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_arf_smiley .fieldname-row,.ar_main_div_{arf_form_id} .edit_field_type_colorpicker .fieldname-row~|~width{arf_field_width_unit}"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_width" value="<?php echo esc_attr($newarr['field_width']) ?>"  size="5" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="height: auto;">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Text Direction', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container" >
                                                <div class="toggle-btn-grp joint-toggle arf_right arffielddirrection" >
                                                    <label onclick="" class="toggle-btn arf_four_button left text_direction <?php
                                                    if ($newarr['text_direction'] == "1") {
                                                        echo "success";
                                                    }
                                                    ?>" style="font-size:10px !important;padding-top: 5px !important;height:33px;"><input type="radio" name="arftds" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~direction||.ar_main_div_{arf_form_id} input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~direction||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~text-align||.ar_main_div_{arf_form_id} .bootstrap-select.btn-group .arfbtn .filter-option~|~text-align||.ar_main_div_{arf_form_id} .autocomplete-content li span, .ar_main_div_{arf_form_id} .autocomplete-content li~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~direction||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~text-align||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~text-align","material":".ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-select-dropdown li span~|~text-align||.ar_main_div_{arf_form_id} .arf_materialize_form .autocomplete-content li span, .ar_main_div_{arf_form_id} .arf_materialize_form .autocomplete-content li~|~text-align||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=password]~|~direction||.ar_main_div_{arf_form_id}  .arf_materialize_form .arf_fieldset .controls input[type=email]~|~direction||.ar_main_div_{arf_form_id}  .arf_materialize_form .arf_fieldset .controls input[type=number]~|~direction||.ar_main_div_{arf_form_id}  .arf_materialize_form .arf_fieldset .controls input[type=url]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=tel]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)~|~direction||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~text-align||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~text-align","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf-select-dropdown li span~|~text-align||.ar_main_div_{arf_form_id} .arf_material_outline_form .autocomplete-content li span, .ar_main_div_{arf_form_id} .arf_material_outline_form .autocomplete-content li~|~text-align||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_fieldset .controls input[type=password]~|~direction||.ar_main_div_{arf_form_id}  .arf_material_outline_form .arf_fieldset .controls input[type=email]~|~direction||.ar_main_div_{arf_form_id}  .arf_material_outline_form .arf_fieldset .controls input[type=number]~|~direction||.ar_main_div_{arf_form_id}  .arf_material_outline_form .arf_fieldset .controls input[type=url]~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_fieldset .controls input[type=tel]~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description)~|~direction||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~text-align~|~direction||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~text-align"}' class="visuallyhidden" id="txt_dir_1" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_text_direction" value="1" <?php checked($newarr['text_direction'], 1); ?> /><svg width="25px" height="29px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#bcc9e0" d="M1.131,19.305h2V0.43h-2V19.305z M26.631,9.867l-7.5-5v3.5H5.06v3h14.071v3.5    L26.631,9.867z" /></svg></label><label onclick="" class="toggle-btn arf_four_button right text_direction <?php
                                                           if ($newarr['text_direction'] == "0") {
                                                               echo "success";
                                                           }
                                                           ?>" style="font-size:10px !important;padding-top: 5px !important;height:33px;"><input type="radio" name="arftds" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~direction||.ar_main_div_{arf_form_id} input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~direction||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~text-align||.ar_main_div_{arf_form_id} .bootstrap-select.btn-group .arfbtn .filter-option~|~text-align||.ar_main_div_{arf_form_id} .autocomplete-content li span, .ar_main_div_{arf_form_id} .autocomplete-content li~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~direction||~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~direction||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~text-align||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~text-align","material":".ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-select-dropdown li span~|~text-align||.ar_main_div_{arf_form_id} .arf_materialize_form .autocomplete-content li span, .ar_main_div_{arf_form_id} .arf_materialize_form .autocomplete-content li~|~text-align||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=password]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=email]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=number]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=url]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=tel]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)~|~direction||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~text-align||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~text-align","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf-select-dropdown li span~|~text-align||.ar_main_div_{arf_form_id} .arf_material_outline_form .autocomplete-content li span, .ar_main_div_{arf_form_id} .arf_material_outline_form .autocomplete-content li~|~text-align||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_fieldset .controls input[type=password]~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_fieldset .controls input[type=email]~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_fieldset .controls input[type=number]~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_fieldset .controls input[type=url]~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_fieldset .controls input[type=tel]~|~direction||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description)~|~direction||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~text-align||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_text_direction" class="visuallyhidden" value="0"  id="txt_dir_2" <?php checked($newarr['text_direction'], 0); ?> /><svg width="25px" height="29px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" fill="#bcc9e0" clip-rule="evenodd" d="M23.881,0.43v18.875h2V0.43H23.881z M8.819,4.867l-7.938,5l7.938,5v-3.5H21.89    v-3H8.819V4.867z"/></svg></label><br>
                                                    <span class="arf_px arf_font_size arfinputfielddirectionltr"><?php echo addslashes(esc_html__('LTR', 'ARForms')); ?></span>
                                                    <span class="arf_px arf_font_size arfinputfielddirectionrtl"><?php echo addslashes(esc_html__('RTL', 'ARForms')); ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Field Transparency', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">
                                                <div class="arf_float_right" style="margin-right:4px;">
                                                    <label class="arf_js_switch_label">
                                                        <span><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                                                    </label>
                                                    <span class="arf_js_switch_wrapper">
                                                        <input type="checkbox" class="js-switch chkstanard <?php echo ($newarr['arfinputstyle'] == 'material' || $newarr['arfinputstyle'] == 'material_outlined') ? 'arfcursornotallow' : ''; ?>" name="arfmfo" id="arfmainfield_opacity" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~field_transparency||.ar_main_div_{arf_form_id} .controls textarea~|~field_transparency||.ar_main_div_{arf_form_id} .controls select~|~field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu~|~field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=text]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls input[type=password]:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls input[type=email]:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls input[type=number]:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls input[type=url]:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls input[type=tel]:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .arfmainformfield .controls textarea:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls select:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle:focus~|~field_transparency_focus","material":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~field_transparency||.ar_main_div_{arf_form_id} .controls textarea~|~field_transparency||.ar_main_div_{arf_form_id} .controls select~|~field_transparency","material_outlined":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~field_transparency||.ar_main_div_{arf_form_id} .controls textarea~|~field_transparency||.ar_main_div_{arf_form_id} .controls select~|~field_transparency","material_outlined":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~field_transparency||.ar_main_div_{arf_form_id} .controls textarea~|~field_transparency||.ar_main_div_{arf_form_id} .controls select~|~field_transparency","material_outlined":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~field_transparency||.ar_main_div_{arf_form_id} .controls textarea~|~field_transparency||.ar_main_div_{arf_form_id} .controls select~|~field_transparency"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_transparency" value="1" <?php echo ($newarr['arfmainfield_opacity'] == 1) ? 'checked="checked"' : ""; ?> <?php echo ($newarr['arfinputstyle'] == 'material' || $newarr['arfinputstyle'] == 'material_outlined') ? 'disabled="disabled"' : ""; ?> />
                                                        <span class="arf_js_switch"></span>
                                                    </span>
                                                    <label class="arf_js_switch_label">
                                                        <span>&nbsp;<?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo esc_html__('Hide Required Indicator', 'ARForms'); ?></div>
                                            
                                                
                                                <div class="arf_accordion_content_container">
                                                    <div class="arf_float_right" style="margin-right:5px;">
                                                        <label class="arf_js_switch_label">
                                                            <span class=""><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                                                        </label>
                                                        <span class="arf_js_switch_wrapper">
                                                           <input type="checkbox" class="js-switch chkstanard" name="arfrinc" id="arfreq_inc" data-arfstyle="true" data-arfstyledata='{"standard":".arf_main_label span.arf_edit_in_place+span~|~req_indicator","material":".arf_main_label span.arf_edit_in_place+span~|~req_indicator","material_outlined":".arf_main_label span.arf_edit_in_place+span~|~req_indicator"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_arfreq_inc" value="1" <?php echo (isset($newarr['arf_req_indicator']) && $newarr['arf_req_indicator'] == 1) ? 'checked="checked"' : ""; ?> />
                                                            <span class="arf_js_switch"></span>
                                                        </span>
                                                        <label class="arf_js_switch_label">
                                                            <span class="">&nbsp;<?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                                                        </label>
                                                    </div>
                                                </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo addslashes(esc_html__('Space Between Two Fields', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <input type="text" name="arffms" id="arffieldmarginsetting" class="arf_small_width_txtbox arfcolor" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} #new_fields .arfmainformfield.edit_form_item~|~field-margin-bottom","material":".ar_main_div_{arf_form_id} #new_fields .arfmainformfield.edit_form_item~|~field-margin-bottom","material_outlined":".ar_main_div_{arf_form_id} #new_fields .arfmainformfield.edit_form_item~|~field-margin-bottom"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_space_between_fields" value="<?php echo esc_attr($newarr['arffieldmarginssetting']) ?>"  size="5" />
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text">
                                                <?php echo addslashes(esc_html__('Placeholder Opacity', 'ARForms')); ?>
                                            </div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                <div class="arf_float_right" style="margin-right:5px;">
                                                    <input type="text" name="arfplaceholder_opacity" id="arfplaceholder_opacity" class="txtxbox_widget" value="<?php echo isset($newarr['arfplaceholder_opacity']) ? esc_attr($newarr['arfplaceholder_opacity']) : 0.5?>" style="width:142px;" />
                                                </div>
                                                <?php } else { ?>
                                                <div class="arf_slider_wrapper">
                                                    <div id="arf_placeholder_opacity_exs" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div> 
                                                    <input id="arfplaceholder_opacity_exs" class="arf_slider_input" data-slider-id='arfplaceholder_opacity_exsSlider' type="text" data-slider-min="0" data-slider-max="1" data-slider-step="0.1" data-slider-value="<?php echo isset($newarr['arfplaceholder_opacity']) ? (esc_attr($newarr['arfplaceholder_opacity']) * 10 ) : (0.5 * 10) ?>"  />
                                                    <div class="arf_slider_unit_data">
                                                        <div style="float:left;"><?php echo addslashes(esc_html__('0', 'ARForms')); ?></div>
                                                        <div style="float:right;"><?php echo addslashes(esc_html__('1', 'ARForms')); ?></div>
                                                    </div>
                                                    <input type="hidden" name="arfplaceholder_opacity" id="arfplaceholder_opacity" class="txtxbox_widget" value="<?php echo isset($newarr['arfplaceholder_opacity']) ? esc_attr($newarr['arfplaceholder_opacity']) : 0.5 ?>" data-arfstyle="true" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_arfplaceholder_opacity" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-webkit-input-placeholder~|~opacity||.wp-admin .allfields .controls .smaple-textarea::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .controls textarea::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=password]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=number]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=url]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=tel]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} select::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field):-moz-placeholder~|~opacity||.wp-admin .allfields .controls .smaple-textarea:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .controls textarea:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=password]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=number]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=url]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=tel]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} select:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-moz-placeholder~|~opacity||.wp-admin .allfields .controls .smaple-textarea::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .controls textarea::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=password]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=number]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=url]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=tel]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} select::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field):-ms-input-placeholder~|~opacity||.wp-admin .allfields .controls .smaple-textarea:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .controls textarea:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=password]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=number]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=url]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=tel]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} select:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-ms-input-placeholder~|~opacity||.wp-admin .allfields .controls .smaple-textarea::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .controls textarea::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=password]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=number]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=url]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=tel]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} select::-ms-input-placeholder~|~opacity","material":".ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form select::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description):-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form select:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form select::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description):-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form select:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form select::-ms-input-placeholder~|~opacity","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description)::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=email]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=password]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=number]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=url]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=tel]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form select::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description):-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=email]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=password]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=number]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=url]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=tel]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form select:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description)::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=email]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=password]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=number]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=url]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=tel]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form select::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description):-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=email]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=password]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=number]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=url]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=tel]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form select:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description)::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=email]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=password]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=number]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=url]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=tel]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_material_outline_form select::-ms-input-placeholder~|~opacity"}' style="width:100px;" />
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width" style="height: 70px;">
                                            <div class="arf_accordion_container_inner_div">
                                                <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Font Settings','ARForms')); ?></div>
                                            </div>
                                            
                                            <div class="arf_accordion_container_inner_div">
                                                <div class="arf_custom_font" data-id="arf_input_font_settings">
                                                    <div class="arf_custom_font_icon">
                                                        <svg viewBox="-10 -10 35 35">
                                                        <g id="paint_brush">
                                                        <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M7.423,14.117c1.076,0,2.093,0.022,3.052,0.068v-0.82c-0.942-0.078-1.457-0.146-1.542-0.205  c-0.124-0.092-0.203-0.354-0.235-0.787s-0.049-1.601-0.049-3.504l0.059-6.568c0-0.299,0.013-0.472,0.039-0.518  C8.772,1.744,8.85,1.725,8.981,1.725c1.549,0,2.584,0.043,3.105,0.128c0.162,0.026,0.267,0.076,0.313,0.148  c0.059,0.092,0.117,0.687,0.176,1.784h0.811c0.052-1.201,0.14-2.249,0.264-3.145l-0.107-0.156c-2.396,0.098-4.561,0.146-6.494,0.146  c-1.94,0-3.936-0.049-5.986-0.146L0.954,0.563c0.078,0.901,0.11,1.976,0.098,3.223h0.84c0.085-1.062,0.141-1.633,0.166-1.714  C2.083,1.99,2.121,1.933,2.17,1.9c0.049-0.032,0.262-0.065,0.641-0.098c0.652-0.052,1.433-0.078,2.34-0.078  c0.443,0,0.674,0.024,0.69,0.073c0.016,0.049,0.024,1.364,0.024,3.947c0,1.313-0.01,2.602-0.029,3.863  c-0.033,1.776-0.072,2.804-0.117,3.084c-0.039,0.201-0.098,0.34-0.176,0.414c-0.078,0.075-0.212,0.129-0.4,0.161  c-0.404,0.065-0.791,0.098-1.162,0.098v0.82C4.861,14.14,6.008,14.117,7.423,14.117L7.423,14.117z"></path>
                                                        </g></svg>
                                                    </div>
                                                    <div class="arf_custom_font_label"><?php echo addslashes(esc_html__('Advanced font options','ARForms')); ?></div>
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Tooltip position', 'ARForms')); ?></div>

                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Position', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">      
                                                <div class="arf_toggle_button_group arf_four_button_group">
                                                    <?php $newarr['arftooltipposition'] = isset($newarr['arftooltipposition']) ? $newarr['arftooltipposition'] : 'top'; ?>
                                                    <label id="rightpos" class="arf_toggle_btn righttoolpos <?php echo ($newarr['arftooltipposition'] == 'right') ? 'arf_success' : ''; ?>"><input type="radio" name="arftippos" class="visuallyhidden arftooltipposition" id='right_pos' data-id="arfestbc2" value="right" <?Php checked($newarr['arftooltipposition'], 'right'); ?> /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label id="leftpos" class="arf_toggle_btn lefttoolpos <?php echo ($newarr['arftooltipposition'] == 'left') ? 'arf_success' : ''; ?>"><input type="radio" name="arftippos" class="visuallyhidden arftooltipposition" id='left_pos' data-id="arfestbc2" value="left" <?Php checked($newarr['arftooltipposition'], 'left'); ?> /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                    <label id="bottompos" class="arf_toggle_btn bottomtoolpos <?php echo ($newarr['arftooltipposition'] == 'bottom') ? 'arf_success' : ''; ?>"><input type="radio" name="arftippos" class="visuallyhidden arftooltipposition" id='bottom_pos'  data-id="arfestbc2" value="bottom" <?Php checked($newarr['arftooltipposition'], 'bottom'); ?> /><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></label>
                                                    <label id="toppos" class="arf_toggle_btn toptoolpos <?php echo ($newarr['arftooltipposition'] == 'top' ) ? 'arf_success' : ''; ?>"><input type='radio' name='arftippos' class='visuallyhidden arftooltipposition' id='top_pos' value='top' <?php checked($newarr['arftooltipposition'], 'top'); ?> /><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Field inner spacing', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Vertical', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input id="arffieldinnermarginsetting_1" name="arffieldinnermarginsetting_1" class="txtxbox_widget" style="width:142px;" type="text" onchange="arf_change_field_spacing2();" value="<?php echo esc_attr($newarr['arffieldinnermarginssetting_1']) ?>" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')) ?></span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                         <div id="arffieldinnermarginsetting_1_vertical" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div> 
                                                        <input id="arffieldinnermarginssetting_1_exs" class="arf_slider_input" data-slider-id='arffieldinnermarginssetting_1_exsSlider' type="text" data-dvalue="<?php echo floatval($newarr['arffieldinnermarginssetting_1']); ?>" data-slider-value="<?php echo floatval($newarr['arffieldinnermarginssetting_1']) ?>" />
                                                        <input type="hidden" name="arffieldinnermarginsetting_1" id="arffieldinnermarginsetting_1" value="<?php echo floatval($newarr['arffieldinnermarginssetting_1']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;">0 px</div>
                                                            <div class="arf_px" style="float:right;">25 px</div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Horizontal', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input id="arffieldinnermarginsetting_2" name="arffieldinnermarginsetting_2" class="txtxbox_widget" style="width:142px;" type="text" onchange="arf_change_field_spacing2();" value="<?php echo esc_attr($newarr['arffieldinnermarginssetting_2']) ?>" />&nbsp;<span class="arf_px">px</span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                         <div id="arffieldinnermarginsetting_2_horizontal" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div>
                                                        <input id="arffieldinnermarginssetting_2_exs" class="arf_slider_input" data-slider-id='arffieldinnermarginssetting_2_exsSlider' type="text" data-slider-min="0" data-slider-max="25" data-slider-step="1" data-dvalue="<?php echo floatval($newarr['arffieldinnermarginssetting_2']); ?>" data-slider-value="<?php echo floatval($newarr['arffieldinnermarginssetting_2']); ?>" />
                                                        <input type="hidden" name="arffieldinnermarginsetting_2" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .sltstandard_front .arfbtn.dropdown-toggle .filter-option~|~left||.ar_main_div_{arf_form_id} .sltstandard_front .arfbtn.dropdown-toggle .filter-option~|~right","material":".ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls .arf_material_theme_container input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"email\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"email\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"phone\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"phone\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"tel\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"tel\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"password\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"password\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"hidden\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"hidden\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"number\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"number\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"url\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"url\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls textarea~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls textarea~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-select-dropdown li span~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-select-dropdown li span~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-selectpicker-control.arf_form_field_picker dt span~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-selectpicker-control.arf_form_field_picker dt span~|~padding-right","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"email\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"email\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"phone\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"phone\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"tel\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"tel\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"password\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"password\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"hidden\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"hidden\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"number\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"number\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"url\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"url\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls textarea~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls textarea~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf-select-dropdown li span~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf-select-dropdown li span~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf-selectpicker-control.arf_form_field_picker dt span~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf-selectpicker-control.arf_form_field_picker dt span~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf-selectpicker-control.arf_form_field_picker dd ul li~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf-selectpicker-control.arf_form_field_picker dd ul li~|~padding-right||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~width"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_inner_spacing_for_dropdown" id="arffieldinnermarginsetting_2" value="<?php echo floatval($newarr['arffieldinnermarginssetting_2']); ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;" >0 px</div>
                                                            <div class="arf_px" style="float:right;" >25 px</div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                                $arffieldinnermarginssetting_value = $newarr['arffieldinnermarginssetting_1'] . "px " . $newarr['arffieldinnermarginssetting_2'] . "px " . $newarr['arffieldinnermarginssetting_1'] . "px " . $newarr['arffieldinnermarginssetting_2'] . "px";
                                                ?>
                                                <input type="hidden" name="arffims" id="arffieldinnermarginsetting" style="width:100px;" class="txtxbox_widget" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_autocomplete):not(.arfslider)~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~padding||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~padding||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~padding||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~padding||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~padding","material":".ar_main_div_{arf_form_id} .arf_materialize_form .arf_material_theme_container input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):not(.arf-selectpicker-input-control)~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_material_theme_container .arf_material_theme_container_with_icons input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):not(.arf-selectpicker-input-control)~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_material_theme_container_with_icons  input[type=\"email\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"phone\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls .arf_material_theme_container_with_icons input[type=\"phone\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls .arf_phone_with_flag input[type=\"phone\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_material_theme_container_with_icons input[type=\"tel\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls .arf_material_theme_container_with_icons input[type=\"password\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls .arf_material_theme_container_with_icons input[type=\"number\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls .arf_material_theme_container_with_icons input[type=\"url\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"password\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"hidden\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"number\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"url\"]~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_material_theme_container textarea:not(.html_field_description):not(.g-recaptcha-response):not(.wp-editor-area)~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~padding||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~padding","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):not(.arf-selectpicker-input-control)~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"phone\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"password\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"hidden\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"number\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls input[type=\"url\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outline_container_with_icons input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):not(.arf-selectpicker-input-control)~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_material_outline_container_with_icons input[type=\"email\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls .arf_material_outline_container_with_icons input[type=\"phone\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_material_outline_container_with_icons input[type=\"tel\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls .arf_material_outline_container_with_icons input[type=\"password\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls .arf_material_outline_container_with_icons input[type=\"hidden\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls .arf_material_outline_container_with_icons input[type=\"number\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .controls .arf_material_outline_container_with_icons input[type=\"url\"]~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container textarea:not(.html_field_description):not(.g-recaptcha-response):not(.wp-editor-area)~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfdropdown-menu > li > a~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~padding||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~padding"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_padding" value="<?php echo $arffieldinnermarginssetting_value; ?>"  size="5" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Field Border Settings', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Border Size', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" name="arffbws" style="width:142px;" id="arffieldborderwidthsetting" class="txtxbox_widget" value="<?php echo esc_attr($newarr['arffieldborderwidthsetting']) ?>" size="4" />&nbsp;<span class="arf_px">px</span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <div id="arf_fieldborderwidthsetting" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div> 
                                                        <input id="arffieldborderwidthsetting_exs" class="arf_slider_input" data-slider-id='arffieldborderwidthsetting_exsSlider' type="text" data-slider-value="<?php echo esc_attr($newarr['arffieldborderwidthsetting']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;">0 px</div>
                                                            <div class="arf_px" style="float:right;">20 px</div>
                                                        </div>

                                                        <input type="hidden" name="arffbws" style="width:100px;" id="arffieldborderwidthsetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker)~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls .trumbowyg-box~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu.open~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu~|~border-width||.ar_main_div_{arf_form_id} .typeahead.arfdropdown-menu~|~border-width||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-left-width||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-top-width||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-bottom-width||.ar_main_div_{arf_form_id} input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dt~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul~|~border-width","material":".ar_main_div_{arf_form_id} .arf_materialize_form .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arf_autocomplete)~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-width||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_colorpicker)~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-left-width||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-top-width||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-width||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-width||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dt~|~border-width||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul~|~border-width","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-left-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-top-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_notch~|~border-top-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_notch~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-right-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-top-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .trumbowyg-box~|~border-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arf_autocomplete)~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-width||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_colorpicker)~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-left-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-top-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dt~|~border-width||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul~|~border-width"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_width" class="txtxbox_widget" value="<?php echo esc_attr($newarr['arffieldborderwidthsetting']) ?>" size="4" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text" ><?php echo addslashes(esc_html__('Border Radius', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" name="arfmbs" style="width:142px;" class="txtxbox_widget"  id="arfmainbordersetting" value="<?php echo esc_attr($newarr['border_radius']) ?>" size="4" />&nbsp;<span class="arf_px">px</span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <div id="arf_arfmainbordersetting" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div>
                                                        <input id="arfmainbordersetting_exs" class="arf_slider_input" data-slider-id='arfmainbordersetting_exsSlider' type="text" data-slider-min="0" data-slider-max="50" data-slider-step="1" data-slider-value="<?php echo esc_attr($newarr['border_radius']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;">0 px</div>
                                                            <div class="arf_px" style="float:right;">50 px</div>
                                                        </div>

                                                        <input type="hidden" name="arfmbs" style="width:100px;" class="txtxbox_widget"  id="arfmainbordersetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_editor_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~border-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-top-left-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-top-right-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon ~|~border-top-right-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon ~|~border-top-left-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-bottom-left-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-bottom-right-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon ~|~border-bottom-right-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon ~|~border-bottom-left-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-radius||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-top-left-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-bottom-left-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-top-right-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-bottom-right-radius||body.rtl .ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider).arf_editor_colorpicker~|~border-top-left-radius||body.rtl .ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider).arf_editor_colorpicker~|~border-bottom-left-radius||body:not(.rtl) .ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider).arf_editor_colorpicker~|~border-top-right-radius||body:not(.rtl) .ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider).arf_editor_colorpicker~|~border-bottom-right-radius||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle~|~border-top-left-radius-custom||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle~|~border-top-right-radius-custom||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker:not(.open) dt~|~border-radius||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open:not(.open_from_top) dt~|~border-top-left-radius||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open:not(.open_from_top) dt~|~border-top-right-radius||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open.open_from_top dt~|~border-bottom-left-radius||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open.open_from_top dt~|~border-bottom-right-radius","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker)~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-radius","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker)~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-radius"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_radius" value="<?php echo esc_attr($newarr['border_radius']) ?>" size="4" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Border Style', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">                                               
                                                <div class="arf_toggle_button_group arf_three_button_group" style="margin-right:5px;">
                                                    <?php $newarr['arffieldborderstylesetting'] = isset($newarr['arffieldborderstylesetting']) ? $newarr['arffieldborderstylesetting'] : 'solid'; ?>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arffieldborderstylesetting'] == 'dashed') ? 'arf_success' : ''; ?>"><input type="radio" name="arffbss" id="arf_input_border_style_dashed" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"email\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"phone\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"tel\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"password\"]~|~border-style||.ar_main_div_{arf_form_id} .trumbowyg-box~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"hidden\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"number\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"url\"]~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu.open~|~border-style||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dt~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul~|~border-style","material":".ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker)~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dt~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul~|~border-style","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-top-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-left-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_notch~|~border-top-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_notch~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-top-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-right-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .trumbowyg-box~|~border-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_style" class="visuallyhidden" value="dashed" <?php checked($newarr['arffieldborderstylesetting'], 'dashed'); ?> /><?php echo addslashes(esc_html__('Dashed', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arffieldborderstylesetting'] == 'dotted') ? 'arf_success' : ''; ?>"><input type="radio" name="arffbss" id="arf_input_border_style_dotted" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id} input[type=\"email\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"phone\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"tel\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"password\"]~|~border-style||.ar_main_div_{arf_form_id} .trumbowyg-box~|~border-style||.ar_main_div_{arf_form_id} input[type=\"hidden\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"number\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"url\"]~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu.open~|~border-style||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dt~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul~|~border-style","material":".ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker)~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dt~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul~|~border-style","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-top-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-left-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_notch~|~border-top-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_notch~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-top-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-right-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .trumbowyg-box~|~border-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_style" class="visuallyhidden" value="dotted" <?php checked($newarr['arffieldborderstylesetting'], 'dotted'); ?> /><?php echo addslashes(esc_html__('Dotted', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arffieldborderstylesetting'] == 'solid') ? 'arf_success' : ''; ?>"><input type="radio" name="arffbss" id="arf_input_border_style_solid" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id} input[type=\"email\"]~|~border-style||.ar_main_div_{arf_form_id} .trumbowyg-box~|~border-style||.ar_main_div_{arf_form_id} input[type=\"phone\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"tel\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"password\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"hidden\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"number\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"url\"]~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu.open~|~border-style||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dt~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul~|~border-style","material":".ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker)~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dt~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul~|~border-style","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-top-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-left-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_notch~|~border-top-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_notch~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-top-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-right-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .trumbowyg-box~|~border-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_style" class="visuallyhidden" value="solid" <?php checked($newarr['arffieldborderstylesetting'], 'solid'); ?> /><?php echo addslashes(esc_html__('Solid', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Calendar Date Format', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Date Format', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">
                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;float:right;">
                                                    <?php
                                                    $wp_format_date = get_option('date_format');

                                                    if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
                                                        ?>
                                                        <div class="sltstandard1" style="float:left;">


                                                            <?php
                                                            $arf_selbx_dt_format = "";
                                                            if ($newarr['date_format'] == 'MMMM D, YYYY') {
                                                                $arf_selbx_dt_format = date('F d, Y', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'MMM D, YYYY') {
                                                                $arf_selbx_dt_format = date('M d, Y', current_time('timestamp'));
                                                            } else {
                                                                $arf_selbx_dt_format = date('m/d/Y', current_time('timestamp'));
                                                            }

                                                            $wp_format_date_opts = array(
                                                                'MM/DD/YYYY' => date('m/d/Y', current_time('timestamp') ),
                                                                'MMM D, YYYY' => date('M d, Y', current_time('timestamp') ),
                                                                'MMMM D, YYYY' => date('F d, Y', current_time('timestamp') ),
                                                            );

                                                            if(!(array_key_exists($newarr['date_format'], $wp_format_date_opts))){

                                                                $wp_format_date_opts[$newarr['date_format']] = date($arf_date_check_arr[ $newarr['date_format'] ], current_time('timestamp') );
                                                            }


                                                            $wp_format_date_attr = array(
                                                                'onchange' => 'change_date_format_new()'
                                                            );

                                                            echo $maincontroller->arf_selectpicker_dom( 'arffdaf', 'frm_date_format', '', 'width:155px', $newarr['date_format'], $wp_format_date_attr, $wp_format_date_opts );

                                                            ?>

                                                        </div>
                                                    <?php

                                                    } else if ($wp_format_date == 'd/m/Y') {

                                                    ?>

                                                        <div class="sltstandard1" style="float:left;">

                                                            <?php
                                                            $arf_selbx_dt_format = "";
                                                            if ($newarr['date_format'] == 'D MMMM, YYYY') {
                                                                $arf_selbx_dt_format = date('d F, Y', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'D MMM, YYYY') {
                                                                $arf_selbx_dt_format = date('d M, Y', current_time('timestamp'));
                                                            }else  {
                                                                $arf_selbx_dt_format = date('d/m/Y', current_time('timestamp'));
                                                            } 


                                                            $wp_format_date_opts = array(
                                                                'DD/MM/YYYY' => date('d/m/Y', current_time('timestamp') ),
                                                                'D MMM, YYYY' => date('d M, Y', current_time('timestamp') ),
                                                                'D MMMM, YYYY' => date('d F, Y', current_time('timestamp') ),
                                                            );

                                                            if(!(array_key_exists($newarr['date_format'], $wp_format_date_opts))){

                                                                $wp_format_date_opts[$newarr['date_format']] = date($arf_date_check_arr[ $newarr['date_format'] ], current_time('timestamp') );
                                                            }

                                                            $wp_format_date_attr = array(
                                                                'onchange' => 'change_date_format_new()'
                                                            );

                                                            echo $maincontroller->arf_selectpicker_dom( 'arffdaf', 'frm_date_format', '', 'width:122px', $newarr['date_format'], $wp_format_date_attr, $wp_format_date_opts );

                                                            ?>

                                                        </div>



                                                    <?php } else if ($wp_format_date == 'Y/m/d') { ?>

                                                        <div class="sltstandard1" style="float:left;">

                                                            <?php
                                                            $arf_selbx_dt_format = "";
                                                            if ($newarr['date_format'] == 'YYYY, MMMM D') {
                                                                $arf_selbx_dt_format = date('Y, F d', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'YYYY, MMM D') {
                                                                $arf_selbx_dt_format = date('Y, M d', current_time('timestamp'));
                                                            } else {
                                                                $arf_selbx_dt_format = date('Y/m/d', current_time('timestamp'));
                                                            }

                                                            $wp_format_date_opts = array(
                                                                'YYYY/MM/DD' => date('Y/m/d', current_time('timestamp') ),
                                                                'YYYY, MMM DD' => date('Y, M d', current_time('timestamp') ),
                                                                'YYYY, MMMM D' => date('Y, F d', current_time('timestamp') ),
                                                            );

                                                            if(!(array_key_exists($newarr['date_format'], $wp_format_date_opts))){

                                                                $wp_format_date_opts[$newarr['date_format']] = date($arf_date_check_arr[ $newarr['date_format'] ], current_time('timestamp') );
                                                            }


                                                            $wp_format_date_attr = array(
                                                                'onchange' => 'change_date_format_new()'
                                                            );

                                                            echo $maincontroller->arf_selectpicker_dom( 'arffdaf', 'frm_date_format', '', 'width:122px', $newarr['date_format'], $wp_format_date_attr, $wp_format_date_opts );
                                                            ?>
                                                        </div>



                                                    <?php }else if($wp_format_date == 'd.F.y' || $wp_format_date == 'd.m.Y' || $wp_format_date == 'Y.m.d' || $wp_format_date == 'd. F Y') { ?>
                                                        
                                                        <div class="sltstandard1" style="float:left;">

                                                            <?php
                                                            $arf_selbx_dt_format = "";
                                                            
                                                            if ($newarr['date_format'] == 'D.MM.YYYY') {
                                                                $arf_selbx_dt_format = date('d.m.Y', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'YYYY.MM.D'){
                                                                $arf_selbx_dt_format = date('Y.m.d', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'D. MMMM YYYY'){
                                                                $arf_selbx_dt_format = date('d. F Y', current_time('timestamp'));
                                                            } else{
                                                                $arf_selbx_dt_format = date('d.F.y', current_time('timestamp'));
                                                            }

                                                            $wp_format_date_opts = array(
                                                                'D.MMMM.YY' => date('d.F.y', current_time('timestamp') ),
                                                                'D.MM.YYYY' => date('d.m.Y', current_time('timestamp') ),
                                                                'YYYY.MM.D' => date('Y.m.d', current_time('timestamp') ),
                                                                'D. MMMM YYYY' => date('d. F Y', current_time('timestamp') ),
                                                            );

                                                            if(!(array_key_exists($newarr['date_format'], $wp_format_date_opts))){

                                                                $wp_format_date_opts[$newarr['date_format']] = date($arf_date_check_arr[ $newarr['date_format'] ], current_time('timestamp') );
                                                            }


                                                            $wp_format_date_attr = array(
                                                                'onchange' => 'change_date_format_new()'
                                                            );

                                                            echo $maincontroller->arf_selectpicker_dom( 'arffdaf', 'frm_date_format', '', 'width:122px', $newarr['date_format'], $wp_format_date_attr, $wp_format_date_opts );

                                                            ?>
                                                        </div>  
                                                    <?php } else { ?>

                                                        <div class="sltstandard1" style="float:left;">

                                                            <?php
                                                            $arf_selbx_dt_format = "";
                                                            if ($newarr['date_format'] == 'MMMM D, YYYY') {
                                                                $arf_selbx_dt_format = date('F d, Y', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'MMM D, YYYY') {
                                                                $arf_selbx_dt_format = date('M d, Y', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'YYYY/MM/DD') {
                                                                $arf_selbx_dt_format = date('Y/m/d', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'MM/DD/YYYY') {
                                                                $arf_selbx_dt_format = date('m/d/Y', current_time('timestamp'));
                                                            } else {
                                                                $arf_selbx_dt_format = date('d/m/Y', current_time('timestamp'));
                                                            }

                                                            $wp_format_date_opts = array(
                                                                'DD/MM/YYYY' => date('d/m/y', current_time('timestamp') ),
                                                                'MM/DD/YYYY' => date('m/d/Y', current_time('timestamp') ),
                                                                'YYYY/MM/DD' => date('Y/m/d', current_time('timestamp') ),
                                                                'MMM D, YYYY' => date('M d, Y', current_time('timestamp') ),
                                                                'MMMM D, YYYY' => date('F d, Y', current_time('timestamp') ),
                                                            );

                                                            if(!(array_key_exists($newarr['date_format'], $wp_format_date_opts))){

                                                                $wp_format_date_opts[$newarr['date_format']] = date($arf_date_check_arr[ $newarr['date_format'] ], current_time('timestamp') );
                                                            }

                                                            $wp_format_date_attr = array(
                                                                'onchange' => 'change_date_format_new()'
                                                            );

                                                            echo $maincontroller->arf_selectpicker_dom( 'arffdaf', 'frm_date_format', '', 'width:122px', $newarr['date_format'], $wp_format_date_attr, $wp_format_date_opts );

                                                            ?>
                                                        </div>
                                                    <?php }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- addtimer -->
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('PageBreak Timer Settings', 'ARForms')); ?></div>
                                        </div>
                                         <div class="arf_help_div">
                                            <?php echo esc_html__('(When this settings is enabled, previous page clickable functionality will not work.)','ARForms');?></div>
                                        <!-- add timer switch -->
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Add Timer', 'ARForms')); ?>
                                            </div>
                                            <div class="arf_float_right arfmarginright4">
                                                <label class="arf_js_switch_label">
                                                    <span><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                                                </label>
                                                <span class="arf_js_switch_wrapper arf_no_transition">
                                                    <input type="checkbox" class="js-switch" name="arfsettimer" id="arfsettimer" value="1" <?php echo (isset($newarr['arfsettimer']) && $newarr['arfsettimer'] == 1) ? 'checked="checked"' : ""; ?>  onchange="arfchangeaddtimer()"/>
                                                    <span class="arf_js_switch"></span>
                                                </span>
                                                <label class="arf_js_switch_label">
                                                    <span>&nbsp;<?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- timerstarton -->
                                        
                                            <div class="arf_accordion_container_row arf_half_width" id="arfpagebreak_settimeron" <?php if(isset($newarr['arfsettimer']) && $newarr['arfsettimer'] == 0){ ?> style="display: none;" <?php } ?> >
                                                <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Set Timer On', 'ARForms')); ?></div>
                                                <div class="arf_accordion_content_container arf_align_right arf_right">                                                    
                                                    <div class="arf_toggle_button_group arf_three_button_group">

                                                        <?php $newarr['arfpagebreaksettimeron'] = isset($newarr['arfpagebreaksettimeron']) ? $newarr['arfpagebreaksettimeron'] : 'overallform'; ?>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arfpagebreaksettimeron'] == 'individualstep') ? 'arf_success' : ''; ?>">
                                                            <input type="radio" name="arfpagebreaksettimeron" class="visuallyhidden" id="arfpagebreaksettimeron_individualstep" value="individualstep" <?Php checked($newarr['arfpagebreaksettimeron'], 'individualstep'); ?> /><?php echo addslashes(esc_html__('Individual Steps', 'ARForms')); ?>
                                                        </label>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arfpagebreaksettimeron'] == 'overallform') ? 'arf_success' : ''; ?>">
                                                            <input type="radio" name="arfpagebreaksettimeron" class="visuallyhidden" id="arfpagebreaksettimeron_overallform" value="overallform" <?Php checked($newarr['arfpagebreaksettimeron'], 'overallform'); ?> /><?php echo addslashes(esc_html__('Overall Steps', 'ARForms')); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                    
                                        <!-- showunits -->
                                    
                                        <?php
                                            $newarr['showunits_breakfield'] = isset($newarr['showunits_breakfield']) ? $newarr['showunits_breakfield'] : 'normal';
                                            $total_showunits = "";
                                            if ($newarr['showunits_breakfield'] != "normal") {
                                                $total_showunits = ", " . $newarr['showunits_breakfield'];
                                            }
                                        ?>
                                        <div class="arf_accordion_container_row arf_half_width" id="showunits_breakfield" <?php if(isset($newarr['arfsettimer']) && $newarr['arfsettimer'] == 0){ ?> style="display: none;" <?php } ?> >
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Show Units', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_right">         

                                                <div class="arf_toggle_button_group arf_three_button_group" style="margin-right: 5px;">
                                                   <input id="arfunitbreak" name="showunits_breakfield" value="<?php echo $newarr['showunits_breakfield']; ?>" type="hidden" data-default-font="<?php echo $newarr['showunits_breakfield']; ?>" />

                                                    <?php $arf_show_unit_arr = explode(',', $newarr['showunits_breakfield']); ?>

                                                    <span class="arf_unit_btn arf_unit_sec <?php echo (in_array('sec', $arf_show_unit_arr)) ? 'active' : ''; ?>" day_val="2"  data-id="arfunitbreak" data-val='sec'>Sec</span>

                                                    <span class="arf_unit_btn arf_unit_min <?php echo (in_array('min', $arf_show_unit_arr)) ? 'active' : ''; ?>" day_val="1"  data-id="arfunitbreak" data-val='min'>Min</span>

                                                    <span class="arf_unit_btn arf_unit_hrs <?php echo (in_array('hrs', $arf_show_unit_arr)) ? 'active' : ''; ?>" day_val="0"  data-id="arfunitbreak" data-val="hrs">Hrs</span>

                                                </div>
                                            </div>
                                        </div>

                                        <!-- set timer -->

                                        <div class="arf_accordion_container_row arf_half_width" id="settimerval" <?php if(isset($newarr['arfsettimer']) && $newarr['arfsettimer'] == 0){ ?> style="display: none; <?php } ?>"> 
                                            <div class="arf_accordion_inner_title arf_form_padding arf_two_row_text"><?php echo addslashes(esc_html__('Set Timer', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_right">
                                                <?php $check_timer_attr = explode(',', $newarr['showunits_breakfield']); ?>
                                                
                                                <div class="arf_form_timer_val_wrapper"><input type="text" name="arfaddtimerbreakfieldhrs" id="arfaddtimerbreakfieldhrs" value="<?php echo isset($newarr['arfaddtimerbreakfieldhrs']) ? esc_attr($newarr['arfaddtimerbreakfieldhrs']) :'' ; ?>" class="arf_form_padding_box arf_input_unithrs arf_total_unit_check arf_unit_reset_field" <?php if(in_array('hrs',$check_timer_attr)) { ?> enabled="enable" <?php } else { ?> disabled="disabled" <?php } ?> /><br />
                                                <span class="arf_px arf_font_size" style="margin:0 14px 0 17px;"><?php echo addslashes(esc_html__('Hrs', 'ARForms')); ?></span></div>
                                                            

                                                <div class="arf_form_timer_val_wrapper"><input type="text" name="arfaddtimerbreakfieldmin" id="arfaddtimerbreakfieldmin" value="<?php echo isset($newarr['arfaddtimerbreakfieldhrs']) ? esc_attr($newarr['arfaddtimerbreakfieldmin']) : ''; ?>"  class="arf_form_padding_box arf_input_unitmin arf_total_unit_check arf_unit_reset_field"  <?php if(in_array('min',$check_timer_attr)) { ?> enabled="enable" <?php } else { ?> disabled="disabled" <?php } ?> /><br />
                                                <span class="arf_px arf_font_size" style="margin:0 14px 0 17px;"><?php echo addslashes(esc_html__('Min', 'ARForms')); ?></span></div>

                                                 <div class="arf_form_timer_val_wrapper"><input type="text" name="arfaddtimerbreakfieldsec" id="arfaddtimerbreakfieldsec" value="<?php echo isset($newarr['arfaddtimerbreakfieldsec']) ? esc_attr($newarr['arfaddtimerbreakfieldsec']) : ''; ?>"  class="arf_form_padding_box arf_input_unitsec arf_total_unit_check arf_unit_reset_field" <?php if(in_array('sec',$check_timer_attr)) { ?> enabled="enable" <?php } else { ?> disabled="disabled" <?php } ?>/><br />
                                                <span class="arf_px arf_font_size" style="margin:0 14px 0 17px;"><?php echo addslashes(esc_html__('Sec', 'ARForms')); ?></span></div>
                                            </div>
                                        </div>
                                        
                                        <!-- start time on page no -->
                                        <div class="arf_accordion_container_row arf_half_width" id="arfstarttimerpg_no" <?php if( isset($newarr['arfsettimer']) && $newarr['arfsettimer'] == 0){ ?> style="display: none; <?php } ?>"> 
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo esc_html__('Start timer on page no.', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arf_right" id="arf_startpgno">
                                                <?php 
                                                $newarr['arfsettimestartpageno'] = ( isset( $newarr['arfsettimestartpageno'] ) && $newarr['arfsettimestartpageno'] != '' ) ? $newarr['arfsettimestartpageno'] : '1';

                                                 ?>
                                                <input type="text" name="arfstarttimerpgno" class="arf_small_width_txtbox arfcolor arf_total_unit_check" id="arfstarttimerpgno" onkeyup="preventzero_startpgno(event)" onchange="arfvalidatelastpagno();" value="<?php echo esc_attr($newarr["arfsettimestartpageno"]); ?>" size="5" />
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width" id="arfendtimerpg_no" <?php if( isset($newarr['arfsettimer']) && $newarr['arfsettimer'] == 0){ ?> style="display: none; <?php } ?>"> 
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo esc_html__('End timer on page no.', 'ARForms'); ?>
                                            </div>

                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arf_right" id="arfsettimeendpageno">
                                                <?php 
                                                $newarr['arfsettimeendpageno'] = ( isset( $newarr['arfsettimeendpageno'] ) && $newarr['arfsettimeendpageno'] != '' ) ? $newarr['arfsettimeendpageno'] : '';

                                                 ?>
                                                <input type="text" name="arfendtimerpgno" class="arf_small_width_txtbox arfcolor arf_total_unit_check" id="arfendtimerpgno" onkeyup="preventzero_startpgno(event)" onchange="arfvalidatelastpagno();"  value="<?php echo esc_attr($newarr["arfsettimeendpageno"]); ?>" size="5" />
                                            </div>
                                        </div>

                                        <!-- timerstyle -->

                                        <div class="arf_accordion_container_row arf_half_width" id="timerstyle" <?php if(isset($newarr['arfsettimer']) && $newarr['arfsettimer'] == 0){ ?> style="display: none; <?php } ?>"> 
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Timer Style', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">
                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;">
                                                    <?php
                                                        $pagebreak_selected_style=""; 
                                                        if(isset($newarr['arftimerstyle']) && $newarr['arftimerstyle']!=''){
                                                                $pagebreak_selected_style = $newarr['arftimerstyle'];
                                                        } else{ 
                                                            $pagebreak_selected_style = "number";
                                                        }
                                                    ?>
                                                <div class="arf_dropdown_wrapper" style="width: 100%;">
                                                    <?php
                                                        $pagebreak_style_opt = array(
                                                            'number' => 'Number',
                                                            'circle' => 'Circle',
                                                            'circle_with_text' => 'Circle With Text'
                                                        );
                                                        
                                                        echo $maincontroller->arf_selectpicker_dom( 'arf_page_break_style', 'arf_page_break_style', 'arf_selectbox_option', 'width:122px', $pagebreak_selected_style, array() , $pagebreak_style_opt );
                                                    ?>
                                                </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- end timer style -->

                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Checkbox & Radio Style', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">
                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;">
                                                    <?php

                                                        $arf_checkbox_options = array(
                                                            'custom' => addslashes(esc_html__('Custom', 'ARForms')),
                                                            'default' => addslashes(esc_html__('Default', 'ARForms')),
                                                            'material' => addslashes(esc_html__('Material 1', 'ARForms')),
                                                            'material_tick' => addslashes(esc_html__('Material 2', 'ARForms')),
                                                        );

                                                        if ($newarr['arfcheckradiostyle'] != 'custom' && $newarr['arfcheckradiostyle'] == '') {
                                                            $newarr['arfcheckradiostyle'] = 'default';
                                                        }

                                                        $arf_checkbox_attrs = array(
                                                            'onchange' => 'ShowColorSelect(this.value);'
                                                        );

                                                        $options_class_arr = array(
                                                            'custom' => '',
                                                            'default' => ( $newarr['arfinputstyle'] == 'standard' || $newarr['arfinputstyle'] == 'rounded' ) ? 'arfvisible' : 'arfhidden',
                                                            'material' => ( $newarr['arfinputstyle'] == 'standard' || $newarr['arfinputstyle'] == 'rounded' ) ? 'arfhidden' : 'arfvisible', 
                                                            'material_tick' => ( $newarr['arfinputstyle'] == 'standard' || $newarr['arfinputstyle'] == 'rounded' ) ? 'arfhidden' : 'arfvisible', 
                                                        );

                                                        echo $maincontroller->arf_selectpicker_dom( 'arfcksn', 'frm_check_radio_style', '', 'width:122px', $newarr['arfcheckradiostyle'], $arf_checkbox_attrs, $arf_checkbox_options, false, $options_class_arr );

                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" id="check_radio_main_icon" style="<?php echo ($newarr['arfcheckradiostyle'] == "custom") ? 'display:block;margin-bottom: 20px;height: auto;' : 'display:none;margin-bottom: 20px;height: auto;'; ?>">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Icon', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 " style="margin-right: -1px;">
                                                <div class="arf_field_check_radio_wrapper" id="arf_field_check_radio_wrapper arf_right" style="margin-left: -5px;">
                                                    <div class="custom_checkbox_wrapper">
                                                        <div class="arf_prefix_suffix_container_wrapper" data-action='edit' data-field='checkbox' id="arf_edit_check" data-toggle="arfmodal" href="#arf_fontawesome_modal" data-field_type='checkbox'>
                                                            <div class="arf_prefix_container" id="arf_select_checkbox">
                                                                <?php
                                                                if (isset($newarr['arf_checked_checkbox_icon']) && $newarr['arf_checked_checkbox_icon'] != '') {
                                                                    echo "<i id='arf_prefix_suffix_icon' class='arf_prefix_suffix_icon {$newarr['arf_checked_checkbox_icon']}'></i>";
                                                                } else {
                                                                    echo "<i id='arf_prefix_suffix_icon' class='arf_prefix_suffix_icon fa fa-check'></i>";
                                                                }
                                                                ?>
                                                            </div>
                                                            <div class="arf_prefix_suffix_action_container" style="position:relative;">
                                                                <div class="arf_prefix_suffix_action" title="Change Icon" style="margin-left: 15px;">
                                                                    <i class="fas fa-caret-down fa-lg"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="howto"> <?php echo addslashes(esc_html__('CheckBoxes', 'ARForms')); ?> </div>
                                                    </div>
                                                    <br>
                                                    <br>
                                                    <div class="custom_checkbox_wrapper">
                                                        <div class="arf_prefix_suffix_container_wrapper" data-action='edit' data-field='radio' id="arf_edit_radio" data-field_type='radio'>
                                                            <div class="arf_suffix_container" id="arf_select_radio">
                                                                <?php
                                                                if (isset($newarr['arf_checked_radio_icon']) && $newarr['arf_checked_radio_icon'] != '') {
                                                                    echo "<i id='arf_prefix_suffix_icon' class='arf_prefix_suffix_icon  {$newarr['arf_checked_radio_icon']}'></i>";
                                                                } else {
                                                                    echo "<i id='arf_prefix_suffix_icon' class='arf_prefix_suffix_icon fa fa-circle'></i>";
                                                                }
                                                                ?>
                                                            </div>
                                                            <div class="arf_prefix_suffix_action_container" style="position:relative;">
                                                                <div class="arf_prefix_suffix_action" title="Change Icon" style="margin-left: 15px;">
                                                                    <i class="fas fa-caret-down fa-lg"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="howto"> <?php echo addslashes(esc_html__('Radio Buttons', 'ARForms')); ?> </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php do_action( 'arf_add_form_additional_input_settings', $id, $values ); ?>
                                        <div class="arf_accordion_container_row arf_half_width" style="height: 5px;min-height:5px"></div>
                                        <input type="hidden" name="enable_arf_checkbox" id="enable_arf_checkbox" value="<?php echo isset($newarr['enable_arf_checkbox']) ? $newarr['enable_arf_checkbox'] : ''; ?>" />
                                        <input type="hidden" name="arf_checkbox_icon" id="arf_checkbox_icon" value="<?php echo (isset($newarr['arf_checked_checkbox_icon']) && $newarr['arf_checked_checkbox_icon'] != '') ? $newarr['arf_checked_checkbox_icon'] : 'fa fa-check'; ?>" />
                                        <input type="hidden" name="enable_arf_radio" id="enable_arf_radio" value="<?php echo isset($newarr['enable_arf_radio']) ? $newarr['enable_arf_radio'] : ''; ?>" />
                                        <input type="hidden" name="arf_radio_icon" id="arf_radio_icon" value="<?php echo (isset($newarr['arf_checked_radio_icon']) && $newarr['arf_checked_radio_icon'] != '') ? $newarr['arf_checked_radio_icon'] : 'fa fa-circle'; ?>" />
                                    </div>
                                </dd>
                            </dl>

                            <!-- change start from here -->

                            <?php
                                $form_fields_animation_style = array(
                                    'backInUp' => addslashes('BackInUp'),
                                    'backInDown' => addslashes('BackInDown'),
                                    'backInLeft' => addslashes('BackInLeft'),
                                    'backInRight' => addslashes('BackInRight'),
                                    'backOutUp' => addslashes('BackOutUp'),
                                    'backOutDown' => addslashes('BackOutDown'),
                                    'backOutLeft' => addslashes('BackOutLeft'),
                                    'backOutRight' => addslashes('BackOutRight'),
                                    'bounceInUp' => addslashes('BounceInUp'),
                                    'bounceInDown' => addslashes('BounceInDown'),
                                    'bounceInLeft' => addslashes('BounceInLeft'),
                                    'bounceInRight' => addslashes('BounceInRight'),
                                    'bounceOutUp' => addslashes('BounceOutUp'),
                                    'bounceOutDown' => addslashes('BounceOutDown'),
                                    'bounceOutLeft' => addslashes('BounceOutLeft'),
                                    'bounceOutRight' => addslashes('BounceOutRight'),
                                    'fadeInUp' => addslashes('FadeInUp'),
                                    'fadeInDown' => addslashes('FadeInDown'),
                                    'fadeInLeft' => addslashes('FadeInLeft'),
                                    'fadeInRight' => addslashes('FadeInRight'),
                                    'fadeInUpBig' => addslashes('FadeInUpBig'),
                                    'fadeInDownBig' => addslashes('FadeInDownBig'),
                                    'fadeInLeftBig' => addslashes('FadeInLeftBig'),
                                    'fadeInRightBig' => addslashes('FadeInRightBig'),
                                    'fadeInTopLeft' => addslashes('FadeInTopLeft'),
                                    'fadeInTopRight' => addslashes('FadeInTopRight'),
                                    'fadeInBottomLeft' => addslashes('FadeInBottomLeft'),
                                    'fadeInBottomRight' => addslashes('FadeInBottomRight'),
                                    'fadeOutUp' => addslashes('FadeOutUp'),
                                    'fadeOutDown' => addslashes('FadeOutDown'),
                                    'fadeOutLeft' => addslashes('FadeOutLeft'),
                                    'fadeOutRight' => addslashes('FadeOutRight'),
                                    'fadeOutUpBig' => addslashes('FadeOutUpBig'),
                                    'fadeOutDownBig' => addslashes('FadeOutDownBig'),
                                    'fadeOutLeftBig' => addslashes('FadeOutLeftBig'),
                                    'fadeOutRightBig' => addslashes('FadeOutRightBig'),
                                    'fadeOutTopLeft' => addslashes('FadeOutTopLeft'),
                                    'fadeOutTopRight' => addslashes('FadeOutTopRight'),
                                    'fadeOutBottomLeft' => addslashes('FadeOutBottomLeft'),
                                    'fadeOutBottomRight' => addslashes('FadeOutBottomRight'),
                                    'flipInX' => addslashes('FlipInX'),
                                    'flipInY' => addslashes('FlipInY'),
                                    'flipOutX' => addslashes('FlipOutX'),
                                    'flipOutY' => addslashes('FlipOutY'),
                                    'lightSpeedInLeft' => addslashes('LightSpeedInLeft'),
                                    'lightSpeedInRight' => addslashes('LightSpeedInRight'),
                                    'lightSpeedOutLeft' => addslashes('LightSpeedOutLeft'),
                                    'lightSpeedOutRight' => addslashes('LightSpeedOutRight'),
                                    'rotateIn' => addslashes('RotateIn'),
                                    'rotateOut' => addslashes('RotateOut'),
                                    'rotateInUpLeft' => addslashes('RotateInUpLeft'),
                                    'rotateInDownLeft' => addslashes('RotateInDownLeft'),
                                    'rotateInUpRight' => addslashes('RotateInUpRight'),
                                    'rotateInDownRight' => addslashes('RotateInDownRight'),
                                    'rotateOutUpLeft' => addslashes('RotateOutUpLeft'),
                                    'rotateOutDownLeft' => addslashes('RotateOutDownLeft'),
                                    'rotateOutUpRight' => addslashes('RotateOutUpRight'),
                                    'rotateOutDownRight' => addslashes('RotateOutDownRight'),
                                    'rollIn' => addslashes('RollIn'),
                                    'rollOut' => addslashes('RollOut'),
                                    'zoomIn' => addslashes('ZoomIn'),
                                    'zoomOut' => addslashes('ZoomOut'),
                                    'zoomInUp' => addslashes('ZoomInUp'),
                                    'zoomInDown' => addslashes('ZoomInDown'),
                                    'zoomInLeft' => addslashes('ZoomInLeft'),
                                    'zoomInRight' => addslashes('ZoomInRight'),
                                    'zoomOutUp' => addslashes('ZoomOutUp'),
                                    'zoomOutDown' => addslashes('ZoomOutDown'),
                                    'zoomOutLeft' => addslashes('ZoomOutLeft'),
                                    'zoomOutRight' => addslashes('ZoomOutRight'),
                                    'slideInUp' => addslashes('SlideInUp'),
                                    'slideInDown' => addslashes('SlideInDown'),
                                    'slideInLeft' => addslashes('SlideInLeft'),
                                    'slideInRight' => addslashes('SlideInRight'),
                                    'slideOutUp' => addslashes('SlideOutUp'),
                                    'slideOutDown' => addslashes('SlideOutDown'),
                                    'slideOutLeft' => addslashes('SlideOutLeft'),
                                    'slideOutRight' => addslashes('SlideOutRight'),
                                );
                            ?>

                            <dl class="arf_accordion_tab_field_animation_settings arf_field_animation_main_container">
                                <dd>
                                    <a href="javascript:void(0)" data-target="arf_accordion_tab_field_animation_settings"><?php echo esc_html__('Field Animation Options', 'ARForms'); ?></a>
                                    <div class="arf_accordion_container">
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo esc_html__('On Load Animation', 'ARForms'); ?></div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Animation', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">

                                                <?php
                                                    $arf_ol_animation_selected_val=""; 
                                                    if(isset($newarr['arffieldanimationstyle']) && $newarr['arffieldanimationstyle']!=''){
                                                            $arf_ol_animation_selected_val = $newarr['arffieldanimationstyle'];
                                                    } else{ 
                                                        $arf_ol_animation_selected_val = "no animation";
                                                    }
                                                ?>

                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;">

                                                    <?php

                                                        $arf_ol_animation_opt = array(
                                                            'no animation' => 'No Animation',
                                                        );

                                                        foreach ($form_fields_animation_style as $key => $value) {
                                                            $arf_ol_animation_opt[$key] = $value;
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'arffans', 'frm_fields_animation_style', '', 'width:170px;', $arf_ol_animation_selected_val, array(), $arf_ol_animation_opt );
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo esc_html__('Animation Duration', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arf_right">
                                                <span class="arfsecondspan arfanimationdurationsecond">S</span>
                                                <?php 

                                                $newarr['arffieldanimationdurationsetting'] = ( isset( $newarr['arffieldanimationdurationsetting'] ) && $newarr['arffieldanimationdurationsetting'] != '' ) ? $newarr['arffieldanimationdurationsetting'] : '0';

                                                 ?>
                                                <input type="text" name="arfandus" class="arf_small_width_txtbox arfcolor arffieldanimationdurationinput" id="arffieldanimationdurationsetting" onkeydown="arfvalidatefloatnumbers(this,event);" value="<?php echo esc_attr($newarr["arffieldanimationdurationsetting"]); ?>" size="5" />
                                                <input type="hidden" name="arfanduu" id="arfanimationdurationunit" value="s"  <?php echo($newarr['position'] == 'top')?'disabled="disabled"':'';?>/>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo esc_html__('Animation Delay', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arf_right">
                                                <span class="arfsecondspan arfanimationdelaysecond">S</span>
                                                <?php 

                                                $newarr['arffieldanimationdelaysetting'] = ( isset( $newarr['arffieldanimationdelaysetting'] ) && $newarr['arffieldanimationdelaysetting'] != '' ) ? $newarr['arffieldanimationdelaysetting'] : '0';

                                                 ?>
                                                <input type="text" name="arfandls" class="arf_small_width_txtbox arfcolor arffieldanimationdelayinput" id="arffieldanimationdelaysetting" onkeydown="arfvalidatefloatnumbers(this,event);" value="<?php echo esc_attr($newarr["arffieldanimationdelaysetting"]); ?>" size="5" />
                                                <input type="hidden" name="arfandlu" id="arfanimationdelayunit" value="s"  <?php echo($newarr['position'] == 'top')?'disabled="disabled"':'';?>/>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="height: auto;">

                                            <div class="arf_accordion_container_inner_div">
                                                <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Preview','ARForms')); ?></div>
                                            </div>
                                            <div class="arf_accordion_container_inner_div arf_field_animation_preview_main_container">
                                                <div class="arf_field_animation_preview_inner_container"><div class="" id="arf_field_animation_preview_text_container">Animation</div></div>
                                                <!-- preview text box goes here -->
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo esc_html__('Page Break Animation', 'ARForms'); ?></div>
                                        </div>

                                        <?php
                                            $disable_pb_inherit_animation_options = '';
                                        ?>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo esc_html__('Inherit', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right">
                                                <div class="arf_float_right" style="margin-right:5px;">
                                                    <label class="arf_js_switch_label">
                                                        <span class=""><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                                                    </label>


                                                    <span class="arf_js_switch_wrapper <?php echo $disable_pb_inherit_animation_options; ?>">

                                                        <?php 

                                                            $newarr['arfpagebreakinheritanimation'] = ( isset( $newarr['arfpagebreakinheritanimation'] ) && $newarr['arfpagebreakinheritanimation'] != '' ) ? $newarr['arfpagebreakinheritanimation'] : 0;
                                                         ?>

                                                        <input type="checkbox" class="js-switch" name="arfpbian" id="arfpagebreakinheritanimation" value="<?php echo $newarr['arfpagebreakinheritanimation']; ?>" onchange="frmSetPosClassHide()"  <?php echo ($newarr['arfpagebreakinheritanimation'] == '1') ? 'checked="checked"' : ""; ?> />
                                                        <span class="arf_js_switch"></span>
                                                    </span>
                                                    <label class="arf_js_switch_label">
                                                        <span class="">&nbsp;<?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Animation', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">

                                                <?php
                                                    $arf_pb_animation_selected_val=""; 
                                                    if(isset($newarr['arfpbfieldanimationstyle']) && $newarr['arfpbfieldanimationstyle']!=''){
                                                            $arf_pb_animation_selected_val = $newarr['arfpbfieldanimationstyle'];
                                                    } else{ 
                                                        $arf_pb_animation_selected_val = "slideInLeft";
                                                    }
                                                ?>

                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;">

                                                    <?php

                                                        $arf_pb_animation_opt = array(
                                                            'no animation' => 'No Animation',
                                                        );

                                                        $arf_pb_animation_attr = array();
                                                        $arf_pb_animation_attr_class = '';

                                                        if($newarr['arfpagebreakinheritanimation'] == '1'){
                                                            $arf_pb_animation_attr['disabled'] = 'disabled';
                                                            $arf_pb_animation_attr_class = ' arf_disabled arf_disabled_container ';
                                                        }

                                                        foreach ($form_fields_animation_style as $key => $value) {
                                                            $arf_pb_animation_opt[$key] = $value;
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'arfpbfans', 'frm_pb_fields_animation_style', $arf_pb_animation_attr_class, 'width:170px;', $arf_pb_animation_selected_val, $arf_pb_animation_attr, $arf_pb_animation_opt );
                                                    ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo esc_html__('Animation Duration', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arf_right <?php echo ($newarr['arfpagebreakinheritanimation'] == '1')? 'arf_disabled_container': ''; ?> ">
                                                <span class="arfsecondspan arfanimationdurationsecond">S</span>
                                                <?php 

                                                $newarr['arfpbfieldanimationdurationsetting'] = ( isset( $newarr['arfpbfieldanimationdurationsetting'] ) && $newarr['arfpbfieldanimationdurationsetting'] != '' ) ? $newarr['arfpbfieldanimationdurationsetting'] : '0';

                                                 ?>
                                                <input type="text" name="arfpbandus" class="arf_small_width_txtbox arfcolor arffieldanimationdurationinput" id="arfpbfieldanimationdurationsetting" onkeydown="arfvalidatefloatnumbers(this,event);" value="<?php echo esc_attr($newarr["arfpbfieldanimationdurationsetting"]); ?>" size="5" <?php echo ($newarr['arfpagebreakinheritanimation'] == '1')? 'disabled': ''; ?> />
                                                <input type="hidden" name="arfanduu" id="arfanimationdurationunit" value="s"  <?php echo($newarr['position'] == 'top')?'disabled="disabled"':'';?>/>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo esc_html__('Animation Delay', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arf_right <?php echo ($newarr['arfpagebreakinheritanimation'] == '1')? 'arf_disabled_container': ''; ?>">
                                                <span class="arfsecondspan arfanimationdelaysecond">S</span>
                                                <?php 

                                                $newarr['arfpbfieldanimationdelaysetting'] = ( isset( $newarr['arfpbfieldanimationdelaysetting'] ) && $newarr['arfpbfieldanimationdelaysetting'] != '' ) ? $newarr['arfpbfieldanimationdelaysetting'] : '0';

                                                 ?>
                                                <input type="text" name="arfpbandls" class="arf_small_width_txtbox arfcolor arffieldanimationdelayinput" id="arfpbfieldanimationdelaysetting" onkeydown="arfvalidatefloatnumbers(this,event);" value="<?php echo esc_attr($newarr["arfpbfieldanimationdelaysetting"]); ?>" size="5" <?php echo ($newarr['arfpagebreakinheritanimation'] == '1')? 'disabled': ''; ?> />
                                                <input type="hidden" name="arfandlu" id="arfanimationdelayunit" value="s"  <?php echo($newarr['position'] == 'top')?'disabled="disabled"':'';?>/>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width" style="height: auto;">

                                            <div class="arf_accordion_container_inner_div">
                                                <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Preview','ARForms')); ?></div>
                                            </div>
                                            <div class="arf_accordion_container_inner_div arf_field_animation_preview_main_container ">
                                                <div class="arf_field_animation_preview_inner_container"><div class="" id="arf_pb_field_animation_preview_text_container">Animation</div></div>
                                                <!-- preview text box goes here -->
                                            </div>
                                        </div>
                                    </div>
                                </dd>
                            </dl>

                            <!-- change over -->

                            <dl class="arf_accordion_tab_submit_settings">
                                <dd>
                                    <a href="javascript:void(0)" data-target="arf_accordion_tab_submit_settings"><?php echo addslashes(esc_html__('Submit Button Options', 'ARForms')); ?></a>
                                    <div class="arf_accordion_container">
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('General Options', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__("Button Alignment", "ARForms")); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right">
                                                <div class="arf_toggle_button_group arf_three_button_group" style="margin-right:8px;">
                                                    <?php $newarr['arfsubmitalignsetting'] = isset($newarr['arfsubmitalignsetting']) ? $newarr['arfsubmitalignsetting'] : 'center'; 
                                                    ?>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfsubmitalignsetting'] == 'right') ? 'arf_success' : ''; ?>"><input type="radio" name="arfmsas" id="frm_submit_align_3"  class="visuallyhidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto","material":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto","material_outlined":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_position" value="right" <?php checked($newarr['arfsubmitalignsetting'], 'right'); ?> /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfsubmitalignsetting'] == 'center') ? 'arf_success' : ''; ?>"><input type="radio" name="arfmsas" class="visuallyhidden" id="frm_submit_align_2" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto","material":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto","material_outlined":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto"}' value="center" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_position" <?php checked($newarr['arfsubmitalignsetting'], 'center'); ?> /><?php echo addslashes(esc_html__('Center', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfsubmitalignsetting'] == 'left') ? 'arf_success' : ''; ?>"><input type="radio" name="arfmsas" class="visuallyhidden" id="frm_submit_align_1" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto","material":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto","material_outlined":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto"}' value="left" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_position" <?php checked($newarr['arfsubmitalignsetting'], 'left'); ?> /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Button Width (optional)', 'ARForms')) ?></div>
                                            <div class="arf_accordion_content_container">
                                                <span class="arfpxspan">px</span>
                                                <input type="text" name="arfsbws" id="arfsubmitbuttonwidthsetting" style="margin-right: 1px;" class="arf_small_width_txtbox arfcolor" value="<?php echo esc_attr($newarr['arfsubmitbuttonwidthsetting']) ?>"  onchange="arfsetsubmitwidth();" size="5" />
                                                <input type="hidden" name="arfsbaw" id="arfsubmitautowidth" value="<?php echo $newarr['arfsubmitautowidth']; ?>" />

                                            </div>

                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50" ><?php echo addslashes(esc_html__('Button Height (optional)', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50">
                                                <span class="arfpxspan">px</span>
                                                <input type="text" name="arfsbhs" id="arfsubmitbuttonheightsetting" class="arf_small_width_txtbox arfcolor" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~height","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~height","material_outlined":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~height"}' style="margin-right: 1px;" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_height" value="<?php echo esc_attr($newarr['arfsubmitbuttonheightsetting']) ?>"  size="5" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Button Text', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">
                                                <?php
                                                $newarr['arfsubmitbuttontext'] = isset($newarr['arfsubmitbuttontext']) ? $newarr['arfsubmitbuttontext'] : '';
                                                if ($newarr['arfsubmitbuttontext'] == '') {
                                                    $arf_option = get_option('arf_options');
                                                    $submit_value = $arf_option->submit_value;
                                                } else {
                                                    $submit_value = esc_attr($newarr['arfsubmitbuttontext']);
                                                }
                                                ?>
                                                <input type="text" name="arfsubmitbuttontext" id="arfsubmitbuttontext" class="arf_large_input_box arfwidth108 arfcolor" value="<?php echo $submit_value; ?>"  style="margin-right:5px;text-align:left;" size="5" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Button Style', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <?php
                                                    $newarr['arfsubmitbuttonstyle'] = isset($newarr['arfsubmitbuttonstyle']) ? $newarr['arfsubmitbuttonstyle'] : 'border';                                                

                                                    $submit_button_style_opts = array(
                                                        'flat' => 'Flat',
                                                        'border' => 'Border',
                                                        'reverse border' => 'Reverse Border'
                                                    );

                                                    $submit_button_style_attr = array(
                                                        'onchange' => 'arfchangebuttonstyle(this.value)'
                                                    );

                                                    echo $maincontroller->arf_selectpicker_dom( 'arfsubmitbuttonstyle', 'arfsubmitbuttonstyle', '', '', $newarr['arfsubmitbuttonstyle'], $submit_button_style_attr, $submit_button_style_opts );
                                                ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="margin-bottom: 30px;">
                                            <div class="arf_accordion_inner_title arf_form_padding" id="arf_sub_btn_margin"><?php echo addslashes(esc_html__('Margin', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_form_container ">
                                                <div class="arf_submit_margin_box_wrapper"><input type="text" name="arfsubmitbuttonmarginsetting_1" id="arfsubmitbuttonmarginsetting_1" onchange="arf_change_field_padding('arfsubmitbuttonmarginsetting');" value="<?php echo esc_attr($newarr['arfsubmitbuttonmarginsetting_1']); ?>" class="arf_submit_margin_box" /><br /><span class="arf_px arf_font_size" ><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span></div>
                                                <div class="arf_submit_margin_box_wrapper"><input type="text" name="arfsubmitbuttonmarginsetting_2" id="arfsubmitbuttonmarginsetting_2" value="<?php echo esc_attr($newarr['arfsubmitbuttonmarginsetting_2']); ?>" onchange="arf_change_field_padding('arfsubmitbuttonmarginsetting');" class="arf_submit_margin_box" /><br /><span class="arf_px arf_font_size" ><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></span></div>
                                                <div class="arf_submit_margin_box_wrapper"><input type="text" name="arfsubmitbuttonmarginsetting_3" id="arfsubmitbuttonmarginsetting_3" value="<?php echo esc_attr($newarr['arfsubmitbuttonmarginsetting_3']); ?>" onchange="arf_change_field_padding('arfsubmitbuttonmarginsetting');" class="arf_submit_margin_box" /><br /><span class="arf_px arf_font_size" style="    margin-left: 5px;"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></span></div>
                                                <div class="arf_submit_margin_box_wrapper"><input type="text" name="arfsubmitbuttonmarginsetting_4" id="arfsubmitbuttonmarginsetting_4" value="<?php echo esc_attr($newarr['arfsubmitbuttonmarginsetting_4']); ?>" onchange="arf_change_field_padding('arfsubmitbuttonmarginsetting');" class="arf_submit_margin_box" /><br /><span class="arf_px arf_font_size" style="margin-left: 10px;"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></span></div>
                                            </div>
                                            <?php
                                            $arfsubmitbuttonmarginsetting_value = '';

                                            if (esc_attr($newarr['arfsubmitbuttonmarginsetting_1']) != '') {
                                                $arfsubmitbuttonmarginsetting_value .= $newarr['arfsubmitbuttonmarginsetting_1'] . 'px ';
                                            } else {
                                                $arfsubmitbuttonmarginsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfsubmitbuttonmarginsetting_2']) != '') {
                                                $arfsubmitbuttonmarginsetting_value .= $newarr['arfsubmitbuttonmarginsetting_2'] . 'px ';
                                            } else {
                                                $arfsubmitbuttonmarginsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfsubmitbuttonmarginsetting_3']) != '') {
                                                $arfsubmitbuttonmarginsetting_value .= $newarr['arfsubmitbuttonmarginsetting_3'] . 'px ';
                                            } else {
                                                $arfsubmitbuttonmarginsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfsubmitbuttonmarginsetting_4']) != '') {
                                                $arfsubmitbuttonmarginsetting_value .= $newarr['arfsubmitbuttonmarginsetting_4'] . 'px';
                                            } else {
                                                $arfsubmitbuttonmarginsetting_value .= '0px';
                                            }
                                            ?>
                                            <input type="hidden" name="arfsbms" id="arfsubmitbuttonmarginsetting" style="width:100px;" class="txtxbox_widget"  data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton.arf_submit_div~|~margin","material":".ar_main_div_{arf_form_id} .arfsubmitbutton.arf_submit_div~|~margin","material_outlined":".ar_main_div_{arf_form_id} .arfsubmitbutton.arf_submit_div~|~margin"}'  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_margin" value="<?php echo $arfsubmitbuttonmarginsetting_value; ?>" size="6" />
                                        </div>

                                        <input type="hidden" name="arfsbcs" id="arfsubmitbuttoncolorsetting" class="hex txtxbox_widget" value="<?php echo esc_attr($newarr['arfsubmitbgcolor2setting']) ?>" style="width:80px;" />
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text " ><?php echo addslashes(esc_html__('Background Image', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right  arf_right">
                                                <div class="arf_imageloader arf_form_style_file_upload_loader" id="ajax_submit_loader"></div>
                                                <div id="submit_btn_img_div" <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') { ?> class="iframe_submit_original_btn" data-id="arfsbis" style="margin-right:5px; position: relative; overflow: hidden; cursor:pointer; max-width:130px; height:27px; background: #1BBAE1; font-weight:bold; <?php if ($newarr['submit_bg_img'] == '') { ?> background:#1BBAE1;padding:7px 10px 0 10px;font-size:13px;border-radius:3px;-webkit-border-radius:3px;-o-border-radius:3px;-moz-border-radius:3px;color:#FFFFFF;border:1px solid #CCCCCC;box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-webkit-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-o-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-moz-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);display: inline-block; <?php } ?>" <?php } else { ?> style="margin-left:0px;" <?php } ?>>
                                                    <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' && $newarr['submit_bg_img'] == '') { ?> <span class="arf_form_style_file_upload_icon">
                                                        <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg></span> <?php } ?>
                                                    <?php
                                                    if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') {
                                                        if ($newarr['submit_bg_img'] != '') {
                                                            ?>
                                                            <img src="<?php echo $newarr['submit_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('button_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                            <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="<?php echo esc_attr($newarr['submit_bg_img']) ?>" id="arfsubmitbuttonimagesetting" />
                                                        <?php } else { ?>
                                                            <input type="text" class="original" name="submit_btn_img" id="field_arfsbis" data-form-id="" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; width: 160px; height: 59px; left: -999px; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                            <input type="hidden" id="type_arfsbis" name="type_arfsbis" value="1" >
                                                            <input type="hidden" value="jpg, jpeg, jpe, gif, png, bmp, tif, tiff, ico" id="file_types_arfsbis" name="field_types_arfsbis" />

                                                            <input type="hidden" name="imagename" id="imagename" value="" />
                                                            <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="" id="arfsubmitbuttonimagesetting" />
                                                            <?php
                                                        }
                                                        echo '<div id="arfsbis_iframe_div"><iframe style="display:none;" id="arfsbis_iframe" src="' . ARFURL . '/core/views/iframe.php" ></iframe></div>';
                                                    } else {
                                                        if ($newarr['submit_bg_img'] != '') {
                                                            ?>
                                                            <img src="<?php echo $newarr['submit_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('button_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                            <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="<?php echo esc_attr($newarr['submit_bg_img']) ?>" id="arfsubmitbuttonimagesetting" />
                                                        <?php } else { ?>
                                                            <div class="arfajaxfileupload">
                                                                <div class="arf_form_style_file_upload_icon">
                                                                    <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg>
                                                                </div>
                                                                <input type="file" data-val="submit_btn_img" name="submit_btn_img" id="submit_btn_img" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                            </div>

                                                            <input type="hidden" name="imagename" id="imagename" value="" />
                                                            <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="" id="arfsubmitbuttonimagesetting" />
                                                            <?php
                                                        }
                                                    }
                                                    ?>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text "><?php echo addslashes(esc_html__('Background Hover Image', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_right">
                                                <div class="arf_imageloader arf_form_style_file_upload_loader" id="ajax_submit_hover_loader"></div>
                                                <div id="submit_hover_btn_img_div" <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') { ?> class="iframe_submit_hover_original_btn" data-id="arfsbhis" style="margin-right:5px; position: relative; overflow: hidden; cursor:pointer; max-width:130px; height:27px; background: #1BBAE1; font-weight:bold; <?php if ($newarr['submit_hover_bg_img'] == '') { ?> background:#1BBAE1;padding:7px 10px 0 10px;font-size:13px;border-radius:3px;-webkit-border-radius:3px;-o-border-radius:3px;-moz-border-radius:3px;color:#FFFFFF;border:1px solid #CCCCCC;box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-webkit-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-o-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-moz-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);display: inline-block; <?php } ?>" <?php } else { ?> style="margin-left:0px;" <?php } ?>>
                                                    <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' && $newarr['submit_hover_bg_img'] == '') { ?> <span class="arf_form_style_file_upload_icon">
                                                        <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg></span> <?php } ?>
                                                    <?php
                                                    if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') {
                                                        if ($newarr['submit_hover_bg_img'] != '') {
                                                            ?>
                                                            <img src="<?php echo $newarr['submit_hover_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_submit_hover_bg_img();" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                            <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="<?php echo esc_attr($newarr['submit_hover_bg_img']) ?>" id="arfsubmithoverbuttonimagesetting" />
                                                        <?php } else { ?>
                                                            <input type="text" class="original" name="submit_hover_btn_img" id="field_arfsbhis" data-form-id="" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; width: 160px; height: 59px; left: -999px; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                            <input type="hidden" id="type_arfsbhis" name="type_arfsbhis" value="1" >
                                                            <input type="hidden" value="jpg, jpeg, jpe, gif, png, bmp, tif, tiff, ico" id="file_types_arfsbhis" name="field_types_arfsbhis" />

                                                            <input type="hidden" name="imagename_submit_hover" id="imagename_submit_hover" value="" />
                                                            <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="" id="arfsubmithoverbuttonimagesetting" />
                                                            <?php
                                                        }
                                                        echo '<div id="arfsbhis_iframe_div"><iframe style="display:none;" id="arfsbhis_iframe" src="' . ARFURL . '/core/views/iframe.php" ></iframe></div>';
                                                    } else {
                                                        if ($newarr['submit_hover_bg_img'] != '') {
                                                            ?>
                                                            <img src="<?php echo $newarr['submit_hover_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('button_hover_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                            <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="<?php echo esc_attr($newarr['submit_hover_bg_img']) ?>" id="arfsubmithoverbuttonimagesetting" />
                                                        <?php } else { ?>
                                                            <div class="arfajaxfileupload">
                                                                <div class="arf_form_style_file_upload_icon">
                                                                    <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg>
                                                                </div>
                                                                <input type="file" name="submit_hover_btn_img" data-val="submit_hover_bg" id="submit_hover_btn_img" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                            </div>

                                                            <input type="hidden" name="imagename_submit_hover" id="imagename_submit_hover" value="" />
                                                            <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="" id="arfsubmithoverbuttonimagesetting" />
                                                            <?php
                                                        }
                                                    }
                                                    ?>

                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="arf_accordion_container_row arf_half_width" style="height: 70px;">
                                            <div class="arf_accordion_container_inner_div">
                                                <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Font Settings','ARForms')); ?></div>
                                            </div>
                                            <div class="arf_accordion_container_inner_div">
                                                <div class="arf_custom_font" data-id="arf_submit_font_settings">
                                                    <div class="arf_custom_font_icon">
                                                        <svg viewBox="-10 -10 35 35">
                                                        <g id="paint_brush">
                                                        <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M7.423,14.117c1.076,0,2.093,0.022,3.052,0.068v-0.82c-0.942-0.078-1.457-0.146-1.542-0.205  c-0.124-0.092-0.203-0.354-0.235-0.787s-0.049-1.601-0.049-3.504l0.059-6.568c0-0.299,0.013-0.472,0.039-0.518  C8.772,1.744,8.85,1.725,8.981,1.725c1.549,0,2.584,0.043,3.105,0.128c0.162,0.026,0.267,0.076,0.313,0.148  c0.059,0.092,0.117,0.687,0.176,1.784h0.811c0.052-1.201,0.14-2.249,0.264-3.145l-0.107-0.156c-2.396,0.098-4.561,0.146-6.494,0.146  c-1.94,0-3.936-0.049-5.986-0.146L0.954,0.563c0.078,0.901,0.11,1.976,0.098,3.223h0.84c0.085-1.062,0.141-1.633,0.166-1.714  C2.083,1.99,2.121,1.933,2.17,1.9c0.049-0.032,0.262-0.065,0.641-0.098c0.652-0.052,1.433-0.078,2.34-0.078  c0.443,0,0.674,0.024,0.69,0.073c0.016,0.049,0.024,1.364,0.024,3.947c0,1.313-0.01,2.602-0.029,3.863  c-0.033,1.776-0.072,2.804-0.117,3.084c-0.039,0.201-0.098,0.34-0.176,0.414c-0.078,0.075-0.212,0.129-0.4,0.161  c-0.404,0.065-0.791,0.098-1.162,0.098v0.82C4.861,14.14,6.008,14.117,7.423,14.117L7.423,14.117z"></path>
                                                        </g></svg>
                                                    </div>
                                                    <div class="arf_custom_font_label"><?php echo addslashes(esc_html__('Advanced font options','ARForms')); ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Border Options', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="margin-left: -5px;">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" name="arfsbbws" id="arfsubmitbuttonborderwidhtsetting" style="width:142px;" value="<?php echo esc_attr($newarr['arfsubmitborderwidthsetting']) ?>" class="txtxbox_widget" size="4" />&nbsp;<span class="arf_px">px</span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <div id="arf_submitbuttonborderwidhtsetting" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div>
                                                        <input id="arfsubmitbuttonborderwidhtsetting_exs" class="arf_slider_input" data-slider-id='arfsubmitbuttonborderwidhtsetting_exsSlider' type="text" data-slider-value="<?php echo esc_attr($newarr['arfsubmitborderwidthsetting']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;">0 px</div>
                                                            <div class="arf_px" style="float:right;">20 px</div>
                                                        </div>

                                                        <input type="hidden" name="arfsbbws" id="arfsubmitbuttonborderwidhtsetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~border-width","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~border-width","material_outlined":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~border-width"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_border_width" style="width:100px;" value="<?php echo esc_attr($newarr['arfsubmitborderwidthsetting']) ?>" class="txtxbox_widget" size="4" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="margin-left: -5px;">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Radius', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" value="<?php echo esc_attr($newarr['arfsubmitborderradiussetting']) ?>" name="arfsbbrs" id="arfsubmitbuttonborderradiussetting" class="txtxbox_widget" size="4" style="width:142px;" />&nbsp;<span class="arf_px">px</span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <div id="arf_submitbuttonborderradiussetting" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div>
                                                        <input id="arfsubmitbuttonborderradiussetting_exs" class="arf_slider_input" data-slider-id='arfsubmitbuttonborderradiussetting_exsSlider' type="text" data-slider-min="0" data-slider-max="50" data-slider-step="1" data-slider-value="<?php echo esc_attr($newarr['arfsubmitborderradiussetting']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;">0 px</div>
                                                            <div class="arf_px" style="float:right;">50 px</div>
                                                        </div>

                                                        <input type="hidden" value="<?php echo esc_attr($newarr['arfsubmitborderradiussetting']) ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~border-radius","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~border-radius","material_outlined":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~border-radius"}' name="arfsbbrs" id="arfsubmitbuttonborderradiussetting" class="txtxbox_widget"  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_border_radius" size="4" style="width:100px;" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        

                                <?php

                                    $formbuttonxoffset = (isset($newarr['arfsubmitboxxoffsetsetting']) && $newarr['arfsubmitboxxoffsetsetting'] != '') ? esc_attr($newarr['arfsubmitboxxoffsetsetting']) : 1;
                                    $formbuttonyoffset = (isset($newarr['arfsubmitboxyoffsetsetting']) && $newarr['arfsubmitboxyoffsetsetting'] != '') ? esc_attr($newarr['arfsubmitboxyoffsetsetting']) : 2;
                                    $formbuttonblur = (isset($newarr['arfsubmitboxblursetting']) && $newarr['arfsubmitboxblursetting'] != '') ? esc_attr($newarr['arfsubmitboxblursetting']) : 3;
                                    $formbuttonspread = (isset($newarr['arfsubmitboxshadowsetting']) && $newarr['arfsubmitboxshadowsetting'] != '') ? esc_attr($newarr['arfsubmitboxshadowsetting']) : 0;
                                ?>    
                                <div class="arf_accordion_container_row_separator"></div>
                                    <div class="arf_accordion_container_row arf_padding">
                                        <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Shadow Options', 'ARForms')); ?></div>
                                    </div>
                                    <div class="arf_accordion_container_row arf_half_width" style="margin-left: -5px;">
                                        <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('X-offset', 'ARForms')); ?></div>
                                        <div class="arf_accordion_content_container arf_align_center">
                                            <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                <div class="arf_float_right">
                                                    <input type="text" name="arfsbxos" id="arfsubmitbuttonxoffsetsetting" style="width:142px;" value="<?php echo esc_attr($formbuttonxoffset) ?>" class="txtxbox_widget" size="4" />&nbsp;<span class="arf_px">px</span>
                                                </div>
                                            <?php } else { ?>
                                                <div class="arf_slider_wrapper">
                                                    <div id="arf_submitbuttonxoffsetsetting" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div>
                                                    <input id="arfsubmitbuttonxoffsetsetting_exs" class="arf_slider_input" data-slider-id='arfsubmitbuttonxoffsetsetting_exsSlider' type="text" data-slider-min="-50" data-slider-max="50" data-slider-step="1" data-slider-value="<?php echo esc_attr($formbuttonxoffset) ?>" />
                                                    <div class="arf_slider_unit_data">
                                                        <div class="arf_px" style="float:left;">-50 px</div>
                                                        <div class="arf_px" style="float:right;">50 px</div>
                                                    </div>

                                                    <input type="hidden" name="arfsbxos" id="arfsubmitbuttonxoffsetsetting"  
                                                   style="width:100px;" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat~|~box-shadow"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_box_shadow" value="<?php echo esc_attr($formbuttonxoffset) ?>" class="txtxbox_widget" size="4" />
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="arf_accordion_container_row arf_half_width" style="margin-left: -5px;">
                                        <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Y-offset', 'ARForms')); ?></div>
                                        <div class="arf_accordion_content_container arf_align_center">
                                            <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                <div class="arf_float_right">
                                                    <input type="text" value="<?php echo esc_attr($formbuttonyoffset) ?>" name="arfsbyos" id="arfsubmitbuttonyoffsetsetting" class="txtxbox_widget" size="4" style="width:142px;" onchange="arf_extract_property_class_from_string()" />&nbsp;<span class="arf_px">px</span>
                                                </div>
                                            <?php } else { ?>
                                                <div class="arf_slider_wrapper">
                                                    <div id="arf_submitbuttonyoffsetsetting" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div>
                                                    <input id="arfsubmitbuttonyoffsetsetting_exs" class="arf_slider_input" data-slider-id='arfsubmitbuttonyoffsetsetting_exsSlider' type="text" data-slider-min="-50" data-slider-max="50" data-slider-step="1" data-slider-value="<?php echo esc_attr($formbuttonyoffset) ?>" />
                                                    <div class="arf_slider_unit_data">
                                                        <div class="arf_px" style="float:left;">-50 px</div>
                                                        <div class="arf_px" style="float:right;">50 px</div>
                                                    </div>

                                                    <input type="hidden" value="<?php echo esc_attr($formbuttonyoffset) ?>" 
                                                    name="arfsbyos" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat~|~box-shadow"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_box_shadow" id="arfsubmitbuttonyoffsetsetting" class="txtxbox_widget"   size="4" style="width:100px;" />
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="margin-left: -5px;">
                                        <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Blur', 'ARForms')); ?></div>
                                        <div class="arf_accordion_content_container arf_align_center">
                                            <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                <div class="arf_float_right">
                                                    <input type="text" value="<?php echo esc_attr($formbuttonblur) ?>" name="arfsbbs" id="arfsubmitbuttonblursetting" class="txtxbox_widget" size="4" style="width:142px;" onchange="arf_extract_property_class_from_string()"/>&nbsp;<span class="arf_px">px</span>
                                                </div>
                                            <?php } else { ?>
                                                <div class="arf_slider_wrapper">
                                                    <div id="arf_submitbuttonblursetting" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div>
                                                    <input id="arfsubmitbuttonblursetting_exs" class="arf_slider_input" data-slider-id='arfsubmitbuttonblursetting_exsSlider' type="text" data-slider-min="0" data-slider-max="50" data-slider-step="1" data-slider-value="<?php echo esc_attr($formbuttonblur) ?>" />
                                                    <div class="arf_slider_unit_data">
                                                        <div class="arf_px" style="float:left;">0 px</div>
                                                        <div class="arf_px" style="float:right;">50 px</div>
                                                    </div>

                                                    <input type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat~|~box-shadow"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_box_shadow" value="<?php echo esc_attr($formbuttonblur) ?>" name="arfsbbs" id="arfsubmitbuttonblursetting" class="txtxbox_widget"  size="4" style="width:100px;" />
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="arf_accordion_container_row arf_half_width" style="margin-left: -5px;">
                                        <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Spread', 'ARForms')); ?></div>
                                        <div class="arf_accordion_content_container arf_align_center">
                                            <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                <div class="arf_float_right">
                                                    <input type="text" name="arfsbsps" id="arfsubmitbuttonshadowsetting" style="width:142px;" value="<?php echo esc_attr($formbuttonspread) ?>" class="txtxbox_widget" size="4" onchange="arf_extract_property_class_from_string()" />&nbsp;<span class="arf_px">px</span>
                                                </div>
                                            <?php } else { ?>
                                                <div class="arf_slider_wrapper">
                                                     <div id="arf_submitbuttonshadowsetting" class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div>
                                                    <input id="arfsubmitbuttonshadowsetting_exs" class="arf_slider_input" data-slider-id='arfsubmitbuttonshadowsetting_exsSlider' type="text" data-slider-min="0" data-slider-max="20" data-slider-step="1" data-slider-value="<?php echo esc_attr($formbuttonspread) ?>" />
                                                    <div class="arf_slider_unit_data">
                                                        <div class="arf_px" style="float:left;">0 px</div>
                                                        <div class="arf_px" style="float:right;">20 px</div>
                                                    </div>

                                                    <input type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat~|~box-shadow"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_box_shadow" name="arfsbsps" id="arfsubmitbuttonshadowsetting" style="width:100px;" value="<?php echo esc_attr($formbuttonspread) ?>" class="txtxbox_widget" size="4" />
                                                </div>
                                               
                                            <?php } ?>
                                        </div>
                                    </div>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="arf_form_style_tab_container" id="arf_form_custom_css">
                    <div class="arf_form_custom_css_tab">
                        <?php
                            global $custom_css_array;
                        ?>
                        <div class="arf_custom_css_cloud_wrapper">
                            <span><?php echo addslashes(esc_html__('Add CSS Elements','ARForms')) ?></span>
                            <i class="fas fa-caret-down"></i>
                            <ul class="arf_custom_css_cloud_list_wrapper">
                            <?php
                                foreach($custom_css_array as $key => $value ){
                                    ?>
                                    <li class="arf_custom_css_cloud_list_item <?php echo (isset($values[$key]) && $values[$key] != '') ? 'arfactive' : ''; ?>" id="<?php echo $value['onclick_1']; ?>"><span><?php echo $value['label_title']; ?></span></li>
                                    <?php
                                }
                            ?>
                            </ul>
                        </div>
                        <div id="arf_expand_css_code" class="arf_expand_css_code_button">
                            <svg width="40px" height="40px" viewBox="-10 -12 39 39">
                                <path fill="#ffffff" d="M18.08,6.598l-1.29,1.289l-0.009-0.009l-4.719,4.72l-1.289-1.29  l4.719-4.719L10.773,1.87l1.289-1.29l4.719,4.719l0.009-0.008l1.29,1.289l-0.009,0.009L18.08,6.598z M7.035,12.598l-4.72-4.72  L2.306,7.887L1.017,6.598l0.009-0.009L1.017,6.58l1.289-1.289l0.009,0.008l4.72-4.719l1.289,1.29L3.605,6.589l4.719,4.719  L7.035,12.598z">
                            </svg>
                        </div>
                        
                        
                        <div class="arf_form_other_css_wrapper">
                            <textarea id="arf_form_other_css" name="options[arf_form_other_css]" cols="50" rows="4" class="arf_other_css_textarea"><?php echo isset($form_opts['arf_form_other_css']) ? stripslashes_deep($form_opts['arf_form_other_css']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                <!-- Custom Color Popup  -->
                <div class="arf_custom_color_popup">
                    <?php
                    $bgColor = (isset($newarr['arfmainformbgcolorsetting']) && $newarr['arfmainformbgcolorsetting'] != '' ) ? esc_attr($newarr['arfmainformbgcolorsetting']) : $skinJson->skins->$active_skin->form->background;
                    $bgColor = (substr($bgColor, 0, 1) != '#') ? '#' . $bgColor : $bgColor;

                    $frmTitleColor = (isset($newarr['arfmainformtitlecolorsetting']) && $newarr['arfmainformtitlecolorsetting'] != '') ? esc_attr($newarr['arfmainformtitlecolorsetting']) : $skinJson->skins->$active_skin->form->title;
                    $frmTitleColor = (substr($frmTitleColor, 0, 1) != '#') ? '#' . $frmTitleColor : $frmTitleColor;
                    
                    $formBrdColor = (isset($newarr['arfmainfieldsetcolor']) && $newarr['arfmainfieldsetcolor'] != "") ? esc_attr($newarr['arfmainfieldsetcolor']) : $skinJson->skins->$active_skin->form->border;
                    $formBrdColor = (substr($formBrdColor, 0, 1) != '#') ? '#' . $formBrdColor : $formBrdColor;

                    $inputBaseColor = (isset($newarr['arfmainbasecolor']) && $newarr['arfmainbasecolor'] != "" ) ? esc_attr($newarr['arfmainbasecolor']) : $skinJson->skins->$active_skin->main;

                    $inputBaseColor = (substr($inputBaseColor,0,1) != '#') ? '#'.$inputBaseColor : $inputBaseColor;

                    
                    $formShadowColor = (isset($newarr['arfmainformbordershadowcolorsetting']) && $newarr['arfmainformbordershadowcolorsetting'] != '') ? esc_attr($newarr['arfmainformbordershadowcolorsetting']) : $skinJson->skins->$active_skin->form->shadow;
                    $formShadowColor = (substr($formShadowColor, 0, 1) != '#') ? '#' . $formShadowColor : $formShadowColor;
                    
                    $formSectionColor = (isset($newarr['arfformsectionbackgroundcolor']) && $newarr['arfformsectionbackgroundcolor'] != '') ? esc_attr($newarr['arfformsectionbackgroundcolor']) : $skinJson->skins->$active_skin->form->section_background;

                    $activePgColor = (isset($newarr['bg_color_pg_break']) && $newarr['bg_color_pg_break']) ? esc_attr($newarr['bg_color_pg_break']) : $skinJson->skins->$active_skin->pagebreak->active_tab;
                    $activePgColor = (substr($activePgColor, 0, 1) != '#') ? '#' . $activePgColor : $activePgColor;

                    $inactivePgColor = (isset($newarr['bg_inavtive_color_pg_break']) && $newarr['bg_inavtive_color_pg_break'] != '' ) ? esc_attr($newarr['bg_inavtive_color_pg_break']) : $skinJson->skins->$active_skin->pagebreak->inactive_tab;
                    $inactivePgColor = (substr($inactivePgColor, 0, 1) != '#') ? '#' . $inactivePgColor : $inactivePgColor;
                    
                    $PgTextColor = ( isset($newarr['text_color_pg_break']) && $newarr['text_color_pg_break'] != '' ) ? esc_attr($newarr['text_color_pg_break']) : $skinJson->skins->$active_skin->pagebreak->text;
                    $PgTextColor = (substr($PgTextColor, 0, 1) != '#') ? '#' . $PgTextColor : $PgTextColor;

                    $PgStyle3TextColor = ( isset($newarr['text_color_pg_break_style3']) && $newarr['text_color_pg_break_style3'] != '' ) ? esc_attr($newarr['text_color_pg_break_style3']) : $skinJson->skins->$active_skin->pagebreak->style3_text;
                    $PgStyle3TextColor = (substr($PgStyle3TextColor, 0, 1) != '#') ? '#' . $PgStyle3TextColor : $PgStyle3TextColor;
                    
                    $labelColor = (isset($newarr['label_color']) && $newarr['label_color'] != '' ) ? esc_attr($newarr['label_color']) : $skinJson->skins->$active_skin->label->text;
                    $labelColor = (substr($labelColor, 0, 1) != '#') ? '#' . $labelColor : $labelColor;
                    
                    $inputTxtColor = ( isset($newarr['text_color']) && $newarr['text_color'] != '' ) ? esc_attr($newarr['text_color']) : $skinJson->skins->$active_skin->input->text;
                    $inputTxtColor = (substr($inputTxtColor, 0, 1) != '#') ? '#' . $inputTxtColor : $inputTxtColor;
                    
                    $iconBgColor = ( isset($newarr['prefix_suffix_bg_color']) && $newarr['prefix_suffix_bg_color'] != '' ) ? esc_attr($newarr['prefix_suffix_bg_color']) : $skinJson->skins->$active_skin->input->prefix_suffix_background;
                    $iconBgColor = (substr($iconBgColor, 0, 1) != '#') ? '#' . $iconBgColor : $iconBgColor;
                    
                    $iconColor = (isset($newarr['prefix_suffix_icon_color']) && $newarr['prefix_suffix_icon_color'] != '' ) ? esc_attr($newarr['prefix_suffix_icon_color']) : $skinJson->skins->$active_skin->input->prefix_suffix_icon_color;
                    $iconColor = (substr($iconColor, 0, 1) != '#') ? '#' . $iconColor : $iconColor;
                    
                    $inputBg = (isset($newarr['bg_color']) && $newarr['bg_color'] != '') ? esc_attr($newarr['bg_color']) : $skinJson->skins->$active_skin->input->background;
                    $inputBg = (substr($inputBg, 0, 1) != '#') ? '#' . $inputBg : $inputBg;
                    
                    $inputActiveBg = ( isset($newarr['arfbgactivecolorsetting']) && $newarr['arfbgactivecolorsetting'] != '' ) ? esc_attr($newarr['arfbgactivecolorsetting']) : $skinJson->skins->$active_skin->input->background_active;
                    $inputActiveBg = (substr($inputActiveBg, 0, 1) != '#') ? '#' . $inputActiveBg : $inputActiveBg;
                    
                    $inputErrorBg = ( isset($newarr['arferrorbgcolorsetting']) && $newarr['arferrorbgcolorsetting'] != '' ) ? esc_attr($newarr['arferrorbgcolorsetting']) : $skinJson->skins->$active_skin->input->background_error;
                    $inputErrorBg = (substr($inputErrorBg, 0, 1) != '#') ? '#' . $inputErrorBg : $inputErrorBg;
                    
                    $inputBrdColor = ( isset($newarr['border_color']) && $newarr['border_color'] != '' ) ? esc_attr($newarr['border_color']) : $skinJson->skins->$active_skin->input->border;
                    $inputBrdColor = (substr($inputBrdColor, 0, 1) != '#') ? '#' . $inputBrdColor : $inputBrdColor;
                    
                    $inputActiveBrd = (isset($newarr['arfborderactivecolorsetting']) && $newarr['arfborderactivecolorsetting'] != '' ) ? esc_attr($newarr['arfborderactivecolorsetting']) : $skinJson->skins->$active_skin->input->border_active;
                    $inputActiveBrd = (substr($inputActiveBrd, 0, 1) != '#') ? '#' . $inputActiveBrd : $inputActiveBrd;
                    
                    $inputErrorBrd = (isset($newarr['arferrorbordercolorsetting']) && $newarr['arferrorbordercolorsetting'] != '' ) ? esc_attr($newarr['arferrorbordercolorsetting']) : $skinJson->skins->$active_skin->input->border_error;
                    $inputErrorBrd = (substr($inputErrorBrd, 0, 1) != '#') ? '#' . $inputErrorBrd : $inputErrorBrd;
                    
                    $submitTxtColor = (isset($newarr['arfsubmittextcolorsetting']) && $newarr['arfsubmittextcolorsetting'] != '' ) ? esc_attr($newarr['arfsubmittextcolorsetting']) : $skinJson->skins->$active_skin->input->text;
                    $submitTxtColor = (substr($submitTxtColor, 0, 1) != '#') ? '#' . $submitTxtColor : $submitTxtColor;
                    
                    $submitBgColor = (isset($newarr['submit_bg_color']) && $newarr['submit_bg_color'] != '' ) ? esc_attr($newarr['submit_bg_color']) : $skinJson->skins->$active_skin->submit->background;
                    $submitBgColor = (substr($submitBgColor, 0, 1) != '#') ? '#' . $submitBgColor : $submitBgColor;
                    
                    $submitHoverBg = (isset($newarr['arfsubmitbuttonbgcolorhoversetting']) && $newarr['arfsubmitbuttonbgcolorhoversetting'] != '' ) ? esc_attr($newarr['arfsubmitbuttonbgcolorhoversetting']) : $skinJson->skins->$active_skin->submit->background_hover;
                    $submitHoverBg = (substr($submitHoverBg, 0, 1) != '#') ? '#' . $submitHoverBg : $submitHoverBg;
                    
                    $submitBrdColor = isset($newarr['arfsubmitbordercolorsetting']) ? esc_attr($newarr['arfsubmitbordercolorsetting']) : $skinJson->skins->$active_skin->submit->border;
                    $submitBrdColor = (substr($submitBrdColor, 0, 1) != '#') ? '#' . $submitBrdColor : $submitBrdColor;
                    
                    $submitShadowColor = ( isset($newarr['arfsubmitshadowcolorsetting']) && $newarr['arfsubmitshadowcolorsetting'] != '' ) ? esc_attr($newarr['arfsubmitshadowcolorsetting']) : $skinJson->skins->$active_skin->submit->shadow;
                    $submitShadowColor = (substr($submitShadowColor, 0, 1) != '#') ? '#' . $submitShadowColor : $submitShadowColor;
                    
                    $successBgColor = ( isset($newarr['arfsucessbgcolorsetting']) && $newarr['arfsucessbgcolorsetting'] != '' ) ? esc_attr($newarr['arfsucessbgcolorsetting']) : $skinJson->skins->$active_skin->success_msg->background;
                    $successBgColor = (substr($successBgColor, 0, 1) != '#') ? '#' . $successBgColor : $successBgColor;
                    
                    $successBrdColor = (isset($newarr['arfsucessbordercolorsetting']) && $newarr['arfsucessbordercolorsetting'] != '') ? esc_attr($newarr['arfsucessbordercolorsetting']) : $skinJson->skins->$active_skin->success_msg->border;
                    $successBrdColor = (substr($successBrdColor, 0, 1) != '#') ? '#' . $successBrdColor : $successBrdColor;
                    
                    $successTxtColor = ( isset($newarr['arfsucesstextcolorsetting']) && $newarr['arfsucesstextcolorsetting'] != '' ) ? esc_attr($newarr['arfsucesstextcolorsetting']) : $skinJson->skins->$active_skin->success_msg->text;
                    $successTxtColor = (substr($successTxtColor, 0, 1) != '#') ? '#' . $successTxtColor : $successTxtColor;

                    $errorBgColor = ( isset($newarr['arfformerrorbgcolorsettings']) && $newarr['arfformerrorbgcolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbgcolorsettings']) : $skinJson->skins->$active_skin->error_msg->background;
                    $errorBgColor = (substr($errorBgColor,0,1) != '#') ? '#' . $errorBgColor : $errorBgColor;

                    $errorBrdColor = ( isset($newarr['arfformerrorbordercolorsettings']) && $newarr['arfformerrorbordercolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbordercolorsettings']) : $skinJson->skins->$active_skin->error_msg->border;
                    $errorBrdColor = (substr($errorBrdColor,0,1) != '#') ? '#' . $errorBrdColor : $errorBrdColor;

                    $errorTxtColor = ( isset($newarr['arfformerrortextcolorsettings']) && $newarr['arfformerrortextcolorsettings'] != '') ? esc_attr($newarr['arfformerrortextcolorsettings']) : $skinJson->skins->$active_skin->error_msg->text;
                    $errorTxtColor = (substr($errorTxtColor,0,1) != '#') ? '#' . $errorTxtColor : $errorTxtColor;

                    
                    $checkboxColor = ( isset($newarr['checked_checkbox_icon_color']) && $newarr['checked_checkbox_icon_color'] != '' ) ? esc_attr($newarr['checked_checkbox_icon_color']) : $skinJson->skins->$active_skin->input->checkbox_icon_color;
                    $checkboxColor = (substr($checkboxColor, 0, 1) != '#') ? '#' . $checkboxColor : $checkboxColor;
                    
                    $radioColor = ( isset($newarr['checked_radio_icon_color']) && $newarr['checked_radio_icon_color'] != '' ) ? esc_attr($newarr['checked_radio_icon_color']) : $skinJson->skins->$active_skin->input->radio_icon_color;
                    $radioColor = (substr($radioColor, 0, 1) != '#') ? '#' . $radioColor : $radioColor;
                    
                    $surveyBarColor = ( isset($newarr['bar_color_survey']) && $newarr['bar_color_survey'] != '' ) ? esc_attr($newarr['bar_color_survey']) : $skinJson->skins->$active_skin->survey->bar_color;
                    $surveyBarColor = (substr($surveyBarColor, 0, 1) != '#') ? '#' . $surveyBarColor : $surveyBarColor;
                    
                    $surveyBgColor = ( isset($newarr['bg_color_survey']) && $newarr['bg_color_survey'] != '' ) ? esc_attr($newarr['bg_color_survey']) : $skinJson->skins->$active_skin->survey->background;
                    $surveyBgColor = (substr($surveyBgColor, 0, 1) != '#') ? '#' . $surveyBgColor : $surveyBgColor;
                    
                    $surveyTxtColor = ( isset($newarr['text_color_survey']) && $newarr['text_color_survey'] != '' ) ? esc_attr($newarr['text_color_survey']) : $skinJson->skins->$active_skin->survey->text;
                    $surveyTxtColor = (substr($surveyTxtColor, 0, 1) != '#' ) ? '#' . $surveyTxtColor : $surveyTxtColor;

                    //timerrelated changes

                    $timerBgcolor = ( isset($newarr['timer_bg_color']) && $newarr['timer_bg_color'] != '' ) ? esc_attr($newarr['timer_bg_color']) : $skinJson->skins->$active_skin->pagebreaktimer->circle_bg_color;
                    $timerBgcolor = (substr($timerBgcolor, 0, 1) != '#') ? '#' . $timerBgcolor : $timerBgcolor;
                    
                    $timerForgroundcolor = ( isset($newarr['timer_forground_color']) && $newarr['timer_forground_color'] != '' ) ? esc_attr($newarr['timer_forground_color']) : $skinJson->skins->$active_skin->pagebreaktimer->circle_forground_color;
                    $timerForgroundcolor = (substr($timerForgroundcolor, 0, 1) != '#' ) ? '#' . $timerForgroundcolor : $timerForgroundcolor;

                    
                    $validationBgColor = ( isset($newarr['arfvalidationbgcolorsetting']) && $newarr['arfvalidationbgcolorsetting'] != '' ) ? esc_attr($newarr['arfvalidationbgcolorsetting']) : (($active_skin != 'custom') ? $skinJson->skins->$active_skin->validation_msg->background : '');
                    $validationBgColor = (substr($validationBgColor, 0, 1) != '#') ? '#' . $validationBgColor : $validationBgColor;
                    
                    $validationTxtColor = ( isset($newarr['arfvalidationtextcolorsetting']) && $newarr['arfvalidationtextcolorsetting'] != '' ) ? esc_attr($newarr['arfvalidationtextcolorsetting']) : (($active_skin != 'custom') ? $skinJson->skins->$active_skin->validation_msg->text : '');
                    $validationTxtColor = (substr($validationTxtColor, 0, 1) != '#') ? '#' . $validationTxtColor : $validationTxtColor;
                    
                    $datepickerBgColor = ( isset($newarr['arfdatepickerbgcolorsetting']) && $newarr['arfdatepickerbgcolorsetting'] != '' ) ? esc_attr($newarr['arfdatepickerbgcolorsetting']) : $skinJson->skins->$active_skin->datepicker->background;
                    $datepickerBgColor = (substr($datepickerBgColor, 0, 1) != '#') ? '#' . $datepickerBgColor : $datepickerBgColor;
                    
                    $datepickerTxtColor = ( isset($newarr['arfdatepickertextcolorsetting']) && $newarr['arfdatepickertextcolorsetting'] != '' ) ? esc_attr($newarr['arfdatepickertextcolorsetting']) : $skinJson->skins->$active_skin->datepicker->text;
                    $datepickerTxtColor = (substr($datepickerTxtColor, 0, 1) != '#') ? '#' . $datepickerTxtColor : $datepickerTxtColor;
                   
                    $uploadBtnTxtColor = ( isset($newarr['arfuploadbtntxtcolorsetting']) && $newarr['arfuploadbtntxtcolorsetting'] != '' ) ? esc_attr($newarr['arfuploadbtntxtcolorsetting']) : $skinJson->skins->$active_skin->uploadbutton->text;
                    $uploadBtnTxtColor = (substr($uploadBtnTxtColor, 0, 1) != '#') ? '#' . $uploadBtnTxtColor : $uploadBtnTxtColor;

                    $uploadBtnBgColor = ( isset($newarr['arfuploadbtnbgcolorsetting']) && $newarr['arfuploadbtnbgcolorsetting'] != '' ) ? esc_attr($newarr['arfuploadbtnbgcolorsetting']) : $skinJson->skins->$active_skin->uploadbutton->background;
                    $uploadBtnBgColor = (substr($uploadBtnBgColor, 0, 1) != '#') ? '#' . $uploadBtnBgColor : $uploadBtnBgColor;

                    $likeBtnColor = ( isset($newarr['arflikebtncolor']) && $newarr['arflikebtncolor'] != "" ) ? esc_attr($newarr['arflikebtncolor']) : $skinJson->skins->$active_skin->input->like_button;
                    $likeBtnColor = (substr($likeBtnColor,0,1) != "#") ? "#".$likeBtnColor : $likeBtnColor; 

                    $dislikeBtnColor = ( isset($newarr['arfdislikebtncolor']) && $newarr['arfdislikebtncolor'] != "" ) ? esc_attr($newarr['arfdislikebtncolor']) : $skinJson->skins->$active_skin->input->dislike_button;
                    $dislikeBtnColor = (substr($dislikeBtnColor,0,1) != "#") ? "#".$dislikeBtnColor : $dislikeBtnColor; 

                    $sliderLeftColor = ( isset($newarr['arfsliderselectioncolor']) && $newarr['arfsliderselectioncolor'] != "" ) ? esc_attr($newarr['arfsliderselectioncolor']) : $skinJson->skins->$active_skin->input->slider_selection_color;
                    $sliderLeftColor = (substr($sliderLeftColor,0,1) != "#") ? "#".$sliderLeftColor : $sliderLeftColor; 

                    $sliderRightColor = ( isset($newarr['arfslidertrackcolor']) && $newarr['arfslidertrackcolor'] != "" ) ? esc_attr($newarr['arfslidertrackcolor']) : $skinJson->skins->$active_skin->input->slider_track_color;
                    $sliderRightColor = (substr($sliderRightColor,0,1) != "#") ? "#".$sliderRightColor : $sliderRightColor;

                    $ratingColor = ( isset($newarr['arfstarratingcolor']) && $newarr['arfstarratingcolor'] != "" ) ? esc_attr($newarr['arfstarratingcolor']) : $skinJson->skins->$active_skin->input->rating_color;
                    $ratingColor = (substr($ratingColor,0,1) != "#") ? "#".$ratingColor : $ratingColor;

                    $allow_section_bg = isset($newarr['arf_divider_inherit_bg']) ? $newarr['arf_divider_inherit_bg'] : 0;
                    $allow_section_bg = isset($newarr['arf_section_inherit_bg']) ? $newarr['arf_section_inherit_bg'] : 0;
                    $allow_matrix_bg = isset( $newarr['arf_matrix_inherit_bg'] ) ? $newarr['arf_matrix_inherit_bg'] : 0;

                    ?>
                    <div class="arf_custom_color_popup_header"><?php echo addslashes(esc_html__('Custom Color', 'ARForms')) ?></div>
                    <div class="arf_custom_color_popup_container">
                        <div class="arf_custom_color_popup_table">
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="form_level_colors"><span><?php echo addslashes(esc_html__('Form', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    <div class="arf_custom_color_popup_right_item">

                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformbgcolorsetting" style="background:<?php echo str_replace('##', '#', $bgColor); ?>;" data-skin="form.background" data-default-color="<?php echo str_replace('##', '#', $bgColor); ?>" data-jscolor='{"hash":true,"valueElement":"arfformbgcolorsetting","onFineChange":"arf_update_color(this,\"arfformbgcolorsetting\")"}'></div>
                                        
                                        <input type="hidden" name="arffbcs" id="arfformbgcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $bgColor); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~background-color","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~background-color","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~background-color"}'  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_background_color" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformtitlecolor" style="background:<?php echo str_replace('##', '#', $frmTitleColor); ?>;" data-skin="form.title" data-default-color="<?php echo str_replace('##', '#', $frmTitleColor); ?>" data-jscolor='{"hash":true,"valueElement":"arfformtitlecolor","onFineChange":"arf_update_color(this,\"arfformtitlecolor\")"}'></div>
                                        <input type="hidden" name="arfftc" style="width:100px;" id="arfformtitlecolor" class="hex txtxbox_widget" data-arfstyle="true" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~color","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~color","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_title_color" value="<?php echo str_replace('##', '#', $frmTitleColor); ?>" /><?php echo addslashes(esc_html__('Form Title', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainfieldsetcolor" style="background:<?php echo str_replace('##', '#', $formBrdColor); ?>;" data-skin="form.border" data-default-color="<?php echo str_replace('##', '#', $formBrdColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfmainfieldsetcolor\")","valueElement":"arfmainfieldsetcolor"}'></div>
                                        <input type="hidden" name="arfmfsc" id="arfmainfieldsetcolor" class="hex txtxbox_widget" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-color","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-color","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_color" value="<?php echo str_replace('##', '#', $formBrdColor); ?>" style="width:100px;" /><?php echo addslashes(esc_html__('Border', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_popup_clear"></div>
                                    
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformbordershadowsetting" data-skin="form.shadow" style="background:<?php echo str_replace('##', '#', $formShadowColor); ?>;" data-default-color="<?php echo str_replace('##', '#', $formShadowColor); ?>" data-jscolor='{"hash":true,"valueElement":"arfformbordershadowsetting","onFineChange":"arf_update_color(this,\"arfformbordershadowsetting\")"}'></div>
                                        <input type="hidden" name="arffboss" id="arfformbordershadowsetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","property":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","property":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","property":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_type" class="hex txtxbox_widget" value="<?php echo str_replace('##', '#', $formShadowColor); ?>" style="width:100px;" /> <?php echo addslashes(esc_html__('Shadow', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_popup_clear"></div>

                                    <div class="arf_custom_color_popup_right_item" style="width: 60%;<?php echo (is_rtl()) ? 'margin-right:0px;margin-left:40px;' : 'margin-left:0px;margin-right:40px;'; ?>">
                                        <div class="arf_custom_checkbox_div">
                                            <div class="arf_custom_checkbox_wrapper">
                                                <input type="checkbox" value="1" <?php checked($allow_section_bg,1) ?> id="arf_section_inherit_bg" name="arf_section_inherit_bg"/>
                                                <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                </svg>
                                            </div>
                                        </div> 
                                        <label for="arf_section_inherit_bg" style="<?php echo (is_rtl()) ? 'float: right;text-align: right;margin-right: -3px;position: relative;' : 'float: left;text-align: left;margin-left: -3px;'; ?>margin-top: 3px;"><?php echo addslashes(esc_html__('Section Background','ARForms')); ?></label>
                                    </div>

                                    <div id="arf_allow_section_bg" class="arf_custom_color_popup_right_item <?php if( $allow_section_bg != 1 ){ echo 'arfdisablediv'; } ?>" style="width:15%;">
                                        <div class="arf_custom_color_popup_picker jscolor <?php if( $allow_section_bg != 1 ){ echo 'arfdisablediv'; } ?>" data-fid="arfformsectionbackgroundcolor" data-skin="form.section_background" style="background:<?php echo str_replace('##','#',$formSectionColor); ?>;" data-default-color="<?php echo str_replace('##', '#', $formSectionColor); ?>" data-jscolor='{"hash":true,"valueElement":"arfformsectionbackgroundcolor","onFineChange":"arf_update_color(this,\"arfformsectionbackgroundcolor\")"}' id="arf_allow_section_bg_inner"></div>
                                        <input type="hidden" name="arfsecbg" id="arfformsectionbackgroundcolor" class="hex txtxbox_widget" value="<?php echo str_replace('##', '#', $formSectionColor); ?>" style="width:100px;" />
                                    </div>

                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="input_colors"><span><?php echo addslashes(esc_html__('Main Input Colors', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainbasecolor" style="background:<?php echo str_replace("##","#",$inputBaseColor); ?>;" data-default-color="<?php echo str_replace("##","#",$inputBaseColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfmainbasecolor\")","valueElement":"arfmainbasecolor"}' data-skin="input.main"></div>
                                        <input type="hidden" name="arfmbsc" data-arfstyle="true" data-arfstyledata='<?php echo json_encode($skinJson->css_main_classes); ?>' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_main_style" value="<?php echo $inputBaseColor; ?>" id="arfmainbasecolor" class="txtxbox_widget hex" style="width:100%;" />
                                        <?php echo addslashes(esc_html__("Base/Active Color","ARForms")); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arftextcolorsetting" style="background:<?php echo str_replace('##', '#', $inputTxtColor); ?>;" data-skin="input.text" data-default-color="<?php echo str_replace('##', '#', $inputTxtColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arftextcolorsetting\")","valueElement":"arftextcolorsetting"}'></div>
                                        <input type="hidden" name="arftcs" id="arftextcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text)~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls .trumbowyg-box~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls .bootstrap-select .dropdown-toggle~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls .bootstrap-select .dropdown-toggle:focus~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls .bootstrap-select ul li a~|~color||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-webkit-input-placeholder~|~color||.wp-admin .allfields .controls .smaple-textarea::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .controls textarea::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=password]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=number]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=url]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=tel]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} select::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field):-moz-placeholder~|~color||.wp-admin .allfields .controls .smaple-textarea:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .controls textarea:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=password]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=number]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=url]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=tel]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} select:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-moz-placeholder~|~color||.wp-admin .allfields .controls .smaple-textarea::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .controls textarea::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=password]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=number]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=url]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=tel]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} select::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field):-ms-input-placeholder~|~color||.wp-admin .allfields .controls .smaple-textarea:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .controls textarea:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=password]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=number]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=url]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=tel]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} select:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-ms-input-placeholder~|~color||.wp-admin .allfields .controls .smaple-textarea::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .controls textarea::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=password]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=number]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=url]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=tel]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} select::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~color||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~color||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li.arm_sel_opt_checked::before~|~background||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li.arm_sel_opt_checked::after~|~background","material":".ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_autocomplete)~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=password]~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=email]~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=number]~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=url]~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=tel]~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=text].arf-select-dropdown~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls textarea~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-select-dropdown~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form ul.arf-select-dropdown li~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form select::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description):-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form select:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form select::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description):-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form select:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form select::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li.arm_sel_opt_checked::before~|~background||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li.arm_sel_opt_checked::after~|~background","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):not(.arf-selectpicker-input-control)~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container input[type=password]~|~color||.ar_main_div_{arf_form_id}  .arf_material_outline_form .arf_material_outline_container input[type=email]~|~color||.ar_main_div_{arf_form_id}  .arf_material_outline_form .arf_material_outline_container input[type=number]~|~color||.ar_main_div_{arf_form_id}  .arf_material_outline_form .arf_material_outline_container input[type=url]~|~color||.ar_main_div_{arf_form_id}  .arf_material_outline_form .arf_material_outline_container input[type=tel]~|~color||.ar_main_div_{arf_form_id}  .arf_material_outline_form .arf_material_outline_container input[type=text].arf-select-dropdown~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container textarea:not(.html_field_description):not(.g-recaptcha-response):not(.wp-editor-area)~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf-select-dropdown~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form ul.arf-select-dropdown li~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description)::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=email]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=password]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=number]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=url]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=tel]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form select::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description):-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=email]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=password]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=number]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=url]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=tel]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form select:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description)::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=email]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=password]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=number]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=url]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=tel]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form select::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description):-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=email]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=password]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=number]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=url]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=tel]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form select:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form textarea:not(.html_field_description)::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=email]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=password]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=number]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=url]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=tel]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form select::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li.arm_sel_opt_checked::before~|~background||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li.arm_sel_opt_checked::after~|~background"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_text_color" value="<?php echo str_replace('##', '#', $inputTxtColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_border_color" style="background:<?php echo str_replace('##', '#', $inputBrdColor); ?>;" data-skin="input.border" data-default-color="<?php echo str_replace('##', '#', $inputBrdColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"frm_border_color\")","valueElement":"frm_border_color"}'></div>
                                        <input type="hidden" name="arffmboc" id="frm_border_color" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $inputBrdColor); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-color||.ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-color||.ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-color||.ar_main_div_{arf_form_id} .controls input[type=password]~|~border-color||.ar_main_div_{arf_form_id} .controls input[type=email]~|~border-color||.ar_main_div_{arf_form_id} .controls .trumbowyg-box~|~border-color||.ar_main_div_{arf_form_id} .controls input[type=number]~|~border-color||.ar_main_div_{arf_form_id} .controls input[type=url]~|~border-color||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~border-color||.ar_main_div_{arf_form_id} .controls textarea~|~border-color||.ar_main_div_{arf_form_id} .controls select~|~border-color||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-color||.ar_main_div_{arf_form_id} input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider), .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor, .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_suffix_editor~|~border-color||.ar_main_div_{arf_form_id} .setting_checkbox.arf_standard_checkbox .arf_checkbox_input_wrapper input[type=checkbox]:not(:checked) + span~|~border-color||.ar_main_div_{arf_form_id} .setting_radio.arf_standard_radio .arf_radio_input_wrapper input[type=radio] + span~|~border-color||.ar_main_div_{arf_form_id} .controls .dropdown-toggle .arf_caret~|~border-top-color||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-color||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt i.arf-selectpicker-caret~|~border-top-color","material":".ar_main_div_{arf_form_id} .arf_materialize_form .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls input[type=password]~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls input[type=email]~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls input[type=number]~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls input[type=url]~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls textarea~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls select~|~border-color||.ar_main_div_{arf_form_id} .controls .arfdropdown-menu.open~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .controls textarea~|~border-bottom-color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider), .ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor, .ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_suffix_editor~|~border-color||.arf_form_outer_wrapper .setting_checkbox.arf_material_checkbox.arf_default_material .arf_checkbox_input_wrapper input[type=checkbox] + span::after~|~border-color||.arf_form_outer_wrapper .setting_checkbox.arf_material_checkbox.arf_advanced_material .arf_checkbox_input_wrapper input[type=checkbox] + span::before~|~border-color||.arf_form_outer_wrapper .setting_radio.arf_material_radio.arf_default_material .arf_radio_input_wrapper input[type=radio] + span::before~|~border-color||.arf_form_outer_wrapper .setting_radio.arf_material_radio.arf_advanced_material .arf_radio_input_wrapper input[type=radio] + span::before~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .select-wrapper .caret~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt i.arf-selectpicker-caret~|~border-top-color","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix~|~border-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_notch~|~border-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix~|~border-color||.ar_main_div_{arf_form_id} .arf_form_outer_wrapper .setting_checkbox.arf_material_checkbox.arf_default_material .arf_checkbox_input_wrapper input[type=checkbox] + span::after~|~border-color||.ar_main_div_{arf_form_id} .arf_form_outer_wrapper .setting_checkbox.arf_material_checkbox.arf_advanced_material .arf_checkbox_input_wrapper input[type=checkbox] + span::before~|~border-color||.ar_main_div_{arf_form_id} .arf_form_outer_wrapper .setting_radio.arf_material_radio.arf_default_material .arf_radio_input_wrapper input[type=radio] + span::before~|~border-color||.ar_main_div_{arf_form_id} .arf_form_outer_wrapper .setting_radio.arf_material_radio.arf_advanced_material .arf_radio_input_wrapper input[type=radio] + span::before~|~border-color||.ar_main_div_{arf_form_id} .ar_main_div_{arf_form_id} .arf_material_outline_form .select-wrapper .caret~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~border-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt i.arf-selectpicker-caret~|~border-top-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_color" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Border Color', 'ARForms')); ?>
                                    </div>
                                    
                                    <div class="arf_popup_clear"></div>

                                    <div class="arf_custom_color_popup_right_item <?php echo ($newarr['arfinputstyle'] == 'material_outlined') || ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>">
                                        <div class="arf_custom_color_popup_picker jscolor <?php echo ($newarr['arfinputstyle'] == 'material_outlined') || ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>" data-fid="frm_bg_color" style="background:<?php echo str_replace('##', '#', $inputBg); ?>;" data-skin="input.background" data-default-color="<?php echo str_replace('##', '#', $inputBg); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"frm_bg_color\")","valueElement":"frm_bg_color"}'></div>
                                        <input type="hidden" name="arffmbc" id="frm_bg_color" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $inputBg); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls .trumbowyg-editor~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls textarea~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls select~|~check_field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~check_field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu~|~check_field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle~|~check_field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt~|~background||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dd ul~|~background","material":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text)~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=password]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=email]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=number]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=url]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~background-color||.ar_main_div_{arf_form_id} .controls textarea~|~background-color||.ar_main_div_{arf_form_id} .controls select~|~background-color","material_outlined":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text)~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=password]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=email]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=number]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=url]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~background-color||.ar_main_div_{arf_form_id} .controls textarea~|~background-color||.ar_main_div_{arf_form_id} .controls select~|~background-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_bg_color" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item  <?php echo ($newarr['arfinputstyle'] == 'material_outlined') || ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>">
                                        <div class="arf_custom_color_popup_picker jscolor  <?php echo ($newarr['arfinputstyle'] == 'material_outlined') || ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>" data-fid="arfbgcoloractivesetting" style="background:<?php echo str_replace('##', '#', $inputActiveBg); ?>;" data-skin="input.background_active" data-default-color="<?php echo str_replace('##', '#', $inputActiveBg); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfbgcoloractivesetting\")","valueElement":"arfbgcoloractivesetting"}'></div>
                                        <input type="hidden" name="arfbcas" id="arfbgcoloractivesetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfmainformfield .controls input:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} textarea:focus:not(.arf_field_option_input_textarea)~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .trumbowyg-editor:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} input:focus:not(.inplace_field):not(.arf_autocomplete):not(.arfslider):not(.arf_field_option_input_text)~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=text]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=text]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=text]:focus:not(.inplace_field):not(.arf_autocomplete):not(.arfslider)~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .arfmainformfield .controls textarea:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .arfmainformfield .controls select:focus~|~check_field_focus_transparency","material":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=password]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=email]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=number]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=url]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=tel]:focus~|~background-color||.ar_main_div_{arf_form_id} .arfmainformfield .controls textarea:focus:not(.arf_field_option_input_textarea)~|~background-color||.ar_main_div_{arf_form_id} .arfmainformfield .controls select:focus~|~background-color","material_outlined":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=password]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=email]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=number]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=url]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=tel]:focus~|~background-color||.ar_main_div_{arf_form_id} .arfmainformfield .controls textarea:focus:not(.arf_field_option_input_textarea)~|~background-color||.ar_main_div_{arf_form_id} .arfmainformfield .controls select:focus~|~background-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_text_focus_bg_color" value="<?php echo str_replace('##', '#', $inputActiveBg); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Active State Background', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item <?php echo ($newarr['arfinputstyle'] == 'material_outlined') || ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>">
                                        <div class="arf_custom_color_popup_picker jscolor <?php echo ($newarr['arfinputstyle'] == 'material_outlined') || ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>" data-fid="arfbgerrorcolorsetting" style="background:<?php echo str_replace('##', '#', $inputErrorBg); ?>;" data-skin="input.background_error" data-default-color="<?php echo str_replace('##', '#', $inputErrorBg); ?>" data-jscolor='{"hash":true,"valueElement":"arfbgerrorcolorsetting","onFineChange":"arf_update_color(this,\"arfbgerrorcolorsetting\")"}'></div>
                                        <input type="hidden" name="arfbecs" id="arfbgerrorcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $inputErrorBg); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Error State Background', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_popup_clear"></div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arflabelcolorsetting" style="background:<?php echo str_replace('##', '#', $labelColor); ?>;" data-skin="label.text" data-default-color="<?php echo str_replace('##', '#', $labelColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arflabelcolorsetting\")","valueElement":"arflabelcolorsetting"}'></div>
                                        <input type="hidden" name="arflcs" id="arflabelcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .arf_field_description~|~color||.ar_main_div_{arf_form_id} .arf_checkbox_style label~|~color||.ar_main_div_{arf_form_id} .arf_radiobutton label~|~color||.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.month~|~color||.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.year:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_cal_body span.year~|~color||.ar_main_div_{arf_form_id} .arf_cal_body span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_cal_body td span.month~|~color||.ar_main_div_{arf_form_id} .datepicker .arf_cal_body .day:not(.old):not(.new)~|~color||.ar_main_div_{arf_form_id} .timepicker .timepicker-hour~|~color||.ar_main_div_{arf_form_id} .timepicker .timepicker-minute~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_hour~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_minute~|~color||.ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table th~|~color||.ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table td~|~color","material":".ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset label.arf_main_label~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .arf_field_description~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_checkbox_style label~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_radiobutton label~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_checkbox_style label~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_radiobutton label~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body td span.month~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body td span.month:hover~|~border-color||.ar_main_div_{arf_form_id} .datepicker .arf_cal_body .day:not(.old):not(.new)~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_hour~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_minute~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.month~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.year~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.decade:not(.disabled):hover~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.year:hover~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_matrix_field_control_wrapper table th~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_matrix_field_control_wrapper table td~|~color","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset label.arf_main_label~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form.arf_fieldset .arf_field_description~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_checkbox_style label~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_radiobutton label~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_checkbox_style label~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_radiobutton label~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_cal_body td span.month~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_cal_body td span.month:hover~|~border-color||.ar_main_div_{arf_form_id} .datepicker .arf_cal_body .day:not(.old):not(.new)~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_hour~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_minute~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_cal_body span.month~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_cal_body span.year~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_cal_body span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_cal_body span.decade:not(.disabled):hover~|~border-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_cal_body span.year:hover~|~border-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_matrix_field_control_wrapper table th~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_matrix_field_control_wrapper table td~|~color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_color" value="<?php echo str_replace('##', '#', $labelColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Label Text Color', 'ARForms')); ?>
                                    </div>
                                    
                                    <div class="arf_custom_color_popup_right_item <?php echo ($newarr['arfinputstyle'] == 'material' || $newarr['arfinputstyle'] == 'material_outlined' || $newarr['arfinputstyle'] == 'rounded') ? 'arfdisablediv' : '';?>">
                                        <div class="arf_custom_color_popup_picker jscolor <?php echo ($newarr['arfinputstyle'] == 'material' || $newarr['arfinputstyle'] == 'material_outlined' || $newarr['arfinputstyle'] == 'rounded') ? 'arfdisablediv' : '';?>" data-fid="prefix_suffix_bg_color" style="background:<?php echo str_replace('##', '#', $iconBgColor); ?>;" data-skin="input.prefix_suffix_background" data-default-color="<?php echo str_replace('##', '#', $iconBgColor); ?>" data-jscolor='{"hash":true,"valueElement":"prefix_suffix_bg_color","onFineChange":"arf_update_color(this,\"prefix_suffix_bg_color\")"}'></div>
                                        <input type="hidden" name="pfsfsbg" id="prefix_suffix_bg_color" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_standard_form .controls .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~background-color||.ar_main_div_{arf_form_id} .arf_standard_form .controls .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~background-color||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~background-color","material":".ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon:not(.arf_spin_prev_button)~|~background-color||.ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon:not(.arf_spin_next_button)~|~background-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_colorpicker_prefix_editor~|~background-color","material_outlined":".ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~background-color||.ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~background-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_colorpicker_prefix_editor~|~background-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_icon_bg_color" value="<?php echo str_replace('##', '#', $iconBgColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Icon Background', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item <?php echo ($newarr['arfinputstyle'] == 'material_outlined') ? 'arfdisablediv' : '';?>">
                                        <div class="arf_custom_color_popup_picker jscolor <?php echo ($newarr['arfinputstyle'] == 'material_outlined') ? 'arfdisablediv' : '';?>" data-fid="prefix_suffix_icon_color" style="background:<?php echo str_replace('##', '#', $iconColor); ?>;" data-skin="input.prefix_suffix_icon_color" data-default-color="<?php echo str_replace('##', '#', $iconColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"prefix_suffix_icon_color\")","valueElement":"prefix_suffix_icon_color"}'></div>
                                        <input type="hidden" name="pfsfscol" id="prefix_suffix_icon_color" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~color||.ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~color||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor svg path~|~fill","material":".ar_main_div_{arf_form_id} .arf_materialize_form .arf_material_theme_container .arf_leading_icon~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_material_theme_container .arf_trailing_icon~|~color","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_leading_icon~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_material_outline_container .arf_trailing_icon~|~color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_icon_color" value="<?php echo str_replace('##', '#', $iconColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Icon Color', 'ARForms')); ?>
                                    </div>


                                    <input type="hidden" name="cbscol" id="checked_checkbox_icon_color" class="txtxbox_widget hex" value="<?php echo isset($newarr['checked_checkbox_icon_color']) ? str_replace('##', '#', $newarr['checked_checkbox_icon_color']) : '' ?>" style="width:100px;" />
                                    <input type="hidden" name="rbscol" id="checked_radio_icon_color" class="txtxbox_widget hex" value="<?php echo isset($newarr['checked_radio_icon_color']) ? (str_replace('##', '#', $newarr['checked_radio_icon_color'])) : '' ?>" style="width:100px;" />
                                    <div class="arf_popup_clear"></div>

                                    
                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Like Button', 'ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="editor_like_button_color" style="background:<?php echo str_replace("##","#",$likeBtnColor); ?>;" data-skin="input.like_button" data-jscolor='{"hash":true,"valueElement":"editor_like_button_color","onFineChange":"arf_update_color(this,\"editor_like_button_color\")"}'></div>
                                        <input type="hidden" name="albclr" id="editor_like_button_color" class="txtxbox_widget" value="<?php echo str_replace("##","#",$likeBtnColor); ?>" style="width:100px" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_like_btn.active~|~background","material":".ar_main_div_{arf_form_id} .arf_like_btn.active~|~background","material_outlined":".ar_main_div_{arf_form_id} .arf_like_btn.active~|~background"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_like_button_color" /><?php echo addslashes(esc_html__('Like Button Color','ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="editor_dislike_button_color" style="background:<?php echo str_replace("##","#",$dislikeBtnColor); ?>;" data-skin="input.dislike_button" data-jscolor='{"hash":true,"valueElement":"editor_dislike_button_color","onFineChange":"arf_update_color(this,\"editor_dislike_button_color\")"}'></div>
                                        <input type="hidden" name="adlbclr" id="editor_dislike_button_color" class="txtxbox_widget" value="<?php echo str_replace("##","#",$dislikeBtnColor); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_dislike_btn.active~|~background","material":".ar_main_div_{arf_form_id} .arf_dislike_btn.active~|~background","material_outlined":".ar_main_div_{arf_form_id} .arf_dislike_btn.active~|~background"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_dislike_button_color" style="width:100px" /><?php echo addslashes(esc_html__('Dislike Button Color','ARForms')); ?>
                                    </div>

                                    <div class="arf_popup_clear"></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Slider Color','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="editor_slider_left_side" style="background:<?php echo str_replace("##","#",$sliderLeftColor); ?>" data-jscolor='{"hash":true,"valueElement":"editor_slider_left_side","onFineChange":"arf_update_color(this,\"editor_slider_left_side\")"}' data-skin="input.slider_selection_color"></div>
                                        <input type="hidden" name="asldrsl" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .noUi-connect~|~background","material":".ar_main_div_{arf_form_id} .noUi-connect~|~background","material_outlined":".ar_main_div_{arf_form_id} .noUi-connect~|~background"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_slider_selection_color" id="editor_slider_left_side" class="txtxbox_widget" value="<?php echo str_replace("##","#",$sliderLeftColor); ?>" style="width:100px;" /><?php echo addslashes(esc_html__("Slider selected","ARForms")); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="editor_slider_right_side" style="background:<?php echo str_replace("##","#",$sliderRightColor); ?>" data-jscolor='{"hash":true,"valueElement":"editor_slider_right_side","onFineChange":"arf_update_color(this,\"editor_slider_right_side\")"}' data-skin="input.slider_track_color"></div>
                                        <input type="hidden" name="asltrcl" id="editor_slider_right_side" class="txtxbox_widget" value="<?php echo str_replace("##","#",$sliderRightColor); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .noUi-connects~|~background","material":".ar_main_div_{arf_form_id} .noUi-connects~|~background","material_outlined":".ar_main_div_{arf_form_id} .noUi-connects~|~background"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_slider_selection_color" style="width:100px;" /><?php echo addslashes(esc_html__("Slider Track","ARForms")); ?>
                                    </div>

                                    <div class='arf_popup_clear'></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Star Rating color','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="editor_rating_color" style="background:<?php echo str_replace("##","#",$ratingColor); ?>" data-jscolor='{"hash":true,"valueElement":"editor_rating_color","onFineChange":"arf_update_color(this,\"editor_rating_color\")"}' data-skin="input.rating_color"></div>
                                        <input type="hidden" name="asclcl" id="editor_rating_color"  data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_star_rating_container input:checked ~ label.arf_star_rating_label svg path~|~fill||.ar_main_div_{arf_form_id} .control-group:not([data-view=arf_disabled]) .arf_star_rating_container label.arf_star_rating_label:hover svg path~|~fill||.ar_main_div_{arf_form_id} .control-group:not([data-view=arf_disabled]) .arf_star_rating_container label.arf_star_rating_label:hover ~ label.arf_star_rating_label svg path~|~fill","material":".ar_main_div_{arf_form_id} .arf_star_rating_container input:checked ~ label.arf_star_rating_label svg path~|~fill||.ar_main_div_{arf_form_id} .control-group:not([data-view=arf_disabled]) .arf_star_rating_container label.arf_star_rating_label:hover svg path~|~fill||.ar_main_div_{arf_form_id} .control-group:not([data-view=arf_disabled]) .arf_star_rating_container label.arf_star_rating_label:hover ~ label.arf_star_rating_label svg path~|~fill","material_outlined":".ar_main_div_{arf_form_id} .arf_star_rating_container input:checked ~ label.arf_star_rating_label svg path~|~fill||.ar_main_div_{arf_form_id} .control-group:not([data-view=arf_disabled]) .arf_star_rating_container label.arf_star_rating_label:hover svg path~|~fill||.ar_main_div_{arf_form_id} .control-group:not([data-view=arf_disabled]) .arf_star_rating_container label.arf_star_rating_label:hover ~ label.arf_star_rating_label svg path~|~fill"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_rating_colors" class="txtxbox_widget" value="<?php echo str_replace("##","#",$ratingColor); ?>" style="width:100px;" /><?php echo addslashes(esc_html__('Star Rating Color','ARForms')); ?>
                                    </div>

                                    
                                    <div class='arf_popup_clear'></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Field Tooltip','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arf_tooltip_bg_color" style="background:<?php echo str_replace('##', '#', $newarr['arf_tooltip_bg_color']) ?>;" data-skin="tooltip.background" data-default-color="<?php echo str_replace('##', '#', $newarr['arf_tooltip_bg_color']) ?>;" data-jscolor='{"hash":true,"valueElement":"arf_tooltip_bg_color","onFineChange":"arf_update_color(this,\"arf_tooltip_bg_color\")"}'></div>
                                        <input type="hidden" name="arf_tooltip_bg_color" id="arf_tooltip_bg_color" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .arf_tooltip_main~|~background-color","material":".ar_main_div_{arf_form_id} .arf_fieldset .arf_tooltip_main~|~background-color","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .arf_tooltip_main~|~background-color"}'  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_tooltip_bg_color" value="<?php echo str_replace('##', '#', $newarr['arf_tooltip_bg_color']) ?>" style="width:100px;" onchange="arftooltipinitialization();"/>
                                        <?php echo addslashes(esc_html__('Background', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arf_tooltip_font_color" style="background:<?php echo str_replace('##', '#', $newarr['arf_tooltip_font_color']) ?>;" data-skin="tooltip.text" data-default-color="<?php echo str_replace('##', '#', $newarr['arf_tooltip_font_color']) ?>;" data-jscolor='{"hash":true,"valueElement":"arf_tooltip_font_color","onFineChange":"arf_update_color(this,\"arf_tooltip_font_color\")"}'></div>
                                        <input type="hidden" name="arf_tooltip_font_color" id="arf_tooltip_font_color" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .arf_tooltip_main~|~color","material":".ar_main_div_{arf_form_id} .arf_fieldset .arf_tooltip_main~|~color","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .arf_tooltip_main~|~color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_tooltip_txt_color" value="<?php echo str_replace('##', '#', $newarr['arf_tooltip_font_color']) ?>" style="width:100px;" onchange="arftooltipinitialization();"/>
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>

                                    <div class='arf_popup_clear'></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes( esc_html__('Matrix Field Background Color', 'ARForms') ); ?></span>

                                    <div class="arf_custom_color_popup_right_item <?php echo ($allow_matrix_bg == 1) ? 'arfdisablediv': ''; ?>" id="arf_matrix_odd_bgcolor_wrapper">
                                        <?php
                                            if( empty( $newarr['arf_matrix_odd_bgcolor'] ) ){
                                                $newarr['arf_matrix_odd_bgcolor'] = '#f4f4f4';
                                            }
                                        ?>
                                        <div class="arf_custom_color_popup_picker jscolor <?php echo ($allow_matrix_bg == 1) ? 'arfdisablediv': ''; ?>" data-fid="arf_matrix_odd_bgcolor" style="background:<?php echo str_replace('##', '#', $newarr['arf_matrix_odd_bgcolor']) ?>;" data-skin="matrix.odd_row" data-default-color="<?php echo str_replace('##', '#', $newarr['arf_matrix_odd_bgcolor']) ?>;" data-jscolor='{"hash":true,"valueElement":"arf_matrix_odd_bgcolor","onFineChange":"arf_update_color(this,\"arf_matrix_odd_bgcolor\")"}'></div>
                                        <input type="hidden" name="arf_matrix_odd_bgcolor" id="arf_matrix_odd_bgcolor" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table tbody tr:nth-child(odd) td~|~background-color","material":".ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table tbody tr:nth-child(odd) td~|~background-color","material_outlined":".ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table tbody tr:nth-child(odd) td~|~background-color"}'  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_matrix_odd_bgcolor" value="<?php echo str_replace('##', '#', $newarr['arf_matrix_odd_bgcolor']) ?>" style="width:100px;"/>
                                        <?php echo addslashes(esc_html__('Odd Row', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item <?php echo ($allow_matrix_bg == 1) ? 'arfdisablediv': ''; ?>" id="arf_matrix_even_bgcolor_wrapper">
                                        <?php
                                            if( empty( $newarr['arf_matrix_even_bgcolor'] ) ){
                                                $newarr['arf_matrix_even_bgcolor'] = '#ffffff';
                                            }
                                        ?>
                                        <div class="arf_custom_color_popup_picker jscolor <?php echo ($allow_matrix_bg == 1) ? 'arfdisablediv': ''; ?>" data-fid="arf_matrix_even_bgcolor" style="background:<?php echo str_replace('##', '#', $newarr['arf_matrix_even_bgcolor']) ?>;" data-skin="matrix.even_row" data-default-color="<?php echo str_replace('##', '#', $newarr['arf_matrix_even_bgcolor']) ?>;" data-jscolor='{"hash":true,"valueElement":"arf_matrix_even_bgcolor","onFineChange":"arf_update_color(this,\"arf_matrix_even_bgcolor\")"}'></div>
                                        <input type="hidden" name="arf_matrix_even_bgcolor" id="arf_matrix_even_bgcolor" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table tbody tr:nth-child(even) td~|~background-color","material":".ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table tbody tr:nth-child(even) td~|~background-color","material_outlined":".ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table tbody tr:nth-child(even) td~|~background-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_matrix_even_bgcolor" value="<?php echo str_replace('##', '#', $newarr['arf_matrix_even_bgcolor']) ?>" style="width:100px;"/>
                                        <?php echo addslashes(esc_html__('Even Row', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                    </div>

                                    <div class="arf_custom_color_popup_right_item" style="width: auto;margin-top: 20px;<?php echo (is_rtl()) ? 'margin-right:20px;margin-left:0px;' : 'margin-left:20px;margin-right:0;'; ?>">
                                        <div class="arf_custom_checkbox_div">
                                            <div class="arf_custom_checkbox_wrapper">
                                                <input type="checkbox" value="1" <?php checked($allow_matrix_bg,1) ?> id="arf_matrix_inherit_bg" name="arf_matrix_inherit_bg"/>
                                                <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                </svg>
                                            </div>
                                        </div> 
                                        <label for="arf_matrix_inherit_bg" style="<?php echo (is_rtl()) ? 'float: right;text-align: right;margin-right: -3px;position: relative;' : 'float: left;text-align: left;margin-left: -3px;'; ?>margin-top: 3px;"><?php echo addslashes(esc_html__('Set Transparent Background','ARForms')); ?></label>
                                    </div>

                                    <?php do_action( 'arf_additional_input_color_options', $form_id, $newarr); ?>

                                    <div class='arf_popup_clear'></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Other color','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfdatepickertextcolorsetting" style="background:<?php echo str_replace('##', '#', $datepickerTxtColor); ?>;" data-skin="datepicker.text" data-default-color="<?php echo str_replace('##', '#', $datepickerTxtColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfdatepickertextcolorsetting\")","valueElement":"arfdatepickertextcolorsetting"}'></div>
                                        <input type="hidden" name="arfdtcs" id="arfdatepickertextcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.month~|~color||.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.year:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_cal_body span.year~|~color||.ar_main_div_{arf_form_id} .arf_cal_body span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_cal_body td span.month~|~color||.ar_main_div_{arf_form_id} .datepicker .arf_cal_body .day:not(.old):not(.new)~|~color||.ar_main_div_{arf_form_id} .timepicker .timepicker-hour~|~color||.ar_main_div_{arf_form_id} .timepicker .timepicker-minute~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_hour~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_minute~|~color","material":".ar_main_div_{arf_form_id} .arf_materialize_form .bootstrap-datetimepicker-widget table td.day:not(.old):not(.new),.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body td span.month~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body td span.month:hover~|~border-color||.ar_main_div_{arf_form_id} .datepicker .arf_cal_body .day:not(.old):not(.new)~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_hour~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_minute~|~color||..ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.month~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.year~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.decade:not(.disabled):hover~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.year:hover~|~border-color","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form .bootstrap-datetimepicker-widget table td.day,.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.month,.ar_main_div_{arf_form_id} .arf_material_outline_form .bootstrap-datetimepicker-widget table span.year:not(.disabled),.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.decade:not(.disabled)~|~color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_datepicker_bgcolor" value="<?php echo str_replace('##', '#', $datepickerTxtColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Datepicker Text Color', 'ARForms')); ?>
                                    </div>
                                    
                                </div>
                            </div>
                            
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="submit_button_colors"><span><?php echo addslashes(esc_html__('Submit Button Colors', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfsubmitbuttontextcolorsetting" style="background:<?php echo str_replace('##', '#', $submitTxtColor); ?>;" data-skin="submit.text" data-default-color="<?php echo str_replace('##', '#', $submitTxtColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfsubmitbuttontextcolorsetting\")","valueElement":"arfsubmitbuttontextcolorsetting"}'></div>
                                        <input type="hidden" name="arfsbtcs" id="arfsubmitbuttontextcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border~|~color||.ar_main_div_{arf_form_id} .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~color||.ar_main_div_{arf_form_id} .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~background-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat~|~color||.arfajax-file-upload~|~color||.arfajax-file-upload-img svg~|~fill","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~background-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat~|~color||.arfajax-file-upload~|~color||.arfajax-file-upload-img svg~|~fill","material_outlined":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~background-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat~|~color||.arfajax-file-upload~|~color||.arfajax-file-upload-img svg~|~fill"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_color" value="<?php echo str_replace('##', '#', $submitTxtColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfsubmitbuttonbgcolorsetting" style="background:<?php echo str_replace('##', '#', $submitBgColor); ?>;" data-skin="submit.background" data-default-color="<?php echo str_replace('##', '#', $submitBgColor); ?>" data-jscolor='{"hash":true,"valueElement":"arfsubmitbuttonbgcolorsetting","onFineChange":"arf_update_color(this,\"arfsubmitbuttonbgcolorsetting\")"}'></div>
                                        <input type="hidden" name="arfsbbcs" id="arfsubmitbuttonbgcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~background-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border~|~border-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border~|~color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border~|~border-color||.ar_main_div_{arf_form_id} .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~border-color||.ar_main_div_{arf_form_id} .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~background-color","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~background-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~background-color","material_outlined":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~background-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border~|~border-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border~|~border-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~border-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .edit_field_type_arf_repeater .arf_repeater_editor_add_icon~|~background-color"}' data-arfstyleappend="true" data-arfstyleappendid="ar_main_div_{arf_form_id}_submit_button_background_color" value="<?php echo str_replace('##', '#', $submitBgColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background', "ARForms")); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfsubmitbuttoncolorhoversetting" style="background:<?php echo str_replace('##', '#', $submitHoverBg); ?>;" data-skin="submit.background_hover" data-default-color="<?php echo str_replace('##', '#', $submitHoverBg); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfsubmitbuttoncolorhoversetting\")","valueElement":"arfsubmitbuttoncolorhoversetting"}'></div>
                                        <input type="hidden" name="arfsbchs" id="arfsubmitbuttoncolorhoversetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:hover~|~background-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~border-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~background-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border~|~border-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border~|~color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_flat~|~background-color","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:hover~|~background-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~background-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_flat~|~background-color","material_outlined":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:hover~|~background-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~border-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~background-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border~|~border-color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border~|~color||.ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_flat~|~background-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_btn_hover" value="<?php echo str_replace('##', '#', $submitHoverBg); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Hover Background', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_popup_clear"></div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfsubmitbuttonbordercolorsetting" style="background:<?php echo str_replace('##', '#', $submitBrdColor); ?>;" data-skin="submit.border" data-default-color="<?php echo str_replace('##', '#', $submitBrdColor); ?>" data-jscolor='{"hash":true,"valueElement":"arfsubmitbuttonbordercolorsetting","onFineChange":"arf_update_color(this,\"arfsubmitbuttonbordercolorsetting\")"}'></div>
                                        <input type="hidden" name="arfsbobcs" id="arfsubmitbuttonbordercolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:not(.arf_submit_btn_border):not(.arf_submit_btn_reverse_border)~|~border-color","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:not(.arf_submit_btn_border):not(.arf_submit_btn_reverse_border)~|~border-color","material_outlined":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:not(.arf_submit_btn_border):not(.arf_submit_btn_reverse_border)~|~border-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_btn_border_color" value="<?php echo str_replace('##', '#', $submitBrdColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Border Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfsubmitbuttonshadowcolorsetting" style="background:<?php echo str_replace('##', '#', $submitShadowColor); ?>;" data-skin="submit.shadow" data-default-color="<?php echo str_replace('##', '#', $submitShadowColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfsubmitbuttonshadowcolorsetting\")","valueElement":"arfsubmitbuttonshadowcolorsetting"}'></div>
										<input type="hidden" name="arfsbscs" id="arfsubmitbuttonshadowcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~box-shadow","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~box-shadow","material_outlined":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~box-shadow"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_btn_box_shadow" value="<?php echo str_replace( '##', '#', $submitShadowColor ); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Shadow Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">&nbsp;</div>
                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>

                            <div class="arf_custom_color_popup_table_row" id="wizard_color_box_wrapper">
                                <div class="arf_custom_color_popup_left_item" id="page_break_colors"><span><?php echo addslashes(esc_html__('Multistep', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    
                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Wizard tabs','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item ">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_bg_color_pg_break" style="background:<?php echo str_replace('##', '#', $activePgColor); ?>;" data-skin="pagebreak.active_tab" data-default-color="<?php echo str_replace('##', '#', $activePgColor); ?>" data-jscolor='{"hash":true,"valueElement":"frm_bg_color_pg_break","onFineChange":"arf_update_color(this,\"frm_bg_color_pg_break\")"}'></div>
                                        <input type="hidden" name="arffbcpb" id="frm_bg_color_pg_break" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $activePgColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Active Tab', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_bg_inactive_color_pg_break" style="background:<?php echo str_replace('##', '#', $inactivePgColor); ?>;" data-skin="pagebreak.inactive_tab" data-default-color="<?php echo str_replace('##', '#', $inactivePgColor); ?>" data-jscolor='{"hash":true,"valueElement":"frm_bg_inactive_color_pg_break","onFineChange":"arf_update_color(this,\"frm_bg_inactive_color_pg_break\")"}'></div>
                                        <input type="hidden" name="arfbicpb" id="frm_bg_inactive_color_pg_break" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $inactivePgColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Inactive Tab', 'ARForms')); ?>
                                    </div>

                                    <?php

                                        $pb_style3_txt_colorpicker_active = 'style="display: none;"';
                                        $pb_txt_colorpicker_active = 'style="display: block;"';

                                        foreach ($values['fields'] as $k1 => $v1) {
                                            if('break' == $v1['type']){
                                                if('wizard' == $v1['page_break_type'] && 'style3' == $v1['page_break_wizard_theme']){
                                                    $pb_style3_txt_colorpicker_active = 'style="display: block;"';
                                                    $pb_txt_colorpicker_active = 'style="display: none;"';
                                                } else {
                                                    $pb_style3_txt_colorpicker_active = 'style="display: none;"';
                                                    $pb_txt_colorpicker_active = 'style="display: block;"';
                                                }
                                                break;
                                            }
                                        }

                                     ?>
                                    <div class="arf_custom_color_popup_right_item" <?php echo $pb_txt_colorpicker_active; ?> >
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_text_color_pg_break" style="background:<?php echo str_replace('##', '#', $PgTextColor); ?>;" data-skin="pagebreak.text" data-default-color="<?php echo str_replace('##', '#', $PgTextColor); ?>" data-jscolor='{"hash":true,"valueElement":"frm_text_color_pg_break","onFineChange":"arf_update_color(this,\"frm_text_color_pg_break\")"}'></div>
                                        <input type="hidden" name="arfftcpb" id="frm_text_color_pg_break" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $PgTextColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>
                                    <!-- text color for style3 added -->
                                    <div class="arf_custom_color_popup_right_item" <?php echo $pb_style3_txt_colorpicker_active; ?> >
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_text_color_pg_break_style3" style="background:<?php echo str_replace('##', '#', $PgStyle3TextColor); ?>;" data-skin="pagebreak.style3_text" data-default-color="<?php echo str_replace('##', '#', $PgStyle3TextColor); ?>" data-jscolor='{"hash":true,"valueElement":"frm_text_color_pg_break_style3","onFineChange":"arf_update_color(this,\"frm_text_color_pg_break_style3\")"}'></div>
                                        <input type="hidden" name="arfftcpbs3" id="frm_text_color_pg_break_style3" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $PgStyle3TextColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>
                                    <!-- text color for style3 over -->
                                    <div class="arf_popup_clear"></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Survey Bar','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_bar_color_survey" style="background:<?php echo str_replace('##', '#', $surveyBarColor); ?>;" data-skin="survey.bar_color" data-default-color="<?php echo str_replace('##', '#', $surveyBarColor); ?>" data-jscolor='{"hash":true,"valueElement":"frm_bar_color_survey","onFineChange":"arf_update_color(this,\"frm_bar_color_survey\")"}'></div>
                                        <input type="hidden" name="arfbcs" id="frm_bar_color_survey" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $surveyBarColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Bar Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_bg_color_survey" style="background:<?php echo str_replace('##', '#', $surveyBgColor); ?>;" data-skin="survey.background" data-default-color="<?php echo str_replace('##', '#', $surveyBgColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"frm_bg_color_survey\")","valueElement":"frm_bg_color_survey"}'></div>
                                        <input type="hidden" name="arfbgcs" id="frm_bg_color_survey" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $surveyBgColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_text_color_survey" style="background:<?php echo str_replace('##', '#', $surveyTxtColor); ?>;" data-skin="survey.text" data-default-color="<?php echo str_replace('##', '#', $surveyTxtColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"frm_text_color_survey\")","valueElement":"frm_text_color_survey"}'></div>
                                        <input type="hidden" name="arfftcs" id="frm_text_color_survey" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $surveyTxtColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_popup_clear"></div>
                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Timer Circle Color','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_timer_bg_color" style="background:<?php echo str_replace('##', '#', $timerBgcolor); ?>;" data-skin="pagebreaktimer.circle_bg_color" data-default-color="<?php echo str_replace('##', '#', $timerBgcolor); ?>" data-jscolor='{"hash":true,"valueElement":"frm_timer_bg_color","onFineChange":"arf_update_color(this,\"frm_timer_bg_color\")"}'></div>

                                        <input type="hidden" name="arftimerbgcolor" id="frm_timer_bg_color" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $timerBgcolor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Circle Background Color', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                       <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_timer_forground_color" style="background:<?php echo str_replace('##', '#', $timerForgroundcolor); ?>;" data-skin="pagebreaktimer.circle_forground_color" data-default-color="<?php echo str_replace('##', '#', $timerForgroundcolor); ?>" data-jscolor='{"hash":true,"valueElement":"frm_timer_forground_color","onFineChange":"arf_update_color(this,\"frm_timer_forground_color\")"}'></div>

                                        <input type="hidden" name="arftimerforgroundcolor" id="frm_timer_forground_color" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $timerForgroundcolor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Circle Forground Color', 'ARForms')); ?>
                                    </div>

                                    
                                </div>
                            </div>
                            
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="success_message_colors"><span><?php echo addslashes(esc_html__('Success message Colors', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    <input type="hidden" name="arfmebs" id="arfmainerrorbgsetting" class="txtxbox_widget hex" value="<?php echo esc_attr($newarr['arferrorbgsetting']) ?>" style="width:100px;" />
                                    <input type="hidden" name="arfmebos" id="arfmainerrotbordersetting" class="txtxbox_widget hex" value="<?php echo esc_attr($newarr['arferrorbordersetting']) ?>" style="width:100px;" />
                                    <input type="hidden" name="arfmets" id="arfmainerrortextsetting" class="txtxbox_widget hex" value="<?php echo esc_attr($newarr['arferrortextsetting']) ?>" style="width:100px;" />
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainsucessbgcolorsetting" style="background:<?php echo str_replace('##', '#', $successBgColor); ?>;" data-skin="success_msg.background" data-default-color="<?php echo str_replace('##', '#', $successBgColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfmainsucessbgcolorsetting\")","valueElement":"arfmainsucessbgcolorsetting"}' data-checkskin="true"></div>
                                        <input name="arfmsbcs" id="arfmainsucessbgcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $successBgColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainsucessbordercolorsetting" style="background:<?php echo str_replace('##', '#', $successBrdColor); ?>;" data-skin="success_msg.border" data-default-color="<?php echo str_replace('##', '#', $successBrdColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfmainsucessbordercolorsetting\")","valueElement":"arfmainsucessbordercolorsetting"}' data-checkskin="true"></div>
                                        <input type="hidden" name="arfmsbocs" id="arfmainsucessbordercolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $successBrdColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__("Border", 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainsucesstextcolorsetting" style="background:<?php echo str_replace('##', '#', $successTxtColor); ?>;" data-skin="success_msg.text" data-default-color="<?php echo str_replace('##', '#', $successTxtColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfmainsucesstextcolorsetting\")","valueElement":"arfmainsucesstextcolorsetting"}' data-checkskin="true"></div>
                                        <input name="arfmstcs" id="arfmainsucesstextcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $successTxtColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="error_message_colors"><span><?php echo addslashes(esc_html__("Error Message Colors", "ARForms")); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformerrorbgcolorsetting" style="background:<?php echo str_replace('##','#', $errorBgColor); ?>" data-skin="error_msg.background" data-default-color="<?php echo str_replace('##','#', $errorBgColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfformerrorbgcolorsetting\")","valueElement":"arfformerrorbgcolorsetting"}' data-checkskin="true" ></div>
                                        <input name="arffebgc" id="arfformerrorbgcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $errorBgColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background','ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformerrorbordercolorsetting" style="background:<?php echo str_replace('##','#', $errorBrdColor); ?>" data-skin="error_msg.border" data-default-color="<?php echo str_replace('##','#', $errorBrdColor); ?>" data-jscolor='{"hash":true,"valueElement":"arfformerrorbordercolorsetting","onFineChange":"arf_update_color(this,\"arfformerrorbordercolorsetting\")"}' data-checkskin="true"></div>
                                        <input name="arffebrdc" id="arfformerrorbordercolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $errorBrdColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Border','ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformerrortextcolorsetting" style="background:<?php echo str_replace('##','#', $errorTxtColor); ?>" data-skin="error_msg.text" data-default-color="<?php echo str_replace('##','#', $errorTxtColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfformerrortextcolorsetting\")","valueElement":"arfformerrortextcolorsetting"}' data-checkskin="true"></div>
                                        <input name="arffetxtc" id="arfformerrortextcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $errorTxtColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text','ARForms')); ?>
                                    </div>
                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="validation_message_colors"><span><?php echo addslashes(esc_html__('Validation Message Colors', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    <div class="arf_custom_color_popup_right_item" id="arf_validation_background_color">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainvalidationbgcolorsetting" style="background:<?php echo str_replace('##', '#', $validationBgColor); ?>;" data-skin="validation_msg.background" data-default-color="<?php echo str_replace('##', '#', $validationBgColor); ?>" data-jscolor='{"hash":true,"valueElement":"arfmainvalidationbgcolorsetting","onFineChange":"arf_update_color(this,\"arfmainvalidationbgcolorsetting\")"}'></div>
                                        <input name="arfmvbcs" id="arfmainvalidationbgcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $validationBgColor); ?>" type="hidden" style="width:100px;" />
                                        <span><?php echo ($newarr['arferrorstyle'] == 'normal') ? addslashes(esc_html__('Color','ARForms')) : addslashes(esc_html__('Background','ARForms')); ?></span>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item" id="arf_validation_text_color" style="<?php echo ($newarr['arferrorstyle'] == 'normal') ? 'display:none;' : 'display:block;'; ?>">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainvalidationtextcolorsetting" style="background:<?php echo str_replace('##', '#', $validationTxtColor); ?>;" data-skin="validation_msg.text" data-default-color="<?php echo str_replace('##', '#', $validationTxtColor); ?>" data-jscolor='{"hash":true,"onFineChange":"arf_update_color(this,\"arfmainvalidationtextcolorsetting\")","valueElement":"arfmainvalidationtextcolorsetting"}'></div>
                                        <input name="arfmvtcs" id="arfmainvalidationtextcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $validationTxtColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="arf_custom_color_popup_footer">
                        <div class="arf_custom_color_button_position">
                            <div class="arf_custom_color_button" id="arf_custom_color_save_btn"><div class="arf_imageloader arf_form_style_custom_color_loader" id="arf_custom_color_loader"></div><?php echo addslashes(esc_html__('Apply', 'ARForms')); ?></div>
                            <div class="arf_custom_color_button arf_custom_color_cancel" id="arf_custom_color_cancel_btn"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></div>
                        </div>
                    </div>
                </div>
                <!-- Custom Font Popup -->
                <div class="arf_custom_font_popup">
                    <div class="arf_custom_color_popup_header"><?php echo addslashes(esc_html__('Custom Font Options', 'ARForms')); ?></div>
                    <div class="arf_custom_font_popup_container">
                        <div class="arf_accordion_container_row arf_margin">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Form Title Font Settings', 'ARForms')); ?></div>
                        </div>
                        <?php
                        $newarr['check_weight_form_title'] = isset($newarr['check_weight_form_title']) ? $newarr['check_weight_form_title'] : 'normal';
                        $label_font_weight = "";
                        if ($newarr['check_weight_form_title'] != "normal") {
                            $label_font_weight = ", " . $newarr['check_weight_form_title'];
                        }
                        ?>
                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <?php
                                            $newarr['arftitlefontfamily'] = ( isset( $newarr['arftitlefontfamily'] ) && $newarr['arftitlefontfamily'] != '' ) ? $newarr['arftitlefontfamily'] : 'Helvetica';

                                            $fontsarr = array(
                                                '' => array(
                                                    'inherit' => addslashes( esc_html__('Inherit from theme', 'ARForms') ),
                                                ),
                                                'default||' . addslashes(esc_html__('Default Fonts', 'ARForms')) => $arformcontroller->get_arf_default_fonts(),
                                                'google||' . addslashes(esc_html__('Google Fonts', 'ARForms')) => arf_google_font_listing(),
                                            );

                                            $fontsattr = array(
                                                'data-arfstyle' => 'true',
                                                'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arfeditorformdescription~|~font-family","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arfeditorformdescription~|~font-family","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arfeditorformdescription~|~font-family"}',
                                                'data-arfstyleappend' => 'true',
                                                'data-arfstyleappendid' => 'arf_{arf_form_id}_form_title_family',
                                                'data-default-font' => $newarr['arftitlefontfamily']
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'arftff', 'arftitlefontsetting', '','', $newarr['arftitlefontfamily'], $fontsattr, $fontsarr, true, array(), false, array(), false, array(), true );

                                        ?>
                                    </div>

                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <?php
                                            $font_size_opts = array();
                                            for( $i = 8; $i <= 20; $i++ ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 22; $i <= 28; $i = $i + 2 ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 32; $i <= 40; $i = $i + 4 ){
                                                $font_size_opts[$i] = $i;
                                            }

                                            $font_size_attr = array(
                                                'data-arfstyle' => 'true',
                                                'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-size","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-size","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-size"}',
                                                'data-arfstyleappend' => 'true',
                                                'data-arfstyleappendid' => 'arf_{arf_form_id}_form_title_size'
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'arfftfss', 'arfformtitlefontsizesetting', '', '', $newarr['form_title_font_size'], $font_size_attr, $font_size_opts );
                                        ?>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <input id="arfformtitleweightsetting" name="arfftws" value="<?php echo $newarr['check_weight_form_title']; ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-style","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-style","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_title_style" type="hidden" class="arf_custom_font_options arf_custom_font_style" data-default-font="<?php echo $newarr['check_weight_form_title']; ?>" />
                                    <?php $arf_form_title_font_style_arr = explode(',', $newarr['check_weight_form_title']); ?>
                                    <span class="arf_font_style_button <?php echo (in_array('strikethrough', $arf_form_title_font_style_arr)) ? 'active' : ''; ?>" data-style="strikethrough" data-id="arfformtitleweightsetting"><i class="fas fa-strikethrough"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('underline', $arf_form_title_font_style_arr)) ? 'active' : ''; ?>" data-style="underline" data-id="arfformtitleweightsetting"><i class="fas fa-underline"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('italic', $arf_form_title_font_style_arr)) ? 'active' : ''; ?>" data-style="italic" data-id="arfformtitleweightsetting"><i class="fas fa-italic"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('bold', $arf_form_title_font_style_arr)) ? 'active' : ''; ?>" data-style="bold" data-id="arfformtitleweightsetting"><i class="fas fa-bold"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="arf_accordion_container_row_separator"></div>
                        <div class="arf_accordion_container_row arf_margin">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Label Font Settings', 'ARForms')); ?></div>
                        </div>
                        <?php
                        $label_font_weight = "";
                        if ($newarr['weight'] != "normal") {
                            $label_font_weight = ", " . $newarr['weight'];
                        }
                        ?>
                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <?php
                                            $newarr['font'] = ( isset( $newarr['font'] ) && $newarr['font'] != '' ) ? $newarr['font'] : 'Helvetica';
                                           

                                            $fontsarr = array(
                                                '' => array(
                                                    'inherit' => addslashes( esc_html__('Inherit from theme', 'ARForms') ),
                                                ),
                                                'default||' . addslashes(esc_html__('Default Fonts', 'ARForms')) => $arformcontroller->get_arf_default_fonts(),
                                                'google||' . addslashes(esc_html__('Google Fonts', 'ARForms')) => arf_google_font_listing(),
                                            );

                                            $fontsattr = array(
                                                'data-arfstyle' => 'true',
                                                'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-family","material":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-family","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-family"}',
                                                'data-arfstyleappend' => 'true',
                                                'data-arfstyleappendid' => 'arf_{arf_form_id}_label_font_family',
                                                'data-default-font' => $newarr['font']
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'arfmfs', 'arfmainfontsetting', '','', $newarr['font'], $fontsattr, $fontsarr, true, array(), false, array(), false, array(), true );
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <?php
                                            $font_size_opts = array();
                                            for( $i = 8; $i <= 20; $i++ ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 22; $i <= 28; $i = $i + 2 ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 32; $i <= 40; $i = $i + 4 ){
                                                $font_size_opts[$i] = $i;
                                            }

                                            $font_size_attr = array(
                                                'data-arfstyle' => 'true',
                                                'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-size||.ar_main_div_{arf_form_id} .arfformfield .arf_matrix_field_control_wrapper tr td~|~font-size||.ar_main_div_{arf_form_id} .arfformfield .arf_matrix_field_control_wrapper tr th~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image)~|~padding-left||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image)~|~padding-left","material":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-size||.ar_main_div_{arf_form_id} .arfformfield .arf_matrix_field_control_wrapper tr td~|~font-size||.ar_main_div_{arf_form_id} .arfformfield .arf_matrix_field_control_wrapper tr th~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image)~|~padding-left||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image)~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image) .arf_checkbox_input_wrapper~|~margin-left||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image) .arf_radio_input_wrapper~|~margin-left","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-size||.ar_main_div_{arf_form_id} .arfformfield .arf_matrix_field_control_wrapper tr td~|~font-size||.ar_main_div_{arf_form_id} .arfformfield .arf_matrix_field_control_wrapper tr th~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image)~|~padding-left||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image)~|~padding-left||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_fieldset .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image) .arf_checkbox_input_wrapper~|~margin-left||.ar_main_div_{arf_form_id} .arf_material_outline_form .arf_fieldset .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image) .arf_radio_input_wrapper~|~margin-left"}',
                                                'data-arfstyleappend' => 'true',
                                                'data-arfstyleappendid' => 'arf_{arf_form_id}_label_font_size'
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'arffss', 'arffontsizesetting', '', '', $newarr['font_size'], $font_size_attr, $font_size_opts );
                                        ?>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <input id="arfmainfontweightsetting" name="arfmfws" value="<?php echo $newarr['weight']; ?>" type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_matrix_field_control_wrapper table th~|~font-style||.ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table td~|~font-style","material":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_matrix_field_control_wrapper table th~|~font-style||.ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table td~|~font-style","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_matrix_field_control_wrapper table th~|~font-style||.ar_main_div_{arf_form_id} .arf_matrix_field_control_wrapper table td~|~font-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_font_style" class="arf_custom_font_options arf_custom_font_style" data-default-font="<?php echo $newarr['weight']; ?>">
                                    <?php $arf_label_font_style_arr = explode(',', $newarr['weight']); ?>
                                    <span class="arf_font_style_button <?php echo (in_array('strikethrough', $arf_label_font_style_arr)) ? 'active' : ''; ?>" data-style="strikethrough" data-id="arfmainfontweightsetting"><i class="fas fa-strikethrough"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('underline', $arf_label_font_style_arr)) ? 'active' : ''; ?>" data-style="underline" data-id="arfmainfontweightsetting"><i class="fas fa-underline"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('italic', $arf_label_font_style_arr)) ? 'active' : ''; ?>" data-style="italic" data-id="arfmainfontweightsetting"><i class="fas fa-italic"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('bold', $arf_label_font_style_arr)) ? 'active' : ''; ?>" data-style="bold" data-id="arfmainfontweightsetting"><i class="fas fa-bold"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="arf_accordion_container_row_separator"></div>
                        <div class="arf_accordion_container_row arf_margin" id="arf_input_font_settings_container">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Input Font Settings', 'ARForms')); ?></div>
                        </div>
                        <?php
                        $input_font_weight_html = "";
                        if ($newarr['check_weight'] != "normal") {
                            $input_font_weight_html = ", " . $newarr['check_weight'];
                        }
                        ?>
                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <?php
                                            $newarr['check_font'] = ( isset( $newarr['check_font'] ) && $newarr['check_font'] != '' ) ? $newarr['check_font'] : 'Helvetica';

                                            $fontsarr = array(
                                                '' => array(
                                                    'inherit' => addslashes( esc_html__('Inherit from theme', 'ARForms') ),
                                                ),
                                                'default||' . addslashes(esc_html__('Default Fonts', 'ARForms')) => $arformcontroller->get_arf_default_fonts(),
                                                'google||' . addslashes(esc_html__('Google Fonts', 'ARForms')) => arf_google_font_listing(),
                                            );

                                            $fontsattr = array(
                                                'data-arfstyle' => 'true',
                                                'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-editor p~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-family||.ar_main_div_101 .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~font-family||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~font-family||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-family||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-family||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-family||.ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-family||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~font-family||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~font-family","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text)~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-editor p~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text].arf-select-dropdown~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~font-family||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-family||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-family||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~font-family||.ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-family||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~font-family||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~font-family","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text)~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-editor p~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .trumbowyg .trumbowyg-editor p~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text].arf-select-dropdown~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~font-family||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-family||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-family||.ar_main_div_{arf_form_id} .arf_material_outline_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~font-family||.ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-family||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~font-family||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~font-family"}',
                                                'data-arfstyleappend' => 'true',
                                                'data-arfstyleappendid' => 'arf_{arf_form_id}_input_font_family',
                                                'data-default-font' => $newarr['check_font']
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'arfcbfs', 'arfcheckboxfontsetting', '','', $newarr['check_font'], $fontsattr, $fontsarr, true, array(), false, array(), false, array(), true );

                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <?php
                                            $font_size_opts = array();
                                            for( $i = 8; $i <= 20; $i++ ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 22; $i <= 28; $i = $i + 2 ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 32; $i <= 40; $i = $i + 4 ){
                                                $font_size_opts[$i] = $i;
                                            }

                                            $font_size_attr = array(
                                                'data-arfstyle' => 'true',
                                                'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~font-size||.ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~font-size||.ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=radio]~|~font-size||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-editor p~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-size||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~font-size||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-size||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-size||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-size|| .ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-size||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~font-size||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~font-size","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-editor p~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-editor p~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text].arf-select-dropdown~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-size||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-size||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-size|| .ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-size||.ar_main_div_{arf_form_id} .arf_material_theme_container_with_icons .arf_leading_icon~|~font-size||.ar_main_div_{arf_form_id} .arf_material_theme_container_with_icons .arf_trailing_icon~|~font-size||.ar_main_div_{arf_form_id} .arf_material_theme_container_with_icons .arf_main_label~|~font-size||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~font-size||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~font-size","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls .trumbowyg .trumbowyg-editor p~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-editor p~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text].arf-select-dropdown~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-size||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-size||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-size||.ar_main_div_{arf_form_id} .arf_material_outline_container_with_icons .arf_leading_icon~|~font-size||.ar_main_div_{arf_form_id} .arf_material_outline_container_with_icons .arf_trailing_icon~|~font-size|| .ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-size||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~font-size||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~font-size"}',
                                                'data-arfstyleappend' => 'true',
                                                'data-arfstyleappendid' => 'arf_{arf_form_id}_label_font_size'
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'arfffss', 'arffieldfontsizesetting', '', '', $newarr['field_font_size'], $font_size_attr, $font_size_opts );
                                        ?>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <input id="arfcheckboxweightsetting" name="arfcbws" value="<?php echo $newarr['check_weight']; ?>" type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-editor p~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-style||.ar_main_div_101 .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-style||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~font-style||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-style||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-style||.ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~font-style||.ar_main_div_{arf_form_id} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~font-style","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-editor p~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-style||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-style||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-style||.ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~font-style||.ar_main_div_{arf_form_id} .arf_materialize_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~font-style","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .controls input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-editor p~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-style||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-style||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-style||.ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span~|~font-style||.ar_main_div_{arf_form_id} .arf_material_outline_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul li~|~font-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_font_style" class="arf_custom_font_options arf_custom_font_style" data-default-font="<?php echo $newarr['check_weight']; ?>" >
                                    <?php $arf_input_font_style_arr = explode(',', $newarr['check_weight']); ?>
                                    <span class="arf_font_style_button <?php echo (in_array('strikethrough', $arf_input_font_style_arr)) ? 'active' : ''; ?>" data-style="strikethrough" data-id="arfcheckboxweightsetting"><i class="fas fa-strikethrough"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('underline', $arf_input_font_style_arr)) ? 'active' : ''; ?>" data-style="underline" data-id="arfcheckboxweightsetting"><i class="fas fa-underline"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('italic', $arf_input_font_style_arr)) ? 'active' : ''; ?>" data-style="italic" data-id="arfcheckboxweightsetting"><i class="fas fa-italic"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('bold', $arf_input_font_style_arr)) ? 'active' : ''; ?>" data-style="bold" data-id="arfcheckboxweightsetting"><i class="fas fa-bold"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="arf_accordion_container_row_separator"></div>
                        <div class="arf_accordion_container_row arf_margin">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Section Font Settings', 'ARForms')); ?></div>
                        </div>        
                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <?php
                                            $newarr['arfsectiontitlefamily'] = ( isset( $newarr['arfsectiontitlefamily'] ) && $newarr['arfsectiontitlefamily'] != '' ) ? $newarr['arfsectiontitlefamily'] : 'Helvetica';

                                            $fontsarr = array(
                                                '' => array(
                                                    'inherit' => addslashes( esc_html__('Inherit from theme', 'ARForms') ),
                                                ),
                                                'default||' . addslashes(esc_html__('Default Fonts', 'ARForms')) => $arformcontroller->get_arf_default_fonts(),
                                                'google||' . addslashes(esc_html__('Google Fonts', 'ARForms')) => arf_google_font_listing(),
                                            );

                                            $fontsattr = array(
                                                'data-arfstyle' => 'true',
                                                'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_section .arfeditorfieldopt_section_label~|~font-family","material":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_section .arfeditorfieldopt_section_label~|~font-family","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_section .arfeditorfieldopt_section_label~|~font-family"}',
                                                'data-arfstyleappend' => 'true',
                                                'data-arfstyleappendid' => 'arf_{arf_form_id}_section_title_family',
                                                'data-default-font' => $newarr['arfsectiontitlefamily']
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'arfsectiontitlefamily', 'arfsectiontitlefamily', '','', $newarr['arfsectiontitlefamily'], $fontsattr, $fontsarr, true, array(), false, array(), false, array(), true );
                                        ?>
                                    </div>

                                </div>
                            </div>
                             <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <?php
                                            $font_size_opts = array();
                                            for( $i = 8; $i <= 20; $i++ ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 22; $i <= 28; $i = $i + 2 ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 32; $i <= 40; $i = $i + 4 ){
                                                $font_size_opts[$i] = $i;
                                            }

                                            $font_size_attr = array(
                                                'data-arfstyle' => 'true',
                                                'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_section .arfeditorfieldopt_section_label~|~font-size","material":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset label.arf_width_counter_label_divider~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_section .arfeditorfieldopt_section_label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset label.arf_width_counter_label_section~|~font-size","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset label.arf_width_counter_label_divider~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_section .arfeditorfieldopt_section_label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset label.arf_width_counter_label_section~|~font-size"}',
                                                'data-arfstyleappend' => 'true',
                                                'data-arfstyleappendid' => 'arf_{arf_form_id}_section_title_size'
                                            );

                                            $section_title_font_size = isset($newarr['arfsectiontitlefontsizesetting']) ? $newarr['arfsectiontitlefontsizesetting'] : '19';;

                                            echo $maincontroller->arf_selectpicker_dom( 'arfsectiontitlefontsizesetting', 'arfsectiontitlefontsizesetting', '', '', $section_title_font_size, $font_size_attr, $font_size_opts );
                                        ?>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <input id="arfsectiontitleweightsetting" name="arfsectiontitleweightsetting" value="<?php echo isset($newarr['arfsectiontitleweightsetting']) ? $newarr['arfsectiontitleweightsetting'] : ''; ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_sectionr .arfeditorfieldopt_section_label~|~font-style","material":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_section .arfeditorfieldopt_section_label~|~font-style","material_outlined":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_section .arfeditorfieldopt_section_label~|~font-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_section_title_style" type="hidden" class="arf_custom_font_options arf_custom_font_style" data-default-font="<?php echo isset($newarr['arfsectiontitleweightsetting']) ? $newarr['arfsectiontitleweightsetting'] : ''; ?>" />
                                    <?php $arf_section_title_font_style_arr = isset($newarr['arfsectiontitleweightsetting']) ? explode(',', $newarr['arfsectiontitleweightsetting']) : array(); ?>
                                    <span class="arf_font_style_button <?php echo (in_array('strikethrough', $arf_section_title_font_style_arr)) ? 'active' : ''; ?>" data-style="strikethrough" data-id="arfsectiontitleweightsetting"><i class="fas fa-strikethrough"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('underline', $arf_section_title_font_style_arr)) ? 'active' : ''; ?>" data-style="underline" data-id="arfsectiontitleweightsetting"><i class="fas fa-underline"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('italic', $arf_section_title_font_style_arr)) ? 'active' : ''; ?>" data-style="italic" data-id="arfsectiontitleweightsetting"><i class="fas fa-italic"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('bold', $arf_section_title_font_style_arr)) ? 'active' : ''; ?>" data-style="bold" data-id="arfsectiontitleweightsetting"><i class="fas fa-bold"></i></span>                    
                                </div>
                            </div>
                        </div>
                        <div class="arf_accordion_container_row_separator"></div>
                        <div class="arf_accordion_container_row arf_margin" id="arf_submit_font_settings_container">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Submit Font Settings', 'ARForms')); ?></div>
                        </div>
                        <?php
                        $submit_font_weight_html = "";
                        if ($newarr['arfsubmitweightsetting'] != "normal") {
                            $submit_font_weight_html = ", " . $newarr['arfsubmitweightsetting'];
                        }
                        ?>
                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <?php
                                            $newarr['arfsubmitfontfamily'] = ( isset( $newarr['arfsubmitfontfamily'] ) && $newarr['arfsubmitfontfamily'] != '' ) ? $newarr['arfsubmitfontfamily'] : 'Helvetica';

                                            $fontsarr = array(
                                                '' => array(
                                                    'inherit' => addslashes( esc_html__('Inherit from theme', 'ARForms') ),
                                                ),
                                                'default||' . addslashes(esc_html__('Default Fonts', 'ARForms')) => $arformcontroller->get_arf_default_fonts(),
                                                'google||' . addslashes(esc_html__('Google Fonts', 'ARForms')) => arf_google_font_listing(),
                                            );

                                            $fontsattr = array(
                                                'data-arfstyle' => 'true',
                                                'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~font-family","material":".ar_main_div_{arf_form_id}  .arfsubmitbutton .arf_submit_btn~|~font-family","material_outlined":".ar_main_div_{arf_form_id}  .arfsubmitbutton .arf_submit_btn~|~font-family"}',
                                                'data-arfstyleappend' => 'true',
                                                'data-arfstyleappendid' => 'arf_{arf_form_id}_submit_btn_font_family',
                                                'data-default-font' => $newarr['arfsubmitfontfamily']
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'arfsff', 'arfsubmitfontfamily', '','', $newarr['arfsubmitfontfamily'], $fontsattr, $fontsarr, true, array(), false, array(), false, array(), true );
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <?php
                                            $font_size_opts = array();
                                            for( $i = 8; $i <= 20; $i++ ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 22; $i <= 28; $i = $i + 2 ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 32; $i <= 40; $i = $i + 4 ){
                                                $font_size_opts[$i] = $i;
                                            }

                                            $font_size_attr = array(
                                                'data-arfstyle' => 'true',
                                                'data-arfstyledata' => '{"standard":".ar_main_div_{arf_form_id}  .arfsubmitbutton .arf_submit_btn~|~font-size","material":".ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_submit_btn~|~font-size","material_outlined":".ar_main_div_{arf_form_id} .arf_material_outline_form .arfsubmitbutton .arf_submit_btn~|~font-size"}',
                                                'data-arfstyleappend' => 'true',
                                                'data-arfstyleappendid' => 'arf_{arf_form_id}_submit_btn_font_size'
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'arfsbfss', 'arfsubmitbuttonfontsizesetting', '', '', $newarr['arfsubmitbuttonfontsizesetting'], $font_size_attr, $font_size_opts );
                                        ?>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <input id="arfsubmitbuttonweightsetting" name="arfsbwes" value="<?php echo $newarr['arfsubmitweightsetting']; ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id}  .arfsubmitbutton .arf_submit_btn~|~font-style","material":".ar_main_div_{arf_form_id}  .arfsubmitbutton .arf_submit_btn~|~font-style","material_outlined":".ar_main_div_{arf_form_id}  .arfsubmitbutton .arf_submit_btn~|~font-style"}'  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_btn_font_style" type="hidden" class="arf_custom_font_options arf_custom_font_style" data-default-font="<?php echo $newarr['arfsubmitweightsetting']; ?>">
                                    <?php $arf_submit_button_font_style_arr = explode(',', $newarr['arfsubmitweightsetting']); ?>
                                    <span class="arf_font_style_button <?php echo (in_array('strikethrough', $arf_submit_button_font_style_arr)) ? 'active' : ''; ?>" data-style="strikethrough" data-id="arfsubmitbuttonweightsetting"><i class="fas fa-strikethrough"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('underline', $arf_submit_button_font_style_arr)) ? 'active' : ''; ?>" data-style="underline" data-id="arfsubmitbuttonweightsetting"><i class="fas fa-underline"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('italic', $arf_submit_button_font_style_arr)) ? 'active' : ''; ?>" data-style="italic" data-id="arfsubmitbuttonweightsetting"><i class="fas fa-italic"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('bold', $arf_submit_button_font_style_arr)) ? 'active' : ''; ?>" data-style="bold" data-id="arfsubmitbuttonweightsetting"><i class="fas fa-bold"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="arf_accordion_container_row_separator"></div>
                        <div class="arf_accordion_container_row arf_margin">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Validation Font Settings', 'ARForms')); ?></div>
                        </div>

                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <?php
                                            $newarr['error_font'] = ( isset( $newarr['error_font'] ) && $newarr['error_font'] != '' ) ? $newarr['error_font'] : 'Helvetica';

                                            $fontsarr = array(
                                                '' => array(
                                                    'inherit' => addslashes( esc_html__('Inherit from theme', 'ARForms') ),
                                                ),
                                                'default||' . addslashes(esc_html__('Default Fonts', 'ARForms')) => $arformcontroller->get_arf_default_fonts(),
                                                'google||' . addslashes(esc_html__('Google Fonts', 'ARForms')) => arf_google_font_listing(),
                                            );

                                            $fontsattr = array(
                                                'data-default-font' => $newarr['error_font']
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'arfmefs', 'arfmainerrorfontsetting', '','', $newarr['error_font'], $fontsattr, $fontsarr, true, array(), false, array(), false, array(), true );
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <?php
                                            $font_size_opts = array();
                                            for( $i = 8; $i <= 20; $i++ ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 22; $i <= 28; $i = $i + 2 ){
                                                $font_size_opts[$i] = $i;
                                            }
                                            for( $i = 32; $i <= 40; $i = $i + 4 ){
                                                $font_size_opts[$i] = $i;
                                            }

                                            echo $maincontroller->arf_selectpicker_dom( 'arfmefss', 'arfmainerrorfontsizesetting', '', '', $newarr['arffontsizesetting'], array(), $font_size_opts );
                                        ?>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="arf_custom_font_popup_footer">
                        <div class="arf_custom_font_button_position">
                            <div class="arf_custom_font_button arf_custom_font_save_close" id="arf_custom_font_save_btn"><?php echo addslashes(esc_html__('Apply', 'ARForms')) ?></div>
                            <div class="arf_custom_font_button arf_custom_font_cancel arf_custom_font_close" id="arf_custom_font_cancel_btn"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Auto Response email -->
        <div class="arf_modal_overlay">
            <div id="arf_mail_notification_model" class="arf_popup_container arf_popup_container_mail_notification_model">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Email Notifications', 'ARForms')); ?>
                <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_mail_notification_popup_button"><svg width="30px" height="30px" viewbox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg></div>
                </div>
                <div class="arf_popup_content_container arf_mail_notification_container">
                    <div class="arf_popup_container_loader">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="arf_popup_checkbox_wrapper" style="width:100%; margin-bottom:10px;">
                        <?php $values['auto_responder'] = isset($values['auto_responder']) ? $values['auto_responder'] : ''; ?>
                        <div class="arf_custom_checkbox_div">
                            <div class="arf_custom_checkbox_wrapper" onclick="CheckUserAutomaticResponseEnableDisable();" style="margin-right: 9px;">
                                <?php $arf_checked = isset($values['auto_responder']) ? $values['auto_responder'] : 0; ?>
                                <input type="checkbox" name="options[auto_responder]" id="auto_responder" value="1" <?php checked($arf_checked, 1);  ?> />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                                <?php unset($arf_checked); ?>
                            </div>
                            <span><label id="arf_auto_responder" for="auto_responder" class="arffont16"><?php echo addslashes(esc_html__('Send an automatic response to users after form submission.', 'ARForms')); ?></label></span>
                        </div>

                         <div style="<?php echo (is_rtl()) ? 'float: left;position:relative;' : 'float: right;position:relative;'; ?>">
                            <a href="<?php echo ARFURL; ?>/documentation/index.html#email_notifiaction" target="_blank" class="arf_adminhelp_icon tipso_style" data-tipso="help">
                                <svg width="30px" height="30px" viewBox="0 0 26 32" class="arfsvgposition arfhelptip tipso_style" data-tipso="help" title="help">
                                <?php echo ARF_LIFEBOUY_ICON;?>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="arf_auto_responder_content arfmarginl10" >
                        <div class="arf_auto_responder_row">
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label arf_send_mail_to_label"><?php echo addslashes(esc_html__('Select field to send E-mail', 'ARForms')); ?></label>
                                <?php
                                $auto_responder_disabled = "";
                                if (isset($values['auto_responder']) && $values['auto_responder'] < 1) {
                                    $auto_responder_disabled = "disabled='disabled'";
                                }
                                $selectbox_field_options = array( '' => addslashes(esc_html__('Select Field', 'ARForms')) );
                                $selectbox_field_value_label = "";
                                $user_responder_email = "";
                                if (!empty($values['fields'])) {
                                    if( !empty( $all_hidden_fields ) ){
                                        $hidden_fields = $arformcontroller->arfObjtoArray( $all_hidden_fields );
                                        $values['fields'] = array_merge( $hidden_fields, $values['fields'] );
                                    }
                                    foreach ($values['fields'] as $val_key => $fo) {
                                        if (in_array($fo['type'], array('email', 'text', 'hidden', 'radio', 'select'))) {

                                            if( isset( $fo['has_parent'] ) && $fo['has_parent'] == 1 && isset( $fo['parent_field_type'] ) && $fo['parent_field_type'] == 'arf_repeater' ){
                                                continue;
                                            }
                                            if (($fo["id"] == $values['ar_email_to'])) {
                                                $selectbox_field_value_label = $fo["name"];
                                                $user_responder_email = $values['ar_email_to'];
                                            }

                                            $current_field_id = $fo["id"];
                                            if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags')=="" ){
                                                $selectbox_field_options[$current_field_id] = '[Field id : '.$current_field_id.']';
                                            }else{
                                                $selectbox_field_options[$current_field_id] = $arfieldhelper->arf_execute_function($fo["name"],'strip_tags');
                                            }
                                            
                                        }
                                    }
                                }
                                $user_responder_email = apply_filters('arf_change_autoresponse_selected_email_value_in_outside', $user_responder_email, $id, $values);
                                $selectbox_field_value_label = apply_filters('arf_change_autoresponse_selected_email_label_in_outside', $selectbox_field_value_label, $id, $values);

                                $ar_email_to_val = ($responder_email != "" && $responder_email != '0') ? $responder_email : $user_responder_email;

                                $ar_email_to_attr = array();
                                if (isset($values['arf_conditional_enable_mail']) && $values['arf_conditional_enable_mail'] == 1) {
                                    $ar_email_to_attr['disabled'] = 'disabled';
                                }

                                if ( $values['auto_responder'] == 0 && $auto_responder_disabled != "" || (isset($values['arf_conditional_enable_mail']) && $values['arf_conditional_enable_mail'] == 1)) {
                                    $ar_email_to_opt_disable = true;
                                    $arf_autoresponder_cls = 'arf_options_ar_user_email_to arf_email_field_dropdown arf_auto_responder_disabled';
                                }else{
                                    $ar_email_to_opt_disable = false;
                                    $arf_autoresponder_cls = 'arf_options_ar_user_email_to arf_email_field_dropdown';
                                }

                                echo $maincontroller->arf_selectpicker_dom( 'options[ar_email_to]', 'options_ar_user_email_to', $arf_autoresponder_cls, 'width:80%;margin-top: 7px;', $ar_email_to_val, $ar_email_to_attr, $selectbox_field_options, false, array(), $ar_email_to_opt_disable, array(), false, array(), true );
                                ?>                                       


                                <?php do_action('arf_add_autoresponse_email_option_in_out_side', $id, $values); ?>
                                
                                <div class="arf_popup_tooltip_main"><img src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" style="margin-left:20px;" class="arfhelptip" title="<?php echo addslashes(esc_html__('Please map desired email field from the list of fields used in your form. And system will send response email to this address.', 'ARForms')) ?>"/></div>

                                <!--Mail redirection starts here.-->
                                <?php
                                if (isset($values['arf_conditional_mail_rules']) && !empty($values['arf_conditional_mail_rules'])) {
                                    $rule_array_conditional_mail_sent = $values['arf_conditional_mail_rules'];
                                } else {
                                    $rule_array_conditional_mail_sent[1]['id_mail'] = '';
                                    $rule_array_conditional_mail_sent[1]['field_id_mail'] = '';
                                    $rule_array_conditional_mail_sent[1]['field_type_mail'] = '';
                                    $rule_array_conditional_mail_sent[1]['value_mail'] = '';
                                    $rule_array_conditional_mail_sent[1]['send_mail_field'] = '';
                                }
                                $total_rule_array_mail = count(array_keys($rule_array_conditional_mail_sent));
                                ?>
                            </div>
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Subject E-mail', 'ARForms')); ?></label>
                                <?php
                                $ar_email_subject = isset($values['ar_email_subject']) ? $values['ar_email_subject'] : '';
                                $ar_email_subject = $arformhelper->replace_field_shortcode($ar_email_subject);
                                ?>
                                <input type="text" name="options[ar_email_subject]" class="arf_advanceemailfield arfheight34" id="ar_email_subject" value="<?php echo esc_attr($ar_email_subject); ?>" <?php echo $auto_responder_disabled; ?> />

                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_subject')" id="add_field_email_subject_but" <?php echo $auto_responder_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_subject">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" onclick="close_add_field_subject('add_field_subject')" class="arf_field_model_close">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_p">
                                            <?php
                                            if (isset($values['id'])) {
                                                
                                                $arfieldhelper->get_shortcode_modal($values['id'], 'ar_email_subject', 'no_email', 'style="width:330px;"', false, $field_list);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="arf_auto_responder_row" style="margin-bottom: 0px;width:95%;">
                            <div class="arf_or_option"><?php echo addslashes(esc_html__('Or', 'ARForms')) ?></div>
                        </div>
                        <div class="arf_auto_responder_row" style="margin-bottom:20px;">
                            <?php $values['arf_conditional_enable_mail'] = isset($values['arf_conditional_enable_mail']) ? $values['arf_conditional_enable_mail'] : ''; ?>

                            <div class="arf_popup_checkbox_wrapper" >
                                <div class="arf_custom_checkbox_div">
                                    <div class="arf_custom_checkbox_wrapper" onclick="arf_conditional_enable_disable_mail_func();" style="margin-right: 9px;">
                                        <input type="checkbox"  <?php checked($values['arf_conditional_enable_mail'], 1); ?> value="1" id="arf_conditional_enable_disable_mail_id_chkbox" name="options[arf_conditional_enable_mail]">
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                        </svg>
                                    </div>
                                <span><label for="arf_conditional_enable_disable_mail_id_chkbox" class="arf_auto_responder_label arfhelptip" title="<?php echo esc_html__('Please select options to send an automatic response to user.', 'ARForms'); ?>"><?php echo addslashes(esc_html__('Configure Conditional Email Notification', 'ARForms')); ?></label></span>
                                </div>
                            </div>

                            <?php
                            if (isset($values['arf_conditional_enable_mail']) && $values['arf_conditional_enable_mail'] == 1) {
                                $arf_dispaly_mail_div = "display:block;";
                            } else {
                                $arf_dispaly_mail_div = "display:none;";
                            }
                            ?>
                            <div id="arf_append_mail_add_div" style="<?php echo $arf_dispaly_mail_div ?>">
                                <span class="arfmailsendmailconditional_if"><?php echo addslashes(esc_html__('Send If', 'ARForms')); ?></span>
                                <?php foreach ($rule_array_conditional_mail_sent as $rule_i => $conditional_mail_value) { ?>
                                    <div class="arf_conditional_logic_mail_div" style="<?php echo $arf_dispaly_mail_div ?>" id="arf_rule_conditional_mail_for_delete_<?php echo $rule_i; ?>">
                                        <input type="hidden" value="<?php echo $rule_i; ?>" class="rule_array_conditional_mail_hidden" name="options[arf_conditional_mail_rules][<?php echo $rule_i;?>][id_mail]">

                                        <div class="arf_conditional_logic_div">
                                            <span id="select_ar_conditional_mail_filed_div" class="arf_conditional_logic_div_span">
                                                <div class="sltstandard">
                                                    <?php
                                                    $selectbox_field_options_for_mail = array('' => addslashes(esc_html__('Select Field', 'ARForms')));
                                                    $selectbox_field_options_for_mail_attr = array();
						    /* reputelog - add html field for the conditional email with running total */
                                                    $selectbox_field_value_label = "";
                                                    $user_responder_mail = "";
                                                    if (!empty($values['fields'])) {
                                                        foreach ($values['fields'] as $val_key => $fo) {

                                                            if (!in_array($fo['type'], $conditional_logic_array_if)) {
                                                                if( ( isset( $fo['has_parent'] ) && $fo['has_parent'] == 1 && isset( $fo['parent_field_type'] ) && $fo['parent_field_type'] == 'arf_repeater' ) || ( isset( $fo['type2'] ) && 'ccfield' == $fo['type2'] ) ){
                                                                    continue;
                                                                }
                                                                if (($fo["id"] == $conditional_mail_value['field_id_mail'])) {
                                                                    $selectbox_field_value_label = $fo["name"];
                                                                    $user_responder_mail = isset($values['field_id_mail']) ? $values['field_id_mail'] : '';
                                                                }

                                                                $current_field_id = $fo["id"];
                                                                if ($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') =="") {
                                                                    $selectbox_field_options_for_mail[$current_field_id] = '[Field id : '.$current_field_id.']';
                                                                }else{
                                                                    $selectbox_field_options_for_mail[$current_field_id] = $arfieldhelper->arf_execute_function($fo["name"],'strip_tags'); 
                                                                }
                                                                $selectbox_field_options_for_mail_attr['data-type'][$current_field_id] = $fo['type'];

                                                                
                                                            }
                                                        }
                                                    }
                                                    ?>

                                                    <input id="arf_conditional_mail_field_type_<?php echo $rule_i; ?>" name="options[arf_conditional_mail_rules][<?php echo $rule_i; ?>][field_type_mail]" value="<?php echo $conditional_mail_value['field_type_mail']; ?>" type="hidden" />

                                                    <?php 
                                                        $arf_conditional_mail_attr = array(
                                                            'class' => 'arf_condition_mail_field_action'
                                                        );
                                                    ?>

                                                    <?php
                                                        echo $maincontroller->arf_selectpicker_dom( 'options[arf_conditional_mail_rules][' . $rule_i .'][field_id_mail]', 'arf_conditional_mail_filed_'.$rule_i , 'arf_name_field_dropdown', 'width:160px;', $conditional_mail_value['field_id_mail'], $arf_conditional_mail_attr, $selectbox_field_options_for_mail, false, array(), false, $selectbox_field_options_for_mail_attr, false, array(), true );
                                                    ?>
                                                       
                                                </div>
                                            </span>
                                            <span class="arf_conditional_filed_is_operator"><?php echo addslashes(esc_html__('is', 'ARForms')); ?></span>

                                            <span id="arf_conditional_filed_mail_operator" class="arf_conditional_filed_mail_operator">
                                                <div class="sltstandard">

                                                    <?php
                                                    $conditional_mail_value['operator_mail'] = isset($conditional_mail_value['operator_mail']) ? $conditional_mail_value['operator_mail'] : "";
                                                    echo $arfieldhelper->arf_cl_rule_for_conditional_email('arf_conditional_filed_mail_operator_' . $rule_i, 'arf_conditional_filed_mail_operator_' . $rule_i, $conditional_mail_value['operator_mail'],$rule_i,$conditional_mail_value['field_type_mail']);
                                                    ?>
                                                </div>
                                            </span>

                                            <span id="select_ar_conditional_mail_value" class="select_ar_conditional_mail_value">
                                                <input style="width:170px;" type="text" class="txtstandardnew arfheight34" value="<?php echo $conditional_mail_value['value_mail']; ?>" id="arf_conditional_filed_mail_value_<?php echo $rule_i; ?>" onkeyup="this.setAttribute('value',this.value)" name="options[arf_conditional_mail_rules][<?php echo $rule_i; ?>][value_mail]" />
                                            </span>


                                                                                       
                                            <?php
                                            $selectbox_field_options_mail =  array('' => addslashes(esc_html__('Select Field', 'ARForms')));
                                            $selectbox_field_value_label = "";
                                            if (!empty($all_hidden_fields)) {
                                                $cond_email_fields = array_merge($all_hidden_fields, $values['fields']);
                                            } else{
                                                $cond_email_fields = $values['fields'];
                                            }
                                            if (!empty($cond_email_fields)) {
                                                foreach ($cond_email_fields as $val_key => $fo) {
                                                    if (!is_array($fo)) {
                                                        $fo = (array) $fo;                                                 
                                                    }
                                                    if (in_array($fo['type'], array('email', 'text', 'hidden', 'radio', 'select'))) {

                                                        if( isset( $fo['has_parent'] ) && $fo['has_parent'] == 1 && isset( $fo['parent_field_type'] ) && $fo['parent_field_type'] == 'arf_repeater' ){
                                                            continue;
                                                        }

                                                        if (($fo["id"] == $conditional_mail_value['send_mail_field'])) {
                                                            $selectbox_field_value_label = $fo["name"];
                                                        }

                                                        $current_field_id = $fo["id"];
                                                        if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags')=="" ){
                                                            $selectbox_field_options_mail[$current_field_id] = '[Field id : '.$current_field_id.']';
                                                        }else{
                                                            $selectbox_field_options_mail[$current_field_id] = $arfieldhelper->arf_execute_function($fo["name"],'strip_tags');
                                                        }
                                                        
                                                    }
                                                }
                                            }
                                            ?>
                                            <?php if($rule_i == 1){ ?>
                                            <span class="select_ar_conditional_filed_than" id="than_display_title">
                                                <?php echo addslashes(esc_html__('Then Mail Send To', 'ARForms')); ?>
                                            </span>
                                            <?php } ?>
                                            <span  id="select_ar_conditional_filed_span_id" class="arf_first_mail_condition" style="width:180px;">
                                                <div class="sltstandard">

                                                    <?php
                                                        echo $maincontroller->arf_selectpicker_dom( 'options[arf_conditional_mail_rules][' .$rule_i .'][send_mail_field]', 'arf_conditional_mailto_filed_'.$rule_i, 'arf_email_field_dropdown', 'width:211px;', $conditional_mail_value['send_mail_field'], array(), $selectbox_field_options_mail, false, array(), false, array(), false, array(), true );
                                                    ?>
                                                        
                                                </div>
                                            </span>
                                            <span class="arf_conditional_mail_bulk_add_remove">
                                                <span class="bulk_add_mail" onclick="arf_conditional_mail_add_function();"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996 c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314 c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052 C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>
                                                    <?php
                                                    if ($total_rule_array_mail > 1) {
                                                        $display_remove = "display:inline-block;";
                                                    } else {
                                                        $display_remove = "display:none;";
                                                    }
                                                    ?>
                                                <span class="bulk_remove_mail" onclick="arf_conditional_delete_mail_rule('<?php echo $rule_i; ?>')" style="<?php echo $display_remove; ?>"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996 c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341 c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>
                                            </span> 
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="arf_auto_responder_row">
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('From/Replyto Name', 'ARForms')); ?></label>
                                <input type="text" id="options_ar_user_from_name" name="options[ar_user_from_name]" value="<?php echo (isset($values['ar_user_from_name']) && $values['ar_user_from_name'] != '') ? $values['ar_user_from_name'] : $arfsettings->reply_to_name; ?>" <?php echo $auto_responder_disabled; ?>>
                            </div>

                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('From E-mail', 'ARForms')); ?></label>
                                <?php
                                $ar_user_from_email = isset($values['ar_user_from_email']) ? $values['ar_user_from_email'] : '';
                                if ($ar_user_from_email == ''){
                                    $ar_user_from_email = $arfsettings->reply_to;
                                } else {
                                    $ar_user_from_email = $values['ar_user_from_email'];
                                }

                                $ar_user_from_email = $arformhelper->replace_field_shortcode($ar_user_from_email);
                                ?>
                                <input type="text" value="<?php echo $ar_user_from_email; ?>" id="ar_user_from_email" name="options[ar_user_from_email]"<?php echo $auto_responder_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_user_email')" id="add_field_user_email_but" <?php echo $auto_responder_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;
                                    <img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" />
                                </button>
                                <span class="arferrmessage" id="arf_invalid_from_email_user" style="top: 0px; display: none;"><?php esc_html_e('Please enter valid email address', 'ARForms') ?></span>
                                <div class="arf_main_field_modal <?php echo isset($auto_res_email_cls) ? $auto_res_email_cls : ""; ?>">
                                    <div class="arf_add_fieldmodal" id="add_field_user_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_user_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php
                                            if (isset($values['id'])) {
                                                $arfieldhelper->get_shortcode_modal($values['id'], 'ar_user_from_email', 'email', 'style="width:330px;"', false, $field_list);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="arf_auto_responder_row">
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Reply to E-mail', 'ARForms')); ?></label>
                                <?php
                                $ar_user_nreplyto_email = isset($values['ar_user_nreplyto_email']) ? $values['ar_user_nreplyto_email'] : '';

                                if ($ar_user_nreplyto_email == ''){
                                    $ar_user_nreplyto_email = $arfsettings->reply_to_email;
                                } else {
                                    $ar_user_nreplyto_email = $values['ar_user_nreplyto_email'];
                                }

                                $ar_user_nreplyto_email = $arformhelper->replace_field_shortcode($ar_user_nreplyto_email);
                                ?>

                                <input type="text" value="<?php echo $ar_user_nreplyto_email; ?>" id="ar_user_nreplyto_email" name="options[ar_user_nreplyto_email]"<?php echo $auto_responder_disabled; ?> class="arf_advanceemailfield" />

                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_user_nreplyto_email')" id="add_field_user_nreplyto_email_but" <?php echo $auto_responder_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;
                                    <img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" />
                                </button>

                                <div class="arf_main_field_modal <?php echo isset($auto_res_email_cls) ? $auto_res_email_cls : ""; ?>">
                                    <div class="arf_add_fieldmodal" id="add_field_user_nreplyto_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_user_nreplyto_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                        <?php
                                        if (isset($values['id'])) {
                                            $arfieldhelper->get_shortcode_modal($values['id'], 'ar_user_nreplyto_email', 'email', 'style="width:330px;"', false, $field_list);
                                        }
                                        ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>



                        <div class="arf_auto_responder_row">
                            <div class="arf_width_80">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Message', 'ARForms')); ?></label>
                                <?php
                                $ar_email_message = (isset($values['ar_email_message']) and ! empty($values['ar_email_message']) ) ? esc_attr($arformcontroller->br2nl($values['ar_email_message'])) : '';
                                $ar_email_message = $arformhelper->replace_field_shortcode($ar_email_message);

                                $email_editor_settings = array(
                                    'wpautop' => true,
                                    'media_buttons' => false,
                                    'textarea_name' => 'options[ar_email_message]',
                                    'textarea_rows' => '4',
                                    'tinymce' => false,
                                    'editor_class' => "txtmultimodal1 arf_advanceemailfield ar_email_message_content",
                                );

                                wp_editor($ar_email_message, 'ar_email_message', $email_editor_settings);
                                ?>
                                <span class="arferrmessage" id="ar_email_message_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                                <textarea style="display:none;opacity: 0; width:0; height: 0" name="options[ar_email_message]" id="ar_email_message_text"><?php echo $ar_email_message; ?></textarea>
                            </div>
                            <div class="arf_width_20">
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_message')" id="add_field_message_but" <?php echo $auto_responder_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;
                                    <img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" />
                                </button>
                                <div class="arf_main_field_modal" style="top:36px;">
                                    <div class="arf_add_fieldmodal" id="add_field_message">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_message')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arfmodal-body_p">
                                            <?php
                                            if (isset($values['id'])) {                                                
                                                $arfieldhelper->get_shortcode_modal($values['id'], 'ar_email_message', 'no_email', 'style="width:330px;"', false, $field_list);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="clear: both;"></div>
                            <div style="margin-top: 5px;">
                                <div><label><code>[ARF_form_all_values]</code> - <?php echo addslashes(esc_html__('This will be replaced with form\'s all fields & labels.', 'ARForms')); ?></label></div>
                                <div><label><code>[ARF_form_referer]</code> - <?php echo esc_html__('This will be replaced with entry referer.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_added_date_time]</code> - <?php echo esc_html__('This will be replaced with entry added time.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_ipaddress]</code> - <?php echo esc_html__('This will be replaced with IP Address.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_browsername]</code> - <?php echo esc_html__('This will be replaced with user browser name.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_entryid]</code> - <?php echo esc_html__('This will be replaced with Entry ID.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_entrykey]</code> - <?php echo esc_html__('This will be replaced with Entry Key.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_userid]</code> - <?php echo esc_html__('This will be replaced with current login ID.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_username]</code> - <?php echo esc_html__('This will be replaced with current login user name.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_useremail]</code> - <?php echo esc_html__('This will be replaced with current login user email.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_page_url]</code> - <?php echo esc_html__('This will be replaced with current form\'s page URL.', 'ARForms'); ?></label></div>
                                <div><label><code>[form_name]</code> - <?php echo addslashes(esc_html__('This will be replaced with form name.', 'ARForms')); ?></label></div>
                                <div><label><code>[site_name]</code> - <?php echo addslashes(esc_html__('This will be replaced with name of site.', 'ARForms')); ?></label></div>
                                <div><label><code>[site_url]</code> - <?php echo addslashes(esc_html__('This will be replaced with the URL of site.', 'ARForms')); ?></label></div>

                                <?php do_action('arf_add_auto_response_mail_shortcode_in_out_side', $id, $values); ?>
                            </div>
                        </div>
                    </div>

                    <div class="arf_separater"></div>

                    <div class="arf_popup_checkbox_wrapper">
                        <div class="arf_custom_checkbox_div">
                            <div class="arf_custom_checkbox_wrapper" onclick="CheckAdminAutomaticResponseEnableDisable();" style="margin-right: 9px;">
                                <?php $arf_checked = isset($values['chk_admin_notification']) ? $values['chk_admin_notification'] : 0; ?>
                                <input type="checkbox" name="options[chk_admin_notification]" id="chk_admin_notification" value="1" <?php checked($arf_checked, 1); ?>  />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                <?php unset($arf_checked); ?>
                                </svg>
                            </div>
                        <span><label id="arf_admin_notification" for="chk_admin_notification" class="arffont16"><?php echo esc_html__('Send an automatic response to admin user after form submission.', 'ARForms'); ?></label></span>
                        </div>
                    </div>

                    <div class="arf_admin_notification_content arfmarginl10" style="width:100%;">
                        <div class="arf_auto_responder_row ">
                            <div class="arf_auto_responder_column">
                                <?php
                                $chk_admin_notification_disabled = "disabled='disabled'";
                                if (isset($values['chk_admin_notification']) && $values['chk_admin_notification'] > 0) {
                                    $chk_admin_notification_disabled = "";
                                    
                                }
                                $ar_admin_to_email = isset($values['notification'][0]['reply_to']) ? esc_attr($values['notification'][0]['reply_to']) : '';
                                if ($ar_admin_to_email == '') {
                                    $ar_admin_to_email = $arfsettings->reply_to;
                                } else {
                                    $ar_admin_to_email = $values['notification'][0]['reply_to'];
                                }
                                $ar_admin_to_email = $arformhelper->replace_field_shortcode($ar_admin_to_email);
                                ?>
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Admin E-mail', 'ARForms')); ?></label>
                                <input type="text" name="options[reply_to]" id="options_admin_reply_to_notification" value="<?php echo $ar_admin_to_email; ?>" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_email_to')" id="add_field_admin_email_but_to"  <?php echo $chk_admin_notification_disabled; ?> ><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_email_to">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_email_to')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'options_admin_reply_to_notification', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Subject E-mail', 'ARForms')); ?></label>
                                <?php
                                $admin_email_subject_value = (isset($values['admin_email_subject'])) ? esc_attr($values['admin_email_subject']) : '';
                                if ($admin_email_subject_value == '') {
                                    $admin_email_subject_value = '[form_name] Form submitted on [site_name]';
                                } else {
                                    $admin_email_subject_value = $values['admin_email_subject'];
                                }
                                ?>
                                <input type="text" name="options[admin_email_subject]" id="admin_email_subject" value="<?php echo $admin_email_subject_value; ?>" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_email_subject')" id="add_field_admin_email_but_subject"  <?php echo $chk_admin_notification_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_email_subject">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_email_subject')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'admin_email_subject', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top: 5px;">
                                    <div><label><code>[form_name]</code> - <?php echo addslashes(esc_html__('This will be replaced with form name.', 'ARForms')); ?></label></div>
                                    <div><label><code>[site_name]</code> - <?php echo addslashes(esc_html__('This will be replaced with name of site.', 'ARForms')); ?></label></div>
                                </div>
                            </div>
                        </div>
                        <div class="arf_auto_responder_row">
                           <div class="arf_auto_responder_column">
                                <?php
                                $chk_admin_notification_disabled = "disabled='disabled'";
                                if (isset($values['chk_admin_notification']) && $values['chk_admin_notification'] > 0) {
                                    $chk_admin_notification_disabled = "";
                                    
                                }
                                
                                $ar_admin_cc_email = isset($values['admin_cc_email']) ? esc_attr($values['admin_cc_email']) : '';
                                if ($ar_admin_cc_email == '') {
                                    $ar_admin_cc_email = '';
                                } else {
                                    $ar_admin_cc_email = $values['admin_cc_email'];
                                }
                                $ar_admin_cc_email = $arformhelper->replace_field_shortcode($ar_admin_cc_email);
                                ?>
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Admin CC Email', 'ARForms')); ?></label>
                                <input type="text" name="options[admin_cc_email]" id="options_admin_cc_email_notification" value="<?php echo $ar_admin_cc_email; ?>" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_cc_email')" id="add_field_admin_cc_email_but_to"  <?php echo $chk_admin_notification_disabled; ?> ><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_cc_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_cc_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'options_admin_cc_email_notification', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                           <div class="arf_auto_responder_column">
                                <?php
                                $chk_admin_notification_disabled = "disabled='disabled'";
                                if (isset($values['chk_admin_notification']) && $values['chk_admin_notification'] > 0) {
                                    $chk_admin_notification_disabled = "";
                                    
                                }
                                $ar_admin_bcc_email = isset($values['admin_bcc_email']) ? esc_attr($values['admin_bcc_email']) : '';
                                if ($ar_admin_bcc_email == '') {
                                    $ar_admin_bcc_email = '';
                                } else {
                                    $ar_admin_bcc_email = $values['admin_bcc_email'];
                                }
                                $ar_admin_bcc_email = $arformhelper->replace_field_shortcode($ar_admin_bcc_email);
                                ?>
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Admin BCC Email', 'ARForms')); ?></label>
                                <input type="text" name="options[admin_bcc_email]" id="options_admin_bcc_email_notification" value="<?php echo $ar_admin_bcc_email; ?>" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_bcc_email')" id="add_field_admin_bcc_email_but_to"  <?php echo $chk_admin_notification_disabled; ?> ><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_bcc_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_bcc_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'options_admin_bcc_email_notification', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          
                            
                        </div>
                        <div class="arf_auto_responder_row">
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('From/Replyto Name', 'ARForms')); ?></label>
                                <input type="text" id="options_ar_admin_from_name" name="options[ar_admin_from_name]" value="<?php echo (isset($values['ar_admin_from_name']) && $values['ar_admin_from_name'] != '') ? $values['ar_admin_from_name'] : $arfsettings->reply_to_name; ?>" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" >
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_from_name')" id="add_field_admin_from_but_name"  <?php echo $chk_admin_notification_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_from_name">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_from_name')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'options_ar_admin_from_name', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('From E-mail', 'ARForms')); ?></label>
                                <?php
                                $ar_admin_from_email = isset($values['ar_admin_from_email']) ? $values['ar_admin_from_email'] : '';
                                if ($ar_admin_from_email == '') {
                                    $ar_admin_from_email = $arfsettings->reply_to;
                                } else {
                                    $ar_admin_from_email = $values['ar_admin_from_email'];
                                }
                                $ar_admin_from_email = $arformhelper->replace_field_shortcode($ar_admin_from_email);
                                ?>
                                <input type="text" value="<?php echo $ar_admin_from_email; ?>" id="ar_admin_from_email" name="options[ar_admin_from_email]" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_email')" id="add_field_admin_email_but"  <?php echo $chk_admin_notification_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <span class="arferrmessage" id="arf_invalid_from_email_admin" style="top: 0px; display: none;"><?php esc_html_e('Please enter valid email address', 'ARForms') ?></span>
                                <span class="arferrmessage" id="arf_shortcode_from_email_admin" style="top: 0px; display: none;"><?php esc_html_e('Please use only one shortcode', 'ARForms') ?></span>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'ar_admin_from_email', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="arf_auto_responder_row">
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Reply to E-mail', 'ARForms')); ?></label>
                                <?php
                                $ar_admin_reply_to_email = isset($values['ar_admin_reply_to_email']) ? $values['ar_admin_reply_to_email'] : '';
                                if ($ar_admin_reply_to_email == '')
                                    $ar_admin_reply_to_email = $arfsettings->reply_to_email;
                                else
                                    $ar_admin_reply_to_email = $values['ar_admin_reply_to_email'];

                                $ar_admin_reply_to_email = $arformhelper->replace_field_shortcode($ar_admin_reply_to_email);
                                ?>

                                <input type="text" value="<?php echo $ar_admin_reply_to_email; ?>" id="ar_admin_reply_to_email" name="options[ar_admin_reply_to_email]"<?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />

                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_nreplyto_email')" id="add_field_admin_nreplyto_email_but" <?php echo $chk_admin_notification_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;
                                    <img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" />
                                </button>

                                <div class="arf_main_field_modal <?php echo isset($auto_res_email_cls) ? $auto_res_email_cls : ""; ?>">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_nreplyto_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_nreplyto_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                        <?php
                                        if (isset($values['id'])) {
                                            $arfieldhelper->get_shortcode_modal($values['id'], 'ar_admin_reply_to_email', 'email', 'style="width:330px;"', false, $field_list);
                                        }
                                        ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="arf_auto_responder_row">
                            <div class="arf_width_80">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Admin Message', 'ARForms')); ?></label>
                                <div>
                                <?php
                                $ar_admin_email_message = (isset($values['ar_admin_email_message']) and ! empty($values['ar_admin_email_message']) ) ? esc_attr($arformcontroller->br2nl($values['ar_admin_email_message'])) : '';
                                $ar_admin_email_message = $arformhelper->replace_field_shortcode($ar_admin_email_message);
                                $email_editor_settings = array(
                                    'wpautop' => true,
                                    'media_buttons' => false,
                                    'textarea_name' => 'options[ar_admin_email_message]',
                                    'textarea_rows' => '4',
                                    'tinymce' => false,
                                    'editor_class' => "txtmultimodal1 arf_advanceemailfield ar_admin_email_message_content",
                                );
                                wp_editor($ar_admin_email_message, 'ar_admin_email_message', $email_editor_settings);
                                ?>
                                <textarea style="display:none;opacity: 0; width:0; height: 0" name="options[ar_admin_email_message]" id="ar_admin_email_message_text"><?php echo $ar_admin_email_message; ?></textarea>
                                </div>
                            </div>
                            <div class="arf_width_20">
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_message')" id="add_field_admin_message_but"  <?php echo $chk_admin_notification_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal" style="margin-top:-21px;">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_message">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_message')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'ar_admin_email_message', 'no_email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="arferrmessage" id="ar_admin_email_message_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                            <div style="margin-top: 5px;clear: both;">
                                <div><label><code>[ARF_form_all_values]</code> - <?php echo addslashes(esc_html__('This will be replaced with form\'s all fields & labels.', 'ARForms')); ?></label></div>
                                <div><label><code>[ARF_form_referer]</code> - <?php echo esc_html__('This will be replaced with entry referer.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_added_date_time]</code> - <?php echo esc_html__('This will be replaced with entry added time.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_ipaddress]</code> - <?php echo esc_html__('This will be replaced with IP Address.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_browsername]</code> - <?php echo esc_html__('This will be replaced with user browser name.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_entryid]</code> - <?php echo esc_html__('This will be replaced with Entry ID.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_entrykey]</code> - <?php echo esc_html__('This will be replaced with Entry Key.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_userid]</code> - <?php echo esc_html__('This will be replaced with current login ID.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_username]</code> - <?php echo esc_html__('This will be replaced with current login user name.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_useremail]</code> - <?php echo esc_html__('This will be replaced with current login user email.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_page_url]</code> - <?php echo esc_html__('This will be replaced with current form\'s page URL.', 'ARForms'); ?></label></div>
                                <div><label><code>[form_name]</code> - <?php echo addslashes(esc_html__('This will be replaced with form name.', 'ARForms')); ?></label></div>
                                <div><label><code>[site_name]</code> - <?php echo addslashes(esc_html__('This will be replaced with name of site.', 'ARForms')); ?></label></div>
                                <div><label><code>[site_url]</code> - <?php echo addslashes(esc_html__('This will be replaced with the URL of site.', 'ARForms')); ?></label></div>
                                <?php do_action('arf_add_admin_mail_shortcode_in_outside', $id, $values); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php do_action('arf_additional_autoresponder_settings', $id, $values); ?>
                    <?php do_action('arf_after_autoresponder_settings_container', $id, $values); ?>
                </div>


                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_mail_notification_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>
        <!-- Auto Response email -->

        <!--- Conditional Logic pop-up -->
        <div class="arf_modal_overlay">
            <div id="arf_conditional_logic_model" class="arf_popup_container arf_popup_container_conditional_logic_model" style="">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Conditional Rule', 'ARForms')); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_conditional_rule_model">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>
                <div class="arf_popup_content_container arf_submit_popup_container">
                    <!-- content start-->
                    <div class="arf_popup_container_loader">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div style="<?php echo (is_rtl()) ? 'float: left;position:relative;' : 'float: right; position: relative;'; ?>clear: both;">
                        <a href="<?php echo ARFURL; ?>/documentation/index.html#conditional_logic" target="_blank" class="arf_adminhelp_icon tipso_style" data-tipso="help">
                            <svg width="30px" height="30px" viewBox="0 0 26 32" class="arfsvgposition arfhelptip tipso_style" data-tipso="help" title="help">
                                <?php echo ARF_LIFEBOUY_ICON;?>
                            </svg>
                        </a>
                    </div>
                    <?php include 'arf_conditional_logic.php'; ?>
                    <!--content over-->
                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_conditional_rule_model" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>

            </div>
        </div>
        <!-- conditional logic over -->


        <!-- Submit Action Model -->
        <div class="arf_modal_overlay">
            <div id="arf_submit_action_model" class="arf_popup_container arf_popup_container_submit_action_model">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Submit Action', 'ARForms')); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_submit_popup_button">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>
                <div class="arf_popup_content_container arf_submit_action_container">
                    <div class="arf_popup_container_loader">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <p class="arftitle_p">
                        <label><?php echo addslashes(esc_html__('Form submission action', 'ARForms')); ?></label>
                        <label style="<?php echo (is_rtl()) ? 'float: left;position:relative;' : 'float: right;position: relative;'; ?>">
                            <a href="<?php echo ARFURL; ?>/documentation/index.html#form_submit_act" target="_blank" class="arf_adminhelp_icon tipso_style" data-tipso="help">
                                <svg width="30px" height="30px" viewBox="0 0 26 32" class="arfsvgposition arfhelptip tipso_style" data-tipso="help" title="help">
                                    <?php echo ARF_LIFEBOUY_ICON;?>
                                </svg>
                            </a>
                        </label>
                    </p>
                    
                    <div class="arf_submit_action_options" style="margin-left: 10px;margin-top: -2px;<?php echo(is_rtl())?'margin-right: -20px':'';?>">
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div">
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" class="arf_custom_radio arf_submit_action" name="options[success_action]" id="success_action_message" value="message" <?php checked($values['success_action'], 'message'); ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label id="success_action_message" for="success_action_message"><?php echo addslashes(esc_html__('Display a Message', 'ARForms')); ?></label>
                            </span>
                        </div>
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div">
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" name="options[success_action]" id="success_action_redirect" class="arf_submit_action arf_custom_radio" value="redirect" <?php checked($values['success_action'], 'redirect'); ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label id="success_action_redirect" for="success_action_redirect"><?php echo esc_html__('Redirect to URL', 'ARForms'); ?></label>
                            </span>
                        </div>
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div" >
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" name="options[success_action]" id="success_action_page" class="arf_submit_action arf_custom_radio" value="page" <?php checked($values['success_action'], 'page'); ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label id="success_action_page" for="success_action_page"><?php echo esc_html__('Display content from another page', 'ARForms'); ?></label>
                            </span>
                        </div>
                    </div>

                    <div id="arf_success_action_message" class="arf_optin_tab_inner_container arfmarginl15 arf_submit_action_inner_container <?php echo ($values['success_action'] == 'message') ? 'arfactive' : ''; ?>">
                        <div class="arfcolumnleft arfsettingsubtitle"><label for="success_msg" class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Confirmation Message', 'ARForms')); ?></label></div>
                        <div class="arfcolumnright fix_height">
                            <textarea id="success_msg" class="auto_responder_webform_code_area txtmultimodal1" name="options[success_msg]" cols="2" rows="4"><?php echo $values['success_msg']; ?></textarea>
                            <span class="arferrmessage" id="success_msg_error"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                        </div>
                    </div>


                    <div id="arf_success_action_redirect" class="arf_optin_tab_inner_container arfmarginl15 arf_submit_action_inner_container <?php echo ($values['success_action'] == 'redirect') ? 'arfactive' : ''; ?>">
                        <label for="success_url" class="arf_dropdown_autoresponder_label"><?php echo esc_html__('Set Static Redirect URL', 'ARForms'); ?></label>
                        <input type="text" id="success_url" class="arf_large_input_box arf_redirect_to_url success_url_width" name="options[success_url]" value="<?php echo isset($values['success_url']) ? $values['success_url'] : ''; ?>" />
                        <span class="arferrmessage" id="success_url_error" style='top:0;'><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                        <br/><i class="arf_notes" style="float: left;width: 100%;"><?php echo esc_html__('Please insert url with http:// or https://.', 'ARForms'); ?></i>
                        <?php do_action('arf_form_submit_after_redirect_to_url', $id, $values); ?>
                        <div class="arfcolumnleft arf_custom_margin_redirect arfsetcondtionalredirect">
                            <div class="arf_custom_checkbox_div">
                                <div class="arf_custom_checkbox_wrapper">
                                    <input type="checkbox" value="1" name="options[arf_data_with_url]" class="chkstanard" id="arf_sa_data_with_url" <?php isset($values['arf_data_with_url']) ? checked($values['arf_data_with_url'], 1) : ''; ?>>
                                    <svg width="18px" height="18px"><path id="arfcheckbox_unchecked" d="M15.643,17.617H3.499c-1.34,0-2.427-1.087-2.427-2.429V3.045  c0-1.341,1.087-2.428,2.427-2.428h12.144c1.342,0,2.429,1.087,2.429,2.428v12.143C18.072,16.53,16.984,17.617,15.643,17.617z   M16.182,2.477H2.961v13.221h13.221V2.477z"></path><path id="arfcheckbox_checked" d="M15.645,17.62H3.501c-1.34,0-2.427-1.087-2.427-2.429V3.048  c0-1.341,1.087-2.428,2.427-2.428h12.144c1.342,0,2.429,1.087,2.429,2.428v12.143C18.074,16.533,16.986,17.62,15.645,17.62z   M16.184,2.48H2.963v13.221h13.221V2.48z M5.851,7.15l2.716,2.717l5.145-5.145l1.718,1.717l-5.146,5.145l0.007,0.007l-1.717,1.717  l-0.007-0.008l-0.006,0.008l-1.718-1.717l0.007-0.007L4.134,8.868L5.851,7.15z"></path></svg>
                                </div>
                                <span>
                                    <label for="arf_sa_data_with_url" style="margin-left: 4px;"><?php echo addslashes(esc_html__('Send form data to redirected page/post','ARForms')); ?></label><br/>
                                </span>
                            </div>
                        </div>
                        <i class="arf_notes" style="float: left;width: 100%;margin-left:30px;font-size:13px;margin-top:-5px;margin-bottom:5px;">(<?php echo esc_html__('When the form has been successfully submitted, it will send data to the redirected page/post with the method you will choose below.', 'ARForms'); ?>)</i>

                        <?php
                            $method_type = "POST";
                            if(isset($values["arf_data_with_url_type"]) && $values["arf_data_with_url_type"] == "GET") {
                                $method_type = "GET";
                            }
                        ?>

                        <div class="arf_submit_action_options arf_data_with_url_type_wrapper" style="<?php echo(is_rtl())?'margin-right: -20px':''; echo (isset($values['arf_data_with_url']) && $values['arf_data_with_url'] == "1") ? 'display: block;':''; ?>">
                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" name="options[arf_data_with_url_type]" id="arf_data_with_url_post_type" class="arf_custom_radio" value="POST" <?php echo ($method_type == "POST") ? 'checked' : '' ?> />
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                        </svg>
                                    </div>
                                </div>
                                <span>
                                    <label id="arf_data_with_url_post_type" for="arf_data_with_url_post_type"><?php echo esc_html__('POST method', 'ARForms'); ?></label>
                                </span>
                            </div>
                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" class="arf_custom_radio" name="options[arf_data_with_url_type]" id="arf_data_with_url_get_type" value="GET" <?php echo ($method_type == "GET") ? 'checked' : '' ?> />
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                        </svg>
                                    </div>
                                </div>
                                <span>
                                    <label id="arf_data_with_url_get_type" for="arf_data_with_url_get_type"><?php echo addslashes(esc_html__('GET method', 'ARForms')); ?></label>
                                </span>
                            </div>
                            
                        </div>
                        
                        <div class="arf_chnge_field_key_container arf_submit_action_options" style="<?php echo(is_rtl())?'margin-right: -20px':''; echo (isset($values['arf_data_with_url']) && $values['arf_data_with_url'] == "1") ? 'display: block;':''; ?>">
                            <div class="arf_custom_checkbox_div">
                            <input type="hidden" value="1" name="options[arf_data_key_with_url]" id="arf_sa_data_key_with_url">
                                <span>
                                    <label for="arf_sa_data_key_with_url" style="margin-left: 4px;"><?php echo addslashes(esc_html__('Field names for submitted form data','ARForms')); ?></label><br/>
                                </span>
                            </div>
                            <?php $item_meta_note = "<b>item_meta_{field_id}</b>"; ?>
                            <i class="arf_notes" style="float: left;width: 90%;margin:5px 25px;"><?php echo sprintf(esc_html__('%sNote:%s If you fill blank value then by default %sitem_meta_{field_id}%s name will be passed. Also, please note that value of Checkbox field(s) and values of those fields which are placed inside the repeater field(s) will be passed as an array.', 'ARForms'), "<b>","</b>","<b>","</b>"); ?></i>
                        </div>

                        <div id="arf_field_list_name" class="arf_submit_action_options arf_field_list_name" style="<?php echo(is_rtl())?'margin-right: -20px':''; echo (isset($values['arf_data_key_with_url']) && $values['arf_data_key_with_url'] == "1") ? 'display: block;':'display: none;'; ?>">
                          
                                <?php
                                global $conditional_logic_array_if;
                                $act_exclude = array('divider', 'section', 'break', 'captcha', 'imagecontrol', 'confirm_email', 'confirm_password', 'signature', 'file', 'arf_repeater');
                                $arf_url_field_list_option = "";
                                if (!empty($values['fields'])) {                                  
                                    
                                    foreach ($values['fields'] as $val_key => $fo) {
                                        
					                    /* reputelog - please check this condition */
                                        if($fo['type'] == 'arfslider' && $fo['arf_range_selector'] == 1) {
                                            continue;
                                        }

                                        if ($fo['type'] == 'html' && isset( $fo['enable_total'] ) && $fo['enable_total'] == 0) {
                                            continue;
                                        }

                                        if( isset( $fo['type2'] )  && 'ccfield' == $fo['type2'] ){
                                            continue;
                                        }

                                        if(!in_array($fo['type'], $conditional_logic_array_if)){

                                            $current_field_id = $fo["id"];
                                            $arf_field_key_name = isset($values['arf_field_key_name'])?arf_json_decode($values['arf_field_key_name'], true):'';
                                            $post_selected_fields = isset($values["arf_select_post_fields"]) ? arf_json_decode($values['arf_select_post_fields'], true) : '';


                                            if(!empty($arf_field_key_name)) {
                                                $arf_field_key_name_input = '';
                                                foreach ($arf_field_key_name as $key => $value) {
                                                    if ($key == $current_field_id ) {
                                                        $arf_field_key_name_input = isset($value) ? $value :'item_meta_'.$current_field_id;
                                                    }
                                                }
                                                $post_selected_fields_input = '';
                                                foreach ($post_selected_fields as $key1 => $value1) {
                                                    if ($key1 == $current_field_id ) {
                                                        $post_selected_fields_input = isset($value1) ? $value1 :'';
                                                    }
                                                }    

                                                $arf_url_field_list_option .=  '<div class="arf_set_url_fields"><div class="arf_url_field_list"><div class="arf_custom_checkbox_wrapper arf_select_post_fields"><input type="checkbox" name="options[arf_select_post_fields]['.$current_field_id.']" id="redirect_field_list_'.$current_field_id.'" value="1" '. checked( $post_selected_fields_input, 1, false ) .' /><svg width="18px" height="18px">'. ARF_CUSTOM_UNCHECKED_ICON.' '.ARF_CUSTOM_CHECKED_ICON.'</svg></div><label id="redirect_field_list_'.$current_field_id.'" class="arf_selectbox_option" for="redirect_field_list_'.$current_field_id.'">'.$arfieldhelper->arf_execute_function($fo["name"],'strip_tags').'</label></div><div class="arf_url_field_list" ><input  type="text" name="options[arf_data_with_url_data]['.$current_field_id.']" id="arf_field_key_name_'.$current_field_id.'" value="'.$arf_field_key_name_input.'"></div></div>'; 

                                            } else {
                                                if(!empty($field_key_nm)) {
                                                    foreach ($field_key_nm as $key => $value) {
                                                        if ($key == $current_field_id ) {
                                                            $arf_field_key_name = isset($value) ? $value :'item_meta_'.$current_field_id;
                                                        }
                                                    }

                                                    foreach ($post_selected_fields as $key1 => $value1) {
                                                        if ($key1 == $current_field_id ) {
                                                            $post_selected_fields = isset($value1) ? $value1 :'';
                                                        }
                                                    }
                                                    
                                                    $arf_url_field_list_option .=  '<div class="arf_set_url_fields"><div class="arf_url_field_list"><div class="arf_custom_checkbox_wrapper arf_select_post_fields"><input type="checkbox" name="options[arf_select_post_fields]['.$current_field_id.']" id="redirect_field_list_'.$current_field_id.'" value="1" '. checked( $post_selected_fields, 1, false ) .' /><svg width="18px" height="18px">'. ARF_CUSTOM_UNCHECKED_ICON.' '.ARF_CUSTOM_CHECKED_ICON.'</svg></div><label for="redirect_field_list_'.$current_field_id.'" id="redirect_field_list_'.$current_field_id.'" class="arf_selectbox_option">'.$arfieldhelper->arf_execute_function($fo["name"],'strip_tags').'</label></div><div class="arf_url_field_list" ><input  type="text" name="options[arf_data_with_url_data]['.$current_field_id.']" id="arf_field_key_name_'.$current_field_id.'" value="'.$arf_field_key_name.'"></div></div>'; 

                                                }
                                            }

                                        }
                                        
                                    }
                                }?>
                                <div>
                                    <div class="arf_url_field_list_label" id="arfkflist">
                                        <label class="arftitle_p" style="padding-left: 40px;">Fields</label>
                                    </div>
                                    <div class="arf_url_field_list_label" >
                                        <label class="arftitle_p">Fields Key</label>
                                    </div>
                                    
                                    <?php echo $arf_url_field_list_option; ?>
                                </div>
                            
                        </div>  

                        <?php
                            $arf_redirect_url_to = isset($values["arf_redirect_url_to"]) ? $values["arf_redirect_url_to"] : "same_tab";
                        ?>

                        <div class="arfcolumnleft arf_custom_margin_redirect arfsetcondtionalredirect">

                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" name="options[arf_redirect_url_to]" id="arf_redirect_url_to_same_tab" class="arf_custom_radio" value="same_tab" <?php echo ($arf_redirect_url_to == "same_tab") ? 'checked' : '' ?> />
                                        <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>                                   
                                        </svg>
                                    </div>
                                </div>
                                <span>
                                    <label id="arf_redirect_url_to_same_tab" for="arf_redirect_url_to_same_tab"><?php echo esc_html__('Same tab', 'ARForms'); ?></label>
                                </span>
                            </div>

                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" class="arf_custom_radio" name="options[arf_redirect_url_to]" id="arf_redirect_url_to_new_tab" value="new_tab" <?php echo ($arf_redirect_url_to == "new_tab") ? 'checked' : '' ?> />
                                        <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>                                    
                                        </svg>
                                    </div>
                                </div>
                                <span>
                                    <label id="arf_redirect_url_to_new_tab" for="arf_redirect_url_to_new_tab"><?php echo addslashes(esc_html__('New tab', 'ARForms')); ?></label>
                                </span>
                            </div>

                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" class="arf_custom_radio" name="options[arf_redirect_url_to]" id="arf_redirect_url_to_new_window" value="new_window" <?php echo ($arf_redirect_url_to == "new_window") ? 'checked' : '' ?> />
                                        <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>                                  
                                        </svg>
                                    </div>
                                </div>
                                <span>
                                    <label id="arf_redirect_url_to_new_window" for="arf_redirect_url_to_new_window"><?php echo addslashes(esc_html__('New window', 'ARForms')); ?></label>
                                </span>
                            </div>

                        </div>  

                    </div>

                    <div id="arf_success_action_page" class="arf_optin_tab_inner_container arfmarginl15 arf_submit_action_inner_container <?php echo ($values['success_action'] == 'page') ? 'arfactive' : ''; ?>">
                        <div class="arf_ar_dropdown_wrapper">
                            <label class="arf_dropdown_autoresponder_label" id="arf_use_content_from_page" style="margin-top: 10px;"><?php echo addslashes(esc_html__('Select Page', 'ARForms')); ?></label>
                            <?php $armainhelper->wp_pages_dropdown('options[success_page_id]', isset($values['success_page_id']) ? $values['success_page_id'] : "", '', 'option_success_page_id'); ?>
                            <span class="arferrmessage" id="option_success_page_id_error" style='top:0;'><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                        </div>
                    </div>
                   
                    <div class="arf_popup_checkbox_wrapper arf_hide_form_sub" style="margin-left: 11px;margin-top:10px;">
                        <div class="arf_custom_checkbox_div" style="margin-top: 4px;">
                            <div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" name="options[arf_form_hide_after_submit]" id="arf_hide_form_after_submitted" value="1" <?php isset($values['arf_form_hide_after_submit']) ? checked($values['arf_form_hide_after_submit'], 1) : ''; ?> />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
                            <span><label id="arf_hide_form_after_submitted" for="arf_hide_form_after_submitted" style="margin-left: 4px;"><?php echo addslashes(esc_html__('Hide Form after submission', 'ARForms')); ?></label></span>
                        </div>
                    </div>

                    <?php do_action('arf_option_before_submit_conditional_logic', $id, $values);  ?>

                    <div class="arf_separater" style="margin-top: 15px;width:98%;"></div>

                    <div class="submit_action_conditonal_law" style="margin-top: -15px;margin-left: 6px;">                        
                        <div class="field_conditional_law field_basic_option arf_fieldoptiontab" style="display:block;">
                            <?php
                            $cl_submit_conditional_login = ( isset($values['submit_conditional_logic']) ) ? $values['submit_conditional_logic'] : array();
                            $cl_rules_array = ( isset($cl_submit_conditional_login['rules']) ) ? $cl_submit_conditional_login['rules'] : array();
                            $cl_submit_conditional_login['enable'] = (isset($cl_submit_conditional_login['enable']) && count($cl_rules_array) > 0) ? $cl_submit_conditional_login['enable'] : 0;

                            ?>
                            <div class="arf_enable_conditional_submit_div" <?php echo(is_rtl())?'style="margin-right:1px;"':'' ?>>
                                <div class="arf_custom_checkbox_div">
                                    <div class="arf_custom_checkbox_wrapper">
                                        <input type="checkbox" class="" name="conditional_logic_arfsubmit" id="conditional_logic_arfsubmit" onchange="arf_cl_change('arfsubmit');" value="<?php echo $cl_submit_conditional_login['enable']; ?>" <?php checked($cl_submit_conditional_login['enable'], 1) ?> />
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                        </svg>
                                    </div>
                                    <span>
                                        <label for="conditional_logic_arfsubmit" class="arftitle_p" style="margin-left: 4px;font-size: 16px !important; margin-top: 3px;"><?php echo addslashes(esc_html__('Configure conditional submission', 'ARForms')); ?></label>
                                    </span>
                                </div>
                            </div>
                            <div id="conditional_logic_div_arfsubmit" style="<?php
                            if (count($cl_rules_array) == 0) {
                                echo 'display:none;';
                            }
                            ?>">
                                <div class="arflabeltitle" style="margin-top: 27px;">
                                    <div class="sltstandard <?php if (count($cl_rules_array) == 0) { echo ' arfhelptip'; } ?>" <?php if (count($cl_rules_array) == 0) { ?>title="<?php echo addslashes(esc_html__('Please add one or more rules', 'ARForms')); ?>"<?php } ?> >
                                        <?php
                                            $selected_list_label = addslashes(esc_html__('Enable', 'ARForms'));
                                            if (isset($cl_submit_conditional_login['display'])) {
                                                if ($cl_submit_conditional_login['display'] == 'show') {
                                                    $selected_list_label = addslashes(esc_html__('Enable', 'ARForms'));
                                                }
                                                if ($cl_submit_conditional_login['display'] == 'hide') {
                                                    $selected_list_label = addslashes(esc_html__('Disable', 'ARForms'));
                                                }
                                            }

                                            $arf_cl_submit_opts = array(
                                                'show' => addslashes( esc_html__( 'Enable', 'ARForms' ) ), 
                                                'hide' => addslashes( esc_html__( 'Disable', 'ARForms' ) )
                                            );

                                            $default_cl_opt = isset( $cl_submit_conditional_login['display'] ) ? $cl_submit_conditional_login['display'] : 'show';

                                            echo $maincontroller->arf_selectpicker_dom( 'conditional_logic_display_arfsubmit', 'conditional_logic_display_arfsubmit', '', 'width:100px', $default_cl_opt, array(), $arf_cl_submit_opts );
                                        ?>
                                    </div>
                                    <span class="if_lable"><label id="txtmultimodal1" class="arf_dropdown_autoresponder_label"><?php echo esc_html__('submit button if', 'ARForms'); ?></label></span>
                                    <div class="sltstandard <?php if (count($cl_rules_array) == 0) { echo ' arfhelptip'; } ?>" <?php if (count($cl_rules_array) == 0) { ?>title="<?php echo addslashes(esc_html__('Please add one or more rules', 'ARForms')); ?>"<?php } ?>>
                                        <?php
                                            $selected_list_label = addslashes(esc_html__('All', 'ARForms'));
                                            if (isset($cl_submit_conditional_login['if_cond'])) {
                                                if ($cl_submit_conditional_login['if_cond'] == 'all') {
                                                    $selected_list_label = addslashes(esc_html__('All', 'ARForms'));
                                                }
                                                if ($cl_submit_conditional_login['if_cond'] == 'any') {
                                                    $selected_list_label = addslashes(esc_html__('Any', 'ARForms'));
                                                }
                                            } else {
                                                $cl_submit_conditional_login['if_cond'] = 'all';
                                            }

                                            $arf_submit_cl_oprator_opts = array(
                                                'all' => addslashes( esc_html__( 'All', 'ARForms' ) ),
                                                'any' => addslashes( esc_html__( 'Any', 'ARForms' ) )
                                            );

                                            echo $maincontroller->arf_selectpicker_dom( 'conditional_logic_if_cond_arfsubmit', 'conditional_logic_if_cond_arfsubmit', '', 'width:100px;', $cl_submit_conditional_login['if_cond'], array(), $arf_submit_cl_oprator_opts );
                                        ?>
                                    </div>
                                    <div class="arf_conditional_submission_field_dropdown arf_name_field_dropdown" id="arf_conditional_submission_field_dropdown_html" style="display: none;">
                                        <?php
                                        global $conditional_logic_array_if;
                                        $conditional_submission_options = '';
                                        if (!empty($values['fields'])) {
                                            foreach ($values['fields'] as $val_key => $fo) {
                                                if (!in_array($fo['type'], $conditional_logic_array_if)) {
                                                    if( ( isset( $fo['parent_field_type'] ) && $fo['parent_field_type'] == 'arf_repeater' ) || ( isset( $fo['type2'] ) && 'ccfield' == $fo['type2'] ) ){
                                                        continue;
                                                    }
                                                    $current_field_id = $fo["id"];
                                                    if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags')==""){
                                                        $conditional_submission_options .= '<li class="arf_selectbox_option" data-type="'.$fo['type'].'" data-value="' . $current_field_id . '" data-label="[Field Id:'.$current_field_id.']">[Field Id:'.$current_field_id.']</li>';
                                                    }else{
                                                        $conditional_submission_options .= '<li class="arf_selectbox_option" data-type="'.$fo['type'].'" data-value="' . $current_field_id . '" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                                                    }
                                                    
                                                }
                                            }
                                        }
                                        ?>
                                        <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                        <?php echo $conditional_submission_options; ?>
                                    </div>

                                    <span class="if_lable"><label id="txtmultimodal1" class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('of the following match', 'ARForms')); ?></label></span>
                                    <div class="button_div">
                                        <button type="button" id="arf_new_law_arfsubmit" onclick="arf_add_new_law('arfsubmit');"  class="rounded_button arf_btn_dark_blue arfaddnewrule" style=" <?php
                                        if ($cl_submit_conditional_login['enable'] == 1 && count($cl_rules_array) > 0) {
                                            echo 'display:none;';
                                        }
                                        ?>"><?php echo addslashes(esc_html__('Add new condition', 'ARForms')); ?></button>
                                        <div id="logic_rules_div_arfsubmit" class="logic_rules_div" style=" <?php
                                        if ($cl_submit_conditional_login['enable'] == 0) {
                                            echo 'display:none;';
                                        }
                                        ?>">
                                        <span style="<?php echo (is_rtl()) ? 'float:right;' : 'float: left;';?> font-size: 14px; line-height: 30px; margin-right: 7px;color: #3f74e7;"><?php echo addslashes(esc_html__('If', 'ARForms')) ?></span>
                                                 <?php
                                                 if (count($cl_rules_array) > 0) {
                                                     $rule_i = 1;
                                                     if($arfaction == 'duplicate'){
                                                        $id = $define_template;
                                                     }
                                                     foreach ($cl_rules_array as $rule) {
                                                         ?>
                                                    <div id="arf_cl_rule_arfsubmit<?php echo '_' . $rule_i; ?>" class="cl_rules">
                                                        <input type="hidden" name="rule_array_arfsubmit[]" value="<?php echo $rule_i; ?>" />
                                                        <span>
                                                            <div class="sltstandard arf_cl_field_menu"><?php echo $arfieldhelper->arf_cl_field_menu_submit_cl($id, 'arf_cl_field_arfsubmit_' . $rule_i, 'arf_cl_field_arfsubmit_' . $rule_i, $rule['field_id']); ?></div>
                                                        </span>
                                                        <span style="float: left; font-size: 14px; line-height: 30px; margin-right: 7px;"><?php echo addslashes(esc_html__('is', 'ARForms')); ?></span>
                                                        <span>
                                                            <div class="sltstandard arf_cl_op_arfsubmit_operator"><?php echo $arfieldhelper->arf_cl_rule_menu('arf_cl_op_arfsubmit_' . $rule_i, 'arf_cl_op_arfsubmit_' . $rule_i, $rule['operator'], $values['submit_conditional_logic']['rules'][$rule_i]['field_type']); ?></div>
                                                        </span>                                                        
                                                        <span class="span_txtnew">
                                                            <input type="text" name="cl_rule_value_arfsubmit<?php echo '_' . $rule_i; ?>" id="cl_rule_value_arfsubmit<?php echo '_' . $rule_i; ?>" onkeyup="this.setAttribute('value',this.value)" placeholder="<?php echo (esc_attr($rule['field_type']) == "date") ? 'YYYY/MM/DD':'' ; ?>" class="txtstandardnew cl_rule_value arfheight34" value='<?php echo esc_attr($rule['value']); ?>' style="width:100%;" />
                                                        </span>
                                                        <span class="bulk_add_remove arf_conditional_logic_on_submisson_bulk_add_remove">
                                                            <span class="bulk_add" onclick="add_new_rule('arfsubmit');"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"></path></g></svg></span>
                                                            <span class="bulk_remove" onclick="delete_rule('arfsubmit', '<?php echo $rule_i; ?>');" ><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"></path></g></svg></span>
                                                        </span>
                                                    </div>
                                                    <?php
                                                    $rule_i++;
                                                }
                                            }
                                            ?>
                                        </div>
                                        <input type="hidden" id="field_type_arfsubmit" data-fid="arfsubmit" value="arfsubmit" />
                                        <input type="hidden" id="field_ref_arfsubmit" value="arfsubmit" />
                                        <input type="hidden" name="field_options[field_key_arfsubmit]" class="txtstandardnew" value="arfsubmit_key" size="20" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php do_action('arf_after_onsubmit_settings_container', $id, $values); ?>
                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_submit_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>
        <!-- Submit Action Model -->

        <!-- Optins Model -->
        <div class="arf_modal_overlay">
            <?php $double_optin = isset($values['arf_enable_double_optin']) ? $values['arf_enable_double_optin'] : ""; ?>
            <div id="arf_optin_model" class="arf_popup_container arf_popup_container_option_model">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Opt-ins (email marketing) configuration', 'ARForms')); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_optin_popup_button">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>
                <div class="arf_option_model_popup_container arf_optins_container">
                    <div class="arf_popup_container_loader">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="arf_popup_container_autoresponder_values arf_autoresponder_values_container" style="margin-top: -10px;">
                        <div>
                            <p class="arftitle_p" style="margin-left: 0px;">
                                <label><?php echo addslashes(esc_html__('Form fields mapping', 'ARForms')); ?></label>
                                <span class="arp_opt_in_field_notice_wrapper" style="padding-top: 0px;padding-bottom: 0px;font-style: italic;display: inline-block;font-weight: normal;font-size: 14px;display: block;margin-left: 25px;margin-bottom: 15px;"><?php echo addslashes(esc_html__('(please select appropriate form fields for first name, last name and email parameters to submit on email marketing softwares)', 'ARForms')); ?>)</span>
                            </p>
                            
                        </div>

                        <div class="arp_opt_in_field_wrapper" style="margin-left: 25px;float: left;width:100%;display: block;">
                            <?php
                            $selectbox_field_options = array('' => addslashes(esc_html__('Select First Name', 'ARForms')));
                            $selectbox_field_options_attr = array();
                            $selectbox_field_value_label = "";
                            if (isset($values['fields']) and count($values['fields']) > 0) {
                                foreach ($values['fields'] as $field1) {
                                    if ($field1['type'] != 'divider' && $field1['type'] != 'section' && $field1['type'] != 'arf_repeater' && $field1['type'] != 'break' && $field1['type'] != 'captcha' && $field1['type'] != 'html' && $field1['type'] != 'file' && $field1['type'] != 'matrix' ) {
                                        if( isset( $field1['parent_field_type'] ) && $field1['parent_field_type'] == 'arf_repeater' ){
                                            continue;
                                        }

                                        if (($field1["id"] == $responder_fname)) {
                                            $selectbox_field_value_label = $field1["name"];
                                        }

                                        $current_field_id = $field1["id"];
                                        if ($current_field_id !="" && $arfieldhelper->arf_execute_function($field1["name"],'strip_tags')=="") {
                                            $selectbox_field_options[$current_field_id] = '[Field Id:'.$current_field_id.']';
                                        }else{
                                            $selectbox_field_options[$current_field_id] = $arfieldhelper->arf_execute_function($field1["name"],'strip_tags');  
                                        }

                                        $selectbox_field_options_attr['data-type'][$current_field_id] = $field1['type'];
                                        
                                    }
                                }
                            }                        
                            ?>
                            <input id="autoresponder_fname" name="autoresponder_fname" value="<?php echo $responder_fname; ?>" type="hidden" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?>>
                            <input id="autoresponder_lname" name="autoresponder_lname" value="<?php echo $responder_lname; ?>" type="hidden" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?>>
                            <input id="autoresponder_email" name="autoresponder_email" value="<?php echo $responder_email; ?>" type="hidden" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?>>
                            <div class="arf_ar_dropdown_wrapper">
                                <label class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('First name field', 'ARForms')); ?></label>
                                <?php
                                
                                echo $maincontroller->arf_selectpicker_dom( 'autoresponder_fname', 'autoresponder_fname', 'arf_name_field_dropdown', 'width:170px;', $responder_fname, array(), $selectbox_field_options, false, array(), false, $selectbox_field_options_attr, false, array(), true );
                                ?>
                                <p class="autoresponder_fname_err"><?php esc_html_e("Please select above field.", "ARForms") ?></p>
                            </div>
                            <?php
                            $selectbox_field_options = array( '' => addslashes(esc_html__('Select Last Name', 'ARForms')) );
                            $selectbox_field_value_label = "";
                            if (isset($values['fields']) and count($values['fields']) > 0) {
                                foreach ($values['fields'] as $field1) {
                                    if ($field1['type'] != 'divider' && $field1['type'] != 'section' && $field1['type'] != 'arf_repeater' && $field1['type'] != 'break' && $field1['type'] != 'captcha' && $field1['type'] != 'html' && $field1['type'] != 'file' && $field1['type'] != 'matrix' ) {
                                        if( isset( $field1['parent_field_type'] ) && $field1['parent_field_type'] == 'arf_repeater' ){
                                            continue;
                                        }
                                        if (($field1["id"] == $responder_lname)) {
                                            $selectbox_field_value_label = $field1["name"];
                                        }

                                        $current_field_id = $field1["id"];
                                        if($current_field_id !="" && $arfieldhelper->arf_execute_function($field1["name"],'strip_tags')==""){
                                            $selectbox_field_options[$current_field_id] = '[Field Id:'.$current_field_id.']';
                                        }else{
                                            $selectbox_field_options[$current_field_id] = $arfieldhelper->arf_execute_function($field1["name"],'strip_tags');
                                        }
                                        
                                    }
                                }
                            }
                            ?>
                            <div class="arf_ar_dropdown_wrapper">
                                <label class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Last name field', 'ARForms')); ?></label>

                                <?php
                                    echo $maincontroller->arf_selectpicker_dom( 'autoresponder_lname', 'autoresponder_lname', 'arf_name_field_dropdown', 'width:170px;', $responder_lname, array(), $selectbox_field_options, false, array(), false, array(), false, array(), true );
                                ?>
                                
                                <p class="autoresponder_lname_err"><?php esc_html_e("Please select above field.", "ARForms") ?></p>
                            </div>
                            <?php
                            $selectbox_field_options = array('' => addslashes(esc_html__('Select Email Field', 'ARForms')));
                            $selectbox_field_value_label = "";
                            if (isset($values['fields']) and count($values['fields']) > 0) {
                                foreach ($values['fields'] as $field1) {
                                    if (in_array($field1['type'], array('email', 'text'))) {
                                        if( isset( $field1['parent_field_type'] ) && $field1['parent_field_type'] == 'arf_repeater' ){
                                            continue;
                                        }
                                        if (($field1["id"] == $responder_email)) {
                                            $selectbox_field_value_label = $field1["name"];
                                        }

                                        $current_field_id = $field1["id"];
                                        if ($current_field_id !="" && $arfieldhelper->arf_execute_function($field1["name"],'strip_tags')=="") {
                                            $selectbox_field_options[$current_field_id] = '[Field Id : '.$current_field_id.']';
                                        }else{
                                            $selectbox_field_options[$current_field_id] = $arfieldhelper->arf_execute_function($field1["name"],'strip_tags');
                                        }
                                        
                                    }
                                }
                            }
                            ?>
                            <div class="arf_ar_dropdown_wrapper">
                                <label class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Email field', 'ARForms')); ?></label>

                                <?php
                                    echo $maincontroller->arf_selectpicker_dom( 'autoresponder_email', 'autoresponder_email', 'arf_name_field_dropdown', 'width:170px;', $responder_email, array(), $selectbox_field_options, false, array(), false, array(), false, array(), true );
                                ?>
                                <p class="autoresponder_email_err"><?php esc_html_e("Please select above field.", "ARForms") ?></p>
                            </div>
                        </div>
                    </div>
                    <?php do_action('arf_condition_on_subscription_html', $id, '', $values); ?>
                    <div class="arf_mailoptin_content_container">
                        <p class="arftitle_p" style="margin-left: 0px;margin-bottom: 30px;"><label><?php echo esc_html__('Select Opt-in provider','ARForms');?></label></p>
                        <ul class="arf_optin_tabs">
                            <li class="arf_optin_tab_item arfactive" data-id="mailchimp"><?php echo addslashes(esc_html__('Mailchimp', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="aweber"><?php echo addslashes(esc_html__('Aweber', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="icontact"><?php echo addslashes(esc_html__('Icontact', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="constant_contact"><?php echo addslashes(esc_html__('Constant Contact', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="get_response"><?php echo addslashes(esc_html__('GetResponse', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="madmimi"><?php echo addslashes(esc_html__('Madmimi', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="ebizac"><?php echo addslashes(esc_html__('Ebizac.com', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="gvo"><?php echo addslashes(esc_html__('GVO', 'ARForms')); ?></li>
                            <?php do_action('arf_email_marketers_tab_outside'); ?>
                        </ul>
                        <div class="arf_optin_tab_wrapper">
                            <div class="arf_optin_tab_inner_container arfactive" id="mailchimp">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo mailchimp_original" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/mailchimp.png'; ?>"/></div>
                                <div class="arf_optin_logo mailchimp_gray" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/mailchimp_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <div>
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_1" value="1" <?php echo (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="mailchimp"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_1">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>
                                </div>
                                <div class="arf_option_configuration_wrapper mailchimp_configuration_wrapper <?php echo (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                                    
                                    <br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['mailchimp_type'] == 1) {
                                        ?>
                                        <br/>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores">
                                            <?php
                                            if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($global_enable_ar['mailchimp']) and $global_enable_ar['mailchimp'] == 0 and isset($mailchimp_arr['enable']) and $mailchimp_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block" style="margin-left: 25px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List/Audience Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List/Audience','ARForms'));
                                                        $responder_list_option = "";
                                                        $lists = json_decode($res2['responder_list_id'],true);
                                                        $responder_list_opts = array(
                                                            '' => addslashes( esc_html__( 'Select List/Audience', 'ARForms') )
                                                        );
                                                        if (is_array($lists) && count($lists) > 0) {
                                                            $cntr = 0;
                                                            foreach ($lists as $list) {
                                                                if ($res2['responder_list'] == $list['id'] || $cntr == 0) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_opts[ $list['id'] ] = $list['name'];
                                                                $cntr++;
                                                            }
                                                        }

                                                        $mailchimp_enable_class = (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if( isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1 ){
                                                            $mailchimp_opt_disable = false;
                                                        } else {
                                                            $mailchimp_opt_disable = true;
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_mailchimp_list', 'i_mailchimp_list', $mailchimp_enable_class, 'width:170px;', $selected_list_id, array(), $responder_list_opts, false, array(), $mailchimp_opt_disable, array(), false, array(), true );

                                                        ?>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block" style="margin-left: 25px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List', 'ARForms'));
                                                        $responder_list_option = "";
                                                        $lists = json_decode($res2['responder_list_id'],true);
                                                        $responder_list_opts = array(
                                                            '' => addslashes( esc_html__( 'Select List', 'ARForms') )
                                                        );
                                                        $default_mail_chimp_select_list = isset($res2['responder_list']) ? $res2['responder_list'] : '';
                                                        $selected_list_id_mailchimp = isset($mailchimp_arr['type_val']) ? $mailchimp_arr['type_val'] : $default_mail_chimp_select_list;
                                                        if (is_array($lists) && count($lists) > 0) {
                                                            $cntr = 0;
                                                            foreach ($lists as $list ) {
                                                                if ($selected_list_id_mailchimp == $list['id'] || $cntr == 0) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_opts[ $list['id'] ] = $list['name'];
                                                                $cntr++;
                                                            }
                                                        }

                                                        $mailchimp_enable_class = (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if( isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1 ){
                                                            $mailchimp_opt_disable = false;
                                                        } else {
                                                            $mailchimp_opt_disable = true;
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_mailchimp_list', 'i_mailchimp_list', $mailchimp_enable_class, 'width:170px;', $selected_list_id, array(), $responder_list_opts, false, array(), $mailchimp_opt_disable, array(), false, array() );

                                                        ?>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1" name="web_form_mailchimp" id="web_form_mailchimp" <?php echo ($setvaltolic != 1 ? "readonly=readonly" : ''); ?>><?php echo stripslashes_deep($res2['responder_web_form']); ?></textarea>
                                    <?php } ?>
                                    <span class="arf_enable_double_optin">
                                        <label class="arf_js_switch_label">
                                            <span style="margin-left: -6px;"><?php echo addslashes(esc_html__('Enable Double Opt-in', 'ARForms')); ?>&nbsp;&nbsp;</span>

                                        </label>
                                        <span class="arf_js_switch_wrapper <?php echo (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? '' : 'arf_disable_switch'; ?>"  <?php if ($setvaltolic != 1) {echo 'onclick="return false"';}?>>
                                            <input type="checkbox" class="js-switch" name="options[arf_enable_double_optin]" <?php checked($double_optin,1); ?> id="arf_enable_double_optin" value="1" onclick="arf_mailchimp_double_opti();" />
                                            <span class="arf_js_switch"></span>
                                        </span>
                                        <label class="arf_js_switch_label" for="arf_enable_double_optin">
                                            <span></span>
                                        </label>  
                                    </span>
                                    <?php do_action('arf_map_malchimp_fields_outside',$values,$record,$responder_list_option,$mailchimp_arr); ?>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="aweber">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                
                                if(isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo aweber_original" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/aweber.png'; ?>"/></div>
                                <div class="arf_optin_logo aweber_gray" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/aweber_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                    <label class="arf_js_switch_label">
                                        <span></span>
                                    </label>
                                    <span class="arf_js_switch_wrapper">                                        
                                        <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_3" value="3" <?php echo (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="aweber"/>
                                        <span class="arf_js_switch"></span>
                                    </span>
                                    <label class="arf_js_switch_label" for="autores_3">
                                        <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                    </label>                                
                                </div>
                                </div>                                
                                <div class="arf_option_configuration_wrapper aweber_configuration_wrapper <?php echo (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">                                    
                                    <br/><br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['aweber_type'] == 1) {
                                        $aweber_data = $res1;
                                        $is_aweber_old = false;
                                        if( ($aweber_data['consumer_key'] != '' && $aweber_data['consumer_secret'] != '' && ($aweber_data['consumer_key'] != ARF_AWEBER_CONSUMER_KEY && $aweber_data['consumer_secret'] != ARF_AWEBER_CONSUMER_SECRET) ) ){
                                            $is_aweber_old = true;
                                        }
                                        ?>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores">
                                            <?php
                                            if (($arfaction == 'new' || ($arfaction == 'duplicate' and $arf_template_id < 100)) || (isset($global_enable_ar['aweber']) and $global_enable_ar['aweber'] == 0 and isset($aweber_arr['enable']) and $aweber_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-aweber"  class="autoresponder_inner_block" style="margin-left: 25px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $aweber_lists = explode("-|-", $aweber_data['responder_list_id']);
                                                        $i = 0;
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List', 'ARForms'));
                                                        $responder_list_option = "";
                                                        $responder_list_opts = array(
                                                            '' => addslashes( esc_html__( 'Select List', 'ARForms') )
                                                        );
                                                        $cntr = 0;
                                                        if (!empty($aweber_lists[0]) && false == $is_aweber_old) {
                                                            $aweber_lists_name = explode("|", $aweber_lists[0]);
                                                            $aweber_lists_id = explode("|", $aweber_lists[1]);

                                                            if (count($aweber_lists_name) > 0 && is_array($aweber_lists_name)) {
                                                                foreach ($aweber_lists_name as $aweber_lists_name1) {
                                                                    if ($aweber_lists_id[$i] != "") {
                                                                        if ($aweber_lists_id[$i] == $aweber_data['responder_list'] || $cntr == 0) {
                                                                            $selected_list_id = $aweber_lists_id[$i];
                                                                            $selected_list_label = $aweber_lists_name1;
                                                                        }
                                                                        $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $aweber_lists_id[$i] . '" data-label="' . htmlentities($aweber_lists_name1) . '">' . $aweber_lists_name1 . '</li>';

                                                                        $responder_list_opts[ $aweber_lists_id[$i] ] = $aweber_lists_name1;

                                                                        $cntr++;
                                                                    }
                                                                    $i++;
                                                                }
                                                            }
                                                        }

                                                        $aweber_enable_class = (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if( isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1 ){
                                                            $aweber_opt_disable = false;
                                                        } else {
                                                            $aweber_opt_disable = true;
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_aweber_list', 'i_aweber_list', $aweber_enable_class, 'width:170px;', $selected_list_id, array(), $responder_list_opts, false, array(), $aweber_opt_disable, array(), false, array(), true );
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block" style="margin-left: 25px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $aweber_lists = explode("-|-", $aweber_data['responder_list_id']);
                                                        $aweber_lists_name = explode("|", $aweber_lists[0]);
                                                        $i = 0;
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__("Select List","ARForms"));
                                                        $responder_list_option = "";
                                                        $responder_list_opts = array(
                                                            '' => addslashes( esc_html__( 'Select List', 'ARForms') )
                                                        );
                                                        $cntr = 0;
                                                        if (!empty($aweber_lists[0]) && false == $is_aweber_old) {
                                                            if (count($aweber_lists_name) > 0 && is_array($aweber_lists_name)) {
                                                                $aweber_lists_id = isset($aweber_lists[1]) ? explode("|", $aweber_lists[1]) : '';

                                                                foreach ($aweber_lists_name as $aweber_lists_name1) {
                                                                    if ($aweber_lists_id[$i] != "") {
                                                                        if ($aweber_lists_id[$i] == $aweber_arr['type_val'] || $cntr == 0) {
                                                                            $selected_list_id = $aweber_lists_id[$i];
                                                                            $selected_list_label = $aweber_lists_name1;
                                                                        }
                                                                        
                                                                        $responder_list_opts[ $aweber_lists_id[$i] ] = $aweber_lists_name1;

                                                                        $cntr++;
                                                                    }
                                                                    $i++;
                                                                }
                                                            }
                                                        }

                                                        $aweber_enable_class = (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if( isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1 ){
                                                            $aweber_opt_disable = false;
                                                        } else {
                                                            $aweber_opt_disable = true;
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_aweber_list', 'i_aweber_list', $aweber_enable_class, 'width:170px;', $selected_list_id, array(), $responder_list_opts, false, array(), $aweber_opt_disable, array(), false, array(), true );
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1" name="web_form_aweber" id="web_form_aweber" style="width:100%; height:100px;" <?php echo( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>><?php echo stripslashes_deep($res1['responder_web_form']); ?></textarea> <?php
                                    }
                                    ?>
                                    <?php do_action('arf_map_aweber_fields_outside',$values,$record,$responder_list_option,$aweber_arr); ?>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="icontact">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo icontact_original" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/icontact.png'; ?>"/></div>
                                <div class="arf_optin_logo icontact_gray" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/icontact_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper ">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_8" value="8" <?php echo (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="icontact"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_8">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>                                
                                <div class="arf_option_configuration_wrapper icontact_configuration_wrapper <?php echo (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">                                    
                                    <br/><br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['icontact_type'] == 1) {
                                        ?>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                                            <?php
                                            if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($global_enable_ar['icontact']) and $global_enable_ar['icontact'] == 0 and isset($icontact_arr['enable']) and $icontact_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-icontact" class="autoresponder_inner_block" style="margin-top:0px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List', 'ARForms'));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        $lists = maybe_unserialize($res6['responder_list_id']);

                                                        $responder_list_opts = array(
                                                            '' => addslashes( esc_html__( 'Select List', 'ARForms') )
                                                        );

                                                        if (is_array($lists) && count($lists) > 0 ) {

                                                            foreach ($lists as $list) {
                                                                
                                                                if ($res6['responder_list'] == $list->listId || $cntr == 0) {
                                                                    $selected_list_id = $list->listId;
                                                                    $selected_list_label = $list->name;
                                                                }
                                                                
                                                                $responder_list_opts[ $list->listId ] = $list->name;
                                                                $cntr++;
                                                            }
                                                        }

                                                        $icontact_enable_class = (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if( isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1 ){
                                                            $icontact_opt_disable = false;
                                                        } else {
                                                            $icontact_opt_disable = true;
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_icontact_list', 'i_icontact_list', $icontact_enable_class, 'width:170px;', $selected_list_id, array(), $responder_list_opts, false, array(), $icontact_opt_disable, array(), false, array(), true );

                                                        ?>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block" style="margin-top:0px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        $lists = maybe_unserialize($res6['responder_list_id']);
                                                        $responder_list_opts = array(
                                                            '' => addslashes( esc_html__( 'Select List', 'ARForms') )
                                                        );

                                                        if (is_array($lists) && count($lists) > 0) {
                                                            foreach ($lists as $list) {
                                                                if (isset( $icontact_arr['type_val']) && ( $icontact_arr['type_val'] == $list->listId || $cntr == 0) ) {
                                                                    $selected_list_id = $list->listId;
                                                                    $selected_list_label = $list->name;
                                                                }
                                                                
                                                                $responder_list_opts[ $list->listId ] = $list->name;
                                                                $cntr++;
                                                            }
                                                        }

                                                        $icontact_enable_class = (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if( isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1 ){
                                                            $icontact_opt_disable = false;
                                                        } else {
                                                            $icontact_opt_disable = true;
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_icontact_list', 'i_icontact_list', $icontact_enable_class, 'width:170px;', $selected_list_id, array(), $responder_list_opts, false, array(), $icontact_opt_disable, array(), false, array(), true );

                                                        ?>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1" name="web_form_icontact" id="web_form_icontact" style="width:100%; height:100px;" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>><?php echo stripslashes_deep($res6['responder_web_form']); ?></textarea>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="constant_contact">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1)
                                {
                                    $style = 'display:block;';
                                    $style_gray = 'display:none;';                                    
                                } else{
                                    $style = 'display:none;';
                                    $style_gray = 'display:block;';                                    
                                }?>
                                <div class="arf_optin_logo constant_contact_original arfconstantconstant" style="<?php echo $style;?>"><img src="<?php echo ARFIMAGESURL . '/constant-contact.png'; ?>"/></div>
                                <div class="arf_optin_logo constant_contact_gray arfconstantconstant" style="<?php echo $style_gray;?>"><img src="<?php echo ARFIMAGESURL . '/constant_contact_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_9" value="9" <?php echo (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="constant_contact"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_9">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>                                
                                <div class="arf_option_configuration_wrapper constant_contact_configuration_wrapper <?php echo (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                                    <br/><br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['constant_type'] == 1) {
                                        ?>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                                            <?php
                                            if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($global_enable_ar['constant_contact']) and $global_enable_ar['constant_contact'] == 0 and isset($constant_contact_arr['enable']) and $constant_contact_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-constant_contact" class="autoresponder_inner_block">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        $lists_new = maybe_unserialize($res7['list_data']);

                                                        $responder_list_opts = array(
                                                            '' => addslashes( esc_html__( 'Select List', 'ARForms' ) )
                                                        );

                                                        if (is_array($lists_new) && count($lists_new) > 0 ) {

                                                            foreach ($lists_new as $list) {
                                                                if ($res7['responder_list'] == $list['id'] || $cntr == 0) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_opts[ $list['id'] ] = $list['name'];

                                                                $cntr++;
                                                            }
                                                        }

                                                        $const_contact_enable_class = (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if( isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1 ){
                                                            $constant_contact_opt_disable = false;
                                                        } else {
                                                            $constant_contact_opt_disable = true;
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_constant_contact_list', 'i_constant_contact_list', $const_contact_enable_class, 'width:170px;', $selected_list_id, array(), $responder_list_opts, false, array(), $constant_contact_opt_disable, array(), false, array(), true );
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-constant_contact" class="autoresponder_inner_block">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')));
                                                        $cntr = 0;
                                                        $lists_new = maybe_unserialize($res7['list_data']);

                                                        $responder_list_opts = array(
                                                            '' => addslashes( esc_html__( 'Select List', 'ARForms' ) )
                                                        );

                                                        if (is_array($lists_new) && count($lists_new) > 0 ) {
                                                            foreach ($lists_new as $list) {
                                                                if ($constant_contact_arr['type_val'] == $list['id']) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_option[$list['id']] = $list['name'];
                                                                $cntr++;
                                                            }
                                                        }


                                                        $constant_contact_enable_class = (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if(isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1){
                                                            $constant_contact_opt_disable = false;
                                                        }else{
                                                            $constant_contact_opt_disable = true;
                                                        }
                                        
                                                        $constant_contact_attr = array();
                                                        if( $setvaltolic != 1 ){
                                                            $constant_contact_attr = array( 'readonly' => 'readonly' );
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_constant_contact_list', 'i_constant_contact_list', $constant_contact_enable_class, 'width:170px;', $selected_list_id, $constant_contact_attr, $responder_list_option, false, array(), $constant_contact_opt_disable, array(), false, array(), true );
                                                        ?>

                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1" name="web_form_constant_contact" id="web_form_constant_contact" style="width:100%; height:100px;" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>><?php echo stripslashes_deep($res7['responder_web_form']); ?></textarea>
                                        <?php
                                    }
                                    ?>
                                    <?php do_action('arf_map_constant_contact_fields_outside',$values,$record,$responder_list_option,$constant_contact_arr); ?>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="get_response">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo getresponse_original" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/getresponse.png'; ?>"/></div>
                                <div class="arf_optin_logo getresponse_gray" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/getresponse_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_4" value="4" <?php echo (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="getresponse"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_4">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>
                                <div class="arf_option_configuration_wrapper getresponse_configuration_wrapper <?php echo (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">                                    

                                    <br/><br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['getresponse_type'] == 1) {
                                        ?>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                                            <?php
                                            if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($global_enable_ar['getresponse']) and $global_enable_ar['getresponse'] == 0 and isset($getresponse_arr['enable']) and $getresponse_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-getresponse" class="autoresponder_inner_block">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Campaign Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select Field','ARForms'));
                                                        $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')));
                                                        $cntr = 0;
                                                        $lists = maybe_unserialize($res3['list_data']);
                                                        if ( is_array($lists) && count($lists) > 0 ) {
                                                            foreach ($lists as $list) {
                                                                
                                                                if ($res3['responder_list_id'] == $list['id']) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_option[$list['id']] = $list['name'];
                                                                $cntr++;
                                                            }
                                                        }

                                                        $getresponse_enable_class = (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if(isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1){
                                                            $getresponse_opt_disable = false;
                                                        }else{
                                                            $getresponse_opt_disable = true;
                                                        }
                                        
                                                        $getresponse_attr = array();
                                                        if( $setvaltolic != 1 ){
                                                            $getresponse_attr = array( 'readonly' => 'readonly' );
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_campain_name', 'i_campain_name', $getresponse_enable_class, 'width:170px;', $selected_list_id, $getresponse_attr, $responder_list_option, false, array(), $getresponse_opt_disable, array(), false, array(), true );

                                                        ?>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-getresponse" class="autoresponder_inner_block">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Campaign Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select Field','ARForms'));
                                                        $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')));
                                                        $cntr = 0;
                                                        
                                                        $lists = maybe_unserialize($res3['list_data']);
                                                        if (is_array($lists) && count($lists) > 0) {
                                                            foreach ($lists as $list) {
                                                                if ($getresponse_arr['type_val'] == $list['id']) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }
                                                                $responder_list_option[$list['id']] = $list['name'];
                                                                $cntr++;
                                                            }
                                                        }

                                                        $getresponse_enable_class = (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if(isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1){
                                                            $getresponse_opt_disable = false;
                                                        }else{
                                                            $getresponse_opt_disable = true;
                                                        }
                                        
                                                        $getresponse_attr = array();
                                                        if( $setvaltolic != 1 ){
                                                            $getresponse_attr = array( 'readonly' => 'readonly' );
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_campain_name', 'i_campain_name', $getresponse_enable_class, 'width:170px;', $selected_list_id, $getresponse_attr, $responder_list_option, false, array(), $getresponse_opt_disable, array(), false, array(), true );

                                                        ?>
                                                        
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1" name="web_form_getresponse" id="web_form_getresponse" style="width:100%; height:100px;" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>><?php echo stripslashes_deep($res3['responder_web_form']); ?></textarea>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="ebizac">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($ebizac_arr['enable']) && $ebizac_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo ebizac_original" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/ebizac.png'; ?>"/></div>
                                <div class="arf_optin_logo ebizac_gray" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/ebizac_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_6" value="6" <?php echo (isset($ebizac_arr['enable']) && $ebizac_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="ebizac"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_6">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>
                                <div class="arf_option_configuration_wrapper ebizac_configuration_wrapper <?php echo (isset($ebizac_arr['enable']) && $ebizac_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" >
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    ?>
                                    <div id="select-autores_<?php echo $rand_num; ?>" style="margin-left: 25px;">
                                        <?php
                                        if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($global_enable_ar['ebizac']) and $global_enable_ar['ebizac'] == 0 and isset($ebizac_arr['enable']) and $ebizac_arr['enable'] == 0 )) {
                                            ?>
                                            <textarea class="auto_responder_webform_code_area txtmultimodal1 arfebizactextarea " name="web_form_ebizac" id="web_form_ebizac" style="height:100px;" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?> <?php echo (isset($ebizac_arr['enable']) && $ebizac_arr['enable'] == 1) ? '' : 'readonly=readonly'; ?> > <?php echo stripslashes_deep($res5['responder_api_key']); ?> </textarea>
                                            <?php
                                        } else {
                                            $ebizac_arr['type_val'] = isset($ebizac_arr['type_val']) ? $ebizac_arr['type_val'] : '';
                                            ?>
                                            <textarea class="auto_responder_webform_code_area txtmultimodal1 arfebizactextarea" name="web_form_ebizac" id="web_form_ebizac" style="height:100px;" <?php echo( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>><?php echo stripslashes_deep($ebizac_arr['type_val']); ?></textarea>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="gvo">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo gvo_original arfgvo" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/gvo.png'; ?>"/></div>
                                <div class="arf_optin_logo gvo_gray arfgvo" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/gvo_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_5" value="5" <?php echo (isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="gvo"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_5">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>                                
                                <div class="arf_option_configuration_wrapper gvo_configuration_wrapper <?php echo (isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                                    <?php
                                    $rand_num = rand(1111, 9999); ?>
                                    <br/>
                                    <div id="select-autores_<?php echo $rand_num; ?>" style="margin-left: 25px;">
                                    <?php
                                    if (( $arfaction == 'new' || ( $arfaction == 'duplicate' && $arf_template_id < 100 ) ) || (isset($global_enable_ar['gvo']) && $global_enable_ar['gvo'] == 0 && isset($gvo_arr['enable']) && $gvo_arr['enable'] == 0 )) {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1 arfgvotextarea" name="web_form_gvo" id="web_form_gvo" style="height:100px;" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?> <?php echo (isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1) ? '' : 'readonly=readonly'; ?>> <?php echo stripslashes_deep($res4['responder_api_key']); ?></textarea>
                                        <?php
                                    } else {
                                        $gvo_arr['type_val'] = isset($gvo_arr['type_val']) ? $gvo_arr['type_val'] : '';
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1 arfgvotextarea" name="web_form_gvo" id="web_form_gvo" style="height:100px;"<?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?> <?php echo (isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1) ? '' : 'readonly=readonly'; ?>><?php echo stripslashes_deep($gvo_arr['type_val']); ?></textarea>
                                        <?php
                                    }
                                    ?>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="madmimi">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo madmimi_original arfmadmimi" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/madmimi.png'; ?>"/></div>
                                <div class="arf_optin_logo madmimi_gray arfmadmimi" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/mad_mimi_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_10" value="10" <?php echo (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="madmimi"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_10">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>                                
                                <div class="arf_option_configuration_wrapper madmimi_configuration_wrapper <?php echo (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                                    <br/><br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['madmimi_type'] == 1) {
                                        ?>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                                            <?php
                                            if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($madmimi_arr['enable']) and $madmimi_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block" data-if="sadsa" >
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')) );
                                                        $lists = maybe_unserialize($res14['responder_list_id']);
                                                        if ( is_array($lists) && count($lists) > 0 ) {
                                                            $cntr = 0;
                                                            foreach ($lists as $list) {
                                                                if ($res14['responder_list'] == $list['id'] || $cntr == 0) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_option[$list['id']] = $list['name'];
                                                                $cntr++;
                                                            }
                                                        }

                                                        $madmimi_enable_class = (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if(isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1){
                                                            $madmimi_opt_disable = false;
                                                        }else{
                                                            $madmimi_opt_disable = true;
                                                        }
                                        
                                                        $madmimi_attr = array();
                                                        if( $setvaltolic != 1 ){
                                                            $madmimi_attr = array( 'readonly' => 'readonly' );
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_madmimi_list', 'i_madmimi_list', $madmimi_enable_class, 'width:170px;', $selected_list_id, $madmimi_attr, $responder_list_option, false, array(), $madmimi_opt_disable, array(), false, array() );

                                                        ?>
                                                        
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')) );
                                                        $lists = maybe_unserialize($res14['responder_list_id']);
                                                        $default_madmimi_select_list = isset($res14['responder_list']) ? $res14['responder_list'] : '';
                                                        $selected_list_id_madmimi = (isset($madmimi_arr['type_val']) && $madmimi_arr['type_val'] != '' ) ? $madmimi_arr['type_val'] : $default_madmimi_select_list;
                                                        if (is_array($lists) && count($lists) > 0) {
                                                            $cntr = 0;
                                                            foreach ($lists as $list) {
                                                                if ($selected_list_id_madmimi == $list['id'] || $cntr == 0) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_option[$list['id']] = $list['name'];
                                                                $cntr++;
                                                            }
                                                        }

                                                        $madmimi_enable_class = (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                                        if(isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1){
                                                            $madmimi_opt_disable = false;
                                                        }else{
                                                            $madmimi_opt_disable = true;
                                                        }
                                        
                                                        $madmimi_attr = array();
                                                        if( $setvaltolic != 1 ){
                                                            $madmimi_attr = array( 'readonly' => 'readonly' );
                                                        }

                                                        echo $maincontroller->arf_selectpicker_dom( 'i_madmimi_list', 'i_madmimi_list', $madmimi_enable_class, 'width:170px;', $selected_list_id, $madmimi_attr, $responder_list_option, false, array(), $madmimi_opt_disable, array(), false, array() );

                                                        ?>

                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    <?php }
                                    ?>
                                    <?php do_action('arf_map_madmimi_fields_outside',$values,$record,$responder_list_option,$madmimi_arr); ?>
                                </div>
                            </div>
                            <?php do_action('arf_email_marketers_tab_container_outside', $arfaction, $global_enable_ar, $current_active_ar, $ar_data, $setvaltolic); ?>
                        </div>
                    </div>
                    
                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_optin_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>
        <!-- Optins Model -->

        <!-- General Options Model -->
        <div class="arf_modal_overlay">
            <style type="text/css">
                 .arf_cal_header {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #ffffff!important;
                }
                .arf_cal_month {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #66aaff!important;
                }
                .arf_selectbox[data-name="arfredirecttolist"] ul{
                    width:302px !important;
                }
                #arf_other_options_model .bootstrap-datetimepicker-widget table td.active,
                #arf_other_options_model .bootstrap-datetimepicker-widget table td.active:hover {
                    color: #66aaff; 
                    background-image : url("data:image/svg+xml;utf8,<svg width='35px' xmlns='http://www.w3.org/2000/svg' height='29px'><path fill='rgb(0,126,228)' d='M15.732,27.748c0,0-14.495,0.2-14.71-11.834c0,0,0.087-7.377,7.161-11.82 c0,0,0.733-0.993-1.294-0.259c0,0-1.855,0.431-3.538,2.2c0,0-1.078,0.216-0.388-1.381c0,0,2.416-3.019,8.585-2.76 c0,0,2.372-2.458,7.419-1.293c0,0,0.819,0.517-0.518,0.819c0,0-5.361,0.514-3.753,1.122c0,0,14.021,3.073,14.322,13.943 C29.019,16.484,29.573,27.32,15.732,27.748z M26.991,16.182C26.24,7.404,14.389,3.543,14.389,3.543 c-2.693-0.747-4.285,0.683-4.285,0.683C8.767,4.969,6.583,7.804,6.583,7.804C2.216,13.627,3.612,18.47,3.612,18.47 c2.168,7.635,12.505,7.097,12.505,7.097C27.376,25.418,26.991,16.182,26.991,16.182z'/></svg>") !important;
                }
            </style>

            <div id="arf_other_options_model" class="arf_popup_container arf_popup_container_other_option_model">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('General Options', 'ARForms')); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_general_popup_button">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>

                <div class="arf_popup_content_container arf_other_options_container">
                    <div class="arf_popup_container_loader">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="arf_popup_checkbox_wrapper">
                        <div class="arf_custom_checkbox_div">
                            <div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" name="options[arf_form_set_cookie]" id="arf_form_set_cookie" value="1" <?php isset($values['arf_form_set_cookie']) ? checked($values['arf_form_set_cookie'], 1) : ''; ?> />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
                            <span>
                            <label id="arf_form_set_cookie" for="arf_form_set_cookie"><?php echo addslashes(esc_html__('Auto save form progress', 'ARForms')) ?></label>
                            </span>
                        </div>  

                        <div style="clear: both;margin-left: 40px;font-size: 14px;font-style: italic;"><?php echo esc_html__('(Until the form is not submitted, save data typed by user on their machine, so they can come back to the form later on, and will be able to continue the from.)', 'ARForms'); ?></div>
                    </div>

                    <div class="arf_popup_checkbox_wrapper">
                        <div class="arf_custom_checkbox_div">
                            <div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" name="options[arf_form_save_database]" id="arf_form_save_database" value="1" <?php isset($values['arf_form_save_database']) ? checked($values['arf_form_save_database'], 1) : ''; ?> />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
                            <span>
                            <label id="arf_form_save_database" for="arf_form_save_database"><?php echo addslashes(esc_html__('Save partial form data', 'ARForms')) ?></label>
                            </span>
                        </div>
                        <div style="clear:both; margin-left: 40px; font-size: 14px; font-style: italic;"><?php esc_html_e( '(This option will automatically store partially filled form data to database. This may increase load of your server due to multiple AJAX call. So please enable this feature only if you need.)', 'ARForms' ); ?></div>
                    </div>


                    <div class="arf_submit_action_tab_wrapper">
                        <?php do_action('arf_additional_onsubmit_settings', $id, $values); ?>

                        <div class="arf_popup_checkbox_wrapper">
                            <div class="arf_custom_checkbox_div">
                                <div class="arf_custom_checkbox_wrapper">
                                    <input type="checkbox" name="options[arf_prevent_view_entry]" id="arf_prevent_view_entry" value="1" <?php isset($values['arf_prevent_view_entry']) ? checked($values['arf_prevent_view_entry']) : ''; ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                    </svg>
                                </div>
                                <span>
                                    <label for="arf_prevent_view_entry"><?php echo esc_html__('Prevent storing visitor analytics data','ARForms'); ?></label>
                                </span>
                            </div>
                        </div>

                        <div class="arf_popup_checkbox_wrapper">
                            <div class="arf_custom_checkbox_div">
                                <div class="arf_custom_checkbox_wrapper">
                                    <input type="checkbox" name="options[arf_skip_store_data]" id="arf_skip_store_data" value="1" <?php isset($values['arf_skip_store_data']) ? checked($values['arf_skip_store_data'], 1) : ''; ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                    </svg>
                                </div>
                                <span>
                                    <label id="arf_skip_store_data" for="arf_skip_store_data"><?php echo addslashes(esc_html__('Don\'t store entry in database', 'ARForms')) ?></label>
                                </span>
                            </div>
                            <div style="clear:both; margin-left: 40px; font-size: 14px; font-style: italic;"><?php esc_html_e('(After enabling this option, form entries data will not be stored in database. If email notification is enabled then data will be sent through email.)', 'ARForms'); ?></div>
                            <div style="color: #ff0000;clear:both; margin-left: 40px; font-size: 14px; font-style: italic;"><?php esc_html_e('Note: If any of the form fields are mapped with any ARForms add-on then this feature will not work.', 'ARForms'); ?></div>
                        </div>

                        <div class="arf_other_option_separator">
                        </div>
                        <span class="arf_hidden_field_title" style="margin-bottom: 10px;"><?php echo addslashes(esc_html__('Restrict Form Entries','ARForms')); ?></span>

                        <div class="arf_popup_checkbox_wrapper" style="width:100%; margin-top: 10px;">
                            <div  class="arf_custom_checkbox_div">
                                <div class="arf_custom_checkbox_wrapper" onclick="arfmaxentryinput();">
                                    <input type="checkbox" name="options[arf_restrict_entry]" id="arf_restrict_entry" value="1"  <?php checked((isset($values['arf_restrict_entry'])?$values['arf_restrict_entry']:''), 1); ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                    </svg>
                                </div>
                                <span>
                                    <label id="" for="arf_restrict_entry"><?php echo addslashes(esc_html__('Disable form submission after', 'ARForms')); ?>
                                    </label>
                                </span>
                                <div class="arf_restrict_entry_div">
                                    <input type="text" id="arf_max_entry_textbox" name="options[arf_restrict_max_entries]" value="<?php echo(isset($values['arf_restrict_max_entries'])?$values['arf_restrict_max_entries']:''); ?>" class="arf_large_input_box" style="width: 50px;float: none !important;height: 30px !important;margin-left: unset !important;margin-right:3px;" <?php echo(!isset($values['arf_restrict_entry']) || $values['arf_restrict_entry']!='1')?'readonly':'';?>>
                                    <?php echo addslashes(esc_html__('Entries', 'ARForms')); ?>
                                </div>
                            </div>
                        </div>
                            <div class="arftablerow entry_res_msg" style="display:none;<?php echo (is_rtl()) ? 'margin-right: 45px;' : 'margin-left: 45px;';?>">
                                <div class="arfcolumnleft arfsettingsubtitle"><?php echo esc_html__('Restricted entry message', 'ARForms'); ?></div>
                                <div class="arfcolumnright arf_pre_dup_msg_width">
                                    <textarea rows="4" id="arf_restriction_message_entries" name="options[arf_res_msg_entry]" class="txtmodal1 auto_responder_webform_code_area" style="padding:10px;"><?php echo (isset($values['arf_res_msg_entry']) && $values['arf_res_msg_entry']!='') ?$values['arf_res_msg_entry']: esc_html__('Maximum entry limit is reached.','ARForms'); ?></textarea><br />
                                    <div class="arferrmessage" id="arf_res_entry_msg_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></div>
                                </div>
                            </div>

                        <div class="arf_popup_checkbox_wrapper" style="width:100%;margin-top: 10px;">
                            <div  class="arf_custom_checkbox_div">
                                <div class="arf_custom_checkbox_wrapper" onclick="arfrestrictentries();">
                                    <input type="checkbox" name="options[arf_restrict_form_entries]" id="arf_restrict_form_entries" value="1" <?php checked($values['arf_restrict_form_entries'], 1); ?>/>
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                    </svg>
                                </div>
                                <span>
                                    <label id="arf_restrict_form_entries_label" for="arf_restrict_form_entries"><?php echo addslashes(esc_html__('Disable form Submission', 'ARForms')); ?> (<?php echo addslashes(esc_html__('Date wise','ARForms')); ?>)</label>
                                </span>
                            </div>
                        </div>
                        <?php

                        if ($values["arf_restrict_form_entries"] == 1) {
                            $arf_restrict_form_entries_class = 'arf_restrict_form_entries_show';
                        } else {
                            $arf_restrict_form_entries_class = 'arf_restrict_form_entries_hide';
                        }

                        if ($values['restrict_action'] == 'before_specific_date') {
                            $display_block_specific_date = 'style="display:block;"';
                        } else {
                            $display_block_specific_date = 'style="display:none;"';
                        }

                        if ($values['restrict_action'] == 'after_specific_date') {
                            $display_block_after_specific_date = 'style="display:block;"';
                        } else {
                            $display_block_after_specific_date = 'style="display:none;"';
                        }

                        if ($values['restrict_action'] == 'date_range') {
                            $display_block_date_range = 'style="display:block;"';
                        } else {
                            $display_block_date_range = 'style="display:none;"';
                        }
                        ?>
                        <div class="arf_restrict_form_entries arfactive <?php echo $arf_restrict_form_entries_class; ?>">
                            <div class="arf_submit_action_options" style="<?php echo (is_rtl()) ? 'margin-right: 45px;' : 'margin-left: 45px;';?>">
                                
                                <div class="arf_radio_wrapper">
                                    <div class="arf_custom_radio_div">
                                        <div class="arf_custom_radio_wrapper">
                                            <input type="radio" class="arf_submit_entries" name="options[restrict_action]" id="success_action_before_specific_date" value="before_specific_date" <?php checked($values['restrict_action'], 'before_specific_date'); ?> />
                                            <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <span>
                                        <label id="success_action_redirect" for="success_action_before_specific_date"><?php echo addslashes(esc_html__('Before specific date', 'ARForms')); ?></label>
                                    </span>
                                </div>

                                <div class="arf_radio_wrapper">
                                    <div class="arf_custom_radio_div">
                                        <div class="arf_custom_radio_wrapper">
                                            <input type="radio" class="arf_submit_entries" name="options[restrict_action]" id="success_action_after_specific_date" value="after_specific_date" <?php checked($values['restrict_action'], 'after_specific_date'); ?> />
                                            <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <span>
                                        <label id="success_action_page" for="success_action_after_specific_date"><?php echo addslashes(esc_html__('After specific date', 'ARForms')); ?></label>
                                    </span>
                                </div>
                                
                                <div class="arf_radio_wrapper">
                                    <div class="arf_custom_radio_div">
                                        <div class="arf_custom_radio_wrapper">
                                            <input type="radio" class="arf_submit_entries" name="options[restrict_action]" id="success_action_date_range" value="date_range" <?php checked($values['restrict_action'], 'date_range'); ?> />
                                            <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <span>
                                        <label id="success_action_page" for="success_action_date_range"><?php echo addslashes(esc_html__('Between two dates', 'ARForms')); ?></label>
                                    </span>


                                </div>
                            </div>
                            <div class="arf_submit_action_options" style="<?php echo (is_rtl()) ? 'margin-right: 90px;' : 'margin-left: 90px;';?>margin-bottom: 20px;margin-top: 20px;">

                                
                                <div class="arf_restriction_entries_type_box" id="arf_type_success_action_before_specific_date" <?php echo $display_block_specific_date; ?>>
                                    <label><?php echo addslashes(esc_html__('Select date', 'ARForms')); ?></label>
                                    <?php $values['arf_restrict_entries_before_specific_date'] = (isset($values['arf_restrict_entries_before_specific_date']) && $values['arf_restrict_entries_before_specific_date'] !='') ? $values['arf_restrict_entries_before_specific_date'] : date('Y-m-d');?>
                                    <input type="text" id="arf_restrict_before_date" name="options[arf_restrict_entries_before_specific_date]" value="<?php echo date($arfdefine_date_formate_array['arfwp_dateformate'],strtotime($values['arf_restrict_entries_before_specific_date'])); ?>" class="arf_large_input_box arf_datetimepicker" style="width:160px;" />
                                    <span class="arferrmessage" id="arf_before_specific_date_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                                    <span class="arferrmessage" id="arf_before_specific_dateformat_error" style="top:0px;"><?php echo addslashes(esc_html__('Entered date is invalid','ARForms')); ?></span>
                                </div>
                                <div class="arf_restriction_entries_type_box" id="arf_type_success_action_after_specific_date" <?php echo $display_block_after_specific_date; ?>>
                                    <label><?php echo addslashes(esc_html__('Select date', 'ARForms')); ?></label>
                                    <?php $values['arf_restrict_entries_after_specific_date'] = (isset($values['arf_restrict_entries_after_specific_date']) && $values['arf_restrict_entries_after_specific_date'] !='') ? $values['arf_restrict_entries_after_specific_date'] : date('Y-m-d');?>
                                    <input type="text" id="arf_restrict_after_date" name="options[arf_restrict_entries_after_specific_date]" value="<?php echo date($arfdefine_date_formate_array['arfwp_dateformate'],strtotime($values['arf_restrict_entries_after_specific_date'])); ?>" class="arf_large_input_box arf_datetimepicker" style="width:160px;" />
                                    <span class="arferrmessage" id="arf_after_specific_date_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                                    <span class="arferrmessage" id="arf_after_specific_dateformat_error" style="top:0px;"><?php echo addslashes(esc_html__('Entered date is invalid','ARForms')); ?></span>
                                </div>
                                <div class="arf_restriction_entries_type_box" id="arf_type_success_action_date_range" <?php echo $display_block_date_range; ?>>
                                    <label><?php echo addslashes(esc_html__('Start from', 'ARForms')); ?></label>
                                    <?php $values['arf_restrict_entries_start_date'] = (isset($values['arf_restrict_entries_start_date']) && $values['arf_restrict_entries_start_date'] !='') ? $values['arf_restrict_entries_start_date'] : date('Y-m-d');

                                    $values['arf_restrict_entries_end_date'] = (isset($values['arf_restrict_entries_end_date']) && $values['arf_restrict_entries_end_date'] !='') ? $values['arf_restrict_entries_end_date'] : date('Y-m-d');
                                    ?>
                                    <input type="text" id="arf_restrict_daterange_start_date" name="options[arf_restrict_entries_start_date]" value="<?php echo date($arfdefine_date_formate_array['arfwp_dateformate'],strtotime($values['arf_restrict_entries_start_date'])); ?>" class="arf_large_input_box arf_datetimepicker" style="width:160px;" />
                                    <label style="<?php echo (is_rtl()) ? 'margin-right: 10px;' : 'margin-left: 10px;';?>"><?php echo addslashes(esc_html__('End date', 'ARForms')); ?></label>
                                    <input type="text" id="arf_restrict_daterange_end_date" name="options[arf_restrict_entries_end_date]" value="<?php echo date($arfdefine_date_formate_array['arfwp_dateformate'],strtotime($values['arf_restrict_entries_end_date'])); ?>" class="arf_large_input_box arf_datetimepicker" style="width:160px;" />
                                    <span class="arferrmessage" id="arf_date_range_start_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                                    <span class="arferrmessage" id="arf_date_range_start_error_dateformat_error" style="top:0px;"><?php echo addslashes(esc_html__('Entered date is invalid','ARForms')); ?></span>

                                    <span class="arferrmessage" id="arf_date_range_end_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                                    <span class="arferrmessage" id="arf_date_range_end_error_dateformat_error" style="top:0px;"><?php echo addslashes(esc_html__('Entered date is invalid','ARForms')); ?></SPAN>
                                </div>
                            </div>

                            <div class="arftablerow prevent_duplicate_message_box prevent_duplicate_box" style="<?php echo (is_rtl()) ? 'margin-right: 45px;' : 'margin-left: 45px;';?>">
                                <div class="arfcolumnleft arfsettingsubtitle"><?php echo esc_html__('Restricted entry message', 'ARForms'); ?></div>
                                <div class="arfcolumnright arf_pre_dup_msg_width">
                                    <textarea rows="4" id="arf_restriction_message" name="options[arf_res_msg]" class="txtmodal1 auto_responder_webform_code_area" style="padding:10px;"><?php echo(isset($values['arf_res_msg']) && $values['arf_res_msg']!='')?$values['arf_res_msg']: addslashes(esc_html__('Form Entry Restricted','ARForms')); ?></textarea><br />
                                    <div class="arferrmessage" id="arf_res_msg_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <?php do_action('arf_add_form_other_option_outside',$values);?>

                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_general_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>        
        <!-- General Options Model -->

        <!-- Hidden Fields Options Model -->
        <div class="arf_modal_overlay">
            <style type="text/css">
                 .arf_cal_header {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #ffffff!important;
                }
                .arf_cal_month {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #66aaff!important;
                }
                .arf_selectbox[data-name="arfredirecttolist"] ul{
                    width:302px !important;
                }
                #arf_hidden_fields_options_model .bootstrap-datetimepicker-widget table td.active,
                #arf_hidden_fields_options_model .bootstrap-datetimepicker-widget table td.active:hover {
                    color: #66aaff; 
                    background-image : url("data:image/svg+xml;utf8,<svg width='35px' xmlns='http://www.w3.org/2000/svg' height='29px'><path fill='rgb(0,126,228)' d='M15.732,27.748c0,0-14.495,0.2-14.71-11.834c0,0,0.087-7.377,7.161-11.82 c0,0,0.733-0.993-1.294-0.259c0,0-1.855,0.431-3.538,2.2c0,0-1.078,0.216-0.388-1.381c0,0,2.416-3.019,8.585-2.76 c0,0,2.372-2.458,7.419-1.293c0,0,0.819,0.517-0.518,0.819c0,0-5.361,0.514-3.753,1.122c0,0,14.021,3.073,14.322,13.943 C29.019,16.484,29.573,27.32,15.732,27.748z M26.991,16.182C26.24,7.404,14.389,3.543,14.389,3.543 c-2.693-0.747-4.285,0.683-4.285,0.683C8.767,4.969,6.583,7.804,6.583,7.804C2.216,13.627,3.612,18.47,3.612,18.47 c2.168,7.635,12.505,7.097,12.505,7.097C27.376,25.418,26.991,16.182,26.991,16.182z'/></svg>") !important;
                }
            </style>

            <div id="arf_hidden_fields_options_model" class="arf_popup_container arf_popup_container_other_option_model">
                <div class="arf_popup_container_header"><?php echo esc_html__('Hidden Input Fields Options', 'ARForms'); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_hidden_input_buttons_model">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>

                <div class="arf_popup_content_container arf_other_options_container">

                    <div class="arf_hidden_fields_wrapper">
                        <span class="arf_hidden_field_title"><?php echo esc_html__('Hidden Input Fields Setup','ARForms'); ?></span>
                        <div class="arf_hidden_field_note">
                            <div><?php echo addslashes(esc_html__('Note','ARForms')).': '.esc_html__('These fields will not shown in the form. Enter the value to be hidden','ARForms'); ?></div>

                            <div>[ARF_current_user_id] : <?php echo addslashes(esc_html__('This shortcode replace the value with currently logged-in User ID.', 'ARForms')); ?></div>
                            <div>[ARF_current_user_name] : <?php echo addslashes(esc_html__('This shortcode replace the value with currently logged-in User Name.', 'ARForms')); ?></div>
                            <div>[ARF_current_user_email] : <?php echo addslashes(esc_html__('This shortcode replace the value with currently logged-in User Email.', 'ARForms')); ?></div>
                            <div>[ARF_current_date] : <?php echo addslashes(esc_html__('This shortcode replace the value with current Date.', 'ARForms')); ?></div>
                            
                        </div>
                        <button type="button" id="arf_add_new_hidden_field" class="rounded_button arf_btn_dark_blue add_new_hidden_field_button" style="<?php echo (count($all_hidden_fields) > 0 ) ? 'display:none;' : ''; ?>"><?php echo addslashes(esc_html__('Add new hidden field','ARForms')); ?></button>
                        <div class="arf_hidden_field_input_wrapper_header <?php echo (count($all_hidden_fields) > 0 ) ? 'arfactive' : ''; ?>">
                            <span class="arf_hidden_field_input_wrapper_header_label"><?php echo addslashes(esc_html__('Label','ARForms')); ?></span>
                            <span class="arf_hidden_field_input_wrapper_header_value"><?php echo addslashes(esc_html__('Value','ARForms')); ?></span>
                            <span class="arf_hidden_field_input_wrapper_header_action"><?php echo addslashes(esc_html__('Action','ARForms')); ?></span>
                        </div>
                        <div class="arf_hidden_fields_input_wrapper">
                        <?php
                            if( count($all_hidden_fields) > 0 ){
                                $counter = 1;
                                $hidden_fields_content = "";
                                foreach($all_hidden_fields as $hkey => $hd_field){
                                    $field_opts = json_decode($hd_field->field_options);
                                    if( json_last_error() != JSON_ERROR_NONE ){
                                        $field_opts = maybe_unserialize($hd_field->field_options);
                                    }
                                    $hidden_fields_content .= "<div class='arf_hidden_field_input_container' id='arf_hidden_field_input_container_{$counter}'>";
                                    $hidden_fields_content .= "<label class='arf_hidden_field_input_label' for='arf_hidden_field_input_{$counter}'>";
                                    $hidden_fields_content .= "<input type='text' class='arf_large_input_box arf_hidden_field_label_input' value='{$hd_field->name}' data-field-id='{$hd_field->id}' id='arf_hidden_field_input_label_{$counter}' />";
                                    $hidden_fields_content .= "</label>";
                                    $hidden_fields_content .= "<input type='text' name='item_meta[{$hd_field->id}]' class='arf_large_input_box' id='arf_hidden_field_input_{$counter}' value='{$field_opts->default_value}' />";
                                    $hidden_fields_content .= "<input type='hidden' name='arf_field_data_{$hd_field->id}' id='arf_field_data_{$hd_field->id}' value='{$hd_field->field_options}' data-field-option='[]' />";
                                    $hidden_fields_content .= "<div class='arf_hidden_field_input_action_button'>";
                                    $hidden_fields_content .= '<span class="arf_hidden_field_add"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996 c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>';
                                    $hidden_fields_content .= '<span class="arf_hidden_field_remove" data-id="'.$counter.'"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389zM11.119,2.341c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>';
                                    $hidden_fields_content .= "</div>";
                                    $hidden_fields_content .= "</div>";
                                    $counter++;
                                }
                                echo $hidden_fields_content;
                            }
                        ?>
                        </div>
                    </div>
                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_hidden_input_buttons_model" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>
        <!-- Hidden Fields Options Model -->

        <!-- Tracking Code Options Model -->
        <div class="arf_modal_overlay">
            <style type="text/css">
                 .arf_cal_header {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #ffffff!important;
                }
                .arf_cal_month {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #66aaff!important;
                }
                .arf_selectbox[data-name="arfredirecttolist"] ul{
                    width:302px !important;
                }
                #arf_tracking_code_options_model .bootstrap-datetimepicker-widget table td.active,
                #arf_tracking_code_options_model .bootstrap-datetimepicker-widget table td.active:hover {
                    color: #66aaff; 
                    background-image : url("data:image/svg+xml;utf8,<svg width='35px' xmlns='http://www.w3.org/2000/svg' height='29px'><path fill='rgb(0,126,228)' d='M15.732,27.748c0,0-14.495,0.2-14.71-11.834c0,0,0.087-7.377,7.161-11.82 c0,0,0.733-0.993-1.294-0.259c0,0-1.855,0.431-3.538,2.2c0,0-1.078,0.216-0.388-1.381c0,0,2.416-3.019,8.585-2.76 c0,0,2.372-2.458,7.419-1.293c0,0,0.819,0.517-0.518,0.819c0,0-5.361,0.514-3.753,1.122c0,0,14.021,3.073,14.322,13.943 C29.019,16.484,29.573,27.32,15.732,27.748z M26.991,16.182C26.24,7.404,14.389,3.543,14.389,3.543 c-2.693-0.747-4.285,0.683-4.285,0.683C8.767,4.969,6.583,7.804,6.583,7.804C2.216,13.627,3.612,18.47,3.612,18.47 c2.168,7.635,12.505,7.097,12.505,7.097C27.376,25.418,26.991,16.182,26.991,16.182z'/></svg>") !important;
                }
            </style>

            <div id="arf_tracking_code_options_model" class="arf_popup_container arf_popup_container_other_option_model ">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Submit Tracking Script', 'ARForms')); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_submit-tracking-script">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>

                <div class="arf_popup_content_container arf_other_options_container">

                    <div class="arf_submit_action_tab_wrapper">

                        <div class="arf_after_submission_tracking_code">
                             <span class="arf_hidden_field_title" style="margin-top: 10px;margin-bottom: 10px;"><?php echo addslashes(esc_html__('After Submission Tracking Script', 'ARForms')); ?></span>
                            <div class="arftablerow prevent_duplicate_message_box prevent_duplicate_box" style="<?php echo (is_rtl()) ? 'margin-right: 45px;' : 'margin-left: 45px;';?>">
                                <div class="arfcolumnleft arfsettingsubtitle"><?php echo addslashes(esc_html__('Enter After submission tracking script', 'ARForms')); ?>&nbsp;(<?php echo addslashes(esc_html__('Example: Google Tracking Code', 'ARForms')); ?>)</div>
                                <div class="arfcolumnright arf_pre_dup_msg_width">
                                    <div class="" style="float:left;width: 100%;background: #f5f5f5;">&lt;script type="text/javascript"&gt;</div>
                                    <textarea rows="10" id="arf_submission_tracking_code" name="options[arf_sub_track_code]" class="txtmodal1 auto_responder_webform_code_area" style="padding:10px;margin:0;"><?php echo(isset($values['arf_sub_track_code']) && $values['arf_sub_track_code']!='')?rawurldecode(stripslashes_deep($values['arf_sub_track_code'])): ''; ?></textarea><br />
                                    <div class="arferrmessage" id="arf_submission_tracking_code" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></div>
                                    <div class="" style="float:left;width: 100%;background: #f5f5f5;">&lt;/script&gt;</div>
                                </div>
                                <div style="clear: both;margin-left: 0px;font-size: 14px;font-style: italic;"><?php echo esc_html__('(Do not insert script tag','ARForms').'(&lt;script&gt;)'.esc_html__(' inside code.)', 'ARForms'); ?></div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_submit-tracking-script" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>
        <!-- Tracking Code Options Model -->

        <?php do_action('arf_add_modal_in_editor',$values); ?>

    </form>
</div>

<!-- Font Awesome Model -->
<div class="arf_modal_overlay">
    <div id="arf_fontawesome_model" class="arf_popup_container arf_popup_container_fontawesome_model">
        <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Font Awesome', 'ARForms')); ?></div>
        <div class="arf_popup_content_container">
            <?php $is_rtl = ''; ?>
            <?php require( VIEWS_PATH . '/arf_font_awesome.php' ); ?>
        </div>
        <div class="arf_popup_container_footer" style="height:auto !important;">
            <input type="hidden" id="icon_field_id">
            <input type="hidden" id="icon_field_type">
            <input type="hidden" id="icon_no_icon">
            <input type="hidden" id="icon_icon">
            <button type="button" class="arf_popup_close_button" style="background-color: #DFECF2;color:black;margin:0px 10px;"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>&nbsp;&nbsp;
            <button type="button" class="arf_popup_close_button arf_fainsideimge_ok_button" id="" ><?php echo esc_html__('OK', 'ARForms'); ?></button>  
        </div>
    </div>
</div>
<!-- Font Awesome Model -->

<!-- Add new form Popup -->
<?php
if( isset($_REQUEST['isp']) && $_REQUEST['isp'] == 1 ){
?>

<div class="arf_modal_overlay">
    <input type="hidden" id="open_new_form_div" value="<?php echo isset($_REQUEST['isp']) ? $_REQUEST['isp'] : 0; ?>" />
    <div id="new_form_model" class="arf_popup_container arf_popup_container_new_form">
        <?php require(VIEWS_PATH . '/new-selection-modal.php'); ?>
    </div>
</div>
<?php
}
?>
<!-- Add new form Popup -->



<!-- preview model -->
<div class="arf_modal_overlay arf_whole_screen">
    <div id="form_previewmodal" class="arf_popup_container" style="overflow:hidden;">
        <div class="arf_preview_model_header">
            <div class="arf_preview_model_header_icons">
                <div onclick="arfchangedevice('computer');" title="<?php echo addslashes(esc_html__('Computer View', 'ARForms')); ?>" class="arfdevicesbg arfhelptip arf_preview_model_device_icon"><div id="arfcomputer" class="arfdevices arfactive"><svg width="75px" height="60px" viewBox="-16 -14 75 60"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M40.561,28.591H24.996v2.996h8.107c0.779,0,1.434,0.28,1.434,1.059  c0,0.779-0.655,0.935-1.434,0.935H9.951c-0.779,0-1.435-0.156-1.435-0.935c0-0.778,0.656-1.059,1.435-1.059h8.045v-2.996H2.452  c-0.779,0-1.435-0.656-1.435-1.435V2.086c0-0.779,0.656-1.434,1.435-1.434h38.109c0.778,0,1.434,0.655,1.434,1.434v25.071  C41.995,27.936,41.339,28.591,40.561,28.591z M22.996,31.587v-2.996h-3v2.996H22.996z M39.995,2.642H3.017v23.895h36.978V2.642z"/></svg></div></div>
                <div onclick="arfchangedevice('tablet');" title="<?php echo addslashes(esc_html__('Tablet View', 'ARForms')); ?>" class="arfdevicesbg arfhelptip arf_preview_model_device_icon"><div id="arftablet" class="arfdevices"><svg width="40px" height="60px" viewBox="-6 -15 40 60"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M23.091,33.642H4.088c-1.657,0-3-1.021-3-2.28V2.816  c0-1.259,1.343-2.28,3-2.28h19.003c1.657,0,3,1.021,3,2.28v28.546C26.091,32.622,24.749,33.642,23.091,33.642z M4.955,31.685h17.262  c1.035,0,1.875-0.638,1.875-1.425v-4.694H3.08v4.694C3.08,31.047,3.92,31.685,4.955,31.685z M24.092,4.002  c0-0.787-0.84-1.425-1.875-1.425H4.955c-1.035,0-1.875,0.638-1.875,1.425v1.563h21.012V4.002z M3.08,7.566v16h21.012v-16H3.08z   M13.618,26.551c1.09,0,1.974,0.896,1.974,2s-0.884,2-1.974,2c-1.09,0-1.974-0.896-1.974-2S12.527,26.551,13.618,26.551zz"/></svg></div></div>
                <div onclick="arfchangedevice('mobile');" title="<?php echo addslashes(esc_html__('Mobile View', 'ARForms')); ?>" class="arfdevicesbg arfhelptip arf_preview_model_device_icon"><div id="arfmobile" class="arfdevices"><svg width="45px" height="60px" viewBox="-12 -15 45 60"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M17.894,33.726H3.452c-1.259,0-2.28-1.021-2.28-2.28V2.899  c0-1.259,1.021-2.28,2.28-2.28h14.442c1.259,0,2.28,1.021,2.28,2.28v28.546C20.174,32.705,19.153,33.726,17.894,33.726z   M18.18,4.086c0-0.787-0.638-1.425-1.425-1.425H4.585c-0.787,0-1.425,0.638-1.425,1.425v26.258c0,0.787,0.638,1.425,1.425,1.425  h12.169c0.787,0,1.425-0.638,1.425-1.425V4.086z M13.787,6.656H7.568c-0.252,0-0.456-0.43-0.456-0.959s0.204-0.959,0.456-0.959  h6.218c0.251,0,0.456,0.429,0.456,0.959S14.038,6.656,13.787,6.656z M10.693,25.635c1.104,0,2,0.896,2,2c0,1.105-0.895,2-2,2  c-1.105,0-2-0.895-2-2C8.693,26.53,9.588,25.635,10.693,25.635z"/></svg></div></div>
            </div>
            <div class="arf_popup_header_close_button" data-dismiss="arfmodal"><svg width="16px" height="16px" viewBox="0 0 12 12"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></svg></div>
        </div>
        <div class="arfmodal-body" style=" overflow:hidden; clear:both;padding:0;">
            <div class="iframe_loader arf_editor_preview_loader" align="center"><?php echo ARF_LOADER_ICON; ?></div>
            <iframe id="arfdevicepreview" name="arf_preview_frame" src="" frameborder="0" height="100%" width="100%"></iframe>
        </div>
    </div>
</div>
<!-- preview model -->

<!-- CSS Code Expand Model -->
<div class="arf_modal_overlay">
    <div id="arf_other_css_expanded_model"  class="arf_popup_container arf_popup_container_other_css_expanded_model" style="overflow:hidden;">
        <div class="arf_other_css_expanded_model_header">
            <span><?php echo addslashes(esc_html__('Custom CSS','ARForms')); ?></span>
            <div class="arf_other_css_expanded_add_element_btn" id="arf_expand_css_code_element_button">
                <span><?php echo addslashes(esc_html__('Add CSS Elements','ARForms')); ?></span>
                <i class="fas fa-caret-down"></i>
                <ul class="arf_custom_css_cloud_list_wrapper">
                <?php
                    global $custom_css_array;
                    foreach($custom_css_array as $key => $value ){
                        ?>
                        <li data-target="expanded" class="arf_custom_css_cloud_list_item <?php echo (isset($values[$key]) && $values[$key] != '') ? 'arfactive' : ''; ?>" id="<?php echo $value['onclick_1']; ?>"><span><?php echo $value['label_title']; ?></span></li>
                        <?php
                    }
                ?>
                </ul>
            </div>
        </div>
        <div class="arf_other_css_expanded_model_container">
        <textarea id="arf_other_css_expanded_textarea"></textarea>
        </div>
        <div class="arf_popup_container_footer">
            <button type="button" class="arf_popup_close_button" id="arf_css_expanded_model_btn">OK</button>
        </div>
    </div>
</div>
<!-- CSS Code Expand Model -->

<!-- Field Option Model -->
<?php require_once VIEWS_PATH . '/arf_field_option_popup.php'; ?>
<!-- Field Option Model -->

<!-- Field Value Model -->
<?php require_once VIEWS_PATH . '/arf_field_values_popup.php'; ?>
<!-- Field Value Model -->

<?php do_action( 'arf_load_field_model_outside' ); ?>

<!-- new field array -->
<?php require(VIEWS_PATH . '/new_field_array.php'); ?>
<!-- new field array -->

<?php require(VIEWS_PATH . '/footer.php'); ?>