<?php

class arrecordcontroller {

    function __construct() {

        add_action('admin_menu', array($this, 'menu'), 20);

        add_action('admin_init', array($this, 'admin_js'), 1);

        add_action('init', array($this, 'register_scripts'));

        add_action('wp_enqueue_scripts', array($this, 'add_js'));

        add_action('wp_footer', array($this, 'footer_js'), 1);

        add_action('admin_footer', array($this, 'footer_js'));

        add_action('arfentryexecute', array($this, 'process_update_entry'), 10, 4);

        add_filter('arfactionsubmitbutton', array($this, 'ajax_submit_button'), 10, 3);

        add_filter('arfformsubmitsuccess', array($this, 'get_confirmation_method'), 10, 2);

        add_action('arfformsubmissionsuccessaction', array($this, 'confirmation'), 10, 4);

        add_filter('arffieldsreplaceshortcodes', array($this, 'filter_shortcode_value'), 10, 4);

        add_action('wp_ajax_updatechart', array($this, 'updatechart'));

        add_action('wp_ajax_managecolumns', array($this, 'managecolumns'));

        add_action('wp_ajax_manageincompletecolumns', array( $this, 'manageincompletecolumns' ) );

        add_action('wp_ajax_updateentries', array($this, 'arf_form_entries'));

        add_action('wp_ajax_arf_retrieve_form_entry',array($this,'arf_retrieve_form_entry_data'));

        add_action('wp_ajax_arf_retrieve_form_incomplete_entry', array( $this, 'arf_retrieve_incomplete_form_entry_data') );

        add_action( 'wp_ajax_arf_retrieve_form_data', array( $this, 'arf_retrieve_form_data' ) );

        add_action('wp_ajax_arfchangebulkentries', array($this, 'arfchangebulkentries'));

        add_action('wp_ajax_arfchangebulkincompleteentries', array($this,'arfchangebulkincompleteentries'));

        add_action('wp_ajax_recordactions', array($this, 'arfentryactionfunc'));

        add_action('wp', array($this, 'process_entry'), 10, 0);

        add_action('wp_process_entry', array($this, 'process_entry'), 10, 0);

        add_filter('arfemailvalue', array($this, 'filter_email_value'), 10, 3);

        add_action('wp_ajax_current_modal', array($this, 'current_modal'));

        add_action('wp_ajax_nopriv_current_modal', array($this, 'current_modal'));

        add_action('wp_ajax_arf_edit_entry_values', array($this, 'arf_edit_entry_values'));

        add_action('wp_ajax_arf_edit_repeater_entry_values', array( $this, 'arf_edit_repeater_entry_values' ) );

        add_action('wp_ajax_arf_delete_single_entry', array($this, 'arf_delete_single_entry_function'));
        add_action('wp_ajax_arf_delete_single_incompelete_entry', array( $this, 'arf_delete_single_incomplete_entry_function'));

        add_action('wp_ajax_arf_forms_file_remove', array($this, 'arf_forms_file_remove'));

        add_action('wp_ajax_arf_save_incomplete_form_data', array( $this, 'arf_save_incomplete_form_data' ) );

        add_action('wp_ajax_arf_get_next_child_data', array( $this, 'arf_get_next_child_data' ) );

        add_action('wp_ajax_nopriv_arf_get_next_child_data', array( $this, 'arf_get_next_child_data' ) );

        add_action('wp_ajax_nopriv_arf_save_incomplete_form_data', array( $this, 'arf_save_incomplete_form_data' ) );

        add_action('arfaftercreateentry', array( $this, 'arf_remove_incomplete_entry_data'), 8, 2 );

        add_action('wp_ajax_arf_move_incomplete_entry', array( $this, 'arf_move_incomplete_entry_data'));

        add_filter('arf_change_edit_select_array_for_repeater', array( $this, 'arf_change_edit_select_array_for_repeater_callback'), 10, 4);

        add_action( 'arfaftercreateentry', array( $this, 'arf_hide_form_after_submit_max_entries' ), 10, 2 );
    }
    function arf_get_next_child_data(){

        global $arffield;
        $arf_parent_selected_data = isset( $_POST['arf_parent_val'] ) ? $_POST['arf_parent_val'] : '';
        $form_data = isset( $_POST['option_data'] ) ? $_POST['option_data'] : '';
        $is_preview = isset( $_POST['is_preview'] ) ? $_POST['is_preview'] : '';
        $arf_field_id = isset( $_POST['arf_field_id'] ) ? $_POST['arf_field_id'] : '';
        $arf_child_fields = isset( $_POST['child_fields'] ) ? $_POST['child_fields'] : '';
        $arf_child_array = explode(',', $arf_child_fields);
        $saved_preview_data = get_option($form_data);
        $posted_data = json_decode(stripslashes_deep($saved_preview_data['posted_data']), true);
        $child_field_id = $posted_data['item_meta'];
        $arf_child_data = array();
        
        if ( $is_preview == 1 ) {
            foreach( $child_field_id as $k=>$v ){
                $field_data = $posted_data['arf_field_data_'.$k];
                $field_options = arf_json_decode ( $field_data );
                if ( $field_options->parent_field_id != '' && $arf_field_id == $field_options->parent_field_id ) {
                    foreach ( arf_json_decode( $field_options->csv_data ) as $k1 => $v1 ) {
                        $child_value = explode(",", $v1[0] );
                        if ( $child_value[0] == $arf_parent_selected_data ) { 
                            $arf_child_data[ $k ][] = $child_value[1];
                        }
                    }
                }   
            }
        } else {
            foreach($arf_child_array as $key=>$val){
                $field_data = $arffield->getOne( $val );
                foreach ( arf_json_decode( $field_data->field_options['csv_data'] ) as $k => $v ) {
                    $child_value = explode(",", $v[0] );
                    if ( $child_value[0] == $arf_parent_selected_data ) { 
                        $arf_child_data[ $field_data->id ][] = $child_value[1];
                    }
                }
            }
        }

        $arf_option_data = array();
        if ( empty( $arf_child_data ) ) {
            foreach( $arf_child_array as $key=>$value ){
                $arf_option_data['field_'.$value] = "<li data-value='' data-label='Please select'>Please select</li>";
            }
        } else {
            foreach( $arf_child_data as $key=>$value ){
                $arf_option_data['field_'.$key] = "<li data-value='' data-label='Please select'>Please select</li>";
                foreach ( $value as $k=>$v ) {
                    $arf_option_data['field_'.$key] .= "<li data-value='".$v."' data-label='".$v."' >".$v."</li>";
                }
            }
        }

        echo json_encode( $arf_option_data );die();

    }

    function arf_forms_file_remove(){
        global $wpdb, $arffield, $MdlDb, $armainhelper, $arfieldhelper;
        
        if(isset($_POST['entry_id']) && isset($_POST['field_id']) && isset($_POST['file_id']) ){
            $res = $wpdb->get_row("SELECT * FROM $MdlDb->entry_metas WHERE field_id=".$_POST['field_id']." and entry_id=".$_POST['entry_id']);
            if(!empty($res)){
                $new_ids = array();
                $delete_file_id = array();
                $exp_ids = explode("|", $res->entry_value);
                if(in_array($_POST['file_id'], $exp_ids)){
                    $delete_file_id[0] = $_POST['file_id'];
                }
                
                if(count($exp_ids) > 1){

                    $new_ids = array_diff( $exp_ids, $delete_file_id );
                    $entry_val = implode("|",$new_ids);
                    echo $wpdb->query($wpdb->prepare("UPDATE $MdlDb->entry_metas SET entry_value=%s WHERE field_id=%d and entry_id=%d", $entry_val, $_POST['field_id'], $_POST['entry_id']));
                    
                } else{
                    echo $wpdb->query( "DELETE FROM $MdlDb->entry_metas WHERE field_id=".$_POST['field_id']." and entry_id=".$_POST['entry_id']);
                }
                
            }
            
            $image_url='';
            $thum_url='';
            $post_meta_data = get_post_meta($_POST['file_id']);
            if(isset($post_meta_data['_wp_attached_file']) && isset($post_meta_data['_wp_attached_file'][0])){
                $image_name = explode('/',$post_meta_data['_wp_attached_file'][0]);

                $image_name = $image_name[count($image_name) -1 ];

                $image_ext = explode('.',$image_name);

                $image_ext = $image_ext[count($image_ext) - 1];

                $image_ext = strtolower($image_ext);

                $exclude_ext = array('png','jpg','jpeg','jpe','gif','bmp','tif','tiff','ico');

                if( in_array($image_ext,$exclude_ext) ){
                    $image_url = ABSPATH.str_replace('thumbs/', '', $post_meta_data['_wp_attached_file'][0]);
                    $thum_url = ABSPATH.$post_meta_data['_wp_attached_file'][0];
                }
            }
            if($thum_url && $image_url){
                @unlink($thum_url);
                @unlink($image_url);
            }
            wp_delete_attachment($_POST['file_id']);
        }
        
        exit;
    }

    function show_form($id = '', $key = '', $title = false, $description = false, $preview = false, $is_widget_or_modal = false) {

        global $arfform, $user_ID, $arfsettings, $post, $wpdb, $armainhelper, $arrecordcontroller, $arformcontroller, $MdlDb;

        $func_val = "true";
        if (!$preview) {
            $func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($id), $id);
        }

        if ($func_val != 'true' && !isset($_REQUEST['using_ajax']) && $_REQUEST['is_submit_form_' . $id] != 1) {
            $error = json_decode($func_val);
            echo $error->message;
            exit;
        }

        if ($id) {
            $form = $arfform->getOne((int) $id);
        } else if ($key) {
            $form = $arfform->getOne($key);
        }

        $is_confirmation_method = false;
        if (isset($_REQUEST['arf_conf']) and $_REQUEST['arf_conf'] != '') {
            if (isset($_REQUEST['arf_conf']) and $_REQUEST['arf_conf'] == $id) {
                $is_confirmation_method = true;
            }
        }


        $form = apply_filters('arfpredisplayform', $form);

        if ((@$form->is_template or @ $form->status == 'draft') and ! ($preview)) {
            return addslashes(esc_html__('Please select a valid form', 'ARForms'));
        } else if (!$form or ( ($form->is_template or $form->status == 'draft') and ! isset($_GET) and ! isset($_GET['form']))) {
            return addslashes(esc_html__('Please select a valid form', 'ARForms'));
        } else if ($form->is_loggedin && !$user_ID) {
            global $arfsettings;
            return do_shortcode($arfsettings->login_msg);
        }

