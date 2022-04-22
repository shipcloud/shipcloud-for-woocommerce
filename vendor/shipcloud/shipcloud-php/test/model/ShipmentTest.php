<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\Shipment;
use shipcloud\phpclient\model\Address;
use shipcloud\phpclient\model\CarrierType;
use shipcloud\phpclient\model\CustomsDeclaration;
use shipcloud\phpclient\model\IncotermType;
use shipcloud\phpclient\model\Label;
use shipcloud\phpclient\model\LabelVoucher;
use shipcloud\phpclient\model\Package;
use shipcloud\phpclient\model\PackageType;
use shipcloud\phpclient\model\Pickup;
use shipcloud\phpclient\model\ServiceType;

final class ShipmentTest extends TestCase {
	
	public function test_create_instance_without_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError		
        $instance = new Shipment();
		
	}
	
	public function test_create_instance_with_invalid_arguments() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/must be an instance of .*?Address/');

		$instance = new Shipment( 'invalid', 'invalid', 'invalid' );
		
	}
	
	public function test_create_instance_with_invalid_carrier_argument() : void {
		
		$last_name 	= 'Mustermann';
		$street 	= 'Beispielstrasse';
		$street_no 	= '42';
		$zip_code 	= '12345';
		$city 		= 'Musterstadt';
		$country 	= 'DE';
		$to_address = new Address( $last_name, $street, $street_no, $city, $zip_code, $country );
		
		$package 	= new Package( 20, 20, 20, 3.5 );
		$package->set_type( PackageType::PARCEL );
		
		$this->expectException( ApiException::class );

		$instance = new Shipment( 'invalid', $to_address, $package );
		
	}
	
	public function test_create_valid_instance() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Shipment::class, $instance );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_carrier() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Shipment::class, $instance );
		
		$this->expectException( ApiException::class );
		
		$instance->set_carrier( 'invalid' );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_service() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Shipment::class, $instance );
		
		$this->expectException( ApiException::class );
		
		$instance->set_service( 'invalid' );
		
	}
	
	public function test_create_valid_instance_and_set_invalid_incoterm() : void {
		
        $instance = $this->get_valid_instance();
		
		$this->assertNotEmpty( $instance );
        $this->assertInstanceOf( Shipment::class, $instance );
		
		$this->expectException( ApiException::class );
		
		$instance->set_incoterm( 'invalid' );
		
	}
	
	public function test_to_array() : void {
		
		$instance = $this->get_valid_instance();
		
		$array = $instance->to_array();
		
		$this->assertTrue( is_array( $array ) );
		$this->assertNotEmpty( $array );
		
	}
	
	private function get_valid_instance() {
		$company 	= 'Musterfirma & Co. KG Test 1';
		$first_name = 'Maximilian';
		$last_name 	= 'Mustermann';
		$street 	= 'Beispielstrasse';
		$street_no 	= '42';
		$zip_code 	= '12345';
		$city 		= 'Musterstadt';
		$country 	= 'DE';
		$email 		= 'max@mustermann.biz';
		$phone 		= '+491234567895';
		
		$to_address = new Address( $last_name, $street, $street_no, $city, $zip_code, $country );
		$to_address->set_company( $company );
		$to_address->set_first_name( $first_name );
		$to_address->set_email( $email );
		$to_address->set_phone( $phone );
		
		$package = new Package( 20, 20, 20, 3.5 );
		$package->set_type( PackageType::PARCEL );
		
		$shipment = new Shipment( CarrierType::DHL, $to_address, $package );
		$shipment->set_reference_number( 'ref123457' );
		$shipment->set_notification_email( 'person@example1.com' );
		$shipment->set_create_shipping_label( false );
		
		$company 	= 'Gewuerze Paderborn';
		$first_name = 'Karl';
		$last_name 	= 'Müller';
		$street 	= 'Musterstraße';
		$street_no 	= '14a';
		$zip_code 	= '33089';
		$city 		= 'Paderborn';
		$country 	= 'DE';
		$email 		= 'max@mustermann.biz';
		$phone 		= '+491234567897';
		
		$from_address = new Address( $last_name, $street, $street_no, $city, $zip_code, $country );
		$from_address->set_company( $company );
		$from_address->set_first_name( $first_name );
		$from_address->set_email( $email );
		$from_address->set_phone( $phone );
		
		$shipment->set_from( $from_address );
		
		return $shipment;
	}
	
}
