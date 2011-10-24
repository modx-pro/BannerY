<?php
$period = $modx->getOption('period', $scriptProperties, '');

$c = $modx->newQuery('bxAd');
$count = $modx->getCount('bxAd',$c);
$c->select('id, name');
$ads = $modx->getIterator('bxAd', $c);

/* iterate */
$list = array();

foreach ($ads as $ad) {

    $ad = $ad->toArray();

    $clickC = $modx->newQuery('bxClick');
    $conditions = array();
    $conditions['ad'] = $ad['id'];
    if(!empty($period)) {
        if($period == 'last month') {
            $conditions['clickdate:LIKE'] = strftime('%Y-%m', strtotime('first day of last month')).'%';
        }
        else {
            $conditions['clickdate:LIKE'] =  strftime($period).'%';
        }
    }
    $clickC->andCondition($conditions);
    $ad['clicks'] = $modx->getCount('bxClick', $clickC);

    $list[] = $ad;
}
return $this->outputArray($list,$count);