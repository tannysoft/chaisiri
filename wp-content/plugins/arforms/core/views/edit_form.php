<?php
global $arfieldhelper, $arformhelper, $MdlDb, $fields_with_external_js, $bootstraped_fields_array,$arformcontroller, $arffield;
$frm_class = 'arf_standard_form';
if( $newarr['arfinputstyle'] == 'rounded' ){
    $frm_class = 'arf_rounded_form';
} else if ($newarr['arfinputstyle'] == 'material' ){
    $frm_class = 'arf_materialize_form';
} else if( $newarr['arfinputstyle'] == 'material_outlined' ){
    $frm_class = 'arf_material_outline_form';
}

if($_GET['arfaction'] == 'new' || $_GET['arfaction'] =='duplicate'){
    if($define_template < 100){
        $values['name'] = isset($_GET['form_name']) ? stripslashes_deep($arformcontroller->arfHtmlEntities($_GET['form_name'],true)) : '';
        $values['description'] = isset($_GET['form_desc']) ? stripslashes_deep($arformcontroller->arfHtmlEntities($_GET['form_desc'],true)) : '';
    }
}
?>
<div id="arfmainformeditorcontainer" class="arf_form arf_form_outer_wrapper arf_main_tabs active_tabs arf_form ar_main_div_<?php echo $id; ?>">
    <div class="allfields">
        <div id="arf_fieldset_<?php echo $id; ?>" class="arf_fieldset <?php echo $frm_class; ?>">
            <div id="success_message" class="arf_success_message">
                <div class="message_descripiton">
                    <div style="float: left; margin-right: 15px;"><?php echo addslashes(esc_html__('Form is successfully updated', 'ARForms')); ?></div>
                    <div class="message_svg_icon">
                        <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M6.075,14.407l-5.852-5.84l1.616-1.613l4.394,4.385L17.181,0.411 l1.616,1.613L6.392,14.407H6.075z"></path></svg>
                    </div>
                </div>
            </div>
            <div id="error_message" class="arf_error_message">
                <div class="message_descripiton">
                    <div style="float: left; margin-right: 15px;"><?php echo addslashes(esc_html__('Form is not successfully updated','ARForms')); ?></div>
                    <div class="message_svg_icon">
                            <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></svg>
                    </div>
                </div>
            </div>            
            <div id="titlediv" class="arftitlediv" <?php echo (isset($newarr['display_title_form']) && $newarr['display_title_form'] == 0 ) ? 'style="display:none;"' : '';?>>
                <input type="hidden" value="<?php echo ARFURL . '/images'; ?>" id="plugin_image_path" />

                <div id="form_desc" class="edit_form_item arffieldbox frm_head_box">

                    <div class="arfformnamediv">
                        <div class="arfformedit arftitlecontainer">
                            <span class="arfeditorformname formtitle_style arf_edit_in_place" id="frmform_<?php echo $id; ?>">
                                <input type="text" name="name" id="form_name" class="arf_edit_in_place_input inplace_field" value="<?php echo stripslashes_deep($values['name']); ?>" data-default-value="<?php echo stripslashes_deep($values['name']); ?>" data-ajax="false" data-action="arfupdateformname" placeholder="<?php echo addslashes(esc_html__('Click here to enter form title', 'ARForms'));?>"/>
                            </span>
                        </div>
                        <div class="arfformeditpencil" style="margin-top:3px;"></div>
                    </div>
                    <div style="clear:both;"></div>
                    <div class="arfformdescriptiondiv">
                        <div class="arfdescriptionedit">

                            <div class="arfeditorformdescription arf_edit_in_place formdescription_style"><input type="text" data-default-value="<?php echo ($values['description'] != '') ? stripslashes_deep($values['description']) : addslashes(esc_html__('Click here to enter form description', 'ARForms')); ?>" class="arf_edit_in_place_input inplace_field" data-ajax="false" name="description" data-action="arfupdateformdescription" value="<?php echo ($values['description'] != '') ? stripslashes_deep($values['description']) : ""; ?>" placeholder="<?php echo addslashes(esc_html__('Click here to enter form description', 'ARForms'));?>"/></div>
                        </div>
                        <div class="arfdescriptioneditpencil"></div>    
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div style="clear:both;"></div>

            </div>




            <div id="new_fields" data-flag="1" class="newfield_div">


                <?php
                $index_arf_fields = 0;
                global $index_repeater_fields;
                $index_repeater_fields = 0;
                if (isset($values['fields']) && !empty($values['fields'])) {
                    $arf_load_password = array();
                    $arf_load_confirm_email = array();
                    $totalpass = 0;
                    foreach ($values['fields'] as $arrkey => $field) {
                        if ($field['type'] == 'password') {
                            $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                            if (isset($field['confirm_password']) and $field['confirm_password'] == 1 and isset($arf_load_password['confirm_pass_field']) and $arf_load_password['confirm_pass_field'] == $field['id'])
                                $values['confirm_password_arr'][$field['id']] = isset($field['confirm_password_field']) ? $field['confirm_password_field'] : "";
                            else
                                $arf_load_password['confirm_pass_field'] = isset($field['confirm_password_field']) ? $field['confirm_password_field'] : "";
                        }

                        if ($field['type'] == 'email') {
                            $field['id'] = $arfieldhelper->get_actual_id($field['id']);

                            if (isset($field['confirm_email']) and $field['confirm_email'] == 1 and isset($arf_load_confirm_email['confirm_email_field']) and $arf_load_confirm_email['confirm_email_field'] == $field['id'])
                                $values['confirm_email_arr'][$field['id']] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                            else
                                $arf_load_confirm_email['confirm_email_field'] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                        }



                        if ($field['type'] == 'email' && isset($field['confirm_email']) && $field['confirm_email'] == 1) {
                            if (isset($field['confirm_email']) and $field['confirm_email'] == 1 and isset($arf_load_confirm_email['confirm_email_field']) and $arf_load_confirm_email['confirm_email_field'] == $field['id']) {
                                $values['confirm_email_arr'][$field['id']] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                            } else {
                                $arf_load_confirm_email['confirm_email_field'] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                            }
                            $confirm_email_field = $arfieldhelper->get_confirm_email_field($field);
                            $values['fields'] = $arfieldhelper->array_push_after($values['fields'], array($confirm_email_field), $arrkey + $totalpass);
                            $totalpass++;
                        }

                        if ($field['type'] == 'password' && $field['confirm_password']) {
                            if (isset($field['confirm_password']) and $field['confirm_password'] == 1 and isset($arf_load_password['confirm_pass_field']) and $arf_load_password['confirm_pass_field'] == $field['id']) {
                                $values['confirm_password_arr'][$field['id']] = isset($field['confirm_password_field']) ? $field['confirm_password_field'] : "";
                            } else {
                                $arf_load_password['confirm_pass_field'] = isset($field['confirm_password_field']) ? $field['confirm_password_field'] : "";
                            }
                            $confirm_password_field = $arfieldhelper->get_confirm_password_field($field);
                            $values['fields'] = $arfieldhelper->array_push_after($values['fields'], array($confirm_password_field), $arrkey + $totalpass);
                            $totalpass++;
                        }
                    }
                    $arf_fields = array();
                    
                    if($arfaction == 'duplicate') {
                        $arf_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $MdlDb->fields . "` WHERE `form_id` = %d", $define_template), ARRAY_A);
                    } else if($arfaction == 'edit') {
                        $arf_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $MdlDb->fields . "` WHERE `form_id` = %d", $id), ARRAY_A);
                    }

                    $arf_is_page_break_no = 0;

                    $frm_opts = maybe_unserialize($data['options']);
                    
                    $frm_css = maybe_unserialize($data['form_css']);

                    $tempinputStyle = $frm_css['arfinputstyle'];

                    if( !empty( $tempinputStyle ) && !in_array( $tempinputStyle, $maincontroller->arf_default_themes() ) ){
                        $frm_css['arfinputstyle'] = 'standard';
                    }

                    $field_order = $frm_opts['arf_field_order'];

                    $inner_field_order = isset( $frm_opts['arf_inner_field_order'] ) ? $frm_opts['arf_inner_field_order'] : json_encode(array());

                    $inner_f_order_arr = arf_json_decode( $inner_field_order, true );


                    $temp_inner_order_arr = array();

                    if( !empty( $inner_f_order_arr ) ){
                        foreach( $inner_f_order_arr as $ifkey => $inner_ord ){
                            $pre_fid = array();
                            $icnt = 1;
                            $temp_inner_order_arr[$ifkey] = array();
                            $total_inner_fields = count( $inner_ord );
                            $x = 1;
                            foreach( $inner_ord as $infkey => $infval ){
                                $exploded_data = explode('|', $infval);
                                $temp_fid = $exploded_data[0];
                                
                                if( !empty( $pre_fid ) && preg_match( '/^[\d]+$/', $temp_fid ) && in_array( $temp_fid, $pre_fid ) ){
                                    continue;
                                }
                                if( $x == $total_inner_fields && isset( $temp_inner_order_arr[ $ifkey ][ $icnt - 2 ] ) ){
                                    $last_counter = $temp_inner_order_arr[ $ifkey ][ $icnt - 2 ];

                                    $last_counter_data = explode( '|', $last_counter );

                                    if( ( $last_counter_data[0] == 'arf_2col' && $temp_fid == 'arf_2col' ) || ( $last_counter_data[0] == 'arf_3col' && $temp_fid == 'arf_3col' ) || ( $last_counter_data[0] == 'arf_4col' && $temp_fid == 'arf_4col' ) || ( $last_counter_data[0] == 'arf_5col' && $temp_fid == 'arf_5col' ) || ( $last_counter_data[0] == 'arf_6col' && $temp_fid == 'arf_6col' ) ){
                                        continue;
                                    }

                                }
                                if( empty( $pre_fid ) || !in_array( $temp_fid, $pre_fid) ){
                                    array_push($pre_fid, $temp_fid );
                                }
                                $temp_inner_order_arr[$ifkey][] = $temp_fid.'|'.$icnt;
                                $icnt++;
                                $x++;
                            }
                        }
                    }


                    $inner_field_order = json_encode( $temp_inner_order_arr );

                    $field_resize_width = isset($frm_opts['arf_field_resize_width']) ? $frm_opts['arf_field_resize_width'] : '';

                    $inner_field_resize_width = isset( $frm_opts['arf_inner_field_resize_width'] ) ? $frm_opts['arf_inner_field_resize_width'] : '';

                    $field_temp_fields = maybe_unserialize($data['temp_fields']);

                    $arf_field_counter = 1;
                    if ($field_resize_width != '') {
                        $field_resize_width = arf_json_decode($field_resize_width, true);
                    }

                    if( $inner_field_resize_width != '' ){
                        $inner_field_resize_width = arf_json_decode( $inner_field_resize_width, true );
                    }

                    $arf_sorted_fields = array();
                    $arf_temp_sorted_fields = array();
                    $arf_inner_class = array();
                    if ($field_order != '') {
                        $field_order = arf_json_decode($field_order, true);

                        asort($field_order);

                        $sorted_counter = 0;
                        foreach ($field_order as $field_id => $order) {
                            if(is_int($field_id)){
                                foreach ($arf_fields as $field) {
                                    if ($field_id == $field['id']) {
                                        $arf_sorted_fields[] = $field;
                                        $temp_field_opts = arf_json_decode( $field['field_options'],true );
                                        $arf_temp_sorted_fields[ $sorted_counter ] = !empty( $temp_field_opts['inner_class'] ) ? $temp_field_opts['inner_class'] : 'arf_1col';
                                        $arf_inner_class[ $field_id ] = $temp_field_opts['inner_class'];
                                    }
                                }
                            } else {
                                $exploded_fid = explode( '|', $field_id );
                                $temp_fid = $exploded_fid[0];
                                $prev_sorted_counter = $sorted_counter - 1;
                                $arf_inner_class[ $field_id ] = $temp_fid;

                                if( !empty( $arf_temp_sorted_fields[ $prev_sorted_counter ] ) ){
                                    $prev_inner_class = $arf_temp_sorted_fields[ $prev_sorted_counter ];
                                    if( $temp_fid == 'arf_2col' && !preg_match( '/(arf21colclass)/', $prev_inner_class ) ){
                                        continue;
                                    } else if( $temp_fid == 'arf_3col' && !preg_match( '/(arf_23col)/', $prev_inner_class ) ){
                                        continue;
                                    } else if( $temp_fid == 'arf_4col' && !preg_match( '/(arf43colclass)/', $prev_inner_class ) ){
                                        continue;
                                    } else if( $temp_fid == 'arf_5col' && !preg_match( '/(arf54colclass)/', $prev_inner_class ) ){
                                        continue;
                                    } else if( $temp_fid == 'arf_6col' && !preg_match( '/(arf65colclass)/', $prev_inner_class ) ){
                                        continue;
                                    }
                                }

                                $arf_sorted_fields[] = $field_id;
                                $arf_temp_sorted_fields[ $sorted_counter ] = $field_id;
                            }
                            $sorted_counter++;
                        }
                    }

                    if (isset($arf_sorted_fields) && !empty($arf_sorted_fields)) {
                        $arf_fields = $arf_sorted_fields;
                    }

                    $field_orders = array();
                    $ord = 0;
                    $ord_ = 1;

                    foreach( $arf_inner_class as $fid => $inner_cls ){
                        $current = current( $arf_inner_class );
                        $current_key = key( $arf_inner_class );

                        $next = next( $arf_inner_class );
                        $next_key = key( $arf_inner_class );

                        if( ( $current == 'arf_2col' && !empty( $next ) && 'arf_2col' == $next && is_int( $next_key ) ) || ( $current == 'arf_3col' && !empty( $next ) && 'arf_3col' == $next && is_int( $next_key ) ) || ( $current == 'arf_4col' && !empty( $next ) && 'arf_4col' == $next && is_int( $next_key ) ) || ( $current == 'arf_5col' && !empty( $next ) && 'arf_5col' == $next && is_int( $next_key ) ) || ( $current == 'arf_6col' && !empty( $next ) && 'arf_6col' == $next && is_int( $next_key ) ) ){
                            $getKey = $arformcontroller->arfSearchArray( $next_key, 'id', $arf_fields );

                            if( '' !== $getKey ){
                                $fopts = arf_json_decode( $arf_fields[$getKey]['field_options'], true );
                                $fopts['classes'] = 'arf_1';
                                $fopts['inner_class'] = 'arf_1col';

                                $arf_fields[$getKey]['field_options'] = json_encode( $fopts );
                            }
                        }

                        if( ( $current == 'arf21colclass' && !empty( $next ) && 'arf_2col' != $next ) && !preg_match( '/(_confirm)/', $next ) ){
                            $field_orders[$fid] = $ord;
                            $ord_++;
                            $field_orders[ 'arf_2col|' . $ord_ ] = ++$ord;
                        } else if( ( 'arf_23col' == $current ) && !empty( $next ) && 'arf_3col' != $next && !preg_match( '/(_confirm)/', $next ) ){
                            $field_orders[$fid] = $ord;
                            $ord_++;
                            $field_orders[ 'arf_3col|' . $ord_ ] = ++$ord;
                        } else if( ( 'arf43colclass' == $current ) && !empty( $next ) && 'arf_4col' != $next && !preg_match( '/(_confirm)/', $next ) ) {
                            $field_orders[$fid] = $ord;
                            $ord_++;
                            $field_orders[ 'arf_4col|' . $ord_ ] = ++$ord;
                        } else if( ( 'arf54colclass' == $current ) && !empty( $next ) && 'arf_5col' != $next && !preg_match( '/(_confirm)/', $next ) ){
                            $field_orders[$fid] = $ord;
                            $ord_++;
                            $field_orders[ 'arf_5col|' . $ord_ ] = ++$ord;
                        } else if( ( 'arf65colclass' == $current ) && !empty( $next ) && 'arf_6col' != $next && !preg_match( '/(_confirm)/', $next ) ){
                            $field_orders[$fid] = $ord;
                            $ord_++;
                            $field_orders[ 'arf_6col|' . $ord_ ] = ++$ord;
                        } else {
                            $field_orders[$fid] = $ord;
                        }
                        $ord++;
            			$ord_++;
                    }

                    $updated_sorted_fields = array();
                    if( !empty( $field_orders ) ){
                        foreach ($field_orders as $field_id => $order) {
                            if(is_int($field_id)){
                                foreach ($arf_fields as $field) {
                                    if( isset( $field['id'] )  && $field_id == $field['id']) {
                                        $updated_sorted_fields[] = $field;
                                    }
                                }
                            } else {
                                $updated_sorted_fields[] = $field_id;
                            }
                        }
                    }

                    if( !empty( $updated_sorted_fields ) ){
                        $total_sorted_fields = count( $updated_sorted_fields );
                        $new_sorted_fields = $updated_sorted_fields;
                        foreach( $updated_sorted_fields as $k => $v ){
                            if( empty( $v ) ){
                                $next_val = $new_sorted_fields[$k + 1];
                                
                                if( preg_match('/(arf_2col\|)/', $next_val) ){
                                    $new_sorted_fields[$k] = 'arf21colclass|' .($k + 1);
                                }
                            }

                            if( $total_sorted_fields == ($k + 1) ){
                                if( is_array( $v )){
                                    $current_fopts = arf_json_decode( $v['field_options'], true );
                                    if( preg_match('/arf21colclass/', $current_fopts['inner_class']) ){
                                        $new_sorted_fields[$k + 1] = 'arf_2col|' . ($k + 1);
                                    }
                                }
                            }
                        }
                        $new_sorted_fields = array_values( $new_sorted_fields );
                        $updated_sorted_fields = $new_sorted_fields;
                    }
                    
                    if( !empty( $updated_sorted_fields ) ){
                        $arf_fields = $updated_sorted_fields;
                    }

                    $class_array = array();
                    $conut_arf_fields = count($arf_fields);

                    $repeater_fields = array();
                    $inner_field_count = 0;
                    $field_classes = array();
                    $field_classes2 = array();
                    $ncounter = 0;
                    $confirm_field_classes = array();
                    foreach( $arf_fields as $field_key => $field ){

                        $display_field_in_editor_from_outside = apply_filters( 'arf_display_field_in_editor_outside', false, $field);
                        if( is_array( $field ) ){
                            if( 'hidden' == $field['type'] ){
                                continue;
                            }
                            $field_opt = arf_json_decode($field['field_options'], true);
                            
                            if (json_last_error() != JSON_ERROR_NONE) {
                                $field_opt = maybe_unserialize($field['field_options']);
                            }

                            $class = (isset($field_opt['inner_class']) && $field_opt['inner_class']) ? $field_opt['inner_class'] : 'arf_1col';
                            if( false == $display_field_in_editor_from_outside ){
                                $field_classes2[ $field['id'] ] = $class;
                            } else {
                                $cache_parent_obj = wp_cache_get( 'arf_parent_field_data_' . $field['id'] );
                                if( isset( $_REQUEST['arfaction'] ) && $_REQUEST['arfaction'] == 'duplicate' ){
                                    if( false === $cache_parent_obj ){
                                        $get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".$MdlDb->fields."` WHERE form_id = %d AND (field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%') ", $_REQUEST['id'], $field['id'], $field['id'] ), ARRAY_A );
                                        wp_cache_set( 'arf_parent_field_data_' . $field['id'], $get_all_inner_fields );
                                    } else {
                                        $get_all_inner_fields = $cache_parent_obj;
                                    }
                                } else {
                                    if( false === $cache_parent_obj )
                                    {
                                        $get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".$MdlDb->fields."` WHERE form_id = %d AND (field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%') ", $id, $field['id'], $field['id'] ), ARRAY_A );
                                        wp_cache_set( 'arf_parent_field_data_' . $field['id'], $get_all_inner_fields );
                                    } else {
                                        $get_all_inner_fields = $cache_parent_obj;
                                    }
                                }
                                $inner_field_order_obj = arf_json_decode( $inner_field_order, true );

                                if( !empty( $inner_field_order_obj[$field['id']] ) ){
                                    
                                    $inner_fields = $inner_field_order_obj[$field['id']];
                                    $pre_field_id = array();

                                    $total_inner_fields2 = count( $inner_fields );
                                    $xi = 1;
                                    foreach( $inner_fields as $ikey => $ifield_order ){
                                        $ifeld_data = explode('|', $ifield_order);
                                        $temp_fid = $ifeld_data[0];
                                        
                                        if( !empty( $pre_field_id ) && is_int( $temp_fid ) && in_array( $temp_fid, $pre_field_id ) ){
                                            continue;
                                        }

                                        if( empty( $pre_field_id ) || !in_array( $temp_fid, $pre_field_id) ){
                                            array_push($pre_field_id, $temp_fid );
                                        }
                                        if( preg_match('/^(\d+)$/',$temp_fid ) ){
                                            $ifobj = $arffield->getOne( $temp_fid );
                                            $ifobj_opts = $ifobj->field_options;

                                            $iclass = (isset($ifobj_opts['inner_class']) && $ifobj_opts['inner_class']) ? $ifobj_opts['inner_class'] : 'arf_1col';
                                            if( ! isset( $field_classes2[ $field['id'] ] )){
                                                $field_classes2[ $field['id'] ] = array();
                                            }
                                            $field_classes2[ $field['id'] ][ $temp_fid ] = $iclass;
                                        } else {
                                            $field_classes2[ $field['id'] ][ $temp_fid.'|'.$ifeld_data[1] ] = $temp_fid;
                                        }
                                        $xi++;
                                    }
                                }
                            }
                        } else {
                            if( preg_match( '/(_confirm)/', $field) ){
                                $main_field_id = str_replace( '_confirm', '', $field );
                                $main_field_key = $arformcontroller->arfSearchArray( $main_field_id, 'id', $arf_fields );
                                if( '' !== $main_field_key){
                                    $field_arr = $arf_fields[ $main_field_key ];

                                    $main_field_opts = arf_json_decode( $field_arr['field_options'], true );

                                    if( 'email' == $field_arr['type'] ){
                                        $field_classes2[ $field ] = $main_field_opts['confirm_email_inner_classes'];
                                        $confirm_field_classes[ $field ] = $main_field_opts['confirm_email_classes'];
                                    } else if ( 'password' == $field_arr['type'] ) {
                                        $field_classes2[ $field ] = $main_field_opts['confirm_password_inner_classes'];                                        
                                        $confirm_field_classes[ $field ] = $main_field_opts['confirm_password_classes'];
                                    }
                                }
                            } else {
                                $field_classes2[ $field ] = $field;
                            }
                        }
                        $ncounter++;
                    }
                    $ar2col_keys = array();
                    
                    
                    if( !empty( $field_classes2 ) ){

                        foreach( $field_classes2 as $fckey => $fcval ){
                            if( is_array( $fcval ) ){
                                $xi = 1;
                                $total_fcval = count( $fcval );
                                foreach( $fcval as $fcikey => $fcival ){
                                    $current_val = current( $fcval );
                                    $next_val = next( $fcval );
                                    $next_key = key( $fcval );

                                    if( ( $xi + 1 ) == $total_fcval ){
                                        if( ( 'arf_2col' == $current_val && 'arf_2col' == $next_val ) || ( 'arf_3col' == $current_val && 'arf_3col' == $next_val ) || ( 'arf_4col' == $current_val && 'arf_4col' == $next_val ) || ( 'arf_5col' == $current_val && 'arf_5col' == $next_val ) || ( 'arf_6col' == $current_val && 'arf_6col' == $next_val ) ){
                                            unset( $field_classes2[$fckey][$next_key] );
                                            unset( $temp_inner_order_arr[$fckey][$xi] );
                                        }
                                    }

                                    if( 'arf21colclass' == $current_val && 'arf_2col' != $next_val && !preg_match( '/_confirm/', $next_val ) ){
                                        $temp_inner_fields = !empty( $temp_inner_order_arr[ $fckey ] ) ? $temp_inner_order_arr[ $fckey ] : array();

                                        if( !empty( $temp_inner_fields ) ){
                                            foreach( $temp_inner_fields as $tikey => $tival ){
                                                $temp_exploded_data = explode( '|', $tival );

                                                $tempinfid = $temp_exploded_data[0];

                                                if( $fcikey == $tempinfid ){
                                                    array_splice( $temp_inner_order_arr[$fckey], ($tikey + 1), 0, 'arf_2col|'. ($temp_exploded_data[1] + 1) );
                                                }

                                            }
                                        }
                                    } else if( 'arf_23col' == $current_val && 'arf_3col' != $next_val && !preg_match( '/_confirm/', $next_val ) ){
                                        $temp_inner_fields = !empty( $temp_inner_order_arr[ $fckey ] ) ? $temp_inner_order_arr[ $fckey ] : array();

                                        if( !empty( $temp_inner_fields ) ){
                                            foreach( $temp_inner_fields as $tikey => $tival ){
                                                $temp_exploded_data = explode( '|', $tival );

                                                $tempinfid = $temp_exploded_data[0];

                                                if( $fcikey == $tempinfid ){
                                                    array_splice( $temp_inner_order_arr[$fckey], ($tikey + 1), 0, 'arf_3col|'. ($temp_exploded_data[1] + 1) );
                                                }

                                            }
                                        }
                                    } else if( 'arf43colclass' == $current_val && 'arf_4col' != $next_val && !preg_match( '/_confirm/', $next_val ) ){
                                        $temp_inner_fields = !empty( $temp_inner_order_arr[ $fckey ] ) ? $temp_inner_order_arr[ $fckey ] : array();

                                        if( !empty( $temp_inner_fields ) ){
                                            foreach( $temp_inner_fields as $tikey => $tival ){
                                                $temp_exploded_data = explode( '|', $tival );

                                                $tempinfid = $temp_exploded_data[0];

                                                if( $fcikey == $tempinfid ){
                                                    array_splice( $temp_inner_order_arr[$fckey], ($tikey + 1), 0, 'arf_4col|'. ($temp_exploded_data[1] + 1) );
                                                }

                                            }
                                        }
                                    } else if ( 'arf54colclass' == $current_val && 'arf_5col' != $next_val && !preg_match( '/_confirm/', $next_val ) ){
                                        if( !empty( $temp_inner_fields ) ){
                                            foreach( $temp_inner_fields as $tikey => $tival ){
                                                $temp_exploded_data = explode( '|', $tival );

                                                $tempinfid = $temp_exploded_data[0];

                                                if( $fcikey == $tempinfid ){
                                                    array_splice( $temp_inner_order_arr[$fckey], ($tikey + 1), 0, 'arf_4col|'. ($temp_exploded_data[1] + 1) );
                                                }

                                            }
                                        }
                                    } else if( 'arf65colclass' == $current_val && 'arf_6col' != $next_val && !preg_match( '/_confirm/', $next_val ) ){
                                        if( !empty( $temp_inner_fields ) ){
                                            foreach( $temp_inner_fields as $tikey => $tival ){
                                                $temp_exploded_data = explode( '|', $tival );

                                                $tempinfid = $temp_exploded_data[0];

                                                if( $fcikey == $tempinfid ){
                                                    array_splice( $temp_inner_order_arr[$fckey], ($tikey + 1), 0, 'arf_6col|'. ($temp_exploded_data[1] + 1) );
                                                }

                                            }
                                        }
                                    }
                                    $xi++;
                                }
                            }
                        }
                    }

                    $inner_field_order = json_encode( $temp_inner_order_arr );

                    foreach ($arf_fields as $field_key => $field) {
                        $display_field_in_editor_from_outside = apply_filters( 'arf_display_field_in_editor_outside', false, $field);

                        if(is_array($field)){
			                if( $field['type'] == 'hidden'){
                                continue;
                            }
                            if ($field['type'] == 'break' && $arf_is_page_break_no == 0) {
                                $field['page_break_first_use'] = 1;
                                $arf_is_page_break_no++;
                            }
                            if ($field['type'] == 'arf_multiselect') {
                                $field_name = "item_meta[" . $field['id'] . "][]";   
                            }else{
                                $field_name = "item_meta[" . $field['id'] . "]";
                            }
                            
                            $has_field_opt = false;
                            if (isset($field['options']) && $field['options'] != '' && !empty($field['options'])) {
                                $has_field_opt = true;
                                $field_options_db = arf_json_decode($field['options'], true);
                                if (json_last_error() != JSON_ERROR_NONE) {
                                    $field_options_db = maybe_unserialize($field['options'], true);
                                }
                            }
                            
                            $field_opt = arf_json_decode($field['field_options'], true);
                            
                            if (json_last_error() != JSON_ERROR_NONE) {
                                $field_opt = maybe_unserialize($field['field_options']);
                            }

                            $class = (isset($field_opt['inner_class']) && $field_opt['inner_class']) ? $field_opt['inner_class'] : 'arf_1col';
                            $field_classes[] = $class;
                            array_push($class_array,$class);

                            if (isset($field_opt) && !empty($field_opt) && is_array($field_opt) ) {
                                foreach ($field_opt as $k => $field_opt_val) {
                                    if ($k != 'options') {
                                        $field[$k] = $arformcontroller->arf_html_entity_decode($field_opt_val);
                                    } else {
                                        if ($has_field_opt == true && $k == 'options') {
                                            $field[$k] = $field_options_db;
                                        }
                                    }
                                }
                            }
                            if (in_array($field['type'], $bootstraped_fields_array)) {
                                array_push($fields_with_external_js, $field['type']);
                            }
                        } else {
                            if( preg_match( '/(_confirm)/', $field ) && !empty( $field_classes2[ $field ] ) ){
                                $field_classes[] = $field_classes2[ $field ];
                            } else {
                                $field_classes[] = $field;
                            }
                        }


                        if( isset( $remove_placeholders ) && true == $remove_placeholders && !empty( $field['placeholdertext'] ) ){
                            $field['placeholdertext'] = '';
                            if( !empty( $field_opt['placeholdertext'] ) ){
                                $field_opt['placeholdertext'] = '';
                                $field['field_options'] = json_encode( $field_opt );
                            }
                        }

                        if( !$display_field_in_editor_from_outside ){
                            require(VIEWS_PATH . '/arf_field_editor.php');
                        } else {
                            global $index_repeater_fields;
                            do_action( 'arf_render_field_in_editor_outside', $field, $field_data_obj, $field_order,$inner_field_order, $index_arf_fields, $frm_css, $data, $id, $inner_field_resize_width, array(), false, $newarr, $remove_placeholders );
                            global $index_repeater_fields;
                            $index_arf_fields = $index_repeater_fields;
                        }
                        
                        unset($field);


                        unset($field_name);

                        $arf_field_counter++;
                    }
                }
                
                ?>

            </div>

            <?php
            echo "<label class='arf_main_label arf_width_counter_label'></label>";            
            echo "<label class='arf_main_label arf_width_counter_label_section'></label>";           
            $newarr['arfsubmitbuttontext'] = isset($newarr['arfsubmitbuttontext']) ? $newarr['arfsubmitbuttontext'] : '';
            if ($newarr['arfsubmitbuttontext'] == '') {
                $arf_option = get_option('arf_options');
                $submit_value = $arf_option->submit_value;
            } else {
                $submit_value = esc_attr($newarr['arfsubmitbuttontext']);
            }

            $submit_buttonwidth = isset($newarr['arfsubmitbuttonwidthsetting']) ? $newarr['arfsubmitbuttonwidthsetting'] : '';
            ?>
            <div style="clear:both;"></div>
            <div class="arfeditorsubmitdiv arf_submit_div top_container">
                <div class="arfsubmitedit arfsubmitbutton">
                    <div class="arf_greensave_button_wrapper">
                        <?php 
                        $arfsubmitbuttonstyleclass = '';

                        if(isset($newarr['arfsubmitbuttonstyle']) && $newarr['arfsubmitbuttonstyle'] == 'flat'){
                            $arfsubmitbuttonstyleclass= 'arf_submit_btn_flat';
                        } else if(isset($newarr['arfsubmitbuttonstyle']) &&  $newarr['arfsubmitbuttonstyle'] == 'border'){
                            $arfsubmitbuttonstyleclass= 'arf_submit_btn_border';
                        } else if(isset($newarr['arfsubmitbuttonstyle']) && $newarr['arfsubmitbuttonstyle'] == 'reverse border'){
                            $arfsubmitbuttonstyleclass= 'arf_submit_btn_reverse_border';
                        }
                        ?>
                        <div class="greensavebtn arf_submit_btn btn btn-info arfstyle-button waves-effect waves-light <?php echo $arfsubmitbuttonstyleclass;?>" data-auto="<?php
                        if ($submit_buttonwidth != '') {
                            echo '1';
                        } else {
                            echo '0';
                        }
                        ?>" <?php
                                if ($submit_buttonwidth != '') {
                                    echo 'style="width:' . $submit_buttonwidth . 'px;"';
                                }
                                ?> data-style="zoom-in" data-width="<?php echo $submit_buttonwidth; ?>">
                            <div class="arfsubmitbtn arf_edit_in_place" id="arfeditorsubmit">
                                <input type='text' class='arf_edit_in_place_input inplace_field arf_submit_button_textbox' data-id="arf_form_submit_button" data-ajax='false' value="<?php echo $submit_value; ?>" <?php echo ( isset( $newarr['submit_bg_img'] ) && !empty( $newarr['submit_bg_img'] ) ) ? 'style="display: none;"' : 'style="display: block;"'; ?> />
                            </div>
                        </div>
                        <span class="arf_submit_button_edit_icon"><svg width='18' height='18' fill='rgb(255, 255, 255)' xmlns='http://www.w3.org/2000/svg' data-name='Layer 1' viewBox='0 0 512 512' x='0px' y='0px'><title>Edit</title><path d='M318.37,85.45L422.53,190.11,158.89,455,54.79,350.38ZM501.56,60.2L455.11,13.53a45.93,45.93,0,0,0-65.11,0L345.51,58.24,449.66,162.9l51.9-52.15A35.8,35.8,0,0,0,501.56,60.2ZM0.29,497.49a11.88,11.88,0,0,0,14.34,14.17l116.06-28.28L26.59,378.72Z'/></svg></span>
                    </div>
                </div>
                <div class="arfsubmiteditpencil arfhelptip" title="<?php echo addslashes(esc_html__('Edit Text', 'ARForms')); ?>"></div>
                <div class="arfsubmitsettingpencil arfhelptip" title="<?php echo addslashes(esc_html__('Settings', 'ARForms')); ?>" id="field-setting-button-arfsubmit" onclick="arfshowfieldoptions('arfsubmit')" data-lower="false"></div>
            </div>
        </div>
    </div>
    <?php
    $key = isset($values['form_key']) ? $values['form_key'] : '';

    $width = isset($_COOKIE['width']) ? $_COOKIE['width'] * 0.80 : 0;

    $width_new = '&width=' . $width;
    ?>
    <?php
    $delete_modal_width = isset($_COOKIE['width']) ? ($_COOKIE['width'] - 850) / 2 : 'auto';
    $delete_modal_height = isset($_COOKIE['height']) ? ($_COOKIE['height'] - 500) / 2 : 'auto';
    ?>
    <div style="clear:both;"></div>

</div>




<?php
$widthmaincontent = isset($_COOKIE['width']) ? $_COOKIE['width'] - 650 : 0;
$extra_width = "0";

$left_width = ( ($widthmaincontent) / 2 + $extra_width) . 'px';
if (is_rtl()) {
    $iframediv_loader_style = 'right:' . $left_width . ';top:180px;';
} else {
    $iframediv_loader_style = 'left:' . $left_width . ';top:180px;';
}

$delete_modal_width = isset($_COOKIE['width']) ? ($_COOKIE['width'] - 350) / 2 : 'auto';
$delete_modal_height = isset($_COOKIE['height']) ? ($_COOKIE['height'] - 180) / 2 : 'auto';

$key = isset($values['form_key']) ? $values['form_key'] : '';
?>
<div style="left:-999px; position:fixed; visibility:hidden;">
    <div class="greensavebtn" style="float:left;min-width: 105px;" id="arfsubmitbuttontext2"><?php echo $submit_value; ?></div>
</div>
<input type="hidden" name="arf_editor_total_rows" id="arf_editor_total_rows" value="<?php echo $index_arf_fields;?>" />