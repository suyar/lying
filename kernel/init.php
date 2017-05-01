<?php
header('Content-Type:text/html; charset=UTF-8');

define('DIR_ROOT', realpath(__DIR__ . '/../'));

define('DIR_KERNEL', __DIR__);

define('DIR_CONFIG', DIR_ROOT . DIRECTORY_SEPARATOR . 'config');

define('DIR_MODULE', DIR_ROOT . DIRECTORY_SEPARATOR . 'module');

define('DIR_RUNTIME', DIR_ROOT . DIRECTORY_SEPARATOR . 'runtime');

define('DIR_WEB', dirname($_SERVER['SCRIPT_FILENAME']));

require __DIR__ . '/Lying.php';

Lying::boot(require DIR_CONFIG . '/web.php');

require __DIR__ . '/function.php';
