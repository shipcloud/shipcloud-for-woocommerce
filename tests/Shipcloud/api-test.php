<?php

namespace WooCommerce_Shipcloud\Tests\Shipcloud;

use Shipcloud\Api;

/**
 * API
 *
 * @author  awesome.ug <support@awesome.ug>
 * @package WooCommerceShipCloud/Tests
 * @since   1.3.0
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
class Api_Test extends \WP_UnitTestCase {
	public function assertApi() {
		$subject = new \Woocommerce_Shipcloud_API();

		$this->assertTrue( $subject->test() );
	}

	public function testApiIsAvailable() {
		$this->assertApi();
	}

	/**
	 * Headers
	 *
	 * Those fields should be send all the time:
	 *
	 * - Affiliate-Id (raw)
	 * - Authorization (as Base-Auth)
	 * - Content-Type (as "application/json")
	 *
	 * @dataProvider getRequestMethods
	 * @group        integration
	 */
	public function testSendsMandatoryHeaders( $method ) {
		$apiKey      = uniqid( 'apiKey', true );
		$affiliateId = uniqid( 'affiliateId', true );
		$api         = new Api( $apiKey, $affiliateId, 'https://httpbin.org' );

		$response = $api->request( 'anything', [], $method );

		// Assert mandatory headers are sent.
		static::assertArraySubset(
			[
				'headers' => [
					'Affiliate-Id'  => $affiliateId,
					'Authorization' => 'Basic ' . base64_encode( $apiKey . ':' ),
					'Content-Type'  => 'application/json',
				]
			],
			$response->getPayload()
		);
	}

	/**
	 * Request
	 *
	 * The SDK supports some HTTP Methods:
	 *
	 * - GET
	 * - POST
	 * - PUT
	 * - DELETE
	 *
	 * @dataProvider getRequestMethods
	 * @group        integration
	 */
	public function testItSupportsDifferentHttpMethods( $httpMethod ) {
		$api = new Api( uniqid( 'apiKey', true ), '', 'https://httpbin.org' );

		$payload = $api->request( 'anything', [], $httpMethod )->getPayload();

		static::assertEquals( $payload['method'], $httpMethod );
	}

	/**
	 * Headers
	 *
	 * When no Affiliate-Id is given or it is empty then it won't be transferred.
	 *
	 * @dataProvider getRequestMethods
	 * @group        integration
	 */
	public function testDoesNotSendEmptyAffiliateId( $method ) {
		$apiKey = uniqid( 'apiKey', true );
		$api    = new Api( $apiKey, '', 'https://httpbin.org' );

		$response = $api->request( strtolower( $method ), [], $method );

		// Assert mandatory headers are sent.
		$payload = $response->getPayload();

		static::assertArraySubset(
			[
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode( $apiKey . ':' ),
					'Content-Type'  => 'application/json',
				]
			],
			$payload
		);

		static::assertArrayNotHasKey( 'Affiliate-Id', $payload['headers'] );
	}

	/**
	 * Headers
	 *
	 * Those HTTP Methods remain unsupported:
	 *
	 * - HEAD
	 * - PATCH
	 * - TRACE
	 * - OPTIONS
	 * - CONNECT
	 *
	 * The SDK will refuse to work with them by throwing an exception.
	 *
	 * @dataProvider             getInvalidHttpMethods
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Invalid HTTP method
	 */
	public function testUnsupportedMethodsThrowException( $httpMethod ) {
		$api = new Api( uniqid( 'apiKey', true ), '', 'https://httpbin.org' );

		$api->request( 'anything', [], $httpMethod )->getPayload();
	}

	public function getRequestMethods() {
		return [
			[ 'DELETE' ],
			[ 'GET' ],
			[ 'POST' ],
			[ 'PUT' ],
		];
	}

	public function getInvalidHttpMethods() {
		return [
			[ 'HEAD' ],
			[ 'PATCH' ],
			[ 'TRACE' ],
			[ 'OPTIONS' ],
			[ 'CONNECT' ],
		];
	}
}
