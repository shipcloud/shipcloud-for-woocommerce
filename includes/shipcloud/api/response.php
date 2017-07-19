<?php

namespace Shipcloud\Api;


class Response {
	/**
	 * @var array
	 */
	private $curlInfo;

	/**
	 * @var array
	 */
	private $payload;

	/**
	 * Response constructor.
	 *
	 * @param array $payload
	 * @param array $curlInfo
	 */
	public function __construct( array $payload, array $curlInfo = array() ) {
		$this->payload  = $payload;
		$this->curlInfo = $curlInfo;
	}

	/**
	 * Payload from the API.
	 *
	 * @return array
	 */
	public function getPayload() {
		return $this->payload;
	}

	public function isSuccessful() {
		return isset( $this->curlInfo['http_code'] )
			   && 200 <= $this->curlInfo['http_code']
			   && 200 >= $this->curlInfo['http_code'];
	}
}
