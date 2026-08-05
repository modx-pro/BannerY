/**
 * Скриншоты админки BannerPro для docs/images/manager/
 * Запуск: node _build/capture-manager-screenshots.mjs
 * Требует активную сессию MODX или MODX_MGR_USER / MODX_MGR_PASSWORD.
 */
import { chromium } from 'playwright'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const outDir = path.join(__dirname, '../docs/images/manager')
const baseUrl = process.env.BANNERPRO_MGR_URL || 'https://project.test/manager/?a=index&namespace=bannerpro'
const loginUrl = process.env.BANNERPRO_MGR_LOGIN_URL || 'https://project.test/manager/'
const user = process.env.MODX_MGR_USER || 'root'
const password = process.env.MODX_MGR_PASSWORD || ''

fs.mkdirSync(outDir, { recursive: true })

async function loginIfNeeded(page) {
  if (!page.url().includes('login') && !page.url().includes('security/login')) {
    return
  }
  if (!password) {
    throw new Error('MODX login required. Set MODX_MGR_PASSWORD or log in manually and reuse storage state.')
  }
  await page.fill('input[name="username"], #modx-login-username', user)
  await page.fill('input[name="password"], #modx-login-password', password)
  await page.click('button[type="submit"], #modx-login-btn')
  await page.waitForLoadState('networkidle')
}

async function waitApp(page) {
  await page.waitForSelector('#bannerpro-mgr-app[data-v-app="true"]', { timeout: 30000 })
  await page.waitForTimeout(800)
}

async function shot(page, name) {
  const file = path.join(outDir, name)
  await page.locator('#modx-content').screenshot({ path: file })
  console.log('saved', file)
}

const browser = await chromium.launch({ headless: true })
const context = await browser.newContext({
  viewport: { width: 1440, height: 900 },
  deviceScaleFactor: 2,
  ignoreHTTPSErrors: true,
})
const page = await context.newPage()

await page.goto(loginUrl, { waitUntil: 'networkidle' })
await loginIfNeeded(page)
await page.goto(baseUrl, { waitUntil: 'networkidle' })
await waitApp(page)

await shot(page, '01-ads-list.png')

await page.getByRole('tab', { name: 'Позиции' }).click()
await waitApp(page)
await shot(page, '02-positions-list.png')

await page.getByRole('tab', { name: 'Статистика' }).click()
await waitApp(page)
await shot(page, '03-stats-overview.png')

await page.getByRole('tab', { name: 'Клики' }).click()
await page.waitForTimeout(600)
await shot(page, '04-stats-clicks.png')

await page.getByRole('tab', { name: 'Рефереры' }).click()
await page.waitForTimeout(600)
await shot(page, '05-stats-referrers.png')

await page.getByRole('tab', { name: 'Баннеры' }).click()
await waitApp(page)
await page.getByRole('button', { name: 'Новый баннер' }).click()
await page.waitForSelector('.p-dialog', { timeout: 10000 })
await page.waitForTimeout(400)
await page.locator('.p-dialog').screenshot({ path: path.join(outDir, '06-ad-form-dialog.png') })
console.log('saved', path.join(outDir, '06-ad-form-dialog.png'))

await browser.close()
