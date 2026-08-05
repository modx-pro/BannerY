# Руководство разработчика

## Архитектура

```
core/components/bannerpro/
├── bootstrap.php              — регистрация namespace (MODX 3)
├── controllers/index.class.php — Vue-админка
├── include/                   — модульный PHP API (bannerpro_*)
│   ├── paths.php, lexicon.php, http.php, helpers.php
│   ├── ads.php, positions.php, clicks.php, resources.php, products.php
│   ├── media.php, cache.php, query.php, impressions.php, stats.php, frontend.php, events.php, handlers.php
│   ├── bulk.php, audit.php, bots.php, templates.php
│   ├── cron/purge.php
├── model/bannerpro/            — xPDO (byAd, byPosition, …)
├── elements/                  — сниппет, плагин, чанки
└── lexicon/                   — ru, en, uk

assets/components/bannerpro/
├── connector.php              — JSON API для админки (POST, ACL)
├── api.php                    — read-only REST API (GET, Bearer)
├── js/mgr/src/                — Vue 3 исходники
└── js/mgr/vue-dist/           — собранный бандл
```

Сервисный класс legacy удалён; логика — функции в `include/` и dispatcher в `handlers.php`.

## Сборка пакета

Структура `_build/` как у msRussianPost:

```
_build/
├── build.php
├── config.inc.php
├── elements/          — описание меню, сниппетов, плагинов, настроек
└── resolvers/
    ├── resolver_tables.php
    ├── resolver_setup.php
    ├── resolver_settings.php
    └── resolve.encryption.php
```

```bash
npm run build
php _build/build.php
```

Конфиг: `_build/config.inc.php` (`encrypt => false` по умолчанию).

## Connector API

**URL:** `{assets_url}components/bannerpro/connector.php`
**Метод:** `POST`
**Параметр:** `action`
**Авторизация:** контекст `mgr`, иначе `401`
**ACL:** мутации требуют `bannerpro_save` / `bannerpro_remove` / `bannerpro_stats` (см. `handlers.php`); иначе HTTP 403

| action | Назначение |
|--------|------------|
| `ads_getlist` | Список баннеров |
| `ads_get` | Один баннер |
| `ads_create` | Создать |
| `ads_update` | Обновить |
| `ads_duplicate` | Дублировать баннер (копия + связи с позициями) |
| `ads_remove` | Удалить |
| `ads_enable` / `ads_disable` | Вкл/выкл |
| `ads_bulk_enable` / `ads_bulk_disable` / `ads_bulk_remove` / `ads_bulk_assign_positions` | Массовые действия (4.6.0) |
| `ads_create_from_template` | Создать баннер из preset (4.6.0) |
| `ad_templates_getlist` | Список шаблонов баннеров (4.6.0) |
| `audit_getlist` | Журнал изменений (4.6.0, `bannerpro_save`) |
| `tags_suggest` | Автодополнение меток (4.6.0) |
| `ads_getclicks` | Статистика кликов |
| `positions_getlist` | Список позиций |
| `positions_create` / `positions_update` / `positions_remove` | CRUD позиций |
| `positions_duplicate` | Дублировать позицию (копия + связи с баннерами) |
| `adpositions_getlist` | Баннеры в позиции |
| `adpositions_add` / `adpositions_remove` / `adpositions_sort` | Связи и порядок |
| `adpositions_update_weight` | Вес связи баннер↔позиция (`bannerpro_save`) |
| `clicks_getreferrers` | Рефереры |
| `stats_summary` | Сводка: клики, показы, CTR |
| `stats_compare` | Сравнение периодов this_week vs last_week + delta (4.6.0) |
| `stats_by_day` | Динамика по дням |
| `stats_top_ads` | Топ баннеров |
| `stats_export` | CSV (не JSON) |
| `stats_purge` | Ручная очистка по retention |
| `resource_getlist` | Поиск ресурсов для URL; POST `class_key` (напр. `msProduct`) для пикера товара |

Ответ: `{ "success": true, "data": …, "total": … }` или `{ "success": false, "message": "…" }`.

## Модель данных

| Класс xPDO | Таблица |
|------------|---------|
| `byAd` | `bannerpro_ads` |
| `byPosition` | `bannerpro_positions` |
| `byAdPosition` | `bannerpro_ads_positions` |
| `byClick` | `bannerpro_clicks` |
| `byImpression` | `bannerpro_impressions` |
| `byAdTemplate` | `bannerpro_ad_templates` (4.6.0) |
| `byAudit` | `bannerpro_audit` (4.6.0) |

