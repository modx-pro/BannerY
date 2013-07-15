<?php
/**
 * Resolve settings.
 *
 * @package bannery
 * @subpackage build
 */
function createSetting(&$modx,$key,$value,$type='textfield') {
	$ct = $modx->getCount('modSystemSetting',array(
		'key' => 'bannery.'.$key,
	));
	if (empty($ct)) {
		$setting = $modx->newObject('modSystemSetting');
		$setting->set('key','bannery.'.$key);
		$setting->set('xtype', $type);
		$setting->set('value',$value);
		$setting->set('namespace','bannery');
		$setting->set('area','');
		$setting->save();
	}
}
if ($object->xpdo) {
	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			$modx =& $object->xpdo;

			/* create mediasource setting */
			createSetting($modx,'media_source', $modx->getOption('default_media_source'), 'modx-combo-source');
		break;
	}
}
return true;