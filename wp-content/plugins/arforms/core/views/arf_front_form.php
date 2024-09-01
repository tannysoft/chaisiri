<?php

if (!function_exists('ars_get_form_builder_string')) {

      function ars_get_form_builder_string($id, $key = "", $preview = 0, $is_widget_or_modal = 0, $errors = array(), $arf_data_uniq_id = '', $desc = '', $type = '', $modal_height = '', $modal_width = '', $position = '', $btn_angle = '', $bgcolor = '', $txtcolor = '', $open_inactivity = '', $open_scroll = '', $open_delay = '', $overlay = '', $is_close_link = '', $modal_bgcolor = '', $is_fullscrn ='',$inactive_min = '',$model_effect = '',$navigation = false,$arf_preset_data = '', $hide_popup_for_loggedin_user='no',$arf_skip_modal_html = false, $viewer_edit_id = '',$arf_img_url = '', $arf_img_height = '', $arf_img_width = '',$is_site_wide = false) {
        @ini_set('max_execution_time', 0);
        /* declare global */
        
        $home_preview = false;
        if( isset($_REQUEST['arf_is_home']) ){
            $home_preview = $_REQUEST['arf_is_home'];
        }
        
        global $arfform, $user_ID, $arfsettings, $post, $wpdb, $armainhelper, $arrecordcontroller, $arformcontroller, $arfieldhelper, $arrecordhelper, $page_break_hidden_array, $arf_page_number, $arfforms_loaded, $arf_form_all_footer_js, $arfcreatedentry, $MdlDb,$func_val,$front_end_get_temp_fields,$arfdecimal_separator,$arfmessage_rest,$arf_repeater_field_class,$maincontroller, $arf_glb_preset_data, $arf_popup_forms, $arf_popup_forms_footer_js,$arsettingcontroller, $arformhelper;

        $maincontroller->arf_start_session(true);

        $arf_current_token = $armainhelper->arf_generate_captcha_code(10);
        
        $_SESSION['arf_form_'.$arf_current_token.'_fileuploads'] = array();

        $arf_form = '';
        if( $arf_skip_modal_html ){
            $arf_popup_data_uniq_id = array_search( $id, $arf_popup_forms );
        } else {
            $arf_popup_data_uniq_id = $arf_data_uniq_id;
        }

        $page_break_hidden_array = array();
        $arf_page_number = 0;
        $browser_info = $arrecordcontroller->getBrowser($_SERVER['HTTP_USER_AGENT']);
        /* get form data */
        if ($id) {
            $form = $arfform->getOne((int) $id);
        } else if ($key) {
            $form = $arfform->getOne($key);
        }

            /* get form data */

            $form = apply_filters('arfpredisplayform', $form);
            

            if ((is_object($form) && (isset($form->is_template) && (!isset($form->status) || $form->status == 'draft'))) && ! ($preview)) {
                $arf_form .= addslashes(esc_html__('Please select a valid form', 'ARForms'));
                return $arf_form;
            } else if (!$form || ( ($form->is_template || $form->status == 'draft'))) {
                $arf_form .= addslashes(esc_html__('Please select a valid form', 'ARForms'));
                return $arf_form;
            } else if('yes' == $hide_popup_for_loggedin_user && is_user_logged_in()) {
                return $arf_form;
            } else if ( isset($form->is_loggedin) && ( ($form->is_loggedin == 1 && !$user_ID) || ($form->is_loggedin == 2 && $user_ID) ) ) {
                global $arfsettings;
                return do_shortcode($arfsettings->login_msg);
            }
            $arfforms_loaded[] = $form;


            /* below filter have query */

            $func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($id), $id);
            /* if entry restricted */
            /* arf_dev_flag here is some confusion with last entry & restrict entry with max entries than while submitting the last entry form will be hide and error message will be shown */

            if ($func_val !='' && isset($_POST['is_submit_form_' . $id])) {
                $error_restrict_entry = json_decode($func_val);
                if (!isset($func_val->hide_forms)) {
                    return $error_restrict_entry->message;
                }
            } else if ($func_val != '' && !$navigation) {
                $error_restrict_entry = json_decode($func_val);
                return $error_restrict_entry->message;
            }
           
            /** submit button text start */
            $form_css_submit = $form->form_css = maybe_unserialize($form->form_css);

            if (is_array($form->form_css)) {
                $form_css_submit = $form->form_css;
                if ($form->form_css['arfsubmitbuttontext'] != '') {
                    $submit = $form->form_css['arfsubmitbuttontext'];
                } else {
                    $submit = $arfsettings->submit_value;
                }
            } else {             
                $submit = $arfsettings->submit_value;
            }
            /** submit button text end */

            $fields = wp_cache_get('arf_form_fields_'.$form->id);
            if( false == $fields ){
                $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);
                wp_cache_set('arf_form_fields_'.$form->id, $fields);
            }
            /* arf_dev_flag  => "there is query in below function" */


            $values = $arrecordhelper->setup_new_vars($fields, $form);
            
            /* get fields data */

            /* after submit action start */

            $params = $arrecordcontroller->get_recordparams($form);

            $saved_message = isset($form->options['success_msg']) ? '<div id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $form->options['success_msg'] . '</div></div></div>' : $arfsettings->success_msg;
            
            $saved_popup_message = isset($form->options['success_msg']) ? '<div id="arf_message_success_popup" style="display:none;"><div class="msg-detail"><div class="msg-description-success">' . $form->options['success_msg'] . '</div></div></div>' : '<div id="arf_message_success_popup" style="display:none;"><div class="msg-detail"><div class="msg-description-success">' . $arfsettings->success_msg . '</div></div></div>';

            $aweber_clientId = ARF_AWEBER_CLIENT_ID;

            $aweber_res = wp_cache_get( 'arf_aweber_responder_data' );

            if( false == $aweber_res ){
                $aweber_res = $arfform->arf_select_db_data( true, '', $MdlDb->autoresponder, '*', 'WHERE responder_id= %d', array(3), '', '', '', false, true);
                wp_cache_set( 'arf_aweber_responder_data', $aweber_res );
            }

            if( $aweber_res->is_verify == 1 ){
                
                $aweber_res_api_key = $aweber_res->responder_api_key;

                $aweber_temp_data = isset($aweber_res->list_data) ? maybe_unserialize($aweber_res->list_data) : array();

                $aweber_acc_id = isset($aweber_temp_data['acc_id']) ? $aweber_temp_data['acc_id'] : '';

                if ( !empty($aweber_res_api_key) ) {

                    $arf_aweber_access_token = get_transient('arf_aweber_access_token');

                    if ( false == $arf_aweber_access_token) {
                        
                        $arsettingcontroller->arf_aweber_refresh_token( $aweber_temp_data );

                    }
                }
            }

            if ($params['action'] == 'create' and $params['posted_form_id'] == $form->id and isset($_POST)) {

                if(isset($_REQUEST['arfsubmiterrormsg'])) {

                    $arferror_message  = ($_REQUEST['arfsubmiterrormsg'] != "") ? $_REQUEST['arfsubmiterrormsg'] : $arfsettings->failed_msg;

                    $failed_message = '<div class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">'.$arferror_message.'</div></div></div>';

                    $arf_display_error = '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container">'.$failed_message.'</div>';

                    return $arf_display_error;
                }

                $errors = isset($arfcreatedentry[$form->id]['errors']) ? $arfcreatedentry[$form->id]['errors'] : array();

                if (!empty($errors)) {
                    $created = isset($arfcreatedentry[$form->id]['entry_id']) ? $arfcreatedentry[$form->id]['entry_id'] : '';
                    if ($arfsettings->form_submit_type == 1) {

                    } else {
                        foreach ($errors as $e) {
                            if (!empty($e)) {

                                foreach ($e as $key => $val) {
                                    $failed_msg = '<div class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $val . '</div></div></div>';

                                    $message = ((isset($created) && is_numeric($created)) ? do_shortcode($saved_message) : $failed_msg);
                                    $arf_form .= '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container">' . $message . '</div>';
                                }
                            }
                        }
                        return $arf_form;
                    }
                } else {

                    if (apply_filters('arfcontinuetocreate', true, $form->id)) {

                        $created = isset($arfcreatedentry[$form->id]['entry_id']) ? $arfcreatedentry[$form->id]['entry_id'] : '';

                        $saved_message = apply_filters('arfcontent', $saved_message, $form, $created);

                        $saved_popup_message = $saved_message;
                       
                        $conf_method = apply_filters('arfformsubmitsuccess', 'message', $form, $form->options);

                        if( !isset( $form->options['arf_redirect_url_to'] ) ){
                            $form->options['arf_redirect_url_to'] = 'same_tab';
                        }

                        if ( $conf_method == 'redirect' && ( 'new_tab' == $form->options['arf_redirect_url_to'] || 'new_window' == $form->options['arf_redirect_url_to'] ) ) {
                            $saved_message = false;
                        } 
                        
                        /* For normal submission method if condition false for conditional redirect. */
                        if ($arfsettings->form_submit_type != 1 && $conf_method == 'redirect' && $saved_message == false) {
                            $conf_method = 'message';
                            $saved_message = '<div  id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $arfsettings->success_msg . '</div></div></div>';
                            $saved_popup_message = '<div id="arf_message_success_popup" style="display:none;"><div class="msg-detail"><div class="msg-description-success">'.$arfsettings->success_msg.'</div>';
                        }
                        
                        if (!$created or ! is_numeric($created)){
                            $conf_method = 'message';
                        }

                        $return_script = '';

                        $return["script"] = apply_filters('arf_after_submit_sucess_outside',$return_script,$form);

                        if (!$created or ! is_numeric($created) or $conf_method == 'message') {

                            if ($arfsettings->form_submit_type == 1) {
                                $return["conf_method"] = $conf_method;

                                /* if ajax sumssion and restrict entry than hide form at last entry */

                                if (isset($func_val['hide_forms'])&&$func_val['hide_forms']==true) {
                                    $return["hide_forms"] = $func_val['hide_forms'];
                                }
                            }

                            $failed_msg = '<div class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $arfsettings->failed_msg . '</div></div></div>';
                            
                            $message = (($created and is_numeric($created)) ? do_shortcode($saved_message) : $failed_msg);

                            if (!isset($form->options['show_form']) or $form->options['show_form']) {

                            } else {
                                if (isset($values['custom_style']) && $values['custom_style'])
                                    $arfloadcss = true;


                                if ($arfsettings->form_submit_type != 1) {

                                    $custom_css_array_form = array(
                                        'arf_form_success_message' => '#message_success',
                                        );

                                    foreach ($custom_css_array_form as $custom_css_block_form => $custom_css_classes_form) {
                                        $form->options[$custom_css_block_form] = $arformcontroller->br2nl($form->options[$custom_css_block_form]);

                                        if (isset($form->options[$custom_css_block_form]) and $form->options[$custom_css_block_form] != '') {
                                            echo '<style type="text/css">.ar_main_div_' . $form->id . ' ' . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } </style>';
                                        }
                                    }
                                }
                                $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
                                if ($arfsettings->form_submit_type == 1) {
                                    $return["message"] = '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container">' . $message . '</div>';
                                    echo json_encode($return);
                                    exit;
                                } else {
                                    if($arfmessage_rest == ''){
                                        $arf_form .= $return["script"];
                                        $arf_form .= '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container">' . $message . '</div>';
                                        $arfmessage_rest = 1;
                                    }
                                    return $arf_form;
                                }

                                if ($arfsettings->form_submit_type == 1){
                                    exit;
                                }
                            }
                        } else {
                            if ($arfsettings->form_submit_type == 1) {
                                $return["conf_method"] = $conf_method;
                            }

                            $form_options = $form->options;
                            $entry_id = $arfcreatedentry[$form->id]['entry_id'];
                            if ($conf_method == 'page' and is_numeric($form_options['success_page_id'])) {
                                global $post;
                                if ($form_options['success_page_id'] != $post->ID) {
                                    $page = get_post($form_options['success_page_id']);
                                    $old_post = $post;
                                    $post = $page;
                                    $content = apply_filters('arfcontent', $page->post_content, $form, $entry_id);
                                        $arf_old_content = get_post($post->ID)->post_content;

                                        $pattern = '\[(\[?)(ARForms|ARForms_popup)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';

                                        preg_match_all('/' . $pattern . '/s', $arf_old_content, $matches);

                                        foreach ($matches[0] as $key => $val) {
                                            $new_val = trim(str_replace(']', '', $val));
                                            $new_val1 = explode(' ', $new_val);

                                            $arf_form_id_extracted = isset($new_val1[1]) ? str_replace('id=','',$new_val1[1]) : $form->id;

                                            $var = 'id=' . $arf_form_id_extracted;
                                            $wp_upload_dir = wp_upload_dir();
                                        
                                            $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
                                            $is_material = false;
                                            $materialize_css = '';

                                            $temp_form_opts = $wpdb->get_row($wpdb->prepare("SELECT `form_css` FROM `".$MdlDb->forms."` WHERE id = %d",$arf_form_id_extracted) );

                                            if( empty($temp_form_opts) || $temp_form_opts == null ){
                                                continue;
                                            }

                                            $temp_opts = maybe_unserialize($temp_form_opts->form_css);

                                            $inputStyle = isset($temp_opts['arfinputstyle']) ? $temp_opts['arfinputstyle'] : 'material';

                                            if( $inputStyle == 'material' ){
                                                $materialize_css = 'materialize';
                                                $is_material = true;
                                            }

                                            if( $inputStyle == 'material_outlined' ){
                                                $materialize_css  = 'materialize_outlined';
                                            }
                                            if( is_ssl() ){
                                                $fid = str_replace("http://", "https://", $upload_main_url . '/maincss' . $materialize_css .'_' . $arf_form_id_extracted . '.css');
                                            } else {
                                                $fid = $upload_main_url . '/maincss' . $materialize_css .'_' . $arf_form_id_extracted . '.css';
                                            }       
                                            $return_link = "";
                                            $stylesheet_handler = 'arfformscss_'.$materialize_css.$arf_form_id_extracted;
                                            
                                            $arf_form .= stripslashes($return_link);

                                            if (trim($new_val1[1]) == $var) {
                                                $replace = $matches[0][$key];
                                            }
                                        }
                                        $arf_form .= $return["script"];
                                        $arf_form .= apply_filters('the_content', $content);

                                        if ($arfsettings->form_submit_type == 1) {
                                            $return['message'] = $arf_form;
                                        } else {
                                            return $arf_form;
                                        }
                                }
                            } else if ($conf_method == 'redirect') {
                                $success_url = apply_filters('arfcontent', $form_options['success_url'], $form, $entry_id);
                                $success_msg = isset($form_options['success_msg']) ? stripslashes($form_options['success_msg']) : addslashes(esc_html__('Please wait while you are redirected.', 'ARForms'));

                                echo "<script type='text/javascript' data-cfasync='false'> jQuery(document).ready(function($){ setTimeout(window.location='" . $success_url . "', 5000); });</script>";
                            }



                            if ($arfsettings->form_submit_type == 1) {
                                echo json_encode($return);
                                exit;
                            }
                        }
                    }
                }
            }
            /* after submit action end */

            $is_hide_form_after_submit = isset($form->options['arf_form_hide_after_submit']) ? $form->options['arf_form_hide_after_submit'] : false;

            /* popup related settings */
            if ($type != '') {

                global $arf_modal_loaded;
                $arf_modal_loaded = true;
                $open_inactivity_value = '1';
                $open_scroll_value = '10';
                $open_delay_value = '500';
                $overlay_value = '0.6';
                $data_inactive = '';
                $class_for_idle = '';
                $is_open_form_class = false;

                $is_onload = false;
                $is_scroll = false;
                $is_onexit = false;
                $is_x_seconds = false;
                $is_onideal = false;

                if ($type == 'onload') {
                    $type = 'link';
                    $is_onload = true;

                    if (!empty($open_delay) && is_numeric($open_delay)) {
                        $open_delay_value = ($open_delay * 1000);
                    }
                }

                /** New Setting for time **/
                $is_timer = false;
                if ($type == 'timer') {
                    $is_timer = true;
                    $type = 'link';
                    $is_onload = true;

                    if (!empty($open_delay) && is_numeric($open_delay)) {
                        $open_delay_value = ($open_delay * 1000);
                    }
                } else if ($type == 'x_seconds') {
                    $type = 'link';
                    $is_onload = true;
                    $is_x_seconds = true;
                    if (!empty($open_inactivity) && is_numeric($open_inactivity)) {
                        $open_inactivity_value = $open_inactivity;
                    }
                } else if ($type == 'scroll') {
                    $type = 'link';
                    $is_onload = true;
                    $is_scroll = true;
                    if (!empty($open_scroll) && is_numeric($open_scroll)) {
                        $open_scroll_value = $open_scroll;
                    }
                } else if ($type == 'on_exit') {
                    $type = 'link';
                    $is_onload = true;
                    $is_onexit = true;
                    $is_open_form_class = true;
                } else if ($type == 'on_idle') {
                    $type = 'link';
                    $is_onload = true;
                    $is_onideal = true;
                    
                    if(!empty($inactive_min) && is_numeric($inactive_min)){
                        $inactive_time = $inactive_min;
                    }else{
                        $inactive_time ='';
                    }

                    $data_inactive = 'data-inactive-minute="'.$inactive_time.'"';
                    $class_for_idle = 'arf_modal_cls';
                }


                if (is_numeric($overlay)) {
                    $overlay_value = $overlay;
                }

                if (empty($modal_width)) {
                    $modal_width = 800;
                }

                if ($is_onload) {
                    $style_onload = ' style="display:none !important;"';
                } else {
                    $style_onload = ' style="cursor:pointer;"';
                }

                if($is_open_form_class){
                    $add_class_onexit = 'show_onexit_window';
                }else{
                    $add_class_onexit = '';
                }

                if($model_effect != ''){
                    $class_modeleffect = $model_effect;
                }
                

                $checkradio_property = "";
                if ($form_css_submit['arfcheckradiostyle'] != "") {
                    if ($form_css_submit['arfcheckradiostyle'] != "none") {
                        if ($form_css_submit['arfcheckradiocolor'] != "default" && $form_css_submit['arfcheckradiocolor'] != "") {
                            if ($form_css_submit['arfcheckradiostyle'] == "custom" || $form_css_submit['arfcheckradiostyle'] == "futurico" || $form_css_submit['arfcheckradiostyle'] == "polaris") {
                                $checkradio_property = $form_css_submit['arfcheckradiostyle'];
                            } else {
                                $checkradio_property = $form_css_submit['arfcheckradiostyle'] . "-" . $form_css_submit['arfcheckradiocolor'];
                            }
                        } else {
                            $checkradio_property = $form_css_submit['arfcheckradiostyle'];
                        }
                    } else {
                        $checkradio_property = "";
                    }
                }

                $checked_checkbox_property = '';
                $checked_radio_property = '';

                if ($checkradio_property == 'custom') {
                    $arf_font_awesome_loaded = 1;

                    $checked_checkbox_property = '';
                    if ($form_css_submit['arf_checked_checkbox_icon'] != "") {
                        $checked_checkbox_property = ' arfa ' . $form_css_submit['arf_checked_checkbox_icon'];
                    } else {
                        $checked_checkbox_property = '';
                    }
                    $checked_radio_property = '';
                    if ($form_css_submit['arf_checked_radio_icon'] != "") {
                        $checked_radio_property = ' arfa ' . $form_css_submit['arf_checked_radio_icon'];
                    } else {
                        $checked_radio_property = '';
                    }
                }
                $form_name = $form->name;
                $popup_extra_attr = "";
                
                if($is_timer){
                    $popup_extra_attr .= " data-ontimer='1' data-delay='{$open_delay_value}' ";
                } else if( $is_onideal ){
                    $popup_extra_attr .= " data-onidle='1' ";
                } else if( $is_onload && !$is_scroll && !$is_onexit && !$is_x_seconds ){
                    $popup_extra_attr .= " data-onload='1' ";
                } else if( $is_scroll ){
                    $popup_extra_attr .= " data-onscroll='1' ";
                }
                
                if ($type == 'link' || $type == '') {
                    $arf_temp_code = $armainhelper->arf_generate_captcha_code(7);
                    $temp_unique_class = 'arf_modal_link_' . $arf_temp_code;
                    $arf_form .= '<div><a href="#" onclick="open_modal_box(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $is_close_link . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\', \''.$viewer_edit_id.'\');" id="arf_modal_default" '.$popup_extra_attr.' data-toggle="arfmodal" title="' . $form_name . '" data-link-popup-id="' . $arf_popup_data_uniq_id . '" class="arform_modal_link_' . $form->id . '_' . $arf_popup_data_uniq_id . ' '.$add_class_onexit.' '.$class_for_idle.' '.$temp_unique_class.' " ' . $style_onload . ' '.$data_inactive.'>' . $desc . '</a></div>';
                    if( $arf_skip_modal_html ){
                        $arf_form .= $arformcontroller->arf_get_form_style($id,$arf_data_uniq_id, $type, $position, $bgcolor, $txtcolor, $btn_angle, $modal_bgcolor, $overlay, $is_fullscrn, $inactive_min, $model_effect, true, $temp_unique_class, $modal_width);
                        return $arf_form;
                    }
                } elseif ($type != 'fly' && $type != 'sticky' && $type !='image') {
                    $arf_temp_code = $armainhelper->arf_generate_captcha_code(7);
                    $temp_unique_class = 'arf_modal_link_' . $arf_temp_code;
                    $arf_form .= '<div><button href="#" onclick="open_modal_box(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $is_close_link . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" id="arf_modal_default"  '.$popup_extra_attr.' data-toggle="arfmodal"  title="' . $form_name . '" class="arform_modal_button_' . $form->id . ' arform_modal_button_popup_' . $arf_popup_data_uniq_id . ' '.$temp_unique_class.'" ' . $style_onload . '>' . $desc . '</button></div>';
                    if( $arf_skip_modal_html ){
                        $arf_form .= $arformcontroller->arf_get_form_style($id,$arf_data_uniq_id, $type, $position, $bgcolor, $txtcolor, $btn_angle, $modal_bgcolor, $overlay, $is_fullscrn, $inactive_min, $model_effect, true, $temp_unique_class, $modal_width);
                        return $arf_form;
                    }
                } elseif ($type == 'image'){
                    $arf_temp_code = $armainhelper->arf_generate_captcha_code(7);
                    $temp_unique_class = 'arf_modal_link_' . $arf_temp_code;
                    $arfimgheight = ( $arf_img_height == 'auto') ? $arf_img_height : $arf_img_height.'px';
                    $arfimgwidth = ( $arf_img_width == 'auto') ? $arf_img_width : $arf_img_width.'px';
                    $arf_form .= '<div><img style="width:'.$arfimgwidth.'; height:'.$arfimgheight.'; cursor:pointer;" src="'.$arf_img_url.'" onclick="open_modal_box(\'' . $form->id . '\',\'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $is_close_link . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" id="arf_modal_default"  '.$popup_extra_attr.' data-toggle="arfmodal"  title="' . $form_name . '" class="arform_modal_button_' . $form->id . ' arform_modal_button_popup_' . $arf_popup_data_uniq_id . ' '.$temp_unique_class.'" ' . $style_onload . '
                        ></img></div>';
                    if( $arf_skip_modal_html ){
                        $arf_form .= $arformcontroller->arf_get_form_style($id,$arf_data_uniq_id, $type, $position, $bgcolor, $txtcolor, $btn_angle, $modal_bgcolor, $overlay, $is_fullscrn, $inactive_min, $model_effect, true, $temp_unique_class, $modal_width);
                        return $arf_form;
                    }

                }

                $form_opacity = ($form_css_submit['arfmainform_opacity'] == '' || $form_css_submit['arfmainform_opacity'] > 1) ? 1 : $form_css_submit['arfmainform_opacity'];


                $arf_popup_forms_footer_js .='function popup_tb_show(form_id, submitted)
                {
                    var last_open_modal = jQuery("[data-id=\'current_modal\']").val();
                    if (last_open_modal == "arf_modal_left")
                    {
                        jQuery(".arform_side_block_left_" + form_id).trigger("click");
                    }
                    else if (last_open_modal == "arf_modal_right")
                    {
                        jQuery(".arform_side_block_right_" + form_id).trigger("click");
                    }
                    else if (last_open_modal == "arf_modal_top")
                    {
                        setTimeout(function () {
                            jQuery(".arform_bottom_fixed_form_block_top_main").css("display", "block");
                            jQuery(".arform_bottom_fixed_form_block_top_main").css("height", "auto");
                        }, 500);
                    }
                    else if (last_open_modal == "arf_modal_bottom")
                    {
                        setTimeout(function () {
                            jQuery(".arform_bottom_fixed_form_block_bottom_main").css("display", "block");
                            jQuery(".arform_bottom_fixed_form_block_bottom_main").css("height", "auto");
                        }, 500);
                    }
                    else if (last_open_modal == "arf_modal_sitcky_left")
                    {
                        setTimeout(function () {
                            jQuery(".arform_bottom_fixed_form_block_left_main").css("display", "block");
                            jQuery(".arform_bottom_fixed_form_block_left_main").css("height", "auto");
                        }, 500);
                    }
                    else if (last_open_modal == "arf_modal_default")
                    {
                        jQuery("#arf_modal_default").trigger("click");
                        if (submitted == true) {
                            var len = jQuery(".arfmodal-backdrop").length;
                            jQuery(".arfmodal-backdrop").each(function () {

                                if (len != 1) {
                                    jQuery(this).remove();
                                }
                                len = len - 1;
                            });
                        }
                    }
                }';


                if ($type == 'link' && $is_onload) {
                    if ($is_scroll) {

                        $arf_form_all_footer_js .='var arf_open_scroll = "' . $open_scroll_value . '";
                        var arf_op_welcome = false;
                        window.onLoadClicked = false;
                        jQuery(window).scroll(function (event) {
                            var scrollPercent = 100 * jQuery(window).scrollTop() / (jQuery(document).height() - jQuery(window).height());
                            if (Math.round(scrollPercent) == arf_open_scroll) {

                            }
                        });

                        jQuery(window).scroll(function () {
                            var h = jQuery(document).height() - jQuery(window).height();
                            var sp = jQuery(window).scrollTop();
                            var p = parseInt(sp / h * 100);

                            if (p >= arf_open_scroll && arf_op_welcome == false) {
                                var mypopup_data_uniq_id = ' . $arf_popup_data_uniq_id . ';
                                if( jQuery(".arform_modal_link_' . $form->id . '_" + mypopup_data_uniq_id).length > 0 ){
                                    jQuery(".arform_modal_link_' . $form->id . '_" + mypopup_data_uniq_id).trigger("click");
                                    window.onLoadClicked = true;
                                    arf_op_welcome = true;
                                }
                            }
                        });';
                    } else if ($is_onexit){
                         $arf_form_all_footer_js .='var arf_op_exit_welcome = false;
                                var mypopup_data_uniq_id = ' . $arf_popup_data_uniq_id . ';                              

                                arf_op_exit_welcome = true;';
                                

                    } else if($is_onideal){
                        $arf_form .= '<script type="text/javascript">

                        window.arf_timer_popup = {};
                        window.arf_opened_popup = new Array();

                        function startTimer(popup_id, timer){
                            var timer = ( timer * 1000 ) * 60;
                            var timerObj = setTimeout(function(){
                                IdleTimeout(popup_id,timer);
                            },timer);
                            window.arf_timer_popup[popup_id] = timerObj;
                        }

                        function IdleTimeout(popup_id,timer){
                           
                            var modal_display = popup_id.split(" ");
                            
                            if(jQuery.inArray( modal_display[0], arf_opened_popup ) < 0){

                                jQuery("."+modal_display[0]).trigger("click");
                                arf_opened_popup.push(modal_display[0]);
                                clearTimeout(window.arf_timer_popup[popup_id]);
                            }
                        }

                        function resetTimeout(){
                            var keys = Object.keys(window.arf_timer_popup);
                            for( var x = 0; x < keys.length; x++ ){
                                var timer_key = keys[x];
                                var current_time = window.arf_timer_popup[timer_key];
                                clearTimeout(current_time);
                            }
                            init_timer();
                        }

                        function init_timer(){
                            var timer_popups = document.getElementsByClassName("arf_modal_cls");
                            for( var i = 0; i < timer_popups.length; i++ ){
                                var current_popup = timer_popups[i];
                                var inactiv_time = current_popup.getAttribute("data-inactive-minute");
                                var popup_id = current_popup.getAttribute("class");
                                startTimer(popup_id,inactiv_time);
                            }
                        }


                        </script>
                    ';

                    }
                }

                $arf_form_all_footer_js .='  jQuery(".arform_right_fly_form_block_right_main").hide();
                jQuery(".arform_left_fly_form_block_left_main").hide();
                var mybtnangle = ' . $btn_angle . ';
                var myformid = ' . $form->id . ';
                var mypopup_data_uniq_id = ' . $arf_popup_data_uniq_id . ';

                if (Number(mybtnangle) == - 90)
                {
                    jQuery(".arf_popup_" + mypopup_data_uniq_id).find(".arform_side_block_right_" + myformid + "").css("transform-origin", "bottom right");
                    jQuery(".arf_popup_" + mypopup_data_uniq_id).find(".arform_side_block_left_" + myformid + "").css("transform-origin", "top left");
                }
                else if (Number(mybtnangle) == 90)
                {
                    jQuery(".arf_popup_" + mypopup_data_uniq_id).find(".arform_side_block_right_" + myformid + "").css("transform-origin", "top right");
                    jQuery(".arf_popup_" + mypopup_data_uniq_id).find(".arform_side_block_left_" + myformid + "").css("transform-origin", "bottom left");
                }
                ';



                if ($type == 'fly') {
                    if ($position == 'right') {

                        $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:99999;">';

                        $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';
                        $arf_form .= '<span href="#" onclick="open_modal_box_fly_right(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');"  title="' . $form_name . '" class="arform_side_block_right_' . $form->id . ' arf_fly_sticky_btn">' . $desc . '</span>';

                        $arf_form .= '<div class="arform_side_fixed_form_block_right_main_' . $form->id . '">';
                        $arf_form .= '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arform_right_fly_form_block_right_main arform_sb_fx_form_right_' . $form->id . ' arf_pop_' . $arf_popup_data_uniq_id . '" style="' . $arf_modal_height . ' width: ' . $modal_width . 'px;z-index:99999; top:20%; right:-110%;">';
                    } else {

                        $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:99999;">';

                        $arf_form .= '<span href="#" onclick="open_modal_box_fly_left(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');"  title="' . $form_name . '" class="arform_side_block_left_' . $form->id . ' arf_fly_sticky_btn">' . $desc . '</span>';

                        $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';

                        $arf_form .= '<div class="arform_side_fixed_form_block_left_main_' . $form->id . '">';
                        $arf_form .= '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arform_left_fly_form_block_left_main arform_sb_fx_form_left_' . $form->id . ' arf_pop_' . $arf_popup_data_uniq_id . '" style="' . $arf_modal_height . ' width: ' . $modal_width . 'px;z-index:99999; top:20%; right:110%; ">';
                    }
                } elseif ($type == 'sticky') {
                    if ($position == 'top') {
                        $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';

                        $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_top arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:100001;">';
                        $arf_form .= '<div class="arform_bottom_fixed_form_block_top_main" style="display:none;">';
                        $arf_form .= '<div id="popup-form-' . $form->id . '"  aria-hidden="false" class="arform_bottom_fixed_form_block_top arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block;' . $arf_modal_height . ' width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;border:none;" >';
                    } else if ($position == 'left') {

                        $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_left arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:9999;">';

                        $arf_form .= '<div class="arform_bottom_fixed_block_left arf_fly_sticky_btn arform_modal_stickybottom_' . $form->id . '" onclick="open_modal_box_sitcky_left(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer; ">
                        <span href="#" data-toggle="arfmodal" title="' . $form_name . '" >' . $desc . '</span>
                    </div>';
                    $arf_form .= '<div style="clear:both;"></div>';

                    $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';

                    $arf_form .= '<div class="arform_bottom_fixed_form_block_left_main" style="float:left;  margin-left:-' . $modal_width . 'px">';
                    $arf_form .= '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arf_flymodal arform_bottom_fixed_form_block_left arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block; ' . $arf_modal_height . ' width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;  border:none;">';
                } else if ($position == 'right') {

                    $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_right arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:9999;">';
                    $arf_form .= '<div class="arform_bottom_fixed_block_right arf_fly_sticky_btn arform_modal_stickybottom_' . $form->id . '" onclick="open_modal_box_sitcky_right(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer;">
                    <span href="#" data-toggle="arfmodal" title="' . $form_name . '" >' . $desc . '</span>
                </div>';
                $arf_form .= '<div style="clear:both;"></div>';

                $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';

                $arf_form .= '<div class="arform_bottom_fixed_form_block_right_main" style="float:right; margin-right:-' . $modal_width . 'px"" >';
                $arf_form .= '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arf_flymodal arform_bottom_fixed_form_block_right arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block;' . $arf_modal_height . ' width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;border:none;">';
            } else {

                $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_bottom arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:10000;">';
                $arf_form .= '<div class="arform_bottom_fixed_block_bottom arf_fly_sticky_btn arform_modal_stickybottom_' . $form->id . '" onclick="open_modal_box_sitcky_bottom(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer;">
                <span href="#" data-toggle="arfmodal" title="' . $form_name . '" >' . $desc . '</span>
            </div>';
            $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';
            $arf_form .= '<div style="clear:both;"></div>';
            $arf_form .= '<div class="arform_bottom_fixed_form_block_bottom_main" style="display:none;">';
            $arf_form .= '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arf_flymodal arform_bottom_fixed_form_block_bottom arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block;' . $arf_modal_height . ' width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;border:none;">';
        }
    } else {
        $model_class = "";
        if( $is_fullscrn == 'yes' ){
            $model_class = "arfmodal-fullscreen";
        }
        $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';
        $arf_form .= '<div id="popup-form-' . $form->id . '" style="' . $arf_modal_height . ' width: ' . $modal_width . 'px; left: 20%;" aria-hidden="false" class="arfmodal arfhide arf_pop_' . $arf_popup_data_uniq_id . ' '.$class_modeleffect.' '.$model_class.' "  >';
    }

    $button_close_div = "";
    $inner_button_close_func = "";
    if ($type == 'fly') {
        if ($position == 'right') {
            $inner_button_close_func = 'open_modal_box_fly_left_move(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $arf_popup_data_uniq_id . '\');';
            $arf_form .= '<button id="open_modal_box_fly_right_' . $form->id . '" onclick="open_modal_box_fly_left_move(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $arf_popup_data_uniq_id . '\');" data-toggle="arfmodal" title="' . $form_name . '"  class="close_btn arf_close_btn_outer" data-poup-unique-id="'.$arf_popup_data_uniq_id.'" type="button" style="background: transparent !important;border: none !important;margin-right:1px; z-index:9999;"></button>';
        } else {
            $inner_button_close_func = 'open_modal_box_fly_right_move(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $arf_popup_data_uniq_id . '\');';
            $arf_form .= '<button id="open_modal_box_fly_left_' . $form->id . '" onclick="open_modal_box_fly_right_move(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $arf_popup_data_uniq_id . '\');" class="close_btn arf_close_btn_outer" data-poup-unique-id="'.$arf_popup_data_uniq_id.'" type="button" style="background: transparent !important;border: none !important;margin-right:1px;z-index:9999;"></button>';
        }
    } else if ($type != 'sticky') {
        $display_button = ($is_close_link == 'no') ? 'display:none;' : '';
        $arf_close_btn_class = "";
        if( $is_fullscrn == 'yes' ){
            $arf_close_btn_class = "arf_full_screen_close_btn";
        }
        $button_close_div = '<button data-modalcolor="'.$modal_bgcolor.'" data-modal-overlay="'.$overlay_value.'" data-form-id="'.$form->id.'" data-poup-unique-id="'.$arf_popup_data_uniq_id.'" class="close_btn arf_close_btn_outer '.$arf_close_btn_class.'" type="button" style="background: transparent !important;border: none !important;margin-right:15px; margin-top:15px; z-index:9999; ' . $display_button . ' " id="arf_popup_close_button"></button>';
    }

    $arfmodalbodypadding = '0';

    $arfstarttimepgno = isset($form->form_css['arfsettimestartpageno']) ? $form->form_css['arfsettimestartpageno'] : 1;
    $field_page_break_addtimer = isset($form->form_css['arfsettimer']) ? $form->form_css['arfsettimer'] : '0';

    $hide_form_class = ($is_hide_form_after_submit != false || ( $field_page_break_addtimer == '1' && $arfstarttimepgno == '1' ))  ? 'arf_hide_form_after_submit' : '';
    $arf_form .= '<div class="arfmodal-body '.$hide_form_class.'" style="padding:' . $arfmodalbodypadding . ';">';

    $arf_form .= $button_close_div;
}

