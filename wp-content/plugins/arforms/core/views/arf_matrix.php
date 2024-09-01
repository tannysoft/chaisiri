<?php

define('ARF_MATRIX_SLUG', 'matrix');

global $arf_matrix;
$arf_matrix = new arf_matrix();

class arf_matrix{

	function __construct(){

		add_filter( 'arfaavailablefields', array( $this, 'arf_add_matrix_field_in_list' ) );

		add_filter( 'arform_input_fields', array( $this, 'arf_add_matrix_input_field') );

		add_filter( 'arf_manage_field_element_order_outside', array( $this, 'arf_add_matrix_in_order' ) );

		add_filter( 'arf_migrate_field_type_from_outside', array( $this, 'arf_add_matrix_for_type_conversion') );

		add_filter( 'arf_positioned_field_options_icon', array( $this, 'arf_positioned_field_options_icon_for_matrix' ), 10, 2 );

		add_filter( 'arf_new_field_array_filter_outside', array( $this, 'arf_matrix_field_for_default_theme'), 10, 4);

		add_filter( 'arf_new_field_array_materialize_filter_outside', array( $this, 'arf_matrix_field_for_material_theme'), 10, 4 );

		add_filter( 'arf_new_field_array_materialize_outlined_filter_outside', array( $this, 'arf_matrix_field_for_material_outline_theme'), 10, 4 );

		add_filter( 'arfavailablefieldsbasicoptions', array( $this, 'arf_matrix_field_basic_options') );

		add_filter( 'arf_field_type_label_filter', array( $this, 'arf_field_label_for_options') );

		add_filter( 'arf_field_option_icon_render_outside', array( $this, 'arf_field_option_icon_for_matrix' ), 10, 8 );

		add_filter( 'arf_change_field_icons_outside', array( $this, 'arf_add_field_row_icons' ) );

		add_action( 'arfdisplayaddedfields', array( $this, 'arf_display_matrix_in_editor' ), 10, 3 );

		add_filter( 'arf_disply_multicolumn_fieldolumn_field_outside', array( $this, 'arf_hide_multicolumn_box_for_matrix' ), 10, 2 );

		add_filter( 'arf_controls_added_class_outside_materialize', array( $this, 'arf_add_class_for_matrix_for_editor' ), 10, 2 );

		add_action( 'arf_set_field_icons_at_start', array( $this, 'arf_add_icons_for_matrix' ) );

		add_action( 'arf_add_fieldiconbox_class_outside', array( $this, 'arf_add_fieldiconbox_class_for_matrix') );

		add_action( 'arf_render_field_extra_model_outside', array( $this, 'arf_add_field_model_for_matrix') );

		add_action( 'arf_load_field_model_outside', array( $this, 'arf_load_matrix_field_rows_model_skeleton') );

		add_filter( 'form_fields', array( $this, 'arf_render_matrix_field' ), 10, 11 );

		add_filter( 'arf_input_style_label_position_outside', array( $this, 'arf_set_matrix_label_position'), 10, 3 );

		add_filter( 'arf_change_edit_select_array_for_matrix', array( $this, 'arf_render_matrix_field_edit_array' ), 10, 3 );

		add_filter( 'arfemailvalue', array( $this, 'arf_matrix_value_for_email'), 10, 3 );

		add_filter( 'arf_onchange_only_click_event_outside', array( $this, 'arf_set_onchange_event_for_matrix'), 10 );
	}

	function arf_add_matrix_field_in_list( $fields ){

		$fields['matrix'] = array(
            'icon' => "<svg width='34' height='34' viewBox='0 0 40 40' fill='none' xmlns='http://www.w3.org/2000/svg'><rect x='0.994141' y='0.994141' width='38.0119' height='38.0119' rx='1.81288' stroke='#4E5462' stroke-width='1.9006'/><rect x='26.3555' y='1.95947' width='1.462' height='36.1216' fill='#4E5462'/><rect x='14.6602' y='1.93701' width='1.462' height='36.1445' fill='#4E5462'/><rect x='1.93896' y='15.4463' width='1.462' height='36.1216' transform='rotate(-90 1.93896 15.4463)' fill='#4E5462'/><rect x='1.93896' y='27.519' width='1.462' height='36.1216' transform='rotate(-90 1.93896 27.519)' fill='#4E5462'/><rect x='5.15674' y='7.229' width='6.28659' height='1.462' rx='0.467839' fill='#4E5462'/><rect x='5.15674' y='31.3496' width='6.28659' height='1.462' rx='0.467839' fill='#4E5462'/><rect x='5.15674' y='19.2759' width='6.87139' height='1.462' rx='0.467839' fill='#4E5462'/><circle cx='21.2316' cy='7.93665' r='2.193' stroke='#4E5462' stroke-width='0.584799'/><circle cx='21.2321' cy='20.7272' r='2.193' stroke='#4E5462' stroke-width='0.584799'/><circle cx='32.9425' cy='32.7999' r='2.193' stroke='#4E5462' stroke-width='0.584799'/><circle cx='21.2316' cy='32.7999' r='2.193' fill='#4E5462'/><circle cx='32.9425' cy='20.7272' r='2.193' fill='#4E5462'/><circle cx='32.9425' cy='7.93665' r='2.193' fill='#4E5462'/></svg>",
            'label' => addslashes(esc_html__('Matrix','ARForms'))
        );

        return $fields;

	}

	function arf_add_matrix_input_field( $inputFields ){

		array_push($inputFields, 'matrix' );

		return $inputFields;
	}

	function arf_add_matrix_in_order( $fields_order ){

		array_push( $fields_order, 'matrix' );

		return $fields_order;
	}

	function arf_add_matrix_for_type_conversion( $field_types ){

		return $field_types;

	}

	function arf_positioned_field_options_icon_for_matrix( $positioned_icon, $field_icons ){

		global $arfieldcontroller;
		$field_icons['arf_edit_option_icon'] = $arfieldcontroller->arf_get_field_control_icons('edit_column_icons', '', '{arf_field_id}', '', 'matrix', '{arf_form_id}', addslashes( esc_html__('Manage Columns', 'ARForms') ) );
		$field_icons['arf_edit_row_icon'] = $arfieldcontroller->arf_get_field_control_icons( 'edit_row_options', '', '{arf_field_id}', '', 'matrix', '{arf_form_id}', addslashes( esc_html__('Manage Rows', 'ARForms' ) ) );

		$positioned_icon['matrix'] = "{$field_icons['arf_edit_row_icon']}{$field_icons['arf_edit_option_icon']}{$field_icons['field_require_icon']}".str_replace('{arf_field_type}', 'matrix', $field_icons['arf_field_duplicate_icon'])."{$field_icons['field_delete_icon']}".str_replace('{arf_field_type}', 'matrix',$field_icons['field_option_icon'])."{$field_icons['arf_field_move_icon']}";

		return $positioned_icon;
	}

