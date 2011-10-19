<?php
class IndexController extends Zend_Controller_Action
{
    protected $_config  = null;

	/**
	 * Обработка вызовов несуществующих действий
	 *
	 * @param string $method
	 * @param array $args
	 */
	public function __call($method, $args)
	{
	    $this->getResponse()->setHttpResponseCode(404);
	    $this->_helper->layout->setLayout('404');
	}

    public function init()
    {
        $this->_config = new Zend_Config_Ini(CONFIG_FILE, APPLICATION_ENV);
    }

    public function indexAction()
    {
        $mongoDB = new Application_Model_Mongodb($this->_config->mongo->DBname, $this->_config->mongo->collection);
        $layout = Zend_Layout::getMvcInstance();
        $contacts = $mongoDB->findOne('contacts');
        if (! is_null($contacts))
            $layout->contacts = $contacts['value'];
        $tags = $mongoDB->findOne('tags');
        if (! is_null($tags))
            $layout->tags = $tags['value'];
    }
}