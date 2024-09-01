<?php
global $arf_post_value_class;
$arf_post_value_class = new arf_post_value();

class arf_post_value {
    function __construct() {

        add_action('arf_option_before_submit_conditional_logic', array($this, 'arf_post_values_after_redirect_to_url_html'), 11, 2);

        add_action('arfaftercreateentry', array($this, 'arf_after_submit_form'), 101, 2);

        add_action('arf_after_paypal_successful_paymnet', array($this, 'arf_after_submit_form_paypal'), 10, 3);

        add_action('arf_user_register', array( $this, 'arf_post_data_to_webhook_after_register' ), 10, 4 );

    }

    function arf_post_data_to_webhook_after_register( $user, $form_data, $form_id, $entry_id ){
        if (!$entry_id || !$form_id)
            return;

        $this->arf_after_submit_form($entry_id, $form_id);
    }

    /* Display html for show post values settings */
    function arf_post_values_after_redirect_to_url_html($id, $values){?>
        <div class="arf_submit_action_post_values_container">
            <div class="arf_submit_action_post_values_inner_container">
                <div class="arf_submit_action_post_values_enable">
                    <div class="arf_popup_checkbox_wrapper" style="margin-top:5px;">
                        <div class="arf_custom_checkbox_div" style="margin-top: 4px;">
                            <div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" class="arf_enable_disable_post_values" name="options[arf_show_post_value]" id="arf_show_post_value" value="1" <?php isset($values['arf_show_post_value']) ? checked($values['arf_show_post_value'], 1) : ''; ?> />
                                <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
                            <span>
                                <label for="arf_show_post_value" style="margin-left: 4px;"><?php echo esc_html__('Send Form Data internaly to Custom URL', 'ARForms'); ?> (<?php echo esc_html__('Webhook','ARForms'); ?>)</label>
                            </span>
                        </div>
                    </div>
                    <?php
                    $arf_post_values_style = 'display: none;';
                    if (isset($values['arf_show_post_value']) && $values['arf_show_post_value'] == 1) {
                        $arf_post_values_style = '';
                    }
                    ?>
                    <span class="arf_submit_action_post_values_inner_block" style="padding-top: 0px;padding-bottom: 0px;font-style: italic;<?php echo $arf_post_values_style; ?>">
                    <?php echo esc_html__('(Upon successful submission form entry data will be sent to below mentioned url using POST method.)','ARForms');?></span>
                </div>
                <?php
                $arf_post_values_style = 'display: none;';
                if (isset($values['arf_show_post_value']) && $values['arf_show_post_value'] == 1) {
                    $arf_post_values_style = '';
                }
                ?>
                <div class="arf_submit_action_post_values_inner_block arfmarginl15" style="<?php echo $arf_post_values_style; ?>">
                    <label for="arf_post_value_url" class="arf_dropdown_autoresponder_label"><?php echo esc_html__('Enter URL to Submit Data', 'ARForms'); ?></label>
                    <input type="text" id="arf_post_value_url" class="arf_large_input_box arf_post_values_url arf_post_values_url_width" name="options[arf_post_value_url]" value="<?php echo isset($values['arf_post_value_url']) ? $values['arf_post_value_url'] : ''; ?>" />
                    <span class="arferrmessage" id="arf_post_value_url_error"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                    <i class="arf_notes" style="float: left;width: 100%;"><?php echo esc_html__('Please insert url with http:// or https://.', 'ARForms'); ?></i>
                </div>
            </div>
      </div>
      <?php
    }

    function arf_after_submit_form_paypal($form_id, $entry_id, $txn_id) {
        if (!$entry_id || !$form_id)
            return;

        $this->arf_after_submit_form($entry_id, $form_id);

    }


