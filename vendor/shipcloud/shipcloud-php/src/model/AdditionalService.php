<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\AdditionalServiceType;
use shipcloud\phpclient\model\Properties;

/**
 * AdditionalService class
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
class AdditionalService extends AbstractApiObject {
	
	/**
	 * @var name
	 */
	private $name;
	
	/**
	 * @var properties
	 */
	private $properties;
	
	/**
	 * AdditionalService constructor.
	 *
	 * @param string   		$name
	 * @param Properties 	$properties
	 * @return void
	 * @see AdditionalServiceType
	 * @see Properties
	 * @throws ApiException
	 */
	public function __construct( string $name, Properties $properties = null ) {
		
		if ( ! AdditionalServiceType::is_valid_value( AdditionalServiceType::get_class_name(), $name ) ) {
			throw new ApiException( 'Invalid service name: ' . $name );
		}
		
		$this->name 	  = $name;
		$this->properties = $properties;
		
		$this->required = [ "name" ];
	}
	
	/**
	 * Getter method for name.
	 * @return string The name.
	 */
	public function get_name() : string {
		return $this->name;
	}
	
	/**
	 * Setter method for name.
	 * @param string $name The name to set.
	 * @return void
	 * @see AdditionalServiceType
	 * @throws ApiException
	 */
	public function set_name( string $name ) : void {
		if ( ! AdditionalServiceType::is_valid_value( AdditionalServiceType::get_class_name(), $name ) ) {
			throw new ApiException( 'Invalid service name: ' . $name );
		}
		$this->name = $name;
	}
	
	/**
	 * Getter method for properties.
	 * @return Properties The properties.
	 * @see Properties
	 */
	public function get_properties() {
		return $this->properties;
	}
	
	/**
	 * Setter method for properties.
	 * @param Properties $properties The properties to set.
	 * @return void
	 * @see Properties
	 */
	public function set_properties( Properties $properties ) : void {
		$this->properties = $properties;
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
		
		if ( ! empty( $this->get_properties() ) ) {
			$result['properties'] = $this->get_properties()->to_array();
		}
		
		return $result;
	}
}
