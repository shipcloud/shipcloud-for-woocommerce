<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\LabelVoucher;
use shipcloud\phpclient\model\LabelVoucherFormatType;
use shipcloud\phpclient\model\LabelVoucherType;

final class LabelVoucherTest extends TestCase {
	
	public function test_create_instance_without_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError		
        $instance = new LabelVoucher();
		
	}
	
	public function test_create_instance_with_invalid_arguments() : void {
		
        $this->expectException( ApiException::class );

		$instance = new LabelVoucher( 'invalid', 'invalid' );
		
	}
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( LabelVoucher::class, $instance );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_format() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( LabelVoucher::class, $instance );
		
        $this->expectException( ApiException::class );
		
		$instance->set_format( 'invalid' );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_type() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( LabelVoucher::class, $instance );
		
        $this->expectException( ApiException::class );
		
		$instance->set_type( 'invalid' );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		$array 	  = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		return new LabelVoucher( LabelVoucherFormatType::PNG, LabelVoucherType::QR_CODE );
	}
	
}
