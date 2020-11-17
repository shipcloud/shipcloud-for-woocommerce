<?php

namespace Shipcloud;

use Shipcloud\Api\Carriers;
use Shipcloud\Api\Response;
use Shipcloud\Api\Shipment;
use Shipcloud\Api\Webhook;

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
	 * Maximum number of redirects.
	 *
	 * @var int
	 */
	protected $curlMaxRedirects = 5;

	/**
	 * Amount of requests that has been fired.
	 *
	 * @var int
	 */
	protected $request_count;

	/**
	 * Access to shipment API.
	 *
	 * @var Shipment
	 */
	protected $shipment;

	/**
	 * URL to the API.
	 *
	 * @var string
	 */
	private $url;

    /**
    * Access to shipment API.
    *
    * @var Webhook
    */
    protected $webhook;


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

	private function getStatusCode() {
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
	 * @throws \RuntimeException
	 * @throws \UnexpectedValueException When the API responded with something else than 2xx OK.
	 */
	public function request( $action, $params = array(), $type = 'GET' ) {
		$this->request_count ++;

		return $this->curlExec( $action, $params, $type );
	}

	/**
	 * Execute cURL handler.
	 *
	 * @since 1.4.1 This simulates following redirects as open_basedir setting of PHP can cause problems with the
	 *              "CURLOPT_FOLLOWLOCATION" setting.
	 *
	 * @param string $action The URL to access.
	 * @param array  $params
	 * @param string $type   HTTP-Request type.
	 *
	 * @return Response
	 *
	 * @throws \RuntimeException When API response was no parseable JSON.
	 * @throws \UnexpectedValueException When the API ended in something else than 2xx OK.
	 */
	protected function curlExec( $action, $params, $type ) {
		$url       = $this->url . '/' . $action;
		$redirects = 0;

		do {
			$curlHandler = $this->curlInit( $url, $params, $type );
			$response    = Response::createFromResponse(
				curl_exec( $curlHandler ),
				curl_getinfo( $curlHandler, CURLINFO_HEADER_SIZE )
			);

			if ( ! $response->isRedirect() ) {
				// No longer a redirect so we stop here.
				break;
			}

			// A redirect we will parse and follow (see http://php.net/manual/de/function.curl-setopt.php#113682)
			$redirects ++;

			$url = $response->getHeader( 'location' );
			curl_close( $curlHandler );
		} while ( $url && $redirects < $this->curlMaxRedirects );

		if ( $response->isRedirect() ) {
			// Still a redirect, enough is enough.
			throw new \UnexpectedValueException( 'Too many redirects.' );
		}

		if ( ! $response->getPayload() ) {
			throw new \RuntimeException( 'Could not parse or empty API response.' );
		}

		$payload = $response->getPayload();
		if ( ! $response->isSuccessful() ) {
			// Something was not right, so we throw an exception.
			$currentException = null;
			foreach ( (array) $payload['errors'] as $error ) {
				$currentException = new \UnexpectedValueException(
					$error,
					$response->getStatusCode(),
					$currentException
				);
			}

			throw $currentException;
		}

		if ( ! $response->isSuccessful() ) {
			// Not successfull and no errors provided by API so the throw generic message.
			throw new \UnexpectedValueException(
				sprintf(
					'Request was for "%s" was not successful (%d).',
					$action,
					$response->getStatusCode()
				),
				$payload['name']
			);
		}

		return $response;
	}

	/**
	 * Open cURL connection.
	 *
	 * @since 1.4.1 The cURL option CURLOPT_FOLLOWLOCATION won't be simulated
	 *              due to conflicts with the open_basedir config of PHP.
	 *
	 * @param string $url    The URL to access.
	 * @param array  $params
	 * @param string $method HTTP-Request type.
	 *
	 * @return resource
	 * @throws \InvalidArgumentException In case of invalid HTTP method.
	 */
	protected function curlInit( $url, $params = array(), $method = 'GET' ) {
		$headers = array(
			'Content-Type: application/json',
		);

		if ( $this->affiliateId ) {
			$headers[] = 'Affiliate-ID: ' . $this->affiliateId;
		}

		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_HEADER, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, intval(wcsc_api()->get_api_timeout()) );
		curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt( $ch, CURLOPT_USERPWD, $this->apiKey );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

		$method = strtoupper( $method );
		if ( ! in_array( $method, array( 'DELETE', 'GET', 'POST', 'PUT' ), true ) ) {
			// Unsupported HTTP Request.
			throw new \InvalidArgumentException( 'Invalid HTTP method: ' . $method );
		}

		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );

		if ( 'POST' === $method || 'PUT' === $method ) {
			// Write operations need payload.
			$jsonParams = json_encode( $params );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonParams );

			$headers[] = 'Content-Length: ' . strlen( $jsonParams );
		}

		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		return $ch;
	}

	/**
	 * Handle shipment.
	 *
	 * @return Shipment
	 */
	public function shipment() {
		if ( $this->shipment ) {
			return $this->shipment;
		}

		return $this->shipment = new Shipment( $this );
	}

    /**
    * Handle webhooks.
    *
    * @return Webhook
    */
    public function webhook() {
        if ( $this->webhook ) {
            return $this->webhook;
        }

        return $this->webhook = new Webhook( $this );
    }
}