/* arf_dev_flag => two queries */
if (!$preview) {
    $arformcontroller->arf_create_visit_entry($form->id);
}

$page_num = isset($values['total_page_break']) ? $values['total_page_break'] : 0;


global $arf_total_page_break;
$arf_total_page_break = $page_num;

if ($page_num > 0) {
    $temp_calss = 'arfpagebreakform';
} else {
    $temp_calss = '';
}

$arf_form .= $arformcontroller->arf_get_form_style($id,$arf_data_uniq_id, $type, $position, $bgcolor, $txtcolor, $btn_angle, $modal_bgcolor, $overlay, $is_fullscrn, $inactive_min, $model_effect,false, '', $modal_width);



$arf_form .= '<div class="arf_form ar_main_div_' . $form->id . ' arf_form_outer_wrapper " id="arffrm_' . $form->id . '_container">';

if( $type != "" && $type != 'sticky' && $is_fullscrn != 'yes' ){
    $arf_form .= "<style type='text/css'>.arf_close_btn_outer[data-poup-unique-id='{$arf_popup_data_uniq_id}']{display:none !important;}</style>";
    $arf_form .= '<button data-modalcolor="'.$modal_bgcolor.'" onclick="'.$inner_button_close_func.'" data-modal-overlay="'.$overlay_value.'" data-form-id="'.$form->id.'" data-poup-unique-id="'.$arf_popup_data_uniq_id.'" class="close_btn arf_close_btn_inner '.((isset($arf_close_btn_class) && $arf_close_btn_class =!'')?$arf_close_btn_class:'').'" type="button" style="background: transparent !important;border: none !important;margin-right:15px; margin-top:15px; z-index:99991; ' . ((isset($display_button) && $display_button =!'')?$display_button:'') . ' " id="arf_popup_close_button"></button>';
}

