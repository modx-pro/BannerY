/**
 * QA-стабы GA4 (dataLayer), Matomo (_paq), Яндекс Метрика (ym) для демо-страницы BannerPro.
 * Подключать до analytics.js. События дублируются в window.bannerproAnalyticsMockLog и CustomEvent bannerpro:analytics-mock.
 */
(function () {
  'use strict';

  var DEMO_YM_COUNTER = 99999999;

  function record(provider, kind, payload) {
    var entry = {
      ts: Date.now(),
      provider: provider,
      kind: kind,
      payload: payload,
    };
    window.bannerproAnalyticsMockLog = window.bannerproAnalyticsMockLog || [];
    window.bannerproAnalyticsMockLog.push(entry);
    document.dispatchEvent(
      new CustomEvent('bannerpro:analytics-mock', {
        detail: entry,
      })
    );
  }

  window.dataLayer = window.dataLayer || [];
  var dataLayerPush = window.dataLayer.push.bind(window.dataLayer);
  window.dataLayer.push = function () {
    var item = arguments[0];
    if (item && typeof item === 'object' && item.event) {
      record('ga4', item.event.indexOf('impression') >= 0 ? 'impression' : 'click', item);
    }
    return dataLayerPush.apply(window.dataLayer, arguments);
  };

  window._paq = window._paq || [];
  var paqPush = window._paq.push.bind(window._paq);
  window._paq.push = function () {
    var args = Array.prototype.slice.call(arguments);
    var row = args[0];
    if (Array.isArray(row) && row[0] === 'trackEvent' && row[1] === 'BannerPro') {
      record('matomo', row[2] === 'impression' ? 'impression' : 'click', {
        category: row[1],
        action: row[2],
        name: row[3],
        value: row[4],
      });
    }
    return paqPush.apply(window._paq, args);
  };

  function ymMock(counterId, method, goal, params) {
    if (method === 'reachGoal') {
      record('ym', String(goal).indexOf('impression') >= 0 ? 'impression' : 'click', {
        counterId: counterId,
        goal: goal,
        params: params || {},
      });
    }
  }
  ymMock.a = ymMock.a || [];
  window.ym = window.ym || ymMock;

  window.bannerproAnalyticsMock = {
    ymCounter: DEMO_YM_COUNTER,
    providers: ['ga4', 'matomo', 'ym'],
  };
})();
