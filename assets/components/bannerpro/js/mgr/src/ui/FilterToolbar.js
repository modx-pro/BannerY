import { defineComponent } from 'vue'
import Toolbar from 'primevue/toolbar'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

export default defineComponent({
  name: 'FilterToolbar',
  components: { Toolbar, IconField, InputIcon, InputText, Button },
  props: {
    search: { type: String, default: '' },
    searchPlaceholder: { type: String, default: '' },
    clearTitle: { type: String, default: '' },
    showSearch: { type: Boolean, default: true },
  },
  emits: ['update:search', 'search', 'clear'],
  setup(props, { emit }) {
    function onInput(value) {
      emit('update:search', value)
    }
    function onEnter() {
      emit('search')
    }
    function onClear() {
      emit('update:search', '')
      emit('clear')
    }
    return { onInput, onEnter, onClear }
  },
  template: `
    <Toolbar class="bannerpro-filter-toolbar mb-3 border-round">
      <template #start>
        <div class="flex flex-wrap align-items-center gap-2">
          <slot name="start" />
        </div>
      </template>
      <template #end>
        <div class="flex flex-wrap align-items-center gap-2">
          <slot name="filters" />
          <IconField v-if="showSearch">
            <InputIcon class="pi pi-search" />
            <InputText
              :modelValue="search"
              :placeholder="searchPlaceholder"
              class="bannerpro-filter-search"
              @update:modelValue="onInput"
              @keyup.enter="onEnter" />
          </IconField>
          <Button icon="pi pi-filter-slash" severity="secondary" text rounded
            :title="clearTitle || searchPlaceholder" @click="onClear" />
        </div>
      </template>
    </Toolbar>
  `,
})