	function arf_matrix_field_for_default_theme( $fields, $field_icons, $field_json, $positioned_field_icons ){

		global $arfieldhelper;

		$field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();

		$field_order_matrix = isset($field_opt_arr['matrix']) ? $field_opt_arr['matrix'] : '';

		$field_data_array = $field_json;
        $field_data_obj_matrix = $field_data_array->field_data->matrix;

        $fields['matrix'] = "
        <div class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'>
        	<div class='sortable_inner_wrapper edit_field_type_matrix' id='arfmainfieldid_{arf_field_id}' inner_class='arf_1col'>
        		<div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container arf_field_{arf_field_id}'>
        			<div class='fieldname-row' style='display : block;'>
        				<div class='fieldname'>
        					<label class='arf_main_label' id='field_{arf_field_id}'>
        						<span class='arfeditorfieldopt_label arf_edit_in_place'>
        							<input type='matrix' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Matrix Question Title' data-field-id='{arf_field_id}' />
        						</span>
        						<span id='require_field_{arf_field_id}'>
        							<a href='javascript:void(0);' onclick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title=". esc_html__('Click to mark as not compulsory field', 'ARForms')."></a>
        						</span>
        					</label>
        				</div>
        			</div>
        		<div class='arf_fieldiconbox arf_fieldiconbox_with_edit_row_column_option' data-field_id='{arf_field_id}'>
        			{$positioned_field_icons['matrix']}
        		</div>
        		<div class='controls arf_matrix_field_control_wrapper'>
        			<table cellpadding='0' cellspacing='0'>
        				<thead>
        					<tr>
        						<th></th>
        						<th align='center'>".esc_html__( 'Bad', 'ARForms')."</th>
        						<th align='center'>".esc_html__( 'Average', 'ARForms')."</th>
        						<th align='center'>".esc_html__( 'Excellent', 'ARForms')."</th>
        					</tr>
        				</thead>
        				<tbody>
        					<tr>
        						<td>".esc_html__('How is the food quality?', 'ARForms')."</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_standard_radio'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-0_0' name='item_meta[{arf_field_id}][0]' value='Bad' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_standard_radio'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_0' name='item_meta[{arf_field_id}][0]' value='Average' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_standard_radio'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-2_0' name='item_meta[{arf_field_id}][0]' value='Excellent' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        					</tr>
        					<tr>
        						<td>".esc_html__('How is the staff behaviour?', 'ARForms')."</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_standard_radio'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_0' name='item_meta[{arf_field_id}][1]' value='Bad' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_standard_radio'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_1' name='item_meta[{arf_field_id}][1]' value='Average' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_standard_radio'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_2' name='item_meta[{arf_field_id}][1]' value='Excellent' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        					</tr>
        					<tr>
        						<td>".esc_html__('How is the hotel environment?', 'ARForms')."</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_standard_radio'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-2_0' name='item_meta[{arf_field_id}][2]' value='Bad' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_standard_radio'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-2_1' name='item_meta[{arf_field_id}][2]' value='Average' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_standard_radio'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-3_0' name='item_meta[{arf_field_id}][2]' value='Excellent' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        					</tr>
        				</tbody>
        			</table>
    				<div class='arf_field_description' id='field_description_{arf_field_id}'></div>
    				<div class='help-block'></div>
        		</div>
        		<input type='hidden' class='arf_field_data_hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_matrix))."' data-field_options='".json_encode($field_order_matrix)."' />
        		<div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'>
        			<div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div>
        			<div class='arf_field_option_model_container'>
        				<div class='arf_field_option_content_row'></div>
        			</div>
        			<div class='arf_field_option_model_footer'>
        				<button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button>
        				<button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
        			</div>
        		</div>
        		<div class='arf_field_values_model' id='arf_field_values_model_skeleton_{arf_field_id}'>
		            <div class='arf_field_values_model_header'>".esc_html__('Edit Columns','ARForms')."</div>
	                <div class='arf_field_values_model_container'>
	                    <div class='arf_field_values_content_row'>
	                        <div class='arf_field_values_content_loader'>
	                            <svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
	                        </div>
	                    </div>
	                </div>
	                <div class='arf_field_values_model_footer'>
	                    <button type='button' class='arf_field_values_close_button'>".esc_html__('Cancel','ARForms')."</button>
	                    <button type='button' class='arf_field_values_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
	                </div>
	            </div>
		        <div class='arf_field_row_values_model' id='arf_field_rows_model_skeleton_{arf_field_id}'>
		        	<div class='arf_field_values_model_header'>".esc_html__('Edit Rows','ARForms')."</div>
	                <div class='arf_field_values_model_container'>
	                    <div class='arf_field_values_content_row'>
	                        <div class='arf_field_values_content_loader'>
	                            <svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
	                        </div>
	                    </div>
	                </div>
	                <div class='arf_field_values_model_footer'>
	                    <button type='button' class='arf_field_rows_close_button'>".esc_html__('Cancel','ARForms')."</button>
	                    <button type='button' class='arf_field_rows_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
	                </div>
	            </div>
        	</div>
        </div>";

		return $fields;
	}

