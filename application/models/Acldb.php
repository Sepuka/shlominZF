<?php
/**
 * Класс для работы с таблицей контроля доступа acl
 *
 */
class Application_Model_Acldb extends Zend_Db_Table_Abstract
{
	// Таблица базы данных
	protected $_name	= 'acl';
	// Первичный ключ
	protected $_primary	= 'login';

	/**
	 * Получение сущности клиента по первичному ключу
	 *
	 * @param string $login
	 * @return Zend_Db_Table_Row
	 */
	public static function get($user)
	{
		$model = new Application_Model_Acldb();
		return $model->find($login)->current();
	}

	public static function create()
	{
		$model = new Application_Model_Acldb();
		return $model->createRow();
	}

	/**
	 * Идентификация и аутентификация
	 *
	 * @param string $login
	 * @param string $hash
	 * @return Zend_Db_Table_Row | null
	 */
	public static function authentication($login, $hash)
	{
		$model = new Application_Model_Acldb();
		$stmt = $model->select()
	    		->where('login=?', $login)
	    		->where('hash=?', $hash)
	    		->query();
		if ($res = $stmt->fetch())
			return $model->find($res['login'])->current();
		else
			return null;
	}

	/**
	 * Авторизация по уникальному хешу из cookie
	 *
	 * @param string $auth
	 * @return Zend_Db_Table_Row
	 */
	public static function authorization($auth)
	{
		$model = new Application_Model_Acldb();
		$stmt = $model->select()
				->where('cookie=?', $auth)
				->query();
		if ($res = $stmt->fetch())
			return $model->find($res['login'])->current();
		else
			return $model->createRow();
	}
}
?>