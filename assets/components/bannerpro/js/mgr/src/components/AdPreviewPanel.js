import { defineComponent, computed, ref, watch } from 'vue'
import { lex } from '../request.js'

export default defineComponent({
  name: 'AdPreviewPanel',
  props: {
    type: { type: String, default: 'image' },
    layout: { type: String, default: 'side' },
    imageSrc: { type: String, default: '' },
    html: { type: String, default: '' },
  },
  setup(props) {
    const iframeKey = ref(0)
    const srcdoc = computed(() => props.html || '')
    const hasHtml = computed(() => String(props.html || '').trim().length > 0)

    watch(() => props.html, () => {
      iframeKey.value += 1
    })

    return { lex, srcdoc, iframeKey, hasHtml }
  },
  template: `
    <div class="bannerpro-preview-panel"
      :class="[
        type === 'html' ? 'bannerpro-preview-panel--html' : 'bannerpro-preview-panel--image',
        layout === 'stacked' ? 'bannerpro-preview-panel--stacked' : '',
      ]">
      <label class="bannerpro-preview-label">{{ lex('bannerpro.ads.preview') }}</label>
      <div v-if="type === 'image'" class="bannerpro-image-preview">
        <img v-if="imageSrc" :src="imageSrc" class="bannerpro-preview-img" alt="" loading="lazy" />
        <span v-else class="bannerpro-preview-empty">
          <i class="pi pi-image bannerpro-preview-empty__icon" aria-hidden="true" />
          {{ lex('bannerpro.ads.preview.empty') }}
        </span>
      </div>
      <div v-else class="bannerpro-html-preview">
        <div class="bannerpro-html-preview__frame">
          <iframe
            v-if="hasHtml"
            :key="iframeKey"
            sandbox="allow-scripts"
            :srcdoc="srcdoc"
            class="bannerpro-preview-iframe"
            :title="lex('bannerpro.ads.preview.html')"
          ></iframe>
          <span v-else class="bannerpro-preview-empty">
            <i class="pi pi-code bannerpro-preview-empty__icon" aria-hidden="true" />
            {{ lex('bannerpro.ads.preview.html_empty') }}
          </span>
        </div>
        <div class="bannerpro-html-preview__footer">
          <i class="pi pi-info-circle" aria-hidden="true" />
          {{ lex('bannerpro.ads.preview.html_hint') }}
        </div>
      </div>
    </div>
  `,
})
