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
		$this->add_to_cart( array( 70, 37, 53 ) );

		$this->go_cart();

		$customer_data = array(
			'first_name' => 'Max',
			'last_name' => 'Mustermann',
			'company' => 'Musterfirma',
			'email' => 'support@awesome.ug',
			'phone' => '110',
			'address_1' => 'Musterweg 1',
			'address_2' => '',
			'postcode' => '66666',
			'city' => 'Musterstadt',
		);

		$order_id = $this->checkout( $customer_data );

		$this->enter_wcsc_order_shipping_data( $order_id );
	}
}
