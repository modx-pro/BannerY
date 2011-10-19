<?php
$ad = $modx->getObject('bxAd', array('id' => $_REQUEST['id']));
if(is_object($ad) && $ad->remove()) {
    return $modx->error->success('');
}
else {
	return $modx->error->failure($modx->lexicon('bannerx.ads.error.nf'));
}