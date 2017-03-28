/* global Backbone, jQuery, _ */
var oneApp = oneApp || {};

(function (window, Backbone, $, _, oneApp) {
  'use strict';

  oneApp.models = oneApp.models || {};

  oneApp.models.productgrid = Backbone.Model.extend({
    defaults: {
      'section-type': 'productgrid'
    }
  });
})(window, Backbone, jQuery, _, oneApp);
