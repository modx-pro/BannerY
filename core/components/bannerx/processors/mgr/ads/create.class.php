<?php
class AdCreateProcessor extends modObjectCreateProcessor {
	public $classKey = 'bxAd';
	public $languageTopics = array('bannerx:default');
	public $objectType = 'bannerx.ad';

	function afterSave() {
		$positions = $this->getProperty('positions');

		//user selected one or more positions, so update
		if(is_array($positions)) {
			foreach($positions as $position) {
				$adPos = $this->modx->newObject('bxAdPosition');
				//add settings
				$idx = $this->modx->getCount('bxAdPosition', array('position' => $position));
				$adPos->fromArray(array(
									'ad' => $this->object->get('id'),
									'position' => $position,
									'idx' => $idx
								  ));
				//save position
				$adPos->save();
			}
		}
	}
}
return 'AdCreateProcessor';