$arf_form = apply_filters('arf_predisplay_form', $arf_form, $form);
do_action('arf_predisplay_form' . $form->id, $form);


if (isset($preview) and $preview) {
    $arf_form .= '<div id="form_success_' . $form->id . '" style="display:none;">' . $saved_message . '</div>';
}

if( !isset( $form->form_css['arfsuccessmsgposition'] ) || ( isset($form->form_css['arfsuccessmsgposition']) && $form->form_css['arfsuccessmsgposition'] == 'top' ) ){
    $arf_form .= '<div id="arf_message_error" class="frm_error_style" style="display:none;"><div class="msg-detail"><div class="msg-description-success">' . addslashes(esc_html__('Form Submission is restricted', 'ARForms')) . '</div></div></div>';
}



/* arf_dev_flag consider action `arfformclasses` */


$form_attr = '';
$formRandomID = $form->id.'_'.$armainhelper->arf_generate_captcha_code('10');

if( 1 != $arfsettings->hidden_captcha ){
    $captcha_code = $armainhelper->arf_generate_captcha_code('8');

    $form_attr .= ' data-random-id="' . $formRandomID . '" ';
    $form_attr .= ' data-submission-key="' . $captcha_code . '" ';
    $form_attr .= ' data-key-validate="false" ';
} else {
    $form_attr .= ' data-key-validate="true" ';
}
if( isset($arf_modal_loaded) and $arf_modal_loaded ){
    $arf_form .='<div class="arf_content_another_page" style="display:none;"></div>';
}

/* reputelog - need to check this code */
$arf_form .= $saved_popup_message;


$is_hide_form = "";
if($func_val != '' && $navigation){
    $is_hide_form = "display:none;";
    $error_restrict_entry = json_decode($func_val);
    $arf_form .= $error_restrict_entry->message;
}

if (isset($preview) and $preview) {
    $arf_form .= '<form enctype="' . apply_filters('arfformenctype', 'multipart/form-data', $form) . '" method="post" class="arfshowmainform arfpreivewform ' . $temp_calss . ' ' . do_action('arfformclasses', $form) . ' " data-form-id="form_' . $form->form_key . '" novalidate="" data-id="' . $arf_data_uniq_id . '" data-popup-id="' . $arf_popup_data_uniq_id . '" '.$form_attr.'>';
} else {
    $action_html = ($arfsettings->use_html) ? '' : 'action=""';
    $arf_form .= '<form enctype="' . apply_filters('arfformenctype', 'multipart/form-data', $form) . '" method="post" class="arfshowmainform ' . $temp_calss . ' ' . do_action('arfformclasses', $form) . '" style="'.$is_hide_form.'" data-form-id="form_' . $form->form_key . '" ' . $action_html . ' novalidate="" data-id="' . $arf_data_uniq_id . '" '. $form_attr ;
    if ($type != '') {
        $arf_form .=' data-popup-id="' . $arf_popup_data_uniq_id . '">';
    } else {
        $arf_form .=' data-popup-id="">';
    }
}

