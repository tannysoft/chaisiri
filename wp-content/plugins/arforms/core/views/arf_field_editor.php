<?php
global $arf_memory_limit, $memory_limit, $arfieldcontroller, $arffield, $maincontroller;

if (isset($arf_memory_limit) && isset($memory_limit) && ($arf_memory_limit * 1024 * 1024) > $memory_limit) {
    @ini_set("memory_limit", $arf_memory_limit . 'M');
}

global $style_settings, $armainhelper, $arfieldhelper, $arformcontroller, $arf_font_awesome_loaded, $MdlDb;

$multicol_html = '';
$arf_disply_multicolumn_field = true;

if(is_array($field))
{
    $arf_disply_multicolumn_field = apply_filters('arf_disply_multicolumn_fieldolumn_field_outside', $arf_disply_multicolumn_field, $field);
}
if ($arf_disply_multicolumn_field && (isset($field['type']) && $field['type'] != 'divider' && $field['type'] != 'arf_wysiwyg' && $field['type'] != 'section' && $field['type'] != 'break' && $field['type'] != 'hidden') || !is_array($field)) {
    $multicol_html = '<div class="arf_multiiconbox">
        <div class="arf_field_option_multicolumn" id="arf_multicolumn_wrapper">
            <input type="hidden" name="multicolumn" />
            ' . $arf_multicolumn_one = $arfieldcontroller->arf_get_field_multicolumn_icon(1, $index_arf_fields) . '
            ' . $arf_multicolumn_one = $arfieldcontroller->arf_get_field_multicolumn_icon(2, $index_arf_fields) . '
            ' . $arf_multicolumn_one = $arfieldcontroller->arf_get_field_multicolumn_icon(3, $index_arf_fields) . '
            ' . $arf_multicolumn_one = $arfieldcontroller->arf_get_field_multicolumn_icon(4, $index_arf_fields) . '
            ' . $arf_multicolumn_one = $arfieldcontroller->arf_get_field_multicolumn_icon(5, $index_arf_fields) . '
            ' . $arf_multicolumn_one = $arfieldcontroller->arf_get_field_multicolumn_icon(6, $index_arf_fields) . '
        </div>
        ' . $arfieldcontroller->arf_get_multicolumn_expand_icon() . '
    </div>';
}
$multicolclass = "single_column_wrapper";
$define_classes = "";
$confirm_field_options = '';
$arf_main_label_cls = '';
if ($frm_css['arfinputstyle'] == 'material') {
    $arf_main_label_cls = $arformcontroller->arf_label_top_position($frm_css['font_size'], $frm_css['field_font_size']);
}



if (is_array($field)) {
    $define_classes = isset($field['classes']) ? $field['classes'] : 'arf_1';
} else {
    if ( strpos($field, '_confirm') !== false ) {
        $field_ext_extract = explode('_', $field);
        $field_id_values = $arffield->getOne($field_ext_extract[0]);        
        $unsaved_fields_confirm = (isset($_REQUEST['extra_fields']) && $_REQUEST['extra_fields'] != '' ) ? json_decode(stripslashes_deep($_REQUEST['extra_fields']), true) : array();
        
        if( (isset($inside_repeatable_field) && $inside_repeatable_field) || (isset($inside_section_field) && $inside_section_field )){
            $unsaved_fields_confirm = ( isset( $_REQUEST['inner_extra_fields']) && $_REQUEST['inner_extra_fields'] != '' ) ? json_decode( stripslashes_deep($_REQUEST['inner_extra_fields']), true ) : array();
        }

        if(is_array($unsaved_fields_confirm) && count($unsaved_fields_confirm)>0 && isset($unsaved_fields_confirm[$field_ext_extract[0]])) {
            $confirm_field_options1 = $unsaved_fields_confirm[$field_ext_extract[0]];
            $confirm_field_options = json_decode($confirm_field_options1,true);
        } else if($field_id_values !='') {
            $confirm_field_options = $field_id_values->field_options;            
        } else {
            $key_val = '';
            foreach ($arf_fields as $key => $val) {
               if ($val['id'] == $field_ext_extract[0]) {
                   $key_val = $key;
               }
            }
            $confirm_field_options = array();
            if($key_val !=''){
                $confirm_field_options = $arf_fields[$key_val];
            }
        }

        $is_confirm_field = false;

        if($confirm_field_options['type'] == 'email'){
            $define_classes = isset($confirm_field_options['confirm_email_inner_classes']) ? $confirm_field_options['confirm_email_inner_classes'] : 'arf_1';
            $is_confirm_field = true;
        }
        if($confirm_field_options['type'] == 'password'){
            $define_classes = isset($confirm_field_options['confirm_password_inner_classes']) ? $confirm_field_options['confirm_password_inner_classes'] : 'arf_1';
            $is_confirm_field = true;
        }

        $last_col_array_keys = array( 'arf_1col', 'arf_2col', 'arf_3col', 'arf_4col', 'arf_5col', 'arf_6col' );

        if( !$is_confirm_field && gettype($field_key) != 'string' && isset( $field_classes[$field_key - 1] ) && in_array( $field_classes[$field_key - 1], $last_col_array_keys ) && !in_array( $field_classes[$field_key], $last_col_array_keys ) ){
            $define_classes = 'arf_1';
        }
        
    } else {
        $field_ext_extract = explode('|', $field);
        $define_classes = $field_ext_extract[0];
    }
}

switch ($define_classes) {
    case 'arf_1':
        $multicolclass = "single_column_wrapper";
        break;
    case 'arf_2':
        $multicolclass = "two_column_wrapper";
        break;
    case 'arf_3':
        $multicolclass = "three_column_wrapper";
        break;
    case 'arf_4':
        $multicolclass = "four_column_wrapper";
        break;
    case 'arf_5':
        $multicolclass = "five_column_wrapper";
        break;
    case 'arf_6':
        $multicolclass = "six_column_wrapper";
        break;
    case 'arf_1col':
        $multicolclass = "single_column_wrapper";
        break;
    case 'arf21colclass':
        $multicolclass = "two_column_wrapper";
        break;
    case 'arf31colclass':
        $multicolclass = "three_column_wrapper";
        break;
    case 'arf41colclass':
        $multicolclass = "four_column_wrapper";
        break;
    case 'arf51colclass':
        $multicolclass = "five_column_wrapper";
        break;
    case 'arf61colclass':
        $multicolclass = "six_column_wrapper";
        break;
}

$sortable_inner_field_style = "";

if (isset($field_resize_width[$arf_field_counter])) {
    $sortable_inner_field_style = "style='width: " . $field_resize_width[$arf_field_counter] . "%' data-width='" . $field_resize_width[$arf_field_counter] . "'";
}

if( ( isset( $inside_repeatable_field ) && true == $inside_repeatable_field ) || ( isset( $inside_section_field ) && true == $inside_section_field ) ){
    if( isset( $inner_field_resize_width[$parent_field_id]) ){
        $temp_field_style = '';
        foreach( $inner_field_resize_width[$parent_field_id] as $k => $inner_field_data ){
            $exploded_data = explode( '|', $inner_field_data );
            $temp_field_id = $exploded_data[0];
            $temp_field_width = $exploded_data[1];
            if( is_array( $field ) && $field['id'] == $temp_field_id ){
                $temp_field_style = $temp_field_width;
                break;
            } else if ( $field == $temp_field_id) {
                $temp_field_style = $temp_field_width;
                break;
            }
        }
        $sortable_inner_field_style = "style='width: ". $temp_field_style . "%' data-width='" . $temp_field_style . "'";
    } else if( isset( $inner_field_resize_width[$inner_field_counter] ) ){        
        $sortable_inner_field_style = "style='width: ". $inner_field_resize_width[$inner_field_counter] . "%' data-width='" . $inner_field_resize_width[$inner_field_counter] . "'";
    }
}