	function arf_matrix_field_for_material_theme( $material_fields, $field_icons, $field_json, $positioned_field_icons ){

		global $arfieldhelper;

		$field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();

		$field_order_matrix = isset($field_opt_arr['matrix']) ? $field_opt_arr['matrix'] : '';

		$field_data_array = $field_json;
        $field_data_obj_matrix = $field_data_array->field_data->matrix;

        $material_fields['matrix'] = "
        <div class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'>
        	<div class='sortable_inner_wrapper edit_field_type_matrix' id='arfmainfieldid_{arf_field_id}' inner_class='arf_1col'>
        		<div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container arf_field_{arf_field_id}'>
        			<div class='fieldname-row' style='display : block;'>
        				<div class='fieldname'>
        					<label class='arf_main_label' id='field_{arf_field_id}'>
        						<span class='arfeditorfieldopt_label arf_edit_in_place'>
        							<input type='matrix' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Matrix Question Title' data-field-id='{arf_field_id}' />
        						</span>
        						<span id='require_field_{arf_field_id}'>
        							<a href='javascript:void(0);' onclick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title=". esc_html__('Click to mark as not compulsory field', 'ARForms')."></a>
        						</span>
        					</label>
        				</div>
        			</div>
        		<div class='arf_fieldiconbox arf_fieldiconbox_with_edit_row_column_option' data-field_id='{arf_field_id}'>
        			{$positioned_field_icons['matrix']}
        		</div>
        		<div class='controls arf_matrix_field_control_wrapper'>
        			<table cellpadding='0' cellspacing='0'>
        				<thead>
        					<tr>
        						<th></th>
        						<th align='center'>".esc_html__( 'Bad', 'ARForms')."</th>
        						<th align='center'>".esc_html__( 'Average', 'ARForms')."</th>
        						<th align='center'>".esc_html__( 'Excellent', 'ARForms')."</th>
        					</tr>
        				</thead>
        				<tbody>
        					<tr>
        						<td>".esc_html__('How is the food quality?', 'ARForms')."</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-0_0' name='item_meta[{arf_field_id}][0]' value='Bad' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_0' name='item_meta[{arf_field_id}][0]' value='Average' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-2_0' name='item_meta[{arf_field_id}][0]' value='Excellent' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        					</tr>
        					<tr>
        						<td>".esc_html__('How is the staff behaviour?', 'ARForms')."</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_0' name='item_meta[{arf_field_id}][1]' value='Bad' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_1' name='item_meta[{arf_field_id}][1]' value='Average' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_2' name='item_meta[{arf_field_id}][1]' value='Excellent' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        					</tr>
        					<tr>
        						<td>".esc_html__('How is the hotel environment?', 'ARForms')."</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-2_0' name='item_meta[{arf_field_id}][2]' value='Bad' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-2_1' name='item_meta[{arf_field_id}][2]' value='Average' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-3_0' name='item_meta[{arf_field_id}][2]' value='Excellent' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        					</tr>
        				</tbody>
        			</table>
    				<div class='arf_field_description' id='field_description_{arf_field_id}'></div>
    				<div class='help-block'></div>
        		</div>
        		<input type='hidden' class='arf_field_data_hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_matrix))."' data-field_options='".json_encode($field_order_matrix)."' />
        		<div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'>
        			<div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div>
        			<div class='arf_field_option_model_container'>
        				<div class='arf_field_option_content_row'></div>
        			</div>
        			<div class='arf_field_option_model_footer'>
        				<button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button>
        				<button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
        			</div>
        		</div>
        		<div class='arf_field_values_model' id='arf_field_values_model_skeleton_{arf_field_id}'>
		            <div class='arf_field_values_model_header'>".esc_html__('Edit Columns','ARForms')."</div>
	                <div class='arf_field_values_model_container'>
	                    <div class='arf_field_values_content_row'>
	                        <div class='arf_field_values_content_loader'>
	                            <svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
	                        </div>
	                    </div>
	                </div>
	                <div class='arf_field_values_model_footer'>
	                    <button type='button' class='arf_field_values_close_button'>".esc_html__('Cancel','ARForms')."</button>
	                    <button type='button' class='arf_field_values_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
	                </div>
	            </div>
		        <div class='arf_field_row_values_model' id='arf_field_rows_model_skeleton_{arf_field_id}'>
		        	<div class='arf_field_values_model_header'>".esc_html__('Edit Rows','ARForms')."</div>
	                <div class='arf_field_values_model_container'>
	                    <div class='arf_field_values_content_row'>
	                        <div class='arf_field_values_content_loader'>
	                            <svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
	                        </div>
	                    </div>
	                </div>
	                <div class='arf_field_values_model_footer'>
	                    <button type='button' class='arf_field_rows_close_button'>".esc_html__('Cancel','ARForms')."</button>
	                    <button type='button' class='arf_field_rows_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
	                </div>
	            </div>
        	</div>
        </div>";

		return $material_fields;
	}

	function arf_matrix_field_for_material_outline_theme( $material_outline_fields, $field_icons, $field_json, $positioned_field_icons ){

		global $arfieldhelper;

		$field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();

		$field_order_matrix = isset($field_opt_arr['matrix']) ? $field_opt_arr['matrix'] : '';

		$field_data_array = $field_json;
        $field_data_obj_matrix = $field_data_array->field_data->matrix;

        $material_outline_fields['matrix'] = "
        <div class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'>
        	<div class='sortable_inner_wrapper edit_field_type_matrix' id='arfmainfieldid_{arf_field_id}' inner_class='arf_1col'>
        		<div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container arf_field_{arf_field_id}'>
        			<div class='fieldname-row' style='display : block;'>
        				<div class='fieldname'>
        					<label class='arf_main_label' id='field_{arf_field_id}'>
        						<span class='arfeditorfieldopt_label arf_edit_in_place'>
        							<input type='matrix' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Matrix Question Title' data-field-id='{arf_field_id}' />
        						</span>
        						<span id='require_field_{arf_field_id}'>
        							<a href='javascript:void(0);' onclick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title=". esc_html__('Click to mark as not compulsory field', 'ARForms')."></a>
        						</span>
        					</label>
        				</div>
        			</div>
        		<div class='arf_fieldiconbox arf_fieldiconbox_with_edit_row_column_option' data-field_id='{arf_field_id}'>
        			{$positioned_field_icons['matrix']}
        		</div>
        		<div class='controls arf_matrix_field_control_wrapper'>
        			<table cellpadding='0' cellspacing='0'>
        				<thead>
        					<tr>
        						<th></th>
        						<th align='center'>".esc_html__( 'Bad', 'ARForms')."</th>
        						<th align='center'>".esc_html__( 'Average', 'ARForms')."</th>
        						<th align='center'>".esc_html__( 'Excellent', 'ARForms')."</th>
        					</tr>
        				</thead>
        				<tbody>
        					<tr>
        						<td>".esc_html__('How is the food quality?', 'ARForms')."</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-0_0' name='item_meta[{arf_field_id}][0]' value='Bad' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_0' name='item_meta[{arf_field_id}][0]' value='Average' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-2_0' name='item_meta[{arf_field_id}][0]' value='Excellent' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        					</tr>
        					<tr>
        						<td>". esc_html__('How is the staff behaviour?', 'ARForms')."</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_0' name='item_meta[{arf_field_id}][1]' value='Bad' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_1' name='item_meta[{arf_field_id}][1]' value='Average' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-1_2' name='item_meta[{arf_field_id}][1]' value='Excellent' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        					</tr>
        					<tr>
        						<td>". esc_html__('How is the hotel environment?', 'ARForms'). "</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-2_0' name='item_meta[{arf_field_id}][2]' value='Bad' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-2_1' name='item_meta[{arf_field_id}][2]' value='Average' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        						<td align='center'>
        							<div class='arf_matrix_radio_control setting_radio arf_material_radio arf_default_material'>
    									<div class='arf_radio_input_wrapper arf_matrix_radio_input_wrapper'>
    										<input type='radio' id='field_{arf_field_id}-3_0' name='item_meta[{arf_field_id}][2]' value='Excellent' />
    										<span></span>
    									</div>
        							</div>
        						</td>
        					</tr>
        				</tbody>
        			</table>
    				<div class='arf_field_description' id='field_description_{arf_field_id}'></div>
    				<div class='help-block'></div>
        		</div>
        		<input type='hidden' class='arf_field_data_hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_matrix))."' data-field_options='".json_encode($field_order_matrix)."' />
        		<div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'>
        			<div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div>
        			<div class='arf_field_option_model_container'>
        				<div class='arf_field_option_content_row'></div>
        			</div>
        			<div class='arf_field_option_model_footer'>
        				<button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button>
        				<button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
        			</div>
        		</div>
        		<div class='arf_field_values_model' id='arf_field_values_model_skeleton_{arf_field_id}'>
		            <div class='arf_field_values_model_header'>".esc_html__('Edit Columns','ARForms')."</div>
	                <div class='arf_field_values_model_container'>
	                    <div class='arf_field_values_content_row'>
	                        <div class='arf_field_values_content_loader'>
	                            <svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
	                        </div>
	                    </div>
	                </div>
	                <div class='arf_field_values_model_footer'>
	                    <button type='button' class='arf_field_values_close_button'>".esc_html__('Cancel','ARForms')."</button>
	                    <button type='button' class='arf_field_values_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
	                </div>
	            </div>
		        <div class='arf_field_row_values_model' id='arf_field_rows_model_skeleton_{arf_field_id}'>
		        	<div class='arf_field_values_model_header'>".esc_html__('Edit Rows','ARForms')."</div>
	                <div class='arf_field_values_model_container'>
	                    <div class='arf_field_values_content_row'>
	                        <div class='arf_field_values_content_loader'>
	                            <svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
	                        </div>
	                    </div>
	                </div>
	                <div class='arf_field_values_model_footer'>
	                    <button type='button' class='arf_field_rows_close_button'>".esc_html__('Cancel','ARForms')."</button>
	                    <button type='button' class='arf_field_rows_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
	                </div>
	            </div>
        	</div>
        </div>";

		return $material_outline_fields;
	}

