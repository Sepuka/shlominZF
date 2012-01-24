<?php
class Acldb_Exception extends Exception {};

/**
 * Класс для работы с таблицей контроля доступа acl
 *
 */
class Application_Model_Acldb extends Zend_Db_Table_Abstract
{
    // Таблица базы данных
    protected $_name = 'acl';
    // Первичный ключ
    protected $_primary = 'id';
    // Разрешенные роли пользователей
    protected $_roles = array('guest', 'staff', 'administrator');

    /**
     * Получение сущности клиента по первичному ключу
     *
     * @param string $login
     * @return Zend_Db_Table_Row
     */
    public static function get($login)
    {
        $model = new Application_Model_Acldb();
        return $model->find($login)->current();
    }

    /**
     * Редактирование пользователя
     *
     * @param integer $id
     * @param string $login
     * @param string $role
     * @param integer $enabled
     */
    public function editUser($id, $login, $role, $enabled)
    {
        if (! in_array($role, $this->_roles))
            throw new Acldb_Exception('unknow role ' . $role);
        $user = $this->find($id)->current();
        if ($user === null)
            throw new Acldb_Exception('user ' . $id . ' not found');
        $user->login = $login;
        $user->role = $role;
        $user->enabled = $enabled;
        $user->change = date('Y-m-d H:i:s');
        $user->save();
    }

    /**
     * Добавление пользователя
     *
     * @param string $login
     * @param string $role
     * @param integer $enabled
     */
    public function createUser($login, $role, $enabled)
    {
        if (! in_array($role, $this->_roles))
            throw new Acldb_Exception('unknow role ' . $role);
        $user = $this->createRow(array(
            'login' => $login,
            'role' => $role,
            'enabled' => $enabled,
            'create' => date('Y-m-d H:i:s')
        ));
        $user->save();
    }

    /**
     * Удаление пользователя
     *
     * @param string $id
     */
    public function destroyUser($id)
    {
        $user = $this->find($id)->current();
        if ($user === null)
            throw new Acldb_Exception('user ' . $id . ' not found');
        $user->delete();
    }

    /**
     * Получение общих данных о пользователях
     *
     * @return array
     */
    static public function metaData()
    {
        $inst = new Application_Model_Acldb();
        $cntAll = $inst->select()->from('acl', array('cnt' => new Zend_db_Expr('COUNT(*)')))->query()->fetch();
        $cntAdministrator = $inst->fetchAll('`role`="administrator"')->count();
        $cntStaff = $inst->fetchAll('`role`="staff"')->count();
        $cntGuest = $inst->fetchAll('`role`="guest"')->count();
        $cntEnabled = $inst->fetchAll('`enabled`=1')->count();
        $cntDisabled = $inst->fetchAll('`enabled`=0')->count();
        return array(
            'cntAll' => $cntAll['cnt'],
            'cntAdministrator' => $cntAdministrator,
            'cntStaff' => $cntStaff,
            'cntGuest' => $cntGuest,
            'cntEnabled' => $cntEnabled,
            'cntDisabled' => $cntDisabled
        );
    }
}