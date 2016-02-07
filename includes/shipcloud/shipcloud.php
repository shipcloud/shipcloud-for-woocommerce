<?php

/**
 * Class Woocommerce_Shipcloud_API
 *
 * API Base class for Shipclod.io
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/API
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2016 (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class Woocommerce_Shipcloud_API
{
	private $api_key;
	private $api_url;

	/**
	 * Woocommerce_Shipcloud_API constructor.
	 *
	 * @param string $apiKey
	 * @since 1.0.0
	 */
	public function __construct( $apiKey )
	{
		$this->api_key = $apiKey;
		$this->api_url = 'https://api.shipcloud.io/v1';
	}

	/**
	 * Getting API endpoint
	 *
	 * @param string $action
	 * @return string $api_url
	 * @since 1.0.0
	 */
	private function get_endpoint( $action )
	{
		return $this->api_url . '/' . $action;
	}

	/**
	 * Updating carriers and saving in WP options
	 *
	 * @return array|WP_Error
	 */
	private function update_carriers()
	{
		$shipment_carriers = $this->request_carriers();

		if( is_wp_error( $shipment_carriers ) || FALSE === $shipment_carriers )
		{
			return $shipment_carriers;
		}

		update_option( 'woocommerce_shipcloud_carriers', $shipment_carriers );

		return $shipment_carriers;
	}

	/**
	 * Getting carriers
	 *
	 * @param bool $force_update
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function get_carriers( $force_update = FALSE )
	{
		$shipment_carriers = get_option( 'woocommerce_shipcloud_carriers', FALSE );

		if( empty( $shipment_carriers ) || TRUE === $force_update )
		{
			$shipment_carriers = $this->update_carriers();
			WooCommerce_Shipcloud::admin_notice( __( 'Updated Carriers!', 'woocommerce-shipcloud' ) );
		}

		$carriers = array();
		foreach( $shipment_carriers AS $shipment_carrier )
		{
			if( isset( $shipment_carrier[ 'services' ] ) ){
				foreach( $shipment_carrier[ 'services' ] AS $service )
				{
					$carriers[] = array(
						'name' => $shipment_carrier[ 'name' ] . '_' . $service,
						'display_name' => $shipment_carrier[ 'display_name' ] . ' - ' . $this->translate_service_name( $service )
					);
				}
			}else{
				$carriers[] = array(
					'name'	=> $shipment_carrier[ 'name' ],
					'display_name'	=> $shipment_carrier[ 'display_name' ]
				);
			}
		}

		return $carriers;
	}

	/**
	 * Requesting carriers
	 *
	 * @return array|WP_Error
	 *
	 * @since 1.0.0
	 */
	private function request_carriers()
	{
		$action = 'carriers';
		$request = $this->send_request( $action );

		if( is_wp_error( $request ) )
		{
			return $request;
		}

		if( FALSE !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			return $request[ 'body' ];
		}
		else
		{
			$error = $this->get_error( $request );
			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], __( 'API error:', 'woocommerce-shipcloud' ) . ' ' . $error[ 'description' ] );
		}
	}

	/**
	 * Splitting carrier name into API usable carrier name and service name
	 *
	 * @param $carrier_name
	 *
	 * @return array
	 */
	private function disassemble_carrier_name( $carrier_name )
	{
		$carrier_arr = explode( '_', $carrier_name );

		$carrier = $carrier_arr[ 0 ];
		$service = '';

		for( $i = 1; $i < count( $carrier_arr ); $i++ ){
			$service .= $i == 1 ? '' : '_';
			$service .= $carrier_arr[ $i ];
		}

		return array(
			'carrier' => $carrier,
			'service' => $service
		);
	}

	/**
	 * Getting display name for a carrier including service
	 *
	 * @param $carrier_name
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function get_carrier_display_name( $carrier_name )
	{
		$carriers = $this->get_carriers();

		foreach( $carriers AS $carrier )
		{
			if( $carrier[ 'name' ] == $carrier_name )
			{
				return $carrier[ 'display_name' ];
			}
		}

		return FALSE;
	}

	/**
	 * Getting display name for a carrier (short name) including service
	 *
	 * @param $carrier_name
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function get_carrier_display_name_short( $carrier_name )
	{
		$carrier_name_arr = $this->disassemble_carrier_name( $carrier_name );
		$display_name = strtoupper( $carrier_name_arr[ 'carrier' ] ) . ' - ' . $this->translate_service_name( $carrier_name_arr[ 'service' ] );

		return $display_name;
	}

	/**
	 * Translating service to a readable name
	 *
	 * @param $service_name
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	private function translate_service_name( $service_name )
	{
		$services = array(
			'standard' => __( 'Standard', 'woocommerce-shipcloud' ),
			'returns' => __( 'Returns', 'woocommerce-shipcloud' ),
			'one_day' => __( 'One Day', 'woocommerce-shipcloud' ),
			'one_day_early' => __( 'One Day Early', 'woocommerce-shipcloud' ),
			'same_day' => __( 'Same Day', 'woocommerce-shipcloud' ),
		);

		if( ! array_key_exists( $service_name, $services ) )
		{
			return FALSE;
		}

		return $services[ $service_name ];
	}

	/**
	 * Getting error string of a request
	 *
	 * @param $request
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	private function get_error( $request )
	{
		$error = $this->translate_error_code( $request[ 'header' ][ 'status' ] );

		if( isset( $request[ 'body' ] ) )
		{
			$error[ 'description' ] .= ' - ' . $this->get_body_errors( $request[ 'body' ] );
		}

		return $error;
	}

	/**
	 * Translating error codes to a readable string
	 *
	 * @param $error_code
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	private function translate_error_code( $error_code )
	{
		$error_codes = array(
			'204'   => array(
				'name' => 'no content',
				'description' => __( 'There is no message body. You\'ll get this code when deleting a shipment was successful.', 'woocommerce-shipcloud' )
			),
			'400'   => array(
				'name' => 'bad_request',
				'description' => __( 'Your request was not correct. Please see the response body for more detailed information.', 'woocommerce-shipcloud' )
			),
			'401'   => array(
				'name' => 'access_denies',
				'description' => __( 'Access denied! Please check your API Key.', 'woocommerce-shipcloud' )
			),
			'402'   => array(
				'name' => 'payment_required',
				'description' => __( 'You\'ve reached the maximum of your current plan. Please upgrade to a higher plan.', 'woocommerce-shipcloud' )
			),
			'403'   => array(
				'name' => 'forbidden',
				'description' => __( 'You are not allowed to talk to this endpoint. This can either be due to a wrong authentication or when you\'re trying to reach an endpoint that your account isn\'t allowed to access.', 'woocommerce-shipcloud' )
			),
			'404'   => array(
				'name' => 'not_found',
				'description' => __( 'The api endpoint you were trying to reach can\'t be found.', 'woocommerce-shipcloud' )
			),
			'422'   => array(
				'name' => 'unprocessable_entity',
				'description' => __( 'Your request was well-formed but couldn\'t be followed due to semantic errors. Please see the response body for more detailed information.', 'woocommerce-shipcloud' )
			),
			'500'   => array(
				'name' => 'internal_server_error',
				'description' => __( 'Something has seriously gone wrong. Don\'t worry, we\'ll have a look at it.', 'woocommerce-shipcloud' )
			),
			'502'   => array(
				'name' => 'bad_gateway',
				'description' => __( 'Something has gone wrong while talking to the carrier backend. Please see the response body for more detailed information.', 'woocommerce-shipcloud' )
			),
			'504'   => array(
				'name' => 'gateway_timeout',
				'description' => __( 'Unfortunately we couldn\'t connect to the carrier backend. It is either very slow or not reachable at all. If you want to stay informed about the carrier status, follow our developer twitter account at @shipcloud_devs.', 'woocommerce-shipcloud' )
			)
		);

		if( ! array_key_exists( $error_code, $error_codes ) )
		{
			return array( 'name' => 'unknown', 'description' => __( 'Unknown Error', 'woocommerce-shipcloud' ) );
		}

		return $error_codes[ $error_code ];
	}

	/**
	 * Getting errors of a shipcloud body response and creating a string
	 *
	 * @param $body
	 *
	 * @return bool|string
	 *
	 * @since 1.0.0
	 */
	private function get_body_errors( $body ){
		if( isset( $body[ 'errors' ] ) )
		{
			$error_str = '';

			foreach( $body[ 'errors' ] as $error )
			{
				$error_str .= $error;
			}
			return $error_str;
		}
		return FALSE;
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
	 *
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

		if( FALSE !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			return $request[ 'body' ][ 'shipment_quote' ][ 'price' ];
		}
		else
		{
			$error = $this->get_error( $request );
			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], __( 'API error:', 'woocommerce-shipcloud' ) . ' ' . $error[ 'description' ] );
		}
	}

	/**
	 * Creating a shipment
	 *
	 * @param string $carrier
	 * @param array $from
	 * @param array $to
	 * @param array $package
	 * @param bool $create_label
	 *
	 * @return string|WP_Error
	 *
	 * @since 1.0.0
	 */
	public function create_shipment( $carrier, $from, $to, $package, $create_label = FALSE )
	{
		$carrier = $this->disassemble_carrier_name( $carrier );

		switch( $carrier[ 'carrier' ] ){

			case 'dpd':
				$to_email = $to[ 'email' ];
				unset( $to[ 'email' ] );

				$params = array(
					'carrier' => $carrier[ 'carrier' ],
					'service' => $carrier[ 'service' ],
					'from'    => $from,
					'to'      => $to,
					'package' => $package,
					'create_shipping_label' => $create_label,
					'additional_services'   => array(
						array(
							'name'  => 'advance_notice',
							'properties' => array(
								'email'  =>  $to_email,
								'language' => strtolower( $to[ 'country' ] )
							)
						)
					)
				);

				break;

			default:
				unset( $to[ 'email' ] );
				$params = array(
					'carrier' => $carrier[ 'carrier' ],
					'service' => $carrier[ 'service' ],
					'from'    => $from,
					'to'      => $to,
					'package' => $package,
					'create_shipping_label' => $create_label
				);

				break;
		}

		$request = $this->send_request( 'shipments', $params, 'POST' );

		if( FALSE !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			if( $create_label )
			{
				return array(
					'id'                    => $request[ 'body' ][ 'id' ],
					'carrier_tracking_no'   => $request[ 'body' ][ 'carrier_tracking_no' ],
					'tracking_url'          => $request[ 'body' ][ 'tracking_url' ],
					'label_url'             => $request[ 'body' ][ 'label_url' ],
					'price'                 => $request[ 'body' ][ 'price' ]
				);
			}
			else
			{
				return array(
					'id'                    => $request[ 'body' ][ 'id' ],
					'carrier_tracking_no'   => '',
					'tracking_url'          => $request[ 'body' ][ 'tracking_url' ],
					'label_url'             => '',
					'price'                 => ''
				);
			}
		}
		else
		{
			$error = $this->get_error( $request );
			p( $error );
			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], __( 'API error:', 'woocommerce-shipcloud' ) . ' ' . $error[ 'description' ] );
		}
	}

	/**
	 * Creating a shipping label
	 *
	 * @param string $shipment_id
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function create_label( $shipment_id )
	{
		$params = array(
			'create_shipping_label' => TRUE
		);

		$action = 'shipments/' . $shipment_id;
		$request = $this->send_request( $action, $params, 'PUT' );

		if( FALSE !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			return $request;

			return array(
				'id' => $request[ 'body' ][ 'id' ],
				'tracking_url' => $request[ 'body' ][ 'tracking_url' ]
			);
		}
		else
		{
			$error = $this->get_error( $request );
			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], __( 'API error:', 'woocommerce-shipcloud' ) . ' ' . $error[ 'description' ] );
		}

		return $request_data;
	}

	/**
	 * Deleting a shipment
	 *
	 * @param string $shipment_id
	 *
	 * @return array $request_data
	 *
	 * @since 1.0.0
	 */
	public function delete_shipment( $shipment_id )
	{
		$params = array();

		$action = 'shipments/' . $shipment_id;
		$request_data = $this->send_request( $action, $params, 'DELETE' );

		return $request_data;
	}

	/**
	 * Getting the tracking status of a shipment
	 *
	 * @param $shipment_id
	 *
	 * @return array $request_data
	 *
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
	 * @param $params
	 *
	 * @return WP_Error
	 */
	public function request_pickup( $params )
	{
		// Todo: Finish it!
		$action = 'pickup_requests';
		$request = $this->send_request( $action, $params, 'POST' );

		if( FALSE !== $request && 200 === (int) $request[ 'header' ][ 'status' ] )
		{
			return $request[ 'body' ];
		}
		else
		{
			$error = $this->get_error( $request );
			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], __( 'API error:', 'woocommerce-shipcloud' ) . ' ' . $error[ 'description' ] );
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
	 */
	private function send_request( $action = '', $params = array(), $method = 'GET' )
	{
		$count_requests = get_option( 'woocommerce_shipcloud_count_requests', 0 ) + 1;
		update_option( 'woocommerce_shipcloud_count_requests', $count_requests );

		$url = $this->get_endpoint( $action );
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $this->api_key ),
			'Content-Type'  => 'application/json',
			'Affiliate-ID'  => 'plugin.woocommerce.z4NVoYhp'
		);

		$params = json_encode( $params );

		switch ( $method )
		{
			case "GET":

				$args = array(
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

				$args = array(
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

		if( is_wp_error( $response ) )
		{
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );

		// Decode if it's json
		if( wp_remote_retrieve_header( $response, 'content-type' ) == 'application/json; charset=utf-8' )
		{
			$body = json_decode( $body, TRUE );
		}

		$response_arr = array(
			'header' => array(
				'status'  => wp_remote_retrieve_response_code( $response )
			),
			'body'   => $body
		);

		return $response_arr;
	}

	/**
	 * Connection testing
	 *
	 * @return bool|WP_Error
	 *
	 * @since 1.0.0
	 */
	public function test(){
		$action = 'carriers';
		$request = $this->send_request( $action );

		if( is_wp_error( $request ) ){
			return $request;
		}

		if( 200 !== (int) $request[ 'header' ][ 'status' ] )
		{
			$error = $this->get_error( $request );
			return new WP_Error( 'shipcloud_api_error_' . $error[ 'name' ], __( 'API error:', 'woocommerce-shipcloud' ) . ' ' . $error[ 'description' ] );
		}

		return TRUE;
	}
}