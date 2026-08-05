<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array<string, array{0: callable(modX): void, 1: string|null}>
 */
function bannerpro_action_handlers(): array
{
    return [
        'ads_getlist' => ['bannerpro_handle_ads_getlist', null],
        'ads_get' => ['bannerpro_handle_ads_get', null],
        'ads_create' => ['bannerpro_handle_ads_create', 'bannerpro_save'],
        'ads_update' => ['bannerpro_handle_ads_update', 'bannerpro_save'],
        'ads_duplicate' => ['bannerpro_handle_ads_duplicate', 'bannerpro_save'],
        'ads_remove' => ['bannerpro_handle_ads_remove', 'bannerpro_remove'],
        'ads_enable' => [static fn (modX $modx) => bannerpro_handle_ads_set_active($modx, true), 'bannerpro_save'],
        'ads_disable' => [static fn (modX $modx) => bannerpro_handle_ads_set_active($modx, false), 'bannerpro_save'],
        'ads_getclicks' => ['bannerpro_handle_ads_getclicks', 'bannerpro_stats'],
        'positions_getlist' => ['bannerpro_handle_positions_getlist', null],
        'positions_create' => ['bannerpro_handle_positions_create', 'bannerpro_save'],
        'positions_update' => ['bannerpro_handle_positions_update', 'bannerpro_save'],
        'positions_duplicate' => ['bannerpro_handle_positions_duplicate', 'bannerpro_save'],
        'positions_remove' => ['bannerpro_handle_positions_remove', 'bannerpro_remove'],
        'adpositions_getlist' => ['bannerpro_handle_adpositions_getlist', null],
        'adpositions_add' => ['bannerpro_handle_adpositions_add', 'bannerpro_save'],
        'adpositions_remove' => ['bannerpro_handle_adpositions_remove', 'bannerpro_remove'],
        'adpositions_sort' => ['bannerpro_handle_adpositions_sort', 'bannerpro_save'],
        'adpositions_update_weight' => ['bannerpro_handle_adpositions_update_weight', 'bannerpro_save'],
        'clicks_getreferrers' => ['bannerpro_handle_clicks_getreferrers', 'bannerpro_stats'],
        'stats_summary' => ['bannerpro_handle_stats_summary', 'bannerpro_stats'],
        'stats_by_day' => ['bannerpro_handle_stats_by_day', 'bannerpro_stats'],
        'stats_top_ads' => ['bannerpro_handle_stats_top_ads', 'bannerpro_stats'],
        'stats_export' => ['bannerpro_handle_stats_export', 'bannerpro_stats'],
        'stats_compare' => ['bannerpro_handle_stats_compare', 'bannerpro_stats'],
        'stats_purge' => ['bannerpro_handle_stats_purge', 'bannerpro_remove'],
        'resource_getlist' => ['bannerpro_handle_resource_getlist', null],
        'ads_bulk_enable' => ['bannerpro_handle_ads_bulk_enable', 'bannerpro_save'],
        'ads_bulk_disable' => ['bannerpro_handle_ads_bulk_disable', 'bannerpro_save'],
        'ads_bulk_remove' => ['bannerpro_handle_ads_bulk_remove', 'bannerpro_remove'],
        'ads_bulk_assign_positions' => ['bannerpro_handle_ads_bulk_assign_positions', 'bannerpro_save'],
        'ad_templates_getlist' => ['bannerpro_handle_ad_templates_getlist', null],
        'ads_create_from_template' => ['bannerpro_handle_ads_create_from_template', 'bannerpro_save'],
        'audit_getlist' => ['bannerpro_handle_audit_getlist', 'bannerpro_save'],
        'tags_suggest' => ['bannerpro_handle_tags_suggest', null],
        'settings_utm_get' => ['bannerpro_handle_settings_utm_get', 'bannerpro_save'],
        'settings_utm_update' => ['bannerpro_handle_settings_utm_update', 'bannerpro_save'],
        'settings_integrations_get' => ['bannerpro_handle_settings_integrations_get', 'bannerpro_save'],
        'settings_integrations_update' => ['bannerpro_handle_settings_integrations_update', 'bannerpro_save'],
    ];
}

