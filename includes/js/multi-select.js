;var shipcloud = shipcloud || {};

shipcloud.MultiSelect = function (wrapperSelector, options) {
    var $ = jQuery;
    var self = this;

    this.$wrapper = $(wrapperSelector);
    this.options = options;
    this.$carrier = $('[rel="shipcloud_carrier"]', self.$wrapper);
    this.$service = $('[rel="shipcloud_carrier_service"]', self.$wrapper);
    this.$packageType = $('[rel="shipcloud_carrier_package"]', self.$wrapper);

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
    };

    this.renderChildren = function () {
        self.renderChild(self.$service, 'services');
        self.renderChild(self.$packageType, 'package_types');
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

            selectNode.append(
                '<option value="' + value + '">' + self.options.label[type][value] + '</option>'
            );
        });

        selectNode.prop('disabled', null);
    };

    this.select = function (map) {
        if (map.hasOwnProperty('carrier') && map['carrier']) {
            self.$carrier.val(map['carrier']);
        }

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