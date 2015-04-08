<?php
/*
 * WooCommerce shipcloud.io postboxes
 *
 * Loading postboxes
 *
 * @author awesome.ug <contact@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 (contact@awesome.ug)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !defined( 'ABSPATH' ) ) exit;

class WC_Shipcloud_Metaboxes{
	
	/**
	 * Initialize class
	 */
	public static function init(){
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_metaboxes' ) );
	}
	
	/**
	 * Adding meta boxes
	 */
	public static function add_metaboxes(){
		add_meta_box(
			'shipcloudio',
			__( 'shipcloud.io shipment', 'wcsc-locale' ),
			array( $this, 'product_metabox' ),
			'shop_order'
		);
	}
	
	/**
	 * Product metabox
	 */
	public static function product_metabox(){
		echo 'test';
	}
}
