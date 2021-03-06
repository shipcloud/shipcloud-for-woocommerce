<?php /** @var \WooCommerce_Shipcloud_Block_Labels_Form $this */ ?>
<?php
  $options = get_option( 'woocommerce_shipcloud_settings' );
?>
<table class="parcel-form-table">
    <tbody>
    <tr>
        <th><?php _e( 'Width', 'shipcloud-for-woocommerce' ); ?></th>
        <td>
            <input type="text" name="parcel_width"
                   class="lengths"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Height', 'shipcloud-for-woocommerce' ); ?></th>
        <td>
            <input type="text" name="parcel_height"
                   class="lengths"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Length', 'shipcloud-for-woocommerce' ); ?> </th>
        <td>
            <input type="text" name="parcel_length"
                   class="lengths"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Weight', 'shipcloud-for-woocommerce' ); ?></th>
        <td>
            <input type="text" name="parcel_weight"
                   class="lengths" />
                   <?php _e( 'kg', 'shipcloud-for-woocommerce' ); ?>
            <?php
              if ( $this->get_order()->is_auto_weight_calculation_on() ) {
                $auto_weight_calculation_checkbox = 'checked="checked"';
              } else {
                $auto_weight_calculation_checkbox = '';
              }
             ?>
            <input type="checkbox" name="shipcloud_use_calculated_weight" value="use_calculated_weight" <?php echo $auto_weight_calculation_checkbox ?> />
            <?php
              $wc_order = $this->get_order()->get_wc_order();
              if( method_exists( $wc_order, 'get_order_number' ) ){
                _e( sprintf('use calculated weight (%s)', $this->get_calculated_weight()), 'shipcloud-for-woocommerce' );
              } else {
                _e( sprintf('use calculated weight', 'shipcloud-for-woocommerce' ));
              }
            ?>
        </td>
    </tr>
    <tr>
        <th>
          <?php _e( 'Higher insurance', 'shipcloud-for-woocommerce' ); ?>
          <?php echo wc_help_tip( __( 'Use this to book additional insurance or expand the liability for a shipment. Caution: Please keep in mind that additional fees are charged by the carrier', 'shipcloud-for-woocommerce' ) ); ?>
        </th>
        <td>
          <input type="text" name="declared_value"
            class="lengths"/> <?php _e( 'EUR', 'shipcloud-for-woocommerce' ); ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php _e( 'Shipping method', 'shipcloud-for-woocommerce' ); ?>
            <?php if ( $this->get_shipping_method_name() ): ?>
                <br />
                <small><?php echo sprintf( __( 'Ordered: %s', 'shipcloud-for-woocommerce' ), $this->get_shipping_method_name() ); ?></small>
            <?php endif; ?>
        </th>
        <td>
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
        </td>
    </tr>
    <tr>
        <th>
          <?php _e( 'Label format', 'shipcloud-for-woocommerce' ); ?>
          <?php echo wc_help_tip( __( 'If you don\'t specifiy a label format, the configured label format of your shipcloud account will be used', 'shipcloud-for-woocommerce' ) ); ?>
        </th>
        <td>
          <select name="shipcloud_label_format"
                  rel="shipcloud_label_format"
                  id="shipcloud_label_format"></select>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Shipment description', 'shipcloud-for-woocommerce' ); ?></th>
        <td>
            <input type="text" name="other_description" value="<?php echo esc_attr($this->get_order()->get_description()); ?>">
        </td>
    </tr>
    <tr>
      <th>
          <?php _e( 'Reference number', 'shipcloud-for-woocommerce' ); ?>
          <?php echo wc_help_tip( __( 'You can use one of the following shortcodes for making the value dynamic: [shipcloud_orderid]', 'shipcloud-for-woocommerce' ) ); ?>
      </th>
      <td>
        <input type="text"
               name="reference_number"
               value="<?php echo _wcsc_container()->get( '\\Woocommerce_Shipcloud_API' )->get_global_reference_number($this->get_order()); ?>"/>
      </td>
    </tr>
	<?php
        if ( $this->get_order() && $this->get_order()->get_wc_order() ):
    ?>
        <tr>
            <th><?php _e( 'Package description', 'shipcloud-for-woocommerce' ); ?></th>
            <td>
                <input type="text"
                       name="parcel_description"
                       value="<?php echo esc_attr( wcsc_order_get_parcel_description( $this->get_order()->get_wc_order() ) ) ?>"/>
            </td>
        </tr>
    <?php
        endif;

        // only applicable for WooCommerce 3
        if (class_exists('WC_DateTime')) {
          if ( ! array_key_exists( 'dhl_express_regular_pickup', $options ) || 'no' === $options['dhl_express_regular_pickup'] ) {
            require WCSC_COMPONENTFOLDER . '/block/pickup-date-and-time.php';
          }
        }
    ?>
        <tr>
          <th>
            <?php _e( 'shipcloud notification email', 'shipcloud-for-woocommerce' ); ?>
            <?php echo wc_help_tip( __( 'Let shipcloud update the customer about the shipping status via email', 'shipcloud-for-woocommerce' ) ); ?>
          </th>
          <td style="text-align: right;">
            <?php
              if ( $this->get_order()->wants_shipcloud_email_notification() ) {
                $notification_email_checkbox = 'checked="checked"';
              } else {
                $notification_email_checkbox = '';
              }
             ?>
            <input type="checkbox" name="shipcloud_notification_email_checkbox" <?php echo $notification_email_checkbox ?> />
            <input type="text"
                   name="shipcloud_notification_email"
                   value="<?php echo $this->get_order()->get_email_for_notification() ?>"
                   class="notification_email_input" />
          </td>
        </tr>

    <?php
      if ( get_current_screen() && 'edit-shop_order' == get_current_screen()->id ) {
    ?>
        <tr>
          <th>
            <?php _e( 'Skip when shipping label present', 'shipcloud-for-woocommerce' ); ?>
            <?php echo wc_help_tip( __( 'Activate, if you want to skip label creation for an order if there is already a shipping label present', 'shipcloud-for-woocommerce' ) ); ?>
          </th>
          <td>
            <?php
              $bulk_only_one_shipping_label = wcsc_shipping_method()->get_option( 'bulk_only_one_shipping_label' );
              if ( $bulk_only_one_shipping_label === 'yes' ) {
                $bulk_only_one_shipping_label_checkbox = 'checked="checked"';
              } else {
                $bulk_only_one_shipping_label_checkbox = '';
              }
             ?>
            <input type="checkbox" name="shipcloud_bulk_only_one_shipping_label" <?php echo $bulk_only_one_shipping_label_checkbox ?> />
          </td>
        </tr>
    <?php
      }
    ?>
    </tbody>
</table>
