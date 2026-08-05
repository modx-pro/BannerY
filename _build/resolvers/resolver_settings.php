<?php

/**
 * Resolver: миграция namespace/настроек/элементов и очистка legacy-файлов BannerPro.
 *
 * @package bannerpro
 */

use xPDO\Transport\xPDOTransport;

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

$corePath = '{core_path}components/bannerpro/';
$assetsPath = '{assets_path}components/bannerpro/';
$nsTable = $modx->getTableName('modNamespace');

if (!$modx->getObject('modNamespace', ['name' => 'bannerpro'])) {
    foreach (['bannery2', 'bannery'] as $legacyNs) {
        if ($modx->getObject('modNamespace', ['name' => $legacyNs])) {
            $modx->exec(
                'UPDATE ' . $nsTable
                . ' SET name=' . $modx->quote('bannerpro')
                . ', path=' . $modx->quote($corePath)
                . ', assets_path=' . $modx->quote($assetsPath)
                . ' WHERE name=' . $modx->quote($legacyNs)
            );
            break;
        }
    }
}

$ns = $modx->getObject('modNamespace', ['name' => 'bannerpro']);
if ($ns) {
    $ns->set('path', $corePath);
    $ns->set('assets_path', $assetsPath);
    $ns->save();
} else {
    $ns = $modx->newObject('modNamespace');
    $ns->fromArray([
        'name' => 'bannerpro',
        'path' => $corePath,
        'assets_path' => $assetsPath,
    ], '', true, true);
    if (!$ns->save()) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[bannerpro] Could not create namespace bannerpro.');
    }
}

foreach (['bannery2', 'bannery'] as $legacyNs) {
    if ($legacyNs !== 'bannerpro' && $modx->getObject('modNamespace', ['name' => $legacyNs])) {
        $modx->exec('DELETE FROM ' . $nsTable . ' WHERE name=' . $modx->quote($legacyNs));
    }
}

foreach ($modx->getIterator('modMenu', ['namespace:IN' => ['bannery', 'bannery2', 'bannerpro']]) as $menu) {
    $menu->set('namespace', 'bannerpro');
    if ($menu->get('action') === 'home') {
        $menu->set('action', 'index');
    }
    $menu->save();
}

$settingMap = [
    'bannery_click' => 'bannerpro_click',
    'bannery_media_source' => 'bannerpro_media_source',
    'bannery2_click' => 'bannerpro_click',
    'bannery2_media_source' => 'bannerpro_media_source',
    'bannery2_cache' => 'bannerpro_cache',
    'bannery2_cache_lifetime' => 'bannerpro_cache_lifetime',
    'bannery2_track_impressions' => 'bannerpro_track_impressions',
    'bannery2_impression' => 'bannerpro_impression',
    'bannery2_clicks_retention_days' => 'bannerpro_clicks_retention_days',
    'bannery2_impressions_retention_days' => 'bannerpro_impressions_retention_days',
    'bannery2_analytics_enabled' => 'bannerpro_analytics_enabled',
    'bannery2_analytics_ga4' => 'bannerpro_analytics_ga4',
    'bannery2_analytics_ga4_click' => 'bannerpro_analytics_ga4_click',
    'bannery2_analytics_ga4_impression' => 'bannerpro_analytics_ga4_impression',
    'bannery2_analytics_matomo' => 'bannerpro_analytics_matomo',
    'bannery2_analytics_ym_counter' => 'bannerpro_analytics_ym_counter',
    'bannery2_analytics_ym_click_goal' => 'bannerpro_analytics_ym_click_goal',
    'bannery2_analytics_ym_impression_goal' => 'bannerpro_analytics_ym_impression_goal',
];

$settingsTable = $modx->getTableName('modSystemSetting');

foreach ($settingMap as $oldKey => $newKey) {
    if ($oldKey === $newKey) {
        continue;
    }
    if (!$modx->getObject('modSystemSetting', ['key' => $oldKey])) {
        continue;
    }
    if ($modx->getObject('modSystemSetting', ['key' => $newKey])) {
        $modx->exec('DELETE FROM ' . $settingsTable . ' WHERE `key`=' . $modx->quote($oldKey));
        continue;
    }
    $modx->exec(
        'UPDATE ' . $settingsTable
        . ' SET `key`=' . $modx->quote($newKey)
        . ', namespace=' . $modx->quote('bannerpro')
        . ', area=' . $modx->quote('bannerpro')
        . ' WHERE `key`=' . $modx->quote($oldKey)
    );
}

$modx->exec(
    'UPDATE ' . $settingsTable
    . ' SET namespace=' . $modx->quote('bannerpro')
    . ', area=' . $modx->quote('bannerpro')
    . ' WHERE namespace IN (' . $modx->quote('bannery') . ',' . $modx->quote('bannery2') . ')'
);

