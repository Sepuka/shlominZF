<?php
/**
 * Работа с БД
 * 
 * $Id: db.php 129 2011-05-17 13:16:39Z Sepuka $
 *
 */
class SQlite {
    static protected $_conn                 =   null;

    /**
     * Возвращает идентификатор соединения с СУБД
     *
     * @return resource
     */
    static public function getConn()
    {
        if (self::$_conn === null) {
            self::$_conn = sqlite_popen('/home/Shlomin/db/shlomin.sqlite', 0666, $err);
            if (self::$_conn === false)
                die($err);
        }
        return self::$_conn;
    }

    /**
     * Экранирование символов
     *
     * @param string $text
     * @return string
     */
    static public function realExcapeString($text)
    {
        return htmlspecialchars($text);
    }
}

/**
 * Класс для работы с MySQL
 *
 */
class MySQL {
    const HOST                              =   'localhost';
    const USERDB                            =   'root';
    const PASSDB                            =   '1';
    const NAMEDB                            =   'shlomin';
    protected $_conn                        =   null;

    public function __construct() {
        $this->getConn();
    }

    /**
     * Возвращает идентификатор соединения с СУБД
     *
     * @return resource
     */
    public function getConn() {
        if ($this->_conn === null) {
            $this->_conn = mysql_pconnect(self::HOST, self::USERDB, self::PASSDB);
            if ($this->_conn === false)
                die(mysql_error());
            if (mysql_select_db(self::NAMEDB, $this->_conn))
                mysql_query('SET NAMES utf8');
            else
                die(mysql_error($this->_conn));
        }
        return $this->_conn;
    }
}
?>