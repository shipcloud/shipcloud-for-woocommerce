<input type="hidden" name="shipment[id]" value="{{ data.model.get('id') }}">
<input type="hidden" name="shipment[carrier]" value="{{ data.model.get('carrier') }}">
<input type="hidden" name="shipment[service]" value="{{ data.model.get('service') }}">

<fieldset class="label-shipment-sender address">
    <legend>
		<?php esc_html_e( 'Sender', 'shipcloud-for-woocommerce' ) ?>
    </legend>
    <div>
        <label for="shipment[from][company]">
			<?php esc_html_e( _x( 'Company', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][company]" value="{{ data.model.get('from').get('company') }}">
    </div>
    <div>
        <label for="shipment[from][first_name]">
			<?php esc_html_e( _x( 'First name', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][first_name]" value="{{ data.model.get('from').get('first_name') }}">
    </div>
    <div>
        <label for="shipment[from][last_name]">
			<?php esc_html_e( _x( 'Last name', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][last_name]" value="{{ data.model.get('from').get('last_name') }}">
    </div>
    <div>
        <label for="shipment[from][street]">
			<?php esc_html_e( _x( 'Street', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][street]" value="{{ data.model.get('from').get('street') }}">
    </div>
    <div>
        <label for="shipment[from][street_no]">
			<?php esc_html_e( _x( 'Street number', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][street_no]" value="{{ data.model.get('from').get('street_no') }}">
    </div>
    <div class="shipment_from_care_of_spacer"></div>
    <div>
        <label for="shipment[from][zip_code]">
			<?php esc_html_e( _x( 'ZIP code', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][zip_code]" value="{{ data.model.get('from').get('zip_code') }}">
    </div>
    <div>
        <label for="shipment[from][city]">
			<?php esc_html_e( _x( 'City', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][city]" value="{{ data.model.get('from').get('city') }}">
    </div>
    <div>
        <label for="shipment[from][state]">
			<?php esc_html_e( _x( 'State', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][state]" value="{{ data.model.get('from').get('state') }}">
    </div>
    <div>
        <label for="shipment[from][country]">
			<?php esc_html_e( _x( 'Country', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <select name="shipment[from][country]">
			<?php foreach ( wc()->countries->get_countries() AS $key => $country ): ?>
				<?php $country_code = WC_Shipping_Shipcloud_Utils::maybe_extract_country_code( $key ); ?>
                <option value="<?php esc_attr_e( $country_code ); ?>">
					<?php esc_html_e( $country ); ?>
                </option>
			<?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="shipment[from][phone]">
			<?php esc_html_e( _x( 'Phone', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[from][phone]" value="{{ data.model.get('from').get('phone') }}">
    </div>
</fieldset>

<fieldset class="label-shipment-recipient address">
    <legend>
		<?php esc_html_e( 'Recipient', 'shipcloud-for-woocommerce' ) ?>
    </legend>
    <div>
        <label for="shipment[to][company]">
			<?php esc_html_e( _x( 'Company', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][company]" value="{{ data.model.get('to').get('company') }}">
    </div>
    <div>
        <label for="shipment[to][first_name]">
			<?php esc_html_e( _x( 'First name', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][first_name]" value="{{ data.model.get('to').get('first_name') }}">
    </div>
    <div>
        <label for="shipment[to][last_name]">
			<?php esc_html_e( _x( 'Last name', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][last_name]" value="{{ data.model.get('to').get('last_name') }}">
    </div>
    <div>
        <label for="shipment[to][street]">
			<?php esc_html_e( _x( 'Street', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][street]" value="{{ data.model.get('to').get('street') }}">
    </div>
    <div>
        <label for="shipment[to][street_no]">
			<?php esc_html_e( _x( 'Street number', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][street_no]" value="{{ data.model.get('to').get('street_no') }}">
    </div>
    <div>
        <label for="shipment[to][Care of]">
			<?php esc_html_e( _x( 'Care of', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][care_of]" value="{{ data.model.get('to').get('care_of') }}">
    </div>
    <div>
        <label for="shipment[to][zip_code]">
			<?php esc_html_e( _x( 'ZIP code', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][zip_code]" value="{{ data.model.get('to').get('zip_code') }}">
    </div>
    <div>
        <label for="shipment[to][city]">
			<?php esc_html_e( _x( 'City', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][city]" value="{{ data.model.get('to').get('city') }}">
    </div>
    <div>
        <label for="shipment[to][state]">
			<?php esc_html_e( _x( 'State', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][state]" value="{{ data.model.get('to').get('state') }}">
    </div>
    <div>
        <label for="shipment[to][country]">
			<?php esc_html_e( _x( 'Country', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <select name="shipment[to][country]">
			<?php foreach ( wc()->countries->get_countries() AS $key => $country ): ?>
				<?php $country_code = WC_Shipping_Shipcloud_Utils::maybe_extract_country_code( $key ); ?>
                <option value="<?php esc_attr_e( $country_code ); ?>">
					<?php esc_html_e( $country ); ?>
                </option>
			<?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="shipment[to][phone]">
			<?php esc_html_e( _x( 'Phone', 'Backend: Shipment edit field label', 'shipcloud-for-woocommerce' ) ) ?>
        </label>
        <input type="text" name="shipment[to][phone]" value="{{ data.model.get('to').get('phone') }}">
    </div>
</fieldset>

<fieldset class="label-shipment-additional-services additional_services">
	<?php include( dirname( __FILE__ ) . '/template-additional-services-edit-form.php' ); ?>
</fieldset>

<# if ( data.model.get('pickup_request') ) { #>
    <fieldset class="label-shipment-pickup-request">
		<?php include( dirname( __FILE__ ) . '/template-pickup-date-and-time-edit-form.php' ); ?>
    </fieldset>
<# } #>

<fieldset class="label-shipment-misc shipcloud_misc">
  <legend>
    <?php esc_html_e( 'misc', 'shipcloud-for-woocommerce' ) ?>
  </legend>
  <div>
    <label for="shipcloud_notification_email">
      <?php _e( 'shipcloud notification email', 'shipcloud-for-woocommerce' ); ?>
      <?php echo wc_help_tip( __( 'Let shipcloud update the customer about the shipping status via email', 'shipcloud-for-woocommerce' ) ); ?>
    </label>
    <input type="checkbox" name="shipcloud_notification_email_checkbox"
      <# if ( data.model.get('notification_email') ) { #>
        checked="checked"
      <# } #>
    />
    <input type="text"
            name="shipcloud_notification_email"
            value="{{ data.model.get('notification_email') }}"
            class="notification_email_input" />
  </div>
  <div>
    <label for="shipcloud_label_format">
      <?php _e( 'Label format', 'shipcloud-for-woocommerce' ); ?>
    </label>

    <select type="text"
            name="shipcloud_label_format"
            class="shipcloud_label_format_input" />
    </select>
  </div>
</fieldset>

<div class="clear"></div>

<# if ( data.model.get('customs_declaration') ) { #>
    <fieldset class="label-shipment-customs-declaration">
<# } else { #>
    <fieldset class="label-shipment-customs-declaration" style="display: none;">
<# } #>
        <legend>
            <?php _e( 'Customs declaration', 'shipcloud-for-woocommerce' ) ?>
        </legend>
        <?php include( dirname( __FILE__ ) . '/template-customs-declaration-edit-form.php' ); ?>
    </fieldset>

<input type="hidden" name="shipment[package][weight]" value="{{ data.model.get('package').get('weight') }}" />
<input type="hidden" name="shipment[package][length]" value="{{ data.model.get('package').get('length') }}" />
<input type="hidden" name="shipment[package][width]" value="{{ data.model.get('package').get('width') }}" />
<input type="hidden" name="shipment[package][height]" value="{{ data.model.get('package').get('height') }}" />
<input type="hidden" name="shipment[package][type]" value="{{ data.model.get('package').get('type') }}" />
<input type="hidden" name="customs_declaration[shown]" value="false" />

<div class="label-shipment-actions">
    <!-- <button class="button shipcloud-show-customs-declaration" type="button">
        <?php _e( 'Add customs declaration', 'shipcloud-for-woocommerce' ) ?>
    </button> -->
    <button class="button wcsc-edit-abort" type="button">
        <?php _ex( 'Abort', 'Order: Backend button to abort edit', 'wcsc' ) ?>
    </button>
    <button class="button wcsc-save-shipment button-primary" type="button">
        <?php _ex( 'Save', 'Order: Backend button to edit prepared labels', 'wcsc' ) ?>
    </button>
</div>
