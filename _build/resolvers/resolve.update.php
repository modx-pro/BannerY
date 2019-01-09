<?php
if ($object->xpdo) {
    /** @var modX $modx */
    /** @var array $options */
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_UPGRADE:
            /** @var modAction $action */
            if ($action = $modx->getObject('modAction', array('namespace' => 'bannery'))) {
                $action->remove();
                /** @var modMenu $menu */
                if ($menu = $modx->getObject('modMenu', array('text' => 'bannery'))) {
                    $menu->remove();
                }
                @unlink(MODX_ASSETS_PATH . 'components/bannery/css/mgr/font-awesome.min.css');
                @unlink(MODX_ASSETS_PATH . 'components/bannery/img/_blank.png');
                @rmdir(MODX_ASSETS_PATH . 'components/bannery/img');
                @unlink(MODX_ASSETS_PATH . 'components/bannery/fonts/FontAwesome.otf');
                @unlink(MODX_ASSETS_PATH . 'components/bannery/fonts/fontawesome-webfont.eot');
                @unlink(MODX_ASSETS_PATH . 'components/bannery/fonts/fontawesome-webfont.svg');
                @unlink(MODX_ASSETS_PATH . 'components/bannery/fonts/fontawesome-webfont.ttf');
                @unlink(MODX_ASSETS_PATH . 'components/bannery/fonts/fontawesome-webfont.woff');
                @rmdir(MODX_ASSETS_PATH . 'components/bannery/fonts');
                @unlink(MODX_CORE_PATH . 'components/bannery/index.class.php');
                @unlink(MODX_CORE_PATH . 'components/bannery/model/bannery/request/bannerycontrollerrequest.class.php');
                @rmdir(MODX_CORE_PATH . 'components/bannery/model/bannery/request');
            }
            break;
    }
}

return true;