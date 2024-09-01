<?php

class arf_file_type_conversion{

	function __construct(){

		add_action('arf_editor_general_options_menu',array($this,'arf_add_general_option_menu'));

		add_action('arf_add_modal_in_editor',array($this,'arf_add_field_conversion_modal'),10);

		add_action('arf_display_additional_css_in_editor',array($this,'arf_field_converter_model_style'));
	}

	function arf_add_general_option_menu(){
		$show_convert_field_menu = "display:none;";
		if( isset($_GET['arfaction']) && $_GET['arfaction'] == 'edit' ){
			$show_convert_field_menu = "";
		}
		echo '<li class="arf_editor_top_dropdown_option" id="arf_field_type_converter" style="'.$show_convert_field_menu.'">'.addslashes(esc_html__('Convert Field Type', 'ARForms')).'</li>';

		echo '<input type="hidden" id="arf_field_type_conversion_array" value="'.base64_encode( json_encode( $this->arf_migrate_field_type() ) ).'" />';

	}

	function arf_field_converter_model_style(){
	?>
		<style type="text/css">
			#arf_field_type_converter_model{
				height: 60%;
		        min-height: 60%;
		        max-height: 60%;
		        width: 50%;
		        max-width: 50%;
			}
			.arf_field_converter_option_container{
				min-height: 75%;
			    max-height: 80%;
			    overflow-y: auto;
			    overflow-x: hidden;
			    padding-left:10px;
			}
			.arf_field_type_conversion_container{
				float: left;
			    width: 100%;
			    min-height: 155px;
			    height: auto;
			    margin-bottom: 10px;
			    text-align: left;
			}
			.arf_field_type_conversion_container .arf_ar_dropdown_wrapper{
				float:left;
				width:100%;
				margin-bottom:10px;
			}
			.arf_field_type_conversion_container .arf_ar_dropdown_wrapper label.arf_dropdown_autoresponder_label{
				float: left;
			    height: 30px;
			    vertical-align: middle;
			    width: 150px;
			    margin-right: 10px;
			    text-align: right;
			    line-height: 32px;
			}
			body.rtl .arf_field_type_conversion_container .arf_ar_dropdown_wrapper label.arf_dropdown_autoresponder_label{
				float:right;
			}
			.arf_field_type_conversion_container .arf_ar_dropdown_wrapper dl.arf_selectbox{
				float:left;
			}
			.arf_ar_dropdown_wrapper_note_current_type,
			.arf_ar_dropdown_wrapper_note_changing_type{
			    float: left;
			    width: 100%;
			    font-family: Asap-regular;
			    height: 28px;
			    margin-bottom: 5px;
			    font-size:15px;
			}
			.arf_ar_dropdown_wrapper_note_changing_type{
			    padding-left: 150px;
			    display: none;
				font-style: italic;
				color:#ff0000;
				height: auto;
			}
			.arf_current_field_type{
				font-family: Asap-Medium;
			    height: 30px;
			    display: inline-block;
			    line-height: 32px;
			}
			.arf_popup_close_button_field_converter {
			    font-family: Asap-Medium;
			    outline: none;
			    float: right;
			    background: #4786ff;
			    border: none;
			    border-radius: 85px;
			    -webkit-border-radius: 85px;
			    -moz-border-radius: 85px;
			    -o-border-radius: 85px;
			    width: 85px;
			    text-align: center;
			    color: #ffffff;
			    font-size: 14px;
			    cursor: pointer;
			    height: 33px;
			    padding-bottom: 3px;
			    outline: none;
			}
			body.rtl .arf_popup_close_button_field_converter{
				float:left;
			}
			#arf_field_converter_loader{
				float: right;
			    right: 10px;
			    position: relative;
			}
			.arf_field_type_conversion_container .arf_feature_recommendation_note{
				float:left;
				width:100%;
				margin:0 0 20px 0;
				padding:0 20px;
			}
			@media all and (min-width:1600px) and (max-width:1899px){
				#arf_field_type_converter_model{
					height: 50%;
			        min-height: 50%;
			        max-height: 50%;
			        width: 40%;
			        max-width: 40%;
				}
			}
			@media all and (min-width:1900px){
				#arf_field_type_converter_model{
					height: 50%;
			        min-height: 50%;
			        max-height: 50%;
			        width: 40%;
			        max-width: 40%;
				}
			}
		</style>
	<?php
	}

	function arf_add_field_conversion_modal($values){
		global $arfieldhelper, $maincontroller;
	?>
		<div class="arf_modal_overlay">
			<div id="arf_field_type_converter_model" class="arf_popup_container arf_popup_container_field_typle_converter_model">
				
				<div class="arf_popup_container_header">
					<?php echo esc_html__('Convert Field Type','ARForms'); ?>
					<div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_optin_popup_button">
	                    <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
	                </div>
				</div>

				<div class="arf_popup_content_container arf_field_converter_option_container">
					<div class="arf_field_type_conversion_container">
						<p class="arf_feature_recommendation_note">
							<?php echo '<strong>'.addslashes(esc_html__('Note','ARForms')).':</strong> '.addslashes(esc_html__('This feature is only recommended when you have big amount of entries in the form and you want to change the particular field type without losing the entry data for that field.','ARForms')); ?>
						</p>
						<div style="margin-left: 25px;float: left;width:100%;display: block;">
							<div class="arf_ar_dropdown_wrapper">
								<label class="arf_dropdown_autoresponder_label"> <?php echo esc_html__('Select Field To Convert','ARForms'); ?> </label> 
								<input type="hidden" id="arf_current_field_type" />
								<?php
									$supported_field_types = $this->arf_migrate_field_type();
									$convert_field_options = array( '' => addslashes(esc_html__('Select Field', 'ARForms')) );
									$convert_field_opt_attr = array();

									if( isset($values['fields']) && count($values['fields']) > 0 ){
										foreach( $values['fields'] as $k => $fields ){
											if( array_key_exists($fields['type'],$supported_field_types) ){
												$convert_field_options[$fields['id']] = $arfieldhelper->arf_execute_function($fields["name"],'strip_tags');
												$convert_field_opt_attr['data-type'][$fields['id']] = $fields['type'];
											}
										}
									}

									echo $maincontroller->arf_selectpicker_dom( '', 'field_type_converter', 'arf_change_type_conversion_dropdown', 'width:200px; clear:none;', '', array(), $convert_field_options, false, array(), false, $convert_field_opt_attr, false, array(), true );
								?>
							</div>

							<div class="arf_ar_dropdown_wrapper">
								<label class="arf_dropdown_autoresponder_label"><?php echo esc_html__('Current Field Type','ARForms'); ?>:</label>
								<span class="arf_current_field_type"></span>
							</div>

							<div class="arf_ar_dropdown_wrapper">
								<label class="arf_dropdown_autoresponder_label"> <?php echo esc_html__('Convert To Field Type','ARForms'); ?> </label> 

								<?php
									$all_fields_type = $this->arf_migrate_field_type();
									$convert_to_field_options = array('' => addslashes(esc_html__('Select Field', 'ARForms')) );
									$convert_to_field_opt_attr = array();

									foreach( $all_fields_type as $type => $label ){
										$convert_to_field_options[$type] = $label;
										$convert_to_field_opt_attr['data-type'][$type] = $type;
									}

									echo $maincontroller->arf_selectpicker_dom( '', 'field_type_to_convert', '', 'width:200px; clear:none;', '', array(), $convert_to_field_options, false, array(), false, $convert_to_field_opt_attr );
								?>

							</div>

							<ul class="arf_ar_dropdown_wrapper_note_changing_type">
							</ul>
						</div>
					</div>
				</div>

				<div class="arf_popup_container_footer">
					<button type="button" class="arf_popup_close_button_field_converter" data-id="arf_optin_popup_button"><?php echo esc_html__('Confirm',"ARForms"); ?></button>
					<div class="arf_imageloader" id="arf_field_converter_loader"></div>
				</div>

			</div>
		</div>
	<?php
	}

	function arf_migrate_field_type(){

        $field_types = array(
            'text' => esc_html__('Single Line Text', 'ARForms'),
            'textarea' => esc_html__('Multiline Text', 'ARForms'),
            'checkbox' => esc_html__('Checkbox','ARForms'),
            'radio' => esc_html__('Radio Buttons','ARForms'),
            'select' => esc_html__('Dropdown','ARForms'),
            'email' => esc_html__('Email','ARForms'),
            'number' => esc_html__('Number','ARForms'),
            'phone' => esc_html__('Phone','ARForms'),
            'url' => esc_html__('Website/URL','ARForms'),
            'password' => esc_html__('Password','ARForms'),
            'scale' => esc_html__('Star Rating','ARForms'),
            'arfslider' => esc_html__('Slider','ARForms'),
            'colorpicker' => esc_html__('Colorpicker','ARForms'),
            'arf_smiley' => esc_html__('Smiley','ARForms'),
            'arf_autocomplete' => esc_html__('Autocomplete','ARForms')
        );

        $field_types = apply_filters('arf_migrate_field_type_from_outside',$field_types);

        return $field_types;
    }

}

global $arf_file_type_conversion;
$arf_file_type_conversion = new arf_file_type_conversion();