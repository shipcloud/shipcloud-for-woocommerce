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

	const FORM_BULK = 'wcsc_order_bulk';
	const BUTTON_PDF = 'wscs_order_bulk_pdf';

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

		if ( self::FORM_BULK !== $request['action']
		     || ! isset( $request['action'], $request['wcsc_carrier'] )
		     || ! $request['wcsc_carrier']
		) {
			return;
		}

		if ( isset( $request[ self::BUTTON_PDF ] ) ) {
			$this->create_pdf( $request );

			return;
		}

		$this->create_label( $request );
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
		$actions[ self::FORM_BULK ] = __( 'Create shipping labels', 'woocommerce-shipcloud' );

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

	protected function create_label( $request ) {
		$succeeded = 0;
		foreach ( $request['post'] as $order_id ) {
			if ( $this->create_label_for_order( $order_id, $request ) ) {
				$succeeded ++;
			}
		}

		WooCommerce_Shipcloud::admin_notice(
			sprintf( 'Created %d labels.', $succeeded ), 'updated'
		);
	}

	protected function save_label( $order_id, $url ) {
		$path = $this->get_storage_path( 'order' . DIRECTORY_SEPARATOR . $order_id )
		        . DIRECTORY_SEPARATOR . 'label.pdf';

		if ( file_exists( $path ) ) {
			// Might be already downloaded, so we won't overwrite it.
			return $path;
		}

		$pdf_content = wp_remote_retrieve_body( wp_remote_get( $url ) );

		if ( ! $pdf_content ) {
			// No content, so we refuse to continue.
			throw new \RuntimeException( 'Could not download PDF - no content delivered.' );
		}

		if ( ! $this->get_filesystem()->put_contents( $path, $pdf_content ) ) {
			throw new \RuntimeException( 'Could not store downloaded PDF contents.' );
		}

		return $path;
	}

	protected function get_storage_url( $suffix = null ) {
		$url = 'shipcloud-woocommerce';

		if ( null !== $suffix && $suffix ) {
			$url .= '/' . $suffix;
		}

		return content_url( $url );
	}

	protected function create_pdf( $request ) {
		$pdf_basename = sha1( implode( ',', $request['post'] ) ) . '.pdf';
		$pdf_file     = $this->get_storage_path( 'labels' ) . DIRECTORY_SEPARATOR . $pdf_basename;
		$pdf_url      = $this->get_storage_url( 'labels' ) . '/' . $pdf_basename;

		$download_message = sprintf(
			'Labels can be downloaded using this URL: %s',
			'<a href="' . esc_attr( $pdf_url ) . '" target="_blank">' . esc_html( $pdf_url ) . '</a>'
		);

		if ( file_exists( $pdf_file ) ) {
			WooCommerce_Shipcloud::admin_notice( $download_message, 'updated' );

			return;
		}

		WooCommerce_Shipcloud::load_fpdf();

		$m = new \iio\libmergepdf\Merger();
		foreach ( $request['post'] as $order_id ) {
			$current       = $this->create_label_for_order( $order_id, $request );
			$error_message = sprintf( 'Problem generating label for order #%d', $order_id );

			if ( ! $current || ! isset( $current['label_url'] ) ) {
				WooCommerce_Shipcloud::admin_notice( $error_message, 'updated' );

				continue;
			}

			try {
				// Storing label.
				$path_to_pdf = $this->save_label( $order_id, $current['label_url'] );
				$m->addFromFile( $path_to_pdf );
			} catch ( \RuntimeException $e ) {
				WooCommerce_Shipcloud::admin_notice( $error_message, 'updated' );
			}
		}

		$content = $m->merge();

		if ( ! $content ) {
			WooCommerce_Shipcloud::admin_notice( 'Could not compose labels into one PDF.', 'error' );

			return;
		}


		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			WP_Filesystem();
		}

		$wp_filesystem->put_contents( $pdf_file, $content );

		WooCommerce_Shipcloud::admin_notice( $download_message, 'updated' );
	}

	protected function create_label_for_order( $order_id, $request ) {
		$order = WC_Shipcloud_Order::create_order( $order_id );

		$shipment = wcsc_api()->create_shipment_by_order(
			$order,
			$request['wcsc_carrier'],
			$this->get_package_data( $request )
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
					$order->get_wc_order()->id,
					str_replace( "\n", ', ', $shipment->get_error_message() )
				),
				'error'
			);

			return array();
		}

		WC_Shipcloud_Shipping::log( 'Order #' . $order->get_wc_order()->get_order_number() . ' - Created shipment successful (' . wcsc_get_carrier_display_name( $request['carrier'] ) . ')' );

		$parcel_title = wcsc_get_carrier_display_name( $request['wcsc_carrier'] )
		                . ' - '
		                . $request['wcsc_width']
		                . __( 'x', 'woocommerce-shipcloud' )
		                . $request['wcsc_height']
		                . __( 'x', 'woocommerce-shipcloud' )
		                . $request['wcsc_length']
		                . __( 'cm', 'woocommerce-shipcloud' )
		                . ' '
		                . $request['wcsc_weight']
		                . __( 'kg', 'woocommerce-shipcloud' );

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

		$order->get_wc_order()->add_order_note( __( 'shipcloud.io label was created.', 'woocommerce-shipcloud' ) );

		return $data;
	}

	/**
	 * @param $request
	 *
	 * @return array
	 */
	protected function get_package_data( $request ) {
		return array(
			'width'  => $request['wcsc_width'],
			'height' => $request['wcsc_height'],
			'length' => $request['wcsc_length'],
			'weight' => $request['wcsc_weight'],
		);
	}

	/**
	 * @param null $order_id
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function get_storage_path( $suffix = null ) {
		$path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'shipcloud-woocommerce';

		if ( null !== $suffix && $suffix ) {
			$path .= DIRECTORY_SEPARATOR . trim( $suffix, '\\/' );
		}

		if ( is_dir( $path ) ) {
			// Already created, nothing to do.
			return $path;
		}

		$wp_filesystem = $this->get_filesystem();

		$created_path = '';
		foreach ( explode( DIRECTORY_SEPARATOR, $path ) as $dir ) {
			$created_path .= DIRECTORY_SEPARATOR . $dir;
			if ( is_dir( $created_path ) ) {
				continue;
			}

			if ( ! $wp_filesystem->mkdir( $created_path ) ) {
				throw new \RuntimeException(
					'Could no create sub-directories for shipcloud storage.'
				);
			}
		}


		return $path;
	}

	/**
	 * Get filesystem adapter.
	 *
	 * @return WP_Filesystem_Base
	 * @throws \RuntimeException
	 */
	protected function get_filesystem() {
		global $wp_filesystem;

		if ( $wp_filesystem ) {
			// Aready connectec / instantiated, so we won't do it again.
			return $wp_filesystem;
		}

		if ( ! WP_Filesystem() ) {
			throw new \RuntimeException(
				'Can not access file system to download created shipping labels.'
			);
		}

		return $wp_filesystem;
	}
}

$wc_shipcloud_order_bulk = new WC_Shipcloud_Order_Bulk();
