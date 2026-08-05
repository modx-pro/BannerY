{extends 'file:templates/demo/base.tpl'}

{*
  Стенд smoke/QA тестов BannerPro (MODX 3 + pdoTools), секции 0–36.
  Документация: Extras/BannerPro/docs/frontend-guide.md

  GET-параметры:
  - by2_pos_sidebar — имя позиции sidebar (по умолчанию demo-sidebar)
  - by2_pos_footer  — footer (demo-footer)
  - by2_pos_html    — HTML-баннеры (demo-html)
  - by2_pos_shop    — MS3 (demo-shop)
  - by2_product_id  — ID msProduct для &productId=
  - by2_resource_id — явный &resource= для секции таргетинга (опционально)

  TV на ресурсе (опционально): position_sidebar, position_footer, position_html, position_shop, product_id

  Демо-данные: php core/elements/demo/seed_bannerpro_demo.php (из корня MODX).
  Плагины: BannerProClickout (обяз.), BannerProImpression, BannerProAnalytics (опц.).

  Важно: при {extends} переменные вне {block} не выполняются — статус плагина и мост задаются в блоках styles/main.
*}

{block 'styles'}
{set $pluginAnalytics = $modx->getObject('modPlugin', ['name' => 'BannerProAnalytics']) ?: $modx->getObject('MODX\Revolution\modPlugin', ['name' => 'BannerProAnalytics'])}
{set $pluginAnalyticsActive = $pluginAnalytics && !$pluginAnalytics->get('disabled')}
{set $analyticsOn = ('bannerpro_analytics_enabled' | option) == '1'}
{set $analyticsYmCounter = ('bannerpro_analytics_ym_counter' | option) ?: '99999999'}
{set $by2ClickSeg = $_modx->config.bannerpro_click | trim:'/'}
{set $by2LoadDemoBridge = !$pluginAnalyticsActive || !$analyticsOn}
<style>
  .bannerpro-test__section { margin-bottom: 2rem; padding: 1.25rem; border: 1px solid #dee2e6; border-radius: 0.375rem; background: #fff; max-width: 100%; min-width: 0; }
  .bannerpro-test__section h2 { margin-top: 0; font-size: 1.125rem; }
  .bannerpro-test__out { margin-top: 0.75rem; padding: 0.75rem; background: #f8f9fa; border-radius: 0.375rem; min-height: 2rem; }
  .bannerpro-test__out--slider {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 0.75rem;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x;
    align-items: stretch;
  }
  .bannerpro-test__out--slider::-webkit-scrollbar { height: 8px; }
  .bannerpro-test__out--slider::-webkit-scrollbar-thumb { background: #ced4da; border-radius: 4px; }
  .bannerpro-test__out--slider > .bannerpro-impression,
  .bannerpro-test__out--slider > .bannerpro-demo-ad {
    flex: 0 0 min(78vw, 17rem);
    flex-shrink: 0;
    scroll-snap-align: start;
    width: min(78vw, 17rem);
    min-width: min(78vw, 17rem);
    max-width: min(78vw, 17rem);
  }
  .bannerpro-test__out--slider > .bannerpro-impression { display: block; }
  .bannerpro-test__out--slider .bannerpro-demo-ad { margin-bottom: 0 !important; height: 100%; }
  .bannerpro-test__out--slider .bannerpro-demo-ad img {
    max-height: 11rem;
    width: 100%;
    object-fit: contain;
  }
  .bannerpro-test__out--slider > .bannerpro-demo-wrapper {
    flex: 1 1 auto;
    width: 100%;
    min-width: 0;
    max-width: 100%;
    display: block;
    overflow-x: auto;
    overflow-y: hidden;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x;
    border: none !important;
    padding: 0 !important;
    background: transparent !important;
  }
  .bannerpro-test__out--slider .bannerpro-demo-wrapper > .small {
    display: block;
    white-space: normal;
    margin-bottom: 0.35rem !important;
  }
  .bannerpro-test__out--slider .bannerpro-demo-wrapper > .bannerpro-impression,
  .bannerpro-test__out--slider .bannerpro-demo-wrapper > .bannerpro-demo-ad {
    display: inline-block;
    vertical-align: top;
    white-space: normal;
    width: min(78vw, 17rem);
    margin-right: 0.75rem;
    scroll-snap-align: start;
  }
  .bannerpro-test__out.row { display: flex; flex-wrap: wrap; overflow-x: visible; scroll-snap-type: none; }
  #bannerpro-snippet-nav { overflow: hidden; min-width: 0; max-width: 100%; }
  .bannerpro-test__pre { font-size: 0.8rem; background: #212529; color: #f8f9fa; padding: 0.75rem; border-radius: 0.375rem; overflow: auto; max-height: 12rem; white-space: pre-wrap; }
  .bannerpro-test__table-scroll {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x;
    margin-top: 0.5rem;
  }
  .bannerpro-test__table-scroll::-webkit-scrollbar { height: 8px; }
  .bannerpro-test__table-scroll::-webkit-scrollbar-thumb { background: #ced4da; border-radius: 4px; }
  .bannerpro-test__table { width: 100%; font-size: 0.8125rem; border-collapse: collapse; }
  .bannerpro-test__table-scroll > .bannerpro-test__table { width: max-content; min-width: 100%; }
  .bannerpro-test__table th, .bannerpro-test__table td { border: 1px solid #dee2e6; padding: 0.4rem 0.5rem; vertical-align: top; white-space: nowrap; }
  .bannerpro-test__table th { background: #f8f9fa; }
  .bannerpro-test__badge-ok { color: #198754; font-weight: 600; }
  .bannerpro-test__badge-warn { color: #fd7e14; font-weight: 600; }
  .bannerpro-test__badge-err { color: #dc3545; font-weight: 600; }
  .bannerpro-test__slider-hint { font-size: 0.75rem; color: #6c757d; margin: 0 0 0.75rem; }
  .bannerpro-test__slider {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 1rem;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x;
    padding: 0.25rem 0.25rem 1rem;
    margin: 0 -0.25rem;
  }
  .bannerpro-test__slider::-webkit-scrollbar { height: 8px; }
  .bannerpro-test__slider::-webkit-scrollbar-thumb { background: #ced4da; border-radius: 4px; }
  html { scroll-behavior: smooth; }
  .bannerpro-test__nav-card {
    flex: 0 0 min(72vw, 11.5rem);
    flex-shrink: 0;
    scroll-snap-align: start;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    background: #fff;
    padding: 0.65rem 0.75rem;
    display: block;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    transition: border-color .15s, box-shadow .15s;
  }
  .bannerpro-test__nav-card:hover, .bannerpro-test__nav-card:focus-visible {
    border-color: #0d6efd;
    box-shadow: 0 2px 6px rgba(13,110,253,.15);
    color: inherit;
  }
  .bannerpro-test__nav-card-num { font-size: 0.7rem; font-weight: 600; color: #0d6efd; margin-bottom: 0.2rem; }
  .bannerpro-test__nav-card-title { font-size: 0.8rem; font-weight: 600; line-height: 1.3; margin: 0; }
  .bannerpro-test__nav-card-desc { font-size: 0.68rem; color: #6c757d; margin: 0.35rem 0 0; line-height: 1.35; }
  @media (min-width: 992px) {
    .bannerpro-test__nav-card { flex-basis: 11rem; }
  }
</style>
<script src="{$_modx->config.assets_url}components/bannerpro/js/analytics-mock.js"></script>
{if $by2LoadDemoBridge}
<script data-by2-analytics-demo-fallback="1">
window.bannerproAnalyticsConfig={
  clickSegment:"{$by2ClickSeg}",
  ga4:{'bannerpro_analytics_ga4' | option}==='1',
  ga4ClickEvent:"{'bannerpro_analytics_ga4_click' | option}",
  ga4ImpressionEvent:"{'bannerpro_analytics_ga4_impression' | option}",
  matomo:{'bannerpro_analytics_matomo' | option}==='1',
  ymCounter:parseInt("{$analyticsYmCounter ?: '0'}",10)||0,
  ymClickGoal:"{'bannerpro_analytics_ym_click_goal' | option}",
  ymImpressionGoal:"{'bannerpro_analytics_ym_impression_goal' | option}"
};
</script>
<script src="{$_modx->config.assets_url}components/bannerpro/js/analytics.js"></script>
{/if}
{/block}

{block 'main'}
    {set $pluginAnalytics = $modx->getObject('modPlugin', ['name' => 'BannerProAnalytics']) ?: $modx->getObject('MODX\Revolution\modPlugin', ['name' => 'BannerProAnalytics'])}
    {set $pluginAnalyticsActive = $pluginAnalytics && !$pluginAnalytics->get('disabled')}
    {set $analyticsEnabledVal = 'bannerpro_analytics_enabled' | option}
    {set $analyticsOn = $analyticsEnabledVal == '1'}
    {set $analyticsYmCounter = ('bannerpro_analytics_ym_counter' | option) ?: '99999999'}
    {set $by2LoadDemoBridge = !$pluginAnalyticsActive || !$analyticsOn}
    {set $posSidebar = ($.get.by2_pos_sidebar ?: ('position_sidebar' | resource) ?: 'demo-sidebar') | trim}
    {set $posFooter = ($.get.by2_pos_footer ?: ('position_footer' | resource) ?: 'demo-footer') | trim}
    {set $posHtml = ($.get.by2_pos_html ?: ('position_html' | resource) ?: 'demo-html') | trim}
    {set $posShop = ($.get.by2_pos_shop ?: ('position_shop' | resource) ?: 'demo-shop') | trim}
    {set $productId = $.get.by2_product_id | intval}
    {if $productId < 1}
        {set $productId = 'product_id' | resource | intval}
    {/if}
    {if $productId < 1}
        {set $productId = 25}
    {/if}
    {set $posMulti = $posSidebar ~ ',' ~ $posFooter}
    {set $posRotator = 'demo-rotator'}
    {set $posHeader = 'demo-header'}
    {set $posTags = 'demo-tags'}
    {set $posTargeting = 'demo-targeting'}
    {set $posWeighted = 'demo-weighted'}
    {set $posDaypart = 'demo-daypart'}
    {set $posAb = 'demo-ab'}
    {set $posLead = 'demo-lead'}
    {set $posEmpty = 'demo-empty-none'}
    {set $qaResourceId = $_modx->resource.id | intval}
    {set $qaParentId = $_modx->resource.parent | intval}
    {set $targetResourceId = $.get.by2_resource_id | intval}
    {if $targetResourceId < 1}
        {set $targetResourceId = ('site_start' | option) | intval}
    {/if}
    {set $siteContext = $_modx->context.key | default:'web'}
    {set $objSidebar = $modx->getObject('byPosition', ['name' => $posSidebar])}
    {set $posSidebarId = $objSidebar ? $objSidebar->get('id') : 0}
    {set $objFooter = $modx->getObject('byPosition', ['name' => $posFooter])}
    {set $posFooterId = $objFooter ? $objFooter->get('id') : 0}
    {set $objRotator = $modx->getObject('byPosition', ['name' => $posRotator])}
    {set $posRotatorId = $objRotator ? $objRotator->get('id') : 0}
    {set $objHeader = $modx->getObject('byPosition', ['name' => $posHeader])}
    {set $posHeaderId = $objHeader ? $objHeader->get('id') : 0}
    {set $objAb = $modx->getObject('byPosition', ['name' => $posAb])}
    {set $posAbId = $objAb ? $objAb->get('id') : 0}
    {set $posIdsMulti = ($posSidebarId > 0 && $posFooterId > 0) ? ($posSidebarId ~ ',' ~ $posFooterId) : ''}
    {* Якоря href="#…" с <base href="site_url/"> уходят на главную — нужен URL текущего ресурса *}
    {set $bannerproPageUrl = $_modx->resource.id | url}

    <div class="container py-4">
        <h1 class="h2 mb-2">{$_modx->resource.pagetitle}</h1>
        <p class="text-muted small mb-3">
            Smoke-тест BannerPro 4.8. Клик через плейсхолдер <code>click_url</code> (UTM при <code>bannerpro_utm_enabled=1</code>).
            A/B: <code>sortby=ab</code> (секция 34). Webhook на показ: <code>bannerpro_webhook_impression_url</code> (секция 35).
            Показы: плагин <code>BannerProImpression</code> + <code>bannerpro_track_impressions</code> + lazy <code>impression.js</code>.
            Аналитика: <code>BannerProAnalytics</code> + <code>bannerpro_analytics_enabled</code>.
        </p>

        {if !($modx->getObject('modSnippet', ['name' => 'BannerPro']) ?: $modx->getObject('MODX\Revolution\modSnippet', ['name' => 'BannerPro']))}
            <div class="alert alert-danger">Сниппет <strong>BannerPro</strong> не найден — установите пакет.</div>
        {else}

        <section class="bannerpro-test__section">
            <h2>0. Системные настройки</h2>
            <div class="bannerpro-test__table-scroll">
            <table class="bannerpro-test__table">
                <tbody>
                    <tr><th>bannerpro_click</th><td><code>{$_modx->config.bannerpro_click}</code></td></tr>
                    <tr><th>bannerpro_cache</th><td>{'bannerpro_cache' | option}</td></tr>
                    <tr><th>bannerpro_cache_lifetime</th><td>{'bannerpro_cache_lifetime' | option}</td></tr>
                    <tr><th>bannerpro_track_impressions</th><td>{'bannerpro_track_impressions' | option}</td></tr>
                    <tr><th>bannerpro_impression</th><td><code>{'bannerpro_impression' | option}</code></td></tr>
                    <tr><th>bannerpro_impression_lazy</th><td>{'bannerpro_impression_lazy' | option}</td></tr>
                    <tr><th>bannerpro_filter_bots</th><td>{'bannerpro_filter_bots' | option}</td></tr>
                    <tr><th>bannerpro_utm_enabled</th><td>{'bannerpro_utm_enabled' | option}</td></tr>
                    <tr><th>bannerpro_utm_campaign</th><td><code>{'bannerpro_utm_campaign' | option}</code></td></tr>
                    <tr><th>bannerpro_webhook_url</th><td><code>{'bannerpro_webhook_url' | option | default:'—'}</code></td></tr>
                    <tr><th>bannerpro_webhook_impression_url</th><td><code>{'bannerpro_webhook_impression_url' | option | default:'—'}</code></td></tr>
                    <tr><th>bannerpro_webhook_secret</th><td>{if ('bannerpro_webhook_secret' | option)}{'***' | escape}{else}—{/if}</td></tr>
                    <tr><th>bannerpro_ab_ttl</th><td>{'bannerpro_ab_ttl' | option | default:'30'} дн.</td></tr>
                    <tr><th>bannerpro_analytics_enabled</th><td>{'bannerpro_analytics_enabled' | option}</td></tr>
                </tbody>
            </table>
            </div>
            <p class="small text-muted mt-2 mb-0">
                Позиции: sidebar=<code>{$posSidebar}</code>{if $posSidebarId} (id={$posSidebarId}){/if},
                footer=<code>{$posFooter}</code>{if $posFooterId} (id={$posFooterId}){/if},
                html=<code>{$posHtml}</code>, shop=<code>{$posShop}</code>,
                rotator=<code>{$posRotator}</code>{if $posRotatorId} (id={$posRotatorId}){/if},
                header=<code>{$posHeader}</code>{if $posHeaderId} (id={$posHeaderId}){/if},
                tags=<code>{$posTags}</code>, targeting=<code>{$posTargeting}</code>,
                weighted=<code>{$posWeighted}</code>, daypart=<code>{$posDaypart}</code>,
                ab=<code>{$posAb}</code>{if $posAbId} (id={$posAbId}){/if}, lead=<code>{$posLead}</code>,
                product_id=<code>{$productId}</code>,
                qa_resource=<code>{$qaResourceId}</code>, qa_parent=<code>{$qaParentId}</code>,
                context=<code>{$siteContext}</code>
            </p>
        </section>

        <section class="bannerpro-test__section" id="by2-snippet-nav">
            <h2>Навигация по сценариям</h2>
            <p class="bannerpro-test__slider-hint">Листайте карточки — клик переходит к секции на странице.</p>
            <div class="bannerpro-test__slider" id="by2-snippet-nav-slider">
                <a href="{$bannerproPageUrl}#bannerpro-section-1" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">1</div><div class="bannerpro-test__nav-card-title">positionName — sidebar</div><p class="bannerpro-test__nav-card-desc">RAND(), без кэша</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-2" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">2</div><div class="bannerpro-test__nav-card-title">positionName — footer</div><p class="bannerpro-test__nav-card-desc">idx ASC + кэш</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-3" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">3</div><div class="bannerpro-test__nav-card-title">positionKey</div><p class="bannerpro-test__nav-card-desc">синоним positionName</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-4" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">4</div><div class="bannerpro-test__nav-card-title">Несколько позиций</div><p class="bannerpro-test__nav-card-desc">positionName через запятую</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-5" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">5</div><div class="bannerpro-test__nav-card-title">tplWrapper</div><p class="bannerpro-test__nav-card-desc">wrapIfEmpty=false</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-6" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">6</div><div class="bannerpro-test__nav-card-title">HTML-баннеры</div><p class="bannerpro-test__nav-card-desc">tplHtml / byHtml</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-7" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">7</div><div class="bannerpro-test__nav-card-title">toPlaceholder</div><p class="bannerpro-test__nav-card-desc">by2.demo.footer</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-8" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">8</div><div class="bannerpro-test__nav-card-title">toSeparatePlaceholders</div><p class="bannerpro-test__nav-card-desc">by2banner0…</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-9" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">9</div><div class="bannerpro-test__nav-card-title">cache=0</div><p class="bannerpro-test__nav-card-desc">без кэша вызова</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-10" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">10</div><div class="bannerpro-test__nav-card-title">MiniShop3</div><p class="bannerpro-test__nav-card-desc">productId</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-11" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">11</div><div class="bannerpro-test__nav-card-title">showInactive</div><p class="bannerpro-test__nav-card-desc">неактивные баннеры</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-12" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">12</div><div class="bannerpro-test__nav-card-title">where</div><p class="bannerpro-test__nav-card-desc">JSON-фильтр</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-13" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">13</div><div class="bannerpro-test__nav-card-title">position (ID)</div><p class="bannerpro-test__nav-card-desc">числовой ID позиции</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-14" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">14</div><div class="bannerpro-test__nav-card-title">positions (IDs)</div><p class="bannerpro-test__nav-card-desc">ID через запятую</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-15" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">15</div><div class="bannerpro-test__nav-card-title">Расписание</div><p class="bannerpro-test__nav-card-desc">start / end</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-16" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">16</div><div class="bannerpro-test__nav-card-title">Ротатор idx</div><p class="bannerpro-test__nav-card-desc">demo-rotator A→B→C</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-17" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">17</div><div class="bannerpro-test__nav-card-title">tplFirst + offset</div><p class="bannerpro-test__nav-card-desc">limit / offset</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-18" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">18</div><div class="bannerpro-test__nav-card-title">wrapIfEmpty</div><p class="bannerpro-test__nav-card-desc">пустая позиция</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-analytics" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">19</div><div class="bannerpro-test__nav-card-title">Analytics</div><p class="bannerpro-test__nav-card-desc">GA4 / Matomo / Метрика</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-20" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">20</div><div class="bannerpro-test__nav-card-title">Клики и показы</div><p class="bannerpro-test__nav-card-desc">bannerclick / impression</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-21" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">21</div><div class="bannerpro-test__nav-card-title">bannerpro:*</div><p class="bannerpro-test__nav-card-desc">DOM-события</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-23" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">23</div><div class="bannerpro-test__nav-card-title">click_url + UTM</div><p class="bannerpro-test__nav-card-desc">плейсхолдер клика</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-24" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">24</div><div class="bannerpro-test__nav-card-title">tags (any)</div><p class="bannerpro-test__nav-card-desc">&tags=sale,promo</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-25" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">25</div><div class="bannerpro-test__nav-card-title">tags (all)</div><p class="bannerpro-test__nav-card-desc">tagsMode=all</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-26" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">26</div><div class="bannerpro-test__nav-card-title">Targeting</div><p class="bannerpro-test__nav-card-desc">resource / parent</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-27" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">27</div><div class="bannerpro-test__nav-card-title">&resource=</div><p class="bannerpro-test__nav-card-desc">явный ID страницы</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-28" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">28</div><div class="bannerpro-test__nav-card-title">Dayparting</div><p class="bannerpro-test__nav-card-desc">show_hours</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-29" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">29</div><div class="bannerpro-test__nav-card-title">weighted</div><p class="bannerpro-test__nav-card-desc">sortby=weighted</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-30" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">30</div><div class="bannerpro-test__nav-card-title">context</div><p class="bannerpro-test__nav-card-desc">&context=</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-31" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">31</div><div class="bannerpro-test__nav-card-title">MS3 category</div><p class="bannerpro-test__nav-card-desc">category_id</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-32" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">32</div><div class="bannerpro-test__nav-card-title">showLog</div><p class="bannerpro-test__nav-card-desc">лог сниппета (mgr)</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-33" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">33</div><div class="bannerpro-test__nav-card-title">Админка 4.8</div><p class="bannerpro-test__nav-card-desc">UTM / KPI / bulk</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-34" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">34</div><div class="bannerpro-test__nav-card-title">A/B split</div><p class="bannerpro-test__nav-card-desc">sortby=ab, cookie</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-35" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">35</div><div class="bannerpro-test__nav-card-title">Webhook показ</div><p class="bannerpro-test__nav-card-desc">impression URL</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-36" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">36</div><div class="bannerpro-test__nav-card-title">FormIt lead</div><p class="bannerpro-test__nav-card-desc">click_url CTA</p></a>
                <a href="{$bannerproPageUrl}#bannerpro-section-22" class="bannerpro-test__nav-card"><div class="bannerpro-test__nav-card-num">22</div><div class="bannerpro-test__nav-card-title">Чеклист QA</div><p class="bannerpro-test__nav-card-desc">матрица кейсов</p></a>
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-1">
            <h2>1. positionName — sidebar (ротация RAND)</h2>
            <p class="small text-muted"><code>sortby=RAND()</code> — кэш HTML не используется.</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posSidebar,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'limit' => 5,
                    'sortby' => 'RAND()'
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-2">
            <h2>2. positionName — footer, порядок idx + кэш</h2>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posFooter,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 10
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-3">
            <h2>3. positionKey (синоним positionName)</h2>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionKey' => $posSidebar,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'limit' => 2
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-4">
            <h2>4. Несколько позиций (через запятую)</h2>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posMulti,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 8
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-5">
            <h2>5. tplWrapper + wrapIfEmpty</h2>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posSidebar,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'tplWrapper' => '@FILE chunks/demo/tpl_bannerpro_wrapper.fenom.tpl',
                    'wrapIfEmpty' => false,
                    'limit' => 5
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-6">
            <h2>6. HTML-баннеры (tplHtml / byHtml)</h2>
            <p class="small text-muted">Позиция <code>{$posHtml}</code> — баннер <code>type=html</code>.</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posHtml,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'tplHtml' => '@FILE chunks/demo/tpl_bannerpro_byhtml.fenom.tpl',
                    'limit' => 3
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-7">
            <h2>7. toPlaceholder</h2>
            {'!BannerPro' | snippet : [
                'positionName' => $posFooter,
                'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                'toPlaceholder' => 'by2.demo.footer',
                'limit' => 3
            ]}
            <div class="bannerpro-test__out bannerpro-test__out--slider">{$_modx->getPlaceholder('by2.demo.footer') ?: '<span class="text-muted">пусто</span>'}</div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-8">
            <h2>8. toSeparatePlaceholders</h2>
            {'!BannerPro' | snippet : [
                'positionName' => $posSidebar,
                'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                'toSeparatePlaceholders' => 'by2banner',
                'limit' => 3,
                'sortby' => 'idx',
                'sortdir' => 'ASC'
            ]}
            <div class="bannerpro-test__out row g-2">
                <div class="col-md-4">{$_modx->getPlaceholder('by2banner0')}</div>
                <div class="col-md-4">{$_modx->getPlaceholder('by2banner1')}</div>
                <div class="col-md-4">{$_modx->getPlaceholder('by2banner2')}</div>
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-9">
            <h2>9. cache=0 для вызова</h2>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posSidebar,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'cache' => 0,
                    'limit' => 3
                ]}
            </div>
        </section>

        {if $modx->getObject('modSnippet', ['name' => 'msProducts']) ?: $modx->getObject('MODX\Revolution\modSnippet', ['name' => 'msProducts'])}
        <section class="bannerpro-test__section" id="bannerpro-section-10">
            <h2>10. MiniShop3 — productId</h2>
            <p class="small text-muted">Глобальные + баннеры с <code>product_id={$productId}</code>.</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posShop,
                    'productId' => $productId,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad_product.fenom.tpl',
                    'limit' => 5
                ]}
            </div>
            <p class="small mb-0">Без productId (только глобальные):</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posShop,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'limit' => 3
                ]}
            </div>
        </section>
        {else}
        <section class="bannerpro-test__section" id="bannerpro-section-10">
            <h2>10. MiniShop3</h2>
            <p class="text-muted small mb-0">MiniShop3 не установлен — секция пропущена.</p>
        </section>
        {/if}

        <section class="bannerpro-test__section" id="bannerpro-section-11">
            <h2>11. showInactive</h2>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posSidebar,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'showInactive' => 1,
                    'limit' => 5
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-12">
            <h2>12. where (JSON-фильтр)</h2>
            <p class="small text-muted">Пример: имя баннера содержит «demo».</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posSidebar,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'where' => '{"byAd.name:LIKE":"%demo%"}',
                    'limit' => 5
                ]}
            </div>
        </section>

        {if $posSidebarId > 0}
        <section class="bannerpro-test__section" id="bannerpro-section-13">
            <h2>13. position (числовой ID)</h2>
            <p class="small text-muted">Параметр <code>position</code> имеет приоритет над <code>positionName</code>. ID={$posSidebarId} для <code>{$posSidebar}</code>.</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'position' => $posSidebarId,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 3
                ]}
            </div>
        </section>
        {/if}

        {if $posIdsMulti}
        <section class="bannerpro-test__section" id="bannerpro-section-14">
            <h2>14. positions (ID через запятую)</h2>
            <p class="small text-muted"><code>positions={$posIdsMulti}</code> — альтернатива нескольким именам в <code>positionName</code> (секция 4).</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positions' => $posIdsMulti,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 6
                ]}
            </div>
        </section>
        {/if}

        <section class="bannerpro-test__section" id="bannerpro-section-15">
            <h2>15. Расписание start / end</h2>
            <p class="small text-muted">
                В sidebar скрыты <code>demo-banner-scheduled-future</code> и <code>demo-banner-scheduled-expired</code>.
                В header — только активный по датам баннер.
            </p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posHeader,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 3
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-16">
            <h2>16. Ротатор idx (<code>{$posRotator}</code>)</h2>
            <p class="small text-muted">Три баннера A → B → C по полю <code>idx</code> в связи с позицией.</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posRotator,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 5
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-17">
            <h2>17. tplFirst + limit / offset</h2>
            <p class="small text-muted">Первый баннер — чанк <code>tplFirst</code>, остальные — <code>tpl</code>; <code>offset=1</code> пропускает первый.</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider mb-2">
                {'!BannerPro' | snippet : [
                    'positionName' => $posSidebar,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'tplFirst' => '@FILE chunks/demo/tpl_bannerpro_byad_first.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 2
                ]}
            </div>
            <p class="small mb-1">offset=1, limit=2:</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posSidebar,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'offset' => 1,
                    'limit' => 2
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-18">
            <h2>18. wrapIfEmpty — пустая позиция</h2>
            <p class="small text-muted">
                <code>wrapIfEmpty=false</code> (секция 5) — без баннеров обёртки нет.
                Здесь <code>wrapIfEmpty=true</code> и несуществующая позиция <code>{$posEmpty}</code>.
            </p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posEmpty,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'tplWrapper' => '@FILE chunks/demo/tpl_bannerpro_wrapper.fenom.tpl',
                    'wrapIfEmpty' => true,
                    'limit' => 3
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-analytics">
            <h2>19. BannerProAnalytics — симуляция GA4 / Matomo / Метрика</h2>
            <p class="small text-muted">
                На этой странице подключён <code>analytics-mock.js</code> (стабы <code>dataLayer</code>, <code>_paq</code>, <code>ym</code>).
                Реальный продакшен: счётчики на сайте + плагин <code>BannerProAnalytics</code> + <code>bannerpro_analytics_enabled=1</code>.
            </p>
            <div class="bannerpro-test__table-scroll mb-3">
            <table class="bannerpro-test__table">
                <tbody>
                    <tr>
                        <th>Плагин BannerProAnalytics</th>
                        <td>
                            {if $pluginAnalyticsActive}
                                <span class="bannerpro-test__badge-ok">включён</span>
                            {elseif $pluginAnalytics}
                                <span class="bannerpro-test__badge-warn">выключен</span> — включите в Элементы → Плагины{if $by2LoadDemoBridge} (на стенде мост подключён отдельно){/if}
                            {else}
                                <span class="bannerpro-test__badge-err">не установлен</span>{if $by2LoadDemoBridge} — на стенде мост подключён отдельно{/if}
                            {/if}
                        </td>
                    </tr>
                    <tr><th>bannerpro_analytics_enabled</th><td>{if $analyticsOn}<span class="bannerpro-test__badge-ok">1</span>{else}<span class="bannerpro-test__badge-warn">{$analyticsEnabledVal ?: '0'}</span> — seed или настройки{if $by2LoadDemoBridge}; на стенде мост всё равно активен{/if}{/if}</td></tr>
                    <tr><th>bannerpro_analytics_ga4</th><td>{'bannerpro_analytics_ga4' | option}</td></tr>
                    <tr><th>bannerpro_analytics_matomo</th><td>{'bannerpro_analytics_matomo' | option}</td></tr>
                    <tr><th>bannerpro_analytics_ym_counter</th><td><code>{$analyticsYmCounter ?: '—'}</code> (демо-mock: <code>99999999</code>)</td></tr>
                    <tr><th>Mock-стабы</th><td id="by2-analytics-mock-status">загрузка…</td></tr>
                    <tr><th>analytics.js</th><td id="by2-analytics-bridge-status">проверка после загрузки…</td></tr>
                </tbody>
            </table>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="by2-sim-impression">Симулировать показ</button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="by2-sim-click">Симулировать клик</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="by2-analytics-clear">Очистить лог</button>
            </div>
            <p class="small text-muted mb-1">
                Автотест: прокрутите страницу (показы) или кликните баннер (клик) — события пойдут в мост и в лог ниже.
                Ожидаемые GA4-события: <code>{'bannerpro_analytics_ga4_impression' | option}</code> / <code>{'bannerpro_analytics_ga4_click' | option}</code>.
            </p>
            <pre class="bannerpro-test__pre mb-0" id="by2-analytics-log">События GA4 / Matomo / Метрика появятся здесь…</pre>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-20">
            <h2>20. Клики и показы</h2>
            <ul class="small mb-2">
                <li>Клик через <code>click_url</code> → <code>{$_modx->config.bannerpro_click}/&lt;adposition&gt;</code> (+ UTM при <code>bannerpro_utm_enabled=1</code>) → редирект, запись в <code>bannerpro_clicks</code>.</li>
                <li>Внешний URL: баннер <code>demo-banner-footer-2</code> (секция 2).</li>
                <li>Показы: <code>data-bannerpro-impression</code> + pixel <code>{'bannerpro_impression' | option}</code> + lazy <code>impression.js</code> (<code>bannerpro_impression_lazy</code>).</li>
                <li>Webhook на клик: <code>bannerpro_webhook_url</code> (payload с <code>event: click</code>). Webhook на показ: <code>bannerpro_webhook_impression_url</code> — см. секцию 35.</li>
                <li>Боты: при <code>bannerpro_filter_bots=1</code> клики/показы CrawlerDetect не пишутся в статистику (редирект остаётся).</li>
                <li>Статистика и CSV: Компоненты → BannerPro.</li>
            </ul>
            <p class="small text-muted mb-0">Пустые блоки: нет позиций/баннеров, <code>active=0</code> (без <code>showInactive</code>) или вне окна <code>start</code>–<code>end</code>.</p>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-21">
            <h2>21. События bannerpro:* (консоль и панель)</h2>
            <pre class="bannerpro-test__pre mb-2" id="bannerpro-event-log">События bannerpro:* появятся здесь…</pre>
            <p class="small text-muted mb-0">Серверные <code>OnBannerProClick</code> / <code>OnBannerProImpression</code> — через свой плагин или лог MODX.</p>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-23">
            <h2>23. click_url + UTM</h2>
            <p class="small text-muted">
                Чанк <code>tpl_bannerpro_byad_meta</code> выводит готовый URL клика. При <code>bannerpro_utm_enabled=1</code> в ссылке должны быть
                <code>utm_source</code>, <code>utm_medium</code> и шаблоны из настроек (см. секцию 0).
            </p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posSidebar,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad_meta.fenom.tpl',
                    'limit' => 2,
                    'sortby' => 'idx',
                    'sortdir' => 'ASC'
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-24">
            <h2>24. Метки — &tags= (режим any)</h2>
            <p class="small text-muted">
                Позиция <code>{$posTags}</code>, <code>&tags=sale,promo</code> — баннеры с меткой <code>sale</code> <em>или</em> <code>promo</code>.
                Без меток в выборку не попадают.
            </p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posTags,
                    'tags' => 'sale,promo',
                    'tagsMode' => 'any',
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad_meta.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 5
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-25">
            <h2>25. Метки — tagsMode=all</h2>
            <p class="small text-muted"><code>&tags=sale,news</code> + <code>tagsMode=all</code> — только <code>demo-banner-tags-both</code>.</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posTags,
                    'tags' => 'sale,news',
                    'tagsMode' => 'all',
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad_meta.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 3
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-26">
            <h2>26. Таргетинг resource / parent (текущая страница)</h2>
            <p class="small text-muted">
                Без <code>&resource=</code> используется ресурс <code>{$qaResourceId}</code>, parent <code>{$qaParentId}</code>.
                Ожидаются: <code>demo-banner-target-global</code>, <code>demo-banner-target-page</code>{if $qaParentId > 0}, <code>demo-banner-target-section</code>{/if}.
                Не должно быть: <code>demo-banner-target-other</code>.
            </p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posTargeting,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad_meta.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 6
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-27">
            <h2>27. Явный &resource=</h2>
            <p class="small text-muted">
                <code>&resource={$targetResourceId}</code> (GET: <code>?by2_resource_id={$targetResourceId}</code>).
                На этой странице QA ожидается <code>demo-banner-target-other</code> вместо page-specific баннера.
            </p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posTargeting,
                    'resource' => $targetResourceId,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad_meta.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 6
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-28">
            <h2>28. Dayparting (show_hours)</h2>
            <p class="small text-muted">
                Позиция <code>{$posDaypart}</code>: <code>demo-banner-daypart-now</code> виден сейчас;
                <code>demo-banner-daypart-off</code> — только в «чужой» час (см. вывод seed).
            </p>
            <div class="bannerpro-test__out">
                {'!BannerPro' | snippet : [
                    'positionName' => $posDaypart,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad_meta.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 3,
                    'cache' => 0
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-29">
            <h2>29. Взвешенная ротация (sortby=weighted)</h2>
            <p class="small text-muted">
                Позиция <code>{$posWeighted}</code>, веса 1 / 3 / 10. Кэш HTML отключён. Обновите страницу несколько раз —
                <code>demo-banner-weighted-heavy</code> должен выпадать чаще.
            </p>
            <div class="bannerpro-test__out">
                {'!BannerPro' | snippet : [
                    'positionName' => $posWeighted,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad_meta.fenom.tpl',
                    'sortby' => 'weighted',
                    'limit' => 1,
                    'cache' => 0
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-30">
            <h2>30. Контекст позиций (&context=)</h2>
            <p class="small text-muted">Текущий контекст: <code>{$siteContext}</code>. Пустой <code>context_key</code> у позиции — видна везде.</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posSidebar,
                    'context' => $siteContext,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                    'limit' => 2,
                    'sortby' => 'idx',
                    'sortdir' => 'ASC'
                ]}
            </div>
        </section>

        {if $modx->getObject('modSnippet', ['name' => 'msProducts']) ?: $modx->getObject('MODX\Revolution\modSnippet', ['name' => 'msProducts'])}
        <section class="bannerpro-test__section" id="bannerpro-section-31">
            <h2>31. MiniShop3 — category_id</h2>
            <p class="small text-muted">
                Глобальные баннеры + <code>category_id</code> родителя товара <code>{$productId}</code>.
                Seed: <code>demo-banner-shop-category</code>.
            </p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posShop,
                    'productId' => $productId,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad_product.fenom.tpl',
                    'sortby' => 'idx',
                    'sortdir' => 'ASC',
                    'limit' => 6
                ]}
            </div>
        </section>
        {else}
        <section class="bannerpro-test__section" id="bannerpro-section-31">
            <h2>31. MiniShop3 — category_id</h2>
            <p class="text-muted small mb-0">MiniShop3 не установлен — секция пропущена.</p>
        </section>
        {/if}

        <section class="bannerpro-test__section" id="bannerpro-section-32">
            <h2>32. showLog (только mgr)</h2>
            <p class="small text-muted">Лог виден только авторизованным в контексте <code>mgr</code>. На публичной странице блок ниже пуст.</p>
            <pre class="bannerpro-test__pre mb-0">{'!BannerPro' | snippet : [
                'positionName' => $posSidebar,
                'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad.fenom.tpl',
                'showLog' => 1,
                'limit' => 1,
                'sortby' => 'idx',
                'sortdir' => 'ASC'
            ]}</pre>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-33">
            <h2>33. Админка BannerPro 4.8 (ручная проверка)</h2>
            <div class="bannerpro-test__table-scroll">
            <table class="bannerpro-test__table">
                <thead><tr><th>Функция</th><th>Где проверить</th></tr></thead>
                <tbody>
                    <tr><td>OverviewWidget (клики/CTR 7 дней)</td><td>Компоненты → BannerPro, вкладка «Баннеры»</td></tr>
                    <tr><td>KPI воронки: заказы, CR (conversions, cvr)</td><td>Вкладка «Статистика» → сводка и OverviewWidget</td></tr>
                    <tr><td>Настройки UTM (вкладка «Настройки»)</td><td>Компоненты → BannerPro → «Настройки» → блок UTM</td></tr>
                    <tr><td>Фильтры ads (active, type, product_id, tags)</td><td>Вкладка «Баннеры» → панель фильтров</td></tr>
                    <tr><td>Bulk enable/disable/remove</td><td>Выбор строк → массовые действия</td></tr>
                    <tr><td>Audit log</td><td>Вкладка «Аудит»</td></tr>
                    <tr><td>Шаблоны баннеров</td><td>Создание баннера → «Из шаблона»</td></tr>
                    <tr><td>Stats compare (недели / периоды)</td><td>Вкладка «Статистика»</td></tr>
                    <tr><td>show_hours, target_*, tags, weight</td><td>Форма редактирования баннера</td></tr>
                    <tr><td>max_clicks / max_impressions</td><td>Форма баннера → лимиты; после исчерпания баннер скрывается на фронте</td></tr>
                    <tr><td>stats-only ACL</td><td>Пользователь без edit → только статистика</td></tr>
                    <tr><td>MS3 catalog positions</td><td>Плагин BannerProMS3 + настройка <code>bannerpro_ms_catalog_positions</code></td></tr>
                </tbody>
            </table>
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-34">
            <h2>34. A/B split — sortby=ab</h2>
            <p class="small text-muted mb-2">
                Позиция <code>{$posAb}</code>{if $posAbId} (id={$posAbId}){/if}, два баннера: <code>demo-banner-ab-a</code> / <code>demo-banner-ab-b</code>.
                <code>&sortby=ab</code> + <code>limit=1</code> — sticky 50/50 по cookie <code>bannerpro_ab_{$posAbId}</code>, TTL <code>bannerpro_ab_ttl</code> дней.
                Кэш HTML отключён. Обновите страницу или откройте в incognito — вариант может смениться только до фиксации cookie.
            </p>
            <p class="small mb-2" id="by2-ab-cookie-status">Cookie A/B: …</p>
            <div class="bannerpro-test__out bannerpro-test__out--slider">
                {'!BannerPro' | snippet : [
                    'positionName' => $posAb,
                    'tpl' => '@FILE chunks/demo/tpl_bannerpro_byad_meta.fenom.tpl',
                    'sortby' => 'ab',
                    'limit' => 1
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-35">
            <h2>35. HTTP webhook на показ</h2>
            <p class="small text-muted mb-2">
                При записи показа (pixel / lazy) отправляется POST на <code>bannerpro_webhook_impression_url</code>, если URL задан.
                Подпись: HMAC-SHA256 в заголовке <code>X-BannerPro-Signature</code> при непустом <code>bannerpro_webhook_secret</code>.
                Для проверки укажите URL (например <a href="https://httpbin.org/post" target="_blank" rel="noopener">httpbin.org/post</a>) в настройках и прокрутите страницу (секция 20).
            </p>
            <div class="bannerpro-test__table-scroll">
            <table class="bannerpro-test__table">
                <tbody>
                    <tr><th>bannerpro_webhook_url (клик)</th><td><code>{'bannerpro_webhook_url' | option | default:'—'}</code></td></tr>
                    <tr><th>bannerpro_webhook_impression_url</th><td><code>{'bannerpro_webhook_impression_url' | option | default:'—'}</code></td></tr>
                    <tr><th>bannerpro_webhook_secret</th><td>{if ('bannerpro_webhook_secret' | option)}задан{else}не задан{/if}</td></tr>
                </tbody>
            </table>
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-36">
            <h2>36. FormIt lead (cookbook)</h2>
            <p class="small text-muted mb-2">
                HTML-баннер с CTA через <code>click_url</code> — типичный сценарий для лендинга с FormIt.
                Cookbook: <code>Extras/BannerPro/docs/integrations/formit.md</code>.
            </p>
            <div class="bannerpro-test__out">
                {'!BannerPro' | snippet : [
                    'positionName' => $posLead,
                    'tplHtml' => '@FILE chunks/demo/tpl_bannerpro_byad_lead.fenom.tpl',
                    'limit' => 1
                ]}
            </div>
        </section>

        <section class="bannerpro-test__section" id="bannerpro-section-22">
            <h2>22. Чеклист QA</h2>
            <div class="bannerpro-test__table-scroll">
            <table class="bannerpro-test__table">
                <thead><tr><th>Кейс</th><th>Секция</th></tr></thead>
                <tbody>
                    <tr><td>positionName / positionKey</td><td>1, 3</td></tr>
                    <tr><td>position (ID) / positions (IDs)</td><td>13, 14</td></tr>
                    <tr><td>Несколько позиций по имени</td><td>4</td></tr>
                    <tr><td>idx + кэш HTML</td><td>2, 9</td></tr>
                    <tr><td>RAND без кэша</td><td>1</td></tr>
                    <tr><td>tplWrapper / wrapIfEmpty</td><td>5, 18</td></tr>
                    <tr><td>tplFirst, limit, offset</td><td>17</td></tr>
                    <tr><td>HTML type + tplHtml</td><td>6</td></tr>
                    <tr><td>toPlaceholder / toSeparatePlaceholders</td><td>7, 8</td></tr>
                    <tr><td>where (JSON)</td><td>12</td></tr>
                    <tr><td>showInactive</td><td>11</td></tr>
                    <tr><td>start / end (расписание)</td><td>15</td></tr>
                    <tr><td>Ротатор idx</td><td>16</td></tr>
                    <tr><td>MS3 productId + product_*</td><td>10</td></tr>
                    <tr><td>Клик / показ / CTR</td><td>20 + админка</td></tr>
                    <tr><td>bannerpro:* / OnBannerPro*</td><td>21</td></tr>
                    <tr><td>GA4 / Matomo / Метрика (mock + analytics.js)</td><td>19</td></tr>
                    <tr><td>click_url + UTM на клике</td><td>23, 0</td></tr>
                    <tr><td>tags any / tagsMode=all</td><td>24, 25</td></tr>
                    <tr><td>target_resource / target_parent</td><td>26, 27</td></tr>
                    <tr><td>show_hours (dayparting)</td><td>28</td></tr>
                    <tr><td>sortby=weighted + weight</td><td>29</td></tr>
                    <tr><td>&context=</td><td>30</td></tr>
                    <tr><td>MS3 category_id</td><td>31</td></tr>
                    <tr><td>bannerpro_impression_lazy / filter_bots</td><td>0, 20</td></tr>
                    <tr><td>showLog (mgr)</td><td>32</td></tr>
                    <tr><td>Админка 4.8 (UTM, KPI, bulk, audit, compare)</td><td>33</td></tr>
                    <tr><td>sortby=ab + cookie sticky</td><td>34</td></tr>
                    <tr><td>webhook impression URL</td><td>35, 0, 20</td></tr>
                    <tr><td>FormIt lead (click_url HTML)</td><td>36</td></tr>
                    <tr><td>bannerpro:* DOM-события</td><td>21</td></tr>
                </tbody>
            </table>
            </div>
        </section>

        {/if}
    </div>
{/block}

{block 'scripts'}
<script>
(function () {
    var eventLog = document.getElementById('bannerpro-event-log');
    function appendEvent(msg) {
        if (!eventLog) return;
        var line = '[' + new Date().toISOString().slice(11, 19) + '] ' + msg + '\n';
        eventLog.textContent = (eventLog.textContent.indexOf('События bannerpro') === 0 && eventLog.textContent.indexOf('\n') === -1 ? '' : eventLog.textContent) + line;
    }
    document.addEventListener('bannerpro:impression', function (e) {
        appendEvent('bannerpro:impression ' + JSON.stringify(e.detail || {}));
    });
    document.addEventListener('bannerpro:click', function (e) {
        appendEvent('bannerpro:click ' + JSON.stringify(e.detail || {}));
    });

    var analyticsLog = document.getElementById('by2-analytics-log');
    var mockStatus = document.getElementById('by2-analytics-mock-status');
    var bridgeStatus = document.getElementById('by2-analytics-bridge-status');

    function appendAnalytics(provider, kind, payload) {
        if (!analyticsLog) return;
        var line = '[' + new Date().toISOString().slice(11, 19) + '] ' + provider + ' / ' + kind + ' ' + JSON.stringify(payload) + '\n';
        analyticsLog.textContent = (analyticsLog.textContent.indexOf('События GA4') === 0 && analyticsLog.textContent.indexOf('\n') === -1 ? '' : analyticsLog.textContent) + line;
    }

    document.addEventListener('bannerpro:analytics-mock', function (e) {
        var d = e.detail || {};
        appendAnalytics(d.provider || '?', d.kind || '?', d.payload || {});
    });

    function updateStatus() {
        if (mockStatus) {
            var ok = window.dataLayer && window._paq && typeof window.ym === 'function';
            mockStatus.innerHTML = ok
                ? '<span class="bannerpro-test__badge-ok">dataLayer, _paq, ym</span>'
                : '<span class="bannerpro-test__badge-err">стабы не загружены</span>';
        }
        if (bridgeStatus) {
            var bridge = window.bannerproAnalytics && window.bannerproAnalyticsConfig;
            var demoFallback = document.querySelector('[data-by2-analytics-demo-fallback]');
            if (bridge && demoFallback) {
                bridgeStatus.innerHTML = '<span class="bannerpro-test__badge-ok">analytics.js активен</span> <span class="bannerpro-test__badge-warn">(стенд)</span>';
            } else if (bridge) {
                bridgeStatus.innerHTML = '<span class="bannerpro-test__badge-ok">analytics.js активен (плагин)</span>';
            } else {
                bridgeStatus.innerHTML = '<span class="bannerpro-test__badge-warn">мост не загружен (включите плагин и bannerpro_analytics_enabled)</span>';
            }
        }
    }

    var demoDetail = {
        adposition: 3,
        ad_id: 2,
        ad_name: 'demo-banner-sidebar-1',
        position_name: 'demo-sidebar'
    };

    function simulateImpression() {
        if (window.bannerproAnalytics && window.bannerproAnalytics.trackImpression) {
            window.bannerproAnalytics.trackImpression(demoDetail);
        }
        document.dispatchEvent(new CustomEvent('bannerpro:impression', { detail: demoDetail }));
    }

    function simulateClick() {
        if (window.bannerproAnalytics && window.bannerproAnalytics.trackClick) {
            window.bannerproAnalytics.trackClick(demoDetail);
        }
        document.dispatchEvent(new CustomEvent('bannerpro:click', { detail: demoDetail }));
    }

    var btnImp = document.getElementById('by2-sim-impression');
    var btnClk = document.getElementById('by2-sim-click');
    var btnClr = document.getElementById('by2-analytics-clear');
    if (btnImp) btnImp.addEventListener('click', simulateImpression);
    if (btnClk) btnClk.addEventListener('click', simulateClick);
    if (btnClr && analyticsLog) {
        btnClr.addEventListener('click', function () {
            analyticsLog.textContent = '';
            window.by2AnalyticsMockLog = [];
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateStatus);
    } else {
        updateStatus();
    }
    window.addEventListener('load', updateStatus);

    var abCookieEl = document.getElementById('by2-ab-cookie-status');
    if (abCookieEl) {
        var abPrefix = 'bannerpro_ab_';
        var abCookie = document.cookie.split(';').map(function (c) { return c.trim(); }).filter(function (c) {
            return c.indexOf(abPrefix) === 0;
        });
        abCookieEl.textContent = abCookie.length
            ? 'Cookie A/B: ' + abCookie.join('; ')
            : 'Cookie A/B: не задан (первый визит — вариант выберется случайно 50/50)';
    }
})();
</script>
{/block}