$definitions = [
    ['key' => 'bannerpro_click', 'value' => 'bannerclick', 'xtype' => 'textfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_click', 'description' => 'setting_bannerpro_click_desc'],
    ['key' => 'bannerpro_media_source', 'value' => '', 'xtype' => 'numberfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_media_source', 'description' => 'setting_bannerpro_media_source_desc'],
    ['key' => 'bannerpro_cache', 'value' => '1', 'xtype' => 'combo-boolean', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_cache', 'description' => 'setting_bannerpro_cache_desc'],
    ['key' => 'bannerpro_cache_lifetime', 'value' => '3600', 'xtype' => 'numberfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_cache_lifetime', 'description' => 'setting_bannerpro_cache_lifetime_desc'],
    ['key' => 'bannerpro_track_impressions', 'value' => '0', 'xtype' => 'combo-boolean', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_track_impressions', 'description' => 'setting_bannerpro_track_impressions_desc'],
    ['key' => 'bannerpro_impression', 'value' => 'bannerimpression', 'xtype' => 'textfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_impression', 'description' => 'setting_bannerpro_impression_desc'],
    ['key' => 'bannerpro_clicks_retention_days', 'value' => '365', 'xtype' => 'numberfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_clicks_retention_days', 'description' => 'setting_bannerpro_clicks_retention_days_desc'],
    ['key' => 'bannerpro_impressions_retention_days', 'value' => '365', 'xtype' => 'numberfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_impressions_retention_days', 'description' => 'setting_bannerpro_impressions_retention_days_desc'],
    ['key' => 'bannerpro_analytics_enabled', 'value' => '0', 'xtype' => 'combo-boolean', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_analytics_enabled', 'description' => 'setting_bannerpro_analytics_enabled_desc'],
    ['key' => 'bannerpro_analytics_ga4', 'value' => '1', 'xtype' => 'combo-boolean', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_analytics_ga4', 'description' => 'setting_bannerpro_analytics_ga4_desc'],
    ['key' => 'bannerpro_analytics_ga4_click', 'value' => 'bannerpro_click', 'xtype' => 'textfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_analytics_ga4_click', 'description' => 'setting_bannerpro_analytics_ga4_click_desc'],
    ['key' => 'bannerpro_analytics_ga4_impression', 'value' => 'bannerpro_impression', 'xtype' => 'textfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_analytics_ga4_impression', 'description' => 'setting_bannerpro_analytics_ga4_impression_desc'],
    ['key' => 'bannerpro_analytics_matomo', 'value' => '1', 'xtype' => 'combo-boolean', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_analytics_matomo', 'description' => 'setting_bannerpro_analytics_matomo_desc'],
    ['key' => 'bannerpro_analytics_ym_counter', 'value' => '', 'xtype' => 'textfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_analytics_ym_counter', 'description' => 'setting_bannerpro_analytics_ym_counter_desc'],
    ['key' => 'bannerpro_analytics_ym_click_goal', 'value' => 'bannerpro_click', 'xtype' => 'textfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_analytics_ym_click_goal', 'description' => 'setting_bannerpro_analytics_ym_click_goal_desc'],
    ['key' => 'bannerpro_analytics_ym_impression_goal', 'value' => 'bannerpro_impression', 'xtype' => 'textfield', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_analytics_ym_impression_goal', 'description' => 'setting_bannerpro_analytics_ym_impression_goal_desc'],
    ['key' => 'bannerpro_filter_bots', 'value' => '0', 'xtype' => 'combo-boolean', 'namespace' => 'bannerpro', 'area' => 'bannerpro', 'name' => 'setting_bannerpro_filter_bots', 'description' => 'setting_bannerpro_filter_bots_desc'],
];
foreach ($definitions as $def) {
    if (!$modx->getObject('modSystemSetting', ['key' => $def['key']])) {
        $obj = $modx->newObject('modSystemSetting');
        $obj->fromArray($def, '', true, true);
        if (!$obj->save()) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[bannerpro] Could not create setting: ' . $def['key']);
        }
    }
}

$elementRenames = [
    'modSnippet' => ['BannerY2' => 'BannerPro'],
    'modPlugin' => [
        'BannerY2Clickout' => 'BannerProClickout',
        'BannerY2Impression' => 'BannerProImpression',
        'BannerY2Analytics' => 'BannerProAnalytics',
    ],
];
foreach ($elementRenames as $class => $map) {
    foreach ($map as $oldName => $newName) {
        $obj = $modx->getObject($class, ['name' => $oldName]);
        if (!$obj) {
            continue;
        }
        if ($modx->getObject($class, ['name' => $newName]) && (int) $obj->get('id') !== (int) $modx->getObject($class, ['name' => $newName])->get('id')) {
            $obj->remove();
            continue;
        }
        $obj->set('name', $newName);
        $obj->save();
    }
}

if ($options[xPDOTransport::PACKAGE_ACTION] === xPDOTransport::ACTION_UPGRADE) {
    $legacyFiles = [
        MODX_ASSETS_PATH . 'components/bannery/css/mgr/font-awesome.min.css',
        MODX_ASSETS_PATH . 'components/bannery/js/mgr/bannery.js',
        MODX_ASSETS_PATH . 'components/bannery2/js/mgr/bannery.js',
        MODX_ASSETS_PATH . 'components/bannerpro/js/mgr/bannery.js',
        MODX_CORE_PATH . 'components/bannery/index.class.php',
        MODX_CORE_PATH . 'components/bannery/controllers/home.class.php',
        MODX_CORE_PATH . 'components/bannery/model/bannery/bannery.class.php',
        MODX_CORE_PATH . 'components/bannery2/controllers/home.class.php',
        MODX_CORE_PATH . 'components/bannery2/model/bannery/bannery.class.php',
        MODX_CORE_PATH . 'components/bannery2/model/bannery2/bannery.class.php',
        MODX_CORE_PATH . 'components/bannerpro/controllers/home.class.php',
        MODX_CORE_PATH . 'components/bannerpro/model/bannery/bannery.class.php',
        MODX_CORE_PATH . 'components/bannerpro/model/bannerpro/bannery.class.php',
    ];
    foreach ($legacyFiles as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

return true;
