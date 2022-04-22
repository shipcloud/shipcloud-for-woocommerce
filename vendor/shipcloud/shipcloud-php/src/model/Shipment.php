<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\Address;
use shipcloud\phpclient\model\CarrierType;
use shipcloud\phpclient\model\CustomsDeclaration;
use shipcloud\phpclient\model\IncotermType;
use shipcloud\phpclient\model\Label;
use shipcloud\phpclient\model\LabelVoucher;
use shipcloud\phpclient\model\Package;
use shipcloud\phpclient\model\Pickup;
use shipcloud\phpclient\model\ServiceType;

/**
 * Shipment class 
 *
 * @category 	Class
 * @package  	shipcloud\phpclient\model
 * @author   	Daniel Muenter <info@msltns.com>
 * @version  	0.0.1
 * @since   	0.0.1
 * @license 	GPL 3
 *          	This program is free software; you can redistribute it and/or modify
 *          	it under the terms of the GNU General Public License, version 3, as
 *          	published by the Free Software Foundation.
 *          	This program is distributed in the hope that it will be useful,
 *          	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          	GNU General Public License for more details.
 *          	You should have received a copy of the GNU General Public License
 *          	along with this program; if not, write to the Free Software
 *          	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class Shipment extends AbstractApiObject {
	
	/**
	 * Acronym of the carrier you want to use.
	 * 
	 * @var string
	 */
	private $carrier;
	
	/**
	 * The receivers address.
	 * 
	 * @var Address
	 */
	private $to;
	
	/**
	 * The senders address. If missing, the default sender address 
	 * (if defined in your shipcloud account) will be used.
	 * 
	 * @var Address
	 */
	private $from;
	
	/**
	 * The cover address. Overwrites the sender address on the 
	 * shipping label.
	 * 
	 * @var Address
	 */
	private $cover_address;
	
	/**
	 * @var Package
	 */
	private $package;
	
	/**
	 * The service that should be used for the shipment.
	 * 
	 * @var string
	 */
	private $service;
	
	/**
	 * A reference number (max. 30 characters) that you want this 
	 * shipment to be identified with. You can use this afterwards 
	 * to easier find the shipment in the shipcloud.io backoffice.
	 * 
	 * @var string
	 */
	private $reference_number;
	
	/**
	 * Text that describes the contents of the shipment. This parameter 
	 * is mandatory if you're using UPS and the following conditions are 
	 * true: from and to countries are not the same; from and/or to 
	 * countries are not in the EU; from and to countries are in the EU 
	 * and the shipments service is not 'standard'. The parameter is also 
	 * mandatory when using DHL Express as carrier.
	 * 
	 * @var string
	 */
	private $description;
	
	/**
	 * Label characteristics.
	 * 
	 * @var Label
	 */
	private $label;
	
	/**
	 * Label voucher characteristics.
	 * 
	 * @var LabelVoucher
	 */
	private $label_voucher;
	
	/**
	 * Email address that we should notify once there's an update for this 
	 * shipment.
	 * 
	 * @var string
	 */
	private $notification_email;
	
	/**
	 * @var string
	 */
	private $incoterm;
	
	/**
	 * @var array
	 */
	private $additional_services;
	
	/**
	 * Pickup request for this shipment.
	 * 
	 * @var Pickup
	 */
	private $pickup;
	
	/**
	 * Declaration of customs related information.
	 * 
	 * @var CustomsDeclaration
	 */
	private $customs_declaration;
	
	/**
	 * Determines if a shipping label should be created at the carrier 
	 * (this means you will be charged when using the production api key).
	 * 
	 * @var bool
	 */
	 private $create_shipping_label;
			
	/**
	 * Here you can save additional data that you want to be associated 
	 * with the shipment. Any combination of key-value pairs is possible.
	 * 
	 * @var object
	 */
	 private $metadata;
	
	/**
	 * Properties constructor.
	 * 
	 * @param string 				$carrier
	 * @param Address 				$to
	 * @param Package 				$package
	 * @param CustomsDeclaration 	$customs_declaration
	 * @return void
	 * @see Address
	 * @see CarrierType
	 * @see Package
	 * @see CustomsDeclaration
	 * @throws ApiException
	 */
	public function __construct( string $carrier, Address $to, Package $package, CustomsDeclaration $customs_declaration = null ) {
		
		if ( ! CarrierType::is_valid_value( CarrierType::get_class_name(), $carrier ) ) {
			throw new ApiException( 'Invalid carrier type: ' . $carrier );
		}
		if ( empty( $to ) ) {
			throw new ApiException( 'To address must not be empty!' );
		}
		if ( empty( $package ) ) {
			throw new ApiException( 'Package must not be empty!' );
		}
		if ( $carrier === CarrierType::FEDEX && $customs_declaration === null ) {
			throw new ApiException( 'FEDEX shipment must contain customs declaration!' );
		}
		
		$this->carrier 	= $carrier;
		$this->to 		= $to;
		$this->package 	= $package;
		if ( $customs_declaration !== null ) {
			$this->customs_declaration = $customs_declaration;
		}
		
		$this->required = [ "carrier", "to", "package" ];
	}
	
	/**
	 * Getter method for carrier.
	 * 
	 * @return string The carrier.
	 */
	public function get_carrier() : string {
		return $this->carrier;
	}
	
	/**
	 * Setter method for carrier.
	 * 
	 * @param string $carrier The carrier to set.
	 * @return void
	 * @see CarrierType
	 * @throws ApiException
	 */
	public function set_carrier( string $carrier ) : void {
		if ( ! CarrierType::is_valid_value( CarrierType::get_class_name(), $carrier ) ) {
			throw new ApiException( 'Invalid carrier type: ' . $carrier );
		}
		$this->carrier = $carrier;
	}
	
	/**
	 * Getter method for receiver address (to).
	 * 
	 * @return Address The receiver address.
	 * @see Address
	 */
	public function get_to() : Address {
		return $this->to;
	}
	
	/**
	 * Setter method for receiver address (to).
	 * 
	 * @param Address $to The receiver address to set.
	 * @return void
	 * @see Address
	 * @throws ApiException
	 */
	public function set_to( Address $to ) : void {
		if ( empty( $to ) ) {
			throw new ApiException( 'To address must not be empty!' );
		}
		$this->to = $to;
	}
	
	/**
	 * Getter method for sender address (from).
	 * 
	 * @return Address The sender address.
	 * @see Address
	 */
	public function get_from() {
		return $this->from;
	}
	
	/**
	 * Setter method for sender address (from).
	 * 
	 * @param Address $from The sender address to set.
	 * @return void
	 * @see Address
	 */
	public function set_from( Address $from ) : void {
		$this->from = $from;
	}
	
	/**
	 * Getter method for cover address.
	 * 
	 * @return Address The cover address.
	 * @see Address
	 */
	public function get_cover_address() {
		return $this->cover_address;
	}
	
	/**
	 * Setter method for cover address.
	 * 
	 * @param Address $cover_address The cover address to set.
	 * @return void
	 * @see Address
	 */
	public function set_cover_address( Address $cover_address ) : void {
		$this->cover_address = $cover_address;
	}
	
	/**
	 * Getter method for package.
	 * 
	 * @return Package The package.
	 * @see Package
	 */
	public function get_package() : Package {
		return $this->package;
	}
	
	/**
	 * Setter method for package.
	 * 
	 * @param Package $package The package to set.
	 * @return void
	 * @see Package
	 * @throws ApiException
	 */
	public function set_package( Package $package ) : void {
		if ( empty( $package ) ) {
			throw new ApiException( 'Package must not be empty!' );
		}
		$this->package = $package;
	}
	
	/**
	 * Getter method for service.
	 * 
	 * @return string The service.
	 * @see ServiceType
	 */
	public function get_service() {
		return ! empty( $this->service ) ? $this->service : ServiceType::get_default_service();
	}
	
	/**
	 * Setter method for service.
	 * 
	 * @param string $service The service to set.
	 * @return void
	 * @see ServiceType
	 * @throws ApiException
	 */
	public function set_service( string $service ) : void {
		if ( ! ServiceType::is_valid_value( ServiceType::get_class_name(), $service ) ) {
			throw new ApiException( 'Invalid service type: ' . $service );
		}
		$this->service = $service;
	}
	
	/**
	 * Getter method for reference number.
	 * 
	 * @return string The reference number.
	 */
	public function get_reference_number() {
		return $this->reference_number;
	}
	
	/**
	 * Setter method for reference number.
	 * 
	 * @param string $reference_number The reference number to set.
	 * @return void
	 */
	public function set_reference_number( string $reference_number ) : void {
		$this->reference_number = $reference_number;
	}
	
	/**
	 * Getter method for description.
	 * 
	 * @return string The description.
	 */
	public function get_description() {
		return $this->description;
	}
	
	/**
	 * Setter method for description.
	 * 
	 * @param string $description The description to set.
	 * @return void
	 */
	public function set_description( string $description ) : void {
		$this->description = $description;
	}
	
	/**
	 * Getter method for label.
	 * 
	 * @return Label The label.
	 * @see Label
	 */
	public function get_label() {
		return $this->label;
	}
	
	/**
	 * Setter method for label.
	 * 
	 * @param Label $label The label to set.
	 * @return void
	 * @see Label
	 */
	public function set_label( Label $label ) : void {
		$this->label = $label;
	}
	
	/**
	 * Getter method for label voucher.
	 * 
	 * @return LabelVoucher The label voucher.
	 * @see LabelVoucher
	 */
	public function get_label_voucher() {
		return $this->label_voucher;
	}
	
	/**
	 * Setter method for label voucher.
	 * 
	 * @param Label $label_voucher The label voucher to set.
	 * @return void
	 * @see LabelVoucher
	 */
	public function set_label_voucher( LabelVoucher $label_voucher ) : void {
		$this->label_voucher = $label_voucher;
	}
	
	/**
	 * Getter method for notification email.
	 * 
	 * @return string The notification email.
	 */
	public function get_notification_email() {
		return $this->notification_email;
	}
	
	/**
	 * Setter method for notification email.
	 * 
	 * @param string $notification_email The notification email to set.
	 * @return void
	 */
	public function set_notification_email( string $notification_email ) : void {
		$this->notification_email = $notification_email;
	}
	
	/**
	 * Getter method for incoterm.
	 * 
	 * @return string The incoterm.
	 * @see ServiceType
	 */
	public function get_incoterm() {
		return $this->incoterm;
	}
	
	/**
	 * Setter method for incoterm.
	 * 
	 * @param string $incoterm The incoterm to set.
	 * @return void
	 * @see IncotermType
	 * @throws ApiException
	 */
	public function set_incoterm( string $incoterm ) : void {
		if ( ! IncotermType::is_valid_value( IncotermType::get_class_name(), $incoterm ) ) {
			throw new ApiException( 'Invalid incoterm type: ' . $incoterm );
		}
		$this->incoterm = $incoterm;
	}
	
	/**
	 * Getter method for additional services.
	 * 
	 * @return array The additional services.
	 */
	public function get_additional_services() {
		return $this->additional_services;
	}
	
	/**
	 * Setter method for additional services.
	 * 
	 * @param array $additional_services The additional services to set.
	 * @return void
	 */
	public function set_additional_services( array $additional_services ) : void {
		$this->additional_services = $additional_services;
	}
	
	/**
	 * Getter method for pickup.
	 * 
	 * @return Pickup The pickup.
	 * @see Pickup
	 */
	public function get_pickup() {
		return $this->pickup;
	}
	
	/**
	 * Setter method for pickup.
	 * 
	 * @param Pickup $pickup The pickup to set.
	 * @return void
	 * @see Pickup
	 */
	public function set_pickup( Pickup $pickup ) : void {
		$this->pickup = $pickup;
	}
	
	/**
	 * Getter method for customs declaration.
	 * 
	 * @return CustomsDeclaration The customs declaration.
	 * @see CustomsDeclaration
	 */
	public function get_customs_declaration() {
		return $this->customs_declaration;
	}
	
	/**
	 * Setter method for customs declaration.
	 * 
	 * @param CustomsDeclaration $customs_declaration The customs declaration to set.
	 * @return void
	 * @see CustomsDeclaration
	 */
	public function set_customs_declaration( CustomsDeclaration $customs_declaration ) : void {
		$this->customs_declaration = $customs_declaration;
	}
	
	/**
	 * Getter method for create_shipping_label.
	 * @return bool The create_shipping_label.
	 */
	public function get_create_shipping_label() {
		return isset( $this->create_shipping_label ) ? $this->create_shipping_label : false;
	}

	/**
	 * Setter method for create_shipping_label.
	 * @param bool $create_shipping_label The create_shipping_label to set.
	 * @return void
	 */
	public function set_create_shipping_label( bool $create_shipping_label ) : void {
		$this->create_shipping_label = $create_shipping_label;
	}

	/**
	 * Getter method for metadata.
	 * @return object The metadata.
	 */
	public function get_metadata() {
		return $this->metadata;
	}

	/**
	 * Setter method for metadata.
	 * @param object $metadata The metadata to set.
	 * @return void
	 */
	public function set_metadata( object $metadata ) : void {
		$this->metadata = $metadata;
	}
	
	/**
	 * Getter method for parameter array.
	 * @return array The address object as array.
	 */
	public function to_array() : array {
		
		$result = parent::to_array();
		if ( ! empty( $this->get_to() ) ) {
			$result['to'] = $this->get_to()->to_array();
		}
		if ( ! empty( $this->get_package() ) ) {
			$result['package'] = $this->get_package()->to_array();
		}
		if ( ! empty( $this->get_carrier() ) ) {
			$result['carrier'] = $this->get_carrier();
		}
		if ( ! empty( $this->get_service() ) ) {
			$result['service'] = $this->get_service();
		}
		$result['create_shipping_label'] = ( ! empty( $this->get_create_shipping_label() ) ? $this->get_create_shipping_label() : false );
		
		if ( ! empty( $this->get_from() ) ) {
			$result['from'] = $this->get_from()->to_array();
		}
		if ( ! empty( $this->get_cover_address() ) ) {
			$result['cover_address'] = $this->get_cover_address()->to_array();
		}
		if ( ! empty( $this->get_reference_number() ) ) {
			$result['reference_number'] = $this->get_reference_number();
		}
		if ( ! empty( $this->get_description() ) ) {
			$result['description'] = $this->get_description();
		}
		if ( ! empty( $this->get_label() ) ) {
			$result['label'] = $this->get_label()->to_array();
		}
		if ( ! empty( $this->get_label_voucher() ) ) {
			$result['label_voucher'] = $this->get_label_voucher()->to_array();
		}
		if ( ! empty( $this->get_notification_email() ) ) {
			$result['notification_email'] = $this->get_notification_email();
		}
		if ( ! empty( $this->get_incoterm() ) ) {
			$result['incoterm'] = $this->get_incoterm();
		}
		$additional_services = $this->get_additional_services();
		if ( ! empty( $additional_services ) ) {
			$array = [];
			foreach( $additional_services as $service ) {
				$array[] = $service->to_array();
			}
			$result['additional_services'] = $array;
		}
		if ( ! empty( $this->get_pickup() ) ) {
			$result['pickup'] = $this->get_pickup()->to_array();
		}
		if ( ! empty( $this->get_customs_declaration() ) ) {
			$result['customs_declaration'] = $this->get_customs_declaration()->to_array();
		}
		if ( ! empty( $this->get_metadata() ) ) {
		    $result['metadata'] = $this->get_metadata()->to_array();
		}
		
		return $result;
	}
}
