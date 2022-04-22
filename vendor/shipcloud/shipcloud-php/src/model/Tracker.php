<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\CarrierType;

/**
 * Tracker class allows you to monitor a shipment even though it wasn't 
 * created using shipcloud.
 * 
 * Trackers make it possible to track a shipment that wasn't created using 
 * shipcloud. They are basically a way to monitor shipments created elsewhere. 
 * All you have to do is provide us with the tracking number you received from 
 * the carrier as well as its corresponding name acronym.
 * 
 * Notice: Since we're always tracking shipments created using shipcloud 
 * contracts, trackers can only be used with your own carrier contracts. 
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
class Tracker extends AbstractApiObject {
	
	/**
	 * Tracking number (provided by the carrier) of the shipment which should be monitored.
	 * 
	 * @var string
	 */
	 private $carrier_tracking_no;
			
	/**
	 * acronym of the carrier the shipment was created with.
	 * 
	 * @var string
	 */
	 private $carrier;
			
	/**
	 * the receivers address.
	 * 
	 * @var Address
	 */
	 private $to;
			
	/**
	 * the senders address.
	 * 
	 * @var Address
	 */
	 private $from;
			
	/**
	 * email address that we should notify once there's an update for this shipment.
	 * 
	 * @var string
	 */
	 private $notification_email;
	
	/**
	 * Webhook constructor.
	 * 
	 * @param string $carrier_tracking_no
	 * @param string $carrier
	 * @return void
	 * @see CarrierType
	 * @throws ApiException
	 */
	public function __construct( string $carrier_tracking_no, string $carrier ) {
		
		if ( empty( $carrier_tracking_no ) ) {
			throw new ApiException( 'Carrier tracking number must not be empty!' );
		}
		if ( ! CarrierType::is_valid_value( CarrierType::get_class_name(), $carrier ) ) {
			throw new ApiException( 'Invalid carrier type: ' . $carrier );
		}
		
		$this->carrier 				= $carrier;
		$this->carrier_tracking_no 	= $carrier_tracking_no;
		
		$this->required = [ "carrier_tracking_no", "carrier" ];
	}
	
	/**
	 * Getter method for carrier_tracking_no.
	 * @return string The carrier_tracking_no.
	 */
	public function get_carrier_tracking_no() {
		return $this->carrier_tracking_no;
	}

	/**
	 * Setter method for carrier_tracking_no.
	 * @param string $carrier_tracking_no The carrier_tracking_no to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_carrier_tracking_no( string $carrier_tracking_no ) : void {
		if ( empty( $carrier_tracking_no ) ) {
			throw new ApiException( 'Carrier tracking number must not be empty!' );
		}
		$this->carrier_tracking_no = $carrier_tracking_no;
	}

	/**
	 * Getter method for carrier.
	 * @return string The carrier.
	 */
	public function get_carrier() {
		return $this->carrier;
	}

	/**
	 * Setter method for carrier.
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
	 * Getter method for to.
	 * @return Address The to.
	 */
	public function get_to() {
		return $this->to;
	}

	/**
	 * Setter method for to.
	 * @param Address $to The to to set.
	 * @return void
	 */
	public function set_to( Address $to ) : void {
		$this->to = $to;
	}

	/**
	 * Getter method for from.
	 * @return Address The from.
	 */
	public function get_from() {
		return $this->from;
	}

	/**
	 * Setter method for from.
	 * @param Address $from The from to set.
	 * @return void
	 */
	public function set_from( Address $from ) : void {
		$this->from = $from;
	}

	/**
	 * Getter method for notification_email.
	 * @return string The notification_email.
	 */
	public function get_notification_email() {
		return $this->notification_email;
	}

	/**
	 * Setter method for notification_email.
	 * @param string $notification_email The notification_email to set.
	 * @return void
	 */
	public function set_notification_email( string $notification_email ) : void {
		$this->notification_email = $notification_email;
	}
	
	/**
	 * Getter method for parameter array.
	 * @return array The class object as array.
	 */
	public function to_array() : array {
		
		$result = parent::to_array();
		
		if ( ! empty( $this->get_carrier_tracking_no() ) ) {
		    $result['carrier_tracking_no'] = $this->get_carrier_tracking_no();
		}
		if ( ! empty( $this->get_carrier() ) ) {
		    $result['carrier'] = $this->get_carrier();
		}
		if ( ! empty( $this->get_to() ) ) {
		    $result['to'] = $this->get_to()->to_array();
		}
		if ( ! empty( $this->get_from() ) ) {
		    $result['from'] = $this->get_from()->to_array();
		}
		if ( ! empty( $this->get_notification_email() ) ) {
		    $result['notification_email'] = $this->get_notification_email();
		}
		
		return $result;
	}
}
