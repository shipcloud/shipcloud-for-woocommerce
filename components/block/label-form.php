<?php /** @var \WooCommerce_Shipcloud_Block_Labels_Form $this */ ?>
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
            require WCSC_COMPONENTFOLDER . '/block/pickup-date-and-time.php';
        }
    ?>
    </tbody>
</table>
