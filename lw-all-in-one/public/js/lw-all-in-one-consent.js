USR_PREFS_CK_NAME = typeof USR_PREFS_CK_NAME !== "undefined" ? USR_PREFS_CK_NAME : "lwaio_consent_preferences";
ACTED_CONSENT_CK_NAME = typeof ACTED_CONSENT_CK_NAME !== "undefined" ? ACTED_CONSENT_CK_NAME : "lwaio_consent_acted";
EXP_CK_NAME = typeof EXP_CK_NAME !== "undefined" ? EXP_CK_NAME : '1 Year';

(function ($) {
	"use strict";
	let GDPR_Cookie = {
		set: function (name, value, days, domain = '') {
				let expires = "";
			if (days) {
				let date = new Date();
				date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
				expires = "; expires=" + date.toGMTString();
			}
			if (domain) {
				domain = "; domain=" + domain;
			}
			document.cookie = name + "=" + value + expires + "; path=/" + domain;
		},
		read: function (name) {
			let nameEQ = name + "=";
			let ca = document.cookie.split(";");
			let ca_length = ca.length;
			for (let i = 0; i < ca_length; i++) {
				let c = ca[i];
				while (c.charAt(0) == " ") {
					c = c.substring(1, c.length);
				}
				if (c.indexOf(nameEQ) === 0) {
					return c.substring(nameEQ.length, c.length);
				}
			}
			return null;
		},
		exists: function (name) {
			return this.read(name) !== null;
		},
		getallcookies: function () {
			let pairs = document.cookie.split(";");
			let cookieslist = {};
			let pairs_length = pairs.length;
			for (let i = 0; i < pairs_length; i++) {
				let pair = pairs[i].split("=");
				cookieslist[(pair[0] + "").trim()] = unescape(pair[1]);
			}
			return cookieslist;
		},
		erase: function (name, domain) {
			if (domain) {
				this.set(name, "", -10, domain);
			} else {
				this.set(name, "", -10);
			}
		},
	};

	let GDPR = {
		bar_config: {},
		show_config: {},
		allowed_categories: [],
		set: function (args) {
			if (typeof JSON.parse !== "function") {
				console.log(
					"LWAIOCookieConsent requires JSON.parse but your browser doesn't support it"
				);
				return;
			}

			this.settings = JSON.parse(args.settings);
			EXP_CK_NAME = this.settings.cookie_expiry;
			this.bar_elm = jQuery(this.settings.notify_div_id);
			this.show_again_elm = jQuery(this.settings.show_again_div_id);

			this.details_elm = this.bar_elm.find(".lwaio_messagebar_detail");

			/* buttons */
			this.main_button = jQuery("#cookie_action_accept");
			this.main_link = jQuery("#cookie_action_link");
			this.reject_button = jQuery("#cookie_action_reject");
			this.settings_button = jQuery("#cookie_action_settings");
			this.save_button = jQuery("#cookie_action_save");
			this.confirm_button = jQuery("#cookie_action_confirm");
			this.cancel_button = jQuery("#cookie_action_cancel");
			this.accept_all_button = jQuery("#cookie_action_accept_all");

			this.configBar();
			this.toggleBar();
			this.attachEvents();
			this.configButtons();

				if (this.settings.auto_scroll) {
					window.addEventListener("scroll", GDPR.acceptOnScroll, false);
				}
				let lwaio_consent_preferences = JSON.parse(
					GDPR_Cookie.read(USR_PREFS_CK_NAME)
				);
				let lwaio_consent_acted = GDPR_Cookie.read(ACTED_CONSENT_CK_NAME);
				let event = "";
					event = new CustomEvent("LwAioCookieConsentOnLoad", {
						detail: {
							lwaio_consent_preferences: lwaio_consent_preferences,
							lwaio_consent_acted: lwaio_consent_acted,
						},
					});
					window.dispatchEvent(event);
		},
		attachEvents: function () {
			jQuery(".lwaio_action_button").click(function (e) {
				e.preventDefault();
				let event = "";
				let lwaio_consent_preferences = "";
				let lwaio_consent_preferences_val = "";
				let lwaio_consent_acted = "";
				let elm = jQuery(this);
				let button_action = elm.attr("data-lwaio_action");

				if (button_action == "accept_all") {
					GDPR.enableAllCookies();
					GDPR.accept_all_close();
					lwaio_consent_preferences = JSON.parse(
						GDPR_Cookie.read(USR_PREFS_CK_NAME)
					);
					lwaio_consent_preferences_val = JSON.stringify(lwaio_consent_preferences);
					lwaio_consent_acted = GDPR_Cookie.read(ACTED_CONSENT_CK_NAME);
						GDPR_Cookie.set(
							USR_PREFS_CK_NAME,
							lwaio_consent_preferences_val,
							EXP_CK_NAME
						);
						event = new CustomEvent("LwAioCookieConsentOnAcceptAll", {
							detail: {
								lwaio_consent_preferences: lwaio_consent_preferences,
								lwaio_consent_acted: lwaio_consent_acted,
							},
						});
						window.dispatchEvent(event);

					GDPR.logConsent(button_action);
				} else if (button_action == "accept") {
					GDPR.accept_close();
					lwaio_consent_preferences = JSON.parse(
						GDPR_Cookie.read(USR_PREFS_CK_NAME)
					);
					lwaio_consent_preferences_val = JSON.stringify(lwaio_consent_preferences);
					lwaio_consent_acted = GDPR_Cookie.read(ACTED_CONSENT_CK_NAME);
						GDPR_Cookie.set(
							USR_PREFS_CK_NAME,
							lwaio_consent_preferences_val,
							EXP_CK_NAME
						);

						event = new CustomEvent("LwAioCookieConsentOnAccept", {
							detail: {
								lwaio_consent_preferences: lwaio_consent_preferences,
								lwaio_consent_acted: lwaio_consent_acted,
							},
						});
						window.dispatchEvent(event);

					GDPR.logConsent(button_action);
				} else if (button_action == "reject") {
					GDPR.reject_close();
					lwaio_consent_preferences = JSON.parse(
						GDPR_Cookie.read(USR_PREFS_CK_NAME)
					);
					lwaio_consent_acted = GDPR_Cookie.read(ACTED_CONSENT_CK_NAME);
					GDPR_Cookie.erase(USR_PREFS_CK_NAME);
					// GDPR_Cookie.erase('lwaio_consent_acted');
					event = new CustomEvent("LwAioCookieConsentOnReject", {
						detail: {
							lwaio_consent_preferences: lwaio_consent_preferences,
							lwaio_consent_acted: lwaio_consent_acted,
						},
					});
					window.dispatchEvent(event);
					let allCookiesList = GDPR_Cookie.getallcookies();
					if (typeof allCookiesList === "object" && allCookiesList !== null) {
						jQuery.each(allCookiesList, function (key, value) {
							if (key != ACTED_CONSENT_CK_NAME) {
								GDPR_Cookie.erase(key, "." + window.location.host);
							}
						});
					}

					GDPR.logConsent(button_action);
				} else if (button_action == "settings") {
					GDPR.bar_elm.slideUp(GDPR.settings.animate_speed_hide);
					GDPR.show_again_elm.slideUp(GDPR.settings.animate_speed_hide);
				} else if (button_action == "close") {
					GDPR.displayHeader();
				} else if (button_action == "show_settings") {
					GDPR.show_details();
				} else if (button_action == "hide_settings") {
					GDPR.hide_details();
				} else if (button_action == "close_banner") {
					GDPR.hideHeader();
					GDPR.accept_close();
					lwaio_consent_acted = GDPR_Cookie.read(ACTED_CONSENT_CK_NAME);
					if (lwaio_consent_acted != "yes") {
						lwaio_consent_preferences = JSON.parse(
							GDPR_Cookie.read(USR_PREFS_CK_NAME)
						);
						lwaio_consent_preferences_val = JSON.stringify(lwaio_consent_preferences);
						GDPR_Cookie.set(
							USR_PREFS_CK_NAME,
							lwaio_consent_preferences_val,
							EXP_CK_NAME
						);

						event = new CustomEvent("LwAioCookieConsentOnAccept", {
							detail: {
								lwaio_consent_preferences: lwaio_consent_preferences,
								lwaio_consent_acted: lwaio_consent_acted,
							},
						});
						window.dispatchEvent(event);
					}
				}
			});

			jQuery(".group-switch-buttons input").each(function () {
				let key = jQuery(this).val();
				let lwaio_consent_preferences_arr = {};
				let lwaio_consent_preferences_val = "";
				if (GDPR_Cookie.read(USR_PREFS_CK_NAME)) {
					lwaio_consent_preferences_arr = JSON.parse(
						GDPR_Cookie.read(USR_PREFS_CK_NAME)
					);
				}
				if (key == "necessary" || jQuery(this).is(":checked")) {
					lwaio_consent_preferences_arr[key] = "yes";
					GDPR.allowed_categories.push(key);
				} else {
					lwaio_consent_preferences_arr[key] = "no";
					let length = GDPR.allowed_categories.length;
					for (let i = 0; i < length; i++) {
						if (GDPR.allowed_categories[i] == key) {
							GDPR.allowed_categories.splice(i, 1);
						}
					}
				}
				lwaio_consent_preferences_val = JSON.stringify(lwaio_consent_preferences_arr);
				GDPR_Cookie.set(
					USR_PREFS_CK_NAME,
					lwaio_consent_preferences_val,
					EXP_CK_NAME
				);
			});
			jQuery(document).on("click", "#lwaio-consent-show-again", function (e) {
				e.preventDefault();
				jQuery(GDPR.settings.notify_div_id).find("p.lwaio").show();
				jQuery(GDPR.settings.notify_div_id)
					.find(".lwaio.group-description-buttons")
					.show();
				GDPR.displayHeader();
				$(this).hide();
			});
			jQuery(document).on(
				"click",
				".group-switch-buttons input",
				function () {
					let key = jQuery(this).val();
					let lwaio_consent_preferences_arr = {};
					let lwaio_consent_preferences_val = "";
					if (GDPR_Cookie.read(USR_PREFS_CK_NAME)) {
						lwaio_consent_preferences_arr = JSON.parse(
							GDPR_Cookie.read(USR_PREFS_CK_NAME)
						);
					}
					if (jQuery(this).is(":checked")) {
						lwaio_consent_preferences_arr[key] = "yes";
						GDPR.allowed_categories.push(key);
					} else {
						lwaio_consent_preferences_arr[key] = "no";
						let length = GDPR.allowed_categories.length;
						for (let i = 0; i < length; i++) {
							if (GDPR.allowed_categories[i] == key) {
								GDPR.allowed_categories.splice(i, 1);
							}
						}
					}
					lwaio_consent_preferences_val = JSON.stringify(lwaio_consent_preferences_arr);
					GDPR_Cookie.set(
						USR_PREFS_CK_NAME,
						lwaio_consent_preferences_val,
						EXP_CK_NAME
					);
				}
			);

			jQuery(document).on(
				"click",
				"#lwaio_consent_tabs_overview",
				function (e) {
					e.preventDefault();
					let elm = jQuery(this);
					jQuery("#lwaio_consent_tabs")
						.find("a")
						.removeClass(
							"tab_selected"
						);
					elm.addClass(
						"tab_selected"
					);
					elm.css("border-bottom-color", GDPR.settings.border_active_color);
					elm.css("background-color", GDPR.settings.background_active_color);
					jQuery("#lwaio_consent_tabs_about").css(
						"border-bottom-color",
						GDPR.settings.border_color
					);
					jQuery("#lwaio_consent_tabs_about").css(
						"background-color",
						GDPR.settings.background_color
					);
					jQuery("#lwaio_consent_about").hide();
					jQuery("#lwaio_consent_overview").show();
				}
			);
			jQuery(document).on(
				"click",
				"#lwaio_consent_tabs_about",
				function (e) {
					e.preventDefault();
					let elm = jQuery(this);
					jQuery("#lwaio_consent_tabs")
						.find("a")
						.removeClass(
							"tab_selected"
						);
					elm.addClass(
						"tab_selected"
					);
					elm.css("border-bottom-color", GDPR.settings.border_active_color);
					elm.css("background-color", GDPR.settings.background_active_color);
					jQuery("#lwaio_consent_tabs_overview").css(
						"border-bottom-color",
						GDPR.settings.border_color
					);
					jQuery("#lwaio_consent_tabs_overview").css(
						"background-color",
						GDPR.settings.background_color
					);
					jQuery("#lwaio_consent_overview").hide();
					jQuery("#lwaio_consent_about").show();
				}
			);
			jQuery(document).on(
				"click",
				"#lwaio_consent_overview_cookie_container_types a",
				function (e) {
					e.preventDefault();
					let elm = jQuery(this);
					let prnt = elm.parent();
					prnt
						.find("a")
						.removeClass(
							"lwaio_consent_overview_cookie_container_type_selected"
						);
					prnt.find("a").css("border-right-color", GDPR.settings.border_color);
					prnt
						.find("a")
						.css("background-color", GDPR.settings.background_color);
					elm.addClass(
						"lwaio_consent_overview_cookie_container_type_selected"
					);
					elm.css("border-right-color", GDPR.settings.border_active_color);
					elm.css("background-color", GDPR.settings.background_active_color);
					let trgt = jQuery(this).attr("data-target");
					let cntr = prnt.siblings(
						"#lwaio_consent_overview_cookie_container_type_details"
					);
					cntr
						.find(".lwaio_consent_cookie_type_details")
						.hide();
					cntr.find("#" + trgt + "").show();
				}
			);
		},

		configButtons: function () {
			this.settings_button.attr(
				"style",
				`color: ${this.settings.button_link_color} !important; background-color: ${this.settings.secondary_color} !important`
			);

			this.main_button.css("color", this.settings.button_link_color);
			this.main_button.css(
				"background-color",
				this.settings.primary_color
			);

			this.accept_all_button.css(
				"color",
				this.settings.button_link_color
			);
			this.accept_all_button.css(
				"background-color",
				this.settings.primary_color
			);

			this.confirm_button.css("color", this.settings.button_link_color);
			this.confirm_button.css(
				"background-color",
				this.settings.primary_color
			);

			/* [wpl_cookie_link] */
			this.main_link.css("color", this.settings.secondary_color);

			this.reject_button.css("display", "inline-block");
			this.reject_button.attr(
				"style",
				`color: ${this.settings.button_link_color} !important; background-color: ${this.settings.secondary_color} !important`
			);

			this.cancel_button.css("color", this.settings.button_link_color);
			this.cancel_button.css("display", "inline-block");
			this.cancel_button.css(
				"background-color",
				this.settings.secondary_color
			);

			this.save_button.css("color", this.settings.button_link_color);
			this.save_button.css(
				"background-color",
				this.settings.primary_color
			);

			this.details_elm
				.find("table.lwaio_consent_cookie_type_table tr")
				.css("border-color", GDPR.settings.border_color);
			this.details_elm
				.find(".lwaio_consent_cookie_type_intro")
				.css("border-color", GDPR.settings.border_color);
			this.details_elm.find("a").each(function () {
				jQuery(this).css("border-color", GDPR.settings.border_color);
				jQuery(this).css("background-color", GDPR.settings.background_color);
			});
			this.details_elm
				.find(
					"a.lwaio_consent_overview_cookie_container_type_selected"
				)
				.css("border-right-color", GDPR.settings.border_active_color);
			this.details_elm
				.find(
					"a.lwaio_consent_overview_cookie_container_type_selected"
				)
				.css("background-color", GDPR.settings.background_active_color);
			this.details_elm
				.find("#lwaio_consent")
				.css("border-color", GDPR.settings.border_color);
			this.details_elm
				.find("#lwaio_consent_tabs")
				.css("border-color", GDPR.settings.border_color);
			this.details_elm
				.find(
					"#lwaio_consent_tabs .tab_selected"
				)
				.css("border-bottom-color", GDPR.settings.border_active_color);
			this.details_elm
				.find(
					"#lwaio_consent_tabs .tab_selected"
				)
				.css("background-color", GDPR.settings.background_active_color);
		},

		configBar: function () {
			this.bar_config = {
				"background-color": this.settings.background,
				color: this.settings.text,
				"border-top": "1px solid " + this.settings.secondary_color,
			};
			this.show_config = {
				width: "auto",
				"background-color": this.settings.background,
				color: this.settings.text,
				position: "fixed",
				opacity: this.settings.opacity,
				bottom: "0",
				"border-top": "1px solid " + this.settings.secondary_color,
			};
			if (this.settings.show_again_position == "right") {
				this.show_config["right"] = this.settings.show_again_margin + "%";
			} else {
				this.show_config["left"] = this.settings.show_again_margin + "%";
			}
			this.bar_config["position"] = "fixed";
			this.bar_config["opacity"] = this.settings.opacity;
			this.bar_elm
				.find(".lwaio_messagebar_content")
				.css("max-width", "800px");
			if (this.settings.banner_position == "bottom") {
				this.bar_config["bottom"] = "0";
			} else {
				this.bar_config["top"] = "0";
			}

			this.bar_elm.css(this.bar_config).hide();
			this.show_again_elm.css(this.show_config).hide();
		},

		toggleBar: function () {
			if (!GDPR_Cookie.exists(ACTED_CONSENT_CK_NAME)) {
				this.displayHeader();
				if (this.settings.auto_hide) {
					setTimeout(function () {
						GDPR.accept_close();
					}, this.settings.auto_hide_delay);
				}
			} else {
				this.hideHeader();
			}
		},

		accept_close: function () {
			GDPR_Cookie.set(
				ACTED_CONSENT_CK_NAME,
				"yes",
				EXP_CK_NAME
			);
			if (this.settings.notify_animate_hide) {
				this.bar_elm.slideUp(
					this.settings.animate_speed_hide,
					GDPR_Blocker.runScripts
				);
			} else {
				this.bar_elm.hide(GDPR_Blocker.runScripts);
			}
			this.show_again_elm.slideDown(this.settings.animate_speed_hide);
			if (this.settings.accept_reload == true) {
				window.location.reload(true);
			}
			return false;
		},

		accept_all_close: function () {
			GDPR_Cookie.set(
				ACTED_CONSENT_CK_NAME,
				"yes",
				EXP_CK_NAME
			);
			if (this.settings.notify_animate_hide) {
				this.bar_elm.slideUp(
					this.settings.animate_speed_hide,
					GDPR_Blocker.runScripts
				);
			} else {
				this.bar_elm.hide(GDPR_Blocker.runScripts);
			}
			this.show_again_elm.slideDown(this.settings.animate_speed_hide);
			if (this.settings.accept_reload == true) {
				window.location.reload(true);
			}
			return false;
		},

		reject_close: function () {
			GDPR_Cookie.set(
				ACTED_CONSENT_CK_NAME,
				"yes",
				EXP_CK_NAME
			);
			GDPR.disableAllCookies();
			if (this.settings.notify_animate_hide) {
				this.bar_elm.slideUp(
					this.settings.animate_speed_hide,
					GDPR_Blocker.runScripts
				);
			} else {
				this.bar_elm.hide(GDPR_Blocker.runScripts);
			}
			this.show_again_elm.slideDown(this.settings.animate_speed_hide);
			if (this.settings.decline_reload == true) {
				window.location.reload(true);
			}
			return false;
		},

		logConsent: function (btn_action) {
			if (this.settings.logging_on) {
				jQuery.ajax({
					url: log_obj.ajax_url,
					type: "POST",
					data: {
						action: "lwaio_log_consent_action",
						lwaio_user_action: btn_action,
						cookie_list: GDPR_Cookie.getallcookies(),
					},
					success: function (response) { },
				});
			}
		},
		disableAllCookies: function () {
			let lwaio_consent_preferences_arr = {};
			let lwaio_consent_preferences_val = "";
			if (GDPR_Cookie.read(USR_PREFS_CK_NAME)) {
				lwaio_consent_preferences_arr = JSON.parse(
					GDPR_Cookie.read(USR_PREFS_CK_NAME)
				);
				jQuery.each(lwaio_consent_preferences_arr, function (key, value) {
					if (key != "necessary") {
						lwaio_consent_preferences_arr[key] = "no";
						$('.group-switch-buttons input[value="' + key + '"]').prop(
							"checked",
							false
						);
						let length = GDPR.allowed_categories.length;
						for (let i = 0; i < length; i++) {
							if (GDPR.allowed_categories[i] == key) {
								GDPR.allowed_categories.splice(i, 1);
							}
						}
					}
				});
				lwaio_consent_preferences_val = JSON.stringify(lwaio_consent_preferences_arr);
				GDPR_Cookie.set(
					USR_PREFS_CK_NAME,
					lwaio_consent_preferences_val,
					EXP_CK_NAME
				);
			}
		},
		enableAllCookies: function () {
			let lwaio_consent_preferences_arr = {};
			let lwaio_consent_preferences_val = "";
			if (GDPR_Cookie.read(USR_PREFS_CK_NAME)) {
				lwaio_consent_preferences_arr = JSON.parse(
					GDPR_Cookie.read(USR_PREFS_CK_NAME)
				);
				jQuery.each(lwaio_consent_preferences_arr, function (key, value) {
					if (key != "necessary") {
						lwaio_consent_preferences_arr[key] = "yes";
						$('.group-switch-buttons input[value="' + key + '"]').prop(
							"checked",
							true
						);
						let length = GDPR.allowed_categories.length;
						for (let i = 0; i < length; i++) {
							if (GDPR.allowed_categories[i] == key) {
								GDPR.allowed_categories.splice(i, 1);
							}
						}
					}
				});
				lwaio_consent_preferences_val = JSON.stringify(lwaio_consent_preferences_arr);
				GDPR_Cookie.set(
					USR_PREFS_CK_NAME,
					lwaio_consent_preferences_val,
					EXP_CK_NAME
				);
			}
		},
		show_details: function () {
			this.details_elm.show();
			this.bar_elm.css("opacity", 1);
			this.details_elm.css("border-top-color", GDPR.settings.border_color);
			this.settings_button.attr("data-lwaio_action", "hide_settings");
		},
		hide_details: function () {
			this.details_elm.hide();
			this.bar_elm.css("opacity", GDPR.settings.opacity);
			this.settings_button.attr("data-lwaio_action", "show_settings");
		},
		displayHeader: function (lwaio_flag, ccpa_flag) {
			this.bar_elm.show();
			if (lwaio_flag) {
				jQuery(GDPR.settings.notify_div_id).find("p.lwaio").hide();
				jQuery(GDPR.settings.notify_div_id)
					.find(".lwaio.group-description-buttons")
					.hide();
			}
			this.show_again_elm.slideUp(this.settings.animate_speed_hide);
		},
		hideHeader: function (geo_flag) {
			this.bar_elm.slideUp(this.settings.animate_speed_hide);
			if (!geo_flag) {
					this.show_again_elm.slideDown(this.settings.animate_speed_hide);
			}
		},
		acceptOnScroll: function () {
			let scrollTop = $(window).scrollTop();
			let docHeight = $(document).height();
			let winHeight = $(window).height();
			let scrollPercent = scrollTop / (docHeight - winHeight);
			let scrollPercentRounded = Math.round(scrollPercent * 100);

			if (
				scrollPercentRounded > GDPR.settings.auto_scroll_offset &&
				!GDPR_Cookie.exists(ACTED_CONSENT_CK_NAME)
			) {
				GDPR.accept_close();
				window.removeEventListener("scroll", GDPR.acceptOnScroll, false);
				if (GDPR.settings.auto_scroll_reload == true) {
					window.location.reload();
				}
			}
		},
	};

	let GDPR_Blocker = {
		blockingStatus: true,
		scriptsLoaded: false,
		set: function (args) {
			if (typeof JSON.parse !== "function") {
				console.log(
					"LWAIOCookieConsent requires JSON.parse but your browser doesn't support it"
				);
				return;
			}
			this.cookies = JSON.parse(args.cookies);
		},
		removeCookieByCategory: function () {
			if (GDPR_Blocker.blockingStatus == true) {
				for (let key in GDPR_Blocker.cookies) {
					let cookie = GDPR_Blocker.cookies[key];
					let current_category = cookie["lwaio_category_slug"];
					if (GDPR.allowed_categories.indexOf(current_category) === -1) {
						let cookies = cookie["data"];
						if (cookies && cookies.length != 0) {
							for (let c_key in cookies) {
								let c_cookie = cookies[c_key];
								GDPR_Cookie.erase(c_cookie["name"]);
							}
						}
					}
				}
			}
		},
		runScripts: function () {
			let srcReplaceableElms = [
				"iframe",
				"IFRAME",
				"EMBED",
				"embed",
				"OBJECT",
				"object",
				"IMG",
				"img",
			];
			let genericFuncs = {
				renderByElement: function (callback) {
					scriptFuncs.renderScripts();
					htmlElmFuncs.renderSrcElement();
					callback();
					GDPR_Blocker.scriptsLoaded = true;
				},
				reviewConsent: function () {
					jQuery(document).on(
						"click",
						".wpl_manage_current_consent",
						function () {
							GDPR.displayHeader();
						}
					);
				},
			};
			let scriptFuncs = {
				scriptsDone: function () {
					let DOMContentLoadedEvent = document.createEvent("Event");
					DOMContentLoadedEvent.initEvent("DOMContentLoaded", true, true);
					window.document.dispatchEvent(DOMContentLoadedEvent);
				},
				seq: function (arr, callback, index) {
					if (typeof index === "undefined") {
						index = 0;
					}

					arr[index](function () {
						index++;
						if (index === arr.length) {
							callback();
						} else {
							scriptFuncs.seq(arr, callback, index);
						}
					});
				},

				insertScript: function ($script, callback) {
					let allowedAttributes = [
						"data-wpl-class",
						"data-wpl-label",
						"data-wpl-placeholder",
						"data-wpl-script-type",
						"data-wpl-src",
					];
					let scriptType = $script.getAttribute("data-wpl-script-type");
					let elementPosition = $script.getAttribute(
						"data-wpl-element-position"
					);
					let isBlock = $script.getAttribute("data-wpl-block");
					let s = document.createElement("script");
					s.type = "text/plain";
					if ($script.async) {
						s.async = $script.async;
					}
					if ($script.defer) {
						s.defer = $script.defer;
					}
					if ($script.src) {
						s.onload = callback;
						s.onerror = callback;
						s.src = $script.src;
					} else {
						s.textContent = $script.innerText;
					}
					let attrs = jQuery($script).prop("attributes");
					let length = attrs.length;
					for (let ii = 0; ii < length; ++ii) {
						if (attrs[ii].nodeName !== "id") {
							if (allowedAttributes.indexOf(attrs[ii].nodeName) !== -1) {
								s.setAttribute(attrs[ii].nodeName, attrs[ii].value);
							}
						}
					}
					if (GDPR_Blocker.blockingStatus === true) {
						if (
							(GDPR_Cookie.read(ACTED_CONSENT_CK_NAME) == "yes" &&
								GDPR.allowed_categories.indexOf(scriptType) !== -1) ||
							(GDPR_Cookie.read(ACTED_CONSENT_CK_NAME) == null &&
								isBlock === "false")
						) {
							s.setAttribute("data-wpl-consent", "accepted");
							s.type = "text/javascript";
						}
					} else {
						s.type = "text/javascript";
					}
					if ($script.type != s.type) {
						if (elementPosition === "head") {
							document.head.appendChild(s);
							if (!$script.src) {
								callback();
							}
							$script.parentNode.removeChild($script);
						} else {
							document.body.appendChild(s);
							if (!$script.src) {
								callback();
							}
							$script.parentNode.removeChild($script);
						}
					}
				},
				renderScripts: function () {
					let $scripts = document.querySelectorAll(
						'script[data-wpl-class="wpl-blocker-script"]'
					);
					if ($scripts.length > 0) {
						let runList = [];
						let typeAttr;
						Array.prototype.forEach.call($scripts, function ($script) {
							typeAttr = $script.getAttribute("type");
							let elmType = $script.tagName;
							runList.push(function (callback) {
								scriptFuncs.insertScript($script, callback);
							});
						});
						scriptFuncs.seq(runList, scriptFuncs.scriptsDone);
					}
				},
			};
			let htmlElmFuncs = {
				renderSrcElement: function () {
					let blockingElms = document.querySelectorAll(
						'[data-wpl-class="wpl-blocker-script"]'
					);
					let length = blockingElms.length;
					for (let i = 0; i < length; i++) {
						let currentElm = blockingElms[i];
						let elmType = currentElm.tagName;
						if (srcReplaceableElms.indexOf(elmType) !== -1) {
							let elmCategory = currentElm.getAttribute("data-wpl-script-type");
							let isBlock = currentElm.getAttribute("data-wpl-block");
							if (GDPR_Blocker.blockingStatus === true) {
								if (
									(GDPR_Cookie.read(ACTED_CONSENT_CK_NAME) == "yes" &&
										GDPR.allowed_categories.indexOf(elmCategory) !== -1) ||
									(GDPR_Cookie.read(ACTED_CONSENT_CK_NAME) != null &&
										isBlock === "false")
								) {
									this.replaceSrc(currentElm);
								} else {
									this.addPlaceholder(currentElm);
								}
							} else {
								this.replaceSrc(currentElm);
							}
						}
					}
				},
				addPlaceholder: function (htmlElm) {
					if (jQuery(htmlElm).prev(".wpl-iframe-placeholder").length === 0) {
						let htmlElemType = htmlElm.getAttribute("data-wpl-placeholder");
						let htmlElemWidth = htmlElm.getAttribute("width");
						let htmlElemHeight = htmlElm.getAttribute("height");
						if (htmlElemWidth == null) {
							htmlElemWidth = htmlElm.offsetWidth;
						}
						if (htmlElemHeight == null) {
							htmlElemHeight = htmlElm.offsetHeight;
						}
						let pixelPattern = /px/;
						htmlElemWidth = pixelPattern.test(htmlElemWidth) ?
							htmlElemWidth :
							htmlElemWidth + "px";
						htmlElemHeight = pixelPattern.test(htmlElemHeight) ?
							htmlElemHeight :
							htmlElemHeight + "px";
						let addPlaceholder =
							'<div style="width:' +
							htmlElemWidth +
							"; height:" +
							htmlElemHeight +
							';" class="wpl-iframe-placeholder"><div class="wpl-inner-text">' +
							htmlElemType +
							"</div></div>";
						if (htmlElm.tagName !== "IMG") {
							jQuery(addPlaceholder).insertBefore(htmlElm);
						}
						htmlElm.removeAttribute("src");
						htmlElm.style.display = "none";
					}
				},
				replaceSrc: function (htmlElm) {
					if (!htmlElm.hasAttribute("src")) {
						let htmlElemSrc = htmlElm.getAttribute("data-wpl-src");
						htmlElm.setAttribute("src", htmlElemSrc);
						if (jQuery(htmlElm).prev(".wpl-iframe-placeholder").length > 0) {
							jQuery(htmlElm).prev(".wpl-iframe-placeholder").remove();
						}
						htmlElm.style.display = "block";
					}
				},
			};
			genericFuncs.reviewConsent();
			genericFuncs.renderByElement(GDPR_Blocker.removeCookieByCategory);
		},
	};

	$(document).ready(function () {
		if (typeof lwaiobar_settings != "undefined") {
			GDPR.set({
				settings: lwaiobar_settings,
			});
		}

		if (typeof lwaios_list != "undefined") {
			GDPR_Blocker.set({
				cookies: lwaios_list,
			});
			GDPR_Blocker.runScripts();
		}
	});

	$(document).ready(function () {
		$(".lwaio-column").click(function () {
			$(".lwaio-column", this);
			if (!$(this).children(".lwaio-columns").hasClass("active-group")) {
				$(".lwaio-columns").removeClass("active-group");
				$(this).children(".lwaio-columns").addClass("active-group");
			}
			if ($(this).siblings(".description-container").hasClass("hide")) {
				$(".description-container").addClass("hide");
				$(this).siblings(".description-container").removeClass("hide");
			}
		});
	});
})(jQuery);