<?php
/**
 * Get a list of Ads
 *
 * @package bannerx
 * @subpackage processors
 */
/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$start = $modx->getOption('start',$scriptProperties,0);
$limit = $modx->getOption('limit',$scriptProperties,20);
$sort = $modx->getOption('sort',$scriptProperties,'id');
$dir = $modx->getOption('dir',$scriptProperties,'ASC');

/* build query */
$c = $modx->newQuery('bxAd');
$count = $modx->getCount('bxAd',$c);
$c->sortby($sort,$dir);
if ($isLimit) $c->limit($limit,$start);
$ads = $modx->getIterator('bxAd', $c);

$positions = $modx->getCollection('bxPosition');
foreach($positions as $position) {
    $positions[] = $position->get('id');
}

/* iterate */
$list = array();
foreach ($ads as $ad) {
    $adPositions = $ad->getMany('Positions');
    $adPositionList = array();
    foreach($adPositions as $adPosition) {
        $adPositionList[] = $adPosition->get('position');
    }

    $ad = $ad->toArray();
    $ad['positions'] = $adPositionList;
    $list[] = $ad;
}
return $this->outputArray($list,$count);