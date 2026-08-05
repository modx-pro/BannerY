<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array<string, mixed>
 */
function bannerpro_impression_period_condition(string $period): array
{
    $period = bannerpro_normalize_period($period);
    if ($period === BannerProClickPeriod::ALL) {
        return [];
    }

    $rangeCondition = bannerpro_date_range_condition('impressiondate', $period);
    if ($rangeCondition !== []) {
        return $rangeCondition;
    }

    if ($period === BannerProClickPeriod::LAST_MONTH) {
        $like = (new DateTimeImmutable('first day of last month'))->format('Y-m') . '%';

        return ['impressiondate:LIKE' => $like];
    }

    $now = new DateTimeImmutable();
    $map = [
        BannerProClickPeriod::TODAY => $now->format('Y-m-d'),
        BannerProClickPeriod::THIS_MONTH => $now->format('Y-m'),
        BannerProClickPeriod::THIS_YEAR => $now->format('Y'),
    ];
    if (isset($map[$period])) {
        return ['impressiondate:LIKE' => $map[$period] . '%'];
    }

    return [];
}

/**
 * @return array{saved: bool, duplicate: bool, bot_filtered?: bool}
 */
function bannerpro_record_impression(modX $modx, int $adpositionId, ?byAdPosition $link = null): array
{
    if (!$link instanceof byAdPosition) {
        $link = $modx->getObject('byAdPosition', $adpositionId);
    }
    if (!$link) {
        return ['saved' => false, 'duplicate' => false];
    }

    if (bannerpro_request_is_bot($modx)) {
        return ['saved' => false, 'duplicate' => false, 'bot_filtered' => true];
    }

    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
    $key = [
        'ad' => (int) $link->get('ad'),
        'position' => (int) $link->get('position'),
        'ip' => $remoteAddr,
        'impressiondate:LIKE' => (new DateTimeImmutable())->format('Y-m-d') . '%',
    ];
    if ($modx->getCount('byImpression', $key)) {
        return ['saved' => false, 'duplicate' => true];
    }

    /** @var byImpression $impression */
    $impression = $modx->newObject('byImpression', [
        'ad' => (int) $link->get('ad'),
        'position' => (int) $link->get('position'),
        'adposition' => $adpositionId,
        'impressiondate' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        'ip' => $remoteAddr,
    ]);

    return ['saved' => (bool) $impression->save(), 'duplicate' => false];
}

function bannerpro_handle_impression(modX $modx): void
{
    if ($modx->event->name !== 'OnPageNotFound') {
        return;
    }

    $segment = trim((string) $modx->getOption('bannerpro_impression', null, 'bannerimpression', true), '/');
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $pattern = '#/' . preg_quote($segment, '#') . '/([0-9]+)#';

    if ($segment === '' || !preg_match($pattern, $requestUri, $matches)) {
        return;
    }

    bannerpro_add_package($modx);

    $adpositionId = (int) $matches[1];
    /** @var byAdPosition|null $link */
    $link = $modx->getObject('byAdPosition', $adpositionId);
    if (!$link) {
        if (!headers_sent()) {
            header('HTTP/1.1 404 Not Found');
        }
        exit;
    }

    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
    $record = bannerpro_record_impression($modx, $adpositionId, $link);
    if (empty($record['bot_filtered'])) {
        $adId = (int) $link->get('ad');
        $positionId = (int) $link->get('position');
        bannerpro_invoke_impression_event($modx, array_merge(
            bannerpro_event_payload($modx, $adId, $positionId, $adpositionId),
            [
                'ip' => $remoteAddr,
                'recorded' => $record['saved'],
                'duplicate' => $record['duplicate'],
            ]
        ));
        bannerpro_dispatch_webhook(
            $modx,
            bannerpro_impression_webhook_payload(
                $adId,
                $positionId,
                $adpositionId,
                $remoteAddr,
                (bool) $record['saved'],
                (bool) $record['duplicate']
            ),
            'bannerpro_webhook_impression_url'
        );
    }

    if (!headers_sent()) {
        header('HTTP/1.1 204 No Content');
        header('Content-Length: 0');
        header('Cache-Control: no-store');
    }
    exit;
}

