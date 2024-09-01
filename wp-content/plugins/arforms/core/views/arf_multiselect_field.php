<?php
define('ARF_MULTISELECT', 'arf_multiselect');

global $arf_multiselect_field_class_name, $arf_multiselect_new_field_data, $arf_multiselect_field_image_path, $arf_font_awesome_loaded;

$arf_multiselect_field_class_name = array(ARF_MULTISELECT => 'red');
$arf_multiselect_new_field_data = array(ARF_MULTISELECT => addslashes(esc_html__('Multi Select', 'ARForms')));
$arf_multiselect_total_class = array();
$arf_multiselect_field_class = new arf_multiselect_field();

global $arf_multiline_loaded;
$arf_multiline_loaded = array();

class arf_multiselect_field {
	
	function __construct() {
		add_filter( 'arfaavailablefields', array( $this, 'arf_add_multiselect_field_element_list'), 11);

		add_filter('arf_all_field_css_class_for_editor', array($this, 'arf_get_multiselect_field_class'), 11, 3);
		
		add_filter('arfavailablefieldsbasicoptions', array($this, 'add_availablefieldsbasicoptions'), 11, 3);

        add_filter('form_fields', array($this, 'add_multiselect_field_to_frontend'), 12, 12);

        add_filter('arf_before_createfield', array($this, 'arf_multiselect_createfield'), 10, 2);    // Before Create new filed
        
        add_filter('arf_add_more_field_options_outside',array($this,'arf_add_multiselect_default_field_options'),10,2);

        add_filter('arf_field_values_options_outside',array($this,'arf_field_values_options_outside_function'),10);
        
        add_filter('arf_new_field_array_filter_outside', array($this, 'arf_add_multiselect_field_in_array'),10,4);
       
       	add_filter('arf_new_field_array_materialize_filter_outside', array($this, 'arf_add_multiselect_field_in_array_materialize'),10,4);

        add_filter( 'arf_new_field_array_materialize_outlined_filter_outside', array( $this, 'arf_add_multiselect_field_in_array_materialize_outlined'), 10, 4);
        
        add_filter('arf_bootstraped_field_from_outside',array($this,'arf_bootstraped_field_from_outside_function'),10);

        add_action('arf_load_bootstrap_js_from_outside',array($this,'arf_load_bootstrap_js_from_outside_function'),10,1);
        
        add_filter('arf_installed_fields_outside',array($this,'arf_install_multiselect_field'),10);

        add_filter('arf_onchange_only_click_event_outside',array($this,'arf_multiselect_onchange_type_func'),11);

        add_filter('arf_positioned_field_options_icon',array($this,'arf_positioned_field_options_icon_for_multiselect'),10,2);

        add_filter('arf_default_value_array_field_type', array($this,'arf_default_value_array_field_type_multiselect'),10);

        add_filter('arf_field_type_label_filter', array( $this, 'arf_add_multiselect_label') );

        add_filter('arform_input_fields', array( $this, 'arf_add_multiselect_for_input_field') );

        add_filter('arf_manage_field_element_order_outside', array( $this, 'arf_multi_select_in_order_array') );

        add_filter('arf_migrate_field_type_from_outside', array( $this, 'arf_add_multiselect_for_type_conversion' ) );
	}

    function arf_add_multiselect_for_type_conversion( $field_types ){
    	$field_types['arf_multiselect'] = esc_html__('Multi Select','ARForms');
        return $field_types;
    }

    function arf_multi_select_in_order_array( $fields ){
        array_push( $fields, 'arf_multiselect' );
        return $fields;
    }

    function arf_add_multiselect_for_input_field( $inputFields ){

        array_push($inputFields, 'arf_multiselect');
        return $inputFields;
    }

    function arf_add_multiselect_label( $field_type_label_array ){
        $field_type_label_array['arf_multiselect'] = esc_html__('Multi Select','ARForms');
        return $field_type_label_array;
    }

    function arf_bootstraped_field_from_outside_function($bootstraped_field){
        $bootstraped_field[count($bootstraped_field) + 1] = 'arf_multiselect';
        return $bootstraped_field;
    }

