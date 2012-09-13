<?php
class AdUpdateProcessor extends modObjectUpdateProcessor {
	public $classKey = 'bxAd';
	public $languageTopics = array('bannerx:default');
	public $objectType = 'bannerx.ad';

	function afterSave() {
		$positions = $this->getProperty('positions');

		//user selected one or more positions, so update
		if(is_array($positions)) {
			//remove unused current positions
			$this->modx->removeCollection('bxAdPosition',
									array(
										'ad' => $this->object->get('id'),
										'position NOT IN('.implode(',', $positions).')'
									)
			);
			foreach($positions as $position) {
				//get current position if it exists
				$adPos = $this->modx->getObject('bxAdPosition', array(
															'ad' => $this->object->get('id'),
															'position' => $position
														  ));
				//this position is new, so create one
				if(!is_object($adPos)) {
					$adPos = $this->modx->newObject('bxAdPosition');
				}
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

		//no positions selected, so remove all of them
		else {
			$this->modx->removeCollection('bxAdPosition', array('ad' => $this->object->get('id')));
		}
	}
}
return 'AdUpdateProcessor';