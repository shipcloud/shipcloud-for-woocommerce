<?php

if( !defined( 'ABSPATH' ) )
	exit;

/**
 * Getting Plugin Template
 *
 * @since 1.0.0
 */
if( defined( 'WCSC_FOLDER' ) ):
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
 *
 * @since 1.0.0
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
 * Get carrier display_name from name
 *
 * @param string $name
 *
 * @return string $display_name
 *
 * @since 1.0.0
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
 * Getting shipment status by string (Needed for translations)
 *
 * @param string $status
 *
 * @return string $message
 *
 * @since 1.0.0
 */
function wcsc_get_shipment_status_string( $status )
{
	/**
	 * Hooks in for further functions after status changes
	 */
	switch( $status )
	{
		case 'shipment.tracking.picked_up':
			$message = __( 'Picked up', 'woocommerce-shipcloud' );
			break;

		case 'shipment.tracking.transit':
			$message = __( 'In transit', 'woocommerce-shipcloud' );
			break;

		case 'shipment.tracking.out_for_delivery':
			$message = __( 'Out for delivery', 'woocommerce-shipcloud' );
			break;

		case 'shipment.tracking.delivered':
			$message = __( 'Delivered', 'woocommerce-shipcloud' );
			break;

		case 'shipment.tracking.awaits_pickup_by_receiver':
			$message = __( 'Awaits pickup by Receiver', 'woocommerce-shipcloud' );
			break;

		case 'shipment.tracking.delayed':
			$message = __( 'Delayed', 'woocommerce-shipcloud' );
			break;

		case 'shipment.tracking.not_delivered':
			$message = __( 'Not delivered', 'woocommerce-shipcloud' );
			break;

		case 'shipment.tracking.notification':
			$message = __( 'Carrier internal notification. Tracking events within the shipment will carry more elaborate information.', 'woocommerce-shipcloud' );
			break;

		case 'shipment.tracking.unknown':
			$message = __( 'Status unknown', 'woocommerce-shipcloud' );
			break;

		default:
			$message = __( 'Not available yet', 'woocommerce-shipcloud' );
			break;
	}

	return $message;
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

	if( preg_match( '/(?P<address>[^\d]+) (?P<number>\d+.?)/', $street, $matches ) )
	{
		return $matches;
	}
    elseif( preg_match('/([^\d]+)\s?(.+)/i', $street, $matches ) )
	{

	    $street = array(
	        'address'   =>  $matches[ 1 ],
	        'number'   =>  $matches[ 2 ]
        );
		return $street;
	}
	else
    {
        explode( ' ', $street );
        $street = array(
            'address'   =>  $matches[ 0 ],
            'number'   =>  $matches[ 1 ]
        );
        return $street;
    }
}

/**
 * Translating shipcloud.io texts
 *
 * @param string $error_text
 *
 * @return string $error_text
 *
 * @since 1.0.0
 */