$arf_form .= $arfieldhelper->get_form_pagebreak_fields($form->id,$form->form_key,$values);
if( 1 != $arfsettings->hidden_captcha ){
    $arf_form .= "<input type='text' name='arf_filter_input' data-jqvalidate='false' data-random-key='{$formRandomID}' value='' style='opacity:0 !important;display:none !important;visibility:hidden !important;' />";
    $arf_form .= "<input type='hidden' id='arf_ajax_url' value='".admin_url('admin-ajax.php')."' />";
    $arf_form .= do_shortcode('[arf_spam_filters]');
}

$arf_form .= $arf_repeater_field_class->arf_check_repeater_field( $fields );

/* arf_dev_flag =>  $form_action passed fixed value */
$form_action = 'create';
$loaded_field = isset( $form->options['arf_loaded_field'] ) ? $form->options['arf_loaded_field'] : array();
$arf_form .= $arformcontroller->arf_get_form_hidden_field($form, $fields, $values, $preview , $is_widget_or_modal, $arf_data_uniq_id, $form_action, $loaded_field, $type, $is_close_link,$arf_current_token,$viewer_edit_id);
    $arf_form .= '<input type="hidden" id="arf_form_site_wide_'.$form->id.'" value="'.$is_site_wide.'" />';



$arf_form .='<div class="allfields"  style="visibility:hidden;height:0;">';

/* arf_dev_flag => loop and seperate function */
$totalpass = 0;

if (count(array_intersect(array('imagecontrol', 'password', 'email'), $loaded_field))) {

    foreach ($values['fields'] as $arrkey => $field) {

        if ($field['type'] == 'imagecontrol') {
            $arf_form .= $arformcontroller->arf_front_display_image_field($field);
        }
        
        /** for confirm email and confirm password arf_dev_flag ( query and loop) */
        $field['id'] = $arfieldhelper->get_actual_id($field['id']);
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

        if ($field['type'] == 'email' && $field['confirm_email']) {


            if (isset($field['confirm_email']) and $field['confirm_email'] == 1 and isset($arf_load_confirm_email['confirm_email_field']) and $arf_load_confirm_email['confirm_email_field'] == $field['id']) {
                $values['confirm_email_arr'][$field['id']] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
            } else {
                $arf_load_confirm_email['confirm_email_field'] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
            }
            $confirm_email_field = $arfieldhelper->get_confirm_email_field($field);
            $values['fields'] = $arfieldhelper->array_push_after($values['fields'], array($confirm_email_field), $arrkey + $totalpass);
            $totalpass++;
        }
    }
}

$inputStyle = isset($form->form_css['arfinputstyle']) ? $form->form_css['arfinputstyle'] : 'standard';

$form_class = ($inputStyle == 'material') ? 'arf_materialize_form' : 'arf_' . $inputStyle . '_form';
if( $inputStyle == 'material_outlined' ){
    $form_class = 'arf_material_outline_form';
}
$arf_form .= '<div class="arf_fieldset ' . $form_class . '" id="arf_fieldset_' . $arf_data_uniq_id . '">';



$arf_form .= $arformcontroller->arf_load_form_css($form->id,$inputStyle);


/* arf_dev_flag =>"Old method have function I have removed . not necessary may be. there was filter `arfformreplaceshortcodes` which need to consider or not o_O" */
if (isset($form->options['display_title_form']) && $form->options['display_title_form'] == 1) {

    $arf_form .='<div class="arftitlecontainer">';

    if (isset($form->name) && $form->name != '') {
        $arf_form .='<div class="formtitle_style">' . html_entity_decode( stripslashes($form->name) ) . '</div>';
    }
    if (isset($form->description) && $form->description != '') {
        $arf_form .='<div class="arf_field_description formdescription_style">' . html_entity_decode( stripslashes($form->description) ) . '</div>';
    }

    $arf_form .= '</div>';
}

$is_recaptcha = 0;


$i = 1;
$field_page_break_type = '';
$field_page_break_type_possition = '';
$field_page_break_top_bar = 0;
$field_page_break_step_clickable = 0;
$field_page_break_wizard_theme = '';
$field_page_break_addtimer = isset($form->form_css['arfsettimer']) ? $form->form_css['arfsettimer'] : '0';
if ($values['fields'] and $page_num > 0) {
    $cntr_break = 0;

    /** arf_dev_flag => Loop */
    foreach ($values['fields'] as $field) {
        if ($field['type'] == 'break') {
            if ($cntr_break == 0 && $i == 1) {
                $progressbarlabel = !empty( $field['progressbarlabel'] ) ? $field['progressbarlabel'] : 'Step {arf_page} of {arf_total}';
                $field_page_break_type = $field['page_break_type'];
                $field_page_break_type_possition = $field['page_break_type_possition'];
                $field_page_break_top_bar = isset($field['pagebreaktabsbar']) ? $field['pagebreaktabsbar'] : 0;
                $field_page_break_step_clickable = isset($field['pagebreakclickable']) ? $field['pagebreakclickable'] : 0;
                $field_page_break_wizard_theme = isset($field['page_break_wizard_theme']) ? $field['page_break_wizard_theme'] : 'style1' ;
                $field_page_break_addtimer = isset($form->form_css['arfsettimer']) ? $form->form_css['arfsettimer'] : 0; 
                $arfshowunits = isset($form->form_css['showunits_breakfield']) ? $form->form_css['showunits_breakfield'] : '';
                $arfsettimeron = isset($form->form_css['arfpagebreaksettimeron']) ? $form->form_css['arfpagebreaksettimeron'] : '';
                $arfunithrs = !empty($form->form_css['arfaddtimerbreakfieldhrs']) ? $form->form_css['arfaddtimerbreakfieldhrs'] : 0;
                $arfunitmin = !empty($form->form_css['arfaddtimerbreakfieldmin']) ? $form->form_css['arfaddtimerbreakfieldmin'] : 0;
                $arfunitsec = !empty($form->form_css['arfaddtimerbreakfieldsec']) ? $form->form_css['arfaddtimerbreakfieldsec'] : 0;
                $arfstarttimepgno = !empty($form->form_css['arfsettimestartpageno']) ? $form->form_css['arfsettimestartpageno'] : 1;
                $arfendtimepgno = !empty($form->form_css['arfsettimeendpageno']) ? $form->form_css['arfsettimeendpageno'] : 0;
                $arfpagebreakstyle = isset($form->form_css['arftimerstyle']) ? $form->form_css['arftimerstyle'] :'';
                $arftimerbgcolor = isset($form->form_css['timer_bg_color']) ? $form->form_css['timer_bg_color'] : '';
                $arftimerforgroundcolor = isset($form->form_css['timer_forground_color']) ? $form->form_css['timer_forground_color'] : '';
            }
            $field_pre_page_title = $field['pre_page_title'];
            $i++;
        }
    }
    
            
    if($field_page_break_addtimer == 1 && $arfshowunits != '' && (($arfunithrs != '' && $arfunithrs != 0 ) || ($arfunitmin != '' && $arfunitmin != 0 ) || ($arfunitsec != '' && $arfunitsec != 0 ))){
        
        $arf_current_date_time = date('Y-j-m H:i:s');

        $arf_pagebreak_style_class = '';

        if($arfpagebreakstyle == 'number'){
            $arf_pagebreak_style_class = 'pagebreak_style_number';
        }else if($arfpagebreakstyle == 'circle'){
            $arf_pagebreak_style_class = 'pagebreak_style_circle';
        }else{
            $arf_pagebreak_style_class = 'pagebreak_style_circlewithtxt';
        }

        $totalshowunit = explode(',', $arfshowunits);

        $arf_totalshowunit_cls = "";

        if(in_array('sec',$totalshowunit) && !in_array('min',$totalshowunit) && !in_array('hrs',$totalshowunit)){
            if($arfunitsec >= 60){
                $arf_totalshowunit_cls .= ' arf_pagebreak_sec';
                $arf_totalshowunit_cls .= ' arf_pagebreak_min';
                if( $arfunitsec >=3600 ){
                    $arf_totalshowunit_cls .= ' arf_pagebreak_hrs';
                }
            }else{
                $arf_totalshowunit_cls .= ' arf_pagebreak_sec';
            }
        }
        if(in_array('min',$totalshowunit) && !in_array('hrs',$totalshowunit)){
            $arf_totalshowunit_cls .= ' arf_pagebreak_min';
            $arf_totalshowunit_cls .= ' arf_pagebreak_sec';
        }
        if(in_array('hrs',$totalshowunit)){
            $arf_totalshowunit_cls .= ' arf_pagebreak_hrs';
            $arf_totalshowunit_cls .= ' arf_pagebreak_min';
            $arf_totalshowunit_cls .= ' arf_pagebreak_sec';
        }

        $days = addslashes(esc_html__('Days', 'ARForms'));
        $hours = addslashes(esc_html__('Hours','ARForms'));
        $minutes = addslashes(esc_html__('Minutes','ARForms'));
        $seconds = addslashes(esc_html__('Seconds','ARForms'));

        if($field_page_break_type_possition == 'top'){
            $arf_form .= '<div id="pagebreaktime" data-direction="'.(is_rtl() ? 'rtl' : 'ltr').'" class="arf_pagebreaktime" > 

                    <div class="arf_pagebreak_timer '.$arf_pagebreak_style_class.' '.$arf_totalshowunit_cls.'" id="arf_date_time_countdown-'.$form->id.'-'.$arf_data_uniq_id.'" data-pagebreak-style="'.$arfpagebreakstyle.'" data-enabled-units="'.$arfshowunits.'" data-timer-seton="'.$arfsettimeron.'"  data-timer-hours="'.$arfunithrs.'" data-timer-minutes="'.$arfunitmin.'" data-timer-seconds="'.$arfunitsec.'" start-timer-pgno="'.$arfstarttimepgno.'" end-timer-pgno="'.$arfendtimepgno.'" data-timer="'.$arf_current_date_time.'" data-timer-bgcolor="'.$arftimerbgcolor.'" data-timer-forgroundcolor="'.$arftimerforgroundcolor.'" data-text-hour="'.$hours.'" data-text-min="'.$minutes.'" data-text-sec="'.$seconds.'"></div>
                    </div>';
            $arf_count_total_page = $arf_total_page_break + 1;
            
            if($arfstarttimepgno <= $arf_count_total_page ){
                
                if(!($type == 'sticky' || $type == 'fly') ){

                    $arf_form_all_footer_js .= 'arf_pagebreak_startTimer("arf_date_time_countdown-'.$form->id.'-'.$arf_data_uniq_id.'");';
                }
            }
        }
    }

    if ($field_page_break_type == 'survey' && $field_page_break_type_possition=='top') {

        $total_page_shows = $page_num;
        if($field_page_break_top_bar != 1) {
               $arf_progressbarlabel = str_replace( array('{arf_page}','{arf_total}' ), 
                                                         array('<span id="current_survey_page" class="current_survey_page">1</span><span class="survey_middle"> </span>','<span id="total_survey_page" class="total_survey_page">'. ($total_page_shows + 1) . '</span>'), 
                                                         $progressbarlabel );
           
                    $arf_form .= '<div class="arf_survey_nav"><div id="current_survey_page" class="survey_step">' . $arf_progressbarlabel . '</div></div>';


            $arf_form .= '<div style="clear:both; margin-top:25px;"></div><div id="arf_progress_bar" style="margin-bottom:20px; clear:both;" class="ui-progress-bar"><div class="ui-progressbar-value" ><span class="ui-label"></span></div></div>';
        }
    } else {
        $total_page_shows = $page_num;
    }

    if( $field_page_break_type == 'wizard' && 1 == $field_page_break_step_clickable ){
        $arf_form .= "<input type='hidden' id='arf_previous_page_clickable_".$form->id."' name='arf_previous_page_clickable_".$form->id."' value='1' />";
    }else{
        $arf_form .= "<input type='hidden' id='arf_previous_page_clickable_".$form->id."' name='arf_previous_page_clickable_".$form->id."' value='0' />";
    }

    if( $field_page_break_addtimer == 1 ){
        
        $arf_form .= "<input type='hidden' id='pagebreak_addtimer_".$form->id."' name='pagebreak_addtimer_".$form->id."' value='1' />";
    }else{
        
        $arf_form .= "<input type='hidden' id='pagebreak_addtimer_".$form->id."' name='pagebreak_addtimer_".$form->id."' value='0' />";
    }

}

$i = 1;
if ($page_num > 0) {

    $td_width_w = number_format((100 / ($total_page_shows + 1)), 3);
    $td_width = $td_width_w . "%";
}

$temp_field_order = json_decode($form->options['arf_field_order'],true);
asort( $temp_field_order );


$page_break_fields = array();
foreach( $temp_field_order as $tfid => $tfod ){
	foreach( $values['fields'] as $temp_fields ){
		if( 'break' == $temp_fields['type'] && $temp_fields['id'] == $tfid ){
			$page_break_fields[] = $temp_fields;
		}
	}
}

