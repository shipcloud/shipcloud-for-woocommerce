<?php

if ( !defined( 'ABSPATH' ) ) exit;

/*
* Getting Plugin Template
* @since 1.0.0
*/
if( defined( 'WCSC_FOLDER') ): // TODO: Replace PluginName
	function wcsc_locate_template( $template_names, $load = FALSE, $require_once = TRUE ) {
	    $located = '';
		
	    $located = locate_template( $template_names, $load, $require_once );
	
	    if ( '' == $located ):
			foreach ( ( array ) $template_names as $template_name ):
			    if ( !$template_name )
					continue;
			    if ( file_exists( WCSC_FOLDER . '/templates/' . $template_name ) ):
					$located = WCSC_FOLDER . '/templates/' . $template_name;
					break;
				endif;
			endforeach;
		endif;
	
	    if ( $load && '' != $located )
		    load_template( $located, $require_once );
	
	    return $located;
	}
endif;

/**
 * Debugging helper function
 */
if( !function_exists( 'p' ) ){
	function p( $var ){
		echo '<pre>';
		print_r( $var );
		echo '</pre>';
	}
}

/**
 * Get carrier display_name from name
 * @param string $name
 * @return string $display_name
 */
function wcsc_get_carrier_display_name( $name ){
	$options = get_option( 'woocommerce_shipcloud_settings' );

	$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );
	$carriers = $shipcloud_api->get_carriers();

	foreach( $carriers AS $carrier ):
		if( $carrier[ 'name' ] == $name )
			return $carrier[ 'display_name' ];
	endforeach;
}

/**
 * Splitting Address for getting number of street and street separate
 * @param string $street
 * @return mixed $matches
 */
function wcsc_explode_street( $street ){
	$matches = array();

	if( !preg_match('/(?P<address>[^\d]+) (?P<number>\d+.?)/', $street, $matches ) )
		return $street;

	return $matches;
}

/**
 * Translating shipcloud.io texts
 * @param string $error_text
 * @return string $error_text
 */
function wcsc_translate_shipcloud_text( $error_text ){
	$translations = array(
		"Sender Street can't be blank"
		=> __( 'Sender Street can\'t be blank.', 'wpsc-locale' ),
		"Sender Street number can't be blank"
		=> __( 'Sender Street number can\'t be blank.', 'wpsc-locale' ),
		"Sender ZIP code can't be blank"
		=> __( 'Sender ZIP code can\'t be blank.', 'wpsc-locale' ),
		"Sender City can't be blank"
		=> __( 'Sender City can\'t be blank.', 'wpsc-locale' ),
		"Sender Country can't be blank"
		=> __( 'Sender Country can\'t be blank.', 'wpsc-locale' ),
		"Sender Country  is not an ALPHA-2 ISO country code."
		=> __( 'Sender Country  is not an ALPHA-2 ISO country code.', 'wpsc-locale' ),
		"Receiver Street can't be blank"
		=> __( 'Sender Street can\'t be blank.', 'wpsc-locale' ),
		"Receiver Street number can't be blank"
		=> __( 'Sender Street number can\'t be blank.', 'wpsc-locale' ),
		"Receiver ZIP code can't be blank"
		=> __( 'Sender ZIP code can\'t be blank.', 'wpsc-locale' ),
		"Receiver City can't be blank"
		=> __( 'Sender City can\'t be blank.', 'wpsc-locale' ),
		"Receiver Country can't be blank"
		=> __( 'Sender Country can\'t be blank.', 'wpsc-locale' ),
		"Receiver Country  is not an ALPHA-2 ISO country code."
		=> __( 'Receiver Country  is not an ALPHA-2 ISO country code.', 'wpsc-locale' ),
		"Package Height (in cm) can't be blank"
		=> __( 'Package Height (in cm) can\'t be blank.', 'wpsc-locale' ),
		"Package Height (in cm) is not a number"
		=> __( 'Package Height (in cm) is not a number.', 'wpsc_locale' ),
		"Package Length (in cm) can't be blank"
		=> __( 'Package Length (in cm) can\'t be blank.', 'wpsc_locale' ),
		"Package Length (in cm) is not a number"
		=> __( 'Package Length (in cm) is not a number.', 'wpsc-locale'),
		"Package Width (in cm) can't be blank"
		=> __( 'Package Width (in cm) can\'t be blank.', 'wpsc-locale' ),
		"Package Width (in cm) is not a number"
		=> __( 'Package Width (in cm) is not a number.', 'wpsc-locale'),
		"Package Weight (in kg) can't be blank"
		=> __( 'Package Weight (in kg) can\'t be blank.', 'wpsc-locale' ),
		"Package Weight (in kg) is not a number"
		=> __( 'Package Weight (in kg) is not a number.', 'wpsc-locale')
	);

	if( array_key_exists( $error_text, $translations ) )
		$error_text = $translations[ $error_text ];

	return $error_text;
}

/**
 * Deleting values
 */
function wcsc_delete_values(){
	global $wpdb;

	$post_type = 'shop_order';
	$meta_key = 'shipcloud_shipment_data';

	$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type=%s ", $post_type );
	$results = $wpdb->get_results( $sql );

	foreach( $results AS $result ){
		$post_id = $result->ID;
		delete_post_meta( $post_id, $meta_key );
	}
}
if( array_key_exists( 'wcscdeletevalues', $_GET ) )
	add_action( 'init', 'wcsc_delete_values' );