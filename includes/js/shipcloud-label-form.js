;shipcloud = shipcloud || {};

shipcloud.LabelForm = function (wrapperSelector) {
    var $ = jQuery;
    var self = this;

    this.$wrapper = $(wrapperSelector);

    this.main = function () {

    };

    this.getRecipient = function () {
        return {
            'first_name': $('input[name="recipient_address[first_name]"]', self.$wrapper).val(),
            'last_name' : $('input[name="recipient_address[last_name]"]', self.$wrapper).val(),
            'company'   : $('input[name="recipient_address[company]"]', self.$wrapper).val(),
            'street'    : $('input[name="recipient_address[street]"]', self.$wrapper).val(),
            'street_no' : $('input[name="recipient_address[street_nr]"]', self.$wrapper).val(),
            'care_of'   : $('input[name="recipient_address[care_of]"]', self.$wrapper).val(),
            'zip_code'  : $('input[name="recipient_address[zip_code]"]', self.$wrapper).val(),
            'city'      : $('input[name="recipient_address[city]"]', self.$wrapper).val(),
            'state'     : $('input[name="recipient_address[state]"]', self.$wrapper).val(),
            'country'   : $('select[name="recipient_address[country]"]', self.$wrapper).val(),
            'phone'     : $('input[name="recipient_address[phone]"]', self.$wrapper).val()
        };
    };

    this.getSender = function () {
        return {
            'first_name': $('input[name="sender_address[first_name]"]', self.$wrapper).val(),
            'last_name' : $('input[name="sender_address[last_name]"]', self.$wrapper).val(),
            'company'   : $('input[name="sender_address[company]"]', self.$wrapper).val(),
            'street'    : $('input[name="sender_address[street]"]', self.$wrapper).val(),
            'street_no' : $('input[name="sender_address[street_nr]"]', self.$wrapper).val(),
            'zip_code'  : $('input[name="sender_address[zip_code]"]', self.$wrapper).val(),
            'city'      : $('input[name="sender_address[city]"]', self.$wrapper).val(),
            'state'     : $('input[name="sender_address[state]"]', self.$wrapper).val(),
            'country'   : $('select[name="sender_address[country]"]', self.$wrapper).val(),
            'phone'     : $('input[name="sender_address[phone]"]', self.$wrapper).val()
        };
    };

    this.getPackage = function () {
      var use_calculated_weight = $('input[name="shipcloud_use_calculated_weight"]', self.$wrapper);
      if (use_calculated_weight.prop('checked')) {
        var weight = $('#shipment-center .section.parcels').data('calculated-weight');
      } else {
        var weight = $('input[name="parcel_weight"]', self.$wrapper).val();
      }
      
      var package_hash = {
        'width'      : $('input[name="parcel_width"]', self.$wrapper).val(),
        'height'     : $('input[name="parcel_height"]', self.$wrapper).val(),
        'length'     : $('input[name="parcel_length"]', self.$wrapper).val(),
        'weight'     : weight,
        'description': $('input[name="parcel_description"]', self.$wrapper).val(),
        'type'       : $('select[name="shipcloud_carrier_package"]', self.$wrapper).val()
      };

      if ($('input[name="declared_value"]', self.$wrapper).val()) {
        package_hash['declared_value'] = {
          'amount'   : $('input[name="declared_value"]', self.$wrapper).val(),
          'currency' : 'EUR'
        };
      }
      return package_hash;
    };

    self.getCarrier = function () {
        return $('select[name="shipcloud_carrier"]', self.$wrapper).val()
    };

    self.getCarrierService = function () {
        return $('select[name="shipcloud_carrier_service"]', self.$wrapper).val();
    };

    self.getDescription = function () {
        return $('input[name="other_description"]', self.$wrapper).val();
    };

    self.getReferenceNumber = function () {
        return $('input[name="reference_number"]', self.$wrapper).val();
    };

    this.getLabelData = function () {
        return {
            'order_id'         : $("#post_ID").val(),
            'from'             : self.getSender(),
            'to'               : self.getRecipient(),
            'package'          : self.getPackage(),
            'carrier'          : self.getCarrier(),
            'service'          : self.getCarrierService(),
            // @todo No API data - swap to WP logic.
            'parcel_id'        : $('select[name="parcel_id"]', self.$wrapper).val(),
            'other_description': self.getDescription(),
            'reference_number' : self.getReferenceNumber()
        };
    };

    self.main();
};

// Extend jQuery if present just by delegating.
if (window.jQuery) {
    (function ($) {
        $.fn.shipcloudLabelForm = function () {
            return new shipcloud.LabelForm(this);
        }
    })(jQuery);
}
