;var wcsc = wcsc || {};

wcsc.OrderBulkActions = function (submitButton) {
  // jQuery and self reference as usual.
  var $ = jQuery;
  var self = this;

  // Protected fields
  this.bulkId = 'wcsc_order_bulk_label';
  this.bulkScreen = '#wcsc-order-bulk-labels';
  this.bulkActionTemplate = 'shipcloud-bulk-action-items';
  this.$submitButton = $(submitButton);
  this.pickupRequestId = 'shipcloud_create_pickup_request';
  this.pickupRequestTemplateId = '#shipcloud-pickup-request';

  this.main = function () {
    self.$submitButton.click(self.handleSubmit);
    $('#wcsc_template').change(self.handleTemplateSwitch);
    $('#shipcloud_add_customs_declaration_bulk').click(self.showCustomsDeclarationForm);
    $('#wscs_order_bulk_pdf').click(self.submitBulkForm);
  };

  this.handleSubmit = function (e) {
    var n = $(this).attr('id').substr(2);
    var entryname = $('select[name="' + n + '"]').val();

    if (!_.contains([self.bulkId, self.pickupRequestId], entryname)) {
      return;
    }

    e.preventDefault();

    if (!self.hasOrdersSelected()) {
      return;
    }

    self.hideBulkActionTemplates();

    switch (entryname) {
      case self.bulkId:
        self.setBulk();
        break;
      case self.pickupRequestId:
        self.showPickupForm();
        break;
      default:
        return;
    }

    return false;
  };

  this.handleTemplateSwitch = function () {
    var data = $(':selected', this).data();

    for (var key in data) {
      $('input[name=wcsc_' + key + ']').val(data[key]);
    }

    if (data['carrier']) {
      $('select[name=wcsc_carrier]').val(data['carrier']);
    }
  };

  this.emptyTitles = function (templateId) {
    $('.order-id-list', templateId).html('');
    // switch (action) {
    //   case 'label-creation':
    //     $('.order-id-list', self.bulkScreen).html('');
    //     break;
    //   case 'pickup-request':
    //     $('.order-id-list', self.pickupRequestTemplateId).html('');
    //     break;
    // }
  };

  this.hideBulkActionTemplates = function () {
    $(self.bulkScreen).hide();
    $(self.pickupRequestTemplateId).hide();
  }

  this.populateTitles = function (templateId) {
    self.emptyTitles(templateId);

    var template = wp.template(self.bulkActionTemplate);

    $('tbody th.check-column input[type="checkbox"]').each(function () {
      if (!$(this).prop('checked')) {
        return;
      }

      var data = {
        'id': $(this).val(),
        'title': '#' + $(this).val()
      };

      $('.order-id-list', self.bulkScreen).append(template(data));
    });
  };

  this.hasOrdersSelected = function () {
    var ithasOrdersSelected = false;

    $('tbody th.check-column input[type="checkbox"]').each(function () {
      if ($(this).prop('checked')) {
        ithasOrdersSelected = true;
      }
    });

    return ithasOrdersSelected;
  };

  this.setBulk = function () {
    self.populateTitles(self.bulkScreen);

    $('> td', self.bulkScreen).attr('colspan', $('th:visible, td:visible', '.widefat:first thead').length);
    // Insert the editor at the top of the table with an empty row above to maintain zebra striping.
    $('table.wp-list-table.widefat > tbody').prepend($(self.bulkScreen)).prepend('<tr class="hidden"></tr>');
    $(self.bulkScreen).show();
    $('.shipcloud-carrier-select', self.bulkScreen).shipcloudMultiSelect(wcsc_carrier);

    $('button.cancel', self.bulkScreen).click(self.hide);

    $('html, body').animate({scrollTop: 0}, 'fast');
  };

  this.showPickupForm = function () {
    self.populateTitles(self.pickupRequestTemplateId);
    var template = wp.template(self.bulkActionTemplate);

    $('tbody th.check-column input[type="checkbox"]').each(function () {
      if (!$(this).prop('checked')) {
        return;
      }

      var data = {
        'id': $(this).val(),
        'title': '#' + $(this).val()
      };

      $('.order-id-list', self.pickupRequestTemplateId).append(template(data));
    });

    $('> td', self.pickupRequestTemplateId).attr('colspan', $('th:visible, td:visible', '.widefat:first thead').length);
    // Insert the editor at the top of the table with an empty row above to maintain zebra striping.
    $('table.wp-list-table.widefat > tbody').prepend($(self.pickupRequestTemplateId)).prepend('<tr class="hidden"></tr>');

    $('.shipcloud-use-different-pickup-address').click(function () {
      $('#shipcloud-pickup-request .shipcloud-different-pickup-address').toggle();
    });

    $('select[name="pickup_address[country]"]').select2();
    $('.shipcloud__pickup_date_and_time').show();
    $(self.pickupRequestTemplateId).show();
  };

  this.showCustomsDeclarationForm = function () {
    var $form = $('.customs-declaration--definition');
    if ($form.is(':visible')) {
      $('input[name="customs_declaration[shown]"]').val('false');
    } else {
      $('input[name="customs_declaration[shown]"]').val('true');
    }
    $form.toggle();
  };

  this.submitBulkForm = function (event) {
    event.preventDefault();
    $('#wscs_order_bulk_pdf').attr( 'disabled', 'disabled' );

    if (
      !$('input[name="shipment[additional_services][advance_notice][email]"]').is(':visible') ||
      $('input[name="shipment[additional_services][advance_notice][email_checkbox]"]:checked').length == 0
    ) {
      $('input[name="shipment[additional_services][advance_notice][email_checkbox]"]').val('');
    }

    if (
      !$('input[name="shipment[additional_services][advance_notice][phone]"]').is(':visible') ||
      $('input[name="shipment[additional_services][advance_notice][phone_checkbox]"]:checked').length == 0
    ) {
      $('input[name="shipment[additional_services][advance_notice][phone_checkbox]"]').val('');
    }

    if (
      !$('input[name="shipment[additional_services][advance_notice][sms]"]').is(':visible') ||
      $('input[name="shipment[additional_services][advance_notice][sms_checkbox]"]:checked').length == 0
    ) {
      $('input[name="shipment[additional_services][advance_notice][sms_checkbox]"]').val('');
    }

    if ( !$('input[name="pickup[pickup_earliest_date]"]').is(':visible') ) {
      $('input[name="pickup[pickup_earliest_date]"]').val('');
    }
    if ( !$('input[name="pickup[pickup_latest_date]"]').is(':visible') ) {
      $('input[name="pickup[pickup_latest_date]"]').val('');
    }


    this.form.submit();
  };

  this.hide = function () {
    $(self.bulkScreen).hide();
  };

  this.main();
};

jQuery(function ($) {
  wcsc.listenBulkActions = new wcsc.OrderBulkActions($('#doaction, #doaction2'));
});
