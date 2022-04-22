<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\CustomsDeclaration;
use shipcloud\phpclient\model\CustomsDeclarationItem;
use shipcloud\phpclient\model\ContentsType;

final class CustomsDeclarationTest extends TestCase {
	
	public function test_create_instance_without_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError
		$instance = new CustomsDeclaration();
		
	}
	
	public function test_create_instance_with_invalid_contents_type_argument() : void {
		
        $this->expectException( ApiException::class );
        $instance = new CustomsDeclaration( 'invalid', 'EUR', 5.90, [] );
		
	}
	
	public function test_create_instance_with_invalid_currency_argument() : void {
		
        $this->expectException( ApiException::class );
        $instance = new CustomsDeclaration( ContentsType::COMMERCIAL_SAMPLE, '€', 5.90, [] );
		
	}
	
	public function test_create_instance_with_invalid_total_value_amount_argument() : void {
		
        $this->expectException( ApiException::class );
        $instance = new CustomsDeclaration( ContentsType::COMMERCIAL_SAMPLE, 'EUR', 1001, [] );
		
	}
	
	public function test_create_instance_with_invalid_items_argument() : void {
		
		$this->expectError();
		$this->expectErrorMessageMatches('/must be of the type array/');
		
		// causes TypeError, because last parameter must be of type array, string given
		$instance = new CustomsDeclaration( ContentsType::COMMERCIAL_SAMPLE, 'EUR', 5.90, '' );
		
	}
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
		$this->assertInstanceOf( CustomsDeclaration::class, $instance );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		$array 	  = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		return new CustomsDeclaration( 
			ContentsType::COMMERCIAL_SAMPLE, 
			'EUR', 
			5.90, 
			[ new CustomsDeclarationItem( 'DE', 'Beschreibung', 3, '500', 3.5 ) ] 
		);
	}
	
}
