<?php

$properties = array();

$tmp = array(
	'positions' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'showLog' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'fastMode' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'limit' => array(
		'value' => 5,
		'type' => 'numberfield',
	),
	'offset' => array(
		'type' => 'numberfield',
		'value' => 0,
	),
	'sortby' => array(
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
		'value' => 'RAND()',
	),
	'sortdir' => array(
		'type' => 'list',
		'options' => array(
			array('text' => 'ASC','value' => 'ASC'),
			array('text' => 'DESC','value' => 'DESC'),
		),
		'value' => 'ASC',
	),
	'outputSeparator' => array(
		'type' => 'textfield',
		'value' => "\n",
	),
	'where' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'showInactive' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),

	'tpl' => array(
		'type' => 'textfield',
		'value' => 'byAd',
	),
	'tplFirst' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'tplLast' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'tplOdd' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'tplWrapper' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'wrapIfEmpty' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'toPlaceholder' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'toSeparatePlaceholder' => array(
		'type' => 'textfield',
		'value' => '',
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