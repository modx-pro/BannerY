<?php
/**
 * Идемпотентное наполнение демо-данными BannerPro для QA / bannerpro_test.tpl.
 * Запуск из корня MODX: php core/elements/demo/seed_bannerpro_demo.php
 */

$root = __DIR__;
while ($root !== '/' && !is_file($root . '/config.core.php')) {
    $root = dirname($root);
}
if (!is_file($root . '/config.core.php')) {
    fwrite(STDERR, "config.core.php not found. Place script under MODX core/elements/demo/.\n");
    exit(1);
}

require_once $root . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new MODX\Revolution\modX();
$modx->initialize('mgr');
require_once MODX_CORE_PATH . 'components/bannerpro/include/helpers.php';
bannerpro_add_package($modx);

function seed_position(modX $modx, string $name): int
{
    /** @var byPosition|null $pos */
    $pos = $modx->getObject('byPosition', ['name' => $name]);
    if ($pos) {
        return (int) $pos->get('id');
    }
    $pos = $modx->newObject('byPosition');
    $pos->fromArray(['name' => $name]);
    if (!$pos->save()) {
        throw new RuntimeException("Failed to create position: {$name}");
    }

    return (int) $pos->get('id');
}

/**
 * @param list<string> $positionNames
 * @param array<string, int> $positionIdx явный порядок idx по имени позиции
 * @param array<string, int> $positionWeights вес byAdPosition.weight по имени позиции (4.6 weighted)
 */
function seed_ad(modX $modx, array $fields, array $positionNames, array $positionIdx = [], array $positionWeights = []): int
{
    $name = (string) ($fields['name'] ?? '');
    /** @var byAd|null $ad */
    $ad = $modx->getObject('byAd', ['name' => $name]);
    if (!$ad) {
        $ad = $modx->newObject('byAd');
    }
    $ad->fromArray(array_merge([
        'url' => '',
        'image' => '',
        'source' => 1,
        'active' => 1,
        'description' => '',
        'type' => 'image',
        'html' => null,
        'start' => null,
        'end' => null,
        'product_id' => 0,
    ], $fields));
    if (!$ad->save()) {
        throw new RuntimeException("Failed to save ad: {$name}");
    }

    $adId = (int) $ad->get('id');
    foreach ($positionNames as $positionName) {
        $positionId = seed_position($modx, $positionName);
        /** @var byAdPosition|null $link */
        $link = $modx->getObject('byAdPosition', ['ad' => $adId, 'position' => $positionId]);
        if (!$link) {
            $link = $modx->newObject('byAdPosition');
            $link->fromArray(['ad' => $adId, 'position' => $positionId, 'idx' => 0]);
        }
        if (isset($positionIdx[$positionName])) {
            $link->set('idx', (int) $positionIdx[$positionName]);
        } elseif ($link->isNew()) {
            $link->set('idx', (int) $modx->getCount('byAdPosition', ['position' => $positionId]));
        }
        if (isset($positionWeights[$positionName])) {
            $link->set('weight', max(1, (int) $positionWeights[$positionName]));
        }
        if (!$link->save()) {
            throw new RuntimeException("Failed to link ad {$adId} to position {$positionName}");
        }
    }

    return $adId;
}

/**
 * В демо-каталоге MS3 часто есть только product-1.jpg — копируем для product-2..7.
 */
function ensure_demo_product_images(string $modxRoot): void
{
    $dir = $modxRoot . '/assets/images/products/22';
    $source = $dir . '/product-1.jpg';
    if (!is_file($source)) {
        fwrite(STDERR, "Skip demo images: {$source} not found.\n");

        return;
    }
    for ($i = 2; $i <= 7; $i++) {
        $target = $dir . '/product-' . $i . '.jpg';
        if (!is_file($target)) {
            copy($source, $target);
        }
    }
}

$now = new DateTimeImmutable();
$fmt = static fn (DateTimeImmutable $dt): string => $dt->format('Y-m-d H:i:s');

/**
 * @return array{resource_id: int, parent_id: int}
 */
