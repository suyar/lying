<?php
header('Content-Type:text/html; charset=UTF-8');

define('DIR_KERNEL', __DIR__);

define('DIR_WEB', dirname($_SERVER['SCRIPT_FILENAME']));

define('DIR_ROOT', dirname(DIR_WEB));

define('DIR_CONFIG', DIR_ROOT . DIRECTORY_SEPARATOR . 'config');

define('DIR_MODULE', DIR_ROOT . DIRECTORY_SEPARATOR . 'module');

define('DIR_RUNTIME', DIR_ROOT . DIRECTORY_SEPARATOR . 'runtime');

require __DIR__ . '/Lying.php';

require __DIR__ . '/function.php';
