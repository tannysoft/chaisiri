<?php
define('ARF_WYSIWYG_SLUG', 'arf_wysiwyg');


global $arf_wysiwyg_field_class_name, $arf_wysiwyg_new_field_data, $arf_wysiwyg_new_field_data, $arf_font_awesome_loaded;

$arf_wysiwyg_field_class_name = array(ARF_WYSIWYG_SLUG => 'red');
$arf_wysiwyg_new_field_data = array(ARF_WYSIWYG_SLUG => addslashes(esc_html__('Rich Text Editor', 'ARForms')));
$arf_wysiwyg_new_field_data = array();
$arf_font_awesome_loaded = new arf_wysiwyg_field();

global $arf_wysiwyg_loaded;
$arf_wysiwyg_loaded = array();                                                                                                                                              
class arf_wysiwyg_field {

    function __construct() {

        add_filter( 'arfaavailablefields', array( $this, 'arf_add_wysiwyg_field_in_list'), 10);

        add_filter( 'arform_input_fields', array( $this, 'arf_add_wysiwyg_input_field') );

        add_filter( 'arf_manage_field_element_order_outside', array( $this, 'arf_add_wysiwyg_in_order' ) );

        add_filter( 'arf_migrate_field_type_from_outside', array( $this, 'arf_add_wysiwyg_for_type_conversion') );

        add_filter('arf_all_field_css_class_for_editor', array($this, 'arf_get_wysiwyg_field_class'), 11, 3);

        add_filter('arfavailablefieldsbasicoptions', array($this, 'add_availablefieldsbasicoptions'), 11, 3);

        add_filter( 'arf_field_type_label_filter', array( $this, 'arf_field_label_for_options') );

        add_action('arfdisplayaddedfields', array($this, 'add_wysiwyg_field_to_editor'), 12, 3);

        add_filter('form_fields', array($this, 'add_wysiwyg_field_to_frontend'), 12, 12);

        add_filter( 'arf_input_style_label_position_outside', array( $this, 'arf_set_wysiwyg_label_position'), 10, 3 );

        add_filter('arf_new_field_array_filter_outside', array($this, 'arf_add_wysiwyg_field_in_array'),11,4);

        add_filter('arf_new_field_array_materialize_filter_outside', array($this, 'arf_add_wysiwyg_field_in_array_materialize'),11,4);

        add_filter( 'arf_new_field_array_materialize_outlined_filter_outside', array( $this, 'arf_add_wysiwyg_field_in_array_materialize_outlined'), 11, 4 );

        add_filter('arf_installed_fields_outside',array($this,'arf_install_wysiwyg_field'),11);

        add_filter('arf_positioned_field_options_icon',array($this,'arf_positioned_field_options_icon_for_wysiwyg'),11,2);

        add_filter('arf_default_value_array_field_type_from_itemmeta', array($this,'arf_default_value_array_field_type_wysiwyg'),11,2); 

        add_action('wp_arf_footer', array($this, 'arf_wysiwyg_set_front_js_css'), 11, 2);

        add_action( 'arf_load_bootstrap_js_from_outside', array( $this, 'arf_wysiwyg_admin_footer_js_css' ) );

        add_filter( 'arf_bootstraped_field_from_outside', array( $this, 'arf_wysiwyg_add_external_fields'));

    }


    function arf_wysiwyg_set_front_js_css( $loaded_field ){

        if ( in_array('arf_wysiwyg', $loaded_field) ) {
            global $arfversion;
            wp_register_script( 'arf-trumbowyg-js', ARFURL . '/js/trumbowyg.min.js', array(), $arfversion );
            wp_enqueue_script( 'arf-trumbowyg-js' );
            wp_register_style( 'arf-trumbowyg-css', ARFURL . '/css/trumbowyg.min.css', array(), $arfversion );
            wp_enqueue_style( 'arf-trumbowyg-css' );
        }

    }

    function arf_default_value_array_field_type_wysiwyg($field_types){
        array_push($field_types, ARF_WYSIWYG_SLUG);
        return $field_types;
    }

