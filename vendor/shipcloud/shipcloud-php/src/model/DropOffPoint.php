<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\DropOffPointType;

/**
 * DropOffPoint class 
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
class DropOffPoint extends AbstractApiObject {
	
	/**
	 * Identifier for where the shipment should be dropped off.
	 * 
	 * @var string
	 */
	private $drop_off_point_id;
	
	/**
	 * The type of the dropoff location.
	 * 
	 * @var string
	 * @see DropOffPointType
	 */
	private $type;
	
	/**
	 * DropOffPoint constructor.
	 *
	 * @param string   $type
	 * @param string   $drop_off_point_id
	 * @return void
	 * @see DropOffPointType
	 * @throws ApiException
	 */
	public function __construct( string $type = DropOffPointType::PARCEL_SHOP, string $drop_off_point_id = null ) {
		
		if ( ! DropOffPointType::is_valid_value( DropOffPointType::get_class_name(), $type ) ) {
			throw new ApiException( 'Invalid dropoff point type: ' . $type );
		}
		if ( empty( $drop_off_point_id ) ) {
			throw new ApiException( 'Drop off point ID must not be empty!' );
		}
		
		$this->type 			 = $type;
		$this->drop_off_point_id = $drop_off_point_id;
		
		$this->required = [ "drop_off_point_id", "type" ];
	}
	
	/**
	 * Getter method for drop off point id.
	 * @return string The drop off point id.
	 */
	public function get_drop_off_point_id() : string {
		return ! empty( $this->drop_off_point_id ) ? $this->drop_off_point_id : '';
	}
	
	/**
	 * Setter method for drop off point id.
	 * @param string $id The drop off point id to set.
	 * @return void
	 */
	public function set_drop_off_point_id( string $id ) : void {
		if ( empty( $drop_off_point_id ) ) {
			throw new ApiException( 'Drop off point ID must not be empty!' );
		}
		$this->drop_off_point_id = $id;
	}
	
	/**
	 * Getter method for type.
	 * @return string The type.
	 */
	public function get_type() : string {
		return $this->type;
	}
	
	/**
	 * Setter method for type.
	 * @param string $type The type to set.
	 * @return void
	 * @see DropOffPointType
	 * @throws ApiException
	 */
	public function set_type( string $type ) : void {
		if ( ! DropOffPointType::is_valid_value( DropOffPointType::get_class_name(), $type ) ) {
			throw new ApiException( 'Invalid dropoff point type: ' . $type );
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
		
		return $result;
	}
}
