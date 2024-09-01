<?php

define( 'ARF_REPEATER', 'arf_repeater');


global $arf_repeater_class_name, $arf_repeater_new_field_data, $arf_repeater_field_class;

$arf_repeater_class_name = array(ARF_REPEATER => 'red');

$arf_repeater_new_field_data = array(ARF_REPEATER => addslashes(esc_html__('Repeater', 'ARForms')));

$arf_repeater_total_class = array();

$arf_repeater_field_class = new arf_repeater_field();

class arf_repeater_field{

	function __construct() {

		add_filter( 'arfaavailablefields', array( $this, 'arf_add_repeater_field_element_list'), 11);

		add_filter( 'arf_new_field_array_filter_outside', array( $this, 'arf_add_repeater_field_outside' ), 11, 4 );

		add_filter( 'arf_new_field_array_materialize_filter_outside', array( $this, 'arf_add_repeater_field_outside' ), 11, 4 );

		add_filter( 'arf_new_field_array_materialize_outlined_filter_outside', array( $this, 'arf_add_repeater_field_outside', ), 11, 4 );

		add_filter( 'arf_disply_multicolumn_fieldolumn_field_outside', array( $this, 'arf_hide_multicolumn_field_outside'), 10, 2 );

		add_filter( 'arf_display_field_opt_icons', array( $this, 'arf_hide_opt_icon_wrapper'), 10, 2);

		add_filter( 'arf_display_field_name_box', array( $this, 'arf_hide_field_name_text'), 10, 2 );

		add_filter( 'arf_hide_description_block', array( $this, 'arf_hide_description_block_for_repeater' ), 10, 2 );

		add_filter( 'arf_display_field_in_editor_outside', array( $this, 'arf_display_field_repeater_in_form_editor' ), 10, 2 );

		add_action( 'arf_render_field_in_editor_outside', array( $this, 'arf_render_repeater_field_in_editor'), 10, 13 );

		add_filter( 'arf_wrap_input_field', array( $this, 'arf_wrap_repeater_field_from_outside'), 11, 2);

		add_filter( 'form_fields', array( $this, 'arf_render_repeater_field_in_form' ), 10, 11 );

		add_filter( 'arf_add_parent_data_to_field', array( $this, 'arf_add_parent_field_data_to_field'), 10, 3 );

		add_filter( 'arfavailablefieldsbasicoptions', array( $this, 'arf_repeater_field_options' ), 10 );

		add_action( 'arf_field_option_model_outside', array( $this, 'arf_repeater_field_popup_options' ), 10 );

		add_filter( 'arf_unset_removed_inner_fields', array( $this, 'arf_unset_removed_child_fields'), 10 );

		add_filter( 'arf_positioned_field_options_icon', array( $this, 'arf_change_field_option_icon_data'),10,2);
	}

	function arf_add_repeater_field_element_list( $fields ){

		$fields['arf_repeater'] = array(
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#4E5462" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><path d="M36.2,86.7c0.5,3,2.5,5.4,5.4,6.6c1.1,0.5,2.3,0.7,3.5,0.7c2.3,0,4.6-0.9,6.3-2.7c3.5-3.5,3.5-9.2,0-12.7   c-2.6-2.6-6.4-3.4-9.8-2c-2,0.8-3.5,2.2-4.4,3.9C25.4,75.5,17.7,63.9,17.7,51c0-12,6.6-23,17.3-28.6c1.5-0.8,2-2.6,1.3-4.1   c-0.8-1.5-2.6-2-4.1-1.3C19.6,23.7,11.7,36.7,11.7,51C11.7,66.8,21.5,81,36.2,86.7z"/><path d="M67.1,78.4c-0.6,0.4-1.3,0.8-1.9,1.1c-1.5,0.8-2,2.6-1.2,4.1c0.5,1,1.6,1.6,2.6,1.6c0.5,0,1-0.1,1.4-0.4   c0.8-0.4,1.5-0.8,2.3-1.3c11.3-7.1,18-19.2,18-32.5c0-15.1-8.7-28.6-22.4-34.8c-0.3-3.4-2.3-6.2-5.5-7.5c-3.4-1.4-7.2-0.7-9.8,2   c-3.5,3.5-3.5,9.2,0,12.7c1.7,1.7,4,2.7,6.3,2.7c1.2,0,2.4-0.2,3.5-0.7c1.7-0.7,3-1.8,4-3.2C75.3,27.6,82.3,38.7,82.3,51   C82.3,62.2,76.6,72.4,67.1,78.4z"/></g></svg>',
			'label' => addslashes( esc_html__('Repeater','ARForms') )
		);

		return $fields;
	}

