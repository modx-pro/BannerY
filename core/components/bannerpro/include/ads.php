<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array<string, mixed>
 */
function bannerpro_normalize_ad_type(string $type): string
{
    return $type === 'html' ? 'html' : 'image';
}

function bannerpro_extract_ad_fields(array $input, ?modX $modx = null): array
{
    $allowed = [
        'name', 'url', 'image', 'source', 'active', 'description', 'type', 'html', 'start', 'end',
        'newimage', 'product_id', 'category_id', 'max_clicks', 'max_impressions', 'show_hours',
        'target_resource_id', 'target_parent_id', 'tags',
    ];
    $fields = [];
    foreach ($allowed as $key) {
        if (array_key_exists($key, $input)) {
            $fields[$key] = $input[$key];
        }
    }
    if (!empty($fields['newimage'])) {
        $fields['image'] = $fields['newimage'];
    }
    unset($fields['newimage']);
    if (isset($fields['active'])) {
        $fields['active'] = (int) (bool) $fields['active'];
    }
    if (array_key_exists('type', $fields)) {
        $fields['type'] = bannerpro_normalize_ad_type((string) $fields['type']);
    }
    $type = bannerpro_normalize_ad_type((string) ($fields['type'] ?? 'image'));
    $fields['type'] = $type;
    if ($type === 'image') {
        $fields['html'] = null;
    }
    if (array_key_exists('product_id', $fields)) {
        $productId = (int) $fields['product_id'];
        $fields['product_id'] = $productId > 0 ? $productId : 0;
    }
    if (array_key_exists('category_id', $fields)) {
        $categoryId = (int) $fields['category_id'];
        $fields['category_id'] = $categoryId > 0 ? $categoryId : 0;
    }
    foreach (['max_clicks', 'max_impressions'] as $limitKey) {
        if (array_key_exists($limitKey, $fields)) {
            $fields[$limitKey] = max(0, (int) $fields[$limitKey]);
        }
    }

    return bannerpro_sanitize_ad_fields($fields, $modx);
}

/**
 * @param array<string, mixed> $fields
 * @return array<string, mixed>
 */
function bannerpro_sanitize_ad_fields(array $fields, ?modX $modx = null): array
{
    if (array_key_exists('show_hours', $fields)) {
        $fields['show_hours'] = bannerpro_normalize_show_hours($fields['show_hours'], $modx);
    }

    if (array_key_exists('tags', $fields)) {
        $fields['tags'] = bannerpro_normalize_tags($fields['tags'], $modx);
    }

    $targetResource = array_key_exists('target_resource_id', $fields) ? (int) $fields['target_resource_id'] : null;
    $targetParent = array_key_exists('target_parent_id', $fields) ? (int) $fields['target_parent_id'] : null;
    if ($targetResource !== null || $targetParent !== null) {
        $targetResource = max(0, (int) ($targetResource ?? ($fields['target_resource_id'] ?? 0)));
        $targetParent = max(0, (int) ($targetParent ?? ($fields['target_parent_id'] ?? 0)));
        if ($targetResource > 0 && $targetParent > 0) {
            $targetParent = 0;
            if ($modx instanceof modX) {
                $modx->log(modX::LOG_LEVEL_WARN, '[bannerpro] target_resource_id and target_parent_id are mutually exclusive; cleared target_parent_id');
            }
        }
        if ($modx instanceof modX) {
            if ($targetResource > 0 && !$modx->getObject('modResource', $targetResource)) {
                $targetResource = 0;
                $modx->log(modX::LOG_LEVEL_WARN, '[bannerpro] Invalid target_resource_id, reset to 0');
            }
            if ($targetParent > 0 && !$modx->getObject('modResource', $targetParent)) {
                $targetParent = 0;
                $modx->log(modX::LOG_LEVEL_WARN, '[bannerpro] Invalid target_parent_id, reset to 0');
            }
        }
        $fields['target_resource_id'] = $targetResource;
        $fields['target_parent_id'] = $targetParent;
    }

    $productId = max(0, (int) ($fields['product_id'] ?? 0));
    $categoryId = max(0, (int) ($fields['category_id'] ?? 0));
    $fields['product_id'] = $productId;
    $fields['category_id'] = $categoryId;
    if ($productId > 0 && $categoryId > 0) {
        return $fields;
    }
    if ($productId > 0) {
        $fields['category_id'] = 0;
    } elseif ($categoryId > 0) {
        $fields['product_id'] = 0;
    }

    return $fields;
}

