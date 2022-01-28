<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\LabelFormatType;
use shipcloud\phpclient\model\LabelSizeType;

/**
 * Label class for label specific definitions.
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
class Label extends AbstractApiObject {
	
	/**
	 * Defines the format that the returned label should have.
	 * 
	 * @var string
	 */
	private $format;
	
	/**
	 * Defines the size that the returned label should have.
	 * 
	 * @var string
	 */
	private $size;
	
	/**
	 * Label constructor.
	 * 
	 * @param string $format
	 * @param string $size
	 * @return void
	 */
	public function __construct( $format = '', $size = '' ) {
		if ( ! empty( $format ) && ! LabelFormatType::is_valid_value( LabelFormatType::get_class_name(), $format ) ) {
			throw new ApiException( 'Invalid label format type: ' . $format );
		}
		$this->format = $format;
		if ( ! empty( $size ) && ! LabelSizeType::is_valid_value( LabelSizeType::get_class_name(), $size ) ) {
			throw new ApiException( 'Invalid label size type: ' . $size );
		}
		$this->size = $size;
		
		$this->required = [];
	}
	
	/**
	 * Getter method for format.
	 * @return string The label format.
	 * @see LabelFormatType
	 */
	public function get_format() {
		return $this->format;
	}
	
	/**
	 * Setter method for format.
	 * @param string $format The format to set.
	 * @return void
	 * @see LabelFormatType
	 * @throws ApiException
	 */
	public function set_format( string $format ) : void {
		if ( ! LabelFormatType::is_valid_value( LabelFormatType::get_class_name(), $format ) ) {
			throw new ApiException( 'Invalid label format type: ' . $format );
		}
		$this->format = $format;
	}
	
	/**
	 * Getter method for size.
	 * @return string The size.
	 * @see LabelSizeType
	 */
	public function get_size() {
		return $this->size;
	}
	
	/**
	 * Setter method for size.
	 * @param string $size The size to set.
	 * @return void
	 * @see LabelSizeType
	 * @throws ApiException
	 */
	public function set_size( string $size ) : void {
		if ( ! LabelSizeType::is_valid_value( LabelSizeType::get_class_name(), $size ) ) {
			throw new ApiException( 'Invalid label size type: ' . $size );
		}
		$this->size = $size;
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
		
		if ( ! empty( $this->get_size() ) ) {
			$result['size'] = $this->get_size();
		}
		
		return $result;
	}
}
