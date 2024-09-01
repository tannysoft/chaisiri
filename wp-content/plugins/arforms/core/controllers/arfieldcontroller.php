<?php

class arfieldcontroller {

    function __construct() {

        add_filter('arfdisplayfieldtype', array($this, 'show_normal_field'), 10, 2);

        add_filter('arfdisplayfieldhtml', array($this, 'arfdisplayfieldhtml'), 10, 2);

        add_action('arfdisplayfieldtype1', array($this, 'show_other'), 10, 5);

        add_filter('arffieldtype', array($this, 'change_type'), 15, 2);

        add_filter('arfdisplaysavedfieldvalue', array($this, 'use_field_key_value'), 10, 3);

        add_action('arfdisplayaddedfields', array($this, 'show'));

        add_filter('arfdisplayfieldoptions', array($this, 'display_field_options'));

        add_filter('arfdisplayfieldoptions', array($this, 'display_basic_field_options'));
        
        add_action('arffieldinputhtml', array($this, 'input_html'));

        add_action('arffieldinputhtml', array($this, 'input_fieldhtml'));

        add_filter('arfaddfieldclasses', array($this, 'add_field_class'), 20, 2);
        
        add_action('arfaddradioimagevalues', array($this, 'add_radio_value_opt_label'));

        add_action('arfdatepickerjs', array($this, 'arfdatepickerjs'), 10, 2);
        
        add_action('wp_ajax_arfmakereqfield', array($this, 'mark_required'));

        add_action('wp_ajax_arfdeleteformfield', array($this, 'destroy'));

        add_filter('arffieldvaluesaved', array($this, 'check_value'), 50, 3);

        add_filter('arffieldlabelseen', array($this, 'check_label'), 10, 3);

        add_action('wp_ajax_arf_is_prevalidateform_outside', array($this, 'arf_prevalidateform_outside'));

        add_action('wp_ajax_nopriv_arf_is_prevalidateform_outside', array($this, 'arf_prevalidateform_outside'));

        add_action('wp_ajax_arf_is_resetformoutside', array($this, 'arf_resetformoutside'));

        add_action('wp_ajax_nopriv_arf_is_resetformoutside', array($this, 'arf_resetformoutside'));

        add_action('wp_ajax_arf_add_new_preset', array($this, 'arf_add_new_preset'));

        add_action('wp_ajax_arf_save_new_preset', array($this, 'arf_save_new_preset'));

        add_action('wp_ajax_upload_radio_label_img', array($this, 'arf_upload_radio_label_img'));

        add_action('wp_ajax_arf_save_new_preset_field', array($this, 'arf_save_new_preset_field_function'));

        add_action('wp_ajax_arf_save_new_dynamic_option_field', array($this, 'arf_save_new_dynamic_option_field'));

        add_action('wp_ajax_arf_get_field_data_dynamic', array( $this,'arf_get_field_data_dynamic'));
    }

    function mark_required() {


        global $arffield;


        $arffield->update($_POST['field'], array('required' => $_POST['required']));


        die();
    }

    function &show_normal_field($show, $field_type) {


        if (in_array($field_type, array('hidden', 'user_id', 'break')))
            $show = false;


        return $show;
    }

    function &arfdisplayfieldhtml($show, $field_type) {


        if (in_array($field_type, array('hidden', 'user_id', 'break', 'section', 'arf_repeater', 'divider', 'html')))
            $show = false;


        return $show;
    }

    function show_other($field,$fields_form,$form, $total_page = 0, $current_form_data_id = 0) {


        $field_name = "item_meta[$field[id]]";


        require(VIEWS_PATH . '/displayotheroptions.php');
    }

    function &change_type($type, $field) {


        global $arfshowfields;


        if ($type != 'user_id' and ! empty($arfshowfields) and ! in_array($field->id, $arfshowfields) and ! in_array($field->field_key, $arfshowfields))
            $type = 'hidden';


        if ($type == 'website')
            $type = 'url';


        return $type;
    }

    function use_field_key_value($opt, $opt_key, $field) {


        if ((isset($field['use_key']) and $field['use_key']) or ( isset($field['type']) and $field['type'] == 'data'))
            $opt = $opt_key;


        return $opt;
    }

    function show($field) {

        global $arfajaxurl;


        $field_name = "item_meta[" . $field['id'] . "]";


        require(VIEWS_PATH . '/displayfield.php');
    }

    function display_field_options($display) {

        if (isset($display['type']) and $display['type'] != '') {

            switch ($display['type']) {


                case 'user_id':


                case 'hidden':


                    $display['label_position'] = false;


                    $display['description'] = false;


                case 'form':


                    $display['required'] = false;


                    $display['default_blank'] = false;


                    break;


                case 'break':


                    $display['required'] = false;


                    $display['options'] = true;


                    $display['default_blank'] = false;


                    $display['css'] = false;


                    break;


                case 'email':


                case 'url':


                case 'website':


                case 'phone':


                case 'image':


                case 'date':


                case 'number':


                    $display['size'] = true;


                    $display['invalid'] = true;


                    $display['clear_on_focus'] = true;


                    break;


                case 'password':


                    $display['size'] = true;


                    $display['clear_on_focus'] = true;


                    break;


                case 'time':


                    $display['size'] = true;


                    break;


                case 'file':


                    $display['invalid'] = true;


                    $display['size'] = true;


                    break;

                case 'html':


                    $display['label_position'] = false;


                    $display['description'] = false;


                case 'divider':
                case 'section':


                    $display['required'] = false;


                    $display['default_blank'] = false;


                    break;
            }
        }

        return $display;
    }

    function display_basic_field_options($display) {

        if (isset($display['type']) and $display['type'] != '') {

            switch ($display['type']) {


                case 'captcha':


                    $display['required'] = false;


                    $display['invalid'] = true;


                    $display['default_blank'] = false;


                    break;


                case 'radio':


                    $display['default_blank'] = false;


                    break;


                case 'text':


                case 'textarea':


                    $display['size'] = true;


                    $display['clear_on_focus'] = true;


                    break;


                case 'select':


                    $display['size'] = true;


                    break;
            }
        }



        return $display;
    }