function bannerpro_stats_position_id(): int
{
    return bannerpro_int_post('position');
}

function bannerpro_parse_active_filter(): ?int
{
    if (!array_key_exists('active', $_POST)) {
        return null;
    }
    $value = bannerpro_post('active');
    if ($value === '' || $value === null) {
        return null;
    }

    return (int) $value ? 1 : 0;
}

function bannerpro_parse_type_filter(): ?string
{
    $type = (string) bannerpro_post('type', '');
    if ($type === 'image' || $type === 'html') {
        return $type;
    }

    return null;
}

function bannerpro_parse_product_id_filter(): ?int
{
    if (!array_key_exists('product_id', $_POST)) {
        return null;
    }
    $value = bannerpro_post('product_id');
    if ($value === '' || $value === null) {
        return null;
    }

    return max(0, (int) $value);
}

function bannerpro_parse_category_id_filter(): ?int
{
    if (!array_key_exists('category_id', $_POST)) {
        return null;
    }
    $value = bannerpro_post('category_id');
    if ($value === '' || $value === null) {
        return null;
    }

    return max(0, (int) $value);
}

function bannerpro_handle_ads_getlist(modX $modx): void
{
    $result = bannerpro_ads_get_list(
        $modx,
        bannerpro_int_post('start'),
        bannerpro_int_post('limit', 20),
        (string) bannerpro_post('sort', 'id'),
        (string) bannerpro_post('dir', 'ASC'),
        (string) bannerpro_post('query', ''),
        bannerpro_int_post('position'),
        (string) bannerpro_post('mode', 'include'),
        bannerpro_parse_active_filter(),
        bannerpro_parse_type_filter(),
        bannerpro_parse_product_id_filter(),
        (string) bannerpro_post('tag', ''),
        (int) bannerpro_post('on_schedule', 0) === 1,
        bannerpro_parse_category_id_filter()
    );
    bannerpro_json_success($result['data'], $result['total']);
}

function bannerpro_handle_ads_get(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_ads_get($modx, bannerpro_int_post('id')));
}

function bannerpro_handle_ads_create(modX $modx): void
{
    $positions = bannerpro_post('positions');
    if (!is_array($positions)) {
        $positions = [];
    }
    bannerpro_dispatch_result(bannerpro_ads_create($modx, bannerpro_extract_ad_fields($_POST, $modx), $positions));
}

function bannerpro_handle_ads_update(modX $modx): void
{
    $positions = array_key_exists('positions', $_POST) ? bannerpro_post('positions') : null;
    if ($positions !== null && !is_array($positions)) {
        $positions = [];
    }
    bannerpro_dispatch_result(bannerpro_ads_update(
        $modx,
        bannerpro_int_post('id'),
        bannerpro_extract_ad_fields($_POST, $modx),
        $positions
    ));
}

function bannerpro_handle_ads_duplicate(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_ads_duplicate($modx, bannerpro_int_post('id')));
}

function bannerpro_handle_ads_remove(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_ads_remove($modx, bannerpro_int_post('id')), true);
}

function bannerpro_handle_ads_set_active(modX $modx, bool $active): void
{
    bannerpro_dispatch_result(bannerpro_ads_set_active($modx, bannerpro_int_post('id'), $active));
}

function bannerpro_handle_ads_getclicks(modX $modx): void
{
    $result = bannerpro_ads_get_clicks(
        $modx,
        bannerpro_period_from_request(),
        bannerpro_stats_position_id()
    );
    bannerpro_json_success($result['data'], $result['total']);
}

function bannerpro_handle_positions_getlist(modX $modx): void
{
    $result = bannerpro_positions_get_list(
        $modx,
        bannerpro_int_post('start'),
        bannerpro_int_post('limit', 20),
        (string) bannerpro_post('sort', 'id'),
        (string) bannerpro_post('dir', 'ASC')
    );
    bannerpro_json_success($result['data'], $result['total']);
}

function bannerpro_handle_positions_create(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_positions_create($modx, bannerpro_extract_position_fields($_POST)));
}

function bannerpro_handle_positions_update(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_positions_update(
        $modx,
        bannerpro_int_post('id'),
        bannerpro_extract_position_fields($_POST)
    ));
}

