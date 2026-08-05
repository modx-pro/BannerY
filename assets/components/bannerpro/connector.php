<?php
/**
 * BannerPro JSON connector (standalone dispatcher)
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

bannerpro_require_mgr_auth($modx);

$action = (string) bannerpro_post('action', '');
$handlers = bannerpro_action_handlers();

if ($action === '' || !isset($handlers[$action])) {
    bannerpro_json_error('invalid action', 400);
}

[$handler, $perm] = $handlers[$action];
if ($perm !== null) {
    bannerpro_require_permission($modx, $perm);
}
$handler($modx);
