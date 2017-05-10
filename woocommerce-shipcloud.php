<?php
/**
 * Plugin Name: WooCommerce shipcloud.io
 * Plugin URI: http://www.woothemes.com/products/woocommerce-shipcloud/
 * Description: Integrates shipcloud.io shipment services to your WooCommerce shop.
 * Version: 1.1.2
 * Author: WooThemes
 * Author URI: http://woothemes.com/
 * Developer: awesome.ug
 * Developer URI: http://www.awesome.ug
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
 * WooCommerce Shipcloud initializing class
 * This class initializes the Plugin.
 *
 * @author  Awesome UG, Author <support@awesome.ug>
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
		require_once( WCSC_FOLDER . '/includes/shipcloud/i18n-iso-convert-class.php' );
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
		if( ! wcsc_is_frontend_screen() )
		{
			return;
		}
		wp_enqueue_style( 'wcsc-plugin-styles', WCSC_URLPATH . '/includes/css/display.css' );
	}

	/**
	 * Registers and enqueues plugin specific scripts.
	 *
	 * @since 1.0.0
	 */
	public function register_plugin_scripts()
	{
		if( ! wcsc_is_frontend_screen() )
		{
			return;
		}
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


add_filter( 'bulk_actions-edit-shop_order', function ( $actions ) {
	$actions['wcsc_order_bulk_label'] = __( 'Create shipping labels', 'woocommerce-shipcloud' );

	return $actions;
} );


add_filter( 'handle_bulk_actions-edit-shop_order', function ( $foo ) {
	return $foo;
} );

add_action( 'load-edit.php', function () {
	wp_register_script(
		'wcsc_bulk_order_label',
		WCSC_URLPATH . '/includes/js/bulk-order-label.js',
		array( 'jquery' )
	);

	wp_enqueue_script( 'wcsc_bulk_order_label', false, array(), false, true );
} );

add_action( 'admin_print_footer_scripts', function () {
	require_once WCSC_FOLDER . '/includes/shipcloud/block-order-labels-bulk.php';

	$block = new WooCommerce_Shipcloud_Block_Order_Labels_Bulk(
		WCSC_COMPONENTFOLDER . '/block/order-labels-bulk.php',
		wcsc_shipping_method()->get_allowed_carriers(),
		new Woocommerce_Shipcloud_API()
	);

	$block->dispatch();
} );

/**
 * Handle bulk action on orders.
 */
function _wcsc_order_bulk() {
	if ( ! is_admin() || ! get_current_screen() || 'edit-shop_order' != get_current_screen()->id ) {
		// None of our business.
		return;
	}

	$request = $_GET; // XSS: OK.

	if ( ! isset( $request['action'] ) || 'wcsc_order_bulk_label' !== $request['action'] ) {
		return;
	}

	if ( ! isset( $request['wcsc_carrier'] ) && ! $request['wcsc_carrier'] ) {
		return;
	}

	$package = array(
		'width'  => $request[ 'wcsc_width' ],
		'height' => $request[ 'wcsc_height' ],
		'length' => $request[ 'wcsc_length' ],
		'weight' => $request[ 'wcsc_weight' ],
	);

	foreach ( $request['post'] as $order_id ) {
		$order = WC_Shipcloud_Order::create_order($order_id);

		$reference_number = sprintf(
			__( 'Order %s', 'woocommerce-shipcloud' ),
			$order->get_wc_order()->get_order_number()
		);

		/**
		 * Filtering reference number
		 *
		 * @param string $reference_number The Reference Number
		 * @param string $order_number The WooCommerce order number
		 * @param string $order_id The WooCommerce order id
		 *
		 * @return string $reference_number The filtered order number
		 * @since 1.1.0
		 */
		$reference_number = apply_filters(
			'wcsc_reference_number',
			$reference_number,
			$order->get_wc_order()->get_order_number(),
			$order_id
		);

		$shipment = wcsc_api()->create_shipment(
			$request['wcsc_carrier'],
			$order->get_sender(),
			$order->get_recipient(),
			$package,
			true,
			$order->get_notification_email(),
			$order->get_carrier_mail(),
			$reference_number
		);

		if ( is_wp_error( $shipment ) )
		{
			$error_message = $shipment->get_error_message();
			WC_Shipcloud_Shipping::log( 'Order #' . $order->get_wc_order()->get_order_number() . ' - ' . $error_message .  ' (' . wcsc_get_carrier_display_name( $request[ 'carrier' ] ) . ')' );

			$errors[] = nl2br( $error_message );
		}

		WC_Shipcloud_Shipping::log( 'Order #' . $order->get_wc_order()->get_order_number() . ' - Created shipment successful (' . wcsc_get_carrier_display_name( $request[ 'carrier' ] ) . ')' );

		$parcel_title = wcsc_get_carrier_display_name( $request[ 'carrier' ] ) . ' - ' . $request[ 'width' ] . __( 'x', 'woocommerce-shipcloud' ) . $request[ 'height' ] . __( 'x', 'woocommerce-shipcloud' ) . $request[ 'length' ] . __( 'cm', 'woocommerce-shipcloud' ) . ' ' . $request[ 'weight' ] . __( 'kg', 'woocommerce-shipcloud' );

		$data = array(
			'id'                   => $shipment[ 'id' ],
			'carrier_tracking_no'  => $shipment[ 'carrier_tracking_no' ],
			'tracking_url'         => $shipment[ 'tracking_url' ],
			'label_url'            => $shipment[ 'label_url' ],
			'price'                => $shipment[ 'price' ],
			'parcel_id'            => $shipment[ 'id' ],
			'parcel_title'         => $parcel_title,
			'carrier'              => $request[ 'carrier' ],
			'width'                => $request[ 'width' ],
			'height'               => $request[ 'height' ],
			'length'               => $request[ 'length' ],
			'weight'               => $request[ 'weight' ],
			'description'          => $_POST[ 'description' ],
			'sender_first_name'    => $_POST[ 'sender_first_name' ],
			'sender_last_name'     => $_POST[ 'sender_last_name' ],
			'sender_company'       => $_POST[ 'sender_company' ],
			'sender_street'        => $_POST[ 'sender_street' ],
			'sender_street_no'     => $_POST[ 'sender_street_nr' ],
			'sender_zip_code'      => $_POST[ 'sender_postcode' ],
			'sender_city'          => $_POST[ 'sender_city' ],
			'sender_state'         => $_POST[ 'sender_state' ],
			'country'              => $_POST[ 'sender_country' ],
			'recipient_first_name' => $_POST[ 'recipient_first_name' ],
			'recipient_last_name'  => $_POST[ 'recipient_last_name' ],
			'recipient_company'    => $_POST[ 'recipient_company' ],
			'recipient_street'     => $_POST[ 'recipient_street' ],
			'recipient_street_no'  => $_POST[ 'recipient_street_nr' ],
			'recipient_zip_code'   => $_POST[ 'recipient_postcode' ],
			'recipient_city'       => $_POST[ 'recipient_city' ],
			'recipient_state'      => $_POST[ 'recipient_state' ],
			'recipient_country'    => $_POST[ 'recipient_country' ],
			'date_created'         => time()
		);

		add_post_meta( $order_id, 'shipcloud_shipment_ids', $data[ 'id' ] );
		add_post_meta( $order_id, 'shipcloud_shipment_data', $data );

		$order->get_wc_order()->add_order_note( __( 'shipcloud.io label was created.', 'woocommerce-shipcloud' ) );
	}
}

add_action( 'load-edit.php', '_wcsc_order_bulk' );

/**
 * @param WC_Order $order
 */
function woocommerce_shipcloud_create_shipment_data( $order, $carrier, $shipcloud_api = null, $shipping = null ) {
	if ( null === $shipcloud_api ) {
		$shipcloud_api = new Woocommerce_Shipcloud_API();
	}

	if (null === $shipping) {
		$shipping = new WC_Shipcloud_Shipping();
	}


	// Sender - defaults will be overridden by shipment address per order if given.
	$from = array(
		'first_name' => $shipping->get_option('sender_first_name'),
		'last_name'  => $shipping->get_option('sender_last_name'),
		'company'    => $shipping->get_option('sender_company'),
		'street'     => $shipping->get_option('sender_street'),
		'street_no'  => $shipping->get_option('sender_street_nr'),
		'zip_code'   => $shipping->get_option('sender_postcode'),
		'city'       => $shipping->get_option('sender_city'),
		'state'      => $shipping->get_option('sender_state'),
		'country'    => $shipping->get_option('sender_country'),
	);

	// Check if there are special settings for the order.
	$order_sender_address = get_post_meta( $order->id, 'shipcloud_sender_address', true );
	if ($order_sender_address) {
		$from = array_merge( $from, $order_sender_address );
	}

	// Recipient - will be overridden by custom data in shipcloud meta box.
	$to = array(
		'first_name' => $order->shipping_first_name,
		'last_name'  => $order->shipping_last_name,
		'company'    => $order->shipping_company,
		'street'     => $order->shipping_address_1,
		'street_no'  => $order->shipping_address_2,
		'care_of'    => null,
		'zip_code'   => $order->shipping_postcode,
		'city'       => $order->shipping_city,
		'state'      => $order->shipping_state,
		'country'    => $order->shipping_country,
		'email'      => $order->billing_email,
	);

	$order_recipient_address = get_post_meta($order->id, 'shipcloud_recipient_address', true);
	if ($order_recipient_address) {
		$to = array_merge( $to, $order_recipient_address );
	}

	$shipment = $shipcloud_api->create_shipment( $carrier, $from, $to, $package, $create_label, $notification_email, $carrier_email, $reference_number );

	$parcel_title = ''; // TODO wcsc_get_carrier_display_name( $_POST[ 'carrier' ] ) . ' - ' . $_POST[ 'width' ] . __( 'x', 'woocommerce-shipcloud' ) . $_POST[ 'height' ] . __( 'x', 'woocommerce-shipcloud' ) . $_POST[ 'length' ] . __( 'cm', 'woocommerce-shipcloud' ) . ' ' . $_POST[ 'weight' ] . __( 'kg', 'woocommerce-shipcloud' );

	$data = array(
		'id'                   => $shipment['id'],
		'carrier_tracking_no'  => $shipment['carrier_tracking_no'],
		'tracking_url'         => $shipment['tracking_url'],
		'label_url'            => $shipment['label_url'],
		'price'                => $shipment['price'],
		'parcel_id'            => $shipment['id'],

		'parcel_title'         => $parcel_title,

		'carrier'              => $_POST['carrier'],
		'width'                => $_POST['width'],
		'height'               => $_POST['height'],
		'length'               => $_POST['length'],
		'weight'               => $_POST['weight'],
		'description'          => $_POST['description'],

		'date_created'         => time()
	);

	// TODO $data = array_merge($data, $from);
	// TODO $data = array_merge($data, $to);

	update_post_meta( $order->id, 'shipcloud_shipment_ids', $data['id'] );
	update_post_meta( $order->id, 'shipcloud_shipment_data', $data );
}
