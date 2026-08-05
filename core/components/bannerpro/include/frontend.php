<?php

defined('MODX_CORE_PATH') || exit;

use ModxPro\PdoTools\Fetch;

/**
 * @return bool
 */
function bannerpro_pdofetch_available(modX $modx): bool
{
    if ($modx->services->has(Fetch::class) || $modx->services->has('pdofetch')) {
        return true;
    }

    $bootstrap = $modx->getOption('core_path') . 'components/pdotools/bootstrap.php';
    if (is_file($bootstrap)) {
        require_once $bootstrap;
    }

    if ($modx->services->has(Fetch::class) || $modx->services->has('pdofetch')) {
        return true;
    }

    return $modx->getService('pdoFetch') && class_exists('pdoFetch');
}

/**
 * @param array<string, mixed> $scriptProperties
 */
function bannerpro_create_pdofetch(modX $modx, array $scriptProperties): object
{
    if ($modx->services->has(Fetch::class) || $modx->services->has('pdofetch')) {
        $modx->services['pdotools_config'] = $scriptProperties;

        return $modx->services->get(Fetch::class);
    }

    if (class_exists('pdoFetch')) {
        return new pdoFetch($modx, $scriptProperties);
    }

    throw new RuntimeException('pdoTools Fetch is not available');
}

function bannerpro_pdofetch_fast_mode(object $pdoFetch): bool
{
    if (method_exists($pdoFetch, 'config')) {
        return (bool) $pdoFetch->config('fastMode');
    }

    return (bool) ($pdoFetch->config['fastMode'] ?? false);
}

/**
 * @param array<string, mixed> $props
 */
function bannerpro_pick_tpl_variant(array $props, string $prefix, int $idx, int $total): string
{
    if ($idx === 1 && !empty($props[$prefix . 'First'])) {
        return (string) $props[$prefix . 'First'];
    }
    if ($total > 0 && $idx === $total && !empty($props[$prefix . 'Last'])) {
        return (string) $props[$prefix . 'Last'];
    }
    if ($idx % 2 === 1 && !empty($props[$prefix . 'Odd'])) {
        return (string) $props[$prefix . 'Odd'];
    }
    if (!empty($props[$prefix])) {
        return (string) $props[$prefix];
    }

    return '';
}

/**
 * @param array<string, mixed> $row
 * @param array<string, mixed> $scriptProperties
 */
function bannerpro_resolve_ad_tpl(array $row, array $scriptProperties, object $pdoFetch, int $total): string
{
    $type = ($row['type'] ?? 'image') === 'html' ? 'html' : 'image';
    $idx = (int) ($row['idx'] ?? $pdoFetch->idx);

    if ($type === 'html') {
        $tpl = bannerpro_pick_tpl_variant($scriptProperties, 'tplHtml', $idx, $total);
        if ($tpl !== '') {
            return $tpl;
        }

        return 'byHtml';
    }

    $tpl = bannerpro_pick_tpl_variant($scriptProperties, 'tplImage', $idx, $total);
    if ($tpl !== '') {
        return $tpl;
    }

    return $pdoFetch->defineChunk($row);
}

/**
 * @param array<string, int|string> $query
 */
function bannerpro_build_click_url(modX $modx, int $adposition, array $query = []): string
{
    if ($adposition <= 0) {
        return '';
    }

    $clickBase = trim((string) $modx->getOption('bannerpro_click', null, 'bannerclick', true), '/');
    $url = '/' . $clickBase . '/' . $adposition;
    if ($query !== []) {
        $qs = http_build_query($query);
        if ($qs !== '') {
            $url .= '?' . $qs;
        }
    }

    return $url;
}

/**
 * @param array<string, mixed> $row
 * @param array<int, modResource> $productsById
 * @return array<string, mixed>
 */
