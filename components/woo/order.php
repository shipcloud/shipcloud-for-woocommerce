<?php
/**
 * shipcloud for WooCommerce postboxes
 * Loading postboxes
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package shipcloudForWooCommerce/Woo
 * @version 1.0.0
 * @since   1.0.0
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

if ( ! defined( 'ABSPATH' ) )
{
	exit;
}

class WC_Shipcloud_Order
{
	const META_OTHER = 'shipcloud_other';
  const PICKUP_IN_SHIPMENT_CARRIERS = array('dhl_express', 'go');
  const PICKUP_CARRIERS = array('dpd', 'gls', 'hermes', 'ups');

	/**
	 * The Single instance of the class
	 *
	 * @var $_instance
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Order ID
	 *
	 * @var $order_id
	 * @since 1.0.0
	 */
	protected $order_id;

	/**
     * WooCommerce Order object
     *
	 * @var \WC_Order
	 */
	private $wc_order;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct($order_id = null)
	{
	    $this->order_id = $order_id;

		$this->init_hooks();
	}

	public function filterArrayPreserveEmptyString( $var ) {
		return ($var !== NULL && $var !== FALSE);
	}

	/**
     * Backward compatibility to WC2
     *
     * The current WC3 has almost anything covered by getter methods
     * while the old WC2 used simple fields for that.
     * This layer allows using the old syntax
     * and makes it compatible with the new one.
     *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		$order = $this->get_wc_order();

		if ( property_exists( '\\WC_Order', $name ) ) {
			// WooCommerce 2
			return $order->$name;
		}

		$method = 'get_' . $name;

		if ( is_callable( array( $order, $method ) ) ) {
			return $order->$method();
		}
	}

	/**
     * Backward compatibility to WC2
     *
	 * The current WC3 has almost anything covered by getter methods
	 * while the old WC2 used simple fields for that.
	 * This layer allows using the old syntax
	 * and makes it compatible with the new one.
     *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set( $name, $value ) {
		$order = $this->get_wc_order();

		if ( property_exists( '\\WC_Order', $name ) ) {
			// WooCommerce 2
			$order->$name = $value;
		}

		$method = 'set_' . $name;

		if ( is_callable( array( $order, $method ) ) ) {
			$order->$method($value);
		}
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
	 * Initialize Hooks
	 *
	 * @since 1.0.0
	 */
	private function init_hooks()
	{
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_settings' ) );

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_determined_parcels' ), 10, 2 );

		add_action( 'wp_ajax_shipcloud_calculate_shipping', array( $this, 'ajax_calculate_shipping' ) );
		add_action( 'wp_ajax_shipcloud_create_shipment', array( $this, 'ajax_create_shipment' ) );
		add_action( 'wp_ajax_shipcloud_create_shipment_label', array( $this, 'ajax_create_shipment' ) );
		add_action( 'wp_ajax_shipcloud_create_label', array( $this, 'ajax_create_label' ) );
		add_action( 'wp_ajax_shipcloud_create_pickup_request', array( $this, 'ajax_create_pickup_request' ) );
		add_action( 'wp_ajax_shipcloud_get_pakadoo_point', array( $this, 'ajax_get_pakadoo_point' ) );
		add_action( 'wp_ajax_nopriv_shipcloud_get_pakadoo_point', array( $this, 'ajax_get_pakadoo_point' ) );

		add_action(
			\Shipcloud\Controller\LabelController::AJAX_UPDATE,
			function () {
				_wcsc_container()->get( '\\Shipcloud\\Controller\\LabelController' )->update();
            }
		);

    add_action( 'wp_ajax_shipcloud_delete_shipment', array( $this, 'ajax_delete_shipment' ) );
    add_action( 'wp_ajax_shipcloud_force_delete_shipment', array( $this, 'ajax_force_delete_shipment' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1 );

		add_action( 'woocommerce_order_details_before_order_table', array( $this, 'my_account_show_tracking' ), 10, 1 );
	}

	/**
     * @deprecated 2.0.0 The API should be fully injected instead of fetched from the container.
     *
	 * @return \Woocommerce_Shipcloud_API
	 */
	protected function get_api() {
		return _wcsc_container()->get( '\\Woocommerce_Shipcloud_API' );
	}

	/**
	 * Main Instance
	 *
	 * @since 1.0.0
	 */
	public static function instance()
	{
		if ( is_null( self::$_instance ) )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
     * Factory to create or load an order.
     *
	 * @param int $order_id ID of the order as chosen by WooCommerce and found in the database.
	 *
	 * @return WC_Shipcloud_Order
	 */
	public static function create_order( $order_id ) {
        return new self($order_id);
	}

	/**
	 * Adding meta boxes
	 *
	 * @since 1.0.0
	 */
	public function add_metaboxes()
	{
		add_meta_box( 'shipcloud-io', __( 'shipcloud shipping center', 'shipcloud-for-woocommerce' ), array(
			$this,
			'shipment_center'
		), 'shop_order' );
	}

    /*
     * Create pickup request at shipcloud
     *
     * @since 1.9.0
     *
     * @param $request
     */
    protected function create_pickup_request($data) {
      WC_Shipcloud_Shipping::log('order: function create_pickup_request called');
      WC_Shipcloud_Shipping::log('with data: '.json_encode($data));

      $shipment_id = $data['id'];
      $shipment_repo = _wcsc_container()->get( '\\Shipcloud\\Repository\\ShipmentRepository' );
      $tmp_order = $shipment_repo->findOrderByShipmentId( $shipment_id );
      $order_id = $tmp_order->get_order_number();
      $shipment = $shipment_repo->find_shipment_by_shipment_id( $order_id, $shipment_id );
      $data['carrier'] = $shipment['carrier'];

      try {
        $pickup_time = self::handle_pickup_request($data);
        $pickup_time = array_shift($pickup_time);
        $pickup_request_params = array();
      } catch ( \Exception $e ) {
        wp_send_json_error(
          array(
              'status' => 'BAD_REQUEST',
              'data' => sprintf( __( 'Error while creating the pickup request: %s', 'shipcloud-for-woocommerce' ), $e->getMessage() )
          )
        );
      }

        if ( !array_key_exists('pickup_request', $shipment) ) {
            $pickupRequestData = array(
                'carrier' => $shipment['carrier'],
                'pickup_time' => $pickup_time,
                'shipments' => array(
                    array('id' => $shipment_id)
                ),
            );

            $pickup_address = array_filter($data['pickup_address']);
            // check to see if there was anything more send than the country code
            if ( count($pickup_address) > 1 ) {
                $pickupRequestData['pickup_address'] = $pickup_address;
            }

            $pickup_request = _wcsc_container()->get( '\\Woocommerce_Shipcloud_API' )->create_pickup_request($pickupRequestData);
            if ( is_wp_error( $pickup_request ) ) {
                WC_Shipcloud_Shipping::log( sprintf( __( 'Error while creating the pickup request: %s', 'shipcloud-for-woocommerce' ), $pickup_request->get_error_message() ) );
                wp_send_json_error(
                    array(
                        'status' => 'BAD_REQUEST',
                        'data' => sprintf( __( 'Error while creating the pickup request: %s', 'shipcloud-for-woocommerce' ), $pickup_request->get_error_message() )
                    )
                );
            } else {
                WC_Shipcloud_Shipping::log( sprintf( __( 'Pickup request created with id %s for shipment with id %s', 'shipcloud-for-woocommerce' ), $pickup_request['id'], $shipment_id) );

                // remove shipments element from pickup_request
                unset($pickup_request['shipments']);
                $shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );

                foreach ( $shipments as $shipment ) {
                    if ( $data['id'] === $shipment['id']) {
                        $new_data = array_merge(
                            $shipment,
                            array(
                                'pickup_request' => $pickup_request
                            )
                        );
                        update_post_meta( $order_id, 'shipcloud_shipment_data', $new_data, $shipment );

                        wp_send_json_success(
                            array(
                                'status'      => 'OK',
                                'data'        => $shipment_repo->translate_to_api_data( $new_data, $order_id ),
                            )
                        );
                        break;
                    }
                }
                WC_Shipcloud_Shipping::log( sprintf( __( 'Couldn\'t find the shipment id in this order', 'shipcloud-for-woocommerce' ), $shipment_id ) );
                wp_send_json_error(
                    array(
                        'status' => 'BAD_REQUEST',
                        'data' => sprintf( __( 'Couldn\'t find the shipment id in this order', 'shipcloud-for-woocommerce' ), $shipment_id )
                    )
                );
            }
        } else {
            WC_Shipcloud_Shipping::log( sprintf( __( 'No pickup request for shipment with id %s created, because there was already one', 'shipcloud-for-woocommerce' ), $shipment_id ) );
            wp_send_json_error(
                array(
                    'status' => 'BAD_REQUEST',
                    'data' => sprintf( __( 'No pickup request for shipment with id %s created, because there was already one', 'shipcloud-for-woocommerce' ), $shipment_id )
                )
            );
        }
    }

	/*
	 * Check to see if it's a return shipment
	 *
	 * @return array
	 */
	 protected function handle_return_shipments( $data ) {
		 if ( 'returns' == $data['service'] ) {
			 WC_Shipcloud_Shipping::log('Detected returns shipment. Switching from and to entries.');
			 $to = $data['to'];
			 $from = $data['from'];
			 $data['from'] = $to;
			 $data['to'] = $from;
		 }

		 return array_filter( $data );
	 }

    /**
    * Create a single pickup request
    *
    * @since 1.9.0
    */
    public static function handle_pickup_request( $data, $method = null) {
      WC_Shipcloud_Shipping::log('function handle_pickup_request called');
      WC_Shipcloud_Shipping::log('with data: '.json_encode($data));

      if (
        !empty($data['pickup']['pickup_earliest_date']) && !empty($data['pickup']['pickup_latest_date']) && (
          empty($data['pickup']['pickup_earliest_time_hour']) || empty($data['pickup']['pickup_earliest_time_minute']) ||
          empty($data['pickup']['pickup_latest_time_hour']) || empty($data['pickup']['pickup_latest_time_minute'])
        )
      ) {
        $error_message = __( 'Please provide a pickup time', 'shipcloud-for-woocommerce' );
        \WC_Shipcloud_Shipping::log( $error_message );

        throw new \UnexpectedValueException( $error_message );
      }

      if (array_key_exists('shipment', $data) && array_key_exists('carrier', $data['shipment'])) {
        $carrier = ['carrier'];
      } else if (array_key_exists('shipcloud_carrier', $data)) {
        $carrier = $data['shipcloud_carrier'];
      } else if (array_key_exists('carrier', $data)) {
        $carrier = $data['carrier'];
      } else {
        $error_message = __( 'Carrier missing in request', 'shipcloud-for-woocommerce' );
        \WC_Shipcloud_Shipping::log( $error_message );

        throw new \UnexpectedValueException( $error_message );
      }

      $pickup = array();
      if (
        in_array($carrier, static::PICKUP_IN_SHIPMENT_CARRIERS) ||
        in_array($carrier, static::PICKUP_CARRIERS) && 'create_shipment' != $method
      ) {
        if ( in_array($carrier, static::PICKUP_IN_SHIPMENT_CARRIERS) ) {
          WC_Shipcloud_Shipping::log('was in PICKUP_IN_SHIPMENT_CARRIERS array');
        } elseif ( in_array($carrier, static::PICKUP_CARRIERS) ) {
          WC_Shipcloud_Shipping::log('was in PICKUP_CARRIERS array');
        }

        $pickup_earliest_date = isset($data['pickup']['pickup_earliest_date']) ? $data['pickup']['pickup_earliest_date'] : '';
        $pickup_earliest_time_hour = isset($data['pickup']['pickup_earliest_time_hour']) ? $data['pickup']['pickup_earliest_time_hour'] : '';
        $pickup_earliest_time_minute = isset($data['pickup']['pickup_earliest_time_minute']) ? $data['pickup']['pickup_earliest_time_minute'] : '';
        $pickup_latest_date = isset($data['pickup']['pickup_latest_date']) ? $data['pickup']['pickup_latest_date'] : '';
        $pickup_latest_time_hour = isset($data['pickup']['pickup_latest_time_hour']) ? $data['pickup']['pickup_latest_time_hour'] : '';
        $pickup_latest_time_minute = isset($data['pickup']['pickup_latest_time_minute']) ? $data['pickup']['pickup_latest_time_minute'] : '';

        $pickup_earliest = $pickup_earliest_date.' '.$pickup_earliest_time_hour.':'.$pickup_earliest_time_minute;
        $pickup_latest = $pickup_latest_date.' '.$pickup_latest_time_hour.':'.$pickup_latest_time_minute;

        WC_Shipcloud_Shipping::log('pickup_earliest: '.json_encode($pickup_earliest));
        WC_Shipcloud_Shipping::log('pickup_latest: '.json_encode($pickup_latest));
        try {
            $pickup_earliest = new WC_DateTime( $pickup_earliest, new DateTimeZone( 'Europe/Berlin' ) );
            $pickup_latest = new WC_DateTime( $pickup_latest, new DateTimeZone( 'Europe/Berlin' ) );

            $pickup['pickup_time']['earliest'] = $pickup_earliest->format(DateTime::ATOM);
            $pickup['pickup_time']['latest'] = $pickup_latest->format(DateTime::ATOM);
        } catch (Exception $e) {
            WC_Shipcloud_Shipping::log(sprintf( __( 'Couldn\'t prepare pickup: %s', 'shipcloud-for-woocommerce' ), $e->getMessage() ));
        }
      }

      return $pickup;
    }

	/**
     * Sanitize shop owner data.
     *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function sanitize_shop_owner_data( $data ) {
		$shopOwner = 'from';

		if ( 'returns' == $data['service'] ) {
			$shopOwner = 'to';
		}

		$from = array_filter( $data[ $shopOwner ] );
		if ( count( $from ) <= 1 ) {
			// Drop shop owner when no address is given (should be only a country then / one entry).
			unset( $data[ $shopOwner ] );

			// Try one last time with the stored sender.
			if ( count( $this->get_sender() ) > 1 ) {
				$data[ $shopOwner ] = $this->get_sender();
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
     * @since 1.9.0
     * @param array $data
     * @return array
     */
    public function sanitize_reference_number($data) {
        if (array_key_exists('reference_number', $data)) {
            $reference_number = $data['reference_number'];

            if ( has_shortcode( $reference_number, 'shipcloud_orderid' ) ) {
                $data['reference_number'] = str_replace('[shipcloud_orderid]', $this->ID, $reference_number);
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
     * @since 1.4.0
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

		if( array_key_exists('declared_value', $package_data) ) {
			$package_data['declared_value']['amount'] = wc_format_decimal( $package_data['declared_value']['amount'] );
		}

		return $package_data;
	}

	/**
	 * Product metabox
	 *
	 * @since 1.0.0
	 */
	public function shipment_center()
	{
		global $post;

		$this->order_id = $post->ID;

		wp_nonce_field( plugin_basename( __FILE__ ), 'save_settings' );

    $html = '<div id="shipment-center">';
    $html .= '<input type="hidden" name="wants_carrier_email_notification" value="';
    if ( $this->wants_carrier_email_notification() ) {
      $html .= 'true" />';
    } else {
      $html .= 'false" />';
    }
		$html .= $this->addresses();
		$html .= $this->parcel();
		$html .= $this->labels();
		$html .= '</div>';
		$html .= '<div class="clear"></div>';

		echo $html;
	}

	/**
	 * Getting addresses
	 *
	 * @return array $addresses
	 * @since 1.1.0
	 */
	private function get_addresses()
	{
		return array(
			'sender' => $this->get_sender(),
			'recipient' => $this->get_recipient( )
		);
	}

	/**
	 * Shows Addresses Content
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function addresses()
	{
		global $woocommerce;

		$addresses = $this->get_addresses();
		extract( $addresses );

		ob_start();
		?>
		<div class="section addresses">

			<div class="address fifty">
				<div class="address-form sender disabled">

					<h3><?php _e( 'Sender address', 'shipcloud-for-woocommerce' ); ?>
						<a class="btn-edit-address"><img width="14" alt="Edit" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/icons/edit.png"></a>
					</h3>

					<p class="fullsize">
						<input type="text" name="sender_address[company]" value="<?php echo $sender[ 'company' ]; ?>" disabled>
						<label for="sender_address[company]"><?php _e( 'Company', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="sender_address[first_name]" value="<?php echo $sender[ 'first_name' ]; ?>" disabled>
						<label for="sender_address[first_name]"><?php _e( 'First name', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="sender_address[last_name]" value="<?php echo $sender[ 'last_name' ]; ?>" disabled>
						<label for="sender_address[last_name]"><?php _e( 'Last name', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="seventyfive">
						<input type="text" name="sender_address[street]" value="<?php echo $sender[ 'street' ]; ?>" disabled>
						<label for="sender_address[street]"><?php _e( 'Street', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="twentyfive">
						<input type="text" name="sender_address[street_nr]" value="<?php echo isset($sender[ 'street_nr' ]) ? $sender[ 'street_nr' ] : $sender[ 'street_no' ]; ?>" disabled>
						<label for="sender_address[street_nr]"><?php _e( 'Number', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="sender_address[zip_code]" value="<?php echo $sender[ 'zip_code' ]?: $sender[ 'postcode' ]; ?>" disabled>
						<label for="sender_address[zip_code]"><?php _e( 'Postcode', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="sender_address[city]" value="<?php echo $sender[ 'city' ]; ?>" disabled>
						<label for="sender_address[city]"><?php _e( 'City', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="sender_address[state]" value="<?php echo $sender[ 'state' ]; ?>" disabled>
						<label for="sender_address[state]"><?php _e( 'State', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

                    <p class="fullsize">
                        <select name="sender_address[country]" disabled>
							<?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
                                <option value="<?php esc_attr_e( $key ); ?>"
									<?php selected( $key === $sender['country'] ); ?>>
									<?php echo $country; ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                        <label for="sender_address[country]">
                            <?php _e( 'Country', 'shipcloud-for-woocommerce' ); ?>
                        </label>
                    </p>

                    <p class="fullsize">
                        <input type="text" name="sender_address[phone]" value="<?php echo $sender[ 'phone' ]; ?>" disabled>
                        <label for="sender_address[phone]"><?php _e( 'Phone', 'shipcloud-for-woocommerce' ); ?></label>
                    </p>
                </div>
			</div>

			<div class="address fifty">
				<div class="address-form recipient disabled">

					<h3><?php _e( 'Recipient address', 'shipcloud-for-woocommerce' ); ?>
						<a class="btn-edit-address"><img width="14" alt="Edit" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/icons/edit.png"></a>
					</h3>

					<p class="fullsize">
						<input type="text" name="recipient_address[company]" value="<?php echo $recipient[ 'company' ]; ?>" disabled>
						<label for="recipient_address[company]"><?php _e( 'Company', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="recipient_address[first_name]" value="<?php echo $recipient[ 'first_name' ]; ?>" disabled>
						<label for="recipient_address[first_name]"><?php _e( 'First name', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="recipient_address[last_name]" value="<?php echo $recipient[ 'last_name' ]; ?>" disabled>
						<label for="recipient_address[last_name]"><?php _e( 'Last name', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

          <p class="fullsize">
            <input type="text" name="recipient_address[email]" value="<?php esc_attr_e( $recipient[ 'email' ] ); ?>" disabled>
            <label for="recipient_address[care_of]"><?php _e( 'email', 'shipcloud-for-woocommerce' ); ?></label>
          </p>

          <p class="fullsize">
              <input type="text" name="recipient_address[care_of]" value="<?php esc_attr_e( $recipient[ 'care_of' ] ); ?>" disabled>
              <label for="recipient_address[care_of]"><?php _e( 'Care of', 'shipcloud-for-woocommerce' ); ?></label>
          </p>

					<p class="seventyfive">
						<input type="text" name="recipient_address[street]" value="<?php echo $recipient[ 'street' ]; ?>" disabled>
						<label for="recipient_address[street]"><?php _e( 'Street', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="twentyfive">
						<?php
							if (array_key_exists('street_no', $recipient)) {
								$recipient_address_street_nr = $recipient[ 'street_no' ];
							} else if (array_key_exists('street_nr', $recipient)) {
								$recipient_address_street_nr = $recipient[ 'street_nr' ];
							} else {
								$recipient_address_street_nr = '';
							}
						?>
						<input type="text" name="recipient_address[street_nr]" value="<?php echo $recipient_address_street_nr; ?>" disabled>
						<label for="recipient_address[street_nr]"><?php _e( 'Number', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="recipient_address[zip_code]" value="<?php echo $recipient[ 'postcode' ]?: $recipient[ 'zip_code' ]; ?>" disabled>
						<label for="recipient_address[zip_code]"><?php _e( 'Postcode', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="recipient_address[city]" value="<?php echo $recipient[ 'city' ]; ?>" disabled>
						<label for="recipient_address[city]"><?php _e( 'City', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text"
                               name="recipient_address[state]"
                               value="<?php if (array_key_exists('state', $recipient)): echo $recipient[ 'state' ]; endif ?>"
                               disabled>
						<label for="recipient_address[state]"><?php _e( 'State', 'shipcloud-for-woocommerce' ); ?></label>
					</p>

					<p class="fullsize">
                        <select name="recipient_address[country]" disabled>
							<?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
                                <option value="<?php esc_attr_e( $key ); ?>"
									<?php selected( $key === $recipient['country'] ) ?>>
									<?php echo $country; ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                        <label for="recipient_address[country]">
                            <?php _e( 'Country', 'shipcloud-for-woocommerce' ); ?>
                        </label>
					</p>

                    <p class="fullsize">
                        <input type="text" name="recipient_address[phone]" value="<?php echo $recipient[ 'phone' ]; ?>" disabled>
                        <label for="recipient_address[phone]"><?php _e( 'Phone', 'shipcloud-for-woocommerce' ); ?></label>
                    </p>
				</div>
			</div>
			<div style="clear: both"></div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Returns Parcel Content
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function parcel()
	{
		$order = $this->get_wc_order();
		ob_start();
		?>
		<div class="section parcels" data-calculated-weight="<?php echo( $this->get_calculated_weight() ); ?>" />
			<h3><?php _e( 'Create shipment', 'shipcloud-for-woocommerce' ); ?></h3>

			<div class="create-label fifty">
				<?php echo $this->parcel_templates(); ?>
				<?php echo $this->parcel_form(); ?>
			</div>

			<div class="additional_services fifty">
				<script type="template/html" id="tmpl-shipcloud-shipment-additional-services">
					<?php require WCSC_COMPONENTFOLDER . '/block/additional-services-edit-form.php'; ?>
				</script>
				<script type="application/javascript">
						jQuery(function ($) {
							shipcloud.additionalServices = new shipcloud.ShipmentAdditionalServicesView({
									model: new shipcloud.ShipmentModel(),
									el   : '.section.parcels .additional_services'
							});

							shipcloud.additionalServices.render();

              <?php
                if ( $this->wants_carrier_email_notification() ) {
                  $advance_notice = array(
                    'email' => $this->get_email_for_notification(),
                    'phone' => $this->get_phone(),
                    'sms' => $this->get_phone()
                  );
              ?>
                  shipcloud.additionalServices.addAdditionalService({
                    'advance_notice': <?php echo(json_encode($advance_notice)); ?>
                  });
              <?php
                }

                                if (method_exists($order, 'get_payment_method')) {
                                    $payment_method = $order->get_payment_method();
                                } else {
                                    $payment_method = $order->payment_method;
                                }

                                if ( wcsc_get_cod_id() === $payment_method ) {
                                    ?>
                                    shipcloud.additionalServices.activateAdditionalService('cash_on_delivery');
                                    <?php
                                }

                                if (method_exists($order, 'get_currency')) {
                                    $currency = $order->get_currency();
                                } else {
                                    $currency = $order->get_order_currency();
                                }

								$cod_data = array(
									'amount'              => $order->get_total(),
									'currency'            => $currency,
									'reference1'          => sprintf( __( 'WooCommerce OrderID: %s', 'shipcloud-for-woocommerce' ), $this->order_id ),
									'bank_account_holder' => $this->get_options('bank_account_holder'),
									'bank_name'           => $this->get_options('bank_name'),
									'bank_account_number' => $this->get_options('bank_account_number'),
									'bank_code'           => $this->get_options('bank_code')
								);
							?>
								// allways add cash_on_delivery data to the form since customer
								// might still like to use this feature although the customer didn't
								// select it in the checkout process
								shipcloud.additionalServices.addAdditionalService({
									'cash_on_delivery': <?php echo(json_encode($cod_data)); ?>
								});
						});
				</script>
			</div>

			<div class="clear"></div>

			<div id="button-actions">
				<button id="shipcloud_create_shipment" type="button" value="<?php _e( 'Prepare label', 'shipcloud-for-woocommerce' ); ?>" class="button">
					<?php _e( 'Prepare label', 'shipcloud-for-woocommerce' ); ?>
				</button>
				<button id="shipcloud_calculate_price" type="button" value="<?php _e( 'Calculate price', 'shipcloud-for-woocommerce' ); ?>" class="button">
					<?php _e( 'Calculate price', 'shipcloud-for-woocommerce' ); ?>
				</button>
				<button id="shipcloud_add_customs_declaration" type="button" value="<?php _e( 'Add customs declaration', 'shipcloud-for-woocommerce' ); ?>" class="button">
					<?php _e( 'Add customs declaration', 'shipcloud-for-woocommerce' ); ?>
				</button>
				<button id="shipcloud_create_shipment_label" type="button" value="<?php _e( 'Create label', 'shipcloud-for-woocommerce' ); ?>" class="button-primary" data-ask-create-label-check="<?php echo esc_attr($this->get_options( 'ask_create_label_check' )); ?>">
					<?php _e( 'Create label', 'shipcloud-for-woocommerce' ); ?>
				</button>
				</p>
			</div>

            <div class="customs-declaration">
                <h3>
                    <?php _e( 'Customs declaration', 'shipcloud-for-woocommerce' ) ?>
                </h3>
                <div class="customs-declaration-form"></div>
                <script type="template/html" id="tmpl-shipcloud-customs-declaration-form">
                    <?php
                        require WCSC_COMPONENTFOLDER . '/block/customs-declaration-edit-form.php';
                    ?>
                </script>
            </div>
            <div style="clear: both"></div>

		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Getting package
	 *
	 * @return array
	 * @since 1.1.0
	 */
	private function get_package()
	{
		$addresses = $this->get_addresses();

		extract( $addresses );

		$recipient = $this->sanitize_address($recipient);

		$package = array();
		$package['destination']['country']   = $recipient['country'];
		$package['destination']['zip_code']  = $recipient['zip_code'];
		$package['destination']['postcode']  = $recipient['zip_code'];
		$package['destination']['state']     = $recipient['state'];
		$package['destination']['city']      = $recipient['city'];
		$package['destination']['address']   = $recipient['street'];
		if (array_key_exists( 'street_nr', $recipient )) {
			$package['destination']['address'] .= ' ' . $recipient['street_nr'];
		} elseif (array_key_exists( 'street_no', $recipient )) {
			$package['destination']['address'] .= ' ' . $recipient['street_no'];
		}

		return $package;
	}

	/**
	 * Getting Carriers
	 *
	 * @return array $carriers
	 * @since 1.1.0
	 */
	private function get_carriers()
	{
		$carriers = array();

		if( function_exists( 'wc_get_shipping_zone' ) )
		{
			WC_Shipcloud_Shipping::log('Shipping zones exist');
			$shipping_zone = wc_get_shipping_zone( $this->get_package() );
			$shipping_methods = $shipping_zone->get_shipping_methods( true );

			foreach( $shipping_methods AS $shipping_method )
			{
				if( 'WC_Shipcloud_Shipping' !== get_class( $shipping_method ) )
				{
					continue;
				}

				$carriers = array_merge( $carriers, $shipping_method->get_allowed_carriers() );
			}



			// Fallback to general settings if there was no shipcloud in shipping zone
			if( 0 === count( $carriers ) && wcsc_shipping_method() )
			{
				WC_Shipcloud_Shipping::log('shipcloud not in shipping zone');
				$carriers = wcsc_shipping_method()->get_allowed_carriers();
			}
		}
		else if ( wcsc_shipping_method() )
		{
			$carriers = wcsc_shipping_method()->get_allowed_carriers();
		}

		return $carriers;
	}

	/**
	 * Returns Parcel Form Content
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function parcel_form() {
		$order = new WC_Order( $this->order_id );

		$carriers = _wcsc_carriers_get();

		ob_start();
		?>
			<?php echo $this->get_label_form($order, $carriers); ?>

			<script type="application/javascript">
				jQuery(function ($) {
					$('#shipcloud_csp_wrapper').shipcloudMultiSelect(wcsc_carrier);
					$('select[name="parcel_list"]').shipcloudFiller('table.parcel-form-table');
				});
			</script>

		<?php
		return ob_get_clean();
	}

	/**
	 * @param \WC_Order                   $order
	 * @param \Shipcloud\Domain\Carrier[] $carriers
	 *
	 * @return string
	 */
	protected function get_label_form( $order, $carriers ) {
		$block = new WooCommerce_Shipcloud_Block_Labels_Form(
            WCSC_FOLDER . '/components/block/label-form.php',
            $this,
            $carriers,
            _wcsc_api()
        );

		return $block->render();
	}

	/**
	 * Returns Parcel Template Form
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function parcel_templates() {
		$posts = get_posts(
			array(
				'post_type'   => 'sc_parcel_template',
				'post_status' => 'publish',
				'posts_per_page' => -1
			)
        );

		$parcel_templates = array();
		if ( is_array( $posts ) && count( $posts ) > 0 )
		{
			$parcel_templates = $this->get_parcel_templates_by_posts( $posts );
		}

		$shipcloud_parcels  = get_post_meta( $this->order_id, 'shipcloud_parcels', true );
		$determined_parcels = array();
		if ( is_array( $shipcloud_parcels ) && count( $shipcloud_parcels ) > 0 )
		{
			foreach ( $shipcloud_parcels AS $carrier_name => $parcels )
			{
				$determined_parcels = $this->get_parcel_template_by_parcels( $parcels, $carrier_name );
			}
		}

		ob_start();
		require WCSC_COMPONENTFOLDER . '/block/order-parcel-templates.php';
		return ob_get_clean();
	}

	/**
	 * Returns Labels
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function labels()
	{
		// delete_post_meta( $this->order_id, 'shipcloud_shipment_data' );

		$shipment_data = get_post_meta( $this->order_id, 'shipcloud_shipment_data' );

		/** @var \Shipcloud\Repository\ShipmentRepository $shipment_repo */
		$shipment_repo = _wcsc_container()->get( '\\Shipcloud\\Repository\\ShipmentRepository' );

		ob_start();
		?>

		<div class="info"></div>

		<div id="create_label">

			<div class="shipping-data">
				<div class="shipment-labels" id="shipment-labels"></div>
					<?php

                    $json_data = array();

					if ( '' != $shipment_data && is_array( $shipment_data ) )
					{
						$shipment_data = array_reverse( $shipment_data );

						foreach ( $shipment_data AS $data ) {
							$json_data[] = $shipment_repo->translate_to_api_data( $data, $this->order_id );
						}
					}

					?>
                <script type="application/javascript">
                    jQuery(function ($) {
                        shipcloud.shipments.add(
							<?php echo json_encode( $json_data, JSON_PRETTY_PRINT ); ?>,
                            {parse: true}
                        );

                        shipcloud.shipmentsList = new shipcloud.ShipmentsView({
                            model: shipcloud.shipments,
                            el   : '#shipment-labels'
                        });

                        $('.shipment-labels .widget-top .widget-quick-actions').find('a,button').unbind();

                        shipcloud.shipmentsList.render();
                    });
                </script>
                <script type="template/html" id="tmpl-shipcloud-shipment">
                    <?php require WCSC_COMPONENTFOLDER . '/block/order-label-template.php'; ?>
                </script>
                <script type="template/html" id="tmpl-shipcloud-shipment-pickup-request">
                    <?php require WCSC_COMPONENTFOLDER . '/block/pickup-request-form-basic.php'; ?>
                </script>
                <script type="template/html" id="tmpl-shipcloud-shipment-edit">
					<?php require WCSC_COMPONENTFOLDER . '/block/order-shipment-edit.php'; ?>
                </script>
                <div style="clear: both"></div>
			</div>
		</div>
		<div id="ask-create-label"><?php _e( 'Depending on the carrier, there will be a fee for creating the label. Do you really want to create a label?', 'shipcloud-for-woocommerce' ); ?></div>
		<div id="ask-delete-shipment"><?php _e( 'Do you really want to delete this shipment?', 'shipcloud-for-woocommerce' ); ?></div>
    <div id="ask-force-delete-shipment"><?php _e( 'Do you want to delete this shipment from the WooCommerce database nonetheless?', 'shipcloud-for-woocommerce' ); ?></div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Creates label HTML
	 *
	 * @param array $data
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	private function get_label_html( $data )
	{
	    global $woocommerce;


		if ( empty( $data[ 'label_url' ] ) )
		{
			$classes_button_create_label   = ' show';
			$classes_button_download_label = ' hide';
		}
		else
		{
			$classes_button_create_label   = ' hide';
			$classes_button_download_label = ' show';
		}

		$display_id      = strtoupper( substr( $data[ 'id' ], 0, 5 ) ) . '-' . strtoupper( substr( $data[ 'id' ], 5, 5 ) );
		$status          = get_post_meta( $this->order_id, 'shipment_' . $data[ 'id' ] . '_status', true );
		$shipment_status = wcsc_get_shipment_status_string( $status );

		ob_start();
		require WCSC_COMPONENTFOLDER . '/block/order-label.php';
		return ob_get_clean();
	}

	/**
	 * Saving product metabox
	 *
	 * @param int $post_id
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function save_settings( $post_id ) {
		// Interrupt on autosave or invalid nonce
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			 || ! isset( $_POST['save_settings'] )
			 || ! wp_verify_nonce( $_POST['save_settings'], plugin_basename( __FILE__ ) )
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

	/**
	 * Saving Data Calculated Parcels
	 *
	 * @param int $order_id
	 *
	 * @since 1.0.0
	 */
	public function save_determined_parcels( $order_id, $posted )
	{
		$shipcloud_parcels = WC()->session->get( 'shipcloud_parcels' );
		update_post_meta( $order_id, 'shipcloud_parcels', $shipcloud_parcels );
	}

	/**
	 * Calulating shipping after submitting calculation
	 *
	 * @since 1.0.0
	 */
	public function ajax_calculate_shipping()
	{
		$package = array(
			'width'  => wc_format_decimal( $_POST['package'][ 'width' ] ),
			'height' => wc_format_decimal( $_POST['package'][ 'height' ] ),
			'length' => wc_format_decimal( $_POST['package'][ 'length' ] ),
			'weight' => wc_format_decimal( $_POST['package'][ 'weight' ] ),
            'type' => $_POST['package']['type'],
		);

		$price = $this->get_api()->get_price(
		        $_POST[ 'carrier' ],
                $_POST['from'],
                $_POST['to'],
                $package,
                $_POST['service']
        );

		if ( is_wp_error( $price ) )
		{
			WC_Shipcloud_Shipping::log( 'Could not calculate shipping - ' . $price->get_error_message() );

			$errors[] = nl2br( $price->get_error_message() );
			$result   = array(
				'status' => 'ERROR',
				'errors' => $errors
			);
			echo json_encode( $result );
			exit;
		}

		WC_Shipcloud_Shipping::log( 'Calculated shipping with - ' . $price . ' (' . wcsc_get_carrier_display_name( $_POST[ 'carrier' ] ) . ')');

		$price_html = wc_price( $price, array( 'currency' => 'EUR' ) );
		$html       = '<div class="notice">' . sprintf( __( 'The calculated price is %s.', 'shipcloud-for-woocommerce' ), $price_html ) . '</div>';

		$result = array(
			'status' => 'OK',
			'price'  => $price,
			'html'   => $html
		);

		echo json_encode( $result );
		exit;
	}

	/**
	 * Get bank information for shop owner.
	 *
	 * @since 1.5.0
	 *
	 * @return \Shipcloud\Domain\ValueObject\BankInformation
	 */
	public function get_bank_information() {
		return new \Shipcloud\Domain\ValueObject\BankInformation(
			$this->get_options('bank_name'),
			$this->get_options('bank_code'),
			$this->get_options('bank_account_holder'),
			$this->get_options('bank_account_number')
		);
	}

	/**
	 * Ask the API to create a shipment label.
	 */
	public function create_shipment_label( $data ) {
    WC_Shipcloud_Shipping::log('order create_shipment_label with data:');
    WC_Shipcloud_Shipping::log(json_encode($data));
    /** @var \Shipcloud\Repository\ShipmentRepository $shipment_repo */
		$shipment_repo = _wcsc_container()->get( '\\Shipcloud\\Repository\\ShipmentRepository' );

		$order_id = null;
		if ( isset( $data['order_id'] ) ) {
			$order_id = $data['order_id'];
		}

		if ( isset( $data['id'] ) ) {
			$shipment_id = $data['id'];
		} else if ( isset( $data['shipment_id'] ) ) {
			$shipment_id = $data['shipment_id'];
		}

		if ( ! $order_id && $shipment_id ) {
			$tmp_order   = $shipment_repo->findOrderByShipmentId( $shipment_id );
			$order_id    = $tmp_order->get_order_number();
		}

		$order = $this->get_wc_order( $order_id );

		if (! isset($data['from']['id']) || '' === $data['from']['id']) {
			unset( $data['from']['id'] );
		}

		if ( ! isset($data['to']['id']) || '' === $data['to']['id'] ) {
			unset( $data['to']['id'] );
		}

		/**
		 * TODO boolean switch inside of method indicated different strategies. Separate them in different methods.
		 */
    $data['create_shipping_label'] = ( 'shipcloud_create_shipment_label' === $data['action'] );
    $create_shipping_label = ( 'shipcloud_create_shipment_label' === $data['action'] ) ? "true" : "false";
    WC_Shipcloud_Shipping::log('create_shipping_label? '.$create_shipping_label);

    $data = $this->handle_return_shipments( $data );
		$data = $this->sanitize_shop_owner_data( $data );
    $data = $this->sanitize_reference_number( $data );
    if (array_key_exists('customs_declaration', $data)) {
        $data['customs_declaration'] = $this->handle_customs_declaration( $data['customs_declaration'] );
    }

    $email = $this->get_email_for_notification();

		if ( array_key_exists( 'package', $data ) ) {
			$data['package'] = $this->sanitize_package( $data['package'] );
		}

        // Only use API fields.
        $data = array_intersect_key(
            $data,
            array(
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
            )
        );

        $message = '';

		try {
      // only applicable for WooCommerce 3
      if (class_exists('WC_DateTime')) {
        $pickup = self::handle_pickup_request( $data, 'create_shipment' );
        unset( $data['pickup_earliest'] );
        unset( $data['pickup_latest'] );
        if (!empty($pickup)) {
            $data['pickup'] = $pickup;
        }
      }

			if ( isset($shipment_id) ) {
				// Update
				WC_Shipcloud_Shipping::log('Updating shipment with shipment_id: '.$shipment_id);
				WC_Shipcloud_Shipping::log('Data: '.json_encode($data));
				$shipment = _wcsc_api()->shipment()->update( $shipment_id, $data );

                $shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );
                foreach ( $shipments as $old_shipment ) {
                    if ( $shipment_id === $old_shipment['id']) {
                        $shipment_data = _shipcloud_shipment_data_to_postmeta( $order_id, $shipment, $data );

                        update_post_meta( $order_id, 'shipcloud_shipment_data', $shipment_data, $old_shipment );

                        if (
                            array_key_exists('customs_declaration', $old_shipment) &&
                            !array_key_exists('customs_declaration', $shipment)
                        ) {
                            $message = __( 'Customs declaration documents not necessary. Therefore they were ignored.', 'shipcloud-for-woocommerce' );
                        }

                        break;
                    }
                }
			} else {
				// Create
				WC_Shipcloud_Shipping::log('Creating new shipment');
				WC_Shipcloud_Shipping::log('Data: '.json_encode($data));
				$shipment = _wcsc_api()->shipment()->create( $data );
                $shipment_data = _shipcloud_shipment_data_to_postmeta( $order_id, $shipment, $data );
                add_post_meta( $order_id, 'shipcloud_shipment_ids', $shipment_data['id'] );
                add_post_meta( $order_id, 'shipcloud_shipment_data', $shipment_data );

                if (
                    array_key_exists('customs_declaration', $data) &&
                    empty($shipment->getCustomsDeclaration())
                ) {
                    $message = __( 'Customs declaration documents not necessary. Therefore they were ignored.', 'shipcloud-for-woocommerce' );
                }
			}

			WC_Shipcloud_Shipping::log( 'Order #' . $order->get_order_number() . ' - Created shipment successful (' . wcsc_get_carrier_display_name( $data['carrier'] ) . ')' );

			wp_send_json_success(
				array(
					'status'      => 'OK',
					'shipment_id' => $shipment->getId(),
					'html'        => $this->get_label_html( $shipment_data ),
					'data'        => $shipment_repo->translate_to_api_data( $shipment_data, $order_id ),
                    'message' => $message,
				)
			);

			wp_die();
		} catch ( \Exception $e ) {
			WC_Shipcloud_Shipping::log(
				sprintf(
					'Order #%d - %s ($s)',
					$order->get_order_number(),
					$e->getMessage(),
					wcsc_get_carrier_display_name( $data['carrier'] )
				)
			);

			wp_send_json_error( _wcsc_exception_to_wp_error( $e ) );
			wp_die();
		}
	}

	/**
	 * Creating shipment
     *
	 * @since 1.0.0
     * @deprecated 2.0.0 Use create_shipment_label instead.
	 */
	public function ajax_create_shipment()
	{
				$this->create_shipment_label( $_POST );
	}

    /**
    * Create a pickup request for a given shipment
    * @since 1.9.0
    */
    public function ajax_create_pickup_request() {
        if ( !$_POST['id'] ) {
            return;
        }

        $this->create_pickup_request($_POST);
    }

	/**
	 * Calulating shipping after sublitting calculation
	 *
	 * @since 1.0.0
	 */
	public function ajax_create_label() {
		$result = $this->create_label( $_POST['order_id'], $_POST['carrier'], $_POST['shipment_id'] );

		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();

			echo json_encode(
				array(
					'status' => 'ERROR',
					'errors' => nl2br( $error_message )
				)
			);

			exit;
		}

		echo json_encode( $result );
		exit;
	}

	public function ajax_get_pakadoo_point() {
		$pakadoo_id = $_POST['pakadoo_id'];
		$response = $this->get_api()->create_address_by_pakadoo_id( $pakadoo_id );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response );
		}

		wp_send_json_success( $response );
		exit;
	}

	/**
	 * Ask the API for a new label.
	 *
	 * @param int $order_id ID of the order as chosen by WooCommerce.
	 * @param string $carrier_id ID of the carrier as given by the API.
	 * @param null|int $shipment_id ID of the shipment.
	 *
	 * @return array|WP_Error
	 */
	public function create_label( $order_id, $carrier_id, $shipment_id = null ) {
		$order = wc_get_order( $order_id );

		/** @var \Shipcloud\Repository\ShipmentRepository $shipmentRepo */
		$shipmentRepo = _wcsc_container()->get('\\Shipcloud\\Repository\\ShipmentRepository');

		$params = array();
		if ($shipment_id) {
		    $params = $shipmentRepo->findByShipmentId( $order_id, $shipment_id );
        }

		$request = $this->get_api()->create_label( $shipment_id, $params );

		if ( is_wp_error( $request ) ) {
			$error_message = $request->get_error_message();
			WC_Shipcloud_Shipping::log( 'Order #' . $order->get_order_number() . ' - ' . $error_message . ' (' . wcsc_get_carrier_display_name( $carrier_id ) . ')' );

			return $request;
		}

		WC_Shipcloud_Shipping::log( 'Order #' . $order->get_order_number() . ' - Created label successful (' . wcsc_get_carrier_display_name( $carrier_id ) . ')' );

		$shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );

		$order->add_order_note( __( 'shipcloud label has been created.', 'shipcloud-for-woocommerce' ) );

		$shipments_old = $shipments;

		// Finding shipment key for updating postmeta
		foreach ( $shipments AS $key => $shipment )
		{
			if ( $shipment[ 'id' ] == $request[ 'body' ][ 'id' ] )
			{
				$shipments[ $key ][ 'tracking_url' ]               = $request[ 'body' ][ 'tracking_url' ];
				$shipments[ $key ][ 'label_url' ]                  = $request[ 'body' ][ 'label_url' ];
				$shipments[ $key ][ 'price' ]                      = $request[ 'body' ][ 'price' ];
				$shipments[ $key ][ 'carrier_tracking_no' ]        = $request[ 'body' ][ 'carrier_tracking_no' ];
				break;
			}
		}

		update_post_meta( $order_id, 'shipcloud_shipment_data', $shipments[ $key ], $shipments_old[ $key ] );

		return array(
			'status'                     => 'OK',
			'id'                         => $request[ 'body' ][ 'id' ],
			'tracking_url'               => $request[ 'body' ][ 'tracking_url' ],
			'label_url'                  => $request[ 'body' ][ 'label_url' ],
			'price'                      => wc_price( $request[ 'body' ][ 'price' ], array( 'currency' => 'EUR' ) ),
			'carrier_tracking_no'        => $request[ 'body' ][ 'carrier_tracking_no' ]
		);
	}

	/**
	 * Deleting a shipment
	 *
	 * @since 1.0.0
	 */
	public function ajax_delete_shipment() {
		$shipment_id = $_POST['shipment_id'];

		/** @var \Shipcloud\Repository\ShipmentRepository $shipment_repository */
		$shipment_repository = _wcsc_container()->get( '\Shipcloud\Repository\ShipmentRepository' );

		$order = $shipment_repository->findOrderByShipmentId( $shipment_id );
		$order_id = $order->get_order_number();

		$request       = $this->get_shipcloud_api()->delete_shipment( $shipment_id );

		if ( is_wp_error( $request ) ) {
			// Do nothing if shipment was not found
			/** @var \WP_Error $request */
			$error_message_shipment_not_found = sprintf( __( 'Order #%s - Could not delete shipment (%s)', 'shipcloud-for-woocommerce' ), $order_id, $shipment_id );

			if ( 'shipcloud_api_error_not_found' == $request->get_error_code() ) {
				WC_Shipcloud_Shipping::log( $error_message_shipment_not_found . ' ' . __( 'because it wasn\'t found at shipcloud', 'shipcloud-for-woocommerce' ) );
			} else {
				WC_Shipcloud_Shipping::log( $error_message_shipment_not_found );
			}

			$errors[] = nl2br( $request->get_error_message() );
			$result   = array(
				'status' => 'ERROR',
				'errors' => $errors
			);

			echo json_encode( $result );
			exit;
		}

		WC_Shipcloud_Shipping::log( 'Order #' . $order_id . ' - Deleted shipment at shipcloud successfully (' . $shipment_id . ')' );

    $this->delete_shipment_from_db($shipment_id);

    // Went through whole shipments and did't find it.
    WC_Shipcloud_Shipping::log( 'Could not find shipment with shipment id ' . $shipment_id . ' belonging to order #' . $order_id );
		echo json_encode(
			array(
				'status' => 'ERROR',
				'errors' => __( 'Shipment was not found in post meta.', 'shipcloud-for-woocommerce' )
			)
		);
		exit;
	}

  /**
   * Force deleting a shipment
   *
   * @since 1.14.1
   */
  public function ajax_force_delete_shipment() {
    $shipment_id = $_POST['shipment_id'];
    $this->delete_shipment_from_db($shipment_id);
  }

  /**
   * Deleting a shipment from the database
   *
   * @since 1.14.1
   */
  public function delete_shipment_from_db($shipment_id) {
    $shipment_repository = _wcsc_container()->get( '\Shipcloud\Repository\ShipmentRepository' );
    $order = $shipment_repository->findOrderByShipmentId( $shipment_id );
    $order_id = $order->get_order_number();
    $shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );

    // Finding shipment key to delete postmeta
    foreach ( $shipments AS $key => $shipment ) {
      if ( $shipment['id'] == $shipment_id ) {
        delete_post_meta( $order_id, 'shipcloud_shipment_data', $shipment );
        delete_post_meta( $order_id, 'shipcloud_shipment_ids', $shipment_id );

        echo json_encode(
          array(
            'status'      => 'OK',
            'shipment_id' => $shipment_id
          )
        );

        $order->add_order_note( __( 'shipcloud shipment has been deleted.', 'shipcloud-for-woocommerce' ) );
        WC_Shipcloud_Shipping::log( 'Deleted shipment with shipment id ' . $shipment_id . ' belonging to order #' . $order_id );

        exit;
      }
    }
  }

	/**
	 * Enqueuing needed Scripts & Styles
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts()
	{
		// JS
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-effects-core' );
		wp_enqueue_script( 'jquery-effects-highlight' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'admin-widgets' );
		wp_enqueue_script( 'wcsc-multi-select' );
		wp_enqueue_script( 'shipcloud-label' );
		wp_enqueue_script( 'shipcloud-label-form' );
		wp_enqueue_script( 'shipcloud-filler' );
		wp_enqueue_script( 'shipcloud-shipments' );

		// CSS
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	}

	/**
	 * Returns Tracking status HTML
	 *
	 * @param string $shipment_id
	 *
	 * @since 1.0.0
	 */
	private function get_tracking_status_html( $shipment_id )
	{
		$this->get_api()->get_tracking_status( $shipment_id );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function prefix_data( $data, $prefix ) {
		if ( ! $prefix ) {
			return $data;
		}

		foreach ( $data as $key => $value ) {
			if ( 0 === strpos( $key, $prefix ) ) {
				// Has already the prefix.
				continue;
			}

			$data[ $prefix . $key ] = $data[ $key ];
			unset( $data[ $key ] );
		}

		return $data;
	}

	/**
	 * @param $options
	 *
	 * @return array|mixed
	 */
	public function get_sender($prefix = '') {
		$sender = get_post_meta( $this->order_id, 'shipcloud_sender_address', true );

		if ( $sender && $prefix ) {
			$sender = $this->prefix_data( $sender, $prefix );
		}

		// Use default data if nothing was saved before
		if ( '' == $sender || 0 == count( $sender ) ) {
			$options = $this->get_options();
			$street_no = $options['sender_street_nr'] ?: (isset($options['sender_street_no']) ? $options['sender_street_no'] : '');
			$zip_code = $options['sender_postcode'] ?: (isset($options['sender_zip_code']) ? $options['sender_zip_code'] : '');

			$sender = array(
				$prefix . 'first_name' => $options['sender_first_name'],
				$prefix . 'last_name'  => $options['sender_last_name'],
				$prefix . 'company'    => $options['sender_company'],
				$prefix . 'street'     => $options['sender_street'],
				$prefix . 'street_no'  => $street_no,
				$prefix . 'zip_code'   => $zip_code,
				$prefix . 'city'       => $options['sender_city'],
				$prefix . 'state'      => $options['sender_state'],
				$prefix . 'country'    => $options['sender_country'],
				$prefix . 'phone'      => isset($options['sender_phone']) ? $options['sender_phone'] : '',
			);
		}

		$address = $this->sanitize_address( $sender, $prefix );

		if ( count( $address ) <= 1 ) {
			// No sender address entered (just the country autofill).
			return array();
		}

		return $address;
	}

	/**
	 * @param string $prefix Mostly "recipient_".
	 *
	 * @return array|mixed
	 * @internal param $options
	 *
	 */
	public function get_recipient( $prefix = '' ) {
		$options = $this->get_options();

		$recipient = get_post_meta( $this->order_id, 'shipcloud_recipient_address', true );

		// Use default data if nothing was saved before
		if ( '' === $recipient || 0 == count( $recipient ) ) {
			WC_Shipcloud_Shipping::log('Use default data for recipient since nothing was saved before');
			$order = $this->get_wc_order();

			$recipient_street_name = method_exists( $order, 'get_shipping_address_1' ) ? $order->get_shipping_address_1() : $order->shipping_address_1;
			$recipient_street_nr   = '';

			if ( ! array_key_exists( 'street_detection', $options ) || 'yes' === $options['street_detection'] ) {
				WC_Shipcloud_Shipping::log('street detection is active');
				$recipient_street = wcsc_explode_street( $recipient_street_name );

				if ( is_array( $recipient_street ) ) {
					$recipient_street_name = $recipient_street['address'];
					$recipient_street_nr   = $recipient_street['number'];
				}

				$shipping_address_2 = method_exists( $order, 'get_shipping_address_2' ) ? $order->get_shipping_address_2() : $order->shipping_address_2;
				if ( ! $recipient_street_nr && $shipping_address_2 ) {
					WC_Shipcloud_Shipping::log('using shipping_address_2 as street no');
					// No house number given but we got secondary information to use for that.
					$recipient_street_nr = $shipping_address_2;
				}
			}

			$recipient = array(
				'first_name' => method_exists( $order, 'get_shipping_first_name' ) ? $order->get_shipping_first_name() : $order->shipping_first_name,
				'last_name'  => method_exists( $order, 'get_shipping_last_name' ) ? $order->get_shipping_last_name() : $order->shipping_last_name,
				'company'    => method_exists( $order, 'get_shipping_company' ) ? $order->get_shipping_company() : $order->shipping_company,
				'care_of'    => $this->get_care_of(),
        'email'      => $this->get_email_for_notification(),
        'street'     => method_exists( $order, 'get_recipient_street_name' ) ? $order->get_recipient_street_name() : $recipient_street_name,
				'street_no'  => method_exists( $order, 'get_recipient_street_nr' ) ? $order->get_recipient_street_nr() : $recipient_street_nr,
				'zip_code'   => method_exists( $order, 'get_shipping_postcode' ) ? $order->get_shipping_postcode() : $order->shipping_postcode,
				'postcode'   => method_exists( $order, 'get_shipping_postcode' ) ? $order->get_shipping_postcode() : $order->shipping_postcode,
				'city'       => method_exists( $order, 'get_shipping_city' ) ? $order->get_shipping_city() : $order->shipping_city,
				'state'      => method_exists( $order, 'get_shipping_state' ) ? $order->get_shipping_state() : $order->shipping_state,
				'country'    => method_exists( $order, 'get_shipping_country' ) ? $order->get_shipping_country() : $order->shipping_country,
				'phone'      => $this->get_phone(),
			);
		}

		return $this->sanitize_address( $this->prefix_data( $recipient, $prefix ), $prefix );
	}

	/**
	 * Resolve phone number from order.
     *
     * @return string
	 */
	public function get_phone() {
		$order = $this->get_wc_order();

		if ( ! $order ) {
		    // No order present.
            return '';
		}

		if ( method_exists( $order, 'get_meta' ) ) {
			return (string) $order->get_meta( '_shipping_phone' );
		} else if (method_exists( $order, 'get_meta_data' )) {
			return (string) $order->get_meta_data( '_shipping_phone' );
		}

		return (string) $order->billing_phone;
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
	public function get_care_of() {
		$order = $this->get_wc_order();

		if ( ! $order ) {
			// No order present.
			return '';
		}

		if ( method_exists( $order, 'get_meta' ) && $care_of = $order->get_meta( '_shipping_care_of' ) ) {
			WC_Shipcloud_Shipping::log('Use care of from _shipping_care_of');
			return (string) $care_of;
		}

		$shipping_address_2 = method_exists( $order, 'get_shipping_address_2' ) ? $order->get_shipping_address_2() : $order->shipping_address_2;
		if ( $shipping_address_2 ) {
			// Shipping address overrides billing address.
			WC_Shipcloud_Shipping::log('Use shipping_address_2 as care of');
			return (string) $shipping_address_2;
		}

		// check to see if WooCommerce germanized was used for supplying a post number
		if( method_exists( $order, 'get_shipping_parcelshop_post_number' ) ) {
			WC_Shipcloud_Shipping::log('WooCommerce germanized detected');
			WC_Shipcloud_Shipping::log('Use parcelshop_post_number as care of');
			return (string) $order->get_shipping_parcelshop_post_number();
		} else if( method_exists( $order, 'shipping_parcelshop_post_number' ) ) {
			WC_Shipcloud_Shipping::log('WooCommerce germanized detected');
			WC_Shipcloud_Shipping::log('Use parcelshop_post_number as care of');
			return (string) $order->shipping_parcelshop_post_number;
		}

		// if all fails, return an empty string
		return '';
	}

	/**
     * Help the user sanitizing the sender address.
     *
	 * @param $data
	 * @param string $prefix
	 *
	 * @return array
	 */
	protected function sanitize_address( $data, $prefix = '' ) {
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
		$data = array_merge($build_data, $data);

		if ( isset( $data[ $prefix . 'street_nr' ] ) ) {
			// Backward compatibility.
			$data[ $prefix . 'street_no' ] = $data[ $prefix . 'street_nr' ];
		}

		if ( isset( $data[ $prefix . 'postcode' ] ) && !empty($data[ $prefix . 'postcode' ]) ) {
			// Backward compatibility.
			$data[ $prefix . 'zip_code' ] = $data[ $prefix . 'postcode' ];
		}

		return array_filter( $data, array($this, 'filterArrayPreserveEmptyString') );
	}

  /**
   * @return string
   * @since 1.12.0
   */
  public function wants_shipcloud_email_notification() {
    $notification_email = $this->get_options( 'notification_email' );

    if ( ! $notification_email || 'yes' !== $notification_email ) {
      return false;
    }

    return true;
  }

  public function wants_carrier_email_notification() {
    $carrier_email = $this->get_options( 'carrier_email' );

    if ( ! $carrier_email || 'yes' !== $carrier_email ) {
      return false;
    }

    return true;
  }

  /**
   * @return string
	 * @since 1.12.0
   */
  public function get_email_for_notification() {
    $order = $this->get_wc_order();

    if (method_exists($order, 'get_billing_email')) {
      return $order->get_billing_email();
    } else if (method_exists($order, 'billing_email')) {
      return $order->billing_email;
    } else {
      return '';
    }
  }

	/**
	 * @param $order_id
	 *
	 * @return WC_Order
	 */
	public function get_wc_order( $order_id = null ) {
		if ( null === $order_id ) {
			if ( $this->wc_order ) {
				return $this->wc_order;
			}

			$order_id = $this->order_id;
		}

		if ( null == $order_id && isset( $_POST['order_id'] ) ) {
			$order_id = $_POST['order_id'];
		}

		$factory = new WC_Order_Factory();

		return $this->wc_order = $factory->get_order( $order_id );
	}

	/**
     * Fetch complete of specific config.
     *
	 * @param null|string $field Fetch a specific config.
	 *
	 * @return array|null
	 */
	public function get_options( $field = null ) {
		$options = get_option( 'woocommerce_shipcloud_settings' );

		if ( null === $field ) {
			return $options;
		}

		if ( ! isset( $options[ $field ] ) ) {
			return null;
		}

		return $options[ $field ];
	}

	/**
	 * @return string
	 */
	public function get_carrier_mail() {
		$carrier_email = $this->get_options( 'carrier_email' );

		if ( ! $carrier_email || 'yes' !== $carrier_email ) {
			return '';
		}

		$order = $this->get_wc_order();
		if (method_exists($order, 'get_billing_email')) {
			return $order->get_billing_email();
		} else {
			return $order->billing_email;
		}
	}

	/**
     * Receive the description.
     *
	 * @return string|null
	 */
	public function get_description() {
		$other = get_post_meta( $this->order_id, static::META_OTHER, true );

		if ( ! isset( $other['description'] ) ) {
			return null;
		}

		return $other['description'];
	}

	/**
	 * @param $posts
	 * @param \Woocommerce_Shipcloud_API $shipcloud_api
	 *
	 * @return array
	 */
	private function get_parcel_templates_by_posts( $posts ) {
		$parcel_templates = [];

		foreach ( $posts AS $post ) {
			$parcel_templates[] = $this->build_parcel_templates( $post );
		}

		return $parcel_templates;
	}

	/**
	 * @param $parcels
	 * @param $carrier_name
	 *
	 * @return array
	 */
	protected function get_parcel_template_by_parcels( $parcels, $carrier_name ) {
		$determined_parcels = array();

		foreach ( $parcels AS $parcel ) {
			$parcel['carrier']    = $carrier_name;
			$determined_parcels[] = $this->build_parcel_templates( $parcel );
		}

		return $determined_parcels;
	}

	/**
	 * @param ArrayObject|array $data
	 *
	 * @return array
	 */
	protected function build_parcel_templates( $data ) {
		if( $data instanceof WP_Post ) {
			$carrier = $data->carrier;
			if ( ! is_array( $data->carrier ) ) {
				$tmp                = explode( '_', $carrier, 2 );
				$carrier = array();
				$carrier['carrier'] = $tmp[0];
				$carrier['service'] = $tmp[1];
				$carrier['package'] = null;
			}

			$option = $data->width . esc_attr( 'x', 'shipcloud-for-woocommerce' )
					   . $data->height . esc_attr( 'x', 'shipcloud-for-woocommerce' )
					   . $data->length . esc_attr( 'cm', 'shipcloud-for-woocommerce' )
					   . ' - ' . $data->weight . esc_attr( 'kg', 'shipcloud-for-woocommerce' )
					   . ' - ' . wcsc_get_carrier_display_name($carrier['carrier'])
                       . ' - ' . $this->get_shipcloud_api()->get_service_name( $carrier['service'] );

			if ( $carrier['package'] ) {
				$option .= ' - ' . WC_Shipcloud_Order::instance()->get_package_label( $carrier['package'] );
	        }

			return array(
				/** @deprecated 2.0.0 Value is not atomic enough so it will be removed. */
				'value'  => $data->width . ';'
							. $data->height . ';'
							. $data->length . ';'
							. $data->weight . ';'
							. $carrier['carrier'] . ';',
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
	}

	/**
	 * @return Woocommerce_Shipcloud_API
	 */
	protected function get_shipcloud_api() {
	    return wcsc_api();
	}

	/**
     * Current chosen package type.
     *
	 * @return string
	 */
	public function get_package_type() {
		return 'parcel';
	}


	/**
	 * Turn package type to readable label.
	 *
	 * @param string $slug
	 *
	 * @return string The proper label or the slug itself if no label was found.
     *
     * @deprecated 2.0.0 This is duplicate to \WCSC_Parceltemplate_Posttype::get_package_label and needs to be injected.
	 */
	public function get_package_label( $slug ) {
		$labels = wcsc_api()->get_package_types();

		if ( ! isset( $labels[ $slug ] ) ) {
			return $slug;
		}

		return $labels[ $slug ];
	}

	/**
	 * Map package type to carrier.
	 *
	 * It is either "all" to be available for all carrier.
	 * Otherwise an array limits the usage to those carriers.
	 *
	 * @see \Woocommerce_Shipcloud_API::get_carriers
	 *
	 * @return array
	 */
	private function get_package_carrier_map() {
		return array(
			'books'         => array( 'all' ),
			'bulk'          => array( 'all' ),
			'letter'        => array( 'all' ),
			'parcel'        => array( 'all' ),
			'parcel_letter' => array( 'dhl', 'dpd' ),
		);
	}

	private function handle_additional_services( $additional_services, $carrier ) {
        $allowed_additional_services = array();

        $shipment_repo = _wcsc_container()->get( '\\Shipcloud\\Repository\\ShipmentRepository' );
        $additional_services_for_carrier = $shipment_repo->additionalServicesForCarrier($carrier);

        foreach ( $additional_services as $additional_service ) {
            $service_included = array_search($additional_service['name'], array_column($additional_services_for_carrier, 'name'));

            if ($service_included !== false) {
                array_push($allowed_additional_services, $additional_service);
            }
        }

        return $allowed_additional_services;
    }

    private function handle_customs_declaration( $data ) {
        $line_items = $data['items'];

        $items = array();
        foreach ( $line_items as $line_item ) {
            array_push($items, $line_item);
        }

        $data['items'] = $items;
        return $data;
    }

	public function get_calculated_weight() {
		$order_items = $this->get_wc_order()->get_items();

		$calculated_weight = 0;
		foreach ( $order_items as $order_item ) {
			if (method_exists($order_item, 'get_quantity')) {
				// WooCommerce 3
				$quantity = $order_item->get_quantity();
			} else {
				// WooCommerce 2
				$quantity = $order_item['qty'];
			}

      $weight = 0;
      if (method_exists($order_item, 'get_product')) {
        // WooCommerce 3
        WC_Shipcloud_Shipping::log('WC3: determining weight');
        $product = $order_item->get_product();
        if ( $product ) {
          $weight = $product->get_weight();
        } else {
          WC_Shipcloud_Shipping::log('couldn\'t get product from order item:');
          WC_Shipcloud_Shipping::log(print_r($order_item, true));
          continue;
        }

        if ( $weight ) {
          WC_Shipcloud_Shipping::log('weight: '.$weight);
          $calculated_weight += $quantity * $weight;
        } else {
          WC_Shipcloud_Shipping::log('weight for product was empty');
        }
     } else {
        // WooCommerce 2
        WC_Shipcloud_Shipping::log('WC2: determining weight');
        $product = $this->get_wc_order()->get_product_from_item($order_item);

        if ( $product ) {
          $weight = get_post_meta( $product->id, '_weight', true );
        } else {
          WC_Shipcloud_Shipping::log('couldn\'t get product from order item:');
          WC_Shipcloud_Shipping::log(print_r($order_item, true));
          continue;
        }

        if ( $weight ) {
          WC_Shipcloud_Shipping::log('weight: '.$weight);
          $calculated_weight += $quantity * $weight;
        } else {
          WC_Shipcloud_Shipping::log('weight for product was empty');
        }
      }
		}
		return $calculated_weight;
	}

	/**
	 * Determine if the config value 'auto_weight_calculation' is active
	 *
	 * @return boolean
	 * @since 1.8.0
	 */
	public function is_auto_weight_calculation_on() {
		$auto_weight_calculation = $this->get_options( 'auto_weight_calculation' );

		if ( $auto_weight_calculation === 'yes' ) {
			return true;
		} else {
			return false;
		}
	}

	public function my_account_show_tracking( $order ) {
		$show_tracking_in_my_account = $this->get_options('show_tracking_in_my_account');
		if ( $show_tracking_in_my_account === 'yes' ) {
			$this->order_id = $order->get_id();
			$shipment_ids = get_post_meta( $order->get_id(), 'shipcloud_shipment_ids' );
			$shipments_data = $shipment_data = get_post_meta( $order->get_id(), 'shipcloud_shipment_data' );

			ob_start();
			?>
            <h2>
                <?php _e( 'Tracking & Tracing', 'shipcloud-for-woocommerce' ); ?>
            </h2>

			<?php
			foreach ( $shipment_ids AS $shipment_id ) {
				$tracking_events = get_post_meta( $order->get_id(), 'shipment_' . $shipment_id . '_trackingevent' );
				$carrier_tracking_number = '';
				foreach ( $shipments_data AS $shipment_data ) {
					if ($shipment_data['id'] === $shipment_id) {
						$carrier_tracking_number = $shipment_data['carrier_tracking_no'];
						// check to see if label url is present because GLS doesn't create a tracking number right away
						if (isset($shipment_data['label_url'])) {
			?>
                            <p>
                                <strong>
                                    <?php _e( 'Carrier', 'shipcloud-for-woocommerce' ); ?>:
                                </strong>
                                <?php echo wcsc_get_carrier_display_name($shipment_data['carrier']); ?>,
                                <strong>
                                    <?php _e( 'Trackingnumber', 'shipcloud-for-woocommerce' ); ?>:
                                </strong>
                                <a href="<?php echo wcsc_get_carrier_tracking_url($shipment_data['carrier'], $carrier_tracking_number); ?>" target="_blank">
                                    <?php echo $carrier_tracking_number; ?>
                                </a>
                            </p>

							<?php
								if (count($tracking_events) > 0 ) {
							?>
								<table class="woocommerce-table woocommerce-table--order-details shop_table order_details shipcloud__tracking">
									<thead>
										<tr>
											<th><?php _e( 'Status', 'shipcloud-for-woocommerce' ); ?></th>
											<th></th>
											<th><?php _e( 'Details', 'shipcloud-for-woocommerce' ); ?></th>
										</tr>
									</thead>

									<tbody>
										<?php
												$tracking_events = array_reverse($tracking_events);
												foreach ( $tracking_events AS $tracking_event ) {
													$occured_at_timestamp = strtotime($tracking_event['occured_at']);
										?>
										<tr>
											<td>
												<div class="shipcloud__tracking--date">
													<?php
														echo strftime('%d.%m.%Y', $occured_at_timestamp);
													?>
												</div>
												<div class="shipcloud__tracking--time">
													<?php
														echo strftime('%H:%M', $occured_at_timestamp);
													?>
												</div>
												<div class="shipcloud__tracking--status">
													<?php echo wcsc_get_shipment_status_string($tracking_event['type']); ?>
												</div>
											</td>
											<td>
												<?php echo wcsc_get_shipment_status_icon($tracking_event['status']); ?>
											</td>
											<td>
												<?php echo $tracking_event['details'] ?>
												<div class="shipcloud__tracking--location">
													<?php echo $tracking_event['location'] ?>
												</div>
											</td>
										</tr>
										<?php
											}
										?>
									</tbody>
								</table>
			<?php
							}
						}
					}
				}
			}
			?>
			<?php
			echo ob_get_clean();
		}
	}
}

WC_Shipcloud_Order::instance();
