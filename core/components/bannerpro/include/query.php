<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @param array<string, mixed> $scriptProperties
 */
function bannerpro_resolve_snippet_context(modX $modx, array $scriptProperties): string
{
    $contextParam = trim((string) ($scriptProperties['context'] ?? ''));
    if ($contextParam === '' || $contextParam === '*') {
        return (string) $modx->context->get('key');
    }

    return mb_substr($contextParam, 0, 50);
}

/**
 * xPDO/pdoFetch: при смешении raw SQL (integer keys) и полей (string keys) в одном where()
 * строковые условия игнорируются. Склеиваем все raw-фрагменты в один блок с ключом 0.
 *
 * @param array<int|string, mixed> $where
 * @return array<int|string, mixed>
 */
function bannerpro_compact_where(array $where): array
{
    $raw = [];
    $assoc = [];
    foreach ($where as $key => $value) {
        if (is_int($key)) {
            if (is_string($value) && $value !== '') {
                $raw[] = $value;
            }
        } else {
            $assoc[$key] = $value;
        }
    }
    if ($raw === []) {
        return $assoc;
    }

    return [0 => implode(' AND ', $raw)] + $assoc;
}

/**
 * @param array<string, mixed> $where
 * @param array<string, mixed> $scriptProperties
 */
function bannerpro_apply_position_context_filter(modX $modx, array &$where, array $scriptProperties): void
{
    $activeContext = bannerpro_resolve_snippet_context($modx, $scriptProperties);
    $quotedContext = $modx->quote($activeContext);
    $where[] = "(byPosition.context_key = '' OR byPosition.context_key = {$quotedContext})";
}

/**
 * @param list<string> $allowedSortFields
 */
function bannerpro_apply_list_query(
    xPDOQuery $query,
    string $sort,
    string $dir,
    array $allowedSortFields,
    string $defaultSort = 'id',
    int $start = 0,
    int $limit = 0
): void {
    $sortField = in_array($sort, $allowedSortFields, true) ? $sort : $defaultSort;
    $sortDir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
    $query->sortby($sortField, $sortDir);
    if ($limit > 0) {
        $query->limit($limit, $start);
    }
}

/**
 * @param 'ad'|'position' $groupField
 * @param array<string, mixed> $conditions
 * @return array<int, int>
 */
function bannerpro_fetch_click_counts(modX $modx, string $groupField, array $conditions = []): array
{
    if (!in_array($groupField, ['ad', 'position'], true)) {
        return [];
    }

    $query = $modx->newQuery('byClick');
    $query->select($groupField . ' AS group_key, COUNT(id) AS clicks');
    if ($conditions !== []) {
        $query->where($conditions);
    }
    $query->groupby($groupField);

    $counts = [];
    if ($query->prepare() && $query->stmt->execute()) {
        while ($row = $query->stmt->fetch(PDO::FETCH_ASSOC)) {
            $counts[(int) $row['group_key']] = (int) $row['clicks'];
        }
    }

    return $counts;
}

/**
 * @param 'ad'|'position' $groupField
 * @param array<string, mixed> $conditions
 * @return array<int, int>
 */
function bannerpro_fetch_conversion_counts(modX $modx, string $groupField, array $conditions = []): array
{
    if (!in_array($groupField, ['ad', 'position'], true)) {
        return [];
    }

    $query = $modx->newQuery('byClick');
    $query->select($groupField . ' AS group_key, COUNT(id) AS conversions');
    $query->where(['order_id:>' => 0]);
    if ($conditions !== []) {
        $query->where($conditions);
    }
    $query->groupby($groupField);

    $counts = [];
    if ($query->prepare() && $query->stmt->execute()) {
        while ($row = $query->stmt->fetch(PDO::FETCH_ASSOC)) {
            $counts[(int) $row['group_key']] = (int) $row['conversions'];
        }
    }

    return $counts;
}

/**
 * @param 'ad'|'position' $groupField
 * @param array<string, mixed> $conditions
 * @return array<int, int>
 */
