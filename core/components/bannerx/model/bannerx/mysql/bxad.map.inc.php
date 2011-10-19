<?php
$xpdo_meta_map['bxAd']= array (
  'package' => 'bannerx',
  'table' => 'bannerx_ads',
  'fields' => 
  array (
    'name' => '',
    'url' => '',
    'image' => '',
    'active' => 0,
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
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
  ),
  'composites' => 
  array (
    'Positions' => 
    array (
      'class' => 'bxAdPosition',
      'local' => 'id',
      'foreign' => 'ad',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
