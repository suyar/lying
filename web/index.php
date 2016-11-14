<?php
if(version_compare(PHP_VERSION, '5.5.0', 'lt')) exit('Lying require 5.5.0 or higher PHP version :).');
define('DEV', true);
//error_reporting(DEV ? -1 : 0);
define('WEB_ROOT', __DIR__);
define('ROOT', dirname(WEB_ROOT));
require ROOT . '/vendor/autoload.php';
require ROOT . '/vendor/lying/Lying.php';

(new app\index\ctrl\IndexCtrl())->index();