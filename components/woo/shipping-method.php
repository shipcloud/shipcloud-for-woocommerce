<?php
/**
 * WooCommerce Shipping method
 *
 * Class which extends the WC_Shipping_Method API
 *
 * @author awesome.ug <contact@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 (contact@awesome.ug)

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
	
	class WC_Shipcloud_Shippig extends WC_Shipping_Method {

		var $carriers = array();

		var $logger;

		var $debug = FALSE;
		
		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->id          = 'shipcloud';
			$this->title       = __( 'shipcloud.io', 'wcsc-locale' );
			$this->method_description = __( 'Add shipcloud to your shipping methods', 'wcsc-locale' );
			
			// Is gateway enabled
			if( is_array( $this->settings ) && array_key_exists( 'enabled', $this->settings ) && 'yes' == $this->settings[ 'enabled' ] )
				$this->enabled = 'yes';
			else
				$this->enabled = 'no';

			$this->init();

			if( 'yes' == $this->settings['debug'] )
				$this->debug = TRUE;

			if( $this->debug && class_exists( 'WC_Logger' ))
				$this->log = new WC_Logger();
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		public function init() {
			$this->init_settings();
			$this->init_form_fields();

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Gateway settings
		 */
		 public function init_form_fields() {
		 	global $woocommerce;
			
			$default_country = wc_get_base_location();
			$default_country = $default_country[ 'country' ];
			
		 	$this->form_fields = array(
				    'enabled' => array(
						'title'   => __( 'Enable', 'wcsc-locale' ),
						'type'    => 'checkbox',
						'label'   => __( 'Enable shipcloud.io', 'wcsc-locale' ),
						'default' => 'no'
					),
					'title' => array(
						'title'       => __( 'Title', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'wcsc-locale' ),
						'default'     => __( 'shipcloud.io', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'api_key' => array(
						'title'       => __( 'API Key', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'Enter your shipcloud.com API Key.', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'standard_price' => array(
						'title'       => __( 'Standard Price', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'Enter standard price for a parcel if no price exists.', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'calculation_type' => array(
						'title' 		=> __( 'Calculation Type', 'woocommerce' ),
						'type' 			=> 'select',
						'description' => __( 'How should the price be calculated.', 'wcsc-locale' ),
						'desc_tip'    => true,
						'class'         => 'wc-enhanced-select',
						'default' 		=> 'class',
						'options' 		=> array(
							'class' 	=> __( 'Per Class: Charge shipping for each shipping class individually', 'woocommerce' ),
							'order' 	=> __( 'Per Order: Charge shipping for the most expensive shipping class', 'woocommerce' ),
						)
					),
					'debug' => array(
						'title'       => __( 'Debug', 'wcsc-locale' ),
						'type'        => 'checkbox',
						'label'       => __( 'Enable logging if you experience problems.', 'wcsc-locale' ),
						'default'     => 'no'
					),
					'standard_sender_data' => array(
						'title'			=> __( 'Standard sender data', 'woocommerce' ),
						'type'			=> 'title',
						'description'   => sprintf( __( 'Setup your standard sender data for sending parcels.', 'woocommerce' ) )
					),
					'sender_first_name' => array(
						'title'       => __( 'First Name', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'Enter standard sender first name for shipment.', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'sender_last_name' => array(
						'title'       => __( 'Last Name', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'Enter standard sender last name for shipment.', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'sender_street' => array(
						'title'       => __( 'Street', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'Enter standard sender street for shipment.', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'sender_street_nr' => array(
						'title'       => __( 'Street number', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'Enter standard sender street number for shipment.', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'sender_postcode' => array(
						'title'       => __( 'Postcode', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'Enter standard sender postcode for shipment.', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'sender_city' => array(
						'title'       => __( 'City', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'Enter standard sender city for shipment.', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'sender_country' => array(
						'title'       => __( 'Country', 'wcsc-locale' ),
						'type'        => 'select',
						'description' => __( 'Enter standard sender country for shipment.', 'wcsc-locale' ),
						'desc_tip'    => true,
						'options'	  => $woocommerce->countries->countries,
						'default'	  => $default_country
					)
			);
		}

		/**
		 * calculate_shipping function
		 *
		 * @access public
		 * @param mixed $package
		 * @return void
		 */
		public function calculate_shipping( $package ) {
			$calculation_type = $this->settings[ 'calculation_type' ];

			// $packages = $package[ '' ];



			// $this->log->add( 'shipcloud', print_r( $package, TRUE ) );

			$rate = array(
				'id' => $this->id,
				'label' => $this->settings['title'],
				'cost' => '10.99',
				'calc_tax' => 'per_item'
			);

			// Register the rate
			$this->add_rate( $rate );
		}
	}
}