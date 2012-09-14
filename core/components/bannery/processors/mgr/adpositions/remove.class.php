<?php
class AdPositionRemoveProcessor extends modObjectRemoveProcessor {
    public $classKey = 'byAdPosition';
    public $languageTopics = array('bannery:default');
    public $objectType = 'bannery.adposition';
	public $position = 0;
	
	function beforeRemove() {
		$this->position = $this->object->get('position');
		return true;
	}
	
	function afterRemove() {
		$this->modx->bannery->refreshIdx($this->position);
		return true;
	}
}
return 'AdPositionRemoveProcessor';