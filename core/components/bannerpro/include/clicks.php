<?php

defined('MODX_CORE_PATH') || exit;

final class BannerProClickPeriod
{
    public const ALL = 'all';
    public const TODAY = 'today';
    public const LAST_7_DAYS = 'last_7_days';
    public const THIS_WEEK = 'this_week';
    public const LAST_WEEK = 'last_week';
    public const THIS_MONTH = 'this_month';
    public const THIS_YEAR = 'this_year';
    public const LAST_MONTH = 'last_month';
}

/**
 * @return list<string>
 */
function bannerpro_period_canonical_values(): array
{
    return [
        BannerProClickPeriod::ALL,
        BannerProClickPeriod::TODAY,
        BannerProClickPeriod::LAST_7_DAYS,
        BannerProClickPeriod::THIS_WEEK,
        BannerProClickPeriod::LAST_WEEK,
        BannerProClickPeriod::THIS_MONTH,
        BannerProClickPeriod::THIS_YEAR,
        BannerProClickPeriod::LAST_MONTH,
    ];
}

function bannerpro_normalize_period(string $period): string
{
    $aliases = [
        '' => BannerProClickPeriod::ALL,
        'all' => BannerProClickPeriod::ALL,
        'overall' => BannerProClickPeriod::ALL,
        'today' => BannerProClickPeriod::TODAY,
        'last_7_days' => BannerProClickPeriod::LAST_7_DAYS,
        'last7days' => BannerProClickPeriod::LAST_7_DAYS,
        '7days' => BannerProClickPeriod::LAST_7_DAYS,
        'this_week' => BannerProClickPeriod::THIS_WEEK,
        'thisweek' => BannerProClickPeriod::THIS_WEEK,
        'last_week' => BannerProClickPeriod::LAST_WEEK,
        'lastweek' => BannerProClickPeriod::LAST_WEEK,
        'this_month' => BannerProClickPeriod::THIS_MONTH,
        'thismonth' => BannerProClickPeriod::THIS_MONTH,
        'month' => BannerProClickPeriod::THIS_MONTH,
        'last_month' => BannerProClickPeriod::LAST_MONTH,
        'lastmonth' => BannerProClickPeriod::LAST_MONTH,
        'this_year' => BannerProClickPeriod::THIS_YEAR,
        'thisyear' => BannerProClickPeriod::THIS_YEAR,
    ];

    $period = trim(strtolower($period));
    if (isset($aliases[$period])) {
        return $aliases[$period];
    }
    if (in_array($period, bannerpro_period_canonical_values(), true)) {
        return $period;
    }

    return $period;
}

function bannerpro_period_from_request(): string
{
    return bannerpro_normalize_period((string) bannerpro_post('period', BannerProClickPeriod::ALL));
}

function bannerpro_week_monday(?DateTimeImmutable $reference = null): DateTimeImmutable
{
    $today = $reference ?? new DateTimeImmutable('today');
    $dayOfWeek = (int) $today->format('N');

    return $today->modify('-' . ($dayOfWeek - 1) . ' days');
}

/**
 * @return array{start: DateTimeImmutable, end: DateTimeImmutable}|null
 */
function bannerpro_period_date_bounds(string $period): ?array
{
    $period = bannerpro_normalize_period($period);
    $today = new DateTimeImmutable('today');
    $now = new DateTimeImmutable();

    switch ($period) {
        case BannerProClickPeriod::LAST_7_DAYS:
            return [
                'start' => $today->modify('-6 days'),
                'end' => $now,
            ];
        case BannerProClickPeriod::THIS_WEEK:
            $monday = bannerpro_week_monday($today);

            return [
                'start' => $monday,
                'end' => $now,
            ];
        case BannerProClickPeriod::LAST_WEEK:
            $monday = bannerpro_week_monday($today)->modify('-7 days');

            return [
                'start' => $monday,
                'end' => $monday->modify('+6 days')->setTime(23, 59, 59),
            ];
        default:
            return null;
    }
}

/**
 * @param 'clickdate'|'impressiondate' $field
 * @return array<string, mixed>
 */