Таблица `bannerpro_impressions` добавлена в 3.6.0.

### byAd

`id`, `name`, `url`, `image`, `source`, `active`, `description`, `start`, `end`, `type` (`image`|`html`), `html`, `product_id` (0 = глобальный баннер, >0 = `msProduct`), `category_id` (0 = без категории, 4.6.0), `max_clicks`, `max_impressions` (0 = без лимита), `show_hours` (JSON weekdays/hours, 4.6.0), `target_resource_id`, `target_parent_id` (4.6.0), `tags` (JSON array, 4.6.0)

### byAdPosition

`id`, `ad`, `position`, `idx`, `weight` — поле `id` = плейсхолдер `adposition` на фронте.

### byPosition

`id`, `name`, `context_key` (пусто = все контексты MODX; с 4.5.0)

### byClick

`order_id` — ID заказа MS3 при атрибуции (с 4.2.0).

## Сниппет и плагин

| Элемент | Файл | Функция |
|---------|------|---------|
| `BannerPro` | `elements/snippets/snippet.bannerpro.php` | `bannerpro_render_snippet()` |
| `BannerProClickout` | `elements/plugins/plugin.bannerproclickout.php` | `bannerpro_handle_clickout()` |
| `BannerProImpression` | `elements/plugins/plugin.bannerproimpression.php` | `bannerpro_handle_impression()` |
| `BannerProAnalytics` | `elements/plugins/plugin.bannerproanalytics.php` | `bannerpro_register_analytics_assets()` (опционально) |

### События MODX (`include/events.php`)

| Событие | Функция | Назначение |
|---------|---------|------------|
| `OnBannerProClick` | `bannerpro_invoke_click_event()` | После учёта клика, перед редиректом |
| `OnBannerProImpression` | `bannerpro_invoke_impression_event()` | После pixel-запроса показа |

Тело JSON: `event` (`click`), `ad_id`, `position_id`, `adposition`, `referrer`, `ip`, `click_id`, `timestamp`, `redirect_url`, `recorded`, `duplicate`.

При клике также вызывается `bannerpro_dispatch_webhook()` (если задан `bannerpro_webhook_url`) — fire-and-forget POST JSON, не блокирует редирект. Payload: `bannerpro_click_webhook_payload()`; URL проверяется через `bannerpro_webhook_url_is_allowed()` (блок localhost и private IP).

При показе (pixel) — `bannerpro_dispatch_webhook(..., 'bannerpro_webhook_impression_url')` и `bannerpro_impression_webhook_payload()` (с 4.8.0). MODX-событие `OnBannerProImpression` не требует HTTP webhook.

Полезная нагрузка: `bannerpro_event_payload()` + `recorded`, `duplicate`, `ip`; для клика — `referrer`, `redirect_url`, `click_id`, `timestamp`.

Клиент: `bannerpro:impression` (CustomEvent из `impression.js`), очередь `bannerproImpressionQueue` до загрузки моста. GA4/Matomo/Метрика: [integrations/analytics.md](integrations/analytics.md).

Сниппет: алиас `positionKey` → `positionName` в `bannerpro_render_snippet()`. Фильтр по `context_key` позиции: `bannerpro_apply_position_context_filter()` и `bannerpro_resolve_snippet_context()` в `query.php`; параметр `&context=` (пусто или `*` — текущий контекст MODX); позиции с пустым `context_key` видны везде.

Фильтр позиций в connector `ads_getlist`: при `mode=include` и пустой позиции возвращается пустой список (не все баннеры).

### Кэш сниппета (`include/cache.php`)

| Функция | Назначение |
|---------|------------|
| `bannerpro_cache_enabled()` | `bannerpro_cache` + переопределение `&cache` |
| `bannerpro_cache_is_eligible()` | Исключения: `RAND()`, `weighted`, `showLog`+mgr, `toSeparatePlaceholders` |
| `bannerpro_cache_key()` | `snippet/` + sha1(activeContext, culture, sortby, hour bucket, props) |
| `bannerpro_cache_get()` / `bannerpro_cache_set()` | Partition `bannerpro`, тег `bannerpro` |
| `bannerpro_cache_invalidate()` | `cacheManager->clean` по тегу — вызывается из `ads.php` и `positions.php` после мутаций |

