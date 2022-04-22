<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;

/**
 * PickupTime class defines a time window when the carrier should pickup shipments.
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
class PickupTime extends AbstractApiObject {
	
	/**
	 * @var earliest
	 */
	private $earliest;
	
	/**
	 * @var latest
	 */
	private $latest;
	
	/**
	 * PickupTime constructor.
	 * 
	 * @param string $earliest
	 * @param string $latest
	 * @return void
	 * @throws ApiException
	 */
	public function __construct( string $earliest, string $latest ) {
		
		if ( empty( $earliest ) ) {
			throw new ApiException( 'Earliest must not be empty!' );
		}
		if ( empty( $latest ) ) {
			throw new ApiException( 'Latest must not be empty!' );
		}
		
		$this->earliest = $earliest;
		$this->latest 	= $latest;
		
		$this->required = [ "earliest", "latest" ];
	}
	
	/**
	 * Getter method for earliest.
	 * @return string The earliest.
	 */
	public function get_earliest() : string {
		return $this->earliest;
	}
	
	/**
	 * Setter method for earliest.
	 * @param string $earliest The earliest to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_earliest( string $earliest ) : void {
		if ( empty( $earliest ) ) {
			throw new ApiException( 'Earliest must not be empty!' );
		}
		$this->earliest = $earliest;
	}
	
	/**
	 * Getter method for latest.
	 * @return string The latest.
	 */
	public function get_latest() : string {
		return $this->latest;
	}
	
	/**
	 * Setter method for latest.
	 * @param string $latest The latest to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_latest( string $latest ) : void {
		if ( empty( $latest ) ) {
			throw new ApiException( 'Latest must not be empty!' );
		}
		$this->latest = $latest;
	}
	
	/**
	 * Getter method for parameter array.
	 * @return array The address object as array.
	 */
	public function to_array() : array {
		
		$result = parent::to_array();
		foreach( $this->get_required_fields() as $field ) {
			$result[$field] = $this->{"get_{$field}"}();
		}
		
		return $result;
	}
}
