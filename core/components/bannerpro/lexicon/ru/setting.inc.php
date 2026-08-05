<?php

$_lang['area_bannerpro'] = 'BannerPro';
$_lang['area_bannerpro_main'] = 'Основное';

$_lang['setting_bannerpro_click'] = 'Переменная для учета клика';
$_lang['setting_bannerpro_click_desc'] = 'Сегмент URL, по которому фиксируется клик по баннеру.';

$_lang['setting_bannerpro_media_source'] = 'Источник файлов';
$_lang['setting_bannerpro_media_source_desc'] = 'Источник файлов по умолчанию для изображений баннеров';

$_lang['setting_bannerpro_cache'] = 'Кэш вывода сниппета';
$_lang['setting_bannerpro_cache_desc'] = 'Кэшировать готовый HTML сниппета BannerPro (partition bannerpro, тег bannerpro). Не используется при sortby=RAND()/weighted и &cache=0.';

$_lang['setting_bannerpro_cache_lifetime'] = 'Время жизни кэша сниппета';
$_lang['setting_bannerpro_cache_lifetime_desc'] = 'TTL в секундах. Для отдельного вызова — параметр &cacheLifetime.';

$_lang['setting_bannerpro_track_impressions'] = 'Учёт показов';
$_lang['setting_bannerpro_track_impressions_desc'] = 'Оборачивать баннеры в data-bannerpro-impression и подключать impression.js на сайте.';
$_lang['setting_bannerpro_impression_lazy'] = 'Lazy-load impression.js';
$_lang['setting_bannerpro_impression_lazy_desc'] = 'Подгружать impression.js через IntersectionObserver / requestIdleCallback, не блокируя загрузку страницы.';

$_lang['setting_bannerpro_impression'] = 'Сегмент URL для показов';
$_lang['setting_bannerpro_impression_desc'] = 'Сегмент pixel-запроса (аналог bannerpro_click для кликов).';

$_lang['setting_bannerpro_clicks_retention_days'] = 'Хранение кликов (дней)';
$_lang['setting_bannerpro_clicks_retention_days_desc'] = 'Удалять клики старше N дней. 0 — не чистить.';

$_lang['setting_bannerpro_impressions_retention_days'] = 'Хранение показов (дней)';
$_lang['setting_bannerpro_impressions_retention_days_desc'] = 'Удалять показы старше N дней. 0 — не чистить.';

