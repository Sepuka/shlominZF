<?php
/**
 * Контроллер действий обрабатывающий запросы на получение
 * конкретных статей
 *
 */
class ArticleController extends Zend_Controller_Action
{
    protected $_articleModel    = null;

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
        $this->_articleModel = new Application_Model_Articles();
    	$this->getResponse()->setHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Действие показывающее запрошенную статью
     *
     * @return void
     */
    public function viewAction()
    {
        if (! $this->getRequest()->isGet())
    		return $this->getResponse()->setHttpResponseCode(415);

        if (is_null($id = $this->getRequest()->getParam('id')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param id');

        $layout = Zend_Layout::getMvcInstance();
        $article = $this->_articleModel->getArticleByID($id);
        if (is_null($article)) {
            $this->_helper->layout->setLayout('404');
            return $this->getResponse()
                ->setHttpResponseCode(404);
        }
        $layout->articleHeadline = $article->headline;
        $layout->articleContent = $article->content;
    }
}