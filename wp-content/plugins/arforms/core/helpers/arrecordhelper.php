<?php

class arrecordhelper {

    function __construct() {
        add_filter('arfemailvalue', array($this, 'email_value'), 10, 3);
    }
    

    function email_value($value, $meta, $entry) {
        global $arffield, $db_record, $arfieldhelper;
        if ($entry->id != $meta->entry_id)
            $entry = $db_record->getOne($meta->entry_id);
        $field = $arffield->getOne($meta->field_id);
        if (!$field)
            return $value;
        $field->field_options = maybe_unserialize($field->field_options);
        switch ($field->type) {
            case 'file':
                $value = $arfieldhelper->get_file_name($value);
                break;
            case 'date':
                $value = $arfieldhelper->get_date_entry($value,$field->form_id,$field->field_options['show_time_calendar'],$field->field_options['clock'],$field->field_options['locale']);
        }
        if (is_array($value)) {
            $new_value = '';
            foreach ($value as $val) {
                if (is_array($val))
                    $new_value .= implode(', ', $val) . "\n";
            }
            if ($new_value != '')
                $value = rtrim($new_value, ',');
        }
        return $value;
    }

    function enqueue_scripts($params) {

        do_action('arfenqueueformscripts', $params);
    }

    function allow_delete($entry) {


        global $user_ID;


        $allowed = false;


        if (current_user_can('arfdeleteentries'))
            $allowed = true;


        if ($user_ID and ! $allowed) {


            if (is_numeric($entry)) {


                global $MdlDb;


                $allowed = $MdlDb->get_var($MdlDb->entries, array('id' => $entry, 'user_id' => $user_ID));
            } else {


                $allowed = ($entry->user_id == $user_ID);
            }
        }
        return apply_filters('arfallowdelete', $allowed, $entry);
    }

    function setup_new_vars($fields, $form = '', $reset = false) {

        global $arfform, $arfsettings, $arfsidebar_width, $arfieldhelper, $armainhelper, $arformhelper;

        $values = array();

        foreach (array('name' => '', 'description' => '', 'entry_key' => '') as $var => $default)
            $values[$var] = $armainhelper->get_post_param($var, $default);


        $values['fields'] = array();

        if ($fields) {

            foreach ($fields as $field) {
                
                $field_options = $field->field_options;
                
                $default = isset($field->field_options['default_value']) ? $field->field_options['default_value'] : '';

                if ($reset)
                    $new_value = $default;
                else
                    $new_value = ($_POST and isset($_POST['item_meta'][$field->id]) and $_POST['item_meta'][$field->id] != '') ? $_POST['item_meta'][$field->id] : $default;



                $is_default = ($new_value == $default) ? true : false;



                if (!is_array($new_value)){
                    $new_value = apply_filters('arfgetdefaultvalue', $new_value, $field);
                }

                if( gettype( $new_value ) == 'string' ){
                    $new_value = str_replace('"', '&quot;', $new_value);
                } else if( gettype( $new_value ) == 'array' || gettype( $new_value ) == 'object' ) {
                    $new_value = $this->arf_recursive_str_replace( $new_value );
                }

                if ($is_default){
                    $field->default_value = $new_value;
                } else {
                    $field->default_value = apply_filters('arfgetdefaultvalue', $field->default_value, $field);
                }


                $field_array = array(
                    'id' => $field->id,
                    'value' => $new_value,
                    'name' => $field->name,
                    'type' => apply_filters('arffieldtype', $field->type, $field, $new_value),
                    'options' => $field->options,
                    'required' => $field->required,
                    'field_key' => $field->field_key,
                    'form_id' => $field->form_id,
                    'option_order' => maybe_unserialize($field->option_order),
                );

                $opt_defaults = $arfieldhelper->get_default_field_options($field_array['type'], $field, true);

                $opt_defaults['required_indicator'] = '';



                foreach ($opt_defaults as $opt => $default_opt) {

                    if ($opt == "confirm_password_label") {
                        $field_array[$opt] = (isset($field->field_options[$opt])) ? $field->field_options[$opt] : $default_opt;
                    } else {
                        $field_array[$opt] = (isset($field->field_options[$opt]) && $field->field_options[$opt] != '') ? $field->field_options[$opt] : $default_opt;
                    }

                    unset($opt);

                    unset($default_opt);
                }



                unset($opt_defaults);



                if ($field_array['size'] == '')
                    $field_array['size'] = $arfsidebar_width;





                if ($field_array['custom_html'] == '')
                    $field_array['custom_html'] = $arfieldhelper->get_basic_default_html($field->type);



                $field_array = apply_filters('arfsetupnewfieldsvars', $field_array, $field);



                foreach ((array) $field->field_options as $k => $v) {

                    if (!isset($field_array[$k]))
                        $field_array[$k] = $v;

                    unset($k);

                    unset($v);
                }



                $values['fields'][] = $field_array;



                if (!$form or ! isset($form->id))
                    $form = $arfform->getOne($field->form_id);
            }



            $form->options = maybe_unserialize($form->options);

            if (is_array($form->options)) {

                foreach ($form->options as $opt => $value)
                    $values[$opt] = $armainhelper->get_post_param($opt, $value);
            }

            if (!isset($values['custom_style']))
                $values['custom_style'] = ($arfsettings->load_style != 'none');



            if (!isset($values['email_to']))
                $values['email_to'] = '';



            if (!isset($values['submit_value']))
                $values['submit_value'] = $arfsettings->submit_value;



            if (!isset($values['success_msg']))
                $values['success_msg'] = $arfsettings->success_msg;



            if (!isset($values['akismet']))
                $values['akismet'] = '';



            if (!isset($values['before_html']))
                $values['before_html'] = $arformhelper->get_default_html('before');



            if (!isset($values['after_html']))
                $values['after_html'] = $arformhelper->get_default_html('after');
        }

        

        return apply_filters('arfsetupnewentry', $values);
    }

