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
	public static function get($login)
	{
		$model = new Application_Model_Acldb();
		return $model->find($login)->current();
	}
}
?>