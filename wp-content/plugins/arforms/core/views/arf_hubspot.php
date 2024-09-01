<?php
	
$arf_hubspot = new arf_hubspot();

class arf_hubspot {
	
	function __construct() {

		add_action('arfafterinstall', array($this, 'arf_add_hubspot_afterinstall'), 10);

		add_action('arf_autoresponder_global_setting_block', array($this, 'arf_add_hubspot_global_setting_block'), 10, 2);
		add_action('arf_autoresponder_out_side_email_marketing_tools_update', array($this, 'arf_hubspot_update_api_data'), 10, 1);
		add_action('arf_email_marketers_tab_outside', array($this, 'arf_hubspot_logo'), 10);

		add_action('arf_email_marketers_tab_container_outside', array($this, 'arf_render_hubspot_block'), 10, 5);
		add_action('arfafterupdateform', array($this, 'arf_hubspot_after_form_save'), 10, 4);

		add_action('arfaftercreateentry', array($this, 'arf_hubspot_after_create_entry'), 10, 2);

		add_filter('arf_current_autoresponse_set_outside', array($this, 'arf_set_current_autoresponse_hubspot'), 10, 2);

		add_action('arf_autoresponder_ref_update', array($this, 'arforms_hubspot_reference_update'), 14, 3);

		add_action('arf_autoresponder_after_insert', array($this, 'arforms_hubspot_save_form_data'), 14, 2);

        add_action('arf_autoresponder_after_update', array($this, 'arforms_hubspot_save_form_data'), 14, 2);

		
	}

