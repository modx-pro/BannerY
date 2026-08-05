<?php

defined('MODX_CORE_PATH') || exit;

function bannerpro_stats_ctr(int $clicks, int $impressions): ?float
{
    if ($impressions <= 0) {
        return null;
    }

    return round($clicks / $impressions * 100, 2);
}

function bannerpro_stats_cvr(int $conversions, int $clicks): ?float
{
    if ($clicks <= 0) {
        return null;
    }

    return round($conversions / $clicks * 100, 2);
}

/**
 * @return array<string, mixed>
 */
function bannerpro_stats_base_conditions(string $period, int $positionId): array
{
    $conditions = [];
    if ($positionId > 0) {
        $conditions['position'] = $positionId;
    }

    return $conditions;
}

/**
 * @return array{clicks: int, impressions: int, conversions: int, ctr: float|null, cvr: float|null}
 */
function bannerpro_stats_summary(modX $modx, string $period, int $positionId = 0): array
{
    $clickConditions = array_merge(
        bannerpro_stats_base_conditions($period, $positionId),
        bannerpro_click_period_condition($period)
    );
    $impressionConditions = array_merge(
        bannerpro_stats_base_conditions($period, $positionId),
        bannerpro_impression_period_condition($period)
    );
    $conversionConditions = array_merge($clickConditions, ['order_id:>' => 0]);

    $clicks = (int) $modx->getCount('byClick', $clickConditions);
    $impressions = (int) $modx->getCount('byImpression', $impressionConditions);
    $conversions = (int) $modx->getCount('byClick', $conversionConditions);

    return [
        'clicks' => $clicks,
        'impressions' => $impressions,
        'conversions' => $conversions,
        'ctr' => bannerpro_stats_ctr($clicks, $impressions),
        'cvr' => bannerpro_stats_cvr($conversions, $clicks),
    ];
}

/**
 * @return list<array{date: string, clicks: int, impressions: int, conversions: int, ctr: float|null, cvr: float|null}>
 */
function bannerpro_stats_by_day(modX $modx, string $period, int $positionId = 0): array
{
    $period = bannerpro_normalize_period($period);
    $clickByDay = bannerpro_fetch_counts_by_day($modx, 'click', $period, $positionId);
    $impressionByDay = bannerpro_fetch_counts_by_day($modx, 'impression', $period, $positionId);
    $conversionByDay = bannerpro_fetch_counts_by_day($modx, 'conversion', $period, $positionId);

    $days = array_keys($clickByDay + $impressionByDay + $conversionByDay);
    sort($days);

    $rows = [];
    foreach ($days as $day) {
        $clicks = $clickByDay[$day] ?? 0;
        $impressions = $impressionByDay[$day] ?? 0;
        $conversions = $conversionByDay[$day] ?? 0;
        $rows[] = [
            'date' => $day,
            'clicks' => $clicks,
            'impressions' => $impressions,
            'conversions' => $conversions,
            'ctr' => bannerpro_stats_ctr($clicks, $impressions),
            'cvr' => bannerpro_stats_cvr($conversions, $clicks),
        ];
    }

    return bannerpro_stats_fill_period_days($rows, $period);
}

/**
 * @return array{start: DateTimeImmutable, end: DateTimeImmutable}|null
 */
