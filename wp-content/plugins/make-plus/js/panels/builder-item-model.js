/* global Backbone, jQuery, _ */
var oneApp = oneApp || {};

(function (window, Backbone, $, _, oneApp) {
	'use strict';

	oneApp.models = oneApp.models || {};

	oneApp.models['panels-item'] = Backbone.Model.extend({
		defaults: {
			parentID: '',
			'section-type': 'panels-item',
			state: 'open'
		}
	});
})(window, Backbone, jQuery, _, oneApp);