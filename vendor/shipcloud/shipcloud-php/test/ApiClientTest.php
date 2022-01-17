<?php

use PHPUnit\Framework\TestCase;

use shipcloud\phpclient\ApiClient;

use shipcloud\phpclient\ApiException;
use shipcloud\phpclient\Logger;
use shipcloud\phpclient\Response;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\AdditionalService;
use shipcloud\phpclient\model\AdditionalServiceType;
use shipcloud\phpclient\model\Address;
use shipcloud\phpclient\model\CarrierType;
use shipcloud\phpclient\model\ContentsType;
use shipcloud\phpclient\model\CustomsDeclaration;
use shipcloud\phpclient\model\CustomsDeclarationItem;
use shipcloud\phpclient\model\DeclaredValue;
use shipcloud\phpclient\model\DropOffPoint;
use shipcloud\phpclient\model\DropOffPointType;
use shipcloud\phpclient\model\Enum;
use shipcloud\phpclient\model\EventType;
use shipcloud\phpclient\model\HandlingType;
use shipcloud\phpclient\model\IdType;
use shipcloud\phpclient\model\IncotermType;
use shipcloud\phpclient\model\Label;
use shipcloud\phpclient\model\LabelFormatType;
use shipcloud\phpclient\model\LabelSizeType;
use shipcloud\phpclient\model\LabelVoucher;
use shipcloud\phpclient\model\LabelVoucherFormatType;
use shipcloud\phpclient\model\LabelVoucherType;
use shipcloud\phpclient\model\Package;
use shipcloud\phpclient\model\PackageType;
use shipcloud\phpclient\model\Pickup;
use shipcloud\phpclient\model\PickupTime;
use shipcloud\phpclient\model\Properties;
use shipcloud\phpclient\model\RegulationClassType;
use shipcloud\phpclient\model\ServiceType;
use shipcloud\phpclient\model\Shipment;
use shipcloud\phpclient\model\Tracker;
use shipcloud\phpclient\model\Webhook;

final class ApiClientTest extends TestCase {
	
	private $api_key;
	
	protected function setUp(): void {
		$this->api_key = md5( 'https://my.webhook.info/test' );
	}
	
	protected function tearDown(): void {
		
	}
	
    public function test_use_invalid_api_key(): void {
		
		$this->expectException( ApiException::class );
		$api = new ApiClient( 'invalid_key' );
		
    }
	
	// address
	
    public function test_create_address_without_arguments() : void {
		
		$api = new ApiClient( $this->api_key );
		
		$this->expectError();
		$this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError
		$api->create_address();
		
	}
	
	public function test_create_address_with_empty_address() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/must be an instance of .*?Address/');
		
