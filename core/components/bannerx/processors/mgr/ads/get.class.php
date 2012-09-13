<?php
class AdGetProcessor extends modObjectGetProcessor {
    public $classKey = 'bxAd';
    public $languageTopics = array('bannerx:default');
    public $objectType = 'bannerx.ad';
	
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