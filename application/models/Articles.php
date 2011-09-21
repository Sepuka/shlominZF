<?php
class Application_Model_Articles extends Zend_Db_Table
{
	// Таблица в базе данных
	protected $_name	=	'articles';
	// Первичный ключ таблицы
	protected $_primary	=	'id';

	/**
	 * Получение древовидного массива категорий и статей
	 *
	 * @param integer $id
	 * @return array
	 */
	protected function _getTreeArticles_Categories($parent = '', $id = 0)
	{
		$stmt = $this->getAdapter()->select()
			->from('categories', array('id', 'name'))
			->where('parent=?', $parent)
			->order('sequence ASC')
			->query();
		$tree = array();
		foreach ($stmt->fetchAll() as $field) {
			$tree[] = array(
				'id' => (int)$field['id'],
				'text' => $field['name'],
				'expanded' => false,
				'articles' => $this->_getTreeArticles_Categories($field['name'], (int)$field['id']));
		}
		$stmt = $this->select()
			->from($this->_name, array('id', 'headline'))
			->where('category=?', $id)
			->query();
		foreach ($stmt->fetchAll() as $field) {
			$tree[] = array(
				'id' => (int)$field['id'],
				'text' => $field['headline'],
				'leaf' => true);
		}
		return $tree;
	}

	/**
	 * Получение дерева статей (JSON)
	 *
	 * @return string
	 */
	public function getTreeArticles()
	{
		$arr = $this->_getTreeArticles_Categories();
		$data['total'] = count($arr);
		$data['success'] = true;
		$data['articles'] = $arr;
		return json_encode($data);
	}

	/**
	 * Обновление статьи
	 *
	 * @param array $data
	 * @return unknown
	 */
	public function updateArticle($data)
	{
		$id = $data->getPost('id');
		$headline = $data->getPost('headline');
		$text = $data->getPost('text');
		if (empty($id) || empty($headline) || empty($text))
			return 0;
	}
}
?>