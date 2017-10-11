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

	const FORM_BULK = 'wcsc_order_bulk_label';
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
		add_action( 'admin_print_footer_scripts', array( $this, 'attach_downloads' ) );

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

		if ( ! isset( $request['action'] )
		     || self::FORM_BULK !== $request['action']
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
		$actions['wcsc_order_bulk_label'] = __( 'Create shipping labels', 'shipcloud-for-woocommerce' );

		return $actions;
	}

	/**
	 * Adding Footer Scripts
	 *
	 * @since   1.2.1
	 */
	public function admin_print_footer_scripts() {

		if (
			false === get_current_screen() instanceof \WP_Screen
			|| 'edit-shop_order' !== get_current_screen()->id
		) {
			// Not the context for bulk action so we won't print the bulk template.
			return;
		}

		require_once WCSC_FOLDER . '/includes/shipcloud/block-order-labels-bulk.php';

		$block = new WooCommerce_Shipcloud_Block_Order_Labels_Bulk(
			WCSC_COMPONENTFOLDER . '/block/order-labels-bulk.php',
			WC_Shipcloud_Order::create_order( null ),
			_wcsc_carriers_get(),
			wcsc_api()
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
			array( 'jquery', 'wcsc-multi-select' )
		);

		wp_enqueue_script( 'wcsc_bulk_order_label', false, array(), false, true );
	}

	/**
     * Create multiple labels.
     *
	 * @param array $request
	 */
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

	/**
	 * Download the label PDF.
	 *
	 * @param int $order_id
	 * @param string $url URL to the label PDF as given by the API.
	 *
	 * @return string
	 */
	protected function save_label( $order_id, $url ) {
		$path = $this->get_storage_path( 'order' . DIRECTORY_SEPARATOR . $order_id )
		        . DIRECTORY_SEPARATOR . md5( $url ) . '.pdf';

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

	/**
	 * Get the URL to some Shipcloud files.
	 *
	 * @param null|string $suffix Path and name of the file.
	 *
	 * @return string
	 */
	protected function get_storage_url( $suffix = null ) {
		$wp_upload_dir = wp_upload_dir();
		$url           = $wp_upload_dir['baseurl'] . '/' . 'shipcloud-woocommerce';

		if ( null !== $suffix && $suffix ) {
			// Add suffix but disallow hopping in other path.
			$url .= '/' . str_replace( '..', '', $suffix );
		}

		return $url;
	}

	/**
	 * Add a new download for admin.
	 *
	 * @param $url
	 */
	public static function admin_download( $url ) {
		WooCommerce_Shipcloud::assert_session();

		$_SESSION['wscs']['downloads'][ md5( $url ) ] = $url;
	}

	/**
	 * Dispatch downloads to frontend.
	 */
	public function attach_downloads() {
		WooCommerce_Shipcloud::assert_session();

		if ( empty( $_SESSION['wscs'] ) ) {
		    // Way to late during runtime.
            // @todo This seems like a bug where the method is called way to late during shutdown.
			return;
		}

		foreach ( (array) $_SESSION['wscs']['downloads'] as $key => $download ) {
			?>
            <script type="application/javascript">
                (window.open('<?php echo $download ?>', '_blank')).focus();
            </script>
			<?php

			// Remove dispatched downloads.
			unset( $_SESSION['wscs']['downloads'][ $key ] );
		}
	}

	/**
	 * Ask API for labels and merge their PDF into one.
	 *
	 * @param $request
	 */
	protected function create_pdf( $request ) {
		if ( ! $request['post'] ) {
			// Nothing selected or no post given, so we don't have anything to do.
			return;
		}

		$pdf_basename = sha1( implode( ',', $request['post'] ) ) . '.pdf';
		$pdf_file     = $this->get_storage_path( 'labels' ) . DIRECTORY_SEPARATOR . $pdf_basename;
		$pdf_url      = $this->get_storage_url( 'labels' ) . '/' . $pdf_basename;

		$download_message = sprintf(
			'Labels can be downloaded using this URL: %s',
			'<a href="' . esc_attr( $pdf_url ) . '" target="_blank">' . esc_html( $pdf_url ) . '</a>'
		);

		WooCommerce_Shipcloud::load_fpdf();

		$pdf_count = 0;
		$m = new \iio\libmergepdf\Merger();
		foreach ( $request['post'] as $order_id ) {
			$current       = $this->create_label_for_order( $order_id, $request );
			$error_message = sprintf( 'Problem generating label for order #%d', $order_id );

			if ( ! $current || ! $current->getLabelUrl() ) {
				WooCommerce_Shipcloud::admin_notice( $error_message, 'error' );

				continue;
			}

			try {
				// Storing label.
				$path_to_pdf = $this->save_label( $order_id, $current->getLabelUrl() );
				$m->addFromFile( $path_to_pdf );
				$pdf_count++;
			} catch ( \RuntimeException $e ) {
				WooCommerce_Shipcloud::admin_notice( $error_message, 'error' );
			}
		}

		$content = '';
		if (0 !== $pdf_count) {
		    $content = $m->merge();
        }

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

		static::admin_download( $pdf_url );
		WooCommerce_Shipcloud::admin_notice( $download_message, 'updated' );
	}

	/**
	 * Ask API for a new label.
	 *
	 * @param $order_id
	 * @param $request
	 *
	 * @return array|\Shipcloud\Domain\Shipment
	 */
	protected function create_label_for_order( $order_id, $request ) {
		$order = WC_Shipcloud_Order::create_order( $order_id );

		$reference_number = sprintf(
			__( 'Order %s', 'shipcloud-for-woocommerce' ),
			$order->get_wc_order()->get_order_number()
		);

		$data = array(
			'to'                    => $order->get_recipient(),
			'from'                  => $order->get_sender(),
			'package'               => new \Shipcloud\Domain\Package(
				wc_format_decimal( $request['parcel_length'] ),
				wc_format_decimal( $request['parcel_width'] ),
				wc_format_decimal( $request['parcel_height'] ),
				wc_format_decimal( $request['parcel_weight'] ),
				$request['shipcloud_carrier_package']
			),
			'carrier'               => $request['shipcloud_carrier'],
			'service'               => $request['shipcloud_carrier_service'],
			'reference_number'      => $reference_number,
			'notification_email'     => $order->get_notification_email(),
			'create_shipping_label' => true,
		);

		if ( $order->get_wc_order() && wcsc_get_cod_id() === $order->get_wc_order()->get_payment_method() ) {
			$cash_on_delivery = new \Shipcloud\Domain\Services\CashOnDelivery(
				$order->get_wc_order()->get_total(),
				$order->get_wc_order()->get_currency(),
				$order->get_bank_information(),
				sprintf( __( 'WooCommerce OrderID: %s', 'shipcloud-for-woocommerce' ), $order_id )
			);

			if (!isset($data['additional_services'])) {
				$data['additional_services'] = array();
			}

			$data['additional_services'][] = array(
				'name' => \Shipcloud\Domain\Services\CashOnDelivery::NAME,
				'properties' => $cash_on_delivery->toArray()
			);
		}

		try {
			$shipment = _wcsc_api()->shipment()->create( array_filter( $data ) );

			$order->get_wc_order()->add_order_note( __( 'shipcloud.io label was created.', 'woocommerce-shipcloud' ) );

			WC_Shipcloud_Shipping::log( 'Order #' . $order->get_wc_order()->get_order_number() . ' - Created shipment successful (' . wcsc_get_carrier_display_name( $request['carrier'] ) . ')' );

			$parcel_title = wcsc_get_carrier_display_name( $request['shipcloud_carrier'] )
							. ' - '
							. $request['parcel_width']
							. __( 'x', 'woocommerce-shipcloud' )
							. $request['parcel_height']
							. __( 'x', 'woocommerce-shipcloud' )
							. $request['parcel_length']
							. __( 'cm', 'woocommerce-shipcloud' )
							. ' '
							. $request['parcel_weight']
							. __( 'kg', 'woocommerce-shipcloud' );

			$label_for_order = array(
				'id'                  => $shipment->getId(),
				'carrier_tracking_no' => $shipment->getCarrierTrackingNo(),
				'tracking_url'        => $shipment->getTrackingUrl(),
				'label_url'           => $shipment->getLabelUrl(),
				'price'               => $shipment->getPrice(),
				'parcel_id'           => $shipment->getId(),
				'parcel_title'        => $parcel_title,
				'carrier'             => $request['shipcloud_carrier'],
				'width'               => wc_format_decimal( $request['parcel_width'] ),
				'height'              => wc_format_decimal( $request['parcel_height'] ),
				'length'              => wc_format_decimal( $request['parcel_length'] ),
				'weight'              => wc_format_decimal( $request['parcel_weight'] ),
				'date_created'        => time(),
			);

			$label_for_order = array_merge( $label_for_order, $order->get_sender( 'sender_' ) );
			$label_for_order = array_merge( $label_for_order, $order->get_recipient( 'recipient_' ) );

			add_post_meta( $order_id, 'shipcloud_shipment_ids', $label_for_order['id'] );
			add_post_meta( $order_id, 'shipcloud_shipment_data', $label_for_order );
		} catch ( \Exception $e ) {
			$error_message = sprintf(
				__( 'No label for order #%d created: %s' ),
				$order_id,
				str_replace( "\n", ', ', $e->getMessage() )
			);

			WC_Shipcloud_Shipping::log( $error_message );
			WooCommerce_Shipcloud::admin_notice( $error_message, 'error' );

			return array();
		}

		return $shipment;
	}

	/**
	 * @param $request
	 *
	 * @return array
	 */
	public function get_package_data( $request ) {
		$package_data = array(
			'width'  => $request['wcsc_width'],
			'height' => $request['wcsc_height'],
			'length' => $request['wcsc_length'],
			'weight' => $request['wcsc_weight'],
			'type'   => 'parcel',
		);

		if ( isset( $request['wcsc_type'] ) ) {
			$package_data['type'] = $request['wcsc_type'];
		}

		return $package_data;
	}

	/**
	 * @param null $order_id
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function get_storage_path( $suffix = null ) {
		$wp_upload_dir = wp_upload_dir();
		$path          = $wp_upload_dir['basedir']
		                 . DIRECTORY_SEPARATOR . 'shipcloud-woocommerce';

		if ( null !== $suffix && $suffix ) {
			$path .= DIRECTORY_SEPARATOR . trim( $suffix, '\\/' );
		}

		if ( is_dir( $path ) ) {
			// Already created, nothing to do.
			return $path;
		}

		// Directory not present - we try to create it.
		if ( ! wp_mkdir_p( $path ) ) {
			throw new \RuntimeException(
				'Could no create sub-directories for shipcloud storage.'
			);
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
