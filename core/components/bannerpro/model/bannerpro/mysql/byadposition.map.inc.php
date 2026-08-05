<?php

$xpdo_meta_map['byAdPosition'] = array (
  'package' => 'bannerpro',
  'version' => '1.1',
  'table' => 'bannerpro_ads_positions',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' =>
  array (
    'engine' => 'InnoDB',
  ),
  'fields' =>
  array (
    'ad' => null,
    'position' => null,
    'idx' => 0,
    'weight' => 1,
  ),
  'fieldMeta' =>
  array (
    'ad' =>
    array (
      'dbtype' => 'integer',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'index' => 'index',
    ),
    'position' =>
    array (
      'dbtype' => 'integer',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'index' => 'index',
    ),
    'idx' =>
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'weight' =>
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 1,
    ),
  ),
  'indexes' =>
  array (
    'ad' =>
    array (
      'alias' => 'ad',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' =>
      array (
        'ad' =>
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'position' =>
    array (
      'alias' => 'position',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' =>
      array (
        'position' =>
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'aggregates' =>
  array (
    'Ad' =>
    array (
      'class' => 'byAd',
      'local' => 'ad',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Position' =>
    array (
      'class' => 'byPosition',
      'local' => 'position',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
