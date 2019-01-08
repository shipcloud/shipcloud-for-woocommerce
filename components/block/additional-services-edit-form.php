<legend>
    <?php _e( 'Additional services', 'shipcloud-for-woocommerce' ); ?>
</legend>

<div class="additional_services__infobox">
  <?php _e( 'You can book so called "additional services" to get a better service for a certain use case of your shipment. Unfortunately these aren\'t available for all carriers.', 'shipcloud-for-woocommerce' ); ?>
  <br />
  <?php _e( 'Notice: Additional charges may apply', 'shipcloud-for-woocommerce' ); ?>
</div>

<div class="shipcloud_additional_service__no_additional_services shipcloud_additional_service--hidden">
  <?php _e( 'shipcloud does not support additional services for your currently selected carrier.', 'shipcloud-for-woocommerce' ); ?>
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
            <?php _e( '[ Please select a minimum age ]', 'shipcloud-for-woocommerce' ); ?>
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
    <?php _e( 'DHL premium international', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'When using this option for international shipments they get a preferred routing by DHL', 'shipcloud-for-woocommerce' ) ); ?>
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
    <?php _e( 'DHL preferred time', 'shipcloud-for-woocommerce' ); ?>
    <?php echo wc_help_tip( __( 'Schedule a timeframe when the delivery should be made by DHL.', 'shipcloud-for-woocommerce' ) ); ?>
    <#
      if ( data.model.hasAdditionalService('delivery_time') ) {
        var time_of_day_earliest = data.model.getAdditionalServiceData('delivery_time').time_of_day_earliest;
        var time_of_day_latest = data.model.getAdditionalServiceData('delivery_time').time_of_day_latest;
        var timeframe = time_of_day_earliest.substring(0, 2) + time_of_day_latest.substring(0, 2);
    #>
    <div class="shipcloud_delivery_time">
    <# } else { #>
    <div class="shipcloud_additional_service--hidden shipcloud_delivery_time">
    <# } #>
      <select name="shipment[additional_services][delivery_time][timeframe]">
        <option value="">
          <?php _e( '[ Please select a delivery timeframe ]', 'shipcloud-for-woocommerce' ); ?>
        </option>
        <option value="1012" <# if ( timeframe === '1012' ) {#>selected="selected"<# } #>>10:00 - 12:00</option>
        <option value="1214" <# if ( timeframe === '1214' ) {#>selected="selected"<# } #>>12:00 - 14:00</option>
        <option value="1416" <# if ( timeframe === '1416' ) {#>selected="selected"<# } #>>14:00 - 16:00</option>
        <option value="1618" <# if ( timeframe === '1618' ) {#>selected="selected"<# } #>>16:00 - 18:00</option>
        <option value="1820" <# if ( timeframe === '1820' ) {#>selected="selected"<# } #>>18:00 - 20:00</option>
        <option value="1921" <# if ( timeframe === '1921' ) {#>selected="selected"<# } #>>19:00 - 21:00</option>
      </select>
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
      <textarea type="text"
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
                <p class="fullsize">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][amount]" class="cash_on_delivery_input" />
                    <label for="shipment[additional_services][cash_on_delivery][amount]">
                        <?php _e( 'Amount', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
                <p class="fullsize">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][currency]" class="cash_on_delivery_input" value="EUR" disabled="disabled" />
                    <label for="">
                        <?php _e( 'Currency', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
                <p class="fullsize shipcloud_cash_on_delivery--reference1">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][reference1]" class="cash_on_delivery_input" />
                    <label for="">
                        <?php _e( 'Reference', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
            </div>
        </div>
        <div class="fifty">
            <div class="shipcloud_cash_on_delivery--right">
                <p class="fullsize">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][bank_account_holder]" class="cash_on_delivery_input" />
                    <label for="">
                        <?php _e( 'Bank account holder', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
                <p class="fullsize">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][bank_name]" class="cash_on_delivery_input" />
                    <label for="">
                        <?php _e( 'Bank name', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
                <p class="fullsize">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][bank_account_number]" class="cash_on_delivery_input" />
                    <label for="">
                        <?php _e( 'Bank account number (IBAN)', 'shipcloud-for-woocommerce' ); ?>
                    </label>
                </p>
                <p class="fullsize">
                    <input type="text" name="shipment[additional_services][cash_on_delivery][bank_code]" class="cash_on_delivery_input" />
                    <label for="">
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
