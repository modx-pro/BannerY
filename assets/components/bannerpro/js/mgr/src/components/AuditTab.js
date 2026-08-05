import { defineComponent, onMounted, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Select from 'primevue/select'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import Popover from 'primevue/popover'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import { lex } from '../request.js'
import {
  AUDIT_ACTIONS,
  buildFilterOptions,
  lexAuditAction,
  lexAuditEntity,
} from '../auditLabels.js'
import FilterToolbar from '../ui/FilterToolbar.js'
import EmptyState from '../ui/EmptyState.js'
import PageSection from '../ui/PageSection.js'
import { MGR_DATATABLE_SCROLL_HEIGHT } from '../layout.js'

const ENTITY_FILTER_VALUES = ['ad', 'position']

function actionSeverity(action) {
  if (action === 'remove' || action === 'bulk_remove') return 'danger'
  if (action === 'create' || action === 'enable' || action === 'bulk_enable') return 'success'
  if (action === 'update' || action === 'bulk_assign') return 'info'
  return 'secondary'
}

export default defineComponent({
  name: 'AuditTab',
  components: {
    DataTable, Column, Select, Tag, Button, Popover, FilterToolbar, EmptyState, PageSection,
  },
  setup() {
    const api = useBannerProApi()
    const { notifyError } = useBannerProNotify()
    const rows = ref([])
    const total = ref(0)
    const loading = ref(false)
    const lazy = ref({ first: 0, rows: 20, sortField: 'id', sortOrder: -1 })
    const entityFilter = ref('')
    const actionFilter = ref('')
    const payloadPopover = ref(null)
    const payloadPreview = ref('')

    const paginatorTemplate =
      'FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport'

    const entityOptions = buildFilterOptions(
      'bannerpro.audit.filter.all',
      ENTITY_FILTER_VALUES,
      lexAuditEntity,
    )

    const actionOptions = buildFilterOptions(
      'bannerpro.audit.filter.all',
      AUDIT_ACTIONS,
      lexAuditAction,
    )

    async function loadAudit() {
      loading.value = true
      try {
        const res = await api.auditGetList({
          start: lazy.value.first,
          limit: lazy.value.rows,
          sort: lazy.value.sortField,
          dir: lazy.value.sortOrder === 1 ? 'ASC' : 'DESC',
          entity: entityFilter.value,
          action_filter: actionFilter.value,
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
      loadAudit()
    }

    function onSort(e) {
      lazy.value.sortField = e.sortField
      lazy.value.sortOrder = e.sortOrder
      loadAudit()
    }

    function showPayload(event, row) {
      payloadPreview.value = row.payload_summary || row.payload || '—'
      payloadPopover.value?.toggle(event)
    }

    onMounted(loadAudit)

    return {
      lex, rows, total, loading, lazy, entityFilter, actionFilter,
      entityOptions, actionOptions, loadAudit, onPage, onSort,
      paginatorTemplate, gridScrollHeight: MGR_DATATABLE_SCROLL_HEIGHT,
      lexAuditAction, lexAuditEntity, actionSeverity, showPayload, payloadPopover, payloadPreview,
    }
  },
  template: `
    <PageSection :title="lex('bannerpro.audit')">
      <FilterToolbar :showSearch="false">
        <template #filters>
          <Select v-model="entityFilter" :options="entityOptions" optionLabel="label" optionValue="value"
            :placeholder="lex('bannerpro.audit.filter.entity')" class="w-12rem" @change="loadAudit" />
          <Select v-model="actionFilter" :options="actionOptions" optionLabel="label" optionValue="value"
            :placeholder="lex('bannerpro.audit.filter.action')" class="w-12rem" @change="loadAudit" />
        </template>
      </FilterToolbar>
      <div class="bannerpro-datatable-shell">
        <DataTable :value="rows" :loading="loading" lazy paginator scrollable :scrollHeight="gridScrollHeight"
          :rows="lazy.rows" :totalRecords="total" :first="lazy.first" :rowsPerPageOptions="[10, 20, 50, 100]"
          :paginatorTemplate="paginatorTemplate"
          :currentPageReportTemplate="lex('bannerpro.paginator.report')"
          stripedRows size="small"
          @page="onPage" @sort="onSort" sortMode="single" removableSort>
          <template #empty>
            <EmptyState :title="lex('bannerpro.empty.audit.title')" :detail="lex('bannerpro.empty.audit.detail')" />
          </template>
          <Column field="created_at" :header="lex('bannerpro.audit.date')" sortable style="width:10rem" />
          <Column field="username" :header="lex('bannerpro.audit.user')" sortable />
          <Column field="action" :header="lex('bannerpro.audit.action')" sortable style="width:10rem">
            <template #body="{ data }">
              <Tag :value="lexAuditAction(data.action)" :severity="actionSeverity(data.action)" rounded />
            </template>
          </Column>
          <Column field="entity" :header="lex('bannerpro.audit.entity')" sortable style="width:6rem">
            <template #body="{ data }">
              <Tag :value="lexAuditEntity(data.entity)" severity="secondary" rounded />
            </template>
          </Column>
          <Column field="entity_id" :header="lex('bannerpro.audit.entity_id')" sortable style="width:5rem" />
          <Column field="payload_summary" :header="lex('bannerpro.audit.summary')">
            <template #body="{ data }">
              <Button v-if="data.payload_summary" size="small" text :label="lex('bannerpro.audit.view_payload')"
                icon="pi pi-code" @click="showPayload($event, data)" />
              <span v-else>—</span>
            </template>
          </Column>
        </DataTable>
      </div>
      <Popover ref="payloadPopover">
        <div class="bannerpro-audit-payload">{{ payloadPreview }}</div>
      </Popover>
    </PageSection>
  `,
})
