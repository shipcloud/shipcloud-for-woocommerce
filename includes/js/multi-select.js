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

        return;
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

var wcsccarrier = {
    'label': {
        'carrier'      : {'placeholder': 'Select carrier'},
        'package_types': {'placeholder': 'Select type', 'parcel': 'Parcel', 'bulk': 'Bulk'},
        'services'     : {
            'placeholder'  : 'Select service',
            'standard'     : 'Standard',
            'one_day'      : 'Express (1 Day)',
            'one_day_early': 'Express (1 Day Early)',
            'same_day'     : 'Same Day'
        }
    },
    'data' : [{
        'name'         : 'dhl',
        'display_name' : 'DHL',
        'package_types': ['parcel', 'bulk'],
        'services'     : ['standard', 'returns', 'one_day', 'one_day_early']
    }, {
        'name'         : 'dhl_express',
        'display_name' : 'DHL Express',
        'package_types': ['parcel'],
        'services'     : ['one_day', 'one_day_early']
    }, {
        'name'         : 'dpag',
        'display_name' : 'Deutsche Post',
        'package_types': ['letter', 'parcel_letter', 'books'],
        'services'     : ['standard']
    }, {
        'name'         : 'dpd',
        'display_name' : 'DPD',
        'package_types': ['parcel', 'parcel_letter'],
        'services'     : ['standard', 'returns', 'one_day', 'one_day_early']
    }, {
        'name'         : 'fedex',
        'display_name' : 'FedEx',
        'package_types': ['parcel'],
        'services'     : ['one_day_early']
    }, {
        'name'         : 'gls',
        'display_name' : 'GLS',
        'package_types': ['parcel'],
        'services'     : ['standard', 'one_day']
    }, {
        'name'         : 'hermes',
        'display_name' : 'Hermes',
        'package_types': ['parcel'],
        'services'     : ['standard', 'returns']
    }, {
        'name'         : 'iloxx',
        'display_name' : 'iloxx (MyDPD Business)',
        'package_types': ['parcel'],
        'services'     : ['standard']
    }, {
        'name'         : 'liefery',
        'display_name' : 'Liefery',
        'package_types': ['parcel'],
        'services'     : ['same_day']
    }, {
        'name'         : 'tnt',
        'display_name' : 'TNT',
        'package_types': ['parcel'],
        'services'     : ['one_day', 'one_day_early']
    }, {
        'name'         : 'ups',
        'display_name' : 'UPS',
        'package_types': ['parcel'],
        'services'     : ['standard', 'returns', 'one_day', 'one_day_early']
    }]
};
