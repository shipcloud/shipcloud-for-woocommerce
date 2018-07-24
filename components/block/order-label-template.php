<div class="loading-overlay" style="display: none;">
    <div class="spin-loader"></div>
</div>

    <div class="widget-quick-actions">
        <# if ( data.model.get('label_url') ) { #>
            <a href="{{ data.model.get('label_url') }}" target="_blank" class="button btn-primary">
                <span class="dashicons dashicons-external"></span>
				<?php _ex( 'Download', 'Download a label', 'shipcloud-for-woocommerce' ); ?>
            </a>
            <# } else { #>
                <button class="shipcloud_create_label btn-primary button" type="button">
                    <span class="dashicons dashicons-yes"></span>
					<?php _e( 'Create', 'shipcloud-for-woocommerce' ); ?>
                </button>
                <# } #>
                    <button class="shipcloud_delete_shipment button" type="button">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
    </div>
<div class="widget-top">
    <div class="widget-title-action">
        <a class="widget-action hide-if-no-js"></a>
    </div>
    <div class="widget-title">
        <img class="shipcloud-widget-icon" src="<?php echo WCSC_URLPATH; ?>assets/icons/truck-32x32.png"/>


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
    <div class="widget-content">
        <div class="label-shipment-sender address" role="switch">
          <strong><?php _e( 'Sender address', 'shipcloud-for-woocommerce' ); ?></strong>
            <div>{{ data.model.get('from').get('company') }}</div>
            <div>{{ data.model.get('from').getFullName() }}</div>
            <div>{{ data.model.get('from').getFullStreet() }}</div>
            <div>{{ data.model.get('from').get('care_of') }}</div>
            <div>{{ data.model.get('from').getFullCity() }}</div>
            <div>{{ data.model.get('from').get('state') }}</div>
            <div>{{ data.model.get('from').get('country') }}</div>
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
                  <?php _e( 'DHL premium international', 'shipcloud-for-woocommerce' ); ?>
                </li>
              <# } #>
              <# if ( additional_service.name === 'delivery_time' ) { #>
                <li>
                  <?php _e( 'DHL preferred time', 'shipcloud-for-woocommerce' ); ?>
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
            <# }); #>
            </ul>
          <# } #>
        </div>

        <table class="label-shipment-status">
            <tbody>
            <# if ( data.model.get('description') ) { #>
                <tr>
                    <th><?php _e( 'Shipment description', 'shipcloud-for-woocommerce' ); ?>:</th>
                    <td>{{ data.model.get('description') }}</td>
                </tr>
                <# } #>

                    <# if ( data.model.get('reference_number') ) { #>
                    <tr>
                        <th><?php _e( 'Reference number:', 'shipcloud-for-woocommerce' ); ?></th>
                        <td>{{ data.model.get('reference_number') }}</td>
                    </tr>
                    <# } #>
                    <# if ( data.model.get('id') ) { #>
                    <tr>
                        <th><?php _e( 'Shipment id:', 'shipcloud-for-woocommerce' ); ?></th>
                        <td>
                          <a href="{{ data.model.get('tracking_url') }}" target="_blank">
                            {{ data.model.get('id') }}
                          </a>
                        </td>
                    </tr>
                    <# } #>
                    <# if ( data.model.get('label_url') ) { #>
                    <tr>
                        <th><?php _e( 'Tracking number:', 'shipcloud-for-woocommerce' ); ?></th>
                        <td class="tracking-number">
                            <# if ( data.model.get('carrier_tracking_no') ) { #>
                                <a href="{{ data.model.getCarrierTrackingUrl() }}" target="_blank">
                                  {{ data.model.get('carrier_tracking_no') }}
                                </a>
                            <# } else { #>
                                <?php _e( 'Not available yet', 'shipcloud-for-woocommerce' ); ?>
                            <# } #>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Tracking status:', 'shipcloud-for-woocommerce' ); ?></th>
                        <td>
                            <# if ( data.model.get('shipment_status') ) { #>
                                {{ data.model.get('shipment_status') }}
                            <# } else { #>
                                <?php _e( 'Not available yet', 'shipcloud-for-woocommerce' ); ?>
                            <# } #>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Price:', 'shipcloud-for-woocommerce' ); ?></strong></th>
                        <td class="price">
                            <# if ( data.model.get('price') ) { #>
                                {{ data.model.get('price') }}
                            <# } else { #>
                                <?php _e( 'Not available yet', 'shipcloud-for-woocommerce' ); ?>
                            <# } #>
                        </td>
                    </tr>
                    <# } #>
            </tbody>
        </table>

        <div class="label-shipment-pickup-request">
            <# if ( data.model.get('pickup_request') ) { #>
                <strong><?php _e( 'Pickup timeframe', 'shipcloud-for-woocommerce' ); ?></strong>
                <div>
                    {{ data.model.get('pickup_request').getPickupTimeAsRange() }}
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
            <button type="button" class="shipcloud_delete_shipment button">
                <?php _e( 'Delete shipment', 'shipcloud-for-woocommerce' ); ?>
            </button>

            <# if ( data.model.get('label_url') ) { #>
                <a href="{{ data.model.get('label_url') }}" target="_blank" class="button">
                    <?php _e( 'Download label', 'shipcloud-for-woocommerce' ); ?>
                </a>

        <?php
            // only applicable for WooCommerce 3
            if (class_exists('WC_DateTime')) :
        ?>
                <# if ( !data.model.get('pickup_request') ) { #>
                    <button class="button button-primary shipcloud-open-pickup-request-form" role="switch" type="button">
                        <?php _e( 'Create pickup request', 'shipcloud-for-woocommerce' ) ?>
                    </button>
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
