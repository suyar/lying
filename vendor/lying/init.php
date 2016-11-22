<?php
use lying\base\Exception;

date_default_timezone_set('Asia/Shanghai');

define('DIR_LYING', __DIR__);

define('DIR_APP', ROOT . '/app');

define('DIR_CONF', ROOT . '/config');

define('DIR_RUNTIME', ROOT . '/runtime');

require DIR_LYING . '/Lying.php';

spl_autoload_register(['\Lying', 'autoload']);

Lying::$classes = require DIR_LYING . '/classes.php';

Exception::register();

$service = require DIR_CONF . '/service.php';

Lying::$container = new lying\service\Container($service);

require DIR_LYING . '/functions.php';