function seed_qa_page_ids(modX $modx): array
{
    $resourceId = 0;
    foreach (['bannerpro-test', 'bannerpro-test.html'] as $alias) {
        /** @var modResource|null $res */
        if ($res = $modx->getObject('modResource', ['alias' => $alias])) {
            $resourceId = (int) $res->get('id');
            break;
        }
    }
    if ($resourceId <= 0) {
        /** @var modTemplate|null $tpl */
        $tpl = $modx->getObject('modTemplate', ['templatename' => 'bannerpro_test']);
        if ($tpl) {
            /** @var modResource|null $res */
            $res = $modx->getObject('modResource', ['template' => (int) $tpl->get('id')]);
            if ($res) {
                $resourceId = (int) $res->get('id');
            }
        }
    }
    $parentId = 0;
    if ($resourceId > 0) {
        /** @var modResource|null $res */
        $res = $modx->getObject('modResource', $resourceId);
        $parentId = $res ? max(0, (int) $res->get('parent')) : 0;
    }

    return ['resource_id' => $resourceId, 'parent_id' => $parentId];
}

$positions = [
    'demo-sidebar',
    'demo-footer',
    'demo-html',
    'demo-shop',
    'demo-header',
    'demo-rotator',
    'demo-cross',
    'demo-tags',
    'demo-targeting',
    'demo-weighted',
    'demo-daypart',
    'demo-ab',
    'demo-lead',
    'test',
];

foreach ($positions as $name) {
    seed_position($modx, $name);
}

ensure_demo_product_images($root);

// --- image: sidebar / footer (стенд bannerpro_test.tpl) ---
seed_ad($modx, [
    'name' => 'demo-banner-sidebar-1',
    'image' => 'assets/images/products/22/product-1.jpg',
    'description' => 'Sidebar #1 (image)',
    'url' => '[[~1]]',
], ['demo-sidebar', 'test'], ['demo-sidebar' => 0, 'test' => 0]);

seed_ad($modx, [
    'name' => 'demo-banner-sidebar-2',
    'image' => 'assets/images/products/22/product-2.jpg',
    'description' => 'Sidebar #2 (image)',
    'url' => '[[~1]]',
], ['demo-sidebar'], ['demo-sidebar' => 1]);

seed_ad($modx, [
    'name' => 'demo-banner-footer-1',
    'image' => 'assets/images/products/22/product-3.jpg',
    'description' => 'Footer #1 (image)',
    'url' => '[[~1]]',
], ['demo-footer'], ['demo-footer' => 0]);

seed_ad($modx, [
    'name' => 'demo-banner-footer-2',
    'image' => 'assets/images/products/22/product-4.jpg',
    'description' => 'Footer #2 (внешний URL)',
    'url' => 'https://example.com/promo',
], ['demo-footer'], ['demo-footer' => 1]);

// --- html ---
seed_ad($modx, [
    'name' => 'demo-banner-html-1',
    'type' => 'html',
    'html' => '<div class="alert alert-info mb-0"><strong>HTML-баннер</strong> BannerPro (info)</div>',
    'url' => '[[~1]]',
    'description' => 'HTML info',
], ['demo-html'], ['demo-html' => 0]);

seed_ad($modx, [
    'name' => 'demo-banner-html-2',
    'type' => 'html',
    'html' => '<div class="alert alert-warning mb-0"><strong>HTML-баннер #2</strong> — warning block</div>',
    'url' => '[[~1]]',
    'description' => 'HTML warning',
], ['demo-html'], ['demo-html' => 1]);

// --- MiniShop3 ---
seed_ad($modx, [
    'name' => 'demo-banner-shop-global',
    'image' => 'assets/images/products/22/product-5.jpg',
    'description' => 'Shop global (product_id=0)',
    'url' => '[[~25]]',
    'product_id' => 0,
], ['demo-shop'], ['demo-shop' => 0]);

