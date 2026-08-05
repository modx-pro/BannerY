import { defineComponent } from 'vue'

export default defineComponent({
  name: 'PageSection',
  props: {
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    panel: { type: Boolean, default: true },
  },
  template: `
    <section class="bannerpro-page-section">
      <div v-if="title || subtitle || $slots.actions"
        class="bannerpro-page-section__header flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
          <h3 v-if="title" class="bannerpro-page-section__title m-0">{{ title }}</h3>
          <p v-if="subtitle" class="bannerpro-page-section__subtitle m-0 mt-1">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap gap-2">
          <slot name="actions" />
        </div>
      </div>
      <div :class="panel ? 'bannerpro-page-section__panel' : ''">
        <slot />
      </div>
    </section>
  `,
})
