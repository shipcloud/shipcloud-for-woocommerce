<?php

/**
 * WC_Shipping_Shipcloud_API_Adapter wrappes the shipcloud API SDK.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

use shipcloud\phpclient\ApiClient;
use shipcloud\phpclient\ApiException;

use shipcloud\phpclient\model\AbstractApiObject;
use shipcloud\phpclient\model\AdditionalService;
use shipcloud\phpclient\model\AdditionalServiceType;
use shipcloud\phpclient\model\Address;
use shipcloud\phpclient\model\CarrierType;
use shipcloud\phpclient\model\ContentsType;
use shipcloud\phpclient\model\CustomsDeclaration;
use shipcloud\phpclient\model\CustomsDeclarationItem;
use shipcloud\phpclient\model\DeclaredValue;
use shipcloud\phpclient\model\DropOffPoint;
use shipcloud\phpclient\model\DropOffPointType;
use shipcloud\phpclient\model\Enum;
use shipcloud\phpclient\model\EventType;
use shipcloud\phpclient\model\HandlingType;
use shipcloud\phpclient\model\IdType;
use shipcloud\phpclient\model\IncotermType;
use shipcloud\phpclient\model\Label;
use shipcloud\phpclient\model\LabelFormatType;
use shipcloud\phpclient\model\LabelSizeType;
use shipcloud\phpclient\model\LabelVoucher;
use shipcloud\phpclient\model\LabelVoucherFormatType;
use shipcloud\phpclient\model\LabelVoucherType;
use shipcloud\phpclient\model\Package;
use shipcloud\phpclient\model\PackageType;
use shipcloud\phpclient\model\Pickup;
use shipcloud\phpclient\model\PickupTime;
use shipcloud\phpclient\model\Properties;
use shipcloud\phpclient\model\RegulationClassType;
use shipcloud\phpclient\model\ServiceType;
use shipcloud\phpclient\model\Shipment;
use shipcloud\phpclient\model\Tracker;
use shipcloud\phpclient\model\Webhook;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud_API_Adapter' ) ) {
	
	class WC_Shipping_Shipcloud_API_Adapter {
		
		/**
		 * The Single instance of the class
		 *
		 * @var object $instance
		 */
	    private static $instance;
		
		/**
		 * The shipcloud API key
		 *
		 * @var string $api_key
		 */
		private $api_key;
		
		/**
		 * ApiClient instance
		 *
		 * @var ApiClient $api
		 * @see ApiClient
		 */
		private $api;
		
		/**
		 * Services list
		 *
		 * @var array $services
		 */
		private $services;
		
		/**
		 * Constructor.
		 *
		 * @return void
		 */
		private function __construct() {
			
			$options = WC_Shipping_Shipcloud_Utils::get_plugin_options();
			if ( $options ) {
				$api_key = $options['api_key'];
				if ( ! empty( $api_key ) ) {
					$this->api_key = $api_key;
					$this->api = $this->get_api();
				} else {
					$msg = sprintf( 
						__( 'No shipcloud API Key found! Please enter your <a href="%s">shipcloud API Key</a>.', 'shipcloud-for-woocommerce' ), 
						admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipcloud' ) 
					);
					$this->log( $msg, 'warning' );
					$this->add_admin_notice( $msg, 'error', true );
					unset( $this->api );
				}
			} else {
				$this->log( 'No shipcloud settings found!', 'error' );
			}
			
			$this->init();
		}
		
		/**
		 * Initialization.
		 *
		 * @return void
		 */
		private function init() {
			$this->services = WC_Shipping_Shipcloud_Utils::get_carrier_services();			
		}
		
		/**
		 * Main Instance
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( !isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		
		/*****************************************************************
		 *
		 *		ADDRESSES 
		 *
		 *****************************************************************/
	
		/**
		 * This method is to create an address. It forces a validation against required fields
		 * before sending the request to API.
		 * 
		 * @param array $data The address to be created.
		 * @return array The created address including ID.
		 */
		public function create_address( $data = [] ) {
			
			$error = "";
			
			if ( empty( $data ) ) {
				$error = "Parameter \$data must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$address  = $this->convert_data_array_to_address_api_object( $data );
				$response = $api->create_address( $address );
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty address response from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
		
		/**
		 * If you want to obtain a certain address, this is the way to go.
		 * 
		 * @param string $id The id attribute that was returned when creating the address.
		 * @return array|WP_Error The address if existing.
		 */
		public function get_address( $id = '' ) {
			
			$error = "";
			
			if ( empty( $id ) ) {
				$error = "Parameter \$id must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->get_address( $id );
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty address response from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
		
		/**
		 * If you want to request a pakadoo point address, this is the way to go.
		 * 
		 * @param string $pakadoo_id The pakadoo point id.
		 * @return array|WP_Error The pakadoo point address if existing.
		 */
		public function get_address_by_pakadoo_id( $pakadoo_id = '' ) {
			
			$error = "";
			
			if ( empty( $pakadoo_id ) ) {
				$error = "Parameter \$pakadoo_id must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
				
				$response = $api->create_pakadoo_address( $pakadoo_id );
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty address response from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * If you want to obtain a list of addresses, this is the way to go.
		 * You can filter the addresses list using one or more of the following
		 * URL parameters:
		 * 
		 * first_name, e.g. 'first_name=Max'
		 * last_name, e.g. 'last_name=Mustermann'
		 * company, e.g. 'company=Example%20Company'
		 * care_of, e.g. 'care_of=Roger%20Receiver'
		 * street, e.g. 'street_no=Musterstraße'
		 * street_no, e.g. 'street_no=42'
		 * zip_code, e.g. 'zip_code=12345'
		 * state, e.g. 'state=CA'
		 * city, e.g. 'city=Musterstadt'
		 * country, e.g. 'country=DE'
		 * phone, e.g. 'phone=555-555'
		 * page, show page number x, e.g. 'page=2'
		 * per_page, show x number of shipments on a page (default & max: 100), e.g. 'per_page=25'
		 * 
		 * @param array $filter A list of filter criteria. 
		 * @return array|WP_Error A list of all created addresses.
		 */
		public function get_addresses( $filter = [] ) {
			
			$error = "";
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->get_addresses( $filter );
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty addresses response from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * This is the default address that will be used for returns shipments when no address was 
		 * specified using the 'to' attribute.
		 * 
		 * @return array|WP_Error The default returns address.
		 */
		public function get_default_returns_address() {
			
			$error = "";
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->get_default_returns_address();
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty default returns address from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * This is the default address that will be used for shipments when no address was specified 
		 * using the 'from' attribute. 
		 * 
		 * @return array The default shipping address.
		 */
		public function get_default_shipping_address() {
			
			$error = "";
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->get_default_shipping_address();
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty default shipping address from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * This is the address that a user has specified as being their invoice address.
		 * 
		 * @return array|WP_Error The invoice address.
		 */
		public function get_invoice_address() {
			
			$error = "";
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->get_invoice_address();
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty invoice address from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
		
		
		/*****************************************************************
		 *
		 *		SHIPMENTS 
		 *
		 *****************************************************************/
	
		/**
		 * This method is to create a shipment.
		 * <pre>
		 * Response:
		 * {
		 *   "id": "3a186c51d4281acbecf5ed38805b1db92a9d668b",
		 *   "carrier_tracking_no": "84168117830018",
		 *   "tracking_url": "https://track.shipcloud.io/3a186c51d4",
		 *   "label_url": "https://shipping-labels.shipcloud.io/shipments/01370b4d/199f803bf8/label/shipping_label_199f803bf8.pdf",
		 *   "price": 3.4
		 * }
		 * </pre>
		 *
		 * @param array $shipment The shipment to be created at API.
		 * @return array|WP_Error The shipment labeling, tracking and pricing information.
		 */
		public function create_shipment( $data = [] ) {
			
			$error = "";
			
			if ( empty( $data ) ) {
				$error = "Parameter \$data must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
				
				$shipment = $this->convert_data_array_to_shipment_api_object( $data );
				
				if ( ! empty( $shipment ) ) {
					
					$this->log( "Create Shipment: " . json_encode( $shipment->to_array() ) );
					
					$response = $api->create_shipment( $shipment );
					if ( ! empty( $response ) ) {
						return array_merge( $data, $response );
					}
					else {
						$error = "Got empty response from shipcloud API.";
					}
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * If you want to obtain a certain shipment, this is the way to go.
		 * 
		 * @param string $id The id attribute that was returned when creating the shipment.
		 * @return array|WP_Error The shipment if existing.
		 */
		public function get_shipment( $id = '' ) {
			
			$error = "";
			
			if ( empty( $id ) ) {
				$error = "Parameter \$id must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$shipment = $api->get_shipment( $id );
				if ( ! empty( $shipment ) ) {
					return $shipment;
				}
				else {
					$error = "Got empty shipment from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
		
		/**
		 * Getting the get current status of a certain shipment.
		 * <pre>
		 * Response:
		 * {
		 *   "timestamp": "2021-10-14T15:56:39+02:00",
		 *   "location": "Paderborn",
		 *   "status": "package_delivered",
		 *   "details": "Es wurde eine Sendung angelegt",
		 *   "id": "4bff7566-33f4-4baa-84aa-9fda2d02b77c"
		 * }
		 * </pre>
		 *
		 * @param $shipment_id The ID of the shipment asked for
		 * @return array|WP_Error $tracking_status The tracking status of the given shipment
		 */
		public function get_current_shipment_status( $shipment_id ) {
			
			if ( empty( $shipment_id ) ) {
				$error = "Parameter \$shipment_id must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$shipment = $this->get_shipment( $shipment_id );
			if ( is_wp_error( $shipment ) ) {
				return $shipment;
			}
			
			$packages = $shipment['packages'];
			if ( ! empty( $packages ) ) {
				
				$result = [];
				foreach( $packages as $package ) {
					if ( isset( $package['tracking_events'] ) ) {
						$result[] = $this->get_latest_tracking_status( $package['tracking_events'] );
					}
				}
				
				if ( count( $result ) === 1 ) {
					$result = $result[0];
				}
				
				return $result;
			}
			
			return false;
		}
		
		/**
		 * If you want to obtain a list of shipments, this is the way to go.
		 * You can filter the shipments list using one or more of the following
		 * URL parameters:
		 * 
		 * <pre>
		 * carrier, e.g. 'carrier=dhl'
	 	 * carrier_tracking_no, e.g. 'carrier_tracking_no=43128000105'
		 * created_at_gt, e.g. 'created_at_gt=20180712T1300Z'
		 * created_at_lt, e.g. 'created_at_lt=20180712T1400Z'
		 * page, show page number x, e.g. 'page=2'
		 * per_page, show x number of shipments on a page (default & max: 100), e.g. 'per_page=25'
		 * reference_number, e.g. 'reference_number=ref123456'
		 * service, e.g. 'service=returns'
		 * shipcloud_tracking_no, e.g. 'shipcloud_tracking_no=86afb143f9c9c0cfd4eb7a7c26a5c616585a6271'
		 * shipment_type, e.g. 'shipment_type=prepared'
		 * source, e.g. 'source=api'
		 * tracking_status, e.g. 'tracking_status=out_for_delivery'
		 * tracking_status_not, e.g. 'tracking_status_not=delivered'
		 * 
		 * shipment_type: Specifies the type of a shipment. The following types are available:
		 * 
		 * prepared: a shipment which was saved in shipcloud but doesn't have a shipping label yet
		 * label_created: a shipment containing a shipping label.
		 * tracking_only: a shipment that was imported via its carrier tracking number (see trackers for more details)
		 * 
		 * created_at_gt / created_at_lt: You can filter the list by using these parameters to specify a timerange to 
		 * find the shipments that were created during this time. The timestamp will be evaluated as ISO 8601 using the 
		 * following format: YYYYMMDDThhmmZ.
		 * 
		 * source: Filter shipments by platform they were created through. The following keys can be used:
		 * 
		 * api: a shipment created through our api
		 * webui: a shipment created using the shipcloud ui
		 * return_portal: a shipment created using the shipcloud return portal
		 * </pre>
		 * 
		 * @param array $filter A list of filter criteria. 
		 * @return array|WP_Error A list of all created shipments.
		 */
		public function get_shipments( $filter = [] ) {
			
			$error = "";
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->get_shipments( $filter );
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty shipments response from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * This method is to update an existing shipment.
		 * 
		 * @param array $data The shipment to edit. 
		 * @return array|WP_Error Shipment labeling, tracking and pricing information.
		 */
		public function update_shipment( $data = [] ) {
			
			$error = "";
			
			if ( empty( $data ) ) {
				$error = "Parameter \$data must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$shipment = $this->convert_data_array_to_shipment_api_object( $data );
				
				$this->log( "Update Shipment: " . json_encode( $shipment->to_array() ) );
				
				$response = $api->update_shipment( $shipment );
				
				$this->log( "Response: " . json_encode( $response ) );
				
				if ( ! empty( $response ) ) {
					
					
					
					return array_merge( $data, $response );
				}
				else {
					$error = "Got empty response from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * This method is to delete an existing shipment.
		 * 
		 * <pre>
		 * NOTE:
		 * Prepared shipments (create_shipping_label is false) can be deleted 
		 * at any time, because no transaction with the carrier has happened 
		 * until this point and no actual shipping label has been created. In 
		 * case you've created a shipping label you can delete it before the 
		 * cutoff time of the carrier. Cutoff times differ from carrier to 
		 * carrier and are some time between 5pm and 8pm. 
		 * </pre>
		 * 
		 * @param $id The id attribute that was returned when creating the shipment.
		 * @return array|bool|WP_Error An empty array if succeeded, false otherwise.
		 */
		public function delete_shipment( $id = '' ) {
			
			$error = "";
			
			if ( empty( $id ) ) {
				$error = "Parameter \$id must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->delete_shipment( $id );
				if ( is_array( $response ) && empty( $response ) ) {
					return true;
				}
				else {
					$error = "Could not delete shipment on shipcloud API";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * With this call you can find out how much we will charge you for a 
		 * specific shipment. 
		 * 
		 * @param array $data The shipment data to get quote for.
		 * @return float|WP_Error An array with shipment quote if succeeded, false otherwise.
		 */
		private function create_shipment_quote( $data = [] ) {
			
			$error = "";
			
			if ( empty( $data ) ) {
				$error = "Parameter \$data must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$shipment = $this->convert_data_array_to_shipment_api_object( $data );
				$response = $api->create_shipment_quote( $shipment );
				
				if ( ! empty( $response ) && ! empty( $response['shipment_quote'] ) 
					&& isset( $response['shipment_quote']['price'] ) 
				) {
					return floatval( $response['shipment_quote']['price'] );
				}
				else {
					$error = "Got empty response from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
		
		/**
		 * Getting the price for a shipment
		 *
		 * @param string $carrier
		 * @param array $from
		 * @param array $to
		 * @param array $package
		 *
		 * @return float|WP_Error
		 */
		public function get_price( $carrier, $from, $to, $package = [], $service = '' ) {
			
			if ( ! $service && strpos( $carrier, '_' ) ) {
				$data 	 = WC_Shipping_Shipcloud_Utils::disassemble_carrier_name( $carrier );
				$carrier = $data['carrier'];
				$service = $data['service'];
			}
			
			$shipment = [
				'carrier' => $carrier,
				'service' => $service,
				'from'	  => $from,
				'to'	  => $to,
				'package' => $package
			];
			
			return $this->create_shipment_quote( $shipment );			
		}
		
		/**
		 * Creating a shipping label
		 *
		 * @param array $shipment
		 *
		 * @return array|WP_Error
		 */
		public function create_label( $shipment = [] ) {
			
			$shipment['create_shipping_label'] = true;
			return $this->update_shipment( $shipment );
		}
	
		/*****************************************************************
		 *
		 *		CARRIERS
		 *
		 *****************************************************************/
	
		/**
		 * Get a list of carriers available for your account.
		 * 
		 * @return array A list of all carriers available for your account.
		 */
		public function get_carrier_list() {
			
			$shipment_carriers = $this->get_carriers();
			
			if ( is_wp_error( $shipment_carriers ) ) {
				return $shipment_carriers;
			}
			
			if ( empty( $shipment_carriers ) ) {
				$error = "Got empty carrier list";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$carriers = [];
			foreach( $shipment_carriers as $shipment_carrier ) {
				if ( isset( $shipment_carrier[ 'services' ] ) ) {
					foreach( (array) $shipment_carrier[ 'services' ] as $service_id ) {
						$carriers[] = array(
							'group'		   => $shipment_carrier[ 'display_name' ],
							'name'         => $shipment_carrier[ 'name' ] . '_' . $service_id,
							'display_name' => $shipment_carrier[ 'display_name' ] . ' - ' . $this->get_service_display_name( $service_id )
						);
					}
				}
				else {
					$carriers[] = array(
						'group'		   => $shipment_carrier[ 'display_name' ],
						'name'         => $shipment_carrier[ 'name' ],
						'display_name' => $shipment_carrier[ 'display_name' ]
					);
				}
			}
			
			return $carriers;
		}

		/**
		 * Get all carriers available for your shipcloud account.
		 *
		 * @return array|false
		 */
		public function get_carriers() {
			
			$transient	= "shipcloud_carriers";
			$duration	= 60 * 60 * 24; // refresh after 24h
			$carriers	= get_transient( $transient );
			
			if ( empty( $carriers ) ) {
				
				$this->log( 'Carrier list is empty. Run update from shipcloud API.' );
				
				$error = "";
				
				$api = $this->get_api();
				if ( is_wp_error( $api ) ) {
					return $api;
				}
				
				try {
				
					$carriers = $api->get_carriers();
					if ( ! empty( $carriers ) ) {
						set_transient( $transient, $carriers, $duration );
						$this->log( "Carriers have successfully been updated." );
					}
					else {
						$error = "Got empty carriers list from shipcloud API.";
					}
					
				} catch ( Exception $e ) {
					return $this->handle_exception( $e );
				}
				
				if ( ! empty ( $error ) ) {
					$this->log( $error, 'error' );
					return new WP_Error( 444, $error );
				}
				
			}
			
			return $carriers;
		}
		
	
		/*****************************************************************
		 *
		 *		PICKUP REQUESTS
		 *
		 *****************************************************************/
	
		/**
		 * There are two ways you can request shipments to be picked up by a 
		 * specific carrier. By simply stating that all shipments that haven't 
		 * been picked up already should be picked up or by specifying which 
		 * shipments should by picked up. 
		 * 
		 * Notice: If you don't supply a pickup_address in your request, we're 
		 * using the default from address that's being defined in the shipcloud 
		 * profile for requesting a pickup by the carrier. Please keep in mind 
		 * there are carrier specific field lengths you have to take into account. 
		 * 
		 * @param array $data The pickup data.
		 * @return array|WP_Error An array with pickup information if succeeded, false otherwise.
		 */
		public function create_pickup_request( $data = [] ) {
			
			$error = "";
			
			if ( empty( $data ) ) {
				$error = "Parameter \$pickup_data must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$pickup 	= $this->convert_data_array_to_pickup_api_object( $data );
				if ( $pickup ) {
					
					$this->log( "create_pickup_request: " . json_encode( $pickup->to_array() ) );
					
					$response = $api->create_pickup_request( $pickup );
					if ( ! empty( $response ) ) {
						return $response;
					}
					else {
						$error = "Got empty response from shipcloud API.";
					}
				}
				else {
					$error = "Something went wrong.";
					$this->log( '$data = ' . json_encode( $pickup ), 'error' );
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * Getting a list of pickup requests.
		 * 
		 * @return array A list of pickup requests.
		 */
		public function get_pickup_requests() {
			
			$error = "";
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->get_pickup_requests();
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty response from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * Getting information about a certain pickup request.
		 * 
		 * @param string $id The id attribute that was returned when creating the pickup request.
		 * @return array|bool The pickup request if existing, false otherwise.
		 */
		public function get_pickup_request( $id = '' ) {
			
			$error = "";
			
			if ( empty( $id ) ) {
				$error = "Parameter \$id must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->get_pickup_request( $id );
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty pickup request from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
		
		
		/*****************************************************************
		 *
		 *		WEBHOOKS
		 *
		 *****************************************************************/
		
		public function get_webhook_id() {
			
			$webhook_id	= get_option( 'woocommerce_shipcloud_catch_all_webhook_id', false );			
			if ( ! empty( $webhook_id ) ) {
				return $webhook_id;
			}
			
			return $this->create_webhook();
		}
	
		/**
		 * If you want to create a webhook, this is the way to go.
		 * 
		 * @return string|bool The id of the created webhook, false otherwise.
		 */
		private function create_webhook() {
			
			$error = "";
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$url 		= WC()->api_request_url( WC_SHIPPING_SHIPCLOUD_NAME, true );
				$webhook  	= new Webhook( $url, [ EventType::ALL ] );
				$response 	= $api->create_webhook( $webhook );
				if ( ! empty( $response ) ) {
					$webhook_id = $response['id'];
					update_option( 'woocommerce_shipcloud_catch_all_webhook_id', $webhook_id );
					return $webhook_id;
				}
				else {
					$this->log( "Got empty response from shipcloud API.", 'warning' );
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
		
		
		/*****************************************************************
		 *
		 *		TRACKERS
		 *
		 *****************************************************************/
	
		/**
		 * If you want to create a tracker, this is the way to go.
		 * 
		 * @param array $data The tracker data.
		 * @return array|bool An array with tracker information if succeeded, false otherwise.
		 */
		public function create_tracker( $data = [] ) {
			
			$error = "";
			
			if ( empty( $data ) ) {
				$error = "Parameter \$data must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$tracker  = $this->convert_data_array_to_tracker_api_object( $data );
				$response = $api->create_tracker( $tracker );
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty response from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * Getting a list of trackers.
		 * 
		 * @return array A list of trackers.
		 */
		public function get_trackers() {
			
			$error = "";
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->get_trackers();
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty trackers from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
	
		/**
		 * Getting information about a certain tracker.
		 * 
		 * @param string $id The id attribute that was returned when creating the tracker.
		 * @return array|bool The tracker if existing, false otherwise.
		 */
		public function get_tracker( $id = '' ) {
			
			$error = "";
			
			if ( empty( $id ) ) {
				$error = "Parameter \$id must not be empty.";
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			$api = $this->get_api();
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			
			try {
			
				$response = $api->get_tracker( $id );
				if ( ! empty( $response ) ) {
					return $response;
				}
				else {
					$error = "Got empty tracker from shipcloud API.";
				}
				
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			if ( ! empty ( $error ) ) {
				$this->log( $error, 'error' );
				return new WP_Error( 444, $error );
			}
			
			return false;
		}
		
		
		/*****************************************************************
		 *
		 *		UTILITIES
		 *
		 *****************************************************************/
		
		/**
		 * Getting shipcloud API.
		 *
		 * @return WP_Error|ApiClient $api
		 * @see ApiClient
		 */
		private function get_api() {
			
			if ( empty( $this->api_key ) ) {
        $error = sprintf( 
          __( 'No shipcloud API Key found! Please enter your <a href="%s">shipcloud API Key</a>.', 'shipcloud-for-woocommerce' ), 
          admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipcloud' ) 
        );
        $this->log( $error, 'error' );
        return new WP_Error( 444, $error );
				// return false;
			}
			
			try {
				$logfile   = '';
				// $logfile   = WC_Shipping_Shipcloud_Utils::get_log_file_path();
				$this->api = new ApiClient( $this->api_key, $logfile, WC_SHIPPING_SHIPCLOUD_AFFILIATE_ID );
			} catch ( ApiException $e ) {
				$error = sprintf( 
					__( 'Please enter a valid <a href="%s">shipcloud API Key</a>.', 'shipcloud-for-woocommerce' ), 
					admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipcloud' ) 
				);
				$this->add_admin_notice( $error, 'error', false );
				return $this->handle_exception( $e );
			} catch ( Exception $e ) {
				return $this->handle_exception( $e );
			}
			
			return $this->api;
		}
		
		/**
		 * Getting service display name by service id.
		 *
		 * @param int $service_id
		 * @return string
		 */
		private function get_service_display_name( $service_id ) {
			if ( array_key_exists( $service_id, $this->services ) ) {
				return $this->services[ $service_id ]['name'];
			} else {
				return $service_id;
			}
		}
		
		/**
		 * Extracting latest tracking information of a certain shipment.
		 *
		 * @param array $tracking_events The tracking events of a certain shipment
		 * @return array|bool The latest tracking information if available
		 */
		private function get_latest_tracking_status( $tracking_events = [] ) {
	
			if ( empty( $tracking_events ) || ! is_array( $tracking_events ) ) {
				return false;
			}

			usort( $tracking_events, function( $a, $b ) {
				$ad = new DateTime( $a['timestamp'] );
				$bd = new DateTime( $b['timestamp'] );

				if ( $ad == $bd ) {
					return 0;
				}

				return $ad > $bd ? -1 : 1;
			} );
	
			return array_shift( $tracking_events );
		}
		
		/**
		 * Converts address data array to Address.
		 *
		 * @param array $data The address data
		 * @return Address
		 */
		private function convert_data_array_to_address_api_object( $data = [] ) {
			
			$address = false;
			if ( ! empty( $data ) && is_array( $data ) ) {
				$address = new Address( 
					$data['last_name'], 
          $data['company'],
					$data['street'], 
					$data['street_no'], 
					$data['city'], 
					$data['zip_code'], 
					$data['country'] 
				);
				
				// if ( ! empty( $data['company'] ) ) {
				// 	$address->set_company( $data['company'] );
				// }
				
				if ( ! empty( $data['first_name'] ) ) {
					$address->set_first_name( $data['first_name'] );
				}
				
				if ( ! empty( $data['email'] ) ) {
					$address->set_email( $data['email'] );
				}
				
				if ( ! empty( $data['phone'] ) ) {
					$address->set_phone( $data['phone'] );
				}
				
				if ( ! empty( $data['care_of'] ) ) {
					$address->set_care_of( $data['care_of'] );
				}
			}
			
			return $address;
		}
		
		/**
		 * Converts shipment data array to Shipment.
		 *
		 * @param array $data The shipment data
		 * @return Shipment
		 */
		private function convert_data_array_to_shipment_api_object( $data = [] ) {
			
			// $this->log( "convert_data_array_to_shipment_api_object: " . json_encode( $data ) );
			
			$carrier = $label = $from_address = $to_address = $package = false;
			
			if ( ! empty( $data['carrier'] ) ) {
				$carrier = $data['carrier'];
			}
			
			if ( ! empty( $data['to'] ) && is_array( $data['to'] ) ) {
				$to_address = $this->convert_data_array_to_address_api_object( $data['to'] );
			}
			
			if ( ! empty( $data['from'] ) && is_array( $data['from'] ) ) {
				$from_address = $this->convert_data_array_to_address_api_object( $data['from'] );
			}
			
			if ( ! empty( $data['package'] ) && is_array( $data['package'] ) ) {
				
				$package = new Package( 
					$data['package']['width'], 
					$data['package']['height'], 
					$data['package']['length'], 
					$data['package']['weight'] 
				);
				
				if ( ! empty( $data['package']['type'] ) ) {
					$package->set_type( $data['package']['type'] );
				}
				
				if ( ! empty( $data['package']['declared_value'] ) 
						&& ! empty( $data['package']['declared_value']['amount'] ) ) {
					$amount 		= $data['package']['declared_value']['amount'];
					$currency 		= get_woocommerce_currency();
					$declared_value = new DeclaredValue( $amount, $currency );
					$package->set_declared_value( $declared_value );
				}
				
				if ( ! empty( $data['package']['description'] ) ) {
					$package->set_description( $data['package']['description'] );
				}
			}
			
			if ( ! empty( $data['label'] ) && is_array( $data['label'] ) ) {
				
				$size 	= ! empty( $data['label']['size'] ) ? $data['label']['size'] : false;
				$format = ! empty( $data['label']['format'] ) ? $data['label']['format'] : false;
				
				if ( ! empty( $size ) && ! empty ( $format ) ) {
					$label = new Label( $format, $size );
				}
				else if ( ! empty( $format ) ) {
					$label = new Label( $format );
				}
				else {
					$label = false;
				}
			}
			
			if ( ! empty( $carrier ) && ! empty( $to_address ) && ! empty( $package ) ) {
				
				$customs_declaration = null;
				if ( ! empty( $data['customs_declaration'] ) ) {
					
					$cd_data		= $data['customs_declaration'];
					$contents_type 	= $cd_data['contents_type'];
					$currency		= $cd_data['currency'];
					$total_amount	= $cd_data['total_value_amount'];
					$items			= $cd_data['items'];
					
					$customs_declaration = new CustomsDeclaration( $contents_type, $currency, $total_amount, $items );
					$customs_declaration->set_contents_explanation( $cd_data['contents_explanation'] );
					$customs_declaration->set_additional_fees( floatval( $cd_data['additional_fees'] ) );
					$customs_declaration->set_drop_off_location( $cd_data['drop_off_location'] );
					$customs_declaration->set_exporter_reference( $cd_data['exporter_reference'] );
					$customs_declaration->set_importer_reference( $cd_data['importer_reference'] );
					$customs_declaration->set_posting_date( $cd_data['posting_date'] );
					$customs_declaration->set_invoice_number( $cd_data['invoice_number'] );
				}
				
				$shipment = new Shipment( $carrier, $to_address, $package, $customs_declaration );
				
				if ( ! empty( $data['id'] ) ) {
					$shipment->set_id( $data['id'] );
				}
				if ( ! empty( $from_address ) ) {
					$shipment->set_from( $from_address );
				}
				if ( ! empty( $label ) ) {
					$shipment->set_label( $label );
				}
				if ( ! empty( $data['service'] ) ) {
					$shipment->set_service( $data['service'] );
				}
				if ( ! empty( $data['reference_number'] ) ) {
					$shipment->set_reference_number( $data['reference_number'] );
				}
				if ( ! empty( $data['notification_email'] ) ) {
					$shipment->set_notification_email( $data['notification_email'] );
				}
				if ( ! empty( $data['create_shipping_label'] ) ) {
					$shipment->set_create_shipping_label( $data['create_shipping_label'] );
				}
				if ( ! empty( $data['description'] ) ) {
					$shipment->set_description( $data['description'] );
				}
				if ( ! empty( $data['additional_services'] ) ) {
					$additional_services = [];
					foreach( $data['additional_services'] as $adservice ) {
						$name 		= $adservice['name'];
						$properties = null;
						if ( isset( $adservice['properties'] ) ) {
							$properties = new Properties();
							foreach( $adservice['properties'] as $key => $value ) {
								$properties->{"set_{$key}"}( $value );
							}
						}
						$additional_services[] = new AdditionalService( $name, $properties );
						if ( $name === AdditionalServiceType::VISUAL_AGE_CHECK ) {
							if ( $label->get_size() === LabelSizeType::SIZE_A6 ) {
								$label->set_size( LabelSizeType::SIZE_A5 );
							}
							if ( $label->get_format() === LabelFormatType::PDF_A6 ) {
								$label->set_format( LabelFormatType::PDF_A5 );
							}
						}
					}
					$shipment->set_additional_services( $additional_services );
				}
				if ( ! empty( $data['pickup'] ) ) {
					$data['pickup']['carrier'] = $carrier;
					$pickup = $this->convert_data_array_to_pickup_api_object( $data['pickup'] );
					$shipment->set_pickup( $pickup );
				}
				
				// $this->log( "convert_data_array_to_shipment_api_object: " . json_encode( $shipment->to_array() ) );
				
				return $shipment;
			}
			
			return false;
		}
		
		/**
		 * Converts pickup data array to Pickup.
		 *
		 * @param array $data The pickup data
		 * @return Pickup
		 */
		private function convert_data_array_to_pickup_api_object( $data = [] ) {
			
			$carrier = $pickup_time = $pickup_address = null;
			
			if ( ! empty( $data['carrier'] ) ) {
				$carrier = $data['carrier'];
			}
			
			if ( ! empty( $data['pickup_time'] ) && is_array( $data['pickup_time'] ) ) {
				$pickup_time = new PickupTime( $data['pickup_time']['earliest'], $data['pickup_time']['latest'] );
			}
			
			if ( ! empty( $data['pickup_address'] ) && is_array( $data['pickup_address'] ) ) {
				$pickup_address = $this->convert_data_array_to_address_api_object( $data['pickup_address'] );
			}
			
			if ( ! empty( $carrier ) && ! empty( $pickup_time ) ) {
				
				$pickup 	= new Pickup( $carrier, $pickup_time, $pickup_address );
				$shipments 	= ! empty( $data['shipments'] ) ? $data['shipments'] : false;
				if ( $shipments ) {
					$pickup->set_shipments( $shipments );
				}
				
				return $pickup;
			}
			
			return false;
		}
		
		/**
		 * Converts tracker data array to Tracker.
		 *
		 * @param array $data The tracker data
		 * @return Tracker
		 */
		private function convert_data_array_to_tracker_api_object( $data = [] ) {
			
			$carrier = $carrier_tracking_no = false;
			
			if ( ! empty( $data['carrier'] ) ) {
				$carrier = $data['carrier'];
			}
			
			if ( ! empty( $data['carrier_tracking_no'] ) ) {
				$carrier_tracking_no = $data['carrier_tracking_no'];
			}
			
			if ( ! empty( $carrier ) && ! empty( $carrier_tracking_no ) ) {
				return new Tracker( $carrier_tracking_no, $carrier );
			}
			
			return false;
		}
		
		/**
		 * Handles Exception and converts it to WP_Error.
		 *
		 * @param Exception $e The exception thrown
		 * @return WP_Error
		 */
		private function handle_exception( Exception $e ) {
			return WC_Shipping_Shipcloud_Utils::convert_exception_to_wp_error( $e );
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

}
