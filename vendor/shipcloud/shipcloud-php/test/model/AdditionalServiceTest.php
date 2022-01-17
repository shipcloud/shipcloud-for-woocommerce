<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AdditionalService;
use shipcloud\phpclient\model\AdditionalServiceType;

final class AdditionalServiceTest extends TestCase {
	
	public function test_create_instance_without_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError
		$instance = new AdditionalService();
		
	}
	
	public function test_create_instance_with_invalid_name_argument() : void {
		
        $this->expectException( ApiException::class );
        $instance = new AdditionalService( 'invalid' );
		
	}
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
		$this->assertInstanceOf( AdditionalService::class, $instance );
		
	}
	
	public function test_set_invalid_name() : void {
		
		$instance = $this->get_valid_instance();
		
        $this->expectException( ApiException::class );
        $instance->set_name( 'invalid' );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		$array 	  = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		return new AdditionalService( AdditionalServiceType::ADVANCE_NOTICE );
	}
	
}
