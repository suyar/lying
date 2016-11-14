<?php

class Lying
{
    public static $classes = [];
    
    /**
     * 框架服务类
     * @var lying\base\Container
     */
    public static $container;
    
    public static function autoload($className)
    {
        if (isset(self::$classes[$className])) {
            $file = self::$classes[$className];
        }else {
            $file = ROOT . '/' . str_replace('\\', '/', $className) . '.php';
        }
        
        require $file;
    }
    
    public static function run()
    {
        $router = self::$container->get('router');
        $router->parse();
    }
    
    
    
    
}


