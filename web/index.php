<?php
version_compare(PHP_VERSION, '5.5.0', '>=') || die('Lying require 5.5.0 or higher PHP version :)');
require __DIR__ . '/../kernel/init.php';
Lying::run(require DIR_CONFIG . '/web.php');
