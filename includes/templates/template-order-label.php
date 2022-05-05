<div class="loading-overlay" style="display: none;">
    <div class="spin-loader"></div>
</div>

<div class="widget-quick-actions">
    <# if ( data.model.get('label_url') ) { #>
        <# if ( data.model.get('customs_declaration').carrier_declaration_document_url ) { #>
            <a href="{{ data.model.get('customs_declaration').carrier_declaration_document_url }}" target="_blank" class="button btn-primary">
                <i class="fa-solid fa-file-contract"></i>
                <?php _e( 'Download customs document', 'shipcloud-for-woocommerce' ); ?>
            </a>
        <# } #>

        <a href="{{ data.model.get('label_url') }}" target="_blank" class="button btn-primary">
            <i class="fa-solid fa-file-alt"></i>
            <?php _e( 'Download label', 'shipcloud-for-woocommerce' ); ?>
        </a>
    <# } else { #>
        <button class="shipcloud_create_label btn-primary button" type="button" data-ask-create-label-check="<?php echo esc_attr($this->get_option( 'ask_create_label_check' )); ?>">
            <i class="fa-solid fa-plus-square"></i>
            <?php _e( 'Create label', 'shipcloud-for-woocommerce' ); ?>
        </button>
    <# } #>
	
	<?php if ( $order_status !== 'completed' ) : ?>
    <button class="shipcloud_delete_shipment button" type="button">
        <i class="fa-solid fa-trash-alt"></i>
    </button>
	<?php endif; ?>
	
</div>

<div class="widget-top">
    <div class="widget-title-action">
        <a class="widget-action hide-if-no-js"></a>
    </div>
    <div class="widget-title">
        <img class="shipcloud-widget-icon" src="<?php echo WC_SHIPPING_SHIPCLOUD_IMG_DIR; ?>/truck-32x32.png"/>
        <h4>
            {{ data.model.get('from').getTitle() }}
            <span class="dashicons dashicons-arrow-right-alt"></span>
            {{ data.model.get('to').getTitle() }}
            <span class="dashicons dashicons-screenoptions"></span>
            {{ data.model.getTitle() }}
        </h4>
    </div>
</div>

