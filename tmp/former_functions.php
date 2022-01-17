<?php








/**
 * Getting package
 *
 * @return array
 */
private function get_package() {
    $addresses = $this->get_addresses();

    extract( $addresses );

    $recipient = $this->sanitize_address( $recipient );

    $package = [];
    $package['destination']['country']   = $recipient['country'];
    $package['destination']['zip_code']  = $recipient['zip_code'];
    $package['destination']['postcode']  = $recipient['zip_code'];
    $package['destination']['state']     = $recipient['state'];
    $package['destination']['city']      = $recipient['city'];
    $package['destination']['address']   = $recipient['street'];
    if ( array_key_exists( 'street_nr', $recipient ) ) {
        $package['destination']['address'] .= ' ' . $recipient['street_nr'];
    } elseif ( array_key_exists( 'street_no', $recipient ) ) {
        $package['destination']['address'] .= ' ' . $recipient['street_no'];
    }

    return $package;
}


/**
 * Getting Carriers
 *
 * @return array $carriers
 */
private function get_carriers() {
    $carriers = [];

    if ( function_exists( 'wc_get_shipping_zone' ) ) {
        $this->log( 'Shipping zone exist' );
        $shipping_zone 		= wc_get_shipping_zone( $this->get_package() );
        $shipping_methods 	= $shipping_zone->get_shipping_methods( true );

        foreach ( $shipping_methods as $shipping_method ) {
            if ( 'WC_Shipping_Shipcloud' !== get_class( $shipping_method ) ) {
                continue;
            }

            $carriers = array_merge( $carriers, $shipping_method->get_allowed_carriers() );
        }

        // Fallback to general settings if there was no shipcloud in shipping zone
        if ( 0 === count( $carriers ) && WC_Shipping_Shipcloud_Utils::get_shipping_method() ) {
            $this->log( 'shipcloud not in shipping zone' );
            $carriers = $this->get_allowed_carriers();
        }
    } elseif ( WC_Shipping_Shipcloud_Utils::get_shipping_method() ) {
        $carriers = $this->get_allowed_carriers();
    }

    return $carriers;
}


/**
 * Get bank information for shop owner.
 * TODO: noch benötigt?
 *
 * @return \Shipcloud\Domain\ValueObject\BankInformation
 */
/*
public function get_bank_information() {
    return new \Shipcloud\Domain\ValueObject\BankInformation(
        $this->get_option( 'bank_name' ),
        $this->get_option( 'bank_code' ),
        $this->get_option( 'bank_account_holder' ),
        $this->get_option( 'bank_account_number' )
   );
}
*/


/**
 * Returns Tracking status HTML
 *
 * @param string $shipment_id
 *
 */
private function get_tracking_status_html( $shipment_id ) {
    $this->api->get_tracking_status( $shipment_id ); // TODO: Methode existiert nicht!
}


/**
 * @return string
 */
public function get_carrier_mail() {
    $carrier_email = $this->get_option( 'carrier_email' );

    if ( ! $carrier_email || 'yes' !== $carrier_email ) {
        return '';
    }

    $order = $this->get_wc_order();
    return $order->get_billing_email();
}


/**
 * Current chosen package type.
 *
 * @return string
 */
/*
public function get_package_type() {
    return 'parcel';
}
*/


/**
 * Map package type to carrier.
 *
 * It is either "all" to be available for all carrier.
 * Otherwise an array limits the usage to those carriers.
 *
 * @see \Woocommerce_Shipcloud_API::get_carriers
 *
 * @return array
 */
/*
private function get_package_carrier_map() {
    return array(
        'books'         => array( 'all' ),
        'bulk'          => array( 'all' ),
        'letter'        => array( 'all' ),
        'parcel'        => array( 'all' ),
        'parcel_letter' => array( 'dhl', 'dpd' ),
   );
}
*/


/*
private function handle_additional_services( $additional_services, $carrier ) {
    $allowed_additional_services = [];

    $shipment_repo = _wcsc_container()->get( '\\Shipcloud\\Repository\\ShipmentRepository' );
    $additional_services_for_carrier = $shipment_repo->additionalServicesForCarrier( $carrier );

    foreach ( $additional_services as $additional_service ) {
        $service_included = array_search( $additional_service['name'], array_column( $additional_services_for_carrier, 'name' ) );

        if ( $service_included !== false ) {
            array_push( $allowed_additional_services, $additional_service );
        }
    }

    return $allowed_additional_services;
}
*/

