<?php
/**
 * BannerX Connector
 *
 * @package bannerx
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$corePath = $modx->getOption('bannerx.core_path',null,$modx->getOption('core_path').'components/bannerx/');
require_once $corePath.'model/bannerx/bannerx.class.php';
$modx->bannerx = new BannerX($modx);

$modx->lexicon->load('bannerx:default');

/* handle request */
$path = $modx->getOption('processorsPath',$modx->bannerx->config,$corePath.'processors/');
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));