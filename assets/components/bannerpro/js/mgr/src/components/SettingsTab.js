import { defineComponent, onMounted, ref } from 'vue'
import ToggleSwitch from 'primevue/toggleswitch'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Button from 'primevue/button'
import Message from 'primevue/message'
import { useBannerProApi } from '../composables/useBannerProApi.js'
import { useBannerProNotify } from '../composables/useBannerProNotify.js'
import { lex } from '../request.js'
import PageSection from '../ui/PageSection.js'

export default defineComponent({
  name: 'SettingsTab',
  components: {
    ToggleSwitch, InputText, InputNumber, Button, Message, PageSection,
  },
  setup() {
    const api = useBannerProApi()
    const { notifySuccess, notifyError } = useBannerProNotify()
    const loading = ref(false)
    const saving = ref(false)
    const utm = ref({
      utm_enabled: false,
      utm_source: 'bannerpro',
      utm_medium: 'banner',
      utm_campaign: '',
      utm_content: '{ad_id}-{adposition}',
    })
    const integrations = ref({
      webhook_url: '',
      webhook_impression_url: '',
      webhook_secret: '',
      ab_ttl: 30,
    })

    async function loadSettings() {
      loading.value = true
      try {
        const [utmRes, integrationsRes] = await Promise.all([
          api.settingsUtmGet(),
          api.settingsIntegrationsGet(),
        ])
        const utmData = utmRes.data || {}
        utm.value = {
          utm_enabled: !!utmData.utm_enabled,
          utm_source: utmData.utm_source ?? 'bannerpro',
          utm_medium: utmData.utm_medium ?? 'banner',
          utm_campaign: utmData.utm_campaign ?? '',
          utm_content: utmData.utm_content ?? '{ad_id}-{adposition}',
        }
        const intData = integrationsRes.data || {}
        integrations.value = {
          webhook_url: intData.webhook_url ?? '',
          webhook_impression_url: intData.webhook_impression_url ?? '',
          webhook_secret: intData.webhook_secret ?? '',
          ab_ttl: Number(intData.ab_ttl ?? 30) || 30,
        }
      } catch (e) {
        notifyError(e.message)
      } finally {
        loading.value = false
      }
    }

    async function saveSettings() {
      saving.value = true
      try {
        const [utmRes, integrationsRes] = await Promise.all([
          api.settingsUtmUpdate({
            utm_enabled: utm.value.utm_enabled ? 1 : 0,
            utm_source: utm.value.utm_source,
            utm_medium: utm.value.utm_medium,
            utm_campaign: utm.value.utm_campaign,
            utm_content: utm.value.utm_content,
          }),
          api.settingsIntegrationsUpdate({
            webhook_url: integrations.value.webhook_url,
            webhook_impression_url: integrations.value.webhook_impression_url,
            webhook_secret: integrations.value.webhook_secret,
            ab_ttl: integrations.value.ab_ttl,
          }),
        ])
        const utmData = utmRes.data || {}
        utm.value = {
          utm_enabled: !!utmData.utm_enabled,
          utm_source: utmData.utm_source ?? '',
          utm_medium: utmData.utm_medium ?? '',
          utm_campaign: utmData.utm_campaign ?? '',
          utm_content: utmData.utm_content ?? '',
        }
        const intData = integrationsRes.data || {}
        integrations.value = {
          webhook_url: intData.webhook_url ?? '',
          webhook_impression_url: intData.webhook_impression_url ?? '',
          webhook_secret: intData.webhook_secret ?? '',
          ab_ttl: Number(intData.ab_ttl ?? 30) || 30,
        }
        notifySuccess(lex('bannerpro.settings.success.save'))
      } catch (e) {
        notifyError(e.message)
      } finally {
        saving.value = false
      }
    }

    onMounted(loadSettings)

    return {
      lex, utm, integrations, loading, saving, saveSettings,
    }
  },
  template: `
    <div class="flex flex-column gap-4">
      <PageSection
        :title="lex('bannerpro.settings.utm')"
        :subtitle="lex('bannerpro.settings.utm.hint')"
      >
        <template #actions>
          <Button
            :label="lex('bannerpro.save')"
            icon="pi pi-check"
            :loading="saving"
            :disabled="loading"
            @click="saveSettings"
          />
        </template>

        <Message severity="secondary" class="mb-3 w-full">
          {{ lex('bannerpro.settings.utm.placeholders') }}
        </Message>

        <div class="bannerpro-settings-form grid">
          <div class="col-12 flex align-items-center gap-3 mb-2">
            <ToggleSwitch v-model="utm.utm_enabled" inputId="bannerpro-utm-enabled" :disabled="loading" />
            <label for="bannerpro-utm-enabled" class="cursor-pointer">
              <span class="font-medium">{{ lex('setting_bannerpro_utm_enabled') }}</span>
              <span class="block text-sm text-color-secondary mt-1">{{ lex('setting_bannerpro_utm_enabled_desc') }}</span>
            </label>
          </div>

          <div class="col-12 md:col-6 field">
            <label for="bannerpro-utm-source" class="block mb-1">{{ lex('setting_bannerpro_utm_source') }}</label>
            <InputText id="bannerpro-utm-source" v-model="utm.utm_source" class="w-full"
              :disabled="loading || !utm.utm_enabled" />
            <small class="text-color-secondary">{{ lex('setting_bannerpro_utm_source_desc') }}</small>
          </div>

          <div class="col-12 md:col-6 field">
            <label for="bannerpro-utm-medium" class="block mb-1">{{ lex('setting_bannerpro_utm_medium') }}</label>
            <InputText id="bannerpro-utm-medium" v-model="utm.utm_medium" class="w-full"
              :disabled="loading || !utm.utm_enabled" />
            <small class="text-color-secondary">{{ lex('setting_bannerpro_utm_medium_desc') }}</small>
          </div>

          <div class="col-12 md:col-6 field">
            <label for="bannerpro-utm-campaign" class="block mb-1">{{ lex('setting_bannerpro_utm_campaign') }}</label>
            <InputText id="bannerpro-utm-campaign" v-model="utm.utm_campaign" class="w-full"
              :disabled="loading || !utm.utm_enabled" />
            <small class="text-color-secondary">{{ lex('setting_bannerpro_utm_campaign_desc') }}</small>
          </div>

          <div class="col-12 md:col-6 field">
            <label for="bannerpro-utm-content" class="block mb-1">{{ lex('setting_bannerpro_utm_content') }}</label>
            <InputText id="bannerpro-utm-content" v-model="utm.utm_content" class="w-full"
              :disabled="loading || !utm.utm_enabled" />
            <small class="text-color-secondary">{{ lex('setting_bannerpro_utm_content_desc') }}</small>
          </div>
        </div>
      </PageSection>

      <PageSection
        :title="lex('bannerpro.settings.integrations')"
        :subtitle="lex('bannerpro.settings.integrations.hint')"
      >
        <div class="bannerpro-settings-form grid">
          <div class="col-12 md:col-6 field">
            <label for="bannerpro-webhook-url" class="block mb-1">{{ lex('setting_bannerpro_webhook_url') }}</label>
            <InputText id="bannerpro-webhook-url" v-model="integrations.webhook_url" class="w-full" :disabled="loading" />
            <small class="text-color-secondary">{{ lex('setting_bannerpro_webhook_url_desc') }}</small>
          </div>

          <div class="col-12 md:col-6 field">
            <label for="bannerpro-webhook-impression-url" class="block mb-1">{{ lex('setting_bannerpro_webhook_impression_url') }}</label>
            <InputText id="bannerpro-webhook-impression-url" v-model="integrations.webhook_impression_url" class="w-full" :disabled="loading" />
            <small class="text-color-secondary">{{ lex('setting_bannerpro_webhook_impression_url_desc') }}</small>
          </div>

          <div class="col-12 md:col-6 field">
            <label for="bannerpro-webhook-secret" class="block mb-1">{{ lex('setting_bannerpro_webhook_secret') }}</label>
            <InputText id="bannerpro-webhook-secret" v-model="integrations.webhook_secret" class="w-full"
              type="password" autocomplete="off" :disabled="loading" />
            <small class="text-color-secondary">{{ lex('setting_bannerpro_webhook_secret_desc') }}</small>
          </div>

          <div class="col-12 md:col-6 field">
            <label for="bannerpro-ab-ttl" class="block mb-1">{{ lex('setting_bannerpro_ab_ttl') }}</label>
            <InputNumber id="bannerpro-ab-ttl" v-model="integrations.ab_ttl" class="w-full" :min="1" :max="365"
              :disabled="loading" />
            <small class="text-color-secondary">{{ lex('setting_bannerpro_ab_ttl_desc') }}</small>
          </div>
        </div>
      </PageSection>
    </div>
  `,
})
