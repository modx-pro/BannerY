<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array<string, string>
 */
function bannerpro_product_placeholder_defaults(): array
{
    return [
        'product_id' => '',
        'product_title' => '',
        'product_price' => '',
        'product_old_price' => '',
        'product_thumb' => '',
        'product_url' => '',
    ];
}

function bannerpro_get_resource_tv(modResource $resource, string $name): string
{
    $value = $resource->getTVValue($name);

    return is_string($value) ? trim($value) : '';
}

/**
 * @param list<int> $ids
 * @return array<int, modResource>
 */
function bannerpro_load_product_resources(modX $modx, array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if ($ids === []) {
        return [];
    }

    $resources = [];
    $query = $modx->newQuery('modResource');
    $query->where(['id:IN' => $ids]);
    foreach ($modx->getCollection('modResource', $query) as $product) {
        $resources[(int) $product->get('id')] = $product;
    }

    return $resources;
}

/**
 * @param array<int, modResource> $productsById
 */
function bannerpro_product_pagetitle(modX $modx, int $productId, array $productsById = []): string
{
    if ($productId <= 0) {
        return '';
    }
    if (isset($productsById[$productId])) {
        return (string) $productsById[$productId]->get('pagetitle');
    }

    $loaded = bannerpro_load_product_resources($modx, [$productId]);

    return isset($loaded[$productId]) ? (string) $loaded[$productId]->get('pagetitle') : '';
}

/**
 * @param list<array<string, mixed>> $rows
 * @return list<int>
 */
function bannerpro_collect_product_ids(array $rows): array
{
    $ids = [];
    foreach ($rows as $row) {
        $id = (int) ($row['product_id'] ?? 0);
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }

    return array_values($ids);
}

/**
 * Цепочка parent ID от товара вверх (прямой parent первым).
 *
 * @return list<int>
 */
function bannerpro_product_category_chain(modX $modx, int $productId): array
{
    static $cache = [];

    if ($productId <= 0) {
        return [];
    }
    if (isset($cache[$productId])) {
        return $cache[$productId];
    }

    $chain = [];
    $currentId = $productId;
    $seen = [];
    while ($currentId > 0 && !isset($seen[$currentId])) {
        $seen[$currentId] = true;
        /** @var modResource|null $resource */
        $resource = $modx->getObject('modResource', $currentId);
        if (!$resource) {
            break;
        }
        $parentId = (int) $resource->get('parent');
        if ($parentId > 0) {
            $chain[] = $parentId;
        }
        $currentId = $parentId;
    }

    $cache[$productId] = $chain;

    return $chain;
}

/**
 * @param array<string, mixed> $row
 * @param list<int> $categoryChain
 */
function bannerpro_ad_matches_product(array $row, int $productId, array $categoryChain = []): bool
{
    $adProductId = (int) ($row['product_id'] ?? 0);
    $adCategoryId = (int) ($row['category_id'] ?? 0);

    if ($adProductId > 0) {
        return $productId > 0 && $adProductId === $productId;
    }
    if ($adCategoryId > 0) {
        return $productId > 0 && in_array($adCategoryId, $categoryChain, true);
    }

    return true;
}

/**
 * @param array<string, mixed> $row
 * @param array<int, modResource> $productsById
 * @return array<string, mixed>
 */
function bannerpro_enrich_product_placeholders(modX $modx, array $row, int $defaultSource, array $productsById = []): array
{
    $row = array_merge(bannerpro_product_placeholder_defaults(), $row);

    $productId = (int) ($row['product_id'] ?? 0);
    if ($productId <= 0) {
        return $row;
    }

    $row['product_id'] = $productId;
    $product = $productsById[$productId] ?? null;
    if (!$product) {
        return $row;
    }

    $row['product_title'] = (string) $product->get('pagetitle');
    $row['product_url'] = (string) $modx->makeUrl($productId, $product->get('context_key'), '', 'full');
    $row['product_price'] = bannerpro_get_resource_tv($product, 'price');
    $row['product_old_price'] = bannerpro_get_resource_tv($product, 'old_price');

    $thumb = bannerpro_get_resource_tv($product, 'thumb');
    if ($thumb === '') {
        $thumb = bannerpro_get_resource_tv($product, 'image');
    }
    if ($thumb !== '') {
        $row['product_thumb'] = bannerpro_resolve_image_url($modx, [
            'image' => $thumb,
            'source' => $defaultSource,
        ]);
    }

    return $row;
}

