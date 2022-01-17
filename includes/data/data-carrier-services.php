<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Array of settings
 */
return array(
	
	'standard'      => array(
		'name'				=> __( 'Standard', 'shipcloud-for-woocommerce' ),
		'description'		=> __( 'Normal shipping', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> true
	),
	
	'one_day'       => array(
		'name'				=> __( 'Express (1 Day)', 'shipcloud-for-woocommerce' ),
		'description'		=> __( 'Express shipping where the package will arrive the next day', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> true
	),
	
	'one_day_early' => array(
		'name'				=> __( 'Express (1 Day Early)', 'shipcloud-for-woocommerce' ),
		'description'		=> __( 'Express shipping where the package will arrive the next day until 12pm', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> true
	),
	
	'same_day'      => array(
		'name'				=> __( 'Same Day', 'shipcloud-for-woocommerce' ),
		'description'		=> __( 'Same Day Delivery', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> true
	),
	
	'returns'       => array(
		'name'				=> __( 'Returns', 'shipcloud-for-woocommerce' ),
		'description'		=> __( 'Shipments that are being send back to the shop', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'ups_express_1200' => array(
		'name'				=> __( 'Express 12:00', 'shipcloud-for-woocommerce' ),
		'description'		=> __( 'Delivery by noon of the next business day throughout the country.', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'dpag_warenpost' => array(
		'name'				=> __( 'Warenpost', 'shipcloud-for-woocommerce' ),
		'description'		=> __( 'Small trackable letter delivery', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'dhl_europaket' => array(
		'name'				=> __( 'Europaket', 'shipcloud-for-woocommerce' ),
		'description'		=> __( 'B2B parcel shipments delivered mostly within 48 hours', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'ups_expedited' => array(
		'name' 				=> __( 'Expedited', 'shipcloud-for-woocommerce' ),
		'description' 		=> __( 'For sending less urgent shipments to destinations outside of Europe', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'cargo_international_express' => array(
		'name' 				=> __( 'Express', 'shipcloud-for-woocommerce' ),
		'description' 		=> __( 'Express delivery for Cargo International shipments', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'gls_express_0800' => array(
		'name' 				=> __( 'Express 08:00', 'shipcloud-for-woocommerce' ),
		'description' 		=> __( 'Express delivery for GLS shipments that should be delivered by 8am', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'gls_express_0900' => array(
		'name' 				=> __( 'Express 09:00', 'shipcloud-for-woocommerce' ),
		'description' 		=> __( 'Express delivery for GLS shipments that should be delivered by 9am', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'gls_express_1000' => array(
		'name' 				=> __( 'Express 10:00', 'shipcloud-for-woocommerce' ),
		'description' 		=> __( 'Express delivery for GLS shipments that should be delivered by 10am', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'gls_express_1200' => array(
		'name' 				=> __( 'Express 12:00', 'shipcloud-for-woocommerce' ),
		'description' 		=> __( 'Express delivery for GLS shipments that should be delivered by 12am', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'gls_pick_and_ship' => array(
		'name' 				=> __( 'Pick&ShipService', 'shipcloud-for-woocommerce' ),
		'description' 		=> __( 'Using the Pick&ShipService you can request GLS to pick up a parcel at the address of your choice and deliver it directly to the recipient.', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'dpag_warenpost_untracked' => array(
		'name' 				=> __( 'Warenpost (untracked)', 'shipcloud-for-woocommerce' ),
		'description' 		=> __( 'Small untracked letter delivery', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'dpag_warenpost_signature' => array(
		'name' 				=> __( 'Warenpost (with signature)', 'shipcloud-for-woocommerce' ),
		'description' 		=> __( 'Small trackable letter delivery which the recipient has to sign upon delivery', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'dhl_warenpost' => array(
		'name' 				=> __( 'Warenpost', 'shipcloud-for-woocommerce' ),
		'description'      	=> __( 'Small trackable letter delivery', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
	'dhl_prio' => array(
		'name' 				=> __( 'Prio', 'shipcloud-for-woocommerce' ),
		'description'      	=> __( 'Priority shipping using DHL', 'shipcloud-for-woocommerce' ),
		'customer_service' 	=> false
	),
	
);
