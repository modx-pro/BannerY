<?php
$xpdo_meta_map['byAd']= array (
  'package' => 'bannery',
  'version' => '1.1',
  'table' => 'bannery_ads',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'name' => '',
    'url' => '',
    'image' => '',
    'source' => 0,
    'active' => 0,
    'description' => NULL,
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
      'default' => 0,
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
