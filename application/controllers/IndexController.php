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
        $this->_config = Application_Model_MemcachedConfig::getInstance();
    }

    public function indexAction()
    {
        $mongoDB = new Application_Model_Mongodb($this->_config->mongo->DBname, $this->_config->mongo->docs->collection);
        $layout = Zend_Layout::getMvcInstance();
        $contacts = $mongoDB->findOne('contacts');
        if (! is_null($contacts))
            $layout->contacts = $contacts['value'];
        # Скрипт google analytics
        $googleAnalytics = $mongoDB->findOne('googleAnalytics');
        if (! is_null($googleAnalytics))
            $layout->googleAnalytics = $googleAnalytics['value'];
        # Скрипт яндекс метрика
        $yandexMetrica = $mongoDB->findOne('yandexMetrica');
        if (! is_null($yandexMetrica))
            $layout->yandexMetrica = $yandexMetrica['value'];
        $tags = $mongoDB->findOne('tags');
        if (! is_null($tags))
            $layout->tags = $tags['value'];
    }
}