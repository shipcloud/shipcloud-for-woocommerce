<?php
/**
 * WooCommerce shipcloud.io postboxes
 * Loading postboxes
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2016 (support@awesome.ug)
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
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct()
	{
		$this->init_hooks();
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
		add_action( 'wp_ajax_shipcloud_delete_shipment', array( $this, 'ajax_delete_shipment' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1 );
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
	 * Adding meta boxes
	 *
	 * @since 1.0.0
	 */
	public function add_metaboxes()
	{
		add_meta_box( 'shipcloud-io', __( 'shipcloud.io Shipment-Center', 'woocommerce-shipcloud' ), array(
			$this,
			'shipment_center'
		), 'shop_order' );
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
		$options = get_option( 'woocommerce_shipcloud_settings' );

		$sender    = get_post_meta( $this->order_id, 'shipcloud_sender_address', true );
		$recipient = get_post_meta( $this->order_id, 'shipcloud_recipient_address', true );

		// Use default data if nothing was saved before
		if ( '' == $sender || 0 == count( $sender ) )
		{
			$sender = array(
				'first_name' => $options[ 'sender_first_name' ],
				'last_name'  => $options[ 'sender_last_name' ],
				'company'    => $options[ 'sender_company' ],
				'street'     => $options[ 'sender_street' ],
				'street_nr'  => $options[ 'sender_street_nr' ],
				'postcode'   => $options[ 'sender_postcode' ],
				'city'       => $options[ 'sender_city' ],
				'country'    => $options[ 'sender_country' ],
			);
		}

		// Use default data if nothing was saved before
		if ( '' == $recipient || 0 == count( $recipient ) )
		{
			$order = new WC_Order( $this->order_id );

			$recipient_street_name = '';
			$recipient_street_nr   = '';
			$recipient_street      = wcsc_explode_street( $order->shipping_address_1 );

			if ( is_array( $recipient_street ) )
			{
				$recipient_street_name = $recipient_street[ 'address' ];
				$recipient_street_nr   = $recipient_street[ 'number' ];
			}

			$recipient = array(
				'first_name' => $order->shipping_first_name,
				'last_name'  => $order->shipping_last_name,
				'company'    => $order->shipping_company,
				'street'     => $recipient_street_name,
				'street_nr'  => $recipient_street_nr,
				'postcode'   => $order->shipping_postcode,
				'city'       => $order->shipping_city,
				'country'    => $order->shipping_country,
			);
		}

		return array(
			'sender' => $sender,
			'recipient' => $recipient
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

					<h3><?php _e( 'Sender Address', 'woocommerce-shipcloud' ); ?>
						<a class="btn-edit-address"><img width="14" alt="Edit" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/icons/edit.png"></a>
					</h3>

					<p class="fullsize">
						<input type="text" name="sender_address[company]" value="<?php echo $sender[ 'company' ]; ?>" disabled>
						<label for="sender_address[company]"><?php _e( 'Company', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="sender_address[first_name]" value="<?php echo $sender[ 'first_name' ]; ?>" disabled>
						<label for="sender_address[first_name]"><?php _e( 'First Name', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="sender_address[last_name]" value="<?php echo $sender[ 'last_name' ]; ?>" disabled>
						<label for="sender_address[last_name]"><?php _e( 'Last Name', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="seventyfive">
						<input type="text" name="sender_address[street]" value="<?php echo $sender[ 'street' ]; ?>" disabled>
						<label for="sender_address[street]"><?php _e( 'Street', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="twentyfive">
						<input type="text" name="sender_address[street_nr]" value="<?php echo $sender[ 'street_nr' ]; ?>" disabled>
						<label for="sender_address[street_nr]"><?php _e( 'Number', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="sender_address[postcode]" value="<?php echo $sender[ 'postcode' ]; ?>" disabled>
						<label for="sender_address[postcode]"><?php _e( 'Postcode', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="sender_address[city]" value="<?php echo $sender[ 'city' ]; ?>" disabled>
						<label for="sender_address[city]"><?php _e( 'City', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="fullsize">
						<select name="sender_address[country]" disabled>
							<?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
								<?php if ( $key == $sender[ 'country' ] ): $selected = ' selected';
								else: $selected = ''; endif; ?>
								<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
							<?php endforeach; ?>
						</select>
						<label for="sender_address[country]"><?php _e( 'Country', 'woocommerce-shipcloud' ); ?></label>
					</p>
				</div>
			</div>

			<div class="address fifty">
				<div class="address-form recipient disabled">

					<h3><?php _e( 'Recipient Address', 'woocommerce-shipcloud' ); ?>
						<a class="btn-edit-address"><img width="14" alt="Edit" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/icons/edit.png"></a>
					</h3>

					<p class="fullsize">
						<input type="text" name="recipient_address[company]" value="<?php echo $recipient[ 'company' ]; ?>" disabled>
						<label for="recipient_address[company]"><?php _e( 'Company', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="recipient_address[first_name]" value="<?php echo $recipient[ 'first_name' ]; ?>" disabled>
						<label for="recipient_address[first_name]"><?php _e( 'First Name', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="recipient_address[last_name]" value="<?php echo $recipient[ 'last_name' ]; ?>" disabled>
						<label for="recipient_address[last_name]"><?php _e( 'Last Name', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="seventyfive">
						<input type="text" name="recipient_address[street]" value="<?php echo $recipient[ 'street' ]; ?>" disabled>
						<label for="recipient_address[street]"><?php _e( 'Street', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="twentyfive">
						<input type="text" name="recipient_address[street_nr]" value="<?php echo $recipient[ 'street_nr' ]; ?>" disabled>
						<label for="recipient_address[street_nr]"><?php _e( 'Number', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="recipient_address[postcode]" value="<?php echo $recipient[ 'postcode' ]; ?>" disabled>
						<label for="recipient_address[postcode]"><?php _e( 'Postcode', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="fullsize">
						<input type="text" name="recipient_address[city]" value="<?php echo $recipient[ 'city' ]; ?>" disabled>
						<label for="recipient_address[city]"><?php _e( 'City', 'woocommerce-shipcloud' ); ?></label>
					</p>

					<p class="fullsize">
						<select name="recipient_address[country]" disabled>
							<?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
								<?php if ( $key == $recipient[ 'country' ] ): $selected = ' selected';
								else: $selected = ''; endif; ?>
								<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
							<?php endforeach; ?>
						</select>
						<label for="recipient_address[country]"><?php _e( 'Country', 'woocommerce-shipcloud' ); ?></label>
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
		ob_start();
		?>
		<div class="section parcels">
			<h3><?php _e( 'Create Shipment', 'woocommerce-shipcloud' ); ?></h3>

			<?php echo $this->parcel_form(); ?>
			<?php echo $this->parcel_templates(); ?>

			<div class="clear"></div>

			<div id="button-actions">
				<input id="shipcloud_calculate_price" type="button" value="<?php _e( 'Calculate Price', 'woocommerce-shipcloud' ); ?>" class="button"/>
				<input id="shipcloud_create_shipment" type="button" value="<?php _e( 'Prepare Label', 'woocommerce-shipcloud' ); ?>" class="button"/>
				<input id="shipcloud_create_shipment_label" type="button" value="<?php _e( 'Create Label', 'woocommerce-shipcloud' ); ?>" class="button-primary"/>
				<input id="shipcloud_create_shipment_return" type="button" value="<?php _e( 'Prepare Return Label', 'woocommerce-shipcloud' ); ?>" class="button"/>
				<input id="shipcloud_create_shipment_return_label" type="button" value="<?php _e( 'Create Return Label', 'woocommerce-shipcloud' ); ?>" class="button-primary"/>
			</div>

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

		$package = array();
		$package['destination']['country']   = $recipient['country'];
		$package['destination']['postcode']  = $recipient['postcode'];
		$package['destination']['city']      = $recipient['city'];
		$package['destination']['address']   = $recipient['street'] . ' ' . $recipient['street_nr'];

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
			if( 0 === count( $carriers ) )
			{
				$carriers = wcsc_shipping_method()->get_allowed_carriers();
			}
		}
		else
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
	private function parcel_form()
	{
		$order = new WC_Order( $this->order_id );

		$carriers = $this->get_carriers();

		$options          = get_option( 'woocommerce_shipcloud_settings' );
		$standard_carrier = $options[ 'standard_carrier' ];
		$shipcloud_api    = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );


		$selected_shipping_method = '';
		$shipping_methods         = $order->get_shipping_methods();

		foreach ( $shipping_methods AS $shipping_method )
		{
			if ( 'shipping' === $shipping_method[ 'type' ] )
			{
				$selected_shipping_method = $shipping_method[ 'method_id' ];
				break;
			}
		}
		$shipping_method_name = $order->get_shipping_method();

		ob_start();
		?>
		<div class="create-label fifty">

			<table class="parcel-form-table">
				<tbody>
				<tr>
					<th><?php _e( 'Width', 'woocommerce-shipcloud' ); ?></th>
					<td>
						<input type="text" name="parcel_width" class="lengths" /> <?php _e( 'cm', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Height', 'woocommerce-shipcloud' ); ?></th>
					<td>
						<input type="text" name="parcel_height" class="lengths" /> <?php _e( 'cm', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Length', 'woocommerce-shipcloud' ); ?> </th>
					<td>
						<input type="text" name="parcel_length" class="lengths" /> <?php _e( 'cm', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Weight', 'woocommerce-shipcloud' ); ?></th>
					<td>
						<input type="text" name="parcel_weight" class="lengths" /> <?php _e( 'kg', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Shipment Method', 'woocommerce-shipcloud' ); ?></th>
					<td>
						<?php if ( count( $carriers ) > 0 ): ?>
							<select name="parcel_carrier">
								<?php foreach ( $carriers AS $name => $display_name ): ?>
									<?php if ( $name === $selected_shipping_method ): ?>
										<option value="<?php echo $name; ?>" selected><?php echo $shipcloud_api->get_carrier_display_name_short( $name ); ?></option>
									<?php elseif ( $name === $standard_carrier && empty( $selected_shipping_method ) ): ?>
										<option value="<?php echo $name; ?>" selected><?php echo $shipcloud_api->get_carrier_display_name_short( $name ); ?></option>
									<?php else: ?>
										<option value="<?php echo $name; ?>"><?php echo $shipcloud_api->get_carrier_display_name_short( $name ); ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						<?php else: ?>
							<?php echo sprintf( __( '<a href="%s">Please select a Carrier</a>.', 'woocommerce-shipcloud' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' ) ); ?>
						<?php endif; ?>
						<?php if ( ! empty( $shipping_method_name ) ): ?>
							<br/>
							<small><?php echo sprintf( __( 'Ordered: %s', 'woocommerce-shipcloud' ), $shipping_method_name ); ?></small>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Description', 'woocommerce-shipcloud' ); ?></th>
					<td>
						<input type="text" name="parcel_description"/> <small><?php echo sprintf( __( 'Required for carriers: %s', 'woocommerce-shipcloud' ), 'DPD' ); ?></small>
					</td>
				</tr>
				</tbody>
			</table>

			<div class="clear"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Returns Parcel Template Form
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function parcel_templates()
	{
		$options       = get_option( 'woocommerce_shipcloud_settings' );
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );

		$args = array(
			'post_type'   => 'sc_parcel_template',
			'post_status' => 'publish'
		);

		$posts = get_posts( $args );

		$parcel_templates = array();

		if ( is_array( $posts ) && count( $posts ) > 0 )
		{
			foreach ( $posts AS $post )
			{
				$parcel_templates[] = array(
					'value'  => get_post_meta( $post->ID, 'width', true ) . ';' . get_post_meta( $post->ID, 'height', true ) . ';' . get_post_meta( $post->ID, 'length', true ) . ';' . get_post_meta( $post->ID, 'weight', true ) . ';' . get_post_meta( $post->ID, 'carrier', true ) . ';',
					'option' => get_post_meta( $post->ID, 'width', true ) . esc_attr( 'x', 'woocommerce-shipcloud' ) . get_post_meta( $post->ID, 'height', true ) . esc_attr( 'x', 'woocommerce-shipcloud' ) . get_post_meta( $post->ID, 'length', true ) . esc_attr( 'cm', 'woocommerce-shipcloud' ) . ' - ' . get_post_meta( $post->ID, 'weight', true ) . esc_attr( 'kg', 'woocommerce-shipcloud' ) . ' - ' . $shipcloud_api->get_carrier_display_name_short( get_post_meta( $post->ID, 'carrier', true ) ),
				);
			}
		}

		$shipcloud_parcels  = get_post_meta( $this->order_id, 'shipcloud_parcels', true );
		$determined_parcels = array();

		if ( is_array( $shipcloud_parcels ) && count( $shipcloud_parcels ) > 0 )
		{
			foreach ( $shipcloud_parcels AS $carrier_name => $parcels )
			{
				foreach( $parcels AS $parcel )
				{
					$determined_parcels[] = array(
						'value'  => $parcel[ 'width' ] . ';' . $parcel[ 'height' ] . ';' . $parcel[ 'length' ] . ';' . $parcel[ 'weight' ] . ';' . $carrier_name . ';',
						'option' => $parcel[ 'width' ] . esc_attr( 'x', 'woocommerce-shipcloud' ) . $parcel[ 'height' ] . esc_attr( 'x', 'woocommerce-shipcloud' ) . $parcel[ 'length' ] . esc_attr( 'cm', 'woocommerce-shipcloud' ) . ' - ' . $parcel[ 'weight' ] . esc_attr( 'kg', 'woocommerce-shipcloud' ) . ' - ' . $shipcloud_api->get_carrier_display_name_short( $carrier_name ),
					);
				}
			}
		}

		ob_start();

		?>
		<div class="parcel-templates fifty">

			<div class="parcel-template-field parcels-recommended">
				<label for="parcels_recommended"><?php _e( 'Automatic determined Parcels', 'woocommerce-shipcloud' ); ?></label>
				<?php if ( count( $determined_parcels ) > 0 ) : ?>
					<input type="button" value="<?php _e( '&#8592; Insert', 'woocommerce-shipcloud' ); ?>" class="insert-to-form button"/>
					<select name="parcel_list">
						<option value="none"><?php _e( '[ Select a Parcel ]', 'woocommerce-shipcloud' ); ?></option>

						<?php foreach ( $determined_parcels AS $determined_parcel ): ?>
							<option value="<?php echo $determined_parcel[ 'value' ]; ?>"><?php echo $determined_parcel[ 'option' ]; ?></option>
						<?php endforeach; ?>
					</select>
				<?php else: ?>
					<p><?php _e( 'Please add weight and parcel dimensions in your Products to use automatic calculations.', 'woocommerce-shipcloud' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="parcel-template-field parcels-templates">
				<label for="parcel_templates"><?php _e( 'Your Parcel Templates', 'woocommerce-shipcloud' ); ?></label>
				<?php if ( count( $parcel_templates ) > 0 ) : ?>
					<input type="button" value="<?php _e( '&#8592; Insert', 'woocommerce-shipcloud' ); ?>" class="insert-to-form button"/>
					<select name="parcel_list">
						<option value="none"><?php _e( '[ Select a Parcel ]', 'woocommerce-shipcloud' ); ?></option>

						<?php foreach ( $parcel_templates AS $parcel_template ): ?>
							<option value="<?php echo $parcel_template[ 'value' ]; ?>"><?php echo $parcel_template[ 'option' ]; ?></option>
						<?php endforeach; ?>
					</select>
				<?php else: ?>
					<p><?php echo sprintf( __( 'Please <a href="%s">add parcel templates</a> if you want to use.', 'woocommerce-shipcloud' ), admin_url( 'edit.php?post_type=sc_parcel_template' ) ); ?></p>
				<?php endif; ?>
			</div>

		</div>
		<?php

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

		ob_start();
		?>

		<div class="info"></div>

		<div id="create_label">

			<div class="shipping-data">
				<div class="shipment-labels">
					<?php

					if ( '' != $shipment_data && is_array( $shipment_data ) )
					{
						$shipment_data = array_reverse( $shipment_data );

						foreach ( $shipment_data AS $data )
						{
							echo $this->get_label_html( $data );
						}
					}

					?>
				</div>
				<div style="clear: both"></div>
			</div>
		</div>
		<div id="ask-create-label"><?php _e( 'Depending on the carrier, there will be a fee fo for creating the label. Do you really want to create a label?', 'woocommerce-shipcloud' ); ?></div>
		<div id="ask-delete-shipment"><?php _e( 'Do you really want to delete this shipment?', 'woocommerce-shipcloud' ); ?></div>
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
		ob_start();

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

		?>
	<div id="shipment-<?php echo $data[ 'id' ]; ?>" class="label widget">
		<div class="widget-top">
			<div class="widget-title-action">
				<a class="widget-action hide-if-no-js"></a>
			</div>
			<div class="widget-title">
				<img class="shipcloud-widget-icon" src="<?php echo WCSC_URLPATH; ?>assets/icons/truck-32x32.png"/>
				<?php

				$title = trim( $data[ 'sender_company' ] ) != '' ? $data[ 'sender_company' ] . ', ' . $data[ 'sender_first_name' ] . ' ' . $data[ 'sender_last_name' ] : $data[ 'sender_first_name' ] . ' ' . $data[ 'sender_last_name' ];
				$title .= ' &gt; ';
				$title .= trim( $data[ 'recipient_company' ] ) != '' ? $data[ 'recipient_company' ] . ', ' . $data[ 'recipient_first_name' ] . ' ' . $data[ 'recipient_last_name' ] : $data[ 'recipient_first_name' ] . ' ' . $data[ 'recipient_last_name' ];
				$title .= ' | <small>' . $data[ 'parcel_title' ] . '</small>';

				?>
				<h4><?php echo $title; ?></h4>
			</div>
		</div>
		<div class="widget-inside">
			<div class="widget-content">
				<div class="data">

					<div class="label-shipment-sender address">
						<div class="sender_company"><?php echo $data[ 'sender_company' ]; ?></div>
						<div class="sender_name"><?php echo $data[ 'sender_first_name' ]; ?><?php echo $data[ 'sender_last_name' ]; ?></div>
						<div class="sender_street"><?php echo $data[ 'sender_street' ]; ?><?php echo $data[ 'sender_street_no' ]; ?></div>
						<div class="sender_city"><?php echo $data[ 'sender_zip_code' ]; ?><?php echo $data[ 'sender_city' ]; ?></div>
						<div class="sender_country"><?php echo $data[ 'country' ]; ?></div>
					</div>

					<div class="label-shipment-recipient address">
						<div class="recipient_company"><?php echo $data[ 'recipient_company' ]; ?></div>
						<div class="recipient_name"><?php echo $data[ 'recipient_first_name' ]; ?><?php echo $data[ 'recipient_last_name' ]; ?></div>
						<div class="recipient_street"><?php echo $data[ 'recipient_street' ]; ?><?php echo $data[ 'recipient_street_no' ]; ?></div>
						<div class="recipient_city"><?php echo $data[ 'recipient_zip_code' ]; ?><?php echo $data[ 'recipient_city' ]; ?></div>
						<div class="recipient_country"><?php echo $data[ 'recipient_country' ]; ?></div>
					</div>

					<div class="label-shipment-actions">

						<p class="button-create-label<?php echo $classes_button_create_label; ?>">
							<input type="button" value="<?php _e( 'Create Label', 'woocommerce-shipcloud' ); ?>" class="shipcloud_create_label button-primary"/>
						</p>
						<p class="button-download-label<?php echo $classes_button_download_label; ?>">
							<a href="<?php echo $data[ 'label_url' ]; ?>" target="_blank" class="button"><?php _e( 'Download Label', 'woocommerce-shipcloud' ); ?></a>
						</p>

						<p class="button-tracking-url">
							<a href="<?php echo $data[ 'tracking_url' ]; ?>" target="_blank" class="button"><?php _e( 'Tracking Link', 'woocommerce-shipcloud' ); ?></a>
						</p>

						<p class="button-delete-shipment">
							<input type="button" value="<?php _e( 'Delete Shipment', 'woocommerce-shipcloud' ); ?>" class="shipcloud_delete_shipment button"/>
						</p>

						<input type="hidden" name="carrier" value="<?php echo $data[ 'carrier' ]; ?>"/>
						<input type="hidden" name="shipment_id" value="<?php echo $data[ 'id' ]; ?>"/>
					</div>

					<div style="clear: both;"></div>

					<div class="label-shipment-status">
						<table>
							<tbody>
							<tr>
								<th><?php _e( 'Description:', 'woocommerce-shipcloud' ); ?></th>
								<td><?php echo $data[ 'description' ]; ?></td>
							</tr>
							<tr>
								<th><?php _e( 'Shipment ID:', 'woocommerce-shipcloud' ); ?></th>
								<td><?php echo $display_id; ?></td>
							</tr>
							<tr>
								<th><?php _e( 'Tracking Status:', 'woocommerce-shipcloud' ); ?></th>
								<td><?php echo $shipment_status; ?></td>
							</tr>
							<?php if ( ! empty( $data[ 'price' ] ) ): ?>
								<tr>
									<th><?php _e( 'Price:', 'woocommerce-shipcloud' ); ?></strong></th>
									<td><?php echo wc_price( $data[ 'price' ], array( 'currency' => 'EUR' ) ); ?></td>
								</tr>
							<?php endif; ?>
							</tbody>
						</table>
					</div>

					<div style="clear: both;"></div>

				</div>
			</div>
		</div>
		</div><?php

		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Saving product metabox
	 *
	 * @param int $post_id
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function save_settings( $post_id )
	{
		if ( ! isset( $_POST[ 'save_settings' ] ) )
		{
			return $post_id;
		}

		// Savety first!
		if ( ! wp_verify_nonce( $_POST[ 'save_settings' ], plugin_basename( __FILE__ ) ) )
		{
			return $post_id;
		}

		// Interrupt on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		{
			return $post_id;
		}

		// Check permissions to edit products
		if ( 'shop_order' == $_POST[ 'post_type' ] )
		{
			if ( ! current_user_can( 'edit_product', $post_id ) )
			{
				return $post_id;
			}
		}

		if( isset( $_POST[ 'sender_address' ] ) )
		{
			update_post_meta( $post_id, 'shipcloud_sender_address', $_POST[ 'sender_address' ] );
		}

		if( isset( $_POST[ 'recipient_address' ] ) )
		{
			update_post_meta( $post_id, 'shipcloud_recipient_address', $_POST[ 'recipient_address' ] );
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
		$options       = get_option( 'woocommerce_shipcloud_settings' );
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );

		$from = array(
			'street'    => $_POST[ 'sender_street' ],
			'street_no' => $_POST[ 'sender_street_nr' ],
			'zip_code'  => $_POST[ 'sender_postcode' ],
			'city'      => $_POST[ 'sender_city' ],
			'country'   => $_POST[ 'sender_country' ]
		);

		$to = array(
			'street'    => $_POST[ 'recipient_street' ],
			'street_no' => $_POST[ 'recipient_street_nr' ],
			'zip_code'  => $_POST[ 'recipient_postcode' ],
			'city'      => $_POST[ 'recipient_city' ],
			'country'   => $_POST[ 'recipient_country' ]
		);

		$package = array(
			'width'  => $_POST[ 'width' ],
			'height' => $_POST[ 'height' ],
			'length' => $_POST[ 'length' ],
			'weight' => str_replace( ',', '.', $_POST[ 'weight' ] ),
		);

		$price = $shipcloud_api->get_price( $_POST[ 'carrier' ], $from, $to, $package );

		if ( is_wp_error( $price ) )
		{
			$errors[] = nl2br( $price->get_error_message() );
			$result   = array(
				'status' => 'ERROR',
				'errors' => $errors
			);
			echo json_encode( $result );
			exit;
		}

		$price_html = wc_price( $price, array( 'currency' => 'EUR' ) );
		$html       = '<div class="notice">' . sprintf( __( 'The calculated price is %s.', 'woocommerce-shipcloud' ), $price_html ) . '</div>';

		$result = array(
			'status' => 'OK',
			'price'  => $price,
			'html'   => $html
		);

		echo json_encode( $result );
		exit;
	}

	/**
	 * Creating shipment
	 *
	 * @since 1.0.0
	 */
	public function ajax_create_shipment()
	{
		$options       = get_option( 'woocommerce_shipcloud_settings' );
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );

		$order_id = $_POST[ 'order_id' ];
		$order    = new WC_Order( $order_id );

		$from = array(
			'first_name' => $_POST[ 'sender_first_name' ],
			'last_name'  => $_POST[ 'sender_last_name' ],
			'company'    => $_POST[ 'sender_company' ],
			'street'     => $_POST[ 'sender_street' ],
			'street_no'  => $_POST[ 'sender_street_nr' ],
			'zip_code'   => $_POST[ 'sender_postcode' ],
			'city'       => $_POST[ 'sender_city' ],
			'country'    => $_POST[ 'sender_country' ],
		);

		$to = array(
			'first_name' => $_POST[ 'recipient_first_name' ],
			'last_name'  => $_POST[ 'recipient_last_name' ],
			'company'    => $_POST[ 'recipient_company' ],
			'street'     => $_POST[ 'recipient_street' ],
			'street_no'  => $_POST[ 'recipient_street_nr' ],
			'zip_code'   => $_POST[ 'recipient_postcode' ],
			'city'       => $_POST[ 'recipient_city' ],
			'country'    => $_POST[ 'recipient_country' ],
			'email'      => $order->billing_email
		);

		$package = array(
			'width'  => $_POST[ 'width' ],
			'height' => $_POST[ 'height' ],
			'length' => $_POST[ 'length' ],
			'weight' => $_POST[ 'weight' ],
			'description' => $_POST[ 'description' ],
		);

		$create_label = false;
		if ( 'shipcloud_create_shipment_label' == $_POST[ 'action' ] )
		{
			$create_label = true;
		}

		$shipment = $shipcloud_api->create_shipment( $_POST[ 'carrier' ], $from, $to, $package, $create_label );

		if ( is_wp_error( $shipment ) )
		{
			$errors[] = nl2br( $shipment->get_error_message() );
			$result   = array(
				'status' => 'ERROR',
				'errors' => $errors
			);
			echo json_encode( $result );
			exit;
		}

		$parcel_title = wcsc_get_carrier_display_name( $_POST[ 'carrier' ] ) . ' - ' . $_POST[ 'width' ] . __( 'x', 'woocommerce-shipcloud' ) . $_POST[ 'height' ] . __( 'x', 'woocommerce-shipcloud' ) . $_POST[ 'length' ] . __( 'cm', 'woocommerce-shipcloud' ) . ' ' . $_POST[ 'weight' ] . __( 'kg', 'woocommerce-shipcloud' );

		$data = array(
			'id'                   => $shipment[ 'id' ],
			'carrier_tracking_no'  => $shipment[ 'carrier_tracking_no' ],
			'tracking_url'         => $shipment[ 'tracking_url' ],
			'label_url'            => $shipment[ 'label_url' ],
			'price'                => $shipment[ 'price' ],
			'parcel_id'            => $shipment[ 'id' ],
			'parcel_title'         => $parcel_title,
			'carrier'              => $_POST[ 'carrier' ],
			'width'                => $_POST[ 'width' ],
			'height'               => $_POST[ 'height' ],
			'length'               => $_POST[ 'length' ],
			'weight'               => $_POST[ 'weight' ],
			'description'          => $_POST[ 'description' ],
			'sender_first_name'    => $_POST[ 'sender_first_name' ],
			'sender_last_name'     => $_POST[ 'sender_last_name' ],
			'sender_company'       => $_POST[ 'sender_company' ],
			'sender_street'        => $_POST[ 'sender_street' ],
			'sender_street_no'     => $_POST[ 'sender_street_nr' ],
			'sender_zip_code'      => $_POST[ 'sender_postcode' ],
			'sender_city'          => $_POST[ 'sender_city' ],
			'country'              => $_POST[ 'sender_country' ],
			'recipient_first_name' => $_POST[ 'recipient_first_name' ],
			'recipient_last_name'  => $_POST[ 'recipient_last_name' ],
			'recipient_company'    => $_POST[ 'recipient_company' ],
			'recipient_street'     => $_POST[ 'recipient_street' ],
			'recipient_street_no'  => $_POST[ 'recipient_street_nr' ],
			'recipient_zip_code'   => $_POST[ 'recipient_postcode' ],
			'recipient_city'       => $_POST[ 'recipient_city' ],
			'recipient_country'    => $_POST[ 'recipient_country' ],
			'date_created'         => time()
		);

		add_post_meta( $order_id, 'shipcloud_shipment_ids', $data[ 'id' ] );
		add_post_meta( $order_id, 'shipcloud_shipment_data', $data );

		$order = wc_get_order( $order_id );
		$order->add_order_note( __( 'shipcloud.io label was prepared.', 'woocommerce-shipcloud' ) );

		$result = array(
			'status'      => 'OK',
			'shipment_id' => $data[ 'id' ],
			'html'        => $this->get_label_html( $data )
		);

		echo json_encode( $result );
		exit;
	}

	/**
	 * Calulating shipping after sublitting calculation
	 *
	 * @since 1.0.0
	 */
	public function ajax_create_label()
	{
		$options       = get_option( 'woocommerce_shipcloud_settings' );
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );

		$order_id    = $_POST[ 'order_id' ];
		$shipment_id = $_POST[ 'shipment_id' ];

		$request = $shipcloud_api->create_label( $shipment_id );

		if ( is_wp_error( $request ) )
		{
			$errors[] = nl2br( $request->get_error_message() );

			$result = $result = array(
				'status' => 'ERROR',
				'errors' => $errors
			);

			echo json_encode( $result );
			exit;
		}

		$shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );

		$order = wc_get_order( $order_id );
		$order->add_order_note( __( 'shipcloud.io label was created.', 'woocommerce-shipcloud' ) );

		$shipments_old = $shipments;

		// Finding shipment key for updating postmeta
		foreach ( $shipments AS $key => $shipment )
		{
			if ( $shipment[ 'id' ] == $request[ 'body' ][ 'id' ] )
			{
				$shipments[ $key ][ 'tracking_url' ] = $request[ 'body' ][ 'tracking_url' ];
				$shipments[ $key ][ 'label_url' ]    = $request[ 'body' ][ 'label_url' ];
				$shipments[ $key ][ 'price' ]        = $request[ 'body' ][ 'price' ];
				break;
			}
		}

		update_post_meta( $order_id, 'shipcloud_shipment_data', $shipments[ $key ], $shipments_old[ $key ] );

		$result = array(
			'status'       => 'OK',
			'id'           => $request[ 'body' ][ 'id' ],
			'tracking_url' => $request[ 'body' ][ 'tracking_url' ],
			'label_url'    => $request[ 'body' ][ 'label_url' ],
			'price'        => $request[ 'body' ][ 'price' ]
		);

		echo json_encode( $result );
		exit;
	}

	/**
	 * Deleting a shipment
	 *
	 * @since 1.0.0
	 */
	public function ajax_delete_shipment()
	{
		$order_id    = $_POST[ 'order_id' ];
		$shipment_id = $_POST[ 'shipment_id' ];

		$options       = get_option( 'woocommerce_shipcloud_settings' );
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );
		$request       = $shipcloud_api->delete_shipment( $shipment_id );

		if ( is_wp_error( $request ) )
		{
			// Do nothing if shipment was not found
			if ( 'shipcloud_api_error_not_found' !== $request->get_error_code() )
			{

				$errors[] = nl2br( $request->get_error_message() );
				$result   = array(
					'status' => 'ERROR',
					'errors' => $errors
				);

				echo json_encode( $result );
				exit;
			}
		}

		$shipments = get_post_meta( $order_id, 'shipcloud_shipment_data' );

		$order = wc_get_order( $order_id );
		$order->add_order_note( __( 'shipcloud.io shipment was deleted.', 'woocommerce-shipcloud' ) );

		$shipments_old = $shipments;

		$delete_shipment_key = '';

		// Finding shipment key to delete postmeta
		foreach ( $shipments AS $key => $shipment )
		{
			if ( $shipment[ 'id' ] == $shipment_id )
			{
				$delete_shipment_key = $shipments[ $key ];
				break;
			}
		}

		if ( ! empty( $delete_shipment_key ) )
		{
			delete_post_meta( $order_id, 'shipcloud_shipment_data', $delete_shipment_key, $shipments_old[ $key ] );

			$result = array(
				'status'      => 'OK',
				'shipment_id' => $shipment_id
			);
		}
		else
		{
			$errors[] = __( 'Shipment was not found in post meta.', 'woocommerce-shipcloud' );
			$result   = array(
				'status' => 'ERROR',
				'errors' => $errors
			);
		}

		echo json_encode( $result );
		exit;
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
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'admin-widgets' );

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
		$settings      = get_option( 'woocommerce_shipcloud_settings' );
		$shipcloud_api = new Woocommerce_Shipcloud_API( $settings[ 'api_key' ] );

		$response = $shipcloud_api->get_tracking_status( $shipment_id );
	}
}

WC_Shipcloud_Order::instance();