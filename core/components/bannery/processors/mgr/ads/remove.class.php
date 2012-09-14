<?php
class AdRemoveProcessor extends modObjectRemoveProcessor {
    public $classKey = 'byAd';
    public $languageTopics = array('bannery:default');
    public $objectType = 'bannery.ad';
}
return 'AdRemoveProcessor';