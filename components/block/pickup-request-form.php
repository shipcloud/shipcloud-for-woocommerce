<table class="shipcloud-pickup-request-table" style="display: none;">
    <tbody>
        <tr
            id="shipcloud-pickup-request"
            class="inline-edit-row inline-edit-row-page inline-edit-shop_order bulk-edit-row bulk-edit-row-page bulk-edit-shop_order">
            <td colspan="10" class="colspanchange" id="shipcloud-io">
                <fieldset class="inline-edit-col-left">
                    <legend class="inline-edit-legend">
                        <?php esc_html_e( 'Create pickup request', 'shipcloud-for-woocommerce' ) ?>
                    </legend>
                    <div class="inline-edit-col">
                        <div class="order-id-list"></div>
                    </div>
                </fieldset>
                <fieldset class="inline-edit-col-right">
                    <?php
                        require WCSC_COMPONENTFOLDER . '/block/pickup-request-form-table.php';
                    ?>
                </fieldset>
                <p class="submit inline-edit-save">
                    <button type="button" class="button cancel alignleft">
                        <?php esc_html_e( 'Cancel' ) ?>
                    </button>
                    <input type="submit"
                           id="<?php esc_attr_e(WC_Shipcloud_Order_Bulk::BUTTON_PICKUP_REQUEST) ?>"
                           name="<?php esc_attr_e(WC_Shipcloud_Order_Bulk::BUTTON_PICKUP_REQUEST) ?>"
                           class="button button-primary alignright"
                           value="<?php esc_attr_e( 'Create pickup request', 'woocommerce-shipcloud' ) ?>">
                    <input type="hidden" name="screen" value="edit-<?php get_current_screen()->id ?>">
                    <span class="error" style="display:none"></span>
                    <br class="clear">
                </p>
            </td>
        </tr>
    </tbody>
</table>
