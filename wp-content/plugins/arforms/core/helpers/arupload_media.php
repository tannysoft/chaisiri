<?php
include(FORMPATH.'/js/filedrag/simple_image.php');
		


function media_handle_upload_custom_v4($file_id, $attach_id,$form_id, $post_data = array(), $overrides = array('test_form' => false)) {
	
	$time = current_time('mysql');
	if($post = get_post($attach_id)) {
		if(substr($post->post_date, 0, 4) > 0)
			$time = $post->post_date;
	}

	$files = $_FILES[$file_id];
	global $wpdb, $MdlDb; $arformhelper;
	$file_id_new = str_replace('file','',$file_id);
	$res_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$MdlDb->fields." WHERE id = %d", $file_id_new));
	$field_options_new = maybe_unserialize($res_data[0]->field_options);
	$field_types = get_allowed_mime_types();
	$field_types['exe'] = '';
	unset($field_types['exe']);
	if (! is_array($field_options_new)) {
		$field_options_new = json_decode($field_options_new, true);
	}


	global $arformhelper, $maincontroller, $wpdb, $MdlDb;

	$ftypes_array = (isset($field_options_new['restrict']) && $field_options_new['restrict']==1) ? (($field_options_new['ftypes']=='') ? $field_types : $field_options_new['ftypes']) : $field_types;
	$new_media_value_ids = array();

	$maxupload_limit = ( !empty( $field_options_new['arf_is_multiple_file'] ) && isset($field_options_new['arf_max_file_upld'] ) ) ? $field_options_new['arf_max_file_upld'] : '';

	
	foreach ($files['name'] as $fkey => $fvalue) {
		if ($maxupload_limit != '') {
			if (count($files) > 1 && $fkey < $maxupload_limit) {
				if($files['name'][$fkey]) {

					$file_data = array(
						'name'     => $files['name'][$fkey],
						'type'     => $files['type'][$fkey],
						'tmp_name' => $files['tmp_name'][$fkey],
						'error'    => $files['error'][$fkey],
						'size'     => $files['size'][$fkey]
					);

					$fn = $form_id."_".$file_id_new."_".time()."_".$file_data['name'];

					$upload_error_handler = 'wp_handle_upload_error';

					$arfilecontroller = new arfilecontroller( $file_data, false );

					if( !$arfilecontroller ){
						return call_user_func( $upload_error_handler, $file_data, addslashes( esc_html__( 'Please select file to upload', 'ARForms' ) ) ); 
					}

					$maincontroller->arf_start_session(true);

		            $field_id = $file_id_new;

		            $arfilecontroller->check_cap = false;

		            $arfilecontroller->check_nonce = false;

		            $arfilecontroller->check_only_image = false;

	            	$specific_files = array();
	            	$field_options = $field_options_new;
	            	if($field_options['restrict'] != 0 ){
	                    $field_types = $field_options['ftypes'];
	                    foreach($field_types as $key => $value){
	                        if($value != '0' ){
	                            array_push($specific_files,str_replace('ftypes_','',$key));
	                        }
	                    }
	                }

	                $file_size_opt = $field_options['max_fileuploading_size'];

	                if( 'auto' == $file_size_opt || empty( $file_size_opt )){
	                    $file_size_opt = ini_get('upload_max_filesize');
	                } else {
	                    $file_size_opt .= 'MB';
	                }



	                $all_files = false;
	                if( empty( $specific_files ) ){
	                    $all_files = true;
	                }

	                $is_preview = (isset($_REQUEST['is_preview']) && $_REQUEST['is_preview'] != '' ) ? $_REQUEST['is_preview']: 0;

	                $allowed_types = array();

	                foreach( $specific_files as $fext ){
	                    if( empty( trim( $fext ) ) ){
	                        continue;
	                    }
	                    if( preg_match( '/(\|)/', $fext ) ){
	                        $fext_ = explode( '|', trim( $fext ) );
	                        $allowed_types = array_merge( $allowed_types, $fext_ );
	                    } else {
	                        $allowed_types[] = trim( $fext );
	                    }
	                }

	                $arfilecontroller->check_specific_ext = true;
	                $arfilecontroller->allowed_ext = $allowed_types;

	                $arfilecontroller->field_error_msg = $field_options['invalid'];
	                $arfilecontroller->field_size_error_msg = $field_options['invalid_file_size'];

	                $arfilecontroller->check_file_size = true;
	                $arfilecontroller->max_file_size = $file_size_opt;

	                global $arformhelper;
	                $movable = $arformhelper->manage_uploaded_file_path($form_id);

	                if( $movable['status'] ){

	                    $destination = $movable['path'] . $fn;

	                    $arfilecontroller->generate_thumb = true;
	                    $arfilecontroller->thumb_path = $movable['path'] . 'thumbs/' . $fn;

	                    $upload_file = $arfilecontroller->arf_process_upload( $destination );

	                    if( ! $upload_file ){
	                        echo '<p class="error_upload_security">' . $arfilecontroller->error_message .'</p>';
	                        die;
	                    }

	                    $image = $fn;

						$title = $fn;

					    $full_image_name = pathinfo($image);

					    $post_title = preg_replace( '/\.[^.]+$/', '', $full_image_name['filename'] );

					    $image_url = $movable['url'] . $image;

					    $image_path = $movable['path'] . $image;

					    $uploaded_file_data = wp_check_filetype( $full_image_name['basename'] );

					    $post_mime_type = $uploaded_file_data['type'];

					    $attachment_args = array(
					        'guid' => $image_path,
					        'post_mime_type' => $post_mime_type,
					        'post_title' => sanitize_text_field(  $post_title ),
					        'post_content' => '',
					        'post_status' => 'inherit'
					    );

					    $upload_id = wp_insert_attachment(
					        $attachment_args,
					        $image_url
					    );

					    require_once( ABSPATH . 'wp-admin/includes/image.php' );
					    require_once( ABSPATH . 'wp-admin/includes/media.php' );

					    $before_meta_data = get_post_meta( $upload_id, '_wp_attached_file', true );

					    wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $image_path ) );
					    
					    $after_meta_data = get_post_meta( $upload_id, '_wp_attached_file', true );

					    if( $before_meta_data != $after_meta_data ){
					        update_post_meta( $upload_id, '_wp_attached_file', $before_meta_data, $after_meta_data );
					    }

					    $new_media_value_ids[] = $upload_id;

					    $post_meta_id = update_post_meta( $upload_id, 'arf_uploaded_file', 'arforms' );

	                }
				}
			}
		}else{
			

			if( $files['name'][$fkey] ){

				$file_data = array(
					'name'     => $files['name'][$fkey],
					'type'     => $files['type'][$fkey],
					'tmp_name' => $files['tmp_name'][$fkey],
					'error'    => $files['error'][$fkey],
					'size'     => $files['size'][$fkey]
				);

				$fn = $form_id."_".$file_id_new."_".time()."_".$file_data['name'];

				$upload_error_handler = 'wp_handle_upload_error';

				$arfilecontroller = new arfilecontroller( $file_data, false );

				if( !$arfilecontroller ){
					return call_user_func( $upload_error_handler, $file_data, addslashes( esc_html__( 'Please select file to upload', 'ARForms' ) ) ); 
				}

				$maincontroller->arf_start_session(true);

	            $field_id = $file_id_new;

	            $arfilecontroller->check_cap = false;

	            $arfilecontroller->check_nonce = false;

	            $arfilecontroller->check_only_image = false;

            	$specific_files = array();
            	$field_options = $field_options_new;
            	if($field_options['restrict'] != 0 ){
                    $field_types = $field_options['ftypes'];
                    foreach($field_types as $key => $value){
                        if($value != '0' ){
                            array_push($specific_files,str_replace('ftypes_','',$key));
                        }
                    }
                }

                $file_size_opt = $field_options['max_fileuploading_size'];

                if( 'auto' == $file_size_opt || empty( $file_size_opt )){
                    $file_size_opt = ini_get('upload_max_filesize');
                } else {
                    $file_size_opt .= 'MB';
                }

                $all_files = false;
                if( empty( $specific_files ) ){
                    $all_files = true;
                }

                $is_preview = (isset($_REQUEST['is_preview']) && $_REQUEST['is_preview'] != '' ) ? $_REQUEST['is_preview']: 0;

                $allowed_types = array();

                foreach( $specific_files as $fext ){
                    if( empty( trim( $fext ) ) ){
                        continue;
                    }
                    if( preg_match( '/(\|)/', $fext ) ){
                        $fext_ = explode( '|', trim( $fext ) );
                        $allowed_types = array_merge( $allowed_types, $fext_ );
                    } else {
                        $allowed_types[] = trim( $fext );
                    }
                }

                $arfilecontroller->check_specific_ext = (true == $all_files) ? false : true;
                $arfilecontroller->allowed_ext = $allowed_types;

                $arfilecontroller->field_error_msg = $field_options['invalid'];
                $arfilecontroller->field_size_error_msg = $field_options['invalid_file_size'];

                $arfilecontroller->check_file_size = true;
                $arfilecontroller->max_file_size = $file_size_opt;

                global $arformhelper;
                $movable = $arformhelper->manage_uploaded_file_path($form_id);

                if( $movable['status'] ){

                    $destination = $movable['path'] . $fn;

                    $arfilecontroller->generate_thumb = true;
                    $arfilecontroller->thumb_path = $movable['path'] . 'thumbs/' . $fn;

                    $upload_file = $arfilecontroller->arf_process_upload( $destination );

                    if( ! $upload_file ){
                        echo '<p class="error_upload_security">' . $arfilecontroller->error_message .'</p>';
                        die;
                    }

                    $image = $fn;

					$title = $fn;

				    $full_image_name = pathinfo($image);

				    $post_title = preg_replace( '/\.[^.]+$/', '', $full_image_name['filename'] );

				    $image_url = $movable['url'] . $image;
				    
				    $image_path = $movable['path'] . $image;

				    $uploaded_file_data = wp_check_filetype( $full_image_name['basename'] );

				    $post_mime_type = $uploaded_file_data['type'];

				    $attachment_args = array(
				        'guid' => $image_path,
				        'post_mime_type' => $post_mime_type,
				        'post_title' => sanitize_text_field(  $post_title ),
				        'post_content' => '',
				        'post_status' => 'inherit'
				    );

				    $upload_id = wp_insert_attachment(
				        $attachment_args,
				        $image_url
				    );

				    require_once( ABSPATH . 'wp-admin/includes/image.php' );
				    require_once( ABSPATH . 'wp-admin/includes/media.php' );

				    $before_meta_data = get_post_meta( $upload_id, '_wp_attached_file', true );

				    wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $image_path ) );
				    
				    $after_meta_data = get_post_meta( $upload_id, '_wp_attached_file', true );

				    if( $before_meta_data != $after_meta_data ){
				        update_post_meta( $upload_id, '_wp_attached_file', $before_meta_data, $after_meta_data );
				    }

				    $new_media_value_ids[] = $upload_id;

				    $post_meta_id = update_post_meta( $upload_id, 'arf_uploaded_file', 'arforms' );

                }
			}
			
		}
	}
	
	$new_media_value_ids = implode('|', $new_media_value_ids);
	return $new_media_value_ids;
}



