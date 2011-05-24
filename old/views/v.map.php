<?php
/**
 * Представление карты сайта
 * 
 * Индексной страницей карты сайта считается страница возвращающая список ссылок на статьи и разделы
 * 
 * $Id: v.map.php 129 2011-05-17 13:16:39Z Sepuka $
 *
 */
require_once(dirname(__FILE__) . '/../models/m.map.php');					// Модель по-умолчанию
require_once(dirname(__FILE__) . '/../lib/obfuscator.php');					// Обфускатор
require_once(dirname(__FILE__) . '/../lib/common.php');						// Общие функции

class v_map {
    const OBFUSCATE                                 =   true;

    /**
     * Получение нужной страницы карты сайта
     *
     * @param string $page
     * @return string
     */
    static public function getPage($page = 'index') {
        switch ($page) {
            case 'index' :
            # Получение списка всех статей в виде ссылок
            	$order = array(
            		'map.tpl' => array(
            			'{COUNTERS}'    => 'counters.tpl',      // Счетчики
                        '{METATAGS}'    => 'metatags.tpl'       // Мета теги
            		)
            	);
            	if ($links = m_map::getListArticles()) {
                    $link = '';
                    foreach ($links as $key => $value) {
                        $link .= sprintf('<a href="http://%s/index.php?act=source&id=%d">%s</a>, ', $_SERVER['HTTP_HOST'], $key, $value);
                    }
                } else
                    $link = 'Карта сайта пуста';
            	$vars = array(
            		'{LINKS}' => $link
            	);
                if (self::OBFUSCATE)
                    return obfuscator(buildPage($order, $vars, 'templates/'));
                else
                    return buildPage($order, $vars, 'templates/');
                break;
        }
    }
}
?>