function bannerpro_prepare_ad_row(modX $modx, array $row, int $defaultSource, array $productsById = []): array
{
    $type = ($row['type'] ?? 'image') === 'html' ? 'html' : 'image';
    $adposition = (int) ($row['adposition'] ?? 0);
    $adId = (int) ($row['id'] ?? 0);
    $clickUrl = bannerpro_build_click_url($modx, $adposition);
    $clickUrl = bannerpro_append_utm($modx, $clickUrl, [
        'ad_id' => $adId,
        'position_id' => (int) ($row['position'] ?? 0),
        'adposition' => $adposition,
        'product_id' => (int) ($row['product_id'] ?? 0),
        'product_pagetitle' => bannerpro_product_pagetitle($modx, (int) ($row['product_id'] ?? 0)),
        'order_id' => 0,
    ]);
    $row['click_url'] = $clickUrl;
    $siteUrl = rtrim((string) $modx->getOption('site_url', null, ''), '/');
    $row['click_url_abs'] = $clickUrl !== '' && $siteUrl !== '' ? $siteUrl . $clickUrl : $clickUrl;

    $tagsRaw = $row['tags'] ?? null;
    if (is_array($tagsRaw)) {
        $row['tags'] = implode(',', array_map('strval', $tagsRaw));
    } elseif (is_string($tagsRaw) && $tagsRaw !== '') {
        $decodedTags = json_decode($tagsRaw, true);
        if (is_array($decodedTags)) {
            $row['tags'] = implode(',', $decodedTags);
        }
    }

    if ($type === 'html') {
        $html = (string) ($row['html'] ?? '');
        $url = trim((string) ($row['url'] ?? ''));
        if ($url !== '' && $html !== '' && $clickUrl !== '') {
            $row['html'] = '<a href="' . htmlspecialchars($clickUrl, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-bannerpro-adposition="' . $adposition . '"'
                . ' data-bannerpro-ad-id="' . (int) ($row['id'] ?? 0) . '">'
                . $html . '</a>';
        } else {
            $row['html'] = $html;
        }
        $row['image'] = '';
    } else {
        $row['image'] = bannerpro_resolve_image_url($modx, $row + ['source' => $row['source'] ?? $defaultSource]);
    }

    return bannerpro_enrich_product_placeholders($modx, $row, $defaultSource, $productsById);
}

/**
 * Исключает баннеры, достигшие лимита кликов или показов (0 = без лимита).
 *
 * @param array<string, mixed> $where
 */
function bannerpro_apply_limit_criteria(modX $modx, array &$where, string $class): void
{
    $prefix = (string) $modx->getOption('table_prefix', null, '');
    $clicksTable = $prefix . 'bannerpro_clicks';
    $impressionsTable = $prefix . 'bannerpro_impressions';
    $where[] = "({$class}.max_clicks = 0 OR (SELECT COUNT(*) FROM {$clicksTable} c WHERE c.ad = {$class}.id) < {$class}.max_clicks)";
    $where[] = "({$class}.max_impressions = 0 OR (SELECT COUNT(*) FROM {$impressionsTable} i WHERE i.ad = {$class}.id) < {$class}.max_impressions)";
}

/**
 * @param array<string, mixed> $scriptProperties
 * @return string|void
 */
function bannerpro_render_snippet(modX $modx, array $scriptProperties)
{
    if (!bannerpro_pdofetch_available($modx)) {
        return 'You need to install pdoTools to use this snippet!';
    }

    $pdoFetch = bannerpro_create_pdofetch($modx, $scriptProperties);
    $pdoFetch->addTime('pdoTools loaded');
    bannerpro_add_package($modx);
    bannerpro_register_impression_assets($modx);

    if (!empty($scriptProperties['tplOuter'])) {
        $scriptProperties['tplWrapper'] = $scriptProperties['tplOuter'];
    }
    if (empty($scriptProperties['outputSeparator'])) {
        $scriptProperties['outputSeparator'] = "\n";
    }

    $class = 'byAd';
    $sortbyRaw = $scriptProperties['sortby'] ?? '';
    $abMode = strtolower(trim((string) $sortbyRaw)) === 'ab';
    $abLimitBackup = null;
    if ($abMode) {
        if (isset($scriptProperties['limit']) && (int) $scriptProperties['limit'] > 0) {
            $abLimitBackup = (int) $scriptProperties['limit'];
        }
        $scriptProperties['limit'] = 0;
    }
    $resolvedSortby = bannerpro_resolve_sortby($class, $scriptProperties);
    $sortdir = (string) ($scriptProperties['sortdir'] ?? 'ASC');
    if (strtolower(trim((string) ($scriptProperties['sortby'] ?? ''))) === 'weighted') {
        $sortdir = 'DESC';
    }
    unset($scriptProperties['sortby'], $scriptProperties['sortdir']);

    if (!array_key_exists('resource', $scriptProperties)) {
        $scriptProperties['resource'] = (int) ($modx->resource->get('id') ?? 0);
    }
    if (!array_key_exists('parent', $scriptProperties)) {
        $scriptProperties['parent'] = (int) ($modx->resource->get('parent') ?? 0);
    }

    $cacheKey = null;
    $cacheLifetime = 0;
    if (bannerpro_cache_is_eligible($modx, $scriptProperties, $sortbyRaw)) {
        $cacheKey = bannerpro_cache_key($modx, $scriptProperties, $resolvedSortby);
        $cacheLifetime = bannerpro_cache_lifetime($modx, $scriptProperties);
        $cached = bannerpro_cache_get($modx, $cacheKey);
        if ($cached !== null) {
            if (!empty($scriptProperties['toPlaceholder'])) {
                $modx->setPlaceholder($scriptProperties['toPlaceholder'], $cached);

                return;
            }

            return $cached;
        }
    }
    $tplProperties = $scriptProperties;
    $additionalPlaceholders = [];
    /** @var modSnippet|null $snippet */
    if ($snippet = $modx->getObject('modSnippet', ['name' => 'BannerPro'])) {
        $properties = $snippet->getProperties();
        if (!is_array($properties)) {
            $properties = [];
        }
        foreach ($scriptProperties as $key => $value) {
            if (!isset($properties[$key])) {
                $additionalPlaceholders[$key] = $value;
            }
        }
    }

    $date = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    $where = [
        "({$class}.start IS NULL OR {$class}.start <= '{$date}') AND ({$class}.end IS NULL OR {$class}.end >= '{$date}')",
    ];
    if (empty($scriptProperties['showInactive'])) {
        $where[$class . '.active'] = 1;
    }
    if (
        !empty($scriptProperties['positionKey'])
        && empty($scriptProperties['position'])
        && empty($scriptProperties['positions'])
        && empty($scriptProperties['positionName'])
    ) {
        $scriptProperties['positionName'] = $scriptProperties['positionKey'];
    }
    unset($scriptProperties['positionKey']);

    if (!empty($scriptProperties['position'])) {
        $where['byAdPosition.position'] = $scriptProperties['position'];
    } elseif (!empty($scriptProperties['positions'])) {
        $where['byAdPosition.position:IN'] = array_map('trim', explode(',', (string) $scriptProperties['positions']));
    } elseif (!empty($scriptProperties['positionName'])) {
        $names = array_values(array_filter(array_map('trim', explode(',', (string) $scriptProperties['positionName']))));
        if (count($names) === 0) {
            if (!empty($scriptProperties['toPlaceholder'])) {
                $modx->setPlaceholder($scriptProperties['toPlaceholder'], '');

                return '';
            }

            return '';
        }
        if (count($names) === 1) {
            $where['byPosition.name'] = $names[0];
        } else {
            $where['byPosition.name:IN'] = $names;
        }
    }
    unset($scriptProperties['positionName']);

    bannerpro_apply_position_context_filter($modx, $where, $scriptProperties);
    unset($scriptProperties['context']);

    $filterProductId = (int) ($scriptProperties['productId'] ?? 0);
    bannerpro_apply_product_filter($modx, $where, $class, $filterProductId);
    unset($scriptProperties['productId']);

    $resourceId = (int) $scriptProperties['resource'];
    $parentId = (int) $scriptProperties['parent'];
    bannerpro_apply_resource_target_filter($modx, $where, $class, $resourceId, $parentId);
    unset($scriptProperties['resource'], $scriptProperties['parent']);

    bannerpro_apply_daypart_filter($where, $class, bannerpro_site_now($modx));

    if (array_key_exists('tags', $scriptProperties) && trim((string) $scriptProperties['tags']) !== '') {
        $requestedTags = bannerpro_parse_snippet_tags((string) $scriptProperties['tags']);
        $tagsMode = (string) ($scriptProperties['tagsMode'] ?? 'any');
        bannerpro_apply_tags_filter($where, $class, $requestedTags, $tagsMode);
    }
    unset($scriptProperties['tags'], $scriptProperties['tagsMode']);

    bannerpro_apply_limit_criteria($modx, $where, $class);

    $sortby = $resolvedSortby;

    $innerJoin = [
        'byAdPosition' => [
            'alias' => 'byAdPosition',
            'on' => $class . '.id = byAdPosition.ad',
        ],
        'byPosition' => [
            'alias' => 'byPosition',
            'on' => 'byAdPosition.position = byPosition.id',
        ],
    ];
    $select = [
        $class => 'all',
        'byAdPosition' => '`byAdPosition`.`id` as `adposition`, `byAdPosition`.`ad`, `byAdPosition`.`position` as `position_id`, `byAdPosition`.`weight`',
        'byPosition' => '`byPosition`.`name` as `position_name`',
    ];

    foreach (['where', 'innerJoin', 'select'] as $key) {
        if (!empty($scriptProperties[$key])) {
            $tmp = $modx->fromJSON($scriptProperties[$key]);
            if (is_array($tmp)) {
                ${$key} = array_merge(${$key}, $tmp);
            }
        }
        unset($scriptProperties[$key]);
    }
    $pdoFetch->addTime('Conditions prepared');

    $where = bannerpro_compact_where($where);

    $default = [
        'class' => $class,
        'innerJoin' => $modx->toJSON($innerJoin),
        'where' => $modx->toJSON($where),
        'select' => $modx->toJSON($select),
        'sortby' => $sortby,
        'sortdir' => $sortdir,
        'return' => 'data',
        'disableConditions' => true,
    ];

    $pdoFetch->setConfig(array_merge($default, $scriptProperties));
    $rows = $pdoFetch->run();

    $output = [];
    $defaultSource = (int) $modx->getOption('bannerpro_media_source', null, $modx->getOption('default_media_source'), true);
    $rows = is_array($rows) ? $rows : [];
    if ($abMode) {
        $rows = bannerpro_apply_ab_split($modx, $rows);
        if ($abLimitBackup !== null && $abLimitBackup > 0) {
            $rows = array_slice($rows, 0, $abLimitBackup);
        }
    }
    $total = count($rows);
    $productsById = bannerpro_load_product_resources($modx, bannerpro_collect_product_ids($rows));

    foreach ($rows as $row) {
        $row['idx'] = $pdoFetch->idx++;
        $row = bannerpro_prepare_ad_row($modx, $row, $defaultSource, $productsById);
        $tpl = bannerpro_resolve_ad_tpl($row, $tplProperties, $pdoFetch, $total);
        if (!empty($additionalPlaceholders)) {
            $row = array_merge($additionalPlaceholders, $row);
        }

        $chunkHtml = !empty($tpl)
            ? $pdoFetch->getChunk($tpl, $row, bannerpro_pdofetch_fast_mode($pdoFetch))
            : '<pre>' . $pdoFetch->getChunk('', $row) . '</pre>';
        if (bannerpro_track_impressions_enabled($modx)) {
            $chunkHtml = bannerpro_wrap_impression_html($chunkHtml, (int) ($row['adposition'] ?? 0), [
                'ad_id' => (int) ($row['id'] ?? 0),
                'ad_name' => (string) ($row['name'] ?? ''),
                'position_name' => (string) ($row['position_name'] ?? ''),
            ]);
        }
        $output[] = $chunkHtml;
    }

    if ($modx->user->hasSessionContext('mgr') && !empty($scriptProperties['showLog'])) {
        $output['log'] = '<pre class="pdoUsersLog">' . print_r($pdoFetch->getTime(), true) . '</pre>';
    }

    $outputSeparator = $scriptProperties['outputSeparator'];
    $toSeparatePlaceholders = $scriptProperties['toSeparatePlaceholders'] ?? null;
    $tplWrapper = $scriptProperties['tplWrapper'] ?? null;
    $wrapIfEmpty = $scriptProperties['wrapIfEmpty'] ?? null;
    $toPlaceholder = $scriptProperties['toPlaceholder'] ?? null;

    if (!empty($toSeparatePlaceholders)) {
        $modx->setPlaceholders($output, $toSeparatePlaceholders);
        return;
    }

    $result = implode($outputSeparator, $output);
    if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || $result !== '')) {
        $result = $pdoFetch->getChunk($tplWrapper, ['output' => $result], bannerpro_pdofetch_fast_mode($pdoFetch));
    }

    if ($cacheKey !== null) {
        bannerpro_cache_set($modx, $cacheKey, $result, $cacheLifetime);
    }

    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $result);
        return;
    }

    return $result;
}

