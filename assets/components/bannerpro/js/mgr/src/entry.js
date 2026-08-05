if (typeof window !== 'undefined') {
  if (typeof window.bannerproConfig !== 'object' || window.bannerproConfig === null) {
    window.bannerproConfig = { connectorUrl: '', lexicon: {} }
  }
}

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { getPrimeVueLocale } from '@vuetools/usePrimeVueLocale'
import PrimeVue from 'primevue/config'
import { BannerProPreset } from './theme/bannerpro-preset.js'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import 'primeicons/primeicons.css'
import 'primeflex/primeflex.css'
import App from './App.js'
import { getConfig } from './request.js'

function resolveManagerCultureKey() {
  const cfg = getConfig()
  if (cfg.managerLanguage) {
    return String(cfg.managerLanguage).toLowerCase()
  }
  if (typeof window !== 'undefined') {
    return window.MODx?.cultureKey ?? window.MODx?.config?.cultureKey ?? 'en'
  }
  return 'en'
}

let appInstance = null
const SELECTOR = '#bannerpro-mgr-app'

if (typeof document !== 'undefined') {
  document.body.classList.add('bannerpro-manager-page')
}

function createVueApp() {
  const app = createApp(App)
  app.use(createPinia())
  app.use(PrimeVue, {
    theme: {
      preset: BannerProPreset,
      options: { darkModeSelector: '.modx-dark-mode' },
    },
    locale: getPrimeVueLocale(resolveManagerCultureKey()),
    ripple: true,
  })
  app.use(ToastService)
  app.use(ConfirmationService)
  return app
}

export function init(selector = SELECTOR) {
  const el = document.querySelector(selector)
  if (!el || el.dataset.vApp === 'true') {
    return appInstance
  }
  appInstance = createVueApp()
  appInstance.mount(selector)
  el.dataset.vApp = 'true'
  return appInstance
}

export function destroy(selector = SELECTOR) {
  const el = document.querySelector(selector)
  if (appInstance && el) {
    appInstance.unmount()
    appInstance = null
    el.dataset.vApp = 'false'
  }
}

if (document.querySelector(SELECTOR)) {
  init(SELECTOR)
}

window.bannerproAdmin = { init, destroy }
