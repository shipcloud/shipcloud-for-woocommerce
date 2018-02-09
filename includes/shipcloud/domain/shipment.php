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
	
	private $reference_number;

	/**
	 * Shipment constructor.
	 *
	 * @param string $id                  The shipment id that can be used for requesting info about a shipment or
	 *                                    tracking it.
	 * @param string $tracking_url        URL you can send your customers so they can track this shipment.
	 * @param string $label_url           URL where you can download the label in PDF format.
	 * @param double $price               Price that we're going to charge you (exl. VAT).
	 * @param string $carrier_tracking_no The original tracking number that can be used on the carriers website.
	 */
	public function __construct( $id, $tracking_url, $label_url, $price, $carrier_tracking_no = null ) {
		$this->id                  = $id;
		$this->tracking_url        = $tracking_url;
		$this->label_url           = $label_url;
		$this->price               = $price;
		$this->carrier_tracking_no = $carrier_tracking_no;
		$this->reference_number    = $reference_number;
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

		return new static(
			$shipment['id'],
			$shipment['tracking_url'],
			$shipment['label_url'],
			$shipment['price'],
			$shipment['carrier_tracking_no'],
			$shipment['reference_number']
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
	
		/**
		 * @return string
		 */
	public function getReferenceNumber() {
		return $this->reference_number;
	}
}
