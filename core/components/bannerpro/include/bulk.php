<?php

defined('MODX_CORE_PATH') || exit;

const BANNERPRO_BULK_MAX_IDS = 100;

/**
 * @return array{ids?: list<int>, error?: string}
 */
function bannerpro_bulk_parse_ids(): array
{
    $raw = bannerpro_post('ids', []);
    if (!is_array($raw)) {
        $raw = $raw !== '' && $raw !== null ? [(int) $raw] : [];
    }
    $ids = array_values(array_unique(array_filter(array_map('intval', $raw), static fn (int $id): bool => $id > 0)));
    if ($ids === []) {
        return ['error' => 'ids_required'];
    }
    if (count($ids) > BANNERPRO_BULK_MAX_IDS) {
        return ['error' => 'ids_too_many'];
    }

    return ['ids' => $ids];
}

/**
 * @return list<int>
 */
function bannerpro_ad_position_ids(modX $modx, int $adId): array
{
    $ids = [];
    foreach ($modx->getCollection('byAdPosition', ['ad' => $adId]) as $adPosition) {
        $ids[] = (int) $adPosition->get('position');
    }

    return $ids;
}

/**
 * @param list<int> $positionIds
 */
function bannerpro_merge_ad_positions(modX $modx, int $adId, array $positionIds): void
{
    $merged = array_values(array_unique(array_merge(
        bannerpro_ad_position_ids($modx, $adId),
        array_map('intval', $positionIds)
    )));
    bannerpro_sync_ad_positions($modx, $adId, $merged);
}

/**
 * @return array{success: bool, message?: string, data?: array{affected: int, errors: list<string>}}
 */
function bannerpro_ads_bulk_enable(modX $modx, array $ids): array
{
    return bannerpro_ads_bulk_set_active($modx, $ids, true);
}

/**
 * @return array{success: bool, message?: string, data?: array{affected: int, errors: list<string>}}
 */
function bannerpro_ads_bulk_disable(modX $modx, array $ids): array
{
    return bannerpro_ads_bulk_set_active($modx, $ids, false);
}

/**
 * @return array{success: bool, message?: string, data?: array{affected: int, errors: list<string>}}
 */
function bannerpro_ads_bulk_set_active(modX $modx, array $ids, bool $active): array
{
    $affected = 0;
    $errors = [];
    foreach ($ids as $id) {
        $result = bannerpro_ads_set_active($modx, $id, $active, true);
        if ($result['success']) {
            ++$affected;
        } else {
            $errors[] = (string) ($result['message'] ?? (string) $id);
        }
    }
    if ($affected > 0) {
        bannerpro_cache_invalidate($modx);
        bannerpro_audit_log(
            $modx,
            $active ? 'bulk_enable' : 'bulk_disable',
            'ad',
            0,
            ['ids' => $ids, 'affected' => $affected]
        );
    }

    return ['success' => true, 'data' => ['affected' => $affected, 'errors' => $errors]];
}

/**
 * @return array{success: bool, message?: string, data?: array{affected: int, errors: list<string>}}
 */
function bannerpro_ads_bulk_remove(modX $modx, array $ids): array
{
    $affected = 0;
    $errors = [];
    $removed = [];
    foreach ($ids as $id) {
        /** @var byAd|null $ad */
        $ad = $modx->getObject('byAd', $id);
        $name = $ad ? (string) $ad->get('name') : '';
        $result = bannerpro_ads_remove($modx, $id, true);
        if ($result['success']) {
            ++$affected;
            $removed[] = ['id' => $id, 'name' => $name];
        } else {
            $errors[] = (string) ($result['message'] ?? (string) $id);
        }
    }
    if ($affected > 0) {
        bannerpro_audit_log($modx, 'bulk_delete', 'ad', 0, ['removed' => $removed, 'affected' => $affected]);
    }

    return ['success' => true, 'data' => ['affected' => $affected, 'errors' => $errors]];
}

/**
 * @param list<int> $positionIds
 * @return array{success: bool, message?: string, data?: array{affected: int, errors: list<string>}}
 */
function bannerpro_ads_bulk_assign_positions(modX $modx, array $ids, array $positionIds, bool $replace): array
{
    $positionIds = array_values(array_unique(array_filter(array_map('intval', $positionIds), static fn (int $id): bool => $id > 0)));
    if ($positionIds === []) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.bulk.positions_required') ?: 'Positions required'];
    }

    $affected = 0;
    $errors = [];
    foreach ($ids as $id) {
        /** @var byAd|null $ad */
        $ad = $modx->getObject('byAd', $id);
        if (!$ad) {
            $errors[] = (string) $id;
            continue;
        }
        if ($replace) {
            bannerpro_sync_ad_positions($modx, $id, $positionIds);
        } else {
            bannerpro_merge_ad_positions($modx, $id, $positionIds);
        }
        ++$affected;
    }
    if ($affected > 0) {
        bannerpro_cache_invalidate($modx);
        bannerpro_audit_log($modx, 'bulk_assign_positions', 'ad', 0, [
            'ids' => $ids,
            'positions' => $positionIds,
            'replace' => $replace,
            'affected' => $affected,
        ]);
    }

    return ['success' => true, 'data' => ['affected' => $affected, 'errors' => $errors]];
}
