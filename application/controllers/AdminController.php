<?php
/**
 * Главный контроллер админки
 *
 */
class AdminController extends Zend_Controller_Action
{

	protected $_ACL			=	null;
	protected $_categories	=	null;

    public function init()
    {
    	# Подключение системы контроля доступа
    	$this->_ACL = new Application_Model_Acl();
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

    public function categoriesAction()
    {
    	$this->_helper->layout->setLayout('layout-site');
    }

    public function categoriesviewAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->disableLayout();
    	$this->_categories = new Application_Model_Categories();
    	echo $this->_categories->viewCategories();
    }
}

