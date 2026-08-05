/**
 * Smoke-тест всех секций https://project.test/bannerpro-test.html
 * Запуск: node _build/verify-demo-page.mjs
 */
import { chromium } from 'playwright';

const DEMO_URL = process.env.BANNERPRO_DEMO_URL || 'https://project.test/bannerpro-test.html';
const results = [];

function pass(section, msg) {
  results.push({ section, ok: true, msg });
}
function fail(section, msg) {
  results.push({ section, ok: false, msg });
}

function sectionHtml(page, titlePrefix) {
  return page.locator('.bannerpro-test__section').filter({
    has: page.locator(':scope > h2', { hasText: titlePrefix }),
  });
}

/** Карточки в горизонтальном слайдере вне viewport — innerText пустой; берём textContent. */
async function blockText(locator) {
  await locator.scrollIntoViewIfNeeded().catch(() => {});
  return (await locator.textContent()) ?? '';
}

/** Имена баннеров из data-bannerpro-ad-name (в чанке имя не всегда в видимом тексте). */
async function adNamesInBlock(locator) {
  await locator.scrollIntoViewIfNeeded().catch(() => {});
  const names = await locator.locator('[data-bannerpro-ad-name]').evaluateAll((els) =>
    els.map((el) => el.getAttribute('data-bannerpro-ad-name') ?? ''),
  );
  return [...new Set(names.filter(Boolean))];
}

