<?php
use lying\base\Exception;
use lying\service\Maker;

date_default_timezone_set('Asia/Shanghai');

define('DIR_LYING', __DIR__);

define('DIR_APP', ROOT . '/app');

define('DIR_CONF', ROOT . '/config');

define('DIR_RUNTIME', ROOT . '/runtime');

require DIR_LYING . '/Lying.php';

Lying::$classMap = require DIR_LYING . '/classes.php';

spl_autoload_register([Lying::class, 'autoload']);

Exception::register();

Lying::$maker = new Maker(require(DIR_CONF . '/service.php'));

require DIR_LYING . '/functions.php';
