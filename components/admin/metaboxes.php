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
	}
	
	/**
	 * Adding meta boxes
	 */
	public static function add_metaboxes(){
		add_meta_box(
			'shipcloudio',
			__( 'shipcloud.io shipment', 'wcsc-locale' ),
			array( __CLASS__, 'product_metabox' ),
			'shop_order'
		);
	}
	
	/**
	 * Product metabox
	 */
	public static function product_metabox(){
		global $post, $woocommerce;
		
		$options = get_option( 'woocommerce_shipcloud_settings' );
		
		$sender_address = get_post_meta( $post->ID, 'shipcloud_sender_address', TRUE );
		$recipient_address = get_post_meta( $post->ID, 'shipcloud_recipient_address', TRUE );
		
		$order = new WC_Order( $post->ID );
		
		// Use default data if nothing was saved before
		if( '' == $sender_address || 0 == count( $sender_address ) ):
			$sender_address = array(
				'name' => $options[ 'sender_name' ],
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
			$recipient_street = '';
			$recipient_street_nr = '';
			
			$recipient_street = self::explode_street( $order->shipping_address_1 );
			
			if( is_array( $recipient_street ) ):
				$recipient_street_nr = $recipient_street[ 'number' ];
				$recipient_street = $recipient_street[ 'address' ];
			endif;
			
			$recipient_address = array(
				'name' => $order->shipping_first_name . ' ' . $order->shipping_last_name,
				'company' => $order->shipping_company,
				'street' => $recipient_street,
				'street_nr' => $recipient_street_nr,
				'postcode' => $order->shipping_postcode,
				'city' => $order->shipping_city,
				'country' => $order->shipping_country,
			);
		endif;
		
		wp_nonce_field( plugin_basename( __FILE__ ), 'save_product_metabox' );
		
		?>
		<div id="shipcloud">
			<div class="shipcloud_adresses">
				<div class="order_data_column_container">
					<div class="order_data_column">
						<h4><?php _e( 'Sender Details', 'wcsc-locale' ); ?></h4>
						
						<div class="address">
							<p><?php echo $sender_address[ 'name' ]; ?><br />
							<?php echo $sender_address[ 'company' ]; ?><br />
							<?php echo $sender_address[ 'street' ]; ?> <?php echo $sender_address[ 'street_nr' ]; ?><br />
							<?php echo $sender_address[ 'postcode' ]; ?> <?php echo $sender_address[ 'city' ]; ?><br />
							<?php echo $sender_address[ 'city' ]; ?></p>
						</div>
						<div class="edit_address">
							<p class="fullsize">
								<label for="sender_address[name]"><?php _e( 'Name', 'wcsc-locale' ); ?></label>
								<input type="text" name="sender_address[name]" value="<?php echo $sender_address[ 'name' ]; ?>">
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
					<div class="order_data_column">
						<h4><?php _e( 'Recipient Details', 'wcsc-locale' ); ?></h4>
						
						<div class="address">
							<p><?php echo $recipient_address[ 'name' ]; ?><br />
							<?php echo $recipient_address[ 'company' ]; ?><br />
							<?php echo $recipient_address[ 'street' ]; ?> <?php echo $recipient_address[ 'street_nr' ]; ?><br />
							<?php echo $recipient_address[ 'postcode' ]; ?> <?php echo $recipient_address[ 'city' ]; ?><br />
							<?php echo $recipient_address[ 'city' ]; ?></p>
						</div>
						<div class="edit_address">
							<p class="fullsize">
								<label for="recipient_address[name]"><?php _e( 'Name', 'wcsc-locale' ); ?></label>
								<input type="text" name="recipient_address[name]" value="<?php echo $recipient_address[ 'name' ]; ?>">
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
			</div>
		</div>
		<div class="clear"></div>
		<?php
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
			
		update_post_meta( $post_id, 'shipcloud_sender_address', $_POST[ 'sender_address' ] );
		update_post_meta( $post_id, 'shipcloud_recipient_address', $_POST[ 'recipient_address' ] );
	}
}
