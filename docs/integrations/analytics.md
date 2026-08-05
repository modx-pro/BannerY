# Внешняя аналитика (GA4, Matomo, Яндекс Метрика)

BannerPro с **3.8.0** вызывает события MODX и отдаёт клиентские хуки для популярных систем аналитики без правки ядра.

## События MODX (сервер)

| Событие | Когда | Где в коде |
|---------|--------|------------|
| `OnBannerProClick` | Запрос на `/bannerclick/{adposition}` перед редиректом | `bannerpro_handle_clickout()` |
| `OnBannerProImpression` | Pixel-запрос на `/bannerimpression/{adposition}` | `bannerpro_handle_impression()` |

### Свойства события

| Ключ | Тип | Описание |
|------|-----|----------|
| `ad` | `byAd` | Объект баннера (может быть null) |
| `ad_id` | int | ID баннера |
| `ad_name` | string | Название |
| `ad_url` | string | URL перехода |
| `ad_type` | string | `image` \| `html` |
| `product_id` | int | MiniShop3, 0 = глобальный |
| `position` | int | ID позиции |
| `position_name` | string | Имя позиции |
| `adposition` | int | ID связи ad↔position |
| `ip` | string | IP посетителя |
| `recorded` | bool | Запись сохранена в БД BannerPro |
| `duplicate` | bool | Дубликат за сегодня (тот же IP/баннер/позиция); клик всё равно обрабатывается |
| `referrer` | string | Только click: HTTP Referer |
| `redirect_url` | string | Только click: URL после обработки чанка |

### Свой плагин

1. Создайте плагин в MODX.
2. Подпишите на `OnBannerProClick` или `OnBannerProImpression`.
3. В коде используйте `$modx->event->name` и свойства из `$scriptProperties` / `$modx->event->getProperties()`.

```php
if ($modx->event->name === 'OnBannerProClick') {
    $modx->log(modX::LOG_LEVEL_INFO, 'Banner click: ' . $scriptProperties['ad_name']);
}
```

## Клиентские события (браузер)

После загрузки `impression.js` при показе баннера:

```javascript
document.addEventListener('bannerpro:impression', function (e) {
  console.log(e.detail); // adposition, ad_id, ad_name, position_name
});
```

Если `analytics.js` ещё не загружен, показы попадают в очередь `window.bannerproImpressionQueue` и обрабатываются при инициализации моста (без двойного учёта в GA4/Matomo/Метрике).

Клики по ссылкам вида `/{bannerpro_click}/{adposition}` перехватывает `analytics.js` через делегирование на `document`.

## Готовый мост: плагин BannerProAnalytics

Поставляется **выключенным**. Включает `analytics.js` на `OnLoadWebDocument` (до вставки скриптов в шаблон; `OnWebPagePrerender` в MODX срабатывает слишком поздно).

### Настройка

1. **Элементы → Плагины** → включить **BannerProAnalytics**.
2. **Системные настройки → BannerPro**:
   - `bannerpro_analytics_enabled` = Да
   - `bannerpro_analytics_ym_counter` = ID счётчика Метрики (если нужна)
   - переименуйте цели/события GA4 и Matomo, если нужны другие имена

### Что отправляется

| Система | Клик | Показ |
|---------|------|-------|
| **GA4** | `dataLayer.push({ event: 'bannerpro_click', … })` | `event: 'bannerpro_impression'` |
| **Matomo** | `_paq.push(['trackEvent', 'BannerPro', 'click', …])` | `…, 'impression', …` |
| **Яндекс Метрика** | `ym(ID, 'reachGoal', 'bannerpro_click', params)` | `…, 'bannerpro_impression', …` |

На странице должны быть уже подключены счётчики GA4 (GTM/dataLayer), Matomo (`_paq`) или Метрика (`ym`).

### GA4 через GTM

Создайте триггеры на пользовательские события `bannerpro_click` и `bannerpro_impression`, используйте параметры `bannerpro_ad_id`, `bannerpro_ad_name`, `bannerpro_adposition`, `bannerpro_position_name`.

### Яндекс Метрика

Создайте цели типа «JavaScript-событие» с идентификаторами из настроек `bannerpro_analytics_ym_click_goal` и `bannerpro_analytics_ym_impression_goal`.

## QA без реальных счётчиков (демо-страница)

Шаблон: `core/elements/templates/demo/bannerpro_test.tpl`, ресурс `bannerpro-test.html`, секция **19**.

### Подготовка

Из корня MODX:

```bash
php core/elements/demo/seed_bannerpro_demo.php
php clear_cache.php
```

Сидер включает `bannerpro_analytics_enabled=1`, плагин **BannerProAnalytics**, счётчик Метрики `99999999`, демо-позиции и баннеры.

### Что подключено на стенде

| Скрипт | Назначение |
|--------|------------|
| `analytics-mock.js` | Стабы `dataLayer`, `_paq`, `ym()`; события в `#bannerpro-analytics-log` |
| `analytics.js` | Через плагин **или** демо-фолбэк шаблона (см. ниже) |

Кнопки **Симулировать показ/клик** вызывают `bannerproAnalytics.trackImpression/Click` → mock перехватывает `push` / `reachGoal`.

Автотест (из репозитория BannerPro):

```bash
node _build/verify-demo-page.mjs
# BANNERPRO_DEMO_URL=https://ваш-сайт/bannerpro-test.html node _build/verify-demo-page.mjs
```

### Демо-фолбэк `analytics.js` на тестовой странице

На стенде мост подключается **даже если** плагин выключен или `bannerpro_analytics_enabled=0`: шаблон сам вставляет `bannerproAnalyticsConfig` и `analytics.js` (пометка **«стенд»** в таблице статуса). На продакшене так делать не нужно — только плагин + настройка.

Условие в шаблоне: плагин не активен **или** аналитика выключена → грузится фолбэк; если плагин включён и `bannerpro_analytics_enabled=1`, скрипт один раз добавляет плагин на `OnLoadWebDocument`.

### Таблица статуса (секция 19)

| Строка | Значение |
|--------|----------|
| Плагин | Состояние `modPlugin` BannerProAnalytics в БД |
| `bannerpro_analytics_enabled` | Системная настройка (`{'bannerpro_analytics_enabled' | option}`) |
| analytics.js | **активен (плагин)** или **активен (стенд)** |

### Fenom `{extends}`: частая ловушка

В `bannerpro_test.tpl` переменные для статуса задаются **внутри** `{block 'styles'}` и `{block 'main'}`. Код `{set …}` **вне** `{block}` при `{extends 'file:…'}` в Fenom **не выполняется** — тогда в таблице ложно показываются «плагин не установлен» и `enabled=0`, хотя в MODX всё включено. При правке шаблона держите инициализацию в блоках.

### Диагностика

| Симптом | Что проверить |
|---------|----------------|
| «Плагин не установлен» при рабочем пакете | Переменные вне `{block}` или устаревший кэш Fenom (`core/cache/pdotools/file`) |
| «мост не загружен» | Плагин выключен и фолбэк не сработал; очистите кэш шаблона |
| События не в логе | `analytics-mock.js` не загрузился; смотрите консоль браузера |

На продакшене подключайте реальные счётчики, не `analytics-mock.js`.

## Связанные документы

- [developer-guide.md](../developer-guide.md) — архитектура `include/events.php`
- [frontend-guide.md](../frontend-guide.md) — вывод баннеров и учёт показов
