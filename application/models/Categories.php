<?php
class Categories_Exception extends Exception {}

/**
 * Модель для работы с категориями (разделами)
 * Поля "id","sequence","folder","parent","name"
 */
class Application_Model_Categories extends Zend_Db_Table_Abstract
{
	protected $_name			= 'categories';
	protected $_primary			= 'id';
	protected $_dependentTables	= array('Application_Model_Articles');
	protected $_referenceMap	= array(
		# Связь родителей и детей в категориях
		'parentCategory' => array(
			'columns'		=> 'name',
			'refTableClass'	=> 'Application_Model_Categories',
			'refColumns'	=> 'parent',
			'onDelete'		=> 'cascade',
			'onUpdate'		=> 'cascade'
		));

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
	 * Конвертирует результат выборки в строку
	 * Возвращает строку вида [["name1","name1"],["name2","name2"], ...]
	 * для использования в html элементе select
	 * 
	 * @param Zend_Db_Statement $stmt
	 * @return string
	 */
	public static function stmt2selectEncode($stmt, $key='id', $name='value')
	{
		if (! $stmt instanceof Zend_Db_Statement)
			throw new Categories_Exception('stmt2selectEncode ожидает Zend_Db_Statement');
		$output = array();
		foreach ($stmt->fetchAll() as $value)
			$output[] = sprintf('["%s","%s"]', $value[$key], $value[$name]);
		return sprintf('[%s]', implode(',', $output));
	}

	/**
	 * Получение дерева статей и категорий
	 *
	 * @param string $parent
	 * @param inteher $articleID
	 * @return array
	 */
	public function getCategoriesTree($parent='', $articleID=0)
	{
		$tree = array();
		$select = $this->select()->where('parent=?', $parent);
		# Получаем папки нужной категории
		foreach ($this->fetchAll($select) as $row) {
			$tree[] = array(
                'id'		=> $row['id'],
				'text'		=> $row['name'],
				'expanded'	=> false,
				'articles'	=> $this->getCategoriesTree($row['name'], $row['id']));
		}
		# Получаем статьи нужной категории
		$stmt = $this->getAdapter()
		      ->select()
		      ->from('articles', array('id', 'headline'))
		      ->where('category=?', $articleID, Zend_Db::INT_TYPE)
		      ->query();
		foreach ($stmt->fetchAll() as $row) {
			$tree[] = array(
				'id'	=> $row['id'],
				'text'	=> $row['headline'],
				'leaf'	=> true);
		}
		return $tree;
	}

	/**
	 * Получение списка всех категорий
	 *
	 * @return json
	 */
	public function getCategories()
	{
		$stmt = $this->select()->query();
		return $this->jsonEncode($stmt->fetchAll());
	}

	/**
	 * Получение списка корневых категорий
	 *
	 * @return Zend_Db_Statement
	 */
	public function getCategoriesRoot()
	{
		return $this->select()
				->where('parent=?', '')
				->order('name ASC')
				->query();
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
	 * @return string json
	 */
	public function getCategoriesListSpecified($category)
	{
		$stmt = $this->select()
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
				->where('folder=?', $type, Zend_Db::INT_TYPE)
				->query();
		return $this->jsonEncode($stmt->fetchAll());
	}

	/**
	 * Редактирование категорий
	 *
	 * Возвращает истину если обновление строки прошло
	 * или ложь если строка не была обновлена
	 * 
	 * @throws CategoryException
	 * @param Zend_Controller_Request_Abstract $data
	 * @return void
	 */
	public function editCategories($data)
	{
		if (! $data instanceof Zend_Controller_Request_Abstract)
			throw new CategoriesException('Ожидается объект Zend_Controller_Request_Abstract');
		$id = $data->getPost('id');
		$sequence = $data->getPost('sequence');
		$folder = $data->getPost('folder');
		$parent = $data->getPost('parent');
		$name = $data->getPost('name');
		if (!isset($id) || !isset($sequence) || !isset($folder) || !isset($parent) || !isset($name))
			throw new CategoriesException('Не все поля заполнены');
		$category = $this->find($id)->current();
		if (is_null($category))
			throw new CategoriesException('Не найдено категории с id ' . $id);
		$category->sequence = $sequence;
		$category->folder = $folder;
		$category->parent = $parent;
		$category->name = $name;
		$category->save();
	}

	/**
	 * Добавление категории или статьи
	 *
	 * @throws CategoryException
	 * @param Zend_Controller_Request_Abstract $data
	 * @return void
	 */
	public function addCategories($data)
	{
		if (! $data instanceof Zend_Controller_Request_Abstract)
			throw new CategoriesException('Ожидается объект Zend_Controller_Request_Abstract');
		$folder = $data->getPost('folder');
		$parent = $data->getPost('parent');
		$name = $data->getPost('name');
		if (!isset($folder) || !isset($parent) || !isset($name))
			throw new CategoriesException('Не все поля заполнены');
		$category = $this->createRow(
			array(
				'folder' 	=> $folder,
				'parent' 	=> $parent,
				'name' 		=> $name)
			);
		$category->save();
	}

	/**
	 * Удаление категорий
	 *
	 * @throws CategoryException
	 * @param Zend_Controller_Request_Abstract $data
	 * @return void
	 */
	public function delCategories($data)
	{
		if (! $data instanceof Zend_Controller_Request_Abstract)
			throw new CategoriesException('Ожидается объект Zend_Controller_Request_Abstract');

		$where = $this->getAdapter()->quoteInto('id = ?', $data->getPost('id'));
		$this->delete($where);
	}

	/**
	 * Поиск неверных категорий
	 * 
	 * Ищет некорневые категории неимеющие родителя
	 *
	 * @return string
	 */
	public function getWarningsCategories()
	{
		$stmt = $this->select()
				->distinct()
				->from($this->_name, 'parent')
				->where('parent NOT IN (SELECT DISTINCT `name` FROM `categories`)')
				->where('parent != ""')
				->query();
		for($i = 0, $warnings = array(), $result = $stmt->fetchAll(); $i < count($result); $i++)
			$warnings[] = $result[$i]['parent'];
		return sprintf('"%s"', (empty($warnings)) ? 'Проблемных категорий нет' : implode(',', $warnings));
	}
}
?>