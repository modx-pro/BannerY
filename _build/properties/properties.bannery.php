<?php

$properties = array();

$tmp = array(
	'position' => array(
		'value' => 0,
		'type' => 'numberfield',
	),
	'tpl' => array(
		'value' => 'byAd',
		'type' => 'textfield',
	),
	'tplOuter' => array(
		'value' => '',
		'type' => 'textfield',
	),
	'limit' => array(
		'value' => 5,
		'type' => 'numberfield',
	),
	'sortby' => array(
		'value' => 'RAND()',
		'type' => 'list',
		'options' => array(
			array('text' => 'Random','value' => 'RAND()'),
			array('text' => 'Index','value' => 'idx'),
			array('text' => 'Name','value' => 'name'),
			array('text' => 'Url','value' => 'url'),
			array('text' => 'Image','value' => 'image'),
			array('text' => 'Active','value' => 'active'),
			array('text' => 'Description','value' => 'description'),
		),
	),
	'sortdir' => array(
		'value' => 'ASC',
		'type' => 'list',
		'options' => array(
			array('text' => 'ASC','value' => 'ASC'),
			array('text' => 'DESC','value' => 'DESC'),
		),
	),
	'toPlaceholder' => array(
		'value' => '',
		'type' => 'textfield',
	),
);

foreach ($tmp as $k => $v) {
	$properties[] = array_merge(
		array(
			'name' => $k,
			'desc' => PKG_NAME_LOWER . '_prop_' . $k,
			'lexicon' => PKG_NAME_LOWER . ':properties',
		), $v
	);
}

return $properties;