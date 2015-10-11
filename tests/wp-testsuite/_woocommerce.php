<?php

require_once( dirname(__FILE__) . '/_wp.php' );

class WooCommerce_Tests extends WP_Tests
{
	public function checkout()
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
}