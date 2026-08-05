<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @param array<string, mixed> $params
 */
function bannerpro_rest_ads(modX $modx, array $params): void
{
    $activeFilter = null;
    if (array_key_exists('active', $params) && $params['active'] !== '') {
        $activeFilter = (int) $params['active'] ? 1 : 0;
    }

    $typeFilter = null;
    $type = (string) ($params['type'] ?? '');
    if ($type === 'image' || $type === 'html') {
        $typeFilter = $type;
    }

    $productIdFilter = null;
    if (array_key_exists('product_id', $params) && $params['product_id'] !== '') {
        $productIdFilter = max(0, (int) $params['product_id']);
    }

    $result = bannerpro_ads_get_list(
        $modx,
        max(0, (int) ($params['offset'] ?? 0)),
        max(1, min(500, (int) ($params['limit'] ?? 20))),
        'id',
        'ASC',
        (string) ($params['query'] ?? ''),
        (int) ($params['position'] ?? 0),
        'include',
        $activeFilter,
        $typeFilter,
        $productIdFilter
    );
    bannerpro_json_success($result['data'], $result['total']);
}

/**
 * @param array<string, mixed> $params
 */
function bannerpro_rest_ad_get(modX $modx, array $params): void
{
    $id = (int) ($params['id'] ?? 0);
    if ($id <= 0) {
        bannerpro_json_error('invalid id', 400);
    }
    $result = bannerpro_ads_get($modx, $id);
    if (!$result['success']) {
        bannerpro_json_error($result['message'] ?? 'not found', 404);
    }
    $row = $result['data'] ?? [];
    $row['clicks'] = bannerpro_fetch_click_counts($modx, 'ad', ['ad' => $id])[$id] ?? 0;
    $row['impressions'] = bannerpro_fetch_impression_counts($modx, 'ad', ['ad' => $id])[$id] ?? 0;
    $row['conversions'] = bannerpro_fetch_conversion_counts($modx, 'ad', ['ad' => $id])[$id] ?? 0;
    bannerpro_json_success($row);
}

/**
 * @param array<string, mixed> $params
 */
function bannerpro_rest_positions(modX $modx, array $params): void
{
    $result = bannerpro_positions_get_list(
        $modx,
        max(0, (int) ($params['offset'] ?? 0)),
        max(1, min(500, (int) ($params['limit'] ?? 20))),
        'id',
        'ASC'
    );
    bannerpro_json_success($result['data'], $result['total']);
}

/**
 * @param array<string, mixed> $params
 */
function bannerpro_rest_stats(modX $modx, array $params): void
{
    $period = bannerpro_normalize_period((string) ($params['period'] ?? 'all'));
    $positionId = (int) ($params['position'] ?? 0);
    bannerpro_json_success([
        'summary' => bannerpro_stats_summary($modx, $period, $positionId),
        'by_day' => bannerpro_stats_by_day($modx, $period, $positionId),
        'top_ads' => bannerpro_stats_top_ads($modx, $period, $positionId, (int) ($params['limit'] ?? 10)),
    ]);
}

/**
 * @param array<string, mixed> $params
 */
function bannerpro_rest_stats_compare(modX $modx, array $params): void
{
    $base = bannerpro_normalize_period((string) ($params['base'] ?? BannerProClickPeriod::THIS_WEEK));
    $compare = bannerpro_normalize_period((string) ($params['compare'] ?? BannerProClickPeriod::LAST_WEEK));
    $positionId = (int) ($params['position'] ?? 0);
    bannerpro_json_success(bannerpro_stats_compare($modx, $base, $compare, $positionId));
}

/**
 * @return array<string, callable(modX, array<string, mixed>): void>
 */
function bannerpro_rest_routes(): array
{
    return [
        'GET /ads' => 'bannerpro_rest_ads',
        'GET /positions' => 'bannerpro_rest_positions',
        'GET /stats' => 'bannerpro_rest_stats',
        'GET /stats/compare' => 'bannerpro_rest_stats_compare',
    ];
}

/**
 * @param array<string, mixed> $query
 */
function bannerpro_rest_dispatch(modX $modx, string $method, string $path, array $query): void
{
    $method = strtoupper($method);
    $path = rtrim($path, '/') ?: '/';

    if ($method === 'GET' && preg_match('#^/ads/(\d+)$#', $path, $matches)) {
        bannerpro_rest_ad_get($modx, ['id' => (int) $matches[1]] + $query);

        return;
    }

    $routes = bannerpro_rest_routes();
    $handler = $routes[$method . ' ' . $path] ?? null;
    if ($handler === null || !function_exists($handler)) {
        bannerpro_json_error('not found', 404);
    }
    $handler($modx, $query);
}
