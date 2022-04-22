<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\Pickup;
use shipcloud\phpclient\model\Address;
use shipcloud\phpclient\model\CarrierType;
use shipcloud\phpclient\model\PickupTime;

final class PickupTest extends TestCase {
	
	public function test_create_instance_without_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError		
        $instance = new Pickup();
		
	}
	
	public function test_create_instance_with_invalid_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/must be an instance of .*?PickupTime/');

		$instance = new Pickup( 'invalid', 'invalid', 'invalid' );
		
	}
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Pickup::class, $instance );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_carrier() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Pickup::class, $instance );
		
		$this->expectException( ApiException::class );
		
		$instance->set_carrier( 'invalid' );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_pickup_time() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Pickup::class, $instance );
		
		$this->expectError();
        $this->expectErrorMessageMatches('/must be an instance of .*?PickupTime/');
		
		$instance->set_pickup_time( 'invalid' );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		
		$array = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		$last_name 	 = 'Müller';
		$street 	 = 'Musterstraße';
		$street_no 	 = '14a';
		$zip_code 	 = '33089';
		$city 		 = 'Paderborn';
		$country 	 = 'DE';
		$address	 = new Address( $last_name, $street, $street_no, $city, $zip_code, $country );
		$carrier	 = CarrierType::DHL;
		$earliest 	 = date( DATE_ATOM, strtotime( "next monday 10:00" ) );
		$latest	  	 = date( DATE_ATOM, strtotime( "next monday 18:00" ) );
		$pickup_time = new PickupTime( $earliest, $latest );
		return new Pickup( $carrier, $pickup_time, $address );
	}
	
}
