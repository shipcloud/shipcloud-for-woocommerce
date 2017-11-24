;shipcloud = shipcloud || {};
wcsc_translate = wcsc_translate || {};

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

    getData: function () {
        var json = _.clone(this.attributes);
        for (var attr in json) {
            if (!json.hasOwnProperty(attr)) {
                continue;
            }
            if ((json[attr] instanceof Backbone.Model) || (json[attr] instanceof Backbone.Collection)) {
                json[attr] = json[attr].toJSON();
            }
        }
        return json;
    },

    createLabel: function (options) {
        // Clone to store shipment_id (BC)
        var self = this;
        var data = _.clone(this);

        // BC for deprecated logic in 'shipcloud_create_shipment_label' handler
        data.set('shipment_id', this.get('id'));

        wp.ajax.send(
            'shipcloud_create_shipment_label',
            _(
                {
                    'data'   : data.getData(),
                    'success': self.handleCreateSuccess.bind(self)
                }
            ).extend(options)
        );
    },

    handleCreateSuccess: function (response) {
        this.clear({silent: true});
        this.set(this.parse(response.data));
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
    }
    ,

    parse: function (data) {
        if (data.hasOwnProperty('from')) {
            data.from = new shipcloud.AddressModel(data.from);
        }

        if (data.hasOwnProperty('to')) {
            data.to = new shipcloud.AddressModel(data.to);
        }

        if (data.hasOwnProperty('package')) {
            data.package = new shipcloud.PackageModel(data.package);
        }

        return data;
    }
    ,

    getTitle: function () {
        return _.filter([this.get('carrier'), this.get('package').getTitle()]).join(' ');
    }
})
;

shipcloud.ShipmentCollection = Backbone.Collection.extend({
    model: shipcloud.ShipmentModel
});

shipcloud.ShipmentsView = wp.Backbone.View.extend({
    tagName   : 'div',
    initialize: function () {
        this.listenTo(this.model, 'add', this.addShipment);

        this.model.each(function (shipment) {
            this.views.add(new shipcloud.ShipmentView({model: shipment, id: shipment.get('id')}));
        }, this);
    },

    addShipment: function (shipment) {
        var shipmentView = new shipcloud.ShipmentView({model: shipment, id: shipment.get('id')});

        this.views.add(shipmentView, {at: 0});
        shipmentView.$el.hide();
        shipmentView.render();

        this.$el.prepend(shipmentView.$el);
        shipmentView.fadeIn();
    }
});

shipcloud.ShipmentView = wp.Backbone.View.extend({
    tagName           : 'div',
    className         : 'label widget',
    id                : function () {
        return 'shipment-' + this.model.get('id');
    },
    template          : wp.template('shipcloud-shipment'),
    controller        : null,
    colorHighlight    : '#90ee90',
    colorHeadingNormal: '#fafafa',

    initialize: function () {
        this.listenTo(this.model, 'change', this.render);
        this.listenTo(this.model, 'destroy', this.remove);
    },

    events: {
        'click .shipcloud_create_label'   : 'createAction',
        'click .wcsc-edit-shipment'       : 'editAction',
        'click .shipcloud_delete_shipment': 'deleteAction'
    },

    fadeIn: function () {
        this.open();
        this.$el.find('.widget-top').css('background-color', this.colorHighlight);
        this.$el.fadeIn('slow', function () {
            jQuery(this).find('.widget-top').animate({backgroundColor: this.colorHeadingNormal}, 'slow');
        });
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
        var self = this;

        this.open();

        this.$loader().show();
        this.model.createLabel({
            'error': this.createError.bind(this),
            'success': function (response) {
                console.log(response);
                self.model.handleCreateSuccess(response);
                self.$loader().hide();
            }
        });
    },

    createError: function (response) {
        this.$loader().hide();
        alert(_(response).pluck('message'));
    },

    editAction: function () {
        this.views['edit'] = new shipcloud.ShipmentEditView({
            model : this.model,
            el    : this.$el.find('.widget-content'),
            parent: this
        });

        this.views['edit'].render({el: this.$el});
    },

    // Extending render so that open widgets are kept open on redrawing.
    render: function () {
        var wasVisible = this.$el.find('.widget-inside').is(':visible');
        wp.Backbone.View.prototype.render.call(this);

        if (wasVisible) {
            this.open();
        }
    },

    scrollTo: function () {
        if (this.$el.offset().top < window.scrollY) {
            // Out of viewport so we scroll up a bit.
            jQuery('html, body').animate({
                scrollTop: this.$el.offset().top - 50
            }, 700);
        }
    },

    deleteAction: function () {
        var self = this;

        this.open();

        jQuery('#ask-delete-shipment').dialog({
            'dialogClass': 'wcsc-dialog wp-dialog',
            'modal'      : true,
            'buttons'    : {
                'yes': {
                    text : wcsc_translate.yes,
                    click: function () {
                        self.$loader().show();

                        jQuery(this).dialog('close');
                        self.model.destroy();
                    }
                },
                'no' : {
                    text : wcsc_translate.no,
                    click: function (e) {
                        jQuery(this).dialog('close');

                        e.stopPropagation();
                        e.preventDefault();
                    }
                }
            }
        });
    }
});

shipcloud.ShipmentEditView = wp.Backbone.View.extend({
    tagName : 'div',
    template: wp.template('shipcloud-shipment-edit'),
    parent  : null,

    initialize: function (args) {
        this.parent = args.parent;
    },

    events: {
        'click .wcsc-edit-abort'   : 'backToParent',
        'click .wcsc-save-shipment': 'saveAction'
    },

    backToParent: function () {
        this.remove();
        this.parent.scrollTo();
        this.parent.render();
    },

    abortAction: function () {
        this.backToParent();
    },

    errorAction: function (response) {
        alert(_(response.responseJSON.data).pluck('message').join('\n'));
        this.parent.$loader().hide();
    },

    render: function () {
        wp.Backbone.View.prototype.render.call(this);

        if (this.model.get('from').get('country')) {
            // Sender country set so we select it.
            this.$el.find('select[name="shipment[from][country]"]').val(this.model.get('from').get('country'));
        }

        if (this.model.get('to').get('country')) {
            // Recipient country set so we select it.
            this.$el.find('select[name="shipment[to][country]"]').val(this.model.get('to').get('country'));
        }
    },

    getData: function () {
        return {
            'id'  : this.model.get('id'),
            'to'  : this.$el.find('[to]').serializeObject(),
            'from': this.$el.find('[from]').serializeObject()
        };
    },

    saveAction: function () {
        this.parent.$loader().show();

        wp.ajax.send(
            'shipcloud_label_update',
            {
                'data'   : this.$el.find('input,select').serializeObject(),
                'success': this.successAction.bind(this),
                'error'  : this.errorAction.bind(this)
            }
        );
    },

    successAction: function (data) {
        this.parent.$loader().hide();

        this.model.set(this.model.parse(data));
        this.parent.scrollTo();
        this.remove(); // Parent will rerender itself when the model changes.
    }
})
;

shipcloud.shipments = new shipcloud.ShipmentCollection();
