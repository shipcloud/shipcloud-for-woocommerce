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
                   class="lengths"/> <?php _e( 'kg', 'shipcloud-for-woocommerce' ); ?>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Shipping method', 'shipcloud-for-woocommerce' ); ?></th>
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

			<?php if ( $this->get_shipping_method_name() ): ?>
                <br/>
                <small><?php echo sprintf( __( 'Ordered: %s', 'shipcloud-for-woocommerce' ), $this->get_shipping_method_name() ); ?></small>
			<?php endif; ?>

        </td>
    </tr>
	<?php if ( $this->get_order() && $this->get_order()->get_wc_order() ): ?>
        <tr>
            <th><?php _e( 'Package description', 'shipcloud-for-woocommerce' ); ?></th>
            <td>
                <input type="text"
                       name="parcel_description"
                       value="<?php echo esc_attr( wcsc_order_get_parcel_description( $this->get_order()->get_wc_order() ) ) ?>"/>
                <small><?php echo sprintf( __( 'Required for carriers: %s', 'shipcloud-for-woocommerce' ), 'DPD' ); ?></small>
            </td>
        </tr>
	<?php endif; ?>
    </tbody>
</table>
