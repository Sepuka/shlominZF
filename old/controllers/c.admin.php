<?php
/**
 * Контроллер админки по-умолчанию
 * 
 * $Id: c.admin.php 129 2011-05-17 13:16:39Z Sepuka $
 */

require_once(dirname(__FILE__) . '/../views/v.admin.php');      // Представление админки по-умолчанию

class c_admin {

    const TITLE                             =   'Админка';      // Заголовок страницы
    protected $_view                        =   null;

    public function __construct() {
        $this->_view = new v_admin();
    }

    /**
     * Получение страницы по-умолчанию
     *
     * @return string
     */
    protected function _getIndexPage() {
        $order = array(
            'admin.tpl' =>
                array(
                    '{METATAGS}'    => 'metatags.tpl',          // Мета теги
                    '{JAVASCRIPT}'  => 'admin_javascript.tpl',  // JavaScript'ы
                    '{CSS}'         => 'admin_css.tpl'          // таблицы стилей
                    )
                );
        $vars = array(
            '{TITLE}'   =>  self::TITLE                         // заголовок страницы
        );
        return $this->_view->buildPage($order, $vars);
    }

    /**
     * Получение различных запрошенных страниц
     * 
     * Определяет что нужно клиенту и возвращает необходимое
     *
     * @return string
     */
    public function router()
    {
        if (!empty($_GET['id'])) {
            switch ($_GET['id']) {
                // Выбор секции редактирования сайта
                case 'choose_section' :
                    return $this->_view->loadTemplate('admin_choose_section.tpl');
                    break;

                // Редактирование категорий
                case 'editCategories' :
                    $order = array(
                        'admin_edit_categories.tpl' =>
                            array(
                                '{METATAGS}'    => 'metatags.tpl',          // Мета теги
                                '{CSS}'         => 'admin_css.tpl'          // таблицы стилей
                                )
                            );
                    $vars = array(
                        // заголовок страницы
                        '{TITLE}'           =>  'Редактирование категорий',
                        // скрипты принадлижащие текущей странице
                        '{JAVASCRIPT}'      =>  $this->_view->loadTemplate('admin_edit_cat.js', '../js/'),
                        // Список родителей-категорий
                        '{COMBO_PARENTS}'   =>  $this->_view->getCategories(),
                        '{ALL_CATS_LIST}'   =>  $this->_view->getCategories(),
                        // Список корневых категорий
                        '{ROOT_CATS_LIST}'  =>  $this->_view->getCategories(True),
                        // Список статей/файлов
                        '{ARTICLES_LIST}'   =>  $this->_view->getArticlesList()
                    );
                    return $this->_view->buildPage($order, $vars);
                    break;

                // Получение списка категорий в формате JSON
                // Формат возвращаемых данных одинаковый но разный по содержанию. Используется для получения всех категорий
                // или только выборочных (детей определенных родителей)
                case 'getCategoriesList' :
                    if (empty($_GET['content']))
                        $_GET['content'] = '';
                    return $this->_view->getPage($_GET['content']);
                    break;

                // Редактирование категорий
                case 'commitCategories' :
                    return $this->_view->updateCategory();
                    break;

                // Удаление категорий
                case 'deleteCategory' :
                    return $this->_view->deleteCategory();
                    break;

                // Добавление новой категории
                case 'addCategory' :
                    return $this->_view->addCategory();
                    break;

                case 'editArticles' :
                    $order = array(
                        'admin.tpl' =>
                            array(
                                '{METATAGS}'    => 'metatags.tpl',          // Мета теги
                                '{CSS}'         => 'admin_css.tpl'          // таблицы стилей
                            )
                    );
                    $vars = array(
                        // заголовок страницы
                        '{TITLE}'           =>  'Редактор статей',
                        // скрипты принадлижащие текущей странице
                        '{JAVASCRIPT}'      =>  $this->_view->loadTemplate('admin_editor_articles.js', '../js/')
                    );
                    return $this->_view->buildPage($order, $vars);
                    break;

                case 'getArticle' :
                    // Если идентификатора статьи нет, клиент получит сообщение о не существующей статье
                    if (empty($_GET['articleID']))
                        $_GET['articleID'] = 0;
                        return $this->_view->getMainContent();
                    break;

                // Сохранение статьи
                case 'saveArticle' :
                    return $this->_view->saveArticle();
                    break;

                default:
                    return 'Запрошенной страницы не существует!';
            }
        }
        return $this->_getIndexPage();
    }
}
?>