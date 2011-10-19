<?php
$position = $modx->getObject('bxPosition', array('id' => $_REQUEST['id']));
if(is_object($position) && $position->remove()) {
    return $modx->error->success('');
}
else {
	return $modx->error->failure($modx->lexicon('bannerx.positions.error.nf'));
}