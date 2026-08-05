import { defineComponent, computed, onMounted, ref, watch } from 'vue'
import Select from 'primevue/select'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import Button from 'primevue/button'
import ToggleSwitch from 'primevue/toggleswitch'
import StatCard from '../ui/StatCard.js'
import FilterToolbar from '../ui/FilterToolbar.js'
import PageSection from '../ui/PageSection.js'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import {
  normalizeStatsPeriod,
  STATS_PERIOD_THIS_WEEK,
  usePeriodFilter,
} from '../composables/usePeriodFilter.js'
import { lex } from '../request.js'
import ClicksChart from './ClicksChart.js'
import CompareLineChart from './CompareLineChart.js'
import FunnelChart from './FunnelChart.js'
import ReferrersTable from './ReferrersTable.js'
import StatsLineChart from './StatsLineChart.js'
import TopAdsTable from './TopAdsTable.js'

function formatDelta(value, suffix = '%') {
  if (value == null || Number.isNaN(Number(value))) {
    return '—'
  }
  const num = Number(value)
  const sign = num > 0 ? '+' : ''
  return `${sign}${num}${suffix}`
}

function deltaClass(value) {
  if (value == null || Number(value) === 0) {
    return 'bannerpro-stat-delta bannerpro-stat-delta--neutral'
  }
  return Number(value) > 0
    ? 'bannerpro-stat-delta bannerpro-stat-delta--up'
    : 'bannerpro-stat-delta bannerpro-stat-delta--down'
}

