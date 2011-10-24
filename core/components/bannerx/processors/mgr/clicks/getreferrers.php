<?php
$period = $modx->getOption('period', $scriptProperties, '');
$isLimit = !empty($scriptProperties['limit']);
$limit = $modx->getOption('limit',$scriptProperties,20);
$start = $modx->getOption('start',$scriptProperties,0);

$conditions = array();
if(!empty($period)) {
    if($period == 'last month') {
        $conditions['clickdate:LIKE'] = strftime('%Y-%m', strtotime('first day of last month'));
    }
    else {
        $conditions['clickdate:LIKE'] =  strftime($period).'%';
    }
}


$c = $modx->newQuery('bxClick');
$c->select('COUNT(DISTINCT(referrer))');
$c->andCondition($conditions);
if ($c->prepare() && $c->stmt->execute()) {
    $rows = $c->stmt->fetchAll(PDO::FETCH_COLUMN);
    $count = (integer) reset($rows);

    $c = $modx->newQuery('bxClick');
    $c->select('COUNT(id) as clicks, referrer');
    $c->andCondition($conditions);
    $c->groupby('referrer');
    $c->sortby('clicks', 'DESC');

    if ($isLimit) $c->limit($limit,$start);
    $c->prepare();
    $c->stmt->execute();
    $referrers = $c->stmt->fetchAll(PDO::FETCH_ASSOC);

    /* iterate */
    $list = array();

    foreach ($referrers as $referrer) {
        $list[] = $referrer;
    }
    return $this->outputArray($list,$count);
}
else {
    return '';
}