<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\HandlingType;
use shipcloud\phpclient\model\IdType;
use shipcloud\phpclient\model\RegulationClassType;

/**
 * Properties class 
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
class Properties extends AbstractApiObject {
	
	/**
	 * @var amount
	 */
	private $amount;
	
	/**
	 * @var bank_account_holder
	 */
	private $bank_account_holder;
	
	/**
	 * @var bank_account_number
	 */
	private $bank_account_number;
	
	/**
	 * @var bank_code
	 */
	private $bank_code;
	
	/**
	 * @var bank_name
	 */
	private $bank_name;
	
	/**
	 * @var currency
	 */
	private $currency;
	
	/**
	 * @var date
	 */
	private $date;
	
	/**
	 * @var date_of_birth
	 */
	private $date_of_birth;
	
	/**
	 * @var email
	 */
	private $email;
	
	/**
	 * @var first_name
	 */
	private $first_name;
	
	/**
	 * Used for dhl_endorsement. By choosing the handling option abandon, your 
	 * parcel will not be returned to you, but rather auctioned off or destroyed 
	 * by the recipient countrys' postal company. You will not be charged with a 
	 * return fee for this option. When using the option return_immediately, the 
	 * shipment will be returned to you and you will be charged for returning it.
	 * 
	 * @var handling
	 */
	private $handling; // HandlingType
	
	/**
	 * Type of ID document that should be used for verifying a Hermes recipient
	 * 
	 * @var id_type
	 */
	private $id_type; // IdType
	
	/**
	 * ID number from the document
	 * 
	 * @var id_number
	 */
	private $id_number;
	
	/**
	 * Language the customer should be notified in (ISO-639-1 format)
	 * 
	 * @var language
	 */
	private $language;
	
	/**
	 * @var last_name
	 */
	private $last_name;
	
	/**
	 * @var message
	 */
	private $message;
	
	/**
	 * @var minimum_age
	 */
	private $minimum_age;
	
	/**
	 * @var phone
	 */
	private $phone;
	
	/**
	 * Text that should be displayed as the reason for transfer
	 * 
	 * @var reference1
	 */
	private $reference1;
	
	/**
	 * Key that identifies the hazardous goods regulation class
	 * 
	 * @var regulation_class
	 */
	private $regulation_class; // RegulationClass
	
	/**
	 * @var sms
	 */
	private $sms;
	
	/**
	 * @var time_of_day_earliest
	 */
	private $time_of_day_earliest;
	
	/**
	 * @var time_of_day_latest
	 */
	private $time_of_day_latest;
	
	/**
	 * @var name
	 */
	private $name;
	
	/**
	 * @var properties
	 */
	private $properties;
	
	/**
	 * Properties constructor.
	 * 
	 * @return void
	 */
	public function __construct() {
		$this->required = [];
	}
	
	/**
	 * Getter method for amount.
	 * @return float The amount.
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Setter method for amount.
	 * @param float $amount The amount to set.
	 * @return void
	 */
	public function set_amount( float $amount ) : void {
		$this->amount = $amount;
	}

	/**
	 * Getter method for bank_account_holder.
	 * @return string The bank_account_holder.
	 */
	public function get_bank_account_holder() {
		return $this->bank_account_holder;
	}

	/**
	 * Setter method for bank_account_holder.
	 * @param string $bank_account_holder The bank_account_holder to set.
	 * @return void
	 */
	public function set_bank_account_holder( string $bank_account_holder ) : void {
		$this->bank_account_holder = $bank_account_holder;
	}

	/**
	 * Getter method for bank_account_number.
	 * @return string The bank_account_number.
	 */
	public function get_bank_account_number() {
		return $this->bank_account_number;
	}

	/**
	 * Setter method for bank_account_number.
	 * @param string $bank_account_number The bank_account_number to set.
	 * @return void
	 */
	public function set_bank_account_number( string $bank_account_number ) : void {
		$this->bank_account_number = $bank_account_number;
	}

	/**
	 * Getter method for bank_code.
	 * @return string The bank_code.
	 */
	public function get_bank_code() {
		return $this->bank_code;
	}

	/**
	 * Setter method for bank_code.
	 * @param string $bank_code The bank_code to set.
	 * @return void
	 */
	public function set_bank_code( string $bank_code ) : void {
		$this->bank_code = $bank_code;
	}

	/**
	 * Getter method for bank_name.
	 * @return string The bank_name.
	 */
	public function get_bank_name() {
		return $this->bank_name;
	}

	/**
	 * Setter method for bank_name.
	 * @param string $bank_name The bank_name to set.
	 * @return void
	 */
	public function set_bank_name( string $bank_name ) : void {
		$this->bank_name = $bank_name;
	}

	/**
	 * Getter method for currency.
	 * @return string The currency.
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * Setter method for currency.
	 * @param string $currency The currency to set.
	 * @return void
	 */
	public function set_currency( string $currency ) : void {
		$this->currency = $currency;
	}

	/**
	 * Getter method for date.
	 * @return string The date.
	 */
	public function get_date() {
		return $this->date;
	}

	/**
	 * Setter method for date.
	 * @param string $date The date to set.
	 * @return void
	 */
	public function set_date( string $date ) : void {
		$this->date = $date;
	}

	/**
	 * Getter method for date_of_birth.
	 * @return string The date_of_birth.
	 */
	public function get_date_of_birth() {
		return $this->date_of_birth;
	}

	/**
	 * Setter method for date_of_birth.
	 * @param string $date_of_birth The date_of_birth to set.
	 * @return void
	 */
	public function set_date_of_birth( string $date_of_birth ) : void {
		$this->date_of_birth = $date_of_birth;
	}

	/**
	 * Getter method for email.
	 * @return string The email.
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Setter method for email.
	 * @param string $email The email to set.
	 * @return void
	 */
	public function set_email( string $email ) : void {
		$this->email = $email;
	}

	/**
	 * Getter method for first_name.
	 * @return string The first_name.
	 */
	public function get_first_name() {
		return $this->first_name;
	}

	/**
	 * Setter method for first_name.
	 * @param string $first_name The first_name to set.
	 * @return void
	 */
	public function set_first_name( string $first_name ) : void {
		$this->first_name = $first_name;
	}

	/**
	 * Getter method for handling.
	 * @return string The handling.
	 */
	public function get_handling() {
		return $this->handling;
	}

	/**
	 * Setter method for handling.
	 * @param string $handling The handling to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_handling( string $handling ) : void {
		if ( ! HandlingType::is_valid_value( HandlingType::get_class_name(), $handling ) ) {
			throw new ApiException( 'Invalid handling type: ' . $handling );
		}
		$this->handling = $handling;
	}

	/**
	 * Getter method for id_type.
	 * @return string The id_type.
	 */
	public function get_id_type() {
		return $this->id_type;
	}

	/**
	 * Setter method for id_type.
	 * @param string $id_type The id_type to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_id_type( string $id_type ) : void {
		if ( ! IdType::is_valid_value( IdType::get_class_name(), $id_type ) ) {
			throw new ApiException( 'Invalid id type: ' . $id_type );
		}
		$this->id_type = $id_type;
	}

	/**
	 * Getter method for id_number.
	 * @return string The id_number.
	 */
	public function get_id_number() {
		return $this->id_number;
	}

	/**
	 * Setter method for id_number.
	 * @param string $id_number The id_number to set.
	 * @return void
	 */
	public function set_id_number( string $id_number ) : void {
		$this->id_number = $id_number;
	}

	/**
	 * Getter method for language.
	 * @return string The language.
	 */
	public function get_language() {
		return $this->language;
	}

	/**
	 * Setter method for language.
	 * @param string $language The language to set.
	 * @return void
	 */
	public function set_language( string $language ) : void {
		$this->language = $language;
	}

	/**
	 * Getter method for last_name.
	 * @return string The last_name.
	 */
	public function get_last_name() {
		return $this->last_name;
	}

	/**
	 * Setter method for last_name.
	 * @param string $last_name The last_name to set.
	 * @return void
	 */
	public function set_last_name( string $last_name ) : void {
		$this->last_name = $last_name;
	}

	/**
	 * Getter method for message.
	 * @return string The message.
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Setter method for message.
	 * @param string $message The message to set.
	 * @return void
	 */
	public function set_message( string $message ) : void {
		$this->message = $message;
	}

	/**
	 * Getter method for minimum_age.
	 * @return string The minimum_age.
	 */
	public function get_minimum_age() {
		return $this->minimum_age;
	}

	/**
	 * Setter method for minimum_age.
	 * @param string $minimum_age The minimum_age to set.
	 * @return void
	 */
	public function set_minimum_age( string $minimum_age ) : void {
		$this->minimum_age = $minimum_age;
	}

	/**
	 * Getter method for phone.
	 * @return string The phone.
	 */
	public function get_phone() {
		return $this->phone;
	}

	/**
	 * Setter method for phone.
	 * @param string $phone The phone to set.
	 * @return void
	 */
	public function set_phone( string $phone ) : void {
		$this->phone = $phone;
	}

	/**
	 * Getter method for reference1.
	 * @return string The reference1.
	 */
	public function get_reference1() {
		return $this->reference1;
	}

	/**
	 * Setter method for reference1.
	 * @param string $reference1 The reference1 to set.
	 * @return void
	 */
	public function set_reference1( string $reference1 ) : void {
		$this->reference1 = $reference1;
	}

	/**
	 * Getter method for regulation_class.
	 * @return string The regulation_class.
	 */
	public function get_regulation_class() {
		return $this->regulation_class;
	}

	/**
	 * Setter method for regulation_class.
	 * @param string $regulation_class The regulation_class to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_regulation_class( string $regulation_class ) : void {
		if ( ! RegulationClassType::is_valid_value( RegulationClassType::get_class_name(), $regulation_class ) ) {
			throw new ApiException( 'Invalid id type: ' . $regulation_class );
		}
		$this->regulation_class = $regulation_class;
	}

	/**
	 * Getter method for sms.
	 * @return string The sms.
	 */
	public function get_sms() {
		return $this->sms;
	}

	/**
	 * Setter method for sms.
	 * @param string $sms The sms to set.
	 * @return void
	 */
	public function set_sms( string $sms ) : void {
		$this->sms = $sms;
	}

	/**
	 * Getter method for time_of_day_earliest.
	 * @return string The time_of_day_earliest.
	 */
	public function get_time_of_day_earliest() {
		return $this->time_of_day_earliest;
	}

	/**
	 * Setter method for time_of_day_earliest.
	 * @param string $time_of_day_earliest The time_of_day_earliest to set.
	 * @return void
	 */
	public function set_time_of_day_earliest( string $time_of_day_earliest ) : void {
		$this->time_of_day_earliest = $time_of_day_earliest;
	}

	/**
	 * Getter method for time_of_day_latest.
	 * @return string The time_of_day_latest.
	 */
	public function get_time_of_day_latest() {
		return $this->time_of_day_latest;
	}

	/**
	 * Setter method for time_of_day_latest.
	 * @param string $time_of_day_latest The time_of_day_latest to set.
	 * @return void
	 */
	public function set_time_of_day_latest( string $time_of_day_latest ) : void {
		$this->time_of_day_latest = $time_of_day_latest;
	}
	
	/**
	 * Getter method for name.
	 * @return string The name.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Setter method for name.
	 * @param string $name The name to set.
	 * @return void
	 */
	public function set_name( string $name ) : void {
		$this->name = $name;
	}
	
	/**
	 * Getter method for properties.
	 * @return array The properties.
	 */
	public function get_properties() {
		return $this->properties;
	}

	/**
	 * Setter method for properties.
	 * @param array $properties The properties to set.
	 * @return void
	 */
	public function set_properties( array $properties ) : void {
		$this->properties = $properties;
	}	
	
	/**
	 * Getter method for other fields.
	 * @return array The other fields.
	 */
	private function get_other_fields() {
		return [
			'amount',
			'bank_account_holder',
			'bank_account_number',
			'bank_code',
			'bank_name',
			'currency',
			'date',
			'date_of_birth',
			'email',
			'first_name',
			'handling',
			'id_type',
			'id_number',
			'language',
			'last_name',
			'message',
			'minimum_age',
			'phone',
			'reference1',
			'regulation_class',
			'sms',
			'time_of_day_earliest',
			'time_of_day_latest',
			'name',
			'properties'
		];
	}
	
	/**
	 * Getter method for parameter array.
	 * @return array The class object as array.
	 */
	public function to_array() : array {
		
		$result = parent::to_array();
		foreach( $this->get_required_fields() as $field ) {
			$result[$field] = $this->{"get_{$field}"}();
		}
		
		foreach( $this->get_other_fields() as $field ) {
			if ( ! empty( $this->{"get_{$field}"}() ) ) {
				$result[$field] = $this->{"get_{$field}"}();
			}
		}
		
		if ( empty( $result ) ) {
			$result[''] = ''; 
		}
		
		return $result;
	}
}
