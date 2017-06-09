<?php
/**
 * WooCommerce shipcloud.io postboxes
 * Loading postboxes
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since   1.2.1
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Shipcloud_Order_Bulk
 *
 * Bulk functionalities for Label creation
 *
 * @since   1.2.1
 */
class WC_Shipcloud_Order_Bulk {
	/**
	 * WC_Shipcloud_Order_Bulk constructor.
	 *
	 * @since   1.2.1
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initializing hooks and filters
	 *
	 * @since   1.2.1
	 */
	private function init_hooks() {
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );
		add_action( 'load-edit.php', array( $this, 'load_edit' ) );

		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions' ) );
		add_action( 'load-edit.php', array( $this, 'handle_wcsc_order_bulk' ) );
	}

	/**
	 * Handling Submit after bulk action
	 *
	 * @since   1.2.1
	 */
	public function handle_wcsc_order_bulk() {
		if ( ! is_admin() || ! get_current_screen() || 'edit-shop_order' !== get_current_screen()->id ) {
			// None of our business.
			return;
		}

		$request = $_GET; // XSS: OK.

		if ( 'wcsc_order_bulk_label' !== $request['action']
		     || ! isset( $request['action'], $request['wcsc_carrier'] )
		     || ! $request['wcsc_carrier']
		) {
			return;
		}

		$package = array(
			'width'  => $request['wcsc_width'],
			'height' => $request['wcsc_height'],
			'length' => $request['wcsc_length'],
			'weight' => $request['wcsc_weight'],
		);

		$succeeded = 0;
		foreach ( $request['post'] as $order_id ) {
			$order = WC_Shipcloud_Order::create_order( $order_id );

			$shipment = wcsc_api()->create_shipment_by_order(
				$order,
				$request['wcsc_carrier'],
				$package
			);

			if ( is_wp_error( $shipment ) ) {
				/** @var \WP_Error $shipment */
				WC_Shipcloud_Shipping::log(
					'Order #' . $order->get_wc_order()->get_order_number()
					. ' - ' . $shipment->get_error_message()
					. ' (' . wcsc_get_carrier_display_name( $request['carrier'] ) . ')'
				);

				WooCommerce_Shipcloud::admin_notice(
					sprintf(
						__( 'No label for order #%d created: %s' ),
						$order->get_wc_order()->get_id(),
						str_replace( "\n", ', ', $shipment->get_error_message() )
					),
					'error'
				);

				continue;
			}

			WC_Shipcloud_Shipping::log( 'Order #' . $order->get_wc_order()->get_order_number() . ' - Created shipment successful (' . wcsc_get_carrier_display_name( $request['carrier'] ) . ')' );

			$parcel_title = wcsc_get_carrier_display_name( $request['wcsc_carrier'] )
			                . ' - '
			                . $request['wcsc_width']
			                . __( 'x', 'shipcloud-for-woocommerce' )
			                . $request['wcsc_height']
			                . __( 'x', 'shipcloud-for-woocommerce' )
			                . $request['wcsc_length']
			                . __( 'cm', 'shipcloud-for-woocommerce' )
			                . ' '
			                . $request['wcsc_weight']
			                . __( 'kg', 'shipcloud-for-woocommerce' );

			$data = array(
				'id'                  => $shipment['id'],
				'carrier_tracking_no' => $shipment['carrier_tracking_no'],
				'tracking_url'        => $shipment['tracking_url'],
				'label_url'           => $shipment['label_url'],
				'price'               => $shipment['price'],
				'parcel_id'           => $shipment['id'],
				'parcel_title'        => $parcel_title,
				'carrier'             => $request['carrier'],
				'width'               => $request['width'],
				'height'              => $request['height'],
				'length'              => $request['length'],
				'weight'              => $request['weight'],
				'description'         => $request['description'],
				'date_created'        => time(),
			);

			$data = array_merge( $data, $order->get_sender( 'sender_' ) );
			$data = array_merge( $data, $order->get_recipient( 'recipient_' ) );

			add_post_meta( $order_id, 'shipcloud_shipment_ids', $data['id'] );
			add_post_meta( $order_id, 'shipcloud_shipment_data', $data );

			$order->get_wc_order()->add_order_note( __( 'shipcloud label has been created.', 'shipcloud-for-woocommerce' ) );

			$succeeded ++;
		}

		WooCommerce_Shipcloud::admin_notice(
			sprintf( 'Created %d labels.', $succeeded ), 'updated'
		);
	}

	/**
	 * Adding bulk action to dropdown
	 *
	 * @since   1.2.1
	 *
	 * @param array $actions Bulk actions
	 *
	 * @return array $actions Bulk actions with own Actions
	 */
	public function add_bulk_actions( $actions ) {
		$actions['wcsc_order_bulk_label'] = __( 'Create shipping labels', 'shipcloud-for-woocommerce' );

		return $actions;
	}

	/**
	 * Adding Footer Scripts
	 *
	 * @since   1.2.1
	 */
	public function admin_print_footer_scripts() {
		require_once WCSC_FOLDER . '/includes/shipcloud/block-order-labels-bulk.php';

		$block = new WooCommerce_Shipcloud_Block_Order_Labels_Bulk(
			WCSC_COMPONENTFOLDER . '/block/order-labels-bulk.php',
			wcsc_shipping_method()->get_allowed_carriers(),
			new Woocommerce_Shipcloud_API()
		);

		$block->dispatch();
	}

	/**
	 * Loading Scripts
	 *
	 * @since   1.2.1
	 */
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
