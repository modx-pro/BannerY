<?php
$xpdo_meta_map['byAd']= array (
  'package' => 'bannery',
  'version' => '1.1',
  'table' => 'bannery_ads',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'name' => '',
    'url' => '',
    'image' => '',
    'source' => 1,
    'active' => 0,
    'description' => NULL,
    'start' => NULL,
    'end' => NULL,
  ),
  'fieldMeta' => 
  array (
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'url' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'image' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'source' => 
    array (
      'dbtype' => 'integer',
      'precision' => '10',
      'phptype' => 'integer',
      'attributes' => 'unsigned',
      'null' => true,
      'default' => 1,
    ),
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'description' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
    ),
    'start' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'timestamp',
      'null' => true,
    ),
    'end' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'timestamp',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'active' => 
    array (
      'alias' => 'active',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'active' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'start' => 
    array (
      'alias' => 'start',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'start' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'end' => 
    array (
      'alias' => 'end',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'end' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'composites' => 
  array (
    'Positions' => 
    array (
      'class' => 'byAdPosition',
      'local' => 'id',
      'foreign' => 'ad',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Clicks' => 
    array (
      'class' => 'byClick',
      'local' => 'id',
      'foreign' => 'ad',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
