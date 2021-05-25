<?php
/**
 * Plugin Name: shipcloud for WooCommerce
 * Plugin URI: https://www.wordpress.org/plugins/shipcloud-for-woocommerce/
 * Description: Integrates shipcloud shipment services to your WooCommerce shop.
 * Version: 1.14.2
 * Author: shipcloud GmbH
 * Author URI: https://shipcloud.io
 * Developer: shipcloud GmbH
 * Developer URI: https://developers.shipcloud.io
 * Text Domain: shipcloud-for-woocommerce
 * WC requires at least: 2.6.4
 * WC tested up to: 3.9.2
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
 * @author  shipcloud GmbH, Author <developers@shipcloud.io>
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

	/**
	 * Indicate the version of the plugin.
	 *
	 * This will be appended to JS and CSS files
	 * for making them update on client site with each new plugin version.
	 *
	 * @since 1.2.1
	 */
	const VERSION = '1.14.2';

	const FILTER_GET_COD_ID = 'wcsc_get_cod_id';

	/**
	 * Construct
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->load_plugin();

		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}
		spl_autoload_register( array( $this, 'autoload' ) );
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
			add_action( 'admin_footer', array( $this, 'show_admin_notices' ) );
			add_action( 'admin_footer', array( $this, 'clear_admin_notices' ) );

            if ( wcsc_is_settings_screen() ) {
                add_action( 'admin_notices', array( $this, 'shipcloud_drop_wc2_support_notice') );
            }

		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );
			add_action( 'wp_footer', array( $this, 'shipcloud_inline_js' ) );
		}
	}

	/**
	 * Defining Constants for Use in Plugin
	 *
	 * @since 1.0.0
	 */
	private function constants() {
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
		require_once( WCSC_FOLDER . '/includes/shipcloud/block-labels-form.php' );
		require_once( WCSC_FOLDER . '/includes/shipcloud/controller/labelcontroller.php' );
	}

	/**
	 * Main Instance
	 *
	 * @return object
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( is_null( static::$_instance ) ) {
			static::$_instance = new static();
		}

		return static::$_instance;
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

	protected static function delete_transients() {
		delete_transient( '_wcsc_carriers_get' );
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                                 disabled or plugin is activated on an individual blog
	 */
	public static function deactivate( $network_wide ) {
		delete_option( 'woocommerce_shipcloud_carriers' );

		static::delete_transients();
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
    $shipcloud_notices = get_transient( 'shipcloud_notices' );

    if ( !isset($shipcloud_notices) ) {
      $shipcloud_notices = array();
    }

    $shipcloud_notices[ md5( $type . ':' . $message ) ] = array(
      'message' => '<b>shipcloud for WooCommerce</b>: ' . $message,
      'type'    => $type
    );

    set_transient( 'shipcloud_notices', $shipcloud_notices );
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
	}

	/**
	 * Auto-load shipcloud classes on demand to reduce memory consumption.
	 *
	 * @param string $class Class name.
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );
        $class = str_replace( '_', '-', $class );

		if ( 0 !== strpos( $class, 'shipcloud' ) ) {
			return;
		}

		$file = WCSC_FOLDER
					. 'includes'
					. DIRECTORY_SEPARATOR
                    . str_replace( array( '\\' ), array( DIRECTORY_SEPARATOR ), $class )
                    . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
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
      'no'                          => __( 'No', 'shipcloud-for-woocommerce' ),
      'force_delete_dialog'         => __( 'Do you want to delete this shipment from the WooCommerce database nonetheless?', 'shipcloud-for-woocommerce' )
		);

		wp_register_script(
			'wcsc-admin-script',
			WCSC_URLPATH . 'includes/js/admin.js',
			array( 'wp-util' ),
			static::VERSION
		);

		wp_register_script(
			'wcsc-multi-select',
			WCSC_URLPATH . 'includes/js/multi-select.js',
			array( 'jquery' ),
			static::VERSION
		);

    $package_types = array(
        'placeholder' => _x( 'Select type', 'backend: Selecting a package type option for label creation', 'wcsc' ),
    );
    $package_types = array_merge(
        $package_types,
        wcsc_api()->get_package_types()
    );
    // $additional_services = wcsc_api()->get_additional_services();

    $carriers_config = wcsc_shipping_method()->get_allowed_carrier_classes();
    $all_additional_services = array();
    foreach ( $carriers_config as $carrier ) {
      WC_Shipcloud_Shipping::log( 'carrier: '.json_encode($carrier) );
      array_push($all_additional_services, $carrier->getAdditionalServices());
    }

    $all_additional_services_values = array_values($all_additional_services);
    WC_Shipcloud_Shipping::log( 'count(all_additional_services_values): '.count($all_additional_services_values) );
    if( count($all_additional_services_values) > 0) {
      WC_Shipcloud_Shipping::log( 'all_additional_services_values length > 0' );
      $all_additional_services = array_merge(...$all_additional_services_values);
      $all_additional_services = array_unique($all_additional_services);
    } else {
      WC_Shipcloud_Shipping::log( 'all_additional_services_values length == 0' );
      WC_Shipcloud_Shipping::log( 'all_additional_services_values: '.json_encode($all_additional_services_values) );
    }

    // Inject translations and data for carrier selection.
    $services = array(
      'placeholder' => _x( 'Select service', 'backend: Selecting a carrier service option for label creation', 'wcsc' )
    );
    $services = array_merge(
      $services,
      array_map(
        function($service) {
          return $service['name'];
        },
        wcsc_api()->get_services()
      )
    );

    wp_localize_script(
      'wcsc-multi-select',
      'wcsc_carrier',
      array(
        'label' => array(
          'carrier' => array(
            'placeholder' => _x( 'Select carrier', 'backend: Selecting a carrier option for label creation', 'wcsc' ),
          ),
          'package_types' => $package_types,
          'services' => $services,
          'label_formats' => wcsc_api()->get_label_format_display_names()
        ),
        'additional_services' => array_values($all_additional_services),
        'data' => $carriers_config,
      )
    );

    wp_localize_script(
      'wcsc-multi-select',
      'shipcloud_pickup_carriers',
      array(
        'carriers_with_pickup_object' => WC_Shipcloud_Order::PICKUP_IN_SHIPMENT_CARRIERS,
        'carriers_with_pickup_request' => WC_Shipcloud_Order::PICKUP_CARRIERS
      )
    );

		wp_register_script(
			'shipcloud-label',
			WCSC_URLPATH . 'includes/js/shipcloud-label.js',
			array( 'jquery' ),
			static::VERSION
		);

		wp_register_script(
			'shipcloud-label-form',
			WCSC_URLPATH . 'includes/js/shipcloud-label-form.js',
			array( 'jquery' ),
			static::VERSION
		);

		wp_register_script(
			'shipcloud-filler',
			WCSC_URLPATH . 'includes/js/shipcloud-filler.js',
			array( 'jquery' ),
			static::VERSION
		);

		wp_register_script(
			'shipcloud-shipments',
			WCSC_URLPATH . 'includes/js/shipcloud-shipments.js',
			array( 'jquery', 'jquery-serialize-object', 'wp-backbone' ),
			static::VERSION
		);

		wp_localize_script( 'wcsc-admin-script', 'wcsc_translate', $translation_array );
		wp_localize_script( 'wcsc-admin-script', 'shipcloud_customs_declaration_contents_types', wcsc_api()->get_customs_declaration_contents_types() );
		wp_enqueue_script( 'wcsc-admin-script' );

		wp_enqueue_script( 'wcsc-font-awesome', WCSC_URLPATH . 'vendor/fontawesome/js/all.js', array( ), static::VERSION );
		wp_enqueue_script( 'shipcloud-jquery-serialize-json', WCSC_URLPATH . 'vendor/jquery.serializeJSON-2.9.0/jquery.serializejson.min.js', array( ), static::VERSION );

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
		wp_enqueue_script( 'wcsc-plugin-script', WCSC_URLPATH . '/includes/js/display.js', array( 'wp-util' ), static::VERSION );
		wp_enqueue_script( 'wcsc-font-awesome', WCSC_URLPATH . 'vendor/fontawesome/js/fontawesome-all.js', array( ), static::VERSION );
	}

	public function shipcloud_inline_js() {
		echo '<script>';
		echo 'ajax_url = "'.admin_url('admin-ajax.php').'";';
		echo '</script>';
	}
	/**
	 * Show Notices in Admin
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Uses session as collection of notices.
   * @since 1.14.0 remove sessions and handle as transient
	 */
	public static function show_admin_notices() {
    $shipcloud_notices = get_transient( 'shipcloud_notices' );
    if ( !isset($shipcloud_notices) ) {
      $shipcloud_notices = array();
    }

    foreach ( $shipcloud_notices as $notice ) {
      echo '<div class="' . esc_attr( $notice['type'] ) . '"><p>' . $notice['message'] . '</p></div>';
    }
	}

	/**
	 * Reset all notices.
	 */
	public static function clear_admin_notices() {
    set_transient( 'shipcloud_notices', array() );
	}

    function shipcloud_drop_wc2_support_notice() {
    ?>
        <div class="shipcloud-panel">
            <div class="shipcloud-panel-content shipcloud-panel--alert">
                <h2>
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php _e( 'WooCommerce 2 support end of life', 'shipcloud-for-woocommerce'); ?>
                </h2>
                <p>
                    <?php _e( 'We will be dropping the WooCommerce 2 support with the upcoming', 'shipcloud-for-woocommerce'); ?>
                    <strong>
                        <?php _e( 'release 2.0.0', 'shipcloud-for-woocommerce'); ?>
                    </strong>
                </p>
            </div>
        </div>
    <?php
    }
}