Интеграция: начало и конец `bannerpro_render_snippet()` в `frontend.php`. Ключ кэша включает все параметры вызова (в т.ч. `productId`).

### MiniShop3 (`include/products.php`)

| Функция | Назначение |
|---------|------------|
| `bannerpro_collect_product_ids()` | Сбор уникальных `product_id` из строк выборки |
| `bannerpro_load_product_resources()` | Один xPDO-запрос `modResource` по списку ID |
| `bannerpro_enrich_product_placeholders()` | Плейсхолдеры `product_*` в строке баннера |
| `bannerpro_product_pagetitle()` | Заголовок товара для API `ads_get` |
| `bannerpro_apply_product_filter()` | Условие WHERE для параметра `&productId=` |

Сниппет загружает товары **до** цикла рендера чанков (без N+1).

Пикер в Vue: `include/resources.php` — `resource_getlist` с `class_key:LIKE '%msProduct'`, `published=1`, `pagetitle:LIKE` при `query`, лимит до 200.

### Медиа и отображение в гриде (`include/media.php`)

`bannerpro_format_ad_url_display()` — колонка URL в админке: `(id) pagetitle` для `[[~id]]`, без сырого HTML.

### Демо QA (`core/elements/templates/demo/bannerpro_test.tpl`)

Smoke-страница с секциями 0–22. Секция 19: `analytics-mock.js` + фолбэк `analytics.js`, если плагин не активен. При Fenom `{extends}` переменные только внутри `{block}` — иначе статус плагина в таблице будет ложным. См. [integrations/analytics.md](integrations/analytics.md).

## REST API (read-only)

**URL:** `{assets_url}components/bannerpro/api.php`
**Метод:** `GET`
**Аутентификация:** `Authorization: Bearer {bannerpro_api_key}` — значение из **Система → Настройки системы** → `bannerpro_api_key`
**Включение:** `bannerpro_api_enabled`

Маршруты в `include/rest.php`: `GET /ads`, `GET /ads/{id}`, `GET /positions`, `GET /stats`.

Подробнее: [rest-api.md](rest-api.md).

## Vue-админка

- Entry: `assets/components/bannerpro/js/mgr/src/entry.js`
- Конфиг с PHP: `window.bannerproConfig` (`connectorUrl`, `lexicon`, `mediaSource`, `permissions`)
- Mount: `#bannerpro-mgr-app`
- API: composable `useBannerProApi.js`

Сборка: `vite.mgr.config.js` → `bannerpro-admin.min.js`.

## Лексиконы

| Топик | Файлы |
|-------|-------|
| UI админки | `lexicon/{lang}/default.inc.php` |
| Свойства сниппета | `lexicon/{lang}/properties.inc.php` |
| Настройки | `lexicon/{lang}/setting.inc.php` |

Загрузка для менеджера и connector: `bannerpro_load_lexicon()` в `include/lexicon.php` — сначала `bannerpro:default`, затем `{manager_language}:bannerpro:default` (паттерн как в msRussianPost).

Список ключей для Vue: `bannerpro_default_lexicon_keys()` читает все ключи из `lexicon/en/default.inc.php` (без ручного whitelist) + `yes`/`no`/`search` → `bannerpro_build_mgr_lexicon()` → `window.bannerproConfig.lexicon`. В JS: `lex(key)` из `request.js`; для audit — `auditLabels.js`.

Языки: **ru**, **en**, **uk** — `default.inc.php`, `properties.inc.php`, `setting.inc.php` в каждой локали. В MODX для украинского: `manager_language` = `uk`.

## Расширение

Новое действие connector:

1. Запись в `bannerpro_action_handlers()` — `[callable, permission|null]`
2. Обработчик в `include/handlers.php` или отдельный файл в `include/`
3. Метод в `useBannerProApi.js` и вызов из Vue-компонента
4. Опционально: `permissions` в UI (`getConfig().permissions`)

## Документация для пользователей

- [frontend-guide.md](frontend-guide.md) — Fenom на сайте
- [manager-guide.md](manager-guide.md) — админка
- [integrations/minishop3.md](integrations/minishop3.md) — MiniShop3
- [faq.md](faq.md) — диагностика
- [rest-api.md](rest-api.md) — REST API
- [roadmap.md](roadmap.md) — план развития
- [integrations/analytics.md](integrations/analytics.md) — внешняя аналитика
