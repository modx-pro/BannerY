<?php
/**
 * @package bannerx
 * @subpackage build
 */
$events = array();

$events['OnPageNotFound']= $modx->newObject('modPluginEvent');
$events['OnPageNotFound']->fromArray(array(
    'event' => 'OnPageNotFound',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

return $events;