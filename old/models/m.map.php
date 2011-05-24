<?php
/**
 * Модель карты сайта
 * 
 * $Id: m.map.php 129 2011-05-17 13:16:39Z Sepuka $
 */

require_once(dirname(__FILE__) . '/../models/db.php');                  // Работа с БД

class m_map {

    /**
     * Получение списка статей в виде ассоциативного массива
     * В формате "имя статьи => ссылка"
     * 
     * @return array
     *
     */
    static public function getListArticles() {
        $query = "SELECT ROWID, headline FROM articles;";
        $resource = sqlite_query($query, SQlite::getConn(), SQLITE_ASSOC);
        $result = array();
        while ($data = sqlite_fetch_array($resource, SQLITE_ASSOC)) {
            $result[$data['ROWID']] = htmlspecialchars_decode($data['headline']);
        }
        return $result;
    }
}
?>