function wp_handle_upload_custom(&$file, $overrides = false, $time = null, $form_id = null, $ftypes_array = array(), $file_id_new = null) {
	global $maincontroller;
	if(!function_exists('wp_handle_upload_error')) {
		function wp_handle_upload_error(&$file, $message) {
			return array('error'=>$message);
		}
	}
	
	$file = apply_filters('wp_handle_upload_prefilter', $file);
	$upload_error_handler = 'wp_handle_upload_error';
	
	if(isset($file['error']) && !is_numeric($file['error']) && $file['error']) {
		return $upload_error_handler($file, $file['error']);
	}
	
	$unique_filename_callback = null;
	$action = 'wp_handle_upload';
	$upload_error_strings = array(false,
		addslashes(esc_html__("The uploaded file exceeds the upload_max_filesize directive in php.ini.","ARForms")),
		addslashes(esc_html__("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.","ARForms")),
		addslashes(esc_html__("The uploaded file was only partially uploaded.","ARForms")),
		addslashes(esc_html__("No file was uploaded.","ARForms")),
		'',
		addslashes(esc_html__("Missing a temporary folder.","ARForms")),
		addslashes(esc_html__("Failed to write file to disk.","ARForms")),
		addslashes(esc_html__("File upload stopped by extension.","ARForms"))
	);
	
	$test_form = true;
	$test_size = true;
	$test_upload = true;
	$test_type = true;
	$mimes = false;
	
	if(is_array($overrides)) {
		extract($overrides, EXTR_OVERWRITE);
	}
	
	if($test_form && (!isset($_POST['action']) || ($_POST['action'] != $action))) {
		return call_user_func($upload_error_handler, $file, addslashes(esc_html__('Invalid form submission.','ARForms')));
	}
	
	if($file['error'] > 0) {
		return call_user_func($upload_error_handler, $file, $upload_error_strings[$file['error']]);
	}
	
	if($test_size && !($file['size'] > 0)) {
		if(is_multisite()) {
			$error_msg = addslashes(esc_html__('File is empty. Please upload something more substantial.','ARForms'));
		}
		else {
			$error_msg = addslashes(esc_html__('File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.','ARForms'));
		}

		return call_user_func($upload_error_handler, $file, $error_msg);
	}
	
	if($test_upload && ! @ is_uploaded_file($file['tmp_name'])) {
		return call_user_func($upload_error_handler, $file, addslashes(esc_html__('Specified file failed upload test.','ARForms')));
	}
	
	if($test_type) {
		$wp_filetype = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $mimes);
		extract($wp_filetype);
		if($proper_filename) {
			$file['name'] = $proper_filename;
		}
		if((!$type || !$ext) && !current_user_can('unfiltered_upload')) {
			return call_user_func($upload_error_handler, $file, addslashes(esc_html__('Sorry, this file type is not permitted for security reasons.','ARForms')));
		}
		
		if(!$ext) {
			$ext = ltrim(strrchr($file['name'], '.'), '.');
		}
		
		if(!$type) {
			$type = $file['type'];
		}
	}
	else {
		$type = '';
	}
	$ext = strtolower($ext);

	if($ext=="php" || $ext=="php3" || $ext=="php4" || $ext=="php5" || $ext=="pl" || $ext=="py" || $ext=="jsp" || $ext=="asp" || $ext=="exe" || $ext=="cgi"){
		return call_user_func($upload_error_handler, $file, addslashes(esc_html__('Sorry, this file type is not permitted for security reasons.','ARForms')));
	}
	
	if(count($ftypes_array) > 0 and !in_array($type, $ftypes_array)) {
		return call_user_func($upload_error_handler, $file, addslashes(esc_html__('Sorry, this file type is not permitted for security reasons.','ARForms')));
	}
		
	if(!(($uploads = wp_upload_dir($time)) && false === $uploads['error'])){
		return call_user_func($upload_error_handler, $file, $uploads['error']);
	}
	$file_bytes = $file['size'];
	$file_size = number_format($file_bytes / 1048576, 2);

	if( preg_match('/(image\/)/',$file['type']) && $file_size <= 10 ){
		
		if( !function_exists('WP_Filesystem' ) ){
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        WP_Filesystem();
        global $wp_filesystem;
		$file_content = $wp_filesystem->get_contents($file['tmp_name']);
		$isValidFile = $maincontroller->arf_check_valid_file($file_content);
		if( false == $isValidFile ){
			return call_user_func( $upload_error_handler, $file, addslashes( esc_html__('The file could not be uploaded due to security reason as it contains malicious code', 'ARForms') ) );
		}
	}

	$maincontroller->arf_start_session(true);
	
	$filename = $form_id."_".$file_id_new."_".time()."_".$file['name'];
	
	$filename = str_replace('?','-', $filename);
	$filename = str_replace('&','-', $filename);
		
	global $arformhelper;
	$movable = $arformhelper->manage_uploaded_file_path($form_id);
	if($movable["status"]) {
		$new_file = $movable["path"] . $filename;
		global $arformcontroller;
		if(false === $arformcontroller->arf_upload_file_function( $file['tmp_name'], $new_file ) ) {
			return $upload_error_handler($file, sprintf(addslashes(esc_html__('The uploaded file could not be moved to %s.','ARForms')), $movable["path"]));
		}
	}
	else {
		return $upload_error_handler($file, sprintf(addslashes(esc_html__('The uploaded file could not be moved to %s.','ARForms')), $movable["path"]));
	}
	
	$stat = stat(dirname($new_file));
	$perms = $stat['mode'] & 0000666;
	@ chmod($new_file, $perms);

	$url = $movable["path"] . "thumbs/$filename";
	
	if(is_multisite()) delete_transient('dirsize_cache');

	$new_file1 = $movable["path"] . "thumbs/".$filename;
	
	return apply_filters('wp_handle_upload', array('file' => $new_file1, 'url' => $url, 'type' => $type,'file_name'=>$filename), 'upload');
}

