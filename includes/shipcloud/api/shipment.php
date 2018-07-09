<?php
/**
 * Contains class to handle shipment on API side.
 */

namespace Shipcloud\Api;

use Shipcloud\Api;
use Shipcloud\Domain\Carrier;

/**
 * Access the API for handling shipment and labels.
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
class Shipment {
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
	 * @param $data
	 *
	 * @return \Shipcloud\Domain\Shipment
	 */
	public function create( $data ) {
		return \Shipcloud\Domain\Shipment::fromResponse(
			$this->api->request( 'shipments', $data, 'POST' )
		);
	}

	public function update( $shipment_id, $data ) {
		return \Shipcloud\Domain\Shipment::fromResponse(
			$this->api->request( 'shipments/' . $shipment_id, $data, 'PUT' )
		);
	}

	public function get( $shipment_id ) {
		$response = $this->api->request( 'shipments/' . $shipment_id, null, 'GET' );
		return \Shipcloud\Domain\Shipment::fromResponse($response);
	}
}
