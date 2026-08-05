<article class="bannerpro-demo-ad mb-2 p-2 border rounded" data-banner-id="{$id}" data-adposition="{$adposition}">
  {if $description}<div class="small text-muted mb-1">{$description|escape}</div>{/if}
  <div class="bannerpro-demo-html">{$html}</div>
  <div class="small text-muted mt-1">#{$id} · pos {$adposition} · html</div>
</article>
