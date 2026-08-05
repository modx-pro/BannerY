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
  name: 'CompareLineChart',
  props: {
    labels: { type: Array, default: () => [] },
    clicks: { type: Array, default: () => [] },
    impressions: { type: Array, default: () => [] },
    compareClicks: { type: Array, default: () => [] },
    compareImpressions: { type: Array, default: () => [] },
    conversions: { type: Array, default: () => [] },
    compareConversions: { type: Array, default: () => [] },
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
      const compareClicks = props.compareClicks.map((value) => Number(value) || 0)
      const compareImpressions = props.compareImpressions.map((value) => Number(value) || 0)
      const conversions = props.conversions.map((value) => Number(value) || 0)
      const compareConversions = props.compareConversions.map((value) => Number(value) || 0)
      const peak = Math.max(
        1,
        ...clicks,
        ...impressions,
        ...compareClicks,
        ...compareImpressions,
        ...conversions,
        ...compareConversions,
      )

      if (chart) chart.destroy()
      chart = new Chart(canvas.value, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: lex('bannerpro.ads.clicks') + ' (' + lex('bannerpro.stats.this_week') + ')',
              data: clicks,
              borderColor: chartPrimaryColor(1),
              backgroundColor: chartPrimaryColor(0.15),
              tension: 0.2,
            },
            {
              label: lex('bannerpro.ads.clicks') + ' (' + lex('bannerpro.stats.last_week') + ')',
              data: compareClicks,
              borderColor: chartPrimaryColor(0.6),
              backgroundColor: chartPrimaryColor(0.05),
              borderDash: [6, 4],
              tension: 0.2,
            },
            {
              label: lex('bannerpro.stats.impressions') + ' (' + lex('bannerpro.stats.this_week') + ')',
              data: impressions,
              borderColor: chartSecondaryColor(1),
              backgroundColor: chartSecondaryColor(0.15),
              tension: 0.2,
            },
            {
              label: lex('bannerpro.stats.impressions') + ' (' + lex('bannerpro.stats.last_week') + ')',
              data: compareImpressions,
              borderColor: chartSecondaryColor(0.6),
              backgroundColor: chartSecondaryColor(0.05),
              borderDash: [6, 4],
              tension: 0.2,
            },
            {
              label: lex('bannerpro.ads.conversions') + ' (' + lex('bannerpro.stats.this_week') + ')',
              data: conversions,
              borderColor: chartTertiaryColor(1),
              backgroundColor: chartTertiaryColor(0.15),
              tension: 0.2,
            },
            {
              label: lex('bannerpro.ads.conversions') + ' (' + lex('bannerpro.stats.last_week') + ')',
              data: compareConversions,
              borderColor: chartTertiaryColor(0.6),
              backgroundColor: chartTertiaryColor(0.05),
              borderDash: [6, 4],
              tension: 0.2,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'top' } },
          scales: {
            x: { ticks: { autoSkip: false } },
            y: {
              beginAtZero: true,
              suggestedMax: peak,
            },
          },
        },
      })
    }

    watch(
      () => [
        props.labels,
        props.clicks,
        props.impressions,
        props.compareClicks,
        props.compareImpressions,
        props.conversions,
        props.compareConversions,
      ],
      render,
      { deep: true, flush: 'post' },
    )
    onMounted(render)
    onBeforeUnmount(() => chart?.destroy())

    return { canvas, lex }
  },
  template: `
    <div class="bannerpro-chart-panel">
      <div class="bannerpro-chart-panel__header">
        <h4 class="bannerpro-chart-panel__title">{{ lex('bannerpro.stats.compare_last_week') }}</h4>
      </div>
      <div class="bannerpro-chart-panel__canvas bannerpro-chart-wrap">
        <canvas ref="canvas"></canvas>
      </div>
    </div>
  `,
})
