<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\EventType;

/**
 * Webhook class allows you to subscribe to certain events.
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
class Webhook extends AbstractApiObject {
	
	/**
	 * @var string
	 */
	 private $url;
			
	/**
	 * @var array
	 */
	 private $event_types;
	
	/**
	 * Webhook constructor.
	 * 
	 * @param string $url
	 * @param array $event_types
	 * @return void
	 * @throws ApiException
	 */
	public function __construct( string $url, array $event_types = [ EventType::ALL ] ) {
		
		if ( empty( $url ) ) {
			throw new ApiException( 'URL must not be empty!' );
		}
		if ( empty( $event_types ) ) {
			throw new ApiException( 'Event types must not be empty!' );
		}
		
		$this->url 	  		= $url;
		$this->event_types 	= $event_types;
		
		$this->required = [ "url", "event_types" ];
	}
	
	/**
	 * Getter method for url.
	 * @return string The url.
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Setter method for url.
	 * @param string $url The url to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_url( string $url ) : void {
		if ( empty( $url ) ) {
			throw new ApiException( 'URL must not be empty!' );
		}
		$this->url = $url;
	}

	/**
	 * Getter method for event_types.
	 * @return array The event_types.
	 */
	public function get_event_types() {
		return $this->event_types;
	}

	/**
	 * Setter method for event_types.
	 * @param array $event_types The event_types to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_event_types( array $event_types ) : void {
		if ( empty( $event_types ) ) {
			throw new ApiException( 'Event types must not be empty!' );
		}
		$this->event_types = $event_types;
	}
	
	/**
	 * Getter method for parameter array.
	 * @return array The class object as array.
	 */
	public function to_array() : array {
		
		$result = parent::to_array();
		if ( ! empty( $this->get_url() ) ) {
		    $result['url'] = $this->get_url();
		}
		if ( ! empty( $this->get_event_types() ) ) {
		    $result['event_types'] = $this->get_event_types();
		}
		
		return $result;
	}
}
