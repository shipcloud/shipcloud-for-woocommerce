<?php
/*
Plugin Name: WooCommerce Shipcloud
Plugin URI: http://www.awesome.ug
Description: Integrates shipcloud.io shipment services to your WooCommerce shop.
Version: 1.0.0
Author: awesome.ug
Author URI: http://www.awesome.ug
Author Email: support@awesome.ug
License:

  Copyright 2015 (support@awesome.ug)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

/*
 * WooCommerce Shipcloud initializing class
 *
 * This class initializes the Plugin.
 *
 * @author rheinschmiede.de, Author <contact@awesome.ug>
 * @package PluginName
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2
 */

if ( !defined( 'ABSPATH' ) ) exit;
 
class WooCommerceShipcloud{
	 
	/**
	 * Initializes the plugin.
	 * @since 1.0.0
	 */
	public static function init() {
		global $wcsc_errors, $wcsc_notices, $wcsc_passed_requirements;
        
        $wcsc_errors = array();
        $wcsc_notices = array();
		$wcsc_passed_requirements = FALSE;
        
        self::constants();
        self::includes();
        self::load_components();
        self::load_textdomain();
        
        // Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
        register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
        register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
        register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );

        // Functions on Frontend
        if( is_admin() ):
            // Register admin styles and scripts
            add_action( 'plugins_loaded', array( __CLASS__, 'check_requirements' ), 1 );
            add_action( 'admin_print_styles', array( __CLASS__, 'register_admin_styles' ) );
            add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_scripts' ) );
            add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
        else:
            // Register plugin styles and scripts
            add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_plugin_styles' ) );
            add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_plugin_scripts' ) );
        endif;
	} // end constructor
	
	/**
	 * Checking Requirements and adding Error Messages.
	 * @since 1.0.0 
	 */
	public static function check_requirements(){
		global $wcsc_errors, $wcsc_passed_requirements;
		
		$wcsc_passed_requirements = TRUE;
		
		if( !class_exists( 'WooCommerce' ) ):
			$wcsc_errors[] = __( 'WooCommerce is not installed. Please install before using Plugin.', 'wcsc-locale' );
			$wcsc_passed_requirements = FALSE;
		endif;
		
		if ( !function_exists( 'curl_init' ) ):
		    $wcsc_errors[] = __( 'Shipcloud needs the CURL PHP extension.', 'wcsc-locale' );
			$wcsc_passed_requirements = FALSE;
		endif;
		
		if ( !function_exists( 'json_decode' ) ):
		    $wcsc_errors[] = __( 'Shipcloud needs the JSON PHP extension.', 'wcsc-locale' );
			$wcsc_passed_requirements = FALSE;
		endif;

		if ( !function_exists( 'mb_detect_encoding' ) ):
		    $wcsc_errors[] = __( 'Shipcloud needs the Multibyte String PHP extension.', 'wcsc-locale' );
			$wcsc_passed_requirements = FALSE;
		endif;
		
	}
	
	/**
	 * Fired when the plugin is activated.
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0 
	 */
	public static function activate( $network_wide ) {
	} // end activate
	
	/**
	 * Fired when the plugin is deactivated.
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public static function deactivate( $network_wide ) {
	} // end deactivate
	
	/**
	 * Fired when the plugin is uninstalled.
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since 1.0.0 
	 */
	public static function uninstall( $network_wide ) {
	} // end uninstall

	/**
	 * Loads the plugin text domain for translation.
	 * @since 1.0.0
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'wcsc-locale', false, WCSC_RELATIVE_FOLDER . '/languages' );
		
	} // end plugin_textdomain

	/**
	 * Registers and enqueues admin-specific styles.
	 * @since 1.0.0
	 */
	public static function register_admin_styles() {
		wp_enqueue_style( 'wcsc-admin-styles', WCSC_URLPATH . '/includes/css/admin.css' );
	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 * @since 1.0.0
	 */	
	public static function register_admin_scripts() {
		$translation_array = array(
			'parcel_added' =>  __( 'Parcel template added!', 'wcsc-locale' ),
			'parcel_not_added' =>  __( 'Parcel template not added!', 'wcsc-locale' ),
			'price_text' => __( 'The calculated price is', 'wcsc-locale' ),
			'kg' => __( 'kg', 'wcsc-locale' ),
			'kg' => __( 'kg', 'wcsc-locale' ),
			'cm' => __( 'cm', 'wcsc-locale' )
		);	
		
		wp_register_script( 'wcsc-admin-script', WCSC_URLPATH . '/includes/js/admin.js' );
		wp_localize_script( 'wcsc-admin-script', 'wcsc_translate', $translation_array );
		wp_enqueue_script( 'wcsc-admin-script' );
	} // end register_admin_scripts
	
	/**
	 * Registers and enqueues plugin-specific styles.
	 * @since 1.0.0
	 */
	public static function register_plugin_styles() {
		wp_enqueue_style( 'wcsc-plugin-styles', WCSC_URLPATH . '/includes/css/display.css' );
	} // end register_plugin_styles
	
	/**
	 * Registers and enqueues plugin-specific scripts.
	 * @since 1.0.0
	 */
	public static function register_plugin_scripts() {
		wp_enqueue_script( 'wcsc-plugin-script',  WCSC_URLPATH . '/includes/js/display.js' );
	} // end register_plugin_scripts
	
	/**
	 * Defining Constants for Use in Plugin
	 * @since 1.0.0
	 */
	public static function constants(){
		define( 'WCSC_FOLDER', self::get_folder() );
		define( 'WCSC_RELATIVE_FOLDER', substr( WCSC_FOLDER, strlen( WP_PLUGIN_DIR ), strlen( WCSC_FOLDER ) ) );  
		define( 'WCSC_URLPATH', self::get_url_path() );
		define( 'WCSC_COMPONENTFOLDER', WCSC_FOLDER . '/components' );
	}
	
	/**
	 * Getting include files
	 * @since 1.0.0
	 */
	public static function includes(){
		// Loading functions
		include( WCSC_FOLDER . '/functions.php' );
		include( WCSC_FOLDER . '/includes/shipcloud/shipcloud.php' );
	}

	/**
	 * Loading components dynamical
	 * @since 1.0.0
	 */
	public static function load_components(){
		// Loading Components
		include( WCSC_FOLDER . '/components/component.php' );
		include( WCSC_FOLDER . '/components/admin/admin.php' );
		include( WCSC_FOLDER . '/components/woo/woo.php' );
	}
	
	/**
	* Getting URL
	* @since 1.0.0
	*/
	private static function get_url_path(){
		$sub_path = substr( WCSC_FOLDER, strlen( ABSPATH ), ( strlen( WCSC_FOLDER ) - 11 ) );
		$script_url = get_bloginfo( 'wpurl' ) . '/' . $sub_path;
		return $script_url;
	}
	
	/**
	* Getting Folder
	* @since 1.0.0
	*/
	private static function get_folder(){
		return plugin_dir_path( __FILE__ );
	}
	
	/**
	* Showing Errors
	* @since 1.0.0
	*/
	public static function admin_notices(){
		global $wcsc_errors, $wcsc_notices; 
		
		if( count( $wcsc_errors ) > 0 ):
				foreach( $wcsc_errors AS $error )
					echo '<div class="error"><p>' . $error . '</p></div>';
		endif;
		
		if( count( $wcsc_notices ) > 0 ):
				foreach( $wcsc_notices AS $notice )
					echo '<div class="updated"><p>' . $notice . '</p></div>';
		endif;	
	} 
	
} // end class

WooCommerceShipcloud::init();