<tr class="shipcloud__pickup_date_and_time">
    <th>
        <?php _e( 'Pickup date and time', 'shipcloud-for-woocommerce' ); ?>
        <?php echo wc_help_tip( __( 'If you\'re using an express carrier you might have to specify this', 'shipcloud-for-woocommerce' ) ); ?>
    </th>
    <td>
        <div class="shipcloud__pickup_time">
            <div class="shipcloud__pickup_time--earliest">
                <div>
                    <?php
                        $today = new DateTime('NOW');
                    ?>
                    <input type="text" class="date-picker pickup_date" name="pickup[pickup_earliest_date]" value="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( $today->format('Y-m-d') ) ) ); ?>" maxlength="10" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" />@
                    &lrm;
                    <input type="number" class="pickup_time hour" placeholder="<?php esc_attr_e( 'h', 'woocommerce' ) ?>" name="pickup[pickup_earliest_time_hour]" min="0" max="23" step="1" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:
                    <input type="number" class="pickup_time minute" placeholder="<?php esc_attr_e( 'm', 'woocommerce' ) ?>" name="pickup[pickup_earliest_time_minute]" min="0" max="59" step="1" pattern="[0-5]{1}[0-9]{1}" />
                </div>
                <div>
                    <label for="pickup_earliest_date">
                        <small><?php _e( 'Earliest pickup date and time', 'shipcloud-for-woocommerce' ) ?></small>
                    </label>
                </div>
            </div>
            <div class="shipcloud__pickup_time--latest">
                <div>
                    <input type="text" class="date-picker pickup_date" name="pickup[pickup_latest_date]" value="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( $today->format('Y-m-d') ) ) ); ?>" maxlength="10" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" />@
                    &lrm;
                    <input type="number" class="pickup_time hour" placeholder="<?php esc_attr_e( 'h', 'woocommerce' ) ?>" name="pickup[pickup_latest_time_hour]" min="0" max="23" step="1" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:
                    <input type="number" class="pickup_time minute" placeholder="<?php esc_attr_e( 'm', 'woocommerce' ) ?>" name="pickup[pickup_latest_time_minute]" min="0" max="59" step="1" pattern="[0-5]{1}[0-9]{1}" />
                </div>
                <div>
                    <label for="pickup_latest_date">
                        <small><?php _e( 'Latest pickup date and time', 'shipcloud-for-woocommerce' ) ?></small>
                    </label>
                </div>
            </div>
        </div>
    </td>
</tr>
