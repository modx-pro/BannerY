<?php
$xpdo_meta_map['byPosition']= array (
  'package' => 'bannery',
  'version' => '1.1',
  'table' => 'bannery_positions',
  'extends' => 'xPDOSimpleObject',
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
  'indexes' => 
  array (
    'name' => 
    array (
      'alias' => 'name',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'name' => 
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
