;shipcloud = shipcloud || {};

shipcloud.AddressModel = Backbone.Model.extend({
    defaults: {
        'id'        : null,
        'company'   : null,
        'first_name': null,
        'last_name' : null,
        'street'    : null,
        'street_no' : null,
        'zip_code'  : null,
        'city'      : null,
        'country'   : null,
        'phone'     : null
    }
});

shipcloud.ShipmentModel = Backbone.Model.extend({
    defaults: {
        'id'                        : null,
        'to'                        : new shipcloud.AddressModel(),
        'from'                      : new shipcloud.AddressModel(),
        'created_at'                : null,
        'package'                   : {},
        'carrier'                   : null,
        'service'                   : null,
        'reference_number'          : null,
        'carrier_tracking_no'       : null,
        'label_url'                 : null,
        'notification_email'        : null,
        'price'                     : null,
        'shipper_notification_email': null,
        'tracking_url'              : null
    },

    initialize: function () {
        if (false === this.get('from') instanceof shipcloud.AddressModel) {
            this.set('from', new shipcloud.AddressModel(this.get('from')));
        }

        if (false === this.get('to') instanceof shipcloud.AddressModel) {
            this.set('to', new shipcloud.AddressModel(this.get('to')));
        }
    },

    parse: function (data, xhr) {
        if (false === data.from instanceof shipcloud.AddressModel) {
            data.from = new shipcloud.AddressModel(data.from);
        }

        if (false === data.to instanceof shipcloud.AddressModel) {
            data.to = new shipcloud.AddressModel(data.to);
        }

        return data;
    }
});

shipcloud.ShipmentCollection = Backbone.Collection.extend({
    model: shipcloud.ShipmentModel,

    parse: function (data) {
        // Assert that everything is a shipcloud.ShipmentModel
        for (var pos in data) {
            if (!data.hasOwnProperty(pos)) {
                continue;
            }

            if (false === data[pos] instanceof shipcloud.ShipmentModel) {
                data[pos] = new shipcloud.ShipmentModel(data[pos]);
            }

            this.push(data[pos]);
        }
    }
});

shipcloud.ShipmentView = wp.Backbone.View.extend({
    tagName  : 'div',
    className: 'label widget',
    template : wp.template('shipcloud-shipment'),

    initialize: function() {
        this.listenTo(this.model, 'change', this.render);
        this.render();
    }
});

shipcloud.shipments = new shipcloud.ShipmentCollection();
