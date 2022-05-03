<?php

/**
 * WC_Shipping_Shipcloud_Order represents an enhanced WooCommerce order.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud_Order' ) ) {
	
    class WC_Shipping_Shipcloud_Order {
    	
		/**
		 * API Adapter
		 *
		 * @var WC_Shipping_Shipcloud_API_Adapter
		 */
	    private $api;

		/**
		 * Loaded Options
		 *
		 * @var array
		 */
        private $options;

		/**
		 * Loaded Carriers
		 *
		 * @var array
		 */
        private $carriers;

		/**
		 * Filtered Carriers
		 *
		 * @var array
		 */
        private $allowed_carriers;

        /**
         * The Single instance of the class
         *
         * @var $_instance
         */
        protected static $_instance = null;

        /**
         * Order ID
         *
         * @var $order_id
         */
        protected $order_id;

        /**
         * WooCommerce Order object
         *
         * @var WC_Order
         */
        private $wc_order = null;

        /**
         * Constructor
         *
         * @param null|mixed $order_id
         * @return void
         */
        private function __construct( $order_id = null ) {
            $this->order_id = $order_id;
            $this->init();
        }

        /**
         * Initialize Hooks
         *
         * @return void
         */
        private function init() {
            
			add_action( 'admin_enqueue_scripts', 						array( $this, 'admin_enqueue_scripts' ), 30 );
			add_action( 'add_meta_boxes', 								array( $this, 'add_metaboxes' ) );
            add_action( 'save_post', 									array( $this, 'save_settings' ) );
			
            add_action( 'woocommerce_order_details_before_order_table', array( $this, 'display_tracking_information' ), 10, 1 );
			
			add_action( 'wp_ajax_shipcloud_calculate_shipping', 		array( $this, 'ajax_calculate_shipping' ) );
			
            add_action( 'wp_ajax_shipcloud_create_shipment', 			array( $this, 'ajax_create_shipment' ) );
            add_action( 'wp_ajax_shipcloud_create_shipment_label', 		array( $this, 'ajax_create_shipment' ) );
			
			add_action( 'wp_ajax_shipcloud_update_shipment',			array( $this, 'ajax_update_shipment' ) ); 
			
            add_action( 'wp_ajax_shipcloud_delete_shipment', 			array( $this, 'ajax_delete_shipment' ) );
            add_action( 'wp_ajax_shipcloud_force_delete_shipment', 		array( $this, 'ajax_force_delete_shipment' ) );
			
            add_action( 'wp_ajax_shipcloud_create_pickup_request', 		array( $this, 'ajax_create_pickup_request' ) );
			
            add_action( 'wp_ajax_shipcloud_get_pakadoo_point', 			array( $this, 'ajax_get_pakadoo_point' ) );
            add_action( 'wp_ajax_nopriv_shipcloud_get_pakadoo_point', 	array( $this, 'ajax_get_pakadoo_point' ) );
			
			add_filter( 'woocommerce_billing_fields', 					array( $this, 'add_care_of_as_billing_input_field' ) );
			add_filter( 'woocommerce_shipping_fields', 					array( $this, 'add_care_of_as_shipping_input_field' ) );
			
			add_filter( 'woocommerce_shipping_fields', 					array( $this, 'add_pakadoo_id_input_field' ) );
			add_filter( 'woocommerce_shipping_fields', 					array( $this, 'add_sender_phone_input_field' ) );
			
			add_action( 'woocommerce_review_order_before_submit', 		array( $this, 'add_legal_checkboxes' ) );
			add_action( 'woocommerce_checkout_update_order_meta', 		array( $this, 'handle_legal_checkboxes_update' ) );
			
			$this->options			= WC_Shipping_Shipcloud_Utils::get_plugin_options();
            $this->api				= WC_Shipping_Shipcloud_API_Adapter::get_instance();

            $this->carriers			= $this->api->get_carrier_list();
            $this->allowed_carriers	= !empty( $this->options ) ? $this->options['allowed_carriers'] : [];
			
        }
		
		/*****************************************************************
         *
         *		GENERAL
         *
         *****************************************************************/

        /**
         * Main Instance
         *
         * @return object
         */
        public static function get_instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
		
        /**
         * Factory to create or load an order.
         *
         * @param int $order_id ID of the order as chosen by WooCommerce and found in the database.
         * @return WC_Shipping_Shipcloud_Order
         */
        public function create_order( $order_id ) {
            return new self( $order_id );
        }
		
        /**
         * Enqueuing needed Scripts & Styles
         *
         * @return void
         */
        public function admin_enqueue_scripts() {
			
			$screen       = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';
			
			if ( $screen_id === 'shop_order' ) {
				
	            // CSS
	            wp_enqueue_style( 'wp-jquery-ui-dialog' );
				wp_enqueue_style( 'shipcloud-admin' );
				wp_enqueue_style( 'jquery-multiselect' );
				wp_enqueue_style( 'shipcloud-fa' );
			
	            // JS
	            wp_enqueue_script( 'jquery' );
	            wp_enqueue_script( 'jquery-ui-core' );
	            wp_enqueue_script( 'jquery-effects-core' );
	            wp_enqueue_script( 'jquery-effects-highlight' );
	            wp_enqueue_script( 'jquery-ui-dialog' );
	            wp_enqueue_script( 'admin-widgets' );
				wp_enqueue_script( 'backbone' );
	            wp_enqueue_script( 'shipcloud-label-form' );
	            wp_enqueue_script( 'shipcloud-filler' );
	            wp_enqueue_script( 'shipcloud-shipments' );
				wp_enqueue_script( 'jquery-multiselect' );
				wp_enqueue_script( 'shipcloud-admin' );
				wp_enqueue_script( 'shipcloud-fa' );
				
				include_once( dirname( __FILE__ ) . '/data/data-shipcloud-multiselect-script.php' );
			}
        }
		
		
		/*****************************************************************
         *
         *		Order
         *
         *****************************************************************/
		
		
		/**
         * Adding meta boxes
         *
         * @return void
         */
        public function add_metaboxes() {
            add_meta_box(
                'shipcloud-io',
                __( 'shipcloud Shipping Center', 'shipcloud-for-woocommerce' ),
                array( $this, 'create_shipment_center' ),
                'shop_order'
           );
        }
		
        /**
         * Product metabox
		 * 
         * @return string
         */
        public function create_shipment_center() {
            global $post, $woocommerce;

            $this->order_id = $post->ID;

            wp_nonce_field( plugin_basename( __FILE__ ), 'save_settings' );

            $addresses = $this->get_addresses();
            extract( $addresses );

            $order 				= $this->get_wc_order( $this->order_id );
			$order_status 		= $order->get_status();
			$parcel_templates 	= $this->get_parcel_templates();
            $carriers 			= $this->get_allowed_carriers();
			
			$shipping_method	= $order->get_shipping_method();
			$shipping_method 	= WC_Shipping_Shipcloud_Utils::get_carrier_name_by_display_name( $shipping_method );
			$shipping_method 	= WC_Shipping_Shipcloud_Utils::disassemble_carrier_name( $shipping_method );
			
			include( dirname( __FILE__ ) . '/templates/template-order-shipment-center.php' );
        }
		
        /**
         * Saving product metabox
         *
         * @param int $post_id
         * @return void
         */
        public function save_settings( $post_id ) {
            // Interrupt on autosave or invalid nonce
            if (( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
                 || ! isset( $_POST['save_settings'] )
                 || ! wp_verify_nonce($_POST['save_settings'], plugin_basename( __FILE__ ) )
           ) {
                return;
            }

            // Check permissions to edit products
            if ( 'shop_order' === $_POST['post_type'] && ! current_user_can( 'edit_product', $post_id ) ) {
                return;
            }

            if ( isset( $_POST['sender_address'] ) ) {
                update_post_meta( $post_id, 'shipcloud_sender_address', $_POST['sender_address'] );
            }

            if ( isset( $_POST['recipient_address'] ) ) {
                update_post_meta( $post_id, 'shipcloud_recipient_address', $_POST['recipient_address'] );
            }
        }
		
		
		/*****************************************************************
         *
         *		Checkout
         *
         *****************************************************************/
		
		
		/**
		 * Adds care of billing input field
         * 
         * @param array $data
         * @return array
		 */
		public function add_care_of_as_billing_input_field( $data ) {
			return $this->add_care_of_input_field( $data, 'billing' );
		}
		
		/**
		 * Adds care of shipping input field
         * 
         * @param array $data
         * @return array
		 */
		public function add_care_of_as_shipping_input_field( $data ) {
			return $this->add_care_of_input_field( $data, 'shipping' );
		}
		
		/**
		 * Adds care of input field
         * 
         * @param array $data
         * @return array
		 */
		private function add_care_of_input_field( $data, $category ) {
			$options = get_option( 'woocommerce_shipcloud_settings' );
			if ( array_key_exists( 'show_recipient_care_of', $options ) ) {
		        if ( 'in_'.$category === $options['show_recipient_care_of'] ||
		             'both' === $options['show_recipient_care_of']) {
		            $pos = array_search( $category.'_country', array_keys( $data ) );

		            $final = array_slice( $data, 0, $pos );
		            $final[$category.'_care_of'] = array(
		                'label'       => __( 'Care of', 'shipcloud-for-woocommerce' ),
		                'description' => '',
		                'class'       => array( 'form-row-wide' ),
		                'clear'       => true,
		            );

		            $data = $final + array_slice( $data, $pos );
		        }
		    }

		    return $data;
		}
		
		/**
		 * Adds pakadoo id shipping input field
         * 
         * @param array $data
         * @return array
		 */
		public function add_pakadoo_id_input_field( $data ) {
		    $options = get_option( 'woocommerce_shipcloud_settings' );
		    if ( !array_key_exists( 'show_pakadoo', $options ) || 'yes' === $options['show_pakadoo'] ) {
		        $shipping_pakadoo_id = array(
		            'shipping_pakadoo_id' => array(
		                'label'       => __( 'pakadoo id', 'shipcloud-for-woocommerce' ),
		                // 'description' => __( 'Enter your pakadoo id to ship directly to a pakadoo point', 'shipcloud-for-woocommerce' ),
		                'class'       => array( 'form-row-wide' ),
		                'clear'       => true,
		            )
		        );
		        $shipping_pakadoo_address_id = array(
		            'shipping_pakadoo_address_id' => array(
		                'type'		=> 'hidden',
		                'clear'		=> true,
						'id'		=> 'shipping_pakadoo_address_id',
		            )
		        );
		        $data = array_merge( $shipping_pakadoo_id, $shipping_pakadoo_address_id, $data );
		    }

		    return $data;
		}
		
		/**
		 * Adds sender phone as shipping input field
         * 
         * @param array $data
         * @return array
		 */
		public function add_sender_phone_input_field( $data ) {
		    $options = get_option( 'woocommerce_shipcloud_settings' );
			if ( !array_key_exists( 'show_recipient_phone', $options ) || 'yes' === $options['show_recipient_phone'] ) {
		        $pos = array_search( 'shipping_city', array_keys( $data ), true ) + 2;
				$final = array_slice( $data, 0, $pos );
		        $final['shipping_phone'] = array(
		            'label'       => _x( 'Phone', 'Frontend label for entering the phone number', 'shipcloud-for-woocommerce' ),
		            'description' => '',
		            'class'       => array( 'form-row-wide' ),
		            'clear'       => true,
		        );

		        $data = $final + array_slice( $data, $pos );
		    }

		    return $data;
		}
		
        /**
         * Add legal checkboxes to checkout page
         *
         * @return void
         */
		public function add_legal_checkboxes() {
			woocommerce_form_field( 'checkbox_parcel_delivery_notification', array(
				'type'          => 'checkbox',
				'class'         => array( 'form-row shipcloud-checkbox' ),
				'label_class'   => array( 'woocommerce-form__label woocommerce-form__label-for-checkbox checkbox' ),
				'input_class'   => array( 'woocommerce-form__input woocommerce-form__input-checkbox input-checkbox' ),
				'required'      => false, // Mandatory or Optional
				'label'         => __( 'Yes, I would like to be reminded via E-mail about parcel delivery. Your E-mail Address will only be transferred to our parcel service provider for that particular reason.', 'shipcloud-for-woocommerce' ),
				'priority'		=> 5,
			));
		}
		
		/**
		 * Handle checkbox updates
         * 
         * @param int $order_id
         * @return void
		 */
		public function handle_legal_checkboxes_update( $order_id ) {
			if ( ! empty( $_POST['checkbox_parcel_delivery_notification'] ) ) {
				update_post_meta( 
					$order_id, 
					'shipcloud_parcel_delivery_notification', 
					sanitize_text_field( $_POST['checkbox_parcel_delivery_notification'] ) 
				);
			}
		}
		
		
		/*****************************************************************
         *
         *		My Account
         *
         *****************************************************************/
		
		
        /**
         * Show tracking information at my account page
         *
         * @param WC_Order $order
         * @return void
         */
        public function display_tracking_information( $order ) {
            $show_tracking_in_my_account = $this->get_option( 'show_tracking_in_my_account' );
            if ( $show_tracking_in_my_account === 'yes' ) {
                $this->order_id = $order->get_id();
                $shipment_ids 	= get_post_meta( $order->get_id(), 'shipcloud_shipment_ids' );
                $shipments_data = $shipment_data = get_post_meta( $order->get_id(), 'shipcloud_shipment_data' );
				
				ob_start();
	            
				include( dirname( __FILE__ ) . '/templates/template-my-account-show-tracking.php' );
				
                echo ob_get_clean();
            }
        }
		
		
		/*****************************************************************
         *
         *		AJAX
         *
         *****************************************************************/


        /**
         * Calulating shipping after submitting calculation
         *
         * @return json
         */
        public function ajax_calculate_shipping() {
            
            if ( array_key_exists( 'package', $_POST ) ) {
                $package = $this->sanitize_package( $_POST['package'] );
            }

            $price = $this->api->get_price( 
				$_POST['carrier'],
                $_POST['from'],
                $_POST['to'],
                $package,
                $_POST['service'] 
			);

            if ( is_wp_error( $price ) ) {
                $this->log( 'Could not calculate shipping - ' . $price->get_error_message(), 'error' );

				wp_send_json_error(
	                array(
	                    'status' => $price->get_error_code(),
	                    'data' 	 => sprintf(
	                        __( 'Error while optaining shipment quote: %s', 'shipcloud-for-woocommerce' ),
	                        $price->get_error_message()
	                   )
	               )
	           );
			   exit();
            }

            $price_html 	= wc_price( $price, array( 'currency' => get_woocommerce_currency() ) );
			$price_notice 	= sprintf( __( 'The calculated price is %s.', 'shipcloud-for-woocommerce'), $price_html );
            $html       	= '<div class="notice">' . $price_notice . '</div>';

			wp_send_json_success(
                array(
                    'status'      => 'OK',
                    'html'        => $html,
                    'data'        => '',
                    'message' 	  => $price_notice,
               )
           );
           exit();
        }

        /**
         * Creating shipment
         *
         * @param $data
         * @return json
         */
        public function ajax_create_shipment( $data ) {
			
			if ( empty( $data ) ) {
				$data = $_POST;
			}
			
			$shipment = $this->create_shipment( $data );
			if ( is_wp_error( $shipment ) ) {
				wp_send_json_error(
					array(
						'status' => $shipment->get_error_code(),
						'data' 	 => sprintf(
							__( 'Error while creating or updating shipment: %s', 'shipcloud-for-woocommerce' ),
							$shipment->get_error_message()
						)
					)
				);
				exit();
			}
			
            $order_id = null;
            if ( isset( $data['order_id'] ) ) {
                $order_id = $data['order_id'];
            }
			
			wp_send_json_success(
				array(
                    'status'      => 'OK',
                    'shipment_id' => $shipment['id'],
                    'data'        => WC_Shipping_Shipcloud_Utils::convert_to_wc_api_response( $shipment, $order_id ),
                    'message' 	  => __( 'Shipment successfully created or updated', 'shipcloud-for-woocommerce' ),
				)
			);
			exit();
        }
		
        /**
         * Updating a shipment
         *
         * @return json
         */
		public function ajax_update_shipment() {
			
	        if ( ! is_user_logged_in() && ! is_admin() ) {
		        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		            wp_send_json_error( new \WP_Error( 403, __( 'Not authenticated', 'shipcloud-for-woocommerce' ) ), 403 );
		        }
	            return;
	        }

	        if ( ! isset( $_POST['shipment'] ) ) {
		        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		            wp_send_json_error( new \WP_Error( 400, __( 'Bad Request', 'shipcloud-for-woocommerce' ) ), 400 );
		        }
	            return;
	        }
			
			$data = $_POST;
			
			unset( $data['action'] );
	        unset( $data['shipment_order_id'] );
			
			$order = WC_Shipping_Shipcloud_Utils::find_order_by_shipment_id( $data['shipment']['id'] );

	        try {
				
				// unset notification_email if it was not checked
		        if ( ! isset( $data['shipcloud_notification_email_checkbox'] ) ) {
		            unset( $data['shipcloud_notification_email'] );
		        }
				
				if ( isset( $data['pickup'] ) ) {
		            $pickup = $this->extract_pickup_time( $data );
					if ( ! is_wp_error( $pickup ) && ! empty( $pickup ) ) {
		                $data['shipment']['pickup'] = $pickup;
		            }
				}
	            
		        $label_format = $data['shipcloud_label_format'];
		        unset( $data['shipcloud_label_format'] );

		        if ( isset( $label_format ) ) {
					$data['shipment']['label'] = array(
						'format' => $label_format
					);
		        }

	            if ( ! empty( $order ) ) {
					
		            $sc_order = $this->create_order( $order->get_id() );

		            $data['shipment']['additional_services'] = $this->additional_services_from_request(
						$data['shipment']['additional_services'],
						$data['shipment']['carrier'],
						$sc_order
					);

		            if ( array_key_exists( 'customs_declaration', $data ) ) {
		                $data['shipment']['customs_declaration'] = $this->handle_customs_declaration( $data['customs_declaration'] );
		                unset($data['customs_declaration']);
		            }

		            $shipment = $this->api->update_shipment( $data['shipment'] );
                    if ( is_wp_error( $shipment ) ) {
                        $message = $shipment->get_error_message();
                        $this->log( $message, 'error' );
                        wp_send_json_error(
                            array(
                                'status' => $shipment->get_error_code(),
                                'data' 	 => sprintf(
                                    __( 'Error while updating shipment: %s', 'shipcloud-for-woocommerce' ),
                                    $shipment->get_error_message()
                               )
                           )
                       );
					   exit();
                    }
					
                    $prev_shipment_data = WC_Shipping_Shipcloud_Utils::get_shipment_for_order( $order->get_id(), $shipment['id'] );
                    $shipment_data 		= array_merge( $prev_shipment_data, $shipment );

                    $order->add_order_note( __( 'shipcloud shipment has been updated.', 'shipcloud-for-woocommerce' ) );
                    update_post_meta( $order->get_id(), 'shipcloud_shipment_data', $shipment_data, $prev_shipment_data );
					
					wp_send_json_success(
	                    array(
	                        'status'      => 'OK',
	                        'shipment_id' => $shipment['id'],
	                        'html'        => '',
	                        'data'        => WC_Shipping_Shipcloud_Utils::convert_to_wc_api_response( $shipment, $order->get_id() ),
	                        'message' 	  => __( 'shipcloud shipment has been updated successfully.', 'shipcloud-for-woocommerce' ),
	                   )
	               );
	               exit();
					
				}
	            
	        } catch ( \Exception $e ) {
				$this->log(
                    sprintf(
                        'Order #%d - %s ( $s )',
                        $order->get_order_number(),
                        $e->getMessage() 
					),
					'error'
				);

                wp_send_json_error( WC_Shipping_Shipcloud_Utils::convert_exception_to_wp_error( $e ) );				
                exit();
	        }
			
		}
		
        /**
         * Deleting a shipment
         *
         * @return json
         */
        public function ajax_delete_shipment() {
            $shipment_id = $_POST['shipment_id'];
			$response	 = $this->api->delete_shipment( $shipment_id );
			if ( is_wp_error( $response ) ) {
                // Do nothing if shipment was not found
				$this->log( $response->get_error_message(), 'error' );				
				wp_send_json_error(
					array(
						'status' => 400,
						'data' 	 => sprintf(
							__( 'Could not delete shipment. Reason: %s', 'shipcloud-for-woocommerce' ),
							$response->get_error_message()
						),
					)
				);
				exit();
            }

            $this->delete_shipment_from_db( $shipment_id );
            wp_send_json_success(
                array(
                    'status'	=> 'OK',
                    'html'		=> '',
                    'data'		=> [ $shipment_id ],
                    'message'	=> sprintf( __( "Shipment %s has been deleted.", 'shipcloud-for-woocommerce' ), $shipment_id ),
				)
           );
           exit();
        }

        /**
         * Force deleting a shipment
         *
         * @return json
         */
        public function ajax_force_delete_shipment() {
            $shipment_id = $_POST['shipment_id'];
            $this->delete_shipment_from_db( $shipment_id );
        }
		
		/**
         * Create a pickup request for a given shipment
		 *
         * @return json
         */
        public function ajax_create_pickup_request() {
            if ( ! $_POST['id'] ) {
                return;
            }
			
		    $shipment_id = $_POST['id'];
			$tmp_order	 = WC_Shipping_Shipcloud_Utils::find_order_by_shipment_id( $shipment_id );
		    $order_id	 = $tmp_order->get_order_number();

            $updated_shipment = $this->create_pickup_request( $order_id, $_POST );
			if ( is_wp_error( $updated_shipment ) ) {
				wp_send_json_error(
					array(
						'status' => 400,
						'data' 	 => sprintf(
							__( 'Error while creating or updating shipment: %s', 'shipcloud-for-woocommerce' ),
							$updated_shipment->get_error_message()
						)
					)
				);
				exit();
			}
			else if ( ! $updated_shipment ) {
				wp_send_json_error(
					array(
						'status' => 400,
						'data' 	 => __( 'Error while creating or updating shipment', 'shipcloud-for-woocommerce' )
					)
				);
				exit();
			}
			
            wp_send_json_success(
                array(
                    'status' => 'OK',
                    'data'	 => WC_Shipping_Shipcloud_Utils::convert_to_wc_api_response( $updated_shipment, $order_id ),
               )
           );
			
        }
		
        /**
         * Getting a pakadoo point
		 * 
         * @return json
         */
        public function ajax_get_pakadoo_point() {
            $pakadoo_id = $_POST['pakadoo_id'];
            $response 	= $this->api->get_address_by_pakadoo_id( $pakadoo_id );

            if ( is_wp_error( $response ) ) {
                wp_send_json_error(
                	array(
                		'status' => $response->get_error_code(),
                	    'data' 	 => sprintf(
                	    	__( 'Could not get pakadoo point. Reason: %s', 'shipcloud-for-woocommerce' ),
                			$response->get_error_message()
                		)
                	)
                );
                exit();
            }
			
			wp_send_json_success(
            	array(
            		'status'      => 'OK',
            		'html'        => '',
            		'data'        => $response,
            		'message' 	  => '',
            	)
            );
            exit();
        }
		
		
		/*****************************************************************
         *
         *		STUFF
         *
         *****************************************************************/
		
        public function create_shipment( $data ) {
			
			// $this->log( "create_shipment: " . json_encode( $data ) ); return;
			
			if ( empty( $data ) ) {
				$data = $_POST;
			}
			
			$order_id = null;
			if ( ! is_null( $this->order_id ) && ! empty( $this->order_id ) ) {
				$order_id = $this->order_id;
			}
            else if ( isset( $data['order_id'] ) ) {
				$order_id = $data['order_id'];
				$this->order_id = $order_id;
            }
			
			if ( isset( $data['id'] ) ) {
                $shipment_id = $data['id'];
            } elseif ( isset( $data['shipment_id'] ) ) {
                $data['id']  = $data['shipment_id'];
                $shipment_id = $data['shipment_id'];
            }

            if ( empty( $order_id ) && ! empty( $shipment_id ) ) {
                $tmp_order = WC_Shipping_Shipcloud_Utils::find_order_by_shipment_id( $shipment_id );
				if ( ! empty( $tmp_order ) ) {
					$order_id = $tmp_order->get_order_number();
				} else {
		            return new \WP_Error( 1, sprintf( 'No matching order found for shipment ID %s', $shipment_id ) );
				}
            }
			
			$order = wc_get_order( $order_id );
			
			if ( array_key_exists( 'shipcloud_carrier', $data ) ) {
                $data['carrier'] = $data['shipcloud_carrier'];
            }
			
			if ( array_key_exists( 'shipcloud_carrier_service', $data ) ) {
                $data['service'] = $data['shipcloud_carrier_service'];
            }
			
			if ( empty( $data['from'] ) ) {
				$data['from'] = $this->get_sender();
			}
			
			if ( ! isset( $data['from']['id'] ) || empty( $data['from']['id'] ) ) {
                unset( $data['from']['id'] );
            }
			
			if ( empty( $data['to'] ) ) {
				$data['to'] = $this->get_recipient();
			}

            if ( ! isset( $data['to']['id'] ) || empty( $data['to']['id'] ) ) {
                unset( $data['to']['id'] );
            }

            $data['create_shipping_label'] = ( $data['action'] === 'shipcloud_create_shipment_label' );
			
			// "shipcloud_label_format": "pdf_a6",
			if ( array_key_exists( 'shipcloud_label_format', $data ) ) {
                $data['label']['format'] = $data['shipcloud_label_format'];
            }
            
			$data = $this->handle_return_shipments( $data );
            $data = $this->sanitize_shop_owner_data( $data );
            $data = $this->sanitize_reference_number( $data );
			
			if ( array_key_exists( 'additional_services', $data ) ) {
				$data['additional_services'] = WC_Shipping_Shipcloud_Utils::filter_additional_services(
					$data['additional_services'],
					$data['carrier']
				);
				$data['additional_services'] = WC_Shipping_Shipcloud_Utils::extract_additional_services( $data );
            }
			else if ( isset( $data['shipment']['additional_services'] ) ) {
	            $data['additional_services'] = $this->additional_services_from_request(
					$data['shipment']['additional_services'],
					$data['carrier'],
					$order
				);
			}

      $data = $this->handle_email_notification( $data );

			if ( array_key_exists( 'customs_declaration', $data ) ) {
                $data['customs_declaration'] = $this->handle_customs_declaration( $data['customs_declaration'] );
            }
			
			if ( array_key_exists( 'package', $data ) ) {
                $data['package'] = $this->sanitize_package( $data['package'] );
            } 
			else if ( array_key_exists( 'parcel_width', $data ) ) {
            	$data['package'] = $this->sanitize_parcel_data( $data );
            }
			
			if ( isset( $data['pickup'] ) ) {
				$pickup = $this->extract_pickup_time( $data, 'create_shipment' );
				if ( ! is_wp_error( $pickup ) && ! empty( $pickup ) ) {
					$data['pickup'] = $pickup;
				} else {
					unset( $data['pickup'] );
				}
				unset( $data['pickup_earliest'] );
				unset( $data['pickup_latest'] );
			}
			else if ( ! empty( $data['pickup_request'] ) ) {
				$data['pickup'] = [ 'pickup_time' => $data['pickup_request']['pickup_time'] ];
				unset( $data['pickup_request'] );
			}
		   
			// Only use API fields.
			$data = array_intersect_key(
                $data,
                array(
                    'id'					=> null,
                    'carrier'               => null,
                    'from'                  => null,
                    'notification_email'    => null,
                    'description'           => null,
                    'package'               => null,
                    'reference_number'      => null,
                    'service'               => null,
                    'create_shipping_label' => null,
                    'to'                    => null,
                    'additional_services'   => null,
                    'pickup'                => null,
                    'customs_declaration'   => null,
                    'label'                 => null,
               ),
		   );
		   
		   $message = '';

		   try {

			   	if ( isset( $shipment_id ) ) {

					// Update shipment
					$this->log( 'Updating shipment with shipment_id: '.$shipment_id );
					
					$shipment = $this->api->update_shipment( $data );
					if ( is_wp_error( $shipment ) ) {
						return $shipment;
					}

                    $prev_shipment_data = WC_Shipping_Shipcloud_Utils::get_shipment_for_order( $order_id, $shipment_id );
					$shipment_data 		= array_merge( $prev_shipment_data, $shipment );
					$order->add_order_note( __( 'shipcloud label has been prepared.', 'shipcloud-for-woocommerce' ) );
                    update_post_meta( $order_id, 'shipcloud_shipment_data', $shipment_data, $prev_shipment_data );

                    if ( array_key_exists( 'customs_declaration', $prev_shipment_data ) 
						&& ! array_key_exists( 'customs_declaration', $shipment )
					) {
                        $message = __( 'Customs declaration documents not necessary. Therefore they were ignored.', 'shipcloud-for-woocommerce' );
                    }
                } else {

                    // Create
                    $shipment = $this->api->create_shipment( $data );
                    if ( is_wp_error( $shipment ) ) {
                        return $shipment;
                    }
					
					if ( isset( $shipment['id'] ) ) {
						add_post_meta( $order_id, 'shipcloud_shipment_ids', $shipment['id'] );
	                    add_post_meta( $order_id, 'shipcloud_shipment_data', $shipment );
					}
					
                    if ( array_key_exists( 'customs_declaration', $data ) && empty( $shipment['customs_declaration'] ) ) {
                        $message = __( 'Customs declaration documents not necessary. Therefore they were ignored.', 'shipcloud-for-woocommerce' );
                    }
                }

				if ( isset( $data['carrier'] ) ) {
					$this->log( 'Order #' . $order->get_order_number() . ' - Created shipment successful (' . WC_Shipping_Shipcloud_Utils::get_carrier_display_name( $data['carrier'] ) . ')' );
				}
				
				// $this->log("Updated shipment: ".json_encode($shipment));
                
				if ( isset( $shipment['price'] ) ) {
					$shipment['price'] = WC_Shipping_Shipcloud_Utils::format_price( $shipment['price'] );
				}
				
				return $shipment;
				
            } catch ( \Exception $e ) {
                $this->log(
                    sprintf(
                        'Order #%d - %s ( $s )',
                        $order->get_order_number(),
                        $e->getMessage(),
                        WC_Shipping_Shipcloud_Utils::get_carrier_display_name( $data['carrier'] )
                   ), 'error'
               );

                return WC_Shipping_Shipcloud_Utils::convert_exception_to_wp_error( $e );
            }
			
			return false;
        }
		
		/**
		 * Getting order object.
		 * 
		 * @return WC_Order
		 */
		private function get_order() {
		    return $this->wc_order;
		}

		/**
		 * Getting WC order object.
		 * 
		 * @param $order_id
		 * @return WC_Order
		 */
		public function get_wc_order( $order_id = '' ) {
		    if ( empty( $order_id ) && ! empty( $this->order_id ) ) {
		        $order_id = $this->order_id;
		    }
			
			return wc_get_order( $order_id );
		}
		
		/**
		 * Getting addresses.
		 *
		 * @return array $addresses
		 */
		private function get_addresses() {
		    return array(
		        'sender' 	=> $this->get_sender(),
		        'recipient' => $this->get_recipient()
		   );
		}
		
		/**
		 * Getting sender.
		 * 
		 * @param mixed $prefix
		 *
		 * @return array|mixed
		 */
		private function get_sender( $prefix = '' ) {
		    
			$first_name = $last_name = '';
			$store_manager = get_option( 'woocommerce_store_manager', '' );
			if ( ! empty( $store_manager ) ) {
				$name 		= WC_Shipping_Shipcloud_Utils::explode_name( $store_manager );
				$first_name = $name['first_name'];
				$last_name  = $name['last_name'];
			}
			
			$street = $street_no = '';
			$store_address	 = get_option( 'woocommerce_store_address', '' );
			$store_address_2 = get_option( 'woocommerce_store_address_2', '' );
			$store_address  .= ' ' . $store_address_2;
			if ( ! empty( $store_address ) ) {
				$address 	= WC_Shipping_Shipcloud_Utils::explode_street( $store_address );
				$street	 	= $address['address'];
				$street_no 	= $address['number'];
			}

			$sender = array(
	            $prefix . 'company'    => get_option( 'woocommerce_store_company', '' ),
				$prefix . 'first_name' => $first_name,
	            $prefix . 'last_name'  => $last_name,
	            
	            $prefix . 'street'     => $street,
	            $prefix . 'street_no'  => $street_no,
	            
				$prefix . 'zip_code'   => get_option( 'woocommerce_store_postcode' ),
	            $prefix . 'city'       => get_option( 'woocommerce_store_city', '' ),
	            $prefix . 'state'      => '',
	            $prefix . 'country'    => WC_Shipping_Shipcloud_Utils::maybe_extract_country_code( get_option( 'woocommerce_default_country' ) ),
	            
				$prefix . 'phone'      => '',
			);
			
			$address = $this->sanitize_address( $sender, $prefix );

		    if ( count( $address ) <= 1 ) {
		        // No sender address entered ( just the country autofill ).
		        return [];
		    }

		    return $address;
		}
		
		/**
		 * Prefixes data.
		 * 
		 * @param array $data
		 * @param mixed $prefix
		 *
		 * @return array
		 */
		private function prefix_data( $data, $prefix ) {
		    if ( ! $prefix ) {
		        return $data;
		    }

		    foreach ( $data as $key => $value ) {
		        if ( 0 === strpos( $key, $prefix ) ) {
		            // Has already the prefix.
		            continue;
		        }

		        $data[$prefix . $key] = $data[$key];
		        unset( $data[$key] );
		    }

		    return $data;
		}

		/**
		 * Getting recipient.
		 * 
		 * @param string $prefix
		 * @return array|mixed
		 *
		 */
		private function get_recipient( $prefix = '' ) {
		    $options 	= WC_Shipping_Shipcloud_Utils::get_plugin_options();
		    $recipient 	= get_post_meta( $this->order_id, 'shipcloud_recipient_address', true );

		    // Use default data if nothing was saved before
		    if ( empty( $recipient ) || 0 == count( $recipient ) ) {
				$order = wc_get_order( $this->order_id );

		        $recipient_street_name = $order->get_shipping_address_1();
		        $recipient_street_nr   = '';

		        if ( ! array_key_exists( 'street_detection', $options ) || 'yes' === $options['street_detection'] ) {
		            $recipient_street = WC_Shipping_Shipcloud_Utils::explode_street( $recipient_street_name );
		            if ( is_array( $recipient_street ) ) {
		                $recipient_street_name = $recipient_street['address'];
		                $recipient_street_nr   = $recipient_street['number'];
		            }
		        }
		
				$recipient = array(
		            'first_name' => $order->get_shipping_first_name(),
		            'last_name'  => $order->get_shipping_last_name(),
		            'company'    => $order->get_shipping_company(),
		            'care_of'    => $this->get_care_of(),
		    		'email'      => $this->get_email_for_notification(),
		    		'street'     => $recipient_street_name,
		            'street_no'  => $recipient_street_nr,
		            'zip_code'   => $order->get_shipping_postcode(),
		            'postcode'   => $order->get_shipping_postcode(),
		            'city'       => $order->get_shipping_city(),
		            'state'      => $order->get_shipping_state(),
		            'country'    => WC_Shipping_Shipcloud_Utils::maybe_extract_country_code( $order->get_shipping_country() ),
		            'phone'      => $order->get_shipping_phone(),
		       );
		    }

		    return $this->sanitize_address( $this->prefix_data( $recipient, $prefix ), $prefix );
		}
		
		/**
		 * Resolve care of from order.
		 *
		 * This will take in advance:
		 *
		 * - The custom field "care of"
		 * - The shipping address
		 * - At last the billing address
		 *
		 * @return string
		 */
		private function get_care_of() {
		    $order = $this->get_wc_order();

		    if ( ! $order ) {
		        // No order present.
		        return '';
		    }

		    if ( method_exists( $order, 'get_meta' ) && $care_of = $order->get_meta( '_shipping_care_of' ) ) {
		        $this->log( 'Use care of from _shipping_care_of' );
		        return ( string ) $care_of;
		    }

		    $shipping_address_2 = $order->get_shipping_address_2();
		    if ( $shipping_address_2 ) {
		        // Shipping address overrides billing address.
		        $this->log( 'Use shipping_address_2 as care of' );
		        return ( string ) $shipping_address_2;
		    }

		    // check to see if WooCommerce germanized was used for supplying a post number
		    if ( method_exists( $order, 'get_shipping_parcelshop_post_number' ) ) {
		        $this->log( 'WooCommerce germanized detected' );
		        $this->log( 'Use parcelshop_post_number as care of' );
		        return ( string ) $order->get_shipping_parcelshop_post_number( );
		    } elseif ( method_exists( $order, 'shipping_parcelshop_post_number' ) ) {
		        $this->log( 'WooCommerce germanized detected' );
		        $this->log( 'Use parcelshop_post_number as care of' );
		        return ( string ) $order->shipping_parcelshop_post_number;
		    }

		    // if all fails, return an empty string
		    return '';
		}
		
		/**
		 * Gets email for notification.
		 *
		 * @return string
		 */
		public function get_email_for_notification() {
		    $order = $this->get_wc_order();

		    if ( method_exists( $order, 'get_billing_email' ) ) {
		        return $order->get_billing_email();
		    } elseif ( method_exists( $order, 'billing_email' ) ) {
		        return $order->billing_email;
		    } else {
		        return '';
		    }
		}
		
		/**
		 * Resolve phone number from order.
		 *
		 * @return string
		 */
		private function get_phone() {
		    $order = $this->get_wc_order();

		    if ( ! $order ) {
		        // No order present.
		        return '';
		    }

        $recipient 	= $this->get_recipient();
        if ( $recipient['phone'] ) {
          return ( string ) $recipient['phone'];
        }

		    if ( method_exists( $order, 'get_meta' ) ) {
		        return ( string ) $order->get_meta( '_shipping_phone' );
		    } elseif ( method_exists( $order, 'get_meta_data' ) ) {
		        return ( string ) $order->get_meta_data( '_shipping_phone' );
		    }

		    return ( string ) $order->billing_phone;
		}
		
		/**
		 * Help the user sanitizing the sender address.
		 *
		 * @param $data
		 * @param string $prefix
		 * @return array
		 */
		private function sanitize_address( $data, $prefix = '' ) {
			$build_data = array(
		        'company'    => '',
		        'first_name' => '',
		        'last_name'  => '',
		        'care_of'    => '',
		        'street'     => '',
		        'street_no'  => '',
		        'zip_code'   => '',
		        'postcode'   => '',
		        'city'       => '',
		        'country'    => '',
		        'phone'      => '',
			);
			$data = array_merge( $build_data, $data );

		    if ( isset( $data[$prefix . 'street_nr'] ) ) {
		        // Backward compatibility.
		        $data[$prefix . 'street_no'] = $data[$prefix . 'street_nr'];
		    }

		    if ( isset( $data[$prefix . 'postcode'] ) && !empty( $data[$prefix . 'postcode'] ) ) {
		        // Backward compatibility.
		        $data[$prefix . 'zip_code'] = $data[$prefix . 'postcode'];
		    }

		    return array_filter( $data, function( $var ) {
				return ( $var !== null && $var !== false );
			} );
		}
		
        /**
         * Creates an array of available parcel templates.
		 * 
         * @return array
         */
		public function get_parcel_templates() {
            $posts = get_posts(
                array( 
					'post_type'   	 => WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE,
                    'post_status' 	 => 'publish',
                    'posts_per_page' => -1 
				)
			);

            $parcel_templates = [];
            if ( is_array( $posts ) && count( $posts ) > 0 ) {
                $parcel_templates = [];
	            foreach ( $posts as $post ) {
					$parcel_template = $this->generate_parcel_template( $post );
					if ( ! empty( $parcel_template ) ) {
						$parcel_templates[] = $parcel_template;
					}
	            }
            }
			
			return $parcel_templates;
		}
		
        /**
         * Generates a single parcel template array.
		 * 
		 * @param WP_Post $data
         * @return array|bool
         */
        private function generate_parcel_template( $data ) {
			
			if ( $data instanceof WP_Post ) {
				
                $carrier = $data->carrier;
                if ( ! is_array( $data->carrier ) ) {
                    $tmp = explode( '_', $carrier, 2 );
                    $carrier = [];
                    $carrier['carrier'] = $tmp[0];
                    $carrier['service'] = $tmp[1];
                    $carrier['package'] = null;
                }

                $option = $data->width . esc_attr( 'x', 'shipcloud-for-woocommerce' )
                           . $data->height . esc_attr( 'x', 'shipcloud-for-woocommerce' )
                           . $data->length . esc_attr( 'cm', 'shipcloud-for-woocommerce' )
                           . ' - ' . $data->weight . esc_attr( 'kg', 'shipcloud-for-woocommerce' )
                           . ' - ' . WC_Shipping_Shipcloud_Utils::get_carrier_display_name( $carrier['carrier'] )
                           . ' - ' . WC_Shipping_Shipcloud_Utils::get_service_name( $carrier['service'] );

                if ( $carrier['package'] ) {
                    $option .= ' - ' . WC_Shipping_Shipcloud_Utils::get_package_label( $carrier['package'] );
                }

                return array(
                    'value'  => "pt_{$data->ID}",
                    'option' => $option,
                    'data'   => array(
                        'parcel_width'      => $data->width,
                        'parcel_height'     => $data->height,
                        'parcel_length'     => $data->length,
                        'parcel_weight'     => $data->weight,
                        'shipcloud_carrier' => $carrier['carrier'],
                        'shipcloud_carrier_service' => $carrier['service'],
                        'shipcloud_carrier_package' => $carrier['package'],
                   ),
				   'shipcloud_is_standard_parcel_template' => $data->shipcloud_is_standard_parcel_template
               );
            }
			
			return false;
        }
		
		/**
		 * Check to see if it's a return shipment
		 *
		 * @param array $data
		 * @return array
		 */
		private function handle_return_shipments( $data ) {
		    if ( isset( $data['service'] ) && $data['service'] === 'returns' ) {
		        $this->log( 'Detected returns shipment. Switching from and to entries.' );
		        $to 			= $data['to'];
		        $from 			= $data['from'];
		        $data['from'] 	= $to;
		        $data['to'] 	= $from;
		    }

		    return array_filter( $data );
		}
		
		/**
		 * Sanitize shop owner data.
		 *
		 * @param array $data
		 * @return array
		 */
		private function sanitize_shop_owner_data( $data ) {
		    $shopOwner = 'from';

		    if ( isset( $data['service'] ) && $data['service'] === 'returns' ) {
		        $shopOwner = 'to';
		    }

			if ( ! empty( $data[$shopOwner] ) ) {
				$from = array_filter( $data[$shopOwner] );
			    if ( count( $from ) <= 1 ) {
			        // Drop shop owner when no address is given ( should be only a country then / one entry ).
			        unset( $data[$shopOwner] );

			        // Try one last time with the stored sender.
			        if ( count( $this->get_sender() ) > 1 ) {
			            $data[$shopOwner] = $this->get_sender();
			        }
			    }
			}
		    
		    if ( array_key_exists( 'other_description', $data ) ) {
		        $data['description'] = $data['other_description'];
		    }

		    return array_filter( $data );
		}

		/**
		 * Replace shipcloud shortcodes in reference_number
		 *
		 * @param array $data
		 * @return array
		 */
		private function sanitize_reference_number( $data ) {
		    if ( array_key_exists( 'reference_number', $data ) ) {
				$data['reference_number'] = str_replace( '[shipcloud_orderid]', $this->order_id, $data['reference_number'] );
		    }

		    return $data;
		}
		
		/**
		 * Handle customs declaration
		 *
		 * @param array $data
		 * @return array
		 */
		private function handle_customs_declaration( $data ) {
			
	        if ( isset( $data['shown'] ) && in_array( $data['shown'], [ '0', 'no', 'false', false ] ) ) {
	            return null;
	        }
			
		    $line_items = $data['items'];

		    $items = [];
            foreach ( $line_items as $line_item_key => $line_item_data ) {
				$items[] = $line_item_data;
            }

		    $data['items'] = $items;
		    return $data;
		}

    /**
     * Check to see if email notification - by either shipcloud or the carrier - is desired by the 
     * customer. Otherwise remove the data.
     *
     * @param array $data
     * @return array
     */
    private function handle_email_notification( $data ) {
      if ( array_key_exists('additional_services', $data) ) {
        if ( !$this->email_notification_enabled() ) {
          unset($data['notification_email']);
          $additional_services_names = array_column($data['additional_services'], 'name');
  
          if ( in_array( 'advance_notice', $additional_services_names ) ) {
            $additional_services = [];
            foreach ( $data['additional_services'] as $additional_service ) {
              if ( $additional_service['name'] != 'advance_notice' ) {
                $additional_services[] = $additional_service;
              }
            }
            $data['additional_services'] = $additional_services;
          }
        } else {
          if ( isset( $data['shipcloud_notification_email_checkbox'] ) ) {
            if ( !isset( $data['shipcloud_notification_email'] )) {
              $data['notification_email'] = $this->get_email_for_notification();
            } else {
              $data['notification_email'] = $data['shipcloud_notification_email'];
            }
          }
  
          $additional_services_names = array_column($data['additional_services'], 'name');
          if ( in_array( 'advance_notice', $additional_services_names ) ) {
            // make sure notification_email doesn't get transmitted when advance notice is being used
            unset($data['notification_email']);
          }
        }
      }

      return $data;
    }

		/**
		 * Sanitize package data.
		 *
		 * User enter package data that can:
		 *
		 * - Have local decimal separator.
		 *
		 * @param array $package_data
		 * @return array
		 */
		private function sanitize_package( $package_data ) {
		    $package_data['width']  = wc_format_decimal( $package_data['width'] );
		    $package_data['height'] = wc_format_decimal( $package_data['height'] );
		    $package_data['length'] = wc_format_decimal( $package_data['length'] );
		    $package_data['weight'] = wc_format_decimal( $package_data['weight'] );

		    if ( array_key_exists( 'declared_value', $package_data ) ) {
		        $package_data['declared_value']['amount'] = wc_format_decimal( $package_data['declared_value']['amount'] );
		    }
			
			if ( array_key_exists( 'shipcloud_carrier_package', $package_data ) ) {
		        $package_data['type'] = $package_data['shipcloud_carrier_package'];
		    }
			
			if ( array_key_exists( 'description', $package_data ) ) {
				$package_data['description'] = $package_data['description'];
			}

		    return $package_data;
		}
		
		private function sanitize_parcel_data( $data ) {
			$parcel_data = [];
			$parcel_data['width']  = wc_format_decimal( $data['parcel_width'] );
		    $parcel_data['height'] = wc_format_decimal( $data['parcel_height'] );
		    $parcel_data['length'] = wc_format_decimal( $data['parcel_length'] );
      if (isset($data['shipcloud_use_calculated_weight']) && $data['shipcloud_use_calculated_weight'] == 'use_calculated_weight') {
        $parcel_data['weight'] = $this->get_calculated_weight();
      } else {
        $parcel_data['weight'] = wc_format_decimal( $data['parcel_weight'] );
      }

		    if ( array_key_exists( 'declared_value', $data ) ) {
		        $parcel_data['declared_value']['amount'] = wc_format_decimal( $data['declared_value']['amount'] );
		    }
			
			if ( array_key_exists( 'shipcloud_carrier_package', $data ) ) {
		        $parcel_data['type'] = $data['shipcloud_carrier_package'];
		    }
			
			if ( array_key_exists( 'description', $data ) ) {
				$parcel_data['description'] = $data['description'];
			}
			
		    return $parcel_data;			
		}
		
		/**
		 * Extract pickup time from request
		 *
		 * @param $data
		 * @param $method
		 * @return array $pickup
		 */
		private function extract_pickup_time( $data, $method = null ) {
			
			if ( empty( $data['pickup']['pickup_earliest_date'] ) 
		        || empty( $data['pickup']['pickup_earliest_time_hour'] )
		        || empty( $data['pickup']['pickup_earliest_time_minute'] )
		        || empty( $data['pickup']['pickup_latest_time_hour'] )
		        || empty( $data['pickup']['pickup_latest_time_minute'] )
			) {
		        $this->log( 'Unsufficient pickup data', 'warning' );
		        return new \WP_Error( 1, __( 'Pickup date and time fields must not be empty!', 'shipcloud-for-woocommerce' ) );
		    }

		    if ( array_key_exists( 'shipment', $data ) && array_key_exists( 'carrier', $data['shipment'] )  ) {
		        $carrier = $data['shipment']['carrier'];
		    } elseif ( array_key_exists( 'shipcloud_carrier', $data ) ) {
		        $carrier = $data['shipcloud_carrier'];
		    } elseif ( array_key_exists( 'carrier', $data ) ) {
		        $carrier = $data['carrier'];
		    } else {
		        $error_message = __( 'Carrier missing in request', 'shipcloud-for-woocommerce' );
		        $this->log( $error_message, 'error' );
		        throw new \UnexpectedValueException( $error_message, 400 );
		    }

		    $pickup = [];
			
			/*
			$carrier_providing_pickup_service 	= WC_Shipping_Shipcloud_Utils::get_carrier_providing_pickup_service();
			$carriers_with_pickup_object		= $carrier_providing_pickup_service['carriers_with_pickup_object'];
			$carriers_with_pickup_request 		= $carrier_providing_pickup_service['carriers_with_pickup_request'];
			
		    if ( in_array( $carrier, $carriers_with_pickup_object )
		        || in_array( $carrier, $carriers_with_pickup_request )
		        && 'create_shipment' !== $method 
			) {
					
				$pickup_earliest_date 		 = isset( $data['pickup']['pickup_earliest_date'] ) ? $data['pickup']['pickup_earliest_date'] : '';
		        $pickup_earliest_time_hour 	 = isset( $data['pickup']['pickup_earliest_time_hour'] ) ? $data['pickup']['pickup_earliest_time_hour'] : '';
		        $pickup_earliest_time_minute = isset( $data['pickup']['pickup_earliest_time_minute'] ) ? $data['pickup']['pickup_earliest_time_minute'] : '';
		        $pickup_latest_date 		 = isset( $data['pickup']['pickup_earliest_date'] ) ? $data['pickup']['pickup_earliest_date'] : '';
		        $pickup_latest_time_hour 	 = isset( $data['pickup']['pickup_latest_time_hour'] ) ? $data['pickup']['pickup_latest_time_hour'] : '';
		        $pickup_latest_time_minute 	 = isset( $data['pickup']['pickup_latest_time_minute'] ) ? $data['pickup']['pickup_latest_time_minute'] : '';

		        $pickup_earliest = $pickup_earliest_date . ' ' . $pickup_earliest_time_hour . ':' . $pickup_earliest_time_minute;
		        $pickup_latest 	 = $pickup_latest_date . ' ' . $pickup_latest_time_hour . ':' . $pickup_latest_time_minute;

		        try {
		            $pickup_earliest = new WC_DateTime( $pickup_earliest, new DateTimeZone( 'Europe/Berlin' ) );
		            $pickup_latest	 = new WC_DateTime( $pickup_latest, new DateTimeZone( 'Europe/Berlin' ) );

		            $pickup['pickup_time']['earliest'] 	= $pickup_earliest->format( DateTime::ATOM );
		            $pickup['pickup_time']['latest'] 	= $pickup_latest->format( DateTime::ATOM );
		        } catch ( Exception $e ) {
		            $this->log( sprintf( __( 'Couldn\'t prepare pickup: %s', 'shipcloud-for-woocommerce' ), $e->getMessage() ), 'error' );
		        }
		    }
			else {
				$error_message = sprintf(
					__( 'Carrier %s doesn\'t provide pickup service', 'shipcloud-for-woocommerce' ),
					$carrier
				);
		        $this->log( $error_message, 'error' );
		        throw new \UnexpectedValueException( $error_message, 400 );
			}
			*/
			
			$pickup_earliest_date 		 = isset( $data['pickup']['pickup_earliest_date'] ) ? $data['pickup']['pickup_earliest_date'] : '';
	        $pickup_earliest_time_hour 	 = isset( $data['pickup']['pickup_earliest_time_hour'] ) ? $data['pickup']['pickup_earliest_time_hour'] : '';
	        $pickup_earliest_time_minute = isset( $data['pickup']['pickup_earliest_time_minute'] ) ? $data['pickup']['pickup_earliest_time_minute'] : '';
	        $pickup_latest_date 		 = isset( $data['pickup']['pickup_earliest_date'] ) ? $data['pickup']['pickup_earliest_date'] : '';
	        $pickup_latest_time_hour 	 = isset( $data['pickup']['pickup_latest_time_hour'] ) ? $data['pickup']['pickup_latest_time_hour'] : '';
	        $pickup_latest_time_minute 	 = isset( $data['pickup']['pickup_latest_time_minute'] ) ? $data['pickup']['pickup_latest_time_minute'] : '';

	        $pickup_earliest = $pickup_earliest_date . ' ' . $pickup_earliest_time_hour . ':' . $pickup_earliest_time_minute;
	        $pickup_latest 	 = $pickup_latest_date . ' ' . $pickup_latest_time_hour . ':' . $pickup_latest_time_minute;

	        try {
	            $pickup_earliest = new WC_DateTime( $pickup_earliest, new DateTimeZone( 'Europe/Berlin' ) );
	            $pickup_latest	 = new WC_DateTime( $pickup_latest, new DateTimeZone( 'Europe/Berlin' ) );

	            $pickup['pickup_time']['earliest'] 	= $pickup_earliest->format( DateTime::ATOM );
	            $pickup['pickup_time']['latest'] 	= $pickup_latest->format( DateTime::ATOM );
	        } catch ( Exception $e ) {
	            $this->log( sprintf( __( 'Couldn\'t prepare pickup: %s', 'shipcloud-for-woocommerce' ), $e->getMessage() ), 'error' );
				return WC_Shipping_Shipcloud_Utils::convert_exception_to_wp_error( $e );
	        }

		    return $pickup;
		}
		
		/**
		 * Calculate weight
		 *
		 * @return float $calculated_weight
		 */
		public function get_calculated_weight() {
		    $order_items = wc_get_order( $this->order_id )->get_items();

		    $calculated_weight = 0;
		    foreach ( $order_items as $order_item ) {
		        $quantity = $order_item->get_quantity();
		        $weight = 0;
		        $product = $order_item->get_product();
		        if ( $product ) {
		            $weight = $product->get_weight();
		        } else {
		            $this->log( 'couldn\'t get product from order item:', 'error' );
		            $this->log( print_r( $order_item, true ), 'error' );
		            continue;
		        }

		        if ( $weight ) {
		            $calculated_weight += $quantity * $weight;
		        } else {
		            $this->log( 'weight for product was empty', 'warning' );
		        }
		    }
		    return $calculated_weight;
		}
		
		/**
		 * Determine if the config value 'auto_weight_calculation' is active
		 *
		 * @return boolean
		 */
		public function is_auto_weight_calculation_on() {
		    $auto_weight_calculation = $this->get_option( 'auto_weight_calculation' );
			
			return in_array( (string) $auto_weight_calculation, [ '1', 'on', 'yes', 'true' ] );		    
		}
		
		/**
		 * Create pickup request at shipcloud
		 *
		 * @param mixed $data
		 * @return json
		 */
		public function create_pickup_request( $order_id, $data ) {
			
			$this->log( "create_pickup_request( ".json_encode($order_id).", ".json_encode($data)." )" );
			
			/*
			{
				"carrier": "dpd",
				"pickup": {
					"pickup_earliest_date": "2022-01-28",
					"pickup_earliest_time_hour": "10",
					"pickup_earliest_time_minute": "00",
					"pickup_latest_time_hour": "19",
					"pickup_latest_time_minute": "00",
					"pickup_latest_date": "2022-01-28"
				},
				"shipments": [
					{
						"id": "7f5c32562516cbf4c7df65c051b49c77b0f1b6c2"
					}
				]
			}
			*/
			
			$shipment_id = isset( $data['id'] ) ? $data['id'] : false;
			
			if ( empty( $data['carrier'] ) && ! empty( $shipment_id ) && ! empty( $order_id ) ) {
				$shipment 			= WC_Shipping_Shipcloud_Utils::get_shipment_for_order( $order_id, $shipment_id );
			    $data['carrier']	= $shipment['carrier'];
			}
			
			try {
				
		        $pickup_time = $this->extract_pickup_time( $data );
				if ( ! $pickup_time || is_wp_error( $pickup_time ) ) {
					return $pickup_time;
				}
				
				$pickup_time						= array_shift( $pickup_time );
		        $pickup_request_data 				= [];
				$pickup_request_data['pickup_time'] = $pickup_time;
				
				if ( ! empty( $data['carrier'] ) ) {
					$pickup_request_data['carrier'] = $data['carrier'];
				}
			
				if ( isset( $shipment ) ) {
					$pickup_request_data['carrier'] = $shipment['carrier'];
					if ( ! array_key_exists( 'pickup_request', $shipment ) ) {
						$pickup_request_data['shipments'] = [ [ 'id' => $shipment_id ] ];
					}
				} 
				else if ( ! empty( $data['shipments'] ) ) {
			   		$pickup_request_data['shipments'] = $data['shipments'];
				}
			
				if ( ! empty( $data['pickup_address'] ) ) {
					$pickup_address = array_filter( $data['pickup_address'] );
					// check to see if there was anything more send than the country code
					if ( count( $pickup_address ) > 1 ) {
						$pickup_request_data['pickup_address'] = $pickup_address;
					}
				}
			
				$pickup_request = $this->api->create_pickup_request( $pickup_request_data );

				if ( is_wp_error( $pickup_request ) ) {
					return $pickup_request;
				}
				else if ( ! empty( $pickup_request['carrier_pickup_number'] ) ) {
					
					// request successful
					if ( ! empty( $shipment_id ) ) {
				
						$this->log( sprintf(
							__( 'Pickup request created with id %s for shipment with id %s', 'shipcloud-for-woocommerce' ),
							$pickup_request['id'],
							$shipment_id
						) );

			            // remove shipments element from pickup_request
			            $shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );
			            foreach ( $shipments as $shipment ) {
							if ( $shipment_id === $shipment['id'] ) {
								$updated_shipment = array_merge(
									$shipment,
									[ 'pickup_request' => $pickup_request ]
								);
								update_post_meta( $order_id, 'shipcloud_shipment_data', $updated_shipment, $shipment );
								return $updated_shipment;
								break;
							}
						}
					}
					else if ( is_int( $order_id ) ) {
					
						$ids = [];
						if ( ! empty( $data['shipments'] ) ) {
							foreach ( $data['shipments'] as $s ) {
								$ids[] = $s['id'];
							}
						}
				
			            $shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );
			            foreach ( $shipments as $shipment ) {
							if ( in_array( $shipment['id'], $ids ) ) {
								$updated_shipment = array_merge(
									$shipment,
									[ 'pickup_request' => $pickup_request ]
								);
								update_post_meta( $order_id, 'shipcloud_shipment_data', $updated_shipment, $shipment );
							}
						}
				
						return $updated_shipment;
					
					}
					else if ( is_array( $order_id ) ) { // bulk pickup request
				
						$ids = [];
						if ( ! empty( $data['shipments'] ) ) {
							foreach ( $data['shipments'] as $s ) {
								$ids[] = $s['id'];
							}
						}
				
						$order_ids = $order_id;
						foreach ( $order_ids as $order_id ) {
				            $shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );
				            foreach ( $shipments as $shipment ) {
								if ( in_array( $shipment['id'], $ids ) ) {
									$updated_shipment = array_merge(
										$shipment,
										[ 'pickup_request' => $pickup_request ]
									);
									update_post_meta( $order_id, 'shipcloud_shipment_data', $updated_shipment, $shipment );
								}
							}
						}
				
						return $updated_shipment;
					}
				}
				
		    } catch ( \Exception $e ) {
				return WC_Shipping_Shipcloud_Utils::convert_exception_to_wp_error( $e );
			}
			
			return false;
		}
		
		/**
		 * Deleting a shipment from the database
		 *
		 * @param mixed $shipment_id
		 * @return bool|WP_Error
		 */
		private function delete_shipment_from_db( $shipment_id ) {
		    $order 		= WC_Shipping_Shipcloud_Utils::find_order_by_shipment_id( $shipment_id );
		    $order_id 	= $order->get_order_number();
		    $shipments 	= get_post_meta( $order_id, 'shipcloud_shipment_data' );

		    // Finding shipment key to delete postmeta
		    foreach ( $shipments as $key => $shipment ) {
		        if ( $shipment['id'] == $shipment_id ) {
		            delete_post_meta( $order_id, 'shipcloud_shipment_data', $shipment );
		            delete_post_meta( $order_id, 'shipcloud_shipment_ids', $shipment_id );
					if ( $order !== null ) {
						$order->add_order_note( __( 'shipcloud shipment has been deleted.', 'shipcloud-for-woocommerce') );
					}
		            $this->log( 'Deleted shipment with shipment id ' . $shipment_id . ' belonging to order #' . $order_id );
		        }
		    }
			
			return true;
		}
		
		/**
	     * Returns parses additional services from request form and returns them in an api hash
	     *
		 * @param array $data
		 * @param string $carrier
		 * @param WC_Order $order
	     * @return string
	     */
	    public function additional_services_from_request( $data, $carrier, $order = null ) {
			$additional_services = [];
			$available_services  = WC_Shipping_Shipcloud_Utils::get_additional_services( $carrier );
			foreach ( $data as $additional_service_key => $additional_service_value ) {
				if ( ! in_array( $additional_service_key, $available_services ) ) {
					continue;
				}
				switch ( $additional_service_key ) {
					case 'visual_age_check':
						if ( array_key_exists( 'age_based_delivery', $data )
							&& array_key_exists( 'checked', $data['age_based_delivery'] ) 
							&& array_key_exists( 'minimum_age', $additional_service_value ) 
							&& !empty( $additional_service_value['minimum_age'] )
						) {
							$additional_services[] = array(
								'name' 		 => 'visual_age_check',
								'properties' => array(
									'minimum_age' => $additional_service_value['minimum_age']
								)
							);
						}
						break;
					case 'ups_adult_signature':
						if ( array_key_exists( 'age_based_delivery', $data ) 
							&& array_key_exists( 'checked', $data['age_based_delivery'] ) 
							&& array_key_exists( 'checked', $additional_service_value ) 
						) {
							$additional_services[] = array(
								'name' => 'ups_adult_signature'
							);
						}						
						break;
					case 'saturday_delivery':
						if ( array_key_exists( 'checked', $additional_service_value ) ) {
							$additional_services[] = array(
								'name' => 'saturday_delivery'
							);
						}
						break;
					case 'premium_international':
						$additional_services[] = array(
							'name' => 'premium_international'
						);
						break;
					case 'delivery_date':
						if ( array_key_exists( 'checked', $additional_service_value ) ) {
							$additional_services[] = array(
								'name' 		 => 'delivery_date',
								'properties' => array(
									'date' => $additional_service_value['date']
								)
							);
						}
						break;
					case 'delivery_time':
						if ( array_key_exists( 'checked', $additional_service_value ) 
							&& array_key_exists( 'timeframe', $additional_service_value )
						) {
							$selected_option = $additional_service_value['timeframe'];
							$time_of_day_earliest = substr($selected_option, 0, 2).':00';
							$time_of_day_latest = substr($selected_option, 2, 2).':00';

							$additional_services[] = array(
								'name' 		 => 'delivery_time',
								'properties' => array(
									'time_of_day_earliest' => $time_of_day_earliest,
									'time_of_day_latest' => $time_of_day_latest
								)
							);
						}
						break;
					case 'drop_authorization':
						if ( array_key_exists( 'checked', $additional_service_value ) 
							&& array_key_exists( 'message', $additional_service_value ) 
							&& isset( $additional_service_value['message'] )
						) {
							$additional_services[] = array(
								'name' 		 => 'drop_authorization',
								'properties' => array(
									'message' => $additional_service_value['message']
								)
							);
						}
						break;
					case 'dhl_endorsement':
						if ( array_key_exists( 'checked', $additional_service_value ) ) {
							$additional_services[] = array(
								'name' 		 => 'dhl_endorsement',
								'properties' => array(
									'handling' => $additional_service_value['handling']
								)
							);
						}
						break;
					case 'dhl_named_person_only':
						if ( array_key_exists( 'checked', $additional_service_value ) ) {
							$additional_services[] = array(
								'name' => 'dhl_named_person_only'
							);
						}
						break;
					case 'cash_on_delivery':
						if ( array_key_exists( 'checked', $additional_service_value ) ) {
							$bank_name = array_key_exists( 'bank_name', $additional_service_value ) ? $additional_service_value['bank_name'] : "";
							$bank_code = array_key_exists( 'bank_code', $additional_service_value ) ? $additional_service_value['bank_code'] : "";
							$bank_account_holder = array_key_exists( 'bank_account_holder', $additional_service_value ) ? $additional_service_value['bank_account_holder'] : "";
							$bank_account_number = array_key_exists( 'bank_account_number', $additional_service_value ) ? $additional_service_value['bank_account_number'] : "";
							$reference = array_key_exists( 'reference1', $additional_service_value ) ? $additional_service_value['reference1'] : "";

              if ( $reference == "" ) {
                $global_reference_number = $this->get_option( 'global_reference_number' );
                $order_id = $this->order_id;
                if ( WC_Shipping_Shipcloud_Utils::shipcloud_admin_is_on_single_order_page() && ! empty( $order_id ) ) {
                  if ( has_shortcode( $global_reference_number, 'shipcloud_orderid' ) ) {
                    $global_reference_number = str_replace( '[shipcloud_orderid]', $order_id, $global_reference_number );
                  }
                }
                if ( $global_reference_number != "") {
                  $reference = $global_reference_number;
                }
              }
              $amount = array_key_exists( 'amount', $additional_service_value ) && $additional_service_value['amount'] != "" ? $additional_service_value['amount'] : $this->get_wc_order()->get_total();
              $currency = array_key_exists( 'currency', $additional_service_value ) && $additional_service_value['currency'] != "" ? $additional_service_value['currency'] : 'EUR';

							$cod_array = array(
								'name' 		 => 'cash_on_delivery',
								'properties' => array(
									'amount' => $amount,
									'currency' => $currency,
								)
							);
							
							switch( $carrier ) {
								case 'cargo_international':
									$cod_array['properties']['bank_account_number'] = $bank_account_number;
									$cod_array['properties']['bank_code'] = $bank_code;
									break;
								case 'dhl':
									$cod_array['properties']['reference1'] = $reference;
									$cod_array['properties']['bank_account_holder'] = $bank_account_holder;
									$cod_array['properties']['bank_name'] = $bank_name;
									$cod_array['properties']['bank_account_number'] = $bank_account_number;
									$cod_array['properties']['bank_code'] = $bank_code;
									break;
								case 'gls':
									$cod_array['properties']['reference1'] = $reference;
									break;
							}

							$additional_services[] = $cod_array;
						}
						break;
					case 'gls_guaranteed24service':
						if ( array_key_exists( 'checked', $data['gls_guaranteed24service'] ) 
							&&	array_key_exists( 'checked', $additional_service_value )
						) {
							$additional_services[] = array(
								'name' => 'gls_guaranteed24service'
							);
						}
						break;
					case 'dhl_parcel_outlet_routing':
						if ( array_key_exists( 'checked', $additional_service_value ) ) {
							/*
							$props = [];
							if ( array_key_exists( 'email', $additional_service_value ) 
								&& ! empty( $additional_service_value['email'] )
							) {
								$props['email'] = $additional_service_value['email'];
							} else {
								$props['email'] = $order->get_email_for_notification();
							}
							*/
							$additional_services[] = array(
								'name' 		 => 'dhl_parcel_outlet_routing',
								'properties' => array(
									'email' => $this->get_email_for_notification(),
								)
							);
						}
						break;
					case 'advance_notice':
						if ( array_key_exists( 'checked', $additional_service_value ) ) {
							$advance_notice_email = $advance_notice_phone = $advance_notice_sms = '';

							$props = [];

							if ( array_key_exists( 'email_checkbox', $additional_service_value ) 
								&& 'email_checkbox' == $additional_service_value['email_checkbox']
							) {
								
								if ( array_key_exists( 'email', $additional_service_value ) 
									&& '' != $additional_service_value['email']
								) {
									$props['email'] = $additional_service_value['email'];
								} else {
                  $props['email'] = $this->get_email_for_notification();
								}
							}
							
							if ( array_key_exists( 'sms_checkbox', $additional_service_value ) 
								&& 'sms_checkbox' == $additional_service_value['sms_checkbox']
							) {
								
								if ( array_key_exists( 'sms', $additional_service_value ) 
									&& '' != $additional_service_value['sms']
								) {
									$props['sms'] = $additional_service_value['sms'];
								} else {
                  $props['sms'] = $this->get_phone();
								}
							}
							
							if ( array_key_exists( 'phone_checkbox', $additional_service_value ) 
								&& 'phone_checkbox' == $additional_service_value['phone_checkbox']
							) {
								
								if ( array_key_exists( 'phone', $additional_service_value ) 
									&& '' != $additional_service_value['phone']
								) {
									$props['phone'] = $additional_service_value['phone'];
								} else {
									$props['phone'] = $this->get_phone();
								}
							}

							if ( ! empty( $props ) ) {
								$additional_services[] = array(
									'name' 		 => 'advance_notice',
									'properties' => $props
								);
							}
						}
						break;
				}
			}

			return $additional_services;
		}
		
		/**
		 * Getting allowed carriers
		 * 
		 * @return array
		 */
		public function get_allowed_carriers() {
			return $this->allowed_carriers;
		}

		/**
		 * Getting shipping method name
		 *
		 * @return string
		 */
		public function get_shipping_method_name() {
		    if ( ! $this->get_order() && ! $this->get_wc_order() ) {
		        return '';
		    }

		    return $this->get_wc_order()->get_shipping_method();
		}
		
        /**
         * Getting option carrier email notification enabled
         *
         * @return bool
         */
		public function carrier_email_notification_enabled() {
		    return $this->email_notification_enabled() 
				&& WC_Shipping_Shipcloud_Utils::carrier_email_notification_enabled();
		}
		
		/**
		 * Getting option shipcloud email notification enabled
         *
         * @return bool
		 */
		public function shipcloud_email_notification_enabled() {
		    return $this->email_notification_enabled() 
				&& WC_Shipping_Shipcloud_Utils::shipcloud_email_notification_enabled();
		}
		
		/**
		 * Getting option email notification enabled by customer
         *
         * @return bool
		 */
		public function email_notification_enabled() {
		    $notification_enabled = get_post_meta( $this->order_id, 'shipcloud_parcel_delivery_notification', true );
			
		    return in_array( (string) $notification_enabled, [ '1', 'on', 'yes', 'true' ] );	
		}
		
		/**
		 * Receive the description
		 *
		 * @return string|null
		 */
		public function get_description() {
		    $other = get_post_meta( $this->order_id, 'shipcloud_other', true );

		    if ( ! isset( $other['description'] ) ) {
		        return null;
		    }

		    return $other['description'];
		}
		
		/**
		 * Receive the global reference number
		 *
		 * @return string|null
		 */
		public function get_global_reference_number() {
		    $global_reference_number = $this->get_option( 'global_reference_number' );
		    $order_id = $this->order_id;
		    if ( WC_Shipping_Shipcloud_Utils::shipcloud_admin_is_on_single_order_page() && ! empty( $order_id ) ) {
		        if ( has_shortcode( $global_reference_number, 'shipcloud_orderid' ) ) {
		            $global_reference_number = str_replace( '[shipcloud_orderid]', $order_id, $global_reference_number );
		        }
		    }

		    return $global_reference_number;
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

    WC_Shipping_Shipcloud_Order::get_instance();
}
