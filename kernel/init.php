<?php
header('Content-Type:text/html; charset=UTF-8');

define('DS', DIRECTORY_SEPARATOR);

define('DIR_KERNEL', __DIR__);

define('DIR_WEB', dirname($_SERVER['SCRIPT_FILENAME']));

define('DIR_ROOT', dirname(DIR_WEB));

define('DIR_CONFIG', DIR_ROOT . DS . 'config');

define('DIR_MODULE', DIR_ROOT . DS . 'module');

define('DIR_RUNTIME', DIR_ROOT . DS . 'runtime');

require __DIR__ . DS . 'Lying.php';

require __DIR__ . DS . 'function.php';
