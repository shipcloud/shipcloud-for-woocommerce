<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\ContentsType;

/**
 * CustomsDeclarationItem class represents a declaration of customs related information.
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
class CustomsDeclarationItem extends AbstractApiObject {
	
	/**
	 * Country as uppercase ISO 3166-1 alpha-2 code.
	 * 
	 * @var string
	 */
	 private $origin_country;
			
	/**
	 * Description of the item.
	 * 
	 * @var string
	 */
	 private $description;
			
	/**
	 * Customs tariff number.
	 * 
	 * @var string
	 * @see https://en.wikipedia.org/wiki/Harmonized_System#Tariffs_by_region for detailed information on region specific tariff numbers.
	 */
	 private $hs_tariff_number;
			
	/**
	 * Number that defines how many items of this kind are in the shipment.
	 * 
	 * @var int
	 */
	 private $quantity;
			
	/**
	 * Value of a single item of this kind.
	 * 
	 * @var string
	 */
	 private $value_amount;
			
	/**
	 * Net weight of a single item of this kind.
	 * 
	 * @var float
	 */
	 private $net_weight;
			
	/**
	 * Gross weight of a single item of this kind.
	 * 
	 * @var float
	 */
	 private $gross_weight;
	
	/**
	 * CustomsDeclarationItem constructor.
	 * 
	 * @param string $origin_country
	 * @param string $description 
	 * @param int 	 $quantity
	 * @param string $value_amount
	 * @param float  $net_weight
	 * @return void
	 * @throws ApiException
	 */
	public function __construct( string $origin_country, string $description = '', int $quantity, string $value_amount, float $net_weight ) {
		
		if ( empty( $origin_country ) || ! preg_match( '/^[A-Z]{2}$/', $origin_country ) ) {
			throw new ApiException( 'Country is required as uppercase ISO 3166-1 alpha-2 code!' );
		}
		if ( empty( $quantity ) ) {
			throw new ApiException( 'Quantity must not be empty!' );
		}
		if ( $quantity <= 0 ) {
			throw new ApiException( 'Quantity must be greater than zero!' );
		}
		
		$this->origin_country = $origin_country;
		$this->description = $description;
		$this->quantity = $quantity;
		$this->value_amount = $value_amount;
		$this->net_weight = $net_weight;
		
		$this->required = [ "origin_country", "description", "quantity", "value_amount", "net_weight" ];
	}
	
	/**
	 * Getter method for origin_country.
	 * @return string The origin_country.
	 */
	public function get_origin_country() {
		return $this->origin_country;
	}

	/**
	 * Setter method for origin_country.
	 * @param string $origin_country The origin_country to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_origin_country( string $origin_country ) : void {
		if ( empty( $origin_country ) || ! preg_match( '/^[A-Z]{2}$/', $origin_country ) ) {
			throw new ApiException( 'Country is required as uppercase ISO 3166-1 alpha-2 code!' );
		}
		$this->origin_country = $origin_country;
	}

	/**
	 * Getter method for description.
	 * @return string The description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Setter method for description.
	 * @param string $description The description to set.
	 * @return void
	 */
	public function set_description( string $description ) : void {
		$this->description = $description;
	}

	/**
	 * Getter method for hs_tariff_number.
	 * @return string The hs_tariff_number.
	 */
	public function get_hs_tariff_number() {
		return $this->hs_tariff_number;
	}

	/**
	 * Setter method for hs_tariff_number.
	 * @param string $hs_tariff_number The hs_tariff_number to set.
	 * @return void
	 */
	public function set_hs_tariff_number( string $hs_tariff_number ) : void {
		$this->hs_tariff_number = $hs_tariff_number;
	}

	/**
	 * Getter method for quantity.
	 * @return int The quantity.
	 */
	public function get_quantity() {
		return $this->quantity;
	}

	/**
	 * Setter method for quantity.
	 * @param int $quantity The quantity to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_quantity( int $quantity ) : void {
		if ( empty( $quantity ) ) {
			throw new ApiException( 'Quantity must not be empty!' );
		}
		if ( $quantity <= 0 ) {
			throw new ApiException( 'Quantity must be greater than zero!' );
		}
		$this->quantity = $quantity;
	}

	/**
	 * Getter method for value_amount.
	 * @return string The value_amount.
	 */
	public function get_value_amount() {
		return $this->value_amount;
	}

	/**
	 * Setter method for value_amount.
	 * @param string $value_amount The value_amount to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_value_amount( string $value_amount ) : void {
		if ( empty( $value_amount )  ) {
			throw new ApiException( 'Value amount must not be empty!' );
		}
		if ( is_numeric( $value_amount )  ) {
			throw new ApiException( 'Value amount must be numeric!' );
		}
		$this->value_amount = $value_amount;
	}

	/**
	 * Getter method for net_weight.
	 * @return float The net_weight.
	 */
	public function get_net_weight() {
		return $this->net_weight;
	}

	/**
	 * Setter method for net_weight.
	 * @param float $net_weight The net_weight to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_net_weight( float $net_weight ) : void {
		if ( empty( $net_weight )  ) {
			throw new ApiException( 'Net weight must not be empty!' );
		}
		$this->net_weight = $net_weight;
	}

	/**
	 * Getter method for gross_weight.
	 * @return float The gross_weight.
	 */
	public function get_gross_weight() {
		return $this->gross_weight;
	}

	/**
	 * Setter method for gross_weight.
	 * @param float $gross_weight The gross_weight to set.
	 * @return void
	 */
	public function set_gross_weight( float $gross_weight ) : void {
		$this->gross_weight = $gross_weight;
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
		
		if ( ! empty( $this->get_hs_tariff_number() ) ) {
		    $result['hs_tariff_number'] = $this->get_hs_tariff_number();
		}
		if ( ! empty( $this->get_gross_weight() ) ) {
		    $result['gross_weight'] = $this->get_gross_weight();
		}
		
		return $result;
	}
}
