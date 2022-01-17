<?php

namespace shipcloud\phpclient;

use shipcloud\phpclient\Logger;

/**
 * ApiException Class 
 *
 * @category 	Class
 * @package  	shipcloud\phpclient
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
class ApiException extends \Exception {
	
	/**
	 * ApiException constructor.
	 * 
	 * @param string  $message
	 * @param integer $code
	 * @param Throwable $previous
	 */
	public function __construct( string $message = "", int $code = 0, Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
		Logger::get_instance()->error( $message );
	}
	
}
