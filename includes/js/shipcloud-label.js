;shipcloud = shipcloud || {};

/**
 * @deprecated 2.0.0
 *
 * @param wrapper
 * @constructor
 */
shipcloud.LabelView = function (wrapper, model, collection) {
    var self = this;
    var $ = jQuery;

    self.$wrapper = $(wrapper);

    self.$toggleButtons = $('.wcsc-edit-shipment', wrapper);

    self.$toggleButtons.length > 0 || console.log('WCSC: Problem - no edit link found for ' + wrapper);

    this.editAction = function () {
        self.toggleScreen();
    };

    this.saveAction = function () {
        self.$wrapper.find('.loading-overlay').show();

        wp.ajax.send(
            'shipcloud_label_update',
            {
                'data'   : self.getData(),
                'success': self.successAction,
                'error'  : function (response) {
                    alert(_(response.responseJSON.data).pluck('message').join('\n'));
                    self.hideSpinner();
                }
            }
        );
    };

    this.setData = function (data) {
        for (var name in data.to) {
            self.$wrapper
                .find('span[class="recipient_' + name + '"],div[class="recipient_' + name + '"]')
                .html(data.to[name]);
        }

        for (name in data.from) {
            self.$wrapper
                .find('span[class="sender_' + name + '"],div[class="recipient_' + name + '"]')
                .html(data.from[name]);
        }
    };

    this.getData = function () {
        var data = {
            'from': {},
            'to'  : {}
        };

        self.$wrapper.find('input, input[type="hidden"], select').each(function () {
            if ('button' === $(this).attr('type')) {
                return;
            }

            var fieldName = $(this).attr('name');

            if ('sender_' === fieldName.substr(0, 7)) {
                data.from[fieldName.substr(7)] = $(this).val();

                return;
            }

            if ('recipient_' === fieldName.substr(0, 10)) {
                data.to[fieldName.substr(10)] = $(this).val();

                return;
            }

            if ('from' === fieldName || 'to' === fieldName) {
                // You shall not overwrite!
                return;
            }

            data[fieldName] = $(this).val();
        });

        return data;
    };

    this.successAction = function (data) {
        self.setData(data);
        self.toggleScreen();
    };

    this.hideSpinner = function () {
        self.$wrapper.find('.loading-overlay').hide();
    };

    this.toggleScreen = function () {
        self.hideSpinner();
        self.$wrapper.find('[role="switch"] > *').toggle();
    };

    self.$wrapper.find('button.wcsc-edit-shipment').on('click', self.editAction);
    self.$wrapper.find('button.wcsc-save-shipment').on('click', self.saveAction);

    self.$wrapper.find('.shipcloud_delete_shipment').on('click', self.deleteAction);
};

shipcloud.ShipmentController = shipcloud.LabelView;

// Extend jQuery if present just by delegating.
if (window.jQuery) {
    (function ($) {
        $.fn.shipcloudLabelView = function () {
            this.shipcloudLabelView = new shipcloud.ShipmentController(this);
            return this.shipcloudLabelView;
        };
    })(jQuery);
}
