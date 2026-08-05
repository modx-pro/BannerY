import { computed, ref } from 'vue'
import { lex } from '../request.js'

export const STATS_PERIOD_ALL = 'all'
export const STATS_PERIOD_TODAY = 'today'
export const STATS_PERIOD_LAST_7_DAYS = 'last_7_days'
export const STATS_PERIOD_THIS_WEEK = 'this_week'
export const STATS_PERIOD_LAST_WEEK = 'last_week'
export const STATS_PERIOD_THIS_MONTH = 'this_month'
export const STATS_PERIOD_LAST_MONTH = 'last_month'
export const STATS_PERIOD_THIS_YEAR = 'this_year'

export function normalizeStatsPeriod(period) {
  if (period == null || period === '') {
    return STATS_PERIOD_ALL
  }
  return period
}

export function usePeriodFilter() {
  const period = ref(STATS_PERIOD_ALL)

  const periodOptions = computed(() => [
    { label: lex('bannerpro.stats.overall'), value: STATS_PERIOD_ALL },
    { label: lex('bannerpro.stats.today'), value: STATS_PERIOD_TODAY },
    { label: lex('bannerpro.stats.last_7_days'), value: STATS_PERIOD_LAST_7_DAYS },
    { label: lex('bannerpro.stats.this_week'), value: STATS_PERIOD_THIS_WEEK },
    { label: lex('bannerpro.stats.last_week'), value: STATS_PERIOD_LAST_WEEK },
    { label: lex('bannerpro.stats.thismonth'), value: STATS_PERIOD_THIS_MONTH },
    { label: lex('bannerpro.stats.lastmonth'), value: STATS_PERIOD_LAST_MONTH },
    { label: lex('bannerpro.stats.thisyear'), value: STATS_PERIOD_THIS_YEAR },
  ])

  return { period, periodOptions }
}
