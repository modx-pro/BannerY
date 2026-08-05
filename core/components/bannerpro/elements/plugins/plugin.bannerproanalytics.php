<?php

/**
 * Опциональный мост GA4 / Matomo / Яндекс Метрика (клиентская аналитика).
 * Включите плагин и настройку bannerpro_analytics_enabled.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

require_once MODX_CORE_PATH . 'components/bannerpro/include/paths.php';
require_once bannerpro_core_path($modx) . 'include/events.php';

$eventName = $modx->event->name;

// OnLoadWebDocument: до вставки regClientScript в </body> (OnWebPagePrerender — уже поздно).
if ($eventName === 'OnLoadWebDocument') {
    bannerpro_register_analytics_assets($modx);
}
