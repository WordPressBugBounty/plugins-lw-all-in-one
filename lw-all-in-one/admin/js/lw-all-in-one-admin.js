(function ($) {
	"use strict";
	const {
		__,
		_x,
		_n,
		_nx
	} = wp.i18n;
	$(document).ready(function () {

		$('.lwaio-color-picker').wpColorPicker();

		$("#lw_all_in_one_privacy_policy_pages").submit(function (event) {
			event.preventDefault();
			var form_data = new FormData(this);
			form_data.append('action', 'lw_all_in_one_create_privacy_pages');
			form_data.append('security', lw_all_in_one_admin_ajax_object.security);
			$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: form_data,
					contentType: false,
					cache: false,
					processData: false
				})
				.done(function (response) {
					if (response.success === true) {
						var createdPages = response.data;
						$.each(createdPages, function (index, value) {
							if (value.status === 'success') {
								$('#created_pages_response').append('<div class="notice notice-success inline"><p>' + value.message + '</p></div>');
							} else {
								$('#created_pages_response').append('<div class="notice notice-error inline"><p>' + value.message + '</p></div>');
							}
						});
					} else {
						$('#created_pages_response').append('<div class="notice notice-error inline"><p>__( \'Something went wrong with the request!\', \'lw_all_in_one\' )</p></div>');
					}
				});
		});

		$(document).on('click', '#wim_verify_attivation', function (event) {
			event.preventDefault();
			var form_data = new FormData();
			form_data.append('action', 'lw_all_in_one_verify_wim_attivation');
			form_data.append('security', lw_all_in_one_admin_ajax_object.security);
			$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: form_data,
					contentType: false,
					cache: false,
					processData: false
				})
				.done(function (response) {
					if (response.success === true) {
						$('#verification_status_response').append('<div class="notice notice-success inline"><p>' + response.data['message'] + '</p></div>');
						$('#wim_fields_verification_status_resp').val('verified');
						$('#wim_fields_token_resp').val(response.data.fields['token']);
						$('#wim_fields_auto_show_wim').val(response.data.fields['auto_show_wim']);
						$('#wim_fields_show_wim_after').val(response.data.fields['show_wim_after']);
						$('#wim_fields_show_mobile').val(response.data.fields['show_mobile']);
						$('#wim_fields_lingua').val(response.data.fields['lingua']);
						$('#wim_fields_messaggio_0').val(response.data.fields['messaggio_0']);
						$('#wim_fields_messaggio_1').val(response.data.fields['messaggio_1']);
						window.setTimeout(
							document.getElementById("lw_all_in_one_options").submit.click(),
							3000
						);
					} else {
						$('#verification_status_response').append('<div class="notice notice-error inline"><p>' + response.data + '</p></div>');
					}
				});
		});

		$(document).on('click', '#lw_aio_delete_record', function (event) {
			if (!confirm(__('Are you sure you want to delete this record?', 'lw_all_in_one'))) {
				event.preventDefault();
			}
		});

		$("#lw_aio_saved_cf7_records, #lw_aio_saved_ga_events_records").submit(function (event) {
			var actionTop = $("select[name=action]").val();
			var actionBottom = $("select[name=action2]").val();
			if ((actionTop == 'bulk-delete-cf7' || actionTop == 'bulk-delete-ga') || (actionBottom == 'bulk-delete-cf7' || actionBottom == 'bulk-delete-ga')) {
				if (!confirm(__('Are you sure you want to delete this record?', 'lw_all_in_one'))) {
					event.preventDefault();
				}
			}
		});

		$(document).on('click', '#lw_aio_reset_data', function (event) {
			event.preventDefault();
			if (confirm(__('Are you sure you want to reset plugin options?', 'lw_all_in_one'))) {
				var form_data = {
					action: 'lw_all_in_one_reset_plugin_options',
					security: lw_all_in_one_admin_ajax_object.security,
				};
				$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: form_data,
						cache: false,
						processData: true
					})
					.done(function (response) {
						if (response.success === true) {
							location.replace('/wp-admin/admin.php?page=lw_all_in_one');
						}
					});
			}
		});

	});
})(jQuery);