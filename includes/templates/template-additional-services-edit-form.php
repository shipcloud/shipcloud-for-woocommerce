<legend>
    <?php _e( 'Additional services', 'shipcloud-for-woocommerce' ); ?>
</legend>

<div class="additional_services__infobox">
  <?php _e( 'You can book so called "additional services" to get a better service for a certain use case of your shipment. Unfortunately these aren\'t available for all carriers.', 'shipcloud-for-woocommerce' ); ?>
  <br />
  <?php _e( 'Notice: Additional charges may apply', 'shipcloud-for-woocommerce' ); ?>
</div>

<div class="shipcloud_additional_service__no_additional_services shipcloud_additional_service--hidden">
  <?php _e( 'The contract used for your currently selected carrier does not support additional services.', 'shipcloud-for-woocommerce' ); ?>
</div>

<!-- Additional Services -->

<div class="shipcloud_additional_service shipcloud_additional_service__angel_de_delivery_date_time">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('angel_de_delivery_date_time') ) { #>
      <input type="checkbox" name="shipment[additional_services][angel_de_delivery_date_time][checked]" value="angel_de_delivery_date_time" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][angel_de_delivery_date_time][checked]" value="angel_de_delivery_date_time" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'MyTime', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'With the ANGEL MyTime service you can choose between several time frames for the delivery of your item', 'shipcloud-for-woocommerce' ) ); ?>
    <#
      if ( data.model.hasAdditionalService('angel_de_delivery_date_time') ) {
    #>
    <div class="shipcloud_angel_de_delivery_date_time">
    <# } else { #>
    <div class="shipcloud_additional_service--hidden shipcloud_angel_de_delivery_date_time">
    <# } #>
      <?php
        $date = new DateTime('NOW');
        $date->modify('+1 day');
      ?>
	  <p class="form-section-title"><?php _e( 'Delivery', 'shipcloud-for-woocommerce' ); ?></p>
	  <input type="text" 
	  		 class="date-picker angel_de_delivery_date_time w100 mb3" 
			 name="shipment[additional_services][angel_de_delivery_date_time][date]" 
			 maxlength="10" 
			 pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" 
			 value="<# if ( data.model.hasAdditionalService('angel_de_delivery_date_time') && data.model.getAdditionalServiceData('angel_de_delivery_date_time').date != undefined ) { #>{{ data.model.getAdditionalServiceData('angel_de_delivery_date_time').date }}<# } else { #><?php echo $date->format('Y-m-d'); ?><# } #>" 
	  />
	  <div class="shipcloud__angel_de_delivery_date_time--earliest">
		<div class="inline-box">
			<label>
				<small><?php _e( 'Earliest delivery time', 'shipcloud-for-woocommerce' ) ?></small>
			</label>
	        <input type="number" 
				   class="delivery_time hour w100px mb3 nofloat" 
				   placeholder="<?php esc_attr_e( 'h', 'woocommerce' ) ?>" 
				   name="angel_de_delivery_date_time_earliest_time_hour" 
				   min="0" 
				   max="23" 
				   step="1" 
				   pattern="[0-9]" 
				   value="<# if (time_of_day_earliest) { #>{{ time_of_day_earliest.substring(0, 2) }}<# } #>" 
			/>&nbsp;:&nbsp;
			
			<input type="number" 
				   class="delivery_time minute w100px mb3 nofloat" 
				   placeholder="<?php esc_attr_e( 'm', 'woocommerce' ) ?>" 
				   name="angel_de_delivery_date_time_earliest_time_minute" 
				   min="0" 
				   max="59" 
				   step="1" 
				   pattern="[0-9]" 
				   value="<# if (time_of_day_earliest) { #>{{ time_of_day_earliest.substring(3, 2) }}<# } #>" 
			/>
		</div>
      </div>
	  <div class="shipcloud__angel_de_delivery_date_time--latest">
		  <div class="inline-box">
			  <label>
				  <small><?php _e( 'Latest delivery time', 'shipcloud-for-woocommerce' ) ?></small>
			  </label>
			  <input type="number" 
			  		 class="delivery_time hour w100px mb3 nofloat" 
					 placeholder="<?php esc_attr_e( 'h', 'woocommerce' ) ?>" 
					 name="angel_de_delivery_date_time_latest_time_hour" 
					 min="0" 
					 max="23" 
					 step="1" 
					 pattern="[0-9]" 
					 value="<# if (time_of_day_latest) { #>{{ time_of_day_latest.substring(0, 2) }}<# } #>" 
			  />&nbsp;:&nbsp;
			  
			  <input type="number" 
			  		 class="delivery_time minute w100px mb3 nofloat" 
					 placeholder="<?php esc_attr_e( 'm', 'woocommerce' ) ?>" 
					 name="angel_de_delivery_date_time_latest_time_minute" 
					 min="0" 
					 max="59" 
					 step="1" 
					 pattern="[0-9]" 
					 value="<# if (time_of_day_latest) { #>{{ time_of_day_latest.substring(3, 2) }}<# } #>" 
			  />
	  	</div>
		
      </div>
	  
    </div>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__saturday_delivery">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('saturday_delivery') ) { #>
      <input type="checkbox" name="shipment[additional_services][saturday_delivery][checked]" value="saturday_delivery" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][saturday_delivery][checked]" value="saturday_delivery" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Saturday delivery', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'Some carriers normally don\'t make deliveries on Saturdays. Use this to request a delivery on a Saturday (when applicable).', 'shipcloud-for-woocommerce' ) ); ?>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__age_based_delivery">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('visual_age_check') || data.model.hasAdditionalService('ups_adult_signature') ) { #>
      <input type="checkbox" name="shipment[additional_services][age_based_delivery][checked]" value="age_based_delivery" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][age_based_delivery][checked]" value="age_based_delivery" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Age based delivery', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'When sending goods that are only legally available for people of a specific age, you can request the carrier to check the receiver\'s age', 'shipcloud-for-woocommerce' ) ); ?>
    <#
      if ( data.model.hasAdditionalService('visual_age_check') ) {
        var minimum_age = data.model.getAdditionalServiceData('visual_age_check').minimum_age;
    #>
    <div class="shipcloud_visual_age_check">
    <# } else { #>
    <div class="shipcloud_additional_service--hidden shipcloud_visual_age_check">
    <# } #>
      <span>
        <?php _e( 'DHL visual age check', 'shipcloud-for-woocommerce' ); ?>
        <?php echo wc_help_tip( __( 'DHL does a visual age check. You only need to specify the minimum age that should be checked.' ) ); ?>
      </span>
      <span>
        <select name="shipment[additional_services][visual_age_check][minimum_age]">
          <option value="">
            <?php _e( 'Select a minimum age', 'shipcloud-for-woocommerce' ); ?>
          </option>
          <option value="16" <# if ( minimum_age === '16' ) {#>selected="selected"<# } #>>
            <?php _e( '16 years', 'shipcloud-for-woocommerce' ); ?>
          </option>
          <option value="18" <# if ( minimum_age === '18' ) {#>selected="selected"<# } #>>
            <?php _e( '18 years', 'shipcloud-for-woocommerce' ); ?>
          </option>
        </select>
      </span>
    </div>
    <# if ( data.model.hasAdditionalService('ups_adult_signature') ) { #>
    <div class="shipcloud_ups_adult_signature">
        <input type="checkbox" name="shipment[additional_services][ups_adult_signature][checked]" value="ups_adult_signature" checked="checked" />
    <# } else { #>
    <div class="shipcloud_additional_service--hidden shipcloud_ups_adult_signature">
        <input type="checkbox" name="shipment[additional_services][ups_adult_signature][checked]" value="ups_adult_signature" />
    <# } #>
      <?php _e( 'UPS adult signature', 'shipcloud-for-woocommerce' ); ?>
      <?php echo wc_help_tip( __( 'UPS will obtain the adult recipient\'s signature and provide you with a printed copy.' ) ); ?>
    </div>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__premium_international">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('premium_international') ) { #>
      <input type="checkbox" name="shipment[additional_services][premium_international][checked]" value="premium_international" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][premium_international][checked]" value="premium_international" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Premium international', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'When using this option for international shipments they get a preferred routing by DHL', 'shipcloud-for-woocommerce' ) ); ?>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__delivery_date">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('delivery_date') ) { #>
      <input type="checkbox" name="shipment[additional_services][delivery_date][checked]" value="delivery_date" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][delivery_date][checked]" value="delivery_date" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Delivery date', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'Tell the carrier on which date the delivery should be made', 'shipcloud-for-woocommerce' ) ); ?>
    <#
      if ( data.model.hasAdditionalService('delivery_date') ) {
    #>
    <div class="shipcloud_delivery_date">
    <# } else { #>
    <div class="shipcloud_additional_service--hidden shipcloud_delivery_date">
    <# } #>
      <?php
        $date = new DateTime('NOW');
        $date->modify('+1 day');
      ?>
      <input type="text" class="date-picker delivery_date" name="shipment[additional_services][delivery_date][date]" maxlength="10" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" value="<# if ( data.model.hasAdditionalService('delivery_date') && data.model.getAdditionalServiceData('delivery_date').date != undefined ) { #>{{ data.model.getAdditionalServiceData('delivery_date').date }}<# } else { #><?php echo $date->format('Y-m-d'); ?><# } #>" />
    </div>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__delivery_note">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('delivery_note') ) { #>
      <input type="checkbox" name="shipment[additional_services][delivery_note][checked]" value="delivery_note" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][delivery_note][checked]" value="delivery_note" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Delivery note', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'Give the carrier specific instructions for delivering the shipment', 'shipcloud-for-woocommerce' ) ); ?>
    <#
      if ( data.model.hasAdditionalService('delivery_note') ) {
    #>
    <div class="shipcloud_delivery_note">
    <# } else { #>
    <div class="shipcloud_additional_service--hidden shipcloud_delivery_note">
    <# } #>
      <textarea type="text" rows="5"
                name="shipment[additional_services][delivery_note][message]"
                placeholder="<?php _e( 'Description where the carrier should drop off the shipment', 'shipcloud-for-woocommerce' ); ?>"><# if ( data.model.hasAdditionalService('delivery_note') ) { #>{{ data.model.getAdditionalServiceData('delivery_note').message }}<# } #></textarea>
    </div>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__delivery_time">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('delivery_time') ) { #>
      <input type="checkbox" name="shipment[additional_services][delivery_time][checked]" value="delivery_time" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][delivery_time][checked]" value="delivery_time" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Delivery time', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'Schedule a timeframe when the delivery should be made.', 'shipcloud-for-woocommerce' ) ); ?>

    <#
      if ( data.model.hasAdditionalService('delivery_time') ) {
        var time_of_day_earliest = data.model.getAdditionalServiceData('delivery_time').time_of_day_earliest;
        var time_of_day_latest = data.model.getAdditionalServiceData('delivery_time').time_of_day_latest;
    #>
    <div class="shipcloud_delivery_time">
    <# } else { #>
    <div class="shipcloud_additional_service--hidden shipcloud_delivery_time">
    <# } #>
      <div class="shipcloud__delivery_time--earliest">
        <label>
          <small><?php _e( 'Earliest delivery time', 'shipcloud-for-woocommerce' ) ?></small>
        </label>
        <input type="number" class="delivery_time hour nofloat" placeholder="<?php esc_attr_e( 'h', 'woocommerce' ) ?>" name="delivery_time_earliest_time_hour" min="0" max="23" step="1" pattern="[0-9]" value="<# if (time_of_day_earliest) { #>{{ time_of_day_earliest.substring(0, 2) }}<# } #>" />:
        <input type="number" class="delivery_time minute nofloat" placeholder="<?php esc_attr_e( 'm', 'woocommerce' ) ?>" name="delivery_time_earliest_time_minute" min="0" max="59" step="1" pattern="[0-9]" value="<# if (time_of_day_earliest) { #>{{ time_of_day_earliest.substring(3, 2) }}<# } #>" />
      </div>
      <div class="shipcloud__delivery_time--latest">
        <label>
          <small><?php _e( 'Latest delivery time', 'shipcloud-for-woocommerce' ) ?></small>
        </label>
        <input type="number" class="delivery_time hour nofloat" placeholder="<?php esc_attr_e( 'h', 'woocommerce' ) ?>" name="delivery_time_latest_time_hour" min="0" max="23" step="1" pattern="[0-9]" value="<# if (time_of_day_latest) { #>{{ time_of_day_latest.substring(0, 2) }}<# } #>" />:
        <input type="number" class="delivery_time minute nofloat" placeholder="<?php esc_attr_e( 'm', 'woocommerce' ) ?>" name="delivery_time_latest_time_minute" min="0" max="59" step="1" pattern="[0-9]" value="<# if (time_of_day_latest) { #>{{ time_of_day_latest.substring(3, 2) }}<# } #>" />
      </div>
    </div>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__drop_authorization">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('drop_authorization') ) { #>
      <input type="checkbox" name="shipment[additional_services][drop_authorization][checked]" value="drop_authorization" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][drop_authorization][checked]" value="drop_authorization" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Drop authorization', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'Let the carrier drop off the shipment at a specified place if the recipient isn\'t available', 'shipcloud-for-woocommerce' ) ); ?>
    <#
      if ( data.model.hasAdditionalService('drop_authorization') ) {
    #>
    <div class="shipcloud_drop_authorization">
    <# } else { #>
    <div class="shipcloud_additional_service--hidden shipcloud_drop_authorization">
    <# } #>
      <textarea type="text" rows="5"
                name="shipment[additional_services][drop_authorization][message]"
                placeholder="<?php _e( 'Description where the carrier should drop off the shipment', 'shipcloud-for-woocommerce' ); ?>"><# if ( data.model.hasAdditionalService('drop_authorization') ) { #>{{ data.model.getAdditionalServiceData('drop_authorization').message }}<# } #></textarea>
    </div>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__cash_on_delivery">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('cash_on_delivery') ) { #>
      <input type="checkbox" name="shipment[additional_services][cash_on_delivery][checked]" value="cash_on_delivery" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][cash_on_delivery][checked]" value="cash_on_delivery" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Cash on delivery', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'The carrier will collect the payment at the recipient and will transfer it to you', 'shipcloud-for-woocommerce' ) ); ?>
    <div class="shipcloud_additional_service--hidden shipcloud_cash_on_delivery">
        <div class="fifty">
            <div class="shipcloud_cash_on_delivery--left">
                <p class="fullsize flex flex-col-reverse">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][amount]"
                      class="cash_on_delivery_input"
                      <# if ( data.model.hasAdditionalService('cash_on_delivery') ) { #>
                        value="{{ data.model.getAdditionalServiceData('cash_on_delivery').amount }}"
                      <# } #>
                    />
                    <label for="shipment[additional_services][cash_on_delivery][amount]">
                        <?php _e( 'Amount', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
                <p class="fullsize flex flex-col-reverse">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][currency]" class="cash_on_delivery_input" value="EUR" disabled="disabled" />
                    <label for="">
                        <?php _e( 'Currency', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
                <p class="fullsize shipcloud_cash_on_delivery--reference1 flex flex-col-reverse">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][reference1]" class="cash_on_delivery_input" />
                    <label for="">
                        <?php _e( 'Reference', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
            </div>
        </div>
        <div class="fifty">
            <div class="shipcloud_cash_on_delivery--right">
                <p class="fullsize flex flex-col-reverse">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][bank_account_holder]" class="cash_on_delivery_input" />
                    <label for="shipment[additional_services][cash_on_delivery][bank_account_holder]">
                        <?php _e( 'Bank account holder', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
                <p class="fullsize flex flex-col-reverse">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][bank_name]" class="cash_on_delivery_input" />
                    <label for="shipment[additional_services][cash_on_delivery][bank_name]">
                        <?php _e( 'Bank name', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
                <p class="fullsize flex flex-col-reverse">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][bank_account_number]" class="cash_on_delivery_input" />
                    <label for="shipment[additional_services][cash_on_delivery][bank_account_number]">
                        <?php _e( 'Bank account number (IBAN)', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
                <p class="fullsize flex flex-col-reverse">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][bank_code]" class="cash_on_delivery_input" />
                    <label for="shipment[additional_services][cash_on_delivery][bank_code]">
                        <?php _e( 'Bank code (SWIFT)', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
            </div>
        </div>
    </div>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__gls_guaranteed24service">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('gls_guaranteed24service') ) { #>
      <input type="checkbox" name="shipment[additional_services][gls_guaranteed24service][checked]" value="gls_guaranteed24service" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][gls_guaranteed24service][checked]" value="gls_guaranteed24service" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'GLS Guaranteed24Service', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'When using the additional service Guaranteed24Service the carrier GLS is guaranteeing delivery on the next business day (except Saturdays) for shipments within Germany. If the delivery can\'t be made within time, GLS will refund the extra charges for the service.', 'shipcloud-for-woocommerce' ) ); ?>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__advance_notice">
	<?php 
    if ( 
      // ( isset($is_bulk_action_page) && WC_Shipping_Shipcloud_Utils::carrier_email_notification_enabled() ) ||
      // ( $this->carrier_email_notification_enabled() )
      ( method_exists( $this, 'is_bulk_action' ) && $this->is_bulk_action() && WC_Shipping_Shipcloud_Utils::carrier_email_notification_enabled() ) ||
      ( $this->carrier_email_notification_enabled() )
    ) : 
  ?>
	<div class="shipcloud_additional_service__checkbox">
    <input type="checkbox" name="shipment[additional_services][advance_notice][checked]" value="advance_notice" checked="checked" />
	</div>
	<div class="shipcloud_additional_service__text">
		<?php _e( 'Advance notice', 'shipcloud-for-woocommerce' ); ?>
		<?php echo wc_help_tip( __( 'The carrier will notify the recipient about an upcoming delivery', 'shipcloud-for-woocommerce' ) ); ?>
		<div class="shipcloud_additional_service--hidden shipcloud_additional_service__advance_notice--content">
			<div class="shipcloud_advance_notice_email">
				<table>
					<tr>
						<td>
							<input type="checkbox" name="shipment[additional_services][advance_notice][email_checkbox]" value="email_checkbox" checked="checked" />
						</td>
						<td>
							<input type="text" name="shipment[additional_services][advance_notice][email]" class="advance_notice_input"
							<# if ( data.model.hasAdditionalService('advance_notice') ) { #>
								value="{{ data.model.getAdditionalServiceData('advance_notice').email }}"
							<# } #>
							/>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<label for="shipment[additional_services][advance_notice][email]">
								<?php _e( 'eMail', 'shipcloud-for-woocommerce' ); ?>
							</label>
						</td>
					</tr>
				</table>
			</div>
			<div class="shipcloud_advance_notice_phone">
				<table>
					<tr>
						<td>
							<input type="checkbox" name="shipment[additional_services][advance_notice][phone_checkbox]" value="phone_checkbox" checked="checked" />
						</td>
						<td>
							<input type="text" name="shipment[additional_services][advance_notice][phone]" class="advance_notice_input"
							<# if ( data.model.hasAdditionalService('advance_notice') ) { #>
								value="{{ data.model.getAdditionalServiceData('advance_notice').phone }}"
							<# } #>
							/>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<label for="shipment[additional_services][advance_notice][phone]">
								<?php _e( 'Phone', 'shipcloud-for-woocommerce' ); ?>
							</label>
						</td>
					</tr>
				</table>
			</div>
			<div class="shipcloud_advance_notice_sms">
				<table>
					<tr>
						<td>
							<input type="checkbox" name="shipment[additional_services][advance_notice][sms_checkbox]" value="sms_checkbox" checked="checked" />
						</td>
						<td>
							<input type="text" name="shipment[additional_services][advance_notice][sms]" class="advance_notice_input"
							<# if ( data.model.hasAdditionalService('advance_notice') ) { #>
								value="{{ data.model.getAdditionalServiceData('advance_notice').sms }}"
							<# } #>
							/>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<label for="shipment[additional_services][advance_notice][sms]">
								<?php _e( 'SMS', 'shipcloud-for-woocommerce' ); ?>
							</label>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<div class="clear"></div>

	<?php else : ?>
		
		<div class="shipcloud_additional_service__only_advance_notice hidden">
		  <?php _e( 'The contract selected for this shipping service provider does not support any additional services other than advance notice. However, the customer has not given permission for this.', 'shipcloud-for-woocommerce' ); ?>
		</div>
	  	<div class="shipcloud_additional_service__notification_disabled">
	  		<input type="hidden" name="shipment[additional_services][advance_notice][checked]" value="0" />
	  		<input type="hidden" name="shipment[additional_services][advance_notice][email_checkbox]" value="0" />
	  		<input type="hidden" name="shipment[additional_services][advance_notice][email]" value="" />
	  		<input type="hidden" name="shipment[additional_services][advance_notice][phone_checkbox]" value="0" />
	  		<input type="hidden" name="shipment[additional_services][advance_notice][phone]" value="" />
	  		<input type="hidden" name="shipment[additional_services][advance_notice][sms_checkbox]" value="0" />
	  		<input type="hidden" name="shipment[additional_services][advance_notice][sms]" value="" />
	  	</div>

	<?php endif; ?>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__dhl_named_person_only">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('dhl_named_person_only') ) { #>
      <input type="checkbox" name="shipment[additional_services][dhl_named_person_only][checked]" value="dhl_named_person_only" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][dhl_named_person_only][checked]" value="dhl_named_person_only" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Named person only', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'The named person only service ensures in an uncomplicated and cost-effective way that your parcels are only delivered to the recipient in person or to an authorized person.', 'shipcloud-for-woocommerce' ) ); ?>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__dhl_parcel_outlet_routing">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('dhl_parcel_outlet_routing') ) { #>
      <input type="checkbox" name="shipment[additional_services][dhl_parcel_outlet_routing][checked]" value="dhl_parcel_outlet_routing" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][dhl_parcel_outlet_routing][checked]" value="dhl_parcel_outlet_routing" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Parcel outlet routing', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'With the DHL additional service retail outlet routing you can tell the carrier to reroute a parcel to the nearest outlet when the recipient can\'t be reached. Otherwise the parcel will be returned immediately.', 'shipcloud-for-woocommerce' ) ); ?>
	<!--
    <#
      if ( data.model.hasAdditionalService('dhl_parcel_outlet_routing') ) {
    #>
    <div class="shipcloud_dhl_parcel_outlet_routing">
    <# } else { #>
    <div class="shipcloud_additional_service--hidden shipcloud_dhl_parcel_outlet_routing">
    <# } #>
  	<input type="text" name="shipment[additional_services][dhl_parcel_outlet_routing][email]"
  		   class="dhl_parcel_outlet_routing_input"
  		   <# if ( data.model.hasAdditionalService('dhl_parcel_outlet_routing') ) { #>
  			   value="{{ data.model.getAdditionalServiceData('dhl_parcel_outlet_routing').email }}"
  		   <# } #>
  	/>
    </div>
	-->
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__dhl_endorsement">
  <div class="shipcloud_additional_service__checkbox">
    <# if ( data.model.hasAdditionalService('dhl_endorsement') ) { #>
      <input type="checkbox" name="shipment[additional_services][dhl_endorsement][checked]" value="dhl_endorsement" checked="checked" />
    <# } else { #>
      <input type="checkbox" name="shipment[additional_services][dhl_endorsement][checked]" value="dhl_endorsement" />
    <# } #>
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Endorsement', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'Prior to sending your parcel you can specify what should happen to the shipment in case it cannot be delivered in the destination country. By choosing the handling option abandon, your parcel will not be returned to you, but rather auctioned off or destroyed by the recipient countrys\' postal company. You will not be charged with a return fee for this option. When using the option return_immediately, the shipment will be returned to you and you will be charged for returning it.', 'shipcloud-for-woocommerce' ) ); ?>
	<#
      if ( data.model.hasAdditionalService('dhl_endorsement') ) {
        var handling = data.model.getAdditionalServiceData('dhl_endorsement').handling;
    #>
    <div class="shipcloud_dhl_endorsement">
    <# } else { #>
    <div class="shipcloud_additional_service--hidden shipcloud_dhl_endorsement">
    <# } #>
      <span>
        <select name="shipment[additional_services][dhl_endorsement][handling]">
          <option value="">
            <?php _e( 'Select a handling', 'shipcloud-for-woocommerce' ); ?>
          </option>
          <option value="abandon" <# if ( handling === 'abandon' ) {#>selected="selected"<# } #>>
            <?php _e( 'Abandon', 'shipcloud-for-woocommerce' ); ?>
          </option>
          <option value="return_immediately" <# if ( handling === 'return_immediately' ) {#>selected="selected"<# } #>>
            <?php _e( 'Return immediately', 'shipcloud-for-woocommerce' ); ?>
          </option>
        </select>
      </span>
    </div>
  </div>
  <div class="clear"></div>
</div>

<div class="shipcloud_additional_service shipcloud_additional_service__pickup_object">
  <div class="shipcloud_additional_service__checkbox">
    <input type="checkbox" name="shipment[additional_services][pickup_object][checked]" value="pickup_object" checked="checked" />
  </div>
  <div class="shipcloud_additional_service__text">
    <?php _e( 'Pickup', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'For some carriers it is mandatory to pass a pickup object when creating a shipment', 'shipcloud-for-woocommerce' ) ); ?>
    <div class="shipcloud_pickup_object">
  	  <div class="shipcloud__pickup_object--pickup">
  		  <?php include( dirname( __FILE__ ) . '/template-pickup-date-and-time.php' ); ?>
  	  </div>
    </div>
  </div>
  <div class="clear"></div>
</div>
