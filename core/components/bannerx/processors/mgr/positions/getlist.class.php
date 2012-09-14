<?php
class PositionGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'bxPosition';
	public $languageTopics = array('bannerx:default');
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'ASC';
	public $objectType = 'bannerx.position';

	function prepareQueryBeforeCount(xPDOQuery $c) {
		// Filter by search query
		if ($query = $this->getProperty('query')) {
			$c->where(array('name:LIKE' => "%$query%"));
		}

		return $c;
	}

	function prepareRow(xPDOObject $object) {
		$object = $object->toArray();
		$object['clicks'] = $this->modx->getCount('bxClick', array('position' => $object['id']));
		return $object;
	}
}
return 'PositionGetListProcessor';