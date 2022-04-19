<?php /** @var WooCommerce_Shipcloud_Block_Order_Labels_Bulk $this */ ?>
<table style="">
    <tbody id="wcsc">

    <tr id="wcsc-order-bulk-labels"
        class="inline-edit-row inline-edit-row-page inline-edit-shop_order bulk-edit-row bulk-edit-row-page bulk-edit-shop_order"
        style="display: none">
        <td colspan="10" class="colspanchange" id="shipcloud-io">

            <h2>
                <?php esc_html_e( 'Create shipping labels', 'shipcloud-for-woocommerce' ) ?>
            </h2>
            <div class="inline-edit-col">
                <div class="bulk-title-div">
                    <div class="order-id-list"></div>
                </div>
            </div>

            <fieldset class="inline-edit-col-right parcels parcels-edit-bulk">
                <div class="parcels__columns-wrapper">
              <div class="create-label">
                <?php echo $this->parcel_templates_html(); ?>
                <?php echo $this->parcel_form_html(); ?>
                <div class="customs_declaration_button">
                    <button id="shipcloud_add_customs_declaration_bulk" type="button" value="<?php _e( 'Add customs declaration', 'shipcloud-for-woocommerce' ); ?>" class="button">
                        <?php _e( 'Add customs declaration', 'shipcloud-for-woocommerce' ); ?>
                    </button>
                </div>

                <?php
                    global $woocommerce;
                 ?>
                <div class="customs-declaration--definition" style="display: none">
                    <input type="hidden" name="customs_declaration[shown]" value="false" />
                    <div class="customs-declaration__infotext">
                        <?php _e( 'For detailed information about shipping to dutiable countries, please refer to this <a href="https://marketing-files.shipcloud.io/de/support/zolldeklaration-in-shipcloud.pdf" target="_blank" rel="noopener noreferrer">documentation</a>', 'shipcloud-for-woocommerce' ); ?>
                    </div>
                    <table class="parcel-form-table">
                        <tbody>
                            <tr>
                                <th>
                                    <?php _e( 'Contents type', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>
                                    <select name="customs_declaration[contents_type]">
                                        <option value="none"><?php _e( '[ Select a contents type ]', 'shipcloud-for-woocommerce' ); ?></option>
                                        <?php
                                            $contents_types = WC_Shipping_Shipcloud_Utils::get_customs_declaration_contents_types();
                                            foreach ( $contents_types as $key => $display_name ) {
                                                echo '<option value="'.$key.'">'.$display_name.'</option>';
                                            }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Contents explanation', 'shipcloud-for-woocommerce' ); ?>
                                    <?php echo wc_help_tip( __( 'Description of contents. Mandatory if contents_type is \'commercial_goods\'. Max 256 characters, when using DHL as your carrier', 'shipcloud-for-woocommerce' ) ); ?>
                                </th>
                                <td>
                                    <input type="text" name="customs_declaration[contents_explanation]" maxlength="256" />
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Currency', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>
                                    <input type="text" name="customs_declaration[currency]" value="EUR" disabled />
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Additional fees', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>
                                    <input type="text" name="customs_declaration[additional_fees]" />
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Drop off location', 'shipcloud-for-woocommerce' ); ?>
                                    <?php echo wc_help_tip( __( 'Location where the package will be dropped of with the carrier', 'shipcloud-for-woocommerce' ) ); ?>
                                </th>
                                <td>
                                    <input type="text" name="customs_declaration[drop_off_location]" />
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Exporter reference', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>
                                    <input type="text" name="customs_declaration[exporter_reference]" />
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Importer reference', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>
                                    <input type="text" name="customs_declaration[importer_reference]" />
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Posting date', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>
                                    <input type="text" name="customs_declaration[posting_date]" />
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e( 'Invoice number', 'shipcloud-for-woocommerce' ); ?>
                                </th>
                                <td>
                                    <input type="text" name="customs_declaration[invoice_number]" value="<?php echo $this->get_global_reference_number(); ?>" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
              </div>

              <?php
                  $carrier_email = $this->get_option( 'carrier_email' );
              ?>
              <input type="hidden" name="wants_carrier_email_notification" value="<?php echo ($carrier_email && ('yes' == $carrier_email) ? 'true' : 'false') ?>" />

              <div class="additional_services">
                <script type="template/html" id="tmpl-shipcloud-shipment-additional-services">
					<?php include( dirname( __FILE__ ) . '/template-additional-services-edit-form.php' ); ?>
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

                    // set placeholders for bulk actions
                    $("input[name='shipment[additional_services][advance_notice][email]']").prop(
                      "placeholder", "<?php _e( 'Leave empty to use email address from order', 'shipcloud-for-woocommerce' ); ?>"
                    );
                    $("input[name='shipment[additional_services][advance_notice][phone]']").prop(
                      "placeholder", "<?php _e( 'Leave empty to use phone number from order', 'shipcloud-for-woocommerce' ); ?>"
                    );
                    $("input[name='shipment[additional_services][advance_notice][sms]']").prop(
                      "placeholder", "<?php _e( 'Leave empty to use phone number from order', 'shipcloud-for-woocommerce' ); ?>"
                    );
                    $("input[name='shipcloud_notification_email']").prop(
                      "placeholder", "<?php _e( 'Leave empty to use email address from order', 'shipcloud-for-woocommerce' ); ?>"
                    );

                  });
                </script>
              </div>

                </div>
            </fieldset>

            <p class="submit inline-edit-save">
                <button type="button" class="button cancel alignleft">
					<?php esc_html_e( 'Cancel' ) ?>
                </button>
                <input type="submit"
                       id="wscs_order_bulk_pdf"
                       name="wscs_order_bulk_pdf"
                       class="button button-primary alignright"
                       value="<?php esc_attr_e( 'Create labels', 'shipcloud-for-woocommerce' ) ?>">
                <input type="hidden" name="screen" value="edit-<?php get_current_screen()->id ?>">
                <span class="error" style="display:none"></span>
                <br class="clear">
            </p>
        </td>
    </tr>
    </tbody>
</table>

<script type="application/javascript">
    jQuery(document).ready(function($){
        $('#shipcloud_bulk').find('#shipcloud_csp_wrapper').shipcloudMultiSelect(wcsc_carrier);
        $('select[name="parcel_list"]').shipcloudFiller('div.parcel-form-table');
		
		$('#doaction,#doaction2').on('click',function(evt){
			var selector = $('#bulk-action-selector-top');
			if(undefined != selector){
				if(selector.val() == 'wcsc_order_bulk_label'){
					$('select[name="parcel_list"]').trigger('change');
				}
			}
		});
		
	});
</script>
