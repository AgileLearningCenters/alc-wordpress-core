/* global Backbone, jQuery, _ */
var oneApp = oneApp || {};

(function (window, Backbone, $, _, oneApp) {
    'use strict';

    oneApp.models = oneApp.models || {};

    oneApp.models.downloads = Backbone.Model.extend({
        defaults: {
            'section-type': 'downloads'
        }
    });
})(window, Backbone, jQuery, _, oneApp);