function bannerpro_stats_period_range(string $period): ?array
{
    $period = bannerpro_normalize_period($period);
    $today = new DateTimeImmutable('today');

    switch ($period) {
        case BannerProClickPeriod::TODAY:
            return ['start' => $today, 'end' => $today];
        case BannerProClickPeriod::LAST_7_DAYS:
            return [
                'start' => $today->modify('-6 days'),
                'end' => $today,
            ];
        case BannerProClickPeriod::THIS_WEEK:
            $monday = bannerpro_week_monday($today);

            return [
                'start' => $monday,
                'end' => $monday->modify('+6 days'),
            ];
        case BannerProClickPeriod::LAST_WEEK:
            $monday = bannerpro_week_monday($today)->modify('-7 days');

            return [
                'start' => $monday,
                'end' => $monday->modify('+6 days'),
            ];
        case BannerProClickPeriod::THIS_MONTH:
            return [
                'start' => $today->modify('first day of this month'),
                'end' => $today->modify('last day of this month'),
            ];
        case BannerProClickPeriod::LAST_MONTH:
            $start = $today->modify('first day of last month');

            return ['start' => $start, 'end' => $start->modify('last day of this month')];
        case BannerProClickPeriod::THIS_YEAR:
            $year = (int) $today->format('Y');

            return [
                'start' => $today->setDate($year, 1, 1),
                'end' => $today->setDate($year, 12, 31),
            ];
        default:
            return null;
    }
}

/**
 * @param list<array{date: string, clicks: int, impressions: int, ctr: float|null}> $rows
 * @return list<array{date: string, clicks: int, impressions: int, ctr: float|null}>
 */
function bannerpro_stats_fill_period_days(array $rows, string $period): array
{
    $range = bannerpro_stats_period_range($period);
    if ($range === null) {
        return $rows;
    }

    $indexed = [];
    foreach ($rows as $row) {
        $indexed[$row['date']] = $row;
    }

    $filled = [];
    for ($day = $range['start']; $day <= $range['end']; $day = $day->modify('+1 day')) {
        $key = $day->format('Y-m-d');
        $filled[] = $indexed[$key] ?? [
            'date' => $key,
            'clicks' => 0,
            'impressions' => 0,
            'conversions' => 0,
            'ctr' => null,
            'cvr' => null,
        ];
    }

    return $filled;
}

function bannerpro_stats_sql_period_where(string $field, string $period): string
{
    $period = bannerpro_normalize_period($period);
    if ($period === BannerProClickPeriod::ALL) {
        return '';
    }

    $bounds = bannerpro_period_date_bounds($period);
    if ($bounds !== null) {
        return sprintf(
            " AND %s >= '%s' AND %s <= '%s'",
            $field,
            $bounds['start']->format('Y-m-d H:i:s'),
            $field,
            $bounds['end']->format('Y-m-d H:i:s')
        );
    }

    if ($period === BannerProClickPeriod::LAST_MONTH) {
        $like = (new DateTimeImmutable('first day of last month'))->format('Y-m');

        return " AND {$field} LIKE '{$like}%'";
    }

    $now = new DateTimeImmutable();
    $map = [
        BannerProClickPeriod::TODAY => $now->format('Y-m-d'),
        BannerProClickPeriod::THIS_MONTH => $now->format('Y-m'),
        BannerProClickPeriod::THIS_YEAR => $now->format('Y'),
    ];
    if (!isset($map[$period])) {
        return '';
    }

    return " AND {$field} LIKE '" . $map[$period] . "%'";
}

/**
 * @return list<array<string, mixed>>
 */
function bannerpro_stats_build_top_ads_rows(modX $modx, string $period, int $positionId = 0): array
{
    $clickCounts = bannerpro_fetch_click_counts(
        $modx,
        'ad',
        array_merge(bannerpro_stats_base_conditions($period, $positionId), bannerpro_click_period_condition($period))
    );
    $impressionCounts = bannerpro_fetch_impression_counts(
        $modx,
        'ad',
        array_merge(bannerpro_stats_base_conditions($period, $positionId), bannerpro_impression_period_condition($period))
    );
    $conversionCounts = bannerpro_fetch_conversion_counts(
        $modx,
        'ad',
        array_merge(bannerpro_stats_base_conditions($period, $positionId), bannerpro_click_period_condition($period))
    );

    $rows = [];
    foreach ($modx->getCollection('byAd') as $ad) {
        $id = (int) $ad->get('id');
        $clicks = $clickCounts[$id] ?? 0;
        $impressions = $impressionCounts[$id] ?? 0;
        if ($clicks === 0 && $impressions === 0) {
            continue;
        }
        $row = $ad->toArray();
        $row['clicks'] = $clicks;
        $row['impressions'] = $impressions;
        $row['conversions'] = $conversionCounts[$id] ?? 0;
        $row['ctr'] = bannerpro_stats_ctr($clicks, $impressions);
        $rows[] = $row;
    }

    usort($rows, static function (array $a, array $b): int {
        return ($b['impressions'] <=> $a['impressions']) ?: ($b['clicks'] <=> $a['clicks']);
    });

    return $rows;
}

