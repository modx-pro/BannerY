import { defineComponent, ref, computed } from 'vue'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import AdsTab from './components/AdsTab.js'
import PositionsTab from './components/PositionsTab.js'
import StatsTab from './components/StatsTab.js'
import AuditTab from './components/AuditTab.js'
import SettingsTab from './components/SettingsTab.js'
import OverviewWidget from './components/OverviewWidget.js'
import { getConfig, lex } from './request.js'

export default defineComponent({
  name: 'BannerProApp',
  components: {
    Tabs, TabList, Tab, TabPanels, TabPanel, Toast, ConfirmDialog,
    AdsTab, PositionsTab, StatsTab, AuditTab, SettingsTab, OverviewWidget,
  },
  setup() {
    const permissions = getConfig().permissions || {}
    const canViewStats = computed(() => !!permissions.stats)
    const canSave = computed(() => !!permissions.save)
    const statsOnly = computed(() => !!permissions.stats && !permissions.save && !permissions.remove)
    const activeTab = ref(statsOnly.value ? 'stats' : 'ads')

    return {
      lex, canViewStats, canSave, statsOnly, activeTab,
    }
  },
  template: `
    <Toast />
    <ConfirmDialog />
    <div class="bannerpro-mgr-shell">
      <OverviewWidget v-if="canViewStats" @open-stats="activeTab = 'stats'" />
      <div class="bannerpro-mgr-main">
        <Tabs v-model:value="activeTab" class="bannerpro-mgr-tabs">
        <TabList>
          <Tab v-if="!statsOnly" value="ads">
            <i class="pi pi-images mr-2" aria-hidden="true" />{{ lex('bannerpro.ads') }}
          </Tab>
          <Tab v-if="!statsOnly" value="positions">
            <i class="pi pi-map-marker mr-2" aria-hidden="true" />{{ lex('bannerpro.positions') }}
          </Tab>
          <Tab v-if="canViewStats" value="stats">
            <i class="pi pi-chart-line mr-2" aria-hidden="true" />{{ lex('bannerpro.stats') }}
          </Tab>
          <Tab v-if="canSave" value="audit">
            <i class="pi pi-history mr-2" aria-hidden="true" />{{ lex('bannerpro.audit') }}
          </Tab>
          <Tab v-if="canSave" value="settings">
            <i class="pi pi-cog mr-2" aria-hidden="true" />{{ lex('bannerpro.settings') }}
          </Tab>
        </TabList>
        <TabPanels>
          <TabPanel v-if="!statsOnly" value="ads"><AdsTab /></TabPanel>
          <TabPanel v-if="!statsOnly" value="positions"><PositionsTab /></TabPanel>
          <TabPanel v-if="canViewStats" value="stats"><StatsTab /></TabPanel>
          <TabPanel v-if="canSave" value="audit"><AuditTab /></TabPanel>
          <TabPanel v-if="canSave" value="settings"><SettingsTab /></TabPanel>
        </TabPanels>
      </Tabs>
      </div>
    </div>
  `,
})
