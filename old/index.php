<?php
/**
 * Глобальная точка входа на сайт
 * 
 * $Id: index.php 129 2011-05-17 13:16:39Z Sepuka $
 */

echo header('Content-Type: text/html; charset=utf-8');
echo header('Content-Language: ru');

// Подключение дополнительных библиотек
include_once(dirname(__FILE__) . '/lib/fix_params.php');    // Исправление входящих параметров

$cachefile = "cache/{$URL}.cache";

if (file_exists($cachefile)) {
    if ($fp = fopen($cachefile, 'rb')) {
    	fpassthru($fp);
    	fclose($fp);
    }
    $f = stat($cachefile);
    if (time() - $f[9] > 2)
        unlink($cachefile);
    return;
} else {
    $cachefile_tmp = $cachefile . '.' . getmypid();
    $cachefp = fopen($cachefile_tmp, "w");
    ob_start();
}

// Этот код будет выполнен в случае отсутствия копии в кеше
require_once(dirname(__FILE__) . '/controllers/c.index.php');
$site = new c_index();

if ($cachefp) {
    $file = ob_get_contents();
    fwrite($cachefp, $file);
    fclose($cachefp);
    rename($cachefile_tmp, $cachefile);
    ob_end_flush();
}
?>