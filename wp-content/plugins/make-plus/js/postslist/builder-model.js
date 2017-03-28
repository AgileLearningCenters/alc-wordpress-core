/* global Backbone, jQuery, _ */
var oneApp = oneApp || {};

(function (window, Backbone, $, _, oneApp) {
  'use strict';

  oneApp.models = oneApp.models || {};

  oneApp.models.postlist = Backbone.Model.extend({
    defaults: {
      'section-type': 'postlist'
    }
  });
})(window, Backbone, jQuery, _, oneApp);
