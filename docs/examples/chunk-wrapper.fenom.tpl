{*
  Обёртка для tplWrapper. Плейсхолдер output — список отрендеренных баннеров.
  {'!BannerPro' | snippet : [
    'position' => 3,
    'tpl' => 'byAd',
    'tplWrapper' => '@FILE docs/examples/chunk-wrapper.fenom.tpl'
  ]}
*}
<div class="banners banners--group">
  {$output}
</div>
