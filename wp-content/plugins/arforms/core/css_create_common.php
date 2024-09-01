<?php

if( !empty( $loaded_field ) ){
    if( in_array( 'file', $loaded_field) ){
        $lwidth = isset($width) ? (int) $width : 0;
        $label_margin = $lwidth + 15;
        echo "{$arf_form_cls_prefix}  .left_container .attachment-thumbnail{clear:both;margin-left:{$label_margin}px;}";
        echo "{$arf_form_cls_prefix}  .original{ opacity: 0; position: relative; z-index: 100; width:". ( ( $arf_input_field_width == '') ? 'auto' : ($arf_input_field_width) )."px}";
    
    
        if (isset($arf_input_font_size) and $arf_input_font_size < '20') {
            $file_upload_padding = '10';
            $file_upload_hw = '14px';
            $file_upload_bg = 'upload-icon.png';
        
            if (isset($arf_input_font_size) and $arf_input_font_size <= 13)
                $file_upload_margin_top = '0px';
            else
                $file_upload_margin_top = '3px';
        } else if (isset($arf_input_font_size) and $arf_input_font_size >= '20' and $arf_input_font_size < '26') {
            $file_upload_padding = '13';
            $file_upload_hw = '14px';
        
            if ($arf_input_font_size > 22)
                $file_upload_margin_top = '9px';
            else
                $file_upload_margin_top = '7px';
        
            $file_upload_bg = 'upload-icon.png';
        } else if (isset($arf_input_font_size) and  $arf_input_font_size >= '26' and $arf_input_font_size < '33') {
            $file_upload_padding = '15';
            $file_upload_hw = '25px';
            $file_upload_margin_top = '5px';
            $file_upload_bg = 'upload-icon_25x25.png';
        } else if (isset($arf_input_font_size) and $arf_input_font_size > '33') {
            $file_upload_hw = '32px';
            $file_upload_padding = '17';
            $file_upload_margin_top = '7px';
            $file_upload_bg = 'upload-icon_32x32.png';
        } else {
            $file_upload_bg = 'upload-icon_32x32.png';
        }
    
    
        echo "{$arf_form_cls_prefix}  .arfajax-file-upload {";
            if ($arf_form_alignment == 'right' && $arf_label_align == 'right') {
                echo "float: right !important;";
            }
        echo "font-size:{$arf_input_font_size}px;
            font-family:{$arf_input_font_family};
            {$arf_input_font_style_str}
            padding: 7px {$file_upload_padding}px 5px {$file_upload_padding}px !important;
        }";
            
        echo "{$arf_form_cls_prefix}  .arfajax-file-upload-drag {";
            if ($arf_form_alignment == 'right' && $arf_label_align == 'right') {
                echo "float: right !important;";
            }
        echo "font-size:{$arf_input_font_size}px;
        font-family:{$arf_input_font_family};
            {$arf_input_font_style_str}
        }";
            
        echo "{$arf_form_cls_prefix}  .ajax-file-remove {
            font-family:{$arf_input_font_family};
        }";
            
        echo "{$arf_form_cls_prefix}  .arfajax-file-upload-img {
            border: medium none !important;
            border-radius: 0 0 0 0 !important;
            -webkit-border-radius: 0 0 0 0 !important;
            -o-border-radius: 0 0 0 0 !important;
            -moz-border-radius: 0 0 0 0 !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            -moz-box-shadow: none !important;
            -o-box-shadow: none !important;
            height: {$file_upload_hw};
            width: {$file_upload_hw};
            float:left;
            margin-top: 0px;
            margin-left:-2px;
            margin-right:2px;
        }";
            
        echo ".arf_file_upload_label{
                float: left;
        }";
        
        echo "{$arf_form_cls_prefix}  .arfformfield .file_main_control{
            max-width:100%;
        }";
        
        echo "{$arf_form_cls_prefix}  .arfformfield .file_main_control{
            min-width: 100% !important;
        }";
    
        echo "{$arf_form_cls_prefix}  .arfformfield.frm_first_half .ajax-file-remove,
        {$arf_form_cls_prefix}  .arfformfield.frm_last_half .ajax-file-remove{
            display: block;
        }";
        if($is_img_crop_enable){
            echo ".cropper-container {
                font-size: 0;
                line-height: 0;
                position: relative;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                direction: ltr;
            }
            .cropper-container img {
                display: block;
                min-width: 0 !important;
                max-width: none !important;
                min-height: 0 !important;
                max-height: none !important;
                width: 100%;
                height: 100%;
                image-orientation: 0deg;
            }
            
            .cropper-wrap-box,
            .cropper-canvas,
            .cropper-drag-box,
            .cropper-crop-box,
            .cropper-modal {
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
            }
            .cropper-wrap-box {
                overflow: hidden;
            }
            .cropper-drag-box {
                opacity: 0;
                background-color: #fff;
            }
            .cropper-modal {
                opacity: .5;
                background-color: #000;
            }
            .cropper-view-box {
                display: block;
                overflow: hidden;
                width: 100%;
                height: 100%;
                outline: 1px solid #39f;
                outline-color: rgba(51, 153, 255, 0.75);
            }
            .cropper-dashed {
                position: absolute;
                display: block;
                opacity: .5;
                border: 0 dashed #eee;
            }
            .cropper-dashed.dashed-h {
                top: 33.33333333%;
                left: 0;
                width: 100%;
                height: 33.33333333%;
                border-top-width: 1px;
                border-bottom-width: 1px;
            }
            .cropper-dashed.dashed-v {
                top: 0;
                left: 33.33333333%;
                width: 33.33333333%;
                height: 100%;
                border-right-width: 1px;
                border-left-width: 1px;
            }
            .cropper-center {
                position: absolute;
                top: 50%;
                left: 50%;
                display: block;
                width: 0;
                height: 0;
                opacity: .75;
            }
            .cropper-center:before,
            .cropper-center:after {
                position: absolute;
                display: block;
                content: ' ';
                background-color: #eee;
            }
            .cropper-center:before {
                top: 0;
                left: -3px;
                width: 7px;
                height: 1px;
            }
            .cropper-center:after {
                top: -3px;
                left: 0;
                width: 1px;
                height: 7px;
            }
            .cropper-face,
            .cropper-line,
            .cropper-point {
                position: absolute;
                display: block;
                width: 100%;
                height: 100%;
                opacity: .1;
            }
            .cropper-face {
                top: 0;
                left: 0;
                background-color: #fff;
            }
            .cropper-line {
                background-color: #39f;
            }
            .cropper-line.line-e {
                top: 0;
                right: -3px;
                width: 5px;
                cursor: e-resize;
            }
            .cropper-line.line-n {
                top: -3px;
                left: 0;
                height: 5px;
                cursor: n-resize;
            }
            .cropper-line.line-w {
                top: 0;
                left: -3px;
                width: 5px;
                cursor: w-resize;
            }
            .cropper-line.line-s {
                bottom: -3px;
                left: 0;
                height: 5px;
                cursor: s-resize;
            }
            .cropper-point {
                width: 5px;
                height: 5px;
                opacity: .75;
                background-color: #39f;
            }
            .cropper-point.point-e {
                top: 50%;
                right: -3px;
                margin-top: -3px;
                cursor: e-resize;
            }
            .cropper-point.point-n {
                top: -3px;
                left: 50%;
                margin-left: -3px;
                cursor: n-resize;
            }
            .cropper-point.point-w {
                top: 50%;
                left: -3px;
                margin-top: -3px;
                cursor: w-resize;
            }
            .cropper-point.point-s {
                bottom: -3px;
                left: 50%;
                margin-left: -3px;
                cursor: s-resize;
            }
            .cropper-point.point-ne {
                top: -3px;
                right: -3px;
                cursor: ne-resize;
            }
            .cropper-point.point-nw {
                top: -3px;
                left: -3px;
                cursor: nw-resize;
            }
            .cropper-point.point-sw {
                bottom: -3px;
                left: -3px;
                cursor: sw-resize;
            }
            .cropper-point.point-se {
                right: -3px;
                bottom: -3px;
                width: 20px;
                height: 20px;
                cursor: se-resize;
                opacity: 1;
            }
            .cropper-point.point-se:before {
                position: absolute;
                right: -50%;
                bottom: -50%;
                display: block;
                width: 200%;
                height: 200%;
                content: ' ';
                opacity: 0;
                background-color: #39f;
            }
            @media (min-width: 768px) {
                .cropper-point.point-se {
                    width: 15px;
                    height: 15px;
                }
            }
            @media (min-width: 992px) {
                .cropper-point.point-se {
                    width: 10px;
                    height: 10px;
                }
            }
            @media (min-width: 1200px) {
                .cropper-point.point-se {
                    width: 5px;
                    height: 5px;
                    opacity: .75;
                }
            }
            .cropper-invisible {
                opacity: 0;
            }
            .cropper-bg {
                background-image: url(\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAQMAAAAlPW0iAAAAA3NCSVQICAjb4U/gAAAABlBMVEXMzMz////TjRV2AAAACXBIWXMAAArrAAAK6wGCiw1aAAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAABFJREFUCJlj+M/AgBVhF/0PAH6/D/HkDxOGAAAAAElFTkSuQmCC\");
            }
            .cropper-hide {
                position: absolute;
                display: block;
                width: 0;
                height: 0;
            }
            .cropper-hidden {
                display: none !important;
            }
            .cropper-move {
                cursor: move;
            }
            .cropper-crop {
                cursor: crosshair;
            }
            .cropper-disabled .cropper-drag-box,
            .cropper-disabled .cropper-face,
            .cropper-disabled .cropper-line,
            .cropper-disabled .cropper-point {
                cursor: not-allowed;
            }
            .arf_crop_div_wrapper{
                background: #ffffff none repeat scroll 0 0 !important;
                border-radius: 5px;
                box-shadow: 0 0 30px 27px rgba(0, 0, 0, 0.12);
                display: none;
                margin-bottom: 40px;
                margin-top: 40px;
                max-width: 99%;
                z-index: 9980;
                padding: 60px 30px 20px;
            }
            .arf_crop_div,
            #arf_crop_div{
                height:275px;width:275px;position:relative;
            }
            .arf_crop_button{
                box-sizing: border-box;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                -o-box-sizing: border-box;
                display: inline-block;
                min-width: 30px;
                min-height: 30px;
                border: #00b2f0 1px solid;
                border-radius: 4px;
                -webkit-border-radius: 4px;
                -moz-border-radius: 4px;
                -o-border-radius: 4px;
                line-height: normal;
                font-size: 16px;
                color: #FFF;
                background-color: #00b2f0;
                text-decoration: none;
                padding: 4px 10px;
                text-align: center;
                cursor: pointer;
            }
            .arf_crop_button:focus,
            .arf_crop_button:hover{
                color: #ffffff;
                text-decoration: none;
                background-color: #00a0d8;
                border-color: transparent !important;
                -webkit-box-shadow: none !important;
                -moz-box-shadow: none !important;
                -o-box-shadow: none !important;
                box-shadow: none !important;
                background-image:none !important; 
            }
            .arf_crop_button{
                margin-left: 93px;
                margin-top: 15px;
            }
            .arf_rotate_button{
                box-sizing: border-box;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                -o-box-sizing: border-box;
                display: inline-block;
                min-width: 30px;
                min-height: 30px;
                border: #00b2f0 1px solid;
                border-radius: 4px;
                -webkit-border-radius: 4px;
                -moz-border-radius: 4px;
                -o-border-radius: 4px;
                line-height: normal;
                font-size: 16px;
                color: #FFF;
                background-color: #00b2f0;
                text-decoration: none;
                padding: 4px 10px;
                text-align: center;
                cursor: pointer;
            }
            .arf_rotate_button:focus,
            .arf_rotate_button:hover{
                color: #ffffff;
                text-decoration: none;
                background-color: #00a0d8;
                border-color: transparent !important;
                -webkit-box-shadow: none !important;
                -moz-box-shadow: none !important;
                -o-box-shadow: none !important;
                box-shadow: none !important;
                background-image:none !important; 
            }
            .arf_rotate_button{
                margin-right: 10px;
                margin-top: 15px;
            }
            .arf_reset_button{
                box-sizing: border-box;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                -o-box-sizing: border-box;
                display: inline-block;
                min-width: 30px;
                min-height: 30px;
                border: #00b2f0 1px solid;
                border-radius: 4px;
                -webkit-border-radius: 4px;
                -moz-border-radius: 4px;
                -o-border-radius: 4px;
                line-height: normal;
                font-size: 16px;
                color: #FFF;
                background-color: #00b2f0;
                text-decoration: none;
                padding: 4px 10px;
                text-align: center;
                cursor: pointer;
            }
            .arf_reset_button:focus,
            .arf_reset_button:hover{
                color: #ffffff;
                text-decoration: none;
                background-color: #00a0d8;
                border-color: transparent !important;
                -webkit-box-shadow: none !important;
                -moz-box-shadow: none !important;
                -o-box-shadow: none !important;
                box-shadow: none !important;
                background-image:none !important; 
            }
            .arf_reset_button{
                margin-right: 15px;
                margin-top: 15px;
            }
            .arf_save_change{
                box-sizing: border-box;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                -o-box-sizing: border-box;
                display: inline-block;
                min-width: 100px;
                min-height: 30px;
                border: #00b2f0 1px solid;
                border-radius: 4px;
                -webkit-border-radius: 4px;
                -moz-border-radius: 4px;
                -o-border-radius: 4px;
                line-height: normal;
                font-size: 16px;
                color: #FFF;
                background-color: #00b2f0;
                text-decoration: none;
                padding: 4px 10px;
                text-align: center;
                cursor: pointer;
                margin-left: 50px;
                min-width: 10px;
            }";
        }
        
        echo ".arf_save_change:focus,
        .arf_save_change:hover{
            color: #ffffff;
            text-decoration: none;
            background-color: #00a0d8;
            border-color: transparent !important;
            -webkit-box-shadow: none !important;
            -moz-box-shadow: none !important;
            -o-box-shadow: none !important;
            box-shadow: none !important;
            background-image:none !important; 
        }
        .arf_save_change{
            margin-right: 15px;
            margin-top: 15px;
        }
        .arf_discription{
            font-style : italic;
            font-size : 15px;
            padding-top : 5px;
            text-align : center;
            color : #666666;
        }
        @media all and (max-width:480px){
            .arf_crop_div_wrapper{
                overflow: hidden;
                height: 100% !important;
                max-height: 100% !important;
                width: 100% !important;
                max-width: 100%;
                left: 0 !important;
                top: 0 !important;
                margin: 0 !important;
                position: fixed !important;
                padding: 60px 20px 30px;
            }
            .arf_crop_div,
            #arf_crop_div{
                margin: 0 auto;
            }
            .arf_crop_button{
                margin-left: 35%;
            }
        }
        .arf_crop_button_wrapper .arf_crop_button{
            margin-left: 0 !important;
            margin-right: 10px;
        }";
        
        echo ".arf_crop_button{
            text-align: center;
        }
        
        .popup_close_icon, .arf_popup_close_btn{
            cursor: pointer;
            float: right;
            height: 25px;
            position: absolute;
            right: 20px;
            top: 18px;
            width: 25px;
        }
        
        .arf_crop_button_wrapper label:hover {
            color: #00b2f0;
            cursor: pointer;
        }
        
        .arf_crop_button_wrapper{
            text-align: left;
        }";
        
        echo "#arf_skip_crop_img{
            position: relative;
            top: 2px;
        }";
    
        echo "{$arf_form_cls_prefix}  .file_name_info {
            font-size:{$arf_input_font_size}px;
            color:{$field_text_color};
            font-family:{$arf_input_font_family};
            {$arf_input_font_style_str}
        }";
    
        echo "{$arf_form_cls_prefix}  .arfajax-file-upload{
            color:#fff;
        }";
        echo "{$arf_form_cls_prefix}  .arfajax-file-upload-img svg{
            fill :#fff;
        }";
        echo "{$arf_form_cls_prefix}  .arfajax-file-upload{
            background : {$base_color};
            border-color:{$base_color};
        }";

        if('right' == $arf_form_alignment){
            echo "{$arf_form_cls_prefix} .arfajax-file-upload{margin-right: 0;}";
        }
    
    }

    if( in_array( 'scale', $loaded_field) ){
        echo "{$arf_form_cls_prefix} .controls .rating { visibility:hidden; height: 0; padding: 0; width: 0; }";
        echo "{$arf_form_cls_prefix} .arf_star_rating_container label.arf_star_rating_label svg path{ fill:#E8E8E8; }";
    
        echo "{$arf_form_cls_prefix} .arf_star_rating_container input:checked ~ label.arf_star_rating_label svg path{";
            echo "fill:{$star_rating_color};";
        echo "}";
        echo "{$arf_form_cls_prefix} .control-group:not([data-view='arf_disabled']) .arf_star_rating_container label.arf_star_rating_label:hover svg path,";
        echo "{$arf_form_cls_prefix} .control-group:not([data-view='arf_disabled']) .arf_star_rating_container label.arf_star_rating_label:hover ~ label.arf_star_rating_label svg path{";
            echo "fill:{$star_rating_color};";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_star_rating_container label.arf_star_rating_label svg path{";
            echo "fill:#E8E8E8;";
        echo "}";

        if('right' == $arf_form_alignment){
            echo "{$arf_form_cls_prefix} .arf_field_type_scale .arf_star_rating_container > label.arf_star_rating_label:first-child {";
                echo "margin-right: 0px !important;";
            echo "}";
        }

        echo "{$arf_form_cls_prefix} .arf_star_rating_container label.arf_star_rating_label {";
            echo "float: right;";
            echo "padding: 0 ;";
            echo "color: #e8e8e8;";
            echo "transition: all .2s;";
            echo "-webkit-transition: all .2s;";
            echo "-o-transition: all .2s;";
            echo "-moz-transition: all .2s;";
            echo "margin-right: 7px;";
            echo "margin-bottom: 0px;";
            echo "cursor: pointer;";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_star_rating_container input {";
            echo "display: none;";
        echo "}";

        echo "{$arf_form_cls_prefix} .arf_star_rating_container {";
            echo "float: none;";
            echo "width: auto;";
            echo "display: inline-block;";
            echo "margin-left:-15px;";
        echo "}";
        
        if( !$preview ) {
            $all_scale_fields = $arfform->arf_select_db_data( true, '', $MdlDb->fields, 'id,field_options', 'WHERE type = %s AND form_id = %d', array( 'scale', $form_id ) );
        } else {
            $all_scale_fields = json_decode( json_encode( $scale_control_array ) );
        }
        
    
        if( !empty( $all_scale_fields ) ){
            foreach( $all_scale_fields as $field ){
                if( is_array( $field->field_options ) ){
                    $field->field_options = json_encode( $field->field_options );
                }
                $fopts = arf_json_decode( $field->field_options );
                $star_size = $fopts->star_size;
                echo "{$arf_form_cls_prefix} .arf_star_rating_container_".$field->id."{";
                    echo "float:none;";
                    echo "width:auto;";
                    echo "display:inline-block;";
                    echo "margin-left:-15px;";
                echo "}";
                echo "{$arf_form_cls_prefix} .arf_star_rating_container_".$field->id." input{";
                    echo "display:none;";
                echo "}";
                echo "{$arf_form_cls_prefix} .arf_star_rating_container_{$field->id} label.arf_star_rating_label:not(.arf_star_rating_label_null),";
                echo "{$arf_form_cls_prefix} .arf_star_rating_container_{$field->id} label.arf_star_rating_label:not(.arf_star_rating_label_null) svg{";
                    echo "width:{$star_size}px;";
                    echo "height:".($star_size-1)."px;";
                echo "}";
                echo "{$arf_form_cls_prefix} .arf_star_rating_container_{$field->id} label.arf_star_rating_label.arf_star_rating_label_null{";
                    echo "width:10px !important;";
                    echo "height:{$star_size}px;";
                    echo "margin:0px;";
                echo "}";
            }
        }
    }
    
    if( in_array( 'like', $loaded_field) ){
        echo "{$arf_form_cls_prefix} .like_container .popover {
            background-color: #000000 !important;
            color:#FFFFFF !important;
            width:auto;
        }";
        echo "{$arf_form_cls_prefix} .like_container .popover .popover-content {
            color:#FFFFFF !important;
        }";
        echo "{$arf_form_cls_prefix} .like_container .popover .popover-title {
            display:none;
        }";
        echo "{$arf_form_cls_prefix} .like_container .popover.top .arrow:after {
            border-top-color: #000000 !important;
        }";
        echo "{$arf_form_cls_prefix} .like_container {
            display: inline-block;
            width:100%;
        }";
        echo "{$arf_form_cls_prefix} .like_container .arf_like_btn,
        {$arf_form_cls_prefix} .like_container .arf_dislike_btn {
            display: inline-block;
            width:40px;
            height:40px;
            -webkit-border-radius:40px;
            -o-border-radius:40px;
            -moz-border-radius:40px;
            border-radius:40px;
            background:#B4BACA;
            margin-right:10px;
            margin-bottom: 0px;
            -webkit-box-shadow: none !important;
            -o-box-shadow:      none !important;
            -moz-box-shadow:    none !important;
            box-shadow:         none !important;
            position:relative;
            cursor:pointer;
            padding:0;
        }";
        echo "{$arf_form_cls_prefix} .like_container .arf_like_btn svg,
        {$arf_form_cls_prefix} .like_container .arf_dislike_btn svg {
            position:absolute;
            -webkit-transform:translate(-50%, -50%) scale(0.8);
            -o-transform:translate(-50%, -50%) scale(0.8);
            -moz-transform:translate(-50%, -50%) scale(0.8);
            transform:translate(-50%, -50%) scale(0.8);
        }";
        echo "{$arf_form_cls_prefix} .like_container .arf_like_btn svg{
            top:53%;
            left:52%;
        }";
        echo "{$arf_form_cls_prefix} .like_container .arf_dislike_btn svg{
            top:55%;
            left:48%;
        }";
    
        echo "@media (min-width:1900px) {
            {$arf_form_cls_prefix}  .like_container .arf_like_btn,
            {$arf_form_cls_prefix}  .like_container .arf_dislike_btn {
                width:46px;
                height:46px;
                -webkit-border-radius:46px;
                -o-border-radius:46px;
                -moz-border-radius:46px;
                border-radius:46px;
            }
            {$arf_form_cls_prefix}  .like_container .arf_like_btn svg {
                top:52%;
                left:53%;
                -webkit-transform:translate(-50%, -50%) scale(0.9);
                -o-transform:translate(-50%, -50%) scale(0.9);
                -moz-transform:translate(-50%, -50%) scale(0.9);
                transform:translate(-50%, -50%) scale(0.9);
            }
            {$arf_form_cls_prefix}  .like_container .arf_dislike_btn svg {
                top:52%;
                left:48%;
                -webkit-transform:translate(-50%, -50%) scale(0.9);
                -o-transform:translate(-50%, -50%) scale(0.9);
                -moz-transform:translate(-50%, -50%) scale(0.9);
                transform:translate(-50%, -50%) scale(0.9);
            }
        }";
    
        echo "{$arf_form_cls_prefix} .like_container .popover{
            background-color: #000000 !important;
            width: auto !important;
        }";
    
        echo "{$arf_form_cls_prefix} .like_container .popover.top .arrow:after, #cs-content .like_container .popover.top .arrow{
            border-top-color: #000000 !important;
        }";
    
        echo "{$arf_form_cls_prefix} .like_container .arf_like_btn.active{
            background:{$like_btn_color};
        }";
    
        echo "{$arf_form_cls_prefix} .like_container .arf_dislike_btn.active{
            background:{$dislike_btn_color};
        }";
    }

    if( in_array( 'arfslider', $loaded_field) ){
        echo "{$arf_form_cls_prefix}  .noUi-connects{
            background:{$slider_track_color} !important;
        }";
    
        echo "{$arf_form_cls_prefix}  .arf_slider_control {
            margin-top:10px;
        }";
        echo "{$arf_form_cls_prefix}  .arf_slider_control .arf_slider {
            max-width:100%;
            overflow:visible;
        }";
        echo "{$arf_form_cls_prefix}  .arf_slider_control .arf-slider-track, 
        {$arf_form_cls_prefix}  .arf_slider_control .slider, 
        {$arf_form_cls_prefix}  .arf_slider_control .arf-slider-handle {
            cursor:pointer;
        }";
        echo "{$arf_form_cls_prefix}  .arf_slider_control .arfslider {
            height:0;
            opacity:0;
            filter:alpha(opacity=0);
        }";
        echo "{$arf_form_cls_prefix}  .arf_slider_control .arf-slider-handle.hide {
            opacity:0;
            filter:alpha(opacity=0);
            display:none;
        }";
    
        echo "{$arf_form_cls_prefix}  .arf_slider_control .arftooltip {
            z-index:1;
        }";
        echo "{$arf_form_cls_prefix}  .arf_slider_control .arftooltip.top .tooltip-arrow {
            border-color:transparent;
            border-top-color:#000000;
        }";
        echo "{$arf_form_cls_prefix}  .arf_slider_control .arf-slider-handle:not(.triangle) {
            border-color:transparent;
        }";
        echo "{$arf_form_cls_prefix}  .arf_slider_control .arftooltip.top {
            top:-32px !important;
            margin-left: auto !important;
            font-family:Arial, Helvetica, sans-serif;
        }";
    
        echo "{$arf_form_cls_prefix}  .arfsliderhover .arf_slider_control .noUi-tooltip{ display:none; }";
    
        echo "{$arf_form_cls_prefix}  .arfsliderhover:hover .arf_slider_control .noUi-tooltip{ display: block; }";
    
        echo "{$arf_form_cls_prefix}  .arfsliderhover .arf_editor_slider_class .noUi-tooltip{ display: none; }";
    
        echo "{$arf_form_cls_prefix}  .arfsliderhover:hover .arf_editor_slider_class .noUi-tooltip{ display: block; }";
    
        echo "{$arf_form_cls_prefix}  .noUi-handle.noUi-handle-lower{
            top: -7px !important;
            width: 18px !important;
            height: 18px !important;
            background-color: #337ab7 !important;
            filter: none !important;
            -webkit-box-shadow: inset 0 1px 0 rgb(255 255 255 / 20%), 0 1px 2px rgb(0 0 0 / 5%);
            box-shadow: inset 0 1px 0 rgb(255 255 255 / 20%), 0 1px 2px rgb(0 0 0 / 5%) !important;
            border: 0px solid transparent !important;
            border-radius: 50% !important;
            right: -9px;
        }";
    
        echo "{$arf_form_cls_prefix}  .arf_editor_slider_class.square .noUi-handle.noUi-handle-lower,
        {$arf_form_cls_prefix}  .arfslider_front.square .noUi-handle.noUi-handle-lower{
            border-radius: 0px !important;
        }";
    
        echo "{$arf_form_cls_prefix}  .arf_editor_slider_class.triangle .noUi-handle.noUi-handle-lower,
        {$arf_form_cls_prefix}  .arfslider_front.triangle .noUi-handle.noUi-handle-lower{
            position: absolute !important;
            top: 50% !important;
            -ms-transform: translateY(-46%) !important;
            transform: translateY(-46%) !important;
            border-width: 0 10px 10px 10px !important;
            width: 0 !important;
            height: 0 !important;
            border-bottom-color: #2e6da4 !important;
            margin-top: 5px !important;
            background-color: transparent !important;
            border-radius: 0px !important;
            right: -10px;
        }";
        
        echo "{$arf_form_cls_prefix}  .noUi-handle.noUi-handle-upper{
                position: absolute;
            top: -7 !important;
            width: 18px !important;
            height: 18px !important;
            background-color: #337ab7 !important;
            filter: none !important;
            -webkit-box-shadow: inset 0 1px 0 rgb(255 255 255 / 20%), 0 1px 2px rgb(0 0 0 / 5%);
            box-shadow: inset 0 1px 0 rgb(255 255 255 / 20%), 0 1px 2px rgb(0 0 0 / 5%) !important;
            border: 0px solid transparent !important;
            border-radius: 50% !important;
        }";
    
        echo "{$arf_form_cls_prefix}  .arf_editor_slider_class.square .noUi-handle.noUi-handle-upper,
        {$arf_form_cls_prefix}  .arfslider_front.square .noUi-handle.noUi-handle-upper{
            border-radius: 0px !important;
        }";
    
        echo "{$arf_form_cls_prefix}  .arf_editor_slider_class.triangle .noUi-handle.noUi-handle-upper,
        {$arf_form_cls_prefix}  .arfslider_front.triangle .noUi-handle.noUi-handle-upper{
           position: absolute !important;
           top: 50% !important;
          -ms-transform: translateY(-50%) !important;
          transform: translateY(-50%) !important;
          border-width: 0 10px 10px 10px !important;
          width: 0 !important;
          height: 0 !important;
          border-bottom-color: #2e6da4 !important;
          margin-top: 5px !important;
          background-color: transparent !important;
          border-radius: 0px !important;
          right: -11px;
        }
        .noUi-connects{
          position: absolute !important;
            background-image: -webkit-linear-gradient(top, #f9f9f9 0%, #f5f5f5 100%) !important;
            background-image: -o-linear-gradient(top, #f9f9f9 0%, #f5f5f5 100%) !important;
            background-image: linear-gradient(to bottom, #f9f9f9 0%, #f5f5f5 100%) !important;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fff9f9f9', endColorstr='#fff5f5f5', GradientType=0) !important;
            background-repeat: repeat-x !important;
            -webkit-box-shadow: inset 0 -1px 0 rgb(0 0 0 / 15%);
            box-shadow: inset 0 -1px 0 rgb(0 0 0 / 15%);
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box !important;
            border-radius: 4px !important;
        }
        .noUi-target{
            height: 7px !important;
            width: 100% !important;
            margin-top: 8px !important;
            top: 50% !important;
            left: 0 !important;
            border-radius: 0px !important;
            border:  0px !important;
            background: red !important;
        }";
    
        echo ".arf_slider_input{
            display: none;
        }";
        echo ".noUi-handle:before, .noUi-handle:after {
            display: none !important;
            background-color: #337ab7 !important;
        }";
        echo "[disabled].noUi-target, [disabled].noUi-handle, [disabled] .noUi-handle {
             background-image: -webkit-linear-gradient(top, #dfdfdf 0%, #bebebe 100%);
          background-image: -o-linear-gradient(top, #dfdfdf 0%, #bebebe 100%);
          background-image: linear-gradient(to bottom, #dfdfdf 0%, #bebebe 100%);
          filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffdfdfdf', endColorstr='#ffbebebe', GradientType=0);
          background-repeat: repeat-x;
        }
        .noUi-tooltip{
            color: #fff !important;
            background-color: #000 !important;
            min-width: 30px !important;
            font-size: 11px !important;
        }";
        
        echo "{$arf_form_cls_prefix}  .noUi-horizontal .noUi-handle{
            background:{$base_color} !important;
        }
    
        {$arf_form_cls_prefix}  .triangle .noUi-handle.noUi-handle-upper,
        {$arf_form_cls_prefix}  .triangle .noUi-handle.noUi-handle-lower{
            border-bottom-color:{$base_color} !important;
            background: transparent !important;
        }
        
        {$arf_form_cls_prefix}  .noUi-connect{
            background:{$slider_selection_color} !important;
        }";
    }

    if( in_array( 'colorpicker', $loaded_field) ){

        $colorpickerpadding = "0";
        $padding_array = explode(" ", $arf_field_inner_padding);
        $colorpickerpadding = isset($padding_array[1]) ? $padding_array[1] : "0";
        $colorpickerpadding = trim(str_replace('px', '', $colorpickerpadding));
    
        $colorpickerpaddingtop = isset($padding_array[0]) ? $padding_array[0] : "0";
        $colorpickerpaddingtop = trim(str_replace('px', '', $colorpickerpaddingtop));
    
        $colorpickerfield_border_width = trim(str_replace('px', '', $field_border_width));
        $colorpickerheight = ( ($arf_input_font_size) + ($colorpickerpaddingtop * 2) );
    
        $colorpickerheight_new = ( ($arf_input_font_size) + ($colorpickerpaddingtop * 2) ) + (2 * $colorpickerfield_border_width);
    
        $colorpickerheight_new = $colorpickerheight_new < 20 ? 20 : $colorpickerheight_new;
    
        $arfcolorpickerfullheight = $colorpickerheight_new;
        $colorpickerwidth1 = 148;
        $colvaluewidth = 109;
        $arfcolorpickerfullwidth = 15;
        $arfcolorpickerfullpadding = "0 13px";
        if ($colorpickerheight_new < 30) {
            $arfcolorpickerheight = $colorpickerheight_new - 6;
            $colorpickerpaddingtop = 6;
            $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
            $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 15 - 5;
            $colrpick_upload_bg = '16';
        } else if ($colorpickerheight_new < 36) {
            $arfcolorpickerheight = $colorpickerheight_new - 8;
            $colorpickerpaddingtop = 8;
            $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
            $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 15 - 5;
            $colrpick_upload_bg = '16';
        } else if ($colorpickerheight_new < 41) {
            $arfcolorpickerheight = $colorpickerheight_new - 10;
            $colorpickerpaddingtop = 10;
            $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
            $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 15 - 5;
            $colrpick_upload_bg = '16';
        } else if ($colorpickerheight_new < 46) {
            $arfcolorpickerheight = $colorpickerheight_new - 12;
            $colorpickerpaddingtop = 12;
            $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
            $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 15 - 5;
            $colrpick_upload_bg = '16';
        } else if ($colorpickerheight_new < 51) {
            $arfcolorpickerheight = $colorpickerheight_new - 14;
            $colorpickerpaddingtop = 14;
            $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
            $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 24 - 5;
            $arfcolorpickerfullwidth = 24;
            $colrpick_upload_bg = '22';
        } else {
            $arfcolorpickerheight = $colorpickerheight_new - 16;
            $colorpickerpaddingtop = 16;
            $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
            $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 24 - 5;
            $arfcolorpickerfullwidth = 24;
            $colrpick_upload_bg = '22';
        }
    
        $colorvaluemargin = $arfcolorpickerfullwidth + $colorpickerfield_border_width;
    
        $border_radius_pxx = str_replace('px', '', $field_border_radius);
        $border_radius_px2 = ( $border_radius_pxx < 2 ) ? 0 : $border_radius_pxx - 1;
        $border_radius_pxx = ( $border_radius_pxx < 3 ) ? 0 : $border_radius_pxx - 2;
        echo "{$arf_form_cls_prefix}  input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider).arf_editor_colorpicker{
            width:100px !important;";
            echo "border-top-left-radius:0px !important;
            border-bottom-left-radius:0px !important;";
        echo "}";
    
        echo "{$arf_form_cls_prefix}  input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider) {";
            $field_border_width_select_custom = $field_border_width;
            if(empty($field_border_width_select)) {
                $field_border_width_select_custom = 1;
            }
            echo "border-top:{$field_border_width_select_custom}px {$field_border_style} {$field_border_color} !important;
            border-bottom:{$field_border_width_select_custom}px {$field_border_style} {$field_border_color} !important;
            border-right:{$field_border_width_select_custom}px {$field_border_style} {$field_border_color} !important;
            border-left:{$field_border_width_select_custom}px {$field_border_style} {$field_border_color} !important;
        }";
        
        if( $is_form_save ){
            echo "{$arf_form_cls_prefix}  .arf_editor_prefix.arf_colorpicker_prefix_editor {
                border-top: {$field_border_width_select_custom}px  {$field_border_style}  {$field_border_color} !important;
                border-bottom: {$field_border_width_select_custom}px  {$field_border_style}  {$field_border_color} !important;";
                echo "border-left: {$field_border_width_select_custom}px  {$field_border_style}  {$field_border_color} !important;
                border-right:0px  {$field_border_style}  {$field_border_color} !important;";
            echo "}";
        
            echo "{$arf_form_cls_prefix}  .arf_editor_prefix.arf_colorpicker_prefix_editor{";
                echo "-webkit-border-radius:{$field_border_radius} 0px 0px  {$field_border_radius};
                -o-border-radius:{$field_border_radius} 0px 0px  {$field_border_radius};
                -moz-border-radius:{$field_border_radius} 0px 0px  {$field_border_radius};
                border-radius:{$field_border_radius} 0px 0px  {$field_border_radius};";
            echo "}";

            echo "{$arf_form_cls_prefix}  .controls .arfcolorpickerfield .arfcolorimg,{$arf_form_cls_prefix}  .arf_editor_prefix.arf_colorpicker_prefix_editor{
                background:{$prefix_suffix_bg_color} !important;
                border-color: {$field_border_color};
            }";
            echo "{$arf_form_cls_prefix}  .controls .arfcolorpickerfield .arfcolorimg svg path,{$arf_form_cls_prefix}  .arf_editor_prefix.arf_colorpicker_prefix_editor .paint_brush_position svg path{
                fill:{$prefix_suffix_icon_color};    
            }";
        }
    
        echo "{$arf_form_cls_prefix}  .arfcolorpickerfield {
            border:{$field_border_width_select_custom}px {$field_border_style} {$field_border_color};
            width:80px;
            height:30px;
            -webkit-border-radius:{$field_border_radius};
            -o-border-radius:{$field_border_radius};
            -moz-border-radius:{$field_border_radius};
            border-radius:{$field_border_radius};
            overflow:hidden;
            cursor:pointer;
        }";
        
        echo "{$arf_form_cls_prefix}  .arfcolorpickerfield .arfcolorimg {
            height:30px;
            width:20px;
            background:{$prefix_suffix_bg_color} !important;
            background-repeat:no-repeat;
            background-position:center center;";
            echo "border-right:{$field_border_width_select_custom}px {$field_border_style} {$field_border_color};
            float:left;";
            echo "font-size:22px;
            padding:0 3px;
        }";
        
        echo "{$arf_form_cls_prefix}  .arfcolorpickerfield .arfcolorimg i.fa-paint-brush {
            height:{$arfcolorpickerfullheight}px;
            line-height:{$arfcolorpickerfullheight}px;
            color:{$prefix_suffix_icon_color};
        }";
            
        echo "{$arf_form_cls_prefix}  .arfcolorvalue {
            color: #333333;
            padding:8px 0px 0px 30px;height:23px;
            background:{$field_bg_color};
            font-family:Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height:normal;
            text-transform:lowercase;
            text-align:{$arf_input_field_text_align};
        }";
            
        echo "{$arf_form_cls_prefix}  .arf_colorpicker_control .arfhiddencolor {
            height:1px;
            max-height:1px !important;
            width:1px !important;
            max-width:1px !important;
            border:none !important;
            background:none !important;
            padding:0 !important;
            opacity:0;
            filter:alpha(opacity=0);
            float:left;
        }";
        echo "{$arf_form_cls_prefix}  .arfcolorpickerfield {
            display: inline-block;
            -webkit-box-sizing: content-box !important;
            -o-box-sizing: content-box !important;
            -moz-box-sizing: content-box !important;
            box-sizing: content-box !important;
        }";
        echo "{$arf_form_cls_prefix}  .arfcolorpickerfield .arfcolorimg, 
        {$arf_form_cls_prefix}  .arfcolorpickerfield .arfcolorvalue {
            -webkit-box-sizing: content-box !important;
            -o-box-sizing: content-box !important;
            -moz-box-sizing: content-box !important;
            box-sizing: content-box !important;
        }
        
        {$arf_form_cls_prefix}  .arfcolorpickerfield .arfcolorimg {
            background: #e7e8ec !important;
        }
        {$arf_form_cls_prefix}  .arfcolorpickerreset {
            display: inline-block;
            margin-left: 15px;
            height: 16px;
            cursor:pointer;
        }
        
        .arf_js_colorpicker{
            z-index: 9999999 !important;
        }
        
        .colpick, div.color-picker {
            z-index:9999;
        }
        .colpick .arf_add_favourite_color {
            display: none;
        }
        div.color-picker {
            border:1px solid #C0C1C3;
            background-color:#E7E8EC;
            position: absolute;
            left: 0px;
            top: 3px;
            padding:8px;
            border-radius:3px;
            -o-border-radius:3px;
            -webkit-border-radius:3px;
            -moz-border-radius:3px;
            z-index:100003;
        }
        div.color-picker ul {
            list-style: none;
            padding: 0px;
            margin: 0px;
            float: left;
        }
        div.color-picker ul li {
            display: block;
            width: 20px;
            height: 20px;
            margin: 2px;
            float: left;
            cursor: pointer;
        }
        div.arf-color-picker-heading {
            display:inline-block;
            padding:0 0 5px 0;
            font-size:12px;
            font-weight:bold;
            font-family:Arial, Helvetica, sans-serif;
            color:#333333;
        }
        
        .colpick.colpick_hex {
            z-index:99999;
        }
        .colpick_hex_field, .colpick_hex_field .colpick_field_letter {
            -webkit-box-sizing: content-box;
            -o-box-sizing: content-box;
            -moz-box-sizing: content-box;
            box-sizing: content-box;
        }
        .colpick_hex_field input, 
        .colpick_hex_field input:focus {
            padding:0 !important;
            margin:0 !important;
            line-height:25px !important;
            width:59px !important;
            background:none !important;
            border:none !important;
            box-shadow:none;
            -webkit-box-shadow:none;
            -moz-box-shadow:none;
            -o-box-shadow:none;
        }
        
        .arf_color_picker_input_div {
            float: left;
            padding-left: 115px;
            padding-top: 155px;
            width: 100%;
            z-index: 2147483647;
            box-sizing:border-box;
            -moz-box-sizing:border-box;
            -webkit-box-sizing:border-box;
            -o-box-sizing:border-box;
        }
        .wp-admin .arf_color_picker_input,
        .wp-admin .arf_color_picker_input:focus{
            height:26px;
        }
        .arf_color_picker_input,
        .arf_color_picker_input:focus {
            border: 1px solid #c9c9c9;
            float: left;
            height: 26px;
            line-height: 22px;
            margin: 0;
            width: 70px;
            font-size: 16px;
            text-align: center;
        }
        .arf_preview_modal_body .arf_color_picker_input,
        .arf_preview_modal_body .arf_color_picker_input:focus{
            height:22px;
        }
        
        .color_input_hex_div {
            background-color: #c9c9c9;
            float: left;
            height: 26px;
            padding-left: 3px;
            text-align: center;
            width: 24px;
            line-height: normal;
        }
        .arf_color_picker_input_div_advanced {
            float: left;
            margin-left: 130px;
            margin-top: 185px;
            position: absolute;
            width: 100%;
            z-index: 2147483647;
        }
        .arf_add_favorite_color {
            float: left;
            height: 30px;
            position: absolute;
            width: 100%;
            line-height:30px;
            z-index:9999999999;
        }
        .arf_add_favorite_color_btn {
            cursor: pointer;
            float: right;
            height: 25px;
            line-height: normal;
            position: relative;
            right: 6px;
            top: 2px;
            width: 25px;
            color: #a9a9a9;
        }
        .arf_favorite_color_buttons {
            float: left;
            margin-left: 13px;
            width: auto;
        }
        .select_from_fav_color {
            border: 1px solid;
            float: left;
            height: 20px;
            margin-right: 5px;
            width: 20px;
            cursor:pointer;
        }
        .arf_add_favorite_color_btn i {
            font-size: 23px;
        }
        .arf_add_favorite_color_btn i:hover:before {
            content: \"\\f004\" !important;
        }";
    }

    if( in_array( 'arf_smiley', $loaded_field) ){
        echo "{$arf_form_cls_prefix} .arf_smiley_btn{
            width: 32px;
            height: 32px;
            padding: 0;
            margin-bottom: 0px;
            text-align: center;
            display:inline-block;
            margin-right:8px;
        }";

        echo "{$arf_form_cls_prefix} .arf_smiley_input{";
            echo "min-width: auto !important;";
            echo "width:auto !important;";
        echo "}";
    
        echo "{$arf_form_cls_prefix} .arf_smiley_btn .arf_smiley_icon{
            float:none;
            display:inline-block;
        }";
        echo "{$arf_form_cls_prefix} .arf_smiley_btn .arf_smiley_img{
            display:inline-block;
            background-size: cover;
            width: 32px !important;
            height: 32px !important;
        }";
        echo "{$arf_form_cls_prefix} .arf_smiley_btn .arf_smiley_img{
            pointer-events: none;
        }";
    
        echo "{$arf_form_cls_prefix}  .arf_smiley_container .arf_smiley_img:hover,
        {$arf_form_cls_prefix}  .arf_smiley_container .arf_smiley_icon:hover,
        {$arf_form_cls_prefix}  input[type=radio].arf_smiley_input:checked + .arf_smiley_btn .arf_smiley_img,
        {$arf_form_cls_prefix}  input[type=radio].arf_smiley_input:checked + .arf_smiley_btn .arf_smiley_icon{
            -ms-transform: scale(1.10);
            -moz-transform: scale(1.10);
            -webkit-transform: scale(1.10);
            transform: scale(1.10);
            opacity: 1;
            display:inline-block;
        }";
    }

    if( in_array('password',$loaded_field)){

        echo "{$arf_form_cls_prefix} .arf_strenth_mtr .inside_title{ 
            font-family: {$arf_input_font_family};
            font-size: {$description_font_size}px;
            color: {$field_label_txt_color};
            text-align: left;
            line-height: normal;
            float: left;
            width: 110px;
        }";

        echo "{$arf_form_cls_prefix} .arf_strenth_meter{
            width: 130px;
            margin-top:3px;
            float:left;
        }";

        echo "{$arf_form_cls_prefix} .arf_strenth_meter .arfp_box {
            background-color: #EEEEEE;
            border: 1px solid #DDDDDD;
            height:7px;
            width:12px;
            margin-right:5px;
            float:left;
        }";

        echo "{$arf_form_cls_prefix} .arf_strenth_meter.short .arfp_box {
            background-color:#FE0201;
        }";

        echo "{$arf_form_cls_prefix} .arf_strenth_meter.bad .arfp_box {
            background-color:#FF7A01;
        }";

        echo "{$arf_form_cls_prefix} .arf_strenth_meter.good .arfp_box {
            background-color:#FEE801;
        }";

        echo "{$arf_form_cls_prefix} .arf_strenth_meter.strong .arfp_box {
            background-color:#247C0B;
        }";
            
    }

    if( in_array( 'arf_switch', $loaded_field) ){
        echo "{$arf_form_cls_prefix} input[type=checkbox]:checked+.arf_js_field_switch 
        {
            background:{$base_color} !important ;
            border-color:{$base_color} !important;
        }";
        
        echo "{$arf_form_cls_prefix} .arf_js_field_switch_wrapper {
            float: left;
            width: 38px;
            height: 19px;
            position: relative;
            -webkit-transition: all 0.3s ease-in-out 0s;
            -moz-transition: all 0.3s ease-in-out 0s;
            -o-transition: all 0.3s ease-in-out 0s;
            -ms-transition: all 0.3s ease-in-out 0s;
            transition: all 0.3s ease-in-out 0s;
        }";
        
        echo "{$arf_form_cls_prefix} .arf_js_field_switch_label {
            float: left;
            width: auto;
            cursor: pointer;
            margin: 5px 3px;
            font-family: Asap-Regular;
            font-size: 14px;
        }";

        echo "{$arf_form_cls_prefix} .arf_js_field_switch_label span{";
            echo "color:{$field_label_txt_color} !important;";
        echo "}";
        
        echo "{$arf_form_cls_prefix} .arf_js_field_switch_wrapper input[type=checkbox], 
        {$arf_form_cls_prefix} .arf_js_field_switch_wrapper input[type=checkbox] {
            float: left;
            width: 100% !important;
            height: 100% !important;;
            opacity: 0;
            cursor: pointer;
            position: absolute !important;;
            z-index: 2;
            left: 0;
        }";
        
        echo "{$arf_form_cls_prefix} .arf_js_field_switch_wrapper .arf_js_field_switch {
            float: left;
            width: 38px;
            height: 19px;
            margin-top: 5px;
            border: 2px solid #d3d3d8;
            background: #d3d3d8;
            border-radius: 30px;
            -webkit-border-radius: 30px;
            -o-border-radius: 30px;
            -moz-border-radius: 30px;
            position: relative;
            box-sizing: border-box !important;
            -webkit-box-sizing: border-box !important;
            -o-box-sizing: border-box !important;
            -moz-box-sizing: border-box !important;
        }";
        
        
        echo "{$arf_form_cls_prefix} .arf_js_field_switch_wrapper .arf_js_field_switch::before {
            float: left;
            width: 15px;
            height: 15px;
            position: relative;
            border-radius: 15px;
            -webkit-border-radius: 15px;
            -o-border-radius: 15px;
            -moz-border-radius: 15px;
            background: #fff;
            content: '';
            z-index: 1;
            left: 0;
            -webkit-transition: all 0.3s ease-in-out 0s;
            -moz-transition: all 0.3s ease-in-out 0s;
            -o-transition: all 0.3s ease-in-out 0s;
            -ms-transition: all 0.3s ease-in-out 0s;
            transition: all 0.3s ease-in-out 0s;
        }";
        
        echo "{$arf_form_cls_prefix}  .arf_js_field_switch_wrapper input[type=checkbox]:checked+.arf_js_field_switch::before, 
        {$arf_form_cls_prefix} .arf_js_field_switch_wrapper input[type=checkbox]:checked+.arf_js_field_switch::before {
            float: left;
            left: 19px;
        }";
        
        echo "{$arf_form_cls_prefix} .arf_js_field_switch_wrapper.arf_no_transition, 
        {$arf_form_cls_prefix} .arf_js_field_switch_wrapper.arf_no_transition .arf_js_field_switch::before {
            -webkit-transition: all 0s ease-in-out 0s !important;
            -moz-transition: all 0s ease-in-out 0s !important;
            -o-transition: all 0s ease-in-out 0s !important;
            -ms-transition: all 0s ease-in-out 0s !important;
            transition: all 0s ease-in-out 0s !important;
        }";

        if( 'right' == $arf_form_alignment ){
            echo "{$arf_form_cls_prefix} .arf_input_field_switch_wrapper.controls {
                width: auto;
            }";
            echo "{$arf_form_cls_prefix} .edit_field_type_arf_switch .controls {
                width: auto;
            }";
        }
    }

    if( in_array( 'arf_wysiwyg', $loaded_field) ){
        echo "{$arf_form_cls_prefix} .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg-box .trumbowyg-editor p{
            font-size:{$arf_input_font_size}px;
            color:{$field_text_color};
            font-family:{$arf_input_font_family};
            {$arf_input_font_style_str}
            clear:none;
            margin-bottom:0; 
        }";
        echo "{$arf_form_cls_prefix} .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg-box{
            color:{$field_text_color};
            border-color:{$field_border_color};
            border-width:{$field_border_width};
            border-style:{$field_border_style};";
            if (isset($arf_input_field_width_unit) && $arf_input_field_width_unit == '%') {
                echo 'width:100%;';
            } else {
                echo (!isset($arf_input_field_width) || $arf_input_field_width == '') ? 'width:auto;' : 'width:'.$arf_input_field_width.';';
            }
            echo"max-width:100%;
            -webkit-box-sizing:border-box;
            -moz-box-sizing:border-box;
            -o-box-sizing:border-box; 
            box-sizing:border-box; 
            -webkit-box-shadow:none;
            -o-box-shadow:none;
            -moz-box-shadow:none;
            box-shadow:none; 
            direction:{$arf_input_field_direction}; 
            outline:none; margin-bottom:0;
        }";

        echo "{$arf_form_cls_prefix} .arf_wysiwyg_fullscreen{";
            echo "z-index:99999;";
            echo "position:fixed;";
        echo "}";
        
        
        echo "{$arf_form_cls_prefix} .trumbowyg-button-group button{
            background-color: inherit !Important;
        }
        {$arf_form_cls_prefix} .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg-dropdown button,
        {$arf_form_cls_prefix} .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg-button-group button{
            background-color: inherit !Important;
            border-radius: 0px !important;
            box-shadow: none !important;
            margin-right: 0px !important;
        }
        
        {$arf_form_cls_prefix} .trumbowyg-button-pane button.trumbowyg-active,
        {$arf_form_cls_prefix} .trumbowyg-button-pane button:not(.trumbowyg-disable):focus,
        {$arf_form_cls_prefix} .trumbowyg-button-pane button:not(.trumbowyg-disable):hover{
            background:  #fff !important;
        } ";
        
        if( preg_match( '/rounded/', $arf_mainstyle ) ){
            echo "{$arf_form_cls_prefix} .arf_rounded_form .control-group .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg-box {
                border-radius: 0px !important;
            }";
        }

        echo ".trumbowyg-modal-box .trumbowyg-input-infos label,
        .trumbowyg-modal-box .trumbowyg-input-infos label span{
            font-weight: normal;
            color: inherit;
            font-size: inherit;
        }
        
        .trumbowyg-modal-box .trumbowyg-modal-button.trumbowyg-modal-submit{
            padding: inherit;
            background: #2bc06a !important;
            color:  #fff !important;
        }
        
        .trumbowyg-modal-box .trumbowyg-modal-button.trumbowyg-modal-reset{
            padding: inherit;
            background: #e6e6e6 !important;
            color:  inherit !important;
        }
        
        .arf_field_type_arf_wysiwyg .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg .trumbowyg-button-pane{
            z-index: 8 !important;
        }
        
        .arf_wysiwyg_fullscreen{
            z-index: 99999;
        }
    
        {$arf_form_cls_prefix} .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg-box .trumbowyg-editor{
            background-color:{$field_bg_color};
        }
        
        {$arf_form_cls_prefix} .controls .arf_field_wysiwyg_container .arf_field_wysiwyg_input .trumbowyg-box .trumbowyg-editor:focus{";
            if( 1 == $arfmainfield_opacity ){
                echo "background:transparent;";
            } else {
                echo "background:{$field_focus_bg_color};";
            }
        echo "}";
    }

    if( in_array( 'section', $loaded_field ) || in_array( 'arf_repeater', $loaded_field ) ){
        echo "{$arf_form_cls_prefix} .arf_heading_div h2.arf_sec_heading_field{
            font-family: {$arf_section_font_family};
            font-size: {$arf_section_font_size}px;
            {$arf_section_font_style_str}
            text-align:$arf_label_align;
        }";
        echo "{$arf_form_cls_prefix} .arf_heading_div{
            padding:{$arf_section_padding};
        }";
    
        echo "{$arf_form_cls_prefix} .arf_heading_div{
            clear:both;
        }";

        echo "{$arf_form_cls_prefix} h2.pos_left, {$arf_form_cls_prefix} h2.pos_top, {$arf_form_cls_prefix} h2.pos_right{
            color:{$field_label_txt_color};
        }";
        
        echo "{$arf_form_cls_prefix} .arf_heading_div{  background:{$section_background}; }";
    }

    if( in_array( 'arf_repeater', $loaded_field ) ){
        echo "{$arf_form_cls_prefix} .edit_field_type_arf_repeater .arf_repeater_editor_add_icon,
        {$arf_form_cls_prefix} .arf_repeater_field .arf_repeater_add_new_button {";
            echo "float: right;";
            echo "background:{$submit_bg_color};
            color: #fff;
            border: 2px solid {$submit_bg_color};
            cursor:pointer;
            line-height:normal;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_repeater_field .arf_repeater_remove_new_button {";
            echo "float: right;";
            echo "background: #fff;
            color: {$submit_bg_color};
            border: 2px solid {$submit_bg_color};
            cursor:pointer;
            line-height:normal;
        }";
    
    
        echo "{$arf_form_cls_prefix} .arf_repeater_field .arf_repeater_remove_new_button:hover {
            background:{$submit_bg_color};
            color: #fff;
        }";
    
        echo "{$arf_form_cls_prefix} .arf_repeater_button_wrapper{
            float:left;
            width: 100%;
            margin-bottom: 0px;
        }
    
        {$arf_form_cls_prefix} .arf_repeater_add_new_button,
        {$arf_form_cls_prefix} .arf_repeater_add_new_button:hover,
        {$arf_form_cls_prefix} .arf_repeater_add_new_button:focus,
        {$arf_form_cls_prefix} .arf_repeater_add_new_button:active{
            float: right;
            width: 35px;
            outline: none;
            height: 35px;
            font-size: 14px;
            border-radius: 50%;
            -webkit-border-radius: 50%;
            -moz-border-radius: 50%;
            -o-border-radius: 50%;
            position: relative;
            padding: 0;
            margin-top: 10px;
        }
    
        {$arf_form_cls_prefix} .arf_repeater_remove_new_button,
        {$arf_form_cls_prefix} .arf_repeater_remove_new_button:hover,
        {$arf_form_cls_prefix} .arf_repeater_remove_new_button:focus,
        {$arf_form_cls_prefix} .arf_repeater_remove_new_button:active{
            float: right;
            width: 35px;
            outline: none;
            height: 35px;
            font-size: 14px;
            border-radius: 50%;
            -webkit-border-radius: 50%;
            -moz-border-radius: 50%;
            -o-border-radius: 50%;
            position: relative;
            padding: 0;
            margin-top: 10px;
        }
    
        {$arf_form_cls_prefix} .arf_repeater_add_new_button i,
        {$arf_form_cls_prefix} .arf_repeater_remove_new_button i{
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%,-50%);
            -webkit-transform: translate(-50%,-50%);
            -moz-transform: translate(-50%,-50%);
            -o-transform: translate(-50%,-50%);
            width: auto;
            height: auto;
        }
    
        {$arf_form_cls_prefix} .arf_repeater_field .arf_repeater_remove_new_button{
            margin-left: 10px;
            display: none;
        } 
    
        {$arf_form_cls_prefix} .arf_repeater_field {
            float: left;
            width: 100%;
        }
    
        {$arf_form_cls_prefix} .arf_repeater_field .arf_repeater_remove_new_button.visible{
            display: block;
        }";
    }

    if( in_array( 'break', $loaded_field ) ){

        echo "{$arf_form_cls_prefix} .arf_wizard_upper_tab .page_break_nav{
            font-family:{$arf_title_font_family};
        }";
    
        echo "{$arf_form_cls_prefix} [class=previous_btn]{
            font-size:{$arf_input_font_size}px;
            color:{$field_text_color};
            font-family:{$arf_input_font_family};
            {$arf_input_font_style_str}  margin-bottom:0;clear:none;
        }";
    
        if( $arfadd_pagebreak_timer == 1 ){

            echo "{$arf_form_cls_prefix} .arf_pagebreaktime{
                float:right;
                width: 177px;
            }
            {$arf_form_cls_prefix} .time_circles{
                position: relative;
                width:100%;
                height: 100%;
            } 
            {$arf_form_cls_prefix} .time_circles > div > h4 {
                margin: 0px;
                padding: 0px;
                text-align: center;
            }
            {$arf_form_cls_prefix} .arf_pagebreak_timer{
                float:right;
            }";

            if( preg_match('/circle/', $arfpagebreak_timer_style) ){
                    echo "{$arf_form_cls_prefix} .pagebreak_style_circle .time_circles > div > span {
                        display: block;
                        width: 100%;
                        text-align: center;
                        font-family: {$arf_label_font_family};
                        font-size: 300%;
                        margin-top: 0;
                        font-weight: bold;
                        top: 50%;
                        transform:translateY(-50%);
                        position: absolute;
                    }";
            }

            if( preg_match( '/circle_with_text/', $arfpagebreak_timer_style ) ){
                echo "{$arf_form_cls_prefix} .pagebreak_style_circlewithtxt .time_circles > div > span {
                        top: 49%;
                        position: absolute;
                        left: 49%;
                        transform: translate(-50%, -40%);
                        width: 59px;
                    }

                    {$arf_form_cls_prefix} .pagebreak_style_circlewithtxt .textDiv_Seconds h4,
                    {$arf_form_cls_prefix} .pagebreak_style_circlewithtxt .textDiv_Minutes h4,
                    {$arf_form_cls_prefix} .pagebreak_style_circlewithtxt .textDiv_Hours h4{
                        font-size: 12px !important;
                        line-height: unset !important;
                        top: 95%;
                        left: 50%;
                        transform: translateX(-50%);
                        position: absolute;
                    }
            
                    {$arf_form_cls_prefix} .pagebreak_style_circlewithtxt .textDiv_Seconds span,
                    {$arf_form_cls_prefix} .pagebreak_style_circlewithtxt .textDiv_Minutes span,
                    {$arf_form_cls_prefix} .pagebreak_style_circlewithtxt .textDiv_Hours span{
                        line-height: unset;
                    }
            
                    {$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_circlewithtxt{
                       margin-bottom: 15px;
                    }";
                        
            }
            
            echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.bottom{
                float: left;
                margin-left: 30%;
            } 
    
            {$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{
                float: left;
                margin-left:0%;
            } 
    
            {$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.arf_pagebreak_min.arf_pagebreak_sec{
                float: left;
                margin-left: 28%
            }        
    
    
            {$arf_form_cls_prefix} .time_circles > div {
                position: absolute;
                text-align: center;
                height: 59px;
                top: 0 !important;
            }";

            if( preg_match( '/number/', $arfpagebreak_timer_style ) ){
    
                echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.pagebreak_style_number.arf_pagebreak_min.arf_pagebreak_sec{
                        float: left;
                        margin-left:58%;
                    }
            
                    {$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.pagebreak_style_number.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{
                        float: left;
                        margin-left: 37%;
                    }
                    {$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_number .time_circles > div{
                        height: 36px;
                        width: auto !important;
                        letter-spacing:1px;
                    }
                    {$arf_form_cls_prefix} .pagebreak_style_number .time_circles > div > span {
                        display: block;
                        width: 100%;
                        text-align: center;
                        font-family: {$arf_label_font_family};
                        font-size: 24px !important;
                        font-weight: bold;
                    }
                    {$arf_form_cls_prefix} .pagebreak_style_number .time_circles > div > span {
                       display: flex;
                    } 
                  
                    {$arf_form_cls_prefix} .pagebreak_style_number.arf_pagebreak_min .textDiv_Hours span:after{
                        content: ':';
                        display: flex;
                    }   
            
                    {$arf_form_cls_prefix} .pagebreak_style_number.arf_pagebreak_sec  .textDiv_Minutes span:after{
                        content: ':';
                        display: flex;
                    }
            
                    {$arf_form_cls_prefix} .pagebreak_style_number.arf_pagebreak_sec.arf_pagebreak_hrs .textDiv_Hours span:after{
                       content: ':';
                        display: flex;
                    }";
            }
    
            
    
            echo "{$arf_form_cls_prefix} .arf_help_div{
                font-size: 12px;
                font-style: italic;
                color: #333333;
                float: left;
                font-family:{$arf_label_font_family};
                width: 100%;
                padding-left: 12px;
            }
    
             @media all and (max-width:480px){
    
                {$arf_form_cls_prefix} .arf_pagebreak_timer{
                    float:right;
                }

                {$arf_form_cls_prefix} .arf_pagebreak_timer.arf_pagebreak_sec.arf_pagebreak_min{
                    width: 85%;
                }
    
                {$arf_form_cls_prefix} .arf_pagebreak_timer.arf_pagebreak_min.arf_pagebreak_hrs{
                   width: 85%;
                }
    
                {$arf_form_cls_prefix} .arf_pagebreak_timer.arf_pagebreak_sec.arf_pagebreak_hrs,
                {$arf_form_cls_prefix} .arf_pagebreak_timer.arf_pagebreak_min.arf_pagabreak_min{
                    width: 82%;
                }
    
                {$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{
                    float: left;
                    margin-left: -14%;
                }
    
    
                {$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.arf_pagebreak_min.arf_pagebreak_sec{
                    float: left;
                    margin-left: 4%;
                } 
    
    
                {$arf_form_cls_prefix} .arf_pagebreak_timer.arf_pagebreak_sec,
                {$arf_form_cls_prefix} .arf_pagebreak_timer.arf_pagebreak_min,
                {$arf_form_cls_prefix} .arf_pagebreak_timer.arf_pagebreak_hrs{
                    width: 80% !important;
                } ";

                if( preg_match( '/number/', $arfpagebreak_timer_style ) ){
                    echo  "{$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_number.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{
                           width: 100% !important; 
                           margin-right: -11%;
                        }
            
                        {$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_number.arf_pagebreak_sec.arf_pagebreak_min{
                            width: 80% !important;
                        }
            
                        {$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_number.arf_pagebreak_hrs,
                        {$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_number.arf_pagebreak_min,
                        {$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_number.arf_pagebreak_sec{
                            width: 70% !important;
                        }

                        {$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.pagebreak_style_number.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{
                            float: left;
                            margin-left: 10%;
                        }
                        
                        {$arf_form_cls_prefix} .arf_pagebreak_timer.arftimer_bottom.pagebreak_style_number.arf_pagebreak_min.arf_pagebreak_sec{
                            float: left;
                            margin-left: 19%;
                        }";
                }

                if( preg_match( '/circle/', $arfpagebreak_timer_style ) ){
                    echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_circle.arf_pagebreak_min.arf_pagebreak_sec{
                        width: 95% !important;
                    }
        
                    {$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_circle.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{
                        width: 120% !important;
                        margin-right: -10%;
                    }";
                }

                if( preg_match( '/circle_with_text/', $arfpagebreak_timer_style ) ){
                    echo "{$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_circlewithtxt.arf_pagebreak_min.arf_pagebreak_sec{
                            width: 95% !important;
            
                        }
            
                        {$arf_form_cls_prefix} .arf_pagebreak_timer.pagebreak_style_circlewithtxt.arf_pagebreak_hrs.arf_pagebreak_min.arf_pagebreak_sec{
                            width: 120% !important;
                            margin-right:-9%;
                        }";
                }
    
           echo "}";
    
        } 
        echo "{$arf_form_cls_prefix} .arf_pagebreak_timer span,
        {$arf_form_cls_prefix} .arf_pagebreak_timer h4{
            color: $field_label_txt_color;
            font-family: {$arf_input_font_family};
        }
    
        {$arf_form_cls_prefix} .next_btn, 
        {$arf_form_cls_prefix} input[type=\"button\"].previous_btn,
        {$arf_form_cls_prefix} .previous_btn,
        {$arf_form_cls_prefix} .arfsubmitbutton input[type=\"submit\"].next_btn{
            clear:none;";
                if( trim($submit_width) == '' ){
                    echo "min-width:".$submit_auto_width."px;";
                } else {
                    echo "width:".$submit_width.";";
                }
            echo " font-family:{$arf_submit_btn_font_family};
            font-size: {$arf_submit_btn_font_size}px;
            height: {$submit_height}px;
            text-align:center;";
             if( preg_match( '/border/',$arfsubmitbuttonstyle )  ){
                echo "background:transparent;
                color: {$submit_bg_color} ;
                border: ". ( ($submit_border_width > 0) ? $submit_border_width : '2px') ." solid  {$submit_bg_color};";
             }
             
             if( preg_match( '/reverse border/',$arfsubmitbuttonstyle ) ){
                echo "background:{$submit_bg_color};
                color:{$submit_text_color};
                border: ". ( ($submit_border_width > 0) ? $submit_border_width : '2px' ) . " solid {$submit_bg_color};";
             }

             if( preg_match( '/flat/',$arfsubmitbuttonstyle ) ){
                echo "background: {$submit_bg_color};
                color:{$submit_text_color};
                border: {$submit_border_width}  solid {$submit_border_color};
                box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color} ;
                -webkit-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color} ;
                -moz-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color} ;
                -o-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color} ;";
             } 
            echo "border-style:solid;
            cursor:pointer;
            -moz-border-radius:{$submit_border_radius} !important;
            -webkit-border-radius:{$submit_border_radius} !important;
            border-radius:{$submit_border_radius} !important;
            -o-border-radius:{$submit_border_radius} !important;
            text-shadow:none;
            filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);";
             if( preg_match( '/flat/',$arfsubmitbuttonstyle ) ){
                echo "-moz-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color}; ;
                -o-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color}; ;
                -webkit-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color}; ;
                box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color}; ;
                -ms-filter:\"progid:DXImageTransform.Microsoft.Shadow(Strength=3, Direction=135, Color='{$submit_shadow_color}')\";";
            } 
            echo "filter:progid:DXImageTransform.Microsoft.Shadow(Strength=3, Direction=135, Color='{$submit_shadow_color}');padding:0 10px !important;";
            if (isset($submit_bg_img) && $submit_bg_img != '') {
            } else {
                echo "text-indent:0px;
                text-transform: none;
                max-width:95%;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance:none;";
            }
            echo "box-sizing:content-box;";
        echo "}";
    
        echo "{$arf_form_cls_prefix} .arfsubmitbutton input[type=\"submit\"].next_btn {
            vertical-align: unset;
        }
        {$arf_form_cls_prefix} input[type=\"button\"].previous_btn{
            margin-right:15px;
            margin-bottom:0px;
        }
        {$arf_form_cls_prefix} .next_btn:hover,
        {$arf_form_cls_prefix} .next_btn:focus,
        {$arf_form_cls_prefix} .previous_btn:hover,
        {$arf_form_cls_prefix} .previous_btn:focus,
        {$arf_form_cls_prefix} .arfsubmitbutton input[type=\"submit\"].next_btn:hover,
        {$arf_form_cls_prefix} .arfsubmitbutton input[type=\"submit\"].next_btn:focus{";
            if( preg_match( '/border/',$arfsubmitbuttonstyle )  ){
                echo "color: {$submit_text_color};
                background-color: {$submit_bg_color_hover} !important;
                border:". ( ($submit_border_width > 0) ? $submit_border_width : '2px' )." solid {$submit_bg_color_hover};";
            }
             
            if( preg_match( '/reverse border/',$arfsubmitbuttonstyle ) ){
                echo "background:transparent;
                color: {$submit_bg_color_hover} !important;
                border:". ( ($submit_border_width > 0) ? $submit_border_width : '2px' )." solid {$submit_bg_color_hover};";
            }
            
            if( preg_match( '/flat/',$arfsubmitbuttonstyle ) ){ 
                echo "background-color:{$submit_bg_color_hover};";
            }
            echo "box-shadow:none;
            -webkit-box-shadow:none;
            -o-box-shadow:none;
            -moz-box-shadow:none;
            outline: none;
        }
        {$arf_form_cls_prefix} .next_btn:hover,
        {$arf_form_cls_prefix} .next_btn:focus,
        {$arf_form_cls_prefix} .next_btn:active,
        {$arf_form_cls_prefix} .previous_btn:hover,
        {$arf_form_cls_prefix} .previous_btn:focus,
        {$arf_form_cls_prefix} .previous_btn:active,
        {$arf_form_cls_prefix} input[type=\"button\"].previous_btn:active,
        {$arf_form_cls_prefix} input[type=\"button\"].previous_btn:hover,
        {$arf_form_cls_prefix} input[type=\"button\"].previous_btn:focus {
            background:none;
            background-color: {$submit_bg_color_hover};
            padding:0 10px;
            border-width: {$submit_border_width};
            border-color: {$submit_border_color};
            border-style:solid;";
            if( preg_match( '/border/',$arfsubmitbuttonstyle )  ){
                echo "color:{$submit_text_color};
                background-color: {$submit_bg_color_hover};
                border: ". ( ($submit_border_width > 0) ? $submit_border_width : '2px' )." solid {$submit_bg_color_hover};";
            }
            if( preg_match( '/reverse border/',$arfsubmitbuttonstyle ) ){
                echo "background:transparent;
                color: {$submit_bg_color_hover};
                border:". ( ($submit_border_width > 0) ? $submit_border_width : '2px' )." solid {$submit_bg_color_hover};";
            }
            if( preg_match( '/flat/',$arfsubmitbuttonstyle ) ){ 
                echo "background-color: echo $submit_bg_color_hover;";
            }
            echo "padding:0 10px; 
            box-shadow:none;
            -webkit-box-shadow:none;
            -o-box-shadow:none;
            -moz-box-shadow:none;
            outline: none;
            filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);
        }
    
        {$arf_form_cls_prefix} .page_break_nav
        {   
        font-size:16px;
        padding:15px 7px;
        margin:3px 1px 3px 1px;
        background:{$pg_wizard_inactive_bg_color};
        color:{$pg_wizard_text_color};
    
        text-align:center;
        font-weight:bold;
        line-height: 20px;
        max-width:10%;
        vertical-align:middle;
        }
    
        
        {$arf_form_cls_prefix} .page_nav_selected
        {
        background: {$pg_wizard_active_bg_color} ;
        }
        {$arf_form_cls_prefix}  .arf_wizard {
        box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);
        -webkit-box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3), 0 0px 0px rgba(0, 0, 0, 0) inset;
        -o-box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3), 0 0px 0px rgba(0, 0, 0, 0) inset;
        -moz-box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3), 0 0px 0px rgba(0, 0, 0, 0) inset;
        -moz-border-radius:1px;
        -webkit-border-radius:1px;
        -o-border-radius:1px;
        border-radius:1px;
        }
    
        {$arf_form_cls_prefix} .arf_wizard {
        border:1px solid {$pg_wizard_inactive_bg_color};
        -moz-border-radius:1px;
        -webkit-border-radius:1px;
        -o-border-radius:1px;
        border-radius:1px;
        }";

        echo "<pre> arf_wizard_style -----> ";
            print_r($arf_page_break_wizard_theme_style);
        echo "</pre>";
        
        if($arf_page_break_wizard_theme_style == 'style1'){
            echo "{$arf_form_cls_prefix}  .arf_wizard_style1 {
                margin:3px 1% 35px 1%;
                width:98%;
                }
                {$arf_form_cls_prefix} .arf_wizard_style1 {
                    margin:3px 1% 10px 1%;
                    width:98%;
                }
                {$arf_form_cls_prefix}  .arf_wizard_style1 {
                    width:98%;
                }";
        }
        
        if($arf_page_break_wizard_theme_style == 'style2'){
            echo "{$arf_form_cls_prefix} .arf_wizard_style2 {
            margin:3px 0% 10px;
            width:100%;
            position: absolute;
            left: 0;
            right: 0;
            }
            {$arf_form_cls_prefix}  .arf_wizard_style2 {
            margin:3px 0% 35px;
            width:100%;
            position: absolute;
            left: 0;
            right: 0;
            }

            .arfdevicemobile {$arf_form_cls_prefix}  .arf_wizard.arf_wizard_style2 {
                position: relative;
            }
        
            .arf_widget_form {$arf_form_cls_prefix}  .arf_wizard.arf_wizard_style2 {
                position: relative;
            }
            {$arf_form_cls_prefix}  .arf_wizard_style2 {";
                 if($arfadd_pagebreak_timer == 1) { echo 'margin-top:10%;'; }
                echo "width:100%;
            }";
            if($arfadd_pagebreak_timer == 1){ 
                echo "{$arf_form_cls_prefix}  .arf_wizard_style2.arf_wizard_bottom {
                    margin-top:0%;
                    width:100%;
                }";

                echo "@media all and (max-width:480px){
                    {$arf_form_cls_prefix}  .arf_wizard_style2.arf_wizard_bottom {
                        margin-top: 5%;
                    } 
                }";
            }
        }

        if($arf_page_break_wizard_theme_style == 'style3'){
            echo ".arf_widget_form {$arf_form_cls_prefix}   .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab {
                display: table;
            }
        
            .arf_widget_form {$arf_form_cls_prefix}  .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav {
                width: unset;
                float: none;
                display: table-cell;
            }
        
            .arf_widget_form {$arf_form_cls_prefix}  .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::before {
                height: 14px;
                width: 14px;
                left:-7px;
            }
        
            .arf_widget_form {$arf_form_cls_prefix}  .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::after {
                top: 16px;
                width: 30%;
            }

            {$arf_form_cls_prefix}  .arf_wizard.arf_wizard_style3 {
                width: 100%;
                box-shadow: none;
                -webkit-box-shadow: none;
                height: auto;
                border:none;
            }
    
            {$arf_form_cls_prefix}  .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab {
                display: inline-table;
                text-align: center;
            }
    
            {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav {
                position: relative;
                display: inline-block;
                font-size: 0px;
                background: transparent;
                margin: -1px;
                border-right: none;
            }
    
            {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::before{
                content: '';
                display: inline-block;
                position: absolute;
                height: 20px;
                width: 20px;
                background: {$pg_wizard_inactive_bg_color};
                border-radius: 100%;";
                echo "left: -9px;";
                echo "z-index: 999;
                transition: all 1.5s;
            }
    
            {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::after{
                content: '';
                display: inline-block;
                position: absolute;
                height: 2px;
                width: 50%;
                background:  {$pg_wizard_inactive_bg_color};
                left: 0;
                top: 25px;
                right: 0px;
                margin: 0 auto;
                background: linear-gradient(to left, {$pg_wizard_inactive_bg_color}  50%, {$pg_wizard_active_bg_color} 50%) right;
                background-size: 200%;
                transition: .5s ease-out;
            }
    
            .arf_widget_form {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::after{
                background: linear-gradient(to left, {$pg_wizard_inactive_bg_color}  50%, {$pg_wizard_active_bg_color} 50%) right ;
                background-size: 200%;
                transition: .5s ease-out;
                width: 30%;
            }
    
            {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav.arf_page_last {
                width: 0px !important;
            }
    
            {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav.arf_page_last::after {
                display: none;
            }   
    
            {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav:first-child::before {
                background: {$pg_wizard_active_bg_color};
            }
    
            {$arf_form_cls_prefix} .pb_wizard_style3_step_title {
                font-size: 22px;
                text-align: center;
                width: 100%;
                font-family: 'roboto';
                color: {$pg_wizard_text_color_style3};
                font-weight: 400;
                font-style: normal;
                text-decoration: none;
                line-height: 1.3;
                margin-bottom: 20px;
            }
    
            {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab div.page_break_nav:not({$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab div.page_nav_selected ~ div.page_break_nav):not({$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab div.page_nav_selected)::before {
                    background: {$pg_wizard_active_bg_color};
            }
    
            {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab div.page_break_nav:not({$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab div.page_nav_selected ~ div.page_break_nav):not({$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab div.page_nav_selected)::after {
                background-position:left;
            }
    
            {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav.page_nav_selected::before {
                background: {$pg_wizard_active_bg_color};
            }";
        }

        echo "{$arf_form_cls_prefix} .arf_wizard td{
        border:0px;
        padding:15px 5px;
        vertical-align:middle;
        }
        {$arf_form_cls_prefix} .arf_current_tab_arrow
        {   
        border-left: 12px solid rgba(0, 0, 0, 0);
        border-right: 12px solid rgba(0, 0, 0, 0);
        border-top: 9px solid {$pg_wizard_active_bg_color};
        height: 0;
        margin: auto auto -9px;
        width: 0;
        }
        {$arf_form_cls_prefix} .arf_wizard.bottom .arf_current_tab_arrow{
            border-bottom: 9px solid {$pg_wizard_active_bg_color};
            border-top: 0;
        }
        {$arf_form_cls_prefix} .page_break_nav
        {   
            border-right:1px solid rgba(255,255,255,0.7);
        }
    
        {$arf_form_cls_prefix} .page_nav_selected,
        {$arf_form_cls_prefix} .arf_page_prev,
        {$arf_form_cls_prefix} .arf_page_last
        {
            border-right:none;
        }
    
        {$arf_form_cls_prefix} .arf_wizard {
            border-collapse:collapse;
            border:none;
            overflow:visible;
        }
    
        {$arf_form_cls_prefix} .arf_wizard tr {
            border:none;
        }
    
        {$arf_form_cls_prefix} #arf_wizard_table.arf_wizard tr td {
            padding:15px 2%;
        }
    
        .arfdevicemobile {$arf_form_cls_prefix} #arf_wizard_table.arf_wizard tr td {
            padding:15px 2%;
        }
    
        .arfdevicemobile {$arf_form_cls_prefix}  .arf_wizard {
            box-shadow:none;
            -webkit-box-shadow:none;
            -moz-box-shadow:none;
            -o-box-shadow:none;
            height:auto;
        }
        .arfdevicemobile {$arf_form_cls_prefix} .arf_wizard_upper_tab {
            width:100%;
            display:inline-block;
        }
        .arfdevicemobile {$arf_form_cls_prefix} .arf_wizard_clear {
            display:none;
        }
        .arfdevicemobile {$arf_form_cls_prefix} .arf_wizard_lower_tab {
            display:none;
        }
        .arfdevicemobile {$arf_form_cls_prefix} .arf_wizard_upper_tab .page_break_nav {
            float:left;
            width:100% !important;
            margin-bottom:10px;
            padding: 10px 0;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            -o-border-radius: 5px;
            border-radius: 5px;
        }
        .arfdevicemobile {$arf_form_cls_prefix} .arf_wizard_clear {
            display:inline-block;
        }
    
        {$arf_form_cls_prefix} #arf_wizard_table.arf_wizard tr td {
            padding:15px 5px;
            border:none;
        }
    
        {$arf_form_cls_prefix}  .arf_wizard {
            display:inline-block;
            height:50px;
            width:100%;
            -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
            -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
            -o-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
            line-height:normal;
        }
        {$arf_form_cls_prefix} .arf_wizard_upper_tab {
            width:100%;
            display: table;
            table-layout: fixed;
        }
        {$arf_form_cls_prefix} .arf_wizard_lower_tab {
            width:100%;
            margin-top:0px;
            display: table;
            table-layout: fixed;
        }
        {$arf_form_cls_prefix} .arf_wizard.bottom .arf_wizard_lower_tab {
            margin-top: -59px;
        }    
        {$arf_form_cls_prefix} .arf_wizard_upper_tab .page_break_nav {
            max-width:100%;
            margin:0;
            padding:15px 0;
            min-width:inherit;
            display:table-cell;
        }
        {$arf_form_cls_prefix} .arf_wizard_lower_tab .page_break_nav {
            max-width:100%;
            border: none;
            margin-bottom: 0;
            margin-left: 0;
            margin-right: 0;
            padding:0;
            min-width:inherit;
            display:table-cell;
        }
        {$arf_form_cls_prefix} .arf_wizard_upper_tab .page_break_nav {
            font-size:16px;
            font-family:Arial, Helvetica, sans-serif;
        }
    
        @media (max-width:480px) {
            {$arf_form_cls_prefix}  .arf_wizard {
                box-shadow:none;
                -webkit-box-shadow:none;
                -moz-box-shadow:none;
                -o-box-shadow:none;
                height:auto;
            }
            {$arf_form_cls_prefix} .arf_wizard_upper_tab {
                width:100%;
                display:inline-block;
            }
            {$arf_form_cls_prefix} .arf_wizard_clear {
                display:none;
            }
            {$arf_form_cls_prefix} .arf_wizard_lower_tab {
                display:none;
            }
            {$arf_form_cls_prefix} .arf_wizard_upper_tab .page_break_nav {
                float:left;
                width:100% !important;
                margin-bottom:10px;
                padding: 10px 0;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                -o-border-radius: 5px;
                border-radius: 5px;
            }
        }
    
        .arf_widget_form {$arf_form_cls_prefix}  .arf_wizard {
            box-shadow:none;
            -webkit-box-shadow:none;
            -moz-box-shadow:none;
            -o-box-shadow:none;
            height:auto;
        }
        .arf_widget_form {$arf_form_cls_prefix} .arf_wizard_upper_tab {
            width:100%;
            display:inline-block;
        }
        .arf_widget_form {$arf_form_cls_prefix} .arf_wizard_clear {
            display:none;
        }
        .arf_widget_form {$arf_form_cls_prefix} .arf_wizard_lower_tab {
            display:none;
        }
        .arf_widget_form {$arf_form_cls_prefix} .arf_wizard_upper_tab .page_break_nav {
            float:left;
            width:100%;
            max-width:100%;
            margin-bottom:10px;
            padding: 10px 0;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            -o-border-radius: 5px;
            border-radius: 5px;
        }
    
        .arf_survey_nav .survey_step {
            float:left;
        }
        .arf_survey_nav .current_survey_page {
            margin-left:1px;
        }
        .arf_survey_nav .survey_middle {
            float:left;
            margin-left:10px;
        }
        .arf_survey_nav .total_survey_page {
            margin-left:1px;
        }
        .arf_page_nav_clickable{ cursor: pointer; }";
    
        
        echo "@media (max-width:480px) {";

            if($arf_page_break_wizard_theme_style == 'style2'){
                echo "{$arf_form_cls_prefix}  .arf_wizard_style2 {
                        position: relative;
                    } ";
            }
            if($arf_page_break_wizard_theme_style == 'style3'){
                echo "{$arf_form_cls_prefix}  .arf_wizard.arf_wizard_style3 {
                        display: table;
                    }
                    {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav {
                        float: unset;
                        width: unset;
                        margin: -6px;
                        -webkit-border-radius: 0px;
                        -moz-border-radius: 0px;
                        -o-border-radius: 0px;
                        border-radius: 0px;
                        display: table-cell;
                    }";
            }
        echo "}
        {$arf_form_cls_prefix} div.arfsubmitbutton .previous_btn { {$arf_submit_font_style_str} }
    
        @media (max-width: 480px) {
            {$arf_form_cls_prefix} input[type=\"button\"].previous_btn,
            {$arf_form_cls_prefix} input[type=\"button\"].previous_btn{
                display: block;
                margin: 0px auto 15px auto;
            }
            {$arf_form_cls_prefix} input[type=\"submit\"].next_btn{ margin-right:0px; }";
            if($arf_page_break_wizard_theme_style == 'style3'){
                echo "{$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::before {
                    height: 14px;
                    width: 14px;
                    left:-7px;
                }
                {$arf_form_cls_prefix} .arf_wizard.arf_wizard_style3 .arf_wizard_upper_tab .page_break_nav::after {
                    top: 16px;
                    width: 30%;
                    background: linear-gradient(to left , {$pg_wizard_inactive_bg_color} 50%, {$pg_wizard_active_bg_color}  60%) right;
                    background-size: 200%;
                    transition: .5s ease-out;
                }";
            }    
        echo "}
    
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar {
            position: relative;
            height: 27px;
            border:1px solid #c7cbce;
            padding: 3px;
            background-color: #dadde2;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            -o-border-radius: 5px;
            -ms-border-radius: 5px;
            -khtml-border-radius: 5px;
            border-radius: 5px;
            box-shadow:none;
            -webkit-box-shadow:none;
            -moz-box-shadow:none;
            -o-box-shadow:none;
        }
    
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar.blue .ui-progressbar-value {
            background-color: #339BB9;
            border: 1px solid #287a91;
        }
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar.error .ui-progressbar-value {
            background-color: #C43C35;
            border: 1px solid #9c302a;
        }
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar.warning .ui-progressbar-value {
            background-color: #D9B31A;
            border: 1px solid #ab8d15;
        }
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar.success .ui-progressbar-value {
            background-color: #57A957;
            border: none;
        }
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar.transition .ui-progressbar-value {
            -moz-transition: background-color 0.5s ease-in, border-color 1.5s ease-out, box-shadow 1.5s ease-out;
            -webkit-transition: background-color 0.5s ease-in, border-color 1.5s ease-out, box-shadow 1.5s ease-out;
            -o-transition: background-color 0.5s ease-in, border-color 1.5s ease-out, box-shadow 1.5s ease-out;
            transition: background-color 0.5s ease-in, border-color 1.5s ease-out, box-shadow 1.5s ease-out;
        }
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar .ui-progressbar-value {
            position: relative;
            display: block;
            overflow: hidden;
            height: 19px;
            margin: 0;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            -o-border-radius: 5px;
            -ms-border-radius: 5px;
            -khtml-border-radius: 5px;
            border-radius: 5px;
            -webkit-background-size: 22px 22px;
            -moz-background-size: 22px 22px;
            background-size: 22px 22px;
            background-color: #087ee2;
            background-image: -webkit-gradient(linear, 0 100%, 100% 0, color-stop(0.25, rgba(255, 255, 255, 0.15)), color-stop(0.25, transparent), color-stop(0.5, transparent), color-stop(0.5, rgba(255, 255, 255, 0.15)), color-stop(0.75, rgba(255, 255, 255, 0.15)), color-stop(0.75, transparent), to(transparent));
            background-image: -webkit-linear-gradient(-45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
            background-image: -moz-linear-gradient(-45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
            background-image: -o-linear-gradient(-45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
            background-image: linear-gradient(-45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
            box-shadow:none;
            -webkit-box-shadow:none;
            -moz-box-shadow:none;
            -o-box-shadow:none;
            border: none;
            -moz-animation: animate-stripes 2s linear infinite;
            -webkit-animation: animate-stripes 2s linear infinite;
            -o-animation: animate-stripes 2s linear infinite;
            -ms-animation: animate-stripes 2s linear infinite;
            -khtml-animation: animate-stripes 2s linear infinite;
            animation: animate-stripes 2s linear infinite;
            transition: all 0.2s ease-in-out 0;
            -webkit-transition: all 0.2s ease-in-out 0;
            -o-transition: all 0.2s ease-in-out 0;
            -moz-transition: all 0.2s ease-in-out 0;
        }
    
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar .ui-progressbar-value span.ui-label {
            -moz-font-smoothing: antialiased;
            -webkit-font-smoothing: antialiased;
            -o-font-smoothing: antialiased;
            -ms-font-smoothing: antialiased;
            -khtml-font-smoothing: antialiased;
            font-size: 13px;
            position: absolute;
            right: 0;
            line-height: 20px;
            width: 100%;    
            color:#ffffff;
            text-align:center;
            white-space: nowrap;
            font-weight:bold;
        }
    
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar .ui-progressbar-value span.ui-label b {
            font-weight: bold;
        }
    
        {$arf_form_cls_prefix} .ui-progress-bar.ui-progressbar, {$arf_form_cls_prefix} .ui-progressbar-value {
            -webkit-box-sizing: content-box;
            -o-box-sizing: content-box;
            -moz-box-sizing: content-box;
            box-sizing: content-box;
        }

        {$arf_form_cls_prefix} .arf_survey_nav { color: {$arf_text_color_survey}; font-family: {$arf_input_font_family} ; font-size: 14px; line-height: 1.5; }
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar { background: {$arf_bg_color_survey}; }
        {$arf_form_cls_prefix} #arf_progress_bar.ui-progress-bar .ui-progressbar-value { background-color:{$arf_bar_color_survey}; font-family:{$arf_input_font_family}; }
    
        {$arf_form_cls_prefix} .arf_bottom_survey_nav{ margin-top:10px; }";
    }

    if( in_array('html', $loaded_field) ){
        echo "{$arf_form_cls_prefix} .arfmainformfield .arf_htmlfield_control{";
            echo "color:{$field_label_txt_color};";
            echo "word-wrap: break-word;";
        echo "}";
        echo "{$arf_form_cls_prefix} .arf_running_total {
            display:inline-block;
        }";
    }

    if( in_array('imagecontrol', $loaded_field) ){
        echo ".ar_main_div_{$form_id} .arf_image_field {
            display:block;
            position:absolute;
            clear:both;
            float:left;
            z-index:9999;
        }
        .ar_main_div_{$form_id} .arf_image_horizontal_center {
            position:relative;
            float:left;
            width:100%;
            z-index: 999;
        }
        .ar_main_div_{$form_id} .arf_imagecontrol_horizontal_center.arf_image_field {
            float:left;
            width:100%;
            height: 1px;
            right: inherit;
        }
        .ar_main_div_{$form_id} .arf_image_horizontal_center .arf_image_field {
            float:none;
            position:unset !important;
            top:none;
            width:auto;
            left:inherit;
        }
        .ar_main_div_{$form_id} .arf_image_field.arf_image_horizontal_center {
            text-align:center;
            width:100%;
            left:inherit;
        }
        .ar_main_div_{$form_id} .arfshowmainform .arf_image_field {
            position: absolute;
            clear: both;
            height: 1px;
            width: auto;
            z-index: 9999;
        }
        .ar_main_div_{$form_id} .arf_image_field img {
            box-shadow:none !important;
            -webkit-box-shadow:none !important;
            -moz-box-shadow:none !important;
            -o-box-shadow:none !important;
            border:none !important;
            padding:0 !important;
            margin:0 !important;
            max-height: none !important;
            max-width: none !important;
        }";
        echo ".arf_summary_active .arf_image_field{
             display : none;
        }";
        if( !$preview ){
            $all_imagecontrol_fields = $arfform->arf_select_db_data( true, '', $MdlDb->fields, 'id,field_options', 'WHERE type = %s AND form_id = %d', array( 'imagecontrol', $form_id ) );
        } else {
            $all_imagecontrol_fields = $image_control_array;
        }
        if( !empty( $all_imagecontrol_fields ) ){
            foreach( $all_imagecontrol_fields as $field ){
                if( is_array( $field->field_options ) ){
                    $field->field_options = json_encode( $field->field_options );
                }
                $fopts = arf_json_decode( $field->field_options );

                echo ' @media all and (max-width: 480px) {';
                    if ( isset($fopts->position_for_mobile_x) && $fopts->position_for_mobile_x != "" ) {
                        echo '#arf_imagefield_' . $field->id.'{';
                            $field_position_x = $fopts->position_for_mobile_x;
                            if( !preg_match('/px/',$field_position_x) ){
                                $field_position_x .= 'px';
                            }
                            echo 'left:'.$field_position_x.' !important;';
                        echo'}';
                    }
                    if (isset($fopts->position_for_mobile_y) && $fopts->position_for_mobile_y !="") {
                        echo '#arf_imagefield_' . $field->id.'{';
                            $field_position_y = $fopts->position_for_mobile_y;
                            if( !preg_match('/px/',$field_position_y) ){
                                $field_position_y .= 'px';
                            }
                            echo 'top:'.$field_position_y.' !important;';
                        echo "}";
                    }
                    if(isset($fopts->width_for_mobile) && $fopts->width_for_mobile !=""){
                        echo '#arf_imagefield_' . $field->id.' '.'img'.'{';    
                            echo 'width:'.$fopts->width_for_mobile.'px !important;';
                        echo'}';
                    }
                    if(isset($fopts->height_for_mobile) && $fopts->height_for_mobile !=""){
                        echo '#arf_imagefield_' . $field->id.' '.'img'.'{';    
                            echo 'height:'.$fopts->height_for_mobile.'px !important;';
                        echo'}';
                    }
                    if (isset($fopts->position_for_mobile_y) && $fopts->position_for_mobile_y !="") {
                        echo'#image_horizontal_center_'.$field->id.'{';
                            echo 'top:'.$fopts->position_for_mobile_y.' !important;';
                        echo '}';
                    }
                echo '} ';
            }
        }
    }

    if( in_array( 'phone', $loaded_field ) ){
        echo "{$arf_form_cls_prefix} .iti__country-list .iti__country-name{
            font-family:{$arf_input_font_family};
            font-size:{$arf_input_font_size}px;
            {$arf_input_font_style_str}
        }";
        
        echo "{$arf_form_cls_prefix} .arf_field_type_phone ul#country-listbox {
            list-style-type: none !important;
            z-index: 9999;
            padding: 0 !important;
            margin:0;
        }";
        
        echo "{$arf_form_cls_prefix} .arf_active_phone_utils .controls{ z-index:2 !important; }";
    }
}

/** Confirmation Summary Related Styling */
$enable_confirm_summary = false;
if( $preview ){
    $enable_confirm_summary = ( isset( $values['arf_confirmation_summary'] ) && 1 == $values['arf_confirmation_summary'] );
} else {
    $enable_confirm_summary = (isset( $values['options']['arf_confirmation_summary'] ) && 1 == $values['options']['arf_confirmation_summary']);
}

if( !isset( $enable_confirm_summary_flag ) ){
    $enable_confirm_summary_flag = false;
}
if( $enable_confirm_summary || $enable_confirm_summary_flag ){
    echo "{$arf_form_cls_prefix} .arf_confirmation_summary_label_full_width{
        font-family:{$arf_section_font_family};
        font-size:{$arf_section_font_size}px;
        {$arf_section_font_style_str}
        text-align:$arf_label_align;
    }";

    echo ".ar_main_div_{$form_id} .arf_confirmation_summary_wrapper{";
        $frm_bg_color = !empty( $form_bg_color ) ? $form_bg_color : '0,0,0';
        $frm_bg_color = $arsettingcontroller->hex2rgb( $frm_bg_color );
        if( !empty( $arf_form_bg_image ) ){
            echo "background:rgba({$frm_bg_color},{$arf_form_opacity}) url({$arf_form_bg_image});";
            if( 'px' == $arf_form_bg_posx ){
                echo "background-position-x:" . $arf_form_bg_posx_custom ."px;";
            } else {
                echo "background-position-x:" . $arf_form_bg_posx .";";
            }
            if( 'px' == $arf_form_bg_posy ){
                echo "background-position-y:" . $arf_form_bg_posy_custom ."px;";
            } else {
                echo "background-position-y:" . $arf_form_bg_posy .";";
            }
            echo "background-repeat: no-repeat;";
        } else {
            echo "background:rgba({$frm_bg_color},{$arf_form_opacity});";
        }
        echo "border:{$arf_form_border_width} solid {$form_border_color};";
        echo "padding:{$arf_form_padding};";
        echo "border-radius:{$arf_form_border_radius};";
        echo "-webkit-border-radius:{$arf_form_border_radius};";
        echo "-o-border-radius:{$arf_form_border_radius};";
        echo "-moz-border-radius:{$arf_form_border_radius};";
        if( 'shadow' == $arf_form_border_type ){
            echo "-moz-box-shadow:0px 0px 7px 2px {$form_border_shadow_color};
            -o-box-shadow:0px 0px 7px 2px {$form_border_shadow_color};
            -webkit-box-shadow:0px 0px 7px 2px {$form_border_shadow_color};
            box-shadow:0px 0px 7px 2px {$form_border_shadow_color};";
        } else {
            echo "-moz-box-shadow:none;-webkit-box-shadow:none;-o-box-shadow:none;box-shadow:none;";
        }
    echo "}";

    echo "{$arf_form_cls_prefix} .arf_confirmation_summary_label{
        color:{$field_label_txt_color};
        font-family:{$arf_label_font_family};
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_label_full_width{
        color:{$field_label_txt_color};
        font-family:{$arf_label_font_family};   
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_row_wrapper.arf_confirmation_summary_repeater_wrapper .arf_confirmation_summary_repeater_label{
        color:{$field_label_txt_color};
        font-family:{$arf_label_font_family};   
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_repeater_wrapper .arf_confirmation_summary_repeater_input .arf_repeater_label_td{
        color:{$field_label_txt_color};
        font-family:{$arf_label_font_family};
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_input{
        color:{$field_text_color} !important;
        font-family:{$arf_input_font_family};
    }
    
    {$arf_form_cls_prefix}  .arf_confirmation_summary_inner_wrapper {
        display: inline-block;
        width: 100%;
        margin-bottom: 30px !important;
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_label_full_width {
        float:left;
        width:100%;
        padding: 10px 3px;
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_row_wrapper {
        float: none;
        width: 100%;
        display: table;
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_label {
        display: table-cell;
        width: 30%;
        padding: 10px 10px;
        font-weight: bold;
        text-align:right;
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_input {
        display: table-cell;
        width: 70%;
        padding: 10px 10px;
        word-break: break-all;
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_title{
        margin-bottom:20px !important;
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_repeater_wrapper table, 
    {$arf_form_cls_prefix} .arf_confirmation_summary_repeater_wrapper table td {
        border: none;
        font-family: Roboto;
        vertical-align: top;
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_repeater_wrapper .arf_confirmation_summary_repeater_input .arf_repeater_label_td {
        color: #706D70 !important;
        font-weight: bold;
        width: 30% !important;
        text-align: right;
        padding-right:20px;
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_row_wrapper.arf_confirmation_summary_repeater_wrapper .arf_confirmation_summary_repeater_input{
        margin-top: -23px;
        
    }
    {$arf_form_cls_prefix} .arf_confirmation_summary_row_wrapper.arf_confirmation_summary_repeater_wrapper{
        margin-top: 10px;
        border: 1px solid;
        padding: 10px 17px;
    }
    {$arf_form_cls_prefix} .arf_confirmation_summary_row_wrapper.arf_confirmation_summary_repeater_wrapper .arf_confirmation_summary_repeater_label{
        text-align: center;
        color: #706D70 !important;
        font-family: Roboto;
        font-weight: bold;
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_repeater_label{
        margin-bottom: 20px !important;
    }
    
    {$arf_form_cls_prefix} .arf_confirmation_summary_repeater_input table td:nth-child(even){
        text-align: left;
    }";

    echo "{$arf_form_cls_prefix} [class=previous_btn]{
        font-size:{$arf_input_font_size}px;
        color:{$field_text_color};
        font-family:{$arf_input_font_family};
        {$arf_input_font_style_str}  margin-bottom:0;clear:none;
    }";

    echo "{$arf_form_cls_prefix} .next_btn, 
    {$arf_form_cls_prefix} input[type=\"button\"].previous_btn,
    {$arf_form_cls_prefix} .previous_btn,
    {$arf_form_cls_prefix} .arfsubmitbutton input[type=\"submit\"].next_btn{
        clear:none;";
            if( trim($submit_width) == '' ){
                echo "min-width:".$submit_auto_width."px;";
            } else {
                echo "width:".$submit_width.";";
            }
        echo " font-family:{$arf_submit_btn_font_family};
        font-size: {$arf_submit_btn_font_size}px;
        height: {$submit_height}px;
        text-align:center;";
         if( preg_match( '/border/',$arfsubmitbuttonstyle )  ){
            echo "background:transparent;
            color: {$submit_bg_color} ;
            border: ". ( ($submit_border_width > 0) ? $submit_border_width : '2px') ." solid  {$submit_bg_color};";
         }
         
         if( preg_match( '/reverse border/',$arfsubmitbuttonstyle ) ){
            echo "background:{$submit_bg_color};
            color:{$submit_text_color};
            border: ". ( ($submit_border_width > 0) ? $submit_border_width : '2px' ) . " solid {$submit_bg_color};";
         }

         if( preg_match( '/flat/',$arfsubmitbuttonstyle ) ){
            echo "background: {$submit_bg_color};
            color:{$submit_text_color};
            border: {$submit_border_width}  solid {$submit_border_color};
            box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color} ;
            -webkit-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color} ;
            -moz-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color} ;
            -o-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color} ;";
         } 
        echo "border-style:solid;
        cursor:pointer;
        -moz-border-radius:{$submit_border_radius};
        -webkit-border-radius:{$submit_border_radius};
        border-radius:{$submit_border_radius};
        -o-border-radius:{$submit_border_radius};
        text-shadow:none;
        filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);";
         if( preg_match( '/flat/',$arfsubmitbuttonstyle ) ){
            echo "-moz-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color}; ;
            -o-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color}; ;
            -webkit-box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color}; ;
            box-shadow: {$submit_xoffset_shadow} {$submit_yoffset_shadow} {$submit_blur_shadow} {$submit_spread_shadow} {$submit_shadow_color}; ;
            -ms-filter:\"progid:DXImageTransform.Microsoft.Shadow(Strength=3, Direction=135, Color='{$submit_shadow_color}')\";";
        } 
        echo "filter:progid:DXImageTransform.Microsoft.Shadow(Strength=3, Direction=135, Color='{$submit_shadow_color}');padding:0 10px;";
         if (isset($submit_bg_img) && $submit_bg_img != '') {
        } else {
            echo "text-indent:0px;
            text-transform: none;
            max-width:95%;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance:none;";
        };
    echo "}";

    echo "{$arf_form_cls_prefix} .arfsubmitbutton input[type=\"submit\"].next_btn {
        vertical-align: unset;
    }
    {$arf_form_cls_prefix} input[type=\"button\"].previous_btn{
        margin-right:15px;
    }
    {$arf_form_cls_prefix} .next_btn:hover,
    {$arf_form_cls_prefix} .next_btn:focus,
    {$arf_form_cls_prefix} .previous_btn:hover,
    {$arf_form_cls_prefix} .previous_btn:focus,
    {$arf_form_cls_prefix} .arfsubmitbutton input[type=\"submit\"].next_btn:hover,
    {$arf_form_cls_prefix} .arfsubmitbutton input[type=\"submit\"].next_btn:focus{";
        if( preg_match( '/border/',$arfsubmitbuttonstyle ) && !preg_match( '/border reverse/',$arfsubmitbuttonstyle ) ){
            echo "color: {$submit_text_color};
            background-color: {$submit_bg_color_hover};
            border:". ( ($submit_border_width > 0) ? $submit_border_width : '2px' )." solid {$submit_bg_color_hover};";
        }
         
        if( preg_match( '/reverse border/',$arfsubmitbuttonstyle ) ){
            echo "background:transparent;
            color: {$submit_bg_color_hover} ;
            border:". ( ($submit_border_width > 0) ? $submit_border_width : '2px' )." solid {$submit_bg_color_hover};";
        }
        
        if( preg_match( '/flat/',$arfsubmitbuttonstyle ) ){ 
            echo "background-color:{$submit_bg_color_hover};";
        }
        echo "box-shadow:none;
        -webkit-box-shadow:none;
        -o-box-shadow:none;
        -moz-box-shadow:none;
        outline: none;
    }
    {$arf_form_cls_prefix} .next_btn:hover,
    {$arf_form_cls_prefix} .next_btn:focus,
    {$arf_form_cls_prefix} .next_btn:active,
    {$arf_form_cls_prefix} .previous_btn:hover,
    {$arf_form_cls_prefix} .previous_btn:focus,
    {$arf_form_cls_prefix} .previous_btn:active,
    {$arf_form_cls_prefix} input[type=\"button\"].previous_btn:active,
    {$arf_form_cls_prefix} input[type=\"button\"].previous_btn:hover,
    {$arf_form_cls_prefix} input[type=\"button\"].previous_btn:focus {
        background:none;
        background-color: {$submit_bg_color_hover};
        padding:0 10px;
        border-width: {$submit_border_width};
        border-color: {$submit_border_color};
        border-style:solid;";
        if( preg_match( '/border/',$arfsubmitbuttonstyle )  ){
            echo "color:{$submit_text_color};
            background-color: {$submit_bg_color_hover};
            border: ". ( ($submit_border_width > 0) ? $submit_border_width : '2px' )." solid {$submit_bg_color_hover};";
        }
        if( preg_match( '/reverse border/',$arfsubmitbuttonstyle ) ){
            echo "background:transparent;
            color: {$submit_bg_color_hover};
            border:". ( ($submit_border_width > 0) ? $submit_border_width : '2px' )." solid {$submit_bg_color_hover};";
        }
        if( preg_match( '/flat/',$arfsubmitbuttonstyle ) ){ 
            echo "background-color: echo $submit_bg_color_hover;";
        }
        echo "padding:0 10px; 
        box-shadow:none;
        -webkit-box-shadow:none;
        -o-box-shadow:none;
        -moz-box-shadow:none;
        outline: none;
        filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);
    }";

    echo "{$arf_form_cls_prefix} .arf_confirmation_summary_title{";
        echo "color:{$form_title_color};";
        echo "font-family:".stripslashes($arf_title_font_family).";";
        echo "font-size:{$arf_title_font_size};";
        echo $arf_title_font_style_str;
    echo "}";

    echo "{$arf_form_cls_prefix} .arf_confirmation_summary_submit_wrapper{text-align:{$submit_align}; }

    @media (min-width:290px) and (max-width:480px) {
        {$arf_form_cls_prefix} .arf_confirmation_summary_submit_wrapper{
            text-align:center !important;
            clear:both !important;
            margin: 0 auto !important;
        }

        {$arf_form_cls_prefix} .arf_confirmation_summary_label {
            display: table-cell !important;
            width: 40% !important;
            padding: 10px 3px !important;
            font-weight: bold !important;
            text-align:right !important;
        }
    }";

    echo "{$arf_form_cls_prefix} div.arfsubmitbutton .previous_btn { {$arf_submit_font_style_str} }";

    echo "@media (max-width: 480px) {
        {$arf_form_cls_prefix} input[type=\"button\"].previous_btn,
        {$arf_form_cls_prefix} input[type=\"button\"].previous_btn{
            display: block;
            margin: 0px auto 15px auto;
        }
        {$arf_form_cls_prefix} input[type=\"submit\"].next_btn{ margin-right:0px; }    
    }";

    echo "{$arf_form_cls_prefix} .arf_confirmation_summary_input .arf_radio_label_image,";
    echo "{$arf_form_cls_prefix} .arf_confirmation_summary_input .arf_checkbox_label_image{";
        echo "display:none;";
    echo"}";
}
/** Confirmation Summary Related Styling */