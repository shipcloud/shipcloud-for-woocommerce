<?php

namespace WooCommerce_Shipcloud\Tests\Shipcloud\Api\v1\Shipments\Services;

use Shipcloud\Domain\Services\CashOnDelivery;
use WooCommerce_Shipcloud\Tests\Shipcloud\ShipcloudTestCase;

class CashOnDeliveryTestCase extends ShipcloudTestCase {


	protected function createShipmentsRequestData() {
		return [
			'from'                  => $this->createAddress()->toArray(),
			'to'                    => $this->createAddress()->toArray(),
			'package'               => $this->createPackage(),
			// TODO Test multiple carrier instead of only one.
			'carrier'               => 'dhl',
			'create_shipping_label' => (bool) random_int( 0, 1 ),
		];
	}

	/**
	 *
	 * @group acceptance
	 * @group api
	 */
	public function testItCorrectlySendsDataToTheApi() {
		$data     = $this->createShipmentsRequestData();

		$data['additional_services'] = [
			[
				'name' => CashOnDelivery::NAME,
				'properties' => $this->createCashOnDelivery()->toArray(),
			]
		];

		$shipment = _wcsc_api()->shipment()->create( $data );

		static::assertNotNull($shipment->getId());
	}
}
