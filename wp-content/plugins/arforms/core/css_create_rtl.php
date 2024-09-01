<?php

if( in_array( 'checkbox', $loaded_field ) ){
    echo "{$arf_form_cls_prefix} .top_container .arf_checkbox_style:not(.arf_enable_checkbox_image){$arf_checkbox_not_admin}{";
        echo "padding-left: unset;";
        echo "padding-right:" . ( ($arf_label_font_size + 12) < 30 ? 30 : ( $arf_label_font_size + 12) ) . "px;";
    echo "}";
        
    echo "{$arf_form_cls_prefix} .top_container .arf_checkbox_style:not(.arf_enable_checkbox_image){$arf_checkbox_not_admin} .arf_checkbox_input_wrapper{";
        echo "margin-right:0;";
        echo "margin-left:unset;";
    echo "}";

    echo "{$arf_form_cls_prefix} .top_container .arf_checkbox_style:not(.arf_enable_checkbox_image){$arf_checkbox_not_admin} .arf_checkbox_input_wrapper{";
        echo "right:0;";
        echo "left:unset;";
    echo "}";
}
        
if( in_array( 'radio', $loaded_field ) ){
    echo "{$arf_form_cls_prefix} .top_container .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image){";
        echo "padding-left: unset;";
        echo "padding-right:" . ( ($arf_label_font_size + 12) < 30 ? 30 : ( $arf_label_font_size + 12) ) . "px;";
    echo "}";
    echo "{$arf_form_cls_prefix} .top_container .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image) .arf_radio_input_wrapper:not(.arf_matrix_radio_input_wrapper){";
        echo "margin-right:0;";
        echo "margin-left:unset;";
    echo "}";

    echo "{$arf_form_cls_prefix} .top_container .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image) .arf_radio_input_wrapper:not(.arf_matrix_radio_input_wrapper){";
        echo "right:0;";
        echo "left:unset;";
    echo "}";
}

if( in_array( 'radio', $loaded_field ) || in_array( 'matrix', $loaded_field ) ){
    echo "{$arf_form_cls_prefix} .setting_radio.arf_standard_radio .arf_radio_input_wrapper,";
    echo "body.arf_preview_rtl {$arf_form_cls_prefix} .setting_radio.arf_standard_radio .arf_radio_input_wrapper {";
        echo "margin-left: 30px;";
        echo "margin-right: 0px;";
    echo "}";

    echo "{$arf_form_cls_prefix} .setting_radio.arf_custom_radio .arf_radio_input_wrapper,";
    echo "body.arf_preview_rtl {$arf_form_cls_prefix} .setting_radio.arf_custom_radio .arf_radio_input_wrapper {";
        echo "margin-left: 10px;";
        echo "margin-right: 0px;";
    echo "}";

    echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table th,";
    echo "{$arf_form_cls_prefix} .arf_matrix_field_control_wrapper table td{";
        echo "text-align:right;";
    echo "}";

    echo "{$arf_form_cls_prefix} .setting_radio.arf_standard_radio .arf_radio_input_wrapper.arf_matrix_radio_input_wrapper{";
        echo "margin-right: unset;";
        echo "margin-left: 0px;";
    echo "}";

    echo "{$arf_form_cls_prefix} .setting_radio .arf_radio_input_wrapper.arf_matrix_radio_input_wrapper{";
        echo "margin-right: unset !important;";
        echo "margin-left: 0px !important;";
    echo "}";

    echo "@media all and (max-width: 991px){

        .arf_matrix_field_radio_control_label{
            display: inline-flex;
            position: absolute;
            min-height: 2.5rem;
            align-items: center;
            top: 0;
            right: 1rem;
	    left: unset;
            width: calc(100% - 1rem);
            font-size: .875rem;
            line-height: 2.5rem;
            cursor: pointer;
        }

        .arf_matrix_field_body_control .arf_matrix_radio_control{
            flex-basis: 100%;
            max-width: 100%;
            justify-content: flex-end;
            min-height: 2.5rem;
            padding-left: 1rem;
	    padding-right: unset;
            display: inline-flex;
            flex-grow: 1;
            align-items: center;
        }

    }";

    echo "{$arf_form_cls_prefix} .arf_matrix_radio_input_wrapper input[type=\"radio\"] + span{";
        echo "right:0;";
        echo "left:unset;";
    echo "}";
}

