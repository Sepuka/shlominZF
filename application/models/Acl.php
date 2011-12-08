<?php
/**
 * Класс контроля доступа
 *
 */
class Application_Model_Acl extends Zend_Acl
{
	/**
	 * Конструктор
     *
     * Своего конструктора у класса Zend_Acl нет
	 *
	 */
	public function __construct()
	{
		$this->_setACL();
	}

	/**
	 * Создание прав доступа к ресурсам
	 *
	 */
	protected function _setACL()
	{
		# Роль гостя
		$this->addRole(new Zend_Acl_Role('guest'));
		# Роль сотрудника
		$this->addRole(new Zend_Acl_Role('staff'), 'guest');
		# Роль администратора
		$this->addRole(new Zend_Acl_Role('administrator'), 'staff');
		# Создаем ресурс Административная панель
		$this->add(new Zend_Acl_Resource('admin'));
		# Персонал может читать админку
		$this->allow('staff', 'admin', 'view');
		# Администратор может вносить изменения в админке
		$this->allow('administrator', 'admin', 'edit');
	}
}