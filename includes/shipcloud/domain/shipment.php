<?php

namespace Shipcloud\Domain;


use Shipcloud\Api\Response;

class Shipment {
	/**
	 * @var null
	 */
	private $carrier_tracking_no;

	private $id;

	private $label_url;

	private $price;

	private $tracking_url;

	private $packages;

	/**
	 * Shipment constructor.
	 *
	 * @param string $id                  The shipment id that can be used for requesting info about a shipment or
	 *                                    tracking it.
	 * @param string $tracking_url        URL you can send your customers so they can track this shipment.
	 * @param string $label_url           URL where you can download the label in PDF format.
	 * @param double $price               Price that we're going to charge you (exl. VAT).
	 * @param string $carrier_tracking_no The original tracking number that can be used on the carriers website.
	 * @param string $reference_number    A reference number provided by the admin
	 */
	public function __construct( $id, $tracking_url, $label_url, $price, $carrier_tracking_no = null, $packages ) {
		$this->id                  = $id;
		$this->tracking_url        = $tracking_url;
		$this->label_url           = $label_url;
		$this->price               = $price;
		$this->carrier_tracking_no = $carrier_tracking_no;
		$this->packages = $packages;
	}

	/**
	 * Parse response to single Shipment.
	 *
	 * @param Response $response
	 *
	 * @return Shipment
	 */
	public static function fromResponse( $response ) {
		$shipment = $response->getPayload();
        if (!array_key_exists('packages', $shipment)) {
            $shipment['packages'] = array();
        }

		return new static(
			$shipment['id'],
			isset($shipment['tracking_url']) ? $shipment['tracking_url'] : null,
			isset($shipment['label_url']) ? $shipment['label_url'] : null,
			isset($shipment['price']) ? $shipment['price'] : null,
			isset($shipment['carrier_tracking_no']) ? $shipment['carrier_tracking_no'] : null,
			$shipment['packages']
		);
	}

	/**
	 * @return null
	 */
	public function getCarrierTrackingNo() {
		return $this->carrier_tracking_no;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getLabelUrl() {
		return $this->label_url;
	}

	/**
	 * @return double
	 */
	public function getPrice() {
		return $this->price;
	}

	/**
	 * @return string
	 */
	public function getTrackingUrl() {
		return $this->tracking_url;
	}

	public function getPackages() {
		return $this->packages;
	}

	public function getPackage() {
		return $this->packages[0];
	}

	public function getTrackingEvents() {
		return $this->getPackage()['tracking_events'];
	}

	public function getTrackingEventByTimestamp($timestamp) {
		$tracking_events = $this->getTrackingEvents();
		foreach ($tracking_events as $tracking_event) {
			if ($tracking_event['timestamp'] == $timestamp) {
				return $tracking_event;
			}
		}

		return null;
	}
}
