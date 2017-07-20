<?php

namespace Shipcloud;

use Shipcloud\Api\Carriers;
use Shipcloud\Api\Response;

/**
 * shipcloud for WooCommerce API
 *
 * @author  awesome.ug <support@awesome.ug>
 * @package shipcloudForWooCommerce/API
 * @version 1.0.0
 * @since   1.4.0
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
class Api {
	/**
	 * @var string
	 */
	private $affiliateId;

	/**
	 * Key of the customer to access the API.
	 *
	 * @var string
	 */
	private $apiKey;

	/**
	 * @var Carriers
	 */
	protected $carriers;

	/**
	 * Amount of requests that has been fired.
	 *
	 * @var int
	 */
	protected $request_count;

	/**
	 * URL to the API.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Api constructor.
	 *
	 * @param string $apiKey Key to access the API.
	 * @param string $affiliateId
	 * @param string $url    URL to the API.
	 */
	public function __construct( $apiKey, $affiliateId = '', $url = 'https://api.shipcloud.io/v1' ) {
		$this->apiKey        = $apiKey;
		$this->url           = $url;
		$this->request_count = 0;
		$this->affiliateId   = $affiliateId;
	}

	/**
	 * Access the carriers.
	 *
	 * @return Carriers
	 */
	public function carriers() {
		if ( $this->carriers ) {
			return $this->carriers;
		}

		return $this->carriers = new Carriers( $this );
	}

	/**
	 * @return int
	 */
	public function getRequestCount() {
		return $this->request_count;
	}

	/**
	 * Fetch data from API.
	 *
	 * @param        $action
	 * @param        $params
	 * @param string $type
	 *
	 * @return Response
	 *
	 * @throws \InvalidArgumentException For invalid HTTP-Request type.
	 * @throws \RuntimeException When API response was no parseable JSON.
	 * @throws \UnexpectedValueException When the API responded with something else than 2xx OK.
	 */
	public function request( $action, $params = array(), $type = 'GET' ) {
		$this->request_count ++;

		$ch       = $this->curlInit( $action, $params, $type );
		$response = array();
		$body     = curl_exec( $ch );

		if ( $body && '{}' !== trim( $body ) ) {
			// We received some data, so we parse it.
			$response = json_decode( $body, true );

			if ( ! $response ) {
				$info = curl_getinfo( $ch );
				throw new \RuntimeException( 'Could not parse API response.' );
			}
		}

		$curlInfo = curl_getinfo( $ch );
		if ( 200 > $curlInfo['http_code'] || 300 <= $curlInfo['http_code'] ) {
			// Something was not right, so we throw an exception.
			throw new \UnexpectedValueException(
				sprintf(
					'Request was for "%s" was not successful (%d).',
					$action,
					$curlInfo['http_code']
				),
				$response['name']
			);
		}

		return new Response( $response, $curlInfo );
	}

	/**
	 * Open cURL connection.
	 *
	 * @param string $action The URL to access.
	 * @param array  $params
	 * @param string $type   HTTP-Request type.
	 *
	 * @return resource
	 */
	protected function curlInit( $action, $params = array(), $type = 'GET' ) {
		$headers = array(
			//'Authorization' => 'Basic ' . base64_encode( $this->apiKey ),
			'Content-Type'  => 'application/json',
		);

		if ( $this->affiliateId ) {
			$headers['Affiliate-ID'] = $this->affiliateId;
		}

		$ch = curl_init( $this->url . '/' . $action );
		curl_setopt( $ch, CURLOPT_HEADER, false );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt( $ch, CURLOPT_USERPWD, $this->apiKey );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

		$type = strtoupper( $type );
		if ( ! in_array( $type, array( 'DELETE', 'GET', 'POST', 'PUT' ), true ) ) {
			// Unsupported HTTP Request.
			throw new \InvalidArgumentException( 'Invalid HTTP request: ' . $type );
		}

		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $type );

		if ( 'POST' === $type || 'PUT' === $type ) {
			// Write operations need payload.
			$jsonParams = json_encode( $params );
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen( $jsonParams )
				)
			);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonParams );
		}

		return $ch;
	}
}
