<?php

/**
 * shipcloud for WooCommerce API
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package shipcloudForWooCommerce/API
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2017 (support@awesome.ug)
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class Woocommerce_Shipcloud_API
{
	/**
	 * API Key
	 *
	 * @var string $api_key
	 * @since 1.0.0
	 */
	private $api_key = null;

	/**
	 * API Url
	 *
	 * @var string $api_url
	 * @since 1.0.0
	 */
	private $api_url = null;

  /**
   * API timeout setting
   *
   * @since 1.14.0
   */
  private $api_timeout = null;

	/**
	 * Saved shipcloud settings
	 *
	 * @var array $settings
	 * @since 1.0.0
	 */
	private $settings = array();

	/**
	 * Shipcloud service informations
	 *
	 * @deprecated 2.0.0 Should be in some separate storage and not defined within the class.
	 *
	 * @var array $services
	 * @since 1.0.0
	 */
	private $services = array();

  /**
   * Carrier names that need special handling
	 * @since 1.14.1
   */
  const CARRIERS_WITH_UNDERSCORE = array(
    'angel_de',
    'cargo_international',
    'db_schenker',
    'dhl_express',
    'parcel_one'
  );

  /**
   * Fallback value for api timeout
  * @since 1.14.2
   */
  const API_TIMEOUT_DEFAULT = 10;

  /**
	 * Woocommerce_Shipcloud_API constructor.
	 *
	 * @param string $api_key
	 *
	 * @since 1.0.0
	 */
	public function __construct( $api_key = null )
	{
		$this->api_key = $api_key;
		$this->api_url = 'https://api.shipcloud.io/v1';
    $this->api_timeout = static::API_TIMEOUT_DEFAULT;

    $this->settings = get_option( 'woocommerce_shipcloud_settings' );
    if ( is_array($this->settings) ) {
      if( array_key_exists( 'api_timeout', $this->settings ) ) {
        $api_timeout = $this->settings['api_timeout'];

        if (!isset($api_timeout) || "" == $api_timeout) {
          $this->api_timeout = static::API_TIMEOUT_DEFAULT;
        }
      }
    }

		$this->services = array(
			'standard'      => array(
				'name'             => __( 'Standard', 'shipcloud-for-woocommerce' ),
				'description'      => __( 'Normal shipping', 'shipcloud-for-woocommerce' ),
				'customer_service' => true
			),
			'one_day'       => array(
				'name'             => __( 'Express (1 Day)', 'shipcloud-for-woocommerce' ),
				'description'      => __( 'Express shipping where the package will arrive the next day', 'shipcloud-for-woocommerce' ),
				'customer_service' => true
			),
			'one_day_early' => array(
				'name'             => __( 'Express (1 Day Early)', 'shipcloud-for-woocommerce' ),
				'description'      => __( 'Express shipping where the package will arrive the next day until 12pm', 'shipcloud-for-woocommerce' ),
				'customer_service' => true
			),
			'same_day'      => array(
				'name'             => __( 'Same Day', 'shipcloud-for-woocommerce' ),
				'description'      => __( 'Same Day Delivery', 'shipcloud-for-woocommerce' ),
				'customer_service' => true
			),
			'returns'       => array(
				'name'             => __( 'Returns', 'shipcloud-for-woocommerce' ),
				'description'      => __( 'Shipments that are being send back to the shop', 'shipcloud-for-woocommerce' ),
				'customer_service' => false
			),
			'ups_express_1200' => array(
				'name' => __( 'Express 12:00', 'shipcloud-for-woocommerce' ),
				'description'      => __( 'Delivery by noon of the next business day throughout the country.', 'shipcloud-for-woocommerce' ),
				'customer_service' => false
			),
			'dpag_warenpost' => array(
				'name' => __( 'Warenpost', 'shipcloud-for-woocommerce' ),
				'description'      => __( 'Small trackable letter delivery', 'shipcloud-for-woocommerce' ),
				'customer_service' => false
			),
            'dhl_europaket' => array(
                'name' => __( 'Europaket', 'shipcloud-for-woocommerce' ),
                'description' => __( 'B2B parcel shipments delivered mostly within 48 hours', 'shipcloud-for-woocommerce' ),
                'customer_service' => false
            ),
            'ups_expedited' => array(
                'name' => __( 'Expedited', 'shipcloud-for-woocommerce' ),
                'description' => __( 'For sending less urgent shipments to destinations outside of Europe', 'shipcloud-for-woocommerce' ),
                'customer_service' => false
            ),
            'cargo_international_express' => array(
              'name' => __( 'Express', 'shipcloud-for-woocommerce' ),
              'description' => __( 'Express delivery for Cargo International shipments', 'shipcloud-for-woocommerce' ),
              'customer_service' => false
      ),
      'gls_express_0800' => array(
        'name' => __( 'Express 08:00', 'shipcloud-for-woocommerce' ),
        'description' => __( 'Express delivery for GLS shipments that should be delivered by 8am', 'shipcloud-for-woocommerce' ),
        'customer_service' => false
      ),
      'gls_express_0900' => array(
        'name' => __( 'Express 09:00', 'shipcloud-for-woocommerce' ),
        'description' => __( 'Express delivery for GLS shipments that should be delivered by 9am', 'shipcloud-for-woocommerce' ),
        'customer_service' => false
      ),
      'gls_express_1000' => array(
        'name' => __( 'Express 10:00', 'shipcloud-for-woocommerce' ),
        'description' => __( 'Express delivery for GLS shipments that should be delivered by 10am', 'shipcloud-for-woocommerce' ),
        'customer_service' => false
      ),
      'gls_express_1200' => array(
        'name' => __( 'Express 12:00', 'shipcloud-for-woocommerce' ),
        'description' => __( 'Express delivery for GLS shipments that should be delivered by 12am', 'shipcloud-for-woocommerce' ),
        'customer_service' => false
      ),
      'gls_pick_and_ship' => array(
        'name' => __( 'Pick&ShipService', 'shipcloud-for-woocommerce' ),
        'description' => __( 'Using the Pick&ShipService you can request GLS to pick up a parcel at the address of your choice and deliver it directly to the recipient.', 'shipcloud-for-woocommerce' ),
        'customer_service' => false
      ),
      'dpag_warenpost_untracked' => array(
        'name' => __( 'Warenpost (untracked)', 'shipcloud-for-woocommerce' ),
        'description' => __( 'Small untracked letter delivery', 'shipcloud-for-woocommerce' ),
        'customer_service' => false
      ),
      'dpag_warenpost_signature' => array(
        'name' => __( 'Warenpost (with signature)', 'shipcloud-for-woocommerce' ),
        'description' => __( 'Small trackable letter delivery which the recipient has to sign upon delivery', 'shipcloud-for-woocommerce' ),
        'customer_service' => false
      ),
      'dhl_warenpost' => array(
        'name' => __( 'Warenpost', 'shipcloud-for-woocommerce' ),
        'description'      => __( 'Small trackable letter delivery', 'shipcloud-for-woocommerce' ),
        'customer_service' => false
      ),
      'dhl_prio' => array(
        'name' => __( 'Prio', 'shipcloud-for-woocommerce' ),
        'description'      => __( 'Priority shipping using DHL', 'shipcloud-for-woocommerce' ),
        'customer_service' => false
      ),
		);

        $this->package_types = array(
            'letter' => _x( 'Letter', 'package type: letter', 'shipcloud-for-woocommerce' ),
            'parcel_letter' => _x( 'Parcel letter', 'package type: parcel letter', 'shipcloud-for-woocommerce' ),
            'books' => _x( 'Books', 'package type: books', 'shipcloud-for-woocommerce' ),
            'parcel' => _x( 'Parcel', 'package type: parcel', 'shipcloud-for-woocommerce' ),
            'bulk' => _x( 'Bulk', 'package type: bulk', 'shipcloud-for-woocommerce' ),
            'disposable_pallet' => _x( 'Disposable pallet', 'package type: disposable pallet', 'shipcloud-for-woocommerce' ),
            'euro_pallet' => _x( 'Euro pallet', 'package type: euro pallet', 'shipcloud-for-woocommerce' ),
            'cargo_international_large_parcel' => _x( 'Large Parcel', 'package type: cargo international large parcel', 'shipcloud-for-woocommerce' ),
        );

        $this->customs_declaration_contents_types = array(
            'commercial_goods' => __( 'Commercial goods', 'shipcloud-for-woocommerce' ),
            'commercial_sample' => __( 'Commercial sample', 'shipcloud-for-woocommerce' ),
            'documents' => __( 'Documents', 'shipcloud-for-woocommerce' ),
            'gift' => __( 'Gift', 'shipcloud-for-woocommerce' ),
            'returned_goods' => __( 'Returned goods', 'shipcloud-for-woocommerce' ),
        );

    $this->label_formats = array(
      'placeholder' =>  '',
      'pdf_a5' => __( 'PDF A5', 'shipcloud-for-woocommerce' ),
      'pdf_a6' => __( 'PDF A6', 'shipcloud-for-woocommerce' ),
      'pdf_a7' => __( 'PDF A7', 'shipcloud-for-woocommerce' ),
      'pdf_100x70mm' => __( 'PDF 100x70mm', 'shipcloud-for-woocommerce' ),
      'pdf_103x199mm' => __( 'PDF 103x199mm', 'shipcloud-for-woocommerce' ),
      'zpl2_4x6in_203dpi' => __( 'ZPL2 4x6in (203dpi)', 'shipcloud-for-woocommerce' ),
      'zpl2_4x6in_203dpi_td' => __( 'ZPL2 4x6in (203dpi td)', 'shipcloud-for-woocommerce' ),
      'zpl2_4x6in_300dpi' => __( 'ZPL2 4x6in (300dpi)', 'shipcloud-for-woocommerce' ),
      'zpl2_4x8in_203dpi' => __( 'ZPL2 4x8in (203dpi)', 'shipcloud-for-woocommerce' ),
      'zpl2_100x70mm_203dpi' => __( 'ZPL2 100x70mm(203dpi)', 'shipcloud-for-woocommerce' ),
      'zpl2_103x199mm_203dpi' => __( 'ZPL2 103x199mm (203dpi)', 'shipcloud-for-woocommerce' ),
      'zpl2_a6_203dpi' => __( 'ZPL2 DIN A6 (203dpi)', 'shipcloud-for-woocommerce' ),
    );
	}

	public function is_valid() {
		return '' !== (string) $this->api_key;
	}

    /**
     * Retrieve all contents_types available for customs declaration
     *
     * @since 1.10.0
     * @return array
     */
    public function get_customs_declaration_contents_types() {
        return $this->customs_declaration_contents_types;
    }

	/**
	 * Retrieve all package types.
	 *
	 * @return array
	 */
	public function get_package_types() {
		return $this->package_types;
	}

    /**
	 * Retrieve all services.
	 *
	 * @return array
	 */
	public function get_services() {
		return $this->services;
	}

  /**
   * Retrieve a mapping of label format keys to display names
   *
   * @since 1.14.0
   * @return array
   */
  public function get_label_format_display_names() {
    return $this->label_formats;
  }

  /**
   * Return the api_timout value from plugin settings
   *
   * @since 1.14.0
   * @return string
   */
  public function get_api_timeout() {
    return $this->api_timeout;
  }

	/**
	 * Is the service a customer Service
	 *
	 * @param string $service_id
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_customer_service( $service_id ) {
		if ( ! array_key_exists( $service_id, $this->services ) ) {
			return false;
		}

		if ( $this->services[ $service_id ]['customer_service'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Getting display name for a carrier including service
	 *
	 * @param string $carrier_name
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function get_carrier_display_name( $carrier_name ) {
		$carriers = $this->get_carriers();

		foreach ( $carriers AS $carrier ) {
			if ( $carrier['name'] == $carrier_name ) {
				return $carrier['display_name'];
			}
		}

		return false;
	}

	/**
	 * Getting carriers
	 *
	 * @param bool $force_update
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_carriers( $force_update = false )
	{
		$shipment_carriers = get_option( 'woocommerce_shipcloud_carriers', false );

		if ( empty( $shipment_carriers ) || true === $force_update )
		{
      if ( empty( $shipment_carriers ) ) {
        WC_Shipcloud_Shipping::log('Carriers empty. Updating from shipcloud api');
      } elseif (true === $force_update) {
        WC_Shipcloud_Shipping::log('Forced carriers update');
      }
			$shipment_carriers = $this->update_carriers();

			if( is_wp_error( $shipment_carriers ) ){
				WC_Shipcloud_Shipping::log('Couldn\'t get carriers. '.$shipment_carriers->get_error_message());
				return $shipment_carriers;
			}
			WooCommerce_Shipcloud::admin_notice( __( 'Carriers have been updated!', 'shipcloud-for-woocommerce' ) );
		}

		if ( is_wp_error( $shipment_carriers ) )
		{
			return $shipment_carriers;
		}

    WC_Shipcloud_Shipping::log('Carriers: '.json_encode($shipment_carriers));

		$carriers = array();
		foreach ( $shipment_carriers AS $shipment_carrier )
		{
			if ( isset( $shipment_carrier[ 'services' ] ) )
			{
				foreach ( (array) $shipment_carrier[ 'services' ] AS $service )
				{
					$carriers[] = array(
						'name'         => $shipment_carrier[ 'name' ] . '_' . $service,
						'display_name' => $shipment_carrier[ 'display_name' ] . ' - ' . $this->get_service_name( $service )
					);
				}
			}
			else
			{
				$carriers[] = array(
					'name'         => $shipment_carrier[ 'name' ],
					'display_name' => $shipment_carrier[ 'display_name' ]
				);
			}
		}

		return $carriers;
	}

	/**
	 * Updating carriers and saving in WP options
	 *
	 * @return array|WP_Error
	 * @since 1.0.0
	 */
	private function update_carriers()
	{
		$shipment_carriers = $this->request_carriers();

		if ( is_wp_error( $shipment_carriers ) || false === $shipment_carriers )
		{
			return $shipment_carriers;
		}

    WC_Shipcloud_Shipping::log('Updating woocommerce_shipcloud_carriers');
		update_option( 'woocommerce_shipcloud_carriers', $shipment_carriers );

		return $shipment_carriers;
	}

	/**
	 * Requesting carriers
	 *
	 * @return array|\Shipcloud\Domain\Carrier[]|WP_Error
	 * @since 1.0.0
	 * @since 1.4.0 Returns \Shipcloud\Domain\Carrier[] which will replace the simple array.
	 *
	 * @deprecated 2.0.0 Use Carriers::get() instead.
	 */
	public function request_carriers()
	{
		$api = new Shipcloud\Api( $this->api_key, 'plugin.woocommerce.z4NVoYhp', $this->api_url );

		try {
			return $api->carriers()->get();
		} catch (\Exception $e) {
			WC_Shipcloud_Shipping::log(print_r($e, true));
			return new \WP_Error( 'shipcloud_api_error_' . $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Sends a request to the API
	 *
	 * @param string $action
	 * @param array $params
	 * @param string $method
	 *
	 * @return array $response_arr
	 * @since 1.0.0
	 */
	private function send_request( $action = '', $params = array(), $method = 'GET' )
	{
		$count_requests = get_option( 'woocommerce_shipcloud_count_requests', 0 ) + 1;
		update_option( 'woocommerce_shipcloud_count_requests', $count_requests );

		$args = array(
			'timeout' => 10,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->api_key ),
				'Content-Type'  => 'application/json',
				'Affiliate-ID'  => 'plugin.woocommerce.z4NVoYhp'
			),
			'method'  => strtoupper( $method ),
		);

		if ( 'PUT' === $args['method'] || 'POST' === $args['method'] ) {
			$args['body'] = json_encode( $this->sanitize_params( $params ) );
		}

		$response = wp_remote_request( $this->get_endpoint( $action ), $args );

		if ( is_wp_error( $response ) ) {
			WC_Shipcloud_Shipping::log('WP_Error while sending request to shipcloud api');
			WC_Shipcloud_Shipping::log('Error message: '.$response->get_error_message());
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );

		// Decode if it's json
		if ( wp_remote_retrieve_header( $response, 'content-type' ) === 'application/json; charset=utf-8' ) {
			$body = json_decode( $body, true );
		}

		return array(
			'header' => array(
				'status' => wp_remote_retrieve_response_code( $response )
			),
			'body'   => $body
		);
	}

	/**
	 * Clean up invalid params.
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	protected function sanitize_params( $params ) {
		if ( isset( $params['from'] ) ) {
			$params['from'] = $this->sanitize_params_from( $params['from'] );
		}

		return $params;
	}

	/**
	 * Clean up invalid from fields.
	 *
	 * The user can define a default sender address
	 * in the shipcloud.io backend
	 * and does not need to fill out the shipcloud config in WooCommerce.
	 * Unfortunately the WooCommerce settings have a default for the country
	 * so the from-data always contain a country
	 * making the API consider the from-address as incomplete.
	 * To suppress this the from address will be sanitized before submitting
	 * removing the from address when it only contains the country.
	 *
	 * @param array $from_data
	 *
	 * @return array
	 */
	protected function sanitize_params_from( $from_data ) {
		$from_data = (array) $from_data;
		foreach ( $from_data as $id => $value ) {
			if ( ! trim( $value ) ) {
				unset( $from_data[ $id ] );
			}
		}

		if ( array_key_exists( 'country', $from_data ) && 1 === count( $from_data ) ) {
			// Seems like no address configured => make completely empty then.
			return array();
		}

		return $from_data;
	}

	/**
	 * Getting API endpoint
	 *
	 * @param string $action
	 *
	 * @return string $api_url
	 * @since 1.0.0
	 */
	private function get_endpoint( $action )
	{
		return $this->api_url . '/' . $action;
	}

	/**
	 * Getting error string of a request
	 *
	 * @param $request
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function get_error( $request )
	{
		$error = $this->translate_error_code( $request[ 'header' ][ 'status' ] );

		if ( ! isset( $request['body'] ) ) {
			return $error;
		}

		$error_string = $this->get_body_errors( $request['body'] );
		if ( false !== $error_string ) {
			$error['description'] = $error_string;
		}

		return $error;
	}

	/**
	 * Translating error codes to a readable string
	 *
	 * @param int $error_code
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function translate_error_code( $error_code )
	{
		$error_codes = array(
			'204' => array(
				'name'        => 'no content',
				'description' => __( 'There is no message body. You\'ll get this code when deleting a shipment was successful.', 'shipcloud-for-woocommerce' )
			),
			'400' => array(
				'name'        => 'bad_request',
				'description' => __( 'Your request was not correct. Please see the response body for more detailed information.', 'shipcloud-for-woocommerce' )
			),
			'401' => array(
				'name'        => 'access_denied',
				'description' => __( 'Access denied! Please check your api key.', 'shipcloud-for-woocommerce' )
			),
			'402' => array(
				'name'        => 'payment_required',
				'description' => __( 'You\'ve reached the maximum number of requests for your current plan. Please upgrade to a higher plan.', 'shipcloud-for-woocommerce' )
			),
			'403' => array(
				'name'        => 'forbidden',
				'description' => __( 'You are not allowed to talk to this endpoint. This can either be due to a wrong authentication or when you\'re trying to reach an endpoint that your account isn\'t allowed to access.', 'shipcloud-for-woocommerce' )
			),
			'404' => array(
				'name'        => 'not_found',
				'description' => __( 'The api endpoint you were trying to reach can\'t be found.', 'shipcloud-for-woocommerce' )
			),
			'422' => array(
				'name'        => 'unprocessable_entity',
				'description' => __( 'Your request was well-formed but couldn\'t be followed due to semantic errors. Please see the response body for more detailed information.', 'shipcloud-for-woocommerce' )
			),
			'500' => array(
				'name'        => 'internal_server_error',
				'description' => __( 'Something has seriously gone wrong. Don\'t worry, we\'ll have a look at it.', 'shipcloud-for-woocommerce' )
			),
			'502' => array(
				'name'        => 'bad_gateway',
				'description' => __( 'Something has gone wrong while talking to the carrier backend. Please see the response body for more detailed information.', 'shipcloud-for-woocommerce' )
			),
			'504' => array(
				'name'        => 'gateway_timeout',
				'description' => __( 'Unfortunately we couldn\'t connect to the carrier backend. It is either very slow or not reachable at all. If you want to stay informed about the carrier status, follow our developer twitter account at @shipcloud_devs.', 'shipcloud-for-woocommerce' )
			)
		);

		if ( ! array_key_exists( $error_code, $error_codes ) )
		{
			return array( 'name' => 'unknown', 'description' => __( 'Unknown error', 'shipcloud-for-woocommerce' ) );
		}

		return $error_codes[ $error_code ];
	}

	/**
	 * Getting errors of a shipcloud body response and creating a string
	 *
	 * @param array $body
	 *
	 * @return bool|string
	 * @since 1.0.0
	 */
	private function get_body_errors( $body )
	{
		if ( isset( $body[ 'errors' ] ) )
		{
			$error_str = '';

			if ( ! is_array( $body['errors'] ) ) {
				return $error_str . wcsc_translate_shipcloud_text( $body['errors'] ) . chr( 13 );
			}

			foreach ( $body['errors'] as $error ) {
				$error_str .= wcsc_translate_shipcloud_text( $error ) . chr( 13 );
			}

			return $error_str;
		}

		return wcsc_translate_shipcloud_text( $body );
	}

	/**
	 * Getting service name by service id
	 *
	 * @param int $service_id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_service_name( $service_name ) {
		if (array_key_exists($service_name, $this->services)) {
			return $this->services[ $service_name ]['name'];
		} else {
			return $service_name;
		}
	}

	/**
	 * Getting display name for a carrier (short name) including service
	 *
	 * @param string $carrier_name
	 *
	 * @internal 2.0.0 Use proper Carrier instance as argument.
	 *
	 * @return string|array
	 * @since 1.0.0
	 */
	public function get_carrier_display_name_short( $carrier_name ) {
		if (is_array($carrier_name)) {
			/** @deprecated 2.0.0 Carrier name will be determined via array in next version. */
			$carrier_name = $carrier_name['carrier'] . '_' . $carrier_name['service'];
		}

		$carrier_name_arr = $this->disassemble_carrier_name( $carrier_name );
		$display_name     = wcsc_get_carrier_display_name( $carrier_name_arr['carrier'] ) . ' - ' . $this->get_service_name( $carrier_name_arr['service'] );

		return $display_name;
	}

	/**
	 * Splitting carrier name into API usable carrier name and service name
	 *
	 * @param string $carrier_name
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function disassemble_carrier_name( $carrier_name ) {
    $carrier_arr = explode( '_', $carrier_name );
    $service = '';

    $carrier_with_underscore_name = $carrier_arr[0].'_'.$carrier_arr[1];
    if (in_array($carrier_with_underscore_name, static::CARRIERS_WITH_UNDERSCORE)) {
      $carrier = $carrier_with_underscore_name;
      $array_start_index = 2;
    } else {
      $carrier = $carrier_arr[0];
      $array_start_index = 1;
    }


		for ( $i = $array_start_index; $i < count( $carrier_arr ); $i ++ ) {
			$service .= $i == $array_start_index ? '' : '_';
			$service .= $carrier_arr[ $i ];
		}

		return array(
			'carrier' => $carrier,
			'service' => $service
		);
	}

	/**
	 * Getting service description by service id
	 *
	 * @param string $service_id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_service_description( $service_id ) {
		if ( ! array_key_exists( $service_id, $this->services ) ) {
			return false;
		}

		return $this->services[ $service_id ]['description'];
	}

	/**
	 * Getting the price for a shipment
	 *
	 * @param string $carrier
	 * @param array $from
	 * @param array $to
	 * @param array $package
	 *
	 * @return float|WP_Error
	 * @since 1.0.0
	 */
	public function get_price( $carrier, $from, $to, $package = array(), $service = '' )
	{
		$action = 'shipment_quotes';

		if ( ! $service && strpos( $carrier, '_' ) ) {
			$data = $this->disassemble_carrier_name( $carrier );
			$carrier = $data['carrier'];
			$service = $data['service'];
		}

		$params = array(
			'carrier' => $carrier,
			'service' => $service,
			'to'      => $to,
			'from'    => $from,
			'package' => $package
		);

		WC_Shipcloud_Shipping::log('ShipmentsQuote request with params:');
		WC_Shipcloud_Shipping::log(json_encode($params));
		$request = $this->send_request( $action, $params, 'POST' );

		if ( false !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			return $request[ 'body' ][ 'shipment_quote' ][ 'price' ];
		}
		else
		{
			$error = $this->get_error( $request );
			WC_Shipcloud_Shipping::log('Error while getting ShipmentQuote.');
			WC_Shipcloud_Shipping::log('Error message: '.$error[ 'description' ]);
			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
		}
	}

	/**
	 * Create shipment label for an order.
	 *
	 * @param WC_Shipcloud_Order $order
	 * @param string $carrier
	 * @param string $package
	 *
	 * @return string|\WP_Error
	 */
	public function create_shipment_by_order( WC_Shipcloud_Order $order, $carrier, $package ) {
		// $reference_number = sprintf(
		// 	__( 'Order %s', 'shipcloud-for-woocommerce' ),
		// 	$order->get_wc_order()->get_order_number()
		// );

		/**
		 * Filtering reference number
		 *
		 * @param string $reference_number The Reference Number
		 * @param string $order_number The WooCommerce order number
		 * @param string $order_id The WooCommerce order id
		 *
		 * @return string $reference_number The filtered order number
		 * @since 1.1.0
		 */
		// $reference_number = apply_filters(
		// 	'wcsc_reference_number',
		// 	$reference_number,
		// 	$order->get_wc_order()->get_order_number(),
		// 	$order->get_wc_order()->id
		// );

		return $this->create_shipment(
			$carrier,
			$order->get_sender(),
			$order->get_recipient(),
			$package,
			true,
			$order->get_notification_email(),
			$order->get_carrier_mail()
		);
	}

	/**
	 * Creating a shipment
	 *
	 * @param string $carrier
	 * @param array  $from
	 * @param array  $to
	 * @param array  $package
	 * @param bool   $create_label
	 *
	 * @return array|\WP_Error
	 * @since 1.0.0
	 */
	public function create_shipment( $carrier, $from, $to, $package, $create_label = false, $notification_email = '', $carrier_email = '', $reference_number = '', $description = '' ) {
		if (!is_array($carrier)) {
			$carrier = $this->disassemble_carrier_name( $carrier );
		}

		$params = $this->get_params_by_carrier(
			$carrier,
			$from,
			$to,
			$package,
			$create_label,
			$notification_email,
			$carrier_email,
			$reference_number,
			$description
		);

		WC_Shipcloud_Shipping::log('Shipments request with params:');
		WC_Shipcloud_Shipping::log(json_encode($params));

		$request = $this->send_request( 'shipments', $params, 'POST' );

		if ( is_wp_error( $request ) )
		{
			WC_Shipcloud_Shipping::log('WP_Error while creating a shipment.');
			WC_Shipcloud_Shipping::log('Error message: '.$error[ 'description' ]);
			return $request;
		}

		if ( false === $request || 2 !== (int) ( $request['header']['status'] / 100 ) )
		{
			$error = $this->get_error( $request );
			WC_Shipcloud_Shipping::log('Error while creating a shipment.');
			WC_Shipcloud_Shipping::log('Error message: '.$error[ 'description' ]);
			return new \WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
		}

		$body = $request[ 'body' ];
		if ( ! $create_label )
		{
			$body['carrier_tracking_no'] = '';
			$body['label_url'] = '';
			$body['price'] = '';
		}

		return $body;
	}

	/**
	 * @param $carrier
	 * @param $from
	 * @param $to
	 * @param $package
	 * @param $create_label
	 * @param $notification_email
	 * @param $carrier_email
	 * @param $reference_number
	 * @param $description
	 *
	 * @return array
	 */
	public function get_params_by_carrier( $carrier, $from, $to, $package, $create_label, $notification_email, $carrier_email, $reference_number = null, $description = null ) {
		$params = array(
			'carrier'               => $carrier['carrier'],
			'service'               => $carrier['service'],
			'from'                  => $from,
			'to'                    => $to,
			'package'               => $package,
			'create_shipping_label' => $create_label,
			'notification_email'    => $notification_email,
		);

		switch ( $carrier['carrier'] ) {
			case 'dpd':
				$params['notification_email'] = $carrier_email;
			case 'dhl':
				$params['additional_services'] = array();

				if ( ! empty ( $carrier_email ) ) {
                    $converter = new \Shipcloud\I18n\Cldr_Converter();
                    $notification_language =
                        $converter->language_from_country_code($to['country']);

					$params['additional_services'] = array(
						array(
							'name'       => 'advance_notice',
							'properties' => array(
								'email'    => $carrier_email,
								'language' => $notification_language
							)
						)
					);
				}

				break;

			case 'ups':
				unset( $params['to']['email'] );

				// Moving the description to the root on international shipment
				if ( $from['country'] !== $to['country'] ) {
					$params['description'] = $params['package']['description'];
					unset( $params['package']['description'] );
				}

				break;

			default:
				unset( $params['to']['email'] );

				break;
		}

		if ( $description ) {
			$params['description'] = $description;
		}

		if ( ! empty( $reference_number ) ) {
			$params['reference_number'] = $reference_number;
		}

		return $params;
	}

	/**
	 * Creating a shipping label
	 *
	 * @param string $shipment_id
	 * @param array  $params
	 *
	 * @return array|WP_Error
	 * @since 1.0.0
	 */
	public function create_label( $shipment_id, $params = array() )
	{
		$params['create_shipping_label'] = true;

		$action  = 'shipments/' . $shipment_id;
		$request = $this->send_request( $action, $params, 'PUT' );

		if ( false !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			return $request;
		}

		$error = $this->get_error( $request );
		WC_Shipcloud_Shipping::log('Error while creating a shipping label.');
		WC_Shipcloud_Shipping::log('Error message: '.$error[ 'description' ]);

		return new WP_Error( 'shipcloud_api_error_' . $error['name'], $error['description'] );
	}

	/**
	 * Deleting a shipment
	 *
	 * @param string $shipment_id
	 *
	 * @return array $request_data
	 * @since 1.0.0
	 */
	public function delete_shipment( $shipment_id )
	{
		$params = array();

		$action  = 'shipments/' . $shipment_id;
		$request = $this->send_request( $action, $params, 'DELETE' );

		if ( is_wp_error( $request ) ) {
			WC_Shipcloud_Shipping::log('WP_Error while deleting a shipment.');
			WC_Shipcloud_Shipping::log('Error message: '.$error[ 'description' ]);
			return $request;
		}

		$request_status = (int) $request[ 'header' ][ 'status' ];

		if ( 204 === $request_status )
		{
			return true;
		}

		$error = $this->get_error( $request );
		WC_Shipcloud_Shipping::log('Error while deleting a shipment.');
		WC_Shipcloud_Shipping::log('Error message: '.$error[ 'description' ]);

		return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
	}

	/**
	 * Getting the tracking status of a shipment
	 *
	 * @param $shipment_id
	 *
	 * @return array $request_data
	 * @since 1.0.0
	 */
	public function get_tracking_status( $shipment_id )
	{
		$request_data = $this->send_request( 'shipments/' . $shipment_id );

		return $request_data;
	}

	/**
	 * Requesting a pickup
	 *
	 * @param array $params
	 *
	 * @return array|WP_Error
	 * @since 1.0.0
	 */
	public function request_pickup( $params )
	{
		// Todo: Finish it!
		$action  = 'pickup_requests';
		$request = $this->send_request( $action, $params, 'POST' );

		if ( false !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			return $request[ 'body' ];
		}

		$error = $this->get_error( $request );
		WC_Shipcloud_Shipping::log('Error while creating a PickupRequest.');
		WC_Shipcloud_Shipping::log('Error message: '.$error[ 'description' ]);

		return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
	}

    /**
     * Create a pickup request
     *
     * @param array $params
     *
     * @return array|WP_Error
     *
     * @since 1.9.0
     */
    public function create_pickup_request( $params ) {
        $response = $this->send_request( 'pickup_requests', $params, 'POST' );

        $status_code = (int) $response[ 'header' ][ 'status' ];

        if ( 200 !== $status_code ) {
            $error = $this->get_error( $response );
            return new WP_Error( $status_code, $error[ 'description' ] );
        }

        return $response[ 'body' ];
    }

	public function create_address_by_pakadoo_id( $pakadoo_id ) {
		$action  = 'addresses';
		$params = array('pakadoo_id' => $pakadoo_id);
		$request = $this->send_request( $action, $params, 'POST' );

		$status_code = (int) $request[ 'header' ][ 'status' ];

		if ( 200 !== $status_code ) {
			$error = $this->get_error( $request );

			return new WP_Error( $status_code, $error[ 'description' ] );
		}

		return $request[ 'body' ];
	}

	public function read_shipment( $shipment_id ) {
		$api = new Shipcloud\Api( $this->api_key, 'plugin.woocommerce.z4NVoYhp', $this->api_url );

		try {
			return $api->shipment()->get($shipment_id);
		} catch (\Exception $e) {
			WC_Shipcloud_Shipping::log(print_r($e, true));
			return new \WP_Error( 'shipcloud_api_error_' . $e->getCode(), $e->getMessage() );
		}
	}

    public function create_webhook() {
        $api = new Shipcloud\Api( $this->api_key, 'plugin.woocommerce.z4NVoYhp', $this->api_url );

        try {
            return $api->webhook()->create();
        } catch (\Exception $e) {
            WC_Shipcloud_Shipping::log(print_r($e, true));
            return new \WP_Error( 'shipcloud_api_error_' . $e->getCode(), $e->getMessage() );
        }
    }

    public function delete_webhook($webhook_id) {
        $params = array();

        $action  = 'webhooks/'.$webhook_id;
        $request = $this->send_request( $action, $params, 'DELETE' );

        if ( is_wp_error( $request ) ) {
            WC_Shipcloud_Shipping::log('WP_Error while deleting a webhook.');
            WC_Shipcloud_Shipping::log('Error message: '.$error[ 'description' ]);
            return $request;
        }

        $request_status = (int) $request[ 'header' ][ 'status' ];

        if ( 204 === $request_status ) {
            delete_option('woocommerce_shipcloud_catch_all_webhook_id');
            return true;
        }

        $error = $this->get_error( $request );
        WC_Shipcloud_Shipping::log('Error while deleting a webhook.');
        WC_Shipcloud_Shipping::log('Error message: '.$error[ 'description' ]);

        return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
    }

  public function get_global_reference_number( $order ) {
    $global_reference_number = wcsc_shipping_method()->get_option( 'global_reference_number' );
    $order_id = $order->order_id;
    if (shipcloud_admin_is_on_single_order_page() && !is_null($order_id)) {
        if ( has_shortcode( $global_reference_number, 'shipcloud_orderid' ) ) {
            $global_reference_number = str_replace('[shipcloud_orderid]', $order_id, $global_reference_number);
        }
    }

    return $global_reference_number;
  }

	/**
	 * Connection testing
	 *
	 * @return bool|WP_Error
	 * @since 1.0.0
	 */
	public function test()
	{
		$action  = 'carriers';
		$request = $this->send_request( $action );

		if ( is_wp_error( $request ) )
		{
			return $request;
		}

		if ( 200 !== (int) $request[ 'header' ][ 'status' ] )
		{
			$error = $this->get_error( $request );

			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
		}

		return true;
	}
}
