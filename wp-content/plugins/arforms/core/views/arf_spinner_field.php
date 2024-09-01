<?php
define('ARF_SPINNER_SLUG', 'arf_spinner');

global $arf_spinner_field_class_name, $arf_spinner_new_field_data, $arf_spinner_new_field_data, $arf_font_awesome_loaded;

$arf_spinner_field_class_name = array(ARF_SPINNER_SLUG => 'red');
$arf_spinner_new_field_data = array(ARF_SPINNER_SLUG => addslashes(esc_html__('Spinner', 'ARForms')));
$arf_spinner_new_field_data = array();
$arf_font_awesome_loaded = new arf_spinner_field();

global $arf_spinner_loaded;
$arf_spinner_loaded = array();                                                                                                                                              
class arf_spinner_field {

    function __construct() {

        add_filter( 'arfaavailablefields', array( $this, 'arf_add_spinner_field_in_list'), 10);

        add_filter( 'arform_input_fields', array( $this, 'arf_add_spinner_input_field') );

        add_filter( 'arf_manage_field_element_order_outside', array( $this, 'arf_add_spinner_in_order' ) );

        add_filter( 'arf_migrate_field_type_from_outside', array( $this, 'arf_add_spinner_for_type_conversion') );

        add_filter('arf_all_field_css_class_for_editor', array($this, 'arf_get_spinner_field_class'), 11, 3);

        add_filter('arfavailablefieldsbasicoptions', array($this, 'add_availablefieldsbasicoptions'), 11, 3);

        add_filter( 'arf_field_type_label_filter', array( $this, 'arf_field_label_for_options') );

        add_action('arfdisplayaddedfields', array($this, 'add_spinner_field_to_editor'), 12, 3);

        add_filter('form_fields', array($this, 'add_spinner_field_to_frontend'), 12, 12);

        add_filter( 'arf_input_style_label_position_outside', array( $this, 'arf_set_spinner_label_position'), 10, 3 );

        add_filter('arf_save_more_field_from_out_side', array($this, 'arf_save_spinner_field'), 11, 2);

        add_filter('arf_new_field_array_filter_outside', array($this, 'arf_add_spinner_field_in_array'),11,4);

        add_filter('arf_new_field_array_materialize_filter_outside', array($this, 'arf_add_spinner_field_in_array_materialize'),11,4);

        add_filter( 'arf_new_field_array_materialize_outlined_filter_outside', array( $this, 'arf_add_spinner_field_in_array_materialize_outlined'), 11, 4 );

        add_filter('arf_installed_fields_outside',array($this,'arf_install_spinner_field'),11);

        add_filter('arf_positioned_field_options_icon',array($this,'arf_positioned_field_options_icon_for_spinner'),11,2);

        add_filter('arf_default_value_array_field_type_from_itemmeta', array($this,'arf_default_value_array_field_type_spinner'),11,2); 
    }
    

    function arf_default_value_array_field_type_spinner($field_types){
        array_push($field_types, ARF_SPINNER_SLUG);
        return $field_types;
    }

    function arf_positioned_field_options_icon_for_spinner($positioned_icon,$field_icons){
        $positioned_icon[ARF_SPINNER_SLUG] = "{$field_icons['field_require_icon']}".str_replace('{arf_field_type}',ARF_SPINNER_SLUG,$field_icons['arf_field_duplicate_icon'])."{$field_icons['field_delete_icon']}".str_replace('{arf_field_type}',ARF_SPINNER_SLUG,$field_icons['field_option_icon'])."{$field_icons['arf_field_move_icon']}";
        return $positioned_icon;
    }

