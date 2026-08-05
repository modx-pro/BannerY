<?php

$_lang['area_bannerpro'] = 'BannerPro';
$_lang['area_bannerpro_main'] = 'Основне';

$_lang['setting_bannerpro_click'] = 'Змінна для обліку кліку';
$_lang['setting_bannerpro_click_desc'] = 'Сегмент URL, за яким фіксується клік по банеру.';

$_lang['setting_bannerpro_media_source'] = 'Джерело файлів';
$_lang['setting_bannerpro_media_source_desc'] = 'Джерело файлів за замовчуванням для зображень банерів.';

$_lang['setting_bannerpro_cache'] = 'Кеш виводу сніпета';
$_lang['setting_bannerpro_cache_desc'] = 'Кешувати готовий HTML сніпета BannerPro (partition bannerpro, тег bannerpro). Не використовується при sortby=RAND()/weighted та &cache=0.';

$_lang['setting_bannerpro_cache_lifetime'] = 'Час життя кешу сніпета';
$_lang['setting_bannerpro_cache_lifetime_desc'] = 'TTL у секундах. Для окремого виклику — параметр &cacheLifetime.';

$_lang['setting_bannerpro_track_impressions'] = 'Облік показів';
$_lang['setting_bannerpro_track_impressions_desc'] = 'Обгортати банери в data-bannerpro-impression і підключати impression.js на сайті.';
$_lang['setting_bannerpro_impression_lazy'] = 'Lazy-load impression.js';
$_lang['setting_bannerpro_impression_lazy_desc'] = 'Підвантажувати impression.js через IntersectionObserver / requestIdleCallback, не блокуючи завантаження сторінки.';

$_lang['setting_bannerpro_impression'] = 'Сегмент URL для показів';
$_lang['setting_bannerpro_impression_desc'] = 'Сегмент pixel-запиту (аналог bannerpro_click для кліків).';

$_lang['setting_bannerpro_clicks_retention_days'] = 'Зберігання кліків (днів)';
$_lang['setting_bannerpro_clicks_retention_days_desc'] = 'Видаляти кліки старші за N днів. 0 — не очищати.';

$_lang['setting_bannerpro_impressions_retention_days'] = 'Зберігання показів (днів)';
$_lang['setting_bannerpro_impressions_retention_days_desc'] = 'Видаляти покази старші за N днів. 0 — не очищати.';

