<?php
global $arfform, $maincontroller;

$all_templates = $arfform->getAll(array('is_template' => 1), 'name');

$sv[1] = '<div class="arfsubscriptionform arftemplateicondiv"></div>';
$sv[2] = '<div class="arfregistrationform arftemplateicondiv"></div>';
$sv[3] = '<div class="arfcontactform arftemplateicondiv"></div>';
$sv[4] = '<div class="arfsurveyform arftemplateicondiv"></div>';
$sv[5] = '<div class="arfffedbackform arftemplateicondiv"></div>';
$sv[6] = '<div class="arfrsvpform arftemplateicondiv"></div>';
$sv[7] = '<div class="arfjobapplicationform arftemplateicondiv"></div>';
$sv[8] = '<div class="arfdonationform arftemplateicondiv"></div>';
$sv[9] = '<div class="arfrequestaquoteform arftemplateicondiv"></div>';
$sv[10] = '<div class="arfmemberloginform arftemplateicondiv"></div>';
$sv[11] = '<div class="arforderform arftemplateicondiv"></div>';

?>

<div id="new_form_selection_modal">
	<form method="get" name="new" id="new">
        <input type="hidden" name="arfaction" id="arfnewaction" value="new" />
        <input type="hidden" name="page" value="ARForms" />
        
        <input type="hidden" name="id" id="template_list_id" value="" />    
	<div class="newform_modal_title_container">
    	<div class="newform_modal_title"><?php echo addslashes(esc_html__('New Form','ARForms'));?></div>
    </div>
 	

    <div class="newform_modal_fields_start_left">
    
    	<div class="arf_form_type_selection_container">
	    	<div class="arf_radio_wrapper">
	            <div class="arf_custom_radio_div">
	                <div class="arf_custom_radio_wrapper">
	                    <input type="radio" class="arf_custom_radio arf_form_type" name="arf_form_type" id="arf_form_type_blank" value="blank_form" checked="checked" />
	                    <svg width="18px" height="18px">
	                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
	                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
	                    </svg>
	                </div>
	            </div>
	            <span>
	                <label for="arf_form_type_blank"><?php echo addslashes(esc_html__('Blank Form', 'ARForms')); ?></label>
	            </span>
	        </div>

	        <div class="arf_radio_wrapper">
	            <div class="arf_custom_radio_div">
	                <div class="arf_custom_radio_wrapper">
	                    <input type="radio" class="arf_custom_radio arf_form_type" name="arf_form_type" id="arf_form_type_template" value="template_form" />
	                    <svg width="18px" height="18px">
	                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
	                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
	                    </svg>
	                </div>
	            </div>
	            <span>
	                <label for="arf_form_type_template"><?php echo addslashes(esc_html__('Templates', 'ARForms')); ?></label>
	            </span>
	        </div>

	        <div class="arf_radio_wrapper">
	            <div class="arf_custom_radio_div">
	                <div class="arf_custom_radio_wrapper">
	                    <input type="radio" class="arf_custom_radio arf_form_type" name="arf_form_type" id="arf_form_type_sample" value="sample_form" />
	                    <svg width="18px" height="18px">
	                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
	                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
	                    </svg>
	                </div>
	            </div>
	            <span>
	                <label for="arf_form_type_sample"><?php echo addslashes(esc_html__('Sample Forms', 'ARForms')); ?></label>
	            </span>
	        </div>
	    </div>

	    <div class="arf_new_form_option_container">
	    	<div class="newmodal_field_title"><?php echo addslashes(esc_html__('Form Title','ARForms'));?>&nbsp;<span class="newmodal_required" style="color:#ff0000; vertical-align:top;">*</span></div>
	        <div class="newmodal_field">
	        	<input name="form_name" id="form_name_new" value="" class="txtmodal1" /><br />
	        	<div id="form_name_new_required" class="arferrmessage" style="display:none;"><?php echo addslashes(esc_html__('Please enter form title','ARForms'));?></div>
	        </div>	
	        
	        <div class="newmodal_field_title">
				<?php echo addslashes(esc_html__('Form Description','ARForms'));?>
			</div>
		    <div class="newmodal_field">
		    	<textarea name="form_desc" id="form_desc_new" class="txtmultimodal1" rows="2" ></textarea>
		    </div>

		    <div class="arf_theme_style_container">
			    <div class="newmodal_field_title">
					<?php echo addslashes(esc_html__('Select Theme','ARForms'));?>
				</div>
			    <div class="newmodal_field">
			    	<?php
			    		$inputStyle = array(
			    			'material_outlined' => addslashes( esc_html__( 'Material Outlined', 'ARForms') ),
		                    'standard' => addslashes(esc_html__('Standard Style', 'ARForms')),
		                    'rounded' => addslashes(esc_html__('Rounded Style', 'ARForms')),
		                    'material' => addslashes(esc_html__('Material Style', 'ARForms'))
		                );
			    		echo $maincontroller->arf_selectpicker_dom( 'templete_style', 'templete_style', 'arf_templete_style_dt', 'width:102.6%', 'material', array(), $inputStyle );
			    	?>
			    </div>
			</div>

			<!-- new form in RTL mode option -->
		    <?php if(is_rtl()){ ?>

		    <div class="newmodal_field_title">
	    		<?php echo addslashes(esc_html__('Input Direction','ARForms'));?>
	    	</div>
		    <div class="newmodal_field">
		    	<?php
		    		$direction_opts = array(
		    			'no'	=> addslashes( esc_html__( 'Left to Right', 'ARForms' ) ),
		    			'yes'	=> addslashes( esc_html__( 'Right to Left', 'ARForms' ) )
		    		);

		    		echo $maincontroller->arf_selectpicker_dom( 'arf_rtl_switch_mode', 'arf_load_form_rtl_switch', '', 'width:102.6%', 'yes', array(), $direction_opts );
		    	?>
		    </div>
		    <?php } ?>
			<!-- end RTL mode Option -->

	    </div>


        <div class="newmodal_field arfdefaulttemplate" style="display:none;margin-top: 20px;<?php echo (is_rtl()) ? 'float: right;' : 'float: left;';?>">
        
		 <div class="newmodal_field_title" style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;margin:10px 0;">
		 	<div>
		 		<?php echo addslashes(esc_html__('Select Template','ARForms'));?>&nbsp;<span class="newmodal_required" style="color:#ff0000; vertical-align:top;">*</span>	
		 	</div>
			<p class="newmodal_default_template_required_error"><?php esc_html_e('Please select any Template.', 'ARForms'); ?></p>
		</div>
		
		<?php 
		    global $arfdefaulttemplate;
		    if( $arfdefaulttemplate )
		    {
			    $ti = 1;
			    foreach($arfdefaulttemplate as $template_id => $template_name)
			    {?>
	    <div id="arftemplate_<?php echo $template_id ?>" onclick="arf_selectform('<?php echo $template_id ?>','<?php echo $template_name['theme'] ?>','<?php echo $template_name['name'] ?>');" class="arf_modalform_box" <?php if($ti <= 3){ ?>style="margin-bottom:5px;"<?php } ?>>
		<div class="arf_formbox_hover"></div>
		<?php echo $sv[$template_id];?>		
		<div class="arf_modalform_boxtitle"><?php echo $template_name['name'];?></div>  
	    </div>
	    <?php
			$ti++;
			}
		    }?> 
        </div>

        <div class="arf_sample_template_container" style="margin-top:10px;">
	         <div class="newmodal_field_title" style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;margin:10px 0;">
				
				<div>
			 		<?php echo addslashes(esc_html__('Select Sample','ARForms'));?>&nbsp;<span class="newmodal_required" style="color:#ff0000; vertical-align:top;">*</span>	
			 	</div>
				<p class="newmodal_sample_template_required_error"><?php esc_html_e('Please select any Sample.', 'ARForms'); ?></p>
			</div>
        	<input type="hidden" class="arf_sample_form_id" name="arf_sample_form_id" value="">
        	<?php
				global $arsamplecontroller;
				$load_list_into_new_form_popup = true;
				$sample_lists = $arsamplecontroller->samples_list($load_list_into_new_form_popup);
			?>
        </div>

    </div>
	<div style="clear:both;"></div>
	
	
	<div id="arfcontinuebtn" >
	    <button type="button" class="rounded_button arf_btn_dark_blue" id="submit_new_form" onclick="submit_form_type();" style=""><?php echo addslashes(esc_html__('Continue', 'ARForms')); ?></button>
	    <button type="button" class="rounded_button arfnewmodalclose" style="<?php echo (is_rtl() ) ? 'margin-right:22px;' : 'margin-right:11px;'; ?>background-color:#ECECEC;color:#666666;position:inherit;"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button> 
	</div>
	<div class="arf_sample_form_loader_wrapper">
    	<div class="arf_loader_icon_wrapper" id="arf_sample_form_loader" style="display: none;"><div class="arf_loader_icon_box"><div class="arf-spinner arf-skeleton arf-grid-loader"></div></div></div>
    </div>
    </form>
    
    <script type="text/javascript">
    jQuery(document).ready(function(e){
	jQuery('#form_name_new').focus();    
    });
  

    </script>
</div>
<div class="arf_modal_overlay">
    <div class="arf_modal_container arf_failed_sample_popup_container">
        <div class="arf_modal_top_belt">
            <span class="arf_modal_title"><?php esc_html_e('Install Failed','ARForms'); ?></span>
            <div class="arf_modal_close_btn arf_failed_sample_popup_container_close"></div>
        </div>
        <div class="arf_sample_popup_content">
            <div class="arf_sample_popup_msg"><?php esc_html_e('Please activate license to install this sample.','ARForms') ?></div>
            <div class="arf_sample_popup_button">
                <button id="arf_sample_popup_btn_div" type="button" class="arf_sample_popup_btn"><?php esc_html_e('OK','ARForms'); ?></button>
            </div>
        </div>
    </div>
</div>