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
    const FORM_PICKUP_REQUEST = 'shipcloud_create_pickup_request';
	const BUTTON_PDF = 'wscs_order_bulk_pdf';
    const BUTTON_PICKUP_REQUEST = 'shipcloud_order_bulk_pickup_request';

	/**
	 * WC_Shipcloud_Order_Bulk constructor.
	 *
	 * @since   1.2.1
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
     * Backward compatibility to WC2.
     *
	 * The current WC3 has almost anything covered by getter methods
	 * while the old WC2 used simple fields for that.
	 * This layer allows using the old syntax
	 * and makes it compatible with the new one.
     *
	 * @param $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return property_exists( '\\WC_Order', $name ) || method_exists( $this->get_wc_order(), 'get_' . $name );
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

        if (isset( $request['action'])) {
            $action = $request['action'];
        } elseif (isset( $request['action2'])) {
            $action = $request['action2'];
        }

        if (
            !isset($action) ||
            ($action !== self::FORM_BULK && $action !== self::FORM_PICKUP_REQUEST)
        ) {
            return;
        }

        if ( isset( $request[ self::BUTTON_PDF ] ) || self::FORM_BULK == $request['action'] ) {
            $this->create_pdf( $request );
            $this->create_label( $request );
            return;
        } elseif ( isset( $request[ self::BUTTON_PICKUP_REQUEST ] ) || self::FORM_PICKUP_REQUEST == $request['action'] ) {
            $this->create_pickup_request( $request );
            return;
        }

        WC_Shipcloud_Shipping::log(sprintf( __( 'Unknown bulk action called. Request: %s', 'shipcloud-for-woocommerce' ), $request ));
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

        // only applicable for WooCommerce 3
        if (class_exists('WC_DateTime')) {
            $actions[self::FORM_PICKUP_REQUEST] = __( 'Create pickup request', 'shipcloud-for-woocommerce' );
        }

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

        require WCSC_COMPONENTFOLDER . '/block/pickup-request-form.php';
        require WCSC_COMPONENTFOLDER . '/block/bulk-action-template.php';
	}

	/**
	 * Loading Scripts
	 *
	 * @since   1.2.1
	 */
	public function load_edit() {
        wp_register_script(
            'shipcloud_bulk_actions',
            WCSC_URLPATH . '/includes/js/bulk-actions.js',
            array( 'jquery', 'wcsc-multi-select' )
        );
        wp_enqueue_script( 'shipcloud_bulk_actions', false, array(), false, true );
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
			WC_Shipcloud_Shipping::log('pdf file already exists');
			// Might be already downloaded, so we won't overwrite it.
			return $path;
		}

		$pdf_content = wp_remote_retrieve_body( wp_remote_get( $url ) );

		if ( ! $pdf_content ) {
			WC_Shipcloud_Shipping::log('Couldn\'t download pdf');
			// No content, so we refuse to continue.
			throw new \RuntimeException( 'Could not download PDF - no content delivered.' );
		}

		if ( ! $this->get_filesystem()->put_contents( $path, $pdf_content ) ) {
			WC_Shipcloud_Shipping::log('Couldn\'t store downloaded PDF contents.');
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
		if (shipcloud_admin_is_on_order_overview_page()) {
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
				WC_Shipcloud_Shipping::log($error_message);
				continue;
			}

			try {
				// Storing label.
				WC_Shipcloud_Shipping::log('Trying to store the label');
				$path_to_pdf = $this->save_label( $order_id, $current->getLabelUrl() );
				$m->addFromFile( $path_to_pdf );
				$pdf_count++;
			} catch ( \RuntimeException $e ) {
				WooCommerce_Shipcloud::admin_notice( $error_message, 'error' );
				WC_Shipcloud_Shipping::log('RuntimeException: '.print_r($e, true));
			}
		}

		$content = '';
		if (0 !== $pdf_count) {
			try {
				$content = $m->merge();
			} catch (\Exception $e) {
				WC_Shipcloud_Shipping::log('Couldn\'t merge pdf files.');
				WC_Shipcloud_Shipping::log(print_r($e, true));
			}
		}

		if ( ! $content ) {
			WooCommerce_Shipcloud::admin_notice( __( 'Could not compose labels into one PDF.', 'shipcloud-for-woocommerce' ), 'error' );

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
        $request = $order->sanitize_reference_number($request);

		$use_calculated_weight = isset($request['shipcloud_use_calculated_weight']) ? $request['shipcloud_use_calculated_weight'] : '';
		if ( $use_calculated_weight == 'use_calculated_weight' ) {
			$request['parcel_weight'] = $order->get_calculated_weight();
		}
		$package = new \Shipcloud\Domain\Package(
			wc_format_decimal( $request['parcel_length'] ),
			wc_format_decimal( $request['parcel_width'] ),
			wc_format_decimal( $request['parcel_height'] ),
			wc_format_decimal( $request['parcel_weight'] ),
			$request['shipcloud_carrier_package']
		);

        $shipment_repo = _wcsc_container()->get( '\\Shipcloud\\Repository\\ShipmentRepository' );

        if (!empty($request['shipment']['additional_services']['cash_on_delivery']['currency'])) {
            $currency = $request['shipment']['additional_services']['cash_on_delivery']['currency'];
        } elseif (method_exists($order, 'get_currency')) {
            $currency = $order->get_currency();
        } elseif (method_exists($order->get_wc_order(), 'get_currency')) {
            $currency = $order->get_wc_order()->get_currency();
        } else {
            $currency = $order->get_wc_order()->get_order_currency();
        }

        $reference_number = '';
        if (!empty($request['shipment']['additional_services']['cash_on_delivery']['reference1'])) {
            $reference_number = $request['shipment']['additional_services']['cash_on_delivery']['reference1'];
        } elseif (!empty($request['reference_number'])) {
            $reference_number = $request['reference_number'];
        }

        $bank_information = new \Shipcloud\Domain\ValueObject\BankInformation(
            $request['shipment']['additional_services']['cash_on_delivery']['bank_name'],
            $request['shipment']['additional_services']['cash_on_delivery']['bank_code'],
            $request['shipment']['additional_services']['cash_on_delivery']['bank_account_holder'],
            $request['shipment']['additional_services']['cash_on_delivery']['bank_account_number']
        );

        $additional_services = $shipment_repo->additional_services_from_request(
            $request['shipment']['additional_services'],
            $order->get_wc_order()->get_total(),
            $currency,
            $bank_information,
            $reference_number,
            $request['shipcloud_carrier']
        );

		$data = array(
			'to'                    => $order->get_recipient(),
			'from'                  => $order->get_sender(),
			'package'               => $package,
			'carrier'               => $request['shipcloud_carrier'],
			'service'               => $request['shipcloud_carrier_service'],
			'reference_number'      => $reference_number,
			'description'           => $request['other_description'],
			'notification_email'    => $order->get_notification_email(),
			'additional_services'   => $additional_services,
			'create_shipping_label' => true,
		);

        $pickup = WC_Shipcloud_Order::handle_pickup_request($request);
        if (!empty($pickup)) {
            $data['pickup'] = $pickup;
        }

		try {
			WC_Shipcloud_Shipping::log('calling shipcloud api to create label with the following data: '.json_encode($data));
			$shipment = _wcsc_api()->shipment()->create( array_filter( $data ) );

			$order->get_wc_order()->add_order_note( __( 'shipcloud.io label was created.', 'woocommerce-shipcloud' ) );

			WC_Shipcloud_Shipping::log( 'Order #' . $order->get_wc_order()->get_order_number() . ' - Created shipment successful (' . wcsc_get_carrier_display_name( $request['shipcloud_carrier'] ) . ')' );

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
				'additional_services' => $additional_services,
				'date_created'        => time(),
			);

            if (!empty($pickup)) {
                $label_for_order['pickup'] = $pickup;
            }

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
			WC_Shipcloud_Shipping::log(print_r($e, true));
			WooCommerce_Shipcloud::admin_notice( $error_message, 'error' );

			return array();
		}

		return $shipment;
	}

    /*
     * Create pickup request at shipcloud
     *
     * @since 1.9.0
     *
     * @param $request
     */
    protected function create_pickup_request($request) {
        $pickup_time = WC_Shipcloud_Order::handle_pickup_request($request);
        $pickup_time = array_shift($pickup_time);
        $pickup_request_params = array();

        foreach ( $request['post'] as $order_id ) {
            $order = WC_Shipcloud_Order::create_order( $order_id );
            $shipments = get_post_meta( $order->ID, 'shipcloud_shipment_data' );
            foreach ( $shipments as $shipment ) {
                $shipment_id = $shipment['id'];
                $carrier = $shipment['carrier'];

                if ( !array_key_exists('pickup_request', $shipment) ) {
                    if ( !array_key_exists($carrier, $pickup_request_params) ) {
                        $pickup_request_params[$carrier] = array();
                    }
                    array_push($pickup_request_params[$carrier], $shipment_id);
                } else {
                    WooCommerce_Shipcloud::admin_notice( sprintf( __( 'No pickup request for shipment with id %s created, because there was already one', 'shipcloud-for-woocommerce' ), $shipment_id ), 'error' );
                }
            }
        }

        foreach ( $pickup_request_params as $carrier => $shipment_ids) {
            $shipment_id_hashes = array();
            foreach ( $shipment_ids as $shipment_id ) {
                array_push($shipment_id_hashes, array(
                    'id' => $shipment_id
                ));
            }

            $data = array(
                'carrier' => $carrier,
                'pickup_time' => $pickup_time,
                'shipments' => $shipment_id_hashes,
            );

            $pickup_address = array_filter($request['pickup_address']);
            // check to see if there was anything more send than the country code
            if ( count($pickup_address) > 1 ) {
                $data['pickup_address'] = $pickup_address;
            }

            $pickup_request = _wcsc_container()->get( '\\Woocommerce_Shipcloud_API' )->create_pickup_request($data);
            if ( is_wp_error( $pickup_request ) ) {
                WC_Shipcloud_Shipping::log( sprintf( __( 'Error while creating the pickup request: %s', 'shipcloud-for-woocommerce' ), $pickup_request->get_error_message() ) );
                WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Error while creating the pickup request: %s', 'shipcloud-for-woocommerce' ), $pickup_request->get_error_message() ), 'error' );
            } else {
                WC_Shipcloud_Shipping::log( sprintf( __( 'Pickup request created with id %s', 'shipcloud-for-woocommerce' ), $pickup_request['id']) );
                WooCommerce_Shipcloud::admin_notice( __( 'Pickup requests created', 'shipcloud-for-woocommerce') );

                // let's update the shipment_data with the pickup requests
                foreach ( $request['post'] as $order_id ) {
                    $order = WC_Shipcloud_Order::create_order( $order_id );
                    $shipments = get_post_meta( $order->ID, 'shipcloud_shipment_data' );

                    // remove shipments element from pickup_request
                    unset($pickup_request['shipments']);

                    foreach ( $shipments as $shipment ) {
                        if (in_array($shipment['id'], $shipment_ids)) {
                            $new_data = array_merge(
                                $shipment,
                                array(
                                    'pickup_request' => $pickup_request
                                )
                            );
                            update_post_meta( $order->ID, 'shipcloud_shipment_data', $new_data, $shipment );
                        }
                    }
                }
            }
        }
    }

	/**
	 * Sanitize package data.
	 *
	 * User enter package data that can:
	 *
	 * - Have local decimal separator.
	 *
	 * @since 1.5.1
	 *
	 * @param array $package_data
	 *
	 * @return array
	 */
	protected function sanitize_package( $package_data ) {
		$package_data['width']  = wc_format_decimal( $package_data['width'] );
		$package_data['height'] = wc_format_decimal( $package_data['height'] );
		$package_data['length'] = wc_format_decimal( $package_data['length'] );
		$package_data['weight'] = wc_format_decimal( $package_data['weight'] );

		return $package_data;
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
			WC_Shipcloud_Shipping::log('Couldn\'t create sub-directories for shipcloud storage.');
			throw new \RuntimeException(
				'Couldn\'t create sub-directories for shipcloud storage.'
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
			WC_Shipcloud_Shipping::log('Can\'t access file system to download created shipping labels.');
			throw new \RuntimeException(
				'Can\'t access file system to download created shipping labels.'
			);
		}

		return $wp_filesystem;
	}

	/*
	 * Check to see if it's a return shipment
	 *
	 * @return array
	 */
	 protected function handle_return_shipments( $order, $data ) {
		if ( 'returns' == $data['shipcloud_carrier_service'] ) {
			WC_Shipcloud_Shipping::log('Detected returns shipment. Switching from and to entries.');
			$from = $order->get_recipient();
			$to = $order->get_sender();
		} else {
			$to = $order->get_recipient();
			$from = $order->get_sender();
		}

		$adresses = array(
			'from' => $from,
			'to' => $to,
		);

		return array_filter( $adresses );
	}
}

$wc_shipcloud_order_bulk = new WC_Shipcloud_Order_Bulk();
