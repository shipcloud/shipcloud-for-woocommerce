<?php
/**
 * Plugin Name: shipcloud for WooCommerce
 * Plugin URI: http://www.woothemes.com/products/woocommerce-shipcloud/
 * Description: Integrates shipcloud shipment services to your WooCommerce shop.
 * Version: 1.3.1
 * Author: Awesome UG
 * Author URI: http://awesome.ug
 * Developer: Awesome UG
 * Developer URI: http://sven-wagener.com
 * Text Domain: shipcloud-for-woocommerce
 * License:
 * Copyright 2017 (support@awesome.ug)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 */

/**
 * shipcloud for WooCommerce initializing class
 * This class initializes the Plugin.
 *
 * @author  Awesome UG, Author <support@awesome.ug>
 * @package shipcloudForWooCommerce
 * @since   1.0.0
 * @license GPL 2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooCommerce_Shipcloud {
	/**
	 * The Single instance of the class
	 *
	 * @var object $_instance
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Notices for screening in Admin
	 *
	 * @var bool $passed_requirements
	 * @since 1.0.0
	 */
	private $passed_requirements = false;

	const VERSION = '1.2.1';

	/**
	 * Construct
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->load_plugin();
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin() {
		$this->constants();
		$this->load_textdomain();
		$this->includes();

		$this->check_requirements();
		$this->load_components();

		if ( is_admin() ) {
			add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ), 0 );
			add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
			add_action( 'admin_footer', array( $this, 'clear_admin_notices' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );
		}
	}

	/**
	 * Defining Constants for Use in Plugin
	 *
	 * @since 1.0.0
	 */
	private function constants() {
		define( 'WCSC_FOLDER', $this->get_folder() );
		define( 'WCSC_RELATIVE_FOLDER', substr( WCSC_FOLDER, strlen( WP_PLUGIN_DIR ), strlen( WCSC_FOLDER ) ) );
		define( 'WCSC_URLPATH', $this->get_url_path() );
		define( 'WCSC_COMPONENTFOLDER', WCSC_FOLDER . '/components' );
	}

	/**
	 * Getting Plugin Folder
	 *
	 * @since 1.0.0
	 */
	private function get_folder() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Getting Plugin URL
	 *
	 * @since 1.0.0
	 */
	private function get_url_path() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	private function load_textdomain()
	{
		load_plugin_textdomain( 'shipcloud-for-woocommerce', false, WCSC_RELATIVE_FOLDER . '/languages' );
	}

	/**
	 * Getting include files
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		// Loading functions
		require_once( WCSC_FOLDER . '/woocommerce-shipcloud-functions.php' );
		require_once( WCSC_FOLDER . '/includes/shipcloud/i18n-iso-convert-class.php' );
		require_once( WCSC_FOLDER . '/includes/shipcloud/shipcloud.php' );
	}

	/**
	 * Main Instance
	 *
	 * @return object
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Load PDF framework for merging.
	 *
	 * @see WC_Shipcloud_Order_Bulk::create_pdf()
	 */
	public static function load_fpdf() {
		require_once WCSC_FOLDER . '/vendor/setasign/fpdf/fpdf.php';

		require_once WCSC_FOLDER . '/vendor/setasign/fpdi/filters/FilterASCII85.php';
		require_once WCSC_FOLDER . '/vendor/setasign/fpdi/filters/FilterASCIIHexDecode.php';
		require_once WCSC_FOLDER . '/vendor/setasign/fpdi/filters/FilterLZW.php';
		require_once WCSC_FOLDER . '/vendor/setasign/fpdi/fpdi.php';

		require_once WCSC_FOLDER . '/vendor/iio/libmergepdf/src/Exception.php';
		require_once WCSC_FOLDER . '/vendor/iio/libmergepdf/src/Merger.php';
		require_once WCSC_FOLDER . '/vendor/iio/libmergepdf/src/Pages.php';
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 *
	 * @since 1.0.0
	 */
	public static function activate( $network_wide ) {
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 */
	public static function deactivate( $network_wide ) {
		delete_option( 'woocommerce_shipcloud_carriers' );
	}

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 *
	 * @since 1.0.0
	 */
	public static function uninstall( $network_wide ) {
	}

	/**
	 * Checking Requirements and adding Error Messages.
	 *
	 * @since 1.0.0
	 */
	public function check_requirements()
	{
		if ( ! class_exists( 'WooCommerce' ) )
		{
			self::admin_notice( __( 'WooCommerce is not installed. Please install before using plugin.', 'shipcloud-for-woocommerce' ), 'error' );

			return;
		}

		if ( ! function_exists( 'json_decode' ) )
		{
			self::admin_notice( __( 'shipcloud needs the JSON PHP extension.', 'shipcloud-for-woocommerce' ), 'error' );

			return;
		}

		if ( ! function_exists( 'mb_detect_encoding' ) )
		{
			self::admin_notice( __( 'shipcloud needs the Multibyte String PHP extension.', 'shipcloud-for-woocommerce' ), 'error' );

			return;
		}

		$this->passed_requirements = true;
	}

	/**
	 * Adds a notice to the admin
	 *
	 * @param string $message
	 * @param string $type
	 *
	 * @since 1.0.0
	 */
	public static function admin_notice( $message, $type = 'updated' ) {
		static::assert_session();

		$_SESSION['wcsc']['notices'][ md5( $type . ':' . $message ) ] = array(
			'message' => '<b>ShipCloud for WooCommerce</b>: ' . $message,
			'type'    => $type
		);
	}

	/**
	 * Assert that a session has been started.
	 *
	 * @since 1.2.0
	 */
	public static function assert_session() {
	    if (session_id()) {
	        return;
        }

        session_start();

		if ( ! isset( $_SESSION['wcsc'] ) || ! $_SESSION['wcsc'] ) {
			$_SESSION['wcsc'] = array();
		}

		$_SESSION['wcsc'] = array_merge(
			array(
				'notices'   => array(),
				'downloads' => array(),
			),
			$_SESSION['wcsc']
        );
	}

	/**
	 * Loading components
	 *
	 * @since 1.0.0
	 */
	public function load_components() {
		if ( ! $this->passed_requirements ) {
			return;
		}

		require_once( WCSC_FOLDER . '/components/component.php' );
		require_once( WCSC_FOLDER . '/components/core/core.php' );
		require_once( WCSC_FOLDER . '/components/woo/woo.php' );

		// Add autoloader for everything else.
		spl_autoload_register( array( $this, 'load_vendor' ) );
	}

	public function load_vendor( $class ) {
		$filename = WCSC_FOLDER
					. DIRECTORY_SEPARATOR . 'vendor'
					. DIRECTORY_SEPARATOR . str_replace( array( '\\' ), array( DIRECTORY_SEPARATOR ), $class ) . '.php';

		if ( file_exists( $filename ) ) {
			require_once $filename;
		}

		return class_exists( $class, false );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_styles() {
		if ( ! wcsc_is_admin_screen() ) {
			return;
		}

		wp_enqueue_style( 'wcsc-admin-styles', WCSC_URLPATH . 'includes/css/admin.css', array(), static::VERSION );
	}

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_scripts() {
		if ( ! wcsc_is_admin_screen() ) {
			return;
		}

		$translation_array = array(
			'parcel_added'                => __( 'Parcel template added!', 'shipcloud-for-woocommerce' ),
			'parcel_dimensions_check_yes' => __( 'Parcel dimensions verified!', 'shipcloud-for-woocommerce' ),
			'parcel_not_added'            => __( 'Parcel template not added!', 'shipcloud-for-woocommerce' ),
			'price_text'                  => __( 'The calculated price is', 'shipcloud-for-woocommerce' ),
			'select'                      => __( 'Select', 'shipcloud-for-woocommerce' ),
			'delete'                      => __( 'Delete', 'shipcloud-for-woocommerce' ),
			'kg'                          => __( 'kg', 'shipcloud-for-woocommerce' ),
			'cm'                          => __( 'cm', 'shipcloud-for-woocommerce' ),
			'yes'                         => __( 'Yes', 'shipcloud-for-woocommerce' ),
			'no'                          => __( 'No', 'shipcloud-for-woocommerce' )
		);

		wp_register_script( 'wcsc-admin-script', WCSC_URLPATH . 'includes/js/admin.js', array(), static::VERSION );
		wp_localize_script( 'wcsc-admin-script', 'wcsc_translate', $translation_array );
		wp_enqueue_script( 'wcsc-admin-script' );
	}

	/**
	 * Registers and enqueues plugin specific styles.
	 *
	 * @since 1.0.0
	 */
	public function register_plugin_styles() {
		if ( ! wcsc_is_frontend_screen() ) {
			return;
		}

		wp_enqueue_style( 'wcsc-plugin-styles', WCSC_URLPATH . '/includes/css/display.css', array(), static::VERSION );
	}

	/**
	 * Registers and enqueues plugin specific scripts.
	 *
	 * @since 1.0.0
	 */
	public function register_plugin_scripts() {
		if ( ! wcsc_is_frontend_screen() ) {
			return;
		}
		wp_enqueue_script( 'wcsc-plugin-script', WCSC_URLPATH . '/includes/js/display.js' );
	}

	/**
	 * Show Notices in Admin
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Uses session as collection of notices.
	 */
	public function show_admin_notices() {
		static::assert_session();

		foreach ( (array) $_SESSION['wcsc']['notices'] as $notice ) {
			echo '<div class="' . esc_attr( $notice['type'] ) . '"><p>' . $notice['message'] . '</p></div>';
		}

	}

	/**
	 * Reset all notices.
	 */
	public function clear_admin_notices() {
		static::assert_session();

		$_SESSION['wcsc']['notices'] = array();
	}

}

register_activation_hook( __FILE__, array( 'WooCommerce_Shipcloud', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WooCommerce_Shipcloud', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'WooCommerce_Shipcloud', 'uninstall' ) );

/**
 * Actionhook Function to load plugin
 *
 * @since 1.0.0
 */
function woocommerce_shipcloud_init() {
	WooCommerce_Shipcloud::instance();
}

add_action( 'plugins_loaded', 'woocommerce_shipcloud_init' );
