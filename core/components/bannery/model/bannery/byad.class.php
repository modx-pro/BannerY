<?php
class byAd extends xPDOSimpleObject {


	public function getImageUrl($image = '') {
		if (empty($image)) {
			$image = parent::get('image');
		};

		if (!empty($image) && $source = parent::get('source')) {
			/** @var modMediaSource $source */
			if ($source = $this->xpdo->getObject('sources.modMediaSource', $source)) {
				$source->initialize();
				$image = $source->getObjectUrl($image);
			}
		}

		return $image;
	}
}