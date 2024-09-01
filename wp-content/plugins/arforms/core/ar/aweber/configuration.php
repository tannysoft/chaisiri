<?php

const AUTHORIZE_URL = "https://auth.aweber.com/oauth2/authorize";
const TOKEN_URL = "https://auth.aweber.com/oauth2/token";

global $wpdb, $arfsiteurl, $MdlDb;

$autores_type = get_option('arf_ar_type');
$autores_type['aweber_type'] = 1;
$arr_new1 = $autores_type;
update_option('arf_ar_type', $arr_new1);
update_option('arf_current_tab', 'autoresponder_settings');

$clientId = ARF_AWEBER_CLIENT_ID;

$redirectUri = 'urn:ietf:wg:oauth:2.0:oob';

if( empty( $_POST['verify_code'] ) && empty( $_POST['refresh_code'] ) ){
    $verifierBytes = random_bytes(64);
    $codeVerifier = rtrim(strtr(base64_encode($verifierBytes), "+/", "-_"), "=");

    $challengeBytes = hash("sha256", $codeVerifier, true);
    $codeChallenge = rtrim(strtr(base64_encode($challengeBytes), "+/", "-_"), "=");

    update_option( 'arf_aweber_code_verifier', $codeVerifier );
    update_option( 'arf_aweber_code_challange', $codeChallenge );

    $state = uniqid();

    update_option( 'arf_aweber_state_code', $state );

    

    $scopes = array(
        "account.read",
        "list.read",
        "list.write",
        "subscriber.read",
        "subscriber.write",
        "email.read",
        "email.write",
        "subscriber.read-extended"
    );

    $authorizeQuery = array(
        "response_type" => "code",
        "client_id" => $clientId,
        "redirect_uri" => $redirectUri,
        "scope" => implode(" ",$scopes),
        "state" => $state,
        "code_challenge" => $codeChallenge,
        "code_challenge_method" => "S256"
    );

    header('location: ' . AUTHORIZE_URL . "?" . http_build_query($authorizeQuery));
    die;
} else {
    if( !empty( $_POST['verify_code'] ) ){
        $authorizationCode = !empty( $_POST['authorization_code'] ) ? $_POST['authorization_code'] : '';

        if( empty( $authorizationCode ) ){
            echo json_encode(
                array(
                    'error' => true,
                    'error_msg' => esc_html__('Authorization Code Not Found. Please re-authorize the Aweber and try again.', 'ARForms')
                )
            );
        } else {
            $codeVerifier = get_option( 'arf_aweber_code_verifier' );

            if( empty( $codeVerifier ) ){
                echo json_encode(
                    array(
                        'error' => true,
                        'error_msg' => esc_html__('Invalid Code. Please re-authorize the Aweber and try again.', 'ARForms')
                    )
                );
            } else {
                $tokenQuery = array(
                    "grant_type" => "authorization_code",
                    "code" => $authorizationCode,
                    "client_id" => $clientId,
                    "code_verifier" => $codeVerifier,
                );

                $tokenUrl = TOKEN_URL . "?" . http_build_query($tokenQuery);

                $response = wp_remote_post( $tokenUrl, array(
                    'timeout' => 45,
                ) );

                if( is_wp_error( $response ) ){
                    echo json_encode(
                        array(
                            'error' => true,
                            'error_msg' => esc_html__('Invalid Code. Please re-authorize the Aweber and try again.', 'ARForms')
                        )
                    );
                } else {
                    $body = $response['body'];
                    $creds = json_decode($body, true);

                    $accessToken = $creds['access_token'];
                    $refreshToken = $creds['refresh_token'];
                    $expire_in = time() + $creds['expires_in'];

                    set_transient( 'arf_aweber_access_token', $accessToken, $creds['expires_in'] );

                    $acc_headers = array(
                        'User-Agent' => 'AWeber-PHP-code-sample/1.0',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken,
                    );

                    $acc_url = 'https://api.aweber.com/1.0/accounts';

                    $acc_response = wp_remote_get( $acc_url,
                        array(
                            'method' => 'GET', 
                            'headers' => $acc_headers,
                            'timeout' => 5000
                        )
                    );


                    $acc_response = json_decode($acc_response['body']);
                    $acc_entries = $acc_response->entries;
                    $acc_id = $acc_entries[0]->id;

                    $lists_url = "https://api.aweber.com/1.0/accounts/".$acc_id."/lists";

                    $lists_response = wp_remote_get(
                        $lists_url,
                        array(
                            'method' => 'GET', 
                            'headers' => $acc_headers,
                            'timeout' => 5000
                        )
                    );

                    $lists_response = json_decode($lists_response['body']);
                    $lists_entries = $lists_response->entries;

                    $listname = $listid = '';
                    $first_list_id = '';
                    $x = 0;
                    foreach($lists_entries as $offset => $list) {
                        if( 0 == $x ){
                            $first_list_id = $list->id;
                        }
                        $listname .= $list->name."|";
                        $listid .= $list->id."|";
                        $x++;
                    }

                    if( $listname != "" && $listid != "" ){
                        $listingdetails = $listname."-|-".$listid;
                    }

                    $list_data = array(
                        'accessToken'   =>  $accessToken,
                        'refreshToken'  =>  $refreshToken,
                        'expires_in'     =>  $expire_in,
                        'acc_id'        =>  $acc_id
                    );

                    //$alldetails = $accessToken.'|'.$refreshToken.'|'.$acc_id;


                    $wpdb->update(
                        $MdlDb->autoresponder,
                        array(
                            'responder_api_key' =>  $authorizationCode,
                            'responder_list_id' =>  $listingdetails,
                            'responder_list'    =>  $first_list_id,
                            'list_data'         =>  maybe_serialize( $list_data ),
                            'is_verify'         =>  '1'
                        ),
                        array(
                            'responder_id' => '3'
                        )
                    );

                    $list_html = '<div class="sltstandard" style="float:none; display:inline;">';
                        
                        $aweber_lists = explode("-|-", $listingdetails);
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

                    delete_option('arf_reauthorize_aweber');

                    echo json_encode(
                        array(
                            'error' => false,
                            'aweber_lists' => $aweber_lists
                        )
                    );
                }
            }
        }
    } else {
        $refreshToken = $_POST['refreshToken'];
        $accessToken = $_POST['accessToken'];

        $tokenQuery = array(
            'client_id' => $clientId,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        );

        $tokenUrl = TOKEN_URL . "?" . http_build_query($tokenQuery);

        $response = wp_remote_post($tokenUrl);

        $body = $response['body'];

        $newCreds = json_decode($body, true);
        
        $accessToken = $newCreds['access_token'];
        $refreshToken = $newCreds['refresh_token'];
        $expire_in = time() + $newCreds['expires_in'];

        set_transient( 'arf_aweber_access_token', $accessToken, $newCreds['expires_in'] );

        $db_data = $wpdb->get_row( $wpdb->prepare( "SELECT list_data FROM `" . $MdlDb->autoresponder . "` WHERE responder_id = %d", 3 ) );

        $list_data = maybe_unserialize( $db_data->list_data );

        $updated_list_data = array(
            'accessToken'   =>  $newCreds['access_token'],
            'refreshToken'  =>  $newCreds['refresh_token'],
            'expires_in'     =>  ( time()  + $newCreds['expires_in'] ),
            'acc_id'        =>  $list_data['acc_id']
        );

        $wpdb->update(
            $MdlDb->autoresponder,
            array(
                'list_data' => maybe_serialize( $updated_list_data )
            ),
            array(
                'responder_id' => 3
            )
        );

        echo json_encode( $updated_list_data );

    } 
}
