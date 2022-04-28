<?php

/**
 * WC_Shipping_Shipcloud_Order_Bulk represents WooCommerce order bulk processing.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud_Order_Bulk' ) ) {
	
    class WC_Shipping_Shipcloud_Order_Bulk {
    	
		/**
         * Constructor
         *
         * @return void
         */
        public function __construct() {
            $this->init();
        }

        /**
         * Initialize Hooks
         *
         * @return void
         */
        private function init() {
			add_filter( 'bulk_actions-edit-shop_order', 		array( $this, 'add_bulk_actions' ) );
			add_filter( 'handle_bulk_actions-edit-shop_order',	array( $this, 'handle_bulk_actions' ), 9, 3 );
			
			add_action( 'admin_enqueue_scripts', 				array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_print_footer_scripts', 			array( $this, 'admin_print_footer_scripts' ) );
        }
		
		/**
		 * Adding bulk action to dropdown
		 *
		 * @param array $actions Bulk actions
		 * @return array $actions Bulk actions with own Actions
		 */
		public function add_bulk_actions( $actions ) {
			$actions['wcsc_order_bulk_label']			= __( 'Create shipping labels', 'shipcloud-for-woocommerce' );
			$actions['wcsc_order_bulk_pickup_request'] 	= __( 'Create pickup requests', 'shipcloud-for-woocommerce' );
			
			return $actions;
		}
		
		/**
		 * Handle bulk actions.
		 *
		 * @param  string $redirect_to URL to redirect to.
		 * @param  string $action      Action name.
		 * @param  array  $order_ids         List of ids.
		 * @return string
		 */
		public function handle_bulk_actions( $redirect_to, $action, $order_ids ) {
			if ( $action === 'wcsc_order_bulk_label' ) {
				$this->log( 'create bulk order labels for orders ' . implode( ', ', $order_ids ) );
				$this->create_pdf( $order_ids );
				
			} elseif ( $action === 'wcsc_order_bulk_pickup_request' ) {
				$this->log( 'create bulk order pickup request for orders ' . implode( ', ', $order_ids ) );
				$this->create_pickup_request( $order_ids );
				
			} else {
				$this->log( 
					sprintf( 
						__( 'Unknown bulk action called. Order IDs: %s', 'shipcloud-for-woocommerce' ), 
						json_encode( $order_ids ) 
					) 
				);
			}
			
			return $redirect_to;
		}
		
		public function admin_enqueue_scripts() {
			
			$screen       = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';
            
			if ( in_array( $screen_id, [ 'edit-shop_order' ] ) ) {
				
	            wp_enqueue_style( 'shipcloud-admin' );
				wp_enqueue_style( 'jquery-multiselect' );
				
				wp_register_script(
					'shipcloud_bulk_actions',
					WC_SHIPPING_SHIPCLOUD_JS_DIR . '/shipcloud-bulk-actions.js',
					array( 'jquery', 'shipcloud-multiselect' )
				);
				wp_enqueue_script( 'shipcloud_bulk_actions', false, array(), false, true );
				
				wp_enqueue_script( 'shipcloud-filler' );
	            wp_enqueue_script( 'shipcloud-shipments' );
				
				include_once( dirname( __FILE__ ) . '/data/data-shipcloud-multiselect-script.php' );
			}
		}
		
		/**
		 * Adding Footer Scripts
		 *
		 * @return void
		 */
		public function admin_print_footer_scripts() {

			if ( false === get_current_screen() instanceof \WP_Screen || 'edit-shop_order' !== get_current_screen()->id ) {
				// Not the context for bulk action so we won't print the bulk template.
				return;
			}
			
			$api				= WC_Shipping_Shipcloud_API_Adapter::get_instance();
			$shipcloud_carriers	= $api->get_carriers();

			require_once __DIR__ . '/class-wc-shipping-shipcloud-order-labels-bulk.php';
			$block = new WC_Shipping_Shipcloud_Order_Labels_Bulk(
				dirname( __FILE__ ) . '/templates/template-order-labels-bulk.php',
				WC_Shipping_Shipcloud_Order::get_instance()->create_order( null )
			);

			$block->dispatch();
			
			include( dirname( __FILE__ ) . '/templates/template-pickup-request-form.php' );
			include( dirname( __FILE__ ) . '/templates/template-bulk-actions.php' );
			
			$shipcloud_downloads = get_transient( 'shipcloud_downloads' );
			if ( is_array( $shipcloud_downloads ) && ! empty( $shipcloud_downloads ) ) {
				foreach ( (array) $shipcloud_downloads as $key => $download ) {
					?>
					<script type="application/javascript">
						(window.open('<?php echo $download ?>', '_blank')).focus();
					</script>
					<?php
				}
			}
			delete_transient( 'shipcloud_downloads' );
		}
		
		/*****************************************************************
         *
         *		UTILITIES
         *
         *****************************************************************/
		
		private function create_pdf( $order_ids ) {
			
			if ( empty( $order_ids ) ) {
				// Nothing selected or no post id given, so we don't have anything to do.
				return;
			}

			$pdf_basename 				= sha1( implode( ',', $order_ids ) ) . '.pdf';
			$shipping_label_count 		= $customs_declaration_count = 0;
			$shipping_label_merger 		= new \iio\libmergepdf\Merger();
			$customs_declaration_merger = new \iio\libmergepdf\Merger();

			foreach ( $order_ids as $order_id ) {
				$shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );

				if ( isset( $request['shipcloud_bulk_only_one_shipping_label'] ) ) {
					// check to see if there's already a shipping label present
					foreach ( $shipments as $shipment ) {
						if ( ! empty( $shipment['label_url'] ) ) {
							$this->log( sprintf( 'found shipment with label_url for order #%d - skipping', $order_id ) );
							$this->add_admin_notice(
								sprintf( 
									'Skipped label creation for order #%d, because there was already a shipping label.', 
									$order_id
								),
								'error' 
							);
							continue(2);
						}
					}
				}

				$order = WC_Shipping_Shipcloud_Order::get_instance()->create_order( $order_id );
				
				$data	 						= $_REQUEST;
				$data['action'] 				= 'shipcloud_create_shipment_label';
				$current 						= $order->create_shipment( $data );
				
				if ( is_wp_error( $current ) ) {
          $error_message = 'There was an error while trying to create the label for order #%d: %s';
					$this->log(
						sprintf( $error_message, $order_id, $current->get_error_message() )
					);
          $this->add_admin_notice( 
            sprintf( $error_message, $order_id, $current->get_error_message() ), 
            'error' 
          );
					continue;
				}
				
				try {
					// Storing label.
					$this->log('Trying to store shipping labels');
					$path_to_shipping_label = $this->save_pdf_to_storage(
						$order_id,
						$current['label_url'],
						'shipping_label'
					);
					$shipping_label_merger->addFile( $path_to_shipping_label );
					$shipping_label_count++;
					
				} catch ( \RuntimeException $e ) {
					$error_message = sprintf(
						__( 'Couldn’t save label for order #%d to disk: %s', 'shipcloud-for-woocommerce' ),
						$order_id,
						str_replace( "\n", ', ', $e->getMessage() )
					);
					$this->log( $error_message, 'error' );
					$this->add_admin_notice( $error_message, 'error' );

					continue;
				}
				
				$carrier_declaration_document_url = false;
		        if ( ! empty( $current['customs_declaration'] ) 
					&& array_key_exists( 'carrier_declaration_document_url', $current['customs_declaration'] ) ) {
		            $carrier_declaration_document_url = $current['customs_declaration']['carrier_declaration_document_url'];
		        }
				
				if ( ! empty( $carrier_declaration_document_url ) ) {
					// Storing customs declaration.
					try {
						$this->log( 'Trying to store customs declaration documents' );
						$path_to_customs_declaration = $this->save_pdf_to_storage(
							$order_id,
							$carrier_declaration_document_url,
							'customs_declaration'
						);
						$customs_declaration_merger->addFile( $path_to_customs_declaration );
						$customs_declaration_count++;
						
					} catch ( \RuntimeException $e ) {
						$error_message = sprintf(
							__( 'Couldn’t save customs declaration documents for order #%d to disk: %s', 'shipcloud-for-woocommerce' ),
							$order_id,
							str_replace( "\n", ', ', $e->getMessage() )
						);
						$this->log( $error_message, 'error' );
						$this->add_admin_notice( $error_message, 'error' );

						continue;
					}
				}
			}
			
			$this->log( 'Done looping through order ids and creating labels' );
			
			$wp_filesystem = $this->get_wp_filesystem();

			if ( $shipping_label_count > 0 ) {
				$this->log( 'Merging shipping labels' );

				$shipping_labels_pdf_content 	= '';
				
				$shipping_labels_pdf_file 		= $this->get_storage_path( 'labels' ) 
												. DIRECTORY_SEPARATOR 
												. 'merged_shipping_labels_'
												. $pdf_basename;
				
				$shipping_labels_pdf_url 		= $this->get_storage_url( 'labels' ) 
												. DIRECTORY_SEPARATOR 
												. 'merged_shipping_labels_' 
												. $pdf_basename;

				try {
					$shipping_labels_pdf_content = $shipping_label_merger->merge();
				} catch ( \Exception $e ) {
					$this->log( 'Couldn\'t merge shipping label pdf files: ' . $e->getMessage(), 'error' );
				}

				if ( ! $shipping_labels_pdf_content ) {
					$this->add_admin_notice( 
						__( 'Could not compose labels into one PDF.', 'shipcloud-for-woocommerce' ), 
						'error' 
					);
					return;
				}
				
				$wp_filesystem->put_contents( $shipping_labels_pdf_file, $shipping_labels_pdf_content );
				$this->add_admin_download( $shipping_labels_pdf_url );

				$download_message = sprintf(
					'Shipping labels can be downloaded using this URL: %s',
					'<a href="' . esc_attr( $shipping_labels_pdf_url ) . '" target="_blank">' . esc_html( $shipping_labels_pdf_url ) . '</a>'
				);
				$this->add_admin_notice( $download_message, 'updated' );
			}

			if ( $customs_declaration_count > 0 ) {
				$this->log( 'Merging customs declarations' );

				$customs_declarations_pdf_content 	= '';
				
				$customs_declarations_pdf_file 		= $this->get_storage_path( 'labels' )
													. DIRECTORY_SEPARATOR
													. 'merged_customs_declarations_'
													. $pdf_basename;
				
				$customs_declaration_pdf_url 		= $this->get_storage_url( 'labels' )
													. DIRECTORY_SEPARATOR
													. 'merged_customs_declarations_'
													. $pdf_basename;
				
				try {
					$customs_declaration_pdf_content = $customs_declaration_merger->merge();
				} catch ( \Exception $e ) {
					$this->log( 'Couldn\'t merge customs declaration documents pdf files: ' . $e->getMessage(), 'error' );
				}

				if ( ! $customs_declaration_pdf_content ) {
					$this->add_admin_notice( 
						__( 'Could not compose customs declaration documents into one PDF.', 'shipcloud-for-woocommerce' ), 
						'error' 
					);
					return;
				}

				$wp_filesystem->put_contents( $customs_declarations_pdf_file, $customs_declaration_pdf_content );
				$this->add_admin_download( $customs_declaration_pdf_url );

				$download_message = sprintf(
					'Customs declaration documents can be downloaded using this URL: %s',
					'<a href="' . esc_attr( $customs_declaration_pdf_url ) . '" target="_blank">' . esc_html( $customs_declaration_pdf_url ) . '</a>'
				);
				$this->add_admin_notice( $download_message, 'updated' );
			}
		}
		
		private function create_pickup_request( $order_ids ) {
			
			if ( empty( $order_ids ) ) {
				// Nothing selected or no post id given, so we don't have anything to do.
				return;
			}
			
			
			// $this->log( "REQUEST: " . json_encode( $_REQUEST ) );
			/*
			"pickup": {
				"pickup_earliest_date": "2022-01-28",
				"pickup_earliest_time_hour": "10",
				"pickup_earliest_time_minute": "00",
				"pickup_latest_time_hour": "19",
				"pickup_latest_time_minute": "00"
			},
			"pickup_address": {
				"company": "",
				"first_name": "",
				"last_name": "",
				"street": "",
				"street_no": "",
				"zip_code": "",
				"city": "",
				"country": "DE",
				"state": "",
				"phone": ""
			},
			*/
			
			$pickup_request_params = $mappings = [];
			
			foreach ( $order_ids as $order_id ) {
				$order		= WC_Shipping_Shipcloud_Order::get_instance()->create_order( $order_id );
				$shipments 	= get_post_meta( $order_id, 'shipcloud_shipment_data' );
				
				// process each created shipment
				foreach ( $shipments as $shipment ) {
	                $shipment_id 			= $shipment['id'];
	                $carrier 	 			= $shipment['carrier'];
					$mappings[$shipment_id] = $order_id;

	                if ( ! array_key_exists( 'pickup_request', $shipment ) ) {
	                    if ( ! array_key_exists( $carrier, $pickup_request_params ) ) {
	                        $pickup_request_params[$carrier] = [];
	                    }
	                    $pickup_request_params[$carrier][] = $shipment_id;
	                }
					else {
	                    $this->add_admin_notice( 
							sprintf( 
								__( 'Pickup for shipment %s already requested, nothing to do here.', 
								'shipcloud-for-woocommerce' ), 
								$shipment_id ), 
							'info' 
						);
	                }
	            }
			}
			
			// create pickup requests per carrier
			foreach ( $pickup_request_params as $carrier => $shipment_ids ) {
				$shipment_id_hashes = $carrier_order_ids = [];
	            foreach ( $shipment_ids as $shipment_id ) {
	                $shipment_id_hashes[] = [ 'id' => $shipment_id ];
					$order_id = $mappings[$shipment_id];
					if ( ! in_array( $order_id, $carrier_order_ids ) ) {
						$carrier_order_ids[] = $order_id;
					}
	            }
			
				$data = [
					'carrier'	=> $carrier,
	                'pickup'	=> $_REQUEST['pickup'],
	                'shipments'	=> $shipment_id_hashes,
	            ];
			
				$data['pickup']['pickup_latest_date'] = $data['pickup']['pickup_earliest_date'];

				$pickup_address = $_REQUEST['pickup_address'];
				if ( ! empty( $pickup_address['street'] ) && ! empty( $pickup_address['city'] ) ) {
					$data['pickup_address'] = $pickup_address;
				}
			
				$pickup_request = $order->create_pickup_request( $carrier_order_ids, $data );
				if ( is_wp_error( $pickup_request ) ) {
					$message = sprintf(
						__( 'Pickup request failed. Carrier %s (Shipment-IDs: %s), Error: %s', 'shipcloud-for-woocommerce' ),
						$carrier,
						implode( ', ', $shipment_ids ),
						$pickup_request->get_error_message()
					);
					$this->log( $message, 'error' );
					$this->add_admin_notice( $message, 'error' );
				}
				else if ( ! $pickup_request ) {
					$message = sprintf(
						__( 'Error while creating a pickup request for orders %s', 'shipcloud-for-woocommerce' ),
						implode( ',', $carrier_order_ids ) 
					);
					$message = sprintf(
						__( 'Pickup request failed. Carrier %s (Shipment-IDs: %s), please refer to log file.', 'shipcloud-for-woocommerce' ),
						$carrier,
						implode( ', ', $shipment_ids )
					);
					$this->log( $message, 'error' );
					$this->add_admin_notice( $message, 'error' );
				}
				else {
					$message = sprintf(
						__( 'Pickup request for orders %s successfully created', 'shipcloud-for-woocommerce' ),
						implode( ',', $carrier_order_ids ) 
					);
					$this->log( $message );
					$this->add_admin_notice( $message, 'success' );
				}
			}
			
		}
		
	    /**
	     * Add a new download for admin.
	     *
	     * @param $url
		 * @return void
	     */
	    private function add_admin_download( $url ) {
			$shipcloud_downloads = get_transient( 'shipcloud_downloads' );
			if ( empty( $shipcloud_downloads ) ) {
				$shipcloud_downloads = [];
			}

			$shipcloud_downloads[ md5( $url ) ] = $url;
			set_transient( 'shipcloud_downloads', $shipcloud_downloads );
		}
		
		/**
		 * Download the label PDF.
		 *
		 * @param int $order_id
		 * @param string $url URL to the PDF as given by the API.
	     * @param string $prefix prefix that will be added to the filename
		 *
		 * @return string
		 */
		private function save_pdf_to_storage( $order_id, $url, $prefix ) {
			$path = $this->get_storage_path( 'order' . DIRECTORY_SEPARATOR . $order_id ) 
					. DIRECTORY_SEPARATOR . $prefix . md5( $url ) . '.pdf';

			if ( file_exists( $path ) ) {
				$this->log('pdf file already exists');
				// Might be already downloaded, so we won't overwrite it.
				return $path;
			}

			$pdf_content = wp_remote_retrieve_body( wp_remote_get( $url ) );

			if ( ! $pdf_content ) {
				$this->log('Couldn\'t download pdf');
				// No content, so we refuse to continue.
				throw new \RuntimeException( 'Could not download PDF - no content delivered.' );
			}

			if ( ! $this->get_wp_filesystem()->put_contents( $path, $pdf_content ) ) {
				$this->log('Couldn\'t store downloaded PDF contents.');
				throw new \RuntimeException( 'Could not store downloaded PDF contents.' );
			}

			return $path;
		}
		
		/**
		 * Get the PATH to shipcloud files.
		 * 
		 * @param null $order_id
		 * @return string
		 * @throws \RuntimeException
		 */
		private function get_storage_path( $suffix = null ) {
			$wp_upload_dir = wp_upload_dir();
			$path          = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'shipcloud-woocommerce';

			if ( null !== $suffix && $suffix ) {
				$path .= DIRECTORY_SEPARATOR . trim( $suffix, '\\/' );
			}

			if ( is_dir( $path ) ) {
				// Already created, nothing to do.
				return $path;
			}

			// Directory not present - we try to create it.
			if ( ! wp_mkdir_p( $path ) ) {
				$this->log( 'Couldn\'t create sub-directories for shipcloud storage.', 'error' );
				throw new \RuntimeException( 'Couldn\'t create sub-directories for shipcloud storage.' );
			}

			return $path;
		}

		/**
		 * Get the URL to some Shipcloud files.
		 *
		 * @param null|string $suffix Path and name of the file.
		 * @return string
		 */
		private function get_storage_url( $suffix = null ) {
			$wp_upload_dir = wp_upload_dir();
			$url           = $wp_upload_dir['baseurl'] . '/' . 'shipcloud-woocommerce';

			if ( null !== $suffix && $suffix ) {
				// Add suffix but disallow hopping in other path.
				$url .= '/' . str_replace( '..', '', $suffix );
			}

			return $url;
		}
		
		/**
		 * Get wp filesystem adapter.
		 *
		 * @return WP_Filesystem_Base
		 * @throws \RuntimeException
		 */
		private function get_wp_filesystem() {
			global $wp_filesystem;

			if ( $wp_filesystem ) {
				// Aready connectec / instantiated, so we won't do it again.
				return $wp_filesystem;
			}

			if ( ! WP_Filesystem() ) {
				$this->log( 'Can\'t access file system to download created shipping labels.', 'error' );
				throw new \RuntimeException( 'Can\'t access file system to download created shipping labels.' );
			}

			return $wp_filesystem;
		}

        /**
         * Getting option (overwrite instance values if there option of instance is empty
         *
         * @param string $key
         * @param null   $empty_value
         * @return mixed|string
         */
        private function get_option( string $key, $empty_value = null ) {
            return WC_Shipping_Shipcloud_Utils::get_option( $key, $empty_value );
        }

        /**
         * Output an admin notice.
         *
         * @param string 	$message 		Debug message.
         * @param string 	$type    		Message type.
         * @param bool 		$dismissible    Message type.
         * @return void
         */
        private function add_admin_notice( $message, $type = 'info', $dismissible = true ) {
            WC_Shipping_Shipcloud_Utils::add_admin_notice( $message, $type, $dismissible );
        }

		/**
		 * Output a debug message.
		 *
		 * @param string 	$message 	Debug message.
		 * @param string 	$level   	Debug level.
         * @param mixed 	$context	The Debug context.
		 * @return void
		 */
        private function log( $message, $level = 'info', $context = [] ) {
            WC_Shipping_Shipcloud_Utils::log( $message, $level, $context );
        }
    }

    new WC_Shipping_Shipcloud_Order_Bulk();
}
