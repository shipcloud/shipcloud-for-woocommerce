<?php

namespace Shipcloud;

use Shipcloud\Domain\Package;

/**
 * Class ShipmentAdapter
 *
 * @package    Shipcloud
 *
 * @since      1.5.0
 * @deprecated 2.0.0 BC layer.
 */
class ShipmentAdapter {
	/**
	 * @var array
	 */
	private $shipmentData;

	/**
	 * ShipmentAdapter constructor.
	 *
	 * @param array $shipmentData
	 */
	public function __construct( $shipmentData ) {
		$this->shipmentData = $shipmentData;
	}

	/**
	 * @return Package
	 */
	public function getPackage() {
		return new Package(
			$this->shipmentData['length'],
			$this->shipmentData['width'],
			$this->shipmentData['height'],
			$this->shipmentData['weight'],
			null,
			$this->shipmentData['description']
		);
	}

	public function toArray() {
		return array(
			'from' => $this->getFrom(),
			'to'   => $this->getTo(),
			// 'package' => (array) $this->getPackage(),
		);
	}

	/**
	 * @return array
	 */
	protected function getFrom() {
		return array(
			'first_name' => $this->shipmentData['sender_first_name'],
			'last_name'  => $this->shipmentData['sender_last_name'],
			'company'    => $this->shipmentData['sender_company'],
			'street'     => $this->shipmentData['sender_street'],
			'street_no'  => $this->shipmentData['sender_street_no'] ?: $this->shipmentData['sender_street_nr'],
			'care_of'    => array_key_exists( 'sender_care_of', $this->shipmentData ) ? $this->shipmentData['sender_care_of'] : '',
			'zip_code'   => $this->shipmentData['sender_zip_code'],
			'city'       => $this->shipmentData['sender_city'],
			'state'      => $this->shipmentData['sender_state'],
			'phone'      => $this->shipmentData['sender_phone'],
			'country'    => $this->shipmentData['country'],
		);
	}

	/**
	 * @return array
	 */
	protected function getTo() {
		return array(
			'first_name' => $this->shipmentData['recipient_first_name'],
			'last_name'  => $this->shipmentData['recipient_last_name'],
			'company'    => $this->shipmentData['recipient_company'],
			'street'     => $this->shipmentData['recipient_street'],
			'street_no'  => $this->shipmentData['recipient_street_no'] ?: $this->shipmentData['recipient_street_nr'],
			'care_of'    => $this->shipmentData['recipient_care_of'],
			'zip_code'   => $this->shipmentData['recipient_zip_code'],
			'city'       => $this->shipmentData['recipient_city'],
			'state'      => $this->shipmentData['recipient_state'],
			'country'    => $this->shipmentData['recipient_country'],
			'phone'      => $this->shipmentData['recipient_phone'],
		);
	}
}
