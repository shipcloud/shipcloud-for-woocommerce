<?php /** @var WooCommerce_Shipcloud_Block_Order_Labels_Bulk $this */ ?>
<table style="">
    <tbody id="wcsc">

    <tr id="wcsc-order-bulk-labels"
        class="inline-edit-row inline-edit-row-page inline-edit-shop_order bulk-edit-row bulk-edit-row-page bulk-edit-shop_order"
        style="display: none">
        <td colspan="10" class="colspanchange">

            <fieldset class="inline-edit-col-left">
                <legend class="inline-edit-legend">
					<?php esc_html_e( 'Create shipping labels', 'shipcloud-for-woocommerce' ) ?>
                </legend>
                <div class="inline-edit-col">
                    <div class="bulk-title-div">
                        <div class="bulk-titles"></div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="inline-edit-col-right">
                <div class="inline-edit-col">
                    <div class="inline-edit-group wp-clearfix" id="shipcloud_bulk">
                            <span style="display: inline-block; width: 9em; line-height: 1.3em; font-size: 14px; padding: 8px 0 42px 10px">
                                <?php esc_html_e( 'Template', 'shipcloud-for-woocommerce' ) ?>
                            </span>
							<?php if ( count( $this->get_parcel_templates() ) > 0 ) : ?>
                                <select name="parcel_list">
                                    <option value="none"><?php _e( '[ Select a parcel ]', 'shipcloud-for-woocommerce' ); ?></option>

									<?php foreach ( $this->get_parcel_templates() AS $parcel_template ): ?>
                                        <option value="<?php echo $parcel_template['value']; ?>"
											<?php foreach ( $parcel_template['data'] as $field => $value ): ?>
                                                data-<?php echo $field ?>="<?php esc_attr_e( $value ) ?>"
											<?php endforeach; ?>
                                        >
											<?php echo $parcel_template['option']; ?>
                                        </option>
									<?php endforeach; ?>
                                </select>
							<?php else: ?>
                                <p><?php echo sprintf( __( 'Please <a href="%s">add parcel templates</a> if you want to use.', 'shipcloud-for-woocommerce' ), admin_url( 'edit.php?post_type=sc_parcel_template' ) ); ?></p>
							<?php endif; ?>

							<?php echo $this->label_form->render() ?>
                    </div>
                </div>
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

<script type="template/html" id="tmpl-wcsc-order-labels-bulk-items">
    <div data-id="{{ data.id }}"
         class="bulk-title">
        {{ data.title }}
    </div>
</script>
