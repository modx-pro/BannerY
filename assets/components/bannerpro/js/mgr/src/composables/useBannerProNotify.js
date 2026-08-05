import { useToast } from 'primevue/usetoast'
import { lex } from '../request.js'

function modxLex(key) {
  if (typeof window !== 'undefined' && typeof window._ === 'function') {
    const value = window._(key)
    if (value && value !== key) {
      return value
    }
  }
  return lex(key)
}

/**
 * Уведомления в стиле MODX Manager (MODx.msg) с fallback на PrimeVue Toast.
 */
export function useBannerProNotify() {
  const toast = useToast()

  function notifySuccess(message, title = 'success') {
    if (typeof MODx !== 'undefined' && MODx.msg?.status) {
      MODx.msg.status({
        title: modxLex(title),
        message,
        dontHide: false,
      })
      return
    }
    toast.add({
      severity: 'success',
      summary: modxLex(title),
      detail: message,
      life: 3500,
    })
  }

  function notifyError(message, title = 'error') {
    if (typeof MODx !== 'undefined' && MODx.msg?.alert) {
      MODx.msg.alert(modxLex(title), message)
      return
    }
    toast.add({
      severity: 'error',
      summary: modxLex(title),
      detail: message,
      life: 5000,
    })
  }

  function notifyWarn(message, title = 'bannerpro') {
    if (typeof MODx !== 'undefined' && MODx.msg?.status) {
      MODx.msg.status({
        title: modxLex(title),
        message,
        dontHide: false,
      })
      return
    }
    toast.add({
      severity: 'warn',
      summary: modxLex(title),
      detail: message,
      life: 4000,
    })
  }

  return { notifySuccess, notifyError, notifyWarn }
}