/**
 * @param array<string, mixed> $row
 * @param array<int, modResource> $productsById
 * @return array<string, mixed>
 */
function bannerpro_enrich_product_placeholders_for_context(
    modX $modx,
    array $row,
    int $contextProductId,
    int $defaultSource,
    array $productsById = []
): array {
    if ($contextProductId <= 0) {
        return bannerpro_enrich_product_placeholders($modx, $row, $defaultSource, $productsById);
    }

    $enriched = $row;
    $enriched['product_id'] = $contextProductId;

    return bannerpro_enrich_product_placeholders($modx, $enriched, $defaultSource, $productsById);
}

/**
 * @param array<string, mixed> $where
 */
function bannerpro_apply_product_filter(modX $modx, array &$where, string $class, int $productId): void
{
    if ($productId > 0) {
        $parents = bannerpro_product_category_chain($modx, $productId);
        $productClause = "({$class}.product_id IS NULL OR {$class}.product_id = 0 OR {$class}.product_id = {$productId})";
        if ($parents !== []) {
            $parentsList = implode(',', $parents);
            $where[] = "({$productClause} OR ({$class}.product_id = 0 AND {$class}.category_id IN ({$parentsList})))";
        } else {
            $where[] = $productClause;
            $where[] = "({$class}.category_id IS NULL OR {$class}.category_id = 0)";
        }

        return;
    }

    $where[] = "({$class}.product_id IS NULL OR {$class}.product_id = 0)";
    $where[] = "({$class}.category_id IS NULL OR {$class}.category_id = 0)";
}

/**
 * @return list<array<string, mixed>>
 */
function bannerpro_fetch_ads_for_product(modX $modx, int $productId): array
{
    if ($productId <= 0) {
        return [];
    }

    $parents = bannerpro_product_category_chain($modx, $productId);
    $query = $modx->newQuery('byAd');
    $query->where(['active' => 1]);

    $productClause = "(product_id = {$productId} OR product_id = 0 OR product_id IS NULL)";
    if ($parents !== []) {
        $parentsList = implode(',', $parents);
        $query->where("({$productClause} OR (product_id = 0 AND category_id IN ({$parentsList})))");
    } else {
        $query->where($productClause);
        $query->where('(category_id = 0 OR category_id IS NULL)');
    }
    $query->sortby('id', 'ASC');

    $rows = [];
    foreach ($modx->getCollection('byAd', $query) as $ad) {
        $rows[] = $ad->toArray();
    }

    return $rows;
}

/**
 * @param list<int> $productIds
 * @return array<int, list<array<string, mixed>>>
 */
function bannerpro_fetch_ads_for_products(modX $modx, array $productIds): array
{
    $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
    if ($productIds === []) {
        return [];
    }

    $idsList = implode(',', $productIds);
    $query = $modx->newQuery('byAd');
    $query->where(['active' => 1]);
    $query->where("(product_id IN ({$idsList}) OR product_id = 0 OR product_id IS NULL)");

    $chains = [];
    foreach ($productIds as $id) {
        $chains[$id] = bannerpro_product_category_chain($modx, $id);
    }

    $byProduct = array_fill_keys($productIds, []);
    foreach ($modx->getCollection('byAd', $query) as $ad) {
        $row = $ad->toArray();
        foreach ($productIds as $id) {
            if (bannerpro_ad_matches_product($row, $id, $chains[$id])) {
                $byProduct[$id][] = $row;
            }
        }
    }

    return $byProduct;
}

/**
 * @return list<string>
 */
function bannerpro_parse_catalog_position_names(modX $modx): array
{
    $raw = trim((string) $modx->getOption('bannerpro_ms_catalog_positions', null, ''));
    if ($raw === '') {
        return [];
    }

    return array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
}

/**
 * @param list<string> $positionNames
 * @return list<array<string, mixed>>
 */
