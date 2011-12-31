<?php
/**
 * Контроллер действий обрабатывающий запросы через AJAX
 *
 */
class AjaxController extends Zend_Controller_Action
{
    /**
     *
     * @var Zend_Config
     */
    protected $_config  = null;
    /**
     *
     * @var Zend_Acl
     */
    protected $_ACL     = null;

    /**
     * Обработка вызовов несуществующих действий
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        $this->getResponse()->setHttpResponseCode(404);
        $this->_helper->layout->setLayout('404');
    }

    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        $this->_ACL = new Application_Model_Acl();
        # Запускаем сессию для авторизации
        $this->_session =  new Zend_Session_Namespace();

        $this->_config = Application_Model_MemcachedConfig::getInstance();
        $this->getResponse()->setHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function preDispatch()
    {
        if (! $this->getRequest()->isXmlHttpRequest()) {
            $this->getRequest()->setDispatched(false);
            // TODO сделать return
            $this->getResponse()->setHttpResponseCode(415);
            exit('expect AJAX');
        }

        $auth = Zend_Auth::getInstance();
    	if (! $auth->hasIdentity()) {
            $this->getRequest()->setDispatched(false);
    		$this->getResponse()->setHttpResponseCode(403);
            exit('not authority');
        }
    }

    /**
     * Получение дерева статей в формате JSON
     * 
     * Запрос этого метода может происходить из админки, с главной страницы сайта
     * или со страницы конкретной статьи (/article/37). В последнем случае нам
     * нужен идентификатор статьи (articleID) чтобы раскрыть дерево в нужном месте.
     *
     * @return void
     */
    public function treearticlesAction()
    {
        if (! $this->getRequest()->isGet())
            return $this->getResponse()->setHttpResponseCode(415);
        if ($articleID = $this->getRequest()->getParam('articleID'))
            $articleID = array_pop(explode('/', $articleID));
        else
            $articleID = null;

        $articlesModel = new Application_Model_Articles();
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->appendBody(Zend_Json::encode($articlesModel->getTreeArticles($articleID)));
    }

    /**
     * Получение списка всех ключей из MongoDB
     * 
     * В левой части страницы элемент Ext.view.View загружает список ключей
     * по адресу /ajax/dumpKeysList
     *
     * @return void
     */
    public function dumpkeyslistAction()
    {
        if (! $this->getRequest()->isGet())
            return $this->getResponse()->setHttpResponseCode(415);

        try {
            $mongoDB = new Application_Model_Mongodb($this->_config->mongo->DBname, $this->_config->mongo->docs->collection,
                'localhost', $this->_config->mongo->conn->user, $this->_config->mongo->conn->pass);
        } catch (MongoDBException $ex) {
            $error = sprintf('<font color="red">%s</font><br>', $ex->getMessage());
            return $this->getResponse()
                ->setHeader('Content-Type', 'application/json; charset=UTF-8')
                ->appendBody(Zend_Json::encode(
                    $answer = array('keys' => array(
                        'success'   => false,
                        'key'       => $error,
                        'total'     => 1)
                    )));
        }
        $keys = $mongoDB->find();
        $answer = array('keys' => array());
        $answer['keys'][] = array('success' => true);
        $answer['keys'][] = array('total' => $keys->count());
        foreach ($keys as $key)
            $answer['keys'][] = array('key' => $key['key']);
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->appendBody(Zend_Json::encode($answer));
    }

