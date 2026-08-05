# BannerPro REST API (read-only)

Read-only HTTP API для внешних систем: список баннеров, позиций и сводная статистика.

**Версия:** 4.3.x
**Точка входа:** `assets/components/bannerpro/api.php`

---

## Включение

1. **Система → Настройки системы** → фильтр namespace **`bannerpro`**
2. **`bannerpro_api_enabled`** = **Да**
3. Скопируйте значение **`bannerpro_api_key`** (см. ниже)
4. Запросы только **GET**, заголовок **`Authorization: Bearer …`**

---

## Ключ API

В примерах `curl` плейсхолдер **`YOUR_API_KEY`** — это значение системной настройки **`bannerpro_api_key`**, не отдельный сервис и не ключ MODX-менеджера.

### Где посмотреть

| Способ | Путь |
|--------|------|
| Менеджер | **Система → Настройки системы** → namespace `bannerpro` → **REST API ключ** |
| База | Таблица `modx_system_settings` (префикс может отличаться), ключ `bannerpro_api_key` |

### Как появляется ключ

При установке или обновлении пакета resolver (`resolver_setup.php`) записывает случайную строку **32 hex-символа**, если поле было пустым. Вручную можно задать свой токен: сохраните новое значение в настройке и используйте его в заголовке.

После смены ключа очистите кэш MODX (`php clear_cache.php` или **Управление → Очистить кэш**), если старое значение ещё отдаётся из кэша настроек.

### Пример запроса

Подставьте ключ из настройки **без** префикса `Bearer` в поле — префикс только в HTTP-заголовке:

```bash
API_KEY="вставьте_значение_bannerpro_api_key"

curl -s -H "Authorization: Bearer ${API_KEY}" \
  "https://example.com/assets/components/bannerpro/api.php?path=/ads&limit=10"
```

Ответ `401 unauthorized` — неверный или пустой ключ. `503 api disabled` — выключен `bannerpro_api_enabled`.

---

## Аутентификация

```http
Authorization: Bearer YOUR_API_KEY
```

| Код | Причина |
|-----|---------|
| 401 | Неверный или отсутствующий ключ |
| 503 | API выключен (`bannerpro_api_enabled = 0`) |
| 404 | Неизвестный маршрут |

---

## Формат ответа

Как у connector менеджера:

```json
{
  "success": true,
  "data": [],
  "total": 0
}
```

Ошибки:

```json
{
  "success": false,
  "message": "not found"
}
```

---

## Эндпоинты

Базовый URL: `https://example.com/assets/components/bannerpro/api.php`

Путь передаётся query-параметром `path` или через PATH_INFO (зависит от веб-сервера).
Примеры ниже используют `?path=...`.

### GET /ads

Список баннеров.

| Параметр | Тип | Описание |
|----------|-----|----------|
| `position` | int | ID позиции (опционально) |
| `active` | 0\|1 | Только активные (опционально) |
| `limit` | int | Лимит (по умолчанию 20) |
| `offset` | int | Смещение |

```bash
curl -s -H "Authorization: Bearer YOUR_API_KEY" \
  "https://example.com/assets/components/bannerpro/api.php?path=/ads&limit=10"
```

### GET /ads/{id}

Один баннер с счётчиками кликов, показов и заказов.

```bash
curl -s -H "Authorization: Bearer YOUR_API_KEY" \
  "https://example.com/assets/components/bannerpro/api.php?path=/ads/42"
```

### GET /positions

Список позиций.

```bash
curl -s -H "Authorization: Bearer YOUR_API_KEY" \
  "https://example.com/assets/components/bannerpro/api.php?path=/positions"
```

### GET /stats

Сводная статистика.

| Параметр | Тип | Описание |
|----------|-----|----------|
| `period` | string | `all` (по умолчанию), `today`, `this_month`, `last_month`, `this_year`. Алиасы: `overall`, `thismonth`, `lastmonth`, `thisyear` |
| `position` | int | ID позиции (0 = все) |
| `limit` | int | Лимит для `top_ads` (по умолчанию 10) |

```bash
curl -s -H "Authorization: Bearer YOUR_API_KEY" \
  "https://example.com/assets/components/bannerpro/api.php?path=/stats&period=this_month&position=0"
```

---

## Безопасность

- API **только для чтения** — создание/изменение баннеров через менеджер MODX.
- Храните ключ в секретах (env), не в публичном фронтенде.
- Ограничьте доступ к `api.php` на веб-сервере (IP allowlist), если API только для внутренних сервисов.

---

## См. также

- [configuration.md](configuration.md) — системные настройки
- [developer-guide.md](developer-guide.md) — connector менеджера
