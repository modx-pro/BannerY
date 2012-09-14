<?php
class AdGetProcessor extends modObjectGetProcessor {
    public $classKey = 'byAd';
    public $languageTopics = array('bannery:default');
    public $objectType = 'bannery.ad';
	
	public function beforeOutput() {
		$adPositionList = array();
		$adPositions = $this->object->getMany('Positions');
		foreach($adPositions as $adPosition) {
			$adPositionList[] = $adPosition->get('position');
		}
		$this->object->set('positions', $adPositionList);
	}
	
}
return 'AdGetProcessor';