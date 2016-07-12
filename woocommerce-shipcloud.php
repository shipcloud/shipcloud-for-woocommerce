<?php
/**
 * Plugin Name: WooCommerce shipcloud.io
 * Plugin URI: http://www.woothemes.com/products/woocommerce-shipcloud/
 * Description: Integrates shipcloud.io shipment services to your WooCommerce shop.
 * Version: 1.0.2
 * Author: WooThemes
 * Author URI: http://woothemes.com/
 * Developer: awesome.ug
 * Developer URI: http://www.awesome.ug
 * License:
 * Copyright 2016 (support@awesome.ug)
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
 * WooCommerce Shipcloud initializing class
 * This class initializes the Plugin.
 *
 * @author  rheinschmiede.de, Author <support@awesome.ug>
 * @package WooCommerceShipCloud
 * @since   1.0.0
 * @license GPL 2
 */

if ( ! defined( 'ABSPATH' ) )
{
	exit;
}

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '99377680d6954f5c19a76538a369fc7e', '1503949' );

class WooCommerce_Shipcloud
{
	/**
	 * Notices for screening in Admin
	 *
	 * @var array $notices
	 * @since 1.0.0
	 */
	static $notices = array();

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

	/**
	 * Construct
	 *
	 * @since 1.0.0
	 */
	private function __construct()
	{
		$this->load_plugin();
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin()
	{
		$this->constants();
		$this->load_textdomain();
        $this->includes();

        $this->check_requirements();
        $this->load_components();

		if ( is_admin() )
		{
			add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ), 0 );
			add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
		}
		else
		{
			add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );
		}
	}

	/**
	 * Defining Constants for Use in Plugin
	 *
	 * @since 1.0.0
	 */
	private function constants()
	{
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
	private function get_folder()
	{
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Getting Plugin URL
	 *
	 * @since 1.0.0
	 */
	private function get_url_path()
	{
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	private function load_textdomain()
	{
		load_plugin_textdomain( 'woocommerce-shipcloud', false, WCSC_RELATIVE_FOLDER . '/languages' );
	}

	/**
	 * Getting include files
	 *
	 * @since 1.0.0
	 */
	private function includes()
	{
		// Loading functions
		require_once( WCSC_FOLDER . '/woocommerce-shipcloud-functions.php' );
		require_once( WCSC_FOLDER . '/includes/shipcloud/shipcloud.php' );
	}

	/**
	 * Main Instance
	 *
	 * @return object
	 * @since 1.0.0
	 */
	public static function instance()
	{
		if ( is_null( self::$_instance ) )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 *
	 * @since 1.0.0
	 */
	public static function activate( $network_wide )
	{
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 */
	public static function deactivate( $network_wide )
	{
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
	public static function uninstall( $network_wide )
	{
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
			self::admin_notice( __( 'WooCommerce is not installed. Please install before using Plugin.', 'woocommerce-shipcloud' ), 'error' );

			return;
		}

		if ( ! function_exists( 'json_decode' ) )
		{
			self::admin_notice( __( 'shipcloud.io needs the JSON PHP extension.', 'woocommerce-shipcloud' ), 'error' );

			return;
		}

		if ( ! function_exists( 'mb_detect_encoding' ) )
		{
			self::admin_notice( __( 'shipcloud.io needs the Multibyte String PHP extension.', 'woocommerce-shipcloud' ), 'error' );

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
	public static function admin_notice( $message, $type = 'updated' )
	{
		self::$notices[] = array(
			'message' => '<b>ShipCloud for WooCommerce</b>: ' . $message,
			'type'    => $type
		);
	}

	/**
	 * Loading components
	 *
	 * @since 1.0.0
	 */
	public function load_components()
	{
		if ( ! $this->passed_requirements )
		{
			return;
		}

		require_once( WCSC_FOLDER . '/components/component.php' );
		require_once( WCSC_FOLDER . '/components/core/core.php' );
		require_once( WCSC_FOLDER . '/components/woo/woo.php' );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_styles()
	{
		if( ! wcsc_is_admin_screen() )
		{
			return;
		}
		wp_enqueue_style( 'wcsc-admin-styles', WCSC_URLPATH . 'includes/css/admin.css' );
	}

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_scripts()
	{
		if( ! wcsc_is_admin_screen() )
		{
			return;
		}

		$translation_array = array(
			'parcel_added'                => __( 'Parcel template added!', 'woocommerce-shipcloud' ),
			'parcel_dimensions_check_yes' => __( 'Parcel dimensions verified!', 'woocommerce-shipcloud' ),
			'parcel_not_added'            => __( 'Parcel template not added!', 'woocommerce-shipcloud' ),
			'price_text'                  => __( 'The calculated price is', 'woocommerce-shipcloud' ),
			'select'                      => __( 'Select', 'woocommerce-shipcloud' ),
			'delete'                      => __( 'Delete', 'woocommerce-shipcloud' ),
			'kg'                          => __( 'kg', 'woocommerce-shipcloud' ),
			'cm'                          => __( 'cm', 'woocommerce-shipcloud' ),
			'yes'                         => __( 'Yes', 'woocommerce-shipcloud' ),
			'no'                          => __( 'No', 'woocommerce-shipcloud' )
		);

		wp_register_script( 'wcsc-admin-script', WCSC_URLPATH . 'includes/js/admin.js' );
		wp_localize_script( 'wcsc-admin-script', 'wcsc_translate', $translation_array );
		wp_enqueue_script( 'wcsc-admin-script' );
	}

	/**
	 * Registers and enqueues plugin specific styles.
	 *
	 * @since 1.0.0
	 */
	public function register_plugin_styles()
	{
		wp_enqueue_style( 'wcsc-plugin-styles', WCSC_URLPATH . '/includes/css/display.css' );
	}

	/**
	 * Registers and enqueues plugin specific scripts.
	 *
	 * @since 1.0.0
	 */
	public function register_plugin_scripts()
	{
		wp_enqueue_script( 'wcsc-plugin-script', WCSC_URLPATH . '/includes/js/display.js' );
	}

	/**
	 * Show Notices in Admin
	 *
	 * @since 1.0.0
	 */
	public function show_admin_notices()
	{
		if ( is_array( self::$notices ) && count( self::$notices ) > 0 )
		{
			$html = '';
			foreach ( self::$notices AS $notice )
			{
				$message = $notice[ 'message' ];
				$html .= '<div class="' . $notice[ 'type' ] . '"><p>' . $message . '</p></div>';
			}
			echo $html;
		}
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
function woocommerce_shipcloud_init()
{
	WooCommerce_Shipcloud::instance();
}

add_action( 'plugins_loaded', 'woocommerce_shipcloud_init' );
