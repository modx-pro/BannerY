<?php
class AdGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'byAd';
	public $languageTopics = array('bannery:default');
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'ASC';
	public $objectType = 'bannery.ad';

	function prepareQueryBeforeCount(xPDOQuery $c) {
		// Filter by position
		if ($position = $this->getProperty('position')) {
			$mode = $this->getProperty('mode','include');
			$c->innerJoin('byAdPosition', 'byAdPosition', array('`byAdPosition`.`ad` = `byAd`.`id`'));
			if ($mode == 'exclude') {
				$c->where(array("byAdPosition.position:!="=>$position));
			}
			else {
				$c->where(array("byAdPosition.position"=>$position));
			}

			$c->sortby("byAdPosition.idx,id", "ASC");
		}
		// Filter by search query
		if ($query = $this->getProperty('query')) {
			$c->where(array('name:LIKE' => "%$query%", 'OR:description:LIKE' => "%$query%"));
		}
		
		return $c;
	}

	function prepareRow(xPDOObject $object) {
		/** @var byAd $object */
		$row = $object->toArray();
		$row['clicks'] = $this->modx->getCount('byClick', array('ad' => $row['id']));
		$row['current_image'] = $object->getImageUrl();

		if (preg_match('/\[\[\~([0-9]{1,})\]\]$/', $row['url'], $matches)) {
			if ($resource = $this->modx->getObject('modResource', $matches[1])) {
				$row['url'] = '<sup>('.$resource->id .')</sup> '.$resource->pagetitle;
			}
		}

		return $row;
	}
}
return 'AdGetListProcessor';