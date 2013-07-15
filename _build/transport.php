<?php
/**
 * BannerY build script
 *
 * @package bannery
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0); /* makes sure our script doesn't timeout */

$root = dirname(dirname(__FILE__)).'/';

$sources= array (
    'root' => $root,
    'build' => $root .'_build/',
    'resolvers' => $root . '_build/resolvers/',
    'data' => $root . '_build/data/',
    'source_core' => $root.'core/components/bannery',
    'lexicon' => $root . 'core/components/bannery/lexicon/',
    'source_assets' => $root.'assets/components/bannery',
    'docs' => $root.'core/components/bannery/docs/',
);

unset($root); // save memory

require_once dirname(__FILE__) . '/build.config.php';

require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx= new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

$modx->loadClass('transport.modPackageBuilder','',false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage('bannery','1.1.0','beta');
$builder->registerNamespace('bannery',false,true,'{core_path}components/bannery/');

/* load action/menu */
$menu = include $sources['data'].'transport.menu.php';

$vehicle= $builder->createVehicle($menu,array (
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Action' => array (
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
        ),
    ),
));
$builder->putVehicle($vehicle);
unset($vehicle,$action); /* to keep memory low */

/* create category */
$category= $modx->newObject('modCategory');
$category->set('id',1);
$category->set('category','BannerY');

$snippets = include $sources['data'].'transport.snippets.php';
if (!is_array($snippets)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'Could not package in snippets.');
} else {
    $category->addMany($snippets);
    $modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($snippets).' snippets.');
}

$chunks = include $sources['data'].'transport.chunks.php';
if (!is_array($chunks)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'Could not package in chunks.');
} else {
    $category->addMany($chunks);
    $modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($chunks).' chunks.');
}

$plugins = include $sources['data'].'transport.plugins.php';
if (!is_array($plugins)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'Could not package in plugins.');
} else {
    $category->addMany($plugins);
    $modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($plugins).' plugins.');
}

/* create category vehicle */
$attr = array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Snippets' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
        'Chunks' => array (
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => false,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
        'Plugins' => array (
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
        'PluginEvents' => array(
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
            xPDOTransport::UNIQUE_KEY => array('pluginid','event'),
        )
    )
);
$vehicle = $builder->createVehicle($category,$attr);

$vehicle->resolve('file',array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));
$vehicle->resolve('file',array(
    'source' => $sources['source_assets'],
    'target' => "return MODX_ASSETS_PATH . 'components/';",
));
$vehicle->resolve('php',array(
    'source' => $sources['resolvers'].'postactions.resolver.php',
));
$vehicle->resolve('php',array(
    'source' => $sources['resolvers'].'settings.resolver.php',
));
$builder->putVehicle($vehicle);

/* now pack in the license file, readme and setup options */

$builder->setPackageAttributes(array(
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt')
));

$builder->pack();

$mtime= microtime();
$mtime= explode(" ", $mtime);
$mtime= $mtime[1] + $mtime[0];
$tend= $mtime;
$totalTime= ($tend - $tstart);
$totalTime= sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO,"\nPackage Built.\nExecution time: {$totalTime}\n");
exit();