    function check_value($opt, $opt_key, $field) {


        if (is_array($opt)) {


            if (isset($field['separate_value']) and $field['separate_value']) {


                $opt = isset($opt['value']) ? $opt['value'] : (isset($opt['label']) ? $opt['label'] : reset($opt));
            } else {


                $opt = (isset($opt['label']) ? $opt['label'] : reset($opt));
            }
        }


        return $opt;
    }

    function check_label($opt, $opt_key, $field) {


        if (is_array($opt))
            $opt = (isset($opt['label']) ? $opt['label'] : reset($opt));





        return $opt;
    }


    function input_html($field, $echo = true) {


        global $arfsettings, $arfnovalidate;

        $add_html = '';

        if (isset($field['read_only']) and $field['read_only']) {


            global $arfreadonly;


            if ($arfreadonly == 'disabled' or ( current_user_can('administrator') and is_admin()))
                return;


            $add_html .= ' readonly="readonly" ';
        }

        if( isset($field['max']) && $field['max'] != '' ){
            $add_html .= ' maxlength="'.$field['max'].'" ';
            if( $field['type'] == 'textarea' ){
                $add_html .= ' class="arf_text_is_countable" ';
            }
        }


        if ($arfsettings->use_html) {


            if ($field['type'] == 'number') {


                if ($field['maxnum'] != '' && !is_numeric($field['minnum']))
                    $field['minnum'] = 0;


                if ($field['maxnum'] != '' && !is_numeric($field['maxnum']))
                    $field['maxnum'] = 9999999;


                if (isset($field['step']) && !is_numeric($field['step']))
                    $field['step'] = 1;

                if ($field['maxnum'] > 0)
                    $add_html .= ' max="' . $field['maxnum'] . '"';

                if ($field['minnum'] > 0)
                    $add_html .= ' min="' . $field['minnum'] . '"';


            }else if (in_array($field['type'], array('url', 'email'))) {

                if( !isset( $field['default_value'] ) ){
                    $field['default_value'] = isset( $field['field_options']['default_value'] ) ? $field['field_options']['default_value'] : '';
                }
                if (!$arfnovalidate and isset($field['value']) and $field['default_value'] == $field['value']){
                    $arfnovalidate = true;
                }
            }
        }





        if (isset($field['dependent_fields']) and $field['dependent_fields']) {


            $trigger = ($field['type'] == 'checkbox' or $field['type'] == 'radio') ? 'onclick' : 'onchange';

            $add_html .= ' ' . $trigger . '="frmCheckDependent(this.value,\'' . $field['id'] . '\')"';
        }

        $add_html = apply_filters('arf_modify_input_field_html_outside',$add_html, $field);

        if ($echo)
            echo $add_html;


        return $add_html;
    }

    function add_field_class($class, $field) {


        if ($field['type'] == 'scale' and isset($field['star']) and $field['star'])
            $class .= ' star';


        else if ($field['type'] == 'date')
            $class .= 'frm_date';



        return $class;
    }

    function add_radio_value_opt_label($field) {
        echo '<div class="arfshowfieldclick">';
        echo '<div class="field_' . $field['id'] . '_option_key frm_option_val_label">' . addslashes(esc_html__('Image', 'ARForms')) . '</div>';
        echo '<div class="field_' . $field['id'] . '_option_key frm_option_key_label" style="display:block;">' . addslashes(esc_html__('Saved Value', 'ARForms')) . '</div>';
        echo '</div>';
    }

    function arfdatepickerjs($field_id, $options) {


        if (isset($options['unique'])) {


            global $MdlDb, $wpdb, $arffield;


            $field = $arffield->getOne($options['field_id']);


            $field->field_options = maybe_unserialize($field->field_options);


            $query = "SELECT entry_value FROM $MdlDb->entry_metas WHERE field_id=" . (int) $options['field_id'];


            if (is_numeric($options['entry_id'])) {


                $query .= " and entry_id != " . (int) $options['entry_id'];
            } else {


                $disabled = wp_cache_get($options['field_id'], 'arfuseddates');
            }


            if (!isset($disabled) or ! $disabled)
                $disabled = $wpdb->get_col($query);



            if (isset($post_dates) and $post_dates)
                $disabled = array_merge((array) $post_dates, (array) $disabled);


            $disabled = apply_filters('arfuseddates', $disabled, $field, $options);


            if (!$disabled)
                return;


            if (!is_numeric($options['entry_id']))
                wp_cache_set($options['field_id'], $disabled, 'arfuseddates');


            $formatted = array();


            foreach ($disabled as $dis)
                $formatted[] = date('Y-n-j', strtotime($dis));


            $disabled = $formatted;


            unset($formatted);


            echo ',beforeShowDay: function(date){var m=(date.getMonth()+1),d=date.getDate(),y=date.getFullYear();var disabled=' . json_encode($disabled) . ';if($.inArray(y+"-"+m+"-"+d,disabled) != -1){return [false];} return [true];}';
        }
    }

    function ajax_get_data($entry_id, $field_id, $current_field) {


        global $arfrecordmeta, $arffield, $arrecordhelper, $arfieldhelper;


        $data_field = $arffield->getOne($field_id);


        $current = $arffield->getOne($current_field);


        $entry_value = $arrecordhelper->get_post_or_entry_value($entry_id, $data_field);


        $value = $arfieldhelper->get_display_value($entry_value, $data_field);

        if ($value and ! empty($value))
            echo "<p class='frm_show_it'>" . $value . "</p>\n";



        echo '<input type="hidden" id="field_' . $current->field_key . '" name="item_meta[' . $current_field . ']" value="' . stripslashes(esc_attr($entry_value)) . '"/>';

        die();
    }

