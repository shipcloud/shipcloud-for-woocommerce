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
        'to'                : new shipcloud.AddressModel(),
        'from'              : new shipcloud.AddressModel(),
        'package'           : {},
        'carrier'           : null,
        'service'           : null,
        'reference_number'  : null,
        'notification_email': null
    }
});

shipcloud.ShipmentCollection = Backbone.Collection.extend({
    model: shipcloud.ShipmentModel
});

shipcloud.shipments = new shipcloud.ShipmentCollection();
