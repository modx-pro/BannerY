<?php
if ($modx->event->name == 'OnPageNotFound') {
	$bannery_click = $modx->getOption('bannery_click', null, 'bannerclick', true);
	if (preg_match('/'.$bannery_click.'\/([0-9]+)/', $_SERVER['REQUEST_URI'], $matches)) {
		$modx->addPackage('bannery', $modx->getOption('core_path') . 'components/bannery/model/');

		$id = $matches[1];
		$c = $modx->newQuery('byAd');
		$c->select('`byAd`.`id`, `Position`.`position`, `byAd`.`url`');
		$c->leftJoin('byAdPosition', 'Position', 'Position.ad = byAd.id');
		$c->where(array('Position.id' => $id));
		if ($ad = $modx->getObject('byAd', $c)) {
			$key = array(
				'ad' => $ad->get('id'),
				'position' => $ad->get('position'),
				'ip' => $_SERVER['REMOTE_ADDR'],
				'clickdate:LIKE' => date('Y-m-d') . '%'
			);
			if (!$modx->getCount('byClick', $key)) {
				$click = $modx->newObject('byClick', array(
					'ad' => $ad->get('id'),
					'position' => $ad->get('position'),
					'clickdate' => date('Y-m-d H:i:s'),
					'referrer' => $_SERVER['HTTP_REFERER'],
					'ip' => $_SERVER['REMOTE_ADDR']
				));
				$click->save();
			}
			$url = $ad->get('url');
			$chunk = $modx->newObject('modChunk');
			$chunk->set('name', 'banneryPosition' . $id);
			$chunk->setContent($url);
			$url = $chunk->process($_GET);

			$modx->sendRedirect($url);
		}
	}
}
