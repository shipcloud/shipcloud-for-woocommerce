<?php

namespace Shipcloud\Domain\Services;

use Shipcloud\Domain\ValueObject\BankInformation;

/**
 * Package
 *
 * Object describing the package dimensions.
 *
 * @package Shipcloud
 */
class CashOnDelivery {
	const NAME = 'cash_on_delivery';

	/**
	 * @var float
	 */
	private $amount;

	/**
	 * @var BankInformation
	 */
	private $bankInformation;

	/**
	 * @var string
	 */
	private $currency;

	/**
	 * @var string
	 */
	private $reference;

	/**
	 * Information for cash on delivery
	 *
	 * @param float           $amount          Amount of money customer needs to pay to the shop.
	 * @param string          $currency        Three letter code of a currency.
	 * @param BankInformation $bankInformation Bank account which shall receive the money.
	 * @param string          $reference       Optional field that shall be shown on the cash transfer statement.
	 *
	 * @throws \InvalidArgumentException For invalid currencies
	 * @throws \OutOfRangeException When the amount is out of allowed range.
	 */
	public function __construct( $amount, $currency, BankInformation $bankInformation, $reference = '' ) {
		if ( $amount < 0.01 || $amount > 3500.00 ) {
			throw new \OutOfRangeException( 'Invalid amount. Supported range is 0.01 - 3500.00' );
		}

		$this->amount = $amount;

		if ( 3 !== strlen( $currency ) ) {
			throw new \InvalidArgumentException( 'Invalid currency. Please provide the three letter code.' );
		}

		$this->currency = $currency;

		$this->bankInformation = $bankInformation;
		$this->reference       = $reference;
	}

	/**
	 * @return string
	 */
	public function getReference() {
		return $this->reference;
	}

	/**
	 * @return float
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @return BankInformation
	 */
	public function getBankInformation() {
		return $this->bankInformation;
	}

	/**
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}

	public function toArray() {
		return array(
			'amount'              => $this->getAmount(),
			'currency'            => $this->getCurrency(),
			'bank_account_holder' => $this->getBankInformation()->getAccountHolder(),
			'bank_name'           => $this->getBankInformation()->getBankName(),
			'bank_account_number' => $this->getBankInformation()->getIban(),
			'bank_code'           => $this->getBankInformation()->getBankSwift(),
			'reference1'          => $this->getReference()
		);
	}
}