/**
 * @return list<array<string, mixed>>
 */
function bannerpro_stats_top_ads(modX $modx, string $period, int $positionId = 0, int $limit = 10, int $start = 0): array
{
    $rows = bannerpro_stats_build_top_ads_rows($modx, $period, $positionId);

    if ($limit > 0) {
        return array_slice($rows, $start, $limit);
    }
    if ($start > 0) {
        return array_slice($rows, $start);
    }

    return $rows;
}

/**
 * @return array<int, string>
 */
function bannerpro_stats_weekday_labels(modX $modx): array
{
    bannerpro_load_lexicon($modx, 'default');
    $keys = [
        1 => 'bannerpro.stats.weekday.mon',
        2 => 'bannerpro.stats.weekday.tue',
        3 => 'bannerpro.stats.weekday.wed',
        4 => 'bannerpro.stats.weekday.thu',
        5 => 'bannerpro.stats.weekday.fri',
        6 => 'bannerpro.stats.weekday.sat',
        7 => 'bannerpro.stats.weekday.sun',
    ];
    $labels = [];
    foreach ($keys as $index => $key) {
        $labels[$index] = (string) $modx->lexicon($key);
    }

    return $labels;
}

/**
 * @return list<array{label: string, weekday: int, clicks: int, impressions: int, conversions: int, ctr: float|null, cvr: float|null}>
 */
function bannerpro_stats_by_weekday(modX $modx, string $period, int $positionId = 0): array
{
    $labels = bannerpro_stats_weekday_labels($modx);
    $series = [];
    for ($weekday = 1; $weekday <= 7; $weekday++) {
        $series[$weekday] = [
            'label' => $labels[$weekday],
            'weekday' => $weekday,
            'clicks' => 0,
            'impressions' => 0,
            'conversions' => 0,
            'ctr' => null,
            'cvr' => null,
        ];
    }

    foreach (bannerpro_stats_by_day($modx, $period, $positionId) as $row) {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', (string) ($row['date'] ?? ''));
        if (!$date) {
            continue;
        }
        $weekday = (int) $date->format('N');
        if ($weekday < 1 || $weekday > 7) {
            continue;
        }
        $series[$weekday]['clicks'] += (int) ($row['clicks'] ?? 0);
        $series[$weekday]['impressions'] += (int) ($row['impressions'] ?? 0);
        $series[$weekday]['conversions'] += (int) ($row['conversions'] ?? 0);
    }

    $rows = [];
    for ($weekday = 1; $weekday <= 7; $weekday++) {
        $row = $series[$weekday];
        $row['ctr'] = bannerpro_stats_ctr($row['clicks'], $row['impressions']);
        $row['cvr'] = bannerpro_stats_cvr($row['conversions'], $row['clicks']);
        $rows[] = $row;
    }

    return $rows;
}

/**
 * @param array{clicks: int, impressions: int, ctr: float|null} $baseSummary
 * @param array{clicks: int, impressions: int, ctr: float|null} $compareSummary
 * @return array{clicks_pct: float|null, impressions_pct: float|null, ctr_pp: float|null}
 */
