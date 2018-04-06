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
            'reference_number' : self.getReferenceNumber(),
            'additional_services': self.getAdditionalServices()
        };
    };

    this.getAdditionalServices = function () {
      var additional_services_array = [];
      var selected_additional_services = $('.shipcloud_additional_service__checkbox input[type="checkbox"]:checked', self.$wrapper).map(function(){
        return $(this).val();
      }).get();

      selected_additional_services.forEach(function(element) {
        switch(element) {
          case 'age_based_delivery':
            switch(self.getCarrier()) {
              case 'dhl':
                var selected_minimum_age = $('select[name="shipment[additional_services][visual_age_check][minimum_age]"]').val();
                additional_services_array.push({
                  'name': 'visual_age_check',
                  'properties': {
                    'minimum_age': selected_minimum_age
                  }
                });
                break;
              case 'ups':
                var use_ups_adult_signature = $('input[name="shipment[additional_services][ups_adult_signature][checked]"]', self.$wrapper);
                if (use_ups_adult_signature.prop('checked')) {
                  additional_services_array.push({
                    'name': 'ups_adult_signature'
                  });
                }
                break;
            }
            break;
          case 'saturday_delivery':
            additional_services_array.push({
              'name': 'saturday_delivery'
            });
            break;
          case 'premium_international':
            additional_services_array.push({
              'name': 'premium_international'
            });
            break;
          case 'delivery_time':
            var selected_option = $('select[name="shipment[additional_services][delivery_time][timeframe]"]').val();
            var time_of_day_earliest = selected_option.substring(0, 2) + ':00';
            var time_of_day_latest = selected_option.substring(2, 4) + ':00';

            additional_services_array.push({
              'name': 'delivery_time',
              'properties': {
                'time_of_day_earliest': time_of_day_earliest,
                'time_of_day_latest': time_of_day_latest
              }
            });
            break;
          case 'drop_authorization':
            additional_services_array.push({
              'name': 'drop_authorization',
              'properties': {
                'message': $('textarea[name="shipment[additional_services][drop_authorization][message]"]').val()
              }
            });
            break;
          case 'cash_on_delivery':
            var cod_hash = {
              'name': 'cash_on_delivery',
              'properties': {
                'amount': $('input[name="shipment[additional_services][cash_on_delivery][amount]"]').val(),
                'currency': $('input[name="shipment[additional_services][cash_on_delivery][currency]"]').val()
              }
            }

            switch(self.getCarrier()) {
              case 'dhl':
                cod_hash['properties']['reference1'] = $('input[name="shipment[additional_services][cash_on_delivery][reference1]"]').val(),
                cod_hash['properties']['bank_account_holder'] = $('input[name="shipment[additional_services][cash_on_delivery][bank_account_holder]"]').val();
                cod_hash['properties']['bank_name'] = $('input[name="shipment[additional_services][cash_on_delivery][bank_name]"]').val();
                cod_hash['properties']['bank_account_number'] = $('input[name="shipment[additional_services][cash_on_delivery][bank_account_number]"]').val();
                cod_hash['properties']['bank_code'] = $('input[name="shipment[additional_services][cash_on_delivery][bank_code]"]').val();
                break;
              case 'gls':
                cod_hash['properties']['reference1'] = $('input[name="shipment[additional_services][cash_on_delivery][reference1]"]').val()
                break;
            }

            additional_services_array.push(cod_hash);
            break;
          case 'gls_guaranteed24service':
            additional_services_array.push({
              'name': 'gls_guaranteed24service'
            });
            break;
        }
      });

      return additional_services_array;
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
