<?php
class MongoDBException extends Exception {}

class Application_Model_Mongodb
{
    protected $_conn    = null;
    protected $_coll    = null;

    public function __construct($dbname, $collection, $host='localhost', $username='', $password='')
    {
        if ($username && $password)
            $auth = "${username}:${password}@";
        else if ($username)
            $auth = "${username}@";
        else $auth = '';

        try {
            $this->_conn = new Mongo("mongodb://{$auth}{$host}");
        } catch (MongoConnectionException $ex) {
            #TODO: пишем в логи
            throw new MongoDBException('Ошибка соединения с сервером MongoDB', 0, $ex);
        }
        $db = $this->_conn->selectDB($dbname);
        $this->_coll = $db->selectCollection($collection);
    }

    public function __destruct()
    {
        $this->_conn->close();
    }

    /**
     * Получение множества документов по ключу
     * 
     * Метод возвращает курсор реализующий шаблон Итератор
     *
     * @param string $key
     * @param array $values
     * @param integer $limit
     * @return MongoCursor
     */
    public function find($key, $values=null, $limit=null)
    {
        $keys = array('key' => $key);
        $values = (is_null($values)) ? array() : $values;
        $cursor = $this->_coll->find($keys, $values);
        if ($limit)
            $cursor->limit($limit);
        return $cursor;
    }

    /**
     * Получение документа по ключу
     * 
     * К элементам полученного курсора следует обращаться как к массиву
     *
     * @param string $key
     * @param array $values
     * @return MongoCursor
     */
    public function findOne($key, $values=null)
    {
        $keys = array('key' => $key);
        $values = (is_null($values)) ? array() : $values;
        return $this->_coll->findOne($keys, $values);
    }
}