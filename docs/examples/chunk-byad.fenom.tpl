{*
  Базовый Fenom-чанк одного баннера BannerPro.
  Подключение:
  {'!BannerPro' | snippet : ['position' => 3, 'tpl' => '@FILE docs/examples/chunk-byad.fenom.tpl']}
*}
<article class="banner" data-banner-id="{$id}" data-adposition="{$adposition}">
  <a class="banner__link"
     href="{$click_url|escape:'html'}"
     data-bannerpro-adposition="{$adposition}"
     data-bannerpro-ad-id="{$id}"
     {if $description}title="{$description|escape}"{/if}>
    {if $image}
      <img class="banner__image"
           src="{$image}"
           alt="{$name|escape}"
           loading="lazy" />
    {else}
      <span class="banner__title">{$name|escape}</span>
    {/if}
  </a>
</article>
