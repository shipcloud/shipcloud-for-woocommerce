;var shipcloud = shipcloud || {};

shipcloud.MultiSelect = function (wrapperSelector) {
    var $ = jQuery;
    var self = this;

    this.$wrapper = $(wrapperSelector);

    this.main = function () {

        // Watch for changing parents to reset their nodes.
        $('select', self.$wrapper).each(function (index, object) {
            $(object).on('change', self.selectChanged);
        });

        // Trigger all nodes to show proper child nodes.
        $('select', self.$wrapper).each(function () {
            $(this).trigger('change');
        });
    };

    this.getRootNodes = function () {
        return self.$wrapper.find('select:not([data-parent])');
    };

    this.selectChanged = function () {
        $('[data-parent="#' + $(this).prop('id') + '"]').each(function () {
            self.resetChildren(this);
        });
    };

    /**
     * Reset all children.
     *
     * @param which
     */
    this.resetChildren = function (which) {
        // Assert jQuery object.
        var $which = $(which);

        if ($which.data('parent')) {
            // Disable and hide while processing child node.
            $which.prop('disabled', 'disabled');
            $which.find('[rel]').hide();
        }

        $parent = $($which.data('parent'));

        // Show only children and dangling entries.
        $parent.find('[rel]:selected').each(function (index, object) {
            $which.find('[rel="' + $(object).data('rel') + '"]').show();
        });

        // Do not keep hidden entries selected.
        $which.find('[rel]:hidden').prop('selected', false);

        // Enable again if parent has something selected.
        if ($parent.find('[rel]:selected').length > 0) {
            $which.prop('disabled', false);
        }

        // Recurse through other children.
        self.$wrapper.find('[data-parent="#' + $which.prop('id') + '"]').each(function (index, object) {
            self.resetChildren(object);
        });

        return;
    };

    self.main();
};

// Extend jQuery if present just by delegating.
if (window.jQuery) {
    (function ($) {
        $.fn.shipcloudMultiSelect = function () {
           return new shipcloud.MultiSelect(this);
        }
    })(jQuery);
}
