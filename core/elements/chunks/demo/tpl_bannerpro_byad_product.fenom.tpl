<div class="bannerpro-demo-product border rounded p-2 mb-2" data-banner-id="{$id}" data-adposition="{$adposition}">
  <a href="{$click_url|escape:'html'}" class="text-decoration-none">
    {if $product_id > 0 && $product_thumb}
      <img src="{$product_thumb}" alt="{$product_title|escape}" loading="lazy" class="img-fluid rounded mb-1" />
    {elseif $image}
      <img src="{$image}" alt="{$name|escape}" title="{$description|escape}" loading="lazy" class="img-fluid rounded mb-1" />
    {/if}
    <strong>
      {if $product_id > 0 && $product_title}
        {$product_title|escape}
      {else}
        {$name|escape}
      {/if}
    </strong>
    {if $description}<div class="small text-muted">{$description|escape}</div>{/if}
    {if $product_id > 0 && $product_price}<div class="small">{$product_price|escape}</div>{/if}
    {if $product_id > 0 && $product_old_price}<div class="small text-muted"><s>{$product_old_price|escape}</s></div>{/if}
    <div class="small text-muted mt-1">#{$id} · pos {$adposition} · {$type}{if $product_id > 0} · product {$product_id}{/if}</div>
  </a>
</div>
