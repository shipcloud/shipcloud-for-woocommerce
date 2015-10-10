<?php

if( !defined( 'ABSPATH' ) )
	exit;

/**
* Getting Plugin Template
* @since 1.0.0
*/
if( defined( 'WCSC_FOLDER' ) ): // TODO: Replace PluginName
	function wcsc_locate_template( $template_names, $load = FALSE, $require_once = TRUE )
	{
		$located = '';

		$located = locate_template( $template_names, $load, $require_once );

		if( '' == $located ):
			foreach( ( array ) $template_names as $template_name ):
				if( !$template_name )
				{
					continue;
				}
				if( file_exists( WCSC_FOLDER . '/templates/' . $template_name ) ):
					$located = WCSC_FOLDER . '/templates/' . $template_name;
					break;
				endif;
			endforeach;
		endif;

		if( $load && '' != $located )
		{
			load_template( $located, $require_once );
		}

		return $located;
	}
endif;

/**
 * Debugging helper function
 */
if( !function_exists( 'p' ) )
{
	function p( $var )
	{
		echo '<pre>';
		print_r( $var );
		echo '</pre>';
	}
}


/**
 * Get allowed Carriers
 *
 * @return array $carriers
 */
function wcsc_get_carriers()
{
	$settings = get_option( 'woocommerce_shipcloud_settings' );

	if( '' == $settings || !array_key_exists( 'api_key', $settings ) || '' == $settings[ 'api_key' ] )
	{
		return array();
	}

	$allowed_carriers = $settings[ 'allowed_carriers' ];

	$shipcloud = new Woocommerce_Shipcloud_API( $settings[ 'api_key' ] );

	if( '' == $allowed_carriers ){
		$shipcloud_carriers = $shipcloud->get_carriers( TRUE );
	}else{
		$shipcloud_carriers = $shipcloud->get_carriers();
	}

	$carriers = array();

	if( is_array( $allowed_carriers ) )
	{
		foreach( $shipcloud_carriers AS $shipcloud_carrier )
		{
			if( in_array( $shipcloud_carrier[ 'name' ], $allowed_carriers ) )
			{
				$carriers[ $shipcloud_carrier[ 'name' ] ] = $shipcloud_carrier[ 'display_name' ];
			}
		}
	}

	return $carriers;
}

/**
 * Get carrier display_name from name
 *
 * @param string $name
 *
 * @return string $display_name
 */
function wcsc_get_carrier_display_name( $name )
{
	$settings = get_option( 'woocommerce_shipcloud_settings' );

	$shipcloud_api = new Woocommerce_Shipcloud_API( $settings[ 'api_key' ] );
	$carriers = $shipcloud_api->get_carriers();

	foreach( $carriers AS $carrier ):
		if( $carrier[ 'name' ] == $name )
		{
			return $carrier[ 'display_name' ];
		}
	endforeach;
}

/**
 * Splitting Address for getting number of street and street separate
 *
 * @param string $street
 *
 * @return mixed $matches
 */
function wcsc_explode_street( $street )
{
	$matches = array();

	if( !preg_match( '/(?P<address>[^\d]+) (?P<number>\d+.?)/', $street, $matches ) )
	{
		return $street;
	}

	return $matches;
}

/**
 * Translating shipcloud.io texts
 *
 * @param string $error_text
 *
 * @return string $error_text
 */
function wcsc_translate_shipcloud_text( $error_text )
{
	$translations = array(
		"Carrier can't be blank"                                => __( 'Carrier can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender Street can't be blank"                          => __( 'Sender Street can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender Street number can't be blank"                   => __( 'Sender Street number can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender ZIP code can't be blank"                        => __( 'Sender ZIP code can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender City can't be blank"                            => __( 'Sender City can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender Country can't be blank"                         => __( 'Sender Country can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender Country  is not an ALPHA-2 ISO country code."   => __( 'Sender Country  is not an ALPHA-2 ISO country code.', 'woocommerce-shipcloud' ),
		"Receiver Street can't be blank"                        => __( 'Sender Street can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver Street number can't be blank"                 => __( 'Sender Street number can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver ZIP code can't be blank"                      => __( 'Sender ZIP code can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver City can't be blank"                          => __( 'Sender City can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver Country can't be blank"                       => __( 'Sender Country can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver Country  is not an ALPHA-2 ISO country code." => __( 'Receiver Country  is not an ALPHA-2 ISO country code.', 'woocommerce-shipcloud' ),
		"Package Height (in cm) can't be blank"                 => __( 'Height (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Package Height (in cm) is not a number"                => __( 'Height (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Package Length (in cm) can't be blank"                 => __( 'Length (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Package Length (in cm) is not a number"                => __( 'Length (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Package Width (in cm) can't be blank"                  => __( 'Width (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Package Width (in cm) is not a number"                 => __( 'Width (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Package Weight (in kg) can't be blank"                 => __( 'Weight (in kg) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Package Weight (in kg) is not a number"                => __( 'Weight (in kg) is not a number.', 'woocommerce-shipcloud' ),
		"Height (in cm) can't be blank"                 => __( 'Height (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Height (in cm) is not a number"                => __( 'Height (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Length (in cm) can't be blank"                 => __( 'Length (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Length (in cm) is not a number"                => __( 'Length (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Width (in cm) can't be blank"                  => __( 'Width (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Width (in cm) is not a number"                 => __( 'Width (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Weight (in kg) can't be blank"                 => __( 'Weight (in kg) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Weight (in kg) is not a number"                => __( 'Weight (in kg) is not a number.', 'woocommerce-shipcloud' )
	);

	if( array_key_exists( $error_text, $translations ) )
	{
		$error_text = $translations[ $error_text ];
	}

	return $error_text;
}

/**
 * Checking if payment gateway is enabled
 *
 * @return bool
 */
function wcsc_is_enabled()
{
	$settings = get_option( 'woocommerce_shipcloud_settings' );

	// Needed if options are saved in this moment
	if( array_key_exists( 'woocommerce_shipcloud_api_key', $_POST ) && array_key_exists( 'woocommerce_shipcloud_enabled', $_POST ) )
	{
		return TRUE;
	}

	// Needed if options are saved in this moment
	if( array_key_exists( 'woocommerce_shipcloud_api_key', $_POST ) && !array_key_exists( 'woocommerce_shipcloud_enabled', $_POST ) )
	{
		return FALSE;
	}

	if( '' == $settings )
	{
		return FALSE;
	}

	if( !array_key_exists( 'enabled', $settings ) )
	{
		return FALSE;
	}

	if( 'yes' != $settings[ 'enabled' ] )
	{
		return FALSE;
	}

	return TRUE;
}

wcsc_is_enabled();

/**
 * Deleting values
 */
function wcsc_delete_values()
{
	global $wpdb;

	$post_type = 'shop_order';
	$meta_key = 'shipcloud_shipment_data';

	$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type=%s ", $post_type );
	$results = $wpdb->get_results( $sql );

	foreach( $results AS $result )
	{
		$post_id = $result->ID;
		delete_post_meta( $post_id, $meta_key );
	}
}

if( array_key_exists( 'wcscdeletevalues', $_GET ) )
{
	add_action( 'init', 'wcsc_delete_values' );
}