seed_ad($modx, [
    'name' => 'demo-banner-shop-product',
    'image' => 'assets/images/products/22/product-6.jpg',
    'description' => 'Shop product #25',
    'url' => '[[~25]]',
    'product_id' => 25,
    'active' => 1,
], ['demo-shop'], ['demo-shop' => 1]);

// --- inactive (showInactive) ---
seed_ad($modx, [
    'name' => 'demo-banner-inactive',
    'image' => 'assets/images/products/22/product-7.jpg',
    'description' => 'Inactive (active=0)',
    'active' => 0,
    'url' => '[[~1]]',
], ['demo-sidebar'], ['demo-sidebar' => 2]);

// --- расписание start/end ---
seed_ad($modx, [
    'name' => 'demo-banner-scheduled-active',
    'image' => 'assets/images/products/22/product-1.jpg',
    'description' => 'Активен по датам (start/end ок)',
    'start' => $fmt($now->modify('-2 days')),
    'end' => $fmt($now->modify('+30 days')),
    'url' => '[[~1]]',
], ['demo-header'], ['demo-header' => 0]);

seed_ad($modx, [
    'name' => 'demo-banner-scheduled-future',
    'image' => 'assets/images/products/22/product-1.jpg',
    'description' => 'Старт в будущем (не показывать)',
    'start' => $fmt($now->modify('+7 days')),
    'end' => null,
    'url' => '[[~1]]',
], ['demo-sidebar'], ['demo-sidebar' => 3]);

seed_ad($modx, [
    'name' => 'demo-banner-scheduled-expired',
    'image' => 'assets/images/products/22/product-1.jpg',
    'description' => 'Просрочен (end в прошлом)',
    'start' => $fmt($now->modify('-30 days')),
    'end' => $fmt($now->modify('-1 day')),
    'url' => '[[~1]]',
], ['demo-sidebar'], ['demo-sidebar' => 4]);

// --- ротатор: проверка idx ---
seed_ad($modx, [
    'name' => 'demo-banner-rotator-a',
    'image' => 'assets/images/products/22/product-1.jpg',
    'description' => 'Rotator A (idx=0)',
    'url' => '[[~1]]',
], ['demo-rotator'], ['demo-rotator' => 0]);

seed_ad($modx, [
    'name' => 'demo-banner-rotator-b',
    'image' => 'assets/images/products/22/product-1.jpg',
    'description' => 'Rotator B (idx=1)',
    'url' => '[[~1]]',
], ['demo-rotator'], ['demo-rotator' => 1]);

seed_ad($modx, [
    'name' => 'demo-banner-rotator-c',
    'image' => 'assets/images/products/22/product-1.jpg',
    'description' => 'Rotator C (idx=2)',
    'url' => '[[~1]]',
], ['demo-rotator'], ['demo-rotator' => 2]);

// --- кросс-позиция (несколько позиций у одного баннера) ---
seed_ad($modx, [
    'name' => 'demo-banner-cross',
    'image' => 'assets/images/products/22/product-1.jpg',
    'description' => 'Один баннер в sidebar + footer + cross',
    'url' => '[[~1]]',
], ['demo-sidebar', 'demo-footer', 'demo-cross'], [
    'demo-sidebar' => 5,
    'demo-footer' => 2,
    'demo-cross' => 0,
]);

// --- header strip ---
seed_ad($modx, [
    'name' => 'demo-banner-header-promo',
    'type' => 'html',
    'html' => '<div class="badge bg-primary">Header promo</div> <span class="small">demo-header · HTML</span>',
    'url' => '[[~1]]',
    'description' => 'Header HTML promo',
], ['demo-header'], ['demo-header' => 1]);

$qaIds = seed_qa_page_ids($modx);
$qaResourceId = (int) $qaIds['resource_id'];
$qaParentId = (int) $qaIds['parent_id'];
$otherResourceId = max(1, (int) $modx->getOption('site_start', null, 1));
if ($otherResourceId === $qaResourceId) {
    $otherResourceId = $qaResourceId > 1 ? 1 : 2;
}
$nowHour = (int) date('G');
$nowDow = (int) date('N');
$offDow = ($nowDow % 7) + 1;
if ($offDow === $nowDow) {
    $offDow = ($nowDow % 6) + 1;
}
$showHoursNow = json_encode(['weekdays' => [1, 2, 3, 4, 5, 6, 7], 'hours' => range(0, 23)], JSON_UNESCAPED_UNICODE);
$showHoursOff = json_encode(['weekdays' => [$offDow], 'hours' => range(0, 23)], JSON_UNESCAPED_UNICODE);

