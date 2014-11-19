<?php
class GetClicksProcessor extends modObjectGetListProcessor {
    public $classKey = 'byAd';
    public $languageTopics = array('bannery:default');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'bannery.ad';

    function prepareRow(xPDOObject $object) {
        $period = $this->getProperty('period');

        $object = $object->toArray();

        $clickC = $this->modx->newQuery('byClick');
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
        $object['clicks'] = $this->modx->getCount('byClick', $clickC);
        return $object;
    }
}
return 'GetClicksProcessor';
