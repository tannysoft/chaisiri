<?php


global $arf_section_field_class;

$arf_section_field_class = new arf_section_field();

class arf_section_field{

	function __construct(){

		add_filter( 'arf_new_field_array_filter_outside', array( $this, 'arf_add_section_field_outside' ), 11, 4 );
	
		add_filter( 'arf_new_field_array_materialize_filter_outside', array( $this, 'arf_add_section_field_outside' ), 11, 4 );

		add_filter( 'arf_new_field_array_materialize_outlined_filter_outside', array( $this, 'arf_add_section_field_outside', ), 11, 4 );

		add_filter( 'arf_display_field_in_editor_outside', array( $this, 'arf_display_field_section_in_form_editor'), 10, 2 );

		add_action( 'arf_render_field_in_editor_outside', array( $this, 'arf_render_section_field_in_editor'), 10, 13 );

		add_filter( 'arf_add_parent_data_to_field', array( $this, 'arf_add_parent_section_field_data_to_field'), 10, 3 );

		add_action( 'arf_disply_multicolumn_fieldolumn_field_outside', array( $this, 'arf_hide_multicolumn_for_section'), 10, 2 );

		add_action( 'arf_wrap_input_field', array( $this, 'arf_wrap_section_field_from_outside'), 11, 2 );

		add_filter( 'form_fields', array( $this, 'arf_render_section_field_in_form' ), 10, 11 );

		add_filter( 'arf_positioned_field_options_icon', array( $this, 'arf_change_section_field_option_icon_data'),10,2);

	}

