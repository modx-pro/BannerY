import { defineComponent, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { lex } from '../request.js'
import { chartPrimaryColor, chartSecondaryColor, chartTertiaryColor } from '../theme/chartColors.js'
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from 'chart.js'

Chart.register(LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend)

export default defineComponent({
  name: 'StatsLineChart',
  props: {
    labels: { type: Array, default: () => [] },
    clicks: { type: Array, default: () => [] },
    impressions: { type: Array, default: () => [] },
    conversions: { type: Array, default: () => [] },
  },
  setup(props) {
    const canvas = ref(null)
    let chart = null

    async function render() {
      await nextTick()
      if (!canvas.value) return

      const labels = [...props.labels]
      const clicks = props.clicks.map((value) => Number(value) || 0)
      const impressions = props.impressions.map((value) => Number(value) || 0)
      const conversions = props.conversions.map((value) => Number(value) || 0)
      const peak = Math.max(1, ...clicks, ...impressions, ...conversions)

      if (chart) chart.destroy()
      chart = new Chart(canvas.value, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: lex('bannerpro.ads.clicks'),
              data: clicks,
              borderColor: chartPrimaryColor(1),
              backgroundColor: chartPrimaryColor(0.15),
              tension: 0.2,
            },
            {
              label: lex('bannerpro.stats.impressions'),
              data: impressions,
              borderColor: chartSecondaryColor(1),
              backgroundColor: chartSecondaryColor(0.15),
              tension: 0.2,
            },
            {
              label: lex('bannerpro.ads.conversions'),
              data: conversions,
              borderColor: chartTertiaryColor(1),
              backgroundColor: chartTertiaryColor(0.15),
              tension: 0.2,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'top' } },
          scales: {
            x: {
              ticks: {
                autoSkip: true,
                maxTicksLimit: labels.length > 20 ? 12 : labels.length > 10 ? 15 : undefined,
              },
            },
            y: {
              beginAtZero: true,
              suggestedMax: peak,
            },
          },
        },
      })
    }

    watch(() => [props.labels, props.clicks, props.impressions, props.conversions], render, { deep: true, flush: 'post' })
    onMounted(render)
    onBeforeUnmount(() => chart?.destroy())

    return { canvas, lex }
  },
  template: `
    <div class="bannerpro-chart-panel">
      <div class="bannerpro-chart-panel__header">
        <h4 class="bannerpro-chart-panel__title">{{ lex('bannerpro.stats.export.section.by_day') }}</h4>
      </div>
      <div class="bannerpro-chart-panel__canvas bannerpro-chart-wrap">
        <canvas ref="canvas"></canvas>
      </div>
    </div>
  `,
})
