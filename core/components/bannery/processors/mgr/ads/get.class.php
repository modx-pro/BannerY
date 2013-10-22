<?php
class AdGetProcessor extends modObjectGetProcessor {
	/** @var byAd $object */
	public $object;
	public $classKey = 'byAd';
	public $languageTopics = array('bannery:default');
	public $objectType = 'bannery.ad';

	public function beforeOutput() {
		$adPositionList = array();
		$adPositions = $this->object->getMany('Positions');
		/** @var byAdPosition $adPosition */
		foreach($adPositions as $adPosition) {
			$adPositionList[] = $adPosition->get('position');
		}
		$this->object->set('positions', $adPositionList);
	}

	public function cleanup() {
		$row = $this->object->toArray();
		$row['current_image'] = $this->object->getImageUrl($row['image']);
		return $this->success('', $row);
	}

}
return 'AdGetProcessor';