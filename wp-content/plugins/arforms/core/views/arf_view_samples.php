<?php

global $arsettingcontroller;

if($arf_samples == ''){

    echo "<div class='error_message' style='margin-top:100px; padding:20px;'>" . addslashes(esc_html__("Forms listing is currently unavailable. Please try again later.", "ARForms")) . "</div>";

} else {

    $arf_samples = maybe_unserialize(base64_decode($arf_samples));

    
    if (is_array($arf_samples) && count($arf_samples) > 0) {

        foreach ($arf_samples as $arf_sample) {
            $link = '';
            $img_class = 'arf_sample_form_image_new_form';
            if($load_list_into_new_form_popup == false) { 
                $link = ' href="'.$arf_sample['redirect_url'].'" target="_blank" ';
                $img_class = '';
            }

            ?>

            <div class="arf_sample_container">
                <div class="arf_sample_title">
                    <a <?php echo $link; ?> ><?php echo $arf_sample['name']; ?></a>
                </div>
                <div class="arf_sample_image <?php echo $img_class; ?>">
                    <a href="javascript: void(0);" ><img src="<?php echo $arf_sample['image']; ?>" width="290" height="119" data-arf-form-id="<?php echo $arf_sample['form_id']; ?>" /></a>
                </div>
                <input type="hidden" class="arf_sample_form_id_hidden" name="arf_sample_form_id_hidden" value="<?php echo $arf_sample['form_id']; ?>">

            <?php 
            if($load_list_into_new_form_popup == false) { ?>
                <div class="arf_sample_form_add_more">
                    <a class="arf_sample_download_button" href="javascript: void(0);" data-arf-form-id="<?php echo $arf_sample['form_id']; ?>"><?php esc_html_e('Install','ARForms'); ?></a>
                </div>
            <?php } ?>
                

            </div>

            <?php
        }
    }


}
?>
<div class="arf_loader_icon_wrapper" id="arfeditor_loader" style="display: none;"><div class="arf_loader_icon_box"><div class="arf-spinner arf-skeleton arf-grid-loader"></div></div></div>
<div id="error_message" class="arf_error_message">
    <div class="message_descripiton">
        <div id="arf_sample_download_error" style="float: left; margin-right: 15px;" id=""><?php echo addslashes(esc_html__('Form is not available.', 'ARForms')); ?></div>
        <div class="message_svg_icon">
            <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></svg>
        </div>
    </div>
</div>