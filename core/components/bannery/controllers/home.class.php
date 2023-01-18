<?php

class BanneryHomeManagerController extends modExtraManagerController
{

    /** @var BannerY $bannery */
    public $bannery;


    public function initialize()
    {
        if (!class_exists('BannerY')) {
            require_once dirname(__DIR__) . '/model/bannery/bannery.class.php';
        }
        $this->bannery = new BannerY($this->modx);

        $this->addJavascript($this->bannery->config['jsUrl'] . 'mgr/bannery.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            Bannery.config = ' . json_encode($this->bannery->config) . ';
        });
        </script>');

        parent::initialize();
    }


    public function getLanguageTopics()
    {
        return array('bannery:default');
    }


    public function checkPermissions()
    {
        return true;
    }


    public function getPageTitle()
    {
        return $this->modx->lexicon('bannery');
    }


    public function loadCustomCssJs()
    {
        $this->addCss($this->bannery->config['cssUrl'] . 'mgr/main.css');
        $this->addLastJavascript($this->bannery->config['jsUrl'] . 'mgr/plugins/dragdropgrid.js');
        $this->addLastJavascript($this->bannery->config['jsUrl'] . 'mgr/widgets/banners.grid.js');
        $this->addLastJavascript($this->bannery->config['jsUrl'] . 'mgr/widgets/positions.grid.js');
        $this->addLastJavascript($this->bannery->config['jsUrl'] . 'mgr/widgets/referrers.grid.js');
        $this->addLastJavascript($this->bannery->config['jsUrl'] . 'mgr/widgets/stats.panel.js');
        $this->addLastJavascript($this->bannery->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addLastJavascript($this->bannery->config['jsUrl'] . 'mgr/sections/index.js');
        $this->addLastJavascript(MODX_MANAGER_URL . 'assets/modext/util/datetime.js');
    }


    public function getTemplateFile()
    {
        return $this->bannery->config['templatesPath'] . 'home.tpl';
    }
}