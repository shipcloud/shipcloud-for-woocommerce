<?php

namespace WooCommerce_Shipcloud\Tests\Shipcloud\Domain;

use Shipcloud\Domain\Package;
use WooCommerce_Shipcloud\Tests\Shipcloud\ShipcloudTestCase;

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
class Package_Test extends ShipcloudTestCase {
	/**
	 * @var Package
	 */
	protected $package;

	/**
	 * @var Package\DeclaredValue
	 */
	protected $packageDeclaredValue;

	/**
	 * @var string
	 */
	protected $packageDescription;

	/**
	 * @var float
	 */
	protected $packageHeight;

	/**
	 * @var float
	 */
	protected $packageLength;

	/**
	 * @var string
	 */
	protected $packageType;

	/**
	 * @var float
	 */
	protected $packageWeight;

	/**
	 * @var float
	 */
	protected $packageWidth;

	public function setUp() {
		$this->package = new Package(
			$this->packageLength = mt_rand( 0, 999 ),
			$this->packageWidth = mt_rand( 0, 999 ),
			$this->packageHeight = mt_rand( 0, 999 ),
			$this->packageWeight = mt_rand( 0, 999 )
		);

		$this->packageDescription   = $this->package->description = uniqid( 'description_', true );
		$this->packageType          = $this->package->type = uniqid( 'type_', true );
		$this->packageDeclaredValue = $this->package->declared_value = new Package\DeclaredValue( 1.0, 'EUR' );
	}

	public function testDeclaredValueMustBeOfTypeDeclaredValue() {
		static::assertTrue( $this->package->isValid() );

		$this->package->declared_value = array( 1.0, 'EUR' );
		static::assertFalse( $this->package->isValid() );

		$this->package->declared_value = $this->packageDeclaredValue;
	}

	public function testHasDeclaredValue() {
		static::assertSame( $this->packageDeclaredValue, $this->package->declared_value );
	}

	public function testHasDescription() {
		static::assertEquals( $this->packageDescription, $this->package->description );
	}

	public function testHasHeight() {
		static::assertEquals( $this->packageHeight, $this->package->height );
	}

	public function testHasLength() {
		static::assertEquals( $this->packageLength, $this->package->length );
	}

	public function testHasType() {
		static::assertEquals( $this->packageType, $this->package->type );
	}

	public function testHasWeight() {
		static::assertEquals( $this->packageWeight, $this->package->weight );
	}

	public function testHasWidth() {
		static::assertEquals( $this->packageWidth, $this->package->width );
	}

	public function testIsValid() {
		static::assertTrue( $this->package->isValid() );
	}

	public function testNonNumericValuesMakeItInvalid() {
		static::assertTrue( $this->package->isValid() );

		$this->package->length = 'a';
		static::assertFalse( $this->package->isValid() );
		$this->package->length = $this->packageLength;

		$this->package->width = 'a';
		static::assertFalse( $this->package->isValid() );
		$this->package->width = $this->packageWidth;

		$this->package->height = 'a';
		static::assertFalse( $this->package->isValid() );
		$this->package->height = $this->packageHeight;

		$this->package->weight = 'a';
		static::assertFalse( $this->package->isValid() );
		$this->package->weight = $this->packageWeight;
	}
}
