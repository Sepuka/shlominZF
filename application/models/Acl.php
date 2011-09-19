<?php
/**
 * Класс контроля доступа
 *
 */
class Application_Model_Acl extends Zend_Acl
{
	# Соль для пароля
	const SALT				= 'salt';

	# Признак того что данные для входа не верны
	public $wrongData		= false;

	protected $_ACL_DB		= null;
	protected $_entity		= null;
	protected $_session		= null;

	/**
	 * Инициализация
	 *
	 */
	public function __construct()
	{
		$this->_session = new Zend_Session_Namespace();
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
	 * Загрузка текущего пользователя из сессии или cookie
	 *
	 */
	protected function _detectUser()
	{
		if ((! empty($this->_session->user)) && $this->_session->user instanceof Zend_Db_Table_Row)
			return $this->_entity = $this->_session->user;

		$this->_entity = Application_Model_Acldb::create();
	}

	/**
	 * Get'тер для сущности текущего пользователя
	 *
	 * @return Zend_Db_Table_row
	 */
	public function getClient()
	{
		return $this->_entity;
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
		if (! empty($login) && ! empty($hash)) {
			$user = Application_Model_Acldb::authentication($login, $hash);
			if ($user !== null) {
				$this->_entity = $user;
				# сохраняем в сессию сериализованный объект
				$this->_session->user = $user;
		    	if ($saveme)
		    		Zend_Session::rememberMe();
			} else
				$this->wrongData = true;
		}
	}

	public function destroySession()
	{
		Zend_Session::forgetMe();
	}
}
?>