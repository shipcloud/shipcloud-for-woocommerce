<?php
/**
 * WooCommerce shipcloud.io postboxes
 *
 * Loading postboxes
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
	exit;

class WC_Shipcloud_Order
{
	/**
	 * @var int Order ID
	 */
	static $order_id;

	/**
	 * @var The Single instance of the class
	 */
	protected static $_instance = NULL;

	/**
	 * Construct
	 */
	private function __construct()
	{
		self::init_hooks();
	}

	/**
	 * Initialize Hooks
	 */
	private static function init_hooks()
	{
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_metaboxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_settings' ) );

		add_action( 'wp_ajax_shipcloud_calculate_shipping', array( __CLASS__, 'ajax_calculate_shipping' ) );
		add_action( 'wp_ajax_shipcloud_create_label', array( __CLASS__, 'ajax_create_label' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 1 );
	}

	/**
	 * Main Instance
	 */
	public static function instance()
	{
		if( is_null( self::$_instance ) )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Adding meta boxes
	 */
	public static function add_metaboxes()
	{
		add_meta_box( 'shipcloudio', __( 'shipcloud.io Shipment-Center', 'woocommerce-shipcloud' ), array( __CLASS__, 'shipment_center' ), 'shop_order' );
	}

	/**
	 * Product metabox
	 */
	public static function shipment_center()
	{
		global $post;

		self::$order_id = $post->ID;

		wp_nonce_field( plugin_basename( __FILE__ ), 'save_settings' );

		$html = '<div id="shipcloud">';
		$html .= self::addresses();
		$html .= self::parcel();
		$html .= self::labels();
		$html .= '</div>';
		$html .= '<div class="clear"></div>';

		echo $html;
	}

	private static function addresses()
	{
		global $woocommerce;

		$options = get_option( 'woocommerce_shipcloud_settings' );

		$sender = get_post_meta( self::$order_id, 'shipcloud_sender_address', TRUE );
		$recipient = get_post_meta( self::$order_id, 'shipcloud_recipient_address', TRUE );

		// Use default data if nothing was saved before
		if( '' == $sender || 0 == count( $sender ) )
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
		if( '' == $recipient || 0 == count( $recipient ) )
		{
			$order = new WC_Order( self::$order_id );

			$recipient_street_nr = '';
			$recipient_street = wcsc_explode_street( $order->shipping_address_1 );

			if( is_array( $recipient_street ) )
			{
				$recipient_street_name = $recipient_street[ 'address' ];
				$recipient_street_nr = $recipient_street[ 'number' ];
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

		ob_start();
		?>
		<div class="order_data_column_container addresses">
			<div class="order_data_column sender">
				<h4><?php _e( 'Sender Address', 'woocommerce-shipcloud' ); ?>
					<a class="btn_edit_address"><img width="14" alt="Edit" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/icons/edit.png"></a>
				</h4>

				<div class="edit_address disabled">
					<p class="fullsize">
						<label for="sender_address[company]"><?php _e( 'Company', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="sender_address[company]" value="<?php echo $sender[ 'company' ]; ?>" disabled>
					</p>

					<p>
						<label for="sender_address[first_name]"><?php _e( 'First Name', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="sender_address[first_name]" value="<?php echo $sender[ 'first_name' ]; ?>" disabled>
					</p>

					<p>
						<label for="sender_address[last_name]"><?php _e( 'Last Name', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="sender_address[last_name]" value="<?php echo $sender[ 'last_name' ]; ?>" disabled>
					</p>

					<p class="seventyfive">
						<label for="sender_address[street]"><?php _e( 'Street', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="sender_address[street]" value="<?php echo $sender[ 'street' ]; ?>" disabled>
					</p>

					<p class="twentyfive">
						<label for="sender_address[street_nr]"><?php _e( 'Number', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="sender_address[street_nr]" value="<?php echo $sender[ 'street_nr' ]; ?>" disabled>
					</p>

					<p>
						<label for="sender_address[postcode]"><?php _e( 'Postcode', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="sender_address[postcode]" value="<?php echo $sender[ 'postcode' ]; ?>" disabled>
					</p>

					<p>
						<label for="sender_address[city]"><?php _e( 'City', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="sender_address[city]" value="<?php echo $sender[ 'city' ]; ?>" disabled>
					</p>

					<p class="fullsize">
						<label for="sender_address[country]"><?php _e( 'Country', 'woocommerce-shipcloud' ); ?></label>
						<select name="sender_address[country]" disabled>
							<?php foreach( $woocommerce->countries->countries AS $key => $country ): ?>
								<?php if( $key == $sender[ 'country' ] ): $selected = ' selected';
								else: $selected = ''; endif; ?>
								<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				</div>
			</div>

			<div class="order_data_column recipient">
				<h4><?php _e( 'Recipient Address', 'woocommerce-shipcloud' ); ?>
					<a class="btn_edit_address"><img width="14" alt="Edit" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/icons/edit.png"></a>
				</h4>

				<div class="edit_address disabled">
					<p class="fullsize">
						<label for="recipient_address[company]"><?php _e( 'Company', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="recipient_address[company]" value="<?php echo $recipient[ 'company' ]; ?>" disabled>
					</p>

					<p>
						<label for="recipient_address[first_name]"><?php _e( 'First Name', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="recipient_address[first_name]" value="<?php echo $recipient[ 'first_name' ]; ?>" disabled>
					</p>

					<p>
						<label for="recipient_address[last_name]"><?php _e( 'Last Name', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="recipient_address[last_name]" value="<?php echo $recipient[ 'last_name' ]; ?>" disabled>
					</p>

					<p class="seventyfive">
						<label for="recipient_address[street]"><?php _e( 'Street', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="recipient_address[street]" value="<?php echo $recipient[ 'street' ]; ?>" disabled>
					</p>

					<p class="twentyfive">
						<label for="recipient_address[street_nr]"><?php _e( 'Number', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="recipient_address[street_nr]" value="<?php echo $recipient[ 'street_nr' ]; ?>" disabled>
					</p>

					<p>
						<label for="recipient_address[postcode]"><?php _e( 'Postcode', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="recipient_address[postcode]" value="<?php echo $recipient[ 'postcode' ]; ?>" disabled>
					</p>

					<p>
						<label for="recipient_address[city]"><?php _e( 'City', 'woocommerce-shipcloud' ); ?></label>
						<input type="text" name="recipient_address[city]" value="<?php echo $recipient[ 'city' ]; ?>" disabled>
					</p>

					<p class="fullsize">
						<label for="recipient_address[country]"><?php _e( 'Country', 'woocommerce-shipcloud' ); ?></label>
						<select name="recipient_address[country]" disabled>
							<?php foreach( $woocommerce->countries->countries AS $key => $country ): ?>
								<?php if( $key == $sender[ 'country' ] ): $selected = ' selected';
								else: $selected = ''; endif; ?>
								<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				</div>
			</div>
			<div style="clear: both"></div>
		</div>
		<?php

		return ob_get_clean();
	}

	private static function parcel()
	{
		ob_start();
		?>
		<div class="order_data_column_container parcels">

			<h3><?php _e( 'Create Parcel Labels', 'woocommerce-shipcloud' ); ?></h3>
			<div class="info"></div>

			<?php echo self::parcel_form(); ?>
			<?php echo self::parcel_templates(); ?>

			<div class="clear"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function parcel_form(){
		$carriers = wcsc_get_carriers();

		ob_start();
		?>
		<div class="order_data_column create-label">
			<table class="parcel-form-table">
				<tbody>
				<tr>
					<th><?php echo esc_attr( 'Width', 'woocommerce-shipcloud' ); ?></th>
					<td>
						<input type="text" name="parcel_width"/> <?php echo esc_attr( 'cm', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php echo esc_attr( 'Height', 'woocommerce-shipcloud' ); ?></th>
					<td>
						<input type="text" name="parcel_height"/> <?php echo esc_attr( 'cm', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php echo esc_attr( 'Length', 'woocommerce-shipcloud' ); ?> </th>
					<td>
						<input type="text" name="parcel_length"/> <?php echo esc_attr( 'cm', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php echo esc_attr( 'Weight', 'woocommerce-shipcloud' ); ?></th>
					<td>
						<input type="text" name="parcel_weight"/> <?php echo esc_attr( 'kg', 'woocommerce-shipcloud' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php echo esc_attr( 'Carrier', 'woocommerce-shipcloud' ); ?></th>
					<td>
						<select name="parcel_carrier">
							<option value="none"><?php echo esc_attr( '[ Select a Carrier ]', 'woocommerce-shipcloud' ); ?></option>
							<?php foreach( $carriers AS $name => $display_name ): ?>
							<option value="<?php echo $name; ?>"><?php echo $display_name; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				</tbody>
			</table>

			<div id="button-actions">
				<input id="shipcloud_calculate_price" type="button" value="<?php _e( 'Calculate Price', 'woocommerce-shipcloud' ); ?>" class="button"/>
				<input id="shipcloud_create_label" type="button" value="<?php _e( 'Create Label', 'woocommerce-shipcloud' ); ?>" class="button-primary"/>
			</div>

			<div class="clear"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function parcel_templates()
	{
		$args = array(
			'post_type'   => 'sc_parcel_template',
			'post_status' => 'publish'
		);

		$posts = get_posts( $args );

		$parcel_templates = array();
		foreach( $posts AS $post )
		{
			$parcel_templates[] = array(
				'value'     => 	get_post_meta( $post->ID, 'width', TRUE ) . ';'
								. get_post_meta( $post->ID, 'height', TRUE ) . ';'
								. get_post_meta( $post->ID, 'length', TRUE ) . ';'
								. get_post_meta( $post->ID, 'weight', TRUE ) . ';'
								. get_post_meta( $post->ID, 'carrier', TRUE ) . ';',
				'option'    => get_post_meta( $post->ID, 'width', TRUE ) . esc_attr( 'x', 'woocommerce-shipcloud' )
								. get_post_meta( $post->ID, 'height', TRUE ) . esc_attr( 'x', 'woocommerce-shipcloud' )
								. get_post_meta( $post->ID, 'length', TRUE ) . esc_attr( 'cm', 'woocommerce-shipcloud' ) . ' - '
								. get_post_meta( $post->ID, 'weight', TRUE ) . esc_attr( 'kg', 'woocommerce-shipcloud' ) . ' - '
								. wcsc_get_carrier_display_name( get_post_meta( $post->ID, 'carrier', TRUE ) ),
			);
		}

		ob_start();
		?>
		<div class="order_data_column parcel-templates">

			<div class="parcel-template-field parcels-recommended">
				<label for="parcels_recommended"><?php echo esc_attr( 'Parcels which have been calulated for Order', 'woocommerce-shipcloud' ); ?></label>
				<select name="parcels_recommended">
					<option value="none"><?php echo esc_attr( '[ Select a Parcel ]', 'woocommerce-shipcloud' ); ?></option>
					<?php foreach( $parcel_templates AS $parcel_template ): ?>
						<option value="<?php echo $parcel_template[ 'value' ];?>"><?php echo $parcel_template[ 'option' ];?></option>
					<?php endforeach; ?>
				</select>
				<input type="button" name="add_parcel" value="<?php echo esc_attr( 'Insert in Form', 'woocommerce-shipcloud' ); ?>" class="button" />
			</div>

			<div class="parcel-template-field parcels-templates">
				<label for="parcel_templates"><?php echo esc_attr( 'Parcel Templates', 'woocommerce-shipcloud' ); ?></label>
				<select name="parcel_templates">
					<option value="none"><?php echo esc_attr( '[ Select a Parcel Template ]', 'woocommerce-shipcloud' ); ?></option>
					<?php foreach( $parcel_templates AS $parcel_template ): ?>
					<option value="<?php echo $parcel_template[ 'value' ];?>"><?php echo $parcel_template[ 'option' ];?></option>
					<?php endforeach; ?>
				</select>
				<input type="button" name="add_parcel" value="<?php echo esc_attr( 'Insert in Form', 'woocommerce-shipcloud' ); ?>" class="button" />
			</div>

		</div>
		<?php

		return ob_get_clean();
	}

	private static function labels()
	{
		$shipment_data = get_post_meta( self::$order_id, 'shipcloud_shipment_data', TRUE );

		ob_start();
		?>
		<div id="create_label">

			<div class="order_data_column_container shipping_data">
				<div class="shipment_labels">
					<?php

					if( '' != $shipment_data && is_array( $shipment_data ) )
					{
						krsort( $shipment_data );

						foreach( $shipment_data AS $time => $data )
						{
							self::get_label_html( $data, $time );
						}
					}

					?>
				</div>
				<div style="clear: both"></div>
			</div>
		</div>
		<div id="ask_create_label"><?php _e( 'Depending on the carrier, there will be a fee fo for creating the label. Do you really want to create a label?', 'woocommerce-shipcloud' ); ?></div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Creates label HTML
	 *
	 * @param array $data
	 * @param int   $time
	 */
	private static function get_label_html( $data, $time = FALSE )
	{
		ob_start();

		?>
		<div class="label widget">
		<div class="widget-top">
			<div class="widget-title-action">
				<a class="widget-action hide-if-no-js"></a>
			</div>
			<div class="widget-title">
				<img class="wcsc-widget-icon" src="<?php echo WCSC_URLPATH; ?>assets/icons/truck-32x32.png"/>
				<?php

				$title = trim( $data[ 'sender_company' ] ) != '' ? $data[ 'sender_company' ] . ', ' . $data[ 'sender_first_name' ] . ' ' . $data[ 'sender_last_name' ] : $data[ 'sender_first_name' ] . ' ' . $data[ 'sender_last_name' ];
				$title .= ' &gt; ';
				$title .= trim( $data[ 'recipient_company' ] ) != '' ? $data[ 'recipient_company' ] . ', ' . $data[ 'recipient_first_name' ] . ' ' . $data[ 'recipient_last_name' ] : $data[ 'recipient_first_name' ] . ' ' . $data[ 'recipient_last_name' ];

				?>
				<h4><?php echo $title; ?></h4>
			</div>
		</div>
		<div class="widget-inside">
			<div class="widget-content">
				<div class="data">

					<div class="label_shipment_sender order_data_column ">
						<div class="sender_company"><?php echo $data[ 'sender_company' ]; ?></div>
						<div class="sender_name"><?php echo $data[ 'sender_first_name' ]; ?><?php echo $data[ 'sender_last_name' ]; ?></div>
						<div class="sender_street"><?php echo $data[ 'sender_street' ]; ?><?php echo $data[ 'sender_street_no' ]; ?></div>
						<div class="sender_city"><?php echo $data[ 'sender_zip_code' ]; ?><?php echo $data[ 'sender_city' ]; ?></div>
						<div class="sender_country"><?php echo $data[ 'country' ]; ?></div>
					</div>

					<div class="label_shipment_recipient order_data_column ">
						<div class="recipient_company"><?php echo $data[ 'recipient_company' ]; ?></div>
						<div class="recipient_name"><?php echo $data[ 'recipient_first_name' ]; ?><?php echo $data[ 'recipient_last_name' ]; ?></div>
						<div class="recipient_street"><?php echo $data[ 'recipient_street' ]; ?><?php echo $data[ 'recipient_street_no' ]; ?></div>
						<div class="recipient_city"><?php echo $data[ 'recipient_zip_code' ]; ?><?php echo $data[ 'recipient_city' ]; ?></div>
						<div class="recipient_country"><?php echo $data[ 'recipient_country' ]; ?></div>
					</div>

					<div class="label_shipment_actions order_data_column ">
						<p class="fullsize">
							<a href="<?php echo $data[ 'label_url' ]; ?>" target="_blank" class="button"><?php _e( 'Download Label', 'woocommerce-shipcloud' ); ?></a>
						</p>

						<p class="fullsize">
							<a href="<?php echo $data[ 'tracking_url' ]; ?>" target="_blank" class="button"><?php _e( 'Tracking Link', 'woocommerce-shipcloud' ); ?></a>
						</p>
						<!-- <p class="fullsize"><input type="button" value="<?php _e( 'Order Pickup', 'woocommerce-shipcloud' ); ?>" class="shipcloud-order-pickup button-primary" /></p> //-->
						<input type="hidden" name="carrier" value="<?php echo $data[ 'carrier' ]; ?>"/>
						<input type="hidden" name="shipment_id" value="<?php echo $data[ 'id' ]; ?>"/>
					</div>

					<div style="clear: both;"></div>

					<div class="label_shipment_parcel">
						<strong><?php _e( 'Selected Parcel:', 'woocommerce-shipcloud' ); ?></strong> <?php echo $data[ 'parcel_title' ]; ?>
						- <?php echo wc_price( $data[ 'price' ], array( 'currency' => 'EUR' ) ); ?>
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
	 */
	public static function save_settings( $post_id )
	{
		// Savety first!
		if( !wp_verify_nonce( $_POST[ 'save_settings' ], plugin_basename( __FILE__ ) ) )
		{
			return $post_id;
		}

		// Interrupt on autosave
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		{
			return $post_id;
		}

		// Check permissions to edit products
		if( 'shop_order' == $_POST[ 'post_type' ] )
		{
			if( !current_user_can( 'edit_product', $post_id ) )
			{
				return $post_id;
			}
		}

		if( array_key_exists( 'draft', $_POST[ 'parcel' ] ) ):
			$parcel_templates = get_option( 'woocommerce_shipcloud_parcel_templates', array() );
			$parcel_templates[] = $_POST[ 'parcel' ];
			update_option( 'woocommerce_shipcloud_parcel_templates', $parcel_templates );
		endif;

		update_post_meta( $post_id, 'shipcloud_sender_address', $_POST[ 'sender_address' ] );
		update_post_meta( $post_id, 'shipcloud_recipient_address', $_POST[ 'recipient_address' ] );
		update_post_meta( $post_id, 'shipcloud_parcel', $_POST[ 'parcel' ] );
	}

	/**
	 * Calulating shipping after submitting calculation
	 */
	public static function ajax_calculate_shipping()
	{
		$options = get_option( 'woocommerce_shipcloud_settings' );

		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );

		$shipment = array(
			'carrier' => $_POST[ 'carrier' ],
			'service' => 'standard',
			'to'      => array(
				'street'    => $_POST[ 'recipient_street' ],
				'street_no' => $_POST[ 'recipient_street_nr' ],
				'zip_code'  => $_POST[ 'recipient_postcode' ],
				'city'      => $_POST[ 'recipient_city' ],
				'country'   => $_POST[ 'recipient_country' ]
			),
			'from'    => array(
				'street'    => $_POST[ 'sender_street' ],
				'street_no' => $_POST[ 'sender_street_nr' ],
				'zip_code'  => $_POST[ 'sender_postcode' ],
				'city'      => $_POST[ 'sender_city' ],
				'country'   => $_POST[ 'sender_country' ]
			),
			'package' => array(
				'width'  => $_POST[ 'width' ],
				'height' => $_POST[ 'height' ],
				'length' => $_POST[ 'length' ],
				'weight' => str_replace( ',', '.', $_POST[ 'weight' ] ),
			)
		);

		$shipment_quote = $shipcloud_api->send_request( 'shipment_quotes', $shipment, 'POST' );
		$request_status = (int) $shipment_quote[ 'header' ][ 'status' ];

		// Getting errors if existing
		if( 200 != $request_status ):
			$errors = $shipment_quote[ 'body' ][ 'errors' ];
			$result = array();

			switch ( $request_status )
			{
				case 422:
					$result[] = __( 'Parcel dimensions are not supported by carrier.', 'woocommerce-shipcloud' );
					break;
				default:
					foreach( $errors AS $key => $error ):
						$result[ $key ] = wcsc_translate_shipcloud_text( $error );
					endforeach;
					break;
			}

			$result = array( 'errors' => $result );
		endif;

		// Getting price if successful
		if( array_key_exists( 'shipment_quote', $shipment_quote[ 'body' ] ) ):
			$result = $shipment_quote[ 'body' ][ 'shipment_quote' ];
			$result[ 'price' ] = wc_price( $result[ 'price' ], array( 'currency' => 'EUR' ) );
		endif;

		echo json_encode( $result );
		exit;
	}

	/**
	 * Calulating shipping after sublitting calculation
	 */
	public static function ajax_create_label()
	{
		$options = get_option( 'woocommerce_shipcloud_settings' );

		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );

		$order_id = $_POST[ 'order_id' ];

		$shipment = array(
			'carrier'               => $_POST[ 'carrier' ],
			'service'               => 'standard',
			'create_shipping_label' => TRUE,
			'to'                    => array(
				'first_name' => $_POST[ 'recipient_first_name' ],
				'last_name'  => $_POST[ 'recipient_last_name' ],
				'company'    => $_POST[ 'recipient_company' ],
				'street'     => $_POST[ 'recipient_street' ],
				'street_no'  => $_POST[ 'recipient_street_nr' ],
				'zip_code'   => $_POST[ 'recipient_postcode' ],
				'city'       => $_POST[ 'recipient_city' ],
				'country'    => $_POST[ 'recipient_country' ]
			),
			'from'                  => array(
				'first_name' => $_POST[ 'sender_first_name' ],
				'last_name'  => $_POST[ 'sender_last_name' ],
				'company'    => $_POST[ 'sender_company' ],
				'street'     => $_POST[ 'sender_street' ],
				'street_no'  => $_POST[ 'sender_street_nr' ],
				'zip_code'   => $_POST[ 'sender_postcode' ],
				'city'       => $_POST[ 'sender_city' ],
				'country'    => $_POST[ 'sender_country' ]
			),
			'package'               => array(
				'width'  => $_POST[ 'width' ],
				'height' => $_POST[ 'height' ],
				'length' => $_POST[ 'length' ],
				'weight' => $_POST[ 'weight' ],
			)
		);

		$shipment = $shipcloud_api->send_request( 'shipments', $shipment, 'POST' );
		$request_status = (int) $shipment[ 'header' ][ 'status' ];

		// Getting errors if existing
		if( 200 != $request_status ):
			$errors = $shipment_quote[ 'body' ][ 'errors' ];
			$result = array();

			switch ( $request_status )
			{
				case 422:
					$result[] = __( 'Parcel dimensions are not supported by carrier.', 'woocommerce-shipcloud' );
					break;
				default:
					foreach( $errors AS $key => $error ):
						$result[ $key ] = wcsc_translate_shipcloud_text( $error );
					endforeach;
					break;
			}

			$result = array( 'errors' => $result );

			echo json_encode( $result );
			exit;
		endif;

		delete_post_meta( $order_id, 'shipcloud_shipment_current_data' );
		// delete_post_meta( $order_id, 'shipcloud_shipment_data' );

		// Saving shipment data to order
		if( 200 == $request_status ):
			$shipment_data = get_post_meta( $order_id, 'shipcloud_shipment_data', TRUE );

			if( !is_array( $shipment_data ) )
			{
				$shipment_data = array();
			}

			$parcel = WCSC_Parcels::get_parcel( $parcel_id );

			$data = array(
				'id'                   => $shipment[ 'body' ][ 'id' ],
				'carrier_tracking_no'  => $shipment[ 'body' ][ 'carrier_tracking_no' ],
				'tracking_url'         => $shipment[ 'body' ][ 'tracking_url' ],
				'label_url'            => $shipment[ 'body' ][ 'label_url' ],
				'price'                => $shipment[ 'body' ][ 'price' ],
				'parcel_id'            => $_POST[ 'parcel_id' ],
				'parcel_title'         => $parcel[ 'post_title' ],
				'carrier'              => $_POST[ 'carrier' ],
				'width'                => $_POST[ 'width' ],
				'height'               => $_POST[ 'height' ],
				'length'               => $_POST[ 'length' ],
				'weight'               => $_POST[ 'weight' ],
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
			$shipment_data[ time() ] = $data;

			update_post_meta( $order_id, 'shipcloud_shipment_data', $shipment_data );

			$result = $shipment[ 'body' ];

			$order = wc_get_order( $order_id );
			$order->add_order_note( __( 'shipcloud.io label was created.', 'woocommerce-shipcloud' ) );

			echo self::get_label_html( $data );
		endif;

		exit;
	}

	public static function enqueue_scripts()
	{
		// JS
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'admin-widgets' );

		// CSS
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	}
}

WC_Shipcloud_Order::instance();
