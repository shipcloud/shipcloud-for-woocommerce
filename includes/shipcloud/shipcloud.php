<?php

/**
 * WooCommerce Shipcloud API
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/API
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2016 (support@awesome.ug)
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
	 * Saved shipcloud settings
	 *
	 * @var array $settings
	 * @since 1.0.0
	 */
	private $settings = array();

	/**
	 * Shipcloud service informations
	 *
	 * @var array $services
	 * @since 1.0.0
	 */
	private $services = array();

	/**
	 * Woocommerce_Shipcloud_API constructor.
	 *
	 * @param string $api_key
	 *
	 * @since 1.0.0
	 */
	public function __construct( $api_key = null )
	{
		$this->settings = get_option( 'woocommerce_shipcloud_settings' );

		if ( null !== $api_key )
		{
			$this->api_key = $api_key;
		}
		else
		{
			if ( empty( $this->settings[ 'api_key' ] ) )
			{
				return new WP_Error( 'shipcloud_api_error_no_api_key', __( 'No API Key given', 'woocommerce-shipcloud' ) );
			}

			$this->api_key = $this->settings[ 'api_key' ];
		}

		$this->api_url = 'https://api.shipcloud.io/v1';

		$this->services = array(
			'standard'      => array(
				'name'             => __( 'Standard', 'woocommerce-shipcloud' ),
				'description'      => __( 'Normal shipping', 'woocommerce-shipcloud' ),
				'customer_service' => true
			),
			'one_day'       => array(
				'name'             => __( 'Express (1 Day)', 'woocommerce-shipcloud' ),
				'description'      => __( 'Express shipping where the package will arrive the next day', 'woocommerce-shipcloud' ),
				'customer_service' => true
			),
			'one_day_early' => array(
				'name'             => __( 'Express (1 Day Early)', 'woocommerce-shipcloud' ),
				'description'      => __( 'Express shipping where the package will arrive the next day until 12pm', 'woocommerce-shipcloud' ),
				'customer_service' => true
			),
			'same_day'      => array(
				'name'             => __( 'Same Day', 'woocommerce-shipcloud' ),
				'description'      => __( 'Same Day Delivery', 'woocommerce-shipcloud' ),
				'customer_service' => true
			),
			'returns'       => array(
				'name'             => __( 'Returns', 'woocommerce-shipcloud' ),
				'description'      => __( 'Shipments that are being send back to the shop', 'woocommerce-shipcloud' ),
				'customer_service' => false
			)
		);
	}

	/**
	 * Get allowed Carriers
	 *
	 * @param bool $only_customer_services If is set true, function returns only services which are available for customers
	 *
	 * @return array $carriers
	 * @since 1.0.0
	 */
	public function get_allowed_carriers( $only_customer_services = false )
	{
		$allowed_carriers   = $this->settings[ 'allowed_carriers' ];
		$shipcloud_carriers = $this->get_carriers();

		if ( is_wp_error( $shipcloud_carriers ) )
		{
			return $shipcloud_carriers;
		}

		$carriers = array();

		if ( is_array( $allowed_carriers ) )
		{
			foreach ( $shipcloud_carriers AS $shipcloud_carrier )
			{
				if ( $only_customer_services )
				{
					$carrier_arr = $this->disassemble_carrier_name( $shipcloud_carrier[ 'name' ] );
					if ( ! $this->is_customer_service( $carrier_arr[ 'service' ] ) )
					{
						continue;
					}
				}
				if ( in_array( $shipcloud_carrier[ 'name' ], $allowed_carriers ) )
				{
					$carriers[ $shipcloud_carrier[ 'name' ] ] = $shipcloud_carrier[ 'display_name' ];
				}
			}
		}

		return $carriers;
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
			$shipment_carriers = $this->update_carriers();
			WooCommerce_Shipcloud::admin_notice( __( 'Updated Carriers!', 'woocommerce-shipcloud' ) );
		}

		if ( is_wp_error( $shipment_carriers ) )
		{
			return $shipment_carriers;
		}

		$carriers = array();
		foreach ( $shipment_carriers AS $shipment_carrier )
		{
			if ( isset( $shipment_carrier[ 'services' ] ) )
			{
				foreach ( $shipment_carrier[ 'services' ] AS $service )
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

		update_option( 'woocommerce_shipcloud_carriers', $shipment_carriers );

		return $shipment_carriers;
	}

	/**
	 * Requesting carriers
	 *
	 * @return array|WP_Error
	 * @since 1.0.0
	 */
	private function request_carriers()
	{
		$action  = 'carriers';
		$request = $this->send_request( $action );

		if ( is_wp_error( $request ) )
		{
			return $request;
		}

		if ( false !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			return $request[ 'body' ];
		}
		else
		{
			$error = $this->get_error( $request );

			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
		}
	}

	/**
	 * Sends a request to the API
	 *
	 * @param string $action
	 * @param array  $params
	 * @param string $method
	 *
	 * @return array $response_arr
	 * @since 1.0.0
	 */
	private function send_request( $action = '', $params = array(), $method = 'GET' )
	{
		$count_requests = get_option( 'woocommerce_shipcloud_count_requests', 0 ) + 1;
		update_option( 'woocommerce_shipcloud_count_requests', $count_requests );

		$url     = $this->get_endpoint( $action );
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $this->api_key ),
			'Content-Type'  => 'application/json',
			'Affiliate-ID'  => 'plugin.woocommerce.z4NVoYhp'
		);

		$params = json_encode( $params );

		switch ( $method )
		{
			case "GET":

				$args     = array(
					'timeout' => 10,
					'headers' => $headers
				);
				$response = wp_remote_get( $url, $args );

				break;

			case "POST":

				$args = array(
					'timeout' => 10,
					'headers' => $headers,
					'body'    => $params
				);

				$response = wp_remote_post( $url, $args );

				break;

			case "PUT":

				$args     = array(
					'timeout' => 10,
					'headers' => $headers,
					'method'  => 'PUT',
					'body'    => $params
				);
				$response = wp_remote_request( $url, $args );

				break;

			case "DELETE":

				$args = array(
					'timeout' => 10,
					'headers' => $headers,
					'method'  => 'DELETE'
				);

				$response = wp_remote_request( $url, $args );

				break;
		}

		if ( is_wp_error( $response ) )
		{
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );

		// Decode if it's json
		if ( wp_remote_retrieve_header( $response, 'content-type' ) == 'application/json; charset=utf-8' )
		{
			$body = json_decode( $body, true );
		}

		$response_arr = array(
			'header' => array(
				'status' => wp_remote_retrieve_response_code( $response )
			),
			'body'   => $body
		);

		return $response_arr;
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

		if ( isset( $request[ 'body' ] ) )
		{
			$error[ 'description' ] = $this->get_body_errors( $request[ 'body' ] );
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
				'description' => __( 'There is no message body. You\'ll get this code when deleting a shipment was successful.', 'woocommerce-shipcloud' )
			),
			'400' => array(
				'name'        => 'bad_request',
				'description' => __( 'Your request was not correct. Please see the response body for more detailed information.', 'woocommerce-shipcloud' )
			),
			'401' => array(
				'name'        => 'access_denied',
				'description' => __( 'Access denied! Please check your API Key.', 'woocommerce-shipcloud' )
			),
			'402' => array(
				'name'        => 'payment_required',
				'description' => __( 'You\'ve reached the maximum of your current plan. Please upgrade to a higher plan.', 'woocommerce-shipcloud' )
			),
			'403' => array(
				'name'        => 'forbidden',
				'description' => __( 'You are not allowed to talk to this endpoint. This can either be due to a wrong authentication or when you\'re trying to reach an endpoint that your account isn\'t allowed to access.', 'woocommerce-shipcloud' )
			),
			'404' => array(
				'name'        => 'not_found',
				'description' => __( 'The api endpoint you were trying to reach can\'t be found.', 'woocommerce-shipcloud' )
			),
			'422' => array(
				'name'        => 'unprocessable_entity',
				'description' => __( 'Your request was well-formed but couldn\'t be followed due to semantic errors. Please see the response body for more detailed information.', 'woocommerce-shipcloud' )
			),
			'500' => array(
				'name'        => 'internal_server_error',
				'description' => __( 'Something has seriously gone wrong. Don\'t worry, we\'ll have a look at it.', 'woocommerce-shipcloud' )
			),
			'502' => array(
				'name'        => 'bad_gateway',
				'description' => __( 'Something has gone wrong while talking to the carrier backend. Please see the response body for more detailed information.', 'woocommerce-shipcloud' )
			),
			'504' => array(
				'name'        => 'gateway_timeout',
				'description' => __( 'Unfortunately we couldn\'t connect to the carrier backend. It is either very slow or not reachable at all. If you want to stay informed about the carrier status, follow our developer twitter account at @shipcloud_devs.', 'woocommerce-shipcloud' )
			)
		);

		if ( ! array_key_exists( $error_code, $error_codes ) )
		{
			return array( 'name' => 'unknown', 'description' => __( 'Unknown Error', 'woocommerce-shipcloud' ) );
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

			foreach ( $body[ 'errors' ] as $error )
			{
				$error_str .= wcsc_translate_shipcloud_text( $error ) . chr( 13 );
			}

			return $error_str;
		}
		else
		{
			return wcsc_translate_shipcloud_text( $body );
		}
	}

	/**
	 * Getting service name by service id
	 *
	 * @param int $service_id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_service_name( $service_id )
	{
		if ( ! array_key_exists( $service_id, $this->services ) )
		{
			return false;
		}

		return $this->services[ $service_id ][ 'name' ];
	}

	/**
	 * Splitting carrier name into API usable carrier name and service name
	 *
	 * @param string $carrier_name
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function disassemble_carrier_name( $carrier_name )
	{
		$carrier_arr = explode( '_', $carrier_name );

		$carrier = $carrier_arr[ 0 ];
		$service = '';

		for ( $i = 1; $i < count( $carrier_arr ); $i ++ )
		{
			$service .= $i == 1 ? '' : '_';
			$service .= $carrier_arr[ $i ];
		}

		return array(
			'carrier' => $carrier,
			'service' => $service
		);
	}

	/**
	 * Is the service a customer Service
	 *
	 * @param string $service_id
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_customer_service( $service_id )
	{
		if ( ! array_key_exists( $service_id, $this->services ) )
		{
			return false;
		}

		if ( $this->services[ $service_id ][ 'customer_service' ] )
		{
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
	public function get_carrier_display_name( $carrier_name )
	{
		$carriers = $this->get_carriers();

		foreach ( $carriers AS $carrier )
		{
			if ( $carrier[ 'name' ] == $carrier_name )
			{
				return $carrier[ 'display_name' ];
			}
		}

		return false;
	}

	/**
	 * Getting display name for a carrier (short name) including service
	 *
	 * @param string $carrier_name
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_carrier_display_name_short( $carrier_name )
	{
		$carrier_name_arr = $this->disassemble_carrier_name( $carrier_name );
		$display_name     = strtoupper( $carrier_name_arr[ 'carrier' ] ) . ' - ' . $this->get_service_name( $carrier_name_arr[ 'service' ] );

		return $display_name;
	}

	/**
	 * Getting service description by service id
	 *
	 * @param string $service_id
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_service_description( $service_id )
	{
		if ( ! array_key_exists( $service_id, $this->services ) )
		{
			return false;
		}

		return $this->services[ $service_id ][ 'description' ];
	}

	/**
	 * Getting the price for a shipment
	 *
	 * @param string $carrier
	 * @param array  $from
	 * @param array  $to
	 * @param array  $package
	 *
	 * @return float|WP_Error
	 * @since 1.0.0
	 */
	public function get_price( $carrier, $from, $to, $package )
	{
		$action = 'shipment_quotes';

		$carrier = $this->disassemble_carrier_name( $carrier );

		$params = array(
			'carrier' => $carrier[ 'carrier' ],
			'service' => $carrier[ 'service' ],
			'to'      => $to,
			'from'    => $from,
			'package' => $package
		);

		$request = $this->send_request( $action, $params, 'POST' );

		if ( false !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			return $request[ 'body' ][ 'shipment_quote' ][ 'price' ];
		}
		else
		{
			$error = $this->get_error( $request );

			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
		}
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
	 * @return string|WP_Error
	 * @since 1.0.0
	 */
	public function create_shipment( $carrier, $from, $to, $package, $create_label = false )
	{
		$carrier = $this->disassemble_carrier_name( $carrier );

		switch ( $carrier[ 'carrier' ] )
		{

			case 'dpd':
				$to_email = $to[ 'email' ];
				unset( $to[ 'email' ] );

				$params = array(
					'carrier'               => $carrier[ 'carrier' ],
					'service'               => $carrier[ 'service' ],
					'from'                  => $from,
					'to'                    => $to,
					'package'               => $package,
					'create_shipping_label' => $create_label,
					'additional_services'   => array( // Needed for
						array(
							'name'       => 'advance_notice',
							'properties' => array(
								'email'    => $to_email,
								'language' => strtolower( $to[ 'country' ] )
							)
						)
					)
				);

				break;

			default:
				unset( $to[ 'email' ] );
				$params = array(
					'carrier'               => $carrier[ 'carrier' ],
					'service'               => $carrier[ 'service' ],
					'from'                  => $from,
					'to'                    => $to,
					'package'               => $package,
					'create_shipping_label' => $create_label
				);

				break;
		}

		$request = $this->send_request( 'shipments', $params, 'POST' );
		if ( is_wp_error( $request ) )
		{
			return $request;
		}

		if ( false !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			if ( $create_label )
			{
				return array(
					'id'                  => $request[ 'body' ][ 'id' ],
					'carrier_tracking_no' => $request[ 'body' ][ 'carrier_tracking_no' ],
					'tracking_url'        => $request[ 'body' ][ 'tracking_url' ],
					'label_url'           => $request[ 'body' ][ 'label_url' ],
					'price'               => $request[ 'body' ][ 'price' ]
				);
			}
			else
			{
				return array(
					'id'                  => $request[ 'body' ][ 'id' ],
					'carrier_tracking_no' => '',
					'tracking_url'        => $request[ 'body' ][ 'tracking_url' ],
					'label_url'           => '',
					'price'               => ''
				);
			}
		}
		else
		{
			$error = $this->get_error( $request );

			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
		}
	}

	/**
	 * Creating a shipping label
	 *
	 * @param string $shipment_id
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function create_label( $shipment_id )
	{
		$params = array(
			'create_shipping_label' => true
		);

		$action  = 'shipments/' . $shipment_id;
		$request = $this->send_request( $action, $params, 'PUT' );

		if ( false !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			return $request;

			return array(
				'id'           => $request[ 'body' ][ 'id' ],
				'tracking_url' => $request[ 'body' ][ 'tracking_url' ]
			);
		}
		else
		{
			$error = $this->get_error( $request );

			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
		}

		return $request_data;
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

		$request_status = (int) $request[ 'header' ][ 'status' ];

		if ( 204 === $request_status )
		{
			return true;
		}
		else
		{
			$error = $this->get_error( $request );

			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
		}
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
		else
		{
			$error = $this->get_error( $request );

			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], $error[ 'description' ] );
		}
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