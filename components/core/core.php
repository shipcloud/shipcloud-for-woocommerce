<?php
/**
 * WooCommerce Core Component
 * Loading extensions for Woo
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package shipcloudForWooCommerce/Core
 * @version 1.0.0
 * @since   1.0.0
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

if ( ! defined( 'ABSPATH' ) )
{
	exit;
}

class WCSC_Core extends WCSC_Component
{
	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	protected function __construct()
	{
		$this->name = __( 'shipcloud.io Core', 'woocommerce-shipcloud' );
		$this->slug = 'sccore';
	}

	/**
	 * Includes
	 *
	 * @since 1.0.0
	 */
	protected function includes()
	{
		if ( wcsc_is_enabled() )
		{
			require_once( __DIR__ . '/core-functions.php' );
			require_once( __DIR__ . '/parcel-template.php' );
		}
	}
}

wcsc_load_component( 'WCSC_Core' );