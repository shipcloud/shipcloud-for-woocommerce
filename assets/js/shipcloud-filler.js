;var shipcloud = shipcloud || {};

shipcloud.Filler = function (sourceSelect, targetForm) {
    var $ = jQuery;
    var self = this;

    this.$source = $(sourceSelect);
    this.$target = $(targetForm);

    this.main = function () {
        self.$source.on('change', self.fillValues);
        selected_option = $(sourceSelect.selector + " option:selected");
        if ( "" != selected_option) {
          self.$source.trigger('change');
        }
    };

    this.fillValues = function () {
        var data = $(':selected', this).data();

        if (Object.keys(data).length <= 0) {
            // No data nothing to do.
            return;
        }

        for (var field in data) {
			
			if (!data.hasOwnProperty(field)) {
                continue;
            }

            $('[name="' + field + '"]', self.$target).val(data[field]).trigger('change');
        }

        // As objects have no guaranteed order this needs to be done by hand.
        $('[name="shipcloud_carrier"]', self.$target).val(data['shipcloud_carrier']).trigger('change');
        $('[name="shipcloud_carrier_service"]', self.$target).val(data['shipcloud_carrier_service']).trigger('change');
        $('[name="shipcloud_carrier_package"]', self.$target).val(data['shipcloud_carrier_package']).trigger('change');
    };

    self.main();
};

// Extend jQuery if present just by delegating.
if (window.jQuery) {
    (function ($) {
        $.fn.shipcloudFiller = function (targetForm) {
            return new shipcloud.Filler(this, targetForm);
        }
    })(jQuery);
}
