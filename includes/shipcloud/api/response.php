<?php

namespace Shipcloud\Api;


use Symfony\Component\Config\Definition\ReferenceDumper;

class Response {
	/**
	 * @var array
	 */
	private $curlInfo;

	/**
	 * @var string
	 */
	private $payload;

	/**
	 * Response constructor.
	 *
	 * @param array $payload
	 * @param array $header
	 */
	public function __construct( array $payload, array $header ) {
		$this->payload = $payload;
		$this->header  = $header;
	}

	/**
	 * Create instance from body.
	 *
	 * @param string $response
	 * @param int    $headerSize
	 *
	 * @return static
	 */
	public static function createFromResponse( $response, $headerSize = 0 ) {
		$header = trim( substr( $response, 0, $headerSize ) );
		$body   = trim( substr( $response, $headerSize ) );

		return new static( (array) json_decode( $body, true ), static::parseHeader( $header ) );
	}

	/**
	 * Turn raw header into array.
	 *
	 * @param $rawHeader
	 *
	 * @return array
	 */
	private static function parseHeader( $rawHeader ) {
		$parseHeader                  = array();
		$headerLines                  = explode( "\r\n", $rawHeader );
		$statusLine                   = explode( ' ', array_shift( $headerLines ) );
		$parseHeader['http_protocol'] = $statusLine[0];
		$parseHeader['http_code']     = $statusLine[1];
		$parseHeader['http_status']   = $statusLine[2];

		foreach ( $headerLines as $i => $line ) {
			$pair                                  = explode( ': ', $line, 2 );
			$parseHeader[ strtolower( $pair[0] ) ] = trim( $pair[1] );
		}

		return $parseHeader;
	}

	/**
	 * Fetch header field.
	 *
	 * @param string $field
	 *
	 * @return string|null
	 */
	public function getHeader( $field ) {
		$field = strtolower( $field );

		if ( ! isset( $this->header[ $field ] ) || $this->header[ $field ] ) {
			return null;
		}

		return $this->header[ $field ];
	}

	/**
	 * Payload from the API.
	 *
	 * @return array
	 */
	public function getPayload() {
		return $this->payload;
	}

	public function getStatusCode() {
		return (int) $this->header['http_code'];
	}

	/**
	 * Marked as redirect.
	 *
	 * @return bool
	 */
	public function isRedirect() {
		return (
			301 === $this->getStatusCode()
			|| 302 === $this->getStatusCode()
			|| 303 === $this->getStatusCode()
			|| 307 === $this->getStatusCode()
		);
	}

	public function isSuccessful() {
		return ( 200 <= $this->getStatusCode() && 300 > $this->getStatusCode() );
	}
}
