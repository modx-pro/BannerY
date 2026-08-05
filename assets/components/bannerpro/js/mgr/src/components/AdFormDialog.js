import { defineComponent, ref, watch, computed } from 'vue'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import AutoComplete from 'primevue/autocomplete'
import DatePicker from 'primevue/datepicker'
import ToggleSwitch from 'primevue/toggleswitch'
import MultiSelect from 'primevue/multiselect'
import InputNumber from 'primevue/inputnumber'
import Chips from 'primevue/chips'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import FloatLabel from 'primevue/floatlabel'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import { getConfig, lex } from '../request.js'
import { getMinSelectableDateTime, resolveMinEndDateTime } from '../datePickerLimits.js'
import { BANNERPRO_DIALOG_CONTENT_STYLE, BANNERPRO_DIALOG_PT } from '../dialogUi.js'
import AdPreviewPanel from './AdPreviewPanel.js'

export default defineComponent({
  name: 'AdFormDialog',
  components: {
    Dialog, InputText, Textarea, Button, AutoComplete, DatePicker,
    ToggleSwitch, MultiSelect, InputNumber, AdPreviewPanel, Chips,
    Tabs, TabList, Tab, TabPanels, TabPanel, FloatLabel, IconField, InputIcon,
  },
  props: {
    visible: Boolean,
    record: { type: Object, default: null },
  },
  emits: ['update:visible', 'saved'],
  setup(props, { emit }) {
    const api = useBannerProApi()
    const { notifySuccess, notifyError, notifyWarn } = useBannerProNotify()
    const saving = ref(false)
    const activeFormTab = ref('main')
    const form = ref(emptyForm())
    const positions = ref([])
    const resourceSuggestions = ref([])
    const productOptions = ref([])
    const productOptionsLoading = ref(false)
    const categoryOptions = ref([])
    const categoryOptionsLoading = ref(false)
    const targetResourceSuggestions = ref([])
    const targetParentSuggestions = ref([])
    const tagSuggestions = ref([])
    let productFilterTimer = null
    let categoryFilterTimer = null
    let tagSuggestTimer = null
    const preview = ref('')
    const minStartDate = computed(() => getMinSelectableDateTime())
    const minEndDate = computed(() => resolveMinEndDateTime(form.value.start))
    const positionOptions = computed(() => positions.value.map((pos) => ({
      label: pos.name,
      value: Number(pos.id),
    })))

    const isHtmlType = computed({
      get: () => form.value.type === 'html',
      set: (value) => {
        form.value.type = value ? 'html' : 'image'
      },
    })

    const weekdayOptions = [1, 2, 3, 4, 5, 6, 7].map((value) => ({
      label: lex(`bannerpro.ads.weekday.${value}`),
      value,
    }))

    const hourOptions = Array.from({ length: 24 }, (_, hour) => ({
      label: String(hour).padStart(2, '0'),
      value: hour,
    }))

    const scheduleOnNow = computed(() => {
      const weekdays = form.value.show_hours_weekdays || []
      const hours = form.value.show_hours_hours || []
      if (!weekdays.length && !hours.length) return true
      const now = new Date()
      const dow = now.getDay() === 0 ? 7 : now.getDay()
      const hour = now.getHours()
      const dayOk = !weekdays.length || weekdays.includes(dow)
      const hourOk = !hours.length || hours.includes(hour)
      return dayOk && hourOk
    })

    const businessHoursWeekdays = [1, 2, 3, 4, 5]
    const businessHoursHours = Array.from({ length: 10 }, (_, i) => i + 9)

    function arraysEqualUnsorted(a, b) {
      if (a.length !== b.length) return false
      const sortedA = [...a].sort((x, y) => x - y)
      const sortedB = [...b].sort((x, y) => x - y)
      return sortedA.every((value, index) => value === sortedB[index])
    }

    const isBusinessHoursPreset = computed(() => {
      const weekdays = form.value.show_hours_weekdays || []
      const hours = form.value.show_hours_hours || []
      return arraysEqualUnsorted(weekdays, businessHoursWeekdays)
        && arraysEqualUnsorted(hours, businessHoursHours)
    })

    const isAllHoursPreset = computed(() => {
      const weekdays = form.value.show_hours_weekdays || []
      const hours = form.value.show_hours_hours || []
      return weekdays.length === 0 && hours.length === 0
    })

    const hasPageTargeting = computed(() => (
      form.value.target_resource_id > 0 || form.value.target_parent_id > 0
    ))

    const targetResourceDisabled = computed(() => form.value.target_parent_id > 0)
    const targetParentDisabled = computed(() => form.value.target_resource_id > 0)

    function emptyForm() {
      return {
        id: null, name: '', url: '', image: '', newimage: '', source: getConfig().mediaSource || 1,
        active: true, description: '', type: 'image', html: '', start: null, end: null, positions: [],
        product_id: 0, category_id: 0, max_clicks: 0, max_impressions: 0,
        show_hours_weekdays: [], show_hours_hours: [],
        target_resource_id: 0, target_parent_id: 0,
        tags: [],
      }
    }

    async function loadPositions() {
      const res = await api.positionsGetList({ start: 0, limit: 0 })
      positions.value = res.data || []
    }

    async function fetchResourceSuggestions(query, classKey = '') {
      const params = { query, start: 0, limit: 20 }
      if (classKey) params.class_key = classKey
      return api.resourceGetList(params)
    }

    async function searchResources(event) {
      const res = await fetchResourceSuggestions(event.query)
      resourceSuggestions.value = (res.data || []).map((r) => ({
        label: r.pagetitle,
        value: r.url || `[[~${r.id}]]`,
        raw: r,
      }))
    }

    async function searchTargetResources(event) {
      const res = await fetchResourceSuggestions(event.query)
      targetResourceSuggestions.value = (res.data || []).map((r) => ({
        label: `#${r.id} ${r.pagetitle}`,
        value: Number(r.id),
        pagetitle: r.pagetitle,
      }))
    }

    async function searchTargetParents(event) {
      const res = await fetchResourceSuggestions(event.query)
      targetParentSuggestions.value = (res.data || []).map((r) => ({
        label: `#${r.id} ${r.pagetitle}`,
        value: Number(r.id),
        pagetitle: r.pagetitle,
      }))
    }

    function onTargetResourceSelect(e) {
      const id = Number(e?.value?.value ?? e?.value ?? 0)
      form.value.target_resource_id = id > 0 ? id : 0
      if (id > 0) form.value.target_parent_id = 0
    }

    function onTargetParentSelect(e) {
      const id = Number(e?.value?.value ?? e?.value ?? 0)
      form.value.target_parent_id = id > 0 ? id : 0
      if (id > 0) form.value.target_resource_id = 0
    }

    function clearTargeting() {
      form.value.target_resource_id = 0
      form.value.target_parent_id = 0
    }

    function applyBusinessHours() {
      form.value.show_hours_weekdays = [...businessHoursWeekdays]
      form.value.show_hours_hours = [...businessHoursHours]
    }

    function applyAllHours() {
      form.value.show_hours_weekdays = []
      form.value.show_hours_hours = []
    }

    async function searchTagSuggestions(query = '') {
      try {
        const res = await api.tagsSuggest({ query, limit: 20 })
        tagSuggestions.value = res.data || []
      } catch {
        tagSuggestions.value = []
      }
    }

    function onTagsInput() {
      clearTimeout(tagSuggestTimer)
      tagSuggestTimer = setTimeout(() => searchTagSuggestions(''), 200)
    }

    function buildShowHoursPayload() {
      const weekdays = (form.value.show_hours_weekdays || []).map((d) => Number(d)).filter((d) => d >= 1 && d <= 7)
      const hours = (form.value.show_hours_hours || []).map((h) => Number(h)).filter((h) => h >= 0 && h <= 23)
      if (!weekdays.length && !hours.length) return ''
      return JSON.stringify({ weekdays, hours })
    }

    function pickResource(e) {
      if (e?.value?.value) form.value.url = e.value.value
      else if (typeof e === 'string') form.value.url = e
    }

    const selectedProductIds = computed({
      get: () => (form.value.product_id > 0 ? [form.value.product_id] : []),
      set: (v) => {
        const ids = Array.isArray(v) ? v.map((id) => Number(id)).filter((id) => id > 0) : []
        form.value.product_id = ids.length ? ids[0] : 0
        if (form.value.product_id > 0) {
          form.value.category_id = 0
        }
      },
    })

    const selectedCategoryIds = computed({
      get: () => (form.value.category_id > 0 ? [form.value.category_id] : []),
      set: (v) => {
        const ids = Array.isArray(v) ? v.map((id) => Number(id)).filter((id) => id > 0) : []
        form.value.category_id = ids.length ? ids[0] : 0
        if (form.value.category_id > 0) {
          form.value.product_id = 0
        }
      },
    })

    function mapResourceOptions(rows) {
      return (rows || []).map((r) => ({
        id: Number(r.id),
        pagetitle: r.pagetitle || '',
      }))
    }

    function mapProductOptions(rows) {
      return mapResourceOptions(rows)
    }

    async function searchCategoryOptions(query = '') {
      const keepId = form.value.category_id
      const keepTitle = categoryOptions.value.find((o) => o.id === keepId)?.pagetitle
        || props.record?.category_title
        || ''
      categoryOptionsLoading.value = true
      try {
        const res = await api.resourceGetList({
          query: String(query || '').trim(),
          start: 0,
          limit: 200,
          class_key: 'msCategory',
        })
        categoryOptions.value = mapResourceOptions(res.data)
        if (keepId > 0) {
          ensureCategoryInOptions(keepId, keepTitle)
        }
      } catch (e) {
        categoryOptions.value = []
        notifyError(e.message)
      } finally {
        categoryOptionsLoading.value = false
      }
    }

    function onCategoryFilter(event) {
      const query = String(event?.value ?? '').trim()
      clearTimeout(categoryFilterTimer)
      categoryFilterTimer = setTimeout(() => searchCategoryOptions(query), 300)
    }

    function ensureCategoryInOptions(categoryId, categoryTitle) {
      const id = Number(categoryId) || 0
      if (id < 1) return
      if (categoryOptions.value.some((o) => o.id === id)) return
      categoryOptions.value = [
        { id, pagetitle: categoryTitle || `#${id}` },
        ...categoryOptions.value,
      ]
    }

    async function searchProductOptions(query = '') {
      const keepId = form.value.product_id
      const keepTitle = productOptions.value.find((o) => o.id === keepId)?.pagetitle
        || props.record?.product_title
        || ''
      productOptionsLoading.value = true
      try {
        const res = await api.resourceGetList({
          query: String(query || '').trim(),
          start: 0,
          limit: 200,
          class_key: 'msProduct',
        })
        productOptions.value = mapProductOptions(res.data)
        if (keepId > 0) {
          ensureProductInOptions(keepId, keepTitle)
        }
      } catch (e) {
        productOptions.value = []
        notifyError(e.message)
      } finally {
        productOptionsLoading.value = false
      }
    }

    function onProductFilter(event) {
      const query = String(event?.value ?? '').trim()
      clearTimeout(productFilterTimer)
      productFilterTimer = setTimeout(() => searchProductOptions(query), 300)
    }

    function ensureProductInOptions(productId, productTitle) {
      const id = Number(productId) || 0
      if (id < 1) return
      if (productOptions.value.some((o) => o.id === id)) return
      productOptions.value = [
        { id, pagetitle: productTitle || `#${id}` },
        ...productOptions.value,
      ]
    }

    function applyProductUrl() {
      if (form.value.product_id > 0) {
        form.value.url = `[[~${form.value.product_id}]]`
      }
    }

    function openMediaBrowser() {
      if (typeof MODx !== 'undefined' && MODx.load) {
        MODx.load({
          xtype: 'modx-browser',
          listeners: {
            select: (data) => {
              form.value.image = data.relativeUrl || data.fullRelativeUrl || ''
              form.value.newimage = form.value.image
              preview.value = data.url || ''
            },
          },
        }).show()
        return
      }
      notifyWarn(lex('bannerpro.ads.image.manual'))
    }

    function formatDate(value) {
      if (!value) return ''
      const d = value instanceof Date ? value : new Date(value)
      const pad = (n) => String(n).padStart(2, '0')
      return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:00`
    }

    async function save() {
      if (form.value.product_id > 0 && form.value.category_id > 0) {
        notifyWarn(lex('bannerpro.ads.error.product_category_exclusive'))
        return
      }
      saving.value = true
      try {
        const payload = {
          ...form.value,
          active: form.value.active ? 1 : 0,
          type: form.value.type === 'html' ? 'html' : 'image',
          start: formatDate(form.value.start),
          end: formatDate(form.value.end),
          positions: (form.value.positions || []).map((id) => Number(id)),
          product_id: Number(form.value.product_id) || 0,
          category_id: Number(form.value.category_id) || 0,
          max_clicks: Math.max(0, Number(form.value.max_clicks) || 0),
          max_impressions: Math.max(0, Number(form.value.max_impressions) || 0),
          show_hours: buildShowHoursPayload(),
          target_resource_id: Number(form.value.target_resource_id) || 0,
          target_parent_id: Number(form.value.target_parent_id) || 0,
          tags: (form.value.tags || []).map((t) => String(t).trim()).filter(Boolean),
        }
        const isUpdate = !!props.record?.id
        if (isUpdate) {
          payload.id = props.record.id
          await api.adsUpdate(payload)
        } else {
          await api.adsCreate(payload)
        }
        notifySuccess(lex(isUpdate ? 'bannerpro.ads.success.update' : 'bannerpro.ads.success.create'))
        emit('saved')
        emit('update:visible', false)
      } catch (e) {
        notifyError(e.message)
      } finally {
        saving.value = false
      }
    }

    watch(() => props.visible, async (v) => {
      if (!v) return
      activeFormTab.value = 'main'
      await Promise.all([loadPositions(), searchProductOptions(''), searchCategoryOptions(''), searchTagSuggestions('')])
      if (props.record) {
        let showHours = props.record.show_hours_data
        if (!showHours && props.record.show_hours) {
          try { showHours = JSON.parse(props.record.show_hours) } catch { showHours = null }
        }
        form.value = {
          ...emptyForm(),
          ...props.record,
          type: props.record.type === 'html' ? 'html' : 'image',
          active: !!props.record.active,
          positions: (props.record.positions || []).map((id) => Number(id)),
          html: props.record.html || '',
          start: props.record.start ? new Date(props.record.start) : null,
          end: props.record.end ? new Date(props.record.end) : null,
          product_id: Number(props.record.product_id) || 0,
          category_id: Number(props.record.category_id) || 0,
          max_clicks: Number(props.record.max_clicks) || 0,
          max_impressions: Number(props.record.max_impressions) || 0,
          show_hours_weekdays: Array.isArray(showHours?.weekdays) ? showHours.weekdays.map(Number) : [],
          show_hours_hours: Array.isArray(showHours?.hours) ? showHours.hours.map(Number) : [],
          target_resource_id: Number(props.record.target_resource_id) || 0,
          target_parent_id: Number(props.record.target_parent_id) || 0,
          tags: Array.isArray(props.record.tags_list) ? [...props.record.tags_list] : [],
        }
        ensureProductInOptions(form.value.product_id, props.record.product_title)
        ensureCategoryInOptions(form.value.category_id, props.record.category_title)
        if (form.value.target_resource_id > 0) {
          targetResourceSuggestions.value = [{
            label: `#${form.value.target_resource_id} ${props.record.target_resource_title || ''}`.trim(),
            value: form.value.target_resource_id,
          }]
        }
        if (form.value.target_parent_id > 0) {
          targetParentSuggestions.value = [{
            label: `#${form.value.target_parent_id} ${props.record.target_parent_title || ''}`.trim(),
            value: form.value.target_parent_id,
          }]
        }
        preview.value = props.record.current_image || ''
      } else {
        form.value = emptyForm()
        preview.value = ''
      }
    })

    return {
      lex, saving, form, positions, positionOptions, resourceSuggestions, productOptions, productOptionsLoading,
      categoryOptions, categoryOptionsLoading, selectedProductIds, selectedCategoryIds, preview,
      isHtmlType, weekdayOptions, hourOptions, scheduleOnNow,
      isBusinessHoursPreset, isAllHoursPreset,
      hasPageTargeting, targetResourceDisabled, targetParentDisabled,
      targetResourceSuggestions, targetParentSuggestions, tagSuggestions,
      minStartDate, minEndDate, dialogPt: BANNERPRO_DIALOG_PT, dialogContentStyle: BANNERPRO_DIALOG_CONTENT_STYLE,
      searchResources, pickResource, onProductFilter, onCategoryFilter, applyProductUrl, openMediaBrowser, save,
      searchTargetResources, searchTargetParents, onTargetResourceSelect, onTargetParentSelect, clearTargeting,
      applyBusinessHours, applyAllHours, onTagsInput, activeFormTab,
    }
  },
  template: `
    <Dialog :visible="visible" @update:visible="$emit('update:visible', $event)" modal
      class="bannerpro-ad-dialog bannerpro-form-dialog bannerpro-form-dialog--wide"
      :header="record ? lex('bannerpro.ads.update') : lex('bannerpro.ads.new')"
      :style="{ width: 'min(95vw, 52rem)', maxHeight: 'min(92vh, 90vh)' }"
      :contentStyle="dialogContentStyle"
      :pt="dialogPt">
      <Tabs v-model:value="activeFormTab" class="bannerpro-form-tabs">
        <TabList>
          <Tab value="main">{{ lex('bannerpro.form.tab.main') }}</Tab>
          <Tab value="schedule">{{ lex('bannerpro.form.tab.schedule') }}</Tab>
          <Tab value="targeting">{{ lex('bannerpro.form.tab.targeting') }}</Tab>
        </TabList>
        <TabPanels>
          <TabPanel value="main">
            <div class="bannerpro-form-stack">
              <div class="bannerpro-form-section-card">
                <div class="bannerpro-form-row bannerpro-form-row--name">
                  <FloatLabel class="flex-1">
                    <InputText id="bannerpro-ad-name" v-model="form.name" class="w-full" />
                    <label for="bannerpro-ad-name">{{ lex('bannerpro.ads.name') }}</label>
                  </FloatLabel>
                  <div class="bannerpro-form-field bannerpro-form-field--active">
                    <label for="bannerpro-ad-active">{{ lex('bannerpro.ads.active') }}</label>
                    <ToggleSwitch id="bannerpro-ad-active" v-model="form.active" />
                  </div>
                </div>
                <div class="bannerpro-form-field bannerpro-form-field--type">
                  <label for="bannerpro-ad-type">{{ lex('bannerpro.ads.type') }}</label>
                  <div class="bannerpro-type-switch">
                    <span class="bannerpro-type-switch__option"
                      :class="{ 'bannerpro-type-switch__option--active': !isHtmlType }">
                      <i class="pi pi-image" aria-hidden="true" />
                      {{ lex('bannerpro.ads.type.image') }}
                    </span>
                    <ToggleSwitch id="bannerpro-ad-type" v-model="isHtmlType" />
                    <span class="bannerpro-type-switch__option"
                      :class="{ 'bannerpro-type-switch__option--active': isHtmlType }">
                      <i class="pi pi-code" aria-hidden="true" />
                      {{ lex('bannerpro.ads.type.html') }}
                    </span>
                  </div>
                  <small class="bannerpro-field-hint">{{ lex('bannerpro.ads.type.switch_hint') }}</small>
                </div>
                <div class="bannerpro-form-field" v-if="!isHtmlType">
                  <label for="bannerpro-ad-source">{{ lex('bannerpro.ads.source') }}</label>
                  <InputText id="bannerpro-ad-source" v-model="form.source" class="w-full" />
                </div>
              </div>

              <div class="bannerpro-form-section-card">
                <div class="bannerpro-form-section-label">{{ lex('bannerpro.form.section.content') }}</div>
                <template v-if="!isHtmlType">
                  <div class="bannerpro-form-content-grid">
                    <AdPreviewPanel type="image" :image-src="preview" />
                    <div class="bannerpro-image-fields">
                      <div class="bannerpro-form-field">
                        <label>{{ preview ? lex('bannerpro.ads.image.current') : lex('bannerpro.ads.image') }}</label>
                        <div class="flex gap-2">
                          <InputText v-model="form.image" class="flex-1" @input="form.newimage = form.image" />
                          <Button icon="pi pi-folder-open" @click="openMediaBrowser" :title="lex('bannerpro.ads.image')" />
                        </div>
                      </div>
                      <div class="bannerpro-form-field">
                        <label>{{ lex('bannerpro.ads.description') }}</label>
                        <Textarea v-model="form.description" rows="3" class="w-full" />
                      </div>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <div class="bannerpro-form-content-stack bannerpro-form-content-stack--html">
                    <div class="bannerpro-form-field bannerpro-form-field--html-editor">
                      <label for="bannerpro-ad-html">{{ lex('bannerpro.ads.html') }}</label>
                      <Textarea id="bannerpro-ad-html" v-model="form.html" rows="14"
                        class="w-full bannerpro-html-editor" spellcheck="false" />
                    </div>
                    <AdPreviewPanel type="html" layout="stacked" :html="form.html" />
                  </div>
                </template>
              </div>

              <div class="bannerpro-form-section-card">
                <div class="bannerpro-form-section-label">{{ lex('bannerpro.form.section.link') }}</div>
                <div class="bannerpro-form-field">
                  <label for="bannerpro-ad-url">{{ lex('bannerpro.ads.url') }}</label>
                  <div class="bannerpro-url-field">
                    <IconField class="bannerpro-url-field__input flex-1">
                      <InputIcon class="pi pi-link" />
                      <AutoComplete id="bannerpro-ad-url" v-model="form.url" :suggestions="resourceSuggestions"
                        optionLabel="label" :placeholder="lex('bannerpro.ads.url.placeholder')"
                        :emptyMessage="lex('bannerpro.empty_filter')"
                        class="bannerpro-autocomplete-field bannerpro-url-autocomplete w-full" dropdown
                        @complete="searchResources" @item-select="pickResource" />
                    </IconField>
                  </div>
                  <small class="bannerpro-field-hint">{{ lex('bannerpro.ads.url.description') }}</small>
                </div>
                <div class="bannerpro-form-field" v-if="isHtmlType">
                  <label>{{ lex('bannerpro.ads.description') }}</label>
                  <Textarea v-model="form.description" rows="3" class="w-full" />
                </div>
              </div>

              <div class="bannerpro-form-section-card">
                <div class="bannerpro-form-section-label">{{ lex('bannerpro.form.section.positions') }}</div>
                <div class="bannerpro-form-field">
                  <MultiSelect
                    id="bannerpro-ad-positions"
                    v-model="form.positions"
                    :options="positionOptions"
                    optionLabel="label"
                    optionValue="value"
                    :placeholder="lex('bannerpro.positions.select')"
                    :emptyMessage="lex('bannerpro.empty_options')"
                    :emptyFilterMessage="lex('bannerpro.empty_filter')"
                    display="chip"
                    filter
                    showClear
                    appendTo="body"
                    panelClass="bannerpro-multiselect-panel"
                    class="bannerpro-positions-multiselect w-full" />
                </div>
              </div>
            </div>
          </TabPanel>

          <TabPanel value="schedule">
            <div class="bannerpro-form-stack">
              <div class="bannerpro-form-section-card">
                <div class="bannerpro-form-section-label">{{ lex('bannerpro.form.section.dates') }}</div>
                <div class="bannerpro-form-row bannerpro-form-row--dates">
                  <div class="bannerpro-form-field flex-1">
                    <label for="bannerpro-ad-start">{{ lex('bannerpro.ads.start') }}</label>
                    <DatePicker v-model="form.start" showTime hourFormat="24" dateFormat="dd.mm.yy"
                      showIcon iconDisplay="input" fluid
                      :minDate="minStartDate" appendTo="body"
                      panelClass="bannerpro-datepicker-panel"
                      inputId="bannerpro-ad-start"
                      class="bannerpro-datepicker-field" />
                  </div>
                  <div class="bannerpro-form-field flex-1">
                    <label for="bannerpro-ad-end">{{ lex('bannerpro.ads.end') }}</label>
                    <DatePicker v-model="form.end" showTime hourFormat="24" dateFormat="dd.mm.yy"
                      showIcon iconDisplay="input" fluid
                      :minDate="minEndDate" appendTo="body"
                      panelClass="bannerpro-datepicker-panel"
                      inputId="bannerpro-ad-end"
                      class="bannerpro-datepicker-field" />
                  </div>
                </div>
              </div>

              <div class="bannerpro-form-section-card">
                <div class="bannerpro-form-section-label">{{ lex('bannerpro.form.section.schedule') }}</div>
                <div class="bannerpro-form-field bannerpro-form-field--schedule">
                  <label>{{ lex('bannerpro.ads.show_hours') }}</label>
                  <div class="bannerpro-schedule-presets" role="group" :aria-label="lex('bannerpro.ads.show_hours')">
                    <button type="button" class="bannerpro-schedule-preset"
                      :class="{ 'bannerpro-schedule-preset--active': isBusinessHoursPreset }"
                      @click="applyBusinessHours">
                      {{ lex('bannerpro.ads.show_hours_preset_business') }}
                    </button>
                    <button type="button" class="bannerpro-schedule-preset"
                      :class="{ 'bannerpro-schedule-preset--active': isAllHoursPreset }"
                      @click="applyAllHours">
                      {{ lex('bannerpro.ads.show_hours_preset_all') }}
                    </button>
                  </div>
                  <div class="bannerpro-form-row">
                    <div class="bannerpro-form-field flex-1">
                      <label>{{ lex('bannerpro.ads.show_hours_weekdays') }}</label>
                      <MultiSelect v-model="form.show_hours_weekdays" :options="weekdayOptions"
                        optionLabel="label" optionValue="value" display="chip" showClear filter
                        appendTo="body" panelClass="bannerpro-multiselect-panel" class="w-full" />
                    </div>
                    <div class="bannerpro-form-field flex-1">
                      <label>{{ lex('bannerpro.ads.show_hours_hours') }}</label>
                      <MultiSelect v-model="form.show_hours_hours" :options="hourOptions"
                        optionLabel="label" optionValue="value" display="chip" showClear filter
                        appendTo="body" panelClass="bannerpro-multiselect-panel" class="w-full" />
                    </div>
                  </div>
                  <small class="bannerpro-field-hint">{{ lex('bannerpro.ads.show_hours_hint') }}</small>
                </div>
                <div class="bannerpro-schedule-status"
                  :class="scheduleOnNow ? 'bannerpro-schedule-status--active' : 'bannerpro-schedule-status--inactive'">
                  <i class="pi" :class="scheduleOnNow ? 'pi-check-circle' : 'pi-clock'" aria-hidden="true" />
                  <div class="bannerpro-schedule-status__body">
                    <span class="bannerpro-schedule-status__title">
                      {{ lex(scheduleOnNow ? 'bannerpro.ads.show_hours_now_yes' : 'bannerpro.ads.show_hours_now_no') }}
                    </span>
                    <span class="bannerpro-schedule-status__hint">{{ lex('bannerpro.ads.show_hours_tz_hint') }}</span>
                  </div>
                </div>
              </div>

              <div class="bannerpro-form-section-card">
                <div class="bannerpro-form-section-label">{{ lex('bannerpro.form.section.limits') }}</div>
                <div class="bannerpro-form-row bannerpro-form-row--limits">
                  <div class="bannerpro-form-field flex-1">
                    <label>{{ lex('bannerpro.ads.max_clicks') }}</label>
                    <InputNumber v-model="form.max_clicks" :min="0" class="w-full" />
                    <small class="bannerpro-field-hint">{{ lex('bannerpro.ads.limit_hint') }}</small>
                  </div>
                  <div class="bannerpro-form-field flex-1">
                    <label>{{ lex('bannerpro.ads.max_impressions') }}</label>
                    <InputNumber v-model="form.max_impressions" :min="0" class="w-full" />
                    <small class="bannerpro-field-hint">{{ lex('bannerpro.ads.limit_hint') }}</small>
                  </div>
                </div>
              </div>
            </div>
          </TabPanel>

          <TabPanel value="targeting">
            <div class="bannerpro-form-stack">
              <div class="bannerpro-form-section-card">
                <div class="bannerpro-form-section-label">{{ lex('bannerpro.form.section.page_target') }}</div>
                <div class="bannerpro-targeting-hint">
                  <i class="pi pi-info-circle" aria-hidden="true" />
                  <span>{{ lex('bannerpro.ads.target_mutual_hint') }}</span>
                </div>
                <div class="bannerpro-form-row bannerpro-form-row--targeting">
                  <div class="bannerpro-form-field flex-1"
                    :class="{ 'bannerpro-form-field--muted': targetResourceDisabled }">
                    <label>{{ lex('bannerpro.ads.target_resource') }}</label>
                    <AutoComplete :modelValue="form.target_resource_id > 0 ? targetResourceSuggestions[0] : null"
                      :suggestions="targetResourceSuggestions" optionLabel="label"
                      :placeholder="lex('bannerpro.ads.target_resource.placeholder')"
                      :disabled="targetResourceDisabled"
                      class="bannerpro-autocomplete-field w-full" dropdown forceSelection
                      appendTo="body" panelClass="bannerpro-autocomplete-panel"
                      @complete="searchTargetResources" @item-select="onTargetResourceSelect"
                      @update:modelValue="(v) => { if (!v) form.target_resource_id = 0 }" />
                  </div>
                  <div class="bannerpro-form-field flex-1"
                    :class="{ 'bannerpro-form-field--muted': targetParentDisabled }">
                    <label>{{ lex('bannerpro.ads.target_parent') }}</label>
                    <AutoComplete :modelValue="form.target_parent_id > 0 ? targetParentSuggestions[0] : null"
                      :suggestions="targetParentSuggestions" optionLabel="label"
                      :placeholder="lex('bannerpro.ads.target_parent.placeholder')"
                      :disabled="targetParentDisabled"
                      class="bannerpro-autocomplete-field w-full" dropdown forceSelection
                      appendTo="body" panelClass="bannerpro-autocomplete-panel"
                      @complete="searchTargetParents" @item-select="onTargetParentSelect"
                      @update:modelValue="(v) => { if (!v) form.target_parent_id = 0 }" />
                  </div>
                </div>
                <button v-if="hasPageTargeting" type="button" class="bannerpro-target-clear"
                  @click="clearTargeting">
                  <i class="pi pi-times" aria-hidden="true" />
                  {{ lex('bannerpro.ads.target_clear') }}
                </button>
              </div>

              <div class="bannerpro-form-section-card">
                <div class="bannerpro-form-section-label">{{ lex('bannerpro.form.section.tags') }}</div>
                <div class="bannerpro-form-field">
                  <Chips v-model="form.tags" :placeholder="lex('bannerpro.ads.tags.placeholder')"
                    class="bannerpro-tags-chips w-full" @add="onTagsInput" @focus="onTagsInput" />
                  <small class="bannerpro-field-hint">{{ lex('bannerpro.ads.tags.hint') }}</small>
                </div>
              </div>

              <div class="bannerpro-form-section-card">
                <div class="bannerpro-form-section-label">{{ lex('bannerpro.form.section.minishop') }}</div>
                <div class="bannerpro-form-field bannerpro-form-field--product">
                  <label for="bannerpro-ad-product">{{ lex('bannerpro.ads.product') }}</label>
                  <div class="bannerpro-minishop-field">
                    <MultiSelect
                      id="bannerpro-ad-product"
                      v-model="selectedProductIds"
                      :options="productOptions"
                      optionLabel="pagetitle"
                      optionValue="id"
                      :placeholder="lex('bannerpro.ads.product.placeholder')"
                      :emptyMessage="lex('bannerpro.empty_options')"
                      :emptyFilterMessage="lex('bannerpro.empty_filter')"
                      :selectionLimit="1"
                      :showToggleAll="false"
                      display="chip"
                      filter
                      showClear
                      :loading="productOptionsLoading"
                      scrollHeight="20rem"
                      appendTo="self"
                      panelClass="bannerpro-product-multiselect-panel"
                      class="bannerpro-minishop-multiselect flex-1 w-full"
                      @filter="onProductFilter"
                      @show="searchProductOptions('')" />
                    <Button v-if="form.product_id" icon="pi pi-link" text rounded
                      class="bannerpro-minishop-field__action"
                      @click="applyProductUrl" :title="lex('bannerpro.ads.product.apply_url')" />
                  </div>
                </div>
                <div class="bannerpro-form-field bannerpro-form-field--category">
                  <label for="bannerpro-ad-category">{{ lex('bannerpro.ads.category') }}</label>
                  <MultiSelect
                    id="bannerpro-ad-category"
                    v-model="selectedCategoryIds"
                    :options="categoryOptions"
                    optionLabel="pagetitle"
                    optionValue="id"
                    :placeholder="lex('bannerpro.ads.category.placeholder')"
                    :emptyMessage="lex('bannerpro.empty_options')"
                    :emptyFilterMessage="lex('bannerpro.empty_filter')"
                    :selectionLimit="1"
                    :showToggleAll="false"
                    display="chip"
                    filter
                    showClear
                    :loading="categoryOptionsLoading"
                    scrollHeight="20rem"
                    appendTo="self"
                    panelClass="bannerpro-product-multiselect-panel"
                    class="bannerpro-minishop-multiselect w-full"
                    @filter="onCategoryFilter"
                    @show="searchCategoryOptions('')" />
                </div>
              </div>
            </div>
          </TabPanel>
        </TabPanels>
      </Tabs>
      <template #footer>
        <Button :label="lex('bannerpro.cancel')" text @click="$emit('update:visible', false)" />
        <Button :label="lex('bannerpro.save')" :loading="saving" @click="save" />
      </template>
    </Dialog>
  `,
})
