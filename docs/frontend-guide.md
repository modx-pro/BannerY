# Руководство по фронтенду

BannerPro выводит баннеры через сниппет **BannerPro** (pdoFetch из pdoTools). Клики считает плагин **BannerProClickout** на `OnPageNotFound`.

**Все примеры ниже — синтаксис Fenom** (шаблоны MODX 3 с pdoTools). Вызов сниппета: `{'!BannerPro' | snippet : [ … ]}`.

---

## Быстрый старт

### Шаг 1. Создайте позицию в админке

**Компоненты → BannerPro → Позиции** → создайте, например, `sidebar`. В сниппете можно указать **`positionName`** (имя позиции) или **`position`** (числовой ID).

### Шаг 2. Создайте баннер и привяжите к позиции

**Баннеры → Создать** → укажите название, URL (ссылка на ресурс через кнопку в админке или `https://…`), изображение, отметьте позицию.

### Шаг 3. Вставьте вывод на страницу

```fenom
{'!BannerPro' | snippet : [
  'positionName' => 'sidebar',
  'tpl' => 'byAd',
  'limit' => 5
]}
```

### Шаг 4. Проверьте плагин

Плагин **BannerProClickout** включён на **OnPageNotFound**. В чанке используйте плейсхолдер **`{$click_url}`** ([configuration.md](configuration.md)).

---

## Сниппет BannerPro

### Собственные параметры

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `position` | int | — | ID позиции (`bannerpro_positions.id`); приоритет над `positionName` |
| `positions` | string | — | Несколько ID через запятую: `1,2,3` |
| `positionName` | string | — | Имя позиции или несколько через запятую: `sidebar,footer` |
| `positionKey` | string | — | Синоним `positionName` (для совместимости со старыми шаблонами) |
| `context` | string | текущий контекст | Фильтр позиций по `context_key`; `*` = текущий контекст; пустой `context_key` на позиции = все контексты (с 4.5.0) |
| `productId` | int | — | ID товара MiniShop3: глобальные баннеры + баннеры с тем же `product_id` в БД |
| `showInactive` | bool | `0` | Показывать неактивные баннеры (`active = 0`) |
| `sortby` | string | `RAND()` | Сортировка: поле `byAd`, `idx` / `index`, `weighted`, `ab` (A/B 50/50 по cookie) |
| `where` | JSON | — | Доп. условия xPDO, мержатся с базовыми |
| `innerJoin` | JSON | — | Доп. join, мержится с `byAdPosition` |
| `select` | JSON | — | Доп. поля select |
| `tplOuter` | string | — | Алиас для `tplWrapper` |
| `outputSeparator` | string | `\n` | Разделитель между баннерами |

### Параметры pdoTools (лексикон `bannerpro:properties`)

| Параметр | Описание |
|----------|----------|
| `limit` | Лимит выборки |
| `offset` | Смещение |
| `sortdir` | `ASC` / `DESC` |
| `tpl` | Чанк одного баннера (fallback для image; для html — `byHtml`) |
| `tplFirst`, `tplLast`, `tplOdd` | Чанки для первого / последнего / нечётного |
| `tplImage`, `tplImageFirst`, … | Чанки только для баннеров `type=image` |
| `tplHtml`, `tplHtmlFirst`, … | Чанки только для баннеров `type=html` (по умолчанию `byHtml`) |
| `tplWrapper` | Обёртка с плейсхолдером `{$output}` |
| `wrapIfEmpty` | Выводить обёртку при пустом результате |
| `toPlaceholder` | Записать результат в плейсхолдер |
| `toSeparatePlaceholders` | Каждый баннер в отдельный плейсхолдер (`prefix0`, `prefix1`, …) |
| `fastMode` | Быстрый режим чанков pdoTools |
| `showLog` | Лог pdoFetch (только для сессии `mgr`) |
| `cache` | `1` / `0` — кэш HTML для этого вызова; пусто = `bannerpro_cache` |
| `cacheLifetime` | TTL в секундах; пусто = `bannerpro_cache_lifetime` |

### Кэширование вывода

Сниппет может кэшировать **готовый HTML** (после рендера чанков и обёртки) через `modX::cacheManager` — partition `bannerpro`, тег `bannerpro`.

**Включено по умолчанию** (`bannerpro_cache = 1`), если сортировка не случайная:

```fenom
{'!BannerPro' | snippet : [
  'positionName' => 'sidebar',
  'sortby' => 'idx',
  'sortdir' => 'ASC'
]}
```

**Отключить** для одного вызова:

```fenom
{'!BannerPro' | snippet : [
  'positionName' => 'sidebar',
  'sortby' => 'idx',
  'cache' => 0
]}
```

**Ротация** (`sortby` по умолчанию `RAND()`, `weighted` или `ab`) кэш не использует — каждый запрос выполняет выборку заново.