/**
 * @param array<string, mixed> $fields
 */
function bannerpro_validate_ad_targeting(array $fields): ?string
{
    if ((int) ($fields['product_id'] ?? 0) > 0 && (int) ($fields['category_id'] ?? 0) > 0) {
        return 'bannerpro.ads.error.product_category_exclusive';
    }

    return null;
}

/**
 * @return array{data: list<array<string, mixed>>, total: int}
 */
function bannerpro_ads_get_list(
    modX $modx,
    int $start,
    int $limit,
    string $sort,
    string $dir,
    string $query,
    int $position,
    string $mode,
    ?int $activeFilter = null,
    ?string $typeFilter = null,
    ?int $productIdFilter = null,
    string $tagFilter = '',
    bool $onSchedule = false,
    ?int $categoryIdFilter = null
): array {
    $criteria = $modx->newQuery('byAd');

    if ($activeFilter !== null) {
        $criteria->where(['active' => $activeFilter ? 1 : 0]);
    }

    if ($typeFilter !== null && $typeFilter !== '') {
        $criteria->where(['type' => bannerpro_normalize_ad_type($typeFilter)]);
    }

    if ($productIdFilter !== null) {
        $criteria->where(['product_id' => max(0, $productIdFilter)]);
    }

    if ($categoryIdFilter !== null) {
        $criteria->where(['category_id' => max(0, $categoryIdFilter)]);
    }

    if ($tagFilter !== '') {
        bannerpro_apply_admin_tag_filter($criteria, 'byAd', $tagFilter);
    }

    if ($onSchedule) {
        $scheduleWhere = [];
        bannerpro_apply_daypart_filter($scheduleWhere, 'byAd', bannerpro_site_now($modx));
        foreach ($scheduleWhere as $fragment) {
            $criteria->where($fragment);
        }
    }

    if ($position > 0) {
        $positionQuery = $modx->newQuery('byAdPosition');
        $positionQuery->select('ad');
        $positionQuery->where(['position' => $position]);
        $ads = [];
        if ($positionQuery->prepare() && $positionQuery->stmt->execute()) {
            $ads = array_unique($positionQuery->stmt->fetchAll(PDO::FETCH_COLUMN));
        }
        if (empty($ads)) {
            if ($mode !== 'exclude') {
                return ['data' => [], 'total' => 0];
            }
        } elseif ($mode === 'exclude') {
            $criteria->where(['id:NOT IN' => $ads]);
        } else {
            $criteria->where(['id:IN' => $ads]);
        }
    }

    if ($query !== '') {
        $criteria->where([
            'name:LIKE' => '%' . $query . '%',
            'OR:description:LIKE' => '%' . $query . '%',
        ]);
    }

    $total = (int) $modx->getCount('byAd', $criteria);
    bannerpro_apply_list_query($criteria, $sort, $dir, ['id', 'name', 'active', 'start', 'end'], 'id', $start, $limit);

    $clickCounts = bannerpro_fetch_click_counts($modx, 'ad');
    $conversionCounts = bannerpro_fetch_conversion_counts($modx, 'ad');
    $rows = [];
    foreach ($modx->getCollection('byAd', $criteria) as $ad) {
        $row = $ad->toArray();
        $row['clicks'] = $clickCounts[(int) $row['id']] ?? 0;
        $row['conversions'] = $conversionCounts[(int) $row['id']] ?? 0;
        $row['current_image'] = bannerpro_resolve_image_url($modx, $row);
        $row['url'] = bannerpro_format_ad_url_display($modx, $row);
        $categoryId = (int) ($row['category_id'] ?? 0);
        $row['category_title'] = $categoryId > 0 ? bannerpro_product_pagetitle($modx, $categoryId) : '';
        $rows[] = $row;
    }

    return ['data' => $rows, 'total' => $total];
}

