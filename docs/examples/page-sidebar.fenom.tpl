{*
  Пример фрагмента страницы: сайдбар с баннерами.
  В шаблоне ресурса или include:
  {include 'file:docs/examples/page-sidebar.fenom.tpl'}
*}
<aside class="page-sidebar">
  <h2 class="page-sidebar__title">Партнёры</h2>

  {'!BannerPro' | snippet : [
    'position' => 3,
    'tpl' => '@FILE docs/examples/chunk-byad.fenom.tpl',
    'tplWrapper' => '@INLINE <div class="page-sidebar__banners">{$output}</div>',
    'limit' => 5,
    'sortby' => 'idx',
    'sortdir' => 'ASC'
  ]}
</aside>
