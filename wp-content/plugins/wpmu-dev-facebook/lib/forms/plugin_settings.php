<?php
function wdfb_do_settings_sections( $page ) {
	global $wp_settings_sections, $wp_settings_fields;

	if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $page ] ) ) {
		return;
	}

	foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
		echo '<div class="postbox wdfb-settings_section">';
		if ( $section['title'] ) {
			echo "<h3 class='hndle' id='wdfb-section_header-{$section['id']}'><span><label>{$section['title']}</label></span></h3>\n";
		}
		call_user_func( $section['callback'], $section );
		if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
			continue;
		}
		echo '<div class="inside"><form autocomplete="off"><table class="form-table" id="wdfb-section-' . $section['id'] . '">';
		do_settings_fields( $page, $section['id'] );
		echo '<tr><th></th><td>' .
		     '<a href="#" class="button button-primary wdfb-save_settings" data-wdfb_section_id="' . $section['id'] . '">' .
		     __( 'Save changes to this section', 'wdfb' ) .
		     '</a>' .
		     '</td></tr>';
		echo '</table></form></div>';
		echo '</div>';
	}
}

?>

<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e( 'Ultimate Facebook settings', 'wdfb' ); ?></h2>


	<?php settings_fields( 'wdfb' ); ?>
	<div class="metabox-holder" id="wdtg_accordion">
		<?php wdfb_do_settings_sections( 'wdfb_options_page' ); ?>
	</div>


</div>

<style type="text/css">
	#icon-wdfb.icon32 {
		background: url(<?php echo WDFB_PLUGIN_URL?>/img/facebook-32.png) top left no-repeat;
	}

	.postbox.wdfb-settings_section .hndle {
		cursor: pointer;
	}

	.wdfb-api_connect-success {
		background: url(<?php echo WDFB_PLUGIN_URL?>/img/ok-16.png) center left no-repeat;
		padding: 2px;
		padding-left: 18px;
	}

	.wdfb-api_connect-failure {
		background: url(<?php echo WDFB_PLUGIN_URL?>/img/cancel-16.png) center left no-repeat;
		padding: 2px;
		padding-left: 18px;
	}
</style>

