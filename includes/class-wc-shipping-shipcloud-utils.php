<?php

/**
 * WC_Shipping_Shipcloud_Utils provides several common utility functions.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

use shipcloud\phpclient\Logger;
use shipcloud\phpclient\model\EventType;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud_Utils' ) ) {
	
	class WC_Shipping_Shipcloud_Utils {
		
		/**
	     * Carrier names that need special handling
	     */
	    const CARRIERS_WITH_UNDERSCORE = array(
			'angel_de',
			'cargo_international',
			'db_schenker',
			'dhl_express',
			'parcel_one'
	    );
		
		/**
		 * Construct
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_footer', 						array( $this, 'show_admin_notices' ) );
			add_action( 'admin_footer', 						array( $this, 'clear_success_admin_notices' ), 99 );
			
			add_action( 'wc_shipcloud_add_admin_notice', 		array( $this, 'add_admin_notice' ), 10, 3 );
			add_action( 'wc_shipcloud_remove_admin_notice',		array( $this, 'remove_admin_notice' ), 10, 2 );
			add_action( 'wc_shipcloud_clear_admin_notices', 	array( $this, 'clear_all_admin_notices' ) );
		}
		
		/**
		 * Returns the object of the shipping method
		 *
		 * @return bool|WC_Shipcloud_Shipping
		 */
		public static function get_shipping_method() {
			$shipping_methods = WC()->shipping()->get_shipping_methods();

			if ( ! array_key_exists( WC_SHIPPING_SHIPCLOUD_NAME, $shipping_methods ) ) {
				return false;
			}

			return $shipping_methods[WC_SHIPPING_SHIPCLOUD_NAME];
		}
		
		/**
		 * Adds a certain admin notice.
		 *
		 * @param string $message
		 * @param string $type
		 * @param bool $dismissible
		 * @return void
		 */
		public static function add_admin_notice( $message = '', $type = 'info', $dismissible = true ) {
			if ( !empty( $message ) ) {
				$is_dismissible = $dismissible ? 'is-dismissible' : '';
			    $admin_notices 	= get_transient( 'shipcloud_notices' );
			    if ( !isset( $admin_notices ) ) {
			      	 $admin_notices = [];
			    }
			    $admin_notices[ md5( $type . ':' . $message ) ] = array(
					'type' 			=> $type,
					'message' 		=> $message,
					'dismissible' 	=> $is_dismissible,
			    );
			    set_transient( 'shipcloud_notices', $admin_notices );
			}
		}
		
		/**
		 * Removes a certain admin notice.
		 *
		 * @param string $message
		 * @param string $type
		 * @return void
		 */
		public static function remove_admin_notice( $message = '', $type = 'info' ) {
			$admin_notices = get_transient( 'shipcloud_notices' );
			if ( isset( $admin_notices[ md5( $type . ':' . $message ) ] ) ) {
				unset( $admin_notices[ md5( $type . ':' . $message ) ] );
			}
			set_transient( 'shipcloud_notices', $admin_notices );
		}
		
		/**
		 * Displays admin notices.
		 *
		 * @return string
		 */
		public static function show_admin_notices() {
			$admin_notices = get_transient( 'shipcloud_notices' );
			if ( empty( $admin_notices ) ) {
				$admin_notices = [];
			}
			foreach ( $admin_notices as $notice ) {
				echo '<div class="notice notice-' . esc_attr( $notice['type'] ) . ' ' . $notice['dismissible'] . '"><p>' . $notice['message'] . '</p></div>';
			}
			self::clear_all_admin_notices();
		}
		
		/**
		 * Clears all admin notices.
		 *
		 * @return void
		 */
		public static function clear_all_admin_notices() {
			delete_transient( 'shipcloud_notices' );
		}
		
		/**
		 * Clears success admin notices.
		 *
		 * @return void
		 */
		public static function clear_success_admin_notices() {
			$admin_notices = get_transient( 'shipcloud_notices' );
			if ( empty( $admin_notices ) ) {
				return true;
			}
			$remaining_notices = [];
			foreach ( $admin_notices as $key => $notice ) {
				if ( 'success' !== $notice['type'] ) {
					$remaining_notices[ $key ] = $notice;
				}
			}
			set_transient( 'shipcloud_notices', $remaining_notices );
		}
		
		/**
		 * Getting shipment status string
		 *
		 * @param string $status
		 * @return string $message
		 */
		public static function get_status_string( $status ) {
		
			$message = __( 'Not available yet', 'shipcloud-for-woocommerce' );

			$status_mapping = array(
				EventType::ALL 											=> __( 'All events', 'shipcloud-for-woocommerce' ),
				EventType::EXAMPLE_EVENT								=> __( 'Example event', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_ALL									=> __( 'Shipment all', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_STATUS_DELETED						=> __( 'Shipment status deleted', 'shipcloud-for-woocommerce' ),
	    		EventType::SHIPMENT_TRACKING_ALL						=> __( 'Shipment tracking all', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_LABEL_CREATED				=> __( 'Shipping label created', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_PICKED_UP					=> __( 'Package(s) picked up', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_TRANSIT					=> __( 'Package(s) in transit', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_OUT_FOR_DELIVERY 			=> __( 'Package(s) out for delivery', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_DELIVERED					=> __( 'Package(s) delivered', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_AWAITS_PICKUP_BY_RECEIVER 	=> __( 'Package(s) awaiting pickup by receiver', 'shipcloud-for-woocommerce' ),
				
				EventType::SHIPMENT_TRACKING_CANCELED 					=> __( 'Package(s) delivery has been canceled', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_DELAYED					=> __( 'Package(s) delayed', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_NOT_DELIVERED				=> __( 'Package(s) not delivered', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_NOTIFICATION				=> __( 'Carrier internal notification. Tracking events within the shipment will carry more elaborate information.', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_UNKNOWN					=> __( 'Tracking status is unknown', 'shipcloud-for-woocommerce' ),
				EventType::SHIPMENT_TRACKING_EXCEPTION					=> __( 'Tracking exception', 'shipcloud-for-woocommerce' ),
			);

			if ( isset( $status_mapping[ $status ] ) ) {
				$message = $status_mapping[ $status ];
			}

			return $message;
		}
		
		/**
		 * Getting shipment status icon
		 *
		 * @param string $status
		 * @return string $html
		 */
		public static function get_status_icon( $status ) {
			$icon = 'fa-angle-down';

			switch( $status ) {
				case 'awaits_pickup_by_receiver':
					$icon = 'fa-building';
					break;
				case 'delayed':
					$icon = 'fa-clock';
					break;
				case 'delivered':
					$icon = 'fa-box-open';
					break;
				case 'exception':
					$icon = 'fa-exclamation';
					break;
				case 'label_created':
					$icon = 'fa-barcode';
					break;
				case 'not_delivered':
					$icon = 'fa-times-circle';
					break;
				case 'notification':
					$icon = 'fa-envelope';
					break;
				case 'out_for_delivery':
					$icon = 'fa-shipping-fast';
					break;
				case 'picked_up':
					$icon = 'fa-truck-loading';
					break;
				case 'transit':
					$icon = 'fa-road';
					break;
				case 'unknown':
					$icon = 'fa-question';
					break;
			}

			$html = '<div class="shipcloud_tracking__timeline shipcloud_tracking__timeline--'.$status.'">';
			$html .= '<i class="fa-solid '.$icon.'"></i>';
			$html .= '</div>';
			echo $html;
		}
		
		/**
		 * Get carrier display_name from name
		 *
		 * @param string $name
		 * @return string $display_name
		 */
		public static function get_carrier_display_name( $carrier_name ) {
			$api 	  = WC_Shipping_Shipcloud_API_Adapter::get_instance();
			$carriers = $api->get_carriers();
			foreach ( $carriers as $carrier ) {
				if ( $carrier['name'] === $carrier_name ) {
					return $carrier['display_name'];
				}
			}

			return $carrier_name;
		}
		
		/**
		 * Getting display name for a carrier (short name) including service
		 *
		 * @param string $carrier_name
		 * @return string|array
		 */
		public static function get_carrier_display_name_short( $carrier_name ) {
			$carrier_name_arr = self::disassemble_carrier_name( $carrier_name );
			if ( ! empty( $carrier_name_arr ) ) {
				return self::get_carrier_display_name( $carrier_name_arr['carrier'] ) . ' - ' . self::get_service_name( $carrier_name_arr['service'] );
			}
			
			return $carrier_name;
		}
		
		/**
		 * Splitting carrier name into API usable carrier name and service name
		 *
		 * @param string $carrier_name
		 * @return array
		 */
		public static function disassemble_carrier_name( $carrier_name ) {
			
			if ( empty( $carrier_name ) ) {
				return false;
			}
			
			$carrier_arr = false;
			if ( is_string( $carrier_name ) ) {
				$carrier_arr = explode( '_', $carrier_name );
			}
			else {
				self::log( gettype( $carrier_name ) . ": " . json_encode( $carrier_name ) );
			}
			
			$carrier = $carrier_name;
			$service = '';
			
			if ( is_array( $carrier_arr ) ) {
				$carrier_with_underscore_name = $carrier_arr[0].'_'.$carrier_arr[1];
				if ( in_array( $carrier_with_underscore_name, self::CARRIERS_WITH_UNDERSCORE ) ) {
					$carrier = $carrier_with_underscore_name;
					$array_start_index = 2;
				} else {
					$carrier = $carrier_arr[0];
					$array_start_index = 1;
				}
			
				for ( $i = $array_start_index; $i < count( $carrier_arr ); $i ++ ) {
					$service .= $i == $array_start_index ? '' : '_';
					$service .= $carrier_arr[ $i ];
				}
			}
			
			return array(
				'carrier' => $carrier,
				'service' => $service
			);
		}
		
		/**
		 * Extracts technical carrier name by carrier display name.
		 * 
		 * <pre>
		 * 	{
		 * 		"carrier": "dhl",
		 * 		"service": "standard"
		 * 	}
		 * </pre>
		 * 
		 * @param string  $carrier_display_name
		 * @return array|bool
		 */
		public static function get_carrier_name_by_display_name( $carrier_display_name ) {
			$api 	  = WC_Shipping_Shipcloud_API_Adapter::get_instance();
			$carriers = $api->get_carrier_list();
			foreach( $carriers as $carrier ) {
				if ( $carrier['display_name'] === $carrier_display_name ) {
					return $carrier['name'];
				}
			}
			
			return false;
		}
		
		/**
		 * Gets carrier providing pickup service.
		 * 
		 * @return array The carrier providing pickup services.
		 */
		public static function get_carrier_providing_pickup_service() {
			$pickup_carriers = include( dirname( __FILE__ ) . '/data/data-carrier-providing-pickup.php' );
			
			return $pickup_carriers;
		}
		
		/**
		 * Gets carrier services as key - value list.
		 * 
		 * @return array The carrier services.
		 */
		public static function get_carrier_services_list() {
			$services = self::get_carrier_services();
			$carrier_services = [];
			foreach( $services as $name => $service ) {
				$carrier_services[$name] = $service['name'];
			}
			
			return $carrier_services;			
		}
		
		/**
		 * Getting service name by carrier service slug
		 *
		 * @param string $slug
		 * @return string
		 */
		public static function get_service_name( $slug ) {
			$carrier_services = self::get_carrier_services();
			if ( array_key_exists( $slug, $carrier_services ) ) {
				return $carrier_services[ $slug ]['name'];
			}
			
			return $slug;
		}
		
		/**
		 * Gets carrier services.
		 * 
		 * @return array The carrier services.
		 */
		public static function get_carrier_services() {
			$carrier_services = include( dirname( __FILE__ ) . '/data/data-carrier-services.php' );
			
			return $carrier_services;
		}
		
		/**
		 * Gets package types.
		 * 
		 * @return array The package types.
		 */
		public static function get_package_types() {
			$package_types = include( dirname( __FILE__ ) . '/data/data-carrier-package-types.php' );
			
			return $package_types;
		}
		
		/**
		 * Gets carrier contents types.
		 * 
		 * @return array The carrier contents types.
		 */
		public static function get_carrier_contents_types() {
			$contents_types = include( dirname( __FILE__ ) . '/data/data-carrier-contents-types.php' );
			
			return $contents_types;
		}
		
		/**
		 * Gets customs declaration contents types.
		 * 
		 * @return array The contents types.
		 */
		public static function get_customs_declaration_contents_types() {
			
			return self::get_carrier_contents_types();
		}
		
		/**
		 * Gets carrier label formats.
		 * 
		 * @return array The carrier label formats.
		 */
		public static function get_carrier_label_formats() {
			$label_formats = include( dirname( __FILE__ ) . '/data/data-carrier-label-formats.php' );
			
			return $label_formats;
		}
		
		/**
		 * Getting the carrier tracking URL by specifying the carrier name and tracking number
		 *
		 * @param string $carrier
		 * @param string $carrier_tracking_no
		 * @return string URL of the carrier tracking page
		 */
		public static function get_carrier_tracking_url( $carrier, $carrier_tracking_no ) {
			switch ($carrier) {
				case 'dhl':
				case 'dhl_express':
					return 'https://nolp.dhl.de/nextt-online-public/set_identcodes.do?idc='.
						$carrier_tracking_no.'&rfn=&extendedSearch=true';
				case 'dpd':
					return 'https://tracking.dpd.de/parcelstatus?query='.$carrier_tracking_no.
						'&locale=de_DE';
				case 'fedex':
					return 'https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber='.
						$carrier_tracking_no;
				case 'gls':
					return 'https://gls-group.eu/DE/de/paketverfolgung?match='.$carrier_tracking_no;
				case 'go':
					return 'https://order.general-overnight.com/ax4/control/customer_service?shId='.
						$carrier_tracking_no.'&hash=JMJyKOfE1v&lang=de&ActionCollectInformation=GO%21';
				case 'hermes':
					return 'https://tracking.hermesworld.com/?TrackID='.$carrier_tracking_no;
				case 'iloxx':
					return 'http://www.iloxx.de/net/einzelversand/tracking.aspx?ix='.$carrier_tracking_no;
				case 'tnt':
					return 'https://www.tnt.com/express/de_de/site/home/applications/tracking.html?cons='.
						$carrier_tracking_no.'&searchType=CON';
				case 'ups':
					return 'http://wwwapps.ups.com/WebTracking/processInputRequest?sort_by=status&'.
						$carrier_tracking_no.'=1&TypeOfInquiryNumber=T&loc=de_DE&InquiryNumber1='.
						$carrier_tracking_no.'&track.x='.$carrier_tracking_no.'&track.y=0';
			}
		}
		
		/**
		 * Turn package type to readable label.
		 * 
		 * @param string $slug
		 * @return string The proper label or the slug itself if no label was found.
		 */
		public static function get_package_label( $slug ) {
			$package_types = self::get_package_types();
			if ( array_key_exists( $slug, $package_types ) ) {
				return $package_types[ $slug ];
			}

			return $slug;
		}
		
		/**
		 * Gets Parcel Templates
		 *
		 * @param array $args
		 * @return array $parcel_templates Parcel templates in an array
		 */
		public static function get_parcel_templates( $args = [] ) {
			$defaults = array(
				'posts_per_page' => - 1,
				'orderby'        => '',
				'order'          => '',
				'include'        => '',
				'exclude'        => ''
			);

			$args                = wp_parse_args( $args, $defaults );
			$args[ 'post_type' ] = WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE;

			$posts            = get_posts( $args );
			$parcel_templates = [];

			foreach ( $posts as $key => $post ) {
				$parcel_templates[ $key ]                          = (array) $post;
				$parcel_templates[ $key ][ 'values' ][ 'carrier' ] = get_post_meta( $post->ID, 'carrier', true );
				$parcel_templates[ $key ][ 'values' ][ 'width' ]   = get_post_meta( $post->ID, 'width', true );
				$parcel_templates[ $key ][ 'values' ][ 'height' ]  = get_post_meta( $post->ID, 'height', true );
				$parcel_templates[ $key ][ 'values' ][ 'length' ]  = get_post_meta( $post->ID, 'length', true );
				$parcel_templates[ $key ][ 'values' ][ 'weight' ]  = get_post_meta( $post->ID, 'weight', true );
				$parcel_templates[ $key ][ 'values' ][ 'shipcloud_is_standard_parcel_template' ]  = get_post_meta( $post->ID, 'shipcloud_is_standard_parcel_template', true );
			}

			return $parcel_templates;
		}
		
	    /**
		 * Gets available shipping services.
		 * 
	  	 * @param array $shipcloud_carriers
	  	 * @return array
	  	 */
		public static function get_available_shipping_services( $shipcloud_carriers ) {
			
			if ( empty( $shipcloud_carriers ) ) {
				self::log( 'shipcloud_carriers is empty!', 'error' );
				return [];
			}
			
			$allowed_carriers 			  = self::get_option( 'allowed_carriers' );
			$carriers_with_pickup_service = self::get_carrier_providing_pickup_service();
			$carriers_with_pickup_object  = $carriers_with_pickup_service['carriers_with_pickup_object'];
						
			$carriers = [];
			if ( is_array( $allowed_carriers ) ) {
				foreach ( $shipcloud_carriers as $shipcloud_carrier ) {
					$allowed  = false;
					$services = [];
					foreach ( $shipcloud_carrier['services'] as $carrier_service ) {
						$carrier_service_combination = $shipcloud_carrier['name'] . "_" . $carrier_service;
						if ( in_array( $carrier_service_combination, $allowed_carriers ) ) {
							$allowed 	= true;
							$services[] = $carrier_service;
						}
					}
					if ( $allowed ) {
						
						// @see https://app.shortcut.com/woocommerce/story/10867/pickup-inputfelder-fehlen-bei-carriern-die-diese-info-bei-erstellung-benötigen
						if ( in_array( $shipcloud_carrier['name'], $carriers_with_pickup_object ) ) {
							if ( $shipcloud_carrier['name'] === 'dhl_express' ) {
								$regular_pickup = self::get_option( 'dhl_express_regular_pickup', false );
								if ( ! in_array( (string) $regular_pickup, [ '1', 'on', 'yes', 'true' ] ) ) {
									$shipcloud_carrier['additional_services'][] = 'pickup_object';
								}
							}
							else {
								$shipcloud_carrier['additional_services'][] = 'pickup_object';
							}
						}
						$carriers[ $shipcloud_carrier['name'] ] = new WC_Shipping_Shipcloud_Carrier( $shipcloud_carrier, $services );
					}
				}
			}
			
			asort( $carriers );
			
			return array_values( $carriers );
		}
		
	    /**
		 * Gets additional services for carrier.
		 * 
	  	 * @param array $shipcloud_carriers
	  	 * @return array
	  	 */
		public static function get_additional_services( $carrier_name = '' ) {
			$api 	  = WC_Shipping_Shipcloud_API_Adapter::get_instance();
			$carriers = $api->get_carriers();
			foreach ( $carriers as $carrier ) {
				if ( $carrier['name'] === $carrier_name ) {
					return $carrier['additional_services'];
				}
			}
			
			return false;
		}
		
		public static function filter_additional_services( $data, $carrier ) {
			$additional_services = [];
			$available_services  = self::get_additional_services( $carrier );
			foreach ( $data as $additional_service ) {
				if ( in_array( $additional_service['name'], $available_services ) ) {
					$additional_services[] = $additional_service;
				}
			}
			
			return $additional_services;
		}
		
	    /**
		 * Find order by shipment id.
		 * 
	  	 * @param $shipment_id
	  	 * @return null|\WC_Order
	  	 */
		public static function find_order_by_shipment_id( $shipment_id ) {
			$orders = get_posts(
				array(
					'post_type'    => ['order', 'shop_order'],
					'post_status'  => 'any',
					'meta_key'     => 'shipcloud_shipment_data',
					'meta_value'   => $shipment_id,
					'meta_compare' => 'LIKE',
				)
			);

			if ( ! $orders || is_wp_error( $orders ) ) {
				return null;
			}

			// Iterate matching orders and check for the exact match.
			foreach ( $orders as $order ) {
				if ( self::get_shipment_for_order( $order->ID, $shipment_id ) ) {
					// This order has the shipment we are searching for.
					return wc_get_order( $order );
				}
			}
			
			return null;
		}
		
	    /**
		 * Get array representing shipment.
		 * 
		 * @param $order_id
		 * @param $shipment_id
		 * @return array
		 */
		public static function get_shipment_for_order( $order_id, $shipment_id ) {
			
			$shipment = self::get_order_meta_shipment_data( $order_id, $shipment_id );
			if ( $shipment && array_key_exists( 'date_created', $shipment ) ) {
				$shipment = self::convert_postmeta_to_shipment( $shipment );
			}
			
			return $shipment;
		}
		
	    /**
		 * Gets order meta shipment data.
		 * 
		 * @param string $order_id
		 * @param string $shipment_id
		 * @return array|bool
		 */
	    public static function get_order_meta_shipment_data( $order_id, $shipment_id ) {
			foreach ( get_post_meta( $order_id, 'shipcloud_shipment_data' ) as $shipment ) {
				if ( $shipment['id'] === $shipment_id ) {
					return $shipment;
				}
			}
			
			return false;
	    }
		
	    /**
		 * Converts shipment data to postmeta data.
		 * 
		 * @param array $shipment
		 * @return array
		 */
		public static function convert_shipment_to_postmeta( array $shipment ) {
			
			$result = [];
			$keys 	= [
				'from' 	=> 'sender_',
				'to' 	=> 'recipient_',
			];
			
			foreach ( $shipment as $key => $value ) {
				if ( is_array( $value ) ) {
					if ( in_array( $key, array_keys( $keys ) ) ) {
						$key = $keys[$key];
					}
					foreach ( $value as $k => $v ) {
						if ( is_array( $v ) ) {
							if ( $key === 'packages' ) {
								foreach ( $v as $i => $c ) {
									$result[$i] = $c;
								}
							}
						}
						else {
							if ( $key !== 'package' ) {
								$result["{$key}{$k}"] = $v;
							}
							else {
								$result["{$k}"] = $v;
							}
						}
					}
				}
				else {
					$result[$key] = $value;
				}
			}
			
			$width  = $shipment['package']['width'];
			$height = $shipment['package']['height'];
			$length = $shipment['package']['length'];
			$weight = $shipment['package']['weight'];
			
			$parcel_title = self::get_carrier_display_name( $shipment['carrier'] )
						  . ' - ' . self::get_service_name( $shipment['service'] )
						  // . ' - ' . self::get_package_label( $shipment['package'] )
						  . ' - ' . $width
						  . ' x ' . $height
						  . ' x ' . $length
						  . ' ' . __( 'cm', 'shipcloud-for-woocommerce' )
						  . ' ' . $weight . __( 'kg', 'shipcloud-for-woocommerce' );
			
			$result['parcel_id'] = $shipment['id'];
			$result['parcel_title'] = $parcel_title;
			
			return $result;
		}
		
	    /**
		 * Converts postmeta data to shipment data.
		 * 
		 * @param array $postmeta
		 * @return array
		 */
		public static function convert_postmeta_to_shipment( $postmeta ) {
			
			// self::log( "convert_postmeta_to_shipment: " . json_encode( $postmeta ) );
			
			$carrier = $service = '';
			$carrier = isset( $postmeta['carrier'] ) ? $postmeta['carrier'] : '';
			$service = isset( $postmeta['service'] ) ? $postmeta['service'] : '';
			
			$created_at = date( DATE_ATOM, strtotime( $postmeta['date_created'] ) );
			
			$shipment 	= array(
				'id' 					=> isset( $postmeta['id'] ) ? $postmeta['id'] : '',
				'carrier_tracking_no' 	=> isset( $postmeta['carrier_tracking_no'] ) ? $postmeta['carrier_tracking_no'] : '',
				'tracking_url' 			=> isset( $postmeta['tracking_url'] ) ? $postmeta['tracking_url'] : '',
				'label_url' 			=> isset( $postmeta['label_url'] ) ? $postmeta['label_url'] : '',
				'price' 				=> isset( $postmeta['price'] ) ? $postmeta['price'] : 0.00,
				'carrier' 				=> $carrier,
				'service' 				=> $service,
				'created_at' 			=> $created_at,
				'reference_number' 		=> isset( $postmeta['reference_number'] ) ? $postmeta['reference_number'] : '',
				'notification_email' 	=> isset( $postmeta['notification_email'] ) ? $postmeta['notification_email'] : '',
				'from' 					=> array(
					'id' 		 => isset( $postmeta['sender_id'] ) ? $postmeta['sender_id'] : '',
					'first_name' => isset( $postmeta['sender_first_name'] ) ? $postmeta['sender_first_name'] : '',
					'last_name'  => isset( $postmeta['sender_last_name'] ) ? $postmeta['sender_last_name'] : '',
					'company'    => isset( $postmeta['sender_company'] ) ? $postmeta['sender_company'] : '',
					'street'     => isset( $postmeta['sender_street'] ) ? $postmeta['sender_street'] : '',
					'street_no'  => isset( $postmeta['sender_street_no'] ) ? $postmeta['sender_street_no'] : '',
					'care_of'    => isset( $postmeta['sender_care_of'] ) ? $postmeta['sender_care_of'] : '',
					'zip_code'   => isset( $postmeta['sender_zip_code'] ) ? $postmeta['sender_zip_code'] : '',
					'city'       => isset( $postmeta['sender_city'] ) ? $postmeta['sender_city'] : '',
					'state'      => isset( $postmeta['sender_state'] ) ? $postmeta['sender_state'] : '',
					'country'    => isset( $postmeta['sender_country'] ) ? $postmeta['sender_country'] : '',
					'phone'      => isset( $postmeta['sender_phone'] ) ? $postmeta['sender_phone'] : '',
					'email' 	 => isset( $postmeta['sender_email'] ) ? $postmeta['sender_email'] : '',
				),
				'to'   					=> array(
					'id' 		 => isset( $postmeta['recipient_id'] ) ? $postmeta['recipient_id'] : '',
					'first_name' => isset( $postmeta['recipient_first_name'] ) ? $postmeta['recipient_first_name'] : '',
					'last_name'  => isset( $postmeta['recipient_last_name'] ) ? $postmeta['recipient_last_name'] : '',
					'company'    => isset( $postmeta['recipient_company'] ) ? $postmeta['recipient_company'] : '',
					'street'     => isset( $postmeta['recipient_street'] ) ? $postmeta['recipient_street'] : '',
					'street_no'  => isset( $postmeta['recipient_street_no'] ) ? $postmeta['recipient_street_no'] : '',
					'care_of'    => isset( $postmeta['recipient_care_of'] ) ? $postmeta['recipient_care_of'] : '',
					'zip_code'   => isset( $postmeta['recipient_zip_code'] ) ? $postmeta['recipient_zip_code'] : '',
					'city'       => isset( $postmeta['recipient_city'] ) ? $postmeta['recipient_city'] : '',
					'state'      => isset( $postmeta['recipient_state'] ) ? $postmeta['recipient_state'] : '',
					'country'    => isset( $postmeta['recipient_country'] ) ? $postmeta['recipient_country'] : '',
					'phone'      => isset( $postmeta['recipient_phone'] ) ? $postmeta['recipient_phone'] : '',
					'email' 	 => isset( $postmeta['recipient_email'] ) ? $postmeta['recipient_email'] : '',
				),
				'packages' 				=> array(
					array(
						'id'		 => isset( $postmeta['parcel_id'] ) ? $postmeta['parcel_id'] : '',
						'width'		 => isset( $postmeta['width'] ) ? $postmeta['width'] : 0,
						'height'	 => isset( $postmeta['height'] ) ? $postmeta['height'] : 0,
						'length'	 => isset( $postmeta['length'] ) ? $postmeta['length'] : 0,
						'weight'	 => isset( $postmeta['weight'] ) ? $postmeta['weight'] : 0,
						'type'		 => 'parcel',
						'title' 	 => isset( $postmeta['parcel_title'] ) ? $postmeta['parcel_title'] : '',
						'tracking_events' => array(
						
						),
					),
				),
				'customs_declaration' => isset( $postmeta['customs_declaration'] ) ? $postmeta['customs_declaration'] : null, 
				'additional_services' => isset( $postmeta['additional_services'] ) ? $postmeta['additional_services'] : null,
			);
			
			return $shipment;
		}
		
	    /**
		 * Converts a shipment array to API response.
		 * 
		 * @param array $shipment
		 * @param string $order_id
		 * @return array
		 */
		public static function convert_to_wc_api_response( $shipment, $order_id = null ) {
			
			$data = array(
				'id'		=> isset($shipment['id']) ? $shipment['id'] : '',
				'from'		=> $shipment['from'],
				'to'		=> $shipment['to'],
				'label_url'           => isset($shipment['label_url']) ? $shipment['label_url'] : '',
				'tracking_url'        => isset($shipment['tracking_url']) ? $shipment['tracking_url'] : '',
				'price'               => isset($shipment['price']) ? self::format_price( $shipment['price'] ) : '',
				'carrier'             => isset($shipment['carrier']) ? $shipment['carrier'] : '',
	            'service'             => isset($shipment['service']) ? $shipment['service'] : '',
				'carrier_tracking_no' => isset($shipment['carrier_tracking_no']) ? $shipment['carrier_tracking_no'] : '',
				'reference_number'    => isset($shipment['reference_number']) ? $shipment['reference_number'] : '',
				'notification_email'  => isset($shipment['notification_email']) ? $shipment['notification_email'] : '',
				'additional_services' => isset($shipment['additional_services']) ? self::extract_additional_services( $shipment ) : '',
				'customs_declaration' => isset($shipment['customs_declaration']) ? $shipment['customs_declaration'] : '',
				'label' => array(
					'format' => isset($shipment['label']['format']) ? $shipment['label']['format'] : '',
				),
			);

			if ( $order_id ) {
				$data['shipment_status'] = self::get_status_string(
					get_post_meta( $order_id, 'shipment_' . $shipment['id'] . '_status', true )
				);
			}
			
			if ( isset( $shipment['packages'] ) ) {
				$shipment['package'] = $shipment['packages'][0];
			}
			if ( isset( $shipment['package'] ) ) {
				$data['package'] = [
					'width'  => wc_format_decimal( $shipment['package']['width'] ),
					'height' => wc_format_decimal( $shipment['package']['height'] ),
					'length' => wc_format_decimal( $shipment['package']['length'] ),
					'weight' => wc_format_decimal( $shipment['package']['weight'] ),
				];
			}

			if ( isset( $shipment['pickup_request'] ) ) {
				$data['pickup_request'] = $shipment['pickup_request'];
			} elseif ( isset( $shipment['pickup'] ) ) {
				$data['pickup'] = $shipment['pickup'];
			}

			return $data;
		}
		
		public static function format_price( $price = 0 ) {
			if ( strpos( $price, ' EUR' ) !== false ) {
				return $price;
			}
			
			return number_format( $price, 2, ',', '.' ) . " " . get_woocommerce_currency();
		}
		
		/**
		 * Enhances shipment data.
		 *
		 * @param array $shipment
		 * @return array
		 */
		public static function enhance_shipment_data( array $shipment ) {
			
			$package = false;
			if ( ! empty( $shipment['package'] ) ) {
				$package = $shipment['package'];
			}
			else if ( ! empty( $shipment['packages'] ) ) {
				$package = $shipment['packages'][0];
			}
			
			if ( $package ) {
				
				$width  = $package['width'];
				$height = $package['height'];
				$length = $package['length'];
				$weight = $package['weight'];
			
				$parcel_title = self::get_carrier_display_name( $shipment['carrier'] )
							  . ' - ' . self::get_service_name( $shipment['service'] )
							  // . ' - ' . self::get_package_label( $shipment['package'] )
							  . ' - ' . $width
							  . ' x ' . $height
							  . ' x ' . $length
							  . ' ' . __( 'cm', 'shipcloud-for-woocommerce' )
							  . ' ' . $weight . __( 'kg', 'shipcloud-for-woocommerce' );
				
				$shipment['parcel_title'] = $parcel_title;
			}
			
			$shipment['parcel_id'] = $shipment['id'];
			
			return $shipment;
		}
		

    /**
     * Getting option carrier email notification enabled
     *
     * @return bool
     */
    public static function carrier_email_notification_enabled() {
      $carrier_email = self::get_option( 'carrier_email' );

      return in_array( (string) $carrier_email, [ '1', 'on', 'yes', 'true' ] );	
    }

    /**
     * Getting option shipcloud email notification enabled
     *
     * @return bool
     */
    public static function shipcloud_email_notification_enabled() {
      $notification_email = self::get_option( 'notification_email' );

      return in_array( (string) $notification_email, [ '1', 'on', 'yes', 'true' ] );	
    }

	    /**
	     * Extract additional services from shipment
	     *
		 * @param $shipment
		 * @param $order_id
	     * @return array $additional_services
	     */
	    public static function extract_additional_services( $shipment ) {
			
			$submitted_additional_services = $shipment['additional_services'];
			$additional_services = [];
			
			// $order = WC_Shipping_Shipcloud_Order::get_instance()->create_order( $order_id );
			
			foreach ( $submitted_additional_services as $additional_service ) {
				switch ( $additional_service['name'] ) {
					case 'visual_age_check':
						if ( array_key_exists( 'minimum_age', $additional_service['properties'] ) ) {
							$additional_services[] = array(
								'name' 		 => 'visual_age_check',
								'properties' => array(
									'minimum_age' => $additional_service['properties']['minimum_age']
								),
							);
						}
						break;
					case 'ups_adult_signature':
						$additional_services[] = array(
							'name' => 'ups_adult_signature'
						);
						break;
					case 'saturday_delivery':
						$additional_services[] = array(
							'name' => 'saturday_delivery'
						);
						break;
					case 'premium_international':
						$additional_services[] = array(
							'name' => 'premium_international'
						);
						break;
					case 'delivery_time':
						$additional_services[] = array(
							'name' 		 => 'delivery_time',
							'properties' => array(
								'time_of_day_earliest' => $additional_service['properties']['time_of_day_earliest'],
								'time_of_day_latest'   => $additional_service['properties']['time_of_day_latest']
							),
						);
						break;
					case 'angel_de_delivery_date_time':
						$additional_services[] = array(
							'name' 		 => 'angel_de_delivery_date_time',
							'properties' => array(
								'date'				   => $additional_service['properties']['date'],
								'time_of_day_earliest' => $additional_service['properties']['time_of_day_earliest'],
								'time_of_day_latest'   => $additional_service['properties']['time_of_day_latest']
							),
						);
						break;
					case 'drop_authorization':
						if ( array_key_exists( 'message', $additional_service['properties'] ) ) {
							$additional_services[] = array(
								'name' 		 => 'drop_authorization',
								'properties' => array(
									'message' => $additional_service['properties']['message']
								),
							);
						}
						break;
					case 'dhl_endorsement':
						$additional_services[] = array(
							'name' => 'dhl_endorsement',
							'properties' => array(
								'handling' => $additional_service['properties']['handling']
							),
						);
						break;
					case 'dhl_named_person_only':
						$additional_services[] = array(
							'name' => 'dhl_named_person_only'
						);
						break;
					case 'cash_on_delivery':
						$additional_services[] = array(
							'name' => 'cash_on_delivery',
							'properties' => array(
								'amount'   => $additional_service['properties']['amount'],
								'currency' => $additional_service['properties']['currency'],
								'bank_account_holder' => array_key_exists( 'bank_account_holder', $additional_service['properties'] ) ? $additional_service['properties']['bank_account_holder'] : '',
								'bank_name' => array_key_exists( 'bank_account_holder', $additional_service['properties'] ) ? $additional_service['properties']['bank_name'] : '',
								'bank_account_number' => array_key_exists( 'bank_account_holder', $additional_service['properties'] ) ? $additional_service['properties']['bank_account_number'] : '',
								'bank_code' => array_key_exists( 'bank_account_holder', $additional_service['properties'] ) ? $additional_service['properties']['bank_code'] : '',
								'reference1' => array_key_exists( 'bank_account_holder', $additional_service['properties'] ) ? $additional_service['properties']['reference1'] : ''
							),
						);
						break;
					case 'gls_guaranteed24service':
						$additional_services[] = array(
							'name' => 'gls_guaranteed24service'
						);
						break;
					case 'dhl_parcel_outlet_routing':
						// @see https://app.shortcut.com/woocommerce/story/10859/zusatzservice-parcel-outlet-routing
						$email = WC_Shipping_Shipcloud_Order::get_instance()->get_email_for_notification();
						$additional_services[] = array(
							'name' => 'dhl_parcel_outlet_routing',
							'properties' => array(
								'email' => $email
							),
						);
						break;
					case 'advance_notice':
						$additional_services[] = array(
							'name' => 'advance_notice',
							'properties' => array(
								'email' => array_key_exists( 'email', $additional_service['properties'] ) ? $additional_service['properties']['email'] : '',
								'phone' => array_key_exists( 'phone', $additional_service['properties'] ) ? $additional_service['properties']['phone'] : '',
								'sms' => array_key_exists( 'sms', $additional_service['properties'] ) ? $additional_service['properties']['sms'] : ''
							),
						);
						break;
				}
			}

			return $additional_services;
		}
		
		/**
		 * Getting option (overwrite instance values if there option of instance is empty
		 *
		 * @param string $key
		 * @param null   $empty_value
		 * @return mixed|string
		 */
		public static function get_option( $key = '', $empty_value = false ) {
			if ( empty( $key ) ) return $empty_value;
			$options = self::get_plugin_options();
			return ( !empty( $options ) && !empty( $options[$key] ) ) ? $options[$key] : $empty_value;
	    }
		
		/**
		 * Gets plugin options.
		 *
		 * @return mixed
		 */
		public static function get_plugin_options() {
			return get_option( WC_SHIPPING_SHIPCLOUD_OPTIONS_NAME, false );
		}
		
		/**
		 * Receive the parcel description of an order.
		 *
		 * @param WC_Order $order
		 * @return mixed|string
		 */
		public static function get_parcel_description( $order ) {
			if ( ! is_object( $order ) || ! $order instanceof WC_Order ) {
				throw new \InvalidArgumentException( 'Please provide an WC_Order instance.' );
			}

			$order_id 		= $order->get_id();
			$shipping_data 	= (array) get_post_meta( $order_id, 'shipcloud_shipment_data', true );

			if ( isset( $shipping_data['description'] ) ) {
				return $shipping_data['description'];
			}

			return '';
		}
		
		/**
		 * Resolve the correct identifier for cash on delivery.
		 *
		 * @return string
		 */
		public static function get_cod_id() {
			static $cod_id = null;

			if ( null === $cod_id ) {
				$cod_id = (string) apply_filters( 'wc_shipping_shipcloud_cod_id', 'cod' );
			}

			return $cod_id;
		}
		
		/**
		 * Checking if admin is on a single order page
		 *
		 * @return bool
		 */
		public static function shipcloud_admin_is_on_single_order_page() {
		    if ('shop_order' === get_current_screen()->id ||
		        'edit-shop_order' === get_current_screen()->id
		    ) {
		      return true;
		    }

		    return false;
		}
		
		/**
		 * Insert an array at an arbitrary position of another array.
		 *
		 * @param   array $array
		 * @param   int   $position
		 * @param   array $insert_array
		 * @return  array
		 */
		public static function array_insert( $array, $position, $insert_array ) {
			if ( $position < 0 && $position >= count( $array ) ) {
				self::log( 'Position must be greater than or equal to 0 and less than array length.', 'error' );
				return $array;
			}
			$first_array = array_splice( $array, 0, $position );
			return array_merge( $first_array, $insert_array, $array );
		}
		
		/**
		 * Splitting Address for getting number of street and street separate
		 *
		 * @param string $street
		 * @return mixed $matches
		 */
		public static function explode_street( $street ) {
			
			$number = '';
			$chunks = explode( ' ', trim( $street ) );

			while ( $part = array_pop( $chunks ) ) {
				// Has digit or other allowed char so we count it as number.
				if ( ! preg_match( '@^[\d\.\-\s]+@', $part ) ) {
					// Does not seem like the house number so we add it again and consider the rest to be the street.
					$chunks[] = $part;
					break;
				}

				// Prepend valid street number as we are going in reverse order.
				$number = $part . ' ' . $number;
			}
			
			$address = trim( implode( ' ', $chunks ) );
			$number  = trim( $number );
			
			return array(
				'address' => $address,
				'number'  => $number,
			);
		}
		
		/**
		 * Splitting name for getting first and last names separate
		 *
		 * @param string $name
		 * @return mixed $matches
		 */
		public static function explode_name( $name ) {
			
			$first_name = $last_name = '';
			
			$chunks = explode( ' ', $name );
			$count  = count( $chunks );
			
			if ( $count === 1 ) {
				// only one word - use as last name
				$last_name = $chunks[0];
			}
			else if ( $count === 2 ) {
				$first_name = $chunks[0];
				$last_name 	= $chunks[1];
			}
			else if ( $count > 2 ) {
				// last element may be last name rest part of first name(s)
				$last_name 	= array_pop( $chunks );
				$first_name = implode( ' ', $chunks );
			}
			
			return array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
			);
		}
		
		/**
		 * Extracts country code from string if available
		 *
		 * @param string $string
		 * @return string
		 */
		public static function maybe_extract_country_code( $string = '' ) {
			if ( preg_match( '/([A-Z]+)\:([A-Z\-]+)/', $string, $matches ) ) {
				return $matches[1];
			}
			return $string;
		}
		
		/**
		 * Turn exceptions into \WP_Error
		 *
		 * @param \Exception $exception
		 * @return WP_Error
		 */
		public static function convert_exception_to_wp_error( $exception ) {
			$wp_error         = new \WP_Error( $exception->getCode() ?: 1, $exception->getMessage() );
			$currentException = $exception->getPrevious();
			$maxDepth         = 20;
			
			for ( $i = $maxDepth; $i > 0; $i-- ) {
				if ( $currentException ) {
					$wp_error->add( $currentException->getCode(), $currentException->getMessage() );
					$currentException = $currentException->getPrevious();
				}
			}

			return $wp_error;
		}
		
		/**
		 * Getting the log file path
		 *
		 * @return string log file path
		 */
		public static function get_log_file_path() {
			$logfile_path = WP_CONTENT_DIR . "/uploads/wc-logs/";
			$date_suffix 	= date( 'Y-m-d', time() );
			$hash_suffix 	= hash_hmac( 'md5', WC_SHIPPING_SHIPCLOUD_NAME, AUTH_KEY );
			$logfile_path  .= sanitize_file_name( 
				implode( '-', array( WC_SHIPPING_SHIPCLOUD_NAME, $date_suffix, $hash_suffix ) ) . '.log' 
			);
			return $logfile_path;
		}
		
		/**
		 * Output a debug message.
		 *
		 * @param string 	$message 	Debug message.
		 * @param string 	$level   	Debug level.
         * @param mixed 	$context	The Debug context.
		 * @return void
		 */
		public static function log( $message = '', $level = 'info', $context = [] ) {
			if ( self::is_debug_enabled() ) {
				if ( is_array( $message ) || is_object( $message ) ) {
	                $message = print_r( $message, true );
	            }
				$logger = Logger::get_instance( WC_SHIPPING_SHIPCLOUD_NAME, self::get_log_file_path() );
				$logger->log( $message, $level );
			}
		}
		
		/**
		 * Is debug mode enabled
		 *
		 * @return bool debug enabled
		 */
		private static function is_debug_enabled() {
			$option = self::get_option( 'debug' );
			return in_array( (string) $option, [ '1', 'on', 'yes', 'true' ] );
		}
	
	}
	
	new WC_Shipping_Shipcloud_Utils();
}
