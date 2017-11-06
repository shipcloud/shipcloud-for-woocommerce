<div class="loading-overlay">
    <div class="spin-loader"></div>
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
            <div>{{ data.model.get('from').get('company') }}</div>
            <div>{{ data.model.get('from').getFullName() }}</div>
            <div>{{ data.model.get('from').getFullStreet() }}</div>
            <div>{{ data.model.get('from').get('care_of') }}</div>
            <div>{{ data.model.get('from').getFullCity() }}</div>
            <div>{{ data.model.get('from').get('state') }}</div>
            <div>{{ data.model.get('from').get('country') }}</div>
        </div>
        <div class="label-shipment-recipient address" role="switch">
            <div>{{ data.model.get('to').get('company') }}</div>
            <div>{{ data.model.get('to').getFullName() }}</div>
            <div>{{ data.model.get('to').getFullStreet() }}</div>
            <div>{{ data.model.get('to').get('care_of') }}</div>
            <div>{{ data.model.get('to').getFullCity() }}</div>
            <div>{{ data.model.get('to').get('state') }}</div>
            <div>{{ data.model.get('to').get('country') }}</div>
        </div>

        <div class="label-shipment-actions">

            <# if ( data.model.get('label_url') ) { #>
                <a href="{{ data.model.get('label_url') }}" target="_blank" class="button">
					<?php _e( 'Download label', 'shipcloud-for-woocommerce' ); ?>
                </a>
                <# } else { #>
                    <button class="shipcloud_create_label button-primary" type="button">
						<?php _e( 'Create label', 'shipcloud-for-woocommerce' ); ?>
                    </button>
                <# } #>

                <# if ( data.model.get('tracking_url') ) { #>
                <a href="{{ data.model.get('tracking_url') }}" target="_blank" class="button">
                    <?php _e( 'Tracking link', 'shipcloud-for-woocommerce' ); ?>
                </a>
                <# } #>

                <button class="button wcsc-save-shipment button-primary" role="switch" type="button"
                        style="display: none;">
                    <?php _ex( 'Save', 'Order: Backend button to edit prepared labels', 'wcsc' ) ?>
                </button>

                <# if ( ! data.model.get('label_url') ) { #>
                <button class="button wcsc-edit-shipment" role="switch" type="button">
                    <?php _ex( 'Edit shipment', 'Order: Backend button to edit prepared labels', 'wcsc' ) ?>
                </button>
                <# } #>

                <button type="button" class="shipcloud_delete_shipment button">
                    <?php _e( 'Delete shipment', 'shipcloud-for-woocommerce' ); ?>
                </button>

                <input type="hidden" name="carrier" value="{{ data.model.get('carrier') }}"/>
                <input type="hidden" name="shipment_id" value="{{ data.model.get('id') }}"/>
                <input type="hidden" name="shipment_order_id" value="<?php echo get_the_ID(); ?>"/>
        </div>

        <table class="label-shipment-status">
            <tbody>
            <# if ( data.model.get('description') ) { #>
                <tr>
                    <th><?php _e( 'Shipment description', 'shipcloud-for-woocommerce' ); ?>:</th>
                    <td>{{ data.model.get('description') }}</td>
                </tr>
                <# } #>

                    <tr>
                        <th><?php _e( 'Shipment id:', 'shipcloud-for-woocommerce' ); ?></th>
                        <td>DISPLAY ID!!!</td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Tracking number:', 'shipcloud-for-woocommerce' ); ?></th>
                        <td class="tracking-number">
                            <# if ( data.model.get('carrier_tracking_no') ) { #>
                                {{ data.model.get('carrier_tracking_no') }}
                                <# } else { #>
									<?php _e( 'Not available yet', 'shipcloud-for-woocommerce' ); ?>
                                    <# } #>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Tracking status:', 'shipcloud-for-woocommerce' ); ?></th>
                        <td>SHIPMENT STATUS!!!</td>
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
            </tbody>
        </table>

    </div>
</div>
