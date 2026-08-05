<?php

$_lang['area_bannerpro'] = 'BannerPro';
$_lang['area_bannerpro_main'] = 'Main';

$_lang['setting_bannerpro_click'] = 'Banner click variable';
$_lang['setting_bannerpro_click_desc'] = 'URL segment that registers a banner click.';

$_lang['setting_bannerpro_media_source'] = 'Media source';
$_lang['setting_bannerpro_media_source_desc'] = 'Default media source for banners images';

$_lang['setting_bannerpro_cache'] = 'Snippet output cache';
$_lang['setting_bannerpro_cache_desc'] = 'Cache rendered HTML from the BannerPro snippet (partition bannerpro, tag bannerpro). Disabled for RAND()/weighted sort and &cache=0.';

$_lang['setting_bannerpro_cache_lifetime'] = 'Snippet cache lifetime';
$_lang['setting_bannerpro_cache_lifetime_desc'] = 'TTL in seconds for cached snippet output. Override per call with &cacheLifetime.';

$_lang['setting_bannerpro_track_impressions'] = 'Track impressions';
$_lang['setting_bannerpro_track_impressions_desc'] = 'Wrap banners with data-bannerpro-impression and load impression.js on the frontend.';
$_lang['setting_bannerpro_impression_lazy'] = 'Lazy-load impression.js';
$_lang['setting_bannerpro_impression_lazy_desc'] = 'When enabled, load impression.js via IntersectionObserver / requestIdleCallback instead of blocking page load.';

$_lang['setting_bannerpro_impression'] = 'Impression pixel segment';
$_lang['setting_bannerpro_impression_desc'] = 'URL segment for impression tracking (like bannerpro_click for clicks).';

$_lang['setting_bannerpro_clicks_retention_days'] = 'Clicks retention (days)';
$_lang['setting_bannerpro_clicks_retention_days_desc'] = 'Delete click records older than N days. 0 = never purge.';

$_lang['setting_bannerpro_impressions_retention_days'] = 'Impressions retention (days)';
$_lang['setting_bannerpro_impressions_retention_days_desc'] = 'Delete impression records older than N days. 0 = never purge.';

$_lang['setting_bannerpro_analytics_enabled'] = 'External analytics (client)';
$_lang['setting_bannerpro_analytics_enabled_desc'] = 'Load analytics.js via BannerProAnalytics plugin (GA4 dataLayer, Matomo, Yandex Metrika).';
$_lang['setting_bannerpro_analytics_ga4'] = 'GA4 (dataLayer)';
$_lang['setting_bannerpro_analytics_ga4_desc'] = 'Push bannerpro_click / bannerpro_impression events to window.dataLayer.';
$_lang['setting_bannerpro_analytics_ga4_click'] = 'GA4 click event name';
$_lang['setting_bannerpro_analytics_ga4_click_desc'] = 'dataLayer event name on banner click.';
$_lang['setting_bannerpro_analytics_ga4_impression'] = 'GA4 impression event name';
$_lang['setting_bannerpro_analytics_ga4_impression_desc'] = 'dataLayer event name on banner impression.';
$_lang['setting_bannerpro_analytics_matomo'] = 'Matomo (_paq)';
$_lang['setting_bannerpro_analytics_matomo_desc'] = 'Send trackEvent when _paq is present on the page.';
$_lang['setting_bannerpro_analytics_ym_counter'] = 'Yandex Metrika counter ID';
$_lang['setting_bannerpro_analytics_ym_counter_desc'] = 'Numeric counter ID for ym(ID, reachGoal, …). Empty = disabled.';
$_lang['setting_bannerpro_analytics_ym_click_goal'] = 'Yandex Metrika click goal';
$_lang['setting_bannerpro_analytics_ym_click_goal_desc'] = 'reachGoal identifier on click.';
$_lang['setting_bannerpro_analytics_ym_impression_goal'] = 'Yandex Metrika impression goal';
$_lang['setting_bannerpro_analytics_ym_impression_goal_desc'] = 'reachGoal identifier on impression.';
$_lang['setting_bannerpro_attribution_ttl'] = 'Attribution cookie TTL (days)';
$_lang['setting_bannerpro_attribution_ttl_desc'] = 'How long to keep bannerpro_click_id cookie after a banner click (MS3 order attribution).';
$_lang['setting_bannerpro_api_enabled'] = 'REST API enabled';
$_lang['setting_bannerpro_api_enabled_desc'] = 'Allow read-only REST API at assets/components/bannerpro/api.php.';
$_lang['setting_bannerpro_api_key'] = 'REST API key';
$_lang['setting_bannerpro_api_key_desc'] = 'Bearer token for REST API. Auto-generated on install if empty.';
$_lang['setting_bannerpro_utm_enabled'] = 'UTM on click';
$_lang['setting_bannerpro_utm_enabled_desc'] = 'Append utm_* query params to banner click redirect URL.';
$_lang['setting_bannerpro_utm_source'] = 'UTM source';
$_lang['setting_bannerpro_utm_source_desc'] = 'utm_source value (default bannerpro).';
$_lang['setting_bannerpro_utm_medium'] = 'UTM medium';
$_lang['setting_bannerpro_utm_medium_desc'] = 'utm_medium value (default banner).';
$_lang['setting_bannerpro_utm_campaign'] = 'UTM campaign';
$_lang['setting_bannerpro_utm_campaign_desc'] = 'utm_campaign. Empty — omit. Placeholders: {ad_id}, {position_id}, {adposition}, {product_id}, {product_pagetitle}, {order_id}.';
$_lang['setting_bannerpro_utm_content'] = 'UTM content (template)';
$_lang['setting_bannerpro_utm_content_desc'] = 'utm_content and utm_campaign template: {ad_id}, {position_id}, {adposition}, {product_id}, {product_pagetitle}, {order_id}.';
$_lang['setting_bannerpro_ms_catalog_positions'] = 'MS3 catalog positions';
$_lang['setting_bannerpro_ms_catalog_positions_desc'] = 'Comma-separated position names for msProducts (e.g. catalog_row,product_card). Plugin fills bannerpro_positions on each product.';
$_lang['setting_bannerpro_webhook_url'] = 'Webhook URL (click)';
$_lang['setting_bannerpro_webhook_url_desc'] = 'POST JSON on banner click. Empty — disabled.';
$_lang['setting_bannerpro_webhook_secret'] = 'Webhook secret';
$_lang['setting_bannerpro_webhook_secret_desc'] = 'Secret for X-BannerPro-Signature header (HMAC-SHA256). Empty — no signature.';
$_lang['setting_bannerpro_webhook_impression_url'] = 'Webhook URL (impression)';
$_lang['setting_bannerpro_webhook_impression_url_desc'] = 'POST JSON on banner impression pixel. Empty — disabled. Uses bannerpro_webhook_secret for HMAC.';
$_lang['setting_bannerpro_ab_ttl'] = 'A/B split cookie TTL (days)';
$_lang['setting_bannerpro_ab_ttl_desc'] = 'How long to keep bannerpro_ab_{position} cookie when &sortby=ab (two banners in slot).';
$_lang['setting_bannerpro_filter_bots'] = 'Filter bots';
$_lang['setting_bannerpro_filter_bots_desc'] = 'Exclude bot clicks and impressions from statistics. Install the CrawlerDetect extra for accurate detection.';