function bannerpro_handle_positions_duplicate(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_positions_duplicate($modx, bannerpro_int_post('id')));
}

function bannerpro_handle_positions_remove(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_positions_remove($modx, bannerpro_int_post('id')), true);
}

function bannerpro_handle_adpositions_getlist(modX $modx): void
{
    $result = bannerpro_adpositions_get_list(
        $modx,
        bannerpro_int_post('position'),
        bannerpro_int_post('start'),
        bannerpro_int_post('limit', 20)
    );
    bannerpro_json_success($result['data'], $result['total']);
}

function bannerpro_handle_adpositions_add(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_adpositions_add(
        $modx,
        bannerpro_int_post('position'),
        bannerpro_int_post('ad')
    ));
}

function bannerpro_handle_adpositions_remove(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_adpositions_remove($modx, bannerpro_int_post('id')), true);
}

function bannerpro_handle_adpositions_sort(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_adpositions_sort(
        $modx,
        bannerpro_int_post('id'),
        bannerpro_int_post('new_order'),
        bannerpro_int_post('old_order')
    ), true);
}

function bannerpro_handle_adpositions_update_weight(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_adpositions_update_weight(
        $modx,
        bannerpro_int_post('id'),
        bannerpro_int_post('weight', 1)
    ));
}

function bannerpro_handle_clicks_getreferrers(modX $modx): void
{
    $result = bannerpro_clicks_get_referrers(
        $modx,
        bannerpro_period_from_request(),
        bannerpro_int_post('start'),
        bannerpro_int_post('limit', 20),
        bannerpro_stats_position_id()
    );
    bannerpro_json_success($result['data'], $result['total']);
}

function bannerpro_handle_stats_summary(modX $modx): void
{
    bannerpro_json_success(bannerpro_stats_summary(
        $modx,
        bannerpro_period_from_request(),
        bannerpro_stats_position_id()
    ));
}

function bannerpro_handle_stats_by_day(modX $modx): void
{
    bannerpro_json_success(bannerpro_stats_by_day(
        $modx,
        bannerpro_period_from_request(),
        bannerpro_stats_position_id()
    ));
}

function bannerpro_handle_stats_top_ads(modX $modx): void
{
    $period = bannerpro_period_from_request();
    $positionId = bannerpro_stats_position_id();
    $start = bannerpro_int_post('start', 0);
    $limit = bannerpro_int_post('limit', 20);
    $rows = bannerpro_stats_build_top_ads_rows($modx, $period, $positionId);
    $total = count($rows);
    if ($limit > 0) {
        $data = array_slice($rows, $start, $limit);
    } elseif ($start > 0) {
        $data = array_slice($rows, $start);
    } else {
        $data = $rows;
    }
    bannerpro_json_success($data, $total);
}

function bannerpro_handle_stats_export(modX $modx): void
{
    bannerpro_stats_export_csv(
        $modx,
        bannerpro_period_from_request(),
        bannerpro_stats_position_id(),
        (string) bannerpro_post('type', 'clicks')
    );
}

function bannerpro_handle_stats_compare(modX $modx): void
{
    $base = bannerpro_normalize_period((string) bannerpro_post('base', BannerProClickPeriod::THIS_WEEK));
    $compare = bannerpro_normalize_period((string) bannerpro_post('compare', BannerProClickPeriod::LAST_WEEK));
    bannerpro_json_success(bannerpro_stats_compare($modx, $base, $compare, bannerpro_stats_position_id()));
}

function bannerpro_handle_stats_purge(modX $modx): void
{
    bannerpro_json_success(bannerpro_purge_old_stats($modx));
}

function bannerpro_handle_resource_getlist(modX $modx): void
{
    $result = bannerpro_resource_get_list(
        $modx,
        (string) bannerpro_post('query', ''),
        bannerpro_int_post('start'),
        bannerpro_int_post('limit', 20),
        (string) bannerpro_post('class_key', '')
    );
    bannerpro_json_success($result['data'], $result['total']);
}

/**
 * @param callable(modX, list<int>): array{success: bool, message?: string, data?: mixed} $callback
 */
