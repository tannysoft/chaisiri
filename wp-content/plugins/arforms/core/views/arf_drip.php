<?php
    
$arf_drip = new arf_drip();

class arf_drip {
    
    function __construct() {

        add_action('arfafterinstall', array($this, 'arf_add_drip_afterinstall'), 10);

        add_action('arf_autoresponder_global_setting_block', array($this, 'arf_add_drip_global_setting_block'), 10, 2);
        
        add_action('arf_autoresponder_out_side_email_marketing_tools_update', array($this, 'arf_drip_update_api_data'), 10, 1);
        
        add_action('arf_email_marketers_tab_outside', array($this, 'arf_drip_logo'), 10);

        add_action('arf_email_marketers_tab_container_outside', array($this, 'arf_render_drip_block'), 10, 5);

        add_action('arfafterupdateform', array($this, 'arf_drip_after_form_save'), 10, 4);

        add_action('arfaftercreateentry', array($this, 'arf_drip_after_create_entry'), 10, 2);

        add_filter('arf_current_autoresponse_set_outside', array($this, 'arf_set_current_autoresponse_drip'), 10, 2);

        add_action('arf_autoresponder_ref_update', array($this, 'arforms_drip_reference_update'), 14, 3);

        add_action('arf_autoresponder_after_insert', array($this, 'arforms_drip_save_form_data'), 14, 2);

        add_action('arf_autoresponder_after_update', array($this, 'arforms_drip_save_form_data'), 14, 2);

        
    }