function bannerpro_stats_delta(array $baseSummary, array $compareSummary): array
{
    $baseClicks = (int) ($baseSummary['clicks'] ?? 0);
    $compareClicks = (int) ($compareSummary['clicks'] ?? 0);
    $baseImpressions = (int) ($baseSummary['impressions'] ?? 0);
    $compareImpressions = (int) ($compareSummary['impressions'] ?? 0);

    $clicksPct = null;
    if ($compareClicks > 0) {
        $clicksPct = round(($baseClicks - $compareClicks) / $compareClicks * 100, 1);
    } elseif ($baseClicks > 0) {
        $clicksPct = null;
    } else {
        $clicksPct = 0.0;
    }

    $impressionsPct = null;
    if ($compareImpressions > 0) {
        $impressionsPct = round(($baseImpressions - $compareImpressions) / $compareImpressions * 100, 1);
    } elseif ($baseImpressions > 0) {
        $impressionsPct = null;
    } else {
        $impressionsPct = 0.0;
    }

    $baseCtr = $baseSummary['ctr'] ?? null;
    $compareCtr = $compareSummary['ctr'] ?? null;
    $ctrPp = null;
    if ($baseCtr !== null && $compareCtr !== null) {
        $ctrPp = round($baseCtr - $compareCtr, 2);
    }

    $baseConversions = (int) ($baseSummary['conversions'] ?? 0);
    $compareConversions = (int) ($compareSummary['conversions'] ?? 0);
    $conversionsPct = null;
    if ($compareConversions > 0) {
        $conversionsPct = round(($baseConversions - $compareConversions) / $compareConversions * 100, 1);
    } elseif ($baseConversions > 0) {
        $conversionsPct = null;
    } else {
        $conversionsPct = 0.0;
    }

    $baseCvr = $baseSummary['cvr'] ?? null;
    $compareCvr = $compareSummary['cvr'] ?? null;
    $cvrPp = null;
    if ($baseCvr !== null && $compareCvr !== null) {
        $cvrPp = round($baseCvr - $compareCvr, 2);
    }

    return [
        'clicks_pct' => $clicksPct,
        'impressions_pct' => $impressionsPct,
        'ctr_pp' => $ctrPp,
        'conversions_pct' => $conversionsPct,
        'cvr_pp' => $cvrPp,
    ];
}

/**
 * @return array{
 *   base: array{summary: array{clicks: int, impressions: int, conversions: int, ctr: float|null, cvr: float|null}, by_day: list<array<string, mixed>>},
 *   compare: array{summary: array{clicks: int, impressions: int, conversions: int, ctr: float|null, cvr: float|null}, by_day: list<array<string, mixed>>},
 *   delta: array{clicks_pct: float|null, impressions_pct: float|null, ctr_pp: float|null, conversions_pct: float|null, cvr_pp: float|null}
 * }
 */
function bannerpro_stats_compare(
    modX $modx,
    string $basePeriod,
    string $comparePeriod,
    int $positionId = 0
): array {
    $basePeriod = bannerpro_normalize_period($basePeriod);
    $comparePeriod = bannerpro_normalize_period($comparePeriod);
    $baseSummary = bannerpro_stats_summary($modx, $basePeriod, $positionId);
    $compareSummary = bannerpro_stats_summary($modx, $comparePeriod, $positionId);

    return [
        'base' => [
            'summary' => $baseSummary,
            'by_day' => bannerpro_stats_by_weekday($modx, $basePeriod, $positionId),
        ],
        'compare' => [
            'summary' => $compareSummary,
            'by_day' => bannerpro_stats_by_weekday($modx, $comparePeriod, $positionId),
        ],
        'delta' => bannerpro_stats_delta($baseSummary, $compareSummary),
    ];
}

/**
 * @return array{clicks: int, impressions: int}
 */
function bannerpro_purge_old_stats(modX $modx): array
{
    $removed = ['clicks' => 0, 'impressions' => 0];
    $clicksDays = (int) $modx->getOption('bannerpro_clicks_retention_days', null, 365);
    $impressionsDays = (int) $modx->getOption('bannerpro_impressions_retention_days', null, 365);

    if ($clicksDays > 0) {
        $cutoff = (new DateTimeImmutable())->modify('-' . $clicksDays . ' days')->format('Y-m-d H:i:s');
        $removed['clicks'] = (int) $modx->removeCollection('byClick', ['clickdate:<=' => $cutoff]);
    }
    if ($impressionsDays > 0) {
        $cutoff = (new DateTimeImmutable())->modify('-' . $impressionsDays . ' days')->format('Y-m-d H:i:s');
        $removed['impressions'] = (int) $modx->removeCollection('byImpression', ['impressiondate:<=' => $cutoff]);
    }

    return $removed;
}

