<?php
class AdUpdateProcessor extends modObjectUpdateProcessor {
	public $classKey = 'byAd';
	public $languageTopics = array('bannery:default');
	public $objectType = 'bannery.ad';

	function afterSave() {
		$positions = $this->getProperty('positions');
		$ad = $this->object->get('id');

		//user selected one or more positions, so update
		if(is_array($positions)) {
			//remove unused current positions
			$q = $this->modx->newQuery('byAdPosition', array('position:NOT IN' => $positions, 'ad' => $ad));
			$adpositions = $this->modx->getCollection('byAdPosition', $q);
			foreach ($adpositions as $v) {
				$tmp = $v->get('position');
				$v->remove();
				$this->modx->bannery->refreshIdx($tmp);
			}
			/*
			$this->modx->removeCollection('byAdPosition',
									array(
										'ad' => $this->object->get('id'),
										'position NOT IN('.implode(',', $positions).')'
									)
			);
			*/
			foreach($positions as $position) {
				//get current position if it exists
				$adPos = $this->modx->getObject('byAdPosition', array(
															'ad' => $ad,
															'position' => $position
														  ));
				//this position is new, so create one
				if(!is_object($adPos)) {
					$adPos = $this->modx->newObject('byAdPosition');
				}
				//add settings
				$idx = $this->modx->getCount('byAdPosition', array('position' => $position));
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
			$this->modx->removeCollection('byAdPosition', array('ad' => $this->object->get('id')));
		}
	}
}
return 'AdUpdateProcessor';