if ($values['fields'] and $page_num > 0) {
    if($field_page_break_top_bar != 1) {
        $enterrowdata = "";
        if ($field_page_break_type == 'wizard' && $field_page_break_type_possition=='top') {
            if( 'style3' == $field_page_break_wizard_theme ){
                $arf_form .= '<div id="arf_wizard_table" class="arf_wizard arf_wizard_style3 arf_wizard_top">';
            }else if( 'style2' == $field_page_break_wizard_theme ){
                $arf_form .= '<div id="arf_wizard_table" class="arf_wizard arf_wizard_style2 arf_wizard_top">';
            }else{
                $arf_form .= '<div id="arf_wizard_table" class="arf_wizard arf_wizard_style1 arf_wizard_top">';
            }
            $arf_form .= '<div class="arf_wizard_upper_tab">';
        
            $cntr_break = 0;
            foreach ($page_break_fields as $field) {
                $field_type = $field['type'];
                $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                if ($field_type == "break") {
                    $first_page_break_field_val = $field; //first page break field
                    $display_page_break = "";

                    $field_first_page_label = $field['first_page_label'];
                    $field_second_page_label = $field['second_page_label'];
                    $field_pre_page_title = $field['pre_page_title'];
                    if ($cntr_break == 0 && $i == 1) {
                        $field_page_break_type = $field['page_break_type'];
                    }
                    if ($field_page_break_type == "wizard") {
                        if ($cntr_break == 0 && $i == 1) {

                            $arf_form .= '<div style="width:' . $td_width . ';" id="page_nav_' . $i . '" class="page_break_nav page_nav_selected">' . $field_first_page_label . '</div>';
                            $i++;
                            $arf_form .= '<div style="width:' . $td_width . '; ' . $display_page_break . '" id="page_nav_' . $i . '" class="page_break_nav">' . $field_second_page_label . '</div>';
                            $cntr_break++;
                        } else {
                            $arf_form .= '<div style="width:' . $td_width . '; ' . $display_page_break . '" id="page_nav_' . $i . '" class="page_break_nav">' . $field_second_page_label . '</div>';
                        }
                        $i++;
                        $enterrowdata = "<br>";
                    }
                }
                $field_name = 'item_meta[' . $field['id'] . ']';
            }

            if ($field_page_break_type == 'wizard') {
                $arf_form .= '</div>';
            }

            $cntr_break = 0;
            $i = 1;
            if ($field_page_break_type == 'wizard') {
                if('style2' == $field_page_break_wizard_theme || 'style3' == $field_page_break_wizard_theme){
                    $arf_form .= '<div class="arf_wizard_lower_tab" style="display: none;">';
                }else{
                    $arf_form .= '<div class="arf_wizard_lower_tab">';
                }
            }

            foreach ($values['fields'] as $field) {
                $field_type = $field['type'];
                $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                if ($field_type == "break") {
                    $field_first_page_label = $field['first_page_label'];
                    $field_second_page_label = $field['second_page_label'];
                    $field_pre_page_title = $field['pre_page_title'];
                    if ($cntr_break == 0 && $i == 1) {
                        $field_page_break_type = $field['page_break_type'];
                    }
                    if ($field_page_break_type == "wizard") {
                        $display = '';

                        if ($cntr_break == 0 && $i == 1) {

                            $arf_form .= '<div style="width:' . $td_width . '; padding:0;" id="page_nav_arrow_' . $i . '" class="page_break_nav page_nav_selected"><div class="arf_current_tab_arrow"></div></div>';
                            $i++;
                            $arf_form .= '<div style="width:' . $td_width . ';padding:0;' . $display . '" id="page_nav_arrow_' . $i . '" class="page_break_nav"></div>';
                            $cntr_break++;
                        } else {
                            $arf_form .= '<div style="width:' . $td_width . ';padding:0;' . $display . '" id="page_nav_arrow_' . $i . '" class="page_break_nav"></div>';
                        }
                        $i++;
                        if('style2' == $field_page_break_wizard_theme){
                            $enterrowdata = "<div class='arf_wizard_clear' style='clear:both; height:100px;'></div>";
                        }else{
                            $enterrowdata = "<div class='arf_wizard_clear' style='clear:both; height:15px;'></div>";
                        }
                    }
                }
                $field_name = 'item_meta[' . $field['id'] . ']';
            }

        
            $arf_form .= '</div>';
            $arf_form .= '</div>' . $enterrowdata;
        }
    }
}

        /* if page break than get page break tab end */


        /* get all field html */
        $arf_form .='<div id="page_0" class="page_break">';

        if(isset($arf_preset_data)){
           
            $arf_arr_preset_data = array();
            $arf_preset_data_new = explode('~!~',$arf_preset_data);
                   
            foreach ($arf_preset_data_new as $key => $value) {
                
                $arf_preset_data_final   = explode('||', $value);
                $arf_preset_data_id      = str_replace('item_meta_', '', $arf_preset_data_final[0]);

                if(isset($arf_preset_data_final[1]) && preg_match("^!^",$arf_preset_data_final[1])){

                    $arf_preset_data_final[1] = explode("^!^", $arf_preset_data_final[1]);
                }
                $arf_preset_data_value   = isset($arf_preset_data_final[1]) ? $arf_preset_data_final[1] : '';
                $arf_arr_preset_data[$arf_preset_data_id] = $arf_preset_data_value;
            }
        }
        $arf_glb_preset_data = $arf_arr_preset_data;
        
        $arf_form .= $arformcontroller->get_all_field_html($form,$values,$arf_data_uniq_id,$fields,$preview,$errors,$inputStyle,$arf_arr_preset_data);

        $captcha_key = $arformcontroller->arfSearchArray('captcha','type',$values['fields']);
        if( '' != $captcha_key ){
            $is_recaptcha = 1;
        }


        /* if section started than end it */
        global $arf_section_div;
        if ($arf_section_div) {
            $arf_form .= "<div class='arf_clear'></div></div>";
            $arf_section_div = 0;
        }

        /* arf_dev_flag action to filter conversion affects paypalpro addon authorise.net addon */
        $arf_form = apply_filters('arfentryform', $arf_form, $form, $form_action, $errors);
        /* get all field html */
        $arf_form .='<div style="clear:both;height:1px;">&nbsp;</div>';
        $arf_form .='</div><!-- page_break && page_0-->';

        /*         * * page break another setting */
        $page_break_hidden_array[$form->id]['data-hide'] = '';
        if ($page_num > 0) {
            if (isset($page_break_hidden_array[$form->id]))
                $page_break_hidden_array[$form->id]['data-hide'] = ',' . $page_break_hidden_array[$form->id]['data-hide'];
        }

        if (!$form->is_template and $form->id != '') {
            if ($page_num == 1) {

                $display_submit = $display_previous = 'style="display:none;"';
                if ($display_submit == '') {
                    $is_submit_form = 0;
                    $last_show_page = 0;
                } else {
                    $is_submit_form = 1;
                    $last_show_page = 1;
                }
            } else if ($page_num > 1) {
                $total_page_number = $arf_page_number;
                $last_show_page = $arf_page_number;

                $compare_value = explode(',', $page_break_hidden_array[$form->id]['data-hide']);

                foreach ($compare_value as $k1 => $v1) {
                    if (is_null($v1) || $v1 == '')
                        unset($compare_value[$k1]);
                }

                for ($i = 0; $i <= (int)$total_page_number; $i++) {

                    if (in_array($i, $compare_value)) {
                        continue;
                    } else {
                        $last_show_page = $i;
                    }
                }


                if ($last_show_page == 0) {
                    $display_submit = '';
                    $display_previous = 'style="display:none;"';
                    /* arf_dev_flag in line css */
                    $arf_form .= '<style type="text/css">.ar_main_div_' . $form->id . ' #arf_submit_div_0 { display:none; }</style>';
                    $is_submit_form = 0;
                } else {
                    $display_submit = 'style="display:none;"';
                    $display_previous = 'style="display:none;"';
                    $is_submit_form = 1;
                }
            } else {
                $display_submit = 'style="display:none;"';
                $display_previous = '';
                $is_submit_form = 1;
            }

            if (isset($preview) and $preview) {
                global $style_settings;


                $aweber_arr = "";
                $aweber_arr = $form->form_css;

                $arr = maybe_unserialize($aweber_arr);

                /* arf_dev_flag loop */
                $newarr = array();
                foreach ($arr as $k => $v)
                    $newarr[$k] = $v;

                $submit_height = ($newarr['arfsubmitbuttonheightsetting'] == '') ? '35' : $newarr['arfsubmitbuttonheightsetting'];
                $padding_loading_tmp = $submit_height - 24;
                $padding_loading = $padding_loading_tmp / 2;

                $submit_width = isset($newarr['arfsubmitbuttonwidthsetting']) ? $newarr['arfsubmitbuttonwidthsetting'] : '';

                $submit_width_loader = ($submit_width == '') ? '1' : $submit_width;
                $width_loader = ($submit_width_loader / 2);
                $width_to_add = $submit_width_loader;
                $top_margin = $submit_height + 5;
                $label_margin = isset($newarr['width']) ? $newarr['width'] : 0;
                $label_margin = $label_margin + 15;

                $arf_form .= '<div class="arfsubmitbutton ' . $_SESSION['label_position'] . '_container" ';

                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= 'id="page_last"';
                    $arf_form .= $display_submit;
                }
                $arf_form .= '>';
                $arf_form .= '<div class="arf_submit_div ' . $_SESSION['label_position'] . '_container">';

                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= '<input type="button" value="' . $field_pre_page_title . '" ' . $display_previous . ' name="previous" data-id="previous_last" class="previous_btn" onclick="go_previous(\'' . ($arf_page_number - 1) . '\', \'' . $form->id . '\', \'no\', \'' . $form->form_key . '\', \'' . $arf_data_uniq_id . '\');"  />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $arf_page_number . '" name="last_page_id" data-id="last_page_id"  />';
                }

                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= '<input type="hidden" value="1" data-jqvalidate="false" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" data-last="' . $last_show_page . '" value="' . $last_show_page . '" name="last_show_page_' . $form->id . '" data-id="last_show_page_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $is_submit_form . '" data-val="1" data-hide="' . $page_break_hidden_array[$form->id]['data-hide'] . '" data-max="' . $arf_page_number . '" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $page_break_hidden_array[$form->id]['data-hide'] . '" name="get_hidden_pages_' . $form->id . '" data-id="get_hidden_pages_' . $form->id . '" />';
                } else {
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="1" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="0" data-val="0" data-max="0" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                }

                $submit = apply_filters('getsubmitbutton', $submit, $form);
                $is_submit_hidden = false;
                $submitbtnstyle = '';
                $submitbtnclass = '';
                $arfsubmitbuttonstyle = isset($form->form_css['arfsubmitbuttonstyle']) ? $form->form_css['arfsubmitbuttonstyle'] : 'border'; 
                
                $sbmt_class = "";
                if( $inputStyle == 'material' ){
                    $sbmt_class = "btn btn-flat";
                }
                $arfbrowser_name = strtolower(str_replace(' ','_',$browser_info['name']));
                $submit_btn_content = '<button class="arf_submit_btn arf_submit_btn_'.str_replace(' ','_',$arfsubmitbuttonstyle).' arfstyle-button  '.$sbmt_class.' ' . $submitbtnclass . ' '.$arfbrowser_name.'" id="arf_submit_btn_' . $arf_data_uniq_id . '" name="arf_submit_btn_' . $arf_data_uniq_id . '" data-style="zoom-in" ' . $submitbtnstyle;
                $submit_btn_content = apply_filters('arf_add_submit_btn_attributes_outside', $submit_btn_content, $form);
                $submit_btn_content .= ' ><span class="arfsubmitloader"></span><span class="arfstyle-label">' . esc_attr($submit) . '</span><span class="arf_ie_image" style="display:none;">';
                if (( $browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' ) || $browser_info['name'] == 'Opera') {
                    $submit_btn_content .= '<img src="' . ARFURL . '/images/submit_btn_image.gif" style="width:24px; box-shadow:none;-webkit-box-shadow:none;-o-box-shadow:none;-moz-box-shadow:none; vertical-align:middle; height:24px; padding-top:' . $padding_loading . 'px" />';
                }
                $submit_btn_content .= '</span></button>';


                $arf_form .= $submit_btn_content;

                $arf_form .= '</div><input type="hidden" name="submit_btn_image" id="submit_btn_image" value="' . ARFURL . '/images/submit_loading_img.gif" /></div><div style="clear:both"></div>';
            } else {

                $arf_form .= '<div class="arfsubmitbutton ' . $_SESSION['label_position'] . '_container" ';
                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= 'id="page_last" ';
                    $arf_form .= $display_submit;
                }

                $arf_form .= '>';
                $sbtm_wrapper_class = "";
                if( $inputStyle == 'material' ){
                    $sbtm_wrapper_class = "file-field ";
                }
                $arf_form .= '<div class="arf_submit_div '.$sbtm_wrapper_class.' ' . $_SESSION['label_position'] . '_container">';
                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= '<input type="button" value="' . $field_pre_page_title . '" ' . $display_previous . ' name="previous" data-id="previous_last" class="previous_btn" onclick="go_previous(\'' . ($arf_page_number - 1) . '\', \'' . $form->id . '\', \'no\', \'' . $form->form_key . '\', \'' . $arf_data_uniq_id . '\');"  />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $arf_page_number . '" name="last_page_id" data-id="last_page_id" />';
                }


                if ($arf_page_number > 0 and $page_num > 0) {
                   
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="1" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" data-last="' . $last_show_page . '" value="' . $last_show_page . '" name="last_show_page_' . $form->id . '" data-id="last_show_page_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $is_submit_form . '" data-val="1" data-hide="' . $page_break_hidden_array[$form->id]['data-hide'] . '" data-max="' . $arf_page_number . '" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $page_break_hidden_array[$form->id]['data-hide'] . '" name="get_hidden_pages_' . $form->id . '" data-id="get_hidden_pages_' . $form->id . '" />';
                } else {

                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="1" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="0" data-val="0" data-max="0" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                }

                $submit = apply_filters('getsubmitbutton', $submit, $form);
                $is_submit_hidden = false;
                $submitbtnstyle = '';
                $submitbtnclass = '';
                
                $arfsubmitbuttonstyle = isset($form->form_css['arfsubmitbuttonstyle']) ? $form->form_css['arfsubmitbuttonstyle'] : 'border';
                $submit_btn_content = '';

                $sbmt_class = "";
                if( $inputStyle == 'material' ){
                    $sbmt_class = "btn btn-flat";
                }
                

                $arfbrowser_name = strtolower(str_replace(' ','_',$browser_info['name']));
                $submit_btn_content .= '<button class="arf_submit_btn  arf_submit_btn_'.str_replace(' ','_',$arfsubmitbuttonstyle).' '.$sbmt_class.'  btn-info arfstyle-button ' . $submitbtnclass .' '.$arfbrowser_name.'"  id="arf_submit_btn_' . $arf_data_uniq_id . '" name="arf_submit_btn_' . $arf_data_uniq_id . '" data-style="zoom-in" ';
                
                $submit_btn_content = apply_filters('arf_add_submit_btn_attributes_outside', $submit_btn_content, $form);

                $submit_btn_content .= $submitbtnstyle . ' >';

                $submit_btn_content .= '<span class="arfsubmitloader"></span><span class="arfstyle-label">' . esc_attr($submit) . '</span>';
                if (( $browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' ) || $browser_info['name'] == 'Opera') {
                    $padding_loading = isset($padding_loading) ? $padding_loading : '';
                    $submit_btn_content .= '<span class="arf_ie_image" style="display:none;">';
                    $submit_btn_content .= '<img src="' . ARFURL . '/images/submit_btn_image.gif" style="width:24px; box-shadow:none;-webkit-box-shadow:none;-o-box-shadow:none;-moz-box-shadow:none; vertical-align:middle; height:24px; padding-top:' . $padding_loading . 'px;"/>';
                    $submit_btn_content .= '</span>';
                }
                
                $submit_btn_content .= '</button>';


                $arf_form .= $submit_btn_content;


                $arf_form .='</div></div><div style="clear:both"></div>';
            }
        } else {

            $arf_form .= '<p class="arfsubmitbutton ' . $_SESSION['label_position'] . '_container">';
            $submit = apply_filters('getsubmitbutton', $submit, $form);
            $arf_form .= '<input type="submit" value="' . esc_attr($submit) . '" onclick="return false;" ';
            $arf_form = apply_filters('arfactionsubmitbutton', $arf_form, $form, $form_action);
            $arf_form .= '/>';
            $arf_form .= '<div id="submit_loader" class="submit_loader" style="display:none;"></div></p>';
        }
        /**         * page break another setting */
        /* arf_dev_flag we can use global variable of global settings */
        if ($field_page_break_type == 'survey' && $field_page_break_type_possition=='bottom') {

            $total_page_shows = $page_num;
            if($field_page_break_top_bar != 1) {
                
                $arf_progressbarlabel = str_replace( array('{arf_page}','{arf_total}' ), 
                                                         array('<span id="current_survey_page" class="current_survey_page">1</span><span class="survey_middle"> </span>','<span id="total_survey_page" class="total_survey_page">'. ($total_page_shows + 1) . '</span>'), 
                                                         $progressbarlabel );
                    $arf_form .= '<div class="arf_survey_nav arf_bottom_survey_nav"><div id="current_survey_page" class="survey_step">' . $arf_progressbarlabel . '</div></div>';



                $arf_form .= '<div id="arf_progress_bar" style="margin-bottom:20px; clear:both;" class="ui-progress-bar"><div class="ui-progressbar-value" ><span class="ui-label"></span></div></div>';
            }
        }
        
        $i = 1;
        if ($page_num > 0) {

            $td_width_w = number_format((100 / ($total_page_shows + 1)), 3);
            $td_width = $td_width_w . "%";
        }

        if ($values['fields'] and $page_num > 0) {
            if($field_page_break_top_bar != 1) {
                $enterrowdata = "";
                if ($field_page_break_type == 'wizard' && $field_page_break_type_possition=='bottom') {
                    $arf_form .= "<div class='arf_wizard_clear' style='clear:both; height:30px;'></div>";
                    if( 'style3' == $field_page_break_wizard_theme ){
                        $arf_form .= '<div id="arf_wizard_table" class="arf_wizard arf_wizard_style3 arf_wizard_bottom">';
                    }else if( 'style2' == $field_page_break_wizard_theme ){
                        $arf_form .= '<div id="arf_wizard_table" class="arf_wizard arf_wizard_style2 arf_wizard_bottom">';
                    }else{
                        $arf_form .= '<div id="arf_wizard_table" class="arf_wizard arf_wizard_style1 arf_wizard_bottom">';
                    }
                    $arf_form .= '<div class="arf_wizard_upper_tab">';
                
                    $cntr_break = 0;
                    foreach ($page_break_fields as $field) {
                        $field_type = $field['type'];
                        $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                        if ($field_type == "break") {
                            $first_page_break_field_val = $field; //first page break field
                            $display_page_break = "";

                            $field_first_page_label = $field['first_page_label'];
                            $field_second_page_label = $field['second_page_label'];
                            $field_pre_page_title = $field['pre_page_title'];
                            if ($cntr_break == 0 && $i == 1) {
                                $field_page_break_type = $field['page_break_type'];
                            }
                            if ($field_page_break_type == "wizard") {
                                if ($cntr_break == 0 && $i == 1) {

                                    $arf_form .= '<div style="width:' . $td_width . ';" id="page_nav_' . $i . '" class="page_break_nav page_nav_selected">' . $field_first_page_label . '</div>';
                                    $i++;
                                    $arf_form .= '<div style="width:' . $td_width . '; ' . $display_page_break . '" id="page_nav_' . $i . '" class="page_break_nav">' . $field_second_page_label . '</div>';
                                    $cntr_break++;
                                } else {
                                    $arf_form .= '<div style="width:' . $td_width . '; ' . $display_page_break . '" id="page_nav_' . $i . '" class="page_break_nav">' . $field_second_page_label . '</div>';
                                }
                                $i++;
                                $enterrowdata = "<br>";
                            }
                        }
                        $field_name = 'item_meta[' . $field['id'] . ']';
                    }

                    if ($field_page_break_type == 'wizard') {
                        $arf_form .= '</div>';
                    }

                    $cntr_break = 0;
                    $i = 1;
                    if ($field_page_break_type == 'wizard') {
                        if('style2' == $field_page_break_wizard_theme || 'style3' == $field_page_break_wizard_theme){
                            $arf_form .= '<div class="arf_wizard_lower_tab" style="display: none;">';
                        }else{
                            $arf_form .= '<div class="arf_wizard_lower_tab">';
                        }
                    }

                    foreach ($values['fields'] as $field) {
                        $field_type = $field['type'];
                        $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                        if ($field_type == "break") {
                            $field_first_page_label = $field['first_page_label'];
                            $field_second_page_label = $field['second_page_label'];
                            $field_pre_page_title = $field['pre_page_title'];
                            if ($cntr_break == 0 && $i == 1) {
                                $field_page_break_type = $field['page_break_type'];
                            }
                            if ($field_page_break_type == "wizard") {
                                $display = '';

                                if ($cntr_break == 0 && $i == 1) {

                                    $arf_form .= '<div style="width:' . $td_width . '; padding:0;" id="page_nav_arrow_' . $i . '" class="page_break_nav page_nav_selected"><div class="arf_current_tab_arrow"></div></div>';
                                    $i++;
                                    $arf_form .= '<div style="width:' . $td_width . ';padding:0;' . $display . '" id="page_nav_arrow_' . $i . '" class="page_break_nav"></div>';
                                    $cntr_break++;
                                } else {
                                    $arf_form .= '<div style="width:' . $td_width . ';padding:0;' . $display . '" id="page_nav_arrow_' . $i . '" class="page_break_nav"></div>';
                                }
                                $i++;
                                
                            }
                        }
                        $field_name = 'item_meta[' . $field['id'] . ']';
                    }

                
                    $arf_form .= '</div>';
                    if('style2' == $field_page_break_wizard_theme){
                        $arf_form .= '</div><div class="arf_wizard_clear" style="clear:both; height:30px;"></div>' . $enterrowdata;
                    }else{
                        $arf_form .= '</div>' . $enterrowdata;
                    }
                }

            }
        }

	if($field_page_break_type_possition == 'bottom') {
               if($field_page_break_addtimer == 1 && $arfshowunits != '' && (($arfunithrs != '' && $arfunithrs != 0 ) || ($arfunitmin != '' && $arfunitmin != 0 ) || ($arfunitsec != '' && $arfunitsec != 0 ))){

                $arf_form .= '<div id="pagebreaktime" data-direction="'.(is_rtl() ? 'rtl' : 'ltr').'" class="arf_pagebreaktime" > 
                    <div class="arf_pagebreak_timer arftimer_bottom '.$arf_pagebreak_style_class.' '.$arf_totalshowunit_cls.'" id="arf_date_time_countdown-'.$form->id.'-'.$arf_data_uniq_id.'" data-pagebreak-style="'.$arfpagebreakstyle.'" data-enabled-units="'.$arfshowunits.'"  data-timer-hours="'.$arfunithrs.'" data-timer-minutes="'.$arfunitmin.'" data-timer-seconds="'.$arfunitsec.'" start-timer-pgno="'.$arfstarttimepgno.'" end-timer-pgno="'.$arfendtimepgno.'" data-timer="'.$arf_current_date_time.'" data-timer-bgcolor="'.$arftimerbgcolor.'" data-timer-forgroundcolor="'.$arftimerforgroundcolor.'" data-text-hour="'.$hours.'" data-text-min="'.$minutes.'" data-text-sec="'.$seconds.'" style="width: 25%;"></div>
                    </div> <br>';
                $arf_count_total_page = $arf_total_page_break + 1;
                if($arfstarttimepgno <= $arf_count_total_page ){
                    if(!($type == 'sticky' || $type == 'fly') ){
                        $arf_form_all_footer_js .= 'arf_pagebreak_startTimer("arf_date_time_countdown-'.$form->id.'-'.$arf_data_uniq_id.'");';
                    }       
                }
            }
        }

        $arfoptions = get_option("arf_options");

        $mybrand = isset($arfoptions->brand) ? $arfoptions->brand : '' ;

        $doliact = 0;
        global $valid_wp_version;
        global $arfmsgtounlicop;
        $doliact = $arformcontroller->$valid_wp_version();

	if($doliact == 0)
	{
	  $mybrand = 0;
	}
	
        $my_aff_code = "";

        if (!isset($arfoptions->affiliate_code) || $arfoptions->affiliate_code == "")
            $my_aff_code = "reputeinfosystems";
        else
            $my_aff_code = $arfoptions->affiliate_code;

        if ($mybrand == 0) {

            $arf_form .='<div id="brand-div" class="brand-div ' . $_SESSION['label_position'] . '_container" style="margin-top:30px; font-size:12px !important; color: #444444 !important; display:block !important; visibility: visible !important;">' . addslashes(esc_html__('Powered by', 'ARForms')) . '&#32;';
            if(is_ssl()) {
                $arf_form .='<a href="https://codecanyon.net/item/arforms-exclusive-wordpress-form-builder-plugin/6023165?ref=' . $my_aff_code . '" target="_blank" style="color:#0066cc !important; font-size:12px !important; display:inline !important; visibility:visible !important;">ARForms</a>';
            } else {
                 $arf_form .='<a href="http://codecanyon.net/item/arforms-exclusive-wordpress-form-builder-plugin/6023165?ref=' . $my_aff_code . '" target="_blank" style="color:#FF0000 !important; font-size:12px !important; display:inline !important; visibility:visible !important;">ARForms</a>';
            }
            $setlicval = 0;

            $setlicval = 0;
            global $valid_wp_version;
            global $arfmsgtounlicop;
            $setlicval = $arformcontroller->$valid_wp_version();

            if ($setlicval == 0) {
                $arf_form .='<span style="color:#FF0000 !important; font-size:12px !important; display:inline !important; visibility: visible !important;">' . addslashes(__('&nbsp;&nbsp;' . $arfmsgtounlicop, 'ARForms')) . '</span>';
            }
            $arf_form .='</div>';
        }


        $arf_form .='</div><!-- arf_fieldset -->';
        $arf_form = apply_filters('arf_additional_form_content_outside',$arf_form,$form,$arf_data_uniq_id,$arfbrowser_name,$browser_info);
        $arf_form .='</div><!-- allfields -->';
        /* get all fields end */


        $arf_form .='</form>';
        
        /* actual from end */
        $form = apply_filters('arfafterdisplayform', $form);
        
        $arf_logic = isset($form->options['arf_conditional_logic_rules']) ? $form->options['arf_conditional_logic_rules'] : array() ;
        $arf_submit_logic = isset($form->options['submit_conditional_logic']) ? $form->options['submit_conditional_logic'] : array();

        $arf_cl = "";
        $arf_pages_field_array = array();
        if (isset($arf_logic) && is_array($arf_logic) && !empty($arf_logic)) {

            $arf_conditional_logic_loaded[$form->id] = 1;
            $page_no = 0;
            $arf_field_array = array();
            /* arf_dev_flag query */

            $res = wp_cache_get('arf_form_fields_'.$form->id);
            if( false == $res ){
                $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->fields . " WHERE form_id = %d ORDER BY id", $form->id));
                wp_cache_set('arf_form_fields_'.$form->id, $res);
            }
            
            $temp_field_order2 = json_decode($form->options['arf_field_order'],true);
            asort( $temp_field_order2 );

            $temp_inner_field_order = !empty( $form->options['arf_inner_field_order'] ) ? json_decode( $form->options['arf_inner_field_order'], true ) : array();
            asort( $temp_inner_field_order );

            $res_new = array();
            $res_sec_fields = array();
            foreach( $temp_field_order2 as $tfid => $tfod ){
                foreach( $res as $temp_fields ){
                    if( $tfid == $temp_fields->id ){
                        $res_new[] = $temp_fields;
                    }
                    if( $temp_fields->type == 'section' || $temp_fields->type == 'divider' ){
                        $res_sec_fields[] = $temp_fields->id;
                    }
                }
            }

            $res_sec_fields = array_unique( $res_sec_fields );

            $res_inner = array();

            if( !empty( $res_sec_fields ) ){
                foreach( $res_sec_fields as $infid => $infdata ){
                    if( !empty( $temp_inner_field_order[$infdata] ) ){
                        foreach( $temp_inner_field_order[$infdata] as $inner_data ){
                            $exploded_data = explode( '|', $inner_data );

                            if( !empty( $exploded_data[0] ) ){
                                foreach( $res as $temp_fields ){
                                    if( $exploded_data[0] == $temp_fields->id ){
                                        $res_inner[] = $temp_fields;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            $res = $res_new;

            foreach ($res as $data) {
                if ($data->type == 'break') {
                    $page_no++;
                }
                $fid = $data->id;
        		if( is_array($data->field_options) ){    
        		    $field_options =  $data->field_options;
        		} else {
        		    $field_options = json_decode( $data->field_options, true );
        	            if( json_last_error() != JSON_ERROR_NONE ){
        	                $field_options = maybe_unserialize($data->field_options);
        	            }
        		}
                $default_value_temp = apply_filters('arf_replace_default_value_shortcode',$field_options['default_value'],$field_options,$form);
        		if( isset($field_options['type']) && $field_options['type'] == 'arfslider' ){
                    if( $field_options['arf_range_selector'] == 1  ){
                        $default_value_temp = array((double)$field_options['arf_range_minnum'],(double)$field_options['arf_range_maxnum']);
                    } else {
                        $default_value_temp = (double)$field_options['slider_value'];
                    }
                }
    		    $arf_field_array[$fid] = array("page_no" => $page_no, "field_key" => $data->field_key, "default_value" => $default_value_temp);
            }

            foreach ($res_inner as $data) {
                if ($data->type == 'break') {
                    $page_no++;
                }
                
                $fid = $data->id;
                if( is_array($data->field_options) ){    
                    $field_options =  $data->field_options;
                } else {
                    $field_options = json_decode( $data->field_options, true );
                        if( json_last_error() != JSON_ERROR_NONE ){
                            $field_options = maybe_unserialize($data->field_options);
                        }
                }
                $default_value_temp = apply_filters('arf_replace_default_value_shortcode',$field_options['default_value'],$field_options,$form);
                if( isset($field_options['type']) && $field_options['type'] == 'arfslider' ){
                    if( $field_options['arf_range_selector'] == 1  ){
                        $default_value_temp = array((double)$field_options['arf_range_minnum'],(double)$field_options['arf_range_maxnum']);
                    } else {
                        $default_value_temp = (double)$field_options['slider_value'];
                    }
                }
                $arf_field_array[$fid] = array("page_no" => $page_no, "field_key" => $data->field_key, "default_value" => $default_value_temp);
            }

        $form_cols=$res;
        $field_order = json_decode($form->options['arf_field_order'],true);
        $new_form_cols = array();

        asort($field_order);
        $hidden_fields = array();
        $hidden_field_ids = array();
        $parent_field_ids = array();
        foreach ($field_order as $field_id => $order) {
            if(is_int($field_id)){
                foreach ($form_cols as $field) {
                    if ($field_id == $field->id) {
                        if( 'section' == $field->type || 'arf_repeater' == $field->type ){
                            $parent_field_ids[] = $field->id;
                        }
                        $new_form_cols[] = $field;
                    } 
                }
            }
        }

        if( isset( $parent_field_ids ) && !empty( $parent_field_ids ) ){
            $inner_field_order = json_decode( $form->options['arf_inner_field_order'], true );
            foreach( $parent_field_ids as $outer_field_id ){
                if( isset( $inner_field_order[$outer_field_id] ) ){
                    $in_ford = $inner_field_order[$outer_field_id];
                    foreach( $in_ford as $iford ){
                        $exploded_data = explode('|', $iford);

                        if( preg_match('/^(\d)+$/', $exploded_data[0] ) ){
                            foreach( $form_cols as $field ){
                                if( $field->id == $exploded_data[0] ){
                                    $inner_new_form_cols[] = $field;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $pageno=0;
        $repeater_id=0;
        $form_cols = $new_form_cols;
	
        foreach ($form_cols as $data1) {
            if ($data1->type == 'break') {
                $pageno++;
            }
           
            $heading_id=0;
            if ($data1->type == 'divider' || $data1->type == 'section') {
                $heading_id=$data1->id;
            }

            if( is_array($data1->field_options) ){    
                $field_options1 =  $data1->field_options;
            } else {
                $field_options1 = json_decode( $data1->field_options, true );
                    if( json_last_error() != JSON_ERROR_NONE ){
                        $field_options1 = maybe_unserialize($data1->field_options);
                    }
            }
            $default_value_temp1 = apply_filters('arf_replace_default_value_shortcode',$field_options1['default_value'],$field_options1,$form);

            if( isset($field_options1['type']) && $field_options1['type'] == 'arfslider' ){
                if( $field_options1['arf_range_selector'] == 1  ){
                    $default_value_temp1 = array((double)$field_options1['arf_range_minnum'],(double)$field_options1['arf_range_maxnum']);
                } else {
                    $default_value_temp1 = (double)$field_options1['slider_value'];
                }
            }

            if( !isset( $field_options1['type'] ) ){
                $field_options1['type'] = '';
            }

            $arf_pages_field_array[]=array("field_id"=>$data1->id,"field_key" => $data1->field_key,"default_value" => $default_value_temp1,"field_type"=>$field_options1['type'],"page_no" => $pageno,"heading_id"=>$heading_id, 'repeater_id' => $repeater_id);
        }

        if( isset( $inner_new_form_cols ) ){
            foreach( $inner_new_form_cols as $in_data1 ){
                $in_field_options1 = arf_json_decode( $in_data1->field_options, true );

                $in_default_value_temp1 = apply_filters( 'arf_replace_default_value_shortcode', $in_field_options1['default_value'], $in_field_options1, $form );
                if( isset( $in_field_options1['type'] ) && 'arfslider' == $in_field_options1['type'] ){
                    if( $in_field_options1['arf_range_selector'] == 1 ){
                        $in_default_value_temp1 = array( (double) $in_field_options1['arf_range_minnum'], (double) $in_field_options1['arf_range_maxnum'] );
                    } else {
                        $in_default_value_temp1 = (double) $in_field_options1['slider_value'];
                    }
                }
                $temp_array = array('field_id'=>$in_data1->id, 'field_key' => $in_data1->field_key, 'default_value' => $in_default_value_temp1, 'field_type' => $in_field_options1['type'], 'page_no' => 0 );
                if( isset( $in_field_options1['parent_field_type'] ) && 'section' == $in_field_options1['parent_field_type'] ){
                    $temp_array['heading_id'] = $in_field_options1['parent_field'];
                    $temp_array['repeater_id'] = 0;
                } else if( isset( $in_field_options1['parent_field_type'] ) && 'arf_repeater' == $in_field_options1['parent_field_type'] ){
                    $temp_array['heading_id'] = 0;
                    $temp_array['repeater_id'] = $in_field_options1['parent_field'];
                }
                $arf_pages_field_array[] = $temp_array;
            }
        }

            $arf_cl = "";
            $arf_cl_data = new stdClass();
            $arf_cl_fields = array();
            $arf_cl_dependents = array();
            $arf_cl_defaults = array();


                
                foreach ($arf_logic as $key => $rule) {
                    $results = $rule['result'];
                    $logicType = (isset($rule['logical_operator']) && $rule['logical_operator'] == 'and') ? 'all' : 'any';

                    foreach ($results as $rK => $result) {
                        $conditions = $rule['condition'];
                        $arf_cl_condition = array();
                        $arf_submit_cl_condition = array();
                        
                        foreach ($conditions as $cK => $condition) {
                            $field_key_val = isset($arf_field_array[$condition['field_id']]['field_key']) ? $arf_field_array[$condition['field_id']]['field_key'] : '';
                            $arf_cl_condition[] = array(
                                'fieldId' => $condition['field_id'],
                                'operator' => $condition['operator'],
                                'value' => $condition['value'],
                                'fieldType' => $condition['field_type'],
                                'fieldKey' => $field_key_val
                                );
                        }
                        $field_defalt_val = isset($arf_field_array[$result['field_id']]['default_value']) ? $arf_field_array[$result['field_id']]['default_value'] : '';
                        $result_field_opt = isset($arf_field_array[$result['field_id']]) ? $arf_field_array[$result['field_id']] : '';
                        $field_defalt_val = apply_filters('arf_replace_default_value_shortcode',$field_defalt_val,$result_field_opt,$form);

                        
                        if( $result['field_id'] == '' ) { continue; }
                        if( !isset($arf_cl_fields[$result['field_id']]) ){
                            $arf_cl_fields[$result['field_id']] = array();
                        }

                        $arf_cl_fields[$result['field_id']]['fields'][] = array(
                            'actionType' => $result['action'],
                            'logicType' => $logicType,
                            'field_key' => isset($arf_field_array[$result['field_id']]['field_key']) ? $arf_field_array[$result['field_id']]['field_key'] : '',
                            'value' => isset($result['value']) ? $result['value'] : '',
                            'default_value' => $field_defalt_val,
                            'field_type' => $result['field_type'],
                            'page_no' => isset($arf_field_array[$result['field_id']]['page_no']) ? $arf_field_array[$result['field_id']]['page_no'] : '',
                            'rules' => $arf_cl_condition
                        );

                        /* arf_dev_flag : Dependent fields logic need to change while having section and page break in form */
                        $arf_cl_dependents[$result['field_id']][] = (int) $result['field_id'];
                    }
                    
                    if( isset($arf_submit_logic) && is_array($arf_submit_logic) && !empty($arf_submit_logic) && $arf_submit_logic['enable'] == 1 ){

                        foreach( $arf_submit_logic['rules'] as $arf_submit_rules ){
                            $field_key_val = isset($arf_field_array[$arf_submit_rules['field_id']]['field_key']) ? $arf_field_array[$arf_submit_rules['field_id']]['field_key'] : '';
                            $arf_submit_cl_condition[] = array(
                                'fieldId' => $arf_submit_rules['field_id'],
                                'operator' => $arf_submit_rules['operator'],
                                'value' => $arf_submit_rules['value'],
                                'fieldType' => $arf_submit_rules['field_type'],
                                'fieldKey' => $field_key_val
                                );
                        }
                        $arf_cl_fields['submit'] = array();
                        $submit_action = ($arf_submit_logic['display'] == 'Enable' || $arf_submit_logic['display'] == 'show')? 'show' : 'hide';
                        $arf_cl_fields['submit']['fields'][] = array(
                            'actionType' => $submit_action,
                            'logicType' => $arf_submit_logic['if_cond'],
                            'field_key' => '',
                            'value' => '',
                            'default_value' => '',
                            'field_type' => 'submit',
                            'page_no' => isset($arf_field_array[$result['field_id']]['page_no']) ? $arf_field_array[$result['field_id']]['page_no'] : '',
                            'rules' => $arf_submit_cl_condition
                            );
                    }
                }
            
            $arf_cl_data->logic = $arf_cl_fields;
            $arf_cl_data->dependents = $arf_cl_dependents;
            $arf_cl_data->defaults = $arf_cl_defaults;
            


            if(isset($form->form_css['arfsuccessmsgposition']) && ($form->form_css['arfsuccessmsgposition'] == 'bottom')){
                $arf_form .= '<div id="arf_message_error" class="frm_error_style" style="display:none;"><div class="msg-detail"><div class="msg-description-success">' . addslashes(esc_html__('Form Submission is restricted', 'ARForms')) . '</div></div></div>';
            }

            $arf_cl .= "<script type='text/javascript' data-cfasync='false'>";
            $arf_cl .= "if(!window['arf_conditional_logic']){window['arf_conditional_logic'] = new Array();}";
            
            $arf_cl .= "window['arf_conditional_logic'][{$arf_data_uniq_id}] = " . json_encode($arf_cl_data,JSON_UNESCAPED_UNICODE) . ";" ;

            $arf_cl .= "if(!window['arf_pages_fields']){window['arf_pages_fields'] = new Array();}";

            $arf_cl .= "window['arf_pages_fields'][{$arf_data_uniq_id}] = " . json_encode($arf_pages_field_array,JSON_UNESCAPED_UNICODE) . ";" ;
            $arf_cl .= "</script>";
        }

          $arf_form .= $arf_cl;

        /* action after render form 
         * arf_dev_flag => if concept is for display content than change it to filter 
         * 
         *  */
        do_action('arf_afterdisplay_form', $form);
        do_action('arf_afterdisplay_form' . $form->id, $form);
        $arf_form .= '</div><!--arf_form_outer_wrapper -->';
        /* actual output end */
        if ($type != '') {
            $arf_form .= '</div>';
            $arf_form .= '</div>';
            if ($type == 'sticky') {
                $arf_form .= '</div>';
                if ($position == 'top') {
                    $arf_form .= '<div style="clear:both;"></div>';
                    $arf_form .= '<div class="arform_bottom_fixed_block_top arf_fly_sticky_btn arform_modal_stickytop_' . $form->id . '" onclick="open_modal_box_sitcky_top(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer;"><span href="#" data-toggle="arfmodal" title="' . $form_name . '">' . $desc . '</span></div>';
                }
                $arf_form .= '</div>';
            } elseif ($type == 'fly') {
                $arf_form .= '</div>';
                $arf_form .= '</div>';
            }

            if ($type == 'sticky' && $position == 'left') {

                $arf_form_all_footer_js .='var winodwHeight = jQuery(window).height();
                var modal_height_left = "' . $modal_height . '";
                

                jQuery("#arf-popup-form-' . $form->id . ' .arform_bottom_fixed_block_left").parents(".arform_bottom_fixed_main_block_left").find(".arform_bottom_fixed_form_block_left_main").css("margin-top", "-35px");
                jQuery("#arf-popup-form-' . $form->id . '.arform_bottom_fixed_main_block_left").css("display", "inline-block");
                jQuery(".arf_popup_' . $arf_popup_data_uniq_id . '").find(".arform_modal_stickybottom_' . $form->id . '").css("transform-origin", "left top");';
            }
            if ($type == 'sticky' && $position == 'right') {

                $arf_form_all_footer_js .='  var winodwHeight = jQuery(window).height();
                var modal_height_right = "' . $modal_height . '";
                jQuery("#arf-popup-form-' . $form->id . ' .arform_bottom_fixed_block_right").parents(".arform_bottom_fixed_main_block_right").find(".arform_bottom_fixed_form_block_right_main").css("margin-top", "-35px");
                jQuery("#arf-popup-form-' . $form->id . '.arform_bottom_fixed_main_block_right").css("display", "inline-block");
                jQuery(".arf_popup_' . $arf_popup_data_uniq_id . '").find(".arform_modal_stickybottom_' . $form->id . '").css("transform-origin", "right top");';
            }
        }

        $arf_form .= '<div class="brand-div"></div><div class=""><input type="hidden" data-jqvalidate="false" name="form_id" data-id="form_id" value="' . $form->id . '" /><input type="hidden" data-jqvalidate="false" name="arfmainformurl" data-id="arfmainformurl" value="' . ARFURL . '" /></div>';

        
        
        if($is_recaptcha==1){
            $arf_form .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_key' value='{$arfsettings->pubkey}' />";
            $arf_form .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_theme' value='{$arfsettings->re_theme}' />";
            $arf_form .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_lang' value='{$arfsettings->re_lang}' />";    
        }
        
               
        
        if( $home_preview == true ){
            $wp_upload_dir = wp_upload_dir();
            $dest_css_url = $wp_upload_dir['baseurl'] . '/arforms/maincss/';
            if ($inputStyle == 'material') {
                if (is_ssl()) {
                  $fid_material = str_replace("http://", "https://", $dest_css_url . '/maincss_materialize_' . $form->id . '.css');
                } else {
                  $fid_material = $dest_css_url . '/maincss_materialize_' . $form->id . '.css';
                }
                $arf_form .= "<link rel='stylesheet' type='text/css' href=".$fid_material." />";
            } else if( $inputStyle == 'material_outlined' ){
                if (is_ssl()) {
                  $fid_material_outlined = str_replace("http://", "https://", $dest_css_url . '/maincss_materialize_outlined_' . $form->id . '.css');
                } else {
                  $fid_material_outlined = $dest_css_url . '/maincss_materialize_outlined_' . $form->id . '.css';
                }
                $arf_form .= "<link rel='stylesheet' type='text/css' href=".$fid_material_outlined." />";
            } else {
                if (is_ssl()) {
                  $fid = str_replace("http://", "https://", $dest_css_url . '/maincss_' . $form->id . '.css');
                } else {
                  $fid = $dest_css_url . '/maincss_' . $form->id . '.css';
                }
                $arf_form .= "<link rel='stylesheet' type='text/css' href=".$fid." />";
            }
        }
        

        /** if tooltip loaded than append its js */
        if ( isset($form->options['tooltip_loaded']) && $form->options['tooltip_loaded']) {
            $arf_tootip_width = (isset($form->form_css['arf_tooltip_width']) && $form->form_css['arf_tooltip_width']!='') ? $form->form_css['arf_tooltip_width'] : 'auto';
            $arf_tooltip_position = (isset($form->form_css['arftooltipposition']) && $form->form_css['arftooltipposition']!='') ? $form->form_css['arftooltipposition'] : 'top';

            $show_slider_tooltip = isset( $field_options['show_slider_tooltip'] ) ? $field_options['show_slider_tooltip'] : 0;

            if ($inputStyle == 'material' && $show_slider_tooltip == 1 && $arf_tooltip_position == 'top') {
                $arf_tooltip_position = 'bottom';
            }
            $arf_mobile_tooltip = ($arf_tooltip_position == 'bottom') ? 'bottom' : 'top';
                $arf_form_all_footer_js .= '
                var sreenwidth = jQuery(window).width();
                if (typeof jQuery().tipso == "function") {
                    jQuery(".ar_main_div_' . $form->id . '").find(".arfhelptip").each(function () {
                        jQuery(this).tipso("destroy");
                        var title = jQuery(this).attr("data-title");
                        jQuery(this).tipso({
                            position: "'.$arf_tooltip_position  . '",
                            width: "' . $arf_tootip_width . '",
                            useTitle: false,
                            content: title,
                            background: "' . str_replace('##', '#', $form->form_css['arf_tooltip_bg_color']) . '",
                            color:"' . str_replace('##', '#',$form->form_css['arf_tooltip_font_color']) . '",
                            tooltipHover: true
                        });
                    });
                }
                if (typeof jQuery().tipso == "function" && sreenwidth < 500 ) {
                    jQuery(".ar_main_div_' . $form->id . '").find(".arfhelptip").each(function () {
                        jQuery(this).tipso("destroy");
                        var title = jQuery(this).attr("data-title");
                        jQuery(this).tipso({
                            position: "'. $arf_mobile_tooltip .'",
                            width: "' . $arf_tootip_width . '",
                            useTitle: false,
                            content: title,
                            background: "' . str_replace('##', '#', $form->form_css['arf_tooltip_bg_color']) . '",
                            color:"' . str_replace('##', '#',$form->form_css['arf_tooltip_font_color']) . '",
                            tooltipHover: true
                        });
                    });
                }';
            
            if ($inputStyle == 'material') {
                $arf_form_all_footer_js .= '
                var sreenwidth = jQuery(window).width();
                if (typeof jQuery().tipso == "function") {
                    jQuery(".ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus input,.ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus textarea").on( "focus", function(e){
                        jQuery(this).parent().parent().each(function () {
                            var arf_data_title = jQuery(this).attr("data-title");
                            if(jQuery(this).find("input").hasClass("arf_phone_utils")){
                                arf_data_title = jQuery(this).parent().attr("data-title");
                            }
                            if(arf_data_title!=null && arf_data_title!=undefined)
                            {
                                jQuery(this).tipso("hide");
                                jQuery(this).tipso("destroy");
                                var arftooltip = jQuery(this).tipso({
                                    position: "' . $arf_tooltip_position . '",
                                    width: "' . $arf_tootip_width . '",
                                    useTitle: false,
                                    content: arf_data_title,
                                    background: "' . str_replace('##', '#', $form->form_css['arf_tooltip_bg_color']) . '",
                                    color:"' . str_replace('##', '#',$form->form_css['arf_tooltip_font_color']) . '",
                                    tooltipHover: true,
                                });
                                jQuery(this).tipso("show");
                                arftooltip.off("mouseover.tipso");
                                arftooltip.off("mouseout.tipso");
                            }
                            
                        });
                    });

                    jQuery(document).on("focusout",".ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus input,.ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus textarea", function(e){
                        jQuery(this).parent().parent().each(function () {
                            var arf_data_title = jQuery(this).attr("data-title");
                            if(jQuery(this).find("input").hasClass("arf_phone_utils")){
                                arf_data_title = jQuery(this).parent().attr("data-title");
                            }
                            if(arf_data_title!=null && arf_data_title!=undefined)
                            {
                                jQuery(this).tipso("hide");
                                jQuery(this).tipso("destroy");
                            }
                        });
                        
                    });
                    
                }
                if (typeof jQuery().tipso == "function" && sreenwidth < 500 ) {
                    jQuery(".ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus input,.ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus textarea").on( "focus", function(e){
                        jQuery(this).parent().parent().each(function () {
                            var arf_data_title = jQuery(this).attr("data-title");
                            if(jQuery(this).find("input").hasClass("arf_phone_utils")){
                                arf_data_title = jQuery(this).parent().attr("data-title");
                            }
                            if(arf_data_title!=null && arf_data_title!=undefined)
                            {
                                jQuery(this).tipso("hide");
                                jQuery(this).tipso("destroy");
                                var arftooltip = jQuery(this).tipso({
                                    position: "'. $arf_mobile_tooltip .'",
                                    width: "' . $arf_tootip_width . '",
                                    useTitle: false,
                                    content: arf_data_title,
                                    background: "' . str_replace('##', '#', $form->form_css['arf_tooltip_bg_color']) . '",
                                    color:"' . str_replace('##', '#',$form->form_css['arf_tooltip_font_color']) . '",
                                    tooltipHover: true,
                                });
                                jQuery(this).tipso("show");
                                arftooltip.off("mouseover.tipso");
                                arftooltip.off("mouseout.tipso");
                            }
                            
                        });
                    });

                    jQuery(document).on("focusout",".ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus input,.ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus textarea", function(e){
                        jQuery(this).parent().parent().each(function () {
                            var arf_data_title = jQuery(this).attr("data-title");
                            if(jQuery(this).find("input").hasClass("arf_phone_utils")){
                                arf_data_title = jQuery(this).parent().attr("data-title");
                            }
                            if(arf_data_title!=null && arf_data_title!=undefined)
                            {
                                jQuery(this).tipso("hide");
                                jQuery(this).tipso("destroy");
                            }
                        });
                    });
                }';
            }
        }

    /* if checkbox or radio field loaded start */

    if (in_array('radio', $loaded_field) || in_array('checkbox', $loaded_field)) {

        $form_css_submit = $form->form_css;
        $checkradio_property = "";
        if ($form_css_submit['arfcheckradiostyle'] != "") {

            if ($form_css_submit['arfcheckradiostyle'] != "none") {
                if ($form_css_submit['arfcheckradiocolor'] != "default" && $form_css_submit['arfcheckradiocolor'] != "") {
                    if ($form_css_submit['arfcheckradiostyle'] == "custom" || $form_css_submit['arfcheckradiostyle'] == "futurico" || $form_css_submit['arfcheckradiostyle'] == "polaris") {
                        $checkradio_property = $form_css_submit['arfcheckradiostyle'];
                    } else {
                        $checkradio_property = $form_css_submit['arfcheckradiostyle'] . "-" . $form_css_submit['arfcheckradiocolor'];
                    }
                } else {
                    $checkradio_property = $form_css_submit['arfcheckradiostyle'];
                }
            } else {
                $checkradio_property = "";
            }
        }

        $checked_checkbox_property = '';
        if (isset($form_css_submit['arf_checked_checkbox_icon']) && $form_css_submit['arf_checked_checkbox_icon'] != "") {
            $checked_checkbox_property = ' arfa ' . $form_css_submit['arf_checked_checkbox_icon'];
        } else {
            $checked_checkbox_property = '';
        }
        $checked_radio_property = '';
        if (isset($form_css_submit['arf_checked_radio_icon']) && $form_css_submit['arf_checked_radio_icon'] != "") {
            $checked_radio_property = ' arfa ' . $form_css_submit['arf_checked_radio_icon'];
        } else {
            $checked_radio_property = '';
        }
            
      }
      /* if checkbox or radio field loaded end */

      /* if smiley field loaded start */

      if (in_array('arf_smiley', $loaded_field)) {


        $arf_form_all_footer_js .='
        jQuery(".arf_smiley_btn").each(function () {
            var title = jQuery(this).attr("data-title");
            if (title !== undefined) {
                jQuery(this).arf_popover({
                    html: true,
                    trigger: "hover",
                    placement: "top",
                    content: title,
                    title: "",
                    animation: false
                });
            }
        });';

        /** arf_dev_flag internal css need to remove */
    }

    /* if smiley field loaded end */

    if (in_array('like', $loaded_field)) {
        $arf_form_all_footer_js .= 'jQuery(".arf_like_btn, .arf_dislike_btn").each(function () {
            var title = jQuery(this).attr("data-title");
            if (title !== undefined) {
                jQuery(this).arf_popover({
                    html: true,
                    trigger: "hover",
                    placement: "top",
                    content: title,
                    title: "",
                    animation: false
                });
            }
        });';
    }

    if (in_array('colorpicker', $loaded_field)) {

        $arf_form_all_footer_js .= "__JSPICKER_NEWROW = [];
        jQuery('.jscolor').each(function (e) {
            var this_val = jQuery(this);
            var object = {};
            var el = this_val[0];
            var jscolorAttr = el.getAttribute('data-jscolor');
            var object = JSON.parse( jscolorAttr );
            __JSPICKER_NEWROW[e] = new jscolor(el, object);
            if (typeof __JSPICKER === 'undefined') {
                __JSPICKER = __JSPICKER_NEWROW;
            } else {
                __JSPICKER = __JSPICKER.concat(__JSPICKER_NEWROW);
            }
        });";
    }

    $arf_file_path = $arformhelper->manage_uploaded_file_path( $form->id );

    /* arf_dev_flag move it to script localization `need to discuss` as ARMember */

    $arf_form_all_footer_js .= 'jQuery("#arffrm_' . $form->id . '_container").find("form").find(".arfformfield").each(function () {
        var data_view = jQuery(this).attr("data-view");
        if (data_view == "arf_disable") {
            if("arf_wysiwyg" == jQuery(this).attr("data-field-type")){
                setTimeout(function() {
                    if(jQuery("button.trumbowyg-fullscreen-button").hasClass("trumbowyg-not-disable")){
                        jQuery("button.trumbowyg-fullscreen-button").removeClass("trumbowyg-not-disable");
                    }
                }, 500);
            }
            var data_type = jQuery(this).attr("data-type");
            arf_field_disable(jQuery(this), data_type);
        }
    });';


    unset($page_break_hidden_array[$form->id]);
    return $arf_form;
}

}

?>