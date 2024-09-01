<?php
class arnotifymodel {
    function __construct() {
        add_filter('arfstopstandardemail', array($this, 'stop_standard_email'));
        add_action('arfaftercreateentry', array($this, 'entry_created'), 11, 2);
        /**
         * Priority Set To 5 For First Execution
         */
        add_action('arfaftercreateentry', array($this, 'arf_prevent_paypal_to_stop_sending_email'),1,2);
        add_action('arfaftercreateentry', array($this, 'arf_autoreponder_entry'), 10, 2);
        add_action('arfaftercreateentry', array($this, 'sendmail_entry_created'), 10, 2);
        add_action('arfafterupdateentry', array($this, 'entry_updated'), 11, 2);
        add_action('arfaftercreateentry', array($this, 'autoresponder'), 11, 2);
        add_action('arfaftercreateentry', array($this, 'arf_remove_entries_after_submit'), 1001, 2);
    }

    function arf_prevent_paypal_to_stop_sending_email($entry_id,$form_id){
        if(empty($entry_id)) {
            return;
        }
        global $wpdb,$MdlDb;
        $prevent_sending_email = false;
        if( !function_exists('is_plugin_active')){
            if (file_exists( ABSPATH . 'wp-admin/includes/plugin.php' )) {
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
        }

        $prevent_sending_email = apply_filters('arf_prevent_paypal_to_stop_sending_email_outside',$prevent_sending_email,$entry_id,$form_id);

        if( file_exists(WP_PLUGIN_DIR.'/arformspaypal/arformspaypal.php') && is_plugin_active('arformspaypal/arformspaypal.php') && $prevent_sending_email ) {
            global $arf_paypal;
            remove_action('check_arf_payment_gateway',array($arf_paypal,'arf_paypal_check_response'),20);
        }
    }
    
    function sendmail_entry_created($entry_id, $form_id) {

        if (apply_filters('arfstopstandardemail', false, $entry_id)) {
            return;
        }

        if ($_SESSION['arf_payment_check_form_id'] === '') {
            $_SESSION['arf_payment_check_form_id'] = $form_id;
        }
        global $arfform, $db_record, $arfrecordmeta;
        $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $entry = $db_record->getOne($entry_id);
        $form = $arfform->getOne($form_id);
        $form->options = maybe_unserialize($form->options);
        $values = $arfrecordmeta->getAll("it.entry_id = $entry_id", " ORDER BY fi.id");
        if (isset($form->options['notification'])) {
            $notification = reset($form->options['notification']);
        }
        else {
            $notification = $form->options;
        }

        $to_email = $notification[0]['email_to'];

        if ($to_email == '') {
            $to_email = get_option('admin_email');
        }

        $to_emails = explode(',', $to_email);
        $reply_to = $reply_to_name = $user_nreplyto = '';
        $opener = sprintf(addslashes(__('%1$s form has been submitted on %2$s.', 'ARForms')), $form->name, $arfblogname) . "\r\n\r\n";

        $entry_data = '';

        foreach ($values as $value) {
            $value = apply_filters('arf_brfore_send_mail_chnage_value', $value, $entry_id, $form_id);
            $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);

            if (is_array($val)) {
                $val = implode(', ', $val);
            }

            if ($value->field_type == 'textarea') {
                $val = "\r\n" . $val;
            }

            $entry_data .= $value->field_name . ': ' . $val . "\r\n\r\n";

            if (isset($notification['reply_to']) and (int) $notification['reply_to'] == $value->field_id and is_email($val)) {
                $reply_to = $val;
            }

            if (isset($notification['admin_nreplyto_email']) and (int) $notification['admin_nreplyto_email'] == $value->field_id and is_email($val)) {
                $user_nreplyto = $val;
            }

            if (isset($notification['reply_to_name']) and (int) $notification['reply_to_name'] == $value->field_id) {
                $reply_to_name = $val;
            }
        }

        if (empty($reply_to)) {

            if ($notification['reply_to'] == 'custom')
                $reply_to = $notification['cust_reply_to'];

            $reply_to = $notification[0]['reply_to'];

            if (empty($reply_to))
                $reply_to = get_option('admin_email');
        }

        if (empty($user_nreplyto)) {

            if (empty($user_nreplyto))
                $user_nreplyto = get_option('admin_email');
        }

        if (empty($reply_to_name)) {

            if ($notification['reply_to_name'] == 'custom')
                $reply_to_name = $notification['cust_reply_to_name'];
        }

        $data = maybe_unserialize($entry->description);

        $mail_body = $opener . $entry_data . "\r\n";
		
		$setlicval = 0;
        global $arf_get_version_val;
        global $arfmsgtounlicop;
        $setlicval = $arformcontroller->$arf_get_version_val();
			
		if($setlicval == 0) 
		{
		  $my_aff_code = "reputeinfosystems";
			
		  $mail_body .='<div id="brand-div" class="brand-div top_container" style="margin-top:30px; font-size:12px !important; color: #444444 !important; display:block !important; visibility: visible !important;">' . addslashes(esc_html__('Powered by', 'ARForms')) . '&#32;';
		  
		  $mail_body .='<a href="https://codecanyon.net/item/arforms-exclusive-wordpress-form-builder-plugin/6023165?ref=' . $my_aff_code . '" target="_blank" style="color:#0066cc !important; font-size:12px !important; display:inline !important; visibility:visible !important;">ARForms</a>';
			
           $mail_body .= "\r\n". '<span style="color:#FF0000 !important; font-size:12px !important; display:block !important; visibility: visible !important;">' . addslashes(__('&nbsp;&nbsp;' . $arfmsgtounlicop, 'ARForms')) . '</span>';
		   
		   $mail_body .='</div>'. "\r\n";
        }

        $subject = sprintf(addslashes(__('%1$s Form submitted on %2$s', 'ARForms')), $form->name, $arfblogname);

        if (is_array($to_emails)) {
            foreach ($to_emails as $to_email)
        		/* reputelog - check for last 2 arguments */
                $this->send_notification_email_user(trim($to_email), $subject, $mail_body, $reply_to, $reply_to_name, true, array(), false, false, false, false, $user_nreplyto,'','', $entry_id );
        } else
		/* reputelog - check for last 2 arguments */
            $this->send_notification_email_user($to_email, $subject, $mail_body, $reply_to, $reply_to_name, true, array(), false, false, false, false, $user_nreplyto,'','', $entry_id);        
    }

	/* reputelog - check for last 2 arguments */
    function send_notification_email_user($to_email, $subject, $message, $reply_to = '', $reply_to_name = '', $plain_text = true, $attachments = array(), $return_value = false, $use_only_smtp_settings = false, $check = false,$enable_debug=false, $user_nreplyto = '',$cc_email = '',$bcc_email = '', $entry_id = '') {
        
        global $is_submit,$arfsettings, $arformcontroller ,$wpdb,$MdlDb;

        $message = $arformcontroller->arf_html_entity_decode($message);

        $is_submit = true;
        if ($check === false) {
            do_action('check_arf_payment_gateway', array('to' => $to_email, 'subject' => $subject, 'message' => $message, 'reply_to' => $reply_to, 'reply_to_name' => $reply_to_name, 'plain_text' => $plain_text, 'attachments' => $attachments, 'return_value' => $return_value, 'use_only_smtp' => $use_only_smtp_settings, 'form_id' => $_SESSION['arf_payment_check_form_id'], 'nreply_to' => $user_nreplyto, 'cc_email' => $cc_email, 'bcc_email' => $bcc_email, 'entry_id' => $entry_id));
            global $is_submit;
            update_option('is_arf_submit',$is_submit);
        } else {
            $is_submit = true;
        }

        if ($is_submit === false) {
            return;
        }
	
	    $plain_text = (isset($arfsettings->arf_email_format) && $arfsettings->arf_email_format == 'plain')?true:false;        
	    $content_type = ($plain_text) ? 'text/plain' : 'text/html';
        $reply_to_name = ($reply_to_name == '') ? wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) : $reply_to_name;
        $reply_to = ($reply_to == '' or $reply_to == '[admin_email]') ? get_option('admin_email') : $reply_to;
        if ($to_email == '[admin_email]')
            $to_email = get_option('admin_email');
        $recipient = $to_email;
        $header = array();
        $header[] = 'From: "' . $reply_to_name . '" <' . $reply_to . '>';
        $header[] = 'Reply-To: ' . $user_nreplyto;
        if( is_array($cc_email) ){
            foreach($cc_email as $ccemail ){
                if( !empty( $ccemail ) ){
                    $header[] = 'Cc: "' . $ccemail . '" <' . $ccemail . '>';
                }
            }
        }else{
            if( !empty( $cc_email ) ){
                $header[] = 'Cc: "' . $cc_email . '" <' . $cc_email . '>';
            }
        }
        if( is_array($bcc_email) ){
            foreach($bcc_email as $bccemail ){
                if( !empty( $bccemail ) ){
                    $header[] = 'Bcc: "' . $bccemail . '" <' . $bccemail . '>';
                }
            }
        }else{
            if( !empty( $bcc_email ) ){
                $header[] = 'Bcc: "' . $bcc_email . '" <' . $bcc_email . '>';
            }
        }

        $header[] = 'Content-Type: ' . $content_type . '; charset="' . get_option('blog_charset') . '"';
        
        $subject = wp_specialchars_decode(strip_tags(stripslashes($subject)), ENT_QUOTES);
        $message = do_shortcode($message);
        $message = stripslashes( $message );
        if ($plain_text){
            $message = wp_specialchars_decode(strip_tags($message), ENT_QUOTES);
        }
        $header = apply_filters('arfemailheader', $header, compact('to_email', 'subject'));
        remove_filter('wp_mail_from', 'bp_core_email_from_address_filter');
        remove_filter('wp_mail_from_name', 'bp_core_email_from_name_filter');
        global $arfsettings, $wp_version;
        
        if( version_compare( $wp_version, '5.5', '<' ) ){
            require_once ABSPATH . WPINC . '/class-phpmailer.php';
            require_once ABSPATH . WPINC . '/class-smtp.php';
            $mail = new PHPMailer();
        } else {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer();
        }

        if($enable_debug) {
            $mail->SMTPDebug = 4;
            ob_start();
        } else {
            $mail->SMTPDebug = 0;
        }
        if( $plain_text ){
            $mail->ContentType = 'text/plain';
        }
        $mail->CharSet = "UTF-8";
        if (isset($arfsettings->smtp_server) and $arfsettings->smtp_server == 'custom') {
            $mail->isSMTP();
            $mail->Host = $arfsettings->smtp_host;
            $mail->SMTPAuth = (isset($arfsettings->is_smtp_authentication) && $arfsettings->is_smtp_authentication == '1') ? true : false;
            $mail->Username = $arfsettings->smtp_username;
            $mail->Password = $arfsettings->smtp_password;
            if (isset($arfsettings->smtp_encryption) and $arfsettings->smtp_encryption != '' and $arfsettings->smtp_encryption != 'none') {
                $mail->SMTPSecure = $arfsettings->smtp_encryption;
            }
            if($arfsettings->smtp_encryption == 'none'){
                $mail->SMTPAutoTLS = false;
            }
            $mail->Port = $arfsettings->smtp_port;
        } else {
            $mail->isMail();
        }
        $mail->setFrom($reply_to, $reply_to_name);
        $mail->addAddress($recipient);
        if( is_array($cc_email) ){
            foreach($cc_email as $ccemail ){
                $mail->addCC($ccemail);
            }
        }else{
                $mail->addCC($cc_email);
        }

