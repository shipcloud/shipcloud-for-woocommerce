<?php
/*
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

if ( ! class_exists( 'WC_Your_Shipping_Method' ) ) {
	
	class WC_Shipcloud_Shippig extends WC_Shipping_Method {
		var $carriers = array();
		
		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->id                 = 'shipcloud';
			$this->title       = __( 'Shipcloud', 'wcsc-locale' );
			$this->method_description = __( 'Add shipcloud to your shipping methods', 'wcsc-locale' );
			
			$this->init();
			
			// Is gateway enabled
			if( is_array( $this->settings ) && array_key_exists( 'enabled', $this->settings ) && 'yes' == $this->settings[ 'enabled' ] )
				$this->enabled = 'yes';
			else
				$this->enabled = 'no';
			
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		function init() {
			$this->init_settings();
			$this->init_form_fields(); 

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}
		
		/**
		 * Gateway settings
		 */
		 function init_form_fields() {
		 	$this->form_fields = array(
				    'enabled' => array(
						'title'   => __( 'Enable', 'wcsc-locale' ),
						'type'    => 'checkbox',
						'label'   => __( 'Enable Shipcloud', 'wcsc-locale' ),
						'default' => 'no'
					),
					'title' => array(
						'title'       => __( 'Title', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'wcsc-locale' ),
						'default'     => __( 'Shipcloud', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'api_key' => array(
						'title'       => __( 'API Key', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'Enter your shipcloud.com API Key.', 'wcsc-locale' ),
						'desc_tip'    => true,
					)
			);
			
			if( array_key_exists( 'api_key', $this->settings ) && '' != $this->settings['api_key'] ):
				$carriers = $this->get_carriers();
				
				foreach( $carriers AS $carrier ):
					$this->form_fields[ '_carrier_' . $carrier[ 'name' ] ] = array(
						'title'       	=> $carrier[ 'display_name' ],
						'type'       	=> 'checkbox',
						'description'   => sprintf( __( 'Activate "%s" to enable shiping method.', 'wcsl-locale' ),  $carrier[ 'display_name' ]),
						'desc_tip'    => true
					);
				endforeach;
			endif;
		} 

		private function get_carriers(){
			$shipment_carriers = get_option( 'woocommerce_shipcloud_carriers' );
			
			if( '' == $shipment_carriers )
				$shipment_carriers = $this->update_carriers();
			
			return $shipment_carriers;
		}
		
		private function update_carriers(){
			$sc_api = new Woocommerce_Shipcloud_API( $this->settings['api_key'] );
			$shipment_carriers = $sc_api->get_carriers();
			update_option( 'woocommerce_shipcloud_carriers', $shipment_carriers );
			
			return $shipment_carriers;
		}

		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 * @param mixed $package
		 * @return void
		 */
		public function calculate_shipping( $package ) {
			if( !array_key_exists( 'api_key', $this->settings ) && '' == $this->settings['api_key'] )
				return;
			
			$sc_api = new Woocommerce_Shipcloud_API( $this->settings['api_key'] );
			$carriers = $this->get_carriers();
			
			$from_address = array(
				'street' => 'Mettmanner Straße',
				'street_no' => '32',
				'zip_code' => '40721',
 				'city' => 'Hilden',
				'country' => 'DE'
			);
			
			$to_address = array(
				'street' => 'Krepperweg',
				'street_no' => '4',
				'zip_code' => '40724',
				'city' => 'Hilden',
				'country' => 'DE'
			);
			
			$package_dimensions = array(
				'weight' => 1.5,
				'length' => 40,
				'width' => 50,
				'height' => 40
			);
			
			foreach( $carriers AS $carrier ):
				if( 'no' == $this->settings[ '_carrier_' . $carrier[ 'name' ] ] )
					continue;
					
				$params = array(
					'carrier' => $carrier[ 'name' ],
					'service' => 'standard',
					'to' => $to_address,
					'from' => $from_address,
					'package' => $package_dimensions
				);
				
				$carrier_rate = $sc_api->get_rates( $params );
				
				if( FALSE == $carrier_rate )
					continue;
				
				$rate = array(
					'id'       => $this->id . '_' . $carrier[ 'name' ],
					'label'    => $carrier[ 'display_name' ],
					'cost'     => $carrier_rate[ 'shipment_quote' ][ 'price' ],
					'taxes'    => $carrier_rate[ 'shipment_quote' ][ 'price' ] * 0.19,
					'calc_tax' => 'per_item'
				);
				
				FB::warn( $rate );
				
				$this->add_rate( $rate );
			endforeach;
		}
			
	}
}