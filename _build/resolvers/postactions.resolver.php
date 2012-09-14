<?php
if($options[xPDOTransport::PACKAGE_ACTION] == xPDOTransport::ACTION_UPGRADE) {
	$action = 'upgrade';	
} elseif ($options[xPDOTransport::PACKAGE_ACTION] == xPDOTransport::ACTION_INSTALL) {
	$action = 'install';	
}

$success = false;
switch ($action) {  
	case 'upgrade':
	case 'install':
		// Create a reference to MODx since this resolver is executed from WITHIN a modCategory
		$modx =& $object->xpdo; 

		if (!isset($modx->bannery) || $modx->bannery == null) {
			$modx->addPackage('bannery', $modx->getOption('core_path').'components/bannery/model/');
		    $modx->bannery = $modx->getService('bannery', 'BannerY', $modx->getOption('core_path').'components/bannery/model/bannery/');
		}

		$mgr = $modx->getManager();
        $mgr->createObjectContainer('byAd');
        $mgr->createObjectContainer('byPosition');
        $mgr->createObjectContainer('byAdPosition');
        $mgr->createObjectContainer('byClick');

		$success = true;
		break;
}