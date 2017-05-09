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
    };

    this.handleSubmit = function (e) {
        var whichBulkButtonId = $(this).attr('id');
        n = whichBulkButtonId.substr(2);

        if (self.bulkId !== $('select[name="' + n + '"]').val()) {
            return;
        }

        e.preventDefault();
        self.setBulk();
        return false;
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
                'id': $(this).val(),
                'title': '#' + $(this).val()
            };

            $('.bulk-titles', self.bulkScreen).append(template(data));
        });
    };

    this.setBulk = function () {
        self.populateTitles();

        $('td', self.bulkScreen).attr('colspan', $('th:visible, td:visible', '.widefat:first thead').length);
        // Insert the editor at the top of the table with an empty row above to maintain zebra striping.
        $('table.widefat tbody').prepend($(self.bulkScreen)).prepend('<tr class="hidden"></tr>');
        $(self.bulkScreen).show();

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
