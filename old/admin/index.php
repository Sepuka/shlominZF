<?php
/**
 * Точка входа в админку
 * 
 * $Id: index.php 129 2011-05-17 13:16:39Z Sepuka $
 */
require_once(dirname(__FILE__) . '/../controllers/c.admin.php');

echo header('Content-Type: text/html; charset=utf-8');
echo header('Content-Language: ru');

$admin = new c_admin();
echo $admin->router();
?>