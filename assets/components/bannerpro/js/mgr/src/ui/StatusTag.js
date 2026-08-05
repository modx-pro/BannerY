import { defineComponent, computed } from 'vue'
import Tag from 'primevue/tag'
import { lex } from '../request.js'

export default defineComponent({
  name: 'StatusTag',
  components: { Tag },
  props: {
    active: { type: Boolean, default: false },
    type: { type: String, default: '' },
  },
  setup(props) {
    const activeTag = computed(() => ({
      severity: props.active ? 'success' : 'danger',
      label: props.active ? lex('yes') : lex('no'),
    }))
    const typeTag = computed(() => {
      if (props.type === 'html') {
        return { severity: 'info', label: lex('bannerpro.ads.type.html') }
      }
      if (props.type === 'image') {
        return { severity: 'secondary', label: lex('bannerpro.ads.type.image') }
      }
      return null
    })
    return { activeTag, typeTag }
  },
  template: `
    <Tag v-if="type && typeTag" :severity="typeTag.severity" :value="typeTag.label" rounded />
    <Tag v-else :severity="activeTag.severity" :value="activeTag.label" rounded />
  `,
})
