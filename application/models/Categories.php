<?php
class Application_Model_Categories extends Zend_Db_Table
{
	// Таблица базы данных
	protected $_name	= 'categories';
	// Первичный ключ
	protected $_primary	= 'id';

	/**
	 * Получение списка категорий
	 *
	 * @return json
	 */
	public function viewCategories()
	{
		$stmt = $this->select()
			->from($this->_name, array("id","sequence","folder","parent","name"))
			->query();
		return $this->jsonEncode($stmt->fetchAll());
	}

	/**
	 * Приведение массива к json
	 *
	 * @param array $arr
	 * @return json
	 */
	public function jsonEncode($arr)
	{
		$data['total'] = count($arr);
		$data['categories'] = $arr;
		return json_encode($data);
	}
}
?>