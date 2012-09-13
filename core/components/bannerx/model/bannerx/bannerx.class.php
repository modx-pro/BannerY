<?php
/**
 * @package bannerx
 */
class BannerX {
	/**
	 * Constructs the BannerX object
	 *
	 * @param modX &$modx A reference to the modX object
	 * @param array $config An array of configuration options
	 */
	function __construct(modX &$modx,array $config = array()) {
		$this->modx =& $modx;

		$basePath = $this->modx->getOption('bannerx.core_path',$config,$this->modx->getOption('core_path').'components/bannerx/');
		$assetsUrl = $this->modx->getOption('bannerx.assets_url',$config,$this->modx->getOption('assets_url').'components/bannerx/');

		$this->config = array_merge(array(
			'baseUrl' => $modx->getOption('base_url'),
			'basePath' => $basePath,
			'corePath' => $basePath,
			'modelPath' => $basePath.'model/',
			'processorsPath' => $basePath.'processors/',
			'templatesPath' => $basePath.'templates/',
			'chunksPath' => $basePath.'elements/chunks/',
			'jsUrl' => $assetsUrl.'js/',
			'cssUrl' => $assetsUrl.'css/',
			'assetsUrl' => $assetsUrl,
			'connectorUrl' => $assetsUrl.'connector.php',
		),$config);

		$this->modx->addPackage('bannerx',$this->config['modelPath']);
	}


	/**
	 * Refreshes order of ads in position after various actions with them
	 *
	 * @param integer $position An id of position
	 */
	function refreshIdx($position = 0) {
		$q = $this->modx->newQuery('bxAdPosition');
		$q ->where(array('position' => $position));
		$q->sortby('idx','ASC');
		
		$res = $this->modx->getCollection('bxAdPosition', $q);
		$i = 0;
		foreach ($res as $v) {
			$v->set('idx', $i);
			$v->save();
			$i++;
		}
	}
}