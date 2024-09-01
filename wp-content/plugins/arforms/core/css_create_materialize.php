<?php

if (!isset($saving)){
    header("Content-type: text/css");
}

if( !isset( $is_form_save ) ){
    $is_form_save = false;
}

if( !isset($is_prefix_suffix_enable)){
    $is_prefix_suffix_enable = false;
}

if( !isset($arf_page_break_wizard_theme_style)){
    $arf_page_break_wizard_theme_style = '';
}

if( !isset($is_checkbox_img_enable)){
    $is_checkbox_img_enable = false;
}
if(!isset($is_img_crop_enable)){
    $is_img_crop_enable = false;
}
if( !isset($is_radio_img_enable)){
    $is_radio_img_enable = false;
}
if( !isset($scale_field_available)){
    $scale_field_available = false;
    $scale_field_size = 24;
}

if( !isset( $preview ) ){
    $preview = false;
}

$form_id = isset($form_id) ? $form_id : '';

foreach ($new_values as $k => $v) {

    if (( preg_match('/color/', $k) or in_array($k, array('arferrorbgsetting', 'arferrorbordersetting', 'arferrortextsetting')) ) && !in_array($k, array('arfcheckradiocolor'))) {
        if(strpos($v,'#') === false) {
            $new_values[$k] = '#' . $v;
        } else {
            $new_values[$k] = $v;
        }
    } else {
        $new_values[$k] = $v;
    }
}

global $arsettingcontroller,$arformcontroller,$arfsettings, $arfieldhelper, $arfform, $MdlDb;

/**Basic Styling Options */

    /** color related variables */
    $arf_mainstyle = !empty($new_values['arfinputstyle']) ? $new_values['arfinputstyle'] : '';
    
    $form_bg_color = !empty($new_values['arfmainformbgcolorsetting']) ? $new_values['arfmainformbgcolorsetting'] : '';
    $form_title_color = isset($new_values['arfmainformtitlecolorsetting']) ? $new_values['arfmainformtitlecolorsetting'] : '';
    $form_border_color = isset($new_values['arfmainfieldsetcolor']) ? $new_values['arfmainfieldsetcolor'] : '';
    $form_border_shadow_color = isset($new_values['arfmainformbordershadowcolorsetting']) ? $new_values['arfmainformbordershadowcolorsetting'] : '';

    $section_background = isset($new_values['arfformsectionbackgroundcolor']) ? $new_values['arfformsectionbackgroundcolor'] : '#ffffff';
    $arf_section_inherit_bg = isset($new_values['arf_section_inherit_bg']) ? $new_values['arf_section_inherit_bg']  : 0;
    if( empty( $arf_section_inherit_bg ) ){
        $section_background = 'transparent';
    }

    $base_color = isset($new_values['arfmainbasecolor']) ? $new_values['arfmainbasecolor'] : '';
    $field_text_color = isset( $new_values['text_color'] ) ? $new_values['text_color'] : '';
    $field_border_color = isset( $new_values['border_color'] ) ? $new_values['border_color'] : '';
    $field_bg_color = isset( $new_values['bg_color'] ) ? $new_values['bg_color'] : '';
    $field_focus_bg_color = isset($new_values['arfbgactivecolorsetting']) ? $new_values['arfbgactivecolorsetting'] : '';
    $field_error_bg_color = isset($new_values['arferrorbgcolorsetting']) ? $new_values['arferrorbgcolorsetting']  : '';
    $field_focus_border_color = !empty($new_values['arfborderactivecolorsetting']) ? $new_values['arfborderactivecolorsetting'] : '#fff';
    $field_label_txt_color = isset( $new_values['label_color'] ) ? $new_values['label_color'] : '';
    $prefix_suffix_bg_color = isset($new_values['prefix_suffix_bg_color']) ? str_replace('##', '#', $new_values['prefix_suffix_bg_color']) : '';
    $prefix_suffix_icon_color = isset($new_values['prefix_suffix_icon_color']) ? $new_values['prefix_suffix_icon_color'] : '';

    $like_btn_color = isset($new_values['arflikebtncolor']) ? $new_values['arflikebtncolor'] : '';
    $dislike_btn_color = isset($new_values['arfdislikebtncolor']) ? $new_values['arfdislikebtncolor'] : '';

    $slider_selection_color = isset($new_values['arfsliderselectioncolor']) ? $new_values['arfsliderselectioncolor'] : '';
    $slider_track_color = isset($new_values['arfslidertrackcolor']) ? $new_values['arfslidertrackcolor'] : '';

    $star_rating_color = isset($new_values['arfstarratingcolor']) ? $new_values['arfstarratingcolor'] : '';

    $tooltip_bg_color = isset($new_values['arf_tooltip_bg_color']) ? $new_values['arf_tooltip_bg_color'] : '';
    $tooltip_font_color = isset($new_values['arf_tooltip_font_color']) ? $new_values['arf_tooltip_font_color'] : '';

    $arf_matrix_odd_bgcolor = isset( $new_values['arf_matrix_odd_bgcolor'] ) ? $new_values['arf_matrix_odd_bgcolor'] : '#F4F4F4';
    $arf_matrix_even_bgcolor = isset( $new_values['arf_matrix_even_bgcolor'] ) ? $new_values['arf_matrix_even_bgcolor'] : '#FFFFFF';

    $arf_date_picker_text_color = isset($new_values['arfdatepickertextcolorsetting']) ? $new_values['arfdatepickertextcolorsetting'] : '#46484d';

    $arf_matrix_inherit_bg = isset( $new_values['arf_matrix_inherit_bg']) ? $new_values['arf_matrix_inherit_bg'] : 0;
    if( 1 == $arf_matrix_inherit_bg ){
        $arf_matrix_odd_bgcolor = $arf_matrix_even_bgcolor = 'transparent';
    }

    $submit_text_color = isset($new_values['arfsubmittextcolorsetting']) ? $new_values['arfsubmittextcolorsetting'] : '';
    $submit_bg_color = isset($new_values['submit_bg_color']) ? $new_values['submit_bg_color'] : '';
    $submit_bg_color_hover = isset($new_values['arfsubmitbuttonbgcolorhoversetting']) ? str_replace("##", '#', $new_values['arfsubmitbuttonbgcolorhoversetting']) :'';
    $submit_border_color = isset($new_values['arfsubmitbordercolorsetting']) ? $new_values['arfsubmitbordercolorsetting'] : '';
    $submit_shadow_color = isset($new_values['arfsubmitshadowcolorsetting']) ? str_replace("##", '#',$new_values['arfsubmitshadowcolorsetting']) : '';

    $pg_wizard_active_bg_color = isset($new_values['bg_color_pg_break']) ? $new_values['bg_color_pg_break'] : '';
    $pg_wizard_inactive_bg_color = isset($new_values['bg_inavtive_color_pg_break']) ? $new_values['bg_inavtive_color_pg_break']  : '';
    $pg_wizard_text_color = isset($new_values['text_color_pg_break']) ? $new_values['text_color_pg_break'] : '';
    $pg_wizard_text_color_style3 = isset($new_values['text_color_pg_break_style3']) ? $new_values['text_color_pg_break_style3'] : '';

    $arf_bar_color_survey = isset($new_values['bar_color_survey']) ? $new_values['bar_color_survey'] : '';
    $arf_bg_color_survey = isset($new_values['bg_color_survey']) ? $new_values['bg_color_survey'] : '';
    $arf_text_color_survey = isset($new_values['text_color_survey']) ? $new_values['text_color_survey'] : '';

    $success_bg_color = isset($new_values['arfsucessbgcolorsetting'] ) ? $new_values['arfsucessbgcolorsetting'] : '';
    $success_border_color = isset($new_values['arfsucessbordercolorsetting']) ? $new_values['arfsucessbordercolorsetting'] : '';
    $success_text_color = isset($new_values['arfsucesstextcolorsetting']) ? $new_values['arfsucesstextcolorsetting'] : '';

    $error_bg_color = isset($new_values['arfformerrorbgcolorsettings']) ? $new_values['arfformerrorbgcolorsettings'] : '';
    $error_border_color = isset($new_values['arfformerrorbordercolorsettings']) ? $new_values['arfformerrorbordercolorsettings'] : "";
    $error_txt_color = isset($new_values['arfformerrortextcolorsettings']) ? $new_values['arfformerrortextcolorsettings'] : "";

    $arferrorstylecolor = isset($new_values['arfvalidationbgcolorsetting']) ? $new_values['arfvalidationbgcolorsetting'] : '';
    $arferrorstylecolorfont = isset($new_values['arfvalidationtextcolorsetting']) ? $new_values['arfvalidationtextcolorsetting'] : '';

    /** color related variables */

    /** Fonts Related variables */

    $arf_title_font_family = isset($new_values['arftitlefontfamily']) ? $new_values['arftitlefontfamily'] : '';
    $arf_title_font_size = isset( $new_values['form_title_font_size'] ) ? $new_values['form_title_font_size'].'px' : '24px';
    $arf_title_font_weight = isset($new_values['check_weight_form_title']) ? $new_values['check_weight_form_title'] : 'bold';
    $arf_title_font_style_arr = explode( ',', $arf_title_font_weight );
    $arf_title_font_style_str = '';
    if( in_array( 'bold', $arf_title_font_style_arr ) ){
        $arf_title_font_style_str .= 'font-weight:bold;';
    } else {
        $arf_title_font_style_str .= 'font-weight:normal;';
    }

    if( in_array( 'italic', $arf_title_font_style_arr ) ){
        $arf_title_font_style_str .= 'font-style:bold;';
    } else {
        $arf_title_font_style_str .= 'font-style:normal;';
    }

    if( in_array( 'underline', $arf_title_font_style_arr ) ){
        $arf_title_font_style_str .= 'text-decoration:underline;';
    } else if( in_array( 'strikethrough', $arf_title_font_style_arr ) ) {
        $arf_title_font_style_str .= 'text-decoration:line-through;';
    } else {
        $arf_title_font_style_str .= 'text-decoration:none;';
    }

    $arf_label_font_family = isset( $new_values['font'] ) ? $new_values['font'] : '';
    $arf_label_font_size = isset( $new_values['font_size'] ) ? $new_values['font_size'] : '';
    $arf_label_font_weight = isset( $new_values['weight'] ) ? $new_values['weight'] : 'normal';
    $arf_label_font_style_arr = explode( ',', $arf_label_font_weight );
    $arf_label_font_style_str = '';
    if( in_array( 'bold', $arf_label_font_style_arr ) ){
        $arf_label_font_style_str .= 'font-weight:bold;';
    } else {
        $arf_label_font_style_str .= 'font-weight:normal;';
    }

    if( in_array( 'italic', $arf_label_font_style_arr ) ){
        $arf_label_font_style_str .= 'font-style:bold;';
    } else {
        $arf_label_font_style_str .= 'font-style:normal;';
    }

    if( in_array( 'underline', $arf_label_font_style_arr ) ){
        $arf_label_font_style_str .= 'text-decoration:underline;';
    } else if( in_array( 'strikethrough', $arf_label_font_style_arr ) ) {
        $arf_label_font_style_str .= 'text-decoration:line-through;';
    } else {
        $arf_label_font_style_str .= 'text-decoration:none;';
    }

    $arf_input_font_family = isset( $new_values['check_font'] ) ? $new_values['check_font'] : '';
    $arf_input_font_size = isset( $new_values['field_font_size'] ) ? $new_values['field_font_size'] : '';
    $arf_input_font_weight = isset( $new_values['check_weight'] ) ? $new_values['check_weight'] : 'normal';
    $arf_input_font_style_arr = explode( ',', $arf_input_font_weight );
    $arf_input_font_style_str = '';
    if( in_array( 'bold', $arf_input_font_style_arr ) ){
        $arf_input_font_style_str .= 'font-weight:bold;';
    } else {
        $arf_input_font_style_str .= 'font-weight:normal;';
    }

    if( in_array( 'italic', $arf_input_font_style_arr ) ){
        $arf_input_font_style_str .= 'font-style:bold;';
    } else {
        $arf_input_font_style_str .= 'font-style:normal;';
    }

    if( in_array( 'underline', $arf_input_font_style_arr ) ){
        $arf_input_font_style_str .= 'text-decoration:underline;';
    } else if( in_array( 'strikethrough', $arf_input_font_style_arr ) ) {
        $arf_input_font_style_str .= 'text-decoration:line-through;';
    } else {
        $arf_input_font_style_str .= 'text-decoration:none;';
    }

    $arf_section_font_family = isset( $new_values['arfsectiontitlefamily'] ) ? $new_values['arfsectiontitlefamily'] : '';
    $arf_section_font_size = isset( $new_values['arfsectiontitlefontsizesetting'] ) ? $new_values['arfsectiontitlefontsizesetting'] : '';
    $arf_section_font_weight = isset( $new_values['arfsectiontitleweightsetting'] ) ? $new_values['arfsectiontitleweightsetting'] : 'bold';
    $arf_section_font_style_arr = explode( ',', $arf_section_font_weight );
    $arf_section_font_style_str = '';
    if( in_array( 'bold', $arf_section_font_style_arr ) ){
        $arf_section_font_style_str .= 'font-weight:bold;';
    } else {
        $arf_section_font_style_str .= 'font-weight:normal;';
    }

    if( in_array( 'italic', $arf_section_font_style_arr ) ){
        $arf_section_font_style_str .= 'font-style:bold;';
    } else {
        $arf_section_font_style_str .= 'font-style:normal;';
    }

    if( in_array( 'underline', $arf_section_font_style_arr ) ){
        $arf_section_font_style_str .= 'text-decoration:underline;';
    } else if( in_array( 'strikethrough', $arf_section_font_style_arr ) ) {
        $arf_section_font_style_str .= 'text-decoration:line-through;';
    } else {
        $arf_section_font_style_str .= 'text-decoration:none;';
    }

    $arf_submit_btn_font_family = isset($new_values['arfsubmitfontfamily']) ? $new_values['arfsubmitfontfamily'] : '';
    $arf_submit_btn_font_size = isset($new_values['arfsubmitbuttonfontsizesetting']) ? $new_values['arfsubmitbuttonfontsizesetting'] : '';
    $arf_submit_btn_font_weight = isset( $new_values['arfsubmitweightsetting'] ) ? $new_values['arfsubmitweightsetting'] : 'normal';
    $arf_submit_font_style_arr = explode( ',', $arf_submit_btn_font_weight );
    $arf_submit_font_style_str = '';
    if( in_array( 'bold', $arf_submit_font_style_arr ) ){
        $arf_submit_font_style_str .= 'font-weight:bold;';
    } else {
        $arf_submit_font_style_str .= 'font-weight:normal;';
    }

    if( in_array( 'italic', $arf_submit_font_style_arr ) ){
        $arf_submit_font_style_str .= 'font-style:bold;';
    } else {
        $arf_submit_font_style_str .= 'font-style:normal;';
    }

    if( in_array( 'underline', $arf_submit_font_style_arr ) ){
        $arf_submit_font_style_str .= 'text-decoration:underline;';
    } else if( in_array( 'strikethrough', $arf_submit_font_style_arr ) ) {
        $arf_submit_font_style_str .= 'text-decoration:line-through;';
    } else {
        $arf_submit_font_style_str .= 'text-decoration:none;';
    }

    $arf_validation_font_family = isset( $new_values['error_font'] ) ? $new_values['error_font'] : '';
    $arf_validation_font_size = isset($new_values['arffontsizesetting']) ? $new_values['arffontsizesetting'] . 'px' : '20px;';

    /** Fonts Related variables */

    /** Form width Variables */

    $form_width = isset($new_values['arfmainformwidth']) ? $new_values['arfmainformwidth'] : '';
    $form_width_unit = isset( $new_values['form_width_unit'] ) ? $new_values['form_width_unit'] : '';

    /** Form width Variables */

    /** success message position */
    $success_message_position = isset( $new_values['arfsuccessmsgposition'] ) ? $new_values['arfsuccessmsgposition'] : 'top';
    /** success message position */

    /**Validation Message Style */
    
    $arf_error_style = isset($new_values['arferrorstyle']) ? $new_values['arferrorstyle'] : '';
    $arf_error_style_position = isset($new_values['arferrorstyleposition']) ? $new_values['arferrorstyleposition'] : '';
    $arf_standard_error_position = isset($new_values['arfstandarderrposition']) ? $new_values['arfstandarderrposition'] : 'relative';

    /**Validation Message Style */

