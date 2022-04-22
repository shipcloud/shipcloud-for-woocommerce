<?php

/**
 * WC_Shipping_Shipcloud_Webhook creates a webhook listening for 
 * shipcloud notifications and processing them accordingly.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

use shipcloud\phpclient\model\EventType;

if ( ! defined( 'ABSPATH' ) ) { exit(); }

if ( ! class_exists( 'WC_Shipping_Shipcloud_Webhook' ) ) {
	
	class WC_Shipping_Shipcloud_Webhook {
		
		private $api;
		
		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			
			$this->api = WC_Shipping_Shipcloud_API_Adapter::get_instance();
			
		    // listener for shipcloud WebHook calls
		    add_action( 'woocommerce_api_shipcloud', array( $this, 'process_webhook_call' ) );
		}
		
	    /**
	     * Processing shipcloud webhook calls
	     *
	     * @return void
	     */
	    public function process_webhook_call() {
			
			$raw_post_data = file_get_contents( 'php://input' );
			
			if ( ! empty( $raw_post_data ) ) {
				
		        $event		= json_decode( $raw_post_data, false ); // returns an object
		        $webhook_id	= get_option( 'woocommerce_shipcloud_catch_all_webhook_id', false );
				
		        if ( empty( $webhook_id ) ) {
				    echo sprintf(
		                __(
		                    'You haven\'t activated the option for event notification in your <a href="%s">shipcloud for woocommerce settings</a>.',
		                    'shipcloud-for-woocommerce'
		                ),
		                admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipcloud' )
		            );
				} 
				else if ( empty ( $event ) ) {
				    echo __(
		                'WooCommerce is ready to handle shipcloud webhook calls',
		                'shipcloud-for-woocommerce'
		            );
				} 
				else {
					
				    if ( ( json_last_error() !== JSON_ERROR_NONE ) ) {
		                $this->log( sprintf( 'Webhook: JSON error (%s).', json_last_error_msg() ), 'error' );
		                exit();
		            }
					
		            if ( ! property_exists( $event, 'data' ) || ! property_exists( $event->data, 'object_type' ) ) {
		                $this->log( 'Webhook got wrong data format.', 'error' );
		                exit();
		            }
					
					// allow further enhancements via object type
					switch ( $event->data->object_type ) {
						case 'shipment':
							$this->process_shipment_event( $event );
							break;
						
						default:
							$this->process_all_event( $event );
							break;
					}
				}
				exit();
			}
			else {
	            echo __(
	                'WooCommerce is ready to handle shipcloud webhook calls but raw_post_data is empty',
	                'shipcloud-for-woocommerce'
	            );
				exit();
			}
	    }
		
		/**
		 * Processes a shipment event notified via webhook.
		 *
		 * @param object	$event	The webhook event.
		 * @return void
		 */
		private function process_shipment_event( $event ) {
			
			global $wpdb;
			
			if ( ! property_exists( $event->data, 'id' ) ) {
                $this->log( 'Webhook got wrong data format.', 'error' );
                exit();
            }
			
			$shipment_id = $event->data->id;

            if ( empty( $shipment_id ) ) {
                $this->log( 'Webhook: Shipment ID not given.', 'error' );
                exit();
            }
		
			$stmnt 	  = "SELECT p.ID 
					  	 FROM {$wpdb->posts} AS p, {$wpdb->postmeta} AS pm 
					  	 WHERE p.ID = pm.post_ID 
						 AND pm.meta_key=%s 
						 AND pm.meta_value=%s";
			
            $sql 	  = $wpdb->prepare( $stmnt, 'shipcloud_shipment_ids', $shipment_id );
			$order_id = $wpdb->get_var( $sql );

            if ( empty( $order_id ) ) {
                $this->log( sprintf( 'Webhook found no order_id for Shipment #%s. Maybe already deleted.', $shipment_id ), 'warning' );
                exit();
            } 
			
			$message = 'Webhook: Got status event "%s" for Shipment ID %s (Order ID %s)';
            $this->log( sprintf( $message, $event->type, $shipment_id, $order_id ) );
			
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$status_string = WC_Shipping_Shipcloud_Utils::get_status_string( $event->type );
				$order->add_order_note( 
					sprintf( 
						__( 'Shipment status changed to: %s', 'shipcloud-for-woocommerce' ), 
						$status_string 
					) 
				);
			}
            
            update_post_meta( $order_id, 'shipment_' . $shipment_id . '_status', $event->type );
			
			if ( EventType::SHIPMENT_STATUS_DELETED === $event->type ) {
				
				/* shipment has been deleted at shipcloud.io and needs to be removed from database */
				do_action( 'shipcloud_shipment_before_deletion', $order_id, $shipment_id, $event );
				
				$this->delete_shipment_from_database( $order_id, $shipment_id, $event );
				
				do_action( 'shipcloud_shipment_deleted', $order_id, $shipment_id, $event );
				
				exit();
			}
			
			/* 
			 * event is of type shipment.tracking.* 
			 */
			
            $shipment = $this->api->get_shipment( $shipment_id );
			if ( ! empty( $shipment ) ) {
				
				$prev_shipment_data = WC_Shipping_Shipcloud_Utils::get_shipment_for_order( $order_id, $shipment_id );
                $shipment_data 		= array_merge( $prev_shipment_data, $shipment );
				$shipment_data 		= WC_Shipping_Shipcloud_Utils::enhance_shipment_data( $shipment );
                update_post_meta( $order_id, 'shipcloud_shipment_data', $shipment_data, $prev_shipment_data );
			
                $tracking_event = $this->get_tracking_event( $shipment_data, $event->occured_at );
				if ( ! empty( $tracking_event ) ) {
                    $arr = array(
                        'occured_at' => $event->occured_at,
                        'type' 		 => $event->type
                    );
                    $meta = array_merge( $arr, $tracking_event );
					
					add_post_meta( $order_id, 'shipment_' . $shipment_id . '_trackingevent', $meta );
                }
				else {
					$this->log( "no tracking_event found" );
				}

                /**
                * Hooks in for further functions after status changes
                */
                do_action( 'shipcloud_shipment_tracking_change', $order_id, $shipment_id, $event->type );


                /**
                * shipcloud_shipment_tracking_default action
                *
                * @param int $order_id ID of the order.
                * @param int $shipment_id ID of the shipment.
                */
                do_action( 'shipcloud_shipment_tracking_default', $order_id, $shipment_id );

                $shipment_action = 'shipcloud_' . str_replace( $event->type, '.', '_' );

                /**
                * shipcloud_shipment_{{ shipment type }} action
                *
                * @param int $order_id ID of the order.
                * @param int $shipment_id ID of the shipment.
                */
                do_action( $shipment_action, $order_id, $shipment_id );
            }
			else {
				$this->log( sprintf(
					'Webhook: Got empty result from API searching for Shipment ID %s.',
					$shipment_id
				) );
			}
			
			exit();
		}
		
		/**
		 * Processes a not related event notified via webhook.
		 *
		 * @param object	$event	The webhook event.
		 * @return void
		 */
		private function process_all_event( $event ) {
			
			$this->log( $event );
			
			/**
			{
				"id": "3e1e327c-955c-46ee-b468-9bda6b5ebf4a",
				"occured_at": "2021-10-14T16:31:11+02:00",
				"type": "example.event",
				"data": {
					"id": "es40a6e7a83ea8253f54eb414606626172b523d8",
					"url": "/v1/shipments/es40a6e7a83ea8253f54eb414606626172b523d8",
					"object_type": "shipment"
				}
			}
			*/
			
		}
		
		/**
		 * Delete shipment data from database.
		 *
		 * @param string 	$order_id 		The order ID.
		 * @param string 	$shipment_id	The shipment ID.
		 * @param string	$event			The fired event.
		 * @return void
		 */
		private function delete_shipment_from_database( $order_id, $shipment_id, $event ) {
			$order 		= WC_Shipping_Shipcloud_Utils::find_order_by_shipment_id( $shipment_id );
		    $shipments 	= get_post_meta( $order_id, 'shipcloud_shipment_data' );

		    // Find shipment key to delete postmeta
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
		}
		
		/**
		 * Extract matching tracking event from shipment.
		 *
		 * @param array 	$shipment 		The shipment.
		 * @param string 	$timestamp    	The timestamp to match.
		 * @return array|bool
		 */
		private function get_tracking_event( $shipment, $timestamp ) {
			$packages = !empty( $shipment['packages'] ) ? $shipment['packages'] : false;
			if ( ! empty( $packages ) ) {
				foreach( $packages as $package ) {
					$tracking_events = ! empty( $package['tracking_events'] ) ? $package['tracking_events'] : [];
					foreach ( $tracking_events as $tracking_event ) {
						if ( $tracking_event['timestamp'] === $timestamp ) {
							$this->log( "found tracking_event " . json_encode( $tracking_event ) );
							return $tracking_event;
						}
					}
				}
			}
			else {
				$this->log( "\$packages is empty", "error" );
			}
			
			return false;
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
	
	add_action( 'woocommerce_init', function() {
		new WC_Shipping_Shipcloud_Webhook();
	} );
	
}
