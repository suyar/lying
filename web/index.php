<?php
if (version_compare(PHP_VERSION, '5.5.0', 'lt')) exit('PHP version is too low.');
define('DEV', true);
define('WEBROOT', dirname(__FILE__));
define('ROOT', dirname(WEBROOT));
require(ROOT . '/core/App.php');
App::run();



