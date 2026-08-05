# Интеграция с MiniShop3

Опциональная связка BannerPro с магазином на **MiniShop3**. Ядро BannerPro работает без MS3; при установленном магазине доступны привязка баннера к товару или категории, позиции в листинге каталога и плейсхолдеры в чанках.

**Требования:** MODX 3, MiniShop3, pdoTools, товары с `class_key = msProduct`, категории `msCategory`, TV `price`, `old_price`, `thumb` (или `image`).

---

## Модель

В таблице `bannerpro_ads` поля **`product_id`** и **`category_id`** (ID ресурсов MODX):

| product_id | category_id | показ при `&productId=` |
|------------|-------------|-------------------------|
| 0 | 0 | глобальный |
| > 0 | 0 | только этот товар |
| 0 | > 0 | товары, у которых категория — предок в цепочке `parent` |
| > 0 | > 0 | **запрещено** (валидация в админке и API) |

`product_id` и `category_id` взаимоисключающие: при выборе товара категория сбрасывается и наоборот.

---

## Админка

### Товар

В форме баннера: **«Товар (MiniShop3)»** — MultiSelect с серверным поиском (`resource_getlist`, `class_key = msProduct`).

| Поведение | Описание |
|-----------|----------|
| Поиск | Фильтр → `pagetitle LIKE`, до 200 товаров |
| Кнопки | **Подставить URL** → `[[~{id}]]`; clear → `product_id = 0` |

### Категория (4.6.0)

**«Категория (MiniShop3)»** — MultiSelect с поиском (`class_key = msCategory`).

- Баннер показывается на всех товарах, у которых `category_id` баннера входит в цепочку родителей товара.
- Выбор категории сбрасывает привязку к товару.

На вкладке «Баннеры»: опциональный фильтр и колонка категории.

Connector: `include/resources.php` (действие `resource_getlist`).

---

## Сниппет BannerPro

### Параметр `productId`

| Параметр | Тип | Описание |
|----------|-----|----------|
| `productId` | int | ID текущего товара. Глобальные баннеры + баннеры с тем же `product_id` + баннеры с `category_id` из цепочки родителей товара. |

**Fenom (карточка товара):**

```fenom
{'!BannerPro' | snippet : [
  'positionName' => 'shop-product-sidebar',
  'productId' => $_modx->resource.id,
  'tpl' => 'byAdProduct',
  'limit' => 3
]}
```

На каталоге и главной **`productId` не передавайте** — выводятся только глобальные баннеры (без product/category targeting).

### Плейсхолдеры в чанке

Добавляются в `bannerpro_prepare_ad_row()` при контексте товара. Данные подгружаются одним запросом (`bannerpro_load_product_resources`).

| Плейсхолдер | Источник |
|-------------|----------|
| `product_id` | ID товара (контекст или баннер) |
| `product_title` | `pagetitle` |
| `product_url` | полный URL |
| `product_price` | TV `price` |
| `product_old_price` | TV `old_price` |
| `product_thumb` | TV `thumb` или `image` |

---

## Позиции каталога msProducts (4.6.0)

Настройка **`bannerpro_ms_catalog_positions`**: список имён позиций через запятую, например:

```
catalog_row,product_card
```

Плагин **BannerProMiniShop3** на событиях `msOnProductPrepare` / `msOnProductsLoad` заполняет на каждом товаре:

| Поле | Формат |
|------|--------|
| `bannerpro_ads` | плоский список баннеров (legacy, как раньше) |
| `bannerpro_positions` | map `{positionName => [ prepared ad rows ]}` |

Подготовленные строки включают `adposition`, `image`/`html`, плейсхолдеры **текущего** товара в листинге (для глобальных и category-баннеров).

### usePackages в msProducts

```fenom
{'!msProducts' | snippet : [
  'parents' => 10,
  'tpl' => 'tpl.msProducts.row',
  'usePackages' => 'bannerpro'
]}
```

### Пример чанка строки каталога

```fenom
<div class="ms-product-row">
  <a href="{$id | url}">{$pagetitle}</a>
  {if $bannerpro_positions.catalog_row?}
    {foreach $bannerpro_positions.catalog_row as $ad}
      <a href="/{$_modx->config.bannerpro_click}/{$ad.adposition}">
        <img src="{$ad.image}" alt="{$ad.name}" loading="lazy" />
      </a>
    {/foreach}
  {/if}
</div>
```

Для legacy-списка без позиций по имени:

```fenom
{foreach $bannerpro_ads as $ad}
  ...
{/foreach}
```

---

## UTM при клике (4.6.0)

В настройках `bannerpro_utm_campaign` и `bannerpro_utm_content` доступны плейсхолдеры:

| Плейсхолдер | Источник на clickout |
|-------------|----------------------|
| `{product_id}` | `product_id` баннера |
| `{product_pagetitle}` | название товара баннера |
| `{order_id}` | `0` (заказ ещё не создан) |

Пример: `bannerpro_utm_content = {product_pagetitle}-{ad_id}`.

---

## Cookbook: позиции магазина

| Позиция | Где вызывать | `productId` | Назначение |
|---------|--------------|-------------|------------|
| `shop-catalog-top` | шаблон категории | — | Промо над листингом |
| `catalog_row` | `msProducts` + plugin | — | Баннер в строке товара (через `bannerpro_positions`) |
| `shop-product-sidebar` | карточка товара | `{$_modx->resource.id}` | Допродажи к товару |
| `shop-cart-top` | корзина | — | Глобально |

**Категорийный баннер:** создайте баннер с `category_id = ID msCategory`, позиция `shop-catalog-inline`, вызов сниппета на странице категории с `&productId=` каждого товара в карточке или через plugin в msProducts.

---

## Плагин BannerProMiniShop3

### Установка

1. **MiniShop3** установлен.
2. **Элементы → Плагины → BannerProMiniShop3** — включите.
3. События: `msOnProductPrepare`, `msOnProductsLoad`, `msOnSubmitOrder`.

### События

| Событие | Назначение |
|---------|------------|
| `msOnProductPrepare` | `bannerpro_ads`, `bannerpro_positions` на товаре |
| `msOnProductsLoad` | bulk-load для списка товаров |
| `msOnSubmitOrder` | связь клика с заказом (cookie `bannerpro_click_id`) |

### Атрибуция клик → заказ (4.2.0)

1. Клик по баннеру → cookie `bannerpro_click_id`.
2. `msOnSubmitOrder` → `order_id` в `bannerpro_clicks`.
3. Срок cookie — **bannerpro_attribution_ttl** (дней).

См. [configuration.md](../configuration.md), [roadmap.md](../roadmap.md).
