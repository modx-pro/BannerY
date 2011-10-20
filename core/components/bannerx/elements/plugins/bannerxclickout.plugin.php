<?php
if ($modx->event->name == 'OnPageNotFound') {
    if(strpos($_SERVER['REQUEST_URI'], 'bannerclick')) {
        if(preg_match('/bannerclick\/[0-9]+$/', $_SERVER['REQUEST_URI'])) {
            $id = explode('/', $_SERVER['REQUEST_URI']);
            $id = end($id);

            $modx->addPackage('bannerx', $modx->getOption('core_path').'components/bannerx/model/');
            $c = $modx->newQuery('bxAd');
            $c->select('bxAd.id, pos.position, bxAd.url');
            $c->leftJoin('bxAdPosition', 'pos', 'pos.ad=bxAd.id');
            $c->where(array('pos.id' => $id));
            $c->limit(1);
            $ad = $modx->getObject('bxAd', $c);
            if(is_object($ad)) {
                $click = $modx->newObject('bxclick');
                $click->fromArray(
                    array(
                        'ad' => $ad->get('id'),
                        'position' => $ad->get('position'),
                        'clickdate' => strftime("%Y-%m-%d %H:%M:%S"),
                        'referer' => $_SERVER['HTTP_REFERER'],
                        'ip' => $_SERVER['REMOTE_ADDR']
                    )
                );
                $click->save();
                $modx->sendRedirect($ad->get('url'));
            }
        }
    }
}