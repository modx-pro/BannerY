# Lead-баннер с FormIt (cookbook)

BannerPro не включает FormIt в ядро. Связка «баннер → landing с формой» делается через **HTML-баннер**, плейсхолдер `{click_url}` и страницу с FormIt в Fenom-шаблоне.

**Требования:** BannerPro 4.6+, FormIt, страница-лендинг с формой.

---

## Схема

```text
Баннер (HTML) → клик → clickout → landing с FormIt
                      ↳ UTM в URL (опционально, 4.4+)
```

---

## Шаг 1. Landing с FormIt

Создайте ресурс, например «Заявка с баннера»:

```fenom
{'!FormIt' | snippet : [
  'hooks' => 'email',
  'emailSubject' => 'Заявка с баннера',
  'emailTo' => 'manager@example.com',
  'validate' => 'name:required,email:email:required'
]}
<form method="post">
  <input type="text" name="name" placeholder="Имя" />
  <input type="email" name="email" placeholder="Email" />
  <input type="hidden" name="source" value="bannerpro" />
  <button type="submit">Отправить</button>
</form>
{if $_modx->getPlaceholder('fi.error.name')}
  <p class="error">{$_modx->getPlaceholder('fi.error.name')}</p>
{/if}
{if $_modx->getPlaceholder('fi.error.email')}
  <p class="error">{$_modx->getPlaceholder('fi.error.email')}</p>
{/if}
```

---

## Шаг 2. HTML-баннер в BannerPro

Тип баннера: **HTML**. Чанк `tplHtml`:

```fenom
<div class="bannerpro-lead">
  <p>{$name|escape}</p>
  <a href="{$click_url|escape:'html'}" class="btn">Оставить заявку</a>
</div>
```

URL баннера в админке можно оставить пустым — clickout пройдёт через BannerPro.

---

## Шаг 3. UTM (опционально)

**Настройки → BannerPro → UTM** — см. [configuration.md](../configuration.md).

---

## Шаг 4. Вызов сниппета

```fenom
{'!BannerPro' | snippet : [
  'positionName' => 'lead-sidebar',
  'tplHtml' => '@FILE chunks/byad-lead.fenom.tpl',
  'limit' => 1
]}
```

---

## Отличие от MS3 attribution

FormIt не создаёт заказ MS3. Для e-commerce — [minishop3.md](minishop3.md).

---

## Связанные документы

- [frontend-guide.md](../frontend-guide.md) — `{click_url}`, `sortby`, кэш
- [configuration.md](../configuration.md) — UTM, webhook
