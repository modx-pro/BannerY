<?php

$xpdo_meta_map['byImpression'] = array (
  'package' => 'bannerpro',
  'version' => '1.1',
  'table' => 'bannerpro_impressions',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' =>
  array (
    'engine' => 'InnoDB',
  ),
  'fields' =>
  array (
    'ad' => null,
    'position' => null,
    'adposition' => null,
    'impressiondate' => null,
    'ip' => null,
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
    'adposition' =>
    array (
      'dbtype' => 'integer',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'index' => 'index',
    ),
    'impressiondate' =>
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'ip' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '39',
      'phptype' => 'string',
      'null' => false,
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
    'AdPosition' =>
    array (
      'class' => 'byAdPosition',
      'local' => 'adposition',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
