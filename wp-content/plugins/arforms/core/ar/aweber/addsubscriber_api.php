<?php

global $wpdb,$MdlDb;

$res = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$MdlDb->autoresponder." WHERE responder_id=%d",3));
$res = $res[0];

$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$MdlDb->ar." WHERE frm_id = %d", $fid), 'ARRAY_A' );
		
$arr_aweber 	= isset($data[0]['aweber'])?maybe_unserialize($data[0]['aweber']):array();

$responder_api_key = stripslashes( stripslashes_deep( $arr_aweber['type_val'] ) );
	
if( $responder_api_key != '' && 1 == $res->is_verify ){

	$temp_data = isset($res->list_data)?maybe_unserialize($res->list_data):array();

	$accessToken      = $temp_data['accessToken'];		 	# put your credentials here
	$account_id     = $temp_data['acc_id']; 				# put the Account ID here
	$list_id        = $responder_api_key; 					# put the List ID here
	$refreshToken = $temp_data['refreshToken'];
	
	$add_sub_url = "https://api.aweber.com/1.0/accounts/".$account_id."/lists/".$list_id."/subscribers";

	$sub_headers = array(
					'Content-Type' => 'application/json',
				    'Authorization' => 'Bearer ' . $accessToken,
				   );

	$params = array(
				'email' => $email,
				'name' => $fname." ".$lname,
				);

	$params = apply_filters('arf_aweber_additional_fields_from_outside',$params,$fid,$arr_aweber);

	if( isset( $params['custom_fields'] ) && empty( $params['custom_fields'] ) ){
		unset( $params['custom_fields'] );
	}

	$params = json_encode($params);

	$request_string = array(
        'method' => 'POST',
        'headers' => $sub_headers,
        'body' => $params,
        'timeout' => 5000
    ); 	
	 
	$add_subscriber = wp_remote_post( $add_sub_url, $request_string );
}
?>