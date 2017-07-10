<?php

namespace WooCommerce_Shipcloud\Tests\Integration_Tests\Shipcloud;

use Brain\Monkey;
use Brain\Monkey\Functions;

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
	public function test_api_key_can_be_injected() {
		$someKey = uniqid( 'apiKey', true );

		$subject = new \Woocommerce_Shipcloud_API( $someKey );

		$this->markTestIncomplete();
	}
}
