<div id="shipment-<?php echo $data[ 'id' ]; ?>" class="label widget">
	<div class="widget-top">
		<div class="widget-title-action">
			<a class="widget-action hide-if-no-js"></a>
		</div>
		<div class="widget-title">
			<img class="shipcloud-widget-icon" src="<?php echo WCSC_URLPATH; ?>assets/icons/truck-32x32.png"/>
			<?php

			$title = trim( $data[ 'sender_company' ] ) != '' ? $data[ 'sender_company' ] . ', ' . $data[ 'sender_first_name' ] . ' ' . $data[ 'sender_last_name' ] : $data[ 'sender_first_name' ] . ' ' . $data[ 'sender_last_name' ];
			$title .= ' <span class="dashicons dashicons-arrow-right-alt"></span> ';
			if ( !empty($data['recipient_company']) ) {
				$title .= $data[ 'recipient_company' ] . ', ';
			}
			$title .= $data[ 'recipient_first_name' ] . ' ' . $data[ 'recipient_last_name' ];
			$title .= ' <span class="dashicons dashicons-screenoptions"></span> <small>' . trim($data[ 'parcel_title' ], ' -') . '</small>';

			?>
			<h4><?php echo $title; ?></h4>
		</div>
	</div>
	<div class="widget-inside">
		<div class="widget-content">
			<div class="data">

				<div class="label-shipment-sender address" role="switch">
                        <span>
                            <div class="sender_company"><?php echo $data[ 'sender_company' ]; ?></div>
                            <div class="sender_name"><?php echo $data[ 'sender_first_name' ]; ?> <?php echo $data[ 'sender_last_name' ]; ?></div>
                            <div class="sender_street"><?php echo $data[ 'sender_street' ]; ?> <?php echo (isset($data[ 'sender_street_nr' ])) ? $data[ 'sender_street_nr' ] : $data[ 'sender_street_no' ]; ?></div>
                            <div class="sender_city"><?php echo $data[ 'sender_zip_code' ]; ?> <?php echo $data[ 'sender_city' ]; ?></div>
                            <div class="sender_state"><?php echo $data[ 'sender_state' ]; ?></div>
                            <div class="sender_country"><?php echo (isset($data['country'])) ? $data[ 'country' ] : ''; ?></div>
                        </span>
					<span style="display: none;">
                            <input type="text" name="sender_company" value="<?php esc_attr_e($data[ 'sender_company' ]); ?>" />
                            <input type="text" name="sender_name" value="<?php esc_attr_e($data[ 'sender_first_name' ]); ?>" />
                            <input type="text" name="sender_last_name" value="<?php esc_attr_e($data[ 'sender_last_name' ]); ?>" />
                            <input type="text" name="sender_street" value="<?php esc_attr_e($data[ 'sender_street' ] . ' ' . ((isset($data[ 'sender_street_nr' ])) ? $data[ 'sender_street_nr' ] : $data[ 'sender_street_no' ])) ?>" />
                            <input type="text" name="sender_city" value="<?php esc_attr_e($data[ 'sender_zip_code' ]); ?>" />
                            <input type="text" name="sender_state" value="<?php esc_attr_e($data[ 'sender_state' ]); ?>" />
                            <select name="sender_country">
                                <?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
									<option value="<?php esc_attr_e( $key ); ?>"
										<?php selected( $key === $data[ 'country' ] ) ?>>
                                        <?php echo $country; ?>
                                    </option>
								<?php endforeach; ?>
                            </select>
                        </span>
				</div>

				<div class="label-shipment-recipient address">
					<div class="recipient_company"><?php echo (isset($data['recipient_company'])) ? $data[ 'recipient_company' ] : ''; ?></div>
					<div class="recipient_name"><?php echo $data[ 'recipient_first_name' ]; ?> <?php echo $data[ 'recipient_last_name' ]; ?></div>
					<div class="recipient_street"><?php echo $data[ 'recipient_street' ]; ?> <?php echo (isset($data[ 'recipient_street_nr' ])) ? $data[ 'recipient_street_nr' ] : $data[ 'recipient_street_no' ]; ?></div>
					<div class="recipient_city"><?php echo $data[ 'recipient_zip_code' ]; ?> <?php echo $data[ 'recipient_city' ]; ?></div>
					<div class="recipient_state"><?php echo (isset($data['recipient_state'])) ? $data[ 'recipient_state' ] : ''; ?></div>
					<div class="recipient_country"><?php echo $data[ 'recipient_country' ]; ?></div>
				</div>

				<div class="label-shipment-actions">

					<p class="button-create-label<?php echo $classes_button_create_label; ?>">
						<input type="button" value="<?php _e( 'Create label', 'shipcloud-for-woocommerce' ); ?>" class="shipcloud_create_label button-primary"/>
					</p>
					<p class="button-download-label<?php echo $classes_button_download_label; ?>">
						<a href="<?php echo $data[ 'label_url' ]; ?>" target="_blank" class="button"><?php _e( 'Download label', 'shipcloud-for-woocommerce' ); ?></a>
					</p>

					<p class="button-tracking-url">
						<a href="<?php echo $data[ 'tracking_url' ]; ?>" target="_blank" class="button"><?php _e( 'Tracking link', 'shipcloud-for-woocommerce' ); ?></a>
					</p>

					<p class="button-edit-shipment" role="switch">
						<button class="button wcsc-edit-shipment" role="switch" type="button"
								style="display: none;">
							<b>
								<?php _ex( 'Save', 'Order: Backend button to edit prepared labels', 'wcsc' ) ?>
							</b>
						</button>
						<button class="button wcsc-edit-shipment" role="switch" type="button">
							<?php _ex( 'Edit shipment', 'Order: Backend button to edit prepared labels', 'wcsc' ) ?>
						</button>
					</p>

					<p class="button-delete-shipment">
						<input type="button" value="<?php _e( 'Delete shipment', 'shipcloud-for-woocommerce' ); ?>" class="shipcloud_delete_shipment button"/>
					</p>

					<input type="hidden" name="carrier" value="<?php echo $data[ 'carrier' ]; ?>"/>
					<input type="hidden" name="shipment_id" value="<?php echo $data[ 'id' ]; ?>"/>
				</div>

				<div style="clear: both;"></div>

				<div class="label-shipment-status">
					<table>
						<tbody>
						<?
						if ( (isset($data['description']) && !empty($data['description'])) ) {
							?>
							<tr>
								<th><?php _e( 'Shipment description', 'shipcloud-for-woocommerce' ); ?>:</th>
								<td><?php echo $data[ 'description' ]; ?></td>
							</tr>
							<?
						}
						?>
						<tr>
							<th><?php _e( 'Shipment id:', 'shipcloud-for-woocommerce' ); ?></th>
							<td><?php echo $display_id; ?></td>
						</tr>
						<tr>
							<th><?php _e( 'Tracking number:', 'shipcloud-for-woocommerce' ); ?></th>
							<td class="tracking-number">
								<?php if( array_key_exists( 'carrier_tracking_no', $data ) && ! empty( $data[ 'carrier_tracking_no' ] ) ): ?>
									<?php echo $data[ 'carrier_tracking_no' ]; ?>
								<?php else: ?>
									<?php _e( 'Not available yet', 'shipcloud-for-woocommerce' ); ?>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Tracking status:', 'shipcloud-for-woocommerce' ); ?></th>
							<td><?php echo $shipment_status; ?></td>
						</tr>
						<tr>
							<th><?php _e( 'Price:', 'shipcloud-for-woocommerce' ); ?></strong></th>
							<td class="price">
								<?php if ( ! empty( $data[ 'price' ] ) ): ?>
									<?php echo wc_price( $data[ 'price' ], array( 'currency' => 'EUR' ) ); ?>
								<?php else: ?>
									<?php _e( 'Not available yet', 'shipcloud-for-woocommerce' ); ?>
								<?php endif; ?>
							</td>
						</tr>
						</tbody>
					</table>
				</div>

				<div style="clear: both;"></div>

			</div>
		</div>
	</div>
</div>

<script type="application/javascript">
    new shipcloud.LabelView('#shipment-<?php echo $data['id']; ?>');
</script>