function bannerpro_handle_bulk_ids(modX $modx, callable $callback): void
{
    $parsed = bannerpro_bulk_parse_ids();
    if (isset($parsed['error'])) {
        bannerpro_json_error($modx->lexicon('bannerpro.bulk.error.' . $parsed['error']) ?: $parsed['error']);
    }
    bannerpro_dispatch_result($callback($modx, $parsed['ids']));
}

function bannerpro_handle_ads_bulk_enable(modX $modx): void
{
    bannerpro_handle_bulk_ids($modx, static fn (modX $m, array $ids) => bannerpro_ads_bulk_enable($m, $ids));
}

function bannerpro_handle_ads_bulk_disable(modX $modx): void
{
    bannerpro_handle_bulk_ids($modx, static fn (modX $m, array $ids) => bannerpro_ads_bulk_disable($m, $ids));
}

function bannerpro_handle_ads_bulk_remove(modX $modx): void
{
    bannerpro_handle_bulk_ids($modx, static fn (modX $m, array $ids) => bannerpro_ads_bulk_remove($m, $ids));
}

function bannerpro_handle_ads_bulk_assign_positions(modX $modx): void
{
    bannerpro_handle_bulk_ids($modx, static function (modX $m, array $ids) {
        $positions = bannerpro_post('positions', []);
        if (!is_array($positions)) {
            $positions = [];
        }
        $replace = (int) bannerpro_post('replace', 0) === 1;

        return bannerpro_ads_bulk_assign_positions($m, $ids, $positions, $replace);
    });
}

function bannerpro_handle_ad_templates_getlist(modX $modx): void
{
    $result = bannerpro_ad_templates_get_list($modx);
    bannerpro_json_success($result['data'], $result['total']);
}

function bannerpro_handle_ads_create_from_template(modX $modx): void
{
    $positions = bannerpro_post('positions');
    if (!is_array($positions)) {
        $positions = [];
    }
    bannerpro_dispatch_result(bannerpro_ads_create_from_template(
        $modx,
        bannerpro_int_post('template_id'),
        $positions
    ));
}

function bannerpro_handle_audit_getlist(modX $modx): void
{
    $entity = (string) bannerpro_post('entity', '');
    $action = (string) bannerpro_post('action_filter', '');
    $result = bannerpro_audit_get_list(
        $modx,
        bannerpro_int_post('start'),
        bannerpro_int_post('limit', 20),
        (string) bannerpro_post('sort', 'id'),
        (string) bannerpro_post('dir', 'DESC'),
        $entity !== '' ? $entity : null,
        $action !== '' ? $action : null
    );
    bannerpro_json_success($result['data'], $result['total']);
}

function bannerpro_handle_tags_suggest(modX $modx): void
{
    $tags = bannerpro_tags_suggest(
        $modx,
        (string) bannerpro_post('query', ''),
        bannerpro_int_post('limit', 20)
    );
    bannerpro_json_success($tags, count($tags));
}

function bannerpro_handle_settings_utm_get(modX $modx): void
{
    bannerpro_json_success(bannerpro_utm_settings_get($modx));
}

function bannerpro_handle_settings_utm_update(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_utm_settings_update($modx, [
        'utm_enabled' => bannerpro_post('utm_enabled'),
        'utm_source' => bannerpro_post('utm_source'),
        'utm_medium' => bannerpro_post('utm_medium'),
        'utm_campaign' => bannerpro_post('utm_campaign'),
        'utm_content' => bannerpro_post('utm_content'),
    ]));
}

function bannerpro_handle_settings_integrations_get(modX $modx): void
{
    bannerpro_json_success(bannerpro_integration_settings_get($modx));
}

function bannerpro_handle_settings_integrations_update(modX $modx): void
{
    bannerpro_dispatch_result(bannerpro_integration_settings_update($modx, [
        'webhook_url' => bannerpro_post('webhook_url'),
        'webhook_impression_url' => bannerpro_post('webhook_impression_url'),
        'webhook_secret' => bannerpro_post('webhook_secret'),
        'ab_ttl' => bannerpro_post('ab_ttl'),
    ]));
}