/**Basic Styling Options */

/** Advanced Form Options */

    /** Form title Options */

    $arf_form_title_alignment = isset($new_values['arfformtitlealign']) ? $new_values['arfformtitlealign'] : '';
    $arf_form_title_margin = isset($new_values['arfmainformtitlepaddingsetting']) ? $new_values['arfmainformtitlepaddingsetting'] : '';

    /** Form title Options */

    /** Form Settings Options */
    $arf_form_alignment = isset( $new_values['form_align'] ) ? $new_values['form_align'] : '';
    
    $arf_form_bg_image = isset( $new_values['arfmainform_bg_img'] ) ? $new_values['arfmainform_bg_img'] : '';
    
    $arf_form_bg_posx = isset( $new_values['arf_bg_position_x'] ) ? $new_values['arf_bg_position_x'] : 'center';
    $arf_form_bg_posy = isset( $new_values['arf_bg_position_y'] ) ? $new_values['arf_bg_position_y'] : 'center';
    $arf_form_bg_posx_custom = isset( $new_values['arf_bg_position_input_x'] ) ? $new_values['arf_bg_position_input_x'] : '';
    $arf_form_bg_posy_custom = isset( $new_values['arf_bg_position_input_y'] ) ? $new_values['arf_bg_position_input_y'] : '';

    $arf_form_padding = isset( $new_values['arfmainfieldsetpadding'] ) ? $new_values['arfmainfieldsetpadding'] : '';

    $arf_section_padding = isset( $new_values['arfsectionpaddingsetting'] ) ? $new_values['arfsectionpaddingsetting'] : '';
    /** Form Settings Options */

    /** Form Border Options */
    $arf_form_border_type = isset( $new_values['form_border_shadow'] ) ? $new_values['form_border_shadow'] : 'border';
    $arf_form_border_width = !empty( $new_values['fieldset'] ) ? $new_values['fieldset'] . 'px' : '0';
    $arf_form_border_radius = !empty( $new_values['arfmainfieldsetradius'] ) ? $new_values['arfmainfieldsetradius'] . 'px' : '0';
    /** Form Border Options */
    
    /** Form Opacity Options */
    $arf_form_opacity = isset($new_values['arfmainform_opacity']) ? $new_values['arfmainform_opacity'] : '';
    /** Form Opacity Options */

/** Advanced Form Options */

/** Input Field Options */

    /** Label Options */
    $arf_label_position = isset( $new_values['position'] ) ? $new_values['position'] : '';
    $arf_label_align = isset($new_values['align']) ? $new_values['align'] : '';
    $arf_label_width = isset( $new_values['width'] ) ? $new_values['width'] : '';
    $arf_hide_label = isset( $new_values['hide_labels'] ) ? $new_values['hide_labels'] : '';
    /** Label Options */

    /** Input Field Description Options */
    $description_font_size = isset($new_values['arfdescfontsizesetting']) ? $new_values['arfdescfontsizesetting'] : '';
    $description_align = isset($new_values['arfdescalighsetting']) ? $new_values['arfdescalighsetting'] : '';
    /** Input Field Description Options */

    /** Input Field Option */

    $arf_input_field_width = isset( $new_values['field_width'] ) ? $new_values['field_width'] : '';
    $arf_input_field_width_unit = isset( $new_values['field_width_unit'] ) ? $new_values['field_width_unit'] : '';
    $text_direction = isset( $new_values['text_direction'] ) ? $new_values['text_direction'] : '';
    $arf_input_field_direction = ($text_direction == 0) ? 'rtl' : 'ltr';
    $arf_input_field_text_align = ($text_direction == 0 ) ? 'right' : 'left';
    $arfmainfield_opacity = isset( $new_values['arfmainfield_opacity'] ) ? $new_values['arfmainfield_opacity'] : '';
    $arf_required_indicator = isset($new_values['arf_req_indicator'])?$new_values['arf_req_indicator']:'0';
    $field_margin =  empty( $new_values['arffieldmarginssetting'] ) ? '0' : $new_values['arffieldmarginssetting'] . 'px';
    $placeholder_opacity = isset($new_values['arfplaceholder_opacity']) ? $new_values['arfplaceholder_opacity'] : '';
    $arf_field_inner_padding = isset($new_values['arffieldinnermarginssetting']) ? $new_values['arffieldinnermarginssetting'] : 0;
    $field_border_width =  empty( $new_values['arffieldborderwidthsetting'] )  ? '0' : $new_values['arffieldborderwidthsetting'] . 'px';
    $field_border_radius = 0;
    $field_border_style = isset($new_values['arffieldborderstylesetting']) ? $new_values['arffieldborderstylesetting'] : '';
    
    $fieldpadding = explode(' ', $arf_field_inner_padding);
    $fieldpadding_1 = $fieldpadding[0];
    $fieldpadding_1 = str_replace('px', '', $fieldpadding_1);
    $fieldpadding_2 = 0;
    if(count($fieldpadding)>1){
        $fieldpadding_2 = $fieldpadding[1];
        $fieldpadding_2 = str_replace('px', '', $fieldpadding_2);
    }
    
    
    $field_ptop = $fieldpadding_1;
    $field_pleft = $fieldpadding_2;

    /** Input Field Option */

    /** Page Break Timer Option */
    $arfadd_pagebreak_timer = !empty($new_values['arfsettimer'] )  ? $new_values['arfsettimer'] : 0;
    $arfpagebreak_timer_style = !empty($new_values['arftimerstyle']) ? $new_values['arftimerstyle'] : '';
    /** Page Break Timer Option */

    /** Checkbox/Radio Style */
    $arfcheck_style_name = isset($new_values['arfcheckradiostyle']) ? $new_values['arfcheckradiostyle'] : '';
    /** Checkbox/Radio Style */

/** Input Field Options */
    
/** Field Animation Options */
    $arffield_animation_style = isset($new_values['arffieldanimationstyle']) ? $new_values['arffieldanimationstyle'] : '';
    $arfpbfield_animation_style = isset($new_values['arfpbfieldanimationstyle']) ? $new_values['arfpbfieldanimationstyle'] : '';
/** Field Animation Options */

/** Submit Button Option */

    $submit_align = isset( $new_values['arfsubmitalignsetting'] ) ? $new_values['arfsubmitalignsetting'] : '';
    $submit_width = empty( $new_values['arfsubmitbuttonwidthsetting'] ) ? '' : $new_values['arfsubmitbuttonwidthsetting'] . 'px';
    $submit_auto_width = (empty( $new_values['arfsubmitautowidth'] ) || $new_values['arfsubmitautowidth'] < 100 ) ? '100' : $new_values['arfsubmitautowidth'];
    $submit_height = ($new_values['arfsubmitbuttonheightsetting'] == '') ? '36' : $new_values['arfsubmitbuttonheightsetting'];
    $arfsubmitbuttonstyle = isset($new_values['arfsubmitbuttonstyle']) ? $new_values['arfsubmitbuttonstyle'] : 'border';
    if( $is_form_save ){
        $arfsubmitbuttonstyle = 'border reverse border flat';
    }
    $submit_margin = empty($new_values['arfsubmitbuttonmarginsetting']) ? '0' : $new_values['arfsubmitbuttonmarginsetting'];
    
    $submit_bg_img = isset($new_values['submit_bg_img']) ? $new_values['submit_bg_img'] : '';
    $submit_hover_bg_img = isset($new_values['submit_hover_bg_img']) ? $new_values['submit_hover_bg_img'] : '';

    $submit_border_width = ($new_values['arfsubmitborderwidthsetting'] == '') ? '0px' : $new_values['arfsubmitborderwidthsetting'] . 'px';
    $submit_border_radius = ($new_values['arfsubmitborderradiussetting'] == '') ? '0px' : $new_values['arfsubmitborderradiussetting'] . 'px';
    $submit_xoffset_shadow = ($new_values['arfsubmitboxxoffsetsetting'] == '') ? '0px' : $new_values['arfsubmitboxxoffsetsetting'] .'px';
    $submit_yoffset_shadow = ($new_values['arfsubmitboxyoffsetsetting'] == '') ? '0px' : $new_values['arfsubmitboxyoffsetsetting'] .'px';
    $submit_blur_shadow = ($new_values['arfsubmitboxblursetting'] == '') ? '0px' : $new_values['arfsubmitboxblursetting'] .'px';
    $submit_spread_shadow = ($new_values['arfsubmitboxshadowsetting'] == '') ? '0px' : $new_values['arfsubmitboxshadowsetting'] .'px';

/** Submit Button Option */

$character_set = isset($arfsettings->arf_css_character_set) && !empty( $arfsettings->arf_css_character_set ) ? (array)$arfsettings->arf_css_character_set : array();

$subset = count($character_set) > 0 ? "&subset=". implode(',',$character_set) : '';
$swap_display = '&display=swap';

$loaded_gfonts = array(
    'Arial',
    'Helvetica',
    'sans-serif',
    'Lucida Grande',
    'Lucida Sans Unicode',
    'Tahoma',
    'Times New Roman',
    'Courier New',
    'Verdana',
    'Geneva',
    'Courier',
    'Monospace',
    'Times',
    'inherit'
);

if (is_ssl() || $arfssl == 1){    
    $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
} else{
    $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
}

$arf_form_cls_prefix = "#arffrm_{$form_id}_container";
$arf_form_cls_prefix_without_material_container = "#arffrm_{$form_id}_container";
$arf_checkbox_not_admin = "";
$arf_prefix_cls = ".arf_prefix";
$arf_suffix_cls = ".arf_suffix";
$arf_prefix_suffix_wrapper_cls = ".arf_prefix_suffix_wrapper";
if( !empty( $is_form_save ) && true == $arf_form_cls_prefix ){
    $arf_form_cls_prefix = ".ar_main_div_{$form_id} ";
    $input_fields = $arfieldhelper->arf_input_field_keys();
    $other_fields = $arfieldhelper->arf_other_fields_keys();
    $arf_prefix_cls = ".arf_editor_prefix_icon";
    $arf_suffix_cls = ".arf_editor_suffix_icon";
    $arf_prefix_suffix_wrapper_cls = ".arf_editor_prefix_suffix_wrapper";
    $is_prefix_suffix_enable = true;
    $is_checkbox_img_enable = true;
    $is_radio_img_enable = true;
    $loaded_field = array_merge( $input_fields, $other_fields );
    $arf_hide_label = true;
    $arf_checkbox_not_admin = ':not(.arf_enable_radio_image_editor)';
} else {

    $arf_form_cls_prefix .= " .arf_materialize_form";
    if( !empty( $arf_title_font_family ) && !in_array( $arf_title_font_family, $loaded_gfonts ) ){
        echo "@import url(" . $googlefontbaseurl . urlencode($arf_title_font_family) . $subset . $swap_display . ");";
        array_push( $loaded_gfonts, $arf_title_font_family );
    }
    
    if( !empty( $arf_label_font_family ) && !in_array( $arf_label_font_family, $loaded_gfonts ) ){
        echo "@import url(" . $googlefontbaseurl . urlencode($arf_label_font_family) . $subset . $swap_display . ");";
        array_push( $loaded_gfonts, $arf_label_font_family );
    }
    
    if( !empty( $arf_input_font_family ) && !in_array( $arf_input_font_family, $loaded_gfonts ) ){
        echo "@import url(" . $googlefontbaseurl . urlencode($arf_input_font_family) . $subset . $swap_display . ");";
        array_push( $loaded_gfonts, $arf_input_font_family );
    }
    
    if( !empty( $arf_section_font_family ) && !in_array( $arf_section_font_family, $loaded_gfonts ) ){
        echo "@import url(" . $googlefontbaseurl . urlencode($arf_section_font_family) . $subset . $swap_display . ");";
        array_push( $loaded_gfonts, $arf_section_font_family );
    }
    
    if( !empty( $arf_submit_btn_font_family ) && !in_array( $arf_submit_btn_font_family, $loaded_gfonts ) ){
        echo "@import url(" . $googlefontbaseurl . urlencode($arf_submit_btn_font_family) . $subset . $swap_display . ");";
        array_push( $loaded_gfonts, $arf_submit_btn_font_family );
    }
    
    if( !empty( $arf_validation_font_family ) && !in_array( $arf_validation_font_family, $loaded_gfonts ) ){
        echo "@import url(" . $googlefontbaseurl . urlencode($arf_validation_font_family) . $subset . $swap_display . ");";
        array_push( $loaded_gfonts, $arf_validation_font_family );
    }
}

$common_field_type_styling = array( 'text', 'email', 'phone', 'tel', 'number', 'date', 'time', 'url', 'image', 'password', 'arf_autocomplete', 'arf_spinner' );

/** Form Level Styling */
if( false == $is_form_save ){
    echo "#arffrm_{$form_id}_container{ max-width:{$form_width}{$form_width_unit}; margin:0 auto;}";
} else {
    echo ".ar_main_div_{$form_id}{ max-width:{$form_width}{$form_width_unit}; margin:0 auto;}";
    
}

echo "$arf_form_cls_prefix_without_material_container *{";
    echo "box-sizing:border-box;";
    echo "-webkit-box-sizing:border-box;";
    echo "-o-box-sizing:border-box;";
    echo "-moz-box-sizing:border-box;";
echo "}";

if( false == $is_form_save ){
    echo $arf_form_cls_prefix_without_material_container . " form{ text-align:{$arf_form_alignment}; }";
} else {
    echo $arf_form_cls_prefix . "{ text-align:{$arf_form_alignment}; }";
}

echo $arf_form_cls_prefix . ".arf_fieldset{";
    $frm_bg_color = !empty( $form_bg_color ) ? $form_bg_color : '0,0,0';
    $frm_bg_color = $arsettingcontroller->hex2rgb( $frm_bg_color );
    if( !empty( $arf_form_bg_image ) ){
        echo "background:rgba({$frm_bg_color},{$arf_form_opacity}) url({$arf_form_bg_image});";
        if( 'px' == $arf_form_bg_posx ){
            echo "background-position-x:" . $arf_form_bg_posx_custom ."px;";
        } else {
            echo "background-position-x:" . $arf_form_bg_posx .";";
        }
        if( 'px' == $arf_form_bg_posy ){
            echo "background-position-y:" . $arf_form_bg_posy_custom ."px;";
        } else {
            echo "background-position-y:" . $arf_form_bg_posy .";";
        }
        echo "background-repeat: no-repeat;";
    } else {
        echo "background:rgba({$frm_bg_color},{$arf_form_opacity});";
    }
    echo "border:{$arf_form_border_width} solid {$form_border_color};";
    echo "padding:{$arf_form_padding};";
    echo "border-radius:{$arf_form_border_radius};";
    echo "-webkit-border-radius:{$arf_form_border_radius};";
    echo "-o-border-radius:{$arf_form_border_radius};";
    echo "-moz-border-radius:{$arf_form_border_radius};";
    if( 'shadow' == $arf_form_border_type ){
        echo "-moz-box-shadow:0px 0px 7px 2px {$form_border_shadow_color};
        -o-box-shadow:0px 0px 7px 2px {$form_border_shadow_color};
        -webkit-box-shadow:0px 0px 7px 2px {$form_border_shadow_color};
        box-shadow:0px 0px 7px 2px {$form_border_shadow_color};";
    } else {
        echo "-moz-box-shadow:none;-webkit-box-shadow:none;-o-box-shadow:none;box-shadow:none;";
    }