/**
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_ads_get(modX $modx, int $id): array
{
    /** @var byAd|null $ad */
    $ad = $modx->getObject('byAd', $id);
    if (!$ad) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.ads.error.nf') ?: 'Ad not found'];
    }

    $positionIds = [];
    foreach ($ad->getMany('Positions') as $adPosition) {
        $positionIds[] = (int) $adPosition->get('position');
    }

    $row = $ad->toArray();
    $row['positions'] = $positionIds;
    $row['current_image'] = bannerpro_resolve_image_url($modx, $row);
    $productId = (int) ($row['product_id'] ?? 0);
    $row['product_title'] = bannerpro_product_pagetitle($modx, $productId);
    $categoryId = (int) ($row['category_id'] ?? 0);
    $row['category_title'] = $categoryId > 0 ? bannerpro_product_pagetitle($modx, $categoryId) : '';
    $targetResourceId = (int) ($row['target_resource_id'] ?? 0);
    $targetParentId = (int) ($row['target_parent_id'] ?? 0);
    $row['target_resource_title'] = $targetResourceId > 0 ? bannerpro_resource_pagetitle($modx, $targetResourceId) : '';
    $row['target_parent_title'] = $targetParentId > 0 ? bannerpro_resource_pagetitle($modx, $targetParentId) : '';
    if (!empty($row['tags']) && is_string($row['tags'])) {
        $decodedTags = json_decode($row['tags'], true);
        $row['tags_list'] = is_array($decodedTags) ? $decodedTags : [];
    } else {
        $row['tags_list'] = [];
    }
    if (!empty($row['show_hours']) && is_string($row['show_hours'])) {
        $decodedHours = json_decode($row['show_hours'], true);
        $row['show_hours_data'] = is_array($decodedHours) ? $decodedHours : null;
    } else {
        $row['show_hours_data'] = null;
    }

    return ['success' => true, 'data' => $row];
}

/**
 * @param array<string, mixed> $fields
 * @param list<int>|null $positions
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_ads_create(modX $modx, array $fields, ?array $positions, bool $skipAudit = false): array
{
    $targetingError = bannerpro_validate_ad_targeting($fields);
    if ($targetingError !== null) {
        return ['success' => false, 'message' => $modx->lexicon($targetingError) ?: 'Invalid product/category targeting'];
    }

    $ad = $modx->newObject('byAd');
    bannerpro_normalize_ad_dates($fields);
    $ad->fromArray($fields);
    if (!$ad->save()) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.ads.error.save') ?: 'Save failed'];
    }

    bannerpro_sync_ad_positions($modx, (int) $ad->get('id'), $positions ?? []);
    bannerpro_cache_invalidate($modx);
    $adId = (int) $ad->get('id');
    if (!$skipAudit) {
        bannerpro_audit_log($modx, 'create', 'ad', $adId, ['name' => (string) $ad->get('name')]);
    }
    return ['success' => true, 'data' => $ad->toArray()];
}

/**
 * @param array<string, mixed> $fields
 * @param list<int>|null $positions
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_ads_update(modX $modx, int $id, array $fields, ?array $positions): array
{
    /** @var byAd|null $ad */
    $ad = $modx->getObject('byAd', $id);
    if (!$ad) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.ads.error.nf') ?: 'Ad not found'];
    }

    $targetingError = bannerpro_validate_ad_targeting($fields);
    if ($targetingError !== null) {
        return ['success' => false, 'message' => $modx->lexicon($targetingError) ?: 'Invalid product/category targeting'];
    }

    bannerpro_normalize_ad_dates($fields);
    $ad->fromArray($fields);
    if (!$ad->save()) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.ads.error.save') ?: 'Save failed'];
    }

    if ($positions !== null) {
        bannerpro_sync_ad_positions($modx, $id, $positions);
    } else {
        $modx->removeCollection('byAdPosition', ['ad' => $id]);
    }

    bannerpro_cache_invalidate($modx);
    bannerpro_audit_log($modx, 'update', 'ad', $id, ['name' => (string) $ad->get('name')]);
    return ['success' => true, 'data' => $ad->toArray()];
}

/**
 * @param array<string, mixed> $fields
 */
