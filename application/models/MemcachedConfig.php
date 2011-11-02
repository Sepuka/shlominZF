<?php
require_once 'Zend/Config.php';

class Application_Model_MemcachedConfig extends Zend_Config
{
    const MEMCACHED_HOST        = 'localhost';
    const MEMCACHED_PORT        = '11211';
    const LIFETIME              = 3600;

    public function __construct($configFile, $section)
    {
        $backend = new Zend_Cache_Backend_Memcached(array(
            'servers'   => array(array(
                'host'      => self::MEMCACHED_HOST,
                'port'      => self::MEMCACHED_PORT
            ))
        ));
        $frontend = new Zend_Cache_Core(array(
            'cache_id_prefix'           => date('YmdH') . "_",
            'lifetime'                  => self::LIFETIME,
            'automatic_serialization'   => true
        ));
        try {
            $cache = Zend_Cache::factory($frontend, $backend);
        } catch (Zend_Cache_Exception $ex) {
            # TODO: лог о том что кеширование не используется
            $config = $this->_loadConfig($configFile, $section);
            $config = self::convertConfig($config);
            return parent::__construct($config);
        }
        // Ключ доступа к данным в memcached меняется ежечасно
        $keyConfig = $frontend->getOption('cache_id_prefix') . 'config';
        if (($config = $cache->load($keyConfig)) === false) {
            $config = $this->_loadConfig($configFile, $section);
            $cache->save($config, $keyConfig, array(), self::LIFETIME);
        }
        // Полученный конфиг имеет вид ключ.ключ.ключ = значение
        // Конвертируем его во вложеный массив массивов
        $config = self::convertConfig($config);
        // и скормим родителям
        parent::__construct($config);
    }

    /**
     * Перевод каждой строки массива с ключами вида ключ.ключ.ключ
     * во вложенным массив
     *
     * @param array $config
     * @return array
     */
    static public function convertConfig($config)
    {
        $result = array();
        // Анонимная функция рекурсивно создает лестницу ключей конфига
        $proccessKeys = function($keys, $value) use (&$proccessKeys) {
            return ($keys) ? array(array_shift($keys) => $proccessKeys($keys, $value)) : $value;
        };
        foreach ($config as $key => $value) {
        	$result = array_merge_recursive($result, $proccessKeys(explode('.', $key), $value));
        }
        return $result;
    }

    /**
     * Расшифровка и обработка конфигурации
     *
     * Метод загружает базовый конфиг (production) и пропатчивает его секцией
     * 
     * @param string $configFile
     * @param string $section
     * @return array
     */
    protected function _loadConfig($configFile, $section)
    {
        $config = parse_ini_string(file_get_contents($configFile), true);
        $baseConfig = array_shift($config);
        $section = "$section : production";
        if (array_key_exists($section, $config))
            $baseConfig = array_merge($baseConfig, $config[$section]);
        return $baseConfig;
    }
}