function bannerpro_fetch_impression_counts(modX $modx, string $groupField, array $conditions = []): array
{
    if (!in_array($groupField, ['ad', 'position'], true)) {
        return [];
    }

    $query = $modx->newQuery('byImpression');
    $query->select($groupField . ' AS group_key, COUNT(id) AS impressions');
    if ($conditions !== []) {
        $query->where($conditions);
    }
    $query->groupby($groupField);

    $counts = [];
    if ($query->prepare() && $query->stmt->execute()) {
        while ($row = $query->stmt->fetch(PDO::FETCH_ASSOC)) {
            $counts[(int) $row['group_key']] = (int) $row['impressions'];
        }
    }

    return $counts;
}

/**
 * Daily click, impression, or conversion totals keyed by Y-m-d.
 *
 * @param 'click'|'impression'|'conversion' $type
 * @return array<string, int>
 */
function bannerpro_fetch_counts_by_day(modX $modx, string $type, string $period, int $positionId = 0): array
{
    $prefix = $modx->getOption('table_prefix', null, 'modx_');
    $extraWhere = '';
    if ($type === 'click') {
        $table = $prefix . 'bannerpro_clicks';
        $dateField = 'clickdate';
    } elseif ($type === 'impression') {
        $table = $prefix . 'bannerpro_impressions';
        $dateField = 'impressiondate';
    } elseif ($type === 'conversion') {
        $table = $prefix . 'bannerpro_clicks';
        $dateField = 'clickdate';
        $extraWhere = ' AND order_id > 0';
    } else {
        return [];
    }

    $where = bannerpro_stats_sql_period_where($dateField, $period);
    if ($positionId > 0) {
        $where .= ' AND position = ' . (int) $positionId;
    }

    $sql = "
        SELECT DATE({$dateField}) AS day, COUNT(id) AS total
        FROM {$table}
        WHERE {$dateField} IS NOT NULL{$where}{$extraWhere}
        GROUP BY DATE({$dateField})
        ORDER BY day ASC
    ";

    $counts = [];
    if ($stmt = $modx->prepare($sql)) {
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $day = (string) ($row['day'] ?? '');
                if ($day === '') {
                    continue;
                }
                $counts[$day] = (int) ($row['total'] ?? 0);
            }
        }
    }

    return $counts;
}

function bannerpro_site_now(modX $modx): DateTimeImmutable
{
    $tzName = trim((string) $modx->getOption('date_timezone', null, ''));
    if ($tzName === '') {
        $tzName = date_default_timezone_get();
    }
    try {
        $tz = new DateTimeZone($tzName);
    } catch (Exception $e) {
        $tz = new DateTimeZone('UTC');
    }

    return new DateTimeImmutable('now', $tz);
}

function bannerpro_daypart_sql_condition(string $class, ?DateTimeImmutable $now = null): string
{
    if ($now === null) {
        $now = new DateTimeImmutable();
    }
    $dow = (int) $now->format('N');
    $hour = (int) $now->format('G');

    return "({$class}.show_hours IS NULL OR {$class}.show_hours = ''"
        . " OR ("
        . "(JSON_LENGTH(JSON_EXTRACT({$class}.show_hours, '$.weekdays')) IS NULL"
        . " OR JSON_LENGTH(JSON_EXTRACT({$class}.show_hours, '$.weekdays')) = 0"
        . " OR JSON_CONTAINS(JSON_EXTRACT({$class}.show_hours, '$.weekdays'), '{$dow}', '\$'))"
        . " AND"
        . " (JSON_LENGTH(JSON_EXTRACT({$class}.show_hours, '$.hours')) IS NULL"
        . " OR JSON_LENGTH(JSON_EXTRACT({$class}.show_hours, '$.hours')) = 0"
        . " OR JSON_CONTAINS(JSON_EXTRACT({$class}.show_hours, '$.hours'), '{$hour}', '\$'))"
        . "))";
}

/**
 * @param array<int|string, mixed> $where
 */