function wcsc_translate_shipcloud_text( $error_text )
{
	$error_text = trim( $error_text );

	$translations = array(
		"Carrier can't be blank"                                => __( 'Carrier can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender: last name can't be blank"                      => __( 'Sender Last Name can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender Street can't be blank"                          => __( 'Sender Street can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender: street can't be blank"                         => __( 'Sender Street can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender Street number can't be blank"                   => __( 'Sender Street number can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender: street number can't be blank"                  => __( 'Sender Street number can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender ZIP code can't be blank"                        => __( 'Sender ZIP code can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender: zip code can't be blank"                       => __( 'Sender ZIP code can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender City can't be blank"                            => __( 'Sender City can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender: city can't be blank"                           => __( 'Sender City can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender Country can't be blank"                         => __( 'Sender Country can\'t be blank.', 'woocommerce-shipcloud' ),
		"Sender Country  is not an ALPHA-2 ISO country code."   => __( 'Sender Country  is not an ALPHA-2 ISO country code.', 'woocommerce-shipcloud' ),
		"Receiver: last name can't be blank"                    => __( 'Receiver Last Name can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver Street can't be blank"                        => __( 'Receiver Street can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver Street number can't be blank"                 => __( 'Receiver Street number can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver ZIP code can't be blank"                      => __( 'Receiver ZIP code can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver: zip code can't be blank"                     => __( 'Receiver ZIP code can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver City can't be blank"                          => __( 'Receiver City can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver: city can't be blank"                         => __( 'Receiver City can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver Country can't be blank"                       => __( 'Receiver Country can\'t be blank.', 'woocommerce-shipcloud' ),
		"Receiver Country  is not an ALPHA-2 ISO country code." => __( 'Receiver Country  is not an ALPHA-2 ISO country code.', 'woocommerce-shipcloud' ),
		"Package Height (in cm) can't be blank"                 => __( 'Height (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Package Height (in cm) is not a number"                => __( 'Height (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Package Length (in cm) can't be blank"                 => __( 'Length (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Package Length (in cm) is not a number"                => __( 'Length (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Package Width (in cm) can't be blank"                  => __( 'Width (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Package Width (in cm) is not a number"                 => __( 'Width (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Package Weight (in kg) can't be blank"                 => __( 'Weight (in kg) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Package Weight (in kg) is not a number"                => __( 'Weight (in kg) is not a number.', 'woocommerce-shipcloud' ),
		"Height (in cm) can't be blank"                         => __( 'Height (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Height (in cm) is not a number"                        => __( 'Height (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Length (in cm) can't be blank"                         => __( 'Length (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Length (in cm) is not a number"                        => __( 'Length (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Width (in cm) can't be blank"                          => __( 'Width (in cm) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Width (in cm) is not a number"                         => __( 'Width (in cm) is not a number.', 'woocommerce-shipcloud' ),
		"Weight (in kg) can't be blank"                         => __( 'Weight (in kg) can\'t be blank.', 'woocommerce-shipcloud' ),
		"Weight (in kg) is not a number"                        => __( 'Weight (in kg) is not a number.', 'woocommerce-shipcloud' ),
		"HTTP Basic: Access denied."                            => __( 'Authentication failure! Please check your API Key.', 'woocommerce-shipcloud' ),
		"Tip: A label has already been created. Only prepared shipments (the ones without a label) can be updated or deleted" => __( 'Tip: A label has already been created. Only prepared shipments (the ones without a label) can be updated or deleted', 'woocommerce-shipcloud' )
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
 *
 * @since 1.0.0
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

/**
 * Checking if we are on shipclud.io settings screen
 *
 * @return bool
 *
 * @since 1.0.0
 */
function wcsc_is_settings_screen(){
	$page = '';

	if( array_key_exists( 'page', $_GET ) )
	{
		$page = $_GET[ 'page' ];
	}

	$tab = '';
	if( array_key_exists( 'tab', $_GET ) )
	{
		$tab = $_GET[ 'tab' ];
	}

	$section = '';
	if( array_key_exists( 'section', $_GET ) )
	{
		$section = $_GET[ 'section' ];
	}

	// If page should noz show a message, interrupt the check and gibe back true
	if( 'wc-settings' === $page && 'shipping' === $tab && 'wc_shipcloud_shipping' === $section )
	{
		return TRUE;
	}

	return TRUE;
}

wcsc_is_enabled();

/**
 * Deleting values
 *
 * @since 1.0.0
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

/**
 * Returns the object of the shipping method
 *
 * @return bool|WC_Shipcloud_Shipping
 * @since 1.1.0
 */
function wcsc_shipping_method()
{
	$shipping_methods = WC()->shipping()->get_shipping_methods();

	if( ! array_key_exists( 'shipcloud', $shipping_methods ) )
	{
		return false;
	}

	return $shipping_methods['shipcloud'];
}

/**
 * Checks if we are a products post type in admin
 *
 * @return bool
 * @since 1.0.0
 */
function wcsc_is_admin_screen()
{
	if ( ! is_admin() ) {
		return false;
	}

	// Shop order
	if( 'shop_order' === get_current_screen()->id )
	{
		return true;
	}

	// Settings screen
	if( 'woocommerce_page_wc-settings' === get_current_screen()->id && 'shipcloud' === $_GET['section'] )
	{
		return true;
	}

	return false;
}

/**
 * Checks if we are a products post type in frontend
 *
 * @return bool
 * @since 1.0.0
 */
function wcsc_is_frontend_screen()
{
	if( is_cart() || is_checkout() || is_checkout_pay_page() )
	{
		return true;
	}

	return false;
}

function wcsc_order_get_parcel_description( WC_Order $order ) {
	$shipping_data = (array) get_post_meta( $order->id, 'shipcloud_shipment_data', true );

	if ( isset( $shipping_data['description'] ) ) {
		return $shipping_data['description'];
	}

	return '';
}

function _wcsc_is_sender_address_valid() {
	$standard_address_err = false;

	$shipping = new \WC_Shipcloud_Shipping();

	if ( $shipping->get_option( 'sender_street' )
		|| ! $shipping->get_option( 'sender_street_nr' )
		|| ! $shipping->get_option( 'sender_postcode' )
		|| ! $shipping->get_option( 'sender_city' )
		|| ! $shipping->get_option( 'sender_country' )
	)
	{
		return false;
	}

	return true;
}

function wcsc_care_of_frontend( $data ) {
	$pos = array_search( 'shipping_company', array_keys( $data ) );

	$final                 = array_slice( $data, 0, $pos );
	$final['wcsc_care_of'] = array(
		'label'       => __( 'Care of', 'wcsc' ),
		'description' => '',
		'class'       => array( 'form-row-wide' ),
		'clear'       => true,
	);

	$data = $final + array_slice( $data, $pos );

	return $data;
}

add_filter( 'woocommerce_shipping_fields', 'wcsc_care_of_frontend' );
