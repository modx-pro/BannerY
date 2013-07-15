<?php
class BanneryHomeManagerController extends BanneryManagerController {
    public function process(array $scriptProperties = array()) {
 
    }
    public function getPageTitle() { return $this->modx->lexicon('bannery'); }
    public function loadCustomCssJs() {
        $this->addJavascript($this->bannery->config['jsUrl'].'mgr/plugins/dragdropgrid.js');
        $this->addJavascript($this->bannery->config['jsUrl'].'mgr/widgets/banners.grid.js');
        $this->addJavascript($this->bannery->config['jsUrl'].'mgr/widgets/positions.grid.js');
        $this->addJavascript($this->bannery->config['jsUrl'].'mgr/widgets/referrers.grid.js');
        $this->addJavascript($this->bannery->config['jsUrl'].'mgr/widgets/stats.panel.js');
        $this->addJavascript($this->bannery->config['jsUrl'].'mgr/widgets/home.panel.js');
        $this->addLastJavascript($this->bannery->config['jsUrl'].'mgr/sections/index.js');
    }
    public function getTemplateFile() {
        return $this->bannery->config['templatesPath'].'home.tpl';
    }
}