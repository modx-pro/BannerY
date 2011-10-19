<?php
$position = $modx->getObject('bxPosition', array('id' => $scriptProperties['id']));
if(!is_object($position)) {
    $position = $modx->newObject('bxPosition');
}
$position->fromArray($scriptProperties);

if ($position->save()) {
	return $modx->error->success('', $position);
}
else {
	return $modx->error->failure('');
}