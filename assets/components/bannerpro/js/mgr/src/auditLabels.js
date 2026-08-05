import { lex } from './request.js'

/** @type {readonly string[]} */
export const AUDIT_ACTIONS = [
  'create',
  'update',
  'delete',
  'enable',
  'disable',
  'bulk_enable',
  'bulk_disable',
  'bulk_delete',
  'bulk_assign_positions',
  'create_from_template',
]

const AUDIT_ENTITY_LABEL_KEYS = {
  ad: 'bannerpro.ads',
  position: 'bannerpro.positions',
}

export function lexKey(key, fallback = '') {
  const value = lex(key)
  return value !== key ? value : (fallback || key)
}

export function lexAuditAction(action) {
  return action ? lexKey(`bannerpro.audit.action.${action}`) : ''
}

export function lexAuditEntity(entity) {
  const labelKey = AUDIT_ENTITY_LABEL_KEYS[entity]
  return labelKey ? lex(labelKey) : (entity || '')
}

/**
 * @param {string} allLabelKey
 * @param {readonly string[]} values
 * @param {(value: string) => string} labelForValue
 */
export function buildFilterOptions(allLabelKey, values, labelForValue) {
  return [
    { label: lex(allLabelKey), value: '' },
    ...values.map((value) => ({ label: labelForValue(value), value })),
  ]
}
