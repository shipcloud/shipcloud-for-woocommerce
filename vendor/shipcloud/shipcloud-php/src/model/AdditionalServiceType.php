<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\model\Enum;

/**
 * AdditionalServiceType enumeration class 
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
class AdditionalServiceType extends Enum {
	
	const ADVANCE_NOTICE 					= 'advance_notice';
	
	const ANGEL_DE_DELIVERY_DATE_TIME 		= 'angel_de_delivery_date_time';
	
	const CASH_ON_DELIVERY 					= 'cash_on_delivery';
	
	const DELIVERY_DATE 					= 'delivery_date';
	
	const DELIVERY_NOTE 					= 'delivery_note';
	
	const DELIVERY_TIME 					= 'delivery_time';
	
	const DHL_ENDORSEMENT 					= 'dhl_endorsement';
	
	const DHL_GOGREEN 						= 'dhl_gogreen';
	
	const DHL_IDENT_CHECK 					= 'dhl_ident_check';
	
	const DHL_NAMED_PERSON_ONLY 			= 'dhl_named_person_only';
	
	const DHL_NO_NEIGHBOR_DELIVERY 			= 'dhl_no_neighbor_delivery';
	
	const DHL_PARCEL_OUTLET_ROUTING 		= 'dhl_parcel_outlet_routing';
	
	const DHL_PREFERRED_NEIGHBOR 			= 'dhl_preferred_neighbor';
	
	const DPD_FOOD 							= 'dpd_food';
	
	const DROP_AUTHORIZATION 				= 'drop_authorization';
	
	const GLS_GUARANTEED_24_SERVICE 		= 'gls_guaranteed24service';
	
	const HAZARDOUS_GOODS 					= 'hazardous_goods';
	
	const HERMES_IDENTSERVICE 				= 'hermes_identservice';
	
	const PREMIUM_INTERNATIONAL 			= 'premium_international';
	
	const SATURDAY_DELIVERY 				= 'saturday_delivery';
	
	const UPS_ACCESS_POINT_NOTIFICATION 	= 'ups_access_point_notification';
	
	const UPS_ADULT_SIGNATURE 				= 'ups_adult_signature';
	
	const UPS_CARBON_NEUTRAL 				= 'ups_carbon_neutral';
	
	const UPS_DIRECT_DELIVERY_ONLY 			= 'ups_direct_delivery_only';
	
	const UPS_SIGNATURE_REQUIRED 			= 'ups_signature_required';
	
	const VISUAL_AGE_CHECK 					= 'visual_age_check';
	
	public static function get_class_name() : string {
		return '\\' . __CLASS__;
	}
	
}