<?php
define('DS', DIRECTORY_SEPARATOR);

define('DIR_KERNEL', __DIR__);

define('DIR_ROOT', dirname(DIR_KERNEL));

define('DIR_WEB', DIR_ROOT . DS . 'web');

define('DIR_CONFIG', DIR_ROOT . DS . 'config');

define('DIR_CONSOLE', DIR_ROOT . DS . 'console');

define('DIR_MODULE', DIR_ROOT . DS . 'module');

define('DIR_RUNTIME', DIR_ROOT . DS . 'runtime');

require __DIR__ . DS . 'Lying.php';

require __DIR__ . DS . 'function.php';