$categoryId = 0;
/** @var modResource|null $productResource */
if ($productResource = $modx->getObject('modResource', 25)) {
    $categoryId = max(0, (int) $productResource->get('parent'));
}

// --- 4.6: tags ---
seed_ad($modx, [
    'name' => 'demo-banner-tags-sale',
    'image' => 'assets/images/products/22/product-1.jpg',
    'description' => 'Tags: sale, promo',
    'url' => '[[~1]]',
    'tags' => '["sale","promo"]',
], ['demo-tags'], ['demo-tags' => 0]);

seed_ad($modx, [
    'name' => 'demo-banner-tags-news',
    'image' => 'assets/images/products/22/product-2.jpg',
    'description' => 'Tags: news',
    'url' => '[[~1]]',
    'tags' => '["news"]',
], ['demo-tags'], ['demo-tags' => 1]);

seed_ad($modx, [
    'name' => 'demo-banner-tags-both',
    'image' => 'assets/images/products/22/product-3.jpg',
    'description' => 'Tags: sale + news (tagsMode=all)',
    'url' => '[[~1]]',
    'tags' => '["sale","news"]',
], ['demo-tags'], ['demo-tags' => 2]);

// --- 4.6: resource / parent targeting ---
seed_ad($modx, [
    'name' => 'demo-banner-target-global',
    'image' => 'assets/images/products/22/product-4.jpg',
    'description' => 'Targeting: global (без target_*)',
    'url' => '[[~1]]',
], ['demo-targeting'], ['demo-targeting' => 0]);

if ($qaResourceId > 0) {
    seed_ad($modx, [
        'name' => 'demo-banner-target-page',
        'image' => 'assets/images/products/22/product-5.jpg',
        'description' => 'Targeting: страница id=' . $qaResourceId,
        'url' => '[[~1]]',
        'target_resource_id' => $qaResourceId,
    ], ['demo-targeting'], ['demo-targeting' => 1]);
}

if ($qaParentId > 0) {
    seed_ad($modx, [
        'name' => 'demo-banner-target-section',
        'image' => 'assets/images/products/22/product-6.jpg',
        'description' => 'Targeting: раздел parent=' . $qaParentId,
        'url' => '[[~1]]',
        'target_parent_id' => $qaParentId,
    ], ['demo-targeting'], ['demo-targeting' => 2]);
}

seed_ad($modx, [
    'name' => 'demo-banner-target-other',
    'image' => 'assets/images/products/22/product-7.jpg',
    'description' => 'Targeting: другая страница id=' . $otherResourceId,
    'url' => '[[~1]]',
    'target_resource_id' => $otherResourceId,
], ['demo-targeting'], ['demo-targeting' => 3]);

// --- 4.6: dayparting (show_hours) ---
seed_ad($modx, [
    'name' => 'demo-banner-daypart-now',
    'image' => 'assets/images/products/22/product-1.jpg',
    'description' => 'Daypart: круглосуточно (всегда виден)',
    'url' => '[[~1]]',
    'show_hours' => $showHoursNow,
], ['demo-daypart'], ['demo-daypart' => 0]);

seed_ad($modx, [
    'name' => 'demo-banner-daypart-off',
    'image' => 'assets/images/products/22/product-2.jpg',
    'description' => 'Daypart: только dow=' . $offDow . ' (сегодня скрыт)',
    'url' => '[[~1]]',
    'show_hours' => $showHoursOff,
], ['demo-daypart'], ['demo-daypart' => 1]);

