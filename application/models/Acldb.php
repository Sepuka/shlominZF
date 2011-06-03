<?php
/**
 * Класс для работы с таблицей контроля доступа acl
 *
 */
class Application_Model_Acldb extends Zend_Db_Table
{
	protected $_name	= 'acl';
	protected $_primary	= 'login';

	/**
	 * Идентификация и аутентификация
	 * 
	 * Возвращает строку содержащую роль текущего пользователя
	 *
	 * @param string $login
	 * @param string $hash
	 * @return string
	 */
	function authentication($login, $hash)
	{
		$stmt = $this->select()
				->from($this->_name, 'role')
	    		->where('login=?', $login)
	    		->where('hash=?', $hash)
	    		->query();
		return $stmt->fetchColumn();
	}

	/**
	 * Авторизация
	 * 
	 * Возвращает массив вида array('login'=>'Логин пользователя', 'role'=>'Его роль')
	 *
	 * @param string $auth
	 * @return array
	 */
	function authorization($auth)
	{
		$stmt = $this->select()
				->from($this->_name, array('login', 'role'))
				->where('cookie=?', $auth)
				->query();
		return ($data = $stmt->fetchAll()) ? $data[0] : array('login' => null, 'role' => 'guest');
	}

	/**
	 * Устанавливает идентификационный токен для пользователя
	 * 
	 * Токен используется для передачи в cookie
	 *
	 * @param string $login
	 * @param string $auth
	 * @return integer
	 */
	function setAuthToken($login, $auth)
	{
		return $this->update(
			array('cookie' => $auth),
			"`login`='{$login}'"
		);
	}
}

