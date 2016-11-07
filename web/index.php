<?php
version_compare(PHP_VERSION, '5.5.0', 'lt') ? exit('PHP version is too low :)') : null;
define('DEV', true);
error_reporting(DEV ? -1 : 0);
define('WEBROOT', __DIR__);
define('ROOT', dirname(WEBROOT));
require ROOT . '/vendor/autoload.php';
require ROOT . '/vendor/lying/Lying.php';
