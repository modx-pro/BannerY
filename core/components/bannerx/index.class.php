<?php
require_once dirname(__FILE__) . '/model/bannerx/bannerx.class.php';
abstract class BannerxManagerController extends modExtraManagerController {
    /** @var BannerX $bannerx */
    public $bannerx;
    public function initialize() {
        $this->bannerx = new BannerX($this->modx);
 
        $this->addJavascript($this->bannerx->config['jsUrl'].'mgr/bannerx.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            Bannerx.config = '.$this->modx->toJSON($this->bannerx->config).';
        });
        </script>');
        return parent::initialize();
    }
    public function getLanguageTopics() {
        return array('bannerx:default');
    }
    public function checkPermissions() { return true;}
}
class IndexManagerController extends BannerxManagerController {
    public static function getDefaultController() { return 'home'; }
}