<?php
/**
 * Класс для работы с таблицей контроля доступа acl
 *
 */
class Ex_acldb extends Zend_Db_Table
{
	protected $_name	= 'acl';
	protected $_primary	= 'login';

	/**
	 * Идентификация и аутентификация
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

	function setAuth($login, $auth)
	{
		return $this->update(
			array('cookie' => $auth),
			"`login`='{$login}'"
		);
	}
}
?>