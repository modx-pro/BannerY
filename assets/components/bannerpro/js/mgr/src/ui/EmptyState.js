import { defineComponent } from 'vue'



export default defineComponent({

  name: 'EmptyState',

  props: {

    title: { type: String, default: '' },

    detail: { type: String, default: '' },

    icon: { type: String, default: 'pi pi-inbox' },

  },

  template: `

    <div class="bannerpro-empty-state">

      <div class="bannerpro-empty-state__icon-wrap">

        <i :class="[icon, 'bannerpro-empty-state__icon']" aria-hidden="true" />

      </div>

      <p v-if="title" class="bannerpro-empty-state__title">{{ title }}</p>

      <p v-if="detail" class="bannerpro-empty-state__detail">{{ detail }}</p>

      <div v-if="$slots.default" class="mt-3">

        <slot />

      </div>

    </div>

  `,

})

