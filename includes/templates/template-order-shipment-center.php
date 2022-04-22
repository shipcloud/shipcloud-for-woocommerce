<?php
	
?>
<div id="shipment-center">
	<input type="hidden" name="carrier_email_notification_enabled" value="<?php echo ( $this->carrier_email_notification_enabled() ? 'true' : 'false' ) ?>" />
	
	<?php /* Addresses */ ?>
	<div class="section addresses <?php if ( $order_status === 'completed' ) { echo 'hidden'; } ?>">

		<div class="address">
			<div class="address-form sender disabled">

				<h3><?php _e( 'Sender address', 'shipcloud-for-woocommerce' ); ?>
					<a class="btn-edit-address"><img width="14" alt="Edit" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/icons/edit.png"></a>
				</h3>

				<p class="fullsize">
					<label for="sender_address[company]"><?php _e( 'Company', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="sender_address[company]" value="<?php echo $sender[ 'company' ]; ?>" disabled>
				</p>

				<p class="fullsize">
					<label for="sender_address[first_name]"><?php _e( 'First name', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="sender_address[first_name]" value="<?php echo $sender[ 'first_name' ]; ?>" disabled>
				</p>

				<p class="fullsize">
					<label for="sender_address[last_name]"><?php _e( 'Last name', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="sender_address[last_name]" value="<?php echo $sender[ 'last_name' ]; ?>" disabled>
				</p>

                <div class="flex gap-10">
                    <p class="seventyfive">
                        <label for="sender_address[street]"><?php _e( 'Street', 'shipcloud-for-woocommerce' ); ?></label>
                        <input type="text" name="sender_address[street]" value="<?php echo $sender[ 'street' ]; ?>" disabled>
                    </p>

                    <p class="twentyfive">
                        <label for="sender_address[street_nr]"><?php _e( 'Number', 'shipcloud-for-woocommerce' ); ?></label>
                        <input type="text" name="sender_address[street_nr]" value="<?php echo isset($sender[ 'street_nr' ]) ? $sender[ 'street_nr' ] : $sender[ 'street_no' ]; ?>" disabled>
                    </p>
                </div>

				<p class="fullsize">
					<label for="sender_address[zip_code]"><?php _e( 'Postcode', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="sender_address[zip_code]" value="<?php echo $sender[ 'zip_code' ]?: $sender[ 'postcode' ]; ?>" disabled>
				</p>

				<p class="fullsize">
					<label for="sender_address[city]"><?php _e( 'City', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="sender_address[city]" value="<?php echo $sender[ 'city' ]; ?>" disabled>
				</p>

				<p class="fullsize">
					<label for="sender_address[state]"><?php _e( 'State', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="sender_address[state]" value="<?php echo $sender[ 'state' ]; ?>" disabled>
				</p>

                <p class="fullsize">
                    <label for="sender_address[country]">
                        <?php _e( 'Country', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                    <select name="sender_address[country]" disabled>
						<?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
							<?php $country_code = WC_Shipping_Shipcloud_Utils::maybe_extract_country_code( $key ); ?>
                            <option value="<?php esc_attr_e( $country_code ); ?>"
								<?php selected( $country_code === $sender['country'] ); ?>>
								<?php echo $country; ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </p>

                <p class="fullsize">
                    <label for="sender_address[phone]"><?php _e( 'Phone', 'shipcloud-for-woocommerce' ); ?></label>
                    <input type="text" name="sender_address[phone]" value="<?php echo $sender[ 'phone' ]; ?>" disabled>
                </p>
            </div>
		</div>

		<div class="address">
			<div class="address-form recipient disabled">

				<h3><?php _e( 'Recipient address', 'shipcloud-for-woocommerce' ); ?>
					<a class="btn-edit-address"><img width="14" alt="Edit" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/icons/edit.png"></a>
				</h3>

				<p class="fullsize">
					<label for="recipient_address[company]"><?php _e( 'Company', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="recipient_address[company]" value="<?php echo $recipient[ 'company' ]; ?>" disabled>
				</p>

				<p class="fullsize">
					<label for="recipient_address[first_name]"><?php _e( 'First name', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="recipient_address[first_name]" value="<?php echo $recipient[ 'first_name' ]; ?>" disabled>
				</p>

				<p class="fullsize">
					<label for="recipient_address[last_name]"><?php _e( 'Last name', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="recipient_address[last_name]" value="<?php echo $recipient[ 'last_name' ]; ?>" disabled>
				</p>

				<p class="fullsize">
					<label for="recipient_address[care_of]"><?php _e( 'email', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="recipient_address[email]" value="<?php esc_attr_e( $recipient[ 'email' ] ); ?>" disabled>
				</p>

				<p class="fullsize">
					<label for="recipient_address[care_of]"><?php _e( 'Care of', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="recipient_address[care_of]" value="<?php esc_attr_e( $recipient[ 'care_of' ] ); ?>" disabled>
				</p>

                <div class="flex gap-10">
                    <p class="seventyfive">
                        <label for="recipient_address[street]"><?php _e( 'Street', 'shipcloud-for-woocommerce' ); ?></label>
                        <input type="text" name="recipient_address[street]" value="<?php echo $recipient[ 'street' ]; ?>" disabled>
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
                        <label for="recipient_address[street_nr]"><?php _e( 'Number', 'shipcloud-for-woocommerce' ); ?></label>
                        <input type="text" name="recipient_address[street_nr]" value="<?php echo $recipient_address_street_nr; ?>" disabled>
                    </p>
                </div>
				
				<p class="fullsize">
					<label for="recipient_address[zip_code]"><?php _e( 'Postcode', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="recipient_address[zip_code]" value="<?php echo $recipient[ 'postcode' ]?: $recipient[ 'zip_code' ]; ?>" disabled>
				</p>

				<p class="fullsize">
					<label for="recipient_address[city]"><?php _e( 'City', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text" name="recipient_address[city]" value="<?php echo $recipient[ 'city' ]; ?>" disabled>
				</p>

				<p class="fullsize">
                    <label for="recipient_address[state]"><?php _e( 'State', 'shipcloud-for-woocommerce' ); ?></label>
					<input type="text"
                           name="recipient_address[state]"
                           value="<?php if (array_key_exists('state', $recipient)): echo $recipient[ 'state' ]; endif ?>"
                           disabled>
				</p>

				<p class="fullsize">
                    <label for="recipient_address[country]">
                        <?php _e( 'Country', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                    <select name="recipient_address[country]" disabled>
						<?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
							<?php $country_code = WC_Shipping_Shipcloud_Utils::maybe_extract_country_code( $key ); ?>
                            <option value="<?php esc_attr_e( $country_code ); ?>"
								<?php selected( $country_code === $recipient['country'] ) ?>>
								<?php echo $country; ?>
                            </option>
						<?php endforeach; ?>
                    </select>
				</p>

                <p class="fullsize">
                    <label for="recipient_address[phone]"><?php _e( 'Phone', 'shipcloud-for-woocommerce' ); ?></label>
                    <input type="text" name="recipient_address[phone]" value="<?php echo $recipient[ 'phone' ]; ?>" disabled>
                </p>
			</div>
		</div>
		
	</div>
	
	<?php /* Parcel Content */ ?>
	<div class="section parcels  <?php if ( $order_status === 'completed' ) { echo 'hidden'; } ?>" data-calculated-weight="<?php echo( $this->get_calculated_weight() ); ?>">
		<h3><?php _e( 'Create shipment', 'shipcloud-for-woocommerce' ); ?></h3>
        <div class="parcels__columns-wrapper">
            <div class="create-label">
                
                <?php include( dirname( __FILE__ ) . '/template-order-parcel-form-table.php' ); ?>
                <?php include( dirname( __FILE__ ) . '/template-label-form.php' ); ?>
                
                <script type="application/javascript">
                    jQuery(function ($) {
                        $('#shipcloud_csp_wrapper').shipcloudMultiSelect(wcsc_carrier);
                        $('select[name="parcel_list"]').shipcloudFiller('div.parcel-form-table');
                        <?php if ( ! empty( $shipping_method ) ) : ?>
                            $('#shipcloud_carrier > option[value="<?php echo $shipping_method['carrier']; ?>"]').prop("selected", true).trigger('change');
                            $('#shipcloud_carrier_service > option[value="<?php echo $shipping_method['service']; ?>"]').prop("selected", true);
                        <?php endif; ?>
                    });
                </script>
            </div>

            <div class="additional_services">
                <script type="template/html" id="tmpl-shipcloud-shipment-additional-services">
                    <?php include( dirname( __FILE__ ) . '/template-additional-services-edit-form.php' ); ?>
                </script>
                <script type="application/javascript">
                    jQuery(function ($) {
                        shipcloud.additionalServices = new shipcloud.ShipmentAdditionalServicesView({
                            model: new shipcloud.ShipmentModel(),
                            el   : '.section.parcels .additional_services'
                        });

                        shipcloud.additionalServices.render();

                        <?php
                        if ( $this->carrier_email_notification_enabled() ) {
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

                        if ( WC_Shipping_Shipcloud_Utils::get_cod_id() === $payment_method ) {
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
                            'reference1'          => $this->get_global_reference_number(),
                            'bank_account_holder' => $this->get_option('bank_account_holder'),
                            'bank_name'           => $this->get_option('bank_name'),
                            'bank_account_number' => $this->get_option('bank_account_number'),
                            'bank_code'           => $this->get_option('bank_code')
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
        </div>


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
			<button id="shipcloud_create_shipment_label" class="btn-primary button button-primary" type="button" value="<?php _e( 'Create label', 'shipcloud-for-woocommerce' ); ?>">
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
                <?php include( dirname( __FILE__ ) . '/template-customs-declaration-edit-form.php' ); ?>
            </script>
        </div>
        <div style="clear: both"></div>

	</div>
	
	<?php /* Labels */ ?>
	<?php 
	
	$shipment_data = get_post_meta( $this->order_id, 'shipcloud_shipment_data' );

	?>

	<div class="info"></div>

	<div id="create_label">

		<div class="shipping-data">
			<div class="shipment-labels" id="shipment-labels"></div>
			<?php
			$json_data = array();
			if ( '' != $shipment_data && is_array( $shipment_data ) ) {
				$shipment_data = array_reverse( $shipment_data );
				foreach ( $shipment_data as $data ) {
					$json_data[] = WC_Shipping_Shipcloud_Utils::convert_to_wc_api_response( $data, $this->order_id );
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
				<?php include( dirname( __FILE__ ) . '/template-order-label.php' ); ?>
			</script>
			<script type="template/html" id="tmpl-shipcloud-shipment-pickup-request">
				<?php include( dirname( __FILE__ ) . '/template-pickup-request-form-basic.php' ); ?>
			</script>
			<script type="template/html" id="tmpl-shipcloud-shipment-edit">
				<?php include( dirname( __FILE__ ) . '/template-order-shipment-edit.php' ); ?>
			</script>
			<div style="clear: both"></div>
		</div>
	</div>
	<div id="ask-create-label"><?php _e( 'Depending on the carrier, there will be a fee for creating the label. Do you really want to create a label?', 'shipcloud-for-woocommerce' ); ?></div>
	<div id="ask-delete-shipment"><?php _e( 'Do you really want to delete this shipment?', 'shipcloud-for-woocommerce' ); ?></div>
	<div id="ask-force-delete-shipment"><?php _e( 'Do you want to delete this shipment from the WooCommerce database nonetheless?', 'shipcloud-for-woocommerce' ); ?></div>
	
</div>
<div class="clear"></div>