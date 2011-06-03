<?php
/**
 * Класс контроля доступа
 *
 */
class Application_Model_Acl extends Zend_Acl
{
	const SALT				= 'salt';

	public $role 			= 'guest';
	public $login			= null;

	protected $_ACL_DB		= null;

	public function __construct()
	{
		$this->_ACL_DB = new Application_Model_Acldb();
		$this->_setACL();
		$this->_detectUser();
	}

	/**
	 * Создание прав доступа к ресурсам
	 *
	 */
	protected function _setACL()
	{
		# Роль гостя
		$this->addRole(new Zend_Acl_Role('guest'));
		# Роль сотрудника
		$this->addRole(new Zend_Acl_Role('staff'), 'guest');
		# Роль администратора
		$this->addRole(new Zend_Acl_Role('administrator'), 'staff');
		# Создаем ресурс Административная панель
		$this->add(new Zend_Acl_Resource('admin'));
		# Персонал может читать админку
		$this->allow('staff', 'admin', 'view');
		# Администратор может вносить изменения в админке
		$this->allow('administrator', 'admin', 'edit');
	}

	/**
	 * Определение роли текущего пользователя
	 *
	 */
	protected function _detectUser()
	{
		if (!empty($_SESSION['role']) && !empty($_SESSION['login'])) {
			$this->role = $_SESSION['role'];
			$this->login = $_SESSION['login'];
			return;
		}

		if (!empty($_COOKIE['auth'])) {
			$user = $this->_ACL_DB->authorization($_COOKIE['auth']);
			$this->login = $user['login'];
			$this->role = $user['role'];
		}
	}

	/**
	 * Вход в систему из формы
	 *
	 */
	public function login($request)
	{
		$login = $request->getPost('login');
    	$hash = md5($request->getPost('password') . self::SALT);
    	$saveme = $request->getPost('saveme');
		if (!empty($login) && !empty($hash)) {
			$user = $this->_ACL_DB->authentication($login, $hash);
			if ($user) {
				$_SESSION['role'] = $user;
				$_SESSION['login'] = $login;
		    	$this->role = $user;
		    	if ($saveme) {
		    		$auth = sha1(microtime(true) . $login);
		    		$this->_ACL_DB->setAuthToken($login, $auth) && setcookie('auth', $auth, time() + 300, '/');
		    	}
			}
		}
	}
}
?>