<?php

namespace WooCommerce_Shipcloud\Tests\Shipcloud;

/**
 * Functions test
 *
 * @author  awesome.ug <support@awesome.ug>
 * @package WooCommerceShipCloud/Tests
 * @since   1.3.2
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
class Functions_Test extends ShipcloudTestCase {
	public function getStreetAndNumber() {
		return [
			['This, That, and the other Street 123', 'This, That, and the other Street', '123'],
			['Sesamstraße', 'Sesamstraße', null],
			['   Einbahnstraße  ', 'Einbahnstraße', null],
			['   Ei nb ahns traß e  ', 'Ei nb ahns traß e', null],
			['Züm Humpfen Stobel 12a', 'Züm Humpfen Stobel', '12a'],
			['Heidenackerweg 15', 'Heidenackerweg', '15'],
			['Straße des 17. Juni 152', 'Straße des 17. Juni', '152'],
			['Gutbürger Weg 4', 'Gutbürger Weg', '4'],
			['Straße des 17. Juni 152-153', 'Straße des 17. Juni', '152-153'],
			['Straße des 17. Juni 152a - 153b', 'Straße des 17. Juni', '152a - 153b'],
		];
	}
	/**
	 * @dataProvider getStreetAndNumber
	 */
	public function testSplittingStreetAndNumber($streetAndNumber, $street, $number) {
		$splitted = wcsc_explode_street($streetAndNumber);

		static::assertEquals($street, $splitted['address']);
		static::assertEquals($number, $splitted['number']);
	}
}