	function arf_add_repeater_field_element( $id = '', $values = '' ){

		global $arf_repeater_class_name, $arf_repeater_new_field_data, $arf_repeater_total_class;

		if( is_rtl() ){
			$floating_style = 'float:right;';
		} else {
			$floating_style = 'float:left;';
		}

		?>
		<li class="arf_form_element_item frmbutton frm_tarf_repeater" id="arf_repeater" data-field-id="<?php echo $id; ?>" data-type="arf_repeater">
			<div class="arf_form_element_item_inner_container">
				<span class="arf_form_element_item_icon">
					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#4E5462" version="1.1" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve">
						<g>
							<path d="M36.2,86.7c0.5,3,2.5,5.4,5.4,6.6c1.1,0.5,2.3,0.7,3.5,0.7c2.3,0,4.6-0.9,6.3-2.7c3.5-3.5,3.5-9.2,0-12.7   c-2.6-2.6-6.4-3.4-9.8-2c-2,0.8-3.5,2.2-4.4,3.9C25.4,75.5,17.7,63.9,17.7,51c0-12,6.6-23,17.3-28.6c1.5-0.8,2-2.6,1.3-4.1   c-0.8-1.5-2.6-2-4.1-1.3C19.6,23.7,11.7,36.7,11.7,51C11.7,66.8,21.5,81,36.2,86.7z"/>
							<path d="M67.1,78.4c-0.6,0.4-1.3,0.8-1.9,1.1c-1.5,0.8-2,2.6-1.2,4.1c0.5,1,1.6,1.6,2.6,1.6c0.5,0,1-0.1,1.4-0.4   c0.8-0.4,1.5-0.8,2.3-1.3c11.3-7.1,18-19.2,18-32.5c0-15.1-8.7-28.6-22.4-34.8c-0.3-3.4-2.3-6.2-5.5-7.5c-3.4-1.4-7.2-0.7-9.8,2   c-3.5,3.5-3.5,9.2,0,12.7c1.7,1.7,4,2.7,6.3,2.7c1.2,0,2.4-0.2,3.5-0.7c1.7-0.7,3-1.8,4-3.2C75.3,27.6,82.3,38.7,82.3,51   C82.3,62.2,76.6,72.4,67.1,78.4z"/>
						</g>
					</svg>
				</span>
				<label class="arf_form_element_item_text"><?php esc_html_e( 'Repeater', 'ARForms' ); ?></label>
			</div>
		</li>
		<?php
	}

	function arf_add_repeater_field_outside( $fields, $field_icons, $json_data, $positioned_field_icons ){

		global $arfieldhelper,$arfieldcontroller;

		$field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();

		$field_order_arf_repeat = isset( $field_opt_arr['arf_repeater'] ) ? $field_opt_arr['arf_repeater'] : '';

		$field_data_array = $json_data;

		$field_data_obj_arf_repeat = $field_data_array->field_data->arf_repeater;

		$arf_repeater_field  = '<div class="arfmainformfield edit_field_type_arf_repeater_wrapper top_container edit_form_item arffieldbox ui-state-default arf1columns" id="arf_repeater">';

			$arf_repeater_field .= '<div class="unsortable_inner_wrapper edit_field_type_repeater">';

				$arf_repeater_field .= '<div class="fieldname-row" style="display:block;">';

					$arf_repeater_field .= '<div class="fieldname">';

							$arf_repeater_field .= '<label class="arf_main_label" id="field_{arf_field_id}">';

								$arf_repeater_field .= '<span class="arfeditorfieldopt_repeater_label arf_edit_in_place arfeditorfieldopt_label">';

									$arf_repeater_field .= '<input type="text" class="arf_edit_in_place_input inplace_field" data-ajax="false" data-field-opt-change="true" data-field-opt-key="name" value="Repeater Section Title" data-field-id="{arf_field_id}" />';

								$arf_repeater_field .= '</span>';

							$arf_repeater_field .= '</label>';

					$arf_repeater_field .= '</div>';

				$arf_repeater_field .= '</div>';

			$arf_repeater_field .= '</div>';

			$arf_repeater_field .= '<div class="arf_fieldiconbox arf_repeater_fieldiconbox" data-field_id="{arf_field_id}">';

				$arf_repeater_field .= $positioned_field_icons['arf_repeater'];

			$arf_repeater_field .= '</div>';

			$arf_repeater_field .= '<div class="sortable_inner_wrapper edit_field_type_arf_repeater" id="arfmainfieldid_{arf_field_id}" inner_class="arf_1col" >';

				$arf_repeater_field .= "<input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='" . htmlspecialchars(json_encode($field_data_obj_arf_repeat)) . "' data-field_options='" . json_encode($field_order_arf_repeat) . "' />";

				$arf_repeater_field .= '<div class="arf_field_option_model arf_field_option_model_cloned" data-field_id="{arf_field_id}">';
                    
                    $arf_repeater_field .= '<div class="arf_field_option_model_header">'.addslashes(esc_html__('Field Options', 'ARForms')).'&nbsp;<span class="arf_pre_populated_field_type" id="{arf_field_type}">[Field Type : [arf_field_type]]</span>&nbsp;<span class="arf_pre_populated_field_id" id="{arf_field_id}">[Field ID:[arf_field_id]]</span></div>';
                    	$arf_repeater_field .= '<div class="arf_field_option_model_container">';
                        	$arf_repeater_field .= '<div class="arf_field_option_content_row">';
                        	$arf_repeater_field .= '</div>';
                    	$arf_repeater_field .= '</div>';
                    	$arf_repeater_field .= '<div class="arf_field_option_model_footer">';
                        	$arf_repeater_field .= '<button type="button" class="arf_field_option_close_button" onClick="arf_close_field_option_popup({arf_field_id});">'.addslashes(esc_html__('Cancel', 'ARForms')).'</button>';
                        	$arf_repeater_field .= '<button type="button" class="arf_field_option_submit_button" data-field_id="{arf_field_id}">'.esc_html__('OK', 'ARForms').'</button>';
                    	$arf_repeater_field .= '</div>';
                	$arf_repeater_field .= '</div>';
				
				$arf_repeater_field .= '</div>';

			$arf_repeater_field .= '</div>';


		$arf_repeater_field .= '</div>';


		$fields['arf_repeater'] = $arf_repeater_field;

		return $fields;
	}

