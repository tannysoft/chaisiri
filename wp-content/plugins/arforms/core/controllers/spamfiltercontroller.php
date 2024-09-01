<?php 


class spamfiltercontroller {
		const nonce_action = 'form_spam_filter';
		const nonce_name = 'arm_nonce_check';
		const nonce_start_time = 'form_filter_st';
		const nonce_keyboard_press = 'form_filter_kp';
		
		var $nonce_fields;
		
	 function __construct() {	
		add_filter('is_to_validate_spam_filter', array($this, 'arf_check_spam_filter_fields'),10,2);
		add_shortcode('arf_spam_filters', array($this, 'arf_spam_filters_func'));
		add_filter('arf_reset_built_in_captcha',array($this,'arf_reset_built_in_captcha_key'),10,2);
		add_action('wp_ajax_arf_generate_captcha', array( $this, 'arf_generate_captcha_function'), 10);
		add_action('wp_ajax_nopriv_arf_generate_captcha', array( $this, 'arf_generate_captcha_function'), 10);
	}

	function arf_generate_captcha_function(){

		global $arfsettings;

		if( 1 == $arfsettings->hidden_captcha ){
			die;
		}

		global $maincontroller;

		if( !session_id() ){
			$maincontroller->arf_start_session( true );
		}


		$form_ids = !empty( $_POST['form_ids'] ) ? explode( ',', $_POST['form_ids'] ) : array();

		if( empty( $form_ids ) ){
			die;
		}

		global $armainhelper;

		$return_arr = array();

		foreach( $form_ids as $frm_data_id ){

			$frm_data = explode( '_', $frm_data_id );

			$form_id = $frm_data[0];

			$formRandomID = $form_id . '_' . $armainhelper->arf_generate_captcha_code('10');

			$captcha_code = $armainhelper->arf_generate_captcha_code('8');

			$_SESSION['ARF_VALIDATE_SCRIPT'] = true;
		    $_SESSION['ARF_FILTER_INPUT'][$formRandomID] = $captcha_code;


		    $return_arr[ $frm_data_id ] = array(
		    	'data_random_id' => $formRandomID,
		    	'data_submission_key' => $captcha_code,
		    	'data_key_validate' => false,
		    );
		}

		echo json_encode( $return_arr );
		die;

	}

