<?php
if ($object->xpdo) {
	/* @var modX $modx */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			break;

		case xPDOTransport::ACTION_UPGRADE:
			$setting = $modx->getObject('modSystemSetting', array('key' => 'bannery.media_source'));
			if (!is_null($setting) && $setting !== false && is_object($setting)) {
				if ($source = $setting->get('value')) {
					$modelPath = $modx->getOption('core_path').'components/bannery/model/';
					$modx->addPackage('bannery', $modelPath);

					$collection = $modx->getCollection('byAd');
					/** @var byAd $v */
					foreach ($collection as $v) {
						$v->set('source', $source);
						$v->save();
					}
				}
				$setting->remove();
			}
			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}
return true;