    function encode_value($line, $from_encoding, $to_encoding) {

        $convmap = false;

        switch ($to_encoding) {
            case 'macintosh':
                $convmap = array(
                    256, 304, 0, 0xffff,
                    306, 337, 0, 0xffff,
                    340, 375, 0, 0xffff,
                    377, 401, 0, 0xffff,
                    403, 709, 0, 0xffff,
                    712, 727, 0, 0xffff,
                    734, 936, 0, 0xffff,
                    938, 959, 0, 0xffff,
                    961, 8210, 0, 0xffff,
                    8213, 8215, 0, 0xffff,
                    8219, 8219, 0, 0xffff,
                    8227, 8229, 0, 0xffff,
                    8231, 8239, 0, 0xffff,
                    8241, 8248, 0, 0xffff,
                    8251, 8259, 0, 0xffff,
                    8261, 8363, 0, 0xffff,
                    8365, 8481, 0, 0xffff,
                    8483, 8705, 0, 0xffff,
                    8707, 8709, 0, 0xffff,
                    8711, 8718, 0, 0xffff,
                    8720, 8720, 0, 0xffff,
                    8722, 8729, 0, 0xffff,
                    8731, 8733, 0, 0xffff,
                    8735, 8746, 0, 0xffff,
                    8748, 8775, 0, 0xffff,
                    8777, 8799, 0, 0xffff,
                    8801, 8803, 0, 0xffff,
                    8806, 9673, 0, 0xffff,
                    9675, 63742, 0, 0xffff,
                    63744, 64256, 0, 0xffff,
                );
                break;
            case 'ISO-8859-1':
                $convmap = array(256, 10000, 0, 0xffff);
                break;
        }

        if (is_array($convmap))
            $line = mb_encode_numericentity($line, $convmap, $from_encoding);


        if ($to_encoding != $from_encoding)
            return iconv($from_encoding, $to_encoding . '//IGNORE', $line);
        else
            return $line;
    }

    function display_value($value, $field, $atts = array(),$form_css = array(), $incomplete_entry = false, $form_name = '') {

        global $wpdb, $arfieldhelper, $armainhelper, $MdlDb;
        
        $entry_table = $MdlDb->entries;
        $entry_meta_table = $MdlDb->entry_metas;
        
        if( true == $incomplete_entry ){
            $entry_table = $MdlDb->incomplete_entries;
            $entry_meta_table = $MdlDb->incomplete_entry_metas;
        }
        $defaults = array(
            'type' => '', 'show_icon' => true, 'show_filename' => true,
            'truncate' => false, 'sep' => ', ', 'attachment_id' => 0, 'form_id' => $field->form_id,
            'field' => $field
        );

        $atts = wp_parse_args($atts, $defaults);

        $field->field_options = maybe_unserialize($field->field_options);

        if (!isset($field->field_options['post_field']))
            $field->field_options['post_field'] = '';


        if (!isset($field->field_options['custom_field']))
            $field->field_options['custom_field'] = '';


        if ($value == '' && 'matrix' != $field->type){
            $value = '-';
            return $value;
        }


        $value = maybe_unserialize($value);


        if (is_array($value))
            $value = stripslashes_deep($value);


        $value = apply_filters('arfdisplayvaluecustom', $value, $field, $atts);
        

        $new_value = '';


        if (is_array($value)) {


            foreach ($value as $val) {


                if (is_array($val)) {

                    $new_value .= implode($atts['sep'], $val);


                    if ($atts['type'] != 'data')
                        $new_value .= "<br/>";
                }


                unset($val);
            }
        }
        
        if (!empty($new_value)){
            $value = $new_value;
        } else if (is_array($value)){
            $value = implode($atts['sep'], $value);
        }


        if ($atts['truncate'] && $atts['type'] != 'image' && $atts['type'] != 'select' && $atts['type'] != 'arf_wysiwyg'){
            $value = $armainhelper->truncate($value, 50);
        }
        
        if ($atts['type'] == 'image') {
            $value = '<img src="' . $value . '" height="50px" alt="" />';
        } else if ($atts['type'] == 'file') {
            $old_value = explode('|', $value);
            $value = '';
            
            foreach ($old_value as $val) {

                $url = wp_get_attachment_url($val);
                
                if( !empty( $url ) ){
                    if ($atts['show_icon']){
                        $value .= $arfieldhelper->get_file_icon($val);
                    }

                    if ($atts['show_icon'] and $atts['show_filename']){
                        $value .= '<br/>';
                    }

                    if ($atts['show_filename'] && !$atts['show_icon']){
                        $value .= $arfieldhelper->get_file_name($val);
                    }
                } else {
                    $value = '-';
                }
            }
        }else if ($atts['type'] == 'date') {
            
            $value = $arfieldhelper->get_date_entry($value,$field->form_id,$field->field_options['show_time_calendar'],$field->field_options['clock'],$field->field_options['locale']);
        } else if ($atts['type'] == 'time') {
            $value = date_i18n(get_option('time_format'), strtotime($value));
        } else if ($atts['type'] == 'textarea' || $atts['type'] == 'arf_wysiwyg' ) {
            $value = nl2br($value);
        } else if ($atts['type'] == 'like') {
            if ($value !== '') {

                $class = ($value == '1') ? 'arf_like_btn' : 'arf_dislike_btn';
                $like_bgcolor = ($value == '1') ? (isset($form_css['arflikebtncolor']) ? $form_css['arflikebtncolor'] : '#4786FF') : (isset($form_css['arfdislikebtncolor']) ? $form_css['arfdislikebtncolor'] : '#EC3838');
                if ($value == '1'){
                    $value = '<label style="margin-left:0;background:' . $like_bgcolor . ';" class="' . $class . ' active  field_in_list"><img src="' . ARFURL . '/images/like-icon.png" alt="' . addslashes(esc_html__('Like', 'ARForms')) . '" /></label>';
                } else {
                    $value = '<label style="margin-left:0;background:' . $like_bgcolor . ';" class="' . $class . ' active  field_in_list"><img src="' . ARFURL . '/images/dislike-icon.png" alt="' . addslashes(esc_html__('Dislike', 'ARForms')) . '" /></label>';
                }
            }
        }
        
        if ($field->type == 'select' || $field->type == 'checkbox' || $field->type == 'radio' || $field->type == 'arf_autocomplete' || $field->type == 'arf_multiselect') {

            $field_entry_value = array();

            $field_values = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $entry_meta_table . " WHERE field_id='%d' AND entry_id='%d'", $field->id, $atts['entry_id']));