    function input_fieldhtml($field, $echo = true) {
        global $arfsettings, $armainhelper, $wpdb, $MdlDb, $arfform;

        $class = '';
        $add_html = '';


        if ($field['type'] == 'date' || $field['type'] == 'phone'){
            $field['size'] = '';
        }

        if( isset($field['max']) && $field['max'] != '' ){
            $add_html .= ' maxlength="'.$field['max'].'" ';
        }

        if( isset($field['minlength']) && $field['minlength'] != '' ){
            $add_html .= ' minlength="'.$field['minlength'].'" ';
            if( $field['type'] == 'phone' || $field['type'] == 'tel' ){
                $add_html .= ' data-validation-minlength-message="'.$field['invalid'].'" ';
            }
        }
        if (isset($field['size']) and $field['size'] > 0) {


            if (!in_array($field['type'], array('textarea', 'select', 'data', 'time')))
                $add_html .= ' size="' . $field['size'] . '"';


            $class .= " auto_width";
        }



        if (!is_admin() or ! isset($_GET) or ! isset($_GET['page']) or $_GET['page'] == 'ARForms_entries') {


            $action = isset($_REQUEST['arfaction']) ? 'arfaction' : 'action';

            $action = $armainhelper->get_param($action);

            $is_home_preview = ( 'preview' == $action && isset( $_GET['arf_is_home'] ) && true == $_GET['arf_is_home'] ) ? true : false;

            if (isset($field['required']) and $field['required']) {

                if ($field['type'] == 'file' and $action == 'edit') {
                    
                } else {
                    if ($field['type'] == 'select' || $field['type'] == 'arf_multiselect' || $field['type'] == ARF_AUTOCOMPLETE_SLUG) {
                        
                        $class .= "select_controll_" . $field['id'] . " arf_required arf_select_controll";

                        if( 'arf_multiselect' == $field['type'] ){
                            $class .= " arf_multiselect_dropdown ";
                        }
                        
                        $form_id = $field['form_id'];

                        $form_css_saved = wp_cache_get( 'arf_form_css_' . $form_id );

                        if( 'preview' == $action && !$is_home_preview ){
                            $preview_opt = get_option( $_GET['arf_opt_id'] );
                            $preview_opt_data = arf_json_decode( stripslashes_deep( $preview_opt['posted_data'] ), true );
                            
                        } else {
                            if( false == $form_css_saved ){
                                $form_data = $arfform->arf_select_db_data( true, '', $MdlDb->forms, 'form_css', 'WHERE id = %d', array( $form_id ), '', '', '', false, true );

                                wp_cache_set( 'arf_form_css_' . $form_id , $form_data->form_css );

                                $form_css = maybe_unserialize( $form_data->form_css );
                            } else {
                                $form_css = maybe_unserialize( $form_css_saved );
                            }

                        }


                    } elseif ($field['type'] == 'time') {
                        $class .= "time_controll_" . $field['id'] . " arf_required ";
                    } else {
                        if( $field['type'] == 'textarea' && $field['required'] == 1 && isset($field['max']) && $field['max'] != ''){
                            $class .= " arf_text_is_countable arf_required ";
                        } else if($field['type'] == 'textarea' && $field['required'] != 1 && isset($field['max']) && $field['max'] != ''){
                            $class .= " arf_text_is_countable";
                        } else {
                            $class .= " arf_required ";    
                        }
                        
                    }
                }
            } else {
                if ($field['type'] == 'select' || $field['type'] == 'arf_multiselect' || $field['type'] == ARF_AUTOCOMPLETE_SLUG){
                    if( $field['type'] == 'arf_multiselect' ){
                        $class .= " arf_multiselect_dropdown ";
                    }
                    $form_id = $field['form_id'];

                    $form_css_saved = wp_cache_get( 'arf_form_css_' . $form_id );

                    if( 'preview' == $action && !$is_home_preview ){
                        $preview_opt = get_option( $_GET['arf_opt_id'] );
                        $preview_opt_data = arf_json_decode( stripslashes_deep( $preview_opt['posted_data'] ), true );
                        
                    } else {
                        if( false == $form_css_saved ){
                            $form_data = $arfform->arf_select_db_data( true, '', $MdlDb->forms, 'form_css', 'WHERE id = %d', array( $form_id ), '', '', '', false, true );

                            wp_cache_set( 'arf_form_css_' . $form_id , $form_data->form_css );

                            $form_css = maybe_unserialize( $form_data->form_css );
                        } else {
                            $form_css = maybe_unserialize( $form_css_saved );
                        }

                    }
                }
            }

            if( $field['type'] == 'phone' && isset($field['phonetype']) && $field['phonetype'] == 1 ){
                $class .= " arf_phone_utils ";
            }

            if (isset($field['clear_on_focus']) and $field['clear_on_focus'] and ! empty($field['default_value'])) {


                $val = esc_attr($field['default_value']);

                $add_html .= ' onfocus="arfcleardedaultvalueonfocus(' . "'" . $val . "'" . ',this,' . "'" . $field['default_blank'] . "'" . ')" onblur="arfreplacededaultvalueonfocus(' . "'" . $val . "'" . ',this,' . "'" . $field['default_blank'] . "'" . ')" placeholder="' . $val . '"';


                if ($field['value'] == $field['default_value'])
                    $class .= ' arfdefault';
            }
        }





        if (isset($field['input_class']) and ! empty($field['input_class']))
            $class .= ' ' . $field['input_class'];





        $class = apply_filters('arfaddfieldclasses', $class, $field);


        if (!empty($class))
            $add_html .= ' class="' . $class . '"';





        if (isset($field['shortcodes']) and ! empty($field['shortcodes'])) {


            foreach ($field['shortcodes'] as $k => $v) {


                $add_html .= ' ' . $k . '="' . $v . '"';


                unset($k);


                unset($v);
            }
        }



        $add_html = apply_filters('arf_modify_input_field_html_outside',$add_html, $field);

        if ($echo)
            echo $add_html;





        return $add_html;
    }