if( in_array( 'textarea', $loaded_field ) ){
    echo "{$arf_form_cls_prefix} .allfields .arfcount_text_char_div{";
        echo "text-align:left;";
    echo "}";
}

if( in_array( 'break', $loaded_field ) ){
    echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.bottom{";
        echo "float:right;";
    echo "}";

    if($arf_page_break_wizard_theme_style == 'style3'){
        echo "{$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::after{";
            echo "background:linear-gradient(to right, {$pg_wizard_inactive_bg_color}  50%, {$pg_wizard_active_bg_color} 50%) left;";
        echo "}";

        echo ".arf_widget_form {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::after{";
            echo "background: linear-gradient(to right, {$pg_wizard_inactive_bg_color}  50%, {$pg_wizard_active_bg_color} 50%) left ;";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab div.page_break_nav:not({$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab div.page_nav_selected ~ div.page_break_nav):not({$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab div.page_nav_selected)::after{";
            echo "background-position:right;";
        echo "}";

        echo ".arf_widget_form {$arf_form_cls_prefix}  .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::before{";
            echo "right:-7px;";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::before{";
            echo "right:-9px;";
            echo "left:unset;";
        echo "}";
    }

    echo "{$arf_form_cls_prefix} input[type=\"button\"].previous_btn{";
        echo "margin-left:15px;";
    echo "}";


    echo "@media (max-width: 480px) {";

        if($arf_page_break_wizard_theme_style == 'style3'){
            echo "{$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::before{";
                echo "right:-7px;";
            echo "}";

            echo "{$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::after{";
                echo "background: linear-gradient(to right , {$pg_wizard_inactive_bg_color} 50%, {$pg_wizard_active_bg_color}  60%) left;";
            echo "}";
        }

    echo "}";

    echo "{$arf_form_cls_prefix} .page_break_nav{
        border-left:1px solid rgba(255,255,255,0.7);
    }";

    echo "{$arf_form_cls_prefix} .page_nav_selected,
        {$arf_form_cls_prefix} .arf_page_prev,
        {$arf_form_cls_prefix} .arf_page_last
        {
        border-left:none;
    }";

    if( $arfadd_pagebreak_timer == 1 ){
        echo "{$arf_form_cls_prefix} .arf_pagebreaktime{";
            echo "float:left;";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_pagebreak_timer{";
            echo "float:left;";
        echo "}";

        echo "{$arf_form_cls_prefix} .time_circles{";
            echo "width:fit-content;";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{";
            echo "margin-left:76%;";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.arf_pagebreak_min.arf_pagebreak_sec{";
            echo "margin-left:41%;";
        echo "}";

        if( preg_match( '/number/', $arfpagebreak_timer_style ) ){
            echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.pagebreak_style_number.arf_pagebreak_min.arf_pagebreak_sec{";
                echo "margin-left:23%;";
            echo "}";
            echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.pagebreak_style_number.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{";
                echo "margin-left:45%;";
            echo "}";
        }

        echo "@media all and (max-width:480px){";
          
           if( preg_match( '/circle_with_text/', $arfpagebreak_timer_style ) ){
                echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_circlewithtxt.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{
                        width: 110% !important;
                }";
            }

            if( preg_match( '/circle/', $arfpagebreak_timer_style ) ){
                echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_circle.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{
                    width: 115% !important;
                    margin-right: -30%;
                }";
            }

            echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.arf_pagebreak_min.arf_pagebreak_sec{";
            echo "margin-left:1%;";
            echo "}";

            echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{";
                echo "margin-left:3%;";
            echo "}";

            if( preg_match( '/number/', $arfpagebreak_timer_style ) ){
                echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.pagebreak_style_number.arf_pagebreak_min.arf_pagebreak_sec{";
                    echo "margin-left:5%;";
                echo "}";

                echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.pagebreak_style_number.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{";
                    echo "margin-left:0%;";
                echo "}";

               
                echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_number.arf_pagebreak_hrs,";
                echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_number.arf_pagebreak_min,";
                echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_number.arf_pagebreak_sec{";
                    echo "width: 70% !important;";
                    echo "margin-right: 57% !important";
                echo "}";
            }
        echo "}";
    }
}

if( $is_prefix_suffix_enable ){

    echo "{$arf_form_cls_prefix} .arfformfield {$arf_prefix_cls}{";
        echo "border-top-left-radius:0;";
        echo "border-bottom-left-radius:0;";
        echo "border-top-right-radius:{$field_border_radius};";
        echo "border-bottom-right-radius:{$field_border_radius};";
    echo "}";

    if( preg_match( '/rounded/', $arf_mainstyle ) ){
        echo "{$arf_form_cls_prefix} .arf_rounded_form .arfformfield {$arf_prefix_cls}{";
            echo "border-right:{$field_border_width} {$field_border_style} {$field_border_color};";
            echo "border-left:none;";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_rounded_form .arfformfield {$arf_prefix_cls}.arf_prefix_focus{";
            echo "border-right:{$field_border_width} {$field_border_style} {$base_color};";
            echo "border-left:none;";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_rounded_form .arfformfield {$arf_suffix_cls}{";
            echo "border-left:{$field_border_width} {$field_border_style} {$field_border_color};";
            echo "border-right:none;";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_rounded_form .arfformfield {$arf_suffix_cls}.arf_suffix_focus{";
            echo "border-left:{$field_border_width} {$field_border_style} {$base_color};";
            echo "border-right:none;";
        echo "}";
    }

    echo "{$arf_form_cls_prefix} .arfformfield {$arf_suffix_cls}{";
        echo "border-top-right-radius:0;";
        echo "border-bottom-right-radius:0;";
        echo "border-top-left-radius:{$field_border_radius};";
        echo "border-bottom-left-radius:{$field_border_radius}";
    echo "}";

    echo $arf_form_cls_prefix . " .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_prefix_only:not(.arf_phone_with_flag) input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_prefix_only input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
        echo "border-right:none !important;";
        echo "border-top-right-radius:0px !important;";
        echo "border-bottom-right-radius:0px !important;";
        echo "border-left:unset !important;";
        echo "border-top-left-radius:unset !important;";
        echo "border-bottom-left-radius:unset !important;";
    echo "}";

    echo $arf_form_cls_prefix . " .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_suffix_only input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_suffix_only input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
        echo "border-left:none !important;";
        echo "border-top-left-radius:0px !important;";
        echo "border-bottom-left-radius:0px !important;";
        echo "border-right:unset !important;";
        echo "border-top-right-radius:unset !important;";
        echo "border-bottom-right-radius:unset !important;";
    echo "}";

    if( preg_match( '/material/', $arf_mainstyle ) ){
        echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_leading_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_leading_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
            echo "padding-right:{$arf_paddingleft_field} !important;";
            echo "padding-left:{$fieldpadding_2}px !important;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_leading_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_leading_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label" : "" )."{";
            echo "padding-right:{$arf_paddingleft_field} !important;";
            echo "padding-left:{$fieldpadding_2}px !important;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_trailing_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_trailing_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
            echo "padding-left:{$arf_paddingleft_field} !important;";
            echo "padding-right:{$fieldpadding_2}px !important;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_trailing_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_theme_container_with_icons.arf_only_trailing_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label" : "" )."{";
            echo "padding-left:{$arf_paddingleft_field} !important;";
            echo "padding-right:{$fieldpadding_2}px !important;";
        echo "}";
    }
    /** Material Outline Style */

    if( preg_match( '/material_outlined/', $arf_mainstyle ) ){
        echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_outline_container_with_icons.arf_only_leading_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_outline_container_with_icons.arf_only_leading_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
            echo "padding-right:{$arf_paddingleft_field} !important;";
            echo "padding-left:{$fieldpadding_2}px !important;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_outline_container_with_icons.arf_only_trailing_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_outline_container_with_icons.arf_only_trailing_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
            echo "padding-left:{$arf_paddingleft_field} !important;";
            echo "padding-right:{$fieldpadding_2}px!important;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_outline_container_with_icons.arf_only_leading_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_outliner label.arf_main_label".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_outline_container_with_icons.arf_only_leading_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label" : "" )."{";
            echo "right:{$arf_lable_padding} !important;";
            echo "left:unset  !important;";
        echo "}";

        echo $arf_form_cls_prefix . " .arfformfield .controls .arf_material_outline_container_with_icons.arf_only_trailing_icon input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_outliner label.arf_main_label".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls .arf_material_outline_container_with_icons.arf_only_trailing_icon input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor) + .arf_material_standard label.arf_main_label" : "" )."{";
            echo "left:{$arf_lable_padding} !important;";
            echo "right:unset !important;";
        echo "}";

        echo $arf_form_cls_prefix . " .arf_material_outline_container input.arf_material_active + .arf_material_outliner .arf_material_outliner_notch .arf_main_label,";
        echo $arf_form_cls_prefix . " .arf_material_outline_container.arf_material_active_container .arf_material_outliner .arf_material_outliner_notch .arf_main_label{";
            echo "right: 0 !important;";
        echo "}";
    }
}

if( $is_img_crop_enable ){
    echo ".arf_save_change{
        margin-right: 50px;
        margin-left: inherit;
    }";

    echo ".arf_crop_button_wrapper .arf_crop_button{
        margin-right: 0 !important;
        margin-left: 10px !important;
    }";

    echo ".arf_crop_button_wrapper{
        text-align: right;
    }
    
    .arf_crop_button_wrapper .arf_save_change{
        float:left;
    }"; 
}

if( in_array( 'like', $loaded_field ) ){
    echo "@media all and (min-width:0\\0) {
         {$arf_form_cls_prefix} .like_container .arf_like_btn svg, body.arf_preview_rtl  .like_container .arf_like_btn svg{
            right:52%;
            left: inherit;
        }

         {$arf_form_cls_prefix} .like_container .arf_dislike_btn svg, body.arf_preview_rtl  .like_container .arf_dislike_btn svg{
            right:48%;
            left: inherit;
        }
    }";

    echo "@supports (-ms-accelerator:true) {
  
        {$arf_form_cls_prefix}  .like_container .arf_like_btn svg, body.arf_preview_rtl  .like_container .arf_like_btn svg{
            right:52%;
            left: inherit;
        }

        {$arf_form_cls_prefix}  .like_container .arf_dislike_btn svg, body.arf_preview_rtl  .like_container .arf_dislike_btn svg{
            right:48%;
            left: inherit;
        }
    }";
}

if( in_array( 'colorpicker', $loaded_field ) ){
    echo "{$arf_form_cls_prefix}  input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider).arf_editor_colorpicker{
        width:100px !important;";
        echo "border-top-right-radius:0px !important;
        border-bottom-right-radius:0px !important;";
        echo "border-top-left-radius:unset !important;";
        echo "border-bottom-left-radius:unset !important;";
    echo "}";

    if( $is_form_save ){
        echo "{$arf_form_cls_prefix}  .arf_editor_prefix.arf_colorpicker_prefix_editor {";
            echo "border-left:0px  {$field_border_style}  {$field_border_color} !important;";
            echo "border-right: {$field_border_width_select_custom}px  {$field_border_style}  {$field_border_color} !important;";
            echo "border-left: unset !important;
            border-right:unset !important;";
        echo "}";

        echo "{$arf_form_cls_prefix}  .arf_editor_prefix.arf_colorpicker_prefix_editor{";
            echo "-webkit-border-radius: 0px {$field_border_radius} {$field_border_radius} 0px !important;
            -o-border-radius: 0px {$field_border_radius} {$field_border_radius} 0px !important;
            -moz-border-radius: 0px {$field_border_radius} {$field_border_radius} 0px !important;
            border-radius: 0px {$field_border_radius} {$field_border_radius} 0px !important;";
        echo "}";
    }

    echo "{$arf_form_cls_prefix}  .arfcolorpickerfield .arfcolorimg {";
        echo "border-left:{$field_border_width_select_custom}px {$field_border_style} {$field_border_color};
        float:right;";
    echo "}";

    echo "{$arf_form_cls_prefix}  .arfcolorvalue {";
        echo "padding:8px 30px 0px 0px;";
    echo "}";
}

if( in_array( 'arf_repeater', $loaded_field ) ){
    echo "{$arf_form_cls_prefix} .arf_repeater_field .arf_repeater_remove_new_button,";
    echo "{$arf_form_cls_prefix} .edit_field_type_arf_repeater .arf_repeater_editor_add_icon,";
    echo "{$arf_form_cls_prefix} .arf_repeater_field .arf_repeater_add_new_button {";
        echo "float: left;";
        echo "margin-left: 0px;";
        echo "margin-right: 10px;";
    echo "}";

}

if( array_intersect( $loaded_field, $common_field_type_styling ) ){
    
    if( $is_form_save ){
        if( preg_match( '/material_outlined/', $arf_mainstyle ) ){
            echo $arf_form_cls_prefix . " .edit_field_type_select .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix,";
            echo $arf_form_cls_prefix . " .edit_field_type_arf_multiselect .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix,";
        }
    }
    if( preg_match( '/material_outlined/', $arf_mainstyle ) ){
        echo $arf_form_cls_prefix . " .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix{";
            echo "border-radius:0 4px 4px 0;";
            echo "border-width:{$field_border_width};";
            echo "border-style:{$field_border_style};";
            echo "border-color:{$field_border_color};";
            echo "border-right:{$field_border_width} {$field_border_style} {$field_border_color};";
            echo "border-left: none;";
        echo "}";
    }

    if( $is_form_save ){
        if( preg_match( '/material_outlined/', $arf_mainstyle ) ){
            echo $arf_form_cls_prefix . " .edit_field_type_select .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix,";
            echo $arf_form_cls_prefix . " .edit_field_type_arf_multiselect .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix,";
        }
    }

    if( preg_match( '/material_outlined/', $arf_mainstyle ) ){
        echo $arf_form_cls_prefix . " .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix{";
            echo "border-radius:4px 0 0 4px;";
            echo "border-width:{$field_border_width};";
            echo "border-style:{$field_border_style};";
            echo "border-color:{$field_border_color};";
            echo "border-left:{$field_border_width} {$field_border_style} {$field_border_color};";
            echo "border-right: none;";
        echo "}";
    }

    echo $arf_form_cls_prefix . " label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label){";
        echo "left:inherit;";
        echo "right:0px;";
    echo "}";

    echo $arf_form_cls_prefix . " .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_prefix_only:not(.arf_phone_with_flag) input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_prefix_only input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
        echo "border-right:none !important;";
        echo "border-top-right-radius:0px !important;";
        echo "border-bottom-right-radius:0px !important;";
        echo "border-left:{$field_border_width} {$field_border_style} {$field_border_color} !important;";
        echo "border-top-left-radius:{$field_border_radius} !important;";
        echo "border-bottom-left-radius:{$field_border_radius} !important;";
        echo "width:100%;";
    echo "}";

    echo $arf_form_cls_prefix . " .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_suffix_only input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)".( ( in_array('phone', $loaded_field ) ) ? ",{$arf_form_cls_prefix} .arfformfield .controls {$arf_prefix_suffix_wrapper_cls}.arf_suffix_only input[type=tel]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)" : "" )."{";
        echo "border-left:none !important;";
        echo "border-top-left-radius:0px !important;";
        echo "border-bottom-left-radius:0px !important;";
        echo "border-right:{$field_border_width} {$field_border_style} {$field_border_color} !important;";
        echo "border-top-right-radius:{$field_border_radius} !important;";
        echo "border-bottom-right-radius:{$field_border_radius} !important;";
        echo "width:100%;";
    echo "}";

    echo $arf_form_cls_prefix . " .arfformfield .arf_leading_icon{";
        echo "right:15px;";
        echo "left: unset;";
    echo "}";

    echo $arf_form_cls_prefix . " .arfformfield .arf_trailing_icon{";
        echo "left:15px;";
        echo "right: unset;";
    echo "}";

    if( preg_match( '/material_outlined/', $arf_mainstyle ) ){
        echo $arf_form_cls_prefix . " {$arf_multiselect_picker_cls} .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix,";
        echo $arf_form_cls_prefix . " {$arf_select_picker_cls} .arf_material_outline_container .arf_material_outliner .arf_material_outliner_prefix{";
            echo "min-width: 12px;";
            echo "width:" . ( !empty( $arffieldinnermarginssetting_2 ) ? $arffieldinnermarginssetting_2 : '12') . "px;";
            echo "border-radius:0 4px 4px 0;";
            echo "border-width:{$field_border_width};";
            echo "border-style:{$field_border_style};";
            echo "border-color:{$field_border_color};";
            echo "border-left: none; ";
        echo "}";


        echo $arf_form_cls_prefix . " {$arf_multiselect_picker_cls} .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix,";
        echo $arf_form_cls_prefix . " {$arf_select_picker_cls} .arf_material_outline_container .arf_material_outliner .arf_material_outliner_suffix{";
            echo "min-width: 12px;";
            echo "width:" . ( !empty( $arffieldinnermarginssetting_2 ) ? $arffieldinnermarginssetting_2 : '12') . "px;";
            echo "border-radius:4px 0 0 4px;";
            echo "border-width:{$field_border_width};";
            echo "border-style:{$field_border_style};";
            echo "border-color:{$field_border_color};";
            echo "border-right: none; ";
        echo "}";
    }
}