	function arf_matrix_field_basic_options( $args ){

		$args['matrix'] = array(
			'labelname' => 1,
            'fielddescription' => 2,
            'tooltipmsg' => 3,
            'requiredmsg' => 4,
		);

		return $args;
	}

	function arf_field_label_for_options( $field_label_arr ){

		$field_label_arr['matrix'] = esc_html__( 'Matrix', 'ARForms');

		return $field_label_arr;
	}

	function arf_field_option_icon_for_matrix( $svg_icon, $type, $field_required_cls, $field_id, $field_required, $field_type, $form_id, $title ){

		if( 'matrix' == $field_type ){

			if( 'edit_row_options' == $type ){
				if( !empty( $title ) ){
					$title_arr = $title;
				} else {
					$title_arr = addslashes(esc_html__('Manage Rows','ARForms'));
				}
				$svg_icon = "<div class='arf_field_option_icon'><a title='".$title_arr."' data-title='".$title_arr."' class='arf_field_option_input arf_field_icon_tooltip arf_edit_row_option_button' data-field-id='{$field_id}' id='arf_edit_row_option_button'><svg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 28 28' style='enable-background:new 0 0 28 28;transform: rotate(90deg);' xml:space='preserve' fill='#fff'><rect id='XMLID_4_' x='8' y='6' class='st0' width='2' height='16'></rect><rect id='XMLID_5_' x='13' y='6' class='st0' width='2' height='16'></rect><rect id='XMLID_9_' x='18' y='6' class='st0' width='2' height='16'></rect></svg></a></div>";
			} else if( 'edit_column_icons' == $type ){
				if( !empty( $title ) ){
					$title_arr = $title;
				} else {
					$title_arr = addslashes(esc_html__('Manage Columns','ARForms'));
				}
				$svg_icon = "<div class='arf_field_option_icon'><a title='".$title_arr."' data-title='".$title_arr."' class='arf_field_option_input arf_field_icon_tooltip arf_edit_row_option_button' data-field-id='{$field_id}' id='arf_edit_value_option_button'><svg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 28 28' style='enable-background:new 0 0 28 28;' xml:space='preserve' fill='#fff'><rect id='XMLID_4_' x='7' y='6' class='st0' width='2' height='16'></rect><rect id='XMLID_5_' x='12' y='6' class='st0' width='2' height='16'></rect><rect id='XMLID_9_' x='17' y='6' class='st0' width='2' height='16'></rect></svg></a></div>";
			}
		}


		return $svg_icon;
	}

	function arf_add_field_row_icons( $field_opts ){

		return $field_opts;
	}

	function arf_hide_multicolumn_box_for_matrix( $flag, $field ){

		if( 'matrix' == $field['type'] ){
			$flag = false;
		}

		return $flag;
	}

	function arf_add_class_for_matrix_for_editor( $selector, $field_type ){

		if( 'matrix' == $field_type ){
			$selector .= ' arf_matrix_field_control_wrapper ';
		}

		return $selector;

	}

