<?php
/**
 * Модель по-умолчанию
 * 
 * $Id: m.index.php 129 2011-05-17 13:16:39Z Sepuka $
 */
require_once(dirname(__FILE__) . '/../models/db.php');                  // Работа с БД

class m_index {

    const CATEGORIES_LIMIT                          =   30;             // Лимит количества возвращаемых категорий

    /**
     * Получение статьи по идентификатору
     *
     * @param integer $id
     * @return array
     */
    static public function getArticleByID($id) {
        $query = sprintf('SELECT headline, content, date FROM articles WHERE ROWID = %d;', $id);
        $resource = mysql_query($query, MySQL::getConn()) or die(mysql_error(MySQL::getConn()));
        if (mysql_num_rows($resource))
            return array(
                'headline'  => htmlspecialchars_decode(mysql_result($resource, 0, 'headline')),
                'content'   => htmlspecialchars_decode(mysql_result($resource, 0, 'content')),
                'date'      => date('Дата публикации: d.m.Y H:i', mysql_result($resource, 0, 'date')),
                'error'     => 'false');
        else
            return False;
    }

    /**
     * Получение списка категорий (разделов) сайта в формате JSON
     *
     * @return string JSON
     */
    static public function getSiteCategories()
    {
        if (!empty($_GET['node']))
            $catNode = intval($_GET['node']);
        else
            $catNode = 0;

        if (!$catNode)
            // Если нет идентификатора категории возвращаем все родительские категории
            $query = sprintf('SELECT id, folder, name FROM categories WHERE parent = "" ORDER BY sequence ASC LIMIT 0, %d;', self::CATEGORIES_LIMIT);
        else
            $query = sprintf('SELECT id, folder, name FROM categories WHERE parent = (
                SELECT name
                FROM categories
                WHERE ROWID = %d)
                ORDER BY sequence ASC LIMIT 0, %d', $catNode, self::CATEGORIES_LIMIT);
            $resource = mysql_query($query, MySQL::getConn()) or die(mysql_error());
            $categories = array();
            while ($data = mysql_fetch_array($resource, MYSQL_ASSOC))
            	$categories[] = array(
            	   'text' => $data['name'],
            	   'id' => $data['id'],
            	   'cls' => ($data['folder']) ? 'folder' : 'file',
            	   'leaf' => ($data['folder']) ? false : true,
                );
            if (empty($categories))
                $categories[] = array('text' => 'Нет созданных категорий', 'id' => 0, 'cls' => 'file', 'leaf' => true);
        return json_encode($categories);
    }
}
?>