<div class="shipcloud__pickup_time">
    <div class="shipcloud__pickup_time--earliest">
        <div class="mb-5">
            <?php
                // $today = new DateTime('NOW');
		        $date = new DateTime('NOW');
		        $date->modify('+1 day');
            ?>
            
            <input type="text" 
				   class="date-picker pickup_date mb-5" 
				   name="pickup[pickup_earliest_date]" 
				   value="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( $date->format('Y-m-d') ) ) ); ?>" 
				   maxlength="10" 
				   pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" />
            
            <label for="pickup_earliest_date" class="block">
                <?php _e( 'Earliest pickup time', 'shipcloud-for-woocommerce' ) ?>
            </label>
            <input type="number" 
				   class="pickup_time hour w100px mb3 nofloat" 
				   placeholder="<?php esc_attr_e( 'h', 'woocommerce' ) ?>" 
				   name="pickup[pickup_earliest_time_hour]" 
				   min="0" 
				   max="23" 
				   step="1" 
				   pattern="[0-9]" />&nbsp;:&nbsp;
				   
			<input type="number" 
				   class="pickup_time minute w100px mb3 nofloat" 
				   placeholder="<?php esc_attr_e( 'm', 'woocommerce' ) ?>" 
				   name="pickup[pickup_earliest_time_minute]" 
				   min="0" 
				   max="59" 
				   step="1" 
				   pattern="[0-9]" />
        </div>
    </div>
    <div class="shipcloud__pickup_time--latest">
        <div>
            
            <!-- <input type="text"
				   class="date-picker pickup_date w100 mb3"
				   name="pickup[pickup_latest_date]"
				   value="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( $date->format('Y-m-d') ) ) ); ?>"
				   maxlength="10"
				   pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" /> -->
            
            <label for="pickup_latest_date" class="block">
                <?php _e( 'Latest pickup time', 'shipcloud-for-woocommerce' ) ?>
            </label>
			<div class="inline-box">
	            <input type="number" 
					   class="pickup_time hour w100px mb3 nofloat" 
					   placeholder="<?php esc_attr_e( 'h', 'woocommerce' ) ?>" 
					   name="pickup[pickup_latest_time_hour]" 
					   min="0" 
					   max="23" 
					   step="1" 
					   pattern="[0-9]" />&nbsp;:&nbsp;
				   
				<input type="number" 
					   class="pickup_time minute w100px mb3 nofloat" 
					   placeholder="<?php esc_attr_e( 'm', 'woocommerce' ) ?>" 
					   name="pickup[pickup_latest_time_minute]" 
					   min="0" 
					   max="59" 
					   step="1" 
					   pattern="[0-9]" />
			</div>
        </div>

    </div>
</div>