jQuery(document).ready(function ($) {

    $('#team_booking_revoke_personal_token').click(function (e) {
        e.preventDefault();
        $('#tb-personal-token-revoke-modal')
            .uiModal({
                transition: 'fade up',
                onApprove : function () {
                    $.post(
                        TB_vars.post_url,
                        {
                            action  : 'tbk_revoke_personal_token',
                            _wpnonce: TB_vars.wpNonce
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            window.location.href = response;
                        }
                    );
                }
            })
            .uiModal('show')
        ;
    });
    $('.tbk-add-google-calendar').click(function (e) {
        e.preventDefault();
        $('#tbk-gcal-not-found').hide();
        $('#tb-personal-add-calendar-modal')
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    var calendar_id = $('#tb-personal-add-calendar-id').val();
                    if (!calendar_id) return false;
                    $clicked.addClass('loading');
                    $('#tbk-gcal-not-found').hide();
                    $.post(
                        TB_vars.post_url,
                        {
                            action     : 'tbk_add_gcal',
                            _wpnonce   : TB_vars.wpNonce,
                            calendar_id: calendar_id
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response === '404') {
                                $('#tbk-gcal-not-found').show();
                                $clicked.removeClass('loading');
                            } else {
                                window.location.href = response;
                            }
                        }
                    );
                    return false;
                }
            })
            .uiModal('show')
        ;
    });
    $('.tb-sync-all-calendars').click(function (e) {
        var $clicked = $(this);
        if ($clicked.hasClass('tbk-loading')) {
            return false;
        }
        e.preventDefault();
        $clicked.addClass('tbk-loading');
        var coworker = $clicked.data('coworker');
        var $progress = $('<progress>');
        var $cell = $clicked.closest('tr').find('.tbk-calendars-state').closest('td');
        $cell.find('.tbk-calendars-state').hide();
        $cell.append($progress);
        $.post(
            TB_vars.post_url,
            {
                action  : 'tbk_sync_all_calendars',
                _wpnonce: TB_vars.wpNonce,
                coworker: coworker
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                if (response.slice(0, 4) == 'http') {
                    location.reload();
                } else {
                    $clicked.removeClass('tbk-loading');
                    $cell.find('.tbk-calendars-state').replaceWith(response);
                    $progress.remove();
                    console.log('full sync complete');
                }
            }
        );
    });
    $('.tb-sync-calendar-id').click(function (e) {
        var $clicked = $(this);
        if ($clicked.hasClass('tbk-loading')) {
            return false;
        }
        e.preventDefault();
        $clicked.addClass('tbk-loading');
        var calendar_id = $clicked.data('key');
        var $progress = $('<progress>');
        var $cell = $clicked.closest('tr').find('.tbk-calendar-state-sync, .tbk-calendar-state-not-sync').closest('td');
        $cell.find('.tbk-calendar-state-sync, .tbk-calendar-state-not-sync').hide();
        $cell.append($progress);
        $.post(
            TB_vars.post_url,
            {
                action     : 'tbk_sync_gcal',
                _wpnonce   : TB_vars.wpNonce,
                calendar_id: calendar_id
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                if (response.slice(0, 4) == 'http') {
                    location.reload();
                } else {
                    $cell.find('.tbk-calendar-state-sync').show();
                    $progress.remove();
                    $clicked.removeClass('tbk-loading');
                    console.log('full sync complete');
                    console.log('response: ' + response);
                }
            }
        );
    });
    $('.tbk-service-action-email, .tbk-service-action-calendar, .tbk-service-action-form, .tbk-service-action-settings').click(function (e) {
        $('.tbk-action-button').removeClass('tbk-loading');
        $(this).addClass('tbk-loading');
    });
    $('.tb-remove-calendar-id').click(function (e) {
        e.preventDefault();
        var calendar_id_key = $(this).data('key');
        $('#tb-personal-remove-calendar-modal')
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    if ($clicked.hasClass('tbk-loading') === false) {
                        $clicked.addClass('tbk-loading');
                        $.post(
                            TB_vars.post_url,
                            {
                                action         : 'tbk_remove_gcal',
                                _wpnonce       : TB_vars.wpNonce,
                                calendar_id_key: calendar_id_key
                            },
                            function (response) {
                                response = tbUnwrapAjaxResponse(response);
                                window.location.href = response;
                            }
                        );
                    }
                    return false;
                }
            })
            .uiModal('show')
        ;
    });
    $('.tb-clean-calendar-id').click(function (e) {
        e.preventDefault();
        var calendar_id_key = $(this).data('key');
        $('#tb-personal-clean-calendar-modal')
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    if ($clicked.hasClass('tbk-loading') === false) {
                        $clicked.addClass('tbk-loading');
                        $.post(
                            TB_vars.post_url,
                            {
                                action         : 'tbk_clean_gcal',
                                _wpnonce       : TB_vars.wpNonce,
                                calendar_id_key: calendar_id_key
                            },
                            function (response) {
                                response = tbUnwrapAjaxResponse(response);
                                window.location.href = response;
                            }
                        );
                    }
                    return false;
                }
            })
            .uiModal('show')
        ;
    });
    $('.tbk-revoke-coworker-token').click(function (e) {
        e.preventDefault();
        var coworker_id = $(this).data('coworker');
        var coworker_name = $(this).data('name');
        $('#tb-coworker-token-revoke-modal')
            .uiModal({
                transition: 'fade up',
                onShow    : function () {
                    $('#revoke-coworker-name').html(coworker_name);
                },
                onApprove : function ($clicked) {
                    if ($clicked.hasClass('tbk-loading') === false) {
                        $clicked.addClass('tbk-loading');
                        $.post(
                            TB_vars.post_url,
                            {
                                action     : 'tbk_revoke_auth_token',
                                _wpnonce   : TB_vars.wpNonce,
                                coworker_id: coworker_id
                            },
                            function (response) {
                                response = tbUnwrapAjaxResponse(response);
                                window.location.href = response;
                            }
                        );
                    }
                    return false;
                }
            })
            .uiModal('show')
        ;
    });
    $('.tbk-coworkers-action-settings').on('click', function (e) {
        e.preventDefault();
        var coworker_id = $(this).data('coworker');
        var modal_id = '#tb-coworker-settings-modal-' + coworker_id;
        $(modal_id)
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    if ($clicked.hasClass('tbk-loading') === false) {
                        $clicked.addClass('tbk-loading');
                        var form_data = $('#team-booking-coworker-settings-form-' + coworker_id).serialize();
                        $.post(
                            TB_vars.post_url,
                            {
                                action     : 'tbk_save_coworker_settings',
                                _wpnonce   : TB_vars.wpNonce,
                                coworker_id: coworker_id,
                                form_data  : form_data
                            },
                            function (response) {
                                response = tbUnwrapAjaxResponse(response);
                                window.location.href = response;
                            }
                        );
                    }
                    return false;
                }
            })
            .uiModal('show')
        ;
    });
    $('.tb-remove-residual-data').click(function () {
        var coworker_id = $(this).data('coworker');
        $.post(
            TB_vars.post_url,
            {
                action     : 'tbk_remove_coworker_residual',
                _wpnonce   : TB_vars.wpNonce,
                coworker_id: coworker_id
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                window.location.href = response;
            }
        );
    });
    $('.tbk-show-pending-payments').on('click', function () {
        $(this).closest('.tbk-panel').find('table.widefat').addClass('tbk-loading');
    });
    $('.clean-error-logs').click(function (e) {
        e.preventDefault();
        $.post(
            TB_vars.post_url,
            {
                action  : 'tbk_clean_errors',
                _wpnonce: TB_vars.wpNonce
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                window.location.href = response;
            }
        );
    });

    $(document).on('click keydown', '.tbk-tab-selector', function (e) {
        e.stopPropagation();
        if (e.which == 13 || e.which == 32 || e.which == 1) {
            var $modal = $(this).closest('.tbk-tabbed-modal');
            $modal.find('.tbk-tab-selector').removeClass('tbk-active').attr('tabindex', 0);
            $(this).addClass('tbk-active').removeAttr('tabindex');
            $modal.find('.tb-data').hide();
            var to_show = jQuery(this).data('show');
            if (to_show !== 'tb-customer-details') {
                $modal.find('.tbk-edit-reservation-data').hide();
                $modal.find('.tbk-edit-reservation-data-save').hide();
            } else {
                $modal.find('.tbk-edit-reservation-data').show();
                if ($modal.find('.tbk-edit-reservation-data').hasClass('selected')) {
                    $modal.find('.tbk-edit-reservation-data-save').show();
                }
            }
            $modal.find('.' + to_show).show();
            $modal.uiModal('refresh');
        }
    });

    $(document).on('click keydown', '.tbk-reservation-details-modal .tbk-edit-reservation-data', function (e) {
        e.stopPropagation();
        if (e.which == 13 || e.which == 32 || e.which == 1) {
            var $modal = $(this).closest('.tbk-reservation-details-modal');
            $(this).toggleClass('selected');
            $modal.find('.tbk-edit-reservation-data-save').toggle();
            $modal.find('.tb-customer-details input:not(.noedit)').each(function () {
                var ro = $(this).prop('readonly');
                $(this).prop('readonly', !ro);
            });
        }
    });

    $(document).on('click keydown', '.tbk-reservation-details-modal .tbk-print-reservation-data', function (e) {
        e.stopPropagation();
        if (e.which == 13 || e.which == 32 || e.which == 1) {
            $(this).addClass('tbk-loading');
            var form = $('#tb-print-reservation-data-form');
            var reservation_id = $("<input>")
                .attr("type", "hidden")
                .attr("name", "reservation_id")
                .val($(this).data('id'));
            form.append($(reservation_id));
            form.submit();
        }
    });

    $(document).on('click', '.tbk-edit-reservation-data-save', function () {
        var button = $(this);
        button.toggleClass('loading');
        var reservation_id = $(this).data('id');
        var fields = {};
        button.closest(".ui.small.modal").find('.tb-customer-details input:not(.noedit)').each(function () {
            var key = $(this).data('key');
            fields[key] = $(this).val();
            $(this).prop('readonly', true);
        });
        $.post(
            TB_vars.post_url,
            {
                action        : 'tbk_edit_reservation_data',
                _wpnonce      : TB_vars.wpNonce,
                reservation_id: reservation_id,
                fields        : JSON.stringify(fields)
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                console.log(response);
                button.toggleClass('loading');
                button.closest(".ui.small.modal").find('.tb-customer-details input:not(.noedit)').each(function () {
                    $(this).prop('readonly', false);
                });
            }
        );
    });

    flatpickr('.tb-flatpickr', {static: true});

    $('#add-new-promotion-coupon').click(function (e) {
        e.preventDefault();
        var $modal = $('#tb-new-promotion-modal-coupon');
        $modal
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    if ($clicked.hasClass('loading')) {
                        return false;
                    }
                    $modal.find('p.error').hide();
                    // validation
                    $modal.find('input[required]').each(function () {
                        if (!$(this).val()) {
                            $(this).addClass('tbk-field-required');
                        } else {
                            $(this).removeClass('tbk-field-required');
                        }
                    });
                    if ($modal.find('input.tbk-field-required').length > 0) {
                        return false;
                    }
                    $clicked.addClass('loading');
                    $modal.find('.tbk-modal-error-message').hide();
                    var coupon_name = $modal.find('input[name="promotion_name"]').val();
                    var promotion_limit = $modal.find('input[name="promotion_limit"]').val();
                    var coupon_discount = $modal.find('input[name="discount_value"]').val();
                    var coupon_discount_type = $modal.find('input[name="coupon-discount_type"]:checked').val();
                    var coupon_start = $modal.find('input[name="start_date"]').val();
                    var coupon_end = $modal.find('input[name="end_date"]').val();
                    var coupon_mode = $modal.find('input[name="coupon-coupon_mode"]:checked').val();
                    var bound_start_active = $modal.find('input[name="bound_start_date_active"]')[0].checked;
                    var bound_end_active = $modal.find('input[name="bound_end_date_active"]')[0].checked;
                    var coupon_services = $modal.find('.tbk-promotion-services').find('input[type="checkbox"]:checked').map(function () {
                        return this.value;
                    }).get();
                    if (coupon_services.length === 0) {
                        $clicked.removeClass('loading');
                        $modal.find('.tbk-modal-error-message').css('display', 'inline-block');
                        return false;
                    }
                    var coupon_list;
                    if (coupon_mode === 'fixed') {
                        coupon_list = '';
                    } else {
                        coupon_list = $modal.find('textarea[name="coupon-coupon_list_values"]').val();
                    }
                    $.post(
                        TB_vars.post_url,
                        {
                            action                 : 'tbk_add_coupon',
                            _wpnonce               : TB_vars.wpNonce,
                            coupon_name            : coupon_name,
                            coupon_discount        : coupon_discount,
                            coupon_discount_type   : coupon_discount_type,
                            coupon_start           : coupon_start,
                            coupon_end             : coupon_end,
                            coupon_services        : coupon_services,
                            coupon_list            : coupon_list,
                            promotion_limit        : promotion_limit,
                            bound_start_date_active: bound_start_active,
                            bound_end_date_active  : bound_end_active
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response === 'ok') {
                                location.reload();
                            } else {
                                console.log(response);
                                if (response.toLowerCase().indexOf("name_fail") >= 0) {
                                    $modal.find('p.error.name').show();
                                }
                                if (response.toLowerCase().indexOf("start_date_fail") >= 0) {
                                    $modal.find('p.error.start-date').show();
                                }
                                if (response.toLowerCase().indexOf("end_date_fail") >= 0) {
                                    $modal.find('p.error.end-date').show();
                                }
                                $clicked.removeClass('loading');
                            }
                        }
                    );
                    return false;
                }
            })
            .uiModal('show')
    });

    $('#add-new-promotion-campaign').click(function (e) {
        e.preventDefault();
        var $modal = $('#tb-new-promotion-modal-campaign');
        $modal
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    if ($clicked.hasClass('loading')) {
                        return false;
                    }
                    $modal.find('p.error').hide();
                    // validation
                    $modal.find('input[required]').each(function () {
                        if (!$(this).val()) {
                            $(this).addClass('tbk-field-required');
                        } else {
                            $(this).removeClass('tbk-field-required');
                        }
                    });
                    if ($modal.find('input.tbk-field-required').length > 0) {
                        return false;
                    }
                    $clicked.addClass('loading');
                    $modal.find('.tbk-modal-error-message').hide();
                    var campaign_name = $modal.find('input[name="promotion_name"]').val();
                    var promotion_limit = $modal.find('input[name="promotion_limit"]').val();
                    var campaign_discount = $modal.find('input[name="discount_value"]').val();
                    var campaign_discount_type = $modal.find('input[name="campaign-discount_type"]:checked').val();
                    var campaign_start = $modal.find('input[name="start_date"]').val();
                    var campaign_end = $modal.find('input[name="end_date"]').val();
                    var bound_start_active = $modal.find('input[name="bound_start_date_active"]')[0].checked;
                    var bound_end_active = $modal.find('input[name="bound_end_date_active"]')[0].checked;
                    var campaign_services = $modal.find('.tbk-promotion-services').find('input[type="checkbox"]:checked').map(function () {
                        return this.value;
                    }).get();
                    if (campaign_services.length === 0) {
                        $clicked.removeClass('loading');
                        $modal.find('.tbk-modal-error-message').css('display', 'inline-block');
                        return false;
                    }
                    $.post(
                        TB_vars.post_url,
                        {
                            action                 : 'tbk_add_campaign',
                            _wpnonce               : TB_vars.wpNonce,
                            campaign_name          : campaign_name,
                            campaign_discount      : campaign_discount,
                            campaign_discount_type : campaign_discount_type,
                            campaign_start         : campaign_start,
                            campaign_end           : campaign_end,
                            campaign_services      : campaign_services,
                            promotion_limit        : promotion_limit,
                            bound_start_date_active: bound_start_active,
                            bound_end_date_active  : bound_end_active
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response === 'ok') {
                                location.reload();
                            } else {
                                console.log(response);
                                if (response.toLowerCase().indexOf("name_fail") >= 0) {
                                    $modal.find('p.error.name').show();
                                }
                                if (response.toLowerCase().indexOf("start_date_fail") >= 0) {
                                    $modal.find('p.error.start-date').show();
                                }
                                if (response.toLowerCase().indexOf("end_date_fail") >= 0) {
                                    $modal.find('p.error.end-date').show();
                                }
                                $clicked.removeClass('loading');
                            }
                        }
                    );
                    return false;
                }
            })
            .uiModal('show')
    });

    $('.tbk-promotion-action-edit').click(function (e) {
        e.preventDefault();
        var promotion_db_id = $(this).data('id');
        var promotion = $(this).data('promotion');
        var decimals = $(this).data('decimals');
        var $modal = $('#' + promotion + '-edit-modal');
        $modal
            .find('span[id$="-discounted"]')
            .each(function () {
                $(this).autoNumeric('init', {mDec: decimals});
                $(this).autoNumeric('update');
            });
        $modal
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    if ($clicked.hasClass('loading')) {
                        return false;
                    }
                    $modal.find('p.error').hide();
                    // validation
                    $modal.find('input[required]').each(function () {
                        if (!$(this).val()) {
                            $(this).addClass('tbk-field-required');
                        } else {
                            $(this).removeClass('tbk-field-required');
                        }
                    });
                    if ($modal.find('input.tbk-field-required').length > 0) {
                        return false;
                    }
                    $clicked.addClass('loading');
                    $modal.find('.tbk-modal-error-message').hide();
                    var promotion_name = $modal.find('input[name="promotion_name"]').val();
                    var promotion_limit = $modal.find('input[name="promotion_limit"]').val();
                    var promotion_discount = $modal.find('input[name="discount_value"]').val();
                    var promotion_discount_type = $modal.find('input[name="' + promotion_name + '-discount_type"]:checked').val();
                    var promotion_start = $modal.find('input[name="start_date"]').val();
                    var promotion_end = $modal.find('input[name="end_date"]').val();
                    var promotion_bound_start = $modal.find('input[name="bound_start_date"]').val();
                    var promotion_bound_end = $modal.find('input[name="bound_end_date"]').val();
                    var promotion_services = $modal.find('.tbk-promotion-services').find('input[type="checkbox"]:checked').map(function () {
                        return this.value;
                    }).get();
                    if (promotion_services.length === 0) {
                        $clicked.removeClass('loading');
                        $modal.find('.tbk-modal-error-message').css('display', 'inline-block');
                        return false;
                    }
                    var coupon_list = '';
                    if ($modal.find('input[name="' + promotion_name + '-coupon_mode"]').length) {
                        if ($modal.find('input[name="' + promotion_name + '-coupon_mode"]:checked').val() !== 'fixed') {
                            coupon_list = $modal.find('textarea[name="' + promotion_name + '-coupon_list_values"]').val();
                        }
                    }
                    var bound_start_active = $modal.find('input[name="bound_start_date_active"]')[0].checked;
                    var bound_end_active = $modal.find('input[name="bound_end_date_active"]')[0].checked;
                    $.post(
                        TB_vars.post_url,
                        {
                            action                 : 'tbk_edit_promotion',
                            _wpnonce               : TB_vars.wpNonce,
                            pricing_name           : promotion_name,
                            pricing_discount       : promotion_discount,
                            pricing_discount_type  : promotion_discount_type,
                            pricing_start          : promotion_start,
                            pricing_end            : promotion_end,
                            pricing_services       : promotion_services,
                            pricing_db_id          : promotion_db_id,
                            promotion_limit        : promotion_limit,
                            coupon_list            : coupon_list,
                            promotion_bound_start  : promotion_bound_start,
                            promotion_bound_end    : promotion_bound_end,
                            bound_start_date_active: bound_start_active,
                            bound_end_date_active  : bound_end_active
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response === 'ok') {
                                location.reload();
                            } else {
                                console.log(response);
                                if (response.toLowerCase().indexOf("name_fail") >= 0) {
                                    $modal.find('p.error.name').show();
                                }
                                if (response.toLowerCase().indexOf("start_date_fail") >= 0) {
                                    $modal.find('p.error.start-date').show();
                                }
                                if (response.toLowerCase().indexOf("end_date_fail") >= 0) {
                                    $modal.find('p.error.end-date').show();
                                }
                                $clicked.removeClass('loading');
                            }
                        }
                    );
                    return false;
                }
            })
            .uiModal('show')
    });
    $('.tbk-promotion-action-pause, .tbk-promotion-action-run').click(function (e) {
        e.preventDefault();
        var $clicked = $(this);
        if ($clicked.hasClass('tbk-loading')) return false;
        $clicked.addClass('tbk-loading');
        var promotion_id = $(this).data('id');
        var status = 'run';
        if ($(this).hasClass('tbk-promotion-action-pause')) {
            status = 'pause';
        }
        $.post(
            TB_vars.post_url,
            {
                action    : 'tbk_toggle_promotion',
                _wpnonce  : TB_vars.wpNonce,
                status    : status,
                pricing_id: promotion_id
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                if (response === 'ok') {
                    location.reload();
                } else {
                    console.log(response);
                    $clicked.addClass('tbk-loading');
                }
            }
        );
    });
    $('.tbk-promotion-action-delete').click(function (e) {
        e.preventDefault();
        var promotion_id = $(this).data('id');
        var promotion_name = $(this).data('name');
        $('#tbk-promotion-delete-modal').find('.promotion-name').html(promotion_name);
        $('#tbk-promotion-delete-modal')
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    if ($clicked.hasClass('loading')) {
                        return false;
                    }
                    $clicked.addClass('loading');
                    $.post(
                        TB_vars.post_url,
                        {
                            action    : 'tbk_delete_promotion',
                            _wpnonce  : TB_vars.wpNonce,
                            pricing_id: promotion_id
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response === 'ok') {
                                location.reload();
                            } else {
                                console.log(response)
                            }
                        }
                    );
                    return false;
                }
            })
            .uiModal('show')
    });

    $('.team-booking-new-service').click(function (e) {
        e.preventDefault();
        $('#tb-booking-new-service-modal')
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    if ($clicked.hasClass('loading')) {
                        return false;
                    }
                    $('#tbk-new-id-already-existant').hide();
                    $('#tbk-new-name-already-existant').hide();
                    $clicked.addClass('loading');
                    var new_service_id = $('#tb-booking-new-service-id').val();
                    var new_service_name = $('#tb-booking-new-service-name').val();
                    var new_service_class = $('input[name=class]:checked', '#tb-booking-new-service-modal').val();
                    $.post(
                        TB_vars.post_url,
                        {
                            action  : 'tbk_add_service',
                            _wpnonce: TB_vars.wpNonce,
                            id      : new_service_id,
                            name    : new_service_name,
                            class   : new_service_class
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response === 'ok') {
                                location.reload();
                            } else {
                                if (response === 'id_fail') {
                                    $('#tbk-new-id-already-existant').show();
                                }
                                if (response === 'name_fail') {
                                    $('#tbk-new-name-already-existant').show();
                                }
                                if (response === 'id_fail name_fail') {
                                    $('#tbk-new-id-already-existant').show();
                                    $('#tbk-new-name-already-existant').show();
                                }
                                $clicked.removeClass('loading');
                            }

                        }
                    );
                    return false;
                }
            })
            .uiModal('show')
        ;
    });
    $('.team-booking-clone-service').click(function (e) {
        e.preventDefault();
        var service_id = $(this).data('serviceid');
        $('#tb-booking-clone-service-modal')
            .uiModal({
                transition: 'fade up',
                onShow    : function () {
                    $('#tbk-clone-id-already-existant').hide();
                    $.post(
                        TB_vars.post_url,
                        {
                            action    : 'tbk_clone_service_hint',
                            _wpnonce  : TB_vars.wpNonce,
                            service_id: service_id
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            $('#tb-booking-clone-service-new-id').val(response);
                        }
                    );
                },
                onApprove : function ($clicked) {
                    $('#tbk-clone-id-already-existant').hide();
                    $clicked.addClass('loading');
                    var new_service_id = $('#tb-booking-clone-service-new-id').val();
                    $.post(
                        TB_vars.post_url,
                        {
                            action        : 'tbk_clone_service',
                            _wpnonce      : TB_vars.wpNonce,
                            service_id    : service_id,
                            new_service_id: new_service_id
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response === 'id already used') {
                                $('#tbk-clone-id-already-existant').show();
                                $clicked.removeClass('loading');
                            } else {
                                location.reload();
                            }
                        }
                    );
                    return false;
                }
            })
            .uiModal('show')
        ;
    });
    $('.team-booking-delete-service').click(function (e) {
        e.preventDefault();
        var service = $(this).data('servicename');
        var service_id = $(this).data('serviceid');
        $('#tb-booking-delete-modal')
            .uiModal({
                transition: 'fade up',
                onShow    : function () {
                    $(this).find('.service-name').text(service)
                },
                onApprove : function () {
                    $.post(
                        TB_vars.post_url,
                        {
                            action    : 'tbk_delete_service',
                            _wpnonce  : TB_vars.wpNonce,
                            booking_id: service_id
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            window.location.href = response;
                        }
                    );
                }
            })
            .uiModal('show')
        ;
    });
    $('.team-booking-approve-reservation').click(function (e) {
        e.preventDefault();
        var reservation_id = $(this).data('reservationid');
        var modal_id = '#tb-reservation-approve-modal-' + reservation_id;
        $(modal_id + ' p.error').hide();
        $(modal_id)
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    $clicked.addClass('loading');
                    $.post(
                        TB_vars.post_url,
                        {
                            action        : 'tbk_approve_reservation',
                            _wpnonce      : TB_vars.wpNonce,
                            reservation_id: reservation_id
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response.substr(0, 5) === 'ERROR') {
                                $clicked.removeClass('loading');
                                $(modal_id + ' p.error').show();
                                $(modal_id + ' p.error > span').html(response);
                            } else {
                                window.location.href = response;
                            }
                        }
                    );
                    return false;
                }
            })
            .uiModal('show')
        ;
    });
    $('.team-booking-confirm-pending-reservation').click(function (e) {
        e.preventDefault();
        var reservation_id = $(this).data('reservationid');
        var modal_id = '#tb-reservation-confirm-pending-modal-' + reservation_id;
        $(modal_id + ' p.error').hide();
        $(modal_id)
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    $clicked.addClass('loading');
                    $.post(
                        TB_vars.post_url,
                        {
                            action        : 'tbk_confirm_pending_reservation',
                            _wpnonce      : TB_vars.wpNonce,
                            set_as_paid   : $clicked.hasClass('tbk-secondary'),
                            reservation_id: reservation_id
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response.substr(0, 5) === 'ERROR') {
                                $clicked.removeClass('loading');
                                $(modal_id + ' p.error').show();
                                $(modal_id + ' p.error > span').html(response);
                            } else {
                                window.location.href = response;
                            }
                        }
                    );
                    return false;
                }
            })
            .uiModal('show')
        ;
    });
    $('.team-booking-delete-reservation, .team-booking-cancel-reservation').click(function (e) {
        e.preventDefault();
        var reservation_id = $(this).data('reservationid');
        var is_previously_confirmed = $(this).data('previouslyconfirmed');
        var reason;
        if ($(this).hasClass('team-booking-delete-reservation')) {
            $('#tb-reservation-delete-modal')
                .uiModal({
                    transition: 'fade up',
                    onApprove : function ($clicked) {
                        $clicked.addClass('tbk-loading');
                        $.post(
                            TB_vars.post_url,
                            {
                                action        : 'tbk_delete_reservation',
                                _wpnonce      : TB_vars.wpNonce,
                                reason        : reason,
                                reservation_id: reservation_id
                            },
                            function (response) {
                                response = tbUnwrapAjaxResponse(response);
                                window.location.href = response;
                            }
                        );
                        return false;
                    }
                })
                .uiModal('show')
            ;
        } else {
            $('#tb-reservation-cancel-modal .previously-confirmed').show();
            if (is_previously_confirmed === 'no') {
                $('#tb-reservation-cancel-modal .previously-confirmed').hide();
            }
            $('#tb-reservation-cancel-modal')
                .uiModal({
                    transition: 'fade up',
                    onApprove : function ($clicked) {
                        reason = $(this).find('textarea#reason').val();
                        $clicked.addClass('loading');
                        $.post(
                            TB_vars.post_url,
                            {
                                action        : 'tbk_cancel_reservation',
                                _wpnonce      : TB_vars.wpNonce,
                                reason        : reason,
                                reservation_id: reservation_id
                            },
                            function (response) {
                                response = tbUnwrapAjaxResponse(response);
                                window.location.href = response;
                            }
                        );
                        return false;
                    }
                })
                .uiModal('show')
            ;
        }
    });
    $('#tb-delete-all-reservations-modal')
        .uiModal({
            transition: 'fade up',
            onApprove : function () {
                $.post(
                    TB_vars.post_url,
                    {
                        action  : 'tbk_delete_all_reservations',
                        _wpnonce: TB_vars.wpNonce
                    },
                    function (response) {
                        response = tbUnwrapAjaxResponse(response);
                        window.location.href = response;
                    }
                );
            }
        })
        .uiModal('attach events', '.delete_all_tb_reservations', 'show')
    ;
    $('.delete_all_tb_reservations').click(function (e) {
        e.preventDefault();
    });
    // Get the CSV log file
    $('.tb-get-csv').click(function (e) {
        e.preventDefault();
        $('#tb-get-csv-form').submit();
    });
    // Get the CSV customers file
    $('.tb-get-customers-csv').click(function (e) {
        e.preventDefault();
        $('#tb-get-customers-csv-form').submit();
    });
    // Get the CSV slot file
    $('.tbk-get-slot-csv').click(function (e) {
        e.preventDefault();
        $('#tb-get-slot-csv-form').find('input[name="reservations"]').val($(this).data('reservations'));
        $('#tb-get-slot-csv-form').find('input[name="filename"]').val($(this).data('filename'));
        $('#tb-get-slot-csv-form').submit();
    });
    // Get the XLSX log file
    $('.tb-get-xlsx').click(function (e) {
        e.preventDefault();
        $('#tb-get-xlsx-form').submit();
    });
    // Get the XLSX customers file
    $('.tb-get-customers-xlsx').click(function (e) {
        e.preventDefault();
        $('#tb-get-customers-xlsx-form').submit();
    });
    // Get the XLSX slot file
    $('.tbk-get-slot-xlsx').click(function (e) {
        e.preventDefault();
        $('#tb-get-slot-xlsx-form').find('input[name="reservations"]').val($(this).data('reservations'));
        $('#tb-get-slot-xlsx-form').find('input[name="filename"]').val($(this).data('filename'));
        $('#tb-get-slot-xlsx-form').submit();
    });
    // Add a custom field
    $('.tb-add-custom-field').click(function (e) {
        e.preventDefault();
        var field = $(this).data('field');
        var serviceid = $(this).data('serviceid');
        var $field_area = $(this).closest('.tbk-wrapper').find('.tbk-form-fields-list');
        $field_area.addClass('tbk-loading');
        $.post(
            TB_vars.post_url,
            {
                action    : 'tbk_add_custom_field',
                _wpnonce  : TB_vars.wpNonce,
                field     : field,
                service_id: serviceid
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                if ($('.tb-no-custom-fields').length) {
                    $('.tb-no-custom-fields').remove();
                }
                $field_area.append(response);
                $('body, html').animate({scrollTop: $field_area.find("li:last").offset().top}, 1000);
                $field_area.removeClass('tbk-loading');
            }
        );
    });
    // Remove a custom field
    $('.tbk-form-fields-list').on("click", ".tb-remove-custom-field", function (e) {
        e.preventDefault();
        var hook = $(this).data('hook');
        var serviceid = $(this).data('serviceid');
        var $clicked = $(this).closest('.tbk-form-field-draggable');
        $clicked.addClass('tbk-loading');
        $(this).parents().eq(5).addClass('toberemoved');
        $.post(
            TB_vars.post_url,
            {
                action    : 'tbk_remove_custom_field',
                _wpnonce  : TB_vars.wpNonce,
                hook      : hook,
                service_id: serviceid
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                $('.toberemoved').next('br').remove();
                $('.toberemoved').remove();
                $clicked.removeClass('tbk-loading');
            }
        );
    });
    // Save a form field
    $('.tbk-form-fields-list').on("click", ".tb-save-custom-field, .tb-save-builtin-field", function (e) {
        e.preventDefault();
        if ($(this).hasClass('tobesaved')) {
            return;
        }
        var hook = $(this).data('hook');
        var serviceid = $(this).data('serviceid');
        var custom_field = $(this).parents().eq(5);
        var inputs = custom_field.find('input, select, textarea').serialize();
        var $clicked = $(this).closest('.tbk-form-field-draggable');
        $clicked.addClass('tbk-loading');
        $(this).addClass('tobesaved').attr("disabled", true);
        var action = 'tbk_save_custom_field';
        if ($(this).hasClass('tb-save-builtin-field')) {
            action = 'tbk_save_builtin_field';
        }
        $.post(
            TB_vars.post_url,
            {
                action    : action,
                _wpnonce  : TB_vars.wpNonce,
                hook      : hook,
                service_id: serviceid,
                inputs    : inputs
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                if (response === 'duplicate_hook') {
                    $(".tobesaved").attr("disabled", false);
                    alert('Hook already existant! Please choose another one.'); //TODO: better handling
                } else {
                    if (response !== 'ok') {
                        console.log(response);
                    }
                    $(".tobesaved").attr("disabled", false).removeClass('tobesaved');
                }
                $clicked.removeClass('tbk-loading');
            }
        );
    });
    // Expand custom field
    $('.tbk-form-fields-list').on("click", ".tb-expand-handle", function (e) {
        e.preventDefault();
        $(this).closest('li').find('.tbk-form-field-draggable').addClass('expanded');
        $(this).closest('li').find('.tb-hide').show();
        $(this).removeClass('tb-expand-handle').addClass('tb-collapse-handle');
        $(this).find('span').addClass('dashicons-arrow-up').removeClass('dashicons-admin-settings');
        $(this).closest('li').find('.tbk-field-label').css('display', 'inline-block');
        $(this).closest('li').find('.tbk-field-preview').hide();
    });
    // Collapse custom field
    $('.tbk-form-fields-list').on("click", ".tb-collapse-handle", function (e) {
        e.preventDefault();
        $(this).closest('li').find('.tbk-form-field-draggable').removeClass('expanded');
        $(this).closest('li').find('.tb-hide').hide();
        $(this).removeClass('tb-collapse-handle').addClass('tb-expand-handle');
        $(this).find('span').addClass('dashicons-admin-settings').removeClass('dashicons-arrow-up');
        $(this).closest('li').find('.tbk-field-label').hide();
        $(this).closest('li').find('.tbk-field-preview').show();
        // Select update
        var $object = $(this).closest('li').find('.tbk-field-preview').find('select');
        $object.empty();
        $(this).closest('li').find('.tbk-select-option-edit').each(function (index) {
            $object.append('<option>' + $(this).val() + '</option>');
        });
        // Radio update
        $object = $(this).closest('li').find('.tbk-field-preview').has('input:radio');
        $object.find('fieldset').empty();
        $(this).closest('li').find('.tbk-radio-option-edit').each(function (index) {
            $object.find('fieldset').append('<input type="radio">' + $(this).val() + '</input><br>');
        });
        // Textfield/Textarea update
        $object = $(this).closest('li').find('.tbk-field-preview').find('input:text, textarea');
        $object.val($(this).closest('li').find('.tbk-text-value-edit').val());
        // Checkbox update
        $object = $(this).closest('li').find('.tbk-field-preview').find('input:checkbox');
        $object.prop('checked', $(this).closest('li').find('.tbk-checkbox-default-state').prop('checked'));
    });
    // Update field content
    $('.tbk-field-label').find('input').on('input', function () {
        $(this).closest('li').find('.tbk-field-label-rendered').html($(this).val());
    });
    $('.tbk-description-edit').on('input', function () {
        $(this).closest('li').find('.tbk-field-description-rendered').html($(this).val());
    });
    // Add custom field option
    $('.tbk-form-fields-list').on("click", ".tb-add-option-handle", function (e) {
        var $sibling = $(this).closest('tr').prev('tr');
        var trs = $(this).closest('table').find('tr')[0];
        var tds = $(trs).find('td');
        $(tds[0]).attr('rowspan', parseInt($(tds[0]).attr('rowspan')) + 1);
        $(tds[1]).attr('rowspan', parseInt($(tds[1]).attr('rowspan')) + 1);
        if ($sibling.find('input.single-option').length === 1) {
            var $cloned = $sibling.clone();
            var $inputs = $cloned.find('input');
            $inputs.each(function () {
                var nth = 0;
                var name = $(this).attr('name');
                $(this).attr('name', name.replace(/\[.+?\]/g, function (match, pos, original) {
                    nth++;
                    return (nth == 4) ? '[' + $sibling.closest('table').find('.single-option').length + ']' : match;
                }));
                if ($(this).hasClass('all-options')) {
                    $(this).attr('value', '');
                } else {
                    $(this).attr('value', '0.00');
                }
            });
            $sibling.after($cloned);
        } else {
            var $cloned = $sibling.clone();
            $cloned.find('td').prepend('<span class="dashicons dashicons-dismiss tb-delete-option-handle"></span>');
            var nth = 0;
            var name_label = $cloned.find('input.all-options').attr('name').slice(0, -15) + 'options][0][label]';
            var name_price_increment = $cloned.find('input.all-options').attr('name').slice(0, -15) + 'options][0][price_increment]';
            $cloned.find('input.all-options').attr('name', name_label).attr('value', '').addClass('single-option');
            $cloned.find('input[type=hidden]').attr('name', name_price_increment).attr('value', '0.00');
            $sibling.after($cloned);
        }
    });
    // Remove custom field option
    $('.tbk-form-fields-list').on("click", ".tb-delete-option-handle", function (e) {
        var trs = $(this).closest('table').find('tr')[0];
        var tds = $(trs).find('td');
        $(tds[0]).attr('rowspan', parseInt($(tds[0]).attr('rowspan')) - 1);
        $(tds[1]).attr('rowspan', parseInt($(tds[1]).attr('rowspan')) - 1);
        $(this).closest('tr').remove();
        var counter = 0;
        $(trs).closest('table').find('.single-option').each(function () {
            var nth = 0;
            var name = $(this).attr('name');
            $(this)
                .attr('name', name.replace(/\[.+?\]/g, function (match, pos, original) {
                    nth++;
                    return (nth == 4) ? '[' + counter + ']' : match;
                }))
            ;
            var nth = 0;
            var name = $(this).next('input').attr('name');
            $(this).next('input')
                .attr('name', name.replace(/\[.+?\]/g, function (match, pos, original) {
                    nth++;
                    return (nth == 4) ? '[' + counter + ']' : match;
                }))
            ;
            counter++;
        });
    });
    // Open custom field advanced option settings
    $('.tbk-form-fields-list').on("click", ".tb-advanced-option-handle", function (e) {
        var $handle = $(this);
        var $modal = $('#' + $(this).data('modal'));
        var $option = $(this).closest('td').find('input.all-options');
        $modal
            .uiModal({
                transition: 'fade up',
                onShow    : function () {
                    $modal.find('input[name="price_increment"]').attr('value', $option.next('input').val());
                },
                onApprove : function () {
                    var price_increment = parseFloat($modal.find('input[name="price_increment"]').val());
                    if (isNaN(price_increment)) {
                        price_increment = '0';
                    }
                    if (price_increment > 0) {
                        $handle.addClass('active');
                    } else {
                        $handle.removeClass('active');
                    }
                    $option.next('input').attr('value', price_increment);
                }
            })
            .uiModal('show')
        ;
    });
    // Draggable form fields
    $(".sortable").sortable({
        axis           : "y",
        handle         : ".tb-drag-handle",
        placeholder    : "tb-placeholder",
        opacity        : 0.5,
        scroll         : false,
        forceHelperSize: true,
        update         : function (event, ui) {
            var hook = ui.item.find('.tb-save-custom-field, .tb-save-builtin-field').data('hook');
            var serviceid = ui.item.find('.tb-save-custom-field, .tb-save-builtin-field').data('serviceid');
            var where = ui.item.index();
            var $moved = ui.item.find('.tbk-form-field-draggable');
            $moved.addClass('tbk-loading');
            $.post(
                TB_vars.post_url,
                {
                    action    : 'tbk_move_field',
                    _wpnonce  : TB_vars.wpNonce,
                    hook      : hook,
                    service_id: serviceid,
                    where     : where
                },
                function (response) {
                    response = tbUnwrapAjaxResponse(response);
                    $moved.removeClass('tbk-loading');
                }
            );
        }
    });

    $('#remove-selected-services').on('click', function (e) {
        // preventing default anchor action
        e.preventDefault();
        // collecting services ids
        var services = [];
        $('#tbk-services-list .tb-table-select-row:checked').each(function () {
            services.push($(this).data('row'));
        });
        // configuring and calling the confirmation modal
        $('#tb-service-delete-selected-modal')
            .uiModal({
                transition: 'fade up',
                onApprove : function ($clicked) {
                    if ($clicked.hasClass('loading')) {
                        return false;
                    }
                    $clicked.addClass('loading');
                    $.post(
                        TB_vars.post_url,
                        {
                            action  : 'tbk_delete_selected_services',
                            _wpnonce: TB_vars.wpNonce,
                            services: JSON.stringify(services)
                        },
                        function (response) {
                            response = tbUnwrapAjaxResponse(response);
                            if (response.slice(0, 4) == 'http') {
                                location.reload();
                            } else {
                                $clicked.removeClass('loading');
                                console.log(response);
                            }
                        }
                    );
                    return false;
                }
            })
            .uiModal('show')
        ;
    });

    tbToggleService = function (activate, service_id, personal) {
        $.post(
            TB_vars.post_url,
            {
                action        : 'tbk_toggle_service',
                _wpnonce      : TB_vars.wpNonce,
                service_action: activate,
                service_id    : service_id,
                personal      : personal
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
            }
        );
    };

    tbToggleCalendarIndependency = function (indep, calendar_id) {
        $.post(
            TB_vars.post_url,
            {
                action     : 'tbk_toggle_gcal_indep',
                _wpnonce   : TB_vars.wpNonce,
                independent: indep,
                calendar_id: calendar_id
            },
            function (response) {
                console.log(tbUnwrapAjaxResponse(response));
            }
        );
    };

    tbInsertAtCaret = function (areaId, text) {
        var txtarea = document.getElementById(areaId);
        var scrollPos = txtarea.scrollTop;
        var strPos = 0;
        var br = ((txtarea.selectionStart || txtarea.selectionStart === '0') ?
            "ff" : (document.selection ? "ie" : false));
        if (br === "ie") {
            txtarea.focus();
            var range = document.selection.createRange();
            range.moveStart('character', -txtarea.value.length);
            strPos = range.text.length;
        }
        else if (br === "ff")
            strPos = txtarea.selectionStart;
        var front = (txtarea.value).substring(0, strPos);
        var back = (txtarea.value).substring(strPos, txtarea.value.length);
        txtarea.value = front + text + back;
        strPos = strPos + text.length;
        if (br === "ie") {
            txtarea.focus();
            var range = document.selection.createRange();
            range.moveStart('character', -txtarea.value.length);
            range.moveStart('character', strPos);
            range.moveEnd('character', 0);
            range.select();
        }
        else if (br === "ff") {
            txtarea.selectionStart = strPos;
            txtarea.selectionEnd = strPos;
            txtarea.focus();
        }
        txtarea.scrollTop = scrollPos;
    };

    $('#team-booking-export-settings').click(function (e) {
        e.preventDefault();
        $('#team-booking-export-settings_form').submit();
    });

    $('#team-booking-import-settings_modal')
        .uiModal({
            transition: 'fade up',
        })
        .uiModal('attach events', '#team-booking-import-settings', 'show')
        .find('.positive.button')
        .click(function (e) {
            e.preventDefault();
            $('#team-booking-import-settings_form').submit();
        })
    ;

    $('#team-booking-repair-database_modal')
        .uiModal({
            transition: 'fade up',
            onApprove : function ($clicked) {
                $clicked.addClass('tbk-loading');
                $.post(
                    TB_vars.post_url,
                    {
                        action  : 'tbk_repair_db',
                        _wpnonce: TB_vars.wpNonce
                    },
                    function (response) {
                        response = tbUnwrapAjaxResponse(response);
                        if (response.slice(0, 4) == 'http') {
                            window.location.replace(response);
                        } else {
                            $clicked.removeClass('tbk-loading');
                        }
                    }
                );
                return false;
            }
        })
        .uiModal('attach events', '#team-booking-repair-database', 'show')
    ;

    $('#team-booking-import-core-json_modal')
        .uiModal({
            transition: 'fade up',
            onApprove : function ($clicked) {
                $clicked.addClass('loading');
                var form = $('#team-booking-import-core-json_form');
                form.find('.json-errors').hide();
                // File handling is done with FormData objects
                // It requires the jQuery.ajax method
                var data = new FormData();
                data.append('_wpnonce', TB_vars.wpNonce);
                data.append('action', 'tbk_core_from_json');
                form.find('input[type="file"]').each(function () {
                    var fileInputName = $(this).attr('name'); //settings_json_file
                    data.append(fileInputName, this.files[0]);
                });
                $.ajax({
                    url        : TB_vars.post_url,
                    type       : 'POST',
                    // Collect form data
                    data       : data,
                    processData: false,
                    contentType: false,
                    success    : function (response) {
                        response = tbUnwrapAjaxResponse(response);
                        if (response === 'no file') {
                            form.find('.no_file').show();
                        } else if (response === 'uri mismatch') {
                            form.find('.uri_mismatch').show();
                        } else if (response === 'invalid file') {
                            form.find('.invalid_file').show();
                        } else {
                            window.location.href = response;
                        }
                        $clicked.removeClass('loading');
                    }
                });
                return false;
            }
        })
        .uiModal('attach events', '#team-booking-import-core-json', 'show')
    ;

    $('body')
        .on('click keypress', '.tb-toggle-email-editor', function (e) {
            e.preventDefault();
            if (e.which == 13 || e.which == 32 || e.which == 1) {
                $(this).toggleClass('orange');
                $(this).siblings('.tb-email-editor').slideToggle('fast');
            }
        })
        .on('click keypress', '.tb-hook-placeholder', function (e) {
            e.preventDefault();
            if (e.which == 13 || e.which == 32 || e.which == 1) {
                var text = $(this).data('value');
                if (typeof $(this).data('open') !== "undefined") {
                    var open = $(this).data('open');
                    var close = $(this).data('close');
                    text = $.trim(prompt(text, text));
                    if (!text) {
                        text = $(this).data('value')
                    }
                    text = open + text + close;
                }
                var textarea_id = $(this).closest('li').find('textarea').attr("id");
                tbInsertAtCaret(textarea_id, text);
                $(this).closest('form:not(.ays-ignore)').trigger('rescan.areYouSure');
            }
        })
        .on('click keypress', '.team-booking-new-token', function (e) {
            e.preventDefault();
            if (e.which == 13 || e.which == 32 || e.which == 1) {
                var $table = $(this).closest('.tbk-panel').find('table.widefat');
                $table.addClass('tbk-loading');
                $.post(
                    TB_vars.post_url,
                    {
                        action  : 'tbk_create_api_token',
                        _wpnonce: TB_vars.wpNonce,
                        write   : $(this).data('write')
                    },
                    function (response) {
                        response = tbUnwrapAjaxResponse(response);
                        if (response.slice(0, 4) == 'http') {
                            location.reload();
                        } else {
                            $table.removeClass('tbk-loading');
                            console.log(response);
                        }
                    }
                );
            }
        })
        .on('click keypress', '.tbk-token-action-delete', function (e) {
            e.preventDefault();
            var $clicked = $(this);
            if (e.which == 13 || e.which == 32 || e.which == 1) {
                $clicked.addClass('tbk-loading');
                var token = $(this).data('token');
                $.post(
                    TB_vars.post_url,
                    {
                        action  : 'tbk_revoke_api_token',
                        _wpnonce: TB_vars.wpNonce,
                        token   : token
                    },
                    function (response) {
                        response = tbUnwrapAjaxResponse(response);
                        if (response.slice(0, 4) == 'http') {
                            location.reload();
                        } else {
                            $clicked.removeClass('tbk-loading');
                            console.log(response);
                        }
                    }
                );
            }
        })
        .on('click', '.tbk-email-reminder-send-manually', function (e) {
            e.preventDefault();
            if (e.which == 13 || e.which == 32 || e.which == 1) {
                var $clicked = $(this);
                $clicked.closest('.tb-data').addClass('tbk-loading');
                var id = $(this).attr('data-id');
                $.post(
                    TB_vars.post_url,
                    {
                        action        : 'tbk_send_email_reminder_manually',
                        _wpnonce      : TB_vars.wpNonce,
                        reservation_id: id
                    },
                    function (response) {
                        response = tbUnwrapAjaxResponse(response);
                        $clicked.closest('.tb-data').removeClass('tbk-loading');
                        if (response === 'ok') {
                            $clicked.parent().find('.tbk-email-reminder-sent').show();
                            $clicked.parent().find('.tbk-email-reminder-notsent').hide();
                            $clicked.remove();
                        } else {
                            alert('NOTICE: e-mail cannot be sent, the event is expired');
                        }
                    }
                );
            }
        })
        .on('click keypress', '.tbk-reset-customer-reservation-limit', function (e) {
            e.preventDefault();
            if (e.which == 13 || e.which == 32 || e.which == 1) {
                var $clicked = $(this);
                var $table = $clicked.closest('table.widefat');
                $table.addClass('tbk-loading');
                $.post(
                    TB_vars.post_url,
                    {
                        action     : 'tbk_reset_enum_res',
                        _wpnonce   : TB_vars.wpNonce,
                        service_id : $clicked.data('service'),
                        customer_id: $clicked.data('customer')
                    },
                    function (response) {
                        response = tbUnwrapAjaxResponse(response);
                        if (response.slice(0, 4) == 'http') {
                            location.reload();
                        } else {
                            $clicked.closest('td').find('.tbk-customer-reservation-limit').removeClass('orange').addClass('green').html($clicked.data('text'));
                            $table.removeClass('tbk-loading');
                        }
                    }
                );
            }
        })
    ;

    $('.tbk-reservations-action-details').click(function (e) {
        e.preventDefault();
        $clicked = $(this);
        $clicked.addClass('tbk-loading');
        var reservation_id = $clicked.data('reservation');
        $.post(
            TB_vars.post_url,
            {
                action        : 'tbk_get_res_details',
                _wpnonce      : TB_vars.wpNonce,
                reservation_id: reservation_id
            },
            function (response) {
                response = tbUnwrapAjaxResponse(response);
                $('.tbk-reservation-details-modal').replaceWith(response);
                $('.tbk-reservation-details-modal')
                    .uiModal({
                        transition   : 'fade up',
                        allowMultiple: true
                    })
                    .uiModal('show')
                ;
                $clicked.removeClass('tbk-loading');
            }
        );
    });

    $('.tbk-slots-action-details').on('click keypress', function (e) {
        e.preventDefault();
        if (e.which == 13 || e.which == 32 || e.which == 1) {
            var $clicked = $(this);
            var modal_id = $clicked.data('modal');
            $('#' + modal_id)
                .uiModal({
                    transition   : 'fade up',
                    allowMultiple: true
                })
                .uiModal('show')
            ;
        }
    });

    $('.tb-show-customer-reservations').click(function (e) {
        e.preventDefault();
        var modal_id = $(this).data('modal');
        $('#' + modal_id)
            .uiModal({
                transition: 'fade up',
            })
            .uiModal('show')
        ;
    });

    // AreYouSUre?
    $('form:not(.ays-ignore)').areYouSure({'message': 'The changes you made will be lost if you navigate away from this page.'});

})
; // <-- end of document.ready

