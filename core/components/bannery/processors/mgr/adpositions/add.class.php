<?php
class AdPositionUpdateProcessor extends modObjectProcessor {
	public $classKey = 'byAdPosition';
	public $languageTopics = array('bannery:default');
	public $objectType = 'bannery.adposition';
	public $checkSavePermission = true;

	function initialize() {
		$this->object = $this->modx->newObject($this->classKey);

		if ($this->checkSavePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('save')) {
			return $this->modx->lexicon('access_denied');
		}
		return true;
	}
	
	
	function process() {
		if (!$position = $this->getProperty('position')) {return $this->modx->error->failure($this->modx->lexicon('bannery.positions.error.ns'));}
		if (!$ad = $this->getProperty('ad')) {return $this->modx->error->failure($this->modx->lexicon('bannery.ads.error.ns'));}
		
		$arr = array(
			'position' => $position
			,'ad' => $ad
		);
		
		if ($this->modx->getCount('byAdPosition', $arr)) {return $this->modx->error->failure($this->modx->lexicon('bannery.adposition.error.ae'));}
		
		$arr['idx'] = $this->modx->getCount('byAdPosition', array('position' => $position));
		$this->object->fromArray($arr);
		$this->object->save();
		
		return $this->modx->error->success();
	}
}
return 'AdPositionUpdateProcessor';