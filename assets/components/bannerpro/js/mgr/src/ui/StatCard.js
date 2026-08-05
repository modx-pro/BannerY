import { defineComponent } from 'vue'
import Card from 'primevue/card'
import Skeleton from 'primevue/skeleton'

export default defineComponent({
  name: 'StatCard',
  components: { Card, Skeleton },
  props: {
    label: { type: String, default: '' },
    value: { type: [String, Number], default: '—' },
    icon: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    accent: { type: Boolean, default: false },
  },
  template: `
    <Card class="bannerpro-stat-card h-full" :class="{ 'bannerpro-stat-card--accent': accent }">
      <template #content>
        <div class="flex align-items-start justify-content-between gap-3">
          <div class="flex-1 min-w-0">
            <div class="bannerpro-stat-card__label">{{ label }}</div>
            <Skeleton v-if="loading" width="4rem" height="2rem" />
            <div v-else
              class="bannerpro-stat-card__value"
              :class="{ 'bannerpro-stat-card__value--accent': accent }">
              {{ value }}
            </div>
            <div v-if="$slots.default" class="mt-2">
              <slot />
            </div>
          </div>
          <div v-if="icon" class="bannerpro-stat-card__icon-wrap" aria-hidden="true">
            <i :class="icon" class="bannerpro-stat-card__icon" />
          </div>
        </div>
      </template>
    </Card>
  `,
})
