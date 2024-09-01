<?php

class arfmigratecontroller{

	function __construct(){

		add_action( 'arf_migrate_lite_data', array( $this, 'arf_migrate_lite_data_callback' ) );

		if ( ! function_exists( 'is_plugin_active' ) ) {
            include ABSPATH . '/wp-admin/includes/plugin.php';
        }

		if( file_exists(WP_PLUGIN_DIR.'/arforms-form-builder/arforms-form-builder.php')  && !is_plugin_active( 'arforms-form-builder/arforms-form-builder.php' ) ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'arflite_load_uninstallation_assets' ) );

            add_filter( 'plugin_action_links', array( $this, 'arf_display_uninstall_note' ), 10, 2 );

            add_action( 'admin_footer', array( $this, 'arflite_uninstallation_popup' ), 1 );

        	add_filter( 'arf_modify_where_clause', array( $this, 'arf_add_lite_condition_in_clause'), 10, 2 );

	        add_filter( 'arf_modify_where_placeholder', array( $this, 'arf_add_lite_condition_in_placeholder'), 10, 3 );

	        add_filter( 'arfpredisplayform', array( $this, 'arf_check_lite_form' ),1 );
        }

	}

	function arflite_load_uninstallation_assets(){
		global $pagenow, $arfversion;

		if( 'plugins.php' == $pagenow ){
			wp_register_script( 'arflite-uninstallation', ARFURL . '/js/arflite_uninstallation.js', array('jquery'), $arfversion );
			wp_enqueue_script( 'arflite-uninstallation' );

			wp_register_style( 'arflite-uninstallation', ARFURL . '/css/arflite_uninstallation.css', array(), $arfversion );
			wp_enqueue_style( 'arflite-uninstallation' );
		}
	}

	function arf_migrate_lite_data_callback(){

		global $wpdb, $MdlDb, $armainhelper;

		$lite_form_table = $wpdb->prefix.'arflite_forms';
		$lite_form_fields = $wpdb->prefix.'arflite_fields';
		$lite_form_entry = $wpdb->prefix.'arflite_entries';
		$lite_form_entry_metas = $wpdb->prefix.'arflite_entry_values';

		$is_lite_form_table = $wpdb->query( "SHOW TABLES LIKE '" . $lite_form_table . "' ");

		if( $is_lite_form_table > 0 ){

			$all_lite_forms = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `' . $lite_form_table . '` WHERE status = %s', 'published' ) );

			if( !empty( $all_lite_forms ) ){

				if( !isset($is_prefix_suffix_enable)){
				    $is_prefix_suffix_enable = false;
				}
				if( !isset($is_checkbox_img_enable)){
				    $is_checkbox_img_enable = false;
				}
				if( !isset($is_radio_img_enable)){
				    $is_radio_img_enable = false;
				}
				if( !isset($is_font_awesome) ){
					$is_font_awesome = false;
				}
				if( !isset($is_tooltip) ){
					$is_tooltip = 0;
				}
				if( !isset($is_input_mask) ){
					$is_input_mask = 0;
				}
				if( !isset($checkbox_img_field_arr) ){
					$checkbox_img_field_arr = array();
				}
				if( !isset($radio_img_field_arr) ){
					$radio_img_field_arr = array();
				}

				foreach( $all_lite_forms as $lite_frm ){
					$loaded_field = array();
					$lite_form_id = $lite_frm->id;

					$new_key = $lite_frm->form_key;

					$new_form_key = $armainhelper->get_unique_key( $new_key, $MdlDb->forms, 'form_key' );

					$lite_form_name = $lite_frm->name;
					$lite_form_desc = $lite_frm->description;
					$lite_form_template = 0;
					$lite_form_status = 'published';
					$lite_form_opts = maybe_unserialize( $lite_frm->options );

					$lite_form_css = maybe_unserialize( $lite_frm->form_css );

					$lite_form_css['arfsectionpaddingsetting_1'] = 20;
					$lite_form_css['arfsectionpaddingsetting_2'] = 0;
					$lite_form_css['arfsectionpaddingsetting_3'] = 20;
					$lite_form_css['arfsectionpaddingsetting_4'] = 20;

					$lite_temp_fields = maybe_unserialize( $lite_frm->temp_fields );

					$wpdb->insert(
						$MdlDb->forms,
						array(
							'form_key' => $new_form_key,
							'name' => $lite_form_name,
							'description' => $lite_form_desc,
							'is_template' => 0,
							'status' => $lite_form_status,
							'options' => maybe_serialize( $lite_form_opts ),
							'created_date' => current_time('mysql', 1),
							'form_css' => maybe_serialize( $lite_form_css ),
							'temp_fields' => maybe_serialize( $lite_temp_fields ),
							'arf_is_lite_form' => 1,
							'arf_lite_form_id' => $lite_form_id
						)
					);

					$migrated_form_id = $wpdb->insert_id;

					$form_id = $migrated_form_id;

					$new_values = $lite_form_css;

					$use_saved = true;

				    $arfssl = (is_ssl()) ? 1 : 0;

					$res1  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 3 ), 'ARRAY_A' );
					$res2  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 1 ), 'ARRAY_A' );
					$res3  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 4 ), 'ARRAY_A' );
					$res4  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 5 ), 'ARRAY_A' );
					$res5  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 6 ), 'ARRAY_A' );
					$res6  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 8 ), 'ARRAY_A' );
					$res7  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 9 ), 'ARRAY_A' );
					$res11 = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 10 ), 'ARRAY_A' );
					$res14 = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 14 ), 'ARRAY_A' );
					$res15 = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 15 ), 'ARRAY_A' );
					$res16 = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 16 ), 'ARRAY_A' );
					$res17 = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 17 ), 'ARRAY_A' );
					$res18 = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 18 ), 'ARRAY_A' );

				    $aweber_arr['enable'] = '';
				    $aweber_arr['is_global'] = 1;
				    $aweber_arr['type'] = '';
				    $aweber_arr['type_val'] = '';

					$aweber = maybe_serialize($aweber_arr);

				    $mailchimp_arr['enable'] = '';
				    $mailchimp_arr['is_global'] = 1;
				    $mailchimp_arr['type'] = '';
				    $mailchimp_arr['type_val'] = '';

					$mailchimp = maybe_serialize($mailchimp_arr);
					
				    $madmimi_arr['enable'] ='';
				    $madmimi_arr['is_global'] = 1;
				    $madmimi_arr['type'] = '';

					$madmimi = maybe_serialize($madmimi_arr);

				    $getresponse_arr['enable'] = '';
				    $getresponse_arr['is_global'] = 1;
				    $getresponse_arr['type'] = '';
				    $getresponse_arr['type_val'] = '';

					$getresponse = maybe_serialize($getresponse_arr);

				    $gvo_arr['enable'] = '';
				    $gvo_arr['is_global'] = 1;
				    $gvo_arr['type'] = '';
				    $gvo_arr['type_val'] = '';

					$gvo = maybe_serialize($gvo_arr);

				    $ebizac_arr['enable'] ='';
				    $ebizac_arr['is_global'] = 1;
				    $ebizac_arr['type'] = '';
				    $ebizac_arr['type_val'] = '';

					$ebizac = maybe_serialize($ebizac_arr);

				    $icontact_arr['enable'] ='';
				    $icontact_arr['is_global'] = 1;
				    $icontact_arr['type'] = '';
				    $icontact_arr['type_val'] = '';

					$icontact = maybe_serialize($icontact_arr);

				    $constant_contact_arr['enable'] = '';
				    $constant_contact_arr['is_global'] = 1;
				    $constant_contact_arr['type'] ='';
				    $constant_contact_arr['type_val'] = '';

					$constant_contact = maybe_serialize($constant_contact_arr);

				    $mailerlite_arr['enable'] ='';
				    $mailerlite_arr['is_global'] = 1;
				    $mailerlite_arr['type'] ='';

					$mailerlite = maybe_serialize($mailerlite_arr);

				    $hubspot_arr['enable'] ='';
				    $hubspot_arr['is_global'] = 1;
				    $hubspot_arr['type'] ='';

					$hubspot = maybe_serialize($hubspot_arr);

					
				    $convertkit_arr['enable'] = '';
				    $convertkit_arr['is_global'] = 1;
				    $convertkit_arr['type'] ='';

					$convertkit = maybe_serialize($convertkit_arr);

				    $sendinblue_arr['enable'] = '';
				    $sendinblue_arr['is_global'] = 1;
				    $sendinblue_arr['type'] = '';

					$sendinblue = maybe_serialize($sendinblue_arr);

				    $drip_arr['enable'] = '';
				    $drip_arr['is_global'] = 1;
				    $drip_arr['type'] = '';

					$drip = maybe_serialize( $drip_arr );

					$frm_id = $migrated_form_id;

					$wpdb->insert(
						$MdlDb->ar,
						array(
							'aweber' 			=> $aweber,
							'mailchimp' 		=> $mailchimp,
							'getresponse' 		=> $getresponse,
							'gvo' 				=> $gvo,
							'ebizac' 			=> $ebizac,
							'madmimi' 			=> $madmimi,
							'icontact' 			=> $icontact,
							'constant_contact' 	=> $constant_contact,
							'enable_ar' 		=> maybe_serialize( array() ),
							'frm_id' 			=> $frm_id
						)
					);

					$all_list_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . $lite_form_fields . "` WHERE form_id = %d", $lite_form_id ) );

					$old_fields_order = arf_json_decode( $lite_form_opts['arf_field_order'], true );

					$arf_field_id_update = array();

					if( !empty( $all_list_fields ) ){
						foreach( $all_list_fields as $field_data ){
							$lite_field_id = $field_data->id;

							$lite_field_key = $field_data->field_key;

							$new_field_key = $armainhelper->get_unique_key( $lite_field_key, $MdlDb->fields, 'field_key' );

							$lite_field_name = $field_data->name;
							$lite_field_type = $field_data->type;
							$loaded_field[] = $lite_field_type;
							$lite_field_opts = $field_data->options;
							$lite_field_required = $field_data->required;
							$lite_field_fopts = $field_data->field_options;
							
							$lite_field_created_date = current_time( 'mysql', 1 );
							$lite_field_opt_order = $field_data->option_order;

							if ((isset($lite_field_opts['enable_arf_prefix']) && $lite_field_opts['enable_arf_prefix'] == 1) || (isset($lite_field_opts['enable_arf_suffix']) && $lite_field_opts['enable_arf_suffix'] == 1)) {
								$is_font_awesome = 1;
								$is_prefix_suffix_enable = true;
							}
		
							if (isset($lite_field_opts['tooltip_text']) && $lite_field_opts['tooltip_text'] != '') {
								$is_tooltip = 1;
							}
		
							if($lite_field_type == 'checkbox' && (isset($lite_field_opts['use_image']) && $lite_field_opts['use_image'] == 1)) {
								$is_font_awesome = 1;
								$is_checkbox_img_enable = true;
								$checkbox_img_field_arr[] = $field_data;
							}
		
							if($lite_field_type == 'radio' && (isset($lite_field_opts['use_image']) && $lite_field_opts['use_image'] == 1)) {
								$is_font_awesome = 1;
								$is_radio_img_enable = true;
								$radio_img_field_arr[] = $field_data;
							}
		
							if ($lite_field_type == 'phone' && ( isset($lite_field_opts['phone_validation']) && $lite_field_opts['phone_validation'] != 'international' )) {
								$is_input_mask = 1;
							}
		
							if( $lite_field_type == 'phone' && ( isset($lite_field_opts['phonetype']) && $lite_field_opts['phonetype'] == 1 ) ){
								$is_input_mask = 1;
							}

							$wpdb->insert(
								$MdlDb->fields,
								array(
									'field_key' => $new_field_key,
									'name' => $lite_field_name,
									'type' => $lite_field_type,
									'options' => $lite_field_opts,
									'required' => $lite_field_required,
									'field_options' => $lite_field_fopts,
									'form_id' => $migrated_form_id,
									'created_date' => $lite_field_created_date,
									'option_order' => $lite_field_opt_order
								)
							);

							$new_field_id = $wpdb->insert_id;

							$arf_field_id_update[ $lite_field_id ] = $new_field_id;
						}
					}

					$updated_field_order = array();
					if( !empty( $arf_field_id_update ) && !empty( $old_fields_order ) ){

						foreach( $old_fields_order as $old_field_id => $field_ord ){

							foreach( $arf_field_id_update as $old_fid => $new_fid ){
								if( $old_field_id == $old_fid ){
									$updated_field_order[ $new_fid ] = $field_ord;
								}
							}
						}
					}

					if( !empty( $updated_field_order ) ){

						$lite_form_opts['arf_field_order'] = json_encode( $updated_field_order );

						$wpdb->update(
							$MdlDb->forms,
							array(
								'options' => maybe_serialize( $lite_form_opts )
							),
							array(
								'id' => $migrated_form_id
							)
						);
					}
					$css_common_filename = FORMPATH . '/core/css_create_common.php';
						$css_rtl_filename = FORMPATH . '/core/css_create_rtl.php';
					if( 'standard' == $lite_form_css['arfinputstyle'] || 'rounded' == $lite_form_css['arfinputstyle'] ){
						$filename = FORMPATH . '/core/css_create_main.php';

					    $wp_upload_dir = wp_upload_dir();

					    $target_path = $wp_upload_dir['basedir'] . '/arforms/maincss';

					    $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";

					    $css .= "\n";

					    ob_start();

					    include $filename;
						include $css_common_filename;
						if( is_rtl() ){
							include $css_rtl_filename;
						}

					    $css .= ob_get_contents();

					    ob_end_clean();

					    $css .= "\n " . $warn;

					    $css_file = $target_path . '/maincss_' . $migrated_form_id . '.css';

					    WP_Filesystem();
					    global $wp_filesystem;
					    $css = str_replace('##', '#', $css);
					    $wp_filesystem->put_contents($css_file, $css, 0777);
					    wp_cache_delete($migrated_form_id, 'arfform');
					}

					if( 'material' == $lite_form_css['arfinputstyle'] ){
						$filename1 = FORMPATH . '/core/css_create_materialize.php';
					    $css1 = $warn1 = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
					    $css1 .= "\n";
					    ob_start();
					    include $filename1;
						include $css_common_filename;
						if( is_rtl() ){
							include $css_rtl_filename;
						}
					    $css1 .= ob_get_contents();
					    ob_end_clean();
					    $css1 .= "\n " . $warn1;
					    $css_file1 = $target_path . '/maincss_materialize_' . $migrated_form_id . '.css';
					    WP_Filesystem();
					    $css1 = str_replace('##', '#', $css1);
					    $wp_filesystem->put_contents($css_file1, $css1, 0777);
					    wp_cache_delete($migrated_form_id, 'arfform');
					}

					$all_form_entries = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . $lite_form_entry . "` WHERE form_id = %d", $lite_form_id ) );

					$updated_entry_ids = array();
					if( !empty( $all_form_entries ) ){
						foreach( $all_form_entries as $form_entry ){
							$entry_id = $form_entry->id;
							$entry_key = $form_entry->entry_key;
							$new_entry_key = $armainhelper->get_unique_key( $entry_key, $MdlDb->entries, 'entry_key' );
							$entry_name = $form_entry->name;
							$entry_description = $form_entry->description;
							$entry_ip = $form_entry->ip_address;
							$entry_country  = $form_entry->country;
							$entry_browser_info = $form_entry->browser_info;
							$entry_form_id = $migrated_form_id;
							$entry_attachment_id = $form_entry->attachment_id;
							$entry_user_id = $form_entry->user_id;
							$entry_created_date = current_time( 'mysql', 1 );

							$wpdb->insert(
								$MdlDb->entries,
								array(
									'entry_key' => $new_entry_key,
									'name' => $entry_name,
									'description' => $entry_description,
									'ip_address' => $entry_ip,
									'country' => $entry_country,
									'browser_info' => $entry_browser_info,
									'form_id' => $entry_form_id,
									'attachment_id' => $entry_attachment_id,
									'user_id' => $entry_user_id,
									'created_date' => $entry_created_date,
								)
							);

							$new_entry_id = $wpdb->insert_id;

							$updated_entry_ids[ $entry_id ] = $new_entry_id;
						}
					}

					if( !empty( $updated_entry_ids ) ){
						foreach( $updated_entry_ids as $old_entry_id => $new_entry_id ){
							$entry_metas = $wpdb->get_results( $wpdb->prepare( "SELECT * from `" . $lite_form_entry_metas . "` WHERE entry_id = %d", $old_entry_id ) );

							if( !empty( $entry_metas ) ){
								foreach( $entry_metas as $entry_obj ){
									$entry_value = $entry_obj->entry_value;
									$entry_id = $entry_obj->entry_id;
									$entry_field_id = $entry_obj->field_id;

									if( $entry_field_id == 0 ){
										$new_entry_field = 0;
									} else {
										if( preg_match( '/\-[\d]+/', $entry_field_id ) ){
											$new_entry_field = '-'.$arf_field_id_update[ abs( $entry_field_id ) ];
										} else {
											$new_entry_field = $arf_field_id_update[ $entry_field_id ];
										}
									}

									$wpdb->insert(
										$MdlDb->entry_metas,
										array(
											'entry_value' => $entry_value,
											'field_id' => $new_entry_field,
											'entry_id' => $new_entry_id
										)
									);
								}
							}
						}
					}

				}

			}

		}

	}

	function arf_display_uninstall_note( $links, $file ){

		if( 'arforms-form-builder/arforms-form-builder.php' == $file ){
			if( isset( $links['delete'] ) ){
				$uninstall_link = $links['delete'];

				$uninstall_link   = str_replace(
					'<a ',
					'<span class="arflite-uninstall-form-wrapper">
                         <span class="arflite-uninstall-form" id="arflite-uninstall-form-' . esc_attr( 'ARFormslite' ) . '"></span>
                     </span><a id="arflite-uninstall-link-' . esc_attr( 'ARFormslite' ) . '" ',
					$uninstall_link
				);

				$uninstall_link   = str_replace(
					'class="delete"',
					'class="arflite_delete_note"',
					$uninstall_link
				);

				$links['delete'] = $uninstall_link;
			}
		}

		return $links;

	}

	function arflite_uninstallation_popup(){
		global $pagenow;

		$uninstall_popup = '';
		if( 'plugins.php' == $pagenow ){

			$uninstall_popup .= '<div class="arflite_uninstallation_note">';

				$uninstall_popup .= '<input type="hidden" data-ajax-nonce="'.wp_create_nonce('_ajax_nonce').'" id="wp_admin_url" value="'.admin_url().'" />';

				$uninstall_popup .= '<span class="arflite_uninstallation_popup_close"></span>';

				$uninstall_popup .= '<div class="arflite_uninstallation_popup_head">';
					$uninstall_popup .= '<span class="arflite_uninstallation_warning_icon"></span>';
					$uninstall_popup .= '<h4>'. esc_html__('Deleting ARForms Lite will remove the forms and entries permanently.','ARForms').' <br/> '.esc_html__('Are you sure you want to continue?','ARForms').'</h4>';
				$uninstall_popup .= '</div>';

				$uninstall_popup .= '<div class="arflite_uninstall_popup_footer">';
					$uninstall_popup .= '<button type="button" class="arflite_uninstallation_button do_delete" id="arflite_confirm_delete">Yes</button>';
					$uninstall_popup .= '<button type="button" class="arflite_uninstallation_button do_cancel" id="arflite_cancel_delete">No</button>';
				$uninstall_popup .= '</div>';

			$uninstall_popup .= '</div>';

			echo '<div class="arflite_uninstallation_note_wrapper">'.$uninstall_popup.'</div>';
		}
	}

	function arf_add_lite_condition_in_clause( $where_clause, $table_name ){

        global $MdlDb, $modify_param;
        $modify_param = false;
        $pro_table_name = $MdlDb->forms;

        $where_blank = false;

        if( empty( $where_clause ) ){
            $where_blank = true;
        }


        if( preg_match('/'.$pro_table_name.'/', $table_name ) ){
            $has_alias = preg_match( '/' . $pro_table_name . '(\s+)[^WHERE]/', $table_name );
            if( $has_alias ){

                $pattern = '/('.$pro_table_name.')(\s|AS)+(.*[^WHERE])/';

                preg_match( $pattern, $table_name, $matches );
                
                if( !empty( $matches[3] ) ){

                	if( preg_match( '/(LEFT JOIN|RIGHT JOIN|INNER JOIN|OUTER JOIN|JOIN)/', $matches[3], $match1 ) ){
                		$delimeter = !empty( $match1[0] ) ? trim( $match1[0] ) : '';

                		if( '' != $delimeter ){
                			$splitted_data = explode( $delimeter,$matches[3] );
                			if( !empty( $splitted_data[0] ) ){
                				$table_alias = trim($splitted_data[0]);
                			}
                		}
                	} else {
                    	$table_alias = $matches[3];
                	}
                    if( preg_match( '/(.*)(\sON\s)(.*)/', $table_alias, $alias_match ) ){
                        $table_alias = $alias_match[1];
                    }
                    if( $where_blank ){
                        $where_clause .= ' WHERE '.$table_alias.'.arf_is_lite_form = %d';
                    } else {
                        $where_clause .= ' AND '.$table_alias.'.arf_is_lite_form = %d';
                    }
                    $modify_param = true;
                
                }
            } else {
                $modify_param = true;
                if( $where_blank ){
                    $where_clause .= ' WHERE arf_is_lite_form = %d';
                } else {
                    $where_clause .= ' AND arf_is_lite_form = %d';
                }
            }
        }

        return $where_clause;
    }

    function arf_add_lite_condition_in_placeholder( $where_params, $table_name, $where_clause ){

        global $modify_param;

        if( true == $modify_param ){
            
            $param_count = count( $where_params );

            preg_match_all( '/(%d|%s|%f)/', $where_clause, $all_matches );

            $matches_count = !empty( $all_matches[0] ) ? count( $all_matches[0] ) : 0;
            
            preg_match_all( '/(([\w\.]+)(\s)(.*?)(\s)(|AND|OR))+/', str_replace('WHERE', '', trim($where_clause) ), $clause_matches );

            if( $param_count < $matches_count ){
                foreach( $all_matches[0] as $k => $val ){
                    if( !isset( $where_params[$k] ) ){
                        if( '%d' == $val ){
                            if( !empty( $clause_matches[2] ) && !empty( $clause_matches[2][$k] ) ){
                                if( preg_match( '/arf_is_lite_form/', $clause_matches[2][$k] ) ){
                                    $where_params[$k] = 0;
                                    $modify_param = false;
                                } else {
                                    $where_params[$k] = 0;
                                }
                            } else {
                                $where_params[$k] = 0;
                            }
                        }
                    }
                }
            }
        }

        return $where_params;
    }

    function arf_check_lite_form( $form ){
		
    	if( isset( $form->arf_is_lite_form ) && 1 == $form->arf_is_lite_form ){
    		return false;
    	}

    	return $form;
    }

}