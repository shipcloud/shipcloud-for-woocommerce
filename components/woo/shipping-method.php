<?php
/**
 * WooCommerce Shipping method
 * Class which extends the WC_Shipping_Method API
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package shipcloudForWooCommerce/Woo
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2017 (support@awesome.ug)
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) )
{
	exit;
}

class WC_Shipcloud_Shipping extends WC_Shipping_Method
{
	/**
	 * Logger
	 *
	 * @var WC_Logger $logger
	 * @since 1.0.0
	 * @todo  Do we need it really here? Have to move to a static function in WooCommerce_Shipcloud Class
	 */
	private static $logger;

	/**
	 * Callback URL
	 *
	 * @var string $callback_url
	 * @since 1.0.0
	 */
	private $callback_url;

	/**
	 * Loaded Carriers
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private $carriers = array();

	/**
	 * Available Carriers
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private $available_carriers = array();

	/**
	 * Shipcloud API Object
	 *
	 * @var Woocommerce_Shipcloud_API
	 * @since 1.1.0
	 */
	private $shipcloud_api;

	/**
	 * Sender data
	 *
	 * @var array
	 * @since 1.1.0
	 */
	private $sender = array();

	/**
	 * Recipient data
	 *
	 * @var array
	 * @since 1.1.0
	 */
	private $recipient = array();

	/**
	 * All Parcels which have been requested at shipcloud for later usage
	 *
	 * @var array
	 * @since 1.1.0
	 */
	private $calculated_parcels = array();

	/**
	 * Constructor for your shipping class
	 *
	 * @since 1.0.0
	 */
	public function __construct( $instance_id = 0 )
	{
		$this->id                 = 'shipcloud';
		$this->instance_id 		  = absint( $instance_id );
		$this->title              = __( 'shipcloud', 'shipcloud-for-woocommerce' );
		$this->method_title       = $this->title;
		$this->method_description = __( 'Add shipcloud to your shipping methods', 'shipcloud-for-woocommerce' );
		$this->callback_url       = WC()->api_request_url( 'shipcloud' );

		$this->supports              = array(
			'settings',
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->enabled = $this->get_option( 'enabled' );

		$this->start();
	}

	/**
	 * Initialize Shipcloud API
	 *
	 * @since 1.1.0
	 */
	private function init_shipcloud_api( $api_key = null ){
		if( is_object( $this->shipcloud_api ) ) {
			return true;
		}

		if( empty( $api_key ) ) {
			$api_key = $this->get_option( 'api_key' );
		}

		// Initializing
		$this->shipcloud_api = new Woocommerce_Shipcloud_API( $api_key );

		if( is_wp_error( $this->shipcloud_api ) ) {
			self::log( $this->shipcloud_api->get_error_message() );
			return $this->shipcloud_api;
		}

		return true;
	}

	/**
	 * Init your settings
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function start()
	{
		$this->init();
		$this->init_settings_fields();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        add_filter( 'woocommerce_shipping_chosen_method', array( $this, 'set_standard_carrier' ) );
	}

	/**
	 * Checking Settings and setup Errors
	 *
	 * @since 1.0.0
	 */
	public function init()
	{
		$api_key = $this->get_option( 'api_key' );

		if ( ( empty( $api_key ) && ! isset( $_POST[ 'woocommerce_shipcloud_api_key' ] ) ) || ( isset( $_POST[ 'woocommerce_shipcloud_api_key' ] ) && '' == $_POST[ 'woocommerce_shipcloud_api_key' ] ) )
		{
			WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Please enter a <a href="%s">shipcloud api key</a>.', 'shipcloud-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' ) ), 'error' );

			return false;
		}

		// If Gateway is disabled just return true for passing further error meessages
		if ( ( 'no' === $this->get_option( 'enabled' ) && ! isset( $_POST[ 'woocommerce_shipcloud_enabled' ] ) ) || ( isset( $_POST[ 'woocommerce_shipcloud_api_key' ] ) && ! isset( $_POST[ 'woocommerce_shipcloud_enabled' ] ) ) )
		{
			return true;
		}

		// Testing Connection on Settings Page
		if ( wcsc_is_settings_screen() )
		{
			$api_key = '';
			if ( isset( $_POST[ 'woocommerce_shipcloud_api_key' ] ) )
			{
				$api_key = $_POST[ 'woocommerce_shipcloud_api_key' ];
			}

			$init_shipcloud_api = $this->init_shipcloud_api( $api_key );
			if( is_wp_error( $init_shipcloud_api ) ) {
				self::log( 'Could not initialize shipcloud api - ' . $init_shipcloud_api->get_error_message() );
				WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Could not initialize shipcloud api.', 'shipcloud-for-woocommerce' ), $init_shipcloud_api->get_error_message() ), 'error' );
				return false;
		    }

			$carriers = $this->shipcloud_api->get_carriers();
			if ( is_wp_error( $carriers ) )
			{
				self::log( 'Could not update carriers - ' . $carriers->get_error_message() );
				WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Could not update carriers: %s', 'shipcloud-for-woocommerce' ), $carriers->get_error_message() ), 'error' );
				return false;
			}
			$this->carriers = $carriers;

			$available_carriers = $this->get_allowed_carriers();
			if ( is_wp_error( $available_carriers ) )
			{
				self::log( 'Could not get available carriers - ' . $available_carriers->get_error_message() );
				WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Could not get available carriers: %s', 'shipcloud-for-woocommerce' ), $available_carriers->get_error_message() ), 'error' );
				return false;
			}
			$this->available_carriers = $available_carriers;
		}

		$allowed_carriers = $this->get_option( 'allowed_carriers' );
		if ( empty( $allowed_carriers ) && ! isset( $_POST[ 'woocommerce_shipcloud_allowed_carriers' ] ) || ( isset( $_POST[ 'woocommerce_shipcloud_api_key' ] ) && ! isset( $_POST[ 'woocommerce_shipcloud_allowed_carriers' ] ) ) )
		{
			WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Please select at least one allowed <a href="%s">shipping method</a>.', 'shipcloud-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' ) ), 'error' );
			return false;
		}

		if ( ( '' == $this->get_option( 'standard_price_products' ) && ! isset( $_POST[ 'woocommerce_shipcloud_standard_price_products' ] ) ) || ( isset( $_POST[ 'woocommerce_shipcloud_standard_price_products' ] ) && '' == $_POST[ 'woocommerce_shipcloud_standard_price_products' ] ) )
		{
			WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Please enter a <a href="%s">standard price</a> for products.', 'shipcloud-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' ) ), 'error' );
			return false;
		}

		if ( ( '' == $this->get_option( 'standard_price_shipment_classes' ) && ! isset( $_POST[ 'woocommerce_shipcloud_standard_price_shipment_classes' ] ) ) || ( isset( $_POST[ 'woocommerce_shipcloud_standard_price_shipment_classes' ] ) && '' == $_POST[ 'woocommerce_shipcloud_standard_price_shipment_classes' ] ) )
		{
			WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Please enter a <a href="%s">standard price</a> for shipping classes.', 'shipcloud-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' ) ), 'error' );
			return false;
		}

		return true;
	}

	/**
	 * Gateway settings
	 *
	 * @since 1.0.0
	 */
	public function init_settings_fields()
	{
		global $woocommerce;

		$default_country = wc_get_base_location();
		$default_country = $default_country[ 'country' ];

		$carriers_options = array();
		foreach ( $this->carriers as $carrier )
		{
			$carriers_options[ $carrier[ 'name' ] ] = $carrier[ 'display_name' ];
		}

		if ( count( $this->available_carriers ) > 0 )
		{
			$standard_carrier_settings = array(
				'title'       => __( 'Standard shipping method', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'This carrier will be preselected if the shop owner selects the carrier or will be preselected as carrier if customer can select the carrier.', 'shipcloud-for-woocommerce' ),
				'options'     => $this->available_carriers,
				'desc_tip'    => true
			);
		}
		else
		{
			$standard_carrier_settings = array(
				'title'       => __( 'Standard shipping method', 'shipcloud-for-woocommerce' ),
				'type'        => 'text_only',
				'description' => __( 'You have to select at least one carrier above to select a standard carrier.', 'shipcloud-for-woocommerce' ),
			);
		}

		$this->form_fields = array(
			'enabled'                           => array(
				'title'   => __( 'Enable', 'shipcloud-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable shipcloud', 'shipcloud-for-woocommerce' ),
				'default' => 'no'
			),
			'api_key'                           => array(
				'title'       => __( 'api key', 'shipcloud-for-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Enter your <a href="%s" target="_blank">shipcloud api key</a>.', 'shipcloud-for-woocommerce' ), 'https://app.shipcloud.io/de/users/api_key' ),
			),
			'allowed_carriers'                  => array(
				'title'       => __( 'Shipping methods', 'shipcloud-for-woocommerce' ),
				'type'        => 'multi_checkbox',
				'description' => __( 'Select the carriers that you want to use in your shop.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'options'     => $carriers_options
			),
			'carrier_selection'                 => array(
				'title'       => __( 'Shipment selection', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Who selects the shipping method?', 'shipcloud-for-woocommerce' ),
				'class'       => 'select',
				'desc_tip'    => true,
				'default'     => 'shopowner',
				'options'     => array(
					'shopowner' => __( 'Shop owner', 'shipcloud-for-woocommerce' ),
					'customer'  => __( 'Customer', 'shipcloud-for-woocommerce' ),
				)
			),
			'standard_carrier'                  => $standard_carrier_settings,
			'notification_email'                => array(
				'title'       => __( 'Notification email', 'shipcloud-for-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Send notification emails from shipcloud to recipients on status changes of shipment.', 'shipcloud-for-woocommerce' ),
				'default'     => 'yes'
			),
			'carrier_email'                => array(
				'title'       => __( 'Carrier email', 'shipcloud-for-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Send notification emails from carriers (supported by DHL and DPD) to recipients on status changes of shipment.', 'shipcloud-for-woocommerce' ),
				'default'     => 'yes'
			),
			'callback_url'                      => array(
				'title'       => __( 'Webhook url', 'shipcloud-for-woocommerce' ),
				'type'        => 'text_only',
				'description' => sprintf( __( '%s<br /><br />You want to get noticed about the shipment status? Copy the webhook url and enter it in your <a href="%s" target="_blank">shipcloud.io webhooks section</a>.', 'shipcloud-for-woocommerce' ), '<code>' . $this->callback_url . '</code>', 'https://app.shipcloud.io/de/webhooks' ),
				'disabled'    => false
			),
			'debug'                             => array(
				'title'   => __( 'Debug', 'shipcloud-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable logging if you experience problems.', 'shipcloud-for-woocommerce' ),
				'default' => 'yes'
			),
			'calculation'                       => array(
				'title'       => __( 'Automatic price calculation', 'shipcloud-for-woocommerce' ),
				'type'        => 'title',
				'description' => sprintf( __( 'To get a price for the customers order, you have to setup the price calculation.', 'shipcloud-for-woocommerce' ) )
			),
			'disable_calculation'                           => array(
				'title'   => __( 'Disable', 'shipcloud-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Disable shipping cost calculation in cart and checkout page (if you only want to use label creation).', 'shipcloud-for-woocommerce' ),
				'default' => 'no'
			),
			'calculate_products_type'           => array(
				'title'       => __( 'Products', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'How should the price for products be calculated.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'default'     => 'class',
				'options'     => array(
					'product'      => __( 'Per product: charge shipping for each product individually', 'shipcloud-for-woocommerce' ),
					'order'        => __( 'Per order: charge shipping for the most expensive shipping for a product', 'shipcloud-for-woocommerce' ),
					'product_sum'   => __( 'Virtual parcel: create a virtual parcel with volume and weight of all products and charge shipping', 'shipcloud-for-woocommerce' ),
				)
			),
			'calculate_products_type_fallback'           => array(
				'title'       => __( 'Products (fallback)', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'How should the price for products be calculated if api limit has been reached.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'default'     => 'product',
				'options'     => array(
					'product'      => __( 'Per product: charge shipping with fallback price for each product', 'shipcloud-for-woocommerce' ),
					'order'        => __( 'Per order: charge shipping with fallback price for one product', 'shipcloud-for-woocommerce' ),
				)
			),
			'standard_price_products'           => array(
				'title'       => __( 'Fallback price', 'shipcloud-for-woocommerce' ),
				'type'        => 'price',
				'description' => __( 'Will be used if no sizes or weight is given to a product or for fallback (have to be entered in EUR).', 'shipcloud-for-woocommerce' ),
			),
			'calculation_type_shipment_classes' => array(
				'title'       => __( 'Shipping classes', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'How should the price for shipping classes be calculated.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'default'     => 'class',
				'options'     => array(
					'class' => __( 'Per class: charge shipping for each shipping class individually', 'shipcloud-for-woocommerce' ),
					'order' => __( 'Per order: charge shipping for the most expensive shipping class', 'shipcloud-for-woocommerce' ),
				)
			),
			'calculation_type_shipment_classes_fallback' => array(
				'title'       => __( 'Shipping classes (fallback)', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'How should the price for shipping classes be calculated if api limit has been reached.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'default'     => 'class',
				'options'     => array(
					'class' => __( 'Per class: charge shipping with fallback price for each class', 'shipcloud-for-woocommerce' ),
					'order' => __( 'Per order: charge shipping with fallback price for one class', 'shipcloud-for-woocommerce' ),
				)
			),
			'standard_price_shipment_classes'   => array(
				'title'       => __( 'Fallback price', 'shipcloud-for-woocommerce' ),
				'type'        => 'price',
				'description' => __( 'Will be used if no sizes or weight is given to a shipping class (have to be entered in EUR).', 'shipcloud-for-woocommerce' ),
			),
			'standard_sender_data'              => array(
				'title'       => __( 'Sender information', 'shipcloud-for-woocommerce' ),
				'type'        => 'title',
				'description' => sprintf( __( 'Setup information for the standard sender.', 'shipcloud-for-woocommerce' ) ),
			),
			'sender_company'                    => array(
				'title'       => __( 'Company', 'shipcloud-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender company for shipment.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'sender_first_name'                 => array(
				'title'       => __( 'First name', 'shipcloud-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender first name for shipment.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'sender_last_name'                  => array(
				'title'       => __( 'Last name', 'shipcloud-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender last name for shipment.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'sender_street'                     => array(
				'title'       => __( 'Street', 'shipcloud-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender street for shipment.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'sender_street_nr'                  => array(
				'title'       => __( 'Street number', 'shipcloud-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender street number for shipment.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'sender_postcode'                   => array(
				'title'       => __( 'Zipcode', 'shipcloud-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender zipcode for shipment.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'sender_state'                      => array(
				'title'       => __( 'State', 'shipcloud-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender state for shipment.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'sender_city'                       => array(
				'title'       => __( 'City', 'shipcloud-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender city for shipment.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'sender_country'                    => array(
				'title'       => __( 'Country', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Enter standard sender country for shipment.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'options'     => $woocommerce->countries->countries,
				'default'     => $default_country
			),
			'recipient_information'              => array(
				'title'       => __( 'Recipient information', 'shipcloud-for-woocommerce' ),
				'type'        => 'title',
				'description' => sprintf( __( 'Setup information for the recipient.', 'shipcloud-for-woocommerce' ) ),
			),
			'street_detection'               => array(
				'title'   => __( 'Street detection', 'shipcloud-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Automatic split street from street number (in some countries this do not work correct because of different street number schemes).', 'shipcloud-for-woocommerce' ),
				'default' => 'yes'
			),
		);

		$this->instance_form_fields = array(
			'allowed_carriers'                  => array(
				'title'       => __( 'Shipping methods', 'shipcloud-for-woocommerce' ),
				'type'        => 'multi_checkbox',
				'description' => __( 'Select the carriers that you want to use in your shop.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'options'     => $carriers_options
			),
			'carrier_selection'                 => array(
				'title'       => __( 'Shipment selection', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Who selects the shipping method?', 'shipcloud-for-woocommerce' ),
				'class'       => 'select',
				'desc_tip'    => true,
				'options'     => array(
					'shopowner' => __( 'Shop owner', 'shipcloud-for-woocommerce' ),
					'customer'  => __( 'Customer', 'shipcloud-for-woocommerce' ),
				)
			),
			'standard_carrier'                  => $standard_carrier_settings,
			'calculation'                       => array(
				'title'       => __( 'Automatic price calculation', 'shipcloud-for-woocommerce' ),
				'type'        => 'title',
				'description' => sprintf( __( 'To get a price for the customers order, you have to setup the price calculation.', 'shipcloud-for-woocommerce' ) )
			),
			'calculate_products_type'           => array(
				'title'       => __( 'Products', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'How should the price for products be calculated.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'options'     => array(
					'product'       => __( 'Per product: charge shipping for each product individually', 'shipcloud-for-woocommerce' ),
					'order'         => __( 'Per order: charge shipping for the most expensive shipping for a product', 'shipcloud-for-woocommerce' ),
					'product_sum'   => __( 'Virtual parcel: create a virtual parcel with volume and weight of all products and charge shipping', 'shipcloud-for-woocommerce' ),
				)
			),
			'calculate_products_type_fallback'           => array(
				'title'       => __( 'Products (fallback)', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'How should the price for products be calculated if api limit has been reached.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'default'     => 'product',
				'options'     => array(
					'product'      => __( 'Per product: charge shipping with fallback price for each product', 'shipcloud-for-woocommerce' ),
					'order'        => __( 'Per order: charge shipping with fallback price for whole order', 'shipcloud-for-woocommerce' ),
				)
			),
			'standard_price_products'           => array(
				'title'       => __( 'Fallback price', 'shipcloud-for-woocommerce' ),
				'type'        => 'price',
				'description' => __( 'Will be used if no sizes or weight is given to a product (have to be entered in EUR).', 'shipcloud-for-woocommerce' ),
			),
			'calculation_type_shipment_classes' => array(
				'title'       => __( 'Shipping classes', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'How should the price for shipping classes be calculated.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'options'     => array(
					'class' => __( 'Per class: charge shipping for each shipping class individually', 'woocommerce' ),
					'order' => __( 'Per order: charge shipping for the most expensive shipping class', 'woocommerce' ),
				)
			),
			'calculation_type_shipment_classes_fallback' => array(
				'title'       => __( 'Shipping classes (fallback)', 'shipcloud-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'How should the price for shipping classes be calculated if api limit has been reached.', 'shipcloud-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'default'     => 'class',
				'options'     => array(
					'class' => __( 'Per class: charge shipping with fallback price for each class', 'shipcloud-for-woocommerce' ),
					'order' => __( 'Per order: charge shipping with fallback price for one class', 'shipcloud-for-woocommerce' ),
				)
			),
			'standard_price_shipment_classes'   => array(
				'title'       => __( 'Fallback price', 'shipcloud-for-woocommerce' ),
				'type'        => 'price',
				'description' => __( 'Will be used if no sizes or weight is given to a shipping class or for fallback (have to be entered in EUR).', 'shipcloud-for-woocommerce' ),
			)
		);
	}

	/**
	 * Adding form field for Address Field and enabling City field
	 *
	 * @param $woocommerce_shipping_calculator_enable_city
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function add_calculate_shipping_form_fields( $woocommerce_shipping_calculator_enable_city )
	{
		$woocommerce_shipping_calculator_enable_city = true;
		?>
		<p class="form-row form-row-wide" id="calc_shipping_address_field">
			<input type="text" class="input-text" value="<?php echo esc_attr( WC()->customer->get_shipping_address() ); ?>" placeholder="<?php esc_attr_e( 'Address', 'woocommerce' ); ?>" name="calc_shipping_address" id="calc_shipping_address"/>
		</p>
		<?php
		return $woocommerce_shipping_calculator_enable_city;
	}

	/**
	 * Setting Address field after submiting
	 *
	 * @since 1.0.0
	 */
	public static function add_calculate_shipping_fields()
	{
		WC()->customer->set_shipping_address( $_POST[ 'calc_shipping_address' ] );
	}

	/**
	 * Listening to Shipcloud Webhooks
	 *
	 * @since 1.0.0
	 */
	public static function shipment_listener()
	{
		global $wpdb;

		$post     = file_get_contents( 'php://input' );
		$shipment = json_decode( $post );

		if ( ( json_last_error() !== JSON_ERROR_NONE ) )
		{
			self::log( sprintf( 'Shipment Listener: JSON error (%s).', json_last_error_msg() ) );
			exit;
		}

		if ( ! property_exists( $shipment, 'data' ) || ! property_exists( $shipment->data, 'id' ) )
		{
			self::log( 'Shipment Listener: Wrong data format.' );
			exit;
		}

		$shipment_id = $shipment->data->id;

		if ( empty( $shipment_id ) )
		{
			self::log( 'Shipment Listener: Shipment ID not given.' );
			exit;
		}

		$sql = $wpdb->prepare( "SELECT p.ID FROM {$wpdb->posts} AS p, {$wpdb->postmeta} AS pm WHERE p.ID = pm.post_ID AND pm.meta_key=%s AND pm.meta_value=%s", 'shipcloud_shipment_ids', $shipment_id );

		$order_id = $wpdb->get_var( $sql );

		if ( null == $order_id )
		{
			self::log( sprintf( 'Shipment Listener: Order ID for Shipment ID #%s not found', $shipment_id ) );
			exit;
		}
		else
		{
			self::log( sprintf( 'Shipment Listener: Changed status to "%s" for Shipment ID %s (Order ID %s) ', $shipment->type, $shipment_id, $order_id ) );
		}

		$order = wc_get_order( $order_id );
		$order->add_order_note( sprintf( __( 'Shipment status changed to: %s', 'shipcloud-for-woocommerce' ), wcsc_get_shipment_status_string( $shipment->type ) ) );

		update_post_meta( $order_id, 'shipment_' . $shipment_id . '_status', $shipment->type );

		/**
		 * Hooks in for further functions after status changes
		 */
		do_action( 'shipcloud_shipment_tracking_change', $order_id, $shipment_id, $shipment->type );


		/**
		 * shipcloud_shipment_tracking_default action
         *
         * @param int $order_id ID of the order.
		 * @param int $shipment_id ID of the shipment.
		 */
		do_action( 'shipcloud_shipment_tracking_default', $order_id, $shipment_id );

		$shipment_action = 'shipcloud_' . str_replace( $shipment->type, '.', '_' );

		/**
		 * shipcloud_shipment_{{ shipment type }} action
         *
		 * @param int $order_id ID of the order.
		 * @param int $shipment_id ID of the shipment.
		 */
		do_action( $shipment_action, $order_id, $shipment_id );

		exit;
	}

	/**
	 * Own processes on saving settings
	 */
	public function process_admin_options()
	{
		parent::process_admin_options();
	}

	/**
	 * Multi Checkbox HTML
	 *
	 * @param $key
	 * @param $data
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function generate_multi_checkbox_html( $key, $data )
	{
		$field    = $this->get_field_key( $key );
		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
			'options'           => array()
		);

		$data  = wp_parse_args( $data, $defaults );
		$value = (array) $this->get_option( $key, array() );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php _e( $field ); ?>"><?php echo wp_kses_post( $data[ 'title' ] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data[ 'title' ] ); ?></span>
					</legend>
					<div class="multi-checkbox <?php _e( $data[ 'class' ] ); ?>" id="<?php _e( $field ); ?>" style="<?php _e( $data[ 'css' ] ); ?>" <?php disabled( $data[ 'disabled' ], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>>
						<?php if ( count( $data[ 'options' ] ) > 0 && '' != trim( $this->get_option('api_key' ) ) ): ?>
							<?php foreach ( (array) $data[ 'options' ] as $option_key => $option_value ) : ?>
								<div>
									<input id="<?php _e( $field ); ?>_<?php _e( $option_key ); ?>" type="checkbox" name="<?php _e( $field ); ?>[]" value="<?php _e( $option_key ); ?>" <?php checked( in_array( $option_key, $value ), true ); ?>> <?php _e( $option_value ); ?>
								</div>
							<?php endforeach; ?>
						<?php else: ?>
							<p><?php _e( 'Please enter an api key to get available shipping carriers.', 'shipcloud-for-woocommerce' ); ?></p>
						<?php endif; ?>
					</div>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
	 * Generate Text only HTML.
	 *
	 * @param  mixed $key
	 * @param  mixed $data
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function generate_text_only_html( $key, $data )
	{
		$defaults = array();

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo wp_kses_post( $data[ 'title' ] ); ?>
			</th>
			<td class="forminp">
				<p><?php echo wp_kses_post( $data[ 'description' ] ); ?></p>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
	 * Validating multi_checkbox field and sanitizing it
	 *
	 * @param $key
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function validate_multi_checkbox_field( $key )
	{
		$field = $this->get_field_key( $key );

		if ( isset( $_POST[ $field ] ) )
		{
			$value = array_map( 'wc_clean', array_map( 'stripslashes', (array) $_POST[ $field ] ) );
		}
		else
		{
			$value = '';
		}

		return $value;
	}

	/**
	 * Calculate_shipping function
	 *
	 * @param array $package
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function calculate_shipping( $package = array() )
	{
		if( 'yes' === $this->get_option( 'disable_calculation' ) )
		{
			return;
		}

		if ( '' == $package['destination']['city']
		     || '' == $package['destination']['country']
		     || '' == $package['destination']['zip_code']
		     || '' == $package['destination']['address']
		) {
			wc_add_notice(
				__( 'Please enter an address to calculate shipping costs.', 'shipcloud-for-woocommerce' ),
				'notice'
			);

			return; // Can't calculate without Address - Stop here!
		}

		$this->init_shipcloud_api();

		/**
		 * Getting Adresses
		 */
		$this->sender = $this->get_sender();
		$this->recipient = $this->get_recipient( $package );

		/**
		 * Ordering Parcels
		 */
		$ordered_package = $this->order_package_by_shipping_class( $package );
		$parcels         = $this->get_ordered_parcels( $ordered_package );
		$carriers = $this->get_carriers();

		if( is_wp_error( $carriers ) )
		{
			self::log( 'Could not get carriers - ' . $carriers->get_error_message() );
			static::log( $carriers->get_error_message() );

			return;
		}

		/**
		 * Calculating
		 */
		foreach ( $carriers AS $carrier_name => $carrier_display_name )
		{
			$sum = $this->get_carrier_cost( $parcels, $carrier_name );

			$this->add_rate(
				array(
					'id'    => $carrier_name,
					'label' => $this->shipcloud_api->get_carrier_display_name_short( $carrier_name ),
					'cost'  => $sum,
				)
            );
		}

		WC()->session->set( 'shipcloud_parcels', $this->calculated_parcels );
	}

	/**
	 * Ordering Package by Shipping Class
	 *
	 * @param $package Package given on
	 *
	 * @return array $shipping_classes
	 * @since 1.0.0
	 */
	private function order_package_by_shipping_class( $package )
	{
		$shipping_classes = array();

		foreach ( $package[ 'contents' ] as $item_id => $values )
		{
			if ( $values[ 'data' ]->needs_shipping() )
			{
				$found_class = $values[ 'data' ]->get_shipping_class();

				if ( ! isset( $shipping_classes[ $found_class ] ) )
				{
					$shipping_classes[ $found_class ] = array();
				}

				$shipping_classes[ $found_class ][ $item_id ] = $values;
			}
		}

		return $shipping_classes;
	}

	/**
	 * Calculate Needed Parcels by Ordered package
	 *
	 * @param array $ordered_package
	 *
	 * @return array $parcels
	 * @since 1.0.0
	 */
	protected function get_ordered_parcels( $ordered_package )
	{
		$parcels  = array();

		foreach ( $ordered_package AS $shipping_class => $products )
		{
			if ( '' === $shipping_class )
			{
				/**
				 * Products
				 */
				foreach ( $products AS $product )
				{
					$parcels[ 'products' ][] = $this->get_product_dimensions( $product );
				}

				continue;
			}

			$parcels[ 'shipping_classes' ][ $shipping_class ] = $this->get_shipping_class_dimensions( $shipping_class );
		}

		return $parcels;
	}

	/**
	 * Getting sender address
	 *
	 * @return array $sender
	 * @since 1.1.0
	 */
	private function get_sender()
	{
		return array(
			'street'    => $this->get_option( 'sender_street' ),
			'street_no' => $this->get_option( 'sender_street_nr' ),
			'zip_code'  => $this->get_option( 'sender_postcode', $this->get_option('sender_zip_code' ) ),
			'city'      => $this->get_option( 'sender_city' ),
			'state'     => $this->get_option( 'sender_state' ),
			'country'   => $this->get_option( 'sender_country' ),
		);
	}

	/**
	 * Geting recipient address from package
	 *
	 * @param $package
	 * @return array
	 * @since 1.1.0
	 */
	private function get_recipient( $package )
	{
		$recipient_street = wcsc_explode_street( $package[ 'destination' ][ 'address' ] );

		if ( is_array( $recipient_street ) )
		{
			$recipient_street_name = $recipient_street[ 'address' ];
			$recipient_street_nr   = $recipient_street[ 'number' ];
		}

		return array(
			'street'    => $recipient_street_name,
			'street_no' => $recipient_street_nr,
			'zip_code'  => $package[ 'destination' ][ 'zip_code' ]?: $package[ 'destination' ][ 'postcode' ],
			'city'      => $package[ 'destination' ][ 'city' ],
			'state'     => $package[ 'destination' ][ 'state' ],
			'country'   => $package[ 'destination' ][ 'country' ]
		);
	}

	/**
	 * Getting Carriers
	 *
	 * @return array
	 * @since 1.1.0
	 */
	private function get_carriers()
	{
		/**
		 * Getting carriers which have to be calculated
		 */
		if ( 'shopowner' === $this->get_option( 'carrier_selection' ) )
		{
			$carriers = array( $this->get_option( 'standard_carrier' ) => wcsc_get_carrier_display_name( $this->get_option( 'standard_carrier' ) ) );
		}
		else
		{
			$carriers = $this->get_allowed_carriers( true );
		}

		return $carriers;
	}

	/**
	 * Getting price for products within shipping classes
	 *
	 * @param string $carrier_name
	 * @param array $parcels
	 *
	 * @return float|WP_Error $sum
	 * @since 1.1.0
	 */
	private function get_price_for_shipping_classes( $carrier_name, $parcels )
	{
		$sum = 0;
		$calculation_type_shipment_classes = $this->get_option( 'calculation_type_shipment_classes' );

		switch( $calculation_type_shipment_classes )
		{
			// Charge for all products
			case 'class':
				$prices = $this->get_prices_for_parcels( $carrier_name, $parcels, 'shipping_class' );

				if( is_wp_error( $prices ) ){
					self::log( $prices->get_error_message() );
					return $this->get_fallback_price_for_shipment_classes( $parcels );
				}

				$sum = array_sum( $prices );
				break;

			// Charge only for the most expensive parcel
			case 'order':
				$prices = $this->get_prices_for_parcels( $carrier_name, $parcels, 'shipping_class' );
				if( is_wp_error( $prices ) ) {
					self::log( $prices->get_error_message() );
					return $this->get_fallback_price_for_shipment_classes( $parcels );
				}
				$sum = max( $prices );
				break;
		}

		return $sum;
	}

	/**
	 * Getting price for products without shipping classes
	 *
	 * @param string $carrier_name
	 * @param array $parcels
	 *
	 * @return float|WP_Error $sum
	 * @since 1.1.0
	 */
	private function get_price_for_products( $carrier_name, $parcels )
	{
		$sum = 0;
		$calculate_products_type = $this->get_option( 'calculate_products_type' );

		switch( $calculate_products_type )
		{
			// Charge for all products
			case 'product':
				$prices = $this->get_prices_for_parcels( $carrier_name, $parcels, 'product' );

				if( is_wp_error( $prices ) )
				{
					self::log( $prices->get_error_message() );
					return $this->get_fallback_price_for_products( $parcels );
				}
				$sum = array_sum( $prices );

				break;

			// Charge for a virtual parcel
			case 'product_sum':
				$price = $this->get_price_for_virtual_parcel( $carrier_name, $parcels, 'product' );

				if( is_wp_error( $price ) ){
					self::log( $price->get_error_message() );
					return $this->get_fallback_price_for_products( $parcels );
				}

				$sum = $price;
				break;

			// Charge only for the most expensive parcel
			case 'order':
				$prices = $this->get_prices_for_parcels( $carrier_name, $parcels, 'product' );

				if( is_wp_error( $prices ) )
				{
					self::log( $prices->get_error_message() );
					return $this->get_fallback_price_for_products( $parcels );
				}

				$sum = max( $prices );
				break;
		}

		return $sum;
	}

	/**
	 * Getting fallback price for products
	 *
	 * @param array $classes
	 *
	 * @return int|mixed|string
	 * @since 1.1.0
	 */
	private function get_fallback_price_for_shipment_classes( $classes )
	{
		$calculate_products_type = $this->get_option( 'calculation_type_shipment_classes_fallback' );

		$sum = 0;
		switch( $calculate_products_type )
		{
			// Charge for all classes
			case 'class':
				$sum = count( $classes ) * $this->get_option( 'standard_price_shipment_classes' );
				break;

			// Charge only for the most expensive class
			case 'order':
				$sum = $this->get_option( 'standard_price_shipment_classes' );
				break;
		}

		return $sum;
	}

	/**
	 * Getting fallback price for products
	 *
	 * @param array $parcels
	 *
	 * @return int|mixed|string
	 * @since 1.1.0
	 */
	private function get_fallback_price_for_products( $products )
	{
		$calculate_products_type = $this->get_option( 'calculate_products_type_fallback' );
		$sum = 0;

		switch( $calculate_products_type )
		{
			// Charge for all products
			case 'product':
				$number_products = 0;
				foreach( $products AS $product )
				{
					$number_products += $product[ 'quantity' ];
				}

				$sum = $number_products * $this->get_option( 'standard_price_products' );
				break;

			// Charge only for the most expensive parcel
			case 'order':
				$sum = $this->get_option( 'standard_price_products' );
				break;
		}

		return $sum;
	}

	/**
	 * Getting prices for parcels
	 *
	 * @param string $carrier_name
	 * @param array $parcel
	 * @param string $type
	 *
	 * @return array|WP_Error $prices
	 * @since 1.1.0
	 */
	private function get_prices_for_parcels( $carrier_name, $parcels, $type )
	{
		$prices = array();

		foreach ( $parcels AS $key => $parcel )
		{
			if ( is_array( $parcel ) )
			{
				$price = $this->get_price( $carrier_name, $parcel );

				if( ! is_wp_error( $price ))
				{
					if( array_key_exists( 'quantity', $parcel ) )
					{
						for( $i = 0; $i < $parcel[ 'quantity' ]; $i++ )
						{
							$prices[] = $price;
						}
					}
					else
					{
						$prices[] = $price;
					}
				}
				else
				{
					return $price;
				}

			}
		}

		return $prices;
	}

	/**
	 * Getting prices for a virtual created parcel
	 *
	 * @param string $carrier_name
	 * @param array $parcels
	 *
	 * @return float|WP_Error $price
	 * @since 1.1.0
	 */
	protected function get_price_for_virtual_parcel( $carrier_name, $parcels )
	{
		$virtual_parcel = $this->calculate_virtual_parcel( $parcels );

		if( is_wp_error( $virtual_parcel ) )
		{
			return $virtual_parcel;
		}

		return $this->get_price( $carrier_name, $virtual_parcel );
	}

	/**
	 * Getting a price for a parcel
	 *
	 * @param string $carrier_name
	 * @param array $parcel
	 *
	 * @return float|WP_Error
	 * @since 1.1.0
	 */
	private function get_price( $carrier_name, $parcel ) {
		$this->init_shipcloud_api();

		$package = array(
			'width'  => $parcel[ 'width' ],
			'height' => $parcel[ 'height' ],
			'length' => $parcel[ 'length' ],
			'weight' => str_replace( ',', '.', $parcel[ 'weight' ] ),
		);

		$price = $this->shipcloud_api->get_price( $carrier_name, $this->sender, $this->recipient, $package );

		if ( is_wp_error( $price ) )
		{
			self::log( $price->get_error_message() );
		}
		else
		{
			$this->calculated_parcels[ $carrier_name ][] = $package;
		}

		return $price;
	}

	/**
	 * Returns a virtual parcel as base for price calculations
	 *
	 * @param array $parcels
	 *
	 * @return array|WP_Error $virtual_parcel
	 * @since 1.1.0
	 */
	private function calculate_virtual_parcel( $parcels )
	{
		$total_weight = 0;
		$total_volume = 0;

		foreach ( $parcels AS $key => $parcel )
		{
			if ( is_array( $parcel ) && array_key_exists( 'width', $parcel ) && array_key_exists( 'height', $parcel ) && array_key_exists( 'length', $parcel ) )
			{
				$parcel_volume = absint( $parcel[ 'width' ] ) * absint( $parcel[ 'height' ] ) * absint( $parcel[ 'length' ] );
				$parcel_weight = floatval( $parcel[ 'weight' ] );;

				if( array_key_exists( 'quantity', $parcel ) )
				{
					$parcel_volume = absint( $parcel[ 'quantity' ] ) * $parcel_volume;
					$parcel_weight = absint( $parcel[ 'quantity' ] ) * $parcel_weight;
				}

				$total_volume += $parcel_volume;
				$total_weight += $parcel_weight;
			}
			else
			{
				return new WP_Error( 'wcsc-calculate-virtual-parcel-missing-parcel', __( 'Parcel dimensions are missing', 'shipcloud-for-woocommerce' ) );
			}
		}

		$average_length = round( pow( $total_volume, (1/3) ), 2 );

		$virtual_parcel = array(
			'width'  => $average_length,
			'height'  => $average_length,
			'length'  => $average_length,
			'weight' => $total_weight
		);

		return $virtual_parcel;
	}

	/**
	 * Get price for parcel which have been selected in Shipping Class.
	 *
	 * @param string $shipping_class
	 *
	 * @return float|boolean $costs
	 * @since 1.0.0
	 */
	private function get_shipping_class_costs( $shipping_class )
	{
		$term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );

		if ( ! is_object( $term ) )
		{
			self::log( sprintf( __( 'No term found for shipping class #%s', 'shipcloud-for-woocommerce' ), $shipping_class ) );
			return false;
		}

		$parcel_id = get_option( 'wcsc_shipping_class_' . $term->term_id . '_parcel_id', 0 );

		if ( 0 == $parcel_id )
		{
			self::log( sprintf( __( 'No parcel found for product id #%s', 'shipcloud-for-woocommerce' ), $product_id ) );
		}

		$retail_price = $this->get_parcel_retail_price( $parcel_id );

		return $retail_price;
	}

	/**
	 * Get retail price for parcel.
	 *
	 * @param string $parcel_id
	 * @return float $retail_price
	 *
	 * @since 1.0.0
	 */
	private function get_parcel_retail_price( $parcel_id = 0 )
	{
		if ( 0 != $parcel_id && '' != $parcel_id )
		{
			// Getting price of parcel, selected in the shipping class
			$parcels      = wcsc_get_parceltemplates( array( 'include' => $parcel_id ) );
			$retail_price = $parcels[ 0 ][ 'values' ][ 'retail_price' ];
		}

		// Price fallback
		if ( '' == $retail_price )
		{
			$retail_price = $this->get_option( 'standard_price' );
			self::log( sprintf( __( 'No price found for parcel. Using fallback price %s', 'shipcloud-for-woocommerce' ), $retail_price ) );
		}

		return $retail_price;
	}

	/**
	 * Get price for parcel which have been selected in product.
	 *
	 * @param $product_id
	 * @return float $retail_price
	 *
	 * @since 1.0.0
	 */
	private function get_product_costs( $product_id )
	{
		$parcel_id    = get_post_meta( $product_id, '_wcsc_parcel_id', true );
		$retail_price = $this->get_parcel_retail_price( $parcel_id );

		return $retail_price;
	}

	/**
	 * Get allowed Carriers
	 *
	 * @param bool $only_customer_services If is set true, function returns only services which are available for customers
	 *
	 * @return array $carriers
	 * @since 1.1.0
	 */
	public function get_allowed_carriers( $only_customer_services = false )
	{
		$this->init_shipcloud_api();

		$allowed_carriers   = $this->get_option( 'allowed_carriers' );
		$shipcloud_carriers = $this->shipcloud_api->get_carriers();

		if ( is_wp_error( $shipcloud_carriers ) )
		{
			self::log( $shipcloud_carriers->get_error_message() );
			return $shipcloud_carriers;
		}

		$carriers = array();

		if ( is_array( $allowed_carriers ) )
		{
			foreach ( $shipcloud_carriers AS $shipcloud_carrier )
			{
				if ( $only_customer_services )
				{
					$carrier_arr = $this->shipcloud_api->disassemble_carrier_name( $shipcloud_carrier[ 'name' ] );
					if ( ! $this->shipcloud_api->is_customer_service( $carrier_arr[ 'service' ] ) )
					{
						continue;
					}
				}
				if ( in_array( $shipcloud_carrier[ 'name' ], $allowed_carriers ) )
				{
					$carriers[ $shipcloud_carrier[ 'name' ] ] = $shipcloud_carrier[ 'display_name' ];
				}
			}
		}

		return $carriers;
	}

    /**
     * Setting standard carrier if no was selected
     *
     * @param $method string Old shipping method
     * @return string New shipping method
     *
     * @since 1.1.2
     */
	public function set_standard_carrier( $method ) {
        return $this->get_option( 'standard_carrier' );
    }

	/**
	 * Getting option (overwrite instance values if there option of instance is empty
	 *
	 * @param string $key
	 * @param null   $empty_value
	 *
	 * @return mixed|string
	 * @since 1.0.0
	 */
	public function get_option( $key, $empty_value = null ) {
		$option = parent::get_option( $key, $empty_value );

		if( ! empty( $option ) ) {
			return $option;
		}

		// If there is no value in instance settings get value from global settings
		return WC_Settings_API::get_option( $key, $empty_value );
	}

	/**
	 * Adding logentry on debug mode
	 *
	 * @param $message
	 *
	 * @since 1.0.0
	 */
	public static function log( $message )
	{
		$settings = get_option( 'woocommerce_shipcloud_settings' );

		if( ! array_key_exists( 'debug', $settings ) ) {
			return;
		}

		if( 'no' === $settings[ 'debug' ] ) {
			return;
	    }

		if ( ! is_object( self::$logger ) )
		{
			self::$logger = new WC_Logger();
		}

		$message = trim(preg_replace( '/\s+/', ' ', $message ) );

		self::$logger->add( 'shipcloud', $message );
	}

	/**
	 * @param $product
	 *
	 * @return array
	 */
	protected function get_product_dimensions( $product ) {
		$dimensions = array(
			'quantity' => $product['quantity']
		);

		$length = get_post_meta( $product['product_id'], '_length', true );
		$width  = get_post_meta( $product['product_id'], '_width', true );
		$height = get_post_meta( $product['product_id'], '_height', true );
		$weight = get_post_meta( $product['product_id'], '_weight', true );

		if ( '' !== $length && '' !== $width && '' !== $height && '' !== $weight ) {
			$dimensions = array(
				'length'   => $length,
				'width'    => $width,
				'height'   => $height,
				'weight'   => $weight,
				'quantity' => $product['quantity']
			);
		}

		return $dimensions;
	}

	/**
	 * @param $shipping_class
	 *
	 * @return array|null
	 */
	private function get_shipping_class_dimensions( $shipping_class ) {
		/**
		 * Shipment Classes
		 */
		$taxonomy = get_term_by( 'name', $shipping_class, 'product_shipping_class' );

		$width  = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_width' );
		$height = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_height' );
		$length = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_length' );
		$weight = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_weight' );

		// If there is missing a dimension, set FALSE
		$dimensions = null;
		if ( $length && $width && $height && $weight ) {
			$dimensions = array(
				'length' => $length,
				'width'  => $width,
				'height' => $height,
				'weight' => $weight
			);
		}

		return $dimensions;
	}

	/**
	 * @param $parcels
	 * @param $carrier_name
	 *
	 * @return float|WP_Error
	 */
	protected function get_carrier_cost( $parcels, $carrier_name ) {
	    $sum = 0;

		if ( isset( $parcels['shipping_classes'] ) ) {
			$sum += $this->get_price_for_shipping_classes( $carrier_name, $parcels['shipping_classes'] );
		}

		if ( isset( $parcels['products'] ) ) {
			$sum += $this->get_price_for_products( $carrier_name, $parcels['products'] );
		}

		return $sum;
	}
}
