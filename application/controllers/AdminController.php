<?php
/**
 * Главный контроллер админки
 *
 */
class AdminController extends Zend_Controller_Action
{
	protected $_ACL			=	null;	# Указатель на объект ACL
	protected $_categories	=	null;	# Указатель на объект модели категорий

    public function init()
    {
    	# Подключение системы контроля доступа
    	$this->_ACL = new Application_Model_Acl();
    	# Подключение модели для работы с категориями
    	$this->_categories = new Application_Model_Categories();
    }

    /**
     * Преддиспетчер
     *
     */
    public function preDispatch()
    {
    	// Перенаправление на login для авторизации
    	if ($this->getRequest()->getActionName() != 'login')
    		if (!$this->_ACL->isAllowed($this->_ACL->role, 'admin', 'view'))
				$this->_redirect('admin/login');
    }

    /**
     * Индексная страница админки
     *
     */
    public function indexAction()
    {
    	if (!$this->_ACL->isAllowed($this->_ACL->role, 'admin', 'view'))
        	$this->_redirect('admin/login');

        $this->_helper->layout->setLayout('layout-admin');
    }

    /**
     * Попытка входа в админку
     *
     */
    public function loginAction()
    {
    	$this->_ACL->login($this->_request);
    	if ($this->_ACL->isAllowed($this->_ACL->role, 'admin', 'view'))
			$this->_redirect('admin');

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
    	session_destroy();
    	$this->_redirect('/');
    }

    /**
     * Просмотр категорий и действий над ними
     *
     */
    public function categoriesAction()
    {
    	$this->_helper->layout->setLayout('layout-site');
    	$this->view->categoriesListRoot = $this->_categories->getCategoriesListRoot();
    	$this->view->categoriesListParent = $this->_categories->getCategoriesFolder();
    }

    /**
     * Получение списка категорий для таблици в формате JSON
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
     * Добавление категорий
     *
     */
    public function categoriesdelAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	echo ($this->_categories->delCategories($this->_request)) ? 'Категория успешно удалена' : 'Ошибка удаления категории';
    }
}

