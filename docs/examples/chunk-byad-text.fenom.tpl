{*
  Текстовый баннер без изображения.
*}
<div class="banner banner--text">
  <a href="{$_modx->config.bannerpro_click}/{$adposition}">
    <strong>{$name|escape}</strong>
    {if $description}
      <span class="banner__desc">{$description|escape}</span>
    {/if}
  </a>
</div>
