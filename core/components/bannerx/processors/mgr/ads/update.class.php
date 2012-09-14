<?php
class AdUpdateProcessor extends modObjectUpdateProcessor {
	public $classKey = 'bxAd';
	public $languageTopics = array('bannerx:default');
	public $objectType = 'bannerx.ad';

	function afterSave() {
		$positions = $this->getProperty('positions');
		$ad = $this->object->get('id');

		//user selected one or more positions, so update
		if(is_array($positions)) {
			//remove unused current positions
			$q = $this->modx->newQuery('bxAdPosition', array('position:NOT IN' => $positions, 'ad' => $ad));
			$adpositions = $this->modx->getCollection('bxAdPosition', $q);
			foreach ($adpositions as $v) {
				$tmp = $v->get('position');
				$v->remove();
				$this->modx->bannerx->refreshIdx($tmp);
			}
			/*
			$this->modx->removeCollection('bxAdPosition',
									array(
										'ad' => $this->object->get('id'),
										'position NOT IN('.implode(',', $positions).')'
									)
			);
			*/
			foreach($positions as $position) {
				//get current position if it exists
				$adPos = $this->modx->getObject('bxAdPosition', array(
															'ad' => $ad,
															'position' => $position
														  ));
				//this position is new, so create one
				if(!is_object($adPos)) {
					$adPos = $this->modx->newObject('bxAdPosition');
				}
				//add settings
				$idx = $this->modx->getCount('bxAdPosition', array('position' => $position));
				$adPos->fromArray(array(
									'ad' => $ad,
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