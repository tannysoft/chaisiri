<?php 

$arf_convertkit = new arf_convertkit();
 /**
  * 
  */
 class arf_convertkit {

 	function __construct()
 	{
 		add_action('arfafterinstall', array($this, 'arf_add_convertkit_afterinstall'), 10);	

 		add_action('arf_autoresponder_global_setting_block', array($this, 'arf_add_convertkit_global_setting_block'), 10, 2);

 		add_action('arf_autoresponder_out_side_email_marketing_tools_update', array($this, 'arf_convertkit_update_api_data'), 10, 1);

 		add_action('arf_email_marketers_tab_outside', array($this, 'arf_convertkit_logo'), 10);

 		add_action('arf_email_marketers_tab_container_outside', array($this, 'arf_render_convertkit_block'), 10, 5);

 		add_action('arfafterupdateform', array($this, 'arf_convertkit_after_form_save'), 10, 4);

 		add_action('arfaftercreateentry', array($this, 'arf_convertkit_after_create_entry'), 10, 2);

 		add_filter('arf_current_autoresponse_set_outside', array($this, 'arf_set_current_autoresponse_convertkit'), 10, 2);

 		add_action('arf_autoresponder_ref_update', array($this, 'arforms_convertkit_reference_update'), 14, 3);

 		add_action('arf_autoresponder_after_insert', array($this, 'arforms_convertkit_save_form_data'), 14, 2);

        add_action('arf_autoresponder_after_update', array($this, 'arforms_convertkit_save_form_data'), 14, 2);
 	}

 	function arf_add_convertkit_afterinstall(){
 		global $wpdb, $MdlDb;

        $wpdb->query("ALTER TABLE " . $MdlDb->ar . "  ADD `convertkit` TEXT NOT NULL");

        $get_responder_id = $wpdb->get_row($wpdb->prepare("SELECT responder_id FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 17));

        if (!isset($get_responder_id->responder_id) || $get_responder_id->responder_id != 17) {
            $wpdb->query("INSERT INTO " . $MdlDb->autoresponder . " (responder_id) VALUES (17)");
        }

        $ar_types = get_option('arf_ar_type');

        $ar_types['convertkit_type'] = 1;

        $ar_types = $ar_types;

        update_option('arf_ar_type', $ar_types);
 	}

 	function arf_add_convertkit_global_setting_block( $autores_type, $setvaltolic ){
 		global $wpdb, $MdlDb, $maincontroller;

        $convertkit_alldata = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d",17));
        $convertkit_data = new stdClass();
        if( count($convertkit_alldata) > 0 ){
            $convertkit_data = $convertkit_alldata[0];
        }


        ?>
            <table class="wp-list-table widefat post " style="margin:20px 0 20px 10px; border:none;">

                <tr>
                    <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                    <th style="background:none; border:none;height:98px;" colspan="2"><img alt='' src="<?php echo ARFURL; ?>/images/convertkit.png" align="absmiddle" height='40px' /></th>
                </tr>

                <tr>
                    <?php $autores_type['convertkit_type'] = ( isset($autores_type['convertkit_type']) && $autores_type['convertkit_type'] != '' ) ? $autores_type['convertkit_type'] : 1; ?>
                    <th style="width:18%; background:none; border:none;"></th>
                    <th id="th_convertkit" style="padding-left:5px;background:none; border:none;">
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div" >
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" class="arf_submit_action arf_custom_radio" id="convertkit_17" <?php if ($autores_type['convertkit_type'] == 1) echo 'checked="checked"'; ?>  name="convertkit_type" value="1" onclick="show_api('convertkit');" />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label for="convertkit_17"><?php echo addslashes(esc_html__('Using API', 'ARForms')); ?></label>
                            </span>
                        </div>
                    </th>

                </tr>

                <tr id="convertkit_api_tr1" <?php if ($autores_type['convertkit_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('API Key', 'ARForms')); ?></label></td>

                    <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="convertkit_api" class="txtmodal1" <?php
                        if ($setvaltolic != 1) {
                            echo "readonly=readonly";
                            echo ' onclick="alert(\'Please activate license to set convertkit settings\');"';
                        }
                        ?> id="convertkit_api" size="80" onkeyup="show_verify_btn('convertkit');" value="<?php echo isset($convertkit_data->responder_api_key) ? $convertkit_data->responder_api_key : ""; ?>" /> &nbsp; &nbsp;
                        
                        <div class="arferrmessage" id="convertkit_api_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>
                </tr>

                <tr id="convertkit_api_tr1" <?php if ($autores_type['convertkit_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('API Secret', 'ARForms')); ?></label></td>

                    <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="convertkit_api_secret" class="txtmodal1" <?php
                        if ($setvaltolic != 1) {
                            echo "readonly=readonly";
                            echo ' onclick="alert(\'Please activate license to set convertkit settings\');"';
                        }
                        ?> id="convertkit_api_secret" size="80" onkeyup="show_verify_btn('convertkit');" value="<?php echo isset($convertkit_data->consumer_secret) ? $convertkit_data->consumer_secret : ""; ?>" /> &nbsp; &nbsp;
                        <span id="convertkit_link" <?php if (isset($convertkit_data->is_verify) && $convertkit_data->is_verify == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('convertkit', '0');" class="arlinks"><?php echo addslashes(esc_html__('Verify', 'ARForms')); ?></a></span>
                        <span id="convertkit_loader" style="display:none;"><div class="arf_imageloader" style="float: none !important;display:inline-block !important; "></div></span>
                        <span id="convertkit_verify" class="frm_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Verified', 'ARForms')); ?></span>
                        <span id="convertkit_error" class="frm_not_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Not Verified', 'ARForms')); ?></span>
                        <input type="hidden" name="convertkit_status" id="convertkit_status" value="<?php echo $convertkit_data->is_verify; ?>" />
                        <div class="arferrmessage" id="convertkit_api_secret_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>
                    </td>
                </tr>


                <tr id="convertkit_api_tr2" <?php if ($autores_type['convertkit_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-top:3px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('List Name', 'ARForms')); ?></label></td>

                    <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;">
                    	<span id="select_convertkit">
                            <div class="sltstandard" style="float:none;display:inline;">
                                <?php
                                $responder_list_option = array( '' => esc_html__('Nothing Selected','ARForms') );
                                $selected_list_id = '';
                                $lists = isset($convertkit_data->responder_list_id) ? maybe_unserialize($convertkit_data->responder_list_id) : array();
                                if ($lists != '' and count($lists) > 0) {
                                    if (is_array($lists)) {
                                        foreach ($lists as $key => $list) {
                                            if ($convertkit_data->responder_list != '') {
                                                if ($convertkit_data->responder_list == $list['id']) {
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

                                echo $maincontroller->arf_selectpicker_dom( 'convertkit_listid', 'convertkit_listid', '', 'width:400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );
                                ?>
                            </div></span>

                        <div id="convertkit_del_link" style="padding-left:5px; margin-top:10px; clear: both; <?php if ($convertkit_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">
                            <a href="javascript:void(0);" onclick="action_autores('refresh', 'convertkit');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                            &nbsp;  &nbsp;  &nbsp;  &nbsp;
                            <a href="javascript:void(0);" onclick="action_autores('delete', 'convertkit');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                        </div>


                    </td>

                </tr>

                <tr>
                    <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                </tr>


            </table>
        <?php
 	}

 	function arf_convertkit_update_api_data($arf_convertkit_data){
 		
 		global $wpdb, $MdlDb;
        $arf_convertkit_api = isset($arf_convertkit_data['convertkit_api']) ? $arf_convertkit_data['convertkit_api'] : '';
        $arf_convertkit_api_secret = isset($arf_convertkit_data['convertkit_api_secret']) ? $arf_convertkit_data['convertkit_api_secret'] : '';
        $arf_convertkit_listid = isset($arf_convertkit_data['convertkit_listid']) ? $arf_convertkit_data['convertkit_listid'] : '';
        $arf_convertkit_data = apply_filters('arf_trim_values', $arf_convertkit_data);
        
        if ( isset($arf_convertkit_data['convertkit_type']) && $arf_convertkit_data['convertkit_type'] == 1 ) {
            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_convertkit_api, 'responder_list' => $arf_convertkit_listid), array('consumer_secret' => $arf_convertkit_api_secret ,'responder_id' => '17'));
        }
        
 	}

 	function arf_convertkit_logo() {
        ?>
            <li class="arf_optin_tab_item" data-id="convertkit"><?php addslashes(esc_html_e('ConvertKit', 'ARForms')); ?></li>
        <?php 
    }

    function arf_render_convertkit_block($arfaction = '', $global_enable_ar = '', $current_active_ar = '', $data = '', $setvaltolic = '') {

        global $wpdb, $MdlDb, $maincontroller;

        $res = get_option('arf_ar_type');
        $res17 = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 17), 'ARRAY_A');
        
        if( count($res17) > 0){
            $res17 = $res17[0];
        }
        $convertkit_arr = isset($data[0]['convertkit']) ? maybe_unserialize($data[0]['convertkit']) : array();

        ?>
        <div class="arf_optin_tab_inner_container" id="convertkit">
            <div>
                <?php 
                $style = '';
                $style_gray = '';
                if(isset($convertkit_arr['enable']) && $convertkit_arr['enable'] == 1)
                {
                    $style = 'style="display:block;"';
                    $style_gray = 'style="display:none;"';
                } else{
                    $style = 'style="display:none;"';
                    $style_gray = 'style="display:block;"';
                }?>
                <div class="arf_optin_logo convertkit_original arfconvertkit" <?php echo $style;?>><img src="<?php echo ARFURL; ?>/images/convertkit.png" height="40px" /></div>
                <div class="arf_optin_logo convertkit_gray arfconvertkit" <?php echo $style_gray;?>><img src="<?php echo ARFURL; ?>/images/convertkit_gray.png" height="40px" /></div>
                <div class="arf_optin_checkbox arfconvertkit">
                    <div>
                        <label class="arf_js_switch_label">
                            <span></span>
                        </label>
                        <span class="arf_js_switch_wrapper">
                            <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_17" data-attr="convertkit" value="17" <?php
                            if (isset($res['convertkit_type']) && $res['convertkit_type'] == 2) {
                                echo 'disabled="disabled"';
                            }
                            ?> <?php if (isset($convertkit_arr['enable']) and $convertkit_arr['enable'] == 1) { echo "checked=checked"; } ?> onchange="show_setting('convertkit', '17');" <?php if ($setvaltolic != 1) { echo 'onclick="return false"'; } ?> />
                            <span class="arf_js_switch"></span>
                        </span>
                        <label class="arf_js_switch_label" for="autores_17">
                            <span>&nbsp;<?php addslashes(esc_html_e('Enable', 'ARForms')); ?></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="arf_option_configuration_wrapper convertkit_configuration_wrapper <?php echo (isset($convertkit_arr['enable']) && $convertkit_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                <br/><br/>
                <?php
                $rand_num = rand(1111, 9999);
                if (isset($res['convertkit_type']) && $res['convertkit_type'] == 1) {
                    ?>
                    <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                        <?php
                        if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and isset($arf_template_id) and $arf_template_id < 100 ) ) || (isset($convertkit_arr['enable']) and $convertkit_arr['enable'] == 0 )) {
                            ?>
                            <div id="autores-convertkit" class="autoresponder_inner_block" data-if="sadsa" >
                                <div class="textarea_space"></div>
                                <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                <div class="textarea_space"></div>
                                <div class="sltstandard">
                                    <?php
                                    $selected_list_id = "";
                                    $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                    $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')) );
                                    if(isset($res17['responder_list_id'])){
                                        $lists = maybe_unserialize($res17['responder_list_id']);
                                        if ( is_array($lists) && count($lists) > 0) {
                                            $cntr = 0;
                                            foreach ($lists as $list) {
                                            if ($res17['responder_list'] == $list['id'] || $cntr == 0) {
                                                $selected_list_id = $list['id'];
                                                $selected_list_label = $list['name'];
                                            }

                                                $responder_list_option[$list['id']] = $list['name'];
                                                $cntr++;
                                            }
                                        }
                                    }

                                    $convertkit_enable_class = (isset($convertkit_arr['enable']) && $convertkit_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                    if(isset($convertkit_arr['enable']) && $convertkit_arr['enable'] == 1){
                                        $convertkit_opt_disable = false;
                                    }else{
                                        $convertkit_opt_disable = true;
                                    }
                    
                                    $convertkit_attr = array();
                                    if( $setvaltolic != 1 ){
                                        $convertkit_attr = array( 'readonly' => 'readonly' );
                                    }

                                    echo $maincontroller->arf_selectpicker_dom( 'i_convertkit_list', 'i_convertkit_list', $convertkit_enable_class, 'width:170px;', $selected_list_id, $convertkit_attr, $responder_list_option, false, array(), $convertkit_opt_disable, array(), false, array(), true );

                                    ?>
                                    
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div id="autores-convertkit" class="autoresponder_inner_block">
                                <div class="textarea_space"></div>
                                <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                <div class="textarea_space"></div>
                                <div class="sltstandard">
                                    <?php
                                    $selected_list_id = "";
                                    $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                    $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')) );
                                    $lists = isset($res17['responder_list_id']) ? maybe_unserialize($res17['responder_list_id']) : '';
                                    $default_convertkit_select_list = isset($res17['responder_list']) ? $res17['responder_list'] : '';
                                    $selected_list_id_convertkit = (isset($convertkit_arr['type_val']) && $convertkit_arr['type_val'] != '' ) ? $convertkit_arr['type_val'] : $default_convertkit_select_list;
                                    if (is_array($lists) && count($lists) > 0) {
                                        $cntr = 0;
                                        foreach ($lists as $list) {
                                            if ($selected_list_id_convertkit == $list['id'] || $cntr == 0) {
                                                $selected_list_id = $list['id'];
                                                $selected_list_label = $list['name'];
                                            }

                                            $responder_list_option[$list['id']] = $list['name'];
                                            $cntr++;
                                        }
                                    }

                                    $convertkit_enable_class = (isset($convertkit_arr['enable']) && $convertkit_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                    if(isset($convertkit_arr['enable']) && $convertkit_arr['enable'] == 1){
                                        $convertkit_opt_disable = false;
                                    }else{
                                        $convertkit_opt_disable = true;
                                    }
                    
                                    $convertkit_attr = array();
                                    if( $setvaltolic != 1 ){
                                        $convertkit_attr = array( 'readonly' => 'readonly' );
                                    }

                                    echo $maincontroller->arf_selectpicker_dom( 'i_convertkit_list', 'i_convertkit_list', $convertkit_enable_class, 'width:170px;', $selected_list_id, $convertkit_attr, $responder_list_option, false, array(), $convertkit_opt_disable, array(), false, array(), true );

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

    function arf_convertkit_after_form_save($id, $values, $create_link, $is_ref_form) {

        global $wpdb, $armainhelper, $MdlDb;

        $get_enabled_ar = $wpdb->get_results($wpdb->prepare("SELECT enable_ar FROM " . $MdlDb->ar . " WHERE frm_id = %d ",$id));

        $enable_ar = maybe_unserialize( $get_enabled_ar[0]->enable_ar );

        if (isset($values['autoresponders']) && is_array($values['autoresponders'])) {
            if (in_array(17, $values['autoresponders'])) {

                $convertkit_entry['enable'] = 1;
                $convertkit_entry['type'] = 0;
                $convertkit_entry['type_val'] = isset($values['i_convertkit_list']) ? $values['i_convertkit_list'] : 0;

                $convertkit_entries = maybe_serialize($convertkit_entry);

                $wpdb->query("UPDATE " . $MdlDb->ar . " SET convertkit = '" . $convertkit_entries . "' WHERE frm_id = " . $id);

                $enable_ar['convertkit'] = 1;
            } else {
                $convertkit_entry['enable'] = 0;
                $convertkit_entry['type'] = 0;
                $convertkit_entry['type_val'] = 0;

                $convertkit_entries = maybe_serialize($convertkit_entry);

                $wpdb->query("UPDATE " . $MdlDb->ar . " SET convertkit = '" . $convertkit_entries . "' WHERE frm_id = " . $id);
                $enable_er['convertkit'] = 0;
            }

            $enable_ar = maybe_serialize($enable_ar);

            $wpdb->query("UPDATE " . $MdlDb->ar . " SET enable_ar = '" . $enable_ar . "' WHERE frm_id = " . $id);
        }
        return '';
    }

    function arf_convertkit_after_create_entry($entry_id, $form_id) {

        global $email, $fname, $lname, $fid, $wpdb, $MdlDb ;


        if( $entry_id == '' || $form_id == '' ){
            return;
        }

        if( $form_id < 0 ){
            $form_id = abs( $form_id );
        }

        $results = wp_cache_get('arf_convertkit_result_'.$form_id);
        if( false == $results ){
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d", $form_id));
            wp_cache_set('arf_convertkit_result_'.$form_id, $results);
        }

        $form_options = maybe_unserialize( $results[0]->options );

        $check_condition_on_subscription = true;
        if (isset($form_options['conditional_subscription']) && $form_options['conditional_subscription'] == 1) {
            $check_condition_on_subscription = apply_filters('arf_check_condition_on_subscription', $form_options, $entry_id);
        }

        if( !$check_condition_on_subscription ){
            return;
        }

        $res = wp_cache_get('arf_convertkit_'.$form_id);
        if( false == $res ){
            $res = $wpdb->get_results($wpdb->prepare("SELECT `convertkit` FROM " .$MdlDb->ar." WHERE frm_id = %d", $form_id), 'ARRAY_A');
            wp_cache_set('arf_convertkit_'.$form_id, $res);
        }
        $ar_convertkit = isset($res[0]) ? maybe_unserialize( $res[0]['convertkit'] ) : '';
        
        $form_id = isset($ar_convertkit['type_val'])?$ar_convertkit['type_val']:'';

        if ( isset($ar_convertkit['enable']) && $ar_convertkit['enable'] == 1 ) {

            $reponder_arr = $wpdb->get_row($wpdb->prepare("SELECT `responder_api_key`, `consumer_secret` FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 17), 'ARRAY_A');
           
            $api_key = $reponder_arr['responder_api_key'];
            $api_secret = $reponder_arr['consumer_secret'];
            

            $add_sub = array(
                'api_key'    => $api_key,
                'email'      => $email,
                'first_name' => $fname,
                'fields'    => array(
                    'last_name' => $lname
                ) 
            );

            $headers = array('Content-Type' => 'application/json' );
            
            $sub_url = "https://api.convertkit.com/v3/forms/".$form_id."/subscribe";
            $added_sub = wp_remote_post(
                $sub_url, 
                array(
                        'timeout' => 5000,
                        'headers' => $headers,
                        'body' => json_encode($add_sub)
                    )
                );

            
            if( is_wp_error( $added_sub ) ){
                    //handle error here
               
            }else{
                $added_sub_res = json_decode($added_sub['body']);
                
                if (isset($added_sub_res->subscription->id)) {

                    $subtofrm_url = "https://api.convertkit.com/v3/forms/".$form_id."/subscriptions?api_secret=".$api_secret;

                    $sublist_frm = wp_remote_get(
                        $subtofrm_url, 
                        array(
                            'timeout' => 500
                        )
                    );
                    if (is_wp_error($sublist_frm)) {
                         $errors[] = esc_html__('Something went wrong while adding subscriber', 'ARForms');
                    }
                }
            }           
        }
    }

    function arf_set_current_autoresponse_convertkit($current_active_ar, $data) {
        $convertkit_arr = isset($data[0]['convertkit']) ? maybe_unserialize($data[0]['convertkit']) : '' ;

        if (isset($convertkit_arr['enable']) && $convertkit_arr['enable'] == 1) {
            $current_active_ar = 'convertkit';
        }
        return $current_active_ar;
    }

    function arforms_convertkit_reference_update($id, $res_rec, $resrpw) {
        global $wpdb, $MdlDb;
        $update = $wpdb->query($wpdb->prepare("update " . $MdlDb->ar . " set convertkit = '%s' where frm_id = %d", $res_rec["convertkit"], $resrpw));
    }

    function arforms_convertkit_save_form_data($id, $data) {
        global $wpdb, $MdlDb;

        $convertkit_arr = array();
        $type = get_option('arf_ar_type');
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 17), 'ARRAY_A');
        if (isset($data['autoresponders']) && in_array('17', $data['autoresponders'])) {
            $convertkit_arr['enable'] = 1;
        } else {
            $convertkit_arr['enable'] = 0;
        }
        $convertkit_arr = apply_filters('arf_trim_values',$convertkit_arr);
        $ar_convertkit = maybe_serialize($convertkit_arr);
        $res = $wpdb->update($MdlDb->ar, array('convertkit' => $ar_convertkit), array('frm_id' => $id));
    }
 }

?>