	function arf_add_multiselect_field_element_list( $fields ){

        $fields['arf_multiselect'] = array(
            'icon' => '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60.123 60.123" style="enable-background:new 0 0 60.123 60.123;" xml:space="preserve">
					<g fill="#4E5462">
						<path d="M57.124,51.893H16.92c-1.657,0-3-1.343-3-3s1.343-3,3-3h40.203c1.657,0,3,1.343,3,3S58.781,51.893,57.124,51.893z"/>
						<path d="M57.124,33.062H16.92c-1.657,0-3-1.343-3-3s1.343-3,3-3h40.203c1.657,0,3,1.343,3,3
							C60.124,31.719,58.781,33.062,57.124,33.062z"/>
						<path d="M57.124,14.231H16.92c-1.657,0-3-1.343-3-3s1.343-3,3-3h40.203c1.657,0,3,1.343,3,3S58.781,14.231,57.124,14.231z"/>
						<circle cx="4.029" cy="11.463" r="4.029"/>
						<circle cx="4.029" cy="30.062" r="4.029"/>
						<circle cx="4.029" cy="48.661" r="4.029"/>
					</g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>',
            'label' => addslashes( esc_html__('Multi Select', 'ARForms') )
        );

        return $fields;
    }

    function arf_default_value_array_field_type_multiselect($field_types){
        array_push($field_types, 'arf_multiselect');
        return $field_types;
    }

    function arf_positioned_field_options_icon_for_multiselect($positioned_icon, $field_icons){
    	
        $positioned_icon['arf_multiselect'] = "{$field_icons['arf_edit_option_icon']}{$field_icons['field_require_icon']}".str_replace('{arf_field_type}', 'arf_multiselect', $field_icons['arf_field_duplicate_icon'])."{$field_icons['field_delete_icon']}".str_replace('{arf_field_type}', 'arf_multiselect',$field_icons['field_option_icon'])."{$field_icons['arf_field_move_icon']}";
        return $positioned_icon;
    }

    function arf_get_multiselect_field_class($class) {
        global $arf_switch_field_class_name, $arf_switch_total_class;
        $as_class = array_merge($class, $arf_multiselect_field_class_name);
        $arf_multiselect_total_class = count($as_class);
        return $as_class;
    }