    function arf_add_spinner_field_in_list( $fields ){

        $fields['arf_spinner'] = array(
            'icon' => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" fill="#4E5462" viewBox="0 0 30 30" style="enable-background:new 0 0 30 30;" xml:space="preserve"> <path id="XMLID_21_" class="st0" d="M7.9,23.3L26.3,5v13.6c0,0.4,0.4,0.6,0.7,0.6h0.4c0.4,0,0.7-0.4,0.7-0.6V2.9 C28,2.4,27.6,2,27.2,2H2.7C2.4,2,2,2.5,2,2.9v24.1C2,27.5,2.4,28,2.7,28h24.5c0.5,0,0.7-0.5,0.7-0.9v-3c0-0.4-0.4-0.6-0.7-0.6h-0.4 c-0.4,0-0.7,0.4-0.7,0.6v2.1H5.2l0,0H3.8V3.8h21.4L6.7,22.1c-0.3,0.3-0.3,0.6,0,0.9l0.3,0.3C7.2,23.5,7.7,23.5,7.9,23.3z"/><path id="XMLID_11_" class="st0" d="M6.6,9.8V9.1c0-0.2,0.2-0.4,0.4-0.4h5.1c0.3,0,0.4,0.1,0.4,0.4v0.8c0,0.2-0.2,0.4-0.4,0.4H7.1    C6.8,10.2,6.6,10,6.6,9.8z"/> <path id="XMLID_2_" class="st0" d="M9.2,6.4H10c0.2,0,0.4,0.2,0.4,0.4V12c0,0.3-0.1,0.4-0.4,0.4H9.2c-0.2,0-0.4-0.2-0.4-0.4V6.9    C8.9,6.6,9,6.4,9.2,6.4z"/><path id="XMLID_13_" class="st0" d="M17.3,21.2v-0.8c0-0.2,0.2-0.4,0.4-0.4h5.1c0.3,0,0.4,0.1,0.4,0.4v0.8c0,0.2-0.2,0.4-0.4,0.4    h-5.1C17.5,21.6,17.3,21.5,17.3,21.2z"/></svg>',
            'label' => addslashes( esc_html__('Spinner', 'ARForms') )
        );
        return $fields;
    }

    
    function arf_add_spinner_input_field( $inputFields ){

        array_push($inputFields, 'arf_spinner' );

        return $inputFields;
    }

    function arf_add_spinner_in_order( $fields_order ){

        array_push( $fields_order, 'arf_spinner' );

        return $fields_order;
    }

    function arf_add_spinner_for_type_conversion( $field_types ){

        $field_types['arf_spinner'] = esc_html__( 'Spinner', 'ARForms' );

        return $field_types;

    }

   function arf_get_spinner_field_class($class) {
        global $arf_spinner_field_class_name, $arf_spinner_total_class;
        $as_class = array_merge($class, $arf_spinner_field_class_name);
        $arf_spinner_total_class = count($as_class);
        return $as_class;
    }

    function add_availablefieldsbasicoptions($basic_option) {

        $spinner_filed_option = array(
            ARF_SPINNER_SLUG => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'numberofsteps' => 5,
                'numberrange' => 7,
                'numberrange_min_validation' => 10,
                'numbberrange_max_validation' => 11,
            )
        );

