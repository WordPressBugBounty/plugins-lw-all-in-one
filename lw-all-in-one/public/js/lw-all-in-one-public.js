(function ($) {
	'use strict';

	function lwAioSaveGaEvent(gaCategory, gaAction, gaLabel) {
		if (lwAioSaveGaEvents === false) {
			return;
		}

		const data = {
			action: 'lw_all_in_one_save_ga_event',
			security: lw_all_in_one_save_ga_event_object.security,
			event_category: gaCategory,
			event_action: gaAction,
			event_label: gaLabel
		};
		jQuery.post(lw_all_in_one_save_ga_event_object.ajaxurl, data, function (response) {
			// console.log(response);
		});
	}

	function lwAioSendEvent(gaCategory, gaAction, gaLabel) {
		if ($.inArray(lwAioTrackingType, ["UA", "G"]) > -1) {
			gtag("event", gaAction, {
				"event_category": gaCategory,
				"event_label": gaLabel
			});
		}
	}

	$(window).on("load", function () {
		if (lwAioGaActivate) {
			if (lwAioMonitorEmailLink) {
				$('a[href^="mailto"]').click(function () {
					var gaCategory = this.getAttribute("data-vars-ga-category") || "email";
					var gaAction = this.getAttribute("data-vars-ga-action") || "send";
					var gaLabel = this.getAttribute("data-vars-ga-label") || this.href;
					lwAioSendEvent(gaCategory, gaAction, gaLabel);
					lwAioSaveGaEvent(gaCategory, gaAction, gaLabel);
				});
			}
			if (lwAioMonitorTelLink) {
				$('a[href^="tel"]').click(function () {
					var gaCategory = this.getAttribute("data-vars-ga-category") || "telephone";
					var gaAction = this.getAttribute("data-vars-ga-action") || "call";
					var gaLabel = this.getAttribute("data-vars-ga-label") || this.href;
					lwAioSendEvent(gaCategory, gaAction, gaLabel);
					lwAioSaveGaEvent(gaCategory, gaAction, gaLabel);
				});
			}
			if (lwAioMonitorFormSubmit) {
				$(".wpcf7").on('wpcf7mailsent', function (event) {
					// console.log(event);
					// return;
					var gaCategory = "form";
					var gaAction = "submit";
					var gaLabel = event.currentTarget.baseURI;
					lwAioSendEvent(gaCategory, gaAction, gaLabel);
					lwAioSaveGaEvent(gaCategory, gaAction, gaLabel);
				});
			}
		}
	});
	$(window).on("load", function () {
		if ($('[id="rag_soc"]').length > 1) {
			console.log('wim_twice');
		}
	});

})(jQuery);