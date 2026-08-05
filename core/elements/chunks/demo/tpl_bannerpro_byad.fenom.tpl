<article class="bannerpro-demo-ad mb-2 p-2 border rounded" data-banner-id="{$id}" data-adposition="{$adposition}">
  <a href="{$click_url|escape:'html'}"
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
