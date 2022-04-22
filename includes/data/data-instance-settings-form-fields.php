<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Array of settings
 */
return array(
	
	'allowed_carriers' => array(
		'title'       => __( 'Shipping carrier', 'shipcloud-for-woocommerce' ),
		'type'        => 'select',
		'description' => __( 'Select the carriers that you want to use in your shop.', 'shipcloud-for-woocommerce' ),
		'desc_tip'    => true,
		'options'     => $this->allowed_carriers_options
	),

	'standard_price_products' => array(
		'title'       => __( 'Standard price products', 'shipcloud-for-woocommerce' ),
		'type'        => 'price',
		'description' => __( 'Will be used if no sizes or weight is given to a product or for fallback (have to be entered in EUR).', 'shipcloud-for-woocommerce' ),
	),
	
	'standard_price_shipment_classes' => array(
		'title'       => __( 'Standard price shipment classes', 'shipcloud-for-woocommerce' ),
		'type'        => 'price',
		'description' => __( 'Will be used if no sizes or weight is given to a shipping class (have to be entered in EUR).', 'shipcloud-for-woocommerce' ),
	),
	
);
