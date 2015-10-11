<?php

require_once( '_woocommerce_shipcloud.php' );

class Order extends WoocommerceShipcloud_Tests
{
	/**
	 * Method testSeleniumTestTestcase
	 * @test
	 */
	public function testBuyProduct()
	{
		$this->url("/shop/");

		$products[] = $this->byCssSelector( ".post-53 .add_to_cart_button" );

		foreach( $products AS $product )
		{
			$product->click();
			sleep( 4 );
		}

		$this->url( "/cart/" );

		sleep( 2 );

		$this->byCssSelector( ".checkout-button" )->click();

		$this->checkout();

		sleep( 4 );

		$this->byId( "payment_method_cheque" )->click();
		$this->byId( "place_order" )->click();

		sleep( 4 );

		$order_id = $this->byCssSelector( '.order_details .order strong' )->text();

		$this->add_wcsc_shipping_to_order( $order_id );
	}
}
