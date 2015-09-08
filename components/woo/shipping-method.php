<?php
/**
 * WooCommerce Shipping method
 *
 * Class which extends the WC_Shipping_Method API
 *
 * @author awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 (very@awesome.ug)

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
 
if ( !defined( 'ABSPATH' ) ) exit;

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	class WC_Shipcloud_Shippig extends WC_Shipping_Method
	{

		var $carriers = array();

		var $logger;

		var $debug = FALSE;

		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct()
		{
			$this->id = 'shipcloud';
			$this->title = __('shipcloud.io', 'woocommerce-shipcloud');
			$this->method_description = __('Add shipcloud to your shipping methods', 'woocommerce-shipcloud');

			// Is gateway enabled
			if (is_array($this->settings) && array_key_exists('enabled', $this->settings) && 'yes' == $this->settings['enabled'])
				$this->enabled = 'yes';
			else
				$this->enabled = 'no';

			$this->init();

			if (class_exists('WC_Logger'))
				$this->log = new WC_Logger();
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		public function init()
		{
			$this->init_settings();
			$this->init_form_fields();

			add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
		}

		/**
		 * Gateway settings
		 */
		public function init_form_fields()
		{
			global $woocommerce;

			$default_country = wc_get_base_location();
			$default_country = $default_country['country'];

			$shipcloud = new Woocommerce_Shipcloud_API( $this->settings[ 'api_key' ] );

			$this->form_fields = array(
				'enabled'              => array(
					'title'   => __( 'Enable', 'woocommerce-shipcloud' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable shipcloud.io', 'woocommerce-shipcloud' ),
					'default' => 'no'
				),
				'api_key'              => array(
					'title'       => __( 'API Key', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter your <a href="%s" target="_blank">shipcloud.io API Key</a>.', 'woocommerce-shipcloud' ), 'https://app.shipcloud.io/de/users/api_key' ),
				),
				'calculation' => array(
					'title'       => __( 'Automatic Price Calculation', 'woocommerce-shipcloud' ),
					'type'        => 'title',
					'description' => sprintf( __( 'To get a price for the customers order, you have to setup the price calculation.', 'woocommerce-shipcloud' ) )
				),
				'calculate_products_type'     => array(
					'title'       => __( 'Calculate Products', 'woocommerce-shipcloud' ),
					'type'        => 'select',
					'description' => __( 'How should the price for products be calculated.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
					'class'       => 'wc-enhanced-select',
					'default'     => 'class',
					'options'     => array(
						'product' => __( 'Per Product: Charge shipping for each Product individually', 'woocommerce' ),
						'order' => __( 'Per Order: Charge shipping for the most expensive Product', 'woocommerce' ),
					)
				),
				'standard_price_products'       => array(
					'title'       => __( 'Standard Price', 'woocommerce-shipcloud' ),
					'type'        => 'price',
					'description' => __( 'Will be used if no sizes or weight is given to a Product.', 'woocommerce-shipcloud' ),
				),
				'calculation_type_shipment_classes'     => array(
					'title'       => __( 'Calculate Shipment Classes', 'woocommerce-shipcloud' ),
					'type'        => 'select',
					'description' => __( 'How should the price for shipment classes be calculated.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
					'class'       => 'wc-enhanced-select',
					'default'     => 'class',
					'options'     => array(
						'class' => __( 'Per Class: Charge shipping for each shipping class individually', 'woocommerce' ),
						'order' => __( 'Per Order: Charge shipping for the most expensive shipping class', 'woocommerce' ),
					)
				),
				'standard_price_shipment_classes'       => array(
					'title'       => __( 'Standard Price', 'woocommerce-shipcloud' ),
					'type'        => 'price',
					'description' => __( 'Will be used if no sizes or weight is given to a Shipment Class.', 'woocommerce-shipcloud' ),
				),
				'carrier_selection'     => array(
					'title'       => __( 'Carrier Selection', 'woocommerce-shipcloud' ),
					'type'        => 'select',
					'description' => __( 'Who can select the carrier?', 'woocommerce-shipcloud' ),
					'class'       => 'wc-enhanced-select',
					'desc_tip'    => TRUE,
					'default'     => 'shopowner',
					'options'     => array(
						'shopowner' => __( 'Shop Owner can select Carrier', 'woocommerce-shipcloud' ),
						'customer' => __( 'Customer can select Carrier', 'woocommerce-shipcloud' ),
					)
				),
				'standard_sender_data' => array(
					'title'       => __( 'Standard sender data', 'woocommerce-shipcloud' ),
					'type'        => 'title',
					'description' => sprintf( __( 'Setup your standard sender data for sending parcels.', 'woocommerce-shipcloud' ) )
				),
				'sender_company'       => array(
					'title'       => __( 'Company', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender company for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_first_name'    => array(
					'title'       => __( 'First Name', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender first name for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_last_name'     => array(
					'title'       => __( 'Last Name', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender last name for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_street'        => array(
					'title'       => __( 'Street', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender street for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_street_nr'     => array(
					'title'       => __( 'Street number', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender street number for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_postcode'      => array(
					'title'       => __( 'Postcode', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender postcode for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_city'          => array(
					'title'       => __( 'City', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender city for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_country'       => array(
					'title'       => __( 'Country', 'woocommerce-shipcloud' ),
					'type'        => 'select',
					'description' => __( 'Enter standard sender country for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
					'options'     => $woocommerce->countries->countries,
					'default'     => $default_country
				),
				'further_settings' => array(
					'title'       => __( 'Further Settings', 'woocommerce-shipcloud' ),
					'type'        => 'title',
					'description' => sprintf( __( 'Setup further settings.', 'woocommerce-shipcloud' ) )
				),
				'debug'                => array(
					'title'   => __( 'Debug', 'woocommerce-shipcloud' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable logging if you experience problems.', 'woocommerce-shipcloud' ),
					'default' => 'no'
				),
			);

			if( count( $carriers_options ) > 0 ){
				$this->form_fields = array_slice( $this->form_fields, 0, 3, true) + $carriers_options + array_slice( $this->form_fields, 3, count( $this->form_fields )-3, true);
			}
		}

		/**
		 * calculate_shipping function
		 *
		 * @access public
		 * @param mixed $packages
		 * @return void
		 */
		public function calculate_shipping($package)
		{
			$rate = array(
				'id' => $this->id,
				'label' => $this->settings['title'],
				'cost' => 0,
			);

			$found_shipping_classes = $this->find_shipping_classes($package);

            // $this->log( print_r( $package, TRUE ) );
            // $this->log( print_r( $found_shipping_classes, TRUE ) );

			// Running Shipment Classes
			$highest_class_cost = 0;

			foreach ($found_shipping_classes as $shipping_class => $products) {

                // $this->log( print_r( $products, TRUE ) );

                if( '' == $shipping_class ){
                    // Product has no shipment classes
                    foreach( $products AS $product ){
                        $cost = $this->get_product_costs( $product[ 'product_id' ] );
                        $cost_total = $cost * $product[ 'quantity' ];
                        $this->log( sprintf( __( 'Adding product #%s without shipping class %s times with cost of %s. Total costs %s', 'woocommerce-shipcloud' ), $product[ 'product_id' ], $product[ 'quantity' ], $cost, $cost_total ) );
                        $rate[ 'cost' ] += $cost_total;
                    }
                }else{
                    // Product has shipment classes
                    $cost = $this->get_shipping_class_costs($shipping_class);

                    if ($this->settings['calculation_type'] === 'class') {
                        $this->log( sprintf( __( 'Adding products of shipping class #%s with cost of %s', 'woocommerce-shipcloud' ), $shipping_class,  $cost ) );
                        $rate['cost'] += $cost;
                    } else {
                        $highest_class_cost = $cost > $highest_class_cost ? $cost : $highest_class_cost;
                        $this->log( sprintf( __( 'Checking products of shipping class #%s with cost of %s', 'woocommerce-shipcloud' ), $shipping_class, $cost ) );
                    }
                }
			}

            if( $highest_class_cost > 0 ) {
                $rate['cost'] += $highest_class_cost;
                $this->log(sprintf(__('Adding highest costs shipping classes of %s', 'woocommerce-shipcloud'), $highest_class_cost));
            }

            $this->log(sprintf(__('Sum of all costs: %s', 'woocommerce-shipcloud'), $rate['cost']));

			// Register the rate
			$this->add_rate($rate);
		}

		/**
		 * Get price for parcel which have been selected in Shipping Class.
		 * @param string $shipping_class
		 * @return float $costs
		 */
		public function get_shipping_class_costs($shipping_class)
		{
			$term = get_term_by('slug', $shipping_class, 'product_shipping_class');

			if ( !is_object($term) ) {
				$this->log( sprintf( __( 'No term found for shipping class #%s', 'woocommerce-shipcloud' ), $shipping_class ) );
				return FALSE;
			}

			$parcel_id = get_option( 'wcsc_shipping_class_' . $term->term_id . '_parcel_id', 0);

			if (0 == $parcel_id) {
				$this->log( sprintf( __('No parcel found for product id #%s', 'woocommerce-shipcloud'), $product_id ) );
			}

			$retail_price = $this->get_parcel_retail_price( $parcel_id );

			return $retail_price;
		}

		/**
		 * Get price for parcel which have been selected in product.
		 * @param $product_id
		 */
		public function get_product_costs($product_id)
		{
			$parcel_id = get_post_meta($product_id, '_wcsc_parcel_id', TRUE);
			$retail_price = $this->get_parcel_retail_price( $parcel_id );

			return $retail_price;
		}

		/**
		 * Get retail price for parcel.
		 * @param $parcel_id
		 */
		public function get_parcel_retail_price($parcel_id = 0)
		{
            if( 0 != $parcel_id && '' != $parcel_id ) {
                // Getting price of parcel, selected in the shipping class
                $parcels = WCSC_Parcels::get(array('include' => $parcel_id));
                $retail_price = $parcels[0]['values']['retail_price'];
            }

            // Price fallback
            if( '' == $retail_price ) {
                $retail_price = $this->settings['standard_price'];
                $this->log(sprintf(__('No price found for parcel. Using fallback price %s', 'woocommerce-shipcloud'), $retail_price));
            }

			return $retail_price;
		}

		/**
		 * Finds and returns shipping classes and the products with said class.
		 * @param mixed $package
		 * @return array
		 */
		public function find_shipping_classes($package)
		{
			$found_shipping_classes = array();

			foreach ($package['contents'] as $item_id => $values) {
				if ($values['data']->needs_shipping()) {
					$found_class = $values['data']->get_shipping_class();

					if (!isset($found_shipping_classes[$found_class])) {
						$found_shipping_classes[$found_class] = array();
					}

					$found_shipping_classes[$found_class][$item_id] = $values;
				}
			}

			return $found_shipping_classes;
		}

		/**
		 * Adding logentry on debug mode
		 * @param $message
		 */
		public function log($message)
		{
			if ('yes' == $this->settings['debug']) {
				$this->log->add('shipcloud', $message);
			}
		}
	}
}