<?php
/**
 * BannerY Connector
 *
 * @package bannery
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$corePath = $modx->getOption('bannery.core_path',null,$modx->getOption('core_path').'components/bannery/');
require_once $corePath.'model/bannery/bannery.class.php';
$modx->bannery = new BannerY($modx);

$modx->lexicon->load('bannery:default');

/* handle request */
$path = $modx->getOption('processorsPath',$modx->bannery->config,$corePath.'processors/');
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));