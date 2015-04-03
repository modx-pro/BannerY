<?php

$settings = array();

$tmp = array(
	'click' => array(
		'xtype' => 'textfield',
		'value' => 'bannerclick',
		'area' => PKG_NAME_LOWER.'_main',
	),
	'media_source' => array(
		'xtype' => 'numberfield',
		'value' => '',
		'area' => PKG_NAME_LOWER.'_main',
	),
);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => PKG_NAME_LOWER.'_'.$k,
			'namespace' => PKG_NAME_LOWER,
		), $v
	),'',true,true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;