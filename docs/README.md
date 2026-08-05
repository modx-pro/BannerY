# Документация BannerPro

Индекс материалов в корне репозитория (`docs/`).

**Версия:** 4.5.0-pl · **Требования:** MODX 3.0+, PHP 8.2+, pdoTools (фронтенд), VueTools (админка). MiniShop3 — опционально.

## Быстрый старт

| Документ | Описание |
|----------|----------|
| [installation.md](installation.md) | Установка, сборка транспорта, npm, демо-стенд |
| [configuration.md](configuration.md) | Системные настройки `bannerpro_*` (кэш, UTM, webhook, REST) |
| [frontend-guide.md](frontend-guide.md) | Сниппет `BannerPro`, чанки, клики, ротация, лимиты |
| [integrations/minishop3.md](integrations/minishop3.md) | MiniShop3: `product_id`, атрибуция клик → заказ |
| [integrations/formit.md](integrations/formit.md) | FormIt: lead-баннер через `{click_url}` и landing (4.8.0) |
| [integrations/analytics.md](integrations/analytics.md) | GA4, Matomo, Яндекс Метрика: события MODX и плагин BannerProAnalytics |

## Админка и разработка

| Документ | Описание |
|----------|----------|
| [manager-guide.md](manager-guide.md) | Баннеры, позиции, статистика, права доступа; скриншоты в [images/manager/](images/manager/) |
| [developer-guide.md](developer-guide.md) | Архитектура, connector API, REST API, ACL |
| [rest-api.md](rest-api.md) | Read-only REST API (Bearer) |
| [roadmap.md](roadmap.md) | Роадмап и сравнение с аналогами |
| [faq.md](faq.md) | Частые вопросы и диагностика |

## Примеры шаблонов

| Файл | Описание |
|------|----------|
| [examples/chunk-byad.fenom.tpl](examples/chunk-byad.fenom.tpl) | Базовый Fenom-чанк баннера |
| [examples/chunk-byad-text.fenom.tpl](examples/chunk-byad-text.fenom.tpl) | Текстовый баннер без изображения |
| [examples/chunk-wrapper.fenom.tpl](examples/chunk-wrapper.fenom.tpl) | Обёртка для блока баннеров |
| [examples/page-sidebar.fenom.tpl](examples/page-sidebar.fenom.tpl) | Вставка в сайдбар страницы |
| [examples/chunk-byad-product.fenom.tpl](examples/chunk-byad-product.fenom.tpl) | Баннер с плейсхолдерами товара MS3 |
| [../core/elements/templates/demo/bannerpro_test.tpl](../core/elements/templates/demo/bannerpro_test.tpl) | Демо-страница QA |
| [../core/elements/demo/seed_bannerpro_demo.php](../core/elements/demo/seed_bannerpro_demo.php) | Сидер демо-данных |
| [../_build/verify-demo-page.mjs](../_build/verify-demo-page.mjs) | Автотест демо-страницы (`BANNERPRO_DEMO_URL`) |

## В пакете MODX (transport)

В `core/components/bannerpro/docs/`: **changelog.txt**, **readme.txt**, **license.txt**.

Корневой [README](../README) — краткая справка.
