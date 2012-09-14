<?php
class AdGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'bxAd';
	public $languageTopics = array('bannerx:default');
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'ASC';
	public $objectType = 'bannerx.ad';

	function prepareQueryBeforeCount(xPDOQuery $c) {
		// Filter by position
		if ($position = $this->getProperty('position')) {
			$mode = $this->getProperty('mode','include');

			$q = $this->modx->newQuery('bxAdPosition');
			$q->select('ad');
			$q->where(array('position' => $position));
			if ($q->prepare() && $q->stmt->execute()) {
				$ads = array_unique($q->stmt->fetchAll(PDO::FETCH_COLUMN));
			}
			if (!empty($ads)) {
				if ($mode == 'exclude') {
					$c->where(array('id:NOT IN' => $ads));
				}
				else {
					$c->where(array('id:IN' => $ads));
				}
			}
		}
		// Filter by search query
		if ($query = $this->getProperty('query')) {
			$c->where(array('name:LIKE' => "%$query%", 'OR:description:LIKE' => "%$query%"));
		}
		
		return $c;
	}
	
	function prepareRow(xPDOObject $object) {
		$object = $object->toArray();
		$object['clicks'] = $this->modx->getCount('bxClick', array('ad' => $object['id']));
		return $object;
	}
}
return 'AdGetListProcessor';