<?php
/** @var array $scriptProperties */
/* @var pdoFetch $pdoFetch */
if (!$modx->getService('pdoFetch')) {return 'You need to install pdoTools to use this snippet!';}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded');
$modx->lexicon->load('bannery:default');
$modx->addPackage('bannery', MODX_CORE_PATH . 'components/bannery/model/');

if (!empty($tplOuter)) {$tplWrapper = $tplOuter;}
if (empty($outputSeparator)) {$outputSeparator = "\n";}
$class = 'byAd';

// Adding extra parameters into special place so we can put them in results
/** @var modSnippet $snippet */
$additionalPlaceholders = array();
if ($snippet = $modx->getObject('modSnippet', array('name' => 'BannerY'))) {
	$properties = unserialize($snippet->properties);
	foreach ($scriptProperties as $k => $v) {
		if (!isset($properties[$k])) {
			$additionalPlaceholders[$k] = $v;
		}
	}
}
// ---

$date = date('Y-m-d H:i:s');
$where = array(
	"({$class}.start IS NULL OR {$class}.start <= '{$date}') AND ({$class}.end IS NULL OR {$class}.end >= '{$date}')"
);
if (empty($showInactive)) {
	$where[$class.'.active'] = 1;
}
if (!empty($position)) {
	$where['byAdPosition.position'] = $position;
}
elseif (!empty($positions)) {
	$where['byAdPosition.position:IN'] = array_map('trim', explode(',', $positions));
}

if (empty($sortby)) {
	$sortby = 'RAND()';
}
elseif ($sortby == 'idx' || $sortby == 'index') {
	$sortby = 'byAdPosition.idx';
}
else {
	$sortby = $class.'.'.$sortby;
}

$innerJoin = array(
	'byAdPosition' => array(
		'alias' => 'byAdPosition',
		'on' => $class.'.id = byAdPosition.ad'
	)
);

// Fields to select
$select = array(
	$class => 'all',
	'byAdPosition' => '`byAdPosition`.`id` as `adposition`, `byAdPosition`.`ad`'
);

// Add custom parameters
foreach (array('where','innerJoin','select') as $v) {
	if (!empty($scriptProperties[$v])) {
		$tmp = $modx->fromJSON($scriptProperties[$v]);
		if (is_array($tmp)) {
			$$v = array_merge($$v, $tmp);
		}
	}
	unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

$default = array(
	'class' => $class,
	'innerJoin' => $modx->toJSON($innerJoin),
	'where' => $modx->toJSON($where),
	'select' => $modx->toJSON($select),
	'sortby' => $sortby,
	'groupby' => $class.'.id',
	'return' => 'data',
	'disableConditions' => true,
);

$pdoFetch->setConfig(array_merge($default, $scriptProperties));
$rows = $pdoFetch->run();

$output = array();
$default_source = $modx->getOption('bannery_media_source', null, $modx->getOption('default_media_source'), true);
$sources = array();
foreach ($rows as $row) {
	$source = !empty($row['source'])
		? $row['source']
		: $default_source;

	if (!isset($sources[$row['source']])) {
		/** @var modMediaSource $source */
		if ($source = $modx->getObject('sources.modMediaSource', $source)) {
			$source->initialize($modx->context->key);
		}
		$sources[$row['source']] = $source;
	}
	else {
		$source = $sources[$row['source']];
	}

	if (!empty($source) && $source instanceof modMediaSource && !empty($row['image'])) {
		$row['image'] = $source->getObjectUrl($row['image']);
	}

	$row['idx'] = $pdoFetch->idx++;
	$tpl = $pdoFetch->defineChunk($row);
	if (!empty($additionalPlaceholders)) {
		$row = array_merge($additionalPlaceholders, $row);
	}

	$output[] = !empty($tpl)
		? $pdoFetch->getChunk($tpl, $row, $pdoFetch->config['fastMode'])
		: '<pre>'.$pdoFetch->getChunk('', $row).'</pre>';
}

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$output['log'] = '<pre class="pdoUsersLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

// Return output
if (!empty($toSeparatePlaceholders)) {
	$modx->setPlaceholders($output, $toSeparatePlaceholders);
}
else {
	$output = implode($outputSeparator, $output);

	if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
		$output = $pdoFetch->getChunk($tplWrapper, array('output' => $output), $pdoFetch->config['fastMode']);
	}

	if (!empty($toPlaceholder)) {
		$modx->setPlaceholder($toPlaceholder, $output);
	}
	else {
		return $output;
	}
}