        return $arrecordcontroller->get_form(VIEWS_PATH . '/formsubmission.php', $form, $title, $description, $preview, $is_widget_or_modal, $is_confirmation_method, $func_val);
    }

    function get_recordparams($form = null) {


        global $arfform, $arfform_params, $armainhelper, $MdlDb;





        if (!$form) {
            $form = $arfform->getAll(array(), 'name', 1);
        }





        if ($arfform_params and isset($arfform_params[$form->id])) {
            return $arfform_params[$form->id];
        }





        $action_var = isset($_REQUEST['arfaction']) ? 'arfaction' : 'action';


        $action = apply_filters('arfshownewentrypage', $armainhelper->get_param($action_var, 'new'), $form);





        $default_values = array(
            'id' => '', 'form_name' => '', 'paged' => 1, 'form' => $form->id, 'form_id' => $form->id,
            'field_id' => '', 'search' => '', 'sort' => '', 'sdir' => '', 'action' => $action
        );





        $values['posted_form_id'] = $armainhelper->get_param('form_id');


        if (!is_numeric($values['posted_form_id']))
            $values['posted_form_id'] = $armainhelper->get_param('form');





        if ($form->id == $values['posted_form_id']) {


            foreach ($default_values as $var => $default) {


                if ($var == 'action')
                    $values[$var] = $armainhelper->get_param($action_var, $default);
                else
                    $values[$var] = $armainhelper->get_param($var, $default);


                unset($var);


                unset($default);
            }
        }else {


            foreach ($default_values as $var => $default) {


                $values[$var] = $default;


                unset($var);


                unset($default);
            }
        }





        if (in_array($values['action'], array('create', 'update')) and ( !isset($_POST) or ( !isset($_POST['action']) and ! isset($_POST['arfaction'])))) {
            $values['action'] = 'new';
        }





        return $values;
    }

    function arf_hide_form_after_submit_max_entries( $entry_id, $form_id ){
        if (!isset($_POST) or ! isset($_POST['form_id']) or ! is_numeric($_POST['form_id']) or ! isset($_POST['entry_key'])) {
            return;
        }
        global $wpdb, $MdlDb;

        $cache_obj = wp_cache_get('arf_form_options_with_css_'.$form_id);
        if($cache_obj == false){
            $form_options = $wpdb->get_row($wpdb->prepare("SELECT `form_css`,`options` FROM `" . $MdlDb->forms . "` WHERE `id` = %d", (int) $form_id));
                
            wp_cache_set('arf_form_options_with_css_'.$form_id, $form_options);
        }else{
            $form_options = $cache_obj;
        }
        
        if(isset($form_options->options) && $form_options->options != ''){
            $form_options = maybe_unserialize($form_options->options);

            $arf_form_total_entries = wp_cache_get( 'arf_total_entries_counter_' . $form_id );
            
            if( false == $arf_form_total_entries ){
                $arf_form_total_entries = $wpdb->get_var($wpdb->prepare("select count(*) from " . $MdlDb->entries . " where form_id = %d", $form_id));                
                wp_cache_set('arf_total_entries_counter_'.$form_id, $arf_form_total_entries);
            }

            if( $arf_form_total_entries >= $form_options['arf_restrict_max_entries'] && $form_options['arf_restrict_entry'] == 1 ){
                if($_POST['form_submit_type'] == 1) {
                    $arf_res_msg_disp = '<div class="arf_form ar_main_div_' . $_POST['form_id'] . '" id="arffrm_' . $_POST['form_id'] . '_container"><div  class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $form_options['arf_res_msg_entry'] . '</div></div></div></div>';
                    $return["conf_method"] = 'message';
                    $return["message"] = $arf_res_msg_disp;
                    $return["hide_forms"] = true;
                    echo json_encode($return);
                    exit;
                } else {
                    $redirect_msg = '<div class="arf_form ar_main_div_' . $_POST['form_id'] . '" id="arffrm_' . $_POST['form_id'] . '_container"><div  class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $form_options['arf_res_msg_entry'] . '</div></div></div></div>';
                    $return["redirect_msg"] = $redirect_msg;
                }
            }
        }
    }

    function process_entry($errors = '') {
        
        global $wpdb, $arformcontroller, $MdlDb, $arfsettings;
        if (!isset($_POST) or ! isset($_POST['form_id']) or ! is_numeric($_POST['form_id']) or ! isset($_POST['entry_key'])) {
            return;
        }
        
        global $db_record, $arfform, $arfcreatedentry, $arfform_params, $arrecordcontroller;

        $form = $arfform->getOne($_POST['form_id']);

        if (!$form) {
            return;
        }


        if (!$arfform_params) {
            $arfform_params = array();
        }


        $params = $arrecordcontroller->get_recordparams($form);

        $arfform_params[$form->id] = $params;

        if (!$arfcreatedentry) {
            $arfcreatedentry = array();
        }

        if (isset($arfcreatedentry[$_POST['form_id']]))
            return;

        $_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']] = isset($_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']]) ? $_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']] : '';

        $arferrormsg = "";
        $errors1 = array();

        if ( empty($errors) && $_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']] == "") {
            $arferr = array();
            $errors = $arrecordcontroller->internal_check_recaptcha();
            if ( !empty( $errors )) {

                $return["conf_method"] = "spamerror";
                $return["message"] = $errors;
                $arferrormsg = strip_tags($errors);
                $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
                if($_POST['form_submit_type'] == 1) {
                    echo json_encode($return);
                    exit;
                }
            }
        }

        unset($_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']]);

        $arfcreatedentry[$_POST['form_id']] = array('errors' => $errors);

        $submit_type = $arfsettings->form_submit_type;

        if (isset($_POST['using_ajax']) and strtolower(trim($_POST['using_ajax'])) == 'yes') {

            $form_id = $_POST['form_id'];

            $arf_errors = array();

            $arf_form_data = array();

            $values = $_POST;

            $arf_form_data = apply_filters('arf_populate_field_from_outside', $arf_form_data, $form_id, $values); 

            $arf_errors = apply_filters('arf_validate_form_outside_errors', $arf_errors, $form_id, $values, $arf_form_data);

            if (isset($arf_errors['arf_form_data']) and $arf_errors['arf_form_data']) {
                $arf_form_data = array_merge($arf_form_data, $arf_errors['arf_form_data']);
            }

            unset($arf_errors['arf_form_data']);

            if (count($arf_form_data) > 0) {
                foreach ($arf_form_data as $fieldid => $fieldvalue)
                    $_POST[$fieldid] = $fieldvalue;
            }

            
            $formRandomKey = isset($_POST['form_random_key']) ? $_POST['form_random_key'] : '';
            $validate = TRUE;
            $is_check_spam = true;

            if ($is_check_spam) {
                $validate = apply_filters('is_to_validate_spam_filter', $validate, $formRandomKey);
            }
            if (!$validate) {
                $return["conf_method"] = "spamerror";
                $message = '<div class="arf_form ar_main_div_{arf_form_id} arf_error_wrapper" id="arffrm_{arf_form_id}_container"><div class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . addslashes(esc_html__('Spam Detected', 'ARForms')) . '</div></div></div></div>';
                $return["message"] = $message;
                $return = apply_filters('arf_reset_built_in_captcha',$return,$_POST);
                echo json_encode($return);
                exit;
            }
        } else if( !isset($_REQUEST['using_ajax']) || (isset($_REQUEST['using_ajax']) && strtolower(trim($_POST['using_ajax'])) != 'yes') ){
            if( $submit_type != 0){
                $this->ajax_check_spam_filter();
            }
        }

        if( !isset($arf_errors)  ){
            $arf_errors = array();
        }
        if (empty($errors) && @count($arf_errors) == 0) {

            $_POST['arfentrycookie'] = 1;

            if ($params['action'] == 'create') {

                $form_entry = $arfform->arf_select_db_data( true, '', $MdlDb->entries, 'count(id)', 'WHERE form_id = %d', array( $_POST['form_id'] ), '', '', '', true  );
                $options = maybe_unserialize($form->options);
                
                if ( $form_entry >= $options['arf_restrict_max_entries'] && $options['arf_restrict_entry'] == 1) {
                    
                    if($_POST['form_submit_type'] == 1) {
                        $arf_res_msg_disp = '<div class="arf_form ar_main_div_' . $_POST['form_id'] . '" id="arffrm_' . $_POST['form_id'] . '_container"><div  class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $options['arf_res_msg_entry'] . '</div></div></div></div>';
                        $return["conf_method"] = 'message';
                        $return["message"] = $arf_res_msg_disp;
                        $return["hide_forms"] = true;
                        echo json_encode($return);
                        exit;
                    } else {
                        $redirect_msg = '<div class="arf_form ar_main_div_' . $_POST['form_id'] . '" id="arffrm_' . $_POST['form_id'] . '_container"><div  class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $options['arf_res_msg_entry'] . '</div></div></div></div>';
                        $return["redirect_msg"] = $redirect_msg;
                    }
                } else {
                    if (apply_filters('arfcontinuetocreate', true, $_POST['form_id']) and ! isset($arfcreatedentry[$_POST['form_id']]['entry_id'])) {
                        $arfcreatedentry[$_POST['form_id']]['entry_id'] = $db_record->create($_POST);
                    }
                }
            }
            
            $item_meta_values = isset($_POST['item_meta']) ? $_POST['item_meta'] : array();

            $item_meta_values = $db_record->create($_POST,true);
            
            do_action('arfentryexecute', $params, $errors, $form,$item_meta_values);
            unset($_POST['arfentrycookie']);
        } else {

            if ($arf_errors) {

                $return["conf_method"] = "validationerror";
                $return["message"] = $arf_errors;
                $arferrormsg = $arfsettings->failed_msg;
                $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
                if($_POST['form_submit_type'] == 1) {
                    echo json_encode($return);
                    exit;
                }
            }

            if($_POST['form_submit_type'] == 1) {
                exit;
            } else {
                do_shortcode("[ARForms id=" . $_POST['form_id'] . " arfsubmiterrormsg='".$arferrormsg."' ]");
            }

        }
        
        if (isset($_POST['using_ajax']) and $_POST['using_ajax'] == 'yes') {
            wp_cache_delete( 'arf_total_entries_counter_' . $_POST['form_id'] );
            echo do_shortcode("[ARForms id=" . $_POST['form_id'] . "]");
        }
    }

    function menu() {


        global $arfsettings, $armainhelper;


        if (current_user_can('administrator') and ! current_user_can('arfviewentries')) {

            global $wp_roles;

            $arfroles = $armainhelper->frm_capabilities();

            foreach ($arfroles as $arfrole => $arfroledescription) {


                if (!in_array($arfrole, array('arfviewforms', 'arfeditforms', 'arfdeleteforms', 'arfchangesettings', 'arfimportexport', 'arfviewpopupform'))) {
                    $wp_roles->add_cap('administrator', $arfrole);
                }
            }
        }


        add_submenu_page('ARForms', 'ARForms' . ' | ' . addslashes(esc_html__('Form Entries', 'ARForms')), addslashes(esc_html__('Form Entries', 'ARForms')), 'arfviewentries', 'ARForms-entries', array($this, 'route'));


        add_action('admin_head-' . 'ARForms' . '_page_ARForms-entries', array($this, 'head'));
    }

    function head() {


        global $style_settings, $armainhelper;


        $css_file = array($armainhelper->jquery_css_url($style_settings->arfcalthemecss));


        require(VIEWS_PATH . '/head.php');
    }

    function admin_js() {

        if (isset($_GET) and isset($_GET['page']) and ( 'ARForms-popups'==$_GET['page'] || $_GET['page'] == 'ARForms-entries' || $_GET['page'] == 'ARForms-entry-templates' or $_GET['page'] == 'ARForms-import' or $_REQUEST['page'] == "ARForms" && ((isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] == 'edit') || (isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] == 'new') || (isset($_REQUEST['arfaction']) && $_REQUEST['arfaction']) == 'duplicate' || (isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] == 'update')))) {

            if (!function_exists('wp_editor')) {


                add_action('admin_print_footer_scripts', 'wp_tiny_mce', 25);


                add_filter('tiny_mce_before_init', array($this, 'remove_fullscreen'));


                if (user_can_richedit()) {


                    wp_enqueue_script('editor');


                    wp_enqueue_script('media-upload');
                }


                wp_enqueue_script('common');


                wp_enqueue_script('post');
            }


            if ( $_GET['page'] == 'ARForms-entries' || $_REQUEST['page'] == "ARForms" && ($_REQUEST['arfaction'] == 'edit' || $_REQUEST['arfaction'] == 'new' || $_REQUEST['arfaction'] == 'duplicate' || $_REQUEST['arfaction'] == 'update')) {
                wp_enqueue_script('bootstrap-locale-js');
                wp_enqueue_script('bootstrap-datepicker');
            }
        }
    }

    function remove_fullscreen($init) {


        if (isset($init['plugins'])) {


            $init['plugins'] = str_replace('wpfullscreen,', '', $init['plugins']);


            $init['plugins'] = str_replace('fullscreen,', '', $init['plugins']);
        }

        return $init;
    }

    function register_scripts() {


        global $wp_scripts, $armainhelper, $arfversion;

        wp_register_script('bootstrap-locale-js', ARFURL . '/bootstrap/js/moment-with-locales.js', array('jquery'), $arfversion);

        wp_register_script('bootstrap-datepicker', ARFURL . '/bootstrap/js/bootstrap-datetimepicker.js', array('jquery'), $arfversion, true);
    }

    function add_js() {


        if (is_admin())
            return;


        global $arfsettings, $arfversion;


        if ($arfsettings->accordion_js) {


            wp_enqueue_script('jquery-ui-widget');


            wp_enqueue_script('jquery-ui-accordion', ARFURL . '/js/jquery.ui.accordion.js', array('jquery', 'jquery-ui-core'), $arfversion, true);
        }
    }

    function &filter_email_value($value, $meta, $entry, $atts = array()) {
        global $arffield;
        $field = $arffield->getOne($meta->field_id);
        if (!$field)
            return $value;
        $value = $this->filter_entry_display_value($value, $field, $atts);
        return $value;
    }

    function footer_js($preview = false, $is_print = false) {
        global $wp_version;
        $path = $_SERVER['REQUEST_URI'];
        $file_path = basename($path);

        if (!strstr($file_path, "post.php")) {


            global $arfversion, $arfforms_loaded, $arf_form_all_footer_js, $is_arf_preview, $arf_modal_loaded, $arfsettings,$footer_cl_logic, $arf_popup_forms_footer_js, $arf_autosaved_forms, $arformhelper;

            $is_multi_column_loaded = array();

            if (empty($arfforms_loaded)){
                return;
            }

            if ($is_print) {
                $print_style = 'wp_print_styles';
                $print_script = 'wp_print_scripts';
            } else {
                $print_style = 'wp_enqueue_style';
                $print_script = 'wp_enqueue_script';
            }

            $load_js_css = $arfsettings->arf_load_js_css;

            $arf_form_wise_script_data = '';

            foreach ($arfforms_loaded as $form) {

                if (!is_object($form))
                    continue;


                $wp_upload_dir = wp_upload_dir();
                if (is_ssl()) {
                    $upload_main_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/maincss');
                } else {
                    $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
                }
                $fid1 = $upload_main_url . '/maincss_' . $form->id . '.css';

                $print_script('jquery');

                $print_script('jquery-validation');

                $form->options = maybe_unserialize($form->options);


                if ((isset($form->options['font_awesome_loaded']) && $form->options['font_awesome_loaded'] ) || in_array('fontawesome', $load_js_css)) {
                    $print_style('arf-fontawesome-css');
                }

                if ((isset($form->options['tooltip_loaded']) && $form->options['tooltip_loaded']) || in_array('tooltip', $load_js_css)) {
                    $print_style('arf_tipso_css_front');
                    $print_script('arf_tipso_js_front');
                }
                if ((isset($form->options['arf_input_mask']) && $form->options['arf_input_mask']) || in_array('mask_input', $load_js_css)) {
                    $print_script('jquery-maskedinput');
                    $print_script('arforms_phone_intl_input');
                    $print_script('arforms_phone_utils');
                }
                
                if ((isset($form->options['arf_number_animation']) && $form->options['arf_number_animation'] ) || in_array('animate_number', $load_js_css)) {
                    $print_script('animate-numbers');
                }
                if ((isset($form->options['arf_page_break_wizard']) && $form->options['arf_page_break_wizard'] ) || in_array('page_break_wizard', $load_js_css)) {
                    if( version_compare($wp_version, '4.2', '<') ){
                        $print_script('jquery-ui-custom');
                    } else {
                        $print_script('jquery-ui-core');
                    }
                    $print_script('jquery-effects-slide');
                }
                if ((isset($form->options['arf_page_break_survey']) && $form->options['arf_page_break_survey'] ) || in_array('page_break_survey', $load_js_css)) {
                    if( version_compare($wp_version, '4.2', '<') ){
                        $print_script('jquery-ui-custom');
                        $print_script('jquery-ui-widget-custom');
                    } else {
                        $print_script('jquery-ui-core');
                        $print_script('jquery-ui-widget');
                    }
                    $print_script('jquery-effects-slide');
                }
                if ((isset($form->options['arf_autocomplete_loaded']) && $form->options['arf_autocomplete_loaded']) || in_array('autocomplete', $load_js_css)) {
                    $print_script('bootstrap-typeahead-js');
                }

                if ((isset($form->options['arf_multiselect_loaded']) && $form->options['arf_multiselect_loaded']) || in_array('arf_multiselect', $load_js_css)) {
                    $print_style('arf_selectpicker');
                    $print_script('arf_selectpicker');
                }


                $loaded_field = isset($form->options['arf_loaded_field']) ? $form->options['arf_loaded_field'] : array();

                if (in_array('select', $loaded_field) || in_array('arf_multiselect', $loaded_field) || in_array('dropdown', $load_js_css) ) {
                    $print_script( 'arf_selectpicker' );
                    $print_style( 'arf_selectpicker' );
                }
                if (in_array('file', $loaded_field) || in_array('file', $load_js_css)) {
                    $print_style('arf-filedrag');
                    $print_script('filedrag');
                }
                if (in_array('time', $loaded_field) || in_array('date', $loaded_field) || in_array('date_time', $load_js_css)) {

                    $css_file = ARFURL . '/bootstrap/css/bootstrap-datetimepicker.css';
                    $print_style('form_custom_css-default_theme', $css_file, array(), $arfversion);
                    $print_script('bootstrap-locale-js');
                    $print_style('arfbootstrap-datepicker-css');
                    $print_script('bootstrap-datepicker');
                }

                if (in_array('phone', $loaded_field) && isset( $form->options['arf_input_mask']) && $form->options['arf_input_mask'] == 1 ) {
                    $print_style('arfdisplayflagiconcss');
                }

                if (in_array('break', $loaded_field) && isset( $form->form_css['arfsettimer'] ) && $form->form_css['arfsettimer'] == 1) {
                    $print_script('arfpagebreaktimer');
                }

                if (in_array('slider', $loaded_field) || in_array('arfslider', $loaded_field) || in_array('slider', $load_js_css)) {
                    $print_script('nouislider');
                    $print_style('nouislider');
                }

                if ((in_array('colorpicker', $loaded_field) && $form->options['arf_advance_colorpicker'] == 1) || in_array('colorpicker', $load_js_css)) {
                    $print_style('arf-fontawesome-css');
                    $print_script('arf_js_color');
                }

                if ((in_array('colorpicker', $loaded_field) && $form->options['arf_normal_colorpicker'] == 1) || in_array('colorpicker', $load_js_css)) {
                    $print_script('arf-colorpicker-basic-js');
                }

                do_action('wp_arf_footer', $loaded_field);
                if( !is_admin() ){
                    $print_script('wp-hooks');
                    $print_script('arforms');
                }
                $print_script('arf-conditional-logic-js');
                
                if ($arf_modal_loaded) {
                    $print_script('arf-modal-js');
                }

                if ((isset($form->options['google_captcha_loaded']) && $form->options['google_captcha_loaded'] ) || in_array('captcha', $load_js_css)) {
                    $lang = $arfsettings->re_lang;
                    echo '<script type="text/javascript" data-cfasync="false" src="https://www.google.com/recaptcha/api.js?hl=' . $lang . '&onload=render_arf_captcha&render=explicit"></script>';
                }
                $arf_file_path = $arformhelper->manage_uploaded_file_path( $form->id );
                $arf_form_wise_script_data .= "__ARF_FILE_PATH_" .$form->id. " ='" . $arf_file_path['url'] . "';\n";
                $arf_form_wise_script_data .= 'jQuery("#arffrm_' . $form->id . '_container").find("form").find(".arfformfield").each(function () {
                    var data_view = jQuery(this).attr("data-view");
                    if (data_view == "arf_disable") {
                        var data_type = jQuery(this).attr("data-type");
                        arf_field_disable(jQuery(this), data_type);
                    }
                });';
            }

            $arf_footer_script_data = 'if (typeof (__ARFERR) != "undefined") {
                        var file_error = __ARFERR;
                    } else {
                        var file_error = "'.addslashes(esc_html__('Sorry, this file type is not permitted for security reasons.', 'ARForms')).'";
                    }';

            $arf_footer_script_data .= "__ARFMAINURL='" . ARFSCRIPTURL . "';\n";

            $arf_footer_script_data .= "__ARFERR='" . addslashes(esc_html__('Sorry, this file type is not permitted for security reasons.', 'ARForms')) . "';\n";

            $arf_footer_script_data .= "__ARFMAXFILEUPLOAD ='" . addslashes(esc_html__('You have reached maximum limit.', 'ARForms')) . "';\n";

            $arf_footer_script_data .= "__ARFAJAXURL='" . admin_url('admin-ajax.php') . "';\n";

            $arf_footer_script_data .= "__ARFSTRRNTH_INDICATOR='" . addslashes(esc_html__('Strength indicator', 'ARForms')) . "';\n";

            $arf_footer_script_data .= "__ARFSTRRNTH_SHORT='" . addslashes(esc_html__('Short', 'ARForms')) . "';\n";

            $arf_footer_script_data .= "__ARFSTRRNTH_BAD='" . addslashes(esc_html__('Bad', 'ARForms')) . "';\n";

            $arf_footer_script_data .= "__ARFSTRRNTH_GOOD='" . addslashes(esc_html__('Good', 'ARForms')) . "';\n";

            $arf_footer_script_data .= "__ARFSTRRNTH_STRONG='" . addslashes(esc_html__('Strong', 'ARForms')) . "';\n";

            $arf_footer_script_data .= "__ARF_NO_FILE_SELECTED='" . addslashes(esc_html__('No file selected', 'ARForms')) . "';\n";

            $arf_footer_script_data .= $arf_form_wise_script_data;

            $arf_inline_script  = '';

            
            if( isset($footer_cl_logic) && !empty($footer_cl_logic) ){
                $arf_inline_script .= "function arf_footer_cl_logic_call(){";
                    foreach( $footer_cl_logic as $cl_logic ){
                        $arf_inline_script .= "eval(" . $cl_logic . ");";
                    }
                $arf_inline_script .= "}";
            }

            $arf_inline_script .= 'function arf_initialize_control_js(){';
                $arf_form_all_footer_js = apply_filters('arf_footer_javascript_from_outside',$arf_form_all_footer_js);
                $arf_inline_script .= "setTimeout(function(){";
                    $arf_inline_script .= $arf_form_all_footer_js;
                $arf_inline_script .= "},100);";
                $arf_inline_script .= 'setTimeout(function(){';
                    if( !empty( $arf_autosaved_forms ) ){
                        foreach( $arf_autosaved_forms as $asfrm_data ){
                            $arf_inline_script .= 'arf_retrieve_data_from_storage("'. $asfrm_data . '");';
                        }
                    }
                $arf_inline_script .= '},100);';
            $arf_inline_script .= '}';

            $arf_inline_script .= 'document.addEventListener("readystatechange", (event)=>{';

                $arf_inline_script .= 'if( document.readyState == "complete" ){';
                    
                    if (isset($arf_form_all_footer_js)) {
                        if( !isset($arfsettings->arfmainformloadjscss) || $arfsettings->arfmainformloadjscss != 1 ){                
                            $arf_inline_script .= 'arf_initialize_control_js();';
                            if( isset($footer_cl_logic) && !empty($footer_cl_logic) ){
                                $arf_inline_script .= 'arf_footer_cl_logic_call();';
                            }
                        }

                        $arf_inline_script .= $arf_popup_forms_footer_js;
                    }

                    if( $arfsettings->arfmainformloadjscss && $arfsettings->arfmainformloadjscss == 1 ){
                        $arf_inline_script .= 'setTimeout(function(){';
                            $arf_inline_script .= 'arf_initialize_control_js();';
                            $arf_inline_script .= 'arf_initialize_form_control_onready();';
                            $arf_inline_script .= 'if(typeof arf_init_signature_class == "function") {
                                    arf_init_signature_class();
                            }';
                            if( isset($footer_cl_logic) && !empty($footer_cl_logic) ){
                                $arf_inline_script .= 'arf_footer_cl_logic_call();';
                            }
                            if( !empty( $arf_autosaved_forms ) ){
                                foreach( $arf_autosaved_forms as $asfrm_data ){
                                    $arf_inline_script .= 'arf_retrieve_data_from_storage("'. $asfrm_data . '");';
                                }
                            }
                        $arf_inline_script .= '},1000);';
                    }

                $arf_inline_script .= '}';

            $arf_inline_script .= '});';

            if( 1 == $preview || '1' == $preview ){
                echo '<script defer id="arf_temp_script" type="text/javascript">';
                    echo $arf_footer_script_data;
                    echo $arf_inline_script;
                echo '</script>';
            } else {
                wp_add_inline_script( 'arforms', $arf_footer_script_data, 'before');
                wp_add_inline_script( 'arforms', $arf_inline_script, 'after' );
            }

            return;
        }
    }

    function list_entries() {


        $params = $this->get_params();


        return $this->display_list($params);
    }

    function create() {


        global $arfform, $db_record;


        $params = $this->get_params();


        if ($params['form'])
            $form = $arfform->getOne($params['form']);


        $errors = $db_record->validate($_POST);


        if (count($errors) > 0) {


            $this->get_new_vars($errors, $form);
        } else {


            if (isset($_POST['arfpageorder' . $form->id])) {


                $this->get_new_vars('', $form);
            } else {


                $_SERVER['REQUEST_URI'] = str_replace('&arfaction=new', '', $_SERVER['REQUEST_URI']);


                $record = $db_record->create($_POST);


                if ($record)
                    $message = addslashes(esc_html__('Entry is Successfully Created', 'ARForms'));


                $this->display_list($params, $message, '', 1);
            }
        }
    }

    function destroy() {


        if (!current_user_can('arfdeleteentries')) {


            global $arfsettings;


            wp_die($arfsettings->admin_permission);
        }


        global $db_record, $arfform;


        $params = $this->get_params();


        if ($params['form'])
            $form = $arfform->getOne($params['form']);


        $message = '';


        if ($db_record->destroy($params['id']))
            $message = addslashes(esc_html__('Entry is Successfully Deleted', 'ARForms'));


        $this->display_list($params, $message, '', 1);
    }

    function destroy_all() {


        if (!current_user_can('arfdeleteentries')) {


            global $arfsettings;


            wp_die($arfsettings->admin_permission);
        }


        global $db_record, $arfform, $MdlDb;


        $params = $this->get_params();


        $message = '';


        $errors = array();


        if ($params['form']) {


            $form = $arfform->getOne($params['form']);


            $entry_ids = $MdlDb->get_col($MdlDb->entries, array('form_id' => $form->id));


            foreach ($entry_ids as $entry_id) {


                if ($db_record->destroy($entry_id))
                    $message = addslashes(esc_html__('Entries were Successfully Destroyed', 'ARForms'));
            }
        }else {


            $errors = addslashes(esc_html__('No entries were specified', 'ARForms'));
        }


        $this->display_list($params, $message, '', 0, $errors);
    }

    function bulk_actions($action = 'list-form') {


        global $db_record, $arfsettings, $armainhelper;


        $params = $this->get_params();


        $errors = array();


        $bulkaction = '-1';


        if ($action == 'list-form') {


            if ($_REQUEST['bulkaction'] != '-1')
                $bulkaction = $_REQUEST['bulkaction'];


            else if ($_POST['bulkaction2'] != '-1')
                $bulkaction = $_REQUEST['bulkaction2'];
        }else {


            $bulkaction = str_replace('bulk_', '', $action);
        }


        $items = $armainhelper->get_param('item-action', '');


        if (empty($items)) {


            $errors[] = addslashes(esc_html__('Please select one or more records.', 'ARForms'));
        } else {


            if (!is_array($items))
                $items = explode(',', $items);


            if ($bulkaction == 'delete') {


                if (!current_user_can('arfdeleteentries')) {


                    $errors[] = $arfsettings->admin_permission;
                } else {


                    if (is_array($items)) {


                        foreach ($items as $entry_id)
                            $db_record->destroy($entry_id);
                    }
                }
            } else if ($bulkaction == 'csv') {


                if (!current_user_can('arfviewentries'))
                    wp_die($arfsettings->admin_permission);





                global $arfform;


                $form_id = $params['form'];


                if ($form_id) {


                    $form = $arfform->getOne($form_id);
                } else {


                    $form = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name', ' LIMIT 1');


                    if ($form)
                        $form_id = $form->id;
                    else
                        $errors[] = addslashes(esc_html__('No form is found', 'ARForms'));
                }


                if ($form_id and is_array($items)) {


                    echo '<script type="text/javascript" data-cfasync="false">window.onload=function(){location.href="' . site_url() . '/index.php?plugin=ARForms&controller=entries&form=' . $form_id . '&arfaction=csv&entry_id=' . implode(',', $items) . '";}</script>';
                }
            }
        }


        $this->display_list($params, '', false, false, $errors);
    }

    function show_form_popup($id = '', $key = '', $title = false, $description = false, $desc = '', $type = 'link', $modal_height = '540', $modal_width = '800', $position = 'left', $btn_angle = '0', $bgcolor = '', $txtcolor = '', $open_inactivity = '1', $open_scroll = '10', $open_delay = '0', $overlay = '0.6', $is_close_link = 'yes', $modal_bgcolor = '#000000') {

        global $arfform, $user_ID, $arfsettings, $post, $wpdb, $armainhelper, $arrecordcontroller, $arformcontroller, $MdlDb;

        $func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($id), $id);

        if ($id)
            $form = $arfform->getOne((int) $id);

        else if ($key)
            $form = $arfform->getOne($key);

        $form = apply_filters('arfpredisplayform', $form);


        if (( isset($form) and ! empty($form) ) and ( @$form->is_template or @ $form->status == 'draft')) {
            return addslashes(esc_html__('Please select a valid form', 'ARForms'));
        } else if (!$form or ( ($form->is_template or $form->status == 'draft') and ! isset($_GET) and ! isset($_GET['form']) )) {
            return addslashes(esc_html__('Please select a valid form', 'ARForms'));
        } else if ($form->is_loggedin && !$user_ID) {
            global $arfsettings;
            return do_shortcode($arfsettings->login_msg);
        }

        return $arrecordcontroller->get_form_popup(VIEWS_PATH . '/view-modal.php', $form, $title, $description, $desc, $type, $modal_height, $modal_width, $position, $btn_angle, $bgcolor, $txtcolor, $open_inactivity, $open_scroll, $open_delay, $overlay, $is_close_link, $modal_bgcolor, $func_val);
    }

    function get_form_popup($filename, $form, $title, $description, $desc, $type, $modal_height, $modal_width, $position, $btn_angle, $bgcolor, $txtcolor, $open_inactivity, $open_scroll, $open_delay, $overlay, $is_close_link, $modal_bgcolor, $func_val = 'true') {

        wp_print_styles('arfdisplaycss');
        wp_print_scripts('jquery-validation');

        if (is_file($filename)) {

            $contents = '';
            ob_start();

            if ($bgcolor == '') {

                if ($type == 'fly') {

                    $bgcolor = ($position == 'left') ? '#2d6dae' : '#8ccf7a';
                } else if ($type == 'sticky') {

                    $bgcolor = ( in_array($position, array('right', 'bottom', 'left'))) ? '#1bbae1' : '#93979d';
                }
            }

            if ($txtcolor == '')
                $txtcolor = '#ffffff';





            include $filename;


            $contents .= ob_get_contents();


            ob_end_clean();


            return $contents;
        }


        return false;
    }

    function process_update_entry($params, $errors, $form, $final_input_meta) {

        global $db_record, $arfsavedentries, $arfcreatedentry, $arfsettings;

        $form->options = stripslashes_deep(maybe_unserialize($form->options));

        if ($params['action'] == 'update' and in_array((int) $params['id'], (array) $arfsavedentries))
            return;

        if ($params['action'] == 'create' and isset($arfcreatedentry[$form->id]) and isset($arfcreatedentry[$form->id]['entry_id']) and is_numeric($arfcreatedentry[$form->id]['entry_id'])) {

            $entry_id = $params['id'] = $arfcreatedentry[$form->id]['entry_id'];
            

            $conf_method = apply_filters('arfformsubmitsuccess', 'message', $form, $form->options);
            
            $return_script = '';
            $return["script"] = apply_filters('arf_after_submit_sucess_outside',$return_script,$form);

            $arf_redirect_url_to = isset($form->options['arf_redirect_url_to']) ? $form->options['arf_redirect_url_to'] : 'same_tab';
            if ($conf_method == 'redirect') {

                $success_url = apply_filters('arfcontent', $form->options['success_url'], $form, $entry_id);
                if ($success_url == false) {
                    global $arfsettings;
                    $message = '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container"><div  id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $arfsettings->success_msg . '</div></div></div>';
                    $message .= do_action('arf_after_success_massage', $form);
                    $message .= '</div>';
                    $return["conf_method"] = "message";
                    $return["message"] = $message;
                    $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
                    if($arfsettings->form_submit_type == 1) {
                        echo json_encode($return);
                        exit;
                    }
                } else if ($arfsettings->form_submit_type == 1) {
                    if(isset($form->options["arf_data_with_url"]) && $form->options["arf_data_with_url"] == 1) {
                        $return["conf_method"] = 'message';
                        $return["message"] = $this->generate_redirect_form($form, $success_url, $form->options["arf_data_with_url_type"],$final_input_meta,$arf_redirect_url_to);
                        if ( 'new_tab' == $arf_redirect_url_to || 'new_window' == $arf_redirect_url_to ) {
                            $return['conf_method2'] = 'addon';
                            $redirect_msg = '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container"><div  id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $arfsettings->success_msg . '</div></div></div>';
                            $redirect_msg .= do_action('arf_after_success_massage', $form);
                            $redirect_msg .= '</div>';
                            $return["redirect_msg"] = $redirect_msg;
                        }
                    } else {
                        $return["conf_method"] = "redirect";
                        $return["message"] = $success_url;
                        if ( 'new_tab' == $arf_redirect_url_to || 'new_window' == $arf_redirect_url_to ) {
                            $redirect_msg = '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container"><div  id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $arfsettings->success_msg . '</div></div></div>';
                            $redirect_msg .= do_action('arf_after_success_massage', $form);
                            $redirect_msg .= '</div>';
                            $return["redirect_msg"] = $redirect_msg;
                        }
                    }
                    $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
                    echo json_encode($return);
                    exit;
                } else {
                    if(isset($form->options["arf_data_with_url"]) && $form->options["arf_data_with_url"] == 1) {
                        echo $redirection_form = $this->generate_redirect_form($form, $success_url, $form->options["arf_data_with_url_type"],$final_input_meta, $arf_redirect_url_to);
                        
                        if ( 'new_tab' == $arf_redirect_url_to || 'new_window' == $arf_redirect_url_to ) {
                            $redirect_msg = '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container"><div  id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $arfsettings->success_msg . '</div></div></div>';
                            $redirect_msg .= do_action('arf_after_success_massage', $form);
                            $redirect_msg .= '</div>';

                            return $return_redirect_msg;
                        }
                    }
                    else {
                        if ( 'new_tab' == $arf_redirect_url_to ) { 
                            $redirect_url_to = "window.open('".$success_url."');"; 
                        } elseif ( 'new_window' == $arf_redirect_url_to ) {
                            $redirect_url_to = "window.open('".$success_url."', '_blank', 'height=700, width=700');";
                        } elseif ( 'same_tab' == $arf_redirect_url_to ) {
                            $redirect_url_to = "window.location='" . $success_url . "';";
                        }

                        echo "<script type='text/javascript' data-cfasync='false'> ".$redirect_url_to." </script>";

                        if ( 'new_tab' == $arf_redirect_url_to || 'new_window' == $arf_redirect_url_to ) {
                            $return_redirect_msg = '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container"><div  id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $arfsettings->success_msg . '</div></div></div>';
                            $return_redirect_msg .= do_action('arf_after_success_massage', $form);
                            $return_redirect_msg .= '</div>';
                            
                            return $return_redirect_msg;
                        }
                    }
                    exit;
                }
            }
        } else if ($params['action'] == 'destroy') {

            $this->ajax_destroy($form->id, false, false);
        }
    }

    function generate_redirect_form($form_data, $url, $method, $item_meta_values, $arf_redirect_url_to) {

        global $wpdb, $MdlDb,$arfieldhelper,$arfsettings;

        if( $method == 'GET' ){

            $key_name = isset($form_data->options['arf_field_key_name']) ? $form_data->options['arf_field_key_name']: '' ;

            $post_fields = isset($form_data->options['arf_select_post_fields']) ? $form_data->options['arf_select_post_fields']: '';

            $post_fields_arr = arf_json_decode($post_fields, true);

            $key_name_arr = arf_json_decode($key_name, true);

            $joiner = '?';

            if( preg_match('/\?/', $url) ){
                $joiner = '&';
            }

            $final_url = $url . $joiner;

            foreach ($post_fields_arr as $key => $value) { 
                if ($value == 1) {
                    $new_val = $key;
                    foreach ($key_name_arr as $key1 => $value1) {
                        if ($key1 == $new_val) {
                           $key_nm_arr[$key1] = $value1;
                        }
                    }
                }
            }

            $query_param = '';

            foreach( $item_meta_values as $field_id => $field_value ){

                $field_type = $wpdb->get_row($wpdb->prepare("SELECT id, type, name, field_options FROM `".$MdlDb->fields."` WHERE form_id = %d AND id = %d",$form_data->id, $field_id));
                if( !isset( $field_type ) || empty( $field_type ) ){
                    continue;
                }
                $redirect_fild_option = json_decode($field_type->field_options, true);
                
                if( isset($field_type) && $field_type->id != '' ){
                    $act_exclude = array('divider', 'section', 'break', 'captcha', 'imagecontrol', 'confirm_email', 'confirm_password', 'signature', 'file');
                    if( in_array($field_type->type, $act_exclude) ) { continue; }
                    if(!empty($key_nm_arr)) {
                        foreach ($key_nm_arr as $kfkey => $kfval) {
                            if ($kfkey == $field_id) {
                                if( isset( $redirect_fild_option['parent_field_type'] ) && $redirect_fild_option['parent_field_type'] == 'arf_repeater' ){
                                    if( 'checkbox' == $field_type->type || 'arf_multiselect' == $field_type->type){
                                        $checked_data = explode('[ARF_JOIN]', $field_value );
                                        $n = 0;
                                        foreach( $checked_data as $checked_value ){
                                            $checkval = explode('!|!',$checked_value);
                                            $checked_val = $checkval[0];
                                            $checked_val_arr = arf_json_decode( $checked_val, true );
                                            if( '' == $kfval ){
                                                $inner_key = "item_meta_".$field_id."[".$n."]";
                                            } else {
                                                $inner_key = $kfval."[".$n."]";
                                            }

                                            foreach( $checked_val_arr as $checked_value ){
                                                $query_param .= $inner_key.'[]='.urlencode( $checked_value ).'&';
                                            }
                                            $n++;   
                                        }
                                    } else {
                                        $repeater_field_id = $redirect_fild_option['parent_field'];
                                        $kfval = ('' == $kfval) ? "item_meta_".$field_id : $kfval;
                                        $field_value_arr = explode( '[ARF_JOIN]', $field_value );
                                        foreach( $field_value_arr as $field_val ){
                                            $query_param .= $kfval.'[]=' . urlencode( $field_val );
                                        }
                                    }
                                } else {
                                    $kfval = ('' == $kfval) ? "item_meta_".$field_id : $kfval;
                                    
                                    if( $field_type->type == 'checkbox' || $field_type->type == 'arf_multiselect' ){
                                        if( !is_array( $field_value ) ){
                                            $field_value = explode(',', $field_value );
                                        }
                                        foreach( $field_value as $checkbox_val){
                                            $query_param .= $kfval . '[]=' . urlencode( $checkbox_val ).'&';
                                        }
                                    } else if( $field_type->type == 'matrix' ){

                                        $field_opts = arf_json_decode( $field_type->field_options, true );

                                        foreach( $field_opts['rows'] as $k => $val ){
                                            if( !empty( $field_value[$k] ) ){
                                                $query_param .= $kfval.'['.$k.']='.urlencode( $field_value[$k] ).'&';
                                            } else {
                                                $query_param .= $kfval.'['.$k.']=&';
                                            }
                                        }
                                    } else {
                                        if( $field_type->type == 'html' ){
                                            $arfdecimal_separator = $arfsettings->decimal_separator;
                                            if($arfdecimal_separator == ',')
                                            {
                                                $field_value = str_replace('.', ',', $field_value);
                                            }
                                            else if($arfdecimal_separator == '.')
                                            {
                                                $field_value = $field_value;
                                            }
                                            else{
                                                $field_value = round($field_value);   
                                            }
                                        }
                                        $query_param .= $kfval . '=' . urlencode( $field_value ).'&';
                                    }
                                }
                            }
                        }    
                    } else {
                        $kfval = "item_meta_".$field_id;
                        if( isset( $redirect_fild_option['parent_field_type'] ) && $redirect_fild_option['parent_field_type'] == 'arf_repeater' ){
                            if( 'checkbox' == $field_type->type || 'arf_multiselect' == $field_type->type ){
                                $checked_data = explode('[ARF_JOIN]', $field_value );
                                $n = 0;
                                foreach( $checked_data as $checked_value ){
                                    $checkval = explode('!|!',$checked_value);
                                    $checked_val = $checkval[0];
                                    $checked_val_arr = arf_json_decode( $checked_val, true );
                                    $kfval = "item_meta_".$field_id."[".$n."]";
                                    foreach( $checked_val_arr as $checked_value ){
                                        $query_param .= $kfval.'[]=' . urlencode( $checked_value ).'&';
                                    }
                                    $n++;
                                }
                            } else {
                                $repeater_field_id = $redirect_fild_option['parent_field'];
                                $kfval = 'item_meta_'.$field_id.'[]';
                                $field_value_arr = explode( '[ARF_JOIN]', $field_value );
                                foreach( $field_value_arr as $field_val ){
                                    $query_param .= $kfval.'=' . urlencode( $field_val ) . '&';
                                }
                            }
                        } else {
                            if( $field_type->type == 'checkbox' || $field_type->type == 'arf_multiselect' ){
                                if( !is_array( $field_value ) ){
                                    $field_value = explode(',', $field_value );
                                }
                                foreach( $field_value as $checkbox_val){
                                    $query_param .= $kfval.'[]='. urlencode( $checkbox_val ).'&';
                                }
                            } else if( $field_type->type == 'matrix' ){

                                $field_opts = arf_json_decode( $field_type->field_options, true );

                                foreach( $field_opts['rows'] as $k => $val ){
                                    if( !empty( $field_value[$k] ) ){
                                        $query_param .= $kfval . '['.$k.']=' . urlencode( $field_value[$k] ).'&';
                                    } else {
                                        $query_param .= $kfval .'[' . $k .']=&';
                                    }
                                }
                            } else {
                                if( $field_type->type == 'html' ){
                                    $arfdecimal_separator = $arfsettings->decimal_separator;
                                    if($arfdecimal_separator == ',')
                                    {
                                        $field_value = str_replace('.', ',', $field_value);
                                    }
                                    else if($arfdecimal_separator == '.')
                                    {
                                        $field_value = $field_value;
                                    }
                                    else{
                                        $field_value = round($field_value);   
                                    }
                                }
                                $query_param .= $kfval.'='.urlencode( $field_value ).'&';
                            }
                        }
                    }
                    
                }
            }
            $final_query_param = trim( $query_param );
            $final_query_param = rtrim( $final_query_param , '&' );

            $final_url .= $final_query_param;


            if( !empty( $arf_redirect_url_to ) && 'new_tab' == $arf_redirect_url_to ){
                $redirect_form = '<script type="text/javascript" data-cfasync="false">window.open("'.$final_url.'");</script>';
            } else if( !empty( $arf_redirect_url_to ) && 'new_window' == $arf_redirect_url_to ){
                $redirect_form = '<script type="text/javascript" data-cfasync="false">window.open("'.$final_url.'", "_blank", "height=700, width=700");</script>';                
            } else {
                $redirect_form = '<script type="text/javascript" data-cfasync="false">window.location.href="'.$final_url.'"</script>';
            }


        } else {

            $form_attrs = "";
            if( !empty( $arf_redirect_url_to ) && 'new_tab' == $arf_redirect_url_to ){
                $form_attrs = " target='blank' ";
            }

            $redirect_form = "<form method='".strtoupper($method)."' action='". $url ."' name='arf_new_redirect_form' id='arf_new_redirect_form' ".$form_attrs.">";

            $key_name = isset($form_data->options['arf_field_key_name']) ? $form_data->options['arf_field_key_name']: '' ;

            $post_fields = isset($form_data->options['arf_select_post_fields']) ? $form_data->options['arf_select_post_fields']: '' ;

            $post_fields_arr = arf_json_decode($post_fields, true);

            $key_name_arr = arf_json_decode($key_name, true);

            foreach ($post_fields_arr as $key => $value) { 
                if ($value == 1) {
                    $new_val = $key;
                    foreach ($key_name_arr as $key1 => $value1) {
                        if ($key1 == $new_val) {
                           $key_nm_arr[$key1] = $value1;
                        }
                    }
                }
            }
            
            foreach( $item_meta_values as $field_id => $field_value ){

                $field_type = $wpdb->get_row($wpdb->prepare("SELECT id, type, name, field_options FROM `".$MdlDb->fields."` WHERE form_id = %d AND id = %d",$form_data->id, $field_id));
                if( !isset( $field_type ) || empty( $field_type ) ){
                    continue;
                }
                $redirect_fild_option = json_decode($field_type->field_options, true);
                
                if( isset($field_type) && $field_type->id != '' ){
                    $act_exclude = array('divider', 'section', 'break', 'captcha', 'imagecontrol', 'confirm_email', 'confirm_password', 'signature', 'file');
                    if( in_array($field_type->type, $act_exclude) ) { continue; }
                    if(!empty($key_nm_arr)) {
                        foreach ($key_nm_arr as $kfkey => $kfval) {
                            if ($kfkey == $field_id) {
                                if( isset( $redirect_fild_option['parent_field_type'] ) && $redirect_fild_option['parent_field_type'] == 'arf_repeater' ){
                                    if( 'checkbox' == $field_type->type || 'arf_multiselect' == $field_type->type){
                                        $checked_data = explode('[ARF_JOIN]', $field_value );
                                        $n = 0;
                                        foreach( $checked_data as $checked_value ){
                                            $checkval = explode('!|!',$checked_value);
                                            $checked_val = $checkval[0];
                                            $checked_val_arr = arf_json_decode( $checked_val, true );
                                            if( '' == $kfval ){
                                                $inner_key = "item_meta_".$field_id."[".$n."]";
                                            } else {
                                                $inner_key = $kfval."[".$n."]";
                                            }
                                            foreach( $checked_val_arr as $checked_value ){
                                                $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$inner_key}[]' value='".$checked_value."' />";
                                            }
                                            $n++;   
                                        }
                                    } else {
                                        $repeater_field_id = $redirect_fild_option['parent_field'];
                                        $kfval = ('' == $kfval) ? "item_meta_".$field_id : $kfval;
                                        $field_value_arr = explode( '[ARF_JOIN]', $field_value );
                                        foreach( $field_value_arr as $field_val ){
                                            $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}[]' value='".$field_val."' />";
                                        }
                                    }
                                } else {
                                    $kfval = ('' == $kfval) ? "item_meta_".$field_id : $kfval;
                                    if( $field_type->type == 'checkbox' || $field_type->type == 'arf_multiselect' ){
                                        if( !is_array( $field_value ) ){
                                            $field_value = explode(',', $field_value );
                                        }
                                        foreach( $field_value as $checkbox_val){
                                            $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}[]' value='{$checkbox_val}' />";
                                        }
                                    } else if( $field_type->type == 'matrix' ){

                                        $field_opts = arf_json_decode( $field_type->field_options, true );

                                        foreach( $field_opts['rows'] as $k => $val ){
                                            if( !empty( $field_value[$k] ) ){
                                                $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}[{$k}]' value='{$field_value[$k]}' />";
                                            } else {
                                                $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}[{$k}]' value='' />";
                                            }
                                        }
                                    } else {
                                        if( $field_type->type == 'html' ){
                                            $arfdecimal_separator = $arfsettings->decimal_separator;
                                            if($arfdecimal_separator == ',')
                                            {
                                                $field_value = str_replace('.', ',', $field_value);
                                            }
                                            else if($arfdecimal_separator == '.')
                                            {
                                                $field_value = $field_value;
                                            }
                                            else{
                                                $field_value = round($field_value);   
                                            }
                                        }
                                        $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}' value='{$field_value}' />";
                                    }
                                }
                            }
                        }    
                    } else {
                        $kfval = "item_meta_".$field_id;
                        if( isset( $redirect_fild_option['parent_field_type'] ) && $redirect_fild_option['parent_field_type'] == 'arf_repeater' ){
                            if( 'checkbox' == $field_type->type || 'arf_multiselect' == $field_type->type ){
                                $checked_data = explode('[ARF_JOIN]', $field_value );
                                $n = 0;
                                foreach( $checked_data as $checked_value ){
                                    $checkval = explode('!|!',$checked_value);
                                    $checked_val = $checkval[0];
                                    $checked_val_arr = arf_json_decode( $checked_val, true );
                                    $kfval = "item_meta_".$field_id."[".$n."]";
                                    foreach( $checked_val_arr as $checked_value ){
                                        $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}[]' value='".$checked_value."' />";
                                    }
                                    $n++;
                                }
                            } else if( $field_type->type == 'matrix' ){

                                $field_opts = arf_json_decode( $field_type->field_options, true );

                                foreach( $field_opts['rows'] as $k => $val ){
                                    if( !empty( $field_value[$k] ) ){
                                        $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}[{$k}]' value='{$field_value[$k]}' />";
                                    } else {
                                        $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}[{$k}]' value='' />";
                                    }
                                }
                            } else {
                                $repeater_field_id = $redirect_fild_option['parent_field'];
                                $kfval = 'item_meta_'.$field_id.'[]';
                                $field_value_arr = explode( '[ARF_JOIN]', $field_value );
                                foreach( $field_value_arr as $field_val ){
                                    $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}' value='".$field_val."' />";
                                }
                            }
                        } else {
                            if( $field_type->type == 'checkbox' || $field_type->type == 'arf_multiselect' ){
                                if( !is_array( $field_value ) ){
                                    $field_value = explode(',', $field_value );
                                }
                                foreach( $field_value as $checkbox_val){
                                    $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}[]' value='{$checkbox_val}' />";
                                }
                            } else if( $field_type->type == 'matrix' ){

                                $field_opts = arf_json_decode( $field_type->field_options, true );

                                foreach( $field_opts['rows'] as $k => $val ){
                                    if( !empty( $field_value[$k] ) ){
                                        $redirect_form .= '<input type="hidden" data-type="' . $field_type->type . '" name="' . $kfval.'['.$k.']' . '" value="' . $field_value[$k] . '" />';
                                    } else {
                                        $redirect_form .= '<input type="hidden" data-type="' . $field_type->type . '" name="' . $kfval.'['.$k.']' . '" value="" />';
                                    }
                                }
                            } else {
                                if( $field_type->type == 'html' ){
                                    $arfdecimal_separator = $arfsettings->decimal_separator;
                                    if($arfdecimal_separator == ',')
                                    {
                                        $field_value = str_replace('.', ',', $field_value);
                                    }
                                    else if($arfdecimal_separator == '.')
                                    {
                                        $field_value = $field_value;
                                    }
                                    else{
                                        $field_value = round($field_value);   
                                    }
                                }
                                $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='{$kfval}' value='{$field_value}' />";
                            }
                        }
                    }
                    
                }
            }


            $redirect_form .= "</form>";
            if( !empty( $arf_redirect_url_to ) && 'new_window' == $arf_redirect_url_to ){
                $redirect_form .= '<script type="text/javascript">';
                    $redirect_form .= 'document.arf_new_redirect_form.target="arf_new_window";';
                    $redirect_form .= 'window.open("","arf_new_window", "height=700, width=700");';
                    $redirect_form .= 'document.arf_new_redirect_form.submit();';
                $redirect_form .= '</script>';
            } else {
                $redirect_form .= '<script type="text/javascript" data-cfasync="false">document.getElementById("arf_new_redirect_form").submit();</script>';
            }

        }
        return $redirect_form;
    }

    function ajax_submit_button($arf_form, $form, $action = 'create') {

        global $arfnovalidate;

        if ($arfnovalidate) {
            $arf_form .= ' formnovalidate="formnovalidate"';
        }

        return $arf_form;
    }

    function get_confirmation_method($method, $form) {

        $method = (isset($form->options['success_action']) and ! empty($form->options['success_action'])) ? $form->options['success_action'] : $method;
        
        return $method;
    }

    function confirmation($method, $form, $form_options, $entry_id) {

        if ($method == 'page' and is_numeric($form_options['success_page_id'])) {


            global $post, $arfsettings;


            if ($form_options['success_page_id'] != $post->ID) {


                $page = get_post($form_options['success_page_id']);


                $old_post = $post;


                $post = $page;


                $content = apply_filters('arfcontent', $page->post_content, $form, $entry_id);

                $return["message"] = $content;

                $post = $old_post;

                if ($arfsettings->form_submit_type != 1) {
                    echo "<script type='text/javascript' data-cfasync='false'>
						jQuery(document).ready(function(){
							if (typeof popup_tb_show != 'function') {
								return;
							}
							popup_tb_show('" . $form->id . "');
						});    
					</script>";
                }
            }
        } else if ($method == 'redirect') {

            $success_url = apply_filters('arfcontent', $form_options['success_url'], $form, $entry_id);

            $success_msg = isset($form_options['success_msg']) ? stripslashes($form_options['success_msg']) : addslashes(esc_html__('Please wait while you are redirected.', 'ARForms'));

            echo "<script type='text/javascript' data-cfasync='false'> jQuery(document).ready(function($){ setTimeout(window.location='" . $success_url . "', 5000); });</script>";
        }

        return $return["message"];
    }

    function csv($all_form_id, $search = '', $fid = '') {

        if (!current_user_can('arfviewentries')) {


            global $arfsettings;


            wp_die($arfsettings->admin_permission);
        }


        if (!ini_get('safe_mode')) {


            @set_time_limit(0);
        }


        global $current_user, $arfform, $arffield, $db_record, $arfrecordmeta, $wpdb, $style_settings;


        require(VIEWS_PATH . '/export_data.php');
    }

    function display_list($params = false, $message = '', $page_params_ov = false, $current_page_ov = false, $errors = array()) {


        global $wpdb, $MdlDb, $armainhelper, $arfform, $db_record, $arfrecordmeta, $arfpagesize, $arffield, $arfcurrentform,$arformcontroller, $arfpagesize2;



        if (!$params){
            $form_obj = new stdClass();
            $form_obj->id = -1;
            $params = $this->get_params($form_obj);
            $params2 = $this->get_params2($form_obj);
        }
        $errors = array();

        $form_select = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name');
        
        if ( !empty( $params['form'] )){
            $form = $arfform->getOne($params['form']);
        } else {
            $form = (isset($form_select[0])) ? $form_select[0] : 0;
        }

        if( isset( $params2['incomplete_form'] ) && $params2['incomplete_form'] ){
            $form2 = $arfform->getOne( $params2['incomplete_form'] );
        } else {
            $form2 = (isset($form_select[0])) ? $form_select[0] : 0;
        }

        if ($form) {
            $params['form'] = $form->id;
            $arfcurrentform = $form;
            $where_clause = " it.form_id=$form->id";
        } else {
            $where_clause = '';
        }

        if( $form2 ){
            $params2['incomplete_form'] = $form2->id;
            $arfcurrentform2 = $form2;
            $where_clause2 = " it.form_id=$form2->id";
        } else {
            $where_clause2 = "";
        }

        $page_params = "&action=0&arfaction=0&form=";
        $page_params2 = "&action=0&arfaction=0&form=";


        $page_params .= ($form) ? $form->id : 0;
        $page_params2 .= ($form2) ? $form2->id : 0;

        if (!empty($_REQUEST['s'])){
            $page_params .= '&s=' . urlencode($_REQUEST['s']);
            $page_params2 .= '&s=' . urlencode($_REQUEST['s']);
        }

        if (!empty($_REQUEST['search'])){
            $page_params .= '&search=' . urlencode($_REQUEST['search']);
            $page_params2 .= '&search=' . urlencode($_REQUEST['search']);
        }


        if (!empty($_REQUEST['fid'])){
            $page_params .= '&fid=' . $_REQUEST['fid'];
            $page_params2 .= '&fid=' . $_REQUEST['fid'];
        }

        $item_vars = $this->get_sort_vars($params, $where_clause);
        $item_vars2 = $this->get_sort_vars($params2, $where_clause2);

        $page_params .= ($page_params_ov) ? $page_params_ov : $item_vars['page_params'];
        $page_params2 .= ($page_params_ov) ? $page_parmas_ov : $item_vars2['page_params'];

        $form_cols_order = array();
        $arfinnerfieldorder = array();
        $form_inner_col_order = array();
        $arffieldorder = array();
        $form_css = array();
        
        if ($form) {

            $form_cols_temp = array();

            $form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'imagecontrol') and fi.form_id=" . (int) $form->id, ' ORDER BY id');

            $cache_obj = wp_cache_get('arf_form_options_with_css_'.$form->id);
            if($cache_obj == false){
                $form_options = $wpdb->get_row($wpdb->prepare("SELECT `form_css`,`options` FROM `" . $MdlDb->forms . "` WHERE `id` = %d", (int) $form->id));
                 
                wp_cache_set('arf_form_options_with_css_'.$form->id, $form_options);
            }else{
                $form_options = $cache_obj;
            }

            if(isset($form_options->form_css) && $form_options->form_css != ''){
                $form_css = maybe_unserialize($form_options->form_css);
            }

            if(isset($form_options->options) && $form_options->options != ''){

                $form_options = maybe_unserialize($form_options->options);

                if(isset($form_options['arf_field_order']) && $form_options['arf_field_order'] != ''){
                    $form_cols_order = json_decode($form_options['arf_field_order'], true);
                    asort($form_cols_order);
                    $arffieldorder = $form_cols_order;
                }

                if( isset( $form_options['arf_inner_field_order']) && $form_options['arf_inner_field_order'] != ''){
                    $form_inner_col_order = json_decode( $form_options['arf_inner_field_order'], true );
                    $arfinnerfieldorder = $form_inner_col_order;
                }
            }

            $section_field_array = array();
            $section_fields = array();
            foreach ($arffieldorder as $fieldkey => $fieldorder) {
                foreach ($form_cols as $frmoptkey => $frmoptarr) {
                   
                    $inside_repeater = ( isset( $frmoptarr->field_options['parent_field_type'] ) && 'arf_repeater' == $frmoptarr->field_options['parent_field_type'] ) ? true : false;
              
                    $inside_html = ( isset($frmoptarr->field_options['enable_total']) && '0' == $frmoptarr->field_options['enable_total'] )?true : false;

                    if($frmoptarr->id == $fieldkey ){
                        if( $frmoptarr->type != 'section' ){
                            $form_cols_temp[] = $frmoptarr;
                        } else {
                            if( isset( $arfinnerfieldorder[$frmoptarr->id]) ){
                                foreach( $arfinnerfieldorder[$frmoptarr->id] as $inner_field_order ){
                                    $exploded_data = explode('|',$inner_field_order);
                                    $inner_field_id = (int)$exploded_data[0];
                                    foreach( $form_cols as $ifrmoptkey => $ifrmoptarr ){
                                        if( $ifrmoptarr->id == $inner_field_id ){
                                            $form_cols_temp[] = $ifrmoptarr;
                                            unset($form_cols[$ifrmoptkey]);
                                        }
                                    }
                                }
                            }
                        }
                        unset($form_cols[$frmoptkey]);
                    } else {
                        if( $inside_repeater ){
                            if( isset( $frmoptarr->field_options['parent_field_type'] ) && $frmoptarr->field_options['parent_field_type'] == 'arf_repeater' ){
                                unset( $form_cols[$frmoptkey] );
                            }
                        }
                        if( $inside_html ){
                                unset( $form_cols[$frmoptkey] );
                        }
                    }
                }
            }

            if(count($form_cols_temp) > 0) {
                if(count($form_cols) > 0) {
                    $form_cols_other = $form_cols;
                    $form_cols = array_merge($form_cols_temp,$form_cols_other);
                } else {
                    $form_cols = $form_cols_temp;
                }
            }

            $record_where = ($item_vars['where_clause'] == " it.form_id=$form->id") ? $form->id : $item_vars['where_clause'];
        } else {

            $form_cols = array();

            $record_where = $item_vars['where_clause'];
        }

        $form_cols_order2 = array();
        $arffieldorder2 = array();
        $arfinnerfieldorder2 = array();
        $form_inner_col_order2 = array();
        $form_css2 = array();
        $record_where2 = '';
        if( $form2 ){
            
            $form_cols_temp2 = array();
            $form_cols2 = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'imagecontrol') and fi.form_id=" . (int) $form2->id, ' ORDER BY id');

            $cache_obj = wp_cache_get('arf_form_options_with_css_'.$form2->id);
            if($cache_obj == false){
                $form_options2 = $wpdb->get_row($wpdb->prepare("SELECT `form_css`,`options` FROM `" . $MdlDb->forms . "` WHERE `id` = %d", (int) $form2->id));
                 
                wp_cache_set('arf_form_options_with_css_'.$form2->id, $form_options2);
            }else{
                $form_options2 = $cache_obj;
            }
            if(isset($form_options2->form_css) && $form_options2->form_css != ''){
                $form_css2 = maybe_unserialize($form_options2->form_css);
            }

            if(isset($form_options2->options) && $form_options2->options != ''){

                $form_options2 = maybe_unserialize($form_options2->options);

                if(isset($form_options2['arf_field_order']) && $form_options2['arf_field_order'] != ''){
                    $form_cols_order2 = json_decode($form_options2['arf_field_order'], true);
                    asort($form_cols_order2);
                    $arffieldorder2 = $form_cols_order2;
                }

                if( isset( $form_options2['arf_inner_field_order'] ) && $form_options2['arf_inner_field_order'] != '' ){
                    $form_inner_col_order2 = json_decode( $form_options2['arf_inner_field_order'], true );
                    $arfinnerfieldorder2 = $form_inner_col_order2;
                }
            }

            foreach ($form_cols_order2 as $fieldkey2 => $fieldorder2) {
                foreach ($form_cols2 as $frmoptkey2 => $frmoptarr2) {
                    $inside_repeater = ( isset( $frmoptarr2->field_options['parent_field_type'] ) && 'arf_repeater' == $frmoptarr2->field_options['parent_field_type'] ) ? true : false;

                    $inside_html = ( isset( $frmoptarr2->field_options['enable_total'] ) && '0' == $frmoptarr2->field_options['enable_total'] ) ? true : false;
                     
                    if($frmoptarr2->id == $fieldkey2){
                        if( $frmoptarr2->type != 'section'){
                            $form_cols_temp2[] = $frmoptarr2;
                        } else {
                            if( isset( $arfinnerfieldorder2[$frmoptarr2->id] ) ){
                                foreach( $arfinnerfieldorder2[$frmoptarr2->id] as $inner_field_order2 ){
                                    $exploded_data2 = explode('|',$inner_field_order2);
                                    $inner_field_id2 = (int)$exploded_data2[0];
                                    foreach( $form_cols2 as $ifrmoptkey2 => $ifrmoptarr2 ){
                                        if( $ifrmoptarr2->id == $inner_field_id2 ){
                                            $form_cols_temp2[] = $ifrmoptarr2;
                                            unset($form_cols2[$ifrmoptkey2]);
                                        }
                                    }
                                }
                            }
                        }
                        unset($form_cols2[$frmoptkey2]);
                    } else {
                        if( $inside_repeater ){
                            if( isset( $frmoptarr2->field_options['parent_field_type'] )  && $frmoptarr2->field_options['parent_field_type'] == 'arf_repeater' ){
                                unset( $form_cols2[$frmoptkey2] );
                            }
                        }
                         if( $inside_html ){
                                unset( $form_cols2[$frmoptkey2] );
                        }
                    }
                }
            }

            if(count($form_cols_temp2) > 0) {
                if(count($form_cols2) > 0) {
                    $form_cols_other2 = $form_cols2;
                    $form_cols2 = array_merge($form_cols_temp2,$form_cols_other2);
                } else {
                    $form_cols2 = $form_cols_temp2;
                }
            }

            $record_where2 = ($item_vars2['where_clause'] == " it.form_id=$form2->id") ? $form2->id : $item_vars2['where_clause'];

        }
        
        if (isset($_REQUEST['form']) && $_REQUEST['form'] == '-1' or ( !isset($_REQUEST['form']) or empty($_REQUEST['form']))) {
            $form_cols = array();
            $items = array();
        } else {
            $current_page = ($current_page_ov) ? $current_page_ov : $params['paged'];
            $sort_str = $item_vars['sort_str'];
            $sdir_str = $item_vars['sdir_str'];
            $search_str = $item_vars['search_str'];
            $fid = $item_vars['fid'];
            $record_count = $db_record->getRecordCount($record_where);
            $page_count = $db_record->getPageCount($arfpagesize, $record_count);
            $items = $db_record->getPage(1, 10, $item_vars['where_clause'], $item_vars['order_by'], '', $arffieldorder);
            
            $page_last_record = $armainhelper->getLastRecordNum($record_count, $current_page, $arfpagesize);
            $page_first_record = $armainhelper->getFirstRecordNum($record_count, $current_page, $arfpagesize);
        }
        
        
        if( isset($_REQUEST['incomplete_form']) && $_REQUEST['incomplete_form'] == '-1' || ( !isset($_REQUEST['incomplete_form']) || empty( $_REQUEST['incomplete_form'] ) ) ){
            $form_cols2 = array();
            $items2 = array();
        } else {
            $current_page2 = ($current_page_ov) ? $current_page_ov : $params2['paged'];
            $sort_str2 = $item_vars2['sort_str'];
            $sdir_str2 = $item_vars2['sdir_str'];
            $search_str2 = $item_vars2['search_str'];
            $fid2 = $item_vars2['fid'];
            $record_count2 = $db_record->getRecordCount($record_where2,true);
            $page_count2 = $db_record->getPageCount($arfpagesize, $record_count2,true);
            $items2 = $db_record->getPage('', '', $item_vars2['where_clause'], $item_vars2['order_by'], '', $arffieldorder2, true);       
            $page_last_record2 = $armainhelper->getLastRecordNum($record_count2, $current_page2, $arfpagesize);
            $page_first_record2 = $armainhelper->getFirstRecordNum($record_count2, $current_page2, $arfpagesize2);
        }

        require_once(VIEWS_PATH . '/view_records.php');
    }

    function get_sort_vars($params = false, $where_clause = '') {


        global $arfrecordmeta, $arfcurrentform;


        if (!$params)
            $params = $this->get_params($arfcurrentform);





        $order_by = '';


        $page_params = '';


        $sort_str = $params['sort'];


        $sdir_str = $params['sdir'];


        $search_str = $params['search'];


        $fid = $params['fid'];

        if( isset($params['incomplete_form']) ){
            $fid = $params['incomplete_form'];
        }


        if (!empty($sort_str))
            $page_params .="&sort=$sort_str";


        if (!empty($sdir_str))
            $page_params .= "&sdir=$sdir_str";



        if (!empty($search_str)) {

            if( isset( $params['incomplete_form'] ) ){
                $where_clause = $this->get_search_str($where_clause, $search_str, $params['incomplete_form'], $fid);
            } else {
                $where_clause = $this->get_search_str($where_clause, $search_str, $params['form'], $fid);
            }


            $page_params .= "&search=$search_str";


            if (is_numeric($fid))
                $page_params .= "&fid=$fid";
        }


        if (is_numeric($sort_str))
            $order_by .= " ORDER BY ID";


        else if ($sort_str == "entry_key")
            $order_by .= " ORDER BY entry_key";
        else
            $order_by .= " ORDER BY ID";





        if ((empty($sort_str) and empty($sdir_str)) or $sdir_str == 'desc') {


            $order_by .= ' DESC';


            $sdir_str = 'desc';
        } else {


            $order_by .= ' ASC';


            $sdir_str = 'asc';
        }





        return compact('order_by', 'sort_str', 'sdir_str', 'fid', 'search_str', 'where_clause', 'page_params');
    }

    function get_search_str($where_clause, $search_str, $form_id = false, $fid = false) {


        global $arfrecordmeta, $armainhelper, $arfform;


        $where_item = '';


        $join = ' (';


        if (!is_array($search_str))
            $search_str = explode(" ", $search_str);



        foreach ($search_str as $search_param) {


            $search_param = esc_sql(like_escape($search_param));


            if (!is_numeric($fid)) {


                $where_item .= (empty($where_item)) ? ' (' : ' OR';



                if (in_array($fid, array('created_date', 'user_id'))) {


                    if ($fid == 'user_id' and ! is_numeric($search_param))
                        $search_param = $armainhelper->get_user_id_param($search_param);


                    $where_item .= " it.{$fid} like '%$search_param%'";
                }else {


                    $where_item .= " it.name like '%$search_param%' OR it.entry_key like '%$search_param%' OR it.description like '%$search_param%' OR it.created_date like '%$search_param%'";
                }
            }


            if (empty($fid) or is_numeric($fid)) {


                $where_entries = "(entry_value LIKE '%$search_param%'";


                if ($data_fields = $arfform->has_field('data', $form_id, false)) {


                    $df_form_ids = array();


                    foreach ((array) $data_fields as $df) {


                        $df->field_options = maybe_unserialize($df->field_options);


                        if (is_numeric($df->field_options['form_select']))
                            $df_form_ids[] = $df->field_options['form_select'];


                        unset($df);
                    }





                    unset($data_fields);


                    global $wpdb, $MdlDb;


                    $data_form_ids = $wpdb->get_col("SELECT form_id FROM $MdlDb->fields WHERE id in (" . implode(',', $df_form_ids) . ")");


                    unset($df_form_ids);


                    if ($data_form_ids) {


                        $data_entry_ids = $arfrecordmeta->getEntryIds("fi.form_id in (" . implode(',', $data_form_ids) . ") and entry_value LIKE '%" . $search_param . "%'");


                        if (!empty($data_entry_ids))
                            $where_entries .= " OR entry_value in (" . implode(',', $data_entry_ids) . ")";
                    }


                    unset($data_form_ids);
                }



                $where_entries .= ")";


                if (is_numeric($fid))
                    $where_entries .= " AND fi.id=$fid";



                $meta_ids = $arfrecordmeta->getEntryIds($where_entries);


                if (!empty($meta_ids)) {


                    if (!empty($where_clause)) {


                        $where_clause .= " AND" . $join;


                        if (!empty($join))
                            $join = '';
                    }


                    $where_clause .= " it.id in (" . implode(',', $meta_ids) . ")";
                }else {


                    if (!empty($where_clause)) {


                        $where_clause .= " AND" . $join;


                        if (!empty($join))
                            $join = '';
                    }


                    $where_clause .= " it.id=0";
                }
            }
        }





        if (!empty($where_item)) {


            $where_item .= ')';


            if (!empty($where_clause))
                $where_clause .= empty($fid) ? ' OR' : ' AND';


            $where_clause .= $where_item;


            if (empty($join))
                $where_clause .= ')';
        }else {


            if (empty($join))
                $where_clause .= ')';
        }





        return $where_clause;
    }

    function get_new_vars($errors = '', $form = '', $message = '') {


        global $arfform, $arffield, $db_record, $arfsettings, $arfnextpage, $arfieldhelper;


        $title = true;


        $description = true;


        $fields = $arfieldhelper->get_all_form_fields($form->id, !empty($errors));


        $values = $arrecordhelper->setup_new_vars($fields, $form);


        $submit = (isset($arfnextpage[$form->id])) ? $arfnextpage[$form->id] : (isset($values['submit_value']) ? $values['submit_value'] : $arfsettings->submit_value);


        require_once(VIEWS_PATH . '/new.php');
    }

    function get_params2($form = null){

        global $arfform, $armainhelper;

        if (!$form){
            $form = wp_cache_get('get_all_record');
            if( false == $form){
                $form = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name', ' LIMIT 1');
                wp_cache_set('get_all_record', $form);
            }
        }

        $values = array();

        foreach (array('id' => '', 'form_name' => '', 'paged' => 1, 'form' => (($form) ? $form->id : 0), 'field_id' => '', 'search' => '', 'sort' => '', 'sdir' => '', 'fid' => '', 'incomplete_form' => (isset($form2) ? $form2->id : 0)) as $var => $default)
            $values[$var] = $armainhelper->get_param($var, $default);

        return $values;


    }

    function get_params( $form = null ) {

        global $arfform, $armainhelper;

        if (!$form){
            $form = wp_cache_get('get_all_record');
            if($form == false){
                $form = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name', ' LIMIT 1');    
                wp_cache_set('get_all_record', $form);
            }
        }

        $values = array();

        foreach (array('id' => '', 'form_name' => '', 'paged' => 1, 'form' => (($form) ? $form->id : 0), 'field_id' => '', 'search' => '', 'sort' => '', 'sdir' => '', 'fid' => '') as $var => $default)
            $values[$var] = $armainhelper->get_param($var, $default);

        return $values;
    }

    function &filter_shortcode_value($value, $tag, $atts, $field) {


        if (isset($atts['show']) and $atts['show'] == 'value')
            return $value;


        $value = $this->filter_display_value($value, $field);


        return $value;
    }

    function &filter_entry_display_value($value, $field, $atts = array()) {
        $field->field_options = maybe_unserialize($field->field_options);
        $saved_value = (isset($atts['saved_value']) and $atts['saved_value']) ? true : false;
        if (!in_array($field->type, array('checkbox', 'arf_multiselect')) or ! isset($field->field_options['separate_value']) or ! $field->field_options['separate_value'] or $saved_value)
            return $value;
        $field->options = maybe_unserialize($field->options);
        $f_values = array();
        $f_labels = array();
        if(is_array($field->options))
        {
            foreach ($field->options as $opt_key => $opt) {
            if (!is_array($opt))
                continue;
            $f_labels[$opt_key] = isset($opt['label']) ? $opt['label'] : reset($opt);
            $f_values[$opt_key] = isset($opt['value']) ? $opt['value'] : $f_labels[$opt_key];
            if ($f_labels[$opt_key] == $f_values[$opt_key]) {
                unset($f_values[$opt_key]);
                unset($f_labels[$opt_key]);
            }
            unset($opt_key);
            unset($opt);
            }        
        }
        if (!empty($f_values)) {
            foreach ((array) $value as $v_key => $val) {
                if (in_array($val, $f_values)) {
                    $opt = array_search($val, $f_values);
                    if (is_array($value))
                        $value[$v_key] = $f_labels[$opt];
                    else
                        $value = $f_labels[$opt];
                }
                unset($v_key);
                unset($val);
            }
        }        
        return $value;
    }

    function &filter_display_value($value, $field) {
        global $arrecordcontroller;
        $value = $arrecordcontroller->filter_entry_display_value($value, $field);


        return $value;
    }

    function route() {

        global $armainhelper;
        $action = $armainhelper->get_param('arfaction');

        if ($action == 'create')
            return $this->create();

        else if ($action == 'destroy')
            return $this->destroy();


        else if ($action == 'destroy_all')
            return $this->destroy_all();


        else if ($action == 'graph')
            return $this->display_graph();


        else if ($action == 'list-form')
            return $this->bulk_actions($action);


        else {
            $action = $armainhelper->get_param('action');

            if ($action == -1)
                $action = $armainhelper->get_param('action2');

            if (strpos($action, 'bulk_') === 0) {

                if (isset($_GET) and isset($_GET['action']))
                    $_SERVER['REQUEST_URI'] = str_replace('&action=' . $_GET['action'], '', $_SERVER['REQUEST_URI']);

                if (isset($_GET) and isset($_GET['action2']))
                    $_SERVER['REQUEST_URI'] = str_replace('&action=' . $_GET['action2'], '', $_SERVER['REQUEST_URI']);

                return $this->bulk_actions($action);
            } else {
                return $this->display_list();
            }
        }
    }

    function get_form($filename, $form, $title, $description, $preview = false, $is_widget_or_modal = false, $is_confirmation_method = false, $func_val = 'true') {
        ;
        global $arfsettings;
        if ($func_val != 'true') {
            echo $func_val;
            exit;
        }


        if ($arfsettings->form_submit_type != 1) {
            wp_print_styles('arfdisplaycss');
            wp_print_scripts('jquery-validation');
        }

        if (is_file($filename)) {


            ob_start();


            include $filename;


            $contents = ob_get_contents();


            ob_end_clean();

            return $contents;
        }


        return false;
    }

    function ajax_create() {

        global $db_record;

        $errors = $db_record->validate($_POST, array('file'));


        if (empty($errors)) {


            echo false;
        } else {


            $errors = str_replace('"', '&quot;', stripslashes_deep($errors));


            $obj = array();


            foreach ($errors as $field => $error) {


                $field_id = str_replace('field', '', $field);


                $obj[$field_id] = $error;
            }


            echo json_encode($obj);
        }


        die();
    }

    function ajax_update() {


        return $this->ajax_create();
    }

    function ajax_destroy($form_id = false, $ajax = true, $echo = true) {


        global $user_ID, $MdlDb, $db_record, $arfdeletedentries, $armainhelper;



        $entry_key = $armainhelper->get_param('entry');


        if (!$form_id)
            $form_id = $armainhelper->get_param('form_id');


        if (!$entry_key)
            return;



        if (is_array($arfdeletedentries) and in_array($entry_key, $arfdeletedentries))
            return;





        $where = array();


        if (!current_user_can('arfdeleteentries'))
            $where['user_id'] = $user_ID;





        if (is_numeric($entry_key))
            $where['id'] = $entry_key;
        else
            $where['entry_key'] = $entry_key;



        $entry = $MdlDb->get_one_record($MdlDb->entries, $where, 'id, form_id');



        if ($form_id and $entry->form_id != (int) $form_id)
            return;
        $entry_id = $entry->id;



        apply_filters('arfallowdelete', $entry_id, $entry_key, $form_id);


        if (!$entry_id) {


            $message = addslashes(esc_html__('There is an error deleting that entry', 'ARForms'));


            if ($echo)
                echo '<div class="frm_message">' . $message . '</div>';
        }else {


            $db_record->destroy($entry_id);


            if (!$arfdeletedentries)
                $arfdeletedentries = array();


            $arfdeletedentries[] = $entry_id;





            if ($ajax) {


                if ($echo)
                    echo $message = 'success';
            }else {


                $message = addslashes(esc_html__('Your entry is successfully deleted', 'ARForms'));





                if ($echo)
                    echo '<div class="frm_message">' . $message . '</div>';
            }
        }


        return $message;
    }

    function send_email($entry_id, $form_id, $type) {

        global $arnotifymodel;

        if (current_user_can('arfviewforms') or current_user_can('arfeditforms')) {


            if ($type == 'autoresponder')
                $sent_to = $arnotifymodel->autoresponder($entry_id, $form_id);
            else
                $sent_to = $arnotifymodel->entry_created($entry_id, $form_id);





            if (is_array($sent_to))
                echo implode(',', $sent_to);
            else
                echo $sent_to;
        }else {


            echo addslashes(esc_html__('No one! You do not have permission', 'ARForms'));
        }
    }

    function display_graph() {

        $form = $_REQUEST['form'];
        require_once(VIEWS_PATH . '/graph.php');
    }

    function updatechart() {

        $form = $_POST['form'];
        $type = $_POST['type'];
        $graph_type = $_POST['graph_type'];
        require_once(VIEWS_PATH . '/graph_ajax.php');

        die();
    }

    function managecolumns() {

        global $wpdb, $MdlDb;

        $form = $_POST['form'];

        $colsArray = $_POST['colsArray'];

        $new_arr = explode(',', $colsArray);

        $array_hidden = array();

        foreach ($new_arr as $key => $val) {

            if ($key % 2 == 0) {

                if ($new_arr[$key + 1] == 'hidden'){
                    $array_hidden[] = $val;
                }
            }
        }

        $ser_arr = maybe_serialize($array_hidden);

        $wpdb->update($MdlDb->forms, array('columns_list' => $ser_arr), array('id' => $form));

        die();
    }

    function manageincompletecolumns(){
        global $wpdb, $MdlDb;

        $form = $_POST['form'];
        $colsArray = $_POST['colsArray'];

        $new_arr = explode( ',', $colsArray );

        $array_hidden = array();

        foreach( $new_arr as $key => $val ) {
            if( $key % 2 == 0 ){
                if( $new_arr[$key + 1] == 'hidden' ){
                    $array_hidden[] = $val;
                }
            }
        }

        $ser_arr = maybe_serialize( $array_hidden );

        $wpdb->update( $MdlDb->forms, array( 'partial_grid_column_list' => $ser_arr ), array( 'id' => $form ) );

        die;
    }

    function arfchangebulkincompleteentries(){
        global $armainhelper, $wpdb, $MdlDb, $arfsettings,$db_record;
        $action1 = isset( $_REQUEST['action3'] ) ? $_REQUEST['action3'] : '-1';
        $action2 = isset( $_REQUEST['action4'] ) ? $_REQUEST['action4'] : '-1';

        $form_id = isset( $_REQUEST['form_id'] ) ? $_REQUEST['form_id'] : '';
        $start_date = isset( $_REQUEST['start_date'] ) ? $_REQUEST['start_date'] : '';
        $end_date = isset( $_REQUEST['end_date'] ) ? $_REQUEST['end_date'] : '';

        $items = isset( $_REQUEST['item-action'] ) ? $_REQUEST['item-action'] : array();

        $bulk_action = "-1";
        if( "-1" != $action1 ){
            $bulk_action = $action1;
        } else if( '-1' == $action1 && '-1' != $action2 ){
            $bulk_action = $action2;
        }

        if( "-1" == $bulk_action ){
            echo json_encode(
                array(
                    'error' => true,
                    'message' => addslashes( esc_html__( 'Please select valid action', 'ARForms' ) )
                )
            );
            die;
        }

        if( empty( $items ) ){
            echo json_encode(
                array(
                    'error' => true,
                    'message' => addslashes( esc_html__( 'Please select one or more records', 'ARForms' ) )
                )
            );
            die;
        }

        if( 'bulk_delete' == $bulk_action ){
            if( ! current_user_can('arfdeleteentries') ){
                echo json_encode(
                    array(
                        'error' => true,
                        'message' => $arfsettings->admin_permission
                    )
                );
                die;
            } else {
                if( is_array( $items ) ){
                    foreach( $items as $entry_id ){
                        $wpdb->delete(
                            $MdlDb->incomplete_entry_metas,
                            array(
                                'entry_id' => $entry_id
                            ),
                            array( '%d' )
                        );

                        $del = $wpdb->delete(
                            $MdlDb->incomplete_entries,
                            array(
                                'id' => $entry_id
                            ),
                            array( '%d' )
                        );
                    }

                    if( $del ){
                        $total_records = '';
                        if( '' != $form_id ){
                            $total_records = $db_record->getRecordCount( (int)$form_id, true );
                        }

                        $message = addslashes(esc_html__('Entries deleted successfully.', 'ARForms'));
                        echo json_encode(
                            array(
                                'error'=>false,
                                'message'=> $message,
                                'arftotrec' => $total_records
                            )
                        );
                    }
                }
            }
        } else if( 'bulk_csv' == $bulk_action ){
            if( ! current_user_can( 'arfviewentries' ) ){
                wp_die($arfsettings->admin_permission);
            }
            global $arfform;

            if( $form_id ){
                $form = $arfform->getOne($form_id);
            } else {
                $form = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name', ' LIMIT 1');
            }

            if ($form){
                $form_id = $form->id;
            } else{
                $errors[] = addslashes(esc_html__('No form is found', 'ARForms'));
            }

            if ($form_id and is_array($items)) {
                $link = site_url() . '/index.php?plugin=ARForms&controller=incomplete_entries&form=' . $form_id . '&arfaction=csv&entry_id=' . implode(',', $items);
                echo json_encode($link);
            }
        } else if( 'move_to_entry' == $bulk_action ){
            foreach( $items as $entry_id ){
                $ret = $this->arf_move_incomplete_entry_data( $entry_id, $form_id, true);
                $status = arf_json_decode( $ret );
            }
            if( $status->errors == false ){
                $total_records = '';
                if( '' != $form_id ){
                    $total_records = $db_record->getRecordCount( (int)$form_id, true );
                }

                $message = addslashes(esc_html__('Entries moved successfully.', 'ARForms'));
                echo json_encode(
                    array(
                        'error'=>false,
                        'message'=> $message,
                        'arftotrec' => $total_records
                    )
                );
            }
        }
        die;
    }

    function arfchangebulkentries() {
        global $armainhelper, $wpdb, $MdlDb,$arfsettings,$db_record;
        $action1 = isset($_REQUEST['action1']) ? $_REQUEST['action1'] : '-1';
        $action2 = isset($_REQUEST['action5']) ? $_REQUEST['action5'] : '-1';

        $form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : '';
        $start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';
        $end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';

        $items = isset($_REQUEST['item-action']) ? explode(',',$_REQUEST['item-action']) : array();


        $bulk_action = "-1";
        if ($action1 != '-1') {
            $bulk_action = $action1;
        } else if ($action1 == "-1" && $action2 != "-1") {
            $bulk_action = $action2;
        }

        if ($bulk_action == '-1') {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Please select valid action.', 'ARForms'))));
            die();
        }

        if (empty($items)) {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Please select one or more records', 'ARForms'))));
            die();
        }

        if ($bulk_action == 'bulk_delete') {
            if (!current_user_can('arfdeleteentries')) {
                echo json_encode(array('error' => true, 'message' => $arfsettings->admin_permission)); 
                die();
            } else {
                if (is_array($items)) {
                    foreach ($items as $entry_id) {
                        $del_res = $db_record->destroy($entry_id);
                    }

                    if ($del_res) {

                        $total_records = '';
                        if($form_id != ''){
                            $total_records = $db_record->getRecordCount( (int)$form_id );
                        }

                        $message = addslashes(esc_html__('Entries deleted successfully.', 'ARForms'));
                        echo json_encode(array('error'=>false, 'message'=> $message, 'arftotrec' => $total_records));
                    }
                }
            }
        } else if ($bulk_action == 'bulk_csv') {
                
    	    if (!current_user_can('arfviewentries'))
    		wp_die($arfsettings->admin_permission);

                        global $arfform;


                        if ($form_id) {

                            $form = $arfform->getOne($form_id);
                        } else {


                            $form = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name', ' LIMIT 1');


                            if ($form)
                                $form_id = $form->id;
                            else
                                $errors[] = addslashes(esc_html__('No form is found', 'ARForms'));
                        }





                        if ($form_id and is_array($items)) {

    		$link = site_url() . '/index.php?plugin=ARForms&controller=entries&form=' . $form_id . '&arfaction=csv&entry_id=' . implode(',', $items);
    		echo json_encode($link);
    	    }
    	} else {
            do_action( 'arf_change_bulk_entries_outside', $bulk_action, $form_id, $items );
        }

	   die();
    }

    function arf_retrieve_form_data(){
        global $wpdb, $MdlDb, $arffield, $armainhelper, $db_record, $arfform, $arfpagesize, $arformcontroller, $arfpagesize2;

        $requested_data = arf_json_decode( stripslashes_deep( $_REQUEST['data'] ), true );

        $filtered_aoData  = $requested_data['aoData'];

        $return_data = array();

        $order_by = !empty( $filtered_aoData['iSortCol_0'] ) ? $filtered_aoData['iSortCol_0'] : 1;

        $order_by_str = 'ORDER BY';

        if( 1 == $order_by ){
            $order_by_str .= ' f.id';
        } else if( 2 == $order_by ){
            $order_by_str .= ' f.name';
        } else if( 3 == $order_by ){
            $order_by_str .= ' total_entries';
        } else if( 5 == $order_by ){
            $order_by_str .= ' f.created_date';
        } else {
            $order_by_str .= ' f.id';
        }

        $order_by_str .=  ' ' . ( !empty( $filtered_aoData['sSortDir_0'] ) ? strtoupper($filtered_aoData['sSortDir_0']) : 'DESC' );

        $form_params = 'f.*,COUNT(e.form_id) AS total_entries';

        $form_table_param = $MdlDb->forms .' f LEFT JOIN '.$MdlDb->entries.' e ON f.id = e.form_id';

        $group_by_param = 'GROUP BY f.id';

        $offset = isset($filtered_aoData['iDisplayStart']) ? $filtered_aoData['iDisplayStart'] : 0;
        $limit = isset($filtered_aoData['iDisplayLength']) ? $filtered_aoData['iDisplayLength'] : 10;

        $limit_param = 'LIMIT '.$offset.', '.$limit;

        $where_clause = 'WHERE f.is_template = %d AND ( f.status is NULL OR f.status = \'\' OR f.status = %s )';
        $where_params = array( 0, 'published' );

        if( !empty( $filtered_aoData['sSearch'] ) ){
            $wild = '%';
            $find = $filtered_aoData['sSearch'];
            $like = $wild . $wpdb->esc_like( $find ) . $wild;
            $where_clause .= ' AND ( f.name LIKE %s )';
            $where_params[] = $like;
        }

        $form_results = $arfform->arf_select_db_data( true, '', $form_table_param, $form_params, $where_clause, $where_params, $group_by_param, $order_by_str, $limit_param );

        $total_records = $arfform->arf_select_db_data( true, '', $MdlDb->forms . ' f', 'COUNT(f.id)', $where_clause, $where_params, '', '', '', true );

        $data = array();
        if( count( $form_results ) > 0 ){

            $ai = 0;
            foreach( $form_results as $form_data ){

                $data[$ai][0] = "<div class='arf_custom_checkbox_div arfmarginl20'><div class='arf_custom_checkbox_wrapper'><input id='cb-item-action-{$form_data->id} class='chkstanard' type='checkbox' value='{$form_data->id}' name='item-action[]'>
                                <svg width='18px' height='18px'>
                                " . ARF_CUSTOM_UNCHECKED_ICON . "
                                " . ARF_CUSTOM_CHECKED_ICON . "
                                </svg>
                            </div>
                        </div>
                        <label for='cb-item-action-{$form_data->id}'><span></span></label>";

                $data[$ai][1] = $form_data->id;
                $edit_link = "?page=ARForms&arfaction=edit&id={$form_data->id}";
                if( current_user_can('arfeditforms')){
                    $data[$ai][2] = "<a class='row-title' href='{$edit_link}'>" . html_entity_decode(stripslashes($form_data->name)) . "</a>";
                } else {
                    $data[$ai][2] = html_entity_decode( stripslashes_deep( $form_data->name ) );
                }

                $data[$ai][3] = ((current_user_can('arfviewentries')) ? "<a href='" . esc_url(admin_url('admin.php') . "?page=ARForms-entries&form=" . $form_data->id) . "'>" . $form_data->total_entries . "</a>" : $form_data->total_entries);

                $shortcode_data = "";

                if( !empty( $form_data->arf_is_lite_form ) && $form_data->arf_is_lite_form == 1 ){
                    $shortcode_data .= "<div class='arf_shortcode_div'>
                                <div class='arf_copied grid_copy_icon' data-attr='[ARFormslite id={$form_data->arf_lite_form_id}]'>".addslashes(esc_html__('Click to Copy','ARForms'))."</div>
                                <input type='text' class='shortcode_textfield' readonly='readonly' onclick='this.select();' onfocus='this.select();' value='[ARFormslite id={$form_data->arf_lite_form_id}]' />
                            </div>";
                } else {
                    $shortcode_data .= "<div class='arf_shortcode_div'>
                                <div class='arf_copied grid_copy_icon' data-attr='[ARForms id={$form_data->id}]'>".addslashes(esc_html__('Click to Copy','ARForms'))."</div>
                                <input type='text' class='shortcode_textfield' readonly='readonly' onclick='this.select();' onfocus='this.select();' value='[ARForms id={$form_data->id}]' />
                            </div>";
                }

                $shortcode_data .= "<div class='arf_shortcode_div'>
                            <div class='arf_copied grid_copy_icon' data-attr=\"[ARForms_popup id={$form_data->id} desc='Click here to open Form' type='link' height='auto' width='800' overlay='0.6' is_close_link='yes' modal_bgcolor='#000000' ]\">".addslashes(esc_html__('Click to Copy','ARForms'))."</div>
                            <input type='text' class='shortcode_textfield' readonly='readonly' onclick='this.select();' onfocus='this.select();' value=\"[ARForms_popup id={$form_data->id} desc='Click here to open Form' type='link' height='auto' width='800' overlay='0.6' is_close_link='yes' modal_bgcolor='#000000' ]\" />
                        </div>";

                $data[$ai][4] = $shortcode_data;

                $wp_format_date = get_option('date_format');
                if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
                    $date_format_new = 'M d, Y';
                } else if ($wp_format_date == 'd/m/Y') {
                    $date_format_new = 'd M, Y';
                } else if ($wp_format_date == 'Y/m/d') {
                    $date_format_new = 'Y, M d';
                } else {
                    $date_format_new = 'M d, Y';
                }

                $data[$ai][5] = date($date_format_new, strtotime($form_data->created_date));

                $action_row_data = "<div class='arf-row-actions'>";

                if( current_user_can('arfeditforms') ){
                    $edit_link = "?page=ARForms&arfaction=edit&id={$form_data->id}";

                    $action_row_data .= "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Edit Form', 'ARForms')) . "'><a href='" . wp_nonce_url($edit_link) . "'><svg width='30px' height='30px' viewBox='-5 -4 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill='#ffffff' d='M17.469,7.115v10.484c0,1.25-1.014,2.264-2.264,2.264H3.75c-1.25,0-2.262-1.014-2.262-2.264V5.082  c0-1.25,1.012-2.264,2.262-2.264h9.518l-2.264,2.001H3.489v13.042h11.979V9.379L17.469,7.115z M15.532,2.451l-0.801,0.8l2.4,2.401  l0.801-0.8L15.532,2.451z M17.131,0.85l-0.799,0.801l2.4,2.4l0.801-0.801L17.131,0.85z M6.731,11.254l2.4,2.4l7.201-7.202  l-2.4-2.401L6.731,11.254z M5.952,14.431h2.264l-2.264-2.264V14.431z' /></svg></a></div>";

                }

                if( current_user_can( 'arfviewentries' ) ){

                    $duplicate_link = "?page=ARForms&arfaction=duplicate&id={$form_data->id}";

                    $action_row_data .= "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Form Entry', 'ARForms')) . "'><a href='" . wp_nonce_url("?page=ARForms-entries&arfaction=list&form={$form_data->id}") . "' ><svg width='30px' height='30px' viewBox='-7 -4 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M1.489,19.829V0.85h14v18.979H1.489z M13.497,2.865H3.481v14.979  h10.016V2.865z M10.489,15.806H4.493v-2h5.996V15.806z M4.495,9.806h7.994v2H4.495V9.806z M4.495,5.806h7.994v2H4.495V5.806z' /></svg></a></div>";

                }

                if( current_user_can('arfeditforms') ){

                    $action_row_data .= "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Duplicate Form', 'ARForms')) . "'><a href='" . wp_nonce_url($duplicate_link) . "' ><svg width='30px' height='30px' viewBox='-5 -5 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M16.501,15.946V2.85H5.498v-2h11.991v0.025h1.012v15.07H16.501z   M15.489,19.81h-14V3.894h14V19.81z M13.497,5.909H3.481v11.979h10.016V5.909z'/></svg></a></div>";
                }

                if( current_user_can('arfviewentries') ){
                    $action_row_data .= "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Export Entries', 'ARForms')) . "'><a onclick='arfaction_func(\"export_csv\", \"{$form_data->id}\");'><svg width='30px' height='30px' viewBox='-3 -5 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill='#ffffff' d='M16.477,10.586V7.091c0-0.709-0.576-1.283-1.285-1.283H2.772c-0.709,0-1.283,0.574-1.283,1.283v3.495    c0,0.709,0.574,1.283,1.283,1.283h12.419C15.9,11.87,16.477,11.295,16.477,10.586z M5.131,9.887c0.277,0,0.492-0.047,0.67-0.116    l0.138,0.862c-0.208,0.092-0.6,0.17-1.047,0.17c-1.217,0-1.995-0.74-1.995-1.925c0-1.102,0.753-2.002,2.156-2.002    c0.308,0,0.646,0.054,0.893,0.146L5.762,7.892C5.623,7.83,5.415,7.776,5.107,7.776c-0.616,0-1.016,0.438-1.01,1.055    C4.098,9.524,4.561,9.887,5.131,9.887z M8.525,10.772c-0.492,0-1.369-0.107-1.654-0.262l0.646-0.839    C7.732,9.8,8.179,9.957,8.525,9.957c0.354,0,0.501-0.124,0.501-0.317c0-0.191-0.116-0.284-0.556-0.43    C7.695,8.948,7.395,8.524,7.402,8.077c0-0.701,0.6-1.231,1.531-1.231c0.44,0,0.832,0.101,1.063,0.216L9.789,7.87    c-0.17-0.094-0.494-0.216-0.816-0.216c-0.285,0-0.446,0.116-0.446,0.309c0,0.177,0.147,0.269,0.608,0.431    c0.717,0.246,1.016,0.608,1.023,1.162C10.158,10.255,9.604,10.772,8.525,10.772z M13.54,10.725h-1.171l-1.371-3.766h1.271    l0.509,1.748c0.092,0.315,0.162,0.617,0.216,0.916h0.023c0.062-0.308,0.124-0.593,0.208-0.916l0.486-1.748h1.23L13.54,10.725z     M19.961,0.85H6.02c-0.295,0-0.535,0.239-0.535,0.534v2.45h1.994V2.79h11.014v11.047l-2.447-0.002    c-0.158,0-0.309,0.064-0.421,0.177c-0.11,0.109-0.173,0.26-0.173,0.418l0.012,3.427H7.479V12.8H5.484v6.501    c0,0.294,0.239,0.533,0.535,0.533h10.389c0.153,0,0.297-0.065,0.398-0.179l3.553-4.048c0.088-0.098,0.135-0.224,0.135-0.355V1.384    C20.496,1.089,20.255,0.85,19.961,0.85z'/></svg></a></div>";
                }

                global $style_settings, $arformhelper;

                $target_url = $arformhelper->get_direct_link($form_data->form_key);

                $target_url = $target_url . '&ptype=list';

                $width = isset($_COOKIE['width']) ? $_COOKIE['width'] * 0.80 : 0;

                if (isset($_COOKIE['width']) and $_COOKIE['width'] != '') {
                    $tb_width = '&width=' . $width;
                } else {
                    $tb_width = '';
                }

                if (isset($_COOKIE['height']) and $_COOKIE['height'] != '') {
                    $tb_height = '&height=' . ($_COOKIE['height'] - 100);
                } else {
                    $tb_height = '';
                }

                $action_row_data .= "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Preview', 'ARForms')) . "'><a class='openpreview' href='javascript:void(0)'  data-url='" . $target_url . $tb_width . $tb_height . "&whichframe=preview&TB_iframe=true'><svg width='30px' height='30px' viewBox='-3 -8 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M12.993,15.23c-7.191,0-11.504-7.234-11.504-7.234  S5.801,0.85,12.993,0.85c7.189,0,11.504,7.19,11.504,7.19S20.182,15.23,12.993,15.23z M12.993,2.827  c-5.703,0-8.799,5.214-8.799,5.214s3.096,5.213,8.799,5.213c5.701,0,8.797-5.213,8.797-5.213S18.694,2.827,12.993,2.827z   M12.993,11.572c-1.951,0-3.531-1.581-3.531-3.531s1.58-3.531,3.531-3.531c1.949,0,3.531,1.581,3.531,3.531  S14.942,11.572,12.993,11.572z'/></svg></a></div>";

                if (current_user_can('arfdeleteforms')) {
                    $delete_link = "?page=ARForms&arfaction=destroy&id={$form_data->id}";
                    $id = $form_data->id;
                    $action_row_data .= "<div class='arfformicondiv arfhelptip arfdeleteform_div_" . $id . "' title='" . addslashes(esc_html__('Delete', 'ARForms')) . "'><a  id='delete_pop' data-toggle='arfmodal' data-id='" . $id . "' style='cursor:pointer'><svg width='30px' height='30px' viewBox='-5 -5 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z' /></svg></a></div>";
                }
            

                $action_row_data .= "</div>";

                $data[$ai][6] = $action_row_data;

                $ai++;
            }

            $sEcho = isset($filtered_aoData['sEcho']) ? intval($filtered_aoData['sEcho']) : intval(10);

            $return_data = array(
                'sEcho' => $sEcho,
                'iTotalRecords' => (int)$total_records,
                'iTotalDisplayRecords' => (int)$total_records,
                'aaData' => $data,
            );
        } else {
            $sEcho = isset($filtered_aoData['sEcho']) ? intval($filtered_aoData['sEcho']) : intval(10);
            $return_data = array(
                'sEcho' => $sEcho,
                'iTotalRecords' => (int)$total_records,
                'iTotalDisplayRecords' => (int)$total_records,
                'aaData' => $data,
            );
        }

        echo json_encode( $return_data );

        die;
    }

    function arf_retrieve_form_entry_data(){
        global $wpdb, $MdlDb, $arffield, $armainhelper, $db_record, $arfform,$arfpagesize,$arformcontroller,$arfpagesize2, $arf_matrix;
        
        $requested_data = json_decode(stripslashes_deep($_REQUEST['data']),true);

        $filtered_aoData = $requested_data['aoData'];

        $form_id = isset($filtered_aoData['form']) ? $filtered_aoData['form'] : '-1';
        $start_date = isset($filtered_aoData['start_date']) ? $filtered_aoData['start_date'] : '';
        $end_date = isset($filtered_aoData['end_date']) ? $filtered_aoData['end_date'] : '';

        $return_data = array();

        $form_select = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$MdlDb->forms."` WHERE `id` = %d AND `is_template` != %d AND `status` = %s", $form_id, 1,'published') );

        $form_name = $form_select->name;

        $form_css = maybe_unserialize($form_select->form_css);

        $form_options = maybe_unserialize($form_select->options);

        $arffieldorder = array();

        $arfinnerfieldorder = array();

        $form_cols_order = array();

        $form_cols_inner_order = array();

        if(isset($form_options['arf_field_order']) && $form_options['arf_field_order'] != ''){
            $form_cols_order = json_decode($form_options['arf_field_order'], true);
            asort($form_cols_order);
            $arffieldorder = $form_cols_order;
        }

        if( isset( $form_options['arf_inner_field_order'] ) && $form_options['arf_inner_field_order'] != '' ){
            $form_cols_inner_order = json_decode( $form_options['arf_inner_field_order'], true );
            asort( $form_cols_inner_order );
            $arfinnerfieldorder = $form_cols_inner_order;
        }

        $offset = isset($filtered_aoData['iDisplayStart']) ? $filtered_aoData['iDisplayStart'] : 0;
        $limit = isset($filtered_aoData['iDisplayLength']) ? $filtered_aoData['iDisplayLength'] : 10;

        $searchStr = isset($filtered_aoData['sSearch']) ? $filtered_aoData['sSearch'] : '';
        $sorting_order = isset($filtered_aoData['sSortDir_0']) ? $filtered_aoData['sSortDir_0'] : 'desc';
        $sorting_column = (isset($filtered_aoData['iSortCol_0']) && $filtered_aoData['iSortCol_0'] > 0) ? $filtered_aoData['iSortCol_0'] : 1;

        $form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'imagecontrol') and fi.form_id=" . (int) $form_id, ' ORDER BY id');

        if(count($arffieldorder) > 0){
            $form_cols_temp = array();
            foreach ($arffieldorder as $fieldkey => $fieldorder) {
                foreach ($form_cols as $frmoptkey => $frmoptarr) {
                    $inside_html = ( isset( $frmoptarr->field_options['enable_total'] ) && '0' == $frmoptarr->field_options['enable_total'] ) ? true : false;
                    $inside_repeater = ( isset( $frmoptarr->field_options['parent_field_type'] ) && 'arf_repeater' == $frmoptarr->field_options['parent_field_type'] ) ? true : false;
                    if($frmoptarr->id == $fieldkey){
                        if( $frmoptarr->type != 'section' ){
                            $form_cols_temp[] = $frmoptarr;
                        } else {
                            foreach( $arfinnerfieldorder[$frmoptarr->id] as $inner_field_order ){
                                $exploded_data = explode('|',$inner_field_order);
                                $inner_field_id = (int)$exploded_data[0];
                                foreach( $form_cols as $ifrmoptkey => $ifrmoptarr ){
                                    if( $ifrmoptarr->id == $inner_field_id ){
                                        $form_cols_temp[] = $ifrmoptarr;
                                        unset($form_cols[$ifrmoptkey]);
                                    }
                                }
                            }
                        }
                        unset($form_cols[$frmoptkey]);
                    }

                    if( $inside_repeater && isset( $frmoptarr->field_options['parent_field_type'] ) && $frmoptarr->field_options['parent_field_type'] == 'arf_repeater' ){
                        unset( $form_cols[$frmoptkey] );
                    }

                    if( $inside_html ){
                        unset( $form_cols[$frmoptkey] );
                    }
                }
            }

            if(count($form_cols_temp) > 0) {
                if(count($form_cols) > 0) {
                    $form_cols_other = $form_cols;
                    $form_cols = array_merge($form_cols_temp,$form_cols_other);
                } else {
                    $form_cols = $form_cols_temp;
                }
            }
        }

        global $style_settings, $wp_scripts;
        $wp_format_date = get_option('date_format');

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'mm/dd/yy';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'dd/mm/yy';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'dd/mm/yy';
        } else {
            $date_format_new = 'mm/dd/yy';
        }
        $new_start_date = $start_date;
        $new_end_date = $end_date;
        $show_new_start_date = $new_start_date;
        $show_new_end_date = $new_end_date;


        $arf_db_columns = array('0' => '', '1' => 'id');

        $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $form_id);

        $arf_sorting_array = array();

        if (count($form_cols) > 0) {
            for ($col_i = 2; $col_i <= count($form_cols) + 1; $col_i++) {
                $col_j = $col_i - 2;
                $arf_db_columns[$col_i] = $armainhelper->truncate($form_cols[$col_j]->name, 40);
                $arf_sorting_array[$form_cols[$col_j]->id] = $col_i;
            }
            $arf_db_columns[$col_i] = 'entry_key';
            $arf_db_columns[$col_i + 1] = 'created_date';
            $arf_db_columns[$col_i + 2] = 'browser_info';
            $arf_db_columns[$col_i + 3] = 'ip_address';
            $arf_db_columns[$col_i + 4] = 'country';
            $arf_db_columns[$col_i + 5] = 'Page URL';
            $arf_db_columns[$col_i + 6] = 'Referrer URL';
            $arf_db_columns[$col_i + 7] = 'Action';

        } else {
            $arf_db_columns['2'] = 'entry_key';
            $arf_db_columns['3'] = 'created_date';
            $arf_db_columns['4'] = 'browser_info';
            $arf_db_columns['5'] = 'ip_address';
            $arf_db_columns['6'] = 'country';
            $arf_db_columns['7'] = 'Page URL';
            $arf_db_columns['8'] = 'Referrer URL';
            $arf_db_columns['9'] = 'Action';
        }

        $arforderbycolumn = isset($arf_db_columns[$sorting_column]) ? $arf_db_columns[$sorting_column] : 'id';
        $item_order_by = " ORDER BY it.$arforderbycolumn $sorting_order";

        $where_clause = "it.form_id=".$form_id;

        if ($new_start_date != '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $where_clause .= " and DATE(it.created_date) >= '" . $new_start_date_var . "' and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        } else if ($new_start_date != '' and $new_end_date == '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $where_clause .= " and DATE(it.created_date) >= '" . $new_start_date_var . "'";
        } else if ($new_start_date == '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $where_clause .= " and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        }

        $total_records = wp_cache_get( 'arf_total_entries_' . $form_id );
        if( false == $total_records ){
            $total_records = $wpdb->get_var("SELECT count(*) as total_entries FROM `".$MdlDb->entries."` it WHERE ".$where_clause);
            wp_cache_set( 'arf_total_entries_' . $form_id, $total_records);
        }
        
        $item_order_by .= " LIMIT {$offset},{$limit}";
        if( isset($arf_sorting_array) && !empty($arf_sorting_array) && in_array($sorting_column,$arf_sorting_array) ){
            $temp_items = $db_record->getPage('', '', $where_clause, '', $searchStr, $arffieldorder);
            $temp_field_metas = array();
            $sorting_value = array_search($sorting_column, $arf_sorting_array);
            foreach( $temp_items as $K => $I ){
                foreach( $arf_sorting_array as $a => $b ){
                    $temp_field_metas[$K][$a] = !empty( $I->metas[$a] ) ? $I->metas[$a] : '';
                    $temp_field_metas[$K]['sorting_column'] = $sorting_value;
                }
            }
                        
            if( $sorting_order == 'asc' ){
                uasort( $temp_field_metas, function($a, $b){
                    $sort_on = $a['sorting_column'];
                    return strnatcasecmp($a[$sort_on],$b[$sort_on]);
                });
            } else {
                uasort( $temp_field_metas, function($a, $b){
                    $sort_on = $a['sorting_column'];
                    return strnatcasecmp($b[$sort_on],$a[$sort_on]);
                });
            }
            $sorted_columns = array();
            $counter = 0;

            foreach( $temp_field_metas as $c => $d ){
            	$sorted_columns[$c] = $temp_items[$c];
                $counter++;
            }
            $sorted_cols = array_chunk($sorted_columns, $limit);

            $chuncked_array_key = ceil($offset / $limit) + 1;

            $chunk_key = $chuncked_array_key - 1;
            $items = $sorted_cols[$chunk_key];

        } else {

            $items = $db_record->getPage('', '', $where_clause, $item_order_by, $searchStr, $arffieldorder);
        }

        $action_no = 0;

        if( is_rtl() ){
            $divStyle = "display:inline-block;position:relative;";
        } else {
            $divStyle = "position:relative;width:100%;text-align:center;";
        }

        $default_hide = array(
            '0' => '<div style="'.$divStyle.'"><div class="arf_custom_checkbox_div arfmarginl15"><div class="arf_custom_checkbox_wrapper arfmargin10custom"><input id="cb-select-all-1" type="checkbox" class=""><svg width="18px" height="18px">'.ARF_CUSTOM_UNCHECKED_ICON.'
                                '.ARF_CUSTOM_CHECKED_ICON.'</svg></div></div>
            <label for="cb-select-all-1"  class="cb-select-all"><span class="cb-select-all-checkbox"></span></label></div>',
            '1' => 'ID'
        );

        $items = apply_filters('arfpredisplaycolsitems', $items, $form_id);

        if (count($form_cols) > 0) {
            for ($i = 2; $i <= count($form_cols) + 1; $i++) {
                $j = $i - 2;
                $default_hide[$i] = $armainhelper->truncate($form_cols[$j]->name, 40);
            }
            $default_hide[$i] = 'Entry key';
            $default_hide[$i + 1] = 'Entry Creation Date';
            $default_hide[$i + 2] = 'Browser Name';
            $default_hide[$i + 3] = 'IP Address';
            $default_hide[$i + 4] = 'Country';
            $default_hide[$i + 5] = 'Page URL';
            $default_hide[$i + 6] = 'Referrer URL';
            $default_hide[$i + 7] = 'Action';
            $action_no = $i + 7;
        } else {
            $default_hide['2'] = 'Entry Key';
            $default_hide['3'] = 'Entry creation date';
            $default_hide['4'] = 'Browser Name';
            $default_hide['5'] = 'IP Address';
            $default_hide['6'] = 'Country';
            $default_hide['7'] = 'Page URL';
            $default_hide['8'] = 'Referrer URL';
            $default_hide['9'] = 'Action';
            $action_no = 9;
        }


        $columns_list_res = $wpdb->get_results($wpdb->prepare('SELECT columns_list FROM ' . $MdlDb->forms . ' WHERE id = %d', $form_id), ARRAY_A);

        $columns_list_res = $columns_list_res[0];

        $columns_list = maybe_unserialize($columns_list_res['columns_list']);

        $is_colmn_array = is_array($columns_list);

        $exclude = '';

        $exclude_array = array();

        if ($is_colmn_array && count($columns_list) > 0 and $columns_list != '') {

            foreach ($columns_list as $keys => $column) {

                foreach ($default_hide as $key => $val) {

                    if ($column == $val) {
                        if ($exclude_array == "") {
                            $exclude_array[] = $key;
                        } else {
                            if (!in_array($key, $exclude_array)) {
                                $exclude_array[] = $key;

                            }
                        }
                    }
                }
            }
        }

        $ipcolumn = ($action_no - 4);
        $page_url_column = ($action_no - 2);
        $referrer_url_column = ($action_no - 1);

        if ($exclude_array == "" and ! $is_colmn_array) {
            $exclude_array = array($ipcolumn, $page_url_column, $referrer_url_column);
        } else if (is_array($exclude_array) and ! $is_colmn_array) {

            if (!in_array($ipcolumn, $exclude_array)) {
                array_push($exclude_array, $ipcolumn);
            }
            if (!in_array($page_url_column, $exclude_array)) {
                array_push($exclude_array, $page_url_column);
            }
            if (!in_array($referrer_url_column, $exclude_array)) {
                array_push($exclude_array, $referrer_url_column);
            }
        }

        if ($exclude_array != "") {
            $exclude = implode(",", $exclude_array);
        }

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        }

        $data = array();

        if( count($items) > 0 ){
            $ai = 0;
            $arf_edit_select_array = array();
            $arf_edit_matrix_array = array();
            foreach ($items as $key => $item) {
                if( is_rtl() ){
                    $divStyle = "display:inline-block;position:relative;";
                } else {
                    $divStyle = "position:relative;width:100%;text-align:center;";
                }
                $data[$ai][0] = "<div class='DataTables_sort_wrapper'><div style='{$divStyle}'>
                       <div class='arf_custom_checkbox_div arfmarginl15'><div class='arf_custom_checkbox_wrapper'><input id='cb-item-action-{$item->id}' class='' type='checkbox' value='{$item->id}' name='item-action[]' />
                                        <svg width='18px' height='18px'>
                                        ".ARF_CUSTOM_UNCHECKED_ICON."
                                        ".ARF_CUSTOM_CHECKED_ICON."
                                        </svg>
                                    </div>
                                </div>
                    <label for='cb-item-action-{$item->id}'><span></span></label></div></div>" ;
                $data[$ai][1] = $item->id;
                $ni = 2;
                $repeater_field_view_entries = '';
                $matrix_field_view_entries = '';

                foreach ($form_cols as $col) {
                    $field_value = isset($item->metas[$col->id]) ? $item->metas[$col->id] : false;
                    
                    if( !is_array($col->field_options) ){
                        $col->field_options = json_decode($col->field_options,true);
                    }

                    if( !is_array($col->options) ){
                        $col->options = json_decode($col->options,true);
                    }
                    
                    if ($col->type == 'checkbox' || $col->type == 'radio' || $col->type == 'select' || $col->type == 'arf_multiselect' || $col->type == 'arf_autocomplete') {
                        if (isset($col->field_options['separate_value']) && $col->field_options['separate_value'] == '1') {
                            $option_separate_value = array();

                            foreach ($col->options as $k => $options) {
                                $option_separate_value[] = array('value' => htmlentities($options['value']), 'text' => $options['label']);
                            }
                            $arf_edit_select_array[] = array($col->id => json_encode($option_separate_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                        } else {
                            $option_value = '';
                            $option_value = array();
                            if(is_array($col->options))
                            {
                                foreach ($col->options as $k => $options) {
                                    if (is_array($options)) {
                                        $option_value[] = ($options['label']);
                                    } else {
                                        $option_value[] = ($options);
                                    }
                                }
                            }
                            $arf_edit_select_array[] = array($col->id => json_encode($option_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                        }
                    }

                    $atts_param = array(
                        'type' => $col->type,
                        'truncate' => true,
                        'attachment_id' => $item->attachment_id,
                        'entry_id' => $item->id
                    );

                    if( 'arf_repeater' == $col->type ){
                        $repeater_field_view_entries .= "<div id='view_repeater_entry_detail_container_{$item->id}_{$col->id}' style='display:none;'>".$this->get_repeater_entries_list_edit( $item->id, $col->id, $arfinnerfieldorder, false, $atts_param, $form_css )."</div><div style='clear:both;' class='arfmnarginbtm10'></div>";
                        $field_value = ' - ';
                        $arf_edit_select_array = apply_filters( 'arf_change_edit_select_array_for_repeater', $arf_edit_select_array, $item->id, $col->id, $arfinnerfieldorder );
                    } else if( 'matrix' == $col->type ){
                        $matrix_field_view_entries .= '<div id="view_matrix_entry_detail_container_' . $item->id . '_' . $col->id .'" style="display:none;">'. $arf_matrix->get_matrix_entries_list_edit( $item->id, $col->id, false, $atts_param, $form_css ).'</div><div style="clear:both;" class="arfmnarginbtm10"></div>';
                        $arf_edit_matrix_array = apply_filters( 'arf_change_edit_select_array_for_matrix', $arf_edit_matrix_array, $item->id, $col->id );
                    }

                    global $arrecordhelper;

                    $data[$ai][$ni] = $arrecordhelper->display_value($field_value, $col, $atts_param,$form_css, false, $form_name);
                    $ni++;
                }
                $data[$ai][$ni] = $item->entry_key;
                $data[$ai][$ni + 1] = date(get_option('date_format'), strtotime($item->created_date));
                $browser_info = $this->getBrowser($item->browser_info);
                $data[$ai][$ni + 2] = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
                $data[$ai][$ni + 3] = $item->ip_address;
                $data[$ai][$ni + 4] = $item->country;
                $http_referrer = maybe_unserialize($item->description);
                $data[$ai][$ni + 5] = isset( $http_referrer['page_url'] ) ? urldecode($http_referrer['page_url']) : '';
                $data[$ai][$ni + 6] = isset( $http_referrer['http_referrer'] ) ? urldecode($http_referrer['http_referrer']) : '';

                $view_entry_icon = is_rtl() ? 'view_icon23_rtl.png' : 'view_icon23.png';
                $view_entry_icon_hover = is_rtl() ? 'view_icon23_hover_rtl.png' : 'view_icon23_hover.png';

                $view_entry_btn = "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Preview', 'ARForms')) . "'><a href='javascript:void(0);'  class='arf_view_entry' onclick='open_entry_thickbox({$item->id},\"".htmlentities($form_name, ENT_QUOTES)."\");'><svg width='30px' height='30px' viewBox='-3 -8 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M12.993,15.23c-7.191,0-11.504-7.234-11.504-7.234  S5.801,0.85,12.993,0.85c7.189,0,11.504,7.19,11.504,7.19S20.182,15.23,12.993,15.23z M12.993,2.827  c-5.703,0-8.799,5.214-8.799,5.214s3.096,5.213,8.799,5.213c5.701,0,8.797-5.213,8.797-5.213S18.694,2.827,12.993,2.827z   M12.993,11.572c-1.951,0-3.531-1.581-3.531-3.531s1.58-3.531,3.531-3.531c1.949,0,3.531,1.581,3.531,3.531  S14.942,11.572,12.993,11.572z'/></svg></a></div>";

                global $PDF_button;
                do_action('arf_additional_action_entries', $item->id, $form_id,true);
                global $PDF_button;
                
                $id = $item->id;

                $delete_entry_icon = is_rtl() ? 'delete_icon223_rtl.png' : 'delete_icon223.png';
                $delete_entry_icon_hover = is_rtl() ? 'delete_icon223_hover_rtl.png' : 'delete_icon223_hover.png';
                $delete_entry_btn = '';
                if( current_user_can('arfdeleteentries') ){
                    $delete_entry_btn = "<div class='arfformicondiv arfhelptip arfentry_delete_div_".$item->id."' title='" . addslashes(esc_html__('Delete', 'ARForms')) . "'><a data-id='".$item->id."' id='arf_delete_single_entry' style='cursor:pointer'><svg width='30px' height='30px' viewBox='-5 -5 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z' /></svg></a></div>";
                }

                $delete_entry_overlay = "<div id='view_entry_detail_container_{$item->id}' style='display:none;'>" . $this->get_entries_list_edit($item->id,$arffieldorder,$arfinnerfieldorder) . "</div><div style='clear:both;' class='arfmnarginbtm10'></div>";


                $data[$ai][$ni + 7] = "<div class='arf-row-actions'>{$view_entry_btn}{$PDF_button}{$delete_entry_btn} {$delete_entry_overlay} {$repeater_field_view_entries} {$matrix_field_view_entries} <input type='hidden' id='arf_edit_select_array_one' value='" . json_encode($arf_edit_select_array) . "' /></div>";
                $data[$ai][$ni + 7] .= "<input type='hidden' id='arf_edit_select_array_{$item->id}' value='".json_encode($arf_edit_select_array)."' />";
                $data[$ai][$ni + 7] .= "<input type='hidden' id='arf_edit_matrix_array_{$item->id}' value='".json_encode($arf_edit_matrix_array)."' />";
                $PDF_button = '';
                $action_no = $ni + 7;
                $ai++;
            }
            $sEcho = isset($filtered_aoData['sEcho']) ? intval($filtered_aoData['sEcho']) : intval(10);

            $return_data = array(
                'sEcho' => $sEcho,
                'iTotalRecords' => (int)$total_records,
                'iTotalDisplayRecords' => (int)$total_records,
                'aaData' => $data,
            );

        } else {
            $sEcho = isset($filtered_aoData['sEcho']) ? intval($filtered_aoData['sEcho']) : intval(10);
            $return_data = array(
                'sEcho' => $sEcho,
                'iTotalRecords' => (int)$total_records,
                'iTotalDisplayRecords' => (int)$total_records,
                'aaData' => $data,
            );
        }

        echo json_encode( $return_data );

        die;
    }

    function arf_change_edit_select_array_for_repeater_callback( $arf_edit_select_array, $item_id, $col_id, $arfinnerfieldorder ){

        
        global $db_record, $arffield, $arfrecordmeta, $user_ID, $armainhelper, $arrecordhelper, $wpdb, $MdlDb;

        if( !isset( $id ) ){
            $id = $armainhelper->get_param('id');
        }

        if( !$id ){
            $id = $armainhelper->get_param( 'entry_id' );
        }

        $entry = $db_record->getOne( $id, true );

        $updated_order = array();

        $get_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT id,type,options,field_options FROM `".$MdlDb->fields."` WHERE field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%'", $col_id, $col_id ) );

        foreach( $get_inner_fields as $inner_field ){

            $field_opts = json_decode( $inner_field->field_options, true );
            
            if( 'checkbox' == $inner_field->type || 'radio' == $inner_field->type || 'arf_multiselect' == $inner_field->type || 'select' == $inner_field->type || 'arf_autocomplete' == $inner_field->type ){
                $fopts = json_decode( $inner_field->options, true );
                
                if( isset( $field_opts['separate_value'] ) && '1' == $field_opts['separate_value'] ){

                    $option_separate_value = array();

                    foreach( $fopts as $options ){
                        $option_separate_value[] = array( 'value' => htmlentities( $options['value'] ), 'text' => $options['label'] );
                    }

                    $arf_edit_select_array[] = array( $inner_field->id => json_encode( $option_separate_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) );
                } else {
                    $option_value = array();
                    if( is_array( $fopts ) ){
                        foreach( $fopts as $options ){
                            if( is_array( $options ) ){
                                $option_value[] = $options['label'];
                            } else {
                                $option_value[] = $options;
                            }
                        }
                    }
                    $arf_edit_select_array[] = array( $inner_field->id => json_encode($option_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                }
            }

        }

        return $arf_edit_select_array;
    }

    function arf_retrieve_incomplete_form_entry_data(){
        global $wpdb, $MdlDb, $arffield, $armainhelper, $db_record, $arfform,$arfpagesize, $arf_matrix;
        
        $requested_data = json_decode(stripslashes_deep($_REQUEST['data']),true);

        $filtered_aoData = $requested_data['aoData'];

        $form_id = isset($filtered_aoData['form']) ? $filtered_aoData['form'] : '-1';
        $start_date = isset($filtered_aoData['start_date']) ? $filtered_aoData['start_date'] : '';
        $end_date = isset($filtered_aoData['end_date']) ? $filtered_aoData['end_date'] : '';

        $return_data = array();

        $form_select = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$MdlDb->forms."` WHERE `id` = %d AND `is_template` != %d AND `status` = %s", $form_id, 1,'published') );

        $form_name = $form_select->name;

        $form_css = maybe_unserialize($form_select->form_css);

        $form_options = maybe_unserialize($form_select->options, true);

        $arffieldorder = array();

        $arfinnerfieldorder = array();

        if(isset($form_options['arf_field_order']) && $form_options['arf_field_order'] != ''){
            $arffieldorder = json_decode($form_options['arf_field_order'], true);
            asort($arffieldorder);
        }


        if( isset( $form_options['arf_inner_field_order'] ) && $form_options['arf_inner_field_order'] != '' ){
            $form_cols_inner_order = json_decode( $form_options['arf_inner_field_order'], true );
            asort( $form_cols_inner_order );
            $arfinnerfieldorder = $form_cols_inner_order;
        }

        $offset = isset($filtered_aoData['iDisplayStart']) ? $filtered_aoData['iDisplayStart'] : 0;
        $limit = isset($filtered_aoData['iDisplayLength']) ? $filtered_aoData['iDisplayLength'] : 10;

        $searchStr = isset($filtered_aoData['sSearch']) ? $filtered_aoData['sSearch'] : '';
        $sorting_order = isset($filtered_aoData['sSortDir_0']) ? $filtered_aoData['sSortDir_0'] : 'desc';
        $sorting_column = (isset($filtered_aoData['iSortCol_0']) && $filtered_aoData['iSortCol_0'] > 0) ? $filtered_aoData['iSortCol_0'] : 1;

        $form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'imagecontrol') and fi.form_id=" . (int) $form_id, ' ORDER BY id');

        if(count($arffieldorder) > 0){
            $form_cols_temp = array();

            foreach ($arffieldorder as $fieldkey => $fieldorder) {
                foreach ($form_cols as $frmoptkey => $frmoptarr) {
                    $inside_repeater = ( isset( $frmoptarr->field_options['parent_field_type'] ) && 'arf_repeater' == $frmoptarr->field_options['parent_field_type'] ) ? true : false;
                      $inside_html = ( isset( $frmoptarr->field_options['enable_total'] ) && '0' == $frmoptarr->field_options['enable_total'] ) ? true : false;

                    if($frmoptarr->id == $fieldkey){
                        if( 'section' != $frmoptarr->type ){
                            $form_cols_temp[] = $frmoptarr;
                        } else {
                            foreach( $arfinnerfieldorder[$frmoptarr->id] as $inner_field_order ){
                                $exploded_data = explode('|',$inner_field_order);
                                $inner_field_id = (int)$exploded_data[0];
                                foreach( $form_cols as $ifrmoptkey => $ifrmoptarr ){
                                    if( $ifrmoptarr->id == $inner_field_id ){
                                        $form_cols_temp[] = $ifrmoptarr;
                                        unset($form_cols[$ifrmoptkey]);
                                    }
                                }
                            }
                        }
                        unset($form_cols[$frmoptkey]);
                    }

                    if( $inside_repeater && isset( $frmoptarr->field_options['parent_field_type'] ) && $frmoptarr->field_options['parent_field_type'] == 'arf_repeater' ){
                        unset( $form_cols[$frmoptkey] );
                    }
                    if( $inside_html ){
                        unset( $form_cols[$frmoptkey] );
                    }
                }
            }

            if(count($form_cols_temp) > 0) {
                if(count($form_cols) > 0) {
                    $form_cols_other = $form_cols;
                    $form_cols = array_merge($form_cols_temp,$form_cols_other);
                } else {
                    $form_cols = $form_cols_temp;
                }
            }
        }

        global $style_settings, $wp_scripts;
        $wp_format_date = get_option('date_format');

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'mm/dd/yy';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'dd/mm/yy';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'dd/mm/yy';
        } else {
            $date_format_new = 'mm/dd/yy';
        }
        $new_start_date = $start_date;
        $new_end_date = $end_date;
        $show_new_start_date = $new_start_date;
        $show_new_end_date = $new_end_date;


        $arf_db_columns = array('0' => '', '1' => 'id');

        $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $form_id);

        $arf_sorting_array = array();

        if (count($form_cols) > 0) {
            for ($col_i = 2; $col_i <= count($form_cols) + 1; $col_i++) {
                $col_j = $col_i - 2;
                $arf_db_columns[$col_i] = $armainhelper->truncate($form_cols[$col_j]->name, 40);
                $arf_sorting_array[$form_cols[$col_j]->id] = $col_i;
            }
            $arf_db_columns[$col_i] = 'entry_key';
            $arf_db_columns[$col_i + 1] = 'created_date';
            $arf_db_columns[$col_i + 2] = 'browser_info';
            $arf_db_columns[$col_i + 3] = 'ip_address';
            $arf_db_columns[$col_i + 4] = 'country';
            $arf_db_columns[$col_i + 5] = 'Page URL';
            $arf_db_columns[$col_i + 6] = 'Referrer URL';
            $arf_db_columns[$col_i + 7] = 'Action';

        } else {
            $arf_db_columns['2'] = 'entry_key';
            $arf_db_columns['3'] = 'created_date';
            $arf_db_columns['4'] = 'browser_info';
            $arf_db_columns['5'] = 'ip_address';
            $arf_db_columns['6'] = 'country';
            $arf_db_columns['7'] = 'Page URL';
            $arf_db_columns['8'] = 'Referrer URL';
            $arf_db_columns['9'] = 'Action';
        }

        $arforderbycolumn = isset($arf_db_columns[$sorting_column]) ? $arf_db_columns[$sorting_column] : 'id';
        $item_order_by = " ORDER BY it.$arforderbycolumn $sorting_order";

        $where_clause = "it.form_id=".$form_id;

        if ($new_start_date != '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $where_clause .= " and DATE(it.created_date) >= '" . $new_start_date_var . "' and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        } else if ($new_start_date != '' and $new_end_date == '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $where_clause .= " and DATE(it.created_date) >= '" . $new_start_date_var . "'";
        } else if ($new_start_date == '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $where_clause .= " and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        }


        $total_records = wp_cache_get('arf_total_incomplete_entries_' . $form_id);
        if( false == $total_records ){
            $total_records = $wpdb->get_var("SELECT count(*) as total_entries FROM `".$MdlDb->incomplete_entries."` it WHERE ".$where_clause);
            wp_cache_set( 'arf_total_incomplete_entries_' . $form_id, $total_records);
        }
        
        $item_order_by .= " LIMIT {$offset},{$limit}";
        if( isset($arf_sorting_array) && !empty($arf_sorting_array) && in_array($sorting_column,$arf_sorting_array) ){
            $temp_items = $db_record->getPage('', '', $where_clause, '', $searchStr, $arffieldorder, true);
            $temp_field_metas = array();
            $sorting_value = array_search($sorting_column, $arf_sorting_array);
            foreach( $temp_items as $K => $I ){
                foreach( $arf_sorting_array as $a => $b ){
                    $temp_field_metas[$K][$a] = $I->metas[$a];
                    $temp_field_metas[$K]['sorting_column'] = $sorting_value;
                }
            }
                        
            if( $sorting_order == 'asc' ){
                uasort( $temp_field_metas, function($a, $b){
                    $sort_on = $a['sorting_column'];
                    return strnatcasecmp($a[$sort_on],$b[$sort_on]);
                });
            } else {
                uasort( $temp_field_metas, function($a, $b){
                    $sort_on = $a['sorting_column'];
                    return strnatcasecmp($b[$sort_on],$a[$sort_on]);
                });
            }
            $sorted_columns = array();
            $counter = 0;

            foreach( $temp_field_metas as $c => $d ){
                $sorted_columns[$c] = $temp_items[$c];
                $counter++;
            }
            $sorted_cols = array_chunk($sorted_columns, $limit);

            $chuncked_array_key = ceil($offset / $limit) + 1;

            $chunk_key = $chuncked_array_key - 1;
            $items = $sorted_cols[$chunk_key];

        } else {

            $items = $db_record->getPage('', '', $where_clause, $item_order_by, $searchStr, $arffieldorder,true);
        }

        $action_no = 0;

        if( is_rtl() ){
            $divStyle = "display:inline-block;position:relative;";
        } else {
            $divStyle = "position:relative;width:100%;text-align:center;";
        }

        $default_hide = array(
            '0' => '<div style="'.$divStyle.'"><div class="arf_custom_checkbox_div arfmarginl15"><div class="arf_custom_checkbox_wrapper arfmargin10custom"><input id="cb-select-all-1" type="checkbox" class=""><svg width="18px" height="18px">'.ARF_CUSTOM_UNCHECKED_ICON.'
                                '.ARF_CUSTOM_CHECKED_ICON.'</svg></div></div>
            <label for="cb-select-all-1"  class="cb-select-all"><span class="cb-select-all-checkbox"></span></label></div>',
            '1' => 'ID'
        );

        $items = apply_filters('arfpredisplaycolsitems', $items, $form_id);

        if (count($form_cols) > 0) {
            for ($i = 2; $i <= count($form_cols) + 1; $i++) {
                $j = $i - 2;
                $default_hide[$i] = $armainhelper->truncate($form_cols[$j]->name, 40);
            }
            $default_hide[$i] = 'Entry key';
            $default_hide[$i + 1] = 'Entry Creation Date';
            $default_hide[$i + 2] = 'Browser Name';
            $default_hide[$i + 3] = 'IP Address';
            $default_hide[$i + 4] = 'Country';
            $default_hide[$i + 5] = 'Page URL';
            $default_hide[$i + 6] = 'Referrer URL';
            $default_hide[$i + 7] = 'Action';

            $action_no = $i + 7;
        } else {
            $default_hide['2'] = 'Entry Key';
            $default_hide['3'] = 'Entry creation date';
            $default_hide['4'] = 'Browser Name';
            $default_hide['5'] = 'IP Address';
            $default_hide['6'] = 'Country';
            $default_hide['7'] = 'Page URL';
            $default_hide['8'] = 'Referrer URL';
            $default_hide['9'] = 'Action';
            $action_no = 9;
        }


        $columns_list_res = $wpdb->get_results($wpdb->prepare('SELECT partial_grid_column_list FROM ' . $MdlDb->forms . ' WHERE id = %d', $form_id), ARRAY_A);

        $columns_list_res = $columns_list_res[0];

        $columns_list = maybe_unserialize($columns_list_res['partial_grid_column_list']);

        $is_colmn_array = is_array($columns_list);

        $exclude = '';

        $exclude_array = array();

        if ($is_colmn_array && count($columns_list) > 0 and $columns_list != '') {

            foreach ($columns_list as $keys => $column) {

                foreach ($default_hide as $key => $val) {

                    if ($column == $val) {
                        if ($exclude_array == "") {
                            $exclude_array[] = $key;
                        } else {
                            if (!in_array($key, $exclude_array)) {
                                $exclude_array[] = $key;

                            }
                        }
                    }
                }
            }
        }

        $ipcolumn = ($action_no - 4);
        $page_url_column = ($action_no - 2);
        $referrer_url_column = ($action_no - 1);

        if ($exclude_array == "" and ! $is_colmn_array) {
            $exclude_array = array($ipcolumn, $page_url_column, $referrer_url_column);
        } else if (is_array($exclude_array) and ! $is_colmn_array) {

            if (!in_array($ipcolumn, $exclude_array)) {
                array_push($exclude_array, $ipcolumn);
            }
            if (!in_array($page_url_column, $exclude_array)) {
                array_push($exclude_array, $page_url_column);
            }
            if (!in_array($referrer_url_column, $exclude_array)) {
                array_push($exclude_array, $referrer_url_column);
            }
        }

        if ($exclude_array != "") {
            $exclude = implode(",", $exclude_array);
        }

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        }

        $data = array();

        if( count($items) > 0 ){
            $ai = 0;
            $arf_edit_select_array = array();
            $arf_edit_matrix_array = array();
            foreach ($items as $key => $item) {
                if( is_rtl() ){
                    $divStyle = "display:inline-block;position:relative;";
                } else {
                    $divStyle = "position:relative;width:100%;text-align:center;";
                }
                $data[$ai][0] = "<div class='DataTables_sort_wrapper'><div style='{$divStyle}'>
                       <div class='arf_custom_checkbox_div arfmarginl15'><div class='arf_custom_checkbox_wrapper'><input id='cb-item-action-{$item->id}' class='' type='checkbox' value='{$item->id}' name='item-action[]' />
                                        <svg width='18px' height='18px'>
                                        ".ARF_CUSTOM_UNCHECKED_ICON."
                                        ".ARF_CUSTOM_CHECKED_ICON."
                                        </svg>
                                    </div>
                                </div>
                    <label for='cb-item-action-{$item->id}'><span></span></label></div></div>" ;
                $data[$ai][1] = $item->id;
                $ni = 2;
                $repeater_field_view_entries = '';
                $matrix_field_view_entries = '';
                foreach ($form_cols as $col) {
                    $field_value = isset($item->metas[$col->id]) ? $item->metas[$col->id] : false;
                    if( !is_array($col->field_options) ){
                        $col->field_options = json_decode($col->field_options,true);
                    }

                    if( !is_array($col->options) ){
                        $col->options = json_decode($col->options,true);
                    }
                    
                    if ($col->type == 'checkbox' || $col->type == 'radio' || $col->type == 'select' || $col->type == 'arf_multiselect' || $col->type == 'arf_autocomplete') {
                        if (isset($col->field_options['separate_value']) && $col->field_options['separate_value'] == '1') {
                            $option_separate_value = array();

                            foreach ($col->options as $k => $options) {
                                $option_separate_value[] = array('value' => htmlentities($options['value']), 'text' => $options['label']);
                            }
                            $arf_edit_select_array[] = array($col->id => json_encode($option_separate_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                        } else {
                            $option_value = '';
                            $option_value = array();
                            if(is_array($col->options))
                            {
                                foreach ($col->options as $k => $options) {
                                    if (is_array($options)) {
                                        $option_value[] = ($options['label']);
                                    } else {
                                        $option_value[] = ($options);
                                    }
                                }
                            }
                            $arf_edit_select_array[] = array($col->id => json_encode($option_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                        }
                    }

                    $param_atts = array(
                        'type' => $col->type,
                        'truncate' => true,
                        'attachment_id' => ( isset( $item->attachment_id ) ? $item->attachment_id : '' ),
                        'entry_id' => $item->id
                    );

                    if( 'arf_repeater' == $col->type ){
                        $repeater_field_view_entries .= "<div id='view_repeater_incomplete_entry_detail_container_{$item->id}_{$col->id}' style='display:none;'>".$this->get_repeater_entries_list_edit( $item->id, $col->id, $arfinnerfieldorder, true, $param_atts, $form_css )."</div><div style='clear:both;' class='arfmnarginbtm10'></div>";
                        $field_value = ' - ';
                        $arf_edit_select_array = apply_filters( 'arf_change_edit_select_array_for_repeater', $arf_edit_select_array, $item->id, $col->id, $arfinnerfieldorder );
                    } else if( 'matrix' == $col->type ){
                        $matrix_field_view_entries .= '<div id="view_matrix_entry_detail_container_' . $item->id . '_' . $col->id .'" style="display:none;">'. $arf_matrix->get_matrix_entries_list_edit( $item->id, $col->id, true, $atts_param, $form_css ).'</div><div style="clear:both;" class="arfmnarginbtm10"></div>';
                        $arf_edit_matrix_array = apply_filters( 'arf_change_edit_select_array_for_matrix', $arf_edit_matrix_array, $item->id, $col->id );
                    }

                    global $arrecordhelper;


                    $data[$ai][$ni] = $arrecordhelper->display_value($field_value, $col, $param_atts,$form_css, true, $form_name);
                    $ni++;
                }
                $data[$ai][$ni] = isset( $item->entry_key ) ? $item->entry_key : '';
                $data[$ai][$ni + 1] = date(get_option('date_format'), strtotime($item->created_date));
                $browser_info = $this->getBrowser($item->browser_info);
                $data[$ai][$ni + 2] = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
                $data[$ai][$ni + 3] = $item->ip_address;
                $data[$ai][$ni + 4] = $item->country;
                $http_referrer = maybe_unserialize($item->description);
                $data[$ai][$ni + 5] = urldecode($http_referrer['page_url']);
                $data[$ai][$ni + 6] = urldecode($http_referrer['http_referrer']);

                
                
                $id = $item->id;

                $delete_entry_btn = '';
                $move_entry_btn = '';

                if( current_user_can('arfdeleteincompleteentries') ){
                    $delete_entry_btn = "<div class='arfformicondiv arfhelptip arfentry_delete_div_".$item->id."' title='" . addslashes(esc_html__('Delete', 'ARForms')) . "'><a data-id='".$item->id."' id='arf_delete_single_incomplete_entry' style='cursor:pointer'><svg width='30px' height='30px' viewBox='-5 -5 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z' /></svg></a></div>";

                }

                $view_entry_btn = "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Preview', 'ARForms')) . "'><a href='javascript:void(0);'  class='arf_incomplete_view_entry' onclick='open_partial_entry_thickbox({$item->id},\"".htmlentities($form_name, ENT_QUOTES)."\");'><svg width='30px' height='30px' viewBox='-3 -8 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M12.993,15.23c-7.191,0-11.504-7.234-11.504-7.234  S5.801,0.85,12.993,0.85c7.189,0,11.504,7.19,11.504,7.19S20.182,15.23,12.993,15.23z M12.993,2.827  c-5.703,0-8.799,5.214-8.799,5.214s3.096,5.213,8.799,5.213c5.701,0,8.797-5.213,8.797-5.213S18.694,2.827,12.993,2.827z   M12.993,11.572c-1.951,0-3.531-1.581-3.531-3.531s1.58-3.531,3.531-3.531c1.949,0,3.531,1.581,3.531,3.531  S14.942,11.572,12.993,11.572z'/></svg></a></div>";

                if( current_user_can('arfeditincompleteentries') ){
                    $move_entry_btn = "<div class='arfformicondiv arfhelptip arfentry_move_to_entry_div_" . $item->id . "' title='" . addslashes( esc_html__( 'Move to Entries List', 'ARForms' ) ) . "'><a data-form-id='" . $form_id . "' data-id='" . $item->id . "' id='arf_move_single_incomplete_entry' style='cursor:pointer'><svg width='34px' height='34px' viewBox='-9 -9 36 36' class='arfsvgposition'><g xmlns='http://www.w3.org/2000/svg'><polygon fill='#ffffff' points='19,15 15,11 11,15 12.4,16.4 14,14.8 14,20 16,20 16,14.8 17.6,16.4'></polygon><polygon fill='#ffffff' points='3,2 15,2 15,10 17,10 17,0 1,0 1,18 11,18 11,16 3,16'></polygon><rect x='5' y='4' fill='#fff' width='8' height='2'></rect><rect x='5' y='8' fill='#fff' width='8' height='2'></rect><rect x='5' y='12' fill='#fff' width='5' height='2'></rect></g></svg></a></div>";
                }

                $view_incomplete_entry_overlay = "<div id='view_partial_entry_detail_container_{$item->id}' style='display:none;'>" . $this->get_incomplete_entries_list_view($item->id,$arffieldorder,$arfinnerfieldorder, $form_css, true, $form_name) . "</div><div style='clear:both;' class='arfmnarginbtm10'></div>";

                $data[$ai][$ni + 7] = "<div class='arf-row-actions'>{$view_incomplete_entry_overlay}{$view_entry_btn} {$move_entry_btn} {$delete_entry_btn} {$repeater_field_view_entries} {$matrix_field_view_entries}<input type='hidden' id='arf_edit_select_array_one' value='" . json_encode($arf_edit_select_array) . "' /></div>";
                $action_no = $ni + 7;
                $ai++;
            }
            $sEcho = isset($filtered_aoData['sEcho']) ? intval($filtered_aoData['sEcho']) : intval(10);

            $return_data = array(
                'sEcho' => $sEcho,
                'iTotalRecords' => (int)$total_records,
                'iTotalDisplayRecords' => (int)$total_records,
                'aaData' => $data,
            );

        } else {
            $sEcho = isset($filtered_aoData['sEcho']) ? intval($filtered_aoData['sEcho']) : intval(10);
            $return_data = array(
                'sEcho' => $sEcho,
                'iTotalRecords' => (int)$total_records,
                'iTotalDisplayRecords' => (int)$total_records,
                'aaData' => $data,
            );
        }

        echo json_encode( $return_data );

        die;
    }

    function arf_form_entries() {
        global $wpdb, $MdlDb, $arffield, $armainhelper, $db_record, $arfform,$arfpagesize;

        $form_id = isset($_REQUEST['form']) ? $_REQUEST['form'] : '-1';
        $start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';
        $end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';

        if (isset($params) && $params['form']){
    		$form = $arfform->getOne($params['form']);
    	} else {
    		$form = (isset($form_select[0])) ? $form_select[0] : 0;
    	}

        if (empty($params) || !$params) {
            $params = $this->get_params();
        }

        $params['form'] = $form_id;

        $form_select = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name');
        if ($params['form']){
            $form = $arfform->getOne($params['form']);
        } else {
            $form = (isset($form_select[0])) ? $form_select[0] : 0;
        }

        if ($form) {
            $params['form'] = $form->id;
            $arfcurrentform = $form;
            $where_clause = " it.form_id=$form->id";
        } else {
            $where_clause = '';
        }

        $page_params = "&action=0&arfaction=0&form=";
        $page_params .= ($form) ? $form->id : 0;

        if (!empty($_REQUEST['fid']))
            $page_params .= '&fid=' . $_REQUEST['fid'];

        $form_css = maybe_unserialize($form->form_css);

        $item_vars = $this->get_sort_vars($params, $where_clause);
        $page_params .= ( isset($page_params_ov) ) ? $page_params_ov : $item_vars['page_params'];

        if ($form) {
            $form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'html', 'imagecontrol') and fi.form_id=" . (int) $form->id, ' ORDER BY id');
            $record_where = ($item_vars['where_clause'] == " it.form_id=$form->id") ? $form->id : $item_vars['where_clause'];
        } else {
            $form_cols = array();
            $record_where = $item_vars['where_clause'];
        }

        $current_page = ( isset($current_page_ov) ) ? $current_page_ov : $params['paged'];

        $sort_str = $item_vars['sort_str'];

        $sdir_str = $item_vars['sdir_str'];

        $search_str = $item_vars['search_str'];

        $fid = $item_vars['fid'];

        $record_count = $db_record->getRecordCount($record_where);

        $page_count = $db_record->getPageCount($arfpagesize, $record_count);


        global $style_settings, $wp_scripts;
        $wp_format_date = get_option('date_format');

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'mm/dd/yy';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'dd/mm/yy';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'dd/mm/yy';
        } else {
            $date_format_new = 'mm/dd/yy';
        }
        $new_start_date = $start_date;
        $new_end_date = $end_date;
        $show_new_start_date = $new_start_date;
        $show_new_end_date = $new_end_date;

        if ($new_start_date != '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) >= '" . $new_start_date_var . "' and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        } else if ($new_start_date != '' and $new_end_date == '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) >= '" . $new_start_date_var . "'";
        } else if ($new_start_date == '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        }

        $items = $db_record->getPage('', '', $item_vars['where_clause'], $item_vars['order_by']);

        $page_last_record = $armainhelper->getLastRecordNum($record_count, $current_page, $arfpagesize);

        $page_first_record = $armainhelper->getFirstRecordNum($record_count, $current_page, $arfpagesize);

        if ((isset($form_id) && $form_id == '-1') || ( empty($form_id) || empty($form->id) )) {
            $form_cols = array();
            $items = array();
        }

        $action_no = 0;
        if( is_rtl() ){
            $divStyle = "display:inline-block;position:relative;";
        } else {
            $divStyle = "position:relative;width:100%;text-align:center;";
        }

        $default_hide = array(
            '0' => '<div style="'.$divStyle.'"><div class="arf_custom_checkbox_div arfmarginl15"><div class="arf_custom_checkbox_wrapper arfmargin10custom"><input id="cb-select-all-1" type="checkbox" class=""><svg width="18px" height="18px">'.ARF_CUSTOM_UNCHECKED_ICON.'
                                '.ARF_CUSTOM_CHECKED_ICON.'</svg></div></div>
            <label for="cb-select-all-1"  class="cb-select-all"><span class="cb-select-all-checkbox"></span></label></div>',
            '1' => 'ID'
        ); 

        $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $form->id);
        $items = apply_filters('arfpredisplaycolsitems', $items, $form->id);

        if (count($form_cols) > 0) {
            for ($i = 2; $i <= count($form_cols) + 1; $i++) {
                $j = $i - 2;
                $default_hide[$i] = $armainhelper->truncate($form_cols[$j]->name, 40);
            }
            $default_hide[$i] = 'Entry key';
            $default_hide[$i + 1] = 'Entry Creation Date';
            $default_hide[$i + 2] = 'Browser Name';
            $default_hide[$i + 3] = 'IP Address';
            $default_hide[$i + 4] = 'Country';
            $default_hide[$i + 5] = 'Page URL';
            $default_hide[$i + 6] = 'Referrer URL';
            $default_hide[$i + 7] = 'Action';

            $action_no = $i + 7;
        } else {
            $default_hide['2'] = 'Entry Key';
            $default_hide['3'] = 'Entry creation date';
            $default_hide['4'] = 'Browser Name';
            $default_hide['5'] = 'IP Address';
            $default_hide['6'] = 'Country';
            $default_hide['7'] = 'Page URL';
            $default_hide['8'] = 'Referrer URL';
            $default_hide['9'] = 'Action';
            $action_no = 9;
        }

        $columns_list_res = $wpdb->get_results($wpdb->prepare('SELECT columns_list FROM ' . $MdlDb->forms . ' WHERE id = %d', $form->id), ARRAY_A);
        $columns_list_res = $columns_list_res[0];

        $columns_list = maybe_unserialize($columns_list_res['columns_list']);

        $is_colmn_array = is_array($columns_list);

        $exclude = '';

        $exclude_array = array();

        if (count($columns_list) > 0 and $columns_list != '') {

            foreach ($columns_list as $keys => $column) {

                foreach ($default_hide as $key => $val) {

                    if ($column == $val) {
                        if ($exclude_array == "") {
                            $exclude_array[] = $key;
                        } else {
                            if (!in_array($key, $exclude_array)) {
                                $exclude_array[] = $key;

                            }
                        }
                    }
                }
            }
        }

        $ipcolumn = ($action_no - 4);
        $page_url_column = ($action_no - 2);
        $referrer_url_column = ($action_no - 1);

        if ($exclude_array == "" and ! $is_colmn_array) {
            $exclude_array = array($ipcolumn, $page_url_column, $referrer_url_column);
        } else if (is_array($exclude_array) and ! $is_colmn_array) {

            if (!in_array($ipcolumn, $exclude_array)) {
                array_push($exclude_array, $ipcolumn);
            }
            if (!in_array($page_url_column, $exclude_array)) {
                array_push($exclude_array, $page_url_column);
            }
            if (!in_array($referrer_url_column, $exclude_array)) {
                array_push($exclude_array, $referrer_url_column);
            }
        }

        if ($exclude_array != "") {
            $exclude = implode(",", $exclude_array);
        }

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        }

        $data = array();

        if (count($items) > 0) {
            $ai = 0;
            $arf_edit_select_array = array();
            foreach ($items as $key => $item) {
                if( is_rtl() ){
                    $divStyle = "display:inline-block;position:relative;";
                } else {
                    $divStyle = "position:relative;width:100%;text-align:center;";
                }
                $data[$ai][0] = "<div class='DataTables_sort_wrapper'><div style='{$divStyle}'>
                       <div class='arf_custom_checkbox_div arfmarginl15'><div class='arf_custom_checkbox_wrapper'><input id='cb-item-action-{$item->id}' class='' type='checkbox' value='{$item->id}' name='item-action[]' />
                                        <svg width='18px' height='18px'>
                                        ".ARF_CUSTOM_UNCHECKED_ICON."
                                        ".ARF_CUSTOM_CHECKED_ICON."
                                        </svg>
                                    </div>
                                </div>
                    <label for='cb-item-action-{$item->id}'><span></span></label></div></div>" ;
                $data[$ai][1] = $item->id;
                $ni = 2;
                foreach ($form_cols as $col) {
                    $field_value = isset($item->metas[$col->id]) ? $item->metas[$col->id] : false;
                    $col->field_options = json_decode($col->field_options, true);
                    if ($col->type == 'checkbox' || $col->type == 'radio' || $col->type == 'select') {
                        if (isset($col->field_options['separate_value']) && $col->field_options['separate_value'] == '1') {
                            $option_separate_value = array();
                            foreach ($col->options as $k => $options) {
                                $option_separate_value[] = array('value' => htmlentities($options['value']), 'text' => $options['label']);
                            }
                            $arf_edit_select_array[] = array($col->id => json_encode($option_separate_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                        } else {
                            $option_value = '';
                            $option_value = array();
                            if(is_array($col->options))
                            {
                                foreach ($col->options as $k => $options) {
                                    if (is_array($options)) {
                                        $option_value[] = ($options['label']);
                                    } else {
                                        $option_value[] = ($options);
                                    }
                                }
                            }
                            $arf_edit_select_array[] = array($col->id => json_encode($option_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                        }
                    }
                    global $arrecordhelper;
                    $data[$ai][$ni] = $arrecordhelper->display_value($field_value, $col, array('type' => $col->type, 'truncate' => true, 'attachment_id' => $item->attachment_id, 'entry_id' => $item->id),$form_css);
                    $ni++;
                }
                $data[$ai][$ni] = $item->entry_key;
                $data[$ai][$ni + 1] = date(get_option('date_format'), strtotime($item->created_date));
                $browser_info = $this->getBrowser($item->browser_info);
                $data[$ai][$ni + 2] = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
                $data[$ai][$ni + 3] = $item->ip_address;
                $data[$ai][$ni + 4] = $item->country;
                $http_referrer = maybe_unserialize( $item->description );
                $data[$ai][$ni + 5] = $http_referrer['page_url'];
                $data[$ai][$ni + 6] = $http_referrer['http_referrer'];

                $view_entry_icon = is_rtl() ? 'view_icon23_rtl.png' : 'view_icon23.png';
                $view_entry_icon_hover = is_rtl() ? 'view_icon23_hover_rtl.png' : 'view_icon23_hover.png';

                $view_entry_btn = "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Preview', 'ARForms')) . "'><a href='javascript:void(0);'  onclick='open_entry_thickbox({$item->id},\"{$form->name}\");'><svg width='30px' height='30px' viewBox='-3 -8 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M12.993,15.23c-7.191,0-11.504-7.234-11.504-7.234  S5.801,0.85,12.993,0.85c7.189,0,11.504,7.19,11.504,7.19S20.182,15.23,12.993,15.23z M12.993,2.827  c-5.703,0-8.799,5.214-8.799,5.214s3.096,5.213,8.799,5.213c5.701,0,8.797-5.213,8.797-5.213S18.694,2.827,12.993,2.827z   M12.993,11.572c-1.951,0-3.531-1.581-3.531-3.531s1.58-3.531,3.531-3.531c1.949,0,3.531,1.581,3.531,3.531  S14.942,11.572,12.993,11.572z'/></svg></a></div>";

                global $PDF_button;
		do_action('arf_additional_action_entries', $item->id, $form->id,true);
		global $PDF_button;
                $delete_link = "?page=ARForms-entries&arfaction=destroy&id={$item->id}";
                $delete_link .= "&form=" . $params['form'];
                $id = $item->id;

                $delete_entry_icon = is_rtl() ? 'delete_icon223_rtl.png' : 'delete_icon223.png';
                $delete_entry_icon_hover = is_rtl() ? 'delete_icon223_hover_rtl.png' : 'delete_icon223_hover.png';

                $delete_entry_btn = "<div class='arfformicondiv arfhelptip arfentry_delete_div_".$item->id."' title='" . addslashes(esc_html__('Delete', 'ARForms')) . "'><a data-id='".$item->id."' id='arf_delete_single_entry' style='cursor:pointer'><svg width='30px' height='30px' viewBox='-5 -5 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z' /></svg></a></div>";

                

                $delete_entry_overlay = "<div id='view_entry_detail_container_{$item->id}' style='display:none;'>" . $this->get_entries_list_edit($item->id) . "</div><div style='clear:both;' class='arfmnarginbtm10'></div>";

                $data[$ai][$ni + 7] = "<div class='arf-row-actions'>{$view_entry_btn}{$PDF_button}{$delete_entry_btn} {$delete_entry_overlay} <input type='hidden' id='arf_edit_select_array_one' value='" . json_encode($arf_edit_select_array) . "' /></div>";
                $PDF_button = '';
                $action_no = $ni + 7;
                $ai++;
            }
        }

        $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10);
        $response = array(
            'sColumns' => implode('||', $default_hide),
            'sEcho' => $sEcho,
            'iTotalRecords' => count($items),
            'iTotalDisplayRecords' => count($items),
            'aaData' => $data,
            'action_no' => $action_no,
            'exclude' => $exclude_array
        );

        echo json_encode($response);
        die();

    }

    function frm_change_entries($new_form_id = '', $new_start_date = '', $new_end_date = '', $bulk = '', $message = '', $errors = '') {

        global $wpdb, $MdlDb, $armainhelper, $arfform, $db_record, $arfrecordmeta, $arfpagesize, $arffield, $arfcurrentform, $arformhelper, $arrecordcontroller, $arfversion;

        if (isset($bulk) && $bulk == '1') {
            $new_form_id = $new_form_id;
            $new_start_date = $new_start_date;
            $new_end_date = $new_end_date;
        } else {
            $new_form_id = $_POST['form'];
            $new_start_date = $_POST['start_date'];
            $new_end_date = $_POST['end_date'];
        }

        if (!isset($new_form_id) && $new_form_id == '')
            $new_form_id == '-1';


        if (empty($params) || !$params)
            $params = $this->get_params();



        $params['form'] = $new_form_id;


        $form_select = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name');



        if ($params['form'])
            $form = $arfform->getOne($params['form']);
        else
            $form = (isset($form_select[0])) ? $form_select[0] : 0;




        if ($form) {


            $params['form'] = $form->id;


            $arfcurrentform = $form;


            $where_clause = " it.form_id=$form->id";
        } else {


            $where_clause = '';
        }




        $page_params = "&action=0&arfaction=0&form=";


        $page_params .= ($form) ? $form->id : 0;



        if (!empty($_REQUEST['fid']))
            $page_params .= '&fid=' . $_REQUEST['fid'];



        $item_vars = $this->get_sort_vars($params, $where_clause);


        $page_params .= ( isset($page_params_ov) ) ? $page_params_ov : $item_vars['page_params'];



        if ($form) {


            $form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'html', 'imagecontrol') and fi.form_id=" . (int) $form->id, ' ORDER BY id');

            $record_where = ($item_vars['where_clause'] == " it.form_id=$form->id") ? $form->id : $item_vars['where_clause'];
        } else {


            $form_cols = array();


            $record_where = $item_vars['where_clause'];
        }


        $current_page = ( isset($current_page_ov) ) ? $current_page_ov : $params['paged'];

        $sort_str = $item_vars['sort_str'];


        $sdir_str = $item_vars['sdir_str'];


        $search_str = $item_vars['search_str'];

        $fid = $item_vars['fid'];

        $record_count = $db_record->getRecordCount($record_where);

        $page_count = $db_record->getPageCount($arfpagesize, $record_count);

        wp_enqueue_style('bootstrap-editable-css', ARFURL . '/bootstrap/css/bootstrap-editable.css', array(), $arfversion);
        wp_enqueue_script('bootstrap-editable-js', ARFURL . '/bootstrap/js/bootstrap-editable.js', array(), $arfversion);

        global $style_settings, $wp_scripts;
        $wp_format_date = get_option('date_format');

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'mm/dd/yy';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'dd/mm/yy';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'dd/mm/yy';
        } else {
            $date_format_new = 'mm/dd/yy';
        }

        $show_new_start_date = $new_start_date;
        $show_new_end_date = $new_end_date;

        if ($new_start_date != '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) >= '" . $new_start_date_var . "' and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        } else if ($new_start_date != '' and $new_end_date == '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) >= '" . $new_start_date_var . "'";
        } else if ($new_start_date == '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        }




        $items = $db_record->getPage('', '', $item_vars['where_clause'], $item_vars['order_by']);

        $page_last_record = $armainhelper->getLastRecordNum($record_count, $current_page, $arfpagesize);

        $page_first_record = $armainhelper->getFirstRecordNum($record_count, $current_page, $arfpagesize);

        if ((isset($new_form_id) && $new_form_id == '-1') || ( empty($new_form_id) || empty($form->id) )) {
            $form_cols = array();
            $items = array();
        }

        if ($form->id != '-1' || $form->id != '') {

            $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $form->id);
            $items = apply_filters('arfpredisplaycolsitems', $items, $form->id);

            $action_no = 0;

            $default_hide = array(
                '0' => '',
                '1' => 'ID',
            );
            if (count($form_cols) > 0) {

                for ($i = 2; 1 + count($form_cols) >= $i; $i++) {
                    $j = $i - 2;
                    $default_hide[$i] = $armainhelper->truncate($form_cols[$j]->name, 40);
                }
                $default_hide[$i] = 'Entry Key';
                $default_hide[$i + 1] = 'Entry creation date';
                $default_hide[$i + 2] = 'Browser Name';
                $default_hide[$i + 3] = 'IP Address';
                $default_hide[$i + 4] = 'Country';
                $default_hide[$i + 5] = 'Page URL';
                $default_hide[$i + 6] = 'Referrer URL';
                $default_hide[$i + 7] = 'Action';
                $action_no = $i + 7;
            } else {
                $default_hide['2'] = 'Entry Key';
                $default_hide['3'] = 'Entry creation date';
                $default_hide['4'] = 'Browser Name';
                $default_hide['5'] = 'IP Address';
                $default_hide['6'] = 'Country';
                $default_hide['7'] = 'Page URL';
                $default_hide['8'] = 'Referrer URL';
                $default_hide['9'] = 'Action';
                $action_no = 9;
            }


            $columns_list_res = $wpdb->get_results($wpdb->prepare('SELECT columns_list FROM ' . $MdlDb->forms . ' WHERE id = %d', $form->id), ARRAY_A);
            $columns_list_res = $columns_list_res[0];

            $columns_list = maybe_unserialize($columns_list_res['columns_list']);
            $is_colmn_array = is_array($columns_list);

            $exclude = '';

            $exclude_array = "";
            if (count($columns_list) > 0 and $columns_list != '') {

                foreach ($columns_list as $keys => $column) {

                    foreach ($default_hide as $key => $val) {

                        if ($column == $val) {
                            if ($exclude_array == "") {
                                $exclude_array[] = $key;
                            } else {
                                if (!in_array($key, $exclude_array)) {
                                    $exclude_array[] = $key;

                                }
                            }
                        }
                    }
                }
            }

            $ipcolumn = ($action_no - 4);
            $page_url_column = ($action_no - 2);
            $referrer_url_column = ($action_no - 1);

            if ($exclude_array == "" and ! $is_colmn_array) {
                $exclude_array = array($ipcolumn, $page_url_column, $referrer_url_column);
            } else if (is_array($exclude_array) and ! $is_colmn_array) {
                if (!in_array($ipcolumn, $exclude_array)) {
                    array_push($exclude_array, $ipcolumn);
                }
                if (!in_array($page_url_column, $exclude_array)) {
                    array_push($exclude_array, $page_url_column);
                }
                if (!in_array($referrer_url_column, $exclude_array)) {
                    array_push($exclude_array, $referrer_url_column);
                }
            }
        }

        if ($exclude_array != "") {
            $exclude = implode(",", $exclude_array);
        }

        $actions = array('bulk_delete' => addslashes(esc_html__('Delete', 'ARForms')));

        $actions['bulk_csv'] = addslashes(esc_html__('Export to CSV', 'ARForms'));

        global $style_settings, $wp_scripts;
        $wp_format_date = get_option('date_format');

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        }

        global $arf_entries_action_column_width;
        ?>
        <script type="text/javascript" data-cfasync="false" charset="utf-8">
            jQuery(document).ready(function () {
                jQuery("#datepicker_from").datetimepicker({
                    useCurrent: false,
                    format: '<?php echo $date_format_new; ?>',
                    locale: '<?php echo (isset($options['locale'])) ? $options['locale'] : ''; ?>',
                    minDate: moment('<?php echo $start_date_new; ?>', '<?php echo $date_format_new1; ?>'),
                    maxDate: moment('<?php echo $end_date_new; ?>', '<?php echo $date_format_new1; ?>')
                });

                jQuery("#datepicker_to").datetimepicker({
                    useCurrent: false,
                    format: '<?php echo $date_format_new; ?>',
                    locale: '<?php echo (isset($options['locale'])) ? $options['locale'] : ''; ?>',
                    minDate: moment('<?php echo $start_date_new; ?>', '<?php echo $date_format_new1; ?>'),
                    maxDate: moment('<?php echo $end_date_new; ?>', '<?php echo $date_format_new1; ?>')
                });

                jQuery.fn.dataTableExt.oPagination.four_button = {
                    "fnInit": function (oSettings, nPaging, fnCallbackDraw)
                    {
                        nFirst = document.createElement('span');
                        nPrevious = document.createElement('span');


                        var nInput = document.createElement('input');
                        var nPage = document.createElement('span');
                        var nOf = document.createElement('span');
                        nOf.className = "paginate_of";
                        nInput.className = "current_page_no";
                        nPage.className = "paginate_page";
                        nInput.type = "text";
                        nInput.style.width = "40px";
                        nInput.style.height = "26px";
                        nInput.style.display = "inline";


                        nPaging.appendChild(nPage);


                        jQuery(nInput).keyup(function (e) {

                            if (e.which == 38 || e.which == 39)
                            {
                                this.value++;
                            }
                            else if ((e.which == 37 || e.which == 40) && this.value > 1)
                            {
                                this.value--;
                            }

                            if (this.value == "" || this.value.match(/[^0-9]/))
                            {

                                return;
                            }

                            var iNewStart = oSettings._iDisplayLength * (this.value - 1);
                            if (iNewStart > oSettings.fnRecordsDisplay())
                            {

                                oSettings._iDisplayStart = (Math.ceil((oSettings.fnRecordsDisplay() - 1) /
                                        oSettings._iDisplayLength) - 1) * oSettings._iDisplayLength;
                                fnCallbackDraw(oSettings);
                                return;
                            }

                            oSettings._iDisplayStart = iNewStart;
                            fnCallbackDraw(oSettings);
                        });



                        nNext = document.createElement('span');
                        nLast = document.createElement('span');
                        var nFirst = document.createElement('span');
                        var nPrevious = document.createElement('span');
                        var nPage = document.createElement('span');
                        var nOf = document.createElement('span');

                        nNext.style.backgroundImage = "url('<?php echo ARFURL; ?>/images/next_normal-icon.png')";
                        nNext.style.backgroundRepeat = "no-repeat";
                        nNext.style.backgroundPosition = "center";
                        nNext.title = "Next";

                        nLast.style.backgroundImage = "url('<?php echo ARFURL; ?>/images/last_normal-icon.png')";
                        nLast.style.backgroundRepeat = "no-repeat";
                        nLast.style.backgroundPosition = "center";
                        nLast.title = "Last";

                        nFirst.style.backgroundImage = "url('<?php echo ARFURL; ?>/images/first_normal-icon.png')";
                        nFirst.style.backgroundRepeat = "no-repeat";
                        nFirst.style.backgroundPosition = "center";
                        nFirst.title = "First";

                        nPrevious.style.backgroundImage = "url('<?php echo ARFURL; ?>/images/previous_normal-icon.png')";
                        nPrevious.style.backgroundRepeat = "no-repeat";
                        nPrevious.style.backgroundPosition = "center";
                        nPrevious.title = "Previous";


                        nFirst.appendChild(document.createTextNode(' '));
                        nPrevious.appendChild(document.createTextNode(' '));
                        nNext.appendChild(document.createTextNode(' '));
                        nLast.appendChild(document.createTextNode(' '));


                        nOf.className = "paginate_button nof";

                        nPaging.appendChild(nFirst);
                        nPaging.appendChild(nPrevious);

                        nPaging.appendChild(nInput);
                        nPaging.appendChild(nOf);

                        nPaging.appendChild(nNext);
                        nPaging.appendChild(nLast);

                        jQuery(nFirst).click(function () {
                            oSettings.oApi._fnPageChange(oSettings, "first");
                            fnCallbackDraw(oSettings);
                        });

                        jQuery(nPrevious).click(function () {
                            oSettings.oApi._fnPageChange(oSettings, "previous");
                            fnCallbackDraw(oSettings);
                        });

                        jQuery(nNext).click(function () {
                            oSettings.oApi._fnPageChange(oSettings, "next");
                            fnCallbackDraw(oSettings);
                        });

                        jQuery(nLast).click(function () {
                            oSettings.oApi._fnPageChange(oSettings, "last");
                            fnCallbackDraw(oSettings);
                        });


                        jQuery(nFirst).bind('selectstart', function () {
                            return false;
                        });
                        jQuery(nPrevious).bind('selectstart', function () {
                            return false;
                        });
                        jQuery('span', nPaging).bind('mousedown', function () {
                            return false;
                        });
                        jQuery('span', nPaging).bind('selectstart', function () {
                            return false;
                        });
                        jQuery(nNext).bind('selectstart', function () {
                            return false;
                        });
                        jQuery(nLast).bind('selectstart', function () {
                            return false;
                        });
                    },
                    "fnUpdate": function (oSettings, fnCallbackDraw)
                    {
                        if (!oSettings.aanFeatures.p)
                        {
                            return;
                        }


                        var an = oSettings.aanFeatures.p;
                        for (var i = 0, iLen = an.length; i < iLen; i++)
                        {
                            var buttons = an[i].getElementsByTagName('span');
                            if (oSettings._iDisplayStart === 0)
                            {
                                buttons[1].className = "paginate_disabled_first arfhelptip";
                                buttons[2].className = "paginate_disabled_previous arfhelptip";
                            }
                            else
                            {
                                buttons[1].className = "paginate_enabled_first arfhelptip";
                                buttons[2].className = "paginate_enabled_previous arfhelptip";
                            }

                            if (oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay())
                            {
                                buttons[4].className = "paginate_disabled_next arfhelptip";
                                buttons[5].className = "paginate_disabled_last arfhelptip";
                            }
                            else
                            {

                                buttons[4].className = "paginate_enabled_next arfhelptip";
                                buttons[5].className = "paginate_enabled_last arfhelptip";
                            }


                            if (!oSettings.aanFeatures.p)
                            {
                                return;
                            }
                            var iPages = Math.ceil((oSettings.fnRecordsDisplay()) / oSettings._iDisplayLength);

                            var iCurrentPage = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1;

                            if (iPages == 0 && iCurrentPage == 1)
                                iPages = iPages + 1;

                            var an = oSettings.aanFeatures.p;
                            for (var i = 0, iLen = an.length; i < iLen; i++)
                            {
                                var spans = an[i].getElementsByTagName('span');
                                var inputs = an[i].getElementsByTagName('input');
                                spans[spans.length - 3].innerHTML = " of " + iPages
                                inputs[0].value = iCurrentPage;
                            }


                        }
                    }
                }

                var oTables = jQuery('#example').dataTable({
                    "sDom": '<"H"lCfr>t<"footer"ip>',
                    "sPaginationType": "four_button",
                    "bJQueryUI": true,
                    "bPaginate": true,
                    "bAutoWidth": false,
                    "sScrollX": "100%",
                    "bScrollCollapse": true,
                    "oColVis": {
                        "aiExclude": [0, <?php echo $action_no; ?>]
                    },
                    "aoColumnDefs": [
                        {"sType": "html", "bVisible": false, "aTargets": [<?php if ($exclude != '') echo $exclude; ?>]},
                        {"bSortable": false, "aTargets": [0, <?php echo $action_no; ?>]}
                    ],
                });
                new FixedColumns(oTables, {
                    "iLeftColumns": 0,
                    "iLeftWidth": 0,
                    "iRightColumns": 1,
                    "iRightWidth": <?php echo isset($arf_entries_action_column_width) ? $arf_entries_action_column_width : '120'; ?>,
                });
            });

            jQuery(document).ready(function () {
                jQuery("#cb-select-all-1").click(function () {
                    jQuery('input[name="item-action[]"]').prop('checked', this.checked);
                });

                jQuery('input[name="item-action[]"]').click(function () {

                    if (jQuery('input[name="item-action[]"]').length == jQuery('input[name="item-action[]"]:checked').length) {
                        jQuery("#cb-select-all-1").prop("checked", true);
                    } else {
                        jQuery("#cb-select-all-1").prop("checked", false);
                    }

                });

            });

        </script>

        <?php
        if (is_rtl()) {
            $sel_frm_div = 'float:right;margin-top:15px;';
            $sel_frm_txt = 'float:right;text-align:right;width:27%;';
        } else {
            $sel_frm_div = 'float:left;margin-top:15px;';
            $sel_frm_txt = 'float:left;text-align:left;width:27%;';
        }
        ?>
        <div class="arf_form_entry_select">
            <div class="arf_form_entry_select_sub">	
                <div>
                    <div class="arf_form_entry_left"><?php echo addslashes(esc_html__('Select form', 'ARForms')); ?>:</div>
                    <div style=" <?php echo $sel_frm_txt; ?>" ><div class="sltstandard" style="float:none;"><?php $arformhelper->forms_dropdown('arfredirecttolist', $new_form_id, addslashes(esc_html__('Select Form', 'ARForms')), false, ""); ?></div></div>
                </div>
        <?php
        if (is_rtl()) {
            $sel_frm_date_wrap = 'float:right;text-align:right;width:65%';
            $sel_frm_sel_date = 'float:right;';
            $sel_frm_button = 'float:right;margin-top:15px;';
        } else {
            $sel_frm_date_wrap = 'float:left;text-align:left;width:65%';
            $sel_frm_sel_date = 'float:left;';
            $sel_frm_button = 'float:left;margin-top:15px;';
        }
        ?>
                <div style=" <?php echo $sel_frm_div ?>">
                    <div class="arf_form_entry_left"><div><?php echo addslashes(esc_html__('Select date From', 'ARForms')); ?>:</div><div class="arf_form_entry_left_sub">(<?php echo addslashes(esc_html__('optional', 'ARForms')); ?>)</div></div>
                    <div style=" <?php echo $sel_frm_date_wrap; ?>">
                        <div style=" <?php echo $sel_frm_sel_date; ?>"><input type="text" class="txtmodal1" id="datepicker_from" value="<?php echo $show_new_start_date; ?>" name="datepicker_from" style="vertical-align:middle; width:130px;" /></div> <div class="arfentrytitle"><?php echo addslashes(esc_html__('To', 'ARForms')); ?>:</div>&nbsp;&nbsp;<div style="float:left;"><input type="text" class="txtmodal1" id="datepicker_to" name="datepicker_to"  value="<?php echo $show_new_end_date; ?>" style="vertical-align:middle;  width:130px;"/></div>

                    </div>

                    <div style=" <?php echo $sel_frm_button; ?>">
                        <div class="arf_form_entry_left">&nbsp;</div>
                        <div style="float:left;text-align:left;"><button type="button" class="rounded_button btn_green" onclick="change_frm_entries();"><?php echo addslashes(esc_html__('Submit', 'ARForms')); ?></button></div>
                    </div>        

                    <input type="hidden" name="please_select_form" id="please_select_form" value="<?php echo addslashes(esc_html__('Please select a form', 'ARForms')); ?>" />
                </div>
                <div style="clear:both;"></div>
            </div>    
        </div>
        <div style="clear:both; height:30px;"></div>


        <?php do_action('arfbeforelistingentries'); ?>

        <div class="arf_loder_entries_section" id="arf_loder_entrie_div">
            <img src="<?php echo ARFIMAGESURL; ?>/ajax_loader_gray_64.gif" />
        </div>  

        <form method="get" id="list_entry_form" class="arf_list_entries_form" onsubmit="return apply_bulk_action();" style="float:left;width:100%;">

            <input type="hidden" name="page" value="ARForms-entries" />

            <input type="hidden" name="form" value="<?php echo ($form) ? $form->id : '-1'; ?>" />

            <input type="hidden" name="arfaction" value="list" />

            <input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php echo addslashes(esc_html__('Show / Hide columns', 'ARForms')); ?>"/>
            <input type="hidden" name="search_grid" id="search_grid" value="<?php echo addslashes(esc_html__('Search', 'ARForms')); ?>"/>
            <input type="hidden" name="entries_grid" id="entries_grid" value="<?php echo addslashes(esc_html__('entries', 'ARForms')); ?>"/>
            <input type="hidden" name="show_grid" id="show_grid" value="<?php echo addslashes(esc_html__('Show', 'ARForms')); ?>"/>
            <input type="hidden" name="showing_grid" id="showing_grid" value="<?php echo addslashes(esc_html__('Showing', 'ARForms')); ?>"/>
            <input type="hidden" name="to_grid" id="to_grid" value="<?php echo addslashes(esc_html__('to', 'ARForms')); ?>"/>
            <input type="hidden" name="of_grid" id="of_grid" value="<?php echo addslashes(esc_html__('of', 'ARForms')); ?>"/>
            <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php echo addslashes(esc_html__('No matching records found', 'ARForms')); ?>"/>
            <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php echo addslashes(esc_html__('No data available in table', 'ARForms')); ?>"/>
            <input type="hidden" name="filter_grid" id="filter_grid" value="<?php echo addslashes(esc_html__('filtered from', 'ARForms')); ?>"/>
            <input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php echo addslashes(esc_html__('total', 'ARForms')); ?>"/>

        <?php require(VIEWS_PATH . '/shared_errors.php'); ?>  

            <?php $two = '1'; ?>
            <div class="alignleft actions">
                <div class="arf_list_bulk_action_wrapper">
                    <?php
                        foreach ($actions as $name => $title) {

                            $opt_class = 'edit' == $name ? ' hide-if-no-js ' : '';

                            $arf_action_opt_arr[$name] = $title;
                        }

                        echo $maincontroller->arf_selectpicker_dom( 'action'.$two , 'arf_bulk_action_one', '', 'width:130px;', '-1', array(), $arf_action_opt_arr, false, $opt_class );
                    ?>
                </div>
                <input type="submit" id="doaction<?php echo $two; ?>" class="rounded_button btn_blue" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')) ?>" style='margin-top:-2px;' />
            </div>


            <table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
                <thead>
                    <tr>
                        <th class="box" style="text-align:center"><div style="position:relative;">
                    <div class="arf_custom_checkbox_div">
                        <div class="arf_custom_checkbox_wrapper arfmargin10custom">
                            <input id="cb-select-all-1" type="checkbox" class="">
                            <svg width="18px" height="18px">
        <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                            <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                            </svg>
                        </div>
                    </div>
                    <label for="cb-select-all-1"  class="cb-select-all"><span></span></label></div>
                </th>
                <th><?php echo addslashes(esc_html__('ID', 'ARForms')); ?></th>
        <?php
        if (count($form_cols) > 0) {
            foreach ($form_cols as $col) {
                ?>
                        <th><?php echo $armainhelper->truncate($col->name, 40) ?></th>
                        <?php
                    }
                }
                ?>
                <th><?php echo addslashes(esc_html__('Entry Key', 'ARForms')); ?></th>
                <th><?php echo addslashes(esc_html__('Entry creation date', 'ARForms')); ?></th>
                <th><?php echo addslashes(esc_html__('Browser Name', 'ARForms')); ?></th>
                <th><?php echo addslashes(esc_html__('IP Address', 'ARForms')); ?></th>
                <th><?php echo addslashes(esc_html__('Country', 'ARForms')); ?></th>
                <th><?php echo esc_html__('Page URL', 'ARForms'); ?></th>
                <th><?php echo addslashes(esc_html__('Referrer URL', 'ARForms')); ?></th>
                <th class="arf_col_action"><?php echo addslashes(esc_html__('Action', 'ARForms')); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    $arf_edit_select_array = array();
                    if (count($items) > 0) {
                        $arf_edit_select_array = array();
                        foreach ($items as $key => $item) {
                            ?>
                            <tr>
                                <td class="center">
                                    <div class="arf_custom_checkbox_div">
                                        <div class="arf_custom_checkbox_wrapper arfmarginl15">
                                            <input id="cb-item-action-<?php echo $item->id; ?>" class="" type="checkbox" value="<?php echo $item->id; ?>" name="item-action[]">
                                            <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <label for="cb-item-action-<?php echo $item->id; ?>"><span></span></label></td>
                                <td><?php echo $item->id; ?></td>
                                <?php foreach ($form_cols as $col) { ?>

                                    <td>
                                        <?php
                                        $field_value = isset($item->metas[$col->id]) ? $item->metas[$col->id] : false;


                                        $col->field_options = json_decode($col->field_options, true);

                                        if ($col->type == 'checkbox' || $col->type == 'radio' || $col->type == 'select' || $col->type == 'arf_multiselect' || $col->type == 'arf_autocomplete') {
                                            if (isset($col->field_options['separate_value']) && $col->field_options['separate_value'] == '1') {
                                                $option_separate_value = array();
                                                foreach ($col->options as $k => $options) {
                                                    $option_separate_value[] = array('value' => htmlentities($options['value']), 'text' => $options['label']);
                                                }
                                                $arf_edit_select_array[] = array($col->id => json_encode($option_separate_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ));
                                            } else {
                                                $option_value = '';
                                                $option_value = array();
                                                foreach ($col->options as $k => $options) {
                                                    if (is_array($options)) {
                                                        $option_value[] = ($options['label']);
                                                    } else {
                                                        $option_value[] = ($options);
                                                    }
                                                }
                                                $arf_edit_select_array[] = array($col->id => json_encode($option_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ));
                                            }
                                        }


                                        global $arrecordhelper;
                                        echo $arrecordhelper->display_value($field_value, $col, array('type' => $col->type, 'truncate' => true, 'attachment_id' => $item->attachment_id, 'entry_id' => $item->id));
                                        ?>

                                    </td>

                                <?php } ?>
                                <td><?php echo $item->entry_key; ?></td>
                                <td><?php echo date(get_option('date_format'), strtotime($item->created_date)); ?></td>
                                <td><?php
                                    $browser_info = $this->getBrowser($item->browser_info);
                                    echo $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
                                    ?></td>
                                <td><?php echo $item->ip_address; ?></td>
                                <td><?php echo $item->country; ?></td>
                                <?php $http_referrer = maybe_unserialize( $item->description ); ?>
                                <td><?php echo $http_referrer['page_url']; ?></td>
                                <td><?php echo $http_referrer['http_referrer']; ?></td>
                                <td class="arf_col_action">			
                                    <div class="arf-row-actions">  


                                        <?php
                                        if (is_rtl()) {
                                            echo "<a href='javascript:void(0);' onclick='open_entry_thickbox({$item->id});'><img src='" . ARFIMAGESURL . "/view_icon23_rtl.png' title='" . addslashes(esc_html__("View Entry", "ARForms")) . "' class='arfhelptip' onmouseover=\"this.src='" . ARFIMAGESURL . "/view_icon23_hover_rtl.png';\" onmouseout=\"this.src='" . ARFIMAGESURL . "/view_icon23_rtl.png';\" /></a>";
                                        } else {
                                            echo "<a href='javascript:void(0);' onclick='open_entry_thickbox({$item->id});'><img src='" . ARFIMAGESURL . "/view_icon23.png' title='" . addslashes(esc_html__("View Entry", "ARForms")) . "' class='arfhelptip' onmouseover=\"this.src='" . ARFIMAGESURL . "/view_icon23_hover.png';\" onmouseout=\"this.src='" . ARFIMAGESURL . "/view_icon23.png';\" /></a>";
                                        }

                                        

                                        $delete_link = "?page=ARForms-entries&arfaction=destroy&id={$item->id}";


                                        $delete_link .= "&form=" . $params['form'];


                                        $id = $item->id;

                                        if (is_rtl()) {
                                            echo "<img src='" . ARFIMAGESURL . "/delete_icon223_rtl.png' class='arfhelptip' title=" . addslashes(esc_html__("Delete", "ARForms")) . " onmouseover=\"this.src='" . ARFIMAGESURL . "/delete_icon223_hover_rtl.png';\" onmouseout=\"this.src='" . ARFIMAGESURL . "/delete_icon223_rtl.png';\" onclick=\"ChangeID({$id}); arfchangedeletemodalwidth('arfdeletemodabox');\" data-toggle='arfmodal' href='#delete_form_message' style='cursor:pointer' /></a>";
                                        } else {
                                            echo "<img src='" . ARFIMAGESURL . "/delete_icon223.png' class='arfhelptip' title=" .addslashes(esc_html__("Delete", "ARForms")) . " onmouseover=\"this.src='" . ARFIMAGESURL . "/delete_icon223_hover.png';\" onmouseout=\"this.src='" . ARFIMAGESURL . "/delete_icon223.png';\" onclick=\"ChangeID({$id}); arfchangedeletemodalwidth('arfdeletemodabox');\" data-toggle='arfmodal' href='#delete_form_message' style='cursor:pointer' /></a>";
                                        }

                                        do_action('arf_additional_action_entries', $item->id, $form->id);

                                        echo "<div class='arf_modal_overlay'>
                        <div id='view_entry_{$item->id}' class='arf_popup_container arf_view_entry_modal'>
                        <div class='arf_popup_container_header'>" . esc_html__('View entry', 'ARForms') . "
						  <div class='arfnewmodalclose arf_entry_model_close' data-dismiss='arfmodal'>
                            <svg viewBox='0 -4 32 32'>
                                <g id='email'><path fill-rule='evenodd' clip-rule='evenodd' fill='#333333' d='M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z'></path></g>
                                </svg>
                          </div>
                        </div>
                        <div class='arfentry_modal_content arf_popup_content_container'>" . $arrecordcontroller->get_entries_list_edit($item->id) . "</div>
						<div style='clear:both;' class='arfmnarginbtm10'></div>";
                                        ?>

                                    </div>
                                    </div>


                                    <!-- For Edit entry  -->
                                    <input type="hidden" id="arf_edit_select_array_one" value='<?php echo json_encode($arf_edit_select_array); ?>' />

                                    </div>
                                </td>              
                            </tr>
                            <?php
                        }
                    }
                    ?>

                </tbody>
            </table>

            <?php $two = '2'; ?>
            <div class="alignleft actions">
                <div class="arf_list_bulk_action_wrapper">
                    <?php
                        foreach ($actions as $name => $title) {
                            $opt_class = 'edit' == $name ? ' hide-if-no-js ' : '';

                            $arf_action_opt_arr[$name] = $title;
                        }

                        echo $maincontroller->arf_selectpicker_dom( 'action'.$two , 'arf_bulk_action_two', '', 'width:130px;', '-1', array(), $arf_action_opt_arr, false, $opt_class );
                    ?>
                </div>
                <input type="submit" id="doaction<?php echo $two; ?>" class="rounded_button btn_blue" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')) ?>" style='margin-top:-2px;' />
            </div>
            <div class="footer_grid"></div> 
        </form>

        <?php do_action('arfafterlistingentries'); ?>

        <div style="clear:both;"></div>
        <br /><br />

        <script type="text/javascript">
            var __ARF_edit_select_array = <?php echo json_encode($arf_edit_select_array); ?>;
            function ChangeID(id)
            {
                document.getElementById('delete_entry_id').value = id;
            }
        </script>
        <?php
        die();
    }

    function get_entries_list($id = '') {

        global $db_record, $arffield, $arfrecordmeta, $user_ID, $armainhelper, $arrecordhelper;

        if (!$id)
            $id = $armainhelper->get_param('id');


        if (!$id)
            $id = $armainhelper->get_param('entry_id');





        $entry = $db_record->getOne($id, true);


        $data = maybe_unserialize($entry->description);


        if (!is_array($data) or ! isset($data['referrer']))
            $data = array('referrer' => $data);



        $fields = $arffield->getAll("fi.type not in ('captcha','html', 'imagecontrol') and fi.form_id=" . (int) $entry->form_id, ' ORDER BY id');

        $fields = apply_filters('arfpredisplayformcols', $fields, $entry->form_id);
        $entry = apply_filters('arfpredisplayonecol', $entry, $entry->form_id);

        $date_format = get_option('date_format');


        $time_format = get_option('time_format');


        $show_comments = true;



        if ($show_comments) {


            $comments = $arfrecordmeta->getAll("entry_id=$id and field_id=0", ' ORDER BY it.created_date ASC');


            $to_emails = apply_filters('arftoemail', array(), $entry, $entry->form_id);
        }



        $var = '<table class="form-table"><tbody>';


        foreach ($fields as $field) {


            if ($field->type == 'divider' || $field->type == 'section') {


                $var .= '</tbody></table>
 				   <div class="arfentrydivider">' . stripslashes($field->name) . '</div>
				   <table class="form-table view_enty_table"><tbody>';
            } else if ($field->type == 'break') {

                $var .= '</tbody></table>
										
										<div class="arfpagebreakline"></div>
										
										<table class="form-table"><tbody>';
            } else if( 'matrix' == $field->type ){
                    $var .= "<tr>";
                        $var .= "<td class='arfviewentry_left arf_matrix_inner_table_data' colspan='2'>";
                            $var .= stripslashes( $field->name );
                        $var .= "</td>";
                    $var .= "</tr>";
                    $var .= "<tr class='arf_matrix_inner_table_wrapper'>";
                        $var .= "<td class='arf_matrix_inner_table_data' colspan='2'>";
                            $var .= "<table class='form-table'><tbody>";
                                $matrix_fopts = arf_json_decode($field->field_options, true);
                                $matrix_frows = arf_json_decode($matrix_fopts['rows'], true );
                                $source_data = array();
                                if( $matrix_fopts['separate_value'] != 1 ){
                                    foreach( $matrix_fopts['options'] as $mat_opts ){
                                        $source_data[ $mat_opts ] = $mat_opts;
                                    }
                                } else {
                                    foreach( $matrix_fopts['options'] as $mat_opts){
                                        $source_data[ $mat_opts['value'] ] = $mat_opts['label'];
                                    }
                                }
                                
                                if( !empty( $matrix_frows ) ){
                                    if( !isset($entry->metas[ $field->id ]) ){
                                        $entry->metas[ $field->id ] = array();
                                    }
                                    
                                    $saved_data = $entry->metas[ $field->id ];
                                    $x = 0;
                                    foreach( $matrix_frows as $matrix_row ){
                                        $var .= "<tr class='arfviewentry_row' valign='top'>";
                                            $var .= "<td class='arfviewentry_left arfwidth25' scope='row'>" . stripslashes_deep( $matrix_row ) . "</td>";
                                            $var .= "<td class='arfviewentry_right'>";
                                                if( current_user_can( 'arfeditentries' ) ){
                                                    $var .= '<span class="arf_editable_values_container arf_edit_type_matrix_option_'.$field->id.'_'.$x.'" id="arf_value_' . $entry->id . '_' . $field->id . '" data-matrix-inner-id="'.$x.'" data-id="'.$field->id.'" data-field-type="'.$field->type.'" data-entry-id="'.$entry->id.'" data-source=\''.json_encode($source_data).'\' data-type="select" data-pk="1" data-separate-value="'.$field->field_options['separate_value'].'" >';

                                                    if( 1 == $matrix_fopts['separate_value'] && !empty( $saved_data[ $x ] ) ){
                                                        $temp_val = '';
                                                        foreach( $matrix_fopts['options'] as $mat_opts){
                                                            if( $saved_data[$x] == $mat_opts['value'] ){
                                                                $temp_val = $mat_opts['label'] . ' (' . $mat_opts['value'] . ')';
                                                            }
                                                        }
                                                        $var .= $temp_val;
                                                    } else {
                                                        $var .= ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '-' );
                                                    }

                                                    $var .= '</span>';
                                                    $var .= '<input type="hidden" name="arf_edit_matrix_field_values['.$entry->id.']['.$x.']" id="arf_edit_new_matrix_values_'.$field->id.'_'.$entry->id.'_'.$x.'" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" />';
                                                    
                                                    $as_edit_matrix_entry_value[$field->id][$x] = ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '' );
                                                } else {
                                                    $var .= '<span class="arf_not_editable_values_container">';
                                                    if( 1 == $matrix_fopts['separate_value'] && !empty( $saved_data[ $x ] ) ){
                                                        $temp_val = '';
                                                        foreach( $matrix_fopts['options'] as $mat_opts){
                                                            if( $saved_data[$x] == $mat_opts['value'] ){
                                                                $temp_val = $mat_opts['label'] . ' (' . $mat_opts['value'] . ')';
                                                            }
                                                        }
                                                        $var .= $temp_val;
                                                    } else {
                                                        $var .= ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '-' );
                                                    }
                                                    $var .= '</span>';
                                                }
                                            $var .= "</td>";
                                        $var .= "</tr>";
                                        $x++;
                                    }
                                }
                            $var .= "</table>";
                        $var .= "</td>";
                    $var .= "</tr>";
            } else {

                if (is_rtl()) {
                    $txt_align = 'text-align:right;';
                } else {
                    $txt_align = 'text-align:left;';
                }
                $var .= '<tr class="arfviewentry_row" valign="top">


                            <td class="arfviewentry_left" scope="row"><strong>' . stripslashes($field->name) . ':</strong></td>


                            <td  class="arfviewentry_right" style="' . $txt_align . '">';





                $field_value = isset($entry->metas[$field->id]) ? $entry->metas[$field->id] : false;


                $field->field_options = arf_json_decode($field->field_options, true);


                $var .= $display_value = $arrecordhelper->display_value($field_value, $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id));





                if (is_email($display_value) and ! in_array($display_value, $to_emails))
                    $to_emails[] = $display_value;





                $var .= '</td>


                        </tr>';
            }
        }

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left"><strong>' . addslashes(esc_html__('Created at', 'ARForms')) . ':</strong></td><td class="arfviewentry_right">' . $armainhelper->get_formatted_time($entry->created_date, $date_format, $time_format);



        if ($entry->user_id) {
            
        }



        $var .= '</td></tr>';

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left"><strong>' . esc_html__('Page url', 'ARForms') . ':</strong></td><td class="arfviewentry_right">' . $data['page_url'];
        $var .= '</td></tr>';

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left"><strong>' . addslashes(esc_html__('Referrer url', 'ARForms')) . ':</strong></td><td class="arfviewentry_right">' . $data['http_referrer'];
        $var .= '</td></tr>';

        $temp_var = apply_filters('arf_entry_payment_detail', $id);

        $var .= ( $temp_var != $id ) ? $temp_var : '';

        $var = apply_filters('arfafterviewentrydetail', $var, $id);

        $var .= '</tbody></table>';

        return $var;
    }

    function get_incomplete_entries_list_edit( $id = '', $arffieldorder = array() ){
        global $db_record, $arffield, $arfrecordmeta, $user_ID, $armainhelper, $arrecordhelper;

        if( !$id ){
            $id = $armainhelper->get_param('id');
        }

        if( !$id ){
            $id = $armainhelper->get_param('entry_id');
        }

        $entry = $db_record->getOneIncomplete($id, true);

        $data = maybe_unserialize($entry->description);

        if (!is_array($data) or ! isset($data['referrer'])){
            $data = array('referrer' => $data);
        }

        $fields = wp_cache_get('get_incomplete_entries_list_edit_'.$entry->form_id);            
        if( false == $fields ){
            $fields = $arffield->getAll("fi.type not in ('captcha','html', 'imagecontrol') and fi.form_id=" . (int) $entry->form_id);
            wp_cache_set('get_incomplete_entries_list_edit_'.$entry->form_id, $fields);
        }
        
        $fields = apply_filters('arfpredisplayformcolsincompleteentries', $fields, $entry->form_id);
        $entry = apply_filters('arfpredisplaycolsitemsincompleteenteries', $entry, $entry->form_id);

        $date_format = get_option('date_format');

        $time_format = get_option('time_format');

        $show_comments = true;

        if ($show_comments) {

            $comments = $arfrecordmeta->getAll("entry_id=$id and field_id=0", ' ORDER BY it.created_date ASC', '', false, true, '', array(), true);

            $to_emails = apply_filters('arftoemail', array(), $entry, $entry->form_id);
        }

        $var = '<table class="form-table"><tbody>';

        $as_edit_entry_value = array();

        if(count($arffieldorder) > 0){

            $form_fields = array();
            foreach ($arffieldorder as $fieldkey => $fieldorder) {
                foreach ($fields as $fieldordkey => $fieldordval) {
                    if($fieldordval->id == $fieldkey) {
                        $form_fields[] = $fieldordval;
                        unset($fields[$fieldordkey]);
                    }
                }
            }

            if(count($form_fields) > 0) {
                if(count($fields) > 0) {
                    $arfotherfields = $fields;
                    $fields = array_merge($form_fields, $arfotherfields);
                } else {
                    $fields = $form_fields;
                }
            }
        }

        foreach ($fields as $field) {


            if ($field->type == 'divider' || $field->type == 'section') {


                $var .= '</tbody></table>


                                            <div class="arfentrydivider">' . stripslashes($field->name) . '</div>


                                            <table class="form-table"><tbody>';
            } else if ($field->type == 'break') {

                $var .= '</tbody></table>
                                        
                                        <div class="arfpagebreakline"></div>
                                        
                                        <table class="form-table"><tbody>';
            } else {

                if (is_rtl()) {
                    $txt_align = 'text-align:right;';
                } else {
                    $txt_align = 'text-align:left;';
                }
                $var .= '<tr class="arfviewentry_row" valign="top">


                            <td class="arfviewentry_left arfwidth25" scope="row">' . stripslashes($field->name) . ':</td>


                            <td  class="arfviewentry_right" style="' . $txt_align . '">';


                $field_value = isset($entry->metas[$field->id]) ? $entry->metas[$field->id] : false;


                $field->field_options = arf_json_decode($field->field_options, true);

                if ($field->type == 'checkbox' || $field->type == 'arf_multiselect') {
                    $as_edit_entry_value[$field->id] = $field_value;
                }

                if ($field->type == 'radio' || $field->type == 'select' || $field->type == 'arf_autocomplete') {
                    $as_edit_entry_value[$field->id] = $field_value;
                }
                
                $var .= $display_value = $arrecordhelper->display_value($field_value, $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id), array(), true);

                $var .= '<input type="hidden" name="arf_edit_form_field_values_'.$entry->id.'[]" id="arf_edit_new_values_'.$field->id.'_'.$entry->id.'" value="" data-id="' . $field->id . '" data-entry-id="' . $entry->id . '">';

                if (is_email($display_value) and ! in_array($display_value, $to_emails)){
                    $to_emails[] = $display_value;
                }


                $var .= '</td>
                        </tr>';
            }
        }

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . addslashes(esc_html__('Created at', 'ARForms')) . ':</td><td class="arfviewentry_right"><span class="arf_not_editable_values_container">' . $armainhelper->get_formatted_time($entry->created_date, $date_format, $time_format) .'</span>';
        if ($entry->user_id) {
            
        }

        $json_data = json_encode($as_edit_entry_value);
        
        $var .= '<input type="hidden" id="arf_edit_select_value_array_'.$entry->id.'" value="'.htmlspecialchars($json_data).'" />';

        $var .= '<script type="text/javascript">';
        $var .= 'var __ARF_edit_select_value_array = ' . json_encode($as_edit_entry_value);
        $var .= '</script>';

        $var .= '</td></tr>';



        $temp_var = apply_filters('arf_incomplete_entry_payment_detail', $id);

        $var .= ( $temp_var != $id ) ? $temp_var : '';
        $data['page_url'] = isset($data['page_url']) ? $data['page_url'] : '';
        $data['http_referrer'] = isset($data['http_referrer']) ? $data['http_referrer'] : '';

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . esc_html__('Page url', 'ARForms') . ':</td><td class="arfviewentry_right"><span class="arf_not_editable_values_container">' . urldecode($data['page_url']) . '</span>';
        $var .= '</td></tr>';

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . addslashes(esc_html__('Referrer url', 'ARForms')) . ':</td><td class="arfviewentry_right"><span class="arf_not_editable_values_container">' . urldecode($data['http_referrer']) . '</span>';
        $var .= '</td></tr>';

        $var = apply_filters('arfafterviewentrydetail', $var, $id);

        $var .= '</tbody></table>';

        return $var;

    }

    function get_repeater_entries_list_edit( $id, $repeater_field_id = '', $arffieldorder = array(), $incomplete = false, $atts_param = array(), $form_css = array() ){

        global $db_record, $arffield, $arfrecordmeta, $user_ID, $armainhelper, $arrecordhelper, $wpdb, $MdlDb;

        if( !$id ){
            $id = $armainhelper->get_param('id');
        }

        if( !$id ){
            $id = $armainhelper->get_param( 'entry_id' );
        }
        if( $incomplete ){
            $entry = $db_record->getOneIncomplete( $id, true );
        } else {
            $entry = $db_record->getOne( $id, true );
        }

        $updated_order = array();

        $get_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `".$MdlDb->fields."` WHERE field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%'", $repeater_field_id, $repeater_field_id ) );


        foreach( $arffieldorder[$repeater_field_id] as $fkey => $forder ){
            $exploded_data = explode( '|', $forder );
            $temp_fkey = $exploded_data[0];
            foreach( $get_inner_fields as $innerKey => $innerField ){
                if( $temp_fkey == $innerField->id ){
                    $updated_order[] = $temp_fkey;
                }
            }
        }

        $fields = array();

        $fields_arr = array();
        foreach( $updated_order as $field_id ){
            $fields[] = $arffield->getOne( $field_id );
        }

        $fields = apply_filters('arfpredisplayformcols', $fields, $entry->form_id);
        $entry = apply_filters('arfpredisplayonecol', $entry, $entry->form_id);



        $date_format = get_option('date_format');
        $time_format = get_option('time_format');

        $as_edit_entry_value = array();

        $var = '<table class="form-table">';

        $as_edit_repeater_entry_value = array();

        if(count($updated_order) > 0){

            $form_fields = array();
            foreach ($updated_order as $fieldorder => $fieldkey) {
                foreach ($fields as $fieldordkey => $fieldordval) {

                    if($fieldordval->id == $fieldkey ) {
                        $form_fields[] = $fieldordval;
                        unset($fields[$fieldordkey]);
                    }
                }
            }

            if(count($form_fields) > 0) {
                if(count($fields) > 0) {
                    $arfotherfields = $fields;
                    $fields = array_merge($form_fields, $arfotherfields);
                } else {
                    $fields = $form_fields;
                }
            }
        }

        $is_single_value = false;
        if( count( $fields ) > 1 ){
            $max_val = [];
            foreach( $fields as $field ){
                $field_value = isset( $entry->metas[$field->id]) ? $entry->metas[$field->id] : false;

                if( false == $field_value ){
                    $max_val[$field->id] = 0;
                } else {

                    if( !is_array( $field_value ) && preg_match( '/\[ARF_JOIN\]/', $field_value ) ){
                        $max_val[$field->id] = count( explode( '[ARF_JOIN]', $field_value ) );
                    } else if( is_array( $field_value ) ) {
                        $max_val[$field->id] = count( $field_value );
                    } else {
                        $max_val[$field->id] = 1;
                    }
                }
            }
            
            if( max( $max_val ) == 1 ){
                $is_single_value = true;
            }
        }
        $arf_list_view_cls = "";
        if( count( $fields ) == 1 || $is_single_value ){
            $n = 0;
            if( count( $fields ) > 1 ){
                $arf_list_view_cls = " arf_entry_verticle_view ";
            }
            foreach ($fields as $field) {

                $tr_class = 'arfviewentry_odd';
                if( $n % 2 == 0 ){
                    $tr_class = 'arfviewentry_even';
                }

                if (is_rtl()) {
                    $txt_align = 'text-align:right;';
                } else {
                    $txt_align = 'text-align:left;';
                }

                $field_value = isset($entry->metas[$field->id]) ? $entry->metas[$field->id] : false;
                $rowspan = 1;
                $valign_top_cls = '';
                $checkbox_val = '';

                if( !is_array( $field_value ) ){
                    if( preg_match( '/\[ARF_JOIN\]/', $field_value ) ){
                        $rowspan = count( explode( '[ARF_JOIN]', $field_value ) );
                        $valign_top_cls = 'arfviewentry_row_valign_top';
                    }

                    $var .= '<tr class="arfviewentry_row '.$tr_class.$arf_list_view_cls.'" valign="top" >';
                        $var .= '<td class="arfviewentry_left arfwidth25 '.$valign_top_cls.'" rowspan="'.$rowspan.'" scope="row">' . stripslashes($field->name) . ':</td>';
                        if( $rowspan > 1 ){
                            $exploded_data = explode( '[ARF_JOIN]', $field_value );                            
                            $var .= '<td class="arfviewentry_right" style="' . $txt_align .'">';

                                if( current_user_can( 'arfeditentries' ) && !$incomplete ){
                                    $var .= $display_value = $arrecordhelper->display_value_with_edit( $exploded_data[0], $field, array( 'type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), false, 0 );
                                    if( $field->type == 'date' ){
                                        $var .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'[0][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_0_date" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-counter="0" />';
                                    } else {
                                        $var .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'[0][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_0" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-counter="0" />';
                                    }
                                } else {
                                    $var .= $display_value = $arrecordhelper->display_value($exploded_data[0], $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id), $form_css, $incomplete);
                                }
                            $var .= '</td>';
                            for( $i = 1; $i <= count( $exploded_data ); $i++ ){
                                if( !isset( $exploded_data[$i] ) ){
                                    continue;
                                }
                                $var .= '<tr class="arfviewentry_row '.$tr_class.'" valign="top">';
                                    $var .= '<td class="arfviewentry_right" style="'. $txt_align .'">';
                                        if( current_user_can( 'arfeditentries' ) && !$incomplete ){
                                            $var .= $display_value = $arrecordhelper->display_value_with_edit( $exploded_data[$i], $field, array( 'type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), false, $i );
                                            if( $field->type == 'date' ){
                                                $var .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'['.$i.'][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_'.$i.'_date" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-editor="data125"  data-counter="'.$i.'" />';
                                            } else {
                                                $var .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'['.$i.'][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_'.$i.'" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-editor="data125"  data-counter="'.$i.'" />';
                                            }
                                        } else {
                                            $var .= $display_value = $arrecordhelper->display_value($exploded_data[$i], $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id),$form_css,$incomplete);
                                        }
                                    $var .= '</td>';
                                $var .= '</tr>';
                            }
                        } else {
                            $var .= '<td class="arfviewentry_right" style="' . $txt_align . '">';
                                if( current_user_can( 'arfeditentries' ) && !$incomplete ){

                                    if( preg_match( '/\!\|\!/', $field_value ) ){
                                        $fext = explode( "!|!", $field_value );
                                        $fval_ext = maybe_unserialize( $fext[0]  );
                                        if( is_array( $fval_ext ) ){
                                            if( count($fval_ext) > 1 ){
                                                $field_value = implode( ', ', $fval_ext );
                                            } else {
                                                $field_value = $fval_ext[0];
                                            }
                                        } else {
                                            $field_value = $fval_ext;
                                        }
                                    }

                                    $attachment_id = isset( $entry->attachment_id ) ? $entry->attachment_id : '';
                                    $var .= $display_value = $arrecordhelper->display_value_with_edit( $field_value, $field, array( 'type' => $field->type, 'attachment_id' => $attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), false, 0 );
                                    if( $field->type == 'date' ){
                                        $var .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'[0][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_0_date" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" />';
                                    } else {
                                        $var .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'[0][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_0" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" />';
                                    }
                                } else {
                                    $attachment_id = isset( $entry->attachment_id ) ? $entry->attachment_id : '';
                                    $var .= $display_value = $arrecordhelper->display_value($field_value, $field, array('type' => $field->type, 'attachment_id' => $attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id),$form_css,$incomplete);
                                }

                            $var .= '</td>';
                        } 
                    $var .= '</tr>';

                } else if( is_array( $field_value ) ){
                    $finner = array();
                    $mx  = array();

                    foreach( $field_value as $i => $field_val ){
                        if( preg_match ('/\!\|\!/', $field_val ) ){
                            $fext = explode('!|!', $field_val );
                            $fval_ext = maybe_unserialize( $fext[0]  );
                            $mx[] = $fext[1];
                            if( is_array( $fval_ext ) ){
                                $finner[$fext[1]] = implode( '[ARF_INNER_JOIN]', $fval_ext );
                            } else {
                                $finner[$fext[1]] = $fval_ext;
                            }
                        } else {
                            $mx[] = $i;
                            $finner[$i] = $field_val;
                        }
                    }

                    $field_value = $finner;

                    $rowspan = max( $mx );
                    if( $rowspan == 1 && count( $mx ) > 1 ){
                        $rowspan = count( $mx );
                    }

                    if( $rowspan > 1 ){
                        $valign_top_cls .= 'arfviewentry_row_valign_top';
                    }

                    $var .= '<tr class="arfviewentry_row '.$tr_class.$arf_list_view_cls.'" valign="top">';
                        $var .= '<td class="arfviewentry_left arfwidth25 '. $valign_top_cls .'" rowspan="'.count($finner).'" scope="row">' . stripslashes( $field->name ) . ':</td>';

                        if( $rowspan > 1 ){
                            if( isset( $finner[0] ) ){
                                $var .= '<td class="arfviewentry_right" style="'. $txt_align .'">';
                                    if( current_user_can( 'arfeditentries' ) && !$incomplete ){
                                        
                                        $var .= $display_value = $arrecordhelper->display_value_with_edit( $finner[0], $field, array( 'type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), $incomplete, 0 );
                                        if( 'date' == $field->type ){
                                            $var .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'[0][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_0_date" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-counter="0" />';
                                        } else {
                                            $var .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'[0][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_0" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-counter="0" />';
                                        }
                                    } else {
                                        $var .= $display_value = $arrecordhelper->display_value( $finner[0], $field, array( 'type' => $field->type, 'attachment_id' => $entry_attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry_id ), $form_css, $incomplete);
                                    }
                                $var .= '</td>';
                            }

                            for( $fi = 1; $fi <= $rowspan; $fi++ ){
                                if( !isset( $finner[$fi] ) ){
                                    continue;
                                }
                                $var .= '<tr class="arfviewentry_row '.$tr_class.'" valign="top">';
                                    $var .= '<td class="arfviewentry_right" style="'. $txt_align .'">';
                                        if( current_user_can( 'arfeditentries' ) && !$incomplete ){
                                            $var .= $display_value = $arrecordhelper->display_value_with_edit( $finner[$fi], $field, array( 'type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), $incomplete, $fi );
                                            if( $field->type == 'date' ){
                                                $var .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'['.$fi.'][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_'.$fi.'_date" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'"  data-counter="'.$fi.'" />';
                                            } else {
                                                $var .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'['.$fi.'][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_'.$fi.'" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'"  data-counter="'.$fi.'" />';
                                            }
                                        } else {
                                            $var .= $display_value = $arrecordhelper->display_value($finner[$fi], $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id), $form_css, $incomplete);
                                        }
                                    $var .= '</td>';
                                $var .= '</tr>';
                            }
                        } else {
                            if( count( $finner ) > 0 ){
                                $fixv = 0;
                                foreach( $finner as $finv ){
                                    if( $fixv == 0 ){
                                        $var .= '<td class="arfviewentry_right" style="' . $txt_align .'">';
                                            if( current_user_can( 'arfeditentries' ) && !$incomplete ){
                                                $var .= $display_value = $arrecordhelper->display_value_with_edit( $finv, $field, array( 'type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), $incomplete, 0 );
                                            } else {
                                                $var .= $display_value = $arrecordhelper->display_value($finv, $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id), $form_css, $incomplete);
                                            }
                                        $var .= '</td>';
                                    } else {
                                        $var .= '<tr class="arfviewentry_row '. $tr_class .'" valign="top">';
                                            $var .= '<td class="arfviewentry_right" style="' . $txt_align .'">';
                                                if( current_user_can( 'arfeditentries' ) && !$incomplete ){
                                                    $var .= $display_value = $arrecordhelper->display_value_with_edit( $finv, $field, array( 'type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), $incomplete, $fixv );
                                                } else {
                                                    $var .= $display_value = $arrecordhelper->display_value($finv, $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id), $form_css, $incomplete);
                                                }
                                            $var .= '</td>';
                                        $var .= '</tr>';
                                    }
                                    $fixv++;
                                }

                            } else if( count( $finner ) == 0 ) {

                            }
                            
                        }
                    $var .= '</tr>';
                }

                $field->field_options = arf_json_decode( $field->field_options, true );
                

                if( 'checkbox' == $field->type || $field->type == 'arf_multiselect'){
                    $as_edit_entry_value[$field->id] = $field_value;
                }

                if ($field->type == 'radio' || $field->type == 'select' || $field->type == 'arf_autocomplete') {
                    $as_edit_entry_value[$field->id] = $field_value;
                }

                $n++;
            }   
        } else if( count($fields) > 1 ){
            $arf_list_view_cls = " arf_entry_horizontal_view ";
            $n = 0;
            $var .= "<thead>";
            $var .= "<tr class='arfviewentry_row'>";
                foreach( $fields as $field ){
                    $var .= "<th class='arfvieweentry_row_head'>". $field->name ."</th>";
                }
            $var .= "</tr>";
            $var .= "</thead>";

            $var .= "<tbody>";
            for( $x = 0; $x < max($max_val); $x++ ){
                foreach( $fields as $field ){
                    $tr_class = 'arfviewentry_odd';
                    if( $n % 2 == 0 ){
                        $tr_class = 'arfviewentry_even';
                    }

                    if (is_rtl()) {
                        $txt_align = 'text-align:right;';
                    } else {
                        $txt_align = 'text-align:left;';
                    }
                    if( isset($entry->metas[$field->id]) && !is_array( $entry->metas[$field->id] ) ) {
                        $entry->metas[$field->id] = (array)$entry->metas[$field->id];
                    }
                    $field_value = isset($entry->metas[$field->id][$x]) ? $entry->metas[$field->id][$x] : false;
                    $field_value_updated = array();
                   
                    if( !is_array( $field_value ) ){
                        if( current_user_can( 'arfeditentries' ) && !$incomplete ){
                            if( preg_match( '/\!\|\!/', $field_value ) ){
                                $fext = explode( "!|!", $field_value );
                                $fval_ext = maybe_unserialize( $fext[0] );
                                if( is_array( $fval_ext ) ){
                                    if( count($fval_ext) > 1 ){
                                        $field_value = implode( ',', $fval_ext );
                                        $fval_ = implode('[ARF_INNER_JOIN]', $fval_ext );
                                    } else {
                                        $field_value = $fval_ext[0];
                                        $fval_ = $fval_ext[0];
                                    }
                                } else {
                                    $field_value = $fval_ext;
                                    $fval_ = $fval_ext;
                                }
                                $field_value_updated[$field->id][$x] = $fval_ext;
                            }
                            $display_value = $arrecordhelper->display_value_with_edit( $field_value, $field, array( 'type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), false, $x );
                            if( 'date' == $field->type ){
                                $display_value .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'['.$x.'][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_'.$x.'_date" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-counter="'.$x.'" />';
                            } else {
                                $display_value .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'['.$x.'][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_'.$x.'" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-counter="'.$x.'" />';
                            }
                            $fields_arr[$x][$field->id] = $display_value;
                        } else {
                            $display_value = $arrecordhelper->display_value($field_value, $field, array('type' => $field->type, 'attachment_id' => '', 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id),$form_css,$incomplete);
                            $fields_arr[$x][$field->id] = $display_value;
                        }
                    } else if( is_array( $field_value ) ) {
                        $finner = array();
                        $mx  = array();
                        $in = 0;
                        
                        foreach( $field_value as $i => $field_val ){

                            if( preg_match ('/\!\|\!/', $field_val ) ){
                                $fext = explode('!|!', $field_val );

                                $fval_ext = maybe_unserialize( $fext[0] );

                                $mx[] = $fext[1];

                                if( is_array( $fval_ext ) ){
                                    $fval_ = implode( '[ARF_INNER_JOIN]', $fval_ext );

                                } else {
                                    $fval_ = $fval_ext;
                                }

                                $field_value_updated[$field->id][] = $fval_;

                                if( current_user_can( 'arfeditentries' ) && !$incomplete ) {
                                    $display_value = $arrecordhelper->display_value_with_edit( $fval_, $field, array( 'type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), $incomplete, $in );
                                    if( 'date' == $field->type ){
                                        $display_value .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'['.$in.'][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_'.$in.'_date" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-counter="'.$in.'" />';
                                    } else {
                                        $display_value .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'['.$in.'][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_'.$in.'" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-counter="'.$in.'" />';
                                    }
                                } else {
                                    $display_value = $arrecordhelper->display_value( $fval_, $field, array( 'type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), $form_css, $incomplete, $n );
                                }
                                if( !isset( $fields_arr[$x][$field->id]) ){
                                    $fields_arr[$x][$field->id] = $display_value;
                                }
                            } else {
                                $field_value_updated[$field->id][] = $field_val;
                                if( current_user_can('arfeditentries') && !$incomplete){
                                    $display_value = $arrecordhelper->display_value_with_edit( $field_val, $field, array( 'type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), $incomplete, $in );
                                    if( 'date' == $field->type ){
                                        $display_value .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'['.$in.'][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_'.$in.'_date" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-counter="'.$in.'" />';
                                    } else {
                                        $display_value .= '<input type="hidden" name="arf_edit_form_field_values_' . $entry->id.'['.$in.'][]" id="arf_edit_new_values_'. $field->id.'_'.$entry->id.'_'.$in.'" value="" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" data-counter="'.$in.'" />';
                                    }
                                } else {
                                    $display_value = $arrecordhelper->display_value( $field_val, $field, array( 'type' => $field->type, 'attachment_id' => $entry_attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), $form_css, $incomplete);
                                }
                                if( !isset( $fields_arr[$x][$field->id] ) ){
                                    $fields_arr[$x][$field->id] = $display_value;
                                }
                            }
                            $in++;
                        }
                    }

                    $field->field_options = arf_json_decode( $field->field_options, true );

                    if( 'checkbox' == $field->type || $field->type == 'arf_multiselect' ){
                        if( !empty( $field_value_updated[$field->id] ) ){
                            $as_edit_entry_value[$field->id][] = $field_value_updated[$field->id][$x];
                        }
                    }

                    if ($field->type == 'radio' || $field->type == 'select' || $field->type == 'arf_autocomplete') {
                        if( !empty( $field_value_updated[$field->id] ) ){
                            $as_edit_entry_value[$field->id][] = $field_value_updated[$field->id][$x];
                        }
                    }
                    $n++;
                }
            }
            foreach( $fields_arr as $k => $farr ){
                $var .= "<tr class='arfviewentry_row ".$arf_list_view_cls."'>";
                    foreach( $fields as $field ){
                        if( isset( $farr[$field->id] ) ){
                            $var .= "<td class='arfviewentry_right'>" . $farr[$field->id] . "</td>";
                        } else {
                            $var .= "<td class='arfviewentry_right'>" . '-' . "</td>";
                        }
                    }
                $var .= "</tr>";
            }
        }

        $json_data = json_encode($as_edit_entry_value);
        
        $var .= '<input type="hidden" id="arf_edit_select_value_array_'.$entry->id.'_'.$repeater_field_id.'" value="'.htmlspecialchars($json_data).'" />';

        $var .= '<script type="text/javascript">';
        $var .= 'var __ARF_edit_select_value_array_'.$entry->id.'_'.$repeater_field_id.' = ' . json_encode($as_edit_entry_value) . ';';
        $var .= '</script>';

        $var .= '</tbody></table>';

        return $var;

    }

    function get_incomplete_entries_list_view($id = '', $arffieldorder = array(), $arfinnerfieldorder = array(), $form_css = array(), $incomplete_entry = true, $form_name = '') {
        global $db_record, $arffield, $arfrecordmeta, $user_ID, $armainhelper, $arrecordhelper;

        
        if (!$id)
            $id = $armainhelper->get_param('id');


        if (!$id)
            $id = $armainhelper->get_param('entry_id');
        
        $entry = $db_record->getOneIncomplete($id, true);
        
        if ( !empty( $entry ) && isset( $entry ) ) {
        
            $data = maybe_unserialize($entry->description);

            if (!is_array($data) or ! isset($data['referrer']))
                $data = array('referrer' => $data);


            $fields = $arffield->getAll("fi.type not in ('captcha', 'imagecontrol') and fi.form_id=" . (int) $entry->form_id);
            
            
            $fields = apply_filters('arfpredisplayformcols', $fields, $entry->form_id);
            $entry = apply_filters('arfpredisplayonecol', $entry, $entry->form_id);
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            $show_comments = true;

            if ($show_comments) {
                $comments = $arfrecordmeta->getAll("entry_id=$id and field_id=0", ' ORDER BY it.created_date ASC');
                $to_emails = apply_filters('arftoemail', array(), $entry, $entry->form_id);
            }

            $var = '<table class="form-table"><tbody>';
            
            $as_edit_matrix_entry_value = array();
            
            if(count($arffieldorder) > 0){

                $form_fields = array();
                foreach ($arffieldorder as $fieldkey => $fieldorder) {
                    foreach ($fields as $fieldordkey => $fieldordval) {
                        if($fieldordval->id == $fieldkey && !isset( $fieldordval->field_options['has_parent'] ) && $fieldordval->type != 'arf_repeater' ) {
                            if( $fieldordval->type != 'section' ){
                                $form_fields[] = $fieldordval;
                            } else {
                                foreach( $arfinnerfieldorder[$fieldordval->id] as $inner_field_order ){
                                    $exploded_data = explode('|',$inner_field_order);
                                    $inner_field_id = (int)$exploded_data[0];
                                    foreach( $fields as $ifieldordkey => $ifieldordval ){
                                        if( $ifieldordval->id == $inner_field_id ){
                                            $form_fields[] = $ifieldordval;
                                            unset( $fields[$ifieldordkey]);
                                        }
                                    }
                                }
                            }
                            unset($fields[$fieldordkey]);
                        }

                        if( $fieldordval->type == 'arf_repeater' || ( isset( $fieldordval->field_options['parent_field_type'] ) && $fieldordval->field_options['parent_field_type']  =='arf_repeater' ) ){
                            unset( $fields[$fieldordkey] );
                        }
                         if( $fieldordval->type == 'html' || ( isset( $fieldordval->field_options['enable_total'] ) && $fieldordval->field_options['enable_total']  =='0' ) ){
                            unset( $fields[$fieldordkey] );
                        }
                    }
                }

                if(count($form_fields) > 0) {
                    if(count($fields) > 0) {
                        $arfotherfields = $fields;
                        $fields = array_merge($form_fields, $arfotherfields);
                    } else {
                        $fields = $form_fields;
                    }
                }
            }
            
            
            foreach ($fields as $field) {


                if ($field->type == 'divider' || $field->type == 'section') {
                    $var .= '</tbody></table>
                        <div class="arfentrydivider">' . stripslashes($field->name) . '</div>
                        <table class="form-table"><tbody>';
                } else if ($field->type == 'break') {
                    $var .= '</tbody></table>
                    <div class="arfpagebreakline"></div>
                    <table class="form-table"><tbody>';
                } else if( 'matrix' == $field->type ){
                    $var .= '</tbody></table>';
                    $var .= '<div class="arfentrydivider" style="text-align:left">' . stripslashes( $field->name ) . '</div>';
                    $var .= '<table class="form-table"></tbody>';
                        $var .= "<tr>";
                            $var .= "<td class='arf_matrix_inner_table_data' colspan='2'>";
                                $var .= "<table class='form-table'><tbody>";
                                    $matrix_fopts = arf_json_decode($field->field_options, true);
                                    $matrix_frows = arf_json_decode($matrix_fopts['rows'], true );
                                    $source_data = array();
                                    if( $matrix_fopts['separate_value'] != 1 ){
                                        foreach( $matrix_fopts['options'] as $mat_opts ){
                                            $source_data[ $mat_opts ] = $mat_opts;
                                        }
                                    }
                                    
                                    if( !empty( $matrix_frows ) ){
                                        $saved_data = $entry->metas[ $field->id ];
                                        $x = 0;
                                        foreach( $matrix_frows as $matrix_row ){
                                            $var .= "<tr class='arfviewentry_row' valign='top'>";
                                                $var .= "<td class='arfviewentry_left arfwidth25' scope='row'>" . stripslashes_deep( $matrix_row ) . "</td>";
                                                $var .= "<td class='arfviewentry_right'>";
                                                    if( current_user_can( 'arfeditentries' ) ){
                                                        $var .= '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="select" data-id="'.$field->id.'" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span class="arf_editable_values_container arf_edit_type_matrix_option_'.$field->id.'" id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" data-source=\''.json_encode($source_data).'\' data-type="select" data-pk="1" data-separate-value="'.$field->field_options['separate_value'].'" >' . ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '-' ) . '</span>';
                                                        $var .= '<input type="hidden" name="arf_edit_form_field_values_'.$field->id.'['.$x.']" id="arf_edit_new_values_'.$field->id.'_'.$entry->id.'" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" />';
                                                        if( !$matrix_fopts['separate_value'] != 1 ){
                                                            $as_edit_matrix_entry_value[$field->id][$x] = ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '' );
                                                        }
                                                    } else {
                                                        $var .= '<span class="arf_not_editable_values_container">'.( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '-' ).'</span>';
                                                    }
                                                $var .= "</td>";
                                            $var .= "</tr>";
                                            $x++;
                                        }
                                    }
                                $var .= "</table>";
                            $var .= "</td>";
                        $var .= "</tr>";
                } else {

                    if (is_rtl()) {
                        $txt_align = 'text-align:right;';
                    } else {
                        $txt_align = 'text-align:left;';
                    }
                    $var .= '<tr class="arfviewentry_row" valign="top">
                                <td class="arfviewentry_left arfwidth25" scope="row">' . stripslashes($field->name) . ':</td>
                                <td  class="arfviewentry_right" style="' . $txt_align . '">';

                    $field_value = isset($entry->metas[$field->id]) ? $entry->metas[$field->id] : false;

                    $field->field_options = arf_json_decode($field->field_options, true);

                    

                    $var .= $display_value = $arrecordhelper->display_value( $field_value, $field, array( 'type' => $field->type, 'attachment_id' => isset( $entry->attachment_id ) ? $entry->attachment_id: '', 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id ), $form_css, $incomplete_entry, $form_name );

                    if (is_email($display_value) and ! in_array($display_value, $to_emails))
                        $to_emails[] = $display_value;

                    $var .= '</td>
                            </tr>';
                }
            }

            $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . addslashes(esc_html__('Created at', 'ARForms')) . ':</td><td class="arfviewentry_right"><span>' . $armainhelper->get_formatted_time($entry->created_date, $date_format, $time_format) .'</span>';
            
            $var .= '</td></tr>';


            $temp_var = apply_filters('arf_entry_payment_detail', $id);

            $var .= ( $temp_var != $id ) ? $temp_var : '';
            $data['page_url'] = isset($data['page_url']) ? $data['page_url'] : '';
            $data['http_referrer'] = isset($data['http_referrer']) ? $data['http_referrer'] : '';

            $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . esc_html__('Page url', 'ARForms') . ':</td><td class="arfviewentry_right"><span>' . urldecode($data['page_url']) . '</span>';
            $var .= '</td></tr>';

            $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . addslashes(esc_html__('Referrer url', 'ARForms')) . ':</td><td class="arfviewentry_right"><span>'. urldecode($data['http_referrer']) . '</span>';
            $var .= '</td></tr>';

            $var = apply_filters('arfafterviewentrydetail', $var, $id);

            $var .= '</tbody></table>';

            return $var;
        }
    }

    function get_entries_list_edit($id = '', $arffieldorder = array(), $arfinnerfieldorder = array()) {

        global $db_record, $arffield, $arfrecordmeta, $user_ID, $armainhelper, $arrecordhelper;

        if (!$id)
            $id = $armainhelper->get_param('id');


        if (!$id)
            $id = $armainhelper->get_param('entry_id');



        $entry = $db_record->getOne($id, true);

        $data = maybe_unserialize($entry->description);

        if (!is_array($data) or ! isset($data['referrer']))
            $data = array('referrer' => $data);

        $fields = wp_cache_get('get_entries_list_edit_'.$entry->form_id);            
        if( false == $fields ){
            $fields = $arffield->getAll("fi.type not in ('captcha', 'imagecontrol') and fi.form_id=" . (int) $entry->form_id);
            wp_cache_set('get_entries_list_edit_'.$entry->form_id, $fields);
        }
        
        $fields = apply_filters('arfpredisplayformcols', $fields, $entry->form_id);
        $entry = apply_filters('arfpredisplayonecol', $entry, $entry->form_id);

        $date_format = get_option('date_format');

        $time_format = get_option('time_format');

        $show_comments = true;

        if ($show_comments) {

            $comments = $arfrecordmeta->getAll("entry_id=$id and field_id=0", ' ORDER BY it.created_date ASC');

            $to_emails = apply_filters('arftoemail', array(), $entry, $entry->form_id);
        }

        $var = '<table class="form-table"><tbody>';

        $as_edit_entry_value = array();
        $as_edit_matrix_entry_value = array();

        if(count($arffieldorder) > 0){

            $form_fields = array();
            foreach ($arffieldorder as $fieldkey => $fieldorder) {
                foreach ($fields as $fieldordkey => $fieldordval) {
                    if($fieldordval->id == $fieldkey && !isset( $fieldordval->field_options['has_parent'] ) && $fieldordval->type != 'arf_repeater' ) {
                        if( $fieldordval->type != 'section' ){
                            $form_fields[] = $fieldordval;
                        } else {
                            foreach( $arfinnerfieldorder[$fieldordval->id] as $inner_field_order ){
                                $exploded_data = explode('|',$inner_field_order);
                                $inner_field_id = (int)$exploded_data[0];
                                foreach( $fields as $ifieldordkey => $ifieldordval ){
                                    if( $ifieldordval->id == $inner_field_id ){
                                        $form_fields[] = $ifieldordval;
                                        unset( $fields[$ifieldordkey]);
                                    }
                                }
                            }
                        }
                        unset($fields[$fieldordkey]);
                    }

                    if( $fieldordval->type == 'arf_repeater' || ( isset( $fieldordval->field_options['parent_field_type'] ) && $fieldordval->field_options['parent_field_type']  =='arf_repeater' ) ){
                        unset( $fields[$fieldordkey] );
                    }
                    if( $fieldordval->type == 'html' || ( isset( $fieldordval->field_options['enable_total'] ) && $fieldordval->field_options['enable_total']  =='0' ) ){
                        unset( $fields[$fieldordkey] );
                    }
                }
            }

            if(count($form_fields) > 0) {
                if(count($fields) > 0) {
                    $arfotherfields = $fields;
                    $fields = array_merge($form_fields, $arfotherfields);
                } else {
                    $fields = $form_fields;
                }
            }
        }



        foreach ($fields as $field) {


            if ($field->type == 'divider' || $field->type == 'section') {


                $var .= '</tbody></table>
                    <div class="arfentrydivider">' . stripslashes($field->name) . '</div>
                    <table class="form-table"><tbody>';
            } else if ($field->type == 'break') {
                $var .= '</tbody></table>
                <div class="arfpagebreakline"></div>
                <table class="form-table"><tbody>';
            } else if( 'matrix' == $field->type ){
                    $var .= "<tr>";
                        $var .= "<td class='arfviewentry_left arf_matrix_inner_table_data' colspan='2'>";
                            $var .= stripslashes( $field->name );
                        $var .= "</td>";
                    $var .= "</tr>";
                    $var .= "<tr class='arf_matrix_inner_table_wrapper'>";
                        $var .= "<td class='arf_matrix_inner_table_data' colspan='2'>";
                            $var .= "<table class='form-table'><tbody>";
                                $matrix_fopts = arf_json_decode($field->field_options, true);
                                $matrix_frows = arf_json_decode($matrix_fopts['rows'], true );
                                $source_data = array();
                                if( $matrix_fopts['separate_value'] != 1 ){
                                    foreach( $matrix_fopts['options'] as $mat_opts ){
                                        $source_data[ $mat_opts ] = $mat_opts;
                                    }
                                } else {
                                    foreach( $matrix_fopts['options'] as $mat_opts){
                                        $source_data[ $mat_opts['value'] ] = $mat_opts['label'];
                                    }
                                }
                                
                                if( !empty( $matrix_frows ) ){
                                    if( !isset($entry->metas[ $field->id ]) ){
                                        $entry->metas[ $field->id ] = array();
                                    }
                                    
                                    $saved_data = $entry->metas[ $field->id ];
                                    $x = 0;
                                    foreach( $matrix_frows as $matrix_row ){
                                        $var .= "<tr class='arfviewentry_row' valign='top'>";
                                            $var .= "<td class='arfviewentry_left arfwidth25' scope='row'>" . stripslashes_deep( $matrix_row ) . "</td>";
                                            $var .= "<td class='arfviewentry_right'>";
                                                if( current_user_can( 'arfeditentries' ) ){
                                                    $var .= '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="matrix" data-id="'.$field->id.'" data-entry-id="' . $entry->id . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span class="arf_editable_values_container arf_edit_type_matrix_option_'.$field->id.'_'.$x.'" id="arf_value_' . $entry->id . '_' . $field->id . '" data-matrix-inner-id="'.$x.'" data-id="'.$field->id.'" data-field-type="'.$field->type.'" data-entry-id="'.$entry->id.'" data-source=\''.json_encode($source_data).'\' data-type="select" data-pk="1" data-separate-value="'.$field->field_options['separate_value'].'" >';

                                                    if( 1 == $matrix_fopts['separate_value'] && !empty( $saved_data[ $x ] ) ){
                                                        $temp_val = '';
                                                        foreach( $matrix_fopts['options'] as $mat_opts){
                                                            if( $saved_data[$x] == $mat_opts['value'] ){
                                                                $temp_val = $mat_opts['label'] . ' (' . $mat_opts['value'] . ')';
                                                            }
                                                        }
                                                        $var .= $temp_val;
                                                    } else {
                                                        $var .= ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '-' );
                                                    }

                                                    $var .= '</span>';
                                                    $var .= '<input type="hidden" name="arf_edit_matrix_field_values['.$entry->id.']['.$x.']" id="arf_edit_new_matrix_values_'.$field->id.'_'.$entry->id.'_'.$x.'" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" />';
                                                    
                                                    $as_edit_matrix_entry_value[$field->id][$x] = ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '' );
                                                } else {
                                                    $var .= '<span class="arf_not_editable_values_container">';
                                                    if( 1 == $matrix_fopts['separate_value'] && !empty( $saved_data[ $x ] ) ){
                                                        $temp_val = '';
                                                        foreach( $matrix_fopts['options'] as $mat_opts){
                                                            if( $saved_data[$x] == $mat_opts['value'] ){
                                                                $temp_val = $mat_opts['label'] . ' (' . $mat_opts['value'] . ')';
                                                            }
                                                        }
                                                        $var .= $temp_val;
                                                    } else {
                                                        $var .= ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '-' );
                                                    }
                                                    $var .= '</span>';
                                                }
                                            $var .= "</td>";
                                        $var .= "</tr>";
                                        $x++;
                                    }
                                }
                            $var .= "</table>";
                        $var .= "</td>";
                    $var .= "</tr>";
            } else {

                if (is_rtl()) {
                    $txt_align = 'text-align:right;';
                } else {
                    $txt_align = 'text-align:left;';
                }
                $var .= '<tr class="arfviewentry_row" valign="top">


                            <td class="arfviewentry_left arfwidth25" scope="row">' . stripslashes($field->name) . ':</td>


                            <td  class="arfviewentry_right" style="' . $txt_align . '">';

                $field_value = isset($entry->metas[$field->id]) ? $entry->metas[$field->id] : false;


                $field->field_options = arf_json_decode($field->field_options, true);



                if ($field->type == 'checkbox' || $field->type == 'arf_multiselect') {
                    $as_edit_entry_value[$field->id] = $field_value;
                }

                if ($field->type == 'radio' || $field->type == 'select' || $field->type == 'arf_autocomplete') {
                    $as_edit_entry_value[$field->id] = $field_value;
                }
                if( current_user_can('arfeditentries') ){
                    $var .= $display_value = $arrecordhelper->display_value_with_edit($field_value, $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id));
                } else {
                    $var .= $display_value = $arrecordhelper->display_value($field_value, $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id));
                }

                if( $field->type == 'date' ){
                    $var .= '<input type="hidden" id="arf_edit_new_values_'.$field->id.'_'.$entry->id.'" value="" data-id="' . $field->id . '" data-entry-id="' . $entry->id . '">';
                    $var .= '<input type="hidden" name="arf_edit_form_field_values_'.$entry->id.'[]" id="arf_edit_new_values_'.$field->id.'_'.$entry->id.'_date" value="" data-id="' . $field->id . '" data-entry-id="' . $entry->id . '">';
                } else {
                    $var .= '<input type="hidden" name="arf_edit_form_field_values_'.$entry->id.'[]" id="arf_edit_new_values_'.$field->id.'_'.$entry->id.'" value="" data-id="' . $field->id . '" data-entry-id="' . $entry->id . '">';
                }

                if (is_email($display_value) and ! in_array($display_value, $to_emails))
                    $to_emails[] = $display_value;



                $var .= '</td>
                        </tr>';
            }
        }

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . addslashes(esc_html__('Created at', 'ARForms')) . ':</td><td class="arfviewentry_right"><span class="arf_not_editable_values_container">' . date( $date_format.' '.$time_format, strtotime($entry->created_date) ) .'</span>';
        if ($entry->user_id) {
            
        }

        $json_data = json_encode($as_edit_entry_value);
        
        $var .= '<input type="hidden" id="arf_edit_select_value_array_'.$entry->id.'" value="'.htmlspecialchars($json_data).'" />';
        $var .= '<input type="hidden" id="arf_edit_matrix_value_array_' . $entry->id .'" value="' . htmlspecialchars( json_encode( $as_edit_matrix_entry_value )  ) . '" />';

        $var .= '<script type="text/javascript">';
        $var .= 'var __ARF_edit_select_value_array = ' . json_encode($as_edit_entry_value) . ';';
        $var .= 'var __ARF_edit_matrix_value_array = ' . json_encode($as_edit_matrix_entry_value) . ';';
        $var .= '</script>';

        $var .= '</td></tr>';



        $temp_var = apply_filters('arf_entry_payment_detail', $id);

        $var .= ( $temp_var != $id ) ? $temp_var : '';
        $data['page_url'] = isset($data['page_url']) ? $data['page_url'] : '';
        $data['http_referrer'] = isset($data['http_referrer']) ? $data['http_referrer'] : '';

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . esc_html__('Page url', 'ARForms') . ':</td><td class="arfviewentry_right"><span class="arf_not_editable_values_container">' . urldecode($data['page_url']) . '</span>';
        $var .= '</td></tr>';

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . addslashes(esc_html__('Referrer url', 'ARForms')) . ':</td><td class="arfviewentry_right"><span class="arf_not_editable_values_container">' . urldecode($data['http_referrer']) . '</span>';
        $var .= '</td></tr>';

        $var = apply_filters('arfafterviewentrydetail', $var, $id);

        $var .= '</tbody></table>';

        return $var;
    }

    function arfentryactionfunc() {

        global $db_record;

        if ($_REQUEST['act'] == 'delete' and $_REQUEST['id'] != '') {

            $del_res = $db_record->destroy($_REQUEST['id']);

            if ($del_res)
                $message = addslashes(esc_html__('Entry deleted successfully', 'ARForms'));

            $errors = array();

            return $this->frm_change_entries($_POST['form'], $_POST['start_date'], $_POST['end_date'], '1', $message, $errors);
        }


        die();
    }

    function include_css_from_form_content($post_content) {

        global $post, $submit_ajax_page, $arfversion, $arf_jscss_version;

        $submit_ajax_page = 1;

        $wp_upload_dir = wp_upload_dir();
        if (is_ssl()) {
            $upload_main_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/maincss');
        } else {
            $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
        }

        $parts = explode("[ARForms", $post_content);
        $myidpart = explode("id=", $parts[1]);
        $myid = explode("]", $myidpart[1]);



        if (!is_admin()) {
            global $wp_query;
            $posts = $wp_query->posts;
            $pattern = get_shortcode_regex();


            if (preg_match_all('/' . $pattern . '/s', $post_content, $matches) && array_key_exists(2, $matches) && in_array('ARForms', $matches[2])) {
                
            }

            $formids = array();

            foreach ($matches as $k => $v) {
                foreach ($v as $key => $val) {
                    $parts = explode("id=", $val);
                    if ($parts > 0) {

                        if (@stripos($parts[1], ']') !== false) {
                            $partsnew = @explode("]", $parts[1]);
                            $formids[] = @$partsnew[0];
                        } else if (@stripos($parts[1], ' ') !== false) {

                            $partsnew = @explode(" ", $parts[1]);
                            $formids[] = @$partsnew[0];
                        } else {
                            
                        }
                    }
                }
            }

            $newvalarr = array();

            if (is_array($formids) && count($formids) > 0) {
                foreach ($formids as $newkey => $newval) {
                    if (stripos($newval, ' ') !== false) {
                        $partsnew = explode(" ", $newval);
                        $newvalarr[] = $partsnew[0];
                    } else
                        $newvalarr[] = $newval;
                }
            }

            if (is_array($newvalarr) && count($newvalarr) > 0) {
                $newvalarr = array_unique($newvalarr);
                foreach ($newvalarr as $newkey => $newval) {
                    $fid1 = $upload_main_url . '/maincss_' . $newval . '.css';

                    wp_register_style('arfformscss_' . $newval, $upload_main_url . '/maincss_' . $newval . '.css', array(), $arf_jscss_version);
                    wp_print_styles('arfformscss_' . $newval);
                }
            }
        }
    }

    function ajax_check_recaptcha() {

        global $wpdb, $errors, $arfieldhelper, $maincontroller;

        $errors = array();

        $arf_options = get_option('arf_options');

        $default_blank_msg = $arf_options->blank_msg;

        $fields = $arfieldhelper->get_form_fields_tmp(false, $_POST['form_id'], false, 0);

        foreach ($fields as $field) {
            $field_id = $field->id;

                if ($field->type == 'captcha' and isset($_POST['recaptcha_challenge_field'])) {

                    $maincontroller->arfafterinstall();

                    global $arfsettings;

                    require_once(FORMPATH . '/core/recaptchalib/recaptchalib.php');

                    $site_key = $arfsettings->pubkey;
                    $private_key = $arfsettings->privkey;


                    if ($site_key == "" || $private_key == "") {

                        $errors[$field_id] = (!isset($field->field_options['invalid']) or $field->field_options['invalid'] == '') ? $arfsettings->re_msg : $field->field_options['invalid'];
                    } else {

                        $recaptcha = new ARForms_ReCaptcha($private_key);

                        $response = $recaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $_POST['g-recaptcha-response']);


                        if ($response->success) {
                            $errors['captcha'] = 'success';
                            $_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']] = 1;
                        } else {
                            $errors[$field_id] = (!isset($field->field_options['invalid']) or $field->field_options['invalid'] == '') ? $arfsettings->re_msg : $field->field_options['invalid'];
                        }
                    }
                }
        }

        echo json_encode($errors);
        die();
    }

    function internal_check_recaptcha(){
        global $wpdb, $errors, $arfform, $arfsettings;

        $errors = '';

        $form_id = !empty( $_POST['form_id'] ) ? $_POST['form_id'] : false;
        $form_data_id = !empty( $_POST['form_data_id'] ) ? $_POST['form_data_id'] : false;

        $captcha_failed_msg = !empty( $arfsettings->re_msg ) ? $arfsettings->re_msg : 'Invalid reCaptcha. Please try again';

        if( false === $form_id || false === $form_data_id ){
            return $errors;
        }

        $privkey = $arfsettings->privkey;
        $pubkey = $arfsettings->pubkey;

        $form_opts = $arfform->getOne( $form_id );

        $form_options = maybe_unserialize( $form_opts->options );

        if( !empty( $form_options['arf_enable_recaptcha']) && !empty( $privkey ) && !empty( $pubkey ) ){
            $captcha_field = !empty( $_POST['arf_captcha_' . $form_data_id ] ) ? $_POST['arf_captcha_' . $form_data_id ] : '';

            if( empty( $captcha_field ) ){
                $errors = '<div class="arf_form ar_main_div_{arf_form_id} arf_error_wrapper" id="arffrm_{arf_form_id}_container"><div class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $captcha_failed_msg . '</div></div></div></div>';
                return $errors;
            }

            require_once FORMPATH . '/core/recaptchalib/recaptchalib.php';

            $sitekey = $arfsettings->pubkey;
            $secret = $arfsettings->privkey;

            $recaptcha = new ARForms_ReCaptcha($secret);
            $response = $recaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $captcha_field );

            if( empty( $response->success ) || 1 != $response->success ){
                $errors = '<div class="arf_form ar_main_div_{arf_form_id} arf_error_wrapper" id="arffrm_{arf_form_id}_container"><div class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $captcha_failed_msg . '</div></div></div></div>';
            }
        }

        return $errors;
    }


    function getBrowser($user_agent) {
        $u_agent = $user_agent;
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";


        if (@preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (@preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (@preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        $ub = "Unknown";
        
        if (@preg_match('/MSIE/i', $u_agent) && !@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (@preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (@preg_match('/OPR/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "OPR";
        } elseif ( @preg_match('/Edge/i', $u_agent) || @preg_match('/Edg/i', $u_agent) ) {
            $bname = 'Edge';
            $ub = "Edg";
        } elseif (@preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (@preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (@preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (@preg_match('/Trident/', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "rv";
        }

        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ |:]+(?<version>[0-9.|a-zA-Z.]*)#';

        if (!@preg_match_all($pattern, $u_agent, $matches)) {
            
        }

        $i = count($matches['browser']);
        if ($i != 1) {
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = isset( $matches['version'][0] ) ? $matches['version'][0] : 'unknown';
            } else {
                $version = isset( $matches['version'][1] ) ? $matches['version'][1] : 'unknown';
            }
        } else {
            $version = isset( $matches['version'][0] ) ? $matches['version'][0] : 'unknown';
        }


        if ( ( $version == null || $version == "" ) && !preg_match('/Edg/i', $u_agent) ) {
            $version = "?";
        } else if( @preg_match('/Edg/i', $u_agent) && ( "" == $version || null ==  $version ) ){
            $version = preg_replace('/(.*?)(Edg\/)(\d+)/', '$3', $u_agent);
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    function current_modal() {
        if (isset($_REQUEST['position_modal']) && $_REQUEST['position_modal'] != '') {
            $current_modal = $_REQUEST['position_modal'];
            $_SESSION['last_open_modal'] = $current_modal;
            echo $_SESSION['last_open_modal'];
            exit;
        }
    }

    function arf_edit_repeater_entry_values(){
        global $wpdb, $arffield, $MdlDb, $armainhelper, $arfieldhelper;

        $arf_return = array(
            'status' => 'error',
            'message' => addslashes( esc_html__( 'Record could not be updated', 'ARForms' ) )
        );

        $arfform_id = ( isset( $_POST['arf_form'] ) && !empty( $_POST['arf_form'] ) ) ? $_POST['arf_form'] : '';
        $entry_id = (isset($_POST['entry_id']) && !empty($_POST['entry_id'])) ? $_POST['entry_id'] : '';
        $arfupdatedfields = (isset($_POST['updatedfields']) && !empty($_POST['updatedfields'])) ? explode("||", $_POST['updatedfields']) : array();
        $arfupdatedfieldvalues = (isset($_POST['newvalues']) && !empty($_POST['newvalues'])) ? explode("||", $_POST['newvalues']) : array();
        $field_types = (isset($_POST['field_types']) && !empty($_POST['field_types'])) ? explode("||", $_POST['field_types']) : array();
        $counterData = (isset($_POST['counter']) && !empty($_POST['counter'])) ? explode("||", $_POST['counter']) : array();
        $totalRows = (isset($_POST['total_rows']) && !empty($_POST['total_rows'])) ? $_POST['total_rows'] : 1;
        
        if( is_array( $counterData ) && empty( $counterData ) && ( $_POST['counter'] == '0' || $_POST['counter'] == '' ) ){
            $counterData[] = 0;
        }

        $form_cols = array();
        $arfdraw_cols = array();
        $edit_select_options = array();

        if( !empty($entry_id) && count( $arfupdatedfields) > 0 ){
            if( $arfform_id != '' && $arfform_id != '-1' ){
                $form_cols = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `" . $MdlDb->fields ."` WHERE form_id = %d AND type NOT IN ('divider', 'section', 'captcha', 'break', 'html', 'imagecontrol')", $arfform_id) );
                $form_cols = apply_filters( 'arfpredisplayformcols', $form_cols, $arfform_id );
            }

            $arf_columns = array( '0' => '', '1' => 'id' );

            if( count( $form_cols ) > 0 ){
                $get_form_options = $wpdb->get_row( $wpdb->prepare("SELECT `options` FROM `".$MdlDb->forms."` WHERE `id` = %d AND `is_template` != %d AND `status` = %s", $arfform_id, 1,'published') );
                $form_options = maybe_unserialize($get_form_options->options);

                $arffieldorder = array();
                if( isset( $form_options['arf_inner_field_order']) && $form_options['arf_inner_field_order'] != '' ){
                    $arffieldorder = json_decode( $form_options['arf_inner_field_order'], true );
                    asort( $arffieldorder );
                }

                if(count($arffieldorder) > 0){
                    $form_cols_temp = array();
                    foreach ($arffieldorder as $fieldkey => $fieldorder) {
                        foreach ($form_cols as $frmoptkey => $frmoptarr) {
                            if($frmoptarr->id == $fieldkey){
                                $form_cols_temp[] = $frmoptarr;
                                unset($form_cols[$frmoptkey]);
                            }
                        }
                    }

                    if(count($form_cols_temp) > 0) {
                        if(count($form_cols) > 0) {
                            $form_cols_other = $form_cols;
                            $form_cols = array_merge($form_cols_temp,$form_cols_other);
                        } else {
                            $form_cols = $form_cols_temp;
                        }
                    }
                }

                for ($col_i = 2; $col_i <= count($form_cols) + 1; $col_i++) {
                    $col_j = $col_i - 2;
                    $arf_columns[$form_cols[$col_j]->id] = $col_i;
                }
            }

            $update_field_ids = array();

            $new_value_array = array();

            $updated_new_vals = array();
            
            foreach ($arfupdatedfields as $arfkey => $field_id) {

                $new_value_sep = '';

                if(!empty($field_id)){

                    $update_field_ids[] = $field_id;

                    $field_type = isset($field_types[$arfkey]) ? $field_types[$arfkey] : '';

                    if( $field_type == '' ){
                        $getFieldType = $wpdb->get_row( $wpdb->prepare( "SELECT type FROM `".$MdlDb->fields."` WHERE id=%d", $field_id) );
                        $field_type = $getFieldType->type;
                    }

                    $new_value = isset($arfupdatedfieldvalues[$arfkey]) ? $arfupdatedfieldvalues[$arfkey] : '';
                    $ocnt = $counter = isset( $counterData[$arfkey] ) ? $counterData[$arfkey] : '';
                    $draw_col_value = $new_value;
                    $is_multiselect_update = 0;

                    $entry_data = $wpdb->get_row($wpdb->prepare("SELECT id, entry_value FROM " . $MdlDb->entry_metas . " WHERE entry_id = %d AND field_id = %d", array($entry_id, $field_id)));
                   
                    $new_value_array = explode( '[ARF_JOIN]', $entry_data->entry_value );

                    $cnt = 0;
                    $new_final_array = array();
                    foreach( $new_value_array as $key => $val ){

                        if( !in_array( $field_type, array( 'checkbox', 'radio', 'select', 'arf_multiselect', 'arf_autocomplete' ) ) ){
                            if( count($counterData) > 1 ){
                                if( $cnt==$counter ){
                                    $new_final_array[$cnt] = $new_value;
                                } else {
                                    $new_final_array[$cnt] = $val;
                                }
                            } else if( count($counterData) == 1 ){
                                $new_final_array[$counter] = $new_value;
                            }
                        } else {
                            if( preg_match( '/\!\|\!/', $val ) ){
                                $nval = explode( '!|!', $val );
                                $ncnt = $nval[1];
                                if( $ncnt == $ocnt ){
                                    if( is_array( $new_value) ){
                                        $new_final_array[$ocnt] = maybe_serialize( $new_value ).'!|!'.$ocnt;
                                    } else {
                                        $new_value = arf_json_decode( stripslashes_deep( $new_value ), true );
                                        if( json_last_error() != JSON_ERROR_NONE ){
                                            $new_final_array[$ocnt] = maybe_serialize( (array) $new_value ).'!|!'.$ocnt;
                                        } else {
                                            $new_final_array[$ocnt] = maybe_serialize( $new_value ).'!|!'.$ocnt;
                                        }
                                    }
                                } else {
                                    $new_final_array[$ncnt] = $val;
                                }

                            } else {
                                if( count($counterData) > 1 ){
                                    if( $cnt == $counter ){
                                        if( is_serialized( $new_value ) ){
                                            $new_final_array[$cnt] = $new_value.'!|!'.$cnt;
                                        } else {
                                            $temp_json = json_decode( stripslashes($new_value), true );
                                            if( json_last_error() == JSON_ERROR_NONE ){
                                                $new_final_array[$cnt] = maybe_serialize( $temp_json ) .'!|!'.$cnt;
                                            } else {
                                                $new_final_array[$cnt] = $new_value;
                                            }
                                        }
                                    } else {
                                        $new_final_array[$cnt] = $val;
                                    }
                                } else if( count( $counterData ) == 1 ){
                                    if( is_serialized( $new_value ) ){
                                        $new_final_array[$counter] = $new_value.'!|!'.$counter;
                                    } else {
                                        $temp_json = json_decode( stripslashes($new_value), true );
                                        if( json_last_error() == JSON_ERROR_NONE ){
                                            $new_final_array[$counter] = maybe_serialize( $temp_json ) .'!|!'.$counter;
                                        } else {
                                            $new_final_array[$counter] = $new_value;
                                        }
                                    }
                                }
                            }
                        }
                        $cnt++;
                    }

                   
                    if( count( $new_final_array ) < $totalRows ){
                        $txk = 0;
                        $temp_arr = array();
                        for( $x = 0; $x < $totalRows; $x++ ){
                            if( isset( $new_final_array[$x] ) ){
                                if( preg_match('/\!\|\!/',$new_final_array[$x]) ){
                                    $dataExp = explode('!|!',$new_final_array[$x]);
                                    $txk = $dataExp[1];
                                    $temp_arr[$txk] = $new_final_array[$x];
                                } else {
                                    $temp_arr[$txk] = $new_final_array[$x];
                                }
                            } else {
                                if( isset( $new_value_array[$txk] ) ){
                                    $temp_arr[$txk] = $new_value_array[$txk];
                                } else {
                                    $temp_arr[$txk] = '';
                                }
                            }
                            $txk++;
                        }

                        $new_final_array = $temp_arr;
                    }
                    

                    $final_entry_data = implode( '[ARF_JOIN]', $new_final_array );
                    
                    $draw_col_value = $final_entry_data;


                    if( isset( $entry_data ) && !empty( $entry_data->id ) ){
                        $rec = $wpdb->update( $MdlDb->entry_metas, array( 'entry_value' => $final_entry_data), array('entry_id' => $entry_id, 'field_id' => $field_id) );
                        if($rec){
                            $arfdraw_cols[] = array('field' => $field_id, 'col' => isset($arf_columns[$field_id]) ? $arf_columns[$field_id] : '', 'val' => $draw_col_value);
                        }
                        if ($new_value_sep) {
                            $rec_sep = $wpdb->update($MdlDb->entry_metas, array('entry_value' => $new_value_sep), array('entry_id' => $entry_id, 'field_id' => "-" . $field_id));
                        }
                    } else {
                        $arf_meta_insert = array(
                            'entry_value' => $final_entry_data,
                            'field_id' => arf_sanitize_value($field_id, 'integer'),
                            'entry_id' => arf_sanitize_value($entry_id, 'integer'),
                            'created_date' => current_time('mysql'),
                        );
                        
                        $rec = $wpdb->insert($MdlDb->entry_metas, $arf_meta_insert, array('%s', '%d', '%d', '%s'));
                        if($rec){
                            $arfdraw_cols[] = array('field' => $field_id, 'col' => isset($arf_columns[$field_id]) ? $arf_columns[$field_id] : '', 'val' => $draw_col_value);
                        }
                        if ($new_value_sep) {
                            $arf_meta_insert_wiht_sep = array(
                                'entry_value' => $new_value_sep,
                                'field_id' => "-" . $field_id,
                                'entry_id' => arf_sanitize_value($entry_id, 'integer'),
                                'created_date' => current_time('mysql'),
                            );
                            $wpdb->insert($MdlDb->entry_metas, $arf_meta_insert_wiht_sep, array('%s', '%d', '%d', '%s'));
                        }
                    }
                }
            }

            $update_field_ids = array_unique( $update_field_ids );

            if(count($arfdraw_cols) > 0){
                $arf_return = array('status' => 'success', 'message' => addslashes(esc_html__('Record is updated successfully.','ARForms')), 'updatecols' => $arfdraw_cols, 'edit_select_options' => $edit_select_options);
            }
        }
        echo json_encode($arf_return);
        die();
    }

    function arf_edit_entry_values() {

        global $wpdb, $arffield, $MdlDb, $armainhelper, $arfieldhelper, $arfform;

        $arf_return = array('status' => 'error', 'message' => addslashes(esc_html__('Record could not be updated','ARForms')));

        $arfform_id =  (isset($_POST['arf_form']) && !empty($_POST['arf_form'])) ? $_POST['arf_form'] : '';
        $entry_id = (isset($_POST['entry_id']) && !empty($_POST['entry_id'])) ? $_POST['entry_id'] : '';
        $arfupdatedfields = (isset($_POST['updatedfields']) && !empty($_POST['updatedfields'])) ? explode("||", $_POST['updatedfields']) : array();
        $arfupdatedfieldvalues = (isset($_POST['newvalues']) && !empty($_POST['newvalues'])) ? explode("||", $_POST['newvalues']) : array();
        $field_types = (isset($_POST['field_types']) && !empty($_POST['field_types'])) ? explode("||", $_POST['field_types']) : array();

        $form_cols = array();
        $arfdraw_cols = array();
        $edit_select_options = array();

        if (!empty($entry_id) && count($arfupdatedfields) > 0) {

            if($arfform_id != '' && $arfform_id != '-1') {
                $form_cols = $wpdb->get_results( $wpdb->prepare("SELECT `id` FROM `".$MdlDb->fields."` WHERE form_id = %d AND type NOT IN ('divider', 'section', 'arf_repeater', 'captcha', 'break', 'html', 'imagecontrol')", $arfform_id) );
                $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $arfform_id);
            }
            $arf_columns = array('0' => '', '1' => 'id');

            if (count($form_cols) > 0) {

                $get_form_options = $wpdb->get_row( $wpdb->prepare("SELECT `options` FROM `".$MdlDb->forms."` WHERE `id` = %d AND `is_template` != %d AND `status` = %s", $arfform_id, 1,'published') );
                $form_options = maybe_unserialize( $get_form_options->options );

                $arffieldorder = array();
                if(isset($form_options['arf_field_order']) && $form_options['arf_field_order'] != ''){
                    $arffieldorder = json_decode($form_options['arf_field_order'], true);
                    asort($arffieldorder);
                }

                if(count($arffieldorder) > 0){
                    $form_cols_temp = array();
                    foreach ($arffieldorder as $fieldkey => $fieldorder) {
                        foreach ($form_cols as $frmoptkey => $frmoptarr) {
                            if($frmoptarr->id == $fieldkey){
                                $form_cols_temp[] = $frmoptarr;
                                unset($form_cols[$frmoptkey]);
                            }
                        }
                    }

                    if(count($form_cols_temp) > 0) {
                        if(count($form_cols) > 0) {
                            $form_cols_other = $form_cols;
                            $form_cols = array_merge($form_cols_temp,$form_cols_other);
                        } else {
                            $form_cols = $form_cols_temp;
                        }
                    }
                }

                for ($col_i = 2; $col_i <= count($form_cols) + 1; $col_i++) {
                    $col_j = $col_i - 2;
                    $arf_columns[$form_cols[$col_j]->id] = $col_i;
                }

            }



            foreach ($arfupdatedfields as $arfkey => $field_id) {

                $new_value_sep = '';

                if(!empty($field_id)){

                    $field_type = isset($field_types[$arfkey]) ? $field_types[$arfkey] : '';

                    if( $field_type == '' ){
                        $getFieldType = $wpdb->get_row( $wpdb->prepare( "SELECT type FROM `".$MdlDb->fields."` WHERE id=%d", $field_id) );
                        $field_type = $getFieldType->type;
                    }

                    $new_value = isset($arfupdatedfieldvalues[$arfkey]) ? $arfupdatedfieldvalues[$arfkey] : '';
                    $draw_col_value = $new_value;
                    $is_multiselect_update = 0;
                    if ($field_type == 'checkbox' || $field_type == 'select' || $field_type == 'radio' || $field_type == 'arf_multiselect' || $field_type == 'arf_autocomplete' ) {
                        $is_multiselect_update = 1;
                        
                        $field = $arffield->getOne($field_id);
                        
                        $as_new_value_sep = array();

                        if ($field_type == 'checkbox' || $field_type == 'arf_multiselect') {
                            $op_value = explode(',', $new_value);
                            $new_value = maybe_serialize($op_value);
                            $draw_col_value = implode(', ', $op_value);
                            $edit_select_options[$field_id] = json_encode($op_value);

                        } else {
                            $edit_select_options[$field_id] = json_encode($new_value);
                        }

                        if( isset($field->field_options['separate_value']) && $field->field_options['separate_value'] == 1 ) {
                            if(isset($field->field_options['options']) && $field->field_options['options'] != ''){
                                $arf_select_values = array_column($field->field_options['options'], 'value');
                                $arf_select_label = array_column($field->field_options['options'], 'label');
                                if (($field_type == 'checkbox' || $field_type == 'arf_multiselect' ) && isset($op_value)  ) {
                                    $op_label = array();
                                    foreach ($op_value as $key => $value) {
                                        if(in_array($value, $arf_select_values)){
                                            $op_label[] = $arf_select_label[array_search($value, $arf_select_values)] . '(' . $value . ')';
                                        }
                                    }
                                    $draw_col_value = implode(', ', $op_label);
                                } else {
                                    $draw_col_value = $arf_select_label[array_search($new_value, $arf_select_values)];
                                }
                            }
                        }

                        if (!empty($field->options)) {
                            $arf_field_options = $field->options;

                            if(!is_array($arf_field_options)){
                                $arf_field_options = json_decode($arf_field_options, true);
                            }
                            foreach ($arf_field_options as $key => $option) {
                                if( isset($field->field_options['separate_value']) && $field->field_options['separate_value'] == 1 ) {
                                    if ($field_type == 'checkbox' || $field_type == 'arf_multiselect') {
                                        if (in_array($option['value'], $op_value)) {
                                            $as_new_value_sep[] = $option;
                                        }
                                    } else {
                                        if ($option['value'] == $new_value) {
                                            $as_new_value_sep = $option;
                                        }
                                    }
                                }else{
                                    if ($field_type == 'checkbox' || $field_type == 'arf_multiselect') {
                                        if (in_array($option, $op_value)) {
                                            $as_new_value_sep[] = $option;
                                        }
                                    } else {
                                        if ($option == $new_value) {
                                            $as_new_value_sep = $option;
                                        }
                                    }
                                }
                            }

                        }

                        if (!empty($as_new_value_sep)) {
                            $new_value_sep = maybe_serialize($as_new_value_sep);
                        }
                    } else if( $field_type == 'date' ){
                        $date_value = $new_value;

                            $getFopt = $wpdb->get_row( $wpdb->prepare( "SELECT field_options FROM `". $MdlDb->fields ."` WHERE id = %d", $field_id ) );
                            
                            $field_opts = json_decode( $getFopt->field_options, true);

                            $show_time_calender = $field_opts['show_time_calendar'];
                            $clock = isset($field_opts['clock'])?$field_opts['clock']:'';
                            $steps = isset($field_opts['steps'])?$field_opts['steps']:'';
                            $locale = isset($field_opts['locale'])?$field_opts['locale']:'en';

                        if( '' != $new_value ){
                            $date_value = trim($date_value, ' ');

                            $date_value = str_replace(' /','/',$date_value);
                            $date_value = str_replace('/ ','/',$date_value);
                            $date_value = str_replace('/','-',$date_value);
                            if( true == $show_time_calender ){
                                $date_value = date('Y-m-d H:i', strtotime($date_value));
                            } else {
                                $date_value = date('Y-m-d', strtotime($date_value));
                            }
                            
                            $new_value = $date_value;
                        }

                        $draw_col_value = $arfieldhelper->get_date_entry( $date_value, $arfform_id, $show_time_calender, $clock, $locale);
                    } else if( $field_type == 'matrix' ){
                        $fid_data = explode('~|~', $field_id );
                        $field_id = $fid_data[0];
                        $fcounter = $fid_data[1];

                        $field = $arffield->getOne($field_id);

                        $get_entry_val = $arfform->arf_select_db_data(true, '', $MdlDb->entry_metas, 'entry_value', 'WHERE entry_id = %d AND field_id = %d', array( $entry_id, $field_id ), '', '', '', false, true );

                        if( !empty( $get_entry_val ) ){

                            $db_entry_val = maybe_unserialize( $get_entry_val->entry_value );
                            
                            $db_entry_val[ $fcounter ] = $new_value;

                            $new_value = maybe_serialize( $db_entry_val );
                        
                        } else {
                            $db_entry_val[ $fcounter ] = $new_value;
                            $new_value = maybe_serialize( $db_entry_val );
                        }
                        
                    }
                    

                    $entry_data = $wpdb->get_row($wpdb->prepare("SELECT id FROM " . $MdlDb->entry_metas . " WHERE entry_id = %d AND field_id = %d", array($entry_id, $field_id)));

                    if (isset($entry_data) && !empty($entry_data->id)) {
                        $rec = $wpdb->update($MdlDb->entry_metas, array('entry_value' => $new_value), array('entry_id' => $entry_id, 'field_id' => $field_id));

                        if($rec){
                            $arfdraw_cols[] = array('field' => $field_id, 'col' => isset($arf_columns[$field_id]) ? $arf_columns[$field_id] : '', 'val' => $draw_col_value);
                        }
                        if ($new_value_sep) {
                            $rec_sep = $wpdb->update($MdlDb->entry_metas, array('entry_value' => $new_value_sep), array('entry_id' => $entry_id, 'field_id' => "-" . $field_id));
                        }
                    } else {

                        $arf_meta_insert = array(
                            'entry_value' => $new_value,
                            'field_id' => arf_sanitize_value($field_id, 'integer'),
                            'entry_id' => arf_sanitize_value($entry_id, 'integer'),
                            'created_date' => current_time('mysql'),
                        );
                        $rec = $wpdb->insert($MdlDb->entry_metas, $arf_meta_insert, array('%s', '%d', '%d', '%s'));
                        if($rec){
                            $arfdraw_cols[] = array('field' => $field_id, 'col' => isset($arf_columns[$field_id]) ? $arf_columns[$field_id] : '', 'val' => $draw_col_value);
                        }

                        if ($new_value_sep) {
                            $arf_meta_insert_wiht_sep = array(
                                'entry_value' => $new_value_sep,
                                'field_id' => "-" . $field_id,
                                'entry_id' => arf_sanitize_value($entry_id, 'integer'),
                                'created_date' => current_time('mysql'),
                            );
                            $wpdb->insert($MdlDb->entry_metas, $arf_meta_insert_wiht_sep, array('%s', '%d', '%d', '%s'));
                        }
                    }
                }
            }

            if(count($arfdraw_cols) > 0){
                $arf_return = array('status' => 'success', 'message' => addslashes(esc_html__('Record is updated successfully.','ARForms')), 'updatecols' => $arfdraw_cols, 'edit_select_options' => $edit_select_options);
            }

        }
        echo json_encode($arf_return);
        die();
    }

    function load_footer_script() {

        global $arfversion;
        $path = $_SERVER['REQUEST_URI'];
        $file_path = basename($path);
        $css_array = array('bootstrap');
        $url = ARFURL . '/bootstrap/css/';
        foreach ($css_array as $cssfile) {
            echo "<link rel='stylesheet' type='text/css' href='" . $url . $cssfile . ".css?ver=" . $arfversion . "' />";
        }

        echo "<script type='text/javascript' data-cfasync='false' src='" . ARFURL . "/js/arf_conditional_logic.js?ver=" . $arfversion . "'></script>";

        if (!strstr($file_path, "post.php")) {


            global $arfrtloaded, $arfdatepickerloaded, $arftimepickerloaded;

            global $arfhiddenfields, $arfforms_loaded, $arfcalcfields, $arfrules, $arfinputmasks;

            if (empty($arfforms_loaded))
                return;


            foreach ($arfforms_loaded as $form) {


                if (!is_object($form))
                    continue;
            }

            $scripts = array('arforms','wp-hooks');


            if (!empty($arfdatepickerloaded)) {
                $scripts[] = 'bootstrap-locale-js';
                $scripts[] = 'bootstrap-datepicker';
            }


            if (!empty($arftimepickerloaded)) {

                $scripts[] = 'bootstrap-locale-js';
                $scripts[] = 'bootstrap-datepicker';
            }



            $arfinputmasks = apply_filters('arfinputmasks', $arfinputmasks, $arfforms_loaded);




            $scripts[] = 'jquery-maskedinput';


            if (!empty($scripts)) {


                global $wp_scripts;


                $wp_scripts->do_items($scripts);
            }


            unset($scripts);

            include_once(VIEWS_PATH . '/common.php');

            ?>
            <script type="text/javascript" data-cfasync="false">
                function arf_load_colorpicker() {
                    if (typeof jQuery().colpick == 'function')
                    {
                        jQuery("form.arfshowmainform").each(function () {
                            var color_data_id = jQuery(this).attr('data-id');
                            var color_curr_form = jQuery("form.arfshowmainform[data-id='" + color_data_id + "']");
                            color_curr_form.find('.arf_colorpicker').colpick({
                                layout: 'hex',
                                submit: 1,
                                color: 'ffffff',
                                onBeforeShow: function () {
                                    var fid = jQuery(this).attr('id');
                                    var fid = fid.replace('arfcolorpicker_', '');
                                    var color = color_curr_form.find('#field_' + fid).val();
                                    var new_color = color.replace('#', '');
                                    if (new_color)
                                        jQuery(this).colpickSetColor(new_color);
                                },
                                onChange: function (hsb, hex, rgb, el, bySetColor) {
                                    var field_key = jQuery(el).attr('id');
                                    field_key = field_key.replace('arfcolorpicker_', '');
                                    color_curr_form.find('#field_' + field_key).val('#' + hex).trigger('change');
                                    jQuery(el).find('.arfcolorvalue').text('#' + hex);
                                    jQuery(el).find('.arfcolorvalue').css('background', '#' + hex);
                                    var arffontcolor = HextoHsl(hex) > 0.5 ? '#000000' : '#ffffff';
                                    jQuery(el).find('.arfcolorvalue').css('color', arffontcolor);
                                },
                                onSubmit: function () {
                                    color_curr_form.find('.arf_colorpicker').colpickHide();
                                }
                            });
                        });
                    }
                    jQuery('.colpick_hex_field').find('input').bind('paste', function (event) {
                        event.preventDefault();
                        var clipboardData = event.originalEvent.clipboardData.getData('text');
                        clipboardData = clipboardData.replace('#', '');
                        jQuery(this).val(clipboardData).trigger('change');
                    });
                }

                function arf_load_simple_colpicker() {
                    if (typeof jQuery().simpleColorPicker == 'function' )
                    {
                        jQuery("form.arfshowmainform").each(function () {
                            var scolor_data_id = jQuery(this).attr('data-id');
                            var scolor_curr_form = jQuery("form.arfshowmainform[data-id='" + scolor_data_id + "']");
                            scolor_curr_form.find('.arf_basic_colorpicker').simpleColorPicker({
                                onChangeColor: function (color) {
                                    var field_key = jQuery(this).attr('id');
                                    field_key = field_key.replace('arfcolorpicker_', '');
                                    scolor_curr_form.find('#field_' + field_key).val(color).trigger('change');
                                    jQuery(this).find('.arfcolorvalue').text(color);
                                    jQuery(this).find('.arfcolorvalue').css('background', color);
                                    var hex = color.replace('#', '');
                                    var arffontcolor = HextoHsl(hex) > 0.5 ? '#000000' : '#ffffff';

                                    if (hex == "ffff00")
                                    {
                                        arffontcolor = "#000000";
                                    }
                                    jQuery(this).find('.arfcolorvalue').css('color', arffontcolor);
                                }
                            });
                        });
                    }
                    jQuery('.colpick_hex_field').find('input').bind('paste', function (event) {
                        event.preventDefault();
                        var clipboardData = event.originalEvent.clipboardData.getData('text');
                        clipboardData = clipboardData.replace('#', '');
                        jQuery(this).val(clipboardData).trigger('change');
                    });
                }
            </script>
            <?php
            $form_id = $form->id;
            global $arfsettings;
            if (!isset($arfsettings)) {
                $arfsettings_new = get_option('arf_options');
            } else {
                $arfsettings_new = $arfsettings;
            }
            ?>
            <script type="text/javascript" data-cfasync="false">
            <?php
            
            if ((isset($arfsettings_new->arfmainformloadjscss) && $arfsettings_new->arfmainformloadjscss == 1)) {
                ?> arf_load_colorpicker(); <?php
            }

            if ((isset($arfsettings_new->arfmainformloadjscss) && $arfsettings_new->arfmainformloadjscss == 1)) {
                ?> arf_load_simple_colpicker(); <?php
            }
            ?>
            </script>
            <?php
        }
    }
    
    function arf_delete_single_entry_function(){
        global $db_record;
        
        $entry_id = isset($_REQUEST['entry_id']) ? $_REQUEST['entry_id'] : 0;
        $form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : '';

        if( $entry_id < 1 ){
            echo json_encode(array('error'=>true,'message'=>addslashes(esc_html__('Please select one or more record.','ARForms'))));
            die();
        }
        
        $del_res = $db_record->destroy($entry_id);
        
        if( $del_res ){

            $total_records = '';
            if($form_id != ''){
                $total_records = $db_record->getRecordCount( (int)$form_id );
            }
            echo json_encode(array('error' => false, 'message' => addslashes(esc_html__('Record is deleted successfully.','ARForms')), 'arftotrec' => $total_records));

        } else {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Record could not be deleted','ARForms'))));
        }

        die();
    }

    function arf_delete_single_incomplete_entry_function(){
        global $wpdb, $MdlDb,$db_record;

        $entry_id = isset( $_REQUEST['entry_id'] ) ? $_REQUEST['entry_id'] : 0;
        $form_id = isset( $_REQUEST['form_id'] ) ? $_REQUEST['form_id'] : '';

        if( $entry_id < 1 ){
            echo json_encode(
                array(
                    'error' => true,
                    'message' => addslashes( esc_html__('Please select one or more record', 'ARForms') )
                )
            );
        }

        do_action( 'arf_before_remove_incomplete_entry', $entry_id, $form_id );

        $wpdb->query($wpdb->prepare('DELETE FROM ' . $MdlDb->incomplete_entry_metas . ' WHERE entry_id=%d', $entry_id));

        $result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $MdlDb->incomplete_entries . ' WHERE id=%d', $entry_id));

        do_action( 'arf_after_destroy_incomplete_entry', $entry_id, $form_id );

        $result = apply_filters( 'arf_after_destroy_incompelete_entry', $result );

        if( $result ){

            $total_records = '';
            if($form_id != ''){
                $total_records = $db_record->getRecordCount( (int)$form_id, true );
            }
            echo json_encode(array('error' => false, 'message' => addslashes(esc_html__('Record is deleted successfully.','ARForms')), 'arftotrec' => $total_records));

        } else {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Record could not be deleted','ARForms'))));
        }

        die;

    }

    function ajax_check_spam_filter() {
        $formRandomKey = isset($_POST['form_random_key']) ? $_POST['form_random_key'] : '';
        $validate = TRUE;
        $is_check_spam = true;

        if ($is_check_spam) {
            $validate = apply_filters('is_to_validate_spam_filter', $validate, $formRandomKey);
        }
        $response = array();
        if (!$validate) {
            $response['error'] = true;
            $response['message'] = addslashes(esc_html__('Spam Detected', 'ARForms'));
        } else {
            $response['error'] = false;
        }
        $response = apply_filters( 'arf_reset_built_in_captcha', $response, $_POST );
        echo json_encode($response);
        die();
    }

    function arf_save_incomplete_form_data(){
        global $wpdb, $MdlDb, $armainhelper;

        $token = isset( $_REQUEST['token'] ) ? $_REQUEST['token'] : '';
        if( '' == $token ){
            return;
        }

        $field_id = isset( $_REQUEST['field_id'] ) ? $_REQUEST['field_id'] : '';
        $form_id = isset( $_REQUEST['form_id'] ) ? $_REQUEST['form_id'] : '';
        $value = isset( $_REQUEST['value'] ) ? $_REQUEST['value'] : '';


        $ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
        
        $referrerinfo = $armainhelper->get_referer_info();

        $description = maybe_serialize(
            array(
                'browser'           => $_SERVER['HTTP_USER_AGENT'],
                'referrer'          => $referrerinfo,
                'http_referrer'     => isset( $_REQUEST["arf_http_referrer_url"] ) ? $_REQUEST["arf_http_referrer_url"] : '',
                'page_url'          => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : ''
            )
        );

        $browser_info = $_SERVER['HTTP_USER_AGENT'];

        $country_name = arf_get_country_from_ip($ip_address);

        $created_date = date('Y-m-d H:i:s');

        global $user_ID;
        if ($user_ID){
            $user_id = $user_ID;
        } else {
            $user_id = 0;
        }

        $rowData = $wpdb->get_results( $wpdb->prepare("SELECT id FROM `" . $MdlDb->incomplete_entries . "` WHERE token = %s AND form_id = %d", $token, $form_id) );
        if( count( $rowData ) == 0 ){
            $new_values = array(
                'form_id' => $form_id,
                'token' => $token,
                'description' => $description,
                'ip_address' => $ip_address,
                'browser_info' => $browser_info,
                'country' => $country_name,
                'user_id' => $user_id,
                'created_date' => $created_date
            );

            do_action( 'arf_before_create_incomplete_entry', $form_id, $new_values );

            $wpdb->insert( $MdlDb->incomplete_entries, $new_values );

            $entry_id = $wpdb->insert_id;
        } else {
            $entry_id = $rowData[0]->id;
        }

        do_action( 'arfbeforecreate_incomplete_entry_data', $form_id, $entry_id, $field_id, $value );
        $this->arf_add_incomplete_entry_meta( $entry_id, $field_id, $value );
        do_action( 'arfaftercreate_incomplete_entry_data', $form_id, $entry_id, $field_id, $value );
        die;

    }

    function arf_remove_incomplete_entry_data( $entry_id, $form_id ){

        global $wpdb, $MdlDb, $db_record;

        if( $entry_id == '' || $form_id == '' ){
            return;
        }

        $entry = $db_record->getOne($entry_id);

        if( $entry->form_id < 0 ){
            $entry->form_id = abs( $entry->form_id );
        }

        $form_options = $wpdb->get_row( $wpdb->prepare( "SELECT options FROM `" . $MdlDb->forms . "` WHERE id = %d ", (int) $entry->form_id ) );
       
        $form_opts = maybe_unserialize( $form_options->options );

        if( isset( $form_opts['arf_form_save_database'] ) && 1 == $form_opts['arf_form_save_database'] && isset( $_POST['arf_incomplete_form_token'] ) && '' != $_POST['arf_incomplete_form_token'] ){
            $token = $_POST['arf_incomplete_form_token'];

            $entry_data = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM `". $MdlDb->incomplete_entries."` WHERE token = %s", $token ) );
            if( !isset( $entry_data->id ) ){
                return;
            }
            $entry_id = $entry_data->id;

            $wpdb->delete(
                $MdlDb->incomplete_entries,
                array(
                    'id' => $entry_id
                ),
                array( '%d' )
            );

            $wpdb->delete(
                $MdlDb->incomplete_entry_metas,
                array(
                    'entry_id' => $entry_id
                ),
                array( '%d' )
            );
        }     

    }

    function arf_add_incomplete_entry_meta( $entry_id, $field_id, $field_value ){
        global $wpdb, $MdlDb, $arfrecordmeta;



        $fieldObj = $wpdb->get_row( $wpdb->prepare( "SELECT type,field_options,options FROM `" . $MdlDb->fields ."` WHERE id = %d", $field_id ) );
        $field_opt = json_decode( $fieldObj->field_options );

        $inside_repeater = false;
        if( isset( $field_opt->type ) && 'arf_repeater' == $field_opt->type ){

            if( preg_match( '/^(\d+)\[(\d+)\]\[(\d+)\]+$/', $field_id) ){
                
                $new_field_id = preg_replace( '/^(\d+)\[(\d+)\]\[(\d+)\]+$/', '$2', $field_id );
                $counter_data = preg_replace( '/^(\d+)\[(\d+)\]\[(\d+)\]+$/', '$3', $field_id );
                $field_id = $new_field_id;
                $fieldObj = $wpdb->get_row( $wpdb->prepare( "SELECT type,field_options,options FROM `" . $MdlDb->fields ."` WHERE id = %d", $field_id ) );
                $field_opt = json_decode( $fieldObj->field_options );
            }

            $inside_repeater = true;
        }

        $is_matrix = false;
        if( preg_match( '/^(\d+)\[\d+\]/', $field_id ) ){
            $field_id_n = explode( '[', $field_id );
            $field_key = str_replace(']','', $field_id_n[1] );
            $field_id = $field_id_n[0];
            $is_matrix = true;
        }

        $entry_meta_row = $wpdb->get_row( $wpdb->prepare( "SELECT id,entry_value FROM `" . $MdlDb->incomplete_entry_metas . "` WHERE entry_id = %d AND field_id = %d", $entry_id, $field_id ) );
        
        if( isset( $field_opt->type2 ) && 'ccfield' == $field_opt->type2 ){
            return false;
        }

        $serialized_val = '';
        if( 'select' == $fieldObj->type || 'radio' == $fieldObj->type || 'checkbox' == $fieldObj->type || 'arf_multiselect' == $fieldObj->type || 'arf_autocomplete' == $fieldObj->type){
            $options_arr = json_decode($fieldObj->options,true);

            $field_options = json_decode($fieldObj->field_options,true);
            
            if( isset($field_options['separate_value']) and $field_options['separate_value'] == 1 ){
                $new_entry_value    = array();

                $entry_value    = maybe_unserialize( $field_value  );
                if( !is_array($entry_value) ){
                    $entry_value = explode( ',', $entry_value );
                }
                if( 'checkbox' == $fieldObj->type || 'arf_multiselect' == $fieldObj->type ){
                    if( is_array( $entry_value ) ){
                        foreach( $entry_value as $k => $fvalue ){
                            $new_entry_value[] = $arfrecordmeta->find_value_in_options_with_separate_value($fvalue, $options_arr,$k);
                        }
                    } else {
                        $new_entry_value[] = $arfrecordmeta->find_value_in_options_with_separate_value( $field_value, $options_arr, $k );
                    }
                } else {
                    $new_entry_value = $arfrecordmeta->find_value_in_options( $field_value, $options_arr );
                }

                $serialized_val    = maybe_serialize( $new_entry_value );
            }
        }

        if( $inside_repeater && isset( $entry_meta_row->entry_value ) ){
            $final_val = '';
            $entry_val = explode( '[ARF_JOIN]', $entry_meta_row->entry_value );
            if( $counter_data > 0 ){
                $entry_val[$counter_data] = $field_value;
                $field_value = implode( '[ARF_JOIN]', $entry_val );
            }
        }


        if( isset($entry_meta_row->id) && $entry_meta_row->id != '' ){

            if( $is_matrix == true && 'matrix' == $fieldObj->type ){
                $saved_value = maybe_unserialize( $entry_meta_row->entry_value );
                $field_value_n = array();
                $rows = $field_opt->rows;
                foreach( $rows as $k => $val ){
                    if( $k == $field_key ){
                        $field_value_n[$k] = $field_value;
                    } else {
                        if( !empty( $saved_value[$k] ) ){
                            $field_value_n[$k] = $saved_value[$k];
                        } else {
                            $field_value_n[$k] = '';
                        }
                    }
                }
                $field_value = maybe_serialize( $field_value_n );
            }

            $wpdb->update(
                $MdlDb->incomplete_entry_metas,
                array(
                    'entry_value' => $field_value
                ),
                array(
                    'id' => $entry_meta_row->id,
                    'entry_id' => $entry_id,
                    'field_id' => $field_id,
                ),
                array(
                    '%s','%s'
                )
            );
            if( '' != $serialized_val ){
                $wpdb->update(
                    $MdlDb->incomplete_entry_metas,
                    array(
                        'entry_value' => $serialized_val
                    ),
                    array(
                        'entry_id' => $entry_id,
                        'field_id' => '-'.$field_id,
                    ),
                    array(
                        '%s','%s'
                    )
                );
            }
        } else {

            if( $is_matrix == true && 'matrix' == $fieldObj->type ){
                $field_value_n = array();
                $rows = $field_opt->rows;
                foreach( $rows as $k => $val ){
                    if( $k == $field_key ){
                        $field_value_n[$k] = $field_value;
                    } else {
                        $field_value_n[$k] = '';
                    }
                }
                $field_value = maybe_serialize( $field_value_n );
            }
            $wpdb->insert(
                $MdlDb->incomplete_entry_metas,
                array(
                    'entry_id' => $entry_id,
                    'field_id' => $field_id,
                    'entry_value' => $field_value,
                    'created_date' => date('Y-m-d H:i:s')
                ),
                array(
                    '%d', '%d', '%s', '%s'
                )
            );
            if( '' != $serialized_val ){
                $wpdb->insert(
                    $MdlDb->incomplete_entry_metas,
                    array(
                        'entry_id' => $entry_id,
                        'field_id' => '-'.$field_id,
                        'entry_value' => $serialized_val,
                        'created_date' => date('Y-m-d H:i:s')
                    ),
                    array(
                        '%d', '%d', '%s', '%s'
                    )
                );
            }
        }

    }

    function arf_move_incomplete_entry_data( $entry_id = '', $form_id = '', $is_bulk = false ){
        global $MdlDb, $wpdb, $armainhelper;

        if( ! $is_bulk ){
            $entry_id = isset( $_POST['id'] ) ? $_POST['id'] : '';
            $form_id = isset( $_POST['form_id'] ) ? $_POST['form_id'] : '';
        }
        $return = array();
        if( '' == $entry_id || '' == $form_id ){
            $return = json_encode(
                array(
                    'error' => true,
                    'message' => esc_html__('There is something wrong while moving the entry. Please try again', 'ARForms')
                )
            );
        } else {
            $incomplete_entries = $MdlDb->incomplete_entries;
            $incomplete_entry_values = $MdlDb->incomplete_entry_metas;

            $form_entries = $MdlDb->entries;
            $form_entry_values = $MdlDb->entry_metas;

            $incomplete_entry_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `". $incomplete_entries . "` WHERE id = %d and form_id = %d", $entry_id, $form_id ) );

            $incomplete_entry_id = $incomplete_entry_data->id;

            $entry_key = $armainhelper->get_unique_key('', $MdlDb->entries, 'entry_key');

            $created_date = date('Y-m-d H:i:s');

            $is_incomplete_entry = '1';

            $description = $incomplete_entry_data->description;
            $ip_address = $incomplete_entry_data->ip_address;
            $country = $incomplete_entry_data->country;
            $browser_info = $incomplete_entry_data->browser_info;
            
            $user_id = $incomplete_entry_data->user_id;

            $inc_entry_values = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . $incomplete_entry_values . "` WHERE entry_id = %d ", $incomplete_entry_id ) );

            $wpdb->insert(
                $form_entries,
                array(
                    'entry_key' => $entry_key,
                    'description' => $description,
                    'ip_address' => $ip_address,
                    'country' => $country,
                    'browser_info' => $browser_info,
                    'form_id' => $form_id,
                    'created_date' => $created_date,
                    'is_incomplete_entry' => $is_incomplete_entry
                )
            );

            $new_entry_id = $wpdb->insert_id;

            foreach( $inc_entry_values as $inck => $incv ){

                $field_id = $incv->field_id;
                $entry_value = $incv->entry_value;
                $created_date = date('Y-m-d H:i:s');

                $wpdb->insert(
                    $form_entry_values,
                    array(
                        'entry_id' => $new_entry_id,
                        'field_id' => $field_id,
                        'entry_value' => $entry_value,
                        'created_date' => $created_date
                    )
                );

            }


            $wpdb->delete(
                $incomplete_entries,
                array(
                    'id' => $entry_id
                )
            );

            $wpdb->delete(
                $incomplete_entry_values,
                array(
                    'id' => $entry_id
                )
            );

            $return = json_encode(
                array(
                    'error' => false,

                    'message' => esc_html__( 'Entry moved successfully', 'ARForms' )
                )
            );
        }

        if( !$is_bulk ){
            echo $return;
            die;
        }
        
        return $return;
        
    }

}
?>