    function add_availablefieldsbasicoptions($basic_option){
    	 $multiselect_filed_option = array(
            'arf_multiselect' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'requiredmsg' => 4,
                'min_opt_selected' => 5,
                'min_opt_selected_msg' => 6,
                'max_opt_selected' => 7,
                'max_opt_selected_msg' => 8,
                'customwidth' => 9,
            )
        );
        return array_merge($basic_option, $multiselect_filed_option);
    }

    function arf_multiselect_onchange_type_func($field_types){
        array_push($field_types, 'arf_multiselect');
        return $field_types;
    }

    function add_multiselect_field_to_frontend($return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tootip, $field_description, $res_data, $inputStyle,$arf_main_label,$get_onchage_func_data, $arf_on_change_function_array) {

        if ($field['type'] != 'arf_multiselect') {
            return $return_string;
        }

        global $style_settings, $arfsettings, $arfeditingentry, $arffield, $arfieldcontroller, $arfieldhelper, $arfversion, $arf_form_all_footer_js, $wpdb, $MdlDb, $armainhelper, $maincontroller;
        $entry_id = $arfeditingentry;
        $field_width = '';
        if (isset($field['field_width']) && $field['field_width'] != '') {
            $field_width = 'style="width:' . $field['field_width'] . 'px;"';
        }

        $arf_input_field_html = '';
        $arf_input_field_html .= $arfieldcontroller->input_fieldhtml($field, false);
        $arf_input_field_html .= $arfieldcontroller->input_html($field, false);

        $form_data = new stdClass();
        $form_data->id = $form->id;
        $form_data->form_key = $form->form_key;
        $form_data->options = maybe_serialize($form->options);

        $arf_save_form_data = "";

        if( isset( $form->options['arf_form_save_database'] ) && 1 == $form->options['arf_form_save_database'] ){
            $arf_save_form_data = ' data-save="true" ';
        }

        if ($res_data == '') {
            $res_data = $wpdb->get_results($wpdb->prepare("SELECT id, type, field_options,conditional_logic FROM " . $MdlDb->fields . " WHERE form_id = %d ORDER BY id", $form->id));
        }

        if ($field['type'] == 'arf_multiselect') {

            $field_tooltip_class = "";
            $field_tootip_material = "";
            $field_tootip_standard =  "";
            if($field_tootip!='')
            {
                if($inputStyle=="material")
                {
                    $field_tootip_material = $field_tootip;
                    $field_tooltip_class = " arfhelptip";
                } else {
                    $field_tootip_standard = $field_tootip;
                }

            }
            
	        $return_string .='<div class=" sltstandard_front controls '.$field_tooltip_class.'" '. $field_width .' '. $field_tootip_material.'>';
	        if( $inputStyle == 'material' ){
	            $return_string .= $arf_main_label;   
	        }

	        $arfdefault_selected_val = (isset($field['separate_value']) && $field['separate_value']) ? $field['default_value'] : (isset($field['value']) ? $field['value'] : '');

	        if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){

	            $arfdefault_selected_val = $arf_arr_preset_data[$field['id']];
	        }

	        if (isset($field['set_field_value'])) {
	            $arfdefault_selected_val = $field['set_field_value'];
	        }
	       
            if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field, $get_onchage_func_data, $arf_data_uniq_id);
            }else {
                $field['options'] = $arfieldhelper->changeoptionorder($field);

                $multi_sel_title = esc_html__( 'Please Select', 'ARForms');

                $arf_set_default_label = false;

                if( 'material_outlined' == $inputStyle ){
                    $arf_set_default_label = true;
                }

                if( !empty( $field['options'] ) ){
                    $opt_counter = 0;
                    $min_field = 0;
                    foreach( $field['options'] as $foptk => $foptv ){
                        $field_val = apply_filters('arfdisplaysavedfieldvalue', $foptv, $foptk, $field);
                        
                        if (is_array($foptv)) {
                            $foptv = $foptv['label'];
                            if ($field_val['value'] == '(Blank)'){
                                $field_val['value'] = "";
                            }    
                            $field_val = (isset($field['separate_value'])) ? $field_val['value'] : $foptv;
                        }
                            if(!empty($foptv)){
                                if(isset($field['separate_value'])){
                                    if(!empty($foptv) && (isset($field_val) && !empty($field_val))){
                                        $min_field++;
                                    }
                                }else{
                            $min_field++;
                                }
                        }
                        $foptv = apply_filters('show_field_label', $foptv, $foptk, $field);

                        if( $opt_counter == 0 ){
                            if( $foptv == '' ){
                                $multi_sel_title = esc_html__( 'Please Select', 'ARForms');
                            } else {
                                if( $field_val == '' ){
                                    $multi_sel_title = $foptv;
                                }
                            }
                        }

                        $opt_counter++;
                    }
                }

                $select_attrs = array();

                if( !empty( $arf_save_form_data ) ){
                    $select_attrs[ 'data-save' ] = 'true';
                }

                $sel_field_id = 'field_' . $field['field_key'] . '_' . $arf_data_uniq_id;

                if (isset($field['size']) && $field['size'] != 1) {
                    if (($field['field_width'] != '' || $newarr['auto_width'] != 1) and $field['field_width'] != '') {
                        $select_field_style = 'width:' . $field['field_width'] . 'px !important; ' . $inline_css_without_style;
                    } else {
                        $select_field_style = isset( $inline_css_with_style_tag ) ? $inline_css_with_style_tag : '';
                    }
                } else {
                    $select_field_style = 'min-width:100px;';
                }

                $select_field_opts = array();

                $count_i = 0;
                if (!empty($field['options'])) {

                    foreach ($field['options'] as $opt_key => $opt) {

                        $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);
                        
                        $opt = apply_filters('show_field_label', $opt, $opt_key, $field);

                        if (is_array($opt)) {
                            $opt = $opt['label'];
                            if ($field_val['value'] == '(Blank)'){
                                $field_val['value'] = "";
                            }    
                            $field_val = (isset($field['separate_value'])) ? $field_val['value'] : $opt;
                        }
                        $disble_att = '';
                        if ($count_i == 0) {
                            if( $opt == '' ){
                                //continue;
                                $opt = esc_html__('Please Select', 'ARForms');
                            } else {
                                if( $field_val == '' && '' == $opt ){
                                    //continue;
                                    $opt = esc_html__('Please Select', 'ARForms');
                                }
                            }
                            $select_attrs['data-placeholder'] = $opt;
                        }

                        $field['value'] = !empty($field['value']) ? $field['value'] : array();
                        
                        $arfdefault_selected_val = (isset($field['separate_value']) && 1 == $field['separate_value'] && !empty( $field['default_value'] ) ) ? implode(',',$field['default_value']) : implode(',',$field['value']);
                        
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

                $select_attrs[ 'data-default-val' ] = $arfdefault_selected_val;
                
                if (isset($field['required']) and $field['required']) {
                    $select_attrs['data-validation-required-message'] = esc_attr($field['blank']);
                }

                if( 'material_outlined' == $inputStyle ){
                    $mo_active_container_cls = ( !empty( $arfdefault_selected_val ) ) ? 'arf_material_active_container_open' : '';
                    $return_string .= '<div class="arf_material_outline_container '.$mo_active_container_cls.'">';
                }
                if( $inputStyle == 'material' ){
                    $mo_active_container_cls = ( !empty( $arfdefault_selected_val ) ) ? 'arf_material_active_container_open' : '';
                    $return_string .= '<div class="arf_material_theme_container '.$mo_active_container_cls.'">';
                }

                if( !empty( $arfdefault_selected_val ) ){
                    $select_attrs['class'] = 'arf_material_active';
                }

                $select_attrs = array_merge( $select_attrs, $arf_on_change_function_array );

                if( !empty( $field['min_opt_sel'] ) ){
                    $select_attrs['data-validation-minselected-minselected'] = $field['min_opt_sel'];
                    $select_attrs['data-validation-minselected-message'] = !empty( $field['min_opt_sel_msg'] ) ? $field['min_opt_sel_msg'] : 'please select minimum options.';
                }

                if( !empty( $field['max_opt_sel'] ) ){
                    $select_attrs['data-validation-maxselected-maxselected'] = $field['max_opt_sel'];
                    $select_attrs['data-validation-maxselected-message'] = !empty( $field['max_opt_sel_msg'] ) ? $field['max_opt_sel_msg'] : 'please select minimum options.';
                }

                $return_string .= $maincontroller->arf_selectpicker_dom( $field_name, $sel_field_id, ' arf_form_field_picker multi-select', $select_field_style, $arfdefault_selected_val, $select_attrs, $select_field_opts, false, array(), false, array(), true, $field, false, '', '', $arf_set_default_label );

                if( 'material_outlined' == $inputStyle ){
                        $mo_active_cls = ( !empty( $arfdefault_selected_val ) ) ? 'arf_material_active' : '';
                        $return_string .= '<div class="arf_material_outliner '.$mo_active_cls.'">';
                            $return_string .= '<div class="arf_material_outliner_prefix"></div>';
                            $return_string .= '<div class="arf_material_outliner_notch">';
                                $return_string .= $arf_main_label;
                            $return_string .= '</div>';
                            $return_string .= '<div class="arf_material_outliner_suffix"></div>';
                        $return_string .= '</div>';
                    $return_string .= '</div>';
                }
                if( $inputStyle == 'material' ){
                    $return_string .= '</div>';
                }
                $return_string .= $field_tootip_standard;
            }
            $return_string .= $field_description;
            $return_string .= '</div>';
        }
        return $return_string;
    }



   	function arf_multiselect_createfield($field_data) {

        if ($field_data['type'] == 'arf_multiselect') {
            $field_data['name'] = addslashes(esc_html__('Multi Select', 'ARForms'));
        }
        return $field_data;
    }

    function arf_add_multiselect_default_field_options($field_options,$type){
        if( $type == 'arf_multiselect' ){
            $field_options['options'] = json_encode(array('', 'Select 1','Select 2'));
        }
        return $field_options;
    }

    function arf_field_values_options_outside_function($fields){
        $count = count($fields);
        $fields[$count+1] = 'arf_multiselect';
        return $fields;
    }

    function arf_add_multiselect_field_in_array($fields,$field_icons,$field_json,$positioned_field_icons) {
        global $arfieldhelper, $maincontroller;
        
        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();        
        $field_order_arf_multiselect = isset($field_opt_arr['arf_multiselect']) ? $field_opt_arr['arf_multiselect'] : '';     
        $field_data_array = $field_json;
        $field_data_obj_arf_multiselect = $field_data_array->field_data->arf_multiselect;

        $select_options = array(
            '' => 'Please Select',
            'Select 1' => 'Select 1',
            'Select 2' => 'Select 2'
        );

        $fields['arf_multiselect'] = "<div class='arf_inner_wrapper_sortable single_column_wrapper arfmainformfield edit_form_item arffieldbox ui-state-default 1 edit_field_type_arf_multiselect arf1columns' data-id='arf_editor_main_row_{arf_editor_index_row}'><div class='arf_multiiconbox'><div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'><input type='hidden' name='multicolumn' />{$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}</div>{$field_icons['multicolumn_expand_icon']}</div><div class='sortable_inner_wrapper' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'><div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'><div class='fieldname-row' style='display : block;'><div class='fieldname'><label class='arf_main_label' id='field_{arf_field_id}'><span class='arfeditorfieldopt_label arf_edit_in_place'><input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Multi Select' data-field-id='{arf_field_id}' /></span><span id='require_field_{arf_field_id}'><a href='javascript:void(0)' onClick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title=". esc_html__('Click to mark as not compulsory field', 'ARForms')."></a></span></label></div></div><div class='arf_fieldiconbox arf_fieldiconbox_with_edit_option' data-field_id='{arf_field_id}'>".$positioned_field_icons['arf_multiselect']."</div><div class='controls sltstandard_front input-field'>" . $maincontroller->arf_selectpicker_dom( 'item_meta[{arf_field_id}][]', 'field_{arf_unique_key}_{arf_field_id}', ' arf_form_field_picker multi-select', 'float:left;', '', array(), $select_options ) . "<div class='arf_field_description' id='field_description_{arf_field_id}'></div><div class='help-block'></div></div><input type='hidden' class='arf_field_data_hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_arf_multiselect))."' data-field_options='".json_encode($field_order_arf_multiselect)."' /><div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'><div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div><div class='arf_field_option_model_container'><div class='arf_field_option_content_row'></div></div><div class='arf_field_option_model_footer'><button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div><div class='arf_field_values_model' id='arf_field_values_model_skeleton_{arf_field_id}'><div class='arf_field_values_model_header'>".esc_html__('Edit Options','ARForms')."</div><div class='arf_field_values_model_container'><div class='arf_field_values_content_row'><div class='arf_field_values_content_loader'><svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></div></div></div><div class='arf_field_values_model_footer'><button type='button' class='arf_field_values_close_button'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_values_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div></div></div></div>";
        return $fields;
    }

    function arf_add_multiselect_field_in_array_materialize($fields,$field_icons,$field_json,$positioned_field_icons) {
        global $arfieldhelper, $maincontroller;
        
        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();        
        $field_order_arf_multiselect = isset($field_opt_arr['arf_multiselect']) ? $field_opt_arr['arf_multiselect'] : '';        
        $field_data_array = $field_json;
        $field_data_obj_arf_multiselect = $field_data_array->field_data->arf_multiselect;

        $select_options = array(
            '' => 'Please Select',
            'Select 1' => 'Select 1',
            'Select 2' => 'Select 2'
        );

        $fields['arf_multiselect'] = "<div class='arf_inner_wrapper_sortable single_column_wrapper arfmainformfield edit_form_item arffieldbox ui-state-default 1 edit_field_type_arf_multiselect arf1columns' data-id='arf_editor_main_row_{arf_editor_index_row}'><div class='arf_multiiconbox'><div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'><input type='hidden' name='multicolumn' />{$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}</div>{$field_icons['multicolumn_expand_icon']}</div><div class='sortable_inner_wrapper' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'><div id='arf_field_{arf_field_id}' class='arfformfield input-field control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'><div class='arf_fieldiconbox arf_fieldiconbox_with_edit_option' data-field_id='{arf_field_id}'>".$positioned_field_icons['arf_multiselect']."</div><div class='controls sltstandard_front input-field'><div class='arf_material_theme_container '>". $maincontroller->arf_selectpicker_dom( 'item_meta[{arf_field_id}][]', 'field_{arf_unique_key}_{arf_field_id}', ' arf_form_field_picker multi-select', 'float:left;', '', array(), $select_options ) ."<label class='arf_main_label active' id='field_{arf_field_id}'><span class='arfeditorfieldopt_label arf_edit_in_place'><input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Multi Select' data-field-id='{arf_field_id}' /></span><span id='require_field_{arf_field_id}'><a href='javascript:void(0)' onClick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title=". esc_html__('Click to mark as not compulsory field', 'ARForms')."></a></span></label><div class='arf_field_description' id='field_description_{arf_field_id}'></div><div class='help-block'></div></div></div><input type='hidden' class='arf_field_data_hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_arf_multiselect))."' data-field_options='".json_encode($field_order_arf_multiselect)."' /><div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'><div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div><div class='arf_field_option_model_container'><div class='arf_field_option_content_row'></div></div><div class='arf_field_option_model_footer'><button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div><div class='arf_field_values_model' id='arf_field_values_model_skeleton_{arf_field_id}'><div class='arf_field_values_model_header'>".esc_html__('Edit Options','ARForms')."</div><div class='arf_field_values_model_container'><div class='arf_field_values_content_row'><div class='arf_field_values_content_loader'><svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></div></div></div><div class='arf_field_values_model_footer'><button type='button' class='arf_field_values_close_button'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_values_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div></div></div></div>";
        return $fields;
    }

    function arf_add_multiselect_field_in_array_materialize_outlined( $fields, $field_icons, $field_json, $positioned_field_icons ){
        global $arfieldhelper, $maincontroller;
        
        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();        
        $field_order_arf_multiselect = isset($field_opt_arr['arf_multiselect']) ? $field_opt_arr['arf_multiselect'] : '';        
        $field_data_array = $field_json;
        $field_data_obj_arf_multiselect = $field_data_array->field_data->arf_multiselect;

        $select_options = array(
            'Select 1' => 'Select 1',
            'Select 2' => 'Select 2'
        );

        $fields['arf_multiselect'] = "<div class='arf_inner_wrapper_sortable single_column_wrapper arfmainformfield edit_form_item arffieldbox ui-state-default 1 edit_field_type_arf_multiselect arf1columns' data-id='arf_editor_main_row_{arf_editor_index_row}'><div class='arf_multiiconbox'><div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'><input type='hidden' name='multicolumn' />{$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}</div>{$field_icons['multicolumn_expand_icon']}</div><div class='sortable_inner_wrapper' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'><div id='arf_field_{arf_field_id}' class='arfformfield input-field control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'><div class='arf_fieldiconbox arf_fieldiconbox_with_edit_option' data-field_id='{arf_field_id}'>".$positioned_field_icons['arf_multiselect']."</div><div class='controls sltstandard_front input-field'><div class='arf_material_outline_container'>". $maincontroller->arf_selectpicker_dom( 'item_mata[{arf_field_id}][]', 'field_{arf_unique_key}_{arf_field_id}', 'arf_form_field_picker multi-select', '', '', array('data-placeholder' => 'Multi Select'), $select_options, false, array( '' => 'arf_material_outline_sel_data_label'), false, array(), false, array(), false, '', '', true  ) ."<div class='arf_material_outliner'><div class='arf_material_outliner_prefix'></div><div class='arf_material_outliner_notch'><label class='arf_main_label' id='field_{arf_field_id}'><span class='arfeditorfieldopt_label arf_edit_in_place'><input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Multi Select' data-field-id='{arf_field_id}' /></span><span id='require_field_{arf_field_id}'><a href='javascript:void(0)' onClick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title=". esc_html__('Click to mark as not compulsory field', 'ARForms')."></a></span></label></div><div class='arf_material_outliner_suffix'></div></div></div><div class='arf_field_description' id='field_description_{arf_field_id}'></div><div class='help-block'></div></div><input type='hidden' class='arf_field_data_hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_arf_multiselect))."' data-field_options='".json_encode($field_order_arf_multiselect)."' /><div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'><div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div><div class='arf_field_option_model_container'><div class='arf_field_option_content_row'></div></div><div class='arf_field_option_model_footer'><button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div><div class='arf_field_values_model' id='arf_field_values_model_skeleton_{arf_field_id}'><div class='arf_field_values_model_header'>".esc_html__('Edit Options','ARForms')."</div><div class='arf_field_values_model_container'><div class='arf_field_values_content_row'><div class='arf_field_values_content_loader'><svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></div></div></div><div class='arf_field_values_model_footer'><button type='button' class='arf_field_values_close_button'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_values_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div></div></div></div>";
        return $fields;
    }

    function arf_load_bootstrap_js_from_outside_function($field_type){
        global $arfversion;
        if( $field_type == 'arf_multiselect' ){
        	wp_register_script('arf_selectpicker', ARFURL . '/js/arf_selectpicker.js', array('jquery'), $arfversion);
            wp_enqueue_script('arf_selectpicker');
            wp_register_style('arf_selectpicker', ARFURL . '/css/arf_selectpicker.css', array(), $arfversion);
            wp_enqueue_style('arf_selectpicker');
        }
    }

     function arf_install_multiselect_field($fields){
        array_push($fields, 'arf_multiselect');
        return $fields;
    }
}
?>