<?php
class ArticleException extends Exception {}

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
	 * Удаление статьи
	 *
	 * @param integer $id
	 */
	public function removeArticle($id)
	{
        if (empty($id))
			throw new ArticleException('Ошибка входящих данных');
		if (is_null($article = $this->find($id)->current())) {
		    throw new ArticleException('Не найдено статьи с указанным ID');
		}
		$article->delete();
	}

	/**
	 * Обновление статьи
	 *
	 * @throws ArticleException
	 * @param integer $id
	 * @param string $headline
	 * @param string $content
	 * @return void
	 */
	public function updateArticle($id, $headline, $content)
	{
		if (empty($id) || empty($headline) || empty($content))
			throw new ArticleException('Ошибка входящих данных');
		if (is_null($article = $this->find($id)->current())) {
		    $article = $this->createRow(array(
                'category'  => $id,
                'createDate'=> date('Y-m-d H:i:s')
		    ));
		}
		$article->headline = $headline;
		$article->content = $content;
		$article->changeDate = date('Y-m-d H:i:s');
		$article->save();
	}

	/**
	 * Получение статьи по идентификатору
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getArticleByID($id)
	{
	    if ($res = $this->find($id)->current()) {
	        return array(
	               'content'   => $res['content'],
	               'headline'  => $res['headline'],
	               'createDate'=> $res['createDate'],
	               'changeDate'=> $res['changeDate']
	           );
	    } else
	       return array('headline'=>'', 'content'=>'?', 'createDate'=>'?', 'changeDate'=>'?');
	}
}
?>