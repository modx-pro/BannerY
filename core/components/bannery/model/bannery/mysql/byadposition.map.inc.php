<?php
$xpdo_meta_map['byAdPosition']= array (
  'package' => 'bannery',
  'version' => '1.1',
  'table' => 'bannery_ads_positions',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'ad' => NULL,
    'position' => NULL,
    'idx' => 0,
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