function bannerpro_apply_daypart_filter(array &$where, string $class, ?DateTimeImmutable $now = null): void
{
    $where[] = bannerpro_daypart_sql_condition($class, $now);
}

/**
 * @return list<int>
 */
function bannerpro_resource_ancestor_ids(modX $modx, int $resourceId): array
{
    $ids = [];
    if ($resourceId <= 0) {
        return $ids;
    }

    $current = $resourceId;
    for ($guard = 0; $current > 0 && $guard < 100; $guard++) {
        /** @var modResource|null $resource */
        $resource = $modx->getObject('modResource', $current);
        if (!$resource) {
            break;
        }
        $parent = (int) $resource->get('parent');
        if ($parent > 0) {
            $ids[] = $parent;
        }
        $current = $parent;
    }

    return array_values(array_unique($ids));
}

/**
 * @param array<int|string, mixed> $where
 */
function bannerpro_apply_resource_target_filter(
    modX $modx,
    array &$where,
    string $class,
    int $resourceId,
    int $parentId = 0
): void {
    if ($resourceId > 0) {
        $ancestorIds = bannerpro_resource_ancestor_ids($modx, $resourceId);
        if ($parentId > 0 && !in_array($parentId, $ancestorIds, true)) {
            $ancestorIds[] = $parentId;
        }
        if ($ancestorIds === []) {
            $where[] = "(({$class}.target_resource_id = 0 AND {$class}.target_parent_id = 0)"
                . " OR {$class}.target_resource_id = {$resourceId})";
        } else {
            $inList = implode(',', array_map('intval', $ancestorIds));
            $where[] = "(({$class}.target_resource_id = 0 AND {$class}.target_parent_id = 0)"
                . " OR {$class}.target_resource_id = {$resourceId}"
                . " OR {$class}.target_parent_id IN ({$inList}))";
        }

        return;
    }

    if ($parentId > 0) {
        $where[] = "(({$class}.target_resource_id = 0 AND {$class}.target_parent_id = 0)"
            . " OR {$class}.target_parent_id = {$parentId})";

        return;
    }

    $where[] = "({$class}.target_resource_id = 0 AND {$class}.target_parent_id = 0)";
}

/**
 * @param array<int, string>|string|null $tags
 */
function bannerpro_normalize_tags($tags, ?modX $modx = null): ?string
{
    if ($tags === null || $tags === '' || $tags === []) {
        return null;
    }

    $list = [];
    if (is_string($tags)) {
        $decoded = json_decode($tags, true);
        if (is_array($decoded)) {
            $list = $decoded;
        } else {
            $list = preg_split('/\s*,\s*/', trim($tags), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
    } elseif (is_array($tags)) {
        $list = $tags;
    }

    $normalized = [];
    foreach ($list as $tag) {
        $slug = mb_strtolower(trim((string) $tag));
        if ($slug === '' || !preg_match('/^[a-z0-9_-]{1,32}$/', $slug)) {
            if ($modx instanceof modX) {
                $modx->log(modX::LOG_LEVEL_WARN, '[bannerpro] Dropped invalid tag: ' . (string) $tag);
            }
            continue;
        }
        $normalized[$slug] = true;
        if (count($normalized) >= 20) {
            break;
        }
    }

    if ($normalized === []) {
        return null;
    }

    $result = array_keys($normalized);
    sort($result);

    return json_encode($result, JSON_UNESCAPED_UNICODE);
}

/**
 * @param array<int|string, mixed> $where
 * @param list<string> $requestedTags
 */
function bannerpro_apply_tags_filter(array &$where, string $class, array $requestedTags, string $mode = 'any'): void
{
    $slugs = [];
    foreach ($requestedTags as $tag) {
        $tag = trim((string) $tag);
        if ($tag === '') {
            continue;
        }
        $json = bannerpro_normalize_tags([$tag]);
        if ($json === null) {
            continue;
        }
        $decoded = json_decode($json, true);
        if (is_array($decoded) && isset($decoded[0])) {
            $slugs[] = (string) $decoded[0];
        }
    }
    $slugs = array_values(array_unique($slugs));
    if ($slugs === []) {
        return;
    }

    $parts = [];
    foreach ($slugs as $slug) {
        $jsonVal = str_replace("'", "\\'", json_encode($slug, JSON_UNESCAPED_UNICODE));
        $parts[] = "JSON_CONTAINS({$class}.tags, '{$jsonVal}', '\$')";
    }

    $connector = strtolower($mode) === 'all' ? ' AND ' : ' OR ';
    $where[] = '(' . implode($connector, $parts) . ')';
}

/**
 * @return list<string>
 */
function bannerpro_parse_snippet_tags(string $tagsParam): array
{
    if (trim($tagsParam) === '') {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $tagsParam))));
}

