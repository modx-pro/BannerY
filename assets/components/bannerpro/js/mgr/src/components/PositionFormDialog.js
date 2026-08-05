import { computed, defineComponent, ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Fieldset from 'primevue/fieldset'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import { getConfig, lex } from '../request.js'
import AdPositionsList from './AdPositionsList.js'
import { BANNERPRO_DIALOG_CONTENT_STYLE, BANNERPRO_DIALOG_PT } from '../dialogUi.js'

export default defineComponent({
  name: 'PositionFormDialog',
  components: { Dialog, InputText, Button, AdPositionsList, Fieldset },
  props: {
    visible: Boolean,
    record: { type: Object, default: null },
  },
  emits: ['update:visible', 'saved'],
  setup(props, { emit }) {
    const api = useBannerProApi()
    const { notifySuccess, notifyError } = useBannerProNotify()
    const saving = ref(false)
    const name = ref('')
    const contextKey = ref('')
    const canSave = computed(() => !!getConfig().permissions?.save)
    const canRemove = computed(() => !!getConfig().permissions?.remove)

    async function save() {
      saving.value = true
      try {
        const isUpdate = !!props.record?.id
        if (isUpdate) {
          await api.positionsUpdate({ id: props.record.id, name: name.value, context_key: contextKey.value })
        } else {
          await api.positionsCreate({ name: name.value, context_key: contextKey.value })
        }
        notifySuccess(lex(isUpdate ? 'bannerpro.positions.success.update' : 'bannerpro.positions.success.create'))
        emit('saved')
        emit('update:visible', false)
      } catch (e) {
        notifyError(e.message)
      } finally {
        saving.value = false
      }
    }

    watch(() => props.visible, (v) => {
      if (!v) return
      name.value = props.record?.name || ''
      contextKey.value = props.record?.context_key || ''
    })

    return {
      lex, saving, name, contextKey, save, canSave, canRemove, positionId: () => props.record?.id || 0,
      dialogPt: BANNERPRO_DIALOG_PT, dialogContentStyle: BANNERPRO_DIALOG_CONTENT_STYLE,
    }
  },
  template: `
    <Dialog :visible="visible" @update:visible="$emit('update:visible', $event)" modal
      class="bannerpro-position-dialog bannerpro-form-dialog"
      :header="record ? lex('bannerpro.positions.update') : lex('bannerpro.positions.new')"
      :style="{ width: 'min(95vw, 48rem)', maxHeight: 'min(92vh, 44rem)' }"
      :contentStyle="dialogContentStyle"
      :pt="dialogPt">
      <Fieldset :legend="lex('bannerpro.form.section.general')" toggleable>
        <div class="bannerpro-form-stack">
          <div class="bannerpro-form-field">
            <label for="bannerpro-position-name">{{ lex('bannerpro.positions.name') }}</label>
            <InputText id="bannerpro-position-name" v-model="name" class="w-full" />
          </div>
          <div class="bannerpro-form-field">
            <label for="bannerpro-position-context">{{ lex('bannerpro.positions.context_key') }}</label>
            <InputText id="bannerpro-position-context" v-model="contextKey" class="w-full"
              :placeholder="lex('bannerpro.positions.context_key.placeholder')" />
            <small class="bannerpro-field-hint">{{ lex('bannerpro.positions.context_key.hint') }}</small>
          </div>
        </div>
      </Fieldset>
      <AdPositionsList v-if="record?.id" :position-id="record.id" :can-save="canSave" :can-remove="canRemove" />
      <template #footer>
        <Button :label="lex('bannerpro.cancel')" text @click="$emit('update:visible', false)" />
        <Button v-if="canSave" :label="lex('bannerpro.save')" :loading="saving" @click="save" />
      </template>
    </Dialog>
  `,
})
