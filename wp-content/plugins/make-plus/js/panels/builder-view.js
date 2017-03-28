/* global jQuery, _ */
var oneApp = oneApp || {};

(function (window, $, _, oneApp) {
	'use strict';

	oneApp.views = oneApp.views || {};

	// Panels section
	oneApp.views.panels = oneApp.views.section.extend({
		itemViews: [],

		events: function() {
			return _.extend({}, oneApp.views.section.prototype.events, {
				'click .ttfmp-panels-add-item' : 'onItemAdd',
				'view-ready': 'onViewReady',
				'model-item-change': 'onItemChange',
				'item-sort': 'onItemSort',
				'item-remove': 'onItemRemove',
				'overlay-open': 'onOverlayOpen',
			});
		},

		render: function () {
			oneApp.views.section.prototype.render.apply(this, arguments);

			var items = this.model.get('panels-items'),
					self = this;

			_(items).each(function (itemModel) {
				var itemView = self.addItem(itemModel);
			});

			return this;
		},

		onViewReady: function(e) {
			this.initializeSortables();

			var items = this.model.get('panels-items');
			if (items.length == 0) {
				$('.ttfmp-panels-add-item', this.$el).trigger('click', true);
			}

			_(this.itemViews).each(function(itemView) {
				itemView.$el.trigger('view-ready');
			});
		},

		addItem: function(itemModel) {
			// Build the view
			var itemView = new oneApp.views['panels-item']({
				model: itemModel
			});

			// Append view
			var html = itemView.render().el;
			$('.ttfmp-panels-stage', this.$el).append(html);

			// Store view
			this.itemViews.push(itemView);

			return itemView;
		},

		onItemAdd: function (e, pseudo) {
			e.preventDefault();

			var itemModelDefaults = ttfMakeSectionDefaults['panels-item'] || {};
			var itemModelAttributes = _(itemModelDefaults).extend({
				id: new Date().getTime().toString(),
				parentID: this.model.id
			});
			var itemModel = new oneApp.models['panels-item'](itemModelAttributes);
			var itemView = this.addItem(itemModel);
			itemView.$el.trigger('view-ready');

			var items = this.model.get('panels-items');
			items.push(itemModel);
			this.model.set('panels-items', items);
			this.model.trigger('change');

			if (!pseudo) {
				oneApp.builder.scrollToSectionView(itemView);
			}
		},

		onItemChange: function() {
			this.model.trigger('change');
		},

		onItemSort: function(e, ids) {
			e.stopPropagation();

			var items = _(this.model.get('panels-items'));
			var sortedItems = _(ids).map(function(id) {
				return items.find(function(item) {
					return item.id.toString() == id.toString()
				});
			});

			this.model.set('panels-items', sortedItems);
		},

		onItemRemove: function(e, itemView) {
			var items = this.model.get('panels-items');
			this.model.set('panels-items', _(items).without(itemView.model));
		},

		getParentID: function() {
			var idAttr = this.$el.attr('id'),
				id = idAttr.replace('ttfmake-section-', '');

			return parseInt(id, 10);
		},

		initializeSortables: function() {
			var $selector = $('.ttfmp-panels-stage', this.$el);
			var self = this;

			$selector.sortable({
				handle: '.ttfmake-sortable-handle',
				placeholder: 'sortable-placeholder',
				forcePlaceholderSizeType: true,
				distance: 2,
				tolerance: 'pointer',

				start: function (event, ui) {
					// Set the height of the placeholder to that of the sorted item
					var $item = $(ui.item.get(0)),
						$stage = $item.parents('.ttfmp-panels-stage');

					$('.sortable-placeholder', $stage).height($item.height());
				},
				stop: function (event, ui) {
					var $item = $(ui.item.get(0)),
						$section = $item.parents('.ttfmake-section'),
						$stage = $item.parents('.ttfmp-panels'),
						$orderInput = $('.ttfmp-panels-item-order', $stage),
						sectionId = $section.attr('data-id'),
						itemId = $item.attr('data-id');

					var ids = $(this).sortable('toArray', {attribute: 'data-id'});
					self.$el.trigger('item-sort', [ids]);
					oneApp.builder.initFrame(sectionId + '-' + itemId);
				}
			});
		},

		onOverlayOpen: function(e, $overlay) {
			var $button = $('.ttfmake-overlay-close-update', $overlay);
			$button.text('Update panels settings');
		},
	});
})(window, jQuery, _, oneApp);