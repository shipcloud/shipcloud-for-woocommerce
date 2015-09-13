<?php
/**
 * WooCommerce Core Component
 *
 * Loading extensions for Woo
 *
 * @author  awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 (very@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) )
	exit;

class WCSCWoo extends WCSCComponent
{
	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->name = __( 'WooCommerce functions', 'woocommerce-shipcloud' );
		$this->slug = 'woo';

		parent::__construct();

		add_action( 'woocommerce_shipping_init', array( $this, 'load_shipping_method' ) );
		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
	}

	public function includes()
	{
		if( wcsc_is_enabled() )
		{
			include( __DIR__ . '/woo-functions.php' );
			include( __DIR__ . '/order.php' );
			include( __DIR__ . '/shipping-classes.php' );
		}
	}

	public function load_shipping_method()
	{
		include( __DIR__ . '/shipping-method.php' );
	}

	public function add_shipping_method( $methods )
	{
		$methods[] = 'WC_Shipcloud_Shippig';

		return $methods;
	}
}

wcsc_load_component( 'WCSCWoo' );