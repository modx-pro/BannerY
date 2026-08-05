<?php

$xpdo_meta_map['byAdTemplate'] = [
    'package' => 'bannerpro',
    'version' => '1.1',
    'table' => 'bannerpro_ad_templates',
    'extends' => 'xPDOSimpleObject',
    'tableMeta' => [
        'engine' => 'InnoDB',
    ],
    'fields' => [
        'name' => '',
        'type' => 'image',
        'tpl' => '',
        'defaults' => null,
    ],
    'fieldMeta' => [
        'name' => [
            'dbtype' => 'varchar',
            'precision' => '255',
            'phptype' => 'string',
            'null' => false,
            'default' => '',
        ],
        'type' => [
            'dbtype' => 'varchar',
            'precision' => '16',
            'phptype' => 'string',
            'null' => false,
            'default' => 'image',
        ],
        'tpl' => [
            'dbtype' => 'varchar',
            'precision' => '255',
            'phptype' => 'string',
            'null' => false,
            'default' => '',
        ],
        'defaults' => [
            'dbtype' => 'text',
            'phptype' => 'string',
            'null' => true,
        ],
    ],
];
