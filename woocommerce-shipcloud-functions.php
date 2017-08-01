<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WCSC_FOLDER' ) ):
	/**
	 * Getting Plugin Template
	 *
	 * @since      1.0.0
	 *
	 * @deprecated 2.0.0 This function is no longer used.
	 */
	function wcsc_locate_template( $template_names, $load = false, $require_once = true ) {
		$located = locate_template( $template_names, $load, $require_once );

		if ( '' == $located ):
			foreach ( ( array ) $template_names as $template_name ):
				if ( ! $template_name ) {
					continue;
				}
				if ( file_exists( WCSC_FOLDER . '/templates/' . $template_name ) ):
					$located = WCSC_FOLDER . '/templates/' . $template_name;
					break;
				endif;
			endforeach;
		endif;

		if ( $load && '' != $located ) {
			load_template( $located, $require_once );
		}

		return $located;
	}
endif;

/**
 * Get carrier display_name from name
 *
 * @param string $name
 *
 * @return string $display_name
 *
 * @since 1.0.0
 */
function wcsc_get_carrier_display_name( $name ) {
	$carriers = _wcsc_carriers_get();

	foreach ( $carriers AS $carrier ) {
		if ( $carrier->getName() === $name ) {
			return $carrier->getDisplayName();
		}
	}

	return $name;
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
function wcsc_get_shipment_status_string( $status ) {
	/**
	 * Hooks in for further functions after status changes.
	 */
	$message = __( 'Not available yet', 'shipcloud-for-woocommerce' );

	$status_mapping = array(
		'shipment.tracking.picked_up'                 => __( 'Picked up', 'shipcloud-for-woocommerce' ),
		'shipment.tracking.transit'                   => __( 'In transit', 'shipcloud-for-woocommerce' ),
		'shipment.tracking.out_for_delivery'          => __( 'Out for delivery', 'shipcloud-for-woocommerce' ),
		'shipment.tracking.delivered'                 => __( 'Delivered', 'shipcloud-for-woocommerce' ),
		'shipment.tracking.awaits_pickup_by_receiver' => __( 'Awaits pickup by receiver', 'shipcloud-for-woocommerce' ),
		'shipment.tracking.delayed'                   => __( 'Delayed', 'shipcloud-for-woocommerce' ),
		'shipment.tracking.not_delivered'             => __( 'Not delivered', 'shipcloud-for-woocommerce' ),
		'shipment.tracking.notification'              => __( 'Carrier internal notification. Tracking events within the shipment will carry more elaborate information.', 'shipcloud-for-woocommerce' ),
		'shipment.tracking.unknown'                   => __( 'Status unknown', 'shipcloud-for-woocommerce' )
	);

	if ( isset( $status_mapping[ $status ] ) ) {
		$message = $status_mapping[ $status ];
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
function wcsc_explode_street( $street ) {
	$matches = array();

	if ( ! preg_match( '/\d/', $street ) ) {
		// No number, just a street.
		return array(
			'address' => trim( $street ),
			'number'  => null,
		);
	}

	if ( preg_match( '/(?P<address>.*)\s+(?P<number>[\d]+\s*[\w]*)/u', $street, $matches ) ) {
		// Named match.
		return $matches;
	}

	if ( preg_match( '/([^\d]+)\s+(.+)/i', $street, $matches ) ) {
		// Classical match (as above but without names).
		return array(
			'address' => $matches[1],
			'number'  => $matches[2]
		);
	}

	// Last try via explode.
	$parts  = explode( ' ', $street );
	$number = array_pop( $parts );
	$street = array(
		'address' => implode( ' ', $parts ),
		'number'  => $number
	);

	return $street;
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
function wcsc_translate_shipcloud_text( $error_text ) {
	$error_text = trim( $error_text );

	$translations = array(
		"Carrier can't be blank"                                                                                              => __( 'Carrier can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender: last name can't be blank"                                                                                    => __( 'Sender last name can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender Street can't be blank"                                                                                        => __( 'Sender street can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender: street can't be blank"                                                                                       => __( 'Sender street can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender Street number can't be blank"                                                                                 => __( 'Sender street number can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender: street number can't be blank"                                                                                => __( 'Sender street number can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender ZIP code can't be blank"                                                                                      => __( 'Sender zipcode can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender: zip code can't be blank"                                                                                     => __( 'Sender zipcode can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender City can't be blank"                                                                                          => __( 'Sender city can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender: city can't be blank"                                                                                         => __( 'Sender city can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender Country can't be blank"                                                                                       => __( 'Sender country can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Sender Country  is not an ALPHA-2 ISO country code."                                                                 => __( 'Sender country  is not an ALPHA-2 ISO country code.', 'shipcloud-for-woocommerce' ),
		"Receiver: last name can't be blank"                                                                                  => __( 'Receiver last name can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Receiver Street can't be blank"                                                                                      => __( 'Receiver street can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Receiver Street number can't be blank"                                                                               => __( 'Receiver street number can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Receiver ZIP code can't be blank"                                                                                    => __( 'Receiver zipcode can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Receiver: zip code can't be blank"                                                                                   => __( 'Receiver zipcode can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Receiver City can't be blank"                                                                                        => __( 'Receiver city can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Receiver: city can't be blank"                                                                                       => __( 'Receiver city can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Receiver Country can't be blank"                                                                                     => __( 'Receiver country can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Receiver Country  is not an ALPHA-2 ISO country code."                                                               => __( 'Receiver country  is not an ALPHA-2 ISO country code.', 'shipcloud-for-woocommerce' ),
		"Package Height (in cm) can't be blank"                                                                               => __( 'Height (in cm) can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Package Height (in cm) is not a number"                                                                              => __( 'Height (in cm) is not a number.', 'shipcloud-for-woocommerce' ),
		"Package Length (in cm) can't be blank"                                                                               => __( 'Length (in cm) can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Package Length (in cm) is not a number"                                                                              => __( 'Length (in cm) is not a number.', 'shipcloud-for-woocommerce' ),
		"Package Width (in cm) can't be blank"                                                                                => __( 'Width (in cm) can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Package Width (in cm) is not a number"                                                                               => __( 'Width (in cm) is not a number.', 'shipcloud-for-woocommerce' ),
		"Package Weight (in kg) can't be blank"                                                                               => __( 'Weight (in kg) can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Package Weight (in kg) is not a number"                                                                              => __( 'Weight (in kg) is not a number.', 'shipcloud-for-woocommerce' ),
		"Height (in cm) can't be blank"                                                                                       => __( 'Height (in cm) can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Height (in cm) is not a number"                                                                                      => __( 'Height (in cm) is not a number.', 'shipcloud-for-woocommerce' ),
		"Length (in cm) can't be blank"                                                                                       => __( 'Length (in cm) can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Length (in cm) is not a number"                                                                                      => __( 'Length (in cm) is not a number.', 'shipcloud-for-woocommerce' ),
		"Width (in cm) can't be blank"                                                                                        => __( 'Width (in cm) can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Width (in cm) is not a number"                                                                                       => __( 'Width (in cm) is not a number.', 'shipcloud-for-woocommerce' ),
		"Weight (in kg) can't be blank"                                                                                       => __( 'Weight (in kg) can\'t be blank.', 'shipcloud-for-woocommerce' ),
		"Weight (in kg) is not a number"                                                                                      => __( 'Weight (in kg) is not a number.', 'shipcloud-for-woocommerce' ),
		"HTTP Basic: Access denied."                                                                                          => __( 'Authentication failure! Please check your api key.', 'shipcloud-for-woocommerce' ),
		"Tip: A label has already been created. Only prepared shipments (the ones without a label) can be updated or deleted" => __( 'Tip: A label has already been created. Only prepared shipments (the ones without a label) can be updated or deleted', 'shipcloud-for-woocommerce' )
	);

	if ( array_key_exists( $error_text, $translations ) ) {
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
function wcsc_is_enabled() {
	$settings = get_option( 'woocommerce_shipcloud_settings' );

	// Needed if options are saved in this moment
	if ( array_key_exists( 'woocommerce_shipcloud_api_key', $_POST ) && array_key_exists( 'woocommerce_shipcloud_enabled', $_POST ) ) {
		return true;
	}

	// Needed if options are saved in this moment
	if ( array_key_exists( 'woocommerce_shipcloud_api_key', $_POST ) && ! array_key_exists( 'woocommerce_shipcloud_enabled', $_POST ) ) {
		return false;
	}

	if ( '' == $settings ) {
		return false;
	}

	if ( ! array_key_exists( 'enabled', $settings ) ) {
		return false;
	}

	if ( 'yes' != $settings['enabled'] ) {
		return false;
	}

	return true;
}

/**
 * Checking if we are on shipclud.io settings screen
 *
 * @return bool
 *
 * @since 1.0.0
 */
function wcsc_is_settings_screen() {
	$page = '';

	if ( array_key_exists( 'page', $_GET ) ) {
		$page = $_GET['page'];
	}

	$tab = '';
	if ( array_key_exists( 'tab', $_GET ) ) {
		$tab = $_GET['tab'];
	}

	$section = '';
	if ( array_key_exists( 'section', $_GET ) ) {
		$section = $_GET['section'];
	}

	// If page should noz show a message, interrupt the check and gibe back true
	if ( 'wc-settings' === $page && 'shipping' === $tab && 'wc_shipcloud_shipping' === $section ) {
		return true;
	}

	return true;
}

wcsc_is_enabled();

/**
 * Deleting values
 *
 * @since 1.0.0
 */
function wcsc_delete_values() {
	global $wpdb;

	$post_type = 'shop_order';
	$meta_key  = 'shipcloud_shipment_data';

	$sql     = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type=%s ", $post_type );
	$results = $wpdb->get_results( $sql );

	foreach ( $results AS $result ) {
		$post_id = $result->ID;
		delete_post_meta( $post_id, $meta_key );
	}
}

if ( array_key_exists( 'wcscdeletevalues', $_GET ) ) {
	add_action( 'init', 'wcsc_delete_values' );
}

/**
 * Returns the object of the shipping method
 *
 * @return bool|WC_Shipcloud_Shipping
 * @since 1.1.0
 */
function wcsc_shipping_method() {
	$shipping_methods = WC()->shipping()->get_shipping_methods();

	if ( ! array_key_exists( 'shipcloud', $shipping_methods ) ) {
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
function wcsc_is_admin_screen() {
	if ( ! is_admin() || ! get_current_screen() ) {
		return false;
	}

	// Shop order
	if ( 'shop_order' === get_current_screen()->id
		 || 'edit-shop_order' === get_current_screen()->id
	) {
		return true;
	}

	// Parcel template
	if ( get_current_screen()->post_type === WCSC_Parceltemplate_Posttype::POST_TYPE ) {
		return true;
	}

	// Settings screen
	if ( 'shipcloud' === $_GET['section'] && 'woocommerce_page_wc-settings' === get_current_screen()->id ) {
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
function wcsc_is_frontend_screen() {
	if ( is_cart() || is_checkout() || is_checkout_pay_page() ) {
		return true;
	}

	return false;
}

/**
 * Receive the parcel description of an order.
 *
 * @param WC_Order $order
 *
 * @return mixed|string
 */
function wcsc_order_get_parcel_description( $order ) {
	if ( ! is_object( $order ) || ! $order instanceof WC_Order ) {
		throw new \InvalidArgumentException( 'Please provide an WC_Order instance.' );
	}

	$order_id = null;
	if ( method_exists( $order, 'get_id' ) ) {
		// WooCommerce 3
		$order_id = $order->get_id();
	} else {
		// Woo2
		$order_id = $order->id;
	}

	$shipping_data = (array) get_post_meta( $order_id, 'shipcloud_shipment_data', true );

	if ( isset( $shipping_data['description'] ) ) {
		return $shipping_data['description'];
	}

	return '';
}

/**
 * Add "care of" field to recipient address.
 *
 * During checkout WooCommerce allows to fill some fields for the delivery address.
 * Shipcloud is capable of "care of" fields,
 * which allows packages to be delivered in some storage or other persons.
 * Therefor an additional field will be shown during checkout.
 *
 * @param $data
 *
 * @return array
 */
function wcsc_care_of_frontend( $data ) {
	$pos = array_search( 'shipping_company', array_keys( $data ) );

	$final                     = array_slice( $data, 0, $pos );
	$final['shipping_care_of'] = array(
		'label'       => __( 'Care of', 'wcsc' ),
		'description' => '',
		'class'       => array( 'form-row-wide' ),
		'clear'       => true,
	);

	$data = $final + array_slice( $data, $pos );

	return $data;
}

add_filter( 'woocommerce_shipping_fields', 'wcsc_care_of_frontend' );

/**
 * Add "phone" field to shipping address.
 *
 * During checkout WooCommerce allows to fill some fields for the delivery address.
 * Shipcloud is capable of "phone" fields,
 * which allows the carrier to call a customer before the delivery.
 * Therefor an additional field will be shown during checkout.
 *
 * @param $data
 *
 * @return array
 */
function wcsc_sender_phone_frontend( $data ) {
	// Place it after the shipping city.
	$pos = array_search( 'shipping_city', array_keys( $data ), true ) + 2;

	$final                   = array_slice( $data, 0, $pos );
	$final['shipping_phone'] = array(
		'label'       => _x( 'Phone', 'Frontend label for entering the phone number', 'wcsc' ),
		'placeholder' => _x( 'To be updated about the package.', 'phone input placeholder', 'woocommerce' ),
		'description' => '',
		'class'       => array( 'form-row-wide' ),
		'clear'       => true,
	);

	return $final + array_slice( $data, $pos );
}

add_filter( 'woocommerce_shipping_fields', 'wcsc_sender_phone_frontend' );

/**
 * Reusable connection to the API.
 *
 * @deprecated 2.0.0 Use internal `_wcsc_api` instead.
 *
 * @return Woocommerce_Shipcloud_API
 */
function wcsc_api() {
	static $api;

	if ( ! $api ) {
		$api = new Woocommerce_Shipcloud_API( wcsc_shipping_method()->get_option( 'api_key' ) );
	}

	return $api;
}

$_wcsc_api = null;

/**
 * Connection to the API.
 *
 * @return \Shipcloud\Api
 */
function _wcsc_api() {
	global $_wcsc_api;

	if ( ! $_wcsc_api ) {
		$_wcsc_api = new Shipcloud\Api(
			wcsc_shipping_method()->get_option( 'api_key' ),
			'plugin.woocommerce.z4NVoYhp'
		);
	}


	return $_wcsc_api;
}

/**
 * Add shipment to order.
 *
 * @param int                        $order_id
 * @param \Shipcloud\Domain\Shipment $shipment
 * @param array                      $data
 * @param string                     $parcel_title
 *
 * @return array
 */
function _wcsc_add_order_shipment( $order_id, $shipment, $data, $parcel_title = '' ) {
	if ( ! $parcel_title ) {
		$parcel_title = wcsc_get_carrier_display_name( $data['carrier'] )
						. ' - ' . $data['package']['width']
						. __( 'x', 'shipcloud-for-woocommerce' ) . $data['package']['height']
						. __( 'x', 'shipcloud-for-woocommerce' ) . $data['package']['length']
						. __( 'cm', 'shipcloud-for-woocommerce' )
						. ' ' . $data['package']['weight']
						. __( 'kg', 'shipcloud-for-woocommerce' );
	}

	$shipment_data = array(
		'id'                   => $shipment->getId(),
		'carrier_tracking_no'  => $shipment->getCarrierTrackingNo(),
		'tracking_url'         => $shipment->getTrackingUrl(),
		'label_url'            => $shipment->getLabelUrl(),
		'price'                => $shipment->getPrice(),
		'parcel_id'            => $shipment->getId(),
		'parcel_title'         => $parcel_title,
		'carrier'              => $data['carrier'],
		'width'                => $data['package']['width'],
		'height'               => $data['package']['height'],
		'length'               => $data['package']['length'],
		'weight'               => $data['package']['weight'],
		'description'          => $data['package']['description'],
		'recipient_first_name' => $data['to']['first_name'],
		'recipient_last_name'  => $data['to']['last_name'],
		'recipient_company'    => $data['to']['company'],
		'recipient_street'     => $data['to']['street'],
		'recipient_street_no'  => $data['to']['street_nr'],
		'recipient_zip_code'   => $data['to']['zip_code'],
		'recipient_city'       => $data['to']['city'],
		'recipient_state'      => $data['to']['state'],
		'recipient_country'    => $data['to']['country'],
		'date_created'         => time()
	);

	if ( isset( $data['from'] ) ) {
		$shipment_data['sender_first_name'] = $data['from']['first_name'];
		$shipment_data['sender_last_name']  = $data['from']['last_name'];
		$shipment_data['sender_company']    = $data['from']['company'];
		$shipment_data['sender_street']     = $data['from']['street'];
		$shipment_data['sender_street_no']  = $data['from']['street_nr'];
		$shipment_data['sender_zip_code']   = $data['from']['zip_code'];
		$shipment_data['sender_city']       = $data['from']['city'];
		$shipment_data['sender_state']      = $data['from']['state'];
		$shipment_data['country']           = $data['from']['country'];
	}

	add_post_meta( $order_id, 'shipcloud_shipment_ids', $shipment_data['id'] );
	add_post_meta( $order_id, 'shipcloud_shipment_data', $shipment_data );

	$order = wc_get_order( $order_id );
	$order->add_order_note( __( 'shipcloud label has been prepared.', 'shipcloud-for-woocommerce' ) );

	return $shipment_data;
}

/**
 * Turn exceptions in \WP_Error
 *
 * @todo Make this a \Shipcloud_Error class with factory.
 *
 * @param \Exception $exception
 *
 * @return WP_Error
 */
function _wcsc_exception_to_wp_error( $exception ) {
	$wp_error         = new \WP_Error( $exception->getCode(), $exception->getMessage() );
	$currentException = $exception->getPrevious();
	$maxDepth         = 20;

	while ( $currentException && $maxDepth > 0 ) {
		$wp_error->add( $currentException->getCode(), $currentException->getMessage() );

		// Next exception in queue.
		$maxDepth --;
		$currentException = $currentException->getPrevious();
	}

	return $wp_error;
}

/**
 * Get carriers from WordPress.
 *
 * This either fetched a cached version of all carriers
 * or tries fetching the data again.
 *
 * @since 1.4.0
 * @deprecated 2.0.0 This will be replaced by some other caching.
 *
 * @return \Shipcloud\Domain\Carrier[]
 */
function _wcsc_carriers_get() {
	$cached = get_transient( '_wcsc_carriers_get' );
	if ( $cached ) {
		return $cached;
	}

	$data = _wcsc_api()->carriers()->get();

	if ( $data ) {
		set_transient( '_wcsc_carriers_get', $data, WEEK_IN_SECONDS );
	}

	return $data;
}
