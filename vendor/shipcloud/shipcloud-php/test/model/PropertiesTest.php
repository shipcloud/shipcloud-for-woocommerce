<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\Properties;
use shipcloud\phpclient\model\HandlingType;
use shipcloud\phpclient\model\IdType;
use shipcloud\phpclient\model\RegulationClassType;

final class PropertiesTest extends TestCase {
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Properties::class, $instance );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_handling() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Properties::class, $instance );
		
		$this->expectException( ApiException::class );
		
		$instance->set_handling( 'invalid' );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_id_type() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Properties::class, $instance );
		
		$this->expectException( ApiException::class );
		
		$instance->set_id_type( 'invalid' );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_regulation_class() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Properties::class, $instance );
		
		$this->expectException( ApiException::class );
		
		$instance->set_regulation_class( 'invalid' );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		
		$array = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		return new Properties();
	}
	
}
