<?php
require MODX_CORE_PATH . 'model/modx/processors/resource/getlist.class.php';
class byResourceGetListProcessor extends modResourceGetListProcessor {

	function prepareQueryBeforeCount(xPDOQuery $c) {
		if ($query = $this->getProperty('query')) {
			$c->where(array('pagetitle:LIKE' => "%$query%", 'OR:longtitle:LIKE' => "%$query%"));
		}
		return $c;
	}
	
	function afterIteration(array $list) {
		if ($query = $this->getProperty('query')) {
			if (filter_var($query, FILTER_VALIDATE_URL)) {
				$list[] = array('pagetitle' => $query, 'url' => $query);
			}
		}
		return $list;
	}
	
	function prepareRow(xPDOObject $object) {
		$object->set('url', '[[~'.$object->get('id').']]');
		return parent::prepareRow($object);
	}

}

return 'byResourceGetListProcessor';