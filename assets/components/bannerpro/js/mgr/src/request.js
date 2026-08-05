export function getConfig() {
  return typeof window !== 'undefined' && window.bannerproConfig ? window.bannerproConfig : { connectorUrl: '', lexicon: {} }
}

export function lex(key) {
  const cfg = getConfig()
  return cfg.lexicon?.[key] ?? key
}

export async function bannerproRequest(action, params = {}) {
  const { connectorUrl } = getConfig()
  const body = new FormData()
  body.append('action', action)
  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null) return
    if (Array.isArray(value)) {
      value.forEach((item) => body.append(`${key}[]`, item))
      return
    }
    body.append(key, value)
  })

  const response = await fetch(connectorUrl, {
    method: 'POST',
    body,
    credentials: 'same-origin',
  })
  if (response.status === 403) {
    throw new Error(lex('bannerpro.error.permission_denied'))
  }
  const json = await response.json()
  if (!json.success) {
    throw new Error(json.message || 'Request failed')
  }
  return json
}
