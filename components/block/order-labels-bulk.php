<?php /** @var WooCommerce_Shipcloud_Block_Order_Labels_Bulk $this */ ?>
<table style="">
    <tbody id="wcsc">

    <tr id="wcsc-order-bulk-labels"
        class="inline-edit-row inline-edit-row-page inline-edit-shop_order bulk-edit-row bulk-edit-row-page bulk-edit-shop_order"
        style="display: none">
        <td colspan="10" class="colspanchange">

            <fieldset class="inline-edit-col-left">
                <legend class="inline-edit-legend">
					<?php esc_html_e( 'Create shipping labels', 'woocommerce-shipcloud' ) ?>
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
                        <label class="alignleft">
                            <span class="title">
                                <?php esc_html_e( 'Template', 'woocommerce-shipcloud' ) ?>
                            </span>
							<?php if ( wcsc_get_parceltemplates() ): ?>
                                <select name="wcsc_template" id="wcsc_template">
                                    <option value="">
										<?php esc_html_e( '(choose from templates here)' ) ?>
                                    </option>
									<?php foreach ( wcsc_get_parceltemplates() as $service_id => $template ): ?>
                                        <option value="<?php echo esc_attr( $service_id ) ?>"
                                                data-carrier="<?php esc_attr_e( $template['values']['carrier'] ) ?>"
                                                data-width="<?php esc_attr_e( $template['values']['width'] ) ?>"
                                                data-height="<?php esc_attr_e( $template['values']['height'] ) ?>"
                                                data-length="<?php esc_attr_e( $template['values']['length'] ) ?>"
                                                data-weight="<?php esc_attr_e( $template['values']['weight'] ) ?>"
                                        >
											<?php echo esc_html( $template['post_title'] ) ?>
                                        </option>
									<?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <a href="<?php echo WCSC_Parceltemplate_Posttype::get_create_link() ?>"
                                   target="_blank">
                                    <?php esc_html_e('Create new parcel template') ?>
                                </a>
							<?php endif; ?>
                        </label>

                        <div class="inline-edit-group wp-clearfix">
                            <label class="alignleft">
                                <span class="title">
                                    <?php esc_html_e( 'Width', 'woocommerce-shipcloud' ) ?>
                                </span>
                                <input type="text" name="wcsc_width"/>
                                <?php echo get_option('woocommerce_dimension_unit') ?>
                            </label>
                        </div>

                        <div class="inline-edit-group wp-clearfix">
                            <label class="alignleft">
                                <span class="title">
                                    <?php esc_html_e( 'Height', 'woocommerce-shipcloud' ) ?>
                                </span>
                                <input type="text" name="wcsc_height"/>
	                            <?php echo get_option('woocommerce_dimension_unit') ?>
                            </label>
                        </div>

                        <div class="inline-edit-group wp-clearfix">
                            <label class="alignleft">
                                <span class="title">
                                    <?php esc_html_e( 'Length', 'woocommerce-shipcloud' ) ?>
                                </span>
                                <input type="text" name="wcsc_length"/>
	                            <?php echo get_option('woocommerce_dimension_unit') ?>
                            </label>
                        </div>

                        <div class="inline-edit-group wp-clearfix">
                            <label class="alignleft">
                                <span class="title">
                                    <?php esc_html_e( 'Weight', 'woocommerce-shipcloud' ) ?>
                                </span>
                                <input type="text" name="wcsc_weight"/>
	                            <?php echo get_option('woocommerce_weight_unit') ?>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="inline-edit-col">

                    <div class="inline-edit-group wp-clearfix">
                        <label class="alignleft">
                            <span class="title">Carrier</span>
                            <select name="wcsc_carrier">
								<?php foreach ( wcsc_api()->get_carriers() as $carrier ): ?>
                                    <option value="<?php echo esc_attr( $carrier['name'] ) ?>">
										<?php echo esc_html( $carrier['display_name'] ) ?>
                                    </option>
								<?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                </div>
            </fieldset>

            <p class="submit inline-edit-save">
                <button type="button" class="button cancel alignleft">
					<?php esc_html_e( 'Cancel' ) ?>
                </button>
                <input type="submit"
                       id="bulk_edit"
                       name="bulk_edit"
                       class="button button-primary alignright"
                       value="<?php esc_attr_e( 'Create labels', 'woocommerce-shipcloud' ) ?>">
                <input type="submit"
                       id="<?php esc_attr_e(WC_Shipcloud_Order_Bulk::BUTTON_PDF) ?>"
                       name="<?php esc_attr_e(WC_Shipcloud_Order_Bulk::BUTTON_PDF) ?>"
                       class="button button-default alignright"
                       value="<?php esc_attr_e( 'Create PDF', 'woocommerce-shipcloud' ) ?>">
                <input type="hidden" name="screen" value="edit-<?php get_current_screen()->id ?>">
                <span class="error" style="display:none"></span>
                <br class="clear">
            </p>
        </td>
    </tr>
    </tbody>
</table>

<script type="template/html" id="tmpl-wcsc-order-labels-bulk-items">
    <div data-id="{{ data.id }}"
         class="bulk-title">
        {{ data.title }}
    </div>
</script>
