<?php
/**
 * WooCommerce shipcloud.io postboxes
 * Loading postboxes
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
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

class WC_Shipcloud_Order_Bulk {
	public function __construct() {
		$this->init_hooks();
	}

	private function init_hooks() {
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );
		add_action( 'load-edit.php', array( $this, 'load_edit' ) );

		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions' ) );
	}

	public function add_bulk_actions( $actions ) {
		$actions['wcsc_order_bulk_label'] = __( 'Create shipping labels', 'woocommerce-shipcloud' );

		return $actions;
	}

	public function admin_print_footer_scripts() {
		require_once WCSC_FOLDER . '/includes/shipcloud/block-order-labels-bulk.php';

		$block = new WooCommerce_Shipcloud_Block_Order_Labels_Bulk(
			WCSC_COMPONENTFOLDER . '/block/order-labels-bulk.php',
			wcsc_shipping_method()->get_allowed_carriers(),
			new Woocommerce_Shipcloud_API()
		);

		$block->dispatch();
	}

	public function load_edit() {
		wp_register_script(
			'wcsc_bulk_order_label',
			WCSC_URLPATH . '/includes/js/bulk-order-label.js',
			array( 'jquery' )
		);

		wp_enqueue_script( 'wcsc_bulk_order_label', false, array(), false, true );
	}
}

$wc_shipcloud_order_bulk = new WC_Shipcloud_Order_Bulk();
