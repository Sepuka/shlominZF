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
     * Сохранение статей
     *
     */
    public function articlessaveAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	echo ($this->_articles->updateArticle($this->_request)) ? 'Данные успешно сохранены' : 'Ошибка сохранения данных';
    }

    /**
     * Получение дерева категорий
     *
     * Отдает клиенту дерево категорий в формате json
     */
    public function articlesviewAction()
    {
    	if (! $this->getRequest()->isGet())
    		return $this->getResponse()->setHttpResponseCode(415);
    	if (! $this->getRequest()->isXmlHttpRequest())
    		return $this->getResponse()->setHttpResponseCode(415);

    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
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
    	$this->view->categoriesListParent = $this->_categories->getCategoriesFolder();
    }

    /**
     * Получение списка категорий для таблицы в формате JSON через AJAX
     *
     */
    public function categoriesviewAction()
    {
    	if (! $this->getRequest()->isGet())
    		return $this->getResponse()->setHttpResponseCode(415);
    	if (! $this->getRequest()->isXmlHttpRequest())
    		return $this->getResponse()->setHttpResponseCode(415);

    	$this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->disableLayout();

    	$category = $this->getRequest()->getQuery('category');
    	$type = $this->getRequest()->getQuery('type');
    	if (! empty($category))
    		$answer = $this->_categories->getCategoriesListSpecified($category);
    	elseif (isset($type))
    		$answer = $this->_categories->getCategoriesListType($type);
    	else
    		$answer = $this->_categories->getCategories();
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
    	if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);

    	$this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->disableLayout();

    	try {
    		$this->_categories->editCategories($this->getRequest());
    	} catch (CategoriesException $ex) {
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
    	if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);

    	$this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->disableLayout();

    	try {
    		$this->_categories->addCategories($this->getRequest());
    	} catch (CategoriesException $ex) {
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
    	if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);

    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	try {
    		$this->_categories->delCategories($this->_request);
    	} catch (CategoriesException $ex) {
    		return $this->getResponse()
    			->setHttpResponseCode(500);
    	}
    	$this->getResponse()
    		->setHttpResponseCode(204);
    }
}