function bannerpro_stats_export_period_label(modX $modx, string $period): string
{
    bannerpro_load_lexicon($modx, 'default');
    $period = bannerpro_normalize_period($period);
    $map = [
        BannerProClickPeriod::ALL => 'bannerpro.stats.overall',
        BannerProClickPeriod::TODAY => 'bannerpro.stats.today',
        BannerProClickPeriod::LAST_7_DAYS => 'bannerpro.stats.last_7_days',
        BannerProClickPeriod::THIS_WEEK => 'bannerpro.stats.this_week',
        BannerProClickPeriod::LAST_WEEK => 'bannerpro.stats.last_week',
        BannerProClickPeriod::THIS_MONTH => 'bannerpro.stats.thismonth',
        BannerProClickPeriod::LAST_MONTH => 'bannerpro.stats.lastmonth',
        BannerProClickPeriod::THIS_YEAR => 'bannerpro.stats.thisyear',
    ];

    return (string) $modx->lexicon($map[$period] ?? 'bannerpro.stats.overall');
}

function bannerpro_stats_export_position_label(modX $modx, int $positionId): string
{
    bannerpro_load_lexicon($modx, 'default');
    if ($positionId <= 0) {
        return (string) $modx->lexicon('bannerpro.stats.all_positions');
    }
    $position = $modx->getObject('byPosition', $positionId);

    return $position ? (string) $position->get('name') : (string) $positionId;
}

/**
 * @param resource $out
 */
function bannerpro_stats_export_section($out, string $title, array $header, array $rows): void
{
    fputcsv($out, []);
    fputcsv($out, [$title]);
    fputcsv($out, $header);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
}

/**
 * @param resource $out
 */
function bannerpro_stats_export_report(modX $modx, $out, string $period, int $positionId): void
{
    bannerpro_load_lexicon($modx, 'default');

    fputcsv($out, [
        (string) $modx->lexicon('bannerpro.stats.export.meta.param'),
        (string) $modx->lexicon('bannerpro.stats.export.meta.value'),
    ]);
    fputcsv($out, [
        (string) $modx->lexicon('bannerpro.stats.export.meta.period'),
        bannerpro_stats_export_period_label($modx, $period),
    ]);
    fputcsv($out, [
        (string) $modx->lexicon('bannerpro.stats.export.meta.position'),
        bannerpro_stats_export_position_label($modx, $positionId),
    ]);
    fputcsv($out, [
        (string) $modx->lexicon('bannerpro.stats.export.meta.generated'),
        date('Y-m-d H:i:s'),
    ]);

    $summary = bannerpro_stats_summary($modx, $period, $positionId);
    bannerpro_stats_export_section(
        $out,
        (string) $modx->lexicon('bannerpro.stats.export.section.summary'),
        [
            (string) $modx->lexicon('bannerpro.ads.clicks'),
            (string) $modx->lexicon('bannerpro.stats.impressions'),
            (string) $modx->lexicon('bannerpro.stats.ctr'),
            (string) $modx->lexicon('bannerpro.ads.conversions'),
            (string) $modx->lexicon('bannerpro.stats.cvr'),
        ],
        [[
            $summary['clicks'],
            $summary['impressions'],
            $summary['ctr'] ?? '',
            $summary['conversions'],
            $summary['cvr'] ?? '',
        ]]
    );

    $byDayRows = [];
    foreach (bannerpro_stats_by_day($modx, $period, $positionId) as $row) {
        $byDayRows[] = [
            $row['date'],
            $row['clicks'],
            $row['impressions'],
            $row['ctr'] ?? '',
        ];
    }
    bannerpro_stats_export_section(
        $out,
        (string) $modx->lexicon('bannerpro.stats.export.section.by_day'),
        [
            (string) $modx->lexicon('bannerpro.stats.export.col.date'),
            (string) $modx->lexicon('bannerpro.ads.clicks'),
            (string) $modx->lexicon('bannerpro.stats.impressions'),
            (string) $modx->lexicon('bannerpro.stats.ctr'),
        ],
        $byDayRows
    );

    $bannerRows = [];
    foreach (bannerpro_stats_top_ads($modx, $period, $positionId, 0) as $row) {
        $bannerRows[] = [
            $row['id'] ?? '',
            $row['name'] ?? '',
            $row['clicks'] ?? 0,
            $row['impressions'] ?? 0,
            $row['ctr'] ?? '',
        ];
    }
    bannerpro_stats_export_section(
        $out,
        (string) $modx->lexicon('bannerpro.stats.export.section.banners'),
        [
            'ID',
            (string) $modx->lexicon('bannerpro.ads.name'),
            (string) $modx->lexicon('bannerpro.ads.clicks'),
            (string) $modx->lexicon('bannerpro.stats.impressions'),
            (string) $modx->lexicon('bannerpro.stats.ctr'),
        ],
        $bannerRows
    );

    $referrerRows = [];
    $referrers = bannerpro_clicks_get_referrers($modx, $period, 0, 0, $positionId);
    foreach ($referrers['data'] as $row) {
        $referrerRows[] = [
            $row['referrer'] ?? '',
            $row['clicks'] ?? 0,
        ];
    }
    bannerpro_stats_export_section(
        $out,
        (string) $modx->lexicon('bannerpro.stats.export.section.referrers'),
        [
            (string) $modx->lexicon('bannerpro.stats.referrer'),
            (string) $modx->lexicon('bannerpro.ads.clicks'),
        ],
        $referrerRows
    );
}

