<?php
/**
 * WooCommerce Shipping method
 * Class which extends the WC_Shipping_Method API
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2016 (support@awesome.ug)
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
	 * Debug mode
	 *
	 * @var bool $debug
	 * @since 1.0.0
	 */
	private $debug = true;

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
	 * Constructor for your shipping class
	 *
	 * @since 1.0.0
	 */
	public function __construct( $instance_id = 0 )
	{
		$this->id                 = 'shipcloud';
		$this->instance_id 		  = absint( $instance_id );
		$this->title              = __( 'shipcloud.io', 'woocommerce-shipcloud' );
		$this->method_description = __( 'Add shipcloud to your shipping methods', 'woocommerce-shipcloud' );
		$this->callback_url       = WC()->api_request_url( 'shipcloud' );

        $this->supports              = array(
            'settings',
            'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
        );

		$this->enabled = $this->get_option( 'enabled' );

		if ( 'no' == $this->get_option( 'debug' ) )
		{
			$this->debug = false;
		}

		$this->start();
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
	}

	/**
	 * Checking Settings and setup Errors
	 *
	 * @since 1.0.0
	 */
	public function init()
	{
		if ( ( empty( $this->get_option( 'api_key' ) ) && ! isset( $_POST[ 'woocommerce_shipcloud_api_key' ] ) ) || ( isset( $_POST[ 'woocommerce_shipcloud_api_key' ] ) && '' == $_POST[ 'woocommerce_shipcloud_api_key' ] ) )
		{
			WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Please enter a <a href="%s">ShipCloud API Key</a>.', 'woocommerce-shipcloud' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' ) ), 'error' );

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
			if ( isset( $_POST[ 'woocommerce_shipcloud_api_key' ] ) )
			{
				$api_key = $_POST[ 'woocommerce_shipcloud_api_key' ];
			}
			else
			{
				$api_key = $this->get_option( 'api_key' );
			}

			$shipcloud_api = new Woocommerce_Shipcloud_API( $api_key );

			$carriers = $shipcloud_api->get_carriers();
			if ( is_wp_error( $carriers ) )
			{
				WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Could not update carriers: %s', 'woocommerce-shipcloud' ), $carriers->get_error_message() ), 'error' );

				return false;
			}
			$this->carriers = $carriers;

			$available_carriers = $this->get_allowed_carriers();
			if ( is_wp_error( $available_carriers ) )
			{
				WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Could not get available carriers: %s', 'woocommerce-shipcloud' ), $available_carriers->get_error_message() ), 'error' );

				return false;
			}
			$this->available_carriers = $available_carriers;
		}

		if ( empty( $this->get_option( 'allowed_carriers' ) ) && ! isset( $_POST[ 'woocommerce_shipcloud_allowed_carriers' ] ) || ( isset( $_POST[ 'woocommerce_shipcloud_api_key' ] ) && ! isset( $_POST[ 'woocommerce_shipcloud_allowed_carriers' ] ) ) )
		{
			WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Please select at least one allowed <a href="%s">shipment method</a>.', 'woocommerce-shipcloud' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' ) ), 'error' );

			return false;
		}

		if ( ( '' == $this->get_option( 'standard_price_products' ) && ! isset( $_POST[ 'woocommerce_shipcloud_standard_price_products' ] ) ) || ( isset( $_POST[ 'woocommerce_shipcloud_standard_price_products' ] ) && '' == $_POST[ 'woocommerce_shipcloud_standard_price_products' ] ) )
		{
			WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Please enter a <a href="%s">Standard Price</a> for Products.', 'woocommerce-shipcloud' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' ) ), 'error' );

			return false;
		}

		if ( ( '' == $this->get_option( 'standard_price_shipment_classes' ) && ! isset( $_POST[ 'woocommerce_shipcloud_standard_price_shipment_classes' ] ) ) || ( isset( $_POST[ 'woocommerce_shipcloud_standard_price_shipment_classes' ] ) && '' == $_POST[ 'woocommerce_shipcloud_standard_price_shipment_classes' ] ) )
		{
			WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Please enter a <a href="%s">Standard Price</a> for Shipment Classes.', 'woocommerce-shipcloud' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' ) ), 'error' );

			return false;
		}

		$standard_address_err = false;

		$sender_val = $this->get_option( 'sender_street' );
		if ( isset( $_POST[ 'woocommerce_shipcloud_sender_street' ] ) )
		{
			$sender_val = $_POST[ 'woocommerce_shipcloud_sender_street' ];
		}

		if ( empty( $sender_val ) )
		{
			$standard_address_err = true;
		}

		$sender_val = $this->get_option( 'sender_street_nr' );
		if ( isset( $_POST[ 'woocommerce_shipcloud_sender_street_nr' ] ) )
		{
			$sender_val = $_POST[ 'woocommerce_shipcloud_sender_street_nr' ];
		}

		if ( empty( $sender_val ) )
		{
			$standard_address_err = true;
		}

		$sender_val = $this->get_option( 'sender_postcode' );
		if ( isset( $_POST[ 'woocommerce_shipcloud_sender_postcode' ] ) )
		{
			$sender_val = $_POST[ 'woocommerce_shipcloud_sender_postcode' ];
		}

		if ( empty( $sender_val ) )
		{
			$standard_address_err = true;
		}

		$sender_val = $this->get_option( 'sender_city' );
		if ( isset( $_POST[ 'woocommerce_shipcloud_sender_city' ] ) )
		{
			$sender_val = $_POST[ 'woocommerce_shipcloud_sender_city' ];
		}

		if ( empty( $sender_val ) )
		{
			$standard_address_err = true;
		}

		$sender_val = $this->get_option( 'sender_country' );
		if ( isset( $_POST[ 'woocommerce_shipcloud_sender_country' ] ) )
		{
			$sender_val = $_POST[ 'woocommerce_shipcloud_sender_country' ];
		}

		if ( empty( $sender_val ) )
		{
			$standard_address_err = true;
		}

		if ( $standard_address_err )
		{
			WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Please enter your <a href="%s">standard sender data</a>! At least, street, street number, postcode, city and country.', 'woocommerce-shipcloud' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' ) ), 'error' );

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
				'title'       => __( 'Standard Shipment Method', 'woocommerce-shipcloud' ),
				'type'        => 'select',
				'description' => __( 'This Carrier will be preselected if the Shop Owner selects the Carrier or will be preselected as Carrier if Customer can select the Carrier.', 'woocommerce-shipcloud' ),
				'options'     => $this->available_carriers,
				'desc_tip'    => true
			);
		}
		else
		{
			$standard_carrier_settings = array(
				'title'       => __( 'Standard Shipment Method', 'woocommerce-shipcloud' ),
				'type'        => 'text_only',
				'description' => __( 'You have to select at least one Carrier above to select a Standard Carrier.', 'woocommerce-shipcloud' ),
			);
		}

		$this->form_fields = array(
			'enabled'                           => array(
				'title'   => __( 'Enable', 'woocommerce-shipcloud' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable shipcloud.io', 'woocommerce-shipcloud' ),
				'default' => 'no'
			),
			'api_key'                           => array(
				'title'       => __( 'API Key', 'woocommerce-shipcloud' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Enter your <a href="%s" target="_blank">shipcloud.io API Key</a>.', 'woocommerce-shipcloud' ), 'https://app.shipcloud.io/de/users/api_key' ),
			),
			'allowed_carriers'                  => array(
				'title'       => __( 'Shipment Methods', 'woocommerce-shipcloud' ),
				'type'        => 'multi_checkbox',
				'description' => __( 'Select the Carriers which you want to use in your Shop.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
				'options'     => $carriers_options
			),
			'carrier_selection'                 => array(
				'title'       => __( 'Shipment Selection', 'woocommerce-shipcloud' ),
				'type'        => 'select',
				'description' => __( 'Who selects the shipment method?', 'woocommerce-shipcloud' ),
				'class'       => 'select',
				'desc_tip'    => true,
				'default'     => 'shopowner',
				'options'     => array(
					'shopowner' => __( 'Shop Owner', 'woocommerce-shipcloud' ),
					'customer'  => __( 'Customer', 'woocommerce-shipcloud' ),
				)
			),
			'standard_carrier'                  => $standard_carrier_settings,
			'callback_url'                      => array(
				'title'       => __( 'Webhook URL', 'woocommerce-shipcloud' ),
				'type'        => 'text_only',
				'description' => sprintf( __( '%s<br /><br />You want to get noticed about the Shipment Status? Copy the Webhook URL and enter it in your <a href="%s" target="_blank">shipcloud.io Webhooks Section.</a>', 'woocommerce-shipcloud' ), '<code>' . $this->callback_url . '</code>', 'https://app.shipcloud.io/de/webhooks' ),
				'disabled'    => false
			),
			'debug'                             => array(
				'title'   => __( 'Debug', 'woocommerce-shipcloud' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable logging if you experience problems.', 'woocommerce-shipcloud' ),
				'default' => 'yes'
			),
			'calculation'                       => array(
				'title'       => __( 'Automatic Price Calculation', 'woocommerce-shipcloud' ),
				'type'        => 'title',
				'description' => sprintf( __( 'To get a price for the customers order, you have to setup the price calculation.', 'woocommerce-shipcloud' ) )
			),
			'disable_calculation'                           => array(
				'title'   => __( 'Disable', 'woocommerce-shipcloud' ),
				'type'    => 'checkbox',
				'label'   => __( 'Disable shipping cost calculation in cart and checkout page (if you only want to use label creation).', 'woocommerce-shipcloud' ),
				'default' => 'no'
			),
			'calculate_products_type'           => array(
				'title'       => __( 'Calculate Products', 'woocommerce-shipcloud' ),
				'type'        => 'select',
				'description' => __( 'How should the price for products be calculated.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'default'     => 'class',
				'options'     => array(
					'product' => __( 'Per Product: Charge shipping for each Product individually', 'woocommerce-shipcloud' ),
					'order'   => __( 'Per Order: Charge shipping for the most expensive shipping for a product', 'woocommerce-shipcloud' ),
					// todo Wording is bad!
				)
			),
			'standard_price_products'           => array(
				'title'       => __( 'Standard Price', 'woocommerce-shipcloud' ),
				'type'        => 'price',
				'description' => __( 'Will be used if no sizes or weight is given to a Product (have to be entered in €).', 'woocommerce-shipcloud' ),
			),
			'calculation_type_shipment_classes' => array(
				'title'       => __( 'Calculate Shipment Classes', 'woocommerce-shipcloud' ),
				'type'        => 'select',
				'description' => __( 'How should the price for shipment classes be calculated.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'default'     => 'class',
				'options'     => array(
					'class' => __( 'Per Class: Charge shipping for each shipping class individually', 'woocommerce' ),
					'order' => __( 'Per Order: Charge shipping for the most expensive shipping class', 'woocommerce' ),
				)
			),
			'standard_price_shipment_classes'   => array(
				'title'       => __( 'Standard Price', 'woocommerce-shipcloud' ),
				'type'        => 'price',
				'description' => __( 'Will be used if no sizes or weight is given to a Shipment Class (have to be entered in €).', 'woocommerce-shipcloud' ),
			),
			'standard_sender_data'              => array(
				'title'       => __( 'Standard sender data', 'woocommerce-shipcloud' ),
				'type'        => 'title',
				'description' => sprintf( __( 'Setup your standard sender data for sending parcels.', 'woocommerce-shipcloud' ) ),
			),
			'sender_company'                    => array(
				'title'       => __( 'Company', 'woocommerce-shipcloud' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender company for shipment.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
			),
			'sender_first_name'                 => array(
				'title'       => __( 'First Name', 'woocommerce-shipcloud' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender first name for shipment.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
			),
			'sender_last_name'                  => array(
				'title'       => __( 'Last Name', 'woocommerce-shipcloud' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender last name for shipment.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
			),
			'sender_street'                     => array(
				'title'       => __( 'Street', 'woocommerce-shipcloud' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender street for shipment.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
			),
			'sender_street_nr'                  => array(
				'title'       => __( 'Street number', 'woocommerce-shipcloud' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender street number for shipment.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
			),
			'sender_postcode'                   => array(
				'title'       => __( 'Postcode', 'woocommerce-shipcloud' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender postcode for shipment.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
			),
			'sender_city'                       => array(
				'title'       => __( 'City', 'woocommerce-shipcloud' ),
				'type'        => 'text',
				'description' => __( 'Enter standard sender city for shipment.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
			),
			'sender_country'                    => array(
				'title'       => __( 'Country', 'woocommerce-shipcloud' ),
				'type'        => 'select',
				'description' => __( 'Enter standard sender country for shipment.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'options'     => $woocommerce->countries->countries,
				'default'     => $default_country
			),
		);

		$this->instance_form_fields = array(
			'allowed_carriers'                  => array(
				'title'       => __( 'Shipment Methods', 'woocommerce-shipcloud' ),
				'type'        => 'multi_checkbox',
				'description' => __( 'Select the Carriers which you want to use in your Shop.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
				'options'     => $carriers_options
			),
			'carrier_selection'                 => array(
				'title'       => __( 'Shipment Selection', 'woocommerce-shipcloud' ),
				'type'        => 'select',
				'description' => __( 'Who selects the shipment method?', 'woocommerce-shipcloud' ),
				'class'       => 'select',
				'desc_tip'    => true,
				'options'     => array(
					'shopowner' => __( 'Shop Owner', 'woocommerce-shipcloud' ),
					'customer'  => __( 'Customer', 'woocommerce-shipcloud' ),
				)
			),
			'standard_carrier'                  => $standard_carrier_settings,
			'calculation'                       => array(
				'title'       => __( 'Automatic Price Calculation', 'woocommerce-shipcloud' ),
				'type'        => 'title',
				'description' => sprintf( __( 'To get a price for the customers order, you have to setup the price calculation.', 'woocommerce-shipcloud' ) )
			),
			'calculate_products_type'           => array(
				'title'       => __( 'Calculate Products', 'woocommerce-shipcloud' ),
				'type'        => 'select',
				'description' => __( 'How should the price for products be calculated.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'options'     => array(
					'product' => __( 'Per Product: Charge shipping for each Product individually', 'woocommerce-shipcloud' ),
					'order'   => __( 'Per Order: Charge shipping for the most expensive shipping for a product', 'woocommerce-shipcloud' ),
					// todo Wording is bad!
				)
			),
			'standard_price_products'           => array(
				'title'       => __( 'Standard Price', 'woocommerce-shipcloud' ),
				'type'        => 'price',
				'description' => __( 'Will be used if no sizes or weight is given to a Product (have to be entered in €).', 'woocommerce-shipcloud' ),
			),
			'calculation_type_shipment_classes' => array(
				'title'       => __( 'Calculate Shipment Classes', 'woocommerce-shipcloud' ),
				'type'        => 'select',
				'description' => __( 'How should the price for shipment classes be calculated.', 'woocommerce-shipcloud' ),
				'desc_tip'    => true,
				'class'       => 'select',
				'options'     => array(
					'class' => __( 'Per Class: Charge shipping for each shipping class individually', 'woocommerce' ),
					'order' => __( 'Per Order: Charge shipping for the most expensive shipping class', 'woocommerce' ),
				)
			),
			'standard_price_shipment_classes'   => array(
				'title'       => __( 'Standard Price', 'woocommerce-shipcloud' ),
				'type'        => 'price',
				'description' => __( 'Will be used if no sizes or weight is given to a Shipment Class (have to be entered in €).', 'woocommerce-shipcloud' ),
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
			if ( self::$debug )
			{
				self::log( sprintf( 'Shipment Listener: JSON error (%s).', json_last_error_msg() ) );
			}
			exit;
		}

		if ( ! property_exists( $shipment, 'data' ) || ! property_exists( $shipment->data, 'id' ) )
		{
			if ( self::$debug )
			{
				self::log( 'Shipment Listener: Wrong data format.' );
			}
			exit;
		}

		$shipment_id = $shipment->data->id;

		if ( empty( $shipment_id ) )
		{
			if ( self::$debug )
			{
				self::log( 'Shipment Listener: Shipment ID not given.' );
			}
			exit;
		}

		$sql = $wpdb->prepare( "SELECT p.ID FROM {$wpdb->posts} AS p, {$wpdb->postmeta} AS pm WHERE p.ID = pm.post_ID AND pm.meta_key=%s AND pm.meta_value=%s", 'shipcloud_shipment_ids', $shipment_id );

		$order_id = $wpdb->get_var( $sql );

		if ( null == $order_id )
		{
			if ( self::$debug )
			{
				self::log( sprintf( 'Shipment Listener: Order ID for Shipment ID #%s not found', $shipment_id ) );
			}
			exit;
		}
		else
		{
			if ( self::$debug )
			{
				self::log( sprintf( 'Shipment Listener: Changed status to "%s" for Shipment ID %s (Order ID %s) ', $shipment->type, $shipment_id, $order_id ) );
			}
		}

		$order = wc_get_order( $order_id );
		$order->add_order_note( sprintf( __( 'Shipment status changed to: %s', 'woocommerce-shipcloud' ), wcsc_get_shipment_status_string( $shipment->type ) ) );

		update_post_meta( $order_id, 'shipment_' . $shipment_id . '_status', $shipment->type );

		/**
		 * Hooks in for further functions after status changes
		 */
		switch ( $shipment->type )
		{
			case 'shipment.tracking.picked_up':
				do_action( 'shipcloud_shipment_tracking_picked_up', $order_id, $shipment_id );
				break;

			case 'shipment.tracking.transit':
				do_action( 'shipcloud_shipment_tracking_transit', $order_id, $shipment_id );
				break;

			case 'shipment.tracking.out_for_delivery':
				do_action( 'shipcloud_shipment_tracking_out_for_delivery', $order_id, $shipment_id );
				break;

			case 'shipment.tracking.delivered':
				do_action( 'shipcloud_shipment_tracking_delivered', $order_id, $shipment_id );
				break;

			case 'shipment.tracking.awaits_pickup_by_receiver':
				do_action( 'shipcloud_shipment_tracking_awaits_pickup_by_receiver', $order_id, $shipment_id );
				break;

			case 'shipment.tracking.delayed':
				do_action( 'shipcloud_shipment_tracking_delayed', $order_id, $shipment_id );
				break;

			case 'shipment.tracking.not_delivered':
				do_action( 'shipcloud_shipment_tracking_not_delivered', $order_id, $shipment_id );
				break;

			case 'shipment.tracking.notification':
				do_action( 'shipcloud_shipment_tracking_notification', $order_id, $shipment_id );
				break;

			case 'shipment.tracking.unknown':
				do_action( 'shipcloud_shipment_tracking_unknown', $order_id, $shipment_id );
				break;

			default:
				do_action( 'shipcloud_shipment_tracking_default', $order_id, $shipment_id );
				break;
		}
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
							<p><?php _e( 'Please enter an API key to get available shipment Carriers.', 'woocommerce-shipcloud' ); ?></p>
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

		$field    = $this->get_field_key( $key );
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
		if ( '' == $package[ 'destination' ][ 'city' ] || '' == $package[ 'destination' ][ 'country' ] || '' == $package[ 'destination' ][ 'postcode' ] || '' == $package[ 'destination' ][ 'address' ] )
		{
			wc_add_notice( __( 'Please enter an address to calculate shipping costs.', 'woocommerce-shipcloud' ), 'notice' );
			return; // Can't calculate without Address - Stop here!
		}

		$shipcloud_api = new Woocommerce_Shipcloud_API( $this->get_option( 'api_key' ) );

		/**
		 * Getting Adresses
		 */
		$sender = array(
			'street'    => $this->get_option( 'sender_street' ),
			'street_no' => $this->get_option( 'sender_street_nr' ),
			'zip_code'  => $this->get_option( 'sender_postcode' ),
			'city'      => $this->get_option( 'sender_city' ),
			'country'   => $this->get_option( 'sender_country' ),
		);

		$recipient_street = wcsc_explode_street( $package[ 'destination' ][ 'address' ] );

		if ( is_array( $recipient_street ) )
		{
			$recipient_street_name = $recipient_street[ 'address' ];
			$recipient_street_nr   = $recipient_street[ 'number' ];
		}

		$recipient = array(
			'street'    => $recipient_street_name,
			'street_no' => $recipient_street_nr,
			'zip_code'  => $package[ 'destination' ][ 'postcode' ],
			'city'      => $package[ 'destination' ][ 'city' ],
			'country'   => $package[ 'destination' ][ 'country' ]
		);

		/**
		 * Ordering Parcels
		 */
		$ordered_package = wcsc_order_package_by_shipping_class( $package );
		$parcels         = wcsc_get_order_parcels( $ordered_package );

		/**
		 * Setup Carrier
		 */
		if ( 'shopowner' === $this->get_option( 'carrier_selection' ) )
		{
			$carriers = array( $this->get_option( 'standard_carrier' ) => wcsc_get_carrier_display_name( $this->get_option( 'standard_carrier' ) ) );
		}
		else
		{
			$carriers = $this->get_allowed_carriers( true );
		}

		/**
		 * Calculating
		 */
		$prices             = array();
		$calculated_parcels = array();

		foreach ( $carriers AS $carrier_name => $carrier_display_name )
		{
			$sum = 0;

			// Shipping Classes
			if ( isset( $parcels[ 'shipping_classes' ] ) )
			{

				// Running each Shipping Class
				foreach ( $parcels[ 'shipping_classes' ] AS $key => $parcel )
				{

					if ( is_array( $parcel ) )
					{
						$package = array(
							'width'  => $parcel[ 'width' ],
							'height' => $parcel[ 'height' ],
							'length' => $parcel[ 'length' ],
							'weight' => str_replace( ',', '.', $parcel[ 'weight' ] ),
						);

						$calculated_parcels[ $carrier_name ][] = array(
							'carrier' => $carrier_shown_name,
							'width'   => $parcel[ 'width' ],
							'height'  => $parcel[ 'height' ],
							'length'  => $parcel[ 'length' ],
							'weight'  => str_replace( ',', '.', $parcel[ 'weight' ] )
						);

						$price = $shipcloud_api->get_price( $carrier_name, $sender, $recipient, $package );

						if ( is_wp_error( $price ) )
						{
							$this->log( $price->get_error_message() );
							continue;
						}
					}
					else
					{
						$price = $parcel;
					}

					if ( 'class' == $this->get_option( 'calculation_type_shipment_classes' ) )
					{
						$sum += $price;
					}
					else
					{
						$sum = $price > $sum ? $price : $sum;
					}
				}
			}

			// Products
			if ( isset( $parcels[ 'products' ] ) )
			{
				// Running each Product
				foreach ( $parcels[ 'products' ] AS $key => $parcel )
				{
					if ( is_array( $parcel ) )
					{
						$package = array(
							'width'  => $parcel[ 'width' ],
							'height' => $parcel[ 'height' ],
							'length' => $parcel[ 'length' ],
							'weight' => str_replace( ',', '.', $parcel[ 'weight' ] ),
						);

						$calculated_parcels[ $carrier_name ][] = array(
							'carrier' => $carrier_name,
							'width'   => $parcel[ 'width' ],
							'height'  => $parcel[ 'height' ],
							'length'  => $parcel[ 'length' ],
							'weight'  => str_replace( ',', '.', $parcel[ 'weight' ] )
						);

						$price = $shipcloud_api->get_price( $carrier_name, $sender, $recipient, $package );

						if ( is_wp_error( $price ) )
						{
							$this->log( $price->get_error_message() );
							continue;
						}
					}
					else
					{
						$price = $parcel;
					}

					if ( 'product' == $this->get_option( 'calculate_products_type' ) )
					{
						$sum += $price;
					}
					else
					{
						$sum = $price > $sum ? $price : $sum;
					}
				}
			}

			$prices[ $carrier_name ] = $sum;

			$rate = array(
				'id'    => $carrier_name,
				'label' => $shipcloud_api->get_carrier_display_name_short( $carrier_name ),
				'cost'  => $sum,
			);

			$this->add_rate( $rate );
		}
		WC()->session->set( 'shipcloud_parcels', $calculated_parcels );
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
		if ( ! is_object( self::$logger ) )
		{
			self::$logger = new WC_Logger();
		}

		self::$logger->add( 'shipcloud', $message );
	}

	/**
	 * Get price for parcel which have been selected in Shipping Class.
	 *
	 * @param string $shipping_class
	 *
	 * @return float $costs
	 * @since 1.0.0
	 */
	public function get_shipping_class_costs( $shipping_class )
	{
		$term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );

		if ( ! is_object( $term ) )
		{
			if ( $this->debug )
			{
				self::log( sprintf( __( 'No term found for shipping class #%s', 'woocommerce-shipcloud' ), $shipping_class ) );
			}

			return false;
		}

		$parcel_id = get_option( 'wcsc_shipping_class_' . $term->term_id . '_parcel_id', 0 );

		if ( 0 == $parcel_id )
		{
			if ( $this->debug )
			{
				self::log( sprintf( __( 'No parcel found for product id #%s', 'woocommerce-shipcloud' ), $product_id ) );
			}
		}

		$retail_price = $this->get_parcel_retail_price( $parcel_id );

		return $retail_price;
	}

	/**
	 * Get retail price for parcel.
	 *
	 * @param string $parcel_id
	 *
	 * @since 1.0.0
	 */
	public function get_parcel_retail_price( $parcel_id = 0 )
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
			if ( $this->debug )
			{
				self::log( sprintf( __( 'No price found for parcel. Using fallback price %s', 'woocommerce-shipcloud' ), $retail_price ) );
			}
		}

		return $retail_price;
	}

	/**
	 * Get price for parcel which have been selected in product.
	 *
	 * @param $product_id
	 *
	 * @since 1.0.0
	 */
	public function get_product_costs( $product_id )
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
		$allowed_carriers   = $this->get_option( 'allowed_carriers' );
		$shipcloud_api = new Woocommerce_Shipcloud_API( $this->get_option( 'shipcloud_api' ) );

		if ( is_wp_error( $shipcloud_api ) )
		{
			return $shipcloud_api;
		}

		$shipcloud_carriers = $shipcloud_api->get_carriers();

		if ( is_wp_error( $shipcloud_carriers ) )
		{
			return $shipcloud_carriers;
		}

		$carriers = array();

		if ( is_array( $allowed_carriers ) )
		{
			foreach ( $shipcloud_carriers AS $shipcloud_carrier )
			{
				if ( $only_customer_services )
				{
					$carrier_arr = $shipcloud_api->disassemble_carrier_name( $shipcloud_carrier[ 'name' ] );
					if ( ! $shipcloud_api->is_customer_service( $carrier_arr[ 'service' ] ) )
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
	 * Getting option (overwrite instance values if there option of instance is empty
	 *
	 * @param string $key
	 * @param null   $empty_value
	 *
	 * @return mixed|string
	 */
	public function get_option( $key, $empty_value = null ) {
		$option = parent::get_option( $key, $empty_value );

		// Return instance option, default instance option or if instance option not exists, check global options
		if( ! empty( $option ) ) {
			return $option;
		}

		// If there is no value in instance settings get value from global options
		return WC_Settings_API::get_option( $key, $empty_value );
	}
}