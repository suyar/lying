<?php
namespace lying;

define('LYING_ROOT', __DIR__);

class BaseLying
{
    
    public static $classes = [];
    
    public static function autoload($className)
    {
        if (isset(self::$classes[$className])) {
            $classFile = LYING_ROOT . self::$classes[$className];
        }else {
            $classFile = ROOT . '/' . str_replace('\\', '/', $className) . '.php';
        }
        
        require $classFile;
    }
    
    public static function run($cfg = [])
    {
        
    }
}



