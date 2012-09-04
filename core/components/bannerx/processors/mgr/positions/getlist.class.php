<?php
class PositionGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'bxPosition';
    public $languageTopics = array('bannerx:default');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'bannerx.position';
}
return 'PositionGetListProcessor';