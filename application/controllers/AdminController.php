<?php
/**
 * Главный контроллер админки
 *
 */
class AdminController extends Zend_Controller_Action
{

	protected $_ACL			=	null;

    public function init()
    {
    	# Подключение системы контроля доступа
    	$this->_ACL = new Application_Model_Acl();
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
}

