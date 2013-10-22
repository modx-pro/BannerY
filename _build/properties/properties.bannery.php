<?php

$properties = array();

$tmp = array(
	array(
		'name' => 'position',
		'value' => 0,
		'type' => 'numberfield',
		'desc' => 'If set to non-zero, will retrieve only ads that are assigned to the position given.',
	),
	array(
		'name' => 'tpl',
		'value' => 'byAd',
		'type' => 'textfield',
		'desc' => 'Name of a chunk for templating an Ad.',
	),
	array(
		'name' => 'tplOuter',
		'value' => '',
		'type' => 'textfield',
		'desc' => 'Name of a chunk for outer templating.',
	),
	array(
		'name' => 'limit',
		'value' => 5,
		'type' => 'numberfield',
		'desc' => 'If set to non-zero, will only show X number of items.',
	),
	array(
		'name' => 'sortby',
		'value' => 'RAND()',
		'type' => 'list',
		'desc' => 'Return results in specified order. It can be any field of byAd, "RAND()" or "idx" - index of ad in position.',
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
	array(
		'name' => 'sortdir',
		'value' => 'ASC',
		'type' => 'list',
		'desc' => 'Order of the results',
		'options' => array(
			array('text' => 'ASC','value' => 'ASC'),
			array('text' => 'DESC','value' => 'DESC'),
		),
	),
	array(
		'name' => 'toPlaceholder',
		'value' => '',
		'type' => 'textfield',
		'desc' => 'If set, will assign the result to this placeholder instead of outputting it directly.',
	)
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