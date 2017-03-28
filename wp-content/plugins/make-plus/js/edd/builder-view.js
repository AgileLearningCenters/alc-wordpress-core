/* global jQuery, _ */
var oneApp = oneApp || {};

(function (window, $, _, oneApp) {
  'use strict';

  oneApp.views = oneApp.views || {};

  // Panels section
  oneApp.views.downloads = oneApp.views.section.extend({
    events: function() {
        return _.extend({}, oneApp.views.section.prototype.events, {
            'change input' : 'onInputChange',
            'keyup input' : 'onInputChange',
            'change input[type=checkbox]' : 'onCheckboxChange',
            'change select': 'onInputChange',
            'overlay-open': 'onOverlayOpen',
        });
    },

    onInputChange: function(e) {
        e.stopPropagation();

        var $input = $(e.target);
        var modelAttrName = $input.attr('data-model-attr');
        this.model.set(modelAttrName, $input.val());
    },

    onCheckboxChange: function(e) {
        e.stopPropagation();

        var $input = $(e.target);
        var modelAttrName = $input.attr('data-model-attr');
        this.model.set(modelAttrName, $input.is(':checked') && 1 || 0);
    },

    render: function() {
        oneApp.views.section.prototype.render.apply(this, arguments);

        var model = this.model;

        $('[data-model-attr]', this.el).each(function(i, input) {
            var $input = $(input);
            var attribute = $input.data('model-attr');
            var value = model.get(attribute);

            if (!$input.is('[type=checkbox]')) {
                $input.val(value);
            } else {
                $input.attr('checked', parseInt(value) == 1);
            }
        });

        return this;
    },

    onOverlayOpen: function(e, $overlay) {
			var $button = $('.ttfmake-overlay-close-update', $overlay);
			$button.text('Update downloads settings');
		},
  });
})(window, jQuery, _, oneApp);