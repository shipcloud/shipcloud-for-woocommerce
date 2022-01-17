<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\DropOffPoint;
use shipcloud\phpclient\model\DropOffPointType;

final class DropOffPointTest extends TestCase {
	
	public function test_create_instance_with_valid_arguments() : void {
		
        $instance = new DropOffPoint( DropOffPointType::PARCEL_SHOP, '987654321' );
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( DropOffPoint::class, $instance );
		
	}
	
	public function test_create_instance_with_invalid_argument() : void {
		
        $this->expectException( ApiException::class );

		$instance = new DropOffPoint( 'invalid' );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_type() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( DropOffPoint::class, $instance );
		
        $this->expectException( ApiException::class );
		
		$instance->set_type( 'invalid' );
		
	}
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( DropOffPoint::class, $instance );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		
		$array = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		return new DropOffPoint( DropOffPointType::PARCEL_SHOP, '987654321' );
	}
	
}
