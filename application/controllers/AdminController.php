<?php
/**
 * Главный контроллер админки
 *
 */
class AdminController extends Zend_Controller_Action
{
	protected $_ACL			=	null;	# Указатель на объект ACL
	protected $_categories	=	null;	# Указатель на объект модели категорий
	protected $_articles	=	null;	# Указатель на объект модели статей
	protected $_session		=	null;	# Указатель на объект сессии Zend_Session_Namespace
	protected $_config		=	null;	# Массив конфигурации

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
    	# Подключение системы контроля доступа
    	$this->_ACL = new Application_Model_Acl();
    	# Подключение модели для работы с категориями
    	$this->_categories = new Application_Model_Categories();
    	# Подключение модели для работы со статьями
    	$this->_articles = new Application_Model_Articles();
    	# Запускаем сессию для авторизации
    	$this->_session =  new Zend_Session_Namespace();
    	$this->_config = new Zend_Config_Ini(CONFIG_FILE, APPLICATION_ENV);
    	$this->getResponse()->setHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Преддиспетчер
     *
     * Проверяет права пользователя и перенаправляет на страницу входа если требуется
     */
    public function preDispatch()
    {
    	// Перенаправление на login для авторизации
    	if ($this->getRequest()->getActionName() != 'login') {
    		$auth = Zend_Auth::getInstance();
    		if (! $auth->hasIdentity())
    			return $this->getResponse()->setRedirect('/admin/login');
    		if (! $this->_ACL->isAllowed($this->_session->role, 'admin', 'view'))
				return $this->getResponse()->setRedirect('/admin/login');
    	}
    }

    /**
     * Индексная страница админки
     *
     */
    public function indexAction()
    {
        $this->_helper->layout->setLayout('layout-admin');
    }

    /**
     * Попытка входа в админку
     *
     */
    public function loginAction()
    {
    	if ($this->getRequest()->isPost()) {
    		Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
    		$authAdapter = new Zend_Auth_Adapter_DbTable();
    		$authAdapter->setTableName('acl');
    		$authAdapter->setIdentityColumn('login');
    		$authAdapter->setCredentialColumn('hash');
    		$authAdapter->setIdentity($this->getRequest()->getPost('login'));
    		$authAdapter->setCredential(md5($this->getRequest()->getPost('password') . $this->_config->salt));
    		$auth = Zend_Auth::getInstance();
    		$authResult = $auth->authenticate($authAdapter);

    		if ($authResult->isValid()) {
    			$user = Application_Model_Acldb::get($authResult->getIdentity());
    			$this->_session->role = $user->role;
    			if ($this->getRequest()->getPost('saveme'))
    				Zend_Session::rememberMe();
    			$this->getResponse()->setRedirect('/admin');
    		} else {
    			switch ($authResult->getCode()) {
    				case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
    					$this->view->wrongData = 'Пользователя с таким логином не существует';
    				break;

    				case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
    					$this->view->wrongData = 'Некорректно введены данные';
    				break;

					default:
						$this->view->wrongData = 'Ошибка входа';
    				break;
    			}
    		}
    	}
    	$this->_helper->layout->setLayout('layout-admin-login');
    }

    /**
     * Действие выхода (разлогинивание)
     *
     */
    public function logoutAction()
    {
    	Zend_Auth::getInstance()->clearIdentity();
    	Zend_Session::destroy();
    	$this->getResponse()->setRedirect('/');
    }

    /**
     * Просмотр статей и действия над ними
     * 
     * Действие отображает страницу для работы со статьями
     *
     */
    public function articlesAction()
    {
    	$this->_helper->layout->setLayout('layout-admin-pages');
    	$this->view->warnings = $this->_categories->getWarningsCategories();
    }

    /**
     * Получение статьи по идентификатору
     *
     */
    public function getarticleAction()
    {
        if (! $this->getRequest()->isGet())
    		return $this->getResponse()->setHttpResponseCode(415);
    	if (! $this->getRequest()->isXmlHttpRequest())
    		return $this->getResponse()->setHttpResponseCode(415);
    	if (is_null($id = $this->getRequest()->getParam('articleID')))
            return $this->getResponse()->setHttpResponseCode(400);

    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	$this->getResponse()
			->setHeader('Content-Type', 'application/json; charset=UTF-8')
			->appendBody(json_encode($this->_articles->getArticleByID($id))->toArray());
    }

