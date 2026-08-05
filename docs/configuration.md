# Настройки BannerPro

Системные настройки: **Система → Настройки системы**, фильтр namespace `bannerpro`.

Область (area): `bannerpro`. Лексикон подписей: `bannerpro:setting`.

## bannerpro_click

| | |
|--|--|
| **Тип** | textfield |
| **По умолчанию** | `bannerclick` |
| **Назначение** | Сегмент URL для учёта клика по баннеру |

Плагин `BannerProClickout` перехватывает запросы вида:

```
/{bannerpro_click}/{adposition_id}
```

Пример при значении `bannerclick`:

```
https://example.com/bannerclick/42
```

где `42` — ID записи в таблице `bannerpro_ads_positions` (плейсхолдер `adposition` в чанке), **не** ID баннера и не ID позиции.

### Использование в шаблонах

В чанках баннера используйте готовый плейсхолдер **`{$click_url}`** (рекомендуется, с 4.6.0):

```fenom
<a href="{$click_url|escape:'html'}">...</a>
```

Ручная сборка (если нужен свой query string):

```fenom
<a href="{'bannerpro_click' | option}/{$adposition}">...</a>
```

### Рекомендации

- Используйте короткий латинский slug без слэшей (`bannerclick`, `go`, `bnr`)
- Не меняйте значение на проде без обновления чанков — иначе сломаются старые ссылки в кэше страниц

## bannerpro_media_source

| | |
|--|--|
| **Тип** | numberfield |
| **По умолчанию** | пусто (fallback: `default_media_source`) |
| **Назначение** | Media Source по умолчанию для URL изображений баннеров |

Если у баннера в админке указан свой **Источник файлов** (`byAd.source`), используется он. Иначе — эта настройка.

### Когда менять

- Все баннеры лежат в одном источнике, отличном от системного по умолчанию
- После смены media source, если картинки «пропали» — проверьте ID media source

## bannerpro_cache

| | |
|--|--|
| **Тип** | combo-boolean |
| **По умолчанию** | `1` (включено) |
| **Назначение** | Глобально включить кэш готового HTML сниппета BannerPro |

Кэш хранится в partition `bannerpro` с тегом `bannerpro`. При изменении баннеров или позиций в админке кэш сбрасывается автоматически.

Кэш **не используется**, если:

- `sortby` пустой, `RAND()` или `weighted` (ротация)
- `&cache=0` в вызове сниппета
- `&toSeparatePlaceholders` задан
- `&showLog=1` и открыта сессия менеджера