function bannerpro_stats_export_csv(modX $modx, string $period, int $positionId, string $type): void
{
    $exportType = in_array($type, ['summary', 'report'], true) ? 'report' : $type;
    $filename = 'bannerpro-' . $exportType . '-' . date('Y-m-d') . '.csv';
    if (!headers_sent()) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    }

    $out = fopen('php://output', 'w');
    if ($out === false) {
        return;
    }

    fwrite($out, "\xEF\xBB\xBF");

    switch ($type) {
        case 'summary':
        case 'report':
            bannerpro_stats_export_report($modx, $out, $period, $positionId);
            break;
        case 'impressions':
            $conditions = array_merge(
                bannerpro_stats_base_conditions($period, $positionId),
                bannerpro_impression_period_condition($period)
            );
            fputcsv($out, ['ad', 'position', 'adposition', 'impressiondate', 'ip']);
            foreach ($modx->getCollection('byImpression', $conditions) as $row) {
                fputcsv($out, [
                    $row->get('ad'),
                    $row->get('position'),
                    $row->get('adposition'),
                    $row->get('impressiondate'),
                    $row->get('ip'),
                ]);
            }
            break;
        case 'referrers':
            $result = bannerpro_clicks_get_referrers($modx, $period, 0, 0, $positionId);
            fputcsv($out, ['referrer', 'clicks']);
            foreach ($result['data'] as $row) {
                fputcsv($out, [$row['referrer'] ?? '', $row['clicks'] ?? 0]);
            }
            break;
        case 'clicks':
        default:
            $conditions = array_merge(
                bannerpro_stats_base_conditions($period, $positionId),
                bannerpro_click_period_condition($period)
            );
            fputcsv($out, ['ad', 'position', 'clickdate', 'referrer', 'ip']);
            foreach ($modx->getCollection('byClick', $conditions) as $row) {
                fputcsv($out, [
                    $row->get('ad'),
                    $row->get('position'),
                    $row->get('clickdate'),
                    $row->get('referrer'),
                    $row->get('ip'),
                ]);
            }
            break;
    }

    fclose($out);
    exit;
}
