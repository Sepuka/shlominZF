<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	public function init()
	{
		$this->_initPlaceholders();
	}

	protected function _initPlaceholders()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT');
		$view->headTitle('Шломин.рф')
			->setSeparator(' :: ');
	}
}

