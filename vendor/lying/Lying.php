<?php

class Lying
{
    /**
     * 自身实例
     * @var \Lying
     */
    private static $instance;
    
    /**
     * 所有的核心类文件映射
     * @var array
     */
    public static $classes = [];
    
    /**
     * 框架单例容器
     * @var lying\service\Container
     */
    public static $container;
    
    /**
     * 初始化自身实例
     */
    public function __construct()
    {
        self::$instance = $this;
    }
    
    /**
     * 自动加载
     * @param string $className 类名
     */
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
    
    /**
     * 程序执行入口
     * @throws \Exception
     */
    public function run()
    {
        $router = self::getRouter();
        list($m, $c, $a) = $router->parse();
        $class = "app\\$m\\ctrl\\$c";
        if (class_exists($class) && method_exists($class, $a) && (new \ReflectionMethod($class, $a))->isPublic()) {
            echo (new $class())->$a();
        }else {
            throw new \Exception('Page not found.', 404);
        }
    }
    
    /**
     * 返回自身实例
     * @return \Lying
     */
    public static function instance()
    {
        return self::$instance;
    }
    
    /**
     * 获取配置类实例
     * @return lying\service\Config
     */
    public static function getConfig()
    {
        return self::$container->make('config');
    }
    
    /**
     * 获取路由类实例
     * @return lying\service\Router
     */
    public static function getRouter()
    {
        return self::$container->make('router');
    }
    
    /**
     * 获取请求类实例
     * @return lying\service\Request
     */
    public function getRequest()
    {
        return self::$container->make('request');
    }
    
    /**
     * 获取加密类实例
     * @return lying\service\Secure
     */
    public static function getSecure()
    {
        return self::$container->make('secure');
    }
    
    /**
     * 获取cookie类实例
     * @return lying\service\Cookie
     */
    public static function getCookie()
    {
        return self::$container->make('cookie');
    }
    
    /**
     * 获取session类实例
     * @return lying\service\Session
     */
    public static function getSession()
    {
        return self::$container->make('session');
    }
    
    
}


