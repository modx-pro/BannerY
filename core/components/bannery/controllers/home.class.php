<?php
class BanneryHomeManagerController extends BanneryManagerController {

	public function process(array $scriptProperties = array()) {}


	public function getPageTitle() { return $this->modx->lexicon('bannery'); }


	public function loadCustomCssJs() {
		$modx23 = !empty($this->modx->version) && version_compare($this->modx->version['full_version'], '2.3.0', '>=');
		$this->modx->controller->addHtml('<script type="text/javascript">
			Ext.onReady(function() {
				MODx.modx23 = '.(int)$modx23.';
			});
		</script>');
		if (!$modx23) {
			$this->addCss($this->bannery->config['cssUrl'] . 'mgr/font-awesome.min.css');
		}
		$this->addCss($this->bannery->config['cssUrl']. 'mgr/main.css');
		$this->addLastJavascript($this->bannery->config['jsUrl'].'mgr/plugins/dragdropgrid.js');
		$this->addLastJavascript($this->bannery->config['jsUrl'].'mgr/widgets/banners.grid.js');
		$this->addLastJavascript($this->bannery->config['jsUrl'].'mgr/widgets/positions.grid.js');
		$this->addLastJavascript($this->bannery->config['jsUrl'].'mgr/widgets/referrers.grid.js');
		$this->addLastJavascript($this->bannery->config['jsUrl'].'mgr/widgets/stats.panel.js');
		$this->addLastJavascript($this->bannery->config['jsUrl'].'mgr/widgets/home.panel.js');
		$this->addLastJavascript($this->bannery->config['jsUrl'].'mgr/sections/index.js');
		$this->addLastJavascript(MODX_MANAGER_URL . 'assets/modext/util/datetime.js');
	}


	public function getTemplateFile() {
		return $this->bannery->config['templatesPath'].'home.tpl';
	}
}