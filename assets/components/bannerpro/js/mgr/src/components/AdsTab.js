import { defineComponent, onMounted, ref, computed } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import MultiSelect from 'primevue/multiselect'
import Checkbox from 'primevue/checkbox'
import AutoComplete from 'primevue/autocomplete'
import SplitButton from 'primevue/splitbutton'
import Accordion from 'primevue/accordion'
import AccordionPanel from 'primevue/accordionpanel'
import AccordionHeader from 'primevue/accordionheader'
import AccordionContent from 'primevue/accordioncontent'
import Menu from 'primevue/menu'
import Badge from 'primevue/badge'
import { useConfirm } from 'primevue/useconfirm'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import { getConfig, lex } from '../request.js'
import AdFormDialog from './AdFormDialog.js'
import TemplatePicker from './TemplatePicker.js'
import FilterToolbar from '../ui/FilterToolbar.js'
import StatusTag from '../ui/StatusTag.js'
import ThumbImage from '../ui/ThumbImage.js'
import EmptyState from '../ui/EmptyState.js'
import PageSection from '../ui/PageSection.js'
import { MGR_DATATABLE_SCROLL_HEIGHT } from '../layout.js'
import { BANNERPRO_DIALOG_CONTENT_STYLE, BANNERPRO_DIALOG_PT } from '../dialogUi.js'

