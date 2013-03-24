<?php
$modx->addPackage('bannery', $modx->getOption('core_path').'components/bannery/model/');
$modx->lexicon->load('bannery:default');

$limit = $modx->getOption('limit', $scriptProperties, 5);
$sortdir = $modx->getOption('sortdir', $scriptProperties, 'ASC');
$sortby = $modx->getOption('sortby', $scriptProperties, 'RAND()');
$tpl = $modx->getOption('tpl', $scriptProperties, 'byAd');
$tplOuter = $modx->getOption('tplOuter', $scriptProperties, null);
$position = $modx->getOption('position', $scriptProperties, 0);
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, null);
$output = '';

if($position > 0) {
	$c = $modx->newQuery('byAd');
	$c->select('byAd.id AS id, byAd.name AS name, byAd.image AS image, byAd.url AS url, pos.id AS adposition, pos.idx AS idx');
	$c->leftJoin('byAdPosition', 'pos', 'pos.ad=byAd.id');
	$c->where(array(
					'byAd.active' => 1,
					'pos.position' => $position
			  )
	);
	if($sortby == 'RAND()') {
		$c->sortby('RAND()');
	}    
	else if($sortby == 'idx' || $sortby == 'index') {
		$c->sortby('pos.idx', $sortdir);
	}
	else {
		$c->sortby('byAd.'.$sortby, $sortdir);
	}
	$c->limit($limit);

	$sourceId = $modx->getOption('bannery.media_source', null, $modx->getOption('default_media_source'));
	$source = $modx->getObject('sources.modMediaSource',array('id'=>$sourceId));
	if (!$source)
		$source = modMediaSource::getDefaultSource($modx);
	$source->initialize();

	$ads = $modx->getCollection('byAd', $c);
	foreach($ads as $ad) {
		$ad = $ad->toArray();
		if (!empty($ad['image']))
			$ad['image'] = $source->getObjectUrl($ad['image']);
		$output .= $modx->getChunk($tpl, $ad);
	}
	if (!empty($tplOuter)) {
		$output = $modx->getChunk($tplOuter, array('items'=>$output));
	}

	if (!empty($toPlaceholder)) {
		$modx->setPlaceholder($toPlaceholder, $output);
		$output = '';
	}
}
return $output;