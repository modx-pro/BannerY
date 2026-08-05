<?php

defined('MODX_CORE_PATH') || exit;

/**
 * Загрузка лексикона компонента с учётом языка менеджера (как в msRussianPost).
 */
function bannerpro_load_lexicon(modX $modx, string $topic = 'default'): void
{
    $modx->lexicon->load('bannerpro:' . $topic);
    $managerLang = $modx->getOption('manager_language', null, $modx->getOption('cultureKey', null, 'en'));
    if ($managerLang !== '') {
        $modx->lexicon->load($managerLang . ':bannerpro:' . $topic);
    }
}

/**
 * Ключи из lexicon/{lang}/default.inc.php (источник истины для Vue).
 *
 * @return list<string>
 */
function bannerpro_default_lexicon_keys(modX $modx): array
{
    require_once __DIR__ . '/paths.php';
    $path = bannerpro_core_path($modx) . 'lexicon/en/default.inc.php';
    if (!is_readable($path)) {
        return [];
    }

    $content = file_get_contents($path);
    if ($content === false || !preg_match_all('/\$_lang\[\'([^\']+)\'\]/', $content, $matches)) {
        return [];
    }

    $keys = array_values(array_unique($matches[1]));
    sort($keys);

    return $keys;
}

/**
 * Ключи лексикона, передаваемые в Vue через window.bannerproConfig.lexicon.
 *
 * @return list<string>
 */
function bannerpro_mgr_lexicon_keys(modX $modx): array
{
    $keys = bannerpro_default_lexicon_keys($modx);
    foreach (['yes', 'no', 'search'] as $coreKey) {
        $keys[] = $coreKey;
    }

    return array_values(array_unique($keys));
}

/**
 * @return list<string>
 */
function bannerpro_mgr_setting_lexicon_keys(): array
{
    return [
        'setting_bannerpro_utm_enabled',
        'setting_bannerpro_utm_enabled_desc',
        'setting_bannerpro_utm_source',
        'setting_bannerpro_utm_source_desc',
        'setting_bannerpro_utm_medium',
        'setting_bannerpro_utm_medium_desc',
        'setting_bannerpro_utm_campaign',
        'setting_bannerpro_utm_campaign_desc',
        'setting_bannerpro_utm_content',
        'setting_bannerpro_utm_content_desc',
    ];
}

/**
 * @return array<string, string>
 */
function bannerpro_build_mgr_lexicon(modX $modx): array
{
    bannerpro_load_lexicon($modx, 'default');
    bannerpro_load_lexicon($modx, 'setting');
    $lexicon = [];
    foreach (bannerpro_mgr_lexicon_keys($modx) as $key) {
        $lexicon[$key] = (string) $modx->lexicon($key);
    }
    foreach (bannerpro_mgr_setting_lexicon_keys() as $key) {
        $lexicon[$key] = (string) $modx->lexicon($key);
    }

    return $lexicon;
}
