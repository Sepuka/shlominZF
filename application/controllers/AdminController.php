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

    public function init()
    {
    	# Подключение системы контроля доступа
    	$this->_ACL = new Application_Model_Acl();
    	# Подключение модели для работы с категориями
    	$this->_categories = new Application_Model_Categories();
    	# Подключение модели для работы со статьями
    	$this->_articles = new Application_Model_Articles();
    }

    /**
     * Преддиспетчер
     *
     * Проверяет права пользователя и перенаправляет на страницу входа если требуется
     */
    public function preDispatch()
    {
    	$client = $this->_ACL->getClient();
    	// Перенаправление на login для авторизации
    	if ($this->getRequest()->getActionName() != 'login')
    		if (! $this->_ACL->isAllowed($client->role, 'admin', 'view'))
				return $this->getResponse()->setRedirect('admin/login');
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
    	if ($this->getRequest()->isPost())
    		$this->_ACL->login($this->getRequest());
    	$client = $this->_ACL->getClient();
    	# Если права появились - перекидываем клиента в админку
    	if ($this->_ACL->isAllowed($client->role, 'admin', 'view'))
			return $this->getResponse()->setRedirect('/admin');

    	$this->_helper->layout->setLayout('layout-admin-login');
    	if ($this->_ACL->wrongData)
    		$this->view->wrongData = true;
    }

    /**
     * Действие выхода (разлогинивание)
     *
     */
    public function logoutAction()
    {
    	setcookie('auth', null, 0, '/');
    	#session_destroy();
    	$this->_ACL->destroySession();
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

    public function articlesviewAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	echo $this->_articles->getTreeArticles();
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
    	$this->view->categoriesListRoot = $this->_categories->getCategoriesListRoot();
    	$this->view->categoriesListParent = $this->_categories->getCategoriesFolder();
    }

    /**
     * Получение списка категорий для таблицы в формате JSON
     *
     */
    public function categoriesviewAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	$category = $this->_request->getQuery('category');
    	$type = $this->_request->getQuery('type');
    	if (!empty($category))
    		echo $this->_categories->getCategoriesListSpecified($category);
    	elseif (isset($type))
    		echo $this->_categories->getCategoriesListType($type);
    	else
    		echo $this->_categories->viewCategories();
    }

    /**
     * Редактирование категорий
     *
     */
    public function categorieseditAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	echo ($this->_categories->editCategories($this->_request)) ? 'Изменения сохранены' : 'Ошибка сохранения изменений';
    }

    /**
     * Добавление категорий
     *
     */
    public function categoriesaddAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	echo ($this->_categories->addCategories($this->_request)) ? 'Изменения сохранены' : 'Ошибка сохранения изменений';
    }

    /**
     * Удаление категорий
     *
     */
    public function categoriesdelAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	echo ($this->_categories->delCategories($this->_request)) ? 'Категория успешно удалена' : 'Ошибка удаления категории';
    }
}