	function arf_display_matrix_in_editor( $field, $inputstyle, $newarr ){

		global $arfform;

		$arf_matrix_field_html = '';

		$total_columns = 1;

		if( 'matrix' != $field['type'] ){
			return;
		}

		if( !empty( $field['options'] ) ){
			$total_columns += count( $field['options'] );
		}

		if( $total_columns > 6 ){
			$total_columns = 6;
		}

		$total_rows = 0;

		if( !empty( $field['rows'] ) ){
			$total_rows = count( $field['rows'] );
		}

		$matrix_cell_class = 'arf_hide_matrix_cell';
		for( $r = 0; $r < $total_rows; $r++ ){
			if( isset( $field['rows'][$r] ) && !empty( trim( $field['rows'][$r] ) ) ){
				$matrix_cell_class = '';
				break;
			}
		}

		$use_custom_radio = false;
	    $arf_control_append_class = '';
		if( $inputstyle == 'material' || $inputstyle == 'material_outlined' ){
    		$arf_control_append_class .= ' arf_material_radio ';
            if ($newarr['arfcheckradiostyle'] == 'material') {
                if ($newarr['arfcheckradiostyle'] != 'custom') {
                    $arf_control_append_class .= ' arf_default_material ';
                } else {
					$use_custom_radio = true;
                    $arf_control_append_class .= ' arf_custom_radio ';
                }
            } else {
                if ($newarr['arfcheckradiostyle'] != 'custom') {
                    $arf_control_append_class .= ' arf_advanced_material ';
                } else {
					$use_custom_radio = true;
                    $arf_control_append_class .= ' arf_custom_radio ';
                }
            }
    	} else {
    		if ($newarr['arfinputstyle'] == 'rounded') {
                if ($newarr['arfcheckradiostyle'] != 'custom') {
                    $arf_control_append_class .= ' arf_rounded_flat_radio ';
                } else {
					$use_custom_radio = true;
                    $arf_control_append_class .= ' arf_custom_radio ';
                }
            } else if ($newarr['arfinputstyle'] == 'standard') {
                if ($newarr['arfcheckradiostyle'] != 'custom') {
                    $arf_control_append_class .= ' arf_standard_radio ';
                } else {
					$use_custom_radio = true;
                    $arf_control_append_class .= ' arf_custom_radio ';
                }
            }
    	}

		$arf_matrix_field_html = "<table cellpadding='0' cellspacing='0'>";
			$arf_matrix_field_html .= "<thead>";
    			$arf_matrix_field_html .= "<tr>";
    				for( $i = -1; $i < ( $total_columns - 1 ); $i++ ){
    					if( $i == -1 ){
							$arf_matrix_field_html .= "<th class='".$matrix_cell_class."'></th>";
    					} else {
    						if( !empty( $field['options'][$i] ) ){
    							if( isset( $field['options'][$i]['label'] ) ){
    								$arf_matrix_field_html .= "<th align='center'>" . $field['options'][$i]['label'] . "</th>";
    							} else {
    								$arf_matrix_field_html .= "<th align='center'>" . $field['options'][$i] . "</th>";        								
    							}
    						} else {
    							$arf_matrix_field_html .= "<th align='center'>".$field['options'][$c]."</th>";
    						}
    					}
    				}
    			$arf_matrix_field_html .= "</tr>";
    		$arf_matrix_field_html .= "</thead>";
    		
    		$arf_matrix_field_html .= "<tbody>";
    			for( $r = 0; $r < $total_rows; $r++ ){
    				$arf_matrix_field_html .= "<tr>";
    					for( $c = -1; $c < ( $total_columns - 1); $c++ ){
    						if( $c == -1 ){
    							$arf_matrix_field_html .= '<td class="'.$matrix_cell_class.'">' . $field['rows'][$r] . '</td>';
    						} else {
    							$arf_matrix_field_html .= '<td align="center">';
    								$arf_matrix_field_html .= '<div class="arf_matrix_radio_control setting_radio '.$arf_control_append_class.'">';
    									$arf_matrix_field_html .= '<div class="arf_radio_input_wrapper arf_matrix_radio_input_wrapper">';

    										if( isset( $field['options'][$c]['value'] ) ){
    											$opt_val = $field['options'][$c]['value'];
    										} else {
    											$opt_val = $field['options'][$c];
    										}
    										$arf_matrix_field_html .= '<input type="radio" id="field_'.$field['id'].'-'.$r.'_'.$c.'" name="item_meta['.$field['id'].']['.$r.']" value="'.$opt_val.'" />';
    										$arf_matrix_field_html .= '<span>';
											if ($use_custom_radio == true) {
												$custom_radio = $newarr['arf_checked_radio_icon'];
												$arf_matrix_field_html .= "<i class='{$custom_radio}'></i>";
											}
											$arf_matrix_field_html .= '</span>';
    									$arf_matrix_field_html .= '</div>';
    								$arf_matrix_field_html .= '</div>';
    							$arf_matrix_field_html .= '</td>';
    						}
    					}
    				$arf_matrix_field_html .= "</tr>";
    			}
    		$arf_matrix_field_html .= "</tbody>";
    	$arf_matrix_field_html .= "</table>";

		echo $arf_matrix_field_html;

	}

	function arf_add_icons_for_matrix( $field ){

		if( 'matrix' == $field['type'] ){
			global $arfieldcontroller;
			echo $arfieldcontroller->arf_get_field_control_icons( 'edit_row_options', '', $field['id'], '', 'matrix', $field['form_id'], addslashes( esc_html__('Manage Rows', 'ARForms' ) ) );
			echo $arfieldcontroller->arf_get_field_control_icons('edit_column_icons', '', $field['id'], '', 'matrix', $field['form_id'], addslashes( esc_html__('Manage Columns', 'ARForms') ) );

		}

	}

	function arf_add_fieldiconbox_class_for_matrix( $field ){
		if( 'matrix' == $field['type'] ){
			echo ' arf_fieldiconbox_with_edit_row_column_option ';
		}
	}

	function arf_add_field_model_for_matrix( $field ){

		if( 'matrix' == $field['type'] ){
			echo "
				<div class='arf_field_values_model' id='arf_field_values_model_skeleton_".$field['id']."'>
		            <div class='arf_field_values_model_header'>".esc_html__('Edit Columns','ARForms')."</div>
	                <div class='arf_field_values_model_container'>
	                    <div class='arf_field_values_content_row'>
	                        <div class='arf_field_values_content_loader'>
	                            <svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
	                        </div>
	                    </div>
	                </div>
	                <div class='arf_field_values_model_footer'>
	                    <button type='button' class='arf_field_values_close_button'>".esc_html__('Cancel','ARForms')."</button>
	                    <button type='button' class='arf_field_values_submit_button' data-field-id='".$field['id']."'>".esc_html__('OK','ARForms')."</button>
	                </div>
	            </div>
		        <div class='arf_field_row_values_model' id='arf_field_rows_model_skeleton_".$field['id']."'>
		        	<div class='arf_field_values_model_header'>".esc_html__('Edit Rows','ARForms')."</div>
	                <div class='arf_field_values_model_container'>
	                    <div class='arf_field_values_content_row'>
	                        <div class='arf_field_values_content_loader'>
	                            <svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
	                        </div>
	                    </div>
	                </div>
	                <div class='arf_field_values_model_footer'>
	                    <button type='button' class='arf_field_rows_close_button'>".esc_html__('Cancel','ARForms')."</button>
	                    <button type='button' class='arf_field_rows_submit_button' data-field-id='".$field['id']."'>".esc_html__('OK','ARForms')."</button>
	                </div>
	            </div>";
		}

	}

