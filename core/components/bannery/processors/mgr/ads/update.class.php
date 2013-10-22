<?php
class AdUpdateProcessor extends modObjectUpdateProcessor {
	public $classKey = 'byAd';
	public $languageTopics = array('bannery:default');
	public $objectType = 'bannery.ad';

	function afterSave() {
		$positions = $this->getProperty('positions');
		$ad = $this->object->get('id');

		if(is_array($positions)) {
			//remove unused current positions
			$q = $this->modx->newQuery('byAdPosition', array('position:NOT IN' => $positions, 'ad' => $ad));
			$adpositions = $this->modx->getCollection('byAdPosition', $q);
			/** @var byAdPosition $adposition */
			foreach ($adpositions as $adposition) {
				$position = $adposition->get('position');
				$adposition->remove();
				$this->modx->bannery->refreshIdx($position);
			}
			// add ad to new postion
			foreach($positions as $position) {
				$arr = array('ad' => $ad,'position' => $position);
				
				if (!$adPos = $this->modx->getObject('byAdPosition', $arr)) {
					$adPos = $this->modx->newObject('byAdPosition');
					$arr['idx'] = $this->modx->getCount('byAdPosition', array('position' => $position));
				}
				$adPos->fromArray($arr);
				$adPos->save();
			}
		}
		else {
			//no positions selected, so remove all of them
			$this->modx->removeCollection('byAdPosition', array('ad' => $ad));
		}
	}
}
return 'AdUpdateProcessor';