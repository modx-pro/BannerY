<?php
/**
 * BannerPro REST API (read-only)
 *
 * @package bannerpro
 */

if (file_exists(dirname(__FILE__, 4) . '/config.core.php')) {
    require_once dirname(__FILE__, 4) . '/config.core.php';
} else {
    require_once dirname(__FILE__, 5) . '/config.core.php';
}
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

require_once MODX_CORE_PATH . 'components/bannerpro/include/paths.php';
require_once bannerpro_core_path($modx) . 'include/helpers.php';

if (!(bool) (int) $modx->getOption('bannerpro_api_enabled', null, 0)) {
    bannerpro_json_error('api disabled', 503);
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
$apiKey = preg_replace('/^Bearer\s+/i', '', trim($authHeader));
$storedKey = (string) $modx->getOption('bannerpro_api_key', null, '');
if ($storedKey === '' || !hash_equals($storedKey, $apiKey)) {
    bannerpro_json_error('unauthorized', 401);
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
if ($basePath !== '' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath)) ?: '/';
}

bannerpro_rest_dispatch(
    $modx,
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $path,
    $_GET
);
