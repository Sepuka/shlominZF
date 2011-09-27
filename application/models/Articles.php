<?php
class Application_Model_Articles extends Zend_Db_Table_Abstract
{
	// Таблица в базе данных
	protected $_name			=	'articles';
	// Первичный ключ таблицы
	protected $_primary			=	'id';
	protected $_referenceMap	=	array(
		'categories' => array(
			'columns'		=> 'category',
			'refTableClass'	=> 'Application_Model_Categories',
			'refColumns'	=> 'id',
			'onDelete'		=> 'cascade',
			'onUpdate'		=> 'cascade'
		)
	);

	/**
	 * Получение дерева статей (JSON)
	 *
	 * @return string
	 */
	public function getTreeArticles()
	{
		$categories_model = new Application_Model_Categories();
		$tree = $categories_model->getCategoriesTree();
		$data['total'] = count($tree);
		$data['success'] = true;
		$data['articles'] = $tree;
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