            $field_entry_value = maybe_unserialize( $field_values->entry_value );

            if( true == $incomplete_entry && !is_array( $field_entry_value ) ){
                $field_entry_value = explode( ',', $field_entry_value );
            }
            if( !empty( $field_entry_value ) ){

                $db_field_opts = arf_json_decode( $field->options, true );

                if( 1 == $field->field_options['separate_value'] ){
                    if( 'checkbox' == $field->type || 'arf_multiselect' == $field->type ){
                        $temp_value = '';
                        if( !is_array( $field_entry_value ) ){
                            $field_entry_value = (array) $field_entry_value;
                        }
                        foreach( $field_entry_value as $entry_val ){
                            foreach( $db_field_opts as $db_opt_val ){
                                if( !empty( $entry_val ) && $entry_val == $db_opt_val['value'] ){
                                    $temp_value .= stripslashes($db_opt_val['value'])." (".stripslashes($db_opt_val['label'])."), ";
                                }
                            }
                        }
                        $temp_value = trim($temp_value);
                        $value = rtrim($temp_value, ",");
                    } else {
                        foreach( $db_field_opts as $db_opt_val ){
                            if( $field_entry_value == $db_opt_val['value'] ){
                                $value = stripslashes_deep( $db_opt_val['value'] )." (" . stripslashes_deep( $db_opt_val['label'] ) .")";
                            }
                        }
                    }
                } else {
                    if( 'checkbox' == $field->type || 'arf_multiselect' == $field->type ){
                        $temp_value = '';
                        if( !is_array( $field_entry_value ) ){
                            $field_entry_value = (array)$field_entry_value;
                        }
                        foreach( $field_entry_value as $entry_val ){
                            if( !empty( $entry_val ) ){
                                $temp_value .= stripslashes_deep( $entry_val ) . ", ";
                            }
                        }
                        $temp_value = trim( $temp_value );
                        $value = rtrim( $temp_value, "," );
                    } else {
                        if( is_array( $field_entry_value ) ){
                            $value = implode( ',', $field_entry_value );
                        } else {
                            $value = $field_entry_value;
                        }

                    }
                }

            } else {
                $value = '-';
            }

        }

        if( 'arf_repeater' == $field->type ){
            if( $incomplete_entry ){
                $value = '<a href="javascript:void(0)" onclick="open_incomplete_repeater_entry_thickbox('.$atts['entry_id'].','.$field->id.',\''.htmlentities($form_name, ENT_QUOTES).'\')" data-entry-id="'.$atts['entry_id'].'" data-field-id="'.$field->id.'">'.esc_html__('View Data','ARForms').'</a>';
            } else {
                $value = '<a href="javascript:void(0)" onclick="open_repeater_entry_thickbox('.$atts['entry_id'].','.$field->id.',\''.htmlentities($form_name, ENT_QUOTES).'\')" data-entry-id="'.$atts['entry_id'].'" data-field-id="'.$field->id.'">'.esc_html__('View Data','ARForms').'</a>';
            }
        }

        if( 'matrix' == $field->type ){
            if( $incomplete_entry ){
                $value = '<a href="javascript:void(0)" onclick="open_matrix_incomplete_entry_thickbox('.$atts['entry_id'].', '.$field->id.',\''.htmlentities($form_name, ENT_QUOTES).'\')" data-entry-id="'.$atts['entry_id'].'" data-field-id="'.$field->id.'">'.esc_html__('View Matrix Data','ARForms').'</a>';
            } else {
                $value = '<a href="javascript:void(0)" onclick="open_matrix_entry_thickbox('.$atts['entry_id'].', '.$field->id.',\''.htmlentities($form_name, ENT_QUOTES).'\')" data-entry-id="'.$atts['entry_id'].'" data-field-id="'.$field->id.'">'.esc_html__('View Matrix Data','ARForms').'</a>';
            }
        }


        return apply_filters('arfdisplayvalue', $value, $field, $atts);
    }

    function display_value_with_edit($value, $field, $atts = array(),$incomplete = false, $inner_counter = '') {

        $is_remove_icon_with_edit = 0;
        if($field->type=='signature'){
            $is_remove_icon_with_edit = 1;
        }
        global $wpdb, $arfieldhelper, $armainhelper, $MdlDb;

        $entry_table = $MdlDb->entries;
        $entry_meta_table = $MdlDb->entry_metas;
        if( true == $incomplete ){
            $entry_table = $MdlDb->incomplete_entries;
            $entry_meta_table = $MdlDb->incomplete_entry_metas;
        }

        $form_css = $wpdb->get_row($wpdb->prepare('SELECT form_css FROM '.$MdlDb->forms.' WHERE id=%d',$field->form_id));
        $form_css = maybe_unserialize( $form_css->form_css );
        
        $defaults = array(
            'type' => '', 'show_icon' => true, 'show_filename' => true,
            'truncate' => false, 'sep' => ', ', 'attachment_id' => 0, 'form_id' => $field->form_id,
            'field' => $field
        );


        $atts = wp_parse_args($atts, $defaults);

        $field->field_options = arf_json_decode($field->field_options,true);

        if (!isset($field->field_options['post_field'])){
            $field->field_options['post_field'] = '';
        }

        if (!isset($field->field_options['custom_field'])){
            $field->field_options['custom_field'] = '';
        }

        $value = maybe_unserialize($value);


        if (is_array($value)){
            $value = stripslashes_deep($value);
        }


        $value = apply_filters('arfdisplayvaluecustom', $value, $field, $atts);


        $new_value = '';


        if (is_array($value)) {
            foreach ($value as $val) {
                if (is_array($val)) {
                    $new_value .= implode($atts['sep'], $val);
                    if ($atts['type'] != 'data'){
                        $new_value .= "<br/>";
                    }
                }
                unset($val);
            }
        }

        $data_attr = '';
        $data_id_attr = '';
        if( '' != (string)$inner_counter ){
            $data_attr = ' data-counter="' . $inner_counter .'" ';
            $data_id_attr = '_' . $inner_counter;
        }

        $arf_ccfield = isset( $field->field_options['type2'] ) ? $field->field_options['type2'] : '';

        if ($field->type == 'email') {
            $temp_value = is_array($value) ? implode(',',$value) : $value;
            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-type="text" data-id="' . $field->id . '" '.$data_attr.' data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . $data_id_attr. '" class="arf_editable_values_container arf_edit_type_text" '.$data_attr.' data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $temp_value . '</span>';
        } elseif ($field->type == 'textarea') {
            $temp_value = is_array($value) ? implode(',',$value) : $value;
            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-id="' . $field->id . '" '.$data_attr.' data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . $data_id_attr . '" class="arf_editable_values_container arf_edit_type_text" '.$data_attr.' data-type="textarea" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $temp_value . '</span>';
        } else if($field->type == 'arf_wysiwyg'){
            $temp_value = is_array($value) ? implode(',',$value) : $value;
            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-id="' . $field->id . '" '.$data_attr.' data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . $data_id_attr . '" class="arf_editable_values_container arf_edit_type_text" '.$data_attr.' data-type="textarea" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $temp_value . '</span>';
        } else if($field->type == 'html'){
            $temp_value = is_array($value) ? implode(',',$value) : $value;
            $value = '<span class="arf_not_editable_values_container"><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . $data_id_attr . '"'.$data_attr.' data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $temp_value . '</span></span>';
        } else {
            if ($field->type == 'text' || $field->type == 'password' || $field->type == 'url' || $field->type == 'phone' || ($arf_ccfield != 'ccfield' && $field->type == 'number') || $field->type == 'time' || $field->type == 'hidden' || $field->type == 'arf_spinner') {
                
                $temp_value = is_array($value) ? implode(',',$value) : $value;
                $dataftype = '';

                if ($field->type == 'number') {
                    $dataftype = $field->type;
                }
                
                $data_step   = '';
                $data_clock    = '';    
                $data_default_hour ='';
                $data_default_minutes = '';
                $data_default_value = '';
                if( $field->type == 'time' ){
                    $dataftype = 'time';
                    $data_step = isset($field->field_options['step']) ? $field->field_options['step'] : "";
                    $data_clock = isset($field->field_options['clock']) ? $field->field_options['clock'] : "";
                    $data_default_hour = isset($field->field_options['default_hour']) ? $field->field_options['default_hour'] : "";
                    $data_default_minutes = isset($field->field_options['default_minutes']) ? $field->field_options['default_minutes'] : "";
                    $data_default_value = isset($field->field_options['default_value']) ? $field->field_options['default_value'] : "";
                }
                $value = '<span class="arf_editable_entry_icon_wrapper"><a data-type="text" data-id="' . $field->id . '" '.$data_attr.' data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . $data_id_attr . '" class="arf_editable_values_container arf_edit_type_text" data-id="' . $field->id . '" '.$data_attr.' data-ftype="'.$dataftype.'" data-step="'.$data_step.'" data-clock="'.$data_clock.'" data-default_hour="'.$data_default_hour.'" data-default_minutes="'.$data_default_minutes.'" data-default_value="'.$data_default_value.'" data-entry-id="' . $atts['entry_id'] . '">' . $temp_value . '</span>';
            
            }
        }

        if (!empty($new_value))
            $value = $new_value;


        else if (is_array($value))
            $value = implode($atts['sep'], $value);


        if ($atts['truncate'] and $atts['type'] != 'image' and $atts['type'] != 'select')
            $value = $armainhelper->truncate($value, 50);

        if ($atts['type'] == 'image') {
            $value = '<span class="arf_not_editable_values_container"><img src="' . $value . '" height="50px" alt="" /></span>';
        } else if ($atts['type'] == 'file') {

            $old_value = explode('|', $value);

            $value = '';
            $url='';
            
            foreach ($old_value as $val) {
                $value .= '<div class="arf_file_inner_div arf_file_inner_container_'.$val.'">';
                $url = wp_get_attachment_url($val);
                if( !empty( $val ) && !empty( $url ) ){
                    $value .= '<span class="arf_deletable_entry_icon_wrapper arf_file_inner_'.$val.'"><a data-id="'.$field->id.'" data-entry-id="' . $atts['entry_id'] . '" data-file="'.$val.'" class="arf_file_remove" style="cursor:pointer"><svg height="28" width="28"><g><path fill="#FF0000" d="M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z"></path></g></svg></a></span>';
                    if ($atts['show_icon'])
                        $value .= $arfieldhelper->get_file_icon($val);

                    if ($atts['show_icon'] and $atts['show_filename'])
                        $value .= '<br/>';

                    if ($atts['show_filename'] && !$atts['show_icon'])
                        $value .= $arfieldhelper->get_file_name($val);


                } else {
                    $value .= '<span class="arf_not_editable_values_container">-</span>';
                }
                $value .= '</div>';
               
            }
            if($url){
                
                $value = '<span class="arf_not_editable_values_container arf_file_viewer arf_file_inner_'.$old_value[0].'" id="arf_file_inner_'.$old_value[0].'">'.$value.'</span>';
            }
        }
        else if ($atts['type'] == 'date') {

            $date_value = $value;
            $value = $arfieldhelper->get_date_entry($value,$field->form_id,$field->field_options['show_time_calendar'],$field->field_options['clock'],$field->field_options['locale']);

            $date_format = $this->arf_get_date_field_format( $field, $form_css );

            $format_data = json_decode( $date_format );

            $final_date_format = $format_data->final_date_format;

            $start_date = $format_data->start_date;
            $end_date = $format_data->end_date;
            $new_format = $format_data->date_new_format;
            

            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-type="text" data-id="' . $field->id . '" '.$data_attr.' data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . $data_id_attr.'" '.$data_attr.' data-show-cal="'.$field->field_options['show_time_calendar'].'" data-clock="'.$field->field_options['clock'].'" data-lang="'.$field->field_options['locale'].'" data-step="'.$field->field_options['step'].'" data-css-format="'.$form_css['date_format'].'" data-format="'.$final_date_format.'" class="arf_editable_values_container arf_edit_type_text" data-ftype="date" data-start-date="' . $start_date . '" data-end-date="' . $end_date . '" data-default-date="' . $value . '" data-moment-format="'.$new_format.'" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';

        } else if ($atts['type'] == 'textarea') {
            $value = nl2br($value);
        } else if ($atts['type'] == 'like') {
            if( 0 === $value ){
                $value = '0';
            }
            
            if ($value != '') {
                $class = ($value == '1') ? 'arf_like_btn' : 'arf_dislike_btn';
                $like_bgcolor = ($value == '1') ?  $form_css['arflikebtncolor'] : $form_css['arfdislikebtncolor'];

                if ($value == '1')
                    $value = '<span class="arf_not_editable_values_container"><label style="margin-left:0;background:' . $like_bgcolor . ';" class="' . $class . ' active field_in_list"><img src="' . ARFURL . '/images/like-icon.png" /></label></span>';
                else
                    $value = '<span class="arf_not_editable_values_container"><label style="margin-left:0;background:' . $like_bgcolor . ';" class="' . $class . ' active field_in_list"><img src="' . ARFURL . '/images/dislike-icon.png" /></label></span>';
            }
        }

        if (($arf_ccfield != 'ccfield'  && $field->type == 'select') || $field->type == 'checkbox' || $field->type == 'radio' || $field->type == 'arf_autocomplete' || $field->type == 'arf_multiselect') {

            if( (string)$inner_counter == '' ){
                $field_opts = wp_cache_get('arf_form_entry_'.$field->id);
                if( false == $field_opts ){
                    $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $entry_meta_table . " WHERE field_id='%d' AND entry_id='%d'", $field->id, $atts['entry_id']));
                    wp_cache_set('arf_form_entry_'.$field->id, $field_opts);
                }

                $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $entry_meta_table . " WHERE field_id='%d' AND entry_id='%d'", "-".$field->id, $atts['entry_id']));

                if (!empty($field_opts)) {
                $field_opts = maybe_unserialize($field_opts->entry_value);
                    
                    if ($field->type == 'checkbox' || $field->type == 'arf_multiselect') {
                        if($field->field_options['separate_value']==1){
                            $temp_value = "";
                            if (is_array($field_opts) && count($field_opts) > 0) {

                                foreach ($field->field_options['options'] as $key => $val) {
                                
                                    if(isset($val['value']) && is_array($field_opts)){

                                        foreach ($field_opts as $foptkey => $foptvalue) {
                                
                                            if( $val['value'] == $foptvalue['value']){

                                                $temp_value .= stripslashes($val['value']) . " (" . stripslashes($val['label']) . "), ";
                                
                                            }
                                       }
                                    }
                                }
                                
                                $temp_value = trim($temp_value);
                                $value = rtrim($temp_value, ",");
                                $value = str_replace('^|^',',',$value);
                                $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="checkbox" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="checklist" data-pk="1" data-separate-value="'.$field->field_options['separate_value'].'" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                            }
                        } else {
                            $temp_value = '';
                            if( is_array($field_opts) && count($field_opts) > 0 ){
                                foreach ($field_opts as $val){
                                    if( !empty( $val['label'] ) ){
                                        $temp_value .= $val['label'].", ";
                                    }
                                }    
                            } else {
                                $temp_value .= $field_opts;                        
                            }

                            $temp_value = trim($temp_value);
                            $value      = rtrim($temp_value, ", ");
                            $value = str_replace('^|^',',',$value);
                            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="checkbox" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="checklist" data-pk="1" data-separate-value="'.$field->field_options['separate_value'].'" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                        }
                        
                    } else {

                        if ($field->type == 'select' || $field->type == 'radio' || $field->type == 'arf_autocomplete' ) {
 
                            if ($field->field_options['separate_value'] == 1) {
                                
                                foreach ( $field->field_options['options'] as $key => $val ){
                                    if( $val['value'] == $field_opts['value'] ){
                                        $value  = stripslashes($val['value'])." (".stripslashes_deep($val['label']).")";
                                    }
                                }
                                
                            } else {
                                $value  = stripslashes_deep( $field_opts['label'] );
                            }

                            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="'.$field->type.'" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="'.$field->field_options['separate_value'].'" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                        } else {
                            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="'.$field->type.'" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="'.$field->field_options['separate_value'].'" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . stripslashes($field_opts['label']) . ' (' . stripslashes($field_opts['value']) . ')' . '</span>';
                        }
                    }
                } else {

                    if ($field->type == 'select') {
                        $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="select" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="0" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                    }

                    if ($field->type == 'arf_multiselect') {
                        $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="arf_multiselect" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="checklist" data-pk="1" data-separate-value="0" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                    }

                    if ($field->type == 'radio') {
                        $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="radio" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="0" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                    }

                    if ($field->type == 'checkbox') {
                        $value = '<span class="arf_editable_entry_icon_wrapper"><a  data-field-type="checkbox" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="checklist" data-pk="1" data-separate-value="0" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                    }

                    if ($field->type == 'arf_autocomplete') {
                        $value = '<span class="arf_editable_entry_icon_wrapper"><a  data-field-type="arf_autocomplete" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="0" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                    }
                }
            } else {
                /* reputelog - data check here */
                if( preg_match( '/\[ARF_INNER_JOIN\]/', $value ) ){
                    $inner_value = explode( '[ARF_INNER_JOIN]', $value );
                } else {
                    $inner_value = $value;
                }
                
                $separate_value = $field->field_options['separate_value'];

                if( 'checkbox' == $field->type || $field->type == 'arf_multiselect'){
                    $temp_value = "";

                    if( $separate_value == 1 ){
                        if( preg_match('/\,/', $inner_value) ){
                            $inner_value = explode( ',', trim( $inner_value ) );
                            $inner_value = array_map('trim', $inner_value);
                        }
                        
                        foreach( $field->field_options['options'] as $key => $val ){
                            if( isset( $val['value'] ) ){
                                if( is_array( $inner_value ) && in_array( $val['value'], $inner_value ) ){
                                    $temp_value .= stripslashes( $val['label'] ) . " (" . stripslashes($val['value']) . "), ";
                                } else if( $val['value'] == $inner_value ){
                                    $temp_value .= stripslashes( $val['label'] ) . " (" . stripslashes($val['value']) . "), ";
                                }
                            }
                        }

                        $temp_value = trim( $temp_value );

                        $value = rtrim( $temp_value, "," );
                        $value = str_replace( '^|^', ',', $value );
                        $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="'.$field->type.'" data-id="' . $field->id . '" '.$data_attr.' data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '_'.$inner_counter.'" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-field-type="'.$field->type.'" data-type="checklist" '.$data_attr.' data-pk="1" data-separate-value="1" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                    } else {
                        $temp_value = '';
                        if( is_array($inner_value) && count($inner_value) > 0 ){
                            foreach ($inner_value as $val){
                                $temp_value .= $val.", ";
                            }
                        } else {
                            $temp_value .= $inner_value;                        
                        }

                        $temp_value = trim($temp_value);
                        $value      = rtrim($temp_value, ", ");
                        $value = str_replace('^|^',',',$value);
                        $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="'.$field->type.'" data-id="' . $field->id . '" '.$data_attr.' data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '_'.$inner_counter.'" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="checklist" data-field-type="'.$field->type.'" '.$data_attr.' data-pk="1" data-separate-value="0" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                    }
                } else {
                    if ($field->type == 'select' || $field->type == 'radio' || $field->type == 'arf_autocomplete' ) {

                        if ($field->field_options['separate_value'] == 1) {
                            $temp_value = '';
                            foreach( $field->field_options['options'] as $key => $val ){
                                if( isset( $val['value'] ) ){
                                    if( is_array( $inner_value ) && in_array( $val['value'], $inner_value ) ){
                                        $temp_value .= stripslashes_deep( $val['label'] ) . " (" . stripslashes( $val['value'] ) . "), ";
                                    } else if( $val['value'] == $inner_value ){
                                        $temp_value .= stripslashes_deep( $val['label'] ) . " (" . stripslashes( $val['value'] ) . "), ";
                                    }
                                }
                            }
                            $value = $temp_value;
                        } else {
                            if( is_array( $inner_value ) && count( $inner_value ) > 0 ){
                                $temp_value = '';
                                foreach( $inner_value as $val ){
                                    $temp_value .= $val.', ';
                                }
                                $value = $temp_value;
                            } else {
                                $value = $inner_value;
                            }
                        }

                        $value =  trim( $value );
                        $value = rtrim( $value, ", " );
                        $value = str_replace( '^|^', ',', $value );

                        $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="'.$field->type.'" '.$data_attr.' data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '_'.$inner_counter.'" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="'.$field->field_options['separate_value'].'" '.$data_attr.' data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                    } else {
                        $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="'.$field->type.'" '.$data_attr.' data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '_'.$inner_counter.'" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="'.$field->field_options['separate_value'].'" '.$data_attr.' data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . stripslashes($field_opts['label']) . ' (' . stripslashes($field_opts['value']) . ')' . '</span>';
                    }
                }
            }
        }



        if(in_array($field->type, array('scale','arfslider','colorpicker','arf_smiley','arf_switch')) || $arf_ccfield == 'ccfield'){
            $value = "<span class='arf_not_editable_values_container'>".$value."</span>";
        }
        if($is_remove_icon_with_edit==1){
            $atts['is_remove_icon_with_edit'] = 1;
        }
        return apply_filters('arfdisplayvalue', $value, $field, $atts);
    }

    function get_post_or_entry_value($entry, $field, $atts = array(), $is_for_mail = false) {

        global $arfrecordmeta;



        if (!is_object($entry)) {


            global $db_record;


            $entry = $db_record->getOne($entry);
        }



        $field->field_options = maybe_unserialize($field->field_options);



        if ($entry->attachment_id) {


            if (!isset($field->field_options['custom_field']))
                $field->field_options['custom_field'] = '';





            if (!isset($field->field_options['post_field']))
                $field->field_options['post_field'] = '';





            $links = true;


            if (isset($atts['links']))
                $links = $atts['links'];

            $value = $arfrecordmeta->get_entry_meta_by_field($entry->id, $field->id, true, $is_for_mail);
        }else {

            $value = $arfrecordmeta->get_entry_meta_by_field($entry->id, $field->id, true, $is_for_mail);
        }


        return $value;
    }

    function arf_get_date_field_format( $field, $form_css ){


        $newarr = $form_css;
        $date_format = $form_css['date_format'];
        $wp_format_date = get_option('date_format');
        
        foreach( $field->field_options as $k => $value ){
            $field->$k = $value;
        }

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            if ($field->arfnewdateformat == 'MMMM D, YYYY') {
                $defaultdate_format = 'F d, Y';
            } elseif ($field->arfnewdateformat == 'MMM D, YYYY') {
                $defaultdate_format = 'M d, Y';
            } else {
                $defaultdate_format = 'm/d/Y';
            }
        } else if ($wp_format_date == 'd/m/Y') {
            if ($field->arfnewdateformat == 'D MMMM, YYYY') {
                $defaultdate_format = 'd F, Y';
            } elseif ($field->arfnewdateformat == 'D MMM, YYYY') {
                $defaultdate_format = 'd M, Y';
            } else {
                $defaultdate_format = 'd/m/Y';
            }
        } else if ($wp_format_date == 'Y/m/d') {
            if ($field->arfnewdateformat == 'YYYY, MMMM D') {
                $defaultdate_format = 'Y, F d';
            } elseif ($field->arfnewdateformat == 'YYYY, MMM D') {
                $defaultdate_format = 'Y, M d';
            } else {
                $defaultdate_format = 'Y/m/d';
            }
        } else if($wp_format_date == 'd.F.y' || $wp_format_date == 'd.m.Y' || $wp_format_date == 'Y.m.d' || $wp_format_date == 'd. F Y'){

            if($field->arfnewdateformat == 'D.MM.YYYY'){
               $defaultdate_format = 'd.m.Y';
            } else if($field->arfnewdateformat == 'D.MMMM.YY'){
               $defaultdate_format = 'd.F.y';
            } else if($field->arfnewdateformat == 'YYYY.MM.D'){
                $defaultdate_format = 'Y.m.d';
            } else if($field->arfnewdateformat == 'D. MMMM YYYY'){
                $defaultdate_format = 'd. F Y';
            }
        } else {
            if ($field->arfnewdateformat == 'MMMM D, YYYY') {
                $defaultdate_format = 'F d, Y';
            } elseif ($field->arfnewdateformat == 'MMM D, YYYY') {
                $defaultdate_format = 'M d, Y';
            } elseif ($field->arfnewdateformat == 'YYYY/MM/DD') {
                $defaultdate_format = 'Y/m/d';
            } elseif ($field->arfnewdateformat == 'MM/DD/YYYY') {
                $defaultdate_format = 'm/d/Y';
            } else {
                $defaultdate_format = 'd/m/Y';
            }
        }


        $show_year_month_calendar = "true";

        if (isset($field->show_year_month_calendar) && $field->show_year_month_calendar < 1) {
            $show_year_month_calendar = "false";
        }

        $show_time_calendar = "true";
        
        if ( !isset( $field->show_time_calendar ) || $field->show_time_calendar < 1) {
            $show_time_calendar = "false";
        }

        $arf_show_min_current_date = "true";
        if ( ! isset( $field->arf_show_min_current_date ) || $field->arf_show_min_current_date < 1) {
            $arf_show_min_current_date = "false";
        }

        if ($arf_show_min_current_date == "true") {
            $field->start_date = current_time('d/m/Y');
        } else {
            $field->start_date = $field->start_date;
        }

        $arf_show_max_current_date = "true";
        if ( ! isset($field->arf_show_max_current_date) || $field->arf_show_max_current_date < 1) {
            $arf_show_max_current_date = "false";
        }

        if ($arf_show_max_current_date == "true") {
            $field->end_date = current_time('d/m/Y');
        } else {
            $field->end_date = $field->end_date;
        }

        $date = new DateTime();

        
        if( $field->end_date == '' ){
            $field->end_date = date('d/m/Y', strtotime('+50 years'));
        }

        if( $field->start_date == '' ){
            $field->start_date = date('d/m/Y', strtotime('-150 years'));
        }

        $end_date_temp = explode("/", $field->end_date);
        $date->setDate($end_date_temp[2], $end_date_temp[1], $end_date_temp[0]);
        $date1 = new DateTime();
        $start_date_temp = explode("/", $field->start_date);
        $date1->setDate($start_date_temp[2], $start_date_temp[1], $start_date_temp[0]);
        

        if ($newarr['date_format'] == 'MM/DD/YYYY' || $newarr['date_format'] == 'MMMM D, YYYY' || $newarr['date_format'] == 'MMM D, YYYY') {
            $start_date = $date1->format("m/d/Y");
            $end_date = $date->format("m/d/Y");
            $date_new_format = 'MM/DD/YYYY';
        } else if ($newarr['date_format'] == 'DD/MM/YYYY' || $newarr['date_format'] == 'D MMMM, YYYY' || $newarr['date_format'] == 'D MMM, YYYY') {
            $start_date = $date1->format("d/m/Y");
            $end_date = $date->format("d/m/Y");
            $date_new_format = 'DD-MM-YYYY';
        } else if ($newarr['date_format'] == 'YYYY/MM/DD' || $newarr['date_format'] == 'YYYY, MMMM D' || $newarr['date_format'] == 'YYYY, MMM D') {
            $start_date = $date1->format("Y/m/d");
            $end_date = $date->format("Y/m/d");
            $date_new_format = 'YYYY-MM-DD';
        } else {
            $start_date = $date1->format("m/d/Y");
            $end_date = $date->format("m/d/Y");
            $date_new_format = 'MM/DD/YYYY';
            $field->date_format = 'MMM D, YYYY';
        }

        if($newarr['date_format'] == 'MM/DD/YYYY'){
            $date_new_format_main = 'MM/DD/YYYY';
        } else if($newarr['date_format'] == 'DD/MM/YYYY') {
            $date_new_format_main = 'DD/MM/YYYY';
        } else if($newarr['date_format'] == 'YYYY/MM/DD') {
            $date_new_format_main = 'YYYY/MM/DD';
        } else if($newarr['date_format'] == 'MMM D, YYYY') {
            $date_new_format_main = 'MMM D, YYYY';
        } else if($newarr['date_format'] == 'D.MM.YYYY'){
           $date_new_format_main = 'd.m.Y';
        } else if($newarr['date_format'] == 'D.MMMM.YY'){
           $date_new_format_main = 'd.F.y';
        } else if($newarr['date_format'] == 'YYYY.MM.D'){
            $date_new_format_main = 'Y.m.d';
        } else if($field->arfnewdateformat == 'D. MMMM YYYY'){
            $date_new_format_main = 'd. F Y';
        } else  {
            $date_new_format_main = 'MMMM D, YYYY';
        }

        if (isset($field->clock) && $field->clock == '24') {
            $format = 'H:mm';
        } else {
            $format = 'h:mm A';
        }

        $date_formate = $newarr['date_format'];
        if ($show_time_calendar == "true") {
            $field->clock = (isset($field->clock) and $field->clock) ? $field->clock : 'h:mm A';
            $date_new_format_main = $date_new_format_main . ' ' . $format;
            $date_formate .=' ' . $format;
        }
               

        $off_days = array();

        if ($field->off_days != "") {
            $off_days = explode(",", $field->off_days);
        }
        
        return json_encode(
            array(
                'date_new_format_main' => $date_new_format_main,
                'final_date_format' => $date_formate,
                'date_new_format' => $date_new_format,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'time_format' => $format,
                'off_days' => $off_days
            )
        );

    }

    function arf_convert_date_to_en( $date, $from_lang ){

        $json_file = VIEWS_PATH.'/arf_editor_data.json';
        $json_data = file_get_contents($json_file);

        $json_data = json_decode( $json_data );
        
        $locale_data = $json_data->date_locale->$from_lang;
    }

    function arf_recursive_str_replace( $content ){

        $final = array();

        if( !is_array( $content ) ){
            return $content;
        }

        foreach( $content as $k => $v ){

            if( is_array( $v ) ){
                $final[$k] = $this->arf_recursive_str_replace( $v );
            } else {
                $final[$k] = str_replace('"', '&quot;', $v );
            }

        }

        return $final;

    }

}
