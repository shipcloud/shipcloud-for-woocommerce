<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

$base_location		= wc_get_base_location();
$default_country	= WC_Shipping_Shipcloud_Utils::maybe_extract_country_code( $base_location[ 'country' ] );

$logfile_path 		= WC_Shipping_Shipcloud_Utils::get_log_file_path();

/**
 * Array of settings
 */
return array(
	
	'api_key' => array(
		'title'       => __( 'API key', 'shipcloud-for-woocommerce' ),
		'type'        => 'text',
		'description' => sprintf( __( 'Enter your <a href="%s" target="_blank">shipcloud api key</a>.', 'shipcloud-for-woocommerce' ), 'https://app.shipcloud.io/de/users/api_key' ),
	),
	
	'allowed_carriers' => array(
		'title'       => __( 'Shipping methods', 'shipcloud-for-woocommerce' ),
		'type'        => 'multiselect',
		'description' => __( 'Select the carriers that you want to use in your shop.', 'shipcloud-for-woocommerce' ),
		'desc_tip'    => true,
		'options'     => $this->carriers_options
	),
	
	'dhl_express_regular_pickup' => array(
		'title'   => __( 'DHL Express regular pickup', 'shipcloud-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'DHL Express will pickup my shipments regularly', 'shipcloud-for-woocommerce' ),
		'description' => __( 'When active you won\'t have to provide pickup data when creating a shipment.', 'shipcloud-for-woocommerce' ),
	),
	
	'notification_email' => array(
		'title'       => __( 'Notification email', 'shipcloud-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Send notification emails from shipcloud to recipients on status changes of shipment.', 'shipcloud-for-woocommerce' ),
		'description' => __( 'When the options notification email and carrier email are active, customers will only be notified by DHL/DPD instead of shipcloud. This avoids customers getting notified twice.', 'shipcloud-for-woocommerce' ),
		'desc_tip'    => true,
		'default'     => 'yes'
	),
	
	'carrier_email' => array(
		'title'       => __( 'Carrier email', 'shipcloud-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Send notification emails from carriers (supported by DHL, DPD, GLS and Hermes) to recipients on status changes of shipment.', 'shipcloud-for-woocommerce' ),
		'default'     => 'yes'
	),
	
	'show_tracking_in_my_account' => array(
		'title'       => __( 'Show tracking in my account', 'shipcloud-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Lets the buyer see detailed tracking information when viewing their order in the my account area.', 'shipcloud-for-woocommerce' ),
		'default'     => 'yes'
	),
	
	'auto_weight_calculation' => array(
		'title'   => __( 'Always use calculated weight from order', 'shipcloud-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'When creating shipping labels the checkbox to use the calculated weight is always active', 'shipcloud-for-woocommerce' ),
		'default' => 'no'
	),
	
	'ask_create_label_check' => array(
		'title'   => __( 'Ask before creating a shipping label', 'shipcloud-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Safety check that adds a dialog to ask you if you really want to create a shipping label', 'shipcloud-for-woocommerce' ),
		'default' => 'yes'
	),
	
	'calculation' => array(
		'title'       => __( 'Price calculation', 'shipcloud-for-woocommerce' ),
		'type'        => 'title',
		'description' => sprintf( __( 'To get a price for the customer\'s order, you have to set up the price calculation.', 'shipcloud-for-woocommerce' ) )
	),
	
	'disable_calculation' => array(
		'type'		=> 'hidden',
		'value'		=> false,
	),
	
	'calculate_products_type' => array(
		'type'		=> 'hidden',
		'value'		=> 'order',
	),
	
	'calculate_products_type_fallback' => array(
		'type'		=> 'hidden',
		'value'		=> 'order',
	),
	
	'standard_price_products' => array(
		'title'       => __( 'Standard price products', 'shipcloud-for-woocommerce' ),
		'type'        => 'price',
		'description' => __( 'Will be used if no size or weight is given to a product or for fallback (have to be entered in EUR).', 'shipcloud-for-woocommerce' ),
	),
	
	'calculation_type_shipment_classes' => array(
		'type'		=> 'hidden',
		'value'		=> 'order',
	),
	
	'calculation_type_shipment_classes_fallback' => array(
		'type'		=> 'hidden',
		'value'		=> 'order',
	),
	
	'standard_price_shipment_classes' => array(
		'title'       => __( 'Standard price shipment classes', 'shipcloud-for-woocommerce' ),
		'type'        => 'price',
		'description' => __( 'Will be used if no size or weight is given to a shipping class (have to be entered in EUR).', 'shipcloud-for-woocommerce' ),
	),
	
	'banking_information' => array(
		'title'       => _x(
			'Banking information',
			'Backend: Title of the settings section',
			'shipcloud-for-woocommerce'
		),
		'type'        => 'title',
		'description' => sprintf( __( 'Fill in defaults for cash on delivery.', 'shipcloud-for-woocommerce' ) ),
	),
	
	'bank_account_holder' => array(
		'title'       => _x(
			'Bank account holder',
			'Backend: Label for input field in settings',
			'shipcloud-for-woocommerce'
		),
		'type'        => 'text',
		'description' => _x(
			'Enter the name of the person who holds the bank account.',
			'Backend: Description for bank_account_holder input field in settings.',
			'shipcloud-for-woocommerce'
		),
		'desc_tip'    => true,
	),
	
	'bank_name' => array(
		'title'       => _x(
			'Bank name',
			'Backend: Label for bank_name input field in settings',
			'shipcloud-for-woocommerce'
		),
		'type'        => 'text',
		'description' => _x(
			'Enter the name of the bank.',
			'Backend: Description for bank_name input field in settings',
			'shipcloud-for-woocommerce'
		),
		'desc_tip'    => true,
	),
	
	'bank_account_number' => array(
		'title'       => _x(
			'Bank account number (IBAN)',
			'Backend: Label for bank_account_number input field in settings',
			'shipcloud-for-woocommerce'
		),
		'type'        => 'text',
		'description' => _x(
			'Enter the account number for the default bank account as IBAN number.',
			'Backend: Description for bank_account_number input field in settings',
			'shipcloud-for-woocommerce'
		),
		'desc_tip'    => true,
	),
	
	'bank_code' => array(
		'title'       => _x(
			'Bank code (SWIFT)',
			'Backend: Label for bank_code input field in settings',
			'shipcloud-for-woocommerce'
		),
		'type'        => 'text',
		'description' => _x(
			'Enter the bank SWIFT code.',
			'Backend: Description for bank_code input field in settings',
			'shipcloud-for-woocommerce'
		),
		'desc_tip'    => true,
	),
	
	'checkout_settings' => array(
		'title' => __( 'Checkout settings', 'shipcloud-for-woocommerce' ),
		'type' => 'title'
	),
    
	'carrier_selection' => array(
		'type'		=> 'hidden',
		'value'		=> 'shopowner',
	),
    
	'show_pakadoo' => array(
        'title'       => __( 'Show input for pakadoo id', 'shipcloud-for-woocommerce' ),
        'type'        => 'checkbox',
        'description' => sprintf( __( 'When using the <a href="%s" target="_blank">pakadoo</a> service your customers can receive private parcels at work.', 'shipcloud-for-woocommerce' ), 'https://www.pakadoo.de/en/'),
        'label'       => __( 'Show input field to specify pakadoo id within shipping address', 'shipcloud-for-woocommerce' ),
        'default' => 'yes'
    ),
    
	'show_recipient_care_of' => array(
        'title'       => __( 'Show input for recipient care of', 'shipcloud-for-woocommerce' ),
        'type'        => 'select',
        'class'       => 'select',
        'description' => __( 'Add an input that lets the buyer specify a care of.', 'shipcloud-for-woocommerce' ),
        'desc_tip'    => true,
        'options'     => array(
            'in_billing' => __( 'In billing address', 'shipcloud-for-woocommerce' ),
            'in_shipping' => __( 'In shipping address', 'shipcloud-for-woocommerce' ),
            'both' => __( 'In billing and shipping address', 'shipcloud-for-woocommerce' ),
            'none' => __( 'Don\'t show', 'shipcloud-for-woocommerce' ),
        ),
        'default' => 'none'
    ),
    
	'show_recipient_phone' => array(
        'title'   => __( 'Show input for recipient phone number', 'shipcloud-for-woocommerce' ),
        'type'        => 'checkbox',
        'description' => __( 'Some carriers need a phone number of the recipient so they can contact her/him to make the delivery.', 'shipcloud-for-woocommerce' ),
        'label'       => __( 'Add an input that lets the buyer specify a phone number, when using a shipping address', 'shipcloud-for-woocommerce' ),
        'default' => 'yes'
    ),
	
	'advanced_settings' => array(
		'title'       => __( 'Advanced settings', 'shipcloud-for-woocommerce' ),
		'type'        => 'title'
	),
	
	'global_reference_number' => array(
		'title'       => __( 'Global reference number', 'shipcloud-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Always use this value as reference number. You can use one of the following shortcodes for making the value dynamic: [shipcloud_orderid]', 'shipcloud-for-woocommerce' )
	),

	'street_detection' => array(
		'title'   => __( 'Street detection', 'shipcloud-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Automatically detect street name and number', 'shipcloud-for-woocommerce' ),
		'description' => __( 'There are some cases where automatic detection doesn\'t work, due to different naming schemes. Always check the recipient\'s address before creating a shipping label!', 'shipcloud-for-woocommerce' ),
		'default' => 'yes'
	),

	'webhook_active' => array(
		'title'   => __( 'Shipment status notification', 'shipcloud-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable webhooks to get notified about status changes of your shipments.', 'shipcloud-for-woocommerce' ),
		'description' => sprintf( __( 'If you want to make changes, you can find the settings in your <a href="%s" target="_blank">shipcloud account</a>.', 'shipcloud-for-woocommerce' ), 'https://app.shipcloud.io/de/webhooks' ),
		'default' => 'no'
	),

	'debug' => array(
		'title'   => __( 'Debug', 'shipcloud-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable logging if you experience problems.', 'shipcloud-for-woocommerce' ),
		'description' => sprintf( __( 'You can find the logfile at <code>%s</code>', 'shipcloud-for-woocommerce' ), $logfile_path ),
		'default' => 'yes'
	),
);
