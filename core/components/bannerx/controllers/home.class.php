<?php
class BannerxHomeManagerController extends BannerxManagerController {
    public function process(array $scriptProperties = array()) {
 
    }
    public function getPageTitle() { return $this->modx->lexicon('bannerx'); }
    public function loadCustomCssJs() {
        $this->addJavascript($this->bannerx->config['jsUrl'].'mgr/widgets/ads.grid.js');
        $this->addJavascript($this->bannerx->config['jsUrl'].'mgr/widgets/positions.grid.js');
        $this->addJavascript($this->bannerx->config['jsUrl'].'mgr/widgets/referrers.grid.js');
        $this->addJavascript($this->bannerx->config['jsUrl'].'mgr/widgets/stats.panel.js');
        $this->addJavascript($this->bannerx->config['jsUrl'].'mgr/widgets/home.panel.js');
        $this->addLastJavascript($this->bannerx->config['jsUrl'].'mgr/sections/index.js');
    }
    public function getTemplateFile() {
        return $this->bannerx->config['templatesPath'].'home.tpl';
    }
}