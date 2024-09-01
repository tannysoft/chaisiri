<?php

class arsettingcontroller {

    function __construct() {

        global $pagenow;

        add_action('admin_init', array($this, 'admin_init'));

        add_action('admin_menu', array($this, 'menu'), 26);

        add_action('wp_ajax_delete_aweber', array($this, 'delete_aweber'));

        add_action('wp_ajax_refresh_aweber', array($this, 'refresh_aweber'));

        add_action('wp_ajax_clear_form', array($this, 'clear_form'));

        add_action('wp_ajax_verify_autores', array($this, 'verify_autores'));

        add_action('wp_ajax_delete_autores', array($this, 'delete_autores'));

        add_action('wp_ajax_upload_submit_bg', array($this, 'upload_submit_bg'));

        add_action('wp_ajax_upload_submit_hover_bg', array($this, 'upload_submit_hover_bg'));

        add_action('wp_ajax_delete_submit_bg_img', array($this, 'delete_submit_bg_img'));

        add_action('wp_ajax_delete_submit_hover_bg_img', array($this, 'delete_submit_hover_bg_img'));

        add_action('wp_ajax_delete_submit_bg_img_IE89', array($this, 'delete_submit_bg_img_IE89'));

        add_action('wp_ajax_delete_submit_hover_bg_img_IE89', array($this, 'delete_submit_hover_bg_img_IE89'));

        add_action('wp_ajax_upload_form_bg_img', array($this, 'upload_form_bg_img'));

        add_action('wp_ajax_delete_form_bg_img', array($this, 'delete_form_bg_img'));

        add_action('wp_ajax_delete_form_bg_img_IE89', array($this, 'delete_form_bg_img_IE89'));

        add_action('wp_ajax_arfverifypurchasecode', array($this, 'arfreqact'));

        add_action('wp_ajax_arfdeactivatelicense', array($this, 'arfreqlicdeact'));

        add_action('wp_ajax_arf_send_test_mail', array($this, 'arf_send_test_mail'));

        add_action('wp_ajax_arf_install_plugin', array($this, 'arf_install_plugin'));
        add_action('wp_ajax_arf_activate_plugin', array($this, 'arf_activate_plugin'));
        add_action('wp_ajax_arf_deactivate_plugin',array($this, 'arf_deactivate_plugin'));

        add_filter('plugins_api_args', array($this, 'arf_plugin_api_args'), 100000, 2);
        add_filter('plugins_api', array($this, 'arf_plugin_api'), 100000, 3);
        add_filter('plugins_api_result', array($this, 'arf_plugins_api_result'), 100000, 3);
        add_filter('upgrader_package_options', array($this, 'arf_upgrader_package_options'), 100000);
    	add_filter('arf_trim_values',array($this,'arf_array_map'),10,1);

        add_action('activated_plugin',array($this,'arf_is_addon_activated'),10,2 );

        add_action('arf_add_mailchimp_subscriber',array($this,'arf_add_mailchimp_subscriber_callback'),10,5);

        add_action('arf_remove_backup_data', array( $this,'arf_remove_backup_data_callback') );

        add_action( 'wp', array( $this, 'arf_aweber_configuration' ) );


        if( isset( $pagenow ) && ( 'upload.php' == $pagenow || 'admin-ajax.php' == $pagenow ) ){

            add_action( 'pre_get_posts', array( $this, 'arf_refresh_arforms_files_for_media' ) );

            add_filter( 'wp_get_attachment_url', array( $this, 'arf_regenerate_attached_urls' ), 10, 2 );
        }

        add_action( 'arf_addon_page_retrieve_notice', array( $this, 'arf_retrieve_addon_page_notice' ) );
    }

    function arf_retrieve_addon_page_notice(){

        $getNoticeData = get_transient( 'arforms_addon_page_notice' );

        if( false == $getNoticeData ){
            $notice_url = 'https://www.arformsplugin.com/addonlist/arforms_addon_notices.php';

            $getNotice = wp_remote_get(
                $notice_url,
                array(
                    'timeout' => 5000
                )
            );

            if( !is_wp_error( $getNotice ) ){
                $notice_data = wp_remote_retrieve_body( $getNotice );

                set_transient( 'arforms_addon_page_notice', base64_encode( $notice_data ), DAY_IN_SECONDS );

                echo $notice_data;
            }
        } else {
            echo base64_decode( $getNoticeData );
        }

    }

