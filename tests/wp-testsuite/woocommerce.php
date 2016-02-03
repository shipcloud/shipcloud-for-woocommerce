<?php
/**
 * PHP Unittests for WordPress
 *
 * @package WordPress-Unittests/WooCommerce
 *
 * Copyright 2016 (support@awesome.ug)
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

require_once( dirname( __FILE__ ) . '/wp.php' );

class WooCommerce_Tests extends WP_Tests
{
	/**
	 * WooComemrce Shop URL
	 *
	 * @var $shop_url
	 * @since 1.0.0
	 */
	protected $shop_url;

	/**
	 * WooCommerce Cart URL
	 *
	 * @var $cart_url
	 * @since 1.0.0
	 */
	protected $cart_url;

	/**
	 * WooComemrce Checkout URL
	 *
	 * @var $checkout_url
	 * @since 1.0.0
	 */
	protected $checkout_url;

	/**
	 * Doing a Checkout
	 *
	 * @param $customer_data
	 * @return string
	 * @since 1.0.0
	 */
	public function checkout( $customer_data, $payment_gateway = 'bacs' )
	{
		$this->go_checkout();

		$this->byId( 'billing_first_name' )->value( $customer_data[ 'first_name' ] );
		$this->byId( 'billing_last_name' )->value( $customer_data[ 'last_name' ] );
		$this->byId( 'billing_company' )->value( $customer_data[ 'company' ] );
		$this->byId( 'billing_email' )->value( $customer_data[ 'email' ] );
		$this->byId( 'billing_phone' )->value( $customer_data[ 'phone' ] );
		$this->byId( 'billing_address_1' )->value( $customer_data[ 'address_1' ] );
		$this->byId( 'billing_address_2' )->value( $customer_data[ 'address_2' ] );
		$this->byId( 'billing_postcode' )->value( $customer_data[ 'postcode' ] );
		$this->byId( 'billing_city' )->value( $customer_data[ 'city' ] );

		sleep( $this->std_sleep + 2 ); // Wating for AJAX

		$this->byId( 'payment_method_' . $payment_gateway )->click();
		$this->byId( "place_order" )->click();
	}

	/**
	 * Returning Order ID after Checkout from Page output
	 * @return int
	 */
	public function get_order_id_after_checkout()
	{
		$order_id = $this->byCssSelector( '.order_details .order strong' )->text();
		return (int)$order_id;
	}

	/**
	 * Adding Products to cart
	 *
	 * @param $product_ids
	 * @since 1.0.0
	 */
	public function add_to_cart( $product_ids )
	{
		$this->go( $this->shop_url );
		sleep( $this->std_sleep );

		foreach( $product_ids AS $product_id )
		{
			$product = $this->byCssSelector( ".post-" . $product_id . " .add_to_cart_button" );
			$product->click();
			sleep( $this->std_sleep );
		}
	}

	/**
	 * Save Order in WordPress Admin
	 *
	 * @since 1.0.0
	 */
	public function admin_save_order()
	{
		$this->byCssSelector( "#woocommerce-order-actions .save_order" );
	}

	/**
	 * Go to Orde in WordPress Admin
	 *
	 * @param $order_id
	 * @since 1.0.0
	 */
	public function admin_go_order( $order_id )
	{
		$this->go( "/wp-admin/post.php?post={$order_id}&action=edit" );
		sleep( $this->std_sleep );
	}

	/**
	 * Go to Checkout
	 *
	 * @since 1.0.0
	 */
	public function go_shop()
	{
		$this->go( $this->shop_url );
		sleep( $this->std_sleep );
	}

	/**
	 * Go to Cart
	 *
	 * @since 1.0.0
	 */
	public function go_cart()
	{
		$this->go( $this->cart_url );
		sleep( $this->std_sleep );
	}

	/**
	 * Go to Checkout
	 *
	 * @since 1.0.0
	 */
	public function go_checkout()
	{
		$this->go( $this->checkout_url );
		sleep( $this->std_sleep );
	}
}