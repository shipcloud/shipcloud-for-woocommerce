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
		 	
			if( array_key_exists( 'username', $this->settings ) && '' != $this->settings['username'] && '' != $this->settings['password'] ):
			endif;
				
			$url = '';
				
			//  Initiate curl
			$ch = curl_init();
			// Disable SSL verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE );
			// Will return the response, if false it print the response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
			// Set the url
			curl_setopt($ch, CURLOPT_URL,$url);
			// Execute
			$result = curl_exec($ch);
			// Closing
			
			curl_close($ch);
			
			// Will dump a beauty json :3
			var_dump( json_decode($result, true) );
			 
			// p( $rates );
			
			$this->form_fields = array(
				    'enabled' => array(
						'title'   => __( 'Enable', 'wcsc-locale' ),
						'type'    => 'checkbox',
						'label'   => __( 'Enable Shippo', 'wcsc-locale' ),
						'default' => 'no'
					),
					'title' => array(
						'title'       => __( 'Title', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'wcsc-locale' ),
						'default'     => __( 'Shippo', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'username' => array(
						'title'       => __( 'Username', 'wcsc-locale' ),
						'type'        => 'text',
						'description' => __( 'Enter your goshippo.com username (email).', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
					'password' => array(
						'title'       => __( 'Password', 'wcsc-locale' ),
						'type'        => 'password',
						'description' => __( 'Enter your goshippo.com password.', 'wcsc-locale' ),
						'desc_tip'    => true,
					),
			);
		} 

		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 * @param mixed $package
		 * @return void
		 */
		public function calculate_shipping( $package ) {
			
			$rate_dhl = array(
				'id'       => $this->id . '_dhl',
				'label'    => "DHL",
				'cost'     => '4.90',
				'calc_tax' => 'per_item'
			);
			// Register the rate
			$this->add_rate( $rate_dhl );
			
			$rate_ups = array(
				'id'       => $this->id . '_ups',
				'label'    => "UPS",
				'cost'     => '5.40',
				'calc_tax' => 'per_item'
			);
			// Register the rate
			$this->add_rate( $rate_ups );
		}
	}
}