import { defineComponent } from 'vue'
import Image from 'primevue/image'

export default defineComponent({
  name: 'ThumbImage',
  components: { Image },
  props: {
    src: { type: String, default: '' },
    alt: { type: String, default: '' },
  },
  template: `
    <Image v-if="src" :src="src" :alt="alt" preview class="bannerpro-thumb-image" imageClass="bannerpro-thumb" />
    <span v-else class="text-color-secondary text-sm">—</span>
  `,
})