	function arf_hide_multicolumn_field_outside( $display_multicolumn, $field ){

		if( isset( $field['type'] ) && 'arf_repeater' == $field['type'] ){
			$display_multicolumn = false;
		}

		return $display_multicolumn;
	}

	function arf_hide_opt_icon_wrapper( $display_opt_icons, $field ){

		if( isset( $field['type'] )  && 'arf_repeater' == $field['type'] ){
			$display_opt_icons = false;
		}

		return $display_opt_icons;
	}

	function arf_hide_field_name_text( $display_name_box, $field ){

		if( isset( $field['type'] ) && 'arf_repeater' == $field['type'] ){
			$display_name_box = false;
		}

		return $display_name_box;
	}

	function arf_hide_description_block_for_repeater( $hide_desc_block, $field ){

		if( isset( $field['type'] ) && 'arf_repeater' == $field['type'] ){
			$hide_desc_block  = true;
		}

		return $hide_desc_block;

	}

	function arf_display_field_repeater_in_form_editor( $display, $field ){

		if( isset( $field['type'] ) && 'arf_repeater' == $field['type'] ){
			$display = true;
		}

		return $display;
	}

	function arf_render_repeater_field_in_editor( $check_field, $field_data_obj, $field_order, $inner_field_order,$index_arf_fields, $frm_css, $data, $id,$inner_field_resize_width, $unsaved_inner_fields, $return_html, $newarr, $remove_placeholders ){

		global $arformcontroller,$MdlDb,$wpdb,$bootstraped_fields_array,$fields_with_external_js, $arfieldcontroller,$arfieldhelper,$index_repeater_fields;

		if( isset( $check_field['type'] ) && 'arf_repeater' == $check_field['type'] ) {

			$arf_repeater_field_html = '';

	        $field_type = $check_field['type'];

	        if( is_array( $inner_field_order ) ){
	        	$inner_field_order = json_encode( $inner_field_order );
	        }
	        $arf_main_label_cls = '';
	        if ($frm_css['arfinputstyle'] == 'material') {
			    $arf_main_label_cls = $arformcontroller->arf_label_top_position($frm_css['font_size'], $frm_css['field_font_size']);
			}

	        $parent_field_id = $check_field['id'];

	        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();

	        $field_opt_order = isset($field_opt_arr[$check_field['type']]) ? $field_opt_arr[$check_field['type']] : '';
	        
	        foreach ($field_data_obj->field_data->$field_type as $key => $val) {
	            $new_field_obj[$key] = (isset($check_field[$key]) && $check_field[$key] != '' ) ? $check_field[$key] : (isset($unserialize_field_optins[$key]) ? $unserialize_field_optins[$key] : '');
	            if ($key == 'options') {
	                $new_field_obj[$key] = $check_field[$key];
	            }
	            if (isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] != 'edit') {
	                if ($key == 'placeholdertext') {
	                    $new_field_obj[$key] = $placeholder_text;
	                }
	            }
	        }

	        $new_field_obj['default_value'] = isset($check_field['default_value']) ? $check_field['default_value'] : (isset($check_field['field_options']['default_value']) ? $check_field['field_options']['default_value'] : '');

	        if (isset($new_field_obj['page_no']) && ($new_field_obj['page_no'] == '' || $new_field_obj['page_no'] < 1)) {
	            $new_field_obj['page_no'] = 1;
	        }

	        if (isset($new_field_obj['locale'])) {
	            $new_field_obj['locale'] = $new_field_obj['locale'] != "" ? $new_field_obj['locale'] : 'en';
	        }
	        $new_field_obj['image_position_from'] = ( isset($new_field_obj['image_position_from']) && $new_field_obj['image_position_from'] != '' ) ? $new_field_obj['image_position_from'] : 'top_left';

	        $new_field_obj = $arformcontroller->arf_html_entity_decode($new_field_obj);

	        $repeater_field_obj = $new_field_obj;

	        $current_field_order = array();

			$arf_repeater_field_html .= '<div class="arfmainformfield edit_field_type_arf_repeater_wrapper top_container edit_form_item arffieldbox ui-state-default arf1columns" id="arf_repeater">';
				$inner_cls = isset($check_field['inner_class']) ? $check_field['inner_class'] : 'arf_1col';

				$arf_repeater_field_html .= '<div class="unsortable_inner_wrapper edit_field_type_repeater">';

					$arf_repeater_field_html .= '<div class="fieldname-row" style="display:block;">';

						$arf_repeater_field_html .= '<div class="fieldname">';

								$arf_repeater_field_html .= '<label class="arf_main_label '.$arf_main_label_cls.'" id="field_'.$check_field['id'].'">';

									$arf_repeater_field_html .= '<span class="arfeditorfieldopt_section_label arf_edit_in_place arfeditorfieldopt_label">';

										$arf_repeater_field_html .= '<input type="text" class="arf_edit_in_place_input inplace_field" data-ajax="false" data-field-opt-change="true" data-field-opt-key="name" value="'.htmlspecialchars($check_field['name']).'" data-field-id="'.$check_field['id'].'" />';

									$arf_repeater_field_html .= '</span>';

								$arf_repeater_field_html .= '</label>';

						$arf_repeater_field_html .= '</div>';

					$arf_repeater_field_html .= '</div>';

				$arf_repeater_field_html .= '</div>';

				$arf_repeater_field_html .= '<div class="arf_fieldiconbox arf_repeater_fieldiconbox" data-field_id="'.$check_field['id'].'">';

					$arf_repeater_field_html .= $arfieldcontroller->arf_get_field_control_icons('duplicate', '', $check_field['id'], 0, $check_field['type'], $id);
					$arf_repeater_field_html .= $arfieldcontroller->arf_get_field_control_icons('delete', '', $check_field['id'], 0, $check_field['type'], $id);
					$arf_repeater_field_html .= $arfieldcontroller->arf_get_field_control_icons('options', '', $check_field['id'], 0, $check_field['type'], $id);
					$arf_repeater_field_html .= $arfieldcontroller->arf_get_field_control_icons('move', '', $check_field['id'], 0, $check_field['type'], $id);

				$arf_repeater_field_html .= '</div>';

				$arf_repeater_field_html .= '<div class="sortable_inner_wrapper edit_field_type_arf_repeater" id="arfmainfieldid_'.$check_field['id'].'" inner_class="'.$inner_cls.'" >';

					$cache_obj = wp_cache_get( 'arf_parent_field_data_'. $check_field['id'] );

					if( isset( $_REQUEST['arfaction'] ) && $_REQUEST['arfaction'] == 'duplicate' ){
						if( false === $cache_obj )
						{
							$get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".$MdlDb->fields."` WHERE form_id = %d AND (field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%') ", $_REQUEST['id'], $check_field['id'], $check_field['id'] ), ARRAY_A );
							wp_cache_set( 'arf_parent_field_data_'. $check_field['id'], $get_all_inner_fields );
						} else {
							$get_all_inner_fields = $cache_obj;
						}
					} else {
						if( false === $cache_obj )
						{
							$get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".$MdlDb->fields."` WHERE form_id = %d AND (field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%') ", $id, $check_field['id'], $check_field['id'] ), ARRAY_A );
							wp_cache_set( 'arf_parent_field_data_'. $check_field['id'], $get_all_inner_fields );
						} else {
							$get_all_inner_fields = $cache_obj;
						}
					}
					
					$class_array = array();
					
					$arf_field_counter = 1;

					$arf_inner_sorted_fields = array();

					if( $inner_field_order != '' ){
						$inner_field_order = json_decode($inner_field_order, true);
						
						if( isset( $inner_field_order[$check_field['id']]  ) ){
							foreach( $inner_field_order[$check_field['id']] as $k => $inner_order ){
								$exploded_data = explode('|', $inner_order);
								$temp_fid = $exploded_data[0];
								
								if( preg_match('/^(\d+)$/',$temp_fid) ){
									foreach( $get_all_inner_fields as $field ){
										if( $field['id'] == $temp_fid && !array_key_exists( (int)$temp_fid, $unsaved_inner_fields ) ){
											$arf_inner_sorted_fields[] = $field;
										}
									}
								} else {
									$arf_inner_sorted_fields[] = $temp_fid;
								}
							}
						}
					}

					if (isset($arf_inner_sorted_fields) && !empty($arf_inner_sorted_fields)) {
                        $get_all_inner_fields = $arf_inner_sorted_fields;
                    }

                    if( is_array( $unsaved_inner_fields ) && !empty( $unsaved_inner_fields ) ){
                    	$arf_sorted_inner_unsave_fields = array();
                    	foreach ($inner_field_order as $inner_field_id => $inner_order) {
                    		foreach( $inner_order as $in_order ){
                    			$exploded_data = explode('|', $in_order);
                    			$in_field_id = $exploded_data[0];
				                foreach ($unsaved_inner_fields as $infid => $inner_field_data) {
				                    if ($in_field_id == $infid) {
				                        $arf_sorted_inner_unsave_fields[$infid] = $inner_field_data;
				                    }
				                }
                    		}
			            }

			            $unsaved_inner_fields = $arf_sorted_inner_unsave_fields;

			            $temp_inner_fields = array();
			            foreach ($unsaved_inner_fields as $inner_key => $inner_value) {
			                $inner_opts = json_decode($inner_value, true);
			                if( isset( $inner_opts ) && $inner_opts['parent_field'] != $check_field['id'] ){
			                	continue;
			                }
			                foreach ($inner_opts as $ink => $inner_val) {
			                    if($ink == 'key'){
			                        $temp_inner_fields[$inner_key]['field_key'] = $inner_val;         
			                    } else if ($ink == 'options' || $ink == 'default_value') {
			                        $temp_inner_fields[$inner_key][$ink] = ($inner_val != '' ) ? json_encode($inner_val) : '';
			                    } else {
			                        $temp_inner_fields[$inner_key][$ink] = $inner_val;
			                    }
			                }
			                $temp_inner_fields[$inner_key]['field_options'] = $inner_value;
			                $temp_inner_fields[$inner_key]['id'] = $inner_key;
			            }
			            $get_all_inner_fields = array_merge($get_all_inner_fields, $temp_inner_fields);
                    }

                    $get_all_inner_fields_new = array();

                    $arf_inner_sorted_fields_new = array();

                	if( isset( $inner_field_order[$check_field['id']]  ) ){
						foreach( $inner_field_order[$check_field['id']] as $k => $inner_order ){
							$exploded_data = explode('|', $inner_order);
							$temp_fid = $exploded_data[0];

							if( preg_match('/^(\d+)$/',$temp_fid ) ){
								foreach( $get_all_inner_fields as $field ){
									if( isset($field['id']) && $field['id'] == $temp_fid ){
										$arf_inner_sorted_fields_new[] = $field;
									}
								}
							} else {
								$arf_inner_sorted_fields_new[] = $temp_fid;
							}
						}
                    }

                    if( !empty( $arf_inner_sorted_fields_new ) ){
	                    $get_all_inner_fields = $arf_inner_sorted_fields_new;
                    }
                    $field_classes = array();
                    $inner_field_counter = 1;
                    foreach( $get_all_inner_fields as $field_key => $field ){

                		$inside_repeatable_field = true;
                    	if( is_array( $field ) ){
							$field_name = "item_meta[" . $field['id'] . "]";
                            $has_field_opt = false;
                            if (isset($field['options']) && $field['options'] != '' && !empty($field['options'])) {
                                $has_field_opt = true;
                                $field_options_db = json_decode($field['options'], true);
                            }
                            
                            $field_opt = json_decode($field['field_options'], true);
                            
                            $class = (isset($field_opt['inner_class']) && $field_opt['inner_class']) ? $field_opt['inner_class'] : 'arf_1col';
                            $field_classes[] = $class;
                            array_push($class_array,$class);

                            if (isset($field_opt) && !empty($field_opt) && is_array($field_opt) ) {
                                foreach ($field_opt as $k => $field_opt_val) {
                                    if ($k != 'options') {
                                        $field[$k] = $arformcontroller->arf_html_entity_decode($field_opt_val);
                                    } else {
                                        if ($has_field_opt == true && $k == 'options') {
                                            $field[$k] = $field_options_db;
                                        }
                                    }
                                }
                            }
                            if (in_array($field['type'], $bootstraped_fields_array)) {
                                array_push($fields_with_external_js, $field['type']);
                            }
                    	} else {
                    		$field_classes[] = $field;
                    	}
                    	if( !empty( $field ) && is_array( $field ) && ( !isset( $field['form_id'] ) || empty( $field['form_id'] ) ) ){
			                $field['form_id'] = $id;
			            }

			            if( isset( $remove_placeholders ) && true == $remove_placeholders && !empty( $field['placeholdertext'] ) ){
                            $field['placeholdertext'] = '';
                            if( !empty( $field_opt['placeholdertext'] ) ){
                                $field_opt['placeholdertext'] = '';
                                $field['field_options'] = json_encode( $field_opt );
                            }
                        }
                		$filename = VIEWS_PATH . '/arf_field_editor.php';
                		ob_start();
			            include $filename;
			            $arf_repeater_field_html .= ob_get_contents();
			            ob_end_clean();
                	
						$inner_field_counter++;
                    }
                    $index_repeater_fields += $index_arf_fields;

				$arf_repeater_field_html .= '<input type="hidden" name="arf_field_data_'.$check_field['id'].'" id="arf_field_data_'.$check_field['id'].'" class="arf_field_data_hidden" value="'.htmlspecialchars(json_encode($repeater_field_obj)).'" data-field_options=\''.json_encode($field_opt_order).'\' />';

				$arf_repeater_field_html .= '<div class="arf_field_option_model arf_field_option_model_cloned" data-field_id="'.$check_field['id'].'">';
                    
                    $arf_repeater_field_html .= '<div class="arf_field_option_model_header">'.addslashes(esc_html__('Field Options', 'ARForms')).'&nbsp;<span class="arf_pre_populated_field_type" id="{arf_field_type}">[Field Type : [arf_field_type]]</span>&nbsp;<span class="arf_pre_populated_field_id" id="{arf_field_id}">[Field ID:[arf_field_id]]</span></div>';
                    	$arf_repeater_field_html .= '<div class="arf_field_option_model_container">';
                        	$arf_repeater_field_html .= '<div class="arf_field_option_content_row">';
                        	$arf_repeater_field_html .= '</div>';
                    	$arf_repeater_field_html .= '</div>';
                    	$arf_repeater_field_html .= '<div class="arf_field_option_model_footer">';
                        	$arf_repeater_field_html .= '<button type="button" class="arf_field_option_close_button" onClick="arf_close_field_option_popup('.$check_field['id'].');">'.addslashes(esc_html__('Cancel', 'ARForms')).'</button>';
                        	$arf_repeater_field_html .= '<button type="button" class="arf_field_option_submit_button" data-field_id="'.$check_field['id'].'">'.esc_html__('OK', 'ARForms').'</button>';
                    	$arf_repeater_field_html .= '</div>';
                	$arf_repeater_field_html .= '</div>';
				
				$arf_repeater_field_html .= '</div>';

			$arf_repeater_field_html .= '</div>';

			if( $return_html ){
				return $arf_repeater_field_html;
			} else {
				echo $arf_repeater_field_html;
			}
		}

	}

