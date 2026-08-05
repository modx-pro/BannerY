(function () {
  'use strict';

  if (window.bannerproImpressionInitDone) {
    return;
  }
  window.bannerproImpressionInitDone = true;

  var config = window.bannerproImpressionConfig || {};
  var segment = (config.segment || 'bannerimpression').replace(/^\/+|\/+$/g, '');

  function trackUrl(adpositionId) {
    return '/' + segment + '/' + adpositionId;
  }

  function impressionDetail(el, adpositionId) {
    var detail = { adposition: adpositionId };
    if (!el) {
      return detail;
    }
    var adId = parseInt(el.getAttribute('data-bannerpro-ad-id') || '0', 10);
    if (adId > 0) {
      detail.ad_id = adId;
    }
    var adName = el.getAttribute('data-bannerpro-ad-name');
    if (adName) {
      detail.ad_name = adName;
    }
    var positionName = el.getAttribute('data-bannerpro-position-name');
    if (positionName) {
      detail.position_name = positionName;
    }
    return detail;
  }

  function clickDetailFromNode(node) {
    if (!node) {
      return {};
    }
    var wrap = node.closest('[data-bannerpro-impression]');
    var detail = {
      adposition: parseInt(
        node.getAttribute('data-bannerpro-adposition') ||
          (wrap && wrap.getAttribute('data-bannerpro-impression')) ||
          '0',
        10
      ) || 0,
      ad_id:
        parseInt(
          node.getAttribute('data-bannerpro-ad-id') ||
            (wrap && wrap.getAttribute('data-bannerpro-ad-id')) ||
            '0',
          10
        ) || 0,
      ad_name:
        node.getAttribute('data-bannerpro-ad-name') ||
        (wrap && wrap.getAttribute('data-bannerpro-ad-name')) ||
        '',
      position_name:
        node.getAttribute('data-bannerpro-position-name') ||
        (wrap && wrap.getAttribute('data-bannerpro-position-name')) ||
        '',
    };
    return detail;
  }

  function notifyImpression(el, adpositionId) {
    var detail = impressionDetail(el, adpositionId);
    document.dispatchEvent(new CustomEvent('bannerpro:impression', { detail: detail }));
    if (!window.bannerproAnalytics) {
      window.bannerproImpressionQueue = window.bannerproImpressionQueue || [];
      window.bannerproImpressionQueue.push(detail);
    }
  }

  function sendImpression(el, adpositionId) {
    notifyImpression(el, adpositionId);
    var url = trackUrl(adpositionId);
    if (navigator.sendBeacon) {
      navigator.sendBeacon(url);
      return;
    }
    try {
      fetch(url, { method: 'GET', keepalive: true, credentials: 'same-origin' });
    } catch (e) {
      /* graceful degrade */
    }
  }

  function initClickTracking() {
    var clickSegment = (config.clickSegment || 'bannerclick').replace(/^\/+|\/+$/g, '');
    var pattern = new RegExp('/' + clickSegment.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '/([0-9]+)');

    document.addEventListener(
      'click',
      function (e) {
        var link = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!link) {
          return;
        }
        var href = link.getAttribute('href') || '';
        if (!pattern.test(href)) {
          return;
        }
        var detail = clickDetailFromNode(link);
        if (!detail.adposition) {
          var match = href.match(pattern);
          if (match) {
            detail.adposition = parseInt(match[1], 10) || 0;
          }
        }
        document.dispatchEvent(new CustomEvent('bannerpro:click', { detail: detail }));
      },
      true
    );
  }

  function init() {
    initClickTracking();

    var nodes = document.querySelectorAll('[data-bannerpro-impression]');
    if (!nodes.length) {
      return;
    }

    if (!('IntersectionObserver' in window)) {
      nodes.forEach(function (el) {
        var id = parseInt(el.getAttribute('data-bannerpro-impression'), 10);
        if (id > 0) {
          sendImpression(el, id);
        }
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting || entry.intersectionRatio < 0.5) {
            return;
          }
          var el = entry.target;
          var id = parseInt(el.getAttribute('data-bannerpro-impression'), 10);
          if (id > 0) {
            sendImpression(el, id);
          }
          observer.unobserve(el);
        });
      },
      { rootMargin: '80px 0px', threshold: [0, 0.25, 0.5] }
    );

    nodes.forEach(function (el) {
      observer.observe(el);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
