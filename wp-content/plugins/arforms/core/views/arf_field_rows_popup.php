<?php
global $armainhelper;

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
    $preset_options[$tag_key] = $tag_name;    
}

if( !empty( $catg_name ) ){
    $cat_key = addslashes(esc_html__('Categories', 'ARForms'));
    $preset_options[$cat_key] = $catg_name;
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
        $preset_options[addslashes(esc_html__('Woocommerce Products', 'ARForms'))] = $product_display;
    }
} 

array_unshift($preset_options[addslashes(esc_html__('Countries', 'ARForms'))],'');

$arf_preset_values = maybe_unserialize(get_option('arf_preset_values'));

if (!empty($arf_preset_values) && is_array($arf_preset_values)) {
    foreach ($arf_preset_values as $data) {
        $preset_data = array();
        
        foreach ($data['data'] as $sub_data) {
            $preset_data[] = htmlspecialchars($sub_data['label'], ENT_QUOTES, 'UTF-8').'|'.htmlspecialchars($sub_data['value'], ENT_QUOTES, 'UTF-8');
        }
        $preset_options[$data['title']] = $preset_data;
    }
}
$arf_preset_fields = $preset_options;
?>
<div class="arf_field_values_model" id="arf_field_rows_model_skeleton">
    <div class="arf_field_values_model_header"><?php echo addslashes(esc_html__('Edit Rows', 'ARForms')); ?></div>
    <div class="arf_field_values_model_container">
        <div class="arf_field_values_content_row">
            <div class="arf_field_rows_content_cell arf_full_width_cell" id="options">
                <div class="arf_field_rows_content_cell_input">
                    <div class="arf_field_row_grid_wrapper">
                        <div class="arf_field_row_grid_container">
                            <div class="arf_field_row_grid_header">
                                <div class="arf_field_row_grid_header_cell_label"><?php echo esc_html__('Option label', 'ARForms'); ?></div>
                                <div class="arf_field_row_grid_header_cell_action"></div>
                            </div>
                            <div class="arf_field_row_grid_data_wrapper" id="arf_field_row_grid_data_wrapper_{arf_field_id}">
                            </div>
                            <input type="hidden" name="arf_radio_image_name" id="arf_radio_image_name" />
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