        return array_merge($basic_option, $spinner_filed_option);
    }

    function arf_field_label_for_options( $field_label_arr ){

        $field_label_arr['arf_spinner'] = esc_html__('Spinner','ARForms');

        return $field_label_arr;
    }

    function add_spinner_field_to_editor($field, $inputstyle, $newarr) {
        global $arfajaxurl, $wpdb;
        $field_name = "item_meta[" . $field['id'] . "]";

        $field['field_options'] = json_decode($field['field_options'],true);


        if( json_last_error() != JSON_ERROR_NONE ){
            $field['field_options'] = maybe_unserialize($field['field_options']);
        }
        
       
        $field['field_options']['default_value'] = isset($field['field_options']['default_value']) ? $field['field_options']['default_value'] : '';
        if ($field['type'] == ARF_SPINNER_SLUG) {
            if ( $inputstyle != 'material_outlined'){

        ?>
    
            <div class="arf_field_spinner_container" id='<?php echo 'arf_field_spinner_container_'.$field['id']; ?>'>
               
                <div class='arf_field_spinner_input'>
                   <div class='arf_editor_prefix_suffix_wrapper arf_both_pre_suffix'>
                        <span class='arf_editor_prefix_icon arf_spin_prev_button' onclick='arf_spin_prev(<?php echo $field['id'];?>)'>
                            <i class='fas fa-minus'></i>
                        </span>
                        <input name='item_meta[<?php echo $field['id']; ?>]' class='arf_spinner_control' type='text' style='float:left;padding-left:10px !important;' id='field_<?php echo $field['id']; ?>-0' value='<?php echo $field['field_options']['default_value']; ?>' onkeydown="arfvalidatenumber(this,event);" />
                        <span class='arf_editor_suffix_icon arf_spin_next_button' onclick='arf_spin_next(<?php echo $field['id'];?>)'>
                            <i class='fas fa-plus'></i>
                        </span>
                    </div>
                </div>
                   
            </div>
            <?php
            } else {
                ?>
                    <div class='arf_material_outline_container arf_material_outline_container_with_icons'>
                        <i class='arf_trailing_icon fas fa-plus arf_spin_next_button' onclick='arf_spin_next(<?php echo $field['id'];?>)'></i>
                        <i class='arf_leading_icon fas fa-minus arf_spin_prev_button' onclick='arf_spin_prev(<?php echo $field['id'];?>)'></i>
                        <input id='field_xuLDk' name='item_meta[<?php echo $field['id'];?>]' type='text' class='arf_material_active arf_spinner_control' style='float: left;padding-left:50px !important;' onkeydown="arfvalidatenumber(this,event);" placeholder='' value='<?php echo $field['field_options']['default_value'];?>'>
                        <div class='arf_material_outliner'>
                            <span class='arf_material_outliner_prefix'></span>
                            <span class='arf_material_outliner_notch'>
                                <label class='arf_main_label' id='field_"<?php echo $field['id'];?>"'>
                                    <span class='arfeditorfieldopt_label arf_edit_in_place'>
                                        <input type='text' class='arf_edit_in_place_input inplace_field arf_material_active' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='<?php echo $field['field_options']['name'];?>' data-field-id='<?php echo $field['id'];?>'>
                                    </span>
                                    <span id="require_field_<?php echo $field['id']; ?>">
                                        <a href="javascript:void(0)" onclick="javascript:arfmakerequiredfieldfunction(<?php echo $field['id']; ?>,<?php echo $field_required = ($field['required'] == '0') ? '0' : '1'; ?>,'1')" class="arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield<?php echo $field_required ?>" id="req_field_<?php echo $field['id']; ?>" title="<?php echo addslashes(esc_html__('Click to mark as', 'ARForms') . ( $field['required'] == '0' ? ' ' : ' not ')) . addslashes(esc_html__('compulsory field.', 'ARForms')); ?>"></a>
                                    </span>
                                </label>
                            </span>
                            <span class='arf_material_outliner_suffix'></span>
                        </div>
                    </div>
            <?php }
        }
    }


    function add_spinner_field_to_frontend($return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tootip, $field_description,$res_data,$inputStyle,$arf_main_label,$get_onchage_func_data) {
        if ($field['type'] != 'arf_spinner') {
            return $return_string;
        }
        
        global $style_settings, $arfsettings, $arfeditingentry, $arffield, $arfieldhelper, $wpdb, $MdlDb, $arformcontroller; 

        $form_data = new stdClass();
        $form_data->id = $form->id;
        $form_data->form_key = $form->form_key;
        $form_data->options = maybe_serialize($form->options);

        if( $res_data == '' ){
            $res_data = $wpdb->get_results($wpdb->prepare("SELECT id, type, field_options,conditional_logic FROM " . $MdlDb->fields . " WHERE form_id = %d ORDER BY id", $form->id));
        }


        $arf_save_form_data = '';

        if( isset( $form->options['arf_form_save_database'] ) && 1 == $form->options['arf_form_save_database'] ){
            $arf_save_form_data = ' data-save="true" ';
        }
        
        $spinner_style = '';
        if($inputStyle == 'material'){
            $spinner_style = "style='padding-left:10px !important;'";
        } 

        $arf_required = '';
        if ($field['required']) {
            $field['required_indicator'] = ( isset($field['required_indicator']) && ($field['required_indicator'] != '' )) ? $field['required_indicator'] : '*';
            $arf_required = '<span class="arfcheckrequiredfield">' . $field['required_indicator'] . '</span>';
        }
        

        if ($field['type'] == ARF_SPINNER_SLUG) {
            if (isset($field['set_field_value'])) {
                $field['default_value'] = $field['set_field_value'];
                $field['value'] = $field['set_field_value'];
            }

            $field_tooltip_class = "";
            $field_tootip_material = "";
            $field_tootip_standard =  "";
            if($field_tootip!='')
            {
                if($inputStyle=="material")
                {
                    $field_tootip_material = $field_tootip;
                    $field_tooltip_class = " arfhelptip ";
                }
                else {
                    $field_tootip_standard = $field_tootip;
                }
                
            }
             
            if( $inputStyle == 'material'){
                $return_string .= $arf_main_label;
            }
             
            $arf_spinner_id = 'field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '';
            $arf_spinner_unique_id = 'arf_spinner_uniqu_'.$arf_data_uniq_id;


            $material_input_cls = ($inputStyle == 'material') ? 'arf_spinner' : '';
            $arf_data_provided = ($inputStyle != 'material' ) ? 'data-provide="typeahead"' : '';

            
            $return_string .= '<div class="controls'.$field_tooltip_class.'" '.$field_tootip_material.'>';
            $spinner_field_options = $arformcontroller->arf_html_entity_decode( $field['field_options'] );
            if ( $inputStyle == 'material_outlined' ) {
                $return_string .= '<div class="arf_material_outline_container arf_material_outline_container_with_icons '.$arf_spinner_unique_id.'">';
                $return_string .= '<i class="arf_leading_icon fas fa-minus arf_spin_prev_button" onclick="arf_spin_prev(\''.$field_name.'\',\''.$arf_spinner_unique_id.'\')"></i>';
                $return_string .= '<i class="arf_trailing_icon fas fa-plus arf_spin_next_button" onclick="arf_spin_next(\''.$field_name.'\',\''.$arf_spinner_unique_id.'\')"></i>';
                $return_string .= '<input type="text" class="arf_spinner_control arf_spinner_control_'. $arf_data_uniq_id .'" id="'.$arf_spinner_id.'" name="'.$field_name.'" value="'.$field['value'].'" onkeydown="arfvalidatenumber(this,event);" ';
                
                $return_string .= ' data-field-options="' . htmlspecialchars( json_encode( $spinner_field_options ) ) . '"';

                if (isset($field['required']) and $field['required']) {
                    $return_string .= ' data-validation-required-message="' . esc_attr($field['blank']) . '" ';
                }
                
                if( $field['minnum'] != '' ){
                    $return_string .= ' min="'.$field['minnum'].'" ';
                    if( !empty( $field['min_error_msg'] ) ){
                        $return_string .= ' data-validation-min-message="'.$field['min_error_msg'].'" ';
                    }
                }

                if( $field['maxnum'] != '' ){
                    $return_string .= ' max="'.$field['maxnum'].'" ';
                    if( !empty( $field['max_error_msg'] ) ){
                        $return_string .= ' data-validation-max-message="'.$field['max_error_msg'].'" ';
                    }
                }
                $return_string .= $spinner_style;
                $return_string .= $get_onchage_func_data . $arf_save_form_data . ' />';
                $material_outlined_notch_cls = '';
                if( empty( $field['name'] ) ){
                    $material_outlined_notch_cls = 'arf_material_outliner_display';
                }
                $return_string .= '<div class="arf_material_outliner"><div class="arf_material_outliner_prefix"></div><div class="arf_material_outliner_notch '.$material_outlined_notch_cls.'">';
                if( !empty( $field['name'] ) ){
                    $return_string .= '<label data-type="text" for="field_"'.$field['field_key'].' class="arf_main_label">'.$field['name'].$arf_required.'</label>';
                }
                $return_string .= '</div><div class="arf_material_outliner_suffix"></div></div></div>';
                $return_string .=$field_tootip_standard;
                $return_string .=$field_description;
                $return_string .='</div>';
            } else {
                $prefix = '<div class="arf_prefix_suffix_wrapper arf_both_pre_suffix '.$arf_spinner_unique_id.'">';
                $prefix .= '<span id="arf_prefix_' . $field['field_key'] . '" class="arf_prefix arf_spin_prev_button" onclick="arf_spin_prev(\''.$field_name.'\',\''. $arf_spinner_unique_id.'\')">
                            <i class="fas fa-minus" ></i>
                            </span>'; 

                $return_string .= $prefix;
                $return_string .= '<input type="text" id="'.$arf_spinner_id.'" name="'.$field_name.'" value="'.$field['value'].'"';
                $return_string .= ' data-field-options="' . htmlspecialchars( json_encode( $spinner_field_options ) ) . '"';
                if( $field['minnum'] != '' ){
                    $return_string .= ' min="'.$field['minnum'].'" ';
                    if( !empty( $field['min_error_msg'] ) ){
                        $return_string .= ' data-validation-min-message="'.$field['min_error_msg'].'" ';
                    }
                }

                if (isset($field['required']) and $field['required']) {
                    $return_string .= ' data-validation-required-message="' . esc_attr($field['blank']) . '" ';
                }

                if( $field['maxnum'] != '' ){
                    $return_string .= ' max="'.$field['maxnum'].'" ';
                    if( !empty( $field['max_error_msg'] ) ){
                        $return_string .= ' data-validation-max-message="'.$field['max_error_msg'].'" ';
                    }
                }
                $return_string .= $spinner_style;
                $return_string .= $get_onchage_func_data . $arf_save_form_data.'  class="arf_spinner_control" onkeydown="arfvalidatenumber(this,event);"  />';
                $suffix ='<span id="arf_suffix_' . $field['field_key'] . '" class="arf_suffix arf_spin_next_button"  onclick="arf_spin_next(\''.$field_name.'\',\''.$arf_spinner_unique_id.'\')">
                            <i class="fas fa-plus"></i>
                            </span>';
                $return_string .= $suffix;
                $return_string .='</div>';
                $return_string .=$field_tootip_standard;
                $return_string .=$field_description;
                $return_string .='</div>';
            }
            
        }

        return $return_string;
    }
    
    
    
    function arf_set_spinner_label_position( $position_arr, $inputStyle, $field_type ){

        if( 'arf_spinner' == $field_type ){
            if( 'material' == $inputStyle ){
                array_push( $position_arr, 'arf_spinner' );
            }
        }

        return $position_arr;
    }


    function arf_save_spinner_field($field_array) {
        return array_merge($field_array, array('arf_spinner_type', 'arf_spinner_title'));
    }

    function arf_add_spinner_field_in_array($fields,$field_icons,$json_data,$positioned_field_icons) {

        global $arfieldhelper;
        

        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();
        
        $field_order_arf_spinner = isset($field_opt_arr['arf_spinner']) ? $field_opt_arr['arf_spinner'] : '';
 
        $field_data_array = $json_data;
        
        $field_data_obj_arf_spinner = $field_data_array->field_data->arf_spinner;

        $default_value = $field_data_obj_arf_spinner->default_value;
        

        $arf_field_move_option_icon = "<div class='arf_field_option_icon'><a class='arf_field_option_input'><svg id='moveing' height='20' width='21'><g>".ARF_CUSTOM_MOVING_ICON."</g></svg></a></div>";

        $fields['arf_spinner'] = "<div  class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'>
                                <div class='arf_multiiconbox'>
                                    <div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'>
                                        <input type='hidden' name='multicolumn' />{$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}
                                    </div>{$field_icons['multicolumn_expand_icon']}
                                </div>

                                    <div class='sortable_inner_wrapper edit_field_type_arf_spinner' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'>
                                    <div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'>
                                    <div class='fieldname-row' style='display : block;'>
                                    <div class='fieldname'>
                                        <label class='arf_main_label' id='field_{arf_field_id}'>
                                            <span class='arfeditorfieldopt_label arf_edit_in_place'>
                                                <input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Spinner' data-field-id='{arf_field_id}' />
                                            </span>
                                            <span id='require_field_{arf_field_id}'>
                                                <a href='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title='Click to mark as not compulsory field'>
                                                </a>
                                            </span>
                                        </label>
                                    </div>
                                    </div>
                                    <div class='arf_fieldiconbox' data-field_id='{arf_field_id}'>".$positioned_field_icons[ARF_SPINNER_SLUG]."</div>
                                    <div class='controls'>
                                        <div class='arf_input_field_spinner_container' id='arf_input_field_spinner_container_{arf_field_id}'>
                                            <div class='arf_input_spinner'>
                                                <div class='arf_editor_prefix_suffix_wrapper arf_both_pre_suffix'>
                                                    <span class='arf_editor_prefix_icon arf_spin_prev_button'  onclick='arf_spin_prev({arf_field_id})'>
                                                        <i class='fas fa-minus'></i>
                                                    </span>
                                                    <input name='item_meta[{arf_field_id}]' class='arf_spinner_control' type='text' style='float:left;' id='field_{arf_field_id}-0' value='".$default_value."' onkeydown='arfvalidatenumber(this,event);' />
                                                    <span class='arf_editor_suffix_icon arf_spin_next_button' onclick='arf_spin_next({arf_field_id});'>
                                                        <i class='fas fa-plus'></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='arf_field_description' id='field_description_{arf_field_id}'></div>
                                        <div class='help-block'></div>
                                        </div>

                                            <input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='" . htmlspecialchars(json_encode($field_data_obj_arf_spinner)) . "' data-field_options='" . json_encode($field_order_arf_spinner) . "' />
                                            <div class='arf_field_option_model arf_field_option_model_cloned'>
                                            <div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div>
                                            <div class='arf_field_option_model_container'>
                                            <div class='arf_field_option_content_row'></div>
                                            </div>
                                            <div class='arf_field_option_model_footer'>
                                                <button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button>
                                                <button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>";

        return $fields;
    }

    function arf_add_spinner_field_in_array_materialize($fields,$field_icons,$json_data,$positioned_field_icons) {
        global $arfieldhelper;

        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();
        
        $field_order_arf_spinner = isset($field_opt_arr['arf_spinner']) ? $field_opt_arr['arf_spinner'] : '';

        $field_data_array = $json_data;
        
        $field_data_obj_arf_spinner = $field_data_array->field_data->arf_spinner;

        $default_value = $field_data_obj_arf_spinner->default_value;


        $fields['arf_spinner'] = "<div  class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'>
                                    <div class='arf_multiiconbox'><div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'><input type='hidden' name='multicolumn' />
                                            {$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}</div>{$field_icons['multicolumn_expand_icon']}
                                    </div>
                                    <div class='sortable_inner_wrapper edit_field_type_arf_spinner' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'>
                                        <div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'>
                                            <div class='fieldname-row' style='display : block;'>
                                                <div class='fieldname'>
                                                <label class='arf_main_label' id='field_{arf_field_id}'>
                                                    <span class='arfeditorfieldopt_label arf_edit_in_place'>
                                                        <input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Spinner' data-field-id='{arf_field_id}' />
                                                    </span>
                                                    <span id='require_field_{arf_field_id}'>
                                                        <a href='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title='Click to mark as not compulsory field'>
                                                        </a>
                                                    </span>
                                                </label>
                                                </div>
                                            </div>
                                            <div class='arf_fieldiconbox' data-field_id='{arf_field_id}'>".$positioned_field_icons[ARF_SPINNER_SLUG]."</div>
                                            <div class='controls'>
                                                <div class='arf_input_field_spinner_container' id='arf_input_field_spinner_container_{arf_field_id}'>
                                                    <div class='arf_input_spinner'>
                                                        <div class='arf_editor_prefix_suffix_wrapper arf_both_pre_suffix'>
                                                            <span class='arf_editor_prefix_icon arf_spin_prev_button'  onclick='arf_spin_prev({arf_field_id})'>
                                                            <i class='fas fa-minus'></i>
                                                            </span>
                                                            <input name='item_meta[{arf_field_id}]'  class='arf_spinner_control' type='text' style='float:left;padding-left:10px !important;' id='field_{arf_field_id}-0' value='".$default_value."' onkeydown='arfvalidatenumber(this,event);'/>
                                                            <span class='arf_editor_suffix_icon arf_spin_next_button' onclick='arf_spin_next({arf_field_id});'>
                                                                <i class='fas fa-plus'></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='arf_field_description' id='field_description_{arf_field_id}'></div>
                                                <div class='help-block'></div>
                                                </div>
                                                <input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='" . htmlspecialchars(json_encode($field_data_obj_arf_spinner)) . "' data-field_options='" . json_encode($field_order_arf_spinner) . "' />
                                                <div class='arf_field_option_model arf_field_option_model_cloned'>
                                                <div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div>
                                                <div class='arf_field_option_model_container'>
                                                <div class='arf_field_option_content_row'></div>
                                                </div>
                                                <div class='arf_field_option_model_footer'>
                                                    <button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button>
                                                    <button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
        
        return $fields;
    }


    function arf_add_spinner_field_in_array_materialize_outlined($fields,$field_icons,$json_data,$positioned_field_icons) {
        global $arfieldhelper;

        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();
        
        $field_order_arf_spinner = isset($field_opt_arr['arf_spinner']) ? $field_opt_arr['arf_spinner'] : '';

        $field_data_array = $json_data;
        
        $field_data_obj_arf_spinner = $field_data_array->field_data->arf_spinner;

        $default_value = $field_data_obj_arf_spinner->default_value;
        

        $fields['arf_spinner'] = "<div  class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'>
                                    <div class='arf_multiiconbox'><div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'><input type='hidden' name='multicolumn' />
                                            {$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}</div>{$field_icons['multicolumn_expand_icon']}
                                    </div>
                                    <div class='sortable_inner_wrapper edit_field_type_arf_spinner' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'>
                                        <div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'>
                                            <div class='arf_fieldiconbox' data-field_id='{arf_field_id}'>".$positioned_field_icons[ARF_SPINNER_SLUG]."</div>
                                            <div class='controls'>
                                                <div class='arf_material_outline_container arf_material_outline_container_with_icons'>
                                                    <i class='arf_trailing_icon fas fa-plus arf_spin_next_button' onclick='arf_spin_next({arf_field_id})'></i>
                                                    <i class='arf_leading_icon fas fa-minus arf_spin_prev_button' onclick='arf_spin_prev({arf_field_id})'></i>
                                                    <input id='field_xuLDk' name='item_meta[{arf_field_id}]' type='text' class='arf_material_active arf_spinner_control' style='float: left;padding-left:50px !important;padding-right:40px !important;' placeholder='' value='".$default_value."' onkeydown='arfvalidatenumber(this,event);'>
                                                    <div class='arf_material_outliner'>
                                                        <span class='arf_material_outliner_prefix'></span>
                                                        <span class='arf_material_outliner_notch'>
                                                            <label class='arf_main_label' id='field_{arf_field_id}'>
                                                                <span class='arfeditorfieldopt_label arf_edit_in_place'>
                                                                    <input type='text' class='arf_edit_in_place_input inplace_field arf_material_active' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Spinner' data-field-id='{arf_field_id}'>
                                                                </span>
                                                                <span id='require_field_{arf_field_id}'>
                                                                    <a href='javascript:void(0)' onclick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0 tipso_style' id='req_field_{arf_field_id}' to='' mark='' as='' not='' compulsory='' field=''></a>
                                                                </span>
                                                            </label>
                                                        </span>
                                                        <span class='arf_material_outliner_suffix'></span>
                                                    </div>
                                                </div>
                                                
                                                <div class='arf_field_description' id='field_description_{arf_field_id}'></div>
                                                <div class='help-block'></div>
                                                </div>
                                                <input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='" . htmlspecialchars(json_encode($field_data_obj_arf_spinner)) . "' data-field_options='" . json_encode($field_order_arf_spinner) . "' />
                                                <div class='arf_field_option_model arf_field_option_model_cloned'>
                                                <div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div>
                                                <div class='arf_field_option_model_container'>
                                                <div class='arf_field_option_content_row'></div>
                                                </div>
                                                <div class='arf_field_option_model_footer'>
                                                    <button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button>
                                                    <button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
        
        return $fields;
    }
    

    function arf_install_spinner_field($fields){
        array_push($fields,'ARF_SPINNER_SLUG');
        return $fields;
    }
}
?>