// --- 4.6: weighted rotation ---
seed_ad($modx, [
    'name' => 'demo-banner-weighted-light',
    'image' => 'assets/images/products/22/product-3.jpg',
    'description' => 'Weighted: weight=1',
    'url' => '[[~1]]',
], ['demo-weighted'], ['demo-weighted' => 0], ['demo-weighted' => 1]);

seed_ad($modx, [
    'name' => 'demo-banner-weighted-mid',
    'image' => 'assets/images/products/22/product-4.jpg',
    'description' => 'Weighted: weight=3',
    'url' => '[[~1]]',
], ['demo-weighted'], ['demo-weighted' => 1], ['demo-weighted' => 3]);

seed_ad($modx, [
    'name' => 'demo-banner-weighted-heavy',
    'image' => 'assets/images/products/22/product-5.jpg',
    'description' => 'Weighted: weight=10',
    'url' => '[[~1]]',
], ['demo-weighted'], ['demo-weighted' => 2], ['demo-weighted' => 10]);

seed_ad($modx, [
    'name' => 'demo-banner-ab-a',
    'description' => 'A/B variant A',
    'image' => 'assets/images/products/22/product-1.jpg',
    'url' => '[[~1]]',
], ['demo-ab'], ['demo-ab' => 0]);

seed_ad($modx, [
    'name' => 'demo-banner-ab-b',
    'description' => 'A/B variant B',
    'image' => 'assets/images/products/22/product-2.jpg',
    'url' => '[[~1]]',
], ['demo-ab'], ['demo-ab' => 1]);

seed_ad($modx, [
    'name' => 'demo-banner-lead',
    'type' => 'html',
    'description' => 'FormIt lead (click_url)',
    'html' => '<p class="small text-muted mb-0">Rendered via tplHtml lead chunk</p>',
    'url' => '',
], ['demo-lead'], ['demo-lead' => 0]);

// --- 4.6: MS3 category_id ---
if ($categoryId > 0) {
    seed_ad($modx, [
        'name' => 'demo-banner-shop-category',
        'image' => 'assets/images/products/22/product-6.jpg',
        'description' => 'MS3 category_id=' . $categoryId . ' (глобальный по категории)',
        'url' => '[[~25]]',
        'product_id' => 0,
        'category_id' => $categoryId,
    ], ['demo-shop'], ['demo-shop' => 2]);
}

// Legacy ad id=1: снять будущую дату start
/** @var byAd|null $legacy */
if ($legacy = $modx->getObject('byAd', 1)) {
    $legacy->set('start', null);
    $legacy->set('end', null);
    $legacy->save();
}

/**
 * @param array<string, string> $settings
 */
function seed_plugin_enabled(modX $modx, string $name, bool $enabled = true): void
{
    /** @var modPlugin|null $plugin */
    $plugin = $modx->getObject('modPlugin', ['name' => $name]);
    if (!$plugin) {
        echo "  WARN: plugin {$name} not found — skip\n";

        return;
    }
    $plugin->set('disabled', $enabled ? 0 : 1);
    if (!$plugin->save()) {
        throw new RuntimeException("Failed to save plugin state: {$name}");
    }
}

function seed_settings(modX $modx, array $settings): void
{
    foreach ($settings as $key => $value) {
        /** @var modSystemSetting|null $setting */
        $setting = $modx->getObject('modSystemSetting', ['key' => $key]);
        if (!$setting) {
            echo "  WARN: setting {$key} not found — skip (upgrade BannerPro package)\n";

            continue;
        }
        $setting->set('value', $value);
        if (!$setting->save()) {
            throw new RuntimeException("Failed to save setting: {$key}");
        }
    }
}

