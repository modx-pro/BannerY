<?php

$xpdo_meta_map['byAudit'] = [
    'package' => 'bannerpro',
    'version' => '1.1',
    'table' => 'bannerpro_audit',
    'extends' => 'xPDOSimpleObject',
    'tableMeta' => [
        'engine' => 'InnoDB',
    ],
    'fields' => [
        'user' => 0,
        'username' => '',
        'action' => '',
        'entity' => '',
        'entity_id' => 0,
        'payload' => null,
        'created_at' => null,
    ],
    'fieldMeta' => [
        'user' => [
            'dbtype' => 'int',
            'precision' => '10',
            'phptype' => 'integer',
            'attributes' => 'unsigned',
            'null' => false,
            'default' => 0,
        ],
        'username' => [
            'dbtype' => 'varchar',
            'precision' => '255',
            'phptype' => 'string',
            'null' => false,
            'default' => '',
        ],
        'action' => [
            'dbtype' => 'varchar',
            'precision' => '64',
            'phptype' => 'string',
            'null' => false,
            'default' => '',
        ],
        'entity' => [
            'dbtype' => 'varchar',
            'precision' => '32',
            'phptype' => 'string',
            'null' => false,
            'default' => '',
        ],
        'entity_id' => [
            'dbtype' => 'int',
            'precision' => '10',
            'phptype' => 'integer',
            'attributes' => 'unsigned',
            'null' => false,
            'default' => 0,
        ],
        'payload' => [
            'dbtype' => 'text',
            'phptype' => 'string',
            'null' => true,
        ],
        'created_at' => [
            'dbtype' => 'datetime',
            'phptype' => 'datetime',
            'null' => true,
        ],
    ],
    'indexes' => [
        'entity' => [
            'alias' => 'entity',
            'primary' => false,
            'unique' => false,
            'type' => 'BTREE',
            'columns' => [
                'entity' => [
                    'length' => '',
                    'collation' => 'A',
                    'null' => false,
                ],
            ],
        ],
        'action' => [
            'alias' => 'action',
            'primary' => false,
            'unique' => false,
            'type' => 'BTREE',
            'columns' => [
                'action' => [
                    'length' => '',
                    'collation' => 'A',
                    'null' => false,
                ],
            ],
        ],
        'created_at' => [
            'alias' => 'created_at',
            'primary' => false,
            'unique' => false,
            'type' => 'BTREE',
            'columns' => [
                'created_at' => [
                    'length' => '',
                    'collation' => 'A',
                    'null' => false,
                ],
            ],
        ],
    ],
];