<div class="widget-inside">
    <div class="widget-content label-shipment-info">
        <div class="label-shipment-sender address" role="switch">
          <strong><?php _e( 'Sender address', 'shipcloud-for-woocommerce' ); ?></strong>
            <div>{{ data.model.get('from').get('company') }}</div>
            <div>{{ data.model.get('from').getFullName() }}</div>
            <div>{{ data.model.get('from').getFullStreet() }}</div>
            <div>{{ data.model.get('from').get('care_of') }}</div>
            <div>{{ data.model.get('from').getFullCity() }}</div>
            <div>{{ data.model.get('from').get('state') }}</div>
            <div>{{ data.model.get('from').get('country') }}</div>
			<div>{{ data.model.get('from').get('phone') }}</div>
        </div>
        <div class="label-shipment-recipient address" role="switch">
          <strong><?php _e( 'Recipient address', 'shipcloud-for-woocommerce' ); ?></strong>
            <div>{{ data.model.get('to').get('company') }}</div>
            <div>{{ data.model.get('to').getFullName() }}</div>
            <div>{{ data.model.get('to').getFullStreet() }}</div>
            <div>{{ data.model.get('to').get('care_of') }}</div>
            <div>{{ data.model.get('to').getFullCity() }}</div>
            <div>{{ data.model.get('to').get('state') }}</div>
            <div>{{ data.model.get('to').get('country') }}</div>
			<div>{{ data.model.get('to').get('phone') }}</div>
        </div>
        
		<div class="label-shipment-additional-services" role="switch">
          <strong><?php _e( 'Additional services', 'shipcloud-for-woocommerce' ); ?></strong>
		  <# if ( data.model.get('additional_services').length === 0 ) { #>
		  <p>
		    <?php _e( 'No additional service has been booked for this shipment', 'shipcloud-for-woocommerce' ); ?>
		  </p>
		  <# } else { #>
		  <ul>
		  <# _.each(data.model.get('additional_services'), function(additional_service) { #>
		    <# if ( additional_service.name === 'advance_notice' ) { #>
		      <li>
		        <?php _e( 'Advance notice', 'shipcloud-for-woocommerce' ); ?>
		        <div class="additional-services-details">
		          <# if (additional_service.properties.email) { #>
		            <?php _e( 'eMail', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.email }}
		          <# } if (additional_service.properties.phone) { #>
		            <?php _e( 'Phone', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.phone }}
		          <# } if (additional_service.properties.sms) { #>
		            <?php _e( 'SMS', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.sms }}
		          <# } #>
		        </div>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'saturday_delivery' ) { #>
		      <li>
		        <?php _e( 'Saturday delivery', 'shipcloud-for-woocommerce' ); ?>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'visual_age_check' ) { #>
		      <li>
		        <?php _e( 'DHL visual age check', 'shipcloud-for-woocommerce' ); ?>
		        <div class="additional-services-details">
		            <?php _e( 'Minimum age', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.minimum_age }}
		        </div>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'ups_adult_signature' ) { #>
		      <li>
		        <?php _e( 'UPS adult signature', 'shipcloud-for-woocommerce' ); ?>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'premium_international' ) { #>
		      <li>
		        <?php _e( 'Premium international', 'shipcloud-for-woocommerce' ); ?>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'delivery_date' ) { #>
		      <li>
		        <?php _e( 'Delivery date', 'shipcloud-for-woocommerce' ); ?>
		        <div class="additional-services-details">
		            <?php _e( 'Date', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.date }}
		        </div>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'delivery_note' ) { #>
		      <li>
		        <?php _e( 'Delivery note', 'shipcloud-for-woocommerce' ); ?>
		        <div class="additional-services-details">
		            <?php _e( 'Message', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.message }}
		        </div>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'angel_de_delivery_date_time' ) { #>
		      <li>
		        <?php _e( 'MyTime delivery', 'shipcloud-for-woocommerce' ); ?>
		        <div class="additional-services-details">
		            <?php _e( 'Date', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.date }}
		            <br />
		            <?php _e( 'Earliest time', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.time_of_day_earliest }}
		            <br />
		            <?php _e( 'Latest time', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.time_of_day_latest }}
		        </div>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'delivery_time' ) { #>
		      <li>
		        <?php _e( 'Delivery time', 'shipcloud-for-woocommerce' ); ?>
		        <div class="additional-services-details">
		            <?php _e( 'Earliest time', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.time_of_day_earliest }}
		            <br />
		            <?php _e( 'Latest time', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.time_of_day_latest }}
		        </div>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'drop_authorization' ) { #>
		      <li>
		        <?php _e( 'Drop authorization', 'shipcloud-for-woocommerce' ); ?>
		        <div class="additional-services-details">
		            <?php _e( 'Message', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.message }}
		        </div>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'dhl_endorsement' ) { #>
		      <li>
		        <?php _e( 'Endorsement', 'shipcloud-for-woocommerce' ); ?>
		        <div class="additional-services-details">
		            <?php _e( 'Handling', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.handling }}
		        </div>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'dhl_named_person_only' ) { #>
		      <li>
		        <?php _e( 'Named person only', 'shipcloud-for-woocommerce' ); ?>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'cash_on_delivery' ) { #>
		      <li>
		        <?php _e( 'Cash on delivery', 'shipcloud-for-woocommerce' ); ?>
		        <div class="additional-services-details">
		            <?php _e( 'Amount', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.amount }}
		            <br />
		            <?php _e( 'Currency', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.currency }}
		            <# if ( additional_service.properties.reference1 ) { #>
		                <br />
		                <?php _e( 'Reference', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.reference1 }}
		            <# } #>
		            <# if ( additional_service.properties.bank_account_holder ) { #>
		                <br />
		                <?php _e( 'Bank account holder', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.bank_account_holder }}
		            <# } #>
		            <# if ( additional_service.properties.bank_name ) { #>
		                <br />
		                <?php _e( 'Bank name', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.bank_name }}
		            <# } #>
		            <# if ( additional_service.properties.bank_account_number ) { #>
		                <br />
		                <?php _e( 'Bank account number (IBAN)', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.bank_account_number }}
		            <# } #>
		            <# if ( additional_service.properties.bank_code ) { #>
		                <br />
		                <?php _e( 'Bank code (SWIFT)', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.bank_code }}
		            <# } #>
		        </div>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'gls_guaranteed24service' ) { #>
		      <li>
		        <?php _e( 'GLS Guaranteed24Service', 'shipcloud-for-woocommerce' ); ?>
		      </li>
		    <# } #>
		    <# if ( additional_service.name === 'dhl_parcel_outlet_routing' ) { #>
		      <li>
		        <?php _e( 'Parcel outlet routing', 'shipcloud-for-woocommerce' ); ?>
		        <div class="additional-services-details">
		          <?php _e( 'email', 'shipcloud-for-woocommerce' ); ?>: {{ additional_service.properties.email }}
		        </div>
		      </li>
		    <# } #>
		  <# }); #>
		  </ul>
		  <# } #>
        </div>

        <# if ( data.model.get('customs_declaration') ) { #>
            <div class="label-shipment-customs-declaration" role="switch">
                <div class="customs-declaration-basic-data">
                    <h3>
                        <?php _e( 'Customs declaration', 'shipcloud-for-woocommerce' ); ?>
                    </h3>
                    <table class="customs-declaration-data">
                        <tbody>
                            <tr>
                                <th>
                                    <?php _e( 'Contents type', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>{{ data.model.getCustomsDeclarationContentsTypeName(data.model.get('customs_declaration').contents_type) }}</td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Contents explanation', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>{{ data.model.get('customs_declaration').contents_explanation }}</td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Currency', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>{{ data.model.get('customs_declaration').currency }}</td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Additional fees', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>{{ data.model.get('customs_declaration').additional_fees }}</td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Drop off location', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>{{ data.model.get('customs_declaration').drop_off_location }}</td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Exporter reference', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>{{ data.model.get('customs_declaration').exporter_reference }}</td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Importer reference', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>{{ data.model.get('customs_declaration').importer_reference }}</td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Posting date', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>{{ data.model.get('customs_declaration').posting_date }}</td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Invoice number', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>{{ data.model.get('customs_declaration').invoice_number }}</td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Total value amount', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>{{ data.model.get('customs_declaration').total_value_amount }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="customs-declaration-item-data">
                    <h4>
                        <?php _e( 'Items', 'shipcloud-for-woocommerce' ); ?>
                    </h4>
                    <# _.each(data.model.get('customs_declaration').items, function(item) { #>
                        <table class="customs-declaration-data">
                            <tbody>
                                <tr>
                                    <th>
                                        <?php _e( 'Description', 'shipcloud-for-woocommerce' ); ?>
                                    </th>
                                    <td>{{ item.description }}</td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php _e( 'Origin country', 'shipcloud-for-woocommerce' ); ?>
                                    </th>
                                    <td>{{ item.origin_country }}</td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php _e( 'Quantity', 'shipcloud-for-woocommerce' ); ?>
                                    </th>
                                    <td>{{ item.quantity }}</td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php _e( 'Value amount', 'shipcloud-for-woocommerce' ); ?>
                                    </th>
                                    <td>{{ item.value_amount }}</td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php _e( 'Net weight', 'shipcloud-for-woocommerce' ); ?>
                                    </th>
                                    <td>{{ item.net_weight }}</td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php _e( 'HS tariff number', 'shipcloud-for-woocommerce' ); ?>
                                    </th>
                                    <td>{{ item.hs_tariff_number }}</td>
                                </tr>
                            </tbody>
                        </table>
                    <# }); #>
                </div>
            </div>
        <# } #>
		
		<div class="clearfix"></div>

		<div class="label-shipment-status">
	        <div class="label-shipment-status-section">
	          <# if ( data.model.get('description') ) { #>
                <div class="shipment-info-item">
                    <span class="shipment-info-item__label"><?php _e( 'Shipment description', 'shipcloud-for-woocommerce' ); ?>:</span>
                    <span class="shipment-info-item__value">{{ data.model.get('description') }}</span>
                </div>
	          <# } #>
	          <# if ( data.model.get('reference_number') ) { #>
	            <div class="shipment-info-item">
	                <span class="shipment-info-item__label"><?php _e( 'Reference number:', 'shipcloud-for-woocommerce' ); ?></span>
	                <span class="shipment-info-item__value">{{ data.model.get('reference_number') }}</span>
	            </div>
	          <# } #>
	          <# if ( data.model.get('notification_email') ) { #>
	            <div class="shipment-info-item">
	                <span class="shipment-info-item__label"><?php _e( 'shipcloud notification email', 'shipcloud-for-woocommerce' ); ?></span>
	                <span class="shipment-info-item__value">{{ data.model.get('notification_email') }}</span>
	            </div>
	          <# } #>
	          <# if ( data.model.get('id') ) { #>
	            <div class="shipment-info-item">
	                <span class="shipment-info-item__label"><?php _e( 'Shipment id:', 'shipcloud-for-woocommerce' ); ?></span>
	                <span class="shipment-info-item__value">
	                  <a href="{{ data.model.get('tracking_url') }}" target="_blank">
	                    {{ data.model.get('id') }}
	                  </a>
	                </span>
	            </div>
	          <# } #>
	          <# if ( data.model.get('label').format ) { #>
	            <div class="shipment-info-item">
	                <span class="shipment-info-item__label"><?php _e( 'Label format:', 'shipcloud-for-woocommerce' ); ?></span>
	                <span class="shipment-info-item__value">
                    {{ data.model.get('label').format }}
                  </span>
	            </div>
	          <# } #>
	          <# if ( data.model.get('label_url') ) { #>
	            <div class="shipment-info-item">
	                <span class="shipment-info-item__label"><?php _e( 'Tracking number:', 'shipcloud-for-woocommerce' ); ?></span>
	                <span class="tracking-number shipment-info-item__value">
	                    <# if ( data.model.get('carrier_tracking_no') ) { #>
	                        <a href="{{ data.model.getCarrierTrackingUrl() }}" target="_blank">
	                          {{ data.model.get('carrier_tracking_no') }}
	                        </a>
	                    <# } else { #>
	                        <?php _e( 'Not available yet', 'shipcloud-for-woocommerce' ); ?>
	                    <# } #>
                    </span>
	            </div>
	            <div class="shipment-info-item">
	                <span class="shipment-info-item__label"><?php _e( 'Tracking status:', 'shipcloud-for-woocommerce' ); ?></span>
	                <span class="shipment-info-item__value">
	                    <# if ( data.model.get('shipment_status') ) { #>
	                        {{ data.model.get('shipment_status') }}
	                    <# } else { #>
	                        <?php _e( 'Not available yet', 'shipcloud-for-woocommerce' ); ?>
	                    <# } #>
                    </span>
	            </div>
	            <div class="shipment-info-item">
	                <span class="shipment-info-item__label"><?php _e( 'Price:', 'shipcloud-for-woocommerce' ); ?></strong></span>
	                <span class="price shipment-info-item__value">
	                    <# if ( data.model.get('price') ) { #>
	                        {{ data.model.get('price') }}
	                    <# } else { #>
	                        <?php _e( 'Not available yet', 'shipcloud-for-woocommerce' ); ?>
	                    <# } #>
                    </span>
	            </div>
	          <# } #>
            </div>
		</div>
		
        <div class="label-shipment-pickup-request">
            <# if ( data.model.get('pickup_request') ) { #>
                <strong><?php _e( 'Pickup timeframe', 'shipcloud-for-woocommerce' ); ?></strong>
                <div>
                    {{ data.model.get('pickup_request').getPickupTimeAsRange() }}
                </div>
                <br />
                <strong><?php _e( 'Pickup id', 'shipcloud-for-woocommerce' ); ?></strong>
                <div>
                    {{ data.model.get('pickup_request').id }}
                </div>
                <# if ( data.model.get('pickup_request').get('pickup_address') ) { #>
                    <br />
                    <strong><?php _e( 'Pickup address', 'shipcloud-for-woocommerce' ); ?></strong>
                    <div>{{ data.model.get('pickup_request').get('pickup_address').get('company') }}</div>
                    <div>{{ data.model.get('pickup_request').get('pickup_address').getFullName() }}</div>
                    <div>{{ data.model.get('pickup_request').get('pickup_address').getFullStreet() }}</div>
                    <div>{{ data.model.get('pickup_request').get('pickup_address').get('care_of') }}</div>
                    <div>{{ data.model.get('pickup_request').get('pickup_address').getFullCity() }}</div>
                    <div>{{ data.model.get('pickup_request').get('pickup_address').get('state') }}</div>
                    <div>{{ data.model.get('pickup_request').get('pickup_address').get('country') }}</div>
                <# } #>
            <# } #>
        </div>

        <div class="label-shipment-actions">
			
			<?php if ( $order_status !== 'completed' ) : ?>
            <button type="button" class="shipcloud_delete_shipment button">
                <?php _e( 'Delete shipment', 'shipcloud-for-woocommerce' ); ?>
            </button>
			<?php endif; ?>

            <# if ( data.model.get('label_url') ) { #>
                <# if ( data.model.get('customs_declaration').carrier_declaration_document_url ) { #>
                    <a href="{{ data.model.get('customs_declaration').carrier_declaration_document_url }}" target="_blank" class="button">
                        <?php _e( 'Download customs document', 'shipcloud-for-woocommerce' ); ?>
                    </a>
                <# } #>

                <a href="{{ data.model.get('label_url') }}" target="_blank" class="button">
                    <?php _e( 'Download label', 'shipcloud-for-woocommerce' ); ?>
                </a>

				<?php if (class_exists('WC_DateTime')) : ?>
                <# if ( !data.model.get('pickup_request') && _.contains(shipcloud_pickup_carriers.carriers_with_pickup_request, data.model.get('carrier'))) { #>
					<?php if ( $order_status !== 'completed' ) : ?>
                    <button class="button button-primary shipcloud-open-pickup-request-form" role="switch" type="button">
                        <?php _e( 'Create pickup request', 'shipcloud-for-woocommerce' ) ?>
                    </button>
					<?php endif; ?>
                <# } #>
				<?php endif; ?>
            <# } else { #>
				<button class="button wcsc-edit-shipment" role="switch" type="button">
                    <?php _ex( 'Edit shipment', 'Order: Backend button to edit prepared labels', 'wcsc' ) ?>
                </button>

                <button class="shipcloud_create_label button-primary" type="button">
                    <?php _e( 'Create label', 'shipcloud-for-woocommerce' ); ?>
                </button>
            <# } #>

            <button class="button wcsc-save-shipment button-primary" role="switch" type="button"
                    style="display: none;">
                <?php _ex( 'Save', 'Order: Backend button to edit prepared labels', 'wcsc' ) ?>
            </button>

            <input type="hidden" name="carrier" value="{{ data.model.get('carrier') }}"/>
            <input type="hidden" name="service" value="{{ data.model.get('service') }}"/>
            <input type="hidden" name="shipment_id" value="{{ data.model.get('id') }}"/>
            <input type="hidden" name="shipment_order_id" value="<?php echo get_the_ID(); ?>"/>
        </div>

    </div>
</div>
