<?php
class Lying
{
    /**
     * @var array 类文件映射
     */
    public static $classMap = [];
    
    /**
     * @var \lying\service\Maker 工厂实例
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
        list($m, $c, $a) = maker()->router()->parse();
        $class = "module\\$m\\ctrl\\$c";
        if (class_exists($class) && method_exists($class, $a) && (new \ReflectionMethod($class, $a))->isPublic()) {
            echo (new $class())->$a();
        } else {
            throw new \Exception('Page not found.', 404);
        }
    }
}
