<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\model\Enum;

/**
 * EventType enumeration class 
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
 * 
 * @see https://developers.shipcloud.io/concepts/#event-types
 */
class EventType extends Enum {
	
    const ALL 											= '*';
	
	const EXAMPLE_EVENT									= 'example.event';
    
	const SHIPMENT_ALL 									= 'shipment.*';
	
	const SHIPMENT_STATUS_DELETED						= 'shipment.status.deleted';
    
	const SHIPMENT_TRACKING_ALL 						= 'shipment.tracking.*';
    
	const SHIPMENT_TRACKING_LABEL_CREATED 				= 'shipment.tracking.label_created';
    
	const SHIPMENT_TRACKING_PICKED_UP 					= 'shipment.tracking.picked_up';
    
	const SHIPMENT_TRACKING_TRANSIT 					= 'shipment.tracking.transit';
    
	const SHIPMENT_TRACKING_OUT_FOR_DELIVERY 			= 'shipment.tracking.out_for_delivery';
    
	const SHIPMENT_TRACKING_DELIVERED 					= 'shipment.tracking.delivered';
    
	const SHIPMENT_TRACKING_AWAITS_PICKUP_BY_RECEIVER 	= 'shipment.tracking.awaits_pickup_by_receiver';
    
	const SHIPMENT_TRACKING_CANCELED 					= 'shipment.tracking.canceled';
    
	const SHIPMENT_TRACKING_DELAYED 					= 'shipment.tracking.delayed';
    
	const SHIPMENT_TRACKING_EXCEPTION 					= 'shipment.tracking.exception';
    
	const SHIPMENT_TRACKING_NOT_DELIVERED 				= 'shipment.tracking.not_delivered';
    
	const SHIPMENT_TRACKING_DESTROYED 					= 'shipment.tracking.destroyed';
    
	const SHIPMENT_TRACKING_NOTIFICATION 				= 'shipment.tracking.notification';
    
	const SHIPMENT_TRACKING_UNKNOWN 					= 'shipment.tracking.unknown';
	
	
	public static function get_class_name() : string {
		return '\\' . __CLASS__;
	}
	
}