    function arf_positioned_field_options_icon_for_wysiwyg($positioned_icon,$field_icons){
        $positioned_icon[ARF_WYSIWYG_SLUG] = "{$field_icons['field_require_icon']}".str_replace('{arf_field_type}',ARF_WYSIWYG_SLUG,$field_icons['arf_field_duplicate_icon'])."{$field_icons['field_delete_icon']}".str_replace('{arf_field_type}',ARF_WYSIWYG_SLUG,$field_icons['field_option_icon'])."{$field_icons['arf_field_move_icon']}";
        return $positioned_icon;
    }

    function arf_add_wysiwyg_field_in_list( $fields ){

        $fields['arf_wysiwyg'] = array(
            'icon' => '<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.3469 6.64H5.44898V9.28H3V5.32C3 4.96991 3.12901 4.63417 3.35864 4.38662C3.58828 4.13907 3.89974 4 4.22449 4H18.9184C19.2431 4 19.5546 4.13907 19.7842 4.38662C20.0138 4.63417 20.1429 4.96991 20.1429 5.32V9.28H17.6939V6.64H12.7959V6.6441H10.3469V6.64ZM10.3469 8.01538V23.36H7.08163V26H16.0612V23.36H12.7959V8.01538H10.3469Z" fill="#4E5462"/>
                        <path d="M16.7143 16.2298H20.6703V26H23.044V16.2298H27V13.5652H23.044H20.6703H16.7143V16.2298Z" fill="#4E5462"/>
                        </svg>',
            'label' => addslashes( esc_html__('Rich Text Editor', 'ARForms') )
        );
        return $fields;
    }

    
    function arf_add_wysiwyg_input_field( $inputFields ){

        array_push($inputFields, 'arf_wysiwyg' );
        return $inputFields;
    }

    function arf_add_wysiwyg_in_order( $fields_order ){

        array_push( $fields_order, 'arf_wysiwyg' );

        return $fields_order;
    }

    function arf_add_wysiwyg_for_type_conversion( $field_types ){

        $field_types['arf_wysiwyg'] = esc_html__( 'Rich Text Editor', 'ARForms' );

        return $field_types;

    }

   function arf_get_wysiwyg_field_class($class) {
        global $arf_wysiwyg_field_class_name, $arf_wysiwyg_total_class;
        $as_class = array_merge($class, $arf_wysiwyg_field_class_name);
        $arf_wysiwyg_total_class = count($as_class);
        return $as_class;
    }

    function add_availablefieldsbasicoptions($basic_option) {

        $wysiwyg_filed_option = array(
            ARF_WYSIWYG_SLUG => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'default_value' => 5,
                'requiredmsg' => 6,
                'customwidth' => 8,
                'arf_enable_readonly' =>9,
            )
        );

