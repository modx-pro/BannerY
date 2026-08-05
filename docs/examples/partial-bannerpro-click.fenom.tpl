{* Обёртка ссылки клика — include из чанка баннера *}
<a href="{$click_url|escape:'html'}"
   data-bannerpro-adposition="{$adposition}"
   data-bannerpro-ad-id="{$id}"
   {if $description}title="{$description|escape}"{/if}>
  {$content}
</a>