	function arf_add_hubspot_afterinstall() {
		global $wpdb, $MdlDb;

        $wpdb->query("ALTER TABLE " . $MdlDb->ar . "  ADD `hubspot` TEXT NOT NULL");

        $get_responder_id = $wpdb->get_row($wpdb->prepare("SELECT responder_id FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 15));

        if (!isset($get_responder_id->responder_id) || $get_responder_id->responder_id != 15) {
            $wpdb->query("INSERT INTO " . $MdlDb->autoresponder . " (responder_id) VALUES (15)");
        }

        $ar_types = get_option('arf_ar_type');

        $ar_types['hubspot_type'] = 1;

        $ar_types = $ar_types;

        update_option('arf_ar_type', $ar_types);
	}

	function arf_add_hubspot_global_setting_block( $autores_type, $setvaltolic ) {

        global $wpdb, $MdlDb, $maincontroller;

        $hubspot_alldata = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d",15));
        $hubspot_data = new stdClass();
        if( count($hubspot_alldata) > 0 ){
            $hubspot_data = $hubspot_alldata[0];
        }

        ?>
            <table class="wp-list-table widefat post " style="margin:20px 0 20px 10px; border:none;">

                <tr>
                    <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                    <th style="background:none; border:none;height:98px;" colspan="2"><img alt='' src="<?php echo ARFURL; ?>/images/hubspot.png" align="absmiddle" height='38px' /></th>
                </tr>

                <tr>
                    <?php $autores_type['hubspot_type'] = ( isset($autores_type['hubspot_type']) && $autores_type['hubspot_type'] != '' ) ? $autores_type['hubspot_type'] : 1; ?>
                    <th style="width:18%; background:none; border:none;"></th>
                    <th id="th_hubspot" style="padding-left:5px;background:none; border:none;">
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div" >
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" class="arf_submit_action arf_custom_radio" id="hubspot_15" <?php if ($autores_type['hubspot_type'] == 1) echo 'checked="checked"'; ?>  name="hubspot_type" value="1" onclick="show_api('hubspot');" />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label for="hubspot_15"><?php echo addslashes(esc_html__('Using API', 'ARForms')); ?></label>
                            </span>
                        </div>
                    </th>

                </tr>

                <tr id="hubspot_api_tr1" <?php if ($autores_type['hubspot_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('API Key', 'ARForms')); ?></label></td>

                    <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="hubspot_api" class="txtmodal1" <?php
                        if ($setvaltolic != 1) {
                            echo "readonly=readonly";
                            echo ' onclick="alert(\'Please activate license to set hubspot settings\');"';
                        }
                        ?> id="hubspot_api" size="80" onkeyup="show_verify_btn('hubspot');" value="<?php echo isset($hubspot_data->responder_api_key) ? $hubspot_data->responder_api_key : ""; ?>" /> &nbsp; &nbsp;
                        <span id="hubspot_link" <?php if (isset($hubspot_data->is_verify) && $hubspot_data->is_verify == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('hubspot', '0');" class="arlinks"><?php echo addslashes(esc_html__('Verify', 'ARForms')); ?></a></span>
                        <span id="hubspot_loader" style="display:none;"><div class="arf_imageloader" style="float: none !important;display:inline-block !important; "></div></span>
                        <span id="hubspot_verify" class="frm_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Verified', 'ARForms')); ?></span>
                        <span id="hubspot_error" class="frm_not_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Not Verified', 'ARForms')); ?></span>
                        <input type="hidden" name="hubspot_status" id="hubspot_status" value="<?php echo $hubspot_data->is_verify; ?>" />
                        <div class="arferrmessage" id="hubspot_api_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>
                </tr>


                <tr id="hubspot_api_tr2" <?php if ($autores_type['hubspot_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-top:3px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('List Name', 'ARForms')); ?></label></td>

                    <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;">
                    	<span id="select_hubspot">
                            <div class="sltstandard" style="float:none;display:inline;">
                                <?php
                                $responder_list_option = array( '' => esc_html__('Nothing Selected','ARForms') );
                                $selected_list_label = esc_html__('Nothing Selected','ARForms');
                                $selected_list_id = '';
                                $lists = isset($hubspot_data->responder_list_id) ? maybe_unserialize($hubspot_data->responder_list_id) : array();
                                if ($lists != '' and count($lists) > 0) {
                                    if (is_array($lists)) {
                                        foreach ($lists as $key => $list) {
                                            if ($hubspot_data->responder_list != '') {
                                                if ($hubspot_data->responder_list == $list['id']) {
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
                                
                                echo $maincontroller->arf_selectpicker_dom( 'hubspot_listid', 'hubspot_listid', '', 'width: 400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );
                                ?>
                            </div></span>


                        <div id="hubspot_del_link" style="padding-left:5px; margin-top:10px; clear: both; <?php if ($hubspot_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">
                            <a href="javascript:void(0);" onclick="action_autores('refresh', 'hubspot');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                            &nbsp;  &nbsp;  &nbsp;  &nbsp;
                            <a href="javascript:void(0);" onclick="action_autores('delete', 'hubspot');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                        </div>


                    </td>

                </tr>

                <tr>
                    <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                </tr>


            </table>
        <?php

    }

    function arf_hubspot_update_api_data($arf_hubspot_data) {
        global $wpdb, $MdlDb;
        $arf_hubspot_api = isset($arf_hubspot_data['hubspot_api']) ? $arf_hubspot_data['hubspot_api'] : '';
        $arf_hubspot_listid = isset($arf_hubspot_data['hubspot_listid']) ? $arf_hubspot_data['hubspot_listid'] : '';
        $arf_hubspot_data = apply_filters('arf_trim_values',$arf_hubspot_data);
        
        if ( isset($arf_hubspot_data['hubspot_type']) && $arf_hubspot_data['hubspot_type'] == 1 ) {
            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_hubspot_api, 'responder_list' => $arf_hubspot_listid), array('responder_id' => '15'));
        }

    }

    function arf_hubspot_logo() {
        ?>
            <li class="arf_optin_tab_item" data-id="hubspot"><?php addslashes(esc_html_e('HubSpot', 'ARForms')); ?></li>
        <?php 
    }

    function arf_render_hubspot_block($arfaction = '', $global_enable_ar = '', $current_active_ar = '', $data = '', $setvaltolic = '') {

        global $wpdb, $MdlDb, $maincontroller;

        $res = get_option('arf_ar_type');
        $res15 = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 15), 'ARRAY_A');
        if( count($res15) > 0){
            $res15 = $res15[0];
        }
        $hubspot_arr = isset($data[0]['hubspot']) ? maybe_unserialize($data[0]['hubspot']) : array();

        ?>
        <div class="arf_optin_tab_inner_container" id="hubspot">
            <div>
                <?php 
                $style = '';
                $style_gray = '';
                if(isset($hubspot_arr['enable']) && $hubspot_arr['enable'] == 1)
                {
                    $style = 'style="display:block;"';
                    $style_gray = 'style="display:none;"';
                } else{
                    $style = 'style="display:none;"';
                    $style_gray = 'style="display:block;"';
                }?>
                <div class="arf_optin_logo hubspot_original arfhubspot" <?php echo $style;?>><img src="<?php echo ARFURL; ?>/images/hubspot.png" height="38px" /></div>
                <div class="arf_optin_logo hubspot_gray arfhubspot" <?php echo $style_gray;?>><img src="<?php echo ARFURL; ?>/images/hubspot_gray.png" height="38px" /></div>
                <div class="arf_optin_checkbox arfhubspot">
                    <div>
                        <label class="arf_js_switch_label">
                            <span></span>
                        </label>
                        <span class="arf_js_switch_wrapper">
                            <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_15" data-attr="hubspot" value="15" <?php
                            if (isset($res['hubspot_type']) && $res['hubspot_type'] == 2) {
                                echo 'disabled="disabled"';
                            }
                            ?> <?php if (isset($hubspot_arr['enable']) and $hubspot_arr['enable'] == 1) { echo "checked=checked"; } ?> onchange="show_setting('hubspot', '15');" <?php if ($setvaltolic != 1) { echo 'onclick="return false"'; } ?> />
                            <span class="arf_js_switch"></span>
                        </span>
                        <label class="arf_js_switch_label" for="autores_15">
                            <span>&nbsp;<?php addslashes(esc_html_e('Enable', 'ARForms')); ?></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="arf_option_configuration_wrapper hubspot_configuration_wrapper <?php echo (isset($hubspot_arr['enable']) && $hubspot_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                <br/><br/>
                <?php
                $rand_num = rand(1111, 9999);
                if (isset($res['hubspot_type']) && $res['hubspot_type'] == 1) {
                    ?>
                    <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                        <?php
                        if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and isset($arf_template_id) and $arf_template_id < 100 ) ) || (isset($hubspot_arr['enable']) and $hubspot_arr['enable'] == 0 )) {
                            ?>
                            <div id="autores-hubspot" class="autoresponder_inner_block" data-if="sadsa" >
                                <div class="textarea_space"></div>
                                <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                <div class="textarea_space"></div>
                                <div class="sltstandard">
                                    <?php
                                    $selected_list_id = "";
                                    $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                    $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')) );
                                    
                                    if(isset($res15['responder_list_id'])) { 
                                        $lists = maybe_unserialize($res15['responder_list_id']);
                                        if (is_array($lists) && count($lists) > 0) {
                                        $cntr = 0;
                                        foreach ($lists as $list) {
                                            if ($res15['responder_list'] == $list['id'] || $cntr == 0) {
                                                $selected_list_id = $list['id'];
                                                $selected_list_label = $list['name'];
                                            }

                                                $responder_list_option[$list['id']] = $list['name'];
                                                $cntr++;
                                            }
                                        }
                                    }

                                    $hubspot_enable_class = (isset($hubspot_arr['enable']) && $hubspot_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                    if(isset($hubspot_arr['enable']) && $hubspot_arr['enable'] == 1){
                                        $hubspot_opt_disable = false;
                                    }else{
                                        $hubspot_opt_disable = true;
                                    }
                    
                                    $hubspot_attr = array();
                                    if( $setvaltolic != 1 ){
                                        $hubspot_attr = array( 'readonly' => 'readonly' );
                                    }

                                    echo $maincontroller->arf_selectpicker_dom( 'i_hubspot_list', 'i_hubspot_list', $hubspot_enable_class, 'width:170px;', $selected_list_id, $hubspot_attr, $responder_list_option, false, array(), $hubspot_opt_disable, array(), false, array(), true );

                                    ?>
                                    
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div id="autores-hubspot" class="autoresponder_inner_block">
                                <div class="textarea_space"></div>
                                <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                <div class="textarea_space"></div>
                                <div class="sltstandard">
                                    <?php
                                    $selected_list_id = "";
                                    $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                    $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')) );
                                    $lists = isset($res15['responder_list_id']) ?  maybe_serialize($res15['responder_list_id']) : '';
                                    $default_hubspot_select_list = isset($res15['responder_list']) ? $res15['responder_list'] : '';
                                    $selected_list_id_hubspot = (isset($hubspot_arr['type_val']) && $hubspot_arr['type_val'] != '' ) ? $hubspot_arr['type_val'] : $default_hubspot_select_list;
                                    if (is_array($lists) && count($lists) > 0) {
                                        $cntr = 0;
                                        foreach ($lists as $list) {
                                            if ($selected_list_id_hubspot == $list['id'] || $cntr == 0) {
                                                $selected_list_id = $list['id'];
                                                $selected_list_label = $list['name'];
                                            }

                                            $responder_list_option[$list['id']] = $list['name'];
                                            $cntr++;
                                        }
                                    }

                                    $hubspot_enable_class = (isset($hubspot_arr['enable']) && $hubspot_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                    if(isset($hubspot_arr['enable']) && $hubspot_arr['enable'] == 1){
                                        $hubspot_opt_disable = false;
                                    }else{
                                        $hubspot_opt_disable = true;
                                    }
                    
                                    $hubspot_attr = array();
                                    if( $setvaltolic != 1 ){
                                        $hubspot_attr = array( 'readonly' => 'readonly' );
                                    }

                                    echo $maincontroller->arf_selectpicker_dom( 'i_hubspot_list', 'i_hubspot_list', $hubspot_enable_class, 'width:170px;', $selected_list_id, $hubspot_attr, $responder_list_option, false, array(), $hubspot_opt_disable, array(), false, array(), true );

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
    function arf_hubspot_after_form_save($id, $values, $create_link, $is_ref_form) {

        global $wpdb, $armainhelper, $MdlDb;

        $get_enabled_ar = $wpdb->get_results($wpdb->prepare("SELECT enable_ar FROM " . $MdlDb->ar . " WHERE frm_id = %d ",$id));

        $enable_ar = maybe_unserialize($get_enabled_ar[0]->enable_ar );

        if (isset($values['autoresponders']) && is_array($values['autoresponders'])) {
            if (in_array(15, $values['autoresponders'])) {

                $hubspot_entry['enable'] = 1;
                $hubspot_entry['type'] = 0;
                $hubspot_entry['type_val'] = isset($values['i_hubspot_list']) ? $values['i_hubspot_list'] : 0;

                $hubspot_entries = maybe_serialize($hubspot_entry);

                $wpdb->query("UPDATE " . $MdlDb->ar . " SET hubspot = '" . $hubspot_entries . "' WHERE frm_id = " . $id);

                $enable_ar['hubspot'] = 1;
            } else {
                $hubspot_entry['enable'] = 0;
                $hubspot_entry['type'] = 0;
                $hubspot_entry['type_val'] = 0;

                $hubspot_entries = maybe_serialize($hubspot_entry);

                $wpdb->query("UPDATE " . $MdlDb->ar . " SET hubspot = '" . $hubspot_entries . "' WHERE frm_id = " . $id);
                $enable_er['hubspot'] = 0;
            }

            $enable_ar = maybe_serialize($enable_ar);

            $wpdb->query("UPDATE " . $MdlDb->ar . " SET enable_ar = '" . $enable_ar . "' WHERE frm_id = " . $id);
        }
        return '';
    }

    function arf_hubspot_after_create_entry($entry_id, $form_id) {

        global $email, $fname, $lname, $fid, $wpdb, $MdlDb ;


        if( $entry_id == '' || $form_id == '' ){
            return;
        }

        if( $form_id < 0 ){
            $form_id = abs( $form_id );
        }

        $results = wp_cache_get('arf_hubspot_result_'.$form_id);
        if( false == $results ){
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d", $form_id));
            wp_cache_set('arf_hubspot_result_'.$form_id, $results);
        }

        $form_options = maybe_unserialize( $results[0]->options );

        $check_condition_on_subscription = true;
        if (isset($form_options['conditional_subscription']) && $form_options['conditional_subscription'] == 1) {
            $check_condition_on_subscription = apply_filters('arf_check_condition_on_subscription', $form_options, $entry_id);
        }

        if( !$check_condition_on_subscription ){
            return;
        }
        $res = wp_cache_get('arf_hubspot_'.$form_id);
        if( false == $res ){
            $res = $wpdb->get_results($wpdb->prepare("SELECT `hubspot` FROM " .$MdlDb->ar." WHERE frm_id = %d", $form_id), 'ARRAY_A');
            wp_cache_set('arf_hubspot_'.$form_id, $res);
        }
        $ar_hubspot = isset($res[0]) ? maybe_unserialize( $res[0]['hubspot'] ) : '';
        $list_id = isset($ar_hubspot['type_val'])?$ar_hubspot['type_val']:'';

        if ( isset($ar_hubspot['enable']) && $ar_hubspot['enable'] == 1 ) {
            $reponder_arr = $wpdb->get_row($wpdb->prepare("SELECT `responder_api_key` FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 15), 'ARRAY_A');
           
            $api_key = $reponder_arr['responder_api_key'];
            

            $contact = array(
                'properties' => array(
                    array(
                        'property' => 'firstname',
                        'value' => $fname
                    ),
                    array(
                        'property' => 'lastname',
                        'value' => $lname
                    ),
                    array(
                        'property' => 'email',
                        'value' => $email
                    ),
                )
            );

            $header = array('Content-Type' => 'application/json' );
            
            $create_contact_url = "https://api.hubapi.com/contacts/v1/contact/?hapikey=".$api_key;
            $created_contact = wp_remote_post(
                $create_contact_url, 
                array(
                        
                        'timeout' => 5000,
                        'headers' => $header,
                        'body' => json_encode($contact)
                    )
                );
            
            if( is_wp_error( $created_contact ) ){
                    //handle error here
            }else{
                $contact_res = json_decode($created_contact['body']);
                
                if (isset($contact_res->vid)) {
                    $contct_vid = $contact_res->vid;
                    $contct_email = $contact_res->properties->email->value;

                    
                    $contolist = array(
                        'vids' => array($contct_vid),
                        'emails' => array($contct_email),

                    );


                    $contolist_url = "https://api.hubapi.com/contacts/v1/lists/".$list_id."/add?hapikey=".$api_key;

                    $header = array('Content-Type' => 'application/json' );

                    $cont_to_list = wp_remote_post(
                        $contolist_url, 
                        array(
                            'timeout' => 500,
                            'headers' => $header,
                            'body' => json_encode($contolist)
                            
                        )
                    );
                }
            }            
        }
    }

    function arf_set_current_autoresponse_hubspot($current_active_ar, $data) {
        $hubspot_arr = isset($data[0]['hubspot']) ? maybe_unserialize($data[0]['hubspot']) : '' ;

        if (isset($hubspot_arr['enable']) && $hubspot_arr['enable'] == 1) {
            $current_active_ar = 'hubspot';
        }
        return $current_active_ar;
    }

    function arforms_hubspot_save_form_data($id, $data) {
        global $wpdb, $MdlDb;

        $hubspot_arr = array();
        $type = get_option('arf_ar_type');
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 15), 'ARRAY_A');
        if (isset($data['autoresponders']) && in_array('15', $data['autoresponders'])) {
            $hubspot_arr['enable'] = 1;
        } else {
            $hubspot_arr['enable'] = 0;
        }
        $hubspot_arr = apply_filters('arf_trim_values',$hubspot_arr);
        $ar_hubspot = maybe_serialize($hubspot_arr);
        $res = $wpdb->update($MdlDb->ar, array('hubspot' => $ar_hubspot), array('frm_id' => $id));

    }

    function arforms_hubspot_reference_update($id, $res_rec, $resrpw) {
        global $wpdb, $MdlDb;
        $update = $wpdb->query($wpdb->prepare("update " . $MdlDb->ar . " set hubspot = '%s' where frm_id = %d", $res_rec["hubspot"], $resrpw));
    }
}

?>