if (is_array($field)) {

    $display = apply_filters('arfdisplayfieldoptions', array(
        'type' => $field['type'], 'field_data' => $field, 'required' => true,
        'description' => true, 'options' => true, 'label_position' => true,
        'invalid' => false, 'size' => false, 'clear_on_focus' => false,
        'default_blank' => true, 'css' => true
    ));

    $fields_for_edit_options = apply_filters('arf_field_values_options_outside', array('checkbox', 'radio', 'select'));

    $arf_form_css = "";
    $arf_form_css = $data['form_css'];

    $arr = maybe_unserialize($arf_form_css);
    $newarr = array();
    if (isset($arr) && is_array($arr) && !empty($arr)) {
        foreach ($arr as $k => $v) {
            $newarr[$k] = $v;
        }
    }
    
    if(isset($_REQUEST['arf_rtl_switch_mode']) && $_REQUEST['arf_rtl_switch_mode']=="yes" ) {
        $newarr['arfformtitlealign'] = "right";
        $newarr['form_align'] = "right";
        $newarr['arfdescalighsetting'] = 'right';
        $newarr['align'] = "right";
        $newarr['text_direction'] = '0';
        $newarr['arfsubmitalignsetting'] = "right";
    }
    
    $newarr['arfinputstyle'] = $frm_css['arfinputstyle'] = (isset($_GET['templete_style']) && $_GET['templete_style'] !='') ? $_GET['templete_style'] : ((isset($newarr['arfinputstyle']) && $newarr['arfinputstyle'] !='') ? $newarr['arfinputstyle'] : 'material');
    if(isset($_REQUEST['arfaction']) && ($_REQUEST['arfaction'] == 'duplicate' || $_REQUEST['arfaction'] == 'new')){
        if($newarr['arfinputstyle'] != 'material' && $newarr['arfinputstyle'] != 'material_outlined'){
            if($newarr['arfinputstyle'] == 'rounded'){
                $newarr['border_radius'] = 50;
            } else {
                $newarr['border_radius'] = 4;
            }
            $newarr['arffieldinnermarginssetting_1'] = 7;
            $newarr['arffieldinnermarginssetting_2'] = 10;
            $newarr['arfcheckradiostyle'] = 'default';
            $newarr['arfsubmitborderwidthsetting'] = '0';
            $newarr['arfsubmitbuttonxoffsetsetting'] = 1 ;
            $newarr['arfsubmitbuttonyoffsetsetting'] = 2;
            $newarr['arfsubmitbuttonblursetting'] = 3;
            $newarr['arfsubmitbuttonshadowsetting'] = 0;
            $newarr['arfsubmitbuttonstyle'] = 'flat';
            $newarr['arfmainfield_opacity'] = 0;
            $newarr['arffieldinnermarginssetting'] = '7px 10px 7px 10px';
            
        } else if($newarr['arfinputstyle'] == 'material'){
            $newarr['arffieldinnermarginssetting_1'] = 0;
            $newarr['arffieldinnermarginssetting_2'] = 0;
            $newarr['border_radius'] = 0;
            $newarr['arfcheckradiostyle'] = 'material';
            $newarr['arfsubmitborderwidthsetting'] = '2';
            $newarr['arfsubmitbuttonstyle'] = 'border';
            $newarr['arfmainfield_opacity'] = 1;
            $newarr['arffieldinnermarginssetting'] = '0px 0px 0px 0px';
            $newarr['hide_labels'] = 0;
        } else if( $newarr['arfinputstyle'] == 'material_outlined' ){
            $newarr['arffieldinnermarginssetting_1'] = 16;
            $newarr['arffieldinnermarginssetting_2'] = 16;
            $newarr['arffieldinnermarginssetting_3'] = 16;
            $newarr['arffieldinnermarginssetting_4'] = 16;
            $newarr['arffieldinnermarginssetting'] = '16px 16px 16px 16px';
            $newarr['arfsubmitborderwidthsetting'] = '2';
            $newarr['arfsubmitbuttonstyle'] = 'border';
            $newarr['hide_labels'] = 0;
        }
        
    }
    
    $myliclass = "";
    if (isset($field['classes']) && $field['classes'] == "arf_2") {
        $myliclass = "width:45.5%;float:left;clear:none;height:130px;";
    } else if (isset($field['classes']) && $field['classes'] == "arf_3") {
        $myliclass = "width:29%;float:left;clear:none;height:130px;";
    } else {
        $myliclass = "float:none;clear:both;height:auto;";
    }
    global $arf_column_classes;

    if ($field['type'] == 'captcha') {
        if (isset($field['is_recaptcha']) && $field['is_recaptcha'] == 'custom-captcha')
            $multicolclass .= " arf-custom-captcha";
        else
            $multicolclass .= " arf-recaptcha";
    }

    if (isset($field['options']) && is_array($field['options']) && ( $field['type'] == 'radio' || $field['type'] == 'checkbox' || $field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG ))
        $field['options'] = $arfieldhelper->changeoptionorder($field);


    $prefix_suffix_bg_color = (isset($newarr['prefix_suffix_bg_color']) && $newarr['prefix_suffix_bg_color'] != "") ? $newarr['prefix_suffix_bg_color'] : '#e7e8ec';
    $prefix_suffix_icon_color = (isset($newarr['prefix_suffix_icon_color']) && $newarr['prefix_suffix_icon_color'] != '') ? $newarr['prefix_suffix_icon_color'] : '#808080';

    $prefix_suffix_wrapper_start = "";
    $prefix_suffix_wrapper_end = "";
    $has_prefix_suffix = false;
    $prefix_suffix_class = "";
    $has_prefix = false;
    $has_suffix = false;
    $arf_prefix_icon = "";
    $arf_suffix_icon = "";
    $prefix_suffix_style_start = "<style id='arf_field_prefix_suffix_style_{$field['id']}' type='text/css'>";
    $prefix_suffix_style = "";
    $prefix_suffix_style_end = "</style>";

    $arf_is_phone_with_flag = false;
    $default_country_code = "";
    $default_country = (isset($field['type']) == 'phone' && isset($field['default_country']) && $field['default_country'] != '' ) ? $field['default_country'] : '';
    if( $field['type'] == 'phone' && isset($field['phonetype']) && $field['phonetype'] == 1 ){
        $arf_is_phone_with_flag = true;

        
        $phtypes = array();
        foreach( $field['phtypes'] as $key => $vphtype ){
            if( $vphtype != 0 ){
                array_push($phtypes, strtolower(str_replace('phtypes_','',$key)) );
            }
        }

        $default_country_code = ' data-defaultCountryCode="'.$phtypes[0].'" ';
    }

    if (isset($field['enable_arf_prefix']) && $field['enable_arf_prefix'] == 1 && $arf_is_phone_with_flag == false ) {
        $has_prefix_suffix = true;
        $has_prefix = true;
        $arf_prefix_icon = "<span class='arf_editor_prefix_icon'><i class='{$field['arf_prefix_icon']}'></i></span>";
    }
    if (isset($field['enable_arf_suffix']) && $field['enable_arf_suffix'] == 1) {
        $has_prefix_suffix = true;
        $has_suffix = true;
        $arf_suffix_icon = "<span class='arf_editor_suffix_icon'><i class='{$field['arf_suffix_icon']}'></i></span>";
    }

    if ($has_prefix == true && $has_suffix == false) {
        $prefix_suffix_class = " arf_prefix_only ";
    } else if ($has_prefix == false && $has_suffix == true) {
        $prefix_suffix_class = " arf_suffix_only ";
    } else if ($has_prefix == true && $has_suffix == true) {
        $prefix_suffix_class = " arf_both_pre_suffix ";
    }


    if (isset($has_prefix_suffix) && $has_prefix_suffix == true) {
        $prefix_suffix_wrapper_start = "<div id='arf_editor_prefix_suffix_container_" . $field['id'] . "' class='arf_editor_prefix_suffix_wrapper " . trim($prefix_suffix_class) . "'>";
        $prefix_suffix_wrapper_end = "</div>";
    }

    if ($frm_css['arfinputstyle'] == 'material') {
        $prefix_suffix_wrapper_start = $prefix_suffix_wrapper_end = "";
    }

    if ($index_arf_fields != 0) {
        $last_index = $index_arf_fields - 1;
        if ($index_arf_fields > 2) {
            $seconud_last_index = $index_arf_fields - 2;
        } else {
            $seconud_last_index = $index_arf_fields;
        }
        if ($index_arf_fields > 3) {
            $third_last_index = $index_arf_fields - 3;
        } else {
            $third_last_index = $index_arf_fields;
        }
        if ($index_arf_fields > 4) {
            $fourth_last_index = $index_arf_fields - 4;
        } else {
            $fourth_last_index = $index_arf_fields;
        }
        if ($index_arf_fields > 5) {
            $fifth_last_index = $index_arf_fields - 5;
        } else {
            $fifth_last_index = $index_arf_fields;
        }
    } else {
        $last_index = 0;
        $seconud_last_index = 0;
        $third_last_index = 0;
        $fourth_last_index = 0;
        $fifth_last_index = 0;
    }
    $arfsliderhover = (isset($field['show_slider_tooltip']) && $field['show_slider_tooltip'] == 1) ? 'arfsliderhover' : '';

    $arf_input_style_label_position = array('checkbox','radio','scale','arf_smiley','arf_switch','html','arfslider','slider','hidden','colorpicker','imagecontrol','like','file','break','divider','section','captcha', 'arf_wysiwyg');
    $arf_input_style_label_position = apply_filters('arf_input_style_label_position_outside',$arf_input_style_label_position,$frm_css['arfinputstyle'],$field['type']);

    if ($class == 'arf_1col' || $class == 'arf21colclass' || $class == 'arf31colclass' || $class == 'arf41colclass' || $class == 'arf51colclass' || $class == 'arf61colclass') {
        ?>
        <div class="arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default arf1columns <?php echo $display['options'] ?>  <?php echo $multicolclass; ?>" style="<?php echo $field['type'] == 'imagecontrol' ? 'display:none;' : ''; ?>" data-id="arf_editor_main_row_<?php echo $index_arf_fields; ?>"><?php echo $multicol_html; ?>
            <?php
    }
    
    $arf_sortable_inner_cls = 'sortable_inner_wrapper ';

    if( 'break' == $display['type'] || 'divider' == $display['type'] || 'section' == $display['type'] || 'arf_repeater' == $display['type'] ){
        $arf_sortable_inner_cls = 'unsortable_inner_wrapper';
    }

    $arf_slider_hover_cls = '';

    if(  $display['type'] == 'arfslider' && isset( $display['field_data']['show_slider_tooltip'] ) && '1' == $display['field_data']['show_slider_tooltip']  ){
        $arf_slider_hover_cls = 'arfsliderhover';
    }

    $arf_inner_cls = 'arf_1col';

    if( !empty( $field['inner_class'] ) ){
        $arf_inner_cls = $field['inner_class'];
    }

    ?>

    <div class="<?php echo $arf_sortable_inner_cls; ?> edit_field_type_<?php echo $display['type']; ?> <?php echo $arfsliderhover; ?> <?php echo $arf_slider_hover_cls; ?>" id="arfmainfieldid_<?php echo $field['id']; ?>" inner_class="<?php echo $arf_inner_cls; ?>" <?php echo $sortable_inner_field_style; ?>>
            <div id="arf_field_<?php echo $field['id']; ?>" class="arfformfield control-group arfmainformfield   <?php echo isset($newarr['position']) ? $newarr['position'] . '_container' : 'top_container'; ?> <?php echo (isset($newarr['hide_labels']) && $newarr['hide_labels'] == 1 && ($display['type'] != 'break' && $display['type'] != 'divider' && $display['type'] != 'section' )) ? 'none_container' : ''; ?> arf_field_<?php echo $field['id']; ?> ">
            	<?php
            	if( ( $frm_css['arfinputstyle'] != 'material' && $frm_css['arfinputstyle'] != 'material_outlined' && apply_filters( 'arf_display_field_name_box',true, $field) ) || ( ($frm_css['arfinputstyle'] == 'material' || $frm_css['arfinputstyle'] == 'material_outlined' ) && in_array($field['type'],$arf_input_style_label_position)) ){
            	?>
                <div class="fieldname-row" style="display : block;" >
                    <?php
                    if (isset($arf_column_classes['three']) and $arf_column_classes['three'] == '(Third)')
                        unset($arf_column_classes['three']);
                    if (isset($arf_column_classes['two']) and $arf_column_classes['two'] == '(Second)')
                        unset($arf_column_classes['two']);
                    ?>
                    <?php do_action('arfextrafieldactions', $field['id']); ?>
                    <?php
                        $page_break_class = ( $display['type'] == 'break' ) ? 'arf_field_break' : '';
                    ?>
                    <div class="fieldname <?php echo $page_break_class; ?>">
                        <?php
                        $arf_disply_required_field = true;
                        $arf_disply_required_field = apply_filters('arf_disply_required_field_outside', $arf_disply_required_field, $field);
                        $is_required_field = false;
                        if ($display['required'] && $field['type'] != 'arfslider' && $field['type'] != 'imagecontrol' && $arf_disply_required_field) {
                            $is_required_field = true;
                        }

                        if ($display['type'] == 'break') {
                            ?><BR />
                            <div class="arf_field_break_control">
                                <span><?php echo addslashes(esc_html__('Page Break','ARForms'));?></span>
                            </div>

                        <?php } else if ($field['type'] == 'divider' || $field['type'] == 'section') { ?>
                            <label class="arf_main_label <?php echo $arf_main_label_cls; ?>" id="field_<?php echo $field['id']; ?>">
                                <span class="arfeditorfieldopt_<?php echo $field['type'] ?>_label arf_edit_in_place arfeditorfieldopt_label">
                                    <input type="text" class="arf_edit_in_place_input inplace_field" data-ajax="false" data-field-opt-change="true" data-field-opt-key='name' value="<?php echo htmlspecialchars($field['name']); ?>" data-field-id="<?php echo $field['id']; ?>" />
                                </span>
                                <?php if(  $is_required_field ){ ?>
                                    <span id="require_field_<?php echo $field['id']; ?>">
                                        <a href="javascript:void(0)" onclick="javascript:arfmakerequiredfieldfunction(<?php echo $field['id']; ?>,<?php echo $field_required = ($field['required'] == '0') ? '0' : '1'; ?>,'1')" class="arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield<?php echo $field_required ?>" id="req_field_<?php echo $field['id']; ?>" title="<?php echo addslashes(esc_html__('Click to mark as', 'ARForms') . ( $field['required'] == '0' ? ' ' : ' not ')) . addslashes(esc_html__('compulsory field.', 'ARForms')); ?>"></a>
                                    </span>
                                <?php } ?>
                            </label>
                        <?php } else { ?>
                            <label class="arf_main_label <?php echo $arf_main_label_cls; ?>" id="field_<?php echo $field['id']; ?>">
                                <span class="arfeditorfieldopt_label arf_edit_in_place">
                                    <input type="text" class="arf_edit_in_place_input inplace_field" data-ajax="false" data-field-opt-change="true" data-field-opt-key='name' value="<?php echo htmlspecialchars($field['name']); ?>" data-field-id="<?php echo $field['id']; ?>" />
                                </span>
                                <?php if(  $is_required_field ){ ?>
                                <span id="require_field_<?php echo $field['id']; ?>">
                                    <a href="javascript:void(0)" onclick="javascript:arfmakerequiredfieldfunction(<?php echo $field['id']; ?>,<?php echo $field_required = (isset($field['required']) && $field['required'] == '0') ? '0' : '1'; ?>,'1')" class="arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield<?php echo $field_required ?>" id="req_field_<?php echo $field['id']; ?>" title="<?php echo addslashes(esc_html__('Click to mark as', 'ARForms')) . ( $field['required'] == '0' ? ' ' : ' not ') . addslashes(esc_html__('compulsory field.', 'ARForms')); ?>"></a>
                                </span>
                                <?php } ?>
                            </label>

                            <?php if ($field['type'] == 'hidden') { ?>
                                <input type="hidden" name="field_options[name_<?php echo $field['id']; ?>]" id="arfname_<?php echo $field['id']; ?>" value="<?php echo esc_attr($field['name']); ?>" />
                            <?php } ?>

                        <?php }
                        ?>
                    </div>
                </div>
                <?php
                }
                $is_edit_option_icon = in_array($display['type'], $fields_for_edit_options) ? true : false;
                $is_enable_running_total = false;
                if( $field['type'] == 'html' && $field['enable_total'] == 1 ){
                    $is_enable_running_total = true;
                }
                $display_opt_icons = true;
                $display_opt_icons = apply_filters( 'arf_display_field_opt_icons', $display_opt_icons, $field );
                if( $display_opt_icons ){
                ?>
                <div class="arf_fieldiconbox <?php do_action('arf_add_fieldiconbox_class_outside', $field) ?> <?php echo ($is_edit_option_icon || $is_enable_running_total) ? 'arf_fieldiconbox_with_edit_option' : ''; ?>" data-field_id="<?php echo $field['id']; ?>">
                    <?php do_action( 'arf_set_field_icons_at_start', $field );
                    if ($field['type'] != 'hidden') {
                        if( in_array($field['type'], $fields_for_edit_options) ){
                            echo $arfieldcontroller->arf_get_field_control_icons('edit_options','',$field['id']);
                        }
                        if( $field['type'] == 'html'){
                            echo $arfieldcontroller->arf_get_field_control_icons('running_total_icon');
                        }
                        
                        ?>  
                        <?php
                        if ($field['type'] != 'html' && $field['type'] != 'divider' && $field['type'] != 'section' && $field['type'] != 'break' && $field['type'] != 'arfslider') {
                            $field_required = (isset( $field['required'] ) && $field['required'] == '0') ? '0' : '1';
                            $field_required_cls = (isset( $field['required'] ) && $field['required'] == '0') ? '' : 'arf_active';
                            $arf_disply_required_field = true;
                            $arf_disply_required_field = apply_filters('arf_disply_required_field_outside', $arf_disply_required_field, $field);
                            if ($display['required'] and $field['type'] != 'arfslider' && $field['type'] != 'imagecontrol' && $arf_disply_required_field) {
                                echo $arf_field_require_option_icon = $arfieldcontroller->arf_get_field_control_icons('require', $field_required_cls, $field['id'], $field_required);
                            }
                        }
                        ?>                                
                        <?php
                        if ($field['type'] != 'hidden') {

                        }
                        ?>
                        <?php
                    }

                    echo $arf_field_require_option_icon = $arfieldcontroller->arf_get_field_control_icons('duplicate', '', $field['id'], 0, $field['type'], $id);
                    echo $arf_field_require_option_icon = $arfieldcontroller->arf_get_field_control_icons('delete', '', $field['id'], 0, '', '');
                    if( $field['type'] != 'hidden' ){
                        echo $arfieldcontroller->arf_get_field_control_icons('options', '', $field['id'], 0, $field['type']);

                        echo $arfieldcontroller->arf_get_field_control_icons('move');
                    }
                    do_action( 'arf_set_field_icons_at_end', $field );
                    ?>
                </div>
                <?php
                }
                $arf_control_append_class = '';
                if ($field['type'] == 'checkbox') {
                    $arf_control_append_class = 'setting_checkbox';
                } else if ($field['type'] == 'radio') {
                    $arf_control_append_class = 'setting_radio';
                } else if ($field['type'] == 'select') {
                    $arf_control_append_class = 'sltstandard_front';
                } else if ($field['type'] == 'arf_multiselect') {
                    $arf_control_append_class = 'sltstandard_front';
                } else if ($field['type'] == 'date') {
                    $arf_control_append_class = 'arf_date_main_controls';
                } else if( $field['type'] == 'arfslider' ){
                    $arf_control_append_class = 'arf_slider_control';
                }
                $unserialize_field_optins = $arformcontroller->arfHtmlEntities(json_decode($field['field_options'], true));
                if (json_last_error() != JSON_ERROR_NONE) {
                    $unserialize_field_optins = maybe_unserialize($field['field_options']);
                }
                $placeholder_text = isset($unserialize_field_optins['placeholdertext']) ? $unserialize_field_optins['placeholdertext'] : (isset($unserialize_field_optins['placehodertext']) ? $unserialize_field_optins['placehodertext'] : '');
                
                $placeholder_text = html_entity_decode(htmlentities($placeholder_text));

                $arf_control_align_class = "";
                if (isset($field['align']) && $field['align'] != '') {
                    switch ($field['align']) {
                        case 'inline':
                            $arf_control_align_class = 'arf_single_row';
                            break;
                        case 'block':
                            $arf_control_align_class = 'arf_multiple_row';
                            break;
                        case 'arf_col_2':
                            $arf_control_align_class = 'arf_col_chk_radio_two';
                            break;
                        case 'arf_col_3':
                            $arf_control_align_class = 'arf_col_chk_radio_three';
                            break;
                        case 'arf_col_4':
                            $arf_control_align_class = 'arf_col_chk_radio_four';
                            break;
                        default:
                            $arf_control_align_class = "";
                            break;
                    }
                }
                switch ($field['type']) {
                    case 'checkbox':
                    	if( $frm_css['arfinputstyle'] == 'material' || $frm_css['arfinputstyle'] == 'material_outlined' ){
                    		$arf_control_append_class .= ' arf_material_checkbox ';
	                        if ($newarr['arfcheckradiostyle'] == 'material') {
	                            if ($newarr['arfcheckradiostyle'] != 'custom') {
	                                $arf_control_append_class .= ' arf_default_material ';
	                            } else {
	                                $arf_control_append_class .= ' arf_custom_checkbox ';
	                            }
	                        } else {
	                            if ($newarr['arfcheckradiostyle'] != 'custom') {
	                                $arf_control_append_class .= ' arf_advanced_material ';
	                            } else {
	                                $arf_control_append_class .= ' arf_custom_checkbox ';
	                            }
	                        }
                    	} else {
	                        if ($newarr['arfinputstyle'] == 'rounded') {
	                            if ($newarr['arfcheckradiostyle'] != 'custom') {
	                                $arf_control_append_class .= ' arf_rounded_flat_checkbox ';
	                            } else {
	                                $arf_control_append_class .= ' arf_rounded_flat_checkbox arf_custom_checkbox ';
	                            }
	                        } else if ($newarr['arfinputstyle'] == 'standard') {
	                            if ($newarr['arfcheckradiostyle'] != 'custom') {
	                                $arf_control_append_class .= ' arf_standard_checkbox ';
	                            } else {
	                                $arf_control_append_class .= ' arf_custom_checkbox ';
	                            }
	                        }
                        }
                        break;
                    case 'radio':
                    	if( $frm_css['arfinputstyle'] == 'material' || $frm_css['arfinputstyle'] == 'material_outlined' ){
                    		$arf_control_append_class .= ' arf_material_radio ';
	                        if ($newarr['arfcheckradiostyle'] == 'material') {
	                            if ($newarr['arfcheckradiostyle'] != 'custom') {
	                                $arf_control_append_class .= ' arf_default_material ';
	                            } else {
	                                $arf_control_append_class .= ' arf_custom_radio ';
	                            }
	                        } else {
	                            if ($newarr['arfcheckradiostyle'] != 'custom') {
	                                $arf_control_append_class .= ' arf_advanced_material ';
	                            } else {
	                                $arf_control_append_class .= ' arf_custom_radio ';
	                            }
	                        }
                    	} else {
                    		if ($newarr['arfinputstyle'] == 'rounded') {
	                            if ($newarr['arfcheckradiostyle'] != 'custom') {
	                                $arf_control_append_class .= ' arf_rounded_flat_radio ';
	                            } else {
	                                $arf_control_append_class .= ' arf_custom_radio ';
	                            }
	                        } else if ($newarr['arfinputstyle'] == 'standard') {
	                            if ($newarr['arfcheckradiostyle'] != 'custom') {
	                                $arf_control_append_class .= ' arf_standard_radio ';
	                            } else {
	                                $arf_control_append_class .= ' arf_custom_radio ';
	                            }
	                        }
                    	}
                        
                        break;
                }
                $arf_control_append_class = apply_filters('arf_controls_added_class_outside_materialize', $arf_control_append_class, $field['type']);
                $arf_field_wrapper_cls = ($frm_css['arfinputstyle'] == 'material' || 'material_outlined' == $frm_css['arfinputstyle'] ) ? 'input-field' : '';
                ?>

                <?php if (isset($field['tooltip_text']) && $field['tooltip_text'] != '' && ( $frm_css['arfinputstyle'] == 'material' || $frm_css['arfinputstyle'] == 'material_outlined' ) ) { ?>
                        
                        <div data-style="<?php echo $frm_css['arfinputstyle']; ?>"   style="<?php echo (isset($field['field_width']) && $field['field_width'] != '') ? 'width:'.$field['field_width'].'px;' : ''; ?>" class="controls arfhelptipfocus tipso_style <?php echo $arf_control_append_class . ' ' . $arf_control_align_class.' '.$arf_field_wrapper_cls; ?>" data-title="<?php echo $field['tooltip_text']; ?>">

                <?php
                    }else { ?>
                        <div data-style="<?php echo $frm_css['arfinputstyle']; ?>"   style="<?php echo (isset($field['field_width']) && $field['field_width'] != '') ? 'width:'.$field['field_width'].'px;' : ''; ?>"  class="controls <?php echo $arf_control_append_class . ' ' . $arf_control_align_class.' '.$arf_field_wrapper_cls; ?>">
                <?php } ?>
                    
                    <?php
                    if( 'material_outlined' == $frm_css['arfinputstyle'] ){
                        $material_outlined_cls = '';
                        $has_phone_with_utils = false;
                        $phone_with_utils_cls = '';
                        if(isset($field['phonetype'])){
                            if( $field['type'] == 'phone' && $field['phonetype'] == 1){
                                $has_phone_with_utils = true;
                                $phone_with_utils_cls = 'arf_phone_with_flag';
                            }
                        }
                        if( !empty( $field['enable_arf_prefix'] ) || !empty( $field['enable_arf_suffix'] ) ){
                            $material_outlined_cls = 'arf_material_outline_container_with_icons ' . $phone_with_utils_cls;
                        }
                        if( !empty( $field['enable_arf_prefix'] ) && empty( $field['enable_arf_suffix'] ) ){
                            $material_outlined_cls .= ' arf_only_leading_icon ';
                        }

                        if( empty( $field['enable_arf_prefix'] ) && !empty( $field['enable_arf_suffix'] ) ){
                            $material_outlined_cls .= ' arf_only_trailing_icon ';
                        }
                        if( !empty( $field['enable_arf_prefix'] ) && !empty( $field['enable_arf_suffix'] ) ){
                            $material_outlined_cls .= ' arf_both_icons ';
                        }
                        echo "<div class='arf_material_outline_container ".$material_outlined_cls." '>";
                        if( !empty( $field['enable_arf_prefix'] ) && $has_phone_with_utils == false ){
                            echo '<i class="arf_leading_icon ' . $field['arf_prefix_icon'] . '"></i>';
                        }
                        if( !empty( $field['enable_arf_suffix'] ) ){
                            echo '<i class="arf_trailing_icon ' . $field['arf_suffix_icon'] . '"></i>';
                        }
                    }
                    if( 'material' == $frm_css['arfinputstyle'] ){
                        $material_outlined_cls = '';
                        $has_phone_with_utils = false;
                        $phone_with_utils_cls = '';
                        if(isset($field['phonetype'])){
                            if( $field['type'] == 'phone' && $field['phonetype'] == 1){
                                $has_phone_with_utils = true;
                                $phone_with_utils_cls = 'arf_phone_with_flag';
                            }
                        }
                        if( !empty( $field['enable_arf_prefix'] ) || !empty( $field['enable_arf_suffix'] ) ){
                            $material_outlined_cls = 'arf_material_theme_container_with_icons ' . $phone_with_utils_cls;
                        }
                        if( !empty( $field['enable_arf_prefix'] ) && empty( $field['enable_arf_suffix'] ) ){
                            $material_outlined_cls .= ' arf_only_leading_icon ';
                        }
                        if( empty( $field['enable_arf_prefix'] ) && !empty( $field['enable_arf_suffix'] ) ){
                            $material_outlined_cls .= ' arf_only_trailing_icon ';
                        }
                        if( !empty( $field['enable_arf_prefix'] ) && !empty( $field['enable_arf_suffix'] ) ){
                            $material_outlined_cls .= ' arf_both_icons ';
                        }
                        echo "<div class='arf_material_theme_container ".$material_outlined_cls." '>";
                        if( !empty( $field['enable_arf_prefix'] ) && $has_phone_with_utils == false ){
                            echo '<i class="arf_leading_icon ' . $field['arf_prefix_icon'] . '"></i>';
                        }
                        if( !empty( $field['enable_arf_suffix'] ) ){
                            echo '<i class="arf_trailing_icon ' . $field['arf_suffix_icon'] . '"></i>';
                        }
                    }
                    switch ($field['type']) {
                        case 'text':
                        case 'website':
                        case 'phone':
                        case 'date':
                        case 'email':
                        case 'confirm_email':
                        case 'url':
                        case 'number':
                        case 'password':
                        case 'confirm_password':
                        case 'time':
                        case 'image':
                        case ARF_AUTOCOMPLETE_SLUG:
                            $input_cls = '';
                            $inp_cls = '';
                            if ($field['type'] == 'date') {
                                $input_cls .= " arf_editor_datetimepicker ";
                            } else if ($field['type'] == 'time') {
                                $input_cls .= " arf_timepicker ";
                            } else if( $field['type'] == 'phone' ){
                                if( isset($field['phonetype']) && $field['phonetype'] == 1 ){
                                    $input_cls = ' arf_phone_utils ';
                                }
                            }
                            if( $frm_css['arfinputstyle'] != 'material' && 'material_outlined' != $frm_css['arfinputstyle'] ){
                                echo $prefix_suffix_style_start;
                                echo $prefix_suffix_style;
                                echo $prefix_suffix_style_end;
                                echo $prefix_suffix_wrapper_start;
                                echo $arf_prefix_icon;
                            }

                            $field_opts = $arformcontroller->arfHtmlEntities(json_decode($field['field_options'], true));
                            if (json_last_error() != JSON_ERROR_NONE) {
                                $field_opts = maybe_unserialize($field['field_options']);
                            }
                            $field_opts['default_value'] = html_entity_decode(htmlentities($field_opts['default_value']));
                            if ($field['type'] == ARF_AUTOCOMPLETE_SLUG) {
                                if (isset($field['separate_value']) && $field['separate_value'] == '1') {
                                    $autocomplete_separate_value = '';
                                    $autocomplete_separate = array();
                                    foreach ($field['options'] as $k => $options) {
                                        $temp_array = new stdClass();
                                        $autocomplete_separate_value .= "{id: '" . esc_attr($options['value']) . "', name: '" . esc_attr($options['label']) . "'},";
                                        $temp_array->id = esc_attr($options['value']);
                                        $temp_array->name = esc_attr($options['label']);
                                        array_push($autocomplete_separate, $temp_array);
                                    }
                                    
                                    ?>
                                    <input id="field_<?php echo $field['field_key']; ?>" name="item_meta[<?php echo $field['id']; ?>]" <?php echo isset($field_opts['default_value']) && trim($field_opts['default_value']) != '' ? "value=\"".$field_opts['default_value']."\"" : ''; ?> data-source='<?php echo json_encode($autocomplete_separate); ?>' data-items="10" <?php echo ($placeholder_text != '') ? 'placeholder="'.$placeholder_text.'"' : ''; ?> type="text"  class="<?php echo $input_cls . ' ' . $inp_cls; ?>" />
                                    <?php
                                } else {
                                    $autocomplete_value = '';
                                    foreach ($field['options'] as $k => $options) {
                                        if (is_array($options)) {
                                            $autocomplete_value .= '"' . esc_attr($options['label']) . '",';
                                        } else {
                                            $autocomplete_value .= '"' . esc_attr($options) . '",';
                                        }
                                    }
                                    ?>
                                    <input id="field_<?php echo $field['field_key']; ?>" name="item_meta[<?php echo $field['id']; ?>]" <?php echo isset($field_opts['default_value']) && $field_opts['default_value'] != '' ? "value=\"".$field_opts['default_value']."\"" : ''; ?> data-source='[<?php echo substr($autocomplete_value, 0, -1); ?>]' data-items="10" data-provide='typeahead' <?php echo ($placeholder_text != '') ? 'placeholder="'.$placeholder_text.'"' : ''; ?> type="text" class="<?php echo $input_cls . ' ' . $inp_cls; ?>" />
                                <?php }
                                ?>

                            <?php } else {
                                ?> 
                                <input id="field_<?php echo $field['field_key']; ?>" name="item_meta[<?php echo $field['id']; ?>]" <?php echo isset($field_opts['default_value']) && $field_opts['default_value'] != '' ? "value=\"".$field_opts['default_value']."\"" : ''; ?> <?php echo ($placeholder_text != '') ? "placeholder=\"{$placeholder_text}\"" : ''; ?> type="<?php echo ($field['type'] == 'password') ? 'password' : 'text'; ?>" <?php echo $default_country_code; ?> class="<?php echo $input_cls . ' ' . $inp_cls; ?>" />
                                <?php
                            }
                            if( $frm_css['arfinputstyle'] == 'material' ){
                                do_action('arf_material_style_editor_content',$field, $frm_css, $display, $arf_main_label_cls, $arf_column_classes);
                            } else if( $frm_css['arfinputstyle'] == 'material_outlined' ){
                                do_action('arf_material_outlined_style_editor_content',$field, $frm_css, $display, $arf_main_label_cls, $arf_column_classes);
                            }
                            if( $frm_css['arfinputstyle'] != 'material' && $frm_css['arfinputstyle'] != 'material_outlined' ){
                                echo $arf_suffix_icon;
                                echo $prefix_suffix_wrapper_end;
                            }

                            break;
                        case 'textarea':
                            
                            ?><textarea name="<?php echo $field_name ?>" id="itemmeta_<?php echo $field['id']; ?>" onkeyup="arfchangeitemmeta('<?php echo $field['id']; ?>');" rows="<?php echo $field['max_rows']; ?>" <?php echo ($placeholder_text != '' ) ? 'placeholder="'.$placeholder_text.'"' : ''; ?> ><?php echo isset($field['default_value']) && $field['default_value'] != '' ? $armainhelper->esc_textarea($field['default_value']) : ''; ?></textarea> <?php
                            if( $frm_css['arfinputstyle'] == 'material' ){
                                do_action('arf_material_style_editor_content',$field, $frm_css, $display, $arf_main_label_cls, $arf_column_classes);
                            } else if( $frm_css['arfinputstyle'] == 'material_outlined' ){
                                do_action('arf_material_outlined_style_editor_content',$field, $frm_css, $display, $arf_main_label_cls, $arf_column_classes);                                
                            }
                            break;
                        case 'colorpicker':
                            $field['colorpicker_type'] = isset($field['colorpicker_type']) ? $field['colorpicker_type'] : 'advanced';

                            $colpick_class = ($field['colorpicker_type'] == 'advanced') ? "jscolor" : "arf_editor_basic_colorpicker";
                            $arfcolorpickerstyle = '';
                            if (isset($field['default_value']) && $field['default_value'] != '') {
                                $defaultcolor = $field['default_value'];
                                $defaultcolor = strtolower(str_replace('#', '', $defaultcolor));
                                if ($defaultcolor == '000' || $defaultcolor == '000000') {
                                    $arfcolorpickerstyle = "background:#000000 !important;color:#FFFFFF;";
                                } else if ($defaultcolor == 'fff' || $defaultcolor == 'ffffff') {
                                    $arfcolorpickerstyle = "background:#ffffff !important;color:#000000;";
                                } else {
                                    $arfcolorpickerstyle = "background:#$defaultcolor!important;color:#333333;";
                                }
                            }
                            if( 'material_outlined' == $frm_css['arfinputstyle'] ){
                            ?>
                                <div class="arfcolorpickerfield" data-field-id="<?php echo $field['id']; ?>" id="arfcolorpicker_<?php echo $field['field_key'] . '_' . $field['id']; ?>" dat-fid="itemmeta_<?php echo $field['id']; ?>">
                                    <div class="arfcolorimg">
                                        <div class="paint_brush_position arf_material_outlined_icon">
                                            <svg width='18px' height='18px' viewBox="0 0 22 22"><g id="email"><path fill="#333333" fill-rule="evenodd" clip-rule="evenodd" d="M15.948,7.303L15.875,7.23l0.049-0.049l-2.459-2.459l3.944-3.872l2.313,0.024v2.654L15.948,7.303z M12.631,6.545c0.058,0.039,0.111,0.081,0.167,0.122c0.036,0.005,0.066,0.011,0.066,0.011c0.022,0.008,0.034,0.023,0.056,0.032l1.643,1.643c0.58,5.877-7.619,6.453-7.619,6.453c-5.389,0.366-5.455-1.907-5.455-1.907c3.559,1.164,6.985-5.223,6.985-5.223C11.001,4.915,12.631,6.545,12.631,6.545z"></path></g></svg>
                                        </div>
                                    </div>
                                    <div class="arfcolorvalue <?php echo $colpick_class; ?>" id="item-meta_<?php echo $field['id']; ?>" style="<?php echo $arfcolorpickerstyle; ?>" data-jscolor='{"hash":true,"valueElement":"itemmeta_<?php echo $field['id']; ?>","onFineChange":"arf_update_color(this,<?php echo '\"itemmeta_'.$field['id'].'\"'; ?>)"}' data-fid="itemmeta_<?php echo $field['id']; ?>">
                                        <?php echo $field['default_value']; ?>
                                    </div>
                                    <input type="hidden" class="arf_editor_colorpicker arf_colorpicker" id="itemmeta_<?php echo $field['id']; ?>" name="<?php echo $field_name; ?>" value="<?php echo isset($field['default_value']) ? esc_attr($field['default_value']) : ''; ?>" />
                                </div>
                            <?php
                            } else {

                            ?>
                                <div class="arf_editor_prefix_suffix_wrapper " data-field-id="<?php echo $field['id']; ?>" id="arfcolorpicker_<?php echo $field['field_key'] . '_' . $field['id']; ?>" data-fid="itemmeta_<?php echo $field['id']; ?>">
                                    <span class="arf_editor_prefix arf_colorpicker_prefix_editor"  id='arf_editor_prefix_<?php echo $field['id']; ?>'>
                                        <div class="paint_brush_position">
                                            <svg width='18px' height='18px' viewBox="0 0 22 22"><g id="email"><path fill="#333333" fill-rule="evenodd" clip-rule="evenodd" d="M15.948,7.303L15.875,7.23l0.049-0.049l-2.459-2.459l3.944-3.872l2.313,0.024v2.654L15.948,7.303z M12.631,6.545c0.058,0.039,0.111,0.081,0.167,0.122c0.036,0.005,0.066,0.011,0.066,0.011c0.022,0.008,0.034,0.023,0.056,0.032l1.643,1.643c0.58,5.877-7.619,6.453-7.619,6.453c-5.389,0.366-5.455-1.907-5.455-1.907c3.559,1.164,6.985-5.223,6.985-5.223C11.001,4.915,12.631,6.545,12.631,6.545z"></path></g></svg>
                                        </div>
                                    </span>
                                    <input type="text" class="textbox arf_prefix_only arf_prefix_suffix arf_colorpicker arf_editor_colorpicker <?php echo $colpick_class; ?>"  data-jscolor='{"hash":true,"valueElement":"itemmeta_<?php echo $field['id']; ?>","onFineChange":"arf_update_color(this,<?php echo '\"itemmeta_'.$field['id'].'\"'; ?>)"}' data-fid="itemmeta_<?php echo $field['id']; ?>" name="<?php echo $field_name ?>" id="itemmeta_<?php echo $field['id']; ?>" value="<?php echo isset($field['default_value']) ? esc_attr($field['default_value']) : ''; ?>" style="width:70px !important;padding:7px 3px !important;font-size:12px !important;margin:0px !important;<?php echo $arfcolorpickerstyle;?>"/>
                                </div>

                            <?php
                            }
                            break;
                        case 'like':
                            ?>
                            <div class="like_container">
                                <input type="hidden" name="hidden_active_like_bgcolor" class="active_like_bgcolor_<?php echo $field['field_key']; ?>" value="<?php echo isset($field['like_bg_color']) ? $field['like_bg_color'] : ''; ?>" />
                                <input type="hidden" name="hidden_active_dislike_bgcolor" class="active_dislike_bgcolor_<?php echo $field['field_key']; ?>" value="<?php echo isset($field['dislike_bg_color']) ? $field['dislike_bg_color'] : ''; ?>" />

                                <input type="radio" data-field-id="<?php echo $field['id']; ?>" style="left: -999px;position: absolute;" class="arf_hide_opacity <?php echo (is_admin()) ? "arf_editor_like_btn" : ""; ?> arf_like" name="item_meta[<?php echo $field['id']; ?>]" id="field_<?php echo $field['field_key']; ?>-0" value="1" <?php isset($field['default_value']) ? checked($field['default_value'], 1) : ''; ?> />
                                <label id="like_<?php echo $field['field_key']; ?>-0" class="arf_like_btn <?php if (isset($field['default_value']) && $field['default_value'] == '1') { ?> active <?php } ?> field_edit arfhelptip" for="field_<?php echo $field['field_key']; ?>-0" title="<?php echo esc_attr($field['lbllike']); ?>" data-title="<?php echo esc_attr($field['lbllike']); ?>">
                                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg"  height="30px" width="30px" viewBox="0 0 25 25"><g><g><path fill="#FFFFFF" d="M22.348,12.349c-0.017,0.011-0.031,0.021-0.047,0.029c0.241,0.281,0.451,0.678,0.451,1.207   c0,0.814-0.486,1.366-1.095,1.692c0.25,0.319,0.378,0.715,0.378,1.178c0,0.579-0.219,1.308-1.168,1.748   c0.175,0.315,0.288,0.722,0.204,1.248c-0.156,0.983-1.39,1.335-3.447,1.335H8.352c-0.842,0-1.207-0.395-1.374-0.98L6.96,19.745   v-9.289c0-0.439,0.081-0.576,0.111-0.627l0.018-0.028C7.311,9.485,7.804,9.19,7.998,8.913c1.802-2.566,2.632-3.43,2.519-5.011   C10.396,2.197,10.509,1.03,12,0.879c0.085-0.009,0.172-0.013,0.258-0.013c0.422,0,1.382,0.105,2.108,0.812   c0.706,0.686,1.451,1.746,1.589,3.151c0.103,1.044,0.127,2.343-0.168,3.242c1.628,0.001,4.758,0.003,5.252,0.003   c1.067,0,2.217,1.08,2.217,2.593C23.255,11.582,22.762,12.087,22.348,12.349z M4.718,20.854H3.442   c-0.409,0-0.756-0.295-0.816-0.694l-1.395-9.732c-0.035-0.234,0.034-0.472,0.191-0.651C1.58,9.598,1.808,9.495,2.047,9.495h2.67   c0.456,0,0.826,0.365,0.826,0.814v9.731C5.543,20.491,5.173,20.854,4.718,20.854z"/></g></g></svg>
                                </label>
                                <input type="radio" data-field-id="<?php echo $field['id']; ?>" style="left: -999px;position: absolute;" class="arf_hide_opacity <?php echo (is_admin()) ? "arf_editor_like_btn" : ""; ?> arf_like" name="item_meta[<?php echo $field['id']; ?>]" id="field_<?php echo $field['field_key']; ?>-1" value="0" <?php isset($field['default_value']) ? checked($field['default_value'], 0) : ''; ?> /><label id="like_<?php echo $field['field_key']; ?>-1" class="arf_dislike_btn <?php if (isset($field['default_value']) && $field['default_value'] == '0') { ?> active <?php } ?> field_edit arfhelptip" for="field_<?php echo $field['field_key']; ?>-1" title="<?php echo esc_attr($field['lbldislike']); ?>" data-title="<?php echo esc_attr($field['lbldislike']); ?>">
                                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg"  height="30px" width="30px" viewBox="0 0 25 25"><g xmlns="http://www.w3.org/2000/svg"><g><path fill="#ffffff" d="M23.041,11.953c-0.156,0.179-0.385,0.282-0.625,0.282h-2.668c-0.455,0-0.824-0.365-0.824-0.815V1.682   c0-0.451,0.369-0.816,0.824-0.816h1.274c0.409,0,0.757,0.296,0.816,0.696l1.394,9.739C23.268,11.535,23.199,11.774,23.041,11.953z    M17.379,11.929c-0.221,0.316-0.715,0.612-0.908,0.889c-1.801,2.568-2.63,3.434-2.518,5.015c0.121,1.707,0.008,2.874-1.481,3.026   c-0.085,0.009-0.172,0.013-0.258,0.013c-0.422,0-1.381-0.104-2.107-0.813c-0.705-0.686-1.451-1.746-1.588-3.152   c-0.103-1.045-0.126-2.346,0.169-3.244c-1.627-0.002-4.756-0.004-5.249-0.004c-1.067,0-2.216-1.081-2.216-2.595   c0-0.916,0.494-1.421,0.907-1.683c0.016-0.01,0.032-0.02,0.047-0.029C1.935,9.068,1.726,8.671,1.726,8.142   c0-0.815,0.486-1.367,1.094-1.694C2.569,6.129,2.441,5.733,2.441,5.27c0-0.58,0.219-1.309,1.168-1.749   c-0.176-0.316-0.288-0.723-0.205-1.25c0.156-0.984,1.39-1.336,3.446-1.336h9.267c0.842,0,1.207,0.394,1.373,0.982l0.018,0.06v9.296   c0,0.44-0.081,0.578-0.112,0.628L17.379,11.929z"/></g></g></svg>
                                </label>
                                <span class='arf_like_reset_btn arfhelptip' title="<?php echo addslashes(esc_html__('Reset','ARForms'));?>" data-title="<?php echo addslashes(esc_html__('Reset','ARForms'));?>" onclick="arfresetlikefield(<?php echo $field['id']; ?>);">
                                    <svg version='1.1' xmlns='http://www.w3.org/2000/svg' height='16px' width='25px' viewBox="0 0 96 100" style="margin-top:6px"><g><?php echo ARF_CUSTOM_RESET_ICON;?></g></svg>
                                </span>
                            </div>

                            <?php
                            break;
                        case 'checkbox':
                        	if( $frm_css['arfinputstyle'] == 'material' || $frm_css['arfinputstyle'] == 'material_outlined' ){
                        		$k = 0;
	                            $arf_chk_counter = 1;
	                            $field_opts = json_decode($field['field_options']);

	                            if (isset($field['options']) && !empty($field['options'])) {
	                                if (!is_array($field['options'])) {
	                                    $field['options'] = json_decode($field['options'], true);
	                                }

                                    $chk_icon = "";
                                    if(isset($field['arf_check_icon']) && $field['arf_check_icon'] !=""){
                                        $chk_icon = $field['arf_check_icon'];
                                    }else{
                                        $chk_icon = "fas fa-check";
                                    }
                                        
                                  
                                    if( isset($field['image_width']) && $field['image_width'] !="") {
                                        $image_size = $field['image_width'];
                                    } else {
                                        $image_size = 120;
                                    }

                                    foreach ($field['options'] as $opt_key => $opt) {
                                        if (is_admin() && $arf_chk_counter > 5) {
                                            continue;
                                        }

	                                    $label_image = '';
	                                    if (isset($atts) and isset($atts['opt']) and ( $atts['opt'] != $opt_key))
	                                        continue;

	                                    $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

	                                    $opt = apply_filters('show_field_label', $opt, $opt_key, $field);



	                                    if (is_array($opt)) {
	                                        $label_image = isset($opt['label_image']) ? $opt['label_image'] : '';
	                                        $opt = $opt['label'];
	                                        $field_val = isset($field['separate_value']) && ($field['separate_value']) ? $field_val['value'] : $opt;
	                                    }

	                                    $checked = '';
	                                    $checked_values = '';

	                                    $checked_values = (isset($field['default_value']) && $field['default_value'] != '') ? $field['default_value'] : array();

	                                    $is_checkbox_checked = false;
	                                    if (!empty($checked_values) && in_array($field_val, $checked_values)) {
	                                        $is_checkbox_checked = true;
	                                        $checked = 'checked="checked"';
	                                    }

	                                    $arf_radio_box_hide = '';
	                                    $arf_custom_checkbox_wrapper = '';

	                                    if ($field_opts->use_image == 1 && $label_image != '') {
	                                        $arf_custom_checkbox_wrapper = 'arffditor_checbox_wrap';
	                                    }

	                                    $label_image_wrapper_class = ($field_opts->use_image == 1 && $label_image != "" ) ? "arf_enable_checkbox_image_editor" : "";
	                                    $material_chk_wrapper = ($field_opts->use_image == 1 && $label_image != "" ) ? "arf_material_checkbox_image_wrapper" : "";

	                                    echo '<div class="arf_checkbox_style ' . $material_chk_wrapper . ' ' . $arf_custom_checkbox_wrapper . ' ' . $label_image_wrapper_class . '" id="frm_checkbox_' . $field['id'] . '-' . $opt_key . '">';
	                                    if (!isset($atts) or ! isset($atts['label']) or $atts['label']) {
	                                        $_REQUEST['arfaction'] = ( isset($_REQUEST['arfaction']) ) ? $_REQUEST['arfaction'] : "";
	                                        echo "<div class='arf_checkbox_input_wrapper'>";
	                                        echo '<input type="checkbox" name="' . $field_name . '[]" id="field_' . $field['id'] . '-' . $opt_key . '" value="' . esc_attr($field_val) . '" ' . $checked . ' style="' . $arf_radio_box_hide . '"';
	                                        if ($k == 0) {
	                                            if (isset($field['required']) and $field['required']) {
                                                    $field_blan_msg_val = (isset($field['blank'])) ? esc_attr($field['blank']) : '';
	                                                echo 'data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . $field_blan_msg_val . '"';
	                                            }
	                                        } echo ' />';
	                                        echo "<span>";
	                                        if ($newarr['arfcheckradiostyle'] == 'custom') {
	                                            echo "<i class='" . $newarr['arf_checked_checkbox_icon'] . "'></i>";
	                                        }
	                                        echo "</span>";
	                                        echo "</div>";
	                                        $label_image_wrapper_class = ($label_image != "" ) ? "arf_enable_checkbox_image_editor" : "";
	                                        echo '<label for="field_' . $field['id'] . '-' . $opt_key . '"  class="' . $label_image_wrapper_class . '">';

	                                        if ($field_opts->use_image == 1 && $label_image != '') {
	                                            $temp_check = '';
	                                            if ($is_checkbox_checked) {
	                                                $temp_check = 'checked';
	                                            }

                                                if($frm_css['arfinputstyle'] == 'material'){ 
                                                  
                                                        echo '<label for="field_'.$field['id'].'-'.$opt_key.'" class="arf_checkbox_label_image_editor '.$temp_check.' '.$chk_icon .'">';
                                                            echo '<svg role"none" style="max-width:100%; width:'.$image_size.'px; height:'.$image_size.'px">';
                                                            echo '<mask id="clip-cutoff_field_'.$field['id'].'-'.$opt_key.'">';
                                                               echo '<rect fill="white" x="0" y="0" rx="8" ry="8" width="'.$image_size.'" height="'.$image_size.'"></rect>';
                                                               echo '<rect fill="black" rx="4" ry="4" width="27" height="27" class="rect-cutoff"></rect>';
                                                            echo '</mask>';
                                                            echo '<g mask="url(#clip-cutoff_field_'.$field['id'].'-'.$opt_key.')">';
                                                                echo '<image x="0" y="0" height="'.$image_size.'px" preserveAspectRatio="xMidYMid slice" width="'.$image_size.'px" href="'.esc_attr($label_image).'"> </image>';
                                                                echo '<rect fill="none" x="0" y="0" rx="8" width="'.$image_size.'px" height="'.$image_size.'px" class="img_stroke"></rect>';
                                                            echo '</g>';
                                                            echo '</svg>';
                                                        echo '</label>';
                                                  
                                                }else{
                                                    echo '<span class="arf_checkbox_label_image_editor '.$temp_check .' '.$chk_icon .' ">';
                                                    echo '<img src="'.esc_attr($label_image).'" style="max-width:100%; width:'.$image_size.'px; height: '.$image_size.'px">';
                                                    echo '</span>';
                                                }
                                                echo '<span class="arf_checkbox_label" style="width:'.$image_size.'px">';
                                            }
                                            echo html_entity_decode($opt);

	                                        if ($field_opts->use_image == 1 && $label_image != '') {
	                                            echo '</span>';
	                                        }

	                                        echo '</label>';
	                                    }
	                                    echo '</div>';

	                                    $k++;
	                                    $arf_chk_counter++;
	                                }
	                            }
                        	} else {
                        		$k = 0;
	                            $arf_chk_counter = 1;

	                            $field_opts = json_decode($field['field_options']);

                                if (isset($field['options']) && !empty($field['options'])) {
                                    if (!is_array($field['options'])) {
                                        $field['options'] = json_decode($field['options'], true);
                                    }

                                    $chk_icon = "";
                                    if(isset($field['arf_check_icon']) && $field['arf_check_icon'] !=""){
                                        $chk_icon = $field['arf_check_icon'];
                                    }else{
                                        $chk_icon = "fas fa-check";
                                    }

                                    if( isset($field['image_width']) && $field['image_width'] !="" ){
                                        $image_size = $field['image_width'];
                                    } else {
                                        $image_size = 120;
                                    }

	                                foreach ($field['options'] as $opt_key => $opt) {
	                                    if (is_admin() && $arf_chk_counter > 5) {
	                                        continue;
	                                    }

	                                    $label_image = '';
	                                    if (isset($atts) and isset($atts['opt']) and ( $atts['opt'] != $opt_key))
	                                        continue;

	                                    $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

	                                    $opt = apply_filters('show_field_label', $opt, $opt_key, $field);

	                                    if (is_array($opt)) {
	                                        $label_image = isset($opt['label_image']) ? $opt['label_image'] : '';
	                                        $opt = $opt['label'];
	                                        $field_val = isset($field['separate_value']) && $field['separate_value'] ? $field_val['value'] : $opt;
	                                    }

	                                    $checked = '';
	                                    $checked_values = '';

	                                    $checked_values = (isset($field['default_value']) && $field['default_value'] != '') ? $field['default_value'] : array();
                                        
	                                    $is_checkbox_checked = false;
	                                    if (!empty($checked_values) && in_array($field_val, $checked_values)) {
	                                        $is_checkbox_checked = true;
	                                        $checked = 'checked="checked"';
	                                    }

	                                    $arf_radio_box_hide = '';
	                                    $arf_custom_checkbox_wrapper = '';

	                                    if ($field_opts->use_image == 1 && $label_image != '') {
	                                        $arf_custom_checkbox_wrapper = 'arf_editor_checbox_wrap arf_enable_checkbox_image_editor';
	                                    }

	                                    echo '<div class="arf_checkbox_style ' . $arf_custom_checkbox_wrapper . '" id="frm_checkbox_' . $field['id'] . '-' . $opt_key . '">';
	                                    echo "<div class='arf_checkbox_input_wrapper'>";
	                                    echo '<input type="checkbox" name="' . $field_name . '[]" id="field_' . $field['id'] . '-' . $opt_key . '" value="' . esc_attr($field_val) . '" ' . $checked . ' style="' . $arf_radio_box_hide . '"';
	                                    if ($k == 0) {
	                                        if (isset($field['required']) and $field['required']) {
                                                $field_require_msg_val = isset($field['blank']) ? esc_attr($field['blank']) : '';
	                                            echo 'data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . $field_require_msg_val . '"';
	                                        }
	                                    } echo ' />';
	                                    echo "<span>";
	                                    if ($newarr['arfcheckradiostyle'] == 'custom') {
	                                        echo "<i class='" . $newarr['arf_checked_checkbox_icon'] . "'></i>";
	                                    }
	                                    echo "</span>";
	                                    echo "</div>";
	                                    if (!isset($atts) or ! isset($atts['label']) or $atts['label']) {
	                                        $_REQUEST['arfaction'] = ( isset($_REQUEST['arfaction']) ) ? $_REQUEST['arfaction'] : "";
	                                        $label_image_wrapper_class = ($label_image != "" ) ? "arf_enable_checkbox_image_editor" : "";
	                                        echo '<label for="field_' . $field['id'] . '-' . $opt_key . '"  class="' . $label_image_wrapper_class . '">';
	                                        if ($field_opts->use_image == 1 && $label_image != '') {
	                                            $temp_check = '';
	                                            if ($is_checkbox_checked) {
	                                                $temp_check = 'checked';
	                                            }
                                               
                                                echo '<span class="arf_checkbox_label_image_editor '.$temp_check .' '.$chk_icon .'">';
                                                echo '<img src="'.esc_attr($label_image).'" style="max-width:100%; width:'.$image_size.'px; height:'.$image_size.'px">';
                                                echo '</span>';
                                                echo '<span class="arf_checkbox_label" style="width:'.$image_size.'px">';
                                            }
                                            echo html_entity_decode($opt);

	                                        if ($field_opts->use_image == 1 && $label_image != '') {
	                                            echo '</span>';
	                                        }

	                                        echo '</label>';
	                                    }
	                                    echo '</div>';
	                                    $k++;
	                                    $arf_chk_counter++;
	                                }
	                            }
                        	}

                            break;
                        case 'radio':
                        	if( $frm_css['arfinputstyle'] == 'material' || $frm_css['arfinputstyle'] == 'material_outlined' ){
                        		$k = 0;
	                            $arf_chk_counter = 1;
	                            $arf_radion_image_class = '';
	                            $field_opts = json_decode(stripslashes_deep($field['field_options']));

                                $chk_icon = "";
                                    if(isset($field['arf_check_icon']) && $field['arf_check_icon'] !=""){
                                        $chk_icon = $field['arf_check_icon'];
                                    }else{
                                        $chk_icon = "fas fa-check";
                                    }

                                if( isset($field['image_width'])  && $field['image_width'] != ""){
                                    $image_size = $field['image_width'];
                                } else {
                                    $image_size = 120;
                                }
                                
                                if (is_array($field['options'])) {
                                    foreach ($field['options'] as $opt_key => $opt) {
                                        if (is_admin() && $arf_chk_counter > 5) {
                                            continue;
                                        }
                                        $label_image = '';
                                        if (isset($atts) and isset($atts['opt']) and ( $atts['opt'] != $opt_key))
                                            continue;

	                                    $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

	                                    $opt = apply_filters('show_field_label', $opt, $opt_key, $field);
	                                    if (is_array($opt)) {
	                                        $label_image = isset($opt['label_image']) ? $opt['label_image'] : '';
	                                        $opt = $opt['label'];
	                                        $field_val = isset($field['separate_value']) && ($field['separate_value']) ? $field_val['value'] : $opt;
	                                    }

	                                    $arf_radio_box_hide = '';
	                                    $arf_custom_checkbox_wrapper = '';

	                                    if ($field_opts->use_image == 1 && $label_image != '') {
	                                        $arf_custom_checkbox_wrapper = 'arf_enable_radio_image_editor';
	                                    }

	                                    echo '<div class="arf_radiobutton ' . $arf_custom_checkbox_wrapper . '">';
	                                    if (!isset($atts) or ! isset($atts['label']) or $atts['label']) {
	                                        echo "<div class='arf_radio_input_wrapper'>";
	                                        echo '<input type="radio" name="' . $field_name . '" id="field_' . $field['id'] . '-' . $opt_key . '" data-unique-id="" value="' . esc_attr($field_val) . '" ';
	                                        $is_radio_checked = false;
	                                        if (isset($field['default_value']) && $field_val == $field['default_value']) {
	                                            $is_radio_checked = true;
	                                            echo 'checked="checked" ';
	                                        }
	                                        if ($k == 0) {
	                                            if (isset($field['required']) and $field['required']) {
                                                    $field_require_msg_val = isset($field['blank']) ? esc_attr($field['blank']) : '';
                                                    echo ' data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . $field_require_msg_val . '"';
	                                            }
	                                        }

	                                        echo ' />';
	                                        echo "<span>";
	                                        if ($newarr['arfcheckradiostyle'] == 'custom') {
	                                            echo "<i class='" . $newarr['arf_checked_radio_icon'] . "'></i>";
	                                        }
	                                        echo "</span>";
	                                        echo "</div>";
	                                        if ($field_opts->use_image == 1 && $label_image != '') {
	                                            $arf_radion_image_class = 'arf_enable_radio_image_editor';
	                                        }
	                                        echo '<label for="field_' . $field['id'] . '-' . $opt_key . '" class="' . $arf_radion_image_class . '">';
	                                        if ($field_opts->use_image == 1 && $label_image != '') {
	                                            $checked = "";
	                                            if ($is_radio_checked) {
	                                                $checked = 'checked';
	                                            }



                                        if($frm_css['arfinputstyle'] == 'material'){

                                            echo '<label for="field_'.$field['id'].'-'.$opt_key.'" class="arf_radio_label_image_editor ' . $checked . ' '. $chk_icon.' ">';
                                                
                                                echo '<svg role"none" style="max-width:100%; width:'.$image_size.'px; height:'.$image_size.'px">';
                                                    echo '<mask id="clip-cutoff_field_'.$field['id'].'-'.$opt_key.'">';
                                                        echo '<rect fill="white" x="0" y="0" rx="8" ry="8" width="'.$image_size.'px" height="'.$image_size.'px"></rect>';
                                                        echo '<rect fill="black" rx="4" ry="4" width="27" height="27" class="rect-cutoff"></rect>';
                                                    echo '</mask>';
                                                        echo '<g mask="url(#clip-cutoff_field_'.$field['id'].'-'.$opt_key.')">';
                                                            echo '<image x="0" y="0" height="'.$image_size.'px" preserveAspectRatio="xMidYMid slice" width="'.$image_size.'px" href="'.esc_attr($label_image).'"> </image>';
                                                            echo '<rect fill="none" x="0" y="0" rx="8" width="'.$image_size.'px" height="'.$image_size.'px" class="img_stroke"></rect>';
                                                        echo '</g>';
                                                echo '</svg>';
                                            echo '</label>';
                                                  
                                        }else{
                                            echo '<span class="arf_radio_label_image_editor ' . $checked . ' '. $chk_icon.' " >';
                                            echo '<img src="'.esc_attr($label_image).'" style="width:' .$image_size. 'px; height:' .$image_size.'px; max-width: 100%;">';
                                            echo '</span>';
                                        }
                                        echo '</span>';
                                        echo '<span class="arf_checkbox_label" style="width:'.$image_size.'px; display:block;">';
	                                        }
	                                        echo html_entity_decode($opt);
	                                        if ($field_opts->use_image == 1 && $label_image != '') {
	                                            echo '</span>';
	                                        }
	                                        echo '</label>';
	                                    }
	                                    echo '</div>';
	                                    $k++;
	                                    $arf_chk_counter++;
	                                }
	                            }
                        	} else {
                        		$k = 0;
	                            $arf_chk_counter = 1;
	                            $arf_radion_image_class = '';
	                            $field_opts = json_decode(stripslashes_deep($field['field_options']));
                                if( json_last_error() != JSON_ERROR_NONE ){
                                    $field_opts = json_decode($field['field_options']);
                                }
                                $chk_icon = "";
                                    if(isset($field['arf_check_icon']) && $field['arf_check_icon'] !=""){
                                        $chk_icon = $field['arf_check_icon'];
                                    }else{
                                        $chk_icon = "fas fa-check";
                                    }

                                if( isset($field['image_width']) && $field['image_width'] !=""){
                                    $image_size = $field['image_width'];
                                } else {
                                    $image_size = 120;
                                }

                                if (is_array($field['options'])) {
                                    foreach ($field['options'] as $opt_key => $opt) {
                                        if (is_admin() && $arf_chk_counter > 5) {
                                            continue;
                                        }
                                        $label_image = '';
                                        if (isset($atts) and isset($atts['opt']) and ( $atts['opt'] != $opt_key))
                                            continue;

	                                    $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

	                                    $opt = apply_filters('show_field_label', $opt, $opt_key, $field);
	                                    if (is_array($opt)) {
	                                        $label_image = isset($opt['label_image']) ? $opt['label_image'] : '';
	                                        $opt = $opt['label'];
	                                        $field_val = isset($field['separate_value']) && ($field['separate_value']) ? $field_val['value'] : $opt;
	                                    }

	                                    $arf_radio_box_hide = '';
	                                    $arf_custom_checkbox_wrapper = '';

	                                    if ($field_opts->use_image == 1 && $label_image != '') {
                                            $arf_custom_checkbox_wrapper = 'arf_enable_radio_image_editor';
	                                    }

	                                    echo '<div class="arf_radiobutton ' . $arf_custom_checkbox_wrapper . '">';

	                                    if (!isset($atts) or ! isset($atts['label']) or $atts['label']) {
	                                        echo "<div class='arf_radio_input_wrapper'>";
	                                        echo '<input type="radio" name="' . $field_name . '" id="field_' . $field['id'] . '-' . $opt_key . '" data-unique-id="" value="' . esc_attr($field_val) . '" ';
	                                        $is_radio_checked = false;
	                                        if (isset($field['default_value']) && $field_val == $field['default_value']) {
	                                            $is_radio_checked = true;
	                                            echo 'checked="checked" ';
	                                        }
	                                        if ($k == 0) {
	                                            if (isset($field['required']) and $field['required']) {
                                                    $field_require_msg_val = isset($field['blank']) ? esc_attr($field['blank']) : '';
	                                                echo ' data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . $field_require_msg_val . '"';
	                                            }
	                                        }

	                                        echo ' />';
	                                        echo "<span>";
	                                        if ($newarr['arfcheckradiostyle'] == 'custom') {
	                                            echo "<i class='" . $newarr['arf_checked_radio_icon'] . "'></i>";
	                                        }
	                                        echo "</span>";
	                                        echo "</div>";
	                                        if ($field_opts->use_image == 1 && $label_image != '') {
	                                            $arf_radion_image_class = 'arf_enable_radio_image_editor';
	                                        }
	                                        echo '<label for="field_' . $field['id'] . '-' . $opt_key . '" class="' . $arf_radion_image_class . '">';
	                                        if ($field_opts->use_image == 1 &&  $label_image != '') {
	                                            $checked = "";
	                                            if ($is_radio_checked) {
	                                                $checked = 'checked';
	                                            }

                                                echo '<span class="arf_radio_label_image_editor ' . $checked . ' '. $chk_icon .'">';
                                                echo '<img src="'. esc_attr($label_image) .'" style="width:'.$image_size.'px; height:'.$image_size.'px; max-width:100%;">';
                                                
                                                 echo '</span>';

	                                            echo '<span class="arf_checkbox_label" style="width:'.$image_size.'px; display:block;">';
	                                        }
	                                        echo html_entity_decode($opt);
	                                        if ($field_opts->use_image == 1 && $label_image != '') {
	                                            echo '</span>';
	                                        }
	                                        echo '</label>';
	                                    }
	                                    echo '</div>';
	                                    $k++;
	                                    $arf_chk_counter++;
	                                }
	                            }
                        	}
                            break;
                        case 'select':
                            $arf_main_label_cls = '';
                            if( 'material' == $frm_css['arfinputstyle'] ){
                                $arf_main_label_cls = ' active';
                            }

                            $select_field_opts = array();
                            $select_attrs = array();

                            $count_i = 0;
                            $field_opts = json_decode($field['field_options']);

                            $arf_set_label = false;

                            if( 'material_outlined' == $frm_css['arfinputstyle'] ){
                                $arf_set_label = true;
                            }

                            $opt_cls = array();

                            if (is_array($field['options']) && !empty($field['options'])) {
                                foreach ($field['options'] as $opt_key => $opt) {

                                    $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

                                    $opt = apply_filters('show_field_label', $opt, $opt_key, $field);

                                    if (is_array($opt)) {
                                        $opt = $opt['label'];
                                        if ($field_val['value'] == '(Blank)') {
                                            $field_val['value'] = "";
                                        }
                                        $field_val = ($field['separate_value']) ? $field_val['value'] : $opt;
                                    }
                                    if ($count_i == 0 and $opt == '') {
                                        $opt = addslashes(esc_html__('Please select', 'ARForms'));
                                    }

                                    $arfdefault_selected_val = isset($field['default_value']) ? trim($field['default_value']) : $field['value'];

                                    if (isset($field['set_field_value']) && !empty( $field['set_field_value'] ) ) {
                                        $arfdefault_selected_val = $field['set_field_value'];
                                    }

                                    if( !empty( $arfdefault_selected_val ) ){
                                        $arf_set_label = false;
                                    }

                                    $select_field_opts[ $field_val ] = $opt;

                                    $count_i++;
                                }
                            }

                            $select_attrs[ 'data-default-val' ] = $arfdefault_selected_val;
                            if (isset($field['required']) and $field['required']) {
                                $select_attrs['data-validation-required-message'] = esc_attr($field['blank']);
                            }

                            if( false == $arf_set_label && 'material_outlined' == $frm_css['arfinputstyle'] && !empty( $arfdefault_selected_val ) ){
                                $select_attrs['class'] = 'arf_material_active';
                            }

                            echo $maincontroller->arf_selectpicker_dom( $field_name, 'field_'.$field['field_key'], ' arf_form_field_picker ', '', $arfdefault_selected_val, $select_attrs, $select_field_opts, false, $opt_cls, false, array(), true, $field, false, '', '', $arf_set_label );

                            if( $frm_css['arfinputstyle'] == 'material' ){
                                do_action('arf_material_style_editor_content',$field, $frm_css, $display, $arf_main_label_cls, $arf_column_classes);
                            } else if( $frm_css['arfinputstyle'] == 'material_outlined' ){
                                do_action('arf_material_outlined_style_editor_content',$field, $frm_css, $display, $arf_main_label_cls, $arf_column_classes);
                            }

                            break;

                        case 'arf_multiselect':

                            $arf_main_label_cls = '';
                            if( 'material' == $frm_css['arfinputstyle'] ){
                                $arf_main_label_cls = ' active';
                            }
                            $field_name_mulselect = "item_meta[".$field['id']."][]";
                            $select_field_opts = array();
                            $select_attrs = array();

                            $count_i = 0;
                            $field_opts = json_decode($field['field_options']);

                            $arfdefault_selected_val = '';

                            $arf_set_default_label = false;

                            if( 'material_outlined' == $frm_css['arfinputstyle'] ){
                                $arf_set_default_label = true;
                            }

                            if (is_array($field['options']) && !empty($field['options'])) {
                                foreach ($field['options'] as $opt_key => $opt) {

                                    $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

                                    $opt = apply_filters('show_field_label', $opt, $opt_key, $field);

                                    if (is_array($opt)) {
                                        $opt = $opt['label'];
                                        if ($field_val['value'] == '(Blank)') {
                                            $field_val['value'] = "";
                                        }
                                        $field_val = ($field['separate_value']) ? $field_val['value'] : $opt;
                                    }
                                    
                                    if ($count_i == 0) {
                                        if( $opt == '' ){
                                            $opt = esc_html__('Please Select', 'ARForms');
                                            continue;
                                        } else {
                                            if( $field_val == '' && '' == $opt ){
                                                $opt = esc_html__('Please Select', 'ARForms');
                                                continue;
                                            }
                                        }
                                        $select_attrs['data-placeholder'] = $opt;
                                    }

                                    $arfdefault_selected_val = isset($field['default_value']) ? $field['default_value'] : '';

                                    if (isset($field['set_field_value'])) {
                                        $arfdefault_selected_val = $field['set_field_value'];
                                    }

                                    if( !empty( $arfdefault_selected_val ) ){
                                        $arf_set_default_label = false;
                                    }

                                    $select_field_opts[ $field_val ] = $opt;

                                    $count_i++;
                                }
                            }
                            if( is_array( $arfdefault_selected_val ) ){
                                $arfdefault_selected_val = implode( ',', $arfdefault_selected_val );
                            }

                            $select_attrs[ 'data-default-val' ] = $arfdefault_selected_val;
                            if (isset($field['required']) and $field['required']) {
                                $select_attrs['data-validation-required-message'] = esc_attr($field['blank']);
                            }
                            $select_attrs['class'] = 'multi-select';
                            if( false == $arf_set_default_label && 'material_outlined' == $frm_css['arfinputstyle'] && !empty( $arfdefault_selected_val ) ){
                                $select_attrs['class'] = 'multi-select arf_material_active';
                            }
                            echo $maincontroller->arf_selectpicker_dom( $field_name_mulselect, 'field_'.$field['field_key'], ' arf_form_field_picker multi-select ', '', $arfdefault_selected_val, $select_attrs, $select_field_opts, false, array(), false, array(), true, $field, false, '', '', $arf_set_default_label );
                            if( $frm_css['arfinputstyle'] == 'material' ){
                                do_action('arf_material_style_editor_content',$field, $frm_css, $display, $arf_main_label_cls, $arf_column_classes);
                            } else if( $frm_css['arfinputstyle'] == 'material_outlined' ){
                                do_action('arf_material_outlined_style_editor_content',$field, $frm_css, $display, $arf_main_label_cls, $arf_column_classes);
                            }
                            break;
                        case 'file':
                            global $arfsettings;
                            $file_upload_text = isset($field['file_upload_text']) ? $field['file_upload_text'] : esc_html__('Upload','ARForms');
                            $arf_dragable_label = isset($field['arf_dragable_label']) ? $field['arf_dragable_label'] : esc_html__('Drop files here or click to select', 'ARForms');
                            $arf_draggable = isset($field['arf_draggable']) ? $field['arf_draggable'] : 0;
                            
                            $simple_upload_display = "";
                            $draggable_upload_display = " display:none;";
                            if($arf_draggable==1 && isset($arfsettings->form_submit_type) && $arfsettings->form_submit_type == 1){
                                
                                $simple_upload_display = " display:none;";
                                $draggable_upload_display = "";
                            }

                            echo '<div class="file_main_control" style="display:inline-block; ';
                            if (isset($field['field_width']) and $field['field_width'] != '') {
                                echo 'width:' . $field['field_width'] . 'px';
                            }
                            echo '">';
                            echo '<div class="arf_file_field">';
                            echo '<div class="';

                            echo ' arfajax-file-upload" id="div_' . $field['field_key'] . '" data-id="' . $field['id'] . '" data-form-data-id="" data-form-id="' . $field['form_id'] . '" style="position: relative; overflow: hidden; cursor:pointer;'.$simple_upload_display.'">';
                            echo '<div class="arfajax-file-upload-img" style="float:left;"><svg width="14" height="14" viewBox="0 0 100 100"><path xmlns="http://www.w3.org/2000/svg" d="M77.656,56.25c2.396,0,4.531-0.625,6.406-1.875c1.822-1.303,3.385-2.865,4.688-4.688c1.25-1.875,1.875-4.037,1.875-6.484  s-1.275-5-3.828-7.656l-6.328-6.484c-6.719-6.927-13.49-13.646-20.312-20.156L50-0.781L39.844,8.906  c-6.823,6.51-13.594,13.229-20.312,20.156l-6.719,6.953c-2.292,2.344-3.438,4.609-3.438,6.797v0.781  c0,1.667,0.208,3.021,0.625,4.062s1.042,2.083,1.875,3.125c0.885,1.094,1.875,2.084,2.969,2.969  c1.042,0.834,2.083,1.459,3.125,1.875s2.344,0.625,3.906,0.625s2.865-0.209,3.906-0.625c1.094-0.469,2.24-1.197,3.438-2.188  c1.25-0.99,2.682-2.344,4.297-4.062l3.984-4.141v41.562c0,2.553,0.417,4.557,1.25,6.016c0.885,1.459,1.719,2.604,2.5,3.438  c0.833,0.781,1.979,1.615,3.438,2.5C46.146,99.584,47.917,100,50,100c2.084,0,3.854-0.416,5.312-1.25  c1.459-0.885,2.604-1.719,3.438-2.5c0.781-0.834,1.615-1.979,2.5-3.438c0.834-1.459,1.25-3.463,1.25-6.016V45.859l7.422,6.719  C72.631,55.025,75.209,56.25,77.656,56.25"/></svg></div>&nbsp;<div class="file_upload_label_'.$field['id'].' arf_file_upload_label">' . $file_upload_text .'</div>';

                            echo '<input type="file"  disabled="disabled"';
                            if (isset($field['required']) and $field['required']) {
                                $field_blank_val = isset($field['blank']) ? esc_attr($field['blank']) : '';
                                echo 'data-validation-required-message="' . $field_blank_val . '"';
                            }
                            $field_invalid = isset($field['invalid']) ? esc_attr($field['invalid']) : esc_html__('File is invalid', 'ARForms');
                            $field_invlaid_size_msg = isset($field['invalid_file_size']) ? esc_attr($field['invalid_file_size']) : esc_html__('Invalid File Size', 'ARForms');
                            echo ' class="file original arfeditor_file_original" name="file' . $field['id'] . '" id="field_' . $field['field_key'] . '" data-invalid-message="' . $field_invalid . '" data-size-invalid-message="' . $field_invlaid_size_msg . '" data-form-data-id="" data-form-id="' . $field['form_id'] . '" data-file-valid="true" ';

                            echo ' />';
                            echo '</div>';

                            echo '<label for="field_' . $field['field_key'] . '" class="arfajax-file-upload-drag arf_reply_drag_file_label label_' . $field['id'] . '" style="'.$draggable_upload_display.'"><span id="arf_file_drag_reply_' . $field['id'] . '" class="arf_file_drag_reply_container" data-id="field_' . $field['id'] . '">'.$arf_dragable_label.'</span></label>';

                            echo '</div>';

                            echo '</div>';
                            break;
                        case 'captcha':
                            global $arfsettings;
                            ?>

                            <img alt='' id="recaptcha_<?php echo $field['id']; ?>" src="<?php echo ARFURL ?>/images/<?php echo $arfsettings->re_theme ?>-captcha.png" alt="captcha" class="captcha_class" style="max-width:100%;"/>

                            <div id="custom-captcha_<?php echo $field['id']; ?>" class="alignleft custom_captcha_div captcha_class"></div>

                            <div style="clear:both"></div>    <?php if (empty($arfsettings->pubkey) && (isset($field['is_recaptcha']) && $field['is_recaptcha'] != 'custom-captcha')) { ?>                <div class="howto" id="setup_captcha_message" style="font-weight:bold;color:red;line-height:1;font-size:11px;"><?php echo addslashes(esc_html__('Please setup site key and private key in Global Settings otherwise recaptcha will not appear', 'ARForms')) ?></div>    <?php } ?>

                            <div class="howto" id="setup_general_message" style="font-weight:bold;color:red;line-height:1;font-size:11px;margin-top: 5px;"></div>

                            <input type="hidden" name="<?php echo $field_name ?>" value="1" id="field_' . $field['field_key'] . '"/>

                            <?php
                            break;
                        case 'html':
                            ?><p class="howto clear"><?php echo addslashes(esc_html__('Note: Set your custom html content', 'ARForms')) ?></p>
                            <?php
                            break;
                        case 'hidden':
                            ?>
                            <input type="text" name="<?php echo $field_name ?>" id="itemmeta_<?php echo $field['id']; ?>" onkeyup="arfchangeitemmeta('<?php echo $field['id']; ?>');" value="<?php echo isset($field['default_value']) ? esc_attr($field['default_value']) : ''; ?>"/>

                            <p class="howto clear"><?php echo addslashes(esc_html__('Note: This field will not show in the form. Enter the value to be hidden.', 'ARForms')) ?><br/>
                                [ARF_current_user_id], [ARF_current_user_name], [ARF_current_user_email], [ARF_current_date]</p>
                            <?php
                            break;
                        case 'section':
                            break;
                        case 'arfslider':
                            $field['slider_handle'] = isset($field['slider_handle']) ? $field['slider_handle'] : 'round';
                            $field['slider_value'] = isset($field['slider_value']) ? $field['slider_value'] : '10';
                            $field['arf_range_selector'] = isset($field['arf_range_selector']) ? $field['arf_range_selector'] : 0;
                            $field['minnum'] = isset($field['minnum']) ? $field['minnum'] : '0';
                            $field['maxnum'] = isset($field['maxnum']) ? $field['maxnum'] : '50';
                            $field['slider_step'] = isset($field['slider_step']) ? $field['slider_step'] : '1';

                            $slider_class = 'slider_class';
                            if (isset($field['arf_range_selector']) && $field['arf_range_selector'] == '1') {
                                $slider_class = 'slider_range_class';
                                if ($field['slider_handle'] == 'square') {
                                    $slider_class = 'square';
                                } else if ($field['slider_handle'] == 'triangle') {
                                    $slider_class = 'triangle';
                                }
                            } else {
                                $slider_class = 'slider_class';
                                if ($field['slider_handle'] == 'square') {
                                    $slider_class = 'square';
                                } else if ($field['slider_handle'] == 'triangle') {
                                    $slider_class = 'triangle';
                                }
                            }
                            $slider_value = $field['slider_value'];

                            if ($field['arf_range_selector'] == 1) {
                                /* arf_dev_flag - need to check this condition */
                                if (is_array($slider_value) && count($slider_value)>0) {
                                    $slider_value = json_encode(array((int)$slider_value[0], (int)$slider_value[1]));
                                }else{
                                    $slider_value = json_encode(array((int)$field['arf_range_minnum'], (int)$field['arf_range_maxnum']));
                                }
                            }
                            $slider_bg_color = isset($field['slider_bg_color2']) ? $field['slider_bg_color2'] : '#f5f5f5';
                            $slider_selection_color = isset($field['slider_bg_color']) ? $field['slider_bg_color'] : '#f9f9f9';
                            $slider_handle_color = isset($field['slider_handle_color']) ? $field['slider_handle_color'] : '#149bdf';
                             ?>
                            <div id="slider_sample_<?php echo $field['id']; ?>" class="<?php echo $slider_class; ?> arf_editor_slider_class noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr slider-track"></div>

                                 <input type='text' name='item_meta[<?php echo $field['id']; ?>]' id='arf_slider_<?php echo $field['id']; ?>' class='inplace_field arf_slider_input' data-slider-min='<?php echo $field['minnum'] ?>' data-slider-max='<?php echo $field['maxnum']; ?>' data-slider-step='<?php echo $field['slider_step'] ?>' data-slider-value='<?php echo $slider_value; ?>' />
                            <?php
                            break;
                        case 'scale':
                            $max_rating = ( isset($field['maxnum']) && $field['maxnum'] > 0) ? $field['maxnum'] : 5;
                            ?>
                            <div class='arf_star_rating_container arf_star_rating_container_<?php echo $field['id']; ?>'>
                                <?php
                                for ($r = $max_rating; $r >= 0; $r--) {
                                    ?>
                                    <input type='radio' name='field_options[star_rating_<?php echo $field['id']; ?>]' class='arf_star_rating_input' value="<?php echo $r; ?>" id="arf_star_rating_<?php echo $field['id'] . '_' . $r; ?>" <?php checked($field['default_value'], $r); ?>/>
                                    <?php
                                    if ($r == 0) {
                                        ?>
                                        <label class="arf_star_rating_label arf_star_rating_label_null" for="arf_star_rating_<?php echo $field['id'] . '_' . $r; ?>"></label>
                                        <?php
                                    } else {
                                        ?>
                                        <label class="arf_star_rating_label" for="arf_star_rating_<?php echo $field['id'] . '_' . $r; ?>">
                                            <svg viewBox="<?php echo "0 0 24 24";?>"><g><?php echo ARF_STAR_RATING_ICON; ?></g></svg>
                                        </label>
                                        <?php
                                    }
                                }

                                ?>
                            </div>
                            <?php
                            break;
                        default:
                            do_action('arfdisplayaddedfields', $field, $frm_css['arfinputstyle'], $newarr);
                            break;
                    }
                    if( 'material_outlined' == $frm_css['arfinputstyle'] || 'material' == $frm_css['arfinputstyle']){
                        echo "</div>";
                    }
                    $field_description = "";
                    if (isset($field['description'])) {
                        $field_description = $field['description'];
                    } else if (isset($field['field_options']['description']) && is_array($field['field_options'])) {
                        $field_description = $field['field_options']['description'];
                    } else if (isset($field['field_options']['description']) && !is_array($field['field_options'])) {
                        $tmp_field_options = json_decode($field['field_options'], true);
                        if (json_last_error() != JSON_ERROR_NONE) {
                            $tmp_field_options = maybe_unserialize($field['field_options']);
                        }
                        $field_description = isset($tmp_field_options['description']) ? $tmp_field_options['description'] : '';
                    }
                    ?>
                    <?php if (isset($field['tooltip_text']) && $field['tooltip_text'] != '' && $frm_css['arfinputstyle'] != 'material') { ?>
                        <div class="arftootltip_position arfhelptip tipso_style" id="tooltip_field_<?php echo $field['id']; ?>" data-title="<?php echo $field['tooltip_text']; ?>">
                            <span>
                                <svg width="30px" height="30px" viewBox="0 0 30 30">
                                <path xmlns="http://www.w3.org/2000/svg" fill="#BEC5D5" d="M9.609,0.33c-4.714,0-8.5,3.786-8.5,8.5s3.786,8.5,8.5,8.5s8.5-3.786,8.5-8.5S14.323,0.33,9.609,0.33z   M10.381,13.467c0,0.23-0.154,0.387-0.387,0.387H9.222c-0.231,0-0.387-0.156-0.387-0.387v-0.772c0-0.231,0.155-0.388,0.387-0.388  h0.772c0.232,0,0.387,0.156,0.387,0.388V13.467z M11.425,10.028c-0.541,0.463-0.929,0.772-1.044,1.197  c-0.039,0.193-0.193,0.309-0.387,0.309H9.222c-0.231,0-0.426-0.193-0.387-0.425c0.155-1.12,0.966-1.738,1.623-2.279  c0.697-0.541,1.082-0.889,1.082-1.546c0-1.082-0.85-1.932-1.932-1.932s-1.933,0.85-1.933,1.932c0,0.078,0,0.154,0,0.232  c0.04,0.192-0.077,0.386-0.27,0.425L6.672,8.173C6.44,8.25,6.208,8.096,6.169,7.864C6.131,7.67,6.131,7.478,6.131,7.284  c0-1.932,1.545-3.478,3.478-3.478c1.932,0,3.477,1.546,3.477,3.478C13.085,8.714,12.16,9.448,11.425,10.028L11.425,10.028z">                               
                                </path>
                                </svg>
                            </span>
                        </div>
                    <?php } ?>

                    <?php
                        $display_description_block = apply_filters( 'arf_hide_description_block', false, $field );
                        if ( $field['type'] != 'html' && $field['type'] != 'break' && !$display_description_block ) {
                    ?>
                        <div class="arf_field_description" id="field_description_<?php echo $field['id']; ?>"><?php echo isset($field['description']) ? $field['description'] : (isset($field['field_options']['description']) ? $field['field_options']['description'] : ''); ?></div>
                    <?php
                        }
                    ?>

                    <div class="help-block">

                    </div>
                    <?php
                    if( $field['type'] == 'phone' && isset($field['phonetype']) && $field['phonetype'] == 1 ){
                        

                        if( isset($phtypes) && count($phtypes) > 0 ){
                            echo "<input type='hidden' id='field_".$field['key']."_country_list' value='".json_encode($phtypes)."' />";
                            echo "<input type='hidden' id='field_".$field['key']."_default_country' value='". $default_country."' />";
                        }
                    }
                    ?>
                </div>

                <?php
                $field_opt_html = "";
                $field_custom_html = "";

                if (isset($field['field_options'])) {
                    if (!is_array($field['field_options'])) {
                        $field['field_options'] = json_decode($field['field_options'], true);
                        if (json_last_error() != JSON_ERROR_NONE) {
                            $field['field_options'] = maybe_unserialize($field['field_options']);
                        }
                    }
                    if (isset($field['field_options']['custom_html'])) {
                        $field_opt_html = htmlspecialchars($field['field_options']['custom_html']);
                    }
                }
                $field_opt_html_set = false;
                if (isset($field['field_options'])) {
                    if (!is_array($field['field_options'])) {
                        $field['field_options'] = json_decode($field['field_options'], true);
                        if (json_last_error() != JSON_ERROR_NONE) {
                            $field['field_options'] = maybe_unserialize($field['field_options']);
                        }
                    }
                    if (isset($field['field_options']['custom_html'])) {
                        unset($field['field_options']['custom_html']);
                        $field_opt_html_set = true;
                    }
                }

                if ($field_opt_html_set) {
                    $field['field_options']['custom_html'] = htmlspecialchars($field_opt_html);
                }

                $field_custom_html = isset($field['custom_html']) ? htmlspecialchars($field['custom_html']) : '';

                $field['custom_html'] = htmlspecialchars($field_custom_html);

                $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();

                $field_order = isset($field_opt_arr[$field['type']]) ? $field_opt_arr[$field['type']] : '';
                $new_field_obj = array();
                $field_type = $field['type'];

                $field_data_obj_array = $arformcontroller->arfObjtoArray($field_data_obj);

                $field_data_obj_array = apply_filters('arf_change_json_default_data_ouside', $field_data_obj_array);

                if( isset( $inside_repeatable_field ) && true == $inside_repeatable_field ){
                    $field_data_obj_array = apply_filters('arf_add_parent_data_to_field', $field_data_obj_array, $field['type'], $check_field['id'] );
                }

                if( isset( $inside_section_field ) && true == $inside_section_field ){
                    $field_data_obj_array = apply_filters( 'arf_add_parent_data_to_field', $field_data_obj_array, $field['type'], $check_field['id'] );
                }



                $field_data_obj_array = json_encode($field_data_obj_array);

                $field_data_obj = json_decode($field_data_obj_array);
                if( isset($field_data_obj->field_data) && isset($field_data_obj->field_data->$field_type) ){
                    foreach ($field_data_obj->field_data->$field_type as $key => $val) {
                        $new_field_obj[$key] = (isset($field[$key]) && $field[$key] != '' ) ? $field[$key] : (isset($unserialize_field_optins[$key]) ? $unserialize_field_optins[$key] : '');
                        if ($key == 'options') {
                            $new_field_obj[$key] = $field[$key];
                        }
                        if (isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] != 'edit') {
                            if ($key == 'placeholdertext') {
                                $new_field_obj[$key] = $placeholder_text;
                            }
                        }
                    }
                }

                $new_field_obj['default_value'] = isset($field['default_value']) ? $field['default_value'] : (isset($field['field_options']['default_value']) ? $field['field_options']['default_value'] : '');

                if (isset($new_field_obj['page_no']) && ($new_field_obj['page_no'] == '' || $new_field_obj['page_no'] < 1)) {
                    $new_field_obj['page_no'] = 1;
                }

                if (isset($new_field_obj['locale'])) {
                    $new_field_obj['locale'] = $new_field_obj['locale'] != "" ? $new_field_obj['locale'] : 'en';
                }
                $new_field_obj['image_position_from'] = ( isset($new_field_obj['image_position_from']) && $new_field_obj['image_position_from'] != '' ) ? $new_field_obj['image_position_from'] : 'top_left';

                $new_field_obj = $arformcontroller->arf_html_entity_decode($new_field_obj);
                
                ?>
                <input type="hidden" name="arf_field_data_<?php echo $field['id']; ?>" id="arf_field_data_<?php echo $field['id']; ?>" class="arf_field_data_hidden" value="<?php echo htmlspecialchars(json_encode($new_field_obj)); ?>" data-field_options='<?php echo json_encode($field_order); ?>' />
                <div class="arf_field_option_model arf_field_option_model_cloned" data-field_id="<?php echo $field['id']; ?>">
                    <div class="arf_field_option_model_header"><?php echo addslashes(esc_html__('Field Options', 'ARForms')); ?>&nbsp;<span class="arf_pre_populated_field_type" id="{arf_field_type}">[Field Type : [arf_field_type]]</span>&nbsp;<span class="arf_pre_populated_field_id" id="{arf_field_id}">[Field ID:[arf_field_id]]</span></div>
                    <div class="arf_field_option_model_container">
                        <div class="arf_field_option_content_row">
                        </div>
                    </div>
                    <div class="arf_field_option_model_footer">
                        <button type="button" class="arf_field_option_close_button" onClick="arf_close_field_option_popup(<?php echo $field['id']; ?>);"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
                        <button type="button" class="arf_field_option_submit_button" data-field_id="<?php echo $field['id']; ?>"><?php echo esc_html__('OK', 'ARForms'); ?></button>
                    </div>
                </div>
                <?php
                if (in_array($field['type'], $fields_for_edit_options)) {
                    ?>
                    <div class="arf_field_values_model" id="arf_field_values_model_skeleton_<?php echo $field['id']; ?>">
                        <div class="arf_field_values_model_header"><?php echo addslashes(esc_html__('Edit Options', 'ARForms')); ?></div>
                        <div class="arf_field_values_model_container">
                            <div class="arf_field_values_content_row">
                                <div class="arf_field_values_content_loader">
                                    <svg version="1.1" id="arf_field_values_loader" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="48px" height="48px" viewBox="0 0 26.349 26.35" style="enable-background:new 0 0 26.349 26.35;" fill="#03A9F4" xml:space="preserve" ><g><g><circle cx="13.792" cy="3.082" r="3.082" /><circle cx="13.792" cy="24.501" r="1.849"/><circle cx="6.219" cy="6.218" r="2.774"/><circle cx="21.365" cy="21.363" r="1.541"/><circle cx="3.082" cy="13.792" r="2.465"/><circle cx="24.501" cy="13.791" r="1.232"/><path d="M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z"/><circle cx="21.364" cy="6.218" r="0.924"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
                                </div>
                            </div>
                        </div>
                        <div class="arf_field_values_model_footer">
                            <button type="button" class="arf_field_values_close_button"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
                            <button type="button" class="arf_field_values_submit_button" data-field-id="<?php echo $field['id']; ?>"><?php echo esc_html__('OK', 'ARForms'); ?></button>
                        </div>
                    </div>
                    <?php
                }
                do_action( 'arf_render_field_extra_model_outside', $field );
                ?>
            </div>
        </div>
        <?php if ($class == 'arf_1col' || $class == 'arf_2col' || $class == 'arf_3col' || $class == 'arf_4col' || $class == 'arf_5col' || $class == 'arf_6col') { ?>
        </div>
        <?php
        $index_arf_fields++;
    }

    unset($display);
    if ($field['type'] == 'imagecontrol') {
        $image_page = (isset($field['page_no']) && $field['page_no'] != '' ) ? $field['page_no'] : '1';
        $left_pos = (isset($field['image_left']) && $field['image_left'] != '' ) ? $field['image_left'] : '0px';
        $top_pos = (isset($field['editor_image_top']) && $field['editor_image_top'] != '' ) ? $field['editor_image_top'] : '0px';
        $position_from = (isset($field['image_position_from']) && $field['image_position_from'] != '' ) ? $field['image_position_from'] : 'top_left';
        $img_width = (isset($field['image_width']) && $field['image_width'] != '') ? $field['image_width'] : '100px';
        $img_height = (isset($field['image_height']) && $field['image_height'] != '' ) ? $field['image_height'] : '100px';
        $image_center = (isset($field['image_center']) && $field['image_center'] != '') ? $field['image_center'] : 'No';
        $div_style = "";
        $img_style = "";
        $div_style_extended = "";
        $img_url = (isset($field['image_url']) && $field['image_url'] != '' ) ? $field['image_url'] : ARFURL . '/images/no-image.png';
        if ($image_center == 'No') {
            switch ($position_from) {
                case 'top_left':
                    $div_style_extended = "left:{$left_pos};top:{$top_pos};";
                    break;
                case 'top_right':
                    $div_style_extended = "right:{$left_pos};top:{$top_pos};";
                    break;
                case 'bottom_left':
                    $div_style_extended = "left:{$left_pos};bottom:{$top_pos};";
                    break;
                case 'bottom_right':
                    $div_style_extended = "right:{$left_pos};bottom:{$top_pos};";
                    break;
                default:
                    $div_style_extended = "left:{$left_pos};top:{$top_pos};";
                    break;
            }
            $div_style = $div_style_extended . "width:{$img_width};height:{$img_height};position:absolute;";
            $img_style = "width:100%;height:100%;";
        } else {
            $div_style = "float:none;margin:0 auto;width:{$img_width};height:{$img_height};text-align:center;top:{$top_pos};";
            $img_style = "float:none;width:{$img_width};height:{$img_width};margin:0 auto;";
        }
        ?>
        <div id="arf_imagefield_<?php echo $field['id']; ?>" class="arf_image_field" style="<?php echo $div_style; ?>">
            <div class="arf_field_icon_for_imagecontrol">
                <div class="arf_field_option_icon">
                    <a class="arf_field_option_input" href="javascript:arfduplicatefield('<?php echo $id; ?>','<?php echo $field['type']; ?>','<?php echo $field['id'] ?>','<?php echo $field['id'] ?>');">
                        <svg id="duplicate" height="18" width="18"><g><path xmlns="http://www.w3.org/2000/svg" fill="#ffffff" d="M9.465,0.85h-6.72c-0.691,0-1.257,0.565-1.257,1.256v8.733H3.47V2.827h5.995V0.85z M13.227,3.833H5.728  c-0.691,0-1.258,0.565-1.258,1.257v11.509c0,0.691,0.566,1.257,1.258,1.257h7.499c0.691,0,1.257-0.565,1.257-1.257V5.089  C14.484,4.398,13.918,3.833,13.227,3.833z M12.465,15.869H6.469V5.837h5.996V15.869z"/></g></svg>
                    </a>
                </div>
                <div class="arf_field_option_icon">
                    <a class="arf_field_option_input" data-toggle="arfmodal" href="#delete_field_message_<?php echo $field['id']; ?>" onClick="arfchangedeletemodalwidth('arfimagecontrol', '<?php echo $field['id']; ?>');">
                        <svg id="delete" height="18" width="18"><g><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M16.939,5.845h-1.415V17.3c0,0.292-0.236,0.529-0.529,0.529H4.055  c-0.292,0-0.529-0.237-0.529-0.529V5.845H2.018c-0.292,0-0.529-0.739-0.529-1.031s0.237-0.982,0.529-0.982h2.509V1.379  c0-0.293,0.237-0.529,0.529-0.529h8.954c0.293,0,0.529,0.236,0.529,0.529v2.452h2.399c0.292,0,0.529,0.69,0.529,0.982  S17.231,5.845,16.939,5.845z M12.533,2.811H6.517v1.011h6.016V2.811z M13.541,5.845l-0.277-0.031L5.788,5.845H5.534v10.001h8.007  V5.845z M8.525,13.849H7.534v-6.08h0.991V13.849z M11.525,13.849h-0.991v-6.08h0.991V13.849z" /></g></svg>
                    </a>
                </div>
                <?php
                echo $arfieldcontroller->arf_get_field_control_icons('options', '', $field['id'], 0, $field['type']);
                echo $arfieldcontroller->arf_get_field_control_icons('move');
                ?>
            </div>
            <img src="<?php echo $img_url; ?>" style="<?php echo $img_style; ?>" />
        </div>
        <div id="arf_field_img_control_options_<?php echo $field['id']; ?>" class="arf_field_option_model arf_field_option_model_cloned">
            <div class="arf_field_option_model_header"><?php echo addslashes(esc_html__('Field Options', 'ARForms')); ?></div>
            <div class="arf_field_option_model_container">
                <div class="arf_field_option_content_row">
                </div>
            </div>
            <div class="arf_field_option_model_footer">
                <button type="button" class="arf_field_option_close_button" onClick="arf_close_field_option_popup(<?php echo $field['id']; ?>);"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
                <button type="button" class="arf_field_option_submit_button" data-field_id="<?php echo $field['id']; ?>"><?php echo esc_html__('OK', 'ARForms'); ?></button>
            </div>
        </div>
        <?php
    }
} else {
    if (!empty($confirm_field_options)) {
        if ($define_classes == 'arf_1' || $define_classes == 'arf_1col' || $define_classes == 'arf21colclass' || $define_classes == 'arf31colclass' || $define_classes == 'arf41colclass' || $define_classes == 'arf51colclass' || $define_classes == 'arf61colclass') {
            ?>
            <div class="arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default arf1columns <?php echo $multicolclass; ?>" data-id="arf_editor_main_row_<?php echo $index_arf_fields; ?>">
                <?php echo $multicol_html; ?>
                <?php
        }

        $confirm_enable_arf_prefix = $confirm_field_options['enable_arf_prefix'];
        $confirm_arf_prefix_icon = $confirm_field_options['arf_prefix_icon'];
        $confirm_enable_arf_suffix = $confirm_field_options['enable_arf_suffix'];
        $confirm_arf_suffix_icon = $confirm_field_options['arf_suffix_icon'];


        $prefix_suffix_wrapper_start = "";
        $prefix_suffix_wrapper_end = "";
        $has_prefix_suffix = false;
        $prefix_suffix_class = "";
        $has_prefix = false;
        $has_suffix = false;
        $arf_prefix_icon = "";
        $arf_suffix_icon = "";
        $prefix_suffix_style_start = "<style id='arf_field_prefix_suffix_style_".$field."' type='text/css'>";
        $prefix_suffix_style = "";
        $prefix_suffix_style_end = "</style>";

        $arf_is_phone_with_flag = false;
	
        if( isset($field['type']) && isset($field['phonetype']) && $field['type'] == 'phone' && $field['phonetype'] == 1 ){
            $arf_is_phone_with_flag = true;
        }

        if (isset($confirm_enable_arf_prefix) && $confirm_enable_arf_prefix == 1 && $arf_is_phone_with_flag == false ) {
            $has_prefix_suffix = true;
            $has_prefix = true;
            $arf_prefix_icon = "<span class='arf_editor_prefix_icon'><i class='".$confirm_arf_prefix_icon."'></i></span>";
        }

        if (isset($confirm_enable_arf_suffix) && $confirm_enable_arf_suffix == 1) {
            $has_prefix_suffix = true;
            $has_suffix = true;
            $arf_suffix_icon = "<span class='arf_editor_suffix_icon'><i class='".$confirm_arf_suffix_icon."'></i></span>";
        }


        if ($has_prefix == true && $has_suffix == false) {
            $prefix_suffix_class = " arf_prefix_only ";
        } else if ($has_prefix == false && $has_suffix == true) {
            $prefix_suffix_class = " arf_suffix_only ";
        } else if ($has_prefix == true && $has_suffix == true) {
            $prefix_suffix_class = " arf_both_pre_suffix ";
        }

        if (isset($has_prefix_suffix) && $has_prefix_suffix == true) {
            $prefix_suffix_wrapper_start = "<div id='arf_editor_prefix_suffix_container_" . $field . "' class='arf_editor_prefix_suffix_wrapper " . trim($prefix_suffix_class) . "'>";
            $prefix_suffix_wrapper_end = "</div>";
        }

        if ($frm_css['arfinputstyle'] == 'material' || $frm_css['arfinputstyle'] == 'material_outlined') {
            $prefix_suffix_wrapper_start = $prefix_suffix_wrapper_end = "";
        }

        if ($confirm_field_options['type'] == 'email') {
           
            $confirm_email_label = $confirm_field_options['confirm_email_label'];
            $confirm_email_placeholder = $confirm_field_options['confirm_email_placeholder'];

                ?>
            <div id="arfmainfieldid_<?php echo $field; ?>" class="sortable_inner_wrapper arf_confirm_field ui-droppable ui-sortable"  inner_class="<?php echo $define_classes; ?>" <?php echo $sortable_inner_field_style; ?>>
                <div id="arf_field_<?php echo $field; ?>" class="arfformfield control-group arfmainformfield <?php echo isset($newarr['position']) ? $newarr['position'] . '_container' : 'top_container'; ?> arf_field arf_confirm_field" style="">
                    <?php if( ('material_outlined' != $frm_css['arfinputstyle']) && ('material' != $frm_css['arfinputstyle']) ){ ?>
                        <div class="fieldname-row" style="display : block;">
                            <div class="fieldname">
                                <label class="arf_main_label <?php echo $arf_main_label_cls; ?>" id="field_<?php echo $field; ?>">
                                    <span class="arfeditorfieldopt_label arf_edit_in_place"><?php echo $confirm_email_label; ?></span>
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="arf_fieldiconbox" data-field_id=<?php echo $field; ?>>
                        <div class='arf_field_option_icon'><a class='arf_field_option_input'><svg id='moveing' height='20' width='21'><g><?php echo ARF_CUSTOM_MOVING_ICON; ?></g></svg></a></div>
                    </div>
                    <div class="controls" style="<?php echo (isset($confirm_field_options['field_width']) && $confirm_field_options['field_width'] != '') ? 'width:'.$confirm_field_options['field_width'].'px;' : ''; ?>" >
                    
                    <?php
                
                        if( 'material_outlined' == $frm_css['arfinputstyle'] ){
                            
                            $material_outlined_cls = '';
                            if( !empty( $confirm_field_options['enable_arf_prefix'] ) || !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                $material_outlined_cls = 'arf_material_outline_container_with_icons';
                            }
                            if( !empty( $confirm_field_options['enable_arf_prefix'] ) && empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                $material_outlined_cls .= ' arf_only_leading_icon ';
                            }
    
                            if( empty( $confirm_field_options['enable_arf_prefix'] ) && !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                $material_outlined_cls .= ' arf_only_trailing_icon ';
                            }
                            echo "<div class='arf_material_outline_container ".$material_outlined_cls." '>";
                            if( !empty( $confirm_field_options['enable_arf_prefix'] ) ){
                                echo '<i class="arf_leading_icon ' . $confirm_field_options['arf_prefix_icon'] . '"></i>';
                            }
                            if( !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                echo '<i class="arf_trailing_icon ' . $confirm_field_options['arf_suffix_icon'] . '"></i>';
                            }
                        }
                        if( 'material' == $frm_css['arfinputstyle'] ){
                            
                            $material_outlined_cls = '';
                            if( !empty( $confirm_field_options['enable_arf_prefix'] ) || !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                $material_outlined_cls = 'arf_material_theme_container_with_icons';
                            }
                            if( !empty( $confirm_field_options['enable_arf_prefix'] ) && empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                $material_outlined_cls .= ' arf_only_leading_icon ';
                            }
    
                            if( empty( $confirm_field_options['enable_arf_prefix'] ) && !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                $material_outlined_cls .= ' arf_only_trailing_icon ';
                            }
                            echo "<div class='arf_material_theme_container ".$material_outlined_cls." '>";
                            if( !empty( $confirm_field_options['enable_arf_prefix'] ) ){
                                echo '<i class="arf_leading_icon ' . $confirm_field_options['arf_prefix_icon'] . '"></i>';
                            }
                            if( !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                echo '<i class="arf_trailing_icon ' . $confirm_field_options['arf_suffix_icon'] . '"></i>';
                            }
                        }
                        if( $frm_css['arfinputstyle'] != 'material' && $frm_css['arfinputstyle'] != 'material_outlined'){
                                echo $prefix_suffix_style_start;
                                echo $prefix_suffix_style;
                                echo $prefix_suffix_style_end;
                                echo $prefix_suffix_wrapper_start;
                                echo $arf_prefix_icon;
                        }
                    ?>
                        <input id="field_confiorm_email" name="confirm_email" <?php echo ($confirm_email_placeholder != '') ? "placeholder=\"{$confirm_email_placeholder}\"" : ''; ?> type="text" class=" " style="float: left;" />
                    <?php
                        if( $frm_css['arfinputstyle'] != 'material' && $frm_css['arfinputstyle'] != 'material_outlined' ){
                            echo $arf_suffix_icon;
                            echo $prefix_suffix_wrapper_end;
                        }
                        if( 'material_outlined' == $frm_css['arfinputstyle'] ){
                                echo '<div class="arf_material_outliner">';
                                    echo '<div class="arf_material_outliner_prefix"></div>';
                                    echo '<div class="arf_material_outliner_notch">';
                                        echo '<label class="arf_main_label '.$arf_main_label_cls.'" id="field_'.$field.'">'.$confirm_email_label.'</label>';
                                    echo '</div>';
                                    echo '<div class="arf_material_outliner_suffix"></div>';
                                echo '</div>';
                            echo '</div>';
                        }
                        if( 'material' == $frm_css['arfinputstyle'] ){
                                echo '<div class="arf_material_standard">';
                                    echo '<div class="arf_material_theme_prefix"></div>';
                                    echo '<div class="arf_material_theme_notch">';
                                        echo '<label class="arf_main_label '.$arf_main_label_cls.'" id="field_'.$field.'">'.$confirm_email_label.'</label>';
                                    echo '</div>';
                                    echo '<div class="arf_material_theme_suffix"></div>';
                                echo '</div>';
                            echo '</div>';
                        }
                    ?>
                    </div>
                </div>
            </div>
            
            <?php
        } 
        else 
        {
            $confirm_password_label = $confirm_field_options['confirm_password_label'];
            $password_placeholder = $confirm_field_options['password_placeholder'];?>
        
            <div id="arfmainfieldid_<?php echo $field; ?>" class="sortable_inner_wrapper arf_confirm_field ui-droppable ui-sortable"  inner_class="<?php echo $define_classes; ?>" <?php echo $sortable_inner_field_style; ?>>
                <div id="arf_field_<?php echo $field; ?>" class="arfformfield control-group arfmainformfield arf_field arf_confirm_field <?php echo isset($newarr['position']) ? $newarr['position'] . '_container' : 'top_container'; ?>" style="">
                    <?php if( ('material_outlined' != $frm_css['arfinputstyle']) && ('material' != $frm_css['arfinputstyle']) ){ ?>
                        <div class="fieldname-row" style="display : block;">
                            <div class="fieldname">
                                <label class="arf_main_label  <?php echo $arf_main_label_cls; ?>" id="field_<?php echo $field; ?>"><span class="arfeditorfieldopt_label arf_edit_in_place"><?php echo $confirm_password_label; ?></span>
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="arf_fieldiconbox" data-field_id=<?php echo $field; ?>>
                        <div class='arf_field_option_icon'><a class='arf_field_option_input'><svg id='moveing' height='20' width='21'><g><?php echo ARF_CUSTOM_MOVING_ICON; ?></g></svg></a></div>
                    </div>
                    <div class="controls"   style="<?php echo (isset($confirm_field_options['field_width']) && $confirm_field_options['field_width'] != '') ? 'width:'.$confirm_field_options['field_width'].'px;' : ''; ?>" >
                        <?php
                            if( 'material_outlined' == $frm_css['arfinputstyle'] ){
                                
                                $material_outlined_cls = '';
                                if( !empty( $confirm_field_options['enable_arf_prefix'] ) || !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                    $material_outlined_cls = 'arf_material_outline_container_with_icons';
                                }
                                if( !empty( $confirm_field_options['enable_arf_prefix'] ) && empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                    $material_outlined_cls .= ' arf_only_leading_icon ';
                                }
        
                                if( empty( $confirm_field_options['enable_arf_prefix'] ) && !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                    $material_outlined_cls .= ' arf_only_trailing_icon ';
                                }
                                echo "<div class='arf_material_outline_container ".$material_outlined_cls." '>";
                                if( !empty( $confirm_field_options['enable_arf_prefix'] ) ){
                                    echo '<i class="arf_leading_icon ' . $confirm_field_options['arf_prefix_icon'] . '"></i>';
                                }
                                if( !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                    echo '<i class="arf_trailing_icon ' . $confirm_field_options['arf_suffix_icon'] . '"></i>';
                                }
                            }
                            else if( 'material' == $frm_css['arfinputstyle'] ){
                                
                                $material_standard_cls = '';
                                if( !empty( $confirm_field_options['enable_arf_prefix'] ) || !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                    $material_standard_cls = 'arf_material_theme_container_with_icons';
                                }
                                if( !empty( $confirm_field_options['enable_arf_prefix'] ) && empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                    $material_standard_cls .= ' arf_only_leading_icon ';
                                }
        
                                if( empty( $confirm_field_options['enable_arf_prefix'] ) && !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                    $material_standard_cls .= ' arf_only_trailing_icon ';
                                }
                                echo "<div class='arf_material_theme_container ".$material_standard_cls." '>";
                                if( !empty( $confirm_field_options['enable_arf_prefix'] ) ){
                                    echo '<i class="arf_leading_icon ' . $confirm_field_options['arf_prefix_icon'] . '"></i>';
                                }
                                if( !empty( $confirm_field_options['enable_arf_suffix'] ) ){
                                    echo '<i class="arf_trailing_icon ' . $confirm_field_options['arf_suffix_icon'] . '"></i>';
                                }
                            }
                            if( $frm_css['arfinputstyle'] != 'material' && $frm_css['arfinputstyle'] != 'material_outlined' ){
                                echo $prefix_suffix_style_start;
                                echo $prefix_suffix_style;
                                echo $prefix_suffix_style_end;
                                echo $prefix_suffix_wrapper_start;
                                echo $arf_prefix_icon;
                            }
                        ?>
                        <input id="field_confiorm_password" name="confirm_password" <?php echo ($password_placeholder != '') ? "placeholder=\"{$password_placeholder}\"" : ''; ?> type="password" class=" " style="float: left;" />
                        <?php
                            if( $frm_css['arfinputstyle'] != 'material' && $frm_css['arfinputstyle'] != 'material_outlined' ){
                                echo $arf_suffix_icon;
                                echo $prefix_suffix_wrapper_end;
                            }
                            if( 'material_outlined' == $frm_css['arfinputstyle'] ){
                                echo '<div class="arf_material_outliner">';
                                    echo '<div class="arf_material_outliner_prefix"></div>';
                                    echo '<div class="arf_material_outliner_notch">';
                                        echo '<label class="arf_main_label '.$arf_main_label_cls.'" id="field_'.$field.'">'.$confirm_password_label.'</label>';
                                    echo '</div>';
                                    echo '<div class="arf_material_outliner_suffix"></div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            if( 'material' == $frm_css['arfinputstyle'] ){
                                echo '<div class="arf_material_standard">';
                                    echo '<div class="arf_material_theme_prefix"></div>';
                                    echo '<div class="arf_material_theme_notch">';
                                        echo '<label class="arf_main_label '.$arf_main_label_cls.'" id="field_'.$field.'">'.$confirm_password_label.'</label>';
                                    echo '</div>';
                                    echo '<div class="arf_material_theme_suffix"></div>';
                                echo '</div>';
                                echo '</div>';
                            }
                        ?>
                    </div>
                </div>
            </div>
                
            
        <?php
        }

        if ($define_classes == 'arf_1' || $define_classes == 'arf_1col' || $define_classes == 'arf_2col' || $define_classes == 'arf_3col' || $define_classes == 'arf_4col' || $define_classes == 'arf_5col' || $define_classes == 'arf_6col') {
            echo '</div>';
            $index_arf_fields++;
        }
    } else {

        if ($define_classes == 'arf_1' || $define_classes == 'arf_1col' || $define_classes == 'arf21colclass' || $define_classes == 'arf31colclass' || $define_classes == 'arf41colclass' || $define_classes == 'arf51colclass' || $define_classes == 'arf61colclass') {    
            ?>
            <div class="arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default arf1columns <?php echo $multicolclass; ?>" data-id="arf_editor_main_row_<?php echo $index_arf_fields; ?>">
                <?php echo $multicol_html; ?>
                <?php
            }
            ?>
            <div class='sortable_inner_wrapper' inner_class='<?php echo $define_classes;?>' <?php echo $sortable_inner_field_style;?>></div>
            <?php 

            if ($define_classes == 'arf_1' || $define_classes == 'arf_1col' || $define_classes == 'arf_2col' || $define_classes == 'arf_3col' || $define_classes == 'arf_4col' || $define_classes == 'arf_5col' || $define_classes == 'arf_6col') {
            ?>
            </div>
            <?php
                $index_arf_fields++;
            }
        }
    }
?>