	function arf_load_matrix_field_rows_model_skeleton(){

		$arf_matrix_field_model  = '<div class="arf_field_row_values_model" id="arf_field_rows_model_skeleton">';

			$arf_matrix_field_model .= '<div class="arf_field_values_model_header">'.addslashes(esc_html__('Edit Rows', 'ARForms')).'</div>';

			$arf_matrix_field_model .= '<div class="arf_field_values_model_container">';

				$arf_matrix_field_model .= '<div class="arf_field_values_content_row">';

					$arf_matrix_field_model .= '<div class="arf_field_rows_content_cell arf_full_width_cell" id="rows">';

						$arf_matrix_field_model .= '<div class="arf_field_rows_content_cell_input">';

							$arf_matrix_field_model .= '<div class="arf_field_row_grid_wrapper">';

								$arf_matrix_field_model .= '<div class="arf_field_row_grid_container arf_full_width">';

									$arf_matrix_field_model .= '<div class="arf_field_row_grid_header">';

										$arf_matrix_field_model .= '<div class="arf_field_row_grid_header_cell_label">'.esc_html__('Matrix Question', 'ARForms').'</div>';

										$arf_matrix_field_model .= '<div class="arf_field_row_grid_header_cell_action"></div>';

									$arf_matrix_field_model .= '</div>';

									$arf_matrix_field_model .= '<div class="arf_field_row_grid_data_wrapper" id="arf_field_row_grid_data_wrapper_{arf_field_id}"></div>';

								$arf_matrix_field_model .= '</div>';

							$arf_matrix_field_model .= '</div>';

						$arf_matrix_field_model .= '</div>';

					$arf_matrix_field_model .= '</div>';

				$arf_matrix_field_model .= '</div>';

			$arf_matrix_field_model .= '</div>';

			$arf_matrix_field_model .= '<div class="arf_field_values_model_footer">';

				$arf_matrix_field_model .= '<button type="button" class="arf_field_rows_close_button">'.addslashes(esc_html__('Cancel', 'ARForms')).'</button>';

				$arf_matrix_field_model .= '<button type="button" class="arf_field_rows_submit_button" data-field_id="">'.esc_html__('OK', 'ARForms').'</button>';

			$arf_matrix_field_model .= '</div>';

		$arf_matrix_field_model .= '</div>';

		echo $arf_matrix_field_model;
	}

