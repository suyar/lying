<?php

class Lying
{
    public static $classes = [];
    
    /**
     * 框架单例容器
     * @var lying\service\Container
     */
    public static $container;
    
    public static function autoload($className)
    {
        if (isset(self::$classes[$className])) {
            $file = self::$classes[$className];
        }else {
            $file = ROOT . '/' . str_replace('\\', '/', $className) . '.php';
        }
        if (file_exists($file)) {
            require $file;
        }
    }
    
    public static function run()
    {
        $router = self::$container->get('router');
        list($m, $c, $a) = $router->parse();
        $class = "app\\$m\\ctrl\\$c";
        if (class_exists($class) && method_exists($class, $a) && (new \ReflectionMethod($class, $a))->isPublic()) {
            echo (new $class())->$a();
        }else {
            throw new \Exception('Page not found.', 404);
        }
        
    }
    
    
    
    
}


