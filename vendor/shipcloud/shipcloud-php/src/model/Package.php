<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\DeclaredValue;
use shipcloud\phpclient\model\PackageType;

/**
 * Package class defines package dimensions.
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
class Package extends AbstractApiObject {
	
	/**
	 * @var width
	 */
	private $width;
	
	/**
	 * @var height
	 */
	private $height;
	
	/**
	 * @var length
	 */
	private $length;
	
	/**
	 * @var weight
	 */
	private $weight;
	
	/**
	 * Use this to book additional insurance or expand the liability 
	 * for a shipment. Caution: Please keep in mind that additional 
	 * fees are charged by the carrier.
	 * 
	 * @var declared_value
	 * @see DeclaredValue
	 */
	private $declared_value;
	
	/**
	 * Text that describes the contents of the package. This parameter is 
	 * mandatory if you're using UPS with service 'returns'.
	 * 
	 * @var description
	 */
	private $description;
	
	/**
	 * Defines packages of being of a certain type - if no value is given, 
	 * parcel will be used.
	 * 
	 * @var type
	 * @see PackageType
	 */
	private $type;
	
	/**
	 * Properties constructor.
	 * 
	 * @param void
	 * @return void
	 */
	public function __construct( float $width, float $height, float $length, float $weight ) {
		
		if ( empty( $width ) ) {
			throw new ApiException( 'Package width must not be empty!' );
		}
		if ( ! is_numeric( $width ) ) {
			throw new ApiException( 'Package width must be numeric!' );
		}
		if ( floatval( $width ) <= 0 ) {
			throw new ApiException( 'Package width must be greater than zero!' );
		}
		
		if ( empty( $height ) ) {
			throw new ApiException( 'Package height must not be empty!' );
		}
		if ( ! is_numeric( $height ) ) {
			throw new ApiException( 'Package height must be numeric!' );
		}
		if ( floatval( $height ) <= 0 ) {
			throw new ApiException( 'Package height must be greater than zero!' );
		}
		
		if ( empty( $length ) ) {
			throw new ApiException( 'Package length must not be empty!' );
		}
		if ( ! is_numeric( $length ) ) {
			throw new ApiException( 'Package length must be numeric!' );
		}
		if ( floatval( $length ) <= 0 ) {
			throw new ApiException( 'Package length must be greater than zero!' );
		}
		
		if ( empty( $weight ) ) {
			throw new ApiException( 'Package weight must not be empty!' );
		}
		if ( ! is_numeric( $weight ) ) {
			throw new ApiException( 'Package weight must be numeric!' );
		}
		if ( floatval( $weight ) <= 0 ) {
			throw new ApiException( 'Package weight must be greater than zero!' );
		}
		
		$this->width  = $width;
		$this->height = $height;
		$this->length = $length;
		$this->weight = $weight;
		
		$this->required = [ "width", "height", "length", "weight" ];
	}
	
	/**
	 * Getter method for width.
	 * @return float The width.
	 */
	public function get_width() : float {
		return $this->width;
	}
	
	/**
	 * Setter method for width.
	 * @param float $width The width to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_width( float $width ) : void {
		if ( empty( $width ) ) {
			throw new ApiException( 'Package width must not be empty!' );
		}
		if ( ! is_numeric( $width ) ) {
			throw new ApiException( 'Package width must be numeric!' );
		}
		if ( floatval( $width ) <= 0 ) {
			throw new ApiException( 'Package width must be greater than zero!' );
		}
		$this->width = $width;
	}
	
	
	/**
	 * Getter method for height.
	 * @return float The height.
	 */
	public function get_height() : float {
		return $this->height;
	}
	
	/**
	 * Setter method for height.
	 * @param float $height The height to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_height( float $height ) : void {
		if ( empty( $height ) ) {
			throw new ApiException( 'Package height must not be empty!' );
		}
		if ( ! is_numeric( $height ) ) {
			throw new ApiException( 'Package height must be numeric!' );
		}
		if ( floatval( $height ) <= 0 ) {
			throw new ApiException( 'Package height must be greater than zero!' );
		}
		$this->height = $height;
	}
	
	/**
	 * Getter method for length.
	 * @return float The length.
	 */
	public function get_length() : float {
		return $this->length;
	}
	
	/**
	 * Setter method for length.
	 * @param float $length The length to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_length( float $length ) : void {
		if ( empty( $length ) ) {
			throw new ApiException( 'Package length must not be empty!' );
		}
		if ( ! is_numeric( $length ) ) {
			throw new ApiException( 'Package length must be numeric!' );
		}
		if ( floatval( $length ) <= 0 ) {
			throw new ApiException( 'Package length must be greater than zero!' );
		}
		$this->length = $length;
	}
	
	/**
	 * Getter method for weight.
	 * @return float The weight.
	 */
	public function get_weight() : float {
		return $this->weight;
	}
	
	/**
	 * Setter method for weight.
	 * @param float $weight The weight to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_weight( float $weight ) : void {
		if ( empty( $weight ) ) {
			throw new ApiException( 'Package weight must not be empty!' );
		}
		if ( ! is_numeric( $weight ) ) {
			throw new ApiException( 'Package weight must be numeric!' );
		}
		if ( floatval( $weight ) <= 0 ) {
			throw new ApiException( 'Package weight must be greater than zero!' );
		}
		$this->weight = $weight;
	}
	
	/**
	 * Getter method for declared value.
	 * @return DeclaredValue The declared value.
	 */
	public function get_declared_value() {
		return $this->declared_value;
	}
	
	/**
	 * Setter method for declared value.
	 * @param DeclaredValue $declared_value The declared value to set.
	 * @return void
	 * @see DeclaredValue
	 */
	public function set_declared_value( DeclaredValue $declared_value ) : void {
		$this->declared_value = $declared_value;
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
	 * Getter method for type.
	 * @return string The type.
	 */
	public function get_type() {
		return $this->type;
	}
	
	/**
	 * Setter method for type.
	 * @param string $type The type to set.
	 * @return void
	 * @see PackageType
	 * @throws ApiException
	 */
	public function set_type( string $type ) : void {
		if ( ! PackageType::is_valid_value( PackageType::get_class_name(), $type ) ) {
			throw new ApiException( 'Invalid package type: ' . $type );
		}
		$this->type = $type;
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
		
		if ( ! empty( $this->get_declared_value() ) ) {
			$result['declared_value'] = $this->get_declared_value()->to_array();
		}
		if ( ! empty( $this->get_description() ) ) {
			$result['description'] = $this->get_description();
		}
		if ( ! empty( $this->get_type() ) ) {
			$result['type'] = $this->get_type();
		}
		
		return $result;
	}
}