    public function articlesremoveAction()
    {
        $this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();

        if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);
    	if (! $this->getRequest()->isXmlHttpRequest())
    		return $this->getResponse()->setHttpResponseCode(415);
        if (is_null($id = $this->getRequest()->getPost('id')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param id');

    	try {
    	   $this->_articles->removeArticle($id);
    	} catch (ArticleException $ex) {
    	    return $this->getResponse()->setHttpResponseCode(400);
    	} catch (Exception $ex) {
    	    return $this->getResponse()->setHttpResponseCode(500);
    	}
    }

    /**
     * Сохранение статей
     *
     */
    public function articlessaveAction()
    {
        $this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();

        if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);
    	if (! $this->getRequest()->isXmlHttpRequest())
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
    	   $this->_articles->updateArticle($id, $categoryID, $headline, $content);
    	} catch (ArticleException $ex) {
    	    return $this->getResponse()->setHttpResponseCode(400);
    	} catch (Exception $ex) {
    	    return $this->getResponse()->setHttpResponseCode(500);
    	}
    }

    /**
     * Получение дерева категорий
     *
     * Отдает клиенту дерево категорий в формате json
     */
    public function articlesviewAction()
    {
        $this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();

    	if (! $this->getRequest()->isGet())
    		return $this->getResponse()->setHttpResponseCode(415);
    	if (! $this->getRequest()->isXmlHttpRequest())
    		return $this->getResponse()->setHttpResponseCode(415);

    	$this->getResponse()
			->setHeader('Content-Type', 'application/json; charset=UTF-8')
			->appendBody($this->_articles->getTreeArticles());
    }

    /**
     * Просмотр категорий и действий над ними
     * 
     * Действие отображает страницу работы с категориями
     *
     */
    public function categoriesAction()
    {
    	$this->_helper->layout->setLayout('layout-admin-pages');
    	$this->view->categoriesListRoot = Application_Model_Categories::stmt2selectEncode(
    		$this->_categories->getCategoriesRoot(), 'name', 'name');
    	$this->view->categoriesList = Application_Model_Categories::stmt2selectEncode(
    	   $this->_categories->getCategories(), 'name', 'name');
        $this->view->categoriesChildList = Application_Model_Categories::stmt2selectEncode(
    	   $this->_categories->getCategories(), 'name', 'name');
    }

    /**
     * Получение списка категорий для таблицы в формате JSON через AJAX
     *
     */
    public function categoriesviewAction()
    {
        $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->disableLayout();

    	if (! $this->getRequest()->isGet())
    		return $this->getResponse()->setHttpResponseCode(415);
    	if (! $this->getRequest()->isXmlHttpRequest())
    		return $this->getResponse()->setHttpResponseCode(415);

    	$category = $this->getRequest()->getQuery('category');
    	if (! empty($category))
    		$categories = $this->_categories->getCategoriesListSpecified($category);
    	else
    		$categories = $this->_categories->getCategories();
        $categories = $categories->fetchAll();
        $data['total'] = count($categories);
        $data['categories'] = $categories;
        $answer = json_encode($data);
    	$this->getResponse()
			->setHeader('Content-Type', 'application/json; charset=UTF-8')
			->appendBody($answer);
    }

    /**
     * Редактирование категорий
     *
     */
    public function categorieseditAction()
    {
        $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->disableLayout();

    	if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);
		if (is_null($id = $this->getRequest()->getPost('id')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param id');
		if (is_null($sequence = $this->getRequest()->getPost('sequence')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param sequence');
		if (is_null($parent = $this->getRequest()->getPost('parent')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param parent');
		if (is_null($name = $this->getRequest()->getPost('name')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param name');

    	try {
    		$this->_categories->editCategories($id, $sequence, $parent, $name);
    	} catch (Categories_Exception $ex) {
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
     * Добавление категорий
     *
     */
    public function categoriesaddAction()
    {
        $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->disableLayout();

    	if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);
        if (is_null($parent = $this->getRequest()->getPost('parent')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param parent');
        if (is_null($name = $this->getRequest()->getPost('name')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param name');

    	try {
    		$this->_categories->addCategories($parent, $name);
    	} catch (CategoriesException $ex) {
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
     * Удаление категорий
     *
     */
    public function categoriesdelAction()
    {
        $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->disableLayout();

    	if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);
        if (is_null($id = $this->getRequest()->getPost('id')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param id');
    	try {
    		$this->_categories->delCategories($id);
    	} catch (Categories_Exception $ex) {
    		return $this->getResponse()
    			->setHttpResponseCode(400);
    	} catch (Exception $ex) {
    		return $this->getResponse()
    			->setHttpResponseCode(500);
    	}
    	$this->getResponse()
    		->setHttpResponseCode(204);
    }
}