	function arf_render_matrix_field( $return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tooltip, $field_description,$OFData,$inputStyle,$arf_main_label,$arf_on_change_function ){

		if( 'matrix' == $field['type'] ){
			global $style_settings, $arfsettings, $arfeditingentry, $arffield, $arfieldhelper, $wpdb, $MdlDb;

			$arf_prevent_auto_save = false;
		    $arf_prevent_auto_save = apply_filters('arf_prevent_auto_save_form', false, $form);

		    $arf_matrix_cookie_data = array();

			$field_tooltip = '';
            $field_tooltip_class = '';
            $field_standard_tooltip = '';
            if (isset($field['tooltip_text']) and $field['tooltip_text'] != "") {
                if($inputStyle=='material'){
                    $field_tooltip = $arfieldhelper->arf_tooltip_display($field['tooltip_text'],$inputStyle);
                    $field_tooltip_class = ' arfhelptip ';
                } else {
                    $field_standard_tooltip = $arfieldhelper->arf_tooltip_display($field['tooltip_text'],$inputStyle);
                }
            }

			$arf_control_append_class = '';
			$use_custom_radio = false;
			if( $inputStyle == 'material' || $inputStyle == 'material_outlined' ){
	    		$arf_control_append_class .= ' arf_material_radio ';
	            if ($form->form_css['arfcheckradiostyle'] == 'material') {
	                if ($form->form_css['arfcheckradiostyle'] != 'custom') {
	                    $arf_control_append_class .= ' arf_default_material ';
	                } else {
	                	$use_custom_radio = true;
	                    $arf_control_append_class .= ' arf_custom_radio ';
	                }
	            } else {
	                if ($form->form_css['arfcheckradiostyle'] != 'custom') {
	                    $arf_control_append_class .= ' arf_advanced_material ';
	                } else {
	                	$use_custom_radio = true;
	                    $arf_control_append_class .= ' arf_custom_radio ';
	                }
	            }
	    	} else {
	    		if ($form->form_css['arfinputstyle'] == 'rounded') {
	                if ($form->form_css['arfcheckradiostyle'] != 'custom') {
	                    $arf_control_append_class .= ' arf_rounded_flat_radio ';
	                } else {
	                	$use_custom_radio = true;
	                    $arf_control_append_class .= ' arf_custom_radio ';
	                }
	            } else if ($form->form_css['arfinputstyle'] == 'standard') {
	                if ($form->form_css['arfcheckradiostyle'] != 'custom') {
	                    $arf_control_append_class .= ' arf_standard_radio ';
	                } else {
	                	$use_custom_radio = true;
	                    $arf_control_append_class .= ' arf_custom_radio ';
	                }
	            }
	    	}

	    	if( 'standard' != $form->form_css['arfinputstyle'] && 'rounded' != $form->form_css['arfinputstyle'] ){
				$return_string .= $arf_main_label;
	    	}

			$total_columns = 1;

			if( !empty( $field['options'] ) ){
				$total_columns += count( $field['options'] );
			}

			$total_rows = 0;

			if( !empty( $field['rows'] ) ){
				$total_rows = count( $field['rows'] );
			}

			$matrix_cell_class = 'arf_hide_matrix_cell';
			for( $r = 0; $r < $total_rows; $r++ ){
				if( isset( $field['rows'][$r] ) && !empty( trim( $field['rows'][$r] ) ) ){
					$matrix_cell_class = '';
					break;
				}
			}

			$arf_save_form_data = '';

	        if( isset( $form->options['arf_form_save_database'] ) && 1 == $form->options['arf_form_save_database']  ){
	            $arf_save_form_data = ' data-save="true" ';            
	        }

			$arf_matrix_req_attr = '';
			$arf_matrix_req_cls = '';
			if( !empty( $field['required'] ) && 1 == $field['required'] ){
				$arf_matrix_req_attr = ' data-validation-minchecked-minchecked="1" data-validation-minchecked-message="'.$field['blank'].'" ';
				$arf_matrix_req_cls = ' arf_required ';
			}

			$return_string .= '<div class="controls input-field">';

				$return_string .= '<div class="arf_matrix_field_control_wrapper '.$field_tooltip_class.'" '.$field_tooltip.'>';

					$return_string .= '<table cellpadding="0" cellspacing="0">';

						$return_string .= '<thead>';

							$return_string .= "<tr>";
			    				for( $i = -1; $i < ( $total_columns - 1 ); $i++ ){
			    					if( $i == -1  ){
			    						$return_string .= "<th class='".$matrix_cell_class."'></th>";
			    					} else {
			    						if( !empty( $field['options'][$i] ) ){
			    							if( isset( $field['options'][$i]['label'] ) ){
			    								$return_string .= "<th align='center'>" . $field['options'][$i]['label'] . "</th>";
			    							} else {
			    								$return_string .= "<th align='center'>" . $field['options'][$i] . "</th>";        								
			    							}
			    						} else {
			    							$return_string .= "<th align='center'>".$field['options'][$i]."</th>";
			    						}
			    					}
			    				}
			    			$return_string .= "</tr>";

						$return_string .= '</thead>';

						$return_string .= "<tbody>";
			    			for( $r = 0; $r < $total_rows; $r++ ){
			    				$return_string .= "<tr>";
			    					for( $c = -1; $c < ( $total_columns - 1); $c++ ){
			    						if( $c == -1 ){
		    								$return_string .= '<td class="'.$matrix_cell_class.'">' . $field['rows'][$r] . '</td>';
			    						} else {
			    							$return_string .= '<td align="center">';
			    								$return_string .= '<div class="arf_matrix_radio_control setting_radio '.$arf_control_append_class.'">';
			    									$return_string .= '<div class="arf_radio_input_wrapper arf_matrix_radio_input_wrapper">';

			    										if( isset( $field['options'][$c]['value'] ) ){
			    											$opt_val = $field['options'][$c]['value'];
			    										} else {
			    											$opt_val = $field['options'][$c];
			    										}
			    										$matrix_input_key = 'item_meta[' . $field['id'] .']['.$r.']';
			    										$matrix_checked = "";
			    										if( !empty( $arf_matrix_cookie_data[ $matrix_input_key ] ) ){
			    											$matrix_checked = checked( $arf_matrix_cookie_data[ $matrix_input_key ], $opt_val, false );
			    										}
			    										$return_string .= '<input type="radio" '.$arf_matrix_req_attr.' class="'.$arf_matrix_req_cls.' arf_matrix_field_option" id="field_'.$field['id'].'-'.$r.'_'.$c.'" name="'.$matrix_input_key.'" '.$matrix_checked.' '.$arf_on_change_function.' '.$arf_save_form_data.' value="'.$opt_val.'" />';
			    										$return_string .= '<span>';
			    											if ($use_custom_radio == true) {
				                                                $custom_radio = $form->form_css['arf_checked_radio_icon'];
				                                                $return_string .= "<i class='{$custom_radio}'></i>";
				                                            }
			    										$return_string .= '</span>';
			    									$return_string .= '</div>';
			    								$return_string .= '</div>';
			    							$return_string .= '</td>';
			    						}
			    					}
			    				$return_string .= "</tr>";
			    			}
			    		$return_string .= "</tbody>";

					$return_string .= '</table>';

					$return_string .= '<div class="arf_matrix_field_control_wrapper_col">';

						for( $r = 0; $r < $total_rows; $r++ ){
							$return_string .= '<div class="arf_matrix_field_row">';
								$return_string .= '<div class="arf_matrix_field_row_label '.$matrix_cell_class.'">'.$field['rows'][$r].'</div>';
								$return_string .= '<div class="arf_matrix_field_body_wrapper">';
									for( $c = 0; $c < ( $total_columns - 1 ); $c++ ){
										$return_string .= '<div class="arf_matrix_field_body_control">';
											$return_string .= '<label for="field_'.$field['id'].'-'.$r.'_'.$c.'-inner" class="arf_matrix_field_radio_control_label">';
												if( !empty( $field['options'][$c] ) ){
					    							if( isset( $field['options'][$c]['label'] ) ){
					    								$return_string .= $field['options'][$c]['label'];
					    							} else {
					    								$return_string .= $field['options'][$c];
					    							}
					    						} else {
					    							$return_string .= $field['options'][$c];
					    						}
											$return_string .= '</label>';
											$return_string .= '<div class="arf_matrix_radio_control setting_radio '.$arf_control_append_class.'">';
		    									$return_string .= '<div class="arf_radio_input_wrapper arf_matrix_radio_input_wrapper">';

		    										if( isset( $field['options'][$c]['value'] ) ){
		    											$opt_val = $field['options'][$c]['value'];
		    										} else {
		    											$opt_val = $field['options'][$c];
		    										}
		    										$arf_matrix_inner_name = 'temp_item_meta[' . $field['id'] .']['.$r.']';
		    										$matrix_checked = "";
		    										
		    										$return_string .= '<input type="radio" '.$arf_matrix_req_attr.' class="'.$arf_matrix_req_cls.' arf_matrix_field_option" id="field_'.$field['id'].'-'.$r.'_'.$c.'-inner" name="'.$arf_matrix_inner_name.'-inner" '.$matrix_checked.' '.$arf_on_change_function.' '.$arf_save_form_data.' value="'.$opt_val.'" />';
		    										$return_string .= '<span>';
		    											if ($use_custom_radio == true) {
			                                                $custom_radio = $form->form_css['arf_checked_radio_icon'];
			                                                $return_string .= "<i class='{$custom_radio}'></i>";
			                                            }
		    										$return_string .= '</span>';
		    									$return_string .= '</div>';
		    								$return_string .= '</div>';
										$return_string .= '</div>';
									}
								$return_string .= '</div>';
							$return_string .= '</div>';
						}

					$return_string .= '</div>';

				$return_string .= '</div>';

				$return_string .= $field_standard_tooltip;

                $return_string .= $field_description;
				
			$return_string .= '</div>';

		}

		return $return_string;
	}

	function arf_set_matrix_label_position( $position_arr, $inputStyle, $field_type ){

		if( 'matrix' == $field_type ){
			if( 'material' == $inputStyle || 'material_outlined' == $inputStyle ){
				array_push( $position_arr, 'matrix' );
			}
		}

		return $position_arr;
	}

	function arf_render_matrix_field_value_in_entry_list( $value, $field, $atts ){

		if( 'matrix' == $field->type ){
			$is_incomplete = ( !empty( $_REQUEST['action'] ) && 'arf_retrieve_form_incomplete_entry' == $_REQUEST['action'] ) ? true : false;
			

			if( $is_incomplete ){

			} else {
				$value = '<a href="javascript:void(0);" onclick="" data-field-id="'.$field->id.'">' . __('View Matrix Data', 'ARForms') . '</a>';
			}
		}

		return $value;
	}

