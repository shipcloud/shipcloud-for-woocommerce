<?php
/**
 * shipcloud API
 *
 * @author  awesome.ug <support@awesome.ug>
 * @package shipcloud
 * @version 2017-02-02
 * @since   2017-02-02
 * @see     https://github.com/shipcloud/shipcloud.github.io/blob/master/API_CHANGELOG.md
 * @see     https://developers.shipcloud.io/reference/
 * @license GPL 2
 *          Copyright 2017 (support@awesome.ug)
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace Shipcloud\Domain;

use Shipcloud\Domain\Package\DeclaredValue;

/**
 * Package
 *
 * Object describing the package dimensions.
 *
 * @package Shipcloud
 */
class Package {
	/**
	 * Describing the value of the package contents.
	 *
	 * @var DeclaredValue
	 */
	public $declared_value;

	/**
	 * Description for some carrier.
	 *
	 * If you’re using UPS with service returns or DHL with service express this is mandatory otherwise it’s optional.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Height of the package in cm
	 *
	 * @var float
	 */
	public $height;

	/**
	 * Length of the package in cm.
	 *
	 * @var float
	 */
	public $length;

	/**
	 * Carrier specific package type declaration.
	 *
	 * Default will be "parcel".
	 * Other available values are “books”, “bulk”, “letter” and “parcel_letter”.
	 * See package types for detailed information.
	 *
	 * @var string
	 */
	public $type = 'parcel';

	/**
	 * Weight of the package in cm
	 *
	 * @var float
	 */
	public $weight;

	/**
	 * Width of the package in cm.
	 *
	 * @var float
	 */
	public $width;

	/**
	 * Package constructor.
	 *
	 * @param float  $length Length of the package in cm.
	 * @param float  $width  Width of the package in cm.
	 * @param float  $height Height of the package in cm.
	 * @param float  $weight Weight of the package in kg.
	 * @param string $type
	 */
	public function __construct( $length, $width, $height, $weight, $type = 'parcel', $description = '' ) {
		$this->length      = $length;
		$this->width       = $width;
		$this->height      = $height;
		$this->weight      = $weight;
		$this->type        = $type;
		$this->description = $description;
	}

	/**
	 * Check if the mandatory fields are valid.
	 *
	 * @return bool
	 */
	public function isValid() {
		return 0.0 < (float) $this->length
			   && 0.0 < (float) $this->width
			   && 0.0 < (float) $this->height
			   && 0.0 < (float) $this->weight
			   && ( null === $this->declared_value || $this->declared_value instanceof DeclaredValue );
	}
}
