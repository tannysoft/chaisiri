<?php

$arf_sendinblue = new arf_sendinblue();

class arf_sendinblue {

    function __construct() {

        add_action('arfafterinstall', array($this, 'arf_add_sendinblue_afterinstall'), 10);

        add_action('arf_autoresponder_global_setting_block', array($this, 'arf_add_sendinblue_global_setting_block'), 10, 2);

        add_action('arf_autoresponder_out_side_email_marketing_tools_update', array($this, 'arf_sendinblue_update_api_data'), 10, 1);

        add_action('arf_email_marketers_tab_outside', array($this, 'arf_sendinblue_logo'), 10);

        add_action('arf_email_marketers_tab_container_outside', array($this, 'arf_render_sendinblue_block'), 10, 5);

        add_action('arfafterupdateform', array($this, 'arf_sendinblue_after_form_save'), 10, 4);

        add_action('arfaftercreateentry', array($this, 'arf_sendinblue_after_create_entry'), 10, 2);

        add_filter('arf_current_autoresponse_set_outside', array($this, 'arf_set_current_autoresponse_sendinblue'), 10, 2);

        add_action('arf_autoresponder_ref_update', array($this, 'arforms_sendinblue_reference_update'), 14, 3);

        add_action('arf_autoresponder_after_insert', array($this, 'arforms_sendinblue_save_form_data'), 14, 2);

        add_action('arf_autoresponder_after_update', array($this, 'arforms_sendinblue_save_form_data'), 14, 2);

    }