	function get_matrix_entries_list_edit( $item_id, $col_id, $is_incomplete, $atts_param, $form_css ){
		global $db_record, $arffield, $arfrecordmeta, $user_ID, $armainhelper, $arrecordhelper;

        $field = $arffield->getOne( $col_id );

        if( $field->type != 'matrix' ){
        	return;
        }

        if( $is_incomplete ){
            $entry = $db_record->getOneIncomplete( $item_id, true );
        } else {
            $entry = $db_record->getOne( $item_id, true );
        }

        $table_html = '<table class="form-table">';

        	$table_html .= '<tbody>';

        		$source_data = array();
                if( $field->field_options['separate_value'] != 1 ){
                    foreach( $field->field_options['options'] as $mat_opts ){
                        $source_data[ $mat_opts ] = $mat_opts;
                    }
                } else {
                    foreach( $field->field_options['options'] as $mat_opts){
                        $source_data[ $mat_opts['value'] ] = $mat_opts['label'];
                    }
                }

        		if( !empty( $field->field_options['rows'] ) ){
        			$total_rows = count( $field->field_options['rows'] );

        			if( !isset($entry->metas[ $field->id ]) ){
        				$entry->metas[ $field->id ] = array();
        			}
        			$saved_data = $entry->metas[ $field->id ];

                    for( $x = 0; $x < $total_rows; $x++ ){
        				$table_html .= '<tr class="arfviewentry_row">';
        					$table_html .= '<td class="arfviewentry_left arfwidth25">' . $field->field_options['rows'][$x] .'</td>';
        					$table_html .= '<td class="arfviewentry_right">';
        						$var = '';
	        					if( current_user_can( 'arfeditentries' ) && !$is_incomplete ){
                                    $var .= '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="matrix" data-id="'.$field->id.'" data-entry-id="' . $entry->id . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span class="arf_editable_values_container arf_edit_type_matrix_option_'.$field->id.'_'.$x.'" id="arf_value_' . $entry->id . '_' . $field->id . '" data-matrix-inner-id="'.$x.'" data-id="'.$field->id.'" data-field-type="'.$field->type.'" data-entry-id="'.$entry->id.'" data-source=\''.json_encode($source_data).'\' data-type="select" data-pk="1" data-separate-value="'.$field->field_options['separate_value'].'" >';

                                    if( 1 == $field->field_options['separate_value'] && !empty( $saved_data[ $x ] ) ){
                                        $temp_val = '';
                                        foreach( $field->field_options['options'] as $mat_opts){
                                            if( $saved_data[$x] == $mat_opts['value'] ){
                                                $temp_val = $mat_opts['label'] . ' (' . $mat_opts['value'] . ')';
                                            }
                                        }
                                        $var .= $temp_val;
                                    } else {
                                        $var .= ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '-' );
                                    }

                                    $var .= '</span>';
                                    $var .= '<input type="hidden" name="arf_edit_matrix_field_values['.$entry->id.']['.$x.']" id="arf_edit_new_matrix_values_'.$field->id.'_'.$entry->id.'_'.$x.'" data-id="'.$field->id.'" data-entry-id="'.$entry->id.'" />';
                                    
                                    $as_edit_matrix_entry_value[$field->id][$x] = ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '' );
                                } else {
                                    $var .= '<span class="arf_not_editable_values_container">';
                                    if( 1 == $field->field_options['separate_value'] && !empty( $saved_data[ $x ] ) ){
                                        $temp_val = '';
                                        foreach( $field->field_options['options'] as $mat_opts){
                                            if( $saved_data[$x] == $mat_opts['value'] ){
                                                $temp_val = $mat_opts['label'] . ' (' . $mat_opts['value'] . ')';
                                            }
                                        }
                                        $var .= $temp_val;
                                    } else {
                                        $var .= ( !empty( $saved_data[ $x ] ) ? $saved_data[ $x ] : '-' );
                                    }
                                    $var .= '</span>';
                                }
                                $table_html .= $var;
        					$table_html .= '</td>';
        				$table_html .= '</tr>';
        			}
        		}

        	$table_html .= '</tbody>';

        $table_html .= '</table>';

        return $table_html;

	}

	function arf_render_matrix_field_edit_array( $edit_array, $item_id, $col_id ){

		global $arffield;

		$field_obj = $arffield->getOne( $col_id );

		if( 'matrix' == $field_obj->type ){
			global $db_record;
			$entry = $db_record->getOne( $item_id, true );
			
			if( empty( $field_obj->field_options->separate_value ) || '1' != $field_obj->field_options->separate_value ){
				if ( isset( $entry->metas[ $col_id ] ) ) {
					$edit_array[] = array( $col_id => json_encode( $entry->metas[ $col_id ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) );
				}
			} else {
				$field_opts = arf_json_decode( $field_obj->options );
				$option_separate_value = array();

                foreach( $fopts as $options ){
                    $option_separate_value[] = array( 'value' => htmlentities( $options['value'] ), 'text' => $options['label'] );
                }

                $edit_array[] = array( $col_id => json_encode( $field_opts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) );
			}
		}

		return $edit_array;
	}

	function arf_matrix_value_for_email( $field_values, $value, $entry ){

		if( 'matrix' == $value->field_type ){
			global $arffield;

			$field_value = maybe_unserialize( $value->entry_value );

			$field_id = $value->field_id;

			$field_obj = $arffield->getOne( $field_id );

			$value_for_mail  = '<table cellpadding="5" cellspacing="0" border="1">';

				$value_for_mail .= '<tbody>';


					if( !empty( $field_obj->rows ) ){

						$total_rows = count( $field_obj->rows );

						$rx = 0;
						foreach( $field_obj->rows as $frows ){

							$value_for_mail .= '<tr>';

								$value_for_mail .= '<td>';
								$value_for_mail .= $frows;
								$value_for_mail .= '</td>';
								$value_for_mail .= '<td>';
								if( isset( $field_obj->field_options['separate_value'] ) && 1 == $field_obj->field_options['separate_value'] ){
									foreach( $field_obj->field_options['options'] as $matrix_opts ){
										if( isset( $field_value[$rx] ) && $matrix_opts['value'] == $field_value[$rx] ){
											$value_for_mail .= $matrix_opts['label'] . ' ('.$field_value[$rx].')';
										}
									}
								} else {
									$value_for_mail .= ( !empty( $field_value[$rx] ) ? $field_value[$rx] : '-' );
								}

								$value_for_mail .= '</td>';

							$value_for_mail .= '</tr>';

							$rx++;
						}
					}

				$value_for_mail .= '</tbody>';

			$value_for_mail .= '</table>';

			$value->entry_value = $value_for_mail;
			$field_values = $value_for_mail;
		}

		return $field_values;
	}

	function arf_set_onchange_event_for_matrix( $fields_arr ){

		array_push( $fields_arr, 'matrix' );

		return $fields_arr;
	}

}