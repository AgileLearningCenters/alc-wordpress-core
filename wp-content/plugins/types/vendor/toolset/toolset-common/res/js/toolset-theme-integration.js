var ToolsetCommon = ToolsetCommon || {};

ToolsetCommon.ThemeIntegration = function ($) {
    var self = this;

    self.hintPointer = null;

    self.makeAjaxCall = function (data, successCallback, errorCallback) {
        $.ajax({
            async: true,
            type: 'POST',
            url: ajaxurl,
            data: data,
            success: successCallback,
            error: errorCallback
        });
    };


    self.renderVisibleSections = function (display_type, keep_cred_message) {
        if (display_type === null) {
            $('.theme-settings-section').hide();
            $('.js-toolset-non-assigned-message').show();
            $('.shared-section').hide();
        } else if (display_type == 'archive') {
            $('.archive-section').show();
            $('.posts-section').hide();
            $('.shared-section').show();
            $('.js-toolset-non-assigned-message').hide();
        } else if (display_type == 'archive-cred') {
            $('.archive-section').show();
            $('.posts-section').hide();
            $('.shared-section').show();
            $('.js-toolset-non-assigned-message').show();
        } else if(display_type == 'posts-cred'){
            $('.archive-section').hide();
            $('.posts-section').show();
            $('.shared-section').show();
            $('.js-toolset-non-assigned-message').show();
        }else if (display_type == 'posts') {
            $('.archive-section').hide();
            $('.posts-section').show();
            $('.shared-section').show();
            if(!keep_cred_message) {
                $('.js-toolset-non-assigned-message').hide();
            }
        }
    };

    self.disableSections = function() {
        jQuery('.theme-integration-section-disable').each(function(){
            var $section_labels = jQuery(this).parent().siblings('.theme-settings-section-content').find('label, p');
            var $section_summary = jQuery(this).parent().siblings('.theme-settings-section-summary');
            var $section_heading = jQuery(this).parent();

            jQuery(this).parent().siblings('.theme-settings-section-content').find(':input').prop('disabled', !$(this).is(":checked"));
            if(!$(this).is(":checked")){
                $section_labels.addClass('disabled-section');
                $section_heading.addClass('disabled-section');
                $section_summary.addClass('disabled-section');
            } else {
                $section_labels.removeClass('disabled-section');
                $section_heading.removeClass('disabled-section');
                $section_summary.removeClass('disabled-section');
            }
        });
    };

    self.init = function () {
        self.initHints();
        self.eventsOn();
        self.disableSections();
        self.add_layouts_edit_page_hooks();
        self.toggle_theme_options_box_visibility();
    }

    self.removeHints = function() {
        $('.js-theme-options-hint').each(function () {
            $(this).pointer('destroy');
        });
    }

    self.initHints = function(hintMessage) {
        $('.js-theme-options-hint').each(function () {
            var hintContent = "";

            if(typeof hintMessage == 'undefined') {
                hintContent = jQuery(this).data('content');
            } else {
                hintContent = hintMessage;
            }

            var hint = this;
            self.hintPointer = $(this).pointer({
                pointerClass: jQuery(this).data('classes'),
                content: '<h3>' + jQuery(this).data('header') + '</h3> <p>' + hintContent + '</p>',
                position: {
                    edge: ( $('html[dir="rtl"]').length > 0 ) ? 'right' : 'left',
                    align: 'center',
                    offset: '15 0'
                },
                buttons: function( event, t ) {
                    var button_close = $('<button class="button button-primary-toolset alignright js-wpv-close-this">'+Toolset_Theme_Integrations_Settings.strings.close+'</button>');
                    button_close.bind( 'click.pointer', function( e ) {
                        jQuery(hint).pointer('close');

                    });
                    return button_close;
                }
            });
        });
    };

    self.eventsOn = function () {
        $(document).on('change', '.toolset_page_ct-editor #toolset_theme_settings_form', function () {
            $('.toolset-theme-settings-spinner').addClass('is-active');
            var data = {
                action: 'toolset_theme_integration_save_ct_settings',
                id: WPViews.ct_edit_screen.ct_data.id,
                wpnonce: WPViews.ct_edit_screen.update_nonce,
                theme_settings: $('#toolset_theme_settings_form').serialize()
            };

            self.makeAjaxCall(data,
                function (originalResponse) {
                    $('.toolset-theme-settings-spinner').removeClass('is-active');
                    WPViews.ct_edit_screen.showSuccessMessage(WPViews.ct_edit_screen.action_bar_message_container.selector, WPViews.ct_edit_screen.l10n.editor.saved);
                    WPViews.ct_edit_screen.highlight_action_bar('success');
                    jQuery(document).trigger('ct_saved');
                }, function (ajaxContext) {
                    $('.toolset-theme-settings-spinner').removeClass('is-active');
                    console.log('Error:', ajaxContext.responseText);
                }
            );
        });

        $(document).on('change', '.toolset_page_view-archives-editor #toolset_theme_settings_form', function () {
            $('.toolset-theme-settings-spinner').addClass('is-active');

            var data = {
                action: 'toolset_theme_integration_save_wpa_settings',
                id: WPViews.wpa_edit_screen.view_id,
                wpnonce: wpv_editor_strings.editor_nonce,
                theme_settings: $('#toolset_theme_settings_form').serialize()
            };

            self.makeAjaxCall(data,
                function (originalResponse) {
                    WPViews.wpa_edit_screen.manage_action_bar_success(originalResponse.data);

                    $('.js-wpv-general-actions-bar').addClass('wpv-action-success');
                    setTimeout(function () {
                        $('.js-wpv-general-actions-bar').removeClass('wpv-action-success');
                    }, 1000);

                    $('.toolset-theme-settings-spinner').removeClass('is-active');
                    $(document).trigger('js_event_wpv_screen_options_saved');
                }, function (ajaxContext) {
                    $('.toolset-theme-settings-spinner').removeClass('is-active');
                    console.log('Error:', ajaxContext.responseText);
                }
            );
        });

        $(document).on('change', '.toolset_page_dd_layouts_edit     #toolset_theme_settings_form', function (evt) {
            $('input[name="save_layout"]').prop('disabled', false);
        });

        $('.js-theme-options-hint').on('click', function () {
            $(this).pointer('open');
        });

        jQuery('.theme-integration-section-disable').on('change', function(){
            self.disableSections();
        });

        jQuery(document).on('change', '.js-layout-used-for-cred', function() {
            if( $(this).val() === 'assigned' ) {
                self.renderVisibleSections( 'posts-cred', true );
            } else if( $(this).val() === 'archive' ) {
                self.renderVisibleSections( 'archive-cred', true );
            } else {
                self.renderVisibleSections( null );
            }
        });
    }

    self.self_through_layouts_ajax_call = function(){
        Toolset.hooks.addFilter('ddl_save_layout_params', function (save_params) {
            if ($('#toolset_theme_settings_form')) {
                save_params.theme_settings = $('#toolset_theme_settings_form').serialize();
            }
            return save_params;
        });
    };

    self.hook_into_layout_assignment_dialog_close = function(){
        jQuery(document).on('DLLayout.admin.ready', function () {
            DDLayout.changeLayoutUseHelper.eventDispatcher.listenTo(
                DDLayout.changeLayoutUseHelper.eventDispatcher,
                'assignment_dialog_close',
                function () {
                    var data = {
                        action: 'toolset_theme_integration_get_section_display_type',
                        nonce: jQuery('#toolset-theme-display-type').val(),
                        id: DDLayout.individual_assignment_manager._current_layout
                    };

                    self.makeAjaxCall(data,
                        function (originalResponse) {
                            if (originalResponse.data.hasOwnProperty('display_type')) {
                                self.renderVisibleSections(originalResponse.data.display_type);
                            }

                            if(originalResponse.data.hasOwnProperty('tooltip_message')) {
                                self.removeHints();
                                self.initHints(originalResponse.data.tooltip_message)
                            }
                        }
                    );
                }
            );
        });
    };

    self.add_layouts_edit_page_hooks = function(){
        if (jQuery('.toolset_page_dd_layouts_edit')[0]) {
            /**
             * Hooks to Layouts saving action and adds the theme settings object to the Layout object.
             */
            self.self_through_layouts_ajax_call();
            self.hook_into_layout_assignment_dialog_close();
        }
    };

    self.toggle_theme_options_box_visibility = function(){
        var $caret = jQuery( '.js-theme-settings-toggle' );

        $caret.on( 'click', function( event ){
            var $me = jQuery( this ),
                closed = $me.data( 'closed' ),
                $target = jQuery( '#toolset_theme_settings_form' );

            if( !closed ){
                $target.slideUp( 'fast', function(event){
                    $me.data( 'closed', true );
                    $me.find('.fa').removeClass( 'fa-caret-up' ).addClass( 'fa-caret-down' );
                    $me.parent('.theme-settings-wrap').addClass('theme-settings-wrap-collapsed');
                });
            } else {
                $target.slideDown( 'fast', function(event){
                    $me.data( 'closed', false );
                    $me.find('.fa').removeClass( 'fa-caret-down' ).addClass( 'fa-caret-up' );
					$me.parent('.theme-settings-wrap').removeClass('theme-settings-wrap-collapsed')
                });
            }
        });
    };

    self.init();

    return self;
};

jQuery(document).ready(function ($) {
    ToolsetCommon.theme_integration = new ToolsetCommon.ThemeIntegration($);
});