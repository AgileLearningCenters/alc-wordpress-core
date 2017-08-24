jQuery(document).ready(function ($) {

    /*
     * Changing the month on the frontend calendar.
     */
    function tbkChangeMonth(calendar, $calendar, $clicked) {
        if (calendar.hasClass('loading')) return false;
        // We're put the calendar area in a loading state
        calendar.toggleClass('tbk-loading');
        // Let's grab the frontend calendar parameters
        var params = calendar.attr('data-params');
        // We need also the explicit instance value
        var instance = calendar.attr('data-instance');
        // Let's post the request for the new month via Ajax
        var start = new Date().getTime();
        $.post(
            TB_Ajax.ajax_url,
            {
                action: 'tbajax_action_change_month',
                month : $clicked.attr('data-month'),
                year  : $clicked.attr('data-year'),
                params: params
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                // Let's replace the entire calendar area with the response
                var $container = calendar.parent();
                $container.find('.tb-frontend-calendar').replaceWith(response);
                calendar = new tbkCalendar($container.find('.tb-frontend-calendar'));
                calendar.init();
                var end = new Date().getTime();
                console.log('milliseconds passed', end - start);
            }
        );
        return false;
    }

    /*
     * Open the selected day slots list
     */
    function tbkLoadSlots(calendar, $calendar, $clicked) {
        if (calendar.hasClass('loading')) return false;
        calendar.find('.tbk-calendar-month-selector.active, .tbk-calendar-year-selector.active').trigger('click');
        // Let's grab the calendar area parameters
        var params = calendar.attr('data-params');
        // We need also the explicit instance value
        var instance = calendar.attr('data-instance');
        // We're put the calendar area in a loading state
        calendar.toggleClass('tbk-loading');
        $.post(
            TB_Ajax.ajax_url,
            {
                action: 'tbajax_action_show_day_schedule',
                day   : $clicked.attr('data-day'),
                slots : $clicked.attr('data-slots'),
                params: params
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                $calendar.showSlots(response);
                calendar.toggleClass('tbk-loading');
            }
        );
        return false;
    }


    /*
     * Open the reservation form
     */
    function tbkLoadReservationForm(calendar, $calendar, $clicked) {
        var clicked = $clicked;
        if (calendar.find('.tb-book').hasClass('tbk-loading')) {
            return false;
        }
        // Put the book button in a loading status
        clicked.toggleClass('tbk-loading');
        // Let's collect useful variables
        var slot = clicked.attr('data-slot');
        var mapstyle = clicked.data('mapstyle');
        var address = clicked.data('address');

        // Let's load the content
        $.post(
            TB_Ajax.ajax_url,
            {
                action: 'tbajax_action_get_reservation_modal',
                slot  : slot
            },
            function (response) {
                var $slider = $calendar.getSlider();
                var $reservation_slider = $slider.addSlide(response);
                // If there is an address, let's load the Google Maps
                if (address.length !== 0) {
                    $reservation_slider.find(".tbk-map").show();
                    var zoom_level = $reservation_slider.find(".tbk-map-canvas").data('zoom');
                    if (typeof google !== 'undefined') {
                        var $map_container = $reservation_slider.find(".tbk-segment.tbk-map");
                        var initial_position = false;
                        $map_container.gmap3({
                            getlatlng    : {
                                address : address,
                                callback: function (results) {
                                    if (!results) {
                                        var map = new google.maps.Map($map_container[0]);
                                        var service = new google.maps.places.PlacesService(map);
                                        var request = {
                                            query: address
                                        };
                                        service.textSearch(request, function (results, status) {
                                            if (status == google.maps.places.PlacesServiceStatus.OK) {
                                                initial_position = results[0].geometry.location;
                                                address = results[0].formatted_address;
                                                $map_container.gmap3({
                                                    clear : {
                                                        id: "tbk-map-directions"
                                                    },
                                                    map   : {
                                                        options: {
                                                            zoom  : zoom_level,
                                                            center: initial_position
                                                        }
                                                    },
                                                    marker: {
                                                        address: address,
                                                        id     : 'tbk-map-init-address'
                                                    }
                                                });
                                            } else {
                                                $map_container.gmap3('destroy');
                                                $map_container.html("<div class='tbk-map-address-failed'>The address or place can't be geocoded by Google.</div>");
                                            }
                                        });
                                    } else {
                                        initial_position = results[0].geometry.location;
                                    }
                                }
                            },
                            marker       : {
                                address: address,
                                id     : 'tbk-map-init-address'
                            },
                            map          : {
                                options: {
                                    zoom             : zoom_level,
                                    scrollwheel      : false,
                                    mapTypeId        : 'style',
                                    mapTypeControl   : false,
                                    navigationControl: true
                                }
                            },
                            styledmaptype: {
                                id     : "style",
                                options: {
                                    name: "Map"
                                },
                                styles : mapstyle
                            }
                        });
                        // Intercept the eventual customer address geocoding
                        $reservation_slider.find('input[id^="tbk-address"]')
                            .on('input', function () {
                                $map_container.gmap3({
                                    clear: {
                                        id: "tbk-map-directions"
                                    },
                                    map  : {
                                        options: {
                                            zoom  : zoom_level,
                                            center: initial_position
                                        }
                                    }
                                });
                            })
                            .on('geocode:result', function (event, result) {
                                $map_container.gmap3({
                                    getroute: {
                                        options : {
                                            origin     : result.geometry.location,
                                            destination: address,
                                            travelMode : google.maps.DirectionsTravelMode.DRIVING
                                        },
                                        callback: function (results) {
                                            if (!results) return;
                                            $map_container.gmap3({
                                                clear             : {
                                                    id: "tbk-map-directions"
                                                },
                                                directionsrenderer: {
                                                    options: {
                                                        directions: results
                                                    },
                                                    id     : "tbk-map-directions"
                                                }
                                            });
                                        }
                                    }
                                });
                            });
                    } else {
                        $reservation_slider.find(".tbk-segment.tbk-map").html("<div class='tbk-map-missing-library'>Google Maps library not loaded.</div>");
                    }
                }
                $slider.goToSlide($reservation_slider.index()).adaptHeight();
                // wait for images to be loaded, if any
                $reservation_slider.find('img').on('load', function () {
                    $slider.adaptHeight();
                });
                // Remove the loading class
                clicked.toggleClass('tbk-loading');
            });
    }

    function tbkUpcomingShowMore(calendar, $calendar, $clicked) {
        if ($clicked.hasClass('tbk-loading')) {
            return false;
        }
        $clicked.toggleClass('tbk-loading');
        var params = $clicked.closest('.tb-frontend-calendar').attr('data-params');
        var increment = $clicked.attr('data-increment');
        var limit = $clicked.attr('data-limit');
        $.post(
            TB_Ajax.ajax_url,
            {
                action   : 'tbajax_action_upcoming_more',
                increment: increment,
                params   : params,
                limit    : limit
            },
            function (response) {
                console.log(response);
                response = tbUnwrapAjaxResponse(response);
                // Let's replace the entire calendar area with the response
                var $container = calendar.parent();
                $container.find('.tb-frontend-calendar').replaceWith(response);
                calendar = new tbkCalendar($container.find('.tb-frontend-calendar'));
                calendar.init();
            }
        );
    }

    /*
     * Open the register/login dimmer
     */
    function tbkOpenLoginDimmer(calendar, $calendar, $clicked) {
        // Put the book button in a loading status
        $clicked.toggleClass('tbk-loading');
        var event = $clicked.attr('data-event');
        var coworker = $clicked.attr('data-coworker');
        var service = $clicked.attr('data-service');
        var post_id = $clicked.closest('.tb-frontend-calendar').parent().attr('data-postid');
        // Let's load the modal content
        $.post(
            TB_Ajax.ajax_url,
            {
                action  : 'tbajax_action_get_register_modal',
                event   : event,
                coworker: coworker,
                service : service,
                post_id : post_id
            },
            function (response) {
                // Put the response inside the modal
                calendar.find('.tbk-dimmer').html(response).addClass('tbk-active');
                // Remove the loading class
                $clicked.toggleClass('tbk-loading');
                $.tbkSliderGet(calendar).adaptHeight();
            });
    }

    /*
     * Fast month selector button
     */
    function tbkFastMonthInit(calendar, $calendar, $clicked) {
        // Grab some variables
        var month = $clicked.attr('data-month'); // from 01 to 12
        var params = calendar.attr('data-params');
        // Let's put the calendar container in a loading state
        calendar.toggleClass('tbk-loading');
        // Let's post the slots for the filtered calendar via Ajax
        $.post(
            TB_Ajax.ajax_url,
            {
                action: 'tbajax_action_fast_month_selector',
                month : month,
                params: params
            },
            function (response) {
                // Let's replace the entire calendar area with the response
                var $container = calendar.parent();
                $container.find('.tb-frontend-calendar').replaceWith(response);
                calendar = new tbkCalendar($container.find('.tb-frontend-calendar'));
                calendar.init();
            });
    }

    /*
     * Fast year selector button
     */
    function tbkFastYearInit(calendar, $calendar, $clicked) {
        // Grab some variables
        var year = $clicked.attr('data-year');
        var params = calendar.attr('data-params');
        // Let's put the calendar container in a loading state
        calendar.toggleClass('tbk-loading');
        // Let's post the slots for the filtered calendar via Ajax
        $.post(
            TB_Ajax.ajax_url,
            {
                action: 'tbajax_action_fast_year_selector',
                year  : year,
                params: params
            },
            function (response) {
                // Let's replace the entire calendar area with the response
                var $container = calendar.parent();
                $container.find('.tb-frontend-calendar').replaceWith(response);
                calendar = new tbkCalendar($container.find('.tb-frontend-calendar'));
                calendar.init();
            });
    }

    // Validating coupon codes
    tbValidateCoupon = function ($clicked, $slider) {
        var reservation = $clicked.closest('.tbk-reservation-review-container').data('reservation');
        var total_amount = $clicked.closest('table').find('span.total-amount');
        var $input = $clicked.closest('.tbk-file-input').find('input');
        if ($clicked.hasClass('tbk-loading')) {
            return false;
        }
        var code = $input.val();
        $clicked.addClass('tbk-loading');
        $clicked.closest('.tbk-reservation-review-container').find('.tbk-wrong-coupon').hide();
        $slider.adaptHeight();
        $.post(
            TB_Ajax.ajax_url,
            {
                action     : 'tbajax_action_validate_coupon',
                code       : code,
                reservation: reservation
            },
            function (response) {
                $clicked.removeClass('tbk-loading');
                var discount = tbUnwrapAjaxResponse(response);
                discount = $.parseJSON(discount);
                if (discount.value !== 'ko') {
                    $clicked.closest('.tbk-reservation-review-container').data('reservation', discount.reservation);
                    $clicked.closest('.tbk-reservation-review-container').find('.tbk-right-coupon').show().html(code);
                    $clicked.remove();
                    $input.parent().remove();
                    total_amount.addClass('tbk-lined-through').next('span.discounted-amount').html(discount.value);
                } else {
                    $clicked.closest('.tbk-reservation-review-container').find('.tbk-wrong-coupon').show();
                }
                $slider.adaptHeight();
            }
        );
    };

    tbUpdateTotalPrice = function ($item_selected) {
        var $form = $item_selected.closest('.tbk-reservation-form-container').find('form');
        var tickets = parseInt($item_selected.val());
        if (tickets == 0) {
            tickets = 1;
        }

        var price_inc = 0;
        var currency_format = $item_selected.closest('.tbk-tickets-price-section').data('currency-format');
        var currency_symbol = $item_selected.closest('.tbk-tickets-price-section').data('currency-symbol');
        $form.find('input[type=radio]:checked').each(function () {
            var inc = parseFloat($(this).data('price-inc'));
            if (isNaN(inc)) {
                inc = 0;
            }
            price_inc = price_inc + inc;
        });
        $form.find('.tbk-dropdown').find('.tbk-item.active.selected').each(function () {
            var inc = parseFloat($(this).data('price-inc'));
            if (isNaN(inc)) {
                inc = 0;
            }
            price_inc = price_inc + inc;
        });

        var price = $item_selected.data('price-num') * tickets;
        var price_disc = $item_selected.data('price-disc-num') * tickets;
        var total = price + price_inc * tickets;
        var total_disc = price_disc + price_inc * tickets;
        var total_string;
        var unit_string;
        if (total === 0) {
            $item_selected.closest('.tbk-reservation-form-footer').find('.tbk-total-price-line, .tbk-total-price-line-price').hide();
        } else {
            $item_selected.closest('.tbk-reservation-form-footer').find('.tbk-total-price-line, .tbk-total-price-line-price').show();
        }
        var decimals = $item_selected.closest('.tbk-reservation-form-footer').find('.tbk-total-price-line-price').data('decimals');
        total = total.toFixed(decimals);
        var total_unit = (total / tickets).toFixed(decimals);
        total_disc = total_disc.toFixed(decimals);
        var total_unit_disc = (total_disc / tickets).toFixed(decimals);

        if (total === total_disc) {
            if (currency_format === 'before') {
                total_string = currency_symbol + total.toString();
                unit_string = currency_symbol + total_unit.toString();
            } else {
                total_string = total.toString() + currency_symbol;
                unit_string = total_unit.toString() + currency_symbol;
            }
        } else {
            if (currency_format === 'before') {
                total_string = currency_symbol + '<del>' + total.toString()
                    + '</del><span class="tbk-discounted-price"> ' + total_disc.toString() + '</span>';
                unit_string = currency_symbol + '<del>' + total_unit.toString()
                    + '</del><span class="tbk-discounted-price"> ' + total_unit_disc.toString() + '</span>';
            } else {
                total_string = total.toString() + currency_symbol;
                unit_string = total_unit.toString() + currency_symbol;
            }
        }
        $item_selected.closest('.tbk-reservation-form-footer').find('.tbk-total-price-line-price').html(total_string);
        $item_selected.closest('.tbk-reservation-form-footer').find('.tbk-total-price-line-price').attr('data-base', total_string);
        $item_selected.closest('tr').find('.tbk-total-price-line-price-unit').html(unit_string);
        $item_selected.closest('tr').find('.tbk-total-price-line-price-unit').attr('data-base', unit_string);
    };

    // Cancel reservation action (frontend reservation list)
    $("table.tb-reservations-list a.tb-cancel-reservation").on('click', function (e) {
        var $clicked = $(this);
        e.preventDefault();
        var reservation_id = $clicked.data('id');
        var reservation_hash = $clicked.data('hash');
        var modal_id_random = $clicked.data('modal');

        var modal = $('#tbk-modal-' + modal_id_random).remodal();
        modal.open();
        $('#tbk-modal-' + modal_id_random)
            .data('id', reservation_id)
            .data('hash', reservation_hash)
        ;
    });
    $(document).on('confirmation', '.tbk-reservation-cancel-modal', function () {
        if ($(this).hasClass('tbk-loading')) return false;
        $(this).addClass('tbk-loading');
        $.post(
            TB_Ajax.ajax_url,
            {
                // wp action
                action          : 'tbajax_action_cancel_reservation',
                reservation_id  : $(this).data('id'),
                reservation_hash: $(this).data('hash')
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                if (response == 'ok') {
                    location.reload(true);
                } else {
                    $(this).removeClass('tbk-loading');
                    console.log(response);
                }
            }
        );
    });

    function tbkCalendar(calendar) {
        var self = this;
        var $slider = null;

        this.getSlider = function () {
            return $slider;
        }

        this.init = function () {
            $slider = new tbkSlider(calendar);
            $slider.init();
            calendar
                .on('click keydown', '.tbk-calendar-month-selector', function (e) {
                    return self.makeAccessible(e, self.showMonths);
                })
                .on('click keydown', '.tbk-calendar-year-selector', function (e) {
                    return self.makeAccessible(e, self.showYears);
                })
                .on('click keydown', '.tbk-back-to', function (e) {
                    if ($(this).hasClass('tbk-back-to-calendar')) {
                        return self.makeAccessible(e, self.backToCalendar);
                    } else {
                        return self.makeAccessible(e, self.backToPrevious);
                    }
                })
                .on("click keydown", ".ui.tb-day.slots", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        tbkLoadSlots(calendar, self, $(this));
                        return false;
                    }
                })
                .on("click keydown", ".tbk-book-now-button", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        if ($(this).hasClass('tbk-loading') === false) {
                            self.bookNow($(this));
                        }
                        return false;
                    }
                })
                .on("click keydown", ".tbk-book-confirmation-button", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        if ($(this).hasClass('tbk-loading') === false) {
                            self.confirmBooking($(this));
                        }
                        return false;
                    }
                })
                .on("click keydown", ".tbk-refresh", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        $(this).addClass('tbk-loading');
                        location.reload(true);
                        return false;
                    }
                })
                .on("click keydown", ".tb-change-month", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        tbkChangeMonth(calendar, self, $(this));
                        return false;
                    }
                })
                .on("click keydown", ".tbk-schedule-slot.tb-book", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        tbkLoadReservationForm(calendar, self, $(this));
                        return false;
                    }
                })
                .on("click keydown", ".tbk-upcoming-slot.tb-book", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        tbkLoadReservationForm(calendar, self, $(this));
                        return false;
                    }
                })
                .on("click keydown", ".tbk-show-more", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        tbkUpcomingShowMore(calendar, self, $(this));
                        return false;
                    }
                })
                .on("click keydown", ".tbk-schedule-slot.tb-book-advice, .tbk-upcoming-slot.tb-book-advice", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        tbkOpenLoginDimmer(calendar, self, $(this));
                        return false;
                    }
                })
                .on("click keydown", ".tb-fast-selector-month-panel .tbk-month-selector", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        tbkFastMonthInit(calendar, self, $(this));
                        return false;
                    }
                })
                .on("click keydown", ".tb-fast-selector-year-panel .tbk-year-selector", function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        tbkFastYearInit(calendar, self, $(this));
                        return false;
                    }
                })
                .on('change', 'input.tbk-ticket-value', function () {
                    var selected_number = $(this).val();
                    tbUpdateTotalPrice($(this));
                    $(this).closest('.tbk-reservation-form-container').find('form input[name=tickets]').val(selected_number);
                })
                .on('click keydown', '.tbk-schedule-filter-icon', function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        var target = $(this).data('target');
                        $(this).toggleClass('tbk-selected');
                        calendar.find('.tbk-' + target + '-filter-panel').toggleClass('lifted');
                        $slider.adaptHeight();
                        return false;
                    }
                })
                .on('keyup', '.tbk-ticket-value', function () {
                    var x = parseInt($(this).val());
                    var max = parseInt(this.getAttribute('max'));
                    if (x < 1) {
                        $(this).val(1);
                    }
                    if (x > max) {
                        $(this).val(max);
                    }
                    $(this).trigger('change');
                })
                .on('click keydown', '.tbk-dimmer-off', function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        calendar.find('.tbk-dimmer').removeClass('tbk-active').html('');
                        $slider.adaptHeight();
                    }
                })
                .on('click keydown', '.tbk-coupon-claim', function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        tbValidateCoupon($(this), $slider);
                    }
                })
                .on('click keydown', '.tbk-pay-button', function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        self.submitPayment($(this));
                    }
                })
                .on('click mousedown', '.tbk-field textarea', function (e) {
                    calendar.css('height', 'auto');
                })
            ;
        };

        function validateForm($form) {
            $form.find("input[type!='hidden'], textarea").each(function () {
                $form.on('input', "input[type!='hidden'], textarea", function () {
                    $(this).closest('.tbk-field').removeClass('tbk-error');
                });
                $form.on('change', "input[type='file'], textarea", function () {
                    $(this).closest('.tbk-field').removeClass('tbk-error');
                });

                if ($(this).prop('required') && !$(this).val()) {
                    $(this).closest('.tbk-field').addClass('tbk-error');
                } else {
                    $(this).closest('.tbk-field').removeClass('tbk-error');
                }
                if (typeof $(this).data('validation') !== "undefined") {
                    var regex = new RegExp(tb_base64_decode($(this).data('validation')));
                    if (regex.test($(this).val()) === false) {
                        $(this).siblings('.tbk-reservation-form-pointing-error').show();
                        $(this).closest('.tbk-field').addClass('tbk-error');
                    } else {
                        $(this).siblings('.tbk-reservation-form-pointing-error').hide();
                        if ($(this).val()) {
                            $(this).closest('.tbk-field').removeClass('tbk-error');
                        }
                    }
                }
            });
            $slider.adaptHeight();
            // Select validation
            $form.find(".tbk-dropdown").each(function () {
                if ($(this).find("input").prop('required') && !$(this).find("input").val()) {
                    $(this).closest('.tbk-field').addClass('tbk-error');
                } else {
                    $(this).closest('.tbk-field').removeClass('tbk-error');
                }
            });
            // Checkboxes form validation
            $form.find("input[type='checkbox']").each(function () {
                if ($(this).prop('required') && !$(this).prop("checked")) {
                    $(this).closest('.tbk-field').addClass('tbk-error');
                    $form.on('change keyup', "input[type='checkbox']", function () {
                        $(this).closest('.tbk-field').removeClass('tbk-error');
                    });
                } else {
                    $(this).closest('.tbk-field').removeClass('tbk-error');
                }
            });
            // Check for invalid data
            var valid = true;
            $form.find("input[type!='hidden'], input[type='checkbox'], .tbk-dropdown, textarea").each(function () {
                if ($(this).closest('.tbk-field').hasClass('tbk-error')) {
                    valid = false;
                }
            });
            $form.closest('.tbk-reservation-form-container').find('.tbk-ticket-value').each(function () {
                $(this).closest('td').find('.tbk-tickets-span').removeClass('tbk-error');
                if (!$(this).val()) {
                    $(this).closest('td').find('.tbk-tickets-span').addClass('tbk-error');
                    valid = false;
                }
            });
            return valid;
        }

        this.submitPayment = function ($clicked) {
            if ($clicked.hasClass('tbk-loading')) {
                return;
            }
            $clicked.addClass('tbk-loading');
            var gateway_id = $clicked.data('gateway');
            var offsite = $clicked.data('offsite');
            var reservation_checksum = $clicked.closest('.tbk-pre-payment').data('checksum');
            var reservation_database_id = $clicked.closest('.tbk-pre-payment').data('id');
            $.post(
                TB_Ajax.ajax_url,
                {
                    action                 : 'tb_submit_payment',
                    reservation_checksum   : reservation_checksum,
                    gateway_id             : gateway_id,
                    reservation_database_id: reservation_database_id
                },
                function (response) {
                    response = tbUnwrapAjaxResponse(response);
                    if (offsite == true || response.slice(0, 4) == 'http') {
                        // redirecting
                        window.location.href = response;
                        return;
                    } else {
                        // parse the dinamically loaded scripts
                        var content = $($.parseHTML(response, document, true));
                        // Load the response box
                        $slider.goToSlide($slider.addSlide(content).index()).adaptHeight();
                        $clicked.removeClass('tbk-loading');
                    }
                }
            );
        }

        this.bookNow = function ($clicked) {
            var $container = $clicked.closest('.calendar_main_container, .calendar_widget_container');
            var withFiles = $clicked.data('files');
            // Let's get the form id
            var $form = $clicked.closest('.tbk-reservation-form-container').find('.tbk-reservation-form');
            // Let's get the post id
            var post_id = $container.attr('data-postid');
            $form.find("input[name='post_id']").val(post_id);
            // Selected timezone
            var timezone = $container.find('.tbk-timezones .tbk-menu .tbk-menu-item.tbk-selected').data('timezone') || false;

            /*
             * Submit, if validation is passed
             */
            if (validateForm($form)) {
                $clicked.addClass('tbk-loading');
                if (withFiles) {
                    // File handling is done with FormData objects
                    // It requires the jQuery.ajax method
                    var data = new FormData();
                    // Seems redundant, but we're doing serialize()
                    // so the form listener can be just one
                    data.append('data', $form.serialize());
                    data.append('timezone', timezone);
                    data.append('action', 'tbajax_action_prepare_form');
                    $form.find('input[type="file"]').each(function () {
                        var fileInputName = $(this).attr('name');
                        data.append(fileInputName, this.files[0]);
                    });
                    $.ajax({
                        url        : TB_Ajax.ajax_url,
                        type       : 'POST',
                        data       : data,
                        processData: false,
                        contentType: false,
                        success    : function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            console.log(response); // for debug
                            if (response.slice(0, 4) == 'http') {
                                // redirecting
                                window.location.href = response;
                            } else {
                                // Load the response box
                                $slider.goToSlide($slider.addSlide(response).index()).adaptHeight();
                                $clicked.removeClass('tbk-loading');
                                $('html, body').animate({
                                    scrollTop: $container.offset().top - 20
                                }, 200);
                            }
                        }
                    });
                } else {
                    $.post(
                        TB_Ajax.ajax_url,
                        {
                            action  : 'tbajax_action_prepare_form',
                            data    : $form.serialize(),
                            timezone: timezone
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response.slice(0, 4) == 'http') {
                                // redirecting
                                window.location.href = response;
                            } else {
                                // Load the response box
                                $slider.goToSlide($slider.addSlide(response).index()).adaptHeight();
                                $clicked.removeClass('tbk-loading');
                                $('html, body').animate({
                                    scrollTop: $container.offset().top - 20
                                }, 200);
                            }
                        }
                    );
                }
            }
        };

        this.confirmBooking = function ($clicked) {
            $clicked.addClass('tbk-loading');
            var $container = $clicked.closest('.calendar_main_container, .calendar_widget_container');
            var reservation = $clicked.closest('.tbk-reservation-review-container').data('reservation');
            $.post(
                TB_Ajax.ajax_url,
                {
                    action     : 'tbajax_action_submit_form',
                    reservation: reservation
                },
                function (response) {
                    response = tbUnwrapAjaxResponse(response);
                    if (response.slice(0, 4) == 'http') {
                        // redirecting
                        window.location.href = response;
                    } else {
                        // Load the response box
                        $slider.goToSlide($slider.addSlide(response).index()).adaptHeight();
                        $clicked.removeClass('tbk-loading');
                        $('html, body').animate({
                            scrollTop: $container.offset().top - 20
                        }, 200);
                    }
                }
            );
        };

        this.makeAccessible = function (event, handler, params) {
            event.stopPropagation();
            if (event.which == 13 || event.which == 32 || event.which == 1) {
                handler(params);
                return false;
            }
        };

        this.showMonths = function () {
            calendar.find('.tb-fast-selector-year-panel').addClass('lifted');
            calendar.find('.tb-fast-selector-month-panel').toggleClass('lifted');
            calendar.find('.tbk-calendar-month-selector').toggleClass('active');
            calendar.find('.tbk-calendar-year-selector').removeClass('active');
            $slider.adaptHeight();
        };

        this.showYears = function () {
            calendar.find('.tb-fast-selector-month-panel').addClass('lifted');
            calendar.find('.tb-fast-selector-year-panel').toggleClass('lifted');
            calendar.find('.tbk-calendar-year-selector').toggleClass('active');
            calendar.find('.tbk-calendar-month-selector').removeClass('active');
            $slider.adaptHeight();
        };

        this.showSlots = function (content) {
            var $slide = $slider.addSlide(content);
            $slide.find('.tbk-schedule-filters')
                .on('click keydown', '.tbk-schedule-filter-item', function (e) {
                    e.stopPropagation();
                    if (e.which == 13 || e.which == 32 || e.which == 1) {
                        $(this).parent().find('.tbk-schedule-filter-item').removeClass('tbk-selected');
                        $(this).addClass('tbk-selected');
                        var $slot_list = jQuery(this).closest('.tbk-schedule-slots');
                        var identifier = '';
                        var params = {
                            timeint : $slide.find('.tbk-schedule-time-select').find('.tbk-selected').data('value'),
                            address : $slide.find('.tbk-schedule-location-select').find('.tbk-selected').data('value'),
                            coworker: $slide.find('.tbk-schedule-coworker-select').find('.tbk-selected').data('value')
                        };
                        for (var key in params) {
                            if (typeof params[key] === "undefined") continue;
                            if (params.hasOwnProperty(key) && params[key] !== 'all' && params[key].length !== 0 && key !== 'timeint') {
                                identifier += '[data-' + key + '="' + params[key] + '"]';
                            }
                        }
                        $slot_list.find('.tbk-schedule-slot').hide();
                        if (params.timeint !== 'all') {
                            $slot_list.find('.tbk-schedule-slot' + identifier)
                                .filter(function () {
                                    return jQuery(this).attr("data-timeint") >= params.timeint;
                                })
                                .show();
                        } else {
                            $slot_list.find('.tbk-schedule-slot' + identifier).show();
                        }
                        $slider.adaptHeight();
                    }
                });
            $slider.goToSlide($slide.index());
        };

        this.backToCalendar = function () {
            $slider.cleanSlides().goToSlide(0);
        };

        this.backToPrevious = function () {
            var index = $slider.getCurrentSlide().index();
            $slider.getCurrentSlide().remove();
            $slider.goToSlide(index - 1);
        }

        this.get = function () {
            return calendar;
        }

    }

    /*
     * Calendar slider framework
     */
    function tbkSlider($container) {
        var self = this;
        var classes = {
            active  : 'tbk-active',
            inactive: 'tbk-inactive',
            canvas  : 'tbk-slide-canvas',
            slide   : 'tbk-slide'
        }
        var $canvas = $container.find('.' + classes.canvas);
        this.slides = $canvas.find('.' + classes.slide);

        this.init = function () {
            this.slides.addClass(classes.inactive);
            $(this.slides.get(0)).removeClass(classes.inactive).addClass(classes.active);
            $container.one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function (e) {
                self.adaptHeight();
            });
            $(window).resize(function () {
                self.adaptHeight();
            });
            // removing whitespaces
            $canvas.contents().each(function () {
                if (this.nodeType === 3 && !$.trim(this.nodeValue)) {
                    $(this).remove();
                }
            });
            self.adaptHeight();
        }

        this.countSlides = function () {
            this.slides = $container.find('.' + classes.canvas).find('.' + classes.slide);
            return this.slides.length;
        }

        this.adaptHeight = function () {
            if (this.slides.length === 1) {
                $container.css('height', 'auto');
            } else {
                if ($container.find('.tbk-dimmer').hasClass('tbk-active')) {
                    $container.height($container.find('.tbk-dimmer').height());
                } else {
                    $container.height($canvas.find('.' + classes.slide + '.' + classes.active).height());
                }
            }
        }

        this.goToSlide = function (index) {
            $canvas.attr('class', classes.canvas + ' ' + classes.slide + '-' + index);
            $canvas.find('.' + classes.slide).addClass(classes.inactive).removeClass(classes.active);
            $(this.slides.get(index)).removeClass(classes.inactive).addClass(classes.active);
            this.adaptHeight();
            return this;
        }

        this.addSlide = function (html) {
            var $added = $('<div>');
            $added.addClass(classes.slide + ' ' + classes.inactive).html(html).appendTo($canvas);
            this.countSlides();
            return $added;
        }

        this.addToSlide = function (html, index) {
            var $slide = $(this.slides.get(index));
            $slide.html(html);
            this.adaptHeight();
            return $slide;
        }

        this.getCurrentSlide = function () {
            return $canvas.find('.' + classes.slide + '.' + classes.active);
        }

        this.cleanSlides = function () {
            this.slides.slice(1).remove();
            this.countSlides();
            return this;
        }
    }

    $.tbkSliderGet = function ($container) {
        return new tbkSlider($container);
    };

    $(document).click(function () {
        var $menu = $('').find('.tbk-menu');
        if ($menu.parent().hasClass('tbk-setting-button')) {
            $menu.parent().removeClass('tbk-selected');
        }
        $menu.hide();
    });

    tbkLoadInstance = function ($container) {
        var calendar = new tbkCalendar($container.find('.tb-frontend-calendar'));
        calendar.init();
        calendar.get().parent().on('click keydown', '.tbk-main-calendar-settings .tbk-setting-button', function (e) {
            e.stopPropagation();
            if (e.which != 13 && e.which != 1) {
                return;
            }
            $(this).siblings().removeClass('tbk-selected').find('.tbk-menu').hide();
            $(this).toggleClass('tbk-selected').find('.tbk-menu').toggle();
        });
        calendar.get().parent().on('click', '.tbk-main-calendar-settings .tbk-setting-button .tbk-menu-item', function () {
            $(this).closest('.tbk-setting-button').find('.tbk-text').html($(this).html());
        });
        /*
         * Fast service/coworker/timezone selectors
         */
        $container.on("click keydown", ".tbk-main-calendar-settings .tbk-setting-button .tbk-menu-item", function (e) {
            if (e.which != 13 && e.which != 1) {
                return;
            }
            var upcoming = false;
            var action = 'tbajax_action_filter_calendar';
            if ($container.hasClass('tbk-upcoming')) {
                upcoming = true;
                action = 'tbajax_action_filter_upcoming';
            }
            $(this).addClass('tbk-selected').siblings().removeClass('tbk-selected');
            var calendar = $(this).closest('.calendar_main_container, .calendar_widget_container').find('.tb-frontend-calendar');
            var attr = {
                service  : false,
                services : false,
                coworker : false,
                coworkers: false,
                timezone : false,
                params   : calendar.attr('data-params')
            };
            $(this).closest('.tbk-filters').find('.tbk-menu-item.tbk-selected').each(function () {
                if ($(this).hasClass('tbk-reset-filter')) {
                    if (typeof $(this).attr('data-services') !== 'undefined') {
                        attr.services = $(this).attr('data-services');
                    }
                    if (typeof $(this).attr('data-coworkers') !== 'undefined') {
                        attr.coworkers = $(this).attr('data-coworkers');
                    }
                } else {
                    if (typeof $(this).attr('data-service') !== 'undefined') {
                        attr.service = $(this).attr('data-service');
                    }
                    if (typeof $(this).attr('data-coworker') !== 'undefined') {
                        attr.coworker = $(this).attr('data-coworker');
                    }
                }
            });
            if (typeof $(this).attr('data-timezone') !== 'undefined') {
                attr.timezone = $(this).attr('data-timezone');
            }
            // Let's put the whole calendar container in a loading state
            calendar.addClass('tbk-loading');
            // Let's post the slots for the filtered calendar via Ajax
            $.post(
                TB_Ajax.ajax_url,
                {
                    action   : action,
                    service  : attr.service,
                    services : attr.services,
                    coworker : attr.coworker,
                    coworkers: attr.coworkers,
                    timezone : attr.timezone,
                    params   : attr.params
                },
                function (response) {
                    // Let's replace all with the results (response is a JSON object)
                    var $container = calendar.parent();
                    if (upcoming) {
                        $container.find('.tb-frontend-calendar').replaceWith(response.upcoming);
                        calendar = new tbkCalendar($container.find('.tb-frontend-calendar'));
                        calendar.init();
                        return;
                    }
                    if (response.unscheduled == false) {
                        $container.find('.tbk-main-calendar-settings').find('.tbk-coworkers, .tbk-timezones').show();
                        $container.find('.tb-frontend-calendar').replaceWith(response.calendar);
                        calendar = new tbkCalendar($container.find('.tb-frontend-calendar'));
                        calendar.init();
                    } else {
                        calendar.removeClass('tbk-loading');
                        $container.find('.tbk-main-calendar-settings').find('.tbk-coworkers, .tbk-timezones').hide();
                        calendar = new tbkCalendar($container.find('.tb-frontend-calendar'));
                        calendar.init();
                        var $slider = calendar.getSlider();
                        var $slide = $slider.cleanSlides().addSlide(response.calendar);
                        $slider.goToSlide($slide.index());
                    }
                }, "json");
        });
    };

    $('.calendar_main_container, .calendar_widget_container').each(function () {
        tbkLoadInstance($(this));
    });
});

function tbUnwrapAjaxResponse(response) {
    return jQuery.trim(response.replace(/^\s*[\r\n]/gm, "").match(/!!TBK-START!!(.*[\s\S]*)!!TBK-END!!/)[1]);
}