<?php

namespace Shipcloud\Domain\ValueObject;

/**
 * Bank information
 *
 * Object describing a bank account.
 *
 * @package Shipcloud
 */
class BankInformation {
	/**
	 * @var string
	 */
	private $accountHolder;

	/**
	 * @var string
	 */
	private $bankName;

	/**
	 * @var string
	 */
	private $bankSwift;

	/**
	 * @var string
	 */
	private $iban;

	/**
	 * BankInformation constructor.
	 *
	 * @param string $bankName
	 * @param string $bankSwift
	 * @param string $accountHolder
	 * @param string $iban
	 */
	public function __construct( $bankName, $bankSwift, $accountHolder, $iban ) {
		$this->bankName = $bankName;
		$this->bankSwift = $bankSwift;
		$this->accountHolder = $accountHolder;
		$this->iban = $iban;
	}

	/**
	 * @return string
	 */
	public function getAccountHolder() {
		return (string) $this->accountHolder;
	}

	/**
	 * @return string
	 */
	public function getBankName() {
		return (string) $this->bankName;
	}

	/**
	 * @return string
	 */
	public function getBankSwift() {
		return (string) $this->bankSwift;
	}

	/**
	 * @return string
	 */
	public function getIban() {
		return (string) $this->iban;
	}
}
