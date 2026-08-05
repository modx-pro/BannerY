<article class="bannerpro-demo-ad mb-2 p-2 border border-success rounded" data-banner-id="{$id}" data-adposition="{$adposition}">
  <span class="badge bg-success mb-1">tplFirst</span>
  <a href="{$_modx->config.bannerpro_click}/{$adposition}"
     data-bannerpro-adposition="{$adposition}"
     data-bannerpro-ad-id="{$id}"
     data-bannerpro-ad-name="{$name|escape}"
     data-bannerpro-position-name="{$position_name|default:''|escape}"
     class="d-block text-decoration-none">
    {if $image}
      <img src="{$image}" alt="{$name|escape}" title="{$description|escape}" loading="lazy" class="img-fluid rounded" />
    {else}
      <strong>{$name|escape}</strong>
    {/if}
    {if $description}<div class="small text-muted mt-1">{$description|escape}</div>{/if}
    <div class="small text-muted mt-1">#{$id} · pos {$adposition} · {$type}</div>
  </a>
</article>
