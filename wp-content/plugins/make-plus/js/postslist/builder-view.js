/* global jQuery, _ */
var oneApp = oneApp || {};

(function (window, $, _, oneApp) {
	'use strict';

	oneApp.views = oneApp.views || {};

	oneApp.views.postlist = oneApp.views.section.extend({
		$typeField: false,
		$fromField: false,
		$taxonomyOptions: false,

		events: function() {
			return _.extend({}, oneApp.views.section.prototype.events, {
				'change input' : 'onInputChange',
				'keyup input' : 'onInputChange',
				'change input[type=checkbox]' : 'onCheckboxChange',
				'change select': 'onInputChange',
				'change .ttfmp-posts-list-select-type': 'onTypeChange',
				'view-ready': 'onViewReady',
				'overlay-open': 'onOverlayOpen',
			});
		},

		onViewReady: function() {
			this.$typeField.trigger('change');
		},

		onTypeChange: function(e) {
			var self = this;
			var type = this.model.get('type');

			if (type == 'post') {
				this.$taxonomyOptions.show();
			} else {
				this.$taxonomyOptions.hide();
			}

			var data = {
				action: 'makeplus-postslist-filter',
				p: type,
				v: this.model.get('taxonomy')
			};

			$.post(ajaxurl, data, function(response) {
				self.$fromField.html(response);
				self.model.set('taxonomy', self.$fromField.val());
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

			this.$typeField = $('.ttfmp-posts-list-select-type', this.$el);
			this.$fromField = $('.ttfmp-posts-list-select-from', this.$el);
			this.$taxonomyOptions = $('.show-taxonomy', this.$el);

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
			$button.text('Update posts list settings');
		},
	});
})(window, jQuery, _, oneApp);