        return array_merge($basic_option, $wysiwyg_filed_option);
    }

    function arf_field_label_for_options( $field_label_arr ){

        $field_label_arr['arf_wysiwyg'] = esc_html__('Rich Text Editor','ARForms');

        return $field_label_arr;
    }

    function add_wysiwyg_field_to_editor($field, $inputstyle, $newarr) {
        global $arfajaxurl, $wpdb, $fields_with_external_js;
        $field_name = "item_meta[" . $field['id'] . "]";
        
        $field['field_options'] = json_decode($field['field_options'],true);
        
        
        if( json_last_error() != JSON_ERROR_NONE ){
            $field['field_options'] = maybe_unserialize($field['field_options']);
        }
        
        $field['field_options']['default_value'] = isset($field['field_options']['default_value']) ? $field['field_options']['default_value'] : '';
        if ($field['type'] == ARF_WYSIWYG_SLUG) {
            array_push( $fields_with_external_js, $field['type'] );
        ?>
            <div class="arf_field_wysiwyg_container" id='<?php echo 'arf_field_wysiwyg_container_'.$field['id']; ?>'>
                <div class='arf_field_wysiwyg_input'>
                    <textarea class="arf_rich_text_editor" name='item_meta[<?php echo $field['id']; ?>]' id='itemmeta_<?php echo $field['id']; ?>'><?php echo $field['field_options']['default_value']; ?></textarea>
                </div>
            </div>
            <?php
        }
    }


    function add_wysiwyg_field_to_frontend($return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tootip, $field_description,$res_data,$inputStyle,$arf_main_label,$get_onchage_func_data) {
        

        if ($field['type'] != 'arf_wysiwyg') {
            return $return_string;
        }
        
        global $style_settings, $arfsettings, $arfeditingentry, $arffield, $arfieldhelper, $wpdb, $MdlDb, $arformcontroller, $arfversion, $armainhelper, $arfieldcontroller, $arf_form_all_footer_js; 


        $form_data = new stdClass();
        $form_data->id = $form->id;
        $form_data->form_key = $form->form_key;
        $form_data->options = maybe_serialize($form->options);
        $is_default_blank = 1;
        $arf_field_width = "";

        if( $res_data == '' ){
            $res_data = $wpdb->get_results($wpdb->prepare("SELECT id, type, field_options,conditional_logic FROM " . $MdlDb->fields . " WHERE form_id = %d ORDER BY id", $form->id));
        }

        $arf_save_form_data = '';

        if( isset( $form->options['arf_form_save_database'] ) && 1 == $form->options['arf_form_save_database'] ){
            $arf_save_form_data = ' data-save="true" ';
        }

        $arf_cookie_field_arr_attr = "";
        if(!empty($arf_cookie_field_arr[$field['id']])){
            $arf_cookie_field_arr_attr = 'arf_cookie_field_default_attr="'.$arf_cookie_field_arr[$field['id']].'"';
        }
        

        $arf_required = '';
        if ($field['required']) {
            $field['required_indicator'] = ( isset($field['required_indicator']) && ($field['required_indicator'] != '' )) ? $field['required_indicator'] : '*';
            $arf_required = '<span class="arfcheckrequiredfield">' . $field['required_indicator'] . '</span>';
        }

        if (isset($field['inline_css']) and $field['inline_css'] != '') {
            $inline_css_with_style_tag = ' style="' . stripslashes_deep($armainhelper->esc_textarea($field['inline_css'])) . '" ';
            $inline_css_without_style = stripslashes_deep($armainhelper->esc_textarea($field['inline_css']));
        } else {
            $inline_css_with_style_tag = $inline_css_without_style = '';
        }

        $field_standard_tooltip = "";
        if (isset($field['tooltip_text']) and $field['tooltip_text'] != "") {
            if($inputStyle=='material')
            {
                $field_tooltip_class = ' arfhelptipfocus ';
            }
            else {
                $field_standard_tooltip = $arfieldhelper->arf_tooltip_display($field['tooltip_text'],$inputStyle);
            }
        }
        

        if ($field['type'] == ARF_WYSIWYG_SLUG) {
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
             
            if( $inputStyle == 'material' || $inputStyle == 'material_outlined' ){
                $return_string .= $arf_main_label;
            }
             
            $arf_wysiwyg_id = 'field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '';

            $arf_input_field_html = '';
            $arf_input_field_html .= $arfieldcontroller->input_fieldhtml($field, false);
            $arf_input_field_html .= $arfieldcontroller->input_html($field, false);
            

            if (isset($field['field_width']) and $field['field_width'] != '') {
                $arf_field_width = $field['field_width']. "px !important";
            }
            
            $return_string .= '<div class="controls'.$field_tooltip_class.'" style="width:'.$arf_field_width.'"'.$field_tootip_material.'>';
            $wysiwyg_field_options = $arformcontroller->arf_html_entity_decode( $field['field_options'] );


            if ( $inputStyle == 'material_outlined' ) {
                $return_string .= "<div class='arf_field_wysiwyg_container' id='arf_field_wysiwyg_container_".$field['id']."'>";
                $return_string .= "<div class='arf_field_wysiwyg_input'>";

                $return_string .= '<textarea data-default-value="'.$field['default_value'].'" class="arf_rich_text_editor" name="'.$field_name.'" id="' . $arf_wysiwyg_id .'"';

                

                if (isset($field['required']) and $field['required']) {
                    $return_string .=' required data-validation-required-message="' . esc_attr($field['blank']) . '" ';
                }

                $return_string .= $arf_input_field_html;
                $return_string .= $arf_cookie_field_arr_attr;

                if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                    $return_string .='readonly="readonly" ';    
                }
                $return_string .= $arf_save_form_data;

                $default_value = $field['default_value'];

                $default_value = apply_filters('arf_replace_default_value_shortcode',$default_value,$field,$form);

                if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                    $default_value = $field['set_field_value'];
                }
                

                if (isset($field['field_width']) and $field['field_width'] != '') {
                    $return_string .=' style="width:' . $field['field_width'] . 'px !important; ' . $inline_css_without_style . '"';
                } else {
                    $return_string .= $inline_css_with_style_tag;
                }

                $return_string .= $get_onchage_func_data . ' >';
                $return_string .= $field['field_options']['default_value'];
                $return_string .= '</textarea>';
                $return_string .= '</div></div>';
                                
                $return_string .=$field_tootip_standard;
                $return_string .=$field_description;
            } else {
                if( 'material_outlined' == $inputStyle ){
                    $material_outlined_cls = '';
                    if( !empty( $field['enable_arf_prefix'] ) || !empty( $field['enable_arf_suffix'] ) ){
                        $material_outlined_cls = 'arf_material_outline_container_with_icons';
                    }
                    if( !empty( $field['enable_arf_prefix'] ) && empty( $field['enable_arf_suffix'] ) ){
                        $material_outlined_cls .= ' arf_only_leading_icon ';
                    }
                    if( empty( $field['enable_arf_prefix'] ) && !empty( $field['enable_arf_suffix'] ) ){
                        $material_outlined_cls .= ' arf_only_trailing_icon ';
                    }
                    $return_string .= '<div class="arf_material_outline_container '.$material_outlined_cls.' ">';

                    $return_string .= $arformcontroller->arf_prefix_suffix_for_outlined( $field );
                }
                
                $return_string .= "<div class='arf_field_wysiwyg_container' id='arf_field_wysiwyg_container_".$field['id']."'>";
                $return_string .= "<div class='arf_field_wysiwyg_input'>";

                $return_string .= '<textarea data-default-value="'.$field['default_value'].'" class="arf_rich_text_editor" name="'.$field_name.'" id="' . $arf_wysiwyg_id .'"';

                if (isset($field['required']) and $field['required']) {
                    $return_string .=' required data-validation-required-message="' . esc_attr($field['blank']) . '" ';
                }

                $return_string .= $arf_input_field_html;
                $return_string .= $arf_cookie_field_arr_attr;

                if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                    $return_string .='readonly="readonly" ';    
                }
                $return_string .= $arf_save_form_data;

                $default_value = $field['default_value'];

                $default_value = apply_filters('arf_replace_default_value_shortcode',$default_value,$field,$form);

                if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                    $default_value = $field['set_field_value'];
                }
                

                if (isset($field['field_width']) and $field['field_width'] != '') {
                    $return_string .=' style="width:' . $field['field_width'] . 'px !important; ' . $inline_css_without_style . '"';
                } else {
                    $return_string .= $inline_css_with_style_tag;
                }


                $return_string .= $get_onchage_func_data . ' >';
                
                $return_string .= $field['field_options']['default_value'];
                $return_string .= '</textarea>';
                $return_string .= "</div></div>";

                $number_of_allowed_char = (isset($field['field_options']['max']) && $field['field_options']['max'] != '')?$field['field_options']['max']:'';

                if($number_of_allowed_char != ''){

                    $number_of_default_value = '0';

                    if ( $default_value != '' ) {
                        $count_default_value     = strlen( $default_value );
                        $number_of_default_value = ( isset( $count_default_value ) && $count_default_value > 0 ) ? $count_default_value : '0';
                    }
                
                   $return_string .= '<div class="arfcount_text_char_div"><span class="arftextarea_char_count">' . $number_of_default_value . '</span> / ' . $number_of_allowed_char . '</div>';
                }
                $return_string .= $field_standard_tooltip;
                $return_string .= $field_description;
            }
            $return_string .= "</div>";
            
            $arf_form_all_footer_js .= 'setTimeout(function() {';
                $arf_form_all_footer_js .= 'jQuery( "textarea[name=\''.$field_name.'\']" ).trumbowyg({';
                    $arf_form_all_footer_js .= 'svgPath: "'.ARFURL.'/images/icons.svg",';
                    if ( $field['field_options']['arf_enable_readonly'] == 1 ) {
                        $arf_form_all_footer_js .= 'disabled: true,';
                    }
                    $arf_form_all_footer_js .= 'btns: [';
                        $arf_form_all_footer_js .= '["undo", "redo"],';
                        $arf_form_all_footer_js .= '["formatting"],';
                        $arf_form_all_footer_js .= '["strong", "em", "del"],';
                        $arf_form_all_footer_js .= '["superscript", "subscript"],';
                        $arf_form_all_footer_js .= '["link"],';
                        $arf_form_all_footer_js .= '["insertImage"],';
                        $arf_form_all_footer_js .= '["justifyLeft", "justifyCenter", "justifyRight", "justifyFull"],';
                        $arf_form_all_footer_js .= '["unorderedList", "orderedList"],';
                        $arf_form_all_footer_js .= '["horizontalRule"],';
                        $arf_form_all_footer_js .= '["removeformat"],';
                        if ( $field['field_options']['arf_enable_readonly'] != 1 ) {
                            $arf_form_all_footer_js .= '["fullscreen"]';
                        }
                    $arf_form_all_footer_js .= ']';
                $arf_form_all_footer_js .= '}).on("tbwinit",function(){
                    if( this.getAttribute("disabled") != null ){
                        this.removeAttribute("disabled");
                    }
                }).on("tbwopenfullscreen",function(){
                    jQuery(this).parents(".arf_field_type_arf_wysiwyg").addClass("arf_wysiwyg_fullscreen");
                }).on("tbwclosefullscreen",function(){
                    jQuery(this).parents(".arf_field_type_arf_wysiwyg").removeClass("arf_wysiwyg_fullscreen");
                });';
            $arf_form_all_footer_js .= '}, 500);';

        }

        return $return_string;
    }
    
    
    
    function arf_set_wysiwyg_label_position( $position_arr, $inputStyle, $field_type ){

        if( 'arf_wysiwyg' == $field_type ){
            if( 'material' == $inputStyle ){
                array_push( $position_arr, 'arf_wysiwyg' );
            }
        }

        return $position_arr;
    }

    function arf_add_wysiwyg_field_in_array($fields,$field_icons,$json_data,$positioned_field_icons) {

        global $arfieldhelper;
        

        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();
        
        $field_order_arf_wysiwyg = isset($field_opt_arr['arf_wysiwyg']) ? $field_opt_arr['arf_wysiwyg'] : '';
 
        $field_data_array = $json_data;
        
        
        $field_data_obj_arf_wysiwyg = $field_data_array->field_data->arf_wysiwyg;

        $default_value = $field_data_obj_arf_wysiwyg->default_value;
        

        $arf_field_move_option_icon = "<div class='arf_field_option_icon'><a class='arf_field_option_input'><svg id='moveing' height='20' width='21'><g>".ARF_CUSTOM_MOVING_ICON."</g></svg></a></div>";

        $fields['arf_wysiwyg'] = "<div class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'><div class='sortable_inner_wrapper test123 edit_field_type_textarea' id='arfmainfieldid_{arf_field_id}' inner_class='arf_1col'><div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'><div class='fieldname-row' style='display : block;'><div class='fieldname'><label class='arf_main_label' id='field_{arf_field_id}'><span class='arfeditorfieldopt_label arf_edit_in_place'><input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Rich Text Editor' data-field-id='{arf_field_id}' /></span><span id='require_field_{arf_field_id}'><a href='javascript:void(0);' onclick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title=". esc_html__('Click to mark as not compulsory field', 'ARForms')."></a></span></label></div></div><div class='arf_fieldiconbox' data-field_id='{arf_field_id}'>".$positioned_field_icons[ARF_WYSIWYG_SLUG]."</div><div class='controls'><textarea name='item_meta[{arf_field_id}]' id='itemmeta_{arf_field_id}' onkeyup='arfchangeitemmeta({arf_field_id});' rows='3'></textarea><div class='arf_field_description' id='field_description_{arf_field_id}'></div><div class='help-block'></div></div><input type='hidden' class='arf_field_data_hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_arf_wysiwyg))."' data-field_options='".json_encode($field_order_arf_wysiwyg)."' /><div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'><div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div><div class='arf_field_option_model_container'><div class='arf_field_option_content_row'></div></div><div class='arf_field_option_model_footer'><button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div></div></div></div>";


        return $fields;
    }

    function arf_add_wysiwyg_field_in_array_materialize($fields,$field_icons,$json_data,$positioned_field_icons) {
        global $arfieldhelper;

        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();
        
        $field_order_arf_wysiwyg = isset($field_opt_arr['arf_wysiwyg']) ? $field_opt_arr['arf_wysiwyg'] : '';

        $field_data_array = $json_data;
        
        $field_data_obj_arf_wysiwyg = $field_data_array->field_data->arf_wysiwyg;

        $default_value = $field_data_obj_arf_wysiwyg->default_value;

        $fields['arf_wysiwyg'] = "<div class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'><div class='sortable_inner_wrapper edit_field_type_textarea' id='arfmainfieldid_{arf_field_id}' inner_class='arf_1col'><div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'><div class='fieldname-row' style='display : block;'><div class='fieldname'><label class='arf_main_label' id='field_{arf_field_id}'><span class='arfeditorfieldopt_label arf_edit_in_place'><input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Rich Text Editor' data-field-id='{arf_field_id}' /></span><span id='require_field_{arf_field_id}'><a href='javascript:void(0);' onclick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title=". esc_html__('Click to mark as not compulsory field', 'ARForms')."></a></span></label></div></div><div class='arf_fieldiconbox' data-field_id='{arf_field_id}'>".$positioned_field_icons[ARF_WYSIWYG_SLUG]."</div><div class='controls'><textarea name='item_meta[{arf_field_id}]' id='itemmeta_{arf_field_id}' onkeyup='arfchangeitemmeta({arf_field_id});' rows='3'></textarea><div class='arf_field_description' id='field_description_{arf_field_id}'></div><div class='help-block'></div></div><input type='hidden' class='arf_field_data_hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_arf_wysiwyg))."' data-field_options='".json_encode($field_order_arf_wysiwyg)."' /><div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'><div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div><div class='arf_field_option_model_container'><div class='arf_field_option_content_row'></div></div><div class='arf_field_option_model_footer'><button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div></div></div></div>";

        
        return $fields;
    }


    function arf_add_wysiwyg_field_in_array_materialize_outlined($fields,$field_icons,$json_data,$positioned_field_icons) {
        global $arfieldhelper;

        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();
        
        $field_order_arf_wysiwyg = isset($field_opt_arr['arf_wysiwyg']) ? $field_opt_arr['arf_wysiwyg'] : '';

        $field_data_array = $json_data;
        
        $field_data_obj_arf_wysiwyg = $field_data_array->field_data->arf_wysiwyg;

        $default_value = $field_data_obj_arf_wysiwyg->default_value;

        $fields['arf_wysiwyg'] = "<div class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'><div class='sortable_inner_wrapper edit_field_type_textarea' id='arfmainfieldid_{arf_field_id}' inner_class='arf_1col'><div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'><div class='fieldname-row' style='display : block;'><div class='fieldname'><label class='arf_main_label' id='field_{arf_field_id}'><span class='arfeditorfieldopt_label arf_edit_in_place'><input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Rich Text Editor' data-field-id='{arf_field_id}' /></span><span id='require_field_{arf_field_id}'><a href='javascript:void(0);' onclick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title=". esc_html__('Click to mark as not compulsory field', 'ARForms')."></a></span></label></div></div><div class='arf_fieldiconbox' data-field_id='{arf_field_id}'>".$positioned_field_icons[ARF_WYSIWYG_SLUG]."</div><div class='controls'><textarea name='item_meta[{arf_field_id}]' id='itemmeta_{arf_field_id}' onkeyup='arfchangeitemmeta({arf_field_id});' rows='3'></textarea><div class='arf_field_description' id='field_description_{arf_field_id}'></div><div class='help-block'></div></div><input type='hidden' class='arf_field_data_hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_arf_wysiwyg))."' data-field_options='".json_encode($field_order_arf_wysiwyg)."' /><div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'><div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div><div class='arf_field_option_model_container'><div class='arf_field_option_content_row'></div></div><div class='arf_field_option_model_footer'><button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div></div></div></div>";

        return $fields;
    }
    

    function arf_install_wysiwyg_field($fields){
        array_push($fields,'ARF_WYSIWYG_SLUG');
        return $fields;
    }

    function arf_wysiwyg_add_external_fields( $field_types ){

        array_push( $field_types, 'arf_wysiwyg' );
        return $field_types;
    }

    function arf_wysiwyg_admin_footer_js_css( $field_type ){
        if( 'arf_wysiwyg' == $field_type ){
            global $arfversion;
            wp_enqueue_script( 'trumbowyg', ARFURL . '/js/trumbowyg.min.js', array('jquery'), $arfversion );
            wp_enqueue_style( 'trumbowyg', ARFURL . '/css/trumbowyg.min.css', array(), $arfversion );
        }
    }
}
?>