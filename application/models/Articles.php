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
		$data['children'] = $tree;
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
	 * @param integer $categoryID
	 * @param string $headline
	 * @param string $content
	 * @return void
	 */
	public function updateArticle($id, $categoryID, $headline, $content)
	{
		if (empty($id) && empty($categoryID))
			throw new ArticleException('id and categoryID is empty!');
		if (empty($headline))
            throw new ArticleException('headline is empty!');
        # Если установлен id, то статья редактируется
        if ($id) {
    		if (is_null($article = $this->find($id)->current()))
                throw new ArticleException('id params is wrong!');
            $article->headline = $headline;
            $article->content = $content;
            $article->changeDate = date('Y-m-d H:i:s');
        } else {
            # если установлен categoryID, то статья создается
            $article = $this->createRow(array(
                'category'  => $categoryID,
                'createDate'=> date('Y-m-d H:i:s'),
                'headline' => $headline,
                'content' => $content
    		));
        }
		$article->save();
	}

	/**
	 * Получение статьи по идентификатору
	 *
	 * @param integer $id
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getArticleByID($id)
	{
	    return $this->find($id)->current();
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