function bannerpro_date_range_condition(string $field, string $period): array
{
    $bounds = bannerpro_period_date_bounds($period);
    if ($bounds === null) {
        return [];
    }

    return [
        $field . ':>=' => $bounds['start']->format('Y-m-d H:i:s'),
        $field . ':<=' => $bounds['end']->format('Y-m-d H:i:s'),
    ];
}

/**
 * @return array<string, mixed>
 */
function bannerpro_click_period_condition(string $period): array
{
    $period = bannerpro_normalize_period($period);
    if ($period === BannerProClickPeriod::ALL) {
        return [];
    }

    $rangeCondition = bannerpro_date_range_condition('clickdate', $period);
    if ($rangeCondition !== []) {
        return $rangeCondition;
    }

    if ($period === BannerProClickPeriod::LAST_MONTH) {
        $like = (new DateTimeImmutable('first day of last month'))->format('Y-m') . '%';

        return ['clickdate:LIKE' => $like];
    }

    $now = new DateTimeImmutable();
    $map = [
        BannerProClickPeriod::TODAY => $now->format('Y-m-d'),
        BannerProClickPeriod::THIS_MONTH => $now->format('Y-m'),
        BannerProClickPeriod::THIS_YEAR => $now->format('Y'),
    ];
    if (isset($map[$period])) {
        return ['clickdate:LIKE' => $map[$period] . '%'];
    }

    return [];
}

function bannerpro_count_ad_clicks(modX $modx, int $adId, string $period = BannerProClickPeriod::ALL): int
{
    $conditions = array_merge(['ad' => $adId], bannerpro_click_period_condition($period));

    return (int) $modx->getCount('byClick', $conditions);
}

/**
 * @return array<string, mixed>
 */
function bannerpro_click_filter_conditions(string $period, int $positionId = 0): array
{
    $conditions = bannerpro_click_period_condition($period);
    if ($positionId > 0) {
        $conditions['position'] = $positionId;
    }

    return $conditions;
}

/**
 * @return array{data: list<array<string, mixed>>, total: int}
 */
function bannerpro_ads_get_clicks(modX $modx, string $period = BannerProClickPeriod::ALL, int $positionId = 0): array
{
    $clickConditions = bannerpro_click_filter_conditions($period, $positionId);
    $impressionConditions = array_merge(
        bannerpro_impression_period_condition($period),
        $positionId > 0 ? ['position' => $positionId] : []
    );
    $counts = bannerpro_fetch_click_counts($modx, 'ad', $clickConditions);
    $impressions = bannerpro_fetch_impression_counts($modx, 'ad', $impressionConditions);
    $rows = [];
    foreach ($modx->getCollection('byAd') as $ad) {
        $row = $ad->toArray();
        $id = (int) $row['id'];
        $row['clicks'] = $counts[$id] ?? 0;
        $row['impressions'] = $impressions[$id] ?? 0;
        $row['ctr'] = bannerpro_stats_ctr($row['clicks'], $row['impressions']);
        $rows[] = $row;
    }

    return ['data' => $rows, 'total' => count($rows)];
}

/**
 * @return array{data: list<array<string, mixed>>, total: int}
 */
function bannerpro_clicks_get_referrers(modX $modx, string $period, int $start, int $limit, int $positionId = 0): array
{
    $conditions = bannerpro_click_filter_conditions($period, $positionId);

    $countQuery = $modx->newQuery('byClick');
    $countQuery->select('COUNT(DISTINCT referrer)');
    if ($conditions !== []) {
        $countQuery->andCondition($conditions);
    }
    $total = 0;
    if ($countQuery->prepare() && $countQuery->stmt->execute()) {
        $total = (int) $countQuery->stmt->fetchColumn();
    }

    $query = $modx->newQuery('byClick');
    $query->select('COUNT(id) AS clicks, referrer');
    if ($conditions !== []) {
        $query->andCondition($conditions);
    }
    $query->groupby('referrer');
    $query->sortby('clicks', 'DESC');
    if ($limit > 0) {
        $query->limit($limit, $start);
    }

    $rows = [];
    if ($query->prepare() && $query->stmt->execute()) {
        $rows = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return ['data' => $rows, 'total' => $total];
}
