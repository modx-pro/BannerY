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
					$modelPath = $modx->getOption('core_path').'components/'.PKG_NAME_LOWER.'/model/';
					$modx->addPackage(PKG_NAME_LOWER, $modelPath);

					$collection = $modx->getCollection('byAd');
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