import { defineComponent, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { lex } from '../request.js'
import { chartPrimaryColor, chartSecondaryColor, chartTertiaryColor } from '../theme/chartColors.js'
import {
  Chart,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from 'chart.js'

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend)

function formatRate(value) {
  if (value == null || Number.isNaN(Number(value))) {
    return '—'
  }
  return `${Number(value)}%`
}

export default defineComponent({
  name: 'FunnelChart',
  props: {
    impressions: { type: Number, default: 0 },
    clicks: { type: Number, default: 0 },
    conversions: { type: Number, default: 0 },
    ctr: { type: Number, default: null },
    cvr: { type: Number, default: null },
    loading: { type: Boolean, default: false },
  },
  setup(props) {
    const canvas = ref(null)
    let chart = null

    async function render() {
      await nextTick()
      if (!canvas.value || props.loading) return

      const impressions = Math.max(0, Number(props.impressions) || 0)
      const clicks = Math.max(0, Number(props.clicks) || 0)
      const conversions = Math.max(0, Number(props.conversions) || 0)
      const peak = Math.max(1, impressions, clicks, conversions)

      if (chart) chart.destroy()
      chart = new Chart(canvas.value, {
        type: 'bar',
        data: {
          labels: [
            lex('bannerpro.stats.impressions'),
            lex('bannerpro.ads.clicks'),
            lex('bannerpro.ads.conversions'),
          ],
          datasets: [{
            data: [impressions, clicks, conversions],
            backgroundColor: [
              chartSecondaryColor(0.85),
              chartPrimaryColor(0.85),
              chartTertiaryColor(0.85),
            ],
            borderColor: [
              chartSecondaryColor(1),
              chartPrimaryColor(1),
              chartTertiaryColor(1),
            ],
            borderWidth: 1,
            borderRadius: 4,
          }],
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label(context) {
                  return `${context.formattedValue}`
                },
              },
            },
          },
          scales: {
            x: {
              beginAtZero: true,
              suggestedMax: peak,
            },
          },
        },
      })
    }

    watch(
      () => [props.impressions, props.clicks, props.conversions, props.loading],
      render,
      { flush: 'post' },
    )
    onMounted(render)
    onBeforeUnmount(() => chart?.destroy())

    return { canvas, lex, formatRate }
  },
  template: `
    <div class="bannerpro-funnel-chart">
      <div class="bannerpro-funnel-chart__header">
        <span class="font-medium">{{ lex('bannerpro.stats.funnel') }}</span>
        <span class="text-sm text-color-secondary">
          {{ lex('bannerpro.stats.funnel.hint') }}
        </span>
      </div>
      <div class="bannerpro-chart-wrap bannerpro-funnel-chart__canvas">
        <canvas ref="canvas"></canvas>
      </div>
      <div class="bannerpro-funnel-chart__rates text-sm text-color-secondary">
        <span>{{ lex('bannerpro.stats.ctr') }}: {{ formatRate(ctr) }}</span>
        <span>{{ lex('bannerpro.stats.cvr') }}: {{ formatRate(cvr) }}</span>
      </div>
    </div>
  `,
})
