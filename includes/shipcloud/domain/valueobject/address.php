<?php

namespace Shipcloud\Domain\ValueObject;

class Address {
	/**
	 * @var string
	 */
	private $careOf;

	/**
	 * @var string
	 */
	private $company;

	/**
	 * @var string
	 */
	private $firstName;

	/**
	 * @var string
	 */
	private $lastName;

	/**
	 * @var Location
	 */
	private $location;

	/**
	 * Address constructor.
	 *
	 * @param string   $company
	 * @param string   $firstName
	 * @param string   $lastName
	 * @param string   $careOf
	 * @param Location $location
	 */
	public function __construct( $company, $firstName, $lastName, $careOf, Location $location ) {
		$this->company   = $company;
		$this->firstName = $firstName;
		$this->lastName  = $lastName;
		$this->careOf    = $careOf;
		$this->location  = $location;
	}

	/**
	 * @return string
	 */
	public function getCareOf() {
		return $this->careOf;
	}

	/**
	 * @return string
	 */
	public function getCompany() {
		return $this->company;
	}

	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * @return Location
	 */
	public function getLocation() {
		return $this->location;
	}

	public function toArray() {
		return array_merge(
			$this->getLocation()->toArray(),
			[
				'company'    => $this->getCompany(),
				'first_name' => $this->getFirstName(),
				'last_name'  => $this->getLastName(),
				'care_of'    => $this->getCareOf(),
			]
		);
	}
}
