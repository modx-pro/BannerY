{* Чанк byAdProduct — баннер с плейсхолдерами товара MiniShop3 *}
<div class="bannerpro-ad bannerpro-ad--product">
  {if $product_thumb}
    <a href="{$click_url|escape:'html'}" class="bannerpro-ad__image"
       data-bannerpro-adposition="{$adposition}"
       data-bannerpro-ad-id="{$id}">
      <img src="{$product_thumb}" alt="{$product_title}" loading="lazy" />
    </a>
  {elseif $image}
    <a href="{$click_url|escape:'html'}" class="bannerpro-ad__image"
       data-bannerpro-adposition="{$adposition}"
       data-bannerpro-ad-id="{$id}">
      <img src="{$image}" alt="{$name}" loading="lazy" />
    </a>
  {/if}
  <div class="bannerpro-ad__body">
    {if $product_title}
      <div class="bannerpro-ad__title">{$product_title}</div>
      {if $product_price}
        <div class="bannerpro-ad__price">
          {if $product_old_price}<s>{$product_old_price}</s>{/if}
          <span>{$product_price}</span>
        </div>
      {/if}
    {else}
      <div class="bannerpro-ad__title">{$name}</div>
    {/if}
    {if $description}<p class="bannerpro-ad__text">{$description}</p>{/if}
  </div>
</div>
