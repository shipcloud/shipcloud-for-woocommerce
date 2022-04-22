<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\model\Enum;

/**
 * ServiceType enumeration class 
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
class ServiceType extends Enum {
	
	const CARGO_INTERNATIONAL_EXPRESS 	= 'cargo_international_express';
	
	const DHL_EUROPAKET 				= 'dhl_europaket';
	
	const DHL_PRIO 						= 'dhl_prio';
	
	const DHL_WARENPOST 				= 'dhl_warenpost';
	
	const DPAG_WARENPOST 				= 'dpag_warenpost';
	
	const DPAG_WARENPOST_SIGNATURE 		= 'dpag_warenpost_signature';
	
	const DPAG_WARENPOST_UNTRACKED 		= 'dpag_warenpost_untracked';
	
	const ECONOMY_SELECT 				= 'economy_select';
	
	const GLS_EXPRESS_0800 				= 'gls_express_0800';
	
	const GLS_EXPRESS_0900 				= 'gls_express_0900';
	
	const GLS_EXPRESS_1000 				= 'gls_express_1000';
	
	const GLS_EXPRESS_1200 				= 'gls_express_1200';
	
	const GLS_PICK_AND_SHIP 			= 'gls_pick_and_ship';
	
	const ONE_DAY 						= 'one_day';
	
	const ONE_DAY_EARLY 				= 'one_day_early';
	
	const RETURNS 						= 'returns';
	
	const SAME_DAY 						= 'same_day';
	
	const STANDARD 						= 'standard';
	
	const UPS_EXPEDITED 				= 'ups_expedited';
	
	const UPS_EXPRESS_1200 				= 'ups_express_1200';
	
	public static function get_class_name() : string {
		return '\\' . __CLASS__;
	}
	
	public static function get_default_service() : string {
		return ServiceType::STANDARD;
	}
	
}