$_lang['setting_bannerpro_analytics_enabled'] = 'Внешняя аналитика (клиент)';
$_lang['setting_bannerpro_analytics_enabled_desc'] = 'Подключить analytics.js через плагин BannerProAnalytics (GA4 dataLayer, Matomo, Яндекс Метрика).';
$_lang['setting_bannerpro_analytics_ga4'] = 'GA4 (dataLayer)';
$_lang['setting_bannerpro_analytics_ga4_desc'] = 'Отправлять события bannerpro_click / bannerpro_impression в window.dataLayer.';
$_lang['setting_bannerpro_analytics_ga4_click'] = 'Имя события GA4: клик';
$_lang['setting_bannerpro_analytics_ga4_click_desc'] = 'Значение поля event в dataLayer при клике по баннеру.';
$_lang['setting_bannerpro_analytics_ga4_impression'] = 'Имя события GA4: показ';
$_lang['setting_bannerpro_analytics_ga4_impression_desc'] = 'Значение поля event в dataLayer при показе баннера.';
$_lang['setting_bannerpro_analytics_matomo'] = 'Matomo (_paq)';
$_lang['setting_bannerpro_analytics_matomo_desc'] = 'Отправлять trackEvent в Matomo/Piwik, если на странице есть _paq.';
$_lang['setting_bannerpro_analytics_ym_counter'] = 'Яндекс Метрика: ID счётчика';
$_lang['setting_bannerpro_analytics_ym_counter_desc'] = 'Числовой ID счётчика для ym(ID, reachGoal, …). Пусто — не отправлять.';
$_lang['setting_bannerpro_analytics_ym_click_goal'] = 'Яндекс Метрика: цель клика';
$_lang['setting_bannerpro_analytics_ym_click_goal_desc'] = 'Идентификатор цели reachGoal при клике.';
$_lang['setting_bannerpro_analytics_ym_impression_goal'] = 'Яндекс Метрика: цель показа';
$_lang['setting_bannerpro_analytics_ym_impression_goal_desc'] = 'Идентификатор цели reachGoal при показе.';
$_lang['setting_bannerpro_attribution_ttl'] = 'Атрибуция: срок cookie (дней)';
$_lang['setting_bannerpro_attribution_ttl_desc'] = 'Сколько дней хранить cookie bannerpro_click_id после клика по баннеру (для связи с заказом MS3).';
$_lang['setting_bannerpro_api_enabled'] = 'REST API включён';
$_lang['setting_bannerpro_api_enabled_desc'] = 'Разрешить read-only REST API по адресу assets/components/bannerpro/api.php.';
$_lang['setting_bannerpro_api_key'] = 'REST API ключ';
$_lang['setting_bannerpro_api_key_desc'] = 'Bearer-токен для REST API. Генерируется при установке, если пусто.';
$_lang['setting_bannerpro_utm_enabled'] = 'UTM при клике';
$_lang['setting_bannerpro_utm_enabled_desc'] = 'Добавлять utm_* к URL редиректа при клике по баннеру.';
$_lang['setting_bannerpro_utm_source'] = 'UTM source';
$_lang['setting_bannerpro_utm_source_desc'] = 'Значение utm_source (по умолчанию bannerpro).';
$_lang['setting_bannerpro_utm_medium'] = 'UTM medium';
$_lang['setting_bannerpro_utm_medium_desc'] = 'Значение utm_medium (по умолчанию banner).';
$_lang['setting_bannerpro_utm_campaign'] = 'UTM campaign';
$_lang['setting_bannerpro_utm_campaign_desc'] = 'utm_campaign. Пусто — не добавлять. Плейсхолдеры: {ad_id}, {position_id}, {adposition}, {product_id}, {product_pagetitle}, {order_id}.';
$_lang['setting_bannerpro_utm_content'] = 'UTM content (шаблон)';
$_lang['setting_bannerpro_utm_content_desc'] = 'Шаблон utm_content и utm_campaign: {ad_id}, {position_id}, {adposition}, {product_id}, {product_pagetitle}, {order_id}.';
$_lang['setting_bannerpro_ms_catalog_positions'] = 'Позиции каталога MS3';
$_lang['setting_bannerpro_ms_catalog_positions_desc'] = 'Имена позиций через запятую для msProducts (например catalog_row,product_card). Плагин заполняет bannerpro_positions на каждом товаре.';
$_lang['setting_bannerpro_webhook_url'] = 'Webhook URL (клик)';
$_lang['setting_bannerpro_webhook_url_desc'] = 'POST JSON при клике по баннеру. Пусто — выключено.';
$_lang['setting_bannerpro_webhook_secret'] = 'Webhook secret';
$_lang['setting_bannerpro_webhook_secret_desc'] = 'Секрет для заголовка X-BannerPro-Signature (HMAC-SHA256). Пусто — без подписи.';
$_lang['setting_bannerpro_webhook_impression_url'] = 'Webhook URL (показ)';
$_lang['setting_bannerpro_webhook_impression_url_desc'] = 'POST JSON при pixel-показе. Пусто — выключено. HMAC через bannerpro_webhook_secret.';
$_lang['setting_bannerpro_ab_ttl'] = 'A/B split: срок cookie (дней)';
$_lang['setting_bannerpro_ab_ttl_desc'] = 'Срок cookie bannerpro_ab_{position} при &sortby=ab (два баннера в слоте).';
$_lang['setting_bannerpro_filter_bots'] = 'Фильтр ботов';
$_lang['setting_bannerpro_filter_bots_desc'] = 'Не учитывать клики и показы ботов в статистике. Для точного определения установите дополнение CrawlerDetect.';
