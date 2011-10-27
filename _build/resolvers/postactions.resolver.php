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

		if (!isset($modx->bannerx) || $modx->bannerx == null) {
			$modx->addPackage('bannerx', $modx->getOption('core_path').'components/bannerx/model/');
		    $modx->bannerx = $modx->getService('bannerx', 'BannerX', $modx->getOption('core_path').'components/bannerx/model/bannerx/');
		}

		$mgr = $modx->getManager();
        $mgr->createObjectContainer('bxAd');
        $mgr->createObjectContainer('bxPosition');
        $mgr->createObjectContainer('bxAdPosition');
        $mgr->createObjectContainer('bxClick');

		$success = true;
		break;
}