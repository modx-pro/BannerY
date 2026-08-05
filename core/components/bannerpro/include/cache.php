<?php

defined('MODX_CORE_PATH') || exit;

use xPDO\xPDO;

/**
 * @param array<string, mixed> $scriptProperties
 */
function bannerpro_resolve_sortby(string $class, array $scriptProperties): string
{
    $sortby = $scriptProperties['sortby'] ?? '';
    if ($sortby === '') {
        return 'RAND()';
    }
    $sortbyUpper = strtoupper(trim($sortby));
    if ($sortbyUpper === 'RAND()' || $sortbyUpper === 'RAND') {
        return 'RAND()';
    }
    if ($sortby === 'idx' || $sortby === 'index') {
        return 'byAdPosition.idx';
    }
    if ($sortby === 'weighted') {
        return 'RAND() * byAdPosition.weight';
    }
    if ($sortby === 'ab') {
        return 'byAdPosition.idx';
    }

    return $class . '.' . $sortby;
}

/**
 * @param array<string, mixed> $props
 */
function bannerpro_cache_enabled(modX $modx, array $props): bool
{
    if (array_key_exists('cache', $props)) {
        return (bool) (int) $props['cache'];
    }

    return (bool) (int) $modx->getOption('bannerpro_cache', null, true);
}

/**
 * @param array<string, mixed> $props
 */
function bannerpro_cache_lifetime(modX $modx, array $props): int
{
    if (isset($props['cacheLifetime']) && $props['cacheLifetime'] !== '') {
        return max(0, (int) $props['cacheLifetime']);
    }

    return max(0, (int) $modx->getOption('bannerpro_cache_lifetime', null, 3600));
}

/**
 * @param array<string, mixed> $props
 */
function bannerpro_cache_is_eligible(modX $modx, array $props, string $sortbyRaw): bool
{
    if (!bannerpro_cache_enabled($modx, $props)) {
        return false;
    }
    if (!empty($props['toSeparatePlaceholders'])) {
        return false;
    }
    if (!empty($props['showLog']) && $modx->user->hasSessionContext('mgr')) {
        return false;
    }

    $raw = strtoupper(trim($sortbyRaw));
    if ($raw === '' || str_contains($raw, 'RAND()') || $raw === 'WEIGHTED' || $raw === 'AB') {
        return false;
    }

    return true;
}

/**
 * @param array<string, mixed> $props
 */
function bannerpro_cache_key(modX $modx, array $props, string $sortby): string
{
    $propsForKey = $props;
    unset($propsForKey['cache'], $propsForKey['cacheLifetime'], $propsForKey['showLog']);
    ksort($propsForKey);

    $payload = [
        'activeContext' => bannerpro_resolve_snippet_context($modx, $props),
        'culture' => $modx->getOption('cultureKey', null, 'en'),
        'sortby' => $sortby,
        'bucket' => bannerpro_site_now($modx)->format('Y-m-d-H'),
        'resource' => (int) ($props['resource'] ?? 0),
        'parent' => (int) ($props['parent'] ?? 0),
        'tags' => (string) ($props['tags'] ?? ''),
        'tagsMode' => (string) ($props['tagsMode'] ?? 'any'),
        'props' => $propsForKey,
    ];

    return 'snippet/' . sha1(json_encode($payload, JSON_UNESCAPED_UNICODE));
}

/**
 * @return array<string, mixed>
 */
function bannerpro_cache_options(): array
{
    return [xPDO::OPT_CACHE_KEY => 'bannerpro'];
}

function bannerpro_cache_get(modX $modx, string $key): ?string
{
    $value = $modx->cacheManager->get($key, bannerpro_cache_options());
    if ($value === false || $value === null) {
        return null;
    }

    return (string) $value;
}

function bannerpro_cache_set(modX $modx, string $key, string $html, int $lifetime): void
{
    if ($lifetime <= 0) {
        return;
    }

    $modx->cacheManager->set($key, $html, $lifetime, bannerpro_cache_options());
}

function bannerpro_cache_invalidate(modX $modx): void
{
    $modx->cacheManager->clean(bannerpro_cache_options());
}
