<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\DropOffPoint;

/**
 * Address class 
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
class Address extends AbstractApiObject {
	
	/**
	 * @var string
	 */
	private $company;

	/**
	 * @var string
	 */
	private $first_name;

	/**
	 * @var string
	 */
	private $last_name;
	
	/**
	 * @var string
	 */
	private $care_of;
	
	/**
	 * @var string
	 */
	private $street;

	/**
	 * @var string
	 */
	private $street_no;
	
	/**
	 * @var string
	 */
	private $city;
	
	/**
	 * @var string
	 */
	private $zip_code;
	
	/**
	 * @var string
	 */
	private $state;

	/**
	 * Country as uppercase ISO 3166-1 alpha-2 code.
	 * 
	 * @var string
	 */
	private $country;
	
	/**
	 * Telephone number (mandatory when the following terms apply - when 
	 * carrier is UPS: service is one_day or one_day_early or ship to 
	 * country is different than ship from country. Carrier is DHL Express: 
	 * always provide phone number.).
	 * 
	 * @var string
	 */
	private $phone;
	
	/**
	 * Corresponding email address. Some carrier will need this to be provided 
	 * (e.g. to notify the sender/receiver).
	 * 
	 * @var string
	 */
	private $email;
	
	/**
	 * @var DropOffPoint
	 */
	private $drop_off_point;
	
	/**
	 * Address constructor.
	 *
	 * @param string   $last_name
	 * @param string   $street
	 * @param string   $street_no
	 * @param string   $city
	 * @param string   $zip_code
	 * @param string   $country
	 * @return void
	 * @throws ApiException
	 */
	public function __construct( string $last_name, string $company, string $street, string $street_no, string $city, string $zip_code, string $country ) {
		
		if ( empty( $last_name ) && empty( $company ) ) {
			throw new ApiException( 'Last name or company must not be empty!' );
		}
		if ( empty( $street ) ) {
			throw new ApiException( 'Street must not be empty!' );
		}
		if ( empty( $street_no ) ) {
			throw new ApiException( 'Street_no must not be empty!' );
		}
		if ( empty( $city ) ) {
			throw new ApiException( 'City must not be empty!' );
		}
		if ( empty( $zip_code ) ) {
			throw new ApiException( 'Zipcode must not be empty!' );
		}
		if ( empty( $country ) ) {
			throw new ApiException( 'Country must not be empty!' );
		}
		if ( ! preg_match( '/^[A-Z]{2}$/', $country ) ) {
			throw new ApiException( 'Country is required as uppercase ISO 3166-1 alpha-2 code!' );
		}
		
		$this->last_name  	= $last_name;
		$this->street 		= $street;
		$this->street_no  	= $street_no;
		$this->city    		= $city;
		$this->zip_code  	= $zip_code;
		$this->country  	= $country;
    $this->company  	= $company;
		
		$this->required = [ "street", "street_no", "city", "zip_code", "country" ];
	}
	
	/**
	 * Getter method for company.
	 * @return string The company.
	 */
	public function get_company() {
		return $this->company;
	}
	
	/**
	 * Setter method for company.
	 * @param string $company The company to set.
	 * @return void
	 */
	public function set_company( string $company ) : void {
		$this->company = $company;
	}
	
	/**
	 * Getter method for first name.
	 * @return string The first name.
	 */
	public function get_first_name() {
		return $this->first_name;
	}
	
	/**
	 * Setter method for first name.
	 * @param string $first_name The first name to set.
	 * @return void
	 */
	public function set_first_name( string $first_name ) : void {
		$this->first_name = $first_name;
	}

	/**
	 * Getter method for last name.
	 * @return string The last name.
	 */
	public function get_last_name() : string {
		return $this->last_name;
	}
	
	/**
	 * Setter method for last name.
	 * @param string $last_name The last name to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_last_name( string $last_name ) : void {
		if ( empty( $last_name ) ) {
			throw new ApiException( 'Last_name must not be empty!' );
		}
		$this->last_name = $last_name;
	}
	
	/**
	 * Getter method for care of.
	 * @return string The care of.
	 */
	public function get_care_of() {
		return $this->care_of;
	}
	
	/**
	 * Setter method for care of.
	 * @param string $care_of The care of to set.
	 * @return void
	 */
	public function set_care_of( string $care_of ) : void {
		$this->care_of = $care_of;
	}
	
	/**
	 * Getter method for street.
	 * @return string The street.
	 */
	public function get_street() : string {
		return $this->street;
	}
	
	/**
	 * Setter method for street.
	 * @param string $street The street to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_street( string $street ) : void {
		if ( empty( $street ) ) {
			throw new ApiException( 'Street must not be empty!' );
		}
		$this->street = $street;
	}
	
	/**
	 * Getter method for street number.
	 * @return string The street_no.
	 */
	public function get_street_no() : string {
		return $this->street_no;
	}
	
	/**
	 * Setter method for street number.
	 * @param string $street_no The street number to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_street_no( string $street_no ) : void {
		if ( empty( $street_no ) ) {
			throw new ApiException( 'Street_no must not be empty!' );
		}
		$this->street_no = $street_no;
	}
	
	/**
	 * Getter method for city.
	 * @return string The city.
	 */
	public function get_city() : string {
		return $this->city;
	}
	
	/**
	 * Setter method for city.
	 * @param string $city The city to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_city( string $city ) : void {
		if ( empty( $city ) ) {
			throw new ApiException( 'City must not be empty!' );
		}
		$this->city = $city;
	}
	
	/**
	 * Getter method for zipcode.
	 * @return string The zipcode.
	 */
	public function get_zip_code() : string {
		return $this->zip_code;
	}
	
	/**
	 * Setter method for zipcode.
	 * @param string $zipcode The zipcode to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_zip_code( string $zipcode ) : void {
		if ( empty( $zip_code ) ) {
			throw new ApiException( 'Zipcode must not be empty!' );
		}
		$this->zip_code = $zipcode;
	}
	
	/**
	 * Getter method for state.
	 * @return string The state.
	 */
	public function get_state() {
		return $this->state;
	}
	
	/**
	 * Setter method for state.
	 * @param string $state The state to set.
	 * @return void
	 */
	public function set_state( string $state ) : void {
		$this->state = $state;
	}
	
	/**
	 * Getter method for country.
	 * @return string The country.
	 */
	public function get_country() : string {
		return $this->country;
	}
	
	/**
	 * Setter method for country.
	 * @param string $country The country to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_country( string $country ) : void {
		if ( empty( $country ) ) {
			throw new ApiException( 'Country must not be empty!' );
		}
		if ( ! preg_match( '/^[A-Z]{2}$/', $country ) ) {
			throw new ApiException( 'Country is required as uppercase ISO 3166-1 alpha-2 code!' );
		}
		$this->country = $country;
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
	 * Getter method for drop off point.
	 * @return DropOffPoint The drop off point.
	 */
	public function get_drop_off_point() {
		return $this->drop_off_point;
	}
	
	/**
	 * Setter method for drop off point.
	 * @param DropOffPoint $drop_off_point The drop off point to set.
	 * @return void
	 */
	public function set_drop_off_point( DropOffPoint $drop_off_point ) : void {
		$this->drop_off_point = $drop_off_point;
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
		
		if ( ! empty( $this->get_company() ) ) {
			$result['company'] = $this->get_company();
		}
		if ( ! empty( $this->get_first_name() ) ) {
			$result['first_name'] = $this->get_first_name();
		}
		if ( ! empty( $this->get_first_name() ) ) {
			$result['last_name'] = $this->get_last_name();
		}
		if ( ! empty( $this->get_care_of() ) ) {
			$result['care_of'] = $this->get_care_of();
		}
		if ( ! empty( $this->get_state() ) ) {
			$result['state'] = $this->get_state();
		}
		if ( ! empty( $this->get_phone() ) ) {
			$result['phone'] = $this->get_phone();
		}
		if ( ! empty( $this->get_email() ) ) {
			$result['email'] = $this->get_email();
		}
		if ( ! empty( $this->get_drop_off_point() ) ) {
			$result['drop_off_point'] = $this->get_drop_off_point()->to_array();
		}
		
		return $result;
	}
}
