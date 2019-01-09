<?php

$menus = array();

$tmp = array(
	'BannerY' => array(
		'description' => 'bannery.desc',
		'action' => 'home',
	),
);

foreach ($tmp as $k => $v) {
    /** @var modMenu $menu */
    $menu = $modx->newObject('modMenu');
    $menu->fromArray(array_merge(array(
        'text' => $k,
        'parent' => 'components',
        'namespace' => PKG_NAME_LOWER,
        'icon' => '',
        'menuindex' => 0,
        'params' => '',
        'handler' => '',
    ), $v), '', true, true);
	$menus[] = $menu;
}
unset($action, $menu);

return $menus;