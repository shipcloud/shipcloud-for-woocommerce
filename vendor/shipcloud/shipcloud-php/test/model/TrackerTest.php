<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\Tracker;
use shipcloud\phpclient\model\CarrierType;

final class TrackerTest extends TestCase {
	
	public function test_create_instance_without_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError		
        $instance = new Tracker();
		
	}
	
	public function test_create_instance_with_invalid_carrier_argument() : void {
		
		$this->expectException( ApiException::class );

		$instance = new Tracker( '723558934169', 'invalid' );
		
	}
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Tracker::class, $instance );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_carrier() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Tracker::class, $instance );
		
		$this->expectException( ApiException::class );
		
		$instance->set_carrier( 'invalid' );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		
		$array = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		return new Tracker( '723558934169', CarrierType::UPS );
	}
	
}
