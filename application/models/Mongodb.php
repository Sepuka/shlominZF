<?php
class MongoDBException extends Exception {}
class MongoDBKeyNotFound extends MongoDBException {}
class MongoDBInsertException extends MongoDBException {}
class MongoDBRemoveException extends MongoDBException {}

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
    public function find($key=null, $values=null, $limit=null)
    {
        $keys = (is_null($key)) ? array() : array('key' => $key);
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

    /**
     * Обновление документа
     *
     * @param string $newkey
     * @param string $oldkey
     * @param string $value
     */
    public function update($newkey, $oldkey, $value)
    {
        $cursor = $this->findOne($oldkey);
        if (is_null($cursor))
            throw new MongoDBKeyNotFound('Не удалось найти документ ' . $oldkey);
        $cursor['value'] = $value;
        $cursor['key'] = $newkey;
        $cursor['changeTime'] = date('Y-m-d H:i:s');
        $this->_coll->save($cursor);
    }

    /**
     * Создание/обновление нового документа
     *
     * @throws MongoDBInsertException
     * @param string $key
     * @param string $value
     */
    public function replace($key, $value)
    {
        if (empty($key))
            throw new MongoDBInsertException('Ключ документа не может быть пустым!');
        $cursor = $this->findOne($key);
        if (is_null($cursor)) {
            $item = array(
                'key'       => $key,
                'value'     => $value,
                'changeTime'=>date('Y-m-d H:i:s')
            );
            if (! $this->_coll->insert($item))
                throw new MongoDBInsertException('Ошибка создания документа ' . $key);
        } else {
        	$cursor['value'] = $value;
            $cursor['changeTime'] = date('Y-m-d H:i:s');
            $this->_coll->save($cursor);
        }
    }

    /**
     * Удаление документа
     *
     * @throws MongoDBRemoveException
     * @param string $key
     */
    public function remove($key)
    {
        $criteriea = array('key' => $key);
        $options = array('justOne' => true);
        if (! $this->_coll->remove($criteriea, $options))
            throw new MongoDBRemoveException('Ошибка удаления документа ' . $key);
    }
}