<?php
/**
 * Контроллер действий обрабатывающий запросы через AJAX
 *
 */
class AjaxController extends Zend_Controller_Action
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
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        if (! $this->getRequest()->isXmlHttpRequest())
            return $this->getResponse()->setHttpResponseCode(415);

    	$this->_config = new Zend_Config_Ini(CONFIG_FILE, APPLICATION_ENV);
    	$this->getResponse()->setHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Получение дерева статей в формате JSON
     *
     * @return void
     */
    public function treearticlesAction()
    {
        if (! $this->getRequest()->isGet())
            return $this->getResponse()->setHttpResponseCode(415);

        $articlesModel = new Application_Model_Articles();
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->appendBody($articlesModel->getTreeArticles());
    }

    /**
     * Получение списка всех ключей из MongoDB
     * 
     * В левой части страницы элемент Ext.view.View загружает список ключей
     * по адресу /ajax/dumpKeysList
     *
     * @return void
     */
    public function dumpkeyslistAction()
    {
        if (! $this->getRequest()->isGet())
            return $this->getResponse()->setHttpResponseCode(415);

        try {
            $mongoDB = new Application_Model_Mongodb($this->_config->mongo->DBname, $this->_config->mongo->collection);
        } catch (MongoDBException $ex) {
            $error = sprintf('<font color="red">%s</font>', $ex->getMessage());
            return $this->getResponse()
                ->setHeader('Content-Type', 'application/json; charset=UTF-8')
                ->appendBody(Zend_Json::encode(
                    $answer = array('keys' => array('key' => $error))));
        }
        $keys = $mongoDB->find();
        $answer = array('keys');
        foreach ($keys as $key)
            $answer['keys'][] = array('key' => $key['key']);
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->appendBody(Zend_Json::encode($answer));
    }

    /**
     * Получение документа из MongoDB по ключу
     *
     * @return void
     */
    public function dumpgetdocumentAction()
    {
        if (! $this->getRequest()->isGet())
            return $this->getResponse()->setHttpResponseCode(415);

        if (is_null($key = $this->getRequest()->getParam('key')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param key');

        $mongoDB = new Application_Model_Mongodb($this->_config->mongo->DBname, $this->_config->mongo->collection);
        $cursor = $mongoDB->findOne($key);
        if (is_null($cursor))
            return $this->getResponse()
                ->setHttpResponseCode(400);

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->appendBody(Zend_Json::encode(
                array('key' => $cursor['key'], 'value' => $cursor['value'])
            ));
    }

    /**
     * Сохранение документа в MongoDB
     *
     * @return void
     */
    public function dumpsetdocumentAction()
    {
        if (! $this->getRequest()->isPost())
            return $this->getResponse()->setHttpResponseCode(415);
        if (is_null($key = $this->getRequest()->getPost('key')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param key');
        if (is_null($value = $this->getRequest()->getPost('value')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param value');

        $mongoDB = new Application_Model_Mongodb($this->_config->mongo->DBname, $this->_config->mongo->collection);
        try {
            $mongoDB->update($key, $value);
        } catch (MongoDBKeyNotFound $ex) {
            return $this->getResponse()
                ->setHttpResponseCode(400);
        }
        $this->getResponse()
            ->setHttpResponseCode(204);
    }
}