Подробнее: [frontend-guide.md](frontend-guide.md#кэширование-вывода).

## bannerpro_cache_lifetime

| | |
|--|--|
| **Тип** | numberfield |
| **По умолчанию** | `3600` (1 час) |
| **Назначение** | TTL кэша сниппета в секундах |

Переопределяется в вызове параметром `&cacheLifetime`. Значение `0` отключает запись в кэш (чтение по-прежнему зависит от `bannerpro_cache` / `&cache`).

Ключ кэша включает часовой «bucket» (`Y-m-d-H`), чтобы фильтры `start` / `end` обновлялись без бесконечно устаревшего HTML.

## bannerpro_track_impressions

| | |
|--|--|
| **Тип** | combo-boolean |
| **По умолчанию** | `0` (выключено) |
| **Назначение** | Учёт показов баннеров на сайте |

При включении сниппет оборачивает каждый баннер в `<span data-bannerpro-impression="…">` и подключает `assets/components/bannerpro/js/impression.js`. Плагин **BannerProImpression** должен быть активен на **OnPageNotFound**.

## bannerpro_impression

| | |
|--|--|
| **Тип** | textfield |
| **По умолчанию** | `bannerimpression` |
| **Назначение** | Сегмент URL для pixel показов |

Запрос вида `/{bannerpro_impression}/{adposition}` записывает показ (1 раз с IP в сутки на баннер+позицию) и возвращает `204 No Content`.

## bannerpro_clicks_retention_days / bannerpro_impressions_retention_days

| | |
|--|--|
| **Тип** | numberfield |
| **По умолчанию** | `365` |
| **Назначение** | Удалять записи старше N дней (`0` = не удалять) |

Очистка через cron:

```bash
php core/components/bannerpro/cron/purge.php
```

На нагруженных сайтах — раз в сутки.

## Внешняя аналитика (с 3.8.0)

Плагин **BannerProAnalytics** (выключен по умолчанию) подключает `analytics.js` на `OnLoadWebDocument`, если включена настройка ниже.

| Ключ | Тип | По умолчанию | Назначение |
|------|-----|--------------|------------|
| `bannerpro_analytics_enabled` | boolean | `0` | Загрузить клиентский мост GA4/Matomo/Метрика |
| `bannerpro_analytics_ga4` | boolean | `1` | События в `dataLayer` |
| `bannerpro_analytics_ga4_click` | text | `bannerpro_click` | Имя события GA4 при клике |
| `bannerpro_analytics_ga4_impression` | text | `bannerpro_impression` | Имя события GA4 при показе |
| `bannerpro_analytics_matomo` | boolean | `1` | `trackEvent` в Matomo (`_paq`) |
| `bannerpro_analytics_ym_counter` | text | пусто | ID счётчика Яндекс Метрики |
| `bannerpro_analytics_ym_click_goal` | text | `bannerpro_click` | Цель `reachGoal` при клике |
| `bannerpro_analytics_ym_impression_goal` | text | `bannerpro_impression` | Цель `reachGoal` при показе |

Серверные хуки MODX (`OnBannerProClick`, `OnBannerProImpression`) работают **без** этих настроек — для своих плагинов.

Проверка без продакшен-счётчиков: демо `bannerpro-test.html` (секция 19), сидер `core/elements/demo/seed_bannerpro_demo.php`. Детали: [integrations/analytics.md](integrations/analytics.md).

## Атрибуция клик → заказ (с 4.2.0)

| Ключ | Тип | По умолчанию | Назначение |
|------|-----|--------------|------------|
| `bannerpro_attribution_ttl` | numberfield | `30` | Срок cookie `bannerpro_click_id` в днях после клика (для связи с заказом MiniShop3) |

Нужны плагин **BannerProMiniShop3** и событие `msOnSubmitOrder`. [integrations/minishop3.md](integrations/minishop3.md).

## REST API (с 4.3.0)

| Ключ | Тип | По умолчанию | Назначение |
|------|-----|--------------|------------|
| `bannerpro_api_enabled` | boolean | `0` | Включить read-only REST API |
| `bannerpro_api_key` | text | автогенерация | Bearer-токен для `assets/components/bannerpro/api.php` |

**REST API ключ** — секрет для заголовка `Authorization: Bearer …` в `api.php`. Смотрите в **Система → Настройки системы** (namespace `bannerpro`). Resolver создаёт 32-символьный hex при установке/upgrade, если поле пустое. Эндпоинты и примеры `curl`: [rest-api.md](rest-api.md).

## UTM при клике (с 4.4.0)

| Ключ | Тип | По умолчанию | Назначение |
|------|-----|--------------|------------|
| `bannerpro_utm_enabled` | boolean | `0` | Добавлять UTM к URL редиректа при клике |
| `bannerpro_utm_source` | text | `bannerpro` | `utm_source` |
| `bannerpro_utm_medium` | text | `banner` | `utm_medium` |
| `bannerpro_utm_campaign` | text | *(пусто)* | `utm_campaign`; пусто — не добавлять; плейсхолдеры `{ad_id}`, `{position_id}`, `{adposition}`, `{product_id}`, `{product_pagetitle}`, `{order_id}` |
| `bannerpro_utm_content` | text | `{ad_id}-{adposition}` | Шаблон `utm_content`; те же плейсхолдеры |

На clickout `{order_id}` всегда `0` (заказ ещё не создан). `{product_pagetitle}` берётся из `product_id` баннера.

| `bannerpro_ms_catalog_positions` | text | *(пусто)* | Имена позиций через запапятую для листинга MS3 (`catalog_row`, …). Плагин BannerProMiniShop3 заполняет `bannerpro_positions` на каждом товаре. |

## Фильтр ботов (с 4.6.0)

| Ключ | Тип | По умолчанию | Назначение |
|------|-----|--------------|------------|
| `bannerpro_filter_bots` | boolean | `0` | Не записывать клики и показы от ботов в статистику |

Soft-зависимость от дополнения **CrawlerDetect** (сниппет `isCrawler` или `crawlerdetect.core_path`). Без CrawlerDetect при включённой настройке — предупреждение в лог MODX, фильтр не применяется. Редирект по клику и pixel показа работают как обычно.

| Настройка | CrawlerDetect | Поведение |
|-----------|---------------|-----------|
| Выкл | — | Все запросы в статистике |
| Вкл | Установлен | Боты не пишутся в `byClick` / `byImpression` |
| Вкл | Нет | Warn в лог, статистика без фильтра |

## Lazy-load показов (с 4.6.0)

| Ключ | Тип | По умолчанию | Назначение |
|------|-----|--------------|------------|
| `bannerpro_impression_lazy` | boolean | `1` | Подключать `impression.js` через IntersectionObserver / requestIdleCallback, а не блокирующим `<script>` в head |

При `0` — прежнее поведение (синхронная регистрация скрипта).

Существующие UTM в URL баннера не перезаписываются. Подробнее: [frontend-guide.md](frontend-guide.md).

## Webhook при клике (с 4.5.0)

| Ключ | Тип | По умолчанию | Назначение |
|------|-----|--------------|------------|
| `bannerpro_webhook_url` | text | *(пусто)* | URL для POST JSON при клике; пусто — выключено |
| `bannerpro_webhook_secret` | text | *(пусто)* | Секрет для заголовка `X-BannerPro-Signature` (HMAC-SHA256); пусто — без подписи |

Запрос отправляется **после** учёта клика и события `OnBannerProClick`, **до** редиректа; timeout 2 с, ошибки не блокируют переход.

Тело JSON: `event` (`click`), `ad_id`, `position_id`, `adposition`, `referrer`, `ip`, `click_id`, `timestamp` (ISO8601), `redirect_url`, `recorded`, `duplicate`.

URL должен быть `http://` или `https://`. Запрещены `localhost`, loopback и private/reserved IP в hostname (защита от SSRF).

Проверка: [httpbin.org/post](https://httpbin.org/post) или локальный mock-сервер.

## Webhook при показе (с 4.8.0)

| Ключ | Тип | По умолчанию | Назначение |
|------|-----|--------------|------------|
| `bannerpro_webhook_impression_url` | text | *(пусто)* | URL для POST JSON при pixel-показе; пусто — выключено |

Подпись — тот же `bannerpro_webhook_secret`, что и для клика. Запрос fire-and-forget после `OnBannerProImpression`, timeout 2 с; не блокирует ответ `204`.

Тело JSON: `event` (`impression`), `ad_id`, `position_id`, `adposition`, `ip`, `timestamp` (ISO8601), `recorded`, `duplicate`.

MODX-событие `OnBannerProImpression` работает **без** этой настройки — webhook только для внешних HTTP-endpoint.

## A/B split (с 4.8.0)

| Ключ | Тип | По умолчанию | Назначение |
|------|-----|--------------|------------|
| `bannerpro_ab_ttl` | numberfield | `30` | Срок cookie `bannerpro_ab_{positionId}` при `&sortby=ab` |

Подробнее: [frontend-guide.md](frontend-guide.md#a-b-split-sortbyab).

## Права доступа (с 4.1.0)

При установке/обновлении resolver создаёт permissions и привязывает их к шаблонам политик MODX 3 (`modAccessPolicyTemplate`: Administrator, Manager и шаблон политики Manager):

| Permission | Назначение |
|------------|------------|
| `bannerpro_save` | Создание/редактирование баннеров и позиций, вес, порядок |
| `bannerpro_remove` | Удаление баннеров и позиций |
| `bannerpro_stats` | Статистика, экспорт, просмотр кликов |

Значения `true` также записываются в данные политик **Administrator** и **Manager**. Для других ролей назначьте права в **Безопасность → Политики доступа**.

Просмотр менеджера (`view`) по-прежнему обязателен для входа в компонент.

## Доступ к настройкам в шаблонах

```fenom
{'bannerpro_click' | option}
{'bannerpro_media_source' | option}
{'bannerpro_cache' | option}
{'bannerpro_cache_lifetime' | option}
```
