import { defineComponent, ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Skeleton from 'primevue/skeleton'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import { lex } from '../request.js'
import { BANNERPRO_DIALOG_CONTENT_STYLE, BANNERPRO_DIALOG_PT } from '../dialogUi.js'

export default defineComponent({
  name: 'TemplatePicker',
  components: { Dialog, Button, Card, Tag, Skeleton },
  props: {
    visible: Boolean,
  },
  emits: ['update:visible', 'selected'],
  setup(props, { emit }) {
    const api = useBannerProApi()
    const { notifyError } = useBannerProNotify()
    const rows = ref([])
    const loading = ref(false)
    const selected = ref(null)

    async function loadTemplates() {
      loading.value = true
      try {
        const res = await api.adTemplatesGetList()
        rows.value = res.data || []
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    function close() {
      emit('update:visible', false)
      selected.value = null
    }

    function pickTemplate(template) {
      selected.value = template
    }

    function confirm() {
      if (!selected.value) return
      emit('selected', selected.value)
      close()
    }

    watch(() => props.visible, (visible) => {
      if (visible) {
        loadTemplates()
      }
    })

    function isSelected(row) {
      return selected.value != null && Number(selected.value.id) === Number(row.id)
    }

    return {
      lex, rows, loading, selected, close, confirm, pickTemplate, isSelected,
      dialogContentStyle: BANNERPRO_DIALOG_CONTENT_STYLE,
      dialogPt: BANNERPRO_DIALOG_PT,
    }
  },
  template: `
    <Dialog :visible="visible" :header="lex('bannerpro.templates.title')" modal
      class="bannerpro-template-picker bannerpro-form-dialog"
      :style="{ width: 'min(95vw, 48rem)' }"
      :contentStyle="dialogContentStyle" :pt="dialogPt"
      @update:visible="(v) => !v && close()">
      <div v-if="loading" class="bannerpro-template-grid">
        <Skeleton v-for="n in 6" :key="n" height="6rem" class="border-round" />
      </div>
      <div v-else class="bannerpro-template-grid">
        <Card v-for="row in rows" :key="row.id"
          class="bannerpro-template-card"
          :class="{ 'bannerpro-template-card--selected': isSelected(row) }"
          @click="pickTemplate(row)"
          @dblclick="pickTemplate(row); confirm()">
          <template #title>
            <div class="flex align-items-center justify-content-between gap-2">
              <div class="flex align-items-center gap-2 min-w-0">
                <i v-if="isSelected(row)" class="pi pi-check-circle text-primary flex-shrink-0" aria-hidden="true" />
                <span class="font-semibold">{{ row.name }}</span>
              </div>
              <Tag :value="row.type === 'html' ? lex('bannerpro.ads.type.html') : lex('bannerpro.ads.type.image')"
                :severity="row.type === 'html' ? 'info' : 'secondary'" rounded />
            </div>
          </template>
          <template #content>
            <div class="text-sm text-color-secondary">{{ row.tpl || '—' }}</div>
          </template>
        </Card>
      </div>
      <template #footer>
        <Button :label="lex('bannerpro.cancel')" severity="secondary" text @click="close" />
        <Button :label="lex('bannerpro.templates.select')" icon="pi pi-check" :disabled="!selected" @click="confirm" />
      </template>
    </Dialog>
  `,
})
