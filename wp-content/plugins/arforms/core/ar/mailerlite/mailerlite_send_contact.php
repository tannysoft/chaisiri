<?php

global $email, $fname, $lname, $wpdb, $fid, $MdlDb ;

$res = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$MdlDb->autoresponder." WHERE responder_id=%d",14));
$res = $res[0];
$api_key = $res->responder_api_key;
$list_id = $res->responder_list;

$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$MdlDb->ar." WHERE frm_id = %d", $fid), 'ARRAY_A' );
$arr_mailerlite = maybe_unserialize( $data[0]['mailerlite'] );
$responder_list_id = isset($arr_mailerlite['type_val'])?$arr_mailerlite['type_val']:'';

$responder_list_id = ($responder_list_id != '') ? $responder_list_id : $list_id;

if (!empty($responder_list_id) && !empty($api_key)) {

		if (isset($email) && strlen($email) > 1) 
		{			

			$get_url = 'https://api.mailerlite.com/api/v2/subscribers/search?query='.$email;

			$sub_headers = array(
				'Content-Type' => 'application/json',
			    'x-mailerlite-apikey' => $api_key,
		   	);

            $get_response = wp_remote_get( $get_url,
                array(
                    'method' => 'GET', 
                    'headers' => $sub_headers,
                    'timeout' => 5000
                )
            );
            
            if ( empty( json_decode( $get_response['body'], true ) ) ) {
            	$add_sub_url = "https://api.mailerlite.com/api/v2/groups/".$responder_list_id."/subscribers";

				$params = array(
					'email' => $email,
					'name' => $fname,
					'fields'=> array(
						'last_name'	=> $lname
					)
				);

				$request_string = array(
					'timeout' => 5000,
			        'headers' => $sub_headers,
			        'body' => json_encode( $params )
			    ); 

			    $add_subscriber = wp_remote_post( $add_sub_url, $request_string );
            } else {
            	$subscriberID = '';
            	foreach ( json_decode( $get_response['body'], true ) as $key=>$val ) {
            		$subscriberID = $val['id'];
            		break;
            	}

            	$edit_sub_url = "https://api.mailerlite.com/api/v2/groups/".$responder_list_id."/subscribers/".$subscriberID;

				$params = array(
					'email' => $email,
					'name' => $fname,
					'fields'=> array(
						'last_name'	=> $lname
					)
				);

				$request_string = array(
					'method'	=> 'PUT',
					'timeout'	=> 5000,
			        'headers'	=> $sub_headers,
			        'body'		=> json_encode( $params )
			    ); 

			    $edit_subscriber = wp_remote_post( $edit_sub_url, $request_string );
            }


		}
}
?>