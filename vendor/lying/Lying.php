<?php
class Lying
{
    /**
     * @var array 类文件映射
     */
    private static $classMap = [];
    
    /**
     * @var array 命名空间映射
     */
    private static $extend = [];
    
    /**
     * @var \lying\service\Maker 工厂实例
     */
    public static $maker;
    
    /**
     * 初始化启动参数
     */
    public static function boot()
    {
        self::$classMap = require DIR_LYING . '/classes.php';
        
        spl_autoload_register([self::class, 'autoload']);
        
        \lying\base\Exception::register();
        
        self::$maker = new \lying\service\Maker(require DIR_CONF . '/service.php');
        
        self::$extend = self::$maker->config()->get('extend');
    }
    
    /**
     * 自动加载
     * @param string $className 完整类名
     */
    private static function autoload($className)
    {
        if (isset(self::$classMap[$className])) {
            $file = self::$classMap[$className];
        } else {
            ($file = self::psr4Loader($className)) || ($file = self::psr0Loader($className));
        }
        if ($file) {
            require $file;
        }
    }
    
    /**
     * PSR-4自动加载,从完整命名空间前缀到最简命名空间前缀,贪婪加载
     * @param string $className 类全名
     * @return string|boolean 成功返回文件,失败返回false
     */
    public static function psr4Loader($className)
    {
        if (isset(self::$extend['psr-4'])) {
            $prefix = $className;
            while (false !== $pos = strrpos($prefix, '\\')) {
                $prefix = substr($prefix, 0, $pos);
                if (isset(self::$extend['psr-4'][$prefix])) {
                    $relativeClass = str_replace('\\', '/', substr($className, $pos));
                    if (is_array(self::$extend['psr-4'][$prefix])) {
                        foreach (self::$extend['psr-4'][$prefix] as $path) {
                            $file = $path . $relativeClass . '.php';
                            if (file_exists($file)) {
                                return $file;
                            }
                        }
                    } else {
                        $file = self::$extend['psr-4'][$prefix] . $relativeClass . '.php';
                        if (file_exists($file)) {
                            return $file;
                        }
                    }
                }
            }
        }
        return false;
    }
    
    public static function psr0Loader($className)
    {
        if (isset(self::$extend['psr-0'])) {
            if (isset(self::$extend['psr-0'][$className])) {
                if (false === $pos = strrpos($className, '\\')) {
                    $file = self::$extend['psr-0'][$className] . '/' . str_replace('_', '/', $className) . '.php';
                    if (file_exists($file)) {
                        return $file;
                    }
                } else {
                    $prefix = substr($className, 0, $pos);
                    $class = substr($className, $pos);
                    var_dump($prefix, $class);
                }
            }
        }
        return false;
    }
}
