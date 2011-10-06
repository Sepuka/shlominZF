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
			throw new Categories_Exception('stmt2selectEncode expect Zend_Db_Statement');
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
		$select = $this->select()
            ->where('parent=?', $parent);
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
	 * Получение всех категорий
	 *
	 * @return Zend_Db_Statement
	 */
	public function getCategories()
	{
	    return $this->select()->query();
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
	 * @return Zend_Db_Statement
	 */
	public function getCategoriesListSpecified($category)
	{
		return $this->select()
				->where('parent=?', $category)
				->query();
	}

	/**
	 * Редактирование категорий
	 * 
	 * @throws Category_Exception
	 * @param integer $id
	 * @param integer $sequence
	 * @param string $parent
	 * @param string $name
	 * @return void
	 */
	public function editCategories($id, $sequence, $parent, $name)
	{

		if (empty($id) || empty($name))
			throw new Categories_Exception('Не все поля заполнены');
		$category = $this->find($id)->current();
		if (is_null($category))
			throw new CategoriesException('Не найдено категории с id ' . $id);
		$category->sequence = $sequence;
		$category->parent = $parent;
		$category->name = $name;
		$category->dateChange = date('Y-m-d H:i:s');
		$category->save();
	}

	/**
	 * Создание новой категории
	 *
	 * @throws Category_Exception
	 * @param string $parent
	 * @param string $name
	 * @return void
	 */
	public function addCategories($parent, $name)
	{
		if (empty($name))
			throw new Categories_Exception('name is empty!');
		$category = $this->createRow(
			array(
				'parent' 	=> $parent,
				'name' 		=> $name,
				'dateCreate'=> date('Y-m-d H:i:s')
			));
		$category->save();
	}

	/**
	 * Удаление категорий
	 *
	 * @throws Category_Exception
	 * @param integer $id
	 * @return void
	 */
	public function delCategories($id)
	{
		$where = $this->getAdapter()->quoteInto('id = ?', $id);
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