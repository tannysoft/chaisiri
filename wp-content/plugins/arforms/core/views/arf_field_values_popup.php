<?php
global $armainhelper,$maincontroller;

$states = $armainhelper->get_us_states();

$current_year = date("Y");

$from_year = "1935";

$year_display = array();
for ($yr_counter = $from_year; $yr_counter <= $current_year; $yr_counter++) {
    $year_display[] = (string) $yr_counter;
}

$country_codes = $armainhelper->get_country_codes();

ksort($country_codes);

$country_codes = array_keys($country_codes);

$categories = get_categories(array(
  'hide_empty' => false
));

$tags = get_tags(array(
  'hide_empty' => false
));

$catg_name = array();
$tag_name = array();

foreach($categories as $ctgry){
    $catg_name[] = $ctgry->name . '|' . $ctgry->cat_ID;
}

foreach($tags as $tg){
    $tag_name[] = $tg->name . '|' . $tg->term_id;
}

$preset_options = array(
    addslashes(esc_html__('Countries', 'ARForms')) => $armainhelper->get_countries(),
    addslashes(esc_html__('U.S. States', 'ARForms')) => array_values($states),
    addslashes(esc_html__('U.S. State Abbreviations', 'ARForms')) => array_keys($states),
    addslashes(esc_html__('Age Group', 'ARForms')) => array(
    addslashes(esc_html__('Under 18', 'ARForms')),
    addslashes(esc_html__('18-24', 'ARForms')),
    addslashes(esc_html__('25-34', 'ARForms')),
    addslashes(esc_html__('35-44', 'ARForms')),
    addslashes(esc_html__('45-54', 'ARForms')),
    addslashes(esc_html__('55-64', 'ARForms')),
    addslashes(esc_html__('65 or Above', 'ARForms'))
    ),
    addslashes(esc_html__('Satisfaction', 'ARForms')) => array(
        addslashes(esc_html__('Very Satisfied', 'ARForms')),
        addslashes(esc_html__('Satisfied', 'ARForms')),
        addslashes(esc_html__('Neutral', 'ARForms')),
        addslashes(esc_html__('Unsatisfied', 'ARForms')),
        addslashes(esc_html__('Very Unsatisfied', 'ARForms')),
        addslashes(esc_html__('N/A', 'ARForms'))
    ),
    addslashes(esc_html__('Days', 'ARForms')) => array(
        
        addslashes(esc_html__('1', 'ARForms')), 
        addslashes(esc_html__('2', 'ARForms')), 
        addslashes(esc_html__('3', 'ARForms')), 
        addslashes(esc_html__('4', 'ARForms')), 
        addslashes(esc_html__('5', 'ARForms')), 
        addslashes(esc_html__('6', 'ARForms')),
        addslashes(esc_html__('7', 'ARForms')), 
        addslashes(esc_html__('8', 'ARForms')), 
        addslashes(esc_html__('9', 'ARForms')), 
        addslashes(esc_html__('10', 'ARForms')), 
        addslashes(esc_html__('11', 'ARForms')), 
        addslashes(esc_html__('12', 'ARForms')),
        addslashes(esc_html__('13', 'ARForms')), 
        addslashes(esc_html__('14', 'ARForms')), 
        addslashes(esc_html__('15', 'ARForms')), 
        addslashes(esc_html__('16', 'ARForms')), 
        addslashes(esc_html__('17', 'ARForms')), 
        addslashes(esc_html__('18', 'ARForms')),
        addslashes(esc_html__('19', 'ARForms')), 
        addslashes(esc_html__('20', 'ARForms')), 
        addslashes(esc_html__('21', 'ARForms')), 
        addslashes(esc_html__('22', 'ARForms')), 
        addslashes(esc_html__('23', 'ARForms')), 
        addslashes(esc_html__('24', 'ARForms')),
        addslashes(esc_html__('25', 'ARForms')), 
        addslashes(esc_html__('26', 'ARForms')), 
        addslashes(esc_html__('27', 'ARForms')), 
        addslashes(esc_html__('28', 'ARForms')), 
        addslashes(esc_html__('29', 'ARForms')), 
        addslashes(esc_html__('30', 'ARForms')),
        addslashes(esc_html__('31', 'ARForms') ),
    ),
    addslashes(esc_html__('Week Days', 'ARForms')) => array(
        addslashes(esc_html__('Sunday', 'ARForms')),
        addslashes(esc_html__('Monday', 'ARForms')),
        addslashes(esc_html__('Tuesday', 'ARForms')),
        addslashes(esc_html__('Wednesday', 'ARForms')),
        addslashes(esc_html__('Thursday', 'ARForms')),
        addslashes(esc_html__('Friday', 'ARForms')),
        addslashes(esc_html__('Saturday', 'ARForms'))
    ),
    addslashes(esc_html__('Months', 'ARForms')) => array(
        addslashes(esc_html__('January', 'ARForms')),
        addslashes(esc_html__('February', 'ARForms')),
        addslashes(esc_html__('March', 'ARForms')),
        addslashes(esc_html__('April', 'ARForms')),
        addslashes(esc_html__('May', 'ARForms')),
        addslashes(esc_html__('June', 'ARForms')),
        addslashes(esc_html__('July', 'ARForms')),
        addslashes(esc_html__('August', 'ARForms')),
        addslashes(esc_html__('September', 'ARForms')),
        addslashes(esc_html__('October', 'ARForms')),
        addslashes(esc_html__('November', 'ARForms')),
        addslashes(esc_html__('December', 'ARForms')),
    ),
    addslashes(esc_html__('Years', 'ARForms')) => $year_display,
    addslashes(esc_html__('Prefix', 'ARForms')) => array(
    addslashes(esc_html__('Mr', 'ARForms')),
    addslashes(esc_html__('Mrs', 'ARForms')),
    addslashes(esc_html__('Ms', 'ARForms')),
    addslashes(esc_html__('Miss', 'ARForms')),
    addslashes(esc_html__('Sr', 'ARForms')),
    ),
    addslashes(esc_html__('Telephone Country Code', 'ARForms')) => $country_codes,
);

