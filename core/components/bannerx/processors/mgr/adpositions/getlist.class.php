<?php
class AdPositionGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'bxAdPosition';
	public $languageTopics = array('bannerx:default');
	public $defaultSortField = 'idx';
	public $defaultSortDirection = 'ASC';
	public $objectType = 'bannerx.adposition';

	function beforeQuery() {
		$position = $this->getProperty('position');
		if (empty($position)) {
			return $this->modx->lexicon('bannerx.positions.error.nf');
		}
		return true;
	}
	
	function prepareQueryBeforeCount(xPDOQuery $c) {
		$position = $this->getProperty('position');
		$c->where(array('position' => $position));
		return $c;
	}


	function prepareRow(xPDOObject $object) {
		$ad = $object->getOne('Ad')->toArray();
		$adposition = $object->toArray();
		
		$object = array_merge($ad, $adposition);
		
		return $object;
	}

}
return 'AdPositionGetListProcessor';