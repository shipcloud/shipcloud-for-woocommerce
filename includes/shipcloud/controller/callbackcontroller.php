<?php

namespace Shipcloud\Controller;

use Shipcloud\Api;
use Shipcloud\Repository\ShipmentRepository;

class CallbackController {
	const PATH_CALLBACK_SHIPMENT = '/callback/shipment';
	/**
	 * @var Api
	 */
	private $api;

	/**
	 * @var ShipmentRepository
	 */
	private $shipment_repository;

	/**
	 * Label_Controller constructor.
	 *
	 * @param Api                $api
	 * @param ShipmentRepository $shipment_repository
	 */
	public function __construct( Api $api, ShipmentRepository $shipment_repository ) {
		$this->api                 = $api;
		$this->shipment_repository = $shipment_repository;
	}

	public function callback_action() {

	}
}
