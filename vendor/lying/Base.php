<?php
namespace lying;

class Base
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
    
    public static function getConfig($config)
    {
        return self::$service->get('config')->get($config);
    }
}