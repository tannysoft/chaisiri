<?php
        global $MdlDb;
        
	$res = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$MdlDb->autoresponder." WHERE responder_id=%d",9));$res = $res[0];
	
	$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$MdlDb->ar." WHERE frm_id = %d", $fid), 'ARRAY_A' );
			
   	$arr_constant_contact 	= maybe_unserialize( $data[0]['constant_contact'] );

	$responder_api_key = stripslashes( stripslashes_deep( $arr_constant_contact['type_val'] ) );		
	
	$formdata = str_replace('"',"'",$responder_api_key);
	
	
	$formdata_hiddn = explode("type='hidden'",$formdata);
	$count_formdata_hiddn = count($formdata_hiddn);
	
	$fields = array();
	for($i=1;$i<count($formdata_hiddn);$i++)
	{
		$findhiddn_name = strpos($formdata_hiddn[$i],">");
		$formdata_h=trim(substr($formdata_hiddn[$i],0,$findhiddn_name));
		
		$formdata_h_name = explode("name='",$formdata_h);
		$formdata_h_name_f = strpos($formdata_h_name[1],"'");
		$formdata_h_name_f1 = substr($formdata_h_name[1], 0, $formdata_h_name_f);
		
		$formdata_h_value = explode("value='",$formdata_h);
		$formdata_h_value_f = strpos($formdata_h_value[1],"'");
		$formdata_h_value_f1 = substr($formdata_h_value[1], 0, $formdata_h_value_f);
		$fields[$formdata_h_name_f1] = $formdata_h_value_f1;
	}
	
	$fields['ea'] = $email;
	
		$get_urlexp = explode("action='",$formdata);	
		$get_url_pos = strpos($get_urlexp[1],"'");		
	
		$url = substr($get_urlexp[1],0,$get_url_pos);	
	 
		$response = wp_remote_post( $url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'sslverify' => false,
			'headers' => array(),
			'body' => $fields,
			'cookies' => array()
			)
		);
?>