    function arf_add_sendinblue_afterinstall() {

        global $wpdb, $MdlDb;


        $wpdb->query("ALTER TABLE " . $MdlDb->ar . "  ADD `sendinblue` TEXT NOT NULL");

        $get_responder_id = $wpdb->get_row($wpdb->prepare("SELECT responder_id FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 16));

        if (!isset($get_responder_id->responder_id) || $get_responder_id->responder_id != 16) {
            
            $wpdb->query("INSERT INTO " . $MdlDb->autoresponder . " (responder_id) VALUES (16)");
        }

        $ar_types = get_option('arf_ar_type');

        $ar_types['sendinblue_type'] = 1;

        $ar_types = $ar_types;

        update_option('arf_ar_type', $ar_types);
    }

    function arf_add_sendinblue_global_setting_block( $autores_type, $setvaltolic ) {

        global $wpdb, $MdlDb, $maincontroller;

        $sendinblue_alldata = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d",16));

        $sendinblue_data   = new stdClass();

        if( count($sendinblue_alldata) > 0 ){
            $sendinblue_data = $sendinblue_alldata[0];
        }
        ?>
            <table class="wp-list-table widefat post " style="margin:20px 0 20px 10px; border:none;">

                <tr>
                    <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                    <th style="background:none; border:none;height:98px;" colspan="2"><img alt='' src="<?php echo ARFURL; ?>/images/sendinblue.png" align="absmiddle" /></th>
                </tr>
                <tr>
                    <?php $autores_type['sendinblue_type'] = ( isset($autores_type['sendinblue_type']) && $autores_type['sendinblue_type'] != '' ) ? $autores_type['sendinblue_type'] : 1; ?>
                    <th style="width:18%; background:none; border:none;"></th>
                    <th id="th_sendinblue" style="padding-left:5px;background:none; border:none;">
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div" >
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" class="arf_submit_action arf_custom_radio" id="sendinblue_16" <?php if ($autores_type['sendinblue_type'] == 1) echo 'checked="checked"'; ?>  name="sendinblue_type" value="1" onclick="show_api('sendinblue');" />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label for="sendinblue_16"><?php echo addslashes(esc_html__('Using API', 'ARForms')); ?></label>
                            </span>
                        </div>
                    </th>
                </tr>

                <tr id="sendinblue_api_tr1" <?php if ($autores_type['sendinblue_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('API Key', 'ARForms')); ?></label></td>

                    <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="sendinblue_api" class="txtmodal1" <?php
                        if ($setvaltolic != 1) {
                            echo "readonly=readonly";
                            echo ' onclick="alert(\'Please activate license to set sendinblue settings\');"';
                        }

                        ?> id="sendinblue_api" size="80" onkeyup="show_verify_btn('sendinblue');" value="<?php echo isset($sendinblue_data->responder_api_key) ? $sendinblue_data->responder_api_key : ""; ?>" /> &nbsp; &nbsp;
                        <span id="sendinblue_link" <?php if (isset($sendinblue_data->is_verify) && $sendinblue_data->is_verify == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('sendinblue', '0');" class="arlinks"><?php echo addslashes(esc_html__('Verify', 'ARForms')); ?></a></span>
                        <span id="sendinblue_loader" style="display:none;"><div class="arf_imageloader" style="float: none !important;display:inline-block !important; "></div></span>
                        <span id="sendinblue_verify" class="frm_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Verified', 'ARForms')); ?></span>
                        <span id="sendinblue_error" class="frm_not_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Not Verified', 'ARForms')); ?></span>
                        <input type="hidden" name="sendinblue_status" id="sendinblue_status" value="<?php echo $sendinblue_data->is_verify; ?>" />
                        <div class="arferrmessage" id="sendinblue_api_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>
                </tr>

                <tr id="sendinblue_api_tr2" <?php if ($autores_type['sendinblue_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-top:3px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('List name', 'ARForms')); ?></label></td>

                    <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_sendinblue">
                            <div class="sltstandard" style="float:none;display:inline;">
                                <?php
                                
                                $responder_list_option = array( '' => esc_html__('Nothing Selected','ARForms') );
                                $selected_list_label = esc_html__('Nothing Selected','ARForms');
                                $selected_list_id = '';
                                $lists = isset($sendinblue_data->responder_list_id) ? maybe_unserialize($sendinblue_data->responder_list_id) : array();

                                if ($lists != '' and count($lists) > 0) {
                                    if (is_array($lists)) {
                                        foreach ($lists as $key => $list) {
                                            if ($sendinblue_data->responder_list != '') {
                                                
                                                if ($sendinblue_data->responder_list == $list['id']) {
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

                                echo $maincontroller->arf_selectpicker_dom( 'sendinblue_listid', 'sendinblue_listid', '', 'width: 400px;', $selected_list_id, array(), $responder_list_option, false, array(), false, array(), false, array(), true );

                                ?>
                                
                            </div></span>

                        <div id="sendinblue_del_link" style="padding-left:5px; margin-top:10px; clear: both; <?php if ($sendinblue_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">
                            <a href="javascript:void(0);" onclick="action_autores('refresh', 'sendinblue');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                            &nbsp;  &nbsp;  &nbsp;  &nbsp;
                            <a href="javascript:void(0);" onclick="action_autores('delete', 'sendinblue');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                </tr>
            </table>
        <?php
    }

    function arf_sendinblue_update_api_data($arf_sendinblue_data) {

        global $wpdb, $MdlDb;

        $arf_sendinblue_api = isset($arf_sendinblue_data['sendinblue_api']) ? $arf_sendinblue_data['sendinblue_api'] : '';

        $arf_sendinblue_listid = isset($arf_sendinblue_data['sendinblue_listid']) ? $arf_sendinblue_data['sendinblue_listid'] : '';

        $arf_sendinblue_data = apply_filters('arf_trim_values',$arf_sendinblue_data);
        
        if ( isset($arf_sendinblue_data['sendinblue_type']) && $arf_sendinblue_data['sendinblue_type'] == 1 ) {
            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_sendinblue_api, 'responder_list' => $arf_sendinblue_listid), array('responder_id' => '16'));
        }
    }

    function arf_sendinblue_logo() {
        ?>
            <li class="arf_optin_tab_item" data-id="sendinblue"><?php addslashes(esc_html_e('Sendinblue', 'ARForms')); ?></li>
        <?php 
    }

    function arf_render_sendinblue_block($arfaction = '', $global_enable_ar = '', $current_active_ar = '', $data = '', $setvaltolic = '') {

        global $wpdb, $MdlDb, $maincontroller;
        $res = get_option('arf_ar_type');

        $res16 = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 16), 'ARRAY_A');

        if( count($res16) > 0){
            $res16 = $res16[0];
        }
        $sendinblue_arr = isset($data[0]['sendinblue']) ? maybe_unserialize($data[0]['sendinblue']) : '' ;

        ?>
        <div class="arf_optin_tab_inner_container" id="sendinblue">
            <div>
                <?php 
                $style = '';
                $style_gray = '';

                if(isset($sendinblue_arr['enable']) && $sendinblue_arr['enable'] == 1)
                {  
                    $style = 'style="display:block;"';
                    $style_gray = 'style="display:none;"';
                } else{
                    $style = 'style="display:none;"';
                    $style_gray = 'style="display:block;"';
                }
                ?>
                <div class="arf_optin_logo sendinblue_original " <?php echo $style;?>><img src="<?php echo ARFURL; ?>/images/sendinblue.png" /></div>
                <div class="arf_optin_logo sendinblue_gray" <?php echo $style_gray;?>><img src="<?php echo ARFURL; ?>/images/sendinblue_gray.png" /></div>
                <div class="arf_optin_checkbox">
                    <div>
                        <label class="arf_js_switch_label">
                            <span></span>
                        </label>
                        <span class="arf_js_switch_wrapper">
                            <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_16" data-attr="sendinblue" value="16" <?php
                            if (isset($res['sendinblue_type']) && $res['sendinblue_type'] == 2) {
                                echo 'disabled="disabled"';
                            }
                            ?> <?php if (isset($sendinblue_arr['enable']) and $sendinblue_arr['enable'] == 1) { echo "checked=checked"; } ?> onchange="show_setting('sendinblue', '16');" <?php if ($setvaltolic != 1) { echo 'onclick="return false"'; } ?> />
                            <span class="arf_js_switch">
                                
                            </span>
                        </span>
                        <label class="arf_js_switch_label" for="autores_16">
                            <span>&nbsp;<?php addslashes(esc_html_e('Enable', 'ARForms')); ?></span>
                        </label>
                    </div>
                </div>
            </div>
          
            <div class="arf_option_configuration_wrapper sendinblue_configuration_wrapper <?php echo (isset($sendinblue_arr['enable']) && $sendinblue_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                <br/><br/>
                <?php
                $rand_num = rand(1111, 9999);
                
                if (isset($res['sendinblue_type']) && $res['sendinblue_type'] == 1) {
                    ?>
                    <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                        <?php
                        if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and isset($arf_template_id) and $arf_template_id < 100 ) ) || (isset($sendinblue_arr['enable']) and $sendinblue_arr['enable'] == 0 )) {

                            ?>
                            <div id="autores-sendinblue" class="autoresponder_inner_block" data-if="sadsa" >
                                <div class="textarea_space"></div>
                                <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                <div class="textarea_space"></div>
                                <div class="sltstandard">

                                    <?php
                                    $selected_list_id = "";
                                    $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                    $responder_list_option = array('' => addslashes(esc_html__('Select Field','ARForms')) );
                                    if( isset($res16['responder_list_id'])){
                                        $lists = maybe_unserialize( $res16['responder_list_id'] );
                                        
                                        if ( is_array($lists) && count($lists) > 0) {
                                            $cntr = 0;
                                            foreach ($lists as $list) {
                                                if ($res16['responder_list'] == $list['id'] || $cntr == 0) {
                                                    $selected_list_id = $list['id'];
                                                    $selected_list_label = $list['name'];
                                                }

                                                $responder_list_option[$list['id']] = $list['name'];

                                                $cntr++;
                                            }
                                        }
                                    }

                                    $sendinblue_enable_class = (isset($sendinblue_arr['enable']) && $sendinblue_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                    if(isset($sendinblue_arr['enable']) && $sendinblue_arr['enable'] == 1){
                                        $sendinblue_opt_disable = false;
                                    }else{
                                        $sendinblue_opt_disable = true;
                                    }
                    
                                    $sendinblue_attr = array();
                                    if( $setvaltolic != 1 ){
                                        $sendinblue_attr = array( 'readonly' => 'readonly' );
                                    }

                                    echo $maincontroller->arf_selectpicker_dom( 'i_sendinblue_list', 'i_sendinblue_list', $sendinblue_enable_class, 'width:170px;', $selected_list_id, $sendinblue_attr, $responder_list_option, false, array(), $sendinblue_opt_disable, array(), false, array(), true );

                                    ?>
                                    
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div id="autores-sendinblue" class="autoresponder_inner_block">
                                <div class="textarea_space"></div>
                                <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                <div class="textarea_space"></div>
                                <div class="sltstandard">
                                    <?php
                                    $selected_list_id = "";
                                    $selected_list_label = addslashes(esc_html__('Select Group','ARForms'));
                                    $responder_list_option = array( '' => addslashes(esc_html__('Select Field','ARForms')) );
                                    $lists = isset($res16['responder_list_id']) ? maybe_unserialize( $res16['responder_list_id'] ) :'' ;

                                    $default_sendinblue_select_list = isset($res16['responder_list']) ? $res16['responder_list'] : '';
                                    $selected_list_id_sendinblue = (isset($sendinblue_arr['type_val']) && $sendinblue_arr['type_val'] != '' ) ? $sendinblue_arr['type_val'] : $default_sendinblue_select_list;
                                    if (is_array($lists) && count($lists) > 0) {
                                        $cntr = 0;
                                        foreach ($lists as $list) {
                                            if ($selected_list_id_sendinblue == $list['id'] || $cntr == 0) {
                                                $selected_list_id = $list['id'];
                                                $selected_list_label = $list['name'];
                                            }

                                            $responder_list_option[$list['id']] = $list['name'];
                                            $cntr++;
                                        }
                                    }

                                    $sendinblue_enable_class = (isset($sendinblue_arr['enable']) && $sendinblue_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins';

                                    if(isset($sendinblue_arr['enable']) && $sendinblue_arr['enable'] == 1){
                                        $sendinblue_opt_disable = false;
                                    }else{
                                        $sendinblue_opt_disable = true;
                                    }
                    
                                    $sendinblue_attr = array();
                                    if( $setvaltolic != 1 ){
                                        $sendinblue_attr = array( 'readonly' => 'readonly' );
                                    }

                                    echo $maincontroller->arf_selectpicker_dom( 'i_sendinblue_list', 'i_sendinblue_list', $sendinblue_enable_class, 'width:170px;', $selected_list_id, $sendinblue_attr, $responder_list_option, false, array(), $sendinblue_opt_disable, array(), false, array() );
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

    function arf_sendinblue_after_form_save($id, $values, $create_link, $is_ref_form) {

        global $wpdb, $armainhelper, $MdlDb;

        $get_enabled_ar = $wpdb->get_results($wpdb->prepare("SELECT enable_ar FROM " . $MdlDb->ar . " WHERE frm_id = %d ",$id));

        $enable_ar = maybe_unserialize($get_enabled_ar[0]->enable_ar, true);

        if (isset($values['autoresponders']) && is_array($values['autoresponders'])) {

            if (in_array(16, $values['autoresponders'])) {

                $sendinblue_entry['enable'] = 1;
                $sendinblue_entry['type'] = 0;
                $sendinblue_entry['type_val'] = isset($values['i_sendinblue_list']) ? $values['i_sendinblue_list'] : 0;

                $sendinblue_entries = maybe_serialize($sendinblue_entry);

                $wpdb->query("UPDATE " . $MdlDb->ar . " SET sendinblue = '" . $sendinblue_entries . "' WHERE frm_id = " . $id);

                $enable_ar['sendinblue'] = 1;
            } else {
                $sendinblue_entry['enable'] = 0;
                $sendinblue_entry['type'] = 0;
                $sendinblue_entry['type_val'] = 0;

                $sendinblue_entries = maybe_serialize($sendinblue_entry);

                $wpdb->query("UPDATE " . $MdlDb->ar . " SET sendinblue = '" . $sendinblue_entries . "' WHERE frm_id = " . $id);
                $enable_er['sendinblue'] = 0;
            }

            $enable_ar = maybe_serialize($enable_ar);

            $wpdb->query("UPDATE " . $MdlDb->ar . " SET enable_ar = '" . $enable_ar . "' WHERE frm_id = " . $id);
        }
        return '';
    }

    function arf_sendinblue_after_create_entry($entry_id, $form_id) {
       

        global $email, $fname, $lname, $wpdb, $fid, $MdlDb;       

        if( $entry_id == '' || $form_id == '' ){
            return;
        }

        if( $form_id < 0 ){
            $form_id = abs( $form_id );
        }
        
        $get_responder_api_key = $wpdb->get_row($wpdb->prepare("SELECT responder_api_key FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 16));
       
        
        $api_key = isset( $get_responder_api_key->responder_api_key ) ? $get_responder_api_key->responder_api_key : '';


        $results = wp_cache_get('arf_sendinblue_result_'.$form_id);
        if( false == $results ){
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d", $form_id));
            wp_cache_set('arf_sendinblue_result_'.$form_id, $results);
        }
        $form_options = maybe_unserialize($results[0]->options, true);


        $check_condition_on_subscription = true;
        if (isset($form_options['conditional_subscription']) && $form_options['conditional_subscription'] == 1) {
            $check_condition_on_subscription = apply_filters('arf_check_condition_on_subscription', $form_options, $entry_id);
        }

        if( !$check_condition_on_subscription ){
            return;
        }

        $res = wp_cache_get('arf_sendinblue_'.$form_id);
        if( false == $res ){
            $res = $wpdb->get_results($wpdb->prepare("SELECT `sendinblue` FROM " .$MdlDb->ar." WHERE frm_id = %d", $form_id), 'ARRAY_A');
            wp_cache_set('arf_sendinblue_'.$form_id, $res);
        }
        
        $ar_sendinblue = isset($res[0]) ? maybe_unserialize($res[0]['sendinblue'], true) : '';
       

        $listIds = isset($ar_sendinblue['type_val'])?$ar_sendinblue['type_val']:'';
        

        if ( isset($ar_sendinblue['enable']) && $ar_sendinblue['enable'] == 1 && '' != $listIds) {
           
            $url = 'https://api.sendinblue.com/v3/contacts';
            $args = array(
                'method'   => 'POST',
                'headers'   => array(
                    'Content-Type' => 'application/json',
                    'api-key' => $api_key,  
                ),
                'body'     => json_encode(array('email' => $email,'attributes' => array('FIRSTNAME'=> $fname,'LASTNAME' => $lname) ,'listIds' => array($listIds))),
            );
            
            $response = wp_remote_post( $url, $args );

           
            if(is_wp_error( $response)){
                $error = $response->get_error_message();
            }else{

                $url_add_to_id = 'https://api.sendinblue.com/v3/contacts/lists/'.$listIds.'/contacts/add';
                $args = array(
                    'method'   => 'POST',
                    'headers'   => array(
                        'Content-Type' => 'application/json',
                        'api-key' => $api_key,
                    ),
                        'body'     => json_encode(array('emails' => array($email))),
                );
                $response_add_to_list = wp_remote_post( $url_add_to_id, $args );         
              
                if(is_wp_error( $response)){
                    $error = $response->get_error_message();
                }
            }        
        }
    }

    function arf_set_current_autoresponse_sendinblue($current_active_ar, $data) {
        
        $sendinblue_arr = isset($data[0]['sendinblue']) ? maybe_unserialize($data[0]['sendinblue']) : '' ;

        if (isset($sendinblue_arr['enable']) && $sendinblue_arr['enable'] == 1) {
            $current_active_ar = 'sendinblue';
        }
        return $current_active_ar;
    }

    function arforms_sendinblue_save_form_data($id, $data) {

        
        global $wpdb, $MdlDb;
        $sendinblue_arr = array();
        $type = get_option('arf_ar_type');
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 16), 'ARRAY_A');
        if (isset($data['autoresponders']) && in_array('16', $data['autoresponders'])) {
            $sendinblue_arr['enable'] = 1;
        } else {
            $sendinblue_arr['enable'] = 0;
        }
        $sendinblue_arr = apply_filters('arf_trim_values',$sendinblue_arr);
        $ar_sendinblue = maybe_serialize($sendinblue_arr);
        $res = $wpdb->update($MdlDb->ar, array('sendinblue' => $ar_sendinblue), array('frm_id' => $id));

    }

    function arforms_sendinblue_reference_update($id, $res_rec, $resrpw) {
        global $wpdb, $MdlDb;
        $update = $wpdb->query($wpdb->prepare("update " . $MdlDb->ar . " set sendinblue = '%s' where frm_id = %d", $res_rec["sendinblue"], $resrpw));
    }
}

?>