<?php
class AdDisableProcessor extends modObjectProcessor {
	public $classKey = 'byAd';
	public $languageTopics = array('bannery:default');
	public $objectType = 'bannery.ad';
	public $checkSavePermission = true;

	function initialize() {
		$primaryKey = $this->getProperty($this->primaryKeyField,false);
		if (empty($primaryKey)) return $this->modx->lexicon($this->objectType.'_err_ns');
		$this->object = $this->modx->getObject($this->classKey,$primaryKey);
		if (empty($this->object)) return $this->modx->lexicon($this->objectType.'_err_nfs',array($this->primaryKeyField => $primaryKey));

		if ($this->checkSavePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('save')) {
			return $this->modx->lexicon('access_denied');
		}
		return true;
	}
	
	function process() {
		$this->object->set('active', 0);
		$this->object->save();
		
		return $this->modx->error->success('');
	}
}
return 'AdDisableProcessor';