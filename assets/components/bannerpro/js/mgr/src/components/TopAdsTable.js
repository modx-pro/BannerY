import { defineComponent, onMounted, ref, watch } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Badge from 'primevue/badge'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import { lex } from '../request.js'
import EmptyState from '../ui/EmptyState.js'

export default defineComponent({
  name: 'TopAdsTable',
  components: { DataTable, Column, Badge, EmptyState },
  props: {
    period: { type: String, default: '' },
    position: { type: Number, default: 0 },
  },
  setup(props) {
    const api = useBannerProApi()
    const { notifyError } = useBannerProNotify()
    const rows = ref([])
    const total = ref(0)
    const loading = ref(false)
    const lazy = ref({ first: 0, rows: 20 })

    async function load() {
      loading.value = true
      try {
        const res = await api.statsTopAds({
          period: props.period,
          position: props.position || '',
          start: lazy.value.first,
          limit: lazy.value.rows,
        })
        rows.value = res.data || []
        total.value = res.total ?? rows.value.length
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    function onPage(e) {
      lazy.value.first = e.first
      lazy.value.rows = e.rows
      load()
    }

    watch(() => [props.period, props.position], () => {
      lazy.value.first = 0
      load()
    })

    onMounted(load)

    return { lex, rows, total, loading, lazy, onPage }
  },
  template: `
    <div class="bannerpro-datatable-shell">
      <DataTable :value="rows" :loading="loading" lazy paginator size="small"
        stripedRows
        :rows="lazy.rows" :totalRecords="total" :rowsPerPageOptions="[10, 20, 50]"
        :first="lazy.first" @page="onPage">
        <template #empty>
          <EmptyState :title="lex('bannerpro.empty.stats.title')" />
        </template>
        <Column field="name" :header="lex('bannerpro.ads.name')" />
        <Column field="clicks" :header="lex('bannerpro.ads.clicks')">
          <template #body="{ data }">
            <Badge :value="data.clicks" severity="info" />
          </template>
        </Column>
        <Column field="impressions" :header="lex('bannerpro.stats.impressions')" />
        <Column field="conversions" :header="lex('bannerpro.ads.conversions')" />
        <Column field="ctr" :header="lex('bannerpro.stats.ctr')">
          <template #body="{ data }">
            {{ data.ctr != null ? data.ctr + '%' : '—' }}
          </template>
        </Column>
      </DataTable>
    </div>
  `,
})
