<?php

return [
    'BannerProClickout' => [
        'file' => 'plugin.bannerproclickout',
        'description' => 'Учёт кликов и редирект по URL баннера.',
        'events' => [
            'OnPageNotFound' => [],
        ],
    ],
    'BannerProImpression' => [
        'file' => 'plugin.bannerproimpression',
        'description' => 'Учёт показов баннеров через pixel-запрос.',
        'events' => [
            'OnPageNotFound' => [],
        ],
    ],
    'BannerProAnalytics' => [
        'file' => 'plugin.bannerproanalytics',
        'description' => 'Мост в GA4, Matomo и Яндекс Метрику (dataLayer / _paq / ym). Выключен по умолчанию.',
        'events' => [
            'OnLoadWebDocument' => [],
        ],
        'disabled' => true,
    ],
    'BannerProMiniShop3' => [
        'file' => 'plugin.bannerprominishop3',
        'description' => 'Интеграция с MiniShop3: баннеры в каталоге и атрибуция клик → заказ.',
        'events' => [
            'msOnProductPrepare' => [],
            'msOnProductsLoad' => [],
            'msOnSubmitOrder' => [],
        ],
        'disabled' => true,
    ],
];
