<?php
/**
 * Представление главной страницы
 * 
 * $Id: v.index.php 129 2011-05-17 13:16:39Z Sepuka $
 */
require_once(dirname(__FILE__) . '/../models/m.index.php');					// Модель по-умолчанию
require_once(dirname(__FILE__) . '/../lib/obfuscator.php');					// Обфускатор
require_once(dirname(__FILE__) . '/../lib/common.php');						// Общие функции

class v_index {
    const TITLE                                     =   'домашний сайт на домашнем хостинге';
    const OBFUSCATE                                 =   True;

    /**
     * Возвращает текст собранной требуемой страницы
     *
     * @param string $page
     * @return string
     */
    static public function getPage($page = 'index')
    {
        switch ($page) {
            // Получение индексной страницы
            case 'index' :
                // Шаблоны
                $order = array(
                    'index.tpl' =>
                        array(
                            '{COUNTERS}'    => 'counters.tpl',      // Счетчики
                            '{METATAGS}'    => 'metatags.tpl',      // Мета теги
                            '{CSS}'         => 'css.tpl'            // таблицы стилей
                        )
                    );
                // Переменные
                $vars = array(
                    '{TITLE}'       =>  self::TITLE,                            // заголовок страницы
                    '{JAVASCRIPT}'  =>  loadTemplate('index.js', 'js/'),  		// javascript
                );
                if (self::OBFUSCATE)
                    return obfuscator(buildPage($order, $vars, 'templates/'));
                else
                    return buildPage($order, $vars, 'templates/');
                break;

            // Получение исходного текста содержимого
            case 'source':
            	$source = m_index::getArticleByID($_GET['id']);
            	$order = array(
            		'source.tpl' => array(
						'{COUNTERS}'    => 'counters.tpl',      // Счетчики
                        '{METATAGS}'    => 'metatags.tpl'       // Мета теги
            		)
            	);
                $vars = array(
                    '{TITLE}'       =>  $source['headline'],    // заголовок страницы
                    '{SOURCE}'		=>	$source['content']
                );
                if (self::OBFUSCATE)
                    return obfuscator(buildPage($order, $vars, 'templates/'));
                else
                    return buildPage($order, $vars, 'templates/');
                break;

            // Получение списка категорий
            case 'parentCategories':
                return m_index::getSiteCategories();

            case 'loadContent':
                // Если идентификатора статьи нет, клиент получит сообщение о не существующей статье
                return self::getMainContent();

            default:
                throw new Exception(sprintf('Не существует страницы %s!', $page));
        }
    }

    /**
     * Получение текста статьи из БД по идентификатору
     * 
     * Заголовок статьи содержит постоянную ссылку на материал
     * 
     * @param bool $counters добавление счетчиков в возвращаемый контент
     * @return JSON
     */
    static protected function getMainContent($counters = True)
    {
        if ($result = m_index::getArticleByID($_GET['articleID'])) {
        	$result['headline'] = sprintf('<a href="http://%s/index.php?act=page&id=%d" title="Постоянная ссылка на статью">%s</a>',
        		$_SERVER['HTTP_HOST'], $_GET['articleID'], $result['headline']);
        	if ($counters)
        		$result['content'] = loadTemplate('counters.tpl') . $result['content'];
            return json_encode($result);
        } else
            return '{"headline":"Ошибка","content":"Запрошенный контент не найден","date":"?","error":"true"}';
    }
}
?>