function bannerpro_query_catalog_ads(modX $modx, array $positionNames): array
{
    $positionNames = array_values(array_unique(array_filter(array_map('trim', $positionNames))));
    if ($positionNames === []) {
        return [];
    }

    $positionQuery = $modx->newQuery('byPosition');
    $positionQuery->where(['name:IN' => $positionNames]);
    $positionIdToName = [];
    foreach ($modx->getCollection('byPosition', $positionQuery) as $position) {
        $positionIdToName[(int) $position->get('id')] = (string) $position->get('name');
    }
    if ($positionIdToName === []) {
        return [];
    }

    $date = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    $linkQuery = $modx->newQuery('byAdPosition');
    $linkQuery->where(['position:IN' => array_keys($positionIdToName)]);
    $linkQuery->innerJoin('byAd', 'byAd', 'byAdPosition.ad = byAd.id');
    $linkQuery->where(['byAd.active' => 1]);
    $linkQuery->where("(byAd.start IS NULL OR byAd.start <= '{$date}') AND (byAd.end IS NULL OR byAd.end >= '{$date}')");

    $limitWhere = [];
    bannerpro_apply_limit_criteria($modx, $limitWhere, 'byAd');
    foreach ($limitWhere as $clause) {
        $linkQuery->where($clause);
    }
    $linkQuery->sortby('byAdPosition.idx', 'ASC');

    $rows = [];
    foreach ($modx->getCollection('byAdPosition', $linkQuery) as $link) {
        /** @var byAd|null $ad */
        $ad = $link->getOne('Ad');
        if (!$ad) {
            continue;
        }
        $row = $ad->toArray();
        $row['adposition'] = (int) $link->get('id');
        $row['weight'] = (int) $link->get('weight');
        $positionId = (int) $link->get('position');
        $row['position_name'] = $positionIdToName[$positionId] ?? '';
        $rows[] = $row;
    }

    return $rows;
}

/**
 * @return array<string, list<array<string, mixed>>>
 */
function bannerpro_fetch_catalog_positions_for_product(modX $modx, int $productId): array
{
    $positionNames = bannerpro_parse_catalog_position_names($modx);
    if ($positionNames === [] || $productId <= 0) {
        return [];
    }

    $allRows = bannerpro_query_catalog_ads($modx, $positionNames);
    if ($allRows === []) {
        return array_fill_keys($positionNames, []);
    }

    $categoryChain = bannerpro_product_category_chain($modx, $productId);
    $defaultSource = (int) $modx->getOption('bannerpro_media_source', null, $modx->getOption('default_media_source'), true);
    $productsById = bannerpro_load_product_resources($modx, [$productId]);

    $result = array_fill_keys($positionNames, []);
    foreach ($allRows as $row) {
        if (!bannerpro_ad_matches_product($row, $productId, $categoryChain)) {
            continue;
        }
        $positionName = (string) ($row['position_name'] ?? '');
        if ($positionName === '' || !isset($result[$positionName])) {
            continue;
        }
        $prepared = bannerpro_prepare_ad_row(
            $modx,
            bannerpro_enrich_product_placeholders_for_context($modx, $row, $productId, $defaultSource, $productsById),
            $defaultSource,
            $productsById
        );
        $result[$positionName][] = $prepared;
    }

    return $result;
}

/**
 * @param list<int> $productIds
 * @return array<int, array<string, list<array<string, mixed>>>>
 */
function bannerpro_fetch_catalog_positions_for_products(modX $modx, array $productIds): array
{
    $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
    $positionNames = bannerpro_parse_catalog_position_names($modx);
    if ($productIds === [] || $positionNames === []) {
        return [];
    }

    $allRows = bannerpro_query_catalog_ads($modx, $positionNames);
    $chains = [];
    foreach ($productIds as $id) {
        $chains[$id] = bannerpro_product_category_chain($modx, $id);
    }

    $defaultSource = (int) $modx->getOption('bannerpro_media_source', null, $modx->getOption('default_media_source'), true);
    $productsById = bannerpro_load_product_resources($modx, $productIds);

    $result = [];
    foreach ($productIds as $productId) {
        $result[$productId] = array_fill_keys($positionNames, []);
    }

    foreach ($allRows as $row) {
        $positionName = (string) ($row['position_name'] ?? '');
        if ($positionName === '' || !in_array($positionName, $positionNames, true)) {
            continue;
        }
        foreach ($productIds as $productId) {
            if (!bannerpro_ad_matches_product($row, $productId, $chains[$productId])) {
                continue;
            }
            $prepared = bannerpro_prepare_ad_row(
                $modx,
                bannerpro_enrich_product_placeholders_for_context($modx, $row, $productId, $defaultSource, $productsById),
                $defaultSource,
                $productsById
            );
            $result[$productId][$positionName][] = $prepared;
        }
    }

    return $result;
}
