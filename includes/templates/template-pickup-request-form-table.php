<?php
    global $woocommerce;
?>

<table class="shipcloud-pickup-request-form-table parcel-form-table">
    <tbody>
		<tr class="shipcloud__pickup_date_and_time">
		    <th>
		        <?php _e( 'Pickup date and time', 'shipcloud-for-woocommerce' ); ?>
		        <?php echo wc_help_tip( __( 'If you\'re using an express carrier you might have to specify this', 'shipcloud-for-woocommerce' ) ); ?>
		    </th>
		    <td>
        		<?php include( dirname( __FILE__ ) . '/template-pickup-date-and-time.php' ); ?>
		    </td>
		</tr>
        <tr>
            <th colspan="2">
                <a class="shipcloud-use-different-pickup-address">
                    <?php _e( 'Use different pickup address', 'shipcloud-for-woocommerce' ); ?> &gt;&gt;
                </a>
            </th>
        </tr>
        <tr class="shipcloud-different-pickup-address">
            <th>
                <?php _e( 'Pickup address', 'shipcloud-for-woocommerce' ); ?>
            </th>
            <td>
                <p class="form-field pickup_address_company_field">
                    <input type="text" name="pickup_address[company]" value="">
                    <label for="pickup_address[company]"><?php _e( 'Company', 'shipcloud-for-woocommerce' ); ?></label>
                </p>
                <p class="form-field pickup_address_first_name_field">
                    <input type="text" name="pickup_address[first_name]" value="">
                    <label for="pickup_address[first_name]"><?php _e( 'First name', 'shipcloud-for-woocommerce' ); ?></label>
                </p>
                <p class="form-field pickup_address_last_name_field">
                    <input type="text" name="pickup_address[last_name]" value="">
                    <label for="pickup_address[last_name]"><?php _e( 'Last name', 'shipcloud-for-woocommerce' ); ?></label>
                </p>
                <p class="form-field pickup_address_street_field">
                    <input type="text" name="pickup_address[street]" value="">
                    <label for="pickup_address[street]"><?php _e( 'Street', 'shipcloud-for-woocommerce' ); ?></label>
                </p>
                <p class="form-field pickup_address_number_field">
                    <input type="text" name="pickup_address[street_no]" value="">
                    <label for="pickup_address[street_no]"><?php _e( 'House number', 'shipcloud-for-woocommerce' ); ?></label>
                </p>
                <p class="form-field pickup_address_zip_code_field">
                    <input type="text" name="pickup_address[zip_code]" value="">
                    <label for="pickup_address[zip_code]"><?php _e( 'Zip code', 'shipcloud-for-woocommerce' ); ?></label>
                </p>
                <p class="form-field pickup_address_city_field">
                    <input type="text" name="pickup_address[city]" value="">
                    <label for="pickup_address[city]"><?php _e( 'City', 'shipcloud-for-woocommerce' ); ?></label>
                </p>
                <p class="form-field pickup_address_country_field">
                    <select name="pickup_address[country]">
                        <?php
                            $base_country = $woocommerce->countries->get_base_country();

                            foreach ( $woocommerce->countries->countries AS $key => $country ) {
                                if ( $key === $base_country ) {
                                    echo(sprintf('<option value="%s" selected>%s</option>', $key, $country));
                                } else {
                                    echo(sprintf('<option value="%s">%s</option>', $key, $country));
                                }
                            }
                        ?>
                    </select>
                    <label for="pickup_address[country]"><?php _e( 'Country', 'shipcloud-for-woocommerce' ); ?></label>
                </p>
                <p class="form-field pickup_address_state_field">
                    <input type="text" name="pickup_address[state]" value="">
                    <label for="pickup_address[state]"><?php _e( 'State', 'shipcloud-for-woocommerce' ); ?></label>
                </p>
                <p class="form-field pickup_address_phone_field">
                    <input type="text" name="pickup_address[phone]" value="">
                    <label for="pickup_address[phone]"><?php _e( 'Phone', 'shipcloud-for-woocommerce' ); ?></label>
                </p>
            </td>
        </tr>
    </tbody>
</table>
