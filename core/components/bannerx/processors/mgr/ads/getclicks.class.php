<?php
class GetClicksProcessor extends modObjectGetListProcessor {
    public $classKey = 'bxAd';
    public $languageTopics = array('bannerx:default');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'bannerx.ad';

    function prepareRow($object) {
        $period = $this->getProperty('period');

        $object = $object->toArray();

        $clickC = $this->modx->newQuery('bxClick');
        $conditions = array();
        $conditions['ad'] = $object['id'];

        if(!empty($period)) {
            if($period == 'last month') {
                $conditions['clickdate:LIKE'] = strftime('%Y-%m', strtotime('first day of last month')).'%';
            }
            else {
                $conditions['clickdate:LIKE'] =  strftime($period).'%';
            }
        }
        $clickC->andCondition($conditions);
        $object['clicks'] = $this->modx->getCount('bxClick', $clickC);
        return $object;
    }
}
return 'GetClicksProcessor';