<?php
/**
 * WooCommerce Shipping method
 *
 * Class which extends the WC_Shipping_Method API
 *
 * @author  awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 (very@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) )
{
	exit;
}

if( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
{

	class WC_Shipcloud_Shippig extends WC_Shipping_Method
	{

		var $carriers = array();

		var $logger;

		var $debug = FALSE;

		/**
		 * Constructor for your shipping class
		 */
		public function __construct()
		{
			$this->id = 'shipcloud';
			$this->title = __( 'shipcloud.io', 'woocommerce-shipcloud' );
			$this->method_description = __( 'Add shipcloud to your shipping methods', 'woocommerce-shipcloud' );

			// Is gateway enabled
			if( is_array( $this->settings ) && array_key_exists( 'enabled', $this->settings ) && 'yes' == $this->settings[ 'enabled' ] )
			{
				$this->enabled = 'yes';
			}
			else
			{
				$this->enabled = 'no';
			}

			$this->init();

			if( class_exists( 'WC_Logger' ) )
			{
				$this->log = new WC_Logger();
			}
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		public function init()
		{
			$this->init_settings();
			$this->init_form_fields();

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Gateway settings
		 */
		public function init_form_fields()
		{
			global $woocommerce;

			$default_country = wc_get_base_location();
			$default_country = $default_country[ 'country' ];

			$shipcloud = new Woocommerce_Shipcloud_API( $this->settings[ 'api_key' ] );

			$carriers_options = array();
			if( $carriers = $shipcloud->get_carriers( TRUE ) )
			{
				foreach( $carriers as $carrier )
				{
					$carriers_options[ $carrier[ 'name' ] ] = $carrier[ 'display_name' ];
				}
			}

			$this->form_fields = array(
				'enabled'                           => array(
					'title'   => __( 'Enable', 'woocommerce-shipcloud' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable shipcloud.io', 'woocommerce-shipcloud' ),
					'default' => 'no'
				),
				'api_key'                           => array(
					'title'       => __( 'API Key', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter your <a href="%s" target="_blank">shipcloud.io API Key</a>.', 'woocommerce-shipcloud' ), 'https://app.shipcloud.io/de/users/api_key' ),
				),
				'allowed_carriers'                  => array(
					'title'       => __( 'Carriers', 'woocommerce-shipcloud' ),
					'type'        => 'multi_checkbox',
					'description' => __( 'Select the Carriers which you want to use in your Shop.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
					'options'     => $carriers_options
				),
				'debug'                             => array(
					'title'   => __( 'Debug', 'woocommerce-shipcloud' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable logging if you experience problems.', 'woocommerce-shipcloud' ),
					'default' => 'no'
				),
				'calculation'                       => array(
					'title'       => __( 'Automatic Price Calculation', 'woocommerce-shipcloud' ),
					'type'        => 'title',
					'description' => sprintf( __( 'To get a price for the customers order, you have to setup the price calculation.', 'woocommerce-shipcloud' ) )
				),
				'calculate_products_type'           => array(
					'title'       => __( 'Calculate Products', 'woocommerce-shipcloud' ),
					'type'        => 'select',
					'description' => __( 'How should the price for products be calculated.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
					'class'       => 'wc-enhanced-select',
					'default'     => 'class',
					'options'     => array(
						'product' => __( 'Per Product: Charge shipping for each Product individually', 'woocommerce-shipcloud' ),
						'order'   => __( 'Per Order: Charge shipping for the most expensive shipping for a product', 'woocommerce-shipcloud' ),
						// todo Wording is bad!
					)
				),
				'standard_price_products'           => array(
					'title'       => __( 'Standard Price', 'woocommerce-shipcloud' ),
					'type'        => 'price',
					'description' => __( 'Will be used if no sizes or weight is given to a Product.', 'woocommerce-shipcloud' ),
				),
				'calculation_type_shipment_classes' => array(
					'title'       => __( 'Calculate Shipment Classes', 'woocommerce-shipcloud' ),
					'type'        => 'select',
					'description' => __( 'How should the price for shipment classes be calculated.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
					'class'       => 'wc-enhanced-select',
					'default'     => 'class',
					'options'     => array(
						'class' => __( 'Per Class: Charge shipping for each shipping class individually', 'woocommerce' ),
						'order' => __( 'Per Order: Charge shipping for the most expensive shipping class', 'woocommerce' ),
					)
				),
				'standard_price_shipment_classes'   => array(
					'title'       => __( 'Standard Price', 'woocommerce-shipcloud' ),
					'type'        => 'price',
					'description' => __( 'Will be used if no sizes or weight is given to a Shipment Class.', 'woocommerce-shipcloud' ),
				),
				'carrier_selection'                 => array(
					'title'       => __( 'Carrier Selection', 'woocommerce-shipcloud' ),
					'type'        => 'select',
					'description' => __( 'Who can select the carrier?', 'woocommerce-shipcloud' ),
					'class'       => 'wc-enhanced-select',
					'desc_tip'    => TRUE,
					'default'     => 'shopowner',
					'options'     => array(
						'shopowner' => __( 'Shop Owner can select Carrier', 'woocommerce-shipcloud' ),
						'customer'  => __( 'Customer can select Carrier', 'woocommerce-shipcloud' ),
					)
				),
				'standard_carrier'                  => array(
					'title'       => __( 'Standard Carrier', 'woocommerce-shipcloud' ),
					'type'        => 'select',
					'description' => __( 'This Carrier will be preselected if the Shop Owner selects the Carrier or will be preselected as Carrier if Customer can select the Carrier.', 'woocommerce-shipcloud' ),
					'options'     => wcsc_get_carriers(),
					'desc_tip'    => TRUE
				),
				'standard_sender_data'              => array(
					'title'       => __( 'Standard sender data', 'woocommerce-shipcloud' ),
					'type'        => 'title',
					'description' => sprintf( __( 'Setup your standard sender data for sending parcels.', 'woocommerce-shipcloud' ) ),
				),
				'sender_company'                    => array(
					'title'       => __( 'Company', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender company for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_first_name'                 => array(
					'title'       => __( 'First Name', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender first name for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_last_name'                  => array(
					'title'       => __( 'Last Name', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender last name for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_street'                     => array(
					'title'       => __( 'Street', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender street for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_street_nr'                  => array(
					'title'       => __( 'Street number', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender street number for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_postcode'                   => array(
					'title'       => __( 'Postcode', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender postcode for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_city'                       => array(
					'title'       => __( 'City', 'woocommerce-shipcloud' ),
					'type'        => 'text',
					'description' => __( 'Enter standard sender city for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
				),
				'sender_country'                    => array(
					'title'       => __( 'Country', 'woocommerce-shipcloud' ),
					'type'        => 'select',
					'description' => __( 'Enter standard sender country for shipment.', 'woocommerce-shipcloud' ),
					'desc_tip'    => TRUE,
					'class'       => 'wc-enhanced-select',
					'options'     => $woocommerce->countries->countries,
					'default'     => $default_country
				),
			);
		}

		/**
		 * Multi Checkbox HTML
		 *
		 * @param $key
		 * @param $data
		 *
		 * @return string
		 */
		public function generate_multi_checkbox_html( $key, $data )
		{

			$field = $this->get_field_key( $key );
			$defaults = array(
				'title'             => '',
				'disabled'          => FALSE,
				'class'             => '',
				'css'               => '',
				'placeholder'       => '',
				'type'              => 'text',
				'desc_tip'          => FALSE,
				'description'       => '',
				'custom_attributes' => array(),
				'options'           => array()
			);

			$data = wp_parse_args( $data, $defaults );
			$value = (array) $this->get_option( $key, array() );

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php _e( $field ); ?>"><?php echo wp_kses_post( $data[ 'title' ] ); ?></label>
					<?php echo $this->get_tooltip_html( $data ); ?>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data[ 'title' ] ); ?></span>
						</legend>
						<div class="multi-checkbox <?php _e( $data[ 'class' ] ); ?>" id="<?php _e( $field ); ?>" style="<?php _e( $data[ 'css' ] ); ?>" <?php disabled( $data[ 'disabled' ], TRUE ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>>
							<?php foreach( (array) $data[ 'options' ] as $option_key => $option_value ) : ?>
								<div>
									<input type="checkbox" name="<?php _e( $field ); ?>[]" value="<?php _e( $option_key ); ?>" <?php checked( in_array( $option_key, $value ), TRUE ); ?>> <?php _e( $option_value ); ?>
								</div>
							<?php endforeach; ?>
						</div>
						<?php echo $this->get_description_html( $data ); ?>
					</fieldset>
				</td>
			</tr>
			<?php

			return ob_get_clean();
		}

		/**
		 * Validating multi_checkbox field and sanitizing it
		 *
		 * @param $key
		 *
		 * @return array
		 */
		public function validate_multi_checkbox_field( $key )
		{
			$field = $this->get_field_key( $key );

			if( isset( $_POST[ $field ] ) )
			{
				$value = array_map( 'wc_clean', array_map( 'stripslashes', (array) $_POST[ $field ] ) );
			}
			else
			{
				$value = '';
			}

			return $value;
		}

		/**
		 * calculate_shipping function
		 *
		 * @access public
		 *
		 * @param mixed $packages
		 *
		 * @return void
		 */
		public function calculate_shipping( $package )
		{
			if( '' == $package[ 'destination' ][ 'city' ] || '' == $package[ 'destination' ][ 'country' ] || '' == $package[ 'destination' ][ 'postcode' ] || '' == $package[ 'destination' ][ 'address' ] )
			{
				return; // Can't calculate without Address - Stop here!
			}

			$settings = get_option( 'woocommerce_shipcloud_settings' );
			$shipcloud_api = new Woocommerce_Shipcloud_API( $settings[ 'api_key' ] );

			/**
			 * Getting Adresses
			 */
			$sender = array(
				'street'     => $settings[ 'sender_street' ],
				'street_no'  => $settings[ 'sender_street_nr' ],
				'zip_code'   => $settings[ 'sender_postcode' ],
				'city'       => $settings[ 'sender_city' ],
				'country'    => $settings[ 'sender_country' ],
			);

			$recipient_street = wcsc_explode_street( $package[ 'destination' ][ 'address' ] );

			if( is_array( $recipient_street ) )
			{
				$recipient_street_name = $recipient_street[ 'address' ];
				$recipient_street_nr = $recipient_street[ 'number' ];
			}

			$recipient = array(
				'street'    => $recipient_street_name,
				'street_no' => $recipient_street_nr,
				'zip_code'  => $package[ 'destination' ][ 'postcode' ],
				'city'      => $package[ 'destination' ][ 'city' ],
				'country'   => $package[ 'destination' ][ 'country' ]
			);

			/**
			 * Ordering Parcels
			 */
			$ordered_package = wcsc_order_package_by_shipping_class( $package );
			$parcels = wcsc_get_order_parcels( $ordered_package );

			/**
			 * Setup Carrier
			 */
			if( 'shopowner' == $settings[ 'carrier_selection' ] )
			{
				$carriers = array( $settings[ 'standard_carrier' ] => wcsc_get_carrier_display_name( $settings[ 'standard_carrier' ] ) );
			}
			else
			{
				$carriers = wcsc_get_carriers();
			}

			/**
			 * Calculating
			 */
			$prices = array();
			$calculated_parcels = array();

			foreach( $carriers AS $carrier_name => $carrier_display_name )
			{
				$sum = 0;

				// Shipping Classes
				if( isset( $parcels[ 'shipping_classes' ] ) )
				{

					// Running each Shipping Class
					foreach( $parcels[ 'shipping_classes' ] AS $key => $parcel )
					{

						if( is_array( $parcel ) )
						{
							$shipment = array(
								'carrier' => $carrier_name,
								'service' => 'standard',
								'to'      => $recipient,
								'from'    => $sender,
								'package' => array(
									'width'  => $parcel[ 'width' ],
									'height' => $parcel[ 'height' ],
									'length' => $parcel[ 'length' ],
									'weight' => str_replace( ',', '.', $parcel[ 'weight' ] ),
								)
							);

							$calculated_parcels[ $carrier_name ][] = array(
								'carrier'=> $carrier_name,
								'width'  => $parcel[ 'width' ],
								'height' => $parcel[ 'height' ],
								'length' => $parcel[ 'length' ],
								'weight' => str_replace( ',', '.', $parcel[ 'weight' ] )
							);

							$price = $shipcloud_api->get_price( $shipment );
						}
						else
						{
							$price = $parcel;
						}

						if( 'class' == $settings[ 'calculation_type_shipment_classes' ] )
						{
							$sum += $price;
						}
						else
						{
							$sum = $price > $sum ? $price : $sum;
						}
					}
				}

				// Products
				if( isset( $parcels[ 'products' ] ) )
				{
					// Running each Product
					foreach( $parcels[ 'products' ] AS $key => $parcel )
					{
						if( is_array( $parcel ) )
						{
							$shipment = array(
								'carrier' => $carrier_name,
								'service' => 'standard',
								'to'      => $recipient,
								'from'    => $sender,
								'package' => array(
									'width'  => $parcel[ 'width' ],
									'height' => $parcel[ 'height' ],
									'length' => $parcel[ 'length' ],
									'weight' => str_replace( ',', '.', $parcel[ 'weight' ] ),
								)
							);

							$calculated_parcels[ $carrier_name ][] = array(
								'carrier'=> $carrier_name,
								'width'  => $parcel[ 'width' ],
								'height' => $parcel[ 'height' ],
								'length' => $parcel[ 'length' ],
								'weight' => str_replace( ',', '.', $parcel[ 'weight' ] )
							);

							$price = $shipcloud_api->get_price( $shipment );
						}
						else
						{
							$price = $parcel;
						}

						if( 'product' == $settings[ 'calculate_products_type' ] )
						{
							$sum += $price;
						}
						else
						{
							$sum = $price > $sum ? $price : $sum;
						}
					}
				}

				$prices[ $carrier_name ] = $sum;

				$rate = array(
					'id'    => $carrier_name,
					'label' => strtoupper( $carrier_name ),
					'cost'  => $sum,
				);

				$this->add_rate( $rate );
			}

			// $this->log( print_r( $package, TRUE ) );
			// $this->log( print_r( $found_shipping_classes, TRUE ) );

			// Register the rate
			// $this->add_rate( $rate );
		}

		/**
		 * Get price for parcel which have been selected in Shipping Class.
		 *
		 * @param string $shipping_class
		 *
		 * @return float $costs
		 */
		public function get_shipping_class_costs( $shipping_class )
		{
			$term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );

			if( !is_object( $term ) )
			{
				$this->log( sprintf( __( 'No term found for shipping class #%s', 'woocommerce-shipcloud' ), $shipping_class ) );

				return FALSE;
			}

			$parcel_id = get_option( 'wcsc_shipping_class_' . $term->term_id . '_parcel_id', 0 );

			if( 0 == $parcel_id )
			{
				$this->log( sprintf( __( 'No parcel found for product id #%s', 'woocommerce-shipcloud' ), $product_id ) );
			}

			$retail_price = $this->get_parcel_retail_price( $parcel_id );

			return $retail_price;
		}

		/**
		 * Get price for parcel which have been selected in product.
		 *
		 * @param $product_id
		 */
		public function get_product_costs( $product_id )
		{
			$parcel_id = get_post_meta( $product_id, '_wcsc_parcel_id', TRUE );
			$retail_price = $this->get_parcel_retail_price( $parcel_id );

			return $retail_price;
		}

		/**
		 * Get retail price for parcel.
		 *
		 * @param $parcel_id
		 */
		public function get_parcel_retail_price( $parcel_id = 0 )
		{
			if( 0 != $parcel_id && '' != $parcel_id )
			{
				// Getting price of parcel, selected in the shipping class
				$parcels = wcsc_get_parceltemplates( array( 'include' => $parcel_id ) );
				$retail_price = $parcels[ 0 ][ 'values' ][ 'retail_price' ];
			}

			// Price fallback
			if( '' == $retail_price )
			{
				$retail_price = $this->settings[ 'standard_price' ];
				$this->log( sprintf( __( 'No price found for parcel. Using fallback price %s', 'woocommerce-shipcloud' ), $retail_price ) );
			}

			return $retail_price;
		}

		/**
		 * Adding logentry on debug mode
		 *
		 * @param $message
		 */
		public function log( $message )
		{
			if( 'yes' == $this->settings[ 'debug' ] )
			{
				$this->log->add( 'shipcloud', $message );
			}
		}
	}
}