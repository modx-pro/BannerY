<?php
class AdGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'bxAd';
	public $languageTopics = array('bannerx:default');
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'ASC';
	public $objectType = 'bannerx.ad';

	function prepareRow(xPDOObject $object) {
		/*
		$adPositionList = array();
		$adPositions = $object->getMany('Positions');
		foreach($adPositions as $adPosition) {
			$adPositionList[] = $adPosition->get('position');
		}
		*/

		$object = $object->toArray();
		//$object['positions'] = $adPositionList;
		$object['clicks'] = $this->modx->getCount('bxClick', array('ad' => $object['id']));
		return $object;
	}
}
return 'AdGetListProcessor';