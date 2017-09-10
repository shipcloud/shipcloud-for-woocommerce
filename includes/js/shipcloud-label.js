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

        console.log(self.getData());
        // wp.ajax.post('shipcloud_label_update', {'data': self.$wrapper.find});

        window.setTimeout(self.successAction, 1000);
    };

    this.getData = function () {
        var data = {};

        self.$wrapper.find('input,select').each(function () {
            if ('button' === $(this).attr('type')) {
                return;
            }

            data[$(this).attr('name')] = $(this).val();
        });

        return data;
    };

    this.successAction = function () {
        self.toggleScreen();
    };

    this.toggleScreen = function () {
        self.$wrapper.find('.loading-overlay').hide();
        self.$wrapper.find('[role="switch"] > *').toggle();
    };

    self.$wrapper.find('button.wcsc-edit-shipment').on('click', self.editAction);
    self.$wrapper.find('button.wcsc-save-shipment').on('click', self.saveAction);
};

// Extend jQuery if present just by delegating.
if (window.jQuery) {
    (function ($) {
        $.fn.shipcloudLabelView = function () {
            return new shipcloud.LabelView(this);
        };
    })(jQuery);
}