export default defineComponent({
  name: 'StatsTab',
  components: {
    Select,
    Tabs,
    TabList,
    Tab,
    TabPanels,
    TabPanel,
    Button,
    ToggleSwitch,
    FilterToolbar,
    StatCard,
    PageSection,
    ClicksChart,
    CompareLineChart,
    FunnelChart,
    ReferrersTable,
    StatsLineChart,
    TopAdsTable,
  },
  setup() {
    const api = useBannerProApi()
    const { notifySuccess, notifyError } = useBannerProNotify()
    const { period, periodOptions } = usePeriodFilter()
    const position = ref(0)
    const positions = ref([])
    const summaryLoading = ref(false)
    const summary = ref({ clicks: 0, impressions: 0, conversions: 0, ctr: null, cvr: null })
    const delta = ref({
      clicks_pct: null,
      impressions_pct: null,
      ctr_pp: null,
      conversions_pct: null,
      cvr_pp: null,
    })
    const compareEnabled = ref(false)
    const lineLabels = ref([])
    const lineClicks = ref([])
    const lineImpressions = ref([])
    const lineConversions = ref([])
    const compareClicks = ref([])
    const compareImpressions = ref([])
    const compareConversions = ref([])
    const chartLabels = ref([])
    const chartValues = ref([])

    const positionOptions = computed(() => [
      { label: lex('bannerpro.stats.all_positions'), value: 0 },
      ...positions.value.map((p) => ({ label: p.name, value: Number(p.id) })),
    ])

    const statsPeriod = computed(() => normalizeStatsPeriod(period.value))
    const showCompareToggle = computed(() => statsPeriod.value === STATS_PERIOD_THIS_WEEK || compareEnabled.value)

    async function loadPositions() {
      try {
        const res = await api.positionsGetList({ start: 0, limit: 500 })
        positions.value = res.data || []
      } catch (e) {
        notifyError(e.message)
      }
    }

    async function loadCompare() {
      if (!compareEnabled.value) {
        delta.value = {
          clicks_pct: null,
          impressions_pct: null,
          ctr_pp: null,
          conversions_pct: null,
          cvr_pp: null,
        }
        compareClicks.value = []
        compareImpressions.value = []
        compareConversions.value = []
        return
      }

      try {
        const res = await api.statsCompare({
          base: STATS_PERIOD_THIS_WEEK,
          compare: 'last_week',
          position: position.value || '',
        })
        const data = res.data || {}
        delta.value = data.delta ?? {
          clicks_pct: null,
          impressions_pct: null,
          ctr_pp: null,
          conversions_pct: null,
          cvr_pp: null,
        }
        const baseDays = Array.isArray(data.base?.by_day) ? data.base.by_day : []
        const compareDays = Array.isArray(data.compare?.by_day) ? data.compare.by_day : []
        lineLabels.value = baseDays.map((row) => String(row.label || ''))
        lineClicks.value = baseDays.map((row) => Number(row.clicks ?? 0))
        lineImpressions.value = baseDays.map((row) => Number(row.impressions ?? 0))
        lineConversions.value = baseDays.map((row) => Number(row.conversions ?? 0))
        compareClicks.value = compareDays.map((row) => Number(row.clicks ?? 0))
        compareImpressions.value = compareDays.map((row) => Number(row.impressions ?? 0))
        compareConversions.value = compareDays.map((row) => Number(row.conversions ?? 0))
        if (data.base?.summary) {
          summary.value = data.base.summary
        }
      } catch (e) {
        notifyError(e.message)
      }
    }

    async function loadOverview() {
      summaryLoading.value = true
      try {
        const params = { period: statsPeriod.value, position: position.value || '' }
        const requests = [
          api.statsSummary(params),
          compareEnabled.value ? Promise.resolve({ data: null }) : api.statsByDay(params),
          api.adsGetClicks(statsPeriod.value, position.value),
        ]
        const [summaryRes, dayRes, clicksRes] = await Promise.all(requests)

        if (!compareEnabled.value) {
          summary.value = summaryRes.data ?? {
            clicks: 0, impressions: 0, conversions: 0, ctr: null, cvr: null,
          }
          const days = Array.isArray(dayRes?.data) ? dayRes.data : []
          lineLabels.value = days.map((row) => String(row.date || row.day || ''))
          lineClicks.value = days.map((row) => Number(row.clicks ?? row.total ?? 0))
          lineImpressions.value = days.map((row) => Number(row.impressions ?? 0))
          lineConversions.value = days.map((row) => Number(row.conversions ?? 0))
        }

        const clicksData = (clicksRes.data || []).filter((row) => Number(row.clicks || 0) > 0)
        chartLabels.value = clicksData.map((row) => row.name)
        chartValues.value = clicksData.map((row) => Number(row.clicks || 0))

        if (compareEnabled.value) {
          await loadCompare()
        }
      } catch (e) {
        notifyError(e.message)
      } finally {
        summaryLoading.value = false
      }
    }

    async function exportCsv(type) {
      try {
        await api.statsExport({
          period: normalizeStatsPeriod(period.value),
          position: position.value || '',
          type: type || 'report',
        })
        notifySuccess(lex('bannerpro.stats.success.export'))
      } catch (e) {
        notifyError(e.message)
      }
    }

    watch(compareEnabled, async (enabled) => {
      if (enabled && statsPeriod.value !== STATS_PERIOD_THIS_WEEK) {
        period.value = STATS_PERIOD_THIS_WEEK
        return
      }
      await loadOverview()
    })

    watch([period, position], () => {
      if (compareEnabled.value && statsPeriod.value !== STATS_PERIOD_THIS_WEEK) {
        compareEnabled.value = false
        return
      }
      loadOverview()
    }, { flush: 'post' })

    onMounted(async () => {
      await loadPositions()
      await loadOverview()
    })

    return {
      loadOverview,
      lex,
      period,
      periodOptions,
      position,
      positionOptions,
      summary,
      summaryLoading,
      delta,
      compareEnabled,
      showCompareToggle,
      lineLabels,
      lineClicks,
      lineImpressions,
      lineConversions,
      compareClicks,
      compareImpressions,
      compareConversions,
      chartLabels,
      chartValues,
      exportCsv,
      statsPeriod,
      formatDelta,
      deltaClass,
    }
  },
  template: `
    <div class="flex flex-column gap-3">
      <PageSection :title="lex('bannerpro.stats')">
        <FilterToolbar :showSearch="false">
          <template #start>
            <Select v-model="period" :options="periodOptions" optionLabel="label" optionValue="value"
              :emptyMessage="lex('bannerpro.empty_options')" class="w-15rem" @update:modelValue="loadOverview" />
            <Select v-model="position" :options="positionOptions" optionLabel="label" optionValue="value"
              :emptyMessage="lex('bannerpro.empty_options')" class="w-15rem" @update:modelValue="loadOverview" />
            <div v-if="showCompareToggle" class="flex align-items-center gap-2">
              <ToggleSwitch v-model="compareEnabled" inputId="bannerpro-compare-week" />
              <label for="bannerpro-compare-week" class="text-sm">{{ lex('bannerpro.stats.compare_last_week') }}</label>
            </div>
          </template>
          <template #filters>
            <Button :label="lex('bannerpro.stats.export')" icon="pi pi-download" severity="success"
              @click="exportCsv('report')" />
          </template>
        </FilterToolbar>

        <div class="grid">
          <div class="col-12 sm:col-6 lg:col-4">
            <StatCard :label="lex('bannerpro.ads.clicks')" :value="summary.clicks"
              icon="pi pi-bolt" :loading="summaryLoading">
              <template v-if="compareEnabled && !summaryLoading">
                <div :class="deltaClass(delta.clicks_pct)">
                  {{ formatDelta(delta.clicks_pct) }} {{ lex('bannerpro.stats.delta_vs') }}
                </div>
              </template>
            </StatCard>
          </div>
          <div class="col-12 sm:col-6 lg:col-4">
            <StatCard :label="lex('bannerpro.stats.impressions')" :value="summary.impressions"
              icon="pi pi-eye" :loading="summaryLoading">
              <template v-if="compareEnabled && !summaryLoading">
                <div :class="deltaClass(delta.impressions_pct)">
                  {{ formatDelta(delta.impressions_pct) }} {{ lex('bannerpro.stats.delta_vs') }}
                </div>
              </template>
            </StatCard>
          </div>
          <div class="col-12 sm:col-6 lg:col-4">
            <StatCard :label="lex('bannerpro.stats.ctr')"
              :value="summary.ctr != null ? summary.ctr + '%' : '—'"
              icon="pi pi-percentage" :loading="summaryLoading" accent>
              <template v-if="compareEnabled && !summaryLoading">
                <div :class="deltaClass(delta.ctr_pp)">
                  {{ formatDelta(delta.ctr_pp, ' pp') }} {{ lex('bannerpro.stats.delta_vs') }}
                </div>
              </template>
            </StatCard>
          </div>
          <div class="col-12 sm:col-6 lg:col-6">
            <StatCard :label="lex('bannerpro.ads.conversions')" :value="summary.conversions"
              icon="pi pi-shopping-cart" :loading="summaryLoading">
              <template v-if="compareEnabled && !summaryLoading">
                <div :class="deltaClass(delta.conversions_pct)">
                  {{ formatDelta(delta.conversions_pct) }} {{ lex('bannerpro.stats.delta_vs') }}
                </div>
              </template>
            </StatCard>
          </div>
          <div class="col-12 sm:col-6 lg:col-6">
            <StatCard :label="lex('bannerpro.stats.cvr')"
              :value="summary.cvr != null ? summary.cvr + '%' : '—'"
              icon="pi pi-chart-bar" :loading="summaryLoading" accent>
              <template v-if="compareEnabled && !summaryLoading">
                <div :class="deltaClass(delta.cvr_pp)">
                  {{ formatDelta(delta.cvr_pp, ' pp') }} {{ lex('bannerpro.stats.delta_vs') }}
                </div>
              </template>
            </StatCard>
          </div>
        </div>
      </PageSection>

      <FunnelChart
        :impressions="summary.impressions"
        :clicks="summary.clicks"
        :conversions="summary.conversions"
        :ctr="summary.ctr"
        :cvr="summary.cvr"
        :loading="summaryLoading" />

      <CompareLineChart v-if="compareEnabled"
        :labels="lineLabels"
        :clicks="lineClicks"
        :impressions="lineImpressions"
        :conversions="lineConversions"
        :compareClicks="compareClicks"
        :compareImpressions="compareImpressions"
        :compareConversions="compareConversions" />
      <StatsLineChart v-else
        :labels="lineLabels"
        :clicks="lineClicks"
        :impressions="lineImpressions"
        :conversions="lineConversions" />

      <Tabs value="overview" class="bannerpro-mgr-tabs">
        <TabList>
          <Tab value="overview">{{ lex('bannerpro.stats.overview') }}</Tab>
          <Tab value="clicks">{{ lex('bannerpro.stats.clicks') }}</Tab>
          <Tab value="referrers">{{ lex('bannerpro.stats.referrers') }}</Tab>
        </TabList>
        <TabPanels>
          <TabPanel value="overview">
            <TopAdsTable :period="statsPeriod" :position="position" />
          </TabPanel>
          <TabPanel value="clicks">
            <ClicksChart :labels="chartLabels" :values="chartValues" />
          </TabPanel>
          <TabPanel value="referrers">
            <ReferrersTable :period="statsPeriod" :position="position" />
          </TabPanel>
        </TabPanels>
      </Tabs>
    </div>
  `,
})
