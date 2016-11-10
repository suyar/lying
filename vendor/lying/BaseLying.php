<?php
namespace lying;

use lying\core\Service;
define('LYING_ROOT', __DIR__);

class BaseLying
{
    
    public static $classes = [];
    
    public static $extends = [];
    
    public static $service = [];
    
    
    public static function autoload($className)
    {
        require dirname(LYING_ROOT) . '/' . str_replace('\\', '/', $className) . '.php';
    }
    
    
    public static function createObject($className)
    {
        if (isset(self::$service[$className])) {
            return self::$service[$className];
        }
        $obj = new $className();
        if ($obj instanceof Service) {
            self::$service[$className] = $obj;
        }
        return $obj;
    }
    
    public static function getConfig($config) {
        return self::createObject('lying\core\Config')->get($config);
    }
    
    
    
    
    
}



