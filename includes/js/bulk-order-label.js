;var wcsc = wcsc || {};

wcsc.OrderBulkLabels = function (submitButton) {
    // jQuery and self reference as usual.
    var $ = jQuery;
    var self = this;

    // Protected fields
    this.bulkId = 'wcsc_order_bulk_label';
    this.bulkScreen = '#wcsc-order-bulk-labels';
    this.bulkItemTemplate = 'wcsc-order-labels-bulk-items';
    this.$submitButton = $(submitButton);

    this.main = function () {
        self.$submitButton.click(self.handleSubmit);
        $('#wcsc_template').change(self.handleTemplateSwitch);
    };

    this.handleSubmit = function (e) {
        var n = $(this).attr('id').substr(2);

        if (self.bulkId !== $('select[name="' + n + '"]').val()) {
            return;
        }

        e.preventDefault();

        if (!self.hasOrdersSelected()) {
            return;
        }

        self.setBulk();
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

    this.emptyTitles = function () {
        $('.bulk-titles', self.bulkScreen).html('');
    };

    this.populateTitles = function () {
        self.emptyTitles();

        template = wp.template(self.bulkItemTemplate);

        $('tbody th.check-column input[type="checkbox"]').each(function () {
            if (!$(this).prop('checked')) {
                return;
            }

            var data = {
                'id'   : $(this).val(),
                'title': '#' + $(this).val()
            };

            $('.bulk-titles', self.bulkScreen).append(template(data));
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
        self.populateTitles();

        $('> td', self.bulkScreen).attr('colspan', $('th:visible, td:visible', '.widefat:first thead').length);
        // Insert the editor at the top of the table with an empty row above to maintain zebra striping.
        $('table.wp-list-table.widefat > tbody').prepend($(self.bulkScreen)).prepend('<tr class="hidden"></tr>');
        $(self.bulkScreen).show();
        $('.shipcloud-carrier-select', self.bulkScreen).shipcloudMultiSelect(wcsc_carrier);

        $('button.cancel', self.bulkScreen).click(self.hide);

        $('html, body').animate({scrollTop: 0}, 'fast');
    };

    this.hide = function () {
        $(self.bulkScreen).hide();
    };

    this.main();
};

jQuery(function ($) {
    wcsc.listenBulkLabels = new wcsc.OrderBulkLabels($('#doaction, #doaction2'));
});
