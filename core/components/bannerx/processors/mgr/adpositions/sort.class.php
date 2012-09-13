<?php
class AdPositionUpdateProcessor extends modObjectProcessor {
	public $classKey = 'bxAdPosition';
	public $languageTopics = array('bannerx:default');
	public $objectType = 'bannerx.adposition';
	public $checkSavePermission = true;

	function initialize() {
		$primaryKey = $this->getProperty($this->primaryKeyField,false);
		if (empty($primaryKey)) return $this->modx->lexicon($this->objectType.'_err_ns');
		$this->object = $this->modx->getObject($this->classKey,$primaryKey);
		if (empty($this->object)) return $this->modx->lexicon($this->objectType.'_err_nfs',array($this->primaryKeyField => $primaryKey));

		if ($this->checkSavePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('save')) {
			return $this->modx->lexicon('access_denied');
		}
		return true;
	}
	
	
	function process() {
		$new_order = $this->getProperty('new_order');
		$old_order = $this->getProperty('old_order');
		
		if ($old_order > $old_order) {$direction = 'down';}
		else {$direction = 'up';}
		
		$positionads = $this->object->getPositionAds();
		$empty = $ordered = $arr = array();
		foreach ($positionads as $v) {
			$id = $v->get('id');
			if ($id == $this->getProperty('id')) {
				$excluded = $v;
				continue;
			}

			$order = $v->get('idx');
			
			if (empty($order)) {
				$empty[] = $v;
			}
			else {
				$ordered[$order] = $v;
			}
		}
		
		if (empty($empty)) {
			$tmp = $ordered;
			$ordered = array();
			foreach ($tmp as $k => $v) {
				$ordered[$k - 1] = $v;
			}
		}
		
		$flag = 0;
		$count = count($positionads) - 1;
		for ($i = 0; $i <= $count; $i++) {
			if ($i == $new_order && ($direction == 'up' || $i == 1 || $old_order == 0)) {
				$arr[] = $excluded;
			}
			
			if (isset($ordered[$i]) && is_object($ordered[$i])) {
				$arr[] = $ordered[$i];
			}
			else {
				$tmp = array_shift($empty);
				if (is_object($tmp)) {
					$arr[] = $tmp;
				}
			}
			
			if ($i == $new_order && $direction == 'down' && $i != 1 && $old_order != 0) {
				$arr[] = $excluded;
			}
		}
		
		foreach ($arr as $k => $v) {
			$v->set('idx', $k);
			$v->save();
		}

		return $this->modx->error->success();
	}
}
return 'AdPositionUpdateProcessor';