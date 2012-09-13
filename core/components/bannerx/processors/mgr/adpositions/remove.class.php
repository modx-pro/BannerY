<?php
class AdPositionRemoveProcessor extends modObjectRemoveProcessor {
    public $classKey = 'bxAdPosition';
    public $languageTopics = array('bannerx:default');
    public $objectType = 'bannerx.adposition';
	public $position = 0;
	
	function beforeRemove() {
		$this->position = $this->object->get('position');
		return true;
	}
	
	function afterRemove() {
		$this->modx->bannerx->refreshIdx($this->position);
		return true;
	}
}
return 'AdPositionRemoveProcessor';