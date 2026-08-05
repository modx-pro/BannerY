<?php

/**
 * CLI: очистка старых кликов и показов по настройкам retention.
 *
 * php core/components/bannerpro/cron/purge.php
 */

$root = dirname(__DIR__, 4);
if (!is_file($root . '/config.core.php')) {
    $root = dirname(__DIR__, 5);
}
require_once $root . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
if (!$modx->initialize('mgr')) {
    fwrite(STDERR, "MODX init failed\n");
    exit(1);
}

require_once MODX_CORE_PATH . 'components/bannerpro/include/helpers.php';

$removed = bannerpro_purge_old_stats($modx);
echo 'Removed clicks: ' . $removed['clicks'] . ', impressions: ' . $removed['impressions'] . PHP_EOL;