    function ajax_time_options() {


        global $style_settings, $MdlDb, $wpdb, $armainhelper, $arfrecordmeta;


        extract($_POST);



        $time_key = str_replace('field_', '', $time_field);


        $date_key = str_replace('field_', '', $date_field);


        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($date)))
            $date = $armainhelper->convert_date($date, $style_settings->date_format, 'Y-m-d');


        $date_entries = $arfrecordmeta->getEntryIds("fi.field_key='$date_key' and entry_value='$date'");





        $opts = array('' => '');


        $time = strtotime($start);


        $end = strtotime($end);


        $step = explode(':', $step);


        $step = (isset($step[1])) ? ($step[0] * 3600 + $step[1] * 60) : ($step[0] * 60);


        $format = ($clock) ? 'H:i' : 'h:i A';





        while ($time <= $end) {


            $opts[date($format, $time)] = date($format, $time);


            $time += $step;
        }





        if ($date_entries and ! empty($date_entries)) {


            $used_times = $wpdb->get_col($wpdb->prepare("SELECT entry_value FROM $MdlDb->entry_metas it LEFT JOIN $MdlDb->fields fi ON (it.field_id = fi.id) WHERE fi.field_key= %s and it.entry_id in (" . implode(',', $date_entries) . ")", $time_key));





            if ($used_times and ! empty($used_times)) {


                $number_allowed = apply_filters('arfallowedtimecount', 1, $time_key, $date_key);


                $count = array();


                foreach ($used_times as $used) {


                    if (!isset($opts[$used]))
                        continue;





                    if (!isset($count[$used]))
                        $count[$used] = 0;


                    $count[$used] ++;





                    if ((int) $count[$used] >= $number_allowed)
                        unset($opts[$used]);
                }


                unset($count);
            }
        }





        echo json_encode($opts);


        die();
    }

    function destroy() {


        global $arffield;


        $field_id = $arffield->destroy($_POST['field_id']);


        die();
    }

    function arf_prevalidateform_outside() {

        $form_id = $_POST['form_id'];

        $arf_errors = array();

        $arf_form_data = array();

        $values = $_POST;

        $arf_form_data = apply_filters('arf_populate_field_from_outside', $arf_form_data, $form_id, $values);

        $arf_errors = apply_filters('arf_validate_form_outside_errors', $arf_errors, $form_id, $values, $arf_form_data);

        if (isset($arf_errors['arf_form_data'])) {
            $arf_form_data = array_merge($arf_form_data, $arf_errors['arf_form_data']);
        }

        unset($arf_errors['arf_form_data']);

        if (count($arf_form_data) > 0) {
            echo '^arf_populate=';
            foreach ($arf_form_data as $field_id => $field_value) {
                echo $field_id . '^|^' . $field_value . '~|~';
            }
            echo '^arf_populate=';
        }

        if (count($arf_errors) > 0) {
            foreach ($arf_errors as $field_id => $error) {
                echo $field_id . '^|^' . $error . '~|~';
            }
        } else {
            echo 0;
        }

        die();
    }

    function arf_resetformoutside() {
        global $arfform, $arfieldhelper;

        $form_id = $_POST['form_id'];

        $arf_form_data = array();

        $form = $arfform->getOne((int) $form_id);

        $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);

        $values = $arrecordhelper->setup_new_vars($fields, $form);

        $arf_form_data = apply_filters('arf_populate_field_after_from_submit', $arf_form_data, $form_id, $values, $form);

        if (count($arf_form_data) > 0) {
            $arferr = array();
            foreach ($arf_form_data as $field_id => $field_value) {
                $arferr[$fieldid] = $fieldvalue;
            }
            $return["conf_method"] = "validationerror";
            $return["message"] = $arferr;
            $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
            echo json_encode($return);
            exit;
        }

        die();
    }

    function arf_add_new_preset(){

        $fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);

        if( $fn ){
            
            $wp_upload_dir = wp_upload_dir();
            $upload_main_url = $wp_upload_dir['basedir'] . '/arforms/import_preset_value/';
            
            $arfilecontroller = new arfilecontroller( $_FILES['preset_file'], false );

            $arfilecontroller->default_error_msg = esc_html__('Please select a CSV file','ARForms');

            if( !$arfilecontroller ){
                echo 'error~|~'.esc_html__('Please select a CSV file','ARForms');
                die;
            }

            $arfilecontroller->check_cap = true;
            $arfilecontroller->capabilities = array( 'arfviewforms', 'arfeditforms', 'arfchangesettings' );

            $arfilecontroller->check_nonce = true;
            $arfilecontroller->nonce_data = isset( $_POST['_nonce'] ) ? $_POST['_nonce'] : '';
            $arfilecontroller->nonce_action = 'arf_edit_form_nonce';

            $arfilecontroller->check_only_image = false;

            $arfilecontroller->check_specific_ext = true;
            $arfilecontroller->allowed_ext = array( 'csv' );

            $destination = $upload_main_url . $fn;

            $upload_file = $arfilecontroller->arf_process_upload( $destination );

            if( false == $upload_file ){
                echo 'error~|~' . $arfilecontroller->error_message;
                die;
            } else {
                echo $fn;
                die;
            }

        } else {
            echo 'error~|~'.esc_html__('Please select a CSV file','ARForms');
            die;
        }

    }

    function arf_add_new_preset_old() {

        if( false == current_user_can( 'arfviewforms' ) || false == current_user_can( 'arfeditforms' ) || false == current_user_can( 'arfchangesettings' ) ){
            echo 'error~|~'.esc_html__("Sorry, you don't have permission to perform this action.","ARForms");
            die;
        }

        $fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['basedir'] . '/arforms/import_preset_value/';

        if( !isset($_FILES['preset_file']) ){
            echo 'error~|~'.esc_html__('Please select a CSV file','ARForms');
            die;
        }

        $checkext = explode(".", $_FILES['preset_file']['name']);
        $ext = $checkext[count($checkext) - 1];
        $ext = strtolower($ext);

        $prevent_ext = array('php','php3','php4','php5','pl','py','jsp','asp','exe','cgi');

        if ( in_array($ext, $prevent_ext) || $ext != 'csv' ) {
            echo 'error~|~'.esc_html__('Please select a CSV file','ARForms');
            die;
        }

        if ($fn) {
            if( !function_exists('WP_Filesystem' ) ){
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            WP_Filesystem();
            global $wp_filesystem;

            $file_content = $wp_filesystem->get_contents( $_FILES['preset_file']['tmp_name'] );

            $wp_filesystem->put_contents( $upload_main_url . $fn, $file_content, 0777 );

            echo $fn;

            $csvData = $wp_filesystem->get_contents( $upload_main_url . $fn );
            $lines = explode(PHP_EOL, $csvData);
            $csv_array = array();
            foreach ($lines as $line) {
                $csv_array[] = str_getcsv($line);
            }          
            $preset_data_array = array();
            $i = 0;
            $preset_data_array['seperate_value'] = 0;
            foreach ($csv_array as $data) {
                if ($data[0] != "") {
                    $preset_data_array[$i]['label'] = $data[0];                    
                    if (isset($data[1]) && $data[1] != "" && $data[0] != "") {      
                        $preset_data_array['seperate_value'] = 1;                  
                        $preset_data_array[$i]['key'] = $data[1];
                    } 
                    $i++;
                }
            }
        } else {
            echo 'error~|~'.esc_html__('Please select a CSV file','ARForms');
        }

        die();
    }

    function arf_save_new_preset() {
        $fn = isset($_POST['file_name']) ? $_POST['file_name'] : "";
        $arf_preset_future_use = isset($_POST['arf_preset_future_use']) ? $_POST['arf_preset_future_use'] : 'false';
        $arf_preset_title = isset($_POST['arf_preset_title']) ? $_POST['arf_preset_title'] : "";
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['basedir'] . '/arforms/import_preset_value/';
        if ($fn != "") {

            $file = $upload_main_url . $fn;
            $csv_data = array();
            ini_set('auto_detect_line_endings', true);

            $fh = fopen($file, 'r');
            $i = 0;

            while (($line = fgetcsv($fh, 1000, "\t")) !== false) {
                $csv_data[] = $line;
                $i++;
            }

            $preset_data_array = array();
            $preset_data_array['title'] = $arf_preset_title;
            $i = 0;
            $data_value = "";
            foreach ($csv_data as $data) {
                if ($data[0] != "") {
                    $preset_data_array['data'][$i]['label'] = $data[0];
                    $data[0] = str_replace('"', "'", $data[0]);



                    if (isset($data[1]) && $data[1] != "") {
                        $data_value .='"' . htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') . '|' . htmlspecialchars(str_replace('"', "'", $data[1]), ENT_QUOTES, 'UTF-8') . '",';
                        $preset_data_array['data'][$i]['value'] = htmlspecialchars(str_replace('"', "'", $data[1]), ENT_QUOTES, 'UTF-8');
                    } else {
                        $data_value .='"' . htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') . '",';
                        $preset_data_array['data'][$i]['value'] = htmlspecialchars(str_replace('"', "'", $data[0]), ENT_QUOTES, 'UTF-8');
                    }
                    $i++;
                }
            }

            if ($arf_preset_future_use === 'true' && isset($preset_data_array['data'])) {
                $arf_preset_values = get_option('arf_preset_values');
                $arf_preset_values[] = $preset_data_array;
                update_option('arf_preset_values', $arf_preset_values);

                $data_value = substr($data_value, 0, -1);
                echo '<li class="arf_selectbox_option" data-label="' . htmlspecialchars(str_replace('"', "'", $arf_preset_title), ENT_QUOTES, 'UTF-8') . '" data-value=\'[' . $data_value . ']\'>' . htmlspecialchars(str_replace('"', "'", $arf_preset_title), ENT_QUOTES, 'UTF-8') . '</li>';
            } else if ($data_value != "") {
                $data_value = substr($data_value, 0, -1);
                echo '<li class="arf_selectbox_option" data-label="Custom" data-value=\'[' . $data_value . ']\'>' . addslashes(esc_html__('Custom', 'ARForms')) . '</li>';
            }
        } else {
            echo 'error';
        }

        die();
    }


    function arf_save_new_dynamic_option_field(){
        $fn = isset($_POST['file_name']) ? $_POST['file_name'] : "";
        
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['basedir'] . '/arforms/import_preset_value/';

        if ($fn != "") {
            $file = $upload_main_url . $fn;

            if( !file_exists( $file ) ){
                echo "error";
                die;
            }

            $csv_data = array();
            ini_set('auto_detect_line_endings', true);

            $fh = fopen($file, 'r');
            $i = 0;

            $csv_length = 0;
            while (($line = fgetcsv($fh, 1000, "\t")) !== FALSE) {
                $csv_data[] = $line;
                $i++;
            }

            echo "<li class='arf_selectbox_option' data-label='Custom' data-value=''>Custom</li>|".json_encode( $csv_data )."";
            unlink( $file );

        } else {
            echo "error";
        }
        die();
    }

    function arf_save_new_preset_field_function() {
        $fn = isset($_POST['file_name']) ? $_POST['file_name'] : "";

        $arf_preset_future_use = isset($_POST['arf_save_preset_for_future']) ? $_POST['arf_save_preset_for_future'] : 'false';
        $arf_preset_title = isset($_POST['arf_preset_title']) ? $_POST['arf_preset_title'] : "";
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['basedir'] . '/arforms/import_preset_value/';

        if ($fn != "") {
            $file = $upload_main_url . $fn;

            if( !file_exists( $file ) ){
                echo "error";
                die;
            }

            $csv_data = array();
            ini_set('auto_detect_line_endings', true);

            $fh = fopen($file, 'r');
            $i = 0;

            $csv_length = 0;
            while (($line = fgetcsv($fh, 1000, "\t")) !== FALSE) {
                $csv_data[] = $line;
                $i++;
            }

            $preset_data_array = array();
            $preset_data_array['title'] = $arf_preset_title;
            $data_value = "";
            if (is_array($csv_data) && count($csv_data) > 0 && $csv_data[0][0] != "") {
                $k = 0;
                foreach ($csv_data as $data) {
                    if ($data[0] != "") {
                        $preset_data_array['data'][$k]['label'] = $data[0];
                        $data[0] = str_replace('"', "'", $data[0]);

                        if (isset($data[1]) && $data[1] != "") {
                            $data_value .= '"' . htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') . '|' . htmlspecialchars(str_replace('"', "'", $data[1]), ENT_QUOTES, 'UTF-8') . '",';
                            $preset_data_array['data'][$k]['value'] = htmlspecialchars(str_replace('"', "'", $data[1]), ENT_QUOTES, 'UTF-8');
                        } else {
                            $data_value .='"' . htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') . '",';
                            $preset_data_array['data'][$k]['value'] = htmlspecialchars(str_replace('"', "'", $data[0]), ENT_QUOTES, 'UTF-8');
                        }
                        $k++;
                    }
                }

                if ( $arf_preset_future_use === 'true' && isset($preset_data_array['data'])) {
                    $arf_preset_values = (get_option('arf_preset_values')!='') ? get_option('arf_preset_values'): '';
                    
                    if( !is_array($arf_preset_values) || $arf_preset_values == '' ){
                        $arf_preset_values = array();
                    }
                    array_push($arf_preset_values,$preset_data_array);
                    $arf_preset_values = isset($arf_preset_values) ? $arf_preset_values : array();
                    update_option('arf_preset_values', $arf_preset_values);
                    $temp_arf_preset_value = $arf_preset_values;
                    end( $temp_arf_preset_value );
                    $last_key = key( $temp_arf_preset_value );
                    $data_value = substr($data_value, 0, -1);
                    echo '<li class="arf_selectbox_option arf_field_data_dynamic" data-label="' . htmlspecialchars(str_replace('"', "'", $arf_preset_title), ENT_QUOTES, 'UTF-8') . '" data-value=\'["csv_preset_' . $last_key . '"]\'>' . htmlspecialchars(str_replace('"', "'", $arf_preset_title), ENT_QUOTES, 'UTF-8') . '</li>';
                } else {
                    $data_value = substr($data_value, 0, -1);
                    echo '<li class="arf_selectbox_option" data-label="Custom" data-value=\'[' . htmlspecialchars( $data_value, ENT_QUOTES, 'UTF-8' ) . ']\'>' . addslashes(esc_html__('Custom', 'ARForms')) . '</li>';
                }
            } else {
                echo "error";
            }
        } else {
            echo "error";
        }
        die();
    }

    function arf_upload_radio_label_img() {
        $file = $_POST['image'];
        ?>
        <img src="<?php echo esc_attr($file); ?>" style="float: left; margin: 0 10px 0 0; height: 20px; width: 20px;" />
        <?php
        die();
    }

    function arfspecialchars($obj) {
        global $arformcontroller;
        $newArray = $return = array();
        if (is_object($obj)) {
            $newArray = $arformcontroller->arfObjtoArray($obj);
        } else {
            $newArray = $obj;
        }
        if (is_array($newArray)) {
            foreach ($newArray as $key => $value) {
                if (is_array($value)) {
                    $return[$key] = array_map(array($this, __FUNCTION__), $value);
                } else if (is_object($value)) {
                    $value = $arformcontroller->arfObjtoArray($value);
                    $return[$key] = array_map(array($this, __FUNCTION__), $value);
                } else {
                    $value = str_replace("'", "&#8217", $value);
                    $return[$key] = $value;
                }
            }
        } else {
            $return = str_replace("'", "&#8217", $newArray);
        }
        return $return;
    }
    
    function arf_change_imagecontrol_field_data_outside($field_json_data){
        if( $field_json_data['field_data']['imagecontrol']['image_url'] == '' ){
            $field_json_data['field_data']['imagecontrol']['image_url'] = ARFIMAGESURL."/no-image.png";
        }
        return $field_json_data;
    }

    function arf_get_field_multicolumn_icon($column,$arf_editor_index_row_val = '{arf_editor_index_row}'){
        if( $column == "" || $column < 1 || $column > 6 ){
            return '';
        }
        $data_value = $function_id = $checked = $svg_icon = "";
        switch($column){
            case 1:
                $function_id = "single_column";
                $data_value = "arf_1";
                $checked = "checked='checked'";
                $svg_icon  = "<svg id='multicolumn_one' height='24' width='18'>".ARF_CUSTOM_COL1_ICON."</svg>";
                break;
            case 2:
                $function_id = "two_column";
                $data_value = "arf_2";
                $checked = "";
                $svg_icon  = "<svg id='multicolumn_two' height='24' width='27'>" . ARF_CUSTOM_COL2_ICON . "</svg>";
                break;
            case 3:
                $function_id = "three_column";
                $data_value = "arf_3";
                $checked = "";
                $svg_icon  = "<svg id='multicolumn_three' height='24' width='35'>" . ARF_CUSTOM_COL3_ICON . "</svg>";
                break;
            case 4:
                $function_id = "four_column";
                $data_value = "arf_4";
                $checked = "";
                $svg_icon  = "<svg id='multicolumn_four' height='24' width='35'>" . ARF_CUSTOM_COL4_ICON . "</svg>";
                break;
            case 5:
                $function_id = "five_column";
                $data_value = "arf_5";
                $checked = "";
                $svg_icon  = "<svg id='multicolumn_five' height='24' width='45'>" . ARF_CUSTOM_COL5_ICON . "</svg>";
                break;
            case 6:
                $function_id = "six_column";
                $data_value = "arf_6";
                $checked = "";
                $svg_icon  = "<svg id='multicolumn_six' height='24' width='50'>" . ARF_CUSTOM_COL6_ICON . "</svg>";
                break;
        }
        $return_func  = "<div class='arf_multicolumn_opt' id='{$function_id}' data-value='{$data_value}'>";
        $return_func .= "<input type='radio' class='rdostandard multicolfield' name='classes' onclick='makeNewSortable({$column},this);' data-id='{$arf_editor_index_row_val}' id='classes_{$arf_editor_index_row_val}_{$column}' {$checked} value='{$data_value}' style='display:none;' />";
        $return_func .= "<label for='classes_{$arf_editor_index_row_val}_{$column}'>";
        $return_func .= "<span class='lblsubtitle_span_column'></span>";
        $return_func .= $svg_icon;
        $return_func .= "</label>";
        $return_func .= "</div>";
        return $return_func;
    }

    function arf_get_multicolumn_expand_icon(){
        $icon = '<div class="arf_multi_column_expand_icon"><svg width="11px" height="20px"><g>' . ARF_FIELD_MULTICOLUMN_EXPAND_ICON . '</g></svg></div>';
        return $icon;
    }

    function arf_get_field_control_icons($type = '',$field_required_cls = '',$field_id = '{arf_field_id}',$field_required = 0,$field_type = '{arf_field_type}',$form_id = '{arf_form_id}', $title = ''){
        if( $type == "" ){
            return '';
        }
        $svg_icon = "";

        switch($type){
            case 'require':
                if( !empty( $title ) ){
                    $title_attr = $title;
                } else {
                    $title_attr = addslashes( esc_html__( 'Required', 'ARForms' ) );
                }
                $svg_icon = "<div class='arf_field_option_icon'><a title='".$title_attr."' data-title='".$title_attr."' class='arf_field_option_input arf_field_icon_tooltip {$field_required_cls}' id='isrequired_{$field_id}' href='javascript:void(0)' onclick='javascript:arfmakerequiredfieldfunction({$field_id},{$field_required},2)'><svg id='required' height='20' width='21'><g>".ARF_CUSTOM_REQUIRED_ICON."</g></svg></a></div>";
                break;
            case 'options':
                if( !empty( $title ) ){
                    $title_attr = $title;
                } else {
                    $title_attr = addslashes( esc_html__( 'Field Settings', 'ARForms' ) );
                }
                $svg_icon = "<div  class='arf_field_option_icon arf_field_settings_icon'><a title='".$title_attr."' data-title='".$title_attr."' class='arf_field_option_input arf_field_icon_tooltip' href='javascript:void(0)' onClick=\"javascript:arfshowfieldoptions({$field_id},'{$field_type}');\"><svg id='fieldoption' height='20' width='20'><g>".ARF_CUSTOM_FIELDOPTION_ICON."</g></svg></a></div>";
                break;
            case 'delete':
                if( !empty( $title ) ){
                    $title_attr = $title;
                } else {
                    $title_attr = addslashes( esc_html__( 'Delete Field', 'ARForms' ) );
                }
                if( $field_type == 'imagecontrol' ){
                    $svg_icon = "<div class='arf_field_option_icon arf_field_action_iconbox'><a title='".$title_attr."' data-title='".$title_attr."' class='arf_field_option_input arf_field_icon_tooltip' data-toggle='arfmodal' href='#delete_field_message_{$field_id}' id='arf_field_delete_{$field_id}' onClick=\"arfchangedeletemodalwidth('arfimagecontrol', {$field_id});\"><svg id='delete' height='19' width='19'><g>".ARF_CUSTOM_DELETE_ICON."</g></svg></a></div>";
                } else {
                    $svg_icon = "<div class='arf_field_option_icon arf_field_action_iconbox'><a title='".$title_attr."' data-title='".$title_attr."' class='arf_field_option_input arf_field_icon_tooltip' data-toggle='arfmodal' href='#delete_field_message_{$field_id}' id='arf_field_delete_{$field_id}' onClick=\"arfchangedeletemodalwidth('arfdeletemodabox', {$field_id});\"><svg id='delete' height='19' width='19'><g>".ARF_CUSTOM_DELETE_ICON."</g></svg></a></div>";
                }
                break;
            case 'duplicate':
                if( !empty( $title ) ){
                    $title_attr = $title;
                } else {
                    $title_attr = addslashes( esc_html__( 'Duplicate Field', 'ARForms' ) );
                }
                $svg_icon = "<div class='arf_field_option_icon'><a title='".$title_attr."' data-title='".$title_attr."' class='arf_field_option_input arf_field_icon_tooltip' href='javascript:void(0)' onclick=\"javascript:arfduplicatefield({$form_id},'{$field_type}',{$field_id},{$field_id});\"><svg id='duplicate' height='19' width='19'><g>".ARF_CUSTOM_DUPLICATE_ITEM."</g></svg></a></div>";
                break;
            case 'move':
                if( !empty( $title ) ){
                    $title_attr = $title;
                } else {
                    $title_attr = addslashes( esc_html__( 'Move', 'ARForms' ) );
                }
                $svg_icon = "<div class='arf_field_option_icon'><a title='".$title_attr."' data-title='".$title_attr."' class='arf_field_option_input arf_field_icon_tooltip'><svg id='moveing' height='20' width='21'><g>".ARF_CUSTOM_MOVING_ICON."</g></svg></a></div>";
                break;
            case 'edit_options':
                if( !empty( $title ) ){
                    $title_attr = $title;
                } else {
                    $title_attr = addslashes( esc_html__( 'Manage Options', 'ARForms' ) );
                }
                $svg_icon = "<div class='arf_field_option_icon'><a title='".$title_attr."' data-title='".$title_attr."' class='arf_field_option_input arf_field_icon_tooltip arf_edit_value_option_button' data-field-id='{$field_id}' id='arf_edit_value_option_button'><svg id='edit_opt_icon' height='20' width='21'><g>".ARF_FIELD_EDIT_OPTION_ICON."</g></svg></a></div>";
                break;
            case 'running_total_icon':
                if( !empty( $title ) ){
                    $title_attr = $title;
                } else {
                    $title_attr = addslashes( esc_html__( 'Running Total (Math Logic) is Enabled', 'ARForms' ) );
                }
                $svg_icon = "<div class='arf_field_option_icon arf_html_running_total_icon'><a title='".$title_attr."' data-title='".$title_attr."' class='arf_field_option_input arf_field_icon_tooltip'><svg id='running_total_icon' height='20' width='21'><g>".ARF_FIELD_HTML_RUNNING_TOTAL_ICON."</g></svg></a></div>";
            default:
                $svg_icon = apply_filters("arf_field_option_icon_render_outside", $svg_icon, $type, $field_required_cls, $field_id, $field_required, $field_type, $form_id, $title);
                break;
        }
        return $svg_icon;
    }

    function arf_get_field_data_dynamic(){

        $dynamic_field_key = isset($_POST['field_key']) ? stripslashes($_POST['field_key']) : "";
        $record_count = isset($_POST['arf_page']) ? $_POST['arf_page'] : "";
        $offset = ( (100 * $record_count) - 100 );
        $continue = true;
        $destroy = false;
            
        if( "[\"woocom_products\"]" == $dynamic_field_key ){

            if ( class_exists( 'WooCommerce' ) ) {

                $all_products = wc_get_products( array(
                    'numberposts'    => -1,
                    'post_status'    => 'publish',
                ) );

                $total_wc_products = count($all_products);

                $products = wc_get_products( array(
                    'posts_per_page' => 100,
                    'paged'          => $record_count,
                    'post_status'    => 'publish',  
                    'order'          => 'DESC',
                ) );

                if( ( $total_wc_products / 100 ) < $record_count ){
                    $continue = false;
                    $destroy = true;
                }

                $product_display = array();
                foreach($products as $product){
                    $product_display[] = $product->get_title() . '|' . $product->get_price();
                }
                if( !empty( $product_display ) ){
                    $dynamic_field_data = array(
                        'field_data_dynamic_arr'    => $product_display,
                        'total_records'             => $total_wc_products,
                        'continue'                  => $continue
                    );

                    echo json_encode($dynamic_field_data);
                }
            }
        }else if( "[\"wp_categories\"]" == $dynamic_field_key ){
            $all_categories = get_categories(array(
              'hide_empty' => false
            ));

            $total_wp_cat = count($all_categories);

            $args = array(
                            'taxonomy'  => 'category',
                            'orderby'   => 'id',
                            'order'     => 'ASC',
                            'hide_empty'=> 0,
                            'number'    => 100,
                            'offset'    => $offset,
                        );

            $categories = get_terms($args);

            if( ( $total_wp_cat / 100 ) < $record_count ){
                $continue = false;
                $destroy = true;
            }

            $catg_name = array();

            foreach($categories as $ctgry){
                $catg_name[] = $ctgry->name . '|' . $ctgry->cat_ID;
            }

            if( !empty( $catg_name ) ){
                $dynamic_field_data = array(
                    'field_data_dynamic_arr'    => $catg_name,
                    'total_records'             => $total_wp_cat,
                    'continue'                  => $continue
                );                
                echo json_encode($dynamic_field_data);
            }
        }else if( "[\"wp_post_tags\"]" == $dynamic_field_key ){

            $all_tags = get_tags(array(
                'hide_empty' => false
            ));

            $total_wp_tag = count($all_tags);

            $args = array(
                            'orderby'   => 'id',
                            'order'     => 'ASC',
                            'hierarchical'  => true,
                            'hide_empty'=> 0,
                            'number'    => 100,
                            'offset'    => $offset,
                        );
            $taxonomies = array( 
                'post_tag'
            );

            $tags = get_terms($taxonomies, $args);

            if( ( $total_wp_tag / 100 ) < $record_count ){
                $continue = false;
                $destroy = true;
            }

            $tag_name = array();

            foreach($tags as $tg){
                $tag_name[] = $tg->name . '|' . $tg->term_id;
            }

            if( !empty( $tag_name ) ){
                $dynamic_field_data = array(
                    'field_data_dynamic_arr'    => $tag_name,
                    'total_records'             => $total_wp_tag,
                    'continue'                  => $continue
                );                
                echo json_encode($dynamic_field_data);
            }
        }else {
            $arf_preset_values = get_option('arf_preset_values');

            $csv_preset_cntr = 0;

            if (!empty($arf_preset_values) && is_array($arf_preset_values)) {
                foreach ($arf_preset_values as $key => $value) {
                    if( "[\"csv_preset_".$key."\"]" == $dynamic_field_key ){
                        $preset_data = array();

                        $total_csv_data = count($value['data']);

                        if( ( $total_csv_data / 100 ) < $record_count ){
                            $continue = false;
                            $destroy = true;
                        }

                        $data_flag = 100 * $record_count;

                        for ( $i = $data_flag - 100; $i < $data_flag ; $i++ ) {
                            $csv_preset_cntr++;

                            if( $i >= $total_csv_data ){
                                break;
                            }
                            $data = $value['data'][$i];
                            $preset_data[] = htmlspecialchars($data['label'], ENT_QUOTES, 'UTF-8').'|'.htmlspecialchars($data['value'], ENT_QUOTES, 'UTF-8');

                        }

                        $dynamic_field_data = array(
                            'field_data_dynamic_arr'    => $preset_data,
                            'total_records'             => $total_csv_data,
                            'continue'                  => $continue
                        );                
                        echo json_encode($dynamic_field_data);                        
                    }
                }
            }
        }

        die();
    }
}
?>