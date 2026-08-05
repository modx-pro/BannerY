<?php

defined('MODX_CORE_PATH') || exit;

/**
 * Preset-баннеры для «Создать из шаблона» в менеджере BannerPro.
 *
 * @param array{
 *   qa_resource_id?: int,
 *   qa_parent_id?: int,
 *   category_id?: int,
 *   now?: DateTimeImmutable
 * } $context
 * @return list<array{name: string, type: string, tpl: string, defaults: string}>
 */
function bannerpro_ad_template_presets(array $context = []): array
{
    $now = $context['now'] ?? new DateTimeImmutable();
    $fmt = static fn (DateTimeImmutable $dt): string => $dt->format('Y-m-d H:i:s');
    $qaResourceId = max(0, (int) ($context['qa_resource_id'] ?? 0));
    $qaParentId = max(0, (int) ($context['qa_parent_id'] ?? 0));
    $categoryId = max(0, (int) ($context['category_id'] ?? 0));

    $businessHours = json_encode([
        'weekdays' => [1, 2, 3, 4, 5],
        'hours' => range(9, 18),
    ], JSON_UNESCAPED_UNICODE);

    $allHours = json_encode([
        'weekdays' => [1, 2, 3, 4, 5, 6, 7],
        'hours' => range(0, 23),
    ], JSON_UNESCAPED_UNICODE);

    $demoImage = 'assets/images/products/22/product-1.jpg';

    return [
        [
            'name' => 'Sidebar 300×250',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Sidebar banner 300×250',
                'image' => $demoImage,
                'url' => '[[~1]]',
                'max_clicks' => 0,
                'max_impressions' => 0,
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'HTML Promo block',
            'type' => 'html',
            'tpl' => 'tpl.BannerPro.byHtml',
            'defaults' => json_encode([
                'description' => 'Promotional HTML block',
                'html' => '<div class="bannerpro-promo alert alert-info mb-0"><strong>Promo</strong> — HTML block</div>',
                'url' => '[[~1]]',
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'Product card',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Product banner for MiniShop3',
                'image' => $demoImage,
                'url' => '[[~25]]',
                'product_id' => 0,
                'max_clicks' => 0,
                'max_impressions' => 0,
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: Leaderboard 728×90',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Wide leaderboard — smoke-тест широкого баннера',
                'image' => $demoImage,
                'url' => '[[~1]]',
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: Tags (sale, promo)',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Метки sale + promo — &tags=sale,promo на фронте',
                'image' => $demoImage,
                'url' => '[[~1]]',
                'tags' => '["sale","promo"]',
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: Target page',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => $qaResourceId > 0
                    ? 'Только страница id=' . $qaResourceId . ' (bannerpro-test)'
                    : 'Таргетинг: укажите target_resource_id QA-страницы',
                'image' => $demoImage,
                'url' => '[[~1]]',
                'target_resource_id' => $qaResourceId,
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: Target section',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => $qaParentId > 0
                    ? 'Только раздел parent=' . $qaParentId
                    : 'Таргетинг: укажите target_parent_id раздела',
                'image' => $demoImage,
                'url' => '[[~1]]',
                'target_parent_id' => $qaParentId,
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: Daypart 9–18',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Dayparting: пн–пт, 9:00–18:59',
                'image' => $demoImage,
                'url' => '[[~1]]',
                'show_hours' => $businessHours,
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: Daypart 24/7',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Dayparting: все дни и часы',
                'image' => $demoImage,
                'url' => '[[~1]]',
                'show_hours' => $allHours,
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: Scheduled campaign',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Активен по start/end (+30 дней от seed)',
                'image' => $demoImage,
                'url' => '[[~1]]',
                'start' => $fmt($now->modify('-1 day')),
                'end' => $fmt($now->modify('+30 days')),
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: HTML CTA button',
            'type' => 'html',
            'tpl' => 'tpl.BannerPro.byHtml',
            'defaults' => json_encode([
                'description' => 'HTML с кнопкой — проверка click_url в HTML-баннере',
                'html' => '<div class="p-3 border rounded text-center">'
                    . '<p class="mb-2"><strong>Спецпредложение</strong></p>'
                    . '<span class="btn btn-primary btn-sm">Перейти</span></div>',
                'url' => '[[~1]]',
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: External URL (UTM)',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Внешний URL — редирект + UTM при bannerpro_utm_enabled=1',
                'image' => $demoImage,
                'url' => 'https://example.com/promo?utm_test=1',
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: Draft (inactive)',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Черновик active=0 — виден только с &showInactive=1',
                'image' => $demoImage,
                'url' => '[[~1]]',
                'active' => 0,
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: Caps (clicks/impressions)',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Лимиты: max_clicks=50, max_impressions=1000',
                'image' => $demoImage,
                'url' => '[[~1]]',
                'max_clicks' => 50,
                'max_impressions' => 1000,
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: MS3 category',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => $categoryId > 0
                    ? 'MS3 category_id=' . $categoryId . ' (родитель товара 25)'
                    : 'MS3: укажите category_id каталога',
                'image' => $demoImage,
                'url' => '[[~25]]',
                'product_id' => 0,
                'category_id' => $categoryId,
            ], JSON_UNESCAPED_UNICODE),
        ],
        [
            'name' => 'QA: MS3 product #25',
            'type' => 'image',
            'tpl' => 'tpl.BannerPro.byAd',
            'defaults' => json_encode([
                'description' => 'Баннер только на карточке product_id=25',
                'image' => $demoImage,
                'url' => '[[~25]]',
                'product_id' => 25,
            ], JSON_UNESCAPED_UNICODE),
        ],
    ];
}

/**
 * Идемпотентно добавляет preset-шаблоны (по уникальному name).
 *
 * @param array<string, mixed> $context см. bannerpro_ad_template_presets()
 */
function bannerpro_seed_ad_templates(modX $modx, array $context = []): int
{
    $created = 0;
    foreach (bannerpro_ad_template_presets($context) as $preset) {
        $name = (string) ($preset['name'] ?? '');
        if ($name === '') {
            continue;
        }
        if ($modx->getObject('byAdTemplate', ['name' => $name])) {
            continue;
        }
        $obj = $modx->newObject('byAdTemplate');
        $obj->fromArray($preset);
        if ($obj->save()) {
            $created++;
        }
    }

    return $created;
}
