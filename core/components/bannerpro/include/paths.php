<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return string Core path with trailing slash
 */
function bannerpro_core_path(modX $modx): string
{
    return $modx->getOption('bannerpro.core_path', null, $modx->getOption('core_path') . 'components/bannerpro/');
}

/**
 * @return string Assets URL with trailing slash
 */
function bannerpro_assets_url(modX $modx): string
{
    return $modx->getOption('bannerpro.assets_url', null, $modx->getOption('assets_url') . 'components/bannerpro/');
}

/**
 * Absolute assets base URL for manager UI (site_url + components/bannerpro/).
 */
function bannerpro_assets_base_url(modX $modx): string
{
    $assetsUrl = bannerpro_assets_url($modx);
    if (strpos($assetsUrl, 'http') !== 0) {
        $base = rtrim((string) $modx->getOption('site_url', null, ''), '/');
        $assetsUrl = $base . '/' . ltrim($assetsUrl, '/');
    }

    return rtrim($assetsUrl, '/') . '/';
}

function bannerpro_add_package(modX $modx): void
{
    $modx->addPackage('bannerpro', bannerpro_core_path($modx) . 'model/');
}

function bannerpro_bootstrap(): void
{
    // Module load only; MODX namespace bootstrap requires helpers.
}
