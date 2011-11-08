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

    	$this->_config = Application_Model_MemcachedConfig::getInstance();
    	$this->getResponse()->setHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Получение дерева статей в формате JSON
     * 
     * Запрос этого метода может происходить из админки, с главной страницы сайта
     * или со страницы конкретной статьи (/article/37). В последнем случае нам
     * нужен идентификатор статьи (articleID) чтобы раскрыть дерево в нужном месте.
     *
     * @return void
     */
    public function treearticlesAction()
    {
        if (! $this->getRequest()->isGet())
            return $this->getResponse()->setHttpResponseCode(415);
        if ($articleID = $this->getRequest()->getParam('articleID'))
            $articleID = array_pop(explode('/', $articleID));
        else
            $articleID = null;

        $articlesModel = new Application_Model_Articles();
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->appendBody(Zend_Json::encode($articlesModel->getTreeArticles($articleID)));
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
            $mongoDB = new Application_Model_Mongodb($this->_config->mongo->DBname, $this->_config->mongo->docs->collection,
                'localhost', $this->_config->mongo->conn->user, $this->_config->mongo->conn->pass);
        } catch (MongoDBException $ex) {
            $error = sprintf('<font color="red">%s</font><br>', $ex->getMessage());
            return $this->getResponse()
                ->setHeader('Content-Type', 'application/json; charset=UTF-8')
                ->appendBody(Zend_Json::encode(
                    $answer = array('keys' => array(
                        'success'   => false,
                        'key'       => $error,
                        'total'     => 1)
                    )));
        }
        $keys = $mongoDB->find();
        $answer = array('keys' => array());
        $answer['keys'][] = array('success' => true);
        $answer['keys'][] = array('total' => $keys->count());
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

        try {
            $mongoDB = new Application_Model_Mongodb($this->_config->mongo->DBname, $this->_config->mongo->docs->collection,
                'localhost', $this->_config->mongo->conn->user, $this->_config->mongo->conn->pass);
        } catch (MongoDBException $ex) {
            return $this->getResponse()
                ->setHttpResponseCode(500)
                ->setHeader('Content-Type', 'application/json; charset=UTF-8')
                ->appendBody(Zend_Json::encode(
                    $answer = array(
                        'success'   => false,
                        'key'       => $ex->getMessage(),
                        'value'     => $ex->getTraceAsString())
                    ));
        }
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
     * Обновление документа в MongoDB
     *
     * @return void
     */
    public function dumpupdatedocumentAction()
    {
        if (! $this->getRequest()->isPost())
            return $this->getResponse()->setHttpResponseCode(415);
        if (is_null($key = $this->getRequest()->getPost('key')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param key');
        if (empty($key))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->setHeader('Content-Type', 'application/json; charset=UTF-8')
                ->appendBody(Zend_Json::encode(
                    $answer = array(
                        'success'   => false,
                        'key'       => 'Ключ документа не указан',
                        'value'     => 'Вы должны выбрать документ который собираетесь изменить')
                    ));
        if (is_null($value = $this->getRequest()->getPost('value')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param value');

        try {
            $mongoDB = new Application_Model_Mongodb(
                $this->_config->mongo->DBname, $this->_config->mongo->docs->collection,
                'localhost', $this->_config->mongo->conn->user, $this->_config->mongo->conn->pass);
            $mongoDB->update($key, $value);
         } catch (MongoDBException $ex) {
             return $this->getResponse()
                ->setHttpResponseCode(500)
                ->setHeader('Content-Type', 'application/json; charset=UTF-8')
                ->appendBody(Zend_Json::encode(
                    $answer = array(
                        'success'   => false,
                        'key'       => $ex->getMessage(),
                        'value'     => $ex->getTraceAsString())
                    ));
         }
        $this->getResponse()
            ->setHttpResponseCode(204);
    }

    /**
     * Работа со статьями
     */

    /**
     * Сохранение статьи
     *
     * @return void
     */
    public function articlessaveAction()
    {
        if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);

        if (is_null($id = $this->getRequest()->getPost('id')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param id');
        if (is_null($categoryID = $this->getRequest()->getPost('categoryID')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param categoryID');
        if (is_null($headline = $this->getRequest()->getPost('headline')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param headline');
        if (is_null($content = $this->getRequest()->getPost('content')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param content');

    	try {
    	   Application_Model_Articles::updateArticle($id, $categoryID, $headline, $content);
    	} catch (ArticleException $ex) {
    	    return $this->getResponse()
                ->setHttpResponseCode(400);
    	} catch (Exception $ex) {
    	    return $this->getResponse()
                ->setHttpResponseCode(500);
    	}
    	$this->getResponse()
            ->setHttpResponseCode(204);
    }

    /**
     * Удаление статьи
     *
     * @return void
     */
    public function articlesremoveAction()
    {

        if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);

        if (is_null($id = $this->getRequest()->getPost('id')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param id');

    	try {
    	   Application_Model_Articles::removeArticle($id);
    	} catch (ArticleException $ex) {
    	    return $this->getResponse()->setHttpResponseCode(400);
    	} catch (Exception $ex) {
    	    return $this->getResponse()->setHttpResponseCode(500);
    	}
    	$this->getResponse()
            ->setHttpResponseCode(204);
    }
}