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
	protected $_dependentTables	= array(
	   'Application_Model_Categories', 'Application_Model_Articles');
	protected $_referenceMap	= array(
		# Связь родителей и детей в категориях
		'childCategory' => array(
			'columns'		=> 'parent',
			'refTableClass'	=> 'Application_Model_Categories',
			'refColumns'	=> 'name'
		));

	/**
	 * Получение Метаданных таблицы
	 *
	 * @return array
	 */
	public function metaData()
	{
	    $cntAll = $this->select()->from($this->_name, array('cnt' => new Zend_db_Expr('COUNT(*)')))->query()->fetch();
	    $cntRoot = count($this->select()->where('parent=""')->query()->fetchAll());
	    return array(
	       'cntAll'    => $cntAll['cnt'],
	       'cntRoot'   => $cntRoot
	    );
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
			throw new Categories_Exception('stmt2selectEncode expect Zend_Db_Statement');
		$output = array();
		foreach ($stmt->fetchAll() as $value)
			$output[] = sprintf('["%s","%s"]', $value[$key], $value[$name]);
		return sprintf('[%s]', implode(',', $output));
	}

	/**
	 * Получение родословной категории
     *
     * Например возвращает массив array([0]=>71,[1]=>63)
     * где 71 это ID программирование, 63 ID python
	 *
	 * @param mixed $row
	 * @param array $branch
	 * @return array
	 */
	public function getBranchCategory($row, $branch = array())
	{
	    if (is_numeric($row)) {
            if (is_null($current = $this->find($row)->current()))
                return $branch;
	    }
        elseif ($row instanceof Zend_Db_Table_Row)
            $current = $row;
        else
            return $branch;
        $branch[] = $current->id;
        if (! is_null($parent = $current->findParentRow('Application_Model_Categories', 'childCategory')))
            return $this->getBranchCategory($parent, $branch);
	    else
            return $branch;
	}

	/**
	 * Получение дерева статей и категорий
	 *
	 * @param string $parent
	 * @param integer $articleID
	 * @return array
	 */
	public function getCategoriesTree($parent='', $articleID=0, $branchCategories=array())
	{
		$tree = array();
		$select = $this->select()
            ->where('parent=?', $parent)
            ->order('sequence ASC');

		# Получаем папки нужной категории
		foreach ($this->fetchAll($select) as $row) {
			$tree[] = array(
                'id'		=> $row['id'],
				'text'		=> $row['name'],
				'expanded'	=> (in_array($row['id'], $branchCategories)) ? true : false,
				'children'	=> $this->getCategoriesTree($row['name'], $row['id'], $branchCategories));
		}
		# Получаем статьи нужной категории сортированные по дате изменений
		$stmt = $this->getAdapter()
		      ->select()
		      ->from('articles', array('id', 'headline'))
		      ->where('category=?', $articleID, Zend_Db::INT_TYPE)
              ->order('changeDate DESC')
		      ->query();
		foreach ($stmt->fetchAll() as $row) {
			$tree[] = array(
				'id'	=> $row['id'],
				'text'	=> $row['headline'],
				'leaf'	=> true,
				'parentId'=>$articleID);
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
				->order('sequence ASC')
				->query();
	}

	/**
	 * Получение всех категорий
	 *
	 * @return Zend_Db_Statement
	 */
	public function getCategories()
	{
	    return $this->select()->order('name ASC')->query();
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
	 * @param string $parent Может быть пустой строкой
	 * @param string $name
	 * @return void
	 */
	public function addCategories($parent, $name)
	{
	    if (! empty($parent)) {
	        $where = $this->select()->where('name=?', $parent);
	        # Если родителя не нашли, создадим его
            if (! count($this->fetchRow($where)))
                $this->addCategories('', $parent);
            unset($where);
	    }
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
	 * Каскадно удаляет указанную категорию, а также всех ее потомков и
	 * статьи. Т.к. каскадное удаление фреймворка работает не правильно
	 * (статьи удаляет, а категории нет) пришлось написать свою реализацию
	 *
	 * @param integer $id
	 * @return void
	 */
	public function delCategories($id)
	{
		if (! is_null($current = $this->find($id)->current())) {
            $childs = $current->findDependentRowset('Application_Model_Categories', 'childCategory');
    		while ($child = $childs->current()) {
    		    $this->delCategories($child->id);
    		    $childs->next();
    		}
    		$current->delete();
		}
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