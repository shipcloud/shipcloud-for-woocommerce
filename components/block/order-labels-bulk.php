<?php /** @var WooCommerce_Shipcloud_Block_Order_Labels_Bulk $this */ ?>
<table style="">
    <tbody id="wcsc">

    <tr id="wcsc-order-bulk-labels"
        class="inline-edit-row inline-edit-row-page inline-edit-shop_order bulk-edit-row bulk-edit-row-page bulk-edit-shop_order"
        style="display: none">
        <td colspan="10" class="colspanchange" id="shipcloud-io">

            <fieldset class="inline-edit-col-left">
                <legend class="inline-edit-legend">
					<?php esc_html_e( 'Create shipping labels', 'shipcloud-for-woocommerce' ) ?>
                </legend>
                <div class="inline-edit-col">
                    <div class="bulk-title-div">
                        <div class="order-id-list"></div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="inline-edit-col-right">
              <div class="create-label fifty">
                <?php echo $this->parcel_templates(); ?>
                <?php echo $this->parcel_form(); ?>
              </div>

              <div class="additional_services fifty">
                <script type="template/html" id="tmpl-shipcloud-shipment-additional-services">
                  <?php require WCSC_COMPONENTFOLDER . '/block/additional-services-edit-form.php'; ?>
                </script>
                <script type="application/javascript">
                  jQuery(function ($) {
                    shipcloud.additionalServices = new shipcloud.ShipmentAdditionalServicesView({
                      model: new shipcloud.ShipmentModel(),
                      el   : '#wcsc-order-bulk-labels .additional_services'
                    });
                    shipcloud.additionalServices.render();

                <?php
                    $cod_data = array(
                        'currency'            => 'EUR',
                        'bank_account_holder' => $this->get_options('bank_account_holder'),
                        'bank_name'           => $this->get_options('bank_name'),
                        'bank_account_number' => $this->get_options('bank_account_number'),
                        'bank_code'           => $this->get_options('bank_code')
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

              <div class="clear"></div>
            </fieldset>

            <p class="submit inline-edit-save">
                <button type="button" class="button cancel alignleft">
					<?php esc_html_e( 'Cancel' ) ?>
                </button>
                <input type="submit"
                       id="<?php esc_attr_e(WC_Shipcloud_Order_Bulk::BUTTON_PDF) ?>"
                       name="<?php esc_attr_e(WC_Shipcloud_Order_Bulk::BUTTON_PDF) ?>"
                       class="button button-primary alignright"
                       value="<?php esc_attr_e( 'Create labels', 'woocommerce-shipcloud' ) ?>">
                <input type="hidden" name="screen" value="edit-<?php get_current_screen()->id ?>">
                <span class="error" style="display:none"></span>
                <br class="clear">
            </p>
        </td>
    </tr>
    </tbody>
</table>

<script type="application/javascript">
    jQuery(function ($) {
        $('#shipcloud_bulk').find('#shipcloud_csp_wrapper').shipcloudMultiSelect(wcsc_carrier);
        $('select[name="parcel_list"]').shipcloudFiller('table.parcel-form-table');
    });
</script>
