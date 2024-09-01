<?php

class maincontroller {

    function __construct() {
        global $is_active_cornorstone;
        add_action('admin_menu', array($this, 'menu'));

        add_action('admin_head', array($this, 'menu_css'));

        add_filter('plugin_action_links_arforms/arforms.php', array($this, 'settings_link'), 10, 2);

        add_action('init', array($this, 'front_head'));

        /* we have move this action to `editor_init` instead of `init` there is not necessity to fire it at all places */
        add_action('before_arforms_editor_init', array($this, 'arf_update_auto_increment_after_install'), 11, 0);

        add_action('wp_head', array($this, 'arf_register_add_action'),1);

        add_action('wp_head', array($this, 'front_head_js'), 1, 0);

        add_action('wp_footer', array($this, 'footer_js'), 2, 0);

        add_action('admin_footer', array($this, 'wp_enqeue_footer_script'), 10);

        add_action('admin_init', array($this, 'admin_js'), 11);

        add_action('admin_enqueue_scripts', array($this, 'set_js'), 11);

        add_action('admin_enqueue_scripts', array($this, 'set_css'), 11);

        register_activation_hook(FORMPATH . '/arforms.php', array($this, 'install'));

        register_activation_hook(FORMPATH . '/arforms.php', array($this, 'arfforms_check_network_activation'));

        add_action('init', array($this, 'parse_standalone_request'));

        add_action('init', array($this, 'arf_start_session'),1 ); 

        add_shortcode('ARForms', array($this, 'get_form_shortcode'));

        add_filter('widget_text', array($this, 'widget_text_filter'), 9);

        add_shortcode('ARForms_popup', array($this, 'get_form_shortcode_popup'));

        add_filter('widget_text', array($this, 'widget_text_filter_popup'), 9);

        add_action('arfstandaloneroute', array($this, 'globalstandalone_route'), 10, 2);

        add_filter('upgrader_pre_install', array($this, 'arf_backup'), 10, 2);

        add_action('admin_init', array($this, 'upgrade_data'));

        add_action('admin_init', array($this, 'arfafterinstall'));

        add_action('init', array($this, 'arfafterinstall_front'));

        add_action('admin_init', array($this, 'arf_db_check'));

        add_filter('the_content', array($this, 'arf_modify_the_content'), 10000);

        add_filter('widget_text', array($this, 'arf_modify_the_content'), 10000);

        add_action('admin_head', array($this, 'arf_hide_update_notice_to_all_admin_users'), 10000);

        add_action('init', array($this, 'arf_export_form_data'));

        add_action('wp_head', array($this, 'arf_front_assets'), 1, 0);

        add_action('print_admin_scripts', array($this, 'arf_print_all_admin_scripts'));

        /* Add what's new popup */
        add_action('admin_footer', array($this, 'arf_add_new_version_release_note'), 1);
        add_action('wp_ajax_arf_dont_show_upgrade_notice', array($this, 'arf_dont_show_upgrade_notice'), 1);

        add_action( 'init', array( $this, 'arf_prevent_addon_configuration') );


        if( !function_exists('is_plugin_active') ){
            require(ABSPATH.'/wp-admin/includes/plugin.php');
        }
        /* Register Element for Cornerstone */
        if($is_active_cornorstone){
            add_action('cornerstone_register_elements', array($this, 'arforms_cs_register_element'));
            add_filter('cornerstone_icon_map', array($this, 'arforms_cs_icon_map'));
        }
        /* Register Element for Cornerstone */
        if( is_plugin_active('wp-rocket/wp-rocket.php') && !is_admin() ){
            add_filter('script_loader_tag', array($this, 'arf_prevent_rocket_loader_script'), 10, 2);
        }

        if( is_admin() ){
            add_filter('script_loader_tag', array($this,'arf_defer_attribute_to_js_for_editor'),10, 2);
        }

        if( !is_admin() ){
            add_filter( 'script_loader_tag', array( $this, 'arf_modify_rocket_script_clf'), 10,2 );
            add_filter( 'script_loader_tag', array( $this, 'arf_defer_attribute_for_assets'), 10, 2);
        }
	
    	add_action('wp_ajax_arf_change_entries_separator',  array($this,'changes_export_entry_separator'));

        add_action('user_register',array($this,'arf_add_capabilities_to_new_user'));

        add_action('admin_init',array($this,'arf_plugin_add_suggested_privacy_content'),20);

        if( is_plugin_active('elementor/elementor.php') ){
            add_action('wp_print_scripts',array($this,'arf_dequeue_elementor_script'),100);
        }
	
	    add_filter( 'upload_mimes',array($this,'arf_custom_mime_types'));

        add_action('enqueue_block_editor_assets',array($this,'arf_enqueue_gutenberg_assets'));

        add_filter( 'elementor/frontend/the_content', array( $this, 'arf_elementor_frontend_content' ) );

        add_action( 'login_footer', array( $this, 'arf_login_footer' ) );

        add_action( 'wp_ajax_arf_regenerate_nonces', array( $this, 'arf_regenerate_nonces' ) );

        add_action( 'admin_notices', array( $this, 'arf_display_addon_update_notice' ) );
    }

