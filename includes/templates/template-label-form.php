<?php
	$options = get_option( 'woocommerce_shipcloud_settings' );
?>

<div class="parcel-form-table">
    <div class="parcel-form-section parcel-dimensions">
        <div>
            <label for="parcel_width"><?php _e( 'Width', 'shipcloud-for-woocommerce' ); ?></label>
            <input type="text" name="parcel_width" class="lengths"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
        </div>
        <div>
            <label for="parcel_height"><?php _e( 'Height', 'shipcloud-for-woocommerce' ); ?></label>
            <input type="text" name="parcel_height" class="lengths"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
        </div>
        <div>
            <label for="parcel_length"><?php _e( 'Length', 'shipcloud-for-woocommerce' ); ?> </label>
            <input type="text" name="parcel_length" class="lengths"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
        </div>
    </div>
    <div class="parcel-form-section parcel-weight">
        <label for="parcel_weight"><?php _e( 'Weight', 'shipcloud-for-woocommerce' ); ?></label>
        <input type="text" name="parcel_weight" class="lengths" />
			<?php _e( 'kg', 'shipcloud-for-woocommerce' ); ?>
            <?php 
			if ( $this->is_auto_weight_calculation_on() ) {
				$auto_weight_calculation_checkbox = 'checked="checked"';
			} else {
                $auto_weight_calculation_checkbox = '';
			} 
			?>
            <input type="checkbox" name="shipcloud_use_calculated_weight" value="use_calculated_weight" <?php echo $auto_weight_calculation_checkbox ?> />
            <?php
			$wc_order = $this->get_wc_order();
			if ( $wc_order && method_exists( $wc_order, 'get_order_number' ) ) {
				_e( sprintf('use calculated weight (%s)', $this->get_calculated_weight()), 'shipcloud-for-woocommerce' );
			} else {
				_e( sprintf('use calculated weight', 'shipcloud-for-woocommerce' ));
			}
			?>
    </div>
    <div class="parcel-form-section parcel-insurance">
        <label for="declared_value">
            <?php _e( 'Higher insurance', 'shipcloud-for-woocommerce' ); ?>
            <?php echo wc_help_tip( __( 'Use this to book additional insurance or expand the liability for a shipment. Caution: Please keep in mind that additional fees are charged by the carrier', 'shipcloud-for-woocommerce' ) ); ?>
        </label>
        <input type="text" name="declared_value" class="lengths"/> <?php echo get_woocommerce_currency(); ?>
    </div>
    <div class="parcel-form-section parcel-shippingmethod">
        <label for="">
            <?php _e( 'Shipping method', 'shipcloud-for-woocommerce' ); ?>
            <?php if ( $this->get_shipping_method_name() ) : ?>
                <small><?php echo sprintf( __( 'Ordered: %s', 'shipcloud-for-woocommerce' ), $this->get_shipping_method_name() ); ?></small>
            <?php endif; ?>
        </label>
        <?php if ( count( $this->get_allowed_carriers() ) > 0 ): ?>
            <div id="shipcloud_csp_wrapper" class="shipcloud-carrier-select">
                <select name="shipcloud_carrier" id="shipcloud_carrier" rel="shipcloud_carrier"></select>

                <select name="shipcloud_carrier_service"
                        rel="shipcloud_carrier_service"
                        id="shipcloud_carrier_service"></select>

                <select name="shipcloud_carrier_package"
                        rel="shipcloud_carrier_package"
                        id="shipcloud_carrier_package"></select>
            </div>
        <?php else: ?>
            <?php echo sprintf(
                __( '<a href="%s">Please select a carrier</a>.', 'shipcloud-for-woocommerce' ),
                admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' )
            ); ?>
        <?php endif; ?>
    </div>
    <div class="parcel-form-section parcel-labelformat">
        <label for="shipcloud_label_format">
            <?php _e( 'Label format', 'shipcloud-for-woocommerce' ); ?>
            <?php echo wc_help_tip( __( 'If you don\'t specifiy a label format, the configured label format of your shipcloud account will be used', 'shipcloud-for-woocommerce' ) ); ?>
        </label>
        <select name="shipcloud_label_format" rel="shipcloud_label_format" id="shipcloud_label_format"></select>
    </div>
    <div class="parcel-form-section parcel-shipment-description">
        <label for="other_description"><?php _e( 'Shipment description', 'shipcloud-for-woocommerce' ); ?></label>
        <input type="text" name="other_description" value="<?php echo esc_attr($this->get_description()); ?>">
    </div>
    <div class="parcel-form-section parcel-reference-number">
		<label for="reference_number">
            <?php _e( 'Reference number', 'shipcloud-for-woocommerce' ); ?>
            <?php echo wc_help_tip( __( 'You can use one of the following shortcodes for making the value dynamic: [shipcloud_orderid]', 'shipcloud-for-woocommerce' ) ); ?>
        </label>
		<input type="text" name="reference_number" value="<?php echo $this->get_global_reference_number(); ?>"/>
	</div>
    <?php if ( $this && $this->get_wc_order() ) : ?>
        <div class="parcel-form-section parcel-description">
            <label for="parcel_description"><?php _e( 'Package description', 'shipcloud-for-woocommerce' ); ?></label>
            <input type="text" name="parcel_description" value="<?php echo esc_attr( WC_Shipping_Shipcloud_Utils::get_parcel_description( $this->get_wc_order() ) ) ?>"/>
        </div>
	<?php endif; ?>
  <?php 
    if ( 
      ( method_exists( $this, 'is_bulk_action' ) && $this->is_bulk_action() && WC_Shipping_Shipcloud_Utils::shipcloud_email_notification_enabled() ) ||
      ( $this->shipcloud_email_notification_enabled() )
    ) : 
  ?>
      <div class="parcel-form-section parcel-notification-email">
        <input type="checkbox" name="shipcloud_notification_email_checkbox" checked="checked" />
        <label for="shipcloud_notification_email"><?php _e( 'Notification email', 'shipcloud-for-woocommerce' ); ?></label>
        <input type="text" name="shipcloud_notification_email" value="<?php echo $this->get_email_for_notification() ?>" />
      </div>
	<?php endif; ?>
    <?php if ( get_current_screen() && 'edit-shop_order' == get_current_screen()->id ) : ?>
        <div class="parcel-form-section parcel-shipping-label-present">
            <label for="shipcloud_bulk_only_one_shipping_label"><?php _e( 'Skip when shipping label present', 'shipcloud-for-woocommerce' ); ?></label>
            <?php echo wc_help_tip( __( 'Activate, if you want to skip label creation for an order if there is already a shipping label present', 'shipcloud-for-woocommerce' ) ); ?>
                <?php
                $bulk_only_one_shipping_label = WC_Shipping_Shipcloud_Utils::get_option( 'bulk_only_one_shipping_label' );
                if ( $bulk_only_one_shipping_label === 'yes' ) {
                    $bulk_only_one_shipping_label_checkbox = 'checked="checked"';
                } else {
                    $bulk_only_one_shipping_label_checkbox = '';
                }
                ?>
                <input type="checkbox" name="shipcloud_bulk_only_one_shipping_label" <?php echo $bulk_only_one_shipping_label_checkbox ?> />
        </div>
	<?php endif; ?>
</div>
