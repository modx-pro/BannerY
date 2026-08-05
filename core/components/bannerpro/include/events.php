<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array<string, mixed>
 */
function bannerpro_event_payload(modX $modx, int $adId, int $positionId, int $adpositionId): array
{
    /** @var byAd|null $ad */
    $ad = $modx->getObject('byAd', $adId);
    /** @var byPosition|null $position */
    $position = $positionId > 0 ? $modx->getObject('byPosition', $positionId) : null;

    return [
        'ad' => $ad,
        'ad_id' => $adId,
        'ad_name' => $ad ? (string) $ad->get('name') : '',
        'ad_url' => $ad ? (string) $ad->get('url') : '',
        'ad_type' => $ad ? (string) ($ad->get('type') ?? 'image') : 'image',
        'product_id' => $ad ? (int) ($ad->get('product_id') ?? 0) : 0,
        'position' => $positionId,
        'position_name' => $position ? (string) $position->get('name') : '',
        'adposition' => $adpositionId,
    ];
}

/**
 * @param array<string, mixed> $properties
 */
function bannerpro_invoke_click_event(modX $modx, array $properties): void
{
    $modx->invokeEvent('OnBannerProClick', $properties);
}

/**
 * @param array<string, mixed> $properties
 */
function bannerpro_invoke_impression_event(modX $modx, array $properties): void
{
    $modx->invokeEvent('OnBannerProImpression', $properties);
}

function bannerpro_webhook_url_is_allowed(string $url): bool
{
    if ($url === '' || !preg_match('#^https?://#i', $url)) {
        return false;
    }

    $host = parse_url($url, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
        return false;
    }

    $hostLower = strtolower($host);
    $blockedHosts = ['localhost', '127.0.0.1', '0.0.0.0', '[::1]', '::1'];
    if (in_array($hostLower, $blockedHosts, true)) {
        return false;
    }

    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    return true;
}

/**
 * @return array<string, mixed>
 */
function bannerpro_click_webhook_payload(
    int $adId,
    int $positionId,
    int $adpositionId,
    string $referrer,
    string $ip,
    int $clickId,
    string $redirectUrl,
    bool $recorded,
    bool $duplicate
): array {
    return [
        'event' => 'click',
        'ad_id' => $adId,
        'position_id' => $positionId,
        'adposition' => $adpositionId,
        'referrer' => $referrer,
        'ip' => $ip,
        'click_id' => $clickId,
        'timestamp' => (new DateTimeImmutable())->format(DATE_ATOM),
        'redirect_url' => $redirectUrl,
        'recorded' => $recorded,
        'duplicate' => $duplicate,
    ];
}

/**
 * @return array<string, mixed>
 */
function bannerpro_impression_webhook_payload(
    int $adId,
    int $positionId,
    int $adpositionId,
    string $ip,
    bool $recorded,
    bool $duplicate
): array {
    return [
        'event' => 'impression',
        'ad_id' => $adId,
        'position_id' => $positionId,
        'adposition' => $adpositionId,
        'ip' => $ip,
        'timestamp' => (new DateTimeImmutable())->format(DATE_ATOM),
        'recorded' => $recorded,
        'duplicate' => $duplicate,
    ];
}

/**
 * @param array<string, mixed> $payload
 */
function bannerpro_dispatch_webhook(modX $modx, array $payload, string $urlSettingKey = 'bannerpro_webhook_url'): void
{
    $url = trim((string) $modx->getOption($urlSettingKey, null, ''));
    if (!bannerpro_webhook_url_is_allowed($url)) {
        return;
    }

    $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($body === false) {
        return;
    }

    $headers = [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($body),
    ];
    $secret = trim((string) $modx->getOption('bannerpro_webhook_secret', null, ''));
    if ($secret !== '') {
        $headers[] = 'X-BannerPro-Signature: ' . hash_hmac('sha256', $body, $secret);
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            $modx->log(modX::LOG_LEVEL_WARN, '[bannerpro] Webhook: curl_init failed');

            return;
        }
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_CONNECTTIMEOUT => 2,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($response === false || ($httpCode >= 400 && $httpCode !== 0)) {
            $modx->log(
                modX::LOG_LEVEL_WARN,
                '[bannerpro] Webhook failed: HTTP ' . $httpCode . ($error !== '' ? ' ' . $error : '')
            );
        }

        return;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => $body,
            'timeout' => 2,
            'ignore_errors' => true,
        ],
    ]);
    $result = @file_get_contents($url, false, $context);
    if ($result === false) {
        $modx->log(modX::LOG_LEVEL_WARN, '[bannerpro] Webhook failed: file_get_contents error');
    }
}

function bannerpro_analytics_enabled(modX $modx): bool
{
    return (bool) (int) $modx->getOption('bannerpro_analytics_enabled', null, 0);
}

function bannerpro_register_analytics_assets(modX $modx): void
{
    static $registered = false;
    if ($registered || !bannerpro_analytics_enabled($modx)) {
        return;
    }
    if ($modx->context->get('key') !== 'web') {
        return;
    }

    $clickSegment = trim((string) $modx->getOption('bannerpro_click', null, 'bannerclick', true), '/');
    $assetsUrl = rtrim((string) $modx->getOption('assets_url', null, ''), '/') . '/';
    $config = [
        'clickSegment' => $clickSegment,
        'ga4' => (bool) (int) $modx->getOption('bannerpro_analytics_ga4', null, 1),
        'ga4ClickEvent' => (string) $modx->getOption('bannerpro_analytics_ga4_click', null, 'bannerpro_click'),
        'ga4ImpressionEvent' => (string) $modx->getOption('bannerpro_analytics_ga4_impression', null, 'bannerpro_impression'),
        'matomo' => (bool) (int) $modx->getOption('bannerpro_analytics_matomo', null, 1),
        'ymCounter' => (int) $modx->getOption('bannerpro_analytics_ym_counter', null, 0),
        'ymClickGoal' => (string) $modx->getOption('bannerpro_analytics_ym_click_goal', null, 'bannerpro_click'),
        'ymImpressionGoal' => (string) $modx->getOption('bannerpro_analytics_ym_impression_goal', null, 'bannerpro_impression'),
    ];

    $modx->regClientStartupHTMLBlock(
        '<script>window.bannerproAnalyticsConfig=' . json_encode($config, JSON_UNESCAPED_UNICODE) . ';</script>'
    );
    $modx->regClientScript($assetsUrl . 'components/bannerpro/js/analytics.js');
    $registered = true;
}
