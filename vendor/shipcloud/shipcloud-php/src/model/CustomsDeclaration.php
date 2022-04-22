<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\ContentsType;

/**
 * CustomsDeclaration class represents a declaration of customs related information.
 *
 * @category 	Class
 * @package  	shipcloud\phpclient\model
 * @author   	Daniel Muenter <info@msltns.com>
 * @version  	0.0.1
 * @since   	0.0.1
 * @license 	GPL 3
 *          	This program is free software; you can redistribute it and/or modify
 *          	it under the terms of the GNU General Public License, version 3, as
 *          	published by the Free Software Foundation.
 *          	This program is distributed in the hope that it will be useful,
 *          	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          	GNU General Public License for more details.
 *          	You should have received a copy of the GNU General Public License
 *          	along with this program; if not, write to the Free Software
 *          	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class CustomsDeclaration extends AbstractApiObject {
	
	/**
	 * Type of contents.
	 * 
	 * @var string
	 */
	 private $contents_type;
			
	/**
	 * Description of contents. Mandatory if contents_type is 'commercial_goods. 
	 * Max 256 characters, when using DHL as your carrier'.
	 * 
	 * @var string
	 */
	 private $contents_explanation;
			
	/**
	 * A valid ISO 4217 curreny code.
	 * 
	 * @var string
	 */
	 private $currency;
			
	/**
	 * Additional custom fees to be payed.
	 * 
	 * @var float
	 */
	 private $additional_fees;
			
	/**
	 * Location where the package will be dropped of with the carrier.
	 * 
	 * @var string
	 */
	 private $drop_off_location;
			
	/**
	 * A note for the exporter.
	 * 
	 * @var string
	 */
	 private $exporter_reference;
			
	/**
	 * A note for the importer.
	 * 
	 * @var string
	 */
	 private $importer_reference;
			
	/**
	 * Date of commital at carrier.
	 * 
	 * @var string
	 */
	 private $posting_date;
			
	/**
	 * Invoice number for the order.
	 * 
	 * @var string
	 */
	 private $invoice_number;
			
	/**
	 * The overall value of the shipments' contents.
	 * 
	 * @var float
	 */
	 private $total_value_amount;
			
	/**
	 * Array of item objects.
	 * 
	 * @var array
	 */
	 private $items;
	
	/**
	 * CustomsDeclaration constructor.
	 * 
	 * @param string $contents_type
	 * @param string $currency
	 * @param float $total_value_amount
	 * @param array $items
	 * @return void
	 * @throws ApiException
	 */
	public function __construct( string $contents_type, string $currency, float $total_value_amount, array $items ) {
		
		if ( ! ContentsType::is_valid_value( ContentsType::get_class_name(), $contents_type ) ) {
			throw new ApiException( 'Invalid contents type: ' . $contents_type );
		}
		if ( ! preg_match( '/[A-Z]{3}/', $currency ) ) {
			throw new ApiException( 'Currency must be a valid ISO 4217 curreny code, ' . $currency . ' given' );
		}
		if ( $total_value_amount < 0 || $total_value_amount > 1000 ) {
			throw new ApiException( 'Invalid total value amount: ' . $total_value_amount . ', must be between 0 and 1000' );
		}
		if ( empty( $items ) ) {
			throw new ApiException( 'Items must not be empty!' );
		}
		
		$this->contents_type 		= $contents_type;
		$this->currency 			= $currency;
		$this->total_value_amount 	= $total_value_amount;
		$this->items 				= $items;
		
		$this->required = [ "contents_type", "currency", "total_value_amount", "items" ];
	}
	
	/**
	 * Getter method for contents_type.
	 * @return string The contents_type.
	 */
	public function get_contents_type() {
		return $this->contents_type;
	}

	/**
	 * Setter method for contents_type.
	 * @param string $contents_type The contents_type to set.
	 * @return void
	 * @see ContentsType
	 * @throws ApiException
	 */
	public function set_contents_type( string $contents_type ) : void {
		if ( ! ContentsType::is_valid_value( ContentsType::get_class_name(), $contents_type ) ) {
			throw new ApiException( 'Invalid contents type: ' . $contents_type );
		}
		$this->contents_type = $contents_type;
	}

	/**
	 * Getter method for contents_explanation.
	 * @return string The contents_explanation.
	 */
	public function get_contents_explanation() {
		return $this->contents_explanation;
	}

	/**
	 * Setter method for contents_explanation.
	 * @param string $contents_explanation The contents_explanation to set.
	 * @return void
	 */
	public function set_contents_explanation( string $contents_explanation ) : void {
		if ( empty( trim( $contents_explanation ) ) ) {
			throw new ApiException( "Contents explanation can't be blank" );
		}
		$this->contents_explanation = $contents_explanation;
	}

	/**
	 * Getter method for currency.
	 * @return string The currency.
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * Setter method for currency.
	 * @param string $currency The currency to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_currency( string $currency ) : void {
		if ( ! preg_match( '/[A-Z]{3}/', $currency ) ) {
			throw new ApiException( 'Currency must be a valid ISO 4217 curreny code, ' . $currency . ' given' );
		}
		$this->currency = $currency;
	}

	/**
	 * Getter method for additional_fees.
	 * @return float The additional_fees.
	 */
	public function get_additional_fees() {
		return $this->additional_fees;
	}

	/**
	 * Setter method for additional_fees.
	 * @param float $additional_fees The additional_fees to set.
	 * @return void
	 */
	public function set_additional_fees( float $additional_fees ) : void {
		$this->additional_fees = $additional_fees;
	}

	/**
	 * Getter method for drop_off_location.
	 * @return string The drop_off_location.
	 */
	public function get_drop_off_location() {
		return $this->drop_off_location;
	}

	/**
	 * Setter method for drop_off_location.
	 * @param string $drop_off_location The drop_off_location to set.
	 * @return void
	 */
	public function set_drop_off_location( string $drop_off_location ) : void {
		$this->drop_off_location = $drop_off_location;
	}

	/**
	 * Getter method for exporter_reference.
	 * @return string The exporter_reference.
	 */
	public function get_exporter_reference() {
		return $this->exporter_reference;
	}

	/**
	 * Setter method for exporter_reference.
	 * @param string $exporter_reference The exporter_reference to set.
	 * @return void
	 */
	public function set_exporter_reference( string $exporter_reference ) : void {
		$this->exporter_reference = $exporter_reference;
	}

	/**
	 * Getter method for importer_reference.
	 * @return string The importer_reference.
	 */
	public function get_importer_reference() {
		return $this->importer_reference;
	}

	/**
	 * Setter method for importer_reference.
	 * @param string $importer_reference The importer_reference to set.
	 * @return void
	 */
	public function set_importer_reference( string $importer_reference ) : void {
		$this->importer_reference = $importer_reference;
	}

	/**
	 * Getter method for posting_date.
	 * @return string The posting_date.
	 */
	public function get_posting_date() {
		return $this->posting_date;
	}

	/**
	 * Setter method for posting_date.
	 * @param string $posting_date The posting_date to set.
	 * @return void
	 */
	public function set_posting_date( string $posting_date ) : void {
		$this->posting_date = $posting_date;
	}

	/**
	 * Getter method for invoice_number.
	 * @return string The invoice_number.
	 */
	public function get_invoice_number() {
		return $this->invoice_number;
	}

	/**
	 * Setter method for invoice_number.
	 * @param string $invoice_number The invoice_number to set.
	 * @return void
	 */
	public function set_invoice_number( string $invoice_number ) : void {
		$this->invoice_number = $invoice_number;
	}

	/**
	 * Getter method for total_value_amount.
	 * @return float The total_value_amount.
	 */
	public function get_total_value_amount() {
		return $this->total_value_amount;
	}

	/**
	 * Setter method for total_value_amount.
	 * @param float $total_value_amount The total_value_amount to set (restriction minimum: 0, maximum: 1000).
	 * @return void
	 * @throws ApiException
	 */
	public function set_total_value_amount( float $total_value_amount ) : void {
		if ( $total_value_amount < 0 || $total_value_amount > 1000 ) {
			throw new ApiException( 'Invalid total value amount: ' . $total_value_amount . ', must be between 0 and 1000' );
		}
		$this->total_value_amount = $total_value_amount;
	}

	/**
	 * Getter method for items.
	 * @return array The items.
	 */
	public function get_items() {
		return $this->items;
	}

	/**
	 * Setter method for items.
	 * @param array $items The items to set.
	 * @return void
	 * @throws ApiException
	 */
	public function set_items( array $items ) : void {
		if ( empty( $items ) ) {
			throw new ApiException( 'Items must not be empty!' );
		}
		$this->items = $items;
	}
	
	/**
	 * Getter method for parameter array.
	 * @return array The class object as array.
	 */
	public function to_array() : array {
		
		$result = parent::to_array();
		foreach( $this->get_required_fields() as $field ) {
			$result[$field] = $this->{"get_{$field}"}();
		}
		
		if ( ! empty( $this->get_contents_explanation() ) ) {
		    $result['contents_explanation'] = $this->get_contents_explanation();
		}
		if ( ! empty( $this->get_additional_fees() ) ) {
		    $result['additional_fees'] = $this->get_additional_fees();
		}
		if ( ! empty( $this->get_drop_off_location() ) ) {
		    $result['drop_off_location'] = $this->get_drop_off_location();
		}
		if ( ! empty( $this->get_exporter_reference() ) ) {
		    $result['exporter_reference'] = $this->get_exporter_reference();
		}
		if ( ! empty( $this->get_importer_reference() ) ) {
		    $result['importer_reference'] = $this->get_importer_reference();
		}
		if ( ! empty( $this->get_posting_date() ) ) {
		    $result['posting_date'] = $this->get_posting_date();
		}
		if ( ! empty( $this->get_invoice_number() ) ) {
		    $result['invoice_number'] = $this->get_invoice_number();
		}
		if ( ! empty( $this->get_total_value_amount() ) ) {
		    $result['total_value_amount'] = $this->get_total_value_amount();
		}
		if ( ! empty( $this->get_items() ) ) {
		    $result['items'] = $this->get_items();
		}
		
		return $result;
	}
}
