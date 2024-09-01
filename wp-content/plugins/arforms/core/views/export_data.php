<?php
@ini_set("memory_limit", "512M");

global $arrecordhelper,$arrecordcontroller,$maincontroller,$arfieldhelper,$armainhelper,$arfsettings,$MdlDb,$arfrecordmeta;

	$maincontroller->arfafterinstall();
	global $style_settings;

	$form_id = $all_form_id;
	
	$form = $arfform->getOne($form_id);
	
	$form_name = sanitize_title_with_dashes($form->name);

	$form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'imagecontrol') and fi.form_id=".$form->id, 'id ASC');

	$repeater_fields = array();

	foreach( $form_cols as $temp_field ){
		if( isset( $temp_field->field_options['has_parent'] ) && $temp_field->field_options['parent_field_type'] == 'arf_repeater' ){
			$repeater_fields[] = $temp_field->id.'||'.$temp_field->field_options['parent_field'];
		}
	}
	if( !isset( $_REQUEST['bulk_export'] ) || ( isset( $_REQUEST['bulk_export'] ) && $_REQUEST['bulk_export'] != 'yes' ) ){
		$entry_id = $armainhelper->get_param('entry_id', false);
	} else if( isset( $_REQUEST['bulk_export'] ) && $_REQUEST['bulk_export'] == 'yes' )  {
		$form_entry_ids = $wpdb->get_results( $wpdb->prepare("SELECT id FROM `". $MdlDb->entries . "` WHERE form_id = %d", $form_id ) );
		$entry_id = "";
		foreach( $form_entry_ids as $frm_entry_id ){
			$entry_id .= $frm_entry_id->id.',';
		}
		$entry_id = rtrim( $entry_id, ',');
	}

	$where_clause = "it.form_id=". (int)$form_id;

	if($entry_id){

		$where_clause .= " and it.id in (";

		$entry_ids = explode(',', $entry_id);
	
		foreach((array)$entry_ids as $k => $it){
			if($k){
				$where_clause .= ",";
			}

			$where_clause .= $it;

			unset($k);

			unset($it);
		}

		$where_clause .= ")";
	} else if(!empty($search)) {
		$where_clause = $this->get_search_str($where_clause, $search, $form_id, $fid);
	}

	$where_clause = apply_filters('arfcsvwhere', $where_clause, compact('form_id'));

	$entries = $db_record->getAll($where_clause, '', '', true, false);
	
	$form_cols	= apply_filters('arfpredisplayformcols', $form_cols, $form->id);
	$entries		= apply_filters('arfpredisplaycolsitems', $entries, $form->id);

	$repeater_cols = array();
	$temp_cnt = 0;
	foreach( $entries as $temp_entry ){
		foreach( $repeater_fields as $tk => $tf ){
			$tfExplode = explode('||', $tf);
			$repeater_field_id = $tfExplode[1];
			$tf_id = $tfExplode[0];
			
			if( isset( $temp_entry->metas[$tf_id] ) ){
				$counter = count( explode( '[ARF_JOIN]', $temp_entry->metas[$tf_id] ) );
				if( $counter > $temp_cnt ){
					$temp_cnt = $counter;
				} else {
					$counter = $temp_cnt;
				}
				if( isset( $repeater_cols[$repeater_field_id] ) && $counter > $repeater_cols[$repeater_field_id] ){
					$repeater_cols[$repeater_field_id] = $counter;
				} else {
					$repeater_cols[$repeater_field_id] = $counter;
				}
			} else {
				$repeater_cols[$repeater_field_id] = 1;
			}
		}
	}

	$max_cols = 0;

	$filename = 'ARForms_'.$form_name.'_'. time() .'_0.csv';

	$wp_date_format = apply_filters('arfcsvdateformat', 'Y-m-d H:i:s');

	$charset = get_option('blog_charset');

	$to_encoding = $style_settings->csv_format;

    $entry_separator_id = get_option('arf_form_entry_separator');
    
    if($entry_separator_id == 'arf_comma'){
        $entry_separator = ',';
    }
    elseif($entry_separator_id == 'arf_semicolon'){
        $entry_separator = ';';
    }
    elseif($entry_separator_id == 'arf_pipe'){
        $entry_separator = '|';
    }

	header('Content-Description: File Transfer');
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header('Content-Type: text/csv; charset=' . $charset, true);
	header('Expires: '. gmdate("D, d M Y H:i:s", mktime(date('H')+2, date('i'), date('s'), date('m'), date('d'), date('Y'))) .' GMT');
	header('Last-Modified: '. gmdate('D, d M Y H:i:s') .' GMT');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');


	$field_order = arf_json_decode($form->options['arf_field_order'],true);
	$field_inner_order = arf_json_decode( $form->options['arf_inner_field_order'], true);
	$new_form_cols = array();


	asort($field_order);
	$hidden_fields = array();
	$hidden_field_ids = array();
	foreach ($field_order as $field_id => $order) {
	    if(is_int($field_id))
	    {
	        foreach ($form_cols as $field) {
	            if ($field_id == $field->id) {
	            	if( $field->type != 'section' ){
	            		if ( $field->type == 'html' && $field->field_options['enable_total'] != 1 ) {
	            			continue;
	            		}else{
	                		$new_form_cols[] = $field;
	                	}
	            	} else {
	            		if( !empty( $field_inner_order ) ){
		            		foreach( $field_inner_order[$field->id] as $inner_field_data ){
		            			$exploded_data = explode('|', $inner_field_data);
		            			$inner_field_id = $exploded_data[0];

		            			foreach( $form_cols as $ifield ){
		            				if( $ifield->id == $inner_field_id ){
		            					$new_form_cols[] = $ifield;
		            				}
		            			}
		            		}
	            		}
	            	}
	            } else if( $field->type == 'hidden' ){
	            	if( !in_array($field->id,$hidden_field_ids) ){
	        			$hidden_fields[] = $field;
	        			$hidden_field_ids[] = $field->id;
	            	}
	            }
	        }
	    }
	}

	if( count($hidden_fields) > 0 ){
		$new_form_cols = array_merge($new_form_cols,$hidden_fields);
	}


	$form_cols = $new_form_cols;


	echo '"ID"'.$entry_separator;
	foreach ($form_cols as $col){
		
		if( 'arf_repeater' == $col->type ){
			$col_id = $col->id;
			$get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT id,name,type,options,field_options FROM `".$MdlDb->fields."` WHERE field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%'", $col_id, $col_id ) );
			$inner_field_order = arf_json_decode($form->options['arf_inner_field_order'],true);
			$new_inner_form_cols = array();

		
			foreach( $inner_field_order[$col->id] as $in_field_id => $in_order ){

				$temp_explode = explode('|', $in_order);
				$temp_in_field_id = (int)$temp_explode[0];
				if( is_int( $temp_in_field_id ) ){

					foreach( $get_all_inner_fields as $inner_field ){
						if( $temp_in_field_id == $inner_field->id ){
							$new_inner_form_cols[] = $inner_field;
						}
					}
				}
			}

			$max_cols = $repeater_cols[$col->id];

			if( isset( $repeater_cols ) && $max_cols > 0 ){
				for( $mcol = 0; $mcol < $max_cols; $mcol++ ){
					foreach( $new_inner_form_cols as $inner_cols ){
						echo '"'. $inner_cols->name . '"'.$entry_separator;
					}
				}
			}

		} else {
			echo '"'. $arrecordhelper->encode_value(strip_tags($col->name), $charset, $to_encoding) .'"'.$entry_separator.'';
		}
	}



	echo '"'. addslashes(esc_html__('Timestamp', 'ARForms')) .'"'.$entry_separator.'"IP"'.$entry_separator.'"Key"'.$entry_separator.'"Country"'.$entry_separator.'"Browser"'.$entry_separator.'"Page URL"'.$entry_separator.'"Referrer URL"'."\n";


	foreach($entries as $entry){
		global $wpdb,$MdlDb;
		echo "\"{$entry->id}\"$entry_separator";
		$res_data = $wpdb->get_results( $wpdb->prepare('SELECT description,country, browser_info FROM '.$MdlDb->entries.' WHERE id = %d', $entry->id), 'ARRAY_A');
		$description = maybe_unserialize($res_data[0]['description']);
		$entry->page_url = isset($description['page_url']) ? $description['page_url'] : '';
		$entry->referrer = isset($description['http_referrer']) ? $description['http_referrer'] : '';
		$entry->country = $res_data[0]['country'];
		$arfrecord_browser = $arrecordcontroller->getBrowser($res_data[0]['browser_info']);
		$entry->browser = $arfrecord_browser['name'] . ' (Version: ' . $arfrecord_browser['version'] . ')';
		foreach ($form_cols as $col){
			if( $col->type == 'section' ){
				continue;
			}
			$field_value = isset($entry->metas[$col->id]) ? $entry->metas[$col->id] : "";
			if(!$field_value and $entry->attachment_id){
				$col->field_options = arf_json_decode($col->field_options, true);
			}

		    if ($col->type == 'file'){
				$old_entry_values = explode('|', $field_value);
				$new_field_value = array();
				
				foreach ($old_entry_values as $old_entry_val){
					$new_field_value[] = str_replace('thumbs/', '', wp_get_attachment_url($old_entry_val));
				}
				$new_field_value = implode('|', $new_field_value);
				$field_value = $new_field_value;
			}else if ($col->type == 'date'){
				$field_value = $arfieldhelper->get_date($field_value, $wp_date_format);
			} else if( 'arf_repeater' == $col->type ){
				$field_value = false;
				$col_id = $col->id;
				$get_all_inner_fields = $wpdb->get_results( $wpdb->prepare( "SELECT id,name,type,options,field_options FROM `".$MdlDb->fields."` WHERE field_options LIKE '%\"parent_field\":\"%d\"%' OR field_options LIKE '%\"parent_field\":%d%'", $col_id, $col_id ) );
				$inner_field_order = arf_json_decode($form->options['arf_inner_field_order'],true);
				$new_inner_form_cols = array();

				asort($inner_field_order);
				
				foreach( $inner_field_order[$col->id] as $in_field_id => $in_order ){
					$temp_explode = explode('|', $in_order);
					$temp_in_field_id = (int)$temp_explode[0];
					if( is_int( $temp_in_field_id ) ){
						foreach( $get_all_inner_fields as $inner_field ){
							if( $temp_in_field_id == $inner_field->id ){
								$new_inner_form_cols[] = $inner_field;
							}
						}
					}
				}
				$max_cols = $repeater_cols[$col->id];

				if( isset( $max_cols ) && $max_cols > 0 ){

					for( $mcol = 0; $mcol < $max_cols; $mcol++ ){
						foreach( $new_inner_form_cols as $inner_cols ){
							if( !isset( $entry->metas[$inner_cols->id]) ){
								echo '""' . $entry_separator;
							} else {
								$exploded_data = explode( '[ARF_JOIN]',$entry->metas[$inner_cols->id] );

								if( isset( $exploded_data[$mcol] ) ){
									if( 'checkbox' == $inner_cols->type ){
										$chk_explode = explode( '!|!', $exploded_data[$mcol] );
										$in_fopts = arf_json_decode( $inner_cols->field_options, true );
										$fopts = arf_json_decode( $inner_cols->options, true );
										if( isset( $in_fopts['separate_value'] ) && $in_fopts['separate_value'] == 1 ){
											$chk_vals = arf_json_decode( $chk_explode[0], true );
											$temp_value = "";
											echo "\"";
											foreach( $chk_vals as $k => $tmp_chk_val ){
												$val = $arfrecordmeta->find_value_in_options_with_separate_value( $tmp_chk_val, $fopts, $k );
												if( $val['value'] != '' ){
													$temp_value .= $val['value'] .'('.$val['label'].'),';
												}
											}
											echo rtrim( $temp_value ,',');
											echo "\"";
										} else {

											$chk_vals = arf_json_decode( $chk_explode[0], true );
											if( is_array( $chk_vals )){
												echo "\"".implode( ', ', $chk_vals )."\"";
											} else {
												echo "\"". $chk_vals ."\"";
											}
										}
									} else if( 'radio' == $inner_cols->type || 'select' == $inner_cols->type || 'arf_autocomplete' == $inner_cols->type ){
										$rdo_explode = explode( '!|!', $exploded_data[$mcol] );
										$in_fopts = arf_json_decode( $inner_cols->field_options, true );
										$fopts = arf_json_decode( $inner_cols->options, true );

										if( isset( $in_fopts['separate_value'] ) && $in_fopts['separate_value'] == 1 ){
											$chk_vals = arf_json_decode( $rdo_explode[0], true );
											$val = $arfrecordmeta->find_value_in_options_with_separate_value( $chk_vals[0], $fopts, 0 );
											if( $val['value'] != '' ){
												echo "\"". $val['value'] . '(' . $val['label'] . ')' . "\"";
											} else {
												echo "\"\"";
											}
										} else {
											echo "\"". $rdo_explode[0] . "\"";
										}
									} else {
										echo "\"".$exploded_data[$mcol]."\"";
									}
									echo $entry_separator;
								} else {
									echo '""' . $entry_separator;
								}
							}
						}
					}
				}
			} else if( $col->type == 'matrix' ){


				$field_rows = $col->field_options['rows'];
				$field_temp_val = '';
				$is_separate_value = (1 == $col->field_options['separate_value']) ? true : false;
				$total_rows = count( $field_rows );
				$rx = 0;
				foreach( $field_rows as $rk => $rv ){
					if( $is_separate_value ){
						$field_temp_val .= $rv . ': ';
						foreach( $col->field_options['options'] as $matrix_opts ){
							if( isset( $field_value[$rx] ) && $matrix_opts['value'] == $field_value[$rx] ){
								$field_temp_val .= $matrix_opts['label'] . ' ('.$field_value[$rx].')';
							}
						}
					} else {
						$field_temp_val .= $rv . ': ' . ( !empty( $field_value[$rx] ) ? $field_value[$rx] : '-' );
					}
					if( ( $rx + 1 )< $total_rows ){
						$field_temp_val .= "\n";
					}
					$rx++;
				}

				$field_value = $field_temp_val;
			} else if( 'checkbox' == $col->type ){

				if( isset( $col->field_options['separate_value'] ) && 1 == $col->field_options['separate_value'] ){
					$temp_field_val = '';

					foreach( $col->field_options['options'] as $fopt ){
						if( is_array( $field_value ) && in_array( $fopt['value'], $field_value ) ){
							$temp_field_val .= $fopt['label'] .' ('.$fopt['value'].'),';
						} else if( $fopt['value'] == $field_value ){
							$temp_field_val .= $fopt['label'] .' ('.$field_value.'),';
						}
					}
					$field_value = rtrim($temp_field_val,',');
				} else if( is_array( $field_value ) ) {
					$field_value = implode(', ', $field_value);
				} else {
					$field_value = $field_value;					
				}
			} else if( 'arf_multiselect' == $col->type ){
				if( isset( $col->field_options['separate_value'] ) && 1 == $col->field_options['separate_value'] ){
					$temp_field_val = '';
					foreach( $col->field_options['options'] as $fopt ){
						if(is_array( $field_value ) && in_array( $fopt['value'], $field_value) ){
							$temp_field_val .= $fopt['label'] .' ('.$fopt['value'].'),';
						} else if( $fopt['value'] == $field_value ) {
							$temp_field_val .= $fopt['label'] .' ('.$fopt['value'].'),';
						}
					}
					$field_value = rtrim($temp_field_val,',');
				}else if(is_array($field_value)){
					$field_value = implode(', ', $field_value);
				}else{
					$field_value = $field_value;
				}
			} else if( 'radio' == $col->type || 'select' == $col->type || 'arf_autocomplete' == $col->type ){
				if( isset( $col->field_options['separate_value'] ) && 1 == $col->field_options['separate_value'] ){
					$temp_field_val = '';

					foreach( $col->field_options['options'] as $fopt ){
						if( $fopt['value'] == $field_value ){
							$temp_field_val .= $fopt['label'] .' ('.$field_value.')';
						}
					}

					$field_value = $temp_field_val;
				}
			} else{
				$checked_values = arf_json_decode($field_value,true);

				$checked_values = apply_filters('arfcsvvalue', $checked_values, array('field' => $col));
				if (is_array($checked_values)){
						$field_value = implode(', ', $checked_values);
				}else{
					$field_value = $checked_values;
				}
				$field_value = $arrecordhelper->encode_value($field_value, $charset, $to_encoding);
				$field_value = str_replace('"', '""', stripslashes($field_value));  
			}
			if( $col->type != 'arf_repeater' ){
				if( $col->type != 'matrix' ){
					$field_value = str_replace(array("\r\n", "\r", "\n"), ' <br />', $field_value);
				}
				echo "\"$field_value\"$entry_separator";
			}
			unset($col);
			unset($field_value);
		}
		$formatted_date = date($wp_date_format, strtotime($entry->created_date));
		echo "\"{$formatted_date}\"$entry_separator";
		echo "\"{$entry->ip_address}\"$entry_separator";
		echo "\"{$entry->entry_key}\"$entry_separator";
		echo "\"{$entry->country}\"$entry_separator";
		echo "\"{$entry->browser}\"$entry_separator";
		echo "\"{$entry->page_url}\"$entry_separator";
		echo "\"{$entry->referrer}\"$entry_separator\n";
		unset($entry);
	}

?>