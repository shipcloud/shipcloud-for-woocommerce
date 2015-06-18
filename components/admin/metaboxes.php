<?php
/*
 * WooCommerce shipcloud.io postboxes
 *
 * Loading postboxes
 *
 * @author awesome.ug <contact@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 (contact@awesome.ug)

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

class WC_Shipcloud_Metaboxes{
	
	/**
	 * Initialize class
	 */
	public static function init(){
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_metaboxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_product_metabox' ) );
		add_action( 'wp_ajax_shipcloud_add_parcel_template', array( __CLASS__ , 'ajax_add_parcel_template' ) );
		add_action( 'wp_ajax_shipcloud_delete_parcel_template', array( __CLASS__ , 'ajax_delete_parcel_template' ) );
		add_action( 'wp_ajax_shipcloud_calculate_shipping', array( __CLASS__ , 'ajax_calculate_shipping' ) );
		add_action( 'wp_ajax_shipcloud_create_label', array( __CLASS__ , 'ajax_create_label' ) );
		
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 1 );
		
		
	}
	
	/**
	 * Adding meta boxes
	 */
	public static function add_metaboxes(){
		add_meta_box(
			'shipcloudio',
			__( 'Shipment', 'wcsc-locale' ),
			array( __CLASS__, 'product_metabox' ),
			'shop_order'
		);
	}
	
	
	public static function enqueue_scripts(){
		// JS
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		// wp_enqueue_script( 'jquery-blockui' );

		// CSS
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	}
	
	/**
	 * Product metabox
	 */
	public static function product_metabox(){
		global $post, $woocommerce, $wp_scripts;
		
		$parcel_templates = get_option( 'woocommerce_shipcloud_parcel_templates', array() );
		$parcel = get_post_meta( $post->ID, 'shipcloud_parcel', TRUE );
		
		$order = new WC_Order( $post->ID );
		
		wp_nonce_field( plugin_basename( __FILE__ ), 'save_product_metabox' );
		
		?>
		<div id="shipcloud">
			<div class="shipcloud-tabs">
				<ul class="nav-tab-wrapper wcsc-nav-tab-wrapper">
					<li><a class="nav-tab" href="#wcsc-tab-label"><?php _e( 'Overview', 'wcsc-locale' ); ?></a></li>
					<li><a class="nav-tab" href="#wcsc-tab-templates"><?php _e( 'Parcel Templates', 'wcsc-locale' ); ?></a></li>
					<div style="clear:both;"></div>
				</ul>
				<div id="wcsc-tab-label" class="wcsc-tab wcsc-tab-label">
				<?php self::tab_create_label(); ?>
				</div>
				<div id="wcsc-tab-templates" class="wcsc-tab wcsc-tab-templates">
				<?php self::tab_create_templates(); ?>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<?php
	}

	private static function tab_create_label(){
		global $post, $woocommerce;
		
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
			
			$recipient_street = '';
			$recipient_street_nr = '';
			
			$recipient_street = self::explode_street( $order->shipping_address_1 );
			
			if( is_array( $recipient_street ) ):
				$recipient_street_nr = $recipient_street[ 'number' ];
				$recipient_street = $recipient_street[ 'address' ];
			endif;
			
			$recipient_address = array(
				'first_name' => $order->shipping_first_name,
				'last_name' => $order->shipping_last_name,
				'company' => $order->shipping_company,
				'street' => $recipient_street,
				'street_nr' => $recipient_street_nr,
				'postcode' => $order->shipping_postcode,
				'city' => $order->shipping_city,
				'country' => $order->shipping_country,
			);
		endif; 
		
		$parcel_templates = get_option( 'woocommerce_shipcloud_parcel_templates', array() );
		
		?>
		<!-- Addresses //-->
			<div class="order_data_column_container addresses">
				<div class="order_data_column sender shadow">
					<h4><?php _e( 'Sender Address', 'wcsc-locale' ); ?> <a class="btn_edit_address"><img width="14" alt="Edit" src="<?php echo WooCommerce::plugin_url(); ?>/assets/images/icons/edit.png"></a></h4>
					<div class="address">
						<p>
						<?php echo $sender_address[ 'first_name' ]; ?> <?php echo $sender_address[ 'last_name' ]; ?><br />
						<?php echo $sender_address[ 'company' ]; ?><br />
						<?php echo $sender_address[ 'street' ]; ?> <?php echo $sender_address[ 'street_nr' ]; ?><br />
						<?php echo $sender_address[ 'postcode' ]; ?> <?php echo $sender_address[ 'city' ]; ?><br />
						<?php echo $sender_address[ 'country' ]; ?>
						</p>
					</div>
					<div class="edit_address">
						<p class="fullsize">
							<label for="sender_address[first_name]"><?php _e( 'First Name', 'wcsc-locale' ); ?></label>
							<input type="text" name="sender_address[first_name]" value="<?php echo $sender_address[ 'first_name' ]; ?>">
						</p>
						<p class="fullsize">
							<label for="sender_address[last_name]"><?php _e( 'Last Name', 'wcsc-locale' ); ?></label>
							<input type="text" name="sender_address[last_name]" value="<?php echo $sender_address[ 'last_name' ]; ?>">
						</p>
						<p class="fullsize">
							<label for="sender_address[company]"><?php _e( 'Company', 'wcsc-locale' ); ?></label>
							<input type="text" name="sender_address[company]" value="<?php echo $sender_address[ 'company' ]; ?>">
						</p>
						<p>
							<label for="sender_address[street]"><?php _e( 'Street', 'wcsc-locale' ); ?></label>
							<input type="text" name="sender_address[street]" value="<?php echo $sender_address[ 'street' ]; ?>">
						</p>
						<p>
							<label for="sender_address[street_nr]"><?php _e( 'Street Number', 'wcsc-locale' ); ?></label>
							<input type="text" name="sender_address[street_nr]" value="<?php echo $sender_address[ 'street_nr' ]; ?>">
						</p>
						<p>
							<label for="sender_address[postcode]"><?php _e( 'Postcode', 'wcsc-locale' ); ?></label>
							<input type="text" name="sender_address[postcode]" value="<?php echo $sender_address[ 'postcode' ]; ?>">
						</p>
						<p>
							<label for="sender_address[city]"><?php _e( 'City', 'wcsc-locale' ); ?></label>
							<input type="text" name="sender_address[city]" value="<?php echo $sender_address[ 'city' ]; ?>">
						</p>
						<p>
							<label for="sender_address[country]"><?php _e( 'Country', 'wcsc-locale' ); ?></label>
							<select name="sender_address[country]">
							<?php foreach( $woocommerce->countries->countries AS $key => $country ): ?>
								<?php if( $key == $sender_address[ 'country' ] ): $selected = ' selected'; else: $selected = ''; endif; ?>
								<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
							<?php endforeach; ?>
							</select>
						</p>
					</div>
				</div>
				
				<div class="order_data_column recipient shadow">
					<h4><?php _e( 'Recipient Address', 'wcsc-locale' ); ?> <a class="btn_edit_address"><img width="14" alt="Edit" src="<?php echo WooCommerce::plugin_url(); ?>/assets/images/icons/edit.png"></a></h4>
					
					<div class="address">
						<p>
						<?php echo $recipient_address[ 'first_name' ]; ?> <?php echo $recipient_address[ 'last_name' ]; ?><br />
						<?php echo $recipient_address[ 'company' ]; ?><br />
						<?php echo $recipient_address[ 'street' ]; ?> <?php echo $recipient_address[ 'street_nr' ]; ?><br />
						<?php echo $recipient_address[ 'postcode' ]; ?> <?php echo $recipient_address[ 'city' ]; ?><br />
						<?php echo $sender_address[ 'country' ]; ?>
						</p>
					</div>
					<div class="edit_address">
						<p class="fullsize">
							<label for="recipient_address[first_name]"><?php _e( 'First Name', 'wcsc-locale' ); ?></label>
							<input type="text" name="recipient_address[first_name]" value="<?php echo $recipient_address[ 'first_name' ]; ?>">
						</p>
						<p class="fullsize">
							<label for="recipient_address[last_name]"><?php _e( 'Last Name', 'wcsc-locale' ); ?></label>
							<input type="text" name="recipient_address[last_name]" value="<?php echo $recipient_address[ 'last_name' ]; ?>">
						</p>
						<p class="fullsize">
							<label for="recipient_address[company]"><?php _e( 'Company', 'wcsc-locale' ); ?></label>
							<input type="text" name="recipient_address[company]" value="<?php echo $recipient_address[ 'company' ]; ?>">
						</p>
						<p>
							<label for="recipient_address[street]"><?php _e( 'Street', 'wcsc-locale' ); ?></label>
							<input type="text" name="recipient_address[street]" value="<?php echo $recipient_address[ 'street' ]; ?>">
						</p>
						<p>
							<label for="recipient_address[street_nr]"><?php _e( 'Street Number', 'wcsc-locale' ); ?></label>
							<input type="text" name="recipient_address[street_nr]" value="<?php echo $recipient_address[ 'street_nr' ]; ?>">
						</p>
						<p>
							<label for="recipient_address[postcode]"><?php _e( 'Postcode', 'wcsc-locale' ); ?></label>
							<input type="text" name="recipient_address[postcode]" value="<?php echo $recipient_address[ 'postcode' ]; ?>">
						</p>
						<p>
							<label for="recipient_address[city]"><?php _e( 'City', 'wcsc-locale' ); ?></label>
							<input type="text" name="recipient_address[city]" value="<?php echo $recipient_address[ 'city' ]; ?>">
						</p>
						<p>
							<label for="recipient_address[country]"><?php _e( 'Country', 'wcsc-locale' ); ?></label>
							<select name="recipient_address[country]">
							<?php foreach( $woocommerce->countries->countries AS $key => $country ): ?>
								<?php if( $key == $sender_address[ 'country' ] ): $selected = ' selected'; else: $selected = ''; endif; ?>
								<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
							<?php endforeach; ?>
							</select>
						</p>
				</div>
			</div>
			<div style="clear: both"></div>
		</div>
		
		<!-- Actions //-->
		<div class="order_data_column_container actions">	
			<div id="create_label" class="action">
			<?php if( is_array( $parcel_templates ) && count( $parcel_templates ) > 0 ): ?>
				<label for"parcel_template"><?php _e( 'Select parcel template:', 'wcsc-locale' ); ?>
				<select name="parcel_template" id="parcel_template">
				<?php foreach( $parcel_templates AS $key => $parcel_template ): ?>
					<?php 
						$show = self::get_carrier_display_name( $parcel_template[ 'carrier' ] ) . ' ';
						$show.= $parcel_template[ 'width' ] . ' x ';
						$show.= $parcel_template[ 'height' ] . ' x ';
						$show.= $parcel_template[ 'length' ] . __( 'cm', 'wcsc-locale' ) . ' ';
						$show.= $parcel_template[ 'weight' ] . __( 'kg', 'wcsc-locale' ) . ' ';
						
						$value = $parcel_template[ 'carrier' ] . ';';
						$value.= $parcel_template[ 'width' ] . ';';
						$value.= $parcel_template[ 'height' ] . ';';
						$value.= $parcel_template[ 'length' ] . ';';
						$value.= $parcel_template[ 'weight' ];
					 ?>
					<option value="<?php echo $value; ?>"><?php echo $show; ?></option>
				<?php endforeach; ?>
				</select></label>
				<input id="shipcloud_create_label" type="button" value="<?php _e( 'Create label', 'wcsc-locale'); ?>" class="button button-wcsc-action" />	
			<?php else: ?>
				<p><?php echo __( 'You have not create a parcel template yet. <a href="#wcsc-tab-templates" class="shipcloud-switchto-parcel-tamplates">Create a parcel template</a>.', 'wcsc-locale' ); ?></p>
			<?php endif; ?>
			</div>
			
		</div>
		<div style="clear:both;"></div>
		
		
		<!-- Label list //-->
		<div class="order_data_column_container shipping_data">
			<h3><?php _e( 'Created labels', 'wcsc-locale' ); ?></h3>
			<div class="order_data_column shipment_labels">
				<?php if( '' != $shipment_data && is_array( $shipment_data ) ): ?>
					<?php krsort( $shipment_data ); ?>
					<?php foreach( $shipment_data AS $time => $data ): ?>
						<?php echo self::get_label_html( $data, $time ); ?>
					<?php endforeach; ?>
				<?php else: ?>
					<p id="no_label_created"><?php _e( 'No Label created yet.', 'wcsc-locale' ); ?></p>
				<?php endif; ?>
			</div>
			<div style="clear: both"></div>
		</div>
		
		<div id="ask_create_label"><?php echo esc_attr__( 'Depending on the carrier, there will be a fee fo for creating the label. Do you really want to create a label?', 'questions-locale' ); ?></div>
		<div id="ask_order_pickup"><?php echo esc_attr__( 'This will order a pickup for this parcel. Do you really want to order?', 'questions-locale' ); ?></div>
		
		<?
	}
	
	/**
	 * Tab for creating parcel templates
	 */
	private static function tab_create_templates(){
		
		$options = get_option( 'woocommerce_shipcloud_settings' );
		$parcel_templates = get_option( 'woocommerce_shipcloud_parcel_templates', array() );
		
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );
		$carriers = $shipcloud_api->get_carriers( TRUE );
		
		$parcel = get_post_meta( $post->ID, 'shipcloud_parcel', TRUE );
		
		?><!-- Parcel settings //-->
		<div class="order_data_column_container parcel">
			<div class="order_data_column info">
			</div>
			<div class="order_data_column">
				<table class="widefat" id="parcel_table">
					<thead>
						<tr>
							<th>
								<label for="parcel[carrier]"><?php _e( 'Carrier', 'wcsc-locale' ); ?></label>
							</th>
							<th>
								<label for="parcel[width]"><?php _e( 'Width', 'wcsc-locale' ); ?></label>
							</th>
							<th>
								<label for="parcel[height]"><?php _e( 'Height', 'wcsc-locale' ); ?></label>
							</th>
							<th>
								<label for="parcel[length]"><?php _e( 'Length', 'wcsc-locale' ); ?></label>
							</th>
							<th>
								<label for="parcel[weight]"><?php _e( 'Weight', 'wcsc-locale' ); ?></label>
							</th>
							<th>
								
							</th>
						</tr>
					</thead>
					<tbody>
						<tr id="parcel_options">
							<td class="parcel_option carrier">
								<select name="parcel[carrier]">
									<?php foreach( $carriers AS $carrier ): ?>
										<?php if( $parcel['carrier'] == $carrier[ 'name' ] ): $selected = ' selected="selected"'; else: $selected = ''; endif; ?>
										<option value="<?php echo $carrier[ 'name' ]; ?>"<?php echo $selected; ?>><?php echo $carrier[ 'display_name' ]; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td class="parcel_option parcel_width">
								<input type="text" name="parcel[width]" value="<?php echo $parcel[ 'width' ]; ?>" placeholder="<?php _e( 'cm', 'wcsc-locale'  ); ?>" />
							</td>
							<td class="parcel_option parcel_height">
								<input type="text" name="parcel[height]" value="<?php echo $parcel[ 'height' ]; ?>" placeholder="<?php _e( 'cm', 'wcsc-locale'  ); ?>" />
							</td>
							<td class="parcel_option parcel_length">
								<input type="text" name="parcel[length]" value="<?php echo $parcel[ 'length' ]; ?>" placeholder="<?php _e( 'cm', 'wcsc-locale'  ); ?>" />
							</td>
							<td class="parcel_option parcel_weight">
								<input type="text" name="parcel[weight]" value="<?php echo $parcel[ 'weight' ]; ?>" placeholder="<?php _e( 'kg', 'wcsc-locale'  ); ?>" />
							</td>
							<td class="parcel_option parcel_button">
								<input type="button" id="shipcloud_calculate_shipping" value="<?php _e( 'Calculate', 'wcsc-locale'  ); ?>" class="button" />
								<input type="button" id="shipcloud_add_parcel_template" value="<?php _e( 'Save as draft', 'wcsc-locale'  ); ?>" class="button" />
							</td>
						</tr>
				
						<?php if( '' != $parcel_templates && is_array( $parcel_templates ) ): ?>
							<?php $i = 0; ?>
							<?php foreach( $parcel_templates AS $parcel_template ): ?>
								<tr<?php echo $i % 2 == 0 ? ' class="alt"': ''; ?>>
									<td><?php echo self::get_carrier_display_name( $parcel_template[ 'carrier' ] ); ?></td>
									<td><?php echo $parcel_template[ 'width' ]; ?> <?php _e( 'cm', 'wcsc-locale' ); ?></td>
									<td><?php echo $parcel_template[ 'height' ]; ?> <?php _e( 'cm', 'wcsc-locale' ); ?></td>
									<td><?php echo $parcel_template[ 'length' ]; ?> <?php _e( 'cm', 'wcsc-locale' ); ?></td>
									<td>
										<?php echo $parcel_template[ 'weight' ]; ?> <?php _e( 'kg', 'wcsc-locale' ); ?> 
										
									</td>
									<td>
										<input type="button" class="carrier_delete button"  value="<?php _e( 'Delete', 'wcsc-locale'  ); ?>" />
										<input type="button" class="carrier_select button" value="<?php _e( 'Select', 'wcsc-locale'  ); ?>" />
										<input type="hidden" name="carrier" value="<?php echo $parcel_template[ 'carrier' ]; ?>">
										<input type="hidden" name="width" value="<?php echo $parcel_template[ 'width' ]; ?>" />
										<input type="hidden" name="height" value="<?php echo $parcel_template[ 'height' ]; ?>" />
										<input type="hidden" name="length" value="<?php echo $parcel_template[ 'length' ]; ?>" />
										<input type="hidden" name="weight" value="<?php echo $parcel_template[ 'weight' ]; ?>" />
									</td>
								</tr>
								<?php $i++; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Creates label HTML
	 * @param array $data
	 * @param int $time
	 */
	private static function get_label_html( $data, $time = FALSE ){
		ob_start();
		?>
		<div class="data">
			<a href="<?php echo $data[ 'label_url' ]; ?>" target="_blank">
				<img src="<?php echo WCSC_URLPATH; ?>/assets/pdf.png" class="current_shipment" />
			</a>
			<?php if( '' !=  $data[ 'carrier_tracking_no' ]): ?>
			<p>
				<span class="shipment_label"><?php _e( 'Carrier tracking number', 'wcsc-locale' ); ?></span>
				<span class="shipment_value"><?php echo $data[ 'carrier_tracking_no' ]; ?></span>
			</p>
			<?php endif; ?>
			<?php if( '' !=  $data[ 'tracking_url' ]): ?>
			<p>
				<span class="shipment_label"><?php _e( 'Carrier tracking url', 'wcsc-locale' ); ?></span>
				<span class="shipment_value">
					<a href="<?php echo $data[ 'tracking_url' ]; ?>" target="_blank">
					<?php echo $data[ 'tracking_url' ]; ?>
					</a>
				</span>
			</p>
			<?php endif; ?>
			<p>
				<span class="shipment_label"><?php _e( 'Label', 'wcsc-locale' ); ?></span>
				<span class="shipment_value">
					<a href="<?php echo $data[ 'label_url' ]; ?>" target="_blank">
					<?php _e( 'Download label', 'wcsc-locale' ); ?>
					</a>
				</span>
			</p>
			<p>
				<span class="shipment_label"><?php _e( 'Price', 'wcsc-locale' ); ?></span>
				<span class="shipment_value"><?php echo wc_price( $data[ 'price' ], array( 'currency' =>  'EUR' ) ); ?></span>
			</p>
			<p>
				<span class="shipment_label"><?php _e( 'Carrier', 'wcsc-locale' ); ?></span>
				<span class="shipment_value"><?php echo $data[ 'carrier' ]; ?></span>
			</p>
			<p>
				<span class="shipment_label"><?php _e( 'Dimensions', 'wcsc-locale' ); ?></span>
				<span class="shipment_value"><?php echo $data[ 'width' ]; ?> x <?php echo $data[ 'height' ]; ?> x <?php echo $data[ 'length' ]; ?> <?php _e( 'cm', 'wcsc-locale' ); ?></span>
			</p>
			<p>
				<span class="shipment_label"><?php _e( 'Weight', 'wcsc-locale' ); ?></span>
				<span class="shipment_value"><?php echo $data[ 'weight' ]; ?>  <?php _e( 'kg', 'wcsc-locale' ); ?></span>
			</p>
			<?php if( FALSE != $time ): ?>
			<p>
				<span class="shipment_label"><?php _e( 'Date created', 'wcsc-locale' ); ?></span>
				<span class="shipment_value"><?php echo date_i18n( get_option( 'date_format' ), $time ); ?> - <?php echo date_i18n( get_option( 'time_format' ), $time ); ?></span>
			</p>
			<?php endif; ?>
			
			<input type="button" value="<?php _e( 'Order Pickup', 'wcsc-locale'); ?>" class="button button-wcsc-action" />
			<div class="clear"></div>
		</div><?php
		
		$html = ob_get_clean();
		
		return $html;
	}

	/**
	 * Get carrier display_name from name
	 * @param string $name
	 * @return string $display_name
	 */
	private static function get_carrier_display_name( $name ){
		$options = get_option( 'woocommerce_shipcloud_settings' );
		
		$shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );
		$carriers = $shipcloud_api->get_carriers();
		
		foreach( $carriers AS $carrier ):
			if( $carrier[ 'name' ] == $name )
				return $carrier[ 'display_name' ];
		endforeach;
	}

	/**
	 * Splitting Address for getting number of street and street separate
	 * @param string $street
	 * @return mixed $matches
	 */
	private static function explode_street( $street ){
		$matches = array();
		
		if( !preg_match('/(?P<address>[^\d]+) (?P<number>\d+.?)/', $street, $matches ) )
		   return $street;
		
		return $matches;
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
	 * Save parcel template
	 */
	public static function ajax_add_parcel_template(){
		$parcel_templates = get_option( 'woocommerce_shipcloud_parcel_templates', array() );
		
		// Checking if parcel template exists
		$found = FALSE;
		foreach( $parcel_templates AS $key => $parcel_template ):
			if( 
				$parcel_template[ 'carrier' ] == $_POST[ 'carrier' ] && 
				$parcel_template[ 'width' ] == $_POST[ 'width' ] && 
				$parcel_template[ 'height' ] == $_POST[ 'height' ] && 
				$parcel_template[ 'length' ] == $_POST[ 'length' ] && 
				$parcel_template[ 'weight' ] == $_POST[ 'weight' ]
			  ):
				$found = TRUE;
			endif;
		endforeach;
		
		// Adding parcel template
		if( !$found ):
			$new_parcel_template = array(
				'carrier' => $_POST[ 'carrier' ],
				'width' => $_POST[ 'width' ],
				'height' => $_POST[ 'height' ],
				'length' => $_POST[ 'length' ],
				'weight' => $_POST[ 'weight' ]
			);
			$parcel_templates[] = $new_parcel_template;
			update_option( 'woocommerce_shipcloud_parcel_templates', $parcel_templates );
			echo json_encode( array( 'added' => TRUE ) );
			exit;
		endif;
		
		echo json_encode( array( 'added' => FALSE ) );		
		exit;
	}
	
	/**
	 * Deleting parcel template
	 */
	public static function ajax_delete_parcel_template(){
		$parcel_templates = get_option( 'woocommerce_shipcloud_parcel_templates', array() );

		foreach( $parcel_templates AS $key => $parcel_template ):
			if( 
				$parcel_template[ 'carrier' ] == $_POST[ 'carrier' ] && 
				$parcel_template[ 'width' ] == $_POST[ 'width' ] && 
				$parcel_template[ 'height' ] == $_POST[ 'height' ] && 
				$parcel_template[ 'length' ] == $_POST[ 'length' ] && 
				$parcel_template[ 'weight' ] == $_POST[ 'weight' ]
			  ):
				unset( $parcel_templates[ $key ] );
			endif;
		endforeach;
		
		update_option( 'woocommerce_shipcloud_parcel_templates', $parcel_templates );
		
		echo json_encode( array( 'deleted' => TRUE ) );
		exit;
	}
	
	/**
	 * Calulating shipping after sublitting calculation
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
				'weight' 	=> $_POST[ 'weight' ],
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
						$result[ $key ] = self::translate_error( $error );
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
						$result[ $key ] = self::translate_error( $error );
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
			
			$data = array(
				'id' 					=> $shipment[ 'body' ][ 'id' ],
				'carrier_tracking_no' 	=> $shipment[ 'body' ][ 'carrier_tracking_no' ],
				'tracking_url' 			=> $shipment[ 'body' ][ 'tracking_url' ],
				'label_url'				=> $shipment[ 'body' ][ 'label_url' ],
				'price' 				=> $shipment[ 'body' ][ 'price' ],
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
				'recipient_country' 	=> $_POST[ 'recipient_country' ]				
			);
			$shipment_data[ time() ] = $data;
			
			update_post_meta( $order_id, 'shipcloud_shipment_data', $shipment_data );
			
			$result = $shipment[ 'body' ];
			
			echo self::get_label_html( $data );
		endif;
		
		exit;
	}
	
	/**
	 * Translating shipcloud.io error texts
	 * @param string $error_text
	 * @return string $error_text
	 */
	public static function translate_error( $error_text ){
		$translations = array(
			"Sender Street can't be blank"			
				=> __( 'Sender Street can\'t be blank.', 'wpsc-locale' ),
			"Sender Street number can't be blank"	
				=> __( 'Sender Street number can\'t be blank.', 'wpsc-locale' ),
			"Sender ZIP code can't be blank" 		
				=> __( 'Sender ZIP code can\'t be blank.', 'wpsc-locale' ),
			"Sender City can't be blank"			
				=> __( 'Sender City can\'t be blank.', 'wpsc-locale' ),
			"Sender Country can't be blank"			
				=> __( 'Sender Country can\'t be blank.', 'wpsc-locale' ),
			"Sender Country  is not an ALPHA-2 ISO country code." 
				=> __( 'Sender Country  is not an ALPHA-2 ISO country code.', 'wpsc-locale' ),
			"Receiver Street can't be blank"		
				=> __( 'Sender Street can\'t be blank.', 'wpsc-locale' ),
			"Receiver Street number can't be blank"	
				=> __( 'Sender Street number can\'t be blank.', 'wpsc-locale' ),
			"Receiver ZIP code can't be blank" 		
				=> __( 'Sender ZIP code can\'t be blank.', 'wpsc-locale' ),
			"Receiver City can't be blank"			
				=> __( 'Sender City can\'t be blank.', 'wpsc-locale' ),
			"Receiver Country can't be blank"		
				=> __( 'Sender Country can\'t be blank.', 'wpsc-locale' ),
			"Receiver Country  is not an ALPHA-2 ISO country code." 
				=> __( 'Receiver Country  is not an ALPHA-2 ISO country code.', 'wpsc-locale' ),
			"Package Height (in cm) can't be blank" 
				=> __( 'Package Height (in cm) can\'t be blank.', 'wpsc-locale' ),
			"Package Height (in cm) is not a number"
				=> __( 'Package Height (in cm) is not a number.', 'wpsc_locale' ),
			"Package Length (in cm) can't be blank"
				=> __( 'Package Length (in cm) can\'t be blank.', 'wpsc_locale' ),
			"Package Length (in cm) is not a number"
				=> __( 'Package Length (in cm) is not a number.', 'wpsc-locale'),
			"Package Width (in cm) can't be blank"
				=> __( 'Package Width (in cm) can\'t be blank.', 'wpsc-locale' ),
			"Package Width (in cm) is not a number"
				=> __( 'Package Width (in cm) is not a number.', 'wpsc-locale'),
			"Package Weight (in kg) can't be blank"
				=> __( 'Package Weight (in kg) can\'t be blank.', 'wpsc-locale' ),
			"Package Weight (in kg) is not a number"
				=> __( 'Package Weight (in kg) is not a number.', 'wpsc-locale')
		);
		
		if( array_key_exists( $error_text, $translations ) )
			$error_text = $translations[ $error_text ];
		
		return $error_text;
	}
}
