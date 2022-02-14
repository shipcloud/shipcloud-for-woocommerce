jQuery(function($) {
  var $pakadooIdInput = $('#shipping_pakadoo_id');

  $pakadooIdInput.on('focusout', function () {
    if (this.value !== '') {
      if ($('.pakadoo_error')) {
        $('.pakadoo_error').remove();
      }
      jQuery.ajax({
        type: 'POST',
        data: { action: 'shipcloud_get_pakadoo_point', pakadoo_id: this.value },
        url: woocommerce_params.ajax_url,
        success: function (response) {
          if (response.success) {
            var data = response.data.data;
			console.log(JSON.stringify(data));
            $('#shipping_company').val(data.company);
            $('#shipping_country').val(data.country.toUpperCase());
            $('#shipping_country').trigger('change');
            $('#shipping_address_1').val(data.street + ' ' + data.street_no);
            $('#shipping_postcode').val(data.zip_code);
            $('#shipping_city').val(data.city);
			$('#shipping_pakadoo_address_id').val(data.id);
			$('#shipping_care_of').val('PAK '+data.pakadoo_id);
          } else {
            $('#shipping_pakadoo_id_field').removeClass('woocommerce-validated');
            $('#shipping_pakadoo_id_field').addClass('woocommerce-invalid');

            var html = '<div class="woocommerce-error pakadoo_error">';
            html += _(response.data).pluck('message').join('\n');
            html += '</div>';
            $('#shipping_pakadoo_id_field .description').after(html);
          }
        }
      });
    }
  });
});