function media_handle_upload_custom($file_id, $attach_id,$form_id, $post_data = array(), $overrides = array('test_form' => false)) {
	
	$time = current_time('mysql');
	if($post = get_post($attach_id)) {
		if(substr($post->post_date, 0, 4) > 0)
			$time = $post->post_date;
	}

	$files = $_FILES[$file_id];
	global $wpdb, $MdlDb; $arformhelper;
	$file_id_new = str_replace('file','',$file_id);
	$res_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$MdlDb->fields." WHERE id = %d", $file_id_new));
	$field_options_new = maybe_unserialize($res_data[0]->field_options);
	$field_types = get_allowed_mime_types();
	$field_types['exe'] = '';
	unset($field_types['exe']);
	if (! is_array($field_options_new)) {
		$field_options_new = json_decode($field_options_new, true);
	}


	global $arformhelper;
	$ftypes_array = (isset($field_options_new['restrict']) && $field_options_new['restrict']==1) ? (($field_options_new['ftypes']=='') ? $field_types : $field_options_new['ftypes']) : $field_types;
	$new_media_value_ids = array();
	$maxupload_limit = isset($field_options_new['arf_max_file_upld'])? $field_options_new['arf_max_file_upld'] : '';

	foreach ($files['name'] as $fkey => $fvalue) {
		if ($maxupload_limit != '') {
			if (count($files) > 1 && $fkey < $maxupload_limit) {
				if($files['name'][$fkey]) {

					$file_data = array(
						'name'     => $files['name'][$fkey],
						'type'     => $files['type'][$fkey],
						'tmp_name' => $files['tmp_name'][$fkey],
						'error'    => $files['error'][$fkey],
						'size'     => $files['size'][$fkey]
					);

					$movable = $arformhelper->manage_uploaded_file_path($form_id);

					if($movable["status"]) {
						$file = wp_handle_upload_custom($file_data, $overrides, $time, $form_id, $ftypes_array, $file_id_new);
					}

					$image = $file['file_name'];

					$title = $file['file_name'];

	                $full_image_name = pathinfo($image);

	                $post_title = preg_replace( '/\.[^.]+$/', '', $full_image_name['filename'] );

	                $image_url = $movable['url'] . $image;
	                
	                $image_path = $movable['path'] . $image;

	                $uploaded_file_data = wp_check_filetype( $full_image_name['basename'] );

	                $post_mime_type = $uploaded_file_data['type'];

	                $attachment_args = array(
	                    'guid' => $image_path,
	                    'post_mime_type' => $post_mime_type,
	                    'post_title' => sanitize_text_field(  $post_title ),
	                    'post_content' => '',
	                    'post_status' => 'inherit'
	                );

	                $upload_id = wp_insert_attachment(
	                    $attachment_args,
	                    $image_url
	                );

	                require_once( ABSPATH . 'wp-admin/includes/image.php' );
	                require_once( ABSPATH . 'wp-admin/includes/media.php' );

	                $before_meta_data = get_post_meta( $upload_id, '_wp_attached_file', true );

	                wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $image_path ) );
	                
	                $after_meta_data = get_post_meta( $upload_id, '_wp_attached_file', true );

	                if( $before_meta_data != $after_meta_data ){
	                    update_post_meta( $upload_id, '_wp_attached_file', $before_meta_data, $after_meta_data );
	                }

	                $new_media_value_ids[] = $upload_id;

	                $post_meta_id = update_post_meta( $upload_id, 'arf_uploaded_file', 'arforms' );

	                $pos = strpos($files['type'][$fkey],'image/');
					if($pos !== false){
						$image = new SimpleImage();
						$image->load($movable["path"] . $title);
						$image->resizeToHeight(100);
						$image->save($movable["path"].'thumbs/'.$title);
					}
				}
			}
		}else{
			
			if($files['name'][$fkey]) {

				$file_data = array(
					'name'     => $files['name'][$fkey],
					'type'     => $files['type'][$fkey],
					'tmp_name' => $files['tmp_name'][$fkey],
					'error'    => $files['error'][$fkey],
					'size'     => $files['size'][$fkey]
				);

				$movable = $arformhelper->manage_uploaded_file_path($form_id);

				if($movable["status"]) {
					$file = wp_handle_upload_custom($file_data, $overrides, $time, $form_id, $ftypes_array, $file_id_new);
				}

				$image = $file['file_name'];

				$title = $file['file_name'];

                $full_image_name = pathinfo($image);

                $post_title = preg_replace( '/\.[^.]+$/', '', $full_image_name['filename'] );

                $image_url = $movable['url'] . $image;
                
                $image_path = $movable['path'] . $image;

                $uploaded_file_data = wp_check_filetype( $full_image_name['basename'] );

                $post_mime_type = $uploaded_file_data['type'];

                $attachment_args = array(
                    'guid' => $image_path,
                    'post_mime_type' => $post_mime_type,
                    'post_title' => sanitize_text_field(  $post_title ),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $upload_id = wp_insert_attachment(
                    $attachment_args,
                    $image_url
                );

                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                require_once( ABSPATH . 'wp-admin/includes/media.php' );

                $before_meta_data = get_post_meta( $upload_id, '_wp_attached_file', true );

                wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $image_path ) );
                
                $after_meta_data = get_post_meta( $upload_id, '_wp_attached_file', true );

                if( $before_meta_data != $after_meta_data ){
                    update_post_meta( $upload_id, '_wp_attached_file', $before_meta_data, $after_meta_data );
                }

                $new_media_value_ids[] = $upload_id;

                $post_meta_id = update_post_meta( $upload_id, 'arf_uploaded_file', 'arforms' );

				$pos = strpos($files['type'][$fkey],'image/');
				if($pos !== false){
					$image = new SimpleImage();
					$image->load($movable["path"] . $title);
					$image->resizeToHeight(100);
					$image->save($movable["path"].'thumbs/'.$title);
				}				
			}
		}
	}
	
	$new_media_value_ids = implode('|', $new_media_value_ids);
	return $new_media_value_ids;
}