echo "}";

echo $arf_form_cls_prefix . " .arftitlecontainer{margin:{$arf_form_title_margin}; text-align:{$arf_form_title_alignment};}";
echo $arf_form_cls_prefix . " .formtitle_style{";
    echo "color:{$form_title_color};";
    echo "font-family:".stripslashes($arf_title_font_family).";";
    echo "font-size:{$arf_title_font_size};";
    echo $arf_title_font_style_str;
echo "}";

echo $arf_form_cls_prefix . " .page_break{ margin-bottom:30px; }";

if( !empty( $form->description ) ){
    echo $arf_form_cls_prefix . " div.formdescription_style{";
        echo "text-align:{$arf_form_title_alignment};";
        echo "color:{$form_title_color};";
        echo "font-family:{$arf_title_font_family};";
        echo "font-size:{$description_font_size}px;";
    echo "}";
} else if( $is_form_save ){
    echo $arf_form_cls_prefix . " .arfeditorformdescription{";
        echo "text-align:{$arf_form_title_alignment};";
        echo "color:{$form_title_color};";
        echo "font-family:{$arf_title_font_family};";
        echo "font-size:{$description_font_size}px;";
    echo "}";
}

/** Form Level Styling */

/** Success/Error Message Styling */
if( empty( $is_form_save ) ){
    echo $arf_form_cls_prefix_without_material_container . " #arf_message_success_popup,";
    echo $arf_form_cls_prefix_without_material_container . " #arf_message_success{";
        echo "width:100%;display:inline-block;min-height:35px;margin:15px 0 15px 0;";
        echo "border:1px solid {$success_border_color};";
        echo "border-radius:3px;";
        echo "-webkit-border-radius:3px;";
        echo "-o-border-radius:3px;";
        echo "-moz-border-radius:3px;";
        echo "font-family:{$arf_validation_font_family};";
        echo "font-size:20px;";
        echo "background:{$success_bg_color};";
        echo "color:{$success_text_color};";
    echo "}";

    echo $arf_form_cls_prefix_without_material_container . " #arf_message_success_popup .msg-detail::before,";
    echo $arf_form_cls_prefix_without_material_container . " #arf_message_success .msg-detail::before{";
        echo "background-image: url(data:image/svg+xml;base64,".base64_encode('<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 52 52" enable-background="new 0 0 52 52" xml:space="preserve"><g><path fill="'.$success_text_color.'" d="M26,0C11.66,0,0,11.66,0,26s11.66,26,26,26s26-11.66,26-26S40.34,0,26,0z M26,50C12.77,50,2,39.23,2,26   S12.77,2,26,2s24,10.77,24,24S39.23,50,26,50z"/><path fill="'.$success_text_color.'" d="M38.25,15.34L22.88,32.63l-9.26-7.41c-0.43-0.34-1.06-0.27-1.41,0.16c-0.35,0.43-0.28,1.06,0.16,1.41l10,8   C22.56,34.93,22.78,35,23,35c0.28,0,0.55-0.11,0.75-0.34l16-18c0.37-0.41,0.33-1.04-0.08-1.41C39.25,14.88,38.62,14.92,38.25,15.34   z"/></g></svg>').");";
        echo "content:'';width: 60px;height: 60px;display: block;margin: 0 auto;background-repeat: no-repeat;position:relative;";
    echo "}";

    echo $arf_form_cls_prefix_without_material_container . " .frm_error_style{
        width:100%; 
        display: inline-block; 
        float:none; 
        min-height:35px; 
        margin: 10px 0 10px 0;
        border: 1px solid {$error_border_color};
        background: {$error_bg_color}; 
        color:{$error_txt_color};
        font-family:".stripslashes($arf_validation_font_family)."; 
        font-weight:normal; 
        -moz-border-radius:3px;  
        -webkit-border-radius:3px; 
        -o-border-radius:3px; 
        border-radius:3px;
        font-size:20px; 
        word-break:break-all;";
    echo "}";

    echo $arf_form_cls_prefix_without_material_container . " .frm_error_style .msg-detail::before{";
        echo "background-image: url(data:image/svg+xml;base64,".base64_encode('<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0" y="0" viewBox="10 10 100 100" enable-background="new 10 10 100 100" xml:space="preserve" height="60" width="60"><g><circle fill="none" stroke="'.$error_txt_color.'" stroke-width="4" stroke-miterlimit="10" cx="60" cy="60" r="47"></circle><line fill="none" stroke="'.$error_txt_color.'" stroke-width="4" stroke-miterlimit="10" x1="81.214" y1="81.213" x2="38.787" y2="38.787"></line><line fill="none" stroke="'.$error_txt_color.'" stroke-width="4" stroke-miterlimit="10" x1="38.787" y1="81.213" x2="81.214" y2="38.787"></line></g></svg>').");";
        echo "content:'';width: 60px;height: 60px;display: block;margin: 0 auto;background-repeat: no-repeat;position:relative;";
    echo "}";

    echo $arf_form_cls_prefix_without_material_container ." .arf_res_front_msg_desc {";
        echo "padding:10px 0 10px 0px;";
        echo "letter-spacing:0.1px;";
        echo "width:100%;";
        echo "vertical-align:middle;";
        echo "display:inline-block;";
        echo "text-align:center;";
    echo "}";
}
/** Success/Error Message Styling */

