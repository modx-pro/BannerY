<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array<string, string|bool>
 */
function bannerpro_utm_settings_get(modX $modx): array
{
    return [
        'utm_enabled' => (bool) (int) $modx->getOption('bannerpro_utm_enabled', null, false),
        'utm_source' => trim((string) $modx->getOption('bannerpro_utm_source', null, 'bannerpro')),
        'utm_medium' => trim((string) $modx->getOption('bannerpro_utm_medium', null, 'banner')),
        'utm_campaign' => trim((string) $modx->getOption('bannerpro_utm_campaign', null, '')),
        'utm_content' => trim((string) $modx->getOption('bannerpro_utm_content', null, '{ad_id}-{adposition}')),
    ];
}

/**
 * @param array<string, mixed> $data
 * @return array{success: bool, message?: string, data?: array<string, string|bool>}
 */
function bannerpro_utm_settings_update(modX $modx, array $data): array
{
    $map = [
        'utm_enabled' => 'bannerpro_utm_enabled',
        'utm_source' => 'bannerpro_utm_source',
        'utm_medium' => 'bannerpro_utm_medium',
        'utm_campaign' => 'bannerpro_utm_campaign',
        'utm_content' => 'bannerpro_utm_content',
    ];

    foreach ($map as $field => $key) {
        if (!array_key_exists($field, $data)) {
            continue;
        }

        $raw = $data[$field];
        if ($field === 'utm_enabled') {
            $value = (bool) $raw ? '1' : '0';
        } else {
            $value = trim((string) $raw);
        }

        /** @var modSystemSetting|null $setting */
        $setting = $modx->getObject('modSystemSetting', ['key' => $key]);
        if (!$setting) {
            return [
                'success' => false,
                'message' => $modx->lexicon('bannerpro.settings.error.missing_key') . ': ' . $key,
            ];
        }

        $setting->set('value', $value);
        if (!$setting->save()) {
            return [
                'success' => false,
                'message' => $modx->lexicon('bannerpro.settings.error.save'),
            ];
        }
    }

    $modx->cacheManager->refresh();

    return [
        'success' => true,
        'data' => bannerpro_utm_settings_get($modx),
    ];
}

/**
 * @return array<string, string|int>
 */
function bannerpro_integration_settings_get(modX $modx): array
{
    return [
        'webhook_url' => trim((string) $modx->getOption('bannerpro_webhook_url', null, '')),
        'webhook_impression_url' => trim((string) $modx->getOption('bannerpro_webhook_impression_url', null, '')),
        'webhook_secret' => trim((string) $modx->getOption('bannerpro_webhook_secret', null, '')),
        'ab_ttl' => max(1, (int) $modx->getOption('bannerpro_ab_ttl', null, 30)),
    ];
}

/**
 * @param array<string, mixed> $data
 * @return array{success: bool, message?: string, data?: array<string, string|int>}
 */
function bannerpro_integration_settings_update(modX $modx, array $data): array
{
    $map = [
        'webhook_url' => 'bannerpro_webhook_url',
        'webhook_impression_url' => 'bannerpro_webhook_impression_url',
        'webhook_secret' => 'bannerpro_webhook_secret',
        'ab_ttl' => 'bannerpro_ab_ttl',
    ];

    foreach ($map as $field => $key) {
        if (!array_key_exists($field, $data)) {
            continue;
        }

        $raw = $data[$field];
        if ($field === 'ab_ttl') {
            $value = (string) max(1, (int) $raw);
        } else {
            $value = trim((string) $raw);
        }

        /** @var modSystemSetting|null $setting */
        $setting = $modx->getObject('modSystemSetting', ['key' => $key]);
        if (!$setting) {
            return [
                'success' => false,
                'message' => $modx->lexicon('bannerpro.settings.error.missing_key') . ': ' . $key,
            ];
        }

        $setting->set('value', $value);
        if (!$setting->save()) {
            return [
                'success' => false,
                'message' => $modx->lexicon('bannerpro.settings.error.save'),
            ];
        }
    }

    $modx->cacheManager->refresh();

    return [
        'success' => true,
        'data' => bannerpro_integration_settings_get($modx),
    ];
}