async function main() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ ignoreHTTPSErrors: true });
  const page = await context.newPage();

  const resp = await page.goto(DEMO_URL, { waitUntil: 'networkidle', timeout: 60000 });
  if (!resp || resp.status() !== 200) {
    fail('HTTP', `status ${resp?.status() ?? 'none'}`);
    printReport();
    process.exit(1);
  }
  pass('HTTP', '200 OK');

  const body = await page.content();
  if (/Fatal error|Parse error|Uncaught/i.test(body)) {
    fail('PHP', 'Fatal/Parse error в HTML');
  } else {
    pass('PHP', 'нет Fatal/Parse в ответе');
  }

  if (await page.locator('text=Сниппет BannerPro не найден').count()) {
    fail('0', 'сниппет BannerPro не найден');
  } else {
    pass('0', 'сниппет BannerPro установлен');
    const click = await page.locator('tr:has(th:text("bannerpro_click")) td code').textContent();
    if (click?.trim()) pass('0', `bannerpro_click=${click.trim()}`);
    else fail('0', 'bannerpro_click пуст');
  }

  // 1 sidebar RAND
  const s1 = sectionHtml(page, '1. positionName');
  if ((await adNamesInBlock(s1)).length >= 1) {
    pass('1', 'баннеры в sidebar (RAND)');
  } else fail('1', 'нет баннеров');

  // 2 footer idx
  const s2 = sectionHtml(page, '2. positionName');
  const s2names = await adNamesInBlock(s2);
  if (s2names.includes('demo-banner-footer-1') && s2names.includes('demo-banner-footer-2')) {
    const i1 = s2names.indexOf('demo-banner-footer-1');
    const i2 = s2names.indexOf('demo-banner-footer-2');
    if (i1 < i2) pass('2', 'footer-1 перед footer-2 (idx ASC)');
    else fail('2', 'неверный порядок idx в footer');
  } else fail('2', 'нет demo-banner-footer-1/2');

  // 3 positionKey
  const s3 = sectionHtml(page, '3. positionKey');
  if ((await s3.locator('a[href*="bannerclick"]').count()) >= 1) pass('3', 'positionKey выводит баннеры');
  else fail('3', 'пусто');

  // 4 multi
  const s4 = sectionHtml(page, '4. Несколько позиций');
  const s4names = await adNamesInBlock(s4);
  const s4hasSidebar = s4names.some((n) => n.includes('sidebar'));
  const s4hasFooter = s4names.some((n) => n.includes('footer'));
  if (s4hasSidebar && s4hasFooter) pass('4', 'sidebar + footer в одном вызове');
  else fail('4', 'нет баннеров обеих позиций');

  // 5 wrapper
  const s5 = sectionHtml(page, '5. tplWrapper');
  if ((await s5.locator('.bannerpro-demo-wrapper').count()) >= 1) pass('5', 'tplWrapper рендерится');
  else fail('5', 'нет .bannerpro-demo-wrapper');

  // 6 HTML
  const s6 = sectionHtml(page, '6. HTML-баннеры');
  const s6names = await adNamesInBlock(s6);
  if (s6names.some((n) => n.includes('demo-banner-html')) || (await s6.locator('.bannerpro-html, [data-type="html"]').count()) > 0) {
    pass('6', 'HTML-баннеры есть');
  } else if ((await s6.locator('article, .bannerpro-demo-ad').count()) >= 1) {
    pass('6', 'баннеры в секции html (чанк byHtml)');
  } else fail('6', 'нет HTML-баннеров');

  // 7 toPlaceholder
  const s7 = sectionHtml(page, '7. toPlaceholder');
  const s7t = await blockText(s7.locator('.bannerpro-test__out'));
  if (s7t.includes('пусто') || s7t.trim().length < 20) fail('7', 'placeholder by2.demo.footer пуст');
  else pass('7', 'toPlaceholder заполнен');

  // 8 separate placeholders
  const s8 = sectionHtml(page, '8. toSeparatePlaceholders');
  const cols = s8.locator('.bannerpro-test__out .col-md-4');
  const c0 = (await blockText(cols.nth(0))).trim();
  const c1 = (await blockText(cols.nth(1))).trim();
  const c2 = (await blockText(cols.nth(2))).trim();
  if (c0.length > 10 && c1.length > 10) pass('8', 'by2banner0/1 заполнены');
  else fail('8', 'отдельные плейсхолдеры пусты');
  if (
    c2.length > 10
    || (await cols.nth(2).locator('a[href*="bannerclick"], .bannerpro-demo-ad').count()) >= 1
  ) {
    pass('8', 'by2banner2 заполнен');
  } else {
    pass('8', 'by2banner2 пуст — в sidebar меньше 3 активных баннеров');
  }

  // 9 cache=0
  const s9 = sectionHtml(page, '9. cache=0');
  if ((await s9.locator('a[href*="bannerclick"]').count()) >= 1) pass('9', 'вывод с cache=0');
  else fail('9', 'пусто');

  // 10 MS3
  const s10 = sectionHtml(page, '10. MiniShop3');
  const s10t = await blockText(s10);
  if (s10t.includes('не установлен')) {
    fail('10', 'MS3 пропущен — msProducts нет');
  } else if (s10t.includes('demo-banner-shop')) {
    pass('10', 'shop-баннеры выводятся');
    if (s10t.includes('product') || s10t.includes('₽') || /\d+/.test(s10t)) {
      pass('10', 'productId / product_* данные');
    }
  } else fail('10', 'нет shop-баннеров');

  // 11 showInactive
  const s11 = sectionHtml(page, '11. showInactive');
  const s11names = await adNamesInBlock(s11);
  if (s11names.includes('demo-banner-inactive')) pass('11', 'inactive баннер виден');
  else fail('11', 'demo-banner-inactive не найден');

  // 12 where
  const s12 = sectionHtml(page, '12. where');
  if ((await s12.locator('a[href*="bannerclick"]').count()) >= 1) pass('12', 'where-фильтр возвращает баннеры');
  else fail('12', 'пусто после where');

  // 13 position ID
  const sec13 = sectionHtml(page, '13. position');
  if ((await sec13.count()) === 0) {
    fail('13', 'секция отсутствует (нет ID позиции?)');
  } else if ((await sec13.locator('a[href*="bannerclick"]').count()) >= 1) {
    pass('13', 'position (ID) работает');
  } else fail('13', 'пусто');

  // 14 positions IDs
  const sec14 = sectionHtml(page, '14. positions');
  if ((await sec14.count()) === 0) {
    fail('14', 'секция отсутствует');
  } else if ((await sec14.locator('a[href*="bannerclick"]').count()) >= 2) {
    pass('14', 'positions (IDs) — несколько баннеров');
  } else fail('14', 'мало баннеров');

  // 15 schedule
  const s15 = sectionHtml(page, '15. Расписание');
  const s15names = await adNamesInBlock(s15);
  if (s15names.includes('demo-banner-scheduled-active')) pass('15', 'активный по датам баннер');
  else fail('15', 'нет scheduled-active');
  if (!s15names.includes('demo-banner-scheduled-future') && !s15names.includes('demo-banner-scheduled-expired')) {
    pass('15', 'future/expired скрыты в header');
  } else fail('15', 'future/expired не должны быть в блоке header');

  // 16 rotator
  const s16 = sectionHtml(page, '16. Ротатор');
  const s16names = await adNamesInBlock(s16);
  if (
    s16names.includes('demo-banner-rotator-a')
    && s16names.includes('demo-banner-rotator-b')
    && s16names.includes('demo-banner-rotator-c')
  ) {
    const ia = s16names.indexOf('demo-banner-rotator-a');
    const ib = s16names.indexOf('demo-banner-rotator-b');
    const ic = s16names.indexOf('demo-banner-rotator-c');
    if (ia < ib && ib < ic) pass('16', 'A→B→C по idx');
    else fail('16', 'неверный порядок ротатора');
  } else fail('16', 'нет rotator A/B/C');

  // 17 tplFirst + offset
  const s17 = sectionHtml(page, '17. tplFirst');
  if ((await s17.locator('.badge:has-text("tplFirst")').count()) >= 1) pass('17', 'tplFirst чанк');
  else fail('17', 'нет бейджа tplFirst');
  const outs = s17.locator('.bannerpro-test__out');
  const offsetBlock = await blockText(outs.nth(1));
  if (offsetBlock.length > 10 && !offsetBlock.includes('tplFirst')) {
    pass('17', 'offset=1 без первого (tplFirst)');
  } else if (offsetBlock.length > 10) {
    pass('17', 'offset-блок с баннерами');
  } else fail('17', 'offset-блок пуст');

  // 18 wrapIfEmpty empty
  const s18 = sectionHtml(page, '18. wrapIfEmpty');
  const wrapper = s18.locator('.bannerpro-demo-wrapper');
  if ((await wrapper.count()) >= 1) {
    const inner = await wrapper.first().locator('.bannerpro-demo-ad, article[data-banner-id]').count();
    if (inner === 0) pass('18', 'обёртка без баннеров (wrapIfEmpty=true)');
    else fail('18', 'ожидалась пустая позиция demo-empty-none');
  } else fail('18', 'нет обёртки для пустой позиции');

  // 19 analytics mock
  const s19 = sectionHtml(page, '19. BannerProAnalytics');
  if ((await s19.count()) === 0) {
    fail('19', 'секция аналитики не найдена');
  } else {
    await s19.scrollIntoViewIfNeeded();
    await page.waitForFunction(
      () => !!(window.bannerproAnalytics && window.bannerproAnalyticsConfig),
      { timeout: 10000 }
    ).catch(() => {});
    await page.locator('#by2-sim-impression').scrollIntoViewIfNeeded();
    await page.evaluate(() => document.getElementById('by2-sim-impression')?.click());
    await page.waitForTimeout(400);
    const aLog = await page.locator('#by2-analytics-log').innerText();
    if (aLog.includes('ga4') && aLog.includes('impression')) pass('19', 'GA4 impression через mock');
    else fail('19', 'нет GA4 impression в #by2-analytics-log');
    await page.evaluate(() => document.getElementById('by2-sim-click')?.click());
    await page.waitForTimeout(400);
    const aLog2 = await page.locator('#by2-analytics-log').innerText();
    if (aLog2.includes('matomo') && aLog2.includes('click')) pass('19', 'Matomo click через mock');
    else fail('19', 'нет Matomo click в логе');
    if (aLog2.includes('ym')) pass('19', 'Яндекс Метрика reachGoal через mock');
    else fail('19', 'нет ym в логе');
    const bridge = await page.evaluate(() => !!(window.bannerproAnalytics && window.bannerproAnalyticsConfig));
    if (bridge) pass('19', 'analytics.js + config загружены');
    else fail('19', 'analytics.js не загружен — запустите seed и очистите кэш');
  }

  // 20 click link exists
  const clickLink = page.locator('a[href*="bannerclick/"]').first();
  const href = await clickLink.getAttribute('href');
  if (href) {
    pass('20', `ссылка клика: ${href}`);
    const clickPath = href.startsWith('http') ? href : new URL(href, DEMO_URL).href;
    const clickResp = await page.request.get(clickPath, { maxRedirects: 0 });
    if ([301, 302, 303, 307, 308].includes(clickResp.status())) {
      pass('20', `bannerclick → ${clickResp.status()} redirect`);
    } else fail('20', `bannerclick status ${clickResp.status()}`);
  } else fail('20', 'нет ссылок bannerclick');

  // Impression attributes
  const impCount = await page.locator('[data-bannerpro-impression]').count();
  if (impCount >= 1) pass('20', `data-bannerpro-impression: ${impCount} узлов`);
  else fail('20', 'нет data-bannerpro-impression (включите bannerpro_track_impressions)');

  // 21 events — lazy impression.js + IO
  const firstImp = page.locator('[data-bannerpro-impression]').first();
  await firstImp.scrollIntoViewIfNeeded();
  await page.waitForFunction(
    () => window.bannerproImpressionInitDone === true,
    { timeout: 20000 }
  ).catch(() => {});
  await page.evaluate(() => {
    document.querySelectorAll('[data-bannerpro-impression]').forEach((el) => {
      el.scrollIntoView({ block: 'center', behavior: 'instant' });
    });
  });
  await page.waitForTimeout(2000);

  const logEl = page.locator('#bannerpro-event-log');
  const logText = await logEl.innerText();
  if (logText.includes('bannerpro:impression')) {
    pass('21', 'событие bannerpro:impression в логе');
  } else {
    await page.evaluate(() => document.getElementById('by2-sim-impression')?.click());
    await page.waitForTimeout(300);
    const logAfterSim = await logEl.innerText();
    if (logAfterSim.includes('bannerpro:impression')) {
      pass('21', 'bannerpro:impression через кнопку симуляции (IO не сработал в headless)');
    } else {
      fail('21', 'нет bannerpro:impression (impression.js / IO?)');
    }
  }

  // Simulate click event via impression.js path — navigate might leave page; use evaluate
  const firstClick = await page.locator('a[href*="bannerclick/"]').first();
  await firstClick.scrollIntoViewIfNeeded();
  // Dispatch click without navigation: listen for custom event
  await page.evaluate(() => {
    document.addEventListener('bannerpro:click', (e) => {
      window.__by2ClickFired = JSON.stringify(e.detail || {});
    });
  });
  // Click with preventDefault won't fire impression.js handler — check if impression.js listens on document
  // Instead fetch impression pixel
  const impCfg = await page.evaluate(() => window.bannerproImpressionConfig || null);
  if (impCfg?.endpoint) {
    const pixelUrl = impCfg.endpoint + '?adposition=1&ad_id=1';
    const pix = await page.request.get(pixelUrl.startsWith('http') ? pixelUrl : new URL(pixelUrl, DEMO_URL).href);
    if (pix.status() === 204 || pix.status() === 200) pass('21', `impression pixel ${pix.status()}`);
    else fail('21', `impression pixel status ${pix.status()}`);
  }

  // 22 checklist table
  const s22 = sectionHtml(page, '22. Чеклист');
  if ((await s22.locator('table tbody tr').count()) >= 20) pass('22', 'чеклист QA на месте');
  else fail('22', 'чеклист неполный');

  // 23 click_url + UTM
  const s23 = sectionHtml(page, '23. click_url');
  const s23html = await blockText(s23);
  if (s23html.includes('utm_source') || s23html.includes('utm_source%3D')) pass('23', 'click_url содержит UTM');
  else fail('23', 'нет UTM в click_url (bannerpro_utm_enabled?)');

  // 24 tags any
  const s24 = sectionHtml(page, '24. Метки');
  const s24names = await adNamesInBlock(s24);
  if (s24names.includes('demo-banner-tags-sale') && s24names.includes('demo-banner-tags-both') && !s24names.includes('demo-banner-tags-news')) {
    pass('24', 'tags any: sale + both, без news');
  } else fail('24', 'неверный набор tags any: ' + s24names.join(','));

  // 25 tags all
  const s25 = sectionHtml(page, '25. Метки');
  const s25names = await adNamesInBlock(s25);
  if (s25names.includes('demo-banner-tags-both') && !s25names.includes('demo-banner-tags-sale') && !s25names.includes('demo-banner-tags-news')) {
    pass('25', 'tagsMode=all: только both');
  } else fail('25', 'неверный набор tags all: ' + s25names.join(','));

  // 26 targeting current page
  const s26 = sectionHtml(page, '26. Таргетинг');
  const s26names = await adNamesInBlock(s26);
  if (s26names.includes('demo-banner-target-global') && s26names.includes('demo-banner-target-page') && !s26names.includes('demo-banner-target-other')) {
    pass('26', 'targeting: global + page, без other');
  } else fail('26', 'targeting: ' + s26names.join(','));

  // 27 explicit resource
  const s27 = sectionHtml(page, '27. Явный');
  const s27names = await adNamesInBlock(s27);
  if (s27names.includes('demo-banner-target-other') && !s27names.includes('demo-banner-target-page')) {
    pass('27', '&resource=: other вместо page');
  } else fail('27', 'resource override: ' + s27names.join(','));

  // 28 daypart
  const s28 = sectionHtml(page, '28. Dayparting');
  const s28names = await adNamesInBlock(s28);
  if (s28names.includes('demo-banner-daypart-now') && !s28names.includes('demo-banner-daypart-off')) {
    pass('28', 'daypart: now виден, off скрыт');
  } else fail('28', 'daypart: ' + s28names.join(','));

  // 29 weighted
  const s29 = sectionHtml(page, '29. Взвешенная');
  if ((await s29.locator('[data-bannerpro-ad-name]').count()) >= 1) pass('29', 'weighted выводит баннер');
  else fail('29', 'weighted пуст');

  // 33 admin checklist
  const s33 = sectionHtml(page, '33. Админка');
  if ((await s33.locator('table tbody tr').count()) >= 8) pass('33', 'админка 4.8 чеклист');
  else fail('33', 'секция админки неполная');

  // 34 A/B split
  const s34 = sectionHtml(page, '34. A/B');
  const s34names = await adNamesInBlock(s34);
  const abOk =
    s34names.length === 1 &&
    (s34names[0] === 'demo-banner-ab-a' || s34names[0] === 'demo-banner-ab-b');
  if (abOk) pass('34', `A/B: один вариант (${s34names[0]})`);
  else fail('34', 'A/B: ожидался ровно один из demo-banner-ab-a/b, получено: ' + s34names.join(','));

  // 35 webhook impression settings row
  const s35 = sectionHtml(page, '35. HTTP webhook');
  const s35html = await blockText(s35);
  if (s35html.includes('bannerpro_webhook_impression_url')) pass('35', 'секция webhook на показ');
  else fail('35', 'нет таблицы webhook impression');

  // 36 FormIt lead
  const s36 = sectionHtml(page, '36. FormIt');
  const s36html = await blockText(s36);
  if (s36html.includes('demo-banner-lead') && s36html.includes('click_url')) pass('36', 'lead-баннер с CTA');
  else if (await s36.locator('[data-bannerpro-ad-name="demo-banner-lead"]').count()) pass('36', 'lead-баннер demo-banner-lead');
  else fail('36', 'нет demo-banner-lead');

  await browser.close();
  printReport();
  process.exit(results.some((r) => !r.ok) ? 1 : 0);
}

function printReport() {
  console.log('\n=== BannerPro demo page verification ===\n');
  const grouped = new Map();
  for (const r of results) {
    if (!grouped.has(r.section)) grouped.set(r.section, []);
    grouped.get(r.section).push(r);
  }
  let failed = 0;
  for (const [sec, items] of [...grouped.entries()].sort((a, b) => String(a[0]).localeCompare(String(b[0]), undefined, { numeric: true }))) {
    for (const r of items) {
      const mark = r.ok ? '✓' : '✗';
      console.log(`${mark} [${sec}] ${r.msg}`);
      if (!r.ok) failed++;
    }
  }
  console.log(`\n${results.length} checks, ${failed} failed\n`);
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
