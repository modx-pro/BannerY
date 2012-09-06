<?php
if ($modx->event->name == 'OnPageNotFound') {
    if(preg_match('/bannerclick\/[0-9]+/', $_SERVER['REQUEST_URI'])) {
        $id = end(explode('/', strtok($_SERVER['REQUEST_URI'], '?')));

        $modx->addPackage('bannerx', $modx->getOption('core_path').'components/bannerx/model/');
        $c = $modx->newQuery('bxAd');
        $c->select('bxAd.id, pos.position, bxAd.url');
        $c->leftJoin('bxAdPosition', 'pos', 'pos.ad=bxAd.id');
        $c->where(array('pos.id' => $id));
        $c->limit(1);
        $ad = $modx->getObject('bxAd', $c);
        if(is_object($ad)) {

            $clickCount = $modx->getCount('bxClick', array(
                'ad' => $ad->get('id'),
                'position' => $ad->get('position'),
                'ip' => $_SERVER['REMOTE_ADDR'],
                'clickdate:LIKE' => strftime("%Y-%m-%d").'%'
            ));
            if($clickCount == 0) {
                $click = $modx->newObject('bxClick');
                $click->fromArray(
                    array(
                        'ad' => $ad->get('id'),
                        'position' => $ad->get('position'),
                        'clickdate' => strftime("%Y-%m-%d %H:%M:%S"),
                        'referrer' => $_SERVER['HTTP_REFERER'],
                        'ip' => $_SERVER['REMOTE_ADDR']
                    )
                );
                $click->save();
            }
            $url = $ad->get('url');

            $chunk = $modx->newObject('modChunk');
            $chunk->set('name','bannerxPosition'.$id);
            $chunk->setContent($url);
            $url = $chunk->process($_GET);

            $modx->sendRedirect($url);
        }
    }
}
