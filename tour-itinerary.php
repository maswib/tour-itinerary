<?php
/**
 * Plugin Name: Tour Itinerary
 * Plugin URI: https://wahyuwibowo.com/projects/tour-itinerary/
 * Description: Helps you plan your tour itinerary.
 * Author: Wahyu Wibowo
 * Author URI: https://wahyuwibowo.com
 * Version: 1.0
 * Text Domain: tour-itinerary
 * Domain Path: languages
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Tour_Itinerary {
    
    private static $_instance = NULL;
    
    /**
     * Initialize all variables, filters and actions
     */
    public function __construct() {
        add_action( 'init',               array( $this, 'load_plugin_textdomain' ), 0 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'http_request_args',  array( $this, 'dont_update_plugin' ), 5, 2 );
        
        add_shortcode( 'tour_itinerary', array( $this, 'add_shortcode' ) );
    }
    
    /**
     * retrieve singleton class instance
     * @return instance reference to plugin
     */
    public static function instance() {
        if ( NULL === self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function load_plugin_textdomain() {
        $locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
        $locale = apply_filters( 'plugin_locale', $locale, 'tour-itinerary' );
        
        unload_textdomain( 'tour-itinerary' );
        load_textdomain( 'tour-itinerary', WP_LANG_DIR . '/tour-itinerary/tour-itinerary-' . $locale . '.mo' );
        load_plugin_textdomain( 'tour-itinerary', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
    }
    
    public function dont_update_plugin( $r, $url ) {
        if ( 0 !== strpos( $url, 'https://api.wordpress.org/plugins/update-check/1.1/' ) ) {
            return $r; // Not a plugin update request. Bail immediately.
        }
        
        $plugins = json_decode( $r['body']['plugins'], true );
        unset( $plugins['plugins'][plugin_basename( __FILE__ )] );
        $r['body']['plugins'] = json_encode( $plugins );
        
        return $r;
    }
    
    public function enqueue_scripts() {
        wp_register_script( 'ti-frontend', plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js', array( 'jquery' ), false, true );
        
        wp_localize_script( 'ti-frontend', 'Tour_Itinerary', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'tour_itinerary' ),
            'loading' => __( 'Loading...', 'tour-itinerary' )
        ) );
        
        wp_enqueue_style( 'ti-frontend', plugin_dir_url( __FILE__ ) . 'assets/css/frontend.css' );
    }
    
    public function add_shortcode() {
        wp_enqueue_script( 'ti-frontend' );
        
        $output = 'tour';
        
        return $output;
    }

}

Tour_Itinerary::instance();
