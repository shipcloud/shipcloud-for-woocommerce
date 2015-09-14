<?php

/**
 * Class Woocommerce_Shipcloud_API
 *
 * API Base class for Shipclod.io
 *
 * @author  awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/API
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 (very@awesome.ug)
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

	public function __construct( $apiKey )
	{
		$this->api_key = $apiKey;
		$this->api_url = 'https://api.shipcloud.io/v1';
	}

	/**
	 * set the cURL url: combining url-endpoint + action
	 *
	 * @param $action
	 *
	 * @return string
	 */
	private function get_endpoint( $action )
	{
		$url = $this->api_url . '/' . $action;

		return $url;
	}

	public function get_price( $params )
	{
		$action = 'shipment_quotes';

		$request = $this->send_request( $action, $params, 'POST' );

		if( FALSE != $request && 200 == $request[ 'header' ][ 'status' ] ):
			return $request[ 'body' ][ 'shipment_quote' ][ 'price' ];
		else:
			return FALSE;
		endif;
	}

	public function update_carriers()
	{
		$shipment_carriers = $this->request_carriers();
		update_option( 'woocommerce_shipcloud_carriers', $shipment_carriers );

		return $shipment_carriers;
	}

	public function get_carriers( $force_update = FALSE )
	{
		$shipment_carriers = get_option( 'woocommerce_shipcloud_carriers' );

		if( '' == $shipment_carriers || $force_update )
		{
			$shipment_carriers = $this->update_carriers();
		}

		return $shipment_carriers;
	}

	public function request_carriers()
	{
		$action = 'carriers';
		$request = $this->send_request( $action );

		if( FALSE != $request && 200 == (int) $request[ 'header' ][ 'status' ] ):
			return $request[ 'body' ];
		else:
			return FALSE;
		endif;
	}

	public function get_tracking_status( $shipment_id )
	{
		$request_data = $this->send_request( 'shipments/' . $shipment_id );

		return $request_data;
	}

	public function create_label( $shipment_id )
	{
		$params = array(
			'create_shipping_label' => TRUE
		);

		$action = 'shipments/' . $shipment_id;
		$request_data = $this->send_request( $action, $params, 'PUT' );

		return $request_data;
	}

	public function request_pickup( $params )
	{
		$action = 'pickup_requests';
		$request = $this->send_request( $action, $params, 'POST' );

		if( FALSE != $request && 200 == $request[ 'header' ][ 'status' ] ):
			return $request[ 'body' ];
		else:
			return FALSE;
		endif;
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
	public function send_request( $action = '', $params = array(), $method = 'GET' )
	{
		$count_requests = get_option( 'woocommerce_shipcloud_count_requests', 0 ) + 1;
		update_option( 'woocommerce_shipcloud_count_requests', $count_requests );

		$url = $this->get_endpoint( $action );
		$headers = array( 'Authorization' => 'Basic ' . base64_encode( $this->api_key ) );

		switch ( $method )
		{
			case "GET":

				$args = array(
					'headers' => $headers
				);
				$response = wp_remote_get( $url, $args );

				break;

			case "POST":

				$args = array(
					'headers' => $headers,
					'body'    => $params
				);

				$response = wp_remote_post( $url, $args );

				break;

			case "PUT":

				$args = array(
					'headers' => $headers,
					'method'  => 'PUT',
					'body'    => $params
				);
				$response = wp_remote_request( $url, $args );

				break;
		}

		// @todo: Better Error Handling
		if( is_wp_error( $response ) )
		{
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";

			return FALSE;
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
}