import { defineComponent, onMounted, ref } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import StatCard from '../ui/StatCard.js'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import { STATS_PERIOD_LAST_7_DAYS } from '../composables/usePeriodFilter.js'
import { lex } from '../request.js'

export default defineComponent({
  name: 'OverviewWidget',
  components: { Card, Button, StatCard },
  emits: ['open-stats'],
  setup(_, { emit }) {
    const api = useBannerProApi()
    const { notifyError } = useBannerProNotify()
    const summary = ref({ clicks: 0, impressions: 0, conversions: 0, ctr: null, cvr: null })
    const loading = ref(false)

    async function loadSummary() {
      loading.value = true
      try {
        const res = await api.statsSummary({ period: STATS_PERIOD_LAST_7_DAYS, position: '' })
        summary.value = res.data ?? { clicks: 0, impressions: 0, conversions: 0, ctr: null, cvr: null }
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    onMounted(loadSummary)

    return {
      summary,
      loading,
      lex,
      openStats: () => emit('open-stats'),
    }
  },
  template: `
    <Card class="bannerpro-overview-widget">
      <template #content>
        <div class="bannerpro-overview-widget__header flex flex-wrap align-items-center justify-content-between gap-2">
          <div>
            <div class="bannerpro-overview-widget__eyebrow">{{ lex('bannerpro.stats.last_7_days') }}</div>
            <h3 class="bannerpro-page-section__title m-0">{{ lex('bannerpro.stats.overview_widget') }}</h3>
          </div>
          <Button :label="lex('bannerpro.stats.more_details')" icon="pi pi-arrow-right" iconPos="right"
            size="small" @click="openStats" />
        </div>
        <div class="grid">
          <div class="col-12 md:col-4">
            <StatCard :label="lex('bannerpro.ads.clicks')" :value="summary.clicks"
              icon="pi pi-bolt" :loading="loading" />
          </div>
          <div class="col-12 md:col-4">
            <StatCard :label="lex('bannerpro.stats.impressions')" :value="summary.impressions"
              icon="pi pi-eye" :loading="loading" />
          </div>
          <div class="col-12 md:col-4">
            <StatCard :label="lex('bannerpro.stats.ctr')"
              :value="summary.ctr != null ? summary.ctr + '%' : '—'"
              icon="pi pi-percentage" :loading="loading" accent />
          </div>
          <div class="col-12 md:col-6">
            <StatCard :label="lex('bannerpro.ads.conversions')" :value="summary.conversions"
              icon="pi pi-shopping-cart" :loading="loading" />
          </div>
          <div class="col-12 md:col-6">
            <StatCard :label="lex('bannerpro.stats.cvr')"
              :value="summary.cvr != null ? summary.cvr + '%' : '—'"
              icon="pi pi-chart-bar" :loading="loading" accent />
          </div>
        </div>
      </template>
    </Card>
  `,
})
