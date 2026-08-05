# Установка BannerPro

## Требования

| Компонент | Версия | Назначение |
|-----------|--------|------------|
| MODX Revolution | ≥ 3.0.0 | Платформа |
| PHP | ≥ 8.2.0 | Runtime |
| [pdoTools](https://modx.pro/components/pdotools) | ≥ 2.1.0 | Сниппет `BannerPro` (pdoFetch) |
| [VueTools](https://docs.modx.pro/components/vuetools/) | актуальная | Админка (Vue 3 + PrimeVue) |
| [MiniShop3](https://docs.modx.pro/components/minishop3/) | опционально | `product_id`, плейсхолдеры `product_*` |

При установке пакета resolver `resolver_setup.php` пытается установить **pdoTools** из репозитория MODX (modstore / simpledream), если пакет ещё не установлен.

## Установка из транспорта

### 1. Сборка пакета

Из корня репозитория (нужен доступ к установленному MODX — `_build/config.inc.php` находит `core/config/config.inc.php`):

```bash
npm install
npm run build
php _build/build.php
```

`package.json` и `vite.mgr.config.js` лежат в **корне** репозитория; Vite собирает исходники из `assets/components/bannerpro/js/mgr/` в `vue-dist/`.

Архив появится в `core/packages/bannerpro-{version}-pl.transport.zip` (версия из `_build/config.inc.php`, сейчас **4.5.0-pl**).

`build.php` при наличии `package.json` и `vite.mgr.config.js` сам запускает `npm run build` из корня репозитория.

Скачать через браузер (если `download=1` в config):

```
https://ваш-сайт/Extras/BannerPro/_build/build.php?download=1
```

### 2. Установка в MODX

1. **Пакеты** → загрузить `.transport.zip` или положить в `core/packages/`
2. Установить **BannerPro**
3. Убедиться, что установлены **pdoTools** и **VueTools**
4. Очистить кэш

### 3. Проверка

- В меню **Компоненты** → **BannerPro** открывается Vue-админка
- На сайте вызов сниппета не возвращает ошибку про pdoTools

## Сборка админки (разработка)

```bash
npm install
npm run build
```

Результат: `assets/components/bannerpro/js/mgr/vue-dist/bannerpro-admin.min.js` (+ `.css`).

## Первый запуск на сайте

1. Создайте позицию и баннер в **Компоненты → BannerPro**.
2. Вставьте вывод в шаблон (Fenom):

```fenom
{'!BannerPro' | snippet : ['positionName' => 'sidebar', 'tpl' => 'byAd']}
```

3. Включите плагин **BannerProClickout** на событии `OnPageNotFound`.
4. Очистите кэш MODX.

Подробнее: [frontend-guide.md](frontend-guide.md).

## Демо-страница и smoke-тест

В репозитории пакета:

| Файл | Назначение |
|------|------------|
| `core/elements/templates/demo/bannerpro_test.tpl` | Шаблон QA-стенда (секции 0–22) |
| `core/elements/demo/seed_bannerpro_demo.php` | Демо-позиции, баннеры, настройки, включение плагинов |
| `_build/verify-demo-page.mjs` | Автопроверка страницы в браузере |

После установки пакета на сайт:

```bash
# из корня MODX
php core/elements/demo/seed_bannerpro_demo.php
php clear_cache.php
```

Откройте ресурс с шаблоном `bannerpro_test` (в сидере — `bannerpro-test.html`). Секция **19** проверяет аналитику без реальных счётчиков GA4/Метрики.

Автотест (из клона репозитория BannerPro):

```bash
npm install   # при первом запуске
BANNERPRO_DEMO_URL=https://ваш-сайт/bannerpro-test.html node _build/verify-demo-page.mjs
```

Автотест и аналитика на демо: [integrations/analytics.md](integrations/analytics.md), [faq.md](faq.md).

## Скриншоты админки для документации

Готовые PNG лежат в `docs/images/manager/`. Переснять после изменений UI:

```bash
# нужна авторизация в менеджере MODX
node _build/capture-manager-screenshots.mjs
```

Переменные: `BANNERPRO_MGR_URL`, `MODX_MGR_USER`, `MODX_MGR_PASSWORD`. Скрипт сохраняет области `#modx-content` и диалог формы баннера.

## Структура файлов после установки

```
core/components/bannerpro/     — PHP, модель, лексиконы, элементы
assets/components/bannerpro/   — connector, JS/CSS админки, vue-dist
```

Namespace MODX: `bannerpro`.
