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
    	$this->_config = Application_Model_MemcachedConfig::getInstance();
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
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->title = 'Редактирование статей';
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
			->appendBody(json_encode($this->_articles->getArticleByID($id)->toArray()));
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
			->appendBody(Zend_Json::encode($this->_articles->getTreeArticles()));
    }

    /**
     * Вызов страницы для работы с категориями сайта
     * 
     * @link http://{HOST}/admin/categories
     *
     */
    public function categoriesAction()
    {
    	$this->_helper->layout->setLayout('layout-admin-pages');
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->title = 'Редактирование категорий';
    	$this->view->categoriesListRoot = Application_Model_Categories::stmt2selectEncode(
    		$this->_categories->getCategoriesRoot(), 'name', 'name');
    	$this->view->categoriesList = Application_Model_Categories::stmt2selectEncode(
    	   $this->_categories->getCategories(), 'name', 'name');
        $this->view->categoriesChildList = Application_Model_Categories::stmt2selectEncode(
    	   $this->_categories->getCategories(), 'name', 'name');
        $metaData = $this->_categories->metaData();
        $this->view->cntAll = $metaData['cntAll'];
        $this->view->cntRoot = $metaData['cntRoot'];
    }

    /**
     * Вызов страницы для работы с mongoDB
     * 
     * @link http://{HOST}/admin/dump
     *
     */
    public function dumpAction()
    {
    	$this->_helper->layout->setLayout('layout-admin-pages');
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->title = 'Редактирование служебной информации';
    }

    /**
     * Вызов страницы для работы с пользователями
     *
     * @link http://{HOST}/admin/users
     */
    public function usersAction()
    {
        $this->_helper->layout->setLayout('layout-admin-pages');
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->title = 'Управление пользователями';
    }
}