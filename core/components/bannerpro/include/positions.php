<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @param array<string, mixed> $input
 * @return array<string, mixed>
 */
function bannerpro_extract_position_fields(array $input): array
{
    $fields = [];
    if (array_key_exists('name', $input)) {
        $fields['name'] = trim((string) $input['name']);
    }
    if (array_key_exists('context_key', $input)) {
        $fields['context_key'] = mb_substr(trim((string) $input['context_key']), 0, 50);
    }

    return $fields;
}

function bannerpro_refresh_idx(modX $modx, int $position): void
{
    $query = $modx->newQuery('byAdPosition');
    $query->where(['position' => $position]);
    $query->sortby('idx', 'ASC');

    $items = $modx->getCollection('byAdPosition', $query);
    $index = 0;
    foreach ($items as $item) {
        $item->set('idx', $index++);
        $item->save();
    }
}

/**
 * @return array{data: list<array<string, mixed>>, total: int}
 */
function bannerpro_positions_get_list(modX $modx, int $start, int $limit, string $sort, string $dir): array
{
    $query = $modx->newQuery('byPosition');
    $total = (int) $modx->getCount('byPosition', $query);
    bannerpro_apply_list_query($query, $sort, $dir, ['id', 'name'], 'id', $start, $limit);

    $clickCounts = bannerpro_fetch_click_counts($modx, 'position');
    $rows = [];
    foreach ($modx->getCollection('byPosition', $query) as $position) {
        $row = $position->toArray();
        $row['clicks'] = $clickCounts[(int) $row['id']] ?? 0;
        $rows[] = $row;
    }

    return ['data' => $rows, 'total' => $total];
}

/**
 * @param array<string, mixed> $fields
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_positions_create(modX $modx, array $fields): array
{
    $position = $modx->newObject('byPosition');
    $position->fromArray($fields);
    if (!$position->save()) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.positions.error.save') ?: 'Save failed'];
    }
    bannerpro_cache_invalidate($modx);
    return ['success' => true, 'data' => $position->toArray()];
}

/**
 * @param array<string, mixed> $fields
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_positions_update(modX $modx, int $id, array $fields): array
{
    /** @var byPosition|null $position */
    $position = $modx->getObject('byPosition', $id);
    if (!$position) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.positions.error.nf') ?: 'Position not found'];
    }
    $position->fromArray($fields);
    if (!$position->save()) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.positions.error.save') ?: 'Save failed'];
    }
    bannerpro_cache_invalidate($modx);
    return ['success' => true, 'data' => $position->toArray()];
}

/**
 * @return array{success: bool, message?: string}
 */
function bannerpro_positions_remove(modX $modx, int $id): array
{
    /** @var byPosition|null $position */
    $position = $modx->getObject('byPosition', $id);
    if (!$position) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.positions.error.nf') ?: 'Position not found'];
    }
    $modx->removeCollection('byAdPosition', ['position' => $id]);
    if (!$position->remove()) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.positions.error.remove') ?: 'Remove failed'];
    }
    bannerpro_cache_invalidate($modx);
    return ['success' => true];
}

/**
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_positions_duplicate(modX $modx, int $id): array
{
    /** @var byPosition|null $position */
    $position = $modx->getObject('byPosition', $id);
    if (!$position) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.positions.error.nf') ?: 'Position not found'];
    }

    $fields = $position->toArray();
    unset($fields['id']);
    $suffix = $modx->lexicon('bannerpro.positions.duplicate_suffix') ?: ' (copy)';
    $fields['name'] = trim((string) ($fields['name'] ?? '')) . $suffix;

    $newPosition = $modx->newObject('byPosition');
    $newPosition->fromArray($fields);
    if (!$newPosition->save()) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.positions.error.save') ?: 'Save failed'];
    }

    $newId = (int) $newPosition->get('id');
    foreach ($modx->getCollection('byAdPosition', ['position' => $id]) as $adPosition) {
        $copy = $modx->newObject('byAdPosition');
        $copy->fromArray([
            'ad' => (int) $adPosition->get('ad'),
            'position' => $newId,
            'idx' => (int) $adPosition->get('idx'),
            'weight' => (int) $adPosition->get('weight'),
        ]);
        $copy->save();
    }

    bannerpro_cache_invalidate($modx);

    return ['success' => true, 'data' => $newPosition->toArray()];
}

