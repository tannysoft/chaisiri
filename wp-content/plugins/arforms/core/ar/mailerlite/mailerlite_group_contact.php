<?php
$get_url = 'https://api.mailerlite.com/api/v2/groups';

$sub_headers = array(
	'Content-Type' => 'application/json',
    'x-mailerlite-apikey' => $api_key,
	);

$mailerlitegroupsApi = wp_remote_get( $get_url,
    array(
        'method' => 'GET', 
        'headers' => $sub_headers,
        'timeout' => 5000
    )
);

if ( $mailerlitegroupsApi['response']['code'] != 200 ) {
	$mailerliteGroupsList[0]['error'] = $mailerlitegroupsApi['response']['message'];;
	return $mailerliteGroupsList;
}

?>