if( !empty( $tag_name ) ){
    $tag_key = addslashes(esc_html__('Tags', 'ARForms'));
    $preset_options[$tag_key] = array('wp_post_tags');    
}

if( !empty( $catg_name ) ){
    $cat_key = addslashes(esc_html__('Categories', 'ARForms'));
    $preset_options[$cat_key] = array('wp_categories');
}

if ( class_exists( 'WooCommerce' ) ) {
    $products = wc_get_products( array(
        'limit' => -1
    ) );

    $product_display = array();
    foreach($products as $product){
        $product_display[] = $product->get_title() . '|' . $product->get_price();
    }
    if( !empty( $product_display ) ){
        $preset_options[addslashes(esc_html__('Woocommerce Products', 'ARForms'))] = array('woocom_products');
    }
} 

array_unshift($preset_options[addslashes(esc_html__('Countries', 'ARForms'))],'');

$arf_preset_values = maybe_unserialize(get_option('arf_preset_values'));

if (!empty($arf_preset_values) && is_array($arf_preset_values)) {
    $file_preset_arr = '';
    foreach ($arf_preset_values as $key => $value) {
        $file_preset_arr = 'csv_preset_'.$key;
        
        $preset_options[$arf_preset_values[$key]['title']] = array($file_preset_arr);
    }
}
$arf_preset_fields = $preset_options;
?>
<div class="arf_field_values_model" id="arf_field_values_model_skeleton">
    <div class="arf_field_values_model_header"><?php echo addslashes(esc_html__('Edit Options', 'ARForms')); ?></div>
    <div class="arf_field_values_model_container">
        <div class="arf_field_values_content_row">
            <div class="arf_field_values_content_cell" id="use_image">
                <label class="arf_field_values_content_cell_label"><?php echo esc_html__('Use image over options', 'ARForms'); ?>:</label>
                <div class="arf_field_values_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('No', 'ARForms')); ?></span>
                    </label>
                    <div class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch" name="use_image" data-field-id="{arf_field_id}" value="1" id="arf_field_use_image" />
                        <span class="arf_js_switch"></span>
                    </div>
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                    </label>
                    <span class="arfhelptip" data-title="<?php echo sprintf(addslashes(esc_html__('Use image over %s label', 'ARForms')), '{arf_field_type}'); ?>"><svg width="18px" height="18px"><?php echo ARF_TOOLTIP_ICON; ?></svg></span>
                </div>
            </div>
            <div class="arf_field_values_content_cell" id="separate_value">
                <label class="arf_field_values_content_cell_label"><?php echo addslashes(esc_html__('Use separate value', 'ARForms')); ?></label>
                <div class="arf_field_values_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('No', 'ARForms')); ?></span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch arf_hide_opacity " name="separate_value" data-field-id="{arf_field_id}" id="arf_field_separate_value" value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                    </label>
                    <?php $title = addslashes(esc_html__('Add a separate value to use for calculations, email routing, saving to database and many other uses. The option values are saved while option labels are shown in the form', 'ARForms')); ?>
                    <span class="arfhelptip" data-title="<?php echo $title; ?>"><svg width="18px" height="18px"><?php echo ARF_TOOLTIP_ICON; ?></svg></span>
                </div>
            </div>
            <div class="arf_field_values_content_cell" id="dynamic_option">
                <label class="arf_field_values_content_cell_label"><?php echo addslashes(esc_html__('Dynamic Option', 'ARForms')); ?></label>
                <div class="arf_field_values_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('No', 'ARForms')); ?></span>
                    </label>
                    <div class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch arf_hide_opacity arf_dynamic_option_{arf_field_id}" name="dynamic_option" data-field-id="{arf_field_id}" id="arf_field_dynamic_option" value="1" />
                        <span class="arf_js_switch"></span>
                    </div>
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                    </label>
                    <?php $title = addslashes(esc_html__('Add a Dynamic option to use csv data as parent-child element.', 'ARForms')); ?>
                    <span class="arfhelptip" data-title="<?php echo $title; ?>"><svg width="18px" height="18px"><?php echo ARF_TOOLTIP_ICON; ?></svg></span>
                </div>
            </div>
            <div class="arf_field_values_content_cell arf_full_width_cell" id="options">
                <div class="arf_field_values_content_cell_input">
                    <div class="arf_field_value_grid_wrapper">
                        <div class="arf_field_value_grid_container">
                            <div class="arf_field_value_grid_header">
                                <div class="arf_field_value_grid_header_cell_input">
                                    <div class='arf_field_radio_reset_wrapper' data-content="<?php echo esc_html__('Reset','ARForms'); ?>">
                                        <i class="fas fa-redo"></i>
                                    </div>
                                </div>
                                <div class="arf_field_value_grid_header_cell_label"><?php echo esc_html__('Option label', 'ARForms'); ?></div>
                                <div class="arf_field_value_grid_header_cell_value"><?php echo addslashes(esc_html__('Saved Value', 'ARForms')); ?></div>
                                <div class="arf_field_value_grid_header_cell_action"></div>
                            </div>
                            <div class="arf_field_value_grid_data_wrapper" id="arf_field_value_grid_data_wrapper_{arf_field_id}">
                            </div>
                            <input type="hidden" name="arf_radio_image_name" id="arf_radio_image_name" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="arf_field_values_content_cell arf_full_width_cell arf_dynamic_option_filed add_dynamic_option_parent_select_{arf_field_id}" id="add_dynamic_option_parent_select">
                <label for=""><?php esc_html_e('Select Parent', 'ARForms'); ?></label>
                <div class='arf_new_dynamic_option_field_parent_select_content_wrapper arf_dynamic_field_content_wrapper_{arf_field_id}'>
                    <?php 
                        $arf_parent_option_arr = array('' => addslashes (esc_html__('Select', 'ARForms')));
                        $select_attrs['class'] = 'arf_dynamic_option_parent_data';
                        echo $maincontroller->arf_selectpicker_dom( '', 'frm_bulk_options-select-{arf_field_id}', '', 'width:225px;', '', $select_attrs , $arf_parent_option_arr );
                    ?>                    
                </div>
            </div>            
                

            <div class="arf_field_values_content_cell arf_full_width_cell arf_dynamic_option_filed" id="add_dynamic_option">
                <div class='arf_new_dynamic_option_field_content_wrapper arf_dynamic_field_content_wrapper_{arf_field_id}'>
                    <span class="arf_new_preset_field_data_uploader">
                        <input type="file" id="arf_dynamic_option_data_{arf_field_id}" class="arf_preset_data" name="arf_preset_data" data-val="{arf_field_id}" />
                        <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Upload only CSV file.', 'ARForms')).'<br/>'.addslashes(esc_html__('Please upload tab separated CSV file.','ARForms')); ?></span>
                    </span>
                    <button type="button" class="arf_new_dynamic_option_apply_button" data-field-type="{arf_field_type}" data-field-id="{arf_field_id}" ><?php echo addslashes(esc_html__('Apply', 'ARForms')); ?></button>
                </div>
            </div>

            

            <div class="arf_field_values_content_cell arf_full_width_cell" id="use_preset_fields">
                <label class="arf_field_values_content_cell_label"></label>
                <div class="arf_field_values_content_cell_input">
                    <button type="button" onClick="arfshowbulkfieldoptions1('{arf_field_id}')" class="arf_preset_field_button" data-field-id="{arf_field_id}"><?php echo addslashes(esc_html__('Preset Field Choices', 'ARForms')); ?></button>
                    <div class="arf_preset_field_dropdown_wrapper" id="arfshowfieldbulkoptions-{arf_field_id}">
                        <?php
                            $preset_field_attr = array(
                                'onClick' => 'arfstorebulkoptionvalue("{arf_field_id}", this.value);',
                                'data-skip' => 'true'
                            );

                            $preset_field_opts = array( '' => addslashes(esc_html__('Select', 'ARForms')) );

                            $preset_list_class = array();
                            foreach ($arf_preset_fields as $preset_label => $preset_values) {
                                $final_preset_values = $preset_values;
                                if (array_keys($preset_values) !== range(0, count($preset_values) - 1)) {
                                    $final_preset_values = array();
                                    foreach ($preset_values as $new_preset_key => $new_preset_data) {
                                        $new_preset_key_data = $new_preset_key;
                                        if ($new_preset_key_data == '') {
                                            $new_preset_key_data = $new_preset_data;
                                        }
                                        $final_preset_values[] = htmlspecialchars($new_preset_key_data, ENT_QUOTES, 'UTF-8') . '|' . htmlspecialchars($new_preset_data, ENT_QUOTES, 'UTF-8');
                                    }
                                }

                                $fields_val = json_encode(array_values($final_preset_values));
                                $val = htmlspecialchars($fields_val, ENT_QUOTES, 'UTF-8');

                                $preset_field_opts[$val] = htmlspecialchars($preset_label, ENT_QUOTES, 'UTF-8');


                                if( "[\"wp_post_tags\"]" == $fields_val || "[\"wp_categories\"]" == $fields_val || "[\"woocom_products\"]" == $fields_val ){
                                    $preset_list_class[$val] = 'arf_field_data_dynamic';
                                }

                                if (!empty($arf_preset_values) && is_array($arf_preset_values)) {
                                    foreach ($arf_preset_values as $key => $value) {
                                        if( "[\"csv_preset_".$key."\"]" == $fields_val ){
                                            $preset_list_class[$val] = 'arf_field_data_dynamic';    
                                        }
                                    }
                                }

                            }

                            echo $maincontroller->arf_selectpicker_dom( '', 'frm_bulk_options-select-{arf_field_id}', '', 'width:225px;', '', $preset_field_attr, $preset_field_opts, false, $preset_list_class, false, array(), false, array(), false, '', '', false );
                        ?>

                        <button type="button" class="arf_preset_apply_button" data-field-type="{arf_field_type}" data-field-id="{arf_field_id}" ><?php echo addslashes(esc_html__('Apply', 'ARForms')); ?></button>
                        <button type="button" class="arf_preset_cancel_button arf_field_cancel_button" data-field-id="{arf_field_id}"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
                        <span class="arf_preset_apply_field_loader" id="arf_preset_apply_field_loader_{arf_field_id}"><?php echo addslashes(esc_html__('Saving', 'ARForms')) . '...'; ?></span>
                    </div>

                </div>
            </div>
            <div class="arf_field_values_content_cell arf_full_width_cell" id="add_preset_fields">
                <label class="arf_field_values_content_cell_label" style="display:none;"></label>
                <div class="arf_field_values_content_cell_input">
                    <button type="button" onClick="arf_preset_field_show('{arf_field_id}')" class="arf_preset_field_button" data-field-id="{arf_field_id}"><?php echo addslashes(esc_html__('Add New Preset Choices', 'ARForms')); ?></button>
                    <div class='arf_new_preset_field_content_wrapper arf_preset_field_content_wrapper_{arf_field_id}'>
                        <span class="arf_new_preset_field_data_uploader">
                            <input type="file" id="arf_preset_data_{arf_field_id}" class="arf_preset_data" name="arf_preset_data" data-val="{arf_field_id}" />
                            <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Upload only CSV file.', 'ARForms')).'<br/>'.addslashes(esc_html__('Please upload tab separated CSV file.','ARForms')); ?></span>
                        </span>
                        <span class="arf_custom_checkbox_wrapper">
                            <input type="checkbox" class="arf_custom_checkbox arf_enable_new_preset_field_save" value="1" name="arf_preset_future_use" id="arf_preset_future_use_{arf_field_id}" data-field-id="{arf_field_id}" />
                            <svg width="18px" height="18px">
                            <path id='arfcheckbox_unchecked' d='M15.205,16.852H3.774c-1.262,0-2.285-1.023-2.285-2.286V3.136  c0-1.263,1.023-2.286,2.285-2.286h11.431c1.263,0,2.286,1.023,2.286,2.286v11.43C17.491,15.829,16.467,16.852,15.205,16.852z M15.49,2.851h-12v12h12V2.851z' />
                            <path id='arfcheckbox_checked' d='M15.205,16.852H3.774c-1.262,0-2.285-1.023-2.285-2.286V3.136  c0-1.263,1.023-2.286,2.285-2.286h11.431c1.263,0,2.286,1.023,2.286,2.286v11.43C17.491,15.829,16.467,16.852,15.205,16.852z   M15.49,2.851h-12v12h12V2.851z M5.93,6.997l2.557,2.558l4.843-4.843l1.617,1.616l-4.844,4.843l0.007,0.007l-1.616,1.616  l-0.007-0.007l-0.006,0.007l-1.617-1.616l0.007-0.007L4.314,8.614L5.93,6.997z' />
                            </svg>
                            <label for="arf_preset_future_use_{arf_field_id}"><?php echo addslashes(esc_html__('Save for future use', 'ARForms')); ?></label>
                        </span>
                        <input type="text" class="arf_preset_field_title inplace_field" name="arf_preset_title" placeholder="<?php echo addslashes(esc_html__('Preset Title', 'ARForms')); ?>" id="arf_preset_field_title_{arf_field_id}" />
                        <button type="button" class="arf_new_preset_apply_button" data-field-type="{arf_field_type}" data-field-id="{arf_field_id}" ><?php echo addslashes(esc_html__('Apply', 'ARForms')); ?></button>
                        <button type="button" class="arf_new_preset_cancel_button arf_field_cancel_button" data-field-id="{arf_field_id}"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
                    </div>
                </div>

            </div>
                  <!-- New Width related changes -->
             <div class="arf_field_values_content_cell" id="arf_check_icon" style="width:98%">
                <div class="arf_field_values_content_cell arf_chk_styling">
                    <label class="arf_field_values_content_cell_label"><?php echo esc_html__('Image Width', 'ARForms'); ?>:</label>
                    <div class="chekbox_image_width_popup" id="image_width_popup" >
                        <div class="checkbox_width_text">
                            <div class=" arfwidth108 arf_checkbox_border">
                                <input type="text" class="arf_field_option_input_text" name="image_width" id="image_width" value="120">

                            </div> 
                        </div>
                        <span class="arfpx_checkboxwidth">px</span>
                    </div>
                </div>
                <!---- icon style  ------>  
                     <div class="arf_field_values_content_cell arf_chk_styling">
                        <label class="arf_field_values_content_cell_label"><?php echo esc_html__('Checked Icon Styling', 'ARForms'); ?>:</label>
                            <div class="arf_field_option_content_cell_input">
                                <div class="arf_field_prefix_suffix_wrapper" id="arf_field_prefix_suffix_wrapper_{arf_field_id}">
                                    <div class="arf_prefix_wrapper" id="arf_check_icon">
                                       <div class="arf_prefix_suffix_container_wrapper" data-action="edit" data-field="prefix" field-id="{arf_field_id}" id="arf_edit_prefix_{arf_field_id}" data-toggle="arfmodal" href="#arf_fontawesome_modal" data-field_type="checkbox">
                                            <div class="arf_prefix_container" id="arf_select_prefix_{arf_field_id}">
                                                    <div class="arf_prefix_container" id="arf_select_prefix_{arf_field_id}">
                                                        <?php echo "<i id='arf_select_prefix_{arf_field_id}' class='arf_prefix_suffix_icon fa fa-check'></i>"; ?>
                                                    </div>
                                            </div>
                                        <div class="arf_prefix_suffix_action_container">
                                            <div class="arf_prefix_suffix_action" title="Change Icon" style="<?php echo (is_rtl()) ? 'margin-right:5px;' : 'margin-left:5px;';?>">
                                                <i class="fas fa-caret-down fa-lg"></i>
                                            </div>
                                        </div>
                                        </div>
                                        <input type="hidden" name="enable_arf_prefix" id="enable_arf_prefix_{arf_field_id}" />
                                        <input type="hidden" name="arf_prefix_icon" id="arf_prefix_icon_{arf_field_id}" value="fas fa-check" />
                                    </div>
                                </div>
                            </div>
                      </div>
                </div>
            
        </div>
    </div>
    <div class="arf_field_values_model_footer">
        <button type="button" class="arf_field_values_close_button"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
        <button type="button" class="arf_field_values_submit_button" data-field_id=""><?php echo esc_html__('OK', 'ARForms'); ?></button>
    </div>
</div>