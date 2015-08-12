<?php
/**
 * WooCommerce shipcloud.io postboxes
 *
 * Loading postboxes
 *
 * @author awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 (very@awesome.ug)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !defined( 'ABSPATH' ) ) exit;

class WC_Shipcloud_Order{
	
	/**
	 * Initialize class
	 */
	public static function init(){
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_metaboxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_product_metabox' ) );

		add_action( 'wp_ajax_shipcloud_calculate_shipping', array( __CLASS__ , 'ajax_calculate_shipping' ) );
		add_action( 'wp_ajax_shipcloud_create_label', array( __CLASS__ , 'ajax_create_label' ) );

		// add_action( 'wp_ajax_shipcloud_request_pickup', array( __CLASS__ , 'ajax_request_pickup' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 1 );
	}
	
	/**
	 * Product metabox
	 */
	public static function product_metabox(){
		global $post, $woocommerce;
		
		$parcel_templates = get_option( 'woocommerce_shipcloud_parcel_templates', array() );
		$parcel = get_post_meta( $post->ID, 'shipcloud_parcel', TRUE );
		
		$order = new WC_Order( $post->ID );
		
		wp_nonce_field( plugin_basename( __FILE__ ), 'save_product_metabox' );

		?>
		<div id="shipcloud">

			<?php
			/**
			 * Starting Shipcloud API
			 */
			$options = get_option( 'woocommerce_shipcloud_settings' );
			$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );

			/**
			 * Sender Address
			 */
			$sender_address = get_post_meta( $post->ID, 'shipcloud_sender_address', TRUE );
			$recipient_address = get_post_meta( $post->ID, 'shipcloud_recipient_address', TRUE );

			/**
			 * Shipment_labels
			 */
			$shipment_data = get_post_meta( $post->ID, 'shipcloud_shipment_data', TRUE );

			// Use default data if nothing was saved before
			if( '' == $sender_address || 0 == count( $sender_address ) ):
				$sender_address = array(
					'first_name' => $options[ 'sender_first_name' ],
					'last_name' => $options[ 'sender_last_name' ],
					'company' => $options[ 'sender_company' ],
					'street' => $options[ 'sender_street' ],
					'street_nr' => $options[ 'sender_street_nr' ],
					'postcode' => $options[ 'sender_postcode' ],
					'city' => $options[ 'sender_city' ],
					'country' => $options[ 'sender_country' ],
				);
			endif;

			// Use default data if nothing was saved before
			if( '' == $recipient_address || 0 == count( $recipient_address ) ):
				$order = new WC_Order( $post->ID );

				$recipient_street_nr = '';

				$recipient_street = wcsc_explode_street( $order->shipping_address_1 );

				if( is_array( $recipient_street ) ) {
					$recipient_street_name = $recipient_street['address'];
					$recipient_street_nr = $recipient_street['number'];
				}

				$recipient_address = array(
					'first_name' => $order->shipping_first_name,
					'last_name' => $order->shipping_last_name,
					'company' => $order->shipping_company,
					'street' => $recipient_street_name,
					'street_nr' => $recipient_street_nr,
					'postcode' => $order->shipping_postcode,
					'city' => $order->shipping_city,
					'country' => $order->shipping_country,
				);
			endif;

			?>

			<!-- Create Label //-->
			<div id="create_label">
				<div class="order_data_column_container addresses">
					<div class="order_data_column sender">
						<h4><?php _e( 'Sender Address', 'wcsc-locale' ); ?>  <a class="btn_edit_address"><img width="14" alt="Edit" src="<?php echo WooCommerce::plugin_url(); ?>/assets/images/icons/edit.png"></a></h4>
						<div class="edit_address disabled">
							<p class="fullsize">
								<label for="sender_address[company]"><?php _e( 'Company', 'wcsc-locale' ); ?></label>
								<input type="text" name="sender_address[company]" value="<?php echo $sender_address[ 'company' ]; ?>" disabled>
							</p>
							<p>
								<label for="sender_address[first_name]"><?php _e( 'First Name', 'wcsc-locale' ); ?></label>
								<input type="text" name="sender_address[first_name]" value="<?php echo $sender_address[ 'first_name' ]; ?>" disabled>
							</p>
							<p>
								<label for="sender_address[last_name]"><?php _e( 'Last Name', 'wcsc-locale' ); ?></label>
								<input type="text" name="sender_address[last_name]" value="<?php echo $sender_address[ 'last_name' ]; ?>" disabled>
							</p>
							<p class="seventyfive">
								<label for="sender_address[street]"><?php _e( 'Street', 'wcsc-locale' ); ?></label>
								<input type="text" name="sender_address[street]" value="<?php echo $sender_address[ 'street' ]; ?>" disabled>
							</p>
							<p class="twentyfive">
								<label for="sender_address[street_nr]"><?php _e( 'Number', 'wcsc-locale' ); ?></label>
								<input type="text" name="sender_address[street_nr]" value="<?php echo $sender_address[ 'street_nr' ]; ?>" disabled>
							</p>
							<p>
								<label for="sender_address[postcode]"><?php _e( 'Postcode', 'wcsc-locale' ); ?></label>
								<input type="text" name="sender_address[postcode]" value="<?php echo $sender_address[ 'postcode' ]; ?>" disabled>
							</p>
							<p>
								<label for="sender_address[city]"><?php _e( 'City', 'wcsc-locale' ); ?></label>
								<input type="text" name="sender_address[city]" value="<?php echo $sender_address[ 'city' ]; ?>" disabled>
							</p>
							<p class="fullsize">
								<label for="sender_address[country]"><?php _e( 'Country', 'wcsc-locale' ); ?></label>
								<select name="sender_address[country]" disabled>
								<?php foreach( $woocommerce->countries->countries AS $key => $country ): ?>
									<?php if( $key == $sender_address[ 'country' ] ): $selected = ' selected'; else: $selected = ''; endif; ?>
									<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
								<?php endforeach; ?>
								</select>
							</p>
						</div>
					</div>

					<div class="order_data_column recipient">
						<h4><?php _e( 'Recipient Address', 'wcsc-locale' ); ?>  <a class="btn_edit_address"><img width="14" alt="Edit" src="<?php echo WooCommerce::plugin_url(); ?>/assets/images/icons/edit.png"></a></h4>

						<div class="edit_address disabled">
							<p class="fullsize">
								<label for="recipient_address[company]"><?php _e( 'Company', 'wcsc-locale' ); ?></label>
								<input type="text" name="recipient_address[company]" value="<?php echo $recipient_address[ 'company' ]; ?>" disabled>
							</p>
							<p>
								<label for="recipient_address[first_name]"><?php _e( 'First Name', 'wcsc-locale' ); ?></label>
								<input type="text" name="recipient_address[first_name]" value="<?php echo $recipient_address[ 'first_name' ]; ?>" disabled>
							</p>
							<p>
								<label for="recipient_address[last_name]"><?php _e( 'Last Name', 'wcsc-locale' ); ?></label>
								<input type="text" name="recipient_address[last_name]" value="<?php echo $recipient_address[ 'last_name' ]; ?>" disabled>
							</p>
							<p class="seventyfive">
								<label for="recipient_address[street]"><?php _e( 'Street', 'wcsc-locale' ); ?></label>
								<input type="text" name="recipient_address[street]" value="<?php echo $recipient_address[ 'street' ]; ?>" disabled>
							</p>
							<p class="twentyfive">
								<label for="recipient_address[street_nr]"><?php _e( 'Number', 'wcsc-locale' ); ?></label>
								<input type="text" name="recipient_address[street_nr]" value="<?php echo $recipient_address[ 'street_nr' ]; ?>" disabled>
							</p>
							<p>
								<label for="recipient_address[postcode]"><?php _e( 'Postcode', 'wcsc-locale' ); ?></label>
								<input type="text" name="recipient_address[postcode]" value="<?php echo $recipient_address[ 'postcode' ]; ?>" disabled>
							</p>
							<p>
								<label for="recipient_address[city]"><?php _e( 'City', 'wcsc-locale' ); ?></label>
								<input type="text" name="recipient_address[city]" value="<?php echo $recipient_address[ 'city' ]; ?>" disabled>
							</p>
							<p class="fullsize">
								<label for="recipient_address[country]"><?php _e( 'Country', 'wcsc-locale' ); ?></label>
								<select name="recipient_address[country]" disabled>
								<?php foreach( $woocommerce->countries->countries AS $key => $country ): ?>
									<?php if( $key == $sender_address[ 'country' ] ): $selected = ' selected'; else: $selected = ''; endif; ?>
									<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
								<?php endforeach; ?>
								</select>
							</p>
						</div>

				</div>

				<!-- Actions //-->
				<?php

				$parcel_templates = WCSC_Parcels::get();

				if( is_array( $parcel_templates ) && count( $parcel_templates ) > 0 ){
					$style_parcel_templates = 'display:block;';
					$style_parcel_templates_missing = 'display:none;';
				}else{
					$style_parcel_templates = 'display:none;';
					$style_parcel_templates_missing = 'display:block;';
				}

				?>
				<div id="create_label_form" class="order_data_column actions">

					<h4><?php _e( 'Shipping', 'wcsc-locale' ); ?></h4>
					<div id="parcel_templates" style="<?php echo $style_parcel_templates; ?>">

						<div id="select_label">

							<p class="fullsize">
								<select name="parcel_id" id="parcel_id">
									<?php foreach( $parcel_templates AS  $parcel_template ): ?>
										<option value="<?php echo $parcel_template[ 'ID' ]; ?>"><?php echo $parcel_template[ 'post_title' ]; ?></option>
									<?php endforeach; ?>
								</select>
								<label for"parcel_id"><?php _e( 'shipcloud Parcel', 'wcsc-locale' ); ?></label>
							</p>

							<?php foreach( $parcel_templates AS $key => $parcel_template ): ?>
								<input type="hidden" name="parcel[<?php echo $parcel_template[ 'ID' ]; ?>][carrier]" value="<?php echo $parcel_template[ 'values'][ 'carrier' ]; ?>" />
								<input type="hidden" name="parcel[<?php echo $parcel_template[ 'ID' ]; ?>][width]" value="<?php echo $parcel_template[ 'values'][ 'width' ]; ?>" />
								<input type="hidden" name="parcel[<?php echo $parcel_template[ 'ID' ]; ?>][height]" value="<?php echo $parcel_template[ 'values'][ 'height' ]; ?>" />
								<input type="hidden" name="parcel[<?php echo $parcel_template[ 'ID' ]; ?>][length]" value="<?php echo $parcel_template[ 'values'][ 'length' ]; ?>" />
								<input type="hidden" name="parcel[<?php echo $parcel_template[ 'ID' ]; ?>][weight]" value="<?php echo $parcel_template[ 'values'][ 'weight' ]; ?>" />
								<input type="hidden" name="parcel[<?php echo $parcel_template[ 'ID' ]; ?>][retail_price]" value="<?php echo $parcel_template[ 'values'][ 'retail_price' ]; ?>" />
							<?php endforeach; ?>

							<div style="clear: both"></div>
						</div>

						<div id="button_actions">
							<p class="fullsize"><input id="shipcloud_calculate_price" type="button" value="<?php _e( 'Calculate Price', 'wcsc-locale'); ?>" class="fullsize button" /></p>
							<p class="fullsize"><input id="shipcloud_create_label" type="button" value="<?php _e( 'Create Label', 'wcsc-locale'); ?>" class="fullsize button-primary" /></p>
							<div style="clear: both"></div>
						</div>

						<div style="clear: both"></div>
						<div class="info">
						</div>
					</div>

					<div id="parcel_templates_missing" style="<?php echo $style_parcel_templates_missing; ?>">
						<p class="fullsize"><?php echo __( 'You need to create at minimum one shipcloud Parcel.', 'wcsc-locale' ); ?></p>
						<p class="fullsize"><a href="<?php echo admin_url( 'edit.php?post_type=sc_parcel_template' ); ?>" class="shipcloud-switchto-parcel-templates button"><?php echo __( 'Edit Parcels', 'wcsc-locale' ); ?></a></p>
					</div>
				</div>
				<div style="clear: both"></div>
				<!-- Label list //-->
				<div class="order_data_column_container shipping_data">
					<div class="shipment_labels">
						<?php if( '' != $shipment_data && is_array( $shipment_data ) ): ?>
							<?php krsort( $shipment_data ); ?>
							<?php foreach( $shipment_data AS $time => $data ): ?>
								<?php echo self::get_label_html( $data, $time ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<div style="clear: both"></div>
				</div>
			</div>
			</div>

			<!-- Hidden Dialog messages //-->
			<div id="ask_create_label"><?php echo esc_attr__( 'Depending on the carrier, there will be a fee fo for creating the label. Do you really want to create a label?', 'wcsc-locale' ); ?></div>
			<div id="ask_order_pickup"><?php echo esc_attr__( 'This will order a pickup for this parcel. Do you really want to order?', 'wcsc-locale' ); ?></div>

		</div>
		<div class="clear"></div>
		<?php
	}
	
	/**
	 * Saving product metabox
	 * @param int $post_id
	 */
	public static function save_product_metabox( $post_id ){
		// Savety first!
		if ( !wp_verify_nonce( $_POST[ 'save_product_metabox' ], plugin_basename( __FILE__ ) ) )
        	return $post_id;
		
		
		// Interrupt on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        	return $post_id;
		
		// Check permissions to edit products
      	if ( 'shop_order' == $_POST['post_type'] )
        	if ( !current_user_can( 'edit_product', $post_id )  )
          		return $post_id;
			
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
	 * Creates label HTML
	 * @param array $data
	 * @param int $time
	 */
	private static function get_label_html( $data, $time = FALSE ){
		ob_start();

		?>
		<div class="label widget">
		<div class="widget-top">
			<div class="widget-title-action">
				<a class="widget-action hide-if-no-js"></a>
			</div>
			<div class="widget-title">
				<img class="wcsc-widget-icon" src ="<?php echo WCSC_URLPATH; ?>assets/icons/truck-32x32.png" />
				<?php

				$title = trim( $data[ 'sender_company' ] ) != '' ? $data[ 'sender_company' ] . ', ' . $data[ 'sender_first_name' ] . ' ' . $data[ 'sender_last_name' ] : $data[ 'sender_first_name' ] . ' ' . $data[ 'sender_last_name' ];
				$title.= ' &gt; ';
				$title.= trim( $data[ 'recipient_company' ] ) != '' ? $data[ 'recipient_company' ] . ', ' . $data[ 'recipient_first_name' ] . ' ' . $data[ 'recipient_last_name' ] : $data[ 'recipient_first_name' ] . ' ' . $data[ 'recipient_last_name' ];

				?>
				<h4><?php echo $title; ?></h4>
			</div>
		</div>
		<div class="widget-inside">
			<div class="widget-content">
				<div class="data">

					<div class="label_shipment_sender order_data_column ">
						<div class="sender_company"><?php echo $data[ 'sender_company' ]; ?></div>
						<div class="sender_name"><?php echo $data[ 'sender_first_name' ]; ?> <?php echo $data[ 'sender_last_name' ]; ?></div>
						<div class="sender_street"><?php echo $data[ 'sender_street' ]; ?> <?php echo $data[ 'sender_street_no' ]; ?></div>
						<div class="sender_city"><?php echo $data[ 'sender_zip_code' ]; ?> <?php echo $data[ 'sender_city' ]; ?></div>
						<div class="sender_country"><?php echo $data[ 'country' ]; ?></div>
					</div>

					<div class="label_shipment_recipient order_data_column ">
						<div class="recipient_company"><?php echo $data[ 'recipient_company' ]; ?></div>
						<div class="recipient_name"><?php echo $data[ 'recipient_first_name' ]; ?> <?php echo $data[ 'recipient_last_name' ]; ?></div>
						<div class="recipient_street"><?php echo $data[ 'recipient_street' ]; ?> <?php echo $data[ 'recipient_street_no' ]; ?></div>
						<div class="recipient_city"><?php echo $data[ 'recipient_zip_code' ]; ?> <?php echo $data[ 'recipient_city' ]; ?></div>
						<div class="recipient_country"><?php echo $data[ 'recipient_country' ]; ?></div>
					</div>

					<div class="label_shipment_actions order_data_column ">
						<p class="fullsize"><a href="<?php echo $data[ 'label_url' ]; ?>" target="_blank" class="button"><?php _e( 'Download Label', 'wcsc-locale'); ?></a></p>
						<p class="fullsize"><a href="<?php echo $data[ 'tracking_url' ]; ?>" target="_blank" class="button"><?php _e( 'Tracking Link', 'wcsc-locale'); ?></a></p>
						<!-- <p class="fullsize"><input type="button" value="<?php _e( 'Order Pickup', 'wcsc-locale'); ?>" class="shipcloud-order-pickup button-primary" /></p> //-->
						<input type="hidden" name="carrier" value="<?php echo $data[ 'carrier' ]; ?>" />
						<input type="hidden" name="shipment_id" value="<?php echo $data[ 'id' ]; ?>" />
					</div>

					<div style="clear: both;"></div>

					<div class="label_shipment_parcel">
						<strong><?php echo esc_attr__( 'Selected Parcel:', 'wcsc-locale' ); ?></strong> <?php echo $data[ 'parcel_title' ]; ?> -  <?php echo wc_price( $data[ 'price' ], array( 'currency' =>  'EUR' ) ); ?>
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
	 * Calulating shipping after submitting calculation
	 */
	public static function ajax_calculate_shipping(){
		$options = get_option( 'woocommerce_shipcloud_settings' );
		
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );
		
		$shipment = array(
			'carrier' => $_POST[ 'carrier' ],
			'service' => 'standard',
			'to' => array(
				'street' 	=> $_POST[ 'recipient_street' ],
				'street_no' => $_POST[ 'recipient_street_nr' ],
				'zip_code' 	=> $_POST[ 'recipient_postcode' ],
				'city' 		=> $_POST[ 'recipient_city' ],
				'country' 	=> $_POST[ 'recipient_country' ]
			),
			'from' => array(
				'street' 	=> $_POST[ 'sender_street' ],
				'street_no' => $_POST[ 'sender_street_nr' ],
				'zip_code' 	=> $_POST[ 'sender_postcode' ],
				'city' 		=> $_POST[ 'sender_city' ],
				'country' 	=> $_POST[ 'sender_country' ]
			),
			'package' => array(
				'width' 	=> $_POST[ 'width' ],
				'height' 	=> $_POST[ 'height' ],
				'length' 	=> $_POST[ 'length' ],
				'weight' 	=> str_replace( ',', '.', $_POST[ 'weight' ] ),
			)
		);
		
		$shipment_quote = $shipcloud_api->send_request( 'shipment_quotes', $shipment, 'POST' );
		$request_status = (int) $shipment_quote[ 'header' ][ 'status' ];
		
		// Getting errors if existing
		if( 200 != $request_status ):
			$errors = $shipment_quote[ 'body' ][ 'errors' ];
			$result = array();
			
			switch( $request_status ){
				case 422:
					$result[] = __( 'Parcel dimensions are not supported by carrier.', 'wcsc-locale');
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
		if( array_key_exists( 'shipment_quote', $shipment_quote['body'] ) ):
			$result = $shipment_quote['body'][ 'shipment_quote' ] ;
			$result[ 'price' ] = wc_price( $result[ 'price' ], array( 'currency' =>  'EUR' ) );
		endif;
		
		echo json_encode( $result );
		exit;
	}

	/**
	 * Calulating shipping after sublitting calculation
	 */
	public static function ajax_create_label(){
		$options = get_option( 'woocommerce_shipcloud_settings' );
		
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );
		
		$order_id = $_POST[ 'order_id' ];
		
		$shipment = array(
			'carrier' => $_POST[ 'carrier' ],
			'service' => 'standard',
			'create_shipping_label' => true,
			'to' => array(
				'first_name' 	=> $_POST[ 'recipient_first_name' ],
				'last_name' 	=> $_POST[ 'recipient_last_name' ],
				'company' 	=> $_POST[ 'recipient_company' ],
				'street' 	=> $_POST[ 'recipient_street' ],
				'street_no' => $_POST[ 'recipient_street_nr' ],
				'zip_code' 	=> $_POST[ 'recipient_postcode' ],
				'city' 		=> $_POST[ 'recipient_city' ],
				'country' 	=> $_POST[ 'recipient_country' ]
			),
			'from' => array(
				'first_name' 	=> $_POST[ 'sender_first_name' ],
				'last_name' 	=> $_POST[ 'sender_last_name' ],
				'company' 	=> $_POST[ 'sender_company' ],
				'street' 	=> $_POST[ 'sender_street' ],
				'street_no' => $_POST[ 'sender_street_nr' ],
				'zip_code' 	=> $_POST[ 'sender_postcode' ],
				'city' 		=> $_POST[ 'sender_city' ],
				'country' 	=> $_POST[ 'sender_country' ]
			),
			'package' => array(
				'width' 	=> $_POST[ 'width' ],
				'height' 	=> $_POST[ 'height' ],
				'length' 	=> $_POST[ 'length' ],
				'weight' 	=> $_POST[ 'weight' ],
			)
		);
		
		$shipment = $shipcloud_api->send_request( 'shipments', $shipment, 'POST' );
		$request_status = (int) $shipment[ 'header' ][ 'status' ];
		
		// Getting errors if existing
		if( 200 != $request_status ):
			$errors = $shipment_quote[ 'body' ][ 'errors' ];
			$result = array();
			
			switch( $request_status ){
				case 422:
					$result[] = __( 'Parcel dimensions are not supported by carrier.', 'wcsc-locale');
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
				$shipment_data = array();

			$parcel = WCSC_Parcels::get_parcel( $parcel_id );
			
			$data = array(
				'id' 					=> $shipment[ 'body' ][ 'id' ],
				'carrier_tracking_no' 	=> $shipment[ 'body' ][ 'carrier_tracking_no' ],
				'tracking_url' 			=> $shipment[ 'body' ][ 'tracking_url' ],
				'label_url'				=> $shipment[ 'body' ][ 'label_url' ],
				'price' 				=> $shipment[ 'body' ][ 'price' ],
				'parcel_id' 			=> $_POST[ 'parcel_id' ],
				'parcel_title' 			=> $parcel[ 'post_title' ],
				'carrier' 				=> $_POST[ 'carrier' ],
				'width' 				=> $_POST[ 'width' ],
				'height' 				=> $_POST[ 'height' ],
				'length' 				=> $_POST[ 'length' ],
				'weight' 				=> $_POST[ 'weight' ],
				'sender_first_name' 	=> $_POST[ 'sender_first_name' ],
				'sender_last_name' 		=> $_POST[ 'sender_last_name' ],
				'sender_company' 		=> $_POST[ 'sender_company' ],
				'sender_street' 		=> $_POST[ 'sender_street' ],
				'sender_street_no' 		=> $_POST[ 'sender_street_nr' ],
				'sender_zip_code' 		=> $_POST[ 'sender_postcode' ],
				'sender_city' 			=> $_POST[ 'sender_city' ],
				'country' 				=> $_POST[ 'sender_country' ],
				'recipient_first_name' 	=> $_POST[ 'recipient_first_name' ],
				'recipient_last_name' 	=> $_POST[ 'recipient_last_name' ],
				'recipient_company' 	=> $_POST[ 'recipient_company' ],
				'recipient_street' 		=> $_POST[ 'recipient_street' ],
				'recipient_street_no' 	=> $_POST[ 'recipient_street_nr' ],
				'recipient_zip_code' 	=> $_POST[ 'recipient_postcode' ],
				'recipient_city' 		=> $_POST[ 'recipient_city' ],
				'recipient_country' 	=> $_POST[ 'recipient_country' ],
				'date_created'			=> time()
			);
			$shipment_data[ time() ] = $data;
			
			update_post_meta( $order_id, 'shipcloud_shipment_data', $shipment_data );
			
			$result = $shipment[ 'body' ];
			
			echo self::get_label_html( $data );
		endif;
		
		exit;
	}

	/**
	 * Requesting pickup
	 */
	/** @todo: Creating order lists later for pickup
	public static function ajax_request_pickup(){
		$options = get_option( 'woocommerce_shipcloud_settings' );
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );

		$pickup_date = date( 'Y/m/d', time() );

		$pickup_date = '2015/07/22';

		$shipment = array(
			'carrier' => $_POST[ 'carrier' ],
			'pickup_date' => $pickup_date,
			'shipments' => array(
				'id' => $_POST[ 'shipment_id' ],
			)
		);

		$pickup_request = $shipcloud_api->send_request( 'pickup_requests', $shipment, 'POST' );
		$request_status = (int) $shipment[ 'header' ][ 'status' ];

		if( 200 != $request_status ):
			$errors = $pickup_request[ 'body' ][ 'errors' ];
			$result = array( 'errors' => $errors );
		endif;

		// Getting price if successful
		if( array_key_exists( 'pickup_date', $pickup_request['body'] ) ):
			$result[ 'pickup_date' ] = $pickup_request['body'][ 'pickup_date' ] ;
		endif;

		echo json_encode( $result );
		exit;
	}
	*/

	/**
	 * Adding meta boxes
	 */
	public static function add_metaboxes(){
		add_meta_box(
			'shipcloudio',
			__( 'shipcloud.io Shipment-Center', 'wcsc-locale' ),
			array( __CLASS__, 'product_metabox' ),
			'shop_order'
		);
	}


	public static function enqueue_scripts(){
		// JS
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'admin-widgets' );

		// CSS
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	}
}
WC_Shipcloud_Order::init();
