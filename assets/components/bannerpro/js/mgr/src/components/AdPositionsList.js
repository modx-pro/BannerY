import { defineComponent, onMounted, ref, watch } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Select from 'primevue/select'
import InputNumber from 'primevue/inputnumber'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import { lex } from '../request.js'

export default defineComponent({
  name: 'AdPositionsList',
  components: { DataTable, Column, Button, Select, InputNumber },
  props: {
    positionId: { type: Number, required: true },
    canSave: { type: Boolean, default: true },
    canRemove: { type: Boolean, default: true },
  },
  setup(props) {
    const api = useBannerProApi()
    const { notifySuccess, notifyError } = useBannerProNotify()
    const rows = ref([])
    const adOptions = ref([])
    const selectedAd = ref(null)
    const loading = ref(false)

    async function load() {
      if (!props.positionId) return
      loading.value = true
      try {
        const res = await api.adpositionsGetList({ position: props.positionId, start: 0, limit: 50 })
        rows.value = res.data || []
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    async function loadAdOptions() {
      const res = await api.adsGetList({
        start: 0, limit: 0, position: props.positionId, mode: 'exclude',
      })
      adOptions.value = (res.data || []).map((a) => ({ label: a.name, value: a.id }))
    }

    async function addAd() {
      if (!selectedAd.value) return
      try {
        await api.adpositionsAdd({ position: props.positionId, ad: selectedAd.value })
        selectedAd.value = null
        notifySuccess(lex('bannerpro.adposition.success.add'))
        await Promise.all([load(), loadAdOptions()])
      } catch (e) {
        notifyError(e.message)
      }
    }

    async function removeRow(row) {
      try {
        await api.adpositionsRemove(row.id)
        notifySuccess(lex('bannerpro.adposition.success.remove'))
        await Promise.all([load(), loadAdOptions()])
      } catch (e) {
        notifyError(e.message)
      }
    }

    async function saveWeight(row) {
      const weight = Math.max(1, Number(row.weight) || 1)
      row.weight = weight
      try {
        await api.adpositionsUpdateWeight({ id: row.id, weight })
        notifySuccess(lex('bannerpro.adposition.success.weight'))
      } catch (e) {
        notifyError(e.message)
        await load()
      }
    }

    async function onRowReorder(event) {
      const { dragIndex, dropIndex, value } = event
      const row = value[dropIndex]
      if (!row?.id) return
      try {
        await api.adpositionsSort({
          id: row.id,
          new_order: dropIndex,
          old_order: dragIndex,
        })
        notifySuccess(lex('bannerpro.adposition.success.sort'))
        await load()
      } catch (e) {
        notifyError(e.message)
        await load()
      }
    }

    watch(() => props.positionId, async () => {
      await Promise.all([load(), loadAdOptions()])
    })

    onMounted(async () => {
      await Promise.all([load(), loadAdOptions()])
    })

    return {
      lex, rows, adOptions, selectedAd, loading, load, addAd, removeRow, saveWeight, onRowReorder,
    }
  },
  template: `
    <div class="flex flex-column gap-2">
      <template v-if="canSave">
        <label>{{ lex('bannerpro.ads.add') }}</label>
        <div class="flex gap-2">
          <Select v-model="selectedAd" :options="adOptions" optionLabel="label" optionValue="value"
            :placeholder="lex('bannerpro.ads.add')" :emptyMessage="lex('bannerpro.empty_options')" class="flex-1" />
          <Button icon="pi pi-plus" @click="addAd" />
        </div>
      </template>
      <div class="bannerpro-datatable-shell">
      <DataTable :value="rows" :loading="loading" size="small" :reorderableRows="canSave" @row-reorder="onRowReorder">
        <Column v-if="canSave" rowReorder headerStyle="width:3rem" />
        <Column field="idx" :header="lex('bannerpro.adposition.idx')" style="width:4rem" />
        <Column field="name" :header="lex('bannerpro.ads.name')" />
        <Column :header="lex('bannerpro.adposition.weight')" style="width:6rem">
          <template #body="{ data }">
            <InputNumber v-if="canSave" v-model="data.weight" :min="1" inputClass="w-full" @blur="saveWeight(data)" />
            <span v-else>{{ data.weight }}</span>
          </template>
        </Column>
        <Column :header="lex('bannerpro.ads.image')" style="width:5.5rem" bodyClass="bannerpro-col-thumb">
          <template #body="{ data }">
            <span v-if="data.image" class="bannerpro-thumb-wrap">
              <img :src="data.image" class="bannerpro-thumb" alt="" loading="lazy" />
            </span>
          </template>
        </Column>
        <Column v-if="canRemove" header="" style="width:4rem" bodyClass="bannerpro-col-actions">
          <template #body="{ data }">
            <div class="bannerpro-row-actions">
              <Button icon="pi pi-times" text size="small" severity="danger" @click="removeRow(data)" />
            </div>
          </template>
        </Column>
      </DataTable>
      </div>
    </div>
  `,
})
