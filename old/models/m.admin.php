<?php
/**
 * Модель админки по-умолчанию
 * 
 * $Id: m.admin.php 129 2011-05-17 13:16:39Z Sepuka $
 */
require_once(dirname(__FILE__) . '/db.php');                    // Работа с БД

class m_admin {

    const LIMIT_CAT                                 =   100;    // Ограничение количества возвращаемых категорий
    protected $_db                                  =   null;

    public function __construct() {
        $this->_db = new MySQL();
    }

    /**
     * Получение списка категорий
     *
     * Возвращает массив категорий и подкатегорий
     * 
     * @return array
     */
    public function getCategoriesList() {
        $query = sprintf('SELECT id, sequence, folder, parent, name FROM categories LIMIT 0,%d', self::LIMIT_CAT);
        $resource = mysql_query($query, $this->_db->getConn());
        if ($resource === false)
            return false;
        $result = array();
        if (mysql_num_rows($resource)) {
            while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
                $result[] = array(
                    'id'        => $row['id'],
                    'sequence'  => $row['sequence'],
                    'folder'    => $row['folder'],
                    'parent'    => $row['parent'],
                    'name'      => $row['name']
                );
            }
        }
        return $result;
    }

    /**
     * Получение списка категорий-родителей
     * 
     * Используется для ComboBox. Родителем может быть любая категория являющаяся папкой
     *
     * @return array
     */
    public function getParentCategories() {
        $query = sprintf('SELECT id, sequence, folder, parent, name FROM categories WHERE folder=1 ORDER BY sequence ASC LIMIT 0,%d',
            self::LIMIT_CAT);
        $resource = mysql_query($query, $this->_db->getConn());
        if (!is_resource($resource))
            die(mysql_error($this->_db->getConn()));
        $result = array();
        if (mysql_num_rows($resource)) {
            while ($row = mysql_fetch_array($resource, MYSQL_ASSOC))
                 $result[] = array(
                    'id'        => $row['id'],
                    'sequence'  => $row['sequence'],
                    'folder'    => $row['folder'],
                    'parent'    => $row['parent'],
                    'name'      => $row['name']
                );
        }
        return $result;
    }

    /**
     * Получение списка корневых категорий
     * 
     * Корневая категория не имеет родителей
     *
     * @return array
     */
    public function getRootCategories() {
        $query = sprintf('SELECT id, sequence, folder, parent, name FROM categories WHERE parent="" ORDER BY sequence ASC LIMIT 0,%d',
            self::LIMIT_CAT);
        $resource = mysql_query($query, $this->_db->getConn());
        if ($resource === false)
            return false;
        $result = array();
        if (mysql_num_rows($resource)) {
            while ($row = mysql_fetch_array($resource, MYSQL_ASSOC))
                $result[] = array(
                    'id'        => $row['id'],
                    'sequence'  => $row['sequence'],
                    'folder'    => $row['folder'],
                    'parent'    => $row['parent'],
                    'name'      => $row['name']
                );
        }
        return $result;
    }

    /**
     * Получение списка статей (файлов)
     *
     * @return array
     */
    public function getArticlesList() {
        $query = sprintf('SELECT name FROM categories WHERE folder=0 ORDER BY sequence ASC LIMIT 0,%d', self::LIMIT_CAT);
        $resource = mysql_query($query, $this->_db->getConn());
        $result = array();
        if (mysql_num_rows($resource)) {
            while ($row = mysql_fetch_array($resource, MYSQL_ASSOC))
                $result[] = $row['name'];
        }
        return $result;
    }

    /**
     * Получение массива детей нужного родителя
     * 
     * Получает из AJAX запроса имя родителя и возвращает его детей.
     * Если имя родителя равно "Показать все корневые" возвращает список корневых категорий
     *
     * @return array
     */
    public function getChildren() {
        if (!empty($_GET['parent']))
            $parent = mysql_real_escape_string($_GET['parent']);
        else
            return False;
        if ($parent == 'Показать все корневые')
            $parent = '';
        $query = sprintf('SELECT id, sequence, folder, parent, name FROM categories WHERE parent="%s" ORDER BY sequence ASC LIMIT 0,%d',
            $parent, self::LIMIT_CAT);
        $resource = mysql_query($query, $this->_db->getConn());
        if (!$resource)
            return false;
        $result = array();
        if (mysql_num_rows($resource)) {
            while ($row = mysql_fetch_array($resource, MYSQL_ASSOC))
                $result[] = array(
                    'id'        => $row['id'],
                    'sequence'  => $row['sequence'],
                    'folder'    => $row['folder'],
                    'parent'    => $row['parent'],
                    'name'      => $row['name']
                );
        }
        return $result;
    }

    /**
     * Обновление категорий/подкатегорий
     * 
     * Изменяет требуемую категорию. Имя родителя может быть пустым.
     *
     * @return bool
     */
    public function editCategories() {
        if (!empty($_POST['id']))
            $id = intval($_POST['id']);
        else
            return 0;
        if (!empty($_POST['sequence']))
            $sequence = intval($_POST['sequence']);
        else
            return 0;
        if (!empty($_POST['folder']))
            $folder = intval($_POST['folder']);
        else
            $folder = 0;
        if (!empty($_POST['parent']))
            $parent = mysql_real_escape_string($_POST['parent']);
        else
            $parent = '';
        if (!empty($_POST['name']))
            $name = mysql_real_escape_string($_POST['name']);
        else
            return 0;
        $query = sprintf('UPDATE categories SET sequence=%d, folder=%d, parent="%s", name="%s" WHERE id=%d',
            $sequence, $folder, $parent, $name, $id);
        return mysql_query($query, $this->_db->getConn());
    }
    
    /**
     * Создание категорий/подкатегорий
     * 
     * Если создается категория/файл с неизвестным родителем, то родитель будет создат автоматически
     *
     * @return bool
     */
    public function addCategory() {
        if (isset($_POST['folder']))
            $folder = intval($_POST['folder']);
        else return false;
        if (!empty($_POST['parent']))
            $parent = mysql_real_escape_string($_POST['parent']);
        else $parent = null;
        if (!empty($_POST['name']))
            $name = mysql_real_escape_string($_POST['name']);
        else return false;
        if (!is_null($parent)) {
            $query = sprintf('SELECT id FROM categories WHERE name=%s', $parent);
            $resource = mysql_query($query, $this->_db->getConn());
            if (!mysql_num_rows($resource)) {
                $query = sprintf('INSERT INTO categories (sequence, folder, name) VALUES (999, 1, "%s")', $parent);
                mysql_query($query, $this->_db->getConn());
            }
        }
        $query = sprintf('INSERT INTO categories (sequence, folder, parent, name) VALUES (999, %d, %s, "%s")',
            $folder, $parent, $name);
        return mysql_query($query, $this->_db->getConn());
    }

    /**
     * Удаление категорий/подкатегорий
     *
     * @return bool
     */
    public function deleteCategory() {
        if (!empty($_POST['id']))
            $id = intval($_POST['id']);
        if (!isset($id))
            return 0;
        $query = sprintf('DELETE FROM categories WHERE id=%d', $id);
        return mysql_query($query, $this->_db->getConn());
    }

    /**
     * Получение статьи по идентификатору
     *
     * @param integer $id
     * @return array
     */
    static public function getArticleByID($id)
    {
        $query = sprintf('SELECT headline, content FROM articles WHERE category=%d', $id);
        $result = mysql_array($query, MySQL::getConn());
        if (!empty($result)) {
            return array(
                'headline'  => htmlspecialchars_decode($result[0]['headline']),
                'content'   => htmlspecialchars_decode($result[0]['content']));
        } else
            return array(
                'headline'  => 'Запрошенный контент не найден',
                'content'   => 'Запрошенный контент не найден');
    }

    /**
     * Обновление статьи в БД
     *
     * @return bool
     */
    static public function saveArticle()
    {
        if (!empty($_POST['articleID']))
            $id = intval($_POST['articleID']);
        if (!isset($id))
            return 0;
        if (!empty($_POST['articleText']))
            $text = mysql_real_escape_string(($_POST['articleText']));
        if (!empty($_POST['articleHeadline']))
            $headline = mysql_real_escape_string(($_POST['articleHeadline']));
        else
            $headline = '';
        $query = sprintf('REPLACE INTO articles (category, headline, content, date) VALUES (%d,"%s","%s","%s")',
            $id, $headline, $text, time());
        return mysql_query($query, MySQL::getConn());
    }
}
?>