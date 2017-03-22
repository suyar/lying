<?php
date_default_timezone_set('Asia/Shanghai');

define('DIR_LYING', __DIR__);

define('ROOT', realpath(DIR_LYING . '/../'));

define('DIR_CONF', ROOT . '/config');

define('DIR_MODULE', ROOT . '/module');

define('DIR_RUNTIME', ROOT . '/runtime');

require DIR_LYING . '/Lying.php';

Lying::boot();

require DIR_LYING . '/function.php';

register_shutdown_function(function() {
    \lying\service\Hook::trigger(\lying\service\Hook::APP_END);
});

\lying\service\Hook::init();

\lying\service\Hook::trigger(\lying\service\Hook::APP_READY);
