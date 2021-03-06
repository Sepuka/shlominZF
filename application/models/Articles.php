<?php
class ArticleException extends Exception {}

class Application_Model_Articles extends Zend_Db_Table_Abstract
{
    // Таблица в базе данных
    protected $_name		= 'articles';
    // Первичный ключ таблицы
    protected $_primary		= 'id';
    protected $_referenceMap    = array(
        'categories' => array(
            'columns'		=> 'category',
            'refTableClass'	=> 'Application_Model_Categories',
            'refColumns'	=> 'id',
            'onDelete'		=> 'cascade',
            'onUpdate'		=> 'cascade'
	)
    );

    /**
     * Формирование дерева категорий и статей
     *
     * Параметр $articleID содержит идентификатор статьи дерево категорий которой
     * нужно раскрыть. Параметр используется только если была запрошена страница
     * с конкретной статьей
     *
     * @param mixed $articleID
     * @return array
     */
    public function getTreeArticles($articleID=null)
    {
        if (is_numeric($articleID)) {
            $article = $this->getArticleByID($articleID);
            $categoryID = $article['category'];
            unset($article);
        } else $categoryID = null;
        $categories_model = new Application_Model_Categories();
        // Определившись с текущей категорией статьи (если произошел запрос конкретной статьи)
        // вытащим всю родословную категории в виде массива
        $branchCategories = $categories_model->getBranchCategory($categoryID);
        $tree = $categories_model->getCategoriesTree('', 0, $branchCategories);
        $data['total'] = count($tree);
        $data['success'] = true;
        $data['children'] = $tree;
        return $data;
    }

    /**
     * Удаление статьи
     *
     * @param integer $id
     */
    public function removeArticle($id)
    {
        $model = new Application_Model_Articles();
        if (empty($id))
            throw new ArticleException('Ошибка входящих данных');
	if (is_null($article = $model->find($id)->current())) {
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
     * @return integer
     */
    static public function updateArticle($id, $categoryID, $headline, $content)
    {
        $model = new Application_Model_Articles();
        if (empty($id) && empty($categoryID))
            throw new ArticleException('id and categoryID is empty!');
        if (empty($headline))
            throw new ArticleException('headline is empty!');
        // Удаление переносов строк
        $content = str_replace(array(chr(10), chr(13)), '', $content);
        // ExtJS выводит текст в одинарных кавычках, поэтому уберем их
        $content = str_replace("'", '"', $content);
        // Не стоит использвать тут htmlspecialchars, он съедает переносы строк
        # Если установлен id, то статья редактируется
        if ($id) {
            if (is_null($article = $model->find($id)->current()))
                throw new ArticleException('id params is wrong!');
            $article->headline = $headline;
            $article->content = $content;
            $article->changeDate = date('Y-m-d H:i:s');
        } else {
            # если установлен categoryID, то статья создается
            $article = $model->createRow(array(
                'category'  => $categoryID,
                'createDate'=> date('Y-m-d H:i:s'),
                'changeDate'=> date('Y-m-d H:i:s'),
                'headline'  => $headline,
                'content'   => $content
    		));
        }
        $article->save();
        // Возвращаем идентификатор статьи
        return $article->id;
    }

    /**
     * Получение статьи по первичному ключу
     *
     * @param integer $id
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getArticleByID($id)
    {
        return $this->find($id)->current();
    }

    /**
     * Получение данных для Sitemap
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    static public function sitemap()
    {
        $model = new Application_Model_Articles();
        $select = $model->select()
            ->from('articles', array('id', new Zend_Db_Expr('DATE(changeDate) as changeDate')));
        return $model->fetchAll($select);
    }
}