/**
 * @return array{data: list<array<string, mixed>>, total: int}
 */
function bannerpro_adpositions_get_list(modX $modx, int $position, int $start, int $limit): array
{
    $query = $modx->newQuery('byAdPosition');
    $query->where(['position' => $position]);
    $total = (int) $modx->getCount('byAdPosition', $query);

    $query->sortby('idx', 'ASC');
    if ($limit > 0) {
        $query->limit($limit, $start);
    }

    $rows = [];
    foreach ($modx->getCollection('byAdPosition', $query) as $adPosition) {
        $row = $adPosition->toArray();
        /** @var byAd|null $ad */
        $ad = $modx->getObject('byAd', (int) $row['ad']);
        if ($ad) {
            $row['name'] = $ad->get('name');
            $row['active'] = $ad->get('active');
            $row['image'] = bannerpro_resolve_image_url($modx, $ad->toArray());
        }
        $rows[] = $row;
    }

    return ['data' => $rows, 'total' => $total];
}

/**
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_adpositions_add(modX $modx, int $position, int $adId): array
{
    if ($modx->getCount('byAdPosition', ['position' => $position, 'ad' => $adId])) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.adposition.error.ae') ?: 'Already assigned'];
    }

    $idx = (int) $modx->getCount('byAdPosition', ['position' => $position]);
    $adPosition = $modx->newObject('byAdPosition');
    $adPosition->fromArray([
        'ad' => $adId,
        'position' => $position,
        'idx' => $idx,
    ]);
    if (!$adPosition->save()) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.adposition.error.save') ?: 'Save failed'];
    }
    bannerpro_cache_invalidate($modx);
    return ['success' => true, 'data' => $adPosition->toArray()];
}

/**
 * @return array{success: bool, message?: string}
 */
function bannerpro_adpositions_remove(modX $modx, int $id): array
{
    /** @var byAdPosition|null $adPosition */
    $adPosition = $modx->getObject('byAdPosition', $id);
    if (!$adPosition) {
        return ['success' => false, 'message' => 'Not found'];
    }
    $position = (int) $adPosition->get('position');
    if (!$adPosition->remove()) {
        return ['success' => false, 'message' => 'Remove failed'];
    }
    bannerpro_refresh_idx($modx, $position);
    bannerpro_cache_invalidate($modx);
    return ['success' => true];
}

/**
 * @return array{success: bool, message?: string}
 */
function bannerpro_adpositions_sort(modX $modx, int $id, int $newOrder, int $oldOrder): array
{
    /** @var byAdPosition|null $moved */
    $moved = $modx->getObject('byAdPosition', $id);
    if (!$moved) {
        return ['success' => false, 'message' => 'Not found'];
    }

    $positionId = (int) $moved->get('position');
    $query = $modx->newQuery('byAdPosition');
    $query->where(['position' => $positionId]);
    $query->sortby('idx', 'ASC');
    $query->sortby('id', 'ASC');

    $items = array_values($modx->getCollection('byAdPosition', $query));
    $currentIndex = null;
    foreach ($items as $index => $item) {
        if ((int) $item->get('id') === $id) {
            $currentIndex = $index;
            break;
        }
    }
    if ($currentIndex === null) {
        return ['success' => false, 'message' => 'Not found'];
    }

    if ($oldOrder >= 0 && $oldOrder < count($items) && (int) $items[$oldOrder]->get('id') === $id) {
        $currentIndex = $oldOrder;
    }

    array_splice($items, $currentIndex, 1);
    $newOrder = max(0, min($newOrder, count($items)));
    array_splice($items, $newOrder, 0, [$moved]);

    foreach ($items as $index => $item) {
        if ((int) $item->get('idx') !== $index) {
            $item->set('idx', $index);
            $item->save();
        }
    }

    bannerpro_cache_invalidate($modx);
    return ['success' => true];
}

/**
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_adpositions_update_weight(modX $modx, int $id, int $weight): array
{
    /** @var byAdPosition|null $adPosition */
    $adPosition = $modx->getObject('byAdPosition', $id);
    if (!$adPosition) {
        return ['success' => false, 'message' => 'Not found'];
    }
    $adPosition->set('weight', max(1, $weight));
    if (!$adPosition->save()) {
        return ['success' => false, 'message' => 'Save failed'];
    }
    bannerpro_cache_invalidate($modx);

    return ['success' => true, 'data' => $adPosition->toArray()];
}
