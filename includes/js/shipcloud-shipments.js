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

shipcloud.PickupRequestModel = Backbone.Model.extend({
  defaults: {
    'id': null,
    'carrier': null,
    'pickup_time': null,
    'pickup_address': null
  },

  getPickupTimeAsRange: function () {
    if (this.get('pickup_time')) {
      var options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: 'numeric',
        minute: '2-digit',
        timeZone: 'Europe/Berlin'
      };

      var earliest = new Date(this.get('pickup_time').earliest);
      var latest = new Date(this.get('pickup_time').latest);

      return new Intl.DateTimeFormat('de-DE', options).format(earliest) +
        ' - ' +
        new Intl.DateTimeFormat('de-DE', options).format(latest);
    }
    return null;
  },

  getPickupTimeAsHash: function (key) {
    var pickupTime;

    if (key === 'earliest') {
      pickupTime = new Date(this.get('pickup_time').earliest);
    } else {
      pickupTime = new Date(this.get('pickup_time').latest);
    }

    var year = pickupTime.getFullYear();
    var month = pickupTime.getMonth() + 1;
    var day = pickupTime.getDate();
    var hours = pickupTime.getHours();
    var minutes = pickupTime.getMinutes();

    return {
      date: year + '-' + ((month < 10) ? '0' + month : month) + '-' + ((day < 10) ? '0' + day : day),
      hours: ((hours < 10) ? '0' + hours : hours),
      minutes: ((minutes < 10) ? '0' + minutes : minutes)
    };
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
        'tracking_url'              : null,
        'carrier_tracking_url'      : null,
        'additional_services'       : null,
        'pickup_request'            : null,
        'customs_declaration'       : null,
        'label'                     : null,
    },

  allowedAdditionalServices: function () {
    var wcsc_data = wcsc_carrier['data'];
    var additional_services_for_carrier = {};

    _.each(wcsc_data, function (carrier) {
      additional_services_for_carrier[carrier.name] = carrier.additional_services;
    });

    // console.log('additional_services_for_carrier: ' + JSON.stringify(additional_services_for_carrier));

    return additional_services_for_carrier;
  },

    getAdditionalServiceData: function (additional_service) {
      if ( !this.hasAdditionalService(additional_service) ) {
        return;
      }
      var data = _.findWhere(this.get('additional_services'), {name: additional_service});
      if ( !data ) {
        return
      }
      return data.properties;
    },

    getCustomsDeclarationContentsTypeName: function(contents_type_name) {
      return shipcloud_customs_declaration_contents_types[contents_type_name];
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
        var data = this.clone();

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

    hasAdditionalService: function (name) {
      var additional_services = _.map(this.get('additional_services'), function(additional_service){ return additional_service.name; });
      return _.contains(additional_services, name);
    },

    destroy: function (force_destroy = false) {
        var self = this;
        var shipment_id = this.get('id');
        var overlay = jQuery('#' + shipment_id + ' .loading-overlay');

        if ( force_destroy ) {
          jQuery('#ask-delete-shipment').dialog({
            'dialogClass': 'wcsc-dialog wp-dialog',
            'modal'      : true,
            'buttons'    : {
                'yes': {
                  text : wcsc_translate.yes,
                  click: function () {
                    jQuery(this).dialog('close');

                    jQuery.post(
                      ajaxurl,
                      {
                        'action'     : 'shipcloud_force_delete_shipment',
                        'shipment_id': shipment_id
                      },
                      function (response) {
                        var result = JSON.parse(response);

                        if (result.status === 'ERROR') {
                          self.printErrors(result.errors);
                        } else {
                          self.trigger('destroy');
                        }
                      }
                    );
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

          jQuery('#shipment-center').find('.info .error').remove();
          overlay.hide();
        } else {
          jQuery.post(
              ajaxurl,
              {
                  'action'     : 'shipcloud_delete_shipment',
                  'shipment_id': this.get('id')
              },
              function (response) {
                  var result = JSON.parse(response);

                  if (result.status === 'ERROR') {
                      self.printErrors(result.errors);

                      var error_element = jQuery('#shipment-center').find('.info .error');
                      html = error_element.html();
                      html += '<a href="#" class="shipcloud_force_delete_shipment">';
                      html += wcsc_translate.force_delete_dialog;
                      html += '</a>';
                      error_element.html(html);
                      error_element.find('.shipcloud_force_delete_shipment').on('click', function (e) {
                        e.stopPropagation();
                        e.preventDefault();
                        self.destroy(true);
                      });
                  } else {
                    self.trigger('destroy');
                  }
              }
          );
        }
    },

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

        if (data.hasOwnProperty('pickup_request')) {
            data.pickup_request = new shipcloud.PickupRequestModel(data.pickup_request);
            data.pickup_request.set({
              pickup_address: new shipcloud.AddressModel(data.pickup_request.get('pickup_address'))
            });
        } else if (data.hasOwnProperty('pickup')) {
          data.pickup_request = new shipcloud.PickupRequestModel(data.pickup);
          delete data.pickup;
        }

        return data;
    },

    getTitle: function () {
        return _.filter([this.get('carrier'), this.get('package').getTitle()]).join(' ');
    },

    getCarrierTrackingUrl: function () {
      var carrierTrackingUrl;
      switch (this.get('carrier')) {
        case 'dhl':
        case 'dhl_express':
          carrierTrackingUrl = 'https://nolp.dhl.de/nextt-online-public/set_identcodes.do?idc=' + this.get('carrier_tracking_no') + '&rfn=&extendedSearch=true';
          break;
        case 'dpd':
          carrierTrackingUrl = 'https://tracking.dpd.de/parcelstatus?query=' + this.get('carrier_tracking_no') + '&locale=de_DE';
          break;
        case 'fedex':
          carrierTrackingUrl = 'https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber=' + this.get('carrier_tracking_no');
          break;
        case 'gls':
          carrierTrackingUrl = 'https://gls-group.eu/DE/de/paketverfolgung?match=' + this.get('carrier_tracking_no');
          break;
        case 'go':
          carrierTrackingUrl = 'https://order.general-overnight.com/ax4/control/customer_service?shId=' + this.get('carrier_tracking_no') + '&hash=JMJyKOfE1v&lang=de&ActionCollectInformation=GO%21';
          break;
        case 'hermes':
          carrierTrackingUrl = 'https://tracking.hermesworld.com/?TrackID=' + this.get('carrier_tracking_no');
          break;
        case 'iloxx':
          carrierTrackingUrl = 'http://www.iloxx.de/net/einzelversand/tracking.aspx?ix=' + this.get('carrier_tracking_no');
          break;
        case 'tnt':
          carrierTrackingUrl = 'https://www.tnt.com/express/de_de/site/home/applications/tracking.html?cons=' + this.get('carrier_tracking_no') + '&searchType=CON';
          break;
        case 'ups':
          carrierTrackingUrl = 'http://wwwapps.ups.com/WebTracking/processInputRequest?sort_by=status&' + this.get('carrier_tracking_no') + '=1&TypeOfInquiryNumber=T&loc=de_DE&InquiryNumber1=' + this.get('carrier_tracking_no') + '&track.x=' + this.get('carrier_tracking_no') + '&track.y=0';
          break;
      }
      this.set('carrier_tracking_url', carrierTrackingUrl);
      return carrierTrackingUrl;
    },

    printErrors: function (errors) {
      if (typeof errors === 'string') {
        // Received single error message, so we convert it to the expected format.
        errors = [errors];
      }

      var html = '<div class="error"><ul class="errors">';
      errors.forEach(function (entry) {
        html += '<li>' + entry + '</li>';
      });
      html += '</ul></div>';

      jQuery('#shipment-center').find('.info').fadeIn().html(html);
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
        'click .shipcloud_delete_shipment': 'deleteAction',
        'click .shipcloud-open-pickup-request-form': 'showPickupRequestForm'
    },

    fadeIn: function () {
        var self = this;

        this.open();
        this.$el.find('.widget-top').css('background-color', this.colorHighlight);
        this.$el.fadeIn('slow', function () {
            self.$el.find('.widget-top').animate({backgroundColor: self.colorHeadingNormal}, 'slow');
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

  showPickupRequestForm: function () {
    this.views['pickup-request'] = new shipcloud.ShipmentPickupRequestView({
      model: this.model,
      el: this.$el.find('.widget-content'),
      parent: this
    });

    this.views['pickup-request'].render({el: this.$el});
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

shipcloud.ShipmentAdditionalServicesView = wp.Backbone.View.extend({
  tagName: 'div',
  template: wp.template('shipcloud-shipment-additional-services'),
  controller: null,
  attributes: {
    activated_additional_services: []
  },

  render: function (shipmentId = false) {
    wp.Backbone.View.prototype.render.call(this);
    this.addHandlers(shipmentId);
    jQuery( document.body ).trigger( 'init_tooltips' );
  },

  addAdditionalService: function (data) {
    for (var entry in data) {
      switch (entry) {
        case 'advance_notice':
          jQuery("input[name='shipment[additional_services][advance_notice][email]']").val(data.advance_notice.email);
          jQuery("input[name='shipment[additional_services][advance_notice][phone]']").val(data.advance_notice.phone);
          jQuery("input[name='shipment[additional_services][advance_notice][sms]']").val(data.advance_notice.sms);
        break;
        case 'cash_on_delivery':
          var codData = data.cash_on_delivery;
          jQuery("input[name='shipment[additional_services][cash_on_delivery][amount]']").val(codData.amount);
          jQuery("input[name='shipment[additional_services][cash_on_delivery][currency]']").val(codData.currency);
          jQuery("input[name='shipment[additional_services][cash_on_delivery][reference1]']").val(codData.reference1);
          jQuery("input[name='shipment[additional_services][cash_on_delivery][bank_account_holder]']").val(codData.bank_account_holder);
          jQuery("input[name='shipment[additional_services][cash_on_delivery][bank_name]']").val(codData.bank_name);
          jQuery("input[name='shipment[additional_services][cash_on_delivery][bank_account_number]']").val(codData.bank_account_number);
          jQuery("input[name='shipment[additional_services][cash_on_delivery][bank_code]']").val(codData.bank_code);
          break;
      }
    }
  },

  addAdditionalServicesFromModel: function (additionalServices) {
    var view = this;
    var model = view.model;

    _.each(additionalServices, function (additionalService) {
      var json = {};
      var propName = additionalService.name;
      json[propName] = model.getAdditionalServiceData(propName);
      view.addAdditionalService(json);
      view.activateAdditionalService(propName);
    });
  },

  addHandlers: function (shipmentId = false) {
    var $ = jQuery;
    var view = this;

    var prefix = '';
    if (shipmentId) {
      prefix = '#' + shipmentId + ' ';
    }

    /**
     * add handlers for displaying context blocks when selecting an additional service
     */
    $(function ($) {
      var carrier = $(prefix + "select[name='shipcloud_carrier']").val() || $(prefix + "input[name='shipment[carrier]']").val();
      view.handleAdditionalServices(carrier);
    });

    $("select[name='shipcloud_carrier']").change(function () {
      view.handleAdditionalServices($(this).val());
    });

    // get all additional services available and add onChange handlers
    var wcsc_data = wcsc_carrier['data'];
    var all_additional_services = [];
    _.each(wcsc_data, function (carrier) {
      all_additional_services.push(carrier.additional_services);
    });
    all_additional_services = _.uniq(_.flatten(all_additional_services));

    _.each(all_additional_services, function (additional_service) {
      $(prefix + "input[name='shipment[additional_services][" + additional_service + "][checked]']").change(function () {
        var carrier = $(prefix + "select[name='shipcloud_carrier']").val() || $(prefix + "input[name='shipment[carrier]']").val();
        switch(additional_service) {
          case 'advance_notice':
            view.handleAdvanceNotice(carrier);
            break;
          case 'cash_on_delivery':
            view.handleCashOnDelivery(carrier, shipmentId);
            break;
          case 'ups_adult_signature':
          case 'visual_age_check':
            view.handleAgeBasedDelivery(carrier, prefix);
            break;
        }
      });
    });

    $(prefix + "input[name='shipment[additional_services][age_based_delivery][checked]']").change(function () {
      if ($(this).prop('checked')) {
        var carrier = $(prefix + "select[name='shipcloud_carrier']").val() || $(prefix + "input[name='shipment[carrier]']").val();

        switch (carrier) {
          case 'dhl':
            $(prefix + '.shipcloud_visual_age_check').fadeIn();
            break;
          case 'ups':
            $(prefix + '.shipcloud_ups_adult_signature').fadeIn();
            break;
        }
      } else {
        $(prefix + '.shipcloud_visual_age_check').fadeOut();
        $(prefix + '.shipcloud_ups_adult_signature').fadeOut();
      }
    });

    $(prefix + "input[name='shipment[additional_services][delivery_date][checked]']").change(function () {
      if ($(this).prop('checked')) {
        $(prefix + '.shipcloud_delivery_date').fadeIn();
        $("input[name='shipment[additional_services][delivery_date][date]']").datepicker({
          dateFormat : 'yyyy-mm-dd',
          defaultDate: +1,
          showButtonPanel: true
        });
      } else {
        $(prefix + '.shipcloud_delivery_date').fadeOut();
      }
    });

    $(prefix + "input[name='shipment[additional_services][delivery_note][checked]']").change(function () {
      if ($(this).prop('checked')) {
        $(prefix + '.shipcloud_delivery_note').fadeIn();
      } else {
        $(prefix + '.shipcloud_delivery_note').fadeOut();
      }
    });

    $(prefix + "input[name='shipment[additional_services][delivery_time][checked]']").change(function () {
      if ($(this).prop('checked')) {
        $(prefix + '.shipcloud_delivery_time').fadeIn();
      } else {
        $(prefix + '.shipcloud_delivery_time').fadeOut();
      }
    });

    $(prefix + "input[name='shipment[additional_services][drop_authorization][checked]']").change(function () {
      if ($(this).prop('checked')) {
        $(prefix + '.shipcloud_drop_authorization').fadeIn();
      } else {
        $(prefix + '.shipcloud_drop_authorization').fadeOut();
      }
    });
  },

  activateAdditionalService: function (additional_service) {
    this.attributes.activated_additional_services.push(additional_service);
  },

  deactivateAdditionalService: function (additional_service) {
    this.attributes.activated_additional_services.pop(additional_service);
  },

  handleAdditionalServices: function (carrier, shipmentId = false) {
    var $ = jQuery;
    var self = this;
    var prefix = '';
    if (shipmentId) {
      prefix = '#' + shipmentId;
    }
    var model = this.model;

    var allowed_additional_services_for_carrier =
      model.allowedAdditionalServices()[carrier];
    // console.log('allowed_additional_services_for_carrier: ' + JSON.stringify(allowed_additional_services_for_carrier));

    // var all_additional_services = wcsc_carrier['additional_services'];
    // console.log('all_additional_services: ' + JSON.stringify(all_additional_services));

    // first hide all additional services
    $(prefix + "[class^='shipcloud_additional_service shipcloud_additional_service__']").hide();

    console.log('carrier: ' + carrier);
    // now show additional services for specific carrier
    if (allowed_additional_services_for_carrier.length > 0) {
      $(prefix + '.shipcloud_additional_service__no_additional_services').hide();
      _.each(allowed_additional_services_for_carrier, function (additional_service) {
        var addserv = prefix + '.shipcloud_additional_service__' + additional_service;
        console.log('additional_service: ' + additional_service);
        // console.log('addserv: ' + addserv);
        $(addserv).show();

        // handle available additional services with side conditions (based on carrier)
        switch(additional_service) {
          case 'advance_notice':
            var wantsCarrierEmailNotification = $("input[name='wants_carrier_email_notification']");
            var advanceNoticeDeliveryCheckbox = $(prefix + " input[name='shipment[additional_services][advance_notice][checked]']");
            if (false == shipmentId && 'true' == wantsCarrierEmailNotification.val()) {
              advanceNoticeDeliveryCheckbox.prop('checked', 'checked');
              advanceNoticeDeliveryCheckbox.trigger("change");
            } else if ( !model.hasAdditionalService('advance_notice')) {
              advanceNoticeDeliveryCheckbox.prop('checked', false);
            }

            self.handleAdvanceNotice(carrier);
            break;
          case 'cash_on_delivery':
            self.handleCashOnDelivery(carrier, shipmentId);
            break;
          case 'ups_adult_signature':
          case 'visual_age_check':
            self.handleAgeBasedDelivery(carrier, prefix);
            break;
        }

      });
    } else {
      $(prefix + '.shipcloud_additional_service__no_additional_services').show();
    }
  },

  handleAdvanceNotice: function (carrier, shipmentId = false) {
    var $ = jQuery;
    var prefix = '';
    if (shipmentId) {
      prefix = '#' + shipmentId + ' .shipcloud_additional_service__advance_notice ';
    } else {
      prefix = '.shipcloud_additional_service__advance_notice ';
    }

    if (_.contains(this.model.allowedAdditionalServices()[carrier], 'advance_notice')) {
      switch (carrier) {
        case 'cargo_international':
          $(prefix + '.shipcloud_advance_notice_email').hide();
          $(prefix + '.shipcloud_advance_notice_phone').show();
          $(prefix + '.shipcloud_advance_notice_sms').hide();
          break;
        case 'dhl':
          $(prefix + '.shipcloud_advance_notice_email').show();
          $(prefix + '.shipcloud_advance_notice_phone').hide();
          $(prefix + '.shipcloud_advance_notice_sms').hide();
          break;
        case 'dpd':
          $(prefix + '.shipcloud_advance_notice_email').show();
          $(prefix + '.shipcloud_advance_notice_phone').hide();
          $(prefix + '.shipcloud_advance_notice_sms').show();
          break;
        case 'gls':
          $(prefix + '.shipcloud_advance_notice_email').show();
          $(prefix + '.shipcloud_advance_notice_phone').hide();
          $(prefix + '.shipcloud_advance_notice_sms').show();
          break;
      }

      var advanceNoticeCheckbox = $(prefix + " input[name='shipment[additional_services][advance_notice][checked]']");
      if (advanceNoticeCheckbox.prop('checked')) {
        $(prefix + ' .shipcloud_additional_service__advance_notice--content').fadeIn();
      } else {
        $(prefix + ' .shipcloud_additional_service__advance_notice--content').fadeOut();
      }

      advanceNoticeCheckbox.change(function () {
        // when user (un)checks advance_notice checkbox set input wants_carrier_email_notification accordingly
        $("input[name='wants_carrier_email_notification']").val($(this).prop('checked'));
      });
    }
  },

  handleAgeBasedDelivery: function (carrier, prefix) {
    var $ = jQuery;

    $(prefix + '.shipcloud_additional_service__age_based_delivery').show();

    var ageBasedDeliveryCheckbox = $(prefix + "input[name='shipment[additional_services][age_based_delivery][checked]']");
    if (ageBasedDeliveryCheckbox.prop('checked')) {
      switch (carrier) {
        case 'dhl':
          $(prefix + '.shipcloud_visual_age_check').show();
          $(prefix + '.shipcloud_ups_adult_signature').hide();
          break;
        case 'ups':
          $(prefix + '.shipcloud_visual_age_check').hide();
          $(prefix + '.shipcloud_ups_adult_signature').show();
          break;
      }
    }
  },

  handleCashOnDelivery: function (carrier, shipmentId = false) {
    var $ = jQuery;
    var prefix = '';
    if (shipmentId) {
      prefix = '#' + shipmentId + ' .shipcloud_additional_service__cash_on_delivery';
    } else {
      prefix = '.shipcloud_additional_service__cash_on_delivery';
    }

    if (_.contains(this.model.allowedAdditionalServices()[carrier], 'cash_on_delivery')) {
      $('.shipcloud_additional_service__cash_on_delivery').show();

      var cashOnDeliveryCheckbox = $(prefix + " input[name='shipment[additional_services][cash_on_delivery][checked]']");

      switch (carrier) {
        case 'dhl':
          $(prefix + '--reference1').show();
          $(prefix + '--right').show();
          // this.showAdditionalServiceIfActive('cash_on_delivery', prefix, shipmentId);
          break;
        case 'gls':
          $(prefix + '--reference1').show();
          // don't show bank information
          $(prefix + '--right').hide();
          // this.showAdditionalServiceIfActive('cash_on_delivery', prefix, shipmentId);
          break;
        case 'ups':
          // don't show reference number
          $(prefix + '--reference1').hide();
          // don't show bank information
          $(prefix + '--right').hide();
          // this.showAdditionalServiceIfActive('cash_on_delivery', prefix, shipmentId);
          break;
      }
      this.showAdditionalServiceIfActive('cash_on_delivery', prefix, shipmentId);

      if (cashOnDeliveryCheckbox.prop('checked')) {
        $(prefix + ' .shipcloud_cash_on_delivery').fadeIn();
      } else {
        $(prefix + ' .shipcloud_cash_on_delivery').fadeOut();
      }
    }
  },

  showAdditionalServiceIfActive: function (additionalService, prefix, shipmentId) {
    if (_.contains(this.attributes.activated_additional_services, additionalService)) {
      jQuery((shipmentId) ? prefix : '.parcels ' + " input[name='shipment[additional_services][" + additionalService + "][checked]']").prop('checked', true);

      // only show if not in batch process
      if (!jQuery('#wcsc-order-bulk-labels').length) {
        jQuery(prefix).show();
      }

      jQuery('.shipcloud_additional_service__' + additionalService).show();

      // deactivate additional service after showing, because we're now handling it in JS
      this.deactivateAdditionalService(additionalService);
    }
  }
});

shipcloud.ShipmentCustomsDeclarationView = wp.Backbone.View.extend({
  tagName: 'div',
  template: wp.template('shipcloud-customs-declaration-form'),
  controller: null,

  render: function (shipmentId = false) {
    wp.Backbone.View.prototype.render.call(this);
    // this.addHandlers(shipmentId);
    jQuery( document.body ).trigger( 'init_tooltips' );
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
        'click .wcsc-save-shipment': 'saveAction',
        'click .shipcloud-show-customs-declaration': 'showCustomsDeclaration'
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

    getCarrierData: function (carrier) {
      for (var i in wcsc_carrier['data']) {
        if (!wcsc_carrier['data'].hasOwnProperty(i)) {
          continue;
        }

        if (wcsc_carrier['data'][i].name === carrier) {
          return wcsc_carrier['data'][i];
        }
      }

      return {};
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

        shipcloud.additionalServices = new shipcloud.ShipmentAdditionalServicesView({
          model : this.model,
          el   : '#' + this.model.id + '.label-shipment-additional-services.additional_services'
        });

        shipcloud.additionalServices.addAdditionalServicesFromModel(this.model.get('additional_services'));
        shipcloud.additionalServices.render(this.model.id);

        shipcloud.additionalServices.handleAdditionalServices(this.model.get('carrier'), this.model.id);

        if (this.model.get('customs_declaration')) {
          if (this.model.get('customs_declaration').contents_type) {
            var contents_type_key = this.model.get('customs_declaration').contents_type;
            jQuery('#' + this.model.get('id') + ' select[name="customs_declaration[contents_type]"] option[value="' + contents_type_key + '"]').prop('selected', true);
          }
          if (this.model.get('customs_declaration').items) {
            var modelId = this.model.get('id');
            _.each(this.model.get('customs_declaration').items, function(item) {
              jQuery('#' + modelId + ' select[name="customs_declaration[items][' + item.id + '][origin_country]"] option[value="' + item.origin_country + '"]').prop('selected', true);
            });
          }
          jQuery('#' + this.model.get('id') + ' input[name="customs_declaration[shown]"]').val('true');
        }

        this.renderLabelFormats(jQuery('#' + this.model.get('id') + ' select[name="shipcloud_label_format"]'));
    },

    renderLabelFormats: function (el) {
      var model = this.model;
      el.prop('disabled', 'disabled');
      el.html('');

      el.append('<option value="">' + wcsc_carrier.label['label_formats'].placeholder + '</option>');

      var carrier_data = this.getCarrierData(model.get('carrier'));

      jQuery(carrier_data['label_formats']).each(function (index, value) {
        var label_formats_for_service = value[model.get('service')];

        jQuery(label_formats_for_service).each(function (index, value) {
          if(value == model.get('label').format) {
            el.append(
              '<option value="' + value + '" selected=selected>' + wcsc_carrier.label['label_formats'][value] + '</option>'
            );
          } else {
            el.append(
              '<option value="' + value + '">' + wcsc_carrier.label['label_formats'][value] + '</option>'
            );
          }
        });
      });

      el.prop('disabled', null);
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

        this.$el.find('input[name="shipment[additional_services][cash_on_delivery][currency]"]').removeAttr( 'disabled' )
        this.$el.find('input[name="customs_declaration[currency]"]').removeAttr( 'disabled' )

        wp.ajax.send(
            'shipcloud_label_update',
            {
                'data'   : this.$el.find('select, textarea, :input:not(:hidden), input[type="hidden"]').serializeObject(),
                'success': this.successAction.bind(this),
                'error'  : this.errorAction.bind(this)
            }
        );
    },

    showCustomsDeclaration: function () {
      this.$el.find('.label-shipment-customs-declaration').toggle();
      if (this.$el.find('.label-shipment-customs-declaration').is(':visible')) {
        this.$el.find('input[name="customs_declaration[shown]"]').val('true');
      } else {
        this.$el.find('input[name="customs_declaration[shown]"]').val('false');
      }
    },

    successAction: function (data) {
        this.parent.$loader().hide();

        this.model.set(this.model.parse(data));
        this.parent.scrollTo();
        this.remove(); // Parent will rerender itself when the model changes.
    }
})

shipcloud.ShipmentPickupRequestView = wp.Backbone.View.extend({
  tagName: 'div',
  template: wp.template('shipcloud-shipment-pickup-request'),
  parent: null,

  events: {
    'click .shipcloud-pickup-request-abort': 'closePickupForm',
    'click .shipcloud-create-pickup-request': 'createPickupRequest',
    'click .shipcloud-use-different-pickup-address': 'togglePickupAddress'
  },

  closePickupForm: function () {
    this.remove();
    this.parent.scrollTo();
    this.parent.render();
  },

  createPickupRequest: function () {
    this.parent.$loader().show();

    var data = this.$el.find('input,select,textarea').serializeObject();
    data['id'] = this.model.get('id');

    wp.ajax.send(
      'shipcloud_create_pickup_request',
      {
        'data': data,
        'success': this.successAction.bind(this),
        'error': this.errorAction.bind(this)
      }
    );
  },

  errorAction: function (response) {
    alert(response.data);
    this.parent.$loader().hide();
  },

  initialize: function (args) {
    this.parent = args.parent;
  },

  render: function () {
    wp.Backbone.View.prototype.render.call(this);

    this.$el.find('.shipcloud-pickup-request-table').show();
  },

  successAction: function (response) {
    this.parent.$loader().hide();

    this.model.set(this.model.parse(response.data));
    this.parent.scrollTo();
    this.remove(); // Parent will rerender itself when the model changes.
  },

  togglePickupAddress: function () {
    this.$el.find('.shipcloud-different-pickup-address').toggle();
  }
})
;

shipcloud.shipments = new shipcloud.ShipmentCollection();
