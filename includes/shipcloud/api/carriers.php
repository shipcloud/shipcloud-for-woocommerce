<?php
/**
 * Contains class to access the API for carrier information.
 */

namespace Shipcloud\Api;

use Shipcloud\Api;
use Shipcloud\Domain\Carrier;

/**
 * Access the API for carrier information.
 *
 * @author  awesome.ug <support@awesome.ug>
 * @package shipcloudForWooCommerce/API
 * @since   1.4.0
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
class Carriers {
	/**
	 * @var Api
	 */
	private $api;

	/**
	 * Carrier constructor.
	 *
	 * @param Api $api
	 */
	public function __construct( Api $api ) {
		$this->api = $api;
	}

	/**
	 * Fetch all carriers.
	 *
	 * @return Carrier[]
	 */
	public function get() {
		try {
			$response = $this->api->request( 'carriers' );
		} catch (\Exception $e) {
			// Problem with API connection so we stop here.
			return array();
		}

		$fetched = array();
		foreach ( $response->getPayload() as $carrier ) {
			$fetched[] = new \Shipcloud\Domain\Carrier(
				$carrier['name'],
				$carrier['display_name'],
				$carrier['services'],
        $carrier['package_types'],
        $carrier['additional_services'],
        $carrier['label_formats']
			);
		}
		return $fetched;
	}

}
