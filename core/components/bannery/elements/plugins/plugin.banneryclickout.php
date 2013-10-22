<?php
if ($modx->event->name == 'OnPageNotFound') {
	if(preg_match('/bannerclick\/[0-9]+/', $_SERVER['REQUEST_URI'])) {
		$id = end(explode('/', strtok($_SERVER['REQUEST_URI'], '?')));

		$modx->addPackage('bannery', $modx->getOption('core_path').'components/bannery/model/');
		$c = $modx->newQuery('byAd');
		$c->select('byAd.id, pos.position, byAd.url');
		$c->leftJoin('byAdPosition', 'pos', 'pos.ad=byAd.id');
		$c->where(array('pos.id' => $id));
		$c->limit(1);
		$ad = $modx->getObject('byAd', $c);
		if(is_object($ad)) {
			$clickCount = $modx->getCount('byClick', array(
				'ad' => $ad->get('id'),
				'position' => $ad->get('position'),
				'ip' => $_SERVER['REMOTE_ADDR'],
				'clickdate:LIKE' => strftime("%Y-%m-%d").'%'
			));
			if($clickCount == 0) {
				$click = $modx->newObject('byClick');
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
			$chunk->set('name','banneryPosition'.$id);
			$chunk->setContent($url);
			$url = $chunk->process($_GET);

			$modx->sendRedirect($url);
		}
	}
}