function bannerpro_ab_ttl_days(modX $modx): int
{
    return max(1, (int) $modx->getOption('bannerpro_ab_ttl', null, 30));
}

function bannerpro_ab_cookie_name(int $positionId): string
{
    return 'bannerpro_ab_' . $positionId;
}

function bannerpro_set_ab_cookie(string $cookieName, int $adId, int $ttlDays): void
{
    setcookie($cookieName, (string) $adId, [
        'expires' => time() + 86400 * $ttlDays,
        'path' => '/',
        'secure' => bannerpro_is_https_request(),
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
}

/**
 * @param list<array<string, mixed>> $rows
 * @return list<array<string, mixed>>
 */
function bannerpro_apply_ab_split(modX $modx, array $rows): array
{
    if ($rows === []) {
        return [];
    }

    $byPosition = [];
    $orphanRows = [];
    foreach ($rows as $row) {
        $positionId = (int) ($row['position_id'] ?? 0);
        if ($positionId <= 0) {
            $orphanRows[] = $row;
            continue;
        }
        $byPosition[$positionId][] = $row;
    }

    $result = [];
    foreach ($byPosition as $positionId => $group) {
        if (count($group) !== 2) {
            foreach ($group as $row) {
                $result[] = $row;
            }
            continue;
        }

        usort($group, static fn (array $a, array $b): int => ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0)));
        $cookieName = bannerpro_ab_cookie_name($positionId);
        $pickedId = (int) ($_COOKIE[$cookieName] ?? 0);
        $adIds = [(int) ($group[0]['id'] ?? 0), (int) ($group[1]['id'] ?? 0)];

        $picked = null;
        if ($pickedId > 0 && in_array($pickedId, $adIds, true)) {
            foreach ($group as $row) {
                if ((int) ($row['id'] ?? 0) === $pickedId) {
                    $picked = $row;
                    break;
                }
            }
        }

        if ($picked === null) {
            $picked = $group[random_int(0, 1)];
            $pickedId = (int) ($picked['id'] ?? 0);
            if ($pickedId > 0 && !headers_sent()) {
                bannerpro_set_ab_cookie($cookieName, $pickedId, bannerpro_ab_ttl_days($modx));
            }
        }

        if ($picked !== null) {
            $result[] = $picked;
        }
    }

    foreach ($orphanRows as $row) {
        $result[] = $row;
    }

    return $result;
}

