<?php
/**
 * Контроллер действий обрабатывающий запросы через AJAX
 *
 */
class AjaxController extends Zend_Controller_Action
{
    protected $_articlesModel   = null;

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
        # Подключение модели для работы со статьями
    	$this->_articlesModel = new Application_Model_Articles();
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
        if (! $this->getRequest()->isXmlHttpRequest())
            return $this->getResponse()->setHttpResponseCode(415);

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->appendBody($this->_articlesModel->getTreeArticles());
    }
}