    /**
     * Получение документа из MongoDB по ключу
     *
     * @return void
     */
    public function dumpgetdocumentAction()
    {
        if (! $this->getRequest()->isGet())
            return $this->getResponse()->setHttpResponseCode(415);

        if (is_null($key = $this->getRequest()->getParam('key')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param key');

        try {
            $mongoDB = new Application_Model_Mongodb($this->_config->mongo->DBname, $this->_config->mongo->docs->collection,
                'localhost', $this->_config->mongo->conn->user, $this->_config->mongo->conn->pass);
        } catch (MongoDBException $ex) {
            return $this->getResponse()
                ->setHttpResponseCode(500)
                ->setHeader('Content-Type', 'application/json; charset=UTF-8')
                ->appendBody(Zend_Json::encode(
                    $answer = array(
                        'success'   => false,
                        'key'       => $ex->getMessage(),
                        'value'     => $ex->getTraceAsString())
                    ));
        }
        $cursor = $mongoDB->findOne($key);
        if (is_null($cursor))
            return $this->getResponse()
                ->setHttpResponseCode(400);

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->appendBody(Zend_Json::encode(
                array('key' => $cursor['key'], 'value' => $cursor['value'])
            ));
    }

    /**
     * Обновление документа в MongoDB
     *
     * @return void
     */
    public function dumpupdatedocumentAction()
    {
        if (! $this->getRequest()->isPost())
            return $this->getResponse()->setHttpResponseCode(415);
        if (is_null($key = $this->getRequest()->getPost('key')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param key');
        if (empty($key))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->setHeader('Content-Type', 'application/json; charset=UTF-8')
                ->appendBody(Zend_Json::encode(
                    $answer = array(
                        'success'   => false,
                        'key'       => 'Ключ документа не указан',
                        'value'     => 'Вы должны выбрать документ который собираетесь изменить')
                    ));
        if (is_null($value = $this->getRequest()->getPost('value')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param value');

        try {
            $mongoDB = new Application_Model_Mongodb(
                $this->_config->mongo->DBname, $this->_config->mongo->docs->collection,
                'localhost', $this->_config->mongo->conn->user, $this->_config->mongo->conn->pass);
            $mongoDB->update($key, $value);
         } catch (MongoDBException $ex) {
             return $this->getResponse()
                ->setHttpResponseCode(500)
                ->setHeader('Content-Type', 'application/json; charset=UTF-8')
                ->appendBody(Zend_Json::encode(
                    $answer = array(
                        'success'   => false,
                        'key'       => $ex->getMessage(),
                        'value'     => $ex->getTraceAsString())
                    ));
         }
        $this->getResponse()
            ->setHttpResponseCode(204);
    }

    /**
     * Работа со статьями
     */

    /**
     * Сохранение статьи
     *
     * @return void
     */
    public function articlessaveAction()
    {
        if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);

        if (is_null($id = $this->getRequest()->getPost('id')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param id');
        if (is_null($categoryID = $this->getRequest()->getPost('categoryID')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param categoryID');
        if (is_null($headline = $this->getRequest()->getPost('headline')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param headline');
        if (is_null($content = $this->getRequest()->getPost('content')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param content');

    	try {
    	   $articleID = Application_Model_Articles::updateArticle($id, $categoryID, $headline, $content);
    	} catch (ArticleException $ex) {
    	    return $this->getResponse()
                ->setHttpResponseCode(400);
    	} catch (Exception $ex) {
    	    return $this->getResponse()
                ->setHttpResponseCode(500);
    	}
    	$this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->appendBody(Zend_Json::encode(array('articleID' => $articleID)));
    }

    /**
     * Удаление статьи
     *
     * @return void
     */
    public function articlesremoveAction()
    {
        if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);

        if (is_null($id = $this->getRequest()->getPost('id')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param id');

    	try {
    	   Application_Model_Articles::removeArticle($id);
    	} catch (ArticleException $ex) {
    	    return $this->getResponse()->setHttpResponseCode(400);
    	} catch (Exception $ex) {
    	    return $this->getResponse()->setHttpResponseCode(500);
    	}
    	$this->getResponse()
            ->setHttpResponseCode(204);
    }

    /**
     * Добавление категорий
     *
     * Принимает параметры parent и name и создает категорию name
     * привязывая ее к parent, при этом если parent не существует
     * так же создает и её
     *
     */
    public function categoriesaddAction()
    {
        if (! $this->_ACL->isAllowed($this->_session->role, 'admin', 'edit'))
			return $this->getResponse()->setHttpResponseCode(403);
    	if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);
        if (is_null($parent = $this->getRequest()->getPost('parent')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param parent');
        if (is_null($name = $this->getRequest()->getPost('name')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param name');

        # Подключение модели для работы с категориями
    	$categories = new Application_Model_Categories();
    	try {
    		$categories->addCategories($parent, $name);
    	} catch (CategoriesException $ex) {
    		return $this->getResponse()
    			->setHttpResponseCode(400);
    	} catch (Exception $ex) {
    		return $this->getResponse()
    			->setHttpResponseCode(500);
    	}
    	$this->getResponse()
    		->setHttpResponseCode(204);
    }

    /**
     * Удаление категории
     *
     */
    public function categoriesdelAction()
    {
        if (! $this->_ACL->isAllowed($this->_session->role, 'admin', 'edit'))
			return $this->getResponse()->setHttpResponseCode(403);
    	if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);
        if (is_null($id = $this->getRequest()->getPost('id')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param id');

        # Подключение модели для работы с категориями
    	$categories = new Application_Model_Categories();
    	try {
    		$categories->delCategories($id);
    	} catch (Categories_Exception $ex) {
    		return $this->getResponse()
    			->setHttpResponseCode(400);
    	} catch (Exception $ex) {
    		return $this->getResponse()
    			->setHttpResponseCode(500);
    	}
    	$this->getResponse()
    		->setHttpResponseCode(204);
    }

    /**
     * Получение списка категорий для таблицы в формате JSON через AJAX
     *
     */
    public function categoriesviewAction()
    {
    	if (! $this->getRequest()->isGet())
    		return $this->getResponse()->setHttpResponseCode(415);
        if (! $this->_ACL->isAllowed($this->_session->role, 'admin', 'view'))
			return $this->getResponse()->setHttpResponseCode(403);

    	$category = $this->getRequest()->getQuery('category');
        # Подключение модели для работы с категориями
    	$categories = new Application_Model_Categories();
    	if (! empty($category))
    		$categories = $categories->getCategoriesListSpecified($category);
    	else
    		$categories = $categories->getCategories();
        $categories = $categories->fetchAll();
        $data['total'] = count($categories);
        $data['categories'] = $categories;
    	$this->getResponse()
			->setHeader('Content-Type', 'application/json; charset=UTF-8')
			->appendBody(Zend_Json::encode($data));
    }

    /**
     * Редактирование категорий
     *
     */
    public function categorieseditAction()
    {
    	if (! $this->getRequest()->isPost())
    		return $this->getResponse()->setHttpResponseCode(415);
        if (! $this->_ACL->isAllowed($this->_session->role, 'admin', 'edit'))
            return $this->getResponse()->setHttpResponseCode(403);
        if (is_null($id = $this->getRequest()->getPost('id')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param id');
		if (is_null($sequence = $this->getRequest()->getPost('sequence')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param sequence');
		if (is_null($parent = $this->getRequest()->getPost('parent')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param parent');
		if (is_null($name = $this->getRequest()->getPost('name')))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expect param name');

        # Подключение модели для работы с категориями
    	$categories = new Application_Model_Categories();
    	try {
    		$categories->editCategories($id, $sequence, $parent, $name);
    	} catch (Categories_Exception $ex) {
    		return $this->getResponse()
    			->setHttpResponseCode(400);
    	} catch (Exception $ex) {
    	    return $this->getResponse()
    			->setHttpResponseCode(500);
    	}
    	$this->getResponse()
    		->setHttpResponseCode(204);
    }

    /**
     * Получение списка пользователей
     *
     * @return void
     */
    public function usersviewAction()
    {
        if (! $this->getRequest()->isPost())
            return $this->getResponse()->setHttpResponseCode(415);
        if (! $this->_ACL->isAllowed($this->_session->role, 'admin', 'view'))
            return $this->getResponse()->setHttpResponseCode(403);
        $inst = new Application_Model_Acldb();
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->appendBody(Zend_Json::encode($inst->fetchAll()->toArray()));
    }

    /**
     * Редактирование списка пользователей
     *
     * @return void
     */
    public function userseditAction()
    {
        if (! $this->getRequest()->isPost())
            return $this->getResponse()->setHttpResponseCode(415);
        if (! $this->_ACL->isAllowed($this->_session->role, 'admin', 'edit'))
            return $this->getResponse()->setHttpResponseCode(403);

        $row = Zend_Json::decode($this->getRequest()->getRawBody());
        if (! array_key_exists('id', $row) || empty($row['id']))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expected id param');
        else
            $id = intval($row['id']);
        if (! array_key_exists('login', $row) || empty($row['login']))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expected login param');
        else
            $login = $row['login'];
        if (! array_key_exists('role', $row) || empty($row['role']))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expected role param');
        else
            $role = $row['role'];
        if (! array_key_exists('enabled', $row) || empty($row['enabled']))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expected enabled param');
        if ($row['enabled'] == 'Блокирован')
            $enabled = 0;
        else
            $enabled = 1;

        $inst = new Application_Model_Acldb();
        try {
            $inst->editUser($id, $login, $role, $enabled);
        } catch (Acldb_Exception $ex) {
            return $this->getResponse()
                ->setHttpResponseCode(400);
        } catch (Exception $ex) {
            return $this->getResponse()
                ->setHttpResponseCode(500);
        }

    	$this->getResponse()
    		->setHttpResponseCode(204);
    }

    /**
     * Редактирование списка пользователей
     *
     * @return void
     */
    public function userscreateAction()
    {
        if (! $this->getRequest()->isPost())
            return $this->getResponse()->setHttpResponseCode(415);
        if (! $this->_ACL->isAllowed($this->_session->role, 'admin', 'edit'))
            return $this->getResponse()->setHttpResponseCode(403);

        $row = Zend_Json::decode($this->getRequest()->getRawBody());
        if (! array_key_exists('login', $row) || empty($row['login']))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expected login param');
        else
            $login = $row['login'];
        if (! array_key_exists('role', $row) || empty($row['role']))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expected role param');
        else
            $role = $row['role'];
        if (! array_key_exists('enabled', $row) || empty($row['enabled']))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expected enabled param');
        if ($row['enabled'] == 'Блокирован')
            $enabled = 0;
        else
            $enabled = 1;

        $inst = new Application_Model_Acldb();
        try {
            $inst->createUser($login, $role, $enabled);
        } catch (Acldb_Exception $ex) {
            return $this->getResponse()
                ->setHttpResponseCode(400);
        } catch (Exception $ex) {
            return $this->getResponse()
                ->setHttpResponseCode(500);
        }

    	$this->getResponse()
    		->setHttpResponseCode(204);
    }

    /**
     * Удаление пользователя
     *
     * @return void
     */
    public function usersdestroyAction()
    {
        if (! $this->getRequest()->isPost())
            return $this->getResponse()->setHttpResponseCode(415);
        if (! $this->_ACL->isAllowed($this->_session->role, 'admin', 'edit'))
            return $this->getResponse()->setHttpResponseCode(403);

        $inst = new Application_Model_Acldb();
        $rows = Zend_Json::decode($this->getRequest()->getRawBody());
        // Проверка что пришел массив
        if (! is_array($rows))
            return $this->getResponse()
                ->setHttpResponseCode(400)
                ->appendBody('expected array');
        // Если массив содержит один элемент, приведем его
        if (! array_key_exists(0, $rows))
            $rows = array($rows);
        foreach ($rows as $key=>$row) {
            if (! array_key_exists('id', $row) || empty($row['id']))
                return $this->getResponse()
                    ->setHttpResponseCode(400)
                    ->appendBody('expected id param');
            else
                $id = $row['id'];

            try {
                $inst->destroyUser($id);
            } catch (Acldb_Exception $ex) {
                return $this->getResponse()
                    ->setHttpResponseCode(400);
            } catch (Exception $ex) {
                return $this->getResponse()
                    ->setHttpResponseCode(500);
            }
        }

    	$this->getResponse()
            ->setHttpResponseCode(204);
    }
}