        if( is_array($bcc_email) ){
            foreach($bcc_email as $bccemail ){
                $mail->addBCC($bccemail);
            }
        }else{
                $mail->addBCC($bcc_email);
        }
        $mail->addReplyTo($user_nreplyto, $reply_to_name);
        if (isset($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);
            }
        }
        if( $plain_text ){
            $mail->isHTML(false);
        } else {
            $mail->isHTML(true);
        }
        $mail->Subject = $subject;
		
		
		$setlicval = 0;
        global $arf_get_version_val;
        global $arfmsgtounlicop;
        $setlicval = $arformcontroller->$arf_get_version_val();
			
		if($setlicval == 0) 
		{
		  $my_aff_code = "reputeinfosystems";
			
		  $message .='<div id="brand-div" class="brand-div top_container" style="margin-top:30px; font-size:12px !important; color: #444444 !important; display:block !important; visibility: visible !important;">' . addslashes(esc_html__('Powered by', 'ARForms')) . '&#32;';
		  
		  $message .='<a href="https://codecanyon.net/item/arforms-exclusive-wordpress-form-builder-plugin/6023165?ref=' . $my_aff_code . '" target="_blank" style="color:#0066cc !important; font-size:12px !important; display:inline !important; visibility:visible !important;">ARForms</a>';
			
           $message .= "\r\n". '<span style="color:#FF0000 !important; font-size:12px !important; display:block !important; visibility: visible !important;">' . addslashes(__('&nbsp;&nbsp;' . $arfmsgtounlicop, 'ARForms')) . '</span>';
		   
		   $message .='</div>'. "\r\n";
        }
        $mail->Body = $message;
        if (isset($arfsettings->smtp_server) and $arfsettings->smtp_server == 'custom') {
            if (!$mail->send()) {
                if($enable_debug){
                    echo '</pre><p style="color:red;">';
                    echo addslashes(esc_html__('The full debugging output is shown below:', 'ARForms'));
                    echo '</p><pre>';
                    var_dump($mail);
                    $smtp_debug_log = ob_get_clean();
                }
                if (!empty($use_only_smtp_settings)) {
                    echo json_encode(
                    array(
                        'success' => 'false',
                        'msg' => $mail->ErrorInfo.' <a href="#arf_smtp_error" data-toggle="arfmodal" >'.addslashes(esc_html__('Check Full Log','ARForms')).'</a>',
                        'log'=> '<div id="arf_smtp_error" style="display:none;" class="arfmodal arfhide arf_smpt_error"><div class="arfnewmodalclose" data-dismiss="arfmodal"><img src="'.ARFIMAGESURL.'/close-button.png" align="absmiddle"></div><p style="color:red;">'. addslashes(esc_html__('The SMTP debugging output is shown below:', 'ARForms')).'</p><pre>'.$smtp_debug_log.'</pre></div>'
                        )
                    );
                } else {
                    if (!empty($return_value)) {
                        return false;
                    }
                }
            } else {
                $smtp_debug_log = ob_get_clean();
                if (!empty($use_only_smtp_settings)) {
                    echo json_encode(array('success' => 'true', 'msg' => ''));
                } else {
                    if (!empty($return_value)) {
                        return true;
                    }
                }
            }
        }
	    else if( isset($arfsettings->smtp_server) && $arfsettings->smtp_server == 'phpmailer'){
	       if ($mail->send()) {
		          $return = true;
	       }
	   }
	   else{
            if (isset($arfsettings->smtp_server) and $arfsettings->smtp_server == 'custom') {
            }
            if (!wp_mail($recipient, $subject, $message, $header, $attachments)) {
                if (!$mail->send()) {
                    if (!empty($return_value)) {
                        return false;
                    }
                } else {
                    if (!empty($return_value)) {
                        return true;
                    }
                }
            } else {
                if (!empty($return_value)) {
                    return true;
                }
            }
        }

    }

    function arf_remove_entries_after_submit( $entry_id, $form_id ){

        if( '' == $entry_id || '' == $form_id ){
            return;
        }

        if( $form_id < 0 ){
            $form_id = abs( $form_id );
        }

        global $wpdb, $MdlDb;

        $form_select_new = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$MdlDb->forms."` WHERE `id` = %d AND `is_template` != %d AND `status` = %s", $form_id, 1,'published') );
        
        $form_options = maybe_unserialize($form_select_new->options );

        $arf_skip_storing_data_value = isset($form_options['arf_skip_store_data']) ? $form_options['arf_skip_store_data'] : '';
            
        $mapped_addon_arr = maybe_unserialize($form_select_new->arf_mapped_addon);
        $arf_activated_addon = '';
        
        $mapped_addon_arr = isset($mapped_addon_arr['arf_mapped_addon']) ? $mapped_addon_arr['arf_mapped_addon'] : array();
        $addon_array = array(
            array(
                'name' => 'arformsauthorizenet||authorize.net'
            ),
            array(
                'name' => 'arformsmollie||mollie'
            ),
            array(
                'name' => 'arformsmymail||mailster'
            ),
            array(
                'name' => 'arformspaypal||paypal'
            ),
            array(
                'name' => 'arformspaypalpro||paypal_pro'
            ),
            array(
                'name' => 'arformspdfcreator||pdfcreator'
            ),
            array(
                'name' => 'arformspostcreator||postcreator'
            ),
            array(
                'name' => 'arformsstripe||stripe'
            ),
            array(
                'name' => 'arformsusersignup||userregistration'
            ),
            array(
                'name' => 'arformspayfast||payfast'
            ),
            array(
                'name' => 'arformspagseguro||pagseguro'
            )
        );

        $addon_array = apply_filters( 'arf_modify_addon_list_outside_for_remove_entry', $addon_array );

        foreach ($addon_array as $key => $value) {
            $value['name'] = explode('||', $value['name']);
          
            $addon_plugin = $value['name'][0].'/'.$value['name'][0].'.php';
            if (is_plugin_active($addon_plugin) && in_array($value['name'][1], $mapped_addon_arr)) {  
                $arf_activated_addon .= $value['name'][0] . " ";
            }
        }
      
        /* 
         * reputelog - change the condition and delete entry when no add-on is mapped with the form. Currently, it's not removing the entry if add-on is only activated
         * but not mapped with the form
         */
        if( '' == $arf_activated_addon && 1 == $arf_skip_storing_data_value){               
            $res_dlt_value = $wpdb->query( $wpdb->prepare("DELETE FROM `" .$MdlDb->entry_metas ."` WHERE `entry_id` = %d", $entry_id) );

            $res_dlt = $wpdb->query( $wpdb->prepare("DELETE FROM `" .$MdlDb->entries ."` WHERE `form_id` = %d AND `id` = %d", $form_id,$entry_id) );
        }
    }

    function stop_standard_email() {
        return true;
    }

    function checksite($str) {
        update_option('wp_get_version', $str);
    }

    function entry_created($entry_id, $form_id) {
        if (defined('WP_IMPORTING')){
            return;
        }
        if( $form_id < 0 ){
            $form_id = abs( $form_id );
        }
        $_SESSION['arf_payment_check_form_id'] = $form_id;
        global $arfform, $db_record, $arfrecordmeta, $style_settings, $armainhelper, $arfieldhelper, $arnotifymodel,$arformcontroller, $arffield, $wpdb, $MdlDb, $arf_matrix, $arrecordcontroller;
        /* arf_dev_flag if entry prevented from user signup plugin*/
        if(!isset($form_id)){
            return;
        }
        $form = $arfform->getOne($form_id);
        $form_options = maybe_unserialize($form->options);
        $entry = $db_record->getOne($entry_id, true);

        if (!isset($form->options['chk_admin_notification']) or ! $form->options['chk_admin_notification'] or ! isset($form->options['ar_admin_email_message']) or $form->options['ar_admin_email_message'] == '') {
            return;
        }

        $form->options['ar_admin_email_message'] = wp_specialchars_decode($form->options['ar_admin_email_message'], ENT_QUOTES);
        $field_order = json_decode($form->options['arf_field_order'],true);
        $inner_field_order = json_decode( $form->options['arf_inner_field_order'],true);
        $to_email = $form_options['email_to'];
        $to_email = preg_replace('/\[(.*?)\]/', ',$0,', $to_email);
        $shortcodes = $armainhelper->get_shortcodes($to_email, $form_id);
        $mail_new = $arfieldhelper->replace_shortcodes($to_email, $entry, $shortcodes);
        $mail_new = $arfieldhelper->arf_replace_shortcodes($mail_new, $entry, true);
        $to_mail = $mail_new;
        $to_email = trim($to_mail, ',');
        
        $cc_email =$form_options['admin_cc_email'];
        $bcc_email =$form_options['admin_bcc_email'];

        $to_email = str_replace(',,', ',', $to_email);
        $email_fields = (isset($form_options['also_email_to'])) ? (array) $form_options['also_email_to'] : array();
        $entry_ids = array($entry->id);
        $exclude_fields = array();
        foreach ($email_fields as $key => $email_field) {
            $email_fields[$key] = (int) $email_field;
            if (preg_match('/|/', $email_field)) {
                $email_opt = explode('|', $email_field);
                if (isset($email_opt[1])) {
                    if (isset($entry->metas[$email_opt[0]])) {
                        $add_id = $entry->metas[$email_opt[0]];
                        $add_id = maybe_unserialize($add_id);
                        if (is_array($add_id)) {
                            foreach ($add_id as $add) {
                                $entry_ids[] = $add;
                            }
                        }
                        else {
                            $entry_ids[] = $add_id;
                        }
                    }
                    $exclude_fields[] = $email_opt[0];
                    $email_fields[$key] = (int) $email_opt[1];
                }
                unset($email_opt);
            }
        }
        if ($to_email == '' and empty($email_fields)) {
            return;
        }

        foreach ($email_fields as $email_field) {
            if (isset($form_options['reply_to_name']) and preg_match('/|/', $email_field)) {
                $email_opt = explode('|', $form_options['reply_to_name']);
                if (isset($email_opt[1])) {
                    if (isset($entry->metas[$email_opt[0]])) {
                        $entry_ids[] = $entry->metas[$email_opt[0]];
                    }
                    $exclude_fields[] = $email_opt[0];
                }
                unset($email_opt);
            }
        }
        $where = '';

        if (!empty($exclude_fields)) {
            $where = " and it.field_id not in (" . implode(',', $exclude_fields) . ")";
        }

        $new_form_cols = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")" . $where, " ORDER BY fi.id");
        
        global $wpdb, $MdlDb;
        $repeater_field_arr = array();
        $repeater_fields = $wpdb->get_results($wpdb->prepare("SELECT id,type,field_key,required,form_id,name,field_options,created_date FROM " .$MdlDb->fields." WHERE type=%s AND form_id = %d", 'arf_repeater', $form_id), ARRAY_A);
        
        foreach ($repeater_fields as $key => $value) {
            array_push($repeater_field_arr, (int) $value['id']);
        }

        $section_field_arr = array();
        $section_fields = $wpdb->get_results( $wpdb->prepare("SELECT id,type,field_key,required,form_id,name,field_options,created_date FROM " .$MdlDb->fields." WHERE type=%s AND form_id = %d", 'section', $form_id), ARRAY_A );

        foreach( $section_fields as $key => $value ){
        	array_push( $section_field_arr, (int) $value['id'] );
        }
        
        $values = array();
        asort($field_order);
        
        $hidden_fields = array();
        $hidden_field_ids = array();

        $allfields = $wpdb->get_results($wpdb->prepare("SELECT id,type FROM " .$MdlDb->fields." WHERE form_id = %d order by id", $form_id), ARRAY_A);
        
        $new_form_cols_arr = $arformcontroller->arfObjtoArray( $new_form_cols );
        $allfieldarray = array();
        if ($allfields) {
            foreach ($allfields as $tmpfield){
                $allfieldarray[] = $tmpfield['id'];
                if( 'html' == $tmpfield['type'] ){
                    $temp_fid = $arformcontroller->arfSearchArray($tmpfield['id'], 'field_id', $new_form_cols_arr );
                    $get_field = $arffield->getOne( $tmpfield['id'] );
                    if( '' === trim($temp_fid) ){
                        if( !empty( $get_field ) ){
                            $temp_obj = array(
                                'id' => '-'.$tmpfield['id'],
                                'entry_value' => $get_field->field_options['description'],
                                'field_id' => $tmpfield['id'],
                                'entry_id' => $entry_id,
                                'field_type' => $get_field->type,
                                'field_key' => $get_field->field_key,
                                'required' => $get_field->required,
                                'field_form_id' => $get_field->form_id,
                                'field_name' => $get_field->name,
                                'fi_options' => $get_field->field_options,
                            );

                            $tmp_obj = json_decode( json_encode( $temp_obj ) );

                            array_push( $new_form_cols, $tmp_obj );
                        }
                    } else {
                        if( !empty( $new_form_cols[$temp_fid] ) && empty( $new_form_cols[$temp_fid]->fi_options ) ){
                            $new_form_cols[$temp_fid]->fi_options = $get_field->field_options;
                        }
                    }
                }
            }
        }


        foreach ($field_order as $field_id => $order) {
            if(is_int($field_id)) {
                foreach ($new_form_cols as $field) {
                    if ($field_id == $field->field_id) {
                        $values[] = $field;
                    } else if( $field->field_type == 'hidden' ) {
                        if( !in_array($field->field_id,$hidden_field_ids) ) {
                            $hidden_fields[] = $field;
                            $hidden_field_ids[] = $field->field_id;
                        }
                    }
                }
                
                if(in_array($field_id, $repeater_field_arr)) {
                    $index = array_search($field_id, $repeater_field_arr, true);
                    $arr = array();
                    $arr['field_id'] = $repeater_fields[$index]['id'];
                    $arr['entry_value'] = "";
                    $arr['entry_id'] = $entry_id;
                    $arr['created_date'] = $repeater_fields[$index]['created_date'];
                    $arr['field_type'] = $repeater_fields[$index]['type'];
                    $arr['field_key'] = $repeater_fields[$index]['field_key'];
                    $arr['required'] = $repeater_fields[$index]['required'];
                    $arr['field_form_id'] = $repeater_fields[$index]['form_id'];
                    $arr['field_name'] = $repeater_fields[$index]['name'];
                    $arr['fi_options'] = $repeater_fields[$index]['field_options'];
                    $values[] = (object)$arr;
                }

                if( in_array( $field_id, $section_field_arr ) ){
                	asort($inner_field_order);
                    if( isset( $inner_field_order[$field_id] ) ){
                    	foreach( $inner_field_order[$field_id] as $key => $inner_field ){
                    		$exploded_data = explode('|', $inner_field);
                    		$in_field_id = $exploded_data[0];
    	                	foreach( $new_form_cols as $field ){
    	                		if( $field->field_id == $in_field_id ){
    	                			$values[] = $field;
    	                		}
    	                	}
                        }
                	}
                }
            }
        }
        
        if( count($hidden_fields) > 0 ){
            $values = array_merge($values,$hidden_fields);
        }

        if ($allfieldarray && $values) {
            foreach ($values as $fieldkey => $tmpfield) {
                if (!in_array($tmpfield->field_id, $allfieldarray)){
                    unset($values[$fieldkey]);
                }
            }
        }

        $to_emails = array();
        if ($to_email)
            $to_emails = explode(',', $to_email);
        foreach ($to_emails as $key => $emails) {
            if (preg_match('/(.*?)\((.*?)\)/', $emails)) {
                $validate_email = preg_replace('/(.*?)\((.*?)\)/', '$2', $emails);
                if (filter_var($validate_email, FILTER_VALIDATE_EMAIL)) {
                    $to_emails[$key] = $validate_email;
                }
            }
        }

        $cc_emails = explode(',', $cc_email);
        $bcc_emails = explode(',', $bcc_email);

        $plain_text = (isset($form_options['plain_text']) and $form_options['plain_text']) ? true : false;
        $custom_message = false;
        $get_default = true;
        $mail_body = '';
        if (isset($form_options['ar_admin_email_message']) and trim($form_options['ar_admin_email_message']) != '') {
            if (!preg_match('/\[ARF_form_all_values\]/', $form_options['ar_admin_email_message'])){
                $get_default = false;
            }
            $custom_message = true;
            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_admin_email_message'], $entry->form_id);
            $mail_body = $arfieldhelper->replace_shortcodes($form_options['ar_admin_email_message'], $entry, $shortcodes);
        }

        if ($get_default){
            $default = '';
        }
        if ($get_default and ! $plain_text) {
            $default .= "<table cellspacing='0' style='font-size:12px;line-height:135%; border-bottom:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color};'><tbody>";
            $bg_color = " style='background-color:#{$style_settings->bg_color};'";
            $bg_color_alt = " style='background-color:#{$style_settings->arfbgactivecolorsetting};'";
        }
        $reply_to_name = $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $odd = true;
        $attachments = array();

        foreach ($values as $value) {
            $value = apply_filters('arf_brfore_send_mail_chnage_value', $value, $entry_id, $form_id);
            if ($value->field_type == 'file') {
                global $MdlDb, $wpdb;
                $file_options = $MdlDb->get_var($MdlDb->fields, array('id' => $value->field_id), 'field_options');
                $file_options = json_decode($file_options);
                if (isset($file_options->attach) && $file_options->attach == 1) {
					$attach_file_values = explode('|', $value->entry_value);
					foreach ($attach_file_values as $attach_file_val) {
						$field_id = $wpdb->get_row($wpdb->prepare("select * from " . $wpdb->prefix . "postmeta where post_id = '%d' AND meta_key = '_wp_attached_file'",$attach_file_val));

                        $file = $field_id->meta_value;  
                        $file = str_replace('thumbs/', '', $file);

                        if (preg_match( '/http/', $file )) {
                            $home_url = get_home_url();
                            $file = str_replace($home_url, "", $file);
                            $file = ltrim($file , '/');
                            $attachments[] = ABSPATH . $file;
                        } else{
                            $attachments[] = ABSPATH . "/$file";
                        } 
                    }
				}
            }

            $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);
            if ($value->field_type == 'file') {
				$icon_file_values = explode('|', $value->entry_value);
                if ( !empty($icon_file_values) && count($icon_file_values) > 1 ) {
    				foreach ($icon_file_values as $icon_file_val){
    					$icon_val = apply_filters('arfemailvalue', maybe_unserialize($icon_file_val), $value, $entry);
    					if (isset($icon_val) and $icon_val != ''){
    						if( is_numeric( $icon_val )){
    							$icon_val = $icon_val;
    						}else{
    							$icon_val = $icon_file_val;
    						}
    						$get_file_field_id = $wpdb->get_row("select * from " . $wpdb->prefix . "postmeta where post_id = '" . $icon_file_val . "' AND meta_key = '_wp_attached_file'");
    						$file = $get_file_field_id->meta_value;

                            $sep = '';
                            if (!empty($val)) {
                                $sep = '<br>';
                            }
                            $val .= $sep;
    						if (file_exists(ABSPATH . $file)){
    							$full_path = site_url() . "/" . str_replace('thumbs/', '', $file);
    							$val .= "<a href='" . $full_path . "' target='_blank'><img src='" . $full_path . "' /></a>";
    						} else {
    							$val .= $arfieldhelper->get_file_name_link($icon_val, false);
    						}
    					}
                    }
                }
            }
            if ($value->field_type == 'select' || $value->field_type == 'arf_multiselect' || $value->field_type == 'checkbox' || $value->field_type == 'radio' || $value->field_type == 'arf_autocomplete') {
                global $wpdb,$MdlDb;
                $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " .$MdlDb->entry_metas." WHERE field_id='%d' AND entry_id='%d'", "-" . $value->field_id, $entry->id));
                if ($field_opts) {
                    $field_opts = maybe_unserialize($field_opts->entry_value);
                    if ($value->field_type == 'checkbox' || $value->field_type == 'arf_multiselect') {
                        if ($field_opts && count($field_opts) > 0) {
                            $temp_value = "";
                            foreach ($field_opts as $new_field_opt) {
                                $temp_value .= $new_field_opt['label'] . " (" . $new_field_opt['value'] . "), ";
                            }
                            $temp_value = trim($temp_value);
                            $val = rtrim($temp_value, ",");
                        }
                    } else {
                        global $wpdb,$MdlDb;
                            $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                    }
                }
            }
            if ($value->field_type == 'textarea' and ! $plain_text)
                $val = str_replace(array("\r\n", "\r", "\n"), ' <br/>', $val);
            if (is_array($val)){
                $val = implode(', ', $val);
            }

            if ($value->field_type == 'html') {
                
                $html_field_opts = arf_json_decode( json_encode( $value->fi_options ), true );
                if( !empty( $html_field_opts ) ){
                    if ( $html_field_opts['enable_total'] == 1 ) {
                        $html_desc = $html_field_opts['description'];
                        $regex = '/<arftotal>(.*?)<\/arftotal>/is';
                        $val = preg_replace($regex, $val, $html_desc);
                    }
                }

            }


            if($value->field_type == 'arf_repeater') {

                $get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT a.*, b.* FROM `".$MdlDb->fields."` a LEFT JOIN ".$MdlDb->entry_metas." b ON b.field_id = a.id WHERE ( field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%' ) AND entry_id=%d GROUP BY a.id", $value->field_id, $value->field_id, $entry->id ), ARRAY_A );
                
                if(!empty($get_all_inner_fields)) {
                    $valign = "";
                    $inner_field_content = "";
                    $row_style = "valign='top' style='text-align:left;color:#{$style_settings->text_color};padding:7px 9px;border-top:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color}'";
                    if( count( $get_all_inner_fields ) == 1 ){
                        foreach ($get_all_inner_fields as $key => $inner_fields) {
                            if(!empty($inner_fields['entry_value']) && strpos($inner_fields['entry_value'],"[ARF_JOIN]") != -1) {
                                $valign = 'valign="top"';
                                $entry_val = !empty($inner_fields['entry_value']) ? explode("[ARF_JOIN]", $inner_fields['entry_value']) : array();  
                                $row_span = count($entry_val);
                                $cnt = 0;
                                $has_separate_value = false;
                                if( in_array( $inner_fields['type'], array('checkbox', 'radio', 'select', 'arf_autocomplete', 'arf_multiselect') ) ){
                                    $field_opts = json_decode( $inner_fields['field_options'], true );
                                    if( isset( $field_opts['separate_value'] ) && '1' == $field_opts['separate_value'] ){
                                        $has_separate_value = true;
                                    }
                                }

                                foreach ($entry_val as $key2 => $in_value) {
                                    $inner_field_content .= "<tr>";
                                    if( preg_match('/\!\|\!/', $in_value) ){
                                        $value_exp = explode( '!|!', $in_value );
                                        $temp_val = '';
                                        $value_arr = maybe_unserialize( $value_exp[0] );

                                        if( $has_separate_value ){
                                            $sep_value = '';
                                            $fopts = json_decode( $inner_fields['options'], true );

                                            foreach( $value_arr as $value_arrk => $varrv ){
                                                $fopt_key = $arformcontroller->arfSearchArray($varrv, 'value', $fopts);
                                                $sep_value .= $fopts[$fopt_key]['label'] . ' ('.$fopts[$fopt_key]['value'].'),';
                                            }
                                            $in_value = rtrim( $sep_value, ',');
                                        } else {
                                            if( is_array( $value_arr ) ){
                                                $temp_val = implode( ',',$value_arr );
                                            } else {
                                                $temp_val = $value_arr;
                                            }
                                            $in_value = $temp_val;
                                        }
                                    }
                                    if( $inner_fields['type'] == 'date'){
                                        $inner_field_opts = arf_json_decode( $inner_fields['field_options'] );
                                        $in_value = $arfieldhelper->get_date_entry( $in_value,$inner_fields['form_id'],$inner_field_opts->show_time_calendar, $inner_field_opts->clock,$inner_field_opts->locale, 'arformspdfcreator'  );
                                    }
                                    if($cnt==0) {
                                        $inner_field_content .= "<td rowspan='".$row_span."' valign='top'>".$inner_fields['name']."</td><td>".$in_value."</td>";
                                    } else {
                                        $inner_field_content .= "<td>".$in_value."</td>";
                                    }                                    
                                    $cnt++;
                                    $inner_field_content .= "</tr>";
                                }
                                
                            } else {
                                if( preg_match('/\!\|\!/', $inner_fields['entry_value']) ){
                                    $value_exp = explode( '!|!', $inner_fields['entry_value'] );
                                    $temp_val = '';
                                    $value_arr = maybe_unserialize( $value_exp[0] );
                                    $temp_val = implode( ',',$value_arr );
                                    $inner_fields['entry_value'] = $temp_val;
                                }

                                if( $inner_fields['type'] == 'date'){
                                    $inner_field_opts = arf_json_decode( $inner_fields['field_options'] );
                                    $inner_fields['entry_value'] = $arfieldhelper->get_date_entry( $inner_fields['entry_value'],$inner_fields['form_id'],$inner_field_opts->show_time_calendar, $inner_field_opts->clock,$inner_field_opts->locale, 'arformspdfcreator'  );
                                }
                                $inner_field_content .= "<tr>";
                                    $inner_field_content .= "<td rowspan='".$row_span."'>".$inner_fields['name']."</td><td>".$inner_fields['entry_value']."</td>";
                                $inner_field_content .= "</tr>";
                            }
                        } 
                        if( !isset($bg_color) ){
                            $bg_color = " style='background-color:#{$style_settings->bg_color};'";
                        }
                        $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th ".$valign." $row_style>$value->field_name</th><td $row_style><table class='arf_repeater_table' border='1' cellpadding='10' cellspacing='0' style='border-color:#ccc'><tbody>";
                        $default .= $inner_field_content;
                        $default .= "</tbody></table></td></tr>";
                    } else if( count( $get_all_inner_fields ) > 1 ){
                        $inner_field_content .= "<table class='arf_repeater_table' border='1' cellpadding='10' cellspacing='0' style='border-color:#ccc'>";

                                $fields_arr = [];
                               
                                foreach( $get_all_inner_fields as $key => $inner_fields ){
                                    
                                    if($inner_fields['type']=='like' && $inner_fields['entry_value']===0) {
                                        $inner_fields['entry_value'] = "0";
                                    }
                                    if(''!=$inner_fields['entry_value'] && strpos($inner_fields['entry_value'],"[ARF_JOIN]") != -1) {
                                        $entry_val = (''!=$inner_fields['entry_value']) ? explode("[ARF_JOIN]", $inner_fields['entry_value']) : array(); 
                                        $has_separate_value = false;
                                        if( in_array( $inner_fields['type'], array('checkbox', 'radio', 'select', 'arf_autocomplete', 'arf_multiselect') ) ){
                                            $field_opts = json_decode( $inner_fields['field_options'], true );
                                            if( isset( $field_opts['separate_value'] ) && '1' == $field_opts['separate_value'] ){
                                                $has_separate_value = true;
                                            }
                                        }
                                        $cnt = 0;
                                        foreach ($entry_val as $key2 => $in_value) {
                                            if( preg_match('/\!\|\!/', $in_value) ){
                                                $value_exp = explode( '!|!', $in_value );
                                                $temp_val = '';
                                                $value_arr = maybe_unserialize( $value_exp[0] );

                                                if( $has_separate_value ){
                                                    $sep_value = '';
                                                    $fopts = json_decode( $inner_fields['options'], true );

                                                    foreach( $value_arr as $value_arrk => $varrv ){
                                                        $fopt_key = $arformcontroller->arfSearchArray($varrv, 'value', $fopts);
                                                        $sep_value .= $fopts[$fopt_key]['label'] . ' ('.$fopts[$fopt_key]['value'].'),';
                                                    }
                                                    $in_value = rtrim( $sep_value, ',');
                                                } else {
                                                    if( is_array( $value_arr ) ){
                                                        $temp_val = implode( ',',$value_arr );
                                                    } else {
                                                        $temp_val = $value_arr;
                                                    }
                                                    $in_value = $temp_val;
                                                }
                                            }
                                            if( $inner_fields['type'] == 'date'){
                                                $inner_field_opts = arf_json_decode( $inner_fields['field_options'] );
                                                $in_value = $arfieldhelper->get_date_entry( $in_value,$inner_fields['form_id'],$inner_field_opts->show_time_calendar, $inner_field_opts->clock,$inner_field_opts->locale, 'arformspdfcreator'  );
                                            }
                                            $fields_arr[$cnt][$inner_fields['field_id']] = $in_value;
                                           
                                            $cnt++;
                                        }
                                    } else{
                                        if( preg_match('/\!\|\!/', $inner_fields['entry_value']) ){
                                            $value_exp = explode( '!|!', $inner_fields['entry_value'] );
                                            $temp_val = '';
                                            $value_arr = maybe_unserialize( $value_exp[0] );
                                            $temp_val = implode( ',',$value_arr );
                                            $inner_fields['entry_value'] = $temp_val;
                                        }
                                        if( $inner_fields['type'] == 'date'){
                                            $inner_field_opts = arf_json_decode( $inner_fields['field_options'] );
                                            $inner_fields['entry_value'] = $arfieldhelper->get_date_entry( $inner_fields['entry_value'],$inner_fields['form_id'],$inner_field_opts->show_time_calendar, $inner_field_opts->clock,$inner_field_opts->locale, 'arformspdfcreator'  );
                                        }
                                    }

                                }
                            $inner_field_content .= "<tbody>";
                            $i = 1;
                            foreach( $fields_arr as $k => $frr ){
                                foreach( $get_all_inner_fields as $inner_fields ){
                                    $inner_field_content .= "<tr>";
                                    $inner_field_content .= "<td style='width:35%'>" . $inner_fields['name'] ." [".$i."] </td>";
                                    $inner_field_content .= "<td>" . $frr[$inner_fields['field_id']] . "</td>";
                                    $inner_field_content .= "</tr>";
                                }
                                $i++;
                            }
                            $inner_field_content .= "</tbody>";
                        $inner_field_content .= "</table>";

                        if( !isset($bg_color) ){
                            $bg_color = " style='background-color:#{$style_settings->bg_color};'";
                        }
                        $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th valign='top' $row_style>$value->field_name</th>";
                        $default .= "<td $row_style>";
                        $default .= $inner_field_content;
                        $default .= "</td></tr>";
                    }
                }
                $odd = ($odd) ? false : true;
            } else {
                if ( $value->field_type == 'html') {
                    $html_field_opts = arf_json_decode( json_encode( $value->fi_options ), true );
                    if( !empty( $html_field_opts ) ){
                        if ( $html_field_opts['enable_total'] == 1 ) {
                            $val = $value->entry_value;        
                        }
                    }

                    if ( $html_field_opts['enable_total'] == 1 ) {

                        if ($get_default and $plain_text) {
                            $default .= $value->field_name . ': ' . $val . "\r\n\r\n";
                        } else if ($get_default) {
                            $row_style = "valign='top' style='text-align:left;color:#{$style_settings->text_color};padding:7px 9px;border-top:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color}'";
                            $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th $row_style>$value->field_name</th><td $row_style>$val</td></tr>";
                            $odd = ($odd) ? false : true;
                        }
                    }
                } else {
                    if ($get_default and $plain_text) {
                            $default .= $value->field_name . ': ' . $val . "\r\n\r\n";
                    } else if ($get_default) {
                        $row_style = "valign='top' style='text-align:left;color:#{$style_settings->text_color};padding:7px 9px;border-top:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color}'";
                        $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th $row_style>$value->field_name</th><td $row_style>$val</td></tr>";
                        $odd = ($odd) ? false : true;
                    }
                }
            }
            $reply_to_name = (isset($form_options['ar_admin_from_name'])) ? $form_options['ar_admin_from_name'] : $arfsettings->reply_to_name;
            $reply_to_id = (isset($form_options['ar_admin_from_email'])) ? $form_options['ar_admin_from_email'] : $arfsettings->reply_to;
            if (isset($reply_to_id)){
                $reply_to = isset($entry->metas[$reply_to_id]) ? $entry->metas[$reply_to_id] : '';
            }
            if ($reply_to == '')
                $reply_to = $reply_to_id;
            if (in_array($value->field_id, $email_fields)) {
                $val = explode(',', $val);
                if (is_array($val)) {
                    foreach ($val as $v) {
                        $v = trim($v);
                        if (is_email($v))
                            $to_emails[] = $v;
                    }
                }else if (is_email($val))
                    $to_emails[] = $val;
            }
        }
        

        if( !isset($reply_to) || $reply_to == '' ){
            $reply_to = (isset($form_options['ar_admin_from_email'])) ? $form_options['ar_admin_from_email'] : $arfsettings->reply_to;
        }


        $attachments = apply_filters('arfnotificationattachment', $attachments, $form, array('entry' => $entry));
        global $arfsettings;

        if ( !empty($html_val) && !empty($html_label) ) {
            $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th $row_style>$html_label</th><td $row_style>$html_val</td></tr>";
        }

        if ($get_default and ! $plain_text){
            $default .= "</tbody></table>";
        }


        if (!isset($arfblogname) || $arfblogname == ''){
            $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        }

        
        if (isset($form_options['admin_email_subject']) and $form_options['admin_email_subject'] != '') {
            $subject = $form_options['admin_email_subject'];
            $subject = str_replace('[form_name]', stripslashes($form->name), $subject);
            $subject = str_replace('[site_name]', $arfblogname, $subject);
        } else {
            $subject = stripslashes($form->name) . ' ' . addslashes(esc_html__('Form submitted on', 'ARForms')) . ' ' . $arfblogname;
        }


        $subject = trim($subject);
        if (isset($reply_to) and $reply_to != '') {
            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_admin_from_email'], $entry->form_id);
            $reply_to = $arfieldhelper->replace_shortcodes($form_options['ar_admin_from_email'], $entry, $shortcodes);
            $reply_to = trim($reply_to);
            $reply_to = $arfieldhelper->arf_replace_shortcodes($reply_to, $entry);
        }
        foreach( $cc_emails as $ck => $cc_email ){
            if (isset($cc_email) and $cc_email != '') {
              
                $shortcodes = $armainhelper->get_shortcodes($cc_email, $entry->form_id);
                $cc_email = $arfieldhelper->replace_shortcodes($cc_email, $entry, $shortcodes);
                $cc_email = trim($cc_email);
                $cc_email = $arfieldhelper->arf_replace_shortcodes($cc_email, $entry);
                $cc_emails[$ck] = $cc_email;
            }
        }
      
        foreach( $bcc_emails as $bck => $bcc_email ){
            if (isset($bcc_email) and $bcc_email != '') {
                $shortcodes = $armainhelper->get_shortcodes($bcc_email, $entry->form_id);
                $bcc_email = $arfieldhelper->replace_shortcodes($bcc_email, $entry, $shortcodes);
                $bcc_email = trim($bcc_email);
                $bcc_email = $arfieldhelper->arf_replace_shortcodes($bcc_email, $entry);
                $bcc_emails[$bck] = $bcc_email;
            }
        }

        $admin_nreplyto = (isset($form_options['ar_admin_reply_to_email'])) ? $form_options['ar_admin_reply_to_email'] : $arfsettings->reply_to_email;

        if (isset($admin_nreplyto) and $admin_nreplyto != '') {
            $shortcodes = $armainhelper->get_shortcodes($admin_nreplyto, $entry->form_id);
            $admin_nreplyto = $arfieldhelper->replace_shortcodes($admin_nreplyto, $entry, $shortcodes);
            $admin_nreplyto = trim($admin_nreplyto);
            $admin_nreplyto = $arfieldhelper->arf_replace_shortcodes($admin_nreplyto, $entry);
        }

        
        if ($get_default and $custom_message) {
            $mail_body = str_replace('[ARF_form_all_values]', $default, $mail_body);
        } else if ($get_default) {
            $mail_body = $default;
        }
        $shortcodes = $armainhelper->get_shortcodes($mail_body, $entry->form_id);
        $mail_body = $arfieldhelper->replace_shortcodes($mail_body, $entry, $shortcodes);
        $mail_body = $arfieldhelper->arf_replace_shortcodes($mail_body, $entry,true);

        /*change added for admin side email notification*/        
        $tagregexp = '';
        $pattern = "/\{(if )?($tagregexp)(.*?)(?:(\/))?\}(?:(.+?)\{\/\2\})?/s";

        $pattern = '/\{field_id\:(\d+)\}/s';

        preg_match_all( $pattern, $mail_body, $matches);
        if( !empty( $matches[0] ) ){
            foreach( $matches[0] as $shortcode ){
                $field_data = str_replace('{', '', $shortcode );
                $field_data = str_replace('}', '', $field_data);
                $splited_data = explode( ':', $field_data );
                $field = $arffield->getOne( end( $splited_data ) );

                $get_field_value = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " .$MdlDb->entry_metas." WHERE field_id='%d' AND entry_id='%d'", $field->id, $entry_id));
                if( !isset( $get_field_value->entry_value ) ){
                    $get_field_value->entry_value = '';
                }
                if ( 'file' == $field->type ) {
                    $old_value = explode('|', $get_field_value->entry_value);
                    
                    foreach ($old_value as $val) {

                        $url = wp_get_attachment_url($val);

                        if( !empty( $url ) ){
                            $get_field_value->entry_value = $url;
                        }
                    }
                }
                if ($field->type == 'select' || $field->type == 'checkbox' || $field->type == 'radio' || $field->type == 'arf_autocomplete' || $field->type == 'arf_multiselect') {

                    $field_entry_value = array();

                    $field_entry_value = maybe_unserialize( $get_field_value->entry_value );

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

                    $get_field_value->entry_value = $value;
                }
                if ( 'matrix' == $field->type ){
                    global $arffield;

                        $field_value = maybe_unserialize( $get_field_value->entry_value );

                        $field_id = $get_field_value->field_id;

                        $field_obj = $field;

                        $value_for_mail  = '<table cellpadding="5" cellspacing="0" border="1">';

                            $value_for_mail .= '<tbody>';


                                if( !empty( $field_obj->rows ) ){

                                    $total_rows = count( $field_obj->rows );

                                    $rx = 0;
                                    foreach( $field_obj->rows as $frows ){

                                        $value_for_mail .= '<tr>';

                                            $value_for_mail .= '<td>';
                                            $value_for_mail .= $frows;
                                            $value_for_mail .= '</td>';
                                            $value_for_mail .= '<td>';
                                            if( isset( $field_obj->field_options['separate_value'] ) && 1 == $field_obj->field_options['separate_value'] ){
                                                foreach( $field_obj->field_options['options'] as $matrix_opts ){
                                                    if( isset( $field_value[$rx] ) && $matrix_opts['value'] == $field_value[$rx] ){
                                                        $value_for_mail .= $matrix_opts['label'] . ' ('.$field_value[$rx].')';
                                                    }
                                                }
                                            } else {
                                                $value_for_mail .= ( !empty( $field_value[$rx] ) ? $field_value[$rx] : '-' );
                                            }

                                            $value_for_mail .= '</td>';

                                        $value_for_mail .= '</tr>';

                                        $rx++;
                                    }
                                }

                            $value_for_mail .= '</tbody>';

                        $value_for_mail .= '</table>';

                        $get_field_value->entry_value = $value_for_mail;
                }

                $mail_body = str_replace( $shortcode, $get_field_value->entry_value, $mail_body );
            }
        }
        /*change over*/

        $data = maybe_unserialize($entry->description);
        $browser_info = $arrecordcontroller->getBrowser($data['browser']);
        
        $browser_detail = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
        if (preg_match('/\[ARF_form_ipaddress\]/', $mail_body)){
            $mail_body = str_replace('[ARF_form_ipaddress]', $entry->ip_address, $mail_body);
        }
        if (preg_match('/\[ARF_form_browsername\]/', $mail_body)){
            $mail_body = str_replace('[ARF_form_browsername]', $browser_detail, $mail_body);
        }
        if (preg_match('/\[ARF_form_referer\]/', $mail_body)){
            $mail_body = str_replace('[ARF_form_referer]', $data['http_referrer'], $mail_body);
        }
        if (preg_match('/\[ARF_form_entryid\]/', $mail_body)){
            $mail_body = str_replace('[ARF_form_entryid]', $entry->id, $mail_body);
        }
        if (preg_match('/\[ARF_form_entrykey\]/', $mail_body)){
            $mail_body = str_replace('[ARF_form_entrykey]', $entry->entry_key, $mail_body);
        }
        if (preg_match('/\[ARF_form_added_date_time\]/', $mail_body)) {
            $wp_date_format = get_option('date_format');
            $wp_time_format = get_option('time_format');
            $mail_body = str_replace('[ARF_form_added_date_time]', date($wp_date_format . " " . $wp_time_format, strtotime($entry->created_date)), $mail_body);
        }
        $arf_current_user = wp_get_current_user();
        if (preg_match('/\[ARF_current_userid\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_userid]', $arf_current_user->ID, $mail_body);
        }
        if (preg_match('/\[ARF_current_username\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_username]', $arf_current_user->user_login, $mail_body);
        }
        if (preg_match('/\[ARF_current_useremail\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_useremail]', $arf_current_user->user_email, $mail_body);
        }
        if (preg_match('/\[ARF_page_url\]/', $mail_body)) {
            $entry_desc = maybe_unserialize($entry->description);
            $mail_body = str_replace('[ARF_page_url]', $entry_desc['page_url'], $mail_body);   
        }
        if(preg_match('/\[form_name\]/', $mail_body)){
            $mail_body = str_replace('[form_name]', $form->name, $mail_body); 
        }
        if(preg_match('/\[site_name\]/', $mail_body)){
            $mail_body = str_replace('[site_name]', $arfblogname, $mail_body);
        }
        if(preg_match('/\[site_url\]/', $mail_body)){
            $mail_body = str_replace('[site_url]', ARF_HOME_URL, $mail_body); 
        }

        $subject_n = $armainhelper->get_shortcodes($subject, $entry->form_id);
        $subject_n = $arfieldhelper->replace_shortcodes($subject, $entry, $subject_n);
        $subject_n = $arfieldhelper->arf_replace_shortcodes($subject_n, $entry, true);
        $subject = $subject_n;
        $reply_to_name_n = $armainhelper->get_shortcodes($reply_to_name, $entry->form_id);
        $reply_to_name_n = $arfieldhelper->replace_shortcodes($reply_to_name, $entry, $reply_to_name_n);
        $reply_to_name_n = $arfieldhelper->arf_replace_shortcodes($reply_to_name_n, $entry, true);
        $reply_to_name = $reply_to_name_n;
        $mail_body = apply_filters('arfbefore_admin_send_mail_body', $mail_body, $entry_id, $form_id);
        $mail_body = nl2br($mail_body);
        $to_emails = apply_filters('arftoemail', $to_emails, $values, $form_id);
        $_SESSION['arf_admin_emails'] = (array) $to_emails;
        $_SESSION['arf_admin_subject'] = $subject;
        $_SESSION['arf_admin_mail_body'] = $mail_body;
        $_SESSION['arf_admin_reply_to'] = $reply_to;
        $_SESSION['arf_admin_reply_to_email'] = $admin_nreplyto;
        $_SESSION['arf_admin_reply_to_name'] = $reply_to_name;
        $_SESSION['arf_admin_plain_text'] = $plain_text;
        $_SESSION['arf_admin_attachments'] = $attachments;

        $admin_email_notification_data = array(
            'arf_admin_emails' => (array) $to_emails,
            'arf_admin_subject' => $subject,
            'arf_admin_mail_body' => $mail_body,
            'arf_admin_reply_to' => $reply_to,
            'arf_admin_reply_to_email' => $admin_nreplyto,
            'arf_admin_reply_to_name' => $reply_to_name,
            'arf_admin_plain_text' => $plain_text,
            'arf_admin_attachments' => $attachments,
            'arf_admin_cc_emails' => $cc_emails,
            'arf_admin_bcc_emails' => $bcc_emails
        );

        do_action( 'arf_update_admin_email_notification_data_outside', $admin_email_notification_data, $entry_id, $form_id );

        foreach ((array) $to_emails as $to_email) {
            $to_email = apply_filters('arfcontent', $to_email, $form, $entry_id);
        	/* reputelog - check for last 2 arguments*/
            $arnotifymodel->send_notification_email_user(trim($to_email), $subject, $mail_body, $reply_to, $reply_to_name, $plain_text, $attachments, false, false, false, false, $admin_nreplyto,$cc_emails,$bcc_emails, $entry_id);
        }

        return $to_emails;
    }

    function sitename() {
        return get_bloginfo('name');
    }

    function entry_updated($entry_id, $form_id) {

        

        global $arfform;


        $form = $arfform->getOne($form_id);


        $form->options = maybe_unserialize($form->options);


        if (isset($form->options['ar_update_email']) and $form->options['ar_update_email'])
            $this->autoresponder($entry_id, $form_id);
    }

    function autoresponder($entry_id, $form_id) {

        global $wpdb, $MdlDb, $arf_matrix;

        if (defined('WP_IMPORTING')) {
            return;
        }
        global $arfform, $db_record, $arfrecordmeta, $style_settings, $arfsettings, $armainhelper, $arfieldhelper, $arnotifymodel, $arformhelper, $arformcontroller, $arffield, $arrecordcontroller;

        if(!isset($form_id)) {
            return;
        }
        if( $form_id < 0 ){
            $form_id = abs( $form_id );
        }
        $form = $arfform->getOne($form_id);
        $form_options = maybe_unserialize($form->options);
        if (!isset($form_options['auto_responder']) or ! $form_options['auto_responder'] or ! isset($form_options['ar_email_message']) or $form_options['ar_email_message'] == '') {

            return;
        }



        $form_options['ar_email_message'] = wp_specialchars_decode($form_options['ar_email_message'], ENT_QUOTES);
        $field_order = json_decode($form_options['arf_field_order'],true);
        $inner_field_order = json_decode( $form_options['arf_inner_field_order'], true);
        $entry = $db_record->getOne($entry_id, true);
        if( !isset( $entry->id) ){
            return;
        }
        $entry_ids = array($entry->id);
        if ($form_options['arf_conditional_enable_mail'] == 1) {
            $rec_url = isset($rec_url) ? $rec_url : '';
            $email_field = $this->arf_set_conditional_mail_sent($rec_url, $form, $entry->id);
        } else {
            $email_field = (isset($form_options['ar_email_to'])) ? $form_options['ar_email_to'] : 0;
        }
        if (preg_match('/|/', $email_field)) {
            $email_fields = explode('|', $email_field);
            if (isset($email_fields[1])) {
                if (isset($entry->metas[$email_fields[0]])) {
                    $add_id = $entry->metas[$email_fields[0]];
                    $add_id = maybe_unserialize($add_id);
                    if (is_array($add_id)) {
                        foreach ($add_id as $add)
                            $entry_ids[] = $add;
                    } else {
                        $entry_ids[] = $add_id;
                    }
                }
                $email_field = $email_fields[1];
            }
            unset($email_fields);
        }
        $inc_fields = array();
        foreach (array($email_field) as $inc_field) {
            if ($inc_field)
                $inc_fields[] = $inc_field;
        }
        $where = "it.entry_id in (" . implode(',', $entry_ids) . ")";
        if (!empty($inc_fields)) {
            $inc_fields = implode(',', $inc_fields);
            $where .= " and it.field_id in ($inc_fields)";
        }
        $new_form_cols = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")", " ORDER BY fi.id");        

        global $wpdb, $MdlDb;
        $repeater_field_arr = array();
        $repeater_fields = $wpdb->get_results($wpdb->prepare("SELECT id,type,field_key,required,form_id,name,field_options,created_date FROM " .$MdlDb->fields." WHERE type=%s AND form_id = %d", 'arf_repeater', $form_id), ARRAY_A);
        
        foreach ($repeater_fields as $key => $value) {
            array_push($repeater_field_arr, (int) $value['id']);
        }
        
        $section_field_arr = array();
        $section_fields = $wpdb->get_results( $wpdb->prepare("SELECT id,type,field_key,required,form_id,name,field_options,created_date FROM " .$MdlDb->fields." WHERE type=%s AND form_id = %d", 'section', $form_id), ARRAY_A );

        foreach( $section_fields as $key => $value ){
        	array_push( $section_field_arr, (int) $value['id'] );
        }
        $values = array();
        asort($field_order);
        $hidden_fields = array();
        $hidden_field_ids = array();

        $allfields = $wpdb->get_results($wpdb->prepare("SELECT id,type FROM " .$MdlDb->fields." WHERE form_id = %d order by id", $form_id), ARRAY_A);
        
        $new_form_cols_arr = $arformcontroller->arfObjtoArray( $new_form_cols );
        $allfieldarray = array();
        if ($allfields) {
            foreach ($allfields as $tmpfield){
                $allfieldarray[] = $tmpfield['id'];
                if( 'html' == $tmpfield['type'] ){
                    $temp_fid = $arformcontroller->arfSearchArray($tmpfield['id'], 'field_id', $new_form_cols_arr );
                    $get_field = $arffield->getOne( $tmpfield['id'] );
                    if( '' === trim($temp_fid) ){
                        if( !empty( $get_field ) ){
                            $temp_obj = array(
                                'id' => '-'.$tmpfield['id'],
                                'entry_value' => $get_field->field_options['description'],
                                'field_id' => $tmpfield['id'],
                                'entry_id' => $entry_id,
                                'field_type' => $get_field->type,
                                'field_key' => $get_field->field_key,
                                'required' => $get_field->required,
                                'field_form_id' => $get_field->form_id,
                                'field_name' => $get_field->name,
                                'fi_options' => $get_field->field_options,
                            );

                            $tmp_obj = json_decode( json_encode( $temp_obj ) );

                            array_push( $new_form_cols, $tmp_obj );
                        }
                    } else {
                        if( !empty( $new_form_cols[$temp_fid] ) && empty( $new_form_cols[$temp_fid]->fi_options ) ){
                            $new_form_cols[$temp_fid]->fi_options = $get_field->field_options;
                        }
                    }
                }
            }
        }

        foreach ($field_order as $field_id => $order) {
            if(is_int($field_id)){
                foreach ($new_form_cols as $field) {
                    if ($field_id == $field->field_id) {
                        $values[] = $field;
                    } else if( $field->field_type == 'hidden' ){
                        if( !in_array($field->field_id,$hidden_field_ids) ){
                            $hidden_fields[] = $field;
                            $hidden_field_ids[] = $field->field_id;
                        }
                    }
                }
                if(in_array($field_id, $repeater_field_arr)) {
                    $index = array_search($field_id, $repeater_field_arr, true);
                    $arr = array();
                    $arr['field_id'] = $repeater_fields[$index]['id'];
                    $arr['entry_value'] = "";
                    $arr['entry_id'] = "";
                    $arr['created_date'] = $repeater_fields[$index]['created_date'];
                    $arr['field_type'] = $repeater_fields[$index]['type'];
                    $arr['field_key'] = $repeater_fields[$index]['field_key'];
                    $arr['required'] = $repeater_fields[$index]['required'];
                    $arr['field_form_id'] = $repeater_fields[$index]['form_id'];
                    $arr['field_name'] = $repeater_fields[$index]['name'];
                    $arr['fi_options'] = $repeater_fields[$index]['field_options'];
                    $values[] = (object)$arr;
                }
                if( in_array( $field_id, $section_field_arr ) ){
                	$index = array_search($field_id, $section_field_arr, true);
                	asort($inner_field_order);
                    if( isset( $inner_field_order[$field_id] ) ){
                    	$inner_fields = $inner_field_order[$field_id];
                    	foreach( $inner_fields as $inner_field ){
                    		$exploded_data = explode('|',$inner_field );
                    		$in_field_id = (int)$exploded_data[0];
                    		if( is_int( $in_field_id ) ){
    	                		foreach ($new_form_cols as $field) {
    	                			if( $field->field_id == $in_field_id ){
    	                				$values[] = $field;
    	                			}
    	                		}
                    		}
                        }
                	}
                }
            }
        }

        if( count($hidden_fields) > 0 ){
            $values = array_merge($values,$hidden_fields);
        }

        $plain_text = (isset($form_options['ar_plain_text']) and $form_options['ar_plain_text']) ? true : false;
        $custom_message = false;
        $get_default = true;
        $message = apply_filters('arfarmessage', $form_options['ar_email_message'], array('entry' => $entry, 'form' => $form));
        $shortcodes = $armainhelper->get_shortcodes($form_options['ar_email_message'], $form_id);
        $mail_body = $arfieldhelper->replace_shortcodes($form_options['ar_email_message'], $entry, $shortcodes);
        $mail_body = $arfieldhelper->arf_replace_shortcodes($mail_body, $entry, true);
        $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $reply_to_name = (isset($form_options['ar_user_from_name'])) ? $form_options['ar_user_from_name'] : $arfsettings->reply_to_name;
        $reply_to_name = trim($reply_to_name);
        $reply_to_id = (isset($form_options['ar_user_from_email'])) ? $form_options['ar_user_from_email'] : $arfsettings->reply_to;
        if (isset($reply_to_id)) {
            $reply_to = isset($entry->metas[$reply_to_id]) ? $entry->metas[$reply_to_id] : '';
        }
        if ($reply_to == '') {
            $reply_to = $reply_to_id;
        }
        $reply_to = trim($reply_to);


        $to_email = '';
        foreach ($values as $value) {
            if ((int) $email_field == $value->field_id) {
                $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);
                if (is_email($val))
                    $to_email = $val;
            }
        }
        $to_email = apply_filters('arfbefore_autoresponse_chnage_mail_address_in_out_side', $to_email, $email_field, $entry_id, $form_id);
        if (preg_match('/(.*?)\((.*?)\)/', $to_email)) {
            $validate_email = preg_replace('/(.*?)\((.*?)\)/', '$2', $to_email);
            if (filter_var($validate_email, FILTER_VALIDATE_EMAIL)) {
                $to_email = $validate_email;
            }
        }
        if (!isset($to_email)) {
            return;
        }
        $get_default = true;
        $mail_body = '';
        if (isset($form_options['ar_email_message']) and trim($form_options['ar_email_message']) != '') {
            if (!preg_match('/\[ARF_form_all_values\]/', $form_options['ar_email_message'])){
                $get_default = false;
            }
            $custom_message = true;
            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_email_message'], $entry->form_id);
            $mail_body = $arfieldhelper->replace_shortcodes($form_options['ar_email_message'], $entry, $shortcodes);
            $mail_body = $arfieldhelper->arf_replace_shortcodes($mail_body, $entry, true);

            /*change added for user email notification*/
            $tagregexp = '';
            $pattern = "/\{(if )?($tagregexp)(.*?)(?:(\/))?\}(?:(.+?)\{\/\2\})?/s";
            $pattern = '/\{field_id\:(\d+)\}/s';
            preg_match_all( $pattern, $mail_body, $matches);
            if( !empty( $matches[0] ) ){
                foreach( $matches[0] as $shortcode ){
                    $field_data = str_replace('{', '', $shortcode );
                    $field_data = str_replace('}', '', $field_data);
                    $splited_data = explode( ':', $field_data );
                    $field = $arffield->getOne( end( $splited_data ) );
                    $get_field_value = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " .$MdlDb->entry_metas." WHERE field_id='%d' AND entry_id='%d'", $field->id, $entry_id));

                    if( !isset( $get_field_value->entry_value ) ){
                        $get_field_value->entry_value = '';
                    }

                    if ( 'file' == $field->type ) {
                        $old_value = explode('|', $get_field_value->entry_value);
                        
                        foreach ($old_value as $val) {

                            $url = wp_get_attachment_url($val);

                            if( !empty( $url ) ){
                                $get_field_value->entry_value = $url;
                            }
                        }
                    }
                    if ($field->type == 'select' || $field->type == 'checkbox' || $field->type == 'radio' || $field->type == 'arf_autocomplete' || $field->type == 'arf_multiselect') {

                        $field_entry_value = array();

                        $field_entry_value = maybe_unserialize( $get_field_value->entry_value );

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

                        $get_field_value->entry_value = $value;
                    }
                    if ( 'matrix' == $field->type ){
                        global $arffield;

                        $field_value = maybe_unserialize( $get_field_value->entry_value );

                        $field_id = $get_field_value->field_id;

                        $field_obj = $field;

                        $value_for_mail  = '<table cellpadding="5" cellspacing="0" border="1">';

                            $value_for_mail .= '<tbody>';


                                if( !empty( $field_obj->rows ) ){

                                    $total_rows = count( $field_obj->rows );

                                    $rx = 0;
                                    foreach( $field_obj->rows as $frows ){

                                        $value_for_mail .= '<tr>';

                                            $value_for_mail .= '<td>';
                                            $value_for_mail .= $frows;
                                            $value_for_mail .= '</td>';
                                            $value_for_mail .= '<td>';
                                            if( isset( $field_obj->field_options['separate_value'] ) && 1 == $field_obj->field_options['separate_value'] ){
                                                foreach( $field_obj->field_options['options'] as $matrix_opts ){
                                                    if( isset( $field_value[$rx] ) && $matrix_opts['value'] == $field_value[$rx] ){
                                                        $value_for_mail .= $matrix_opts['label'] . ' ('.$field_value[$rx].')';
                                                    }
                                                }
                                            } else {
                                                $value_for_mail .= ( !empty( $field_value[$rx] ) ? $field_value[$rx] : '-' );
                                            }

                                            $value_for_mail .= '</td>';

                                        $value_for_mail .= '</tr>';

                                        $rx++;
                                    }
                                }

                            $value_for_mail .= '</tbody>';

                        $value_for_mail .= '</table>';

                        $get_field_value->entry_value = $value_for_mail;
                    }

                    $mail_body = str_replace( $shortcode, $get_field_value->entry_value, $mail_body );
                }
            }
            /*change over*/
        }

        $default = "";
        if ($get_default and ! $plain_text) {
            $default .= "<table cellspacing='0' style='font-size:12px;line-height:135%; border-bottom:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color};'><tbody>";
            $bg_color = " style='background-color:#{$style_settings->bg_color};'";
            $bg_color_alt = " style='background-color:#{$style_settings->arfbgactivecolorsetting};'";
        }
        $odd = true;
        $attachments = array();

        foreach ($values as $value) {            
            $value = apply_filters('arf_brfore_send_mail_chnage_value', $value, $entry_id, $form_id);
            if ($value->field_type == 'file') {
                global $MdlDb, $wpdb;
                $file_options = $MdlDb->get_var($MdlDb->fields, array('id' => $value->field_id), 'field_options');
                $file_options = json_decode($file_options);
                if (isset($file_options->attach) && $file_options->attach == 1) {
                    $attach_file_values = explode('|', $value->entry_value);
                    foreach ($attach_file_values as $attach_file_val){
                        $field_id = $wpdb->get_row("select * from " . $wpdb->prefix . "postmeta where post_id = '" . $attach_file_val . "' AND meta_key = '_wp_attached_file'");
                        $file = $field_id->meta_value;  
                        $file = str_replace('thumbs/', '', $file);

                        if (preg_match( '/http/', $file )) {
                            $home_url = get_home_url();
                            $file = str_replace($home_url, "", $file);
                            $file = ltrim($file , '/');
                            $attachments[] = ABSPATH . $file;
                        } else{
                            $attachments[] = ABSPATH . "/$file";
                        } 
                    }
                }
            }

            $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);
            
            if ($value->field_type == 'file') {

                if ( isset($file_options->arf_is_multiple_file) && $file_options->arf_is_multiple_file == 1) {
                    $icon_file_values = explode('|', $value->entry_value);
                    if ( !empty($icon_file_values) && count($icon_file_values) > 1 ) {
                        foreach ($icon_file_values as $icon_file_val){
                            $icon_val = apply_filters('arfemailvalue', maybe_unserialize($icon_file_val), $value, $entry);

                            if (isset($icon_val) and $icon_val != '') {
                                if (is_numeric($icon_val)) {
                                    $icon_val = $icon_val;
                                } else {
                                    $icon_val = $icon_file_val;
                                }
                                
                                $get_file_field_id = $wpdb->get_row("select * from " . $wpdb->prefix . "postmeta where post_id = '" . $icon_file_val . "' AND meta_key = '_wp_attached_file'");
                                $file = $get_file_field_id->meta_value;

                                $sep = '';
                                if (!empty($val)) {
                                    $sep = '<br>';
                                }

                                $val .= $sep;
                                if (file_exists(ABSPATH . $file)){
                                    $full_path = site_url() . "/" . str_replace('thumbs/', '', $file);
                                    $val .= "<a href='" . $full_path . "' target='_blank'><img src='" . $full_path . "' /></a>";
                                } else {
                                    $val .= $arfieldhelper->get_file_name_link($icon_val, false);
                                }
                            }
                                                    
                        } 
                    }
                    
                }				
            }

            if ($value->field_type == 'checkbox' || $value->field_type == 'radio' || $value->field_type == 'select' || $value->field_type == 'arf_autocomplete' || $value->field_type == 'arf_multiselect' ) {
                if (isset($value->entry_value)) {
                    if (is_array(maybe_unserialize($value->entry_value))) {
                        $val = implode(', ', maybe_unserialize($value->entry_value));
                    } else {
                        $val = $value->entry_value;
                    }
                }
            }

            if ($value->field_type == 'select' || $value->field_type == 'checkbox' || $value->field_type == 'radio' || $value->field_type == 'arf_autocomplete' || $value->field_type == 'arf_multiselect' ) {
                global $wpdb,$MdlDb;
                $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " .$MdlDb->entry_metas." WHERE field_id='%d' AND entry_id='%d'", "-" . $value->field_id, $entry->id));

                if ($field_opts) {
                    $field_opts = maybe_unserialize($field_opts->entry_value);

                    if ($value->field_type == 'checkbox' || $value->field_type == 'arf_multiselect') {
                        if ($field_opts && count($field_opts) > 0) {
                            $temp_value = "";
                            foreach ($field_opts as $new_field_opt) {
                                $temp_value .= $new_field_opt['label'] . " (" . $new_field_opt['value'] . "), ";
                            }
                            $temp_value = trim($temp_value);
                            $val = rtrim($temp_value, ",");
                        }
                    } else {
                        if ($value->field_type == 'select' ) {
                            $field_id = $value->field_id;
                            $field_tmp = $wpdb->get_row($wpdb->prepare("SELECT * FROM " .$MdlDb->fields." WHERE id = '%d'",$field_id));
                            $field_tmp_opts = json_decode($field_tmp->field_options,true);
                            if( json_last_error() != JSON_ERROR_NONE ){
                                $field_tmp_opts = maybe_unserialize($field_tmp->field_options);
                            }
                            if ($field_tmp_opts['separate_value']) {
                                $label_field_id = ( $value->field_id * 100 );
                                $get_field_label = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " .$MdlDb->entry_metas.' WHERE field_id = "-%d" and entry_id="%d"',$label_field_id,$value->entry_id));
                                $field_label = isset( $get_field_label->entry_value ) ? $get_field_label->entry_value : '';
                                if ($field_label != '') {
                                    $val = stripslashes($get_field_label->entry_value) . " (" . stripslashes($field_opts['value']) . ")";
                                } else {
                                    $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                                }
                            } else {
                                $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                            }
                        } else {
                            $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                        }
                    }
                }
            }
            if ($value->field_type == 'textarea' and ! $plain_text){
                $val = str_replace(array("\r\n", "\r", "\n"), ' <br/>', $val);
            }
            if (is_array($val)){
                $val = implode(', ', $val);
            }

            if ($value->field_type == 'html') {
                $html_field_opts = arf_json_decode( json_encode( $value->fi_options ), true );
                if( !empty( $html_field_opts ) ){
                    if ( $html_field_opts['enable_total'] == 1 ) {
                        $html_desc = $html_field_opts['description'];
                        $regex = '/<arftotal>(.*?)<\/arftotal>/is';
                        $val = preg_replace($regex, $val, $html_desc);
                    }
                }
            }

            if($value->field_type == 'arf_repeater') {

                $get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT a.*, b.* FROM `".$MdlDb->fields."` a LEFT JOIN ".$MdlDb->entry_metas." b ON b.field_id = a.id WHERE ( field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%' ) AND entry_id=%d GROUP BY a.id", $value->field_id, $value->field_id, $entry->id ), ARRAY_A );
                
                if(!empty($get_all_inner_fields)) {
                    $valign = "";
                    $inner_field_content = "";
                    $row_style = "valign='top' style='text-align:left;color:#{$style_settings->text_color};padding:7px 9px;border-top:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color}'";
                    if( count( $get_all_inner_fields ) == 1 ){
                        foreach ($get_all_inner_fields as $key => $inner_fields) {
                            if(!empty($inner_fields['entry_value']) && strpos($inner_fields['entry_value'],"[ARF_JOIN]") != -1) {
                                $valign = 'valign="top"';
                                $entry_val = !empty($inner_fields['entry_value']) ? explode("[ARF_JOIN]", $inner_fields['entry_value']) : array();  
                                $row_span = count($entry_val);
                                $cnt = 0;
                                $has_separate_value = false;
                                if( in_array( $inner_fields['type'], array('checkbox', 'radio', 'select', 'arf_autocomplete', 'arf_multiselect') ) ){
                                    $field_opts = json_decode( $inner_fields['field_options'], true );
                                    if( isset( $field_opts['separate_value'] ) && '1' == $field_opts['separate_value'] ){
                                        $has_separate_value = true;
                                    }
                                }

                                foreach ($entry_val as $key2 => $in_value) {
                                    $inner_field_content .= "<tr>";
                                    if( preg_match('/\!\|\!/', $in_value) ){
                                        $value_exp = explode( '!|!', $in_value );
                                        $temp_val = '';
                                        $value_arr = maybe_unserialize( $value_exp[0] );

                                        if( $has_separate_value ){
                                            $sep_value = '';
                                            $fopts = json_decode( $inner_fields['options'], true );

                                            foreach( $value_arr as $value_arrk => $varrv ){
                                                $fopt_key = $arformcontroller->arfSearchArray($varrv, 'value', $fopts);
                                                $sep_value .= $fopts[$fopt_key]['label'] . ' ('.$fopts[$fopt_key]['value'].'),';
                                            }
                                            $in_value = rtrim( $sep_value, ',');
                                        } else {
                                            if( is_array( $value_arr ) ){
                                                $temp_val = implode( ',',$value_arr );
                                            } else {
                                                $temp_val = $value_arr;
                                            }
                                            $in_value = $temp_val;
                                        }
                                    }
                                    if( $inner_fields['type'] == 'date'){
                                        $inner_field_opts = arf_json_decode( $inner_fields['field_options'] );
                                        $in_value = $arfieldhelper->get_date_entry( $in_value,$inner_fields['form_id'],$inner_field_opts->show_time_calendar, $inner_field_opts->clock,$inner_field_opts->locale, 'arformspdfcreator'  );
                                    }
                                    if($cnt==0) {
                                        $inner_field_content .= "<td rowspan='".$row_span."' valign='top'>".$inner_fields['name']."</td><td>".$in_value."</td>";
                                    } else {
                                        $inner_field_content .= "<td>".$in_value."</td>";
                                    }                                    
                                    $cnt++;
                                    $inner_field_content .= "</tr>";
                                }
                                
                            } else {
                                if( preg_match('/\!\|\!/', $inner_fields['entry_value']) ){
                                    $value_exp = explode( '!|!', $inner_fields['entry_value'] );
                                    $temp_val = '';
                                    $value_arr = maybe_unserialize( $value_exp[0] );
                                    $temp_val = implode( ',',$value_arr );
                                    $inner_fields['entry_value'] = $temp_val;
                                }

                                if( $inner_fields['type'] == 'date'){
                                    $inner_field_opts = arf_json_decode( $inner_fields['field_options'] );
                                    $inner_fields['entry_value'] = $arfieldhelper->get_date_entry( $inner_fields['entry_value'],$inner_fields['form_id'],$inner_field_opts->show_time_calendar, $inner_field_opts->clock,$inner_field_opts->locale, 'arformspdfcreator'  );
                                }
                                $inner_field_content .= "<tr>";
                                    $inner_field_content .= "<td rowspan='".$row_span."'>".$inner_fields['name']."</td><td>".$inner_fields['entry_value']."</td>";
                                $inner_field_content .= "</tr>";
                            }
                        } 
                        if( !isset($bg_color) ){
                            $bg_color = " style='background-color:#{$style_settings->bg_color};'";
                        }
                        $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th ".$valign." $row_style>$value->field_name</th><td $row_style><table class='arf_repeater_table' border='1' cellpadding='10' cellspacing='0' style='border-color:#ccc'><tbody>";
                        $default .= $inner_field_content;
                        $default .= "</tbody></table></td></tr>";
                    } else if( count( $get_all_inner_fields ) > 1 ){
                        $inner_field_content .= "<table class='arf_repeater_table' border='1' cellpadding='10' cellspacing='0' style='border-color:#ccc'>";
                            
                                $fields_arr = [];
                                foreach( $get_all_inner_fields as $key => $inner_fields ){
                                    
                                    if($inner_fields['type']=='like' && $inner_fields['entry_value']===0) {
                                        $inner_fields['entry_value'] = "0";
                                    }
                                    if('' != $inner_fields['entry_value'] && strpos($inner_fields['entry_value'],"[ARF_JOIN]") != -1) {
                                        $entry_val = ('' != $inner_fields['entry_value']) ? explode("[ARF_JOIN]", $inner_fields['entry_value']) : array(); 
                                        $has_separate_value = false;
                                        if( in_array( $inner_fields['type'], array('checkbox', 'radio', 'select', 'arf_autocomplete', 'arf_multiselect') ) ){
                                            $field_opts = json_decode( $inner_fields['field_options'], true );
                                            if( isset( $field_opts['separate_value'] ) && '1' == $field_opts['separate_value'] ){
                                                $has_separate_value = true;
                                            }
                                        }
                                        $cnt = 0;
                                        foreach ($entry_val as $key2 => $in_value) {
                                            if( preg_match('/\!\|\!/', $in_value) ){
                                                $value_exp = explode( '!|!', $in_value );
                                                $temp_val = '';
                                                $value_arr = maybe_unserialize( $value_exp[0] );

                                                if( $has_separate_value ){
                                                    $sep_value = '';
                                                    $fopts = json_decode( $inner_fields['options'], true );

                                                    foreach( $value_arr as $value_arrk => $varrv ){
                                                        $fopt_key = $arformcontroller->arfSearchArray($varrv, 'value', $fopts);
                                                        $sep_value .= $fopts[$fopt_key]['label'] . ' ('.$fopts[$fopt_key]['value'].'),';
                                                    }
                                                    $in_value = rtrim( $sep_value, ',');
                                                } else {
                                                    if( is_array( $value_arr ) ){
                                                        $temp_val = implode( ',',$value_arr );
                                                    } else {
                                                        $temp_val = $value_arr;
                                                    }
                                                    $in_value = $temp_val;
                                                }
                                            }
                                            if( $inner_fields['type'] == 'date'){
                                                $inner_field_opts = arf_json_decode( $inner_fields['field_options'] );
                                                $in_value = $arfieldhelper->get_date_entry( $in_value,$inner_fields['form_id'],$inner_field_opts->show_time_calendar, $inner_field_opts->clock,$inner_field_opts->locale, 'arformspdfcreator'  );
                                            }
                                            $fields_arr[$cnt][$inner_fields['field_id']] = $in_value;
                                           
                                            $cnt++;
                                        }
                                    } else{
                                        if( preg_match('/\!\|\!/', $inner_fields['entry_value']) ){
                                            $value_exp = explode( '!|!', $inner_fields['entry_value'] );
                                            $temp_val = '';
                                            $value_arr = maybe_unserialize( $value_exp[0] );
                                            $temp_val = implode( ',',$value_arr );
                                            $inner_fields['entry_value'] = $temp_val;
                                        }
                                        if( $inner_fields['type'] == 'date'){
                                            $inner_field_opts = arf_json_decode( $inner_fields['field_options'] );
                                            $inner_fields['entry_value'] = $arfieldhelper->get_date_entry( $inner_fields['entry_value'],$inner_fields['form_id'],$inner_field_opts->show_time_calendar, $inner_field_opts->clock,$inner_field_opts->locale, 'arformspdfcreator'  );
                                        }
                                    }

                                }
                               
                            $inner_field_content .= "<tbody>";
                            $i = 1;
                                foreach( $fields_arr as $k => $frr ){
                                    foreach( $get_all_inner_fields as $inner_fields ){
                                        $inner_field_content .= "<tr>";
                                        $inner_field_content .= "<td style='width:35%'>" . $inner_fields['name'] . " [".$i."] </td>";
                                        $inner_field_content .= "<td>" . $frr[$inner_fields['field_id']] . "</td>";
                                        $inner_field_content .= "</tr>";
                                    }
                                    $i++;
                                }
                            $inner_field_content .= "</tbody>";
                        $inner_field_content .= "</table>";

                        if( !isset($bg_color) ){
                            $bg_color = " style='background-color:#{$style_settings->bg_color};'";
                        }
                        $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th valign='top' $row_style>$value->field_name</th>";
                        $default .= "<td $row_style>";
                        $default .= $inner_field_content;
                        $default .= "</td></tr>";
                    }
                }
                $odd = ($odd) ? false : true;
            } else {
                if ( $value->field_type == 'html') {
                    $html_field_opts = arf_json_decode( json_encode( $value->fi_options ), true );
                    if( !empty( $html_field_opts ) ){
                        if ( $html_field_opts['enable_total'] == 1 ) {
                            $val = $value->entry_value;        
                        }
                    }
                    if ( $html_field_opts['enable_total'] == 1 ) {
                        if ($get_default and $plain_text) {
                            $default .= $value->field_name . ': ' . $val . "\r\n\r\n";
                        } else if ($get_default) {
                            $row_style = "valign='top' style='text-align:left;color:#{$style_settings->text_color};padding:7px 9px;border-top:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color}'";
                            $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th $row_style>$value->field_name</th><td $row_style>$val</td></tr>";
                            $odd = ($odd) ? false : true;
                        }
                    }
                } else {
                    if ($get_default and $plain_text) {
                            $default .= $value->field_name . ': ' . $val . "\r\n\r\n";
                        } else if ($get_default) {
                            $row_style = "valign='top' style='text-align:left;color:#{$style_settings->text_color};padding:7px 9px;border-top:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color}'";
                            $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th $row_style>$value->field_name</th><td $row_style>$val</td></tr>";
                            $odd = ($odd) ? false : true;
                        }
                }
            }
            
            if ( isset($email_fields) and is_array($email_fields)) {
                if (in_array($value->field_id, $email_fields)) {
                    $val = explode(',', $val);
                    if (is_array($val)) {
                        foreach ($val as $v) {
                            $v = trim($v);
                            if (is_email($v))
                                $to_emails[] = $v;
                        }
                    }else if (is_email($val))
                        $to_emails[] = $val;
                }
            }
        }

        if ( !empty($html_val) && !empty($html_label) ) {
            $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th $row_style>$html_label</th><td $row_style>$html_val</td></tr>";
        }

        if ($get_default and ! $plain_text){
            $default .= "</tbody></table>";
        }
        if (isset($form_options['ar_email_subject']) and $form_options['ar_email_subject'] != '') {
            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_email_subject'], $form_id);
            $subject = $arfieldhelper->replace_shortcodes($form_options['ar_email_subject'], $entry, $shortcodes);
            $subject = $arfieldhelper->arf_replace_shortcodes($subject, $entry, true);
        } else {
            $subject = sprintf(addslashes(__('%1$s Form submitted on %2$s', 'ARForms')), stripslashes($form->name), $arfblogname);
        }
        $subject = trim($subject);
        if ($reply_to) {

            $reply_to = $arfieldhelper->arf_replace_shortcodes($reply_to, $entry, true);
        }
        if( preg_match_all('/(.*?)\s+\(([A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]+)\)/',$reply_to,$rt_matches) ){
        	$reply_to = ( !empty( $rt_matches[2][0] ) ) ? $rt_matches[2][0] : '';
        }
        $user_nreplyto = (isset($form_options['ar_user_nreplyto_email'])) ? $form_options['ar_user_nreplyto_email'] : $arfsettings->reply_to;

        if (isset($user_nreplyto) and $user_nreplyto != '') {
            $user_nreplyto = $arfieldhelper->arf_replace_shortcodes($user_nreplyto, $entry, true);
        }
        if( preg_match_all('/(.*?)\s+\(([A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]+)\)/',$user_nreplyto,$urt_matches) ){
        	$user_nreplyto = ( !empty( $urt_matches[2][0] ) ) ? $urt_matches[2][0] : '';
        }

        if ($get_default and $custom_message) {
            $mail_body = str_replace('[ARF_form_all_values]', $default, $mail_body);
        }
        else if ($get_default){
            $mail_body = $default;
        }
        $data = maybe_unserialize($entry->description);
        $browser_info = $arrecordcontroller->getBrowser($data['browser']);
        $browser_detail = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
        if (preg_match('/\[ARF_form_ipaddress\]/', $mail_body)){
            $mail_body = str_replace('[ARF_form_ipaddress]', $entry->ip_address, $mail_body);
        }
        if (preg_match('/\[ARF_form_browsername\]/', $mail_body)){
            $mail_body = str_replace('[ARF_form_browsername]', $browser_detail, $mail_body);
        }
        if (preg_match('/\[ARF_form_referer\]/', $mail_body)){
            $mail_body = str_replace('[ARF_form_referer]', $data['http_referrer'], $mail_body);
        }
        if (preg_match('/\[ARF_form_entryid\]/', $mail_body)){
            $mail_body = str_replace('[ARF_form_entryid]', $entry->id, $mail_body);
        }
        if (preg_match('/\[ARF_form_entrykey\]/', $mail_body)){
            $mail_body = str_replace('[ARF_form_entrykey]', $entry->entry_key, $mail_body);
        }
        if (preg_match('/\[ARF_form_added_date_time\]/', $mail_body)) {
            $wp_date_format = get_option('date_format');
            $wp_time_format = get_option('time_format');
            $mail_body = str_replace('[ARF_form_added_date_time]', date($wp_date_format . " " . $wp_time_format, strtotime($entry->created_date)), $mail_body);
        }

        $arf_current_user = wp_get_current_user();
        if (preg_match('/\[ARF_current_userid\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_userid]', $arf_current_user->ID, $mail_body);
        }
        if (preg_match('/\[ARF_current_username\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_username]', $arf_current_user->user_login, $mail_body);
        }
        if (preg_match('/\[ARF_current_useremail\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_useremail]', $arf_current_user->user_email, $mail_body);
        }
        if (preg_match('/\[ARF_page_url\]/', $mail_body)) {
            $entry_desc = maybe_unserialize($entry->description);
            $mail_body = str_replace('[ARF_page_url]', $entry_desc['page_url'], $mail_body);   
        }
        if(preg_match('/\[form_name\]/', $mail_body)){
            $mail_body = str_replace('[form_name]', $form->name, $mail_body); 
        }
        if(preg_match('/\[site_name\]/', $mail_body)){
            $mail_body = str_replace('[site_name]', $arfblogname, $mail_body);
        }
        if(preg_match('/\[site_url\]/', $mail_body)){
            $mail_body = str_replace('[site_url]', ARF_HOME_URL, $mail_body); 
        }
        
        $mail_body = apply_filters('arfbefore_autoresponse_send_mail_body', $mail_body, $entry_id, $form_id);
        $attachments = apply_filters('arfautoresponderattachment', $attachments, $form, array('entry' => $entry));
        $mail_body = nl2br($mail_body);
        
        $arnotifymodel->send_notification_email_user($to_email, $subject, $mail_body, $reply_to, $reply_to_name, $plain_text, $attachments, false, false, false, false, $user_nreplyto, '', '', $entry_id);
        return $to_email;
    }

    function arfchangesmtpsetting($phpmailer) {
        global $arfsettings;


        if (isset($arfsettings->is_smtp_authentication) && $arfsettings->is_smtp_authentication == '1') {
            if (!isset($arfsettings->smtp_host) || empty($arfsettings->smtp_host) || !isset($arfsettings->smtp_username) || empty($arfsettings->smtp_username) || !isset($arfsettings->smtp_password) || empty($arfsettings->smtp_password)) {
                return;
            }
        } else {
            if (!isset($arfsettings->smtp_host) || empty($arfsettings->smtp_host)) {
                return;
            }
        }

        if (!isset($arfsettings->smtp_port) || empty($arfsettings->smtp_port))
            $arfsettings->smtp_port = 25;


        $phpmailer->IsSMTP();


        $phpmailer->Host = $arfsettings->smtp_host;
        $phpmailer->Port = $arfsettings->smtp_port;


        if (isset($arfsettings->is_smtp_authentication) && $arfsettings->is_smtp_authentication == '1') {
            $phpmailer->SMTPAuth = true;
        }else{
            $phpmailer->SMTPAuth = false;
        }

        $phpmailer->Username = $arfsettings->smtp_username;
        $phpmailer->Password = $arfsettings->smtp_password;
        if (isset($arfsettings->smtp_encryption) and $arfsettings->smtp_encryption != '' and $arfsettings->smtp_encryption != 'none') {
            $phpmailer->SMTPSecure = $arfsettings->smtp_encryption;
        }
    }

    function arf_set_conditional_mail_sent($rec_url, $form, $entry_id) {
        global $wpdb,$MdlDb, $arfrecordmeta;

        $options = $form->options;

        if (isset($options['arf_conditional_enable_mail']) && $options['arf_conditional_enable_mail'] == '1' && !empty($entry_id)) {
            $entry_ids = array($entry_id);
            $values = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")", " ORDER BY fi.id");

            if (isset($options['arf_conditional_mail_rules']) && !empty($options['arf_conditional_mail_rules'])) {
                foreach ($options['arf_conditional_mail_rules'] as $key => $rules_value) {

                    if (count($values) > 0) {
                        foreach ($values as $value) {
                            if ($rules_value['field_id_mail'] == $value->field_id) {

                                $mail_send_value = $value->entry_value;
                                if ('date' == $rules_value['field_type_mail'] ) {
                                    $date_value = $value->entry_value;                                    
                                    $mail_send_value = date("Y/m/d", strtotime($date_value));
                                }

                                break;
                            }
                        }
                    }

                    $conditional_logic_field_type = $rules_value['field_type_mail'];

                    $conditional_logic_value1 = isset($mail_send_value) ? $mail_send_value : '';

                    $conditional_logic_value1 = trim(strtolower($conditional_logic_value1));

                    $conditional_logic_value2 = trim(strtolower($rules_value['value_mail']));

                    $conditional_logic_operator = $rules_value['operator_mail'];

                    if ($this->arf_conditional_mail_send_calculate_rule($conditional_logic_value1, $conditional_logic_value2, $conditional_logic_operator, $conditional_logic_field_type)) {
                        $rec_url = $rules_value['send_mail_field'];
                        break;
                    }
                }
            }
        }
        return $rec_url;
    }

    function arf_conditional_mail_send_calculate_rule($value1, $value2, $operator, $field_type) {
        global $arfieldhelper;

        if ($field_type == 'checkbox' || $field_type == 'arf_multiselect') {
            $chk = 0;
            $default_value = maybe_unserialize($value1);

            if ($default_value && is_array($default_value)) {
                foreach ($default_value as $chk_value) {
                    $value1 = trim(strtolower($chk_value));
                    if ($arfieldhelper->ar_match_rule($value1, $value2, $operator))
                        $chk++;
                }
            }
            else if ($arfieldhelper->ar_match_rule($value1, $value2, $operator)) {
                $chk++;
            }


            if ($chk > 0)
                return true;
            else
                return false;
        } elseif ('date' == $field_type) {
            $value1 = strtotime($value1);
            $value2 = strtotime($value2);
            return $arfieldhelper->ar_match_rule($value1, $value2, $operator);
        } else {

            return $arfieldhelper->ar_match_rule($value1, $value2, $operator);
        }

        return false;
    }

    function arf_autoreponder_entry($entry_id, $form_id, $is_force = false) {


        global $wpdb, $MdlDb, $fid, $check_itemid, $form_responder_fname, $form_responder_lname, $form_responderemail, $email, $fname, $lname, $arfrecordmeta;

        if ($check_itemid == "") {
            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM " .$MdlDb->forms." WHERE id=%d", $fid));
            /* arf_dev_flag if entry prevented from user signup plugin*/
            if(!isset($result[0])){
                return;
            }

            $result = $result[0];

            $autoresponder_fname = $result->autoresponder_fname;


            $autoresponder_lname = $result->autoresponder_lname;


            $autoresponder_email = $result->autoresponder_email;

            $autoresponder_email = apply_filters('arf_check_autoresponder_email_outside',$autoresponder_email,$fid,$entry_id);


            $entry_ids = array($entry_id);

            $values = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")", " ORDER BY fi.id");


            foreach ($values as $key => $entry_details) {
                if ($autoresponder_fname == $entry_details->field_id) {


                    $form_responder_fname = $result->autoresponder_fname;


                    $fname = trim($entry_details->entry_value);
                }


                if ($autoresponder_lname == $entry_details->field_id) {


                    $form_responder_lname = $result->autoresponder_lname;


                    $lname = trim($entry_details->entry_value);
                }


                if ($autoresponder_email == $entry_details->field_id) {


                    $form_responderemail = $result->autoresponder_email;


                    $email = trim($entry_details->entry_value);
                }
            }


            $check_condition_on_subscription = true;

            $form_options = maybe_unserialize($result->options);


            /* condition on subscription */
            if (isset($form_options['conditional_subscription']) && $form_options['conditional_subscription'] == 1) {
                $check_condition_on_subscription = apply_filters('arf_check_condition_on_subscription', $form_options, $entry_id);
            }

            $is_mapped_outside = apply_filters('arf_send_autoresponder_data',false,$fid,$entry_id);
            $arf_check_payment = apply_filters('arf_check_payment',false,$fid,$entry_id);


            if ( $arf_check_payment && !$is_force ) {
                return;
            }
            
            if ( ($check_condition_on_subscription && $autoresponder_email != '' && $form_responderemail != '') || $is_mapped_outside) {

                $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " .$MdlDb->ar." WHERE frm_id = %d", $fid), 'ARRAY_A');

                $ar_aweber = maybe_unserialize($res[0]['aweber']);
                $ar_mailchimp = maybe_unserialize($res[0]['mailchimp']);
                $ar_madmimi = maybe_unserialize($res[0]['madmimi']);
                $ar_getresponse = maybe_unserialize($res[0]['getresponse']);
                $ar_gvo = maybe_unserialize($res[0]['gvo']);
                $ar_ebizac = maybe_unserialize($res[0]['ebizac']);
                $ar_icontact = maybe_unserialize($res[0]['icontact']);
                $ar_constant = maybe_unserialize($res[0]['constant_contact']);

                $type = get_option('arf_ar_type');
                if ($ar_aweber['enable'] == 1 && $type['aweber_type'] == 0 && $type['aweber_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/aweber/addsubscriber.php');
                } else if ($ar_aweber['enable'] == 1 && $type['aweber_type'] == 1 && $type['aweber_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/aweber/addsubscriber_api.php');
                }


                if ($ar_mailchimp['enable'] == 1 && $ar_mailchimp['type'] == 1 && $type['mailchimp_type'] != 2) {
                   
                    do_action('arf_add_mailchimp_subscriber',$ar_mailchimp,$fname,$lname,$email,$fid);
                } else if ($ar_mailchimp['enable'] == 1 && $ar_mailchimp['type'] == 0 && $type['mailchimp_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/mailchimp/mailchimp_webform.php');
                }

                if ($ar_madmimi['enable'] == 1 && $ar_madmimi['type'] == 1 && $type['madmimi_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/madmimi/madmimi_send_contact.php');
                } else if ($ar_madmimi['enable'] == 1 && $ar_madmimi['type'] == 0 && $type['madmimi_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/madmimi/madmimi_webform.php');
                }
               
                $res_url = "http://app.getresponse.com/view_webform.js?wid=123";
                if ($ar_getresponse['enable'] == 1 && $ar_getresponse['type'] == 1 && $type['getresponse_type'] != 2) {
                    $api_key_arr = $wpdb->get_row($wpdb->prepare("SELECT responder_api_key FROM ".$MdlDb->autoresponder." WHERE responder_id=%d",4));
                    $api_key = $api_key_arr->responder_api_key;
                    $add_contact = array(
                        'name' => $fname.' '.$lname,
                        'campaign'=> array(
                            'campaignId' => $ar_getresponse['type_val']
                        ),
                        'email'      => $email,
                    );
                    $headers = array(
                            'Content-Type' => 'application/json',
                            'X-Auth-Token' => 'api-key '.$api_key,
                        );
            
                    $contact_url = "https://api.getresponse.com/v3/contacts";
                    $added_contact = wp_remote_post(
                        $contact_url, 
                        array(
                                'timeout' => 5000,
                                'headers' => $headers,
                                'body' => json_encode($add_contact)
                            )
                        );
                    if(is_wp_error($added_contact) ){  
                         //handle error here
                    }
                }else if ($ar_getresponse['enable'] == 1 && $ar_getresponse['type'] == 0 && $type['getresponse_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/getresponse/getresponse_webform.php');
                }


                if ($ar_gvo['enable'] == 1 && $type['gvo_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/gvo/gvo.php');
                }

                if ($ar_ebizac['enable'] == 1 && $type['ebizac_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/ebizac/ebizac.php');
                }


                if ($ar_icontact['enable'] == 1 && $ar_icontact['type'] == 1 && $type['icontact_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/icontact/icontact.php');
                } else if ($ar_icontact['enable'] == 1 && $ar_icontact['type'] == 0 && $type['icontact_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/icontact/icontact_webform.php');
                }


                if ($ar_constant['enable'] == 1 && $ar_constant['type'] == 1 && $type['constant_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/constant_contact/addOrUpdateContact.php');
                } else if ($ar_constant['enable'] == 1 && $ar_constant['type'] == 0 && $type['constant_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/constant_contact/constant_contact_webform.php');
                }


                $check_itemid = $entry_id;
            }
        }
    }

}

?>