<?php

namespace shipcloud\phpclient;

use shipcloud\phpclient\ApiException;
use shipcloud\phpclient\Logger;
use shipcloud\phpclient\Response;

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

/**
 * ApiClient class that encapsulates request methods for shipcloud API.
 *
 * @category 	Class
 * @package  	shipcloud\phpclient
 * @author   	Daniel Muenter <info@msltns.com>
 * @version  	0.0.1
 * @since   	0.0.1
 * @license 	GPL 3
 *          	This program is free software; you can redistribute it and/or modify
 *          	it under the terms of the GNU General Public License, version 3, as
 *          	published by the Free Software Foundation.
 *          	This program is distributed in the hope that it will be useful,
 *          	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          	GNU General Public License for more details.
 *          	You should have received a copy of the GNU General Public License
 *          	along with this program; if not, write to the Free Software
 *          	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class ApiClient {
	
	/**
	 * URL to the API.
	 *
	 * @var string
	 */
	private $base_url = 'https://api.shipcloud.io/v1';
	
	/**
	 * Key of the customer to access the API.
	 *
	 * @var string
	 */
	private $api_key;
	
	/**
	 * Maximum number of redirects.
	 *
	 * @var int
	 */
	protected $max_redirects = 5;
	
	/**
	 * Timeout for requests.
	 *
	 * @var int
	 */
	protected $request_timeout = 30;
	
	/**
	 * Affiliate ID.
	 * 
	 * @var string
	 */
	private $affiliate_id;
	
	/**
	 * Logger.
	 * 
	 * @var Logger
	 */
	private $logger;

	/**
	 * ApiClient constructor.
	 *
	 * @param string $api_key 		Key to access the API.
	 * @param string $logfile		Path to logfile.
	 * @param string $affiliate_id	Affiliate ID (optional).
	 * @param string $base_url    	URL to the API (optional).
	 * @return void
	 */
	public function __construct( $api_key, $logfile = '', $affiliate_id = false, $base_url = '' ) {
		if ( $this->validate_api_key( $api_key ) ) {
			$this->api_key = $api_key;
		}
		$this->affiliate_id	= $affiliate_id;
		if ( ! empty( $base_url ) ) {
			$this->base_url = $base_url;
		}
		if ( ! empty( $logfile ) ) {
			$this->logger = Logger::get_instance( 'shipcloud-api-client', $logfile );
		}
	}
	
	/*****************************************************************
	 *
	 *		ADDRESSES 
	 *
	 *****************************************************************/
	
	/**
	 * Create a pakadoo address.
	 * 
	 * If the pakadoo user has been identified, shipcloud will return an address object, 
	 * containing the currently selected delivery address for said pakadoo user:
	 * <pre>
	 * {
	 * 		"id": "71f2522f-be6f-4606-8eda-67997edfe2ac",
	 * 		"pakadoo_id": "5KQTPH5",
	 * 		"company": "LGI GmbH",
	 * 		"street": "Hewlett-Packard-Str.",
	 * 		"street_no": "1/1",
	 * 		"zip_code": "71083",
	 * 		"city": "Herrenberg",
	 * 		"country": "DE"
	 * }
	 * </pre>
	 * Like with every other address you can then use its unique address id to create a 
	 * new shipment with it.
	 * 
	 * @param string  $pakadoo_id
	 * @return void
	 * @see https://developers.shipcloud.io/examples/#create-a-pakadoo-address-and-shipment-using-the-pakadoo_id
	 */
	public function create_pakadoo_address( string $pakadoo_id ) {
		if ( ! empty( $pakadoo_id ) ) {
			return $this->post( '/addresses', [ 'pakadoo_id' => $pakadoo_id ] );
		}
		return false;
	}
	
	/**
	 * This method is to create an address. It forces a validation against required fields
	 * before sending the request to API.
	 * 
	 * @param Address $address The address to be created.
	 * @return array The created address including ID.
	 */
	public function create_address( Address $address ) {
		if ( $this->validate_address( $address ) ) {
			return $this->post( '/addresses', $address->to_array() );
		}
		return false;
	}
	
	/**
	 * If you want to obtain a certain address, this is the way to go.
	 * 
	 * @param string $id The id attribute that was returned when creating the address.
	 * @return array The address if existing.
	 */
	public function get_address( string $id ) {
		if ( $this->validate_id( $id ) ) {
			return $this->get( "/addresses/{$id}" );
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
	 * @return array A list of all created addresses.
	 */
	public function get_addresses( array $filter = [] ) {
		return $this->get( '/addresses', $filter );
	}
	
	/**
	 * Checks a given array of addresses if a certain address exists filtered by certain criteria.
	 * 
	 * @param array  $addresses The address list to search.
	 * @param string $filter    The search criteria.
	 * @return array Address if found, otherwise false.
	 */
	public function address_exists( array $addresses, array $filter = [] ) {
		if ( empty( $filter ) ) return false;
		foreach( $addresses as $address ) {
			echo $address['id'] . PHP_EOL;
			foreach( $filter as $key => $value ) {
				if ( ! isset( $address[$key] ) || $address[$key] !== $value ) {
					continue 2;
				}
			}
			return $address;
		}
		return false;
	}
	
	/**
	 * This is the default address that will be used for returns shipments when no address was 
	 * specified using the 'to' attribute.
	 * 
	 * @return array The default returns address.
	 */
	public function get_default_returns_address() {
		return $this->get( '/default_returns_address' );
	}
	
	/**
	 * This is the default address that will be used for shipments when no address was specified 
	 * using the 'from' attribute. 
	 * 
	 * @return array The default shipping address.
	 */
	public function get_default_shipping_address() {
		return $this->get( '/default_shipping_address' );
	}
	
	/**
	 * This is the address that a user has specified as being their invoice address.
	 * 
	 * @return array The invoice address.
	 */
	public function get_invoice_address() {
		return $this->get( '/invoice_address' );
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
	 * @param Shipment $shipment The shipment to be created at API.
	 * @return array Shipment labeling, tracking and pricing information.
	 */
	public function create_shipment( Shipment $shipment ) {
		if ( $this->validate_shipment( $shipment ) ) {
			return $this->post( '/shipments', $shipment->to_array() );
		}
		return false;
	}
	
	/**
	 * If you want to obtain a certain shipment, this is the way to go.
	 * 
	 * @param string $id The id attribute that was returned when creating the shipment.
	 * @return array The shipment if existing.
	 */
	public function get_shipment( string $id ) {
		if ( $this->validate_id( $id ) ) {
			return $this->get( "/shipments/{$id}" );
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
	 * @return array A list of all created shipments.
	 */
	public function get_shipments( array $filter = [] ) {
		return $this->get( '/shipments', $filter );
	}
	
	/**
	 * This method is to update an existing shipment.
	 * 
	 * NOTE:
	 * API returns code 400 (The requested URL returned error: 400 Bad Request) 
	 * if a label has already been created. Therefore a precheck will be processed.
	 * 
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
	 * @param Shipment $shipment The shipment to edit. 
	 * @return array Shipment labeling, tracking and pricing information.
	 */
	public function update_shipment( Shipment $shipment ) {
		if ( $this->validate_shipment( $shipment ) && $this->validate_id( $shipment->get_id() ) && $this->shipment_is_editable( $shipment->get_id() ) ) {
			return $this->put( "/shipments/{$shipment->get_id()}", $shipment->to_array() );
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
	 * @return array|bool An empty array if succeeded, false otherwise.
	 */
	public function delete_shipment( string $id ) {
		if ( $this->validate_id( $id ) ) {
			return $this->delete( "/shipments/{$id}" );
		}
		return false;
	}
	
	/**
	 * With this call you can find out how much we will charge you for a 
	 * specific shipment. 
	 * 
	 * @param Shipment $shipment The shipment to get quote for.
	 * @return array|bool An array with shipment quote if succeeded, false otherwise.
	 */
	public function create_shipment_quote( Shipment $shipment ) {
		if ( $this->validate_shipment( $shipment ) ) {
			$shipment = $shipment->to_array();
			unset( $shipment['create_shipping_label'] );
			return $this->post( "/shipment_quotes", $shipment );
		}
		return false;
	}
	
	/*****************************************************************
	 *
	 *		CARRIERS
	 *
	 *****************************************************************/
	
	/**
	 * Get all carriers available for your account.
	 * 
	 * @return array A list of all carriers available for your account.
	 */
	public function get_carriers() {
		return $this->get( '/carriers' );
	}
	
	/*****************************************************************
	 *
	 *		RATES
	 *
	 *****************************************************************/
	
	/** 
	 * With this call you can find out how much we will charge you for a 
	 * specific shipment. 
	 * 
	 * @deprecated The rates call is deprecated. Please use method create_shipment_quote() instead.
	 * @param Shipment $shipment The shipment to get quote for.
	 * @return array|bool An array with shipment quote if succeeded, false otherwise.
	 * @see ApiClient::create_shipment_quote()
	 */
	public function get_shipping_rate( Shipment $shipment ) {
		return $this->create_shipment_quote( $shipment );
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
	 * @param Pickup $pickup The pickup data.
	 * @return array|bool An array with pickup information if succeeded, false otherwise.
	 */
	public function create_pickup_request( Pickup $pickup ) {
		if ( $this->validate_pickup( $pickup ) ) {
			return $this->post( "/pickup_requests", $pickup->to_array() );
		}
		return false;
	}
	
	/**
	 * Getting a list of pickup requests.
	 * 
	 * @return array A list of pickup requests.
	 */
	public function get_pickup_requests() {
		return $this->get( '/pickup_requests' );
	}
	
	/**
	 * Getting information about a certain pickup request.
	 * 
	 * @param string $id The id attribute that was returned when creating the pickup request.
	 * @return array|bool The pickup request if existing, false otherwise.
	 */
	public function get_pickup_request( string $id ) {
		if ( $this->validate_id( $id ) ) {
			return $this->get( "/pickup_requests/{$id}" );
		}
		return false;
	}
	
	/*****************************************************************
	 *
	 *		WEBHOOKS
	 *
	 *****************************************************************/
	
	/**
	 * If you want to create a webhook, this is the way to go.
	 * 
	 * @param Webhook $webhook The webhook object.
	 * @return array|bool An array with webhook information if succeeded, false otherwise.
	 */
	public function create_webhook( Webhook $webhook ) {
		if ( $this->validate_webhook( $webhook ) ) {
			return $this->post( "/webhooks", $webhook->to_array() );
		}
		return false;
	}
	
	/**
	 * Getting a list of webhooks.
	 * 
	 * @return array A list of webhooks.
	 */
	public function get_webhooks() {
		return $this->get( '/webhooks' );
	}
	
	/**
	 * Getting information about a certain webhook.
	 * 
	 * @param string $id The id attribute that was returned when creating the webhook.
	 * @return array|bool The webhook if existing, false otherwise.
	 */
	public function get_webhook( string $id ) {
		if ( $this->validate_id( $id ) ) {
			return $this->get( "/webhooks/{$id}" );
		}
		return false;
	}
	
	/**
	 * This method is to delete an existing webhook.
	 * 
	 * @param $id The id attribute that was returned when creating the webhook.
	 * @return array|bool An empty array if succeeded, false otherwise.
	 */
	public function delete_webhook( string $id ) {
		if ( $this->validate_id( $id ) ) {
			return $this->delete( "/webhooks/{$id}" );
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
	 * @param Tracker $tracker The tracker object.
	 * @return array|bool An array with tracker information if succeeded, false otherwise.
	 */
	public function create_tracker( Tracker $tracker ) {
		if ( $this->validate_tracker( $tracker ) ) {
			return $this->post( "/trackers", $tracker->to_array() );
		}
		return false;
	}
	
	/**
	 * Getting a list of trackers.
	 * 
	 * @return array A list of trackers.
	 */
	public function get_trackers() {
		return $this->get( '/trackers' );
	}
	
	/**
	 * Getting information about a certain tracker.
	 * 
	 * @param string $id The id attribute that was returned when creating the tracker.
	 * @return array|bool The tracker if existing, false otherwise.
	 */
	public function get_tracker( string $id ) {
		if ( $this->validate_id( $id ) ) {
			return $this->get( "/trackers/{$id}" );
		}
		return false;
	}
	
	/*****************************************************************
	 *
	 *		STUFF
	 *
	 *****************************************************************/
	
	/**
	 * If you need more details about the current user that is making a request, 
	 * you can get it by querying the /me endpoint.
	 *
	 * @return array Details about the current API user.
	 */
	public function get_me() {
		return $this->get( '/me' );
	}
	
	/*****************************************************************
	 *
	 *		UTILITIES
	 *
	 *****************************************************************/
	
	/**
	 * Validates an id.
	 * 
	 * @param string $api_key The api key to be validated.
	 * @return bool True if validation succeeded.
	 * @throws ApiException
	 */
	private function validate_api_key( string $api_key ) : bool {
		if ( empty( $api_key ) || ! preg_match( '/^[0-9a-f]{32}$/', $api_key ) ) {
			throw new ApiException( "\$api_key is not valid!" );
		}
		return true;
	}
	
	/**
	 * Validates an id.
	 * 
	 * @param string $id The id to be validated.
	 * @return bool True if validation succeeded.
	 * @throws ApiException
	 */
	private function validate_id( string $id ) : bool {
		if ( empty( $id ) ) {
			throw new ApiException( "\$id must not be empty!" );
		}
		return true;
	}
	
	/**
	 * Validates an array of address data.
	 * 
	 * @param array $address The address to be validated.
	 * @return bool True if validation succeeded.
	 * @throws ApiException
	 */
	private function validate_address( Address $address ) : bool {
		
		if ( ! empty( $address ) ) {
			foreach( $address->get_required_fields() as $field ) {
				if ( empty( $address->{"get_{$field}"}() ) ) {
					throw new ApiException( "\$address->get_{$field}() returns empty result!" );
				}
			}
		
			$address_array = $address->to_array();
			if ( ! is_array( $address_array ) || empty( $address_array ) ) {
				throw new ApiException( "\$address->to_array() returns no or empty array!" );
			}
		
			if ( ! $this->validate_country( $address->get_country() ) ) {
				throw new ApiException( 
					"Country is required as uppercase ISO 3166-1 alpha-2 code! {$address->get_country()} doesn't fit these requirements." 
				);
			}
		}
		
		return true;
	}
	
	/**
	 * Validates a country string.
	 * 
	 * @param string $country The country string to be validated.
	 * @return bool True if validation succeeded, false otherwise.
	 */
	private function validate_country( string $country ) : bool {
		// Country is required as uppercase ISO 3166-1 alpha-2 code
		if ( empty( $country ) || ! preg_match( '/^[A-Z]{2}$/', $country ) ) {
			throw new ApiException( 'Country is required as uppercase ISO 3166-1 alpha-2 code!' );
		}
		return true;
	}
	
	/**
	 * Validates a carrier string.
	 * 
	 * @param string $carrier The carrier string to be validated.
	 * @return bool True if validation succeeded.
	 * @throws ApiException
	 */
	private function validate_carrier( string $carrier ) : bool {
		if ( empty( $carrier ) ) {
			throw new ApiException( 'Carrier is empty or null!' );
		}
		if ( ! CarrierType::is_valid_value( CarrierType::get_class_name(), $carrier ) ) {
			throw new ApiException( 'Invalid carrier type: ' . $carrier );
		}
		return true;
	}
	
	/**
	 * Validates shipment data. It throws an ApiException if the
	 * validation fails. A message with concrete details is given 
	 * then.
	 * 
	 * @param Shipment $shipment The shipment to be validated.
	 * @return bool True if validation succeeded.
	 * @throws ApiException
	 */
	private function validate_shipment( Shipment $shipment ) : bool {
		
		if ( ! empty( $shipment ) ) {
			foreach( $shipment->get_required_fields() as $field ) {
				if ( empty( $shipment->{"get_{$field}"}() ) ) {
					throw new ApiException( "\$shipment->get_{$field}() returns empty result!" );
				}
			}
		
			$shipment_array = $shipment->to_array();
			if ( ! is_array( $shipment_array ) || empty( $shipment_array ) ) {
				throw new ApiException( "\$shipment->to_array() returns no or empty array!" );
			}
		
			// validate carrier
			$this->validate_carrier( $shipment->get_carrier() );
			
			// validate to address
			$this->validate_address( $shipment->get_to() );
			
			// validate package
			$package = $shipment->get_package();
			if ( $package ) {
				foreach( $package->get_required_fields() as $field ) {
					if ( empty( $package->{"get_{$field}"}() ) ) {
						throw new ApiException( "\$package->get_{$field}() returns empty result!" );
					}
				}
			} else {
				throw new ApiException( "\$package is empty or null!" );
			}
		}
		
		return true;
	}
	
	/**
	 * Precheck before updating a shipment.
	 * 
	 * @param string $shipment_id The id of a shipment to be prechecked.
	 * @return bool True if shipment is editable.
	 * @throws ApiException
	 */
	private function shipment_is_editable( string $shipment_id ) : bool {
		$shipment = $this->get_shipment( $shipment_id );
		if ( empty( $shipment ) ) {
			throw new ApiException( "\$shipment {$shipment_id} not found!" );
		}
		if ( is_array( $shipment ) && isset( $shipment['carrier_tracking_no'] ) && $shipment['carrier_tracking_no'] !== null ) {
			throw new ApiException( "\$shipment {$shipment_id} can not be edited!" );
		}
		return true;
	}
	
	/**
	 * Validates pickup data. It throws an ApiException if the
	 * validation fails. A message with concrete details is given 
	 * then.
	 * 
	 * @param Pickup $pickup The pickup data to be validated.
	 * @return bool True if validation succeeded.
	 * @throws ApiException
	 */
	private function validate_pickup( Pickup $pickup ) : bool {
		
		if ( ! empty( $pickup ) ) {
			foreach( $pickup->get_required_fields() as $field ) {
				if ( empty( $pickup->{"get_{$field}"}() ) ) {
					throw new ApiException( "\$pickup->get_{$field}() returns empty result!" );
				}
			}
		
			$pickup_array = $pickup->to_array();
			if ( ! is_array( $pickup_array ) || empty( $pickup_array ) ) {
				throw new ApiException( "\$pickup->to_array() returns no or empty array!" );
			}
		
			// validate carrier
			$this->validate_carrier( $pickup->get_carrier() );
			
			// validate to address
			if ( ! empty( $pickup->get_pickup_address() ) ) {
				$this->validate_address( $pickup->get_pickup_address() );
			}
			
		}
		
		return true;
	}
	
	/**
	 * Validates webhook data. It throws an ApiException if the
	 * validation fails. A message with concrete details is given 
	 * then.
	 * 
	 * @param Webhook $webhook The webhook to be validated.
	 * @return bool True if validation succeeded.
	 * @throws ApiException
	 */
	private function validate_webhook( Webhook $webhook ) : bool {
		
		if ( ! empty( $webhook ) ) {
			foreach( $webhook->get_required_fields() as $field ) {
				if ( empty( $webhook->{"get_{$field}"}() ) ) {
					throw new ApiException( "\$webhook->get_{$field}() returns empty result!" );
				}
			}
		
			$webhook_array = $webhook->to_array();
			if ( ! is_array( $webhook_array ) || empty( $webhook_array ) ) {
				throw new ApiException( "\$webhook->to_array() returns no or empty array!" );
			}
		}
		
		return true;
	}
	
	/**
	 * Validates tracker data. It throws an ApiException if the
	 * validation fails. A message with concrete details is given 
	 * then.
	 * 
	 * @param Tracker $tracker The tracker to be validated.
	 * @return bool True if validation succeeded.
	 * @throws ApiException
	 */
	private function validate_tracker( Tracker $tracker ) : bool {
		
		if ( ! empty( $tracker ) ) {
			foreach( $tracker->get_required_fields() as $field ) {
				if ( empty( $tracker->{"get_{$field}"}() ) ) {
					throw new ApiException( "\$tracker->get_{$field}() returns empty result!" );
				}
			}
		
			$tracker_array = $tracker->to_array();
			if ( ! is_array( $tracker_array ) || empty( $tracker_array ) ) {
				throw new ApiException( "\$tracker->to_array() returns no or empty array!" );
			}
		}
		
		return true;
	}
	
	/*****************************************************************
	 *
	 *		REQUEST MANAGEMENT
	 *
	 *****************************************************************/
	
	/**
	 * GET request.
	 * 
	 * @param string $method	The API method to request.
	 * @param array  $params	The request parameters.
	 * @return array The response payload.
	 */
	protected function get( string $method, array $params = [] ) : array {
		if ( count( $params ) > 0 ) {
			$method = "$method?" . http_build_query( $params );
		}
		return $this->request( $method, 'GET' );
	}
	
	/**
	 * POST request.
	 * 
	 * @param string $method	The API method to request.
	 * @param array  $params	The request parameters.
	 * @return array The response payload.
	 */
	protected function post( string $method, array $params = [] ) : array {
		return $this->request( $method, 'POST', $params );
	}
	
	/**
	 * PUT request.
	 * 
	 * @param string $method	The API method to request.
	 * @param array  $params	The request parameters.
	 * @return array The response payload.
	 */
	protected function put( string $method, array $params = [] ) : array {
		return $this->request( $method, 'PUT', $params );			
	}
	
	/**
	 * DELETE request.
	 * 
	 * @param string $method	The API method to request.
	 * @param array  $params	The request parameters.
	 * @return array The response payload.
	 */
	protected function delete( string $method ) : array {
		return $this->request( $method, 'DELETE' );
	}
	
	/**
	 * The actual request function.
	 * 
	 * @param string $method	The API method to request.
	 * @param string $rtype		The request type (GET|POST|PUT|DELETE).
	 * @param array  $params	The request parameters.
	 * @return array The response payload.
	 * @throws ApiException
	 */
	private function request( string $method, string $rtype = 'GET', array $params = [] ) : array {
		
        $curl 		 = curl_init();
		$method 	 = ltrim( $method, '/\\' );
		$json_params = '';
		
		$rtype 	= strtoupper( $rtype );
		if ( ! in_array( $rtype, array( 'DELETE', 'GET', 'POST', 'PUT' ), true ) ) {
			throw new ApiException( 'Invalid HTTP request type: ' . $rtype );
		}
		
		ob_start();
		$out = fopen('php://output', 'w');
				
		$options = [
			CURLOPT_URL 			=> $this->base_url . '/' . $method,
			CURLOPT_HEADER			=> true,
			CURLOPT_RETURNTRANSFER 	=> false,
			CURLOPT_ENCODING 		=> "",
			CURLOPT_MAXREDIRS 		=> $this->max_redirects,
			CURLOPT_TIMEOUT 		=> $this->request_timeout,
			CURLOPT_HTTPAUTH		=> CURLAUTH_BASIC,
			CURLOPT_USERPWD			=> $this->api_key,
			CURLOPT_FOLLOWLOCATION	=> true,
			CURLOPT_VERBOSE			=> false,
			CURLOPT_STDERR			=> $out,
			CURLOPT_FAILONERROR 	=> false,
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 	=> $rtype, // GET | POST | PUT | DELETE
			CURLOPT_HTTPHEADER 		=> [
				// "Authorization: $this->api_key",
				"Content-Type: application/json",
				"Cache-Control: no-cache"
			]
		];
		
		if ( in_array( $rtype, [ 'POST', 'PUT', 'DELETE' ] ) && ! empty( $params ) ) {
			$json_params = json_encode( $params );
			$options[CURLOPT_POSTFIELDS]   = $json_params;
			$options[CURLOPT_HTTPHEADER][] = 'Content-Length: ' . strlen( $json_params );
		}
		
		if ( ! empty( $this->affiliate_id ) ) {
			$options[CURLOPT_HTTPHEADER][] = 'Affiliate-ID: ' . $this->affiliate_id;
		}
		
		curl_setopt_array( $curl, $options );
		
		$response 	 = curl_exec( $curl );
		$header_size = curl_getinfo( $curl, CURLINFO_HEADER_SIZE );
		
		fclose( $out );
		$debug = ob_get_clean();
		
		$response = Response::create_from_api_response( $debug, $header_size );
		
		$http_code 	= curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		$error 		= curl_error( $curl );
		if ( ! empty( $error ) ) {
			$error = str_replace( 'The requested URL returned error: ', '', $error );
			$error = trim( str_replace( $http_code, '', $error ) );
		}
						
		curl_close( $curl );
		
		if ( $http_code === 204 ) {
			/*
			 * There is no message body. You'll get this code when 
			 * deleting a shipment or webhook was successful. 
			 */
			$this->log( 'Shipment or webhook has been deleted.', 'info' );
			return [];
			
		} else if ( $http_code === 401 ) {
			/*
			 * Unauthorized
			 * You didn't authorize with our api. Probably because you 
			 * forgot to send your api key for authorizing at our api. 
			 */
			$this->log( 'Unauthorized request. Check your API key!', 'error' );
			throw new ApiException( 'Unauthorized request. Check your API key!', $http_code );
		}
		
		if ( $http_code >= 400 && ! empty( $error ) ) {
			
			$this->log( 'Bad request: ' . $json_params, 'error' );
			throw new ApiException( $error, $http_code );
		}
		
		if ( ! $response->get_payload() ) {
			$this->log( 'Could not parse or empty API response.', 'error' );
			throw new ApiException( 'Could not parse or empty API response.', $http_code );
		}

		$payload = $response->get_payload();
		
		if ( ! $response->is_successful() ) {
			// Something was not right, so we throw an exception.
			if ( array_key_exists( 'errors', $payload ) ) {
				$errors = $payload['errors'];
				$errors = implode( ' ', $errors );
				$error  = trim( $error . ' ' . $errors );
			}
			$this->log( $error, 'error' );
			throw new ApiException( $error, $http_code );
		}

		return $payload;
    }
	
	/**
	 * Output a debug message.
	 *
	 * @param string 	$message 	Debug message.
	 * @param string 	$level   	Debug level.
     * @param mixed 	$context	The Debug context.
	 * @return void
	 */
	public function log( $message, $level = 'info', $context = [] ) {
		if ( ! is_null( $this->logger ) ) {
			$this->logger->log( $message, $level, $context );
		}
	}
	
}