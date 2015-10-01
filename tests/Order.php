<?php

class Order extends PHPUnit_Extensions_Selenium2TestCase
{

	var $order_id;
	var $customer_data = array();

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

		$this->doCustomerData();

		sleep( 4 );

		$this->byId( "payment_method_cheque" )->click();
		$this->byId( "place_order" )->click();

		sleep( 4 );

		$order_id = $this->byCssSelector( '.order_details .order strong' )->text();

		$this->doShipping( $order_id );
	}

	public function doCustomerData()
	{
		$this->customer_data[ 'first_name' ] = 'Sven';
		$this->customer_data[ 'last_name' ] = 'Wagener';
		$this->customer_data[ 'company' ] = 'Rheinschmiede';
		$this->customer_data[ 'email' ] = 'sven.wagener@rheinschmiede.de';
		$this->customer_data[ 'phone' ] = '015154675596';
		$this->customer_data[ 'address_1' ] = 'Krepperweg 4';
		$this->customer_data[ 'address_2' ] = '';
		$this->customer_data[ 'postcode' ] = '40721';
		$this->customer_data[ 'city' ] = 'Hilden';

		$this->byId( 'billing_first_name' )->value( $this->customer_data[ 'first_name' ] );
		$this->byId( 'billing_last_name' )->value( $this->customer_data[ 'last_name' ] );
		$this->byId( 'billing_company' )->value( $this->customer_data[ 'company' ] );
		$this->byId( 'billing_email' )->value( $this->customer_data[ 'email' ] );
		$this->byId( 'billing_phone' )->value( $this->customer_data[ 'phone' ] );
		$this->byId( 'billing_address_1' )->value( $this->customer_data[ 'address_1' ] );
		$this->byId( 'billing_address_2' )->value( $this->customer_data[ 'address_2' ] );
		$this->byId( 'billing_postcode' )->value( $this->customer_data[ 'postcode' ] );
		$this->byId( 'billing_city' )->value( $this->customer_data[ 'city' ] );
	}

	public function doShipping( $order_id ){
		$this->login();

		$url = "/wp-admin/post.php?post={$order_id}&action=edit";

		$this->url( $url );

		sleep( 5 );

		$this->byName( "parcel_width" )->value( '10' );
		$this->byName( "parcel_height" )->value( '15' );
		$this->byName( "parcel_length" )->value( '20' );
		$this->byName( "parcel_weight" )->value( '5,5' );
	}

	protected function setUp()
	{
		global $argv, $argc;

		$host = 'clean.wp';

		if( $argc == 3 )
		{
			$host = $argv[ 2 ];
		}

		$this->setBrowser( "firefox" );
		$this->setBrowserUrl( "http://" . $host );
	}

	private function login()
	{
		$this->url( "/wp-admin/" );

		$this->byId( 'user_login' )->value( 'wagesve' );
		$this->byId( 'user_pass' )->value( 'tv7r66' );
		$this->byId( 'wp-submit' )->submit();
	}
}
