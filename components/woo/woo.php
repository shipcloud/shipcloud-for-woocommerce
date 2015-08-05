<?php
/**
 * WooCommerce Core Component
 *
 * Loading extensions for Woo
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

class WCSCWoo extends WCSCComponent{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->name = __( 'WooCommerce functions', 'wcsc-locale' );
		$this->slug = 'woo';
		
		parent::__construct();
		
	} // end constructor
	
	public function includes(){
		include( __DIR__ . '/shipping-method.php' );
		include( __DIR__ . '/product.php' );
		add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'add_shippo' ) );
	}
	
	/**
	 * Adding shipping method to WooCommerce
	 */
	public static function add_shippo( $methods ){
		$methods[] = 'WC_Shipcloud_Shippig'; 
		return $methods;
	}
}
wcsc_load_component( 'WCSCWoo' );
