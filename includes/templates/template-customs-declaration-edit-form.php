<?php
	
?>
<div class="fifty customs-declaration--definition">
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
                    <# if ( data.model.get('customs_declaration') && data.model.get('customs_declaration').contents_explanation ) { #>
                        <input type="text" name="customs_declaration[contents_explanation]" maxlength="256" value="{{ data.model.get('customs_declaration').contents_explanation }}" />
                    <# } else { #>
                        <input type="text" name="customs_declaration[contents_explanation]" maxlength="256" />
                    <# } #>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Currency', 'shipcloud-for-woocommerce' ); ?>
                </th>
                <td>
                    <input type="text" name="customs_declaration[currency]" value="<?php echo get_woocommerce_currency(); ?>" disabled />
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Additional fees', 'shipcloud-for-woocommerce' ); ?>
                </th>
                <td>
                    <# if ( data.model.get('customs_declaration') && data.model.get('customs_declaration').additional_fees ) { #>
                        <input type="text" name="customs_declaration[additional_fees]" value="{{ data.model.get('customs_declaration').additional_fees }}" />
                    <# } else { #>
                        <input type="text" name="customs_declaration[additional_fees]" />
                    <# } #>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Drop off location', 'shipcloud-for-woocommerce' ); ?>
                    <?php echo wc_help_tip( __( 'Location where the package will be dropped of with the carrier', 'shipcloud-for-woocommerce' ) ); ?>
                </th>
                <td>
                    <# if ( data.model.get('customs_declaration') && data.model.get('customs_declaration').drop_off_location ) { #>
                        <input type="text" name="customs_declaration[drop_off_location]" value="{{ data.model.get('customs_declaration').drop_off_location }}" />
                    <# } else { #>
                        <input type="text" name="customs_declaration[drop_off_location]" />
                    <# } #>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Exporter reference', 'shipcloud-for-woocommerce' ); ?>
                </th>
                <td>
                    <# if ( data.model.get('customs_declaration') && data.model.get('customs_declaration').exporter_reference ) { #>
                        <input type="text" name="customs_declaration[exporter_reference]" value="{{ data.model.get('customs_declaration').exporter_reference }}" />
                    <# } else { #>
                        <input type="text" name="customs_declaration[exporter_reference]" />
                    <# } #>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Importer reference', 'shipcloud-for-woocommerce' ); ?>
                </th>
                <td>
                    <# if ( data.model.get('customs_declaration') && data.model.get('customs_declaration').importer_reference ) { #>
                        <input type="text" name="customs_declaration[importer_reference]" value="{{ data.model.get('customs_declaration').importer_reference }}" />
                    <# } else { #>
                        <input type="text" name="customs_declaration[importer_reference]" />
                    <# } #>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Posting date', 'shipcloud-for-woocommerce' ); ?>
                </th>
                <td>
                    <# if ( data.model.get('customs_declaration') && data.model.get('customs_declaration').posting_date ) { #>
                        <input type="text" name="customs_declaration[posting_date]" value="{{ data.model.get('customs_declaration').posting_date }}" />
                    <# } else { #>
                        <input type="text" name="customs_declaration[posting_date]" />
                    <# } #>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e( 'Invoice number', 'shipcloud-for-woocommerce' ); ?>
                </th>
                <td>
                    <# if ( data.model.get('customs_declaration') && data.model.get('customs_declaration').invoice_number ) { #>
                        <input type="text" name="customs_declaration[invoice_number]" value="{{ data.model.get('customs_declaration').invoice_number }}" />
                    <# } else { #>
                        <input type="text" name="customs_declaration[invoice_number]" value="<?php echo $this->get_global_reference_number(); ?>" />
                    <# } #>
                </td>
            </tr>
			<?php
			$order = wc_get_order( $this->order_id );
			?>
            <tr>
                <th>
                    <?php _e( 'Total value amount', 'shipcloud-for-woocommerce' ); ?>
                </th>
                <td>
                    <# if ( data.model.get('customs_declaration') && data.model.get('customs_declaration').total_value_amount ) { #>
                        <input type="number" min="0" max="1000" step="0.01" name="customs_declaration[total_value_amount]" value="{{ data.model.get('customs_declaration').total_value_amount }}" />
                    <# } else { #>
                        <input type="number" min="0" max="1000" step="0.01" name="customs_declaration[total_value_amount]" value="<?php echo $order->get_total() - $order->get_shipping_total(); ?>" />
                    <# } #>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="fifty customs-declaration--items">
    <div class="customs-declaration--items_content">
        <h4>
            <?php _e( 'Items', 'shipcloud-for-woocommerce' ) ?>
        </h4>

        <#
            if ( data.model.get('customs_declaration') && data.model.get('customs_declaration').items ) {
                _.each(data.model.get('customs_declaration').items, function(item) {
        #>

            <fieldset>
                <table class="parcel-form-table">
                    <tbody>
                        <tr>
                            <th>
                                <?php _e( 'Description', 'shipcloud-for-woocommerce' ); ?>
                            </th>
                            <td>
                                <input type="text" name="customs_declaration[items][{{ item.id }}][description]" value="{{ item.description }}" />
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'Origin country', 'shipcloud-for-woocommerce' ); ?>
                            </th>
                            <td>
                                <select name="customs_declaration[items][{{ item.id }}][origin_country]">
                                    <?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
                                        <option value="<?php esc_attr_e( $key ); ?>">
                                            <?php echo $country; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'Quantity', 'shipcloud-for-woocommerce' ); ?>
                            </th>
                            <td>
                                <input type="number" name="customs_declaration[items][{{ item.id }}][quantity]" value="{{ item.quantity }}" step="1" />
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'Value amount', 'shipcloud-for-woocommerce' ); ?>
                            </th>
                            <td>
                                <input type="number" name="customs_declaration[items][{{ item.id }}][value_amount]" value="{{ item.value_amount }}" />
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'Net weight', 'shipcloud-for-woocommerce' ); ?>
                            </th>
                            <td>
                                <input type="number" name="customs_declaration[items][{{ item.id }}][net_weight]" value="{{ item.net_weight }}" step="0.01" />
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'HS tariff number', 'shipcloud-for-woocommerce' ); ?>
                                <?php echo wc_help_tip( __( 'Harmonized System Tariff Number', 'shipcloud-for-woocommerce' ) ); ?>
                            </th>
                            <td>
                                <input type="text" name="customs_declaration[items][{{ item.id }}][hs_tariff_number]" value="{{ item.hs_tariff_number }}" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
        <#
                });
            } else {
        #>
            <?php
              $order_data = $this->get_wc_order()->get_data();
              foreach ( $order_data['line_items'] as $line_item_id => $line_item ) {
				  // $this->log('customs-declaration-edit-form');
				  // $this->log('Trying to get product from line item');
				  // $this->log('line_item: '.print_r($line_item, true));
				  $product = $line_item->get_product();
				  // $this->log('product: '.print_r($product, true));
				  if (!$product) {
					  $this->log('no product found so continue with next item');
					  continue;
				  }
            ?>
            <fieldset>
                <table class="parcel-form-table">
                    <tbody>
                        <tr>
                            <th>
                                <?php _e( 'Description', 'shipcloud-for-woocommerce' ); ?>
                            </th>
                            <td>
                                <input type="text" name="customs_declaration[items][<?php echo $line_item_id; ?>][description]" value="<?php echo $product->get_title(); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'Origin country', 'shipcloud-for-woocommerce' ); ?>
                            </th>
                            <td>
                                <select name="customs_declaration[items][<?php echo $line_item_id; ?>][origin_country]">
                                    <option value=""></option>
                                    <?php foreach ( $woocommerce->countries->countries AS $key => $country ): ?>
                                        <option value="<?php esc_attr_e( $key ); ?>"
                                            <?php selected( $key === get_post_meta( $product->get_id(), 'shipcloud_origin_country', true ) ); ?>>
                                            <?php echo $country; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'Quantity', 'shipcloud-for-woocommerce' ); ?>
                            </th>
                            <td>
                                <input type="number" name="customs_declaration[items][<?php echo $line_item_id; ?>][quantity]" value="<?php echo $line_item->get_quantity(); ?>" step="1" />
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'Value amount', 'shipcloud-for-woocommerce' ); ?>
                            </th>
                            <td>
                                <input type="number" name="customs_declaration[items][<?php echo $line_item_id; ?>][value_amount]" value="<?php echo $line_item->get_total(); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'Net weight', 'shipcloud-for-woocommerce' ); ?>
                            </th>
                            <td>
                                <input type="number" name="customs_declaration[items][<?php echo $line_item_id; ?>][net_weight]" value="<?php if( $product->has_weight() ) { echo $product->get_weight(); } ?>" step="0.01" />
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e( 'HS tariff number', 'shipcloud-for-woocommerce' ); ?>
                                <?php echo wc_help_tip( __( 'Harmonized System Tariff Number', 'shipcloud-for-woocommerce' ) ); ?>
                            </th>
                            <td>
                                <input type="text" name="customs_declaration[items][<?php echo $line_item_id; ?>][hs_tariff_number]" value="<?php echo get_post_meta( $product->get_id(), 'shipcloud_hs_tariff_number', true );?>" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <?php
                }
            ?>
        <# } #>
    </div>
</div>
