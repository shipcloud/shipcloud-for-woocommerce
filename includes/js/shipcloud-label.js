;shipcloud = shipcloud || {};

shipcloud.LabelView = function (wrapper) {
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

    this.successAction = function () {
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
};

// Extend jQuery if present just by delegating.
if (window.jQuery) {
    (function ($) {
        $.fn.shipcloudLabelView = function () {
            this.shipcloudLabelView = new shipcloud.LabelView(this);
            return this.shipcloudLabelView;
        };
    })(jQuery);
}
