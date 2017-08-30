<?php

namespace WooCommerce_Shipcloud\Tests\Shipcloud;

use Shipcloud\Domain\Package;
use Shipcloud\Domain\Services\CashOnDelivery;
use Shipcloud\Domain\ValueObject\Address;
use Shipcloud\Domain\ValueObject\BankInformation;
use Shipcloud\Domain\ValueObject\Location;

abstract class ShipcloudTestCase extends \WP_UnitTestCase {
	public function createAddress() {
		return new Address(
			uniqid( 'company', true ),
			uniqid( 'first_name', true ),
			uniqid( 'last_name', true ),
			uniqid( 'care_of', true ),
			$this->createLocation()
		);
	}

	protected function createCashOnDelivery() {
		return new CashOnDelivery(
			random_int(1,35000) / 10,
			// TODO generate currency
			'EUR',
			$this->createBankInformation(),
			'As of ' . uniqid( 'reference', true )
		);
	}

	protected function createBankInformation() {
		return new BankInformation(
			uniqid( 'bank_name', true ),
			uniqid( 'bank_swift', true ),
			uniqid( 'bank_holder', true ),
			// TODO generate IBAN
			'DE93700500000009836862'
		);
	}

	public function createPackage() {
		return new Package(
			random_int(10,50) / 10,
			random_int(10,50) / 10,
			random_int(10,50) / 10,
			random_int(10,50) / 10,
			// TODO This only tests with parcel which is not good.
			'parcel'
		);
	}

	public function createLocation() {
		$validLocations = [
			[
				'country'  => 'DE',
				'city'     => 'Dresden',
				'zipCode'  => '01101',
				'street'   => 'Schulstraße',
				'streetNo' => mt_rand( 1, 100 ),
			]
		];

		$validLocation = $validLocations[0];

		return new Location(
			$validLocation['country'],
			$validLocation['zipCode'],
			$validLocation['city'],
			$validLocation['street'],
			$validLocation['streetNo']
		);
	}
}
