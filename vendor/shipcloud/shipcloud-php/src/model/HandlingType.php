<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\model\Enum;

/**
 * HandlingType enumeration class 
 *
 * @category 	Enum
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
class HandlingType extends Enum {
	
	const ABANDON 				= 'abandon';
	
	const RETURN_IMMEDIATELY 	= 'return_immediately';
	
	public static function get_class_name() : string {
		return '\\' . __CLASS__;
	}
	
}