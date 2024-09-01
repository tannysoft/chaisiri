<?php
if (!defined('WPINC')) {
    die;
}

class ARForms_VCExtendArp {

    protected static $instance = null;
    var $is_arforms_vdextend = 0;

    public function __construct() {
        add_action('init', array($this, 'ARFintegrateWithVC'));
        add_action('init', array($this, 'ArfCallmyFunction'));
    }

    public static function arp_get_instance() {
        if ($this->instance == null) {
            $this->instance = new self;
        }

        return $this->instance;
    }

    public function ARFintegrateWithVC() {
        if (function_exists('vc_map')) {
            global $arfversion, $armainhelper;
	    
            if (version_compare(WPB_VC_VERSION, '4.3.4', '>=')) {


                if (isset($_REQUEST['vc_action']) && !empty($_REQUEST['vc_action'])) {

                    wp_register_style( 'arf_selectpicker', ARFURL . '/css/arf_selectpicker.css', array(), $arfversion );
                    wp_enqueue_style( 'arf_selectpicker' );
		    
                    wp_register_script('jquery-validation', ARFURL . '/bootstrap/js/jqBootstrapValidation.js', array('jquery'), $arfversion);
                    wp_enqueue_script('jquery-validation');

		            wp_register_style('arf-fontawesome-css', ARFURL . '/css/font-awesome.min.css', array(), $arfversion);
		            wp_enqueue_script('arf-fontawesome-css');

                    wp_enqueue_style( 'wp-color-picker' );
                    wp_enqueue_script( 'wp-color-picker');
                }
            }


            vc_map(array(
                'name' => addslashes(esc_html__('ARForms', 'ARForms')),
                'description' => addslashes(esc_html__('Exclusive Wordpress Form Builder Plugin', 'ARForms')),
                'base' => 'ARForms_popup',
                'category' => addslashes(esc_html__('Content', 'ARForms')),
                'class' => '',
                'controls' => 'full',
                'admin_enqueue_css' => array(ARFURL . '/core/vc/arforms_vc.css'),
                'front_enqueue_css' => ARFURL . '/core/vc/arforms_vc.css',
                'front_enqueue_js' => ARFURL . '/core/vc/arforms_vc.js',
                'icon' => 'arforms_vc_icon',
                'params' => array(
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'id',
                        'value' => '',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'shortcode_type',
                        'value' => 'normal',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => 'ARForms_Popup_Shortode',
                        'heading' => false,
                        'param_name' => 'type',
                        'value' => 'link',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'position',
                        'value' => 'top',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'desc',
                        'value' => 'Click here to open Form',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'width',
                        'value' => '800',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'height',
                        'value' => 'auto',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'angle',
                        'value' => '0',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'bgcolor',
                        'value' => '#8ccf7a',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'txtcolor',
                        'value' => '#ffffff',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'on_inactivity',
                        'value' => '1',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'arf_img_url',
                        'value' => '',
                        'description' => addslashes('&nbsp;'),
                        'admin_label'=> true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'arf_img_height',
                        'value' => 'auto',
                        'description' => addslashes('&nbsp;'),
                        'admin_label'=> true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'arf_img_width',
                        'value' => 'auto',
                        'description' => addslashes('&nbsp;'),
                        'admin_label'=> true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'on_scroll',
                        'value' => '10',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'on_delay',
                        'value' => '0',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'overlay',
                        'value' => '0.6',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'is_close_link',
                        'value' => 'yes',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'modal_bgcolor',
                        'value' => '#000000',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),

                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'inactive_min',
                        'value' => '0',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'is_fullscreen',
                        'value' => 'no',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'modaleffect',
                        'value' => 'no_animation',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'hide_popup_for_loggedin_user',
                        'value' => 'no',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),

                )
            ));
        }
    }

    public function ArfCallmyFunction() {
        if (function_exists('vc_add_shortcode_param')) {
            vc_add_shortcode_param('ARForms_Popup_Shortode', array($this, 'arforms_param_html'), ARFURL . '/core/vc/arforms_vc.js');
        }
    }

    public function arforms_param_html($settings, $value) {

        global $armainhelper, $arformhelper,$maincontroller;

        echo '<input  id="Arf_param_id" type="hidden" name="id" value="" class="wpb_vc_param_value">';

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_arfield" type="hidden" value="' . esc_attr($value) . '" />';

	
	    
        if ($this->is_arforms_vdextend == 0) {
            $this->is_arforms_vdextend = 1;
            ?>


            <style type="text/css">
                @font-face {
                    font-family: 'Asap-Regular';
                    src: url('<?php echo ARFURL; ?>/fonts/Asap-Regular.eot');
                    src: url('<?php echo ARFURL; ?>/fonts/asap-regular-webfont.woff2') format('woff2'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Regular.woff') format('woff'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Regular.ttf') format('truetype'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Regular.svg#Asap-Regular') format('svg'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Regular.eot?#iefix') format('embedded-opentype');
                    font-weight: normal;
                    font-style: normal;
                }

                @font-face {
                    font-family: 'Asap-Medium';
                    src: url('<?php echo ARFURL; ?>/fonts/Asap-Medium.eot');
                    src: url('<?php echo ARFURL; ?>/fonts/asap-medium-webfont.woff2') format('woff2'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Medium.woff') format('woff'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Medium.ttf') format('truetype'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Medium.svg#Asap-Medium') format('svg'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Medium.eot?#iefix') format('embedded-opentype');
                    font-weight: normal;
                    font-style: normal;
                }


                .arfmodal_vc .btn-group.bootstrap-select 
                {
                    text-align:left;
                }

                .arfmodal_vc .btn-group .btn.dropdown-toggle,.arfmodal_vc .btn-group .arfbtn.dropdown-toggle {
                    border: 1px solid #CCCCCC;
                    background-color:#FFFFFF;
                    background-image:none;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    outline:0 !important;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                }
                .arfmodal_vc .btn-group.open .btn.dropdown-toggle,.arfmodal_vc .btn-group.open .arfbtn.dropdown-toggle {
                    border:solid 1px #CCCCCC;
                    background-color:#FFFFFF;
                    border-bottom-color:transparent;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    outline:0 !important;
                    outline-style:none;
                    border-bottom-left-radius:0px;
                    border-bottom-right-radius:0px;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                }
                .arfmodal_vc .btn-group.dropup.open .btn.dropdown-toggle, .arfmodal_vc .btn-group.dropup.open .arfbtn.dropdown-toggle {
                    border:solid 1px #CCCCCC;
                    background-color:#FFFFFF;
                    border-top-color:transparent;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    outline:0 !important;
                    outline-style:none;
                    border-top-left-radius:0px;
                    border-top-right-radius:0px;
                    border-bottom-left-radius:6px;
                    border-bottom-right-radius:6px;
                }
                .arfmodal_vc .btn-group .arfdropdown-menu {
                    margin:0;
                }
                .arfmodal_vc .btn-group.open .arfdropdown-menu {
                    border:solid 1px #CCCCCC;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    border-top:none;
                    margin:0;
                    margin-top:-1px;
                    border-top-left-radius:0px;
                    border-top-right-radius:0px;	
                }
                .arfmodal_vc .btn-group.dropup.open .arfdropdown-menu {
                    border-top:solid 1px #CCCCCC;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    border-bottom:none;
                    margin:0;
                    margin-bottom:-1px;
                    border-bottom-left-radius:0px;
                    border-bottom-right-radius:0px;
                    border-top-left-radius:6px;
                    border-top-right-radius:6px;	
                }
                .arfmodal_vc .btn-group.dropup.open .arfdropdown-menu .arfdropdown-menu.inner {
                    border-top:none;
                }
                .arfmodal_vc .btn-group.open ul.arfdropdown-menu {
                    border:none;
                }

                .arfmodal_vc .arfdropdown-menu > li {
                    margin:0px;
                }

                .arfmodal_vc .arfdropdown-menu > li > a {
                    padding: 6px 12px;
                    text-decoration:none;
                }

                .arfmodal_vc .arfdropdown-menu > li:hover > a {
                    background:#1BBAE1;
                }

                .arfmodal_vc .bootstrap-select.btn-group, 
                .arfmodal_vc .bootstrap-select.btn-group[class*="span"] {
                    margin-bottom:5px;
                }

                .arfmodal_vc ul, .wrap ol {
                    margin:0;
                    padding:0;
                }

                .arfmodal_vc form {
                    margin:0;
                }	

                .arfmodal_vc label {
                    display:inline;
                    margin-left:5px;
                }

                .arfnewmodalclose
                {
                    font-size: 15px;
                    font-weight: bold;
                    height: 19px;
                    position: absolute;
                    right: 3px;
                    top:5px;
                    width: 19px;
                    cursor:pointer;
                    color:#D1D6E5;
                } 
                #arfinsertform
                {
                    text-align:center;
                    top: 0px !important;
                }
                .newform_modal_title
                {
                    font-size:24px;
                    font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;
                    color:#d1d6e5;
                    margin-top:14px;
                }

                #arfcontinuebtn
                {
                    background:#1bbae1;
                    font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;
                    font-size:18px;
                    cursor:pointer;
                    color:#ffffff;
                    margin-top:10px;
                    padding-top:18px;	
                    height:42px;
                }

                .arfmodal_vc .txtmodal1 
                {
                    height:36px;
                    border:1px solid #cccccc;
                    -o-border-radius:3px;
                    -moz-border-radius:3px;
                    -webkit-border-radius:3px;
                    border-radius:3px;
                    color:#353942;
                    background:#FFFFFF;
                    font-family:'Asap-Regular', Arial, Helvetica, Verdana, sans-serif;
                    font-size:14px;
                    margin:0px;
                    letter-spacing:0.8px;
                    padding:0px 10px 0 10px;
                    width:360px;
                    outline:none;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-sizing: content-box;
                    -o-box-sizing: content-box;
                    -moz-box-sizing: content-box;
                    box-sizing: content-box;
                }
                .arfmodal_vc .txtmodal1:focus
                {
                    border:1px solid #1BBAE1;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    transition:none;
                    -webkit-transition:none;
                    -o-transition:none;
                    -moz-transition:none;
                }
                .newmodal_field_title
                {
                    margin:20px 0 10px 0;
                    font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;

                    font-size:14px;
                    color:#353942;
                }
                .arfmodal_vc input[class="rdomodal"] {
                    display:none;
                }

                .arfmodal_vc input[class="rdomodal"] + label {
                    color:#333333;
                    font-size:14px;
                    font-family:'Asap-Regular', Arial, Helvetica, Verdana, sans-serif;
                }

                .arfmodal_vc input[class="rdomodal"] + label span {
                    display:inline-block;
                    width:19px;
                    height:19px;
                    margin:-1px 4px 0 0;
                    vertical-align:middle;
                    background:url(<?php echo ARFURL; ?>/images/dark-radio-green.png) -37px top no-repeat;
                    cursor:pointer;
                }

                .arfmodal_vc input[class="rdomodal"]:checked + label span
                {
                    background:url(<?php echo ARFURL; ?>/images/dark-radio-green.png) -56px top no-repeat;
                }
                .arfmodal_vcfields
                {

                    display:table;
                    text-align: center;
                    margin-top:10px;
                    width:100%;

                    float:left !important;
                    width:250px !important;
                    min-height:80px !important;
                    height:  auto;
                }
                .arfmodal_vcfields .arfmodal_vcfield_left
                {
                    display:table-cell;
                    text-align:right;
                    width:45%;
                    padding-right:20px;	
                    font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;
                    font-weight:normal;
                    font-size:14px;
                    color:#353942;
                }
                .arfmodal_vcfields .arfmodal_vcfield_right
                {
                    display:table-cell;
                    text-align:left;
                }
                .arfmodal_vc .arf_px
                {
                    font-family:'Asap-Regular', Arial, Helvetica, Verdana, sans-serif;
                    font-size:12px;
                    color:#353942;	
                }


                body.rtl .arfnewmodalclose
                {
                    right:auto;
                    left:3px;
                }
                body.rtl .arfmodal_vcfields .arfmodal_vcfield_left
                {
                    text-align:left;
                }
                body.rtl .arfmodal_vcfields .arfmodal_vcfield_right
                {
                    text-align:right;
                    padding-right:20px;	

                    float:left !important;
                }
                body.rtl .arfmodal_vc .bootstrap-select.btn-group .arfbtn .filter-option
                {
                    top:5px;
                    right:8px;
                    left:auto;
                }

                body.rtl .arfmodal_vc .bootstrap-select.btn-group .arfbtn .caret
                {
                    left:8px;
                    right:auto;
                }
                body.rtl .arfmodal_vc .btn-group.open .arfdropdown-menu {
                    text-align:right;
                }

                .arf_coloroption_sub{
                    border: 4px solid #dcdfe4;
                    border-radius: 2px;
                    -webkit-border-radius: 2px;
                    -moz-border-radius: 2px;
                    -o-border-radius: 2px;
                    cursor: pointer;
                    height: 22px;
                    width: 47px;
                    margin-left:22px;
                    margin-top:5px;
                }

                .arf_coloroption{
                    cursor: pointer;
                    height: 22px;
                    width: 47px;
                }

                .arf_coloroption_subarrow_bg{
                    background: none repeat scroll 0 0 #dcdfe4;
                    height: 8px;
                    margin-left: 39px;
                    margin-top: -8px;
                    text-align: center;
                    vertical-align: middle;
                    width: 8px;
                }

                .arf_coloroption_subarrow{
                    background: <?php echo "url(" . ARFURL . "/images/colpickarrow.png) no-repeat center center"; ?>;
                    height: 3px;
                    padding-left: 5px;
                    padding-top: 6px;
                    width: 5px;
                }

                .colpick_hex{
                    z-index:999999;
                }
                .arfmodal_vc.fade{ opacity:1; }

                .arf_label{
                    float:left;
                    margin-bottom:5px;
                }
        .arfinsertform_modal_container.arf_popup_content_container{
            overflow: visible;
        }
        .main_div_container{
            padding:0px 25px !important;
            margin-left: 0px !important;
        }
        .arf_select_form_label{
            color:#000000; vertical-align:top;
        }
        .arfmarginb20{
            display: inline-block;
            position: relative !important;
            width: 100% !important;
        }        
        .arf_select_span{
            margin-left: 8px;
            float: left;
        }
        #arfinsertform{
            width: 100%;
            display: inline-block;
            position: relative;
            padding: 0px 10px !important;
        }
        #show_link_type_vc{
            width: 100%;
            position: relative;
            display:none;
        }
        .arf_vc_inncer_wrapper{
            display: inline-flex;
            flex-wrap: wrap;
            width: 100%;
        }
        .arfsecond_div{
            display: inline-block;
            margin: 0px 0px 15px !important;
            width: 50% !important;
            text-align: left !important;
            box-sizing: border-box;
            float: left !important;
            padding-right:10px !important;
            padding-left: 10px !important;
        }
        .arf_vc_label{
            width: 100%;
            display: inline-block;
            position: relative;
            margin: 0px !important;
            padding-bottom: 10px;
        }
        #short_caption{width: 100% !important; display: inline-block; height: 36px;}
        .arfmodal_vcfields .arfmodal_vcfield_right{
            display: inline-block !important;
            width: 100%;
        }
        .arf_coloroption_subarrow, .arf_coloroption_subarrow_bg{
            display: none;
        }
        .arf_coloroption_sub{
            background:  #D5E3FF !important;
            border-radius: 4px !important;
            height: 36px !important;
        }
        .wp-picker-container .wp-color-result.button{
            height: 28px !important;
        }
        .arfmodal_vc .wp-picker-container{
            top:-70px;
            height: 30px;
        }
        .arfmodal_vc .arf_coloroption{
            height: 30px;
        }
        .arfmodal_vc .arf_coloroption_sub{
            height: 38px !important;
        }
        .arfmodal_vc .arfbgcolornote{
            line-height: normal;
            position:relative;
            top:5px;
        }
        .arf_inner_input{
            width: 50%;
            float: left;
            position: relative;
            padding-right: 10px;
            margin: 0px !important;
            box-sizing: border-box;
        }
        .arf_inner_input:nth-child(2){
            padding-right: 0px !important;
            padding-left: 10px;
        }
        .arf_inner_input .arfbgcolornote{
            width: 100% !important;
            display: inline-block;
        }
        .arf_inner_input .wpb_vc_param_value{
            width: 100% !important; 
            display: inline-block;
        }
        #arfmodalbuttonstyles{
            min-height: 105px !important;
        }
        #overlay_div_vc{
            min-height: 105px !important;
        }
        .arf_color_div{
            display: inline-block;
            width: 100%;
        }
        .arf_coloroption_sub{
            width: calc(50% - 20px) !important;
            float: left !important;
            margin: 5px 10px 0px 0px !important;
            box-sizing: border-box;
        }
        .arf_coloroption_sub:nth-child(2) {
            margin-left: 10px !important;
            margin-right: 0px !important;
        }
        #modal_width, #modal_height{width: auto !important;}
        .arfbgcolornote{width: 100% !important; font-size: 12px !important; }
        .height_setting{width: auto !important;}
        #arf_div_height_setting.arf_height_active{
            min-height: 170px !important;
        }
            </style>        

            <div class='arfinsertform_modal_container arf_popup_content_container' style="overflow: visible;">
		
                <div class="main_div_container" style="padding:0px;">
                    <div class="select_form arfmarginb20">
                        <label><?php echo addslashes(esc_html__('Select a form to insert into page', 'ARForms')); ?>&nbsp;<span class="newmodal_required" style="color:#000000; vertical-align:top;">*</span></label>
                        <div class="selectbox">
                            <?php $arformhelper->forms_dropdown_new('arfaddformid_vc_popup', '', 'Select form') ?>

                        </div>
                    </div>
                    <input type="hidden" id="arf_shortcode_type" value="normal" name="shortcode_type"  class="wpb_vc_param_value" />
                    <div class="select_type arfmarginb20">
                        <label><?php echo addslashes(esc_html__('How you want to include this form into page?', 'ARForms')); ?></label>
                        <div class="radio_selection">
                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" class="" checked="checked" name="shortcode_type" value="normal" id="shortcode_type_normal_vc" onclick="showarfpopupfieldlist();"/>
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                        </svg>
                                    </div>
                                
                                    <span style="margin-left: 8px;float: left;">
                                        <label for="shortcode_type_normal_vc" <?php if (is_rtl()) {
                					   echo 'style="float:right; margin-right:167px;"';
                				        } ?>><?php echo addslashes(esc_html__('Internal', 'ARForms')); ?></label>
                					</span>
                                </div>
                            </div>
                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" class=" arf_submit_entries" name="shortcode_type" value="popup" id="shortcode_type_popup_vc" onclick="showarfpopupfieldlist();" />
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                        </svg>
                                    </div>
                                
                                    <span style="margin-left: 8px;float: left;">
                                        <label for="shortcode_type_popup_vc" <?php if (is_rtl()) {
                    					echo 'style="float:right; margin-right:167px;"';
                    				    } else {
                    					echo 'style="width:170px;"';
                    				    } ?>><?php echo addslashes(esc_html__('Modal(popup) window', 'ARForms')); ?></label>
                                    </span>
                                </div>
                            </div>

                        </div>

                    </div>


                </div>

           

            <div id="arfinsertform" class="arfmodal_vc fade">

                <input type="hidden" id="form_title_i" value="" />
                <div class="newform_modal_fields" style="margin-bottom:30px;">
                    <div id="show_link_type_vc" style="display:none; margin-top:15px;">   

                        <div class="arfmodal_vcfields arfsecond_div" id="normal_link_type"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Modal Trigger Type', 'ARForms')); ?></label>

                            <div class="sltmodal" style="float:none; font-size:15px; <?php
                                 if (is_rtl()) {
                                     echo 'text-align:right;';
                                 } else {
                                     echo 'text-align:left;';
                                 }
                                 ?>">

                                <?php 

                                    $arf_type_attr = array( 'onChange' => 'javascript:changetopposition(this.value); arf_set_link_type_data(this.value)',
                                                            'class' => 'wpb_vc_param_value' );

                                    $arf_type_opts = array( 'onclick' => addslashes(esc_html__('On Click', 'ARForms')),
                                                            'onload' => addslashes(esc_html__('On Page Load', 'ARForms')),
                                                            'scroll' => addslashes(esc_html__('On Page Scroll', 'ARForms')),
                                                            'timer' => addslashes(esc_html__('On Timer(Scheduled)', 'ARForms')),
                                                            'on_exit' => addslashes(esc_html__('On Exit(Exist Intent)', 'ARForms')),
                                                            'on_idle' => addslashes(esc_html__('On Idle', 'ARForms')), );

                                    echo $maincontroller->arf_selectpicker_dom( 'type', 'link_type_vc', '', 'width:235px;', 'onclick', $arf_type_attr, $arf_type_opts );
                                ?>
                            </div>

                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="list_of_onclick_vc" style="width: 100% !important">
                            <label style="text-align: left;display:block;"><?php echo addslashes(esc_html__('Click Types', 'ARForms')); ?></label>     
                            <div class="radio_selection ">
                                   
                               <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" checked="checked" name="onclick_type" value="link" id="onclick_type_link" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_link" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Link', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                               <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio arf_submit_entries" name="onclick_type" value="button" id="onclick_type_button" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_button" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Button', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                               <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio arf_submit_entries" name="onclick_type" value="image" id="onclick_type_image" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_image" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Image', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                               <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" name="onclick_type" value="sticky" id="onclick_type_sticky" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_sticky" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Sticky', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                                <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" name="onclick_type" value="fly" id="onclick_type_fly" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_fly" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Fly(Sidebar)', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                            </div>
                         </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="shortcode_caption_vc" style="width: 225px;float:left;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Caption :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;">
                                <input type="text" name="desc" id="short_caption" value="Click here to open Form" class="wpb_vc_param_value txtstandardnew" style="width:230px;" />
                            </div>          
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="is_scroll_vc" style="display:none;width:450px !important;"> 	
                            <label style="float:left;text-align:left"><?php echo addslashes(esc_html__('Open popup when user scroll % of page after page load', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <input type="text" name="on_scroll" id="open_scroll" value="10" class="wpb_vc_param_value txtstandardnew" style="width:70px;" /> %
                                <span style="font-style:italic;">&nbsp;<?php echo addslashes(esc_html__('(eg. 100% - end of page)', 'ARForms')); ?></span>
                            </div>          
                        </div>


                        <div class="arfmodal_vcfields arfsecond_div" id="is_delay_vc" style="display:none;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Open popup after page load', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <input type="text" name="on_delay" id="open_delay" value="0" class="wpb_vc_param_value txtstandardnew" style="width:70px;" />
                                <span style="font-size:12px;"><?php echo addslashes(esc_html__('(in seconds)', 'ARForms')); ?></span>
                            </div>          
                        </div>



                        <div class="arfmodal_vcfields arfsecond_div" id="is_sticky_vc" style="display:none;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Link Position?', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <div class="sltmodal" style="float:none; font-size:15px;<?php
                                    if (is_rtl()) {
                                        echo 'text-align:right;';
                                    } else {
                                        echo 'text-align:left;';
                                    }
                                    ?>">   

                                    <?php
                                        $arf_position_attr = array( 'class' => 'wpb_vc_param_value' );
                                        $arf_position_opts = array( 'top' => addslashes(esc_html__('Top', 'ARForms')),
                                                                    'bottom' => addslashes(esc_html__('Bottom', 'ARForms')),
                                                                    'left' => addslashes(esc_html__('Left', 'ARForms')),
                                                                    'right' => addslashes(esc_html__('Right', 'ARForms')),);

                                        echo $maincontroller->arf_selectpicker_dom( 'position', 'link_position_vc', '', 'width:235px;', 'top', $arf_position_attr, $arf_position_opts );
                                    ?>
                                </div>
                            </div>          
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="overlay_div_vc" style="display:none;clear:both;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Background Overlay :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;">
                                <div class="sltmodal" style="float:none; font-size:15px;display:inline-block; float: left; margin-top:5px; <?php
                                     if (is_rtl()) {
                                         echo 'text-align:right;';
                                     } else {
                                         echo 'text-align:left;';
                                     }
                                     ?>">
                                    
                                    <?php
                                        $arf_overlay_attr = array( 'class' => 'wpb_vc_param_value' );
                                        $arf_overlay_opts = array( '0' => addslashes(esc_html__('0 (None)', 'ARForms')),
                                                                 '0.1' => addslashes(esc_html__('10%', 'ARForms')),
                                                                 '0.2' => addslashes(esc_html__('20%', 'ARForms')),
                                                                 '0.3' => addslashes(esc_html__('30%', 'ARForms')),
                                                                 '0.4' => addslashes(esc_html__('40%', 'ARForms')),
                                                                 '0.5' => addslashes(esc_html__('50%', 'ARForms')),
                                                                 '0.6' => addslashes(esc_html__('60%', 'ARForms')),
                                                                 '0.7' => addslashes(esc_html__('70%', 'ARForms')),
                                                                 '0.8' => addslashes(esc_html__('80%', 'ARForms')),
                                                                 '0.9' => addslashes(esc_html__('90%', 'ARForms')),
                                                                 '1' => addslashes(esc_html__('100%', 'ARForms')),
                                                                  );

                                        echo $maincontroller->arf_selectpicker_dom( 'overlay', 'overlay', '', 'width:85px;', '0.6', $arf_overlay_attr, $arf_overlay_opts );
                                    ?>
                                </div>

    				            <div style="display: inline-block; float:left;" class="arf_coloroption_sub" id="arf_vc_wp_picker_container">
                                    <div class="arf_coloroption" id="arf_vc_modal_bg_color"></div>
                                    <div class="arf_coloroption_subarrow_bg">
                                        <div class="arf_coloroption_subarrow"></div>
                                    </div>
                                    <div class="arfbgcolornote">(<?php echo addslashes(esc_html__('Background Color', 'ARForms')); ?>)</div>
                                    <input type="hidden" name="modal_bgcolor" id="arf_vc_modal_bg_color_input" class="txtmodal1 wpb_vc_param_value" value="#000000" />
                                </div>
                                
                            </div> 
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="is_close_link_div_vc" style="float:left;">
                            <label class="arf_label"><?php echo addslashes(esc_html__('Show Close Button :', 'ARForms')); ?></label>
                           
                            <div class="radio_selection" style="clear: both;">
                                <input type="hidden" id="is_close_link_value" value="yes" name="is_close_link"  class="wpb_vc_param_value" />
                                  
                                <div class="arf_radio_wrapper arfminwidth30">
                                    <div class="arf_custom_radio_div">
                                        <div class="arf_custom_radio_wrapper">
                                            <input onclick="is_close_link_change();" type="radio" checked="checked"  name="is_close_link_vc" value="yes" id="show_close_link_yes_vc" class="arf_custom_radio"  />
                                            <svg width="18px" height="18px"r>
                    					    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON;?>
                    					    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON;?>
                                            </svg>
                                        </div>
                                  
                                        <span style="margin-left: 8px;float: left;">
                                            <label for="show_close_link_yes_vc" <?php
                                                if (is_rtl()) {
                                                    echo 'style="float:right; margin-right:167px;"';
                                                }
                                                ?>>
                                                <span <?php
                                        if (is_rtl()) {
                                            echo 'style="margin-left:5px;"';
                                        }
                                        ?>></span><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?>
                                            </label>
                                        </span>
                                    </div>
                                </div>

                                <div class="arf_radio_wrapper arfminwidth30">
                                    <div class="arf_custom_radio_div">
                                        <div class="arf_custom_radio_wrapper">
                                            <input onclick="is_close_link_change();" type="radio" name="is_close_link_vc" value="no" id="show_close_link_no_vc" class="arf_custom_radio" />
                                            <svg width="18px" height="18px"r>
                    					    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                    					    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                            </svg>
                                        </div>
                                    
                                        <span style="margin-left: 8px;float: left;">
                                            <label for="show_close_link_no_vc" <?php
                                                if (is_rtl()) {
                                                    echo 'style="float:right;"';
                                                }
                                                ?>>
                                                <span <?php
                                        if (is_rtl()) {
                                            echo 'style="margin-left:5px;"';
                                        }
                                        ?>></span><?php echo addslashes(esc_html__('No', 'ARForms')); ?>
                                            </label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="arf_img_url_vc" style="display:none;">
                            <label class="arf_label arf_vc_label"><?php echo addslashes(esc_html__('Image Url :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;">
                                <input type="text" name="arf_img_url" id="arf_img_url" value="" class="wpb_vc_param_value txtstandardnew" style="width:230px;" />
                            </div>
                        </div>
                        
                        <div class="arfmodal_vcfields arfsecond_div" id="arf_img_height_vc" style="width: 225px;float:left;display:none;">
                            <label class="arf_label arf_vc_label"><?php echo addslashes(esc_html__('Image Size :', 'ARForms')); ?></label>
                            
                            <div class="arfmodal_vcfield_right" style="float:left;">
                                <div class="arf_inner_input">
                                    <input type="text" name="arf_img_width" id="arf_img_width" value="auto" class="wpb_vc_param_value txtstandardnew" />
                                    <div class="arfbgcolornote">(<?php echo addslashes(esc_html__('Width', 'ARForms')); ?>)</div>
                                </div>
                                <div class="arf_inner_input">
                                    <input type="text" name="arf_img_height" id="arf_img_height" value="auto" class="wpb_vc_param_value txtstandardnew" /> 
                                    <div class="arfbgcolornote">(<?php echo addslashes(esc_html__('Height', 'ARForms')); ?>)</div>
                                </div>
                            </div>
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="arfmodalbuttonstyles" style="display:none;min-height:95px;">
                            <label class="arf_label" style="margin-bottom:0px !important;"><?php echo addslashes(esc_html__('Button Colors :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <div style="display:inline">
				                    <div style="display: inline-block; float:left;margin-left:0px;" id="arf_btn_bgcolor arf_vc_btn_bgcolor_picker" class="arf_coloroption_sub">
            					        <div class="arf_coloroption" id='arf_vc_modal_btn_bg_color'></div>
            					        <div class="arf_coloroption_subarrow_bg">
            					            <div class="arf_coloroption_subarrow"></div>
            					        </div>
            					        <div class="arfbgcolornote" style="width:150px !important;">(<?php echo addslashes(esc_html__('Button Background', 'ARForms')); ?>)</div>
            				            <input type="hidden" name="bgcolor" id="arf_vc_modal_btn_bg_color_input" class="txtmodal1 wpb_vc_param_value" value="#808080" />
            				        </div>
				    
            				        <div style="display: inline-block; float:left;margin-left:30px !important;" id="arf_btn_txtcolor arf_vc_btn_txtcolor_picker"  class="arf_coloroption_sub">
            					        <div class="arf_coloroption" id="arf_vc_modal_btn_txt_color"></div>
            					        <div class="arf_coloroption_subarrow_bg">
            					            <div class="arf_coloroption_subarrow"></div>
            					        </div>
            					        <div class="arfbgcolornote">(<?php echo addslashes(esc_html__('Button Text', 'ARForms')); ?>)</div>
            				            <input type="hidden" name="txtcolor" id="arf_vc_modal_btn_txt_color_input" class="txtmodal1 wpb_vc_param_value" value="#FFFFFF" />
            				        </div>
                                </div>
                            </div>
                        </div> 

                        <div class="arfmodal_vcfields arfsecond_div" style="margin-bottom: 20px;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Size :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <div style="display:inline;">
                                    <div class="height_setting" style="float: left;display: none;">
                                            <input type="text" onkeyup="if (jQuery(this).val() == 'auto') { jQuery('span#arf_vc_height_px').hide(); } else { jQuery('span#arf_vc_height_px').show(); }" class="wpb_vc_param_value txtstandardnew" name="height" id="modal_height" value="auto" style="width:70px;" />&nbsp;<span style="display:none;"  class="arf_px" id="arf_vc_height_px">px &nbsp; &nbsp;</span><br/>
                                            <div style="margin-top: 4px;padding-left: 22px; width: 50px !important;line-height: normal !important;" class="arfbgcolornote"><?php echo addslashes(esc_html__('Height', 'ARForms')); ?>
                                            </div>
                                    </div>                    
                                    <div class="height_setting" style="float: left;">
                                        <input type="text" class="wpb_vc_param_value txtstandardnew" name="width" id="modal_width" value="800" style="width:70px;" />&nbsp;<span class="arf_px">px</span><br/><div class="arfbgcolornote"><?php echo addslashes(__('Width &nbsp; (Form width will be overwritten)', 'ARForms')); ?></div>
                                       
                                    </div>
                                </div>
                            </div>          
                        </div>
                        
                        <div class="arfmodal_vcfields arfsecond_div" id="button_angle_div_vc" style="float:left;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Button angle :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <div class="sltmodal" style="float:none; font-size:15px;display:inline-block; <?php if (is_rtl()) { echo 'text-align:right;'; } else { echo 'text-align:left;'; } ?>">
                                    <?php

                                        $arf_angle_attr = array( 'onchange' => 'changeflybutton();',
                                                                 'class' => 'wpb_vc_param_value');
                                        $arf_angle_opts = array( '0' => addslashes(esc_html__('0', 'ARForms')),
                                                                 '90' => addslashes(esc_html__('90', 'ARForms')),
                                                                 '-90' => addslashes(esc_html__('-90', 'ARForms')) );

                                        echo $maincontroller->arf_selectpicker_dom( 'angle', 'button_angle', '', 'width:85px;', '0', $arf_angle_attr, $arf_angle_opts );
                                    ?>
                                </div>
                            </div>          
                        </div>


                        <div class="arfmodal_vcfields arfsecond_div" id="ideal_time">
                            <label class="arf_label"><?php echo addslashes(esc_html__('Show after user is inactive for', 'ARForms')); ?></label>     
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <input type="text" name="inactive_min" id="inact_time" value="1" class="wpb_vc_param_value txtstandardnew" style="width:70px;" />
                                <span style="font-size:12px;"><?php echo addslashes(esc_html__('(in Minute)', 'ARForms')); ?></span>
                            </div> 
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="arf_full_screen_modal" style="text-align: left">
                            <label style="text-align: left;"><?php echo addslashes(esc_html__('Show Full Screen Popup :', 'ARForms')); ?></label>
                            <div class="radio_selection ">
                             <input type="hidden" class="arf_custom_radio wpb_vc_param_value" name="is_fullscreen" value="no" id="is_fullscreen_id"/>
                               <div class="arf_radio_wrapper arfminwidth30">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio wpb_vc_param_value" name="_is_fullscreen" value="yes" id="show_full_screen_yes" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="show_full_screen_yes" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>
                               <div class="arf_radio_wrapper arfminwidth30">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio wpb_vc_param_value" checked="checked" name="_is_fullscreen" value="no" id="show_full_screen_no" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="show_full_screen_no" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('No', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>
                            </div>                        

                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="modal_effect_div">
                            <label class="arf_label"><?php echo addslashes(esc_html__('Animation Effect', 'ARForms')); ?></label>
                            <div class="dt_dl" id="" style="<?php
                                if (is_rtl()) {
                                    echo 'text-align:right;';
                                } else {
                                    echo 'text-align:left;';
                                }
                                ?>">
                                
                                <?php
                                    $arf_modaleffect_attr = array( 'class' => 'wpb_vc_param_value' );
                                    $arf_modaleffect_opts = array( 'no_animation' => addslashes(esc_html__('No Animation', 'ARForms')),
                                                             'fade_in' => addslashes(esc_html__('Fade in', 'ARForms')),
                                                             'slide_in_top' => addslashes(esc_html__('Slide In Top', 'ARForms')),
                                                             'slide_in_bottom' => addslashes(esc_html__('Slide In Bottom', 'ARForms')),
                                                             'slide_in_right' => addslashes(esc_html__('Slide In Right', 'ARForms')),
                                                             'slide_in_left' => addslashes(esc_html__('Slide In Left', 'ARForms')),
                                                             'zoom_in' => addslashes(esc_html__('Zoom In', 'ARForms')),
                                                              );

                                    echo $maincontroller->arf_selectpicker_dom( 'modaleffect', 'modal_effect', '', 'width:135px;', 'fade_in', $arf_modaleffect_attr, $arf_modaleffect_opts );
                                ?>
                            </div>                     
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="hide_popup_loggedin_user_div">
                            <label class="arf_label"><?php echo addslashes(esc_html__('Hide popup for Logged in User', 'ARForms')); ?></label>
                            <input type="hidden" class="arf_custom_radio wpb_vc_param_value" name="hide_popup_for_loggedin_user" value="no" id="hide_popup_for_loggedin_user_id"/>
                            <div class="arf_custom_checkbox_wrapper" style="margin-left:4px; margin-top: 9px;">
                                <input type="checkbox" id="_hide_popup_for_loggedin_user" name="_hide_popup_for_loggedin_user" value="yes" style="border:none;">
                                <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
                        </div>
                </div>

                <div style="float:left; width:100%; height:25px;"> </div>
                <div style="clear:both;"></div>
                <script type="text/javascript" data-cfasync="false">
                    __LINK_POSITION_TOP = '<?php echo addslashes(esc_html__('Top', 'ARForms')); ?>';
                    __LINK_POSITION_BOTTOM = '<?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?>';
                    __LINK_POSITION_LEFT = '<?php echo addslashes(esc_html__('Left', 'ARForms')); ?>';
                    __LINK_POSITION_RIGHT = '<?php echo addslashes(esc_html__('Right', 'ARForms')); ?>';
		    __BLANK_FORM_MSG  = '<?php echo addslashes(esc_html__('Please select a form', 'ARForms')) ?>';
                </script>
            </div>   

		 </div>
                                                            </div>
            <?php
        }
    }

}
?>