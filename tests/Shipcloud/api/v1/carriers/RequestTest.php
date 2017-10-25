<?php

class RequestTest extends \WooCommerce_Shipcloud\Tests\Shipcloud\ShipcloudTestCase {
	public function testItFetchesCarriers() {
		$api = new Woocommerce_Shipcloud_API();

		$carriers = $api->request_carriers();

		foreach ( $carriers as $carrier ) {
			static::assertInstanceOf( '\\ArrayAccess', $carrier );
			static::assertArrayHasKey( 'name', $carrier );
			static::assertArrayHasKey( 'display_name', $carrier );
			static::assertArrayHasKey( 'services', $carrier );
			static::assertArrayHasKey( 'package_types', $carrier );
		}
	}

	public function testItTransfersAffiliateId() {
		$api = _wcsc_api();
	}
}
