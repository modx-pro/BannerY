import { bannerproRequest, getConfig } from '../request.js'

async function bannerproDownload(action, params = {}, filename = 'export.csv') {
  const { connectorUrl } = getConfig()
  const body = new FormData()
  body.append('action', action)
  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null || value === '') return
    body.append(key, value)
  })
  const response = await fetch(connectorUrl, { method: 'POST', body, credentials: 'same-origin' })
  if (!response.ok) {
    throw new Error('Export failed')
  }
  const blob = await response.blob()
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  link.remove()
  URL.revokeObjectURL(url)
}

export function useBannerProApi() {
  return {
    adsGetList: (params) => bannerproRequest('ads_getlist', params),
    adsGet: (id) => bannerproRequest('ads_get', { id }),
    adsCreate: (data) => bannerproRequest('ads_create', data),
    adsUpdate: (data) => bannerproRequest('ads_update', data),
    adsDuplicate: (id) => bannerproRequest('ads_duplicate', { id }),
    adsRemove: (id) => bannerproRequest('ads_remove', { id }),
    adsEnable: (id) => bannerproRequest('ads_enable', { id }),
    adsDisable: (id) => bannerproRequest('ads_disable', { id }),
    adsGetClicks: (period, position = 0) => bannerproRequest('ads_getclicks', { period, position: position || '' }),
    statsSummary: (params) => bannerproRequest('stats_summary', params),
    statsByDay: (params) => bannerproRequest('stats_by_day', params),
    statsCompare: (params) => bannerproRequest('stats_compare', params),
    statsTopAds: (params) => bannerproRequest('stats_top_ads', params),
    statsPurge: () => bannerproRequest('stats_purge'),
    statsExport: (params) => bannerproDownload(
      'stats_export',
      params,
      `bannerpro-${params.type || 'report'}-${new Date().toISOString().slice(0, 10)}.csv`,
    ),
    positionsGetList: (params) => bannerproRequest('positions_getlist', params),
    positionsCreate: (data) => bannerproRequest('positions_create', data),
    positionsUpdate: (data) => bannerproRequest('positions_update', data),
    positionsDuplicate: (id) => bannerproRequest('positions_duplicate', { id }),
    positionsRemove: (id) => bannerproRequest('positions_remove', { id }),
    adpositionsGetList: (params) => bannerproRequest('adpositions_getlist', params),
    adpositionsAdd: (data) => bannerproRequest('adpositions_add', data),
    adpositionsRemove: (id) => bannerproRequest('adpositions_remove', { id }),
    adpositionsSort: (data) => bannerproRequest('adpositions_sort', data),
    adpositionsUpdateWeight: (data) => bannerproRequest('adpositions_update_weight', data),
    clicksGetReferrers: (params) => bannerproRequest('clicks_getreferrers', {
      ...params,
      position: params.position || '',
    }),
    resourceGetList: (params) => bannerproRequest('resource_getlist', params),
    tagsSuggest: (params) => bannerproRequest('tags_suggest', params),
    adsBulkEnable: (ids) => bannerproRequest('ads_bulk_enable', { ids }),
    adsBulkDisable: (ids) => bannerproRequest('ads_bulk_disable', { ids }),
    adsBulkRemove: (ids) => bannerproRequest('ads_bulk_remove', { ids }),
    adsBulkAssignPositions: (params) => bannerproRequest('ads_bulk_assign_positions', params),
    adTemplatesGetList: () => bannerproRequest('ad_templates_getlist'),
    adsCreateFromTemplate: (params) => bannerproRequest('ads_create_from_template', params),
    auditGetList: (params) => bannerproRequest('audit_getlist', params),
    settingsUtmGet: () => bannerproRequest('settings_utm_get'),
    settingsUtmUpdate: (data) => bannerproRequest('settings_utm_update', data),
    settingsIntegrationsGet: () => bannerproRequest('settings_integrations_get'),
    settingsIntegrationsUpdate: (data) => bannerproRequest('settings_integrations_update', data),
  }
}
