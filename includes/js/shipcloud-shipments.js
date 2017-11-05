;shipcloud = shipcloud || {};

shipcloud.AddressModel = Backbone.Model.extend({
    defaults: {
        'id'        : null,
        'company'   : null,
        'first_name': null,
        'last_name' : null,
        'street'    : null,
        'street_no' : null,
        'care_of'   : null,
        'zip_code'  : null,
        'city'      : null,
        'state'     : null,
        'country'   : null,
        'phone'     : null
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

    destroy: function () {
        var self = this;

        jQuery.post(
            ajaxurl,
            {
                'action'     : 'shipcloud_delete_shipment',
                'shipment_id': this.get('id')
            },
            function (response) {
                var result = JSON.parse(response);

                if (result.status === 'ERROR') {
                    print_errors(result.errors);

                    return;
                }

                self.trigger('destroy');
            }
        );
    },

    parse: function (data) {
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
    tagName: 'div'
});

shipcloud.ShipmentView = wp.Backbone.View.extend({
    tagName   : 'div',
    className : 'label widget',
    id        : function () {
        return 'shipment-' + this.model.get('id');
    },
    template  : wp.template('shipcloud-shipment'),
    controller: null,

    initialize: function () {
        this.listenTo(this.model, 'change', this.render);
        this.listenTo(this.model, 'destroy', this.remove);
        this.render();
    },

    events: {
        'click .shipcloud_create_label'   : 'createAction',
        'click .wcsc-edit-shipment'       : 'editAction',
        'click .shipcloud_delete_shipment': 'deleteAction'
    },

    remove: function () {
        var self = this;
        this.$el.fadeOut(500, function () {
            wp.Backbone.View.prototype.remove.call(self);
        });
    },

    // Render and open widget / keep open.
    open: function () {
        this.$el.find('.widget-inside').show();
    },

    $loader: function () {
        return jQuery(this.$el.find('.loading-overlay'));
    },

    // Create label for shipment.
    createAction: function () {
        this.$loader().show();
        this.model.set('label_url', 'example.org');
    },

    editAction: function () {
        if (false === this.views.hasOwnProperty('edit')) {
            this.views['edit'] = new shipcloud.ShipmentEditView({
                model: this.model,
                el   : this.$el.find('.widget-content')
            });
        }

        this.views['edit'].render();
    },

    // Extending render so that open widgets are kept open on redrawing.
    render: function () {
        var wasVisible = this.$el.find('.widget-inside').is(':visible');
        wp.Backbone.View.prototype.render.call(this);

        if (wasVisible) {
            this.open();
        }
    },


    deleteAction: function () {
        var self = this;

        jQuery('#ask-delete-shipment').dialog({
            'dialogClass': 'wcsc-dialog wp-dialog',
            'modal'      : true,
            'buttons'    : {
                'yes': {
                    text : wcsc_translate.yes,
                    click: function () {
                        self.$loader().show();

                        //shipcloud_delete_shipment(this.model.get('order_id'), this.model.get('shipment_id'));
                        jQuery(this).dialog('close');
                        self.model.destroy();

                        self.$loader().hide();
                    }
                },
                'no' : {
                    text : wcsc_translate.no,
                    click: function () {
                        jQuery(this).dialog('close');
                    }
                }
            }
        });
    }
});

shipcloud.ShipmentEditView = wp.Backbone.View.extend({
    tagName : 'div',
    template: wp.template('shipcloud-shipment-edit')
});

shipcloud.shipments = new shipcloud.ShipmentCollection();
