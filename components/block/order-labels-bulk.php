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
                    <div class="inline-edit-group wp-clearfix">
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
        $('#shipcloud_csp_wrapper').shipcloudMultiSelect();
    });
</script>

<script type="template/html" id="tmpl-wcsc-order-labels-bulk-items">
    <div data-id="{{ data.id }}"
         class="bulk-title">
        {{ data.title }}
    </div>
</script>
