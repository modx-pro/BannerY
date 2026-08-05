<?php

return [
    'BannerPro' => [
        'file' => 'snippet.bannerpro',
        'description' => 'Вывод баннеров по позициям (требуется pdoTools).',
        'properties' => [
            'position' => ['type' => 'numberfield', 'value' => ''],
            'positions' => ['type' => 'textfield', 'value' => ''],
            'positionName' => ['type' => 'textfield', 'value' => ''],
            'positionKey' => ['type' => 'textfield', 'value' => ''],
            'context' => ['type' => 'textfield', 'value' => ''],
            'productId' => ['type' => 'numberfield', 'value' => ''],
            'showInactive' => ['type' => 'combo-boolean', 'value' => false],
            'sortby' => ['type' => 'textfield', 'value' => 'RAND()'],
            'tpl' => ['type' => 'textfield', 'value' => 'byAd'],
            'tplFirst' => ['type' => 'textfield', 'value' => ''],
            'tplLast' => ['type' => 'textfield', 'value' => ''],
            'tplOdd' => ['type' => 'textfield', 'value' => ''],
            'tplImage' => ['type' => 'textfield', 'value' => ''],
            'tplImageFirst' => ['type' => 'textfield', 'value' => ''],
            'tplImageLast' => ['type' => 'textfield', 'value' => ''],
            'tplImageOdd' => ['type' => 'textfield', 'value' => ''],
            'tplHtml' => ['type' => 'textfield', 'value' => ''],
            'tplHtmlFirst' => ['type' => 'textfield', 'value' => ''],
            'tplHtmlLast' => ['type' => 'textfield', 'value' => ''],
            'tplHtmlOdd' => ['type' => 'textfield', 'value' => ''],
            'cache' => ['type' => 'combo-boolean', 'value' => ''],
            'cacheLifetime' => ['type' => 'numberfield', 'value' => ''],
        ],
    ],
];