/** Submit Button Styling */
echo $arf_form_cls_prefix . " .arf_submit_div { clear:both; text-align:{$submit_align}}";
echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn{";
    echo "height:{$submit_height}px;";
    if( '' == $submit_width ){
        echo "min-width:{$submit_auto_width}px;";
    } else {
        echo "width:{$submit_width};";
    }
    echo "text-align:center;max-width:100%;display:inline-block;";
    echo "font-family:{$arf_submit_btn_font_family};";
    echo "font-size:{$arf_submit_btn_font_size}px;";
    echo $arf_submit_font_style_str;
    echo "cursor:pointer;outline:none;line-height:1.3;padding:0;";
    echo "box-shadow:{$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color};";
    echo "-webkit-box-shadow:{$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color};";
    echo "-o-box-shadow:{$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color};";
    echo "-moz-box-shadow:{$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color};";

    echo "background-position: left top;";
    echo "border-radius:{$submit_border_radius};";
    echo "-webkit-border-radius:{$submit_border_radius};";
    echo "-o-border-radius:{$submit_border_radius};";
    echo "-moz-border-radius:{$submit_border_radius};";
    echo "position:relative;";
    echo "transition: .2s ease-out;";
    echo "-webkit-transition: .2s ease-out;";
    echo "-o-transition: .2s ease-out;";
    echo "-moz-transition: .2s ease-out;";
    echo "box-sizing:content-box;";    
    echo "margin:0;";    
echo "}";

if( preg_match( '/flat/', $arfsubmitbuttonstyle ) ){
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_flat{";
        echo "background:{$submit_bg_color}" . ( !empty( $submit_bg_img ) ? " url({$submit_bg_img}) " : "" ) . ";";
        if( !empty( $submit_bg_img ) ){
            echo "color:transparent;";
        } else {
            echo "color:{$submit_text_color};";
        }        
        echo "border:{$submit_border_width} solid {$submit_bg_color};";
    echo "}";

    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_flat.arf_active_loader .arfsubmitloader{";
        echo "width:{$arf_submit_btn_font_size}px;";
        echo "height:{$arf_submit_btn_font_size}px;";
        echo "border:" . ceil($arf_submit_btn_font_size / 8) . "px solid {$submit_text_color};";
        echo "border-bottom:" . ceil($arf_submit_btn_font_size / 8) . "px solid transparent;";
    echo "}";
}

if( preg_match( '/border/',$arfsubmitbuttonstyle ) ) {
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_border{";
        echo "background:transparent" . ( !empty( $submit_bg_img ) ? " url({$submit_bg_img}) " : "" ) . " !important;";
        if( !empty( $submit_bg_img ) ){
            echo "color:transparent;";
        } else {
            echo "color:{$submit_bg_color};";
        }
        echo "border:".( ( $submit_border_width > 0 ) ? $submit_border_width : '2px' )." solid ".$submit_bg_color.";";
    echo "}";

    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_border.arf_active_loader .arfsubmitloader{";
        echo "width:{$arf_submit_btn_font_size}px;";
        echo "height:{$arf_submit_btn_font_size}px;";
        echo "border:" . ceil($arf_submit_btn_font_size / 8) . "px solid {$submit_text_color};";
        echo "border-bottom:" . ceil($arf_submit_btn_font_size / 8) . "px solid transparent;";
    echo "}";
} 

if( preg_match('/reverse border/',$arfsubmitbuttonstyle ) ) {
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_reverse_border{";
        echo "background:{$submit_bg_color}" . ( !empty( $submit_bg_img ) ? " url({$submit_bg_img}) " : "" ) . ";";
        if( !empty( $submit_bg_img ) ){
            echo "color:transparent;";
        } else {
            echo "color:{$submit_text_color};";
        }
        echo "border:".( ( $submit_border_width > 0 ) ? $submit_border_width : '2px' )." solid ".$submit_bg_color.";";
    echo "}";

    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_reverse_border.arf_active_loader .arfsubmitloader{";
        echo "width:{$arf_submit_btn_font_size}px;";
        echo "height:{$arf_submit_btn_font_size}px;";
        echo "border:" . ceil($arf_submit_btn_font_size / 8) . "px solid {$submit_bg_color};";
        echo "border-bottom:" . ceil($arf_submit_btn_font_size / 8) . "px solid transparent;";
    echo "}";
}

if( preg_match( '/flat/', $arfsubmitbuttonstyle ) ){
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_flat.arf_active_loader,";
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_flat.arf_complete_loader,";
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_flat:hover{";
        if( !empty( $submit_hover_bg_img ) ){
            echo "background-image:url({$submit_hover_bg_img});";
            echo "color:transparent;";
        } else {
            echo "color:{$submit_text_color};";
        }
        echo "background-color:{$submit_bg_color_hover};";
    echo "}";
}

if( preg_match( '/border/', $arfsubmitbuttonstyle ) ){
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_border.arf_active_loader,";
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_border.arf_complete_loader,";
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_border:hover{";
        if( !empty( $submit_hover_bg_img ) ){
            echo "background-image:url({$submit_hover_bg_img});";
        } else {
            echo "background:{$submit_bg_color} !important;";
            echo "border:".( ( $submit_border_width > 0 ) ? $submit_border_width : '2px' )." solid ".$submit_bg_color.";";
            echo "color:{$submit_text_color};";
        }
    echo "}";
}

if( preg_match( '/reverse border/', $arfsubmitbuttonstyle ) ){
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_reverse_border.arf_active_loader,";
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_reverse_border.arf_complete_loader,";
    echo $arf_form_cls_prefix . " .arfsubmitbutton .arf_submit_btn.arf_submit_btn_reverse_border:hover{";
        if( !empty( $submit_hover_bg_img ) ){
            echo "background-image:url({$submit_hover_bg_img});";
        } else {
            echo "background:transparent !important;";
            echo "color:{$submit_bg_color};";
            echo "border:".( ( $submit_border_width > 0 ) ? $submit_border_width : '2px' )." solid ".$submit_bg_color.";";
        }
    echo "}";
}

echo $arf_form_cls_prefix . " .arf_submit_btn.arf_submit_after_confirm.arf_active_loader,";
echo $arf_form_cls_prefix . " .arf_submit_btn.arf_submit_after_confirm.arf_complete_loader{";
    echo "top:-6px;";
echo "}";

echo $arf_form_cls_prefix . " .arf_submit_btn.arf_complete_loader .arfsubmitloader{";
    echo "height:{$arf_submit_btn_font_size}px;";
    echo "width:".($arf_submit_btn_font_size/2)."px;";
    if( preg_match( '/flat/', $arfsubmitbuttonstyle ) || preg_match( '/border/', $arfsubmitbuttonstyle ) ){
        echo "border-right:" . ceil($arf_submit_btn_font_size / 8) . "px solid {$submit_text_color};";
        echo "border-top:" . ceil($arf_submit_btn_font_size / 8) . "px solid {$submit_text_color};";
    }
    if( preg_match( '/reverse border/', $arfsubmitbuttonstyle ) ){
        echo "border-right:" . ceil($arf_submit_btn_font_size / 8) . "px solid {$submit_bg_color};";
        echo "border-top:" . ceil($arf_submit_btn_font_size / 8) . "px solid {$submit_bg_color};";
    }
    echo "animation-name:arf_loader_checkmark;";
    echo "animation-duration:0.5s;";
    echo "animation-timing-function:linear;";
    echo "animation-fill-mode:initial;";
    echo "animation-iteration-count:1;";
    echo "-webkit-animation-name:arf_loader_checkmark;";
    echo "-webkit-animation-duration:0.5s;";
    echo "-webkit-animation-timing-function:linear;";
    echo "-webkit-animation-fill-mode:initial;";
    echo "-webkit-animation-iteration-count:1;";
    echo "-o-animation-name:arf_loader_checkmark;";
    echo "-o-animation-duration:0.5s;";
    echo "-o-animation-timing-function:linear;";
    echo "-o-animation-fill-mode:initial;";
    echo "-o-animation-iteration-count:1;";
    echo "-moz-animation-name:arf_loader_checkmark;";
    echo "-moz-animation-duration:0.5s;";
    echo "-moz-animation-timing-function:linear;";
    echo "-moz-animation-fill-mode:initial;";
    echo "-moz-animation-iteration-count:1;";
    echo "transform:scaleX(-1) rotate(140deg);";
    echo "-webkit-transform:scaleX(-1) rotate(140deg);";
    echo "-o-transform:scaleX(-1) rotate(140deg);";
    echo "-moz-transform:scaleX(-1) rotate(140deg);";
echo "}";

echo "@keyframes arf_loader_checkmark{";
    echo "0% {";
        echo "height:0px;width:0px;opacity:1;";
    echo "}";
    echo "20% {";
        echo "height:0px;width:".( $arf_submit_btn_font_size / 2)."px;opacity:1;";
    echo "}";
    echo "40% {";
        echo "height:{$arf_submit_btn_font_size}px;width:".( $arf_submit_btn_font_size / 2)."px;opacity:1;";
    echo "}";
    echo "100% {";
        echo "height:{$arf_submit_btn_font_size}px;width:".( $arf_submit_btn_font_size / 2)."px;opacity:1;";
    echo "}";
echo "}";
echo "@-webkit-keyframes arf_loader_checkmark{";
    echo "0% {";
        echo "height:0px;width:0px;opacity:1;";
    echo "}";
    echo "20% {";
        echo "height:0px;width:".( $arf_submit_btn_font_size / 2)."px;opacity:1;";
    echo "}";
    echo "40% {";
        echo "height:{$arf_submit_btn_font_size}px;width:".( $arf_submit_btn_font_size / 2)."px;opacity:1;";
    echo "}";
    echo "100% {";
        echo "height:{$arf_submit_btn_font_size}px;width:".( $arf_submit_btn_font_size / 2)."px;opacity:1;";
    echo "}";
echo "}";
/** Submit Button Styling */

/** Field Level Styling */
if( !empty( $loaded_field ) ){

    echo $arf_form_cls_prefix . " .arf_material_theme_container{ position:relative; display:inline-block; width:100%; }";

    /** Field Label Styling */
    echo $arf_form_cls_prefix . " label.arf_main_label{";
        echo "text-align:{$arf_label_align};";
        echo "font-family:{$arf_label_font_family};";
        echo "font-size:{$arf_label_font_size}px;";
        echo $arf_label_font_style_str;
        echo "color:{$field_label_txt_color};";
        echo "text-transform:none;";
        echo "padding:0;";
        echo "margin:0;";
        if( 'left' == $arf_label_position ){
            echo "display:inline-block;";
            echo "float:left;";
            echo "width:{$arf_label_width}px;";
        } else if( 'right' == $arf_label_position ){
            echo "display:inline-block;";
            echo "float:right;";
            echo "width:{$arf_label_width}px;";
        } else {
            echo "width:100%;";
        }
    echo "}";

    echo $arf_form_cls_prefix . " .controls .arf_main_label.active{";
        echo "font-size:12px;";
    echo "}";

    echo $arf_form_cls_prefix . " label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label){";
        echo "right:inherit;";
        echo "left:0px;";
    echo "}";


    echo $arf_form_cls_prefix . " .arfformfield{";
        echo "margin-bottom:{$field_margin};";
    echo "}";

    echo $arf_form_cls_prefix . " .arfcheckrequiredfield{ color:{$field_label_txt_color} !important; }";

    echo $arf_form_cls_prefix . " .arfformfield .controls{ width: {$arf_input_field_width}{$arf_input_field_width_unit} }";

    if( $arf_hide_label ){
        echo $arf_form_cls_prefix . " .none_container label.arf_main_label{";
            echo "display:none;";
        echo "}";
    }
    /** Field Label Styling */

    /** Field Level Error styling */
    if($arf_error_style == 'advance'){
        echo $arf_form_cls_prefix . " .popover{ background-color:{$arferrorstylecolor}; }";
        echo $arf_form_cls_prefix . " .popover.right .arrow:after, #cs_content {$arf_form_cls_prefix} .popover.right .arrow{";
            echo "border-right-color:{$arferrorstylecolor};";
        echo "}";
        echo $arf_form_cls_prefix . " .popover.left .arrow:after, #cs_content {$arf_form_cls_prefix} .popover.left .arrow{";
            echo "border-left-color:{$arferrorstylecolor};";
        echo "}";
        echo $arf_form_cls_prefix . " .popover.top .arrow:after, #cs_content {$arf_form_cls_prefix} .popover.top .arrow{";
            echo "border-top-color:{$arferrorstylecolor};";
        echo "}";
        echo $arf_form_cls_prefix . " .popover.bottom .arrow:after, #cs_content {$arf_form_cls_prefix} .popover.bottom .arrow{";
            echo "border-bottom-color:{$arferrorstylecolor};";
        echo "}";
        echo $arf_form_cls_prefix . " .popover-content{";
            echo "color:{$arferrorstylecolorfont};";
            echo "font-family:{$arf_validation_font_family};";
            echo "font-size:{$arf_validation_font_size};";
            echo "line-height:normal;";
        echo "}";
    }else{
        echo $arf_form_cls_prefix . " .help-block{";
            echo "margin:4px 0px 0px 0px;";
            echo "padding:0;";
            echo "text-align:{$description_align};";
            echo "max-width:100%;
            width:100%;
            line-height: 20px;";
            echo "position: {$arf_standard_error_position};";
        echo "}";

        echo $arf_form_cls_prefix . " .help-block ul{ margin:0; }";

        echo $arf_form_cls_prefix . " .help-block ul li{";
            echo "color:{$arferrorstylecolor};";
            echo "font-family:{$arf_validation_font_family};";
            echo "font-size:{$arf_validation_font_size};";
        echo "}";
    }
    /** Field Level Error styling */
    
    /** Form Fields Styling */
    if( array_intersect( $loaded_field, $common_field_type_styling ) ){
        
        echo $arf_form_cls_prefix . " .arfformfield .controls input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
            echo "background:transparent;";
            echo "width:100%;";
            echo "font-family:{$arf_input_font_family};";
            echo "font-size:{$arf_input_font_size}px;";
            echo $arf_input_font_style_str;
            echo "padding:{$arf_field_inner_padding};";
            echo "direction:{$arf_input_field_direction};";
            echo "border:none;";
            echo "border-bottom:{$field_border_width} {$field_border_style} {$field_border_color};";
            echo "border-radius:{$field_border_radius};";
            echo "-webkit-border-radius:{$field_border_radius};";
            echo "-o-border-radius:{$field_border_radius};";
            echo "-moz-border-radius:{$field_border_radius};";
            echo "color:{$field_text_color};";
            echo "line-height:normal;";
            echo "outline:none;";
            echo "box-shadow:none;";
            echo "-webkit-box-shadow:none;";
            echo "-o-box-shadow:none;";
            echo "-moz-box-shadow:none;";
            echo "padding-top:8px;";
            echo "padding-bottom:8px;";
            echo "background:transparent !important;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield .controls input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):focus".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls input[type=tel]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
            echo "border-bottom:{$field_border_width} {$field_border_style} {$base_color};";
        echo "}";

        /** Placeholder - webkit browsers - chrome/edge/opera */
        echo $arf_form_cls_prefix . " .arfformfield .controls input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)::-webkit-input-placeholder".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)::-webkit-input-placeholder" : "" )."{";
            echo "color:{$field_text_color};";
            echo "opacity:{$placeholder_opacity}";
        echo "}";

        /** Placeholder - mozilla firefox older versions */
        echo $arf_form_cls_prefix . " .arfformfield .controls input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):-moz-placeholder".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):-moz-placeholder" : "" )."{";
            echo "color:{$field_text_color};";
            echo "opacity:{$placeholder_opacity}";
        echo "}";

        /** Placeholder - mozilla firefox */
        echo $arf_form_cls_prefix . " .arfformfield .controls input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)::-moz-placeholder".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)::-moz-placeholder" : "" )."{";
            echo "color:{$field_text_color};";
            echo "opacity:{$placeholder_opacity}";
        echo "}";

        /** Placeholder - microsoft internet explorer */
        echo $arf_form_cls_prefix . " .arfformfield .controls input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):-ms-input-placeholder".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):-ms-input-placeholder" : "" )."{";
            echo "color:{$field_text_color};";
            echo "opacity:{$placeholder_opacity}";
        echo "}";

        if( $is_prefix_suffix_enable ){

            $arf_prefix_padding = '';
            $arf_prefix_width = '';
            $arf_prefix_padding = '0 0px';

            if ($arf_input_font_size < 10) $arf_prefix_width = '32px';
            else if ($arf_input_font_size >= 10 && $arf_input_font_size < 12) $arf_prefix_width = '34px';
            else if ($arf_input_font_size >= 12 && $arf_input_font_size < 14) $arf_prefix_width = '36px';
            else if ($arf_input_font_size >= 14 && $arf_input_font_size < 16) $arf_prefix_width = '38px';
            else if ($arf_input_font_size >= 16 && $arf_input_font_size < 18) $arf_prefix_width = '40px';
            else if ($arf_input_font_size >= 18 && $arf_input_font_size < 20) $arf_prefix_width = '42px';
            else if ($arf_input_font_size >= 20 && $arf_input_font_size < 22) $arf_prefix_width = '44px';
            else if ($arf_input_font_size == 22) $arf_prefix_width = '46px';
            else if ($arf_input_font_size == 24) $arf_prefix_width = '51px';
            else if ($arf_input_font_size == 26) $arf_prefix_width = '53px';
            else if ($arf_input_font_size == 28) $arf_prefix_width = '55px';
            else if ($arf_input_font_size == 32) $arf_prefix_width = '60px';
            else if ($arf_input_font_size == 34) $arf_prefix_width = '62px';
            else if ($arf_input_font_size == 36) $arf_prefix_width = '64px';
            else if ($arf_input_font_size == 38) $arf_prefix_width = '67px';
            else if ($arf_input_font_size == 40) $arf_prefix_width = '70px';

            $arf_paddingleft_field = ((int)$arf_prefix_width + $field_pleft).'px';

            echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_leading_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_leading_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
                echo "padding-left:{$arf_paddingleft_field} !important;";
            echo "}";

            echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_leading_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_leading_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label" : "" )."{";
                echo "padding-left:{$arf_paddingleft_field} !important;";
            echo "}";

            echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_trailing_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_trailing_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
                echo "padding-right:{$arf_paddingleft_field} !important;";
            echo "}";

            echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_trailing_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_trailing_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label" : "" )."{";
                echo "padding-right:{$arf_paddingleft_field} !important;";
            echo "}";

            echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_both_icons input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_both_icons input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
                echo "padding-left:{$arf_paddingleft_field} !important;";
                echo "padding-right:{$arf_paddingleft_field} !important;";
            echo "}";

            echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_both_icons input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_both_icons input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label" : "" )."{";
                echo "padding-left:{$arf_paddingleft_field} !important;";
                echo "padding-right:{$arf_paddingleft_field} !important;";
            echo "}";

            if( in_array( 'phone', $loaded_field ) ){
                if( $is_form_save ){
                    echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_phone_with_flag .iti + input + input + .arf_material_standard label.arf_main_label,";    
                    echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_phone_with_flag .iti + input + .arf_material_standard label.arf_main_label,";    
                }
                echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_phone_with_flag .iti + .arf_material_standard label.arf_main_label{";
                    echo "padding-left:{$arf_paddingleft_field} !important;";
                echo "}";
            }

            if( $is_form_save ){
                echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_leading_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + label.arf_main_label".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_leading_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )." + label.arf_main_label{";
                    echo "left:{$arf_paddingleft_field};";
                echo "}";

                echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_trailing_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + label.arf_main_label".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_trailing_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )." + label.arf_main_label{";
                    echo "right:{$arf_paddingleft_field};";
                echo "}";

                echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_both_icons input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + label.arf_main_label".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_both_icons input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )." + label.arf_main_label{";
                    echo "left:{$arf_paddingleft_field};";
                    echo "right:{$arf_paddingleft_field};";
                echo "}";
            }

            echo "{$arf_form_cls_prefix} .arfformfield .arf_leading_icon{
                position:absolute;
                top: 50%;
                left: 10px;
                transform:translateY(-50%);
                -webkit-transform:translateY(-50%);
                -o-transform:translateY(-50%);
                -moz-transform:translateY(-50%);
                font-size:{$arf_input_font_size}px;
                height:{$arf_input_font_size}px;
                width:{$arf_input_font_size}px;
                line-height: normal;
                color:{$prefix_suffix_icon_color};
            }
            
            {$arf_form_cls_prefix} .arfformfield .arf_trailing_icon{
                position: absolute;
                top: 50%;
                right: 10px;
                height:{$arf_input_font_size}px;
                width:{$arf_input_font_size}px;
                line-height: unset;
                transform:translateY(-50%);
                -webkit-transform:translateY(-50%);
                -o-transform:translateY(-50%);
                -moz-transform:translateY(-50%);
                font-size:{$arf_input_font_size}px;
                color:{$prefix_suffix_icon_color};
            }";
        }
    }

    if( in_array( 'checkbox', $loaded_field ) ){
        echo $arf_form_cls_prefix . " .arf_checkbox_style{";
            echo "position: relative;";
            echo "min-height: 20px;";
            echo "max-width: 100%;";
            echo "display: inline-flex;";
            $final_width_calc = ($arf_label_font_size + 12) < 30 ? 30 : ($arf_label_font_size + 12);
            $checkbox_spacing = $arf_input_font_size;
            echo "padding-left:{$final_width_calc}px;";
            if( $checkbox_spacing >= 38 ){
                echo 'margin:0 4% 25px 0;';
            }else if( $checkbox_spacing >= 36 ){
                echo 'margin:0 3.5% 22px 0;';
            }else if( $checkbox_spacing >= 32 ){
                echo 'margin:0 3% 20px 0;';
            }else if( $checkbox_spacing >= 30 ){
                echo 'margin:0 2.5% 15px 0;';
            }else if( $checkbox_spacing >= 26){
                echo 'margin:0 2% 15px 0;';
            }else if( $checkbox_spacing >= 22){
                echo 'margin:0 2% 10px 0;';
            }else{        
                echo 'margin:0 2% 10px 0;';
            }
        echo "}";

        echo $arf_form_cls_prefix . " .arf_checkbox_style .arf_checkbox_input_wrapper{";
            echo "position: absolute;";
            echo "left: 0px;";
            echo "width:20px;";
            echo "height: 20px;";
            echo "margin-right: 10px;";
            echo "vertical-align:middle;";
            echo "display:inline-flex;";
            echo "align-self:center;";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"]{";
            echo "position:absolute;";
            echo "left: 0;";
            echo "top: 0;";
            echo "width: 20px;";
            echo "height: 20px;";
            echo "opacity:0;";
            echo "z-index:2;";
            echo "cursor:pointer;";
            echo "margin:0;";
        echo "}";
        
        echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span::after{";
            echo "position: absolute;";
            echo "content: '';";
            echo "border:2px solid {$field_border_color};";
            echo "width: 20px;";
            echo "height: 20px;";
            echo "box-sizing: border-box;";
            echo "-webkit-box-sizing: border-box;";
            echo "-o-box-sizing: border-box;";
            echo "-moz-box-sizing: border-box;";
        echo "}";

        if( $arf_label_font_size > 20 ){
            $final_width_calc = ($arf_label_font_size + 12) < 30 ? 30 : ($arf_label_font_size + 12);
            echo $arf_form_cls_prefix . " .arf_checkbox_style .arf_checkbox_input_wrapper,";
            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"],";
            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span::after{";
                echo "width:{$arf_label_font_size}px;";
                echo "height:{$arf_label_font_size}px;";
            echo "}";
        }

        /** Default material checkbox ( Material 1 ) */
        echo $arf_form_cls_prefix . " .setting_checkbox.arf_defaulat_material .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span::after{";
            echo "transition: border .25s, background-color .25s, width .20s .1s, height .20s .1s, top .20s .1s, left .20s .1s;";
            echo "-webkit-transition: border .25s, background-color .25s, width .20s .1s, height .20s .1s, top .20s .1s, left .20s .1s;";
            echo "-o-transition: border .25s, background-color .25s, width .20s .1s, height .20s .1s, top .20s .1s, left .20s .1s;";
            echo "-moz-transition: border .25s, background-color .25s, width .20s .1s, height .20s .1s, top .20s .1s, left .20s .1s;";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_checkbox.arf_default_material .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span::before{";
            echo "content: '';";
            echo "width:0;";
            echo "height: 0;";
            echo "position: absolute;";
            echo "left:50%;";
            echo "top: 65%;";
            echo "border:3px solid transparent;";
            echo "box-sizing: border-box;";
            echo "-webkit-box-sizing: border-box;";
            echo "-o-box-sizing: border-box;";
            echo "-moz-box-sizing: border-box;";
            echo "transition: border .25s, background-color .25s, width .20s .1s, height .20s .1s, top .20s .1s, left .20s .1s;";
            echo "-webkit-transition: border .25s, background-color .25s, width .20s .1s, height .20s .1s, top .20s .1s, left .20s .1s;";
            echo "-o-transition: border .25s, background-color .25s, width .20s .1s, height .20s .1s, top .20s .1s, left .20s .1s;";
            echo "-moz-transition: border .25s, background-color .25s, width .20s .1s, height .20s .1s, top .20s .1s, left .20s .1s;";
            echo "transform: rotateZ(40deg) translate(-50%, -50%);";
            echo "-webkit-transform: rotateZ(40deg) translate(-50%, -50%);";
            echo "-o-transform: rotateZ(40deg) translate(-50%, -50%);";
            echo "-moz-transform: rotateZ(40deg) translate(-50%, -50%);";
            echo "transform-origin: 45% -10%;";
            echo "-webkit-transform-origin: 45% -10%;";
            echo "-o-transform-origin: 45% -10%;";
            echo "-moz-transform-origin: 45% -10%;";
            echo "z-index:1;";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_checkbox.arf_default_material .arf_checkbox_input_wrapper input[type=\"checkbox\"]:checked + span::after{";
            echo "background:{$base_color};";
            echo "border-color:{$base_color};";
        echo "}";
        
        echo $arf_form_cls_prefix . " .setting_checkbox.arf_default_material .arf_checkbox_input_wrapper input[type=\"checkbox\"]:checked + span::before{";
            echo "top: 50%;";
            echo "left: 50%;";
            echo "width: 30%;";
            echo "height: 50%;";
            echo "border-top: 2px solid transparent;";
            echo "border-left: 2px solid transparent;";
            echo "border-right: 2px solid #fff;";
            echo "border-bottom: 2px solid #fff;";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style label{";
            echo "font-size:{$arf_label_font_size}px;";
            echo "color:{$field_label_txt_color};";
            echo "font-family:{$arf_input_font_family};";
            echo $arf_input_font_style_str;
            echo "vertical-align: top;";
            echo "word-wrap: break-word;";
            echo "width: auto;";
            echo "margin:unset;";
            echo "padding:0;";
            echo "position:relative;";
            echo "max-width:100%;";
            echo "top:0;";
            echo "display:inline-flex;";
            echo "word-break:break-all;";
            echo "line-height: 1.1em;";
            echo "align-self:center;";
            echo "cursor:pointer;";
            echo "width:auto;";
        echo "}";

        /** Advanced Material Design ( Material 2 ) */
        echo $arf_form_cls_prefix . " .setting_checkbox.arf_advanced_material .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span::after{";
            echo "content:'';";
            echo "transition: .25s;";
            echo "-webkit-transition: .25s;";
            echo "-o-transition: .25s;";
            echo "-moz-transition: .25s;";
            echo "top:0;";
            echo "left:0;";
            echo "z-index:0;";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_checkbox.arf_advanced_material .arf_checkbox_input_wrapper input[type=\"checkbox\"]:checked + span::after{";
            echo "top: 40%;";
            echo "left: 55%;";
            echo "width: 50%;";
            echo "height: 100%;";
            echo "border-top: 2px solid transparent;";
            echo "border-left: 2px solid transparent;";
            echo "border-right: 2px solid {$base_color};";
            echo "border-bottom: 2px solid {$base_color};";
            echo "transform: rotate(40deg) translate(-50%,-50%);";
            echo "-webkit-transform: rotate(40deg) translate(-50%,-50%);";
            echo "-o-transform: rotate(40deg) translate(-50%,-50%);";
            echo "-moz-transform: rotate(40deg) translate(-50%,-50%);";
            echo "transform-origin: 50% 20%;";
            echo "-webkit-transform-origin: 50% 20%;";
            echo "-o-transform-origin: 50% 20%;";
            echo "-moz-transform-origin: 50% 20%;";
        echo "}";
        
        echo $arf_form_cls_prefix . " .setting_checkbox.arf_custom_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span:after{";
            echo "border-width:1px;";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_checkbox.arf_custom_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span i{";
            echo "position:absolute;";
            echo "top:50%;";
            echo "left:50%;";
            echo "transform:translate(-50%,-50%);";
            echo "-webkit-transform:translate(-50%,-50%);";
            echo "-o-transform:translate(-50%,-50%);";
            echo "-moz-transform:translate(-50%,-50%);";
            echo "display:none;";
            echo "color:{$base_color};";
            if( ( $arf_label_font_size - 14 ) > 16 ){
                echo "font-size:" . ( $arf_label_font_size - 14 ) . "px;";
            } else {
                echo "font-size:13px;";
            }
        echo "}";

        echo $arf_form_cls_prefix . " .setting_checkbox.arf_custom_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"]:checked + span:after{";
            echo "border-color:{$base_color};";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_checkbox.arf_custom_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"]:checked + span i{";
            echo "display:block;";
        echo "}";
        
        echo $arf_form_cls_prefix . " .arf_multiple_row .arf_checkbox_style,";
        echo $arf_form_cls_prefix . " .arf_vertical_radio .arf_checkbox_style{display:flex; width:100%}";

        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_two,";
        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_thiree,";
        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_four {";
            echo "width: 100%;";
            echo "display:flex;";
        echo "}";
        
        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_two .arf_checkbox_style{";
            echo "width: 48%;";
            echo "margin: 0 2% 10px 0;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_thiree .arf_checkbox_style{";
            echo "width: 31.33%;";
            echo "margin: 0 2% 10px 0;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_four .arf_checkbox_style{";
            echo "width: 23%;";
            echo "margin: 0 2% 10px 0;";
        echo "}";

        if( $is_checkbox_img_enable ){
            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"]{";
                echo "padding-left:0;";
            echo "}";
            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] div{";
                echo "opacity: 0;";
            echo "}";
            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] label{";
                echo "width: auto;";
                echo "display:block;";
            echo "}";
            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] label[class*=\"arf_checkbox_label_image\"]{";
                echo "display:block;";
            echo "}";
            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] label[class*=\"arf_checkbox_label_image\"]::before{";
                echo "background-color:{$base_color};";
                echo "color:". ( ( $arsettingcontroller->isColorDark($base_color) == '1')?'#ffffff':'#1A1A1A' ). ";";
                echo "display:flex;";
                echo "align-items:center;";
                echo "justify-content:center;";
                echo "border-radius: 4px;";
                echo "position: absolute;";
                echo "right: -3px;";
                echo "width: 24px;";
                echo "height: 24px;";
                echo "text-align: center;";
                echo "line-height: 28px;";
                echo "z-index: 2;";
                echo "font-size: 12px;";
                echo "font-weight: 900;";
                echo "top:60%;";
                echo "opacity:0;";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] label[class*=\"arf_checkbox_label_image\"].far::before,";
            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] label[class*=\"arf_checkbox_label_image\"].fas::before{";
                echo "font-family:'Font Awesome 5 Free';";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] label[class*=\"arf_checkbox_label_image\"].fab::before{";
                echo "font-family:'Font Awesome 5 Brands';";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] .img_stroke,";
            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] .rect-cutoff{";
                echo "display:none;";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] span.arf_checkbox_label{";
                echo "display:inline-block;";
                echo "margin-top:7px;";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] label[class*=\"arf_checkbox_label_image\"].checked::before{";
                echo "top:-8px;";
                echo "opacity:1;";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] label[class*=\"arf_checkbox_label_image\"].checked .img_stroke,";
            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] label[class*=\"arf_checkbox_label_image\"].checked .rect-cutoff{";
                echo "display:block;";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_checkbox .arf_checkbox_style[class*=\"arf_enable_checkbox_i\"] label[class*=\"arf_checkbox_label_image\"].checked .img_stroke{";
                echo "stroke-width:5px;";
                echo "stroke:{$base_color};";
            echo "}";

            if( $preview ){
                $all_checkbox_fields = $checkbox_img_field_arr;
            } else {
                $all_checkbox_fields = $arfform->arf_select_db_data( true, '', $MdlDb->fields, 'id,field_options', 'WHERE type = %s AND form_id = %s', array( 'checkbox', $form_id ) );
            }
            

            if( !empty( $all_checkbox_fields ) ){
                foreach( $all_checkbox_fields as $field ){
                    if( is_array( $field->field_options ) ){
                        $field->field_options = json_encode( $field->field_options );
                    }
                    $fopts = arf_json_decode( $field->field_options );
                    if( !isset( $fopts->image_width ) || $fopts->image_width == '' ){
                        $fopts->image_width = 120;
                    }
                    
                    echo ":root{--arf_field_{$field->id} : ".$fopts->image_width."px; }";
                    echo ".arf_field_{$field->id} .rect-cutoff{ transform: translateX( calc( var(--arf_field_{$field->id}) - 25px ) ) translateY(-6.5px); }";
                }
            }
        }
    }

    if( in_array( 'radio', $loaded_field ) ){

        echo $arf_form_cls_prefix . " .setting_radio .arf_radio_input_wrapper + label{";
            echo "font-size:{$arf_label_font_size}px;";
            echo "color:{$field_label_txt_color};";
            echo "font-family:{$arf_input_font_family};";
            echo $arf_input_font_style_str;
            echo "vertical-align: top;";
            echo "word-wrap: break-word;";
            echo "width: auto;";
            echo "margin:unset;";
            echo "padding:0;";
            echo "position:relative;";
            echo "max-width:100%;";
            echo "top:0;";
            echo "display:inline-flex;";
            echo "word-break:break-all;";
            echo "line-height: 1.1em;";
            echo "align-self:center;";
            echo "cursor:pointer;";
            echo "width:auto;";
        echo "}";

        if( $is_radio_img_enable ){
            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"]{";
                echo "padding-left:0;";
            echo "}";
            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] div{";
                echo "opacity: 0;";
            echo "}";
            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] label{";
                echo "width: auto;";
                echo "display:block;";
            echo "}";
            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] label[class*=\"arf_radio_label_image\"]{";
                echo "display:block;";
                echo "cursor:pointer;";
            echo "}";
            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] label[class*=\"arf_radio_label_image\"]::before{";
                echo "background-color:{$base_color};";
                echo "color:". ( ( $arsettingcontroller->isColorDark($base_color) == '1')?'#ffffff':'#1A1A1A' ). ";";
                echo "display:flex;";
                echo "align-items:center;";
                echo "justify-content:center;";
                echo "border-radius: 4px;";
                echo "position: absolute;";
                echo "right: -3px;";
                echo "width: 24px;";
                echo "height: 24px;";
                echo "text-align: center;";
                echo "line-height: 28px;";
                echo "z-index: 2;";
                echo "font-size: 12px;";
                echo "font-weight: 900;";
                echo "top:60%;";
                echo "opacity:0;";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] label[class*=\"arf_radio_label_image\"].far::before,";
            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] label[class*=\"arf_radio_label_image\"].fas::before{";
                echo "font-family:'Font Awesome 5 Free';";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] label[class*=\"arf_radio_label_image\"].fab::before{";
                echo "font-family:'Font Awesome 5 Brands';";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] .img_stroke,";
            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] .rect-cutoff{";
                echo "display:none;";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] span.arf_radio_label{";
                echo "display:inline-block;";
                echo "margin-top:7px;";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] label[class*=\"arf_radio_label_image\"].checked::before{";
                echo "top:-8px;";
                echo "opacity:1;";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] label[class*=\"arf_radio_label_image\"].checked .img_stroke,";
            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] label[class*=\"arf_radio_label_image\"].checked .rect-cutoff{";
                echo "display:block;";
            echo "}";

            echo $arf_form_cls_prefix . " .setting_radio .arf_radiobutton[class*=\"arf_enable_radio_i\"] label[class*=\"arf_radio_label_image\"].checked .img_stroke{";
                echo "stroke-width:5px;";
                echo "stroke:{$base_color};";
            echo "}";

            if( $preview ){
                $all_radio_fields = $radio_img_field_arr;
            } else {
                $all_radio_fields = $arfform->arf_select_db_data( true, '', $MdlDb->fields, 'id,field_options', 'WHERE type = %s AND form_id = %s', array( 'radio', $form_id ) );
            }
            

            if( !empty( $all_radio_fields ) ){
                foreach( $all_radio_fields as $field ){
                    if( is_array( $field->field_options ) ){
                        $field->field_options = json_encode( $field->field_options );
                    }
                    $fopts = arf_json_decode( $field->field_options );
                    if( !isset( $fopts->image_width ) || $fopts->image_width == '' ){
                        $fopts->image_width = 120;
                    }
                    
                    echo ":root{--arf_field_{$field->id} : ".$fopts->image_width."px; }";
                    echo ".arf_field_{$field->id} .rect-cutoff{ transform: translateX( calc( var(--arf_field_{$field->id}) - 25px ) ) translateY(-6.5px); }";
                }
            }
        }
    }

    if( in_array( 'radio', $loaded_field ) || in_array( 'matrix', $loaded_field ) ){
        
        echo $arf_form_cls_prefix . " .arf_radiobutton{";
            echo "position: relative;";
            echo "min-height: 20px;";
            echo "max-width: 100%;";
            echo "display: inline-flex;";
            $final_width_calc = ($arf_label_font_size + 12) < 30 ? 30 : ($arf_label_font_size + 12);
            $checkbox_spacing = $arf_input_font_size;
            echo "padding-left:{$final_width_calc}px;";
            if( $checkbox_spacing >= 38 ){
                echo 'margin:0 4% 25px 0;';
            }else if( $checkbox_spacing >= 36 ){
                echo 'margin:0 3.5% 22px 0;';
            }else if( $checkbox_spacing >= 32 ){
                echo 'margin:0 3% 20px 0;';
            }else if( $checkbox_spacing >= 30 ){
                echo 'margin:0 2.5% 15px 0;';
            }else if( $checkbox_spacing >= 26){
                echo 'margin:0 2% 15px 0;';
            }else if( $checkbox_spacing >= 22){
                echo 'margin:0 2% 10px 0;';
            }else{        
                echo 'margin:0 2% 10px 0;';
            }
        echo "}";

        echo $arf_form_cls_prefix . " .arf_matrix_radio_input_wrapper{";
            echo "display:inline-block;";
            echo "width: 20px;";
            echo "height: 20px;";
            echo "float:none;";
            echo "position:relative;";
        echo "}";

        echo $arf_form_cls_prefix . " .arf_matrix_radio_input_wrapper input[type=\"radio\"] + span{";
            echo "position:absolute;";
            echo "left:0;";
        echo "}";

        echo $arf_form_cls_prefix . " .arf_radiobutton .arf_radio_input_wrapper{";
            echo "position: absolute;";
            echo "left: 0px;";
            echo "width:20px;";
            echo "height: 20px;";
            echo "margin-right: 10px;";
            echo "vertical-align:middle;";
            echo "display:inline-flex;";
            echo "align-self:center;";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_radio .arf_radio_input_wrapper input[type=\"radio\"]{";
            echo "position:absolute;";
            echo "left: 0;";
            echo "top: 0;";
            echo "width: 20px;";
            echo "height: 20px;";
            echo "opacity:0;";
            echo "z-index:2;";
            echo "cursor:pointer;";
            echo "margin:0;";
        echo "}";
        
        echo $arf_form_cls_prefix . " .setting_radio:not(.arf_custom_radio) .arf_radio_input_wrapper input[type=\"radio\"] + span::after,";
        echo $arf_form_cls_prefix . " .setting_radio:not(.arf_custom_radio) .arf_radio_input_wrapper input[type=\"radio\"] + span::before{";
            echo "position: absolute;";
            echo "content: '';";
            echo "border:2px solid {$field_border_color};";
            echo "width: 20px;";
            echo "height: 20px;";
            echo "border-radius:100px;";
            echo "box-sizing: border-box;";
            echo "-webkit-box-sizing: border-box;";
            echo "-o-box-sizing: border-box;";
            echo "-moz-box-sizing: border-box;";
        echo "}";

        if( $arf_label_font_size > 20 ){
            $final_width_calc = ($arf_label_font_size + 12) < 30 ? 30 : ($arf_label_font_size + 12);
            echo $arf_form_cls_prefix . " .arf_radiobutton .arf_radio_input_wrapper,";
            echo $arf_form_cls_prefix . " .setting_radio .arf_radio_input_wrapper input[type=\"radio\"],";
            echo $arf_form_cls_prefix . " .setting_radio .arf_radio_input_wrapper input[type=\"radio\"] + span::before,";
            echo $arf_form_cls_prefix . " .setting_radio .arf_radio_input_wrapper input[type=\"radio\"] + span::after{";
                echo "width:{$arf_label_font_size}px;";
                echo "height:{$arf_label_font_size}px;";
            echo "}";
        }

        echo $arf_form_cls_prefix . " .setting_radio:not(.arf_custom_radio) .arf_radio_input_wrapper input[type=\"radio\"] + span::after{";
            echo "transform:scale(0);";
            echo "-webkit-transform:scale(0);";
            echo "-o-transform:scale(0);";
            echo "-moz-transform:scale(0);";
            echo "transition:.28s ease;";
            echo "-webkit-transition:.28s ease;";
            echo "-o-transition:.28s ease;";
            echo "-moz-transition:.28s ease;";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_radio:not(.arf_custom_radio) .arf_radio_input_wrapper input[type=\"radio\"]:checked + span::after{";
            echo "transform:scale(1);";
            echo "-webkit-transform:scale(1);";
            echo "-o-transform:scale(1);";
            echo "-moz-transform:scale(1);";
            echo "background:{$base_color};";
            echo "border:2px solid {$base_color};";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_radio.arf_advanced_material .arf_radio_input_wrapper input[type=\"radio\"]:checked + span::before{";
            echo "border:2px solid {$base_color};";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_radio.arf_advanced_material .arf_radio_input_wrapper input[type=\"radio\"]:checked + span::after{";
            echo "transform:scale(0.5);";
            echo "-webkit-transform:scale(0.5);";
            echo "-o-transform:scale(0.5);";
            echo "-moz-transform:scale(0.5);";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_radio.arf_custom_radio .arf_radio_input_wrapper input[type=\"radio\"] + span:after{";
            echo "border-width:1px;";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_radio.arf_custom_radio .arf_radio_input_wrapper input[type=\"radio\"] + span i{";
            echo "position:absolute;";
            echo "top:45%;";
            echo "left:47%;";
            echo "transform:translate(-50%,-50%);";
            echo "-webkit-transform:translate(-50%,-50%);";
            echo "-o-transform:translate(-50%,-50%);";
            echo "-moz-transform:translate(-50%,-50%);";
            echo "display:none;";
            echo "color:{$base_color};";
            if( ( $arf_label_font_size - 14 ) > 16 ){
                echo "font-size:" . ( $arf_label_font_size - 14 ) . "px;";
            } else {
                echo "font-size:11px;";
            }
        echo "}";

        echo $arf_form_cls_prefix . " .setting_radio.arf_custom_radio .arf_radio_input_wrapper input[type=\"radio\"]:checked + span{";
            echo "border-color:{$base_color};";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_radio.arf_custom_radio .arf_radio_input_wrapper input[type=\"radio\"] + span{";
            echo "display:inline-block;";
            echo "width: 100%;";
            echo "height: 100%;";
            echo "position:relative;";
            echo "border:2px solid {$field_border_color};";
            echo "border-radius:100px;";
            echo "box-sizing: border-box;";
            echo "-webkit-box-sizing: border-box;";
            echo "-o-box-sizing: border-box;";
            echo "-moz-box-sizing: border-box;";
        echo "}";

        echo $arf_form_cls_prefix . " .setting_radio.arf_custom_radio .arf_radio_input_wrapper input[type=\"radio\"]:checked + span i{";
            echo "display:block;";
        echo "}";
        
        echo $arf_form_cls_prefix . " .arf_multiple_row .arf_radiobutton,";
        echo $arf_form_cls_prefix . " .arf_vertical_radio .arf_radiobutton{display:flex; width:100%}";

        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_two,";
        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_thiree,";
        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_four {";
            echo "width: 100%;";
            echo "display:flex;";
        echo "}";
        
        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_two .arf_radiobutton{";
            echo "width: 48%;";
            echo "margin: 0 2% 10px 0;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_thiree .arf_radiobutton{";
            echo "width: 31.33%;";
            echo "margin: 0 2% 10px 0;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield.arf_horizontal_radio .arf_chk_radio_col_four .arf_radiobutton{";
            echo "width: 23%;";
            echo "margin: 0 2% 10px 0;";
        echo "}";
    }

    if( in_array('select', $loaded_field ) || in_array( 'arf_multiselect', $loaded_field ) ){
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt{
            border:none;
            border-bottom: {$field_border_width} {$field_border_style} {$field_border_color};";
            echo "background:transparent;";
            echo "background-image:none;
            box-shadow:none;
            -webkit-box-shadow:none;
            -o-box-shadow:none;
            -moz-box-shadow:none;
            outline:0 !important;
            -moz-border-radius:{$field_border_radius};
            -webkit-border-radius:{$field_border_radius};
            -o-border-radius:{$field_border_radius};
            border-radius:{$field_border_radius};
            padding: {$arf_field_inner_padding} !important;
            line-height: normal;
            width:100%;
            margin-top:0px;
        }";

        echo "{$arf_form_cls_prefix} .arfformfield.arfcurrent_field_active .controls{";
            echo "z-index:2 !important;";
        echo "}";
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul{
            -moz-border-radius:{$field_border_radius};
            -webkit-border-radius:{$field_border_radius};
            -o-border-radius:{$field_border_radius};
            border-radius:{$field_border_radius};
        }";
        
        if( 'rtl' == $arf_input_field_direction ){
            echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt i{";
                echo "right:unset;";
                echo "left:8px;";
            echo "}";

            echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span{";
                echo "text-align:right;";
                echo "float:right;";
                echo "right:0;";
                echo "left:unset !important;";
            echo "}";

            echo "{$arf_form_cls_prefix} .arf-selectpicker-control.multi-select.arf_form_field_picker dd ul li.arm_sel_opt_checked::before{";
                echo "right:unset;";
                echo "left: 10px;";
                echo "transform: rotate(45deg) translateX(25%);";
            echo "}";
            echo "{$arf_form_cls_prefix} .arf-selectpicker-control.multi-select.arf_form_field_picker dd ul li.arm_sel_opt_checked::after{";
                echo "right:unset;";
                echo "left: 23px;";
                echo "transform: rotate(-45deg) translateX(30%);";
            echo "}";
            echo "{$arf_form_cls_prefix} {$arf_multiselect_picker_cls} .arf-selectpicker-control.arf_form_field_picker dd ul li,";
            echo "{$arf_form_cls_prefix} {$arf_select_picker_cls} .arf-selectpicker-control.arf_form_field_picker dd ul li{";
                echo "text-align:right;";
            echo "}";
        }
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dt span{
            font-size:{$arf_input_font_size}px;
            color:{$field_text_color} !important; 
            font-family:{$arf_input_font_family};
            {$arf_input_font_style_str}
            position:absolute;
            left:0;
            top:50%;
            transform:translateY(-50%);
            -webkit-transform:translateY(-50%);
            -o-transform:translateY(-50%);
            -moz-transform:translateY(-50%);
        }";
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dt{
            border:none;
            border-bottom:  {$field_border_width} {$field_border_style} {$base_color};
            background:transparent;
            background-image:none;
            box-shadow:none;
            -webkit-box-shadow:none;
            -o-box-shadow:none;
            -moz-box-shadow:none;
            outline:0 !important;
            width:100%;
            margin-top:0px;
        }";
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open:not(.open_from_top) dt{
            border-bottom-left-radius:0px !important;
            border-bottom-right-radius:0px !important;
        }";
        
        echo "{$arf_form_cls_prefix} .arf_rounded_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open:not(.open_from_top) dt{
            border-top-left-radius:20px !important;
            border-top-right-radius:20px !important;
        }";
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open.open_from_top dt{
            border-top-left-radius:0px !important;
            border-top-right-radius:0px !important;
        }";
        
        echo "{$arf_form_cls_prefix} .arf_rounded_form .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open.open_from_top dt{
            border-bottom-left-radius: 20px !important;
            border-bottom-right-radius: 20px !important;
        }";
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd{
            border:none;
        }";

        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dd{
            float:left;
            width: 100%;
            position:absolute;
            top:10px;
        }";

        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul{";
            echo "display:block;";
            echo "transform:scaleX(0) scaleY(0);";
            echo "-webkit-transform:scaleX(0) scaleY(0);";
            echo "-o-transform:scaleX(0) scaleY(0);";
            echo "-moz-transform:scaleX(0) scaleY(0);";
            echo "transition:all .25s;";
            echo "-webkit-transition:all .25s;";
            echo "-o-transition:all .25s;";
            echo "-moz-transition:all .25s;";
            echo "transform-origin: 0% 0%;";
            echo "opacity:0;";
            echo "-webkit-transform-origin: 0% 0%;";
            echo "-o-transform-origin: 0% 0%;";
            echo "-moz-transform-origin: 0% 0%;";
        echo "}";

        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open dd ul{
            border: {$field_border_width} {$field_border_style} {$base_color};
            background-color: {$field_bg_color};
            box-shadow:none;
            -webkit-box-shadow:none;
            -o-box-shadow:none;
            -moz-box-shadow:none;
            margin:0;
            top:0;
            width:100%;";
            echo "opacity:1;";
            echo "transform:scaleX(1) scaleY(1);";
            echo "-webkit-transform:scaleX(1) scaleY(1);";
            echo "-o-transform:scaleX(1) scaleY(1);";
            echo "-moz-transform:scaleX(1) scaleY(1);";
        echo "}";
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open:not(.open_from_top) dd ul{
            border-top-left-radius:0px !important;
            border-top-right-radius:0px !important;
        }";
    
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open:not(.open_from_top) dd ul{
            border-bottom-left-radius:3px !important;
            border-bottom-right-radius:3px !important;
        }";
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker.open.open_from_top dd ul{
            border: {$field_border_width} {$field_border_style} {$field_border_color};
            border-bottom:none;
            border-bottom-left-radius:0px !important;
            border-bottom-right-radius:0px !important;
            border-top-left-radius:3px !important;
            border-top-right-radius:3px !important;
        }";
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul > li {
            font-size:{$arf_input_font_size}px;
            color:{$field_text_color};
            font-family:{$arf_input_font_family};
            {$arf_input_font_style_str}";
            echo "padding:14px 12px !important;";
            
            echo "line-height: normal;
            text-align: {$arf_input_field_text_align};
        }";
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul > li.arm_sel_opt_checked::before,
        {$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul > li.arm_sel_opt_checked::after{
            background: {$field_text_color} !important;
        }";
        
        echo "{$arf_form_cls_prefix} .arf-selectpicker-control.arf_form_field_picker dd ul li.hovered,
        {$arf_form_cls_prefix} .arf-selectpicker-control.arf_form_field_picker dd ul li:hover{
            color: #ffffff !important;    
            background-color: {$base_color} !important;
        }";
        
        echo "{$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul > li.hovered.arm_sel_opt_checked::before,
        {$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul > li.hovered.arm_sel_opt_checked::after,
        {$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul > li:hover.arm_sel_opt_checked::before,
        {$arf_form_cls_prefix} .sltstandard_front .arf-selectpicker-control.arf_form_field_picker dd ul > li:hover.arm_sel_opt_checked::after{
            background: #ffffff !important;
        }";
    }

    if( in_array( 'arf_spinner', $loaded_field ) ){
        echo "{$arf_form_cls_prefix} .arfformfield.arf_field_type_arf_spinner .arf_prefix_suffix_wrapper input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor){ text-align:center; }";

        echo "{$arf_form_cls_prefix} .arfformfield.arf_field_type_arf_spinner .arf_prefix_suffix_wrapper .arf_spin_next_button,";
        echo "{$arf_form_cls_prefix} .arfformfield.arf_field_type_arf_spinner .arf_prefix_suffix_wrapper .arf_spin_prev_button{
            cursor:pointer;
        }";
    
        $arf_prefix_padding = '';
        $arf_prefix_width = '';
        $arf_prefix_padding = '0 0px';

        if ($arf_input_font_size < 10) $arf_prefix_width = '32px';
        else if ($arf_input_font_size >= 10 && $arf_input_font_size < 12) $arf_prefix_width = '34px';
        else if ($arf_input_font_size >= 12 && $arf_input_font_size < 14) $arf_prefix_width = '36px';
        else if ($arf_input_font_size >= 14 && $arf_input_font_size < 16) $arf_prefix_width = '38px';
        else if ($arf_input_font_size >= 16 && $arf_input_font_size < 18) $arf_prefix_width = '40px';
        else if ($arf_input_font_size >= 18 && $arf_input_font_size < 20) $arf_prefix_width = '42px';
        else if ($arf_input_font_size >= 20 && $arf_input_font_size < 22) $arf_prefix_width = '44px';
        else if ($arf_input_font_size == 22) $arf_prefix_width = '46px';
        else if ($arf_input_font_size == 24) $arf_prefix_width = '51px';
        else if ($arf_input_font_size == 26) $arf_prefix_width = '53px';
        else if ($arf_input_font_size == 28) $arf_prefix_width = '55px';
        else if ($arf_input_font_size == 32) $arf_prefix_width = '60px';
        else if ($arf_input_font_size == 34) $arf_prefix_width = '62px';
        else if ($arf_input_font_size == 36) $arf_prefix_width = '64px';
        else if ($arf_input_font_size == 38) $arf_prefix_width = '67px';
        else if ($arf_input_font_size == 40) $arf_prefix_width = '70px';

        echo $arf_form_cls_prefix .  " .arfformfield {$arf_prefix_cls}{";
            echo "display:table-cell;";
            echo "width:{$arf_prefix_width};";
            echo "padding:{$arf_prefix_padding};";
            echo "vertical-align:middle;";
            echo "color:{$prefix_suffix_icon_color};";
            echo "text-align:center;";
            echo "background:transparent;";
            echo "border:none !important;";
            echo "border:0 {$field_border_style} {$field_border_color};";
            echo "border-bottom:{$field_border_width} {$field_border_style} {$field_border_color} !important;";
        echo "}";
            
        echo $arf_form_cls_prefix . " .arfformfield {$arf_suffix_cls}.arf_suffix_focus,";
        echo $arf_form_cls_prefix . " .arfformfield {$arf_prefix_cls}.arf_prefix_focus{";
            echo "border-color:{$base_color};";
            echo "transition:all 0.4s ease 0s;
            -webkit-transition:all 0.4s ease 0s;
            -moz-transition:all 0.4s ease 0s;
            -o-transition:all 0.4s ease 0s;";
            echo 'box-shadow:none;
            -moz-box-shadow:none;
            -webkit-box-shadow:none;
            -o-box-shadow:none;';
        echo "}";
                
        echo $arf_form_cls_prefix . " .arfformfield {$arf_suffix_cls} i,";
        echo $arf_form_cls_prefix . " .arfformfield {$arf_prefix_cls} i{";
            echo "font-size:{$arf_input_font_size}px;";
        echo "}";
                    
        echo $arf_form_cls_prefix .  " .arfformfield {$arf_suffix_cls}{";
            echo "display:table-cell;";
            echo "width:{$arf_prefix_width};";
            echo "padding:{$arf_prefix_padding};";
            echo "vertical-align:middle;";
            echo "color:{$prefix_suffix_icon_color};";
            echo "text-align:center;";
            echo "background:transparent;";
            echo "border:none !important;";
            echo "border:0 {$field_border_style} {$field_border_color};";
            echo "border-bottom:{$field_border_width} {$field_border_style} {$field_border_color} !important;";
        echo "}";

        echo "@media (min-width:290px) and (max-width:480px){";
            echo $arf_form_cls_prefix . " .arfformfield {$arf_suffix_cls},";
            echo $arf_form_cls_prefix . " .arfformfield {$arf_prefix_cls}{";
                echo "width:40px !important;";
                echo "padding:0 !important;";
            echo "}";
            echo $arf_form_cls_prefix . " .arfformfield {$arf_suffix_cls} i,";
            echo $arf_form_cls_prefix . " .arfformfield {$arf_prefix_cls} i{";
                echo "font-size:20px;";
            echo "}";
        echo "}";

        echo "{$arf_form_cls_prefix}  {$arf_prefix_suffix_wrapper_cls}{";
            echo "width:100%;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_prefix_only input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_prefix_only input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
            echo "border-left:none !important;";
            echo "border-top-left-radius:0px !important;";
            echo "border-bottom-left-radius:0px !important;";
            echo "width:100%;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_suffix_only input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_suffix_only input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
            echo "border-right:none !important;";
            echo "border-top-right-radius:0px !important;";
            echo "border-bottom-right-radius:0px !important;";
            echo "width:100%;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_both_pre_suffix input:not(.inplace_field):not(.arf_smiley_input):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_both_pre_suffix input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
            echo "width:100%;";
            echo "border-left:none !important;";
            echo "border-right:none !important;";
            echo "border-radius:0 !important;";
            echo "-webkit-border-radius:0 !important;";
            echo "-moz-border-radius:0 !important;";
            echo "-o-border-radius:0 !important;";
        echo "}";
    }

    if( in_array('textarea', $loaded_field ) ){
        echo $arf_form_cls_prefix . " textarea{";
            echo "width:100%;";
            echo "background:transparent;";
            echo "font-family:{$arf_input_font_family};";
            echo "font-size:{$arf_input_font_size}px;";
            echo $arf_input_font_style_str;
            echo "padding:{$arf_field_inner_padding};";
            echo "direction:{$arf_input_field_direction};";
            echo "color:{$field_text_color};";
            echo "border:none;";
            echo "border-bottom:{$field_border_width} {$field_border_style} {$field_border_color};";
            echo "border-radius:{$field_border_radius};";
            echo "-webkit-border-radius:{$field_border_radius};";
            echo "-o-border-radius:{$field_border_radius};";
            echo "-moz-border-radius:{$field_border_radius};";
            echo "line-height:normal;";
            echo "outline:none;";
            echo "box-shadow:none;";
            echo "-webkit-box-shadow:none;";
            echo "-o-box-shadow:none;";
            echo "-moz-box-shadow:none;";
            echo "max-width:100%;";
            echo "padding-top:8px;";
            echo "padding-bottom:8px;";
        echo "}";
        echo $arf_form_cls_prefix . " textarea:focus{";
            echo "border:none;";
            echo "border-bottom:{$field_border_width} {$field_border_style} {$base_color};";
            echo "background:transparent;";
        echo "}";
        echo $arf_form_cls_prefix . " .arfcount_text_char_div{";
            echo "margin:2px 0px 0px 0px;padding:0;";
            echo "font-family:{$arf_input_font_family};";
            echo "font-size:{$description_font_size}px;";
            echo "text-align:right;";
            echo "color:{$field_label_txt_color};";
            echo "max-width:100%;width:auto; line-height: 20px;";
        echo "}";
        echo $arf_form_cls_prefix . " .arf_textareachar_limit{float:left;width:95%;width:calc(100% - 50px) !important;}";
        echo $arf_form_cls_prefix . " .arf_field_type_textarea label.arf_main_label:not(.active){";
            echo "top:8px;";
            echo "transform: translateY(0);";
        echo "}";
        if( $is_form_save ){
            echo $arf_form_cls_prefix . " .edit_field_type_textarea label.arf_main_label:not(.active){";
                echo "top:8px;";
                echo "transform: translateY(0);";
            echo "}";
        }
    }

    if( in_array('date', $loaded_field ) || in_array( 'time', $loaded_field) ){
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table tbody tr{background:#FFFFFF !important;}";
        echo "{$arf_form_cls_prefix} .picker-switch td span:hover{background-color:{$base_color} !important;}";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table td.active, 
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table td.active:hover{ 
            color: {$base_color} !important; 
        }";
    
        echo ".bootstrap-datetimepicker-widget table td.old, .bootstrap-datetimepicker-widget table td.new{color: #96979a !important;}";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table td.day,{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table span.month,{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table span.year:not(.disabled),{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table span.decade:not(.disabled){
        color :{$arf_date_picker_text_color};
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table td.day:not(.active):hover {
        background-color: #F5F5F5;border-radius: 50px;-webkit-border-radius: 50px;-o-border-radius: 50px;-moz-border-radius: 50px;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table td.active:not(.disabled), 
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table td.active:not(.disabled):hover{
        background-image : url(\"data:image/svg+xml;base64,". base64_encode("<svg width='35px' xmlns='http://www.w3.org/2000/svg' height='29px'><path fill='rgb(".$arsettingcontroller->hex2rgb($base_color).")' d='M15.732,27.748c0,0-14.495,0.2-14.71-11.834c0,0,0.087-7.377,7.161-11.82 c0,0,0.733-0.993-1.294-0.259c0,0-1.855,0.431-3.538,2.2c0,0-1.078,0.216-0.388-1.381c0,0,2.416-3.019,8.585-2.76 c0,0,2.372-2.458,7.419-1.293c0,0,0.819,0.517-0.518,0.819c0,0-5.361,0.514-3.753,1.122c0,0,14.021,3.073,14.322,13.943 C29.019,16.484,29.573,27.32,15.732,27.748z M26.991,16.182C26.24,7.404,14.389,3.543,14.389,3.543 c-2.693-0.747-4.285,0.683-4.285,0.683C8.767,4.969,6.583,7.804,6.583,7.804C2.216,13.627,3.612,18.47,3.612,18.47 c2.168,7.635,12.505,7.097,12.505,7.097C27.376,25.418,26.991,16.182,26.991,16.182z'/></svg>")."\") !important;
        background-repeat:no-repeat;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table td.today:before{ border-color: {$base_color}; }
        {$arf_form_cls_prefix} .arfmainformfieldrepeater{
            margin-bottom:{$field_margin};
        }";
        echo "{$arf_form_cls_prefix} .arf_cal_month{border-bottom : {$base_color} !important;}";
    
        echo "{$arf_form_cls_prefix} .widget-area .bootstrap-datetimepicker-widget {
            left: auto !important;
            right: 0 !important;
        }";
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget {
            z-index:99999;
        }";
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget .datepicker thead {
            box-shadow: none;
            -webkit-box-shadow:none;
            -moz-box-shadow:none;
            -o-box-shadow:none;
        }";
        echo "{$arf_form_cls_prefix} .arf_cal_header th, 
        {$arf_form_cls_prefix} .arf_cal_month th {
            border: none !important;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget a[data-action],
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget a[data-action]:hover{
            box-shadow: none !important;   
            -webkit-box-shadow: none !important;
        -o-box-shadow: none !important;
        -moz-box-shadow: none !important;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget .list-unstyled {
            padding: 0px;
        }";
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget .list-unstyled li {
            list-style: none;
            padding: 0px;
            margin-bottom: 0px !important;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget .table-condensed { margin-bottom: 0 !important; }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget div,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget span,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget ul,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget li,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget tbody,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget tfoot,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget thead,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget tr,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget th,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget td {
            vertical-align: baseline;
        }";

        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget .timepicker tr{ background:inherit; }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget {
            font-family:{$arf_input_font_family};
            padding: 0px !important;
            font-size: 14px !important;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table th {
            border: 0px none rgba(0,0,0,0) !important;
            letter-spacing: 0px;
            background: none;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table td {
            border: 0px none !important;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget .list-unstyled {
            padding: 0px;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget .list-unstyled li {
            list-style: none;
            padding: 0px;
            margin-bottom: 0px !important;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table tbody tr{
        border: 0px none !important;
        background: #ffffff !important;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table thead tr.arf_cal_header{
            border-bottom: 1px solid #FFFFFF !important;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget table {
            border: 0px none !important;
            border-collapse: collapse !important;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_date_main_controls .bootstrap-datetimepicker-widget table{
            overflow: hidden !important;
            border-radius:0 0 0 0 !important;
            -moz-border-radius:0 0 0 0 !important;
            -webkit-border-radius:0 0 0 0 !important;
            -o-border-radius:0 0 0 0 !important;
            -webkit-transform: translateZ(0);
            -o-transform:translateZ(0);
            background: #ffffff !important;
            border: transparent !important;    
        }";

        echo "{$arf_form_cls_prefix} .controls .arf_cal_header,";
        echo "{$arf_form_cls_prefix} .controls .arf_cal_month,";
        echo "{$arf_form_cls_prefix} .controls .arf_cal_month th,";
        echo "{$arf_form_cls_prefix} .controls .arf_cal_header th{";
            echo "background-color:transparent !important; color:#1A1A1A; font-weight:bold;";
        echo "}";

        echo "{$arf_form_cls_prefix} .controls .timepicker .arf-glyphicon-chevron-down:hover, {$arf_form_cls_prefix} .controls .timepicker .arf-glyphicon-chevron-up:hover, {$arf_form_cls_prefix} .controls .timepicker-picker .timepicker-minute:hover, {$arf_form_cls_prefix} .controls .timepicker-picker .timepicker-hour:hover, {$arf_form_cls_prefix} .controls .timepicker-hours .arf_cal_hour:hover, {$arf_form_cls_prefix} .controls .timepicker-minutes .arf_cal_minute:hover{
            background-color: #eeeeee;
            border:none;
            border-radius:50px;
            -webkit-border-radius:50px;
            -o-border-radius:50px;
            -moz-border-radius:50px;
        }";
        
        echo "{$arf_form_cls_prefix} .arf_cal_header th,
        {$arf_form_cls_prefix} .arf_cal_month th,
        {$arf_form_cls_prefix} .arf_cal_body{
            font-size: 14px;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_cal_header th,
        {$arf_form_cls_prefix} .arf_cal_month th {
            font-family: Arial, Helvetica, Verdana, sans-serif;
            text-transform: none;
            font-weight: bold;
            text-shadow: none;
        }";
    
        echo "{$arf_form_cls_prefix} .controls .picker-switch td span.arf-glyphicon-time, {$arf_form_cls_prefix} .controls .picker-switch td span.arf-glyphicon-calendar{";
            echo "background:{$base_color};";
        echo "}";

        echo "{$arf_form_cls_prefix} .controls .arf-glyphicon-time:before, {$arf_form_cls_prefix} .controls .arf-glyphicon-calendar:before{";
            echo "color:". ( ( $arsettingcontroller->isColorDark($base_color) == '1')?'#ffffff':'#1A1A1A' ). " !important;";
        echo "}";
    
        echo "{$arf_form_cls_prefix} .arf-glyphicon {
            position: relative;
            top: 0px !important;
            display: inline-block;
            font-family: 'Glyphicons Halflings';
            font-style: normal;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_time_main_controls .arfdate-dropdown-menu {
            overflow: hidden !important;
            border-radius:4px 4px 4px 4px !important;
             -moz-border-radius:4px 4px 4px 4px !important;
             -webkit-border-radius:4px 4px 4px 4px !important;
             -o-border-radius:4px 4px 4px 4px !important;
           -webkit-transform: translateZ(0);
           -o-transform:translateZ(0);
           background: #ffffff !important;
            border: transparent !important;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_date_main_controls .arfdate-dropdown-menu{
            overflow: hidden !important;
           border-radius:4px 4px 4px 4px !important;
           -moz-border-radius:4px 4px 4px 4px !important;
           -webkit-border-radius:4px 4px 4px 4px !important;
           -o-border-radius:4px 4px 4px 4px !important;
           -webkit-transform: translateZ(0);
           -o-transform:translateZ(0);
           background: #ffffff !important;
           border: transparent !important;
        }";
    
        echo "{$arf_form_cls_prefix} .arfdate-dropdown-menu {
          position: absolute;
          top: 100%;
          left: 0;
          z-index: 1000;
          display: none;
          float: left;
          min-width: 160px;
          margin: 2px 0 0;
          font-size: 14px;
          text-align: left;
          list-style: none;
          background-color: #fff;
          -webkit-background-clip: padding-box;
                  background-clip: padding-box;
          -webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
          -o-box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
          -moz-box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
                  box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
        }";
    
        echo ".arfdate-dropdown-menu ul li {
            margin-left: 0em !important;
        }";
    
        echo "{$arf_form_cls_prefix} .timepicker-picker .timepicker-hour,{$arf_form_cls_prefix} .timepicker-picker .timepicker-minute,{$arf_form_cls_prefix} .timepicker-picker .arf-glyphicon,{$arf_form_cls_prefix} .timepicker .arf_cal_minute,{$arf_form_cls_prefix} .timepicker .arf_cal_hour {color:{$arf_date_picker_text_color} !important; border:none; }";
    
        echo "{$arf_form_cls_prefix} .timepicker-picker .arf-glyphicon::before{
            color:{$base_color} !important;
        }";
    
        echo "{$arf_form_cls_prefix} .timepicker-picker .btn.btn-primary{
            background-color:{$base_color} !important; 
            border-color:{$base_color} !important;   
        }";
    
        echo "{$arf_form_cls_prefix} .timepicker .arf_cal_minute:hover,
        {$arf_form_cls_prefix} .timepicker .arf_cal_hour:hover {border-color:{$base_color} !important;  }";
    
        echo "{$arf_form_cls_prefix} .sltstandard_time .btn-group .arfbtn.dropdown-toggle{
            border: {$field_border_width} {$field_border_style} {$field_border_color} !important;
            background-color:{$field_bg_color} !important;
            background-image:none;
            box-shadow:none;
            -webkit-box-shadow:none;
            -o-box-shadow:none;
            -moz-box-shadow:none;
            outline:0 !important;
            -moz-border-radius:{$field_border_radius} !important;
            -webkit-border-radius:{$field_border_radius} !important;
            -o-border-radius:{$field_border_radius} !important;
            border-radius:{$field_border_radius};
            padding:{$arf_field_inner_padding} !important;
            line-height: normal;
            font-size:{$arf_input_font_size}px;
            color:{$field_text_color};; 
            font-family:{$arf_input_font_family};
            {$arf_input_font_style_str}
            width:100%;
            margin-top:0px;    
        }";
    
        echo "{$arf_form_cls_prefix} .sltstandard_time .btn-group.open .arfbtn.dropdown-toggle{";
            $border_radius_open = '0px';
            if(isset($field_border_radius) && !empty($field_border_radius))
            {
                $border_radius_open = str_replace('px', '', $field_border_radius);
                if($border_radius_open>19)
                {
                    if($border_radius_open>$arf_input_font_size)
                    {
                        if($arf_input_font_size>=40)
                        {
                            $border_radius_open = '36px';
                        }
                        else if($arf_input_font_size>=36)
                        {
                            $border_radius_open = '34px';
                        }
                        else if($arf_input_font_size>20)
                        {
                            $border_radius_open = $arf_input_font_size.'px';
                        }
                        else 
                        {
                            $border_radius_open = '20px';
                        }
                    }
                    else if($border_radius_open>36 && $arf_input_font_size==40)
                    {
                        $border_radius_open = '36px';
                    }
                    else if($arf_input_font_size>14)
                    {
                        $border_radius_open = $field_border_radius;
                    }
                    else {
                        $border_radius_open = '20px';
                    }
                }
                else 
                {
                    $border_radius_open = $field_border_radius;
                }
            }
            
            echo "border-radius:{$border_radius_open};
            -moz-border-radius:{$border_radius_open};
            -webkit-border-radius:{$border_radius_open};
            -o-border-radius:{$border_radius_open};
        }";
    
        echo "{$arf_form_cls_prefix} .sltstandard_time .btn-group:focus{
            border: {$field_border_width} {$field_border_style} {$field_border_color} !important;
            background-color:{$base_color};
            background-image:none;
            box-shadow:none;
            -webkit-box-shadow:none;
            -o-box-shadow:none;
            -moz-box-shadow:none;
            outline:0 !important;
            font-size:{$arf_input_font_size}px;
            color:{$field_text_color};
            font-family:{$arf_input_font_family};
            {$arf_input_font_style_str}
            width:100%;
            -moz-box-shadow:0px 0px 2px rgba(".$arsettingcontroller->hex2rgb($base_color).", 0.4);
            -webkit-box-shadow:0px 0px 2px rgba(".$arsettingcontroller->hex2rgb($base_color).", 0.4);
            -o-box-shadow:0px 0px 2px rgba(".$arsettingcontroller->hex2rgb($base_color).", 0.4);
            box-shadow:0px 0px 2px rgba(".$arsettingcontroller->hex2rgb($base_color).", 0.4);
            margin-top:0px;    
            min-height:".( ( $arf_input_font_size ) + ( 2 * (int)$field_border_width) ) . "px;
        }";
    
        echo "{$arf_form_cls_prefix} .bootstrap-datetimepicker-widget .timepicker-picker th,
        {$arf_form_cls_prefix} .bootstrap-datetimepicker-widget .timepicker-picker td{
        padding: 0;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_time_main_controls .bootstrap-datetimepicker-widget table{
            overflow: hidden !important;
            border-radius:4px 4px 4px 4px !important;
            -moz-border-radius:4px 4px 4px 4px !important;
            -webkit-border-radius:4px 4px 4px 4px !important;
            -o-border-radius:4px 4px 4px 4px !important;
            -webkit-transform: translateZ(0);
            -o-transform:translateZ(0);
            background: #ffffff !important;
            border: transparent !important;
        }";
    
        echo "{$arf_form_cls_prefix} .timepicker-picker .btn {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            -ms-touch-action: manipulation;
            touch-action: manipulation;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-image: none;
            border: 1px solid transparent;
            border-radius: 4px;
            -webkit-border-radius: 4px;
            -o-border-radius: 4px;
            -moz-border-radius: 4px;
            background: none;
            box-shadow: none;
            -webkit-box-shadow: none;
            -o-box-shadow: none;
            -moz-box-shadow: none;
        }";
    
        echo "{$arf_form_cls_prefix} .timepicker-picker table,
        {$arf_form_cls_prefix} .timepicker-hours table,
        {$arf_form_cls_prefix} .timepicker-minutes table {
            font-size: 14px;
        }";
    
        echo "{$arf_form_cls_prefix} .ardropdown-menu.bootstrap-timepicker-widget tr, 
        {$arf_form_cls_prefix} .ardropdown-menu.bootstrap-timepicker-widget td, 
        {$arf_form_cls_prefix} .ardropdown-menu.bootstrap-timepicker-widget table {
            border:none;
            vertical-align:middle;
            color:#333333;
            font-size:13px;
            box-shadow:none !important;
            -webkit-box-shadow:none !important;
            -moz-box-shadow:none !important;
            -o-box-shadow:none !important;
            background:none;
        }";
    
        echo "{$arf_form_cls_prefix} .ardropdown-menu.bootstrap-timepicker-widget {
            z-index:99999;
            max-width:160px;
        }";
    
        echo "{$arf_form_cls_prefix} .sltstandard_time .btn-group.open .arfdropdown-menu { background-color: {$field_bg_color} !important; }";
    
        echo "{$arf_form_cls_prefix} .sltstandard_time .btn-group.open .arfdropdown-menu:focus { background-color: {$field_focus_bg_color} !important; }";
    
        echo "{$arf_form_cls_prefix} .sltstandard_time .btn-group.open .arfdropdown-menu.open { 
            border-top:{$field_border_width} {$field_border_style} {$field_border_color};
        }";
        
        echo "{$arf_form_cls_prefix} .datepicker .topdateinfo{";
            echo "background:{$base_color};";
        echo "}";
        
    }

    if( in_array( 'arf_autocomplete', $loaded_field ) ){
        echo "{$arf_form_cls_prefix} .sltstandard_front .btn-group.open .arfdropdown-menu, 
            {$arf_form_cls_prefix} .controls .typeahead.arfdropdown-menu {
            border: {$field_border_width} {$field_border_style} {$field_border_color};
            box-shadow:none;
            -webkit-box-shadow:none;
            -o-box-shadow:none;
            -moz-box-shadow:none;
            border-top:none;
            margin:0;
            margin-top:-{$field_border_width};
            border-top-left-radius:0px;
            border-top-right-radius:0px;    
            width:100%;
            overflow:hidden;
            position:absolute;
            display:none;
            z-index: 1000;
            list-style: none;
            padding: 5px 0;
            margin: 2px 0 0;
            background-color: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.2);
        }";
        echo "{$arf_form_cls_prefix} ul.typeahead.arfdropdown-menu > li{
            text-align: {$arf_input_field_text_align};
        }";
        echo "{$arf_form_cls_prefix} ul.typeahead.arfdropdown-menu > li strong{
            color:inherit;
        }";
        echo "{$arf_form_cls_prefix} ul.arfdropdown-menu { overflow-x:hidden; margin:0 !important; }";
        echo "{$arf_form_cls_prefix} .arfdropdown-menu > li { margin:0 !important; }";
    
        echo "{$arf_form_cls_prefix} .sltstandard_front .btn-group .arfdropdown-menu {
            margin:0;
        }";
        echo "{$arf_form_cls_prefix} .arf_standard_form .arfdropdown-menu > li > a > .check-mark::after{
            background: {$field_text_color};
        }";
    
        echo "{$arf_form_cls_prefix} .arf_standard_form .arfdropdown-menu > li > a img {
            opacity: 1 !important;
            background: none;
            padding: 0px;
            border: 0 none;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_standard_form .arfdropdown-menu > li:hover > a > .check-mark::after{
            background: #ffffff !important;
        }";
    
        echo "{$arf_form_cls_prefix} .sltstandard_front .btn-group.dropup.open .arfdropdown-menu {
        border: {$field_border_width} {$field_border_style} {$field_border_color};
        box-shadow:none;
        -webkit-box-shadow:none;
        -o-box-shadow:none;
        -moz-box-shadow:none;
        border-bottom:none;
        margin:0;
        margin-bottom:-{$field_border_width}
        border-bottom-left-radius:0px;
        border-bottom-right-radius:0px;
        border-top-left-radius:{$field_border_radius};
        border-top-right-radius:{$field_border_radius};
        font-size:{$arf_input_font_size}px;
        color:{$field_text_color}; 
        font-family:{$arf_input_font_family};
        {$arf_input_font_style_str}
        width:100%;
        margin-top:0px;    
        min-height:".( ( $arf_input_font_size ) + ( 2 * (int)$field_border_width) ) . "px;
        }";
        echo ".arfdropdown-menu ul.arfdropdown-menu li a span.text {
        font-size:{$arf_input_font_size}px;
        }";
        echo "{$arf_form_cls_prefix} .sltstandard_front .btn-group.dropup.open .arfdropdown-menu .arfdropdown-menu.inner {
        border-top:none;
        }";
        echo "{$arf_form_cls_prefix} .control-group.arf_error .sltstandard_front .btn-group.open .arfdropdown-menu {
        border: {$field_border_width} {$field_border_style} {$field_border_color};
        border-top:none;
        }";
        echo "{$arf_form_cls_prefix} .control-group.arf_error .sltstandard_front .btn-group.dropup.open .arfdropdown-menu {
        border: {$field_border_width} {$field_border_style} {$field_border_color};
        border-bottom:none;
        }";
        echo "{$arf_form_cls_prefix} .sltstandard_front .btn-group.open ul.arfdropdown-menu,
        {$arf_form_cls_prefix} .sltstandard_front .btn-group.dropup.open ul.arfdropdown-menu { 
        border:none;
        }";
        echo "{$arf_form_cls_prefix} .sltstandard_front .btn-group.open ul.arfdropdown-menu > li,
        {$arf_form_cls_prefix} .sltstandard_front .btn-group.open ul.arfdropdown-menu > li {
        margin:0 !important;
        outline:none;
        }";
        echo "{$arf_form_cls_prefix} .control-group.arf_error .sltstandard_front .btn-group.open ul.arfdropdown-menu,
        {$arf_form_cls_prefix} .control-group.arf_error .sltstandard_front .btn-group.dropup.open ul.arfdropdown-menu {
        border:none;
        }";
        echo "{$arf_form_cls_prefix} .control-group.arf_error .sltstandard_front .btn-group.open ul.arfdropdown-menu > li,
        {$arf_form_cls_prefix} .control-group.arf_error .sltstandard_front .btn-group.open ul.arfdropdown-menu > li,
        {$arf_form_cls_prefix} .control-group.arf_error .sltstandard_front .btn-group.dropup.open ul.arfdropdown-menu > li,
        {$arf_form_cls_prefix} .control-group.arf_error .sltstandard_front .btn-group.dropup.open ul.arfdropdown-menu > li {
        margin:0 !important;
        }";

        echo "{$arf_form_cls_prefix} .arfdropdown-menu > li > a {
            font-size:{$arf_input_font_size}px;
            color:{$field_text_color}; 
            font-family:{$arf_input_font_family};
            {$arf_input_font_style_str}";
            echo "line-height: normal;
            padding:12px; 
            word-wrap: break-word;
            display:block;
            -webkit-word-wrap: break-word;
            -o-word-wrap: break-word;
            -moz-word-wrap: break-word;
            white-space: normal;
        }";
        echo "{$arf_form_cls_prefix} .sltstandard_front .btn-group.open ul.arfdropdown-menu > li{
            text-align: {$arf_input_field_text_align}
        }";
        echo "{$arf_form_cls_prefix} .arfdropdown-menu > li:hover > a,
        {$arf_form_cls_prefix} .arfdropdown-menu > li:focus > a,
        {$arf_form_cls_prefix} .arfdropdown-menu > .active > a,
        {$arf_form_cls_prefix} .arfdropdown-menu > li:hover > a > span.text {
            color: #ffffff !important;
        }";
        echo "{$arf_form_cls_prefix} .arfdropdown-menu > li:hover > a,
        {$arf_form_cls_prefix} .arfdropdown-menu > .active > a,
        {$arf_form_cls_prefix} .arfdropdown-menu > li:focus > a{
            background-color: {$base_color};
        }";
    
        echo "{$arf_form_cls_prefix} .arf_field_type_arf_autocomplete .controls ul {
            list-style-type: none !important;
            padding: 0 !important;
        }";
        echo "{$arf_form_cls_prefix} .arf_field_type_select ul.arfdropdown-menu{
            padding: 0 !important;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_field_type_arf_autocomplete li {
            list-style: none !important;
        }";
        echo "{$arf_form_cls_prefix} .arfformfield.arf_active_autocomplete .controls{";
            echo "z-index:2 !important;";
        echo "}";
    }

    if( in_array( 'matrix', $loaded_field) ){
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table tbody tr td{text-align:{$arf_form_alignment}; }";
    
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper .arf_matrix_field_body_wrapper .arf_matrix_field_body_control:nth-child(odd),
        {$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table tbody tr:nth-child(odd) td {";
            if( $arf_matrix_inherit_bg == 1 ){
                echo "background-color: transparent !important;";
            } else {
                echo "background-color :{$arf_matrix_odd_bgcolor} !important;";
            }
        echo "}";
    
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper .arf_matrix_field_body_wrapper .arf_matrix_field_body_control:nth-child(even),
        {$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table tbody tr:nth-child(even) td {";
            if( $arf_matrix_inherit_bg == 1 ){
                echo "background-color: transparent !important;";
            } else {
                echo "background-color :{$arf_matrix_even_bgcolor} !important;";
            }
        echo "}";
    
        if( $arf_matrix_inherit_bg != 1 ){
            echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper_col .arf_matrix_field_row{
                border:2px solid {$arf_matrix_odd_bgcolor};
            }";
        }
    
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table th,
        {$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table td{
            font-family:{$arf_label_font_family};
            font-size:{$arf_label_font_size}px;
            {$arf_label_font_style_str};
            color:{$field_label_txt_color};
            cursor:pointer;
            width:auto;
            padding: 10px;
            letter-spacing:normal;
            text-transform:none;
            box-shadow:none;
            border-color:transparent;";
        echo "}";
    
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper .arf_matrix_field_row_label,
        {$arf_form_cls_prefix} .arf_matrix_field_control_wrapper .arf_matrix_field_radio_control_label{
            font-family:{$arf_label_font_family};
            font-size:{$arf_label_font_size}px;
            {$arf_label_font_style_str};
            color:{$field_label_txt_color};
        }";
    
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table{
            width:  100%;
            display: table;
            margin: 0;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table thead tr th{
            text-align: center;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table tbody tr .arf_matrix_radio_control{
            text-align: center;
            width:100%;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table tr{
            border: none;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table tbody tr:nth-child(odd) td{
            background: rgba(0,0,0,.045);
        }";
    
        echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper{
            width: 100%;
            overflow-x:  auto;
        }";
    
        echo ".arf_matrix_field_control_wrapper_col{
            display: none;
        }";
    
        echo ".arf_hide_matrix_cell{
            display: none;
        }";
    
        echo "@media all and (max-width: 991px){
            {$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table{
                display: none;
            }
    
            {$arf_form_cls_prefix} .arf_matrix_field_control_wrapper{
                display: flex;
                flex-wrap: wrap;
            }
    
            {$arf_form_cls_prefix} .arf_matrix_field_control_wrapper_col{
                flex-basis: 0;
                flex-grow: 1;
                max-width: 100%;
                display: block;
            }
    
            {$arf_form_cls_prefix} .arf_matrix_field_row{
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 10px;
            }
    
            {$arf_form_cls_prefix} .arf_matrix_field_row .arf_matrix_field_row_label{
                flex-basis: 100%;
                width: 100%;
                display: flex;
                min-height: 2.5rem;
                align-items: center;
                padding: .5rem;
            }
    
            .arf_matrix_field_body_wrapper{
                width: 100%;
                flex-wrap: wrap;
                justify-content: space-around;
                display: flex;
            }
    
            .arf_matrix_field_body_control{
                flex-basis: 100%;
                max-width: 100%;
                justify-content: flex-end;
                min-height: 2.5rem;
                padding-right: 1rem;
                display: inline-flex;
                flex-grow: 1;
                position: relative;
            }
    
            .arf_matrix_field_radio_control_label{
                display: inline-flex;
                position: absolute;
                min-height: 2.5rem;
                align-items: center;
                top: 0;
                left: 1rem;
                width: calc(100% - 1rem);
                font-size: .875rem;
                line-height: 2.5rem;
                cursor: pointer;
            }
            .arf_matrix_field_body_control .arf_matrix_radio_control{
                flex-basis: 100%;
                max-width: 100%;
                justify-content: flex-end;
                min-height: 2.5rem;
                padding-right: 1rem;
                display: inline-flex;
                flex-grow: 1;
                align-items: center;
            }
        }";
    }
    /** Form Fields Styling */

    /** Field Description Styling */
    echo $arf_form_cls_prefix . " .arfformfield .arf_field_description{";
        echo "margin:2px 0px 0px 0px;padding:0;";
        echo "font-family:{$arf_input_font_family};";
        echo "font-size:{$description_font_size}px;";
        echo "text-align:{$description_align};";
        echo "color:{$field_label_txt_color};";
        echo "max-width:100%;width:auto; line-height: 20px;";
    echo "}";
    /** Field Description Styling */
}
/** Field Level Styling */

$use_saved = isset($use_saved) ? $use_saved : '';
do_action('arf_outsite_print_style', $new_values, $use_saved, $form_id);