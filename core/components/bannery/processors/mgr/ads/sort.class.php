<?php
class AdSortProcessor extends modObjectProcessor {
	public $classKey = 'byAdPosition';
	public $languageTopics = array('bannery:default');
	public $objectType = 'bannery.ad';
	private $pos;
	public function process() {
		/* @var string $pos */
		$this->pos = $this->getProperty('position');
		/* @var byAd $source */
		$source = $this->modx->getObject($this->classKey, array('ad'=>$this->getProperty('source'), 'position'=>$this->pos));
		/* @var byAd $target */
		$target = $this->modx->getObject($this->classKey, array('ad'=>$this->getProperty('target'), 'position'=>$this->pos));
		if (empty($source) || empty($target)) {
			return $this->modx->error->failure();
		}
		$table = $this->modx->getTableName($this->classKey);
		if ($source->get('idx') < $target->get('idx')) {
			$this->modx->exec("UPDATE {$table}
				SET idx = idx - 1 WHERE
					position = {$this->pos}
					AND idx <= {$target->get('idx')}
					AND idx > {$source->get('idx')}
					AND idx > 0
			");
		} else {
			$this->modx->exec("UPDATE {$table}
				SET idx = idx + 1 WHERE
					position = {$this->pos}
					AND idx >= {$target->get('idx')}
					AND idx < {$source->get('idx')}
			");
		}
		$newRank = $target->get('idx');
		$source->set('idx',$newRank);
		$source->save();
		if (!$this->modx->getCount($this->classKey, array('position' => $this->pos))) {
			$this->setIndex();
		}
		return $this->modx->error->success();
	}
	public function setIndex() {
		$q = $this->modx->newQuery($this->classKey, array('position' => $this->pos));
		$q->select('id');
		$q->sortby('idx ASC, id', 'ASC');
		if ($q->prepare() && $q->stmt->execute()) {
			$ids = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
			$sql = '';
			$table = $this->modx->getTableName($this->classKey);
			foreach ($ids as $k => $id) {
				$sql .= "UPDATE {$table} SET `idx` = '{$k}' WHERE `id` = '{$id}';";
			}
			$this->modx->exec($sql);
		}
	}
}
return 'AdSortProcessor';