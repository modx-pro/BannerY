(function () {
  'use strict';

  var config = window.bannerproAnalyticsConfig || {};

  function detailFromNode(node) {
    if (!node) {
      return {};
    }
    var wrap = node.closest('[data-bannerpro-impression]');
    return {
      adposition: parseInt(node.getAttribute('data-bannerpro-adposition') || (wrap && wrap.getAttribute('data-bannerpro-impression')) || '0', 10) || 0,
      ad_id: parseInt(node.getAttribute('data-bannerpro-ad-id') || (wrap && wrap.getAttribute('data-bannerpro-ad-id')) || '0', 10) || 0,
      ad_name: node.getAttribute('data-bannerpro-ad-name') || (wrap && wrap.getAttribute('data-bannerpro-ad-name')) || '',
      position_name: node.getAttribute('data-bannerpro-position-name') || (wrap && wrap.getAttribute('data-bannerpro-position-name')) || '',
    };
  }

  function pushGa4(eventName, detail) {
    if (!config.ga4 || !window.dataLayer) {
      return;
    }
    window.dataLayer.push({
      event: eventName,
      bannerpro_ad_id: detail.ad_id,
      bannerpro_ad_name: detail.ad_name,
      bannerpro_adposition: detail.adposition,
      bannerpro_position_name: detail.position_name,
    });
  }

  function pushMatomo(action, detail) {
    if (!config.matomo || !window._paq) {
      return;
    }
    window._paq.push([
      'trackEvent',
      'BannerPro',
      action,
      detail.ad_name || String(detail.ad_id || detail.adposition),
      detail.adposition || undefined,
    ]);
  }

  function pushYm(goal, detail) {
    if (!config.ymCounter || typeof window.ym !== 'function') {
      return;
    }
    window.ym(config.ymCounter, 'reachGoal', goal, {
      ad_id: detail.ad_id,
      ad_name: detail.ad_name,
      adposition: detail.adposition,
      position_name: detail.position_name,
    });
  }

  function trackImpression(detail) {
    pushGa4(config.ga4ImpressionEvent || 'bannerpro_impression', detail);
    pushMatomo('impression', detail);
    pushYm(config.ymImpressionGoal || 'bannerpro_impression', detail);
  }

  function trackClick(detail) {
    pushGa4(config.ga4ClickEvent || 'bannerpro_click', detail);
    pushMatomo('click', detail);
    pushYm(config.ymClickGoal || 'bannerpro_click', detail);
  }

  window.bannerproAnalytics = {
    trackImpression: trackImpression,
    trackClick: trackClick,
  };

  document.addEventListener('bannerpro:impression', function (e) {
    trackImpression(e.detail || {});
  });

  document.addEventListener('bannerpro:click', function (e) {
    trackClick(e.detail || detailFromNode(e.target));
  });

  var queued = window.bannerproImpressionQueue;
  if (queued && queued.length) {
    queued.forEach(function (detail) {
      trackImpression(detail);
    });
    window.bannerproImpressionQueue = [];
  }
})();
