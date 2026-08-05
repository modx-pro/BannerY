<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @param array<string, mixed> $row
 */
function bannerpro_resolve_image_url(modX $modx, array $row): string
{
    $image = (string) ($row['image'] ?? '');
    if ($image === '') {
        return '';
    }

    $defaultSource = (int) $modx->getOption(
        'bannerpro_media_source',
        null,
        $modx->getOption('default_media_source'),
        true
    );
    $sourceId = !empty($row['source']) ? (int) $row['source'] : $defaultSource;
    if ($sourceId <= 0) {
        return $image;
    }

    /** @var modMediaSource|null $source */
    $source = $modx->getObject('sources.modMediaSource', $sourceId);
    if (!$source) {
        return $image;
    }

    $source->initialize($modx->context->key);
    return (string) $source->getObjectUrl($image);
}

/**
 * @param array<string, mixed> $row
 */
function bannerpro_format_ad_url_display(modX $modx, array $row): string
{
    $url = (string) ($row['url'] ?? '');
    if (preg_match('/\[\[\~([0-9]{1,})\]\]$/', $url, $matches)) {
        $resource = $modx->getObject('modResource', (int) $matches[1]);
        if ($resource) {
            return '(' . $resource->get('id') . ') ' . $resource->get('pagetitle');
        }
    }
    return $url;
}
