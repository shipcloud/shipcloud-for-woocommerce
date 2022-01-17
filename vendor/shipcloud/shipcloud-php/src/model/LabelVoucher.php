<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\LabelVoucherFormatType;
use shipcloud\phpclient\model\LabelVoucherType;

/**
 * LabelVoucher class for label voucher characteristics.
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
class LabelVoucher extends AbstractApiObject {
	
	/**
	 * File format the label voucher should be in.
	 * 
	 * @var string
	 */
	private $format;
	
	/**
	 * Defines the size that the returned label should have.
	 * 
	 * @var string
	 */
	private $type;
	
	/**
	 * Label constructor.
	 * 
	 * @param string $format
	 * @param string $type
	 * @return void
	 * @throws ApiException
	 */
	public function __construct( string $format, string $type ) {
		
		if ( ! LabelVoucherFormatType::is_valid_value( LabelVoucherFormatType::get_class_name(), $format ) ) {
			throw new ApiException( 'Invalid label format type: ' . $format );
		}
		if ( ! LabelVoucherType::is_valid_value( LabelVoucherType::get_class_name(), $type ) ) {
			throw new ApiException( 'Invalid label voucher type: ' . $type );
		}
		
		$this->format = $format;
		$this->type   = $type;
		
		$this->required = [ "format", "type" ];
	}
	
	/**
	 * Getter method for format.
	 * @return string The label format.
	 * @see LabelFormatType
	 */
	public function get_format() : string {
		return $this->format;
	}
	
	/**
	 * Setter method for format.
	 * @param string $format The format to set.
	 * @return void
	 * @see LabelVoucherFormatType
	 * @throws ApiException
	 */
	public function set_format( string $format ) : void {
		if ( ! LabelVoucherFormatType::is_valid_value( LabelVoucherFormatType::get_class_name(), $format ) ) {
			throw new ApiException( 'Invalid label format type: ' . $format );
		}
		$this->format = $format;
	}
	
	/**
	 * Getter method for type.
	 * @return string The type.
	 * @see LabelVoucherType
	 */
	public function get_type() : string {
		return $this->type;
	}
	
	/**
	 * Setter method for type.
	 * @param string $type The type to set.
	 * @return void
	 * @see LabelVoucherType
	 * @throws ApiException
	 */
	public function set_type( string $type ) : void {
		if ( ! LabelVoucherType::is_valid_value( LabelVoucherType::get_class_name(), $type ) ) {
			throw new ApiException( 'Invalid label voucher type: ' . $type );
		}
		$this->type = $type;
	}
	
	/**
	 * Getter method for parameter array.
	 * @return array The class object as array.
	 */
	public function to_array() : array {
		
		$result = parent::to_array();
		
		if ( ! empty( $this->get_format() ) ) {
			$result['format'] = $this->get_format();
		}
		
		if ( ! empty( $this->get_type() ) ) {
			$result['type'] = $this->get_type();
		}
		
		return $result;
	}
}