export default defineComponent({
  name: 'AdsTab',
  components: {
    DataTable, Column, Button, Select, Dialog, MultiSelect, Checkbox,
    AutoComplete, SplitButton, Accordion, AccordionPanel, AccordionHeader, AccordionContent,
    Menu, Badge, AdFormDialog, TemplatePicker, FilterToolbar, StatusTag, ThumbImage, EmptyState, PageSection,
  },
  setup() {
    const api = useBannerProApi()
    const confirm = useConfirm()
    const { notifySuccess, notifyError, notifyWarn } = useBannerProNotify()
    const rows = ref([])
    const total = ref(0)
    const loading = ref(false)
    const lazy = ref({ first: 0, rows: 20, sortField: 'id', sortOrder: 1 })
    const query = ref('')
    const positionFilter = ref(null)
    const activeFilter = ref(null)
    const typeFilter = ref(null)
    const productFilterMode = ref(null)
    const productSearch = ref(null)
    const productSuggestions = ref([])
    const categorySearch = ref(null)
    const tagSearch = ref(null)
    const tagSuggestions = ref([])
    const categorySuggestions = ref([])
    const positions = ref([])
    const selection = ref([])
    const dialogVisible = ref(false)
    const editRecord = ref(null)
    const templatePickerVisible = ref(false)
    const assignDialogVisible = ref(false)
    const assignPositions = ref([])
    const assignReplace = ref(false)
    const bulkLoading = ref(false)
    const rowMenu = ref(null)
    const rowMenuItems = ref([])
    const canSave = computed(() => !!getConfig().permissions?.save)
    const canRemove = computed(() => !!getConfig().permissions?.remove)
    const hasSelection = computed(() => selection.value.length > 0)

    const activeOptions = computed(() => [
      { label: lex('bannerpro.ads.filter.active.all'), value: null },
      { label: lex('yes'), value: 1 },
      { label: lex('no'), value: 0 },
    ])

    const typeOptions = computed(() => [
      { label: lex('bannerpro.ads.filter.type.all'), value: null },
      { label: lex('bannerpro.ads.type.image'), value: 'image' },
      { label: lex('bannerpro.ads.type.html'), value: 'html' },
    ])

    const productModeOptions = computed(() => [
      { label: lex('bannerpro.ads.filter.product.all'), value: null },
      { label: lex('bannerpro.ads.filter.product.global'), value: 0 },
      { label: lex('bannerpro.ads.product'), value: 'specific' },
    ])

    const newAdMenu = computed(() => [
      { label: lex('bannerpro.templates.create'), icon: 'pi pi-clone', command: openTemplatePicker },
    ])

    const paginatorTemplate =
      'FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport'

    function resolveProductIdParam() {
      if (productFilterMode.value === null) return ''
      if (productFilterMode.value === 0) return 0
      if (productFilterMode.value === 'specific') {
        const id = Number(productSearch.value?.value ?? productSearch.value ?? 0)
        return id > 0 ? id : ''
      }
      return ''
    }

    async function loadPositions() {
      const res = await api.positionsGetList({ start: 0, limit: 0 })
      positions.value = (res.data || []).map((p) => ({ label: p.name, value: p.id }))
    }

    async function loadAds() {
      loading.value = true
      try {
        const params = {
          start: lazy.value.first,
          limit: lazy.value.rows,
          sort: lazy.value.sortField,
          dir: lazy.value.sortOrder === 1 ? 'ASC' : 'DESC',
          query: query.value,
          position: positionFilter.value || '',
        }
        if (activeFilter.value !== null) params.active = activeFilter.value
        if (typeFilter.value) params.type = typeFilter.value
        const productId = resolveProductIdParam()
        if (productId !== '') params.product_id = productId
        const categoryId = Number(categorySearch.value?.value ?? 0)
        if (categoryId > 0) params.category_id = categoryId
        const tag = String(tagSearch.value?.value ?? tagSearch.value ?? '').trim()
        if (tag) params.tag = tag

        const res = await api.adsGetList(params)
        rows.value = res.data || []
        total.value = res.total ?? rows.value.length
        selection.value = []
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    async function searchTags(event) {
      try {
        const res = await api.tagsSuggest({ query: event.query || '', limit: 20 })
        tagSuggestions.value = (res.data || []).map((tag) => ({ label: tag, value: tag }))
      } catch {
        tagSuggestions.value = []
      }
    }

    function onTagPick(e) {
      if (e?.value) {
        tagSearch.value = e.value
        loadAds()
      }
    }

    async function searchProducts(event) {
      const res = await api.resourceGetList({
        query: event.query,
        start: 0,
        limit: 20,
        class_key: 'msProduct',
      })
      productSuggestions.value = (res.data || []).map((r) => ({
        label: r.pagetitle,
        value: Number(r.id),
      }))
    }

    function onProductPick(e) {
      if (e?.value) {
        productSearch.value = e.value
        loadAds()
      }
    }

    async function searchCategories(event) {
      const res = await api.resourceGetList({
        query: event.query,
        start: 0,
        limit: 20,
        class_key: 'msCategory',
      })
      categorySuggestions.value = (res.data || []).map((r) => ({
        label: r.pagetitle,
        value: Number(r.id),
      }))
    }

    function onCategoryPick(e) {
      if (e?.value) {
        categorySearch.value = e.value
        loadAds()
      }
    }

    function onPage(e) {
      lazy.value.first = e.first
      lazy.value.rows = e.rows
      loadAds()
    }

    function onSort(e) {
      lazy.value.sortField = e.sortField
      lazy.value.sortOrder = e.sortOrder
      loadAds()
    }

    function clearFilters() {
      query.value = ''
      positionFilter.value = null
      activeFilter.value = null
      typeFilter.value = null
      productFilterMode.value = null
      productSearch.value = null
      categorySearch.value = null
      tagSearch.value = null
      lazy.value.first = 0
      loadAds()
    }

    function openCreate() {
      if (!positions.value.length) {
        notifyWarn(lex('bannerpro.error.no_positions'))
        return
      }
      editRecord.value = null
      dialogVisible.value = true
    }

    function openTemplatePicker() {
      if (!positions.value.length) {
        notifyWarn(lex('bannerpro.error.no_positions'))
        return
      }
      templatePickerVisible.value = true
    }

    async function onTemplateSelected(template) {
      try {
        const res = await api.adsCreateFromTemplate({ template_id: template.id })
        editRecord.value = res.data
        dialogVisible.value = true
        notifySuccess(lex('bannerpro.ads.success.create'))
        await loadAds()
      } catch (e) {
        notifyError(e.message)
      }
    }

    async function openEdit(row) {
      if (!positions.value.length) {
        notifyWarn(lex('bannerpro.error.no_positions'))
        return
      }
      const res = await api.adsGet(row.id)
      editRecord.value = res.data
      dialogVisible.value = true
    }

    function selectedIds() {
      return selection.value.map((row) => row.id)
    }

    async function runBulk(actionFn, successKey) {
      bulkLoading.value = true
      try {
        const res = await actionFn(selectedIds())
        const affected = res.data?.affected ?? 0
        notifySuccess(lex(successKey).replace('{count}', String(affected)))
        await loadAds()
      } catch (e) {
        notifyError(e.message)
      } finally {
        bulkLoading.value = false
      }
    }

    function bulkEnable() {
      runBulk((ids) => api.adsBulkEnable(ids), 'bannerpro.bulk.success')
    }

    function bulkDisable() {
      runBulk((ids) => api.adsBulkDisable(ids), 'bannerpro.bulk.success')
    }

    function bulkRemove() {
      confirm.require({
        message: lex('bannerpro.bulk.remove.confirm').replace('{count}', String(selection.value.length)),
        header: lex('bannerpro.bulk.remove'),
        accept: () => runBulk((ids) => api.adsBulkRemove(ids), 'bannerpro.bulk.success'),
      })
    }

    function openAssignDialog() {
      assignPositions.value = []
      assignReplace.value = false
      assignDialogVisible.value = true
    }

    async function confirmAssign() {
      if (!assignPositions.value.length) {
        notifyWarn(lex('bannerpro.bulk.positions_required'))
        return
      }
      bulkLoading.value = true
      try {
        const res = await api.adsBulkAssignPositions({
          ids: selectedIds(),
          positions: assignPositions.value,
          replace: assignReplace.value ? 1 : 0,
        })
        assignDialogVisible.value = false
        const affected = res.data?.affected ?? 0
        notifySuccess(lex('bannerpro.bulk.success').replace('{count}', String(affected)))
        await loadAds()
      } catch (e) {
        notifyError(e.message)
      } finally {
        bulkLoading.value = false
      }
    }

    function toggleActive(row) {
      const action = row.active ? api.adsDisable : api.adsEnable
      action(row.id)
        .then(() => {
          notifySuccess(lex(row.active ? 'bannerpro.ads.success.disable' : 'bannerpro.ads.success.enable'))
          return loadAds()
        })
        .catch((e) => notifyError(e.message))
    }

    function removeAd(row) {
      confirm.require({
        message: lex('bannerpro.ads.remove.confirm'),
        header: lex('bannerpro.ads.remove'),
        accept: () => api.adsRemove(row.id)
          .then(() => {
            notifySuccess(lex('bannerpro.ads.success.remove'))
            return loadAds()
          })
          .catch((e) => notifyError(e.message)),
      })
    }

    function duplicateAd(row) {
      api.adsDuplicate(row.id)
        .then(() => {
          notifySuccess(lex('bannerpro.ads.success.duplicate'))
          return loadAds()
        })
        .catch((e) => notifyError(e.message))
    }

    function openRowMenu(event, row) {
      const items = []
      if (canSave.value) {
        items.push(
          { label: lex('bannerpro.ads.update'), icon: 'pi pi-pencil', command: () => openEdit(row) },
          { label: lex('bannerpro.ads.duplicate'), icon: 'pi pi-copy', command: () => duplicateAd(row) },
          {
            label: row.active ? lex('bannerpro.bulk.disable') : lex('bannerpro.bulk.enable'),
            icon: row.active ? 'pi pi-ban' : 'pi pi-check',
            command: () => toggleActive(row),
          },
        )
      }
      if (canRemove.value) {
        items.push({
          label: lex('bannerpro.ads.remove'),
          icon: 'pi pi-trash',
          command: () => removeAd(row),
        })
      }
      rowMenuItems.value = items
      rowMenu.value?.toggle(event)
    }

    onMounted(async () => {
      await loadPositions()
      await loadAds()
    })

    return {
      lex, rows, total, loading, lazy, query, positionFilter, activeFilter, typeFilter,
      productFilterMode, productSearch, productSuggestions, categorySearch, categorySuggestions,
      tagSearch, tagSuggestions, positions, selection,
      dialogVisible, editRecord, templatePickerVisible, assignDialogVisible,
      assignPositions, assignReplace, bulkLoading, canSave, canRemove, hasSelection,
      activeOptions, typeOptions, productModeOptions, newAdMenu, loadAds, onPage, onSort, clearFilters,
      openCreate, openTemplatePicker, onTemplateSelected, openEdit, bulkEnable, bulkDisable,
      bulkRemove, openAssignDialog, confirmAssign, searchProducts, onProductPick,
      searchCategories, onCategoryPick, searchTags, onTagPick,
      toggleActive, removeAd, duplicateAd, openRowMenu, rowMenu, rowMenuItems,
      paginatorTemplate, gridScrollHeight: MGR_DATATABLE_SCROLL_HEIGHT,
      dialogContentStyle: BANNERPRO_DIALOG_CONTENT_STYLE,
      dialogPt: BANNERPRO_DIALOG_PT,
    }
  },
  template: `
    <PageSection :title="lex('bannerpro.ads')">
      <FilterToolbar v-model:search="query" :searchPlaceholder="lex('search')"
        :clearTitle="lex('bannerpro.filters.clear')"
        @search="loadAds" @clear="clearFilters">
        <template #start>
          <SplitButton v-if="canSave" :label="lex('bannerpro.ads.new')" icon="pi pi-plus"
            :model="newAdMenu" @click="openCreate" />
          <template v-if="canSave && hasSelection">
            <span class="bannerpro-bulk-selected">{{ lex('bannerpro.bulk.selected').replace('{count}', String(selection.length)) }}</span>
            <Button :label="lex('bannerpro.bulk.enable')" icon="pi pi-check" size="small" :loading="bulkLoading" @click="bulkEnable" />
            <Button :label="lex('bannerpro.bulk.disable')" icon="pi pi-ban" size="small" severity="secondary" :loading="bulkLoading" @click="bulkDisable" />
            <Button :label="lex('bannerpro.bulk.assign_positions')" icon="pi pi-sitemap" size="small" severity="secondary" :loading="bulkLoading" @click="openAssignDialog" />
            <Button v-if="canRemove" :label="lex('bannerpro.bulk.remove')" icon="pi pi-trash" size="small" severity="danger" :loading="bulkLoading" @click="bulkRemove" />
          </template>
        </template>
        <template #filters>
          <Select v-model="activeFilter" :options="activeOptions" optionLabel="label" optionValue="value"
            :placeholder="lex('bannerpro.ads.filter.active')" showClear class="w-10rem" @change="loadAds" />
          <Select v-model="typeFilter" :options="typeOptions" optionLabel="label" optionValue="value"
            :placeholder="lex('bannerpro.ads.filter.type')" showClear class="w-10rem" @change="loadAds" />
          <Select v-model="positionFilter" :options="positions" optionLabel="label" optionValue="value"
            :placeholder="lex('bannerpro.positions.select')" :emptyMessage="lex('bannerpro.empty_options')"
            showClear class="w-12rem" @change="loadAds" />
        </template>
      </FilterToolbar>

      <Accordion class="mb-3">
        <AccordionPanel value="advanced">
          <AccordionHeader>{{ lex('bannerpro.filters.advanced') }}</AccordionHeader>
          <AccordionContent>
            <div class="flex flex-wrap gap-2 pt-2">
              <Select v-model="productFilterMode" :options="productModeOptions" optionLabel="label" optionValue="value"
                :placeholder="lex('bannerpro.ads.filter.product')" showClear class="w-12rem" @change="loadAds" />
              <AutoComplete v-if="productFilterMode === 'specific'" v-model="productSearch"
                :suggestions="productSuggestions" optionLabel="label" forceSelection dropdown
                :placeholder="lex('bannerpro.ads.product.placeholder')" class="w-14rem"
                @complete="searchProducts" @item-select="onProductPick" @change="loadAds" />
              <AutoComplete v-model="categorySearch" :suggestions="categorySuggestions" optionLabel="label"
                forceSelection dropdown showClear
                :placeholder="lex('bannerpro.ads.category.filter')" class="w-14rem"
                @complete="searchCategories" @item-select="onCategoryPick" @clear="loadAds" @change="loadAds" />
              <AutoComplete v-model="tagSearch" :suggestions="tagSuggestions" optionLabel="label"
                forceSelection dropdown showClear
                :placeholder="lex('bannerpro.ads.filter.tag')" class="w-14rem"
                @complete="searchTags" @item-select="onTagPick" @clear="loadAds" />
            </div>
          </AccordionContent>
        </AccordionPanel>
      </Accordion>

      <div class="bannerpro-datatable-shell">
        <DataTable v-model:selection="selection" :value="rows" :loading="loading" lazy paginator scrollable
          :scrollHeight="gridScrollHeight" :rows="lazy.rows" :totalRecords="total" :first="lazy.first"
          :rowsPerPageOptions="[10, 20, 50, 100]" :paginatorTemplate="paginatorTemplate"
          :currentPageReportTemplate="lex('bannerpro.paginator.report')"
          stripedRows size="small"
          @page="onPage" @sort="onSort" sortMode="single" removableSort
          :rowClass="(data) => (!data.active ? 'bannerpro-row-disabled' : '')"
          @row-dblclick="(e) => openEdit(e.data)">
          <template #empty>
            <EmptyState :title="lex('bannerpro.empty.ads.title')" :detail="lex('bannerpro.empty.ads.detail')">
              <Button v-if="canSave" :label="lex('bannerpro.ads.new')" icon="pi pi-plus" @click="openCreate" />
            </EmptyState>
          </template>
          <Column v-if="canSave" selectionMode="multiple" headerStyle="width:3rem" />
          <Column field="id" header="ID" sortable style="width:4rem" />
          <Column field="name" :header="lex('bannerpro.ads.name')" sortable />
          <Column field="type" :header="lex('bannerpro.ads.type')" style="width:7rem">
            <template #body="{ data }">
              <StatusTag :type="data.type" />
            </template>
          </Column>
          <Column field="url" :header="lex('bannerpro.ads.url')" sortable />
          <Column field="category_title" :header="lex('bannerpro.ads.category')" style="width:8rem" />
          <Column field="clicks" :header="lex('bannerpro.ads.clicks')" style="width:5rem">
            <template #body="{ data }">
              <Badge :value="data.clicks" severity="info" />
            </template>
          </Column>
          <Column field="conversions" :header="lex('bannerpro.ads.conversions')" style="width:5rem" />
          <Column field="active" :header="lex('bannerpro.ads.active')" sortable style="width:6rem">
            <template #body="{ data }">
              <StatusTag :active="!!data.active" />
            </template>
          </Column>
          <Column :header="lex('bannerpro.ads.image')" style="width:5.5rem">
            <template #body="{ data }">
              <ThumbImage :src="data.current_image" />
            </template>
          </Column>
          <Column field="start" :header="lex('bannerpro.ads.start')" sortable />
          <Column field="end" :header="lex('bannerpro.ads.end')" sortable />
          <Column header="" style="width:3.5rem" bodyClass="bannerpro-col-actions">
            <template #body="{ data }">
              <Button icon="pi pi-ellipsis-v" text rounded size="small"
                @click="openRowMenu($event, data)" :aria-label="lex('bannerpro.actions.menu')" />
            </template>
          </Column>
        </DataTable>
      </div>

      <Menu ref="rowMenu" :model="rowMenuItems" popup />

      <AdFormDialog v-model:visible="dialogVisible" :record="editRecord" @saved="loadAds" />
      <TemplatePicker v-model:visible="templatePickerVisible" @selected="onTemplateSelected" />
      <Dialog v-model:visible="assignDialogVisible" :header="lex('bannerpro.bulk.assign_positions.title')" modal
        class="bannerpro-bulk-assign-dialog" :contentStyle="dialogContentStyle" :pt="dialogPt">
        <MultiSelect v-model="assignPositions" :options="positions" optionLabel="label" optionValue="value"
          :placeholder="lex('bannerpro.positions.select')" display="chip" class="bannerpro-bulk-assign-positions" />
        <div class="bannerpro-bulk-replace-row">
          <Checkbox v-model="assignReplace" inputId="bulk-replace" :binary="true" />
          <label for="bulk-replace">{{ lex('bannerpro.bulk.replace_positions') }}</label>
        </div>
        <template #footer>
          <Button :label="lex('bannerpro.cancel')" severity="secondary" text @click="assignDialogVisible = false" />
          <Button :label="lex('bannerpro.save')" icon="pi pi-check" :loading="bulkLoading" @click="confirmAssign" />
        </template>
      </Dialog>
    </PageSection>
  `,
})
