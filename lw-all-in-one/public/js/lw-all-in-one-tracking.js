(function () {
  'use strict';
  var loaded = {};
  window.dataLayer = window.dataLayer || [];
  window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };

  function preferences() {
    var match = document.cookie.match(/(?:^|;\s*)lwaio_consent_preferences=([^;]*)/);
    if (!match) { return {}; }
    try {
      var value = JSON.parse(match[1]);
      return value && typeof value === 'object' && !Array.isArray(value) ? value : {};
    } catch (error) {
      return {};
    }
  }

  window.lwAioHasConsent = function (purpose) {
    return preferences()[purpose] === 'yes';
  };

  function loadScript(id, url) {
    if (loaded[id] || document.getElementById(id)) { return; }
    loaded[id] = true;
    var script = document.createElement('script');
    script.id = id;
    script.src = url;
    script.async = true;
    document.head.appendChild(script);
  }

  window.gtag('consent', 'default', {
    analytics_storage: 'granted', ad_storage: 'granted',
    ad_user_data: 'granted', ad_personalization: 'granted'
  });

  function update() {
    var config = window.lwAioTracking || {};
    var analytics = window.lwAioHasConsent('analytics');
    var marketing = window.lwAioHasConsent('marketing');
    window.gtag('consent', 'update', {
      analytics_storage: analytics ? 'granted' : 'denied',
      ad_storage: marketing ? 'granted' : 'denied',
      ad_user_data: marketing ? 'granted' : 'denied',
      ad_personalization: marketing ? 'granted' : 'denied'
    });
    if (config.analytics && /^(UA-\d+-\d+|G-[A-Z0-9]+|GTM-[A-Z0-9]+|AW-[A-Z0-9]+|DC-[A-Z0-9]+)$/i.test(config.trackingId || '')) {
      // A GTM container can contain arbitrary marketing tags. Require both
      // purposes unless the site's container has been independently restricted.
      var isContainer = config.trackingId.indexOf('GTM-') === 0;
      var isAds = /^(AW|DC)-/.test(config.trackingId);
      var allowed = isContainer ? analytics && marketing : (isAds ? marketing : analytics);
      if (loaded.google && !allowed) { window.location.reload(); return; }
      if (allowed && !loaded.google) {
        if (isContainer) {
          window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
        } else {
          window.gtag('js', new Date());
          window.gtag('config', config.trackingId);
        }
        loadScript('google', 'https://www.googletagmanager.com/' + (isContainer ? 'gtm.js?id=' : 'gtag/js?id=') + encodeURIComponent(config.trackingId));
      }
    }
    // The external widget includes optional analytics/location functionality.
    // Require all optional purposes until the provider offers isolated modes.
    if (config.chat && analytics && marketing && window.lwAioHasConsent('preferences')) {
      window.WidgetId = 'USC_WIDGET';
      loadScript('ultimate_support_chat', 'https://www.localweb.it/chat/widget/ultimate_chat_widget.js');
    } else if (loaded.ultimate_support_chat) {
      window.location.reload();
    }
  }

  ['LwAioCookieConsentOnLoad', 'LwAioCookieConsentOnAccept', 'LwAioCookieConsentOnAcceptAll'].forEach(function (event) {
    window.addEventListener(event, update);
  });
  window.addEventListener('LwAioCookieConsentOnReject', function () {
    window.gtag('consent', 'update', {
      analytics_storage: 'denied', ad_storage: 'denied',
      ad_user_data: 'denied', ad_personalization: 'denied'
    });
    // Already executed third-party code cannot be unloaded safely. Reload into
    // a denied page after the banner has persisted the visitor's refusal.
    if (loaded.google || loaded.ultimate_support_chat) { window.location.reload(); }
  });
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', update, { once: true });
  } else {
    update();
  }
}());