    function arf_regenerate_attached_urls( $url, $attachment_id ){

        global $wpdb, $MdlDb;

        $is_arf_image = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . $MdlDb->entry_metas . "` WHERE entry_value = %d OR entry_value LIKE '%|".$attachment_id."%' OR entry_value LIKE '%".$attachment_id."|%' ", $attachment_id ) );

        if( !empty( $is_arf_image->id ) ){

            $post_meta_data = get_post_meta($attachment_id);

            $attached_file = get_attached_file( $attachment_id );

            $image_array_link = $post_meta_data["_wp_attached_file"][0];

            if( !preg_match( "/wp\-content\/uploads/", $image_array_link ) ){
                $upload_dir = wp_upload_dir();

                $upload_dir_dir = $upload_dir['basedir'];
                $upload_dir_url = $upload_dir['baseurl'];

                if( file_exists( $upload_dir_dir . '/' . $image_array_link ) ){
                    $image_array_link = $upload_dir_url . '/' . $image_array_link;
                }
            }

            if( preg_match( '/http/', $image_array_link ) ){
                $image_array_link = str_replace('thumbs/', '', $image_array_link);
            } else {
                $image_array_link = get_home_url() . "/" . str_replace('thumbs/', '', $image_array_link);
            }

            return $image_array_link;

        } else {
            return $url;
        }
    }

    function arf_refresh_arforms_files_for_media( $query ){

        if( ( isset( $_POST['action'] ) && $_POST['action'] == 'query-attachments' ) || ( isset($_GET['mode'] ) && 'list' == $_GET['mode'] ) ){
            global $arfsettings;
            if( $arfsettings->arfdisplayfilesinwpmedia < 1 ){

                $meta_query = $query->get( 'meta_query' );

                if( !is_array( $meta_query ) ){
                    $meta_query = array();
                }

                $meta_query[] = array(
                    array(
                        'key'  => 'arf_uploaded_file',
                        'value' => 'arforms',
                        'compare' => 'NOT EXISTS'
                    )
                );

                $query->set( 'meta_query', $meta_query );
            }
        }
    }

    function arf_send_test_mail() {
        global $arnotifymodel;

        $reply_to = (isset($_POST['reply_to']) && !empty($_POST['reply_to'])) ? $_POST['reply_to'] : '';
        $send_to = (isset($_POST['send_to']) && !empty($_POST['send_to'])) ? $_POST['send_to'] : '';

        $subject = (isset($_POST['subject']) && !empty($_POST['subject'])) ? $_POST['subject'] : addslashes(esc_html__('SMTP Test E-Mail', 'ARForms'));
        $message = (isset($_POST['message']) && !empty($_POST['message'])) ? $_POST['message'] : '';
        $reply_to_name = (isset($_POST['reply_to_name']) && !empty($_POST['reply_to_name'])) ? $_POST['reply_to_name'] : '';

        if (empty($send_to) || empty($reply_to) || empty($message) || empty($subject)) {
            return;
        }

        echo $arnotifymodel->send_notification_email_user($send_to, $subject, $message, $reply_to, $reply_to_name, '', array(), true, true, true, true);

        die();
    }

    function arfreqlicdeact() {
        global $arformcontroller;

        $plugres = $arformcontroller->arfdeactivatelicense();

        if (isset($plugres) && $plugres != "") {
            echo $plugres;
            exit;
        } else {
            echo "Received Blank Response From Server While License Deactivation";
            exit;
        }
        exit;
    }

    function arfreqlicdeactuninst() {
        global $arformcontroller;
        $plugres = $arformcontroller->arfdeactivatelicense();

        return;
    }

    function arfreqact() {
        global $arformcontroller;
        $plugres = $arformcontroller->arfverifypurchasecode();

        if (isset($plugres) && $plugres != "") {
            $responsetext = $plugres;

            if ($responsetext == "License Activated Successfully.") {
                echo "VERIFIED";
                exit;
            } else {
                echo $plugres;
                exit;
            }
        } else {
            echo "Received Blank Response From Server While License Activation";
            exit;
        }
    }

    function generateplugincode() {
        $siteinfo = array();

        global $arnotifymodel, $arfform;

        $siteinfo[] = $arnotifymodel->sitename();
        $siteinfo[] = $arfform->sitedesc();
        $siteinfo[] = home_url();
        $siteinfo[] = get_bloginfo('admin_email');
        $siteinfo[] = $_SERVER['SERVER_ADDR'];

        $newstr = implode("^", $siteinfo);
        $postval = base64_encode($newstr);

        return $postval;
    }

    function menu() {

        
        add_submenu_page('ARForms', 'ARForms | ' . addslashes(esc_html__('Site-wide Popups', 'ARForms')), addslashes(esc_html__('Site-wide Popups', 'ARForms')), 'arfviewpopupform', 'ARForms-popups', array($this, 'route'));

        add_submenu_page('ARForms', 'ARForms | ' . addslashes(esc_html__('General Settings', 'ARForms')), addslashes(esc_html__('General Settings', 'ARForms')), 'arfchangesettings', 'ARForms-settings', array($this, 'route'));

        add_submenu_page('ARForms', 'ARForms | ' . addslashes(esc_html__('Import Export', 'ARForms')), addslashes(esc_html__('Import / Export', 'ARForms')), 'arfchangesettings', 'ARForms-import-export', array($this, 'route'));

        add_submenu_page('ARForms', 'ARForms | ' . addslashes(esc_html__('Addons', 'ARForms')), addslashes(esc_html__('Addons', 'ARForms')), 'arfviewforms', 'ARForms-addons', array($this, 'route'));

        add_submenu_page('ARForms', 'ARForms | ' . esc_html__('Licensing', 'ARForms'), esc_html__('Licensing', 'ARForms'), 'arfviewforms', 'ARForms-license', array($this, 'route'));
    }

    function route() {

        global $arsettingcontroller;
        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'ARForms-import-export') {
            return $arsettingcontroller->import_export_form();
        } else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'ARForms-addons') {

            if (file_exists(VIEWS_PATH . '/addon_lists.php')) {
                include( VIEWS_PATH . '/addon_lists.php' );
            }
        } else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'ARForms-license') {

            if (file_exists(VIEWS_PATH . '/license_activation.php')) {
                include( VIEWS_PATH . '/license_activation.php' );
            }
        } else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'ARForms-popups'){
            if(file_exists(VIEWS_PATH . '/arf_forms_popup.php')){
                include(VIEWS_PATH . '/arf_forms_popup.php');
            }
        } else {
            $action = isset($_REQUEST['arfaction']) ? 'arfaction' : 'action';


            global $armainhelper, $arsettingcontroller;

            $cur_tab = isset($_REQUEST['arfcurrenttab']) ? $_REQUEST['arfcurrenttab'] : '';

            $action = $armainhelper->get_param($action);


            if ($action == 'process-form')
                return $arsettingcontroller->process_form($cur_tab);
            else
                return $arsettingcontroller->display_form();
        }
    }

    function getdeactlicurl() {
        $deactlicurl = "https://www.reputeinfosystems.com/tf/plugins/arforms/verify/deactivelicwc.php";

        return $deactlicurl;
    }
	
	function getdeactlicurl_wssl() {
        $deactlicurl = "http://www.reputeinfosystems.com/tf/plugins/arforms/verify/deactivelicwc.php";

        return $deactlicurl;
    }

    function display_form() {


        global $arfsettings, $arfajaxurl, $wpdb, $arfform, $armainhelper, $MdlDb;


        $arfroles = $armainhelper->frm_capabilities();





        $uploads = wp_upload_dir();


        $target_path = $uploads['basedir'] . "/arforms/css";


        $sections = apply_filters('arfaddsettingssection', array());



        if (get_option('arf_ar_type') == '') {

            $arr = array(
                'aweber_type' => arf_sanitize_value(1, 'integer'),
                'mailchimp_type' => arf_sanitize_value(1, 'integer'),
                'getresponse_type' => arf_sanitize_value(1, 'integer'),
                'icontact_type' => arf_sanitize_value(1, 'integer'),
                'constant_type' => arf_sanitize_value(1, 'integer'),
                'gvo_type' => arf_sanitize_value(1, 'integer'),
                'ebizac_type' => arf_sanitize_value(1, 'integer'),
                'madmimi_type' => arf_sanitize_value(1, 'integer'),
                'mailerlite_type' => arf_sanitize_value(1, 'integer'),
                'hubspot_type' => arf_sanitize_value(1, 'integer'),
                'convertkit_type' => arf_sanitize_value(1, 'integer'),
                'sendinblue_type' => arf_sanitize_value(1, 'integer'),
                'drip_type' => arf_sanitize_value(1, 'integer'),
            );

            $arr_new = $arr;

            update_option('arf_ar_type', $arr_new);
            
        }


        if (get_option('arf_current_tab') == '') {

            update_option('arf_current_tab', arf_sanitize_value('general_settings'));
        }


        $autores_type = get_option('arf_ar_type');
        $default_ar = maybe_unserialize(get_option('arfdefaultar'));


        $autoresponder_all_data_query = $wpdb->get_results("SELECT * FROM " . $MdlDb->autoresponder);

        $mailchimp_data = $autoresponder_all_data_query[0];
        $madmimi_data = $autoresponder_all_data_query[9];
        $aweber_data = $autoresponder_all_data_query[2];
        $getresponse_data = $autoresponder_all_data_query[3];
        $gvo_data = $autoresponder_all_data_query[4];
        $ebizac_data = $autoresponder_all_data_query[5];
        $icontact_data = $autoresponder_all_data_query[7];
        $constant_data = $autoresponder_all_data_query[8];
        require(VIEWS_PATH . '/settings_form.php');
    }

    function addons_page() {
        global $arsettingcontroller;
        ?><script type="application/javascript" data-cfasync="false">jQuery('#arfsaveformloader').show();</script> <?php
        
        global $arfversion, $MdlDb, $arnotifymodel, $arfform, $arfrecordmeta;
        $bloginformation = array();
        $str = $MdlDb->get_rand_alphanumeric(10);

        if (is_multisite())
            $multisiteenv = "Multi Site";
        else
            $multisiteenv = "Single Site";

        $addon_listing = 1;

        $bloginformation[] = $arnotifymodel->sitename();
        $bloginformation[] = $arfform->sitedesc();
        $bloginformation[] = home_url();
        $bloginformation[] = get_bloginfo('admin_email');
        $bloginformation[] = $arfrecordmeta->wpversioninfo();
        $bloginformation[] = $arfrecordmeta->getlanguage();
        $bloginformation[] = $arfversion;
        $bloginformation[] = $_SERVER['REMOTE_ADDR'];
        $bloginformation[] = $str;
        $bloginformation[] = $multisiteenv;
        $bloginformation[] = $addon_listing;

        $valstring = implode("||", $bloginformation);
        $encodedval = base64_encode($valstring);

        $addon_data = get_transient( 'arf_addon_listing_data_page' );

        if( false == $addon_data ){

            $urltopost = 'https://www.arformsplugin.com/addonlist/addon_list_3.0.php';

            $raw_response = wp_remote_post($urltopost, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array('wpversion' => $encodedval, 'user_agent' => $_SERVER['HTTP_USER_AGENT']),
                'cookies' => array()
                    )
            );

            if (is_wp_error($raw_response) || $raw_response['response']['code'] != 200) {
                echo "<div class='error_message' style='margin-top:100px; padding:20px;'>" . addslashes(esc_html__("Add-On listing is currently unavailable. Please try again later.", "ARForms")) . "</div>";
            } else {
                set_transient( 'arf_addon_listing_data_page', $raw_response['body'], DAY_IN_SECONDS );
                echo $arsettingcontroller->arf_display_addons($raw_response['body']);
            }
        } else {
            echo $arsettingcontroller->arf_display_addons( $addon_data );
        }
        ?><script type="application/javascript" data-cfasync="false">jQuery('#arfsaveformloader').hide();</script><?php
    }

    function arf_display_addons($arf_addons = '') {

        require(VIEWS_PATH . '/arf_view_addons.php');

    }

    function CheckpluginStatus($mypluginsarray, $pluginname, $attr, $purchase_addon, $plugin_type,$install_url) {

        foreach ($mypluginsarray as $pluginarr) {
            $response = "";
            if ($pluginname == $pluginarr[$attr]) {
                if ($pluginarr['is_active'] == 1) {
                    $response = "ACTIVE";
                    $actionurl = $pluginarr["deactivation_url"];
                    $active_action_url = $pluginarr["deactivation_url"];
                    break;
                } else {
                    $response = "NOT ACTIVE";
                    $actionurl = $pluginarr["activation_url"];
                    $active_action_url = $pluginarr["activation_url"];
                    break;
                }
            } else {
                if ($plugin_type == "free") {
                    $response = "NOT INSTALLED FREE";
                    $actionurl = $install_url;
                } else if ($plugin_type == "paid") {
                    $response = "NOT INSTALLED PAID";
                    $actionurl = $install_url;
                }
            }
        }

        global $arformcontroller,$arformsplugin;
        $setvaltolic = $arformcontroller->$arformsplugin();
        $active_plugin_text = esc_html__('Active','ARForms');

        if( $setvaltolic != 1 ){
            $active_plugin_text = esc_html__('Activate License','ARForms');
            $active_action_url = admin_url('admin.php?page=ARForms-license');
        }

        $myicon = "";
        $divclassname = "";
        if ($response == "NOT INSTALLED FREE") {
            $myicon = '<button class="addon_button no_icon" data-action="free_addon_install" data-plugin="' . $pluginname . '" href="javascript:void(0);"><span class="addon_processing_div addon_processing_tick">'.esc_html__('Installed','ARForms').'</span><span class="get_it_a">'.esc_html__('Install','ARForms').'</span><span class="arf_addon_loader"><svg class="arf_circular" viewBox="0 0 60 60"><circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle></svg></span></button>';
        } else if ($response == "NOT INSTALLED PAID") {
            $myicon = '<button class="addon_button" onClick="window.open(\'' . $actionurl . '\',\'_blank\')">
                <span><svg width="25px" height="25px" viewBox="0 0 30 30"><g><path style="fill:#8e9fb2;" d="M26.818,19.037l3.607-10.796c0.181-0.519,0.044-0.831-0.102-1.037   c-0.374-0.527-1.143-0.532-1.292-0.532L8.646,6.668L8.102,4.087c-0.147-0.609-0.581-1.19-1.456-1.19H0.917   C0.323,2.897,0,3.175,0,3.73v1.49c0,0.537,0.322,0.677,0.938,0.677h4.837l3.702,15.717c-0.588,0.623-0.908,1.531-0.908,2.378   c0,1.864,1.484,3.582,3.38,3.582c1.79,0,3.132-1.677,3.35-2.677h7.21c0.218,1,1.305,2.717,3.349,2.717   c1.863,0,3.378-1.614,3.378-3.475c0-1.851-1.125-3.492-3.359-3.492c-0.929,0-2.031,0.5-2.543,1.25h-8.859   c-0.643-1-1.521-1.31-2.409-1.345l-0.123-0.655h13.479C26.438,19.897,26.638,19.527,26.818,19.037z M25.883,22.828   c0.701,0,1.27,0.569,1.27,1.27s-0.569,1.27-1.27,1.27s-1.271-0.568-1.271-1.27C24.613,23.397,25.182,22.828,25.883,22.828z    M13.205,24.098c0,0.709-0.576,1.286-1.283,1.286c-0.709-0.002-1.286-0.577-1.286-1.286s0.577-1.286,1.286-1.286   C12.629,22.812,13.205,23.389,13.205,24.098z"></path></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></span><span class="get_it_a">'.esc_html__('Get It','ARForms').'</span></button>';
        } else if ($response == "ACTIVE") {
            $myicon = '<button class="addon_button no_icon" data-action="deactivate" data-plugin="' . $pluginname . '" href="javascript:void(0);" data-isvalid="'.$setvaltolic.'" data-href=' . $actionurl . '><span class="addon_processing_div addon_processing_tick_deactivation">'.esc_html__('Deactivated','ARForms').'</span><span class="get_it_a">'.esc_html__('Deactivate','ARForms').'</span><span class="arf_addon_loader"><svg class="arf_circular" viewBox="0 0 60 60"><circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle></svg></span></button>';
        } else if ($response == "NOT ACTIVE") {
            $myicon = '<button class="addon_button no_icon" data-action="activate" data-plugin="' . $pluginname . '" href="javascript:void(0);" data-isvalid="'.$setvaltolic.'" data-href=' . $active_action_url . '><span class="addon_processing_div addon_processing_tick">'.esc_html__('Activated','ARForms').'</span><span class="get_it_a">'.$active_plugin_text.'</span><span class="arf_addon_loader"><svg class="arf_circular" viewBox="0 0 60 60"><circle class="path" cx="25px" cy="23px" r="18" fill="none" stroke-width="4" stroke-miterlimit="7"></circle></svg></span></button>';
        }
        return $myicon;
    }

    function import_export_form() {
        require(VIEWS_PATH . '/import_export_form.php');
    }

    function process_form($cur_tab = '') {


        global $arfsettings, $arfajaxurl, $wpdb, $MdlDb;


        $errors = array();


        if ($cur_tab == 'autoresponder_settings') {

            
            if (isset($_REQUEST['mailchimp_type']) && $_REQUEST['mailchimp_type'] == 1) {
                $arf_mailchimp_api = isset($_REQUEST['mailchimp_api']) ? $_REQUEST['mailchimp_api'] : '';
                $arf_mailchimp_listid = isset($_REQUEST['mailchimp_listid']) ? $_REQUEST['mailchimp_listid'] : '';
                $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_mailchimp_api, 'responder_list' => $arf_mailchimp_listid), array('responder_id' => '1'));
            } else {
                $arf_mailchimp_webform = isset($_REQUEST['mailchimp_web_form']) ? $_REQUEST['mailchimp_web_form'] : '';
                $wpdb->update($MdlDb->autoresponder, array('responder_web_form' => $arf_mailchimp_webform), array('responder_id' => '1'));
            }

            if (isset($_REQUEST['madmimi_type']) && $_REQUEST['madmimi_type'] == 1) {
                $arf_responder_api = isset($_REQUEST['madmimi_api']) ? $_REQUEST['madmimi_api'] : '';
                $arf_madmimi_email = isset($_REQUEST['madmimi_email']) ? $_REQUEST['madmimi_email'] : '';
                $arf_madmimi_list_id = isset($_REQUEST['madmimi_listid']) ? $_REQUEST['madmimi_listid'] : '';
                $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_responder_api, 'madmimi_email' => $arf_madmimi_email, 'responder_list' => $arf_madmimi_list_id), array('responder_id' => '10'));
            } else {
                $arf_madmimi_webform = isset($_REQUEST['madmimi_web_form']) ? $_REQUEST['madmimi_web_form'] : '';
                $wpdb->update($MdlDb->autoresponder, array('responder_web_form' => $arf_madmimi_webform), array('responder_id' => '10'));
            }


            if (isset($_REQUEST['aweber_type']) && $_REQUEST['aweber_type'] == 1) {
                $arf_aweber_api = !empty( $_REQUEST['aweber_oauth_code'] ) ? $_REQUEST['aweber_oauth_code'] : '';
                $awe_responder_list = isset($_REQUEST['responder_list']) ? $_REQUEST['responder_list'] : '';
                $wpdb->update($MdlDb->autoresponder, array( 'responder_api_key' => $arf_aweber_api, 'responder_list' => $awe_responder_list), array('responder_id' => '3'));
            } else {
                $arf_aweber_webform = isset($_REQUEST['aweber_web_form']) ? $_REQUEST['aweber_web_form'] : '';
                $wpdb->update($MdlDb->autoresponder, array('responder_web_form' => $arf_aweber_webform), array('responder_id' => '3'));
            }

            if (isset($_REQUEST['getresponse_type']) && $_REQUEST['getresponse_type'] == 1) {
                $arf_getresponse_api = isset($_REQUEST['getresponse_api']) ? $_REQUEST['getresponse_api'] : '';
                $arf_getresponse_list_id = isset($_REQUEST['getresponse_listid']) ? $_REQUEST['getresponse_listid'] : '';
                $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_getresponse_api, 'responder_list_id' => $arf_getresponse_list_id), array('responder_id' => '4'));
            } else {
                $arf_getresponse_webform = isset($_REQUEST['getresponse_web_form']) ? $_REQUEST['getresponse_web_form'] : '';
                $wpdb->update($MdlDb->autoresponder, array('responder_web_form' => $arf_getresponse_webform), array('responder_id' => '4'));
            }
            $arf_gvo_api = isset($_REQUEST['gvo_api']) ? $_REQUEST['gvo_api'] : "";
            $arf_ebazic_api = isset($_REQUEST['ebizac_api']) ? $_REQUEST['ebizac_api'] : '';
            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_gvo_api), array('responder_id' => '5'));
            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_ebazic_api), array('responder_id' => '6'));

            if (isset($_REQUEST['icontact_type']) && $_REQUEST['icontact_type'] == 1) {
                $arf_icontact_api = isset($_REQUEST['icontact_api']) ? $_REQUEST['icontact_api'] : '';
                $arf_icontact_username = isset($_REQUEST['icontact_username']) ? $_REQUEST['icontact_username'] : "";
                $arf_icontact_password = isset($_REQUEST['icontact_password']) ? $_REQUEST['icontact_password'] : '';
                $arf_icontact_listname = isset($_REQUEST['icontact_listname']) ? $_REQUEST['icontact_listname'] : '';

                $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_icontact_api, 'responder_username' => $arf_icontact_username, 'responder_password' => $arf_icontact_password, 'responder_list' => $arf_icontact_listname), array('responder_id' => '8'));
            } else {
                $arf_icontact_webform = isset($_REQUEST['icontact_web_form']) ? $_REQUEST['icontact_web_form'] : '';
                $wpdb->update($MdlDb->autoresponder, array('responder_web_form' => $arf_icontact_webform), array('responder_id' => '8'));
            }


            if (isset($_REQUEST['constant_type']) && $_REQUEST['constant_type'] == 1) {
                $arf_responder_api = isset($_REQUEST['constant_api']) ? $_REQUEST['constant_api'] : "";
                $arf_responder_token = isset($_REQUEST['constant_access_token']) ? $_REQUEST['constant_access_token'] : '';
                $arf_responder_list_id = isset($_REQUEST['constant_listname']) ? $_REQUEST['constant_listname'] : '';
                $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_responder_api, 'responder_list_id' => $arf_responder_token, 'responder_list' => $arf_responder_list_id), array('responder_id' => '9'));
            } else {
                $arf_responder_webform = isset($_REQUEST['constant_web_form']) ? $_REQUEST['constant_web_form'] : '';
                $wpdb->update($MdlDb->autoresponder, array('responder_web_form' => $arf_responder_webform), array('responder_id' => '9'));
            }


            do_action('arf_autoresponder_out_side_email_marketing_tools_update', $_REQUEST);




            $arr = array(
                'aweber_type' => arf_sanitize_value($_REQUEST['aweber_type'], 'integer'),
                'mailchimp_type' => arf_sanitize_value($_REQUEST['mailchimp_type'], 'integer'),
                'getresponse_type' => arf_sanitize_value($_REQUEST['getresponse_type'], 'integer'),
                'icontact_type' => arf_sanitize_value($_REQUEST['icontact_type'], 'integer'),
                'constant_type' => arf_sanitize_value($_REQUEST['constant_type'], 'integer'),
                'gvo_type' => arf_sanitize_value($_REQUEST['gvo_type'], 'integer'),
                'ebizac_type' => arf_sanitize_value($_REQUEST['ebizac_type'], 'integer'),
                'madmimi_type' => arf_sanitize_value($_REQUEST['madmimi_type'], 'integer'),
                'mailerlite_type' => arf_sanitize_value($_REQUEST['madmimi_type'], 'integer'),
                'hubspot_type' => arf_sanitize_value($_REQUEST['hubspot_type'], 'integer'),
                'convertkit_type' => arf_sanitize_value($_REQUEST['convertkit_type'], 'integer'),
                'sendinblue_type' => arf_sanitize_value($_REQUEST['sendinblue_type'], 'integer'),
                'drip_type' => arf_sanitize_value($_REQUEST['drip_type'], 'integer'),
            );

            $arr_new = $arr;


            update_option('arf_ar_type', $arr_new);


            $autores_type = $arr;
        }


        if ($cur_tab == 'general_settings') {

            $arfsettings->update($_POST, $cur_tab);

            $autores_type = get_option('arf_ar_type');
        }

        $autoresponder_all_data_query = $wpdb->get_results("SELECT * FROM " . $MdlDb->autoresponder);
        $mailchimp_data = $autoresponder_all_data_query[0];
        $madmimi_data = $autoresponder_all_data_query[9];
        $aweber_data = $autoresponder_all_data_query[2];
        $getresponse_data = $autoresponder_all_data_query[3];
        $gvo_data = $autoresponder_all_data_query[4];
        $ebizac_data = $autoresponder_all_data_query[5];
        $icontact_data = $autoresponder_all_data_query[7];
        $constant_data = $autoresponder_all_data_query[8];
        if ($cur_tab != '') {

            update_option('arf_current_tab', $cur_tab);
        }

        if (empty($errors)) {


            $arfsettings->store($cur_tab);

            $message_notRquireFeild = '';

            if ($cur_tab == 'general_settings') {
                $message = addslashes(esc_html__('General setting saved successfully.', 'ARForms'));
            } elseif ($cur_tab == 'autoresponder_settings') {
                $message = addslashes(esc_html__('Email Marketing Tools setting saved successfully.', 'ARForms'));
            } else {
                $message = addslashes(esc_html__('Settings Saved.', 'ARForms'));
            }

            if (isset($web_form_msg) and $web_form_msg != '')
                $web_form_msg_default = esc_html__('You have made below required fields which may not supported by system.', 'ARForms').'<br>';

            $web_form_msg = ( (isset($web_form_msg_default)) ? $web_form_msg_default : '') . ( (isset($web_form_msg)) ? $web_form_msg : '');

            @$message_notRquireFeild .= $web_form_msg;
        }

        global $armainhelper;
        $arfroles = $armainhelper->frm_capabilities();


        $sections = apply_filters('arfaddsettingssection', array());



        require(VIEWS_PATH . '/settings_form.php');
    }

    function admin_init() {


        global $arfsettings;


        if (isset($_GET) and isset($_GET['page']) and $_GET['page'] != 'ARForms-settings') {
            wp_enqueue_script('bootstrap-locale-js');
            wp_enqueue_script('bootstrap-datepicker');
        }

        add_action('admin_head-' . sanitize_title($arfsettings->menu) . '_page_ARForms-settings', array($this, 'head'));
    }

    function head() {

        global $armainhelper, $arfversion;

        $customcss = ARFSCRIPTURL . '&amp;controller=settings';

        wp_register_style('custom-css', $customcss, array(), $arfversion);
        $armainhelper->load_styles(array('custom-css'));
        ?>
        <?php
        require(VIEWS_PATH . '/head.php');
    }

    function delete_aweber($atts) {

        global $wpdb, $MdlDb;

        $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => '', 'responder_list_id' => '', 'responder_list' => '', 'is_verify' => '0'), array('responder_id' => 3));


        die();
    }

    function arf_aweber_refresh_token( $list_data ){

        $refreshToken = $list_data['refreshToken'];
        $accessToken = $list_data['accessToken'];

        $post_url = get_home_url().'?arf_page=arfawb_config';

        $refresh_token = wp_remote_post(
            $post_url,
            array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array(
                    'refresh_code' => true,
                    'refreshToken' => $refreshToken,
                    'accessToken' => $accessToken
                ),
                'cookies' => array()
            )
        );

        $updated_list_data  = json_decode($refresh_token['body'],true);

        return $updated_list_data;

    }

    function refresh_aweber($atts) {
        global $wpdb, $arfsiteurl, $MdlDb, $arfform, $maincontroller;

        $res = $arfform->arf_select_db_data( true, '', $MdlDb->autoresponder, '*', 'WHERE responder_id= %d', array(3));

        $res = $res[0];

        $list_data = maybe_unserialize($res->list_data);

        
        if( $list_data['expires_in'] < time() ){
            $list_data = $this->arf_aweber_refresh_token( $list_data );
        }

        $acc_id = $list_data['acc_id'];

        

        $acc_headers = [
            'User-Agent' => 'AWeber-PHP-code-sample/1.0',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $list_data['accessToken'],
        ];

        $lists_url = "https://api.aweber.com/1.0/accounts/".$acc_id."/lists";

        $lists_response = wp_remote_get( $lists_url,
            array(
                'method' => 'GET', 
                'headers' => $acc_headers,
                'timeout' => 5000
            )
        );


        $lists_response = json_decode($lists_response['body']);
        $lists_entries = $lists_response->entries;

        $listname = '';
        $listid = '';
        $first_list_id = '';
        $x = 0;
        foreach ($lists_entries as $offset => $list) {
            if( 0 == $x ){
                $first_list_id = $list->id;
            }
            $listname .= $list->name . "|";

            $listid .= $list->id . "|";
            $x++;
        }
        $listingdetails = "";
        if ($listname != "" && $listid != "") {

            $listingdetails = $listname . "-|-" . $listid;
        }

        
        $res = $wpdb->update($MdlDb->autoresponder, array('responder_list_id' => $listingdetails, 'responder_list' => $first_list_id), array('responder_id' => '3'));
        
        ?>
        <div class="sltstandard" style="float:none; display:inline;">
            

                <?php
                $aweber_lists = explode("-|-", $listingdetails);
                $aweber_lists_name = explode("|", $aweber_lists[0]);
                $aweber_lists_id = explode("|", $aweber_lists[1]);

                $i = 0;
                $selected_list_id = '';
                $selected_list_label = '';

                $aweber_responder_list_option = array();

                foreach ($aweber_lists_name as $aweber_lists_name1) {

                    if ($aweber_lists_id[$i] != "") {
                        
                        if ( 0 == $i ) {
                            $selected_list_id = $aweber_lists_id[$i];
                            $selected_list_label = $aweber_lists_name1;
                        }

                        $aweber_responder_list_option[$aweber_lists_id[$i]] = $aweber_lists_name1;

                    } 
                    $i++;
                }

                echo $maincontroller->arf_selectpicker_dom( 'responder_list', 'aweber_listid', '', 'width:400px;', $selected_list_id, array(), $aweber_responder_list_option, false, array(), false, array(), false, array(), true );
            ?>
        </div>
        <?php
        echo '<span id="aweber_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';

        die();
    }

    function clear_form($atts) {

        global $wpdb, $MdlDb;

        $form_id = $_POST['id'];

        $res = $wpdb->query($wpdb->prepare("DELETE FROM " . $MdlDb->fields . " WHERE form_id = %d", $form_id));

        echo $res;

        die();
    }

    function verify_autores($atts) {

        global $wpdb, $MdlDb,$maincontroller;

        $name = isset($_POST['id']) ? $_POST['id'] : '';

        $api_key = isset($_POST['api_key']) ? $_POST['api_key'] : '';

        $api_secret = isset($_POST['api_secret']) ? $_POST['api_secret'] : '';

        $user = isset($_POST['user']) ? $_POST['user'] :'';

        $pass = isset($_POST['pass']) ? $_POST['pass'] : '';

        $refresh_li = isset($_POST['refresh_li']) ? $_POST['refresh_li'] : '';


        if ($name == 'mailchimp' ){
            global $arf_mcapi_version;
            
            $dataCenter = substr($api_key,strpos($api_key,'-')+1);

            $mailchimp_url = 'https://'.$dataCenter.'.api.mailchimp.com/'.$arf_mcapi_version.'/lists?apikey='.$api_key.'&count=500';

            $response = wp_remote_get($mailchimp_url,array(
                'timeout' => '5000'
            ));

            if( is_wp_error($response) ){
                $errors = $response->get_error_code(); 
                $errors_msg = $response->get_error_message();                
                echo $errors_msg;

            } else {
                $mailchimp_list = json_decode($response['body'],true);


                $list_str = array();

                echo '<div class="sltstandard" style="float:none; display:inline;">';
                $responder_list_option = array();
                $selected_list_label = '';
                $selected_list_id = '';
                $ls = 0;
                foreach ($mailchimp_list['lists'] as $key => $list) {
                    if ($key == 0) {
                        $selected_list_id = $list['id'];
                        $selected_list_label = $list['name'];
                    }
                    $list_str[$ls]['id'] = $list['id'];
                    $list_str[$ls]['name'] = $list['name'];

                    $responder_list_option[$list['id']] = $list['name'];
                    $ls++;
                }

                $wpdb->update(
                    $MdlDb->autoresponder,
                    array(
                        'responder_api_key' => $api_key,
                        'is_verify' => 1,
                        'responder_list_id' => json_encode($list_str)
                    ),
                    array(
                        'responder_id' => 1
                    )
                );

                echo $maincontroller->arf_selectpicker_dom( 'mailchimp_listid', 'mailchimp_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );

                echo '</div>';

            if ($refresh_li == 1)
                echo '<span id="mailchimp_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';
            }
        } 
        if ($name == 'madmimi') {

            require_once(AUTORESPONDER_PATH . 'madmimi/MadMimi.class.php');

            $mailer = new ARFMadMimi($user, $api_key);

            $lists = array();
         
            $string = $mailer->Lists(false);

            $xml = simplexml_load_string($string);
            $xml_array = object2array($xml);
            
            foreach ($xml_array['list'] as $key => $value) {
                $lists[$key]['name'] = $value['@attributes']['name'];
                $lists[$key]['id'] = $value['@attributes']['id'];
            }
            if (count($lists) > 0) {

                $lists_ser = maybe_serialize($lists);

                $res = $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $api_key, 'madmimi_email' => $user, 'is_verify' => 1, 'responder_list_id' => $lists_ser), array('responder_id' => 10));

                echo '<div class="sltstandard" style="float:none; display:inline;">';
                $responder_list_option = array();
                $selected_list_label = '';
                $selected_list_id = '';
                foreach ($lists as $key => $list) {
                    if ($key == 0) {
                        $selected_list_id = $list['id'];
                        $selected_list_label = $list['name'];
                    }

                    $responder_list_option[$list['id']] = $list['name'];
                }

                echo $maincontroller->arf_selectpicker_dom( 'madmimi_listid', 'madmimi_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );

                echo '</div>';
                if ($refresh_li == 1)
                    echo '<span id="madmimi_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';
            }else{
                echo $string;
            }
        }


        if ($name == 'getresponse') {
            $camp = $this->arf_get_getresponse_campaign($api_key);
           
            if (isset($camp) && is_array($camp) && count($camp) > 0) {
               
                $camp_ser = maybe_serialize($camp);

                $res = $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $api_key, 'is_verify' => 1, 'list_data' => $camp_ser), array('responder_id' => 4));

                echo '<div class="sltstandard" style="float:none; display:inline;">';
                $responder_list_option = array();
                $selected_list_label = '';
                $selected_list_id = '';
                foreach ($camp as $list) {
                    $selected_list_id = $list['id'];
                    $selected_list_label = $list['name'];

                    $responder_list_option[$list['id']] = $list['name'];
                }

                echo $maincontroller->arf_selectpicker_dom( 'getresponse_listid', 'getresponse_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );

                echo '</div>';
                if ($refresh_li == 1)
                    echo '<span id="getresponse_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';
            }else{
                echo $camp;
            }
        }


        if ($name == 'icontact') {

            require_once(AUTORESPONDER_PATH . 'icontact/lib/iContactApi.php');

            iContactApi::getInstance()->setConfig(array(
                'appId' => $api_key,
                'apiPassword' => $pass,
                'apiUsername' => $user
            ));

            $oiContact = iContactApi::getInstance();
            try {
                $lists = $oiContact->getLists();
                
                if (count($lists) > 0) {

                    $lists_ser = maybe_serialize($lists);

                    $res = $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $api_key, 'responder_username' => $user, 'responder_password' => $pass, 'is_verify' => 1, 'responder_list_id' => $lists_ser), array('responder_id' => 8));

                    echo '<div class="sltstandard" style="float:none; display:inline;">';
                    $responder_list_option = array();
                    $selected_list_label = '';
                    $selected_list_id = '';
                    foreach ($lists as $key => $list) {
                        if ($key == 0) {
                            $selected_list_id = $list->listId;
                            $selected_list_label = $list->name;
                        }

                        $responder_list_option[$list->listId] = $list->name;
                    }

                echo $maincontroller->arf_selectpicker_dom( 'icontact_listname', 'icontact_listname', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );
                echo '</div>';


                    if ($refresh_li == 1)
                        echo '<span id="icontact_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';
                }
            } catch (Exception $oException) {

                $oiContact->getErrors();
                $oiContact->getLastRequest(); 
                $oiContact->getLastResponse();
            }
        }


        if ($name == 'constant') {

            require_once(AUTORESPONDER_PATH . 'constant_contact/list_contact.php');

            $lists_new = $cc->getLists($user);

            $is_exist = '';
            if (count($lists_new) > 0) {

                $i = 0;
                foreach ($lists_new as $list) {
                    $new_arr[$i]['id'] = arf_sanitize_value($list->id, 'integer');
                    $new_arr[$i]['name'] = arf_sanitize_value($list->name);
                    $new_arr[$i]['status'] = arf_sanitize_value($list->status, 'integer');
                    $new_arr[$i]['contact_count'] = arf_sanitize_value($list->contact_count, 'integer');
                    $i++;
                    if ($is_exist == '')
                        $is_exist = $list->id;
                    else
                        $is_exist = ',' . $list->id;
                }

                if ($is_exist != '') {
                    $lists_ser = maybe_serialize($new_arr);

                    $res = $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $api_key, 'responder_list_id' => $user, 'is_verify' => 1, 'list_data' => $lists_ser), array('responder_id' => 9));

                    echo '<div class="sltstandard" style="float:none; display:inline;">';
                    $responder_list_option = array();
                    $selected_list_label = '';
                    $selected_list_id = '';
                    foreach ($lists_new as $key => $list) {
                        if ($listid == 0) {
                            $selected_list_id = $list->id;
                            $selected_list_label = $list->name;
                        }

                        $responder_list_option[$list->id] = $list->name;
                    }

                echo $maincontroller->arf_selectpicker_dom( 'getresponse_listid', 'getresponse_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );
                echo '</div>';

                    if ($refresh_li == 1)
                        echo '<span id="constant_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';
                }
            }
        }

        if ($name == 'mailerlite') {

            $mailerlitegroups = $this->arf_get_mailerlite_groups($api_key);

            if (count($mailerlitegroups) > 0 && !array_key_exists('error',$mailerlitegroups[0])) {

                $lists_ser = maybe_serialize($mailerlitegroups);

                $res = $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $api_key, 'is_verify' => 1, 'responder_list_id' => $lists_ser), array('responder_id' => 14));

                echo '<div class="sltstandard" style="float:none; display:inline;">';
                $responder_list_option = array();
                $selected_list_label = '';
                $selected_list_id = '';
                foreach ($mailerlitegroups as $key => $mailerlitegroup) {
                    if ($key == 0) {
                        $selected_list_id = $mailerlitegroup['id'];
                        $selected_list_label = $mailerlitegroup['name'];
                    }

                    $responder_list_option[$mailerlitegroup['id']] = $mailerlitegroup['name'];
                }

                echo $maincontroller->arf_selectpicker_dom( 'mailerlite_listid', 'mailerlite_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );
                echo '</div>';

                if ($refresh_li == 1)
                    echo '<span id="mailerlite_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';
            }else{
                foreach ($mailerlitegroups as $key => $mailerlitegroup) {
                    if ($key == 0) {
                        $mailerlite_error = $mailerlitegroup['error'];
                    }
                }   
                echo $mailerlite_error->message;                
            }
        }
	
	    if( 'sendinblue' == $name){

            $sendinblueList = $this->arf_get_sendinblue_lists($api_key);

                if (is_array($sendinblueList) && count($sendinblueList) > 0) {              

                $sendinbluelists_ser = maybe_serialize($sendinblueList);

                $sendinblueres = $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $api_key, 'is_verify' => 1, 'responder_list_id' => $sendinbluelists_ser), array('responder_id' => 16));
                echo '<div class="sltstandard" style="float:none; display:inline;">';
                $responder_list_option = array();
                $selected_list_label = '';
                $selected_list_id = '';
               
                foreach ($sendinblueList as $key => $value) {     
                if ($key == 0) {              
                        $selected_list_id = $value['id'];
                        $selected_list_label = $value['name'];
                }

                $responder_list_option[$value['id']] = $value['name'];
                }

                echo $maincontroller->arf_selectpicker_dom( 'sendinblue_listid', 'sendinblue_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );
                echo '</div>';

                if ($refresh_li == 1)
                    echo '<span id="sendinblue_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';
                }else{
                    echo $sendinblueList;
            }
        }

        if ($name == 'hubspot'){
            $hubspot_lists = $this->arf_get_hubspot_lists($api_key);              

            if ( is_array($hubspot_lists) && count($hubspot_lists) > 0) {
              
                $hub_lists_ser = maybe_serialize($hubspot_lists);

                $res = $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $api_key, 'is_verify' => 1, 'responder_list_id' => $hub_lists_ser), array('responder_id' => 15));

                echo '<div class="sltstandard" style="float:none; display:inline;">';
                $responder_list_option = array();
                $selected_list_label = '';
                $selected_list_id = '';
                foreach ($hubspot_lists as $key => $hub_list) {
                    if ($key == 0) {
                        $selected_list_id = $hub_list['id'];
                        $selected_list_label = $hub_list['name'];
                    }

                    $responder_list_option[$hub_list['id']] = $hub_list['name'];
                }

                echo $maincontroller->arf_selectpicker_dom( 'hubspot_listid', 'hubspot_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );
                echo '</div>';

                if ($refresh_li == 1)
                    echo '<span id="hubspot_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';
            }else{
                echo $hubspot_lists;
            }

        }

        if ($name == 'convertkit'){
            $convertkit_forms = $this->arf_get_convertkit_forms($api_key);
            if ( is_array($convertkit_forms) && count($convertkit_forms) > 0) {
                $convertkit_forms_ser = maybe_serialize($convertkit_forms);

                $res = $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $api_key, 'consumer_secret' => $api_secret, 'is_verify' => 1, 'responder_list_id' => $convertkit_forms_ser), array('responder_id' => 17));

                echo '<div class="sltstandard" style="float:none; display:inline;">';
                $responder_list_option = array();
                $selected_list_label = '';
                $selected_list_id = '';
                foreach ($convertkit_forms as $key => $cnvrtkit_frm) {
                    if ($key == 0) {
                        $selected_list_id = $cnvrtkit_frm['id'];
                        $selected_list_label = $cnvrtkit_frm['name'];
                    }

                    $responder_list_option[$cnvrtkit_frm['id']] = $cnvrtkit_frm['name'];
                }

                echo $maincontroller->arf_selectpicker_dom( 'convertkit_listid', 'convertkit_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );
                echo '</div>';


                if ($refresh_li == 1)
                    echo '<span id="convertkit_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';
            }else{
                echo $convertkit_forms;
            }

        }

        if( $name == 'drip' ){
            $drip_lists = $this->arf_get_drip_campaign_lists( $api_key, $api_secret );

            if( is_array( $drip_lists ) && count( $drip_lists ) > 0 ){
                $drip_lists_ser = maybe_serialize($drip_lists);

                $res = $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $api_key, 'consumer_secret' => $api_secret, 'is_verify' => 1, 'responder_list_id' => $drip_lists_ser), array('responder_id' => 18));

                echo '<div class="sltstandard" style="float:none; display:inline;">';
                $responder_list_option = array();
                $selected_list_label = '';
                $selected_list_id = '';
                foreach ($drip_lists as $key => $drip_list) {
                    if ($key == 0) {
                        $selected_list_id = $drip_list['id'];
                        $selected_list_label = $drip_list['name'];
                    }

                    $responder_list_option .= '<li class="arf_selectbox_option" data-label="' . htmlentities($drip_list['name']) . '" data-value="' . $drip_list['id'] . '">' . $drip_list['name'] . '</li>';

                    $responder_list_option[$drip_list['id']] = $drip_list['name'];
                }

                echo $maincontroller->arf_selectpicker_dom( 'drip_listid', 'drip_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );
                echo '</div>';

                if ($refresh_li == 1){
                    echo '<span id="drip_refresh" class="frm_refresh_li">'.esc_html__('Refreshed','ARForms').'</span>';
                }
            } else {
                echo $drip_lists;
            }
        }

        if( 'aweber' == $name ){
            $authorization_code = !empty( $_POST['api_key'] ) ? $_POST['api_key'] : '';

            if( !empty( $authorization_code ) ){
                $aweber_lists = $this->arf_aweber_authorize_app( $authorization_code );

                if( is_array( $aweber_lists ) && count( $aweber_lists ) > 0 ){
                    $list_html = '<div class="sltstandard" style="float:none; display:inline;">';
                    
                        $aweber_lists_name = explode("|", $aweber_lists[0]);
                        $aweber_lists_id = explode("|", $aweber_lists[1]);

                        $i = 0;
                        $selected_list_id = '';
                        $selected_list_label = '';

                        $aweber_responder_list_option = "";

                        foreach ($aweber_lists_name as $aweber_lists_name1) {

                            if ($aweber_lists_id[$i] != "") {
                                
                                if ( 0 == $i ) {
                                    $selected_list_id = $aweber_lists_id[$i];
                                    $selected_list_label = $aweber_lists_name1;
                                }

                                $aweber_responder_list_option .= '<li class="arf_selectbox_option" data-label="'.$aweber_lists_name1.'" data-value="'.$aweber_lists_id[$i].'" value="'.$aweber_lists_id[$i].'">'.$aweber_lists_name1.'</li>';

                            } 
                            $i++;
                        }

                        $list_html .= '<input name="responder_list" id="aweber_listid" value="'.$selected_list_id.'" type="hidden" class="frm-dropdown frm-pages-dropdown">';
                        $list_html .= '<dl class="arf_selectbox" data-name="aweber_listid" data-id="aweber_listid" style="width: 400px;">';
                            $list_html .= '<dt>';
                                $list_html .= '<span>'.$selected_list_label.'</span>';
                                $list_html .= '<svg viewBox="0 0 2000 1000" width="15px" height="15px">';
                                    $list_html .= '<g fill="#000">';
                                        $list_html .= '<path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>';
                                    $list_html .= '</g>';
                                $list_html .= '</svg>';
                            $list_html .= '</dt>';
                            $list_html .= '<dd>';
                                $list_html .= '<ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="aweber_listid">';
                                    $list_html .= $aweber_responder_list_option;
                                $list_html .= '</ul>';
                            $list_html .= '</dd>';
                            $list_html .= '<span id="aweber_loader2"><div class="arf_imageloader"></div></span>';
                        $list_html .= '</dl>';

                    $list_html .= '</div>';

                    echo $list_html;
                } else {
                    echo $aweber_lists;
                }
            } else {
                echo esc_html__('Authorization Code should not be empty', 'ARForms');
            }
        }

        die();
    }

    function arf_aweber_authorize_app( $authorization_code ){

        $post_url = get_home_url().'?arf_page=arfawb_config';

        $verify_code = wp_remote_post(
            $post_url,
            array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array(
                    'verify_code' => 'true',
                    'authorization_code' => $authorization_code
                ),
                'cookies' => array()
            )
        );

        if( is_wp_error( $verify_code ) ){
            return esc_html__( 'Something went wrong while verifying Authorization', 'ARForms');
        } else {
            $response = json_decode( $verify_code['body'], true );
            if( !empty( $response['aweber_lists']) ){
                $aweber_lists = $response['aweber_lists'];
            } else {
                $aweber_lists = $response['error_msg'];
            }
            return $aweber_lists;
        }


    }

    function arf_get_mailerlite_groups($api_key = '') {
        $mailerliteGroupsList = array();
        if (!empty($api_key)) {
            
            require_once(AUTORESPONDER_PATH . '/mailerlite/mailerlite_group_contact.php');

            $mailerlitegroups = arf_json_decode( $mailerlitegroupsApi['body'] );
            if (count($mailerlitegroups) > 0) {
                $i = 0;
                foreach ($mailerlitegroups as $mailerlitegroupslist) {
                    if(!empty($mailerlitegroupslist->id)){
                        $mailerliteGroupsList[$i]['id'] = $mailerlitegroupslist->id;
                        $mailerliteGroupsList[$i]['name'] = $mailerlitegroupslist->name;
                        $mailerliteGroupsList[$i]['active'] = $mailerlitegroupslist->active;
                        $mailerliteGroupsList[$i]['total'] = $mailerlitegroupslist->total;
                    }
                    $i++;
                }    
            }
        }
        return $mailerliteGroupsList;
    }

    function arf_get_hubspot_lists($api_key = '') {
       
        $hubspot_list = array();
        if (!empty($api_key)) {
            $huburl = "https://api.hubapi.com/contacts/v1/lists?hapikey=".$api_key;
            $get_list_hubspot = wp_remote_get(
                    $huburl,
                    array(
                        'method' => 'GET', 
                        'timeout' => 5000
                    )
                );
           
            if( is_wp_error( $get_list_hubspot ) ){
                    //handle error here
            }else {
                $get_list_hubspot_res = json_decode( $get_list_hubspot['body'] );
                if( 200 != $get_list_hubspot['response']['code']){

                    $error_hubspot = json_decode($get_list_hubspot['body']);                
                    $actual_error_msg = $error_hubspot->message;                                       
                    return $actual_error_msg;  
                }else{
                    if( isset( $get_list_hubspot_res->lists ) ){
                        $get_hubspot_lists = $get_list_hubspot_res->lists;
                        
                        if (count($get_hubspot_lists) > 0) {
                        $i = 0;
                            foreach ($get_hubspot_lists as $get_hubspot_list) {
                                $hubspot_list[$i]['id'] = $get_hubspot_list->listId;
                                $hubspot_list[$i]['name'] = $get_hubspot_list->name;
                                $i++;
                            }
                        }
                    }
                }                
            }
        }
        return $hubspot_list;
    }

    function arf_get_drip_campaign_lists( $api_token = '', $account_id = '' ){
        $drip_campaigns = array();
        if( !empty( $account_id ) && !empty( $api_token ) ){

            $drip_url = 'https://api.getdrip.com/v2/' . $account_id . '/campaigns';
            
            $get_list_drip = wp_remote_get(
                $drip_url,
                array(
                    'timeout' => 5000,
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode( $api_token ),
                        'Content-Type'  => 'application/vnd.api+json',
                    )
                )
            );

            if( is_wp_error( $get_list_drip ) ){
                return esc_html__( 'Something went wrong while verifying Authorization', 'ARForms');
            } else {
                $get_drip_campaigns = json_decode( $get_list_drip['body'] );

                if( 200 != $get_list_drip['response']['code']){
                    return esc_html__( 'Something went wrong while verifying Authorization', 'ARForms');;
                } else {

                    $allCampaigns = !empty( $get_drip_campaigns->campaigns ) ? $get_drip_campaigns->campaigns : array();

                    if (count($allCampaigns) > 0) {
                        $i = 0;
                        foreach ($allCampaigns as $drip_campaign) {
                            $drip_campaigns[$i]['id'] = $drip_campaign->id;
                            $drip_campaigns[$i]['name'] = $drip_campaign->name;
                            $i++;
                        }
                    }

                }
            }

        }
        return $drip_campaigns;
    }

    function arf_get_convertkit_forms($api_key= ''){
        $convertkit_form = array();
        if (!empty($api_key)) {
            $convertkit_url = "https://api.convertkit.com/v3/forms?api_key=".$api_key;

            $get_form_convertkit = wp_remote_get(
                    $convertkit_url,
                    array( 
                        'timeout' => 5000
                    )
                );
            

            if( is_wp_error( $get_form_convertkit ) ){
                    //handle error here
                $errors[] = esc_html__('Something went wrong while verifying Authorization', 'ARForms');
            }else {
                $get_form_convertkit_res = json_decode( $get_form_convertkit['body'] );

                if(200 != $get_form_convertkit['response']['code']){
                   
                    $error_convertkit = json_decode($get_form_convertkit['body']); 
                    
                    $actual_error_msg = $error_convertkit->message;  
                    return $actual_error_msg; 
                }else{
                    if( isset( $get_form_convertkit_res->forms ) ){

                        $get_convertkit_forms = $get_form_convertkit_res->forms;
                        
                        if (count($get_convertkit_forms) > 0) {
                            
                        $i = 0;
                            foreach ($get_convertkit_forms as $get_convertkit_form) {
                                $convertkit_form[$i]['id'] = $get_convertkit_form->id;
                                $convertkit_form[$i]['name'] = $get_convertkit_form->name;
                                $i++;
                            }
                        }
                    }
                }
            }
        }
        return $convertkit_form;
    }

    function arf_get_getresponse_campaign($api_key= ''){
      
      $arf_getresponse_campaign = array();
        if (!empty($api_key)) {
            $arf_getresponse_url = "https://api.getresponse.com/v3/campaigns";
            $arf_args = array(               
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'X-Auth-Token' => 'api-key '.$api_key,
                ),
                'timeout' => 5000

            );
            $arf_getresponse_res = wp_remote_get( $arf_getresponse_url, $arf_args );
            if(is_wp_error($arf_getresponse_res) ){  
                
                $errors = $arf_getresponse_res->get_error_message(); 
                return;
            }else{

                $getresponse_data = json_decode($arf_getresponse_res['body']);            
                
                if( 200 != $arf_getresponse_res['response']['code']){
                    $error_getresponse = json_decode($arf_getresponse_res['body']);                
                    $actual_error_msg = $error_getresponse->message; 
                    
                    return $actual_error_msg;  
                }else{
                    if (count($getresponse_data) > 0 ) {
                    $i = 0;
                        foreach ($getresponse_data as $campaign_list) {
                            if(!empty($campaign_list->campaignId)){
                                $arf_getresponse_campaign[$i]['id'] = $campaign_list->campaignId;
                                $arf_getresponse_campaign[$i]['name'] = $campaign_list->name;
                            }
                            $i++;
                        }
                    }
                }
            }

            
        }
        return $arf_getresponse_campaign;
    }

    function arf_get_sendinblue_lists($api_key = ''){

        $response_data_list = array();
        if(!empty($api_key)){

            $arp_sendinblue_url = 'https://api.sendinblue.com/v3/contacts/lists';
            $args = array(               
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'api-key' => $api_key,
                ),

            );
            $sendinblue_response = wp_remote_get( $arp_sendinblue_url, $args );

            if(is_wp_error($sendinblue_response) ){  
                
                $errors = $sendinblue_response->get_error_message(); 
                return;
            }else{

                $response_data = json_decode($sendinblue_response['body']);             
                
                if( 200 != $sendinblue_response['response']['code']){
                    $error_sendinblue = json_decode($sendinblue_response['body']);                
                    $actual_error_msg = $error_sendinblue->message; 
                    
                    return $actual_error_msg;  
                }else{
                    if (count($response_data->lists) > 0 ) {
                    $i = 0;
                        foreach ($response_data->lists as $response_datalist) {
                            if(!empty($response_datalist->id)){
                                $response_data_list[$i]['id'] = $response_datalist->id;
                                $response_data_list[$i]['name'] = $response_datalist->name;
                            }
                            $i++;
                        }
                    }
                }
            }
        }
        return $response_data_list;

    }

    function delete_autores($atts) {

        global $wpdb, $MdlDb;

        $id = $_POST['id'];

        if ($id == 'mailchimp') {

            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => '', 'responder_list_id' => '', 'responder_list' => '', 'is_verify' => 0), array('responder_id' => 1));
        }

        if ($id == 'madmimi') {

            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => '', 'madmimi_email' => '', 'responder_list_id' => '', 'responder_list' => '', 'is_verify' => 0), array('responder_id' => 10));
        }

        if ($id == 'getresponse') {

            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => '', 'responder_list_id' => '', 'responder_list' => '', 'list_data' => '', 'is_verify' => 0), array('responder_id' => 4));
        }

        if ($id == 'icontact') {

            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => '', 'responder_list_id' => '', 'responder_list' => '', 'is_verify' => 0, 'responder_username' => '', 'responder_password' => ''), array('responder_id' => 8));
        }

        if ($id == 'constant') {

            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => '', 'responder_list_id' => '', 'responder_list' => '', 'list_data' => '', 'is_verify' => 0), array('responder_id' => 9));
        }

        if ($id == 'mailerlite') {

            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => '', 'responder_list_id' => '', 'responder_list' => '', 'is_verify' => 0), array('responder_id' => 14));
        }
         if ($id == 'sendinblue') {

            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => '', 'responder_list_id' => '', 'responder_list' => '', 'is_verify' => 0), array('responder_id' => 16));
        }

        if ($id == 'hubspot') {

            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => '', 'responder_list_id' => '', 'responder_list' => '', 'is_verify' => 0), array('responder_id' => 15));
        }

        if ($id == 'convertkit') {

            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => '', 'responder_list_id' => '', 'responder_list' => '', 'is_verify' => 0), array('responder_id' => 17));
        }

        if( $id == 'drip' ){
            $wpdb->update( $MdlDb->autoresponder, array( 'responder_api_key' => '', 'consumer_secret' => '', 'responder_list_id' => '', 'responder_list' => '', 'is_verify' => 0), array( 'responder_id' => 18 ) );
        }

        die();
    }

    function upload_submit_bg() {


        $file = $_POST['image'];
        ?>
        <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="<?php echo $file; ?>" id="arfsubmitbuttonimagesetting" />
        <img src="<?php echo $file; ?>" height="35" width="35" style="border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('button_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
        <?php
        die();
    }

    function upload_submit_hover_bg() {


        $file = $_POST['image'];
        ?>
        <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="<?php echo $file; ?>" id="arfsubmithoverbuttonimagesetting" />
        <img src="<?php echo $file; ?>" height="35" width="35" style="border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('button_hover_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
        <?php
        die();
    }

    function delete_submit_bg_img() {
        global $arfversion;
        ?>

        <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="" id="arfsubmitbuttonimagesetting" />
        <div class="arfajaxfileupload">
            <?php echo addslashes(esc_html__('Upload Image', 'ARForms')); ?>
            <input type="file" name="submit_btn_img" id="submit_btn_img" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
        </div>

        <input type="hidden" name="imagename" id="imagename" value="" />
        <?php
        
        die();
    }

    function delete_submit_hover_bg_img() {
        global $arfversion;
        ?>

        <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="" id="arfsubmithoverbuttonimagesetting" />
        <div class="arfajaxfileupload">
            <?php echo addslashes(esc_html__('Upload Image', 'ARForms')); ?>
            <input type="file" name="submit_hover_btn_img" id="submit_hover_btn_img" data-val="submit_hover_bg" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
        </div>

        <input type="hidden" name="imagename_submit_hover" id="imagename_submit_hover" value="" />
        <?php
       
        die();
    }

    function upload_form_bg_img() {

        $file = $_POST['image'];
        ?>
        <input type="hidden" name="arfmfbi" onclick="clear_file_submit();" value="<?php echo $file; ?>" id="arfmainform_bg_img" />
        <img src="<?php echo $file; ?>" height="35" width="35" style="border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('form_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
        <?php
        die();
    }

    function delete_form_bg_img() {
        global $arfversion;
        ?>
        <div class="arfajaxfileupload">

            <?php echo addslashes(esc_html__('Upload Image', 'ARForms')); ?>
            <input type="file" name="form_bg_img" id="form_bg_img" data-val="form_bg" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
        </div>
        <input type="hidden" name="imagename_form" id="imagename_form" value="" />
        <input type="hidden" name="arfmfbi" onclick="clear_file_submit();" value="" id="arfmainform_bg_img" />

        <?php
        wp_register_script('arffiledrag', ARFURL . '/js/filedrag/filedrag.js', array(), $arfversion);
        wp_print_scripts('arffiledrag');
        ?>
        <script type="application/javascript" data-cfasync="false">
        <?php
        $wp_upload_dir = wp_upload_dir();
        if (is_ssl()) {
            $upload_css_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/');
        } else {
            $upload_css_url = $wp_upload_dir['baseurl'] . '/arforms/';
        }
        
        die();
    }

    function delete_submit_bg_img_IE89() {
        ?>
        <span style="display:inline-block;color:#FFFFFF;text-align:center;">Upload</span>
        <input type="text" class="original" name="submit_btn_img" id="field_arfsbis" data-form-id="" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; width: 160px; height: 59px; left: -999px; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />

        <input type="hidden" id="type_arfsbis" name="type_arfsbis" value="1" >
        <input type="hidden" value="jpg, jpeg, jpe, gif, png, bmp, tif, tiff, ico" id="file_types_arfsbis" name="field_types_arfsbis" />
        <input type="hidden" name="imagename" id="imagename" value="" />
        <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="" id="arfsubmitbuttonimagesetting" />
        <input type="hidden" name="imagename" id="imagename" value="" />
        <script type="application/javascript" data-cfasync="false">
        <?php
        $wp_upload_dir = wp_upload_dir();
        if (is_ssl()) {
            $upload_css_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/');
        } else {
            $upload_css_url = $wp_upload_dir['baseurl'] . '/arforms/';
        }
        die();
    }

    function delete_submit_hover_bg_img_IE89() {
        ?>
        <span style="display:inline-block;color:#FFFFFF;text-align:center;">Upload</span>
        <input type="text" class="original" name="submit_hover_btn_img" id="field_arfsbhis" data-form-id="" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; width: 160px; height: 59px; left: -999px; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />

        <input type="hidden" id="type_arfsbhis" name="type_arfsbhis" value="1" >
        <input type="hidden" value="jpg, jpeg, jpe, gif, png, bmp, tif, tiff, ico" id="file_types_arfsbhis" name="field_types_arfsbhis" />
        <input type="hidden" name="imagename_submit_hover" id="imagename_submit_hover" value="" />
        <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="" id="arfsubmithoverbuttonimagesetting" />
        <input type="hidden" name="imagename_submit_hover" id="imagename_submit_hover" value="" />
        <script type="application/javascript" data-cfasync="false">
        <?php
        die();
    }

    function delete_form_bg_img_IE89() {
        ?>
        <span style="display:inline-block;color:#FFFFFF;text-align:center;">Upload</span>
        <input type="text" class="original" name="form_bg_img" id="field_arfmfbi" data-form-id="" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; width: 160px; height: 59px; left: -999px; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />

        <input type="hidden" id="type_arfmfbi" name="type_arfmfbi" value="1" >
        <input type="hidden" value="jpg, jpeg, jpe, gif, png, bmp, tif, tiff, ico" id="file_types_arfmfbi" name="field_types_arfmfbi" />
        <input type="hidden" name="imagename_form" id="imagename_form" value="" />
        <input type="hidden" name="arfmfbi" onclick="clear_file_submit();" value="" id="arfmainform_bg_img" />
        <script type="application/javascript" data-cfasync="false">
        <?php
        $wp_upload_dir = wp_upload_dir();
        if (is_ssl()) {
            $upload_css_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/');
        } else {
            $upload_css_url = $wp_upload_dir['baseurl'] . '/arforms/';
        }
        die();
    }

    function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array($r, $g, $b);

        return implode(",", $rgb);
    }

    function rgba2rgb($rgb,$alpha){

        $r = 1 * $rgb[0] + $alpha * $rgb[0];
        $g = 1 * $rgb[1] + $alpha * $rgb[1];
        $b = 1 * $rgb[2] + $alpha * $rgb[2];

        return array( $r,$g,$b );

    }
    
    function isColorDark($color) {
        $colors = explode(',',$this->hex2rgb($color));
        $r = $colors[0];
        $g = $colors[1];
        $b = $colors[2];
        $darkness = round((1 - (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255),2);
        if ($darkness < 0.5) {
            return false; // It's a light color
        } else {
            return true; // It's a dark color
        }
    }

    function arf_is_addon_activated($plugin,$network_activation){

        $setvaltolic = 0;
        global $arformcontroller,$arformsplugin,$maincontroller;
        $setvaltolic = $arformcontroller->$arformsplugin();
        if( $setvaltolic != 0 ){
            return;
        }
        $maincontroller->arf_start_session(true);

        $urltopost = 'https://www.arformsplugin.com/addonlist/arf_addon_api_details.php';

        $raw_response = wp_remote_post($urltopost,
            array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array(),
                'cookies' => array()
            )
        );

        if (is_wp_error($raw_response) || $raw_response['response']['code'] != 200) {
            return;
        } else {
            $arf_addons = json_decode($raw_response['body'],true);
            $arforms_addons = array();
            if( is_array($arf_addons) && count($arf_addons) > 0 ){
                foreach( $arf_addons as $arf_addon){
                    $arforms_addons[$arf_addon['plugin_installer']] = $arf_addon['arf_plugin_full_name'];
                }
            }
            
            if( is_array($arforms_addons) && count($arforms_addons) > 0 && array_key_exists($plugin, $arforms_addons) && $setvaltolic == 0 ){
                
                deactivate_plugins($plugin, TRUE);
                header('Location: ' . network_admin_url('plugins.php?deactivate=true&arf_license_deactivate=true&arf_addon=' . $arforms_addons[$plugin]));
                die;
            }
        }
    }

    function arf_install_plugin() {

        if (empty($_POST['slug'])) {
            wp_send_json_error(array(
                'slug' => '',
                'errorCode' => 'no_plugin_specified',
                'errorMessage' => addslashes(esc_html__('No plugin specified.', 'ARForms')),
            ));
        }

        $plugin = $_POST['slug'];
        $plugin = plugin_basename(trim($plugin));
        $plugin_slug = explode("/", $plugin);
        $plugin_slug = $plugin_slug[0];

        $status = array(
            'install' => 'plugin',
            'slug' => sanitize_key(wp_unslash($plugin_slug)),
        );

        if (!current_user_can('install_plugins')) {
            $status['errorMessage'] = addslashes(esc_html__('Sorry, you are not allowed to install plugins on this site.', 'ARForms'));
            wp_send_json_error($status);
        }

        if (file_exists(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php')) {
            include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
        }

        if (file_exists(ABSPATH . 'wp-admin/includes/plugin-install.php')){
            include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
        }

        $api = plugins_api('plugin_information', array(
            'slug' => sanitize_key(wp_unslash($plugin_slug)),
            'fields' => array(
                'sections' => false,
            ),
        ));

        if (is_wp_error($api)) {
            $status['errorMessage'] = $api->get_error_message();
            wp_send_json_error($status);
        }

        $status['pluginName'] = $api->name;

        $skin = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader($skin);

        $result = $upgrader->install($api->download_link);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $status['debug'] = $skin->get_upgrade_messages();
        }

        if (is_wp_error($result)) {
            $status['errorCode'] = $result->get_error_code();
            $status['errorMessage'] = $result->get_error_message();
            wp_send_json_error($status);
        } elseif (is_wp_error($skin->result)) {
            $status['errorCode'] = $skin->result->get_error_code();
            $status['errorMessage'] = $skin->result->get_error_message();
            wp_send_json_error($status);
        } elseif ($skin->get_errors()->get_error_code()) {
            $status['errorMessage'] = $skin->get_error_messages();
            wp_send_json_error($status);
        } elseif (is_null($result)) {
            global $wp_filesystem;

            $status['errorCode'] = 'unable_to_connect_to_filesystem';
            $status['errorMessage'] = addslashes(esc_html__('Unable to connect to the filesystem. Please confirm your credentials.', 'ARForms'));

            if ($wp_filesystem instanceof WP_Filesystem_Base && is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
                $status['errorMessage'] = esc_html($wp_filesystem->errors->get_error_message());
            }

            wp_send_json_error($status);
        }

        $install_status = $this->arf_install_plugin_install_status($api);

        if (current_user_can('activate_plugins') && is_plugin_inactive($install_status['file'])) {
            $status['activateUrl'] = add_query_arg(array(
                '_wpnonce' => wp_create_nonce('activate-plugin_' . $install_status['file']),
                'action' => 'activate',
                'plugin' => $install_status['file'],
                    ), network_admin_url('plugins.php'));
        }

        if (is_multisite() && current_user_can('manage_network_plugins')) {
            $status['activateUrl'] = add_query_arg(array('networkwide' => 1), $status['activateUrl']);
        }
        $status['pluginFile'] = $install_status['file'];

        wp_send_json_success($status);
    }

    function arf_activate_plugin() {
        $plugin = $_POST['slug'];
        $plugin = plugin_basename(trim($plugin));
        $network_wide = false;
        $silent = false;
        $redirect = '';
        if (is_multisite() && ( $network_wide || is_network_only_plugin($plugin) )) {
            $network_wide = true;
            $current = get_site_option('active_sitewide_plugins', array());
            $_GET['networkwide'] = 1; // Back compat for plugins looking for this value.
        } else {
            $current = get_option('active_plugins', array());
        }

        $valid = validate_plugin($plugin);
        if (is_wp_error($valid))
            return $valid;

        if (( $network_wide && !isset($current[$plugin]) ) || (!$network_wide && !in_array($plugin, $current) )) {
            if (!empty($redirect))
                wp_redirect(add_query_arg('_error_nonce', wp_create_nonce('plugin-activation-error_' . $plugin), $redirect)); // we'll override this later if the plugin can be included without fatal error
            ob_start();
            wp_register_plugin_realpath(WP_PLUGIN_DIR . '/' . $plugin);
            $_wp_plugin_file = $plugin;
            include_once( WP_PLUGIN_DIR . '/' . $plugin );
            $plugin = $_wp_plugin_file; // Avoid stomping of the $plugin variable in a plugin.

            if (!$silent) {
                do_action('activate_plugin', $plugin, $network_wide);
                do_action('activate_' . $plugin, $network_wide);
            }

            if ($network_wide) {
                $current = get_site_option('active_sitewide_plugins', array());
                $current[$plugin] = time();
                update_site_option('active_sitewide_plugins', $current);
            } else {
                $current = get_option('active_plugins', array());
                $current[] = $plugin;
                sort($current);
                update_option('active_plugins', $current);
            }

            if (!$silent) {
                do_action('activated_plugin', $plugin, $network_wide);
            }
            $response = array();
            if (ob_get_length() > 0) {
                $response = array(
                    'type' => 'error'
                );
                echo json_encode($response);
                die();
            } else {
                $response = array(
                    'type' => 'success'
                );
                echo json_encode($response);
                die();
            }
        }
        die();
    }

    function arf_deactivate_plugin() {        
        $plugin = $_POST['slug'];
        $silent = false;
        $network_wide = false;
        if (is_multisite())
            $network_current = get_site_option('active_sitewide_plugins', array());
        $current = get_option('active_plugins', array());
        $do_blog = $do_network = false;


        $plugin = plugin_basename(trim($plugin));


        $network_deactivating = false !== $network_wide && is_plugin_active_for_network($plugin);

        if (!$silent) {
            do_action('deactivate_plugin', $plugin, $network_deactivating);
        }

        if (false != $network_wide) {
            if (is_plugin_active_for_network($plugin)) {
                $do_network = true;
                unset($network_current[$plugin]);
            } elseif ($network_wide) {
                
            }
        }

        if (true != $network_wide) {
            $key = array_search($plugin, $current);
            if (false !== $key) {
                $do_blog = true;
                unset($current[$key]);
            }
        }

        if (!$silent) {
            do_action('deactivate_' . $plugin, $network_deactivating);
            do_action('deactivated_plugin', $plugin, $network_deactivating);
        }


        if ($do_blog)
            update_option('active_plugins', $current);
        if ($do_network)
            update_site_option('active_sitewide_plugins', $network_current);

        global $arformcontroller,$arformsplugin;
        $setvaltolic = $arformcontroller->$arformsplugin();

        $response = array(
            'type' => 'success'
        );
        if( $setvaltolic != 1){
            $response['url'] = admin_url('admin.php?page=ARForms-license');
        }
        echo json_encode($response);
        die();
    }

    function arf_install_plugin_install_status($api, $loop = false) {
        // This function is called recursively, $loop prevents further loops.
        if (is_array($api))
            $api = (object) $api;

        // Default to a "new" plugin
        $status = 'install';
        $url = false;
        $update_file = false;
        $version = '';

        /*
         * Check to see if this plugin is known to be installed,
         * and has an update awaiting it.
         */
        $update_plugins = get_site_transient('update_plugins');
        if (isset($update_plugins->response)) {
            foreach ((array) $update_plugins->response as $file => $plugin) {
                if ($plugin->slug === $api->slug) {
                    $status = 'update_available';
                    $update_file = $file;
                    $version = $plugin->new_version;
                    if (current_user_can('update_plugins'))
                        $url = wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=' . $update_file), 'upgrade-plugin_' . $update_file);
                    break;
                }
            }
        }

        if ('install' == $status) {
            if (is_dir(WP_PLUGIN_DIR . '/' . $api->slug)) {
                $installed_plugin = get_plugins('/' . $api->slug);
                if (empty($installed_plugin)) {
                    if (current_user_can('install_plugins'))
                        $url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
                } else {
                    $key = array_keys($installed_plugin);
                    $key = reset($key); //Use the first plugin regardless of the name, Could have issues for multiple-plugins in one directory if they share different version numbers
                    $update_file = $api->slug . '/' . $key;
                    if (version_compare($api->version, $installed_plugin[$key]['Version'], '=')) {
                        $status = 'latest_installed';
                    } elseif (version_compare($api->version, $installed_plugin[$key]['Version'], '<')) {
                        $status = 'newer_installed';
                        $version = $installed_plugin[$key]['Version'];
                    } else {
                        //If the above update check failed, Then that probably means that the update checker has out-of-date information, force a refresh
                        if (!$loop) {
                            delete_site_transient('update_plugins');
                            wp_update_plugins();
                            return arf_install_plugin_install_status($api, true);
                        }
                    }
                }
            } else {
                // "install" & no directory with that slug
                if (current_user_can('install_plugins'))
                    $url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug), 'install-plugin_' . $api->slug);
            }
        }
        if (isset($_GET['from']))
            $url .= '&amp;from=' . urlencode(wp_unslash($_GET['from']));

        $file = $update_file;
        return compact('status', 'url', 'version', 'file');
    }

    function arf_upgrader_package_options($options) {
        $options['is_multi'] = false;
        return $options;
    }

    function arf_plugin_api_args($args, $action) {
        return $args;
    }

    function arf_plugin_api($res, $action, $args) {
        $arforms_addons = get_transient( 'arf_addon_installation_page_data' );
        if( isset( $arforms_addons) && !empty( $arforms_addons) ) {
            $obj = array();
            foreach ($arforms_addons as $slug => $arforms_addon) {
                if (isset($slug) && isset($args->slug)) {
                    if ($slug != $args->slug) {
                        continue;
                    } else {
                        $obj['name'] = $arforms_addon['full_name'];
                        $obj['slug'] = $slug;
                        $obj['version'] = $arforms_addon['plugin_version'];
                        $obj['download_link'] = $arforms_addon['install_url'];
                        return (object) $obj;
                    }
                } else {
                    continue;
                }
            }
        }
        return $res;
    }

    function arf_plugins_api_result($res, $action, $args) {
        return $res;
    }

    function arf_generate_color_tone($hex, $steps) {

        $steps = max(-255, min(255, $steps));

        $hex = str_replace('#', '', $hex);

        if ($hex != '' && strlen($hex) < 6) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $color_parts = str_split($hex, 2);
        $return = '#';

        $acsteps = str_replace(array('+', '-'), array('', ''), $steps);

        if (strlen($acsteps) > 2)
            $lum = $steps / 1000;
        else
            $lum = $steps / 100;

        foreach ($color_parts as $color) {
            $color = hexdec($color);
            $color = round(max(0, min(255, $color + ($color * $lum))));
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT);
        }

        return $return;
    }
    
    function arf_array_map($input = array()) {
        if (empty($input)) {
            return $input;
        }
	
        return is_array($input) ? array_map(array($this, __FUNCTION__), $input) : trim($input);
    }

    function arf_remove_directory($directory){
        if( $directory == '' ){
            return false;
        }

        if( is_dir($directory) )
            $dir_handle = opendir($directory);

        if( !isset($dir_handle) )
            return false;

        while( $file = readdir($dir_handle) ){
            if( $file != "." && $file != ".." ){
                if( !is_dir($directory.'/'.$file) ){
                    if( false == @unlink($directory.'/'.$file) ){
                        @chmod($directory.'/'.$file,0777);
                        @unlink($directory.'/'.$file);
                    }
                } else {
                    $this->arf_remove_directory($directory.'/'.$file);
                }
            }
        }
        closedir($dir_handle);
        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->rmdir($directory);
        return true;
    }

    function arf_add_mailchimp_subscriber_callback($mailchimp_arr,$fname,$lname,$email,$fid){
        if( '' == $email ){
            return esc_html__('No email address provided','ARForms');
        }

        if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email)) {
            return esc_html__("Email address is invalid","ARForms");
        }

        global $wpdb,$MdlDb,$arf_mcapi_version;

        $res = $wpdb->get_results("SELECT * FROM ".$MdlDb->autoresponder." WHERE responder_id='1'");$res = $res[0];
        $responder_api_key = $res->responder_api_key;

        $data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$MdlDb->ar." WHERE frm_id = %d", $fid), 'ARRAY_A' );
        $arr_mailchimp  = maybe_unserialize( $data[0]['mailchimp'] );
        $responder_list_id = isset($arr_mailchimp['type_val'])?$arr_mailchimp['type_val']:'';
        $double_opt_in = isset($arr_mailchimp['double_optin']) ? $arr_mailchimp['double_optin'] : 0;

        $status = 'subscribed';
        if( 1 == $double_opt_in  ){
            $status = 'pending';
        }

        $merge_fields = array(
            'FNAME' => isset( $fname ) ? $fname : '',
            'LNAME' => isset( $lname ) ? $lname : ''
        );

        $merge_fields = apply_filters('arf_mailchimp_additional_fields_from_outside',$merge_fields,$fid,$arr_mailchimp);

        $update_existing = apply_filters('arf_is_update_mailchimp_subscriber',false,$fid);
            
        $arf_mcapi_dc = substr($responder_api_key,strpos($responder_api_key,'-')+1);

        $post_fields = array(
            'email_address' => $email,
            'status' => $status,
            'merge_fields' => $merge_fields,
        );

        $post_fields = apply_filters('arf_mailchimp_tag_fields_from_outside',$post_fields );


        if( true == $update_existing ){
            $arf_mcapi_member = md5(strtolower($email));
            $arf_mcapi_url = 'https://'.$arf_mcapi_dc.'.api.mailchimp.com/'.$arf_mcapi_version.'/lists/'.$responder_list_id.'/members/'.$arf_mcapi_member;

            $arguments = array(
                'timeout' => '5000',
                'method' => 'PUT',
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic '.base64_encode( 'user:' . $responder_api_key )
                ),
                'body' => json_encode($post_fields)
            );

            $arf_mc_subscriber = wp_remote_request($arf_mcapi_url,$arguments);
            

        } else {
            

            $arguments = array(
                'timeout' => '5000',
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode( 'user:' . $responder_api_key )
                ),
                'body' => json_encode($post_fields),
            );

            $arf_mc_api_url = 'https://'.$arf_mcapi_dc.'.api.mailchimp.com/'.$arf_mcapi_version.'/lists/'.$responder_list_id.'/members';

            $arf_mc_subscriber = wp_remote_post($arf_mc_api_url,$arguments);

        }

    }

    function arf_remove_backup_data_callback(){
        global $wpdb;

        $wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS `".$wpdb->prefix."arf_forms_backup`" ) );
        $wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS `".$wpdb->prefix."arf_fields_backup`" ) );

        $wp_upload_dir = wp_upload_dir();
        $backup_dir = $wp_upload_dir['basedir'].'/arforms/maincss_backup';
        if( is_dir($backup_dir) ){
            arf_rmdir( $backup_dir );
        }
    }

    function arf_aweber_configuration(){
        if( !empty( $_REQUEST['arf_page'] ) && 'arfawb_config' == $_REQUEST['arf_page'] ){
            require_once AUTORESPONDER_PATH . '/aweber/configuration.php';
            die;
        }

    }

}

function object2array($object) {
    return @json_decode(@json_encode($object), 1);
}
?>