<?php
date_default_timezone_set('Asia/Shanghai');

define('DIR_LYING', __DIR__);

define('ROOT', realpath('../'));

define('DIR_CONF', ROOT . '/config');

define('DIR_MODULE', ROOT . '/module');

define('DIR_RUNTIME', ROOT . '/runtime');

require DIR_LYING . '/Lying.php';

Lying::boot();

require DIR_LYING . '/function.php';
