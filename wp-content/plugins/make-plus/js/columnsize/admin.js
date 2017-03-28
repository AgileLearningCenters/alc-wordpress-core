(function (window, $, _, oneApp) {
    'use strict';

    var TtfmpColumnsSize = function(view, evt) {
			this.cache = {
				view: view,
        model: view.model,
        evt: evt
      },

      this.selectors = {
        sortable: '> .ttfmake-text-columns-stage',
        columnSizeContainer: '.ttfmp-column-size-container'
      },

      this.columnsClasses = {
        2: {
         '0': 'one-half',
         '1': 'two-thirds',
         '2': 'three-fourths',
         '-1': 'one-third',
         '-2': 'one-fourth'
        },
		    3: {
         '0': 'one-third',
         '1': 'one-half',
         '-1': 'one-fourth'
        }
      },

			/**
			 * Initialize TtfmpColumnsSize
			 *
			 * @return object
			 */
      TtfmpColumnsSize.prototype.init = function() {
				if (typeof evt.type !== 'undefined') {
          /*
           * The class check makes sure to only trigger events from columns
           * sortable - i.e. it won't trigger any sub-sortables such as widgets
           * in widgetized columns.
           */
					if (typeof evt.target !== 'undefined' && $(evt.target).hasClass('ttfmake-text-columns-stage')) {
						// If it's an init (sortcreate), start binding events to elements
						if (evt.type === 'sortcreate') {
							this.bindEvents();
						}

						// If it's a change in number of columns, call updateOnColumnsChange
						if (evt.type === 'change') {
							this.updateOnColumnsChange();
						}
					}
				}

				return this;
			},

			/**
			 * Bind sortstart and sortstop event on columns
			 */
      TtfmpColumnsSize.prototype.bindEvents = function() {
				var self = this;
				var $sortableEl = this.cache.view.$el.find(this.selectors.sortable);

				$sortableEl.on('sortstart', function(evt, ui) {
						$(self.selectors.columnSizeContainer).hide();
				});

				$sortableEl.on('sortstop', function(evt, ui) {
						$(self.selectors.columnSizeContainer).show();
				});

				self.cache.view.$el.on('change', '[data-model-attr=columns-number]', function() {
						self.updateOnColumnsChange();
				});

				self.setupColumns();
      },

			/**
			 * Initial setup of columns, binding `on slide` event and updating model
			 */
      TtfmpColumnsSize.prototype.setupColumns = function() {
				var self = this;
				var $section = $(self.cache.evt.target).parents('.ttfmake-section');
				var columnsNum = parseInt(self.cache.model.get('columns-number'), 10);

				if (typeof columnsNum !== 'undefined') {
					var $container = $section.find('.ttfmp-column-size-container'),
						$slider,
						initialValue,
						sliderID;

          var $containerEl = $container.clone().appendTo($container.parent());
          $container.remove();

					// initialize for 2 columns
					if (columnsNum === 2) {
						initialValue = 1;

						$slider = $('<div />')
							.addClass('ttfmp-column-size-slider two-col')
							.appendTo($containerEl);

						$slider.slider({
							value: initialValue,
							min: -2,
							max: 2,
							step: 1
						});

						$slider.on('slide', function(evt, ui) {
							var classes = self.getColumnClasses(2, ui.value);
							self.removeClasses($section.find('.ttfmake-text-column'), 'ttfmake-column-width-');

							$.each(classes, function(index, size) {
								var columnModel = self.cache.model.get('columns')[index];

								$section.find('.ttfmake-text-column:eq('+index+')')
									.addClass('ttfmake-column-width-'+size);

								// update corresponding column's model with new size
								columnModel.set('size', size);
							});

							// trigger model change to perform toJSON
							self.cache.view.$el.trigger('model-item-change');
						});
					}

					// initialize for 3 columns
					if (columnsNum === 3) {
						for (sliderID = 1; sliderID <= 2; sliderID++) {
							initialValue = 1;

							$slider = $('<div />')
								.addClass('ttfmp-column-size-slider three-col-'+sliderID)
								.attr('data-id', sliderID)
								.appendTo($containerEl);

							$slider.slider({
								value: initialValue,
								min: -1,
								max: 1,
								step: 1
							});

							$slider.on('slide', function(evt, ui) {
								var id = parseInt($(this).data('id'), 10),
									classes = self.getColumnClasses(3, ui.value),
									bigger,
									$sibling = $(this).siblings('.ttfmp-column-size-slider').first();

                // Assign "bigger column" values to a respective ui.value
                var biggerArray = {
                  '1': {
                    '-1': 1,
                    '0': 0,
                    '1': 0
                  },
                  '2': {
                    '-1': 2,
                    '0': 2,
                    '1': 1
                  }
                };

                var uiVal = ui.value.toString();
                bigger = biggerArray[id][uiVal];

							  self.removeClasses($section.find('.ttfmake-text-column'), 'ttfmake-column-width-');

								$.each(classes, function(index, size) {
                  var i = index;

                  if (id === 2) {
                      i = index + 1;
                  }

                  var columnModel = self.cache.model.get('columns')[i];
                  var columns = [0, 1, 2];

								  if (i === bigger) {
                    $section.find('.ttfmake-text-column:eq('+bigger+')')
										.addClass('ttfmake-column-width-'+ size);

                    columnModel.set('size', size);
								  } else {
									  $section.find('.ttfmake-text-column:not(:eq('+bigger+'))')
										.addClass('ttfmake-column-width-'+ size);

									  _.each(columns, function(column) {
                      if (column !== bigger) {
												var model = self.cache.model.get('columns')[column];
												model.set('size', size);
											}
										});
									}
								});

								// update sibling column value
								$sibling.slider('value', self.getSliderValue($sibling.data('id'), 3, $section, self.cache.model));

								// trigger model change to perform toJSON
								self.cache.view.$el.trigger('model-item-change');
							});
  					}
					}
        }
			},

			/**
			 * Gets value from slider
			 *
			 * @param	{number} Slider ID of column
			 * @param {number} Number of columns
			 * @param {object} jQuery element, section wrapper
			 *
			 * @return {number} The slider value
			 */
			TtfmpColumnsSize.prototype.getSliderValue = function(slider, columns, $section) {
				var model = this.cache.model.get('columns')[slider];

        var range = this.columnsClasses[columns],
					$keyPos = $section.find('.ttfmake-text-column-position-'+ slider),
					size = model.get('size'),
					value = 0;

        if (size) {
					for (var key in range) {
						if (range[key] == size) {
							value = parseInt(key);
							break;
						}
           }
        }

        return value;
			},

			/**
			 * Returns class of column depending on slider value
			 *
			 * @param {number} Number of columns
			 * @param {number} Slider value
			 *
			 * @return {string} Column class
			 */
      TtfmpColumnsSize.prototype.getColumnClasses = function(columns, val) {
				var range = this.columnsClasses[columns],
					classes = [ range[parseInt(val, 10)] ];

				if (val !== 0) {
					classes[1] = range[-val];
				} else {
					classes[1] = range[val];
				}

				return classes;
      },

			/**
			 * Removes class from element by specifying stub
			 *
			 * @param {object} jQuery element to perform class removal on
			 * @param {string} Actual class stub
			 *
			 * @return {object} jQuery elements
			 */
			TtfmpColumnsSize.prototype.removeClasses = function($el, classStub) {
				return $el.removeClass(function (index, css) {
					var regex = new RegExp('(^|\\s)' + classStub + '\\S+', 'g');
					return (css.match(regex) || []).join(' ');
				});
      },

			/**
			 * Destroys slider. Then sets up a new instance and triggers sortcreate if switching
			 * to supported number of columns (2 or 3).
			 */
      TtfmpColumnsSize.prototype.updateOnColumnsChange = function() {
				var $select = $(this.cache.evt.target);
				var selectVal = parseInt($select.val(), 10);

				// destroy slider
				$('.ttfmp-column-size-slider', this.cache.view.$el).remove();

				var columns = this.cache.model.get('columns');

				_(columns).each(function(columnModel, index) {
					// reset all columns sizes
					columnModel.set('size', '');
				});

				this.cache.view.$el.trigger('model-item-change');

				this.removeClasses($('.ttfmake-text-column', this.cache.view.$el), 'ttfmake-column-width-');

				// if not unsupported number of columns, trigger sortcreate and create a new instance
				if (selectVal !== 1 && selectVal !== 4) {
					$('.ttfmake-text-columns-stage', this.cache.view.$el).trigger('sortcreate');
				}
      },

      this.init();
    };

    function handleTtfmpColumnsSizeInit(evt) {
			var columnSizeObj = new TtfmpColumnsSize(this, evt);
    }

    function initTtfmpColumnsSize() {
			var viewClass = oneApp.views.text;
			var events = viewClass.prototype.events;

			events = typeof events === "function" && events() || events;

			events = _(events).extend({
				'sortcreate .ttfmake-text-columns-stage': handleTtfmpColumnsSizeInit
			});

			oneApp.views.text = viewClass.extend({events: events});
    }

    initTtfmpColumnsSize();
})(window, jQuery, _, oneApp);
