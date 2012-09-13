<?php
$properties[0] = array(
	array(
		'name' => 'position',
		'value' => 0,
		'type' => 'numberfield',
		'desc' => 'If set to non-zero, will retrieve only ads that are assigned to the position given.',
	),
	array(
		'name' => 'tpl',
		'value' => 'bxAd',
		'type' => 'textfield',
		'desc' => 'Name of a chunk for templating an Ad.',
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
		'desc' => 'Return results in specified order. It can be any field of bxAd, "RAND()" or "idx" - index of ad in position.',
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
);

return $properties;
?>