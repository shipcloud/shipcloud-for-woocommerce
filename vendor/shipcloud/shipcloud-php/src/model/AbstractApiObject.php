<?php

namespace shipcloud\phpclient\model;

/**
 * AbstractApiObject class 
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
abstract class AbstractApiObject {
	
	/**
	 * @var id
	 */
	protected $id;
	
	/**
	 * @var required
	 */
	protected $required;
	
	/**
	 * Private constructor to avoid instanciation.
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Getter method for id.
	 * @return string|null The id.
	 */
	public function get_id() {
		return $this->id;
	}
	
	/**
	 * Setter method for id.
	 * @param string $id The id to set.
	 * @return void
	 */
	public function set_id( string $id ) : void {
		$this->id = $id;
	}
	
	/**
	 * Getter method for id.
	 * @return array The id.
	 */
	public function get_required_fields() : array {
		return $this->required;
	}
	
	/**
	 * Getter method for parameter array.
	 * @return array The address object as array.
	 */
	public function to_array() : array {
		
		$result = [];
		if ( ! empty( $this->get_id() ) ) {
			$result['id'] = $this->get_id();
		}
		
		return $result;
	}
	
	/**
	 * Getter method for string representation.
	 * @return string The object as string representation.
	 */
	public function to_string() {
		return json_encode( $this->to_array() );
	}
	
}