function tbUnwrapAjaxResponse(response) {
    var $return = response.replace(/^\s*[\r\n]/gm, "").match(/!!TBK-START!!(.*[\s\S]*)!!TBK-END!!/);
    if ($return === null) {
        console.log(response);
        return response;
    } else {
        return jQuery.trim($return[1]);
    }
}

(function ($) {
    // Add Color Picker to all inputs that have 'tb-color-field' class
    $(function () {
        $('.tb-color-field').wpColorPicker({
            change: function (event, ui) {
                var name = $(this).attr('name');
                // event = standard jQuery event, produced by whichever control was changed.
                // ui = standard jQuery UI object, with a color member containing a Color.js object
                var c = ui.color.toString().substring(1);      // strip #
                var rgb = parseInt(c, 16);   // convert rrggbb to decimal
                var r = (rgb >> 16) & 0xff;  // extract red
                var g = (rgb >> 8) & 0xff;  // extract green
                var b = (rgb >> 0) & 0xff;  // extract blue
                var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b; // per ITU-R BT.709
                if (name === 'tb-background-color') {
                    $(".tb-frontend-calendar").css('background', ui.color.toString());
                    if (luma > 145) {
                        $(".tb-day:not(#free-slot, #soldout-slot), .tbk-row-preview").css('color', '#414141');
                    } else {
                        $(".tb-day:not(#free-slot, #soldout-slot), .tbk-row-preview").css('color', '#FFFFFF');
                    }
                } else if (name === 'tb-weekline-color') {
                    $("#week-line").css('background-color', ui.color.toString());
                    if (luma > 145) {
                        $("#week-line").css('color', '#414141');
                    } else {
                        $("#week-line").css('color', '#FFFFFF');
                    }
                }
                else if (name === 'tb-freeslot-color') {
                    $("#free-slot").css('background', ui.color.toString());
                    if (luma > 145) {
                        $("#free-slot").css('color', '#414141');
                    } else {
                        $("#free-slot").css('color', '#FFFFFF');
                    }
                }
                else if (name === 'tb-soldoutslot-color') {
                    $("#soldout-slot").css('background', ui.color.toString());
                    if (luma > 145) {
                        $("#soldout-slot").css('color', '#414141');
                    } else {
                        $("#soldout-slot").css('color', '#FFFFFF');
                    }
                }
            }
        });
    });
})(jQuery);