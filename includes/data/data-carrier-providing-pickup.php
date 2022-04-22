<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Array of customs declaration contents types
 */
return array(
	/*
	 * For some carriers, it is mandatory to include a pickup object 
	 * when creating a shipment. This way, they can ensure that the 
	 * express or same-day shipments reach the recipients as quickly 
	 * as possible.
	 */
    'carriers_with_pickup_object'	=> [
		'angel_de',
    	'dhl_express',
		'go'
    ],
    'carriers_with_pickup_request' 	=> [
    	'dpd',
		// 'gls', // TODO: GLS doesn't support pickup requests any more???
		'hermes',
		'ups'
    ],
	
);
