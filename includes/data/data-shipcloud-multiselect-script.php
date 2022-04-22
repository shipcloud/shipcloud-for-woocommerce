<?php

$package_types = array_merge(
	[ 
		'placeholder' => _x( 'Select type', 'backend: Selecting a package type option for label creation', 'shipcloud-for-woocommerce' ) 
	],
	WC_Shipping_Shipcloud_Utils::get_package_types()
);

$services = array_merge(
	[ 
		'placeholder' => _x( 'Select service', 'backend: Selecting a carrier service option for label creation', 'shipcloud-for-woocommerce' ) 
	],
	WC_Shipping_Shipcloud_Utils::get_carrier_services_list()
);

$label_formats = array_merge(
	[
		'placeholder' => _x( 'Select label format', 'backend: Selecting a format option for label creation', 'shipcloud-for-woocommerce' ) 
	],
	WC_Shipping_Shipcloud_Utils::get_carrier_label_formats()
);

$carriers_with_pickup_service = WC_Shipping_Shipcloud_Utils::get_carrier_providing_pickup_service();
$carriers_with_pickup_object  = $carriers_with_pickup_service['carriers_with_pickup_object'];
$carriers_with_pickup_request = $carriers_with_pickup_service['carriers_with_pickup_request'];

$api				= WC_Shipping_Shipcloud_API_Adapter::get_instance();
$shipcloud_carriers	= $api->get_carriers();
$carriers_config	= WC_Shipping_Shipcloud_Utils::get_available_shipping_services( $shipcloud_carriers );

$all_additional_services = [];
foreach ( $carriers_config as $carrier ) {
	$all_additional_services[] = $carrier->get_additional_services();
}
$all_additional_services_values = array_values( $all_additional_services );
if ( count( $all_additional_services_values ) > 0 ) {
	$all_additional_services = array_merge( ...$all_additional_services_values );
	$all_additional_services = array_unique( $all_additional_services );
}

wp_register_script( 'shipcloud-multiselect', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/shipcloud-multiselect.js', array( 'jquery' ) );
wp_localize_script(
	'shipcloud-multiselect',
	'wcsc_carrier',
	array(
		'label' => array(
			'carrier' => array(
				'placeholder' => _x( 'Select carrier', 'backend: Selecting a carrier option for label creation', 'shipcloud-for-woocommerce' ),
			),
			'package_types' => $package_types,
			'services' 		=> $services,
			'label_formats' => $label_formats,
		),
		'additional_services' => array_values( $all_additional_services ),
		'data' => $carriers_config,
	)
);

wp_localize_script(
	'shipcloud-multiselect',
	'shipcloud_pickup_carriers',
	array(
		'carriers_with_pickup_object' 	=> $carriers_with_pickup_object,
		'carriers_with_pickup_request' 	=> $carriers_with_pickup_request
	)
);

wp_enqueue_script( 'shipcloud-multiselect' );
