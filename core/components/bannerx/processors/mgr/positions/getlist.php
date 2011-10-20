<?php
/**
 * Get a list of positions
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
$query = $modx->getOption('query',$scriptProperties,'');

/* build query */
$c = $modx->newQuery('bxPosition');

if (!empty($query)) {
    $c->where(array(
        'name:LIKE' => '%'.$query.'%'
    ));
}

$count = $modx->getCount('bxPosition',$c);
$c->sortby($sort,$dir);
if ($isLimit) $c->limit($limit,$start);
$positions = $modx->getIterator('bxPosition', $c);

/* iterate */
$list = array();
foreach ($positions as $position) {
    $list[]= $position->toArray();
}
return $this->outputArray($list,$count);