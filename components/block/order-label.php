<div id="shipment-<?php echo $data[ 'id' ]; ?>" class="label widget">
    <div class="loading-overlay">
        <div class="spin-loader"></div>
    </div>
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
                        <div class="sender_company"><?php echo $data['sender_company']; ?></div>
                        <div>
                            <span class="sender_first_name">
                                <?php echo $data['sender_first_name']; ?>
                            </span>
                            <span class="sender_last_name">
                                <?php echo $data['sender_last_name']; ?>
                            </span>
                        </div>
                        <div>
                            <span class="sender_street"><?php echo $data['sender_street']; ?></span>
                            <span class="sender_street_no">
                                <?php echo ( isset( $data['sender_street_nr'] ) ) ? $data['sender_street_nr'] : $data['sender_street_no']; ?>
                            </span>
                        </div>
                        <div>
                            <span class="sender_care_of">
                                <?php echo $data['sender_care_of']; ?>
                            </span>
                        </div>
                        <div>
                            <span class="sender_zip_code"><?php echo $data['sender_zip_code']; ?></span>
                            <span class="sender_city"><?php echo $data['sender_city']; ?></span>
                        </div>
                        <div class="sender_state"><?php echo $data['sender_state']; ?></div>
                        <div class="sender_country"><?php echo ( isset( $data['country'] ) ) ? $data['country'] : ''; ?></div>
                    </span>
                    <fieldset style="display: none;">
                        <legend><?php echo esc_html_x('Sender address', 'Backend: Legend for sender fieldset', 'shipcloud-for-woocommerce') ?></legend>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'Company', 'Backend: Label for company input', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="sender_company"
                                   value="<?php esc_attr_e( $data['sender_company'] ); ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'First name', 'Backend: Label for first name', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="sender_first_name"
                                   value="<?php esc_attr_e( $data['sender_first_name'] ); ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'Last name', 'Backend: Label for last name', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="sender_last_name"
                                   value="<?php esc_attr_e( $data['sender_last_name'] ); ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="sender_care_of">
                                <?php echo esc_html_x( 'Care Of', 'Backend: Label for care of', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text"
                                   name="sender_care_of"
                                   value="<?php esc_attr_e( $data['sender_care_of'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'Street', 'Backend: Label for street input', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="sender_street"
                                   value="<?php esc_attr_e( $data['sender_street'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'Street number', 'Backend: Label for street no', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="sender_street_no"
                                   value="<?php esc_attr_e( ( isset( $data['sender_street_nr'] ) ) ? $data['sender_street_nr'] : $data['sender_street_no'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'ZIP code', 'Backend: Label for zip code', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="sender_zip_code"
                                   value="<?php esc_attr_e( $data['sender_zip_code'] ); ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'City', 'Backend: Label for city', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="sender_city" value="<?php esc_attr_e( $data['sender_city'] ); ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'State', 'Backend: Label for state', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="sender_state"
                                   value="<?php esc_attr_e( $data['sender_state'] ); ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'Country', 'Backend: Label for country', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <select name="country">
                                <?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
                                    <option value="<?php esc_attr_e( $key ); ?>"
										<?php selected( $key === $data['country'] ) ?>>
                                        <?php echo $country; ?>
                                    </option>
								<?php endforeach; ?>
                            </select>
                        </div>
                    </fieldset>
                </div>

                <div class="label-shipment-recipient address" role="switch">
                    <span>
					<div class="recipient_company"><?php echo ( isset( $data['recipient_company'] ) ) ? $data['recipient_company'] : ''; ?></div>
					<div>
                        <span class="recipient_first_name">
                            <?php echo $data['recipient_first_name']; ?>
                        </span>
                        <span class="recipient_last_name">
                            <?php echo $data['recipient_last_name']; ?>
                        </span>
                    </div>
					<div>
                        <span class="recipient_street">
                            <?php echo $data['recipient_street']; ?>
                        </span>
                        <span class="recipient_street_no">
                            <?php echo ( isset( $data['recipient_street_nr'] ) ) ? $data['recipient_street_nr'] : $data['recipient_street_no']; ?>
                        </span>
                    </div>
                    <div>
                        <span class="recipient_care_of">
                            <?php echo $data['recipient_care_of']; ?>
                        </span>
                    </div>
                    <div>
                        <span class="recipient_company">
                            <?php echo $data['recipient_company']; ?>
                        </span>
                    </div>
					<div>
                        <span class="recipient_zip_code">
                            <?php echo $data['recipient_zip_code']; ?>
                        </span>
                        <span class="recipient_city">
                            <?php echo $data['recipient_city']; ?>
                        </span>
                    </div>
					<div class="recipient_state"><?php echo ( isset( $data['recipient_state'] ) ) ? $data['recipient_state'] : ''; ?></div>
					<div class="recipient_country"><?php echo $data['recipient_country']; ?></div>
                    </span>
                    <fieldset style="display: none;">
                        <legend><?php echo esc_html_x( 'Recipient address', 'Backend: Title for recipient form-fieldset.', 'shipcloud-for-woocommerce' ) ?></legend>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'Company', 'Backend: Label for company', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="recipient_company"
                                   value="<?php esc_attr_e( $data['recipient_company'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'First name', 'Backend: Label for first name', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="recipient_first_name"
                                   value="<?php esc_attr_e( $data['recipient_first_name'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
								<?php echo esc_html_x( 'Last name', 'Backend: Label for last name', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="recipient_last_name"
                                   value="<?php esc_attr_e( $data['recipient_last_name'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="recipient_care_of">
                                <?php echo esc_html_x( 'Care Of', 'Backend: Label for care of', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text"
                                   name="recipient_care_of"
                                   value="<?php esc_attr_e( $data['recipient_care_of'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'Street', 'Backend: Label for street', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text"
                                   name="recipient_street"
                                   value="<?php esc_attr_e( $data['recipient_street'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'Street number', 'Backend: Label for street number', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text"
                                   name="recipient_street_no"
                                   value="<?php esc_attr_e( ( isset( $data['recipient_street_nr'] ) ) ? $data['recipient_street_nr'] : $data['recipient_street_no'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'ZIP code', 'Backend: Label for zip code', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text"
                                   name="recipient_zip_code"
                                   value="<?php esc_attr_e( $data['recipient_zip_code'] ) ?>" />
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'City', 'Backend: Label for City', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text"
                                     name="recipient_city"
                                     value="<?php esc_attr_e( $data['recipient_city'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'State', 'Backend: Label for state', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <input type="text" name="recipient_state"
                                   value="<?php esc_attr_e( $data['recipient_state'] ) ?>"/>
                        </div>
                        <div class="form-field">
                            <label for="">
                                <?php echo esc_html_x( 'Country', 'Backend: Label for country', 'shipcloud-for-woocommerce' ) ?>
                            </label>
                            <select name="recipient_country">
								<?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
                                    <option value="<?php esc_attr_e( $key ); ?>"
										<?php selected( $key === $data['recipient_country'] ) ?>>
										<?php echo $country; ?>
                                    </option>
								<?php endforeach; ?>
                            </select>
                        </div>
                    </fieldset>
                </div>

				<div class="label-shipment-actions">

					<p class="button-create-label<?php echo $classes_button_create_label; ?>">
            <button class="shipcloud_create_label button-primary" type="button">
              <?php _e( 'Create label', 'shipcloud-for-woocommerce' ); ?>
            </button>
					</p>

					<p class="button-download-label<?php echo $classes_button_download_label; ?>">
						<a href="<?php echo $data[ 'label_url' ]; ?>" target="_blank" class="button"><?php _e( 'Download label', 'shipcloud-for-woocommerce' ); ?></a>
					</p>

					<p class="button-tracking-url">
						<a href="<?php echo $data[ 'tracking_url' ]; ?>" target="_blank" class="button"><?php _e( 'Tracking link', 'shipcloud-for-woocommerce' ); ?></a>
					</p>

                    <?php if ( empty( $data[ 'label_url' ] ) ): ?>
                        <p class="button-edit-shipment" role="switch">
                            <button class="button wcsc-save-shipment button-primary" role="switch" type="button"
                                    style="display: none;">
                                <?php _ex( 'Save', 'Order: Backend button to edit prepared labels', 'wcsc' ) ?>
                            </button>
                            <button class="button wcsc-edit-shipment" role="switch" type="button">
                                <?php _ex( 'Edit shipment', 'Order: Backend button to edit prepared labels', 'wcsc' ) ?>
                            </button>
                        </p>
                    <?php endif; ?>

					<p class="button-delete-shipment">
                        <button type="button" class="shipcloud_delete_shipment button">
							<?php _e( 'Delete shipment', 'shipcloud-for-woocommerce' ); ?>
                        </button>
					</p>

					<input type="hidden" name="carrier" value="<?php echo $data[ 'carrier' ]; ?>"/>
					<input type="hidden" name="shipment_id" value="<?php echo $data[ 'id' ]; ?>"/>
					<input type="hidden" name="shipment_order_id" value="<?php echo get_the_ID(); ?>"/>
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

