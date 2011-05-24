<?php
/**
 * Контроллер карты сайта
 * 
 * $Id$
 */
require_once(dirname(__FILE__) . "/../views/v.map.php");

class c_map {

    public function __construct()
    {
        echo $this->router();
    }

    protected function getIndexPage() {
        return v_map::getPage('index');
    }

    protected function router() {
        // Тут будет логика какая-нибудь
        return $this->getIndexPage();
    }
}
?>