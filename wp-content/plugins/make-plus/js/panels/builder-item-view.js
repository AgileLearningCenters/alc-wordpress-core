/* global jQuery, _ */
var oneApp = oneApp || {};

(function (window, $, _, oneApp) {
  'use strict';

  oneApp.views = oneApp.views || {};

  oneApp.views['panels-item'] = oneApp.views.item.extend({
    events: function() {
      return _.extend({}, oneApp.views.item.prototype.events, {
        'click .ttfmp-panels-item-remove': 'onItemRemove',
        'click .ttfmp-panels-item-toggle': 'toggleSection',
        'overlay-open': 'onOverlayOpen',
        'view-ready': 'onViewReady',
      });
    },

    initialize: function (options) {
      this.template = _.template(ttfMakeSectionTemplates['panels-item'], oneApp.builder.templateSettings);
    },

    render: function () {
      var html = this.template(this.model)
      this.setElement(html);

      return this;
    },

    onViewReady: function(e) {
      e.stopPropagation();

      this.initFrame();
    },

    initFrame: function() {
      var link = oneApp.builder.getFrameHeadLinks();
      var $iframe = $('iframe', this.$el);
      var id = $iframe.attr('id').replace('ttfmake-iframe-', '');

      oneApp.builder.initFrame(id, link);
    },

    onItemRemove: function (e) {
      e.preventDefault();

      var $stage = this.$el.parents('.ttfmp-panels');

      // Fade and slide out the section, then cleanup view
      this.$el.animate({
        opacity: 'toggle',
        height: 'toggle'
      }, oneApp.builder.options.closeSpeed, function() {
        this.$el.trigger('item-remove', this);
        this.remove();
      }.bind(this));
    },

    toggleSection: function (evt) {
      evt.preventDefault();

      var $this = $(evt.target),
        $section = $this.parents('.ttfmp-panels-item'),
        $sectionBody = $('.ttfmp-panels-item-body', $section),
        $input = $('.ttfmp-panels-item-state', this.$el);

      if ($section.hasClass('ttfmp-panels-item-open')) {
        $sectionBody.slideUp(oneApp.builder.options.closeSpeed, function() {
          $section.removeClass('ttfmp-panels-item-open');
          $input.val('closed');
        });
      } else {
        $sectionBody.slideDown(oneApp.options.openSpeed, function() {
          $section.addClass('ttfmp-panels-item-open');
          $input.val('open');
        });
      }
    },

		onOverlayOpen: function (e, $overlay) {
			e.stopPropagation();

			var $button = $('.ttfmake-overlay-close-update', $overlay);
			$button.text('Update item');
		},
  });
})(window, jQuery, _, oneApp);