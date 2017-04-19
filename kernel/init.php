<?php
header('Content-Type:text/html; charset=UTF-8');

date_default_timezone_set('Asia/Shanghai');

define('DIR_LYING', __DIR__);

define('ROOT', realpath(DIR_LYING . '/../'));

define('DIR_CONF', ROOT . DIRECTORY_SEPARATOR . 'config');

define('DIR_MODULE', ROOT . DIRECTORY_SEPARATOR . 'module');

define('DIR_RUNTIME', ROOT . DIRECTORY_SEPARATOR . 'runtime');

require DIR_LYING . DIRECTORY_SEPARATOR . 'Lying.php';

Lying::boot();

require DIR_LYING . DIRECTORY_SEPARATOR . 'function.php';

register_shutdown_function(function() {
    \lying\service\Hook::trigger(\lying\service\Hook::APP_END);
});

\lying\service\Hook::init();

\lying\service\Hook::trigger(\lying\service\Hook::APP_READY);
