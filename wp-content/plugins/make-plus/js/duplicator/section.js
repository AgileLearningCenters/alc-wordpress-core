/* global jQuery */
var oneApp = oneApp || {};

(function($) {
	'use strict';

	function onSectionDuplicate(e) {
		e.preventDefault();

		var sectionType = this.model.get('section-type');
		var modelAttributes = this.model.toJSON();
		var id = new Date().getTime();
		modelAttributes.id = id;

		// Custom HTML id
		if (modelAttributes.hasOwnProperty('section-html-id')) {
			delete(modelAttributes['section-html-id']);
		}

		switch (sectionType) {
			case 'text':
				var columns = {1: {}, 2: {}, 3: {}, 4: {}};

				_(modelAttributes['columns']).each(function(column, index) {
					column.set('id', ++id);

					columns[index] = _.clone(column.attributes);

					var sidebarLabel = columns[index]['sidebar-label'];

					if (sidebarLabel && sidebarLabel !== '') {
						columns[index]['sidebar-label'] = sidebarLabel + ' (Copy)';
						columns[index]['widget-area-id'] = '';
						columns[index]['widgets'] = [];
					}
				});

				modelAttributes['columns'] = columns;
				break;

			case 'banner':
				modelAttributes['banner-slides'] = _(modelAttributes['banner-slides']).map(function(slide) {
					slide.id = ++id;
					return slide;
				});
				break;

			case 'gallery':
				modelAttributes['gallery-items'] = _(modelAttributes['gallery-items']).map(function(slide) {
					slide.id = ++id;
					return slide;
				});
				break;

			case 'panels':
				modelAttributes['panels-items'] = _(modelAttributes['panels-items']).map(function(item) {
					item.id = ++id;
					return item;
				});
				break;
		}

		var duplicateModel = new oneApp.models[sectionType](modelAttributes, {parse: true});
		var originalModelIndex = oneApp.builder.sections.indexOf(this.model);

		oneApp.builder.sections.add(duplicateModel, { at: originalModelIndex + 1});

		var sectionView = oneApp.builder.addSectionView(duplicateModel, this);
		oneApp.builder.scrollToSectionView(sectionView);
	}

	function mixin() {
		oneApp.views.section.prototype.events['click .ttfmp-duplicate-section'] = 'onSectionDuplicate';
		oneApp.views.section.prototype.onSectionDuplicate = onSectionDuplicate;
	}

	mixin();
})(jQuery);
