<?php

/**
 * WC_Shipping_Shipcloud represents a shipcloud shipping method for WooCommerce.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud' ) ) {
	
	class WC_Shipping_Shipcloud extends WC_Shipping_Method {
		
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
		 * Carriers select options
		 *
		 * @var array
		 */
		private $carriers_options;
		
		/**
		 * Allowed carriers select options
		 *
		 * @var array
		 */
		private $allowed_carriers_options;
		
		/**
		 * Allowed Carriers
		 *
		 * @var array
		 */
		private $allowed_carriers;
		
		/**
		 * Sender data
		 *
		 * @var array
		 */
		private $sender = [];

		/**
		 * Recipient data
		 *
		 * @var array
		 */
		private $recipient = [];

		/**
		 * All Parcels which have been requested at shipcloud for later usage
		 *
		 * @var array
		 */
		private $calculated_parcels = [];
		
		/**
		 * Constructor.
		 *
		 * @param int $instance_id Instance ID.
		 * @return void
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id					= WC_SHIPPING_SHIPCLOUD_NAME;
			$this->instance_id			= absint( $instance_id );
			$this->title				= __( WC_SHIPPING_SHIPCLOUD_NAME, 'shipcloud-for-woocommerce' );
			$this->method_title			= __( WC_SHIPPING_SHIPCLOUD_NAME, 'shipcloud-for-woocommerce' );
			$this->method_description	= __( 'Add shipcloud to your shipping methods', 'shipcloud-for-woocommerce' );
			$this->enabled				= 'yes'; // This can be added as an setting but for this example its forced enabled
			$this->supports				= array(
				'settings',
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			);
			
			$this->api 						= WC_Shipping_Shipcloud_API_Adapter::get_instance();
			$this->callback_url       		= WC()->api_request_url( WC_SHIPPING_SHIPCLOUD_NAME, true );
			
			$this->carriers					= $this->api->get_carrier_list();
			$this->carriers_options 		= $this->get_carrier_options( $this->carriers, false, true ); // global settings
			
			// ["dhl_standard","dhl_express_one_day","dpd_standard","fedex_standard","gls_standard","ups_standard"]
			$this->allowed_carriers			= $this->get_option( 'allowed_carriers', [] );
			$this->allowed_carriers_options	= $this->get_carrier_options( $this->carriers, true ); // instance settings
			
			if ( $instance_id > 0 ) {
				$instance_options = get_option( "woocommerce_shipcloud_{$instance_id}_settings" );
				if ( ! empty( $instance_options ) && isset( $instance_options['allowed_carriers'] ) ) {
					$carriers 		= $instance_options['allowed_carriers'];
					if ( is_string( $carriers ) ) {
						$carriers = explode( ',', $carriers );
					}
					$carrier_names 	= [];
					foreach( $carriers as $carrier ) {
						$carrier_names[] = WC_Shipping_Shipcloud_Utils::get_carrier_display_name_short( $carrier );
					}
					$name 	 	= implode( ', ', $carrier_names );
					$title	 	= sprintf( __( '%s via ' . WC_SHIPPING_SHIPCLOUD_NAME, 'shipcloud-for-woocommerce' ), $name );
					$decription = sprintf( __( 'Add %s to your shipping methods', 'shipcloud-for-woocommerce' ), $name );
					
					$this->title				= $title;
					$this->method_title			= $title;
					$this->method_description	= $decription;
				}
			}
			
			$this->init();
		}
		
		/**
		 * Extract carrier options for multiselect list.
		 *
		 * @param array  	$carriers
		 * @param bool		$only_allowed
		 * @param bool		$grouped
		 * @return array
		 */
		private function get_carrier_options( $carriers, $only_allowed = false, $grouped = false ) {
			$carriers_options = [];
			if ( ! empty( $carriers ) ) {
				foreach ( $carriers as $carrier ) {
					if ( ( $only_allowed && in_array( $carrier[ 'name' ], $this->get_allowed_carriers() ) ) || ! $only_allowed ) {
						if ( $grouped ) {
							$carriers_options[ $carrier[ 'group' ] ][ $carrier[ 'name' ] ] = $carrier[ 'display_name' ];
						}
						else {
							$carriers_options[ $carrier[ 'name' ] ] = $carrier[ 'display_name' ];
						}
					}
				}
			}
			
			return $carriers_options;
		}
		
		/**
		 * Initialization.
		 *
		 * @return void
		 */
		private function init() {
			
			// Load the settings API
            $this->init_form_fields(); 	// This is part of the settings API. Override the method to add your own settings
            $this->set_settings(); 	// This is part of the settings API. Loads settings you previously init.
			
			// Save settings in admin if you have any defined
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			// add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'clear_transients' ) );
			
			// Additional Shipment calculation Field
		    add_action( 'woocommerce_shipping_calculator_enable_city', 	array( $this, 'add_calculate_shipping_form_fields' ) );
		    add_action( 'woocommerce_calculated_shipping', 				array( $this, 'add_calculate_shipping_fields' ) );
			
		}
		
		/**
		 * Set form fields.
		 *
		 * @return void
		 */
		public function init_form_fields() {
			
			$instance_form_fields 		= include( dirname( __FILE__ ) . '/data/data-instance-settings-form-fields.php' );
			$this->instance_form_fields = apply_filters( 'woocommerce_shipping_' . $this->id . '_instance_form_fields', $instance_form_fields );
			
			$form_fields 				= include( dirname( __FILE__ ) . '/data/data-global-settings-form-fields.php' );
			$this->form_fields 			= apply_filters( 'woocommerce_shipping_' . $this->id . 'form_fields', $form_fields );
			
		}
		
		/**
		 * Get settings fields for instances of this shipping method (within zones).
		 * Should be overridden by shipping methods to add options.
		 *
		 * @return array
		 */
		public function get_instance_form_fields() {
			return array_map( array( $this, 'set_defaults' ), $this->instance_form_fields );
		}
		
		/**
		 * Checks whether shipping method is available or not.
		 *
		 * @param array $package Package to ship.
		 * @return bool True if shipping method is available.
		 */
		public function is_available( $package ) {
			
			if ( empty( $package['destination']['country'] ) ) {
				$this->log( "'" . $package['destination']['country'] . "' is empty", 'error' );
				return false;
			}
			
			if ( ! WC_Validation::is_postcode( $package['destination']['postcode'], $package['destination']['country'] ) ) {
				$this->log( "'" . $package['destination']['country'] . "' is not valid postcode", 'error' );
				return false;
			}

			return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', true, $package );
		}
		
		/**
		 * Initialize settings is called on init and after saving plugin settings.
		 *
		 * @return bool
		 */
		private function set_settings() {
			
			$this->title			= $this->get_option( 'title', $this->method_title );
			$this->allowed_carriers	= $this->get_option( 'allowed_carriers', [] );
			$this->webhook_active 	= $this->get_option( 'webhook_active', false );
			$this->webhook_id 		= null;
			if ( $this->webhook_active ) {
				$this->webhook_id = $this->api->get_webhook_id();
			}
			
			return true;
		}
		
		/**
		 * Getting carriers
		 * 
		 * @return array
		 */
		public function get_carriers() {
			return $this->carriers;
		}
		
		/**
		 * Getting allowed carriers
		 * 
		 * @return array
		 */
		public function get_allowed_carriers() {
			if ( ! is_array( $this->allowed_carriers ) ) {
				$this->allowed_carriers = [ $this->allowed_carriers ];
			}
			return $this->allowed_carriers;
		}
		
		/**
		 * Clear Template transients.
		 *
		 * @return void
		 */
		public function clear_transients() {
			global $wpdb;

			$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_shipcloud_quote_%') OR `option_name` LIKE ('_transient_timeout_shipcloud_quote_%')" );
		}
		
		/**
		 * Calculate shipping costs.
         *
         * @param array $package Package to ship.
         * @return void
		 */
		public function calculate_shipping( $package = [] ) {
			
			// $this->log( '$package = ' . json_encode( $package ) );
			
			if ( 'yes' === $this->get_option( 'disable_calculation' ) ) {
				return;
			}

			if ( '' == $package['destination']['city']
			     || '' == $package['destination']['country']
			     || '' == $package['destination']['postcode']
			     || '' == $package['destination']['address']
			) {
				wc_add_notice(
					__( 'Please enter an address to calculate shipping costs.', 'shipcloud-for-woocommerce' ),
					'notice'
				);

				return; // Can't calculate without Address - Stop here!
			}

			/**
			 * Getting Adresses
			 */
			$this->sender 	 = $this->get_sender();
			$this->recipient = $this->get_recipient( $package );

			/**
			 * Order Parcels
			 */
			$parcel_dimensions 	= $this->get_parcel_dimensions( $package );
			$carriers 		 	= $this->get_allowed_carriers();
			
			if ( empty( $carriers ) ) {
				$this->log( 'Could not get carriers!', 'error' );
				return;
			}

			/**
			 * Calculating
			 */
			foreach( $carriers as $carrier_name ) {
			
				$costs = $this->get_shipping_costs( $parcel_dimensions );
				
				$shipping_rate = array(
					'id'    => $carrier_name,
					'label' => WC_Shipping_Shipcloud_Utils::get_carrier_display_name_short( $carrier_name ),
					'cost'  => $costs,
				);
			
				$this->add_rate( apply_filters( 'wc_shipping_shipcloud_add_rate', $shipping_rate, $package ) );
			}
		}
		
		/**
		 * Getting dimensions for each parcel
		 *
		 * @param array $ordered_package
		 * @return array $parcels
		 */
		private function get_parcel_dimensions( $package ) {
			$parcels			= [];
			$shipping_classes 	= $this->get_shipping_classes( $package );
			foreach ( $shipping_classes as $shipping_class => $products ) {
				if ( empty( $shipping_class ) ) {
					/* get dimensions for each product */
					foreach ( $products as $product ) {
						$parcels[ 'products' ][] = $this->get_product_dimensions( $product );
					}
					continue;
				}
				$parcels[ 'shipping_classes' ][ $shipping_class ] = $this->get_shipping_class_dimensions( $shipping_class );
			}
			
			return $parcels;
		}
		
		/**
		 * Getting shipping classes for order package
		 *
		 * @param $package Package given on
		 * @return array $shipping_classes
		 */
		private function get_shipping_classes( $package ) {
			$shipping_classes = [];
			foreach ( $package[ 'contents' ] as $item_id => $values ) {
				if ( $values[ 'data' ]->needs_shipping() ) {
					$found_class = $values[ 'data' ]->get_shipping_class();
					if ( ! isset( $shipping_classes[ $found_class ] ) ) {
						$shipping_classes[ $found_class ] = [];
					}
					$shipping_classes[ $found_class ][ $item_id ] = $values;
				}
			}

			return $shipping_classes;
		}
		
		/**
		 * Getting product dimensions
		 * 
		 * @param $product
		 * @return array
		 */
		private function get_product_dimensions( $product ) {
			$dimensions = array(
				'quantity' => $product['quantity']
			);

			$length = get_post_meta( $product['product_id'], '_length', true );
			$width  = get_post_meta( $product['product_id'], '_width', true );
			$height = get_post_meta( $product['product_id'], '_height', true );
			$weight = get_post_meta( $product['product_id'], '_weight', true );

			if ( '' !== $length && '' !== $width && '' !== $height && '' !== $weight ) {
				$dimensions = array(
					'length'   => $length,
					'width'    => $width,
					'height'   => $height,
					'weight'   => $weight,
					'quantity' => $product['quantity']
				);
			}

			return $dimensions;
		}

		/**
		 * Getting shipping class dimensions
		 * 
		 * @param $shipping_class
		 * @return array|null
		 */
		private function get_shipping_class_dimensions( $shipping_class ) {
			
			// $this->log( "get_shipping_class_dimensions( $shipping_class )" );
			
			/**
			 * Shipment Classes
			 */
			$taxonomy = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );

			$width  = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_width' );
			$height = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_height' );
			$length = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_length' );
			$weight = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_weight' );

			// If there is missing a dimension, set FALSE
			$dimensions = null;
			if ( $length && $width && $height && $weight ) {
				$dimensions = array(
					'length' => $length,
					'width'  => $width,
					'height' => $height,
					'weight' => $weight
				);
			}

			return $dimensions;
		}
		
		/**
		 * Calculate the costs for parcel shipping.
		 * 
		 * @param $parcel_dimensions
		 * @return float
		 */
		private function get_shipping_costs( $parcel_dimensions ) {
			$costs = 0;
			
			if ( isset( $parcel_dimensions['shipping_classes'] ) ) {
				$costs += $this->get_option( 'standard_price_shipment_classes' );
			}
			
			if ( isset( $parcel_dimensions['products'] ) ) {
				$costs += $this->get_option( 'standard_price_products' );
			}
			
			return $costs;
		}
		
		/**
		 * Getting sender address
		 *
		 * @return array $sender
		 */
		private function get_sender() {
			
			// $company = get_option( 'woocommerce_store_address' );
			
			return array(
				'last_name' => '',
				'street'    => get_option( 'woocommerce_store_address' ),
				'street_no' => get_option( 'woocommerce_store_address_2' ),
				'zip_code'  => get_option( 'woocommerce_store_postcode' ),
				'city'      => get_option( 'woocommerce_store_city' ),
				'state'     => '',
				'country'   => WC_Shipping_Shipcloud_Utils::maybe_extract_country_code( get_option( 'woocommerce_default_country' ) ),
				'phone'   	=> '',
			);
		}

		/**
		 * Getting recipient address from package
		 *
		 * @param $package
		 * @return array
		 */
		private function get_recipient( $package ) {
			$recipient_street_name = $recipient_street_nr = $package[ 'destination' ][ 'address' ];
			
			$recipient_street = WC_Shipping_Shipcloud_Utils::explode_street( $package[ 'destination' ][ 'address' ] );
			if ( is_array( $recipient_street ) ) {
				$recipient_street_name = $recipient_street[ 'address' ];
				$recipient_street_nr   = $recipient_street[ 'number' ];
			}
			
			return array(
				'last_name' => 'Recipient Name',
				'street'    => $recipient_street_name,
				'street_no' => $recipient_street_nr,
				'zip_code'  => isset($package[ 'destination' ][ 'zip_code' ]) ? $package[ 'destination' ][ 'zip_code' ] : $package[ 'destination' ][ 'postcode' ],
				'city'      => $package[ 'destination' ][ 'city' ],
				'state'     => $package[ 'destination' ][ 'state' ],
				'country'   => $package[ 'destination' ][ 'country' ]
			);
		}
		
		/**
		 * Adding form field for Address Field and enabling City field
		 *
		 * @param 	bool 	$wc_shipping_calculator_enable_city
		 * @return 	bool
		 */
		public function add_calculate_shipping_form_fields( $wc_shipping_calculator_enable_city ) {
			$wc_shipping_calculator_enable_city = true;
			?>
			<p class="form-row form-row-wide" id="calc_shipping_address_field">
				<input type="text" class="input-text" value="<?php echo esc_attr( WC()->customer->get_shipping_address() ); ?>" placeholder="<?php esc_attr_e( 'Address', 'woocommerce' ); ?>" name="calc_shipping_address" id="calc_shipping_address"/>
			</p>
			<?php
			return $wc_shipping_calculator_enable_city;
		}

		/**
		 * Setting Address field after submiting
		 * 
		 * @return void
		 */
		public function add_calculate_shipping_fields() {
			WC()->customer->set_shipping_address( $_POST[ 'calc_shipping_address' ] );
		}
		
		/**
		 * This method is called just before the settings are displayed.
		 *
		 * @return void
		 */
		public function admin_options() {
			
			$screen       = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';
			
			if ( $screen_id === 'woocommerce_page_wc-settings' ) {
				
				// Check users environment supports this method.
				$this->environment_check();
			
				// Enqueue plugin scripts and styles.
				wp_enqueue_style( 'shipcloud-admin' );
				wp_enqueue_style( 'jquery-multiselect' );
				wp_enqueue_script( 'jquery-multiselect' );
				wp_enqueue_script( 'shipcloud-admin' );
			
				// Show settings.
				parent::admin_options();
			}
		}
		
		/**
		 * This method is called once settings are saved.
		 *
		 * @return void
		 */
		public function process_admin_options() {
			parent::process_admin_options();
			$this->set_settings();
			do_action( 'wc_shipcloud_clear_admin_notices' );
		}
		
		/**
		 * Environment check.
		 *
		 * @return void
		 */
		private function environment_check() {
			
			$error_message = '';

			// Check for Template User ID.
			if ( ! $this->api ) {
				$error_message .= __( 'shipcloud is active, but shipcloud API is not available!', 'shipcloud-for-woocommerce' );
			}
			
			$this->check_webhook_preconditions();

			// Check environment only on shipping instance page.
			if ( 0 < $this->instance_id ) {
				
				// add further checks here
				
			}
			
			// Check for at least one shipping method enabled.
			if ( empty( $this->get_allowed_carriers() ) ) {
				$error_message .= __( 'shipcloud is active, but there are no shipping methods enabled.', 'shipcloud-for-woocommerce' );
			}
			
			if ( ! empty( $error_message ) ) {
				$this->add_admin_notice( $error_message, 'error' );
			}
		}
		
	    /**
	     * Check if webhook woocommerce api is enabled when shipcloud webhook should be used
	     *
		 * @return void
	     */
	    private function check_webhook_preconditions() {
			if ( is_admin() ) {
				$webhook_id 	 = get_option( 'woocommerce_shipcloud_catch_all_webhook_id' );
				$wc_api_enabled  = get_option( 'woocommerce_api_enabled' );
				
				if ( $this->options ) {
					if ( (
							$webhook_id &&
							array_key_exists( 'webhook_active', $plugin_settings ) &&
							$plugin_settings[ 'webhook_active' ] === 'yes' &&
							! isset( $_POST[ 'woocommerce_shipcloud_webhook_active' ] )
						) ||
						(
							! $webhook_id &&
							array_key_exists( 'webhook_active', $plugin_settings ) &&
							$plugin_settings[ 'webhook_active' ] === 'no' &&
							isset( $_POST[ 'woocommerce_shipcloud_webhook_active' ] )
						)
					) {
						
						$this->add_admin_notice(
							sprintf(
								__(
									'You have to activate the REST-API option in your <a href="%s">WooCommerce api settings</a>.',
									'shipcloud-for-woocommerce'
								),
								admin_url( 'admin.php?page=wc-settings&tab=advanced&section=legacy_api' )
							), 'error'
						);
						
					}

					// make sure the checkbox for active webhook is deactivated when no webhook id is present
					if ( ! $webhook_id && array_key_exists( 'webhook_active', $plugin_settings ) && $plugin_settings['webhook_active'] === 'yes') {
						$plugin_settings['webhook_active'] = 'no';
						update_option('woocommerce_shipcloud_settings', $plugin_settings);
					}
				}
			}
		}
		
		/**
		 * Generate multi-select HTML.
		 *
		 * @param string $key Field key.
		 * @param array  $data Field data.
		 * @return string
		 */
		public function generate_multiselect_html( $key, $data ) {
			
			$field_key = $this->get_field_key( $key );
			$defaults  = array(
				'title'             => '',
				'disabled'          => false,
				'class'             => '',
				'css'               => '',
				'placeholder'       => '',
				'type'              => 'text',
				'desc_tip'          => false,
				'description'       => '',
				'custom_attributes' => [],
				'select_buttons'    => false,
				'options'           => [],
			);

			$data  = wp_parse_args( $data, $defaults );
			$value = (array) $this->get_option( $key, array() );

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
						<select id="<?php echo esc_attr( $field_key ); ?>" name="<?php echo esc_attr( $field_key ); ?>[]" multiple="multiple" class="multiselect <?php echo esc_attr( $data['class'] ); ?>" autocomplete="off">
							<?php foreach( (array) $data['options'] as $option_group => $option_values ) : ?>
								<optgroup label="<?php echo esc_attr( $option_group ); ?>">
									<?php foreach( (array) $option_values as $option_key => $option_value ) : ?>
										<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( in_array( (string) $option_key, $value, true ), true ); ?>><?php echo esc_html( $option_value ); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>
						<?php echo $this->get_description_html( $data ); // WPCS: XSS ok. ?>
					</fieldset>
				</td>
			</tr>
			<?php
			
			return ob_get_clean();
		}
		
		/**
		 * Generate hidden HTML.
		 *
		 * @param string $key Field key.
		 * @param array  $data Field data.
		 * @return string
		 */
		public function generate_hidden_html( $key, $data ) {
			
			$field_key = $this->get_field_key( $key );
			$defaults  = array(
				'value'	=> '',
			);
			$data  = wp_parse_args( $data, $defaults );
			
			ob_start();
			?>
			<tr class="hidden">
				<td colspan=2>
					<input type="hidden" id="<?php echo esc_attr( $field_key ); ?>" name="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_html( $data['value'] ); ?>">
				</td>
			</tr>
			<?php
			
			return ob_get_clean();
		}
		
	    /**
		 * Getting option (overwrite instance values if there option of instance is empty
		 *
		 * @param string $key
		 * @param null   $empty_value
		 * @return mixed|string
		 */
		public function get_option( $key, $empty_value = null ) {
			$option = parent::get_option( $key, $empty_value );
			if ( ! empty( $option ) ) {
				return $option;
			}
			// If there is no value in instance settings get value from global settings
			return WC_Settings_API::get_option( $key, $empty_value );
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
