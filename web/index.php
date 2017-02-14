<?php
if(version_compare(PHP_VERSION, '5.5.0', 'lt')) exit('Lying require 5.5.0 or higher PHP version :).');
define('WEB_ROOT', __DIR__);
require realpath(WEB_ROOT . '/../vendor/autoload.php');
require realpath(WEB_ROOT . '/../vendor/lying/init.php');
maker()->dispatch()->runAction();
