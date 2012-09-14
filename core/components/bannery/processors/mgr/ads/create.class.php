<?php
class AdCreateProcessor extends modObjectCreateProcessor {
	public $classKey = 'byAd';
	public $languageTopics = array('bannery:default');
	public $objectType = 'bannery.ad';

	function afterSave() {
		$positions = $this->getProperty('positions');

		//user selected one or more positions, so update
		if(is_array($positions)) {
			foreach($positions as $position) {
				$adPos = $this->modx->newObject('byAdPosition');
				//add settings
				$idx = $this->modx->getCount('byAdPosition', array('position' => $position));
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