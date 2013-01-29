<?php
/**
 * @package bannery
 */
class BannerY {
	/**
	 * Constructs the BannerY object
	 *
	 * @param modX &$modx A reference to the modX object
	 * @param array $config An array of configuration options
	 */
	function __construct(modX &$modx,array $config = array()) {
		$this->modx =& $modx;

		$basePath = $this->modx->getOption('bannery.core_path',$config,$this->modx->getOption('core_path').'components/bannery/');
		$assetsUrl = $this->modx->getOption('bannery.assets_url',$config,$this->modx->getOption('assets_url').'components/bannery/');

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
			'managerUrl' => $this->modx->getOption('manager_url'),
			'media_source' => $this->modx->getOption('bannery.media_source', null, $this->modx->getOption('default_media_source'))
		),$config);

		$this->modx->addPackage('bannery',$this->config['modelPath']);
	}


	/**
	 * Refreshes order of ads in position after various actions with them
	 *
	 * @param integer $position An id of position
	 */
	function refreshIdx($position = 0) {
		$q = $this->modx->newQuery('byAdPosition');
		$q ->where(array('position' => $position));
		$q->sortby('idx','ASC');
		
		$res = $this->modx->getCollection('byAdPosition', $q);
		$i = 0;
		foreach ($res as $v) {
			$v->set('idx', $i);
			$v->save();
			$i++;
		}
	}
}