	function arf_render_section_field_in_editor( $check_field, $field_data_obj, $field_order, $inner_field_order,$index_arf_fields, $frm_css, $data, $id,$inner_field_resize_width, $unsaved_inner_fields, $return_html, $newarr, $remove_placeholders ){

		global $arformcontroller,$MdlDb,$wpdb,$bootstraped_fields_array,$fields_with_external_js, $arfieldcontroller,$arfieldhelper,$index_repeater_fields;

		if( 'section' == $check_field['type'] ){
			$arf_section_field_html = '';

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
	            if ( isset( $_REQUEST['arfaction'] ) && $_REQUEST['arfaction'] != 'edit' ) {
	                if ( $key == 'placeholdertext' ) {
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

	        $section_field_obj = $new_field_obj;

	        $current_field_order = array();

	        $arf_section_field_html .= '<div class="arfmainformfield top_container edit_field_type_arf_section_wrapper edit_form_item arffieldbox ui-state-default arf1columns" id="section">';
				$inner_cls = isset($check_field['inner_class']) ? $check_field['inner_class'] : 'arf_1col';

				$arf_section_field_html .= '<div class="unsortable_inner_wrapper edit_field_type_section" id="arf_field_'.$check_field['id'].'" >';

					$arf_section_field_html .= '<div class="fieldname-row" style="display:block;">';

						$arf_section_field_html .= '<div class="fieldname">';

								$arf_section_field_html .= '<label class="arf_main_label '.$arf_main_label_cls.'" id="field_'.$check_field['id'].'">';

									$arf_section_field_html .= '<span class="arfeditorfieldopt_section_label arf_edit_in_place arfeditorfieldopt_label">';

										$arf_section_field_html .= '<input type="text" class="arf_edit_in_place_input inplace_field" data-ajax="false" data-field-opt-change="true" data-field-opt-key="name" value="'.htmlspecialchars($check_field['name']).'" data-field-id="'.$check_field['id'].'" />';

									$arf_section_field_html .= '</span>';

								$arf_section_field_html .= '</label>';

						$arf_section_field_html .= '</div>';

						$arf_section_field_html .= '<div class="arf_field_description" id="field_description_'.$check_field['id'].'">'.$check_field['description'].'</div>';

					$arf_section_field_html .= '</div>';

				$arf_section_field_html .= '</div>';

				$arf_section_field_html .= '<div class="arf_fieldiconbox arf_section_fieldiconbox" data-field_id="'.$check_field['id'].'">';

					$arf_section_field_html .= $arfieldcontroller->arf_get_field_control_icons('duplicate', '', $check_field['id'], 0, $check_field['type'], $id);
					$arf_section_field_html .= $arfieldcontroller->arf_get_field_control_icons('delete', '', $check_field['id'], 0, $check_field['type'], $id);
					$arf_section_field_html .= $arfieldcontroller->arf_get_field_control_icons('options', '', $check_field['id'], 0, $check_field['type'], $id);
					$arf_section_field_html .= $arfieldcontroller->arf_get_field_control_icons('move', '', $check_field['id'], 0, $check_field['type'], $id);

				$arf_section_field_html .= '</div>';

				$arf_section_field_html .= '<div class="sortable_inner_wrapper edit_field_type_arf_section" id="arfmainfieldid_'.$check_field['id'].'" inner_class="'.$inner_cls.'" >';

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

			            $is_saved_field_key = $arformcontroller->arfSearchArray( $inner_key, 'id', $get_all_inner_fields );
			            if( $is_saved_field_key > -1 ){
			            	$get_all_inner_fields[ $is_saved_field_key ] = $temp_inner_fields[ $inner_key ];
			            } else {
			            	$get_all_inner_fields = array_merge($get_all_inner_fields, $temp_inner_fields);
			            }
                    }

                    $get_all_inner_fields_new = array();

                    $arf_inner_sorted_fields_new = array();


                	if( isset( $inner_field_order[$check_field['id']]  ) ){
                		$pre_fid = array();
						foreach( $inner_field_order[$check_field['id']] as $k => $inner_order ){
							$exploded_data = explode('|', $inner_order);
							$temp_fid = $exploded_data[0];
							
							if( !empty( $pre_fid ) && preg_match( '/^[\d]+$/', $temp_fid ) && in_array( $temp_fid, $pre_fid ) ){
                                continue;
                            }
							if( preg_match('/^(\d+)$/',$temp_fid ) ){
								foreach( $get_all_inner_fields as $field ){
									if( isset($field['id']) && $field['id'] == $temp_fid ){
										$arf_inner_sorted_fields_new[] = $field;
									}
								}
							} else {
								$arf_inner_sorted_fields_new[] = $temp_fid;
							}

							if( empty( $pre_fid ) || !in_array( $temp_fid, $pre_fid) ){
	                            array_push($pre_fid, $temp_fid );
	                        }
						}

                    }


                    if( !empty( $arf_inner_sorted_fields_new ) ){
	                    $get_all_inner_fields = $arf_inner_sorted_fields_new;
                    }

                    
                    $field_classes = array();
                    $inner_field_counter = 1;

                    foreach( $get_all_inner_fields as $field_key => $field ){

                		$inside_section_field = true;
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
			            $arf_section_field_html .= ob_get_contents();
			            ob_end_clean();
						$inner_field_counter++;
                    }
                    $index_repeater_fields += $index_arf_fields;


				$arf_section_field_html .= '<input type="hidden" name="arf_field_data_'.$check_field['id'].'" id="arf_field_data_'.$check_field['id'].'" class="arf_field_data_hidden" value="'.htmlspecialchars(json_encode($section_field_obj)).'" data-field_options=\''.json_encode($field_opt_order).'\' />';

				$arf_section_field_html .= '<div class="arf_field_option_model arf_field_option_model_cloned" data-field_id="'.$check_field['id'].'">';
                    
                    $arf_section_field_html .= '<div class="arf_field_option_model_header">'.addslashes(esc_html__('Field Options', 'ARForms')).'&nbsp;<span class="arf_pre_populated_field_type" id="{arf_field_type}">[Field Type : [arf_field_type]]</span>&nbsp;<span class="arf_pre_populated_field_id" id="{arf_field_id}">[Field ID:[arf_field_id]]</span></div>';
                    	$arf_section_field_html .= '<div class="arf_field_option_model_container">';
                        	$arf_section_field_html .= '<div class="arf_field_option_content_row">';
                        	$arf_section_field_html .= '</div>';
                    	$arf_section_field_html .= '</div>';
                    	$arf_section_field_html .= '<div class="arf_field_option_model_footer">';
                        	$arf_section_field_html .= '<button type="button" class="arf_field_option_close_button" onClick="arf_close_field_option_popup('.$check_field['id'].');">'.addslashes(esc_html__('Cancel', 'ARForms')).'</button>';
                        	$arf_section_field_html .= '<button type="button" class="arf_field_option_submit_button" data-field_id="'.$check_field['id'].'">'.esc_html__('OK', 'ARForms').'</button>';
                    	$arf_section_field_html .= '</div>';
                	$arf_section_field_html .= '</div>';
				
				$arf_section_field_html .= '</div>';

			$arf_section_field_html .= '</div>';

			if( $return_html ){
				return $arf_section_field_html;
			} else {
				echo $arf_section_field_html;
			}
		}

	}

	function arf_display_field_section_in_form_editor( $display, $field ){

		if( isset( $field['type'] ) && 'section' == $field['type'] ){
			$display = true;
		}

		return $display;
	}

	function arf_add_section_field_outside( $fields, $field_icons, $json_data, $positioned_field_icons ){
		
		global $arfieldhelper,$arfieldcontroller;

		$field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();

		$field_order_arf_section = isset( $field_opt_arr['section'] ) ? $field_opt_arr['section'] : '';

		$field_data_array = $json_data;

		$field_data_obj_arf_section = $field_data_array->field_data->section;


		$arf_section_field  = '<div class="arfmainformfield edit_field_type_arf_section_wrapper top_container edit_form_item arffieldbox ui-state-default arf1columns" id="section">';

			$arf_section_field .= '<div class="unsortable_inner_wrapper edit_field_type_section" id="arf_field_{arf_field_id}">';

				$arf_section_field .= '<div class="fieldname-row" style="display:block;">';

					$arf_section_field .= '<div class="fieldname">';

							$arf_section_field .= '<label class="arf_main_label" id="field_{arf_field_id}">';

								$arf_section_field .= '<span class="arfeditorfieldopt_section_label arf_edit_in_place arfeditorfieldopt_label">';

									$arf_section_field .= '<input type="text" class="arf_edit_in_place_input inplace_field" data-ajax="false" data-field-opt-change="true" data-field-opt-key="name" value="Section Title" data-field-id="{arf_field_id}" />';

								$arf_section_field .= '</span>';

							$arf_section_field .= '</label>';

					$arf_section_field .= '</div>';

					$arf_section_field .= '<div class="arf_field_description" id="field_description_{arf_field_id}"></div>';

				$arf_section_field .= '</div>';

			$arf_section_field .= '</div>';

			$arf_section_field .= '<div class="arf_fieldiconbox arf_section_fieldiconbox" data-field_id="{arf_field_id}">';

				$arf_section_field .= $positioned_field_icons['section'];

			$arf_section_field .= '</div>';

			$arf_section_field .= '<div class="sortable_inner_wrapper edit_field_type_arf_section" id="arfmainfieldid_{arf_field_id}" inner_class="arf_1col" >';


				$arf_section_field .= "<input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='" . htmlspecialchars(json_encode($field_data_obj_arf_section)) . "' data-field_options='" . json_encode($field_order_arf_section) . "' />";

				$arf_section_field .= '<div class="arf_field_option_model arf_field_option_model_cloned" data-field_id="{arf_field_id}">';
                    
                    $arf_section_field .= '<div class="arf_field_option_model_header">'.addslashes(esc_html__('Field Options', 'ARForms')).'&nbsp;<span class="arf_pre_populated_field_type" id="{arf_field_type}">[Field Type : [arf_field_type]]</span>&nbsp;<span class="arf_pre_populated_field_id" id="{arf_field_id}">[Field ID:[arf_field_id]]</span></div>';
                    	$arf_section_field .= '<div class="arf_field_option_model_container">';
                        	$arf_section_field .= '<div class="arf_field_option_content_row">';
                        	$arf_section_field .= '</div>';
                    	$arf_section_field .= '</div>';
                    	$arf_section_field .= '<div class="arf_field_option_model_footer">';
                        	$arf_section_field .= '<button type="button" class="arf_field_option_close_button" onClick="arf_close_field_option_popup({arf_field_id});">'.addslashes(esc_html__('Cancel', 'ARForms')).'</button>';
                        	$arf_section_field .= '<button type="button" class="arf_field_option_submit_button" data-field_id="{arf_field_id}">'.esc_html__('OK', 'ARForms').'</button>';
                    	$arf_section_field .= '</div>';
                	$arf_section_field .= '</div>';
				
				$arf_section_field .= '</div>';

			$arf_section_field .= '</div>';

		$arf_section_field .= '</div>';


		$fields['section'] = $arf_section_field;


		return $fields;


	}

	function arf_add_parent_section_field_data_to_field( $field_data_object_array, $ftype, $parent_field_id ){
		
		$no_parent_data_fields = apply_filters( 'arf_prevent_parent_data_in_fields', array('divider', 'section', 'break', 'hidden', 'imagecontrol', 'arf_repeater') );
		if( !in_array( $ftype, $no_parent_data_fields ) ){

			$field_data_object_array['field_data'][$ftype]['has_parent'] = true;
			$field_data_object_array['field_data'][$ftype]['parent_field'] = $parent_field_id;
			$field_data_object_array['field_data'][$ftype]['parent_field_type'] = 'section';

		}

		return $field_data_object_array;

	}

	function arf_hide_multicolumn_for_section( $display_multicolumn, $field ){

		if( isset( $field['type'] ) && 'section' == $field['type'] ){
			$display_multicolumn = false;
		}

		return $display_multicolumn;
	}

	function arf_wrap_section_field_from_outside( $wrap, $field_type ){

		if( 'section' == $field_type ){
			$wrap = false;
		}

		return $wrap;
	}

	function arf_render_section_field_in_form( $return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tooltip, $field_description,$OFData,$inputStyle,$arf_main_label,$arf_on_change_function ){

		if( 'section' == $field['type'] ){
			global $MdlDb, $wpdb,$arrecordhelper,$arformcontroller,$all_preview_fields,$arfieldhelper, $arf_glb_preset_data;
			$arfsubmitbuttonstyle = isset($form->form_css['arfsubmitbuttonstyle']) ? $form->form_css['arfsubmitbuttonstyle'] : 'border';
			$sbmt_class = "btn btn-flat";
			$max_add = isset($field['field_options']['repeater_upto']) ? $field['field_options']['repeater_upto'] : 0;
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
				$arf_parent_field_cache_obj = wp_cache_get( 'arf_field_data_' . $field['id'] );

				if( false === $arf_parent_field_cache_obj )
				{
					$get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".$MdlDb->fields."` WHERE 	field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%'", $field['id'], $field['id'] ), ARRAY_A );
					wp_cache_set( 'arf_field_data_' . $field['id'], $get_all_inner_fields );
				} else {
					$get_all_inner_fields = $arf_parent_field_cache_obj;
				}
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
			$return_string .= '<div id="heading_'.$field['id'].'" class="arf_heading_div">';

				$section_style = "style='display:block';";
                if(isset($field['field_options']['ishidetitle']) && $field['field_options']['ishidetitle']==1){
                    $section_style = "style='display:none';";
                }

				$return_string .= "<h2 class='arf_sec_heading_field pos_".$field['label']." {$summary_field_cls} ' {$section_style} data-field-type='section' >".$field['name']."</h2>";
				$return_string .= '<div class="arf_field_description arf_heading_description">'.$field['description'].'</div>';
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
					if( 'password' == $iconf_field['type'] && $iconf_field['confirm_password'] ){
						if( isset( $iconf_field['confirm_password'] ) && 1 == $iconf_field['confirm_password'] && isset( $arf_load_confirm_password['confirm_password_field'] ) && $arf_load_confirm_password['confirm_password_field'] == $iconf_field['id'] ){
							$values['confirm_password_arr'][$iconf_field['id']] = isset($iconf_field['confirm_password_field']) ? $iconf_field['confirm_password_field'] : "";
						} else {
							$arf_load_confirm_password['confirm_password_field'] = isset($iconf_field['confirm_password_field']) ? $iconf_field['confirm_password_field'] : "";
						}
						$confirm_password_field = $arfieldhelper->get_confirm_password_field($iconf_field);

					    $values['fields'] = $arfieldhelper->array_push_after($values['fields'], array($confirm_password_field), (int)$arrkey + (int)$totalpass);
					    $totalpass++;
					}
				}
				
				$return_string .= $arformcontroller->get_all_field_html( $form, $values, $arf_data_uniq_id, $all_inner_fields, $is_preview, false, $inputStyle, $arf_glb_preset_data, true, 0, $field['id'], true );

			$return_string .= '</div>';
		}

		return $return_string;

	}

	function arf_change_section_field_option_icon_data( $positioned_field_icons,$field_icons ){
		$positioned_field_icons['section'] = str_replace('{arf_field_type}','section',$field_icons['arf_field_duplicate_icon'])."{$field_icons['field_delete_icon']}".str_replace('{arf_field_type}','section',$field_icons['field_option_icon'])."{$field_icons['arf_field_move_icon']}";

		return $positioned_field_icons;

	}

}