<script type="text/javascript">
(function ($) {

	/**
	 * Respond to invalid API settings.
	 */
	function invalid_api_settings() {
		$(".wdfb-settings_section h3")
			.unbind('click')
			.click(function () {
				return false;
			})
		;
		$(".wdfb-api_connect-success").remove();
		$("#wdfb-section-wdfb_api .wdfb-api_connect-result").html(
			"<b class='wdfb-api_connect-failure'><?php echo esc_js(__('Please, enter correct Facebook API settings and make sure Facebook app is public', 'wdfb')); ?></b>"
		);
	}

	/**
	 * Parse header hash and select appropriate accordion item, if any
	 */
	function check_hash() {
		if (window.location.hash && window.location.hash.match(/#wdfb-section_header/)) {
			var active_header_id = window.location.hash.replace(/^#/, '');
			var $active_header = $("#" + active_header_id);
			if ($active_header.length) $active_header.click();
			return true;
		}
		return false;
	}

	/**
	 * Checks if the user is logged into Facebook
	 * and toggles mapping fields availability.
	 */
	function check_autopost_prerequisites() {
		FB.getLoginStatus(function (resp) {
			if (resp.authResponse && resp.authResponse.userID) return true; // All good
			$selects = $('#wdfb-section-wdfb_autopost select[name^="wdfb_autopost"], #wdfb-section-wdfb_autopost .wdfb-autopost-shortlink');
			$selects.attr("disabled", true);
			$("#wdfb-autopost_map_message")
				.addClass('error below-h2')
				.html(
				'<p><?php echo esc_js(__('You need to be logged into Facebook with at least basic permissions set granted to set up Autopost mappings.', 'wdfb')); ?> ' +
				'<a href="<?php echo $this->model->fb->getLoginUrl(); ?>"><?php echo esc_js(__('Click here to do so now', 'wdfb')); ?></a></p>' +
				'<p><?php echo esc_js(__('Once the mappings are set up, you do not need to be logged into Facebook for Autoposting to work.', 'wdfb')); ?></p>'
			)
			;
		});
	}

	$(function () {

// Inject next step buttons
		$(".wdfb-settings_section:gt(0):not(:last) .wdfb-save_settings").after(
			' <a class="button wdfb-next_step" href="#"><?php echo esc_js('... or move on to next step', 'wdfb');?></a>'
		);

// Initialize quasi-accordion and setup behaviors
		$(".wdfb-settings_section .inside").hide();
		$(".wdfb-settings_section h3").click(function () {
			var $me = $(this);
			$(".wdfb-settings_section .inside").hide();
			$me.parents('.wdfb-settings_section').find('.inside').show();
			return false;
		});

// Initial item position
		if (!check_hash()) {
			$(".wdfb-settings_section:first h3").click();
		}

		<?php if (!is_network_admin()) { ?>
// Disable all other steps if we don't have enough credentials
		if (!$('#app_key').val() || !$('#secret_key').val()) {
			invalid_api_settings();
		} else {
			// Validate app ID
			$.post(ajaxurl, {
				"action": "wdfb_check_api_status",
				"network": false
			}, function (res) {
				var name = false;
				try {
					name = res.data;
				} catch (e) {
					name = false;
				}
				if (!name) {
					invalid_api_settings();
					return false;
				}
				$("#wdfb-section-wdfb_api .wdfb-api_connect-result").html(
					"<b class='wdfb-api_connect-success'><?php echo esc_js(__('Successfully connected to ', 'wdfb')); ?>" + name + "</b>"
				);
				$("#wdfb-section-wdfb_api .wdfb-save_settings").after(
					' <a href="#" class="button wdfb-next_step"><?php echo esc_js(__('Next step', 'wdfb'));?></a>'
				);
			}, 'json').error(function () {
				invalid_api_settings();
			});
		}
		<?php } else { ?>
// We're in network admin, just make a note.
		if (!$('#app_key').val() || !$('#secret_key').val()) {
			$("#wdfb-section-wdfb_api .wdfb-api_connect-result").html(
				"<b class='wdfb-api_connect-failure'><?php echo esc_js(__('No API info - some settings will be unavailable.', 'wdfb')); ?></b>"
			);
		} else {
			$.post(ajaxurl, {
				"action": "wdfb_check_api_status",
				"network": true
			}, function (res) {
				var name = false;
				try {
					name = res.data;
				} catch (e) {
					name = false;
				}
				if (!name) {
					$(".wdfb-api_connect-success").remove();
					$("#wdfb-section-wdfb_api .wdfb-api_connect-result").html(
						"<b class='wdfb-api_connect-failure'><?php echo esc_js(__('Please, enter correct Facebook API settings', 'wdfb')); ?></b>"
					);
					return false;
				}
				$("#wdfb-section-wdfb_api .wdfb-api_connect-result").html(
					"<b class='wdfb-api_connect-success'><?php echo esc_js(__('Successfully connected to ', 'wdfb')); ?>" + name + "</b>"
				);
			}, 'json').error(function () {
				$(".wdfb-api_connect-success").remove();
				$("#wdfb-section-wdfb_api .wdfb-api_connect-result").html(
					"<b class='wdfb-api_connect-failure'><?php echo esc_js(__('Please, enter correct Facebook API settings', 'wdfb')); ?></b>"
				);
			});
		}
		<?php } ?>

// Next step switching
		$(document).on('click', ".wdfb-next_step", function () {
			var $parent = $(this).parents(".wdfb-settings_section");
			var $next = $parent.next(".wdfb-settings_section").find("h3.hndle");
			if ($next.length) $next.click();
			return false;
		});

		/* ----- Saving section data ----- */

// Section save deferred posting - requires at least jQuery 1.5
		function wdfb_send_save_request(part, data) {
			// Wrap request in deferred, resolve once the paging completes
			var dreq = $.Deferred(function () {
				$.post(ajaxurl, {
					"action": "<?php echo (
				(defined('WP_NETWORK_ADMIN') && WP_NETWORK_ADMIN)
					? "wdfb_network_partial_data_save"
					: "wdfb_partial_data_save"
				); ?>",
					"part": part,
					"data": data
				}, function (response) {
					if (response && response.page) { // So, we're paging, obviously
						// Prepare data paging
						data = data.match(/&page=\d+/)
							? data.replace(/&page=\d+/, '&page=' + response.page)
							: data + '&page=' + response.page
						;
						wdfb_send_save_request(part, data); // More paging to do, rebind deferred recursively
					} else {
						dreq.resolve();
					} // We're done paging, resolve the deferred
				}, 'json');
			});

//	Reload when we're done
			$.when(dreq).then(function () {
				$("#wdfb-save_settings-waiting").after(
					"<?php echo esc_js(__('Done. Applying new settings, please hold on.', 'wdfb')); ?>"
				).remove();
				var loc = window.location;
				loc.hash = '#wdfb-section_header-' + part;
				window.location = loc;
				window.location.reload();
			});
		}

// Section save handler
		$(".wdfb-save_settings").click(function () {
			var $me = $(this),
				section_id = $me.attr("data-wdfb_section_id"),
				$section = $("#wdfb-section-" + section_id);
			if (!$section.length) return false;

			$me.after(
				'<img src="' + _wdfb_root_url + '/img/waiting.gif" id="wdfb-save_settings-waiting">'
			).remove();

			/*
			 $.post(ajaxurl, {
			 "action": "
			<?php echo (
						(defined('WP_NETWORK_ADMIN') && WP_NETWORK_ADMIN)
							? "wdfb_network_partial_data_save"
							: "wdfb_partial_data_save"
						); ?>",
			 "part": section_id,
			 "data": $section.parents("form:first").serialize()
			 }, function () {
			 var loc = window.location;
			 loc.hash = '#wdfb-section_header-' + section_id;
			 window.location = loc;
			 window.location.reload();
			 });
			 */

			var data = $section.parents("form:first").serialize(),
				request = wdfb_send_save_request(section_id, data);

			return false;
		});

// Skips - to 0
		$('.wdfb_skip_to_step_0').click(function () {
			$("#wdfb-section_header-wdfb_api").click();
			return false;
		});
// Skips - to 1
		$('.wdfb_skip_to_step_1').click(function () {
			$("#wdfb-section_header-wdfb_grant").click();
			return false;
		});

// Comments import
		$('.wdfb_import_comments_now').click(function () {
			var $me = $(this);
			var oldHtml = $me.html();
			$me.html('<img src="' + _wdfb_root_url + '/img/waiting.gif">');
			$.post(ajaxurl, {"action": "wdfb_import_comments"}, function (data) {
				$me.html(oldHtml);
			});
			return false;
		});

// Profile mappings
		$('#wdfb_connect_add_mapping').click(function () {
			var $clone = $('.wdfb_connect_wp_registration:last').clone();

			var oldName = $clone.find('input:text').attr('name');
			if (!oldName) return false;

			var oldId = parseInt(oldName.replace(/[^0-9]/g, ''));
			if (!oldId) return false;

			var newId = oldId + 1;
			var newNameWp = oldName.replace(/\[\d+\]/, '[' + newId + ']');
			var newNameFb = newNameWp.replace(/\[wp\]/, '[fb]');

			$clone
				.find('input:text')
				.attr('name', newNameWp)
				.val('')
				.end()
				.find('select')
				.attr('name', newNameFb)
				.val('')
				.find('option:first').attr('selected', true)
			;
			$('#wdfb_connect_wp_registration_container').append($clone);
			return false;
		});

// Auto-check blog trumping settings
		if ($("#prevent_blog_settings").is(":checked")) {
			$("#_override_all").attr("checked", true);
			$('input.wdfb_checkbox_helper[name="_override_all"]').val(1);
		}
		$("#prevent_blog_settings").change(function () {
			if ($(this).is(":checked")) {
				$("#_override_all").attr("checked", true);
				$('input.wdfb_checkbox_helper[name="_override_all"]').val(1);
			} else {
				$("#_override_all").attr("checked", false);
				$('input.wdfb_checkbox_helper[name="_override_all"]').val(0);
			}
		});

// Map FB UIDs to names
		$(".wdfb_uid_to_name_mapper").each(function () {
			var $me = $(this);
			var uid = $.trim($me.text());
			if (!uid) return true;

			var page_query = "SELECT name from page WHERE page_id=" + uid;
			var user_query = "SELECT name FROM user WHERE uid=" + uid;
			FB.api({
				"method": "fql.multiquery",
				"queries": {
					"page_query": page_query,
					"user_query": user_query
				}
			}, function (resp) {
				var name = false;
				try {
					$.each(resp, function (idx, obj) {
						if (obj.fql_result_set && obj.fql_result_set.length) {
							name = obj.fql_result_set[0].name;
							return false;
						}
					});
				} catch (e) {
				}
				if (name) $me.html(name);
			});
		});

// Toggle custom OG type
		function toggle_custom_og_type() {
			if ($("#og_custom_type").is(":checked")) $("#og_custom_mapping").find("input:text").attr("disabled", false).end().show();
			else $("#og_custom_mapping").find("input:text").attr("disabled", true).end().hide();
		}

		$("#og_custom_type").change(toggle_custom_og_type);
		toggle_custom_og_type();

// Extra OG mappings removal
		$(".wdfb_og_remove_extra").click(function () {
			$(this).parents(".wdfb_og_extra_mapping").remove();
			return false;
		});

// Checking autopost setup prerequisites
		check_autopost_prerequisites();

// Restart tutorial
		$(".wdfb-restart_tutorial").click(function () {
			var $me = $(this);
			// Change UI
			$me.after(
				'<img src="' + _wdfb_root_url + '/img/waiting.gif" />'
			).remove();
			// Do call
			$.post(ajaxurl, {
				"action": "wdfb_restart_tutorial"
			}, function () {
				window.location.reload();
			});
			return false;
		});

		$("#wdfb-refresh_access_token").on("click", function () {
			var perms = $(this).attr("data-wdfb_perms");
			FB.login(function (response) {
				if (response.authResponse) {
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {"action": "wdfb_refresh_access_token"},
						success: function (res) {
							location.reload(true);
						}
					});
				}
			}, {"scope": perms});
			return false;
		});
		$("#wdfb-remap_user").on("click", function () {
			var perms = $(this).attr("data-wdfb_perms"),
				wdfb_remap_login = function () {
					FB.login(function (response) {
						if (response.authResponse) {
							$.ajax({
								'url': ajaxurl,
								'type': 'POST',
								'data': {"action": "wdfb_remap_user"},
								success: function(res){
									location.reload(true);
								}
							});
						}
					}, {"scope": perms});
				}
			FB.getLoginStatus(function (status) {
				if (status.authResponse) FB.logout(wdfb_remap_login);
				else wdfb_remap_login();
			});
			return false;
		});

		$(".wdfb-cache_purge").on("click", function () {
			var $me = $(this),
				purge = $me.attr("data-wdfb_purge")
				;
			if (!purge) return false;
			$.post(ajaxurl, {"action": "wdfb_cache_purge", "purge": purge}, window.location.reload);
			return false;
		});

	});
})(jQuery);
</script>