        $api = new ApiClient( $this->api_key );
	    $api->create_address( null );
	}
	
	public function test_get_address_without_argument() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');
		
		$api = new ApiClient( $this->api_key );
        $api->get_address(); // causes ArgumentCountError
		
	}
	
	public function test_get_address_with_empty_id() : void {
		
		$this->expectException( ApiException::class );
		$this->expectExceptionMessage( '$id must not be empty!' );
		
		$api = new ApiClient( $this->api_key );
        $api->get_address('');
		
	}
	
	public function test_address_exists_with_empty_arguments() : void {
		
		$api = new ApiClient( $this->api_key );
		
		$this->assertFalse( $api->address_exists( [], [] ) );
		
	}
    
	// shipment
	
	public function test_create_shipment_without_arguments() : void {
		
		$api = new ApiClient( $this->api_key );
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError
		$api->create_shipment();
		
	}
	
	public function test_get_shipment_without_argument() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');
		
		$api = new ApiClient( $this->api_key );
        $api->get_shipment(); // causes ArgumentCountError
		
	}
	
	public function test_get_shipment_with_empty_id() : void {
		
        $this->expectException( ApiException::class );
		$this->expectExceptionMessage( '$id must not be empty!' );
		
		$api = new ApiClient( $this->api_key );
        $api->get_shipment('');
		
	}
	
	public function test_update_shipment_with_empty_shipment() : void {
		
		$api = new ApiClient( $this->api_key );
        
		$this->expectError();
        $this->expectErrorMessageMatches('/must be an instance of .*?Shipment/');

		// causes TypeError
		$api->update_shipment( null );
		
	}
	
	public function test_delete_shipment_with_empty_id() : void {
		
        $this->expectException( ApiException::class );
		$this->expectExceptionMessage( '$id must not be empty!' );
		
		$api = new ApiClient( $this->api_key );
        $api->delete_shipment( '' );
		
	}
	
	public function test_create_shipment_quote_without_arguments() : void {
		
		$api = new ApiClient( $this->api_key );
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError
		$api->create_shipment_quote();
		
	}
	
	// pickup_request
	
	public function test_create_pickup_request_without_arguments() : void {
		
		$api = new ApiClient( $this->api_key );
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError
		$api->create_pickup_request();
		
	}
	
	public function test_create_pickup_request_with_empty_argument() : void {
		
		$api = new ApiClient( $this->api_key );
        
		$this->expectError();
        $this->expectErrorMessageMatches('/must be an instance of .*?Pickup/');

		// causes TypeError
		$api->create_pickup_request( 'invalid' );
		
	}
	
	public function test_get_pickup_request_without_argument() : void {
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');
		
		$api = new ApiClient( $this->api_key );
        $api->get_pickup_request(); // causes ArgumentCountError
		
	}
	
	public function test_get_pickup_request_with_empty_id() : void {
		
        $this->expectException( ApiException::class );
		$this->expectExceptionMessage( '$id must not be empty!' );
		
		$api = new ApiClient( $this->api_key );
        $api->get_pickup_request('');
		
	}
	
	// webhook
	
	public function test_create_webhook_without_arguments() : void {
		
		$api = new ApiClient( $this->api_key );
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError
		$api->create_webhook();
		
	}
	
	public function test_create_webhook_with_empty_webhook() {
				
		$api = new ApiClient( $this->api_key );
        
		$this->expectError();
        $this->expectErrorMessageMatches('/must be an instance of .*?Webhook/');

		// causes TypeError
		$api->create_webhook( null );
		
	}
	
	public function test_get_webhook_with_empty_id() : void {
		
        $this->expectException( ApiException::class );
		$this->expectExceptionMessage( '$id must not be empty!' );
		
		$api = new ApiClient( $this->api_key );
        $api->get_webhook('');
		
	}
	
	public function test_delete_webhook_with_empty_id() : void {
		
        $this->expectException( ApiException::class );
		$this->expectExceptionMessage( '$id must not be empty!' );
		
		$api = new ApiClient( $this->api_key );
        $api->delete_webhook( '' );
		
	}
	
	// tracker
	
	public function test_create_tracker_without_arguments() : void {
		
		$api = new ApiClient( $this->api_key );
		
		$this->expectError();
        $this->expectErrorMessageMatches('/Too few arguments/');

        // causes ArgumentCountError
		$api->create_tracker();
		
	}
	
	public function test_create_tracker_with_empty_tracker() {
				
		$api = new ApiClient( $this->api_key );
        
		$this->expectError();
        $this->expectErrorMessageMatches('/must be an instance of .*?Tracker/');

		// causes TypeError
		$api->create_tracker( null );
		
	}
	
	public function test_get_tracker_with_empty_id() : void {
		
        $this->expectException( ApiException::class );
		$this->expectExceptionMessage( '$id must not be empty!' );
		
		$api = new ApiClient( $this->api_key );
        $api->get_tracker('');
		
	}
	
	private function get_valid_shipment() {
		$last_name 	= 'Mustermann';
		$street 	= 'Beispielstraße';
		$street_no 	= '42';
		$zip_code 	= '12345';
		$city 		= 'Musterstadt';
		$country 	= 'DE';
		$to_address = new Address( $last_name, $street, $street_no, $city, $zip_code, $country );
		
		$package = new Package( 20, 20, 20, 3.5 );
		$package->set_type( PackageType::PARCEL );
		
		$shipment = new Shipment( CarrierType::DHL, $to_address, $package );
		
		$last_name 	  = 'Müller';
		$street 	  = 'Musterstraße';
		$street_no 	  = '14a';
		$zip_code 	  = '33089';
		$city 		  = 'Paderborn';
		$country 	  = 'DE';
		$from_address = new Address( $last_name, $street, $street_no, $city, $zip_code, $country );
		
		$shipment->set_from( $from_address );
		
		return $shipment;
	}
	
}
