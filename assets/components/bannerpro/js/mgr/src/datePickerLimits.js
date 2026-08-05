export function getMinSelectableDateTime() {
  return new Date()
}

function toValidDate(value) {
  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? null : value
  }
  if (!value) return null
  const date = new Date(String(value).trim().replace(' ', 'T'))
  return Number.isNaN(date.getTime()) ? null : date
}

/** Минимум для «Окончание»: не раньше «Начало» и не раньше текущего момента. */
export function resolveMinEndDateTime(startValue) {
  const now = getMinSelectableDateTime()
  const start = toValidDate(startValue)
  if (start && start > now) return start
  return now
}
