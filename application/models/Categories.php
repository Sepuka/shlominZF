<?php
/**
 * Модель для работы с категориями (разделами)
 *
 */
class Application_Model_Categories extends Zend_Db_Table
{
	// Таблица базы данных
	protected $_name	= 'categories';
	// Первичный ключ
	protected $_primary	= 'id';

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
	 * Получение списка корневых категорий
	 * 
	 * Возвращает строку вида [["name1","name1"],["name2","name2"], ...]
	 *
	 * @return string
	 */
	public function getCategoriesListRoot()
	{
		$stmt = $this->select()
				->from($this->_name, 'name')
				->where('parent=?', '')
				->order('name ASC')
				->query();
		$output = array();
		foreach ($stmt->fetchAll() as $value)
			$output[] = sprintf('["%s","%s"]', $value['name'], $value['name']);
		return sprintf('[%s]', implode(',', $output));
	}

	/**
	 * Получение списка категорий которые могут быть родителями
	 *
	 * @return string
	 */
	public function getCategoriesFolder()
	{
		$stmt = $this->select()
				->distinct()
				->from($this->_name, 'name')
				->where('folder=1')
				->order('name ASC')
				->query();
		$output = array();
		foreach ($stmt->fetchAll() as $value)
			$output[] = sprintf('["%s","%s"]', $value['name'], $value['name']);
		return sprintf('[%s]', implode(',', $output));
	}

	/**
	 * Получение подкатегорий указанной категории
	 *
	 * @param string $category
	 * @return string
	 */
	public function getCategoriesListSpecified($category)
	{
		$stmt = $this->select()
				->from($this->_name, array("id","sequence","folder","parent","name"))
				->where('parent=?', $category)
				->query();
		return $this->jsonEncode($stmt->fetchAll());
	}

	/**
	 * Получение списка категорий определенного типа (файлы или папки)
	 *
	 * @param integer $type
	 * @return string
	 */
	public function getCategoriesListType($type)
	{
		$stmt = $this->select()
				->from($this->_name, array("id","sequence","folder","parent","name"))
				->where('folder=?', (int)$type)
				->query();
		return $this->jsonEncode($stmt->fetchAll());
	}

	/**
	 * Редактирование категорий
	 *
	 * @param array $data
	 * @return integer
	 */
	public function editCategories($data)
	{
		$id = $data->getPost('id');
		$sequence = $data->getPost('sequence');
		$folder = $data->getPost('folder');
		$parent = $data->getPost('parent');
		$name = $data->getPost('name');
		if (!isset($id) || !isset($sequence) || !isset($folder) || !isset($parent) || !isset($name))
			return 0;
		else {
			$where = sprintf('id = %d', $id);
			return $this->update(
				array(
					'sequence'	=> $sequence,
					'folder' 	=> $folder,
					'parent' 	=> $parent,
					'name' 		=> $name),
				$where
			);
		}
	}

	/**
	 * Добавление категорий
	 *
	 * @param array $data
	 * @return integer
	 */
	public function addCategories($data)
	{
		$folder = $data->getPost('folder');
		$parent = $data->getPost('parent');
		$name = $data->getPost('name');
		if (!isset($folder) || !isset($parent) || !isset($name))
			return 0;
		else {
			return $this->insert(
				array(
					'folder' 	=> $folder,
					'parent' 	=> $parent,
					'name' 		=> $name)
			);
		}
	}

	/**
	 * Удаление категорий
	 *
	 * @param array $data
	 * @return integer
	 */
	public function delCategories($data)
	{
		$id = $data->getPost('id');
		if (empty($id))
			return 0;
		else {
			$where = sprintf('id = %d', $id);
			return $this->delete($where);
		}
	}
}
?>