<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array{data: list<array<string, mixed>>, total: int}
 */
function bannerpro_ad_templates_get_list(modX $modx): array
{
    $rows = [];
    foreach ($modx->getCollection('byAdTemplate', null, false) as $template) {
        $row = $template->toArray();
        $defaults = json_decode((string) ($row['defaults'] ?? ''), true);
        $row['defaults_data'] = is_array($defaults) ? $defaults : [];
        $rows[] = $row;
    }

    return ['data' => $rows, 'total' => count($rows)];
}

/**
 * @param list<int>|null $positions
 * @return array{success: bool, message?: string, data?: array<string, mixed>}
 */
function bannerpro_ads_create_from_template(modX $modx, int $templateId, ?array $positions): array
{
    /** @var byAdTemplate|null $template */
    $template = $modx->getObject('byAdTemplate', $templateId);
    if (!$template) {
        return ['success' => false, 'message' => $modx->lexicon('bannerpro.templates.error.nf') ?: 'Template not found'];
    }

    $defaults = json_decode((string) $template->get('defaults'), true);
    if (!is_array($defaults)) {
        $defaults = [];
    }

    $fields = array_merge([
        'name' => (string) $template->get('name'),
        'type' => bannerpro_normalize_ad_type((string) $template->get('type')),
        'active' => 0,
        'description' => '',
        'url' => '',
        'image' => '',
        'html' => '',
        'product_id' => 0,
        'max_clicks' => 0,
        'max_impressions' => 0,
    ], $defaults);

    $fields['name'] = trim((string) ($fields['name'] ?? ''));
    if ($fields['name'] === '') {
        $fields['name'] = (string) $template->get('name');
    }
    $fields['type'] = bannerpro_normalize_ad_type((string) ($fields['type'] ?? $template->get('type')));

    $result = bannerpro_ads_create($modx, $fields, $positions ?? [], true);
    if (!$result['success']) {
        return $result;
    }

    $adId = (int) (($result['data']['id'] ?? 0));
    bannerpro_audit_log($modx, 'create_from_template', 'ad', $adId, [
        'template_id' => $templateId,
        'template_name' => (string) $template->get('name'),
        'name' => $fields['name'],
    ]);

    return bannerpro_ads_get($modx, $adId);
}
