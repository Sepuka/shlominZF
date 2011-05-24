<?php
/**
 * Исправление входящих параметров
 * 
 * Если необходимо удалить лишние GET параметры запроса, внесите их в массив $wrongParams
 * 
 * $Id$
 */
$URL = $_SERVER['REQUEST_URI'];
$params = parse_url($URL);
if ($params['path'] == '/')
    $URL = 'index.php';
else {
    parse_str($params['query'], $query);
    $wrongParams = array('_dc');
    foreach ($wrongParams as $param)
        unset($query[$param]);
    $URL = str_replace('&', '.', $params['path'] . '?' . http_build_query($query));
}
?>