function bannerpro_normalize_ad_dates(array &$fields): void
{
    if (empty($fields['start'])) {
        $fields['start'] = null;
    }
    if (empty($fields['end'])) {
        $fields['end'] = null;
    }
}

/**
 * @param list<int> $positions
 */
function bannerpro_sync_ad_positions(modX $modx, int $adId, array $positions): void
{
    if (empty($positions)) {
        $modx->removeCollection('byAdPosition', ['ad' => $adId]);
        return;
    }

    $removeQuery = $modx->newQuery('byAdPosition', ['position:NOT IN' => $positions, 'ad' => $adId]);
    foreach ($modx->getCollection('byAdPosition', $removeQuery) as $adPosition) {
        $positionId = (int) $adPosition->get('position');
        $adPosition->remove();
        bannerpro_refresh_idx($modx, $positionId);
    }

    foreach ($positions as $positionId) {
        $positionId = (int) $positionId;
        $data = ['ad' => $adId, 'position' => $positionId];
        $adPosition = $modx->getObject('byAdPosition', $data);
        if (!$adPosition) {
            $adPosition = $modx->newObject('byAdPosition');
            $data['idx'] = (int) $modx->getCount('byAdPosition', ['position' => $positionId]);
        }
        $adPosition->fromArray($data);
        $adPosition->save();
    }
}

/**
 * @return array{success: bool, message?: string}
 */
function bannerpro_ads_remove(modX $modx, int $id, bool $skipAudit = false): array
{
    /** @var byAd|null $ad */
    $ad = $modx->getObject('byAd', $id);
    if (!$ad) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.ads.error.nf') ?: 'Ad not found'];
    }

    $adName = (string) $ad->get('name');
    $modx->removeCollection('byAdPosition', ['ad' => $id]);
    $modx->removeCollection('byClick', ['ad' => $id]);
    $modx->removeCollection('byImpression', ['ad' => $id]);
    if (!$ad->remove()) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.ads.error.remove') ?: 'Remove failed'];
    }
    bannerpro_cache_invalidate($modx);
    if (!$skipAudit) {
        bannerpro_audit_log($modx, 'delete', 'ad', $id, ['name' => $adName]);
    }
    return ['success' => true];
}

/**
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_ads_set_active(modX $modx, int $id, bool $active, bool $skipAudit = false): array
{
    /** @var byAd|null $ad */
    $ad = $modx->getObject('byAd', $id);
    if (!$ad) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.ads.error.nf') ?: 'Ad not found'];
    }
    $ad->set('active', $active ? 1 : 0);
    if (!$ad->save()) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.ads.error.save') ?: 'Save failed'];
    }
    bannerpro_cache_invalidate($modx);
    if (!$skipAudit) {
        bannerpro_audit_log($modx, $active ? 'enable' : 'disable', 'ad', $id, ['name' => (string) $ad->get('name')]);
    }
    return ['success' => true, 'data' => $ad->toArray()];
}

/**
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_ads_duplicate(modX $modx, int $id): array
{
    /** @var byAd|null $ad */
    $ad = $modx->getObject('byAd', $id);
    if (!$ad) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.ads.error.nf') ?: 'Ad not found'];
    }

    $fields = $ad->toArray();
    unset($fields['id']);
    $suffix = $modx->lexicon('bannerpro.ads.duplicate_suffix') ?: ' (copy)';
    $fields['name'] = trim((string) ($fields['name'] ?? '')) . $suffix;

    $newAd = $modx->newObject('byAd');
    bannerpro_normalize_ad_dates($fields);
    $newAd->fromArray($fields);
    if (!$newAd->save()) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.ads.error.save') ?: 'Save failed'];
    }

    $newId = (int) $newAd->get('id');
    foreach ($modx->getCollection('byAdPosition', ['ad' => $id]) as $adPosition) {
        $copy = $modx->newObject('byAdPosition');
        $copy->fromArray([
            'ad' => $newId,
            'position' => (int) $adPosition->get('position'),
            'idx' => (int) $adPosition->get('idx'),
            'weight' => (int) $adPosition->get('weight'),
        ]);
        $copy->save();
    }

    bannerpro_cache_invalidate($modx);

    return bannerpro_ads_get($modx, $newId);
}
