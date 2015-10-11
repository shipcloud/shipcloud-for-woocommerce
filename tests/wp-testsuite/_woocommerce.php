<?php

require_once( dirname(__FILE__) . '/_wp.php' );

class WooCommerce_Tests extends WP_Tests
{
	public function checkout( $customer_data )
	{
		$this->byCssSelector( ".checkout-button" )->click();

		$this->byId( 'billing_first_name' )->value( $customer_data[ 'first_name' ] );
		$this->byId( 'billing_last_name' )->value( $customer_data[ 'last_name' ] );
		$this->byId( 'billing_company' )->value( $customer_data[ 'company' ] );
		$this->byId( 'billing_email' )->value( $customer_data[ 'email' ] );
		$this->byId( 'billing_phone' )->value( $customer_data[ 'phone' ] );
		$this->byId( 'billing_address_1' )->value( $customer_data[ 'address_1' ] );
		$this->byId( 'billing_address_2' )->value( $customer_data[ 'address_2' ] );
		$this->byId( 'billing_postcode' )->value( $customer_data[ 'postcode' ] );
		$this->byId( 'billing_city' )->value( $customer_data[ 'city' ] );

		sleep( 4 ); // Wating for AJAX

		$this->byId( "payment_method_cheque" )->click();
		$this->byId( "place_order" )->click();

		sleep( 4 );

		$order_id = $this->byCssSelector( '.order_details .order strong' )->text();

		return $order_id;
	}

	public function add_to_cart( $product_ids )
	{
		$this->url("/shop/");
		sleep( 2 );

		foreach( $product_ids AS $product_id )
		{
			$product = $this->byCssSelector( ".post-" . $product_id . " .add_to_cart_button" );
			$product->click();
			sleep( 2 );
		}
	}

	public function save_order(){
		$this->byCssSelector( "#woocommerce-order-actions .save_order" );
	}

	public function go_order( $order_id )
	{
		$this->url( "/wp-admin/post.php?post={$order_id}&action=edit" );
		sleep( 2 ); // Waiting for AJAX
	}

	public function go_cart()
	{
		$this->url( "/cart/" );
		sleep( 2 );  // Waiting for AJAX
	}
}