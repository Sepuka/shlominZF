<?php
/**
 * Представление админки
 * 
 * $Id: v.index.php 24 2010-11-17 15:25:57Z Sepuka $
 */
require_once(dirname(__FILE__) . '/../models/m.admin.php');                  // Модель админки

class v_admin {

    const DEFAULT_JSON_DATA                 =   '{"total":0,"categories":[{"id":?,"sequence":"?","folder":"?","parent":"Не удалось выполнить запрос","name":"Проверьте логи ошибок"}]}';
    protected $_model                       =   null;

    public function __construct() {
        $this->_model = new m_admin();
    }

    /**
     * Возвращает текст собранной требуемой страницы
     *
     * @param string $page
     * @return string
     */
    public function getPage($page = 'index') {
        switch ($page) {
            // Получение списка категорий в формате JSON (весь список)
        	case 'getCategoriesListJSON':
                if (!$result = $this->_model->getCategoriesList())
                    return self::DEFAULT_JSON_DATA;
                else {
                    $json = sprintf('{"total":%d,"categories":[', count($result));
                    foreach ($result as $data) {
                        $json .= sprintf('{"id":%d,"sequence":"%s","folder":"%s","parent":"%s","name":"%s"},',
                            $data['id'], $data['sequence'], $data['folder'], $data['parent'], $data['name']
                        );
                    }
                    return substr($json, 0, -1) . ']}';
                }
                break;

            // Получение списка категорий в формате JSON (конкретные дети)
            case 'getChildren':
                if (!$result = $this->_model->getChildren())
                    return self::DEFAULT_JSON_DATA;
                else {
                    $json = sprintf('{"total":%d,"categories":[', count($result));
                    foreach ($result as $data) {
                        $json .= sprintf('{"id":%d,"sequence":"%s","folder":"%s","parent":"%s","name":"%s"},',
                            $data['id'], $data['sequence'], $data['folder'], $data['parent'], $data['name']
                        );
                    }
                    return substr($json, 0, -1) . ']}';
                }
                break;

            default:
                return self::DEFAULT_JSON_DATA;
        }
    }

    /**
     * Получение текста статьи из БД по идентификатору
     *
     * @return JSON
     */
    public function getMainContent() {
        return json_encode($this->_model->getArticleByID($_GET['articleID']));
    }

   /**
     * Получение списка [корневых] категорий
     * 
     * Метод служит для вставки значения в combo в виде [["name","name"],["name","name"]]
     * на этапе "сборки" страницы
     *
     * @param bool $root
     * @return array
     */
    public function getCategories($root = false) {
        if ($root) {
            $result = $this->_model->getRootCategories();
            if (is_array($result))
                array_push($result, array('name' => 'Показать все корневые'));
        } else
            $result = $this->_model->getParentCategories();
        if (!$result)
            return '[]';
        else {
            $parents = '[';
            foreach ($result as $parent)
                $parents .= sprintf('["%s","%s"],', $parent['name'], $parent['name']);
        }
        return substr($parents, 0, -1) . ']';
    }

    /**
     * Возвращает JSON-список файлов/статей
     *
     * @return string
     */
    public function getArticlesList() {
        if (!$result = $this->_model->getArticlesList())
            return '[]';
        else {
            $parents = '[';
            foreach ($result as $parent)
                $parents .= sprintf('["%s","%s"],', $parent, $parent);
        }
        return substr($parents, 0, -1) . ']';
    }

    /**
     * Обновление текста статьи
     *
     * @return string
     */
    static public function saveArticle()
    {
        if ($this->_model->saveArticle())
            return 'Статья успешно обновлена';
        else
            return 'Не удалось обновить статью';
    }

    /**
     * Добавление категорий
     *
     * @return string
     */
    public function addCategory() {
        if (!$result = $this->_model->addCategory())
            return 'Ошибка добавления категории';
        else
            return 'Категория успешно добавлена';
    }

    /**
     * Обновление категорий
     *
     * @return string
     */
    public function updateCategory() {
        if ($this->_model->editCategories())
            return 'Данные успешно обновлены';
        else
            return 'Ошибка обновления данных';
    }

    /**
     * Удаление категорий
     *
     * @return string
     */
    public function deleteCategory() {
        if ($this->_model->deleteCategory())
            return 'Категория успешно удалена';
        else
            return 'Ошибка удаления категории!';
    }

    /**
     * Сборка страниц
     * 
     * Загружает основную страницу и добавляет в нее подшаблоны и переменные
     *
     * @param array $tmpl
     * @param array $vars
     * @return string
     */
    public function buildPage($tmpl, $vars) {
        $page = key($tmpl);
        $mainTemplate = $this->loadTemplate($page);
        $template = str_replace(array_keys($tmpl[$page]), $this->loadTemplate(array_values($tmpl[$page])), $mainTemplate);
        return str_replace(array_keys($vars), array_values($vars), $template);
    }

    /**
     * Возвращает шаблон
     * 
     * Возвращает шаблон страницы или массива страниц в виде массива
     *
     * @param mixed $object
     * @return array
     */
    public function loadTemplate($object, $path = '../templates/') {
        static $pages = array();
        if (is_array($object)) {
            foreach ($object as $templates)
                $pages[] = $this->loadTemplate($templates, $path);
        } elseif (is_string($object)) {
            return file_get_contents($path . $object);
        }
        return $pages;
    }
}
?>