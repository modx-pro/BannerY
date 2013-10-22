<?php

if ($object->xpdo) {
	/* @var modX $modx */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			$modelPath = $modx->getOption('core_path').'components/'.PKG_NAME_LOWER.'/model/';
			$modx->addPackage(PKG_NAME_LOWER, $modelPath);

			$manager = $modx->getManager();
			$objects = array(
				'byAd',
				'byPosition',
				'byAdPosition',
				'byClick',
			);
			foreach ($objects as $object) {
				$manager->createObjectContainer($object);
			}
			break;

		case xPDOTransport::ACTION_UPGRADE:
			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}
return true;