**Взвешенная ротация** — вес задаётся в админке для связи баннер↔позиция (`byAdPosition.weight`, минимум 1):

```fenom
{'!BannerPro' | snippet : [
  'positionName' => 'sidebar',
  'sortby' => 'weighted',
  'limit' => 1
]}
```

### A/B split (`sortby=ab`, с 4.8.0)

Sticky 50/50 между **ровно двумя** баннерами в одной позиции (после фильтров расписания, лимитов и таргетинга):

```fenom
{'!BannerPro' | snippet : [
  'positionName' => 'hero',
  'sortby' => 'ab',
  'limit' => 1
]}
```

| Поведение | Описание |
|-----------|----------|
| Первый визит | Случайный выбор одного из двух баннеров |
| Повторные визиты | Тот же баннер по cookie `bannerpro_ab_{positionId}` |
| Не 2 баннера | A/B не применяется — выводятся все подходящие |
| Кэш сниппета | Отключён (как для `RAND()` / `weighted`) |
| TTL cookie | `bannerpro_ab_ttl` (дней, по умолчанию 30) |

Статистическая значимость и отчёт по вариантам **не** входят в scope — только sticky split (см. [roadmap.md](roadmap.md), Фаза 5).

При сохранении баннера или позиции в админке кэш сбрасывается по тегу `bannerpro`.

Ключ кэша включает **effective context** (`activeContext`): текущий контекст MODX или явный `&context=`. Два вызова с разным `context` на одной странице не делят один кэш.

### Базовая выборка (всегда)

- Активные по датам: `start` / `end` относительно текущего времени
- `active = 1`, если не `showInactive`
- `INNER JOIN` `byAdPosition` по `byAd.id = byAdPosition.ad`
- Фильтр по `position` / `positions` / `positionName`, если заданы
- Фильтр по `context_key` позиции (текущий контекст MODX или `context`)
- **Лимиты** (с 4.1.0): баннеры с `max_clicks` / `max_impressions` > 0 скрываются из выборки при достижении порога

### Плейсхолдеры в чанке баннера

| Плейсхолдер | Источник |
|-------------|----------|
| `id` | ID баннера |
| `name` | Название |
| `url` | URL перехода (после клика) |
| `click_url` | Готовый URL клика (clickout + UTM при включённых настройках) |
| `type` | `image` или `html` |
| `image` | Полный URL картинки (для `type=image`) |
| `html` | HTML-код баннера (для `type=html`) |
| `description` | Описание |
| `adposition` | **ID связи** `bannerpro_ads_positions` |
| `idx` | Порядковый номер в выводе (с 1) |
| `product_id` | ID привязанного товара |
| `product_title`, `product_url`, `product_price`, … | Данные `msProduct` |

Дополнительные параметры вызова сниппета тоже попадают в плейсхолдеры строки.

### MiniShop3

На карточке товара передайте `productId` — см. [integrations/minishop3.md](integrations/minishop3.md).

---

## Примеры вызова

### Один баннер (случайный)

```fenom
{'!BannerPro' | snippet : [
  'position' => 3,
  'tpl' => 'byAd',
  'limit' => 1,
  'sortby' => 'RAND()'
]}
```

### Несколько позиций

```fenom
{'!BannerPro' | snippet : [
  'positions' => '1,3,5',
  'tpl' => 'byAd',
  'sortby' => 'idx',
  'sortdir' => 'ASC'
]}
```

### Фиксированный порядок

```fenom
{'!BannerPro' | snippet : [
  'position' => 3,
  'tpl' => 'byAd',
  'sortby' => 'idx',
  'sortdir' => 'ASC'
]}
```

### Обёртка вокруг блока

```fenom
{'!BannerPro' | snippet : [
  'position' => 3,
  'tpl' => 'byAd',
  'tplWrapper' => '@INLINE <div class="banners banners--sidebar">{$output}</div>',
  'wrapIfEmpty' => false
]}
```

Отдельный чанк-обёртка `bannerWrapper`:

```fenom
<div class="banners">
  {$output}
</div>
```

```fenom
{'!BannerPro' | snippet : [
  'position' => 3,
  'tpl' => 'byAd',
  'tplWrapper' => 'bannerWrapper'
]}
```

### Вывод в плейсхолдер

```fenom
{'!BannerPro' | snippet : [
  'position' => 3,
  'tpl' => 'byAd',
  'toPlaceholder' => 'sidebarBanners'
]}
{$_modx->getPlaceholder('sidebarBanners')}
```

### Отдельный плейсхолдер на каждый баннер

```fenom
{'!BannerPro' | snippet : [
  'position' => 3,
  'tpl' => 'byAd',
  'toSeparatePlaceholders' => 'banner'
]}
{$_modx->getPlaceholder('banner0')}
{$_modx->getPlaceholder('banner1')}
```

### Дополнительный фильтр (JSON where)

