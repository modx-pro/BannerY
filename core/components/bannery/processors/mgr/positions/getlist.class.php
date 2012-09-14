<?php
class PositionGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'byPosition';
	public $languageTopics = array('bannery:default');
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'ASC';
	public $objectType = 'bannery.position';

	function prepareQueryBeforeCount(xPDOQuery $c) {
		// Filter by search query
		if ($query = $this->getProperty('query')) {
			$c->where(array('name:LIKE' => "%$query%"));
		}

		return $c;
	}

	function prepareRow(xPDOObject $object) {
		$object = $object->toArray();
		$object['clicks'] = $this->modx->getCount('byClick', array('position' => $object['id']));
		return $object;
	}
}
return 'PositionGetListProcessor';