<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\Address;
use shipcloud\phpclient\model\CarrierType;
use shipcloud\phpclient\model\PickupTime;

/**
 * Pickup class represents a pickup request for a shipment.
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
class Pickup extends AbstractApiObject {
	
	/**
	 * Acronym of the carrier you want to use.
	 * 
	 * @var string
	 */
	private $carrier;
	
	/**
	 * Defined time window when the carrier should pickup shipments.
	 * 
	 * @var pickup_time
	 */
	private $pickup_time;
	
	/**
	 * Address where the shipment should be picked up.
	 * 
	 * @var pickup_address
	 */
	private $pickup_address;
	
	/**
	 * Array of shipments to be picked up.
	 * 
	 * @var shipments
	 */
	private $shipments;
	
	/**
	 * Pickup constructor.
	 * 
	 * @param string $carrier
	 * @param PickupTime $pickup_time
	 * @param Address $pickup_address
	 * @return void
	 * @see PickupTime
	 * @see Address
	 * @see CarrierType
	 * @throws ApiException
	 */
	public function __construct( string $carrier, PickupTime $pickup_time, Address $pickup_address = null ) {
		
		if ( ! CarrierType::is_valid_value( CarrierType::get_class_name(), $carrier ) ) {
			throw new ApiException( 'Invalid carrier type: ' . $carrier );
		}
		if ( empty( $pickup_time ) ) {
			throw new ApiException( 'Pickup_time must not be empty!' );
		}
		
		$this->carrier 		  = $carrier;
		$this->pickup_time 	  = $pickup_time;
		$this->pickup_address = $pickup_address;
		
		$this->required = [ "carrier", "pickup_time" ];
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
	 * Getter method for pickup time.
	 * @return PickupTime The pickup time.
	 * @see PickupTime
	 */
	public function get_pickup_time() : PickupTime {
		return $this->pickup_time;
	}
	
	/**
	 * Setter method for pickup time.
	 * @param PickupTime $pickup_time The pickup time to set.
	 * @return void
	 * @see PickupTime
	 */
	public function set_pickup_time( PickupTime $pickup_time ) : void {
		if ( empty( $pickup_time ) ) {
			throw new ApiException( 'Pickup_time must not be empty!' );
		}
		$this->pickup_time = $pickup_time;
	}
	
	/**
	 * Getter method for pickup_address.
	 * @return Address The pickup_address.
	 * @see Address
	 */
	public function get_pickup_address() {
		return $this->pickup_address;
	}
	
	/**
	 * Setter method for pickup_address.
	 * @param Address $pickup_address The pickup_address to set.
	 * @return void
	 * @see Address
	 */
	public function set_pickup_address( Address $pickup_address ) : void {
		$this->pickup_address = $pickup_address;
	}
	
	/**
	 * Getter method for shipments.
	 * @return array The shipments list.
	 */
	public function get_shipments() {
		return $this->shipments;
	}
	
	/**
	 * Setter method for shipments.
	 * @param array $shipments The shipments to set.
	 * @return void
	 */
	public function set_shipments( array $shipments ) : void {
		$this->shipments = $shipments;
	}
	
	/**
	 * Getter method for parameter array.
	 * @return array The class object as array.
	 */
	public function to_array() : array {
		
		$result = parent::to_array();
		
		if ( ! empty( $this->get_carrier() ) ) {
			$result['carrier'] = $this->get_carrier();
		}
		if ( ! empty( $this->get_pickup_time() ) ) {
			$result['pickup_time'] = $this->get_pickup_time()->to_array();
		}
		if ( ! empty( $this->get_pickup_address() ) ) {
			$result['pickup_address'] = $this->get_pickup_address()->to_array();
		}
		if ( ! empty( $this->get_shipments() ) ) {
			$result['shipments'] = $this->get_shipments();
		}
		
		return $result;
	}
}
