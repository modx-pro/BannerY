<?php
class AdPositionUpdateProcessor extends modObjectProcessor {
	public $classKey = 'bxAdPosition';
	public $languageTopics = array('bannerx:default');
	public $objectType = 'bannerx.adposition';
	public $checkSavePermission = true;

	function initialize() {
		$this->object = $this->modx->newObject($this->classKey);

		if ($this->checkSavePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('save')) {
			return $this->modx->lexicon('access_denied');
		}
		return true;
	}
	
	
	function process() {
		if (!$position = $this->getProperty('position')) {return $this->modx->error->failure($this->modx->lexicon('bannerx.positions.error.ns'));}
		if (!$ad = $this->getProperty('ad')) {return $this->modx->error->failure($this->modx->lexicon('bannerx.ads.error.ns'));}
		
		$arr = array(
			'position' => $position
			,'ad' => $ad
		);
		
		if ($this->modx->getCount('bxAdPosition', $arr)) {return $this->modx->error->failure($this->modx->lexicon('bannerx.adposition.error.ae'));}
		
		$arr['idx'] = $this->modx->getCount('bxAdPosition', array('position' => $position));
		$this->object->fromArray($arr);
		$this->object->save();
		
		return $this->modx->error->success();
	}
}
return 'AdPositionUpdateProcessor';