<?php
/**
 * Контроллер действий обрабатывающий запросы на получение
 * конкретных статей
 *
 */
class ArticleController extends Zend_Controller_Action
{
    protected $_articleModel    = null;
    protected $_config          = null;

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
        $this->_config = Application_Model_MemcachedConfig::getInstance();
        $this->getResponse()->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * Действие показывающее запрошенную статью
     *
     * Обрабатывает URL вида /article/37 которые преобразуются в index.php
     * с помощью маршрутов из application/configs/application.ini
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

        $mongoDB = new Application_Model_Mongodb($this->_config->mongo->DBname, $this->_config->mongo->docs->collection);
        $contacts = $mongoDB->findOne('contacts');
        if (! is_null($contacts))
            $layout->contacts = $contacts['value'];
        $tags = $mongoDB->findOne('tags');
        if (! is_null($tags))
            $layout->tags = $tags['value'];
        # Скрипт google analytics
        $googleAnalytics = $mongoDB->findOne('googleAnalytics');
        if (! is_null($googleAnalytics))
            $layout->googleAnalytics = $googleAnalytics['value'];
        # Скрипт яндекс метрика
        $yandexMetrica = $mongoDB->findOne('yandexMetrica');
        if (! is_null($yandexMetrica))
            $layout->yandexMetrica = $yandexMetrica['value'];
        # Кнопка twitter
        $twitterButton = $mongoDB->findOne('twitterButton');
        if (! is_null($twitterButton))
            $layout->twitterButton = $twitterButton['value'];
    }
}