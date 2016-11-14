<?php
define('LYING_ROOT', __DIR__);

require LYING_ROOT . '/Base.php';

class Lying extends lying\Base
{
    
}
spl_autoload_register([Lying::class, 'autoload']);
Lying::$classes = require LYING_ROOT . '/classes.php';
set_exception_handler([lying\base\Exception::class, 'exceptionHandler']);
set_error_handler([lying\base\Exception::class, 'errorHandler']);
$service = require ROOT . '/config/service.php';
Lying::$service = new lying\service\Service($service);
require LYING_ROOT . '/functions.php';