function bannerpro_track_impressions_enabled(modX $modx): bool
{
    return (bool) (int) $modx->getOption('bannerpro_track_impressions', null, false);
}

function bannerpro_register_impression_assets(modX $modx): void
{
    static $registered = false;
    if ($registered || !bannerpro_track_impressions_enabled($modx)) {
        return;
    }

    $segment = trim((string) $modx->getOption('bannerpro_impression', null, 'bannerimpression', true), '/');
    $clickSegment = trim((string) $modx->getOption('bannerpro_click', null, 'bannerclick', true), '/');
    $assetsUrl = rtrim((string) $modx->getOption('assets_url', null, ''), '/') . '/';
    $scriptUrl = $assetsUrl . 'components/bannerpro/js/impression.js';
    $configJson = json_encode(
        ['segment' => $segment, 'clickSegment' => $clickSegment],
        JSON_UNESCAPED_UNICODE
    );
    $lazy = (bool) (int) $modx->getOption('bannerpro_impression_lazy', null, true);

    if ($lazy) {
        $loader = '(function(){'
            . 'if(window.bannerproImpressionLoading||window.bannerproImpressionInitDone)return;'
            . 'var nodes=document.querySelectorAll("[data-bannerpro-impression]");'
            . 'if(!nodes.length)return;'
            . 'function loadScript(){'
            . 'if(window.bannerproImpressionLoading||window.bannerproImpressionInitDone)return;'
            . 'window.bannerproImpressionLoading=true;'
            . 'var s=document.createElement("script");'
            . 's.src=' . json_encode($scriptUrl, JSON_UNESCAPED_UNICODE) . ';'
            . 's.async=true;'
            . 's.onload=function(){window.bannerproImpressionLoading=false;};'
            . 'document.head.appendChild(s);'
            . '}'
            . 'if("IntersectionObserver" in window){'
            . 'var io=new IntersectionObserver(function(entries){'
            . 'entries.forEach(function(entry){'
            . 'if(!entry.isIntersecting)return;'
            . 'io.disconnect();loadScript();'
            . '});'
            . '},{rootMargin:"200px",threshold:0});'
            . 'io.observe(nodes[0]);'
            . 'return;'
            . '}'
            . 'if("requestIdleCallback" in window){'
            . 'requestIdleCallback(loadScript,{timeout:2000});'
            . '}else if(document.readyState==="loading"){'
            . 'document.addEventListener("DOMContentLoaded",function(){setTimeout(loadScript,0);});'
            . '}else{setTimeout(loadScript,0);}'
            . '})();';
        $modx->regClientStartupHTMLBlock(
            '<script>window.bannerproImpressionConfig=' . $configJson . ';' . $loader . '</script>'
        );
    } else {
        $modx->regClientStartupHTMLBlock(
            '<script>window.bannerproImpressionConfig=' . $configJson . ';</script>'
        );
        $modx->regClientScript($scriptUrl);
    }

    $registered = true;
}

/**
 * @param array{ad_id?: int, ad_name?: string, position_name?: string} $meta
 */
function bannerpro_wrap_impression_html(string $html, int $adpositionId, array $meta = []): string
{
    if ($adpositionId <= 0) {
        return $html;
    }

    $attrs = ' class="bannerpro-impression" data-bannerpro-impression="' . $adpositionId . '"';
    if (!empty($meta['ad_id'])) {
        $attrs .= ' data-bannerpro-ad-id="' . (int) $meta['ad_id'] . '"';
    }
    if (!empty($meta['ad_name'])) {
        $attrs .= ' data-bannerpro-ad-name="' . htmlspecialchars((string) $meta['ad_name'], ENT_QUOTES, 'UTF-8') . '"';
    }
    if (!empty($meta['position_name'])) {
        $attrs .= ' data-bannerpro-position-name="' . htmlspecialchars((string) $meta['position_name'], ENT_QUOTES, 'UTF-8') . '"';
    }

    return '<div' . $attrs . '>' . $html . '</div>';
}
