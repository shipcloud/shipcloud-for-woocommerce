<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\Package;
use shipcloud\phpclient\model\DeclaredValue;
use shipcloud\phpclient\model\PackageType;

final class PackageTest extends TestCase {
	
	public function test_create_instance_without_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError		
        $instance = new Package();
		
	}
	
	public function test_create_instance_with_invalid_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/must be of the type float/');

		$instance = new Package( 'invalid', 'invalid', 'invalid', 'invalid' );
		
	}
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Package::class, $instance );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_declared_value() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Package::class, $instance );
		
		$this->expectError();
        $this->expectErrorMessageMatches('/must be an instance of .*?DeclaredValue/');
		
		$instance->set_declared_value( 'invalid' );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_type() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Package::class, $instance );
		
		$this->expectException( ApiException::class );
		
		$instance->set_type( 'invalid' );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		
		$array = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		return new Package( 20, 20, 50, 3.5 );
	}
	
}
