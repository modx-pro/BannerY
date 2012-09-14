<?php
require_once dirname(__FILE__) . '/model/bannery/bannery.class.php';
abstract class BanneryManagerController extends modExtraManagerController {
    /** @var BannerY $bannery */
    public $bannery;
    public function initialize() {
        $this->bannery = new BannerY($this->modx);
 
        $this->addJavascript($this->bannery->config['jsUrl'].'mgr/bannery.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            Bannery.config = '.$this->modx->toJSON($this->bannery->config).';
        });
        </script>');
        return parent::initialize();
    }
    public function getLanguageTopics() {
        return array('bannery:default');
    }
    public function checkPermissions() { return true;}
}
class IndexManagerController extends BanneryManagerController {
    public static function getDefaultController() { return 'home'; }
}