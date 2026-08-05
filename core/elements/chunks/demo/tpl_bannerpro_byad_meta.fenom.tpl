<article class="bannerpro-demo-ad bannerpro-demo-ad--meta mb-2 p-2 border rounded" data-banner-id="{$id}" data-adposition="{$adposition}">
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
    <div class="small text-muted mt-1">
      #{$id} · pos {$adposition} · {$type}
      {if is_array($tags) && $tags|count > 0}<span class="ms-1">tags: {$tags|join:', '|escape}</span>{elseif $tags|default:''}<span class="ms-1">tags: {$tags|escape}</span>{/if}
      {if $weight|default:0 > 0}<span class="ms-1">w={$weight}</span>{/if}
    </div>
    <div class="small mt-1"><code class="text-break user-select-all">{$click_url|escape:'html'}</code></div>
  </a>
</article>
