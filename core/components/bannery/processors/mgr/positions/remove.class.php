<?php
class PositionRemoveProcessor extends modObjectRemoveProcessor {
    public $classKey = 'byPosition';
    public $languageTopics = array('bannery:default');
    public $objectType = 'bannery.position';
}
return 'PositionRemoveProcessor';