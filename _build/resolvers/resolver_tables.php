<?php

/**
 * Resolver: миграция и создание таблиц BannerPro при установке/обновлении.
 *
 * @package bannerpro
 */

use xPDO\Transport\xPDOTransport;
use xPDO\xPDO;

/** @var xPDOTransport $transport */
/** @var array $options */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return true;
}

$modx = $transport->xpdo;

if ($options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_INSTALL
    && $options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_UPGRADE
) {
    return true;
}

$modelPath = $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/bannerpro/model/';
$modx->addPackage('bannerpro', $modelPath);
$manager = $modx->getManager();

$tableRenames = [
    'bannery_ads' => 'bannerpro_ads',
    'bannery_positions' => 'bannerpro_positions',
    'bannery_ads_positions' => 'bannerpro_ads_positions',
    'bannery_clicks' => 'bannerpro_clicks',
    'bannery_impressions' => 'bannerpro_impressions',
];

$prefix = (string) $modx->getOption('table_prefix', null, '');
$tableExists = static function (modX $modx, string $table): bool {
    $stmt = $modx->query('SHOW TABLES LIKE ' . $modx->quote($table));
    if (!$stmt) {
        return false;
    }
    $row = $stmt->fetch(\PDO::FETCH_NUM);

    return $row !== false;
};

foreach ($tableRenames as $oldBase => $newBase) {
    $oldTable = $prefix . $oldBase;
    $newTable = $prefix . $newBase;
    if ($tableExists($modx, $oldTable) && !$tableExists($modx, $newTable)) {
        $modx->exec('RENAME TABLE `' . $oldTable . '` TO `' . $newTable . '`');
        $modx->log(modX::LOG_LEVEL_INFO, '[bannerpro] Renamed table ' . $oldTable . ' → ' . $newTable);
    }
}

foreach (['byAd', 'byPosition', 'byAdPosition', 'byClick', 'byImpression', 'byAdTemplate', 'byAudit'] as $class) {
    $manager->createObjectContainer($class);
}

$level = $modx->getLogLevel();
$modx->setLogLevel(xPDO::LOG_LEVEL_FATAL);
$manager->addField('byAd', 'source');
$manager->addField('byAd', 'type');
$manager->addField('byAd', 'html');
$manager->addField('byAd', 'start');
$manager->addField('byAd', 'end');
$manager->addField('byAd', 'product_id');
$manager->addField('byAd', 'category_id', ['after' => 'product_id']);
$manager->addField('byAd', 'max_clicks', ['after' => 'category_id']);
$manager->addField('byAd', 'max_impressions', ['after' => 'max_clicks']);
$manager->addField('byAd', 'show_hours', ['after' => 'max_impressions']);
$manager->addField('byAd', 'target_resource_id', ['after' => 'show_hours']);
$manager->addField('byAd', 'target_parent_id', ['after' => 'target_resource_id']);
$manager->addField('byAd', 'tags', ['after' => 'target_parent_id']);
$manager->addField('byAdPosition', 'weight', ['after' => 'idx']);
$manager->addField('byClick', 'order_id', ['after' => 'ip']);
$manager->addField('byPosition', 'context_key', ['after' => 'name']);
$manager->addIndex('byAd', 'active');
$manager->addIndex('byAd', 'start');
$manager->addIndex('byAd', 'end');
$manager->addIndex('byAd', 'product_id');
$manager->addIndex('byAd', 'category_id');
$manager->addIndex('byAd', 'target_resource_id');
$manager->addIndex('byAd', 'target_parent_id');
$manager->addIndex('byPosition', 'name');
$manager->addIndex('byAdPosition', 'ad');
$manager->addIndex('byAdPosition', 'position');
$manager->addIndex('byImpression', 'ad');
$manager->addIndex('byImpression', 'position');
$manager->addIndex('byImpression', 'impressiondate');
$modx->setLogLevel($level);

$templateTable = $prefix . 'bannerpro_ad_templates';
if (!$tableExists($modx, $templateTable)) {
    $modx->log(modX::LOG_LEVEL_WARN, '[bannerpro] Table bannerpro_ad_templates missing — skip template seed');
} elseif ((int) $modx->getCount('byAdTemplate') === 0) {
    require_once dirname(__DIR__, 2) . '/include/ad_template_presets.php';
    $created = bannerpro_seed_ad_templates($modx);
    $modx->log(modX::LOG_LEVEL_INFO, '[bannerpro] Seeded ad templates: ' . $created);
}

return true;
