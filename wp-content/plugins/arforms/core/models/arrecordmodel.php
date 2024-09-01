<?php
class arrecordmodel {

    function create($values,$return_values = false) {
        global $wpdb, $MdlDb, $arfrecordmeta, $fid, $armainhelper, $db_record, $arfieldhelper, $arfsettings, $arformhelper, $arfcreatedentry,$arformcontroller, $arfform;
        $checkfield_validation = $db_record->validate($values, false, 1);

        if (!is_null($checkfield_validation) && count($checkfield_validation) > 0) {
            return false;
        }
        $form_id = $values["form_id"];
       
        $fields = $arfieldhelper->get_form_fields_tmp(false, $form_id, false, 0);
        $arfall_fields = $fields;

        $posted_item_fields = isset($values["item_meta"]) ? $values["item_meta"] : array();
        $posted_item_fields = apply_filters('arf_trim_values',$posted_item_fields);
        $form_options = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `".$MdlDb->forms."` WHERE id = %d",$form_id) );
        
        $form = $form_options[0];
        $options = maybe_unserialize($form->options);
        
        $field_order = json_decode($options['arf_field_order'],true);
 
        asort($field_order);

        $inner_field_order = json_decode( $options['arf_inner_field_order'], true );

        $conditional_logic = $options['arf_conditional_logic_rules'];
        $tempfields = array();
        
        $removed_repeatable_fields = isset( $values['repeater_removed_fields'] ) ? arf_json_decode( stripslashes_deep( $values['repeater_removed_fields'] ), true ) : '';

        $repeatable_fields_ids = array();
        $section_fields_ids = array();

        $arf_sorted_fields = array();

        if( !empty( $field_order ) && !empty( $fields ) ){
        	$i = 1;
        	$fields_arr = $arformcontroller->arfObjtoArray( $fields );

        	foreach( $field_order as $field_id => $order ){
        		if( preg_match('/^[\d+]+$/', $field_id ) ){
        			$field_arr_key = $arformcontroller->arfSearchArray( $field_id, 'id', $fields_arr );
        			if( '' !== $field_arr_key ){
        				$field_type = $fields_arr[ $field_arr_key ]['type'];
        				if( 'arf_repeater' == $field_type ){
        					$repeatable_fields_ids[] = $field_id;
        				} else if( 'section' == $field_type ){
        					$arf_sorted_fields[] = $fields[$field_arr_key];
        					$section_fields_ids[] = $field_id;
        				} else {
        					$arf_sorted_fields[] = $fields[$field_arr_key];
        				}
        			}
        			$i++;
        		}
        	}
        }
       
        if( !empty( $arf_sorted_fields ) ){
            $fields = $arf_sorted_fields;
        }
        
        $repeatable_fields_ids = array_unique( $repeatable_fields_ids );

        foreach ($fields as $field) {
            $field_conditional_logic = maybe_unserialize($field->conditional_logic);
            if( $field_conditional_logic == "1" ){
                $item_meta_values = isset($values["item_meta"]) ? $values["item_meta"] : array();
                $tempfields = $arfieldhelper->post_validation_filed_display($field, $fields, $item_meta_values,$conditional_logic);
            }
        }

        foreach( $section_fields_ids as $section_key => $section_val ){
            $section_id = $section_val;

            if( in_array($section_id, $tempfields) ){

                $get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `".$MdlDb->fields."` WHERE field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%'", $section_id, $section_id ), ARRAY_A );

                foreach( $get_all_inner_fields as $inner_fields ){
                    
                    $inner_field_id = $inner_fields['id'];
                    
                    array_push( $tempfields, $inner_field_id );
                }
            }
        }

        foreach( $repeatable_fields_ids as $repeatable_key => $repeatable_val ){
            $repeater_id = $repeatable_val;

            if( in_array( $repeater_id, $tempfields ) ){
                $get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `".$MdlDb->fields."` WHERE field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%'", $repeater_id, $repeater_id ), ARRAY_A );

                if( count( $get_all_inner_fields ) > 0 ){
                    foreach( $get_all_inner_fields as $inner_fields ){
                        $inner_field_id = $inner_fields['id'];
                        array_push( $tempfields, $inner_field_id );
                    }
                }
            }
        }
        
        $removed_field_ids = array();
        if(!empty($values['item_meta'])){
           foreach ($values['item_meta'] as $key => $value) {
                if (is_array($tempfields)) {
                    if (in_array($key, $tempfields)) {
                        array_push($removed_field_ids,$key);
                        unset($values['item_meta'][$key]);
                    }
                }
            }
        }
        
        if( isset($_FILES) && isset($tempfields) && count($tempfields) > 0 ){
            foreach( $tempfields as $key => $tmp_val ){
                if( isset($_FILES['file'.$tmp_val]) ){ 
                    unset($_FILES['file'.$tmp_val]);
                }
            }
        }
        
        if( !empty($removed_field_ids) ){
            foreach($fields as $k => $pst_field ){
                if( in_array($pst_field->id,$removed_field_ids) ){
                    unset($fields[$k]);
                } 
            }
            $fields = array_values($fields);
        }
        
        $tmpbreaks = array();
        $tmpdivider = array();
        $allfieldsarr = array();
        $allfieldstype = array();

        foreach ($fields as $key => $postfield) {
            $allfieldsarr[] = $postfield->id;
            $allfieldstype[] = $postfield->type;

            if (is_array($tempfields) and ! empty($tempfields) and in_array($postfield->id, $tempfields)) {
                if (( $postfield->type == 'break' )) {
                    $tmpbreaks[] = $key;
                }
                if (( $postfield->type == 'divider')) {
                    $tmpdivider[] = $key;
                }

            }
        }



        $fieldsarray = array();


        /* Remove Fields from Hidden Page Break */
        foreach( $tmpbreaks as $key => $value ){
            $first = $tmpbreaks[$key];
            $last = isset($tmpbreaks[$key + 1]) ? $tmpbreaks[$key + 1] : count($allfieldsarr) - 1;
            for( $pg = $first; $pg < $last; $pg++ ){
                if( isset($allfieldstype[$pg + 1]) && $allfieldstype[$pg + 1] == 'break' && !in_array($allfieldsarr[$pg + 1], $tempfields) ){
                    $last = $pg + 1;
                }
            }
            for( $x1 = $first; $x1 <= $last; $x1++ ){
                $fieldsarray[] = isset($allfieldsarr[$x1]) ? $allfieldsarr[$x1] : '';
            }
        }

        /* Remove Fields from Hidden Section */
        foreach( $tmpdivider as $key => $value ){
            $first = $tmpdivider[$key];
            $last = isset($tmpdivider[$key + 1]) ? $tmpdivider[$key + 1] : count($allfieldsarr) - 1;
            for( $pd = $first; $pd < $last; $pd++ ){
                if( isset($allfieldstype[$pd + 1]) && ($allfieldstype[$pd + 1] == 'break' || $allfieldstype[$pd + 1] == 'divider') && !in_array($allfieldsarr[$pd + 1], $tempfields) ){
                    $last = $pd + 1;
                }
            }

            for( $x2 = $first; $x2 <= $last; $x2++ ){
                $fieldsarray[] = isset($allfieldsarr[$x2]) ? $allfieldsarr[$x2] : '';
            }
        }

        $fieldsarray = array_values(array_unique($fieldsarray));


        if (isset($fieldsarray) and ! empty($fieldsarray)) {
            foreach ($fieldsarray as $key => $value) {
                unset($values['item_meta'][$value]);
            }
        }

        foreach ($fields as $k => $f) {
            if (isset($fieldsarray) and ! empty($fieldsarray) and is_array($fieldsarray)) {
                if (in_array($f->id, $fieldsarray)) {
                    unset($fields[$k]);
                }
            }
        }

        $fields = apply_filters( 'arf_modify_fields_array_before_validate', $fields, $form_id );
        
        foreach ($fields as $postfield) {
            $field_conditional_logic = maybe_unserialize($postfield->conditional_logic);
            if (isset($field_conditional_logic['enable']) && $field_conditional_logic['enable'] == '1') {
                $display = $arfieldhelper->post_validation_filed_display($postfield, $fields, $values["item_meta"]);
                if ($display == 'true') {
                    if ($postfield->required) {
                        if ($arfsettings->form_submit_type != 1) {
                            if ($postfield->type == "file") {
                                if (isset($_FILES["file" . $postfield->id]["name"]) && $_FILES["file" . $postfield->id]["name"] == '') {
                                    return false;
                                    break;
                                }
                            } else if ($postfield->type == 'number') {
                                if (isset( $posted_item_fields[$postfield->id] ) && $posted_item_fields[$postfield->id] == '') {
                                    return false;
                                    break;
                                }
                            } else {
                                if (isset( $posted_item_fields[$postfield->id] ) && $posted_item_fields[$postfield->id] == '') {
                                    return false;
                                    break;
                                }
                            }
                        } else {
                            if ($postfield->type == 'number') {
                                if (isset( $posted_item_fields[$postfield->id] ) && $posted_item_fields[$postfield->id] == '') {
                                    return false;
                                    break;
                                }
                            } else {
                                if ( isset( $posted_item_fields[$postfield->id] ) && $posted_item_fields[$postfield->id] == '') {
                                    return false;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            else {
                if ($postfield->required) {
                    if ($arfsettings->form_submit_type != 1) {
                        if ($postfield->type == "file") {
                            if (isset($_FILES["file" . $postfield->id]["name"]) && $_FILES["file" . $postfield->id]["name"] == '') {
                                return false;
                                break;
                            }
                        } else if ($postfield->type == 'number') {
                            if (isset( $posted_item_fields[$postfield->id] ) && $posted_item_fields[$postfield->id] == '') {
                                return false;
                                break;
                            }
                        } else {
                            if (isset( $posted_item_fields[$postfield->id] ) && $posted_item_fields[$postfield->id] == '') {
                                return false;
                                break;
                            }
                        }
                    } else {
                        if ($postfield->type == 'number') {
                            if (isset( $posted_item_fields[$postfield->id] ) && $posted_item_fields[$postfield->id] == '') {
                                return false;
                                break;
                            }
                        } else {
                            if (isset($posted_item_fields[$postfield->id]) && $posted_item_fields[$postfield->id] == '') {
                                return false;
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        $values = apply_filters( 'arf_unset_removed_inner_fields', $values );

        foreach( $repeatable_fields_ids as $repeat_key => $repeat_val ){
            $inner_fields = ( isset( $values['item_meta'] ) && isset( $values['item_meta'][$repeat_val] ) ) ? ( is_array($values['item_meta'][$repeat_val]) ? $values['item_meta'][$repeat_val] : array() ) : array();
            
            $max_arr_key_arr = array();
            foreach( $inner_fields as $k => $v ){
                foreach( $v as $ik => $iv ){
                    if( !in_array( $ik, $max_arr_key_arr ) ){
                        array_push($max_arr_key_arr,$ik);
                    }
                }
            }
            if( count( $max_arr_key_arr ) > 0 ){
                $max_arr_key = max( $max_arr_key_arr );
            }
            

            foreach( $inner_fields as $k => $v ){
                $inner_field_id = $k;

                if( preg_match('/(_confirm)/', $inner_field_id ) ){
                    continue;
                }

                $inner_field_options = $wpdb->get_row( $wpdb->prepare("SELECT type, field_options, options FROM `" . $MdlDb->fields ."` WHERE id = %d", $inner_field_id) );

                if( !isset( $inner_field_options ) || empty( $inner_field_options ) ){
                    continue;
                }

                $field_opts = arf_json_decode( $inner_field_options->field_options, true );

                $fopts = json_decode( $inner_field_options->options, true );

                $array_ftype = apply_filters(  'arf_inner_field_types_array', array( 'select', 'checkbox', 'radio', 'arf_autocomplete', 'arf_multiselect' ) );
                if( isset( $removed_repeatable_fields[$inner_field_id] ) ){
                    $removed_inner_keys = explode(',',$removed_repeatable_fields[$inner_field_id]);
                } else {
                    $removed_inner_keys = array();
                }

                if( is_array( $v ) && in_array( $inner_field_options->type, $array_ftype ) ){
                    global $arformcontroller;
                    if( 'true' == $field_opts['separate_value'] || '1' == $field_opts['separate_value'] ){
                        
                        $inv = array();
                        $inx = 0;
                        for( $ink = 0; $ink <= $max_arr_key; $ink++ ){
                            if( in_array($ink, $removed_inner_keys) ){
                                continue;
                            }
                            if( isset( $v[$ink] ) ){
                                $ivar = $v[$ink];
                                if( is_array( $ivar ) ){
                                    foreach( $ivar as $iiv ){
                                        $arr_key = $arformcontroller->arfSearchArray( $iiv, 'value', $fopts );
                                        $inv[$inx][] = $fopts[$arr_key]['value'];
                                    }
                                } else {
                                    $arr_key = $arformcontroller->arfSearchArray( $ivar, 'value', $fopts );
                                    $inv[$inx][] = $fopts[$arr_key]['value'];
                                }
                                if( $inx != $ink ){
                                    unset( $inv[$ink] );
                                }
                            } else {
                                $inv[$inx] = array();
                            }
                            $inx++;
                        }

                        $fiv = array();
                        foreach( $inv as $ni => $in ){
                            $fiv[$ni] = maybe_serialize( $in ).'!|!'.$ni;
                        }
                        $v = $fiv;
                    } else {

                        $inv = array();
                        $inx = 0;
                        for( $ink = 0; $ink <= $max_arr_key; $ink++ ){
                            if( in_array($ink, $removed_inner_keys) ){
                                continue;
                            }
                            if( isset( $v[$ink] ) ){
                                $iv = $v[$ink];
                                if( is_array( $iv ) ){
                                    foreach( $iv as $iiv ){
                                        if( is_array( $iiv ) ){
                                            $inv[$inx][] = implode( ',', $iiv );
                                        } else {
                                            $inv[$inx][] = $iiv;
                                        }
                                    }
                                } else {
                                    $inv[$inx] = $iv;
                                }
                                if( $inx != $ink ){
                                    unset( $inv[$ink] );
                                }
                            } else {
                                $inv[$inx] = array();
                            }
                            $inx++;
                        }
                        $fiv = array();
                        foreach( $inv as $ni => $in ){
                            $fiv[$ni] = maybe_serialize( $in ).'!|!'.$ni;
                        }
                        $v = $fiv;
                    }

                }

                $extra_fields = array('arf_smiley','scale', 'like');

                if( is_array( $v ) && in_array( $inner_field_options->type, $extra_fields ) ){
                    $i = 0;
                    $keys = array_keys( $v );
                    $x = 0;
                    for( $i = 0; $i <= $max_arr_key; $i++ ){
                        if( !empty( $removed_inner_keys) && in_array($i, $removed_inner_keys) ){
                            continue;
                        }
                        
                        if( !isset( $v[$i] ) ){
                            $v[$x] = '';
                        } else {
                            $v[$x] = $v[$i];
                            if( $x != $i ){
                                unset( $v[$i] );
                            }
                        }
                        $x++;
                    }
                    ksort($v);
                }


                if( $inner_field_options->type == 'phone' && !preg_match('/(_country_code)/',$k) && isset( $values['item_meta'][$repeat_val][$k.'_country_code'])  ){
                    $phone_values = $v;
                    $country_codes = array();
                    $phone_code_values = $values['item_meta'][$repeat_val][$k.'_country_code'];
                    foreach( $phone_values as $phk => $phv ){
                        $country_codes[$phk] = $phone_code_values[$phk];
                    }
                    
                    $values['item_meta'][$k.'_country_code'] = implode('[ARF_JOIN]',$country_codes);
                } else if( $inner_field_options->type == 'phone' && preg_match('/(_country_code)/',$k) ){
                    continue;
                } else if( $inner_field_options->type == 'phone' && $field_opts['phone_validation'] == 'custom_validation_5'){
                    $tempval_arr = array();

                    foreach ($v as $vkey => $tempvalue) {
                        $tempval_arr[$vkey] = '+1' .$tempvalue;
                    }
                    $v = array_merge($tempval_arr);
                }

                $values['item_meta'][$k] = implode('[ARF_JOIN]',$v);


            }
            unset( $values['item_meta'][$repeat_val]);
        }
        
        if( isset($return_values) && $return_values == true ){
            return isset($values['item_meta']) ? $values['item_meta'] : array();
        }

        if( apply_filters('arf_prevent_duplicate_entry',false,$form_id,$values) ){
            return false;
        }
        
        $values = apply_filters('arf_before_create_formentry', $values);

        do_action('arfbeforecreateentry', $values);


        $fid = isset($values["form_id"])?$values["form_id"]:'';

        $new_values = array();
        $values['entry_key'] = isset($values['entry_key'])?$values['entry_key']:'';
        $new_values['entry_key'] = $armainhelper->get_unique_key($values['entry_key'], $MdlDb->entries, 'entry_key');

        $field_data_cached = wp_cache_get( 'arf_field_data_from_db' );

        if( false == $field_data_cached ){
            $field_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->fields . " WHERE form_id = %d", $fid));
            wp_cache_set( 'arf_field_data_from_db', $field_data );
        } else {
            $field_data = $field_data_cached;
        }



        if (count($field_data) > 0) {
            foreach ($field_data as $new_field) {
                if ($new_field->type == 'scale') {
                    $values['item_meta'][$new_field->id] = ( isset($values['item_meta'][$new_field->id]) and $values['item_meta'][$new_field->id] != '' ) ? $values['item_meta'][$new_field->id] : 0;
                }
                
                if ($new_field->type == 'arf_autocomplete') {
                    /* if result not found than entry value for autocomplete will be -21. So if entry value is euqual to -21 than replace it with null value */
                    $values['item_meta'][$new_field->id] = ( isset($values['item_meta'][$new_field->id]) and ( $values['item_meta'][$new_field->id]== 'Result not Found'|| $values['item_meta'][$new_field->id] == '-21' )) ? '' : $values['item_meta'][$new_field->id];
                }
                if( $new_field->type == 'phone' ){
                    $temp_field_opts = arf_json_decode( $new_field->field_options );
                    if( 'custom_validation_5' == $temp_field_opts->phone_validation && ( !isset( $temp_field_opts->has_parent ) || $temp_field_opts->has_parent == 0 ) ){
                        $values['item_meta'][$new_field->id] = ( isset($values['item_meta'][$new_field->id]) && '' != $values['item_meta'][$new_field->id] ) ? '+1' . $values['item_meta'][$new_field->id] : '';
                    }
                }
            }
        }

        $new_values['name'] = isset($values['name']) ? $values['name'] : $values['entry_key'];

        if (is_array($new_values['name'])) {
            $new_values['name'] = reset($new_values['name']);
        }

        $new_values['ip_address'] = $_SERVER['REMOTE_ADDR'];

        if (isset($values['description']) and ! empty($values['description'])) {
            $new_values['description'] = $values['description'];
        }
        else {
            $referrerinfo = $armainhelper->get_referer_info();
            $new_values['description'] = maybe_serialize(
                array(
                    'browser'           => $_SERVER['HTTP_USER_AGENT'],
                    'referrer'          => $referrerinfo,
                    'http_referrer'     => isset($values["arf_http_referrer_url"])?$values["arf_http_referrer_url"]:'',
                    'page_url'          => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
                )
            );
        }

        $new_values['browser_info'] = $_SERVER['HTTP_USER_AGENT'];
        $country_name = arf_get_country_from_ip($new_values['ip_address']);

        $new_values['country'] = $country_name;
        $new_values['form_id'] = isset($values['form_id']) ? (int) $values['form_id'] : null;
        $new_values['created_date'] = isset($values['created_date']) ? $values['created_date'] : current_time('mysql');

        if (isset($values['arfuserid']) and is_numeric($values['arfuserid'])) {
            $new_values['user_id'] = $values['arfuserid'];
        }
        else {
            global $user_ID;
            if ($user_ID){
                $new_values['user_id'] = $user_ID;
            }
        }

        if( !isset($new_values['user_id']) || $new_values['user_id'] == null || $new_values['user_id'] == '' ) {
            $new_values['user_id'] = get_current_user_id();
        }

        $create_entry = true;
        
        if ($create_entry) {
            $query_results = $wpdb->insert($MdlDb->entries, $new_values);
        }
        
        if (isset($query_results) and $query_results) {
            $entry_id = $wpdb->insert_id;
            global $arfsavedentries;
            $arfsavedentries[] = (int) $entry_id;
            if (isset($_REQUEST['form_display_type']) and $_REQUEST['form_display_type'] != '') {
                global $wpdb,$MdlDb;
                $arf_meta_insert = array(
                    'entry_value' => arf_sanitize_value($_REQUEST['form_display_type']),
                    'field_id' => arf_sanitize_value('0', 'integer'),
                    'entry_id' => arf_sanitize_value($entry_id, 'integer'),
                    'created_date' => current_time('mysql'),
                );
                $wpdb->insert($MdlDb->entry_metas, $arf_meta_insert, array('%s', '%d', '%d', '%s'));
            }

            if (isset($values['item_meta'])) {
                if(isset($options['arf_twilio_to_number']) && ''!=$options['arf_twilio_to_number']){
                    if(isset($values['item_meta'][$options['arf_twilio_to_number']]) && ''!=trim($values['item_meta'][$options['arf_twilio_to_number']])){
                        $values['item_meta'][$options['arf_twilio_to_number']] = preg_replace("/^0/", "", $values['item_meta'][$options['arf_twilio_to_number']]);    
                    }
                }
                $tmp_key = array();
                foreach ($values['item_meta'] as $key => $value) {
                    if(strpos($key, '_country_code') !== false){
                        $key_id = str_replace("_country_code", "", $key);
                        if(isset($values['item_meta'][$key_id]) && !empty($values['item_meta'][$key_id])){
                            $tmp_key[$key_id] = $value;
                        }
                        unset($values['item_meta'][$key]); 
                    }
                    
                }
                
                foreach ($tmp_key as $key => $value) {
                    if(isset($values['item_meta'][$key])){
                        $value_explode = explode("[ARF_JOIN]", $value);
                        $values_explode = explode("[ARF_JOIN]", $values['item_meta'][$key]);
                        $meta_val = "";
                        for($i=0; $i<count($value_explode); $i++) {
                            $meta_val .= $value_explode[$i]." ".$values_explode[$i]."[ARF_JOIN]";
                        }
                        $values['item_meta'][$key] = arf_sanitize_value(trim($meta_val, '[ARF_JOIN]'));
                    }
                }

                if(isset($_REQUEST['arfform_date_formate_'.$_POST['form_id']]) && '' != $_REQUEST['arfform_date_formate_'.$_POST['form_id']]){
                    $arfrecordmeta->update_entry_metas($entry_id, $values['item_meta'],$_REQUEST['arfform_date_formate_'.$_POST['form_id']], $field_data);
                }else{
                    $arfrecordmeta->update_entry_metas($entry_id, $values['item_meta'],"MMM D, YYYY", $field_data);
                }
            }

            $arfcreatedentry[$_POST['form_id']]['entry_id'] = $entry_id;

            $movable = $arformhelper->manage_uploaded_file_path($form_id);

            foreach ($arfall_fields as $fkey => $fvalue) {

                $images_string = isset($_POST['uploaded_file_name_'. $_POST['form_id'] . '_' . $_POST['form_data_id'] . '_' . $fvalue->id ]) ? $_POST['uploaded_file_name_'. $_POST['form_id'] . '_' . $_POST['form_data_id'] . '_' . $fvalue->id ] : '' ;
                $imagesToUpload = explode(',', $images_string);

                $upload_field_string = (isset($_POST['upload_field_id_' . $_POST['form_id'] . '_' . $_POST['form_data_id']]) && '' != $_POST['upload_field_id_' . $_POST['form_id'] . '_' . $_POST['form_data_id']]) ? explode(',', $_POST['upload_field_id_' . $_POST['form_id'] . '_' . $_POST['form_data_id']]) : array();
                
                if (isset($_REQUEST['using_ajax']) && $_REQUEST['using_ajax'] == 'yes') {
                    
                    foreach ($imagesToUpload as $key => $image) {
                        if ($image != "") {

                            $full_image_name = pathinfo($image);

                            $post_title = preg_replace( '/\.[^.]+$/', '', $full_image_name['filename'] );

                            $image_url = $movable['url'] . $image;
                            
                            $image_path = $movable['path'] . $image;

                            $image_ext = explode('.',$image);

                            $image_ext = $image_ext[count($image_ext) - 1];

                            $image_ext = strtolower($image_ext);

                            $exclude_ext = array('png','jpg','jpeg','jpe','gif','bmp','tif','tiff','ico');

                            $arf_thumbs_image_path = '';
                            if( in_array($image_ext,$exclude_ext) ){
                                $arf_thumbs_image_path = $movable['path'] . 'thumbs/' . $image;
                            }

                            $file_data = wp_check_filetype( $full_image_name['basename'] );

                            $post_mime_type = $file_data['type'];

                            $attachment_args = array(
                                'guid' => $image_path,
                                'post_mime_type' => $post_mime_type,
                                'post_title' => sanitize_text_field(  $post_title ),
                                'post_content' => '',
                                'post_status' => 'inherit'
                            );

                            $upload_id = wp_insert_attachment(
                                $attachment_args,
                                $image_url
                            );

                            require_once( ABSPATH . 'wp-admin/includes/image.php' );
                            require_once( ABSPATH . 'wp-admin/includes/media.php' );

                            $before_meta_data = get_post_meta( $upload_id, '_wp_attached_file', true );

                            wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $image_path ) );
                            
                            $after_meta_data = get_post_meta( $upload_id, '_wp_attached_file', true );

                            if( $before_meta_data != $after_meta_data ){
                                update_post_meta( $upload_id, '_wp_attached_file', $before_meta_data, $after_meta_data );
                            }
                            
                            $post_meta_id = update_post_meta( $upload_id, 'arf_uploaded_file', 'arforms' );

                            $field_id = isset($_POST['field_id']) ? $_POST['field_id'] : "";
                            $upload_field_key = $upload_field_string[$key];
                            
                            $arf_upload_key1 = explode("_", $upload_field_key);

                            $upload_field_id = $wpdb->get_row($wpdb->prepare("select * from " .$MdlDb->fields." where field_key =%s",$arf_upload_key1[0]));
                            $field_id = $fvalue->id;

                            $check_upload_field_available = $wpdb->get_row($wpdb->prepare("select * from " .$MdlDb->entry_metas." where field_id='%d' and entry_id='%d'",$field_id,$arfcreatedentry[$_POST['form_id']]['entry_id']));
                            $new_entry_value_ids = array();
                            if ($check_upload_field_available != '' && $check_upload_field_available->id != '') {
                                $old_entry_value = $check_upload_field_available->entry_value;
                                $old_entry_value_ids = explode('|', $old_entry_value);

                                if (count($old_entry_value_ids) == 1 && !is_numeric($old_entry_value_ids[0])) {
                                    $new_entry_value_ids[] = $upload_id;
                                } else {
                                    $new_entry_value_ids = $old_entry_value_ids;
                                    if (!in_array($upload_id, $old_entry_value_ids)) {
                                        $new_entry_value_ids[] = $upload_id;
                                    }
                                }

                                $new_entry_value_ids = implode('|', $new_entry_value_ids);
                                $wpdb->query('UPDATE ' .$MdlDb->entry_metas.' SET entry_value="' . $new_entry_value_ids . '" WHERE field_id="' . $field_id . '" and entry_id="' . $arfcreatedentry[$_POST['form_id']]['entry_id'] . '"');
                            } else {
                                $wpdb->query('insert into ' .$MdlDb->entry_metas.' (entry_value,field_id,entry_id,created_date) values("' . $upload_id . '","' . $field_id . '","' . $arfcreatedentry[$_POST['form_id']]['entry_id'] . '",NOW())');
                            }

                            if ($arfsettings->form_submit_type == 1) {
                                $arf_junk_files = get_option('arf_remove_junk_files');
                                $arf_junk_files = maybe_unserialize($arf_junk_files);                                
                                if( !empty( $arf_junk_files ) && is_array( $arf_junk_files ) ){

                                    foreach( $arf_junk_files as $uploaded_img_val => $junk_file ){
                                        $junk_file_data = explode( '<|>', $junk_file );
                                        if( $image_path == $junk_file_data[1] ){
                                            unset($arf_junk_files[$uploaded_img_val]);
                                        }

                                        if( !empty( $arf_thumbs_image_path ) && $arf_thumbs_image_path == $junk_file_data[1] ){
                                            unset($arf_junk_files[$uploaded_img_val]);
                                        }
                                    }

                                    $arf_updated_junk_files = $arf_junk_files;
                                    update_option('arf_remove_junk_files', $arf_updated_junk_files);
                                }
                            }
                        }
                    }
                }
            }

            if( isset( $values['arf_file_token_'.$new_values['form_id']] ) && '' != $values['arf_file_token_'.$new_values['form_id']] ){
                $token = $values['arf_file_token_'.$new_values['form_id']];
                unset( $_SESSION['arf_file_' . $token . '_fileuploads'] );
            }
            
            $entry_id = apply_filters('arf_after_create_formentry', $entry_id, $new_values['form_id']);
            
            if ($entry_id == false || $entry_id == '' || !isset($entry_id)) {
                return false;
            }
            wp_cache_delete( 'arf_total_entries_counter_' . $new_values['form_id'] );
            do_action('arfaftercreateentry', $entry_id, $new_values['form_id']);
            return $entry_id;
        }
        else {
            return false;
        }
    }

    function &destroy($id) {


        global $wpdb, $MdlDb;


        $id = (int) $id;

        $id = apply_filters('arf_before_destroy_entry', $id);

        $resl = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$MdlDb->fields." a, ".$MdlDb->entry_metas." b WHERE a.type=%s AND b.entry_id=%d AND b.field_id=a.id GROUP BY b.id", 'file', $id));

        foreach($resl as $res){

            if(isset($res->entry_value) && $res->entry_value != ''){

                $new_ids = array();

                $exp_ids = explode("|", $res->entry_value);

                foreach ($exp_ids as $key => $file_id) {
                    $image_url = "";
                    $thumb_url = '';
                    $post_meta_data = get_post_meta($file_id);

                    if(isset($post_meta_data['_wp_attached_file']) && isset($post_meta_data['_wp_attached_file'][0])){

                        $image_name = explode('/',$post_meta_data['_wp_attached_file'][0]);

                        $image_name = $image_name[count($image_name) -1 ];

                        $image_ext = explode('.',$image_name);

                        $image_ext = $image_ext[count($image_ext) - 1];

                        $image_ext = strtolower($image_ext);

                        $exclude_ext = array('png','jpg','jpeg','jpe','gif','bmp','tif','tiff','ico');

                        if( in_array($image_ext,$exclude_ext) ){
                            if( preg_match( '/http/', $post_meta_data['_wp_attached_file'][0] ) ){
                                $image_url = str_replace('thumbs/', '', $post_meta_data['_wp_attached_file'][0]);
                                $thum_url = $post_meta_data['_wp_attached_file'][0];
                            } else {
                                $image_url = ABSPATH.str_replace('thumbs/', '', $post_meta_data['_wp_attached_file'][0]);
                                $thum_url = ABSPATH.$post_meta_data['_wp_attached_file'][0];
                            }

                        }

                        if(isset($post_meta_data['_wp_attachment_metadata'][0])){

                            $attachment_metadata = maybe_unserialize( $post_meta_data['_wp_attachment_metadata'][0] );

                            if( file_exists( $attachment_metadata['file'] ) ){
                                unlink( $attachment_metadata['file'] );
                                unlink( str_replace( 'userfiles', 'userfiles/thumbs', $attachment_metadata['file'] ) );
                            } else if( file_exists( ABSPATH . $attachment_metadata['file'] ) ){
                                unlink($image_url);
                                unlink($thum_url);                        
                            }
                        }

                        wp_delete_attachment($file_id);
                    }
                }
            }
        }


        

        $wpdb->query($wpdb->prepare('DELETE FROM ' . $MdlDb->entry_metas . ' WHERE entry_id=%d', $id));

        $result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $MdlDb->entries . ' WHERE id=%d', $id));

        $result = apply_filters('arf_after_destroy_entry', $result);

        return $result;
    }

    function getOneIncomplete( $id, $meta = false ){
        global $wpdb, $MdlDb;

        $query = "SELECT it.*, fr.name as form_name, fr.form_key as form_key FROM $MdlDb->incomplete_entries it 

                  LEFT OUTER JOIN $MdlDb->forms fr ON it.form_id=fr.id WHERE ";


        if (is_numeric($id)){
            $query .= $wpdb->prepare('it.id=%d', $id);
        } else {
            $query .= $wpdb->prepare('it.entry_key=%s', $id);
        }

        $entry = $wpdb->get_row($query);

        if ($meta and $entry) {

            global $arfrecordmeta;

            $metas = $arfrecordmeta->getAll("entry_id=$entry->id and field_id != 0", '', '', false, true, '', array(), true);

            $entry_metas = array();

            foreach ($metas as $meta_val){
                if( preg_match( '/\[ARF_JOIN\]/', $meta_val->entry_value) ){
                    $entry_metas_arr = explode( '[ARF_JOIN]', $meta_val->entry_value );
                    $x = 0;
                    foreach( $entry_metas_arr as $emeta_arr ){
                        $entry_metas[$meta_val->field_id][$x]= $entry_metas[$meta_val->field_key][$x] = maybe_unserialize($emeta_arr);
                        $x++;
                    }
                } else {
                    $entry_metas[$meta_val->field_id] = $entry_metas[$meta_val->field_key] = maybe_unserialize($meta_val->entry_value);
                }
            }

            $entry->metas = $entry_metas;
        }

        return stripslashes_deep($entry);
    }

    function getOne($id, $meta = false) {


        global $wpdb, $MdlDb;


        $query = "SELECT it.*, fr.name as form_name, fr.form_key as form_key FROM $MdlDb->entries it 


                  LEFT OUTER JOIN $MdlDb->forms fr ON it.form_id=fr.id WHERE ";


        if (is_numeric($id))
            $query .= $wpdb->prepare('it.id=%d', $id);
        else
            $query .= $wpdb->prepare('it.entry_key=%s', $id);


        $entry = wp_cache_get('arf_get_row_'.$id);
        if( false == $entry ){
            $entry = $wpdb->get_row($query);
            wp_cache_set('arf_get_row_'.$id, $entry);
        }




        if ($meta and $entry) {


            global $arfrecordmeta;


            $metas = $arfrecordmeta->getAll("entry_id=$entry->id and field_id != 0");
            

            $entry_metas = array();

            foreach ($metas as $meta_val){
                if( preg_match( '/\[ARF_JOIN\]/', $meta_val->entry_value) ){
                    $entry_metas_arr = explode( '[ARF_JOIN]', $meta_val->entry_value );
                    $x = 0;
                    foreach( $entry_metas_arr as $emeta_arr ){
                        $entry_metas[$meta_val->field_id][$x]= $entry_metas[$meta_val->field_key][$x] = maybe_unserialize($emeta_arr);
                        $x++;
                    }
                } else {
                    $entry_metas[$meta_val->field_id] = $entry_metas[$meta_val->field_key] = maybe_unserialize($meta_val->entry_value);
                }
            }

            $entry->metas = $entry_metas;
        }





        return stripslashes_deep($entry);
    }

    function getAll($where = '', $order_by = '', $limit = '', $meta = false, $inc_form = true, $arfSearch = '', $arffieldorder = array(), $incomplete_form_entry = false) {


        global $wpdb, $MdlDb, $armainhelper;

        if (is_numeric($limit))
            $limit = " LIMIT {$limit}";

        $left_outer_join = "";

        $entry_table = $MdlDb->entries;
        $entry_meta_table = $MdlDb->entry_metas;
        if( true == $incomplete_form_entry ){
            $entry_table = $MdlDb->incomplete_entries;
            $entry_meta_table = $MdlDb->incomplete_entry_metas;
        }

        $temp_cols = 'it.id, it.entry_key, it.ip_address, it.created_date, it.browser_info, it.country, itmeta.entry_value';
        $temp_selcol = 'it.id, it.entry_key, it.name, it.ip_address, it.form_id, it.attachment_id, it.user_id, it.created_date';
        if( true == $incomplete_form_entry ){
            $temp_cols = 'it.id, it.ip_address, it.created_date, it.browser_info, it.country, itmeta.entry_value';
            $temp_selcol = 'it.id, it.ip_address, it.form_id, it.user_id, it.created_date';
        }

        if ($arfSearch != ''){
            $left_outer_join = " LEFT OUTER JOIN {$entry_meta_table} itmeta ON it.id=itmeta.entry_id ";
            $where .= " and Concat(".$temp_cols.") LIKE '%".$arfSearch."%'";
        }


        if ($inc_form) {


            $query = "SELECT it.*, fr.name as form_name,fr.form_key as form_key


                FROM $entry_table it LEFT OUTER JOIN $MdlDb->forms fr ON it.form_id=fr.id" .$left_outer_join.
                    $armainhelper->prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
        } else {


            $query = "SELECT ".$temp_selcol." FROM $entry_table it".$left_outer_join.$armainhelper->prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
        }


        $entries = $wpdb->get_results($query, OBJECT_K);


        unset($query);

        if ($meta and $entries) {


            if ($limit == '' and ! is_array($where) and preg_match('/^it\.form_id=\d+$/', $where)) {


                $meta_where = 'fi.form_id=' . substr($where, 11);
            } else if ($limit == '' and is_array($where) and count($where) == 1 and isset($where['it.form_id'])) {


                $meta_where = 'fi.form_id=' . $where['it.form_id'];
            } else {


                $meta_where = "entry_id in (" . implode(',', array_keys($entries)) . ")";
            }


            $query = $wpdb->prepare("SELECT entry_id, entry_value, field_id, 


                fi.field_key as field_key FROM $entry_meta_table it 


                LEFT OUTER JOIN $MdlDb->fields fi ON it.field_id=fi.id 


                WHERE $meta_where and field_id != %d", 0);





            $metas = $wpdb->get_results($query);


            unset($query);





            if ($metas) {

                if(count($arffieldorder) > 0){

                    $form_metas = array();
                    foreach ($arffieldorder as $fieldkey => $fieldorder) {
                        foreach ($metas as $fieldmetakey => $fieldmetaval) {
                            if($fieldmetaval->field_id == $fieldkey) {
                                $form_metas[] = $fieldmetaval;
                                unset($metas[$fieldmetakey]);
                            }
                        }
                    }

                    if(count($form_metas) > 0) {
                        if(count($metas) > 0) {
                            $arfothermetas = $metas;
                            $metas = array_merge($form_metas,$arfothermetas);
                        } else {
                            $metas = $form_metas;
                        }
                    }

                }


                foreach ($metas as $m_key => $meta_val) {


                    if (!isset($entries[$meta_val->entry_id]))
                        continue;





                    if (!isset($entries[$meta_val->entry_id]->metas))
                        $entries[$meta_val->entry_id]->metas = array();


                    $entries[$meta_val->entry_id]->metas[$meta_val->field_id] = $entries[$meta_val->entry_id]->metas[$meta_val->field_key] = maybe_unserialize($meta_val->entry_value);
                }
            }
        }

        return stripslashes_deep($entries);
    }

    function getRecordCount($where = '', $entry2 = false) {


        global $wpdb, $MdlDb, $armainhelper;

        $entry_table = $MdlDb->entries;
        if( $entry2 ){
            $entry_table = $MdlDb->incomplete_entries;
        }


        if (is_numeric($where)) {

            $cache_obj = wp_cache_get($entry_table.'_count');
            if($cache_obj == false){
                $query = "SELECT COUNT(*) FROM $entry_table WHERE form_id=" . $where;
               
                $cache_obj = wp_cache_set($entry_table.'_count', $query);
            }else{
                $query = $cache_obj;
            }
        } else {


            $cache_obj = wp_cache_get($entry_table.'_join');
            if($cache_obj == false){
                $query = "SELECT COUNT(*) FROM $entry_table it LEFT OUTER JOIN $MdlDb->forms fr ON it.form_id=fr.id" . $armainhelper->prepend_and_or_where(' WHERE ', $where);
              
                $cache_obj = wp_cache_set($entry_table.'_join', $query);
            }else{
                $query = $cache_obj;
            }

        }


        return $wpdb->get_var($query);
    }

    function getPageCount($p_size, $where = '', $entry2 = false) {


        if (is_numeric($where))
            return ceil((int) $where / (int) $p_size);
        else
            return ceil((int) $this->getRecordCount($where,$entry2) / (int) $p_size);
    }

    function getPage($current_p, $p_size, $where = '', $order_by = '', $arfSearch = '', $arffieldorder = array(), $incomplete_form_entry = false) {


        global $wpdb, $MdlDb, $armainhelper;


        $end_index = (int)$current_p * (int)$p_size;


        $start_index = (int)$end_index - (int)$p_size;

        if ($current_p != '' and $p_size != '')
            $results = $this->getAll($where, $order_by, " LIMIT $start_index,$p_size;", true, true, $arfSearch, $arffieldorder, $incomplete_form_entry);
        else
            $results = $this->getAll($where, $order_by, "", true, true, $arfSearch, $arffieldorder, $incomplete_form_entry);

        return $results;
    }

    function validate($values, $exclude = false, $unset_custom_captcha = 0) {
        
    }

    function akismet($values) {


        global $akismet_api_host, $akismet_api_port, $arfsiteurl;





        $content = '';


        foreach ($values['item_meta'] as $val) {


            if ($content != '')
                $content .= "\n\n";


            if (is_array($val))
                $val = implode(',', $val);


            $content .= $val;
        }





        if ($content == '')
            return false;





        $datas = array();


        $datas['blog'] = $arfsiteurl;


        $datas['user_ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);


        $datas['user_agent'] = $_SERVER['HTTP_USER_AGENT'];


        $datas['referrer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;


        $datas['comment_type'] = 'ARForms';


        if ($permalink = get_permalink())
            $datas['permalink'] = $permalink;





        $datas['comment_content'] = $content;





        foreach ($_SERVER as $key => $value)
            if (!in_array($key, array('HTTP_COOKIE', 'argv')))
                $datas["$key"] = $value;





        $query_string = '';


        foreach ($datas as $key => $data)
            $query_string .= $key . '=' . urlencode(stripslashes($data)) . '&';





        $response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);


        return ( is_array($response) and $response[1] == 'true' ) ? true : false;
    }

    function user_can_edit($entry, $form) {

        global $db_record;

        $allowed = $db_record->user_can_edit_check($entry, $form);

        return apply_filters('arfusercanedit', $allowed, compact('entry', 'form'));
    }

    function user_can_edit_check($entry, $form) {

        global $user_ID, $armainhelper, $db_record, $arfform;



        if (!$user_ID)
            return false;



        if (is_numeric($form))
            $form = $arfform->getOne($form);



        $form->options = maybe_unserialize($form->options);

        if (is_object($entry)) {

            if ($entry->user_id == $user_ID)
                return true;
            else
                return false;
        }



        $where = "user_id='$user_ID' and fr.id='$form->id'";

        if ($entry and ! empty($entry)) {
	
            if (is_numeric($entry))
                $where .= ' and it.id=' . $entry;
            else
                $where .= " and entry_key='" . $entry . "'";
        }



        return $db_record->getAll($where, '', ' LIMIT 1', true);
    }

}
