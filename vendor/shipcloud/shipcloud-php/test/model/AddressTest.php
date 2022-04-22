<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\Address;

final class AddressTest extends TestCase {
	
	public function test_create_invalid_instance() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError
		$instance = new Address();
		
	}
	
	public function test_create_address_with_invalid_street() : void {
		
		$this->expectException( ApiException::class );
		$this->expectExceptionMessage( 'Street must not be empty!' );
		
        $last_name 	= 'Mustermann';
		$street 	= ''; // <-- should fail at validation
		$street_no 	= '42';
		$zip_code 	= '54321';
		$city 		= 'Musterstadt';
		$country 	= 'DE';
		$address 	= new Address( $last_name, $street, $street_no, $city, $zip_code, $country );
		
	}
	
	public function test_create_address_with_invalid_country_code() : void {
		
		$this->expectException( ApiException::class );
		$this->expectExceptionMessage( 'Country is required as uppercase ISO 3166-1 alpha-2 code!' );
		
        $last_name 	= 'Mustermann'; 
		$street 	= 'Musterstraße';
		$street_no 	= '42';
		$zip_code 	= '54321';
		$city 		= 'Musterstadt';
		$country 	= 'Germany'; // <-- should fail at validation
		$address 	= new Address( $last_name, $street, $street_no, $city, $zip_code, $country );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_country_code() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Address::class, $instance );
		
        $this->expectException( ApiException::class );
		
		$instance->set_country( 'Deutschland' );
		
	}
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Address::class, $instance );
		
	}
	
	public function test_set_invalid_street() : void {
		
		$this->expectException( ApiException::class );
		$this->expectExceptionMessage( 'Street must not be empty!' );
		
        $instance = $this->get_valid_instance();
		$instance->set_street( '' ); // force validation error
		
		$this->expectException( ApiException::class );
		$this->expectExceptionMessage( '$address->get_street() returns empty result!' );
		
		$api = new ApiClient( $this->invalid_api_key );
        $api->create_shipment( $shipment );
		
	}
	
	public function test_set_invalid_drop_off_point() : void {
		
		$instance = $this->get_valid_instance();
		
		$this->expectError();
        $this->expectErrorMessageMatches('/must be an instance of .*?DropOffPoint/');

		// causes TypeError
		$instance->set_drop_off_point( 'invalid' );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();		
		$array 	  = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		$last_name 	= 'Mustermann';
		$street 	= 'Musterstraße';
		$street_no 	= '42';
		$zip_code 	= '54321';
		$city 		= 'Musterstadt';
		$country 	= 'DE';
		return new Address( $last_name, $street, $street_no, $city, $zip_code, $country );
	}
}
