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

namespace Shipcloud\Domain\Package;

/**
 * Declared value.
 *
 * Describing the value of the package contents.
 *
 * @package Shipcloud
 */
class DeclaredValue {
	/**
	 * Value of package contents.
	 *
	 * @var float
	 */
	public $amount;

	/**
	 * Currency as uppercase ISO 4217 code (e.g. EUR).
	 *
	 * @var string
	 */
	public $currency;

	/**
	 * DeclaredValue constructor.
	 *
	 * @param float  $amount   Value of package contents.
	 * @param string $currency Currency as uppercase ISO 4217 code (e.g. EUR).
	 */
	public function __construct( $amount, $currency ) {
		$this->amount   = $amount;
		$this->currency = $currency;
	}
}