/**
 * @param array<string, mixed>|string|null $input
 */
function bannerpro_normalize_show_hours($input, ?modX $modx = null): ?string
{
    if ($input === null || $input === '' || $input === []) {
        return null;
    }

    $data = $input;
    if (is_string($input)) {
        $decoded = json_decode($input, true);
        if (!is_array($decoded)) {
            if ($modx instanceof modX) {
                $modx->log(modX::LOG_LEVEL_WARN, '[bannerpro] Invalid show_hours JSON, treating as unrestricted');
            }

            return null;
        }
        $data = $decoded;
    }

    if (!is_array($data)) {
        return null;
    }

    $weekdays = [];
    if (isset($data['weekdays']) && is_array($data['weekdays'])) {
        foreach ($data['weekdays'] as $day) {
            $day = (int) $day;
            if ($day >= 1 && $day <= 7) {
                $weekdays[$day] = true;
            }
        }
    }

    $hours = [];
    if (isset($data['hours']) && is_array($data['hours'])) {
        foreach ($data['hours'] as $hour) {
            $hour = (int) $hour;
            if ($hour >= 0 && $hour <= 23) {
                $hours[$hour] = true;
            }
        }
    }

    if ($weekdays === [] && $hours === []) {
        return null;
    }

    $weekdayList = array_keys($weekdays);
    $hourList = array_keys($hours);
    sort($weekdayList);
    sort($hourList);

    return json_encode(['weekdays' => $weekdayList, 'hours' => $hourList], JSON_UNESCAPED_UNICODE);
}

/**
 * @return list<string>
 */
function bannerpro_tags_suggest(modX $modx, string $query, int $limit = 20): array
{
    bannerpro_add_package($modx);

    $criteria = $modx->newQuery('byAd');
    $criteria->select('tags');
    $criteria->where("tags IS NOT NULL AND tags != '' AND tags != '[]'");

    $tags = [];
    $queryLower = mb_strtolower(trim($query));
    foreach ($modx->getCollection('byAd', $criteria) as $ad) {
        $decoded = json_decode((string) $ad->get('tags'), true);
        if (!is_array($decoded)) {
            continue;
        }
        foreach ($decoded as $tag) {
            $tag = (string) $tag;
            if ($queryLower !== '' && mb_strpos($tag, $queryLower) === false) {
                continue;
            }
            $tags[$tag] = true;
        }
    }

    $result = array_keys($tags);
    sort($result);

    return array_slice($result, 0, max(1, $limit));
}

/**
 * Apply admin list filter for a single tag slug.
 */
function bannerpro_apply_admin_tag_filter(xPDOQuery $criteria, string $class, string $tag): void
{
    $tag = trim($tag);
    if ($tag === '') {
        return;
    }

    $json = bannerpro_normalize_tags([$tag]);
    if ($json === null) {
        return;
    }

    $decoded = json_decode($json, true);
    $slug = is_array($decoded) && isset($decoded[0]) ? (string) $decoded[0] : '';
    if ($slug === '') {
        return;
    }

    $jsonVal = str_replace("'", "\\'", json_encode($slug, JSON_UNESCAPED_UNICODE));
    $criteria->where("({$class}.tags IS NOT NULL AND {$class}.tags != '' AND JSON_CONTAINS({$class}.tags, '{$jsonVal}', '\$'))");
}
