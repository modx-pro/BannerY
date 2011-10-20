<?php
$ad = $modx->getObject('bxAd', array('id' => $scriptProperties['id']));

if(!is_object($ad)) {
    $ad = $modx->newObject('bxAd');
}
$ad->fromArray($scriptProperties);
if ($ad->save()) {

    if(is_array($scriptProperties['positions'])) {
        $modx->removeCollection('bxAdPosition',
                                array(
                                    'ad' => $ad->get('id'),
                                    'position NOT IN('.implode(',', $scriptProperties['positions']).')'
                                )
        );
        foreach($scriptProperties['positions'] as $position) {
            $adPos = $modx->getObject('bxAdPosition', array(
                                                        'ad' => $ad->get('id'),
                                                        'position' => $position
                                                      ));
            if(!is_object($adPos)) {
                $adPos = $modx->newObject('bxAdPosition');
            }
            $adPos->fromArray(array(
                                'ad' => $ad->get('id'),
                                'position' => $position
                              ));
            $adPos->save();
        }
    }
    else {
        $modx->removeCollection('bxAdPosition', array('ad' => $ad->get('id')));
    }
    return $modx->error->success('', $ad);
}
else {
    return $modx->error->failure('');
}