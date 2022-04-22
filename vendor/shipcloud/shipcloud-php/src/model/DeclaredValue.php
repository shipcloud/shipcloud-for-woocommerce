<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\model\AbstractApiObject;

/**
 * DeclaredValue class 
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
class DeclaredValue extends AbstractApiObject {
	
	/**
	 * @var float
	 */
	private $amount;
	
	/**
	 * @var string
	 */
	private $currency;
	
	/**
	 * Properties constructor.
	 * 
	 * @param float  $amount
	 * @param string $currency
	 * @return void
	 * @throws ApiException
	 */
	public function __construct( float $amount, string $currency ) {
		
		if ( empty( $amount ) ) {
			throw new ApiException( 'Amount must not be empty!' );
		}
		if ( ! is_numeric( $amount ) ) {
			throw new ApiException( 'Amount must be numeric!' );
		}
		if ( floatval( $amount ) <= 0 ) {
			throw new ApiException( 'Amount must be greater than zero!' );
		}
		if ( empty( $currency ) ) {
			throw new ApiException( 'Currency must not be empty!' );
		}
		
		$this->amount 	= floatval( $amount );
		$this->currency = $currency;
		
		$this->required = [ "amount", "currency" ];
	}
	
	/**
	 * Getter method for amount.
	 * @return float The amount.
	 */
	public function get_amount() : float {
		return $this->amount;
	}
	
	/**
	 * Setter method for amount.
	 * @param float $amount The amount to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_amount( float $amount ) : void {
		if ( empty( $amount ) ) {
			throw new ApiException( 'Amount must not be empty!' );
		}
		if ( ! is_numeric( $amount ) ) {
			throw new ApiException( 'Amount must be numeric!' );
		}
		if ( floatval( $amount ) <= 0 ) {
			throw new ApiException( 'Amount must be greater than zero!' );
		}
		$this->amount = floatval( $amount );
	}
	
	/**
	 * Getter method for currency.
	 * @return float The currency.
	 */
	public function get_currency() : string {
		return $this->currency;
	}
	
	/**
	 * Setter method for currency.
	 * @param string $currency The amount to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_currency( string $currency ) : void {
		if ( empty( $currency ) ) {
			throw new ApiException( 'Currency must not be empty!' );
		}
		$this->currency = $currency;
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
		
		return $result;
	}
}
