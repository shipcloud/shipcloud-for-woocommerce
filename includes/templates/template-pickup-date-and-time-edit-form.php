<h4><?php _e( 'Pickup date and time', 'shipcloud-for-woocommerce' ); ?></h4>
<div class="shipcloud__pickup_time">
    <div class="shipcloud__pickup_time--earliest">
        <label for="pickup_earliest_date">
            <small><?php _e( 'Earliest pickup date and time', 'shipcloud-for-woocommerce' ) ?></small>
        </label>
        <input type="text" class="date-picker pickup_date" name="pickup[pickup_earliest_date]" maxlength="10" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" value="{{ data.model.get('pickup_request').getPickupTimeAsHash('earliest').date }}" />@
        &lrm;
        <input type="number" class="pickup_time hour nofloat" placeholder="<?php esc_attr_e( 'h', 'woocommerce' ) ?>" name="pickup[pickup_earliest_time_hour]" min="0" max="23" step="1" pattern="([01]?[0-9]{1}|2[0-3]{1})" value="{{ data.model.get('pickup_request').getPickupTimeAsHash('earliest').hours }}" />:
        <input type="number" class="pickup_time minute nofloat" placeholder="<?php esc_attr_e( 'm', 'woocommerce' ) ?>" name="pickup[pickup_earliest_time_minute]" min="0" max="59" step="1" pattern="[0-5]{1}[0-9]{1}" value="{{ data.model.get('pickup_request').getPickupTimeAsHash('earliest').minutes }}" />
    </div>
    <div class="shipcloud__pickup_time--latest">
        <label for="pickup_latest_date">
            <small><?php _e( 'Latest pickup date and time', 'shipcloud-for-woocommerce' ) ?></small>
        </label>
        <input type="text" class="date-picker pickup_date" name="pickup[pickup_latest_date]" maxlength="10" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" value="{{ data.model.get('pickup_request').getPickupTimeAsHash().date }}" />@
        &lrm;
        <input type="number" class="pickup_time hour nofloat" placeholder="<?php esc_attr_e( 'h', 'woocommerce' ) ?>" name="pickup[pickup_latest_time_hour]" min="0" max="23" step="1" pattern="([01]?[0-9]{1}|2[0-3]{1})" value="{{ data.model.get('pickup_request').getPickupTimeAsHash().hours }}" />:
        <input type="number" class="pickup_time minute nofloat" placeholder="<?php esc_attr_e( 'm', 'woocommerce' ) ?>" name="pickup[pickup_latest_time_minute]" min="0" max="59" step="1" pattern="[0-5]{1}[0-9]{1}" value="{{ data.model.get('pickup_request').getPickupTimeAsHash().minutes }}" />
    </div>
</div>
