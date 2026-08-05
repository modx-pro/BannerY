/** Read theme token from DOM (PrimeVue preset CSS vars). */
function cssVar(name, fallback) {
  if (typeof document === 'undefined') {
    return fallback
  }
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim()
  return value || fallback
}

export function chartPrimaryColor(alpha = 1) {
  const base = cssVar('--p-primary-500', '#10b981')
  if (alpha >= 1) {
    return base
  }
  return base.startsWith('#')
    ? hexToRgba(base, alpha)
    : `rgba(16, 185, 129, ${alpha})`
}

export function chartSecondaryColor(alpha = 1) {
  const base = cssVar('--p-teal-500', '#14b8a6')
  if (alpha >= 1) {
    return base
  }
  return base.startsWith('#')
    ? hexToRgba(base, alpha)
    : `rgba(20, 184, 166, ${alpha})`
}

export function chartTertiaryColor(alpha = 1) {
  const base = cssVar('--p-amber-500', '#f59e0b')
  if (alpha >= 1) {
    return base
  }
  return base.startsWith('#')
    ? hexToRgba(base, alpha)
    : `rgba(245, 158, 11, ${alpha})`
}

export function chartDeltaUpColor() {
  return cssVar('--bp-text-delta-up', '#16a34a')
}

export function chartDeltaDownColor() {
  return cssVar('--bp-text-delta-down', '#dc2626')
}

function hexToRgba(hex, alpha) {
  const raw = hex.replace('#', '')
  const full = raw.length === 3
    ? raw.split('').map((c) => c + c).join('')
    : raw
  const num = parseInt(full, 16)
  const r = (num >> 16) & 255
  const g = (num >> 8) & 255
  const b = num & 255
  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}
