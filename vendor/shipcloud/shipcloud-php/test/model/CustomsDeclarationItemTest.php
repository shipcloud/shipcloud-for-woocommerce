<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\CustomsDeclarationItem;

final class CustomsDeclarationItemTest extends TestCase {
	
	public function test_create_instance_without_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError
		$instance = new CustomsDeclarationItem();
		
	}
	
	public function test_create_instance_with_invalid_origin_country_argument() : void {
		
        $this->expectException( ApiException::class );
        $instance = new CustomsDeclarationItem( 'Deutschland', 'Beschreibung', 3, '500', 3.5 );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_origin_country() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
		$this->assertInstanceOf( CustomsDeclarationItem::class, $instance );
		
        $this->expectException( ApiException::class );
        $instance->set_origin_country( 'Deutschland' );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		$array 	  = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		return new CustomsDeclarationItem( 'DE', 'Beschreibung', 3, '500', 3.5 );
	}
	
}
