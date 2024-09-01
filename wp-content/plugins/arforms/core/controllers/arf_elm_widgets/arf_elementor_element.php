<?php
namespace ElementorARFELEMENT;

class elementor_arf_element{

   
   private static $_instance = null;

   
   public function __construct() {

      // Register widget scripts
      add_action( 'elementor/frontend/after_register_scripts',array($this, 'widget_scripts'));
      
      // Register widgets
      add_action( 'elementor/widgets/widgets_registered',array($this, 'register_widgets'));

      
   }
    
   public static function instance() {
      if ( is_null( self::$_instance ) ) {
         self::$_instance = new self();
      }
      return self::$_instance;
   }

   
   public function widget_scripts() {
      global $arfversion;
      wp_register_script('elementor-arf-element', ARFURL . '/js/arf-element.js', array('jquery'), $arfversion, true);
   }

   private function include_widgets_files() {
      require_once( __DIR__ . '/arf_element_add.php' );
   }

   
   public function register_widgets() {
      // Its is now safe to include Widgets files
      $this->include_widgets_files();

      // Register Widgets
      \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\arf_element_shortcode() );
   }

}

// Instantiate Plugin Class
elementor_arf_element::instance();