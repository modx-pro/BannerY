<?php
$xpdo_meta_map['byClick']= array (
  'package' => 'bannery',
  'version' => '1.1',
  'table' => 'bannery_clicks',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'ad' => NULL,
    'position' => NULL,
    'clickdate' => NULL,
    'referrer' => NULL,
    'ip' => NULL,
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
    'clickdate' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'referrer' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
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
  ),
);