```fenom
{'!BannerPro' | snippet : [
  'position' => 3,
  'tpl' => 'byAd',
  'where' => '{"byAd.name:LIKE":"%акция%"}'
]}
```

### Фильтр по имени позиции

```fenom
{'!BannerPro' | snippet : [
  'positionName' => 'sidebar',
  'tpl' => 'byAd'
]}
```

Несколько позиций: `'positionName' => 'sidebar,footer'`. Если заданы и `position`, и `positionName`, приоритет у **`position`**.

### Фильтр по контексту MODX (с 4.5.0)

```fenom
{'!BannerPro' | snippet : [
  'positionName' => 'sidebar',
  'context' => 'web',
  'tpl' => 'byAd'
]}
```

### Продвинутый фильтр: join к byPosition

```fenom
{'!BannerPro' | snippet : [
  'tpl' => 'byAd',
  'innerJoin' => '{"byPosition":{"alias":"byPosition","on":"byPosition.id = byAdPosition.position"}}',
  'where' => '{"byPosition.name:LIKE":"%side%"}'
]}
```

---

## Чанки

Рекомендуется **`@FILE`** и Fenom. Ссылка клика — **`{$click_url}`** (с 4.6.0), не ручная сборка из `bannerpro_click`.

Пример ([examples/chunk-byad.fenom.tpl](examples/chunk-byad.fenom.tpl)):

```fenom
<article class="banner" data-ad="{$id}">
  <a href="{$click_url|escape:'html'}"
     data-bannerpro-adposition="{$adposition}"
     data-bannerpro-ad-id="{$id}"
     {if $description}title="{$description|escape}"{/if}>
    {if $image}
      <img src="{$image}" alt="{$name|escape}" loading="lazy" />
    {else}
      <span class="banner__text">{$name|escape}</span>
    {/if}
  </a>
</article>
```

Вызов:

```fenom
{'!BannerPro' | snippet : [
  'position' => 3,
  'tpl' => '@FILE chunks/byad.fenom.tpl',
  'fastMode' => true
]}
```

### Текстовый баннер

```fenom
{'!BannerPro' | snippet : [
  'position' => 2,
  'tpl' => '@FILE chunks/byad-text.fenom.tpl'
]}
```

---

## Учёт кликов

1. Пользователь переходит по `{$click_url}` → `/{bannerpro_click}/{adposition}`
2. `OnPageNotFound` → **BannerProClickout**
3. Запись в `bannerpro_clicks`, событие `OnBannerProClick`, редирект на URL баннера

Query-параметры ссылки клика попадают в плейсхолдеры при обработке URL баннера:

```fenom
<a href="{$click_url|escape:'html'}?page_id={$_modx->resource.id}&utm_source=sidebar">
  <img src="{$image}" alt="{$name|escape}" />
</a>
```

### UTM на redirect (с 4.4.0)

Настройки `bannerpro_utm_*` в админке (**Настройки → BannerPro**). См. [configuration.md](configuration.md).

### URL баннера в админке

| Тип | Пример |
|-----|--------|
| Ресурс MODX | выбор через кнопку «Выбрать» (id ресурса) |
| Внешний | `https://partner.example/landing` |

---

## Учёт показов (impressions)

1. `bannerpro_track_impressions` + плагин **BannerProImpression**
2. Обёртка `data-bannerpro-impression` + `impression.js`
3. Pixel и `OnBannerProImpression` — [integrations/analytics.md](integrations/analytics.md)

---

## Внешняя аналитика

Плагин **BannerProAnalytics** + `bannerpro_analytics_enabled`. Подробно: [integrations/analytics.md](integrations/analytics.md).

---

## Интеграция в шаблон сайта

### Сайдбар в layout

```fenom
<aside class="layout__sidebar">
  <h2 class="sidebar__title">Реклама</h2>
  {'!BannerPro' | snippet : [
    'position' => 3,
    'tpl' => 'byAd',
    'tplWrapper' => '@INLINE <div class="sidebar-banners">{$output}</div>',
    'limit' => 3,
    'sortby' => 'idx',
    'sortdir' => 'ASC'
  ]}
</aside>
```

### Главный баннер + ротация

```fenom
<section class="hero-banner">
  {'!BannerPro' | snippet : [
    'position' => 1,
    'tpl' => '@FILE chunks/hero-banner.fenom.tpl',
    'limit' => 1,
    'sortby' => 'RAND()'
  ]}
</section>
```

---

## Отладка

Только для авторизованных в контексте `mgr`:

```fenom
{'!BannerPro' | snippet : [
  'position' => 3,
  'tpl' => 'byAd',
  'showLog' => true
]}
```

---

## Схема потока данных

```
Админка: byAd + byPosition + byAdPosition
              ↓
BannerPro (pdoFetch) → Fenom-чанк tpl
              ↓
click_url → BannerProClickout → byClick + redirect
              ↓
(опц.) impression.js → pixel + bannerpro:impression
```
