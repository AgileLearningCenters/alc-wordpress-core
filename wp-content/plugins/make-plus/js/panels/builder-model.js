/* global Backbone, jQuery, _ */
var oneApp = oneApp || {};

(function (window, Backbone, $, _, oneApp) {
	'use strict';

	oneApp.models = oneApp.models || {};

	oneApp.models.panels = Backbone.Model.extend({
		defaults: function() {
			return {
				'section-type': 'panels',
				state: 'open',
				'panels-items': [],
			}
		},

		parse: function(data) {
			var attributes = _(data).clone();
			attributes['panels-items'] = _(attributes['panels-items'])
				.map(function(item) {
					var itemModel = new oneApp.models['panels-item'](item);
					itemModel.set('parentID', data.id);
					return itemModel;
				});

			return attributes;
		},

		toJSON: function() {
			var json = oneApp.models.section.prototype.toJSON.apply(this, arguments);
			json['panels-items'] = _(json['panels-items']).map(function(item) {
				return item.toJSON();
			});

			return json;
		}
	});
})(window, Backbone, jQuery, _, oneApp);