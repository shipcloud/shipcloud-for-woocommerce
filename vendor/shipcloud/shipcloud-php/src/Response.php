<?php

namespace shipcloud\phpclient;

/**
 * Response class that encapsulates the response of an API request.
 *
 * @category 	Class
 * @package  	shipcloud\phpclient
 * @author   	Daniel Muenter <info@msltns.com>
 * @version  	0.0.1
 * @since   	0.0.1
 * @license 	GPL 3
 *          	This program is free software; you can redistribute it and/or modify
 *          	it under the terms of the GNU General Public License, version 3, as
 *          	published by the Free Software Foundation.
 *          	This program is distributed in the hope that it will be useful,
 *          	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          	GNU General Public License for more details.
 *          	You should have received a copy of the GNU General Public License
 *          	along with this program; if not, write to the Free Software
 *          	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class Response {
	
	/**
	 * @var array
	 */
	private $header;

	/**
	 * @var string
	 */
	private $payload;

	/**
	 * Response constructor.
	 *
	 * @param array $payload
	 * @param array $header
	 * @return void
	 */
	public function __construct( array $payload, array $header ) {
		$this->payload = $payload;
		$this->header  = $header;
	}

	/**
	 * Creates instance from API response body.
	 *
	 * @param string $response
	 * @param int    $header_size
	 * @return static
	 */
	public static function create_from_api_response( string $response, int $header_size = 0 ) {
		if ( $response === false ) {
			return false;
		}
		
		$header = trim( substr( $response, 0, $header_size ) );
		$body   = trim( substr( $response, $header_size ) );
		
		return new static( (array) json_decode( $body, true ), static::parse_header( $header ) );
	}

	/**
	 * Parses a status line.
	 *
	 * @param string $status_line
	 * @return array
	 */
	protected static function parse_status_line( string $status_line ) {
		$chunks = explode( ' ', $status_line, 3 );

		return array(
			'http_protocol' => $chunks[0],
			'http_code'     => !empty( $chunks[1] ) ? $chunks[1] : '',
			'http_status'   => !empty( $chunks[2] ) ? $chunks[2] : '',
		);
	}

	/**
	 * Parses raw header into an array.
	 *
	 * @param $raw_header
	 * @return array
	 */
	private static function parse_header( string $raw_header ) {
		$header_lines = explode( "\r\n", $raw_header );
		$parse_header = static::parse_status_line( array_shift( $header_lines ) );

		foreach ( $header_lines as $i => $line ) {
			if ( ! $line ) {
				// Empty lines occur when multiple HTTP status codes are set.
				continue;
			}

			$status = array();
			if ( preg_match( '@(\w*\/[\d\.]*) (\d*) (\w*)@', $line, $status ) ) {
				// Another response starts so we reset.
				$parse_header = static::parse_status_line( $line );
				continue;
			}

			$pair = explode( ': ', $line, 2 );
			$parse_header[ strtolower( $pair[0] ) ] = trim( $pair[1] );
		}

		return $parse_header;
	}

	/**
	 * Fetches a header field.
	 *
	 * @param string $field
	 *
	 * @return string|null
	 */
	public function get_header( string $field ) {
		$field = strtolower( $field );

		if ( ! isset( $this->header[ $field ] ) || $this->header[ $field ] ) {
			return null;
		}

		return $this->header[ $field ];
	}

	/**
	 * Returns payload from the API response.
	 *
	 * @return array
	 */
	public function get_payload() {
		return $this->payload;
	}

	/**
	 * Returns status code from the API response.
	 *
	 * @return int
	 */
	public function get_status_code() {
		return (int) $this->header['http_code'];
	}

	/**
	 * Checks wether request was successful or not.
	 *
	 * @return bool
	 */
	public function is_successful() {
		return (
			200 <= $this->get_status_code()
			&& 300 > $this->get_status_code()
			&& ! isset( $payload['errors'] )
		);
	}
}
