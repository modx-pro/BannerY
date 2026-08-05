import { computed, defineComponent, onMounted, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import Badge from 'primevue/badge'
import { useConfirm } from 'primevue/useconfirm'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import { getConfig, lex } from '../request.js'
import PositionFormDialog from './PositionFormDialog.js'
import FilterToolbar from '../ui/FilterToolbar.js'
import EmptyState from '../ui/EmptyState.js'
import PageSection from '../ui/PageSection.js'
import { MGR_DATATABLE_SCROLL_HEIGHT } from '../layout.js'

export default defineComponent({
  name: 'PositionsTab',
  components: {
    DataTable, Column, Button, Menu, Badge, PositionFormDialog, FilterToolbar, EmptyState, PageSection,
  },
  setup() {
    const api = useBannerProApi()
    const confirm = useConfirm()
    const { notifySuccess, notifyError } = useBannerProNotify()
    const rows = ref([])
    const total = ref(0)
    const loading = ref(false)
    const lazy = ref({ first: 0, rows: 20 })
    const dialogVisible = ref(false)
    const editRecord = ref(null)
    const rowMenu = ref(null)
    const rowMenuItems = ref([])
    const canSave = computed(() => !!getConfig().permissions?.save)
    const canRemove = computed(() => !!getConfig().permissions?.remove)

    const paginatorTemplate =
      'FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport'

    async function load() {
      loading.value = true
      try {
        const res = await api.positionsGetList({
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

    function openCreate() {
      editRecord.value = null
      dialogVisible.value = true
    }

    function openEdit(row) {
      editRecord.value = { ...row }
      dialogVisible.value = true
    }

    function remove(row) {
      confirm.require({
        message: lex('bannerpro.positions.remove.confirm'),
        header: lex('bannerpro.positions.remove'),
        accept: () => api.positionsRemove(row.id)
          .then(() => {
            notifySuccess(lex('bannerpro.positions.success.remove'))
            return load()
          })
          .catch((e) => notifyError(e.message)),
      })
    }

    function duplicate(row) {
      api.positionsDuplicate(row.id)
        .then(() => {
          notifySuccess(lex('bannerpro.positions.success.duplicate'))
          return load()
        })
        .catch((e) => notifyError(e.message))
    }

    function openRowMenu(event, row) {
      const items = []
      if (canSave.value) {
        items.push(
          { label: lex('bannerpro.positions.update'), icon: 'pi pi-pencil', command: () => openEdit(row) },
          { label: lex('bannerpro.positions.duplicate'), icon: 'pi pi-copy', command: () => duplicate(row) },
        )
      }
      if (canRemove.value) {
        items.push({ label: lex('bannerpro.positions.remove'), icon: 'pi pi-trash', command: () => remove(row) })
      }
      rowMenuItems.value = items
      rowMenu.value?.toggle(event)
    }

    onMounted(load)

    return {
      lex, rows, total, loading, lazy, dialogVisible, editRecord, canSave, canRemove,
      paginatorTemplate, load, onPage, openCreate, openEdit, remove, duplicate,
      openRowMenu, rowMenu, rowMenuItems, gridScrollHeight: MGR_DATATABLE_SCROLL_HEIGHT,
    }
  },
  template: `
    <PageSection :title="lex('bannerpro.positions')">
      <FilterToolbar :showSearch="false">
        <template #start>
          <Button v-if="canSave" :label="lex('bannerpro.positions.new')" icon="pi pi-plus" @click="openCreate" />
        </template>
      </FilterToolbar>
      <div class="bannerpro-datatable-shell">
        <DataTable :value="rows" :loading="loading" lazy paginator scrollable :scrollHeight="gridScrollHeight"
          :rows="lazy.rows" :totalRecords="total"
          :first="lazy.first" :rowsPerPageOptions="[10, 20, 50, 100]"
          :paginatorTemplate="paginatorTemplate"
          :currentPageReportTemplate="lex('bannerpro.paginator.report')"
          stripedRows size="small"
          @page="onPage" @row-dblclick="(e) => openEdit(e.data)">
          <template #empty>
            <EmptyState :title="lex('bannerpro.empty.positions.title')" :detail="lex('bannerpro.empty.positions.detail')">
              <Button v-if="canSave" :label="lex('bannerpro.positions.new')" icon="pi pi-plus" @click="openCreate" />
            </EmptyState>
          </template>
          <Column field="id" header="ID" style="width:4rem" />
          <Column field="name" :header="lex('bannerpro.positions.name')" />
          <Column field="context_key" :header="lex('bannerpro.positions.context_key')" style="width:6rem">
            <template #body="{ data }">{{ data.context_key || '—' }}</template>
          </Column>
          <Column field="clicks" :header="lex('bannerpro.positions.clicks')" style="width:5rem">
            <template #body="{ data }">
              <Badge :value="data.clicks" severity="info" />
            </template>
          </Column>
          <Column header="" style="width:3.5rem" bodyClass="bannerpro-col-actions">
            <template #body="{ data }">
              <Button icon="pi pi-ellipsis-v" text rounded size="small"
                @click="openRowMenu($event, data)" :aria-label="lex('bannerpro.actions.menu')" />
            </template>
          </Column>
        </DataTable>
      </div>
      <Menu ref="rowMenu" :model="rowMenuItems" popup />
      <PositionFormDialog v-model:visible="dialogVisible" :record="editRecord" @saved="load" />
    </PageSection>
  `,
})
