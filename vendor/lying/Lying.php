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
}