    function arf_after_submit_form($entry_id, $form_id) {

        global $arfrecordmeta, $wpdb, $arfform, $MdlDb;

        $ar_form = $arfform->getOne($form_id);

        $options = ($ar_form != '' && isset($ar_form->options)) ? $ar_form->options : array();

        if (isset($options['arf_show_post_value']) && $options['arf_show_post_value'] == 1) {

            if (isset($options['arf_post_value_url']) && $options['arf_post_value_url'] != '') {

                $arposturl = $options['arf_post_value_url'];

                $entry_ids = array($entry_id);
                $values = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")", " ORDER BY fi.id");

                if (!isset($uploads) or ! isset($uploads['baseurl'])) {
                    $uploads = wp_upload_dir();
                }

                $request_string = array();

                foreach($values as $key=>$value) {

                    if( isset($value->field_type) && $value->field_type != '' && $value->field_id != '' ){

                        if( $this->arf_has_repeater_field( $value->field_id ) ){
                            $request_string[$value->field_id] = array();
                            if( $value->field_type == 'checkbox' ){
                                $fvalues = explode( '[ARF_JOIN]', $value->entry_value);
                                $n = 0;
                                foreach( $fvalues as $fval ){
                                    $fval_arr = explode('!|!',$fval);
                                    $checked_val = $fval_arr[0];
                                    $fchk_val = arf_json_decode( $fval_arr[0] );
                                    if( !isset( $request_string[$value->field_id][$n] ) ){
                                        $request_string[$value->field_id][$n] = array();
                                    }
                                    $request_string[$value->field_id][$n] = $fchk_val;
                                    $n++;
                                }
                            } else {
                                $request_string[$value->field_id] = explode('[ARF_JOIN]', $value->entry_value);
                            }
                        } else {
                            if ($value->field_type == 'file') {

                                $attach_file_values = explode('|', $value->entry_value);

                                $arf_uploaded_files = "";

                                foreach ($attach_file_values as $attach_file_val){

                                    if($attach_file_val != "") {

                                        $meta_field = $wpdb->get_row($wpdb->prepare("select `meta_value` from " . $wpdb->prefix . "postmeta where post_id = '%d' AND meta_key = '_wp_attached_file'",$attach_file_val));
                                        if( $meta_field ) {
                                            $file = $meta_field->meta_value;
                                            if ($file) {
                                                $file = str_replace('thumbs/', '', $file);
                                                $arf_uploaded_files .= $file.'|';
                                            }
                                        }
                                    }
                                }

                                if ($arf_uploaded_files != "") {
                                    $arf_uploaded_files = rtrim($arf_uploaded_files, "|");
                                    $request_string[$value->field_id] = $arf_uploaded_files;
                                }
                            } else {
                                if( $value->field_type == 'checkbox' || $value->field_type == 'arf_multiselect' ){
                                    $request_string[$value->field_id] = arf_json_decode( $value->entry_value, true );
                                } else if( $value->field_type == 'matrix' ){

                                    $field_opts = $arfform->arf_select_db_data(true, '', $MdlDb->fields, 'field_options', 'WHERE id = %d', array( $value->field_id ), '', '', '', false, true );
                                    $fopts = arf_json_decode( $field_opts->field_options, true );

                                    $rows = $fopts['rows'];
                                    $field_value = arf_json_decode( $value->entry_value, true );
                                    $final_field_value = array();
                                    foreach( $rows as $k => $val ){
                                        if( !empty( $field_value[$k] ) ){
                                            $final_field_value[$k] = $field_value[$k];
                                        } else {
                                            $final_field_value[$k] = '';
                                        }
                                    }

                                    $request_string[$value->field_id] = $final_field_value;
                                } else {
                                    $request_string[$value->field_id] = $value->entry_value;
                                }
                                

                            }
                        }

                    }
                }

                $arf_posts = array(
                    'method' => 'POST',
                    'timeout' => 5000,
                    'body' => $request_string,
                );

                $raw_response = wp_remote_post($arposturl, $arf_posts);

            }

        }
    }

    function arf_has_repeater_field( $field_id ){
        global $wpdb, $MdlDb;

        if( '' == $field_id || !isset( $field_id )){
            return false;
        }

        $field_data = $wpdb->get_row( $wpdb->prepare( "SELECT field_options FROM `".$MdlDb->fields."` WHERE id = %d", $field_id ) );

        if( !empty( $field_data ) ){
            $field_opt = arf_json_decode( $field_data->field_options, true );

            if( isset( $field_opt['parent_field_type'] ) && 'arf_repeater' == $field_opt['parent_field_type'] ){
                return true;
            }
        }
        return false;
    }
}

?>