    function arf_prevent_addon_configuration(){

        if( !empty( $_REQUEST['page'] ) && 'ARForms-user-registration' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformsusersignup/arformsusersignup.php' );
            $arf_user_signup_version = $arf_addon_data['Version'];
            if( !empty( $arf_user_signup_version ) && version_compare( $arf_user_signup_version, '2.4', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-user-registration&arf_error_flag=true&plugin=user_signup_addon&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        } else if( !empty( $_REQUEST['page'] ) && 'ARForms-AuthNet' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformsauthorizenet/arformsauthorizenet.php' );
            $arf_authnet_version = $arf_addon_data['Version'];
            if( !empty( $arf_authnet_version ) && version_compare( $arf_authnet_version, '2.4', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-AuthNet&arf_error_flag=true&plugin=authorizenet&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        } else if( !empty( $_REQUEST['page'] ) && 'ARForms-Stripe' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformsstripe/arformsstripe.php' );
            $arf_stripe_version = $arf_addon_data['Version'];
            if( !empty( $arf_stripe_version ) && version_compare( $arf_stripe_version, '2.9', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-Stripe&arf_error_flag=true&plugin=stripe&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        } else if( !empty( $_REQUEST['page'] ) && 'ARForms-postcreator' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformspostcreator/arformspostcreator.php' );
            $arf_postcreator_version = $arf_addon_data['Version'];
            if( !empty( $arf_postcreator_version ) && version_compare( $arf_postcreator_version, '2.0', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-postcreator&arf_error_flag=true&plugin=post_creator&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        } else if( !empty( $_REQUEST['page'] ) && 'ARForms-View' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformsview/arformsview.php' );
            $arf_view_addon = $arf_addon_data['Version'];
            if( !empty( $arf_view_addon ) && version_compare( $arf_view_addon, '1.4', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-View&arf_error_flag=true&plugin=arf_view&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        } else if( !empty( $_REQUEST['page'] ) && 'ARForms-Mailster' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformsmymail/arformsmymail.php' );
            $arf_mailster_version = $arf_addon_data['Version'];
            if( !empty( $arf_mailster_version ) && version_compare( $arf_mailster_version, '2.3', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-Mailster&arf_error_flag=true&plugin=mailster&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        } else if( !empty( $_REQUEST['page'] ) && 'ARForms-Payfast' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformspayfast/arformspayfast.php' );
            $arf_payfast_version = $arf_addon_data['Version'];
            if( !empty( $arf_payfast_version ) && version_compare( $arf_payfast_version, '1.2', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-Payfast&arf_error_flag=true&plugin=payfast&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        } else if( !empty( $_REQUEST['page'] ) && 'ARForms-Paypal' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformspaypal/arformspaypal.php' );
            $arf_paypal_version = $arf_addon_data['Version'];
            if( !empty( $arf_paypal_version ) && version_compare( $arf_paypal_version, '2.6', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-Paypal&arf_error_flag=true&plugin=paypal&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        } else if( !empty( $_REQUEST['page'] ) && 'ARForms-paypalpro' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformspaypalpro/arformspaypalpro.php' );
            $arf_paypal_pro_version = $arf_addon_data['Version'];
            if( !empty( $arf_paypal_pro_version ) && version_compare( $arf_paypal_pro_version, '2.0', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-paypalpro&arf_error_flag=true&plugin=paypal_pro&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        } else if( !empty( $_REQUEST['page'] ) && 'ARForms-Mollie' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformsmollie/arformsmollie.php' );
            $arf_mollie_version = $arf_addon_data['Version'];
            if( !empty( $arf_mollie_version ) && version_compare( $arf_mollie_version, '1.7', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-Mollie&arf_error_flag=true&plugin=mollie&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        } else if( !empty( $_REQUEST['page'] ) && 'ARForms-Zapier' == $_REQUEST['page'] && !empty( $_REQUEST['arfaction'] ) ){
            $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformszapier/arformszapier.php' );
            $arf_zapier_version = $arf_addon_data['Version'];
            if( !empty( $arf_zapier_version ) && version_compare( $arf_zapier_version, '1.6', '<' ) ){
                $redirect_url = admin_url( 'admin.php?page=ARForms-Zapier&arf_error_flag=true&plugin=zapier&notice_type=update' );
                wp_redirect( $redirect_url );
                die;
            }
        }

    }

    function arf_display_addon_update_notice(){

        if( !empty( $_REQUEST['arf_error_flag'] ) && 'true' == $_REQUEST['arf_error_flag'] ){

            if( isset( $_REQUEST['notice_type'] ) && $_REQUEST['notice_type'] == 'update' ){

                $plugin_name = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( $_REQUEST['plugin'] ) : '';

                if( !empty( $plugin_name ) ){
                    $class = 'notice addon_notice_wrapper notice-error arf-notice-update-warning is-dismissible';
                    if( 'user_signup_addon' == $plugin_name ){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - User Signup Add-on</strong> to the latest version.</p></div>';
                    } else if ( 'authorizenet' == $plugin_name ){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - Authorize.net Add-on</strong> to the latest version.</p></div>';
                    } else if ( 'stripe' == $plugin_name ){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - Stripe Add-on</strong> to the latest version.</p></div>';
                    } else if ( 'post_creator' == $plugin_name ){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - Post Creator Add-on</strong> to the latest version.</p></div>';
                    } else if ( 'arf_view' == $plugin_name ){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - Front End Entry View Add-on</strong> to the latest version.</p></div>';
                    } else if ( 'mailster' == $plugin_name ){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - Mailster Add-on</strong> to the latest version.</p></div>';
                    } else if ( 'payfast' == $plugin_name ){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - Payfast Add-on</strong> to the latest version.</p></div>';
                    } else if ( 'paypal' == $plugin_name ){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - PayPal Add-on</strong> to the latest version.</p></div>';
                    } else if ( 'paypal_pro' == $plugin_name ){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - PayPal Pro Add-on</strong> to the latest version.</p></div>';
                    } else if ( 'mollie' == $plugin_name ){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - Mollie Add-on</strong> to the latest version.</p></div>';
                    } else if( 'zapier' == $plugin_name){
                        echo '<div class="' .esc_attr( $class ) .'"><p>Sorry! You will not be able to add/edit configuration because you need to update <strong>ARForms - Zapier Add-on</strong> to the latest version.</p></div>';
                    }
                }

            }

        }
    }

    function arf_login_footer(){
        $arf_script = '<script type="text/javascript" data-cfasync="false">';
            $arf_script .= 'jQuery(document).ready(function(){';
                $arf_script .= 'if( typeof window.parent.adminpage != "undefined" && window.parent.adminpage == "toplevel_page_ARForms" ){';
                    $arf_script .= 'if( document.getElementById("loginform") == null && window.parent.arforms_regenerate_nonce != null ){';
                        $arf_script .= ' window.parent.arforms_regenerate_nonce(); ';
                    $arf_script .= '}';
                $arf_script .= '} else if( window.opener != null && typeof window.opener.adminpage != "undefined" && window.opener.adminpage == "toplevel_page_ARForms" ){';
                    $arf_script .= 'if( document.getElementById("loginform") == null && window.opener != null && window.opener.arforms_regenerate_nonce != null ){';
                        $arf_script .= ' window.opener.arforms_regenerate_nonce(); ';
                        $arf_script .= ' window.close() ';
                    $arf_script .= '}';
                $arf_script .= '}';
            $arf_script .= '});';
        $arf_script .= '</script>';

        echo $arf_script;
    }

    function arf_regenerate_nonces(){
        echo json_encode(
            array(
                'arf_editor_nonce' => wp_create_nonce( 'arf_edit_form_nonce' )
            )
        );
        die;
    }

    function arf_elementor_frontend_content( $content ){
        $pattern = '/\[(\[?)(ARForms|ARForms_popup)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)/';
        if( preg_match( $pattern, $content ) ){
            return do_shortcode( $content );
        } else {
            return $content;
        }

    }
    
    function arf_enqueue_gutenberg_assets(){

        global $arfversion, $wpdb, $MdlDb ;

        $page = basename($_SERVER['PHP_SELF']);

        if (in_array($page, array('post.php', 'page.php', 'page-new.php', 'post-new.php')) or ( isset($_GET) and isset($_GET['page']) and $_GET['page'] == 'ARForms-entry-templates') ) {

            
            wp_register_script('arforms_gutenberg_script',ARFURL.'/js/arf_gutenberg_script.js',array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components'),$arfversion);
            
            wp_enqueue_script('arforms_gutenberg_script');
            
            wp_register_style('arforms_gutenberg_style',ARFURL.'/css/arf_gutenberg_style.css',array(), $arfversion);
            
            wp_enqueue_style('arforms_gutenberg_style');
            
        }else if ( in_array($page, array('widgets.php')) ) {
            
            wp_enqueue_script( 'jquery');
            
            wp_register_script('arforms_gutenberg_script',ARFURL.'/js/arf_gutenberg_widget_script.js',array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components'),$arfversion);

            wp_enqueue_script('arforms_gutenberg_script');

            wp_register_style('arforms_gutenberg_style',ARFURL.'/css/arf_gutenberg_style.css',array(), $arfversion);

            wp_enqueue_style('arforms_gutenberg_style');

            $arforms_forms = $MdlDb->forms;

            $arforms_forms_data = $wpdb->get_results("SELECT * FROM `".$arforms_forms."` WHERE is_template=0 AND (status is NULL OR status = '' OR status = 'published') ORDER BY id DESC");

            $arforms_forms_list = array();
            $n = 0;
            foreach( $arforms_forms_data as $k => $value ){
                $arforms_forms_list[$n]['id'] = $value->id;
                $arforms_forms_list[$n]['label'] = $value->name . ' (id: '.$value->id.')';
                $n++;
            }

            wp_localize_script('arforms_gutenberg_script','arforms_list_for_gutenberg',$arforms_forms_list);

        }
    }
    function arf_custom_mime_types($mimes){

        $mimes['heic'] = 'image/heic';
        $mimes['heif'] = 'image/heif';
        return $mimes;
    }

    function arf_dequeue_elementor_script(){
        global $wp_scripts;
            
        if( isset($_GET['page']) && preg_match('/ARForms*/', $_GET['page']) ){
            
            wp_deregister_script('backbone-marionette');
            wp_dequeue_script('backbone-marionette');

            wp_deregister_script('backbone-radio');
            wp_dequeue_script('backbone-radio');            

            wp_deregister_script('elementor-common');
            wp_dequeue_script('elementor-common');            
            
            wp_deregister_script('editor-preview');
            wp_dequeue_script('editor-preview');

            wp_deregister_script('elementor-admin');
            wp_dequeue_script('elementor-admin');

            wp_deregister_script('wp-color-picker-alpha');
            wp_dequeue_script('wp-color-picker-alpha');
        
        }
    }

    function arf_plugin_add_suggested_privacy_content(){
        global $arfsettings;

        $content  = '<b>'.esc_html__('Who we are?','ARForms').'</b>';
        $content .= '<p>'. esc_html__('ARForms is a WordPress Premium Form Builder Plugin to create stylish and modern style form withing few clicks.','ARForms').' </p>';
        $content .= '<br/>';
        $content .= '<b>'.esc_html__('What Personal Data we collect and why we collect it.','ARForms').'</b>';
        $content .= '<p>'.esc_html__('ARForms stores ip address and country of visitor. However, ARForms provide an option to prevent storing visitor data.','ARForms').'</p>';
        $content .= '<p>'.esc_html__('ARForms will not store any personal data except user_id (only if user is logged in), ip address, country, browser user_agent, referrer only when submit the form.','ARForms').'</p>';
        $content .= '<p>'.esc_html__('We store this data to provide the analytics of the visitor and the user who submit the form.','ARForms').'</p>';
        $content .= '<p>'.esc_html__('ARForms will also store the all type of data (this may contain personal data as well as subscribe user to third party opt-in like MailChimp, Aweber, etc) in the database which plugin user has included in the form. These data are editable as well as removable from form entry section of ARForms.','ARForms').'</p>';

        if( function_exists('wp_add_privacy_policy_content') ){
            wp_add_privacy_policy_content('ARForms', $content);
        }
    }

    function arf_register_add_action(){
        ?>
        <script type="text/javascript" data-cfasync="false">
            if( typeof arf_add_action == 'undefined' ){
                
            arf_actions = [];
            function arf_add_action( action_name, callback, priority ) {
                if ( ! priority )  {
                    priority = 10;
                }
                
                if ( priority > 100 ) {
                    priority = 100;
                } 
                
                if ( priority < 0 ) {
                    priority = 0;
                }

                if( typeof arf_actions == 'undefined' ){
                    arf_actions = [];
                }
                
                if ( typeof arf_actions[action_name] == 'undefined' ) {
                    arf_actions[action_name] = [];
                }
                
                if ( typeof arf_actions[action_name][priority] == 'undefined' ) {
                    arf_actions[action_name][priority] = []
                }
                
                arf_actions[action_name][priority].push( callback );
            }
            function arf_do_action() {
                if ( arguments.length == 0 ) {
                    return;
                }
                
                var args_accepted = Array.prototype.slice.call(arguments),
                    action_name = args_accepted.shift(),
                    _this = this,
                    i,
                    ilen,
                    j,
                    jlen;
                
                if ( typeof arf_actions[action_name] == 'undefined' ) {
                    return;
                }
                
                for ( i = 0, ilen=100; i<=ilen; i++ ) {
                    if ( arf_actions[action_name][i] ) {
                        for ( j = 0, jlen=arf_actions[action_name][i].length; j<jlen; j++ ) {
                            if( typeof window[arf_actions[action_name][i][j]] != 'undefined' ){
                                window[arf_actions[action_name][i][j]](args_accepted);
                            }
                        }
                    }
                }
            }
            }
        </script>
      <?php
    }
    
    function arf_add_capabilities_to_new_user($user_id){
	   global $armainhelper;
    	if( $user_id == '' ){
    	    return;
    	}
    	if( user_can($user_id,'administrator')){

    	    global $current_user;
    	    $arfroles = $armainhelper->frm_capabilities();

    	    $userObj = new WP_User($user_id);
    	    foreach ($arfroles as $arfrole => $arfroledescription){
    		  $userObj->add_cap($arfrole);
    	    }
    	    unset($arfrole);
    	    unset($arfroles);
    	    unset($arfroledescription);
    	}
    }
    /**
     *       arf_dev_flag review below function's query
     * * */
    function arf_update_auto_increment_after_install() {
        global $wpdb, $MdlDb;
        $result_1 = $wpdb->get_results("SHOW TABLE STATUS LIKE '" . $MdlDb->forms . "'");
        if ($result_1[0]->Auto_increment < 100) {
            $wpdb->query("ALTER TABLE {$MdlDb->forms} AUTO_INCREMENT = 100");
        }
    }

    function arf_prevent_rocket_loader_script($tag, $handle) {
        
        $script = htmlspecialchars($tag);
        $pattern2 = '/\/(wp\-content\/plugins\/arforms)|(wp\-includes\/js)/';
        preg_match($pattern2,$script,$match_script);

        if( !isset($match_script[0]) || $match_script[0] == '' ){
            return $tag;
        }

        $pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
        preg_match_all($pattern, $tag, $matches);
        if (!is_array($matches)) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if (!empty($matches) && !empty($matches[2]) && !empty($matches[2][0]) && strtolower(trim($matches[2][0])) != 'data-cfasync=') {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if (!empty($matches) && empty($matches[2])) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else {
            return $tag;
        }
    }

    function arf_defer_attribute_to_js_for_editor($tag, $handle){
        if( isset($_GET['page']) && $_GET['page'] == 'ARForms' && isset($_GET['arfaction']) && $_GET['arfaction'] != ''  ){
            $script = htmlspecialchars($tag);
            $pattern = '/\/(wp\-content\/plugins\/arforms)/';
            preg_match($pattern,$script,$match_script);

            if( !isset($match_script[0]) || $match_script[0] == '' ){
                return $tag;
            }

            return str_replace( ' src', ' defer="defer" src', $tag);
        } else {
            return $tag;
        }
    }

    function arf_defer_attribute_for_assets( $tag, $handle ){
        if( !is_admin() ){
            $script = htmlspecialchars($tag);
            $pattern = '/\/(wp\-content\/plugins\/arforms)/';
            preg_match($pattern,$script,$match_script);

            if( !isset($match_script[0]) || $match_script[0] == '' ){
                return $tag;
            }

            $tag = str_replace( ' src', ' defer src', $tag);

            $pattern = '/(id=\'arforms\-js\-after\')/';

            preg_match( $pattern, $script, $mat );

            if( preg_match( $pattern, $script ) ){
                $tag = preg_replace( '/(id=\'arforms\-js\-after\')/', 'data-cfasync="false" defer $1', $tag );
            }
        }

        return $tag;
    }

    function arf_modify_rocket_script_clf( $tag, $handle ){
        $script = htmlspecialchars($tag);
        $pattern2 = '/\/(wp\-content\/plugins\/arforms)|(wp\-includes\/js)/';
        preg_match($pattern2,$script,$match_script);

        if( !isset($match_script[0]) || $match_script[0] == '' ){
            return $tag;
        }

        $pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
        preg_match_all($pattern, $tag, $matches);

        $pattern3 = '/type\=(\'|")[a-zA-Z0-9]+\-(text\/javascript)(\'|")/';
        preg_match_all($pattern3, $tag, $match_tag);

        if( !isset( $match_tag[0] ) || '' == $match_tag[0] ){
            return $tag;
        }

        if (!is_array($matches)) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if (!empty($matches) && !empty($matches[2]) && !empty($matches[2][0]) && strtolower(trim($matches[2][0])) != 'data-cfasync=') {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if (!empty($matches) && empty($matches[2])) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else {
            return $tag;
        }

    }

    function arf_get_remote_post_params($plugin_info = "") {
        global $wpdb, $arfversion;

        $action = "";
        $action = $plugin_info;

        if (!function_exists('get_plugins')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_list = get_plugins();
        $site_url = home_url();
        $plugins = array();

        $active_plugins = get_option('active_plugins');

        foreach ($plugin_list as $key => $plugin) {
            $is_active = in_array($key, $active_plugins);


            if (strpos(strtolower($plugin["Title"]), "arforms") !== false) {
                $name = substr($key, 0, strpos($key, "/"));
                $plugins[] = array("name" => $name, "version" => $plugin["Version"], "is_active" => $is_active);
            }
        }
        $plugins = json_encode($plugins);


        $theme = wp_get_theme();
        $theme_name = $theme->get("Name");
        $theme_uri = $theme->get("ThemeURI");
        $theme_version = $theme->get("Version");
        $theme_author = $theme->get("Author");
        $theme_author_uri = $theme->get("AuthorURI");

        $im = is_multisite();
        $sortorder = get_option("arfSortOrder");

        $post = array("wp" => get_bloginfo("version"), "php" => phpversion(), "mysql" => $wpdb->db_version(), "plugins" => $plugins, "tn" => $theme_name, "tu" => $theme_uri, "tv" => $theme_version, "ta" => $theme_author, "tau" => $theme_author_uri, "im" => $im, "sortorder" => $sortorder);

        return $post;
    }

    public static function arfforms_check_network_activation($network_wide) {
        if (!$network_wide)
            return;

        deactivate_plugins(plugin_basename(__FILE__), TRUE, TRUE);

        header('Location: ' . network_admin_url('plugins.php?deactivate=true'));
        exit;
    }

    function arf_modify_the_content($content) {

        /* arf_dev_flag removed */
        $regex = '/<arfsubmit>(.*?)<\/arfsubmit>/is';
        $content = preg_replace_callback($regex, array($this, 'arf_the_content_remove_ptag'), $content);

        /* arf_dev_flag removed */
        $regex = '/<arffile>(.*?)<\/arffile>/is';
        $content = preg_replace_callback($regex, array($this, 'arf_the_content_remove_ptag'), $content);

        /* arf_dev_flag removed */
        $regex = '/<arfpassword>(.*?)<\/arfpassword>/is';
        $content = preg_replace_callback($regex, array($this, 'arf_the_content_remove_ptag'), $content);

        /* arf_dev_flag removed */
        $content = preg_replace("/<arfsubmit>|<\/arfsubmit>|<arffile>|<\/arffile>|<arfpassword>|<\/arfpassword>/is", '', $content);

        return $content;
    }

    function arf_the_content_remove_ptag($match) {
        $content = $match[1];

        $content = preg_replace('|<p>|', '', $content);

        $content = preg_replace('|</p>|', '', $content);

        $content = preg_replace('|<br />|', '', $content);

        return $content;
    }

    function arf_the_content_removeptag($matches) {
        return $matches[1];
    }

    function arf_the_content_removeemptyptag($matches) {
        return $matches[1];
    }

    function arfafterinstall() {
        global $arfsettings;
        $arfsettings = get_transient('arf_options');

        if (!is_object($arfsettings)) {
            if ($arfsettings) {
                $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
            } else {
                $arfsettings = get_option('arf_options');


                if (!is_object($arfsettings)) {
                    if ($arfsettings)
                        $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
                    else
                        $arfsettings = new arsettingmodel();
                    update_option('arf_options', $arfsettings);
                    set_transient('arf_options', $arfsettings);
                }
            }
        }

        $arfsettings->set_default_options();



        global $style_settings;

        $style_settings = get_transient('arfa_options');
        if (!is_object($style_settings)) {
            if ($style_settings) {
                $style_settings = maybe_unserialize(maybe_serialize($style_settings));
            } else {
                $style_settings = get_option('arfa_options');
                if (!is_object($style_settings)) {
                    if ($style_settings)
                        $style_settings = maybe_unserialize(maybe_serialize($style_settings));
                    else
                        $style_settings = new arstylemodel();
                    update_option('arfa_options', $style_settings);
                    set_transient('arfa_options', $style_settings);
                }
            }
        }

        $style_settings = get_option('arfa_options');
        if (!is_object($style_settings)) {
            if ($style_settings)
                $style_settings = maybe_unserialize(maybe_serialize($style_settings));
            else
                $style_settings = new arstylemodel();
            update_option('arfa_options', $style_settings);
        }

        $style_settings->set_default_options();

        if (!is_admin() and $arfsettings->jquery_css)
            $arfdatepickerloaded = true;

        global $arfadvanceerrcolor;

        $arfadvanceerrcolor = array('white' => '#e9e9e9|#000000|#e9e9e9', 'black' => '#000000|#FFFFFF|#000000', 'darkred' => '#ed4040|#FFFFFF|#ed4040', 'blue' => '#D9EDF7|#31708F|#0561bf', 'pink' => '#F2DEDE|#A94442|#508b27', 'yellow' => '#FAEBCC|#8A6D3B|#af7a0c', 'red' => '#EF8A80|#FFFFFF|#1393c3', 'green' => '#6CCAC9|#FFFFFF|#7a37ac', 'color1' => '#6cca7b|#FFFFFF|#fb9900', 'color2' => '#c2b079|#FFFFFF|#ed40ae', 'color3' => '#f3b431|#FFFFFF|#ff6600', 'color4' => '#6d91d3|#FFFFFF|#0bb7b5', 'color5' => '#a466cc|#FFFFFF|#a79902');

        global $arfdefaulttemplate;
        $arfdefaulttemplate = array(
            '3' => array('name' => addslashes(esc_html__('Contact us', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '1' => array('name' => addslashes(esc_html__('Subscription Form', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '5' => array('name' => addslashes(esc_html__('Feedback Form', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '6' => array('name' => addslashes(esc_html__('RSVP Form', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '2' => array('name' => esc_html__('Registration Form', 'ARForms'),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '4' => array('name' => esc_html__('Survey Form', 'ARForms'),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '7' => array('name' => esc_html__('Job Application', 'ARForms'),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '8' => array('name' => addslashes(esc_html__('Donation Form', 'ARForms')),'theme'=> addslashes(esc_html__('material', 'ARForms'))),
            '9' => array('name' => addslashes(esc_html__('Request a Quote', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '10' => array('name' => addslashes(esc_html__('Member Login', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '11' => array('name' => addslashes(esc_html__('Order Form', 'ARForms')),'theme'=> addslashes(esc_html__('material', 'ARForms'))),
        );

        global $arfmsgtounlicop;
        $arfmsgtounlicop = "(";
        $arfmsgtounlicop .= "Un";
        $arfmsgtounlicop .= "lic";
        $arfmsgtounlicop .= "ens";
        $arfmsgtounlicop .= "ed";
        $arfmsgtounlicop .= ")";
    }

    function arfafterinstall_front() {
        if (!is_admin()) {
            global $arfsettings;
            $arfsettings = get_transient('arf_options');

            if (!is_object($arfsettings)) {
                if ($arfsettings) {
                    $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
                } else {
                    $arfsettings = get_option('arf_options');

                    if (!is_object($arfsettings)) {
                        if ($arfsettings)
                            $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
                        else
                            $arfsettings = new arsettingmodel();
                        update_option('arf_options', $arfsettings);
                        set_transient('arf_options', $arfsettings);
                    }
                }
            }

            $arfsettings->set_default_options();



            global $style_settings;

            $style_settings = get_transient('arfa_options');
            if (!is_object($style_settings)) {
                if ($style_settings) {
                    $style_settings = maybe_unserialize(maybe_serialize($style_settings));
                } else {
                    $style_settings = get_option('arfa_options');
                    if (!is_object($style_settings)) {
                        if ($style_settings)
                            $style_settings = maybe_unserialize(maybe_serialize($style_settings));
                        else
                            $style_settings = new arstylemodel();
                        update_option('arfa_options', $style_settings);
                        set_transient('arfa_options', $style_settings);
                    }
                }
            }

            $style_settings = get_option('arfa_options');
            if (!is_object($style_settings)) {
                if ($style_settings)
                    $style_settings = maybe_unserialize(serialize($style_settings));
                else
                    $style_settings = new arstylemodel();
                update_option('arfa_options', $style_settings);
            }

            $style_settings->set_default_options();

            if (!is_admin() and $arfsettings->jquery_css)
                $arfdatepickerloaded = true;

            global $arfadvanceerrcolor;

            $arfadvanceerrcolor = array('white' => '#e9e9e9|#000000|#e9e9e9', 'black' => '#000000|#FFFFFF|#000000', 'darkred' => '#ed4040|#FFFFFF|#ed4040', 'blue' => '#D9EDF7|#31708F|#0561bf', 'pink' => '#F2DEDE|#A94442|#508b27', 'yellow' => '#FAEBCC|#8A6D3B|#af7a0c', 'red' => '#EF8A80|#FFFFFF|#1393c3', 'green' => '#6CCAC9|#FFFFFF|#7a37ac', 'color1' => '#6cca7b|#FFFFFF|#fb9900', 'color2' => '#c2b079|#FFFFFF|#ed40ae', 'color3' => '#f3b431|#FFFFFF|#ff6600', 'color4' => '#6d91d3|#FFFFFF|#0bb7b5', 'color5' => '#a466cc|#FFFFFF|#a79902');

            global $arfdefaulttemplate;
            $arfdefaulttemplate = array(
                '3' => addslashes(esc_html__('Contact us', 'ARForms')),
                '1' => addslashes(esc_html__('Subscription Form', 'ARForms')),
                '5' => addslashes(esc_html__('Feedback Form', 'ARForms')),
                '6' => addslashes(esc_html__('RSVP Form', 'ARForms')),
                '2' => addslashes(esc_html__('Registration Form', 'ARForms')),
                '4' => addslashes(esc_html__('Survey Form', 'ARForms')),
                '7' => addslashes(esc_html__('Job Application', 'ARForms')),
            );

            global $arfmsgtounlicop;
            $arfmsgtounlicop = "(";
            $arfmsgtounlicop .= "Un";
            $arfmsgtounlicop .= "lic";
            $arfmsgtounlicop .= "ens";
            $arfmsgtounlicop .= "ed";
            $arfmsgtounlicop .= ")";
        }
    }

    function globalstandalone_route($controller, $action) {
        global $armainhelper, $arsettingcontroller;

        if ($controller == 'fields') {


            if (!defined('DOING_AJAX'))
                define('DOING_AJAX', true);


            global $arfieldcontroller;


            if ($action == 'ajax_get_data')
                $arfieldcontroller->ajax_get_data($armainhelper->get_param('entry_id'), $armainhelper->get_param('field_id'), $armainhelper->get_param('current_field'));


            else if ($action == 'ajax_time_options')
                $arfieldcontroller->ajax_time_options();
        }else if ( $controller == 'incomplete_entries'){
            global $arrecordcontroller;

            if( 'csv' == $action ){
                $s = isset( $_REQUEST['s'] ) ? 's' : 'search';

                if( ! current_user_can( 'arfviewentries' ) ){
                    global $arfsettings;
                    wp_die($arfsettings->admin_permission);
                }

                if (!ini_get('safe_mode')) {
                    @set_time_limit(0);
                }

                global $current_user, $arfform, $arffield, $db_record, $arfrecordmeta, $wpdb, $style_settings;

                $all_form_id = $armainhelper->get_param('form');
                $search = $armainhelper->get_param($s);
                $fid = $armainhelper->get_param('fid');


                require(VIEWS_PATH . '/export_incomplete_data.php');

            }
        }else if ($controller == 'entries') {

            global $arrecordcontroller;


            if ($action == 'csv') {


                $s = isset($_REQUEST['s']) ? 's' : 'search';


                $arrecordcontroller->csv($armainhelper->get_param('form'), $armainhelper->get_param($s), $armainhelper->get_param('fid'));


                unset($s);
            } else {


                if (!defined('DOING_AJAX'))
                    define('DOING_AJAX', true);

                if ($action == 'send_email')
                    $arrecordcontroller->send_email($armainhelper->get_param('entry_id'), $armainhelper->get_param('form_id'), $armainhelper->get_param('type'));


                else if ($action == 'create')
                    $arrecordcontroller->ajax_create();

                else if ($action == 'previous')
                    $arrecordcontroller->ajax_previous();
                else if ($action == 'check_recaptcha')
                    $arrecordcontroller->ajax_check_recaptcha();
                else if ($action == 'checkinbuiltcaptcha')
                    $arrecordcontroller->ajax_check_spam_filter();
                
                else if ($action == 'update')
                    $arrecordcontroller->ajax_update();


                else if ($action == 'destroy')
                    $arrecordcontroller->ajax_destroy();
            }
        }else if ($controller == 'settingspreview') {


            global $style_settings, $arfsettings;


            if (!is_admin())
                $use_saved = true;

            if (isset($_REQUEST['arfmfws'])) {
                $arfssl = (is_ssl()) ? 1 : 0;
                $css_class = '';
                if( isset($_REQUEST['arfinpst']) && $_REQUEST['arfinpst'] == 'material'){
                    $css_class = ' .arf_materialize_form ';
                    include(FORMPATH . '/core/css_create_materialize.php');
                } else {
                    $css_class = '';
                    include(FORMPATH . '/core/css_create_main.php');
                }

                include(FORMPATH . '/core/css_create_common.php');
                if( is_rtl() ){
                    include(FORMPATH . '/core/css_create_rtl.php');
                }

                global $arfform, $wpdb, $arrecordhelper, $arfieldhelper, $arformcontrollerm, $arformcontroller;
                $arfformid = $_REQUEST['arfformid'];
                $form = $arfform->getOne((int) $arfformid);

                $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);
                $values = $arrecordhelper->setup_new_vars($fields, $form);

                echo stripslashes_deep(get_option('arf_global_css'));
                $form->options['arf_form_other_css'] = $arformcontroller->br2nl($form->options['arf_form_other_css']);
                echo $armainhelper->esc_textarea($form->options['arf_form_other_css']);

                $custom_css_array_form = array(
                    'arf_form_outer_wrapper' => '.arf_form_outer_wrapper|.arfmodal',
                    'arf_form_inner_wrapper' => '.arf_fieldset|.arfmodal',
                    'arf_form_title' => '.formtitle_style',
                    'arf_form_description' => 'div.formdescription_style',
                    'arf_form_element_wrapper' => '.arfformfield',
                    'arf_form_element_label' => 'label.arf_main_label',
                    'arf_form_elements' => '.controls',
                    'arf_submit_outer_wrapper' => 'div.arfsubmitbutton',
                    'arf_form_submit_button' => '.arfsubmitbutton button.arf_submit_btn',
                    'arf_form_next_button' => 'div.arfsubmitbutton .next_btn',
                    'arf_form_previous_button' => 'div.arfsubmitbutton .previous_btn',
                    'arf_form_success_message' => '#arf_message_success',
                    'arf_form_error_message' => '.control-group.arf_error .help-block|.control-group.arf_warning .help-block|.control-group.arf_warning .help-inline|.control-group.arf_warning .control-label|.control-group.arf_error .popover|.control-group.arf_warning .popover',
                    'arf_form_page_break' => '.page_break_nav',
                );

                foreach ($custom_css_array_form as $custom_css_block_form => $custom_css_classes_form) {


                    if (isset($form->options[$custom_css_block_form]) and $form->options[$custom_css_block_form] != '') {

                        $form->options[$custom_css_block_form] = $arformcontroller->br2nl($form->options[$custom_css_block_form]);

                        if ($custom_css_block_form == 'arf_form_outer_wrapper') {
                            $arf_form_outer_wrapper_array = explode('|', $custom_css_classes_form);

                            foreach ($arf_form_outer_wrapper_array as $arf_form_outer_wrapper1) {
                                if ($arf_form_outer_wrapper1 == '.arf_form_outer_wrapper')
                                    echo '.ar_main_div_' . $form->id . $css_class . '.arf_form_outer_wrapper { ' . $form->options[$custom_css_block_form] . ' } ';
                                if ($arf_form_outer_wrapper1 == '.arfmodal')
                                    echo '#popup-form-' . $form->id . $css_class. '.arfmodal{ ' . $form->options[$custom_css_block_form] . ' } ';
                            }
                        }
                        else if ($custom_css_block_form == 'arf_form_inner_wrapper') {
                            $arf_form_inner_wrapper_array = explode('|', $custom_css_classes_form);
                            foreach ($arf_form_inner_wrapper_array as $arf_form_inner_wrapper1) {
                                if ($arf_form_inner_wrapper1 == '.arf_fieldset')
                                    echo '.ar_main_div_' . $form->id . $css_class. ' ' . $arf_form_inner_wrapper1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                                if ($arf_form_inner_wrapper1 == '.arfmodal')
                                    echo '.arfmodal .arfmodal-body .ar_main_div_' . $form->id . $css_class . ' .arf_fieldset { ' . $form->options[$custom_css_block_form] . ' } ';
                            }
                        }
                        else if ($custom_css_block_form == 'arf_form_error_message') {
                            $arf_form_error_message_array = explode('|', $custom_css_classes_form);

                            foreach ($arf_form_error_message_array as $arf_form_error_message1) {
                                echo '.ar_main_div_' . $form->id . $css_class . ' ' . $arf_form_error_message1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                            }
                        } else {
                            echo '.ar_main_div_' . $form->id . $css_class . ' ' . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                        }
                    }
                }

                foreach ($values['fields'] as $field) {

                    $field['id'] = $arfieldhelper->get_actual_id($field['id']);

                    if (isset($field['field_width']) and $field['field_width'] != '') {
                        echo ' .ar_main_div_' . $form->id . $css_class . ' #arf_field_' . $field['id'] . '_container .help-block { width: ' . $field['field_width'] . 'px; } ';
                    }

                    if ($field['type'] == 'divider') {

                        if ($newarr['arfsectiontitlefamily'] != "Arial" && $newarr['arfsectiontitlefamily'] != "Helvetica" && $newarr['arfsectiontitlefamily'] != "sans-serif" && $newarr['arfsectiontitlefamily'] != "Lucida Grande" && $newarr['arfsectiontitlefamily'] != "Lucida Sans Unicode" && $newarr['arfsectiontitlefamily'] != "Tahoma" && $newarr['arfsectiontitlefamily'] != "Times New Roman" && $newarr['arfsectiontitlefamily'] != "Courier New" && $newarr['arfsectiontitlefamily'] != "Verdana" && $newarr['arfsectiontitlefamily'] != "Geneva" && $newarr['arfsectiontitlefamily'] != "Courier" && $newarr['arfsectiontitlefamily'] != "Monospace" && $newarr['arfsectiontitlefamily'] != "Times"  && $newarr['arfsectiontitlefamily'] != "" && $newarr['arfsectiontitlefamily'] != "inherit" ) {
                            if (is_ssl())
                             $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
                            else
                             $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
                            echo "@import url(" . $googlefontbaseurl . urlencode($newarr['arfsectiontitlefamily']) . ");";
                        }

                        if ($newarr['arfsectiontitleweightsetting'] == 'italic') {
                            $arf_heading_font_style = ' font-weight:normal; font-style:italic; ';
                        } else {
                            $arf_heading_font_style = ' font-weight:' . $field['arfsectiontitleweightsetting'] . '; font-style:normal; ';
                        }

                        
                    }

                    $custom_css_array = array(
                        'css_outer_wrapper' => '.arf_form_outer_wrapper',
                        'css_label' => '.css_label',
                        'css_input_element' => '.css_input_element',
                        'css_description' => '.arf_field_description',
                    );

                    foreach ($custom_css_array as $custom_css_block => $custom_css_classes) {

                        if (isset($field[$custom_css_block]) and $field[$custom_css_block] != '') {

                            $field[$custom_css_block] = $arformcontroller->br2nl($field[$custom_css_block]);

                            if ($custom_css_block == 'css_outer_wrapper' and $field['type'] != 'divider') {
                                echo ' .ar_main_div_' . $form->id . $css_class . ' #arf_field_' . $field['id'] . '_container { ' . $field[$custom_css_block] . ' } ';
                            } else if ($custom_css_block == 'css_outer_wrapper' and $field['type'] == 'divider') {
                                echo ' .ar_main_div_' . $form->id . $css_class . ' #heading_' . $field['id'] . ' { ' . $field[$custom_css_block] . ' } ';
                            } else if ($custom_css_block == 'css_label' and $field['type'] != 'divider') {
                                echo ' .ar_main_div_' . $form->id . $css_class . ' #arf_field_' . $field['id'] . '_container label.arf_main_label { ' . $field[$custom_css_block] . ' } ';
                            } else if ($custom_css_block == 'css_label' and $field['type'] == 'divider') {
                                echo ' .ar_main_div_' . $form->id . ' #heading_' . $field['id'] . ' h2.arf_sec_heading_field { ' . $field[$custom_css_block] . ' } ';
                            } else if ($custom_css_block == 'css_input_element') {

                                if ($field['type'] == 'textarea') {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls textarea { ' . $field[$custom_css_block] . ' } ';
                                } else if ($field['type'] == 'select' || $field['type'] == 'arf_multiselect' || $field['type'] == ARF_AUTOCOMPLETE_SLUG) {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls select { ' . $field[$custom_css_block] . ' } ';
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls .arfbtn.dropdown-toggle { ' . $field[$custom_css_block] . ' } ';
                                } else if ($field['type'] == 'radio') {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .arf_radiobutton label { ' . $field[$custom_css_block] . ' } ';
                                } else if ($field['type'] == 'checkbox') {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .arf_checkbox_style label { ' . $field[$custom_css_block] . ' } ';
                                } else if ($field['type'] == 'file') {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls .arfajax-file-upload { ' . $field[$custom_css_block] . ' } ';
                                } else if ($field['type'] == 'colorpicker') {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls .arfcolorpickerfield { ' . $field[$custom_css_block] . ' } ';
                                } else {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls input { ' . $field[$custom_css_block] . ' } ';
                                    if ($field['type'] == 'email') {
                                        echo '.ar_main_div_' . $form->id . $css_class . ' #arf_field_' . $field['id'] . '_container + .confirm_email_container .controls input {' . $field[$custom_css_block] . '}';
                                    }
                                    if ($field['type'] == 'password') {
                                        echo '.ar_main_div_' . $form->id . $css_class . ' #arf_field_' . $field['id'] . '_container + .confirm_password_container .controls input{ ' . $field[$custom_css_block] . '}';
                                    }
                                }
                            } else if ($custom_css_block == 'css_description' and $field['type'] != 'divider') {
                                echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .arf_field_description { ' . $field[$custom_css_block] . ' } ';
                            } else if ($custom_css_block == 'css_description' and $field['type'] == 'divider') {
                                echo ' .ar_main_div_' . $form->id . $css_class . '  #heading_' . $field['id'] . ' .arf_heading_description { ' . $field[$custom_css_block] . ' } ';
                            }
                        }
                    }

                    
                }
            } else
                return false;
        }
    }

    function menu() {

        global $arfsettings, $armainhelper;

        function get_free_menu_position($start, $increment = 0.1) {
            foreach ($GLOBALS['menu'] as $key => $menu) {
                $menus_positions[] = $key;
            }

            if (!in_array($start, $menus_positions)) {
                return $start;
            } else {
                $start += $increment;
            }

	    while (in_array($start, $menus_positions)) {
                $start += $increment;
            }
            return $start;
        }

        $place = get_free_menu_position(26.1, .1);

        if (current_user_can('arfviewforms')) {


            global $arformcontroller;

            add_menu_page('ARForms', 'ARForms', 'arfviewforms', 'ARForms', array($arformcontroller, 'route'), ARFIMAGESURL . '/main-icon-small2n.png', (string) $place);
        } elseif (current_user_can('arfviewentries')) {


            global $arrecordcontroller;


            add_menu_page('ARForms', 'ARForms', 'arfviewentries', 'ARForms', array($arrecordcontroller, 'route'), ARFIMAGESURL . '/main-icon-small2n.png', (string) $place);
        }

        add_submenu_page('', '', '', 'administrator', 'ARForms-settings1', array($this, 'list_entries'));
    }

    function menu_css() {
        ?>


        <style type="text/css">
            #adminmenu .toplevel_page_ARForms div.wp-menu-image img{  padding: 5px 0 0 2px; }

        </style>    


        <?php

    }

    function get_form_nav($id, $show_nav, $values, $record, $template_id = 0, $is_ref_form = 0) {


        global $pagenow, $armainhelper;

        if( empty( $show_nav ) ){
            $show_nav = false;
        }

        $show_nav = $armainhelper->get_param('show_nav', $show_nav);

        if ($show_nav){
            include(VIEWS_PATH . '/formmenu.php');
        }
    }

    function settings_link($links, $file) {

        $settings = '<a href="' . admin_url('admin.php?page=ARForms-settings') . '">' . addslashes(esc_html__('Settings', 'ARForms')) . '</a>';

        array_unshift($links, $settings);

        return $links;
    }

    function admin_js() {


        global $arfversion, $pagenow, $maincontroller, $wp_version;

        $jquery_handler = 'jquery';
        $jquery_ui_handler = 'jquery-ui-core';
        $jq_draggable_handler = 'jquery-ui-draggable';

        if( version_compare( $wp_version, '5.0', '<' ) ){
            wp_register_script( 'wp-hooks', ARFURL . '/js/hooks.js', array( $jquery_handler ), $arfversion );
        }

        if (isset($_GET) and ( isset($_GET['page']) and preg_match('/ARForms*/', $_GET['page']) and !preg_match('/ARForms-Lite*/', $_GET['page']) ) or ( $pagenow == 'edit.php' and isset($_GET) and isset($_GET['post_type']) and $_GET['post_type'] == 'frm_display')) {

            add_filter('admin_body_class', array($this, 'admin_body_class'));

            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');

            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-resizable');
            wp_enqueue_script('admin-widgets');


            wp_enqueue_style('widgets');

            wp_enqueue_script( 'wp-hooks' );
            if ( $_REQUEST['page'] != "ARForms-addons") {
                wp_enqueue_script( 'arf_selectpicker', ARFURL . '/js/arf_selectpicker.js', array( $jquery_handler ), $arfversion );
               wp_enqueue_style( 'arf_selectpicker', ARFURL . '/css/arf_selectpicker.css', array(), $arfversion );
            }

            wp_enqueue_script('arforms_admin', ARFURL . '/js/arforms_admin.js', array($jquery_handler, $jq_draggable_handler), $arfversion);

            if (is_rtl()) {
                wp_enqueue_style('arforms-admin-rtl', ARFURL . '/css/arforms-rtl.css', array(), $arfversion);
            }
            wp_enqueue_style( 'arforms_v3.0', ARFURL . '/css/arforms_v3.0.css', array(), $arfversion);
            if( !empty( $_REQUEST['arf_error_flag'] ) ){
                $arf_notice_inlin_style = ".addon_notice_wrapper{background:red;color:#fff;}.addon_notice_wrapper p{font-size:20px;}.addon_notice_wrapper .notice-dismiss::before{color:#fff}";
                wp_add_inline_style( 'arforms_v3.0', $arf_notice_inlin_style );
            }

            /* NEW CSS FOR ALL MEDIA QUERY */ 
            wp_register_style('arf-media-css', ARFURL . '/css/arf_media_css.css', array(), $arfversion);
            wp_enqueue_style('arf-media-css');
            
            if (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == 'ARForms' && isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] != '') {
                wp_enqueue_script('arfjquery-json', ARFURL . '/js/jquery/jquery.json-2.4.js', array($jquery_handler), $arfversion);
            }

            if ($GLOBALS['wp_version'] >= '3.8' and version_compare($GLOBALS['wp_version'], '3.9', '<')) {

                wp_enqueue_style('arforms-admin-3.8', ARFURL . '/css/arf_plugin_3.8.css', array(), $arfversion);
            }

            if ($GLOBALS['wp_version'] >= '3.9' and version_compare($GLOBALS['wp_version'], '3.10', '<')) {

                wp_enqueue_style('arforms-admin-3.9', ARFURL . '/css/arf_plugin_3.9.css', array(), $arfversion);
            }

            if ($GLOBALS['wp_version'] >= '4.0') {

                wp_enqueue_style('arforms-admin-4.0', ARFURL . '/css/arf_plugin_4.0.css', array(), $arfversion);
            }
        } else if ($pagenow == 'post.php' or ( $pagenow == 'post-new.php' and isset($_REQUEST['post_type']) and $_REQUEST['post_type'] == 'frm_display')) {


            if (isset($_REQUEST['post_type'])) {


                $post_type = $_REQUEST['post_type'];
            } else if (isset($_REQUEST['post']) and ! empty($_REQUEST['post'])) {


                $post = get_post($_REQUEST['post']);


                if (!$post)
                    return;


                $post_type = $post->post_type;
            }else {


                return;
            }

            if ($post_type == 'frm_display') {

                wp_enqueue_script('jquery-ui-draggable');

                wp_enqueue_script( 'wp-hooks' );
                wp_enqueue_script( 'arf_selectpicker', ARFURL . '/js/arf_selectpicker.js', array( $jquery_handler ), $arfversion );

                wp_enqueue_script('nouislider', ARFURL . '/js/nouislider.js', array($jquery_handler), $arfversion, true);
                

                wp_enqueue_script('arforms_admin', ARFURL . '/js/arforms_admin.js', array($jquery_handler, $jq_draggable_handler), $arfversion);

                wp_enqueue_style('arforms_v3.0', ARFURL . '/css/arforms_v3.0.css', array(), $arfversion);

                wp_enqueue_style('arf_animate_main', ARFURL . '/css/animate.css', array(), $arfversion);
		
                wp_enqueue_style( 'arf_selectpicker', ARFURL . '/css/arf_selectpicker.css', array(), $arfversion );

                /* NEW CSS FOR ALL MEDIA QUERY */ 
                wp_register_style('arf-media-css', ARFURL . '/css/arf_media_css.css', array(), $arfversion);
                wp_enqueue_style('arf-media-css');

                if ($GLOBALS['wp_version'] >= '3.8' and version_compare($GLOBALS['wp_version'], '3.9', '<')) {

                    wp_enqueue_style('arforms-admin-3.8', ARFURL . '/css/arf_plugin_3.8.css', array(), $arfversion);
                }
            }
        }
    }

    function admin_body_class($classes) {


        global $wp_version;


        if (version_compare($wp_version, '3.4.9', '>'))
            $classes .= ' arf35trigger';

        return $classes;
    }

    function front_head($ispost = '') {

        
        global $arfsettings, $arfversion, $arfdbversion, $maincontroller, $arformcontroller, $wp_version;

        if (!is_admin()) {
            wp_enqueue_script('jquery');
            wp_register_style('nouislider', ARFURL . '/css/nouislider.css', array(), $arfversion);
            wp_register_script('jquery-validation', ARFURL . '/bootstrap/js/jqBootstrapValidation.js', array('jquery'), $arfversion);
            wp_register_script('nouislider', ARFURL . '/js/nouislider.js', array(), $arfversion, true);
            
            wp_register_script('arfpagebreaktimer', ARFURL . '/js/arf_timer.js', array(), $arfversion, true);
           
            wp_register_style('arfdisplaycss', ARFURL . '/css/arf_front.css', array(), $arfversion);
            wp_register_style('arf_animate_css_front', ARFURL . '/css/animate.css', array(), $arfversion);

            
            wp_register_style('arfdisplayflagiconcss', ARFURL . '/css/flag_icon.css', array(), $arfversion);

            wp_register_style('arf-filedrag', ARFURL . '/css/arf_filedrag.css', array(), $arfversion);
            
            wp_register_script('arf-modal-js', ARFURL . '/js/arf_modal_js.js', array('jquery'), $arfversion);

            wp_register_script('arf-conditional-logic-js', ARFURL . '/js/arf_conditional_logic.js', array('jquery'), $arfversion);

            wp_register_style('arfbootstrap-datepicker-css', ARFURL . '/bootstrap/css/bootstrap-datetimepicker.css', array(), $arfversion);

            wp_register_script('jquery-maskedinput', ARFURL . '/js/inputmask.min.js', array('jquery'), $arfversion, true);


            wp_register_script('arf_js_color',ARFURL.'/js/jscolor.js',array('jquery'), $arfversion);

            wp_register_script('arf-colorpicker-basic-js', ARFURL . '/js/jquery.simple-color-picker.js', array(), $arfversion);

            wp_register_style('arf-fontawesome-css', ARFURL . '/css/font-awesome.min.css', array(), $arfversion);

            wp_register_script('arf_tipso_js_front', ARFURL . '/js/tipso.min.js', array(), $arfversion);

            wp_register_style('arf_tipso_css_front', ARFURL . '/css/tipso.min.css', array(), $arfversion);

            wp_register_script('animate-numbers', ARFURL . '/js/jquery.animateNumber.js', array(), $arfversion);

            wp_register_script('filedrag', ARFURL . '/js/filedrag/filedrag_front.js', array(), $arfversion);
            wp_register_script('bootstrap-typeahead-js', ARFURL . '/bootstrap/js/bootstrap-typeahead.js', array(), $arfversion);
            wp_register_script( 'arf_selectpicker', ARFURL . '/js/arf_selectpicker.js', array( 'jquery' ), $arfversion );
            wp_register_script('arf_trumbowyg-js', ARFURL . '/js/trumbowyg.min.js', array(), $arfversion);
            wp_register_style('arf_trumbowyg-css', ARFURL . '/css/trumbowyg.min.css', array(), $arfversion);
            wp_register_style( 'arf_selectpicker', ARFURL . '/css/arf_selectpicker.css', array(), $arfversion );
        } else {
            wp_enqueue_script('jquery');
        }

        $path = $_SERVER['REQUEST_URI'];
        $file_path = basename($path);

        if (!strstr($file_path, "post.php")) {

            wp_register_script('jquery-maskedinput', ARFURL . '/js/inputmask.min.js', array('jquery'), $arfversion, true);


            wp_register_script('arforms_phone_intl_input', ARFURL . '/js/intlTelInput.min.js', array(), $arfversion, true);
            wp_register_script('arforms_phone_utils', ARFURL . '/js/arf_phone_utils.js', array(), $arfversion, true);
            if( version_compare($wp_version, '5.0', '<') ){
                wp_register_script( 'wp-hooks', ARFURL . '/js/hooks.js', array( 'jquery' ), $arfversion.'_'.rand(1,5), true );
            }
            wp_register_script('arforms', ARFURL . '/js/arforms.js', array('jquery'), $arfversion.'_'.rand(1,5), true);
        }

        if ($ispost = '1' && !is_admin()) {
            global $post;
            $post_content = isset($post->post_content) ? $post->post_content : '';
            $parts = explode("[ARForms", $post_content);
            if (isset($parts[1])) {
                $myidpart = explode("id=", $parts[1]);
                $myid = isset($myidpart[1]) ? explode("]", $myidpart[1]) : array() ;
                if (isset($myid[0]) && $myid[0] > 0) {
                    
                }
            }
        }

        if (!is_admin() and isset($arfsettings->load_style) and $arfsettings->load_style == 'all') {


            $css = apply_filters('getarfstylesheet', ARFURL . '/css/arf_front.css', 'header');


            if (is_array($css)) {


                foreach ($css as $css_key => $file)
                    wp_enqueue_style('arf-forms' . $css_key, $file, array(), $arfversion);


                unset($css_key);


                unset($file);
            } else
                wp_enqueue_style('arf-forms', $css, array(), $arfversion);


            unset($css);





            global $arfcssloaded;


            $arfcssloaded = true;
        }
    }

    function footer_js($location = 'footer') {
        global $arfloadcss, $arfsettings, $arfversion, $arfcssloaded, $arfforms_loaded, $armainhelper,$forms_in_menu,$wpdb,$arformcontroller,$MdlDb, $arf_jscss_version;
    
        /* Direct Nav Menu */
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
    

        if(!is_null($forms_in_menu) && count($forms_in_menu) > 0){

            foreach($forms_in_menu as $formid){
    	    
        	    if (is_ssl()) {
                    $fid = str_replace("http://", "https://", $upload_main_url . '/maincss_' . $formid . '.css');
        	    } else {
                    $fid = $upload_main_url . '/maincss_' . $formid . '.css';
        	    }
        	    
        	    if (is_ssl()) {
                    $fid_material = str_replace("http://", "https://", $upload_main_url . '/maincss_materialize_' . $formid . '.css');
        	    } else {
                    $fid_material = $upload_main_url . '/maincss_materialize_' . $formid . '.css';
        	    }

                if( is_ssl() ){
                    $fid_material_outlined = str_replace( "http://", "https://", $upload_main_url . '/maincss_materialize_outlined_' . $formid . '.css' );
                } else {
                    $fid_material_outlined = $upload_main_url . '/maincss_materialize_outlined_' . $formid . '.css';
                }

                $res = $wpdb->get_row($wpdb->prepare("SELECT is_template,status,form_css FROM " . $MdlDb->forms . " WHERE id = %d", $formid), 'ARRAY_A');

        	    if (isset($res['is_template']) && isset($res['status']) && $res['is_template'] == '0' && $res['status'] == 'published') {
                    /* arf_dev_flag below function contain query */
                    $func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($formid), $formid);

                    $form_css = maybe_unserialize($res['form_css']);
                    if ($func_val == '') {
                        if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] != 'material' && $form_css['arfinputstyle'] != 'material_outlined' ) {
                            wp_enqueue_style('arfformscss_' . $formid, $fid, array(), $arf_jscss_version);
                        }

                        if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] == 'material') {
                            wp_enqueue_style('arfformscss_materialize_' . $formid, $fid_material, array(), $arf_jscss_version);
            		    }

                        if( isset($form_css['arfinputstyle'] ) && $form_css['arfinputstyle'] == 'material_outlined' ){
                            wp_enqueue_style( 'arfformscss_materialize_outlined_' . $formid, $fid_material_outlined, array(), $arf_jscss_version );
                        }

            		    wp_enqueue_style('arfdisplaycss');
                        wp_enqueue_style('arf_animate_css_front');
                        wp_enqueue_style('arfdisplayflagiconcss');
            		} else {
                        wp_enqueue_style('arfdisplaycss');
                        wp_enqueue_style('arf_animate_css_front');
                        wp_enqueue_style('arfdisplayflagiconcss');
            		    if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] != 'material' && $form_css['arfinputstyle'] != 'material_outlined' ) {
                            wp_enqueue_style('arfformscss_' . $formid, $fid, array(), $arf_jscss_version);
                	    }
                        if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] == 'material') {
                            wp_enqueue_style('arfformscss_materialize_' . $formid, $fid_material, array(), $arf_jscss_version);
                	    }
                        if( isset($form_css['arfinputstyle'] ) && $form_css['arfinputstyle'] == 'material_outlined' ){
                            wp_enqueue_style( 'arfformscss_materialize_outlined_' . $formid, $fid_material_outlined, array(), $arf_jscss_version );
                        }
            		}
        	    }
            }
    	}
    	/* Direct Nav over */
	
        if ($arfloadcss and ! is_admin() and ( $arfsettings->load_style != 'none')) {
            if ($arfcssloaded) {
                $css = apply_filters('getarfstylesheet', '', $location);
            } else {
                $css = apply_filters('getarfstylesheet', ARFURL . '/css/arf_front.css', $location);
            }

            if (!empty($css)) {
                echo "\n" . '<script type="text/javascript" data-cfasync="false">';
                if (is_array($css)) {
                    foreach ($css as $css_key => $file) {
                        echo 'jQuery("head").append(unescape("%3Clink rel=\'stylesheet\' id=\'arf-forms' . ($css_key + $arfcssloaded) . '-css\' href=\'' . $file . '\' type=\'text/css\' media=\'all\' /%3E"));';
                        unset($css_key);
                        unset($file);
                    }
                } else {
                    echo 'jQuery("head").append(unescape("%3Clink rel=\'stylesheet\' id=\'arfformscss\' href=\'' . $css . '\' type=\'text/css\' media=\'all\' /%3E"));';
                }
                unset($css);
                echo '</script>' . "\n";
            }
        }

        if (!is_admin() and $location != 'header' and ! empty($arfforms_loaded)) {
            $armainhelper->load_scripts(array('arforms','wp-hooks'));
        }
	
    }

    function wp_enqeue_footer_script() {
        
        global $fields_with_external_js, $bootstraped_fields_array, $wpdb, $MdlDb,$arfversion;

        if (is_admin() && isset($_REQUEST['page']) && $_REQUEST['page'] == 'ARForms' && isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] != '') {
            if (isset($fields_with_external_js) && is_array($fields_with_external_js) && !empty($fields_with_external_js)) {
                $matched_fields = array_intersect($fields_with_external_js, $bootstraped_fields_array);

                foreach ($matched_fields as $field_type) {
                    switch ($field_type) {
                        case 'select':
                            wp_register_script('arf_selectpicker', ARFURL . '/js/arf_selectpicker.js', array('jquery'), $arfversion);
                            wp_enqueue_script('arf_selectpicker');
                            wp_register_style('arf_selectpicker', ARFURL . '/css/arf_selectpicker.css', array(), $arfversion);
                            wp_enqueue_style('arf_selectpicker');
                            break;
                        case 'date':
                            break;
                        case 'time':
                            break;
                        case 'colorpicker':
                            $action = isset($_REQUEST['arfaction']) ? $_REQUEST['arfaction'] : '';
                            if ($action == 'edit') {
                                $form_id = $_REQUEST['id'];
                                $getcpfields = $wpdb->get_results($wpdb->prepare("SELECT field_options FROM `" . $MdlDb->fields . "` WHERE `type` = %s and `form_id` = %d", 'colorpicker', $form_id));
                                $load_simple_colorpicker = false;
                                if (!empty($getcpfields)) {
                                    foreach ($getcpfields as $key => $cpfieldoptions) {
                                        $field_options = json_decode($cpfieldoptions->field_options, true);
                                        if (json_last_error() != JSON_ERROR_NONE) {
                                            $field_options = maybe_unserialize($field_options);
                                        }
                                        $colorpicker_type = $field_options['colorpicker_type'];
                                        if ($colorpicker_type == 'basic') {
                                            $load_simple_colorpicker = true;
                                        }
                                    }
                                }
                                if( $load_simple_colorpicker == true ){
                                    wp_enqueue_script('arf-colorpicker-basic-js', ARFURL . '/js/jquery.simple-color-picker.js', array(), $arfversion);
                                }
                            }
                            break;
                        
                        default:
                            do_action('arf_load_bootstrap_js_from_outside', $field_type);
                            break;
                    }
                }
            }
        }
    }

    function front_head_js() {
        global $post, $wpdb, $arformcontroller, $arfversion, $arfform, $armainhelper, $arrecordhelper, $arfieldhelper, $form_type_with_id, $MdlDb,$func_val, $arf_jscss_version;
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';

        if( !isset($form_type_with_id) || $form_type_with_id == '' ){
            $form_type_with_id = array();
        }

        global $arfsettings,$arfversion;
        if (!isset($arfsettings)) {
            $arfsettings_new = get_option('arf_options');
        } else {
            $arfsettings_new = $arfsettings;
        }

        $post_content = isset($post->post_content) ? $post->post_content : '';
        $parts = explode("[ARForms", $post_content);
        $parts[1] = isset($parts[1]) ? $parts[1] : '';
        $myidpart = ($parts[1]!='') ? explode("id=", $parts[1]) : array();
        $myidpart[1] = isset($myidpart[1]) ? $myidpart[1] : '';
        $myid = ($myidpart[1]!='') ? explode("]", $myidpart[1]) : '';

        if ( ! function_exists( 'is_plugin_active' ) ) {
            include ABSPATH . '/wp-admin/includes/plugin.php';
        }
        
        $is_lite_form = false;
        if (!is_admin()) {
            global $wp_query,$is_active_cornorstone;
            $posts = $wp_query->posts;            
            if($is_active_cornorstone){
                if( is_plugin_active( 'arforms-form-builder/arforms-form-builder.php' ) ){
                    $pattern = '\[(\[?)(ARFormslite|cs_arformslite_cs|ARForms|ARForms_popup|cs_arforms_cs)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                } else {
                    $pattern = '\[(\[?)(ARForms|ARForms_popup|cs_arforms_cs)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                }
            } else {
                if( is_plugin_active( 'arforms-form-builder/arforms-form-builder.php' ) ){
                    $is_lite_form = true;
                    $pattern = '\[(\[?)(ARFormslite|ARForms|ARForms_popup)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                } else {
                    $pattern = '\[(\[?)(ARForms|ARForms_popup)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                }
            }

            if (is_array($posts)) {
                foreach ($posts as $post) {
                    if (preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) && array_key_exists(2, $matches) && in_array('ARForms', $matches[2])) {
                        break;
                    }
                }

                $formids = array();
                $form_type_with_id = array();

                if (isset($matches)) {
                    if (is_array($matches) && count($matches) > 0) {
                        foreach ($matches as $k => $v) {
                            foreach ($v as $key => $val) {
                                $parts_cornerstone = 0;
                                if (strpos($val, 'id=') !== false) {
                                    $parts = explode("id=", $val);
                                } else if (strpos($val, 'arf_forms=') !== false) {

                                    $parts_cornerstone = explode("arf_forms=", $val);
                                }

                                if ($parts > 0 && isset($parts[1])) {

                                    if (stripos($parts[1], ']') !== false) {
                                        $partsnew = explode("]", $parts[1]);
                                        $formids[] = $partsnew[0];
                                    } else if (stripos($parts[1], ' ') !== false) {

                                        $partsnew = explode(" ", $parts[1]);
                                        $formids[] = $partsnew[0];
                                    } else {
                                        
                                    }
                                }
                                
                                if ($parts_cornerstone > 0 && isset($parts_cornerstone[1])) {
                                    if (!is_array($parts_cornerstone[1])) {

                                        $parts_cornerstone[1] = explode(' ', $parts_cornerstone[1]);
                                        $parts_cornerstone[1][0] = str_replace('"', '', $parts_cornerstone[1][0]);

                                        $formids[] = $parts_cornerstone[1][0];
                                    }
                                }


                                /* arf_dev_flag need improvement */
                                if (strpos($val, '[') !== false && strpos($val, ']') !== false) {
                                    $temp_value = shortcode_parse_atts($val);
                                    if (isset($temp_value[1])) {

                                        $temp_value[1] = explode('=', $temp_value[1]);
                                        if (isset($temp_value[1][1])) {
                                            $temp_value[1][1] = str_replace("'", '', $temp_value[1][1]);
                                            $temp_value[1][1] = str_replace('"', '', $temp_value[1][1]);
                                            $temp_value[1][1] = str_replace(']', '', $temp_value[1][1]);
                                            $temp_value[1][1] = str_replace('[', '', $temp_value[1][1]);
                                            $temp_value[$temp_value[1][0]] = $temp_value[1][1];
                                        }
                                    }

                                    if (isset($temp_value['id'])) {
                                        $form_type_with_id[] = $temp_value;
                                    } else if (isset($temp_value['arf_forms'])) {
                                        $temp_value['id'] = $temp_value['arf_forms'];
                                        $form_type_with_id[] = $temp_value;
                                    }
                                }
                            }
                        }
                    }
                }
            }



            $newvalarr = array();

            if (isset($formids) and is_array($formids) && count($formids) > 0) {
                foreach ($formids as $newkey => $newval) {
                    if (stripos($newval, ' ') !== false) {
                        $partsnew = explode(" ", $newval);
                        $newvalarr[] = $partsnew[0];
                    } else
                        $newvalarr[] = $newval;
                }
            }

            if (is_array($newvalarr) && count($newvalarr) > 0) {
                $newvalarr = array_unique($newvalarr);
                
                foreach ($newvalarr as $newkey => $newval) {
                    $pattern = '/(\d+)/';
                    preg_match_all($pattern,$newval,$matches);
                    $newval = $matches[0][0];
                    if( $is_lite_form ){
                        $get_pro_form_id = wp_cache_get('get_pro_form_id_'.$newval);
                        if( false == $get_pro_form_id ){
                            $get_pro_form_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `" . $MdlDb->forms . "` WHERE arf_is_lite_form = %d AND arf_lite_form_id = %d", 1, $newval ) );
                            wp_cache_set('get_pro_form_id_'.$newval, $get_pro_form_id);
                        }
                        if( $get_pro_form_id > 0 ){
                            $newval = $get_pro_form_id;
                        }
                    }
                    if (is_ssl()) {
                        $fid = str_replace("http://", "https://", $upload_main_url . '/maincss_' . $newval . '.css');
                    } else {
                        $fid = $upload_main_url . '/maincss_' . $newval . '.css';
                    }

                    if (is_ssl()) {
                        $fid_material = str_replace("http://", "https://", $upload_main_url . '/maincss_materialize_' . $newval . '.css');
                    } else {
                        $fid_material = $upload_main_url . '/maincss_materialize_' . $newval . '.css';
                    }

                    if( is_ssl() ){
                        $fid_material_outlined = str_replace( "http://", "https://", $upload_main_url . '/maincss_materialize_outlined_' . $newval . '.css' );
                    } else {
                        $fid_material_outlined = $upload_main_url . '/maincss_materialize_outlined_' . $newval . '.css';
                    }
                    
                    $res = wp_cache_get('arf_form_data_'.$newval);
                    if( false == $res ){
                        $res = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d", $newval), 'ARRAY_A');
                        wp_cache_set('arf_form_data_'.$newval, $res);
                    }


                    if (isset($res['is_template']) && isset($res['status']) && $res['is_template'] == '0' && $res['status'] == 'published') {
                        /* arf_dev_flag below function contain query */
                        $func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($newval), $newval);

                        $form_css = maybe_unserialize($res['form_css']);
                        if ($func_val == '') {
                            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] != 'material' && $form_css['arfinputstyle'] != 'material_outlined') {
                                wp_enqueue_style('arfformscss_' . $newval, $fid, array(), $arf_jscss_version);
                            }

                            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] == 'material') {
                                wp_enqueue_style('arfformscss_materialize_' . $newval, $fid_material, array(), $arf_jscss_version);
                            }

                            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] == 'material_outlined') {
                                wp_enqueue_style( 'arfformscss_materialize_outlined_' . $newval, $fid_material_outlined, array(), $arf_jscss_version );
                            }

                            wp_enqueue_style('arfdisplaycss');
                            wp_enqueue_style('arf_animate_css_front');

                        } else {
                            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] != 'material' && $form_css['arfinputstyle'] != 'material_outlined') {
                                wp_enqueue_style('arfformscss_' . $newval, $fid, array(), $arf_jscss_version);
                            }

                            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] == 'material') {
                                wp_enqueue_style('arfformscss_materialize_' . $newval, $fid_material, array(), $arf_jscss_version);
                            }

                            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] == 'material_outlined') {
                                wp_enqueue_style( 'arfformscss_materialize_outlined_' . $newval, $fid_material_outlined, array(), $arf_jscss_version );
                            }
                        }
                    }
                }
            }
            /* arf_dev_flag if form restricted with max_entries or date than  echo style or not?? */

            foreach ($form_type_with_id as $key => $value) {
                
                $define_cs_position = '';
                if(isset($value['arf_link_type']) == 'fly')
                {
                    $define_cs_position = (isset($value['arf_fly_position']) ? $value['arf_fly_position'] : '');
                }
                else
                {
                    $define_cs_position = (isset($value['arf_link_position']) ? $value['arf_link_position'] : '');
                }
                $value['type'] = isset($value['type']) ? $value['type'] : (isset($value['arf_link_type']) ? $value['arf_link_type'] : '');
                $value['position'] = isset($value['position']) ? $value['position'] : (isset($define_cs_position) ? $define_cs_position : '');
                $bgcolor = isset($value['bgcolor']) ? $value['bgcolor'] : (isset($value['arf_button_background_color']) ? $value['arf_button_background_color'] : '#8ccf7a');
                $txtcolor = isset($value['txtcolor']) ? $value['txtcolor'] : (isset($value['arf_button_text_color']) ? $value['arf_button_text_color'] : '#ffffff');
                $btn_angle = isset($value['angle']) ? $value['angle'] : (isset($value['arf_fly_button_angle']) ? $value['arf_fly_button_angle'] : '0');
                $modal_bgcolor = isset($value['modal_bgcolor']) ? $value['modal_bgcolor'] : (isset($value['arf_background_overlay_color']) ? $value['arf_background_overlay_color'] : '#000000');
                $overlay = isset($value['overlay']) ? $value['overlay'] : (isset($value['arf_background_overlay']) ? $value['arf_background_overlay'] : '0.6');

                $is_fullscreen_act = (isset($value['is_fullscreen']) && $value['is_fullscreen'] == 'yes') ? $value['is_fullscreen'] : 'no';
                 
                if( isset($value['arf_show_full_screen']) && $value['arf_show_full_screen'] == 'yes' ){
                    $is_fullscreen_act = 'yes';
                }
                

                $inactive_min      = isset($value['inactive_min']) ? $value['inactive_min'] : (isset($value['arf_inactive_min']) ? $value['arf_inactive_min'] : '0');

                $modaleffect       = isset($value['modaleffect']) ? $value['modaleffect'] : (isset($value['arf_modaleffect']) ? $value['arf_modaleffect'] : 'no_animation');
                
               
                $type = $value['type'];
                if(isset($value['arf_onclick_type']) && !empty($value['arf_onclick_type'])){
                    $type = $value['arf_onclick_type'];
                }
                
            }
        }
    }

    public static function arf_db_check() {
        global $MdlDb;
        $arf_db_version = get_option('arf_db_version');
        if (( $arf_db_version == '' || !isset($arf_db_version) ) && IS_WPMU)
            $MdlDb->upgrade($old_db_version);
    }

    public static function install($old_db_version = false) {

        global $MdlDb,$armainhelper;

        $arf_db_version = get_option('arf_db_version');
        if ($arf_db_version == '' || !isset($arf_db_version))
            $MdlDb->upgrade($old_db_version);
	

    	$args = array(
            'role' => 'administrator',
            'fields' => 'id'
        );
        $users = get_users($args);
        if( count($users) > 0 ){
            foreach($users as $key => $user_id ){
                global $current_user;
                $arfroles = $armainhelper->frm_capabilities();

                $userObj = new WP_User($user_id);
                foreach ($arfroles as $arfrole => $arfroledescription){
                    $userObj->add_cap($arfrole);
                }
                unset($arfrole);
                unset($arfroles);
                unset($arfroledescription);
            }
        }
    }

    function arf_start_session( $force = false ){

        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            if( ( function_exists('session_status') && session_status() == PHP_SESSION_NONE && !is_admin() ) || $force == true ) {
                @session_start(
                    array(
                        'read_and_close' => false
                    )
                );
            }
        } else if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            if ( ( function_exists('session_status') && session_status() == PHP_SESSION_NONE && !is_admin()  ) || $force == true ) {
                @session_start();
            }
        } else {
            if( ( session_id() == '' && !is_admin() )  || $force == true ) {
                @session_start();
            }
        }

    }

    function parse_standalone_request() {


        $plugin = $this->get_param('plugin');


        $action = isset($_REQUEST['arfaction']) ? 'arfaction' : 'action';


        $action = $this->get_param($action);


        $controller = $this->get_param('controller');

        if (!empty($plugin) and $plugin == 'ARForms' and ! empty($controller)) {


            $this->standalone_route($controller, $action);


            exit;
        }
    }

    function standalone_route($controller, $action = '') {

        global $arformcontroller;


        if ($controller == 'forms' and ! in_array($action, array('export', 'import')))
            $arformcontroller->preview($this->get_param('form'));
        else
            do_action('arfstandaloneroute', $controller, $action);
    }

    function get_param($param, $default = '') {


        return (isset($_POST[$param]) ? $_POST[$param] : (isset($_GET[$param]) ? $_GET[$param] : $default));
    }

    function get_form_shortcode($atts) {

        global $arfskipshortcode, $arrecordcontroller, $arfsettings, $arf_loaded_form_unique_id_array, $arformcontroller;

        wp_enqueue_style('arfdisplaycss');
        wp_enqueue_style('arf_animate_css_front');
        
        if ($arfskipshortcode) {


            $sc = '[ARForms';


            foreach ($atts as $k => $v)
                $sc .= ' ' . $k . '="' . $v . '"';


            return $sc . ']';
        }

        extract(shortcode_atts(array('id' => '', 'key' => '', 'title' => false, 'description' => false, 'readonly' => false, 'entry_id' => false, 'fields' => array()), $atts));


        do_action('ARForms_shortcode_atts', compact('id', 'key', 'title', 'description', 'readonly', 'entry_id', 'fields'));


        global $wpdb, $MdlDb;

        $res = wp_cache_get( 'arf_form_data_'.$id );
        if( false == $res ){
            $res = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d", $id), 'ARRAY_A');
            wp_cache_set('arf_form_data_'.$id, $res);
        }

        if( !empty( $res ) ){
            if( isset( $res['arf_update_form'] ) && 1 == $res['arf_update_form'] ){
                
                do_action( 'arf_rewrite_css_after_update', $id, $res['form_css'] );

                $wpdb->update(
                    $MdlDb->forms,
                    array(
                        'arf_update_form' => 0
                    ),
                    array(
                        'id' => $id
                    )  
                );
            }
        }

        $values = isset($res['options']) ? maybe_unserialize($res['options'])  : '' ;


        if (isset($values['display_title_form']) and $values['display_title_form'] == '0') {
            $title = false;
            $description = false;
        } else {
            $title = true;
            $description = true;
        }

        $arf_data_uniq_id = '';
        if (isset($arf_loaded_form_unique_id_array[$id]['normal'][0])) {
            $arf_data_uniq_id = current($arf_loaded_form_unique_id_array[$id]['normal']);
            if (is_array($arf_loaded_form_unique_id_array[$id]['normal'])) {
                array_shift($arf_loaded_form_unique_id_array[$id]['normal']);
            } else {
                unset($arf_loaded_form_unique_id_array[$id]['normal']);
            }
        } else {
            $arf_data_uniq_id = rand(1, 99999);
            if (empty($arf_data_uniq_id) || $arf_data_uniq_id == '') {
                $arf_data_uniq_id = $id;
            }
        }

        if(isset($atts['arfsubmiterrormsg'])){
            $_REQUEST['arfsubmiterrormsg'] = $atts['arfsubmiterrormsg'];
        }

        require_once VIEWS_PATH . '/arf_front_form.php';
        $contents = ars_get_form_builder_string($id, $key, false, false, '', $arf_data_uniq_id);
        $contents = apply_filters('arf_pre_display_arfomrms', $contents, $id, $key);

        return $contents;
    }

    function get_form_shortcode_popup($atts) {

        global $arfskipshortcode, $arrecordcontroller, $arfsettings, $arf_loaded_form_unique_id_array,$arf_popup_forms;

        wp_enqueue_style('arfdisplaycss');
        wp_enqueue_style('arf_animate_css_front');
        if ($arfskipshortcode) {


            $sc = '[ARForms_popup';


            foreach ($atts as $k => $v)
                $sc .= ' ' . $k . '="' . $v . '"';


            return $sc . ']';
        }


        extract(shortcode_atts(array('id' => '', 'key' => '', 'title' => false, 'description' => false, 'readonly' => false, 'entry_id' => false, 'fields' => array(), 'desc' => 'Click here to open Form', 'shortcode_type' => '','preset_data' => ''), $atts));

        do_action('ARForms_popup_shortcode_atts', compact('id', 'key', 'title', 'description', 'readonly', 'entry_id', 'fields', 'desc', 'shortcode_type','preset_data'));

        $arf_skip_modal_html = false;

        if( !empty( $atts['type'] ) && !in_array($atts['type'], array( 'sticky', 'fly' ) ) ){
            if( !empty( $arf_popup_forms ) && in_array( $id, $arf_popup_forms ) ){
                $arf_skip_modal_html = true;
            }
        }

        global $wpdb, $MdlDb,$arfform;

        $res = wp_cache_get( 'arf_form_options_'.$id );
        
        if( false == $res ){
            $res = $arfform->arf_select_db_data( true, '', $MdlDb->forms, 'options', 'WHERE id = %d', array( $id ), '', '', '', false, true );
            wp_cache_set( 'arf_form_options_'.$id, $res );
        }

        if( !empty( $res ) ){
            if( isset( $res->arf_update_form ) && 1 == $res->arf_update_form ){
                
                do_action( 'arf_rewrite_css_after_update', $id, $res->form_css );

                $wpdb->update(
                    $MdlDb->forms,
                    array(
                        'arf_update_form' => 0
                    ),
                    array(
                        'id' => $id
                    )  
                );
            }
        }

        $values = isset($res->options) ? maybe_unserialize($res->options, true) : '';

        if (isset($values['display_title_form']) and $values['display_title_form'] == '0') {
            $title = false;
            $description = false;
        } else {
            $title = true;
            $description = true;
        }

        $type = isset($atts['type']) ? $atts['type'] : 'link';
        $modal_height = isset($atts['height']) ? $atts['height'] : 'auto';
        $modal_width = isset($atts['width']) ? $atts['width'] : '800';
        $position = isset($atts['position']) ? $atts['position'] : 'top';
        $btn_angle = isset($atts['angle']) ? $atts['angle'] : '0';
        $bgcolor = isset($atts['bgcolor']) ? $atts['bgcolor'] : '#8ccf7a';
        $txtcolor = isset($atts['txtcolor']) ? $atts['txtcolor'] : '#ffffff';
        $arf_img_url = isset($atts['arf_img_url']) ? $atts['arf_img_url'] : '';
        $arf_img_height = isset($atts['arf_img_height']) ? $atts['arf_img_height'] : 'auto';
        $arf_img_width = isset($atts['arf_img_width']) ? $atts['arf_img_width'] : 'auto';
        $open_inactivity = isset($atts['on_inactivity']) ? $atts['on_inactivity'] : '1';
        $open_scroll = isset($atts['on_scroll']) ? $atts['on_scroll'] : '10';
        $open_delay = isset($atts['on_delay']) ? $atts['on_delay'] : '0';
        $overlay = isset($atts['overlay']) ? $atts['overlay'] : '0.6';
        $is_close_link = isset($atts['is_close_link']) ? $atts['is_close_link'] : 'yes';
        $modal_bgcolor = isset($atts['modal_bgcolor']) ? $atts['modal_bgcolor'] : '#000000';
        $is_fullscreen_act = isset($atts['is_fullscreen']) ? $atts['is_fullscreen'] : 'no';
        $inactive_min  = isset($atts['inactive_min']) ? $atts['inactive_min'] : '0';
        $modaleffect  = isset($atts['modaleffect']) ? $atts['modaleffect'] : 'no_animation';
        $arf_preset_data  = isset($atts['preset_data']) ? $atts['preset_data'] : '';
        $arf_hide_popup_for_loggedin_user  = isset($atts['hide_popup_for_loggedin_user']) ? $atts['hide_popup_for_loggedin_user'] : 'no';

        $is_site_wide = !empty( $atts['site_wide'] ) ? $atts['site_wide'] : 'no';

        if( 'yes' === $is_site_wide ){
            $arf_skip_modal_html = false;
        }
        
        $viewer_edit_id = !empty( $atts['edit_entry_id'] ) ? $atts['edit_entry_id']: '';

        $desc = isset($atts['desc']) ? $atts['desc'] : addslashes(esc_html__('Click here to open Form', 'ARForms'));

        $arf_data_uniq_id = '';

        if (isset($arf_loaded_form_unique_id_array[$id]['type'][$type][$position])) {
            $arf_data_uniq_id = current($arf_loaded_form_unique_id_array[$id]['type'][$type][$position]);
            if (is_array($arf_loaded_form_unique_id_array[$id]['type'][$type][$position])) {

                array_shift($arf_loaded_form_unique_id_array[$id]['type'][$type][$position]);
            } else {


                unset($arf_loaded_form_unique_id_array[$id]['type'][$type][$position]);
            }
        } else if (isset($arf_loaded_form_unique_id_array[$id]['type'][$type]) && !empty($arf_loaded_form_unique_id_array[$id]['type'][$type])) {

            $arf_data_uniq_id = current($arf_loaded_form_unique_id_array[$id]['type'][$type]);
            if (is_array($arf_loaded_form_unique_id_array[$id]['type'][$type])) {

                array_shift($arf_loaded_form_unique_id_array[$id]['type'][$type]);
            } else {


                unset($arf_loaded_form_unique_id_array[$id]['type'][$type]);
            }
        } else {
            $arf_data_uniq_id = rand(1, 99999);
            if (empty($arf_data_uniq_id) || $arf_data_uniq_id == '') {
                $arf_data_uniq_id = $id;
            }
        }
        /* arf_dev_flag - Cornerstone Check Once */
        if(is_array($arf_data_uniq_id))
        {
            $arf_data_uniq_id = $arf_data_uniq_id[0];            
        }
        else
        {
          $arf_data_uniq_id = $arf_data_uniq_id;   
        }

        if( 'no' === $is_site_wide ){
            if( empty( $arf_popup_forms ) || !in_array( $id, $arf_popup_forms ) ){
                $arf_popup_forms[ $arf_data_uniq_id ] = $id;
            }
        }

        $contents = "";
        require_once VIEWS_PATH . '/arf_front_form.php';
        
        $is_navigation = (isset($atts['is_navigation'])) ? $atts['is_navigation'] : false;
    	
        $is_display_popup = 1;
    	if('yes' == $arf_hide_popup_for_loggedin_user && is_user_logged_in()) { 
    		$is_display_popup = 0;
    	}

        if((isset($atts['shortcode_type']) && $atts['shortcode_type'] !='') || (isset($atts['type']) && $atts['type'] !='')) {
            if(1 == $is_display_popup) {
                $contents = ars_get_form_builder_string($id, $key, false, false, '', $arf_data_uniq_id, $desc, $type, $modal_height, $modal_width, $position, $btn_angle, $bgcolor, $txtcolor, $open_inactivity, $open_scroll, $open_delay, $overlay, $is_close_link, $modal_bgcolor,$is_fullscreen_act,$inactive_min,$modaleffect,$is_navigation,$arf_preset_data,$arf_hide_popup_for_loggedin_user,$arf_skip_modal_html,$viewer_edit_id, $arf_img_url, $arf_img_height, $arf_img_width,$is_site_wide);
            }
           
        } else {
            if(1 == $is_display_popup) {
                $contents = ars_get_form_builder_string($id, $key, false, false, '', $arf_data_uniq_id,'','','','','','','','','','','','','','','','','',false,$arf_preset_data,$arf_hide_popup_for_loggedin_user,$arf_skip_modal_html,$viewer_edit_id, $arf_img_url, $arf_img_height, $arf_img_width,$is_site_wide);
            }
        }
        $contents = apply_filters('arf_pre_display_arfomrms', $contents, $id, $key);

        return $contents;

        
    }

    function widget_text_filter($content) {


        $regex = '/\[\s*ARForms\s+.*\]/';


        return preg_replace_callback($regex, array($this, 'widget_text_filter_callback'), $content);
    }

    function widget_text_filter_callback($matches) {

        if ($matches[0]) {
            $parts = explode("id=", $matches[0]);
            $partsnew = explode(" ", $parts[1]);
            $formid = $partsnew[0];
            $formid = str_replace(']', '', $formid);
            $formid = trim($formid);
            global $arforms_loaded;
            $arforms_loaded[$formid] = true;
        }

        return do_shortcode($matches[0]);
    }

    function widget_text_filter_popup($content) {


        $regex = '/\[\s*ARForms_popup\s+.*\]/';


        return preg_replace_callback($regex, array($this, 'widget_text_filter_callback_popup'), $content);
    }

    function widget_text_filter_callback_popup($matches) {

        if ($matches[0]) {
            $parts = explode("id=", $matches[0]);
            $partsnew = explode(" ", $parts[1]);
            $formid = $partsnew[0];
            $formid = trim($formid);
            global $arforms_loaded;
            $arforms_loaded[$formid] = true;
        }

        return do_shortcode($matches[0]);
    }

    function get_postbox_class() {

        return 'postbox-container';
    }

    function set_js( $hook ) {
        global $arfversion,$wp_version;
        $jquery_handler = 'jquery';
        $jq_draggable_handler = "jquery-ui-draggable";
        
        if( isset( $hook ) && 'plugins.php' == $hook ){
            global $wp_version;

            if ( version_compare( $wp_version, '4.5.0', '<' ) ) {
                deactivate_plugins( plugin_basename( 'arforms/arforms.php' ), true, false );
                $redirect_url = network_admin_url( 'plugins.php?deactivate=true' );
                wp_die( '<div class="arf_dig_sig_wp_notice"><p class="arf_dig_sig_wp_notice_text" >Please meet the minimum requirement of WordPress version 4.5 to activate ARForms<p class="arf_dig_sig_wp_notice_continue">Please <a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">Click Here</a> to continue.</p></div>' );
            }
        }


        if( !empty( $_GET['page'] ) && preg_match('/ARForms*/', $_GET['page']) ){
            wp_deregister_script('datatables');
            wp_dequeue_script( 'datatables' );

            wp_deregister_script('buttons-colvis');
            wp_dequeue_script( 'buttons-colvis' );

            wp_register_script( 'datatables', ARFURL . '/datatables/media/js/datatables.js', array(), $arfversion );
            wp_register_script( 'buttons-colvis', ARFURL . '/datatables/media/js/buttons.colVis.js', array(), $arfversion );
        }

        if ((isset($_REQUEST['page']) && $_REQUEST['page'] != '') && ($_REQUEST['page'] == "ARForms-entries" )) {

            wp_enqueue_script($jquery_handler);
            
            wp_enqueue_script('arfhighcharts', ARFURL . '/js/highcharts/arfhighcharts.js', array(), $arfversion);
            wp_enqueue_script('arfexporting', ARFURL . '/js/highcharts/arfexporting.js', array(), $arfversion);
            wp_enqueue_script('arfmap', ARFURL . '/js/highcharts/arfmap.js', array(), $arfversion);
            wp_enqueue_script('arfdata', ARFURL . '/js/highcharts/arfdata.js', array(), $arfversion);
            wp_enqueue_script('arfworld', ARFURL . '/js/highcharts/arfworld.js', array(), $arfversion);
            
            wp_enqueue_script( 'datatables' );
            wp_enqueue_script( 'buttons-colvis' );

            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arf_tipso');
        } elseif ((isset($_REQUEST['page']) && $_REQUEST['page'] != '') && ("ARForms-popups" == $_REQUEST['page'] )) {
            wp_enqueue_script($jquery_handler);
            wp_enqueue_script( 'datatables' );
            wp_enqueue_script( 'buttons-colvis' );

            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arf_tipso');

        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms-settings") {

            wp_enqueue_script('arfjquery-json', ARFURL . '/js/jquery/jquery.json-2.4.js', array($jquery_handler), $arfversion);
            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arf_tipso');

            if ( version_compare( $wp_version , '4.9.0', '>=' ) ) {
                $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
                wp_localize_script('jquery', 'cm_settings', $cm_settings); 
                wp_enqueue_script('wp-theme-plugin-editor');
                wp_enqueue_script( 'csslint' );
            }

        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms-import-export") {

            wp_enqueue_script('form1', ARFURL . '/js/jquery.form.js', array(), $arfversion);
            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arf_tipso');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && ($_REQUEST['page'] == "ARForms" || $_REQUEST['page'] == "ARForms-license") && !isset($_REQUEST['arfaction'])) {
            wp_enqueue_script($jquery_handler);
            
            wp_enqueue_script( 'datatables' );
            wp_enqueue_script( 'buttons-colvis' );

            wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array($jquery_handler), $arfversion);
            if ($_REQUEST['page'] == 'ARForms-license') {
                wp_enqueue_script('arfbootstrap-js');
            }

            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array('jquery'), $arfversion);
            wp_enqueue_script('arf_tipso');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms" && ($_REQUEST['arfaction'] == 'edit' || $_REQUEST['arfaction'] == 'new' || $_REQUEST['arfaction'] == 'duplicate' || $_REQUEST['arfaction'] == 'update')) {
            
            wp_enqueue_script( 'wp-hooks' );

            wp_enqueue_script('nouislider', ARFURL . '/js/nouislider.js', array($jquery_handler), $arfversion, true);
            
            wp_enqueue_script( 'arforms_editor', ARFURL . '/js/arforms_editor.js', array( $jquery_handler, $jq_draggable_handler ), $arfversion );
            wp_enqueue_script( 'arforms_sortable_resizable', ARFURL . '/js/arforms_sortable_resizable.js', array( $jquery_handler, $jq_draggable_handler ), $arfversion );
            
            wp_enqueue_script( 'arf_selectpicker', ARFURL . '/js/arf_selectpicker.js', array( $jquery_handler ), $arfversion );
            wp_enqueue_script('arforms_admin', ARFURL . '/js/arforms_admin.js', array(), $arfversion);
            
            wp_enqueue_script('arf_js_color',ARFURL.'/js/jscolor.js',array($jquery_handler), $arfversion);
            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arf_tipso');
            if ( version_compare( $wp_version , '4.9.0', '>=' ) ) {
                $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
                wp_localize_script('jquery', 'cm_settings', $cm_settings); 
                wp_enqueue_script('wp-theme-plugin-editor');
                wp_enqueue_script( 'csslint' );
            }
            
            wp_enqueue_script('bootstrap-typeahead-js', ARFURL.'/bootstrap/js/bootstrap-typeahead.js');

            wp_enqueue_script('arforms_editor_phone_utils', ARFURL . '/js/arf_phone_utils.js', array(), $arfversion, true);
            wp_enqueue_script('arforms_editor_phone_intl_input', ARFURL . '/js/intlTelInput.min.js', array(), $arfversion, true);
            wp_enqueue_script('nouislider', ARFURL . '/js/nouislider.js', array($jquery_handler), $arfversion, true);
        }elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "AROrder-entries") {
            
            wp_enqueue_script( 'wp-hooks' );
            
            wp_enqueue_script( 'arf_selectpicker', ARFURL . '/js/arf_selectpicker.js', array( $jquery_handler ), $arfversion );
            wp_enqueue_script('nouislider', ARFURL . '/js/nouislider.js', array($jquery_handler), $arfversion, true);
            
            
            wp_enqueue_script('arforms_admin', ARFURL . '/js/arforms_admin.js', array(), $arfversion);
        }    

        $field_type_label_array = array(
            'text' => esc_html__('Single Line Text','ARForms'),
            'textarea' => esc_html__('Multiline Text','ARForms'),
            'checkbox' => esc_html__('Checkbox','ARForms'),
            'radio' => esc_html__('Radio','ARForms'),
            'select' => esc_html__('Dropdown','ARForms'),
            'file' => esc_html__('File Upload','ARForms'),
            'email' => esc_html('Email Address','ARForms'),
            'number' => esc_html('Number','ARForms'),
            'phone' => esc_html__('Phone Number','ARForms'),
            'date' => esc_html__('Date','ARForms'),
            'time' => esc_html__('Time','ARForms'),
            'url' => esc_html__('Website/URL','ARForms'),
            'image'=> esc_html__('Image URL','ARForms'),
            'password' => esc_html__('Password','ARForms'),
            'html' => esc_html__('HTML','ARForms'),
            'divider' => esc_html__('Section','ARForms'),
            'section' => esc_html__('Section','ARForms'),
            'break' => esc_html__('Page Break','ARForms'),
            'scale' => esc_html__('Star Rating','ARForms'),
            'like' => esc_html__('Like button','ARForms'),
            'arfslider' => esc_html__('Slider','ARForms'),
            'colorpicker' => esc_html__('Color Picker','ARForms'),
            'imagecontrol' => esc_html__('Image','ARForms'),
            'arf_smiley' => esc_html__('Smiley','ARForms'),
            'arf_autocomplete' => esc_html__('Autocomplete','ARForms'),
            'arf_switch' => esc_html__('Switch','ARForms'),
            'arfcreditcard' => esc_html__('Credit Card','ARForms'),
            'arf_repeater' => esc_html__('Repeater', 'ARForms'),
            'signature' => esc_html__('Signature','ARForms'),
            'arf_product' => esc_html__('Product','ARForms')
        );

        $field_type_label_array = apply_filters('arf_field_type_label_filter',$field_type_label_array);

        $js_data = "__ARF_FIELD_TYPE_LABELS = '".json_encode($field_type_label_array)."';";

        $cc_expiry_year_opts = array(
            array(
                "label" => "Please Select",
                "value" => ""
            )
        );

        $n = 1;
        for( $i = date('Y'); $i < date('Y',strtotime('+31 Years')); $i++ ){
            $cc_expiry_year_opts[$n]['label'] = $i;
            $cc_expiry_year_opts[$n]['value'] = $i;
            $n++;
        }

        $cc_field_data = array(
            'cc_holder_name' => array(
                "required" => "1",
                "required_indicator" =>  "*",
                "max" =>  "",
                "minlength" =>  "",
                "field_width" =>  "",
                "name" =>  "Cardholder Name",
                "blank" =>  "This field cannot be blank.",
                "minlength_message" =>  "Invalid minimum characters => ",
                "placeholdertext" =>  "",
                "description" =>  "",
                "classes" =>  "arf_1",
                "key" =>  "{arf_unique_key}",
                "inner_class" =>  "arf_1col",
                "enable_arf_prefix" =>  "",
                "arf_prefix_icon" =>  "",
                "enable_arf_suffix" =>  "",
                "arf_suffix_icon" =>  "",
                "single_custom_validation" =>  "",
                "arf_regular_expression_msg" =>  "Entered value is invalid",
                "arf_regular_expression" =>  "",
                "arf_tooltip" =>  "",
                "frm_arf_tooltip_field_indicator" =>  "",
                "tooltip_text" =>  "",
                "css_outer_wrapper" =>  "",
                "css_label" =>  "",
                "css_input_element" =>  "",
                "css_description" =>  "",
                "css_add_icon" =>  "",
                "type" =>  "text",
                "type2" => "ccfield",
                "default_value" =>  "",
                "arf_enable_readonly" => "0"
            ),
            'cc_number' => array(
                "required" => "1",
                "required_indicator" => "*",
                "max" => "16",
                "minlength" => "13",
                "field_width" => "",
                "default_value" => "",
                "name" => "Card Number",
                "blank" => "This field cannot be blank.",
                "minlength_message" => "Invalid minimum characters length",
                "minnum" => "",
                "maxnum" => "",
                "invalid" => "Number is out of range",
                "placeholdertext" => "",
                "description" => "",
                "arf_tooltip" => "",
                "frm_arf_tooltip_field_indicator" => "",
                "tooltip_text" => "",
                "enable_arf_prefix" => "",
                "arf_prefix_icon" => "",
                "enable_arf_suffix" => "",
                "arf_suffix_icon" => "",
                "classes" => "arf_1",
                "inner_class" => "arf_1col",
                "key" => "{arf_unique_key}",
                "css_outer_wrapper" => "",
                "css_label" => "",
                "css_input_element" => "",
                "css_description" => "",
                "css_add_icon" => "",
                "type" => "number",
                "type2" => "ccfield",
                "arf_enable_readonly" =>"0"
            ),
            'cc_expiry_month' => array(
                "required" => "1",
                "required_indicator" => "*",
                "blank" => "This field cannot be blank.",
                "field_width" => "",
                "name" => "Expiration Month",
                "description" => "",
                "default_value" => "",
                "arf_tooltip" => "",
                "frm_arf_tooltip_field_indicator" => "",
                "tooltip_text" => "",
                "classes" => "arf_3",
                "inner_class" => "arf31colclass",
                "key" => "{arf_unique_key}",
                "css_outer_wrapper" => "",
                "css_label" => "",
                "css_input_element" => "",
                "css_description" => "",
                "separate_value" => "1",
                "type" => "select",
                "type2" => "ccfield",
                "options" => array(
                    array(
                        "label" => "Please Select",
                        "value" => ""
                    ),
                    array(
                        "label" => "January",
                        "value" => "01"
                    ),
                    array(
                        "label" => "February",
                        "value" => "02"
                    ),
                    array(
                        "label" => "March",
                        "value" => "03"
                    ),
                    array(
                        "label" => "April",
                        "value" => "04"
                    ),
                    array(
                        "label" => "May",
                        "value" => "05"
                    ),
                    array(
                        "label" => "June",
                        "value" => "06"
                    ),
                    array(
                        "label" => "July",
                        "value" => "07"
                    ),
                    array(
                        "label" => "August",
                        "value" => "08"
                    ),
                    array(
                        "label" => "September",
                        "value" => "09"
                    ),
                    array(
                        "label" => "October",
                        "value" => "10"
                    ),
                    array(
                        "label" => "November",
                        "value" => "11"
                    ),
                    array(
                        "label" => "December",
                        "value" => "12"
                    ),
                )
            ),
            'cc_expiry_year' => array(
                "required" => "1",
                "required_indicator" => "*",
                "blank" => "This field cannot be blank.",
                "field_width" => "",
                "name" => "Expiration Year",
                "description" => "",
                "default_value" => "",
                "arf_tooltip" => "",
                "frm_arf_tooltip_field_indicator" => "",
                "tooltip_text" => "",
                "classes" => "arf_3",
                "inner_class" => "arf_23col",
                "key" => "{arf_unique_key}",
                "css_outer_wrapper" => "",
                "css_label" => "",
                "css_input_element" => "",
                "css_description" => "",
                "separate_value" => "1",
                "type" => "select",
                "type2" => "ccfield",
                "options" => $cc_expiry_year_opts
            ),
            'cc_cvc_number' => array(
                "required" => "1",
                "required_indicator" => "*",
                "max" => "4",
                "minlength" => "3",
                "field_width" => "",
                "default_value" => "",
                "name" => "CVC",
                "blank" => "This field cannot be blank.",
                "minlength_message" => "Invalid minimum characters length",
                "minnum" => "",
                "maxnum" => "",
                "invalid" => "Number is out of range",
                "placeholdertext" => "",
                "description" => "",
                "arf_tooltip" => "",
                "frm_arf_tooltip_field_indicator" => "",
                "tooltip_text" => "",
                "enable_arf_prefix" => "",
                "arf_prefix_icon" => "",
                "enable_arf_suffix" => "",
                "arf_suffix_icon" => "",
                "classes" => "arf_3",
                "inner_class" => "arf_3col",
                "key" => "{arf_unique_key}",
                "css_outer_wrapper" => "",
                "css_label" => "",
                "css_input_element" => "",
                "css_description" => "",
                "css_add_icon" => "",
                "type" => "number",
                "type2" => "ccfield",
                "arf_enable_readonly" =>"0"
            )
        );

        $cc_field_options = "__ARFCCFIELDOPTIONS = '".json_encode($cc_field_data)."'; ";

        $js_data .= "
            var __ARF_DEL_FORM_MSG = '" . sprintf(addslashes(esc_html__("Are you sure you want to %s delete this form?", "ARForms")), "<br />") ."';

            var __ARF_DEL_ENTRY_MSG = '". sprintf(addslashes(esc_html__("Are you sure you want to %s delete this entry?", "ARForms")), "<br />")."';

            var __ARF_DEL_FILE_MSG = '".sprintf(addslashes(esc_html__("Are you sure you want to %s delete this file?", "ARForms")), "<br />")."';

            var __ARF_RESET_STYLE_MSG = '". sprintf(addslashes(esc_html__("Are you sure want to %s reset style?", "ARForms")), "<br />") ."';

            var __ARF_SELECT_FIELD_TEXT = '".addslashes(esc_html__("Please select one or more record to perform action", "ARForms"))." ';
            var __ARF_ADDIMG = '".addslashes(esc_html__("Add Image", "ARForms"))."';

            var __ARF_SEL_FIELD = '".addslashes(esc_html__("Please Select Field", "ARForms"))."';
            var __CLICKTOCOPY = '". addslashes(esc_html__("Click to Copy", "ARForms"))."';
            var __COPIED = '".addslashes(esc_html__("copied", "ARForms"))."';
            var __ARF_CSV_MSG = '".addslashes(esc_html__("Please upload csv file","ARForms"))." ';

            var __ARF_PRESET_FILE_MSG = '".  addslashes(esc_html__("Please Enter preset title","ARForms"))." ';

            var __ARF_SELECT_VALIDACTION_MSG = '". addslashes(esc_html__("Please select valid action","ARForms"))." ';

            var __ARF_DELETE_TEXT = '".addslashes(esc_html__('Delete','ARForms')) ."';

            var __ARF_CANCEL_TEXT = '".addslashes(esc_html__('Cancel','ARForms')) ."';

            var __ARF_AJAX_SAVE_FORM_ERROR = '".addslashes(esc_html__('There is something error while saving form','ARForms')) ."';

            var __ARF_RESET_TEXT = '". addslashes(esc_html__("Reset","ARForms"))."'; ";

        $traslated_text = "
            var __ARF_DEL_CONF_MSG  = '" . addslashes(esc_html__("Are you sure to delete configuration?", "ARForms")). "';
            var __ARF_DEL_FIELD_MSG = '" .sprintf(addslashes(esc_html__("Are you sure you want to %s delete this field?", "ARForms")), "<br />") ."';
            var __SEL_FIELD = '".addslashes(esc_html__("Select Field", "ARForms"))."';
            var __TRYAGAIN = '".addslashes(esc_html__("Please try again", "ARForms"))."';

            var __REMOVE_MSG = '".addslashes(esc_html__("Successfully removed file", "ARForms"))."';
           
            var __SEL_FORM = '".addslashes(esc_html__("Please select form", "ARForms"))."';

            var __ARFINVALIDFORMULA = '".addslashes(esc_html__("Your formula is invalid", "ARForms")) ."';

            var __ARFVALIDFORMULA = '". addslashes(esc_html__("Your formula is valid", "ARForms")) ."';

            var __ARF_SELECT_FIELD_TEXT = '".addslashes(esc_html__("Please select one or more record to perform action", "ARForms")) ."';
            var __ARF_BLANKMSG_CHK = '" . addslashes( esc_html__( 'Select atleast one flag', 'ARForms' ) ) . "';
            var __ARF_BLANKMSG_FILE_CHK = '" . addslashes( esc_html__( 'Select atleast one file type', 'ARForms' ) ) . "';
            var __NOTHNG_SEL = '". addslashes(esc_html__("Nothing Selected", "ARForms")) ."';

            var __ARF_NOT_REQUIRE = '".addslashes(esc_html__("Click to mark as not compulsory field", "ARForms")) ."';

            var __ARF_REQUIRE = '".addslashes(esc_html__("Click to mark as compulsory field", "ARForms")) ."';

            __ARF_SELECT_DEFAULT_LABEL = '" . addslashes( esc_html__('Choose Option', 'ARForms') ) . "';
            __ARF_MULTI_SELECT_DEFAULT_LABEL = '" . addslashes( esc_html__('Choose Options', 'ARForms') ) . "';";


        wp_add_inline_script('arforms_admin',$js_data);
        wp_add_inline_script('arforms_admin',$cc_field_options);
        wp_add_inline_script('arforms_admin', $traslated_text);
        wp_add_inline_script('arforms_admin',$cc_field_options);

        $arf_hook_data = "
            var field_convert_part1 = '" . addslashes( esc_html__( 'Field values will be lost once converted to', 'ARForms' ) ) . "';
            var arf_type_text = '" . addslashes( esc_html__( 'Type', 'ARForms' ) ) . "';
            var field_converting_part1 = '" . addslashes( esc_html__( 'You are converting', 'ARForms' ) ) . "';
            var field_converting_type_to = '" . addslashes( esc_html__( 'type to', 'ARForms' ) ) . "';
            var field_converting_part2 = '" . addslashes( esc_html__( 'type, field options will be different from', 'ARForms' ) ) . "';
            var field_coverting_to_text = '" . addslashes( esc_html__( 'type to', 'ARForms' ) ) . "';
            var field_converting_part3 = '" . addslashes( esc_html__( 'Please do needful', 'ARForms' ) ) . "';
            var field_converting_notice = '" . addslashes( esc_html__( 'Field type changing also may affect email notification section, conditional rule section, payment gateways configuration and other add-ons configuration. So it is highly recommend to verify all these settings after changing field type', 'ARForms' ) ) . "';
        ";

        wp_add_inline_script( 'arforms_editor', $arf_hook_data );
    }

    function set_css() {
        global $arfversion,$wp_version;
        if( !empty( $_GET['page'] ) && preg_match('/ARForms*/', $_GET['page']) ){
            wp_deregister_style( 'datatables' );
            wp_dequeue_style( 'datatables' );

            wp_register_style( 'datatables', ARFURL . '/datatables/media/css/datatables.css', array(), $arfversion );
        }

        if ((isset($_REQUEST['page']) && $_REQUEST['page'] != '') && ($_REQUEST['page'] == "ARForms-entries" || "ARForms-popups"==$_REQUEST['page'])) {
            wp_enqueue_style('arf_selectpicker', ARFURL . '/css/arf_selectpicker.css', array(), $arfversion);
            wp_register_style('arf_tipso_css', ARFURL . '/css/tipso.min.css', array(), $arfversion);
            wp_enqueue_style('arf_tipso_css');

            wp_register_style('arfbootstrap-datepicker-css', ARFURL . '/bootstrap/css/bootstrap-datetimepicker.css', array(), $arfversion);
            wp_enqueue_style('arfbootstrap-datepicker-css');

            wp_enqueue_style( 'datatables' );
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms-settings") {
            wp_enqueue_style('arf_selectpicker', ARFURL . '/css/arf_selectpicker.css', array(), $arfversion);
            wp_register_style('arf_tipso_css', ARFURL . '/css/tipso.min.css', array(), $arfversion);
            wp_enqueue_style('arf_tipso_css');
            if ( version_compare( $wp_version, '4.9.0', '>=' ) ) {
                wp_enqueue_style( 'wp-codemirror' );
            }
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms-import-export") {
            wp_enqueue_style('arf_selectpicker', ARFURL . '/css/arf_selectpicker.css', array(), $arfversion);
             wp_register_style('arf_tipso_css', ARFURL . '/css/tipso.min.css', array(), $arfversion);
            wp_enqueue_style('arf_tipso_css');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && ($_REQUEST['page'] == "ARForms" || $_REQUEST['page'] == "ARForms-license" ) && !isset($_REQUEST['arfaction'])) {
            wp_register_style('arf-fontawesome-css', ARFURL . '/css/font-awesome.min.css', array(), $arfversion);
            if ($_REQUEST['page'] == 'ARForms-license') {
                wp_enqueue_style('arf_selectpicker', ARFURL . '/bootstrap/css/arf_selectpicker.css', array(), $arfversion);
                wp_enqueue_style('arf-fontawesome-css');
            } else {
                wp_enqueue_style( 'datatables' );
            }
            wp_register_style('arf_tipso_css', ARFURL . '/css/tipso.min.css', array(), $arfversion);
            wp_enqueue_style('arf_tipso_css');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms" && ($_REQUEST['arfaction'] == 'edit' || $_REQUEST['arfaction'] == 'new' || $_REQUEST['arfaction'] == 'duplicate' || $_REQUEST['arfaction'] == 'update')) {
            wp_enqueue_style('arf_animate_main', ARFURL . '/css/animate.css', array(), $arfversion);
            wp_register_style('arfdisplaycss_editor', ARFURL . '/css/arf_front.css', array(), $arfversion);
            wp_enqueue_style('arfdisplaycss_editor');

            wp_register_style('arfdisplayflagiconcss_editor', ARFURL . '/css/flag_icon.css', array(), $arfversion);
            wp_enqueue_style('arfdisplayflagiconcss_editor');

            wp_register_style('arf_tipso_css', ARFURL . '/css/tipso.min.css', array(), $arfversion);
            wp_enqueue_style('arf_tipso_css');

            wp_register_style('nouislider', ARFURL . '/css/nouislider.css', array(), $arfversion);
            wp_enqueue_style('nouislider');

            wp_register_style('arf-fontawesome-css', ARFURL . '/css/font-awesome.min.css', array(), $arfversion);
            wp_enqueue_style('arf-fontawesome-css');
            wp_register_style('arfbootstrap-datepicker-css', ARFURL . '/bootstrap/css/bootstrap-datetimepicker.css', array(), $arfversion);
            wp_enqueue_style('arfbootstrap-datepicker-css');
            wp_register_script('bootstrap-typeahead-js', ARFURL . '/bootstrap/js/bootstrap-typeahead.js', array('jquery'), $arfversion);
            wp_enqueue_style('bootstrap-typeahead-js');
            if ( version_compare( $wp_version , '4.9.0', '>=' ) ) {
                wp_enqueue_style('wp-codemirror');
            }

            wp_register_style('arf_flags_css', ARFURL . '/css/flag_icon.css', array(), $arfversion);
            wp_enqueue_style('arf_flags_css');         
        }
    }

    function wp_dequeue_script_custom($handle) {
        global $wp_scripts;
        if (!is_a($wp_scripts, 'WP_Scripts'))
            $wp_scripts = new WP_Scripts();

        $wp_scripts->dequeue($handle);
    }

    function wp_dequeue_style_custom($handle) {
        global $wp_styles;
        if (!is_a($wp_styles, 'WP_Styles'))
            $wp_styles = new WP_Styles();

        $wp_styles->dequeue($handle);
    }

    function getwpversion() {
        global $arfversion, $MdlDb, $arnotifymodel, $arfform, $arfrecordmeta;
        $bloginformation = array();
        $str = $MdlDb->get_rand_alphanumeric(10);

        if (is_multisite())
            $multisiteenv = "Multi Site";
        else
            $multisiteenv = "Single Site";

        $bloginformation[] = $arnotifymodel->sitename();
        $bloginformation[] = $arfform->sitedesc();
        $bloginformation[] = home_url();
        $bloginformation[] = get_bloginfo('admin_email');
        $bloginformation[] = $arfrecordmeta->wpversioninfo();
        $bloginformation[] = $arfrecordmeta->getlanguage();
        $bloginformation[] = $arfversion;
        $bloginformation[] = $_SERVER['REMOTE_ADDR'];
        $bloginformation[] = $str;
        $bloginformation[] = $multisiteenv;

        $arnotifymodel->checksite($str);

        $valstring = implode("||", $bloginformation);
        $encodedval = base64_encode($valstring);

        $urltopost = $arfform->getsiteurl();
        $response = wp_remote_post($urltopost, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array('wpversion' => $encodedval),
            'cookies' => array()
                )
        );
    }

    function arf_backup() {
        $databaseversion = get_option('arf_db_version');
        update_option('old_db_version', $databaseversion);
    }

    function upgrade_data() {
        global $newdbversion;

        if (!isset($newdbversion) || $newdbversion == ""){
            $newdbversion = get_option('arf_db_version');
        }

        if (version_compare($newdbversion, '5.7.1', '<')) {
            $path = FORMPATH . '/core/views/upgrade_latest_data.php';
            include($path);
        }
    }

    function arf_rmdirr($dirname) {

        if (!file_exists($dirname)) {
            return false;
        }


        if (is_file($dirname)) {
            return unlink($dirname);
        }


        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {

            if ($entry == '.' || $entry == '..') {
                continue;
            }


            $this->arf_rmdirr("$dirname/$entry");
        }


        $dir->close();
        return rmdir($dirname);
    }

    function arf_copyr($source, $dest) {
        global $wp_filesystem;

        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }


        if (is_file($source)) {
            return $wp_filesystem->copy($source, $dest);
        }


        if (!is_dir($dest)) {
            $wp_filesystem->mkdir($dest);
        }


        $dir = dir($source);
        while (false !== $entry = $dir->read()) {

            if ($entry == '.' || $entry == '..') {
                continue;
            }


            $this->arf_copyr("$source/$entry", "$dest/$entry");
        }


        $dir->close();
        return true;
    }

    function arf_hide_update_notice_to_all_admin_users() {
        global $pagenow;

        if (isset($_GET) and ( isset($_GET['page']) and preg_match('/ARForms*/', $_GET['page'])) or ( $pagenow == 'edit.php' and isset($_GET) and isset($_GET['post_type']) and $_GET['post_type'] == 'frm_display')) {
            remove_all_actions('network_admin_notices', 10000);
            remove_all_actions('user_admin_notices', 10000);
            remove_all_actions('admin_notices', 10000);
            remove_all_actions('all_admin_notices', 10000);
        }
    }

    function arf_export_form_data() {

        if (isset($_POST['s_action']) && !in_array($_POST['s_action'], array('opt_export_form', 'opt_export_both'))) {
            return false;
        }

        global $wpdb, $submit_bg_img, $arfmainform_bg_img, $form_custom_css, $WP_Filesystem, $submit_hover_bg_img, $MdlDb,$arformcontroller;

        $arf_db_version = get_option('arf_db_version');

        $wp_upload_dir = wp_upload_dir();
        $upload_dir = $wp_upload_dir['basedir'] . '/arforms/';
        $upload_baseurl = $wp_upload_dir['baseurl'] . '/arforms/';
        $form_id_req = (isset($_REQUEST['is_single_form']) && $_REQUEST['is_single_form'] == 1) ? $_REQUEST['frm_add_form_id_name'] : (isset($_REQUEST['frm_add_form_id']) ? $_REQUEST['frm_add_form_id'] : '');

        if (isset($_REQUEST['export_button'])) {
            if (!empty($form_id_req)) {
                if($_REQUEST['is_single_form'] == 1 )
                {
                    $form_ids = $_REQUEST['frm_add_form_id_name'];
                }
                else{
                    $arf_frm_add_form_id =  $_REQUEST['frm_add_form_id'];
                    $arf_frm_add_form_ids = array();
                    if( is_array($arf_frm_add_form_id) && count($arf_frm_add_form_id) > 0 )
                    {
                        foreach ($arf_frm_add_form_id as $arf_frm_add_form_id_key => $arf_frm_add_form_id_value) {
                            if($arf_frm_add_form_id_value!='')
                            {
                                $arf_frm_add_form_ids[] = $arf_frm_add_form_id_value;
                            }
                        }
                    }
                    $form_ids = (count($arf_frm_add_form_ids) > 0) ? implode(",", $arf_frm_add_form_ids) : '';
                }
                
                $res = $wpdb->get_results("SELECT * FROM " . $MdlDb->forms . " WHERE id in (" . $form_ids . ")");

                if( !is_array($form_ids) && empty($res) ){
                    
                }

                $file_name = "ARForms_" . time();

                $filename = $file_name . ".txt";

                

                $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

                $xml .= "<forms>\n";

                foreach ($res as $key => $result_array) {

                    $form_id = $res[$key]->id;

                    $xml .= "\t<form id='" . $res[$key]->id . "'>\n";

                    $xml .= "\t<site_url>" . site_url() . "</site_url>\n";

                    $xml .= "\t<exported_site_uploads_dir>" . $upload_baseurl . "</exported_site_uploads_dir>\n";

                    $xml .= "\t<arf_db_version>" . $arf_db_version . "</arf_db_version>\n";

                    $xml .= "\t\t<general_options>\n";
                    foreach ($result_array as $key => $value) {

                        if ($key == 'options') {
                            foreach (maybe_unserialize($value) as $ky => $vl) {
                                if ($ky != 'before_html') {
                                    if (!is_array($vl)) {
                                        if ($ky == 'success_url') {
                                            $new_field[$ky] = $vl;

                                            $new_field[$ky] = str_replace('&amp;', '[AND]', $new_field[$ky]);
                                        } else if ($ky == 'form_custom_css') {
                                            $form_custom_css = str_replace(site_url(), '[REPLACE_SITE_URL]', $vl);

                                            $form_custom_css = str_replace('&lt;br /&gt;', '[ENTERKEY]', str_replace('&lt;br/&gt;', '[ENTERKEY]', str_replace('&lt;br&gt;', '[ENTERKEY]', str_replace('<br />', '[ENTERKEY]', str_replace('<br/>', '[ENTERKEY]', str_replace('<br>', '[ENTERKEY]', trim(preg_replace('/\s\s+/', '[ENTERKEY]', $form_custom_css))))))));
                                        } else if ($ky == 'arf_form_other_css') {
                                            $new_field[$ky] = str_replace('&lt;br /&gt;', '[ENTERKEY]', str_replace('&lt;br/&gt;', '[ENTERKEY]', str_replace('&lt;br&gt;', '[ENTERKEY]', str_replace('<br />', '[ENTERKEY]', str_replace('<br/>', '[ENTERKEY]', str_replace('<br>', '[ENTERKEY]', trim(preg_replace('/\s\s+/', '[ENTERKEY]', str_replace(site_url(), '[REPLACE_SITE_URL]', $vl)))))))));
                                        } else {
                                            $string = ( ( is_array($vl) and count($vl) > 0 ) ? $vl : str_replace('&lt;br /&gt;', '[ENTERKEY]', str_replace('&lt;br/&gt;', '[ENTERKEY]', str_replace('&lt;br&gt;', '[ENTERKEY]', str_replace('<br />', '[ENTERKEY]', str_replace('<br/>', '[ENTERKEY]', str_replace('<br>', '[ENTERKEY]', trim(preg_replace('/\s\s+/', '[ENTERKEY]', $vl)))))))) );

                                            $new_field[$ky] = $string;
                                        }
                                    } else
                                        $new_field[$ky] = $vl;
                                }
                                else {
                                    $vl2 = '[REPLACE_BEFORE_HTML]';
                                    $new_field[$ky] = $vl2;
                                }
                            }
                            $value1 = json_encode($new_field);

                            $value1 = "<![CDATA[" . $value1 . "]]>";

                            $xml .= "\t\t\t<$key>";


                            $xml .= "$value1";


                            $xml .= "</$key>\n";
                        } elseif ($key == 'form_css') {
                            $form_css_arry = maybe_unserialize($value);
                            foreach ($form_css_arry as $form_css_key => $form_css_val) {
                                if ($form_css_key == "submit_bg_img") {
                                    $submit_bg_img = $form_css_val;
                                } else if ($form_css_key == "submit_hover_bg_img") {
                                    $submit_hover_bg_img = $form_css_val;
                                } elseif ($form_css_key == "arfmainform_bg_img") {
                                    $arfmainform_bg_img = $form_css_val;
                                }
                            }

                            $xml .= "\t\t\t<$key>";

                            $new_form_css_val = json_encode( $form_css_arry );

                            $xml .= "<![CDATA[" . $new_form_css_val . "]]>";

                            //and close the element
                            $xml .= "</$key>\n";
                        } else if ($key == "description" || $key == "name") {
                            $value = "<![CDATA[" . $value . "]]>";

                            $xml .= "\t\t\t<$key>";

                            //embed the SQL data in a CDATA element to avoid XML entity issues
                            $xml .= "$value";

                            //and close the element
                            $xml .= "</$key>\n";
                        } else if('columns_list' == $key) {
                            $xml .= "\t\t\t<$key>";

                            //embed the SQL data in a CDATA element to avoid XML entity issues
                            $xml .= "<![CDATA[" . $value . "]]>";

                            //and close the element
                            $xml .= "</$key>\n";
                        } else {
                            $xml .= "\t\t\t<$key>";

                            //embed the SQL data in a CDATA element to avoid XML entity issues
                            $xml .= "$value";

                            //and close the element
                            $xml .= "</$key>\n";
                        }
                    }
                    $xml .= "\t\t</general_options>\n";


                    $xml .= "\t\t<fields>\n";

                    $res_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->fields . " WHERE form_id = %d",$result_array->id));

                    foreach ($res_fields as $key_fields => $result_field_array) {
                        $xml .= "\t\t\t<field>\n";
                        $field_options_array = array();
                        $new_field1 = array();
                        foreach ($result_field_array as $key_field => $value_field) {
                            if ($key_field == 'field_options') {
                                $field_options_array = json_decode($value_field);
                                if (json_last_error() == JSON_ERROR_NONE) {
                                    
                                } else {
                                    $field_options_array = maybe_unserialize($value_field);
                                }
                                
                                foreach ($field_options_array as $ky => $vl) {
                                    if ($ky != 'custom_html') {
                                        if(is_object($vl))
                                        {
                                            $vl = $arformcontroller->arfObjtoArray($vl);
                                        }
                                        $vl = ( (is_array($vl) ) ? $vl : str_replace('&lt;br /&gt;', '[ENTERKEY]', str_replace('&lt;br/&gt;', '[ENTERKEY]', str_replace('&lt;br&gt;', '[ENTERKEY]', str_replace('<br />', '[ENTERKEY]', str_replace('<br/>', '[ENTERKEY]', str_replace('<br>', '[ENTERKEY]', trim(preg_replace('/\s\s+/', '[ENTERKEY]', $vl)))))))) );

                                        $new_field1[$ky] = $vl;
                                    }
                                }
                                $value_field_ser = json_encode($new_field1);

                                $value_field_ser = "<![CDATA[" . $value_field_ser . "]]>";

                                $xml .= "\t\t\t\t<$key_field>";


                                $xml .= "$value_field_ser";


                                $xml .= "</$key_field>\n";
                            } elseif ($key_field == 'conditional_logic') {
                                $conditional_logic_array = maybe_unserialize($value_field);
                                if (is_array($conditional_logic_array)) {
                                    foreach ($conditional_logic_array as $ky_cl => $vl_cl) {
                                        $new_field_cl[$ky_cl] = $vl_cl;
                                    }
                                    $new_field_cl1 = json_encode($new_field_cl);
                                    $xml .= "\t\t\t\t<$key_field>";


                                    $new_field_cl1 = "<![CDATA[" . $new_field_cl1 . "]]>";

                                    $xml .= "$new_field_cl1";


                                    $xml .= "</$key_field>\n";
                                }
                            } else {
                                if ($key_field == "description" || $key_field == "name" || $key_field == "default_value") {
                                    $vl1 = "<![CDATA[" . stripslashes_deep($value_field). "]]>";
                                } elseif ($key_field == "options" && $result_field_array->type == 'radio') {
                                    $vl1 = "<![CDATA[" . trim(json_encode($value_field), '"') . "]]>";
                                } else if ($key_field == "options") {
                                    $vl1 = "<![CDATA[" . json_encode($value_field) . "]]>";
                                } else {
                                    $vl1 = $value_field;
                                }

                                $xml .= "\t\t\t\t<$key_field>";

                                //embed the SQL data in a CDATA element to avoid XML entity issues
                                $xml .= "$vl1";


                                //and close the element
                                $xml .= "</$key_field>\n";
                            }
                        }
                        $xml .= "\t\t\t</field>\n";
                    }
                    $xml .= "\t\t</fields>\n";

                    $xml .= "\t\t<autoresponder>\n";

                    $res_ar = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->ar . " WHERE frm_id = %d",$result_array->id));

                    foreach ($res_ar as $key_ar => $result_ar_array) {

                        foreach ($result_ar_array as $key_ar => $value_ar) {


                            if ($key_ar == 'aweber' || $key_ar == 'mailchimp' || $key_ar == 'getresponse' || $key_ar == 'gvo' || $key_ar == 'ebizac' || $key_ar == 'madmimi' || $key_ar == 'icontact' || $key_ar == 'constant_contact' || $key_ar == 'infusionsoft' || $key_ar == 'mailerlite' || 'sendinblue' == $key_ar || $key_ar == 'hubspot' || $key_ar == 'convertkit') {

                                $xml .= "\t\t\t\t<$key_ar>\n";

                                
                                
                                    if(!empty($value_ar)){    
                                    foreach (maybe_unserialize($value_ar) as $autores_key => $autores_val) {

                                        $xml .= "\t\t\t\t\t<$autores_key>";

                                        $autores_val = "<![CDATA[" . json_encode($autores_val) . "]]>";


                                        $xml .= "$autores_val";


                                        $xml .= "</$autores_key>\n";
                                    }
                                }

                                $xml .= "\t\t\t\t</$key_ar>\n";
                            } else {

                                $xml .= "\t\t\t\t<$key_ar>";

                                $value_ar = "<![CDATA[" . json_encode( arf_json_decode($value_ar,true) ) . "]]>";


                                $xml .= "$value_ar";


                                $xml .= "</$key_ar>\n";
                            }
                        }
                    }
                    $xml .= "\t\t</autoresponder>\n";

                    $xml .= "\t\t<submit_bg_img>";


                    $xml .= "$submit_bg_img";


                    $xml .= "</submit_bg_img>\n";


                    $xml .= "\t\t<submit_hover_bg_img>";


                    $xml .= "$submit_hover_bg_img";


                    $xml .= "</submit_hover_bg_img>\n";


                    $xml .= "\t\t<arfmainform_bg_img>";


                    $xml .= "$arfmainform_bg_img";


                    $xml .= "</arfmainform_bg_img>\n";

                    $xml .= "\t\t<form_custom_css>";


                    $xml .= "$form_custom_css";


                    $xml .= "</form_custom_css>\n";

                    /* Exporting Form Entries */
                    if ($_REQUEST['opt_export'] == 'opt_export_both') {

                        global $wpdb, $arfform, $arffield, $db_record, $style_settings, $armainhelper, $arfieldhelper, $arrecordhelper;

                        $form = $arfform->getOne($form_id);

                        $form_name = sanitize_title_with_dashes($form->name);

                        $form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'imagecontrol') and fi.form_id=" . $form->id, 'ORDER BY id');

                        $entry_id = $armainhelper->get_param('entry_id', false);

                        $where_clause = "it.form_id=" . (int) $form_id;

                        $wp_date_format = apply_filters('arfcsvdateformat', 'Y-m-d H:i:s');

                        if ($entry_id) {


                            $where_clause .= " and it.id in (";


                            $entry_ids = explode(',', $entry_id);


                            foreach ((array) $entry_ids as $k => $it) {


                                if ($k)
                                    $where_clause .= ",";


                                $where_clause .= $it;


                                unset($k);


                                unset($it);
                            }

                            $where_clause .= ")";
                        }else if (!empty($search)) {
                            $where_clause = $arrecordcontroller->get_search_str($where_clause, $search, $form_id, $fid);
                        }

                        $where_clause = apply_filters('arfcsvwhere', $where_clause, compact('form_id'));

                        $entries = $db_record->getAll($where_clause, '', '', true, false);

                        $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $form->id);
                        $entries = apply_filters('arfpredisplaycolsitems', $entries, $form->id);
                        $to_encoding = isset($style_settings->csv_format) ? $style_settings->csv_format : 'UTF-8';

                        $xml .= "\n\t\t<form_entries>\n";

                        foreach ($entries as $entry) {

                            global $wpdb, $MdlDb;

                            $res_data = $wpdb->get_results($wpdb->prepare('SELECT country, browser_info FROM ' . $MdlDb->entries . ' WHERE id = %d', $entry->id), 'ARRAY_A');

                            $entry->country = $res_data[0]['country'];
                            $entry->browser = $res_data[0]['browser_info'];

                            $i = 0;
                            $size_of_form_cols = count($form_cols);

                            $list = '';

                            $xml .= "\n\t\t\t<form_entry>\n";

                            foreach ($form_cols as $col) {

                                $field_value = isset($entry->metas[$col->id]) ? $entry->metas[$col->id] : false;

                                if (!$field_value and $entry->attachment_id) {

                                    $col->field_options = maybe_unserialize($col->field_options);
                                }

                                if( $col->type == 'html' && $col->field_options['enable_total'] != 1 ){
                                    continue;
                                }


                                if ($col->type == 'file') {

                                    $old_entry_values = explode('|', $field_value);
                                    $new_field_value = array();

                                    foreach ($old_entry_values as $old_entry_val) {
                                        $new_field_value[] = str_replace('thumbs/', '', wp_get_attachment_url($old_entry_val));
                                    }
                                    $new_field_value = implode('|', $new_field_value);
                                    $field_value = $new_field_value;
                                } else if ($col->type == 'date') {

                                    $field_value = $arfieldhelper->get_date($field_value, $wp_date_format);
                                } else {

                                    $checked_values = maybe_unserialize($field_value);

                                    $checked_values = apply_filters('arfcsvvalue', $checked_values, array('field' => $col));

                                    if (is_array($checked_values)) {

                                        if( in_array($col->type,array('checkbox','radio','select','arf_autocomplete', 'arf_multiselect')) ){
                                            $field_value = implode('^|^', $checked_values);
                                        } elseif($col->type == 'matrix'){

                                            $checked_values = maybe_unserialize($field_value);
                                            $field_obj = $arffield->getOne( $col->id );

                                            $rx = 0;
                                            $new_checked_values = array();
                                            foreach( $field_obj->rows as $frows ){
                                                $new_checked_values[$rx] = !empty( $checked_values[$rx] ) ? $checked_values[$rx] : '';
                                                $rx++;
                                            }

                                            $field_value = implode(',', $new_checked_values);

                                        } else {
                                            $field_value = implode(',', $checked_values);
                                        }

                                    } else {


                                        $field_value = $checked_values;
                                    }

                                    $charset = get_option('blog_charset');

                                    $field_value = $arrecordhelper->encode_value($field_value, $charset, $to_encoding);


                                    $field_value = str_replace('"', '""', stripslashes($field_value));
                                }


                                $field_value = str_replace(array("\r\n", "\r", "\n"), ' <br />', $field_value);

                                if ($size_of_form_cols == $i) {
                                    $list .= $field_value;
                                } else
                                    $list .= $field_value . ',';

                                $col_name = str_replace(' ', '_ARF_', $col->name);

                                $col_name = str_replace('/', '_ARF_SLASH_', $col_name);

                                $col_name = str_replace('&','&amp;',$col_name);

                                $col_name = str_replace('"','&quot;',$col_name);

                                $xml .= "\t\t\t\t<ARF_Field field_label=\"".$col_name."\" field_type='$col->type'>";

                                $xml .= "<![CDATA[" . $field_value . "]]>";

                                $xml .= "</ARF_Field>\n";
                                
                                unset($col);
                                unset($field_value);

                                $i++;
                            }
                            $formatted_date = date($wp_date_format, strtotime($entry->created_date));
                            $xml .= "\t\t\t\t<ARF_Field field_label='Created_ARF_Date'><![CDATA[{$formatted_date}]]></ARF_Field>";
                            $xml .= "\n\t\t\t\t<ARF_Field field_label='IP_ARF_Address'><![CDATA[{$entry->ip_address}]]></ARF_Field>";
                            $xml .= "\n\t\t\t\t<ARF_Field field_label='Entry_ARF_id'><![CDATA[{$entry->id}]]></ARF_Field>";
                            $xml .= "\n\t\t\t\t<ARF_Field field_label='Country'><![CDATA[{$entry->country}]]></ARF_Field>";
                            $xml .= "\n\t\t\t\t<ARF_Field field_label='Browser'><![CDATA[{$entry->browser}]]></ARF_Field>";

                            $xml .= "\n\t\t\t</form_entry>";
                            unset($entry);
                        }

                        $xml .= "\n\t\t</form_entries>\n";
                    }

                    /* Exporting Form Entries */

                    $xml .= "\t</form>\n\n";
                }
                $xml .= "</forms>";

                $xml = base64_encode($xml);

                ob_start();
                ob_clean();
                header("Content-Type: plain/text");
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header("Pragma: no-cache");
                print($xml);
                exit;
            }
        }
    }

    function arf_front_assets() {
        global $arfsettings,$arfversion;
        if (!isset($arfsettings)) {
            $arfsettings_new = get_option('arf_options');
        } else {
            $arfsettings_new = $arfsettings;
        }

        if (isset($arfsettings_new->arfmainformloadjscss) && $arfsettings_new->arfmainformloadjscss == 1) {
            wp_enqueue_script('jquery-maskedinput');
            wp_enqueue_script('arforms_phone_utils');
            wp_enqueue_script('wp-hooks');
            wp_enqueue_script('arforms');
            wp_enqueue_script('arf-conditional-logic-js');
            wp_enqueue_script('arf-modal-js');
            wp_enqueue_style('arfdisplaycss');
            wp_enqueue_style('arf_animate_css_front');
            wp_enqueue_style('arfdisplayflagiconcss');
            wp_enqueue_script('jquery-validation');
            if(!empty($arfsettings_new->arf_load_js_css))
            {
                if(in_array('slider',$arfsettings_new->arf_load_js_css))
                {
                    wp_enqueue_script('nouislider');
                    wp_enqueue_style('nouislider');
                }
                if(in_array('colorpicker',$arfsettings_new->arf_load_js_css))
                {
                    wp_enqueue_script('arf_js_color');
                    wp_enqueue_script('arf-colorpicker-basic-js');
                    
                }
                if(in_array('dropdown',$arfsettings_new->arf_load_js_css))
                {
                    wp_enqueue_script('arf_selectpicker');                    
                    wp_enqueue_style('arf_selectpicker');                    
                }
                if(in_array('file',$arfsettings_new->arf_load_js_css))
                {
                    wp_enqueue_script('filedrag');
                    wp_enqueue_style('arf-filedrag');
                }
                if(in_array('date_time',$arfsettings_new->arf_load_js_css))
                {
                    wp_enqueue_script('bootstrap-locale-js');
                    wp_enqueue_script('bootstrap-datepicker');
                    wp_enqueue_style('arfbootstrap-datepicker-css');
                }
                if(in_array('autocomplete',$arfsettings_new->arf_load_js_css)){
                    wp_enqueue_script('bootstrap-typeahead-js');
                }
                if(in_array('fontawesome',$arfsettings_new->arf_load_js_css)){
                    wp_enqueue_style('arf-fontawesome-css');
                }
                if(in_array('mask_input',$arfsettings_new->arf_load_js_css)){
                    wp_enqueue_script('jquery-maskedinput');
                    wp_enqueue_script('arforms_phone_intl_input');
                    wp_enqueue_script('arforms_phone_utils');
                }
                if(in_array('tooltip',$arfsettings_new->arf_load_js_css)){
                    wp_enqueue_script('arf_tipso_js_front');
                    wp_enqueue_style('arf_tipso_css_front');
                }
                if(in_array('animate_number',$arfsettings_new->arf_load_js_css)){
                    wp_enqueue_script('animate-numbers');
                }                
            }
        }
    }

    function arf_print_all_admin_scripts() {
        global $arfversion,$wp_version;
        
        $jquery_handler = 'jquery';
        $jq_draggable_handler = "jquery-ui-draggable";
        if( version_compare($wp_version, '4.2','<') ){
            $jquery_handler = "jquery-custom";
            $jq_draggable_handler = "jquery-ui-draggable-custom";
        }
        wp_register_script('arf_tipso_ajax', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
        wp_print_scripts('arf_tipso_ajax');
	    
        wp_print_scripts( 'wp-hooks' );
        
        wp_register_script( 'arf_selectpicker_js_ajax', ARFURL . '/js/arf_selectpicker.js', array(), $arfversion );
        wp_print_scripts( 'arf_selectpicker_js_ajax' );
	
        wp_register_script('nouislider_ajax', ARFURL . '/js/nouislider.js', array($jquery_handler), $arfversion, true);
        wp_print_scripts('nouislider_ajax');


        wp_register_script('arf_admin_js_ajax', ARFURL . '/js/arforms_admin.js', array(), $arfversion);
        wp_print_scripts('arf_admin_js_ajax');

        wp_register_script('arforms_editor_js_ajax', ARFURL . '/js/arforms_editor.js', array($jquery_handler, $jq_draggable_handler), $arfversion);
        wp_print_scripts('arforms_editor_js_ajax');

        wp_register_script('arforms_sortable_resizable_js_ajax', ARFURL . '/js/arforms_sortable_resizable.js', array($jquery_handler, $jq_draggable_handler), $arfversion);
        wp_print_scripts('arforms_sortable_resizable_js_ajax');

        wp_register_script('slideControl_new_ajax', ARFURL . '/bootstrap/js/modernizr.js', array($jquery_handler), $arfversion, true);
        wp_print_scripts('slideControl_new_ajax');

        wp_register_script('slideControl_ajax', ARFURL . '/bootstrap/js/bootstrap-slider.js', array($jquery_handler), $arfversion, true);
        wp_print_scripts('slideControl_ajax');


        if(version_compare($wp_version, '4.2', '<')){
            wp_print_scripts('jquery-ui-widget-custom');
            wp_print_scripts('jquery-ui-mouse-custom');

            wp_print_scripts('jquery-ui-sortable-custom');
            wp_print_scripts('jquery-ui-draggable-custom');
            wp_print_scripts('jquery-ui-resizable-custom');
        } else {
            wp_print_scripts('jquery-ui-sortable');

            wp_print_scripts('jquery-ui-draggable');
        }

        wp_print_scripts('admin-widgets');

        wp_print_scripts('widgets');

        wp_register_script('arfjquery-json-ajax', ARFURL . '/js/jquery/jquery.json-2.4.js', array($jquery_handler), $arfversion);
        if (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == 'ARForms' && isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] != '') {
            wp_print_scripts('arfjquery-json-ajax');
        }

    }

    function changes_export_entry_separator(){
        $separator =  $_REQUEST['separator'];
        update_option( 'arf_form_entry_separator', $separator );
    }

    /* Cornerstone Methods */

    function arforms_cs_register_element() {
        cornerstone_register_element('ARForms_CS', 'arforms-cs', ARF_CSDIR . '/includes/arforms-cs');
    }

    function arforms_cs_icon_map($icon_map) {
        $icon_map['ARFORMS'] = ARF_CSURL . '/assets/svg/ar_forms.svg';
        return $icon_map;
    }

    /* Cornerstone Methods */

    function arf_add_new_version_release_note() {
        global $wp, $wpdb, $pagenow, $arfajaxurl, $plugin_slug, $wp_version, $maincontroller, $arfversion;;
        
        $popupData = '';
        $arf_slugs = array('ARForms', 'ARForms-entries', 'ARForms-settings', 'ARForms-import-export', 'ARForms-addons');

        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arf_slugs)) {

            $show_document_video = get_option('arf_new_version_installed', 0);

            if ($show_document_video == '0') {
                return;
            }

            $popupData = '<div class="arf_modal_overlay arfactive">
                <div class="arf_whatsnew_popup_container_wrapper">
                    <div class="arf_popup_container arf_popup_container_whatnew_model arf_view_whatsnew_modal arfactive arf_whatsnew_model_larger">
                        <div class="arf_popup_container_header">'.esc_html__("What's New in ARForms", "ARForms"). ' '.$arfversion.'</div>
                        <div class="arfwhatsnew_modal_content arf_whatsnew_popup_content_container">

                            <div class="arf_whatsnew_popup_row">
                                <div class="arf_whatsnew_popup_inner_content">
                                    You can always refer our online documentation for all the features <a href="https://www.arformsplugin.com/documentation/1-getting-started-with-arforms/" target="_blank">here</a><br>
                                    <ul style="list-style-type: disc;">
                                        <li>Added New Facility to set timer on multistep forms</li>
                                        <li>Added completely new two page break styles for wizard type forms</li>
                                        <li>Added New option to set success message posstion to bottom</li>
                                        <li>Added New facility to set modal forms on the image</li>
                                        <li>Added new facility to set Prefix/Suffix icons for the material theme</li>
                                        <li>Improved CSS loading performance for forms</li>
                                        <li>Fixed issue : Like and Smiley control with bootstrap theme</li>
                                        <li>Other minor bug fixes</li>
                                    </ul>
                                </div>';                   

                            $arf_addon_list_api_url = "https://www.arformsplugin.com/addonlist/arf_addon_api_details.php";

                            $args = array(
                                'slug' => $plugin_slug,
                                'version' => $arfversion,
                                'other_variables' => $maincontroller->arf_get_remote_post_params(),
                            );
                            $arf_addon_list_api_request_str = array(
                                'body' => array(
                                    'action' => 'plugin_new_version_check',
                                    'request' => maybe_serialize($args),
                                    'api-key' => md5(home_url())
                                ),
                                'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url()
                            );
                            $arf_addon_raw_response_json = wp_remote_post($arf_addon_list_api_url, $arf_addon_list_api_request_str);
                            if ( !is_wp_error( $arf_addon_raw_response_json ) ) 
                            {
                                $arf_addon_raw_response_json = $arf_addon_raw_response_json['body'];
                                $arf_addon_raw_response = json_decode($arf_addon_raw_response_json,true);
                                $count_arf_addon_raw_response = count($arf_addon_raw_response);
                                if(!empty($arf_addon_raw_response) && $count_arf_addon_raw_response>0)
                                {
                                    $arf_list_addon_width = (142)*($count_arf_addon_raw_response);
                                    $popupData .= '<div class="arf_whatsnew_addons_list_title">' . addslashes(esc_html__('Available Add-ons', "ARForms")) . '</div>';
                                    $popupData .= '<div class="arf_whatsnew_addons_list_div" style="min-height:165px;">';
                                    $popupData .= '<div class="arf_whatsnew_addons_list" style="width:'.$arf_list_addon_width.'px;min-width:100%;">';

                                    foreach($arf_addon_raw_response as $arf_addon_raw_key => $arf_addon_raw)
                                    {
                                        $popupData .= '<div class="arf_whatsnew_add_on"><a href="'.$arf_addon_raw['arf_plugin_link'].'" target="_blank"><img src="' . $arf_addon_raw['arf_plugin_image'] . '" width="82" height="82" /></a><div class="arf_whatsnew_add_on_text"><a href="'.$arf_addon_raw['arf_plugin_link'].'" target="_blank">'.$arf_addon_raw['arf_plugin_name'].'</a></div></div>';
                                    }

                                    $popupData .= '</div>';
                                    $popupData .= '</div>';
                                }
                            }

                    $popupData .= '</div></div>
                        <div class="arf_popup_footer arf_view_whatsnew_modal_footer">
                            <button class="rounded_button arf_btn_dark_blue" style="margin-right:7px;" name="arf_update_whatsnew_button" onclick="arf_hide_update_notice();">'. esc_html__('OK','ARForms').'</button>
                        </div>
                    </div>
                </div>
            </div>';

            $popupData .= '<script type="text/javascript">';
            $popupData .= 'jQuery(document).ready(function(){ jQuery("html").css("overflow","hidden");  });';
            $popupData .= 'function arf_hide_update_notice(){
                var ishide = 1;
                jQuery.ajax({
                type: "POST",
                url: "'.$arfajaxurl.'",
                data: "action=arf_dont_show_upgrade_notice&is_hide=" + ishide,
                success: function (res) {
                        jQuery(".arf_view_whatsnew_modal.arfactive").parents(".arf_modal_overlay.arfactive").removeClass("arfactive");
                        jQuery(".arf_view_whatsnew_modal.arfactive").removeClass("arfactive");
                        jQuery("html").css("overflow",""); 
                        return false;
                    }
                });
                return false;
            }';
            $popupData .= '</script>';
            echo $popupData;
        }
    }

    function arf_dont_show_upgrade_notice() {
        global $wp, $wpdb;
        delete_option('arf_new_version_installed');
        die();
    }

    function arf_check_valid_file($file_content = ''){
        if( '' == $file_content ){
            return true;
        }

        $arf_valid_pattern = '/(\<\?(php))/';

        if( preg_match($arf_valid_pattern,$file_content) ){
            return false;
        }

        return true;
    }

    function arf_default_themes(){

        $arf_theme_arr = array(
            'standard',
            'rounded',
            'material',
            'material_outlined'
        );

        return $arf_theme_arr;

    }

    function arforms_check_user_caps( $arf_capability, $arf_check_nonce = false, $arf_nonce_action = 'arf_edit_form_nonce' ){
        $errors = array();
        if( true == $arf_check_nonce ){
            if( !current_user_can( $arf_capability ) ){
                $msg = esc_html__('Sorry, you do not have permission to perform this action','ARForms');

                array_push($errors,$msg);
                array_push($errors,'capability_error');
                return json_encode($errors);
            }
        }

        $wpnonce = isset($_REQUEST['_wpnonce_arforms']) ? $_REQUEST['_wpnonce_arforms'] : '';
        if( '' == $wpnonce ){
            $wpnonce = isset($_POST['_wpnonce_arforms']) ? $_POST['_wpnonce_arforms'] : '';
        }

        $arf_verify_nonce = wp_verify_nonce( $wpnonce, $arf_nonce_action );

        if( !$arf_verify_nonce ){
            $msg = esc_html__('Sorry, your request cannot be processed due to security reason.','ARForms');
            array_push($errors,$msg);
            array_push($errors,'security_error');
            return json_encode($errors);
        }

        return 'success';

    }

    public function arf_selectpicker_dom( $name = '', $id = '', $attr_class = '', $style = '', $default = '', $attrs = array(), $options = array(), $grouped = false, $options_cls = array(), $disable = false, $options_attr = array(), $is_form_field = false, $field = array(), $enable_autocomplete = false, $list_class = '', $list_id = '', $use_label_as_default = false ){

        $return_dom  = '';

        $is_mutliselect = false;
        if( preg_match('/(multi\-select)/', $attr_class ) ){
            $is_mutliselect = true;
        }

        $multi_sel_val = array();

        if( !$grouped ){
            if( empty( $default ) ){
                $first_option_key = key( $options );
            } else {
                $first_option_key = $default;
            }
            if( $is_mutliselect && !empty( $default ) ){
                if( !is_array( $first_option_key ) && !empty( $options[ $first_option_key ] ) ){
                    $first_option_value = $options[$first_option_key];
                } else {
                    $first_option_value = $default;                    
                }
                $multi_sel_val = explode( ',', $default );
            } else {
                $first_option_value = isset( $options[$first_option_key] ) ? $options[$first_option_key] : '';
            }
        } else {
            if( empty( $default ) ){
                $first_group_key = key( $options );
                $first_option_key = key( $options[ $first_group_key ] );
                $first_option_value = !empty( $options[ $first_group_key ][ $first_option_key ] ) ? $options[ $first_group_key ][ $first_option_key ] : '';
            } else {
                foreach( $options as $k => $opt_arr ){
                    foreach( $opt_arr as $opt_key => $opt_val ){
                        if( $opt_key == $default ){
                            $first_option_value = $opt_val;
                            break;
                        }
                    }
                }
            }
        }

        

        $attr_str = '';

        $input_cls = '';

        if( !empty( $attrs ) ){
            foreach( $attrs as $key => $value ){
                if( 'class' == $key ){
                    $input_cls = $value;
                } else {
                    $attr_str .= ' '.$key.'=\''.$value.'\' ';
                }
            }
        }

        if( $disable ){
            $attr_class .= ' arf_disabled';
        }

        if( $enable_autocomplete ){
            $attr_class .= ' arf-has-autocomplete ';
        }

        $return_dom .= '<div class="arf_selectpicker_wrapper" style="'.$style.'">';

            $return_dom .= '<input type="'.( $enable_autocomplete ? 'hidden' : 'text' ).'" autocomplete="off" class="arf-selectpicker-input-control '.$input_cls.'" id="'.$id.'" name="'.$name.'" value="'.$default.'" '.$attr_str.'>';

            $return_dom .= '<dl class="arf-selectpicker-control '.$attr_class.'" data-id="'.$id.'" data-name="'.$name.'">';

                $return_dom .= '<dt>';

                    $return_dom .= '<span>';
                        if( true == $use_label_as_default ){
                            $return_dom .= '';
                        } else {
                            $return_dom .= $first_option_value;
                        }
                    $return_dom .='</span>';

                    if( $enable_autocomplete ){
                        $return_dom .= '<input type="text" class="arf-selectpicker-autocomplete">';
                    }
                    
                    $return_dom .= '<i class="arf-selectpicker-caret"></i>';
                
                $return_dom .= '</dt>';

                $return_dom .= '<dd>';
                        
                    $return_dom .= '<ul data-id="'.$id.'" id="'.$list_id.'" class="'.$list_class.'">';
                    if( !$is_form_field ){
                        if( !$grouped ){
                            if( !empty( $options ) ){
                                foreach( $options as $value => $label ){
                                    $cls_attr = "";
                                    if( !empty( $options_cls[$value] ) ){
                                        $cls_attr = $options_cls[$value];
                                    }

                                    $opts_attr = "";
                                    if( isset( $options_attr['data-type'][$value] ) ){
                                        $opts_attr = $options_attr['data-type'][$value];
                                        $opts_attr = ' data-type="'.$opts_attr.'"';
                                    }

                                    $option_condition = "";
                                    if( isset( $options_attr['data-field-in-condition'][$value] ) ){
                                        $option_condition = $options_attr['data-field-in-condition'][$value];
                                        $option_condition = ' data-field-in-condition="'.$option_condition.'"';
                                    }

                                    $opts_style = "";
                                    if( isset( $options_attr['style'][$value] ) ){
                                        $opts_style = $options_attr['style'][$value];
                                        $opts_style = ' style="'.$opts_style.'"';
                                    }

                                    $opts_val = "";
                                    if( isset( $options_attr['value'][$value] ) ){
                                        $opts_val = $options_attr['value'][$value];
                                        $opts_val = ' value="'.$opts_val.'"';
                                    }

                                    $opts_ids = "";
                                    if( isset( $options_attr['id'][$value] ) ){
                                        $opts_ids = $options_attr['id'][$value];
                                        $opts_ids = ' id="' . $opts_ids . '"';
                                    }

                                    if( !empty( $multi_sel_val ) ){
                                        if( $label == $value && in_array( $label, $multi_sel_val ) ){
                                            $cls_attr .= ' arm_sel_opt_checked ';
                                        }
                                    }

                                    $return_dom .= '<li class="'.$cls_attr.'" data-value="' . $value . '" data-label="'.htmlentities( $label ).'" ' . $opts_ids . $opts_attr . $option_condition . $opts_style . $opts_val .'>' . $label . '</li>';
                                }
                            }
                        } else {

                            foreach( $options as $k => $opt_arr ){
                                if( !empty( $k ) ){
                                    $extracted = explode('||', $k);
                                    $return_dom .= '<ol>' . $extracted[1] . '</ol>';
                                }
                                foreach( $opt_arr as $opt_val => $opt_label ){
                                    $cls_attr = "";
                                    if( !empty( $options_cls[$opt_val] ) ){
                                        $cls_attr = $options_cls[$opt_val];
                                    }

                                    $opts_attr = "";
                                    if( isset( $options_attr['data-type'][$opt_val] ) ){
                                        $opts_attr = $options_attr['data-type'][$opt_val];
                                        $opts_attr = ' data-type="'.$opts_attr.'"';
                                    }

                                    $option_condition = "";
                                    if( isset( $options_attr['data-field-in-condition'][$opt_val] ) ){
                                        $option_condition = $options_attr['data-field-in-condition'][$opt_val];
                                        $option_condition = ' data-field-in-condition="'.$option_condition.'"';
                                    }

                                    $opts_style = "";
                                    if( isset( $options_attr['style'][$opt_val] ) ){
                                        $opts_style = $options_attr['style'][$opt_val];
                                        $opts_style = ' style="'.$opts_style.'"';
                                    }

                                    $opts_val = "";
                                    if( isset( $options_attr['value'][$opt_val] ) ){
                                        $opts_val = $options_attr['value'][$opt_val];
                                        $opts_val = ' value="'.$opts_val.'"';
                                    }

                                    $return_dom .= '<li class="'.$cls_attr.'" data-value="' . $opt_val . '" data-label="'. htmlentities( $opt_label ) .'" '. $opts_attr . $option_condition . $opts_style . $opts_val .'>' . $opt_label . '</li>';
                                }
                            }
                        }
                    } else if( $is_form_field ){

                        if( !empty( $field['options'] ) ){
                            $count_i = 0;
                            foreach( $field['options'] as $opt_key => $opt ){
                                $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);
                                
                                $opt = apply_filters('show_field_label', $opt, $opt_key, $field);

                                if (is_array($opt)) {
                                    $opt = $opt['label'];
                                    if ($field_val['value'] == '(Blank)'){
                                        $field_val['value'] = "";
                                    }    
                                    $field_val = (isset($field['separate_value'])) ? $field_val['value'] : $opt;
                                }

                                if ($count_i == 0 and $field_val == ''){
                                    if( $is_mutliselect ){
                                        continue;
                                    }
                                    $opt = esc_html__('Please select', 'ARForms');
                                    if( $use_label_as_default ){
                                        $opt = !empty( $field['name'] ) ? $field['name'] : esc_html__( 'Choose Option', 'ARForms' ) ;
                                        $field['options'][$opt_key] = $opt;
                                    }
                                    $options_cls[$value] = 'arf_material_outline_sel_data_label';
                                } else {
                                    if( !empty( $options_cls[$value] ) && 'arf_material_outline_sel_data_label' == $options_cls[$value] ){
                                        $options_cls[$value] = '';
                                    }
                                }


                                $cls_attr = "";
                                if( !empty( $options_cls[$value] ) ){
                                    $cls_attr = $options_cls[$value];
                                }

                                if( $is_mutliselect && !empty( $multi_sel_val ) ) {
                                    if( $opt == $field_val && in_array( $opt, $multi_sel_val ) ){
                                        $cls_attr .= ' arm_sel_opt_checked ';
                                    }
                                }

                                $field['value'] = isset($field['value']) ? $field['value'] : "";
                                
                                $arfdefault_selected_val = (isset($field['separate_value'])) ? $field['default_value'] : $field['value'];
                                if( empty( $arfdefault_selected_val ) && !empty( $field['default_value'] ) ){
                                    $arfdefault_selected_val = $field['default_value'];
                                }
                                if (isset($field['set_field_value'])) {
                                    $arfdefault_selected_val = $field['set_field_value'];
                                }

                                if( $is_mutliselect && !empty( $multi_sel_val ) ) {
                                    if( $multi_sel_val == $arfdefault_selected_val && in_array( $field_val, $arfdefault_selected_val ) ){
                                        $cls_attr .= ' arm_sel_opt_checked ';
                                    }
                                }

                                $return_dom .= '<li class="'.$cls_attr.'" data-pos="'.$count_i.'" data-value="' . $field_val . '" data-label="'.htmlentities( $opt ).'">'.$opt.'</li>';
                                
                                $count_i++;
                            }
                        }
                    }
                    $return_dom .= '</ul>';

                $return_dom .= '</dd>';
            $return_dom .= '</dl>';

        $return_dom .= '</div>';

        return $return_dom;

    }
}
