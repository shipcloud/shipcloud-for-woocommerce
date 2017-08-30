<?php

namespace Shipcloud\Domain\ValueObject;

class Location {
	/**
	 * @var string
	 */
	private $city;

	/**
	 * @var string
	 */
	private $country;

	/**
	 * @var string
	 */
	private $street;

	/**
	 * @var string
	 */
	private $streetNo;

	/**
	 * @var string
	 */
	private $zipCode;

	/**
	 * Location constructor.
	 *
	 * @param string $country
	 * @param string $zipCode
	 * @param string $city
	 * @param string $streetNo
	 * @param string $street
	 */
	public function __construct( $country, $zipCode, $city, $street, $streetNo ) {
		$this->country  = $country;
		$this->zipCode  = $zipCode;
		$this->city     = $city;
		$this->street   = $street;
		$this->streetNo = $streetNo;
	}

	/**
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * @return string
	 */
	public function getStreet() {
		return $this->street;
	}

	/**
	 * @return string
	 */
	public function getStreetNo() {
		return $this->streetNo;
	}

	/**
	 * @return string
	 */
	public function getZipCode() {
		return $this->zipCode;
	}

	public function toArray() {
		return [
			'country'   => $this->getCountry(),
			'zip_code'  => $this->getZipCode(),
			'city'      => $this->getCity(),
			'street'    => $this->getStreet(),
			'street_no' => $this->getStreetNo(),
		];
	}
}
