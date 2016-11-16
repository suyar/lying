<?php
function mtime()
{
    list($msec, $sec) = explode(' ', microtime());
    return $sec . ceil($msec * 1000);
}
$GLOBALS['s'] = mtime();
if(version_compare(PHP_VERSION, '5.5.0', 'lt')) exit('Lying require 5.5.0 or higher PHP version :).');
define('DEV', true);
//error_reporting(DEV ? -1 : 0);
define('WEB_ROOT', __DIR__);
define('ROOT', dirname(WEB_ROOT));
require ROOT . '/vendor/autoload.php';
require ROOT . '/vendor/lying/init.php';
var_dump(mtime() - $GLOBALS['s']);
Lying::run();

var_dump(mtime() - $GLOBALS['s']);