function seed_sync_qa_template(modX $modx): void
{
    $path = MODX_CORE_PATH . 'elements/templates/demo/bannerpro_test.tpl';
    if (!is_readable($path)) {
        echo "  WARN: {$path} not readable — skip template sync\n";

        return;
    }
    /** @var modTemplate|null $tpl */
    $tpl = $modx->getObject('modTemplate', ['templatename' => 'bannerpro_test']);
    if (!$tpl) {
        echo "  WARN: modTemplate bannerpro_test not found — skip template sync\n";

        return;
    }
    $content = file_get_contents($path);
    if ($content === false || $content === '') {
        echo "  WARN: empty template file — skip template sync\n";

        return;
    }
    $tpl->set('content', $content);
    if (!$tpl->save()) {
        throw new RuntimeException('Failed to sync bannerpro_test template from file');
    }
    echo "  template bannerpro_test synced from {$path}\n";
}

seed_settings($modx, [
    'bannerpro_track_impressions' => '1',
    'bannerpro_impression' => 'bannerimpression',
    'bannerpro_click' => 'bannerclick',
    'bannerpro_impression_lazy' => '1',
    'bannerpro_filter_bots' => '0',
    'bannerpro_utm_enabled' => '1',
    'bannerpro_utm_source' => 'bannerpro',
    'bannerpro_utm_medium' => 'banner',
    'bannerpro_utm_campaign' => 'demo-{ad_id}',
    'bannerpro_utm_content' => '{ad_id}-{adposition}',
    'bannerpro_analytics_enabled' => '1',
    'bannerpro_analytics_ga4' => '1',
    'bannerpro_analytics_ga4_click' => 'bannerpro_click',
    'bannerpro_analytics_ga4_impression' => 'bannerpro_impression',
    'bannerpro_analytics_matomo' => '1',
    'bannerpro_analytics_ym_counter' => '99999999',
    'bannerpro_analytics_ym_click_goal' => 'bannerpro_click',
    'bannerpro_analytics_ym_impression_goal' => 'bannerpro_impression',
    'bannerpro_webhook_url' => '',
    'bannerpro_webhook_impression_url' => '',
    'bannerpro_webhook_secret' => '',
    'bannerpro_ab_ttl' => '30',
]);

seed_sync_qa_template($modx);

seed_plugin_enabled($modx, 'BannerProClickout', true);
seed_plugin_enabled($modx, 'BannerProImpression', true);
seed_plugin_enabled($modx, 'BannerProAnalytics', true);

require_once MODX_CORE_PATH . 'components/bannerpro/include/ad_template_presets.php';
$templatesCreated = bannerpro_seed_ad_templates($modx, [
    'qa_resource_id' => $qaResourceId,
    'qa_parent_id' => $qaParentId,
    'category_id' => $categoryId,
    'now' => $now,
]);

$modx->cacheManager->delete('bannerpro');
$modx->cacheManager->refresh();

echo "BannerPro demo seed OK\n\n";
if ($templatesCreated > 0) {
    echo "Ad templates created: {$templatesCreated}\n";
}
echo "QA page: resource_id={$qaResourceId}, parent_id={$qaParentId}, other_resource_id={$otherResourceId}\n";
echo "Daypart: now=all hours; off_dow={$offDow} (today dow={$nowDow})\n";
if ($categoryId > 0) {
    echo "MS3 category_id={$categoryId} (parent of product 25)\n";
}
echo "\nПозиции:\n";
foreach ($positions as $name) {
    $id = seed_position($modx, $name);
    $count = (int) $modx->getCount('byAdPosition', ['position' => $id]);
    echo "  - {$name} (id={$id}, links={$count})\n";
}

echo "\nБаннеры:\n";
/** @var byAd[] $ads */
$ads = $modx->getCollection('byAd', ['name:LIKE' => 'demo-banner-%']);
usort($ads, static fn (byAd $a, byAd $b) => strcmp($a->get('name'), $b->get('name')));
foreach ($ads as $ad) {
    $type = $ad->get('type');
    $active = (int) $ad->get('active');
    $start = $ad->get('start') ?: '—';
    $end = $ad->get('end') ?: '—';
    $product = (int) $ad->get('product_id');
    echo sprintf(
        "  - %s [id=%d, type=%s, active=%d, product=%d, start=%s, end=%s]\n",
        $ad->get('name'),
        $ad->get('id'),
        $type,
        $active,
        $product,
        $start,
        $end
    );
}