register_activation_hook( __FILE__, array( 'WooCommerce_Shipcloud', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WooCommerce_Shipcloud', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'WooCommerce_Shipcloud', 'uninstall' ) );

define( 'WCSC_FOLDER', plugin_dir_path( __FILE__ ) );

require_once __DIR__ . '/components/service-container.php';

/**
 * @return \Shipcloud\ServiceContainer
 */
function _wcsc_container() {
	return \Shipcloud\container();
}

require_once __DIR__ . '/components/compatibility.php';


/**
 * Actionhook Function to load plugin
 *
 * @since 1.0.0
 */
function woocommerce_shipcloud_init() {
	WooCommerce_Shipcloud::instance();
}

add_action( 'plugins_loaded', 'woocommerce_shipcloud_init' );

/**
 * handle plugin action links
 *
 * @since 1.6.0
 *
 * @return string
 */
function wcsc_action_links( $links ) {
	$action_links = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipcloud' ) . '" aria-label="' . esc_attr__( 'Settings', 'shipcloud-for-woocommerce' ) . '">' . esc_html__( 'Settings', 'shipcloud-for-woocommerce' ) . '</a>',
	);
	return array_merge( $links, $action_links );
}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wcsc_action_links' );

function load_admin_js(){
  add_action( 'admin_enqueue_scripts', 'enqueue_shipcloud_settings_scripts' );
}

function enqueue_shipcloud_settings_scripts($hook) {

  if ( 'toplevel_page_shipcloud_for_woocommerce' != $hook ) {
      return;
  }

  wp_enqueue_script( 'admin.js', WCSC_URLPATH . 'includes/js/admin.js', array( 'wp-util') );
}
