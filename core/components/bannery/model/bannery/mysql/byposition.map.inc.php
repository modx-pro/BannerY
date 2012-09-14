<?php
$xpdo_meta_map['byPosition']= array (
  'package' => 'bannery',
  'table' => 'bannery_positions',
  'fields' => 
  array (
    'name' => '',
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
  ),
  'composites' => 
  array (
    'Ads' => 
    array (
      'class' => 'byAdPosition',
      'local' => 'id',
      'foreign' => 'position',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Clicks' => 
    array (
      'class' => 'byClick',
      'local' => 'id',
      'foreign' => 'position',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