	function arf_wrap_repeater_field_from_outside( $wrap, $field_type ){

		if( 'arf_repeater' == $field_type ){
			return false;
		}

		return $wrap;
	}

	function arf_render_repeater_field_in_form( $return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tooltip, $field_description,$OFData,$inputStyle,$arf_main_label,$arf_on_change_function ){

		if( 'arf_repeater' == $field['type'] ){

			global $MdlDb, $wpdb,$arrecordhelper,$arformcontroller,$all_preview_fields,$arfieldhelper;
			$arfsubmitbuttonstyle = isset($form->form_css['arfsubmitbuttonstyle']) ? $form->form_css['arfsubmitbuttonstyle'] : 'border';
			$sbmt_class = "btn btn-flat";
			$max_add = isset($field['field_options']['repeater_upto']) ? $field['field_options']['repeater_upto'] : 0;
			$repeater_field_name = isset($field['field_options']['name']) ? $field['field_options']['name'] : esc_html__('Repeater Section Title', 'ARForms');
			$section_style = "style='display:block;'";
            if(isset($field['field_options']['ishidetitle']) && $field['field_options']['ishidetitle']==1){
                $section_style = "style='display:none;'";
            }
			$is_preview = false;
			if( isset( $_REQUEST['arf_opt_id'] ) && '' != $_REQUEST['arf_opt_id']  ){
				$is_preview = true;
			}

			if( $is_preview && !empty( $all_preview_fields ) ){
				$get_all_inner_fields = array();
				
				foreach( $all_preview_fields as $pfkey => $pfval ){
					if( isset( $pfval->has_parent ) && $pfval->has_parent && $pfval->parent_field == $field['id'] ){
						$get_all_inner_fields[] = $arformcontroller->arfObjtoArray($pfval);
					}
				}
			} else {
				$arf_parent_field_cache_obj = wp_cache_get( 'arf_repeater_field_data_' . $field['id'] );
				if( false === $arf_parent_field_cache_obj )
				{
					$get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".$MdlDb->fields."` WHERE field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%'", $field['id'], $field['id'] ), ARRAY_A );
					wp_cache_set( 'arf_repeater_field_data_' . $field['id'], $get_all_inner_fields );
				} else {
					$get_all_inner_fields = $arf_parent_field_cache_obj;
				}
			}

			if( count( $get_all_inner_fields ) < 1 ){
				return $return_string;
			}
			$add_btn_lbl = !empty($field['field_options']['add_button_label']) ? $field['field_options']['add_button_label'] : esc_html__('Add More', 'ARForms') ;
			$remove_btn_lbl = !empty($field['field_options']['remove_button_label']) ? $field['field_options']['remove_button_label'] : esc_html__('Remove', 'ARForms');

			$return_string .= '<input type="hidden" id="arf_repeatable_field_counter_'.$field['id'].'" data-max_add="'.$max_add.'" value="1" />';

			
			$display_confirmation_summary = false;
			$summary_field_cls = "";
			$frm_opt = $form->options;
			
			if( isset($frm_opt['arf_confirmation_summary']) && $frm_opt['arf_confirmation_summary'] == 1 ){
                $display_confirmation_summary = true;
                $summary_field_cls = "arf_display_to_confirmation_summary";
            }
            $return_string .= '<div class="arf_repeater_field_wrapper arf_heading_div arfmainformfieldrepeater">';
            	$return_string .= '<h2 class="arf_sec_heading_field pos_'.$field['label'].'"  '.$section_style.'>'.$repeater_field_name.'</h2>';
				$return_string .= '<div id="arf_field_'.$field['id'].'_'.$arf_data_uniq_id.'_container_0" class="arf_repeater_field arfformfield control-group arfmainformfield input-field arf_field_type_'.$field['type'].'  top_container '.$summary_field_cls.' top_container arfformfield  arf_field_'.$field['id'].'" data-field-type="arf_repeater">';

					$return_string .= '<div class="repeater_field_controls">';

						if( $is_preview && !empty( $all_preview_fields ) ){

							$all_inner_fields = array();

							foreach( $get_all_inner_fields as $ik => $in_field ){
								if( is_array( $in_field ) ){
									if( !isset( $all_inner_fields[$ik]) ){
										$all_inner_fields[$ik] = new stdClass;
									}							

									foreach( $in_field as $infk => $infv ){
										if( 'field_options' == $infk ){
											$inf_opt = arf_json_decode( $infv, true );
											$temp_obj = array();
											foreach( $inf_opt as $ink => $inv ){
												$temp_obj[$ink] = $inv;
											}
											$all_inner_fields[$ik]->$infk = $temp_obj;
										} else if( 'options' == $infk ){
											if( '' != $infv ){
												$inf_opt = arf_json_decode( $infv, true );
												$temp_obj = array();
												foreach( $inf_opt as $ink => $inv ){
													$temp_obj[$ink] = $inv;
												}
												$all_inner_fields[$ik]->$infk = $temp_obj;
											} else {
												$all_inner_fields[$ik]->$infk = $infv;
											}
										} else {
											$all_inner_fields[$ik]->$infk = $infv;
										}
									}
								}
							}

							$values = $arrecordhelper->setup_new_vars($all_inner_fields, $form);
						} else {

							$all_inner_fields = array();

							foreach( $get_all_inner_fields as $ik => $in_field ){
								if( is_array( $in_field ) ){
									if( !isset( $all_inner_fields[$ik]) ){
										$all_inner_fields[$ik] = new stdClass;
									}
									
									foreach( $in_field as $infk => $infv ){
										if( 'field_options' == $infk ){
											$inf_opt = arf_json_decode( $infv, true );
											$temp_obj = array();
											foreach( $inf_opt as $ink => $inv ){
												$temp_obj[$ink] = $inv;
											}
											$all_inner_fields[$ik]->$infk = $temp_obj;
										} else if( 'options' == $infk ){
											if( '' != $infv ){
												$inf_opt = arf_json_decode( $infv, true );
												$temp_obj = array();
												foreach( $inf_opt as $ink => $inv ){
													$temp_obj[$ink] = $inv;
												}
												$all_inner_fields[$ik]->$infk = $temp_obj;
											} else {
												$all_inner_fields[$ik]->$infk = $infv;
											}
										} else {
											$all_inner_fields[$ik]->$infk = $infv;
										}
									}
								}
							}

							$values = $arrecordhelper->setup_new_vars($all_inner_fields, $form);
						}

						$totalpass = 0;
						foreach( $values['fields'] as $arrkey => $iconf_field ){

							if( 'email' == $iconf_field['type'] && $iconf_field['confirm_email'] ){
								if( isset( $iconf_field['confirm_email'] ) && 1 == $iconf_field['confirm_email'] && isset( $arf_load_confirm_email['confirm_email_field'] ) && $arf_load_confirm_email['confirm_email_field'] == $iconf_field['id'] ){
									$values['confirm_email_arr'][$iconf_field['id']] = isset($iconf_field['confirm_email_field']) ? $iconf_field['confirm_email_field'] : "";
								} else {
									$arf_load_confirm_email['confirm_email_field'] = isset($iconf_field['confirm_email_field']) ? $iconf_field['confirm_email_field'] : "";
								}
								$confirm_email_field = $arfieldhelper->get_confirm_email_field($iconf_field);

					            $values['fields'] = $arfieldhelper->array_push_after($values['fields'], array($confirm_email_field), (int)$arrkey + (int)$totalpass);
					            $totalpass++;
							}
						}
						
						$return_string .= $arformcontroller->get_all_field_html( $form, $values, $arf_data_uniq_id, $all_inner_fields, $is_preview, false, $inputStyle, array(), true, 0, $field['id'] );

					$return_string .= '</div>';

					$return_string .= '<div class="arf_repeater_button_wrapper">';
						$return_string .= '<button type="button" class="arf_repeater_remove_new_button arf_remove_btn_'.$field['id'].'" data-random-id="' . $arf_data_uniq_id . '" data-field-id="'.$field['id'].'"><i class="fa fa-minus fa-lg"></i></button>';
						$return_string .= '<button type="button" class="arf_repeater_add_new_button arf_add_new_btn_'.$field['id'].'" data-random-id="'. $arf_data_uniq_id . '" data-field-id="' . $field['id'] . '"><i class="fa fa-plus fa-lg"></i></button>';

					$return_string .= '</div>';

				$return_string .= '</div>';
			$return_string .= '</div>';
		}

		return $return_string;
	}

	function arf_add_parent_field_data_to_field( $field_data_object_array, $ftype, $parent_field_id ){

		$no_parent_data_fields = apply_filters( 'arf_prevent_parent_data_in_fields', array('divider', 'section', 'break', 'hidden','imagecontrol') );

		if( !in_array( $ftype, $no_parent_data_fields ) ){

			if( 'number' == $ftype ){
				$isccfield = ( 'ccfield' == $field_data_object_array['field_data'][$ftype]['type2'] ) ? true : false;

				if( $isccfield ){
					return $field_data_object_array;
				} else {
					$field_data_object_array['field_data'][$ftype]['has_parent'] = true;
					$field_data_object_array['field_data'][$ftype]['parent_field'] = $parent_field_id;
					$field_data_object_array['field_data'][$ftype]['parent_field_type'] = 'arf_repeater';
				}
			} else {
				$field_data_object_array['field_data'][$ftype]['has_parent'] = true;
				$field_data_object_array['field_data'][$ftype]['parent_field'] = $parent_field_id;
				$field_data_object_array['field_data'][$ftype]['parent_field_type'] = 'arf_repeater';
			}

		}

		return $field_data_object_array;
	}

	function arf_repeater_field_options( $args ){

		$repeater_field_opt = array(
			'labelname' => 1,
			'repeater_upto' => 2,
			'ishidetitle' => 3
		);

		$args['arf_repeater'] = $repeater_field_opt;

		return $args;
	}

	function arf_repeater_field_popup_options(){

		?>
		<div class="arf_field_option_content_cell" data-sort="-1" id="repeater_upto">
            <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Maximum possible repeat', 'ARForms'); ?>&nbsp;&nbsp;<img src="<?php echo esc_url(ARFIMAGESURL.'/tooltips-icon.png'); ?>" class="arf_popup_tooltip_main" data-title="<?php printf(esc_html__('Maximum x number of times this group of field can be repeated.','ARForms'),'<br/>'); ?>" />
            </label>

            <div class="arf_field_option_content_cell_label">
                <input id="arfheight_for_moblie{arf_field_id}" type="text" name="repeater_upto" value="0" class="arf_field_option_input_text txtstandardnew arfblank_txt" />
                <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Set 0 or blank to unlimited times.', 'ARForms')); ?></span>
            </div>
        </div>
        <?php
	}

	function arf_check_repeater_field( $fields ){

		$is_repeater = 0;
		$repeater_field_ids = array();
		foreach( $fields as $k => $field ){
			if( isset( $field->type ) && 'arf_repeater' == $field->type ){
				$repeater_field_ids[] = $field->id;
				$is_repeater++;
			}
		}

		if( $is_repeater > 0 ){
			return '<input type="hidden" name="repeater_removed_fields" id="repeater_remove_fields" /><input type="hidden" id="repeater_field_ids" name="repeater_field_ids" value=\'' . json_encode( $repeater_field_ids ) . '\' />';
		} else {
			return '';
		}

	}

	function arf_unset_removed_child_fields( $values ){

		$repeater_field_ids = ( isset( $_POST['repeater_field_ids'] ) && '' != $_POST['repeater_field_ids'] ) ? json_decode( stripslashes_deep( $_POST['repeater_field_ids'] ) ) : '';
		$removed_field_ids = ( isset( $_POST['repeater_removed_fields'] ) && '' != $_POST['repeater_removed_fields'] ) ? json_decode( stripslashes_deep( $_POST['repeater_removed_fields'] ) ) : '';

		if( '' == $repeater_field_ids || '' == $removed_field_ids ){
			return $values;
		}

		foreach( $repeater_field_ids as $repeater_id ){
			if( isset( $values['item_meta'][$repeater_id] ) ){
				foreach( $values['item_meta'][$repeater_id] as $inner_field_key => $inner_field_array ){
					if( isset( $removed_field_ids->$inner_field_key ) ){
						$inner_keys = explode( ',', $removed_field_ids->$inner_field_key );

						foreach( $inner_keys as $k => $v ){
							unset( $values['item_meta'][$repeater_id][$inner_field_key][$v] );
						}
					}
				}
			}
		}

		return $values;
	}

	function arf_change_field_option_icon_data( $positioned_field_icons,$field_icons ){

		$positioned_field_icons['arf_repeater'] = str_replace('{arf_field_type}','arf_repeater',$field_icons['arf_field_duplicate_icon'])."{$field_icons['field_delete_icon']}".str_replace('{arf_field_type}','arf_repeater',$field_icons['field_option_icon'])."{$field_icons['arf_field_move_icon']}";

		return $positioned_field_icons;
	}

}