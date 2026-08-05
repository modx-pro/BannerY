import { defineComponent, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js'
import { lex } from '../request.js'
import { chartPrimaryColor } from '../theme/chartColors.js'

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend)

export default defineComponent({
  name: 'ClicksChart',
  props: {
    labels: { type: Array, default: () => [] },
    values: { type: Array, default: () => [] },
  },
  setup(props) {
    const canvas = ref(null)
    let chart = null

    function render() {
      if (!canvas.value) return
      if (chart) chart.destroy()
      chart = new Chart(canvas.value, {
        type: 'bar',
        data: {
          labels: props.labels,
          datasets: [{
            label: lex('bannerpro.ads.clicks'),
            data: props.values,
            backgroundColor: chartPrimaryColor(0.6),
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
        },
      })
    }

    watch(() => [props.labels, props.values], render, { deep: true })
    onMounted(render)
    onBeforeUnmount(() => chart?.destroy())

    return { canvas, lex }
  },
  template: `
    <div class="bannerpro-chart-panel">
      <div class="bannerpro-chart-panel__header">
        <h4 class="bannerpro-chart-panel__title">{{ lex('bannerpro.stats.clicks') }}</h4>
      </div>
      <div class="bannerpro-chart-panel__canvas bannerpro-chart-wrap">
        <canvas ref="canvas"></canvas>
      </div>
    </div>
  `,
})
