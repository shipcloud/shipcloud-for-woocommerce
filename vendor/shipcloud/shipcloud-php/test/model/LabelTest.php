<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\Label;
use shipcloud\phpclient\model\LabelFormatType;
use shipcloud\phpclient\model\LabelSizeType;

final class LabelTest extends TestCase {
	
	public function test_create_instance_without_arguments() : void {
		
		$instance = new Label();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Label::class, $instance );
	}
	
	public function test_create_instance_with_invalid_arguments() : void {
		
        $this->expectException( ApiException::class );

		$instance = new Label( 'invalid', 'invalid' );
		
	}
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Label::class, $instance );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_format() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Label::class, $instance );
		
        $this->expectException( ApiException::class );
		
		$instance->set_format( 'invalid' );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_size() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Label::class, $instance );
		
        $this->expectException( ApiException::class );
		
		$instance->set_size( 'invalid' );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		
		$array = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		return new Label( LabelFormatType::PDF_A5, LabelSizeType::SIZE_A5 );
	}
	
}
