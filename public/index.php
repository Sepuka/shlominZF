<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
    
defined('CONFIG_FILE')
    || define('CONFIG_FILE', APPLICATION_PATH . '/configs/application.ini');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

/** Загрузка собственного обработчика конфигов */
require_once APPLICATION_PATH . '/models/MemcachedConfig.php';
require_once 'Zend/Cache/Backend/Memcached.php';
require_once 'Zend/Cache/Core.php';
require_once 'Zend/Cache.php';

$config = new Application_Model_MemcachedConfig(CONFIG_FILE, APPLICATION_ENV);
// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    $config
);

// Добавление маршрутов
$router = Zend_Controller_Front::getInstance()->getRouter();
$router->addConfig($config, 'routes');
unset($config);

$application->bootstrap()
            ->run();