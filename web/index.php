<?php
version_compare(PHP_VERSION, '5.5.0', '>=') || die('Lying requires 5.5.0 or higher PHP version :)');
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../kernel/init.php';
(new Lying(require DIR_CONFIG . '/web.php'))->run();

