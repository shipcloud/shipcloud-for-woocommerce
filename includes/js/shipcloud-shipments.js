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
        'state'     : null,
        'city'      : null,
        'country'   : null,
        'phone'     : null,
        'care_of'   : null
    },


    getFullCity: function () {
        return (this.get('zip_code') + ' ' + this.get('city')).trim();
    },

    getFullName: function () {
        return (this.get('first_name') + ' ' + this.get('last_name')).trim();
    },

    getFullStreet: function () {
        return (this.get('street') + ' ' + this.get('street_no')).trim();
    },

    getTitle: function () {
        return _.filter([
            this.get('company'),
            this.getFullName()
        ]).join(', ');
    }
});

shipcloud.PackageModel = Backbone.Model.extend({
    defaults: {
        'weight': null,
        'length': null,
        'width' : null,
        'height': null,
        'type'  : 'parcel'
    },

    getTitle: function () {
        return this.get('width')
            + 'x' + this.get('height')
            + 'x' + this.get('length')
            + 'cm'
            + ' ' + this.get('weight')
            + 'kg';
    }
});

shipcloud.ShipmentModel = Backbone.Model.extend({
    defaults: {
        'id'                        : null,
        'to'                        : new shipcloud.AddressModel(),
        'from'                      : new shipcloud.AddressModel(),
        'created_at'                : null,
        'package'                   : new shipcloud.PackageModel(),
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

        if (false === this.get('package') instanceof shipcloud.AddressModel) {
            this.set('package', new shipcloud.PackageModel(this.get('package')));
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
    },

    getTitle: function () {
        return _.filter([this.get('carrier'), this.get('package').getTitle()]).join(' ');
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

shipcloud.ShipmentsView = wp.Backbone.View.extend({
    tagName  : 'div',
    className: 'label widget'
});

shipcloud.ShipmentView = wp.Backbone.View.extend({
    tagName   : 'div',
    className : 'label widget',
    template  : wp.template('shipcloud-shipment'),
    controller: null,

    initialize: function () {
        this.listenTo(this.model, 'change', this.render);
        this.render();

        var $ = jQuery;
    },

    events: {
        'click .shipcloud_delete_shipment': 'deleteAction'
    },

    deleteAction: function () {
        this.$el.fadeOut();
    }
});

shipcloud.shipments = new shipcloud.ShipmentCollection();
