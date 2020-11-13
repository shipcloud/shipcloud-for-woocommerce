;var shipcloud = shipcloud || {};

shipcloud.MultiSelect = function (wrapperSelector, options) {
    var $ = jQuery;
    var self = this;

    this.$wrapper = $(wrapperSelector);
    this.options = options;
    this.$carrier = $('[rel="shipcloud_carrier"]', self.$wrapper);
    this.$service = $('[rel="shipcloud_carrier_service"]', self.$wrapper);
    this.$packageType = $('[rel="shipcloud_carrier_package"]', self.$wrapper);
    this.$labelFormat = $('[rel="shipcloud_label_format"]');

    this.main = function () {
        self.render();
    };

    this.getCarrierData = function (carrier) {
        for (var i in self.options.data) {
            if (!self.options.data.hasOwnProperty(i)) {
                continue;
            }

            if (self.options.data[i].name === carrier) {
                return self.options.data[i];
            }
        }

        return {};
    };

    this.render = function () {
        // Carriers
        self.$carrier.html('');
        self.$service.append('<option value="">' + self.options.label.carrier.placeholder + '</option>');
        $(self.options.data).each(function () {
            self.$carrier.append('<option value="' + this.name + '">' + this.display_name + '</option>');
        });

        self.renderChildren();

        self.$carrier.on('change', self.renderChildren);

        $('.shipcloud__pickup_date_and_time').hide();
        self.$carrier.on('change', self.renderCarrierSpecificInputs);
        self.$service.on('change', self.renderLabelFormats);
    };

  this.renderCarrierSpecificInputs = function () {
    switch (self.$carrier.val()) {
      case 'dhl_express':
      case 'go':
      case 'tnt':
        $('.shipcloud__pickup_date_and_time').show();
        break;
      default:
        $('.shipcloud__pickup_date_and_time').hide();
    }
  };

    this.renderChildren = function () {
        self.renderChild(self.$service, 'services');
        self.renderChild(self.$packageType, 'package_types');
        self.renderLabelFormats();
    };

    this.renderChild = function (selectNode, type) {
        // Assert jQuery.
        selectNode = $(selectNode);

        selectNode.prop('disabled', 'disabled');
        selectNode.html('');
        selectNode.append('<option>' + self.options.label[type].placeholder + '</option>');

        var carrier = self.getCarrierData(self.$carrier.val());

        $(carrier[type]).each(function (index, value) {
            if (typeof self.options.label[type][value] === 'undefined') {
                console.log('No ' + type + ' label found for ' + value);
                return;
            }

            if(index == 0) {
              selectNode.append(
                '<option value="' + value + '" selected="selected">' + self.options.label[type][value] + '</option>'
              );
            } else {
              selectNode.append(
                '<option value="' + value + '">' + self.options.label[type][value] + '</option>'
              );
            }
        });

        selectNode.prop('disabled', null);
    };

    this.renderLabelFormats = function () {
      selectNode = $(self.$labelFormat);

      selectNode.prop('disabled', 'disabled');
      selectNode.html('');

      selectNode.append('<option value="">' + self.options.label['label_formats'].placeholder + '</option>');

      var carrier = self.getCarrierData(self.$carrier.val());

      $(carrier['label_formats']).each(function (index, value) {
        var label_formats_for_service = value[self.$service.val()];

        $(label_formats_for_service).each(function (index, value) {
          selectNode.append(
            '<option value="' + value + '">' + self.options.label['label_formats'][value] + '</option>'
          );
        });
      });

      selectNode.prop('disabled', null);
    };

    this.select = function (map) {
        if (map.hasOwnProperty('carrier') && map['carrier']) {
            self.$carrier.val(map['carrier']);
        }

        self.renderChildren();

        if (map.hasOwnProperty('service') && map['service']) {
            self.$service.val(map['service']);
        }

        if (map.hasOwnProperty('package') && map['package']) {
            self.$packageType.val(map['package']);
        }
    };

    self.main();
};

// Extend jQuery if present just by delegating.
if (window.jQuery) {
    (function ($) {
        $.fn.shipcloudMultiSelect = function (options) {
            return new shipcloud.MultiSelect(this, options);
        };
    })(jQuery);
}