	function arf_reset_built_in_captcha_key($return, $post_val){
		global $arfsettings;
		if( empty( $arfsettings ) ){
	  		$arfsettings = get_option('arf_options');
	  	}

	  	if( !isset( $_SESSION ) ){
	  		global $maincontroller;
	  		$maincontroller->arf_start_session( true );
	  	}
		if(1 == $arfsettings->hidden_captcha){
	  		return $return;
	  	}
		if( empty($post_val) ){
			$return['recaptcha_key'] = '';
		} else {
			global $armainhelper;
	        $form_id = $post_val['form_id'];
	        $frm_id = isset($post_val['form_random_key']) ? $post_val['form_random_key'] : '';
	        $possible_letters = '23456789bcdfghjkmnpqrstvwxyz';
	        $random_dots = 0;
	        $random_lines = 20;

	        $session_var = '';
	        $i = 0;
	        while ($i < 8) {
	            $session_var .= substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1);
	            $i++;
	        }
	        $_SESSION['ARF_FILTER_INPUT'][$frm_id] = $session_var;
	        $return['recaptcha_key'] = base64_encode($session_var.'~|~'.$form_id.'~|~'.$frm_id);
		}
		return $return;
	}
	
  	function arf_check_spam_filter_fields($validate = true,$form_key = ''){
	  	global $arfsettings;
	  	
	  	if( empty( $arfsettings ) ){
	  		$arfsettings = get_option('arf_options');
	  	}

  		global $maincontroller;
  		$maincontroller->arf_start_session( true );

	  	if(1 == $arfsettings->hidden_captcha){
	  		return true;
	  	}
		$is_form_key = $arf_is_removed_field = true;

		/* Return false if session is blank. */
		
		if( !isset($_SESSION['ARF_FILTER_INPUT']) && isset($_SESSION['ARF_VALIDATE_SCRIPT']) && $_SESSION['ARF_VALIDATE_SCRIPT'] == true ){
			$arf_is_removed_field = false;
		}

		/* Return false if form key not found */
		if( $form_key == '' || (isset($_SESSION['ARF_FILTER_INPUT']) && !array_key_exists($form_key, $_SESSION['ARF_FILTER_INPUT'])) ){
			$is_form_key = false;
		}
		/* Get dynamic generated field */
		$field_name = isset($_SESSION['ARF_FILTER_INPUT'][$form_key]) ? $_SESSION['ARF_FILTER_INPUT'][$form_key] : '';
		
		if( isset($_REQUEST[$field_name]) ){ 
			$field_value = $_REQUEST[$field_name];
			$arf_is_dynamic_field = true;
			/* Check if dynamic generated field value. Return if modified */
			if( $field_value != "" || !empty($field_value) || $field_value != NULL ){
				$arf_is_dynamic_field = false;
			}
		} else {
			$arf_is_dynamic_field = false;
		}

		$is_removed_field_exists = false;
		/* Get dynamically removed field. Return if found */
		if( isset($_REQUEST['arf_filter_input']) || isset($_POST['arf_filter_input']) || isset($_GET['arf_filter_input']) ){
			$arf_is_removed_field = false;
			$is_removed_field_exists = true;
		}

		/* Remove old keys from stored session */
		unset($_SESSION['ARF_FILTER_INPUT'][$form_key]);

		/* Check if Script is Executed. Bypass if script is not executed due to suPHP extension or blocked iframe */
		if( !isset($_SESSION['ARF_VALIDATE_SCRIPT']) || $_SESSION['ARF_VALIDATE_SCRIPT'] == false ){
			$arf_is_dynamic_field = true;
			$is_form_key = true;
		}

		$validateNonce = $validateReferer = $in_time = $is_user_keyboard = false;
		if (isset($_REQUEST) && isset($_REQUEST[self::nonce_name])) {
			$referer = $this->validateReferer();
			if ($referer['pass'] === true && $referer['hasReferrer'] === true) {
				$validateReferer = true;
			}
			/* Check Form Submission Time. */
			$in_time = $this->validateTimedFormSubmission();
			/* Check Keyboard Use */
			$is_user_keyboard = $this->validateUsedKeyboard();
		}
		$validateNonce = true;
		
		if ($validateNonce && $validateReferer && $in_time && $is_user_keyboard && $is_form_key && $arf_is_dynamic_field && $arf_is_removed_field )	{
			$validate = true;
		} else if( !$is_user_keyboard && $validateNonce && $validateReferer && $in_time && $is_form_key && $arf_is_dynamic_field && $arf_is_removed_field ){
			$validate = true;
		} else {
			$validate = false;
		}
		return $validate;
    }
    
    function validateReferer()
    {
	if (isset($_SERVER['HTTPS'])) {
		$protocol = "https://";
	} else {
		$protocol = "http://";
	}
	$absurl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
	$absurlParsed = parse_url($absurl);
	$result["pass"] = false;
	$result["hasReferrer"] = false;
	$httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	if (isset($httpReferer)) {
		$refererParsed = parse_url($httpReferer);
		if (isset($refererParsed['host'])) {
			$result["hasReferrer"] = true;
			$absUrlRegex = '/' . strtolower($absurlParsed['host']) . '/';
			$isRefererValid = preg_match($absUrlRegex, strtolower($refererParsed['host']));
			if ($isRefererValid == 1) {
				$result["pass"] = true;
			}
		} else {
			$result["status"] = "Absolute URL: " . $absurl . " Referer: " . $httpReferer;
		}
	} else {
		$result["status"] = "Absolute URL: " . $absurl . " Referer: " . $httpReferer;
	}
	return $result;
    }
    
    function validateTimedFormSubmission($formContents=array())
    {
	$in_time = false;
	if(empty($formContents[self::nonce_start_time])) {
		$formContents[self::nonce_start_time] = isset($_REQUEST[self::nonce_start_time]) ? $_REQUEST[self::nonce_start_time] : '';
	}
	if(isset($formContents[self::nonce_start_time]))
	{
		$displayTime = (int)$formContents[self::nonce_start_time] - 14921;
		$submitTime = time();
		$fillOutTime = $submitTime - $displayTime;
		/* Less than 3 seconds */
		if ($fillOutTime < 3) {
			$in_time = false;
		} else {
			$in_time = true;
		}
	}
	return $in_time;
    }
    function validateUsedKeyboard($formContents=array())
    {
	$is_user_keyboard = false;
	if (empty($formContents[self::nonce_keyboard_press])) {
	    $formContents[self::nonce_keyboard_press] = isset($_REQUEST[self::nonce_keyboard_press]) ? $_REQUEST[self::nonce_keyboard_press] : '';
	}
	if (isset($formContents[self::nonce_keyboard_press])) {
		if (is_numeric($formContents[self::nonce_keyboard_press]) !== false) {
			$is_user_keyboard = true;
		}
	}
	return $is_user_keyboard;
    }
     
    function arf_spam_filters_func($atts, $content = "")
    {
    	global $arfsettings;
    	if( empty( $arfsettings ) ){
	  		$arfsettings = get_option('arf_options');
	  	}

    	if(1 == $arfsettings->hidden_captcha){
	  		return '';
	  	}
	    $defaults = array(
		    'var' => '',
	    );
	    /* Extract Shortcode Attributes */
	    $opts = shortcode_atts( $defaults, $atts, 'spam_filters' );
	    extract( $opts );

	    $content .= $this->add_form_fields();

	    return do_shortcode($content);
    }
    
    function add_form_fields()
    {
	$this->nonce_fields = '<input type="hidden" data-jqvalidate="false" class="kpress" value="" />';
	$this->nonce_fields .= '<input type="hidden" data-jqvalidate="false" class="stime" value="'. (time()+14921) .'" />';
	$this->nonce_fields .= '<input type="hidden" data-jqvalidate="false" data-id="nonce_start_time" class="nonce_start_time" value="'.self::nonce_start_time.'" />';
	$this->nonce_fields .= '<input type="hidden" data-jqvalidate="false" data-id="nonce_keyboard_press" class="nonce_keyboard_press" value="'.self::nonce_keyboard_press.'" />';
	$this->nonce_fields .= '<input type="hidden" data-jqvalidate="false" data-id="'.self::nonce_name.'" name="'.self::nonce_name.'" value="'.wp_create_nonce(self::nonce_action).'" />';
	return $this->nonce_fields;
    }

}