$_lang['setting_bannerpro_analytics_enabled'] = 'Зовнішня аналітика (клієнт)';
$_lang['setting_bannerpro_analytics_enabled_desc'] = 'Підключити analytics.js через плагін BannerProAnalytics (GA4 dataLayer, Matomo, Яндекс Метрика).';
$_lang['setting_bannerpro_analytics_ga4'] = 'GA4 (dataLayer)';
$_lang['setting_bannerpro_analytics_ga4_desc'] = 'Надсилати події bannerpro_click / bannerpro_impression у window.dataLayer.';
$_lang['setting_bannerpro_analytics_ga4_click'] = 'Ім\'я події GA4: клік';
$_lang['setting_bannerpro_analytics_ga4_click_desc'] = 'Значення поля event у dataLayer при кліку по банеру.';
$_lang['setting_bannerpro_analytics_ga4_impression'] = 'Ім\'я події GA4: показ';
$_lang['setting_bannerpro_analytics_ga4_impression_desc'] = 'Значення поля event у dataLayer при показі банера.';
$_lang['setting_bannerpro_analytics_matomo'] = 'Matomo (_paq)';
$_lang['setting_bannerpro_analytics_matomo_desc'] = 'Надсилати trackEvent у Matomo/Piwik, якщо на сторінці є _paq.';
$_lang['setting_bannerpro_analytics_ym_counter'] = 'Яндекс Метрика: ID лічильника';
$_lang['setting_bannerpro_analytics_ym_counter_desc'] = 'Числовий ID лічильника для ym(ID, reachGoal, …). Порожньо — не надсилати.';
$_lang['setting_bannerpro_analytics_ym_click_goal'] = 'Яндекс Метрика: ціль кліку';
$_lang['setting_bannerpro_analytics_ym_click_goal_desc'] = 'Ідентифікатор цілі reachGoal при кліку.';
$_lang['setting_bannerpro_analytics_ym_impression_goal'] = 'Яндекс Метрика: ціль показу';
$_lang['setting_bannerpro_analytics_ym_impression_goal_desc'] = 'Ідентифікатор цілі reachGoal при показі.';
$_lang['setting_bannerpro_attribution_ttl'] = 'Атрибуція: термін cookie (днів)';
$_lang['setting_bannerpro_attribution_ttl_desc'] = 'Скільки днів зберігати cookie bannerpro_click_id після кліку по банеру (для зв\'язку з замовленням MS3).';
$_lang['setting_bannerpro_api_enabled'] = 'REST API увімкнено';
$_lang['setting_bannerpro_api_enabled_desc'] = 'Дозволити read-only REST API за адресою assets/components/bannerpro/api.php.';
$_lang['setting_bannerpro_api_key'] = 'Ключ REST API';
$_lang['setting_bannerpro_api_key_desc'] = 'Bearer-токен для REST API. Генерується при встановленні, якщо порожньо.';
$_lang['setting_bannerpro_utm_enabled'] = 'UTM при кліку';
$_lang['setting_bannerpro_utm_enabled_desc'] = 'Додавати utm_* до URL редиректу при кліку по банеру.';
$_lang['setting_bannerpro_utm_source'] = 'UTM source';
$_lang['setting_bannerpro_utm_source_desc'] = 'Значення utm_source (за замовчуванням bannerpro).';
$_lang['setting_bannerpro_utm_medium'] = 'UTM medium';
$_lang['setting_bannerpro_utm_medium_desc'] = 'Значення utm_medium (за замовчуванням banner).';
$_lang['setting_bannerpro_utm_campaign'] = 'UTM campaign';
$_lang['setting_bannerpro_utm_campaign_desc'] = 'utm_campaign. Порожньо — не додавати.';
$_lang['setting_bannerpro_utm_content'] = 'UTM content (шаблон)';
$_lang['setting_bannerpro_utm_content_desc'] = 'Шаблон utm_content та utm_campaign: {ad_id}, {position_id}, {adposition}, {product_id}, {product_pagetitle}, {order_id}.';
$_lang['setting_bannerpro_ms_catalog_positions'] = 'Позиції каталогу MS3';
$_lang['setting_bannerpro_ms_catalog_positions_desc'] = 'Імена позицій через кому для msProducts (наприклад catalog_row,product_card). Плагін заповнює bannerpro_positions на кожному товарі.';
$_lang['setting_bannerpro_webhook_url'] = 'Webhook URL (клік)';
$_lang['setting_bannerpro_webhook_url_desc'] = 'POST JSON при кліку по банеру. Порожньо — вимкнено.';
$_lang['setting_bannerpro_webhook_secret'] = 'Webhook secret';
$_lang['setting_bannerpro_webhook_secret_desc'] = 'Секрет для заголовка X-BannerPro-Signature (HMAC-SHA256). Порожньо — без підпису.';
$_lang['setting_bannerpro_webhook_impression_url'] = 'Webhook URL (показ)';
$_lang['setting_bannerpro_webhook_impression_url_desc'] = 'POST JSON при pixel-показі. Порожньо — вимкнено. HMAC через bannerpro_webhook_secret.';
$_lang['setting_bannerpro_ab_ttl'] = 'A/B split: термін cookie (днів)';
$_lang['setting_bannerpro_ab_ttl_desc'] = 'Термін cookie bannerpro_ab_{position} при &sortby=ab (два банери в слоті).';
$_lang['setting_bannerpro_filter_bots'] = 'Фільтр ботів';
$_lang['setting_bannerpro_filter_bots_desc'] = 'Не враховувати кліки та покази ботів у статистиці. Для точного визначення встановіть доповнення CrawlerDetect.';
