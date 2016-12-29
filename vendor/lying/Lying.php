<?php
class Lying
{
    /**
     * 类文件映射
     * @var array
     */
    public static $classMap = [];
    
    /**
     * 工厂实例
     * @var \lying\service\Maker
     */
    public static $maker;
    
    /**
     * 自动加载
     * @param string $className 类名
     */
    public static function autoload($className)
    {
        if (isset(self::$classMap[$className])) {
            $file = self::$classMap[$className];
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
        $router = maker()->router();
        list($m, $c, $a) = $router->parse();
        $class = "module\\$m\\ctrl\\$c";
        if (class_exists($class) && method_exists($class, $a) && (new \ReflectionMethod($class, $a))->isPublic()) {
            echo (new $class())->$a();
        }else {
            throw new \Exception('Page not found.', 404);
        }
    }
}