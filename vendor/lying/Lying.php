<?php
define('LYING_ROOT', __DIR__);

class Lying
{
    
    public static $classes = [];
    
    public static $extends = [];
    
    public static $service;
    
    
    public static function autoload($className)
    {
        if (isset(self::$classes[$className])) {
            require self::$classes[$className];
        }
    }
}
spl_autoload_register([Lying::class, 'autoload']);
Lying::$classes = require __DIR__ . '/classes.php';
set_exception_handler(['lying\base\Exception', 'exceptionHandler']);
set_error_handler(['lying\base\Exception', 'errorHandler']);
Lying::$service = new lying\service\Service();
require __DIR__ . '/functions.php';


