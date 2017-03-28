/* global jQuery */
(function(window, $, _, oneApp) {
	'use strict';

	var TtfmpWidgetAreas = function(view, evt) {
		this.cache = {
			view: view,
			model: view.model,
			evt: evt
		},

		this.elements = {
			$el: view.$el,
			$container: $(evt.target)
		},

		this.columnModelWidgetExtension = {
			'sidebar-label': '',
			'widget-area': '',
			'widgets': ''
		},

		TtfmpWidgetAreas.prototype.init = function() {
			this.cache.evt.stopPropagation();

			this.initColumns();
		},

		TtfmpWidgetAreas.prototype.initColumns = function() {
			var self = this;
			var columnViews = this.cache.view.itemViews;

			_(columnViews).each(function(view, index) {
				self.bindEvents(view);

				if (parseInt(view.model.get('widget-area'), 10) === 1) {
					var pageID = $('#post_ID').val();

					$.ajax({
						url: ajaxurl,
						method: 'POST',
						data: {
							action: 'section_get_widget_data',
							sidebar_id: view.model.get('widget-area-id')
						},
						success: function(data) {
							var data = JSON.parse(data);
							var html = data.widgets_html;

							if (html) {
								view.$el.find('p[data-msg-type="no-widgets"]').hide();
								view.$el.find('p[data-msg-type="add-more-widgets"]').show();
							}

							view.$el.find('.ttfmp-widget-list').html('');
							view.$el.find('.ttfmp-widget-list').append(html);

							view.$el.find('.ttfmake-widget-configuration-overlay').remove();
							view.$el.find('.ttfmp-widget-area-overlay').after(data.overlay_html);
							view.$el.find('a.convert-widget-area-link').click();
							view.$el.trigger('widget-added', view.$el);

							self.initSortables(view);
						}
					});
				}
			});
		},

		TtfmpWidgetAreas.prototype.initSortables = function(view) {
			view.$el.find('.ttfmp-widget-list-sortable').sortable({
				items: 'li',
				placeholder: 'sortable-placeholder',
				forcePlaholderSizeType: true,
				distance: 2,
				tolerance: 'pointer',
				create: function(evt, ui) {
					var $this = $(this);
					var order = $this.sortable('toArray', {attribute: 'data-id'});

					view.model.set('widgets', order);
					view.$el.trigger('model-item-change');
				},
				stop: function(evt, ui) {
					var $this = $(this);
					var order = $this.sortable('toArray', {attribute: 'data-id'});

					view.model.set('widgets', order);
					view.$el.trigger('model-item-change');
				}
			});
		},

		TtfmpWidgetAreas.prototype.bindEvents = function(view) {
			view.$el.find('a.ttfmp-create-widget-area').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var $overlay = view.$el.find('.ttfmp-widget-area-overlay');

				if ($overlay.css('opacity') == 0) {
					if (e.originalEvent) {
						$overlay
							.css({
								'z-index': 100
							})
							.animate({
								'opacity': 1
							}, 300);

							$overlay.find('.ttfmake-title').focus();
					} else {
						setTimeout(function() {
							$overlay
								.css({
									'z-index': 100,
									'opacity': 1
								});
						}, 300);
					}

					view.model.set('widget-area', 1);

					if (!view.model.get('widgets') || typeof view.model.get('widgets') !== 'object') {
						view.model.set('widgets', []);
					}
				} else {
					if (e.originalEvent) {
						$overlay
							.animate({
								'opacity': 0
							}, {
								duration: 300,
								complete: function() {
									$overlay.css({'z-index': -10});
								}
							});

						view.model.set('widget-area', 0);
					}
				}

				view.$el.trigger('model-item-change');
			});

			view.$el.find('.ttfmp-revert-widget-area').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var $overlay = view.$el.find('.ttfmp-widget-area-overlay');

				$overlay
					.animate({
						'opacity': 0
					}, {
						duration: 300,
						complete: function() {
							$overlay.css({'z-index': -10});
						}
					});

				view.model.set('widget-area', 0);
				view.$el.trigger('model-item-change');
			});

			view.$el.find('input[type=text]').on('keyup', function() {
				var modelAttr = $(this).attr('data-model-attr');
				view.model.set(modelAttr, $(this).val());
				view.$el.trigger('model-item-change');
			});

			view.$el.on('click', '.remove-widget-link', function(evt) {
				var $this = $(this);

				var $widgetSection = $this.parents('.ttfmp-widget-area-display'),
					$widget = $this.parents('li'),
					widgetID = $widget.attr('data-id');

				evt.preventDefault();

				$widget.animate({
					opacity: 'toggle',
					height: 'toggle'
				}, oneApp.builder.options.closeSpeed, function() {
					$widget.remove();
				});

				var modelWidgets = view.model.get('widgets');

				var modelWidgetsWithoutRemovedItem = _.reject(modelWidgets, function(item) {
					return item === widgetID;
				});

				view.model.set('widgets', modelWidgetsWithoutRemovedItem);
				view.$el.trigger('model-item-change');
			});
		},

		this.init();
	};

	function handleTtfmpWidgetAreasInit(evt) {
		evt.stopPropagation();

		var ttfMpWidgetAreas = new TtfmpWidgetAreas(this, evt);
	}

  function initTtfmpWidgetAreas() {
		var viewClass = oneApp.views.text;
		var events = viewClass.prototype.events;

		events = typeof events === "function" && events() || events;

		events = _(events).extend({
			'columns-ready': handleTtfmpWidgetAreasInit,
		});

		oneApp.views.text = viewClass.extend({events: events});
  }

  initTtfmpWidgetAreas();
})(window, jQuery, _, oneApp);