    function arf_add_drip_afterinstall() {
        global $wpdb, $MdlDb;

        $wpdb->query("ALTER TABLE " . $MdlDb->ar . "  ADD `drip` TEXT NOT NULL");

        $get_responder_id = $wpdb->get_row($wpdb->prepare("SELECT responder_id FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 18));

        if (!isset($get_responder_id->responder_id) || $get_responder_id->responder_id != 18) {
            $wpdb->query("INSERT INTO " . $MdlDb->autoresponder . " (responder_id) VALUES (18)");
        }

        $ar_types = get_option('arf_ar_type');

        $ar_types['drip_type'] = 1;

        $ar_types = $ar_types;

        update_option('arf_ar_type', $ar_types);
    }

    function arf_add_drip_global_setting_block( $autores_type, $setvaltolic ) {

        global $wpdb, $MdlDb, $maincontroller;

        $drip_alldata = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d",18));
        $drip_data = new stdClass();
        if( count($drip_alldata) > 0 ){
            $drip_data = $drip_alldata[0];
        }

        ?>
            <table class="wp-list-table widefat post " style="margin:20px 0 20px 10px; border:none;">

                <tr>
                    <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                    <th style="background:none; border:none;height:98px;" colspan="2"><img alt='' src="<?php echo ARFURL; ?>/images/drip.png" align="absmiddle" height='38px' /></th>
                </tr>

                <tr>
                    <?php $autores_type['drip_type'] = ( isset($autores_type['drip_type']) && $autores_type['drip_type'] != '' ) ? $autores_type['drip_type'] : 1; ?>
                    <th style="width:18%; background:none; border:none;"></th>
                    <th id="th_drip" style="padding-left:5px;background:none; border:none;">
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div" >
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" class="arf_submit_action arf_custom_radio" id="drip_18" <?php if ($autores_type['drip_type'] == 1) echo 'checked="checked"'; ?>  name="drip_type" value="1" onclick="show_api('drip');" />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label for="drip_18"><?php echo addslashes(esc_html__('Using API', 'ARForms')); ?></label>
                            </span>
                        </div>
                    </th>

                </tr>

                <tr id="drip_api_tr1" <?php if ($autores_type['drip_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('API Token', 'ARForms')); ?></label></td>

                    <td style="padding-bottom:3px; padding-left:5px;">
                        <input type="text" name="drip_api" class="txtmodal1" <?php if ($setvaltolic != 1) { echo "readonly=readonly"; echo ' onclick="alert(\'Please activate license to set drip settings\');"'; } ?> id="drip_api" size="80" onkeyup="show_verify_btn('drip');" value="<?php echo isset($drip_data->responder_api_key) ? $drip_data->responder_api_key : ""; ?>" /> &nbsp; &nbsp;
                        <div class="arferrmessage" id="drip_api_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>
                    </td>
                </tr>

                <tr id="drip_api_tr1" <?php if ($autores_type['drip_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Account ID', 'ARForms')); ?></label></td>

                    <td style="padding-bottom:3px; padding-left:5px;">

                        <input type="text" name="drip_account_id" class="txtmodal1" <?php if ($setvaltolic != 1) { echo "readonly=readonly"; echo ' onclick="alert(\'Please activate license to set drip settings\');"'; } ?> id="drip_account_id" size="80" onkeyup="show_verify_btn('drip');" value="<?php echo isset($drip_data->consumer_secret) ? $drip_data->consumer_secret : ""; ?>" />
                        &nbsp; &nbsp;
                        <span id="drip_link" <?php if (isset($drip_data->is_verify) && $drip_data->is_verify == 1) { ?>style="display:none;"<?php } ?>>
                            <a href="javascript:void(0);" onclick="verify_autores('drip', '0');" class="arlinks"><?php echo addslashes(esc_html__('Verify', 'ARForms')); ?></a>
                        </span>
                        <span id="drip_loader" style="display:none;">
                            <div class="arf_imageloader" style="float: none !important;display:inline-block !important; "></div>
                        </span>
                        <span id="drip_verify" class="frm_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Verified', 'ARForms')); ?></span>
                        <span id="drip_error" class="frm_not_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Not Verified', 'ARForms')); ?></span>
                        <input type="hidden" name="drip_status" id="drip_status" value="<?php echo $drip_data->is_verify; ?>" />
                        <div class="arferrmessage" id="drip_account_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>
                </tr>


                <tr id="drip_api_tr2" <?php if ($autores_type['drip_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-top:3px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Campaign Name', 'ARForms')); ?></label></td>

                    <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;">
                        <span id="select_drip">
                            <div class="sltstandard" style="float:none;display:inline;">
                                <?php
                                $responder_list_option = array ( '' => esc_html__('Nothing Selected','ARForms'));
                                $selected_list_id = '';
                                $lists = isset($drip_data->responder_list_id) ? maybe_unserialize($drip_data->responder_list_id) : array();
                                if ($lists != '' and count($lists) > 0) {
                                    if (is_array($lists)) {
                                        foreach ($lists as $key => $list) {
                                            if ($drip_data->responder_list != '') {
                                                if ($drip_data->responder_list == $list['id']) {
                                                    $selected_list_id = $list['id'];
                                                    $responder_list_option[$list['id']] = $list['name'];
                                                }
                                            } else {
                                                if ($key == 0) {
                                                    $selected_list_id = $list['id'];
                                                    $responder_list_option[$list['id']] = $list['name'];
                                                }
                                            }
                                            $responder_list_option[$list['id']] = $list['name'];
                                        }
                                    }
                                }

                                echo $maincontroller->arf_selectpicker_dom( 'drip_listid', 'drip_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );
                                ?>
                            </div></span>

                        <div id="drip_del_link" style="padding-left:5px; margin-top:10px; clear: both; <?php if ($drip_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">
                            <a href="javascript:void(0);" onclick="action_autores('refresh', 'drip');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                            &nbsp;  &nbsp;  &nbsp;  &nbsp;
                            <a href="javascript:void(0);" onclick="action_autores('delete', 'drip');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                        </div>


                    </td>

                </tr>

                <tr>
                    <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                </tr>


            </table>
        <?php

    }

    function arf_drip_update_api_data($arf_drip_data) {
        global $wpdb, $MdlDb;
        $arf_drip_api = isset($arf_drip_data['drip_api']) ? $arf_drip_data['drip_api'] : '';
        $arf_drip_secret = isset($arf_drip_data['drip_account_id'] ) ? $arf_drip_data['drip_account_id'] : '';
        $arf_drip_listid = isset($arf_drip_data['drip_listid']) ? $arf_drip_data['drip_listid'] : '';
        $arf_drip_data = apply_filters('arf_trim_values',$arf_drip_data);
        
        if ( isset($arf_drip_data['drip_type']) && $arf_drip_data['drip_type'] == 1 ) {
            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_drip_api, 'responder_list' => $arf_drip_listid, 'consumer_secret' => $arf_drip_secret), array('responder_id' => '18'));
        }

    }

    function arf_drip_logo() {
        ?>
            <li class="arf_optin_tab_item" data-id="drip"><?php addslashes(esc_html_e('drip', 'ARForms')); ?></li>
        <?php 
    }

    function arf_render_drip_block($arfaction = '', $global_enable_ar = '', $current_active_ar = '', $data = '', $setvaltolic = '') {

        global $wpdb, $MdlDb, $maincontroller;

        $res = get_option('arf_ar_type');
        $res18 = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 18), 'ARRAY_A');
        if( count($res18) > 0){
            $res18 = $res18[0];
        }
        $drip_arr = isset($data[0]['drip']) ? maybe_unserialize($data[0]['drip']) : array();

        ?>
        <div class="arf_optin_tab_inner_container" id="drip">
            <div>
                <?php 
                $style = '';
                $style_gray = '';
                if(isset($drip_arr['enable']) && $drip_arr['enable'] == 1)
                {
                    $style = 'style="display:block;"';
                    $style_gray = 'style="display:none;"';
                } else{
                    $style = 'style="display:none;"';
                    $style_gray = 'style="display:block;"';
                }?>
                <div class="arf_optin_logo drip_original arfdrip" <?php echo $style;?>><img src="<?php echo ARFURL; ?>/images/drip.png" height="38px" /></div>
                <div class="arf_optin_logo drip_gray arfdrip" <?php echo $style_gray;?>><img src="<?php echo ARFURL; ?>/images/drip_gray.png" height="38px" /></div>
                <div class="arf_optin_checkbox arfdrip">
                    <div>
                        <label class="arf_js_switch_label">
                            <span></span>
                        </label>
                        <span class="arf_js_switch_wrapper">
                            <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_18" data-attr="drip" value="18" <?php
                            if (isset($res['drip_type']) && $res['drip_type'] == 2) {
                                echo 'disabled="disabled"';
                            }
                            ?> <?php if (isset($drip_arr['enable']) and $drip_arr['enable'] == 1) { echo "checked=checked"; } ?> onchange="show_setting('drip', '18');" <?php if ($setvaltolic != 1) { echo 'onclick="return false"'; } ?> />
                            <span class="arf_js_switch"></span>
                        </span>
                        <label class="arf_js_switch_label" for="autores_18">
                            <span>&nbsp;<?php addslashes(esc_html_e('Enable', 'ARForms')); ?></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="arf_option_configuration_wrapper drip_configuration_wrapper <?php echo (isset($drip_arr['enable']) && $drip_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                <br/><br/>
                <?php
                $rand_num = rand(1111, 9999);
                if (isset($res['drip_type']) && $res['drip_type'] == 1) {
                    ?>
                    <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                        <?php
                        if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and isset($arf_template_id) and $arf_template_id < 100 ) ) || (isset($drip_arr['enable']) and $drip_arr['enable'] == 0 )) {
                            ?>
                            <div id="autores-drip" class="autoresponder_inner_block" data-if="sadsa" >
                                <div class="textarea_space"></div>
                                <span class="lblstandard"><?php echo addslashes(esc_html__('Select Campaign Name', 'ARForms')); ?></span>
                                <div class="textarea_space"></div>
                                <div class="sltstandard">
                                    <?php
                                    $selected_list_id = "";
                                    $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                    $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')) );
                                    if( isset($res18['responder_list_id'])){
                                        $lists = maybe_unserialize($res18['responder_list_id']);
                                    } else {
                                        $lists = '';
                                    }
                                    if (is_array($lists) && count($lists) > 0) {
                                        $cntr = 0;
                                        foreach ($lists as $list) {
                                            if ($res18['responder_list'] == $list['id'] || $cntr == 0) {
                                                $selected_list_id = $list['id'];
                                                $selected_list_label = $list['name'];
                                            }

                                            $responder_list_option[$list['id']] = $list['name'];
                                            $cntr++;
                                        }
                                    }

                                    $drip_enable_class = (isset($drip_arr['enable']) && $drip_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                    if(isset($drip_arr['enable']) && $drip_arr['enable'] == 1){
                                        $drip_opt_disable = false;
                                    }else{
                                        $drip_opt_disable = true;
                                    }
                    
                                    $drip_attr = array();
                                    if( $setvaltolic != 1 ){
                                        $drip_attr = array( 'readonly' => 'readonly' );
                                    }

                                    echo $maincontroller->arf_selectpicker_dom( 'i_drip_list', 'i_drip_list', $drip_enable_class, 'width:170px;', $selected_list_id, $drip_attr, $responder_list_option, false, array(), $drip_opt_disable, array(), false, array(), true );

                                    ?>
                                    
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div id="autores-drip" class="autoresponder_inner_block">
                                <div class="textarea_space"></div>
                                <span class="lblstandard"><?php echo addslashes(esc_html__('Select Campaign Name', 'ARForms')); ?></span>
                                <div class="textarea_space"></div>
                                <div class="sltstandard">
                                    <?php
                                    $selected_list_id = "";
                                    $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                    $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')) );
                                    $lists = isset($res18['responder_list_id']) ?  maybe_unserialize($res18['responder_list_id']) : '';
                                    
                                    $default_drip_select_list = isset($res18['responder_list']) ? $res18['responder_list'] : '';
                                    $selected_list_id_drip = (isset($drip_arr['type_val']) && $drip_arr['type_val'] != '' ) ? $drip_arr['type_val'] : $default_drip_select_list;
                                    if (is_array($lists) && count($lists) > 0) {
                                        $cntr = 0;
                                        foreach ($lists as $list) {
                                            if ($selected_list_id_drip == $list['id'] || $cntr == 0) {
                                                $selected_list_id = $list['id'];
                                                $selected_list_label = $list['name'];
                                            }

                                            $responder_list_option[$list['id']] = $list['name'];

                                            $cntr++;
                                        }
                                    }

                                    $drip_enable_class = (isset($drip_arr['enable']) && $drip_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                    if(isset($drip_arr['enable']) && $drip_arr['enable'] == 1){
                                        $drip_opt_disable = false;
                                    }else{
                                        $drip_opt_disable = true;
                                    }
                    
                                    $drip_attr = array();
                                    if( $setvaltolic != 1 ){
                                        $drip_attr = array( 'readonly' => 'readonly' );
                                    }

                                    echo $maincontroller->arf_selectpicker_dom( 'i_drip_list', 'i_drip_list', $drip_enable_class, 'width:170px;', $selected_list_id, $drip_attr, $responder_list_option, false, array(), $drip_opt_disable, array(), false, array(), true );

                                    ?>
                                    
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                <?php }
                ?>
            </div>
        </div>
        <?php

    }
    function arf_drip_after_form_save($id, $values, $create_link, $is_ref_form) {

        global $wpdb, $armainhelper, $MdlDb;

        $get_enabled_ar = $wpdb->get_results($wpdb->prepare("SELECT enable_ar FROM " . $MdlDb->ar . " WHERE frm_id = %d ",$id));

        $enable_ar = maybe_unserialize($get_enabled_ar[0]->enable_ar );

        if (isset($values['autoresponders']) && is_array($values['autoresponders'])) {
            if (in_array(18, $values['autoresponders'])) {

                $drip_entry['enable'] = 1;
                $drip_entry['type'] = 0;
                $drip_entry['type_val'] = isset($values['i_drip_list']) ? $values['i_drip_list'] : 0;

                $drip_entries = maybe_serialize($drip_entry);

                $wpdb->query("UPDATE " . $MdlDb->ar . " SET drip = '" . $drip_entries . "' WHERE frm_id = " . $id);

                $enable_ar['drip'] = 1;
            } else {
                $drip_entry['enable'] = 0;
                $drip_entry['type'] = 0;
                $drip_entry['type_val'] = 0;

                $drip_entries = maybe_serialize($drip_entry);

                $wpdb->query("UPDATE " . $MdlDb->ar . " SET drip = '" . $drip_entries . "' WHERE frm_id = " . $id);
                $enable_er['drip'] = 0;
            }

            $enable_ar = maybe_serialize($enable_ar);

            $wpdb->query("UPDATE " . $MdlDb->ar . " SET enable_ar = '" . $enable_ar . "' WHERE frm_id = " . $id);
        }
        return '';
    }

    function arf_drip_after_create_entry( $entry_id, $form_id ){

        if( $entry_id == '' || $form_id == '' ){
            return;
        }

        if( $form_id < 0 ){
            $form_id = abs( $form_id );
        }

        global $email, $fname, $lname, $fid, $wpdb, $MdlDb ;

        $results = wp_cache_get('arf_drip_result_'.$form_id);
        if( false == $results ){
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d", $form_id));
            wp_cache_set('arf_drip_result_'.$form_id, $results);
        }

        $form_options = maybe_unserialize( $results[0]->options );

        $check_condition_on_subscription = true;
        if (isset($form_options['conditional_subscription']) && $form_options['conditional_subscription'] == 1) {
            $check_condition_on_subscription = apply_filters('arf_check_condition_on_subscription', $form_options, $entry_id);
        }

        if( !$check_condition_on_subscription ){
            return;
        }

        $res = wp_cache_get('arf_drip_'.$form_id);
        if( false == $res ){
            $res = $wpdb->get_results($wpdb->prepare("SELECT `drip` FROM " .$MdlDb->ar." WHERE frm_id = %d", $form_id), 'ARRAY_A');
            wp_cache_set('arf_drip_'.$form_id, $res);
        }
        $ar_drip = isset($res[0]) ? maybe_unserialize( $res[0]['drip'] ) : '';
        $list_id = isset($ar_drip['type_val'])?$ar_drip['type_val']:'';

        if ( isset($ar_drip['enable']) && $ar_drip['enable'] == 1 ) {
            $reponder_arr = $wpdb->get_row($wpdb->prepare("SELECT `responder_api_key`,`consumer_secret` FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 18), 'ARRAY_A');
           
            $api_key = $reponder_arr['responder_api_key'];
            $account_id = $reponder_arr['consumer_secret'];

            $subscriber = array(
                'subscribers' => array(
                    array(
                        'email' => $email,
                        'first_name' => $fname,
                        'last_name' => $lname
                    )
                )
            );


            $sub_url = 'https://api.getdrip.com/v2/'.$account_id.'/subscribers';

            $subscribe = wp_remote_post(
                $sub_url,
                array(
                    'timeout' => 5000,
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode( $api_key ),
                        'Content-Type'  => 'application/json',
                    ),
                    'body' => json_encode( $subscriber )
                )
            );

            if( is_wp_error( $subscribe ) ){

            } else {
                $response = json_decode( $subscribe['body'] );
                $subscriber_obj = $response->subscribers[0];
                $subscriber_email = $subscriber_obj->email;

                $drip_campaign_url = 'https://api.getdrip.com/v2/'.$account_id.'/campaigns/'.$list_id.'/subscribers';

                $subscr_obj = array(
                    'subscribers' => array(
                        array(
                            'email' => $subscriber_email
                        )
                    )
                );

                $addtocampaign = wp_remote_post(
                    $drip_campaign_url,
                    array(
                        'timeout' => 5000,
                        'headers' => array(
                            'Authorization' => 'Basic ' . base64_encode( $api_key ),
                            'Content-Type' => 'application/json'
                        ),
                        'body' => json_encode( $subscr_obj )
                    )
                );
            }
        }

    }

    function arf_set_current_autoresponse_drip($current_active_ar, $data) {
        $drip_arr = isset($data[0]['drip']) ? maybe_unserialize($data[0]['drip']) : '' ;

        if (isset($drip_arr['enable']) && $drip_arr['enable'] == 1) {
            $current_active_ar = 'drip';
        }
        return $current_active_ar;
    }

    function arforms_drip_save_form_data($id, $data) {
        global $wpdb, $MdlDb;

        $drip_arr = array();
        $type = get_option('arf_ar_type');
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 18), 'ARRAY_A');
        if (isset($data['autoresponders']) && in_array('18', $data['autoresponders'])) {
            $drip_arr['enable'] = 1;
        } else {
            $drip_arr['enable'] = 0;
        }
        $drip_arr = apply_filters('arf_trim_values',$drip_arr);
        $ar_drip = maybe_serialize($drip_arr);
        $res = $wpdb->update($MdlDb->ar, array('drip' => $ar_drip), array('frm_id' => $id));

    }

    function arforms_drip_reference_update($id, $res_rec, $resrpw) {
        global $wpdb, $MdlDb;
        $update = $wpdb->query($wpdb->prepare("update " . $MdlDb->ar . " set drip = '%s' where frm_id = %d", $res_rec["drip"], $resrpw));
    }
}

?>