function bannerpro_is_https_request(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    return isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443;
}

function bannerpro_set_attribution_cookie(int $clickId, int $ttlDays): void
{
    setcookie('bannerpro_click_id', (string) $clickId, [
        'expires' => time() + 86400 * $ttlDays,
        'path' => '/',
        'secure' => bannerpro_is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * @param array<string, int|string> $params
 */
function bannerpro_merge_query_params(string $url, array $params): string
{
    if ($url === '' || $params === []) {
        return $url;
    }

    $fragment = '';
    if (($hashPos = strpos($url, '#')) !== false) {
        $fragment = substr($url, $hashPos);
        $url = substr($url, 0, $hashPos);
    }

    $existing = [];
    $base = $url;
    if (($queryPos = strpos($url, '?')) !== false) {
        parse_str(substr($url, $queryPos + 1), $existing);
        $base = substr($url, 0, $queryPos);
    }

    foreach ($params as $key => $value) {
        if ($value !== '' && !array_key_exists($key, $existing)) {
            $existing[$key] = $value;
        }
    }

    $query = http_build_query($existing);

    return $base . ($query !== '' ? '?' . $query : '') . $fragment;
}

/**
 * @param array<string, int|string> $context
 */
function bannerpro_apply_utm_template(string $template, array $context): string
{
    $value = $template;
    foreach ($context as $key => $ctxValue) {
        $value = str_replace('{' . $key . '}', (string) $ctxValue, $value);
    }

    return $value;
}

/**
 * @param array<string, int|string> $context
 */
function bannerpro_append_utm(modX $modx, string $url, array $context): string
{
    if (!(bool) $modx->getOption('bannerpro_utm_enabled', null, false)) {
        return $url;
    }

    if ($url === '') {
        return $url;
    }

    $params = [];
    $source = trim((string) $modx->getOption('bannerpro_utm_source', null, 'bannerpro'));
    $medium = trim((string) $modx->getOption('bannerpro_utm_medium', null, 'banner'));
    $campaignTemplate = trim((string) $modx->getOption('bannerpro_utm_campaign', null, ''));
    $contentTemplate = trim((string) $modx->getOption('bannerpro_utm_content', null, '{ad_id}-{adposition}'));

    if ($source !== '') {
        $params['utm_source'] = $source;
    }
    if ($medium !== '') {
        $params['utm_medium'] = $medium;
    }
    if ($campaignTemplate !== '') {
        $campaign = bannerpro_apply_utm_template($campaignTemplate, $context);
        if ($campaign !== '') {
            $params['utm_campaign'] = $campaign;
        }
    }

    $content = bannerpro_apply_utm_template($contentTemplate, $context);
    if ($content !== '') {
        $params['utm_content'] = $content;
    }

    return bannerpro_merge_query_params($url, $params);
}

function bannerpro_handle_clickout(modX $modx): void
{
    if ($modx->event->name !== 'OnPageNotFound') {
        return;
    }

    $bannerproClick = trim((string) $modx->getOption('bannerpro_click', null, 'bannerclick', true), '/');
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
    $httpReferer = $_SERVER['HTTP_REFERER'] ?? '';
    $pattern = '#/' . preg_quote($bannerproClick, '#') . '/([0-9]+)#';

    if ($bannerproClick === '' || !preg_match($pattern, $requestUri, $matches)) {
        return;
    }

    bannerpro_add_package($modx);

    $adpositionId = (int) $matches[1];
    /** @var byAdPosition|null $link */
    $link = $modx->getObject('byAdPosition', $adpositionId);
    if (!$link) {
        return;
    }

    $adId = (int) $link->get('ad');
    $positionId = (int) $link->get('position');
    /** @var byAd|null $ad */
    $ad = $modx->getObject('byAd', $adId);
    if (!$ad) {
        return;
    }

    $key = [
        'ad' => $adId,
        'position' => $positionId,
        'ip' => $remoteAddr,
        'clickdate:LIKE' => (new DateTimeImmutable())->format('Y-m-d') . '%',
    ];
    $clickDuplicate = $modx->getCount('byClick', $key) > 0;
    $botFiltered = bannerpro_request_is_bot($modx);
    $clickRecorded = false;
    $clickId = 0;
    if (!$clickDuplicate && !$botFiltered) {
        $click = $modx->newObject('byClick', [
            'ad' => $adId,
            'position' => $positionId,
            'clickdate' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'referrer' => $httpReferer,
            'ip' => $remoteAddr,
            'order_id' => 0,
        ]);
        $clickRecorded = (bool) $click->save();
        if ($clickRecorded) {
            $clickId = (int) $click->get('id');
            $ttlDays = max(1, (int) $modx->getOption('bannerpro_attribution_ttl', null, 30));
            bannerpro_set_attribution_cookie($clickId, $ttlDays);
        }
    }

    $url = trim((string) $ad->get('url'));
    if ($url !== '') {
        $chunk = $modx->newObject('modChunk');
        $chunk->set('name', 'bannerproPosition' . $adpositionId);
        $chunk->setContent($url);
        $url = trim((string) $chunk->process($_GET));
    }

    bannerpro_invoke_click_event($modx, array_merge(
        bannerpro_event_payload($modx, $adId, $positionId, $adpositionId),
        [
            'referrer' => $httpReferer,
            'ip' => $remoteAddr,
            'recorded' => $clickRecorded,
            'duplicate' => $clickDuplicate,
            'bot_filtered' => $botFiltered,
            'redirect_url' => $url,
        ]
    ));

    if ($url === '') {
        $modx->sendErrorPage();

        return;
    }

    $utmProductId = (int) $ad->get('product_id');
    $url = bannerpro_append_utm($modx, $url, [
        'ad_id' => $adId,
        'position_id' => $positionId,
        'adposition' => $adpositionId,
        'product_id' => $utmProductId,
        'product_pagetitle' => bannerpro_product_pagetitle($modx, $utmProductId),
        'order_id' => 0,
    ]);

    if (!$botFiltered) {
        bannerpro_dispatch_webhook($modx, bannerpro_click_webhook_payload(
            $adId,
            $positionId,
            $adpositionId,
            $httpReferer,
            $remoteAddr,
            $clickId,
            $url,
            $clickRecorded,
            $clickDuplicate
        ));
    }

    $modx->sendRedirect($url);
}
